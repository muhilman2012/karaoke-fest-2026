<?php

namespace Database\Seeders;

use App\Models\Aspect;
use App\Models\ChallengeParticipant;
use App\Models\Judge;
use App\Models\Participant;
use Illuminate\Database\Seeder;

class KorpriMusicalChallengeSeeder extends Seeder
{
    public function run(): void
    {
        $participants = [
            ['name' => 'Sekretariat Dukungan Kabinet', 'song_title' => 'Sambalado - Ayu Ting Ting'],
            ['name' => 'PPK Kemayoran', 'song_title' => 'Tor Monitor Ketua - Ecko Show, Juan Reza, Chesylino'],
            ['name' => 'Sekretariat Wakil Presiden', 'song_title' => 'Harus Bahagia - Yura Yunita'],
            ['name' => 'Sekretariat Kementerian 2', 'song_title' => 'Kamulah Satu-Satunya - Dewa 19'],
            ['name' => 'Sekretariat Kementerian', 'song_title' => 'Bang Jono - Zaskia Gotik'],
            ['name' => 'Sekretariat Presiden', 'song_title' => 'Kita - Sheila on 7'],
            ['name' => 'Pengelola TMII', 'song_title' => 'Seperti Mati Lampu - King Nassar'],
            ['name' => 'Sekretariat Militer Presiden 2', 'song_title' => 'Pudar - Rossa'],
            ['name' => 'Sekretariat Kantor Staf Presiden', 'song_title' => 'Salam Rindu - Tipe X'],
            ['name' => 'Bappisus', 'song_title' => 'Cerita Cinta - Kahitna'],
            ['name' => 'Sekretariat Militer Presiden', 'song_title' => 'Kala Cinta Menggoda - Ariel Noah'],
        ];

        foreach ($participants as $index => $participant) {
            $order = $index + 1;

            Participant::updateOrCreate(
                ['order_number' => $order],
                $participant + ['status' => $order === 1 ? 'performing' : 'waiting']
            );

            ChallengeParticipant::updateOrCreate(
                ['order_number' => $order],
                ['name' => $participant['name'], 'status' => 'pending']
            );
        }

        Participant::whereNotIn('name', array_column($participants, 'name'))->delete();
        ChallengeParticipant::whereNotIn('name', array_column($participants, 'name'))->delete();

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
    }
}
