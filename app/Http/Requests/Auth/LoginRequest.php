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

        // Coba login sebagai Siswa menggunakan NIS
        $siswa = Siswa::where('nis', $loginIdentifier)->with('user')->first();

        if ($siswa && $siswa->user) {
            // Coba autentikasi dengan user yang terhubung ke siswa
            // Password awal siswa adalah NIS mereka
            if (Auth::attempt(['email' => $siswa->user->email, 'password' => $password], $this->boolean('remember'))) {
                RateLimiter::clear($this->throttleKey());
                return;
            }
        }

        // Jika bukan siswa atau login NIS gagal, coba login sebagai Admin/Guru menggunakan input sebagai email
        if (!Auth::attempt(['email' => $loginIdentifier, 'password' => $password], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'login_identifier' => trans('auth.failed'),
            ]);
        }

        // Jika user yang login adalah siswa TAPI mencoba login via form email/password standar,
        // pastikan tidak terjadi. Ini seharusnya sudah ditangani oleh pengecekan $siswa di atas.
        $loggedInUser = Auth::user();
        if ($loggedInUser instanceof User && $loggedInUser->hasRole('siswa') && filter_var($loginIdentifier, FILTER_VALIDATE_EMAIL)) {
            // Jika siswa mencoba login dengan emailnya (yang pseudo) dan password NIS,
            // ini mungkin berhasil jika email pseudo-nya kebetulan sama dengan input.
            // Skenario ini seharusnya tidak menjadi masalah utama jika NIS adalah identifier utama.
        }


        RateLimiter::clear($this->throttleKey());
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
