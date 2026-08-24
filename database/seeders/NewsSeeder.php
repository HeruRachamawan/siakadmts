<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $userId = $user ? $user->id : 1;

        $posts = [
            [
                'title' => 'Penerimaan Peserta Didik Baru (PPDB) Tahun Ajaran 2026/2027 Resmi Dibuka!',
                'content' => 'Kabar gembira bagi seluruh calon peserta didik! Pendaftaran PPDB untuk tahun ajaran baru kini telah resmi dibuka. Segera daftarkan diri Anda dan jadilah bagian dari keluarga besar sekolah kami. Kuota pendaftaran tahun ini sangat terbatas untuk memastikan kualitas pembelajaran yang optimal. Calon siswa dapat mendaftar langsung melalui portal web ini atau mengunjungi ruang tata usaha di jam kerja.',
                'image' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=2070&auto=format&fit=crop',
                'status' => 'published',
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Prestasi Gemilang: Tim Robotik Sekolah Sabet Juara 1 Tingkat Nasional',
                'content' => 'Prestasi membanggakan kembali diukir oleh siswa-siswi kita. Tim Robotik sekolah berhasil menyisihkan lebih dari 50 tim dari seluruh penjuru negeri dan meraih Juara 1 pada ajang Kompetisi Robotik Nasional 2026! Keberhasilan ini tidak lepas dari kerja keras para siswa dan bimbingan tanpa lelah dari bapak/ibu guru pembina. Mari kita terus dukung bakat-bakat luar biasa ini!',
                'image' => 'https://images.unsplash.com/photo-1561557944-6e7860d1a7eb?q=80&w=2070&auto=format&fit=crop',
                'status' => 'published',
                'user_id' => $userId,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'title' => 'Peringatan Hari Pendidikan & Gebyar Seni Budaya Siswa',
                'content' => 'Dalam rangka memperingati Hari Pendidikan Nasional, OSIS dengan bangga menyelenggarakan "Gebyar Seni Budaya 2026". Acara ini menampilkan puluhan pentas seni dari berbagai ekstrakurikuler, mulai dari tari tradisional, teater, hingga konser musik akustik. Acara berlangsung meriah dan ditutup dengan pemotongan tumpeng oleh Bapak Kepala Sekolah.',
                'image' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?q=80&w=1974&auto=format&fit=crop',
                'status' => 'published',
                'user_id' => $userId,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ]
        ];

        foreach ($posts as $postData) {
            $postData['slug'] = Str::slug($postData['title']);
            Post::updateOrCreate(['slug' => $postData['slug']], $postData);
        }
    }
}
