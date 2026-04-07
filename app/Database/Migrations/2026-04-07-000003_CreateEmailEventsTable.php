<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmailEventsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'campaign_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'recipient_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'event_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'event_at' => [
                'type' => 'DATETIME',
            ],
            'provider_message_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 191,
                'null'       => true,
            ],
            'meta_json' => [
                'type' => 'TEXT',
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
        $this->forge->addKey('campaign_id', false, false, 'idx_email_events_campaign_id');
        $this->forge->addKey('recipient_id', false, false, 'idx_email_events_recipient_id');
        $this->forge->addKey('event_type', false, false, 'idx_email_events_event_type');
        $this->forge->addKey('event_at', false, false, 'idx_email_events_event_at');
        $this->forge->addKey(['campaign_id', 'recipient_id', 'event_type'], false, false, 'idx_email_events_campaign_recipient_type');
        $this->forge->addForeignKey('campaign_id', 'campaigns', 'id', 'CASCADE', 'CASCADE', 'fk_email_events_campaign_id');
        $this->forge->addForeignKey('recipient_id', 'recipients', 'id', 'CASCADE', 'CASCADE', 'fk_email_events_recipient_id');
        $this->forge->createTable('email_events', true);
    }

    public function down()
    {
        $this->forge->dropTable('email_events', true);
    }
}
