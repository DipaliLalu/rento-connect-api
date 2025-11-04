<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BlogMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "id" => [
                "type" => "INT",
                "auto_increment" => true,
                "unsigned" => true,
            ],
            "title" => [
                "type" => "VARCHAR",
                "constraint" => 200,
                "null" => false,
            ],
            "slug" => [
                "type" => "VARCHAR",
                "constraint" => 200,
                "null" => false,
            ],
            "description" => [
                "type" => "TEXT",
                "null" => false,
            ],
            "image" => [
                "type" => "VARCHAR",
                "constraint" => 300,
                "null" => false,
            ],
            "active" => [
                "type" => "TINYINT",
                "default" => 1,
                "null" => false,
            ],
            "deleted" => [
                "type" => "TINYINT",
                "default" => 0,
                "null" => false,
            ],
            "metadata" => [
                "type" => "TEXT",
                "null" => false,
            ],
            "metatag" => [
                "type" => "TEXT",
                "null" => false,
            ],
            "created_at datetime default current_timestamp",
            "updated_at datetime default current_timestamp",
        ]);
        $this->forge->addKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('blogs');
    }

    public function down()
    {
        $this->forge->dropTable('blogs');
    }
}
