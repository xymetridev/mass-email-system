<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCampaignsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'sender_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'sender_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'reply_to_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'body_html' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'body_text' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'draft',
            ],
            'scheduled_at' => [
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
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('name', 'uq_campaigns_name');
        $this->forge->addKey('status', false, false, 'idx_campaigns_status');
        $this->forge->addKey('scheduled_at', false, false, 'idx_campaigns_scheduled_at');
        $this->forge->createTable('campaigns', true);
    }

    public function down()
    {
        $this->forge->dropTable('campaigns', true);
    }
}
