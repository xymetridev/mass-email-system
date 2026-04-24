<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CleanupDatabase extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Hapus tabel-tabel sisa yang sudah tidak digunakan
        $tablesToDrop = ['recipients', 'campaign_logs', 'delivery_logs'];
        
        foreach ($tablesToDrop as $table) {
            if ($db->tableExists($table)) {
                $this->forge->dropTable($table);
            }
        }

        // 2. Perbaikan konsistensi nama kolom di recipient_tags (recipient_id -> contact_id)
        // Ini agar konsisten dengan tabel 'contacts' yang baru
        if ($db->tableExists('recipient_tags')) {
            if ($db->fieldExists('recipient_id', 'recipient_tags') && !$db->fieldExists('contact_id', 'recipient_tags')) {
                $this->forge->modifyColumn('recipient_tags', [
                    'recipient_id' => [
                        'name'       => 'contact_id',
                        'type'       => 'INT',
                        'constraint' => 11,
                        'unsigned'   => true,
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        // Re-creating dropped tables is complex and usually not needed for a cleanup migration
    }
}
