<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateTemplatesTable extends Migration
{
    public function up()
    {
        // 1. Tambah kolom user_id
        $this->forge->addColumn('templates', [
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id'
            ],
        ]);

        // 2. Ubah campaign_id agar boleh NULL
        $this->forge->modifyColumn('templates', [
            'campaign_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);

        // 3. Rename subject -> name, body_html -> content
        $this->forge->modifyColumn('templates', [
            'subject' => [
                'name'       => 'name',
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'body_html' => [
                'name'       => 'content',
                'type'       => 'TEXT',
            ],
        ]);
        
        // 4. Tambah Index untuk User ID
        $this->db->query("ALTER TABLE templates ADD INDEX (user_id)");
    }

    public function down()
    {
        // Rollback is usually not needed for simple structural updates unless critical
    }
}
