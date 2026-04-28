<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class LogController extends BaseController
{
    public function index()
    {
        $logModel = new \App\Models\ActivityLogModel();
        
        $logPath = WRITEPATH . 'logs/';
        // Ambil file log harian, urutkan dari yang terbaru
        $files = [];
        if (is_dir($logPath)) {
            $files = array_diff(scandir($logPath, SCANDIR_SORT_DESCENDING), array('.', '..', 'index.html', '.htaccess'));
        }
        
        $data = [
            'pageTitle'    => 'System & Activity Logs',
            'logFiles'     => $files,
            'activityLogs' => $logModel->getLogs(200) // Ambil 200 log terbaru
        ];
        return view('admin/logs/index', $data);
    }

    public function view($fileName)
    {
        // Sanitasi: cegah path traversal (../../../etc/passwd)
        $fileName = basename($fileName);

        // Hanya izinkan file log harian atau file log worker
        if (!preg_match('/^(log-\d{4}-\d{2}-\d{2}|worker\.(out|err))\.log$/', $fileName)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nama file tidak valid.']);
        }

        $filePath = WRITEPATH . 'logs/' . $fileName;
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            return $this->response->setJSON(['status' => 'success', 'content' => $content]);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'File tidak ditemukan.']);
    }
}