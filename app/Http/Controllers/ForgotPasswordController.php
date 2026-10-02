<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $maskedEmail = $this->maskEmail($request->email);

        $user = User::where('email', $request->email)->first();

        // Hanya proses jika email terdaftar dan perannya adalah Bidan Desa
        if ($user && $user->isBidan()) {
            // Hapus request reset password sebelumnya dari user ini
            PasswordReset::where('user_id', $user->id)->delete();

            // Buat token kriptografis acak 32 bytes (64 karakter hex)
            $plainToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $plainToken);

            // Simpan hash token dengan masa aktif 30 menit
            PasswordReset::create([
                'user_id' => $user->id,
                'token_hash' => $tokenHash,
                'expires_at' => now()->addMinutes(30),
                'created_at' => now(),
            ]);

            $resetUrl = route('password.reset', ['token' => $plainToken]);

            try {
                Mail::to($user->email)->send(new ResetPasswordMail($user, $resetUrl));
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim email reset password: '.$e->getMessage(), [
                    'user_id' => $user->id,
                    'exception' => $e,
                ]);
            }
        }

        // Selalu tampilkan pesan netral dengan masked email (anti-enumerasi)
        return back()->with('status', "Jika email {$maskedEmail} terdaftar, kami telah mengirim link reset password di Inbox/Spam.");
    }

    public function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];
        $len = strlen($name);

        if ($len <= 5) {
            // Jika panjang nama email 5 huruf atau kurang (terlalu pendek)
            $maskedName = substr($name, 0, 1).'***';
        } else {
            // 3 huruf depan dan 2 huruf belakang kelihatan
            $maskedName = substr($name, 0, 3).'***'.substr($name, -2);
        }

        return $maskedName.'@'.$domain;
    }
}
