<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NextUpdateCampaignsTable extends Migration
{
    public function up()
    {
        $fields = [
            'contacts_json' => [
                'type'       => 'LONGTEXT',
                'null'       => true,
                'after'      => 'content',
            ],
        ];
        $this->forge->addColumn('campaigns', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('campaigns', 'contacts_json');
    }
}
