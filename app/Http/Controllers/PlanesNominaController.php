<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  stdClass;
use Auth;
use DB;
use App\Empresa;
use Carbon\Carbon;
use App\PlanesNomina;
use App\SuscripcionPagoNomina;
use App\SuscripcionNomina;

class PlanesNominaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
        view()->share(['inicio' => 'master', 'seccion' => 'nomina', 'subseccion' => 'planes-nomina', 'title' => 'Planes Nómina Electrónica', 'icon' => '']);
    }

    public function respuestapagowompi()
    {
        $this->getAllPermissions(Auth::user()->id);
        return view('nomina.respuestapago');
    }

    public function store_respuesta_wompi(Request $request)
    {
        $suplente_pago = PlanesNomina::where('referencia_pago', $_REQUEST['reference'])->where('EstadoTransaccion', 'PENDING')->first();

        if ($suplente_pago) {
            if ($_REQUEST['status'] == 'APPROVED') {
                $suplente_pago->EstadoTransaccion = "APPROVED";
                $suplente_pago->estado = 1;
                $suplente_pago->transactionState = 1;

                $suscripcion_pago = new SuscripcionPagoNomina();
                $suscripcion_pago->id_empresa = auth()->user()->empresa;
                $suscripcion_pago->plan = $suplente_pago->plan;
                $suscripcion_pago->referencia = $suplente_pago->referencia_pago;
                $suscripcion_pago->personalizado = $suplente_pago->personalizado;
                $suscripcion_pago->meses = $suplente_pago->meses;
                $suscripcion_pago->estado = 1;
                $suscripcion_pago->suplentepago_id = $suplente_pago->id;
                $suscripcion_pago->monto = $suplente_pago->monto;
                $suscripcion_pago->medio_pago = $suplente_pago->medio_pago;
                $suscripcion_pago->tipo_pago = $suplente_pago->tipo_pago;
                $suscripcion_pago->medio_pago = $suplente_pago->tipo_pago;
                $suscripcion_pago->save();

                $mesesPagos = $suscripcion_pago->meses;

                $suscripcion = SuscripcionNomina::where('id_empresa', $suscripcion_pago->id_empresa)->first();

                if ($suscripcion) {
                    $fecha_vencimiento = Carbon::now()->addMonth($mesesPagos);
                    $fecha_final = date('Y-m-d', strtotime(Carbon::now()."+ $mesesPagos month"));
                    $tmpFecha = Carbon::parse($suscripcion->fec_vencimiento);
                    if (!$tmpFecha->gte($fecha_vencimiento)) {
                        $suscripcion->fec_vencimiento = $fecha_final;
                        $suscripcion->fec_corte = $fecha_final;
                    }
                    $suscripcion->fec_inicio = Carbon::now();
                    $suscripcion->updated_at = Carbon::now();
                    $suscripcion->save();
                } else {
                    $fecha_inicio = date('Y-m-d', strtotime($suscripcion_pago->created_at));
                    $fecha_final = date('Y-m-d', strtotime($suscripcion_pago->created_at."+ $mesesPagos month"));
                    $suscripcion = new SuscripcionNomina();
                    $suscripcion->id_empresa = $suscripcion_pago->id_empresa;
                    $suscripcion->fec_inicio = $fecha_inicio;
                    $suscripcion->fec_vencimiento = $fecha_final;
                    $suscripcion->fec_corte = $fecha_final;
                    $suscripcion->created_at = Carbon::now();
                    $suscripcion->save();
                }

                $empresa = Empresa::find(auth()->user()->empresa);
                $empresa->nomina = 1;
                $empresa->save();

                $mensaje = 'Pago Aprobado';
                $success = true;
                $type = 'success';
            } elseif ($_REQUEST['status'] == 'DECLINED') {
                $suplente_pago->EstadoTransaccion = "DECLINED";
                $suplente_pago->estado = 2;
                $suplente_pago->transactionState = 2;

                $suscripcion_pago = new SuscripcionPagoNomina();
                $suscripcion_pago->id_empresa = auth()->user()->empresa;
                $suscripcion_pago->plan = $suplente_pago->plan;
                $suscripcion_pago->referencia = $suplente_pago->referencia_pago;
                $suscripcion_pago->personalizado = $suplente_pago->personalizado;
                $suscripcion_pago->meses = $suplente_pago->meses;
                $suscripcion_pago->estado = 2;
                $suscripcion_pago->suplentepago_id = $suplente_pago->id;
                $suscripcion_pago->monto = $suplente_pago->monto;
                $suscripcion_pago->tipo_pago = $suplente_pago->tipo_pago;
                $suscripcion_pago->medio_pago = $suplente_pago->tipo_pago;
                $suscripcion_pago->save();

                $mensaje = 'Pago Declinado';
                $success = true;
                $type = 'error';
            } elseif ($_REQUEST['status']=='VOIDED') {
                $suplente_pago->EstadoTransaccion = "VOIDED";
                $suplente_pago->estado = 3;
                $suplente_pago->transactionState = 3;

                $suscripcion_pago = new SuscripcionPagoNomina();
                $suscripcion_pago->id_empresa = auth()->user()->empresa;
                $suscripcion_pago->plan = $suplente_pago->plan;
                $suscripcion_pago->referencia = $suplente_pago->referencia_pago;
                $suscripcion_pago->personalizado = $suplente_pago->personalizado;
                $suscripcion_pago->meses = $suplente_pago->meses;
                $suscripcion_pago->estado = 3;
                $suscripcion_pago->suplentepago_id = $suplente_pago->id;
                $suscripcion_pago->monto = $suplente_pago->monto;
                $suscripcion_pago->tipo_pago = $suplente_pago->tipo_pago;
                $suscripcion_pago->medio_pago = $suplente_pago->tipo_pago;
                $suscripcion_pago->save();

                $mensaje = 'Pago Anulado';
                $success = true;
                $type = 'warning';
            } elseif ($_REQUEST['status']=='PENDING') {
                $suplente_pago->EstadoTransaccion = "PENDING";
                $suplente_pago->estado = 0;
                $suplente_pago->transactionState = 0;

                $suscripcion_pago = new SuscripcionPagoNomina();
                $suscripcion_pago->id_empresa = auth()->user()->empresa;
                $suscripcion_pago->plan = $suplente_pago->plan;
                $suscripcion_pago->referencia = $suplente_pago->referencia_pago;
                $suscripcion_pago->personalizado = $suplente_pago->personalizado;
                $suscripcion_pago->meses = $suplente_pago->meses;
                $suscripcion_pago->estado = 0;
                $suscripcion_pago->suplentepago_id = $suplente_pago->id;
                $suscripcion_pago->monto = $suplente_pago->monto;
                $suscripcion_pago->tipo_pago = $suplente_pago->tipo_pago;
                $suscripcion_pago->medio_pago = $suplente_pago->tipo_pago;
                $suscripcion_pago->save();

                $mensaje = 'Pago Pendiente';
                $success = true;
                $type = 'warning';
            }

            $suplente_pago->tipo_pago =  $_REQUEST['tipo_pago'];
            $suplente_pago->transactionId = $_REQUEST['transactionId'];
            $suplente_pago->type_currency = 'COP';
            $suplente_pago->save();
        }else {
            $mensaje = 'La validación del pago ya ha sido efectuada con anterioridad.';
            $success = false;
            $type = 'error';
        }
        return response()->json([
            'success' => $success,
            'mensaje' => $mensaje,
            'type'    => $type
        ]);
    }
}
