<?php

use Domain\Individuals\Models\IndividualEntity;
use Domain\Individuals\States\IndividualEntityState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('attaches individuals to entities with a valid membership state', function (string $seeder) {
    $this->artisan("db:seed --class={$seeder}");

    $rows = IndividualEntity::all();

    expect($rows)->not->toBeEmpty();
    $rows->each(fn (IndividualEntity $row) => expect($row->state)->toBeInstanceOf(IndividualEntityState::class));
    expect(DB::table('individual_entity')->whereNull('status_class')->orWhere('status_class', '')->count())->toBe(0);
})->with(['IndividualSeeder', 'EntitySeeder']);
