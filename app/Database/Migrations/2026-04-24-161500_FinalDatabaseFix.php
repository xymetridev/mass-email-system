<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FinalDatabaseFix extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Ubah ENUM reason menjadi VARCHAR agar tidak error saat insert string baru
        $this->forge->modifyColumn('suppression_list', [
            'reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => 'UNSUBSCRIBE'
            ],
        ]);

        // 2. Perbaiki Unique Key suppression_list (Email harus unik PER USER, bukan global)
        // Kita hapus dulu index unik lama
        $db->query("ALTER TABLE suppression_list DROP INDEX email");
        // Tambahkan index unik gabungan
        $this->forge->addUniqueKey(['user_id', 'email']);
        
        // 3. Tambahkan sender_account_id ke automations agar worker tahu pengirimnya
        $this->forge->addColumn('automations', [
            'sender_account_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'trigger_tag_id'
            ],
        ]);

        // 4. Pastikan recipient_id di email_queue bisa NULL 
        // (Karena email sistem/tes mungkin tidak punya recipient_id di tabel contacts)
        $this->forge->modifyColumn('email_queue', [
            'recipient_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true
            ],
        ]);
    }

    public function down()
    {
        // No need to revert for this fix
    }
}
