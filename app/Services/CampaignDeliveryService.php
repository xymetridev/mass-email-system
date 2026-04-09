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

    public function getRunningCampaigns(): array
    {
        return $this->db->table('campaigns')
            ->where('status', 'RUNNING')
            ->get()
            ->getResultArray();
    }

    private function getSender(int $senderId): ?array
    {
        return $this->db->table('sender_accounts')
            ->where('id', $senderId)
            ->get()
            ->getRowArray();
    }

    private function getTemplateByCampaign(int $campaignId): ?array
    {
        return $this->db->table('templates')
            ->where('campaign_id', $campaignId)
            ->get()
            ->getRowArray();
    }

    public function claimPendingRecipients(int $campaignId, int $limit): array
    {
        $limit = max(1, $limit);
        $token = bin2hex(random_bytes(16));
        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        // release stale claim
        $this->db->table('recipients')
            ->where('campaign_id', $campaignId)
            ->where('claimed_by IS NOT NULL')
            ->where('claimed_at <', date('Y-m-d H:i:s', strtotime('-5 minutes')))
            ->update([
                'claimed_by' => null,
                'claimed_at' => null,
            ]);

        // ambil kandidat
        $rows = $this->db->table('recipients')
            ->select('id')
            ->where('campaign_id', $campaignId)
            ->where('status', 'PENDING')
            ->where('claimed_by IS NULL')
            ->orderBy('id', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        $ids = array_column($rows, 'id');

        if ($ids) {
            $this->db->table('recipients')
                ->whereIn('id', $ids)
                ->where('claimed_by IS NULL')
                ->update([
                    'claimed_by' => $token,
                    'claimed_at' => $now,
                    'updated_at' => $now,
                ]);
        }

        $claimed = $this->db->table('recipients')
            ->where('claimed_by', $token)
            ->get()
            ->getResultArray();

        $this->db->transComplete();

        return $claimed;
    }

    public function processCampaign(array $campaign, callable $output): void
    {
        $campaignId = (int) $campaign['id'];
        $senderId = (int) ($campaign['sender_account_id'] ?? 0);
        $batchSize = max(1, (int) ($campaign['batch_size'] ?? 100));

        $sender = $this->getSender($senderId);
        $template = $this->getTemplateByCampaign($campaignId);

        if (! $sender || ! $template) {
            $output("Campaign #$campaignId skipped (missing sender/template)");
            return;
        }

        $recipients = $this->claimPendingRecipients($campaignId, $batchSize);

        foreach ($recipients as $r) {
            $this->deliver($campaign, $r, $sender, $template, $output);
        }

        $this->markCompletedIfFinished($campaignId);
    }

    private function deliver(array $campaign, array $r, array $sender, array $template, callable $output): void
    {
        $id = (int) $r['id'];
        $fullName = trim(((string) ($r['first_name'] ?? '')) . ' ' . ((string) ($r['last_name'] ?? '')));
        $name = $fullName !== '' ? $fullName : ((string) ($campaign['default_name'] ?? 'Customer'));

        $subject = (string) ($template['subject'] ?? '');
        $htmlBody = str_replace('{{name}}', $name, (string) ($template['body_html'] ?? ''));
        $textBody = str_replace('{{name}}', $name, (string) ($template['body_text'] ?? ''));
        $finalBody = $htmlBody !== '' ? $htmlBody : $textBody;

        try {
            $email = $this->buildMailer($sender);
            $email->setFrom((string) $sender['sender_email'], (string) ($sender['sender_name'] ?? ''));
            $email->setTo((string) $r['email']);
            $email->setSubject($subject);
            $email->setMessage($finalBody);

            if ($textBody !== '') {
                $email->setAltMessage($textBody);
            }

            if (! $email->send(false)) {
                $this->fail($id, 'SMTP failed', (int) ($r['retry_count'] ?? 0));
                return;
            }

            $now = date('Y-m-d H:i:s');
            $this->db->table('recipients')
                ->where('id', $id)
                ->update([
                    'status' => 'SENT',
                    'sent_at' => $now,
                    'last_attempt_at' => $now,
                    'last_error' => null,
                    'claimed_by' => null,
                    'claimed_at' => null,
                    'updated_at' => $now,
                ]);

            $output("Recipient $id SENT");
        } catch (Throwable $e) {
            $this->fail($id, $e->getMessage(), (int) ($r['retry_count'] ?? 0));
        }
    }

    private function fail(int $id, string $error, int $retry): void
    {
        $max = 3;
        $next = $retry + 1;

        $status = $next >= $max ? 'FAILED' : 'PENDING';

        $this->db->table('recipients')
            ->where('id', $id)
            ->update([
                'status' => $status,
                'retry_count' => $next,
                'last_error' => mb_substr($error, 0, 1000),
                'last_attempt_at' => date('Y-m-d H:i:s'),
                'claimed_by' => null,
                'claimed_at' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    private function markCompletedIfFinished(int $campaignId): void
    {
        $remaining = $this->db->table('recipients')
            ->where('campaign_id', $campaignId)
            ->where('status', 'PENDING')
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
        return Services::email([
            'protocol' => 'smtp',
            'SMTPHost' => $sender['smtp_host'],
            'SMTPPort' => $sender['smtp_port'],
            'SMTPUser' => $sender['smtp_user'],
            'SMTPPass' => $sender['smtp_pass'],
            'SMTPCrypto' => $sender['encryption'] ?? '',
            'mailType' => 'html',
        ], false);
    }
}