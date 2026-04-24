<?php

namespace App\Controllers\Admin;

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
            'pageTitle' => 'Master SMTP Bisnis',
            'accounts'  => $this->smtpModel->findAll()
        ];
        return view('admin/smtp/index', $data);
    }

    public function create()
    {
        return view('admin/smtp/form', ['pageTitle' => 'Tambah Akun SMTP']);
    }

    public function store()
    {
        $model = new SenderAccountModel();
        
        $data = [
            'sender_name'     => $this->request->getPost('sender_name'),
            'sender_email'    => $this->request->getPost('sender_email'),
            'smtp_host'       => $this->request->getPost('smtp_host'),
            'smtp_port'       => $this->request->getPost('smtp_port'),
            'smtp_username'   => $this->request->getPost('smtp_username'),
            'smtp_password'   => $this->request->getPost('smtp_password'),
            'smtp_encryption' => $this->request->getPost('smtp_encryption'),
            'hourly_limit'    => $this->request->getPost('hourly_limit'),
            'imap_host'       => $this->request->getPost('imap_host'),
            'imap_port'       => $this->request->getPost('imap_port'),
            'imap_encryption' => $this->request->getPost('imap_encryption'),
        ];
        $data['user_id'] = auth()->id(); // Tetapkan pemilik akun
        $data['type']    = 'BUSINESS';

        $data['smtp_password'] = base64_encode(service('encrypter')->encrypt($data['smtp_password']));

        if ($model->save($data)) {
            return redirect()->to(url_to('admin.smtp.index'))->with('success', 'Akun SMTP berhasil disimpan.');
        }

        return redirect()->back()->withInput()->with('errors', $model->errors());
    }

    public function edit($id)
    {
        $model = new SenderAccountModel();
        $account = $model->find($id);

        if (!$account) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/smtp/edit', [
            'pageTitle' => 'Edit Akun SMTP',
            'account'   => $account
        ]);
    }

    public function update($id)
    {
        $model = new SenderAccountModel();
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
        ];

        // Hanya update password jika diisi
        if ($this->request->getPost('smtp_password')) {
            $data['smtp_password'] = base64_encode(service('encrypter')->encrypt($this->request->getPost('smtp_password')));
        }

        if ($model->update($id, $data)) {
            return redirect()->to(url_to('admin.smtp.index'))->with('success', 'Akun SMTP berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('errors', $model->errors());
    }

    public function delete($id)
    {
        $model = new SenderAccountModel();
        $account = $model->find($id);

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

        $model->delete($id);
        return redirect()->to(url_to('admin.smtp.index'))->with('success', 'Akun berhasil dihapus.');
    }

    public function testConnection($id)
    {
        $sender = (new SenderAccountModel())->find($id);

        if (!$sender) {
            return redirect()->back()->with('error', 'Akun SMTP tidak ditemukan.');
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
        log_message('error', 'Admin SMTP Test Failure: ' . $debugger);

        return redirect()->back()
            ->with('error', 'Gagal mengirim email tes.')
            ->with('email_debug', $debugger);
    }
}