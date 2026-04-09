<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use Config\Database;

class RecipientImportService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    /**
     * @return array<string, int>
     */
    public function importFromTxt(int $campaignId, UploadedFile $file, int $insertChunkSize = 500): array
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

        $stream = new \SplFileObject((string) $file->getRealPath());
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
            [$firstName, $lastName] = $this->splitName($name);

            $insertBuffer[] = [
                'campaign_id' => $campaignId,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if (count($insertBuffer) >= $insertChunkSize) {
                $this->flushBuffer($campaignId, $insertBuffer, $summary);
            }
        }

        if ($insertBuffer !== []) {
            $this->flushBuffer($campaignId, $insertBuffer, $summary);
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
     * @return array{0: ?string, 1: ?string}
     */
    private function splitName(?string $name): array
    {
        $name = trim((string) $name);
        if ($name === '') {
            return [null, null];
        }

        $parts = preg_split('/\s+/', $name);
        if (! is_array($parts) || $parts === []) {
            return [$name, null];
        }

        $firstName = (string) array_shift($parts);
        $lastName = $parts !== [] ? implode(' ', $parts) : null;

        return [$firstName, $lastName];
    }

    /**
     * @param  array<int, array<string, mixed>>  $insertBuffer
     * @param  array<string, int>  $summary
     */
    private function flushBuffer(int $campaignId, array &$insertBuffer, array &$summary): void
    {
        $emails = array_column($insertBuffer, 'email');

        $existingRows = $this->db->table('recipients')
            ->select('email')
            ->where('campaign_id', $campaignId)
            ->whereIn('email', $emails)
            ->get()
            ->getResultArray();

        $existingEmails = [];
        foreach ($existingRows as $row) {
            $existingEmails[mb_strtolower((string) $row['email'])] = true;
        }

        $rowsToInsert = [];
        foreach ($insertBuffer as $row) {
            if (isset($existingEmails[$row['email']])) {
                $summary['duplicate_rows']++;
                continue;
            }

            $rowsToInsert[] = $row;
        }

        if ($rowsToInsert !== []) {
            $this->db->table('recipients')->ignore(true)->insertBatch($rowsToInsert);
            $summary['inserted_rows'] += count($rowsToInsert);
        }

        $insertBuffer = [];
    }
}
