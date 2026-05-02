<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Dimas Argadipraja',
            'email' => 'admin@speedline.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '081234567891',
            'email_verified_at' => now(),
        ]);

        // Owner
        User::create([
            'name' => 'Hendra Wijayakusuma',
            'email' => 'owner@speedline.id',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'phone' => '082145678902',
            'email_verified_at' => now(),
        ]);

        // Mekanik
        User::create([
            'name' => 'Sujatmiko Baskoro',
            'email' => 'mekanik@speedline.id',
            'password' => Hash::make('password'),
            'role' => 'mekanik',
            'phone' => '083156789013',
            'email_verified_at' => now(),
        ]);

        // Kasir
        User::create([
            'name' => 'Sekar Ayu Kirana',
            'email' => 'kasir@speedline.id',
            'password' => Hash::make('password'),
            'role' => 'kasir',
            'phone' => '084167890124',
            'email_verified_at' => now(),
        ]);

        // Customer
        User::create([
            'name' => 'Gunawan Saputra',
            'email' => 'customer@speedline.id',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '085178901235',
            'email_verified_at' => now(),
        ]);
    }
}
