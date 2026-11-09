<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\SupporterJudge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupporterScoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_supporter_judge_can_follow_live_participant_and_lock_a_score(): void
    {
        $participant = Participant::create([
            'name' => 'Instansi Uji',
            'song_title' => 'Lagu Uji',
            'order_number' => 1,
            'status' => 'performing',
        ]);
        $judge = SupporterJudge::create([
            'name' => 'Juri 1',
            'affiliation' => 'Unsur Penguji',
            'passcode' => '2101',
        ]);
        session(['supporter_judge_id' => $judge->id]);

        Livewire::test('supporter-judge-form')
            ->assertSet('participant_id', $participant->id)
            ->call('chooseScore', 5)
            ->assertSet('score', null)
            ->call('chooseScore', 9)
            ->assertSet('score', 9)
            ->call('saveDraft')
            ->assertSet('is_locked', false)
            ->call('lockScore')
            ->assertSet('is_locked', true);

        $this->assertDatabaseHas('supporter_scores', [
            'participant_id' => $participant->id,
            'supporter_judge_id' => $judge->id,
            'score' => 9,
            'is_locked' => true,
        ]);
    }

    public function test_supporter_pages_are_separate_and_admin_recap_is_protected(): void
    {
        $this->get('/juri-supporter')->assertOk();
        $this->get('/juri-supporter/form')->assertRedirect(route('supporter.login'));
        $this->get('/admin/supporter')->assertRedirect(route('admin.login'));
    }
}
