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
        $db = \Config\Database::connect();
        $agent = $this->request->getUserAgent();
        
        $db->table('tracking_logs')->ignore(true)->insert([
            'email_queue_id' => $queueId,
            'campaign_id'    => $this->getCampaignId($queueId, $db),
            'event_type'     => 'OPEN',
            'ip_address'     => $this->request->getIPAddress(),
            'user_agent'     => substr((string)$agent, 0, 255),
            'device'         => $agent->isMobile() ? 'Mobile' : 'Desktop',
            'created_at'     => date('Y-m-d H:i:s')
        ]);

        $pixel = base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
        return $this->response->setHeader('Content-Type', 'image/gif')->setBody($pixel);
    }

    public function click($queueId)
    {
        $encodedUrl = $this->request->getGet('url');
        if (!$encodedUrl) return redirect()->to(site_url());

        $originalUrl = base64_decode(rawurldecode($encodedUrl), true);

        if (!$originalUrl || !filter_var($originalUrl, FILTER_VALIDATE_URL)) {
            return redirect()->to('/');
        }

        // 🔒 Domain whitelist
        $allowedDomains = ['m-link.id']; // tambahkan sesuai kebutuhan
        $host = parse_url($originalUrl, PHP_URL_HOST);

        if (!$host || !in_array($host, $allowedDomains)) {
            return redirect()->to('/');
        }

        $db = \Config\Database::connect();
        $agent = $this->request->getUserAgent();
        $userAgent = (string)$agent;

        try {
            // (Optional) skip bot
            if (stripos($userAgent, 'bot') === false) {
                $db->table('tracking_logs')->insert([
                    'email_queue_id' => $queueId,
                    'campaign_id'    => $this->getCampaignId($queueId, $db),
                    'event_type'     => 'CLICK',
                    'url'            => substr($originalUrl, 0, 65000),
                    'ip_address'     => $this->request->getIPAddress(),
                    'user_agent'     => substr($userAgent, 0, 255),
                    'device'         => $agent->isMobile() ? 'Mobile' : 'Desktop',
                    'created_at'     => date('Y-m-d H:i:s')
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Click tracking failed: ' . $e->getMessage());
        }

        return redirect()->to($originalUrl);
    }

    public function unsubscribe($queueId)
    {
        $db = \Config\Database::connect();
        $queue = $db->table('email_queue')->where('id', $queueId)->get()->getRowArray();
        
        if ($queue) {
            $db->table('suppression_list')->ignore(true)->insert([
                'email'      => $queue['to_email'],
                'reason'     => 'UNSUBSCRIBE',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $agent = $this->request->getUserAgent();
            $db->table('tracking_logs')->insert([
                'email_queue_id' => $queueId,
                'campaign_id'    => $queue['campaign_id'],
                'event_type'     => 'CLICK',
                'url'            => 'UNSUBSCRIBE',
                'ip_address'     => $this->request->getIPAddress(),
                'user_agent'     => substr((string)$agent, 0, 255),
                'device'         => $agent->isMobile() ? 'Mobile' : 'Desktop',
                'created_at'     => date('Y-m-d H:i:s')
            ]);
            
            return view('user/unsubscribe_success', ['email' => $queue['to_email']]);
        }
        return "Data tidak ditemukan.";
    }

    private function getCampaignId($queueId, $db)
    {
        $queue = $db->table('email_queue')->select('campaign_id')->where('id', $queueId)->get()->getRowArray();
        return $queue ? $queue['campaign_id'] : 0;
    }
}
