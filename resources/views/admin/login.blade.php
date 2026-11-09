<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>Admin login &middot; Master Gyi</title>
    <link rel="icon" type="image/png" href="{{ asset('img/master-gyi-favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Noto+Sans+Myanmar:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin-flow.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin-login.css') }}?v={{ filemtime(public_path('css/admin-login.css')) }}" rel="stylesheet">
</head>
<body class="app-root admin-login" data-theme="light" data-density="compact">
    <script>
        try {
            document.body.dataset.theme = localStorage.getItem('app.theme') === 'dark' ? 'dark' : 'light';
            const brand = localStorage.getItem('app.brand');
            if (/^#[0-9a-f]{6}$/i.test(brand || '')) document.body.style.setProperty('--color-primary', brand);
        } catch (error) { /* Use the default theme when storage is unavailable. */ }
    </script>

    <header class="login-header">
        <div class="login-brand">
            <img src="{{ asset('img/master-gyi-logo-small.webp') }}" alt="" width="36" height="36">
            <div><strong>Master Gyi</strong><span>Office admin</span></div>
        </div>
        <button class="login-theme" type="button" id="login-theme" aria-label="Switch to dark theme" aria-pressed="false">
            <i class="fas fa-moon" aria-hidden="true"></i>
            <span>Dark mode</span>
        </button>
    </header>

    <main class="login-main">
        <section class="login-panel" aria-labelledby="login-title">
            <div class="login-heading">
                <span class="login-symbol" aria-hidden="true"><i class="fas fa-user-shield"></i></span>
                <p class="eyebrow">ADMIN CONSOLE</p>
                <h1 id="login-title">Welcome back</h1>
                <p>Sign in to your Master Gyi admin account.</p>
            </div>

            @if (session('status'))
                <p class="login-status" role="status">{{ session('status') }}</p>
            @endif

            <form class="login-form" action="{{ route('admin.login') }}" method="POST">
                @csrf
                <div class="login-field">
                    <label for="login-email">Email address</label>
                    <input type="email" id="login-email" name="email" value="{{ old('email') }}"
                        placeholder="you@example.com" autocomplete="username" autocapitalize="none" spellcheck="false" required
                        @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    @error('email')
                        <p class="login-error" id="email-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="login-field">
                    <label for="login-password">Password</label>
                    <div class="login-password">
                        <input type="password" id="login-password" name="password" placeholder="Enter your password"
                            autocomplete="current-password" required
                            @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                        <button type="button" id="login-password-toggle" aria-label="Show password" aria-pressed="false" aria-controls="login-password" hidden>
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="login-error" id="password-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <label class="login-remember" for="login-remember">
                    <input type="checkbox" id="login-remember" name="remember" value="1" @checked(old('remember'))>
                    <span>Remember me</span>
                </label>

                <button class="login-submit" type="submit">
                    <span>Sign in</span><i class="fas fa-arrow-right" aria-hidden="true"></i>
                </button>
            </form>

            <p class="login-help">Need access? Contact your administrator.</p>
        </section>
    </main>

    <footer class="login-footer">&copy; {{ date('Y') }} Master Gyi <span aria-hidden="true">&middot;</span> Office admin</footer>

    <script>
        (function () {
            const themeButton = document.getElementById('login-theme');
            const updateThemeButton = () => {
                const dark = document.body.dataset.theme === 'dark';
                themeButton.setAttribute('aria-pressed', String(dark));
                themeButton.setAttribute('aria-label', dark ? 'Switch to light theme' : 'Switch to dark theme');
                themeButton.querySelector('i').className = dark ? 'fas fa-sun' : 'fas fa-moon';
                themeButton.querySelector('span').textContent = dark ? 'Light mode' : 'Dark mode';
            };
            updateThemeButton();
            themeButton.addEventListener('click', () => {
                const theme = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
                document.body.dataset.theme = theme;
                try { localStorage.setItem('app.theme', theme); } catch (error) { }
                updateThemeButton();
            });

            const password = document.getElementById('login-password');
            const passwordButton = document.getElementById('login-password-toggle');
            passwordButton.hidden = false;
            passwordButton.addEventListener('click', () => {
                const visible = password.type === 'password';
                password.type = visible ? 'text' : 'password';
                passwordButton.setAttribute('aria-pressed', String(visible));
                passwordButton.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
                passwordButton.querySelector('i').className = visible ? 'fas fa-eye-slash' : 'fas fa-eye';
            });

            document.querySelector('.login-form').addEventListener('submit', () => {
                const button = document.querySelector('.login-submit');
                button.disabled = true;
                button.querySelector('span').textContent = 'Signing in\u2026';
            });
            window.addEventListener('pageshow', () => {
                const button = document.querySelector('.login-submit');
                button.disabled = false;
                button.querySelector('span').textContent = 'Sign in';
            });
        })();
    </script>
</body>
</html>
