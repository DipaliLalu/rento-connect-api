<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSubcategoriesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'subcategory_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'category_slug' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'subcategory_name' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'created_by' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'heading' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'slug' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'subcategory_image' => [
                'type' => 'VARCHAR',
                'constraint' => 300,
                'null' => true,
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
            'metadata' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'metatag' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            "created_at datetime default current_timestamp",
        ]);

        $this->forge->addKey('subcategory_id', true);
        $this->forge->createTable('subcategories');
    }

    public function down()
    {
        $this->forge->dropTable('subcategories');
    }
}
