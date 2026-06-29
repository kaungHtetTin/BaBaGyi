@php
    $topbarUser = Auth::user();
    $topbarNameParts = preg_split('/\s+/', trim($topbarUser->name ?? 'Admin'));
    $topbarInitials = strtoupper(substr($topbarNameParts[0] ?? 'A', 0, 1) . substr($topbarNameParts[1] ?? '', 0, 1));
@endphp

<header class="admin-topbar glass">
    <button id="sidebarToggleTop" class="icon-btn d-md-none" type="button" aria-label="Toggle sidebar">
        <i class="fa fa-bars"></i>
    </button>

    <form action="{{ route('admin.users.search') }}" class="global-search" method="GET">
        <label class="search-box mb-0">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search user by name, email, or phone" aria-label="Search users" name="search">
        </label>
    </form>

    <div class="topbar-actions">
        <a class="icon-btn d-sm-none" href="{{ route('admin.users.search') }}" aria-label="Search">
            <i class="fas fa-search"></i>
        </a>

        <div class="theme-control" data-theme-control>
            <button class="icon-btn" type="button" data-admin-theme-toggle aria-label="Theme and brand">
                <i class="fas fa-palette"></i>
            </button>
            <div class="theme-popover glass">
                <p class="eyebrow">Theme mode</p>
                <div class="segmented-control" data-theme-mode>
                    <button type="button" data-theme-value="light">Light</button>
                    <button type="button" data-theme-value="dark">Dark</button>
                </div>

                <p class="eyebrow mt-3">Brand color</p>
                <div class="brand-swatches">
                    <button type="button" style="background:#545760" data-brand-value="#545760" aria-label="Slate"></button>
                    <button type="button" style="background:#2874bc" data-brand-value="#2874bc" aria-label="Blue"></button>
                    <button type="button" style="background:#168255" data-brand-value="#168255" aria-label="Green"></button>
                    <button type="button" style="background:#b77700" data-brand-value="#b77700" aria-label="Amber"></button>
                    <input type="color" value="#545760" data-brand-picker aria-label="Custom brand color">
                </div>
            </div>
        </div>

        <div class="profile-menu" data-profile-menu>
            <button class="profile-menu-toggle" type="button" data-profile-menu-toggle aria-expanded="false" aria-label="Profile menu">
                @if (!empty($topbarUser->avatar_url))
                    <img src="{{ asset($topbarUser->avatar_url) }}" alt="{{ $topbarUser->name }}">
                @else
                    <span>{{ $topbarInitials ?: 'A' }}</span>
                @endif
                <i class="fas fa-chevron-down"></i>
            </button>

            <div class="profile-popover glass">
                <div class="profile-popover-header">
                    @if (!empty($topbarUser->avatar_url))
                        <img src="{{ asset($topbarUser->avatar_url) }}" alt="{{ $topbarUser->name }}">
                    @else
                        <span>{{ $topbarInitials ?: 'A' }}</span>
                    @endif
                    <div>
                        <strong>{{ $topbarUser->name }}</strong>
                        <small>{{ $topbarUser->email ?: 'Office admin' }}</small>
                    </div>
                </div>

                <nav class="profile-quick-links" aria-label="Profile quick links">
                    <a href="{{ route('admin.profile') }}">
                        <i class="fas fa-user"></i>
                        Profile
                    </a>
                    <a href="{{ route('admin.users') }}">
                        <i class="fas fa-users"></i>
                        Users
                    </a>
                    <a href="{{ route('admin.admins') }}">
                        <i class="fas fa-user-shield"></i>
                        Admins
                    </a>
                    <a href="{{ route('admin.register') }}">
                        <i class="fas fa-user-plus"></i>
                        Add admin
                    </a>
                    <a class="danger" href="{{ route('logout') }}">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </nav>
            </div>
        </div>
    </div>
</header>

<script>
    (function () {
        const root = document.querySelector('.app-root');
        const control = document.querySelector('[data-theme-control]');
        const toggle = document.querySelector('[data-admin-theme-toggle]');
        const modeButtons = document.querySelectorAll('[data-theme-value]');
        const brandButtons = document.querySelectorAll('[data-brand-value]');
        const brandPicker = document.querySelector('[data-brand-picker]');
        const profileMenu = document.querySelector('[data-profile-menu]');
        const profileToggle = document.querySelector('[data-profile-menu-toggle]');

        if (!root || !control || !toggle) return;

        const setTheme = (theme) => {
            root.setAttribute('data-theme', theme);
            localStorage.setItem('app.theme', theme);
            modeButtons.forEach((button) => button.classList.toggle('active', button.dataset.themeValue === theme));
        };

        const setBrand = (brand) => {
            root.style.setProperty('--color-primary', brand);
            localStorage.setItem('app.brand', brand);
            if (brandPicker) brandPicker.value = brand;
        };

        setTheme(localStorage.getItem('app.theme') || 'light');
        setBrand(localStorage.getItem('app.brand') || '#545760');

        toggle.addEventListener('click', () => {
            control.classList.toggle('open');
            if (profileMenu && profileToggle) {
                profileMenu.classList.remove('open');
                profileToggle.setAttribute('aria-expanded', 'false');
            }
        });

        if (profileMenu && profileToggle) {
            profileToggle.addEventListener('click', () => {
                const isOpen = profileMenu.classList.toggle('open');
                profileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                control.classList.remove('open');
            });
        }

        document.addEventListener('click', (event) => {
            if (!control.contains(event.target)) control.classList.remove('open');
            if (profileMenu && !profileMenu.contains(event.target)) {
                profileMenu.classList.remove('open');
                if (profileToggle) profileToggle.setAttribute('aria-expanded', 'false');
            }
        });

        modeButtons.forEach((button) => {
            button.addEventListener('click', () => setTheme(button.dataset.themeValue));
        });

        brandButtons.forEach((button) => {
            button.addEventListener('click', () => setBrand(button.dataset.brandValue));
        });

        if (brandPicker) {
            brandPicker.addEventListener('input', () => setBrand(brandPicker.value));
        }
    })();
</script>
