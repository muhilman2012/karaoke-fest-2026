<?php

namespace Database\Seeders;

use App\Models\Aspect;
use App\Models\Judge;
use App\Models\SupporterJudge;
use App\Services\CompetitionMasterDataService;
use Illuminate\Database\Seeder;

class KorpriMusicalChallengeSeeder extends Seeder
{
    public function run(): void
    {
        app(CompetitionMasterDataService::class)->synchronizeParticipants(resetStatuses: true);

        $judges = [
            [
                'name' => 'Dr. Rizal Edwin Manansang, Ak., M.Sc.',
                'profile' => 'Member of Trio Libels; Analis Kebijakan Ahli Utama Kementerian Koordinator Bidang Perekonomian.',
                'passcode' => '1101',
            ],
            [
                'name' => 'Alfredo Agustinus',
                'profile' => 'Vocal Coach dan Choir Conductor, Dux Stella Voce.',
                'passcode' => '1102',
            ],
            [
                'name' => 'Hedi Yunus',
                'profile' => 'Member of Kahitna; penyanyi.',
                'passcode' => '1103',
            ],
        ];

        foreach ($judges as $judge) {
            Judge::updateOrCreate(['passcode' => $judge['passcode']], $judge);
        }

        Judge::whereNotIn('passcode', array_column($judges, 'passcode'))->delete();

        $aspects = [
            [
                'name' => 'Teknik Kualitas Vokal',
                'description' => 'Ketepatan nada, teknik pernapasan, artikulasi, dan kualitas vokal.',
                'percentage' => 50,
            ],
            [
                'name' => 'Pembagian Peran (Vokalis)',
                'description' => 'Keseimbangan dan pembagian bagian lagu antarvokalis.',
                'percentage' => 20,
            ],
            [
                'name' => 'Kreativitas Penampilan',
                'description' => 'Konsep, penguasaan panggung, dan kreativitas penyajian.',
                'percentage' => 30,
            ],
        ];

        foreach ($aspects as $aspect) {
            Aspect::updateOrCreate(['name' => $aspect['name']], $aspect);
        }

        Aspect::whereNotIn('name', array_column($aspects, 'name'))->delete();

        $supporterJudges = [
            ['name' => 'Juri 1', 'affiliation' => 'Unsur PKK Kemayoran', 'passcode' => '2101'],
            ['name' => 'Juri 2', 'affiliation' => 'DPP KORPRI (Pak Nandang)', 'passcode' => '2102'],
            ['name' => 'Juri 3', 'affiliation' => 'Unsur Setwapres (Pak Yayat)', 'passcode' => '2103'],
        ];

        foreach ($supporterJudges as $judge) {
            SupporterJudge::updateOrCreate(['passcode' => $judge['passcode']], $judge);
        }

        SupporterJudge::whereNotIn('passcode', array_column($supporterJudges, 'passcode'))->delete();
    }
}
