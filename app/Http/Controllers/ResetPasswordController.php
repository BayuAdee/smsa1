<?php

namespace App\Http\Controllers;

use App\Mail\PasswordChangedMail;
use App\Models\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $token = (string) $request->query('token');

        if (empty($token)) {
            return view('auth.reset-password', [
                'valid' => false,
                'error' => 'Token reset password tidak ditemukan. Silakan minta link baru.',
            ]);
        }

        $tokenHash = hash('sha256', $token);
        $passwordReset = PasswordReset::where('token_hash', $tokenHash)->with('user')->first();

        if (! $passwordReset || ! $passwordReset->isValid() || ! $passwordReset->user || ! $passwordReset->user->isBidan()) {
            return view('auth.reset-password', [
                'valid' => false,
                'error' => 'Link reset password tidak valid atau sudah kedaluwarsa. Silakan minta link baru.',
            ]);
        }

        return view('auth.reset-password', [
            'valid' => true,
            'token' => $token,
            'email' => $passwordReset->user->email,
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'token.required' => 'Token reset password wajib disertakan.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $tokenHash = hash('sha256', $request->token);
        $passwordReset = PasswordReset::where('token_hash', $tokenHash)->with('user')->first();

        // Validasi ulang: token valid, belum expired, belum used, dan user masih Bidan
        if (! $passwordReset || ! $passwordReset->isValid() || ! $passwordReset->user || ! $passwordReset->user->isBidan()) {
            return back()->withErrors([
                'token' => 'Permintaan reset password tidak valid atau telah kedaluwarsa. Silakan ajukan link baru.',
            ]);
        }

        $user = $passwordReset->user;

        // Simpan password baru sebagai hash
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Hapus token reset untuk user ini
        PasswordReset::where('user_id', $user->id)->delete();

        // Invalidate semua sesi aktif pengguna jika menggunakan session database
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        // Jika user yang sedang login adalah user yang sama, logout
        if (Auth::check() && Auth::id() === $user->id) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Kirim email notifikasi bahwa password berhasil diubah
        try {
            Mail::to($user->email)->send(new PasswordChangedMail($user));
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim email notifikasi perubahan password: '.$e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e,
            ]);
        }

        return redirect()->route('login')->with('success', 'Password berhasil diperbarui! Silakan masuk menggunakan password baru Anda.');
    }
}
