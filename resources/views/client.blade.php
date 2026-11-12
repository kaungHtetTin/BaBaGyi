<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
    <meta name="theme-color" content="#FFFFFF" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#1B2535" media="(prefers-color-scheme: dark)">
    <meta name="description" content="MasterGyi client app">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>MasterGyi</title>
    <link rel="manifest" href="{{ route('client.manifest') }}">
    <link rel="icon" href="{{ asset('img/master-gyi-favicon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('client/images/icon-192.png') }}">
    @vite('public/client/app.css')
</head>
<body>
    <div id="client-root" aria-busy="true"><div class="boot"><img src="{{ asset('client/images/mastergyi_logo.webp') }}" alt="MasterGyi" width="80" height="80"><div class="spinner" role="status" aria-label="Loading"></div></div></div>
    <div id="dialog-root"></div><div id="toast-root" role="status" aria-live="polite"></div>
    <script>window.CLIENT_CONFIG = @json($clientConfig);</script>
    @vite('public/client/app.js')
    <noscript><p class="notice">MasterGyi requires JavaScript. Please enable JavaScript to use the app.</p></noscript>
</body>
</html>
