<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Participant;
use App\Models\Judge;
use App\Models\Aspect;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seeder Aspek Penilaian (Dinamis)
        $aspects = [
            [
                'name' => 'Teknik dan Kualitas Vokal',
                'description' => 'Kualitas suara, intonasi, artikulasi, dan teknik bernyanyi',
                'percentage' => 45
            ],
            [
                'name' => 'Pembagian Peran (Vokalis)',
                'description' => 'Kekompakan pembagian suara antar vokalis',
                'percentage' => 20
            ],
            [
                'name' => 'Kreativitas Penampilan',
                'description' => 'Koreografi, kostum, dan kekompakan serta konsep/tema',
                'percentage' => 30
            ],
            [
                'name' => 'Keterlibatan Pimpinan',
                'description' => 'Tambahan nilai untuk keikutsertaan Eselon I dan/atau II',
                'percentage' => 5
            ]
        ];

        foreach ($aspects as $aspect) {
            Aspect::create($aspect);
        }

        // 2. Seeder Juri
        $judges = [
            ['name' => 'Alfredo Agustinus', 'passcode' => '3849'],
            ['name' => 'Abdul and The Coffee Theory', 'passcode' => '7364'],
            ['name' => 'Rifky Kurnia', 'passcode' => '8291'],
        ];
        
        foreach ($judges as $judge) {
            Judge::create($judge);
        }

        // 3. Seeder Peserta beserta Judul Lagu
        $participants = [
            ['name' => 'Biro Protokol', 'song' => 'Melompat Lebih Tinggi'],
            ['name' => 'Deputi 1', 'song' => 'Dia Milikku'],
            ['name' => 'DWP', 'song' => 'Anak Sekolah'],
            ['name' => 'Tim SKWP', 'song' => 'Berharap Tak Berpisah'],
            ['name' => 'Biro Umum', 'song' => 'Begitu Indah'],
            ['name' => 'Deputi 2', 'song' => 'Ku Bahagia'],
            ['name' => 'Deputi 3', 'song' => 'Pacarku Superstar'],
            ['name' => 'Outsourcing', 'song' => 'Batal Kawin'],
            ['name' => 'Biro PMI', 'song' => 'Hip Hip Hura'],
            ['name' => 'Tim Sespri', 'song' => 'Kamu Ngga Sendirian'],
            ['name' => 'Biro TUSDM', 'song' => 'Galih dan Ratna'],
            ['name' => 'Biro Perkeu', 'song' => 'Spontan (Tanpa) Uhuy'],
        ];

        foreach ($participants as $index => $participant) {
            $order = $index + 1;
            Participant::create([
                'name' => $participant['name'],
                'song_title' => $participant['song'] ?: 'Lagu Belum Ditentukan', // Fallback jika kosong
                'order_number' => $order,
                'status' => $order === 1 ? 'performing' : 'waiting', 
            ]);
        }
    }
}