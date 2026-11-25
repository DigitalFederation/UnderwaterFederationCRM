<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIsMainFederation
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $userFederations = auth()->user()->federations;

        // Check if the user belongs to at least one main federation
        if ($userFederations->contains(function ($federation) {
            return ! $federation->is_local;
        })) {
            return $next($request);
        }

        abort(403);
    }
}
