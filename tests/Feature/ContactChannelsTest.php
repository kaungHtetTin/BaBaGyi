<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ContactChannelsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
    }

    private function admin(): User
    {
        return User::forceCreate(['name' => 'Contact Admin', 'email' => 'contacts@example.com',
            'phone' => '09123456789', 'password' => bcrypt('secret123'), 'admin' => true]);
    }

    public function test_admin_can_create_edit_and_delete_launchable_contacts(): void
    {
        $this->actingAs($this->admin());
        $this->post('/admin/contacts', ['service' => 'Telegram', 'contact' => '@support_test'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $contact = Contact::firstOrFail();
        $this->assertSame('https://t.me/support_test', $contact->contact);
        $this->get('/admin/contacts')->assertOk()->assertSee('Edit contact');
        $this->put('/admin/contacts/'.$contact->id, ['service' => 'Viber', 'contact' => '+95 9123456789'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('viber://chat?number=%2B959123456789', $contact->fresh()->launch_url);
        $this->getJson('/api/contacts')->assertOk()
            ->assertJsonPath('0.launch_url', 'viber://chat?number=%2B959123456789');
        $this->getJson('/app/api/contacts')->assertOk()
            ->assertJsonPath('0.fallback_url', 'https://www.viber.com/en/download/');
        $this->delete('/admin/contacts/'.$contact->id)->assertRedirect();
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_invalid_platforms_and_unsafe_or_mismatched_links_are_rejected(): void
    {
        $this->actingAs($this->admin());
        foreach ([['Facebook', 'https://facebook.com/test'], ['Telegram', 'javascript:alert(1)'],
            ['Telegram', 'https://t.me.evil.example/test'], ['Viber', 'https://t.me/test'],
            ['Viber', '09123456789']] as [$service, $contact]) {
            $this->post('/admin/contacts', compact('service', 'contact'))->assertSessionHasErrors();
        }
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_legacy_contacts_remain_readable_and_only_valid_contacts_get_launch_links(): void
    {
        $legacy = Contact::forceCreate(['service' => 'Phone', 'contact' => '09123456789']);
        $this->assertNull($legacy->launch_url);
        $this->assertNull($legacy->fallback_url);
        $telegram = Contact::forceCreate(['service' => 'telegram', 'contact' => 'https://telegram.me/support_test']);
        $this->assertSame('https://t.me/support_test', $telegram->launch_url);
        $viber = Contact::forceCreate(['service' => 'Viber', 'contact' => 'https://invite.viber.com/?g2=test']);
        $this->assertSame($viber->contact, $viber->launch_url);
    }

    public function test_guests_cannot_mutate_contacts(): void
    {
        $this->post('/admin/contacts', ['service' => 'Telegram', 'contact' => '@support_test'])->assertRedirect();
        $this->put('/admin/contacts/1', ['service' => 'Viber', 'contact' => '+959123456789'])->assertRedirect();
        $this->delete('/admin/contacts/1')->assertRedirect();
        $this->assertDatabaseCount('contacts', 0);
    }
}
