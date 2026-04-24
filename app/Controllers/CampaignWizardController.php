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
            $campaign = $db->table('campaigns')->where('id', $wizard['campaign_id'])->get()->getRowArray();
            
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
            } else {
                $db->table('campaigns')->where('id', $wizard['campaign_id'])->update($dataDB);
            }
        }

        if ($step == 2 && isset($wizard['campaign_id'])) {
            $sourceMode = $input['source_mode'] ?? 'upload';
            $contactsJson = $input['contacts_json'] ?? null;
            
            if ($sourceMode == 'database') {
                $tagIds = explode(',', $input['db_tags']);
                $contacts = $db->table('contacts')
                    ->select('contacts.email, contacts.name')
                    ->join('recipient_tags', 'recipient_tags.contact_id = contacts.id')
                    ->whereIn('recipient_tags.tag_id', $tagIds)
                    ->groupBy('contacts.email')
                    ->groupBy('contacts.name')
                    ->get()->getResultArray();

                $rows = [];
                foreach ($contacts as $c) {
                    $rows[] = [$c['email'], $c['name']];
                }

                $contactsJson = json_encode([
                    'mapping' => ['email', 'name'],
                    'rows'    => $rows
                ]);
            } else {
                // Jika Upload CSV + Save to Master
                if ($this->request->getPost('save_to_master_form')) {
                    $tagId = $this->request->getPost('tag_id_form');
                    $parsed = json_decode($contactsJson, true);
                    $rows = $parsed['rows'] ?? [];
                    $mapping = $parsed['mapping'] ?? [];
                    $emailIdx = array_search('email', $mapping);
                    $nameIdx = array_search('name', $mapping);

                    if ($emailIdx !== false) {
                        foreach ($rows as $row) {
                            $email = $row[$emailIdx];
                            $name = ($nameIdx !== false) ? ($row[$nameIdx] ?? '') : '';
                            
                            try {
                                $db->table('contacts')->insert([
                                    'user_id' => auth()->id(),
                                    'email'   => $email,
                                    'name'    => $name,
                                    'created_at' => date('Y-m-d H:i:s')
                                ]);
                                $cid = $db->insertID();
                                if ($tagId) {
                                    $db->table('recipient_tags')->insert(['contact_id' => $cid, 'tag_id' => $tagId]);
                                }
                            } catch (\Exception $e) {}
                        }
                    }
                }
            }

            $db->table('campaigns')->where('id', $wizard['campaign_id'])->update([
                'contacts_json' => $contactsJson
            ]);

            $wizard['contacts_json'] = $contactsJson;
            $wizard['source_mode']   = $sourceMode;
            $wizard['db_tags']       = $input['db_tags'] ?? '';
            $wizard['max_step']      = max($wizard['max_step'] ?? 1, 3);
            session()->set('campaign_wizard', $wizard);
            
            return redirect()->to(url_to('app.campaigns.wizard', 3))->with('success', 'Penerima berhasil diatur.');
        }

        if ($step == 3 && isset($wizard['campaign_id'])) {
            $db->table('campaigns')->where('id', $wizard['campaign_id'])->update([
                'subject' => $input['subject'] ?? '',
                'content' => $input['email_html'] ?? ''
            ]);
        }

        if ($step == 4 && isset($wizard['campaign_id'])) {
            $sendMode = $input['send_mode'] ?? 'now';
            $db->table('campaigns')->where('id', $wizard['campaign_id'])->update([
                'scheduled_at' => ($sendMode == 'now') ? date('Y-m-d H:i:s') : $input['scheduled_at'],
                'status'       => ($sendMode == 'now') ? 'READY' : 'SCHEDULED'
            ]);
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
        $baseHtml = $campaign['content'] ?? '';
        $existingEmails = [];

        foreach ($rows as $row) {
            if (!isset($row[$emailIdx]) || empty(trim($row[$emailIdx]))) continue;

            $personalSubject = $baseSubject;
            $personalBody = $baseHtml;

            // --- SISTEM REPLACE MERGE TAGS ---
            foreach ($mapping as $index => $tag) {
                if ($tag !== 'none' && isset($row[$index])) {
                    // Gunakan str_ireplace agar tidak sensitif terhadap huruf besar/kecil
                    $personalSubject = str_ireplace('{{' . $tag . '}}', $row[$index], $personalSubject);
                    $personalBody    = str_ireplace('{{' . $tag . '}}', $row[$index], $personalBody);
                }
            }

            $toEmail = trim($row[$emailIdx]);

            // Cegah duplikat email akibat user manual edit di Step 2 (mencegah crash saat insertBatch)
            if (isset($existingEmails[$toEmail])) continue;
            $existingEmails[$toEmail] = true;

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

        if (!empty($queueData)) {
            // --- TAMBAHKAN BARIS INI: Sapu bersih antrean lama milik kampanye ini ---
            $db->table('email_queue')->where('campaign_id', $campaign['id'])->delete();
            
            // Baru masukkan data yang paling *fresh*
            $db->table('email_queue')->insertBatch($queueData);
            
            // Update jumlah total target di tabel campaigns
            $db->table('campaigns')->where('id', $campaign['id'])->update([
                'total_targets' => count($queueData)
            ]);
        }

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
}