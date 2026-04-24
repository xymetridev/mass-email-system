<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ReportController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // Ambil statistik per hari untuk 7 hari terakhir (dari email_queue)
        $data['daily_stats'] = $db->query("
            SELECT DATE(updated_at) as date, 
                COUNT(*) as total 
            FROM email_queue 
            WHERE status = 'SENT' 
            GROUP BY DATE(updated_at) 
            ORDER BY DATE(updated_at) DESC LIMIT 7
        ")->getResult();

        $data['pageTitle'] = 'Laporan Pengiriman Global';
        return view('admin/reports/index', $data);
    }
}
