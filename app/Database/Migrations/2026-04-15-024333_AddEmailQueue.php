<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailQueue extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'campaign_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'recipient_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'sender_account_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'to_email'           => ['type' => 'VARCHAR', 'constraint' => 255],
            'subject'            => ['type' => 'VARCHAR', 'constraint' => 255],
            'body'               => ['type' => 'TEXT'],
            'status'             => ['type' => 'ENUM', 'constraint' => ['PENDING', 'PROCESSING', 'SENT', 'FAILED'], 'default' => 'PENDING'],
            'attempt'            => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'last_error'         => ['type' => 'TEXT', 'null' => true],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['status', 'campaign_id']); // Index untuk kecepatan query
        $this->forge->createTable('email_queue');
    }

    public function down() { $this->forge->dropTable('email_queue'); }
}
