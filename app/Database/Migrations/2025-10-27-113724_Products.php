<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Product extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "product_id" => [
                "type" => "INT",
                "auto_increment" => true,
                "unsigned" => true,
            ],
            "product_name" => [
                "type" => "VARCHAR",
                "constraint" => 200,
                "null" => false,
            ],
            "price_hour" => [
                "type" => "DECIMAL",
                "constraint" => "10,2",
                "null" => false,
            ],
            "price_day" => [
                "type" => "DECIMAL",
                "constraint" => "10,2",
                "null" => false,
            ],
            "slug" => [
                "type" => "VARCHAR",
                "constraint" => 200,
                "null" => false,
                "unique" => true,
            ],
            "description" => [
                "type" => "TEXT",
                "null" => false,
            ],
            "product_image" => [
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
        ]);
        $this->forge->addKey('product_id', true);
        $this->forge->createTable('products');
    }

    public function down()
    {
        $this->forge->dropTable('products');
    }
}
