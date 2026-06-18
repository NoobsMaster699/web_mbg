<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin SPK MBG</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-intro">
            <p class="eyebrow">Sistem Pendukung Keputusan</p>
            <h1>Menu Makan Bergizi Gratis</h1>
            <p>
                Masuk sebagai admin untuk mengelola alternatif menu, penilaian gizi,
                perbandingan AHP, hasil SAW, dan laporan rekomendasi.
            </p>
            <div class="login-method">
                <span>Session aktif setelah login</span>
                <strong>Cookie: mbg_admin</strong>
            </div>
        </section>

        <section class="login-card">
            <div class="brand login-brand">
                <span class="brand-mark">M</span>
                <span>
                    <strong>SPK MBG</strong>
                    <small>AHP + SAW</small>
                </span>
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="login-form">
                @csrf
                <label>
                    <span>Username</span>
                    <input
                        type="text"
                        name="username"
                        value="{{ old('username', 'admin') }}"
                        autocomplete="username"
                        required
                        autofocus
                    >
                </label>

                <label>
                    <span>Password</span>
                    <input
                        type="password"
                        name="password"
                        placeholder="admin123"
                        autocomplete="current-password"
                        required
                    >
                </label>

                @error('username')
                    <p class="form-error">{{ $message }}</p>
                @enderror

                <button class="btn primary" type="submit">Masuk</button>
                <p class="login-hint">Demo login: username <strong>admin</strong>, password <strong>admin123</strong>.</p>
            </form>
        </section>
    </main>
</body>
</html>
