<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class Roles
{
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, Closure $next)
    {
        $user    = $this->auth->user();
        $action   = $this->getAction($request);
        $collection   = $this->getCollection($request);

        if (!$user->is_admin && $user->cando($collection, $action) == 0)
        {
            return response('', 403);
        }

        return $next($request);
    }

    private function getAction($request)
    {
        return $request->route()->getAction()['action'];
    }

    private function getCollection($request)
    {
        return $request->route()->getAction()['collection'];
    }
}
