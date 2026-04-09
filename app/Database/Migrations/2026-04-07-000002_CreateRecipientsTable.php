<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRecipientsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'campaign_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['PENDING', 'SENT', 'FAILED'],
                'default'    => 'PENDING',
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'retry_count' => [
                'type' => 'INT',
                'default' => 0,
            ],
            'last_attempt_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['campaign_id', 'email'], 'uq_recipients_campaign_email');
        $this->forge->addKey('campaign_id', false, false, 'idx_recipients_campaign_id');
        $this->forge->addKey('status', false, false, 'idx_recipients_status');
        $this->forge->addKey(['campaign_id', 'status'], false, false, 'idx_recipients_campaign_status');
        $this->forge->addForeignKey('campaign_id', 'campaigns', 'id', 'CASCADE', 'CASCADE', 'fk_recipients_campaign_id');
        $this->forge->createTable('recipients', true);
    }

    public function down()
    {
        $this->forge->dropTable('recipients', true);
    }
}
