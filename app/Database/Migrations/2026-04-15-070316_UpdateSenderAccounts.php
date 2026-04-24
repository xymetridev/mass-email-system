<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateSenderAccounts extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Ubah user_id menjadi NULLABLE agar Admin bisa buat akun Bisnis (user_id = NULL)
        $this->forge->modifyColumn('sender_accounts', [
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true, // Diubah jadi NULLABLE
            ],
        ]);

        // 2. Tambahkan kolom tambahan yang dibutuhkan
        $fields = [];
        
        if (! $db->fieldExists('type', 'sender_accounts')) {
            $fields['type'] = [
                'type'       => 'ENUM',
                'constraint' => ['BUSINESS', 'INDIVIDUAL'],
                'default'    => 'INDIVIDUAL',
                'after'      => 'user_id'
            ];
        }
        
        if (! $db->fieldExists('is_active', 'sender_accounts')) {
            $fields['is_active'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'encryption'
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('sender_accounts', $fields);
        }
    }

    public function down()
    {
        // Jika rollback, kembalikan ke NOT NULL (Opsional)
        $this->forge->modifyColumn('sender_accounts', [
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);
        $this->forge->dropColumn('sender_accounts', ['type', 'is_active']);
    }
}
