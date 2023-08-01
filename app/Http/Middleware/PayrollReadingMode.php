<?php

namespace App\Http\Middleware;

use Closure;

class PayrollReadingMode
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
        $modoLecturaNomina = (object) $request->user()->modoLecturaNomina();
        if ($modoLecturaNomina->success) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Acción no permitida en modo lectura, adquiere un plan para continuar']);
            }
            return back()->with('error', 'Acción no permitida en modo lectura, adquiere un plan para continuar');
        }
        return $next($request);
    }
}
