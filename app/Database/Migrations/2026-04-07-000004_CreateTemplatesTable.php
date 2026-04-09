<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTemplatesTable extends Migration
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
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'body_html' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'body_text' => [
                'type' => 'LONGTEXT',
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
        $this->forge->addUniqueKey('campaign_id', 'uq_templates_campaign');
        $this->forge->addForeignKey('campaign_id', 'campaigns', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('templates', true);
    }

    public function down()
    {
        $this->forge->dropTable('templates', true);
    }
}
