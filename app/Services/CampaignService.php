<?php

namespace App\Services;

use App\Mail\CampaignTestMail;
use App\Models\Campaign;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;

class CampaignService
{
    public function __construct(
        private readonly RecipientImportService $recipientImportService,
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function importRecipients(Campaign $campaign, UploadedFile $file): array
    {
        return $this->recipientImportService->importFromTxt($campaign, $file);
    }

    public function sendTestEmail(Campaign $campaign, string $recipientEmail): void
    {
        $campaign->loadMissing(['senderAccount', 'template']);

        $mailData = [
            'subject' => $campaign->template->subject,
            'html' => $campaign->template->html_content,
            'sender_name' => $campaign->senderAccount->from_name,
            'sender_email' => $campaign->senderAccount->from_email,
        ];

        Mail::to($recipientEmail)->send(new CampaignTestMail($mailData));
    }
}
