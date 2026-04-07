<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Recipient;
use Illuminate\Http\UploadedFile;

class RecipientImportService
{
    /**
     * @return array<string, int>
     */
    public function importFromTxt(Campaign $campaign, UploadedFile $file, int $insertChunkSize = 500): array
    {
        $summary = [
            'total_rows' => 0,
            'valid_rows' => 0,
            'inserted_rows' => 0,
            'duplicate_rows' => 0,
            'invalid_rows' => 0,
        ];

        $seenInFile = [];
        $insertBuffer = [];

        $stream = new \SplFileObject($file->getRealPath());
        $stream->setFlags(\SplFileObject::DROP_NEW_LINE);

        while (! $stream->eof()) {
            $line = $stream->fgets();
            if ($line === false) {
                continue;
            }

            $summary['total_rows']++;

            $line = trim($line);
            if ($line === '') {
                continue;
            }

            [$email, $name] = $this->parseLine($line);

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $summary['invalid_rows']++;
                continue;
            }

            $email = mb_strtolower($email);

            if (isset($seenInFile[$email])) {
                $summary['duplicate_rows']++;
                continue;
            }

            $seenInFile[$email] = true;
            $summary['valid_rows']++;

            $insertBuffer[] = [
                'campaign_id' => $campaign->id,
                'email' => $email,
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($insertBuffer) >= $insertChunkSize) {
                $this->flushBuffer($campaign, $insertBuffer, $summary);
            }
        }

        if ($insertBuffer !== []) {
            $this->flushBuffer($campaign, $insertBuffer, $summary);
        }

        return $summary;
    }

    /**
     * @return array{0: string, 1: ?string}
     */
    private function parseLine(string $line): array
    {
        $parts = explode(',', $line, 2);

        $email = trim($parts[0] ?? '');
        $name = trim($parts[1] ?? '');

        return [$email, $name !== '' ? $name : null];
    }

    /**
     * @param  array<int, array<string, mixed>>  $insertBuffer
     * @param  array<string, int>  $summary
     */
    private function flushBuffer(Campaign $campaign, array &$insertBuffer, array &$summary): void
    {
        $emails = array_column($insertBuffer, 'email');

        $existingEmails = Recipient::query()
            ->where('campaign_id', $campaign->id)
            ->whereIn('email', $emails)
            ->pluck('email')
            ->map(fn (string $email) => mb_strtolower($email))
            ->flip()
            ->all();

        $rowsToInsert = [];
        foreach ($insertBuffer as $row) {
            if (isset($existingEmails[$row['email']])) {
                $summary['duplicate_rows']++;
                continue;
            }

            $rowsToInsert[] = $row;
        }

        if ($rowsToInsert !== []) {
            Recipient::query()->insertOrIgnore($rowsToInsert);
            $summary['inserted_rows'] += count($rowsToInsert);
        }

        $insertBuffer = [];
    }
}
