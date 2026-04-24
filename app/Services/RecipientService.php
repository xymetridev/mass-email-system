<?php

namespace App\Services;

use App\Models\RecipientModel;

class RecipientService
{
    /**
     * Memproses file TXT dengan memori yang efisien (Line-by-line streaming)
     */
    public function processTxtUpload($filePath, $campaignId)
    {
        $file = fopen($filePath, 'r');
        if (!$file) {
            throw new \Exception("Gagal membuka file upload.");
        }

        $recipientModel = new RecipientModel();
        
        $batchData = [];
        $uniqueEmails = []; // Mencegah duplikat di dalam file yang sama
        $batchSizeLimit = 500; // Insert ke DB per 500 baris
        
        $stats = [
            'inserted' => 0,
            'duplicates' => 0,
            'invalid' => 0
        ];

        while (($line = fgets($file)) !== false) {
            $line = trim($line);
            if (empty($line)) continue;

            // Format ekspektasi: email@domain.com,Nama Penerima (Nama opsional)
            $parts = explode(',', $line, 2);
            $email = trim($parts[0]);
            $name  = isset($parts[1]) ? trim($parts[1]) : null;

            // 1. Validasi Format Email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $stats['invalid']++;
                continue;
            }

            // 2. Validasi Duplikat (Memory checking)
            if (isset($uniqueEmails[$email])) {
                $stats['duplicates']++;
                continue;
            }

            // Tandai email sudah ada
            $uniqueEmails[$email] = true;

            // 3. Masukkan ke array antrean Batch
            $batchData[] = [
                'campaign_id' => $campaignId,
                'email'       => $email,
                'name'        => $name,
                'status'      => 'PENDING'
            ];

            // 4. Jika antrean sudah mencapai limit, eksekusi Insert Batch lalu kosongkan antrean
            if (count($batchData) >= $batchSizeLimit) {
                $recipientModel->insertBatch($batchData);
                $stats['inserted'] += count($batchData);
                $batchData = []; // Reset memory
            }
        }

        // 5. Insert sisa data yang kurang dari 500 baris
        if (!empty($batchData)) {
            $recipientModel->insertBatch($batchData);
            $stats['inserted'] += count($batchData);
        }

        fclose($file);

        return $stats;
    }
}