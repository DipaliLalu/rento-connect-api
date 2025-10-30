<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CategoryMigration extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "category_id" => [
                "type" => "INT",
                "auto_increment" => true,
                "unsigned" => true,
            ],
            "category_name" => [
                "type" => "VARCHAR",
                "constraint" => 200,
                "null" => false,
            ],
            "created_by" => [
                "type" => "VARCHAR",
                "unsigned" => true,
                "null" => false,
            ],
            "heading" => [
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
            "category_image" => [
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
        ]);
        $this->forge->addPrimaryKey('category_id');

        $this->forge->createTable('categories');
    }

    public function down()
    {
        $this->forge->dropTable('categories');
    }
}
