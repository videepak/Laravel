<?php

namespace App\Http\Middleware;

use Closure;

class LogAfterRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $userId)
    {
        $request->attributes->add(["userId" => $userId]);
        return $next($request);
    }

    public function terminate($request, $response)
    {
        $url = $request->fullUrl();
        $ip = $request->ip();
        
        if (!is_null(\Request::get('userId')) && (\Request::get('userId') == 1659 || \Request::get('userId') == 223)) {
            \App\TrackViolation::create(
                [
                    'violation_request' => json_encode($request->all()),
                    'violation_response' => $response,
                    'url' => $url,
                    'user_id' => \Request::get('userId')
                ]
            );
        }
    }
}
