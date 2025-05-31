<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SIKACE</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
        }
        body {
            display: flex;
            height: 100vh;
            flex-direction: row;
            background-color: #f9fafb; /* Add a subtle background */
        }

        .left {
            flex-basis: 50%; /* Keep 50% basis */
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .bg-desktop {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .bg-mobile {
            display: none;
            width: 100%;
            height: auto;
            object-fit: cover;
        }

        .right {
            flex: 1; /* Takes remaining space */
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem; /* Keep padding for overall spacing */
            background-color: white;
        }

        .form-container {
            max-width: 400px; /* Keep max-width */
            width: 100%;
        }

        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem; /* Reduced logo margin */
        }

        .logo img {
            width: 32px;
            height: 32px;
            margin-right: 0.5rem;
        }

        .logo h1 {
            /* font-size: 1.25rem; /* Slightly reduce if needed, check original */
             font-size: 23px; /* Match original CSS */
            font-weight: 600;
            color: #1f2937;
        }

        h2 {
            font-size: 1.375rem; /* Reduced h2 size (22px) */
            font-weight: 600;
            margin-bottom: 0.2rem; /* Slightly reduced margin */
            color: #111827;
        }

        p {
            color: #6b7280;
            margin-bottom: 1.25rem; /* Reduced paragraph margin */
            font-size: 0.875rem; /* Reduced paragraph size (14px) */
             line-height: 1.4; /* Adjust line height */
        }

        label {
            display: block;
            font-size: 0.875rem; /* 14px */
            margin-bottom: 0.25rem;
            color: #374151;
            font-weight: 500; /* Keep label weight */
        }

        input[type="text"], /* Apply to text input as well */
        input[type="password"] {
            width: 100%;
            padding: 0.65rem 0.75rem; /* Slightly reduced vertical padding */
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            margin-bottom: 0.25rem; /* Reduced margin below input */
            font-size: 0.9rem; /* Slightly reduced input font size (14.4px) */
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            line-height: 1.5; /* Ensure consistent line height */
        }
         input[type="text"]:focus, /* Apply to text input as well */
         input[type="email"]:focus,
         input[type="password"]:focus {
            outline: none;
            border-color: #22c55e;
            box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.3);
         }

        .input-error-message {
            color: #dc2626;
            font-size: 0.75rem; /* 12px */
            margin-top: 0.1rem; /* Minimal top margin */
            margin-bottom: 0.6rem; /* Space before next element */
            display: block;
        }


        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem; /* Reduced margin */
            font-size: 0.875rem; /* 14px */
        }

        .options label {
            display: flex;
            align-items: center;
            color: #4b5563;
            margin-bottom: 0;
            font-weight: 400;
            cursor: pointer; /* Make label clickable */
        }

        .options input[type="checkbox"] {
             margin-right: 0.4rem; /* Slightly reduced margin */
             border-radius: 0.25rem;
             border-color: #d1d5db;
             color: #22c55e;
             cursor: pointer;
             /* Vertically align checkbox slightly better if needed */
             vertical-align: middle;
        }
        .options input[type="checkbox"]:focus {
             outline: none;
             box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.3);
             border-color: #22c55e;
        }
        .options span { /* Target the text span */
             vertical-align: middle;
        }


        .options a {
            color: #22c55e;
            text-decoration: none;
            font-weight: 500;
        }
        .options a:hover {
            text-decoration: underline;
        }

        .btn-green {
            background-color: #22c55e;
            color: white;
            padding: 0.7rem 0; /* Adjusted padding */
            border: none;
            border-radius: 0.375rem;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            font-size: 0.9rem; /* Reduced button font size */
            transition: background-color 0.2s ease-in-out;
            text-align: center; /* Ensure text is centered */
            line-height: 1.5; /* Ensure consistent line height */
        }

        .btn-green:hover {
            background-color: #16a34a;
        }

        /* Spacing adjustment for the password field */
        .password-field-container {
             margin-top: 0.75rem; /* Adjusted spacing */
        }

        .session-status {
            margin-bottom: 0.75rem; /* Reduced margin */
            font-weight: 500;
            font-size: 0.8rem; /* Slightly smaller status text */
            color: #16a34a;
            background-color: #d1fae5;
            padding: 0.6rem 0.75rem; /* Adjusted padding */
            border-radius: 0.375rem;
            border-left: 4px solid #10b981;
        }


        /* Responsive - Mobile */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
                height: auto;
                min-height: 100vh;
            }

            .left {
                flex-basis: auto;
                height: 180px; /* Adjusted mobile height */
                width: 100%;
            }

            .bg-desktop {
                display: none;
            }

            .bg-mobile {
                display: block;
                height: 100%;
                width: 100%;
            }

            .right {
                flex: 1;
                width: 100%;
                padding: 1.25rem; /* Adjusted mobile padding */
            }

            .form-container {
                max-width: 100%;
            }

            .logo h1 {
                font-size: 1rem; /* Adjust mobile logo size */
            }

            h2 {
                font-size: 1.25rem; /* Adjust mobile heading size */
            }

            p {
                font-size: 0.8rem; /* Adjust mobile paragraph size */
                margin-bottom: 1rem;
            }

             input[type="text"], /* Apply to text input as well */
             input[type="password"] {
                 font-size: 0.85rem; /* Adjust mobile input size */
                 padding: 0.6rem 0.7rem;
             }


            .btn-green {
                padding: 0.65rem 0; /* Adjust mobile button padding */
                font-size: 0.85rem; /* Adjust mobile button font size */
            }
        }

    </style>
</head>
<body>
    <div class="left">
        <img src="{{ asset('images/background1.png') }}" class="bg-desktop" alt="Background Desktop">
        {{-- Jika Anda punya gambar background khusus mobile, gunakan ini --}}
        <img src="{{ asset('images/background2.png') }}" class="bg-mobile" alt="Background Mobile">
        {{-- Jika tidak, Anda bisa gunakan gambar yang sama atau sembunyikan .left di mobile --}}
    </div>
    <div class="right">
        <div class="form-container">
            <div class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="Logo">
                <h1>SMA KARTIKA XIX 1 BANDUNG</h1>
            </div>

            <h2>Selamat datang</h2>
            <p>Silakan masukkan kredensial Anda</p>

            <x-auth-session-status class="session-status" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div>
                    <label for="login_identifier">Username / NIS (Untuk Siswa) | NIP (Untuk Guru)</label>
                    <input id="login_identifier" type="text" name="login_identifier" value="{{ old('login_identifier') }}" required autofocus autocomplete="username">
                    {{-- Error bisa untuk login_identifier, email, atau nis --}}
                    @error('login_identifier') <span class="input-error-message">{{ $message }}</span> @enderror
                    @error('email') <span class="input-error-message">{{ $message }}</span> @enderror
                    @error('nis') <span class="input-error-message">{{ $message }}</span> @enderror
                </div>

                <div class="password-field-container">
                    <label for="password">Kata Sandi</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password">
                    @error('password')
                        <span class="input-error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="options">
                    <label for="remember_me">
                        <input id="remember_me" type="checkbox" name="remember">
                        <span>Ingat saya</span>
                    </label>
                    {{-- Hapus Lupa Password jika tidak relevan untuk NIS --}}
                    {{-- @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">
                            Lupa kata sandi?
                        </a>
                    @endif --}}
                </div>

                <button type="submit" class="btn-green">
                    Masuk
                </button>
            </form>
        </div>
    </div>
</body>
</html>