<?php

namespace App\Models;

use CodeIgniter\Model;

class SuppressionModel extends Model
{
    protected $table            = 'suppression_list';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['user_id', 'email', 'reason', 'created_at'];

    // Menampilkan daftar dengan pagination & pencarian
    public function getList($userId = null, $search = '')
    {
        $builder = $this->builder();
        
        if ($userId) {
            $builder->where('user_id', $userId);
        }

        if ($search) {
            $builder->groupStart()
                    ->like('email', $search)
                    ->orLike('reason', $search)
                    ->groupEnd();
        }

        $builder->orderBy('created_at', 'DESC');
        
        return $this;
    }
}
