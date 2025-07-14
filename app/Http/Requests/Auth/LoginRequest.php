<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Guru;
class LoginRequest extends FormRequest
{
    // ... (metode authorize dan rules tetap sama) ...
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login_identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginIdentifier = $this->input('login_identifier');
        $password = $this->input('password');
        $remember = $this->boolean('remember');

        $credentials = ['password' => $password];

        // Attempt 1: Cek jika identifier adalah email (untuk Admin atau Guru dengan email asli)
        if (filter_var($loginIdentifier, FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = $loginIdentifier;
            if (Auth::attempt($credentials, $remember)) {
                RateLimiter::clear($this->throttleKey());
                return;
            }
        }

        // Attempt 2: Cek jika identifier adalah username (untuk Guru)
        // Cek ini dilakukan jika identifier bukan email, atau login email gagal
        $credentialsForUsername = $credentials;
        $credentialsForUsername['username'] = $loginIdentifier;
        if (Auth::attempt($credentialsForUsername, $remember)) {
                RateLimiter::clear($this->throttleKey());
                return;
            }

        // Attempt 3: Cek jika identifier adalah NIP Guru
        $guru = Guru::where('nip', $loginIdentifier)->with('user')->first();
        if ($guru && $guru->user) {
            $credentialsForNip = $credentials;
            $credentialsForNip['email'] = $guru->user->email;
            if (Auth::attempt($credentialsForNip, $remember)) {
                    RateLimiter::clear($this->throttleKey());
                    return;
                }
            }

        // Attempt 4: Cek jika identifier adalah NIS Siswa
        $siswa = Siswa::where('nis', $loginIdentifier)->with('user')->first();
        if ($siswa && $siswa->user) {
            $credentialsForNis = $credentials;
            $credentialsForNis['email'] = $siswa->user->email; // Gunakan pseudo-email
            if (Auth::attempt($credentialsForNis, $remember)) {
            RateLimiter::clear($this->throttleKey());
            return;
        }
        }

        // Jika semua upaya gagal
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'login_identifier' => trans('auth.failed'),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login_identifier' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('login_identifier')).'|'.$this->ip());
    }
}