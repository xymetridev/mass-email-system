<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class UserController extends BaseController
{
    public function profile()
    {
        return view('user/profile', [
            'pageTitle' => 'Profil Saya',
            'user'      => auth()->user()
        ]);
    }

    public function update()
    {
        $user = auth()->user();
        $rules = [
            'username' => "required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[users.username,id,{$user->id}]",
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $user->fill($this->request->getPost(['username']));
        
        $users = model('UserModel');
        $users->save($user);

        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function changePassword()
    {
        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[8]|strong_password',
            'confirm_pw'       => 'required|matches[new_password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $user = auth()->user();
        
        // Verifikasi password saat ini
        if (! $user->checkPassword($this->request->getPost('current_password'))) {
            return redirect()->back()->with('errors', ['current_password' => 'Password saat ini tidak valid.']);
        }

        $user->password = $this->request->getPost('new_password');
        
        $users = model('UserModel');
        $users->save($user);

        return redirect()->back()->with('success', 'Password berhasil diperbarui.');
    }
}