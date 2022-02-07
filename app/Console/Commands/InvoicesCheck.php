<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use App\Contacto;

class InvoicesCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chequea si hay facturación pendientes de pago';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $busqueda=false;
        $campos=array('factura.nro', 'factura.id', 'nombrecliente', 'factura.fecha', 'factura.vencimiento', 'total', 'pagado', 'porpagar', 'factura.estatus');
        if (!$request->orderby) {
            $request->orderby=0; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';

        $facturas=Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')->join('items_factura as if', 'factura.id', '=', 'if.factura')->leftJoin('vendedores as v', 'factura.vendedor', '=', 'v.id')->select('factura.id', 'factura.codigo', 'factura.nro', DB::raw('c.UID AS client_UID'), DB::raw('c.nombre as nombrecliente'), 'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) as pagado'), DB::raw('(SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant)-((Select SUM(pago) from ingresos_factura where factura=factura.id)+(Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id))-(Select if(SUM(pago), SUM(pago), 0) from notas_factura where factura=factura.id)) as porpagar'))->where('tipo','!=',2)->where('tipo','!=',5);

        $appends=array('orderby'=>$request->orderby, 'order'=>$request->order);

        if($request->has('search')){
            $busqueda              = true;
            $search                = mb_strtolower($request->input('search'));
            $filter                = '';
            switch ($request->input('search')){
                case (preg_match('/-fven/', $search) ? true : false):
                    $filter        = "ven";
                    break;
                case (preg_match('/-fvto/', $search) ? true : false):
                    $filter        = "vto";
                    break;
                case (preg_match('/-fiva/', $search) ? true : false):
                    $filter        = "iva";
                    break;
                case (preg_match('/-fpgo/', $search) ? true : false):
                    $filter        = "pgo";
                    break;
                case (preg_match('/-fppr/', $search) ? true : false):
                    $filter        = "ppr";
                    break;
            }

            if($filter)
                $search = str_replace(' -f'.$filter, '', $search);
            if(is_numeric($request->input('search'))){
                if($filter != ''){
                }else{
                    $facturas = $facturas->havingRaw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+
                    (if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) > ?',
                        [$search]);
                }
            }else{
                if (preg_match('/[A-Za-z]/', $search) && preg_match('/[0-9]/', $search)){
                    $facturas      = $facturas->where('factura.codigo', 'like', '%' .$search.'%');
                }else{
                    if (strcmp($search, 'abierta') == 0 || strcmp($search, 'cerrada') == 0 || strcmp($search, 'anulada') == 0){
                        $facturas  = $facturas->whereIn('factura.estatus', $search);
                    }elseif (date('d-m-Y', strtotime($search)) == $search){
                        if(preg_match('/-vto/i', $search)){
                            dd("d");
                            $facturas  = $facturas->where('factura.vencimiento', date('Y-m-d', strtotime($search)));
                        }else{
                            $facturas  = $facturas->where('factura.fecha', date('Y-m-d', strtotime($search)));
                        }
                    }else{
                        $facturas  = $facturas->where('c.nombre', 'like', '%' .$search.'%');
                    }
                }
            }
        }

        if ($request->name_1) {
            $busqueda=true; $appends['name_1']=$request->name_1; $facturas=$facturas->where('factura.codigo', 'like', '%' .$request->name_1.'%');
        }
        if ($request->name_2) {
            $busqueda=true; $appends['name_2']=$request->name_2; $facturas=$facturas->where('c.nombre', 'like', '%' .$request->name_2.'%');
        }
        if ($request->name_3) {
            $busqueda=true; $appends['name_3']=$request->name_3; $facturas=$facturas->where('factura.fecha', date('Y-m-d', strtotime($request->name_3)));
        }
        if ($request->name_4) {
            $busqueda=true; $appends['name_4']=$request->name_4; $facturas=$facturas->where('factura.vencimiento', date('Y-m-d', strtotime($request->name_4)));
        }
        if ($request->name_8) {
            $busqueda=true; $appends['name_8']=$request->name_8; $facturas=$facturas->whereIn('factura.estatus', $request->name_8);
        }
        if ($request->name_6) {
            $busqueda=true; $appends['name_6']=$request->name_6; $appends['name_6_simb']=$request->name_6_simb; $facturas=$facturas->whereRaw(DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) '.$request->name_6_simb.' ?'), [$request->name_6]);
        }
        if ($request->name_9) {
            $busqueda=true; $appends['name_9']=$request->name_9; $facturas=$facturas->where('v.nombre', 'like', '%' .$request->name_9.'%');
        }
        if ($request->name_7) {
            $tmpFacturas = $facturas->groupBy('if.factura');
            $tmpFacturas = $tmpFacturas->get();
            foreach ($tmpFacturas as $tmpFactura){
                if ($request->name_7_simb == '>'){
                    if($tmpFactura->porpagar() > $request->name_7){
                        $tmpArry[] = $tmpFactura->id;
                    }
                }elseif($request->name_7_simb == '<'){
                    if($tmpFactura->porpagar() < $request->name_7){
                        $tmpArry[] = $tmpFactura->id;
                    }
                }else{
                    if($tmpFactura->porpagar() == $request->name_7){
                        $tmpArry[] = $tmpFactura->id;
                    }
                }
            }
            $facturas = $facturas->whereIn('factura.id', $tmpArry);
            $appends['name_7']=$request->name_7;
            $appends['name_7_simb']=$request->name_7_simb;
            $busqueda=true;
        }

        $facturas=$facturas->groupBy('if.factura');

        if ($request->name_5) {
            $busqueda=true;
            $appends['name_5']=$request->name_5;
            $appends['name_5_simb']=$request->name_5_simb;
            $facturas=$facturas->havingRaw('(SUM(
                (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+
                (if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) )'
                .$request->name_5_simb.' ?', [$request->name_5]);
        }

        $facturas=$facturas->OrderBy($orderby, $order)->paginate(15000)->appends($appends);

        if ($facturas) {$x=0;$y=0;
            foreach ($facturas as $factura) {
                if($factura->vencimiento < date('Y-m-d')){
                    if($factura->total == $factura->pagado){
                        DB::table('contracts')->where('client_id',$factura->client_UID)->update(['state' => 'enabled']);
                        $x++;
                    }else{
                        DB::table('contracts')->where('client_id',$factura->client_UID)->update(['state' => 'disabled']);
                        $y++;
                    }
                }
            }
        }
        echo 'Disabled: '.$y.' Enabled: '.$x;
    }
}
