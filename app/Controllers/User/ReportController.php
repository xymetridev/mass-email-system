<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;

class ReportController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // Ambil statistik pengiriman milik user ini saja (dari email_queue)
        $data['daily_stats'] = $db->table('email_queue')
            ->select("DATE(email_queue.updated_at) as date, COUNT(*) as total")
            ->join('campaigns', 'campaigns.id = email_queue.campaign_id')
            ->where('campaigns.user_id', auth()->id())
            ->where('email_queue.status', 'SENT')
            ->groupBy("DATE(email_queue.updated_at)")
            ->orderBy("DATE(email_queue.updated_at)", "DESC")
            ->limit(7)
            ->get()->getResult();

        $data['pageTitle'] = 'Laporan Pengiriman Saya';
        return view('user/reports/index', $data);
    }
}