<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\RouteCollection;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
    $this->artisan('db:seed --class=UserGroupSeeder');

    $this->admin = User::factory()->create([
        'group_id' => Group::where('code', 'ADMIN')->first()->id,
        'active' => true,
    ]);
    $this->admin->assignRole('admin');
});

it('renders the payment methods page when the EasyPay webhook route is not registered', function () {
    // routes/api.php only registers the webhook route when EasyPay is enabled.
    // Rebuild the route collection without it to mirror a deployment with EasyPay off.
    $routes = new RouteCollection;
    foreach (app('router')->getRoutes() as $route) {
        if ($route->getName() !== 'api.payment.webhook.easypay') {
            $routes->add($route);
        }
    }
    app('router')->setRoutes($routes);

    $this->actingAs($this->admin)
        ->get(route('admin.payment-methods.index'))
        ->assertOk();
});
