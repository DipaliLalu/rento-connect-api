<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['permission_name' => 'user.create'],
            ['permission_name' => 'user.edit'],
            ['permission_name' => 'user.view'],
          
            ['permission_name' => 'category.create'],
            ['permission_name' => 'category.edit'],
            ['permission_name' => 'category.view'],
          
        ];

        $this->db->table('permissions')->insertBatch($data);
    }
}
