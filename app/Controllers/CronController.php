<?php

namespace App\Controllers;

/**
 * DEPRECATED: File ini adalah versi lama engine pengiriman email.
 * 
 * Flow pengiriman sekarang ditangani oleh:
 * - app/Commands/CronRun.php   → Mengaktifkan kampanye terjadwal (SCHEDULED → RUNNING)
 * - app/Commands/EmailWorker.php → Mengirim email dari tabel email_queue
 * 
 * File ini dipertahankan sebagai referensi arsip.
 * JANGAN gunakan atau panggil controller ini di route manapun.
 */
class CronController extends \CodeIgniter\Controller
{
    // Intentionally left empty — see Commands/EmailWorker.php & Commands/CronRun.php
}