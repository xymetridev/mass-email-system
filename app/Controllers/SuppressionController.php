<?php

namespace App\Controllers;

use App\Models\SuppressionModel;
use CodeIgniter\Controller;

class SuppressionController extends BaseController
{
    public function index()
    {
        $model = new SuppressionModel();
        
        $search = $this->request->getGet('search') ?? '';
        
        // Admin bisa lihat semua, user biasa cuma miliknya
        $userId = auth()->user()->inGroup('admin') ? null : auth()->id();
        
        $suppressions = $model->getList($userId, $search)->paginate(20);
        
        $data = [
            'pageTitle'    => 'Blacklist / Suppression List',
            'activeMenu'   => 'suppressions',
            'suppressions' => $suppressions,
            'pager'        => $model->pager,
            'search'       => $search
        ];

        return view('suppressions/index', $data);
    }

    public function store()
    {
        $model = new SuppressionModel();
        
        $email = $this->request->getPost('email');
        $reason = $this->request->getPost('reason') ?: 'Manual Blacklist';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Format email tidak valid.');
        }

        // Cek duplikat
        $existing = $model->where('email', $email)->first();
        if ($existing) {
            return redirect()->back()->with('error', 'Email ini sudah ada di dalam Blacklist.');
        }

        $model->insert([
            'user_id'    => auth()->id(),
            'email'      => strtolower($email),
            'reason'     => $reason,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        record_activity('ADD_SUPPRESSION', "Menambahkan email '$email' ke dalam daftar hitam (Alasan: $reason).");

        return redirect()->back()->with('success', 'Email berhasil dimasukkan ke daftar hitam.');
    }

    public function delete($id)
    {
        $model = new SuppressionModel();
        
        $builder = $model->where('id', $id);
        if (!auth()->user()->inGroup('admin')) {
            $builder->where('user_id', auth()->id());
        }
        
        $data = $builder->first();

        if ($data) {
            $model->delete($id);
            record_activity('DELETE_SUPPRESSION', "Menghapus email '{$data['email']}' dari daftar hitam.");
            return redirect()->back()->with('success', 'Email berhasil dihapus dari daftar hitam (Whitelist).');
        }

        return redirect()->back()->with('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
    }
}
