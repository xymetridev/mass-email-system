<?php

namespace App\Libraries;

use Config\Database;

class BounceHandler
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Mengecek inbox IMAP untuk mencari bounce email
     */
    public function checkBounces($sender)
    {
        if (empty($sender->imap_host) || empty($sender->imap_port)) {
            return false;
        }

        if (!function_exists('imap_open')) {
            log_message('error', 'PHP IMAP extension is not enabled.');
            return false;
        }

        $encryption = ($sender->imap_encryption === 'None') ? '/notls' : '/' . strtolower($sender->imap_encryption);
        $mbox_path = "{" . $sender->imap_host . ":" . $sender->imap_port . "/imap" . $encryption . "}INBOX";
        
        $password = $this->decryptPassword($sender->smtp_password);

        $mbox = @imap_open($mbox_path, $sender->smtp_username, $password);

        if (!$mbox) {
            log_message('error', 'IMAP Connection failed for ' . $sender->sender_email . ': ' . imap_last_error());
            return false;
        }

        // Cari email yang mengandung keyword bounce (Delivery Status Notification)
        $emails = imap_search($mbox, 'SUBJECT "Delivery Status Notification" UNSEEN');
        if (!$emails) {
            $emails = imap_search($mbox, 'SUBJECT "Undelivered Mail Returned to Sender" UNSEEN');
        }

        if ($emails) {
            foreach ($emails as $email_number) {
                $header = imap_headerinfo($mbox, $email_number);
                $body = imap_fetchbody($mbox, $email_number, 1);
                
                // Cari alamat email penerima yang gagal menggunakan Regex
                if (preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}/i', $body, $matches)) {
                    $failed_email = $matches[0];
                    
                    // Jika email gagal adalah recipient kita, masukkan ke blacklist
                    $this->blacklistEmail($failed_email, $sender->user_id);
                }

                // Tandai sudah dibaca
                imap_setflag_full($mbox, $email_number, "\\Seen");
            }
        }

        imap_close($mbox);
        return true;
    }

    protected function blacklistEmail($email, $userId)
    {
        // Masukkan ke suppression_list
        $exists = $this->db->table('suppression_list')
            ->where('email', $email)
            ->where('user_id', $userId)
            ->countAllResults();

        if ($exists === 0) {
            $this->db->table('suppression_list')->insert([
                'user_id'    => $userId,
                'email'      => $email,
                'reason'     => 'Auto-detected Bounce (IMAP)',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            log_message('info', "Email $email blacklisted due to bounce.");
        }
    }

    protected function decryptPassword($encrypted)
    {
        $encrypter = \Config\Services::encrypter();
        return $encrypter->decrypt(base64_decode($encrypted));
    }
}
