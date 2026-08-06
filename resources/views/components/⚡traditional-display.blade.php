<?php

use Livewire\Component;
use Illuminate\Support\Facades\Cache;

new class extends Component
{
    public function with(): array
    {
        $defaultGames = [
            'estafet_bola' => ['name' => 'Estafet Bola', 'r1' => '', 'r2' => '', 'r3' => ''],
            'estafet_sarung' => ['name' => 'Estafet Sarung', 'r1' => '', 'r2' => '', 'r3' => ''],
            'estafet_kelereng' => ['name' => 'Estafet Kelereng', 'r1' => '', 'r2' => '', 'r3' => ''],
            'paku_botol' => ['name' => 'Paku Botol', 'r1' => '', 'r2' => '', 'r3' => ''],
            'estafet_air' => ['name' => 'Estafet Air', 'r1' => '', 'r2' => '', 'r3' => ''],
        ];

        return [
            'games' => Cache::get('traditional_winners', $defaultGames)
        ];
    }
};
?>

<div class="min-h-screen bg-slate-900 py-10 px-6 flex flex-col items-center" wire:poll.2s>
    
    <div class="text-center w-full mb-10">
        <h1 class="text-5xl md:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-emerald-600 drop-shadow-2xl tracking-widest uppercase mb-2">
            PESTA LOMBA TRADISIONAL SETWAPRES
        </h1>
        <p class="text-xl text-slate-300 font-bold">Kobarkan Semangat Kemerdekaan, Rayakan Kebersamaan</p>
    </div>

    <div class="flex flex-wrap justify-center gap-8 w-full max-w-7xl">
        @foreach($games as $game)
            <div class="bg-slate-800 rounded-3xl p-6 border-b-4 border-indigo-500 shadow-2xl w-full md:w-[46%] xl:w-[31%] flex flex-col transform transition hover:-translate-y-2 min-h-[400px]">
                
                <h2 class="text-center text-xl md:text-2xl font-black text-white bg-slate-900 py-3 rounded-xl border border-slate-700 shadow-inner">
                    {{ $game['name'] }}
                </h2>

                <div class="flex flex-1 justify-center items-end gap-2 px-2 mt-8">
                    
                    <!-- JUARA 2 (KIRI) -->
                    <div class="w-1/3 flex flex-col items-center h-full justify-end">
                        <!-- 'truncate' diganti 'break-words leading-tight' -->
                        <div class="w-full text-center text-sm font-bold text-slate-200 mb-2 px-1 break-words leading-tight">
                            {{ $game['r2'] ?: '????' }}
                        </div>
                        <div class="h-[60%] w-full bg-gradient-to-t from-slate-600 to-slate-400 rounded-t-lg border-t-2 border-l-2 border-slate-300 flex justify-center items-start pt-4 shadow-lg">
                            <span class="text-3xl font-black text-slate-900 opacity-70">2</span>
                        </div>
                    </div>

                    <!-- JUARA 1 (TENGAH - PALING TINGGI) -->
                    <div class="w-1/3 flex flex-col items-center h-full justify-end relative z-10">
                        <div class="text-4xl mb-1 animate-bounce drop-shadow-xl" style="animation-duration: 2s;">🏆</div>
                        <!-- 'truncate' diganti 'break-words leading-tight' -->
                        <div class="w-full text-center text-lg font-black text-yellow-400 mb-2 px-1 drop-shadow-md break-words leading-tight">
                            {{ $game['r1'] ?: '????' }}
                        </div>
                        <div class="h-[80%] w-full bg-gradient-to-t from-yellow-700 via-yellow-500 to-yellow-400 rounded-t-xl border-t-4 border-yellow-200 flex justify-center items-start pt-4 shadow-[0_0_20px_rgba(250,204,21,0.4)]">
                            <span class="text-5xl font-black text-yellow-900">1</span>
                        </div>
                    </div>

                    <!-- JUARA 3 (KANAN - PALING RENDAH) -->
                    <div class="w-1/3 flex flex-col items-center h-full justify-end">
                        <!-- 'truncate' diganti 'break-words leading-tight' -->
                        <div class="w-full text-center text-xs font-bold text-orange-300 mb-2 px-1 break-words leading-tight">
                            {{ $game['r3'] ?: '????' }}
                        </div>
                        <div class="h-[45%] w-full bg-gradient-to-t from-orange-900 to-orange-600 rounded-t-lg border-t-2 border-r-2 border-orange-400 flex justify-center items-start pt-3 shadow-lg">
                            <span class="text-2xl font-black text-orange-950 opacity-80">3</span>
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    </div>
</div>