<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ['service_name' => 'Tune-Up Mesin Intensif', 'description' => 'Pembersihan menyeluruh sistem pembakaran untuk performa maksimal', 'price' => 750000, 'estimated_time' => 120],
            ['service_name' => 'Ganti Oli & Filter', 'description' => 'Penggantian oli sintetik berkualitas beserta filter oli baru', 'price' => 350000, 'estimated_time' => 30],
            ['service_name' => 'Servis Rem Menyeluruh', 'description' => 'Pengecekan dan penggantian kampas rem serta pembersihan piringan', 'price' => 500000, 'estimated_time' => 60],
            ['service_name' => 'Kuras Oli Transmisi', 'description' => 'Penggantian total cairan transmisi agar perpindahan gigi tetap halus', 'price' => 1200000, 'estimated_time' => 180],
            ['service_name' => 'Spooring & Balancing 3D', 'description' => 'Penyelarasan roda menggunakan teknologi 3D terbaru', 'price' => 450000, 'estimated_time' => 90],
            ['service_name' => 'Cek Kaki-Kaki & Suspensi', 'description' => 'Pemeriksaan mendalam komponen suspensi dan kaki-kaki mobil', 'price' => 600000, 'estimated_time' => 120],
            ['service_name' => 'Servis AC & Cuci Evaporator', 'description' => 'Penambahan freon dan pembersihan saluran udara AC', 'price' => 550000, 'estimated_time' => 45],
            ['service_name' => 'Servis Kelistrikan & Aki', 'description' => 'Pembersihan terminal aki dan pengecekan tegangan alternator', 'price' => 150000, 'estimated_time' => 20],
            ['service_name' => 'Rotasi & Balancing Ban', 'description' => 'Pemindahan posisi ban untuk memastikan keausan yang merata', 'price' => 100000, 'estimated_time' => 30],
            ['service_name' => 'Kuras Minyak Rem', 'description' => 'Penggantian minyak rem lama dengan yang baru (DOT 4)', 'price' => 350000, 'estimated_time' => 45],
            ['service_name' => 'Diagnosa Komputer (ECU)', 'description' => 'Pemindaian kode kesalahan mesin menggunakan scanner OEM', 'price' => 150000, 'estimated_time' => 20],
            ['service_name' => 'Ganti Kopling Set', 'description' => 'Pemasangan unit kopling baru (Dekrup, Kampas, Release Bearing)', 'price' => 2200000, 'estimated_time' => 360],
        ];

        foreach ($services as $s) {
            Service::create($s);
        }
    }
}
