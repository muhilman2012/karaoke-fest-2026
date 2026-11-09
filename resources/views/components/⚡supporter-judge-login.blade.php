<?php

use App\Models\SupporterJudge;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

new class extends Component
{
    public $pin = '';
    public $errorMessage = '';

    public function login()
    {
        $key = 'supporter-judge-login:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->errorMessage = 'Terlalu banyak percobaan. Coba kembali dalam ' . RateLimiter::availableIn($key) . ' detik.';
            $this->pin = '';
            return;
        }

        $judge = SupporterJudge::where('passcode', $this->pin)->first();

        if (!$judge) {
            RateLimiter::hit($key, 60);
            $this->errorMessage = 'PIN salah. Silakan periksa kembali.';
            $this->pin = '';
            return;
        }

        RateLimiter::clear($key);
        request()->session()->regenerate();
        session(['supporter_judge_id' => $judge->id]);

        return redirect()->route('supporter.form');
    }
};
?>

<div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-orange-600 via-amber-500 to-yellow-400 p-4">
    <div class="absolute -left-20 -top-20 h-64 w-64 rounded-full bg-white/15 blur-2xl"></div>
    <div class="absolute -bottom-24 -right-16 h-72 w-72 rounded-full bg-orange-950/20 blur-3xl"></div>

    <div class="relative w-full max-w-sm overflow-hidden rounded-[2rem] border border-white/60 bg-white/95 p-6 text-center shadow-2xl shadow-orange-950/25 backdrop-blur sm:p-9">
        <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-orange-500 to-amber-400 text-4xl shadow-lg shadow-orange-200">📣</div>
        <p class="mb-2 text-xs font-black uppercase tracking-[0.22em] text-orange-500">KORPRI Musical Challenge</p>
        <h1 class="mb-2 text-3xl font-black leading-tight text-slate-900">Juri Best Supporter</h1>
        <p class="mb-7 text-sm leading-relaxed text-slate-500">Masukkan PIN khusus yang diberikan panitia</p>

        <form wire:submit.prevent="login" class="space-y-6">
            <div class="text-left">
                <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">PIN Juri</label>
                <input type="password" inputmode="numeric" pattern="[0-9]*" maxlength="4" wire:model="pin"
                    class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50 px-5 py-4 text-center text-4xl font-black tracking-[0.5em] text-orange-600 outline-none transition placeholder:text-slate-300 focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                    placeholder="••••" required autofocus>
            </div>

            @if($errorMessage)
                <div class="rounded-xl bg-red-50 p-3 font-bold text-red-600">{{ $errorMessage }}</div>
            @endif

            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-orange-600 to-amber-500 py-4 text-lg font-black text-white shadow-lg shadow-orange-200 transition hover:-translate-y-0.5 hover:shadow-xl">
                Masuk ke Penilaian <span aria-hidden="true">→</span>
            </button>
        </form>

        <p class="mt-6 text-xs text-slate-400">Satu akun hanya digunakan oleh satu juri</p>
    </div>
</div>
