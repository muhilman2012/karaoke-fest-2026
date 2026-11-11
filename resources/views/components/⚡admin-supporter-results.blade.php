<?php

use App\Models\Participant;
use App\Models\SupporterJudge;
use Livewire\Component;

new class extends Component
{
    public function with(): array
    {
        $totalJudges = SupporterJudge::count() ?: 3;

        $participants = Participant::with([
            'supporterScores',
            'supporterScores.judge',
        ])->orderBy('order_number')->get()->map(function ($participant) use ($totalJudges) {
            $lockedScores = $participant->supporterScores->where('is_locked', true);
            $participant->supporter_total = $lockedScores->sum('score');
            $participant->supporter_average = round($participant->supporter_total / $totalJudges, 2);
            $participant->supporter_judges_count = $lockedScores->count();
            $participant->supporter_drafts_count = $participant->supporterScores->where('is_locked', false)->count();
            return $participant;
        })->sortByDesc('supporter_average')->values();

        return [
            'participants' => $participants,
            'judges' => SupporterJudge::orderBy('id')->get(),
            'totalJudges' => $totalJudges,
        ];
    }
};
?>

<div class="min-h-screen bg-slate-100 p-4 sm:p-8" wire:poll.5s>
    <div class="mx-auto max-w-6xl">
        <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <p class="font-bold uppercase tracking-wider text-orange-500">Rekap Penilaian</p>
                <h1 class="text-3xl font-black text-slate-900">Best Supporter</h1>
                <p class="mt-1 text-sm text-slate-500">Nilai akhir = jumlah nilai terkunci dibagi {{ $totalJudges }} juri.</p>
            </div>
            <div class="flex gap-3">
                <a href="/admin/dashboard" class="rounded-xl bg-white px-4 py-3 font-bold text-slate-700 shadow">Kembali ke Admin</a>
                <a href="/juri-supporter" target="_blank" class="rounded-xl bg-orange-600 px-4 py-3 font-bold text-white shadow">Buka Login Juri</a>
            </div>
        </div>

        <div class="mb-6 grid gap-3 sm:grid-cols-3">
            @foreach($judges as $judge)
                <div class="rounded-2xl bg-white p-4 shadow-sm">
                    <p class="font-black text-slate-800">{{ $judge->name }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ preg_replace('/\s*\([^)]*\)\s*/', '', $judge->affiliation) }}</p>
                </div>
            @endforeach
        </div>

        <div class="overflow-x-auto rounded-3xl bg-white shadow-xl">
            <table class="w-full min-w-[760px] text-left">
                <thead class="bg-gradient-to-r from-orange-600 to-amber-500 text-white">
                    <tr>
                        <th class="p-4 text-center">Peringkat</th>
                        <th class="p-4">Instansi</th>
                        @foreach($judges as $judge)
                            <th class="p-4 text-center">{{ $judge->name }}</th>
                        @endforeach
                        <th class="p-4 text-center">Kelengkapan</th>
                        <th class="p-4 text-center">Nilai Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($participants as $index => $participant)
                        <tr class="hover:bg-orange-50/50">
                            <td class="p-4 text-center text-xl font-black text-slate-400">#{{ $index + 1 }}</td>
                            <td class="p-4">
                                <p class="font-black text-slate-800">{{ $participant->name }}</p>
                                <p class="text-xs text-slate-400">Urutan tampil {{ $participant->order_number }}</p>
                            </td>
                            @foreach($judges as $judge)
                                @php($judgeScore = $participant->supporterScores->firstWhere('supporter_judge_id', $judge->id))
                                <td class="p-4 text-center">
                                    <span class="block text-xl font-black {{ $judgeScore ? 'text-orange-600' : 'text-slate-300' }}">
                                        {{ $judgeScore?->score ?? '–' }}
                                    </span>
                                    @if($judgeScore)
                                        <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-[10px] font-black {{ $judgeScore->is_locked ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                            {{ $judgeScore->is_locked ? 'Terkunci' : 'Draft' }}
                                        </span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="p-4 text-center">
                                <span class="rounded-full px-3 py-1 text-xs font-black {{ $participant->supporter_judges_count === $totalJudges ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $participant->supporter_judges_count }}/{{ $totalJudges }} juri
                                </span>
                                @if($participant->supporter_drafts_count > 0)
                                    <span class="mt-1 block text-[10px] font-bold text-yellow-600">{{ $participant->supporter_drafts_count }} draft tersimpan</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <span class="text-2xl font-black {{ $participant->supporter_judges_count === $totalJudges ? 'text-orange-600' : 'text-slate-400' }}">
                                    {{ number_format($participant->supporter_average, 2) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
