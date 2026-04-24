<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailQueueModel extends Model
{
    protected $table            = 'email_queue';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'campaign_id', 'recipient_id', 'sender_account_id',
        'to_email', 'subject', 'body',
        'status', 'attempt', 'last_error',
    ];

    protected $useTimestamps = true;
}
