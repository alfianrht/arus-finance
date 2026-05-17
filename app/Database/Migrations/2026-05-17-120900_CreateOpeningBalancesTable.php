<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOpeningBalancesTable extends Migration
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
            'account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'book_period_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'report_position_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'source_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
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
        $this->forge->addKey('book_period_id');
        $this->forge->addKey('report_position_id');
        $this->forge->addForeignKey('institution_id', 'institutions', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('book_period_id', 'book_periods', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('report_position_id', 'report_positions', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('opening_balances');
    }

    public function down(): void
    {
        $this->forge->dropTable('opening_balances', true);
    }
}
