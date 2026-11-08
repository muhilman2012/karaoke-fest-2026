<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\Vote;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_device_token_can_only_vote_once(): void
    {
        $first = Participant::create([
            'name' => 'Peserta 1',
            'song_title' => 'Lagu 1',
            'order_number' => 1,
        ]);
        $second = Participant::create([
            'name' => 'Peserta 2',
            'song_title' => 'Lagu 2',
            'order_number' => 2,
        ]);

        Vote::create([
            'participant_id' => $first->id,
            'device_token' => '244467fc-7cf2-4b59-b76d-facb8b7c21d5',
        ]);

        $this->expectException(QueryException::class);

        Vote::create([
            'participant_id' => $second->id,
            'device_token' => '244467fc-7cf2-4b59-b76d-facb8b7c21d5',
        ]);
    }
}
