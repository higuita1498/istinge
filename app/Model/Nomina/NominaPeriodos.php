<?php

namespace App\Model\Nomina;

use Illuminate\Database\Eloquent\Model;

use App\Model\Nomina\NominaDetalleUno;
use App\Model\Nomina\Nomina;
use App\Model\Nomina\Persona;
use Carbon\Carbon;
use DB;
use Auth;
use App\Traits\Funciones;

class NominaPeriodos extends Model
{
    use Funciones;

    protected $table = "ne_nomina_periodos";


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nro', 'periodo', 'nota', 'fk_idempresa', 'fk_idpersona', 'created_at', 'updated_at', 'fk_idnomina'
    ];


    protected $casts = [
        'fecha_desde' => 'datetime:Y-m-d H:00',
        'fecha_hasta' => 'datetime:Y-m-d H:00',
    ];

    public function persona(){
        return $this->belongsTo(Persona::class, 'fk_idpersona');
    }

    public function nomina(){
        return $this->belongsTo(Nomina::class,'fk_idnomina');
    }

    public function nominaDetallesUno()
    {
        return $this->hasMany(NominaDetalleUno::class, 'fk_nominaperiodo', 'id');
    }

    public function nominaCalculoFijos(){
        return $this->hasMany(NominaCalculoFijo::class, 'fk_nominaperiodo', 'id');
    }

    public function extras(){
        return NominaDetalleUno::where('fk_nominaperiodo', $this->id)->whereIn('fk_nomina_cuenta_tipo', [1,2,3])->sum('numero_horas');
    }

    public function vacaciones(){
        $detalles = NominaDetalleUno::where('fk_nominaperiodo', $this->id)->whereIn('fk_nomina_cuenta_tipo', [4,5,6])->get();
        $dias = 0;
        foreach ($detalles as $detalle) {
            if ($detalle->fecha_inicio) {
                $fechaEmision = Carbon::parse($detalle->fecha_inicio);
                $fechaExpiracion = Carbon::parse($detalle->fecha_fin);
                $dias += $fechaExpiracion->diffInDays($fechaEmision);
                $dias += $detalle->dias_compensados_dinero;
                if ($dias>=1) {
                    $dias += 1;
                }else{
                    $dias = 1;
                }
                $dias = $dias - $this->validar31($detalle->fecha_inicio, $detalle->fecha_fin);
            }
        }
        return ($dias);
    }

    public static function validar31($start_date,$end_date)
    {
        $start_date = Carbon::parse($start_date);
        $end_date = Carbon::parse($end_date);
        $diff = 0;

        for($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
            $day = $date->format('d');
            if($day == 31){
                $diff++;
            }
        }

        return $diff;
    }

    public function ingresos(){
        return NominaDetalleUno::where('fk_nominaperiodo', $this->id)->whereIn('fk_nomina_cuenta_tipo', [7,8,9])->sum('valor_categoria');
    }

    public function deducciones(){
        return NominaDetalleUno::where('fk_nominaperiodo', $this->id)->whereIn('fk_nomina_cuenta_tipo', [10,11,12])->sum('valor_categoria');
    }

    public function deduccionesObj(){
        return $this->hasMany(NominaDetalleUno::class, 'fk_nominaperiodo')->where('fk_nomina_cuenta', 4);
    }

    public function periodo(){
        $date = Carbon::create($this->year, $this->periodo, 1)->locale('es');
        return ucfirst($date->monthName).' '.$this->year;
    }

    public function tipo(){
        return NominaDetalleUno::where('fk_nominaperiodo', $this->id)->where('fk_nomina_cuenta_tipo', 4)->first()->nombre;
    }

    public function calculos_vacaciones($tipo = ''){
        $detalles = NominaDetalleUno::where('fk_nominaperiodo', $this->id)->where('fk_nomina_cuenta_tipo', 4)->get();
        $dias = 0;
        foreach ($detalles as $detalle) {
            if ($detalle->fecha_inicio) {
                $fechaEmision = Carbon::parse($detalle->fecha_inicio);
                $fechaExpiracion = Carbon::parse($detalle->fecha_fin);
                $dias += $fechaExpiracion->diffInDays($fechaEmision);
                $dias += $detalle->dias_compensados_dinero;
                if ($dias>1) {
                    $dias += 1;
                }
            }
        }
        return ($tipo == 'dias') ? $dias : ($dias * $this->valor_total) / 30;
    }


    /**
     * Retornar número de Dias trabajados sin calculos de las categorias (a excepcion de la fecha de contratación).
     *
     * @var array
     */
    public function diasTrabajados(){
        $inicio = new Carbon($this->fecha_desde);
        $hasta = new Carbon($this->fecha_hasta);

        if(!($persona = $this->persona)){
            $persona = $this->nomina->persona;
        }
        
        if($hasta->format('d') >= 27 && $hasta->format('d') < 30){
                $hasta->day = 30;
        }
        
        if($hasta->format('d') >= 31){
                $hasta->day = 30;
        }

        $fechaContratacion = new Carbon($persona->fecha_contratacion);

        $diasRestados = 0;
        //CONTRATO VIGENTE ACTUAL.
        if($inicio->format('y') == $fechaContratacion->format('y')){
            if($inicio->format('m') == $fechaContratacion->format('m')){
                if(intval($fechaContratacion->format('d')) <= intval($hasta->format('d'))){
                    if(intval($fechaContratacion->format('d')) >= intval($inicio->format('d'))){
                        /* >>> a los dias restados no se le suma +1 por que el dia que entra a trabajar empieza el pago. <<< */
                        $diasRestados = $inicio->diffInDays($fechaContratacion);
                    }
                }
            }
        }
        
        
        //CONTRATOS ANTERIORES O LIQUIDADOS EL ONTRATO ACTUAL DE LA PERSONA NO SE LISTA ACÀ
        if($persona->contratos->count() > 0){
            foreach($persona->contratos as $co){
                $fechaTerminacion = $co->comprobanteLiquidacion->fecha_terminacion;
                $fechaTerminacion = new Carbon($fechaTerminacion);
                
                if($inicio->format('y') == $fechaTerminacion->format('y')){
                    if($inicio->format('m') == $fechaTerminacion->format('m')){
                        if(intval($fechaTerminacion->format('d')) <= intval($hasta->format('d'))){
                                if($fechaContratacion->format('y') == $fechaTerminacion->format('y')){
                                    if($fechaContratacion->format('m') == $fechaTerminacion->format('m')){
                                        
                                        //AQUI ENTRA CUANDO SE ECHO EL MISMO MES EL MISMO AÑO Y SE CONTRATO EL MISMO MES Y EL MISMO AÑO
                                        //NO SE HACE NADA PORQUE LOS ANTERIORES CONTRATOS SE LIQUIDAN JUNTO AL COMPROBANTE DE LIQUIDACION POR ENDE NO SE TOMAN EN CUENTA EN ESTE PERIODO.
                                        if($fechaTerminacion->format('d') >= 1){
                                            // $diasRestados -= $inicio->diffInDays($fechaTerminacion) + 1;   
                                             
                                             // 
                                        }
                                        
                                        //continua por que no es igual el año de contratacion y terminqcion y no es igual el mes de contratcion y liquidacion
                                        continue;
                                    }
                                }
                                // AQUI ENTRA CUANDO LA PERSONA SE LIQUIDO Y NO SE VOLVIO A CONTRATAR EN EL PRESENTE MES O AÑO
                              $diasRestados += $fechaTerminacion->diffInDays($hasta);
                        }
                    }
                }
                
            }
        }
      
         /* >>>
        Segun la teoria, al empleado se le paga siempre sobre 15 días si el pago es quincenal o sobre 30 días
        así el mes tenga 28,29,30 o 31 días.
        <<< */
        $dias_trabajados = 0;

        if($this->mini_periodo == 1){
            $dias_trabajados = 30;
        }else if($this->mini_periodo == 2){
            $dias_trabajados = 15;
        }else if($this->mini_periodo == 4){
            $dias_trabajados = 8;
        }

        return $dias_trabajados = $dias_trabajados - $diasRestados;
    }


    /**
     * Método que se encarga de editar o crear salud, pension, subsidio de transporte en la tabla ne_nomina_calculos_fijos
     * y actualiza el total segun las vacaciones, el salario, ingresos adicionales, horas extras y deducciones, pretsaciones y retefuente
     *
     * return json
     */
    public function editValorTotal($calculosFijos = []){

        $ibcSeguridadSocial = collect([]);

         /* >>> Si no se tiene el pago del empleado obtenemos el valor total de ne_nomina_periodo <<< */
        if($this->pago_empleado === null){
            $this->pago_empleado = $this->valor_total;
        }

        $pagoEmpleado = $this->pago_empleado;

        /* >>>
            Si el periodo no es completo (30 días) entonces ingresa y divide en 2 el pago del empleado ya que cuenta con 2 miniperiodos (2 quincenas)
            (si fuea cada 8 días entonces sería 4 miniperiodos.)
        <<< */
        if($this->periodo != 0){
            $pagoEmpleado = $pagoEmpleado / $this->mini_periodo;
        }

        /* >>> Obtenemos valores_totales (en dinero) de vacaciones, salario, ingresos e incapacidades <<< */
        $ibcSeguridadSocial['vacaciones'] = floatval(NominaDetalleUno::select(DB::raw("SUM(valor_categoria) as valor_total"))->where('fk_nominaperiodo', $this->id)->where('fk_nomina_cuenta', 2)->where('fk_nomina_cuenta_tipo', 4)->groupBy('fk_nominaperiodo')->first()->valor_total ?? 0);
        $ibcSeguridadSocial['salario']= (($this->pago_empleado / 30) * $this->diasTrabajados()) - $ibcSeguridadSocial['vacaciones'];
        $ibcSeguridadSocial['ingresosyExtras'] = floatval(NominaDetalleUno::select(DB::raw("SUM(valor_categoria) as valor_total"))->where('fk_nominaperiodo', $this->id)->whereIn('fk_nomina_cuenta', [1,3])->whereNotIn('fk_nomina_cuenta_tipo', [8, 9])->groupBy('fk_nominaperiodo')->first()->valor_total ?? 0);
        $ibcSeguridadSocial['incapacidades'] = floatval(NominaDetalleUno::select(DB::raw("SUM(valor_categoria) as valor_total"))->where('fk_nominaperiodo', $this->id)->where('fk_nomina_cuenta', 2)->where('fk_nomina_cuenta_tipo', 5)->groupBy('fk_nominaperiodo')->first()->valor_total ?? 0);

        /* >>> Array de incapacidades e iniciamos una variable para ocntar los dias incapacitados <<< */
        $incapacidades = NominaDetalleUno::where('fk_nomina_cuenta', 2)->where('fk_nomina_cuenta_tipo', 5)->where('fk_nominaperiodo', $this->id)->get();
        $diasIncapacitado = 0;

        /* >>>
            Recorremos en un array los posibles días incapacitados y obtenemos la fecha inicio y fecha fin
            (si no tiene dias incapacitados no hay nada en fecha inicio y fecha fin)
            (se le suma +1 para que cuente el mismo dia que se incapacitó)
        <<< */
        foreach($incapacidades as $incapacidad){
            $fechaInicio = new Carbon($incapacidad->fecha_inicio);
            $fechaFin = new Carbon($incapacidad->fecha_fin);

            $diasIncapacitado += $fechaInicio->diffInDays($fechaFin);
        }

        if($diasIncapacitado){
            $diasIncapacitado++;
        }


        if($ibcSeguridadSocial['incapacidades'] > 0){
            $diasValidosTrabajados = $this->diasTrabajados() - $diasIncapacitado;
            /*>>> el salario se recalcula ya que la persona no trabajo un dia
             formula para obtener el dia trabajado de una persona con incapacidad (50mil pesos) <<<*/
            $ibcSeguridadSocial['salario'] -= $this->pago_empleado * ((30 / $this->mini_periodo) - $diasValidosTrabajados) / 30;
            /*>>> a las vacaciones se les suma el porcentaje del o los dias que se incapacito <<<*/
            $ibcSeguridadSocial['vacaciones'] += $ibcSeguridadSocial['incapacidades'];
        }
        $licenciaPaga = 0;
        $licencias = NominaDetalleUno::where('fk_nomina_cuenta', 2)->where('fk_nomina_cuenta_tipo', 6)->where('fk_nominaperiodo', $this->id)->whereNotNull('fecha_inicio')->get();
        foreach($licencias as $licencia){
            if(!($licencia->is_remunerado())){
                $ibcSeguridadSocial['salario'] -= $licencia->valor_categoria;
            }else{
                $ibcSeguridadSocial['salario'] -= $licencia->valor_categoria;
                $licenciaPaga += $licencia->valor_categoria;
            }
        }

        /* >>> Cálculo final del ibc seguridad social <<< */
        $ibcSeguridadSocial['total'] = $subtotal = $licenciaPaga + $ibcSeguridadSocial['vacaciones'] + $ibcSeguridadSocial['salario'] + $ibcSeguridadSocial['ingresosyExtras'];


        /* >>> Obtenemos los valores de salud y pension configurados desde el modulo de calculos fijos. <<< */
        $empresa = Auth::user()->empresa;
        $retenSalud = NominaConfiguracionCalculos::where('fk_idempresa',$empresa)->where('nro',2)->first();
        $retenPension = NominaConfiguracionCalculos::where('fk_idempresa',$empresa)->where('nro',3)->first();

        $persona = $this->nomina->persona;

        /* >>> Cálculo de retencion en salud y pensión <<< */
        
        if($persona->fk_salario_base == 2){
            $calculosFijos['reten_salud'] = (object)['valor' => (($subtotal * (70 / 100)) * $retenSalud->porcDecimal()), 'simbolo' => '-'];
            $calculosFijos['reten_pension'] = (object)['valor' => (($subtotal * (70 / 100)) * $retenPension->porcDecimal()), 'simbolo' => '-'];
            
            /* provisional
            if($this->pago_empleado >= 4000000 && $this->pago_empleado <= 16000000){
                 $calculosFijos['reten_pension_solidaria'] = (object)['valor' => (($subtotal * (70 / 100)) * (1/100)), 'simbolo' => '-'];
            }
            */
            
        }elseif($persona->fk_tipo_contrato == 4 || $persona->fk_tipo_contrato == 6){
            $calculosFijos['reten_salud'] = (object)['valor' => (0), 'simbolo' => '-'];
            $calculosFijos['reten_pension'] = (object)['valor' => (0), 'simbolo' => '-'];
        }else{
            $calculosFijos['reten_salud'] = (object)['valor' => ($subtotal * $retenSalud->porcDecimal()), 'simbolo' => '-'];
            $calculosFijos['reten_pension'] = (object)['valor' => ($subtotal * $retenPension->porcDecimal()), 'simbolo' => '-'];
        }

        //pensionado con aporte a salud
        if ($persona->fk_tipo_contrato == 17){
            $calculosFijos['reten_pension'] = (object)['valor' => (0), 'simbolo' => '-'];
        }

        /* >>> Cálculo de dias trabajados  <<< */
        $calculosFijos['dias_trabajados'] =  (object)['valor' => ($this->diasTrabajados() - array_sum($this->diasAusenteDetalle())), 'simbolo' => '#'];
        
        /* >>>
        Validamos si no viene ya un array con calculos fijos con subsidio de transporte, actualmente se ejecuta desde
        update_vacaciones en NominaController, de lo contrario hacemos el calculo del subisdio de transporte
        con base los dias_trabajados
        <<< */
        if(!isset($calculosFijos['subsidio_transporte'])){
            $subsidioTransporte = NominaConfiguracionCalculos::where('fk_idempresa', $empresa)->where('nro', 1)->first();
            if ($persona->subsidio == 1) {
                $calculosFijos['subsidio_transporte'] = (object)['valor' => ($subsidioTransporte->valor * $calculosFijos['dias_trabajados']->valor / 30), 'simbolo' => '+'];
            }else{
                $calculosFijos['subsidio_transporte'] = (object)['valor' => (0), 'simbolo' => '+'];
            }
        }

        $subtotal += floatval(NominaDetalleUno::select(DB::raw("SUM(valor_categoria) as valor_total"))->where('fk_nominaperiodo', $this->id)->where('fk_nomina_cuenta', 3)->where('fk_nomina_cuenta_tipo', 8)->groupBy('fk_nominaperiodo')->first()->valor_total ?? 0);


        foreach($calculosFijos as $key => $calculoFijo){

            /* >>> Si no hay dias trabajados entonces este se convierte en 0 días <<< */
            if(!isset($calculoFijo->dias_trabajados)){
                $calculoFijo->dias_trabajados = 0;
            }

            /* >>> Si ya existe un calculo fijo para cierto periodo entonces lo actualizamos, de lo contrario se crea <<< */
                 NominaCalculoFijo::updateOrCreate([
                                        'tipo' => $key,
                                        'fk_nominaperiodo' => $this->id,
                                    ], [
                                        'tipo' => $key,
                                        'valor' => $calculoFijo->valor,
                                        'simbolo' => $calculoFijo->simbolo,
                                        'dias_pagos' => $calculoFijo->dias_trabajados,
                                        'fk_nominaperiodo' => $this->id,
                                        'updated_at' => now(),
                                    ]);

        }

        /* >>> Asignamos la data actualizada a la variable calculosFijosCollect <<< */
        $calculosFijosCollect = $this->nominaCalculoFijos;

        /* >>> Sumamos y restamos retenciones en salud y pensión de los calculos que se obtuvieron actualizados <<< */
        $subtotal += $calculosFijosCollect->where('simbolo', '+')->sum('valor');
        $subtotal -= $calculosFijosCollect->where('simbolo', '-')->sum('valor');

        /* >>> Restamos deducciones, prestaciones y retefuente de la cuenta general numero 4 <<< */
        $subtotal -= floatval(NominaDetalleUno::select(DB::raw("SUM(valor_categoria) as valor_total"))->where('fk_nominaperiodo', $this->id)->where('fk_nomina_cuenta', 4)->groupBy('fk_nominaperiodo')->first()->valor_total ?? 0);

        /* >>> Asignamos el nuevo total a la nominaperiodo ($this->total) y actualizamos ($this->updae()) <<< */
        $total = $subtotal;
        $this->valor_total = $total;
        $this->update();
    }


    public function resumenTotal(){

        $totalidad = ['pago' => ['salario' => 0, 'subsidioDeTransporte' => 0, 'retencionesDeducciones' => 0, 'total' => 0],
                      'diasTrabajados' => ['diasPeriodo' => 0, 'total' => 0],
                      'salarioSubsidio' => ['salario' => 0, 'subsidioTransporte' => 0, 'total' => 0],
                      'ibcSeguridadSocial' =>  ['salario' => 0, 'total' => 0],
                      'retenciones' => ['salud' => 0, 'pension' => 0, 'total' => 0, 'porcentajeSalud' => 0, 'porcentajePension' => 0],
                      'seguridadSocial' => ['pension' => 0, 'riesgo1' => 0, 'total' => 0],
                      'parafiscales' => ['cajaCompensacion' => 0, 'total' => 0],
                      'provisionPrestacion' => ['cesantias' => 0, 'interesesCesantias' => 0, 'primaServicios' => 0, 'vacaciones' => 0, 'total' => 0],
                      'pagoContratado' => ['total' => 0]
                     ];

        $calculosFijosCollect = $this->nominaCalculoFijos;

        $pagoEmpleado = $this->pago_empleado;
        $totalidad['salarioSubsidio']['salarioCompleto'] = $pagoEmpleado;
        $totalidad['salarioSubsidio']['valorDia'] = $this->pago_empleado / 30;

        if($this->periodo != 0){
            $pagoEmpleado = $pagoEmpleado / $this->mini_periodo;
        }
        $totalidad['salarioSubsidio']['salario'] = $pagoEmpleado;
        $totalidad['salarioSubsidio']['subsidioTransporte'] = floatval($calculosFijosCollect->where('tipo', 'subsidio_transporte')->first()->valor ?? 0);
        $totalidad['salarioSubsidio']['total'] = $totalidad['salarioSubsidio']['salario'] + $totalidad['salarioSubsidio']['subsidioTransporte'];
        $totalidad['diasTrabajados']['diasPeriodo'] = $this->diasTrabajados();

        /*>>> Valor vacaciones <<<*/
        $totalidad['ibcSeguridadSocial']['vacaciones'] = floatval(NominaDetalleUno::select(DB::raw("SUM(valor_categoria) as valor_total"))->where('fk_nominaperiodo', $this->id)->where('fk_nomina_cuenta', 2)->where('fk_nomina_cuenta_tipo', 4)->groupBy('fk_nominaperiodo')->first()->valor_total ?? 0);
        $totalidad['ibcSeguridadSocial']['salario']= $pagoEmpleado - $totalidad['ibcSeguridadSocial']['vacaciones'];
        $totalidad['ibcSeguridadSocial']['ingresosyExtras'] = floatval(NominaDetalleUno::select(DB::raw("SUM(valor_categoria) as valor_total"))->where('fk_nominaperiodo', $this->id)->whereIn('fk_nomina_cuenta', [1,3])->whereNotIn('fk_nomina_cuenta_tipo', [8, 9])->groupBy('fk_nominaperiodo')->first()->valor_total ?? 0);
        $totalidad['ibcSeguridadSocial']['incapacidades'] = floatval(NominaDetalleUno::select(DB::raw("SUM(valor_categoria) as valor_total"))->where('fk_nominaperiodo', $this->id)->where('fk_nomina_cuenta', 2)->where('fk_nomina_cuenta_tipo', 5)->groupBy('fk_nominaperiodo')->first()->valor_total ?? 0);

        $incapacidades = NominaDetalleUno::where('fk_nomina_cuenta', 2)->where('fk_nomina_cuenta_tipo', 5)->where('fk_nominaperiodo', $this->id)->get();
        $diasIncapacitado = 0;
        $diasValidosTrabajados = $totalidad['diasTrabajados']['diasPeriodo'];
        

        foreach($incapacidades as $incapacidad){
            $fechaInicio = new Carbon($incapacidad->fecha_inicio);
            $fechaFin = new Carbon($incapacidad->fecha_fin);

            $diasIncapacitado += $fechaInicio->diffInDays($fechaFin);
        }
        if($diasIncapacitado){
            $diasIncapacitado++;
        }

        if($totalidad['ibcSeguridadSocial']['incapacidades'] > 0){
            $diasValidosTrabajados = $totalidad['diasTrabajados']['diasPeriodo'] - $diasIncapacitado;
            $totalidad['ibcSeguridadSocial']['salario'] -= $this->pago_empleado * ($totalidad['diasTrabajados']['diasPeriodo'] - $diasValidosTrabajados) / 30;
            $totalidad['ibcSeguridadSocial']['vacaciones'] += $totalidad['ibcSeguridadSocial']['incapacidades'];
        }

        $totalidad['ibcSeguridadSocial']['salarioParcial'] = $diasValidosTrabajados * $totalidad['salarioSubsidio']['valorDia'];
        //Valor real trabajado, contando unicamente con liquidaciones de la persona
        $totalidad['pagoContratado']['total'] = $diasValidosTrabajados * $this->pago_empleado / 30;

        $totalidad['diasTrabajados']['ausencia'] = $this->diasAusenteDetalle();
        $totalidad['diasTrabajados']['total'] = $totalidad['diasTrabajados']['diasPeriodo'] - array_sum($totalidad['diasTrabajados']['ausencia']);
        $totalidad['ibcSeguridadSocial']['licencias'] = 0;
        $totalidad['pago']['licencias'] = 0;
        $licencias = NominaDetalleUno::where('fk_nomina_cuenta', 2)->where('fk_nomina_cuenta_tipo', 6)->where('fk_nominaperiodo', $this->id)->whereNotNull('fecha_inicio')->get();
        $licenciaNoRemunerada = 0;
        foreach($licencias as $licencia){
            if(!($licencia->is_remunerado())){
                $totalidad['ibcSeguridadSocial']['licencias'] += $licencia->valor_categoria;
                $totalidad['ibcSeguridadSocial']['salario'] -= $licencia->valor_categoria;
                $totalidad['ibcSeguridadSocial']['salarioParcial'] -= $licencia->valor_categoria;
                $licenciaNoRemunerada += $licencia->valor_categoria;
                $totalidad['pagoContratado']['total'] -= $licencia->valor_categoria;
            }else{
                $totalidad['pago']['licencias'] += $licencia->valor_categoria;
                $totalidad['ibcSeguridadSocial']['salario'] -= $licencia->valor_categoria;
                $totalidad['ibcSeguridadSocial']['salarioParcial'] -= $licencia->valor_categoria;
            }
        }

        $totalidad['ibcSeguridadSocial']['total'] = $subtotal = $totalidad['pago']['licencias'] + $totalidad['ibcSeguridadSocial']['vacaciones'] + ($totalidad['pagoContratado']['total']) + $totalidad['ibcSeguridadSocial']['ingresosyExtras'];

        $totalidad['retenciones']['salud'] = floatval($calculosFijosCollect->where('tipo', 'reten_salud')->first()->valor ?? 0);
        $totalidad['retenciones']['pension'] = floatval($calculosFijosCollect->where('tipo', 'reten_pension')->first()->valor ?? 0);
        $totalidad['retenciones']['total'] += $totalidad['retenciones']['salud'] + $totalidad['retenciones']['pension'];
        $totalidad['retenciones']['porcentajeSalud'] =  round($totalidad['retenciones']['salud'] * 100 / $totalidad['ibcSeguridadSocial']['total']);
        $totalidad['retenciones']['porcentajePension'] =  round($totalidad['retenciones']['pension'] * 100 / $totalidad['ibcSeguridadSocial']['total']);
        
        /*>>> Valor neto pago empleado <<<*/
        $subtotal += floatval(NominaDetalleUno::select(DB::raw("SUM(valor_categoria) as valor_total"))->where('fk_nominaperiodo', $this->id)->where('fk_nomina_cuenta', 3)->where('fk_nomina_cuenta_tipo', 8)->groupBy('fk_nominaperiodo')->first()->valor_total ?? 0);
        $subtotal += $calculosFijosCollect->where('simbolo', '+')->sum('valor');
        $subtotal -= $calculosFijosCollect->where('simbolo', '-')->sum('valor');
        $subtotal -= $deducciones = $totalidad['deducciones']['total'] = floatval(NominaDetalleUno::select(DB::raw("SUM(valor_categoria) as valor_total"))->where('fk_nominaperiodo', $this->id)->where('fk_nomina_cuenta', 4)->groupBy('fk_nominaperiodo')->first()->valor_total ?? 0);
        //$subtotal -= $totalidad['ibcSeguridadSocial']['vacaciones'];
        
        $totalidad['pago']['total'] = $subtotal;
        $totalidad['pago']['salario'] = $totalidad['ibcSeguridadSocial']['salario'];
        $totalidad['pago']['extrasOrdinariasRecargos'] = floatval(NominaDetalleUno::select(DB::raw("SUM(valor_categoria) as valor_total"))->where('fk_nominaperiodo', $this->id)->where('fk_nomina_cuenta', 1)->groupBy('fk_nominaperiodo')->first()->valor_total ?? 0);
        $totalidad['pago']['vacaciones'] = $totalidad['ibcSeguridadSocial']['vacaciones'];
        $totalidad['pago']['ingresosAdicionales'] = floatval(NominaDetalleUno::select(DB::raw("SUM(valor_categoria) as valor_total"))->where('fk_nominaperiodo', $this->id)->where('fk_nomina_cuenta', 3)->whereNotIn('fk_nomina_cuenta_tipo', [8, 9])->groupBy('fk_nominaperiodo')->first()->valor_total ?? 0);
        $totalidad['pago']['subsidioDeTransporte'] = floatval($calculosFijosCollect->where('tipo', 'subsidio_transporte')->first()->valor ?? 0);
        $totalidad['pago']['retencionesDeducciones'] = $totalidad['retenciones']['total'] + $deducciones;


        $porcentajeRiesgo = 0.00522;

        if($claseRiesgo = $this->nomina->persona->clase_riesgo()){
            if($claseRiesgo == 'Máximo - Riesgo 5'){
                $porcentajeRiesgo = 0.0696;
            }else if($claseRiesgo == 'Bajo - riesgo 2'){
                $porcentajeRiesgo = 0.01044;
            }else if($claseRiesgo == 'Medio - Riesgo 3'){
                $porcentajeRiesgo = 0.02436;
            }else if($claseRiesgo == 'Alto - riesgo 4'){
                $porcentajeRiesgo = 0.0435;
            }
        }
        $totalidad['seguridadSocial']['valorRiesgo'] = $porcentajeRiesgo;
        $totalidad['seguridadSocial']['pension'] = $totalidad['ibcSeguridadSocial']['total'] * 0.12;
        $totalidad['seguridadSocial']['riesgo1'] = $totalidad['ibcSeguridadSocial']['salario'] * $porcentajeRiesgo;
        $totalidad['seguridadSocial']['total'] = $totalidad['seguridadSocial']['pension'] + $totalidad['seguridadSocial']['riesgo1'];

        $totalidad['parafiscales']['cajaCompensacion'] = $totalidad['ibcSeguridadSocial']['total'] * 0.04;
        $totalidad['parafiscales']['total'] = $totalidad['parafiscales']['cajaCompensacion'];
        $totalidad['provisionPrestacion']['cesantias'] = $totalidad['salarioSubsidio']['total'] * (8.33 / 100);
        $totalidad['provisionPrestacion']['interesesCesantias'] = $totalidad['provisionPrestacion']['cesantias'] * 0.12;
        $totalidad['provisionPrestacion']['primaServicios'] = $totalidad['salarioSubsidio']['total'] * (8.33 / 100);
        $totalidad['provisionPrestacion']['vacaciones'] = $totalidad['ibcSeguridadSocial']['total'] * (4.17 / 100);
        $totalidad['provisionPrestacion']['total'] = $totalidad['provisionPrestacion']['cesantias'] + $totalidad['provisionPrestacion']['interesesCesantias'] + $totalidad['provisionPrestacion']['primaServicios'] + $totalidad['provisionPrestacion']['vacaciones'];
        return $totalidad;
    }

    public function diasAusenteDetalle(){
        $detalles = NominaDetalleUno::where('fk_nominaperiodo', $this->id)->where('fk_nomina_cuenta', 2)->get();
        $dias = [];
        foreach ($detalles as $detalle) {
            if ($detalle->fecha_inicio) {
                if(!$detalle->nombre){
                    $detalle->nombre = 'sin definir';
                }
                $fechaEmision = Carbon::parse($detalle->fecha_inicio);
                $fechaExpiracion = Carbon::parse($detalle->fecha_fin);
                if(!isset($dias[$detalle->nombre])){
                    $dias[$detalle->nombre] = 0;
                }
                $dias[$detalle->nombre] += ($fechaExpiracion->diffInDays($fechaEmision) + 1);
            }
        }
        return $dias;
    }

    /**
     *
     * Método para obtener una coleccion de rangos de fechas desde y hasta de las nominas que se han generado en una empresa
     * ejm: (1-15 oct 2021 - 16-31oct 2021 - 1-15 nov 2021 - 16 - 30nov 2021)
     *
     * return json
     */
    public static function rangosFechas(){

        $empresa = Auth::user()->empresa;
        $rangoFechas = Nomina::join('ne_nomina_periodos as np','ne_nomina.id','=','np.fk_idnomina')
        ->where('ne_nomina.fk_idempresa',$empresa)
        ->where('np.isPagado',1)
        ->select('np.id','np.fecha_desde','np.fecha_hasta')
        ->groupBy('np.fecha_desde')->get();

        /*>>> Organizamos la data para separar cada rango en un espacio de un array unico <<<*/
        $rangoFinales = [];
        foreach($rangoFechas as $rango){
            array_push($rangoFinales,$rango->fecha_desde);
            array_push($rangoFinales,$rango->fecha_hasta);
        }

        return (object)$rangoFinales;

    }


}
