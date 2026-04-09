<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSenderAccountsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'sender_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'sender_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'smtp_host' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'smtp_port' => [
                'type' => 'INT',
            ],
            'smtp_user' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'smtp_pass' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'encryption' => [
                'type'       => 'ENUM',
                'constraint' => ['tls', 'ssl', 'none'],
                'default'    => 'tls',
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
        $this->forge->addKey('user_id');
        $this->forge->addKey('sender_email');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sender_accounts', true);
    }

    public function down()
    {
        $this->forge->dropTable('sender_accounts', true);
    }
}
