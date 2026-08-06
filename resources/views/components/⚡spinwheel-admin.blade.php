<?php

use Livewire\Component;
use App\Models\SpinwheelItem;
use Illuminate\Support\Facades\Cache;

new class extends Component
{
    public $bulkNames = '';
    public $spinDuration = 5;
    public $currentPrize = ''; 
    public $eventTitle = ''; 

    public function mount()
    {
        if (!session('is_admin')) {
            return redirect('/admin/login');
        }
        $this->spinDuration = Cache::get('spin_duration', 5);
        $this->eventTitle = Cache::get('spin_event_title', 'ACARA PUNCAK HUT Ke-81'); 
    }

    public function updatedEventTitle($value)
    {
        Cache::forever('spin_event_title', $value);
    }

    public function saveNames()
    {
        if (empty(trim($this->bulkNames))) return;

        $names = explode("\n", str_replace("\r", "", $this->bulkNames));
        $count = 0;

        $existingNames = SpinwheelItem::pluck('name')->map(function($name) {
            return strtolower(trim($name));
        })->toArray();

        foreach ($names as $name) {
            $cleanName = trim($name);
            
            if ($cleanName !== '') {
                $lowerName = strtolower($cleanName);
                
                if (!in_array($lowerName, $existingNames)) {
                    SpinwheelItem::create([
                        'name' => $cleanName,
                        'win_probability' => 10 
                    ]);
                    $count++;
                    $existingNames[] = $lowerName; 
                }
            }
        }
        
        $this->bulkNames = '';
        
        if ($count > 0) {
            $this->dispatch('swal:success', ['title' => 'Berhasil!', 'text' => $count . ' nama baru ditambahkan (nama duplikat diabaikan).', 'icon' => 'success']);
        } else {
            $this->dispatch('swal:error', ['title' => 'Peringatan', 'text' => 'Tidak ada nama baru yang ditambahkan. Semua nama yang diinput sudah ada di sistem / sudah menang.', 'icon' => 'warning']);
        }
    }

    public function triggerSpin()
    {
        $items = SpinwheelItem::where('is_winner', false)->get();
        if ($items->isEmpty()) {
            $this->dispatch('swal:error', ['title' => 'Kosong', 'text' => 'Data peserta habis!', 'icon' => 'error']);
            return;
        }

        $totalWeight = $items->sum('win_probability');
        $random = mt_rand(1, $totalWeight);
        $current = 0;
        $winnerId = null;

        foreach ($items as $item) {
            $current += $item->win_probability;
            if ($random <= $current) {
                $winnerId = $item->id;
                break;
            }
        }

        $winner = SpinwheelItem::find($winnerId);
        $winner->update(['prize' => $this->currentPrize]);

        Cache::forever('spin_duration', $this->spinDuration);
        Cache::put('spin_trigger', [
            'winner_id' => $winnerId,
            'duration' => $this->spinDuration,
            'prize' => $this->currentPrize, 
            'time' => microtime(true)
        ], 10);

        $this->dispatch('swal:success', ['title' => 'Berputar!', 'text' => 'Lihat layar utama.', 'icon' => 'success']);
    }

    public function resetData()
    {
        SpinwheelItem::where('is_winner', false)->delete();
        $this->dispatch('swal:success', ['title' => 'Dihapus', 'text' => 'Data antrean peserta dihapus. Daftar pemenang tetap aman!', 'icon' => 'success']);
    }

    public function with(): array
    {
        return [
            'totalPeserta' => SpinwheelItem::where('is_winner', false)->count(),
            'totalPemenang' => SpinwheelItem::where('is_winner', true)->count(),
            'winnersList' => SpinwheelItem::where('is_winner', true)->orderBy('won_at', 'desc')->get(),
        ];
    }
};
?>

<div class="p-8 bg-gray-50 min-h-screen">
    <!-- SCRIPT ALPINE.JS CDN TELAH DIHAPUS DARI SINI KARENA LIVEWIRE 3 SUDAH MEMBAWANYA OTOMATIS -->
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.addEventListener('swal:success', event => {
            const data = event.detail[0] ? event.detail[0] : event.detail;
            Swal.fire({ title: data.title, text: data.text, icon: data.icon, timer: 3000 });
        });
        window.addEventListener('swal:error', event => {
            const data = event.detail[0] ? event.detail[0] : event.detail;
            Swal.fire({ title: data.title, text: data.text, icon: data.icon });
        });
    </script>

    <div class="max-w-4xl mx-auto space-y-6">
        <h1 class="text-3xl font-black text-gray-800">Admin Spinwheel Controller</h1>

        <div class="bg-white p-6 rounded-2xl shadow-lg border-2 border-pink-100">
            <label class="block font-bold text-pink-600 mb-2">Ubah Judul Acara (Otomatis Berubah di Layar)</label>
            <input type="text" wire:model.live.debounce.500ms="eventTitle" 
                   class="border-2 border-gray-200 p-4 rounded-xl w-full text-2xl font-black text-center text-indigo-700 uppercase tracking-wider focus:border-pink-500 focus:ring-0 transition shadow-inner" 
                   placeholder="Contoh: DOORPRIZE AGUSTUSAN">
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-lg border border-indigo-100">
            <div class="flex flex-col md:flex-row gap-4 items-end justify-between">
                <div class="flex-1 flex gap-4 w-full">
                    <div class="w-1/3">
                        <label class="block font-bold text-gray-700 mb-2">Durasi (Detik)</label>
                        <input type="number" wire:model="spinDuration" class="border p-3 rounded-lg w-full font-bold text-xl text-center" min="3" max="20">
                    </div>
                    <div class="w-2/3">
                        <label class="block font-bold text-gray-700 mb-2">Hadiah Putaran Ini (Opsional)</label>
                        <input type="text" wire:model="currentPrize" class="border p-3 rounded-lg w-full text-xl" placeholder="Contoh: TV LED 32 Inch">
                    </div>
                </div>
                
                <button wire:click="triggerSpin" class="bg-gradient-to-r from-pink-500 to-indigo-600 hover:from-pink-600 hover:to-indigo-700 text-white font-black text-2xl px-10 py-5 rounded-2xl shadow-xl transform transition hover:scale-105 active:scale-95 w-full md:w-auto">
                    🎰 PUTAR RODA!
                </button>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
            <h2 class="text-xl font-bold mb-4">Input Daftar Nama</h2>
            
            <p wire:poll.2s class="text-gray-500 mb-2 text-sm">
                Peserta saat ini: <span class="font-bold text-indigo-600">{{ $totalPeserta }}</span> | 
                Pemenang: <span class="font-bold text-pink-600">{{ $totalPemenang }}</span>
            </p>
            <textarea wire:model.live.debounce.1000ms="bulkNames" rows="6" class="w-full border border-gray-300 rounded-xl p-4 focus:ring-indigo-500 font-mono" placeholder="Budi&#10;Andi&#10;Siti..."></textarea>
            
            <!-- x-data memastikan ini dibaca oleh Alpine bawaan Livewire -->
            <div class="flex justify-between mt-4" x-data>
                <button type="button" @click="
                    Swal.fire({
                        title: 'Yakin hapus data peserta?',
                        text: 'Hanya nama peserta yang BELUM MENANG yang akan dihapus. Daftar pemenang Anda akan tetap aman!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#9ca3af',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $wire.resetData();
                        }
                    })
                " class="text-red-500 font-bold px-4 py-2 hover:bg-red-50 rounded-lg transition border border-transparent hover:border-red-200">Hapus Data Peserta</button>
                
                <button wire:click="saveNames" class="bg-indigo-600 text-white font-bold px-6 py-3 rounded-xl hover:bg-indigo-700 shadow-md transition">Simpan Nama</button>
            </div>
        </div>

        <div wire:poll.2s class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
            <h2 class="text-xl font-bold mb-4 text-pink-600">🏆 Daftar Pemenang & Hadiah</h2>
            <table class="w-full text-left border-collapse mt-4">
                <tr class="bg-gray-100"><th class="p-3">Waktu Menang</th><th class="p-3">Nama Pemenang</th><th class="p-3">Hadiah Didapatkan</th></tr>
                @forelse($winnersList as $w)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 text-sm text-gray-500">{{ \Carbon\Carbon::parse($w->won_at)->timezone('Asia/Jakarta')->format('H:i') }} WIB</td>
                    <td class="p-3 font-bold">{{ $w->name }}</td>
                    <td class="p-3 text-indigo-600 font-bold">{{ $w->prize ?: '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="p-3 text-center text-gray-400 italic">Belum ada pemenang.</td></tr>
                @endforelse
            </table>
        </div>
    </div>
</div>