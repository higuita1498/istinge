<?php

namespace App\Http\Controllers;

use App\Suscripcion;
use App\SuscripcionPago;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\DB;
use App\User;

class SuscripcionController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        view()->share(['seccion' => 'suscripcion', 'title' => 'Suscripciones', 'icon' =>'fas fa-money-bill-wave']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $this->getAllPermissions(Auth::user()->id);
        $suscripciones = Suscripcion::all();
        return view('suscripciones.suscripcionesIndex')->with(compact('suscripciones'));
    }

    public function indexPagos(){
        if(Auth::user()->rol == 1){
            $suscripcionesPagos = SuscripcionPago::all();
            $certificado = Suscripcion::where('id_empresa',1)->first();
            $sw = 1;
            return view('suscripciones.pagosIndex')->with(compact('suscripcionesPagos','certificado','sw'));
        }else{
            $this->getAllPermissions(Auth::user()->id);
            $suscripcionesPagos = SuscripcionPago::where('id_empresa',Auth::user()->empresa)
            ->get();
            $sw = 0;
            $certificado = Suscripcion::where('id_empresa',auth()->user()->empresa)->first();
            return view('suscripciones.pagosIndex')->with(compact('suscripcionesPagos','certificado','sw'));
        }
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $suscripcionPago = new SuscripcionPago();
        $suscripcionPago->id_empresa = Auth::user()->empresa;
        $suscripcionPago->plan = $request->tipo_plan;
        $suscripcionPago->tipo_pago = $request->tipo_pago;
        $suscripcionPago->referencia = $request->nro_ref;
        $suscripcionPago->meses = $request->meses;
        $suscripcionPago->estado = 0;
        $suscripcionPago->monto = $request->monto;
        $suscripcionPago->save();

        $mensaje='Pago realizado con Exito.';

        return redirect('empresa/suscripcion/pagos')->with('success', $mensaje);
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

    public function aprobarPago($id){

        $suscripcionPagos = SuscripcionPago::find($id);

        $mesesPagos = $suscripcionPagos->meses;

        $fecha_inicio = date('Y-m-d',strtotime($suscripcionPagos->created_at));
        $fecha_final = date('Y-m-d', strtotime($suscripcionPagos->created_at."+ $mesesPagos month"));


        if($suscripcionPagos){
            DB::table('suscripciones_pagos')->where('id', $id)->update(['estado' => 1]);
            $suscripcion = DB::table('suscripciones')->where('id_empresa',$suscripcionPagos->id_empresa)->count();

            if($suscripcion){
                DB::table('suscripciones')->update([
                    'fec_inicio' => $fecha_inicio,
                    'fec_vencimiento' => $fecha_final,
                    'fec_corte' => $fecha_final,
                    'updated_at' => Carbon::now()
                ])->where([
                    'id_empresa',$suscripcionPagos->id_empresa,
                    'id', $suscripcionPagos->id
                ]);

            }else{

                DB::table('suscripciones')->insert([
                    'id_empresa' => $suscripcionPagos->id_empresa,
                    'fec_inicio' => $fecha_inicio,
                    'fec_vencimiento' => $fecha_final,
                    'fec_corte' => $fecha_final,
                    'created_at' => Carbon::now()
                ]);
            }


            $mensaje = 'Se aprobo el pago satisfactoriamente!';
        }

        return back()->with('success', $mensaje);


    }

    public function prorrogaForm($id)
    {
        $suscripcion = Suscripcion::find($id);
        return view('suscripciones.modal.prorroga')->with(compact('suscripcion'));
    }

    public function prorrogaUpdate(Request $request){

       $suscripcion = Suscripcion::find($request->id_suscripcion);
       $fecha_prorroga = date('Y-m-d', strtotime($suscripcion->fec_vencimiento."+ $request->prorroga days"));

       $suscripcion->update([
           'fec_vencimiento' => $fecha_prorroga,
           'fec_corte' => $fecha_prorroga,
           'prorroga' => $request->prorroga,
           'updated_at' => Carbon::now()
       ]);
       $mensaje = 'Se agrego satisfacotriamente la prorroga';

       return back()->with('success', $mensaje);

   }
   
   public function ilimitado($empresa)
   {
       $suscripcion             = Suscripcion::find($empresa);
       $suscripcion->ilimitado  = !$suscripcion->ilimitado;
       $suscripcion->save();
       $mensaje = ($suscripcion->ilimitado) ? 'Se ha activado el plan ilimitado' : 'Se ha desactivado el plan ilimitado';

       return back()->with('success', $mensaje);
   }
   
   public function anular($id)
    {
        $suscripcion = SuscripcionPago::find($id);
        $suscripcion->estado = 10;
        $suscripcion->save();
        return back();
    }

    public function activar($id)
    {
        $suscripcion = SuscripcionPago::find($id);
        $suscripcion->estado = 1;
        $suscripcion->save();
        return back();
    }

   public function rechazarPago(){

   }

}
