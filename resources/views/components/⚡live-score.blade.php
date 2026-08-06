<?php

use Livewire\Component;
use App\Models\Participant;
use App\Models\Judge;

new class extends Component
{
    public function with(): array
    {
        return [
            'totalJudges' => Judge::count()
        ];
    }
};
?>

<div class="min-h-screen bg-gray-900 text-white flex flex-col items-center justify-center p-8 font-sans overflow-hidden">
    
    <div class="absolute inset-0 z-0 opacity-20">
        <div class="absolute w-96 h-96 bg-indigo-500 rounded-full blur-3xl top-10 left-10 animate-pulse"></div>
        <div class="absolute w-96 h-96 bg-purple-600 rounded-full blur-3xl bottom-10 right-10 animate-pulse" style="animation-delay: 1s;"></div>
    </div>

    <div class="z-10 w-full max-w-6xl text-center">
        <div id="live-container">
            <div class="text-center">
                <img src="{{ asset('logo/setneg.png') }}" alt="Logo" class="w-48 h-48 mx-auto mb-12 opacity-50">
                <h1 class="text-6xl font-black text-gray-300 mb-6 tracking-tight">DEFILE COMPETITION</h1>
                <p class="text-3xl text-indigo-400 animate-pulse">Menghubungkan ke pusat data...</p>
            </div>
        </div>
    </div>
</div>