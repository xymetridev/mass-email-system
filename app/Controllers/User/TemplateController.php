<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\TemplateModel;

class TemplateController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        // Ambil template milik sendiri ATAU milik Admin (Global)
        $templates = $db->table('templates')
            ->select('templates.*, campaigns.name as campaign_name')
            ->join('campaigns', 'campaigns.id = templates.campaign_id', 'left')
            ->join('auth_groups_users', 'auth_groups_users.user_id = templates.user_id', 'left')
            ->where('templates.user_id', auth()->id())
            ->orWhere('auth_groups_users.group', 'admin')
            ->groupBy('templates.id')
            ->orderBy('templates.id', 'DESC')
            ->get()->getResultArray();

        $data = [
            'pageTitle' => 'Pustaka Template Saya',
            'templates' => $templates
        ];

        return view('user/templates/index', $data);
    }

    public function create()
    {
        return view('user/templates/create', [
            'pageTitle' => 'Buat Template Baru',
            'availableTags' => ['name', 'email']
        ]);
    }

    public function store()
    {
        $model = new \App\Models\TemplateModel();
        
        $name = $this->request->getPost('name');
        $html = $this->request->getPost('html');

        if (empty($name) || empty($html)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nama dan konten tidak boleh kosong.']);
        }

        $model->insert([
            'user_id'     => auth()->id(),
            'campaign_id' => null,
            'name'        => $name,
            'content'     => $html
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Template berhasil disimpan ke pustaka pribadi!']);
    }

    public function edit($id)
    {
        $model = new \App\Models\TemplateModel();
        $template = $model->where('user_id', auth()->id())->find($id);

        if (!$template) {
            return redirect()->to(url_to('user.templates'))->with('error', 'Template tidak ditemukan atau bukan milik Anda.');
        }

        return view('user/templates/edit', [
            'pageTitle' => 'Edit Template Pribadi',
            'template'  => $template,
            'availableTags' => ['name', 'email']
        ]);
    }

    public function update($id)
    {
        $model = new \App\Models\TemplateModel();
        
        // Pastikan hanya bisa update milik sendiri
        $template = $model->where('user_id', auth()->id())->find($id);
        if (!$template) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak.']);
        }

        $name = $this->request->getPost('name');
        $html = $this->request->getPost('html');

        if (empty($name) || empty($html)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nama dan konten tidak boleh kosong.']);
        }

        $model->update($id, [
            'name'    => $name,
            'content' => $html
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Template berhasil diperbarui!']);
    }

    public function show($id)
    {
        $db = \Config\Database::connect();
        $template = $db->table('templates')
            ->where('id', $id)
            ->get()->getRowArray();

        if ($template) {
            return $this->response->setJSON(['status' => 'success', 'data' => $template]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Template tidak ditemukan.']);
    }
}