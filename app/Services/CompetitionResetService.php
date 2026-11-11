<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\Score;
use App\Models\ScoreDetail;
use App\Models\Vote;
use App\Models\SupporterScore;
use Illuminate\Support\Facades\DB;

class CompetitionResetService
{
    public function __construct(
        private readonly CompetitionMasterDataService $masterData,
    ) {}

    public function resetScoresAndVotes(): void
    {
        DB::transaction(function (): void {
            ScoreDetail::query()->delete();
            Score::query()->delete();
            Vote::query()->delete();
            SupporterScore::query()->delete();

            Participant::query()->update(['is_favorite' => false]);
            $this->masterData->synchronizeParticipants(resetStatuses: true);
        });

        app(VotingSessionService::class)->close();
    }
}
