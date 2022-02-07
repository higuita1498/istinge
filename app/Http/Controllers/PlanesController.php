<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  stdClass;
use Auth; use DB;
use App\Empresa;
use Carbon\Carbon; use App\Planes;
use App\SuscripcionPago; use App\Suscripcion;
class PlanesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
        view()->share(['inicio' => 'master', 'seccion' => 'Planes', 'title' => 'Planes', 'icon' =>'fa fa-building']);
    }

    public function index($ingresosLimit = false, $facturasLimit = false, $fechaLimit = false, $fecha = false,
                          $msg = false, $AllOk = false)
    {
        $this->getAllPermissions(Auth::user()->id);
        $personalPlan = Empresa::find(Auth::user()->empresa)->p_personalizado;
        return view('planes.index')->with(compact('ingresosLimit', 'facturasLimit', 'fechaLimit',
            'fecha', 'msg', 'AllOk', 'personalPlan'));
    }

    public function indexPersonalizado()
    {
        $planPersonalizado = Empresa::find(Auth::user()->empresa);
        $planPersonalizado = $planPersonalizado->p_personalizado;
        $plan = DB::table('planes_personalizados')->find($planPersonalizado);
        
        $pagoPersonal = SuscripcionPago::where('id_empresa', Auth::user()->empresa)
            ->where('personalizado', true)
            ->get()->last();
        $price = $plan->precio;
        $idPlan = $plan->id;
        if ($pagoPersonal){
            $pagoPersonal = true;
        }else{
            $pagoPersonal = false;
        }

        view()->share(['inicio' => 'master', 'seccion' => 'Planes', 'title' => 'Plan Personalizado', 'icon' =>'fa fa-building']);
        return view('planes.planPersonalizado')->with(compact('plan', 'personalPlan', 'pagoPersonal', 'price', 'idPlan'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //respuest
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function verificarLimites($cuenta){

        $msg                = '';
        switch ($cuenta){
            case 0:
                $msg = "Su suscripción aún no llega a la fecha de renovación.";
                break;
            case 1:
                $msg = "Espere a que finalice su suscripción activa para escoger un plan inferior al actual.";
                break;
            case 2:
                Log::error('ERR - Planes controles - VerificarLimites()');
        }
        $ingresosLimit      = Auth::user()->ingresosMaximos();
        $facturasLimit      = Auth::user()->facturasHechas();
        $suscripcionPago    = SuscripcionPago::where("id_empresa", Auth::user()->empresa)->get()->last();
        $suscrpcionPago     = ($suscripcionPago) ? $suscripcionPago->valid : false;
        $fechaLimit         = true;
        $suscripcion        = Suscripcion::where('id_empresa', Auth::user()->empresa)->get()->last();

        if($suscripcion->ilimitado)
            return $this->index(false, false, false, false, "Usted posee el plan ilimitado activo.");
    
        //messagebird
        $fechaActual        = Carbon::now();
        $fechaVenc = Carbon::parse($suscripcion->fec_vencimiento);
        $fechaLimit = ($fechaVenc->greaterThan($fechaActual)) ? $fechaLimit : false;
        if ($fechaLimit || $suscripcionPago){
            return $this->index($ingresosLimit, $facturasLimit, $fechaLimit, $fechaVenc, $msg);
        }
        $suscripcion->fec_inicio        = $fechaActual;
        $suscripcion->fec_vencimiento   = (Carbon::parse($suscripcion->fec_inicio))->addMonth();
        $suscripcion->fec_corte         = $suscripcion->fec_vencimiento;
        $suscripcion->save();
        $msg                = "¡Plan gratuito renovado exitosamente!";
        return $this->index($ingresosLimit, $facturasLimit, $fechaLimit, $suscripcion->fec_vencimiento, $msg, true);
    }

    public function pagos($valor, $personalizado=false)
    {
        if($personalizado){
            $plan = DB::table('planes_personalizados')->find($personalizado);
            $personalizado = true;
            $tipo = $plan->nombre;
            $plan = $plan;
            return view ('planes.pagopersonalizado', compact('valor','tipo','plan','personalizado'));
        }
        if ($valor) {
            switch ($valor){
                case 35000:
                    $tipo = "Plan Emprendedor";
                    $plan = 2;
                    break;
                case 60000:
                    $tipo = "Plan Pyme";
                    $plan = 1;
                    break;
                case 90000:
                    $tipo = "Plan Avanzado";
                    $plan = 3;
                    break;
                default:
                    redirect('/PlanesPagina');
            
            }
        }
        else
        {
            return redirect('/PlanesPagina');
        }

        return view ('planes.pagosplan', compact('valor','tipo','plan','personalizado'));
    }

    public function respuestapago()
    {
        $suplente_pago = Planes::where('referencia_pago',$_REQUEST['referenceCode'])->first();
        $ApiKey = config('app.api_key');


        if ($suplente_pago) {

            // Validamos que tipo de estado tiene la transaccion para poder guardarla en forma de texto.
            if ($_REQUEST['transactionState']==4) {
                $suplente_pago->EstadoTransaccion = "APPROVED";
                $suplente_pago->estado = 1;

                //Como todo salió bien  y quedo de inmediato con el estado 4 entonces vamos a pasarlo a la tabla suscripciones_pagos y posteriormente a aumentar los dias de prorroga en la tabla suscripciones


                $suscripcion_pago = new SuscripcionPago();
                $suscripcion_pago->id_empresa = auth()->user()->empresa;
                $suscripcion_pago->plan = $suplente_pago->plan;
                $suscripcion_pago->referencia = $suplente_pago->referencia_pago;
                $suscripcion_pago->personalizado = $suplente_pago->personalizado;
                $suscripcion_pago->meses = $suplente_pago->meses;
                $suscripcion_pago->estado = 1;
                $suscripcion_pago->suplentepago_id = $suplente_pago->id;
                $suscripcion_pago->monto = $suplente_pago->monto;

                //Para el tipo de pago payu me tira el texto, mientras que nosotros lo guardamos en modo int entonces vamos a hacer la validación.

                if ($suplente_pago->tipo_pago == "CREDIT_CARD") {
                    $suscripcion_pago->tipo_pago = 5;
                }
                else if ($suplente_pago->tipo_pago == "PSE")
                {
                    $suscripcion_pago->tipo_pago = 3;
                }else if($suplente_pago->tipo_pago == "DEBIT_CARD")
                {
                    $suscripcion_pago->tipo_pago = 6;
                }else if($suplente_pago->tipo_pago == "CASH" || $suplente_pago->tipo_pago == "REFERENCED"){
                    $suscripcion_pago->tipo_pago = 1;
                }else if($suplente_pago->tipo_pago == "ACH")
                {
                    $suscripcion_pago->tipo_pago = 3;
                }else if($suplente_pago->tipo_pago == "SPEI")
                {
                    $suscripcion_pago->tipo_pago = 3;
                }else if($suplente_pago->tipo_pago == "BANK_REFERENCED"){
                    $suscripcion_pago->tipo_pago = 3;
                }
                $suscripcion_pago->save();


                //Ahora buscamos la tabla suscripciones para cambiar los dias de activación del sistema.
                $mesesPagos = $suscripcion_pago->meses;;

                //$fecha_inicio = date('Y-m-d',strtotime($suscripcion_pago->created_at));
                //$fecha_final = date('Y-m-d', strtotime($suscripcion_pago->created_at."+ $mesesPagos month"));

                $suscripcion = Suscripcion::where('id_empresa',$suscripcion_pago->id_empresa)->first();



                if($suscripcion){
                    $fecha_vencimiento = Carbon::now()->addMonth($mesesPagos);
                    $fecha_final = date('Y-m-d', strtotime(Carbon::now()."+ $mesesPagos month"));
                    $tmpFecha = Carbon::parse($suscripcion->fec_vencimiento);
                    if (!$tmpFecha->gte($fecha_vencimiento)){
                        $suscripcion->fec_vencimiento = $fecha_final;
                        $suscripcion->fec_corte = $fecha_final;
                    }
                    $suscripcion->fec_inicio = Carbon::now();
                    $suscripcion->updated_at = Carbon::now();
                    $suscripcion->save();

                }else{
                    $fecha_inicio = date('Y-m-d',strtotime($suscripcion_pago->created_at));
                    $fecha_final = date('Y-m-d', strtotime($suscripcion_pago->created_at."+ $mesesPagos month"));
                    $suscripcion = New Suscripcion;
                    $suscripcion->id_empresa = $suscripcion_pago->id_empresa;
                    $suscripcion->fec_inicio = $fecha_inicio;
                    $suscripcion->fec_vencimiento = $fecha_final;
                    $suscripcion->created_at = Carbon::now();
                    $suscripcion->save();
                }
                $mensaje = "Pago realizado Correctamente";
            }
            else if($_REQUEST['transactionState']==6)
            {
                $suplente_pago->EstadoTransaccion = "DECLINED";
                $suplente_pago->estado = 1;

                //---------info repetitiva-----------------//
                $suscripcion_pago = new SuscripcionPago();
                $suscripcion_pago->id_empresa = auth()->user()->empresa;
                $suscripcion_pago->plan = $suplente_pago->plan;
                $suscripcion_pago->personalizado = $suplente_pago->personalizado;
                $suscripcion_pago->referencia = $suplente_pago->referencia_pago;
                $suscripcion_pago->meses = ($suplente_pago->personalizado) ? DB::table('planes_personalizados')->find($suplente_pago->plan)->meses :
                    $suplente_pago->meses;
                $suscripcion_pago->estado =3;
                $suscripcion_pago->suplentepago_id = $suplente_pago->id;
                $suscripcion_pago->monto = $suplente_pago->monto;

                if ($suplente_pago->tipo_pago == "CREDIT_CARD") {
                    $suscripcion_pago->tipo_pago = 5;
                }
                else if ($suplente_pago->tipo_pago == "PSE")
                {
                    $suscripcion_pago->tipo_pago = 3;
                }else if($suplente_pago->tipo_pago == "DEBIT_CARD")
                {
                    $suscripcion_pago->tipo_pago = 6;
                }else if($suplente_pago->tipo_pago == "CASH" || $suplente_pago->tipo_pago == "REFERENCED"){
                    $suscripcion_pago->tipo_pago = 1;
                }else if($suplente_pago->tipo_pago == "ACH")
                {
                    $suscripcion_pago->tipo_pago = 3;
                }else if($suplente_pago->tipo_pago == "SPEI")
                {
                    $suscripcion_pago->tipo_pago = 3;
                }else if($suplente_pago->tipo_pago == "BANK_REFERENCED"){
                    $suscripcion_pago->tipo_pago = 3;
                }
                $suscripcion_pago->save();
                //------------- /info repetitiva-----------------//

                $mensaje= "Pago Rechazado, intentelo nuevamente";
            }
            else if($_REQUEST['transactionState']==104)
            {
                $suplente_pago->EstadoTransaccion = "ERROR";
                $suplente_pago->estado = 1;

                //---------info repetitiva-----------------//
                $suscripcion_pago = new SuscripcionPago();
                $suscripcion_pago->id_empresa = auth()->user()->empresa;
                $suscripcion_pago->plan = $suplente_pago->plan;
                $suscripcion_pago->personalizado = $suplente_pago->personalizado;
                $suscripcion_pago->referencia = $suplente_pago->referencia_pago;
                $suscripcion_pago->meses = ($suplente_pago->personalizado) ? DB::table('planes_personalizados')->find($suplente_pago->plan)->meses :
                    $suplente_pago->meses;
                $suscripcion_pago->estado =5;
                $suscripcion_pago->suplentepago_id = $suplente_pago->id;
                $suscripcion_pago->monto = $suplente_pago->monto;

                if ($suplente_pago->tipo_pago == "CREDIT_CARD") {
                    $suscripcion_pago->tipo_pago = 5;
                }
                else if ($suplente_pago->tipo_pago == "PSE")
                {
                    $suscripcion_pago->tipo_pago = 3;
                }else if($suplente_pago->tipo_pago == "DEBIT_CARD")
                {
                    $suscripcion_pago->tipo_pago = 6;
                }else if($suplente_pago->tipo_pago == "CASH" || $suplente_pago->tipo_pago == "REFERENCED"){
                    $suscripcion_pago->tipo_pago = 1;
                }else if($suplente_pago->tipo_pago == "ACH")
                {
                    $suscripcion_pago->tipo_pago = 3;
                }else if($suplente_pago->tipo_pago == "SPEI")
                {
                    $suscripcion_pago->tipo_pago = 3;
                }else if($suplente_pago->tipo_pago == "BANK_REFERENCED"){
                    $suscripcion_pago->tipo_pago = 3;
                }
                $suscripcion_pago->save();
                //------------- /info repetitiva-----------------//
                $mensaje= "Pago Rechazado, intentelo nuevamente";
            }
            else if($_REQUEST['transactionState']==7)
            {
                $suplente_pago->EstadoTransaccion = "PENDING";
                $suplente_pago->estado = 1;

                //---------info repetitiva-----------------//
                $suscripcion_pago = new SuscripcionPago();
                $suscripcion_pago->id_empresa = auth()->user()->empresa;
                $suscripcion_pago->plan = $suplente_pago->plan;
                $suscripcion_pago->personalizado = $suplente_pago->personalizado;
                $suscripcion_pago->referencia = $suplente_pago->referencia_pago;
                $suscripcion_pago->meses = ($suplente_pago->personalizado) ? DB::table('planes_personalizados')->find($suplente_pago->plan)->meses :
                    $suplente_pago->meses;
                $suscripcion_pago->estado =2;
                $suscripcion_pago->suplentepago_id = $suplente_pago->id;
                $suscripcion_pago->monto = $suplente_pago->monto;

                if ($suplente_pago->tipo_pago == "CREDIT_CARD") {
                    $suscripcion_pago->tipo_pago = 5;
                }
                else if ($suplente_pago->tipo_pago == "PSE")
                {
                    $suscripcion_pago->tipo_pago = 3;
                }else if($suplente_pago->tipo_pago == "DEBIT_CARD")
                {
                    $suscripcion_pago->tipo_pago = 6;
                }else if($suplente_pago->tipo_pago == "CASH" || $suplente_pago->tipo_pago == "REFERENCED"){
                    $suscripcion_pago->tipo_pago = 1;
                }else if($suplente_pago->tipo_pago == "ACH")
                {
                    $suscripcion_pago->tipo_pago = 3;
                }else if($suplente_pago->tipo_pago == "SPEI")
                {
                    $suscripcion_pago->tipo_pago = 3;
                }else if($suplente_pago->tipo_pago == "BANK_REFERENCED"){
                    $suscripcion_pago->tipo_pago = 3;
                }
                $suscripcion_pago->save();
                //------------- /info repetitiva-----------------//
                $mensaje= "Pago En proceso";
            }
            else if($_REQUEST['transactionState']==5)
            {
                $suplente_pago->EstadoTransaccion = "EXPIRED";
                $suplente_pago->estado = 1;

                //---------info repetitiva-----------------//
                $suscripcion_pago = new SuscripcionPago();
                $suscripcion_pago->id_empresa = auth()->user()->empresa;
                $suscripcion_pago->plan = $suplente_pago->plan;
                $suscripcion_pago->personalizado = $suplente_pago->personalizado;
                $suscripcion_pago->referencia = $suplente_pago->referencia_pago;
                $suscripcion_pago->meses = ($suplente_pago->personalizado) ? DB::table('planes_personalizados')->find($suplente_pago->plan)->meses :
                    $suplente_pago->meses;
                $suscripcion_pago->estado =4;
                $suscripcion_pago->suplentepago_id = $suplente_pago->id;
                $suscripcion_pago->monto = $suplente_pago->monto;

                if ($suplente_pago->tipo_pago == "CREDIT_CARD") {
                    $suscripcion_pago->tipo_pago = 5;
                }
                else if ($suplente_pago->tipo_pago == "PSE")
                {
                    $suscripcion_pago->tipo_pago = 3;
                }else if($suplente_pago->tipo_pago == "DEBIT_CARD")
                {
                    $suscripcion_pago->tipo_pago = 6;
                }else if($suplente_pago->tipo_pago == "CASH" || $suplente_pago->tipo_pago == "REFERENCED"){
                    $suscripcion_pago->tipo_pago = 1;
                }else if($suplente_pago->tipo_pago == "ACH")
                {
                    $suscripcion_pago->tipo_pago = 3;
                }else if($suplente_pago->tipo_pago == "SPEI")
                {
                    $suscripcion_pago->tipo_pago = 3;
                }else if($suplente_pago->tipo_pago == "BANK_REFERENCED"){
                    $suscripcion_pago->tipo_pago = 3;
                }
                $suscripcion_pago->save();
                //------------- /info repetitiva-----------------//
                $mensaje= "Pago Rechazado, intentelo nuevamente";
            }


            $suplente_pago->tipo_pago =  $_REQUEST['lapPaymentMethodType'];
            $suplente_pago->lapPaymentMethod = $_REQUEST['lapPaymentMethod'];
            $suplente_pago->transactionState = $_REQUEST['transactionState'];
            $suplente_pago->transactionId = $_REQUEST['transactionId'];
            $suplente_pago->type_currency = $_REQUEST['currency'];
            $suplente_pago->api_key = config('app.api_key');

            $firma_cadena = $ApiKey."~".$suplente_pago->merchant_id."~".$suplente_pago->referencia_pago."~". number_format($suplente_pago->monto, 2, '.', '')."~".$suplente_pago->type_currency."~".$suplente_pago->transactionState;

            $suplente_pago->firm = md5($firma_cadena);
            $suplente_pago->save();
        }
        //¿pasa por acá si el cliente recarga la pasarela? No sé porque tengo que verificar si queda con el mismo referencecode
        else{

        }

        return redirect('/empresa/suscripcion/pagos')->with('success',$mensaje);
    }

    public function pagohecho()
    {
        return "Pago hecho";
    }

    public function getObtenerInformacionPago($monto)
    {
        // MANERA DE COMPROBAR QUE TODOS LOS CODIGOS SEAN UNICOS
        $sw = 1;
        while ($sw == 1) {
            $numref = "pago-".Planes::generateRandomString();
            if (Planes::where('referencia_pago',$numref)->first()) {
                $numref = "pago-".Planes::generateRandomString();
            }
            else
            {
                $sw = 0;
            }

        }

        $api_key = "tZdIpXl9HrE9hrzVncOv8UO0Fd";
        /*Manra de crear un objeto nuevo*/
        $info_pago = new stdClass();
        $info_pago->merchantId = "569332";
        $info_pago->accountId = "571960";
        $info_pago->description = "Pago Suscripción Gestordepartes.net";
        $info_pago->referenceCode = $numref;
        $info_pago->amount = $monto;
        $info_pago->tax = "0";
        $info_pago->taxReturnBase = "0";
        $info_pago->currency = "COP";
        $info_pago->signature = md5($api_key."~".$info_pago->merchantId."~".$info_pago->referenceCode."~".$monto."~".$info_pago->currency);
        $info_pago->test="0";
        $info_pago->buyerEmail=Auth()->user()->email;
        $info_pago->responseUrl="https://gestordepartes.net/respuestapago/";
        $info_pago->confirmationUrl="https://gestordepartes.net/PagoHecho/";
        //$info_pago->responseUrl="http://127.0.0.1:8000/respuestapago/";
        //$info_pago->confirmationUrl="http://127.0.0.1:8000/PagoHecho/";
        return response()->json($info_pago);
    }

    public function personalizados_index()
    {
        $planes = DB::table('planes_personalizados')->get();
        $empresasPlanes = Empresa::all()->pluck('p_person   alizado');
        $empresasPlanes = $empresasPlanes->toArray();
        $used = DB::table('planes_personalizados')->whereNotIn('id', $empresasPlanes)->get();
        return view('master.planes.index')->with(compact('planes', 'used'));
    }

    public function personalizados_create()
    {
        return view('master.planes.create');
    }

    public function personalizados_store(Request $request)
    {
        DB::table('planes_personalizados')->insert([
            'nombre' => $request->input('nombre'),
            'facturas' => $request->input('facturas'),
            'ingresos' => $request->input('ingresos'),
            'precio' => $request->input('precio'),
            'meses' => $request->input('meses'),
        ]);
        $planes = DB::table('planes_personalizados')->get();
        $empresasPlanes = Empresa::all()->pluck('p_personalizado');
        $used = DB::table('planes_personalizados')->whereNotIn('id', $empresasPlanes)->get();
        return view('master.planes.index')->with('success', 'Plan agregado exitosamente.')->with(compact('planes', 'used'));
    }

    public function personalizados_edit($plan)
    {
        $plan = DB::table('planes_personalizados')->find($plan);
        return view('master.planes.edit')->with('plan', $plan);
    }

    public function personalizados_update(Request $request, $plan)
    {
        DB::table('planes_personalizados')->where('id',$plan)
            ->update([
                'nombre' => $request->input('nombre'),
                'facturas' => $request->input('facturas'),
                'ingresos' => $request->input('ingresos'),
                'precio' => $request->input('precio'),
                'meses' => $request->input('meses'),
            ]);
        $planes = DB::table('planes_personalizados')->get();
        $empresasPlanes = Empresa::all()->pluck('p_personalizado');
        $used = DB::table('planes_personalizados')->whereNotIn('id', $empresasPlanes)->get();

        return view('master.planes.index')->with('success', 'Plan modificado exitosamente')
            ->with(compact('planes', 'used'));
    }

    public function personalizados_destroy($id)
    {

        $plan = DB::table('planes_personalizados')->where('id', $id)->first();
        $empresasPlanes = Empresa::all()->pluck('p_personalizado');
        $used = DB::table('planes_personalizados')->whereNotIn('id', $empresasPlanes)->get();
        $planes = DB::table('planes_personalizados')->get();
        if($plan){
            DB::table('planes_personalizados')->where('id',$id)->delete();
            $planes = DB::table('planes_personalizados')->get();

            return view('master.planes.index')->with('success', 'Plan eliminado exitosamente.')->with(compact('planes', 'used'));
        }
        return view('master.planes.index')->with('warning', 'No se puede eliminar el plan.')->with(compact('planes', 'used'));
    }

//Guardardo previo de la informacion del pedido, ya que el cliente puede que no regrese a la tienda una vez haya comprado un plan
    function PreGuardarPago(Request $data)
    {

        if($data->pMeses != 0 ){
           $meses = $data->pMeses;
        }else{
            //Validamos que mes escogio el usuario por medio del id del radiobutton
            if ($data->meses == "optradio1") {
                $meses = 1;
            }
            else if ($data->meses == "optradio2"){
                $meses = 6;
            }
            else if ($data->meses == "optradio3"){
                $meses = 12;
            }
            else
            {
                return null;
            }
        }


        $suplente_pago = Planes::firstOrCreate(
            ['id_empresa' => auth()->user()->empresa,
                'monto' => $data->amount,
                'meses' => $meses,
                'plan' => $data->plan,
                'personalizado' => $data->personalPlan,
                'referencia_pago' => $data->referenceCode,
                'api_key' => config('app.api_key'),
                'merchant_id' => $data->merchantId,
                'account_id' => $data->accountId,
                'signature' => $data->signature,
                'estado' => 0,
                'description' => $data->description,
            ]
        );

        $suplente_pago->plazo = date('Y-m-d H:m:s',strtotime($suplente_pago->created_at."+ 1 day"));
        $suplente_pago->save();

        return response()->json("hecho");
    }

    function consultaestado()
    {
        $pedidos = Planes::where('id_empresa',Auth()->user()->empresa)->where('TransactionState',7)->get();



        if ($pedidos) {

            foreach ($pedidos as $pdi) {

                $pregunta = json_decode(Planes::ConsultaEstado($pdi->referencia_pago),true);

                if ($pregunta['result']) {

                    $suscripcion_pago = SuscripcionPago::where('referencia',$pdi->referencia_pago)->first();

                    $newestado = $pregunta['result']['payload']['order']['transactions']['transaction']['transactionResponse']['state'];

                    //Pdi es igual a eso para poder hacer las validaciones con el newestado, ya que
                    //el newestado no me devuelve un número si no un string
                    //Ademas acá solo se van a recorrer los status = 7 (pendientes)
                    //$pdi->TransactionState = 'PENDING';

                    //Si es diferente el transaccionState al newestado es porque hubo un cambio
                    //Entonces entramos a actualizar ese nuevo estado
                    if ("PENDING" != $newestado) {

                        if ($newestado == 'APPROVED') {
                            $pdi->TransactionState = 4;
                            $pdi->EstadoTransaccion = 'APPROVED';
                            $suscripcion_pago->estado = 1;


                            ////////////////Tabla: Suscripciones ////////////////////
                            $mesesPagos = $suscripcion_pago->meses;
                            //$fecha_inicio = date('Y-m-d',strtotime($suscripcion_pago->created_at));
                            //$fecha_final = date('Y-m-d', strtotime($suscripcion_pago->created_at."+ $mesesPagos month"));

                            $suscripcion = Suscripcion::where('id_empresa',$suscripcion_pago->id_empresa)->first();

                            if($suscripcion){
                                $fecha_vencimiento = Carbon::now()->addMonth($mesesPagos);
                                $fecha_final = date('Y-m-d', strtotime(Carbon::now()."+ $mesesPagos month"));
                                $tmpFecha = Carbon::parse($suscripcion->fec_vencimiento);
                                if (!$tmpFecha->gte($fecha_vencimiento)){
                                    $suscripcion->fec_vencimiento = $fecha_final;
                                    $suscripcion->fec_corte = $fecha_final;
                                }
                                $suscripcion->fec_inicio = Carbon::now();
                                $suscripcion->updated_at = Carbon::now();
                                $suscripcion->save();

                            }else{
                                $fecha_inicio = date('Y-m-d',strtotime($suscripcion_pago->created_at));
                                $fecha_final = date('Y-m-d', strtotime($suscripcion_pago->created_at."+ $mesesPagos month"));
                                $suscripcion = New Suscripcion;
                                $suscripcion->id_empresa = $suscripcion_pago->id_empresa;
                                $suscripcion->fec_inicio = $fecha_inicio;
                                $suscripcion->fec_vencimiento = $fecha_final;
                                $suscripcion->created_at = Carbon::now();
                                $suscripcion->save();
                            }
                            ////////////////Tabla: Suscripciones ////////////////////


                        }
                        else if ($newestado == 'DECLINED')
                        {
                            $pdi->TransactionState = 6;
                            $pdi->EstadoTransaccion = 'DECLINED';
                            $suscripcion_pago->estado = 3;
                        }
                        else if($newestado == 'ERROR')
                        {
                            $pdi->TransactionState = 104;
                            $pdi->EstadoTransaccion = 'ERROR';
                            $suscripcion_pago->estado = 5;
                        }
                        else if ($newestado == 'EXPIRED')
                        {
                            $pdi->TransactionState = 5;
                            $pdi->EstadoTransaccion = 'EXPIRED';
                            $suscripcion_pago->estado = 4;
                        }
                        else
                        {
                            break;
                        }
                        $suscripcion_pago->save();
                        $pdi->save();
                    }
                }
            }
            return response()->json($pedidos);
        }else
        {
            return response()->json("No hay pedidos pendientes");
        }

    }

    function datosfaltantes()
    {
        $suplente_planes = Planes::where('id_empresa',Auth()->user()->empresa)->where('transactionId',null)->get();
        $entra = Planes::where('id_empresa',Auth()->user()->empresa)->where('transactionId',null)->count();



        //Si hay transaciones nulas si entra.
        if ($entra > 0)
        {

            foreach ($suplente_planes as $pdi)
            {
                $pregunta = json_decode(Planes::ConsultaEstado($pdi->referencia_pago),true);

                //Si hay un resultado entonces vamos a guardar si o si los datos en la tabla suscripciones_pagos y actualizar los de la suplente.
                if ($pregunta['result'])
                {

                    /////////////////////////////Consulta PAYU/////////////////////////////
                    $paymentMethod = $pregunta['result']['payload']['order']['transactions']['transaction']['paymentMethod'];

                    $transactionId = $pregunta['result']['payload']['order']['processedTransactionId'];

                    $status = $pregunta['result']['payload']['order']['transactions']['transaction']['transactionResponse']['state'];

                    $type_currency = $pregunta['result']['payload']['order']['additionalValues']['entry'][0]['additionalValue']['currency'];

                    $firma_cadena = $pdi->api_key."~".$pdi->merchant_id."~".$pdi->referencia_pago."~". number_format($pdi->monto, 2, '.', '')."~".$type_currency."~".$status;
                    /////////////////////////////Consulta PAYU/////////////////////////////

                    ////////////////////////tabla: Suplente_pago //////////////////////////
                    $pdi->firm = md5($firma_cadena);
                    $pdi->lapPaymentMethod = $paymentMethod;
                    $pdi->transactionId = $transactionId;
                    $pdi->type_currency = $type_currency;
                    $pdi->EstadoTransaccion = $status;
                    $pdi->estado = 1;

                    if ($status == 'APPROVED') {
                        $pdi->transactionState = 4;
                    } else if($status == 'DECLINED')
                    {
                        $pdi->transactionState = 6;
                    }else if($status == 'ERROR')
                    {
                        $pdi->transactionState = 104;
                    }else if($status == 'EXPIRED')
                    {
                        $pdi->transactionState = 5;
                    }else if($status == 'PENDING')
                    {
                        $pdi->transactionState = 7;
                    }
                    $pdi->save();
                    ////////////////////////tabla: Suplente_pago //////////////////////////

                    ///////////////////////Tabla: Suscripciones_pagos///////////////////////
                    $suscripcion_pago = SuscripcionPago::where('referencia',$pdi->referencia_pago)->first();
                    if ($suscripcion_pago) {
                        $suscripcion_pago->id_empresa = auth()->user()->empresa;
                        $suscripcion_pago->plan = $pdi->plan;
                        $suscripcion_pago->referencia = $pdi->referencia_pago;
                        $suscripcion_pago->meses = $pdi->meses;
                        $suscripcion_pago->suplentepago_id = $pdi->id;
                        $suscripcion_pago->monto = $pdi->monto;
                        $suscripcion_pago->medio_pago = $pdi->lapPaymentMethod;
                        $suscripcion_pago->save();

                        if ($pdi->transactionState == 4) {
                            $suscripcion_pago->estado = 1;
                            ////Como el estado es aprovado, nos dirijimos a guardar la suscripcion///////

                            ////////////////Tabla: Suscripciones ////////////////////
                            $mesesPagos = $suscripcion_pago->meses;
                            //$fecha_inicio = date('Y-m-d',strtotime($suscripcion_pago->created_at));
                            //$fecha_final = date('Y-m-d', strtotime($suscripcion_pago->created_at."+ $mesesPagos month"));

                            $suscripcion = Suscripcion::where('id_empresa',$suscripcion_pago->id_empresa)->first();

                            if($suscripcion){
                                $fecha_vencimiento = Carbon::now()->addMonth($mesesPagos);
                                $fecha_final = date('Y-m-d', strtotime(Carbon::now()."+ $mesesPagos month"));
                                $tmpFecha = Carbon::parse($suscripcion->fec_vencimiento);
                                if (!$tmpFecha->gte($fecha_vencimiento)){
                                    $suscripcion->fec_vencimiento = $fecha_final;
                                    $suscripcion->fec_corte = $fecha_final;
                                }
                                $suscripcion->fec_inicio = Carbon::now();
                                $suscripcion->updated_at = Carbon::now();
                                $suscripcion->save();

                            }else{
                                $fecha_inicio = date('Y-m-d',strtotime($suscripcion_pago->created_at));
                                $fecha_final = date('Y-m-d', strtotime($suscripcion_pago->created_at."+ $mesesPagos month"));
                                $suscripcion = New Suscripcion;
                                $suscripcion->id_empresa = $suscripcion_pago->id_empresa;
                                $suscripcion->fec_inicio = $fecha_inicio;
                                $suscripcion->fec_vencimiento = $fecha_final;
                                $suscripcion->created_at = Carbon::now();
                                $suscripcion->save();
                            }
                            ////////////////Tabla: Suscripciones ////////////////////


                        }else if($pdi->transactionState == 5)
                        {
                            $suscripcion_pago->estado = 4;
                        }else if($pdi->transactionState == 6)
                        {
                            $suscripcion_pago->estado = 3;
                        }else if($pdi->transactionState == 7)
                        {
                            $suscripcion_pago->estado = 2;
                        }else if($pdi->transactionState == 104)
                        {
                            $suscripcion_pago->estado = 5;
                        }

                        $suscripcion_pago->save();
                    }else{
                        $suscripcion_pago = new SuscripcionPago();
                        $suscripcion_pago->id_empresa = auth()->user()->empresa;
                        $suscripcion_pago->plan = $pdi->plan;
                        $suscripcion_pago->referencia = $pdi->referencia_pago;
                        $suscripcion_pago->meses = $pdi->meses;
                        $suscripcion_pago->suplentepago_id = $pdi->id;
                        $suscripcion_pago->monto = $pdi->monto;
                        $suscripcion_pago->medio_pago = $pdi->lapPaymentMethod;
                        $suscripcion_pago->save();

                        if ($pdi->transactionState == 4) {
                            $suscripcion_pago->estado = 1;
                            ////Como el estado es aprovado, nos dirijimos a guardar la suscripcion///////

                            ////////////////Tabla: Suscripciones ////////////////////
                            $mesesPagos = $suscripcion_pago->meses;
                            //$fecha_inicio = date('Y-m-d',strtotime($suscripcion_pago->created_at));
                            //$fecha_final = date('Y-m-d', strtotime($suscripcion_pago->created_at."+ $mesesPagos month"));

                            $suscripcion = Suscripcion::where('id_empresa',$suscripcion_pago->id_empresa)->first();

                            if($suscripcion){
                                $fecha_vencimiento = Carbon::now()->addMonth($mesesPagos);
                                $fecha_final = date('Y-m-d', strtotime(Carbon::now()."+ $mesesPagos month"));
                                $tmpFecha = Carbon::parse($suscripcion->fec_vencimiento);
                                if (!$tmpFecha->gte($fecha_vencimiento)){
                                    $suscripcion->fec_vencimiento = $fecha_final;
                                    $suscripcion->fec_corte = $fecha_final;
                                }
                                $suscripcion->fec_inicio = Carbon::now();
                                $suscripcion->updated_at = Carbon::now();
                                $suscripcion->save();

                            }else{
                                $fecha_inicio = date('Y-m-d',strtotime($suscripcion_pago->created_at));
                                $fecha_final = date('Y-m-d', strtotime($suscripcion_pago->created_at."+ $mesesPagos month"));
                                $suscripcion = New Suscripcion;
                                $suscripcion->id_empresa = $suscripcion_pago->id_empresa;
                                $suscripcion->fec_inicio = Carbon::now();
                                $suscripcion->fec_vencimiento = $fecha_final;
                                $suscripcion->created_at = Carbon::now();
                                $suscripcion->save();
                            }
                            ////////////////Tabla: Suscripciones ////////////////////


                        }else if($pdi->transactionState == 5)
                        {
                            $suscripcion_pago->estado = 4;
                        }else if($pdi->transactionState == 6)
                        {
                            $suscripcion_pago->estado = 3;
                        }else if($pdi->transactionState == 7)
                        {
                            $suscripcion_pago->estado = 2;
                        }else if($pdi->transactionState == 104)
                        {
                            $suscripcion_pago->estado = 5;
                        }

                        $suscripcion_pago->save();
                    }
                    ///////////////////////Tabla: Suscripciones_pagos///////////////////////
                }
            }
            return response()->json("Hecho");
        }
        else
        {
            return response()->json("No hay pedidos con informacion faltante");
        }
    }

}
