<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CronRun extends BaseCommand
{
    protected $group       = 'MailCore';
    protected $name        = 'email:run'; // Nama perintah baru
    protected $description = 'Satpam pengaktif jadwal kampanye.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        // Cari kampanye SCHEDULED yang sudah waktunya dikirim (maks 10 per eksekusi)
        $campaigns = $db->table('campaigns')
                        ->where('status', 'SCHEDULED')
                        ->where('scheduled_at <=', $now)
                        ->limit(10)
                        ->get()->getResult();

        if (empty($campaigns)) {
            CLI::write("Tidak ada jadwal kampanye saat ini.", 'white');
            return;
        }

        foreach ($campaigns as $campaign) {
            $db->table('campaigns')
               ->where('id', $campaign->id)
               ->update(['status' => 'RUNNING', 'updated_at' => $now]);

            CLI::write("Kampanye [{$campaign->name}] sekarang RUNNING!", 'green');
        }
    }
}