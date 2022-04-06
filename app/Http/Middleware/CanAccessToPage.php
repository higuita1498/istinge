<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Support\Facades\Auth;

class CanAccessToPage
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $idPermiso)
    {

        app(Controller::class)->getAllPermissions(Auth::user()->id);

        if (isset($_SESSION['permisos'][$idPermiso])) {
            return $next($request);
        }



        return redirect()->back()->with('cannot-access-module',
            'La ruta a la que intentas acceder no existe o no tienes los permisos suficientes')->withInput();


    }
}
