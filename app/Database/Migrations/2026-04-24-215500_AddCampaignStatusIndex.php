<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCampaignStatusIndex extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Menambah index untuk kolom status dan scheduled_at
        // Agar polling jadwal kampanye jadi sangat ringan (O(log n) bukan O(n))
        try {
            $db->query("ALTER TABLE campaigns ADD INDEX idx_status_schedule (status, scheduled_at)");
        } catch (\Exception $e) {
            // Index mungkin sudah ada jika dijalankan manual
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        try {
            $db->query("ALTER TABLE campaigns DROP INDEX idx_status_schedule");
        } catch (\Exception $e) {
            // Index mungkin tidak ada
        }
    }
}
