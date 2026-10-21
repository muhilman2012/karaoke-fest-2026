<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Participant;
use App\Models\Judge;
use App\Models\Aspect;
use App\Models\ChallengeParticipant;
use App\Models\ChallengeSong;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $aspects = [
            [
                'name' => 'Total Nilai Juri 1',
                'description' => 'Rekapitulasi total poin dari Juri 1',
                'percentage' => 25
            ],
            [
                'name' => 'Total Nilai Juri 2',
                'description' => 'Rekapitulasi total poin dari Juri 2',
                'percentage' => 25
            ],
            [
                'name' => 'Total Nilai Juri 3',
                'description' => 'Rekapitulasi total poin dari Juri 3',
                'percentage' => 25
            ],
            [
                'name' => 'Total Nilai Juri 4',
                'description' => 'Rekapitulasi total poin dari Juri 4',
                'percentage' => 25
            ]
        ];

        foreach ($aspects as $aspect) {
            Aspect::firstOrCreate(['name' => $aspect['name']], $aspect);
        }

        $judges = [
            ['name' => 'Admin Rekap Defile', 'passcode' => '9999'], 
        ];
        
        foreach ($judges as $judge) {
            Judge::firstOrCreate(['name' => $judge['name']], $judge);
        }

        // 11 Peserta Resmi KORPRI Musical Challenge 2026
        $korpriParticipants = [
            'Sekretariat Wakil Presiden',
            'Sekretariat Militer Presiden',
            'Sekretariat Dukungan Kabinet',
            'Sekretariat Dewan Pertimbangan Presiden RI',
            'Sekretariat Kantor Staf Presiden',
            'Dewan Ekonomi Nasional',
            'Bakom RI',
            'Bappisus',
            'PPK Gelora Bung Karno',
            'PPK Kemayoran',
            'Pengelola Taman Mini Indonesia Indah',
        ];

        // 1. Seed ke Tabel Participant (untuk Voting & Scoring Panggung)
        foreach ($korpriParticipants as $index => $name) {
            $order = $index + 1;
            $existing = Participant::where('name', $name)->first();
            $song = ($existing && $existing->song_title && $existing->song_title !== '-') ? $existing->song_title : '-';

            Participant::updateOrCreate(
                ['name' => $name],
                [
                    'song_title' => $song, 
                    'order_number' => $order,
                    'status' => $order === 1 ? 'performing' : 'waiting', 
                ]
            );
        }

        // 2. Seed ke Tabel ChallengeParticipant (untuk Roda Undian / Controller)
        foreach ($korpriParticipants as $index => $name) {
            $order = $index + 1;
            ChallengeParticipant::firstOrCreate(
                ['name' => $name],
                [
                    'order_number' => $order,
                    'status' => 'pending',
                ]
            );
        }

        // 3. Seed Master Pool Lagu Tantangan (35 Lagu dalam 5 Genre)
        $challengeSongs = [
            // POP (15)
            ['genre' => 'POP', 'title' => 'Kulakukan Semua Untukmu - RAN'],
            ['genre' => 'POP', 'title' => 'Alamak - Rizky Febrian'],
            ['genre' => 'POP', 'title' => 'Pudar - Rosa'],
            ['genre' => 'POP', 'title' => 'Kamu - Coboy Junior'],
            ['genre' => 'POP', 'title' => 'Harus Bahagia - Yura Yunita'],
            ['genre' => 'POP', 'title' => 'Salah - Potret'],
            ['genre' => 'POP', 'title' => 'Cerita Cinta - Kahitna'],
            ['genre' => 'POP', 'title' => 'Kala Cinta Menggoda - Ariel Noah'],
            ['genre' => 'POP', 'title' => 'Dahulu - the groove'],
            ['genre' => 'POP', 'title' => 'Cenat cenut - Smash'],
            ['genre' => 'POP', 'title' => 'Bebas - Iwa K'],
            ['genre' => 'POP', 'title' => 'Aku wanita - Reza Artamevia'],
            ['genre' => 'POP', 'title' => 'Kita - Sheila on 7'],
            ['genre' => 'POP', 'title' => 'Gejolak kawula muda - Club 80s'],
            ['genre' => 'POP', 'title' => 'Kamulah satu-satunya - Dewa 19'],

            // ROCK (7)
            ['genre' => 'ROCK', 'title' => 'Ningrat - Jamrud'],
            ['genre' => 'ROCK', 'title' => 'Racun - The Changcuters'],
            ['genre' => 'ROCK', 'title' => 'Pangeran cinta - Dewa 19'],
            ['genre' => 'ROCK', 'title' => 'Ceria - Jrock'],
            ['genre' => 'ROCK', 'title' => 'Cukup Siti Nurbaya - Dewa 19'],
            ['genre' => 'ROCK', 'title' => 'Papa rock n roll - The Dance Company'],
            ['genre' => 'ROCK', 'title' => 'Jika kami bersama - Superman is Dead'],

            // DANGDUT (5)
            ['genre' => 'DANGDUT', 'title' => 'Seperti Mati Lampu - King Nassar'],
            ['genre' => 'DANGDUT', 'title' => 'Bang Jono - Zaskia Gotik'],
            ['genre' => 'DANGDUT', 'title' => 'Sambalado - Ayu Ting-Ting'],
            ['genre' => 'DANGDUT', 'title' => 'Meriang - Cita Citata'],
            ['genre' => 'DANGDUT', 'title' => 'Pacar Lima Langkah - Iceu Wong'],

            // NUSANTARA MIX (4)
            ['genre' => 'NUSANTARA MIX', 'title' => 'Tor Monitor Ketua - Ecko Show, Juan Reza, Chesylino'],
            ['genre' => 'NUSANTARA MIX', 'title' => 'Koyo Jogja Istimewa - Ndarboy Gen'],
            ['genre' => 'NUSANTARA MIX', 'title' => 'Tresno Tekane Mati - NDX A.K.A'],
            ['genre' => 'NUSANTARA MIX', 'title' => 'Los Dol - Denny Caknan'],

            // SKA/REGGAE (4)
            ['genre' => 'SKA/REGGAE', 'title' => 'Sakit Hati - Tipe X'],
            ['genre' => 'SKA/REGGAE', 'title' => 'Hey Cantik - Shaggy Dog'],
            ['genre' => 'SKA/REGGAE', 'title' => 'Genit - Tipe X'],
            ['genre' => 'SKA/REGGAE', 'title' => 'Salam Rindu - Tipe X'],
        ];

        foreach ($challengeSongs as $song) {
            ChallengeSong::firstOrCreate(
                ['title' => $song['title']],
                [
                    'genre' => $song['genre'],
                    'is_active' => true,
                    'is_used' => false,
                ]
            );
        }
    }
}
