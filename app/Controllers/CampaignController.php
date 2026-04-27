<?php

namespace App\Controllers;

class CampaignController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('campaigns');

        $model = new \App\Models\CampaignModel();
        
        // Filter untuk user biasa
        if (!auth()->user()->inGroup('admin')) {
            $builder->where('campaigns.user_id', auth()->id());
        }

        // KUNCI FIX: Gunakan getResultArray()
        $campaigns = $builder->select('campaigns.*, sender_accounts.sender_name, sender_accounts.sender_email')
            ->join('sender_accounts', 'sender_accounts.id = campaigns.sender_account_id', 'left')
            ->orderBy('campaigns.updated_at', 'DESC')
            ->get()->getResultArray();

        // FIX #6: Single aggregate query menggantikan N+1 (4 query per baris kampanye)
        // Sebelumnya: 50 kampanye = 200+ extra queries. Sekarang: 1 query total.
        $statsRaw = $db->query("
            SELECT
                campaign_id,
                COUNT(*) AS total_targets,
                SUM(status = 'SENT') AS total_sent,
                SUM(status = 'FAILED') AS total_failed,
                SUM(status IN ('PENDING','PROCESSING')) AS total_pending
            FROM email_queue
            GROUP BY campaign_id
        ")->getResultArray();

        // Buat lookup array berdasarkan campaign_id untuk akses O(1)
        $stats = [];
        foreach ($statsRaw as $s) {
            $stats[$s['campaign_id']] = $s;
        }

        foreach ($campaigns as &$c) {
            $s = $stats[$c['id']] ?? ['total_targets' => 0, 'total_sent' => 0, 'total_failed' => 0, 'total_pending' => 0];

            $c['total_targets'] = (int) $s['total_targets'];
            $c['total_sent']    = (int) $s['total_sent'];
            $c['total_failed']  = (int) $s['total_failed'];

            $divisor = $c['total_targets'] > 0 ? $c['total_targets'] : 1;
            $c['progress_percent'] = min(100, round((($c['total_sent'] + $c['total_failed']) / $divisor) * 100));
        }
        unset($c); // Putuskan referensi setelah loop

        $data = [
            'pageTitle'  => 'Manajemen Kampanye',
            'activeMenu' => 'campaigns',
            'campaigns'  => $campaigns,
            'pager'      => $model->pager
        ];

        if ($this->request->isAJAX()) {
            return view('campaigns/_table', $data);
        }

        return view('campaigns/index', $data);
    }

    // PINTU MASUK EDIT WIZARD
    public function editDraft($id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('campaigns')->where('id', $id);

        // Security check
        if (!auth()->user()->inGroup('admin')) {
            $builder->where('user_id', auth()->id());
        }

        $campaign = $builder->get()->getRowArray();

        if (!$campaign) {
            return redirect()->to(url_to('app.campaigns'))->with('error', 'Akses ditolak: Kampanye tidak ditemukan atau bukan milik Anda.');
        }

        // BEST PRACTICE: Tangkap request step (Jika tidak ada, kembalikan ke step 1)
        $targetStep = $this->request->getGet('step') ?? 1;

        // Simpan memori status lama sebelum diubah
        $wizardData = [
            'campaign_id'           => $id,
            'original_status'       => $campaign['status'],
            'original_scheduled_at' => $campaign['scheduled_at']
        ];

        // Ubah ke DRAFT agar aman dari eksekusi Cron Job/Worker saat diedit
        if ($campaign['status'] === 'SCHEDULED') {
            $db->table('campaigns')->where('id', $id)->update([
                'status' => 'DRAFT', 
                'scheduled_at' => null
            ]);
        }

        // Tanam session baru
        session()->remove('campaign_wizard');
        session()->set('campaign_wizard', $wizardData);

        // Lempar ke Wizard sesuai step yang diklik user (Bukan selalu ke 1)
        return redirect()->to(url_to('app.campaigns.wizard', $targetStep));
    }

    // FITUR BARU: DUPLICATE
    public function duplicate($id)
    {
        $db = \Config\Database::connect();
        $old = $db->table('campaigns')->where('id', $id)->where('user_id', auth()->id())->get()->getRowArray();

        if (!$old) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        unset($old['id']);
        $old['name']          = $old['name'] . ' (Copy)';
        $old['status']        = 'DRAFT';
        $old['scheduled_at']  = null;
        $old['total_targets'] = 0;
        $old['created_at']    = date('Y-m-d H:i:s');
        $old['updated_at']    = date('Y-m-d H:i:s');

        $db->table('campaigns')->insert($old);
        $newId = $db->insertID();

        record_activity('DUPLICATE_CAMPAIGN', "Menduplikat kampanye ID #$id menjadi kampanye baru: " . $old['name']);

        return redirect()->to(url_to('app.campaigns.edit_draft', $newId))->with('success', 'Kampanye berhasil diduplikat.');
    }

    // UPDATE STATUS (PAUSE, RESUME, CANCEL)
    public function updateStatus($id, $status)
    {
        $db = \Config\Database::connect();
        $validStatuses = ['RUNNING', 'PAUSED', 'CANCELLED'];

        if (!in_array($status, $validStatuses)) return redirect()->back()->with('error', 'Status tidak valid.');

        $campaign = $db->table('campaigns')->where('id', $id)->where('user_id', auth()->id())->get()->getRowArray();
        if (!$campaign) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        // VALIDASI KRUSIAL: Jika dari DRAFT ingin ke RUNNING
        if ($status === 'RUNNING' && $campaign['status'] === 'DRAFT') {
            // Cek apakah antrean email sudah di-generate (lewat Wizard Step 5)
            $queueCount = $db->table('email_queue')->where('campaign_id', $id)->countAllResults();
            
            if ($queueCount === 0) {
                return redirect()->to(url_to('app.campaigns.edit_draft', $id) . '?step=5')
                    ->with('error', 'Kampanye belum siap dijalankan. Harap selesaikan langkah terakhir di Wizard untuk memproses antrean email.');
            }
        }

        $db->table('campaigns')->where('id', $id)->where('user_id', auth()->id())->update(['status' => $status]);
        
        $msg = ($status == 'RUNNING') ? 'dijalankan kembali.' : (($status == 'PAUSED') ? 'dihentikan sejenak.' : 'dibatalkan.');
        
        record_activity('UPDATE_CAMPAIGN_STATUS', "Mengubah status kampanye '" . $campaign['name'] . "' menjadi $status.");

        return redirect()->back()->with('success', "Kampanye berhasil $msg");
    }

    // HAPUS KAMPANYE (Hanya bisa dihapus jika belum dikirim atau dibatalkan)
    public function delete($id)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('campaigns')->where('id', $id);
        if (!auth()->user()->inGroup('admin')) {
            $builder->where('user_id', auth()->id());
        }
        
        $campaign = $builder->get()->getRowArray();

        // FIX #9: Guard null agar tidak PHP Fatal Error saat akses array pada null
        if (! $campaign) {
            return redirect()->back()->with('error', 'Akses ditolak: Kampanye tidak ditemukan atau bukan milik Anda.');
        }

        if ($campaign['status'] === 'RUNNING') {
            return redirect()->back()->with('error', 'Kampanye yang sedang berjalan tidak bisa dihapus. Silakan hentikan (Pause/Cancel) terlebih dahulu.');
        }

        // 1. Hapus data relasi (Cleanup)
        $db->table('email_queue')->where('campaign_id', $id)->delete();
        $db->table('tracking_logs')->where('campaign_id', $id)->delete();

        // 2. Hapus kampanye utama
        $db->table('campaigns')->where('id', $id)->delete();
        
        record_activity('DELETE_CAMPAIGN', "Menghapus kampanye '" . $campaign['name'] . "' beserta seluruh antrean dan log-nya.");

        return redirect()->back()->with('success', 'Kampanye berhasil dihapus beserta seluruh datanya.');
    }

    // HALAMAN LOG DETAIL
    public function show($id)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('campaigns')
            ->select('campaigns.*, sender_accounts.sender_name, sender_accounts.sender_email')
            ->join('sender_accounts', 'sender_accounts.id = campaigns.sender_account_id', 'left')
            ->where('campaigns.id', $id);
            
        // BEST PRACTICE: Keamanan (Security Restriction)
        // Pastikan user biasa hanya bisa melihat kampanye miliknya sendiri
        if (!auth()->user()->inGroup('admin')) {
            $builder->where('campaigns.user_id', auth()->id());
        }

        $campaign = $builder->get()->getRowArray();

        // Jika kampanye tidak ada atau bukan miliknya, tendang kembali ke index
        if (!$campaign) {
            return redirect()->to(url_to('app.campaigns'))->with('error', 'Akses Ditolak: Data tidak ditemukan atau bukan milik Anda.');
        }

        // Hitung Statistik Real-time dari tabel email_queue
        $total   = $db->table('email_queue')->where('campaign_id', $id)->countAllResults();
        $sent    = $db->table('email_queue')->where(['campaign_id' => $id, 'status' => 'SENT'])->countAllResults();
        $failed  = $db->table('email_queue')->where(['campaign_id' => $id, 'status' => 'FAILED'])->countAllResults();
        $pending = $db->table('email_queue')->where(['campaign_id' => $id, 'status' => 'PENDING'])->countAllResults();
        
        // Dapatkan Tracking Statistik
        $opens  = $db->table('tracking_logs')->where(['campaign_id' => $id, 'event_type' => 'OPEN'])->countAllResults();
        $clicks = $db->table('tracking_logs')->where(['campaign_id' => $id, 'event_type' => 'CLICK'])->countAllResults();
        
        // Kalkulasi Persentase Progres (Aman dari Division by Zero)
        $divisor = $total > 0 ? $total : 1;
        $progress_percent = round((($sent + $failed) / $divisor) * 100);
        if ($progress_percent > 100) $progress_percent = 100; // Safety net agar maksimal 100%

        // Pagination untuk Log Antrean
        $pager   = \Config\Services::pager();
        $page    = $this->request->getVar('page') ?? 1;
        $perPage = 50; // Tampilkan 50 antrean per halaman
        $offset  = ($page - 1) * $perPage;

        $recipients = $db->table('email_queue q')
            ->select('q.*, 
                (SELECT COUNT(*) FROM tracking_logs WHERE email_queue_id = q.id AND event_type = "OPEN") as opens_count,
                (SELECT COUNT(*) FROM tracking_logs WHERE email_queue_id = q.id AND event_type = "CLICK") as clicks_count,
                (SELECT MAX(created_at) FROM tracking_logs WHERE email_queue_id = q.id) as last_track')
            ->where('q.campaign_id', $id)
            ->orderBy('q.id', 'ASC')
            ->limit($perPage, $offset)
            ->get()->getResultArray();

        // Kirim semua variabel yang dibutuhkan View
        return view('campaigns/show', [
            'pageTitle'        => 'Detail: ' . $campaign['name'],
            'campaign'         => $campaign,
            'recipients'       => $recipients,
            'total'            => $total,
            'sent'             => $sent,
            'failed'           => $failed,
            'pending'          => $pending,
            'opens'            => $opens,
            'clicks'           => $clicks,
            'progress_percent' => $progress_percent,
            'pager'            => $pager->makeLinks($page, $perPage, $total, 'default_full')
        ]);
    }

    // AUTO REFRESH STATUS UTK INDEX
    public function checkStatuses()
    {   
        if (!auth()->loggedIn()) return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        
        $db = \Config\Database::connect();
        $builder = $db->table('campaigns')->select('id, status, total_targets');
        if (!auth()->user()->inGroup('admin')) $builder->where('user_id', auth()->id());
        
        return $this->response->setJSON($builder->get()->getResultArray());
    }
    // EKSPOR LAPORAN KE CSV
    public function export($id)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('campaigns')->where('id', $id);
        if (!auth()->user()->inGroup('admin')) {
            $builder->where('user_id', auth()->id());
        }
        $campaign = $builder->get()->getRowArray();
        
        if (!$campaign) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        $recipients = $db->table('email_queue')
            ->select('to_email, subject, status, attempt, last_error, updated_at')
            ->where('campaign_id', $id)
            ->get()->getResultArray();

        $filename = 'report_' . url_title($campaign['name'], '_', true) . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // Header CSV
        fputcsv($output, ['Email Penerima', 'Subjek', 'Status', 'Percobaan', 'Error Terakhir', 'Waktu Update']);

        foreach ($recipients as $row) {
            fputcsv($output, $row);
        }

        record_activity('EXPORT_CAMPAIGN_REPORT', "Mengekspor laporan kampanye '{$campaign['name']}' ke CSV (" . count($recipients) . " data).");

        fclose($output);
        exit;
    }
}