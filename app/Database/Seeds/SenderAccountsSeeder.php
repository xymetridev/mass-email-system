<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SenderAccountsSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'user_id'        => 1,
                'sender_name'    => 'System Admin',
                'sender_email'   => 'sandbox.smtp.mailtrap.io',
                'smtp_host'      => 'sandbox.smtp.mailtrap.io',
                'smtp_port'      => 2525,
                'smtp_username'  => '93ee224d8ba6c8',
                'smtp_password'  => '63550aff56d4df',
                'encryption'     => 'TLS',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert batch
        $this->db->table('sender_accounts')->insertBatch($data);
    }
}