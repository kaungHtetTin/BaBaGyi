<?php

namespace App\Http\Controllers;

use App\Models\MobileVersion;
use Illuminate\Support\Facades\Storage;

class WelcomeController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function download()
    {
        $version = MobileVersion::orderByDesc('id')->first();
        $path = $version?->url;

        // Only serve APK files from the existing public release directory.
        if (! is_string($path) || ! preg_match('~\Aapks/[^/\\\\\x00]+\.apk\z~i', $path)
            || ! Storage::disk('public')->exists($path)) {
            return redirect()->route('index')->with('download_status',
                'ဆော့ဝဲကို ပြင်ဆင်နေပါသည်။ ခဏအကြာတွင် ပြန်လည်ဒေါင်းလုဒ်လုပ်ပေးပါ။');
        }

        return Storage::disk('public')->download($path, 'mastergyi.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
}
