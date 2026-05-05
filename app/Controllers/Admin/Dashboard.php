<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CampaignModel;
use App\Models\SenderAccountModel;
use CodeIgniter\Shield\Models\UserModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $campaignModel = new CampaignModel();
        $smtpModel = new SenderAccountModel();
        $userModel = new UserModel();

        // Cek apakah user sedang login dan wajib ganti password
        if (auth()->user()->forcePasswordReset) {
            return redirect()->to(url_to('set-password-view'))
                            ->with('error', 'Silakan buat password baru untuk keamanan akun Anda.');
        }
        
        // 1. Statistik Global Sistem
        $data['total_users']      = $userModel->countAllResults();
        $data['total_smtp']       = $smtpModel->countAllResults();
        $data['global_campaigns'] = $campaignModel->countAllResults();

        // 2. Pantauan Antrean Server (dari email_queue, bukan recipients)
        $data['server_queue'] = $db->table('email_queue')
            ->where('status', 'PENDING')
            ->countAllResults();

        // 3. Ambil Kampanye yang sedang RUNNING dari semua user
        $data['active_campaigns'] = $campaignModel
            ->select('campaigns.*, sender_accounts.sender_name')
            ->join('sender_accounts', 'sender_accounts.id = campaigns.sender_account_id')
            ->where('campaigns.status', 'RUNNING')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // 4. Statistik Performa Global
        $data['total_opens']  = $db->table('tracking_logs')->where('event_type', 'OPEN')->countAllResults();
        $data['total_clicks'] = $db->table('tracking_logs')->where('event_type', 'CLICK')->countAllResults();

        // 5. Data untuk Grafik (7 Hari Terakhir - Seluruh Sistem)
        $chartDates = [];
        $chartSent = [];
        $chartOpens = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chartDates[] = date('d M', strtotime($date));
            
            // Sent pada tanggal ini (global)
            $sentCount = $db->table('email_queue')
                ->where('status', 'SENT')
                ->where('DATE(updated_at)', $date)
                ->countAllResults();
            $chartSent[] = $sentCount;

            // Opens pada tanggal ini (global)
            $openCount = $db->table('tracking_logs')
                ->where('event_type', 'OPEN')
                ->where('DATE(created_at)', $date)
                ->countAllResults();
            $chartOpens[] = $openCount;
        }

        $data['chartDates'] = json_encode($chartDates);
        $data['chartSent'] = json_encode($chartSent);
        $data['chartOpens'] = json_encode($chartOpens);

        $data['pageTitle'] = 'Dashboard';

        return view('admin/dashboard', $data);
    }

    public function getStats()
    {
        $db = \Config\Database::connect();

        // Admin melihat antrean GLOBAL (dari email_queue)
        $server_queue = $db->table('email_queue')
            ->where('status', 'PENDING')
            ->countAllResults();

        $total_sent_today = $db->table('email_queue')
            ->where('status', 'SENT')
            ->where('DATE(updated_at)', date('Y-m-d'))
            ->countAllResults();

        return $this->response->setJSON([
            'queue'      => number_format($server_queue),
            'sent_today' => number_format($total_sent_today)
        ]);
    }
}