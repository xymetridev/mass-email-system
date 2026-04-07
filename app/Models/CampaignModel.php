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
        'name',
        'subject',
        'sender_name',
        'sender_email',
        'reply_to_email',
        'body_html',
        'body_text',
        'status',
        'scheduled_at',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name'          => 'required|max_length[150]|is_unique[campaigns.name,id,{id}]',
        'subject'       => 'required|max_length[255]',
        'sender_name'   => 'required|max_length[150]',
        'sender_email'  => 'required|valid_email|max_length[255]',
        'reply_to_email'=> 'permit_empty|valid_email|max_length[255]',
        'body_html'     => 'permit_empty',
        'body_text'     => 'permit_empty',
        'status'        => 'required|in_list[draft,scheduled,sending,completed,cancelled]',
        'scheduled_at'  => 'permit_empty|valid_date[Y-m-d H:i:s]',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;
    protected $cleanValidationRules = true;
}
