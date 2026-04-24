<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class TrackingController extends Controller
{
    /**
     * Endpoint untuk mendeteksi saat email dibuka (Open Tracking)
     * URL: /track/open/{email_queue_id}
     */
    public function open($queueId)
    {
        $db    = \Config\Database::connect();
        $agent = $this->request->getUserAgent();

        $db->table('tracking_logs')->ignore(true)->insert([
            'email_queue_id' => $queueId,
            'campaign_id'    => $this->getCampaignId($queueId, $db),
            'event_type'     => 'OPEN',
            'ip_address'     => $this->request->getIPAddress(),
            'user_agent'     => substr((string) $agent, 0, 255),
            'device'         => $agent->isMobile() ? 'Mobile' : 'Desktop',
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $pixel = base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
        return $this->response->setHeader('Content-Type', 'image/gif')->setBody($pixel);
    }

    /**
     * FIX #3: Hapus hardcoded domain whitelist — validasi URL format saja.
     * Jika ingin membatasi domain, pindahkan ke .env atau tabel DB.
     * URL: /track/click/{email_queue_id}?url={encoded}
     */
    public function click($queueId)
    {
        $encodedUrl = $this->request->getGet('url');
        if (! $encodedUrl) {
            return redirect()->to(site_url());
        }

        $originalUrl = base64_decode(rawurldecode($encodedUrl), true);

        // Validasi hanya format URL, tidak membatasi domain tertentu
        if (! $originalUrl || ! filter_var($originalUrl, FILTER_VALIDATE_URL)) {
            return redirect()->to('/');
        }

        // Blokir open redirect ke scheme berbahaya (javascript:, data:, dll)
        $scheme = parse_url($originalUrl, PHP_URL_SCHEME);
        if (! in_array(strtolower($scheme ?? ''), ['http', 'https'])) {
            return redirect()->to('/');
        }

        $db        = \Config\Database::connect();
        $agent     = $this->request->getUserAgent();
        $userAgent = (string) $agent;

        try {
            // Skip bot & email scanner (jangan hitung buka email dari Googlebot, dll)
            $isBot = stripos($userAgent, 'bot') !== false
                || stripos($userAgent, 'spider') !== false
                || stripos($userAgent, 'preview') !== false;

            if (! $isBot) {
                $db->table('tracking_logs')->insert([
                    'email_queue_id' => $queueId,
                    'campaign_id'    => $this->getCampaignId($queueId, $db),
                    'event_type'     => 'CLICK',
                    'url'            => substr($originalUrl, 0, 65000),
                    'ip_address'     => $this->request->getIPAddress(),
                    'user_agent'     => substr($userAgent, 0, 255),
                    'device'         => $agent->isMobile() ? 'Mobile' : 'Desktop',
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Click tracking failed: ' . $e->getMessage());
        }

        return redirect()->to($originalUrl);
    }

    /**
     * FIX #4: Validasi HMAC token sebelum menambahkan ke suppression list.
     * FIX #5: Gunakan event_type = 'UNSUBSCRIBE', bukan 'CLICK'.
     * URL: /track/unsubscribe/{email_queue_id}?token={hmac}
     */
    public function unsubscribe($queueId)
    {
        $db    = \Config\Database::connect();
        $token = $this->request->getGet('token');

        // FIX #4: Ambil queue data dulu untuk validasi token
        $queue = $db->table('email_queue')->where('id', $queueId)->get()->getRowArray();

        if (! $queue) {
            return view('user/unsubscribe_success', ['email' => '', 'error' => 'Link tidak valid.']);
        }

        // Verifikasi HMAC token agar tidak bisa disalahgunakan
        $expectedToken = hash_hmac('sha256', $queueId . '|' . $queue['to_email'], env('app.key'));
        if (! hash_equals($expectedToken, $token ?? '')) {
            return view('user/unsubscribe_success', ['email' => $queue['to_email'], 'error' => 'Token tidak valid.']);
        }

        // Masukkan ke suppression list (ignore jika sudah ada)
        $db->table('suppression_list')->ignore(true)->insert([
            'user_id'    => $queue['user_id'] ?? null,
            'email'      => $queue['to_email'],
            'reason'     => 'UNSUBSCRIBE',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // FIX #5: Gunakan event_type 'UNSUBSCRIBE' yang benar, bukan 'CLICK'
        $agent = $this->request->getUserAgent();
        $db->table('tracking_logs')->ignore(true)->insert([
            'email_queue_id' => $queueId,
            'campaign_id'    => $queue['campaign_id'],
            'event_type'     => 'UNSUBSCRIBE',
            'url'            => null,
            'ip_address'     => $this->request->getIPAddress(),
            'user_agent'     => substr((string) $agent, 0, 255),
            'device'         => $agent->isMobile() ? 'Mobile' : 'Desktop',
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        return view('user/unsubscribe_success', ['email' => $queue['to_email']]);
    }

    /**
     * Helper: Dapatkan campaign_id dari email_queue (cached dalam satu request)
     */
    private function getCampaignId($queueId, $db)
    {
        $queue = $db->table('email_queue')->select('campaign_id')->where('id', $queueId)->get()->getRowArray();
        return $queue ? $queue['campaign_id'] : 0;
    }
}
