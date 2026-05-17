<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AppSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ArusDefaultSeeder::class);
        $this->call(ArusOperationalSeeder::class);
    }
}
