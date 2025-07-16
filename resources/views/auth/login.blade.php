<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SIKACE</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    {{-- Font Awesome untuk ikon panah --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
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
            background-color: #f9fafb;
        }

        .left {
            flex-basis: 50%;
            position: relative; /* Diubah untuk menampung gambar dan tombol */
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background-color: #1a202c;
        }

        /* --- CSS untuk Gambar Rotasi --- */
        .auth-bg-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            transition: opacity 1.5s ease-in-out; /* Durasi dan timing untuk fade */
        }

        /* --- CSS untuk Tombol Panah --- */
        .image-controls {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            padding: 0 1.5rem;
            transform: translateY(-50%);
            z-index: 10;
        }

        .arrow-button {
            background-color: rgba(0, 0, 0, 0.3);
            color: white;
            border: none;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            cursor: pointer;
            opacity: 0; /* Sembunyikan secara default */
            transition: opacity 0.3s ease-in-out, background-color 0.3s ease-in-out;
        }

        .left:hover .arrow-button {
            opacity: 0.7; /* Munculkan saat hover di area gambar */
        }

        .arrow-button:hover {
            opacity: 1;
            background-color: rgba(0, 0, 0, 0.5);
        }
        /* --- Akhir CSS Tombol Panah --- */


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
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background-color: white;
        }

        .form-container {
            max-width: 400px;
            width: 100%;
        }

        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .logo img {
            width: 32px;
            height: 32px;
            margin-right: 0.5rem;
        }

        .logo h1 {
            font-size: 23px;
            font-weight: 600;
            color: #1f2937;
        }

        h2 {
            font-size: 1.375rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
            color: #111827;
        }

        p {
            color: #6b7280;
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
             line-height: 1.4;
        }

        label {
            display: block;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
            color: #374151;
            font-weight: 500;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.65rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            line-height: 1.5;
        }
         input[type="text"]:focus,
         input[type="email"]:focus,
         input[type="password"]:focus {
            outline: none;
            border-color: #22c55e;
            box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.3);
         }

        .input-error-message {
            color: #dc2626;
            font-size: 0.75rem;
            margin-top: 0.1rem;
            margin-bottom: 0.6rem;
            display: block;
        }


        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
        }

        .options label {
            display: flex;
            align-items: center;
            color: #4b5563;
            margin-bottom: 0;
            font-weight: 400;
            cursor: pointer;
        }

        .options input[type="checkbox"] {
             margin-right: 0.4rem;
             border-radius: 0.25rem;
             border-color: #d1d5db;
             color: #22c55e;
             cursor: pointer;
             vertical-align: middle;
        }
        .options input[type="checkbox"]:focus {
             outline: none;
             box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.3);
             border-color: #22c55e;
        }
        .options span {
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
            padding: 0.7rem 0;
            border: none;
            border-radius: 0.375rem;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.2s ease-in-out;
            text-align: center;
            line-height: 1.5;
        }

        .btn-green:hover {
            background-color: #16a34a;
        }

        .password-field-container {
             margin-top: 0.75rem;
        }

        .session-status {
            margin-bottom: 0.75rem;
            font-weight: 500;
            font-size: 0.8rem;
            color: #16a34a;
            background-color: #d1fae5;
            padding: 0.6rem 0.75rem;
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
                height: 180px;
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
            
            .image-controls { /* Sembunyikan tombol panah di mobile */
                display: none;
            }

            .right {
                flex: 1;
                width: 100%;
                padding: 1.25rem;
            }

            .form-container {
                max-width: 100%;
            }

            .logo h1 {
                font-size: 1rem;
            }

            h2 {
                font-size: 1.25rem;
            }

            p {
                font-size: 0.8rem;
                margin-bottom: 1rem;
            }

             input[type="text"],
             input[type="password"] {
                 font-size: 0.85rem;
                 padding: 0.6rem 0.7rem;
             }

            .btn-green {
                padding: 0.65rem 0;
                font-size: 0.85rem;
            }
        }

    </style>
</head>
<body>
    <div class="left">
        {{-- Div untuk gambar rotasi --}}
        <div id="auth-bg-1" class="auth-bg-image"></div>
        <div id="auth-bg-2" class="auth-bg-image" style="opacity: 0;"></div>
        
        {{-- Tombol panah untuk navigasi gambar --}}
        <div class="image-controls">
            <button id="prev-image" class="arrow-button"><i class="fas fa-chevron-left"></i></button>
            <button id="next-image" class="arrow-button"><i class="fas fa-chevron-right"></i></button>
        </div>
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
                </div>

                <button type="submit" class="btn-green">
                    Masuk
                </button>
            </form>
        </div>
    </div>

    {{-- JavaScript untuk Rotasi Gambar --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Pastikan Anda sudah meletakkan gambar-gambar ini di folder public
            const images = [
                "{{ asset('images/upacara.jpg') }}",
                "{{ asset('images/sehat.jpg') }}",
                "{{ asset('images/hut guru.jpg') }}"
            ];

            const bg1 = document.getElementById('auth-bg-1');
            const bg2 = document.getElementById('auth-bg-2');
            const prevButton = document.getElementById('prev-image');
            const nextButton = document.getElementById('next-image');
            
            if (bg1 && bg2) {
                let currentIndex = 0;
                let currentBg = bg1;
                let nextBg = bg2;
                let imageInterval;
                const intervalDuration = 5000; // Durasi diubah menjadi 5 detik

                function preloadImage(url) {
                    const img = new Image();
                    img.src = url;
                }

                function changeImage(newIndex) {
                    // Penanganan index agar berputar (looping)
                    if (newIndex < 0) {
                        newIndex = images.length - 1;
                    } else if (newIndex >= images.length) {
                        newIndex = 0;
                    }
                    
                    currentIndex = newIndex;

                    // Set gambar berikutnya ke div yang tersembunyi
                    nextBg.style.backgroundImage = `url('${images[currentIndex]}')`;
                    
                    // Lakukan cross-fade
                    currentBg.style.opacity = '0';
                    nextBg.style.opacity = '1';
                    
                    // Tukar peran div untuk siklus berikutnya
                    const temp = currentBg;
                    currentBg = nextBg;
                    nextBg = temp;

                    // Preload gambar selanjutnya
                    preloadImage(images[(currentIndex + 1) % images.length]);
                }

                function startImageRotation() {
                    stopImageRotation(); // Hentikan interval sebelumnya jika ada
                    imageInterval = setInterval(() => {
                        changeImage(currentIndex + 1);
                    }, intervalDuration);
                }

                function stopImageRotation() {
                    clearInterval(imageInterval);
                }

                // Event listener untuk tombol panah
                if (prevButton && nextButton) {
                    prevButton.addEventListener('click', () => {
                        stopImageRotation();
                        changeImage(currentIndex - 1);
                        startImageRotation(); // Mulai lagi interval setelah klik
                    });

                    nextButton.addEventListener('click', () => {
                        stopImageRotation();
                        changeImage(currentIndex + 1);
                        startImageRotation(); // Mulai lagi interval setelah klik
                    });
                }
                
                // Set gambar awal dan mulai rotasi otomatis
                changeImage(0);
                startImageRotation();
            }
        });
    </script>
</body>
</html>