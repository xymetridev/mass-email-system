<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $userId = auth()->id();

        // 1. Statistik Ringkas (query langsung ke email_queue, bukan recipients)
        $data['total_campaigns'] = $db->table('campaigns')
            ->where('user_id', $userId)
            ->countAllResults();

        $data['total_sent'] = $db->table('email_queue')
            ->join('campaigns', 'campaigns.id = email_queue.campaign_id')
            ->where('campaigns.user_id', $userId)
            ->where('email_queue.status', 'SENT')
            ->countAllResults();

        $data['total_failed'] = $db->table('email_queue')
            ->join('campaigns', 'campaigns.id = email_queue.campaign_id')
            ->where('campaigns.user_id', $userId)
            ->where('email_queue.status', 'FAILED')
            ->countAllResults();

        // 2. Hitung Success Rate
        $total_processed = $data['total_sent'] + $data['total_failed'];
        $data['success_rate'] = ($total_processed > 0)
            ? round(($data['total_sent'] / $total_processed) * 100, 1)
            : 0;

        // 3. Ambil 5 Kampanye Terbaru
        $data['recent_campaigns'] = $db->table('campaigns')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()->getResult();

        // 4. Data untuk Grafik (7 Hari Terakhir)
        $chartDates = [];
        $chartSent = [];
        $chartOpens = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chartDates[] = date('d M', strtotime($date));
            
            // Sent pada tanggal ini
            $sentCount = $db->table('email_queue')
                ->join('campaigns', 'campaigns.id = email_queue.campaign_id')
                ->where('campaigns.user_id', $userId)
                ->where('email_queue.status', 'SENT')
                ->where('DATE(email_queue.updated_at)', $date)
                ->countAllResults();
            $chartSent[] = $sentCount;

            // Opens pada tanggal ini
            $openCount = $db->table('tracking_logs')
                ->join('campaigns', 'campaigns.id = tracking_logs.campaign_id')
                ->where('campaigns.user_id', $userId)
                ->where('tracking_logs.event_type', 'OPEN')
                ->where('DATE(tracking_logs.created_at)', $date)
                ->countAllResults();
            $chartOpens[] = $openCount;
        }

        $data['chartDates'] = json_encode($chartDates);
        $data['chartSent'] = json_encode($chartSent);
        $data['chartOpens'] = json_encode($chartOpens);

        $data['pageTitle'] = 'Dashboard Ringkasan';

        return view('user/dashboard', $data);
    }

    public function getStats()
    {
        $db = \Config\Database::connect();
        $userId = auth()->id();

        $total_sent = $db->table('email_queue')
            ->join('campaigns', 'campaigns.id = email_queue.campaign_id')
            ->where('campaigns.user_id', $userId)
            ->where('email_queue.status', 'SENT')
            ->countAllResults();

        $server_queue = $db->table('email_queue')
            ->join('campaigns', 'campaigns.id = email_queue.campaign_id')
            ->where('campaigns.user_id', $userId)
            ->where('email_queue.status', 'PENDING')
            ->countAllResults();

        return $this->response->setJSON([
            'total_sent'   => number_format($total_sent),
            'server_queue' => number_format($server_queue)
        ]);
    }
}