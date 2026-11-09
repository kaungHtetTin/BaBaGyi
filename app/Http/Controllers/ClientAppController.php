<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientAppController extends Controller
{
    public function index(Request $request)
    {
        return response()->view('client', [
            'clientConfig' => ['version' => '1.0.0', 'base' => url('/app').'/', 'scope' => url('/app'), 'assets' => asset('client').'/',
                'api' => url('/app/api').'/', 'session' => url('/app/session'),
                'download' => route('app.download'), 'welcome' => route('index'),
                'guides' => ['topup' => url('/guide/top-up'), 'withdraw' => url('/guide/withdraw')]],
        ])->header('Cache-Control', 'no-cache');
    }

    public function manifest()
    {
        return response()->json([
            'id' => url('/app'), 'name' => 'MasterGyi', 'short_name' => 'MasterGyi',
            'description' => 'MasterGyi client app', 'lang' => 'my',
            'start_url' => url('/app'), 'scope' => url('/app'),
            'display' => 'standalone', 'background_color' => '#F5F6FA', 'theme_color' => '#FFFFFF',
            'icons' => [
                ['src' => asset('client/images/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('client/images/icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('client/images/icon-maskable-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json']);
    }

    public function worker()
    {
        $files = ['app.js', 'core.js', 'screens.js', 'betting.js', 'logic.js', 'app.css', 'android-resources.json',
            'images/mastergyi_logo.webp', 'images/mastergyi_avatar.webp', 'images/icon-192.png', 'images/icon-512.png', 'images/icon-maskable-512.png',
            'fonts/poppins_regular.woff2', 'fonts/poppins_medium.woff2', 'fonts/poppins_semibold.woff2', 'fonts/poppins_bold.woff2', 'fonts/Roboto-Regular.woff2', 'fonts/NotoSansMyanmarUI-Regular.woff2', 'fonts/NotoSansMyanmarUI-Bold.woff2'];
        foreach (glob(public_path('client/icons/*.svg')) as $file) $files[] = 'icons/'.basename($file);
        $urls = array_map(fn ($file) => asset('client/'.$file), $files);
        array_unshift($urls, url('/app'), url('/app').'/');
        $version = hash('sha256', implode('', array_map(fn ($file) => is_file(public_path('client/'.$file)) ? hash_file('sha256', public_path('client/'.$file)) : '', $files)));
        $script = str_replace(['__SHELL_URLS__', '__CACHE_VERSION__'], [json_encode($urls), substr($version, 0, 16)], file_get_contents(public_path('client/sw.js')));

        return response($script, 200, ['Content-Type' => 'application/javascript',
            'Cache-Control' => 'no-cache', 'Service-Worker-Allowed' => parse_url(url('/app'), PHP_URL_PATH)]);
    }
}
