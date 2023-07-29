<?php

namespace App;

use App\Model\Ingresos\Factura;
use App\Model\Ingresos\IngresosFactura;
use App\Model\Ingresos\ItemsFactura;

use App\Model\Ingresos\IngresoR;
use App\Model\Ingresos\Remision;
use App\Model\Ingresos\IngresosRemision;
use App\Model\Ingresos\ItemsRemision;

use Illuminate\Database\Eloquent\Model;
class Vendedor extends Model
{
    protected $table = "vendedores";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'nombre', 'identificacion', 'observaciones', 'empresa'
    ];

    public function usado()
    {
        return Factura::where('vendedor',$this->id)->count();
        
    }

    public function pagosFecha($inicio, $fin)
    {
        $facturas = Factura::where('vendedor', $this->id)
            ->where('fecha', '>=', $inicio)
            ->where('fecha', '<=', $fin)
            ->where('tipo','<>',2)
            ->where('estatus','<>',2)
            ->get();
        $ingresos = 0;
        foreach ($facturas as $factura){
            $ingresos += $factura->pagado();
        }

        return $ingresos;
    }

    public function nroFacturas($inicio, $fin)
    {
        $facturas = Factura::where('vendedor', $this->id)
            ->where('fecha', '>=', $inicio)
            ->where('fecha', '<=', $fin)
            ->where('tipo','<>',2)
            ->where('estatus','<>',2)
            ->get();

        return count($facturas);
    }

    public function montoTotal($inicio, $fin)
    {
        $facturas = Factura::where('vendedor', $this->id)
            ->where('fecha', '>=', $inicio)
            ->where('fecha', '<=', $fin)
            ->where('tipo','<>',2)
            ->where('estatus','<>',2)
            ->get();

        $monto = array(
            'subtotal'      => 0,
            'total'         => 0,
        );
       foreach ($facturas as $factura){
            $monto['subtotal'] += $factura->total()->subsub;
            $monto['total'] += $factura->total()->total;
        }

        return $monto;
    }


    /*
    * CLASES PARA BUSCAR REMISIONES POR VENDEDOR
    * */

    public function nroRemisiones($inicio, $fin)
    {
        $remisiones = Remision::where('vendedor', $this->id)
            ->where('fecha', '>=', $inicio)
            ->where('fecha', '<=', $fin)
            ->where('estatus', '<>', 2)
            ->get();

        return count($remisiones);
    }

    public function pagosFechaR($inicio, $fin){

        $remisiones = Remision::where('vendedor', $this->id)
            ->where('fecha', '>=', $inicio)
            ->where('fecha', '<=', $fin)
            ->where('estatus', '<>', 2)
            ->get();
        $ingresos = 0;
        foreach ($remisiones as $remision){
            $ingresos += $remision->pagado();
        }
        return $ingresos;
    }



    public function montoTotalR($inicio, $fin)
    {
        $remisiones = Remision::where('vendedor', $this->id)
            ->where('fecha', '>=', $inicio)
            ->where('fecha', '<=', $fin)
            ->where('estatus', '<>', 2)
            ->get();

        $monto = array(
            'subtotalR'      => 0,
            'totalR'         => 0,
        );
        foreach ($remisiones as $remision){
            
            $monto['subtotalR'] += $remision->total()->subsub;
            $monto['totalR'] += $remision->total()->total;
            
        }

        return $monto;
    }
}