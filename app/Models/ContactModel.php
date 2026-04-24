<?php

namespace App\Models;

use CodeIgniter\Model;

class ContactModel extends Model
{
    protected $table            = 'contacts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'email', 'name', 'created_at', 'updated_at'];

    protected $useTimestamps = true;

    /**
     * Get contacts with their tags
     */
    public function getWithTags($userId)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }
}
