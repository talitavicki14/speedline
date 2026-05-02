<?php

namespace Database\Seeders;

use App\Models\Hero;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class HeroSeeder extends Seeder
{
    public function run(): void
    {
        if (!Storage::disk('public')->exists('heroes')) {
            Storage::disk('public')->makeDirectory('heroes');
        }

        $data = [
            [
                'src' => 'public/images/carousel/carousel-1.webp',
                'title' => 'Presisi Tanpa Kompromi untuk Mobil Anda.',
                'subtitle' => 'Kami menggabungkan teknologi diagnostik terbaru dengan teknisi ahli bersertifikat untuk memastikan performa kendaraan Anda tetap di level puncak.',
                'order' => 1
            ],
            [
                'src' => 'public/images/carousel/carousel-2.webp',
                'title' => 'Servis Cepat, Hasil Akurat, Performa Hebat.',
                'subtitle' => 'Nikmati kemudahan booking online dan layanan servis transparan yang dirancang untuk menghemat waktu Anda tanpa mengurangi kualitas pengerjaan.',
                'order' => 2
            ],
            [
                'src' => 'public/images/carousel/carousel-3.webp',
                'title' => 'Bengkel Terpercaya untuk Kendaraan Istimewa.',
                'subtitle' => 'Menggunakan 100% sparepart asli dan peralatan standar pabrikan. Keamanan dan kenyamanan berkendara Anda adalah prioritas utama kami.',
                'order' => 3
            ],
        ];

        foreach ($data as $item) {
            $filename = basename($item['src']);
            $targetPath = 'heroes/' . $filename;

            if (File::exists(base_path($item['src']))) {
                Storage::disk('public')->put($targetPath, File::get(base_path($item['src'])));
            }

            Hero::updateOrCreate(
                ['order' => $item['order']],
                [
                    'image_url' => $targetPath,
                    'title' => $item['title'],
                    'subtitle' => $item['subtitle'],
                    'is_active' => true,
                ]
            );
        }
    }
}
