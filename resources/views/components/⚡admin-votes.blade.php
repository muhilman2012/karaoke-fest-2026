<?php

use Livewire\Component;
use App\Models\Participant;
use App\Models\Vote;

new class extends Component
{
    public function with(): array
    {
        $participants = Participant::withCount('votes')
                            ->orderByDesc('votes_count')
                            ->get();
                            
        $totalVotes = Vote::count();

        return [
            'participants' => $participants,
            'totalVotes' => $totalVotes
        ];
    }
};
?>

<div class="min-h-screen bg-gray-100 p-8" wire:poll.5s>
    <div class="max-w-5xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-black text-gray-800">🏆 Rekap Voting Favorit</h1>
                <p class="text-gray-500">Halaman ini otomatis diperbarui setiap 5 detik.</p>
            </div>
            <div class="bg-indigo-600 text-white px-6 py-3 rounded-xl shadow-lg text-center">
                <p class="text-sm font-bold opacity-80">Total Suara Masuk</p>
                <p class="text-3xl font-black">{{ $totalVotes }}</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-4 text-gray-500">Peringkat</th>
                        <th class="p-4 text-gray-500">Nama Peserta</th>
                        <th class="p-4 text-gray-500 text-right">Perolehan Suara</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($participants as $index => $p)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="p-4">
                                @if($index === 0 && $p->votes_count > 0)
                                    <span class="text-2xl">🥇</span>
                                @elseif($index === 1 && $p->votes_count > 0)
                                    <span class="text-2xl">🥈</span>
                                @elseif($index === 2 && $p->votes_count > 0)
                                    <span class="text-2xl">🥉</span>
                                @else
                                    <span class="font-bold text-gray-400 ml-2">#{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="p-4 font-bold text-gray-800 text-lg">{{ $p->name }}</td>
                            <td class="p-4 text-right">
                                <span class="bg-indigo-100 text-indigo-700 font-black px-4 py-2 rounded-lg text-xl">
                                    {{ $p->votes_count }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>