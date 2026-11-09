<?php

use App\Models\Participant;
use App\Models\SupporterJudge;
use App\Models\SupporterScore;
use Livewire\Component;

new class extends Component
{
    public $judge_id;
    public $participant_id = null;
    public $score = null;
    public $is_locked = false;
    public $show_participant_list = false;
    public $is_editing_mode = false;

    public function mount()
    {
        if (!session()->has('supporter_judge_id')) {
            return redirect()->route('supporter.login');
        }

        $this->judge_id = session('supporter_judge_id');
        $this->checkParticipant();
    }

    public function checkParticipant()
    {
        if ($this->show_participant_list || $this->is_editing_mode) {
            return;
        }

        $participant = Participant::where('status', 'performing')->first();

        if (!$participant) {
            $this->participant_id = null;
            $this->score = null;
            $this->is_locked = false;
            return;
        }

        if ($this->participant_id !== $participant->id) {
            $this->participant_id = $participant->id;
            $this->loadScore();
        }
    }

    public function chooseScore(int $score)
    {
        if (!$this->is_locked && $score >= 6 && $score <= 10) {
            $this->score = $score;
        }
    }

    public function saveDraft()
    {
        $this->saveScore(false);
        session()->flash('success', 'Nilai sementara berhasil disimpan.');
    }

    public function lockScore()
    {
        $this->saveScore(true);
        session()->flash('success', 'Nilai Best Supporter berhasil dikunci.');
    }

    private function saveScore(bool $locked)
    {
        $this->validate([
            'participant_id' => ['required', 'exists:participants,id'],
            'score' => ['required', 'integer', 'min:6', 'max:10'],
        ], [
            'score.required' => 'Pilih nilai terlebih dahulu.',
            'score.min' => 'Nilai minimal adalah 6.',
            'score.max' => 'Nilai maksimal adalah 10.',
        ]);

        $existing = SupporterScore::where('participant_id', $this->participant_id)
            ->where('supporter_judge_id', $this->judge_id)
            ->first();

        if ($existing?->is_locked) {
            $this->is_locked = true;
            return;
        }

        SupporterScore::updateOrCreate(
            ['participant_id' => $this->participant_id, 'supporter_judge_id' => $this->judge_id],
            ['score' => $this->score, 'is_locked' => $locked]
        );

        $this->is_locked = $locked;
    }

    public function toggleParticipantList()
    {
        $this->show_participant_list = !$this->show_participant_list;
        if (!$this->show_participant_list && !$this->is_editing_mode) {
            $this->backToLive();
        }
    }

    public function editParticipant(int $id)
    {
        abort_unless(Participant::whereKey($id)->exists(), 404);
        $this->is_editing_mode = true;
        $this->show_participant_list = false;
        $this->participant_id = $id;
        $this->loadScore();
    }

    public function backToLive()
    {
        $this->is_editing_mode = false;
        $this->show_participant_list = false;
        $this->participant_id = null;
        $this->checkParticipant();
    }

    private function loadScore()
    {
        $record = SupporterScore::where('participant_id', $this->participant_id)
            ->where('supporter_judge_id', $this->judge_id)
            ->first();

        $this->score = $record?->score;
        $this->is_locked = (bool) $record?->is_locked;
    }

    public function logout()
    {
        session()->forget('supporter_judge_id');
        request()->session()->regenerate();
        return redirect()->route('supporter.login');
    }

    public function with(): array
    {
        return [
            'currentParticipant' => Participant::find($this->participant_id),
            'judge' => SupporterJudge::find($this->judge_id),
            'participants' => Participant::orderBy('order_number')->get(),
            'myScores' => SupporterScore::where('supporter_judge_id', $this->judge_id)
                ->get()->keyBy('participant_id'),
        ];
    }
};
?>

<div class="min-h-screen bg-gradient-to-b from-slate-100 to-orange-50 px-3 py-4 sm:px-4 sm:py-6" wire:poll.3s="checkParticipant">
    <div class="mx-auto max-w-3xl">
        <header class="mb-4 flex items-center justify-between gap-3 rounded-2xl border border-white bg-white/90 p-3 shadow-sm backdrop-blur sm:p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-100 text-xl">📣</div>
                <div>
                    <div class="flex items-center gap-2">
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-500">Best Supporter</p>
                        @if(!$is_editing_mode && $currentParticipant)
                            <span class="rounded-full bg-red-100 px-2 py-0.5 text-[9px] font-black uppercase text-red-600">Live</span>
                        @endif
                    </div>
                    <p class="text-lg font-black leading-tight text-slate-800">{{ $judge?->name ?? 'Juri' }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <button wire:click="toggleParticipantList" class="rounded-xl bg-orange-100 px-3 py-2 text-xs font-black text-orange-700 transition hover:bg-orange-200 sm:text-sm">
                    {{ $show_participant_list ? 'Tutup' : '📋 Daftar' }}
                </button>
                <button wire:click="logout" class="px-1 text-xs font-black text-red-500 sm:px-2 sm:text-sm">Logout</button>
            </div>
        </header>

        @if($show_participant_list)
            <section class="rounded-3xl bg-white p-5 shadow-xl sm:p-7">
                <h2 class="mb-5 text-2xl font-black text-slate-800">Daftar Instansi</h2>
                <div class="space-y-3">
                    @foreach($participants as $participant)
                        @php($saved = $myScores->get($participant->id))
                        <button wire:click="editParticipant({{ $participant->id }})"
                            class="flex w-full items-center justify-between gap-3 rounded-2xl border p-4 text-left transition hover:border-orange-400 hover:bg-orange-50">
                            <span class="font-bold text-slate-800">{{ $participant->order_number }}. {{ $participant->name }}</span>
                            <span class="shrink-0 rounded-lg px-2 py-1 text-xs font-black {{ $saved?->is_locked ? 'bg-green-100 text-green-700' : ($saved ? 'bg-yellow-100 text-yellow-700' : 'bg-slate-100 text-slate-500') }}">
                                {{ $saved?->is_locked ? '✓ Terkunci' : ($saved ? 'Draft' : 'Belum') }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </section>
        @elseif($currentParticipant)
            @if($is_editing_mode)
                <div class="mb-4 flex items-center justify-between rounded-xl border-l-4 border-yellow-500 bg-yellow-100 p-4">
                    <span class="font-bold text-yellow-800">Mode pilih dari daftar</span>
                    <button wire:click="backToLive" class="rounded-lg bg-yellow-500 px-3 py-2 text-sm font-bold text-white">Kembali ke Live</button>
                </div>
            @endif

            <section class="overflow-hidden rounded-[1.75rem] border border-white bg-white shadow-xl shadow-slate-300/50">
                <div class="relative overflow-hidden bg-gradient-to-r from-orange-600 to-amber-500 px-5 py-7 text-center text-white sm:py-9">
                    <div class="absolute -right-8 -top-12 h-36 w-36 rounded-full bg-white/10"></div>
                    <div class="absolute -bottom-16 -left-8 h-32 w-32 rounded-full bg-orange-950/10"></div>
                    <p class="relative mb-2 text-xs font-black uppercase tracking-[0.2em] text-orange-100">Instansi yang dinilai</p>
                    <h1 class="relative text-2xl font-black leading-tight sm:text-4xl">{{ $currentParticipant->name }}</h1>
                    <p class="relative mt-2 text-xs font-semibold text-orange-100">Urutan tampil #{{ $currentParticipant->order_number }}</p>
                </div>

                <div class="p-5 sm:p-8">
                    <div class="mb-7 rounded-2xl border border-orange-100 bg-orange-50 p-4 text-center">
                        <p class="mb-3 text-sm font-black text-orange-950">Satu nilai mewakili seluruh aspek</p>
                        <div class="flex flex-wrap justify-center gap-2">
                            @foreach(['Kehebohan', 'Kekompakan', 'Kreativitas', 'Ke-all-out-an'] as $aspect)
                                <span class="rounded-full bg-white px-3 py-1.5 text-xs font-bold text-orange-700 shadow-sm">{{ $aspect }}</span>
                            @endforeach
                        </div>
                    </div>

                    @if(session()->has('success'))
                        <div class="mb-5 rounded-xl bg-green-100 p-4 text-center font-bold text-green-700">{{ session('success') }}</div>
                    @endif

                    @if($is_locked)
                        <div class="py-8 text-center">
                            <div class="mb-4 text-6xl">✅</div>
                            <h2 class="mb-2 text-2xl font-black text-slate-800">Nilai Terkunci</h2>
                            <div class="mx-auto mt-5 flex h-24 w-24 items-center justify-center rounded-full bg-green-600 text-5xl font-black text-white">{{ $score }}</div>
                        </div>
                    @else
                        <div class="mb-4 text-center">
                            <p class="text-lg font-black text-slate-800">Pilih nilai</p>
                            <p class="text-xs text-slate-500">Rentang nilai 6 sampai 10</p>
                        </div>
                        <div class="grid grid-cols-5 gap-2 sm:gap-4">
                            @foreach(range(6, 10) as $value)
                                <button type="button" wire:click="chooseScore({{ $value }})"
                                    class="relative aspect-square rounded-2xl border text-2xl font-black transition sm:text-4xl {{ (int) $score === $value ? 'scale-105 border-orange-500 bg-gradient-to-br from-orange-600 to-amber-500 text-white shadow-lg shadow-orange-200 ring-4 ring-orange-100' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-orange-300 hover:bg-orange-50' }}">
                                    {{ $value }}@if((int) $score === $value)<span class="absolute right-1 top-1 text-[10px] sm:right-2 sm:top-2 sm:text-xs">✓</span>@endif
                                </button>
                            @endforeach
                        </div>
                        @error('score')<p class="mt-3 text-center font-bold text-red-500">{{ $message }}</p>@enderror

                        <div class="mt-7 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <button wire:click="saveDraft" class="rounded-2xl border border-slate-200 bg-white py-4 font-black text-slate-700 shadow-sm transition hover:bg-slate-50">Simpan Draft</button>
                            <button wire:click="lockScore" wire:confirm="Nilai yang dikunci tidak dapat diubah. Lanjutkan?" class="rounded-2xl bg-gradient-to-r from-orange-600 to-amber-500 py-4 font-black text-white shadow-lg shadow-orange-200 transition hover:-translate-y-0.5">Kunci & Kirim</button>
                        </div>
                    @endif
                </div>
            </section>
        @else
            <div class="rounded-3xl bg-white p-10 text-center shadow-xl">
                <div class="mb-4 text-6xl">⏳</div>
                <h2 class="text-2xl font-black text-slate-800">Menunggu Peserta</h2>
                <p class="mt-2 text-slate-500">Admin belum mengaktifkan peserta. Anda juga dapat memilih melalui tombol Daftar.</p>
            </div>
        @endif
    </div>
</div>
