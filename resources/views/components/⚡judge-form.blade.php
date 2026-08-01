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
    public $aspectScores = []; // Array untuk menampung nilai dinamis (aspect_id => nilai)

    public function mount()
    {
        // Tolak akses jika belum login (tidak ada session)
        if (!session()->has('judge_id')) {
            return redirect('/juri');
        }
        $this->judge_id = session('judge_id');

        // Siapkan kerangka array input form berdasarkan jumlah aspek di database
        $aspects = Aspect::all();
        foreach ($aspects as $aspect) {
            $this->aspectScores[$aspect->id] = '';
        }
    }

    public function submitScore()
    {
        $participant = Participant::where('status', 'performing')->first();
        if (!$participant) return;

        $aspects = Aspect::all();
        $totalFinalScore = 0;

        // 1. Buat record induk di tabel scores (nilai total sementara 0)
        $scoreRecord = Score::create([
            'participant_id' => $participant->id,
            'judge_id' => $this->judge_id,
            'total_score' => 0
        ]);

        // 2. Looping untuk menghitung bobot per aspek dan menyimpannya di score_details
        foreach ($aspects as $aspect) {
            $rawScore = (float) $this->aspectScores[$aspect->id];
            
            // Hitung: (Nilai Mentah * Persentase) / 100
            $weightedScore = ($rawScore * $aspect->percentage) / 100;
            $totalFinalScore += $weightedScore;

            ScoreDetail::create([
                'score_id' => $scoreRecord->id,
                'aspect_id' => $aspect->id,
                'score_value' => $rawScore,
                'weighted_score' => $weightedScore
            ]);
        }

        // 3. Update total akhir di tabel induk
        $scoreRecord->update(['total_score' => $totalFinalScore]);

        // 4. Kosongkan form kembali
        foreach ($this->aspectScores as $key => $val) {
            $this->aspectScores[$key] = '';
        }
    }

    public function logout()
    {
        session()->forget('judge_id');
        return redirect('/juri');
    }

    public function with(): array
    {
        $participant = Participant::where('status', 'performing')->first();
        $isSubmitted = false;
        $judge = Judge::find($this->judge_id);

        if ($participant) {
            $isSubmitted = Score::where('participant_id', $participant->id)
                                ->where('judge_id', $this->judge_id)
                                ->exists();
        }

        return [
            'participant' => $participant,
            'isSubmitted' => $isSubmitted,
            'judgeName' => $judge ? $judge->name : 'Juri',
            'aspects' => Aspect::all() // Kirim data aspek ke UI
        ];
    }
};
?>

<div class="min-h-screen bg-gray-100 flex flex-col items-center p-4" wire:poll.2s>
    
    <!-- Tombol Logout Juri -->
    <div class="w-full max-w-4xl flex justify-between items-center mb-4">
        <div class="font-bold text-gray-500">Login sebagai: <span class="text-indigo-600">{{ $judgeName }}</span></div>
        <button wire:click="logout" class="text-red-500 font-bold hover:underline">Keluar (Logout)</button>
    </div>

    @if($participant)
        <div class="w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden">
            <div class="bg-indigo-600 text-white p-8 text-center transition-all">
                <h1 class="text-4xl font-black mb-2">{{ $participant->name }}</h1>
                <p class="text-xl text-indigo-200 font-semibold">🎵 {{ $participant->song_title ?: 'Lagu Belum Ditentukan' }}</p>
            </div>

            @if($isSubmitted)
                <div class="p-16 text-center animate-pulse">
                    <div class="text-green-500 text-8xl mb-6">✅</div>
                    <h2 class="text-4xl font-bold text-gray-800 mb-4">Nilai Terkirim!</h2>
                    <p class="text-2xl text-gray-500">Silakan tunggu admin memanggil peserta selanjutnya.</p>
                </div>
            @else
                <form wire:submit.prevent="submitScore" class="p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <!-- Looping Aspek Secara Dinamis -->
                        @foreach($aspects as $index => $aspect)
                        <div class="bg-gray-50 p-6 rounded-2xl border-2 border-gray-100">
                            <label class="block text-xl font-bold text-gray-700 mb-1">
                                {{ $index + 1 }}. {{ $aspect->name }} ({{ $aspect->percentage }}%)
                            </label>
                            <p class="text-sm text-gray-500 mb-4">{{ $aspect->description }}</p>
                            
                            <input type="number" inputmode="numeric" pattern="[0-9]*" min="0" max="100" 
                                wire:model="aspectScores.{{ $aspect->id }}" 
                                class="w-full text-center text-5xl font-black text-indigo-600 p-4 rounded-xl border-2 border-gray-300 focus:border-indigo-500" required>
                        </div>
                        @endforeach

                    </div>

                    <button type="submit" 
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-3xl font-black py-8 rounded-2xl shadow-lg transition">
                        KUNCI NILAI & KIRIM
                    </button>
                </form>
            @endif
        </div>
    @else
        <div class="text-center mt-20">
            <h2 class="text-3xl font-bold text-gray-400">Belum ada peserta yang tampil di panggung.</h2>
        </div>
    @endif
</div>