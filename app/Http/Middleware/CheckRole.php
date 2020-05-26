<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if($role == 'drivers')
        {
            // 1 = drivers
            if ($request->user()->user_type == 1) {
                return $next($request);
            }
            return response()->json(['message' => 'This action is unauthorized.'],401);
        }
        elseif($role == 'riders')
        {
            // 0 = riders
            if ($request->user()->user_type == 0) {
                return $next($request);
            }
            return response()->json(['message' => 'This action is unauthorized.'],401);

        }
        else
        {
            return response()->json(['message' => 'This action is unauthorized.'],401);
        }
    }
}
