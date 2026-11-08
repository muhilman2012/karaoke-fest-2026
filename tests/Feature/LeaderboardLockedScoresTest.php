<?php

namespace Tests\Feature;

use App\Models\Judge;
use App\Models\Participant;
use App\Models\Score;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardLockedScoresTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_leaderboard_ignores_unlocked_draft_scores(): void
    {
        $participant = Participant::create([
            'name' => 'Tim Uji',
            'song_title' => 'Lagu Uji',
            'order_number' => 1,
        ]);
        $judge = Judge::create(['name' => 'Juri Uji', 'passcode' => '4321']);

        Score::create([
            'participant_id' => $participant->id,
            'judge_id' => $judge->id,
            'total_score' => 99,
            'is_locked' => false,
        ]);

        $this->getJson('/api/leaderboard-data')
            ->assertOk()
            ->assertJsonPath('champion1.average_score', 0);

        Score::query()->update(['is_locked' => true]);

        $this->getJson('/api/leaderboard-data')
            ->assertOk()
            ->assertJsonPath('champion1.average_score', 99);
    }
}
