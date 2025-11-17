<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsers extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'user_id'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'username'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'password'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'mobile'    => ['type' => 'VARCHAR', 'constraint' => 15, 'null' => true],
            'email'     => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'id_proof'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            "created_at datetime default current_timestamp",
            'roles' => [
                "type" => "TEXT",
                "null" => true,
            ],
            'permission' => [
                "type" => "TEXT",
                "null" => true,
            ],

        ]);
        $this->forge->addKey('user_id', true);
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
