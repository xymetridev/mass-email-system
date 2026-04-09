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
        'claimed_by',
        'claimed_at',
        'sent_at',
        'retry_count',
        'last_error',
        'last_attempt_at',
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
        'status'      => 'required|in_list[PENDING,SENT,FAILED]',
        'sent_at'     => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'retry_count' => 'required|integer|greater_than_equal_to[0]',
        'last_attempt_at' => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'last_error'  => 'permit_empty|max_length[1000]',
    ];

    protected $validationMessages = [
        'campaign_id.required' => 'The campaign ID is required.',
        'campaign_id.is_natural_no_zero' => 'The campaign ID must be a positive integer.',
        'email.required' => 'The email is required.',
        'email.valid_email' => 'The email must be a valid email address.',
        'email.max_length' => 'The email must be less than 255 characters.',
        'first_name.max_length' => 'The first name must be less than 100 characters.',
        'last_name.max_length' => 'The last name must be less than 100 characters.',
        'status.required' => 'The status is required.',
        'status.in_list' => 'The status must be a valid status.',
        'sent_at.valid_date' => 'The sent date must be a valid date.',
        'retry_count.required' => 'The retry count is required.',
        'retry_count.integer' => 'The retry count must be an integer.',
        'retry_count.greater_than_equal_to' => 'The retry count must be 0 or more.',
    ];

}