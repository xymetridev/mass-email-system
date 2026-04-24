<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Shield\Models\UserModel;

class AuthController extends BaseController
{
    /**
     * Menampilkan halaman set password baru untuk user yang dipaksa reset.
     */
    public function setPasswordView()
    {
        // Pastikan user memang butuh reset password
        if (! auth()->user()->forcePasswordReset) {
            return redirect()->to('/');
        }

        return view('auth_custom/CodeIgniterShield/set_password', [
            'pageTitle' => 'Set Password Baru'
        ]);
    }

    /**
     * Memproses pembaruan password.
     */
    public function setPasswordUpdate()
    {
        $rules = [
            'password'         => 'required|min_length[8]|strong_password',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $user = auth()->user();

        // Update password dan matikan flag forcePasswordReset
        $user->password = $this->request->getPost('password');
        
        // Di Shield, kita bisa mematikan flag ini secara manual
        $user->forcePasswordReset = false;
        
        if ($userModel->save($user)) {
            return redirect()->to('/')->with('success', 'Password berhasil disimpan. Selamat datang di Dashboard!');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui password. Silakan coba lagi.');
    }
}
