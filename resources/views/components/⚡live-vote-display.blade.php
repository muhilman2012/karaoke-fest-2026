<?php

use App\Models\Participant;
use App\Models\Vote;
use Livewire\Component;
use App\Services\VotingSessionService;

new class extends Component
{
    public function with(): array
    {
        $participants = Participant::withCount('votes')
            ->orderByDesc('votes_count')
            ->orderBy('order_number')
            ->get();
        $totalVotes = Vote::count();

        $participants->each(function ($participant) use ($totalVotes) {
            $participant->vote_percentage = $totalVotes > 0
                ? round(($participant->votes_count / $totalVotes) * 100, 1)
                : 0;
        });

        return [
            'participants' => $participants,
            'totalVotes' => $totalVotes,
            'leader' => $totalVotes > 0 ? $participants->first() : null,
            'votingIsOpen' => app(VotingSessionService::class)->status()['is_open'],
        ];
    }
};
?>

<div class="relative h-screen w-screen overflow-hidden bg-slate-950 text-white" wire:poll.3s>
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-[10vw] -top-[20vh] h-[75vh] w-[50vw] rounded-full bg-indigo-600/25 blur-[100px]"></div>
        <div class="absolute -right-[10vw] bottom-[-25vh] h-[70vh] w-[45vw] rounded-full bg-pink-600/20 blur-[110px]"></div>
    </div>

    <div class="relative flex h-full flex-col px-[3vw] py-[2.5vh]">
        <header class="flex h-[17vh] shrink-0 items-center justify-between gap-[3vw] border-b border-white/10 pb-[2vh]">
            <div class="flex min-w-0 items-center gap-[1.3vw]">
                <div class="flex h-[10vh] w-[10vh] shrink-0 items-center justify-center rounded-[2.2vh] bg-white p-[1.2vh] shadow-2xl">
                    <img src="{{ asset('logo/setneg.png') }}" alt="Logo Setneg" class="h-full w-full object-contain">
                </div>
                <div class="min-w-0">
                    <div class="mb-[0.5vh] flex items-center gap-[0.7vw]">
                        <span class="h-[1.2vh] w-[1.2vh] rounded-full {{ $votingIsOpen ? 'animate-pulse bg-emerald-400' : 'bg-red-500' }}"></span>
                        <p class="text-[1.5vh] font-black uppercase tracking-[0.24em] {{ $votingIsOpen ? 'text-emerald-300' : 'text-red-300' }}">
                            {{ $votingIsOpen ? 'Voting Sedang Dibuka' : 'Voting Telah Ditutup' }}
                        </p>
                    </div>
                    <h1 class="truncate text-[4.4vh] font-black leading-none tracking-tight">LIVE HASIL VOTING</h1>
                    <p class="mt-[0.9vh] text-[1.8vh] font-semibold text-indigo-200">KORPRI Musical Challenge 2026 • Peserta Favorit</p>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-[1.2vw]">
                @if($leader)
                    <div class="hidden rounded-[2vh] border border-amber-300/20 bg-amber-400/10 px-[1.4vw] py-[1.5vh] text-right lg:block">
                        <p class="text-[1.2vh] font-black uppercase tracking-wider text-amber-300">Peringkat Teratas</p>
                        <p class="mt-[0.4vh] max-w-[24vw] truncate text-[2vh] font-black text-white">{{ $leader->name }}</p>
                    </div>
                @endif
                <div class="min-w-[10vw] rounded-[2vh] bg-gradient-to-br from-indigo-500 to-violet-700 px-[1.8vw] py-[1.4vh] text-center shadow-xl shadow-indigo-950/50">
                    <p class="text-[1.2vh] font-black uppercase tracking-wider text-indigo-100">Total Suara</p>
                    <p class="mt-[0.2vh] text-[4.3vh] font-black leading-none">{{ number_format($totalVotes) }}</p>
                </div>
            </div>
        </header>

        <main class="min-h-0 flex-1 py-[2.1vh]">
            <div class="grid h-full grid-cols-2 grid-rows-6 gap-x-[1.5vw] gap-y-[1.1vh]">
                @foreach($participants as $index => $participant)
                    @php
                        $rankStyles = match($index) {
                            0 => 'from-amber-300 to-yellow-600 text-slate-950 shadow-amber-500/20',
                            1 => 'from-slate-200 to-slate-500 text-slate-950 shadow-slate-400/20',
                            2 => 'from-amber-600 to-orange-800 text-white shadow-orange-700/20',
                            default => 'from-slate-700 to-slate-800 text-slate-200 shadow-transparent',
                        };
                        $barStyles = match($index) {
                            0 => 'from-yellow-300 via-amber-400 to-orange-500',
                            1 => 'from-indigo-300 to-indigo-500',
                            2 => 'from-orange-400 to-rose-500',
                            default => 'from-indigo-500 to-violet-600',
                        };
                    @endphp
                    <div class="flex min-h-0 items-center gap-[0.8vw] rounded-[1.6vh] border border-white/10 bg-white/[0.07] px-[0.8vw] py-[0.7vh] shadow-lg backdrop-blur-sm">
                        <div class="flex h-[5.4vh] w-[5.4vh] shrink-0 items-center justify-center rounded-[1.2vh] bg-gradient-to-br text-[2.1vh] font-black shadow-lg {{ $rankStyles }}">
                            {{ $index + 1 }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="mb-[0.55vh] flex items-end justify-between gap-[0.8vw]">
                                <div class="min-w-0">
                                    <h2 class="truncate text-[1.75vh] font-black leading-tight text-white">{{ $participant->name }}</h2>
                                    <p class="truncate text-[1.15vh] font-medium text-slate-400">{{ $participant->song_title }}</p>
                                </div>
                                <div class="flex shrink-0 items-baseline gap-[0.35vw]">
                                    <span class="text-[2.5vh] font-black leading-none text-white">{{ $participant->votes_count }}</span>
                                    <span class="text-[1.1vh] font-bold text-slate-400">suara</span>
                                </div>
                            </div>
                            <div class="h-[0.9vh] overflow-hidden rounded-full bg-slate-900/80 ring-1 ring-white/5">
                                <div class="h-full rounded-full bg-gradient-to-r transition-all duration-700 {{ $barStyles }}" style="width: {{ $participant->vote_percentage }}%"></div>
                            </div>
                            <p class="mt-[0.25vh] text-right text-[1.05vh] font-black text-indigo-200">{{ number_format($participant->vote_percentage, 1) }}%</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </main>

        <footer class="flex h-[4vh] shrink-0 items-center justify-between border-t border-white/10 pt-[1vh] text-[1.2vh] font-semibold text-slate-500">
            <span>Hasil diperbarui otomatis setiap 3 detik</span>
            <span>https://bit.ly/korpri-musical-2026</span>
        </footer>
    </div>
</div>
