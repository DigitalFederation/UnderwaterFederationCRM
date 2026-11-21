<?php

use App\Models\Group;
use App\Models\User;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\Models\MembershipPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
    $this->artisan('db:seed --class=UserGroupSeeder');
    $this->artisan('db:seed --class=CommitteeSeeder');

    $this->admin = User::factory()->create([
        'email' => 'membership-admin@example.test',
        'group_id' => Group::where('code', 'ADMIN')->value('id'),
        'active' => true,
    ]);
    $this->admin->assignRole('admin');
});

/**
 * The show page eager-loaded `plans.products`, a relation MembershipPlan has
 * never had, so any membership with at least one plan failed to render.
 */
test('the admin membership page renders a membership that has plans', function () {
    $membership = Membership::factory()->create();
    $membership->plans()->attach(MembershipPlan::factory()->create());

    $this->actingAs($this->admin)
        ->get(route('admin.membership.show', $membership))
        ->assertOk();
});
