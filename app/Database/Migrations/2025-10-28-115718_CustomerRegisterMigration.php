<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CustomerRegisterMigration extends Migration
{
    public function up()
    {
          $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
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
            'alternativecontact' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
                'unique'=>true,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'address' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'gstin' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'location' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'service_frequency' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
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
        $this->forge->createTable('customer_register');
    }
    
    public function down()
    {
        $this->forge->dropTable('customer_register');
    }
}
