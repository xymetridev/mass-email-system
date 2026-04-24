<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContactsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('contacts')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'email'      => ['type' => 'VARCHAR', 'constraint' => 255],
                'name'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['user_id', 'email']);
            $this->forge->createTable('contacts');
        }

        // Update recipient_tags to use contact_id instead of recipient_id
        if ($db->tableExists('recipient_tags') && $db->fieldExists('recipient_id', 'recipient_tags')) {
            $this->db->query("ALTER TABLE recipient_tags CHANGE recipient_id contact_id INT(11) UNSIGNED");
        }
    }

    public function down()
    {
        $this->forge->dropTable('contacts');
    }
}
