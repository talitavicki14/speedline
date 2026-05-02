<?php

namespace Database\Seeders;

use App\Models\Distributor;
use Illuminate\Database\Seeder;

class DistributorSeeder extends Seeder
{
    public function run(): void
    {
        $distributors = [
            [
                'name' => 'PT Astra Otoparts',
                'address' => 'Jl. Raya Pegangsaan Dua No.8, Jakarta Utara',
                'phone' => '021-4603550',
                'email' => 'contact@astra-otoparts.com',
                'contact_person' => 'Budi Santoso'
            ],
            [
                'name' => 'PT Sinar Maju Otomotif',
                'address' => 'Pusat Otomotif Senen Blok A No. 15, Jakarta Pusat',
                'phone' => '021-3845566',
                'email' => 'sales@sinarmaju.id',
                'contact_person' => 'Ani Wijaya'
            ],
            [
                'name' => 'Indo Part Pratama',
                'address' => 'Kawasan Industri Jababeka II, Cikarang',
                'phone' => '021-8934422',
                'email' => 'info@indopart.com',
                'contact_person' => 'Hendra'
            ],
            [
                'name' => 'CV Mandiri Jaya Motor',
                'address' => 'Jl. Ciputat Raya No. 10, Jakarta Selatan',
                'phone' => '021-7234455',
                'email' => 'support@mandirijaya.com',
                'contact_person' => 'Siti Aminah'
            ],
        ];

        foreach ($distributors as $d) {
            Distributor::create($d);
        }
    }
}
