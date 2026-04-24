<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class EmailWorker extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'email:worker';
    protected $description = 'Worker pengirim email real-time dengan proteksi rate limit.';

    private $encrypter;

    public function run(array $params)
    {
        set_time_limit(0);
        $db = db_connect();
        $emailService = \Config\Services::email(null, false);
        $this->encrypter = service('encrypter'); // Inisialisasi sekali di luar loop
        
        CLI::write("Worker sedang berjalan... Tekan CTRL+C untuk stop.", 'green');

        while (true) {
            $db->reconnect();

            // --- PROSES OTOMASI (SEQUENCES) ---
            $this->processAutomations($db);

            // ATOMIC LOCK: Ambil & kunci 1 antrean dalam satu transaksi (aman multi-worker)
            $db->transStart();
            $email = $db->query("
                SELECT q.*, c.status as campaign_status 
                FROM email_queue q
                JOIN campaigns c ON c.id = q.campaign_id
                WHERE q.status = 'PENDING'
                AND (q.next_attempt_at IS NULL OR q.next_attempt_at <= NOW())
                AND c.status IN ('READY', 'RUNNING')
                ORDER BY q.id ASC
                LIMIT 1
                FOR UPDATE OF q SKIP LOCKED
            ")->getRow();

            if (!$email) {
                $db->transComplete();
                CLI::print("Antrean kosong, istirahat 5 detik...\r");
                sleep(5);
                continue;
            }

            // 1. CEK BLACKLIST (SUPPRESSION LIST) SEBELUM DIKIRIM
            $isSuppressed = $db->table('suppression_list')->where('email', $email->to_email)->countAllResults();
            if ($isSuppressed > 0) {
                // Email di-blacklist (unsubscribe/bounce), skip pengiriman tapi tandai sbg FAILED
                $db->table('email_queue')->where('id', $email->id)->update([
                    'status' => 'FAILED',
                    'last_error' => 'Email is in Suppression List (Blacklisted/Unsubscribed)',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $db->transComplete();
                CLI::write("SKIPPED (Blacklisted): " . $email->to_email, 'yellow');
                continue;
            }

            $sender = $db->table('sender_accounts')->where('id', $email->sender_account_id)->get()->getRow();
            if (!$sender) {
                CLI::write("SKIPPED: Sender account $email->sender_account_id tidak ditemukan.", 'red');
                $db->table('email_queue')->where('id', $email->id)->update(['status' => 'FAILED', 'last_error' => 'Sender account missing', 'updated_at' => date('Y-m-d H:i:s')]);
                $db->transComplete();
                continue;
            }

            // 3. LOGIKA WARM-UP MODE
            // --- LOGIKA WARM-UP & HOURLY THROTTLING ---
            $now = date('Y-m-d H:i:s');
            $today = date('Y-m-d');
            $currentHour = date('Y-m-d H:00:00');

            // Cek Bounce via IMAP secara berkala (misal: 10% probabilitas per loop agar tidak lambat)
            if (rand(1, 10) === 1) {
                $bounceHandler = new \App\Libraries\BounceHandler();
                $bounceHandler->checkBounces($sender);
            }

            // Reset Harian (Warm-up)
            if ($sender->warmup_last_date !== $today) {
                $db->table('sender_accounts')->where('id', $sender->id)->update([
                    'warmup_sent_today' => 0,
                    'warmup_last_date'  => $today
                ]);
                $sender->warmup_sent_today = 0;
            }

            // Reset Hourly Throttling
            if ($sender->last_hour_reset !== $currentHour) {
                $db->table('sender_accounts')->where('id', $sender->id)->update([
                    'sent_this_hour'  => 0,
                    'last_hour_reset' => $currentHour
                ]);
                $sender->sent_this_hour = 0;
            }

            // Cek Limit Harian (Warm-up)
            if ($sender->warmup_mode && $sender->warmup_sent_today >= $sender->warmup_daily_limit) {
                CLI::write("Limit harian tercapai untuk {$sender->sender_email}. Skipping...", 'yellow');
                $db->table('email_queue')->where('id', $email->id)->update(['status' => 'PENDING']);
                $db->transComplete();
                continue;
            }

            // Cek Limit Per Jam (Throttling)
            if ($sender->hourly_limit > 0 && $sender->sent_this_hour >= $sender->hourly_limit) {
                CLI::write("Limit per jam tercapai untuk {$sender->sender_email}. Menunggu jam berikutnya...", 'yellow');
                $db->table('email_queue')->where('id', $email->id)->update(['status' => 'PENDING']);
                $db->transComplete();
                continue;
            }

            // Kunci status dalam transaksi yang sama
            $db->table('email_queue')->where('id', $email->id)->update(['status' => 'PROCESSING']);
            $db->transComplete();

            try {
                // Dekripsi password dan Initialize SMTP
                $config = $this->getSmtpConfig($sender);
                $emailService->initialize($config);
                
                $emailService->setFrom($sender->sender_email, $sender->sender_name);
                if (!filter_var($email->to_email, FILTER_VALIDATE_EMAIL)) {
                    $this->markAsFailed($db, $email, 'Invalid recipient email format', $sender);
                    continue;
                }
                $emailService->setTo($email->to_email);
                $emailService->setSubject($email->subject);
                $emailService->setReplyTo($sender->sender_email, $sender->sender_name);
                
                // 2. INJEKSI TRACKING & UNSUBSCRIBE
                $body = $email->body;

                // Replace merge tag {{unsubscribe_url}} if user put it manually
                $token = hash_hmac('sha256', $email->id . '|' . $email->to_email, env('app.key'));
                $unsubUrl = site_url('track/unsubscribe/' . $email->id . '?token=' . $token);
                $body = str_ireplace('{{unsubscribe_url}}', $unsubUrl, $body);

                $emailService->setHeader('List-Unsubscribe', '<' . $unsubUrl . '>');
                $emailService->setHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');

                // Auto-append Unsubscribe if not present
                if (stripos($body, 'track/unsubscribe') === false) {
                    $body .= '<br><br><p style="font-size: 11px; color: #999;">Ingin berhenti menerima email dari kami? <a href="'.$unsubUrl.'">Klik disini untuk Unsubscribe</a>.</p>';
                }

                // Injeksi Tracking Pixel (Open Tracking)
                $pixelUrl = site_url('track/open/' . $email->id);
                $body .= '<img src="'.$pixelUrl.'" width="1" height="1" alt="" style="display:none;" />';

                // Wrap semua link <a href="..."> untuk Click Tracking
                $body = preg_replace_callback('/<a\s+(?:[^>]*?\s+)?href=([\'"])(.*?)\1/', function($matches) use ($email) {
                    $originalUrl = $matches[2];
                    
                    // Jangan wrap link tracking kita sendiri atau mailto: / tel:
                    if (strpos($originalUrl, 'track/') !== false || strpos($originalUrl, 'mailto:') === 0 || strpos($originalUrl, 'tel:') === 0 || strpos($originalUrl, '#') === 0) {
                        return $matches[0];
                    }
                    
                    //$originalUrl = base64_decode(rawurldecode($this->request->getGet('url')), true);
                    $trackUrl = site_url('track/click/' . $email->id) . '?url=' . rawurlencode(base64_encode($originalUrl));
                    return str_replace($originalUrl, $trackUrl, $matches[0]);
                }, $body);

                $emailService->setAltMessage(strip_tags($body));
                $emailService->setMessage($body);

                if ($emailService->send()) {
                    $this->markAsSent($db, $email);
                    
                    // Update statistik pengiriman
                    $db->table('sender_accounts')
                       ->where('id', $sender->id)
                       ->set('warmup_sent_today', 'warmup_sent_today + 1', false)
                       ->set('sent_this_hour', 'sent_this_hour + 1', false)
                       ->update();

                    CLI::write("Sukses: " . $email->to_email, 'green');
                } else {
                    $this->markAsFailed($db, $email, $emailService->printDebugger(['headers', 'subject', 'body']), $sender);
                    CLI::write("Gagal: " . $email->to_email, 'red');

                    $debug = $emailService->printDebugger(['headers', 'subject']);
                    log_message('error', 'SMTP send failed for {to}. Debug: {debug}', [
                        'to'    => $email->to_email,
                        'debug' => $debug,
                    ]);
                }

            } catch (\Exception $e) {
                $this->markAsFailed($db, $email, $e->getMessage(), $sender);
            }

            // Clear data email service untuk loop berikutnya
            $emailService->clear(true);

            // 🔥 IMPLEMENTASI RATE LIMIT YANG KAMU MAU
            $delaySeconds = 2; // Dasar jeda 2 detik
            $randomFactor = rand(80, 120) / 100; // 0.8x - 1.2x
            $finalDelay = (int)($delaySeconds * $randomFactor * 1000000); 
            
            usleep($finalDelay);
        }
    }

    private function getSmtpConfig($sender) {
        return [
            'protocol'   => 'smtp',
            'SMTPHost'   => $sender->smtp_host,
            'SMTPUser'   => $sender->smtp_username,
            'SMTPPass'   => $this->encrypter->decrypt(base64_decode($sender->smtp_password)),
            'SMTPPort'   => (int)$sender->smtp_port,
            'SMTPCrypto' => ($sender->encryption === 'None') ? '' : strtolower($sender->encryption),
            'mailType'   => 'html',
            'charset'    => 'utf-8',
            'newline'    => "\r\n",
            'CRLF'       => "\r\n",

            // 🔥 STABILITAS
            'SMTPTimeout'   => 15,
            'SMTPKeepAlive' => false,

            // 🔥 DEBUG (sementara)
            'SMTPDebug'     => 2,
        ];
    }


    private function markAsSent($db, $email) {
        $now = date('Y-m-d H:i:s');
        
        // 1. Update status di queue (Sudah mencakup semua info target)
        $db->table('email_queue')->where('id', $email->id)->update([
            'status'     => 'SENT', 
            'updated_at' => $now
        ]);

        // 2. 🔥 CEK APAKAH INI EMAIL TERAKHIR UNTUK KAMPANYE INI?
        if ($email->campaign_id > 0) {
            $remaining = $db->table('email_queue')
                            ->where('campaign_id', $email->campaign_id)
                            ->whereIn('status', ['PENDING', 'PROCESSING'])
                            ->countAllResults();

            if ($remaining === 0) {
                $db->table('campaigns')->where('id', $email->campaign_id)->update(['status' => 'COMPLETED']);
                CLI::write("INFO: Kampanye ID {$email->campaign_id} SELESAI!", 'blue');
            }
        }
    }

    private function markAsFailed($db, $email, $error, $sender = null)
    {
        $attempt = (int)$email->attempt + 1;

        $isHardBounce = preg_match('/\b(550|551|552|553|554|5\.\d\.\d)\b/', $error);
        $isTemporary  = preg_match('/\b(421|450|451|452|4\.\d\.\d)\b/', $error);
        $isAuthError  = strpos($error, '535') !== false;

        // Handle auth error (pause sender)
        if ($isAuthError) {
            $db->table('sender_accounts')
            ->where('id', $email->sender_account_id)
            ->update(['status' => 'PAUSED']);
        }

        if ($isHardBounce) {
            $finalFailed = true;

            // Masukkan ke suppression list
            $db->table('suppression_list')->insert([
                'user_id'    => $sender ? $sender->user_id : 0,
                'email'      => $email->to_email,
                'reason'     => 'Hard bounce',
                'created_at' => date('Y-m-d H:i:s')
            ]);

        } elseif ($isTemporary) {
            $finalFailed = false;
        } else {
            $finalFailed = $attempt >= 3;
        }

        $backoffSeconds = min(86400, 60 * (2 ** max(0, $attempt - 1)));
        $nextAttemptAt  = $finalFailed ? null : date('Y-m-d H:i:s', time() + $backoffSeconds);

        $db->table('email_queue')->where('id', $email->id)->update([
            'status'          => $finalFailed ? 'FAILED' : 'PENDING',
            'attempt'         => $attempt,
            'next_attempt_at' => $nextAttemptAt,
            'last_error'      => mb_substr(trim(strip_tags($error)), 0, 1000, 'UTF-8'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    private function processAutomations($db)
    {
        $now = date('Y-m-d H:i:s');
        
        $queueItems = $db->table('automation_queue')
            ->where('next_run_at <=', $now)
            ->where('status', 'PENDING')
            ->get()->getResultArray();

        foreach ($queueItems as $item) {
            $step = $db->table('automation_steps')->where('id', $item['current_step_id'])->get()->getRowArray();
            $contact = $db->table('contacts')->where('id', $item['recipient_id'])->get()->getRowArray();
            $automation = $db->table('automations')->where('id', $item['automation_id'])->get()->getRowArray();

            if ($step && $contact && $automation['status'] == 'ACTIVE') {
                $template = $db->table('templates')->where('id', $step['template_id'])->get()->getRowArray();
                
                $db->table('email_queue')->insert([
                    'campaign_id'       => 0,
                    'recipient_id'      => $contact['id'],
                    'sender_account_id' => $automation['sender_account_id'] ?? 0, // Pastikan ada sender_id
                    'to_email'          => $contact['email'],
                    'subject'           => $template['name'],
                    'body'              => $template['content'],
                    'status'            => 'PENDING',
                    'scheduled_at'      => $now,
                    'created_at'        => $now,
                    'updated_at'        => $now
                ]);

                $nextStep = $db->table('automation_steps')
                    ->where('automation_id', $item['automation_id'])
                    ->where('step_order >', $step['step_order'])
                    ->orderBy('step_order', 'ASC')
                    ->get()->getRowArray();

                if ($nextStep) {
                    $db->table('automation_queue')->where('id', $item['id'])->update([
                        'current_step_id' => $nextStep['id'],
                        'next_run_at'     => date('Y-m-d H:i:s', strtotime("+{$nextStep['delay_days']} days")),
                        'status'          => 'PENDING'
                    ]);
                } else {
                    $db->table('automation_queue')->where('id', $item['id'])->update(['status' => 'COMPLETED']);
                }
            }
        }
    }
}