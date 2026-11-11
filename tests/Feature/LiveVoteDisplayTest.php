<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveVoteDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_vote_display_is_public_and_shows_current_results(): void
    {
        $participant = Participant::create([
            'name' => 'Instansi Favorit',
            'song_title' => 'Lagu Favorit',
            'order_number' => 1,
        ]);
        Vote::create([
            'participant_id' => $participant->id,
            'device_token' => '244467fc-7cf2-4b59-b76d-facb8b7c21d5',
        ]);

        $this->get('/live-votes')
            ->assertOk()
            ->assertSeeText('LIVE HASIL VOTING')
            ->assertSeeText('Instansi Favorit')
            ->assertSeeText('1 suara');
    }
}
