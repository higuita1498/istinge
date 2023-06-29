<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class MasterReportesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        view()->share(['inicio' => 'empresa', 'seccion' => 'Reportes', 'title' => 'Reportes', 'icon' => 'fa fa-building']);
    }

    public function reportefactura()
    {
        view()->share(['title' => 'Reporte Facturas']);
        $facturas = DB::table('empresas')
            ->join('factura', 'factura.empresa', '=', 'empresas.id')
            ->select('empresas.nombre', DB::raw('COUNT(factura.id) as facturas'))
            ->groupBy('factura.empresa')
            ->orderBy('facturas', 'DESC')
            ->get();

        return view('master.reportes.facturas', compact('facturas'));
    }

    public function reporteremisiones()
    {
        view()->share(['title' => 'Reporte Remisiones']);
        $remisiones = DB::table('empresas')
            ->join('remisiones', 'remisiones.empresa', '=', 'empresas.id')
            ->select('empresas.nombre', DB::raw('COUNT(remisiones.id) as remisiones'))
            ->groupBy('remisiones.empresa')
            ->orderBy('remisiones', 'DESC')
            ->get();

        return view('master.reportes.remisiones', compact('remisiones'));
    }

    public function reportepagos()
    {
        view()->share(['title' => 'Reporte Pagos']);
        $pagos = DB::table('empresas')
            ->join('gastos', 'gastos.empresa', '=', 'empresas.id')
            ->select('empresas.nombre', DB::raw('COUNT(gastos.id) as gastos'))
            ->groupBy('gastos.empresa')
            ->orderBy('gastos', 'DESC')
            ->get();

        return view('master.reportes.pagos', compact('pagos'));
    }

    public function reporteproductos()
    {
        view()->share(['title' => 'Reporte Productos']);
        $productos = DB::table('empresas')
            ->join('inventario', 'inventario.empresa', '=', 'empresas.id')
            ->select('empresas.nombre', DB::raw('COUNT(inventario.id) as productos'))
            ->groupBy('inventario.empresa')
            ->orderBy('productos', 'DESC')
            ->get();

        return view('master.reportes.productos', compact('productos'));
    }
}
