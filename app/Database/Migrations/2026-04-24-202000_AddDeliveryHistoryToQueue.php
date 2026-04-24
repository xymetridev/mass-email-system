<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeliveryHistoryToQueue extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        if (!$db->fieldExists('delivery_history', 'email_queue')) {
            $this->forge->addColumn('email_queue', [
                'delivery_history' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                    'after' => 'last_error'
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('email_queue', 'delivery_history');
    }
}
