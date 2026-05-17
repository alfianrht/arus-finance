<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactionCategoriesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'institution_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'kind' => [
                'type'       => 'ENUM',
                'constraint' => ['Masuk', 'Keluar'],
            ],
            'report_position_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'is_quick' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'chip_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('institution_id');
        $this->forge->addKey('report_position_id');
        $this->forge->addForeignKey('institution_id', 'institutions', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('report_position_id', 'report_positions', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->createTable('transaction_categories');
    }

    public function down(): void
    {
        $this->forge->dropTable('transaction_categories', true);
    }
}
