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


class SuscripcionPago extends Model
{
    //
    protected $table = "suscripciones_pagos";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_empresa','plan','tipo_pago','referencia','meses','estado','monto','suplentepago_id','medio_pago',
    ];

    public function empresa(){
        $empresa = Empresa::where('id',$this->id_empresa)->first();

        return $empresa;
    }

    public function plan(){
        $plan='';
        if($this->personalizado == 1){
            $plan = DB::table('planes_personalizados')->find($this->plan);
            return $plan->nombre;
        }
        switch ($this->plan){
            case 2:
                $plan = "Plan Emprendedor";
                break;
            case 1:
                $plan = "Plan Pyme";
                break;
            case 3:
                $plan = "Plan Avanzado";
                break;
        }

        return $plan;
    }

    public function tipoPago(){
        $tipoPago='';

        if($this->tipo_pago == 1){
            $tipoPago = 'Transferencia';
        }elseif($this->tipo_pago == 2){
            $tipoPago = 'Paypal';
        }else{
            $tipoPago = 'Payuu';
        }

        return $tipoPago;
    }

    public function estado(){
        $estado = '';

        if($this->estado == 0){
            $estado = 'No hay pago';
        }else if($this->estado ==1){
            $estado = 'Aprobado';
        }else if($this->estado == 2)
        {
            $estado = 'Pendiente';
        }else if($this->estado == 3)
        {
            $estado = 'Rechazado';
        }else if ($this->estado == 4)
        {
            $estado = 'Expirado';
        }else if ($this->estado == 5)
        {
            $estado = 'Error';
        }

        return $estado;
    }

    private function ingresos() {

        $this->actualizarFecha();
        $suscripcion    = Suscripcion::where('id_empresa', Auth::user()->empresa)->get()->last();
        $empresa = Auth::user()->empresa();
        $fechaAnt       = Carbon::parse($this->created_at);
        if($empresa->subscriptions() != false)
            $fechaAnt = $empresa->subscriptions()->last()->created_at;

        $fechaCorte     = Carbon::parse($suscripcion->fec_corte);

        $dates['inicio'] = $fechaAnt;
        $dates['fin']    = $fechaCorte;

        //Se obtienen todas las facturas dentro de la fecha correspondinete
        $itemsFacturas = ItemsFactura::select('*')
            ->whereIn('factura', function($query) use ($dates){
                $query->select('id')
                    ->from(with(new Factura)->getTable())
                    ->where('created_at', '<=', $dates['fin'])
                    ->where('created_at', '>=', $dates['inicio'])
                    ->where('tipo', 1)
                    ->where('empresa', Auth::user()->empresa);
            })->get();
            

        $ingresosItem = IngresosCategoria::select('*')
            ->whereIn('ingreso', function ($query) use ($dates){
                $query->select('id')
                    ->from(with(new Ingreso)->getTable())
                    ->where('created_at', '<=', $dates['fin'])
                    ->where('created_at', '>=', $dates['inicio'])
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


    public function facturasHechas($multi = false){

        $this->actualizarFecha();
        $suscripcion    = Suscripcion::where('id_empresa', Auth::user()->empresa)->get()->last();
        $fechaAnt       = Carbon::parse($this->created_at);
        $empresa = Auth::user()->empresa();
        if($empresa->subscriptions() != false){
            $fechaAnt = $empresa->subscriptions()->last()->created_at;
            $multi = true;
        }
            
        $fechaCorte     = Carbon::parse($suscripcion->fec_corte);

        $facturas       = Factura::where('created_at', '>=', $fechaAnt)
            ->where('created_at', '<=', $fechaCorte)
            ->where('tipo', 1)
            ->where('empresa', Auth::user()->empresa)
            ->get();
        $facturas       = count($facturas);
        $tmpSuscripcion = $suscripcion;
        $suscripcion    = $this->plan;
        $freeTest       = false;
        $empresa = Empresa::find(Auth::user()->empresa);

        if ($tmpSuscripcion->ilimitado){
            return false;
        }

        if (!$empresa->subscriptions()){
            if(is_array($this->checkPlan()))
                return $this->verifyLimitsPersonalPlan($facturas, 'facturas');
        }
        return $multi ? $facturas >= $this->multFacturas() : $facturas >= $this->cantidadFacturas($suscripcion, $freeTest);

    }

    /*
     * Este metodo hay que adecuarlo a los planes actuales
     */
    private function cantidadFacturas($plan, $freeTest)
    {
        if ($this->personalizado)
            return $this->checkPlan()['facturas'];
        if(!$freeTest){
            switch ($plan){
                case 1:
                    return 500;
                case 2:
                    return 100;
                case 3:
                    return 1000;
                default:
                    100;
            }

            return 10;
        }

        return 10;

    }

    public function ingresosLimit(){

        $ingresos       = $this->ingresos()['ingresos'];
        $limites        = $this->limites();

        if (Auth::user()->empresa == 1){
            return false;
        }

        
        if (Auth::user()->empresa()->subscriptions() != false){
            return ($ingresos >= $this->multIngresos());
        }
        
        return ($ingresos >= $limites) ? true : false;
    }

    public function restanteIngresos($multi = false){
        $ingresos       = $this->ingresos()['ingresos'];
        $limites        = $multi ? $this->multIngresos() : $this->limites();

        return $limites - $ingresos;
    }

    private function limites() {
        $plan       = $this->plan;
        if ($this->personalizado)
            return $this->checkPlan()['ingresos'];
        switch ($plan){
            case 1:
                return 35000000;
            case 2:
                return 10000000;
            case 3:
                return 100000000;
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
     * Verifica los limites de los planes asociados a la cuenta
     * @return int
     */
    private function multIngresos()
    {
        $empresa = Empresa::find(Auth::user()->empresa);
        $suscriptions = $empresa->subscriptions();
        $ingresos = 0;
        foreach ($suscriptions as $suscription) {
            $ingresos += $suscription->limites();
        }
        return $ingresos;
    }

    private function multFacturas()
    {
        $empresa = Empresa::find(Auth::user()->empresa);
        $subscriptions = $empresa->subscriptions();
        $facturas = 0;
        foreach ($subscriptions as $subscription) {
            $facturas += $subscription->cantidadFacturas($subscription->plan, false);
        }
        return $facturas;
    }

    public function numeroFacturas(){

        $this->actualizarFecha();
        $suscripcion    = Suscripcion::where('id_empresa', Auth::user()->empresa)->get()->last();
        $fechaAnt       = Carbon::parse($this->created_at);
        if(Auth::user()->empresa()->subscriptions() != false)
            $fechaAnt = Auth::user()->empresa()->subscriptions()->last()->created_at;
        $fechaCorte     = Carbon::parse($suscripcion->fec_corte);

        $facturas       = Factura::where('created_at', '>=', $fechaAnt)
            ->where('created_at', '<=', $fechaCorte)
            ->where('empresa', Auth::user()->empresa)
            ->where('tipo', 1)
            ->get();

        return count($facturas);

    }

    private function actualizarFecha(){
        $suscripcion            = Suscripcion::where('id_empresa', Auth::user()->empresa)->get()->last();
        $fechaActual            = Carbon::now();
        $fechaCorte             = Carbon::parse($suscripcion->fec_corte);
        while ($fechaActual->greaterThanOrEqualTo($fechaCorte)){
            $fechaCorte->addMonth();
        }
        $suscripcion->fec_corte = $fechaCorte;
        $suscripcion->save();
    }


    public function rechazado(){
        $empresa = Empresa::find(Auth::user()->empresa);
        $planes = $empresa->subscriptions();
        return $planes != false ? ($planes->first()->estado != 1 && $planes->last()->estado != 1) : $this->estado != 1;
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
        if(is_array($checkPlan))
            return ($checkPlan['pago']) ? (($field >= $checkPlan[$indexAssoc]) ? true : false) : false;

    }

    /**
     * Verificación del pago de la suscripcion personalizada
     * @return bool
     */
    private function payPersonalPlan()
    {
        $suscripcion = SuscripcionPago::where('id_empresa', Auth::user()->empresa)
            ->where('personalizado', 1)
            ->get();
        return count($suscripcion) > 0 ? true : false;
    }

    /**
     * Retorna la fecha de vencimiento del pago de la suscripcion
     * @return Carbon
     */
    public function getExpirationAttribute()
    {
        if ($this->estado != 1)
            return $this->created_at;

        return Carbon::parse($this->created_at)->addMonth($this->meses);
    }

    /**
     * Determina si la suscripcion esta vigente o no
     * @return bool
     */
    public function getValidAttribute()
    {
        return !Carbon::now()->gte($this->expiration);
    }


}
