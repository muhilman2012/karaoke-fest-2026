<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        // Password default yang Anda minta
        if ($request->password === 'rkvetbz2') {
            session(['is_admin' => true]);
            return redirect('/admin/dashboard');
        }

        return back()->withErrors(['password' => 'Password Admin salah!']);
    }

    public function logout()
    {
        session()->forget('is_admin');
        return redirect('/admin/login');
    }
}