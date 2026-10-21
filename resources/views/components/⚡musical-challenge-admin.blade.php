<?php

use Livewire\Component;
use App\Models\ChallengeSong;
use App\Models\ChallengeParticipant;
use App\Models\ChallengeResult;
use App\Models\Participant;
use App\Models\Vote;
use App\Models\Score;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    // Konfigurasi & Pengaturan
    public $eventTitle = 'KORPRI MUSICAL CHALLENGE 2026';
    public $spinDuration = 5;

    // Sesi Aktif
    public $sessionActive = false;
    public $activeParticipantId = null;
    public $participantName = '';

    // Alur Game:
    // round: 1 atau 2
    // step: 'idle', 'spin_genre', 'genre_chosen', 'spin_song', 'decision', 'completed'
    public $round = 1; 
    public $step = 'idle';

    // Hasil Putaran Saat Ini
    public $currentGenre = null;
    public $currentSong = null;
    public $currentSongId = null;

    // Riwayat Putaran 1 (jika ditolak)
    public $round1Genre = null;
    public $round1Song = null;
    public $round1SongId = null;

    // Form Tambah & Edit Peserta
    public $newParticipantName = '';
    public $editingParticipantId = null;
    public $editingParticipantName = '';

    // Form Tambah Lagu Baru
    public $newSongGenre = 'POP 1';
    public $newSongTitle = '';

    public function mount()
    {
        if (!session('is_admin')) {
            return redirect('/admin/login');
        }

        $this->eventTitle = Cache::get('challenge_event_title', 'KORPRI MUSICAL CHALLENGE 2026');
        $this->spinDuration = Cache::get('challenge_spin_duration', 5);

        // Pastikan tabel voting tersinkron dengan peserta challenge
        if (Participant::count() === 0 || Participant::count() !== ChallengeParticipant::count()) {
            $this->syncToVotingList();
        }

        // Pulihkan state jika ada sesi yang sedang berlangsung
        $cachedState = Cache::get('challenge_state');
        if ($cachedState && ($cachedState['sessionActive'] ?? false)) {
            $this->sessionActive = true;
            $this->activeParticipantId = $cachedState['participant_id'] ?? null;
            $this->participantName = $cachedState['participant'] ?? '';
            $this->round = $cachedState['round'] ?? 1;
            $this->step = $cachedState['step'] ?? 'idle';
            $this->currentGenre = $cachedState['genre'] ?? null;
            $this->currentSong = $cachedState['song'] ?? null;
            $this->currentSongId = $cachedState['song_id'] ?? null;
            $this->round1Genre = $cachedState['round1_genre'] ?? null;
            $this->round1Song = $cachedState['round1_song'] ?? null;
            $this->round1SongId = $cachedState['round1_song_id'] ?? null;
        }
    }

    public function updatedEventTitle($val)
    {
        Cache::forever('challenge_event_title', $val);
        $this->broadcastState();
    }

    public function updatedSpinDuration($val)
    {
        Cache::forever('challenge_spin_duration', (int) $val);
    }

    // 1. Mulai Sesi Peserta Tertentu
    public function selectParticipant($participantId)
    {
        $participant = ChallengeParticipant::find($participantId);
        if (!$participant) return;

        $this->sessionActive = true;
        $this->activeParticipantId = $participant->id;
        $this->participantName = $participant->name;
        $this->round = 1;
        $this->step = 'spin_genre';
        $this->currentGenre = null;
        $this->currentSong = null;
        $this->currentSongId = null;
        $this->round1Genre = null;
        $this->round1Song = null;
        $this->round1SongId = null;

        $participant->update(['status' => 'in_progress']);

        $this->broadcastState();
        $this->dispatch('swal:success', [
            'title' => 'Sesi Dimulai!',
            'text' => 'Peserta aktif: ' . $participant->name . '. Layar display siap memutar Roda Genre (Putaran 1).',
            'icon' => 'success'
        ]);
    }

    // Mulai Sesi dengan Nama Bebas / Manual
    public function startCustomParticipant()
    {
        $name = trim($this->participantName);
        if (empty($name)) {
            $this->dispatch('swal:error', ['title' => 'Nama Belum Diisi', 'text' => 'Silakan pilih peserta atau ketik nama peserta terlebih dahulu.', 'icon' => 'warning']);
            return;
        }

        $p = ChallengeParticipant::firstOrCreate(
            ['name' => $name],
            ['order_number' => (ChallengeParticipant::max('order_number') ?? 0) + 1, 'status' => 'in_progress']
        );

        $this->selectParticipant($p->id);
    }

    // 2. Putar Genre (Putaran 1 atau Putaran 2)
    public function spinGenre()
    {
        if ($this->step !== 'spin_genre') return;

        $genres = ['POP 1', 'POP 2', 'ROCK', 'DANGDUT', 'NUSANTARA MIX', 'SKA/REGGAE'];

        $winnerGenre = $genres[array_rand($genres)];
        $this->currentGenre = $winnerGenre;
        $this->currentSong = null;
        $this->currentSongId = null;

        // Kirim trigger putaran GENRE ke display
        Cache::put('challenge_trigger', [
            'step' => 'genre',
            'round' => $this->round,
            'winner_id' => $winnerGenre,
            'winner_name' => $winnerGenre,
            'duration' => $this->spinDuration,
            'participant' => $this->participantName,
            'time' => microtime(true)
        ], 15);

        // Beralih ke step 'genre_chosen' (TIDAK langsung ke spin_song, agar roda display tetap menampilkan genre!)
        $this->step = 'genre_chosen';
        $this->broadcastState();

        $this->dispatch('swal:success', [
            'title' => 'Roda Genre Berputar!',
            'text' => 'Genre terpilih di layar: ' . $winnerGenre . '. Tunggu roda berhenti, lalu klik tombol "Lanjut ke Spin Lagu".',
            'icon' => 'info'
        ]);
    }

    // 3. Admin klik tombol lanjut ke Spin Lagu (Display berganti roda lagu)
    public function proceedToSongSpin()
    {
        if ($this->step !== 'genre_chosen' || !$this->currentGenre) return;

        $this->step = 'spin_song';
        $this->broadcastState();

        $this->dispatch('swal:success', [
            'title' => 'Roda Lagu Siap!',
            'text' => 'Layar display telah beralih ke daftar lagu ' . $this->currentGenre . '. Klik tombol "Putar Roda Lagu" untuk mengundi lagu.',
            'icon' => 'info'
        ]);
    }

    // 4. Putar Lagu (Putaran 1 atau Putaran 2)
    public function spinSong()
    {
        if ($this->step !== 'spin_song' || !$this->currentGenre) return;

        // Ambil lagu yang belum terpakai (is_used = false) di genre terpilih
        $query = ChallengeSong::where('genre', $this->currentGenre)
            ->where('is_active', true)
            ->where('is_used', false);

        $songs = $query->get();

        // Fallback jika seluruh lagu di genre ini sudah terpakai
        if ($songs->isEmpty()) {
            $songs = ChallengeSong::where('genre', $this->currentGenre)->where('is_active', true)->get();
        }

        if ($songs->isEmpty()) {
            $this->dispatch('swal:error', ['title' => 'Lagu Habis', 'text' => 'Tidak ada lagu yang tersedia di genre ini.', 'icon' => 'error']);
            return;
        }

        $winner = $songs->random();
        $this->currentSong = $winner->title;
        $this->currentSongId = $winner->id;

        // Kirim trigger putaran LAGU ke display
        Cache::put('challenge_trigger', [
            'step' => 'song',
            'round' => $this->round,
            'winner_id' => $winner->id,
            'winner_name' => $winner->title,
            'duration' => $this->spinDuration,
            'participant' => $this->participantName,
            'genre' => $this->currentGenre,
            'time' => microtime(true)
        ], 15);

        if ($this->round === 1) {
            // Pada Putaran 1: Paket Genre + Lagu selesai, sekarang masuk fase KEPUTUSAN (Terima atau Spin Ulang)
            $this->step = 'decision';
        } else {
            // Pada Putaran 2: Ini adalah kesempatan terakhir, hasil otomatis FINAL!
            $this->step = 'completed';
        }

        $this->broadcastState();

        $this->dispatch('swal:success', [
            'title' => 'Roda Lagu Berputar!',
            'text' => 'Lagu terpilih di layar: ' . $winner->title,
            'icon' => 'info'
        ]);
    }

    // 5. Tim Menerima Paket Putaran 1 (Langsung Kunci Final)
    public function acceptRound1()
    {
        $this->step = 'completed';
        $this->broadcastState();
        $this->dispatch('swal:success', [
            'title' => 'Paket Putaran 1 Diterima!',
            'text' => 'Genre: ' . $this->currentGenre . ' & Lagu: ' . $this->currentSong . ' telah disepakati.',
            'icon' => 'success'
        ]);
    }

    // 6. Tim Menolak Paket Putaran 1 -> SPIN ULANG DARI AWAL (PUTARAN 2 / FINAL)
    public function restartToRound2()
    {
        if ($this->round >= 2) return;

        // Simpan data putaran 1 sebagai riwayat penolakan
        $this->round1Genre = $this->currentGenre;
        $this->round1Song = $this->currentSong;
        $this->round1SongId = $this->currentSongId;

        // Reset hasil aktif dan masuk ke Putaran 2
        $this->round = 2;
        $this->step = 'spin_genre';
        $this->currentGenre = null;
        $this->currentSong = null;
        $this->currentSongId = null;

        $this->broadcastState();

        $this->dispatch('swal:success', [
            'title' => 'Spin Ulang dari Awal!',
            'text' => 'Kesempatan ke-2 dimulai. Layar kembali ke Roda Genre. Hasil putaran ke-2 ini akan menjadi final.',
            'icon' => 'warning'
        ]);
    }

    // 7. Simpan Hasil Akhir (Lagu resmi Dihilangkan dari list agar tidak bisa terpilih lagi)
    public function saveFinalResult()
    {
        if (!$this->participantName || !$this->currentGenre || !$this->currentSong) {
            $this->dispatch('swal:error', ['title' => 'Data Belum Lengkap', 'text' => 'Genre dan lagu belum terpilih.', 'icon' => 'warning']);
            return;
        }

        // HILANGKAN LAGU DARI LIST (Tandai sudah terpakai oleh peserta ini)
        if ($this->currentSongId) {
            $song = ChallengeSong::find($this->currentSongId);
            if ($song) {
                $song->update([
                    'is_used' => true,
                    'used_by' => $this->participantName
                ]);
            }
        } else {
            ChallengeSong::where('title', $this->currentSong)->update([
                'is_used' => true,
                'used_by' => $this->participantName
            ]);
        }

        // Update status di ChallengeParticipant
        if ($this->activeParticipantId) {
            $cp = ChallengeParticipant::find($this->activeParticipantId);
            if ($cp) {
                $cp->update([
                    'status' => 'completed',
                    'genre' => $this->currentGenre,
                    'song_title' => $this->currentSong,
                    'attempts_used' => $this->round,
                    'round1_genre' => $this->round1Genre,
                    'round1_song' => $this->round1Song,
                ]);
            }
        }

        // Simpan juga ke ChallengeResult untuk log historis
        ChallengeResult::create([
            'participant_id' => $this->activeParticipantId,
            'participant_name' => $this->participantName,
            'genre' => $this->currentGenre,
            'genre_attempt' => $this->round,
            'genre_rejected' => $this->round1Genre,
            'song_title' => $this->currentSong,
            'song_attempt' => $this->round,
            'song_rejected' => $this->round1Song,
        ]);

        $savedName = $this->participantName;
        $savedSong = $this->currentSong;

        // Sinkronkan hasil final ke list peserta voting
        $this->syncToVotingList();

        // Reset sesi aktif untuk persiapan peserta berikutnya
        $this->resetSessionInternal();

        $this->dispatch('swal:success', [
            'title' => 'Hasil Tersimpan & Terkunci!',
            'text' => $savedName . ' resmi mendapatkan lagu "' . $savedSong . '". Lagu ini telah dihilangkan dari daftar putaran berikutnya.',
            'icon' => 'success'
        ]);
    }

    // Reset Sesi
    public function resetSession()
    {
        if ($this->activeParticipantId) {
            $p = ChallengeParticipant::find($this->activeParticipantId);
            if ($p && $p->status === 'in_progress') {
                $p->update(['status' => 'pending']);
            }
        }
        $this->resetSessionInternal();
        $this->dispatch('swal:success', ['title' => 'Sesi Direset', 'text' => 'Layar kembali ke mode standby.', 'icon' => 'info']);
    }

    private function resetSessionInternal()
    {
        $this->sessionActive = false;
        $this->activeParticipantId = null;
        $this->participantName = '';
        $this->round = 1;
        $this->step = 'idle';
        $this->currentGenre = null;
        $this->currentSong = null;
        $this->currentSongId = null;
        $this->round1Genre = null;
        $this->round1Song = null;
        $this->round1SongId = null;

        $this->broadcastState();
    }

    // Sinkronisasi data ke Redis/Cache untuk dibaca Display secara Real-time
    public function broadcastState()
    {
        $items = [];
        // PENTING: Saat step 'spin_genre' ATAU 'genre_chosen', roda di layar display TETAP menampilkan 6 GENRE!
        if ($this->step === 'spin_genre' || $this->step === 'genre_chosen' || $this->step === 'idle') {
            $genres = ['POP 1', 'POP 2', 'ROCK', 'DANGDUT', 'NUSANTARA MIX', 'SKA/REGGAE'];
            $colors = ['#ec4899', '#f43f5e', '#ef4444', '#f59e0b', '#10b981', '#8b5cf6'];

            foreach ($genres as $i => $g) {
                $items[] = [
                    'id' => $g,
                    'name' => $g,
                    'color' => $colors[$i % count($colors)]
                ];
            }
        } elseif ($this->step === 'spin_song' || $this->step === 'decision' || $this->step === 'completed') {
            // Roda hanya berganti ke lagu saat sudah masuk fase 'spin_song'
            if ($this->currentGenre) {
                $query = ChallengeSong::where('genre', $this->currentGenre)
                    ->where('is_active', true)
                    ->where('is_used', false);

                $songs = $query->get();
                if ($songs->isEmpty()) {
                    $songs = ChallengeSong::where('genre', $this->currentGenre)->where('is_active', true)->get();
                }

                $palette = ['#f43f5e', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#6366f1', '#14b8a6', '#f97316'];
                foreach ($songs as $i => $s) {
                    $items[] = [
                        'id' => $s->id,
                        'name' => $s->title,
                        'color' => $palette[$i % count($palette)]
                    ];
                }
            }
        }

        $state = [
            'sessionActive' => $this->sessionActive,
            'participant_id' => $this->activeParticipantId,
            'participant' => $this->participantName,
            'round' => $this->round,
            'step' => $this->step,
            'genre' => $this->currentGenre,
            'song' => $this->currentSong,
            'song_id' => $this->currentSongId,
            'round1_genre' => $this->round1Genre,
            'round1_song' => $this->round1Song,
            'items' => $items,
            'title' => $this->eventTitle,
            'updated_at' => microtime(true)
        ];

        Cache::forever('challenge_state', $state);
    }

    // Sinkronkan seluruh peserta challenge ke tabel Participant (Voting Penonton & Dewan Juri)
    public function syncToVotingList($showSwal = false)
    {
        $cps = ChallengeParticipant::orderBy('order_number')->get();
        $cpNames = $cps->pluck('name')->toArray();

        DB::transaction(function () use ($cps, $cpNames) {
            // Hapus peserta voting lama jika belum ada transaksi vote & score aktif
            if (Vote::count() === 0 && Score::count() === 0) {
                Participant::whereNotIn('name', $cpNames)->delete();
            }

            // Hindari tabrakan unique constraint kolom order_number di MySQL
            Participant::query()->update(['order_number' => DB::raw('id + 10000')]);

            foreach ($cps as $idx => $cp) {
                $order = $idx + 1;
                Participant::updateOrCreate(
                    ['name' => $cp->name],
                    [
                        'order_number' => $order,
                        'song_title' => $cp->song_title ?: '-',
                        'status' => $cp->status === 'completed' ? 'finished' : ($order === 1 ? 'performing' : 'waiting'),
                    ]
                );
            }
        });

        if ($showSwal) {
            $this->dispatch('swal:success', [
                'title' => 'Tersinkronisasi!',
                'text' => count($cps) . ' peserta Musical Challenge berhasil disinkronkan ke daftar voting penonton & sistem penilaian.',
                'icon' => 'success'
            ]);
        }
    }

    // Rapikan urutan peserta agar selalu 1, 2, 3.. N tanpa celah
    private function resequenceParticipants()
    {
        $all = ChallengeParticipant::orderBy('order_number')->orderBy('id')->get();
        foreach ($all as $idx => $item) {
            $order = $idx + 1;
            if ($item->order_number !== $order) {
                $item->update(['order_number' => $order]);
            }
        }
    }

    // Tambah Peserta Baru ke Daftar
    public function addParticipant()
    {
        $name = trim($this->newParticipantName);
        if (empty($name)) return;

        $maxOrder = ChallengeParticipant::max('order_number') ?? 0;
        $newOrder = $maxOrder + 1;

        ChallengeParticipant::create([
            'name' => $name,
            'order_number' => $newOrder,
            'status' => 'pending'
        ]);

        $this->newParticipantName = '';
        $this->resequenceParticipants();
        $this->syncToVotingList();

        $this->dispatch('swal:success', [
            'title' => 'Peserta Ditambahkan',
            'text' => $name . ' berhasil dimasukkan ke urutan #' . $newOrder . ' dan otomatis terdaftar di list voting.',
            'icon' => 'success'
        ]);
    }

    // Geser Peserta Naik 1 Tingkat
    public function moveUp($id)
    {
        $current = ChallengeParticipant::find($id);
        if (!$current) return;

        $prev = ChallengeParticipant::where('order_number', '<', $current->order_number)
                    ->orderByDesc('order_number')
                    ->first();

        if ($prev) {
            $prevOrder = $prev->order_number;
            $currOrder = $current->order_number;

            $prev->update(['order_number' => $currOrder]);
            $current->update(['order_number' => $prevOrder]);

            $this->resequenceParticipants();
            $this->syncToVotingList();
        }
    }

    // Geser Peserta Turun 1 Tingkat
    public function moveDown($id)
    {
        $current = ChallengeParticipant::find($id);
        if (!$current) return;

        $next = ChallengeParticipant::where('order_number', '>', $current->order_number)
                    ->orderBy('order_number')
                    ->first();

        if ($next) {
            $nextOrder = $next->order_number;
            $currOrder = $current->order_number;

            $next->update(['order_number' => $currOrder]);
            $current->update(['order_number' => $nextOrder]);

            $this->resequenceParticipants();
            $this->syncToVotingList();
        }
    }

    // Ubah Nomor Urut Secara Langsung (misal dari dropdown #1..#N)
    public function changeOrder($id, $newOrder)
    {
        $newOrder = (int) $newOrder;
        $current = ChallengeParticipant::find($id);
        if (!$current || $current->order_number == $newOrder) return;

        $all = ChallengeParticipant::where('id', '!=', $id)->orderBy('order_number')->get();
        
        $ordered = collect();
        $placed = false;

        foreach ($all as $index => $item) {
            if (($index + 1) == $newOrder) {
                $ordered->push($current);
                $placed = true;
            }
            $ordered->push($item);
        }

        if (!$placed) {
            $ordered->push($current);
        }

        foreach ($ordered->values() as $idx => $item) {
            ChallengeParticipant::where('id', $item->id)->update(['order_number' => $idx + 1]);
        }

        $this->resequenceParticipants();
        $this->syncToVotingList();

        $this->dispatch('swal:success', [
            'title' => 'Urutan Diperbarui',
            'text' => "{$current->name} sekarang berada di urutan #{$newOrder}",
            'icon' => 'success'
        ]);
    }

    // Reorder massal dari Drag and Drop
    public function updateOrderList($orderedIds)
    {
        if (empty($orderedIds) || !is_array($orderedIds)) return;

        DB::transaction(function () use ($orderedIds) {
            // Gunakan offset sementara untuk menghindari error duplicate key constraint MySQL
            foreach ($orderedIds as $idx => $id) {
                ChallengeParticipant::where('id', $id)->update(['order_number' => $idx + 10000]);
            }

            foreach ($orderedIds as $idx => $id) {
                ChallengeParticipant::where('id', $id)->update(['order_number' => $idx + 1]);
            }
        });

        $this->resequenceParticipants();
        $this->syncToVotingList();

        $this->dispatch('swal:toast', [
            'title' => 'Urutan Peserta Disimpan!',
            'icon' => 'success'
        ]);
    }

    // Mulai Edit Nama Peserta
    public function startEditParticipant($id)
    {
        $p = ChallengeParticipant::find($id);
        if ($p) {
            $this->editingParticipantId = $p->id;
            $this->editingParticipantName = $p->name;
        }
    }

    // Batal Edit Nama
    public function cancelEditParticipant()
    {
        $this->editingParticipantId = null;
        $this->editingParticipantName = '';
    }

    // Simpan Perubahan Nama Peserta
    public function saveEditParticipant()
    {
        $name = trim($this->editingParticipantName);
        if (empty($name)) return;

        $p = ChallengeParticipant::find($this->editingParticipantId);
        if ($p) {
            $oldName = $p->name;
            $p->update(['name' => $name]);

            // Jika lagu terpakai oleh nama lama, update nama pemegang lagu
            ChallengeSong::where('used_by', $oldName)->update(['used_by' => $name]);

            $this->syncToVotingList();
            $this->editingParticipantId = null;
            $this->editingParticipantName = '';

            $this->dispatch('swal:success', [
                'title' => 'Nama Diperbarui',
                'text' => "Nama peserta berhasil diubah menjadi {$name}",
                'icon' => 'success'
            ]);
        }
    }

    // Reset Peserta Tertentu
    public function resetParticipantResult($id)
    {
        $p = ChallengeParticipant::find($id);
        if ($p) {
            if ($p->song_title) {
                ChallengeSong::where('title', $p->song_title)->update(['is_used' => false, 'used_by' => null]);
            }
            $p->update([
                'status' => 'pending',
                'genre' => null,
                'song_title' => null,
                'attempts_used' => 1,
                'round1_genre' => null,
                'round1_song' => null
            ]);
            $this->syncToVotingList();
            $this->broadcastState();
            $this->dispatch('swal:success', ['title' => 'Direset', 'text' => 'Status peserta ' . $p->name . ' dikembalikan ke Belum Undi, lagu dibuka kembali.', 'icon' => 'success']);
        }
    }

    // Hapus Peserta
    public function deleteParticipant($id)
    {
        $p = ChallengeParticipant::find($id);
        if ($p) {
            $name = $p->name;
            if ($p->song_title) {
                ChallengeSong::where('title', $p->song_title)->update(['is_used' => false, 'used_by' => null]);
            }
            $p->delete();
            $this->resequenceParticipants();
            $this->syncToVotingList();
            $this->dispatch('swal:success', ['title' => 'Dihapus', 'text' => "Peserta {$name} berhasil dihapus dari daftar.", 'icon' => 'success']);
        }
    }

    // Reset Semua Lagu Menjadi Tersedia
    public function resetAllSongsUsage()
    {
        ChallengeSong::query()->update(['is_used' => false, 'used_by' => null]);
        $this->broadcastState();
        $this->dispatch('swal:success', ['title' => 'Reset Lagu Selesai', 'text' => 'Semua master lagu kini tersedia kembali (0 terpakai).', 'icon' => 'success']);
    }

    // Tambah Lagu Baru
    public function addNewSong()
    {
        $title = trim($this->newSongTitle);
        if (empty($title)) return;

        ChallengeSong::create([
            'genre' => $this->newSongGenre,
            'title' => $title,
            'is_active' => true,
            'is_used' => false
        ]);

        $this->newSongTitle = '';
        $this->broadcastState();
        $this->dispatch('swal:success', ['title' => 'Lagu Ditambahkan', 'text' => 'Lagu baru berhasil ditambahkan ke genre ' . $this->newSongGenre, 'icon' => 'success']);
    }

    // Reset Total Seluruh Data Tantangan (Membutuhkan Otoritas Password)
    public function resetAllChallengeData($password)
    {
        if (trim($password) !== 'rkvetbz2') {
            $this->dispatch('swal:error', [
                'title' => 'Password Otoritas Salah!',
                'text' => 'Password yang Anda masukkan salah. Proses reset total dibatalkan untuk keamanan data.',
                'icon' => 'error'
            ]);
            return;
        }

        // 1. Reset seluruh ChallengeParticipant kembali ke pending & kosong
        ChallengeParticipant::query()->update([
            'status' => 'pending',
            'genre' => null,
            'song_title' => null,
            'attempts_used' => 1,
            'round1_genre' => null,
            'round1_song' => null,
        ]);

        // 2. Buka kembali seluruh lagu di ChallengeSong (is_used = false)
        ChallengeSong::query()->update([
            'is_used' => false,
            'used_by' => null,
        ]);

        // 3. Hapus seluruh catatan log riwayat di ChallengeResult
        ChallengeResult::truncate();

        // 4. Sinkronkan ke tabel Participant (voting penonton & scoring)
        $this->syncToVotingList();

        // 5. Reset sesi aktif dan cache panggung
        $this->resetSessionInternal();
        Cache::forget('challenge_state');
        Cache::forget('challenge_trigger');
        $this->broadcastState();

        $this->dispatch('swal:success', [
            'title' => 'Reset Total Berhasil!',
            'text' => 'Seluruh data tantangan, status peserta, dan lagu telah dikosongkan kembali ke kondisi awal (kosongan). Layar display siap diputar kembali.',
            'icon' => 'success'
        ]);
    }

    public function with(): array
    {
        return [
            'participantsList' => ChallengeParticipant::orderBy('order_number')->get(),
            'totalParticipants' => ChallengeParticipant::count(),
            'completedParticipants' => ChallengeParticipant::where('status', 'completed')->count(),
            'songsByGenre' => ChallengeSong::orderBy('genre')->orderBy('title')->get()->groupBy('genre'),
            'totalSongs' => ChallengeSong::count(),
            'usedSongsCount' => ChallengeSong::where('is_used', true)->count(),
        ];
    }
};
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <style>
        .sortable-ghost {
            opacity: 0.35 !important;
            background-color: #e0e7ff !important;
            border: 2px dashed #6366f1 !important;
            border-radius: 0.75rem !important;
        }
        .sortable-chosen {
            background-color: #ffffff !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15) !important;
        }
        .sortable-drag {
            cursor: grabbing !important;
            opacity: 0.95 !important;
        }
    </style>
    <script>
        window.addEventListener('swal:toast', (e) => {
            const data = e.detail[0] || e.detail;
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                icon: data.icon || 'success',
                title: data.title
            });
        });
        window.addEventListener('swal:success', (e) => {
            const data = e.detail[0] || e.detail;
            Swal.fire({
                title: data.title,
                text: data.text,
                icon: data.icon || 'success',
                confirmButtonColor: '#4f46e5'
            });
        });
        window.addEventListener('swal:error', (e) => {
            const data = e.detail[0] || e.detail;
            Swal.fire({
                title: data.title,
                text: data.text,
                icon: data.icon || 'error',
                confirmButtonColor: '#ef4444'
            });
        });

        function confirmResetAll(wire) {
            Swal.fire({
                title: '⚠️ Reset Total Semua Data?',
                html: `
                    <div class="text-slate-600 text-sm mb-3 text-left">
                        Tindakan ini akan <strong>mengatur ulang seluruh data tantangan menjadi kosongan lagi</strong>:
                        <ul class="list-disc list-inside mt-1.5 space-y-1 text-slate-500 text-xs">
                            <li>Status semua tim kembali menjadi <strong>Belum Undi</strong></li>
                            <li>Semua lagu di pool master kembali <strong>tersedia (0 terpakai)</strong></li>
                            <li>Riwayat putaran & tantangan panggung dibersihkan</li>
                        </ul>
                    </div>
                    <p class="text-xs text-rose-600 font-bold mb-1">Masukkan password otoritas admin untuk konfirmasi:</p>
                `,
                input: 'password',
                inputPlaceholder: 'Password otoritas...',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Kosongkan Semua!',
                cancelButtonText: 'Batal',
                preConfirm: (password) => {
                    if (!password) {
                        Swal.showValidationMessage('Password wajib diisi!');
                    }
                    return password;
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    wire.resetAllChallengeData(result.value);
                }
            });
        }
    </script>

    <!-- Header Panel -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center pb-6 border-b border-slate-200 gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="text-3xl">🎤</span>
                <h1 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">KORPRI Musical Challenge Controller</h1>
            </div>
            <p class="text-slate-500 text-xs md:text-sm mt-1">Alur: <strong>Spin Genre</strong> → Lihat Hasil Genre → <strong>Spin Lagu</strong> → Keputusan Tim (Terima / Spin Ulang) → <strong>Kunci & Hilangkan Lagu</strong></p>
        </div>
        
        <!-- Toolbar Navigasi Header Rapi & Sejajar -->
        <div class="flex flex-wrap items-center gap-2 sm:gap-2.5 shrink-0">
            <!-- Layar Panggung (Tombol Utama) -->
            <a href="/display/challenge" target="_blank" class="inline-flex items-center gap-2 bg-gradient-to-r from-pink-500 via-rose-500 to-indigo-600 hover:from-pink-600 hover:to-indigo-700 text-white font-extrabold px-4 py-2 rounded-xl shadow-md transition transform hover:scale-105 text-xs sm:text-sm">
                <span>📺</span>
                <span>Layar Panggung</span>
                <span>→</span>
            </a>

            <!-- Rekap Lagu Peserta -->
            <a href="/display/challenge-results" target="_blank" class="inline-flex items-center gap-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold px-3.5 py-2 rounded-xl transition text-xs sm:text-sm">
                <span>📋</span>
                <span>Rekap Hasil</span>
            </a>

            <!-- Kembali ke Dashboard -->
            <a href="/admin/dashboard" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 font-bold px-3.5 py-2 rounded-xl transition text-xs sm:text-sm">
                <span>←</span>
                <span>Dashboard</span>
            </a>

            <!-- Tombol Reset Total (Otoritas Password) -->
            <button type="button" @click="confirmResetAll($wire)" class="inline-flex items-center gap-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-300 font-bold px-3.5 py-2 rounded-xl transition text-xs sm:text-sm cursor-pointer shadow-xs">
                <span>⚠️</span>
                <span>Reset Total</span>
            </button>
        </div>
    </div>

    <!-- Grid Kontrol Utama -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">

        <!-- Kolom Kiri: Manajemen Peserta & Lagu -->
        <div class="space-y-6">

            <!-- Card Daftar Peserta & Mulai Sesi -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                        <span>👥</span> Daftar Peserta ({{ $completedParticipants }}/{{ $totalParticipants }})
                    </h2>
                    <div class="flex items-center gap-1.5">
                        <button wire:click="syncToVotingList(true)" title="Sinkronkan daftar & lagu peserta ke list voting penonton" class="text-[11px] bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold px-2 py-1 rounded-lg border border-indigo-200 transition flex items-center gap-1 cursor-pointer">
                            <span>🔄</span> Sinkron
                        </button>
                        <span class="text-[11px] bg-slate-100 text-slate-600 font-bold px-2 py-1 rounded-lg border border-slate-200">
                            {{ $totalParticipants - $completedParticipants }} Belum Undi
                        </span>
                    </div>
                </div>

                <!-- Form Tambah Peserta Cepat -->
                <div class="flex gap-2 mb-3.5">
                    <input type="text" wire:model="newParticipantName" wire:keydown.enter="addParticipant" placeholder="Tambah nama peserta/tim..." class="flex-1 text-xs border-slate-300 rounded-xl px-3 py-2 border focus:ring-2 focus:ring-indigo-500">
                    <button wire:click="addParticipant" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition whitespace-nowrap shadow-sm cursor-pointer">
                        + Tambah
                    </button>
                </div>

                <!-- List Peserta & Kontrol Urutan (Mendukung Drag & Drop Sortable) -->
                <div x-data="{
                        init() {
                            if (typeof Sortable !== 'undefined') {
                                new Sortable(this.$el, {
                                    animation: 200,
                                    handle: '.drag-handle',
                                    draggable: '.sortable-item',
                                    ghostClass: 'sortable-ghost',
                                    chosenClass: 'sortable-chosen',
                                    dragClass: 'sortable-drag',
                                    onEnd: () => {
                                        let orderedIds = Array.from(this.$el.querySelectorAll('.sortable-item'))
                                            .map(el => el.getAttribute('data-id'))
                                            .filter(Boolean);
                                        $wire.updateOrderList(orderedIds);
                                    }
                                });
                            }
                        }
                    }" 
                    class="space-y-2.5 max-h-[480px] overflow-y-auto pr-1">
                    @forelse($participantsList as $idx => $p)
                        @php
                            $isFirst = ($idx === 0);
                            $isLast = ($idx === count($participantsList) - 1);
                        @endphp
                        <div wire:key="participant-card-{{ $p->id }}" data-id="{{ $p->id }}" class="sortable-item p-3 rounded-xl border transition flex flex-col gap-2 {{ $activeParticipantId === $p->id ? 'bg-indigo-50/80 border-indigo-400 shadow-sm ring-1 ring-indigo-400/30' : ($p->status === 'completed' ? 'bg-slate-50/80 border-slate-200' : 'bg-white border-slate-200 hover:border-indigo-300') }}">
                            
                            <!-- Baris Atas: Drag Handle, Stepper Urutan, Nama Peserta, Status Badge & Opsi Hapus -->
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 flex-1 min-w-0">
                                    <!-- Drag Handle Gripper -->
                                    <button type="button" class="drag-handle cursor-grab active:cursor-grabbing text-slate-300 hover:text-indigo-600 p-1 -ml-1 rounded transition select-none flex items-center shrink-0" title="Klik dan tahan (drag) untuk mengatur urutan peserta">
                                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M7 4a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm6-12a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                        </svg>
                                    </button>

                                    <!-- Stepper Pengubah Urutan Terpadu [ ▲ | #X | ▼ ] -->
                                    <div class="inline-flex items-center bg-slate-100 border border-slate-200 rounded-lg p-0.5 shrink-0 shadow-xs">
                                        <button wire:click="moveUp({{ $p->id }})" 
                                                {{ $isFirst ? 'disabled' : '' }} 
                                                title="Geser Naik" 
                                                class="w-5 h-5 flex items-center justify-center text-[10px] font-black text-slate-500 hover:text-indigo-600 hover:bg-white rounded disabled:opacity-20 transition cursor-pointer">
                                            ▲
                                        </button>
                                        <select wire:change="changeOrder({{ $p->id }}, $event.target.value)" 
                                                class="bg-transparent border-0 text-[11px] font-black text-slate-700 px-1 py-0 cursor-pointer focus:ring-0 appearance-none text-center"
                                                title="Klik untuk memilih langsung nomor urut">
                                            @for($num = 1; $num <= $totalParticipants; $num++)
                                                <option value="{{ $num }}" {{ $p->order_number == $num ? 'selected' : '' }}>
                                                    #{{ $num }}
                                                </option>
                                            @endfor
                                        </select>
                                        <button wire:click="moveDown({{ $p->id }})" 
                                                {{ $isLast ? 'disabled' : '' }} 
                                                title="Geser Turun" 
                                                class="w-5 h-5 flex items-center justify-center text-[10px] font-black text-slate-500 hover:text-indigo-600 hover:bg-white rounded disabled:opacity-20 transition cursor-pointer">
                                            ▼
                                        </button>
                                    </div>

                                    <!-- Nama Peserta / Mode Edit -->
                                    @if($editingParticipantId === $p->id)
                                        <div class="flex items-center gap-1 flex-1">
                                            <input type="text" wire:model="editingParticipantName" wire:keydown.enter="saveEditParticipant" class="text-xs border-indigo-400 rounded px-2 py-0.5 flex-1 border focus:ring-1 focus:ring-indigo-500">
                                            <button wire:click="saveEditParticipant" title="Simpan" class="bg-emerald-600 text-white text-[11px] px-2 py-0.5 rounded font-bold hover:bg-emerald-700">✓</button>
                                            <button wire:click="cancelEditParticipant" title="Batal" class="bg-slate-300 text-slate-700 text-[11px] px-2 py-0.5 rounded font-bold hover:bg-slate-400">✕</button>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-1 truncate flex-1 min-w-0">
                                            <span class="font-extrabold text-xs text-slate-800 truncate {{ $p->status === 'completed' ? 'text-slate-500' : '' }}" title="{{ $p->name }}">
                                                {{ $p->name }}
                                            </span>
                                            <button wire:click="startEditParticipant({{ $p->id }})" title="Edit nama peserta" class="text-slate-400 hover:text-indigo-600 text-[11px] p-0.5 shrink-0 opacity-60 hover:opacity-100 transition cursor-pointer">
                                                ✏️
                                            </button>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Status Badge & Tombol Hapus Cepat -->
                                <div class="flex items-center gap-1.5 shrink-0">
                                    @if($p->status === 'completed')
                                        <span class="text-[10px] font-extrabold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full whitespace-nowrap">
                                            ✓ Selesai
                                        </span>
                                    @elseif($activeParticipantId === $p->id)
                                        <span class="text-[10px] font-extrabold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full animate-pulse whitespace-nowrap">
                                            Sedang Undi
                                        </span>
                                    @else
                                        <span class="text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full whitespace-nowrap">
                                            Belum
                                        </span>
                                        <button wire:click="deleteParticipant({{ $p->id }})" wire:confirm="Hapus peserta ini dari daftar?" title="Hapus peserta" class="text-slate-300 hover:text-rose-600 text-xs p-0.5 rounded hover:bg-rose-50 transition cursor-pointer">
                                            🗑️
                                        </button>
                                    @endif
                                </div>
                            </div>

                            @if($p->status === 'completed')
                                <div class="bg-white/90 p-2.5 rounded-xl border border-slate-200/80 text-[11px] text-slate-600 space-y-0.5">
                                    <div class="flex justify-between items-center">
                                        <span>Genre: <strong class="text-amber-700">{{ $p->genre }}</strong></span>
                                        <span class="text-[10px] text-slate-400">{{ $p->attempts_used === 2 ? 'Spin Ulang' : 'Langsung' }}</span>
                                    </div>
                                    <div class="truncate text-slate-800 font-bold">
                                        🎵 <span class="text-pink-700">{{ $p->song_title }}</span>
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 text-[11px] pt-0.5">
                                    <button wire:click="resetParticipantResult({{ $p->id }})" wire:confirm="Reset status peserta ini dan buka kembali lagunya?" class="text-amber-600 hover:text-amber-700 font-semibold hover:underline cursor-pointer">
                                        Undi Ulang
                                    </button>
                                    <span class="text-slate-300">•</span>
                                    <button wire:click="deleteParticipant({{ $p->id }})" wire:confirm="Hapus peserta ini?" class="text-rose-500 hover:text-rose-600 font-semibold hover:underline cursor-pointer">
                                        Hapus
                                    </button>
                                </div>
                            @else
                                <!-- Tombol Mulai Undian: Full Width, Rapi & Elegan -->
                                <button wire:click="selectParticipant({{ $p->id }})" class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-sm hover:shadow transition flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>🚀</span> Mulai Undian Tim Ini
                                </button>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-6 text-slate-400 text-xs">
                            Belum ada peserta terdaftar.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Card Status Lagu & Pengaturan -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <span>🎵</span> Pool Master Lagu
                    </h2>
                    <span class="text-xs bg-pink-50 text-pink-700 font-bold px-2 py-0.5 rounded-full">
                        {{ $totalSongs - $usedSongsCount }} Tersisa ({{ $usedSongsCount }} Dihilangkan)
                    </span>
                </div>
                <p class="text-xs text-slate-500">
                    Lagu yang sudah final didapatkan peserta otomatis dihilangkan dari roda agar tidak terpilih oleh peserta lain.
                </p>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Durasi Putar (dtk)</label>
                        <input type="number" min="3" max="15" wire:model.live="spinDuration" class="w-full text-xs border-slate-300 rounded-lg p-2 border">
                    </div>
                    <div class="flex items-end">
                        <button wire:click="resetAllSongsUsage" wire:confirm="Buka kembali semua lagu menjadi tersedia (0 terpakai)?" class="w-full py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs rounded-lg border border-amber-200 transition">
                            ♻️ Reset Pool Lagu
                        </button>
                    </div>
                </div>
            </div>

            <!-- Card Reset Total Sistem (Dengan Otoritas Password) -->
            <div class="bg-rose-50/80 border border-rose-200 rounded-2xl p-5 space-y-3 shadow-sm">
                <div class="flex items-start gap-3">
                    <span class="text-2xl p-2 bg-rose-100 rounded-xl">🔒</span>
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-wider text-rose-800">Reset Total Sistem</h3>
                        <p class="text-[11px] text-rose-600 mt-0.5">Kembalikan semua peserta & lagu menjadi kosongan lagi (Membutuhkan Password Otoritas).</p>
                    </div>
                </div>
                <button type="button" @click="confirmResetAll($wire)" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center justify-center gap-2 cursor-pointer">
                    <span>⚠️</span> Kosongkan Ulang Semua Data
                </button>
            </div>

        </div>

        <!-- Kolom Tengah & Kanan: Kontrol Flow Game Interaktif -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Banner Sesi Panggung Aktif -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl shadow-xl p-6 border border-indigo-500/30">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs uppercase font-extrabold tracking-widest text-indigo-400 bg-indigo-950/80 border border-indigo-500/50 px-3 py-1 rounded-full">
                                Peserta di Layar Panggung
                            </span>
                            @if($sessionActive)
                                <span class="text-xs uppercase font-extrabold px-3 py-1 rounded-full {{ $round === 1 ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40' }}">
                                    Putaran {{ $round }} / 2 ({{ $round === 1 ? 'Kesempatan 1' : 'Kesempatan Terakhir' }})
                                </span>
                            @endif
                        </div>
                        <h2 class="text-3xl md:text-4xl font-black mt-2 text-white">
                            {{ $sessionActive ? $participantName : 'Belum Ada Sesi Peserta Aktif' }}
                        </h2>
                    </div>

                    @if($sessionActive)
                        <button wire:click="resetSession" wire:confirm="Batalkan sesi peserta ini?" class="text-xs text-slate-400 hover:text-rose-400 border border-slate-700 hover:border-rose-500 px-3 py-1.5 rounded-lg transition">
                            ✕ Batalkan Sesi
                        </button>
                    @endif
                </div>

                <!-- Status Progress Putaran Aktif -->
                @if($sessionActive)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6 pt-6 border-t border-slate-800">
                        <!-- Kotak Genre -->
                        <div class="bg-slate-800/80 rounded-xl p-4 border {{ $currentGenre ? 'border-amber-400/60 shadow-[0_0_15px_rgba(251,191,36,0.15)]' : 'border-slate-700' }}">
                            <div class="flex justify-between items-center text-xs text-slate-400 mb-1">
                                <span>GENRE MUSIK (PUTARAN {{ $round }})</span>
                                @if($currentGenre)
                                    <span class="text-emerald-400 font-bold">✓ Terpilih: {{ $currentGenre }}</span>
                                @else
                                    <span class="text-amber-400 font-bold animate-pulse">Menunggu Spin...</span>
                                @endif
                            </div>
                            <div class="text-2xl font-black text-amber-300">
                                {{ $currentGenre ?: '—' }}
                            </div>
                            @if($round1Genre && $round === 2)
                                <div class="text-[11px] text-amber-400 mt-1">Pilihan Putaran 1: {{ $round1Genre }}</div>
                            @endif
                        </div>

                        <!-- Kotak Lagu -->
                        <div class="bg-slate-800/80 rounded-xl p-4 border {{ $currentSong ? 'border-pink-400/60 shadow-[0_0_15px_rgba(244,63,94,0.15)]' : 'border-slate-700' }}">
                            <div class="flex justify-between items-center text-xs text-slate-400 mb-1">
                                <span>LAGU TANTANGAN (PUTARAN {{ $round }})</span>
                                @if($currentSong)
                                    <span class="text-emerald-400 font-bold">✓ Terpilih</span>
                                @elseif($step === 'spin_song')
                                    <span class="text-pink-400 font-bold animate-pulse">Siap Diundi!</span>
                                @else
                                    <span class="text-slate-500 font-bold">Menunggu Genre...</span>
                                @endif
                            </div>
                            <div class="text-xl font-black text-pink-300 truncate">
                                {{ $currentSong ?: '—' }}
                            </div>
                            @if($round1Song && $round === 2)
                                <div class="text-[11px] text-amber-400 mt-1 truncate">Pilihan Putaran 1: {{ $round1Song }}</div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- KONTROL STEP-BY-STEP: GENRE DULU -> HASIL GENRE -> SPIN LAGU -> HASIL LAGU -->
            @if($sessionActive)

                <!-- STEP 1: PUTAR GENRE -->
                @if($step === 'spin_genre')
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full">
                                    Tahap 1: Pemilihan Genre (Putaran {{ $round }})
                                </span>
                                <h3 class="text-xl font-black text-slate-900 mt-1">Putar Roda Genre Musik</h3>
                            </div>
                            <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-lg">
                                6 Genre: POP 1, POP 2, ROCK, DANGDUT, NUSANTARA MIX, SKA/REGGAE
                            </span>
                        </div>

                        <p class="text-slate-600 text-sm">
                            Layar display panggung saat ini menampilkan <strong>6 Genre Musik</strong>. 
                            Klik tombol di bawah untuk memutar roda genre terlebih dahulu.
                            @if($round === 2)
                                <br><strong class="text-amber-700">* Kesempatan ke-2: Seluruh genre dan lagu tetap tersedia lengkap di roda putar.</strong>
                            @endif
                        </p>

                        <button wire:click="spinGenre" class="w-full py-4 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-black text-lg rounded-xl shadow-lg transition transform hover:-translate-y-0.5 flex items-center justify-center gap-3">
                            <span>🎲</span> PUTAR RODA GENRE (PUTARAN {{ $round }})
                        </button>
                    </div>
                @endif

                <!-- STEP 1.5: GENRE TELAH TERPILIH, TUNGGU ADMIN LANJUTKAN KE SPIN LAGU -->
                @if($step === 'genre_chosen')
                    <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-800 bg-amber-200 px-3 py-1 rounded-full">
                                Hasil Spin Genre (Putaran {{ $round }})
                            </span>
                            <span class="text-xs font-bold text-amber-700">Langkah Berikutnya: Undi Lagu</span>
                        </div>

                        <div class="bg-white rounded-xl p-4 border border-amber-200 text-center">
                            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">GENRE TERPILIH DI LAYAR</div>
                            <div class="text-4xl font-black text-amber-600 mt-1">{{ $currentGenre }}</div>
                        </div>

                        <p class="text-slate-600 text-sm">
                            Roda genre di layar telah berhenti pada <strong>{{ $currentGenre }}</strong>. 
                            Klik tombol di bawah untuk mengganti roda layar panggung ke daftar lagu <strong>{{ $currentGenre }}</strong> dan memulai undian lagu.
                        </p>

                        <button wire:click="proceedToSongSpin" class="w-full py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-black text-lg rounded-xl shadow-lg transition transform hover:-translate-y-0.5 flex items-center justify-center gap-3">
                            <span>🎵</span> LANJUT KE SPIN LAGU (BUKA RODA LAGU {{ $currentGenre }})
                        </button>
                    </div>
                @endif

                <!-- STEP 2: SPIN LAGU DARI GENRE TERPILIH -->
                @if($step === 'spin_song')
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-pink-600 bg-pink-50 px-2.5 py-1 rounded-full">
                                    Tahap 2: Pemilihan Lagu (Putaran {{ $round }})
                                </span>
                                <h3 class="text-xl font-black text-slate-900 mt-1">Putar Roda Lagu (Genre: {{ $currentGenre }})</h3>
                            </div>
                            <span class="text-xs font-bold text-amber-700 bg-amber-100 px-3 py-1 rounded-lg">
                                Genre: {{ $currentGenre }}
                            </span>
                        </div>

                        <p class="text-slate-600 text-sm">
                            Layar display sekarang sudah memuat daftar lagu yang tersedia untuk genre <strong>{{ $currentGenre }}</strong>. 
                            Klik tombol di bawah untuk memutar roda dan menentukan lagu bagi <strong>{{ $participantName }}</strong>.
                        </p>

                        <button wire:click="spinSong" class="w-full py-4 bg-gradient-to-r from-pink-500 to-rose-600 hover:from-pink-600 hover:to-rose-700 text-white font-black text-lg rounded-xl shadow-lg transition transform hover:-translate-y-0.5 flex items-center justify-center gap-3">
                            <span>🎵</span> PUTAR RODA LAGU (PUTARAN {{ $round }})
                        </button>
                    </div>
                @endif

                <!-- STEP 3: KEPUTUSAN TIM DI AKHIR PUTARAN 1 (TERIMA ATAU SPIN ULANG DARI AWAL) -->
                @if($step === 'decision')
                    <div class="bg-amber-50/80 border-2 border-amber-300 rounded-2xl p-6 space-y-5">
                        <div class="text-center">
                            <span class="text-xs font-extrabold uppercase tracking-widest text-amber-800 bg-amber-200/80 px-3 py-1 rounded-full">
                                HASIL PUTARAN 1 SELESAI
                            </span>
                            <h3 class="text-2xl font-black text-slate-900 mt-3">Paket Tantangan Putaran 1</h3>
                            <p class="text-slate-600 text-sm mt-1">Tanyakan kepada tim: Apakah menerima paket ini, atau ingin menggunakan <strong>kesempatan ubah (spin ulang dari awal)</strong>?</p>
                        </div>

                        <div class="bg-white rounded-xl p-5 shadow-sm border border-amber-200 max-w-lg mx-auto text-center space-y-2">
                            <div class="text-xs font-semibold text-slate-400 uppercase">GENRE TERPILIH</div>
                            <div class="text-2xl font-black text-amber-900">{{ $currentGenre }}</div>
                            <div class="text-xs font-semibold text-slate-400 uppercase pt-2">LAGU TANTANGAN</div>
                            <div class="text-xl font-black text-pink-700">{{ $currentSong }}</div>
                        </div>

                        <!-- Dua Tombol Pilihan Alur Baru -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl mx-auto pt-2">
                            <!-- Tombol A: Terima Paket Ini -->
                            <button wire:click="acceptRound1" class="py-4 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl shadow-md transition flex flex-col items-center justify-center gap-1">
                                <span class="text-lg">✅ TERIMA PAKET INI</span>
                                <span class="text-xs text-emerald-100 font-normal">Kunci Genre & Lagu ini sebagai Final</span>
                            </button>

                            <!-- Tombol B: Tolak & Spin Ulang dari Awal -->
                            <button wire:click="restartToRound2" wire:confirm="Tim memilih untuk spin ulang? Kesempatan ke-2 akan dimulai dari pemilihan Genre lalu Lagu, dan hasilnya otomatis final!" class="py-4 px-4 bg-rose-600 hover:bg-rose-700 text-white font-black rounded-xl shadow-md transition flex flex-col items-center justify-center gap-1">
                                <span class="text-lg">🔄 SPIN ULANG DARI AWAL</span>
                                <span class="text-xs text-rose-100 font-normal">Gunakan Kesempatan ke-2 (Terakhir)</span>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- STEP 4: HASIL FINAL (TERKUNCI) & SIMPAN HILANGKAN LAGU -->
                @if($step === 'completed')
                    <div class="bg-emerald-50 border-2 border-emerald-400 rounded-2xl p-6 text-center space-y-5">
                        <div class="text-4xl">🎉</div>
                        <div>
                            <span class="text-xs font-extrabold uppercase tracking-widest text-emerald-800 bg-emerald-200 px-3 py-1 rounded-full">
                                {{ $round === 1 ? 'HASIL FINAL (PUTARAN 1 DITERIMA)' : 'HASIL FINAL (PUTARAN 2 - KESEMPATAN KE-2)' }}
                            </span>
                            <h3 class="text-3xl font-black text-emerald-950 mt-2">Tantangan Musik Terkunci!</h3>
                        </div>

                        <div class="bg-white rounded-xl p-5 shadow-sm max-w-lg mx-auto text-left space-y-3 border border-emerald-200">
                            <div class="flex justify-between text-sm py-1 border-b border-slate-100">
                                <span class="text-slate-500 font-semibold">Peserta:</span>
                                <span class="font-black text-slate-900">{{ $participantName }}</span>
                            </div>
                            <div class="flex justify-between text-sm py-1 border-b border-slate-100">
                                <span class="text-slate-500 font-semibold">Genre Final:</span>
                                <span class="font-black text-amber-700">{{ $currentGenre }}</span>
                            </div>
                            <div class="flex justify-between text-sm py-1">
                                <span class="text-slate-500 font-semibold">Lagu Final:</span>
                                <span class="font-black text-pink-700">{{ $currentSong }}</span>
                            </div>
                            @if($round === 2)
                                <div class="bg-rose-50 p-2.5 rounded-lg text-xs text-rose-700 border border-rose-200">
                                    Catatan: Peserta menggunakan kesempatan ke-2 setelah menolak (Genre: {{ $round1Genre }}, Lagu: {{ $round1Song }}).
                                </div>
                            @endif
                        </div>

                        <div class="pt-2 flex flex-col sm:flex-row gap-3 justify-center max-w-md mx-auto">
                            <button wire:click="saveFinalResult" class="w-full py-4 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl shadow-lg transition flex items-center justify-center gap-2 text-base">
                                <span>💾</span> SIMPAN & HILANGKAN LAGU DARI LIST
                            </button>
                        </div>
                        <p class="text-xs text-slate-500">
                            * Klik tombol di atas untuk merekap hasil ke database dan otomatis <strong>menghilangkan lagu "{{ $currentSong }}" dari daftar</strong> agar peserta berikutnya tidak dapat lagu ini lagi.
                        </p>
                    </div>
                @endif

            @else
                <!-- Saat Belum Ada Sesi Aktif -->
                <div class="bg-white rounded-2xl border-2 border-dashed border-slate-300 p-12 text-center">
                    <span class="text-5xl">🎯</span>
                    <h3 class="text-xl font-bold text-slate-700 mt-4">Belum Ada Sesi yang Dimulai</h3>
                    <p class="text-slate-500 text-sm max-w-md mx-auto mt-2">
                        Pilih peserta dari daftar di kolom kiri (misal: <strong>Sekretariat Wakil Presiden</strong>) lalu klik tombol 
                        <strong>"Mulai Undian Tim Ini"</strong>.
                    </p>
                </div>
            @endif

            <!-- DAFTAR MASTER 35 LAGU LOMBA (STATUS TERSEDIA / DIHILANGKAN) -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6" x-data="{ open: true }">
                <div class="flex justify-between items-center cursor-pointer" @click="open = !open">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <span>📋</span> Status 35 Lagu per Genre (KORPRI Musical Challenge)
                        </h2>
                        <p class="text-xs text-slate-500">Lagu yang dicoret adalah lagu yang sudah didapatkan oleh tim lain</p>
                    </div>
                    <span class="text-slate-400 text-sm" x-text="open ? '▲ Sembunyikan' : '▼ Tampilkan'"></span>
                </div>

                <div x-show="open" class="mt-6 pt-6 border-t border-slate-100 space-y-6">
                    
                    <!-- Form Tambah Lagu -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <span class="text-xs font-bold text-slate-700 uppercase">Tambah Lagu Cadangan</span>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mt-2">
                            <div>
                                <select wire:model="newSongGenre" class="w-full text-xs border-slate-300 rounded-lg p-2 border bg-white">
                                    <option value="POP 1">POP 1</option>
                                    <option value="POP 2">POP 2</option>
                                    <option value="ROCK">ROCK</option>
                                    <option value="DANGDUT">DANGDUT</option>
                                    <option value="NUSANTARA MIX">NUSANTARA MIX</option>
                                    <option value="SKA/REGGAE">SKA/REGGAE</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <input type="text" wire:model="newSongTitle" placeholder="Judul Lagu - Artis" class="w-full text-xs border-slate-300 rounded-lg p-2 border">
                            </div>
                            <div>
                                <button wire:click="addNewSong" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg transition">
                                    + Tambah Lagu
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Grid List Lagu per Genre -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($songsByGenre as $genreName => $songs)
                            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                                <div class="flex justify-between items-center mb-2 pb-2 border-b border-slate-200">
                                    <span class="font-black text-xs text-slate-800 uppercase tracking-wider">{{ $genreName }}</span>
                                    <span class="text-[11px] font-bold text-slate-500">
                                        {{ $songs->where('is_used', false)->count() }} Tersedia / {{ $songs->count() }} Total
                                    </span>
                                </div>
                                <ul class="space-y-1 text-xs">
                                    @foreach($songs as $s)
                                        <li class="flex justify-between items-center p-1 rounded {{ $s->is_used ? 'opacity-40 line-through text-slate-400 bg-slate-100' : 'text-slate-700 hover:bg-white' }}">
                                            <span class="truncate pr-2">• {{ $s->title }}</span>
                                            @if($s->is_used)
                                                <span class="text-[10px] bg-rose-100 text-rose-700 px-1.5 py-0.5 rounded font-bold whitespace-nowrap not-italic no-underline">
                                                    {{ $s->used_by ?: 'Terpakai' }}
                                                </span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
