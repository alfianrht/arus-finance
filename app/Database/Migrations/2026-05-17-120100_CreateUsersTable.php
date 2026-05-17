<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
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
            'whatsapp' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'otp_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 6,
                'null'       => true,
            ],
            'otp_expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'otp_attempts' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'default'    => 0,
            ],
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['admin', 'operator'],
                'default'    => 'operator',
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'last_login_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addUniqueKey('whatsapp');
        $this->forge->addKey('institution_id');
        $this->forge->addForeignKey('institution_id', 'institutions', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('users');
    }

    public function down(): void
    {
        $this->forge->dropTable('users', true);
    }
}
