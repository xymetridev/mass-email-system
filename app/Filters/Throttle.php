<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class Throttle implements FilterInterface
{
    /**
     * Membatasi jumlah permintaan (Rate Limiting)
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $throttler = Services::throttler();

        // Batasi 10 request per menit (10, 60) per IP Address
        // Anda bisa menyesuaikan angkanya di sini jika dirasa terlalu ketat
        if ($throttler->check(md5($request->getIPAddress()), 10, 60) === false) {
            return Services::response()->setStatusCode(429)->setBody('Terlalu banyak permintaan. Silakan tunggu sebentar.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi setelah request
    }
}
