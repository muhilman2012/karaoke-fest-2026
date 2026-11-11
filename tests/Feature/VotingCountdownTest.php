<?php

namespace Tests\Feature;

use App\Services\VotingSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class VotingCountdownTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Carbon::setTestNow('2026-11-11 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_admin_can_start_countdown_and_voting_closes_automatically(): void
    {
        Livewire::test('admin-dashboard')
            ->set('voting_duration_minutes', 2)
            ->call('startVotingCountdown')
            ->assertSet('voting_is_open', true)
            ->assertSet('voting_remaining_seconds', 120);

        $this->assertTrue(Cache::get('voting_is_open'));
        $this->assertSame(now()->addMinutes(2)->timestamp, Cache::get('voting_closes_at'));

        Carbon::setTestNow(now()->addSeconds(121));
        $status = app(VotingSessionService::class)->status();

        $this->assertFalse($status['is_open']);
        $this->assertNull($status['remaining_seconds']);
        $this->assertFalse(Cache::get('voting_is_open'));
        $this->assertNull(Cache::get('voting_closes_at'));
    }

    public function test_countdown_duration_must_be_a_valid_number_of_minutes(): void
    {
        Livewire::test('admin-dashboard')
            ->set('voting_duration_minutes', 0)
            ->call('startVotingCountdown')
            ->assertHasErrors(['voting_duration_minutes' => 'min'])
            ->set('voting_duration_minutes', 1441)
            ->call('startVotingCountdown')
            ->assertHasErrors(['voting_duration_minutes' => 'max']);
    }
}
