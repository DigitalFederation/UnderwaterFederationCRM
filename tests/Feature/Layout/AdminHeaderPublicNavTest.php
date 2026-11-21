<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
    $this->artisan('db:seed --class=UserGroupSeeder');

    $this->admin = User::factory()->create([
        'email' => 'header-admin@example.test',
        'group_id' => Group::where('code', 'ADMIN')->value('id'),
        'active' => true,
    ]);
    $this->admin->assignRole('admin');
});

/**
 * The header's centre navigation points at the public-facing registries and map.
 * They belong to the member/visitor experience, not the back office, where they
 * compete with the admin sidebar.
 */
test('the back office header does not show the public content navigation', function () {
    $html = $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->getContent();

    foreach ([
        route('public.map.locations'),
        route('public.club-registry'),
        route('public.coach-registry'),
        route('public.technical-official-registry'),
        route('public.diving-service-providers'),
        route('public.diving-professionals'),
    ] as $publicUrl) {
        expect($html)->not->toContain('href="' . $publicUrl . '"');
    }
});

test('the back office header keeps its own controls', function () {
    $html = $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->getContent();

    // Section title and the portal name must survive the change.
    expect($html)
        ->toContain(e(__('role_management.protection_levels.admin')))
        ->toContain(e(config('branding.primary.portal_name')));
});

test('member facing portals still expose the public content navigation', function () {
    $this->actingAs($this->admin);

    // The header decides on the first URL segment, so render it directly for a
    // non-admin segment rather than depending on member-portal route permissions.
    app()->instance('request', Request::create('/entity/dashboard', 'GET'));

    $html = view('components.layout.header')->render();

    expect($html)
        ->toContain(route('public.map.locations'))
        ->toContain(route('public.club-registry'));
});

test('the back office renders that same header component without the nav', function () {
    $this->actingAs($this->admin);

    app()->instance('request', Request::create('/admin/dashboard', 'GET'));

    $html = view('components.layout.header')->render();

    expect($html)
        ->not->toContain(route('public.club-registry'))
        ->toContain('Administrador');
});

/**
 * The header used to hardcode Portuguese ("Portal Subaquático", "Administrador"),
 * so every non-Portuguese installation showed them.
 */
test('the header shows the configured portal name and translated section title', function () {
    config(['branding.primary.portal_name' => 'Example Portal Name']);
    app()->setLocale('en');

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Example Portal Name')
        ->assertSee(__('role_management.protection_levels.admin'))
        ->assertDontSee('Portal Subaquático')
        ->assertDontSee('Administrador');
});
