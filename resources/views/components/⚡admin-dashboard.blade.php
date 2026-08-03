<?php

use Livewire\Component;
use App\Models\Participant;
use App\Models\Judge;
use App\Models\Aspect;
use App\Models\Score;
use App\Models\Vote; // Tambahkan ini

new class extends Component
{
    public $activeTab = 'live'; // 'live', 'peserta', 'juri', 'aspek', 'rekap', 'favorit', 'voting'

    // State untuk Modal Edit
    public $showEditModal = false;
    public $editType = ''; 
    public $editId = null;

    // Field Form
    public $formName = '';
    public $formOrder = '';
    public $formSongTitle = '';
    public $formPasscode = '';
    public $formPercentage = '';

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function setPerforming($participantId)
    {
        $performingParticipants = Participant::where('status', 'performing')->get();
        foreach ($performingParticipants as $active) {
            $scoreCount = Score::where('participant_id', $active->id)->count();
            $active->status = $scoreCount >= count(Judge::all()) ? 'finished' : 'waiting';
            $active->save();
        }
        
        $participant = Participant::find($participantId);
        $participant->status = 'performing';
        $participant->save();
    }

    public function deleteParticipant($id) { Participant::find($id)->delete(); }
    public function deleteJudge($id) { Judge::find($id)->delete(); }
    public function deleteAspect($id) { Aspect::find($id)->delete(); }

    public function openEdit($type, $id)
    {
        $this->editType = $type;
        $this->editId = $id;
        $this->showEditModal = true;

        if ($type === 'peserta') {
            $data = Participant::find($id);
            $this->formName = $data->name;
            $this->formOrder = $data->order_number;
            $this->formSongTitle = $data->song_title;
        } elseif ($type === 'juri') {
            $data = Judge::find($id);
            $this->formName = $data->name;
            $this->formPasscode = $data->passcode;
        } elseif ($type === 'aspek') {
            $data = Aspect::find($id);
            $this->formName = $data->name;
            $this->formPercentage = $data->percentage;
        }
    }

    public function updateData()
    {
        if ($this->editType === 'peserta') {
            Participant::where('id', $this->editId)->update([
                'name' => $this->formName, 
                'order_number' => $this->formOrder,
                'song_title' => $this->formSongTitle
            ]);
        } elseif ($this->editType === 'juri') {
            Judge::where('id', $this->editId)->update([
                'name' => $this->formName, 
                'passcode' => $this->formPasscode
            ]);
        } elseif ($this->editType === 'aspek') {
            Aspect::where('id', $this->editId)->update([
                'name' => $this->formName, 
                'percentage' => $this->formPercentage
            ]);
        }
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->editType = '';
        $this->editId = null;
        $this->reset(['formName', 'formOrder', 'formSongTitle', 'formPasscode', 'formPercentage']);
    }

    public function with(): array
    {
        return [
            'participants' => Participant::with(['scores.details.aspect', 'scores.judge'])->orderBy('order_number')->get(),
            'judges' => Judge::all(),
            'aspects' => Aspect::all(),
            // Data khusus untuk tab voting
            'votingResults' => Participant::withCount('votes')->orderByDesc('votes_count')->get(),
            'totalVotes' => Vote::count(),
        ];
    }

    public function setFavorite($id)
    {
        // Reset semua status favorit terlebih dahulu agar hanya ada 1 juara favorit
        Participant::query()->update(['is_favorite' => false]);
        
        // Set peserta yang dipilih menjadi juara favorit
        Participant::where('id', $id)->update(['is_favorite' => true]);
    }
};
?>

<div class="min-h-screen bg-gray-50 p-8">
    <div class="max-w-7xl mx-auto relative">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h1 class="text-3xl font-black text-gray-800">Administrator Panel</h1>
            
            <!-- Tombol Download Excel -->
            <a href="/admin/export-excel" target="_blank" class="bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-xl shadow-lg flex items-center gap-2 transition">
                📊 Download Rekap Excel (.xlsx)
            </a>
        </div>

        <!-- Sistem Navigasi Tab -->
        <div class="flex space-x-2 mb-6 bg-white p-2 rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
            <button wire:click="setTab('live')" class="whitespace-nowrap px-6 py-3 rounded-lg font-bold transition {{ $activeTab === 'live' ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-100' }}">Live Control</button>
            <button wire:click="setTab('rekap')" class="whitespace-nowrap px-6 py-3 rounded-lg font-bold transition {{ $activeTab === 'rekap' ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-100' }}">Rekap Nilai</button>
            <button wire:click="setTab('peserta')" class="whitespace-nowrap px-6 py-3 rounded-lg font-bold transition {{ $activeTab === 'peserta' ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-100' }}">Kelola Peserta</button>
            <button wire:click="setTab('juri')" class="whitespace-nowrap px-6 py-3 rounded-lg font-bold transition {{ $activeTab === 'juri' ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-100' }}">Kelola Juri</button>
            <button wire:click="setTab('aspek')" class="whitespace-nowrap px-6 py-3 rounded-lg font-bold transition {{ $activeTab === 'aspek' ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-100' }}">Aspek Penilaian</button>
            <button wire:click="setTab('favorit')" class="whitespace-nowrap px-6 py-3 rounded-lg font-bold transition {{ $activeTab === 'favorit' ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-100' }}">Juara Favorit</button>
            <!-- Tombol Tab Baru: Hasil Voting -->
            <button wire:click="setTab('voting')" class="whitespace-nowrap px-6 py-3 rounded-lg font-bold transition {{ $activeTab === 'voting' ? 'bg-pink-600 text-white' : 'text-gray-500 hover:bg-gray-100' }}">📊 Hasil Voting</button>
        </div>

        <!-- TAB: LIVE CONTROL -->
        @if($activeTab === 'live')
        <div wire:poll.2s class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-max">
                <thead>
                    <tr class="bg-indigo-600 text-white">
                        <th class="p-4 text-center font-bold">No</th>
                        <th class="p-4 font-bold">Tim Peserta & Lagu</th>
                        <th class="p-4 font-bold">Status</th>
                        <th class="p-4 text-center font-bold">Juri Menilai</th>
                        <th class="p-4 text-center font-bold">Total Nilai Rata-rata</th>
                        <th class="p-4 text-center font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($participants as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="p-4 text-center font-bold text-gray-500">{{ $p->order_number }}</td>
                            <td class="p-4">
                                <div class="font-bold text-gray-800 text-lg">{{ $p->name }}</div>
                                <div class="text-sm text-gray-500">🎵 {{ $p->song_title ?: 'Belum diisi' }}</div>
                            </td>
                            <td class="p-4">
                                @if($p->status === 'performing')
                                    <span class="px-3 py-1 bg-green-100 text-green-700 font-bold text-xs rounded-full animate-pulse">SEDANG TAMPIL</span>
                                @elseif($p->status === 'finished')
                                    <span class="px-3 py-1 bg-gray-100 text-gray-600 font-bold text-xs rounded-full">SELESAI</span>
                                @else
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 font-bold text-xs rounded-full">MENUNGGU</span>
                                @endif
                            </td>
                            <td class="p-4 text-center font-bold {{ $p->scores->count() >= count($judges) ? 'text-green-500' : 'text-red-500' }}">
                                {{ $p->scores->count() }} / {{ count($judges) }}
                            </td>
                            <td class="p-4 text-center font-black text-xl text-indigo-600">
                                {{ number_format($p->scores->sum('total_score') / (count($judges) > 0 ? count($judges) : 1), 2) }}
                            </td>
                            <td class="p-4 text-center">
                                @if($p->status !== 'performing')
                                    <button wire:click="setPerforming({{ $p->id }})" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg text-sm shadow">Panggil</button>
                                @else
                                    <button disabled class="bg-gray-300 text-gray-500 font-bold py-2 px-4 rounded-lg text-sm cursor-not-allowed">Aktif</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- TAB: REKAP NILAI (DETAIL PER JURI & ASPEK) -->
        @if($activeTab === 'rekap')
        <div class="space-y-6">
            @foreach($participants as $p)
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden p-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b pb-4 mb-4 gap-2">
                    <div>
                        <span class="text-sm font-bold text-indigo-600 uppercase tracking-wider">Urut #{{ $p->order_number }}</span>
                        <h2 class="text-2xl font-black text-gray-800">{{ $p->name }} <span class="text-base font-normal text-gray-500">("{{ $p->song_title ?: '-' }}")</span></h2>
                    </div>
                    <div class="bg-indigo-50 px-4 py-2 rounded-xl text-right">
                        <span class="text-xs text-gray-500 block font-bold">NILAI AKHIR RATA-RATA</span>
                        <span class="text-2xl font-black text-indigo-600">
                            {{ number_format($p->scores->sum('total_score') / (count($judges) > 0 ? count($judges) : 1), 2) }}
                        </span>
                    </div>
                </div>

                @if($p->scores->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($p->scores as $score)
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                        <div class="flex justify-between items-center mb-3 border-b pb-2">
                            <span class="font-bold text-gray-800 text-lg">👨‍⚖️ {{ $score->judge->name ?? 'Juri' }}</span>
                            <span class="bg-indigo-600 text-white text-xs font-bold px-2.5 py-1 rounded-lg">Total: {{ number_format($score->total_score, 2) }}</span>
                        </div>
                        <ul class="space-y-2 text-sm">
                            @foreach($score->details as $detail)
                            <li class="flex justify-between items-center text-gray-600">
                                <span>{{ $detail->aspect->name ?? 'Aspek' }} <span class="text-xs text-gray-400">({{ $detail->aspect->percentage ?? 0 }}%)</span>:</span>
                                <span class="font-bold text-gray-800">
                                    {{ $detail->score_value }} <span class="text-xs text-indigo-500 font-normal">(Bobot: {{ number_format($detail->weighted_score, 2) }})</span>
                                </span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-6 text-gray-400 font-medium italic">
                    Belum ada juri yang memberikan penilaian untuk peserta ini.
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        <!-- TAB: KELOLA PESERTA -->
        @if($activeTab === 'peserta')
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 p-6 overflow-x-auto">
            <h2 class="text-xl font-bold mb-4">Daftar Peserta & Lagu</h2>
            <table class="w-full text-left border-collapse mt-4 min-w-max">
                <tr class="bg-gray-100"><th class="p-3">No Urut</th><th class="p-3">Nama Tim</th><th class="p-3">Judul Lagu</th><th class="p-3">Aksi</th></tr>
                @foreach($participants as $p)
                <tr class="border-b">
                    <td class="p-3 text-center">{{ $p->order_number }}</td>
                    <td class="p-3 font-bold">{{ $p->name }}</td>
                    <td class="p-3 text-gray-600 italic">{{ $p->song_title ?: '-' }}</td>
                    <td class="p-3 flex gap-4">
                        <button wire:click="openEdit('peserta', {{ $p->id }})" class="text-indigo-500 font-bold hover:underline">Edit</button>
                        <button wire:click="deleteParticipant({{ $p->id }})" class="text-red-500 font-bold hover:underline">Hapus</button>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
        @endif

        <!-- TAB: KELOLA JURI -->
        @if($activeTab === 'juri')
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 p-6 overflow-x-auto">
            <h2 class="text-xl font-bold mb-4">Daftar Juri</h2>
            <table class="w-full text-left border-collapse mt-4 min-w-max">
                <tr class="bg-gray-100"><th class="p-3">Nama Juri</th><th class="p-3">Passcode PIN</th><th class="p-3">Aksi</th></tr>
                @foreach($judges as $j)
                <tr class="border-b"><td class="p-3 font-bold">{{ $j->name }}</td><td class="p-3 text-indigo-500 font-mono">{{ $j->passcode }}</td>
                <td class="p-3 flex gap-4">
                    <button wire:click="openEdit('juri', {{ $j->id }})" class="text-indigo-500 font-bold hover:underline">Edit</button>
                    <button wire:click="deleteJudge({{ $j->id }})" class="text-red-500 font-bold hover:underline">Hapus</button>
                </td></tr>
                @endforeach
            </table>
        </div>
        @endif

        <!-- TAB: KELOLA ASPEK -->
        @if($activeTab === 'aspek')
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 p-6 overflow-x-auto">
            <h2 class="text-xl font-bold mb-4">Kriteria Penilaian</h2>
            <table class="w-full text-left border-collapse mt-4 min-w-max">
                <tr class="bg-gray-100"><th class="p-3">Nama Aspek</th><th class="p-3">Bobot Persentase</th><th class="p-3">Aksi</th></tr>
                @foreach($aspects as $a)
                <tr class="border-b"><td class="p-3 font-bold">{{ $a->name }}</td><td class="p-3 text-indigo-500 font-bold">{{ $a->percentage }}%</td>
                <td class="p-3 flex gap-4">
                    <button wire:click="openEdit('aspek', {{ $a->id }})" class="text-indigo-500 font-bold hover:underline">Edit</button>
                    <button wire:click="deleteAspect({{ $a->id }})" class="text-red-500 font-bold hover:underline">Hapus</button>
                </td></tr>
                @endforeach
            </table>
        </div>
        @endif

        <!-- TAB BARU: HASIL VOTING -->
        @if($activeTab === 'voting')
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 p-6 overflow-x-auto" wire:poll.5s>
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">🏆 Rekap Voting Penonton</h2>
                    <p class="text-sm text-gray-500">Halaman ini otomatis diperbarui setiap 5 detik.</p>
                </div>
                <div class="bg-pink-50 text-pink-700 px-6 py-2 rounded-xl text-center border border-pink-100">
                    <span class="text-xs font-bold block uppercase tracking-wider">Total Suara Masuk</span>
                    <span class="text-3xl font-black">{{ $totalVotes }}</span>
                </div>
            </div>
            
            <table class="w-full text-left border-collapse min-w-max">
                <thead>
                    <tr class="bg-gray-100 text-gray-600">
                        <th class="p-3 text-center">Peringkat</th>
                        <th class="p-3">Nama Tim</th>
                        <th class="p-3 text-right">Perolehan Suara</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($votingResults as $index => $p)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-center text-2xl">
                            @if($index === 0 && $p->votes_count > 0) 🥇
                            @elseif($index === 1 && $p->votes_count > 0) 🥈
                            @elseif($index === 2 && $p->votes_count > 0) 🥉
                            @else <span class="text-sm font-bold text-gray-400">#{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td class="p-3 font-bold text-gray-800 text-lg">{{ $p->name }}</td>
                        <td class="p-3 text-right">
                            <span class="bg-pink-100 text-pink-700 font-black px-4 py-1.5 rounded-lg text-lg">
                                {{ $p->votes_count }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- TAB: JUARA FAVORIT (MANUAL OVERRIDE) -->
        @if($activeTab === 'favorit')
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 p-6 overflow-x-auto">
            <h2 class="text-xl font-bold mb-2">Pilih Juara Favorit (Tampilan Layar)</h2>
            <p class="text-sm text-gray-500 mb-6">Pilih salah satu peserta yang akan ditampilkan sebagai Juara Favorit pada layar Leaderboard. Anda bisa menyesuaikannya dengan hasil dari tab Voting di atas.</p>
            
            <table class="w-full text-left border-collapse mt-4 min-w-max">
                <tr class="bg-gray-100"><th class="p-3">No Urut</th><th class="p-3">Nama Tim</th><th class="p-3">Status Favorit</th><th class="p-3">Aksi</th></tr>
                @foreach($participants as $p)
                <tr class="border-b">
                    <td class="p-3 text-center">{{ $p->order_number }}</td>
                    <td class="p-3 font-bold">{{ $p->name }}</td>
                    <td class="p-3">
                        @if($p->is_favorite)
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 font-bold text-xs rounded-full">⭐ JUARA FAVORIT AKTIF</span>
                        @else
                            <span class="text-gray-400 text-sm">-</span>
                        @endif
                    </td>
                    <td class="p-3">
                        <button wire:click="setFavorite({{ $p->id }})" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow transition">
                            Jadikan Favorit
                        </button>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
        @endif

        <!-- MODAL EDIT POP-UP -->
        @if($showEditModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md max-h-[90vh] overflow-y-auto">
                <h2 class="text-2xl font-black text-gray-800 mb-6">
                    Edit {{ ucfirst($editType) }}
                </h2>
                
                <form wire:submit.prevent="updateData" class="space-y-4">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Nama</label>
                        <input type="text" wire:model="formName" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>

                    @if($editType === 'peserta')
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-1">
                            <label class="block font-bold text-gray-700 mb-1">No Urut</label>
                            <input type="number" wire:model="formOrder" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500" required>
                        </div>
                        <div class="col-span-2">
                            <label class="block font-bold text-gray-700 mb-1">Judul Lagu</label>
                            <input type="text" wire:model="formSongTitle" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500">
                        </div>
                    </div>
                    @endif

                    @if($editType === 'juri')
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Passcode PIN</label>
                        <input type="text" wire:model="formPasscode" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500 font-mono" required>
                    </div>
                    @endif

                    @if($editType === 'aspek')
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Bobot Persentase (%)</label>
                        <input type="number" wire:model="formPercentage" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-indigo-500" required>
                    </div>
                    @endif

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" wire:click="closeModal" class="px-5 py-2 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
        
    </div>
</div>