<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Email\Email;
use Config\Database;
use Config\Services;
use Throwable;

class CampaignDeliveryService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getRunningCampaigns(): array
    {
        return $this->db->table('campaigns')
            ->where('status', 'RUNNING')
            ->get()
            ->getResultArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSenderConfig(int $senderId): ?array
    {
        $row = $this->db->table('senders')
            ->select('id, smtp_host, smtp_port, smtp_user, smtp_pass, smtp_crypto, from_email, from_name')
            ->where('id', $senderId)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getTemplate(int $templateId): ?array
    {
        $row = $this->db->table('campaign_templates')
            ->select('id, subject, html_body, text_body')
            ->where('id', $templateId)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Claims pending recipients by setting them PROCESSING to prevent duplicates.
     *
     * @return list<array<string, mixed>>
     */
    public function claimPendingRecipients(int $campaignId, int $limit): array
    {
        $limit = max(1, $limit);
        $claimToken = bin2hex(random_bytes(16));

        $this->db->transStart();

        $candidates = $this->db->table('campaign_recipients')
            ->select('id')
            ->where('campaign_id', $campaignId)
            ->where('status', 'PENDING')
            ->orderBy('id', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        $ids = array_map(static fn (array $row): int => (int) $row['id'], $candidates);

        if ($ids !== []) {
            $builder = $this->db->table('campaign_recipients');
            $builder->set('status', 'PROCESSING')
                ->set('claimed_by', $claimToken)
                ->set('updated_at', date('Y-m-d H:i:s'))
                ->where('campaign_id', $campaignId)
                ->whereIn('id', $ids)
                ->where('status', 'PENDING')
                ->update();
        }

        $claimed = $this->db->table('campaign_recipients')
            ->select('id, campaign_id, email, name, status, retry_count')
            ->where('campaign_id', $campaignId)
            ->where('claimed_by', $claimToken)
            ->where('status', 'PROCESSING')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $this->db->transComplete();

        return $claimed;
    }

    public function processCampaign(array $campaign, callable $output): void
    {
        $campaignId = (int) $campaign['id'];
        $senderId = (int) ($campaign['sender_id'] ?? 0);
        $templateId = (int) ($campaign['template_id'] ?? 0);
        $batchSize = max(1, (int) ($campaign['batch_size'] ?? 100));

        $sender = $this->getSenderConfig($senderId);
        $template = $this->getTemplate($templateId);

        if ($sender === null || $template === null) {
            $output(sprintf('Campaign #%d skipped: missing sender/template.', $campaignId));

            return;
        }

        $recipients = $this->claimPendingRecipients($campaignId, $batchSize);

        foreach ($recipients as $recipient) {
            $this->deliverRecipient($campaign, $recipient, $sender, $template, $output);
        }

        $this->markCompletedIfFinished($campaignId);
    }

    private function deliverRecipient(
        array $campaign,
        array $recipient,
        array $sender,
        array $template,
        callable $output
    ): void {
        $campaignId = (int) $campaign['id'];
        $recipientId = (int) $recipient['id'];
        $defaultName = (string) ($campaign['default_name'] ?? 'Customer');
        $recipientName = trim((string) ($recipient['name'] ?? ''));
        $toName = $recipientName !== '' ? $recipientName : $defaultName;

        $subject = (string) ($template['subject'] ?? '');
        $htmlBody = str_replace('{{name}}', $toName, (string) ($template['html_body'] ?? ''));
        $textBody = str_replace('{{name}}', $toName, (string) ($template['text_body'] ?? ''));

        try {
            $email = $this->buildMailer($sender);
            $email->setFrom((string) $sender['from_email'], (string) ($sender['from_name'] ?? ''));
            $email->setTo((string) $recipient['email']);
            $email->setSubject($subject);
            $email->setMessage($htmlBody !== '' ? $htmlBody : $textBody);

            if ($textBody !== '') {
                $email->setAltMessage($textBody);
            }

            if (! $email->send(false)) {
                $this->markFailure($recipientId, 'SMTP send returned false', (int) ($recipient['retry_count'] ?? 0));
                $output(sprintf('Campaign #%d recipient #%d failed.', $campaignId, $recipientId));

                return;
            }

            $this->db->table('campaign_recipients')
                ->where('id', $recipientId)
                ->update([
                    'status' => 'SENT',
                    'sent_at' => date('Y-m-d H:i:s'),
                    'last_error' => null,
                    'claimed_by' => null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            $output(sprintf('Campaign #%d recipient #%d sent.', $campaignId, $recipientId));
        } catch (Throwable $e) {
            $this->markFailure($recipientId, $e->getMessage(), (int) ($recipient['retry_count'] ?? 0));
            $output(sprintf('Campaign #%d recipient #%d error handled.', $campaignId, $recipientId));
        }
    }

    private function markFailure(int $recipientId, string $error, int $currentRetryCount): void
    {
        $nextRetry = $currentRetryCount + 1;
        $newStatus = $nextRetry > 3 ? 'FAILED' : 'PENDING';

        $this->db->table('campaign_recipients')
            ->where('id', $recipientId)
            ->update([
                'status' => $newStatus,
                'retry_count' => $nextRetry,
                'last_error' => mb_substr($error, 0, 1000),
                'claimed_by' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    private function markCompletedIfFinished(int $campaignId): void
    {
        $remaining = $this->db->table('campaign_recipients')
            ->where('campaign_id', $campaignId)
            ->whereNotIn('status', ['SENT', 'FAILED'])
            ->countAllResults();

        if ($remaining === 0) {
            $this->db->table('campaigns')
                ->where('id', $campaignId)
                ->where('status', 'RUNNING')
                ->update([
                    'status' => 'COMPLETED',
                    'completed_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        }
    }

    private function buildMailer(array $sender): Email
    {
        $config = [
            'protocol' => 'smtp',
            'SMTPHost' => (string) $sender['smtp_host'],
            'SMTPPort' => (int) $sender['smtp_port'],
            'SMTPUser' => (string) $sender['smtp_user'],
            'SMTPPass' => (string) $sender['smtp_pass'],
            'SMTPCrypto' => (string) ($sender['smtp_crypto'] ?? ''),
            'mailType' => 'html',
            'charset' => 'utf-8',
            'wordWrap' => true,
        ];

        return Services::email($config, false);
    }
}
