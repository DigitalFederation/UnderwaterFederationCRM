<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use Domain\Individuals\Models\Individual;
use Illuminate\Contracts\View\View;

class EntityIndividualSubscriptionController extends Controller
{
    /**
     * Display the interface for managing individual member subscriptions.
     * This is where entities can subscribe their members to packages.
     */
    public function index(): View
    {
        return view('web.entity.individual-memberships.index');
    }
}
