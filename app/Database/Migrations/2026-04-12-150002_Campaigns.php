<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Campaigns extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'              => ['type' => 'VARCHAR', 'constraint' => 255],
            'sender_account_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'batch_size'        => ['type' => 'INT', 'constraint' => 5, 'default' => 50],
            'default_name'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'            => ['type' => 'ENUM', 'constraint' => ['DRAFT', 'RUNNING', 'PAUSED', 'COMPLETED', 'CANCELLED'], 'default' => 'DRAFT'],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('sender_account_id', 'sender_accounts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('campaigns');
    }

    public function down() { $this->forge->dropTable('campaigns'); }
}