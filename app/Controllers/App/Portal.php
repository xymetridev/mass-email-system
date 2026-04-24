<?php

namespace App\Controllers\App;

use App\Controllers\BaseController;

class Portal extends BaseController
{
    public function smtp()
    {
        return view('pages/smtp', [
            'pageTitle' => 'Akun Pengirim / SMTP',
            'pageSubtitle' => 'Kelola koneksi SMTP untuk pengiriman email.',
        ]);
    }

    public function campaignList()
    {
        return view('pages/campaign-list', [
            'pageTitle' => 'Daftar Kampanye',
            'pageSubtitle' => 'Lihat semua kampanye email yang sudah dibuat.',
        ]);
    }

    public function campaignCreate()
    {
        return view('pages/campaign-create', [
            'pageTitle' => 'Buat Kampanye Baru',
            'pageSubtitle' => 'Mulai susun kampanye email baru.',
        ]);
    }

    public function campaignLogs()
    {
        return view('pages/campaign-logs', [
            'pageTitle' => 'Log Kampanye',
            'pageSubtitle' => 'Pantau riwayat pengiriman dan status kampanye.',
        ]);
    }

    public function profile()
    {
        return view('pages/profile', [
            'pageTitle' => 'Profil',
            'pageSubtitle' => 'Informasi akun pengguna yang sedang login.',
        ]);
    }
}
