<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGoogleSsoFieldsToUsersTable extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('email', 'users')) {
            $this->forge->addColumn('users', [
                'email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'name',
                ],
            ]);
        }

        if (! $this->db->fieldExists('google_id', 'users')) {
            $this->forge->addColumn('users', [
                'google_id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 191,
                    'null'       => true,
                    'after'      => 'whatsapp',
                ],
            ]);
        }

        if (! $this->db->fieldExists('auth_provider', 'users')) {
            $this->forge->addColumn('users', [
                'auth_provider' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'google_id',
                ],
            ]);
        }

        if (! $this->db->fieldExists('avatar_url', 'users')) {
            $this->forge->addColumn('users', [
                'avatar_url' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'auth_provider',
                ],
            ]);
        }

        if ($this->db->fieldExists('whatsapp', 'users')) {
            $this->forge->modifyColumn('users', [
                'whatsapp' => [
                    'name'       => 'whatsapp',
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                ],
            ]);
        }

        if (! $this->indexExists('users', 'users_email_unique')) {
            $this->db->query('CREATE UNIQUE INDEX users_email_unique ON users (email)');
        }

        if (! $this->indexExists('users', 'users_google_id_unique')) {
            $this->db->query('CREATE UNIQUE INDEX users_google_id_unique ON users (google_id)');
        }
    }

    public function down(): void
    {
        if ($this->indexExists('users', 'users_google_id_unique')) {
            $this->db->query('DROP INDEX users_google_id_unique ON users');
        }

        if ($this->indexExists('users', 'users_email_unique')) {
            $this->db->query('DROP INDEX users_email_unique ON users');
        }

        foreach (['avatar_url', 'auth_provider', 'google_id', 'email'] as $column) {
            if ($this->db->fieldExists($column, 'users')) {
                $this->forge->dropColumn('users', $column);
            }
        }

        if ($this->db->fieldExists('whatsapp', 'users')) {
            $this->forge->modifyColumn('users', [
                'whatsapp' => [
                    'name'       => 'whatsapp',
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => false,
                ],
            ]);
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = $this->db->query('SHOW INDEX FROM ' . $table . ' WHERE Key_name = ' . $this->db->escape($indexName))->getResultArray();

        return $result !== [];
    }
}
