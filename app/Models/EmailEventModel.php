<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailEventModel extends Model
{
    protected $table            = 'email_events';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'campaign_id',
        'recipient_id',
        'event_type',
        'event_at',
        'provider_message_id',
        'meta_json',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'campaign_id'          => 'required|is_natural_no_zero',
        'recipient_id'         => 'required|is_natural_no_zero',
        'event_type'           => 'required|in_list[queued,sent,delivered,opened,clicked,bounced,complained,failed]',
        'event_at'             => 'required|valid_date[Y-m-d H:i:s]',
        'provider_message_id'  => 'permit_empty|max_length[191]',
        'meta_json'            => 'permit_empty',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;
    protected $cleanValidationRules = true;
}
