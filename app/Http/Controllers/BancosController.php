<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Banco;  use App\Categoria;
use App\Movimiento; use App\Funcion;
use App\Numeracion; use App\Impuesto; 
use App\Model\Ingresos\Ingreso; 
use App\Model\Ingresos\IngresosCategoria; 
use App\Model\Gastos\Gastos;
use App\Model\Gastos\GastosCategoria;
use Validator; use Auth; use DB; use Carbon\Carbon;
use Session;
class BancosController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
        view()->share(['seccion' => 'bancos', 'title' => 'Bancos', 'icon' =>'fas fa-university']);
    }
    
    public function index(){
        $this->getAllPermissions(Auth::user()->id);
        (Auth::user()->cuenta > 0) ? $bancos = Banco::where('empresa',Auth::user()->empresa)->whereIn('id',[Auth::user()->cuenta,Auth::user()->cuenta_1,Auth::user()->cuenta_2,Auth::user()->cuenta_3,Auth::user()->cuenta_4])->where('estatus',1)->get() : $bancos = Banco::where('empresa',Auth::user()->empresa)->where('estatus',1)->get();
        if(Auth::user()->rol < 3){
            $bancos = Banco::where('empresa',Auth::user()->empresa)->where('estatus',1)->get();
        }
        return view('bancos.index')->with(compact('bancos'));   		
 	}
 	
 	public function create(){
 	    $this->getAllPermissions(Auth::user()->id);
 	    view()->share(['title' => 'Nuevo Banco']);
 	    return view('bancos.create');
 	}
 	
    public function store(Request $request){
        if( Banco::where('empresa',auth()->user()->empresa)->count() > 0){
            //Tomamos el tiempo en el que se crea el registro
            Session::put('posttimer', Banco::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
            $sw = 1;
            //Recorremos la sesion para obtener la fecha
            foreach (Session::get('posttimer') as $key) {
                if ($sw == 1) {
                    $ultimoingreso = $key;
                    $sw=0;
                }
            }
            //Tomamos la diferencia entre la hora exacta acutal y hacemos una diferencia con la ultima creación
            $diasDiferencia = Carbon::now()->diffInseconds($ultimoingreso);
            //Si el tiempo es de menos de 30 segundos mandamos al listado general
            if ($diasDiferencia <= 10) {
                $mensaje = "El formulario ya ha sido enviado.";
                return redirect('empresa/bancos')->with('success', $mensaje);
            }
        }
        
        $request->validate([
            'tipo_cta' => 'required|numeric',
            'nombre' => 'required|max:200',
            'saldo' => 'required|numeric',
            'fecha' => 'required'
        ]);
        
        if (Banco::where('empresa', Auth::user()->empresa)->orderby('id', 'DESC')->take(1)->first()) {
            $nro = Banco::where('empresa', Auth::user()->empresa)->orderby('id', 'DESC')->take(1)->first()->nro + 1;
        } else {
            $nro = 1;
        }
        
        $banco = new Banco;
        $banco->nro=$nro;
        $banco->empresa=Auth::user()->empresa;
        $banco->tipo_cta=$request->tipo_cta;
        $banco->nombre=$request->nombre;
        $banco->nro_cta=$request->nro_cta;
        $banco->saldo=$request->saldo;
        $banco->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
        $banco->descripcion=$request->descripcion;
        $banco->save();
        
        $mensaje='Se ha creado satisfactoriamente el banco';
        return redirect('empresa/bancos')->with('success', $mensaje)->with('banco_id', $banco->id);
    }
    
    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $banco = Banco::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
        if ($banco) {        
            view()->share(['title' => 'Modificar Cuenta: '.$banco->nombre]);
            return view('bancos.edit')->with(compact('banco'));
        }
        return redirect('empresa/bancos')->with('success', 'No existe un registro con ese id');
    }
    
    public function update(Request $request, $id){
        $banco =Banco::find($id);
        if ($banco) {
            $request->validate([
                'nombre' => 'required|max:200',
                'saldo' => 'required|numeric',
                'fecha' => 'required'
            ]);
            $banco->nombre=$request->nombre;
            $banco->nro_cta=$request->nro_cta;
            $banco->tipo_cta=$request->tipo_cta;
            $banco->saldo=$request->saldo;
            $banco->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
            $banco->descripcion=$request->descripcion;
            $banco->save();
            $mensaje='Se ha modificado satisfactoriamente el banco';
            return redirect('empresa/bancos')->with('success', $mensaje)->with('banco_id', $banco->id);
        }
        return redirect('empresa/bancos')->with('success', 'No existe un registro con ese id');
    }
    
    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $banco = Banco::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
        if ($banco) {     
            $bancos = Banco::where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
            $saldo= $banco->saldo;
            $saldo+=Movimiento::where('movimientos.empresa', Auth::user()->empresa)->where('banco', $banco->id)->where('tipo', 1)->where('estatus', 1)->sum('saldo');
            $saldo-=Movimiento::where('movimientos.empresa', Auth::user()->empresa)->where('banco', $banco->id)->where('tipo', 2)->where('estatus', 1)->sum('saldo');
            
            view()->share(['icon'=>'', 'title' => $banco->nombre, 'title_sub'=>'Banco', 'minus_dere'=>true]);
            $movimientos = $this->mostrarMovimientos($banco->id);
            return view('bancos.show')->with(compact('banco', 'saldo', 'bancos', 'movimientos'));
        }
        return redirect('empresa/bancos')->with('success', 'No existe un registro con ese id');
    }
    
    public function destroy($id){      
        $banco=Banco::find($id);   
        if ($banco) {
            $banco->delete();
        }
        return redirect('empresa/bancos')->with('success', 'Se ha eliminado el banco');
    }
    
    public function act_desac($id){
        $banco=Banco::find($id);   
        if ($banco) {
            if ($banco->estatus==1) {        
                $banco->estatus=0;
                $mensaje='Se ha desactivado satisfactoriamente el banco';
            }else{
                $banco->estatus=0;
                $mensaje='Se ha activado satisfactoriamente el banco';
            }
            $banco->save();
            return back()->with('success', $mensaje)->with('banco_id', $banco->id);
        }
        return back('empresa/bancos')->with('success', 'No existe un registro con ese id');
    }
    
    public function datatable_movimientos_cliente($contacto, Request $request){
        $requestData =  $request;
        $columns = array(
            // datatable column index  => database column name
            0 => 'movimientos.fecha',
            1 => 'movimientos.cuenta',
            2 => 'movimientos.saldo',
            3 => 'movimientos.saldo'
        );
        $requestData =  $request;
        $movimientos=Movimiento::leftjoin('contactos as c', 'movimientos.contacto', '=', 'c.id')    
        ->select('movimientos.*', DB::raw('if(movimientos.contacto,c.nombre,"") as nombrecliente'))
        ->where('movimientos.empresa',Auth::user()->empresa);
        
        if ($contacto) { $movimientos=$movimientos->where('movimientos.contacto', $contacto); }
        //Busca los campos saldo, fecha y nombre del cliente
        if ($requestData->search['value']) {
            // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $movimientos=$movimientos->where(function ($query) use ($requestData) {
                $query->where('movimientos.saldo', 'like', '%'.$requestData->search['value'].'%')
                ->orwhere('c.nombre', 'like', '%'.$requestData->search['value'].'%')
                ->orwhere('movimientos.fecha', 'like', '%'.$requestData->search['value'].'%');
            });
        }
        $totalFiltered=$totalData=$movimientos->count();
        
        //Ordenar por el tipo (Entrada o Salida) y el saldo
        if ($requestData['order'][0]['column']==2 || $requestData['order'][0]['column']==3) {
            $tipo=$requestData['order'][0]['column']==2?'desc':'asc';
            $movimientos=$movimientos->orderby('tipo', $tipo)->orderby($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        }else{
            $movimientos=$movimientos->orderby($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        }
        
        $movimientos=$movimientos->skip($requestData['start'])->take($requestData['length']);
        $movimientos=$movimientos->get();
        $data = array();
        
        foreach ($movimientos as $movimiento) {
            $nestedData = array();
            $nestedData[] = '<a href="'.$movimiento->show_url().'">'.date('d-m-Y', strtotime($movimiento->fecha)).'</a>';
            $nestedData[] = $movimiento->banco()->nombre;
            $nestedData[] = $movimiento->categoria();
            $nestedData[] = $movimiento->tipo==2?Auth::user()->empresa()->moneda.Funcion::Parsear($movimiento->saldo):'';
            $nestedData[] = $movimiento->tipo==1?Auth::user()->empresa()->moneda.Funcion::Parsear($movimiento->saldo):'';
            $data[] = $nestedData;
        }
        
        $json_data = array(
            "draw" => intval($requestData->draw),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData),  // total number of records
            "recordsFiltered" => intval($totalFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data   // total data array
        );
        return json_encode($json_data);
    }
    
    public function mostrarMovimientos($banco){
        $movimientos=Movimiento::leftjoin('contactos as c', 'movimientos.contacto', '=', 'c.id')
        ->select('movimientos.*', DB::raw('if(movimientos.contacto,c.nombre,"") as nombrecliente'))
        ->where('movimientos.empresa',Auth::user()->empresa);
        
        if ($banco) { $movimientos=$movimientos->where('banco', $banco); }
        $movimientos=  $movimientos->orderBy('id', 'DESC')->get();
        return $movimientos;
    }
    
    public function datatable_movimientos($banco=null, Request $request){
        // storing  request (ie, get/post) global array to a variable
        $requestData =  $request;
        $columns = array(
            // datatable column index  => database column name
            0 => 'movimientos.fecha',
            1 => 'nombrecliente',
            2 => ' ',
            3 => '',
            4 => 'movimientos.estatus',
            5 => 'movimientos.saldo',
            6 => 'movimientos.saldo',
            7=>'acciones'
        );
        
        $movimientos=Movimiento::leftjoin('contactos as c', 'movimientos.contacto', '=', 'c.id')    
        ->select('movimientos.*', DB::raw('if(movimientos.contacto,c.nombre,"") as nombrecliente'))
        ->where('movimientos.empresa',Auth::user()->empresa);
        
        if ($banco) { $movimientos=$movimientos->where('banco', $banco); }
        
        //Busca los campos saldo, fecha y nombre del cliente
        if ($requestData->search['value']) {
            // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $movimientos=$movimientos->where(function ($query) use ($requestData) {
                $query->where('movimientos.saldo', 'like', '%'.$requestData->search['value'].'%')
                ->orwhere('c.nombre', 'like', '%'.$requestData->search['value'].'%')
                ->orwhere('movimientos.fecha', 'like', '%'.$requestData->search['value'].'%');
            });
        }
        
        $totalFiltered=$totalData=$movimientos->count();
        
        //Ordenar por el tipo (Entrada o Salida) y el saldo
        if ($requestData['order'][0]['column']==5 || $requestData['order'][0]['column']==6) {
            $tipo=$requestData['order'][0]['column']==5?'desc':'asc';
            $movimientos=$movimientos->orderby('tipo', $tipo)->orderby($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        }else{
            $movimientos=$movimientos->orderby($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        }
        
        $movimientos=$movimientos->skip($requestData['start'])->take($requestData['length']);
        $movimientos=$movimientos->get();
        $data = array();
        
        foreach ($movimientos as $movimiento) {
            $nestedData = array();
            $nestedData[] = '<a href="'.$movimiento->show_url().'">'.date('d-m-Y', strtotime($movimiento->fecha)).'</a>';
            $nestedData[] = ($movimiento->nombrecliente) ? $movimiento->nombrecliente : auth()->user()->empresa()->nombre;
            $nestedData[] = $movimiento->conciliado();
            $nestedData[] = $movimiento->categoria();
            $nestedData[] = '<spam class="text-'.$movimiento->estatus(true).'">'.$movimiento->estatus()."</spam>";
            $nestedData[] = $movimiento->tipo==2?Auth::user()->empresa()->moneda.Funcion::Parsear($movimiento->saldo):'';
            $nestedData[] = $movimiento->tipo==1?Auth::user()->empresa()->moneda.Funcion::Parsear($movimiento->saldo):'';
            if(Auth::user()->modo_lectura() || Auth::user()->rol != 45){
                $nestedData[] = '<a  href="'.$movimiento->show_url().'" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>';
            }else{
                $nestedData[] = $movimiento->boton();
            }
            $data[] = $nestedData;
        }
        
        $json_data = array(
            "draw" => intval($requestData->draw),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData),  // total number of records
            "recordsFiltered" => intval($totalFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data   // total data array
        );
        return json_encode($json_data);
    }
    
    public function create_transferencia($banco){    
        $banco = Banco::where('empresa',Auth::user()->empresa)->where('nro', $banco)->first();
        if ($banco) {
            $bancos = Banco::where('empresa',Auth::user()->empresa)->get();
            return view('bancos.transferencia.create')->with(compact('banco', 'bancos'));
        }    
    }
    
    public function store_transferencia($banco, Request $request){
        $banco = Banco::where('empresa',Auth::user()->empresa)->where('nro', $banco)->first();
        if ($banco) {
            $nro=Numeracion::where('empresa',Auth::user()->empresa)->first();
            $caja=$nro->caja;
            while (true) {
                $numero=Ingreso::where('empresa', Auth::user()->empresa)->where('nro', $caja)->count();
                if ($numero==0) {
                    break;
                }
                $caja++;
            }
            
            $ingreso = new Ingreso;
            $ingreso->nro=$caja;
            $ingreso->empresa=Auth::user()->empresa;
            $ingreso->cuenta=$request->cuenta_destino;
            $ingreso->tipo=4;
            $ingreso->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
            $ingreso->observaciones=$request->observaciones;
            $ingreso->save();

            $impuesto = Impuesto::where('id', 2)->first();
            $categoria = Categoria::where('empresa',Auth::user()->empresa)->where('nro', 6)->first();
            $items = new IngresosCategoria;
            $items->valor=$this->precision($request->monto);
            $items->id_impuesto=$impuesto->id;
            $items->impuesto=$impuesto->porcentaje;
            $items->ingreso=$ingreso->id;
            $items->categoria=$categoria->id;
            $items->cant=1;
            $items->save();

            //ingresos
            $movimiento1=new Movimiento;
            $movimiento1->empresa=Auth::user()->empresa;
            $movimiento1->banco=$ingreso->cuenta;
            $movimiento1->tipo=1;
            $movimiento1->saldo=$this->precision($request->monto);
            $movimiento1->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
            $movimiento1->modulo=1;
            $movimiento1->id_modulo=$ingreso->id;
            $movimiento1->descripcion=$request->observaciones;
            $movimiento1->save();
            
            $gasto = new Gastos;
            $gasto->nro=Gastos::where('empresa',Auth::user()->empresa)->count()+1;
            $gasto->empresa=Auth::user()->empresa;
            $gasto->cuenta=$banco->id;
            $gasto->tipo=4;
            $gasto->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
            $gasto->observaciones=$request->observaciones;
            $gasto->save();
            
            $items = new GastosCategoria;
            $items->valor=$this->precision($request->monto);
            $items->id_impuesto=$impuesto->id;
            $items->impuesto=$impuesto->porcentaje;
            $items->gasto=$gasto->id;
            $items->categoria=$categoria->id;
            $items->cant=1;
            $items->save();

            //ingresos
            $movimiento2=new Movimiento;
            $movimiento2->empresa=Auth::user()->empresa;
            $movimiento2->banco=$gasto->cuenta;
            $movimiento2->tipo=2;
            $movimiento2->saldo=$this->precision($request->monto);
            $movimiento2->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
            $movimiento2->modulo=3;
            $movimiento2->id_modulo=$gasto->id;
            $movimiento2->descripcion=$request->observaciones;
            $movimiento2->transferencia=$movimiento1->id;      
            $movimiento2->save();
            //Rename
            $movimiento1->transferencia=$movimiento2->id;    
            $movimiento1->save();
            $mensaje='Se ha creado satisfactoriamente la transferencia';
            return redirect('empresa/bancos/'.$banco->nro)->with('success', $mensaje);
        }
    }
}
