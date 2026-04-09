<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCampaignsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
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
                'null'       => true,
            ],
            'sender_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
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
                'type' => 'ENUM',
                'constraint' => ['DRAFT', 'READY', 'RUNNING', 'PAUSED', 'CANCELED', 'COMPLETED', 'FAILED'],
                'default' => 'DRAFT',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'batch_size' => [
                'type' => 'INT',
                'default' => 100,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addUniqueKey(['user_id', 'name'], 'uq_campaigns_user_name');
        $this->forge->addForeignKey('user_id','users','id','CASCADE','CASCADE');
        $this->forge->addKey('status', false, false, 'idx_campaigns_status');
        $this->forge->createTable('campaigns', true);
    }

    public function down()
    {
        $this->forge->dropTable('campaigns', true);
    }
}
