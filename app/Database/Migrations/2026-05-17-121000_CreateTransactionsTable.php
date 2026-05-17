<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactionsTable extends Migration
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
            'book_period_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['masuk', 'keluar', 'pindah', 'honor'],
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'admin_fee' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'unit_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'activity_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'from_account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'to_account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'receiver_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'transaction_date' => [
                'type' => 'DATE',
            ],
            'transaction_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'proof_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
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
        $this->forge->addKey('unit_id');
        $this->forge->addKey('activity_id');
        $this->forge->addKey('category_id');
        $this->forge->addKey('from_account_id');
        $this->forge->addKey('to_account_id');
        $this->forge->addKey('receiver_id');
        $this->forge->addKey('created_by');
        $this->forge->addForeignKey('institution_id', 'institutions', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('book_period_id', 'book_periods', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->addForeignKey('unit_id', 'units', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('activity_id', 'activities', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('category_id', 'transaction_categories', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->addForeignKey('from_account_id', 'accounts', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->addForeignKey('to_account_id', 'accounts', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->addForeignKey('receiver_id', 'receivers', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('transactions');
    }

    public function down(): void
    {
        $this->forge->dropTable('transactions', true);
    }
}
