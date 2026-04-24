<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Entities\User;

class Users extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        
        $data = [
            'pageTitle' => 'Manajemen Pengguna',
            // Kita ambil user beserta grupnya
            'users'     => $userModel->orderBy('created_at', 'DESC')->paginate(10),
            'pager'     => $userModel->pager,
        ];

        return view('admin/users/index', $data);
    }

    // Tambah Akun Baru (Oleh Admin)
    public function store()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[auth_identities.secret]',
            'role'     => 'required|in_list[admin,user]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        
        // Buat entitas user dengan password super acak agar aman
        // Karyawan nantinya wajib menggunakan fitur "Magic Link" untuk login pertama kali
        $user = new User([
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => bin2hex(random_bytes(16)), 
        ]);

        $userModel->save($user);

        // Ambil ID user yang baru dibuat
        $userId = $userModel->getInsertID();
        $newUser = $userModel->findById($userId);

        // Tambahkan user ke dalam grup/role yang dipilih
        $role = $this->request->getPost('role');
        $newUser->addGroup($role);
        
        // Aktifkan langsung akunnya (karena dibuat oleh admin, tidak perlu email aktivasi)
        $newUser->activate();

        $newUser->forcePasswordReset();
        $userModel->save($newUser);

        record_activity('ADMIN_CREATE_USER', "Admin membuat akun baru '{$newUser->username}' ({$newUser->email}) dengan role '{$role}'.");

        return redirect()->back()->with('success', "Akun {$newUser->username} berhasil dibuat. Silakan instruksikan user untuk login via Magic Link.");
    }

    // Fitur untuk Ban/Unban User (Keamanan)
    public function toggleBan($id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);

        if ($user->isBanned()) {
            $user->unBan();
            record_activity('UNBAN_USER', "Mengaktifkan kembali akun '{$user->username}'.");
            return redirect()->back()->with('success', "Akun {$user->username} telah diaktifkan kembali.");
        }

        $user->ban();
        record_activity('BAN_USER', "Menonaktifkan akun '{$user->username}'.");
        return redirect()->back()->with('success', "Akun {$user->username} berhasil dinonaktifkan.");
    }
}