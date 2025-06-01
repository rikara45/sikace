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
use App\Models\Guru; // Tambahkan ini
use Illuminate\Support\Facades\DB; // Tambahkan ini

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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

        // 1. Try Siswa login by NIS
        $siswa = Siswa::where('nis', $loginIdentifier)->with('user')->first();
        if ($siswa && $siswa->user) {
            if (Auth::attempt(['email' => $siswa->user->email, 'password' => $password], $remember)) {
                RateLimiter::clear($this->throttleKey());
                return;
            }
        }

        // 2. Try Guru login
        // 2a. By NIP
        $guruByNip = Guru::where('nip', $loginIdentifier)->with('user')->first();
        if ($guruByNip && $guruByNip->user) {
            if (Auth::attempt(['email' => $guruByNip->user->email, 'password' => $password], $remember)) {
                RateLimiter::clear($this->throttleKey());
                return;
            }
        }

        // 2b. By Formatted Name (e.g., muhammad.habib)
        // Hanya coba jika login identifier bukan format email dan bukan NIP yang sudah dicoba
        if (!filter_var($loginIdentifier, FILTER_VALIDATE_EMAIL)) {
            $formattedName = strtolower(str_replace(' ', '.', $loginIdentifier));
            // Query ini akan mencari guru yang `nama_guru` nya setelah diformat cocok.
            // Perlu dipastikan `nama_guru` setelah diformat cukup unik antar guru jika metode ini diandalkan.
            $guruByName = Guru::where(DB::raw('LOWER(REPLACE(nama_guru, " ", "."))'), '=', $formattedName)
                              ->with('user')->first();
            if ($guruByName && $guruByName->user) {
                if (Auth::attempt(['email' => $guruByName->user->email, 'password' => $password], $remember)) {
                    RateLimiter::clear($this->throttleKey());
                    return;
                }
            }
        }

        // 3. Try Admin/Guru login by Email (fallback or primary for admins/gurus using actual email)
        if (Auth::attempt(['email' => $loginIdentifier, 'password' => $password], $remember)) {
            RateLimiter::clear($this->throttleKey());
            return;
        }

        // If all attempts fail
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