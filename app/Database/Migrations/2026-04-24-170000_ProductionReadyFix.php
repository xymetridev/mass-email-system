<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Production-Ready Fix Migration
 * Fixes:
 * - #7: Add missing `status` column to sender_accounts
 * - #10: Rename recipient_id -> contact_id in automation_queue for schema consistency
 * - Add `subject` column to templates table for proper automation emails
 * - Add index on email_queue(status, next_attempt_at) for worker performance
 */
class ProductionReadyFix extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // FIX #7: Add missing `status` column to sender_accounts
        if (! $db->fieldExists('status', 'sender_accounts')) {
            $this->forge->addColumn('sender_accounts', [
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['ACTIVE', 'PAUSED'],
                    'default'    => 'ACTIVE',
                    'after'      => 'is_active',
                ],
            ]);
        }

        // FIX #8: Add `subject` column to templates for proper automation email subjects
        if (! $db->fieldExists('subject', 'templates')) {
            $this->forge->addColumn('templates', [
                'subject' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'name',
                ],
            ]);
        }

        // FIX #10: Add contact_id column to automation_queue (consistent with contacts table)
        // recipient_id was referencing the old `recipients` table
        if (! $db->fieldExists('contact_id', 'automation_queue')) {
            $this->forge->addColumn('automation_queue', [
                'contact_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'recipient_id',
                ],
            ]);

            // Migrate existing data: copy recipient_id -> contact_id
            $db->query("UPDATE automation_queue SET contact_id = recipient_id WHERE contact_id IS NULL");
        }

        // PERFORMANCE: Add composite index for worker's main fetch query
        // Prevents full table scan on large email_queue tables
        try {
            $db->query("ALTER TABLE email_queue ADD INDEX idx_worker_fetch (status, next_attempt_at, id)");
        } catch (\Exception $e) {
            // Index might already exist, ignore
        }

        // PERFORMANCE: Index on automation_queue for worker
        try {
            $db->query("ALTER TABLE automation_queue ADD INDEX idx_auto_fetch (status, next_run_at)");
        } catch (\Exception $e) {
            // Index might already exist, ignore
        }

        // FIX: Add UNSUBSCRIBE to tracking_logs.event_type ENUM
        // Original only had ['OPEN', 'CLICK'] — we need UNSUBSCRIBE for correct tracking
        $db->query("
            ALTER TABLE tracking_logs
            MODIFY COLUMN event_type ENUM('OPEN', 'CLICK', 'UNSUBSCRIBE') NOT NULL DEFAULT 'OPEN'
        ");

        // FIX: Add PROCESSING and FAILED to automation_queue.status ENUM
        // Original only had ['PENDING', 'COMPLETED'] — worker now uses PROCESSING for atomic locking
        $db->query("
            ALTER TABLE automation_queue
            MODIFY COLUMN status ENUM('PENDING', 'PROCESSING', 'COMPLETED', 'FAILED') NOT NULL DEFAULT 'PENDING'
        ");
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if ($db->fieldExists('status', 'sender_accounts')) {
            $this->forge->dropColumn('sender_accounts', 'status');
        }
        if ($db->fieldExists('subject', 'templates')) {
            $this->forge->dropColumn('templates', 'subject');
        }
        if ($db->fieldExists('contact_id', 'automation_queue')) {
            $this->forge->dropColumn('automation_queue', 'contact_id');
        }
    }
}
