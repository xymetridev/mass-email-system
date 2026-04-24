<?php

namespace App\Controllers;

use App\Models\SenderAccountModel;
use App\Models\CampaignModel;

class CampaignWizardController extends BaseController
{
    protected $session;

    public function __construct() {
        $this->session = session();
    }

    /**
     * TAMPILAN (INDEX)
     * Strategi: Ambil dari Session, jika kosong/parsial ambil dari DB.
     */
    public function index($step = 1)
    {   
        $wizard = $this->session->get('campaign_wizard') ?? [];

        // --- HYDRATION (PROSES SEDOT DATA DARI DB SAAT EDIT) ---
        // Ciri-ciri sedang edit: Di session sudah ada 'campaign_id', tapi 'campaign_name' masih kosong
        if (isset($wizard['campaign_id']) && empty($wizard['campaign_name'])) {
            $db = \Config\Database::connect();
            $campaign = $db->table('campaigns')
                           ->where('id', $wizard['campaign_id'])
                           ->where('user_id', auth()->id())
                           ->get()->getRowArray();
            
            if ($campaign) {
                $origStatus = $wizard['original_status'] ?? null;
                $origSched  = $wizard['original_scheduled_at'] ?? null;

                // Mapping isi tabel DB ke dalam format Session Wizard
                $wizard = [
                    'campaign_id'   => $campaign['id'],
                    'campaign_name' => $campaign['name'],
                    'sender_id'     => $campaign['sender_account_id'],
                    'subject'       => $campaign['subject'],
                    'email_html'    => $campaign['content'],
                    'contacts_json' => $campaign['contacts_json'],
                    'scheduled_at'  => ($origStatus == 'SCHEDULED') ? $origSched : $campaign['scheduled_at'],
                    'send_mode'     => ($origStatus == 'SCHEDULED') ? 'scheduled' : 'now',
                    'max_step'      => 5,
                    'original_status'       => $origStatus,
                    'original_scheduled_at' => $origSched
                ];
                session()->set('campaign_wizard', $wizard);
            } else {
                // Jika ID aneh/dihapus, bersihkan session dan tendang keluar
                session()->remove('campaign_wizard');
                return redirect()->to(url_to('app.campaigns'))->with('error', 'Data kampanye rusak.');
            }
        }

        $data = [
            'step'      => $step,
            'pageTitle' => "Buat Kampanye - Langkah $step",
            'wizard'    => $wizard
        ];

        // Proteksi: Jangan biarkan lompat step tanpa ID (kecuali step 1)
        if ($step > 1 && !isset($wizard['campaign_id'])) {
            return redirect()->to(url_to('app.campaigns.wizard', 1))->with('error', 'Sesi kadaluarsa.');
        }

        // Data spesifik per step
        if ($step == 1) {
            $model = new SenderAccountModel();
            $data['businessSmtp'] = $model->where('type', 'BUSINESS')->findAll();
            $data['personalSmtp'] = $model->where('user_id', auth()->id())->where('type', 'INDIVIDUAL')->findAll();
        }

        if ($step == 2) {
            $db = \Config\Database::connect();
            $data['tags'] = $db->table('tags')->where('user_id', auth()->id())->get()->getResultArray();
        }

        if ($step == 2 || $step == 3) {
            $data['availableTags'] = [];
            if (!empty($wizard['contacts_json'])) {
                $parsed = json_decode($wizard['contacts_json'], true);
                $data['availableTags'] = $parsed['mapping'] ?? [];
            }
        }
        
        if ($step == 3) {
            $db = \Config\Database::connect();
            // Ambil template milik sendiri ATAU milik Admin (Global)
            $data['templates'] = $db->table('templates')
                ->select('templates.*')
                ->join('auth_groups_users', 'auth_groups_users.user_id = templates.user_id', 'left')
                ->where('templates.user_id', auth()->id())
                ->orWhere('auth_groups_users.group', 'admin')
                ->groupBy('templates.id')
                ->orderBy('templates.id', 'DESC')
                ->get()->getResultArray();
        }

        return view("campaigns/wizard/step$step", $data); 
    }

    /**
     * PROSES SIMPAN (PROCESS)
     * Strategi: Setiap klik 'Lanjut', data WAJIB masuk Database.
     */
    public function process($step)
    {
        $wizard = $this->session->get('campaign_wizard') ?? [];
        $input = $this->request->getPost();
        $db = db_connect();

        // 1. Logika Simpan Database per Step
        if ($step == 1) {
            $dataDB = [
                'user_id'           => auth()->id(),
                'name'              => $input['campaign_name'],
                'sender_account_id' => $input['sender_id'],
                'status'            => 'DRAFT',
                'updated_at'        => date('Y-m-d H:i:s')
            ];

            if (!isset($wizard['campaign_id'])) {
                $dataDB['created_at'] = date('Y-m-d H:i:s');
                $db->table('campaigns')->insert($dataDB);
                $wizard['campaign_id'] = $db->insertID();
                record_activity('WIZARD_INFO', "Memulai draft kampanye baru bernama '{$input['campaign_name']}'.");
            } else {
                $db->table('campaigns')->where('id', $wizard['campaign_id'])->update($dataDB);
                record_activity('WIZARD_INFO', "Mengupdate info dasar kampanye '{$input['campaign_name']}'.");
            }
        }

        if ($step == 2 && isset($wizard['campaign_id'])) {
            $contactsJson = $input['contacts_json'] ?? null;

            // -- SEMUA SUMBER (Upload/Database/Manual) sudah digabung di FE (contacts_json) --
            $parsed  = json_decode($contactsJson ?? '{}', true);
            $mapping = $parsed['mapping'] ?? [];
            $rows    = $parsed['rows'] ?? [];

            $emailIdx = array_search('email', $mapping);
            if ($emailIdx === false) {
                return redirect()->back()->with('error', 'Format data tidak valid (Kolom Email tidak ditemukan).');
            }

                // Normalisasi + filter invalid + dedupe
                $seen       = [];
                $validRows  = [];
                $skipped    = 0;

                foreach ($rows as $row) {
                    $email = strtolower(trim($row[$emailIdx] ?? ''));
                    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $skipped++;
                        continue;
                    }
                    if (isset($seen[$email])) {
                        $skipped++;
                        continue;
                    }
                    $seen[$email]       = true;
                    $row[$emailIdx]     = $email; // simpan kembali yg sudah dinormalisasi
                    $validRows[]        = $row;
                }

                if (empty($validRows)) {
                    return redirect()->back()->with('error', 'Tidak ada email yang valid. Periksa kembali data Anda.');
                }

                $contactsJson = json_encode(['mapping' => $mapping, 'rows' => $validRows]);

                // -- Simpan ke Master jika diaktifkan --
                if ($input['save_to_master_form'] ?? false) {
                    $tagId     = $input['tag_id_form'] ?? null;
                    $nameIdx   = array_search('name', $mapping);
                    $contactModel = new \App\Models\ContactModel();

                    foreach ($validRows as $row) {
                        $email = $row[$emailIdx];
                        $name  = ($nameIdx !== false) ? ($row[$nameIdx] ?? '') : '';

                        // Skip jika sudah ada (cegah duplikat insert)
                        $exists = $db->table('contacts')
                            ->where('email', $email)
                            ->where('user_id', auth()->id())
                            ->countAllResults();

                        if ($exists) continue;

                        try {
                            $contactModel->insert([
                                'user_id'    => auth()->id(),
                                'email'      => $email,
                                'name'       => $name,
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                            $cid = $contactModel->insertID();
                            if ($tagId) {
                                $db->table('recipient_tags')->ignore(true)->insert(['contact_id' => $cid, 'tag_id' => $tagId]);
                            }
                        } catch (\Exception $e) {
                            // Skip gracefully
                        }
                    }
                }


            $db->table('campaigns')->where('id', $wizard['campaign_id'])->update([
                'contacts_json' => $contactsJson
            ]);

            $wizard['contacts_json'] = $contactsJson;
            // source_mode and db_tags are kept for UI state restoration if needed
            $wizard['source_mode']   = $input['source_mode'] ?? 'upload';
            $wizard['db_tags']       = $input['db_tags'] ?? '';
            $wizard['max_step']      = max($wizard['max_step'] ?? 1, 3);
            session()->set('campaign_wizard', $wizard);

            $campaignName = $wizard['campaign_name'] ?? 'Tanpa Nama';
            record_activity('WIZARD_RECIPIENTS', "Mengatur target penerima untuk kampanye '{$campaignName}'.");

            return redirect()->to(url_to('app.campaigns.wizard', 3))->with('success', 'Penerima berhasil diatur.');
        }

        if ($step == 3 && isset($wizard['campaign_id'])) {
            // SERVER-SIDE: Subject wajib tidak kosong
            $subject = trim($input['subject'] ?? '');
            if (empty($subject)) {
                return redirect()->back()->withInput()->with('error', 'Subjek email wajib diisi sebelum melanjutkan.');
            }

            $db->table('campaigns')->where('id', $wizard['campaign_id'])->update([
                'subject' => $subject,
                'content' => $input['email_html'] ?? ''
            ]);

            $campaignName = $wizard['campaign_name'] ?? 'Tanpa Nama';
            record_activity('WIZARD_CONTENT', "Mengubah konten email kampanye '{$campaignName}'.");
        }

        if ($step == 4 && isset($wizard['campaign_id'])) {
            $sendMode = $input['send_mode'] ?? 'now';
            $db->table('campaigns')->where('id', $wizard['campaign_id'])->update([
                'scheduled_at' => ($sendMode == 'now') ? date('Y-m-d H:i:s') : $input['scheduled_at'],
                'status'       => ($sendMode == 'now') ? 'READY' : 'SCHEDULED'
            ]);
            
            $campaignName = $wizard['campaign_name'] ?? 'Tanpa Nama';
            $schedText = ($sendMode == 'now') ? "sekarang" : "pada {$input['scheduled_at']}";
            record_activity('WIZARD_SCHEDULE', "Mengatur jadwal tayang kampanye '{$campaignName}' untuk {$schedText}.");
        }

        // 2. Update Session (Gunakan loop agar tidak menimpa data step lain yang tidak ada di form saat ini)
        foreach ($input as $key => $value) {
            if ($value !== null) $wizard[$key] = $value;
        }

        //Rekam jejak langkah tertinggi yang pernah dicapai
        $currentMax = $wizard['max_step'] ?? 1;
        $wizard['max_step'] = max($currentMax, $step + 1);
        
        $this->session->set('campaign_wizard', $wizard);

        //Cek apakah user menekan tombol "Simpan & Ke Review"
        if ($this->request->getPost('jump_to_review') == 'yes') {
            return redirect()->to(url_to('app.campaigns.wizard', 5));
        }

        return redirect()->to(url_to('app.campaigns.wizard', $step + 1));
    }

    /**
     * FINALISASI (FINISH)
     */

    public function finish()
    {
        $wizard = session()->get('campaign_wizard');
        
        if (!$wizard || !isset($wizard['campaign_id'])) {
            return redirect()->to(url_to('app.campaigns'))->with('error', 'Sesi kadaluarsa.');
        }

        $db = db_connect();

        // 1. AMBIL DATA DARI DATABASE (Bukan Session) agar subjek & konten pasti ada
        $campaign = $db->table('campaigns')->where('id', $wizard['campaign_id'])->get()->getRowArray();
        
        if (!$campaign) {
            return redirect()->to(url_to('app.campaigns'))->with('error', 'Data kampanye tidak ditemukan di database.');
        }

        $contacts = json_decode($campaign['contacts_json'] ?? '{"rows":[], "mapping":[]}', true);
        $mapping = $contacts['mapping'] ?? [];
        $rows = $contacts['rows'] ?? [];
        $emailIdx = array_search('email', $mapping);

        if ($emailIdx === false) {
            return redirect()->back()->with('error', 'Kolom Email tidak ditemukan.');
        }

        $queueData = [];
        $baseSubject = $campaign['subject'] ?? '';
        $baseHtml    = $campaign['content'] ?? '';
        $existingEmails = [];

        foreach ($rows as $row) {
            if (!isset($row[$emailIdx])) continue;

            // Normalisasi server-side final
            $toEmail = strtolower(trim($row[$emailIdx]));

            // Skip jika kosong, format tidak valid, atau duplikat
            if (!$toEmail || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) continue;
            if (isset($existingEmails[$toEmail])) continue;
            $existingEmails[$toEmail] = true;

            $personalSubject = $baseSubject;
            $personalBody    = $baseHtml;

            // Replace Merge Tags
            foreach ($mapping as $index => $tag) {
                if ($tag !== 'none' && isset($row[$index])) {
                    $personalSubject = str_ireplace('{{' . $tag . '}}', $row[$index], $personalSubject);
                    $personalBody    = str_ireplace('{{' . $tag . '}}', $row[$index], $personalBody);
                }
            }

            $queueData[] = [
                'campaign_id'       => $campaign['id'],
                'recipient_id'      => 0,
                'sender_account_id' => $campaign['sender_account_id'],
                'to_email'          => $toEmail,
                'subject'           => $personalSubject,
                'body'              => $personalBody,
                'status'            => 'PENDING',
                'created_at'        => date('Y-m-d H:i:s')
            ];
        }

            $db->transStart();
            
            // Hapus HANYA yang masih PENDING agar yang sudah SENT tidak hilang
            $db->table('email_queue')
               ->where('campaign_id', $campaign['id'])
               ->where('status', 'PENDING')
               ->delete();
            
            // Baru masukkan data yang paling *fresh* dengan ignore(true) untuk menghindari konflik Unique Key
            $db->table('email_queue')->ignore(true)->insertBatch($queueData);
            
            // Hitung total ulang yang ada di antrean
            $realTotal = $db->table('email_queue')->where('campaign_id', $campaign['id'])->countAllResults();
            
            // Update jumlah total target di tabel campaigns
            $db->table('campaigns')->where('id', $campaign['id'])->update([
                'total_targets' => $realTotal
            ]);
            
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                return redirect()->back()->with('error', 'Gagal memproses antrean ke database.');
            }
            
        record_activity('CAMPAIGN_LAUNCH', "Resmi meluncurkan kampanye '{$campaign['name']}' ke antrean sistem dengan {$realTotal} target.");
        session()->remove('campaign_wizard');
        return redirect()->to(url_to('app.campaigns'))->with('success', 'Kampanye berhasil diluncurkan!');
    }



    public function cancel()
    {
        $wizard = session()->get('campaign_wizard');
        
        if ($wizard && isset($wizard['campaign_id'])) {
            // Jika dulu status aslinya adalah SCHEDULED, kita kembalikan jadwalnya!
            if (isset($wizard['original_status']) && $wizard['original_status'] === 'SCHEDULED') {
                $db = \Config\Database::connect();
                $db->table('campaigns')->where('id', $wizard['campaign_id'])->update([
                    'status'       => 'SCHEDULED',
                    'scheduled_at' => $wizard['original_scheduled_at']
                ]);
            }
        }
        
        // Bersihkan session dan tendang kembali ke Index
        session()->remove('campaign_wizard');
        return redirect()->to(url_to('app.campaigns'))->with('info', 'Edit dibatalkan. Status kampanye tidak berubah.');
    }

    /**
     * AJAX ENDPOINT: Mengambil kontak berdasarkan Tag ID untuk fitur Merge di Wizard Step 2
     */
    public function getTagContacts($tagId)
    {
        $db = \Config\Database::connect();
        $contacts = $db->table('contacts')
            ->select('email, name')
            ->join('recipient_tags', 'recipient_tags.contact_id = contacts.id')
            ->where('recipient_tags.tag_id', $tagId)
            ->where('contacts.user_id', auth()->id())
            ->get()->getResultArray();
            
        return $this->response->setJSON(['status' => 'success', 'data' => $contacts]);
    }
}