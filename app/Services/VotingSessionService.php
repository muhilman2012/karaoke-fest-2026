<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class VotingSessionService
{
    private const OPEN_KEY = 'voting_is_open';
    private const CLOSES_AT_KEY = 'voting_closes_at';

    public function status(): array
    {
        $isOpen = (bool) Cache::get(self::OPEN_KEY, true);
        $closesAt = Cache::get(self::CLOSES_AT_KEY);

        if ($isOpen && is_numeric($closesAt) && (int) $closesAt <= now()->timestamp) {
            $this->close();
            $isOpen = false;
            $closesAt = null;
        }

        return [
            'is_open' => $isOpen,
            'closes_at' => is_numeric($closesAt) ? (int) $closesAt : null,
            'remaining_seconds' => $isOpen && is_numeric($closesAt)
                ? max(0, (int) $closesAt - now()->timestamp)
                : null,
        ];
    }

    public function start(int $minutes): array
    {
        Cache::forever(self::OPEN_KEY, true);
        Cache::forever(self::CLOSES_AT_KEY, now()->addMinutes($minutes)->timestamp);

        return $this->status();
    }

    public function open(): void
    {
        Cache::forever(self::OPEN_KEY, true);
        Cache::forget(self::CLOSES_AT_KEY);
    }

    public function close(): void
    {
        Cache::forever(self::OPEN_KEY, false);
        Cache::forget(self::CLOSES_AT_KEY);
    }
}
