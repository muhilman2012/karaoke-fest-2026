<?php

namespace Tests\Feature;

use App\Models\Aspect;
use App\Models\ChallengeParticipant;
use App\Models\Judge;
use App\Models\Participant;
use App\Models\SupporterJudge;
use Database\Seeders\KorpriMusicalChallengeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KorpriMusicalChallengeSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_official_competition_data_idempotently(): void
    {
        // Simulasikan database lama yang berisi 13 peserta dan duplikat nama resmi.
        Participant::create(['name' => 'PPK Kemayoran', 'song_title' => '-', 'order_number' => 12]);
        Participant::create(['name' => 'Peserta Tambahan', 'song_title' => '-', 'order_number' => 13]);
        ChallengeParticipant::create(['name' => 'PPK Kemayoran', 'order_number' => 1]);
        ChallengeParticipant::create(['name' => 'Peserta Tambahan', 'order_number' => 12]);

        $this->seed(KorpriMusicalChallengeSeeder::class);
        $this->seed(KorpriMusicalChallengeSeeder::class);

        $this->assertSame(11, Participant::count());
        $this->assertSame(11, ChallengeParticipant::count());
        $this->assertSame(3, Judge::count());
        $this->assertSame(3, Aspect::count());
        $this->assertSame(3, SupporterJudge::count());
        $this->assertSame(100, Aspect::sum('percentage'));

        $this->assertDatabaseHas('participants', [
            'order_number' => 1,
            'name' => 'Sekretariat Dukungan Kabinet',
            'song_title' => 'Sambalado - Ayu Ting Ting',
        ]);
        $this->assertDatabaseHas('participants', [
            'order_number' => 2,
            'name' => 'PPK Kemayoran',
        ]);
        $this->assertDatabaseHas('participants', [
            'order_number' => 11,
            'name' => 'Sekretariat Militer Presiden',
            'song_title' => 'Kala Cinta Menggoda - Ariel Noah',
        ]);
        $this->assertDatabaseHas('judges', [
            'name' => 'Hedi Yunus',
            'passcode' => '1103',
        ]);
        $this->assertDatabaseHas('supporter_judges', [
            'name' => 'Juri 2',
            'affiliation' => 'DPP KORPRI',
            'passcode' => '2102',
        ]);
    }
}
