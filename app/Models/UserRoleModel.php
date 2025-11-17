<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $table            = 'user_roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $allowedFields    = [
        'user_id',
        'role_id',
        'category_id'
    ];

    // Get roles assigned to user with role name + category name
    public function getUserRoles($userId)
    {
        return $this->select('user_roles.*, roles.role_name, categories.category_name')
                    ->join('roles', 'roles.role_id = user_roles.role_id')
                    ->join('categories', 'categories.category_id = user_roles.category_id', 'left')
                    ->where('user_roles.user_id', $userId)
                    ->findAll();
    }
}
