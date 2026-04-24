<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTurboIndexes extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Index untuk Audit Trail (Sering di-filter berdasarkan user dan waktu)
        try {
            $db->query("ALTER TABLE activity_logs ADD INDEX idx_user_created (user_id, created_at)");
        } catch (\Exception $e) {}

        // 2. Index untuk Dashboard Stats (Sangat krusial untuk hitung SENT/FAILED per kampanye)
        try {
            $db->query("ALTER TABLE email_queue ADD INDEX idx_campaign_status (campaign_id, status)");
        } catch (\Exception $e) {}

        // 3. Index untuk Segmentasi (Sering di-filter berdasarkan Tag ID di Wizard Step 2)
        try {
            $db->query("ALTER TABLE recipient_tags ADD INDEX idx_tag_only (tag_id)");
        } catch (\Exception $e) {}

        // 4. Index untuk Suppression List (Cepat dalam mengecek email terlarang per user)
        try {
            $db->query("ALTER TABLE suppression_list ADD INDEX idx_user_email (user_id, email)");
        } catch (\Exception $e) {}
        
        // 5. Index untuk Automation (Mempercepat trigger berdasarkan Tag)
        try {
            $db->query("ALTER TABLE automations ADD INDEX idx_trigger_tag (trigger_tag_id)");
        } catch (\Exception $e) {}
    }

    public function down()
    {
        $db = \Config\Database::connect();
        try { $db->query("ALTER TABLE activity_logs DROP INDEX idx_user_created"); } catch (\Exception $e) {}
        try { $db->query("ALTER TABLE email_queue DROP INDEX idx_campaign_status"); } catch (\Exception $e) {}
        try { $db->query("ALTER TABLE recipient_tags DROP INDEX idx_tag_only"); } catch (\Exception $e) {}
        try { $db->query("ALTER TABLE suppression_list DROP INDEX idx_user_email"); } catch (\Exception $e) {}
        try { $db->query("ALTER TABLE automations DROP INDEX idx_trigger_tag"); } catch (\Exception $e) {}
    }
}
