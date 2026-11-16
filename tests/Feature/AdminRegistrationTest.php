<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminRegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array', 'session.driver' => 'array']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
    }

    public function test_admin_can_create_an_email_based_admin_without_a_phone(): void
    {
        $owner = User::forceCreate(['name' => 'Owner', 'email' => 'owner@example.com',
            'phone' => '09123456789', 'password' => Hash::make('secret123'), 'admin' => true]);

        $this->actingAs($owner)->post('/admin/register', [
            'name' => 'Office Admin',
            'email' => 'office@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect(route('admin.admins'))->assertSessionHasNoErrors();

        $admin = User::where('email', 'office@example.com')->firstOrFail();
        $this->assertEquals(1, $admin->admin);
        $this->assertSame('', $admin->phone);
        $this->assertTrue(Hash::check('secret123', $admin->password));
        $this->assertNotNull($admin->created_at);
        $this->assertNotNull($admin->updated_at);
        $this->assertAuthenticatedAs($owner);
        $this->get(route('admin.admins'))->assertOk()->assertSee('office@example.com');
    }
}
