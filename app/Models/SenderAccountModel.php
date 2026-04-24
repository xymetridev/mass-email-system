<?php

namespace App\Models;

use CodeIgniter\Model;

class SenderAccountModel extends Model
{

    protected $table            = 'sender_accounts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object'; // Object lebih rapi untuk akses properti
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'sender_name', 'sender_email', 'smtp_host', 'smtp_port', 
        'smtp_username', 'smtp_password', 'encryption',
        'warmup_mode', 'warmup_daily_limit', 'warmup_sent_today', 'warmup_last_date',
        'hourly_limit', 'sent_this_hour', 'last_hour_reset',
        'imap_host', 'imap_port', 'imap_encryption'
    ];

    // protected bool $allowEmptyInserts = false;
    // protected bool $updateOnlyChanged = true;

    // protected array $casts = [];
    // protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

    // // Validation
    // protected $validationRules      = [];
    // protected $validationMessages   = [];
    // protected $skipValidation       = false;
    // protected $cleanValidationRules = true;

    // // Callbacks
    // protected $allowCallbacks = true;
    // protected $beforeInsert   = [];
    // protected $afterInsert    = [];
    // protected $beforeUpdate   = [];
    // protected $afterUpdate    = [];
    // protected $beforeFind     = [];
    // protected $afterFind      = [];
    // protected $beforeDelete   = [];
    // protected $afterDelete    = [];
}
