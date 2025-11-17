<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Permission extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'permission_id'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'permission_name' => ['type' => 'VARCHAR', 'constraint' => 100],
        ]);
        $this->forge->addKey('permission_id', true);
        $this->forge->createTable('permissions');
    }
    
    public function down()
    {
        $this->forge->dropTable('permissions');
    }
}
