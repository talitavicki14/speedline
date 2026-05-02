<?php

namespace Database\Seeders;

use App\Models\Sparepart;
use App\Models\Distributor;
use App\Models\Purchase;
use Illuminate\Database\Seeder;

class SparepartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $spareparts = [
            ['name' => 'Mobil 1 Full Synthetic 5W-30', 'type' => 'Oli', 'brand' => 'Mobil', 'stock' => 50, 'price' => 180000],
            ['name' => 'Castrol EDGE 5W-40', 'type' => 'Oli', 'brand' => 'Castrol', 'stock' => 40, 'price' => 195000],
            ['name' => 'Bosch Oil Filter', 'type' => 'Filter', 'brand' => 'Bosch', 'stock' => 60, 'price' => 85000],
            ['name' => 'Brembo Brake Pad Front', 'type' => 'Rem', 'brand' => 'Brembo', 'stock' => 30, 'price' => 650000],
            ['name' => 'Brembo Brake Pad Rear', 'type' => 'Rem', 'brand' => 'Brembo', 'stock' => 30, 'price' => 550000],
            ['name' => 'NGK Spark Plug Iridium', 'type' => 'Busi', 'brand' => 'NGK', 'stock' => 100, 'price' => 125000],
            ['name' => 'K&N Air Filter', 'type' => 'Filter', 'brand' => 'K&N', 'stock' => 25, 'price' => 450000],
            ['name' => 'Coolant Prestone 1L', 'type' => 'Cairan', 'brand' => 'Prestone', 'stock' => 80, 'price' => 65000],
            ['name' => 'Brake Fluid DOT 4', 'type' => 'Cairan', 'brand' => 'ATE', 'stock' => 35, 'price' => 75000],
            ['name' => 'Amaron Battery NS40Z', 'type' => 'Aki', 'brand' => 'Amaron', 'stock' => 15, 'price' => 850000],
            ['name' => 'Bosch Advantage Wiper', 'type' => 'Aksesoris', 'brand' => 'Bosch', 'stock' => 45, 'price' => 75000],
            ['name' => 'Denso Cabin Air Filter', 'type' => 'Filter', 'brand' => 'Denso', 'stock' => 35, 'price' => 110000],
            ['name' => 'Gates Micro-V Fan Belt', 'type' => 'Mesin', 'brand' => 'Gates', 'stock' => 25, 'price' => 225000],
            ['name' => 'Kayaba Excel-G Shock', 'type' => 'Suspensi', 'brand' => 'KYB', 'stock' => 16, 'price' => 1150000],
            ['name' => 'Shell Helix HX8 5W-30', 'type' => 'Oli', 'brand' => 'Shell', 'stock' => 60, 'price' => 125000],
            ['name' => 'Aisin Clutch Disc Set', 'type' => 'Transmisi', 'brand' => 'Aisin', 'stock' => 18, 'price' => 1450000],
            ['name' => 'TRW Tie Rod End', 'type' => 'Kemudi', 'brand' => 'TRW', 'stock' => 24, 'price' => 195000],
            ['name' => 'Bosch Fuel Pump Assembly', 'type' => 'Mesin', 'brand' => 'Bosch', 'stock' => 10, 'price' => 1850000],
        ];

        $distributors = Distributor::pluck('id')->toArray();

        foreach ($spareparts as $s) {
            $s['purchase_price'] = round($s['price'] * (rand(60, 80) / 100));
            $distributorId = $distributors[array_rand($distributors)];
            $item = Sparepart::create([
                'name' => $s['name'],
                'type' => $s['type'],
                'brand' => $s['brand'],
                'stock' => $s['stock'],
                'purchase_price' => $s['purchase_price'],
                'price' => $s['price'],
                'distributor_id' => $distributorId,
            ]);

            Purchase::create([
                'sparepart_id' => $item->id,
                'distributor_id' => $distributorId,
                'qty' => $s['stock'],
                'purchase_price' => $s['purchase_price'],
                'total_price' => $s['stock'] * $s['purchase_price'],
                'purchase_date' => now()->subDays(rand(0, 10)),
            ]);
        }
    }
}
