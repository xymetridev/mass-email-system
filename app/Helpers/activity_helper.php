<?php

if (!function_exists('record_activity')) {
    /**
     * Mencatat aktivitas pengguna ke dalam database
     * 
     * @param string $action Nama aksi (misal: CREATE_CAMPAIGN)
     * @param string $description Penjelasan aktivitas
     * @param array $context Data tambahan dalam bentuk array (opsional)
     */
    function record_activity(string $action, string $description, array $context = [])
    {
        $db = \Config\Database::connect();
        $request = \Config\Services::request();

        $data = [
            'user_id'     => auth()->id(),
            'action'      => $action,
            'description' => $description,
            'context'     => !empty($context) ? json_encode($context) : null,
            'ip_address'  => $request->getIPAddress(),
            'user_agent'  => $request->getUserAgent()->getAgentString(),
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        $db->table('activity_logs')->insert($data);
    }
}
