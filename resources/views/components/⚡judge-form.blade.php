<?php

use Livewire\Component;
use App\Models\Participant;
use App\Models\Judge;
use App\Models\Aspect;
use App\Models\Score;
use App\Models\ScoreDetail;

new class extends Component
{
    public $judge_id;
    public $participant_id = null;
    public $aspectScores = []; 
    public $is_locked = false;
    
    public $show_participant_list = false;
    public $is_editing_mode = false;

    protected function rules()
    {
        $rules = [];
        foreach (Aspect::all() as $aspect) {
            $rules['aspectScores.' . $aspect->id] = 'required|numeric|min:10|max:100';
        }
        return $rules;
    }

    protected function messages()
    {
        return [
            'aspectScores.*.required' => 'Nilai aspek ini tidak boleh kosong.',
            'aspectScores.*.numeric' => 'Nilai harus berupa angka.',
            'aspectScores.*.min' => 'Pemberian nilai belum sesuai! Minimal nilai adalah 10.',
            'aspectScores.*.max' => 'Pemberian nilai belum sesuai! Maksimal nilai adalah 100.',
        ];
    }

    public function mount()
    {
        if (!session()->has('judge_id')) {
            return redirect('/juri');
        }
        $this->judge_id = session('judge_id');
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
            $this->is_locked = false;
            $this->aspectScores = [];
            return;
        }

        if ($this->participant_id !== $participant->id) {
            $this->participant_id = $participant->id;
            $this->loadDraft();
        }
    }

    public function toggleParticipantList()
    {
        $this->show_participant_list = !$this->show_participant_list;
        
        if (!$this->show_participant_list && !$this->is_editing_mode) {
            $this->backToLive();
        }
    }

    public function editParticipant($id)
    {
        $this->is_editing_mode = true;
        $this->show_participant_list = false;
        $this->participant_id = $id;
        $this->loadDraft();
    }

    public function backToLive()
    {
        $this->is_editing_mode = false;
        $this->show_participant_list = false;
        $this->participant_id = null;
        $this->checkParticipant();
    }

    public function loadDraft()
    {
        $score = Score::where('participant_id', $this->participant_id)
                      ->where('judge_id', $this->judge_id)
                      ->first();

        if ($score) {
            $this->is_locked = $score->is_locked;
            $details = ScoreDetail::where('score_id', $score->id)->get();
            foreach ($details as $detail) {
                $this->aspectScores[$detail->aspect_id] = $detail->score_value;
            }
        } else {
            $this->is_locked = false;
            $aspects = Aspect::all();
            foreach ($aspects as $aspect) {
                $this->aspectScores[$aspect->id] = '';
            }
        }
    }

    public function saveDraft()
    {
        $this->saveData(false);
        session()->flash('success', 'Nilai sementara berhasil disimpan. Anda masih bisa mengeditnya.');
    }

    public function lockScore()
    {
        $this->saveData(true);
    }

    private function saveData($locked)
    {
        $this->validate();

        $aspects = Aspect::all();
        $totalFinalScore = 0;
        $detailsData = [];

        foreach ($aspects as $aspect) {
            $rawScore = (float) $this->aspectScores[$aspect->id];
            $weightedScore = ($rawScore * $aspect->percentage) / 100;
            $totalFinalScore += $weightedScore;

            $detailsData[] = [
                'aspect_id' => $aspect->id,
                'score_value' => $rawScore,
                'weighted_score' => $weightedScore
            ];
        }

        $scoreRecord = Score::updateOrCreate(
            ['participant_id' => $this->participant_id, 'judge_id' => $this->judge_id],
            ['total_score' => $totalFinalScore, 'is_locked' => $locked]
        );

        ScoreDetail::where('score_id', $scoreRecord->id)->delete();
        foreach ($detailsData as $data) {
            ScoreDetail::create([
                'score_id' => $scoreRecord->id,
                'aspect_id' => $data['aspect_id'],
                'score_value' => $data['score_value'],
                'weighted_score' => $data['weighted_score']
            ]);
        }

        $this->is_locked = $locked;
    }

    public function logout()
    {
        session()->forget('judge_id');
        return redirect('/juri');
    }

    public function with(): array
    {
        $current_participant = Participant::find($this->participant_id);
        $judge = Judge::find($this->judge_id);
        
        $all_participants = Participant::orderBy('order_number')->get();
        $my_scores = Score::where('judge_id', $this->judge_id)->get()->keyBy('participant_id');

        return [
            'current_participant' => $current_participant,
            'judgeName' => $judge ? $judge->name : 'Juri',
            'aspects' => Aspect::all(),
            'all_participants' => $all_participants,
            'my_scores' => $my_scores,
        ];
    }
};
?>

<div class="min-h-screen bg-gray-100 flex flex-col items-center p-4" wire:poll.2s="checkParticipant">
    
    <div class="w-full max-w-4xl flex flex-col md:flex-row justify-between items-center mb-6 bg-white p-4 rounded-2xl shadow-sm gap-4">
        <div class="font-bold text-gray-500">
            Juri: <span class="text-indigo-600">{{ $judgeName }}</span>
        </div>
        
        <div class="flex gap-4">
            <button wire:click="toggleParticipantList" class="bg-indigo-100 text-indigo-700 font-bold px-4 py-2 rounded-xl hover:bg-indigo-200 transition">
                @if($show_participant_list) Tutup Daftar @else 📋 Daftar Peserta @endif
            </button>
            <button wire:click="logout" class="text-red-500 font-bold hover:underline px-2">Logout</button>
        </div>
    </div>

    @if($show_participant_list)
        <div class="w-full max-w-4xl bg-white rounded-3xl shadow-xl overflow-hidden p-8">
            <h2 class="text-2xl font-black text-gray-800 mb-6">Daftar Seluruh Peserta</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($all_participants as $p)
                    @php 
                        $scoreData = $my_scores->get($p->id); 
                        $statusClass = 'bg-gray-100 text-gray-500';
                        $statusText = 'Belum Dinilai';
                        
                        if($scoreData) {
                            if($scoreData->is_locked) {
                                $statusClass = 'bg-green-100 text-green-700 border border-green-300';
                                $statusText = '✅ Terkunci';
                            } else {
                                $statusClass = 'bg-yellow-100 text-yellow-700 border border-yellow-300';
                                $statusText = '📝 Draft (Disimpan)';
                            }
                        }
                    @endphp
                    
                    <div class="p-4 rounded-xl border flex justify-between items-center bg-gray-50 hover:bg-gray-100 transition">
                        <div>
                            <p class="font-bold text-gray-800">{{ $p->order_number }}. {{ $p->name }}</p>
                            <span class="text-xs font-bold px-2 py-1 rounded-md mt-1 inline-block {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </div>
                        <button wire:click="editParticipant({{ $p->id }})" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-indigo-700 text-sm">
                            Lihat / Edit
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

    @else
        @if($is_editing_mode)
            <!-- Banner Mode Edit -->
            <div class="w-full max-w-4xl bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-4 rounded-r-xl flex justify-between items-center shadow-sm">
                <div>
                    <h3 class="font-black text-yellow-700">MODE EDIT MANUAL Aktif</h3>
                    <p class="text-yellow-600 text-sm font-medium">Anda sedang melihat nilai lampau. Tampilan tidak akan mengikuti panggung live.</p>
                </div>
                <button wire:click="backToLive" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold px-4 py-2 rounded-xl text-sm transition shadow">
                    Kembali ke Live
                </button>
            </div>
        @endif

        @if($current_participant)
            <div class="w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden border-t-4 @if($is_editing_mode) border-yellow-500 @else border-indigo-500 @endif">
                <div class="@if($is_editing_mode) bg-yellow-500 @else bg-indigo-600 @endif text-white p-8 text-center transition-all">
                    <h1 class="text-4xl font-black mb-2">{{ $current_participant->name }}</h1>
                    <p class="text-xl font-semibold opacity-90">🎵 {{ $current_participant->song_title ?: 'Lagu Belum Ditentukan' }}</p>
                </div>

                @if($is_locked)
                    <div class="p-16 text-center">
                        <div class="text-green-500 text-8xl mb-6">✅</div>
                        <h2 class="text-4xl font-bold text-gray-800 mb-4">Nilai Terkunci & Terkirim!</h2>
                        <p class="text-xl text-gray-500">Nilai peserta ini sudah dikunci dan masuk ke sistem panggung.</p>
                    </div>
                @else
                    <div class="p-8 space-y-8">
                        @if (session()->has('success'))
                            <div class="px-6 py-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-lg text-lg font-bold">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            @foreach($aspects as $index => $aspect)
                            <div class="bg-gray-50 p-6 rounded-2xl border-2 @error('aspectScores.'.$aspect->id) border-red-400 @else border-gray-100 @enderror">
                                <label class="block text-xl font-bold text-gray-700 mb-1">
                                    {{ $index + 1 }}. {{ $aspect->name }} ({{ $aspect->percentage }}%)
                                </label>
                                <p class="text-sm text-gray-500 mb-4">{{ $aspect->description }}</p>
                                
                                <input type="number" inputmode="numeric" min="10" max="100" 
                                    wire:model="aspectScores.{{ $aspect->id }}" 
                                    class="w-full text-center text-5xl font-black text-indigo-600 p-4 rounded-xl border-2 @error('aspectScores.'.$aspect->id) border-red-500 bg-red-50 @else border-gray-300 @enderror focus:border-indigo-500">
                                
                                @error('aspectScores.'.$aspect->id)
                                    <p class="mt-3 text-red-500 text-sm font-bold animate-pulse">
                                        ⚠️ {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            @endforeach
                        </div>

                        <div class="flex flex-col md:flex-row gap-4 mt-8">
                            <button type="button" wire:click="saveDraft"
                                class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white text-2xl font-black py-6 rounded-2xl shadow-lg transition duration-200 transform hover:scale-105">
                                📝 SIMPAN SEMENTARA
                            </button>
                            
                            <button type="button" wire:click="lockScore"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-2xl font-black py-6 rounded-2xl shadow-lg transition duration-200 transform hover:scale-105">
                                🔒 KUNCI NILAI & KIRIM
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="text-center mt-20">
                <h2 class="text-3xl font-bold text-gray-400">Belum ada peserta yang tampil di panggung.</h2>
            </div>
        @endif
    @endif
</div>