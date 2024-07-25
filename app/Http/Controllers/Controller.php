<?php

namespace App\Http\Controllers;

use App\Banco;
use App\Categoria;
use App\Contacto;
use App\Cotizacion;
use App\Model\Gastos\FacturaProveedores;
use App\Model\Gastos\Gastos;
use App\Model\Gastos\NotaDedito;
use App\Model\Gastos\Ordenes_Compra;
use App\Model\Ingresos\Factura;
use App\Model\Ingresos\Ingreso;
use App\Model\Ingresos\IngresosFactura;
use App\Model\Ingresos\NotaCredito;
use App\Model\Ingresos\Remision;
use App\Model\Inventario\Bodega;
use App\Model\Inventario\Inventario;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Empresa;  use Auth; use App\Movimiento;
use DB;
use Illuminate\Support\Facades\Log;
use App\Radicado;
use App\NumeracionFactura;
use App\Numeracion;
use App\Contrato;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Ping;
use App\Segmento;
use App\FormaPago;
use App\PucMovimiento;

include_once(app_path() .'/../public/routeros_api.class.php');
use RouterosAPI;
use App\Mikrotik;
use App\Model\Ingresos\FacturaRetencion;
use App\Model\Ingresos\ItemsFactura;
use App\PlanesVelocidad;
use Barryvdh\DomPDF\Facade as PDF;
use Mail;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function precision($valor){
    	return round($valor, Auth::user()->empresa()->precision);
    }

    public function normaliza($cadena){

            $cadena = trim($cadena);

            $cadena = str_replace(
                array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
                array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
                $cadena
            );

            $cadena = str_replace(
                array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
                array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
                $cadena
            );

            $cadena = str_replace(
                array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
                array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
                $cadena
            );

            $cadena = str_replace(
                array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
                array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
                $cadena
            );

            $cadena = str_replace(
                array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
                array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
                $cadena
            );

            $cadena = str_replace(
                array('ñ', 'Ñ', 'ç', 'Ç'),
                array('n', 'N', 'c', 'C',),
                $cadena);
            $cadena = str_replace(
                array("\\", "¨", "º", "~",
                     "#", "@", "|", "!", "\"",
                     "·", "$", "%", "&", "/",
                     "(", ")", "?", "'", "¡",
                     "¿", "[", "^", "<code>", "]",
                     "+", "}", "{", "¨", "´",
                     ">", "< ", ";", ",", ":",
                     ".", " "),
                ' ',
                $cadena
            );

            return $cadena;
    }

    /*
    $modulo = Pagos recibidos, PG Remisiones.. etc
    $id = id de pagos recibidos, pgremisiones... etc
    $banco = 123 (solo el id del banco)
    $tipo = 1 Entrada, 2 salida
    $generoSaldoFavor = con esto podemos identificar si una factura recibio mas dinero y tiene saldo a favor.
    */
    public function up_transaccion($modulo, $id, $banco, $contacto, $tipo, $saldo, $fecha, $descripcion,$generoSaldoFavor=null){

        $empresa = Auth::user()->empresa;
        $movimiento=new Movimiento;
        $probableMovimiento = Movimiento::where('modulo', 7)->where('id_modulo', $id)->where('estatus',1)->first();

        //Caso1: Cuando cambiamos de un saldo a favor a un pago normal, necesitamos buscarlo por el modulo.
        $regis=Movimiento::where('modulo', $modulo)->where('id_modulo', $id)->where('estatus',1)->first();

        if(!$regis && $probableMovimiento && $generoSaldoFavor == null){
            $movimiento=$probableMovimiento;
        }

        if ($regis) {
            $movimiento=$regis;
        }

        //Caso1: Se esta pasando de un saldo a favor a un movimiento normal, se devuelve el dinero al cliente de saldo a favor.
        if($probableMovimiento && $probableMovimiento->tipo == 2 && $modulo != 7){
            $conta = Contacto::Find($probableMovimiento->contacto);
            $conta->saldo_favor = $conta->saldo_favor + $probableMovimiento->saldo;
            $conta->save();
        }

        if($modulo == 7){
            $banco = Banco::where('empresa',$empresa)->where('nombre','like','Saldos a favor')->first()->id;
        }

        $movimiento->empresa=$empresa;
        $movimiento->banco=$banco;
        $movimiento->contacto=$contacto;
        $movimiento->tipo=$tipo;
        $movimiento->saldo=$saldo;
        $movimiento->fecha=$fecha;
        $movimiento->modulo=$modulo;
        $movimiento->id_modulo=$id;
        $movimiento->descripcion=$id . " " . $descripcion;
        $movimiento->save();
    }

    public function destroy_transaccion($modulo, $id){

         //Eliminando el saldo a favor que se genero con el recibo.
         if(Movimiento::where('id_modulo', $id)->where('modulo', 7)->first()){
            $MovimientoSaldoFavor = Movimiento::where('id_modulo', $id)->where('modulo', 7)->first();
            $ingreso = Ingreso::Find($id);

            //Tambien restamos este valor de la tabla de contactos.
            $contacto = Contacto::find($MovimientoSaldoFavor->contacto);
            //si el movimiento es una salida del banco de saldos a favor entonces debe volver a sumar.
            if($MovimientoSaldoFavor->tipo == 2 && $MovimientoSaldoFavor->estatus != 2){
                $contacto->saldo_favor = $contacto->saldo_favor + $MovimientoSaldoFavor->saldo;
                $ingreso->valor_anticipo = $ingreso->valor_anticipo + $MovimientoSaldoFavor->saldo;
            }else{
                $contacto->saldo_favor = $contacto->saldo_favor - $MovimientoSaldoFavor->saldo;
                $ingreso->valor_anticipo = $ingreso->valor_anticipo - $MovimientoSaldoFavor->saldo;
            }
            $MovimientoSaldoFavor->delete();
            $contacto->save();
        }

        Movimiento::where('modulo', $modulo)->where('id_modulo', $id)->delete();
    }

    public function chage_status_transaccion($modulo, $id, $estatus){
        $regis=Movimiento::where('modulo', $modulo)->where('id_modulo', $id)->first();

        //Cuando el pago tambien tiene saldo a favor y requerimos anularlo.
        if(Movimiento::where('id_modulo', $id)->where('modulo', 7)->first()){
            $MovimientoSaldoFavor = Movimiento::where('id_modulo', $id)->where('modulo', 7)->first();
            $MovimientoSaldoFavor->estatus=$estatus;
            $MovimientoSaldoFavor->save();

            $ingreso = Ingreso::Find($id);

            //Tambien restamos este valor de la tabla de contactos.
            $contacto = Contacto::find($MovimientoSaldoFavor->contacto);
            if($estatus == 2){
                //si el movimiento es una salida del banco de saldos a favor entonces debe volver a sumar.
                if($MovimientoSaldoFavor->tipo == 2){
                    $contacto->saldo_favor = $contacto->saldo_favor + $MovimientoSaldoFavor->saldo;
                    $ingreso->valor_anticipo = $ingreso->valor_anticipo + $MovimientoSaldoFavor->saldo;
                }else{
                    $contacto->saldo_favor = $contacto->saldo_favor - $MovimientoSaldoFavor->saldo;
                    $ingreso->valor_anticipo = $ingreso->valor_anticipo - $MovimientoSaldoFavor->saldo;
                }
                //Tambien debe sumar sobre el recibo de caja que tenia el saldo a favor.
                // PucMovimiento::where('consecutivo_comprobante',$ingreso->nro)->update([
                //     ''
                // ]);

            }else{
                if($MovimientoSaldoFavor->tipo == 2){
                    $contacto->saldo_favor = $contacto->saldo_favor - $MovimientoSaldoFavor->saldo;
                    $ingreso->valor_anticipo = $ingreso->valor_anticipo - $MovimientoSaldoFavor->saldo;
                }else{
                    $contacto->saldo_favor = $contacto->saldo_favor + $MovimientoSaldoFavor->saldo;
                    $ingreso->valor_anticipo = $ingreso->valor_anticipo + $MovimientoSaldoFavor->saldo;
                }
            }
            $contacto->save();
            $ingreso->save();
        }

        if ($regis) {
            $movimiento=$regis;
            $movimiento->estatus=$estatus;
            $movimiento->save();
        }

    }

    public function change_out_in($modulo, $id, $tipo)
    {
       $movimiento =  Movimiento::where('modulo', $modulo)->where('id_modulo', $id)->first();
       $movimiento->tipo = $tipo;
       $movimiento->save();
    }

    public function tester54()
    {
        return 'ok';
    }

    public function getAllData($empresa, $key)
    {
        $request = $key;

        $data = array(
            'bank'          => $this->getAllBanks($empresa, $request),
            'contact'       => $this->getAllContacts($empresa ,$request),
            'invoices'      => $this->getAllInvoices($empresa, $request),
            'invoicesOut'   => $this->getAllInvoicesOut($empresa, $request),
            'billIn'        => $this->getAllBillIn($empresa, $request),
            'billOut'       => $this->getAllBillOut($empresa, $request),
            'warehouse'     => $this->getAllWarehouse($empresa, $request),
            'inventory'     => $this->getAllInventary($empresa, $request),
            'credit'        => $this->getAllCredit($empresa, $request),
            'debit'         => $this->getAllDebit($empresa, $request),
            'order'         => $this->getAllOrder($empresa, $request),
            'remission'     => $this->getAllRemission($empresa, $request),
            'quotation'     => $this->getAllQuotation($empresa, $request),
        );

        return json_encode($data);

    }

    private function getAllBanks($empresa, $request)
    {
        return Banco::where('empresa', $empresa)->where('nombre','like', "%$request%")->take(10)->get();
    }

    private function getAllContacts($empresa, $request)
    {
        $contactos =  Contacto::where('empresa',$empresa)
            ->where(function($query) use ($request){
                if(is_numeric($request)){
                    $query->where('nit', 'like', "$request%");
                }else{
                    $query->where('nombre', 'like', "$request%");
                }
            })
            ->take(10)->get();

        return $contactos;
    }

    private function getAllWarehouse($empresa, $request)
    {
        return Bodega::where('empresa',$empresa)->where('bodega','like', "%$request%")->take(10)->get();
    }

    private function getAllInventary($empresa, $request)
    {
        $inventario = Inventario::where('empresa', $empresa)->where('producto','like', "%$request%")->take(10)->get();
        $inventarioPorRef = Inventario::where('empresa', $empresa)->where('ref','like', "$request%")->take(10)->get();
        $array = array();
        foreach ($inventario as $item){
            $array[] = $item;
        }
        foreach ($inventarioPorRef as $item){
            $array[] = $item;
        }
        return $array;
    }

    private function getAllInvoices($empresa, $request)
    {
        //Codigo base tomado de facturasController@index
        $facturas=Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->join('items_factura as if', 'factura.id', '=', 'if.factura')
            ->leftJoin('vendedores as v', 'factura.vendedor', '=', 'v.id')
            ->select('factura.id', 'factura.codigo', 'factura.nro', DB::raw('c.nombre as nombrecliente'),
                'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.vendedor',
                'factura.emitida', DB::raw('v.nombre as nombrevendedor'))
            ->where('factura.empresa',$empresa)
            ->where(function ($query) use ($request){
                if (is_numeric($request) || $this->is_Alphanumeric($request)){
                    $query->where('factura.codigo', 'like', "$request%");
                }else{
                    $query->where('c.nombre', 'like', "$request%");
                }
            })
            ->where('tipo','!=',2)
            ->groupBy('if.factura')
            ->take(10)->get();
        return $facturas;
    }

    private function getAllInvoicesOut($empresa, $request)
    {
        //Codigo base tomado de facturaspController@index
        $facturas=FacturaProveedores::leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')
            ->join('items_factura_proveedor as if', 'factura_proveedores.id', '=', 'if.factura')
            ->select('factura_proveedores.id', 'factura_proveedores.tipo',  'factura_proveedores.codigo', 'factura_proveedores.nro',
                DB::raw('c.nombre as nombrecliente'), 'factura_proveedores.proveedor', 'factura_proveedores.fecha_factura',
                'factura_proveedores.vencimiento_factura', 'factura_proveedores.estatus')
            ->where('factura_proveedores.empresa',$empresa)
            ->where(function ($query) use ($request){
                if (is_numeric($request) || $this->is_Alphanumeric($request)){
                    $query->where('factura_proveedores.codigo', 'like', "$request%");
               }else{
                    $query->where('c.nombre', 'like', "%$request%");
                }
            })
            ->where('factura_proveedores.tipo',1)
            ->groupBy('if.factura')
            ->take(10)->get();
        return $facturas;
    }

    private function getAllBillOut($empresa, $request)
    {

        return Gastos::leftJoin('contactos as c', 'c.id','=','gastos.beneficiario')->where('gastos.empresa',$empresa)
            ->select('gastos.id', 'gastos.nro', 'gastos.beneficiario', 'c.nombre as nombrecliente')
            ->where(function ($query) use ($request){
                if (is_numeric($request) || $this->is_Alphanumeric($request)){
                    $query->where('gastos.nro', 'like', "$request%");
                }else{
                    $query->where('c.nombre', 'like', "$request%");
                }
            })->take(10)->get();

    }

    private function getAllBillIn($empresa, $request)
    {
        $ingresos = Ingreso::leftjoin('contactos as c', 'c.id', '=', 'ingresos.cliente')
            ->leftjoin('ingresos_factura as if', 'if.ingreso', '=', 'ingresos.id')
            ->join('bancos as b', 'b.id', '=', 'ingresos.cuenta')
            ->select('ingresos.*', DB::raw('if(ingresos.tipo=1, group_concat(if.factura), "")
               as detalle'), 'c.nombre as nombrecliente', 'b.nombre as banco')
            ->where('ingresos.empresa',$empresa)
            ->where(function ($query) use ($request){
                if (is_numeric($request) || $this->is_Alphanumeric($request)){
                    $query->where('ingresos.nro', 'like', "$request%");
                }else{
                    $query->where('c.nombre', 'like', "$request%");
                }
            })
            ->groupBy( 'ingresos.id')
            ->take(10)->get();
        return $ingresos;
    }

    private function getAllCredit($empresa, $request)
    {

        $credito = NotaCredito::leftjoin('contactos as c', 'c.id', '=', 'notas_credito.cliente')
            ->select('notas_credito.*', 'c.nombre as nombrecliente')
            ->where('notas_credito.empresa',$empresa)
            ->where(function ($query) use ($request){
                if (is_numeric($request) || $this->is_Alphanumeric($request)){
                    $query->where('notas_credito.nro', 'like', "$request%");
                }else{
                    $query->where('c.nombre', 'like', "$request%");
                }
            })
            ->take(10)->get();
        return $credito;
    }

    private function getAllDebit($empresa, $request)
    {
        return NotaDedito::leftjoin('contactos as c', 'c.id', '=', 'notas_debito.proveedor')->select('notas_debito.*',
            'c.nombre as nombrecliente')
            ->where('notas_debito.empresa', $empresa)
            ->where(function ($query) use ($request){
                if (is_numeric($request) || $this->is_Alphanumeric($request)){
                    $query->where('notas_debito.nro', 'like', "$request%");
                }else{
                    $query->where('c.nombre', 'like', "$request%");
                }
            })
            ->take(10)->get();

    }

    private function getAllOrder($empresa, $request)
    {
         return Ordenes_Compra::leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')
            ->join('items_factura_proveedor as if', 'factura_proveedores.id', '=', 'if.factura')
            ->select('factura_proveedores.id', 'factura_proveedores.orden_nro', DB::raw('c.nombre as nombrecliente'))
            ->where('factura_proveedores.empresa',$empresa)
            ->where('factura_proveedores.tipo',2)
            ->where(function($query) use ($request){
                if(is_numeric($request) || $this->is_Alphanumeric($request)){
                    $query->where('factura_proveedores.orden_nro', 'like',"$request%");
                }else{
                    $query->where('c.nombre', 'like', "$request%");
                }
            })
            ->WhereNotNull('factura_proveedores.orden_nro')
            ->groupBy('if.factura')
            ->take(10)->get();

    }

    private function getAllRemission($empresa, $request)
    {
         return Remision::join('contactos as c', 'remisiones.cliente', '=', 'c.id')
            ->join('items_remision as if', 'remisiones.id', '=', 'if.remision')
            ->leftjoin('vendedores as v','remisiones.vendedor','=','v.id')
            ->select('remisiones.id', 'remisiones.nro', DB::raw('c.nombre as nombrecliente') )
            ->where('remisiones.empresa', $empresa)
            ->where(function ($query) use ($request){
                if(is_numeric($request) || $this->is_Alphanumeric($request)){
                    $query->where('remisiones.nro', 'like',"$request%");
                }else{
                    $query->where('c.nombre', 'like', "$request%");
                }
            })
            ->groupBy('if.remision')
            ->take(10)->get();

    }

    private function getAllQuotation($empresa, $request)
    {
        return Cotizacion::leftjoin('contactos as c', 'factura.cliente', '=', 'c.id')
                    ->leftjoin('factura_contacto as fc', 'factura.id', '=', 'fc.factura')
                    ->join('items_factura as if', 'factura.id', '=', 'if.factura')
                    ->select('factura.id', 'factura.cot_nro', DB::raw('if(factura.cliente,c.nombre,fc.nombre) as nombrecliente'))
                    ->where('factura.empresa',$empresa)
                    ->where(function ($query) use($request){
                        if(is_numeric($request) || $this->is_Alphanumeric($request)){
                            $query->where('factura.cot_nro', 'like',"$request%");
                        }else{
                            $query->where('c.nombre', 'like', "$request%");
                        }
                     })
                    ->where('factura.tipo', 2)
                    ->take(10)->get();
    }

    private function is_Alphanumeric(String $str)
    {
        return preg_match('/[A-Za-z]/', $str) && preg_match('/[0-9]/', $str);
    }

    public function getAllPermissions($id)
    {
        if(Auth::user()->rol>=2){
            if (DB::table('permisos_usuarios')->select('id_permiso')->where('id_usuario', $id)->count() > 0 ) {
                $permisos = DB::table('permisos_usuarios')->select('id_permiso')->where('id_usuario', $id)->get();
                foreach ($permisos as $key => $value) {
                    $_SESSION['permisos'][$permisos[$key]->id_permiso] = '1';
                }
                return $_SESSION['permisos'];
            }
            else return null;
        }
    }

    public function EnviarDatosDian($xml)
    {
        $encoded = base64_encode($xml);
        $json = json_encode($encoded);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://apivp.efacturacadena.com/v1/vp/documentos/proceso/alianzas", //"https://apivp.efacturacadena.com/staging/vp-hab/documentos/proceso/alianzas",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Postman-Token: 13e97781-32ef-49b7-ad05-3461f465d410",
                "cache-control: no-cache",
                "efacturaAuthorizationToken:62808bf1-d446-46ee-8120-00162e95c059"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }


    public function getTechnicalKey($softwareCode, $accountCodeVendor, $accountCode)
    {
        $curl = curl_init();
        $url = "https://apivp.efacturacadena.com/v1/vp/consulta/rango-numeracion?softwareCode=" . $softwareCode . "&accountCodeVendor=" . $accountCodeVendor . "&accountCode=" . $accountCode . "";
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    //Metodo para devolver json de rspuesta para saber si existe o no un documento
    public function validateStatusDian($nitemisor, $idDocumento, $codigoTipo, $prefijo)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://apivp.efacturacadena.com/v1/vp/consulta/documentos?nit_emisor=" . $nitemisor . "&id_documento=" . $idDocumento . "&codigo_tipo_documento=" . $codigoTipo . "&prefijo=" . $prefijo . "",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "efacturaAuthorizationToken: 62808bf1-d446-46ee-8120-00162e95c059",
                "Content-Type: text/plain",
                "Partnership-Id: 1128464945"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

        //Metodo para devolver json de rspuesta para saber si existe o no una nota crédito
        public function validateStatusDianNota($nitemisor, $idDocumento, $codigoTipo)
        {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://apivp.efacturacadena.com/v1/vp/consulta/documentos?nit_emisor=" . $nitemisor . "&id_documento=" . $idDocumento . "&codigo_tipo_documento=" . $codigoTipo . "",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "efacturaAuthorizationToken: 62808bf1-d446-46ee-8120-00162e95c059",
                    "Content-Type: text/plain",
                    "Partnership-Id: 1128464945"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return $response;
        }

    public function change_pass($nombre,$identificacion){
        $curl = curl_init();
        $empresa = Empresa::find(1);

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/clients/?name_unaccent_cont=".$nombre."&per_page=100",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: ".$empresa->wispro
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    public function show_contract($id){
        $curl = curl_init();
        $empresa = Empresa::find(1);

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/contracts/?client_id_eq=".$id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: ".$empresa->wispro
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    public function consultar($codigo,$identificacion){
        $radicado=Radicado::where('codigo',$codigo)->first();

        Radicado::join('contactos as c', 'c.id', '=', 'radicados.cliente')->select('radicados.*', 'c.nombre', 'c.nit', 'c.email')-> where('radicados.codigo',$codigo)->where('c.nit', $identificacion)->first();
        return json_encode($radicado);
    }

    public function import_plans(){
        $empresa = Empresa::find(1);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/plans/?per_page=100",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: ".$empresa->wispro
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            //$data = @file_get_contents('import_planes.json');
            $plans = json_decode($response, true);
            $i=0;$j=0;
            $planes = $plans['data'];
            foreach ($planes as $plan) {
                $find = DB::table('planes')->where('id',$plan['id'])->get();

                if(count($find)>0){
                    DB::table('planes')->where('id',$plan['id'])->update([
                        "name" => $plan['name'],
                        "public_id" => $plan['public_id'],
                        "cir" => $plan['cir'],
                        "ceil_down_kbps" => $plan['ceil_down_kbps'],
                        "ceil_up_kbps" => $plan['ceil_up_kbps'],
                        "price" => $plan['price'],
                        "frequency_in_months" => $plan['frequency_in_months'],
                        "contracts_count" => $plan['contracts_count']
                    ]);
                    $j++;

                    /****/
                        DB::table('inventario')->where('ref',$plan['public_id'])->update([
                            "producto" => $plan['name'],
                            "descripcion" => $plan['name'],
                            "precio" => $plan['price'],
                            "id_impuesto" => 2
                        ]);
                    /****/
                }else{
                    /****/
                        $categoriaInventario = Categoria::where('empresa',1)->where('nro','=',1)->get();
                        $inventario = new Inventario;
                        $inventario->empresa= '1';
                        $inventario->producto= $plan['name'];
                        $inventario->ref= $plan['public_id'];
                        $inventario->descripcion= $plan['name'];
                        $inventario->precio= $plan['price'];
                        $inventario->tipo_producto= 2;
                        $inventario->unidad=1;
                        $inventario->nro=0;
                        $inventario->categoria = $categoriaInventario[0]->id;
                        $inventario->id_impuesto = 2;
                        $inventario->lista = 0;
                        $inventario->save();
                    /****/

                    DB::table('planes')->insert([
                        "id" => $plan['id'],
                        "name" => $plan['name'],
                        "public_id" => $plan['public_id'],
                        "cir" => $plan['cir'],
                        "ceil_down_kbps" => $plan['ceil_down_kbps'],
                        "ceil_up_kbps" => $plan['ceil_up_kbps'],
                        "price" => $plan['price'],
                        "frequency_in_months" => $plan['frequency_in_months'],
                        "contracts_count" => $plan['contracts_count'],
                        "item" => $inventario->id
                    ]);
                    $i++;
                }
            }
        }
        $mensaje = 'Sincronización de Planes Satisfactoria';
        $arrayPost['status']  = '200';
        $arrayPost['message'] = $mensaje;
        echo json_encode($arrayPost);
        exit;
    }

    public function import_clients(){
        $x=0;$j=0;$a=0;

        $curl = curl_init();
        $empresa = Empresa::find(1);

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/clients/?per_page=100",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: ".$empresa->wispro
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            $clients = json_decode($response, true);
            $pages = $clients['meta']['pagination']['total_pages'];
            //$pages = 1;
            $total_records = $clients['meta']['pagination']['total_records'];

            for ($i=0; $i <= $pages ; $i++) {
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/clients/?per_page=100&page=".$i,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => array("Authorization: ".$empresa->wispro),
                ));
                $response = curl_exec($curl); $err = curl_error($curl); curl_close($curl);
                $clients = json_decode($response, true);
                $clientes = $clients['data'];

                foreach ($clientes as $client) {
                    $find = DB::table('contactos')->where('UID',$client['id'])->get();

                    if(count($find)>0){
                        DB::table('contactos')->where('UID',$client['id'])->update([
                        "nombre" => $client['name'],
                        "email" => $client['email'],
                        "direccion" => $client['address'],
                        "telefono1" => $client['phone'],
                        "celular" => $client['phone_mobile'],
                        "nit" => $client['national_identification_number'],
                        ]);
                        $j++;
                    }else{
                        DB::table('contactos')->insert([
                        "UID" => $client['id'],
                        "nombre" => $client['name'],
                        "email" => $client['email'],
                        "direccion" => $client['address'],
                        "telefono1" => $client['phone'],
                        "celular" => $client['phone_mobile'],
                        "nit" => $client['national_identification_number'],
                        ]);
                        $x++;
                    }
                }
            }
            $mensaje = 'Sincronización de Clientes Satisfactoria';
            $arrayPost['status']  = '200';
            $arrayPost['message'] = $mensaje;
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function import_contracts(){
        $x=0;$j=0;$a=0;

        $curl = curl_init();
        $empresa = Empresa::find(1);

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/contracts/?per_page=100",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: ".$empresa->wispro
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            $clients = json_decode($response, true);
            $pages = $clients['meta']['pagination']['total_pages'];
            //$pages = 1;
            $total_records = $clients['meta']['pagination']['total_records'];

            for ($i=0; $i <= $pages ; $i++) {
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/contracts/?per_page=100&page=".$i,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => array("Authorization: ".$empresa->wispro),
                ));
                $response = curl_exec($curl); $err = curl_error($curl); curl_close($curl);
                $contracts = json_decode($response, true);
                $contratos = $contracts['data'];

                foreach ($contratos as $contract) {
                    $find = DB::table('contracts')->where('id',$contract['id'])->get();

                    if(count($find)>0){
                        DB::table('contracts')->where('id',$contract['id'])->update([
                            "public_id"               => $contract['public_id'],
                            "plan_id"                 => $contract['plan_id'],
                            "contrato_id"             => $contract['id'],
                            "client_id"               => $contract['client_id'],
                            "server_configuration_id" => $contract['server_configuration_id'],
                            "state"                   => $contract['state'],
                            "ip"                      => $contract['ip'],
                            "netmask"                 => $contract['netmask'],
                            "mac_address"             => $contract['mac_address'],
                            "latitude"                => $contract['latitude'],
                            "longitude"               => $contract['longitude'],
                            "address_street"          => $contract['address_street'],
                            "address_number"          => $contract['address_number'],
                            "address_city"            => $contract['address_city'],
                            "address_state"           => $contract['address_state'],
                            "address_country"         => $contract['address_country'],
                            "address_additional_data" => $contract['address_additional_data'],
                            "coverage_id"             => $contract['coverage_id'],
                            "details"                 => $contract['details'],
                            "created_at"              => $contract['created_at'],
                            "updated_at"              => $contract['updated_at'],
                        ]);
                        $j++;
                    }else{
                        DB::table('contracts')->insert([
                            "id"                      => $contract['id'],
                            "public_id"               => $contract['public_id'],
                            "plan_id"                 => $contract['plan_id'],
                            "contrato_id"             => $contract['id'],
                            "client_id"               => $contract['client_id'],
                            "server_configuration_id" => $contract['server_configuration_id'],
                            "state"                   => $contract['state'],
                            "ip"                      => $contract['ip'],
                            "netmask"                 => $contract['netmask'],
                            "mac_address"             => $contract['mac_address'],
                            "latitude"                => $contract['latitude'],
                            "longitude"               => $contract['longitude'],
                            "address_street"          => $contract['address_street'],
                            "address_number"          => $contract['address_number'],
                            "address_city"            => $contract['address_city'],
                            "address_state"           => $contract['address_state'],
                            "address_country"         => $contract['address_country'],
                            "address_additional_data" => $contract['address_additional_data'],
                            "coverage_id"             => $contract['coverage_id'],
                            "details"                 => $contract['details'],
                            "created_at"              => $contract['created_at'],
                            "updated_at"              => $contract['updated_at'],
                        ]);
                        $x++;
                    }
                }
            }
            $mensaje = 'Sincronización de Contratos Satisfactoria';
            $arrayPost['status']  = '200';
            $arrayPost['message'] = $mensaje;
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function generarFacturas($limit){
        set_time_limit(0);
        $i=0;
        $contactos = DB::select("SELECT C.id as cliente, I.id as item, I.ref, I.precio, Im.id AS impuesto FROM contactos AS C INNER JOIN contracts AS CS ON (C.UID = CS.client_id) INNER JOIN planes AS P ON (P.id = CS.plan_id) INNER JOIN inventario AS I ON (I.id = P.item) INNER JOIN impuestos AS Im ON (I.id_impuesto = Im.id) WHERE CS.state = 'enabled' LIMIT 500");

        foreach ($contactos as $contacto) {
            $factura = Factura::where('cliente',$contacto->cliente)->first();
            if(!$factura){
            $nro=NumeracionFactura::where('empresa',1)->where('preferida',1)->where('estado',1)->first();

            $inicio      = $nro->inicio;
            $nro->inicio += 1;

            $key       = Hash::make(date("H:i:s"));
            $toReplace = array('/', '$','.');
            $key       = str_replace($toReplace, "", $key);

            $factura                 = new Factura;
            $factura->nonkey         = $key;
            $factura->nro            = Factura::where('empresa',1)->where('tipo','!=',2)->count()+1;
            $factura->codigo         = $nro->prefijo.$inicio;
            $factura->numeracion     = $nro->id;
            $factura->plazo          = '';
            $factura->term_cond      = '';
            //$factura->facnotas       = '';
            $factura->empresa        = 1;
            $factura->cliente        = $contacto->cliente;
            $factura->tipo           = 1;
            $factura->fecha          = date('Y-m-d');
            $factura->vencimiento    = date('Y-m-d');
            $factura->observaciones  = mb_strtolower('$request->observaciones');
            $factura->vendedor       = 1;
            //$factura->lista_precios  = $request->lista_precios;
            $factura->bodega         = 1;
            //$factura->nro_remision   = $request->nro_remision;
            //$factura->tipo_operacion = 1;
            //$factura->ordencompra    = $request->ordencompra;
            $factura->save();
            $nro->save();
            $i++;

            /*exit;

            $bodega = Bodega::where('empresa',1)->where('status', 1)->where('id', $request->bodega)->first();
            if (!$bodega) {
                $bodega = Bodega::where('empresa',1)->where('status', 1)->first();
            }

            for ($i=0; $i < count($request->ref) ; $i++) {
                $impuesto = Impuesto::where('id', $request->impuesto[$i])->first();

                if($impuesto){
                    $impuesto->porcentaje = $impuesto->porcentaje;
                }else{
                    $impuesto->porcentaje = '';
                }

                $producto = Inventario::where('id', $request->item[$i])->first();

                if ($producto->tipo_producto==1) {
                    $ajuste=ProductosBodega::where('empresa', 1)->where('bodega', $bodega->id)->where('producto', $producto->id)->first();
                    if ($ajuste) {
                        $ajuste->nro-=$request->cant[$i];
                        $ajuste->save();
                    }
                }

                $items              = new ItemsFactura;
                $items->factura     = $factura->id;
                $items->producto    = $request->item[$i];
                $items->ref         = $request->ref[$i];
                $items->precio      = $this->precision($request->precio[$i]);
                $items->descripcion = $request->descripcion[$i];
                $items->id_impuesto = $request->impuesto[$i];
                $items->impuesto    = $impuesto->porcentaje;
                $items->cant        = $request->cant[$i];
                $items->desc        = $request->desc[$i];
                $items->save();
            }

            if ($request->retencion) {
                foreach ($request->retencion as $key => $value) {
                    if ($request->precio_reten[$key]) {
                        $retencion = Retencion::where('id', $request->retencion[$key])->first();
                        $reten = new FacturaRetencion;
                        $reten->factura=$factura->id;
                        $reten->valor=$this->precision($request->precio_reten[$key]);
                        $reten->retencion=$retencion->porcentaje;
                        $reten->id_retencion=$retencion->id;
                        $reten->save();
                    }
                }
            }

            $cant=Factura::where('empresa',1)->where('tipo','!=',2)->where('codigo','=',($nro->prefijo.$inicio))->count();
            if($cant==0){
                $nro->inicio-=1;
                $nro->save();
            }

            $mensaje='Se ha creado satisfactoriamente la factura';
            $print=false;

            if ($request->print) {
                $print=$factura->nro;
            }

            if ($request->send) {
                $this->enviar($factura->nro, null, false);
            }
            return redirect('empresa/facturas')->with('success', $mensaje)->with('print', $print)->with('codigo', $factura->id);*/
            }
        }
        echo 'Se han generado '.$i.' facturas';
    }

    public function consultar_invoice_old($identificacion){
        $contrato = Contrato::join('contactos as c', 'c.id', '=', 'contracts.client_id')->
        join('factura as f','f.cliente','c.id')->
        join('items_factura as if','f.id','if.factura')->
        select('contracts.id', 'contracts.public_id', 'contracts.state',  'contracts.fecha_corte', 'contracts.fecha_suspension', 'c.nombre', 'c.apellido1', 'c.apellido2', 'c.nit', 'c.celular', 'c.telefono1', 'c.email', 'f.fecha as emision', 'f.vencimiento', 'f.codigo as factura', 'if.precio as price')->
        where('c.nit', $identificacion)->
        where('f.estatus',1)->
        where('contracts.status',1)->
        get()->last();

        return json_encode($contrato);
    }

    public function consultar_invoice($identificacion){
        $contrato = Contrato::join('contactos as c', 'c.id', '=', 'contracts.client_id')->
        join('factura as f','f.cliente','c.id')->
        join('items_factura as if','f.id','if.factura')->
        select('contracts.id', 'contracts.public_id', 'contracts.state',  'contracts.fecha_corte', 'contracts.fecha_suspension', 'c.nombre', 'c.apellido1', 'c.apellido2', 'c.nit', 'c.celular', 'c.telefono1', 'c.email', 'f.fecha as emision', 'f.vencimiento', 'f.codigo as factura', 'if.impuesto', 'c.direccion', 'c.tip_iden', DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as price'))->
        where('c.nit', $identificacion)->
        where('f.estatus',1)->
        where('contracts.status',1)->
        groupBy('f.id', 'contracts.id')->
        get();

        if (count($contrato->factura) == 1){
            $contrato = Contrato::join('contactos as c', 'c.id', '=', 'contracts.client_id')->
            join('factura as f','f.cliente','c.id')->
            join('items_factura as if','f.id','if.factura')->
            select('contracts.id', 'contracts.public_id', 'contracts.state',  'contracts.fecha_corte', 'contracts.fecha_suspension', 'c.nombre', 'c.apellido1', 'c.apellido2', 'c.nit', 'c.celular', 'c.telefono1', 'c.email', 'f.fecha as emision', 'f.vencimiento', 'f.codigo as factura', 'if.impuesto', 'c.direccion', 'c.tip_iden', DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as price'))->
            where('c.nit', $identificacion)->
            where('f.estatus',1)->
            where('contracts.status',1)->
            groupBy('f.id', 'contracts.id')->
            get()->last();
        }

        if(is_null($contrato)){
            $contrato = Contacto::join('factura as f','f.cliente','contactos.id')->
            join('items_factura as if','f.id','if.factura')->
            select('contactos.nombre', 'contactos.apellido1', 'contactos.apellido2', 'contactos.nit', 'contactos.celular', 'contactos.telefono1', 'contactos.email', 'f.fecha as emision', 'f.vencimiento', 'f.codigo as factura', 'if.impuesto', 'contactos.direccion', 'contactos.tip_iden', DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as price'))->
            where('contactos.nit', $identificacion)->
            where('f.estatus',1)->
            groupBy('f.id')->
            get()->last();
        }

        $pasarelas = DB::table('integracion')->where('web', 1)->where('tipo', 'PASARELA')->where('status', 1)->get();

        return response()->json(['contrato' => $contrato, 'pasarelas' => $pasarelas]);

        return json_encode($contrato);
    }

    public function pagopayu(){
        return "Pago hecho";
    }

    public function respuestapayu(){
        if(isset($_REQUEST['referenceCode'])){
            $factura = Factura::where('codigo',$_REQUEST['referenceCode'])->where('estatus',1)->first();
            $contrato = Contacto::join('contracts AS CS','contactos.UID','=','CS.client_id')->join('planes AS P','P.id','=','CS.plan_id')->select('CS.*')->where('contactos.id', $factura->cliente)->first();
            $ApiKey = 'H3hg412ir2N5u33dSbXBL8CY4J';
            if ($factura) {
                $firma_cadena = $ApiKey."~".$_REQUEST['merchantId']."~".$_REQUEST['referenceCode']."~". number_format($_REQUEST['TX_VALUE'], 2, '.', '')."~".$_REQUEST['currency']."~".$_REQUEST['transactionState'];

                if ($_REQUEST['transactionState']==4) {
                    //$factura->EstadoTransaccion = "APPROVED";
                    $estadoTx = "Transacción aprobada";
                    $nro = Numeracion::where('empresa', 1)->first();
                    $caja = $nro->caja;

                    while (true) {
                        $numero = Ingreso::where('empresa', 1)->where('nro', $caja)->count();
                        if ($numero == 0) {
                            break;
                        }
                        $caja++;
                    }

                    $ingreso = new Ingreso;
                    $ingreso->nro = $caja;
                    $ingreso->empresa = 1;
                    $ingreso->cliente = $factura->cliente;
                    $ingreso->cuenta = 1;
                    $ingreso->tipo = 1;
                    $ingreso->fecha = date('Y-m-d');
                    $ingreso->observaciones = 'Pago por PayU: '.md5($firma_cadena);

                    if ($_REQUEST['lapPaymentMethod'] == "CREDIT_CARD") {
                        $ingreso->metodo_pago = 9;
                    }else if ($_REQUEST['lapPaymentMethod'] == "PSE" || $_REQUEST['lapPaymentMethod'] == "ACH" || $_REQUEST['lapPaymentMethod'] == "SPEI" || $_REQUEST['lapPaymentMethod'] == "BANK_REFERENCED"){
                        $ingreso->metodo_pago = 9;
                    }else if($_REQUEST['lapPaymentMethod'] == "DEBIT_CARD"){
                        $ingreso->metodo_pago = 9;
                    }else if($_REQUEST['lapPaymentMethod'] == "CASH" || $_REQUEST['lapPaymentMethod'] == "REFERENCED"){
                        $ingreso->metodo_pago = 9;
                    }

                    $factura->estatus = 0;
                    $contrato->state = 'enabled';

                    $ingreso->save();
                    $factura->save();
                    $contrato->save();

                    $ingresoFac = new IngresosFactura;
                    $ingresoFac->ingreso = $ingreso->id;
                    $ingresoFac->factura = $factura->id;
                    $ingresoFac->pagado = 0;
                    /*$ingresoFac->pago = $_REQUEST['TX_VALUE']-4000;*/
                    $ingresoFac->pago = $_REQUEST['TX_VALUE']-2800;
                    $ingresoFac->save();

                    $ingreso = Ingreso::find($ingreso->id);
                    $this->up_transaccion(1, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, $ingreso->descripcion);

                    $path = $contrato->contrato_id.'?state='.$contrato->state;

                    $curl = curl_init();
                    $empresa = Empresa::find(1);
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/contracts/".$path,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "PUT",
                        CURLOPT_HTTPHEADER => array(
                            "Authorization: ".$empresa->wispro
                        ),
                    ));

                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);
                    $mensaje = "Pago realizado Correctamente";

                }else if($_REQUEST['transactionState']==6){
                    //$factura->EstadoTransaccion = "DECLINED";
                    $estadoTx = "Transacción rechazada";
                    $factura->estatus = 1;
                    $mensaje= "Pago Rechazado, intentelo nuevamente";
                }else if($_REQUEST['transactionState']==104){
                    //$factura->EstadoTransaccion = "ERROR";
                    $estadoTx = "Error";
                    $factura->estatus = 1;
                    $mensaje= "Pago Rechazado, intentelo nuevamente";
                }else if($_REQUEST['transactionState']==7){
                    //$factura->EstadoTransaccion = "PENDING";
                    $estadoTx = "Transacción pendiente";
                    $factura->estatus = 1;
                    $mensaje= "Pago En proceso";
                }else if($_REQUEST['transactionState']==5){
                    //$factura->EstadoTransaccion = "EXPIRED";
                    $estadoTx = "Transacción expirada";
                    $factura->estatus = 1;
                    $mensaje= "Pago Rechazado, intentelo nuevamente";
                }
            }else{
                $mensaje= "Factura ya pagada";
            }
        }else{
            $mensaje= "Error en la transacción, intentelo nuevamente";
        }

        if(isset($_REQUEST['referenceCode']) && $factura){
            echo '<h2>Resumen Transacción</h2><table><tr><td>Estado de la transaccion</td><td>'.$mensaje.'</td></tr><tr><tr><td>ID de la transaccion</td><td>'.$_REQUEST['transactionId'].'</td></tr><tr><td>Referencia de la venta</td><td>'.$_REQUEST['reference_pol'].'</td></tr><tr><td>Referencia de la transaccion</td><td>'.$_REQUEST['referenceCode'].'</td></tr><tr><td>Valor total</td><td>Factura: '.number_format($_REQUEST['TX_VALUE']-2800, 2, '.', '').' + Uso de Plataforma '.number_format(2800, 2, '.', '').'</td></tr><tr><td>Moneda</td><td>'.$_REQUEST['currency'].'</td></tr><tr><td>Descripción</td><td>'.$_REQUEST['extra1'].'</td></tr><tr><td>Entidad:</td><td>'.$_REQUEST['lapPaymentMethod'].'</td></tr></table>';
        }else{
            echo '<h2>Resumen Transacción</h2><table><tr><td>'.$mensaje.'</td></tr></table>';
        }
    }

    public function import_bmus(){
        $x=0;$j=0;$a=0;

        $curl = curl_init();
        $empresa = Empresa::find(1);

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/bmus/?per_page=100",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: ".$empresa->wispro
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            $clients = json_decode($response, true);
            $pages = $clients['meta']['pagination']['total_pages'];
            //$pages = 1;
            $total_records = $clients['meta']['pagination']['total_records'];

            for ($i=0; $i <= $pages ; $i++) {
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/bmus/?per_page=100&page=".$i,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => array("Authorization: ".$empresa->wispro),
                ));
                $response = curl_exec($curl); $err = curl_error($curl); curl_close($curl);
                $contracts = json_decode($response, true);

                foreach ($contracts['data'] as $contract) {
                    $find = DB::table('servidores')->where('id',$contract['id'])->get();

                    if(count($find)>0){
                        DB::table('servidores')->where('id',$contract['id'])->update([
                            "public_id"  => $contract['public_id'],
                            "name"       => $contract['name'],
                            "state"      => $contract['state'],
                            "type"       => $contract['type'],
                            "api_token"  => $contract['api_token']
                        ]);
                        $j++;
                    }else{
                        DB::table('servidores')->insert([
                            "id"         => $contract['id'],
                            "public_id"  => $contract['public_id'],
                            "name"       => $contract['name'],
                            "state"      => $contract['state'],
                            "type"       => $contract['type'],
                            "api_token"  => $contract['api_token']
                        ]);
                        $x++;
                    }
                }
            }
            $mensaje = 'Servidores Sincronizados con WISPRO';
            $arrayPost['status']  = '200';
            $arrayPost['message'] = $mensaje;
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function import_mikrotiks(){
        $x=0;$j=0;$a=0;

        $curl = curl_init();
        $empresa = Empresa::find(1);

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/mikrotiks/?per_page=100",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: ".$empresa->wispro
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            $clients = json_decode($response, true);
            $pages = $clients['meta']['pagination']['total_pages'];
            //$pages = 1;
            $total_records = $clients['meta']['pagination']['total_records'];

            for ($i=0; $i <= $pages ; $i++) {
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/mikrotiks/?per_page=100&page=".$i,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => array("Authorization: ".$empresa->wispro),
                ));
                $response = curl_exec($curl); $err = curl_error($curl); curl_close($curl);
                $contracts = json_decode($response, true);

                foreach ($contracts['data'] as $contract) {
                    $find = DB::table('servidores')->where('id',$contract['id'])->get();

                    if(count($find)>0){
                        DB::table('servidores')->where('id',$contract['id'])->update([
                            "public_id"  => $contract['public_id'],
                            "name"       => $contract['name'],
                            "state"      => $contract['state'],
                            "type"       => $contract['type']
                        ]);
                        $j++;
                    }else{
                        DB::table('servidores')->insert([
                            "id"         => $contract['id'],
                            "public_id"  => $contract['public_id'],
                            "name"       => $contract['name'],
                            "state"      => $contract['state'],
                            "type"       => $contract['type']
                        ]);
                        $x++;
                    }
                }
            }
            $mensaje = 'Servidores Sincronizados con WISPRO';
            $arrayPost['status']  = '200';
            $arrayPost['message'] = $mensaje;
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function getInterfaces($mikrotik){
        $mikrotik = Mikrotik::where('id', $mikrotik)->first();
        $ARRAY = '';

        if ($mikrotik) {
            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;

            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                $API->write('/interface/getall');
                $READ = $API->read(false);
                $ARRAY = $API->parseResponse($READ);

                $API->disconnect();
            }
        }
        return json_encode($this->convert_from_latin1_to_utf8_recursively($ARRAY));
    }

    public function getPlanes($mikrotik){
        $planes = PlanesVelocidad::where('mikrotik', $mikrotik)->where('status', 1)->get();
        $mikrotik = Mikrotik::find($mikrotik);
        $API = new RouterosAPI();
        $API->port = $mikrotik->puerto_api;
        $registro = false;
        $getall = '';
        $profile = $API->port;

        if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
            $API->write('/ppp/profile/getall');
            $READ = $API->read(false);
            $profile = $API->parseResponse($READ);
            $API->disconnect();
           }

        //   return "";
        return response()->json(['planes' => $planes, 'mikrotik' => $mikrotik,'profile' => $profile]);
    }

    public function logsMK($mikrotik){
        $mikrotik = Mikrotik::find($mikrotik);
        if ($mikrotik) {
            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;

            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                $API->write("/log/getall");
                $API->write('/cancel', false);
                $READ = $API->read(false);
                $ARRAY = $API->parseResponse($READ);
                $API->disconnect();

                $arrayPost['data'] = $ARRAY;
                return json_encode($arrayPost);
            }
        }
    }

    public function getDetails($cliente, $contrato = null){
        $cliente  = Contacto::find($cliente);

        if($contrato){
            $contrato = Contrato::where('client_id',$cliente->id)->where('id', $contrato)->first();
        }else{
            $contrato = Contrato::where('client_id',$cliente->id)->first();
        }

        $contratos = Contrato::where('client_id', $cliente->id)->where('status', 1)->latest()->get();

        return response()->json(['cliente' => $cliente, 'contrato' => $contrato, 'contratos' => $contratos]);
    }

    public function getMigracion($mikrotik){
        $mikrotik = Mikrotik::find($mikrotik);
        if ($mikrotik) {
            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;

            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                $API->write("/ip/address/getall");
                $API->write('/cancel', false);
                $READ = $API->read(false);
                $ARRAY = $API->parseResponse($READ);
                $API->disconnect();

                $arrayPost['data'] = $ARRAY;
                return response()->json($ARRAY);
                return json_encode($arrayPost);
            }
        }
    }

    public function getPing($rango){
        switch ($rango) {
            case '1':
                $from = 1;
                $to   = 50;
            break;
            case '2':
                $from = 50;
                $to   = 100;
            break;
            case '3':
                $from = 100;
                $to   = 150;
            break;
            case '4':
                $from = 150;
                $to   = 200;
            break;
            case '5':
                $from = 200;
                $to   = 250;
            break;
            case '6':
                $from = 250;
                $to   = 300;
            break;
            case '7':
                $from = 300;
                $to   = 350;
            break;
            case '8':
                $from = 350;
                $to   = 400;
            break;
            case '9':
                $from = 400;
                $to   = 450;
            break;
            case '10':
                $from = 450;
                $to   = 500;
            break;
            case '11':
                $from = 500;
                $to   = 550;
            break;
            case '12':
                $from = 550;
                $to   = 600;
            break;
            case '13':
                $from = 600;
                $to   = 650;
            break;
            case '14':
                $from = 650;
                $to   = 700;
            break;
            case '15':
                $from = 700;
                $to   = 750;
            break;
            case '16':
                $from = 750;
                $to   = 800;
            break;
            case '17':
                $from = 800;
                $to   = 850;
            break;
            case '18':
                $from = 850;
                $to   = 900;
            break;
            case '19':
                $from = 900;
                $to   = 950;
            break;
            case '20':
                $from = 950;
                $to   = 1000;
            break;
            case '21':
                $from = 1000;
                $to   = 1050;
            break;
            case '22':
                $from = 1050;
                $to   = 1100;
            break;
            case '23':
                $from = 1100;
                $to   = 1150;
            break;
            case '24':
                $from = 1150;
                $to   = 1200;
            break;
            case '25':
                $from = 1200;
                $to   = 1250;
            break;
            case '26':
                $from = 1250;
                $to   = 1300;
            break;
            case '27':
                $from = 1300;
                $to   = 1350;
            break;
            case '28':
                $from = 1350;
                $to   = 1400;
            break;
            case '29':
                $from = 1400;
                $to   = 1450;
            break;
            case '30':
                $from = 1450;
                $to   = 1500;
            break;
        }

        $contratos = Contrato::where('status', 1)->where('state', 'enabled')->whereBetween('id', [$from, $to])->get();

        $y=0;$z=0;
        $inicio = date('h:m:s');
        $fallidos = [];
        foreach($contratos as $contrato){
            $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();

            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;

            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                // PING
                $API->write("/ping",false);
                $API->write("=address=".$contrato->ip,false);
                $API->write("=count=1",true);
                $READ = $API->read(false);
                $ARRAY = $API->parseResponse($READ);

                if(count($ARRAY)>0){
                    if(isset($ARRAY[0])){
                        if($ARRAY[0]["received"]!=$ARRAY[0]["sent"]){
                            /*$ping = Ping::firstOrCreate([
                                'contrato' => $contrato->id,
                                'ip' => $contrato->ip,
                                'fecha' => date('Y-m-d')
                            ]);*/
                            $data = [
                                'contrato' => $contrato->id,
                                'ip' => $contrato->ip,
                                'fecha' => Carbon::parse(now())->format('Y-m-d')
                            ];

                            $ping = Ping::updateOrCreate(
                                ['contrato' => $contrato->id],
                                $data
                            );
    						array_push($fallidos, $contrato->ip);
    						$y++;
    					}else{
    					    Ping::where('contrato', $contrato->id)->delete();
    						$mensaje = 'SE HA REALIZADO EL PING DE CONEXIÓN DE MANERA EXITOSA';
    						$z++;
    					}
                    }
                }
                $API->disconnect();
            }
        }

        echo 'Hora Inicio: '.$inicio.'<br>Hora Final: '.date('h:m:s').'<br>Ping Exitosos: '.$z.'<br>Ping Fallidos: '.$y.'<br><br>';
        foreach($fallidos as $fallido) {
            echo $fallido. "<br>";
        }
    }

    public function getIps($mikrotik){
        $ips = Contrato::where('status', 1)->select('ip', 'state')->orderBy('ip', 'asc')->get();

$mikrotik = Mikrotik::find($mikrotik);
if ($mikrotik) {
    $API = new RouterosAPI();
    $API->port = $mikrotik->puerto_api;

    if ($API->connect($mikrotik->ip, $mikrotik->usuario, $mikrotik->clave)) {
        $API->write("/queue/simple/getall");
        $READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);
        $API->disconnect();
        $sanitizedArray = [];

        foreach ($ARRAY as $item) {
            $sanitizedItem = [];

            foreach ($item as $key => $value) {
                // Verificar si $value es una cadena antes de intentar convertirla
                if (is_string($value)) {
                    // Detectar la codificación
                    $encoding = mb_detect_encoding($value, mb_list_encodings(), true);
                    if ($encoding) {
                        // Convertir a UTF-8 si se detecta la codificación
                        $sanitizedItem[$key] = mb_convert_encoding($value, "UTF-8", $encoding);
                    } else {
                        // Sanitizar la cadena si la codificación no se detecta
                        $sanitizedItem[$key] = iconv('UTF-8', 'UTF-8//IGNORE', $value);
                    }
                } else {
                    $sanitizedItem[$key] = $value; // Si no es una cadena, mantener el valor original
                }
            }

            // Agregar el item sanitizado al array resultante
            $sanitizedArray[] = $sanitizedItem;
        }

        return response()->json(['software' => $ips, 'mikrotik' => $sanitizedArray]);
    }
}
        /*$ips = Contrato::where('status', 1)->select('ip', 'state')->orderBy('ip', 'asc')->get();

        $mikrotik = Mikrotik::find($mikrotik);
        if ($mikrotik) {
            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;

            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                $API->write("/queue/simple/getall");
                $READ = $API->read(false);
                $ARRAY = $API->parseResponse($READ);
                $API->disconnect();
                $sanitizedArray = [];

                foreach ($ARRAY as $item) {
                    $sanitizedItem = [];

                    foreach ($item as $key => $value) {
                        // Verificar si $value es una cadena antes de intentar convertirla
                        if (is_string($value)) {
                            $sanitizedItem[$key] = mb_convert_encoding($value, "UTF-8", "auto");
                        } else {
                            $sanitizedItem[$key] = $value; // Si no es una cadena, mantener el valor original
                        }
                    }

                    // Agregar el item sanitizado al array resultante
                    $sanitizedArray[] = $sanitizedItem;
                }
               /* foreach ($ARRAY as $i => $value) { unset($ARRAY[$i]['name']); }

                // Se estaba generando el problema de que la codificación del
                // arreglo y de los valores no llegaba correctamente, por eso
                // es necesario convertir nuevamente el arreglo a UTF-8.
                $sanitizedArray = mb_convert_encoding($ARRAY, "UTF-8", "auto");*/
              /*  return response()->json(['software' => $ips, 'mikrotik' => $sanitizedArray]);
            }
        }*/
    }

    public function getSegmentos($mikrotik){
        $segmentos = Segmento::where('mikrotik', $mikrotik)->get()->toArray();
        $i = 0;
        foreach($segmentos as $segmento){
            $parte = explode('/',$segmento['segmento']);

            if(isset($parte[1])){
                if($parte[1] == 30){
                    $seg = Contrato::where('local_address', $segmento['segmento'])->where('status', 1)->select('local_address')->first();
                    if($seg){
                        unset($segmentos[$i]);
                    }
                }
                $i++;
            }
        }
        return response()->json($segmentos);
    }

    public function getContracts($id){
        $contratos = Contrato::join('contactos as c', 'contracts.client_id', '=', 'c.id')->where('contracts.client_id', $id)->get();
        if(count($contratos)>0){
            return response()->json($contratos);
        }
        return response()->json(Contacto::find($id));
    }

    public function addMovimientoPuc($id){

        $forma = FormaPago::find($id);

        // $pucMovimiento = new PucMovimiento;
        // dd($forma);

    }

    public function getSubnetting($ip_address,$prefijo){
        switch ($prefijo) {
            case '0':
                $ip_nmask = "0.0.0.0";
            break;
            case '1':
                $ip_nmask = "128.0.0.0";
            break;
            case '2':
                $ip_nmask = "192.0.0.0";
            break;
            case '3':
                $ip_nmask = "224.0.0.0";
            break;
            case '4':
                $ip_nmask = "240.0.0.0";
            break;
            case '5':
                $ip_nmask = "248.0.0.0";
            break;
            case '6':
                $ip_nmask = "252.0.0.0";
            break;
            case '7':
                $ip_nmask = "254.0.0.0";
            break;
            case '8':
                $ip_nmask = "255.0.0.0";
            break;
            case '9':
                $ip_nmask = "255.128.0.0";
            break;
            case '10':
                $ip_nmask = "255.192.0.0";
            break;
            case '11':
                $ip_nmask = "255.224.0.0";
            break;
            case '12':
                $ip_nmask = "255.240.0.0";
            break;
            case '13':
                $ip_nmask = "255.248.0.0";
            break;
            case '14':
                $ip_nmask = "255.252.0.0";
            break;
            case '15':
                $ip_nmask = "255.254.0.0";
            break;
            case '16':
                $ip_nmask = "255.255.0.0";
            break;
            case '17':
                $ip_nmask = "255.255.128.0";
            break;
            case '18':
                $ip_nmask = "255.255.192.0";
            break;
            case '19':
                $ip_nmask = "255.255.224.0";
            break;
            case '20':
                $ip_nmask = "255.255.240.0";
            break;
            case '21':
                $ip_nmask = "255.255.248.0";
            break;
            case '22':
                $ip_nmask = "255.255.252.0";
            break;
            case '23':
                $ip_nmask = "255.255.254.0";
            break;
            case '24':
                $ip_nmask = "255.255.255.0";
            break;
            case '25':
                $ip_nmask = "255.255.255.128";
            break;
            case '26':
                $ip_nmask = "255.255.255.192";
            break;
            case '27':
                $ip_nmask = "255.255.255.224";
            break;
            case '28':
                $ip_nmask = "255.255.255.240";
            break;
            case '29':
                $ip_nmask = "255.255.255.248";
            break;
            case '30':
                $ip_nmask = "255.255.255.252";
            break;
            case '31':
                $ip_nmask = "255.255.255.254";
            break;
            case '32':
                $ip_nmask = "255.255.255.255";
            break;
        }

        //CONVERT IP ADDRESSES TO LONG FORM
        $ip_address_long = ip2long($ip_address);
        $ip_nmask_long = ip2long($ip_nmask);

        //CACULATE NETWORK ADDRESS
        $ip_net = $ip_address_long & $ip_nmask_long;

        //CACULATE FIRST USABLE ADDRESS
        $ip_host_first = ((~$ip_nmask_long) & $ip_address_long);
        $ip_first = ($ip_address_long ^ $ip_host_first) + 1;

        //CACULATE LAST USABLE ADDRESS
        $ip_broadcast_invert = ~$ip_nmask_long;
        $ip_last = ($ip_address_long | $ip_broadcast_invert) - 1;

        //CACULATE BROADCAST ADDRESS
        $ip_broadcast = $ip_address_long | $ip_broadcast_invert;

        //OUTPUT
        $ip_net_short = long2ip($ip_net);
        $ip_first_short = long2ip($ip_first);
        $ip_last_short = long2ip($ip_last);
        $ip_broadcast_short = long2ip($ip_broadcast);

        $parte = explode(".", $ip_address);
        $ip_first_short = $parte[0].'.'.$parte[1].'.'.$parte[2].'.'.($parte[3] + 1);

        return response()->json([
            'address'   => $ip_address,
            'netmask'   => $ip_nmask,
            'network'   => $ip_net_short.'/'.$prefijo,
            'inicial'   => $ip_first_short,
            'final'     => $ip_last_short,
            'broadcast' => $ip_broadcast_short,
            'i'         => $parte[3] + 1,
        ]);
    }

    public static function convert_from_latin1_to_utf8_recursively($dat){
        if (is_string($dat)) {
            return utf8_encode($dat);
        } elseif (is_array($dat)) {
            $ret = [];
            foreach ($dat as $i => $d) $ret[ $i ] = self::convert_from_latin1_to_utf8_recursively($d);
            return $ret;
        } elseif (is_object($dat)) {
            foreach ($dat as $i => $d) $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);
            return $dat;
        } else {
            return $dat;
        }
    }

    public function getClienteSMS($id){
        return response()->json([
            'succes' => true,
            'id'     => $id,
            'contacto' => Contacto::where('id', $id)->first(),
            'contrato' => Contrato::
            join('planes_velocidad as p', 'p.id', '=', 'contracts.plan_id')->
            join('grupos_corte as g', 'g.id', '=', 'contracts.grupo_corte')->
            select('p.name as plan', 'g.nombre as corte')->
            where('contracts.client_id', $id)->
            first(),
            'factura' => Factura::where('cliente', $id)->get()->last(),
        ]);
    }

    public function getContractsBarrio($barrio){
        $contratos = Contrato::query()
            ->select('contracts.id', 'contracts.state', 'contactos.nombre', 'contactos.apellido1', 'contactos.apellido2', 'contactos.nit')
            ->join('contactos', 'contracts.client_id', '=', 'contactos.id');

        $contratos->where(function ($query) use ($barrio) {
            $query->orWhere('contactos.direccion', 'like', "%{$barrio}%");
            $query->orWhere('contracts.address_street', 'like', "%{$barrio}%");
        });

        return response()->json([
            'succes'    => true,
            'search'    => $barrio,
            'data'      => $contratos->orderBy('contracts.state', 'desc')->get(),
        ]);
    }

    public function radicadosBarrio(){
        $radicados = Radicado::join('contactos as c', 'c.id', '=', 'radicados.cliente')->select('c.barrio', 'radicados.id')->get();

        foreach ($radicados as $radicado){
            $rad = Radicado::find($radicado->id);
            $rad->barrio = $radicado->barrio;
            $rad->save();
        }
    }

    public function getMAC($mk, $ip){
        $mikrotik = Mikrotik::find($mk);
        $ARRAY = '';
        if ($mikrotik) {
            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;

            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                $API->write('/ip/arp/print', false);
                $API->write('?address='.$ip, true);
                //$API->write('=.proplist');
                $arrays = $API->read();

                if(count($arrays)>0){
                    return response()->json([
                        'success'     => true,
                        'mac_address' => $arrays[0]['mac-address'],
                        'dynamic'     => $arrays[0]['dynamic'],
                        'complete'    => $arrays[0]['complete'],
                    ]);
                }else{
                    return response()->json([
                        'success' => false,
                        'icon'    => 'error',
                        'title'   => 'ERROR',
                        'text'    => 'NO SE HA PODIDO REALIZAR LA GRÁFICA'
                    ]);
                }
                $API->disconnect();
            }
        }
    }

    public function validateStatusDocumentoSoporte($nitEmisor, $idDocumento, $codigoTipo, $nitReceptor){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://apivp.efacturacadena.com/v1/vp/consulta/documentos?nit_emisor=" .$nitEmisor ."&id_documento=" .$idDocumento. "&codigo_tipo_documento=". $codigoTipo. "&nit_receptor=".$nitReceptor,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Partnership-Id: 1128464945',
            'efacturaAuthorizationToken: 62808bf1-d446-46ee-8120-00162e95c059'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public static function sendInBlue($html, $titulo, $emails, $nombreCliente, $adjuntos = []){

        $empresa = auth()->user()->empresa();
        foreach($emails as $email){
            $fields = [
                'to' => [
                    [
                        'email' => $email,
                        'name' => $nombreCliente
                    ]
                ],
                'sender' => [
                    'name' => $empresa->nombre,
                    'email' => $empresa->email
                ],
                'subject' => $titulo,
                'htmlContent' => '<html>'.$html.'</html>',
            ];

            if(is_array($adjuntos) && count($adjuntos) > 0){
                $fields['attachment'] = $adjuntos;
            }

            $fields = json_encode($fields);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.sendinblue.com/v3/smtp/email');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'accept: application/json',
                'api-key: '.$empresa->api_key_mail.'', 'content-type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);

            $response = json_decode($response, true);
        }

    }


    public static function sendMail($vista, $data, $usedData, $fn){

        $html = '';
        $adjuntos = [];

        if($vista){
            $html = view($vista, $data)->render();
        }

        /*
        $reflection = new \ReflectionFunction($fn);
        $message = new \stdClass();
        $data = $reflection->invoke($message);
        */


        if(isset($usedData['tituloCorreo'])){
            $titulo = $usedData['tituloCorreo'];
        }else{
            $titulo = 'Comunicado ' . auth()->user()->empresa()->nombre;
        }


        if(isset($usedData['emails'])){
            $emails = $usedData['emails'];
        }else if(isset($usedData['email'])){
            $emails = $usedData['email'];
        }else{
            $emails = null;
        }

        if(isset($data['cliente'])){
            $nombreCliente = $data['cliente'];
        }else{
            $nombreCliente = 'Usuario';
        }

        if(isset($usedData['pdf'])){
            /*
            if($usedData['pdf'] && str_contains($usedData['pdf'], 'pdf')){
                if(file_exists($url = config('app.url') . '/' . $usedData['pdf'])){
                    $adjuntos[] = ['url' => $url, 'name' => 'file.pdf' ];
                }
            }
            */

            $adjuntos[] = ['name' => 'document.pdf', 'content' => chunk_split(base64_encode($usedData['pdf']))];
        }

        if(isset($usedData['xmlPath'])){
            /*
            if($usedData['xmlPath'] && str_contains($usedData['xmlPath'], 'xml')){
                if(file_exists($url = config('app.url') . '/' . $usedData['xmlPath'])){
                    $adjuntos[] = ['url' => $url, 'name' => 'xml.xml'];
                }
            }
            */

            if(file_exists($usedData['xmlPath'])){
                $url = config('app.url') . '/' . $usedData['xmlPath'];

                $file = null;

                try {
                    $file = file_get_contents($url);
                } catch (\Throwable $t) {
                    $file = null;
                }

                if($file){
                    $adjuntos[] = ['name' => 'xml.xml', 'content' => chunk_split(base64_encode($file))];
                }
            }
        }

        if(!is_array($emails)){
            $emails = [$emails];
        }


        try {
            self::sendInBlue($html, $titulo, $emails, $nombreCliente, $adjuntos);
        } catch (\Throwable $t) {
        // exception is raised and it'll be handled here
        // $e->getMessage() contains the error message
        }


        try {
            Mail::send($vista, $data, $fn);
        } catch (\Throwable $t) {
        // exception is raised and it'll be handled here
        // $e->getMessage() contains the error message
        }

    }

    public function getPdfFactura($id)
    {

        $factura = Factura::find($id);
        $empresa = Empresa::find($factura->empresa);
        $items = ItemsFactura::where('factura',$factura->id)->get();
        $itemscount=ItemsFactura::where('factura',$factura->id)->count();
        $retenciones = FacturaRetencion::where('factura', $factura->id)->get();
        $resolucion = NumeracionFactura::where('id',$factura->numeracion)->first();
        $tipo = $factura->tipo;

        if($factura->emitida == 1){
            $impTotal = 0;
            foreach ($factura->totalAPI($empresa->id)->imp as $totalImp){
                if(isset($totalImp->total)){
                    $impTotal = $totalImp->total;
                }
            }

            $CUFEvr = $factura->info_cufeAPI($factura->id, $impTotal, $empresa->id);
            $infoEmpresa = Empresa::find($empresa->id);
            $data['Empresa'] = $infoEmpresa->toArray();
            $infoCliente = Contacto::find($factura->cliente);
            $data['Cliente'] = $infoCliente->toArray();
            /*..............................
            Construcción del código qr a la factura
            ................................*/
            $impuesto = 0;
            foreach ($factura->totalAPI($empresa->id)->imp as $key => $imp) {
                if(isset($imp->total)){
                    $impuesto = $imp->total;
                }
            }

            $codqr = "NumFac:" . $factura->codigo . "\n" .
            "NitFac:"  . $data['Empresa']['nit']   . "\n" .
            "DocAdq:" .  $data['Cliente']['nit'] . "\n" .
            "FecFac:" . Carbon::parse($factura->created_at)->format('Y-m-d') .  "\n" .
            "HoraFactura" . Carbon::parse($factura->created_at)->format('H:i:s').'-05:00' . "\n" .
            "ValorFactura:" .  number_format($factura->totalAPI($empresa->id)->subtotal, 2, '.', '') . "\n" .
            "ValorIVA:" .  number_format($impuesto, 2, '.', '') . "\n" .
            "ValorOtrosImpuestos:" .  0.00 . "\n" .
            "ValorTotalFactura:" .  number_format($factura->totalAPI($empresa->id)->subtotal + $factura->impuestos_totalesFe(), 2, '.', '') . "\n" .
            "CUFE:" . $CUFEvr;
            /*..............................
            Construcción del código qr a la factura
            ................................*/
            return PDF::loadView('pdf.electronica', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion','codqr','CUFEvr', 'empresa'))->save(public_path() . "/convertidor/" . $factura->codigo . ".pdf")->output();
        }else{
            return PDF::loadView('pdf.electronica', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion', 'empresa'))->save(public_path() . "/convertidor/" . $factura->codigo . ".pdf")->output();
        }
    }


}
