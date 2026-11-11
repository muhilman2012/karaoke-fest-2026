<?php

namespace Tests\Feature;

use App\Models\Aspect;
use App\Models\Judge;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JudgeScoreRangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_judge_score_must_be_between_60_and_100(): void
    {
        $participant = Participant::create([
            'name' => 'Peserta Uji',
            'song_title' => 'Lagu Uji',
            'order_number' => 1,
            'status' => 'performing',
        ]);
        $judge = Judge::create([
            'name' => 'Juri Uji',
            'profile' => 'Penguji',
            'passcode' => '1199',
        ]);
        $aspect = Aspect::create([
            'name' => 'Aspek Uji',
            'description' => 'Aspek untuk pengujian.',
            'percentage' => 100,
        ]);
        session(['judge_id' => $judge->id]);

        Livewire::test('judge-form')
            ->assertSet('participant_id', $participant->id)
            ->set('aspectScores.'.$aspect->id, 59)
            ->call('saveDraft')
            ->assertHasErrors(['aspectScores.'.$aspect->id => 'min'])
            ->set('aspectScores.'.$aspect->id, 101)
            ->call('saveDraft')
            ->assertHasErrors(['aspectScores.'.$aspect->id => 'max'])
            ->set('aspectScores.'.$aspect->id, 60)
            ->call('saveDraft')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('score_details', [
            'aspect_id' => $aspect->id,
            'score_value' => 60,
        ]);
    }
}
