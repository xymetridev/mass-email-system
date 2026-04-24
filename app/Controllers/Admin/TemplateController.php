<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TemplateModel;

class TemplateController extends BaseController
{
    public function index()
    {
        $model = new TemplateModel();
        
        $data = [
            'pageTitle' => 'Pustaka Template',
            'templates' => $model->select('templates.*, campaigns.name as campaign_name')
                                 ->join('campaigns', 'campaigns.id = templates.campaign_id', 'left')
                                 ->findAll()
        ];

        return view('admin/templates/index', $data);
    }

    public function create()
    {
        $data = [
            'pageTitle' => 'Buat Template Master Baru',
            'availableTags' => ['name', 'email'] // Default tags
        ];
        return view('admin/templates/create', $data);
    }

    // Dipanggil via AJAX dari Wizard Step 3
    public function store()
    {
        $model = new TemplateModel();
        
        $name = $this->request->getPost('name');
        $html = $this->request->getPost('html');

        if (empty($name) || empty($html)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nama dan konten tidak boleh kosong.']);
        }

        $model->insert([
            'user_id'     => auth()->id(),
            'campaign_id' => null, // null berarti template umum/pustaka
            'name'        => $name,
            'content'     => $html
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Template berhasil disimpan!']);
    }

    // Dipanggil via AJAX dari Wizard Step 3
    public function show($id)
    {
        $db = \Config\Database::connect();
        $template = $db->table('templates')->where('id', $id)->get()->getRowArray();
        
        if ($template) {
            return $this->response->setJSON(['status' => 'success', 'data' => $template]);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Template tidak ditemukan.']);
    }

    public function edit($id)
    {
        $model = new TemplateModel();
        $template = $model->find($id);

        if (!$template) {
            return redirect()->to(url_to('admin.templates'))->with('error', 'Template tidak ditemukan.');
        }

        return view('admin/templates/edit', [
            'pageTitle' => 'Edit Template Master',
            'template'  => $template,
            'availableTags' => ['name', 'email']
        ]);
    }

    public function update($id)
    {
        $model = new TemplateModel();
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
}