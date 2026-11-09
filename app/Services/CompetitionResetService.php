<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\Score;
use App\Models\ScoreDetail;
use App\Models\Vote;
use App\Models\SupporterScore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CompetitionResetService
{
    public function resetScoresAndVotes(): void
    {
        DB::transaction(function (): void {
            ScoreDetail::query()->delete();
            Score::query()->delete();
            Vote::query()->delete();
            SupporterScore::query()->delete();

            Participant::query()->update([
                'status' => 'waiting',
                'is_favorite' => false,
            ]);

            Participant::where('order_number', 1)->update([
                'status' => 'performing',
            ]);
        });

        Cache::forever('voting_is_open', false);
    }
}
