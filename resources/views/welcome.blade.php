<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#151710">
    <meta name="description" content="မာစတာကြီး 2D 3D ဆော့ဝဲကို Android ဖုန်းအတွက် ဒေါင်းလုဒ်ရယူနိုင်ပါသည်။">
    <meta property="og:title" content="မာစတာကြီး | Master Gyi 2D 3D">
    <meta property="og:description" content="မာစတာကြီးမှ ကြိုဆိုပါတယ်။ Android ဆော့ဝဲကို ဒီနေရာမှာ ဒေါင်းလုဒ်ရယူလိုက်ပါ။">
    <meta property="og:image" content="{{ asset('img/master-gyi-social.png') }}">
    <title>မာစတာကြီး | Master Gyi 2D 3D</title>
    <link rel="icon" type="image/png" href="{{ asset('img/master-gyi-favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Noto+Sans+Myanmar:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}?v={{ filemtime(public_path('css/welcome.css')) }}">
</head>
<body>
    <a class="skip-link" href="#welcome">အကြောင်းအရာသို့ သွားရန်</a>
    <div class="page-shell">
        <header class="site-header">
            <a class="brand" href="{{ route('index') }}" aria-label="Master Gyi home">
                <img src="{{ asset('img/master-gyi-logo-small.webp') }}" alt="" width="52" height="52">
                <span class="brand-copy"><strong>Master Gyi<span class="brand-dot">.</span></strong><span>မာစတာကြီး 2D · 3D</span></span>
            </a>
            <span class="header-note"><span class="status-dot" aria-hidden="true"></span> Android အတွက် ရယူနိုင်ပါပြီ</span>
        </header>

        <main id="welcome">
            <section class="hero" aria-labelledby="welcome-title">
                <div class="hero-copy">
                    <p class="eyebrow"><span aria-hidden="true">✦</span> WELCOME TO MASTER GYI</p>
                    <h1 id="welcome-title">မင်္ဂလာပါ။<br><span>မာစတာကြီး</span> မှ<br>ကြိုဆိုပါတယ်။</h1>
                    <p class="intro">သင့်ဖုန်းထဲက <span class="latin">2D · 3D</span> ကမ္ဘာလေး။</p>
                    <div class="description">
                        <p>Agent မလို ကိုယ်တိုင် Free အကောင့်ဖွင့်နိုင်သည်။</p>
                        <p>နိုင်ငွေကို အလျင်မြန်ဆုံး system ဖြင့် အကောင့်ထဲသို့ တိုက်ရိုက်ထည့်ပေးသည်။</p>
                        <p>အခုပဲ အခမဲ့ အကောင့်ဖွင့်ပြီး <span class="latin">MasterGyi</span> ဆော့ဝဲမှာ ကစားလိုက်ကြရအောင်ရှင့်။</p>
                    </div>

                    <div class="download-area" id="download">
                        <a class="download-button" href="{{ route('app.download') }}" aria-describedby="download-note">
                            <svg width="29" height="29" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 15v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span><small>DOWNLOAD FOR ANDROID</small><strong>ဆော့ဝဲ ဒေါင်းလုဒ်ရယူရန်</strong></span>
                            <span class="download-arrow" aria-hidden="true">↗</span>
                        </a>
                        <p class="download-note" id="download-note"><svg width="16" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m7 3-1-2 .8-.5L8 2.7a10 10 0 0 1 8 0L17.2.5l.8.5-1 2a7 7 0 0 1 3 5H4a7 7 0 0 1 3-5ZM8 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2Zm8 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2ZM4 9h16v9a2 2 0 0 1-2 2v3h-3v-3H9v3H6v-3a2 2 0 0 1-2-2V9ZM0 9h3v10H0V9Zm21 0h3v10h-3V9Z"/></svg> Android APK <span aria-hidden="true">·</span> တိုက်ရိုက်ဒေါင်းလုဒ်</p>
                        <a class="web-app-link" href="{{ url('/app') }}/">iOS / Web App အသုံးပြုရန် <span aria-hidden="true">↗</span></a>
                        @if (session('download_status'))
                            <p class="download-status" role="status">{{ session('download_status') }}</p>
                        @endif
                    </div>

                    <div class="hero-details" aria-label="App details">
                        <div><span class="detail-icon" aria-hidden="true">↓</span><span>အလွယ်တကူ<br><strong>ဒေါင်းလုဒ်ရယူပါ</strong></span></div>
                        <div><span class="detail-icon" aria-hidden="true">◎</span><span>Android ဖုန်းမှာ<br><strong>အသုံးပြုနိုင်ပါသည်</strong></span></div>
                    </div>
                </div>

                <div class="hero-art" aria-label="Master Gyi 2D 3D app illustration" role="img">
                    <div class="art-glow"></div>
                    <div class="orbit orbit-outer"></div>
                    <div class="orbit orbit-inner"></div>
                    <span class="spark spark-one">✦</span><span class="spark spark-two">✧</span><span class="spark spark-three">✦</span>
                    <span class="art-caption">A LITTLE LUCK. A NEW DAY.</span>
                    <div class="app-display">
                        <div class="display-top"><span class="status-dot"></span> MASTER GYI <span>✦</span></div>
                        <div class="app-emblem"><img src="{{ asset('img/master-gyi-logo.webp') }}" alt="" width="144" height="144"></div>
                        <span class="display-burmese">မာစတာကြီး</span>
                        <span class="display-subtitle">သင့်နေ့ရက်တိုင်းအတွက်</span>
                        <div class="display-divider"><span></span>✦<span></span></div>
                        <div class="display-draws"><div><strong>2D</strong><span>နှစ်လုံးထီ</span></div><span class="draw-divider"></span><div><strong>3D</strong><span>သုံးလုံးထီ</span></div></div>
                        <div class="display-bottom">MASTER GYI <span>ANDROID APP</span></div>
                    </div>
                    <div class="floating-badge badge-2d"><span>နှစ်လုံးထီ</span><strong>2D<span>✦</span></strong></div>
                    <div class="floating-badge badge-3d"><span>သုံးလုံးထီ</span><strong>3D<span>✧</span></strong></div>
                    <div class="art-bottom"><span></span> YOUR EVERYDAY COMPANION <span></span></div>
                </div>
            </section>
        </main>

        <footer class="site-footer"><p>© {{ date('Y') }} Master Gyi. All rights reserved.</p><span>မာစတာကြီး <span class="footer-star" aria-hidden="true">✦</span> 2D · 3D</span></footer>
    </div>
</body>
</html>
