<?php

namespace Tests\Feature;

use App\Http\Controllers\LayoutController;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminLoginRedirectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array', 'session.driver' => 'array']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
    }

    private function user(bool $admin): User
    {
        return User::forceCreate(['name' => 'Login Test', 'email' => 'login@example.com',
            'phone' => '09123456789', 'password' => Hash::make('secret123'), 'admin' => $admin]);
    }

    public function test_successful_admin_login_redirects_to_dashboard(): void
    {
        $user = $this->user(true);
        $this->withSession(['url.intended' => url('/')])
            ->post('/admin/login', ['email' => $user->email, 'password' => 'secret123'])
            ->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertSame(LayoutController::class.'@index',
            Route::getRoutes()->getByName('admin.index')->getActionName());
    }

    public function test_old_admin_url_redirects_to_dashboard(): void
    {
        $this->actingAs($this->user(true))->get('/admin')->assertRedirect('/admin/dashboard');
    }

    public function test_invalid_admin_login_stays_unauthenticated(): void
    {
        $user = $this->user(true);
        $this->from('/admin/login')->post('/admin/login', [
            'email' => $user->email, 'password' => 'incorrect',
        ])->assertRedirect('/admin/login')->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_non_admin_web_login_preserves_existing_destination(): void
    {
        $user = $this->user(false);
        $this->post('/login', ['email' => $user->email, 'password' => 'secret123'])
            ->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }
}
