<?php

namespace App\Models;

use CodeIgniter\Model;

class CampaignModel extends Model
{
    protected $table            = 'campaigns';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'name',
        'subject',
        'sender_account_id',
        'default_name',
        'status',
        'batch_size',
        'completed_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'user_id'       => 'required|is_natural_no_zero',
        'name'          => 'required|max_length[150]',
        'subject'       => 'required|max_length[255]',
        'sender_account_id' => 'permit_empty|is_natural_no_zero',
        'default_name'  => 'permit_empty|max_length[150]',
        'status'        => 'required|in_list[DRAFT,READY,RUNNING,PAUSED,CANCELED,COMPLETED,FAILED]',
        'batch_size'    => 'required|integer|greater_than[0]',
        'completed_at'  => 'permit_empty|valid_date[Y-m-d H:i:s]',
    ];

    protected $validationMessages = [
        'name.required' => 'The campaign name is required.',
        'name.max_length' => 'The campaign name must be less than 150 characters.',
        'subject.required' => 'The campaign subject is required.',
        'subject.max_length' => 'The campaign subject must be less than 255 characters.',
        'sender_account_id.is_natural_no_zero' => 'The sender account must be a valid ID.',
    ];

    protected $skipValidation     = false;
    protected $cleanValidationRules = true;

    public function forUser(UserModel $user)
    {
        return $this->where('user_id', $user->id);
    }
}
