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
        // 1. Seeder Aspek Penilaian (Diubah fungsi menjadi wadah nilai ke-5 Juri)
        // Masing-masing berbobot 20% agar total akhirnya menjadi 100%
        $aspects = [
            [
                'name' => 'Total Nilai Juri 1',
                'description' => 'Rekapitulasi total poin dari Juri 1',
                'percentage' => 20
            ],
            [
                'name' => 'Total Nilai Juri 2',
                'description' => 'Rekapitulasi total poin dari Juri 2',
                'percentage' => 20
            ],
            [
                'name' => 'Total Nilai Juri 3',
                'description' => 'Rekapitulasi total poin dari Juri 3',
                'percentage' => 20
            ],
            [
                'name' => 'Total Nilai Juri 4',
                'description' => 'Rekapitulasi total poin dari Juri 4',
                'percentage' => 20
            ],
            [
                'name' => 'Total Nilai Juri 5',
                'description' => 'Rekapitulasi total poin dari Juri 5',
                'percentage' => 20
            ]
        ];

        foreach ($aspects as $aspect) {
            Aspect::create($aspect);
        }

        // 2. Seeder Juri (Hanya 1 Akun Juri/Admin untuk keperluan input rekap nilai)
        $judges = [
            ['name' => 'Admin Rekap Defile', 'passcode' => '9999'], 
        ];
        
        foreach ($judges as $judge) {
            Judge::create($judge);
        }

        // 3. Seeder Peserta (Urutan Pasti Sesuai Daftar)
        $participants = [
            ['name' => 'Biro Protokol dan Kerumahtanggaan', 'song' => '-'],
            ['name' => 'Staf Khusus Wakil Presiden (SKWP)', 'song' => '-'],
            ['name' => 'Biro Perencanaan dan Keuangan', 'song' => '-'],
            ['name' => 'Biro Tata Usaha dan Sumber Daya Manusia', 'song' => '-'],
            ['name' => 'Dharma Wanita Persatuan (DWP)', 'song' => '-'],
            ['name' => 'Biro Pers, Media, dan Informasi', 'song' => '-'],
            ['name' => 'Biro Umum', 'song' => '-'],
            ['name' => 'Deputi Bidang Dukungan Kebijakan Perekonomian, Pariwisata, dan Transformasi Digital (D1)', 'song' => '-'],
            ['name' => 'Outsourcing', 'song' => '-'],
            ['name' => 'Mitra Kerja', 'song' => '-'],
            ['name' => 'Tim Sespri Wakil Presiden', 'song' => '-'],
            ['name' => 'Deputi Bidang Dukungan Kebijakan Pemerintahan dan Pemerataan Pembangunan (D3)', 'song' => '-'],
            ['name' => 'Deputi Bidang Dukungan Kebijakan Peningkatan Kesejahteraan Dan Pembangunan SDM (D2)', 'song' => '-'],
            ['name' => 'Paspampres', 'song' => '-'],
            ['name' => 'Wartawan', 'song' => '-'],
        ];

        foreach ($participants as $index => $participant) {
            $order = $index + 1;
            Participant::create([
                'name' => $participant['name'],
                'song_title' => $participant['song'], 
                'order_number' => $order,
                'status' => $order === 1 ? 'performing' : 'waiting', 
            ]);
        }
    }
}