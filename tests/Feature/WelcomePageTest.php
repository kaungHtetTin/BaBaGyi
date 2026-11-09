<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        Schema::create('mobile_versions', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->timestamps();
        });
        Storage::fake('public');
    }

    public function test_guests_see_the_welcome_page_without_an_admin_redirect(): void
    {
        $this->get('/')->assertOk()->assertViewIs('welcome')
            ->assertSee('Master Gyi')->assertSee('မာစတာကြီး')->assertDontSee('ဘဘကြီး')
            ->assertSee(route('app.download'), false);
        $this->get('/admin/login')->assertOk();
    }

    public function test_download_serves_the_latest_published_apk(): void
    {
        DB::table('mobile_versions')->insert([
            ['id' => 1, 'url' => 'apks/babagyi-old.apk'],
            ['id' => 2, 'url' => 'apks/babagyi-new.apk'],
        ]);
        Storage::disk('public')->put('apks/babagyi-old.apk', 'old-release');
        Storage::disk('public')->put('apks/babagyi-new.apk', 'latest-release');

        $response = $this->get('/download')->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.android.package-archive')
            ->assertDownload('mastergyi.apk');
        $this->assertSame('latest-release', $response->streamedContent());
    }

    public function test_an_unpublished_app_shows_a_message_on_the_same_page(): void
    {
        $this->get('/download')->assertRedirect(route('index'))->assertSessionHas('download_status');
        $this->get('/')->assertOk()->assertSee('role="status"', false);
    }

    public function test_a_missing_latest_file_does_not_download_an_old_release(): void
    {
        DB::table('mobile_versions')->insert([
            ['id' => 1, 'url' => 'apks/old.apk'], ['id' => 2, 'url' => 'apks/missing.apk'],
        ]);
        Storage::disk('public')->put('apks/old.apk', 'outdated-release');

        $this->get('/download')->assertRedirect(route('index'))->assertSessionHas('download_status');
    }

    /** @dataProvider invalidReleasePaths */
    public function test_download_cannot_serve_files_outside_the_apk_directory(string $path): void
    {
        DB::table('mobile_versions')->insert(['url' => $path]);
        Storage::disk('public')->put('private.apk', 'private-file');
        Storage::disk('public')->put('apks/notes.txt', 'not-an-apk');

        $this->get('/download')->assertRedirect(route('index'))->assertSessionHas('download_status');
    }

    public static function invalidReleasePaths(): array
    {
        return [
            ['apks/../private.apk'], ['apks/..\\private.apk'],
            ['private.apk'], ['apks/notes.txt'], ['https://example.com/app.apk'],
        ];
    }
}
