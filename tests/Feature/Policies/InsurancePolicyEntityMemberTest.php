<?php

use App\Enums\UserGroupEnum;
use App\Models\User;
use App\Policies\InsurancePolicy;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\PendingIndividualEntityState;
use Domain\Insurance\Models\Insurance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    // viewDocument() checks this permission first; it must exist for hasPermissionTo().
    Permission::findOrCreate('access memberships', 'web');

    $this->entity = Entity::factory()->create();
    $this->entityUser = User::factory()->create(['group_id' => UserGroupEnum::ENTITY->value]);
    $this->entityUser->entities()->attach($this->entity);

    $this->member = Individual::factory()->create();
    $this->insurance = Insurance::factory()->create([
        'member_type' => 'individual',
        'member_id' => $this->member->id,
    ]);
});

it('lets an entity user view the insurance document of an active member of their entity', function () {
    $this->member->entities()->attach($this->entity, ['status_class' => ActiveIndividualEntityState::class]);

    expect((new InsurancePolicy)->viewDocument($this->entityUser, $this->insurance))->toBeTrue();
});

it('denies an entity user when the individual is only pending at their entity', function () {
    $this->member->entities()->attach($this->entity, ['status_class' => PendingIndividualEntityState::class]);

    expect((new InsurancePolicy)->viewDocument($this->entityUser, $this->insurance))->toBeFalse();
});

it('denies an entity user when the individual is active at a different entity', function () {
    $this->member->entities()->attach(Entity::factory()->create(), ['status_class' => ActiveIndividualEntityState::class]);

    expect((new InsurancePolicy)->viewDocument($this->entityUser, $this->insurance))->toBeFalse();
});
