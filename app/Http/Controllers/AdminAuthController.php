<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        // Jika sudah login admin, langsung lempar ke dashboard admin
        if (session('is_admin')) {
            return redirect('/admin/dashboard');
        }
        return view('admin-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'password' => ['required', 'string', 'max:255'],
        ]);
        $passwordHash = config('admin.password_hash');

        if (is_string($passwordHash) && Hash::check($credentials['password'], $passwordHash)) {
            $request->session()->regenerate();
            $request->session()->put('is_admin', true);

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()
            ->withErrors(['password' => 'Password Admin salah!'])
            ->onlyInput();
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
