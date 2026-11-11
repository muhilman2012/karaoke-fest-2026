<?php

namespace App\Services;

use App\Models\ChallengeParticipant;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;

class CompetitionMasterDataService
{
    /**
     * Sinkronkan kedua tabel peserta ke daftar resmi dan hapus baris ekstra/duplikat.
     */
    public function synchronizeParticipants(bool $resetStatuses = false): void
    {
        $officialParticipants = config('competition.participants', []);

        if (count($officialParticipants) !== 11) {
            throw new \RuntimeException('Daftar peserta resmi harus tepat berjumlah 11.');
        }

        DB::transaction(function () use ($officialParticipants, $resetStatuses): void {
            $keptParticipantIds = [];
            $keptChallengeParticipantIds = [];

            foreach ($officialParticipants as $index => $official) {
                $order = $index + 1;

                $participant = Participant::updateOrCreate(
                    ['order_number' => $order],
                    [
                        'name' => $official['name'],
                        'song_title' => $official['song_title'],
                        ...($resetStatuses ? ['status' => $order === 1 ? 'performing' : 'waiting'] : []),
                    ]
                );
                $keptParticipantIds[] = $participant->id;

                // Tabel challenge tidak memiliki unique order_number, sehingga pilih satu
                // baris resmi dan hapus semua baris lain setelah sinkronisasi.
                $challengeParticipant = ChallengeParticipant::where('order_number', $order)
                    ->orderBy('id')
                    ->first();

                if (!$challengeParticipant) {
                    $challengeParticipant = new ChallengeParticipant([
                        'order_number' => $order,
                        'status' => 'pending',
                    ]);
                }

                $challengeParticipant->name = $official['name'];
                if ($resetStatuses) {
                    $challengeParticipant->status = 'pending';
                }
                $challengeParticipant->save();
                $keptChallengeParticipantIds[] = $challengeParticipant->id;
            }

            Participant::whereNotIn('id', $keptParticipantIds)->delete();
            ChallengeParticipant::whereNotIn('id', $keptChallengeParticipantIds)->delete();
        });
    }
}
