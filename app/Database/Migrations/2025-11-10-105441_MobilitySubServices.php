<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MobilitySubServices extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "id" => [
                "type" => "INT",
                "auto_increment" => true,
                "unsigned" => true,
            ],
            "slug" => [
                "type" => "VARCHAR",
                "constraint" => 200,
                "null" => false,
                "unique" => true,
            ],
            "display_name" => [
                "type" => "VARCHAR",
                "constraint" => 200,
                "null" => false,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('mobilitysubservices');
    }

    public function down()
    {
        $this->forge->dropTable('mobilitysubservices');
    }
}
