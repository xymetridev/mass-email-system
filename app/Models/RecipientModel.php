<?php

namespace App\Models;

use CodeIgniter\Model;

class RecipientModel extends Model
{
    protected $table            = 'recipients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'campaign_id',
        'email',
        'first_name',
        'last_name',
        'status',
        'sent_at',
        'opened_at',
        'clicked_at',
        'bounced_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'campaign_id' => 'required|is_natural_no_zero',
        'email'       => 'required|valid_email|max_length[255]',
        'first_name'  => 'permit_empty|max_length[100]',
        'last_name'   => 'permit_empty|max_length[100]',
        'status'      => 'required|in_list[pending,queued,sent,opened,clicked,bounced,failed,unsubscribed]',
        'sent_at'     => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'opened_at'   => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'clicked_at'  => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'bounced_at'  => 'permit_empty|valid_date[Y-m-d H:i:s]',
    ];

    protected $validationMessages = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $beforeInsert = ['normalizeEmail'];
    protected $beforeUpdate = ['normalizeEmail'];


    private function normalizeEmail(array $data): array
    {
        if (isset($data['data']['email']) && is_string($data['data']['email'])) {
            $data['data']['email'] = strtolower(trim($data['data']['email']));
        }

        return $data;
    }
}
