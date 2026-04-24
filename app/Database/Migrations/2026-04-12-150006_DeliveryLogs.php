<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DeliveryLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'recipient_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'error_message'  => ['type' => 'TEXT', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('recipient_id', 'recipients', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('delivery_logs');
    }

    public function down() { $this->forge->dropTable('delivery_logs'); }
}