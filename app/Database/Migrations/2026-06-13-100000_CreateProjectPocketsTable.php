<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectPocketsTable extends Migration
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
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'pocket_type' => [
                'type'       => 'ENUM',
                'constraint' => ['main', 'execution'],
                'default'    => 'execution',
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'contract_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'contract_terms_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
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
        $this->forge->addKey('unit_id');
        $this->forge->addKey('activity_id');
        $this->forge->addKey('pocket_type');
        $this->forge->addKey(['activity_id', 'slug']);
        $this->forge->addForeignKey('institution_id', 'institutions', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('unit_id', 'units', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('activity_id', 'activities', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('project_pockets');

        $this->forge->addColumn('transactions', [
            'project_pocket_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'activity_id',
            ],
        ]);

        $this->forge->addColumn('transactions', [
            'counter_project_pocket_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'project_pocket_id',
            ],
        ]);

        $this->db->query('ALTER TABLE `transactions` ADD KEY `transactions_project_pocket_id_index` (`project_pocket_id`)');
        $this->db->query('ALTER TABLE `transactions` ADD KEY `transactions_counter_project_pocket_id_index` (`counter_project_pocket_id`)');
        $this->db->query('ALTER TABLE `transactions` ADD CONSTRAINT `transactions_project_pocket_id_foreign` FOREIGN KEY (`project_pocket_id`) REFERENCES `project_pockets`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT');
        $this->db->query('ALTER TABLE `transactions` ADD CONSTRAINT `transactions_counter_project_pocket_id_foreign` FOREIGN KEY (`counter_project_pocket_id`) REFERENCES `project_pockets`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT');
    }

    public function down(): void
    {
        if ($this->db->tableExists('transactions')) {
            $this->forge->dropForeignKey('transactions', 'transactions_counter_project_pocket_id_foreign');
            $this->forge->dropForeignKey('transactions', 'transactions_project_pocket_id_foreign');
            $this->db->query('ALTER TABLE `transactions` DROP KEY `transactions_counter_project_pocket_id_index`');
            $this->db->query('ALTER TABLE `transactions` DROP KEY `transactions_project_pocket_id_index`');
            $this->forge->dropColumn('transactions', ['counter_project_pocket_id', 'project_pocket_id']);
        }

        $this->forge->dropTable('project_pockets', true);
    }
}
