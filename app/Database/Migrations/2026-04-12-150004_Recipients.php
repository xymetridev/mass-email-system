<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Recipients extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'campaign_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'email'              => ['type' => 'VARCHAR', 'constraint' => 255],
            'name'               => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'             => ['type' => 'ENUM', 'constraint' => ['PENDING', 'SENT', 'FAILED'], 'default' => 'PENDING'],
            'retry_count'        => ['type' => 'INT', 'constraint' => 2, 'default' => 0],
            'last_error_message' => ['type' => 'TEXT', 'null' => true],
            'sent_at'            => ['type' => 'DATETIME', 'null' => true],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        
        // Composite Index untuk optimasi kecepatan query Cronjob
        $this->forge->addKey(['campaign_id', 'status'], false, false, 'idx_campaign_status');
        
        // Unique Index untuk Idempotency (Anti email ganda dalam 1 campaign)
        $this->forge->addUniqueKey(['campaign_id', 'email'], 'unique_campaign_email');
        
        $this->forge->addForeignKey('campaign_id', 'campaigns', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('recipients');
    }

    public function down() { $this->forge->dropTable('recipients'); }
}