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

    // 1. Aturan Validasi
    protected function rules()
    {
        $rules = [];
        foreach (Aspect::all() as $aspect) {
            $rules['aspectScores.' . $aspect->id] = 'required|numeric|min:10|max:100';
        }
        return $rules;
    }

    // 2. Pesan Error Validasi Kustom
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
        // Tolak akses jika belum login
        if (!session()->has('judge_id')) {
            return redirect('/juri');
        }
        $this->judge_id = session('judge_id');
        
        // Panggil pengecekan peserta saat pertama kali muat
        $this->checkParticipant();
    }

    // 3. Pengecekan Peserta (Dipanggil tiap 2 detik oleh wire:poll)
    public function checkParticipant()
    {
        $participant = Participant::where('status', 'performing')->first();
        
        // Jika panggung kosong
        if (!$participant) {
            $this->participant_id = null;
            $this->is_locked = false;
            $this->aspectScores = [];
            return;
        }

        // Jika peserta baru saja naik atau ganti, muat draf nilainya
        if ($this->participant_id !== $participant->id) {
            $this->participant_id = $participant->id;
            $this->loadDraft();
        }
    }

    // 4. Memuat nilai yang sudah disimpan (Draft) sebelumnya
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

    // 5. Simpan Sementara
    public function saveDraft()
    {
        $this->saveData(false); // is_locked = false
        session()->flash('success', 'Nilai sementara berhasil disimpan. Anda masih bisa mengeditnya.');
    }

    // 6. Kunci Nilai & Kirim
    public function lockScore()
    {
        $this->saveData(true); // is_locked = true
    }

    // 7. Logika Utama Penyimpanan (Digunakan untuk Draft dan Lock)
    private function saveData($locked)
    {
        $this->validate();

        $aspects = Aspect::all();
        $totalFinalScore = 0;
        $detailsData = [];

        foreach ($aspects as $aspect) {
            $rawScore = (float) $this->aspectScores[$aspect->id];
            
            // Hitung: (Nilai Mentah * Persentase) / 100
            $weightedScore = ($rawScore * $aspect->percentage) / 100;
            $totalFinalScore += $weightedScore;

            $detailsData[] = [
                'aspect_id' => $aspect->id,
                'score_value' => $rawScore,
                'weighted_score' => $weightedScore
            ];
        }

        // Simpan / Update ke tabel Induk
        $scoreRecord = Score::updateOrCreate(
            [
                'participant_id' => $this->participant_id, 
                'judge_id' => $this->judge_id
            ],
            [
                'total_score' => $totalFinalScore,
                'is_locked' => $locked
            ]
        );

        // Hapus detail lama agar tidak ganda, lalu simpan yang baru
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
        $participant = Participant::where('status', 'performing')->first();
        $judge = Judge::find($this->judge_id);

        return [
            'participant' => $participant,
            'judgeName' => $judge ? $judge->name : 'Juri',
            'aspects' => Aspect::all()
        ];
    }
};
?>

<!-- wire:poll.2s sekarang memanggil fungsi checkParticipant agar form tidak ke-reset saat ngetik -->
<div class="min-h-screen bg-gray-100 flex flex-col items-center p-4" wire:poll.2s="checkParticipant">
    
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

            @if($is_locked)
                <div class="p-16 text-center animate-pulse">
                    <div class="text-green-500 text-8xl mb-6">✅</div>
                    <h2 class="text-4xl font-bold text-gray-800 mb-4">Nilai Terkunci & Terkirim!</h2>
                    <p class="text-2xl text-gray-500">Silakan tunggu admin memanggil peserta selanjutnya.</p>
                </div>
            @else
                <div class="p-8 space-y-8">
                    
                    <!-- Alert Sukses Simpan Draf -->
                    @if (session()->has('success'))
                        <div class="px-6 py-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-lg text-lg font-bold">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <!-- Looping Aspek Secara Dinamis -->
                        @foreach($aspects as $index => $aspect)
                        <div class="bg-gray-50 p-6 rounded-2xl border-2 @error('aspectScores.'.$aspect->id) border-red-400 @else border-gray-100 @enderror">
                            <label class="block text-xl font-bold text-gray-700 mb-1">
                                {{ $index + 1 }}. {{ $aspect->name }} ({{ $aspect->percentage }}%)
                            </label>
                            <p class="text-sm text-gray-500 mb-4">{{ $aspect->description }}</p>
                            
                            <input type="number" inputmode="numeric" min="10" max="100" 
                                wire:model="aspectScores.{{ $aspect->id }}" 
                                class="w-full text-center text-5xl font-black text-indigo-600 p-4 rounded-xl border-2 @error('aspectScores.'.$aspect->id) border-red-500 bg-red-50 @else border-gray-300 @enderror focus:border-indigo-500">
                            
                            <!-- Alert Peringatan Validasi Angka -->
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
</div>