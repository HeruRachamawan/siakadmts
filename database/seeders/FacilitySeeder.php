<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            [
                'name' => 'Laboratorium Komputer',
                'description' => 'Dilengkapi dengan 30 PC spesifikasi tinggi, AC sentral, proyektor interaktif, dan koneksi internet fiber optic 100 Mbps untuk menunjang praktikum TIK dan desain grafis.',
                'image' => 'https://picsum.photos/id/0/800/500',
                'status' => 'published',
            ],
            [
                'name' => 'Perpustakaan Digital',
                'description' => 'Perpustakaan modern dengan ribuan koleksi buku fisik dan e-book. Dilengkapi area baca lesehan yang nyaman dan komputer pencarian katalog digital.',
                'image' => 'https://picsum.photos/id/20/800/500',
                'status' => 'published',
            ],
            [
                'name' => 'Masjid Raya Madrasah',
                'description' => 'Masjid luas berkapasitas 500 jamaah yang digunakan untuk kegiatan salat berjamaah, pengajian rutin, dan praktik ibadah siswa.',
                'image' => 'https://picsum.photos/id/30/800/500',
                'status' => 'published',
            ],
        ];

        foreach ($facilities as $data) {
            Facility::updateOrCreate(['name' => $data['name']], $data);
        }
    }
}
