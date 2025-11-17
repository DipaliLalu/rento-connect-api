<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['role_name' => 'Operation Head'],
            ['role_name' => 'HR'],
        ];

        $this->db->table('roles')->insertBatch($data);
    }
}
