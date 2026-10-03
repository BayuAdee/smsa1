<?php

namespace Tests\Feature;

use App\Mail\PasswordChangedMail;
use App\Mail\ResetPasswordMail;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected User $bidan;

    protected User $kader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->bidan = User::where('role', 'bidan')->first() ?? User::create([
            'name' => 'Bidan Desa Test',
            'username' => 'bidantest',
            'email' => 'bidantest@posyandu.id',
            'password' => Hash::make('password123'),
            'role' => 'bidan',
        ]);

        if (empty($this->bidan->email)) {
            $this->bidan->update(['email' => 'bidantest@posyandu.id']);
        }

        $this->kader = User::where('role', 'kader')->first() ?? User::create([
            'name' => 'Kader Test',
            'username' => 'kadertest',
            'email' => 'kadertest@posyandu.id',
            'password' => Hash::make('password123'),
            'role' => 'kader',
        ]);

        if (empty($this->kader->email)) {
            $this->kader->update(['email' => 'kadertest@posyandu.id']);
        }
    }

    public function test_login_page_contains_forgot_password_link(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee(route('password.request'));
        $response->assertSee('Lupa password?');
    }

    public function test_forgot_password_page_can_be_rendered(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertSee('Lupa Password?');
        $response->assertSee('Khusus Bidan Desa');
    }

    public function test_bidan_can_request_password_reset_link_and_receives_masked_email_message(): void
    {
        Mail::fake();

        $response = $this->post(route('password.email'), [
            'email' => $this->bidan->email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', function ($value) {
            return str_contains($value, 'Jika email') && str_contains($value, 'terdaftar, kami telah mengirim link reset password');
        });

        // Pastikan token tersimpan di tabel password_resets
        $this->assertDatabaseHas('password_resets', [
            'user_id' => $this->bidan->id,
        ]);

        // Pastikan email reset terkirim ke bidan
        Mail::assertSent(ResetPasswordMail::class, function ($mail) {
            return $mail->user->id === $this->bidan->id && $mail->hasTo($this->bidan->email);
        });
    }

    public function test_non_existent_email_shows_neutral_message_and_creates_no_token_or_email(): void
    {
        Mail::fake();

        $response = $this->post(route('password.email'), [
            'email' => 'unknown@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', function ($value) {
            return str_contains($value, 'Jika email') && str_contains($value, 'terdaftar, kami telah mengirim link reset password');
        });

        $this->assertDatabaseCount('password_resets', 0);
        Mail::assertNothingSent();
    }

    public function test_non_bidan_email_shows_neutral_message_and_creates_no_token_or_email(): void
    {
        Mail::fake();

        $response = $this->post(route('password.email'), [
            'email' => $this->kader->email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', function ($value) {
            return str_contains($value, 'Jika email') && str_contains($value, 'terdaftar, kami telah mengirim link reset password');
        });

        // Pastikan tidak ada token dibuat untuk kader
        $this->assertDatabaseMissing('password_resets', [
            'user_id' => $this->kader->id,
        ]);
        Mail::assertNothingSent();
    }

    public function test_reset_password_page_shows_form_when_token_is_valid(): void
    {
        $plainToken = 'test-plain-token-1234567890123456';
        $tokenHash = hash('sha256', $plainToken);

        PasswordReset::create([
            'user_id' => $this->bidan->id,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addMinutes(30),
            'created_at' => now(),
        ]);

        $response = $this->get(route('password.reset', ['token' => $plainToken]));

        $response->assertStatus(200);
        $response->assertSee('Buat Password Baru');
        $response->assertSee($this->bidan->email);
    }

    public function test_reset_password_page_shows_error_when_token_is_invalid_or_expired(): void
    {
        // 1. Invalid token
        $response = $this->get(route('password.reset', ['token' => 'invalid-token']));
        $response->assertStatus(200);
        $response->assertSee('Tautan Tidak Valid');

        // 2. Expired token
        $plainToken = 'expired-plain-token';
        PasswordReset::create([
            'user_id' => $this->bidan->id,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->subMinutes(1),
            'created_at' => now()->subMinutes(31),
        ]);

        $expiredResponse = $this->get(route('password.reset', ['token' => $plainToken]));
        $expiredResponse->assertStatus(200);
        $expiredResponse->assertSee('Tautan Tidak Valid');
    }

    public function test_bidan_can_reset_password_successfully_and_receives_confirmation_email(): void
    {
        Mail::fake();

        $plainToken = 'valid-token-for-reset-operation-12345';
        $tokenHash = hash('sha256', $plainToken);

        PasswordReset::create([
            'user_id' => $this->bidan->id,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addMinutes(30),
            'created_at' => now(),
        ]);

        $response = $this->post(route('password.update'), [
            'token' => $plainToken,
            'password' => 'passwordBaru123!',
            'password_confirmation' => 'passwordBaru123!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        // Verify password is updated in database
        $this->bidan->refresh();
        $this->assertTrue(Hash::check('passwordBaru123!', $this->bidan->password));

        // Verify token is deleted
        $this->assertDatabaseMissing('password_resets', [
            'token_hash' => $tokenHash,
        ]);

        // Verify confirmation mail sent
        Mail::assertSent(PasswordChangedMail::class, function ($mail) {
            return $mail->user->id === $this->bidan->id && $mail->hasTo($this->bidan->email);
        });
    }

    public function test_reset_fails_if_password_confirmation_does_not_match_or_too_short(): void
    {
        $plainToken = 'valid-token-for-mismatch';
        $tokenHash = hash('sha256', $plainToken);

        PasswordReset::create([
            'user_id' => $this->bidan->id,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addMinutes(30),
            'created_at' => now(),
        ]);

        // Mismatch
        $response = $this->post(route('password.update'), [
            'token' => $plainToken,
            'password' => 'passwordBaru123!',
            'password_confirmation' => 'berbeda123!',
        ]);

        $response->assertSessionHasErrors('password');

        // Short password (< 8 chars)
        $shortResponse = $this->post(route('password.update'), [
            'token' => $plainToken,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $shortResponse->assertSessionHasErrors('password');
    }
}
