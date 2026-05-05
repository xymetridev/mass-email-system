<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\I18n\Time;

class Users extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        
        $data = [
            'pageTitle' => 'Manajemen Pengguna',
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
        
        $user = new User([
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => bin2hex(random_bytes(16)), 
        ]);

        $userModel->save($user);

        $userId = $userModel->getInsertID();
        $newUser = $userModel->findById($userId);

        $role = $this->request->getPost('role');
        $newUser->addGroup($role);
        $newUser->activate();
        $userModel->save($newUser);
        $newUser->forcePasswordReset();

        // 🔥 LOGIKA GENERATE MAGIC LINK (Fix: Tanpa MagicLinkModel)
        try {
            /** @var UserIdentityModel $identityModel */
            $identityModel = model(UserIdentityModel::class);
            $identityModel->deleteIdentitiesByType($newUser, Session::ID_TYPE_MAGIC_LINK);

            helper('text');
            $token = random_string('crypto', 20);

            $identityModel->insert([
                'user_id' => $newUser->id,
                'type'    => Session::ID_TYPE_MAGIC_LINK,
                'secret'  => $token,
                'expires' => Time::now()->addSeconds(setting('Auth.magicLinkLifetime')),
            ]);
            
            $email = \Config\Services::email();
            $email->setTo($newUser->email);
            $email->setSubject(lang('Auth.magicLinkSubject'));
            $email->setMessage(view(config('Auth')->views['magic-link-email'], [
                'token' => $token,
                'user'  => $newUser,
            ]));

            if ($email->send()) {
                record_activity('ADMIN_CREATE_USER', "Admin membuat akun baru '{$newUser->username}' ({$newUser->email}) dan mengirimkan undangan Magic Link.");
                $message = "Akun {$newUser->username} berhasil dibuat dan email undangan Magic Link telah dikirim.";
            } else {
                $message = "Akun {$newUser->username} berhasil dibuat, namun GAGAL mengirim email undangan. Silakan cek log sistem.";
                log_message('error', "Gagal mengirim Magic Link ke {$newUser->email}. Debug: " . $email->printDebugger(['headers', 'subject']));
            }
        } catch (\Exception $e) {
            log_message('error', "Exception saat mengirim Magic Link: " . $e->getMessage());
            $message = "Akun {$newUser->username} berhasil dibuat, tapi sistem gagal memproses pengiriman email.";
        }

        return redirect()->back()->with('success', $message);
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

    /**
     * 🔥 Soft Delete User
     */
    public function delete($id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        $userModel->delete($user->id);
        
        record_activity('DELETE_USER', "Menghapus (soft-delete) akun '{$user->username}'.");

        return redirect()->back()->with('success', "Akun {$user->username} berhasil dihapus.");
    }

    /**
     * 🔥 API Fallback: Generate Magic Link untuk dicopy Admin (Fixed Version)
     */
    public function getMagicLink($id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);

        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User tidak ditemukan.']);
        }

        try {
            /** @var UserIdentityModel $identityModel */
            $identityModel = model(UserIdentityModel::class);
            $identityModel->deleteIdentitiesByType($user, Session::ID_TYPE_MAGIC_LINK);

            helper('text');
            $token = random_string('crypto', 20);

            $identityModel->insert([
                'user_id' => $user->id,
                'type'    => Session::ID_TYPE_MAGIC_LINK,
                'secret'  => $token,
                'expires' => Time::now()->addSeconds(setting('Auth.magicLinkLifetime')),
            ]);
            
            $link = url_to('verify-magic-link') . "?token={$token}";

            record_activity('ADMIN_GET_MAGIC_LINK', "Admin mengambil manual Magic Link untuk user '{$user->username}'.");

            return $this->response->setJSON([
                'success' => true,
                'link'    => $link
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Gagal generate link: ' . $e->getMessage()
            ]);
        }
    }
}