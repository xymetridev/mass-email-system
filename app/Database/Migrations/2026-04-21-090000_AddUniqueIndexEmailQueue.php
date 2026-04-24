<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueIndexEmailQueue extends Migration
{
    public function up()
    {
        // Cegah duplikasi email dalam satu kampanye (safety net jika double-click finalisasi)
        $this->forge->addUniqueKey(['campaign_id', 'to_email'], 'unique_campaign_to_email');

        // Perlu raw query karena addUniqueKey hanya bekerja saat createTable
        $this->db->query("ALTER TABLE email_queue ADD UNIQUE INDEX unique_campaign_to_email (campaign_id, to_email)");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE email_queue DROP INDEX unique_campaign_to_email");
    }
}
