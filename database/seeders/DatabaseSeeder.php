<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            DistributorSeeder::class,
            ServiceSeeder::class,
            SparepartSeeder::class,
            TransactionalSeeder::class,
            HeroSeeder::class,
        ]);
    }
}
