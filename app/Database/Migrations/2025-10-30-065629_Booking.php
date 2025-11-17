<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Booking extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'customer_id' => [
                'type' => 'INT',
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'contact' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'subcategory' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'gstin' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            "description" => [
                "type" => "TEXT",
                "null" => false,
            ],
            'location' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'startDate' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'endDate' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'null' => false,
            ],
            'deleted' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
            ],
            "created_at datetime default current_timestamp",
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('bookings');
    }
    
    public function down()
    {
        $this->forge->dropTable('bookings');
    }
}
