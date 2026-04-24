<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateCampaignsTable extends Migration
{
    public function up()
    {
        $fields = [
            // Tambah kolom baru
            'subject'      => ['type' => 'VARCHAR', 'constraint' => 255, 'after' => 'name'],
            'content'      => ['type' => 'LONGTEXT', 'null' => true, 'after' => 'subject'],
            'scheduled_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'status'],
            'total_targets'=> ['type' => 'INT', 'default' => 0, 'after' => 'scheduled_at'],
        ];
        $this->forge->addColumn('campaigns', $fields);

        // Hapus batch_size karena throttling sudah ditangani Worker usleep
        $this->forge->dropColumn('campaigns', 'batch_size');

        // Update Enum Status
        $this->db->query("ALTER TABLE campaigns MODIFY COLUMN status ENUM('DRAFT','SCHEDULED','READY','RUNNING','PAUSED','COMPLETED','CANCELLED') DEFAULT 'DRAFT'");
    }

    public function down()
    {
        $this->forge->dropColumn('campaigns', ['subject', 'content', 'scheduled_at', 'total_targets']);
        $this->forge->addColumn('campaigns', [
            'batch_size' => ['type' => 'INT', 'default' => 50]
        ]);
    }
}