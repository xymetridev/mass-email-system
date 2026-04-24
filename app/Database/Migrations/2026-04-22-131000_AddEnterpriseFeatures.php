<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEnterpriseFeatures extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Update sender_accounts for Hourly Throttling
        $fields = [];
        if (!$db->fieldExists('hourly_limit', 'sender_accounts')) {
            $fields['hourly_limit'] = ['type' => 'INT', 'constraint' => 11, 'default' => 0];
        }
        if (!$db->fieldExists('sent_this_hour', 'sender_accounts')) {
            $fields['sent_this_hour'] = ['type' => 'INT', 'constraint' => 11, 'default' => 0];
        }
        if (!$db->fieldExists('last_hour_reset', 'sender_accounts')) {
            $fields['last_hour_reset'] = ['type' => 'DATETIME', 'null' => true];
        }
        if (!$db->fieldExists('imap_host', 'sender_accounts')) {
            $fields['imap_host'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true];
        }
        if (!$db->fieldExists('imap_port', 'sender_accounts')) {
            $fields['imap_port'] = ['type' => 'INT', 'constraint' => 11, 'null' => true];
        }
        if (!$db->fieldExists('imap_encryption', 'sender_accounts')) {
            $fields['imap_encryption'] = ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true];
        }
        
        if (!empty($fields)) {
            $this->forge->addColumn('sender_accounts', $fields);
        }

        // 2. Table for Recipient Tags (Segmentation)
        if (!$db->tableExists('tags')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('tags');
        }

        // 3. Pivot table for Recipient Tags
        if (!$db->tableExists('recipient_tags')) {
            $this->forge->addField([
                'recipient_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'tag_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            ]);
            $this->forge->addKey(['recipient_id', 'tag_id']);
            $this->forge->createTable('recipient_tags');
        }

        // 4. Table for Automations (Sequences)
        if (!$db->tableExists('automations')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'name'       => ['type' => 'VARCHAR', 'constraint' => 255],
                'trigger_tag_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'status'     => ['type' => 'ENUM', 'constraint' => ['ACTIVE', 'PAUSED'], 'default' => 'PAUSED'],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('automations');
        }

        // 5. Table for Automation Steps
        if (!$db->tableExists('automation_steps')) {
            $this->forge->addField([
                'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'automation_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'step_order'    => ['type' => 'INT', 'constraint' => 11],
                'template_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'delay_days'    => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('automation_steps');
        }

        // 6. Automation Queue (Tracking who is where)
        if (!$db->tableExists('automation_queue')) {
            $this->forge->addField([
                'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'automation_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'recipient_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'current_step_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'next_run_at'   => ['type' => 'DATETIME', 'null' => true],
                'status'        => ['type' => 'ENUM', 'constraint' => ['PENDING', 'COMPLETED'], 'default' => 'PENDING'],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('automation_queue');
        }
        
        // 7. Update tracking_logs
        if (!$db->fieldExists('device', 'tracking_logs')) {
            $this->forge->addColumn('tracking_logs', [
                'device' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            ]);
        }

        // 8. Update suppression_list
        if (!$db->fieldExists('user_id', 'suppression_list')) {
            $this->forge->addColumn('suppression_list', [
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'after' => 'id'],
            ]);
        }
    }

    public function down()
    {
        // Drop all created tables
    }
}
