<?php

use Livewire\Component;
use App\Models\Judge;

new class extends Component
{
    public $pin = '';
    public $errorMessage = '';

    public function login()
    {
        // Cari juri berdasarkan passcode PIN
        $judge = Judge::where('passcode', $this->pin)->first();

        if ($judge) {
            // Jika benar, simpan ID juri ke dalam Session dan arahkan ke form
            session(['judge_id' => $judge->id]);
            return redirect('/juri/form');
        } else {
            // Jika salah, tampilkan error dan kosongkan input
            $this->errorMessage = 'PIN salah. Silakan periksa kembali.';
            $this->pin = '';
        }
    }
};
?>

<div class="min-h-screen bg-gray-900 flex items-center justify-center p-4">
    <div class="bg-white p-10 rounded-3xl shadow-2xl w-full max-w-sm text-center">
        <div class="mb-8">
            <h1 class="text-3xl font-black text-gray-800 mb-2">Login Juri</h1>
            <p class="text-gray-500">Masukkan 4 digit PIN Anda</p>
        </div>

        <form wire:submit.prevent="login" class="space-y-6">
            <div>
                <!-- Input type password tapi memunculkan numpad di tablet -->
                <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="4" wire:model="pin" 
                    class="w-full text-center text-5xl tracking-[0.5em] font-black text-indigo-600 p-4 rounded-xl border-2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition-all" 
                    placeholder="••••" required autofocus>
            </div>

            @if($errorMessage)
                <div class="text-red-500 font-bold animate-pulse">{{ $errorMessage }}</div>
            @endif

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-2xl font-black py-4 rounded-xl shadow-lg transition">
                MASUK
            </button>
        </form>
    </div>
</div>