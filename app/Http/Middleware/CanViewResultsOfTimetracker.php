<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class CanViewResultsOfTimetracker
{
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, Closure $next)
    {
        $user = $this->auth->user();

        if (!$user->can_view_results_of_timetracker)
        {
            return response('', 403);
        }

        return $next($request);
    }

}
