<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeliveryOperationalColumns extends Migration
{
    public function up()
    {
        $campaigns = $this->db->getFieldData('campaigns');
        $campaignFields = array_map(static fn ($field) => $field->name, $campaigns);

        if (! in_array('sender_account_id', $campaignFields, true)) {
            $this->forge->addColumn('campaigns', [
                'sender_account_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                    'after'    => 'user_id',
                ],
            ]);
            $this->db->query('ALTER TABLE campaigns ADD INDEX idx_campaigns_sender_account_id (sender_account_id)');
            $this->db->query('ALTER TABLE campaigns ADD CONSTRAINT fk_campaigns_sender_account_id FOREIGN KEY (sender_account_id) REFERENCES sender_accounts(id) ON DELETE SET NULL ON UPDATE CASCADE');
        }

        if (! in_array('default_name', $campaignFields, true)) {
            $this->forge->addColumn('campaigns', [
                'default_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                    'after'      => 'batch_size',
                ],
            ]);
        }

        if (! in_array('completed_at', $campaignFields, true)) {
            $this->forge->addColumn('campaigns', [
                'completed_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'updated_at',
                ],
            ]);
        }

        $recipients = $this->db->getFieldData('recipients');
        $recipientFields = array_map(static fn ($field) => $field->name, $recipients);

        if (! in_array('claimed_by', $recipientFields, true)) {
            $this->forge->addColumn('recipients', [
                'claimed_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                    'null'       => true,
                    'after'      => 'status',
                ],
            ]);
            $this->db->query('ALTER TABLE recipients ADD INDEX idx_recipients_claimed_by (claimed_by)');
        }

        if (! in_array('claimed_at', $recipientFields, true)) {
            $this->forge->addColumn('recipients', [
                'claimed_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'claimed_by',
                ],
            ]);
            $this->db->query('ALTER TABLE recipients ADD INDEX idx_recipients_claimed_at (claimed_at)');
        }

        if (! in_array('last_error', $recipientFields, true)) {
            $this->forge->addColumn('recipients', [
                'last_error' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'retry_count',
                ],
            ]);
        }
    }

    public function down()
    {
        $campaigns = $this->db->getFieldData('campaigns');
        $campaignFields = array_map(static fn ($field) => $field->name, $campaigns);

        foreach (['completed_at', 'default_name', 'sender_account_id'] as $field) {
            if (in_array($field, $campaignFields, true)) {
                $this->forge->dropColumn('campaigns', $field);
            }
        }

        $recipients = $this->db->getFieldData('recipients');
        $recipientFields = array_map(static fn ($field) => $field->name, $recipients);

        foreach (['last_error', 'claimed_at', 'claimed_by'] as $field) {
            if (in_array($field, $recipientFields, true)) {
                $this->forge->dropColumn('recipients', $field);
            }
        }
    }
}
