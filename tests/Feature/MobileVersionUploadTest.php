<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MobileVersionUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array', 'session.driver' => 'array']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        Storage::fake('public');
        $this->actingAs(User::forceCreate([
            'name' => 'Release Admin', 'email' => 'release@example.com',
            'phone' => '09123456789', 'password' => 'unused', 'admin' => true,
        ]));
    }

    private function release(array $overrides = []): array
    {
        return array_replace([
            'version_code' => 111, 'version_name' => '1.1.1', 'min_android_version' => 21,
            'anroid_apk_file' => UploadedFile::fake()->createWithContent('release.apk', 'test-apk-content'),
        ], $overrides);
    }

    public function test_first_upload_creates_the_apk_directory_and_can_be_downloaded(): void
    {
        $this->assertFalse(Storage::disk('public')->exists('apks'));
        $this->from('/admin/mobile-versions/add')->post('/admin/mobile-versions/add', $this->release())
            ->assertRedirect('/admin/mobile-versions/add')->assertSessionHasNoErrors()->assertSessionHas('msg');

        Storage::disk('public')->assertExists('apks/mastergyi-1.1.1.apk');
        $this->assertDatabaseHas('mobile_versions', [
            'version_code' => 111, 'version_name' => '1.1.1', 'url' => 'apks/mastergyi-1.1.1.apk',
        ]);
        $response = $this->get('/download')->assertOk()->assertDownload('mastergyi.apk');
        $this->assertSame('test-apk-content', $response->streamedContent());
    }

    public function test_version_name_cannot_escape_the_apk_directory(): void
    {
        $this->post('/admin/mobile-versions/add', $this->release(['version_name' => '../../outside']))
            ->assertSessionHasErrors('version_name');
        $this->assertDatabaseCount('mobile_versions', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_non_apk_upload_is_rejected(): void
    {
        $this->post('/admin/mobile-versions/add', $this->release([
            'anroid_apk_file' => UploadedFile::fake()->create('release.php', 1),
        ]))->assertSessionHasErrors('anroid_apk_file');
        $this->assertDatabaseCount('mobile_versions', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_text_instead_of_an_uploaded_file_is_rejected(): void
    {
        $this->post('/admin/mobile-versions/add', $this->release(['anroid_apk_file' => 'release.apk']))
            ->assertSessionHasErrors('anroid_apk_file');
        $this->assertDatabaseCount('mobile_versions', 0);
    }
}
