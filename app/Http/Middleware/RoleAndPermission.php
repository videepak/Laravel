<?php

namespace App\Http\Middleware;

use Closure;

class RoleAndPermission
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$permission)
    {

        $user = \Auth::user();

        if (!$user->can($permission))
        {
            return redirect('unauthorized');
        }

        return $next($request);
    }
}
