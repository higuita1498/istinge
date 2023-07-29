<?php

namespace App;

use App\Model\Ingresos\Factura;
use App\Model\Ingresos\Ingreso;
use App\Model\Ingresos\IngresosCategoria;
use App\Model\Ingresos\ItemsFactura;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Suscripcion extends Model
{

    protected $table = "suscripciones";
    protected $primaryKey = 'id';

    protected $fillable = [
        'fec_vencimiento','prorroga','fec_inicio','id_empresa','created_at','updated_at',
        'fec_corte'
    ];

    public function empresa(){
        $empresa = Empresa::where('id',$this->id_empresa)->first();

        return $empresa;
    }
    
    public function ilimitado()
    {
        return $this->ilimitado ? 'Desactivar plan ilimitado' : 'Activar plan ilimitado';
    }

    /*
     * Verificar y restringir por cantidad de facturas para el usuario sin suscripciÃ³n (gratis)
     */
    public function facturasHechas(){
        $suscripcion    = Suscripcion::where('id_empresa', Auth::user()->empresa)->get()->last();
        $fechaAnt       = (Carbon::parse($suscripcion->fec_corte))->subMonth();
        $fechaCorte     = Carbon::parse($suscripcion->fec_corte);

        $facturas       = Factura::where('created_at', '>=', $fechaAnt)
            ->where('created_at', '<=', $fechaCorte)
            ->where('empresa', Auth::user()->empresa)
            ->where('tipo', 1)
            ->get();
        $facturas       = count($facturas);
    
        if ($this->unlimited() || $suscripcion->ilimitado){
            return false;
        }
        if(is_array($this->checkPlan())){
            return $this->verifyLimitsPersonalPlan($facturas, 'facturas');
        }
        return ($facturas >= 15) ? true : false;

    }

    private function unlimited(){

        $suscripcionFree    = Suscripcion::where('id_empresa', Auth::user()->empresa)->get()->last();
        $suscripcionFree    = Carbon::parse($suscripcionFree->created_at);

        return Carbon::now()->diffInMonths($suscripcionFree) >= 1 ? false : true;
    }

    public function numeroFacturas(){
        $suscripcion    = Suscripcion::where('id_empresa', Auth::user()->empresa)->get()->last();
        $fechaAnt       = (Carbon::parse($suscripcion->fec_corte))->subMonth();
        $fechaCorte     = Carbon::parse($suscripcion->fec_corte);

        $facturas       = Factura::where('created_at', '>=', $fechaAnt)
            ->where('created_at', '<=', $fechaCorte)
            ->where('tipo', 1)
            ->where('empresa', Auth::user()->empresa)
            ->get();
        return  count($facturas);
    }


    public function ingresos(){
        $suscripcion    = Suscripcion::where('id_empresa', Auth::user()->empresa)->get()->last();
        $fechaAnt       = (Carbon::parse($suscripcion->fec_corte))->subMonth();
                    $fechaAnt       = Carbon::parse($suscripcion->fec_inicio);
        $fechaCorte     = Carbon::parse($suscripcion->fec_corte);

        $dates['inicio'] = $fechaAnt;
        $dates['fin']    = $fechaCorte;

        //Se obtienen todas las facturas dentro de la fecha correspondinete
        $itemsFacturas = ItemsFactura::select('*')
            ->whereIn('factura', function($query) use ($dates){
                $query->select('id')
                    ->from(with(new Factura)->getTable())
                    ->where('fecha', '<=', $dates['fin'])
                    ->where('fecha', '>=', $dates['inicio'])
                    ->where('empresa', Auth::user()->empresa);
            })->get();
        $ingresosItem = IngresosCategoria::select('*')
            ->whereIn('ingreso', function ($query) use ($dates){
                $query->select('id')
                    ->from(with(new Ingreso)->getTable())
                    ->where('fecha', '<=', $dates['fin'])
                    ->where('fecha', '>=', $dates['inicio'])
                    ->where('empresa', Auth::user()->empresa);
            })->get();

        $categoriaGanancia = array();
        $categoriaGanancia ['ingresos'] = 0;
        //Se filtra por tipo de item y se agrupan su total por categoria
        foreach ($itemsFacturas as $itemsFactura){
            if($itemsFactura->tipo_inventario == 1){
                $categoria = $itemsFactura->productoTotal()->categoriaId();
                if($categoria){
                    if(!isset($categoriaGanancia[$categoria->id])){
                        $categoriaGanancia[$categoria->id]['nombre']        = $categoria->nombre;
                        $categoriaGanancia[$categoria->id]['descripcion']   = $categoria->descripcion;
                        $categoriaGanancia[$categoria->id]['total']         = $itemsFactura->totalImp();
                        $categoriaGanancia ['ingresos']                     += $itemsFactura->totalImp();
                    }else{
                        $categoriaGanancia[$categoria->id]['total'] += $itemsFactura->totalImp();
                        $categoriaGanancia ['ingresos']             += $itemsFactura->totalImp();
                    }                    
                }

            }
        }

        if (count($ingresosItem) > 0)
        {
            foreach ($ingresosItem as $ingresoItem)
            {
                if(!isset($categoriaGanancia[$ingresoItem->categoria])){
                    $categoriaGanancia[$ingresoItem->categoria]['nombre']        = $ingresoItem->categoria(true);
                    $categoriaGanancia[$ingresoItem->categoria]['descripcion']   = $ingresoItem->categoria()->descripcion;
                    $categoriaGanancia[$ingresoItem->categoria]['total']         = $ingresoItem->pago();
                    $categoriaGanancia ['ingresos']                              += $ingresoItem->pago();
                }else{
                    $categoriaGanancia[$ingresoItem->categoria]['total']         += $ingresoItem->pago();
                    $categoriaGanancia ['ingresos']                              += $ingresoItem->pago();
                }
            }
        }
        return $categoriaGanancia;
    }

    /**
     * Verifica los limites del plan personalizado
     * -field: es el campo a comprobar (ej: facturas o ingresos)
     * -indexAssoc: es el campo a consultar dentro del plan personalizado, debe ser String (ej: 'facturas')
     * @param $field
     * @param String $indexAssoc
     * @return bool
     */
    public function verifyLimitsPersonalPlan($field, String $indexAssoc)
    {
        $checkPlan = $this->checkPlan();
        if(is_array($checkPlan)){
            return ($checkPlan['pago']) ? (($field > $checkPlan[$indexAssoc]) ? true : false) : false;
        }
    }

    /**
     * Verifica si la empresa posee un plan personalizado.
     * En caso de que sea asi, devuelve los datos relacionados al mismo.
     * @return array|bool
     */
    private function checkPlan()
    {
        $empresa = Empresa::find(Auth::user()->empresa);
        $plan = ($empresa->p_personalizado != 0) ? DB::table('planes_personalizados')->find($empresa->p_personalizado) : '' ;
        return ($empresa->p_personalizado == 0) ? true : array(
            'nombre' => $plan->nombre,
            'facturas' => $plan->facturas,
            'ingresos' => $plan->ingresos,
            'pago' => $this->payPersonalPlan()
        );

    }
    
    /**
     * Verificaci¨®n del pago de la suscripcion personalizada
     * @return bool
     */
    private function payPersonalPlan()
    {
        $empresa = Empresa::find(Auth::user()->empresa);
        $suscripcion = SuscripcionPago::where('id_empresa', $empresa)
            ->where('personalizado', 1)
            ->get();
        return count($suscripcion) > 0 ? true : false;
    }

}
