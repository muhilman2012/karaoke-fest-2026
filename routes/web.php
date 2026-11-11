<?php
use Illuminate\Support\Facades\Route;

use App\Models\Participant;
use App\Models\Judge;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\AdminAuthController;

Route::get('/', function () {
    return view('welcome');
});

// 2. Autentikasi Admin
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])
    ->middleware('throttle:admin-login')
    ->name('admin.login.submit');

// 3. Dashboard Admin (Dilindungi Session Login)
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn () => view('admin'))->name('dashboard');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::get('/export-excel', [ExportController::class, 'download'])->name('export');
    Route::get('/votes', fn () => view('admin-votes'))->name('votes');
    Route::get('/spinwheel', fn () => view('spinwheel-admin-page'))->name('spinwheel');
    Route::get('/challenge', fn () => view('challenge-admin-page'))->name('challenge');
    Route::get('/supporter', fn () => view('admin-supporter'))->name('supporter');
});
// Redirect /admin lama langsung ke /admin/dashboard atau login
Route::get('/admin', function () {
    return redirect('/admin/dashboard');
});

// 5. Juri Routes
Route::get('/juri', function () { return view('juri-login'); });
Route::get('/juri/form', function () { return view('juri'); });

// Penilaian Best Supporter (terpisah dari penilaian karaoke)
Route::get('/juri-supporter', fn () => view('supporter-login'))->name('supporter.login');
Route::get('/juri-supporter/form', fn () => view('supporter-form'))->name('supporter.form');

// 6. Display Publik (Live Score & Leaderboard)
Route::get('/live', function () { return view('live'); });
Route::get('/leaderboard', function () { return view('leaderboard'); });
Route::get('/live-votes', fn () => view('live-votes'))->name('live-votes');

// 7. Voting Penonton
Route::get('/vote', function () { 
    return view('vote'); 
});

Route::get('/display/spinwheel', function () {
    return view('spinwheel-display-page');
});

// ROUTE BARU: Halaman Daftar Pemenang
Route::get('/display/winners', function () {
    return view('spinwheel-winners-page');
});

// Route untuk Live Leaderboard
Route::get('/display/leaderboard', function () {
    return view('leaderboard-page');
});

Route::get('/display/tradisional', function () {
    return view('traditional-page');
});

// 10. Modul KORPRI Musical Challenge (Spinwheel Genre & Lagu)
Route::get('/display/challenge', function () {
    return view('challenge-display-page');
});

Route::get('/display/challenge-results', function () {
    return view('challenge-results-page');
});

Route::get('/api/live-score', function () {
    $activeParticipant = Participant::with([
        'scores' => fn ($query) => $query->where('is_locked', true),
        'scores.details.aspect',
        'scores.judge',
    ])
                                  ->where('status', 'performing')
                                  ->first();

    $totalJudges = Judge::count();
    $currentScore = 0;
    $judgesCount = 0;

    if ($activeParticipant) {
        $judgesCount = $activeParticipant->scores->count();
        $currentScore = $judgesCount > 0 
            ? $activeParticipant->scores->sum('total_score') / ($totalJudges > 0 ? $totalJudges : 1) 
            : 0;
    }

    return response()->json([
        'participant' => $activeParticipant,
        'currentScore' => round($currentScore, 2),
        'judgesCount' => $judgesCount,
        'totalJudges' => $totalJudges
    ]);
});

// Endpoint API Leaderboard
Route::get('/api/leaderboard-data', function () {
    $totalJudges = Judge::count() > 0 ? Judge::count() : 1;

    // Ambil semua peserta beserta skornya, lalu urutkan berdasarkan rata-rata nilai tertinggi
    $participants = Participant::with([
        'scores' => fn ($query) => $query->where('is_locked', true),
    ])->get()->map(function ($p) use ($totalJudges) {
        $avgScore = $p->scores->count() > 0 ? $p->scores->sum('total_score') / $totalJudges : 0;
        $p->average_score = round($avgScore, 2);
        return $p;
    })->sortByDesc('average_score')->values();

    // Juara 1, 2, 3 otomatis dari urutan teratas
    $champion1 = $participants->get(0);
    $champion2 = $participants->get(1);
    $champion3 = $participants->get(2);

    // Juara Favorit berdasarkan pilihan Admin
    $favorite = Participant::where('is_favorite', true)->first();

    return response()->json([
        'champion1' => $champion1,
        'champion2' => $champion2,
        'champion3' => $champion3,
        'favorite'  => $favorite
    ]);
});

Route::get('/receipt', function () {
    return view('receipt');
});
