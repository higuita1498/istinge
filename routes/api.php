<?php

use App\Contacto;
use App\Contrato;
use App\Empresa;
use App\Http\Controllers\ContratosController;
use App\Model\Ingresos\Factura;
use App\Model\Ingresos\FacturaRetencion;
use App\Model\Ingresos\ItemsFactura;
use App\Model\Ingresos\NotaCredito;
use App\NumeracionFactura;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('contrato-digital/{key}', function (Request $request, $key) {

    $contacto = Contacto::where('referencia_asignacion', $key)->first();
    if($contacto){
        $contacto->firma_isp = $request->firma_isp;
        $contacto->fecha_isp = date('Y-m-d');
        $contacto->referencia_asignacion = null;
        $contacto->save();

        $empresa = Empresa::find($contacto->empresa);
        $formulario = false;
        $title = $empresa->nombre;
        view()->share(['seccion' => 'contratos', 'subseccion' => 'asignaciones', 'title' => 'Asignaciones', 'icon' =>'fas fa-file-contract']);
        return view('asignaciones.firma')->with(compact('contacto', 'title', 'empresa', 'formulario'));
    }
    abort(403, 'ACCIÓN NO AUTORIZADA');
})->name('asignaciones.store_firma');

Route::get('getInterfaces/{mikrotik}', 'Controller@getInterfaces');
Route::get('getDetails/{cliente}/{contrato?}', 'Controller@getDetails');
Route::get('getPlanes/{mikrotik}', 'Controller@getPlanes');
Route::get('{empresa}/getDataSearch/{key}', 'Controller@getAllData');
Route::get('getMigracion/{mikrotik}', 'Controller@getMigracion');
Route::get('getPing/{rango}', 'Controller@getPing');
Route::get('getNotificaciones', 'Controller@getNotificaciones');
Route::get('getIps/{mikrotik}', 'Controller@getIps');
Route::get('getGrupo/{grupo}', 'Controller@getGrupo');
Route::get('getSegmentos/{mikrotik}', 'Controller@getSegmentos');
Route::get('getContracts/{id}', 'Controller@getContracts');
Route::get('getSubnetting/{ip_address}/{prefijo}', 'Controller@getSubnetting');
Route::get('habilitarContratos/{fecha}', 'CronController@habilitarContratos');
Route::get('getMAC/{mk}/{ip}', 'Controller@getMAC');

/* api whatsive */

Route::post('whatsapp/{action}', 'WhatsappController@whatsappApi');
Route::post('uploadfile', 'WhatsappController@whatsappUpload')->name("uploadFile");

/** EVENTOS WOMPI **/
Route::post('pagos/wompi', 'CronController@eventosWompi');

/** EVENTOS PAYU **/
Route::post('pagos/payu', 'CronController@eventosPayu');

/** EVENTOS EPAYCO **/
Route::post('pagos/epayco', 'CronController@eventosEpayco');

/** EVENTOS COMBOPAY **/
Route::post('pagos/combopay', 'CronController@eventosCombopay');

/** EVENTOS TOPPAY **/
Route::post('pagos/toppay', 'CronController@eventosTopPay');

/**
 * Mostrar los datos de la factura mediante la llave unica asignada en el método
 * facturasController@enviar
 */
Route::get('facturaElectronica/{key}', function ($key) {
    $factura     = Factura::where('nonkey', $key)->get()->first();
    if(!$factura){
        return abort(419);
    }
    $limitDate   = (Carbon::parse($factura->created_at))->addHour();
    $actualDate  = Carbon::now();
    $noEdit      = (( $limitDate->greaterThanOrEqualTo($actualDate) && $factura->modificado == 0)? false: true);
    $mody        = $factura->modificado == 1 ? true : false;
    if ($factura){
        $factura = Factura::find($factura->id);
        $empresa = Empresa::find($factura->empresa);
        return view('facturas.landing')->with(compact('factura', 'empresa', 'key', 'noEdit', 'mody'));
    }
    return abort(419);
});


Route::get('facturaElectronica/{key}/pdf', function ($key) {
    $tipo1=$tipo = 'original';

    /**
     * toma en cuenta que para ver los mismos
     * datos debemos hacer la misma consulta
     **/

    $factura = Factura::where('nonkey', $key)->first();
    $empresa = Empresa::find($factura->empresa);
    if($factura->tipo == 1){
        view()->share(['title' => 'Imprimir Factura']);
        if ($tipo<>'original') {
            $tipo='Copia Factura de Venta';
        }else{
            $tipo='Factura de Venta Original';
        }
    }elseif($factura->tipo == 3){
        view()->share(['title' => 'Imprimir Cuenta de Cobro']);
        if ($tipo<>'original') {
            $tipo='Cuenta de Cobro Copia';
        }else{
            $tipo='Cuenta de Cobro Original';
        }

    }

    $resolucion =  NumeracionFactura::where('empresa', $factura->empresa)->latest()->first();

    if ($factura) {

        $items = ItemsFactura::where('factura',$factura->id)->get();
        $itemscount=ItemsFactura::where('factura',$factura->id)->count();
        $retenciones = FacturaRetencion::where('factura', $factura->id)->get();

        $pdf = PDF::loadView('pdf.facturaAPI', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones',
            'resolucion', 'empresa'));
        return  response ($pdf->stream())->withHeaders(['Content-Type' =>'application/pdf',]);

    }
})->name('imprimirFe');

Route::get('facturaElectronica/{key}/xml', function ($key) {
    $FacturaVenta = Factura::where('nonkey', $key)->first();
    $ResolucionNumeracion = NumeracionFactura::where('empresa',$FacturaVenta->empresa)->where('preferida',1)->first();

    $infoEmpresa = Empresa::find($FacturaVenta->empresa);
    $data['Empresa'] = $infoEmpresa->toArray();

    $retenciones = FacturaRetencion::where('factura', $FacturaVenta->id)->get();

    $impTotal = 0;

    foreach ($FacturaVenta->totalAPI($FacturaVenta->empresa)->imp as $totalImp){
        if(isset($totalImp->total)){
            $impTotal = $totalImp->total;
        }
    }
    $items = ItemsFactura::where('factura',$FacturaVenta->id)->get();

    $CUFEvr = $FacturaVenta->info_cufeAPI($FacturaVenta->id, $impTotal, $FacturaVenta->empresa);

    $infoCliente = Contacto::find($FacturaVenta->cliente);
    $data['Cliente'] = $infoCliente->toArray();

    $responsabilidades_empresa = DB::table('empresa_responsabilidad as er')
        ->join('responsabilidades_facturacion as rf','rf.id','=','er.id_responsabilidad')
        ->select('rf.*')
        ->where('er.id_empresa',$FacturaVenta->empresa)
        ->get();

    return $xml = response()->view('templates.xmlAPI.01',compact('infoEmpresa','CUFEvr','ResolucionNumeracion','FacturaVenta', 'data','items','retenciones','responsabilidades_empresa'))->header('Cache-Control', 'public')
        ->header('Content-Description', 'File Transfer')
        ->header('Content-Disposition', 'attachment; filename=FV-'.$FacturaVenta->codigo.'.xml')
        ->header('Content-Transfer-Encoding', 'binary')
        ->header('Content-Type', 'text/xml');
})->name('xmlFe');

/**
 * Se realizan los cambios correspondientes a seleccionar un estatus de respuesta la factura
 * En cualquier caso, la factura ha sido modificada por el usuario, posteriormente ya no podrá
 */
Route::post('response', function (Request $request){
    $factura = Factura::where('nonkey', $request->nonkey)->get()->first();
    $factura = Factura::find($factura->id);
    $empresa = Empresa::find($factura->empresa);
    if ($request->statusdian == 0){
        $factura->statusdian        = $request->statusdian;
        $factura->observacionesdian = $request->observacionesdian;
        $status = "RECHAZADO";
    }else{
        $factura->statusdian        = $request->statusdian;
        $status = "ACEPTADO";
    }
    $factura->modificado = 1;
    $save = $factura->save();
    $empresa = $empresa->nombre;
    return view('facturas.messageLanding')->with(compact('empresa', 'status'));
})->name('saveFe');

Route::get('NotaCreditoElectronica/{id}', function ($id) {
    $nota = NotaCredito::find($id);
    $empresa = Empresa::find($nota->empresa);
    return view('notascredito.landing')->with(compact('nota', 'empresa'));
});

/**
 * FIRMA DIGITAL
 */
 Route::get('contrato-digital/{key}', function ($key) {
     $contacto = Contacto::where('referencia_asignacion', $key)->first();

     if($contacto){
         $empresa = Empresa::find($contacto->empresa);
         $title = $empresa->nombre;
         view()->share(['seccion' => 'contratos', 'subseccion' => 'asignaciones', 'title' => 'Asignaciones', 'icon' =>'fas fa-file-contract']);
         $formulario = true;
         return view('asignaciones.firma')->with(compact('contacto', 'title', 'empresa', 'formulario'));
     }
     abort(403, 'ACCIÓN NO AUTORIZADA');
 });


Route::get('deudacontrato/{contro_nro}', function ($contro_nro) {
    $contrato = Contrato::where('nro' , $contro_nro)->first();
    if($contrato){
        $deuda = "$" . App\Funcion::Parsear($contrato->deudaFacturas());

        return response()->json(['data' => $deuda, 'status' => 200]);
    }else{
        return response()->json(['status' => 400, 'message' => 'No se encontraron datos']);
    }
});
