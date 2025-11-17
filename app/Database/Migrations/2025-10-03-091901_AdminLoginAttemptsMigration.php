<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AdminLoginAttemptsMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "id" => [
                "type" => "INT",
                "auto_increment" => true,
                "unsigned" => true,
            ],
            "ip_address" => [
                "type" => "VARCHAR",
                "constraint" => 15,
                "null" => false,
            ],
            "user_id" => [
                "type" => "INT",
                "null" => false,
            ],
            "username" => [
                "type" => "VARCHAR",
                "constraint" => 150,
                "null" => false,
            ],
            "login_time datetime default current_timestamp",
            "logout_time" => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('admin_login_attempts');
    }

    public function down()
    {
        $this->forge->dropTable('admin_login_attempts');
    }
}
