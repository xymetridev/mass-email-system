<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SenderAccounts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'sender_name'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'sender_email'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'smtp_host'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'smtp_port'      => ['type' => 'INT', 'constraint' => 5, 'default' => 587],
            'smtp_username'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'smtp_password'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'encryption'     => ['type' => 'ENUM', 'constraint' => ['SSL', 'TLS', 'None'], 'default' => 'TLS'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sender_accounts');
    }

    public function down() { $this->forge->dropTable('sender_accounts'); }
}