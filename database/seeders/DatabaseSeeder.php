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
            ['name' => 'Alfredo Agustinus', 'passcode' => '1111'],
            ['name' => 'Abdul and The Coffee Theory', 'passcode' => '2222'],
            ['name' => 'Aldi Taher', 'passcode' => '3333'],
        ];
        
        foreach ($judges as $judge) {
            Judge::create($judge);
        }

        // 3. Seeder Peserta
        $participants = [
            'Biro Protokol', 'Deputi 1', 'DWP', 'Tim SKWP', 
            'Biro Umum', 'Deputi 2', 'Deputi 3', 'Outsourcing', 
            'Biro PMI', 'Tim Sespri', 'Biro TUSDM', 'Biro Perkeu'
        ];

        foreach ($participants as $index => $name) {
            $order = $index + 1;
            Participant::create([
                'name' => $name,
                'song_title' => 'Lagu Pilihan ' . $order,
                'order_number' => $order,
                'status' => $order === 1 ? 'performing' : 'waiting', 
            ]);
        }
    }
}