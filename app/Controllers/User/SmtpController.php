<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\SenderAccountModel;

class SmtpController extends BaseController
{
    protected $smtpModel;

    public function __construct()
    {
        $this->smtpModel = new SenderAccountModel();
    }

    public function index()
    {
        $data = [
            'pageTitle' => 'Kredensial SMTP Saya',
            'accounts'  => $this->smtpModel->where('user_id', auth()->id())->findAll()
        ];
        return view('user/smtp/index', $data);
    }

    public function create()
    {
        return view('user/smtp/form', ['pageTitle' => 'Tambah SMTP']);
    }

    public function store()
    {
        $data = [
            'sender_name'     => $this->request->getPost('sender_name'),
            'sender_email'    => $this->request->getPost('sender_email'),
            'smtp_host'       => $this->request->getPost('smtp_host'),
            'smtp_port'       => $this->request->getPost('smtp_port'),
            'smtp_username'   => $this->request->getPost('smtp_username'),
            'encryption'      => $this->request->getPost('encryption'),
            'warmup_mode'     => $this->request->getPost('warmup_mode') == '1',
            'warmup_daily_limit' => $this->request->getPost('warmup_daily_limit'),
            'hourly_limit'    => $this->request->getPost('hourly_limit'),
            'imap_host'       => $this->request->getPost('imap_host'),
            'imap_port'       => $this->request->getPost('imap_port'),
            'imap_encryption' => $this->request->getPost('imap_encryption'),
            'user_id'         => auth()->id(),
            'type'            => 'INDIVIDUAL',
            'smtp_password'   => $this->request->getPost('smtp_password')
        ];
        
        // Enkripsi Password
        $data['smtp_password'] = base64_encode(service('encrypter')->encrypt($data['smtp_password']));

        if ($this->smtpModel->save($data)) {
            return redirect()->to(url_to('app.smtp'))->with('success', 'SMTP berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('errors', $this->smtpModel->errors());
    }

    public function edit($id)
    {
        // Pastikan user hanya bisa edit miliknya sendiri
        $account = $this->smtpModel->where('user_id', auth()->id())->find($id);

        if (!$account) return redirect()->to(url_to('app.smtp'))->with('error', 'Akses ditolak.');

        return view('user/smtp/edit', [
            'pageTitle' => 'Edit SMTP',
            'account'   => $account
        ]);
    }

    public function update($id)
    {
        $account = $this->smtpModel->where('user_id', auth()->id())->find($id);
        if (!$account) return redirect()->back()->with('error', 'Akses ditolak.');

        $data = $this->request->getPost();
        
        if (!empty($data['smtp_password'])) {
            $data['smtp_password'] = base64_encode(service('encrypter')->encrypt($data['smtp_password']));
        } else {
            unset($data['smtp_password']);
        }

        if ($this->smtpModel->update($id, $data)) {
            return redirect()->to(url_to('app.smtp'))->with('success', 'Update berhasil.');
        }

        return redirect()->back()->withInput()->with('errors', $this->smtpModel->errors());
    }


    public function delete($id)
    {
        $account = $this->smtpModel->where('user_id', auth()->id())->find($id);

        if (!$account) {
            return redirect()->back()->with('error', 'Akun SMTP tidak ditemukan.');
        }

        // Cegah hapus jika masih ada kampanye aktif yang menggunakan SMTP ini
        $db = \Config\Database::connect();
        $activeCount = $db->table('campaigns')
            ->where('sender_account_id', $id)
            ->whereIn('status', ['RUNNING', 'READY', 'SCHEDULED', 'PAUSED'])
            ->countAllResults();

        if ($activeCount > 0) {
            return redirect()->back()->with('error', "Tidak bisa dihapus: {$activeCount} kampanye aktif masih menggunakan SMTP ini.");
        }

        $this->smtpModel->delete($id);
        return redirect()->to(url_to('app.smtp'))->with('success', 'Akun SMTP berhasil dihapus.');
    }

    public function testConnection($id)
    {
        // Pastikan user hanya bisa tes SMTP miliknya sendiri
        $sender = $this->smtpModel->where('user_id', auth()->id())->find($id);

        if (!$sender) {
            return redirect()->back()->with('error', 'Akun tidak ditemukan atau bukan milik Anda.');
        }

        $password = service('encrypter')->decrypt(base64_decode($sender->smtp_password));

        $email = \Config\Services::email();
        $email->initialize([
            'protocol'   => 'smtp',
            'SMTPHost'   => $sender->smtp_host,
            'SMTPUser'   => $sender->smtp_username,
            'SMTPPass'   => $password,
            'SMTPPort'   => (int) $sender->smtp_port,
            'mailType'   => 'html',
        ]);

        $email->setFrom($sender->sender_email, $sender->sender_name);
        $email->setTo($sender->sender_email);
        $email->setSubject('Tes Koneksi SMTP - MailCore');
        $email->setMessage('Jika Anda menerima email ini, berarti konfigurasi SMTP & Enkripsi Anda <b>BERHASIL!</b>');

        if ($email->send()) {
            return redirect()->back()->with('success', 'Koneksi Sukses! Email tes telah dikirim ke ' . $sender->sender_email);
        }

        $debugger = $email->printDebugger(['headers', 'subject', 'body']);
        log_message('error', 'SMTP Test Failure: ' . $debugger);

        return redirect()->back()
            ->with('error', 'Gagal mengirim email. Silakan cek log debug di bawah.')
            ->with('email_debug', $debugger);
    }
}