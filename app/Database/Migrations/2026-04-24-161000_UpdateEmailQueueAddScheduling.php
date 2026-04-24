<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateEmailQueueAddScheduling extends Migration
{
    public function up()
    {
        $fields = [
            'next_attempt_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'attempt'
            ],
            'scheduled_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'next_attempt_at'
            ],
        ];
        
        $this->forge->addColumn('email_queue', $fields);
        
        // Tambahkan index agar query WHERE next_attempt_at jadi cepat
        $this->db->query("CREATE INDEX idx_next_attempt ON email_queue (next_attempt_at)");
    }

    public function down()
    {
        $this->forge->dropColumn('email_queue', ['next_attempt_at', 'scheduled_at']);
    }
}
