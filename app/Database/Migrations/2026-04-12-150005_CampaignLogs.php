<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CampaignLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'campaign_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'action'      => ['type' => 'ENUM', 'constraint' => ['CREATE', 'RUN', 'PAUSE', 'RESUME', 'CANCEL', 'COMPLETED']],
            'message'     => ['type' => 'TEXT', 'null' => true],
            'user_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('campaign_id', 'campaigns', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('campaign_logs');
    }

    public function down() { $this->forge->dropTable('campaign_logs'); }
}