<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table            = 'activity_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['user_id', 'action', 'description', 'context', 'ip_address', 'user_agent', 'created_at'];

    protected $useTimestamps = false; // Kita handle manual di helper

    /**
     * Ambil log dengan data user
     */
    public function getLogs($limit = 100)
    {
        return $this->select('activity_logs.*, users.username')
                    ->join('users', 'users.id = activity_logs.user_id', 'left')
                    ->orderBy('activity_logs.created_at', 'DESC')
                    ->limit($limit)
                    ->get()->getResultArray();
    }
}
