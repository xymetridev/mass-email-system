<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\ContactModel;

class ContactController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $contacts = $db->table('contacts')
            ->select('contacts.*, GROUP_CONCAT(tags.name) as tag_names, GROUP_CONCAT(tags.id) as tag_ids')
            ->join('recipient_tags', 'recipient_tags.contact_id = contacts.id', 'left')
            ->join('tags', 'tags.id = recipient_tags.tag_id', 'left')
            ->where('contacts.user_id', auth()->id())
            ->groupBy('contacts.id')
            ->orderBy('contacts.id', 'DESC')
            ->get()->getResultArray();

        $data = [
            'pageTitle' => 'Manajemen Kontak',
            'contacts'  => $contacts,
            'tags'      => $db->table('tags')->where('user_id', auth()->id())->get()->getResultArray()
        ];

        return view('user/contacts/index', $data);
    }

    public function store()
    {
        $model = new ContactModel();
        $db = \Config\Database::connect();

        $email = $this->request->getPost('email');
        $name  = $this->request->getPost('name');
        $tagId = $this->request->getPost('tag_id');

        if (empty($email)) {
            return redirect()->back()->with('error', 'Email wajib diisi.');
        }

        $data = [
            'user_id' => auth()->id(),
            'email'   => $email,
            'name'    => $name
        ];

        try {
            $model->insert($data);
            $contactId = $model->insertID();

            if ($tagId) {
                $db->table('recipient_tags')->insert([
                    'contact_id' => $contactId,
                    'tag_id'     => $tagId
                ]);
                
                // Trigger Automations for this tag
                $this->triggerAutomations($contactId, $tagId);
            }

            return redirect()->back()->with('success', 'Kontak berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Email sudah terdaftar atau terjadi kesalahan.');
        }
    }

    public function storeTag()
    {
        $db = \Config\Database::connect();
        $name = $this->request->getPost('name');

        if ($name) {
            $db->table('tags')->insert([
                'user_id'    => auth()->id(),
                'name'       => $name,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        return redirect()->back()->with('success', 'Tag berhasil dibuat.');
    }

    public function downloadSample()
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="sample_contacts.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['email', 'name']);
        fputcsv($output, ['budi@example.com', 'Budi Santoso']);
        fputcsv($output, ['siti@example.com', 'Siti Aminah']);
        fclose($output);
        exit;
    }

    public function import()
    {
        $file = $this->request->getFile('csv_file');
        $tagId = $this->request->getPost('tag_id');

        if (!$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid.');
        }

        $db = \Config\Database::connect();
        $model = new ContactModel();
        
        $count = 0;
        if (($handle = fopen($file->getTempName(), "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ","); // Skip header
            
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (empty($data[0])) continue;

                $email = $data[0];
                $name  = $data[1] ?? '';

                // Insert ignore logic
                try {
                    $model->insert([
                        'user_id' => auth()->id(),
                        'email'   => $email,
                        'name'    => $name
                    ]);
                    $contactId = $model->insertID();

                    if ($tagId) {
                        $db->table('recipient_tags')->insert([
                            'contact_id' => $contactId,
                            'tag_id'     => $tagId
                        ]);
                        $this->triggerAutomations($contactId, $tagId);
                    }
                    $count++;
                } catch (\Exception $e) {
                    // Skip jika sudah ada
                }
            }
            fclose($handle);
        }

        if ($count > 0) {
            record_activity('IMPORT_CONTACTS', "Mengimpor $count kontak baru via CSV.");
        }

        return redirect()->to(url_to('user.contacts') . '#tab-contacts')->with('success', "$count kontak berhasil di-import.");
    }

    public function update($id)
    {
        $model = new ContactModel();
        $db = \Config\Database::connect();

        $contact = $model->where(['id' => $id, 'user_id' => auth()->id()])->first();
        if (!$contact) return redirect()->back()->with('error', 'Kontak tidak ditemukan.');

        $email = $this->request->getPost('email');
        $name  = $this->request->getPost('name');
        $tagId = $this->request->getPost('tag_id');

        $model->update($id, ['email' => $email, 'name' => $name]);

        // Update Tag (Sederhananya hapus yang lama, pasang yang baru)
        if ($tagId) {
            $db->table('recipient_tags')->where('contact_id', $id)->delete();
            $db->table('recipient_tags')->insert([
                'contact_id' => $id,
                'tag_id'     => $tagId
            ]);
        }

        return redirect()->back()->with('success', 'Kontak berhasil diperbarui.');
    }

    public function delete($id)
    {
        $model = new ContactModel();
        $db = \Config\Database::connect();

        if ($model->where(['id' => $id, 'user_id' => auth()->id()])->delete()) {
            $db->table('recipient_tags')->where('contact_id', $id)->delete();
            return redirect()->back()->with('success', 'Kontak berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Gagal menghapus kontak.');
    }

    public function deleteTag($id)
    {
        $db = \Config\Database::connect();
        $db->table('tags')->where(['id' => $id, 'user_id' => auth()->id()])->delete();
        $db->table('recipient_tags')->where('tag_id', $id)->delete();

        return redirect()->back()->with('success', 'Tag berhasil dihapus.');
    }

    public function updateTag($id)
    {
        $db = \Config\Database::connect();
        $name = $this->request->getPost('name');

        if ($name) {
            $db->table('tags')->where(['id' => $id, 'user_id' => auth()->id()])->update([
                'name' => $name
            ]);
        }

        return redirect()->to(url_to('user.contacts') . '#tab-segments')->with('success', 'Nama segmen berhasil diubah.');
    }

    private function triggerAutomations($contactId, $tagId)
    {
        $db = \Config\Database::connect();
        
        // Cari otomasi yang pemicunya adalah tag ini
        $automations = $db->table('automations')
            ->where('trigger_tag_id', $tagId)
            ->where('status', 'ACTIVE')
            ->get()->getResultArray();

        foreach ($automations as $auto) {
            // Ambil step pertama
            $firstStep = $db->table('automation_steps')
                ->where('automation_id', $auto['id'])
                ->orderBy('step_order', 'ASC')
                ->get()->getRowArray();

            if ($firstStep) {
                // Masukkan ke antrean otomasi
                $db->table('automation_queue')->insert([
                    'automation_id'   => $auto['id'],
                    'recipient_id'    => $contactId, // contact_id
                    'current_step_id' => $firstStep['id'],
                    'next_run_at'     => date('Y-m-d H:i:s', strtotime("+{$firstStep['delay_days']} days")),
                    'status'          => 'PENDING'
                ]);
            }
        }
    }
}
