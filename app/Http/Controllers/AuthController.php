<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Posyandu;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $fieldType = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$fieldType => $credentials['login'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            if ($user->isKader()) {
                session(['selected_posyandu_id' => $user->posyandu_id]);
            } else {
                session(['selected_posyandu_id' => 'all']);
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'login' => 'Username/Email atau Password salah.',
        ])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }

    public function selectPosyandu(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isBidan()) {
            abort(403, 'Akses khusus Bidan');
        }

        $posyanduId = $request->input('posyandu_id', 'all');
        session(['selected_posyandu_id' => $posyanduId]);

        return back()->with('success', 'Filter Posyandu berhasil diperbarui.');
    }
}
