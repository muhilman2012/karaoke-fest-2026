<?php

namespace Tests\Feature;

use App\Models\Judge;
use App\Models\Participant;
use App\Models\Score;
use App\Models\Vote;
use App\Models\SupporterJudge;
use App\Models\SupporterScore;
use App\Services\CompetitionResetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CompetitionResetServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resets_results_without_changing_participants_or_songs(): void
    {
        $first = Participant::create([
            'name' => 'Peserta Pertama',
            'song_title' => 'Lagu Tetap Ada',
            'order_number' => 1,
            'status' => 'finished',
            'is_favorite' => true,
        ]);
        $second = Participant::create([
            'name' => 'Peserta Kedua',
            'song_title' => 'Lagu Kedua',
            'order_number' => 2,
            'status' => 'performing',
        ]);
        $judge = Judge::create(['name' => 'Juri', 'passcode' => '9876']);

        Score::create([
            'participant_id' => $first->id,
            'judge_id' => $judge->id,
            'total_score' => 85,
            'is_locked' => true,
        ]);
        Vote::create([
            'participant_id' => $second->id,
            'device_token' => '244467fc-7cf2-4b59-b76d-facb8b7c21d5',
        ]);
        $supporterJudge = SupporterJudge::create([
            'name' => 'Juri 1',
            'passcode' => '2101',
        ]);
        SupporterScore::create([
            'participant_id' => $first->id,
            'supporter_judge_id' => $supporterJudge->id,
            'score' => 9,
            'is_locked' => true,
        ]);
        Cache::forever('voting_is_open', true);

        app(CompetitionResetService::class)->resetScoresAndVotes();

        $this->assertDatabaseCount('participants', 2);
        $this->assertDatabaseCount('scores', 0);
        $this->assertDatabaseCount('votes', 0);
        $this->assertDatabaseCount('supporter_scores', 0);
        $this->assertDatabaseHas('participants', [
            'id' => $first->id,
            'song_title' => 'Lagu Tetap Ada',
            'status' => 'performing',
            'is_favorite' => false,
        ]);
        $this->assertDatabaseHas('participants', [
            'id' => $second->id,
            'song_title' => 'Lagu Kedua',
            'status' => 'waiting',
        ]);
        $this->assertFalse(Cache::get('voting_is_open'));
    }
}
