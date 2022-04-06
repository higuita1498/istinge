<?php

namespace App\Http\Middleware;

use Closure;

class CheckUserForPayroll
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (isset($request->user()->empresaObj->nomina) && $request->user()->empresaObj->nomina == 1) {
            return $next($request);
        }
        return redirect(route('home'));
    }
}
