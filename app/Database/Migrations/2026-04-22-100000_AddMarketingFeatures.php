<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMarketingFeatures extends Migration
{
    public function up()
    {
        // 1. Tabel Blacklist / Suppression List (Untuk Unsubscribe & Bounce)
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'reason'     => ['type' => 'ENUM', 'constraint' => ['UNSUBSCRIBE', 'BOUNCE', 'SPAM_COMPLAINT'], 'default' => 'UNSUBSCRIBE'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('suppression_list');

        // 2. Tabel Tracking Logs (Untuk Open & Click Tracking)
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'email_queue_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'campaign_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'event_type'     => ['type' => 'ENUM', 'constraint' => ['OPEN', 'CLICK'], 'default' => 'OPEN'],
            'url'            => ['type' => 'TEXT', 'null' => true], // Hanya diisi jika event = CLICK
            'ip_address'     => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['campaign_id', 'event_type']);
        $this->forge->addKey('email_queue_id');
        $this->forge->createTable('tracking_logs');

        // 3. Tambahan kolom untuk Warm-up Mode di sender_accounts
        $this->forge->addColumn('sender_accounts', [
            'warmup_mode'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'is_active'],
            'warmup_daily_limit'=> ['type' => 'INT', 'constraint' => 11, 'default' => 50, 'after' => 'warmup_mode'],
            'warmup_sent_today' => ['type' => 'INT', 'constraint' => 11, 'default' => 0, 'after' => 'warmup_daily_limit'],
            'warmup_last_date'  => ['type' => 'DATE', 'null' => true, 'after' => 'warmup_sent_today']
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('suppression_list');
        $this->forge->dropTable('tracking_logs');
        $this->forge->dropColumn('sender_accounts', ['warmup_mode', 'warmup_daily_limit', 'warmup_sent_today', 'warmup_last_date']);
    }
}
