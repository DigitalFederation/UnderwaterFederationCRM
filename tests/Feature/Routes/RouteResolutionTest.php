<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

function routeNameFor(string $method, string $uri): ?string
{
    return app('router')->getRoutes()->match(Request::create($uri, $method))->getName();
}

it('no longer registers subscription history routes that render missing views', function () {
    // Both actions rendered views that were never created, and the individual one was
    // unreachable behind /subscriptions/{subscription}. History is shown on the index.
    expect(Route::has('individual.subscriptions.history'))->toBeFalse()
        ->and(Route::has('entity.individual-memberships.history'))->toBeFalse()
        ->and(routeNameFor('GET', '/individual/subscriptions/123'))->toBe('individual.subscriptions.show');
});

it('does not register an entity event-application create route without a controller method', function () {
    // Entities create applications from a template or directly, like federations do.
    expect(Route::has('entity.event-applications.create'))->toBeFalse()
        ->and(Route::has('entity.event-applications.create-direct'))->toBeTrue()
        ->and(Route::has('entity.event-applications.create-from-template'))->toBeTrue();
});

it('no longer registers the stale entity diving-licence invitations page', function () {
    // The page rendered a view written for an invitation model that does not exist;
    // technical director invitations are managed on the licence directors page.
    expect(Route::has('entity.diving_licenses.invitations'))->toBeFalse()
        ->and(Route::has('entity.diving_licenses.cancel_invitation'))->toBeFalse();
});

it('does not register unlinked routes whose views were never created', function (string $name) {
    expect(Route::has($name))->toBeFalse();
})->with([
    'entity.individual-memberships.preview',
    'entity.individual-memberships.process',
    'entity.individual-memberships.show',
    'entity.evt-events.events.officials-enrollment.create',
    'entity.evt-events.events.officials-enrollment.store',
    'admin.route-permissions.preview',
    'admin.zones.manage-districts',
    'admin.zones.update-districts',
    'admin.evt-events.attributes.show',
    'admin.evt-events.events.attributes.show',
    'admin.evt-events.disciplines.show',
]);

it('keeps the routes that sit next to the removed ones', function (string $name) {
    expect(Route::has($name))->toBeTrue();
})->with([
    'entity.individual-memberships.index',
    'entity.evt-events.events.officials-enrollment.index',
    'entity.evt-events.events.officials-enrollment.destroy',
    'admin.route-permissions.index',
    'admin.zones.edit',
    'admin.evt-events.attributes.edit',
    'admin.evt-events.events.attributes.edit',
    'admin.evt-events.disciplines.edit',
]);
