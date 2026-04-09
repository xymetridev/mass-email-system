<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Email\Email;
use Config\Database;
use Config\Services;
use RuntimeException;

class CampaignService
{
    private BaseConnection $db;
    private RecipientImportService $recipientImportService;

    public function __construct(
        RecipientImportService $recipientImportService,
        ?BaseConnection $db = null
    ) {
        $this->recipientImportService = $recipientImportService;
        $this->db = $db ?? Database::connect();
    }

    /**
     * @return array<string, int>
     */
    public function importRecipients(int $campaignId, UploadedFile $file): array
    {
        return $this->recipientImportService->importFromTxt($campaignId, $file);
    }

    public function sendTestEmail(int $campaignId, string $recipientEmail): void
    {
        $campaign = $this->db->table('campaigns')
            ->where('id', $campaignId)
            ->get()
            ->getRowArray();

        if (! $campaign) {
            throw new RuntimeException('Campaign not found.');
        }

        $senderId = (int) ($campaign['sender_account_id'] ?? 0);
        $sender = $this->db->table('sender_accounts')->where('id', $senderId)->get()->getRowArray();
        $template = $this->db->table('templates')->where('campaign_id', $campaignId)->get()->getRowArray();

        if (! $sender || ! $template) {
            throw new RuntimeException('Missing sender account or template.');
        }

        $email = $this->buildMailer($sender);
        $email->setFrom((string) $sender['sender_email'], (string) ($sender['sender_name'] ?? ''));
        $email->setTo($recipientEmail);
        $email->setSubject((string) ($template['subject'] ?? 'Test Email'));
        $email->setMessage((string) ($template['body_html'] ?? ''));

        if (! $email->send(false)) {
            throw new RuntimeException('Test email failed to send.');
        }
    }

    private function buildMailer(array $sender): Email
    {
        return Services::email([
            'protocol' => 'smtp',
            'SMTPHost' => (string) $sender['smtp_host'],
            'SMTPPort' => (int) $sender['smtp_port'],
            'SMTPUser' => (string) $sender['smtp_user'],
            'SMTPPass' => (string) $sender['smtp_pass'],
            'SMTPCrypto' => (string) ($sender['encryption'] ?? ''),
            'mailType' => 'html',
        ], false);
    }
}
