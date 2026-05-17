<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReceiversTable extends Migration
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
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['Tim Internal', 'Vendor', 'Klien', 'Lainnya'],
            ],
            'nik' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'npwp' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => true,
            ],
            'bank_account' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addForeignKey('institution_id', 'institutions', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('receivers');
    }

    public function down(): void
    {
        $this->forge->dropTable('receivers', true);
    }
}
