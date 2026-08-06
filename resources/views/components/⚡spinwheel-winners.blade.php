<?php

use Livewire\Component;
use App\Models\SpinwheelItem;

new class extends Component
{
    public function with(): array
    {
        return [
            // Ambil daftar pemenang
            'winners' => SpinwheelItem::where('is_winner', true)->orderBy('won_at', 'desc')->get()
        ];
    }
};
?>

<!-- wire:poll.2s membuat halaman ini ter-refresh otomatis setiap 2 detik -->
<div class="min-h-screen bg-slate-900 p-8 md:p-12" wire:poll.2s>
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-pink-400 to-indigo-400 inline-block mb-2">🏆 HALL OF FAME 🏆</h1>
            <p class="text-lg text-slate-400">Daftar Pemenang Roda Keberuntungan</p>
        </div>

        <!-- 1 KOTAK UTAMA -->
        <div class="bg-slate-800 rounded-2xl border border-slate-700 shadow-2xl overflow-hidden">
            @if($winners->isEmpty())
                <div class="p-12 text-center">
                    <div class="text-5xl mb-4">🎁</div>
                    <h2 class="text-2xl font-bold text-slate-300">Belum ada pemenang</h2>
                    <p class="text-slate-500 mt-2">Pemenang akan muncul secara otomatis di sini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <!-- TAMPILAN TABEL (SPREADSHEET STYLE) -->
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900 text-slate-300 uppercase text-sm tracking-wider border-b border-slate-700">
                                <th class="p-4 font-bold text-center w-16">#</th>
                                <th class="p-4 font-bold">Nama Pemenang</th>
                                <th class="p-4 font-bold">Hadiah</th>
                                <th class="p-4 font-bold text-center">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($winners as $i => $w)
                                <tr class="border-b border-slate-700/50 hover:bg-slate-700 transition duration-150">
                                    <td class="p-4 text-center font-bold text-pink-500">
                                        {{ $i + 1 }}
                                    </td>
                                    <td class="p-4 font-bold text-white text-lg">
                                        {{ $w->name }}
                                    </td>
                                    <td class="p-4 text-indigo-400 font-semibold">
                                        {{ $w->prize ?: '-' }}
                                    </td>
                                    <td class="p-4 text-center text-slate-300">
                                        {{ \Carbon\Carbon::parse($w->won_at)->timezone('Asia/Jakarta')->format('H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>