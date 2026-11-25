<?php

use App\Http\Middleware\EnsureIsMainFederation;
use App\Models\User;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

function runEnsureIsMainFederation(User $user): mixed
{
    test()->actingAs($user);

    return (new EnsureIsMainFederation)->handle(Request::create('/federation/local-membership-plan'), fn () => 'passed');
}

it('lets a user of a main federation through', function () {
    $user = User::factory()->create();
    $user->federations()->attach(Federation::factory()->create(['is_local' => false]));

    expect(runEnsureIsMainFederation($user))->toBe('passed');
});

it('responds 403 rather than a server error for a user of a local federation only', function () {
    $user = User::factory()->create();
    $user->federations()->attach(Federation::factory()->create(['is_local' => true]));

    try {
        runEnsureIsMainFederation($user);
        $this->fail('Expected the middleware to reject the request.');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(403);
    }
});
