<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReportPositionsTable extends Migration
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
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'group' => [
                'type'       => 'ENUM',
                'constraint' => ['Laba Rugi', 'Neraca'],
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
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('institution_id');
        $this->forge->addForeignKey('institution_id', 'institutions', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('report_positions');
    }

    public function down(): void
    {
        $this->forge->dropTable('report_positions', true);
    }
}
