<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Mail;
use Validator;
use Illuminate\Validation\Rule;
use Auth;
use DB;

include_once(app_path() .'/../public/PHPExcel/Classes/PHPExcel.php');
use PHPExcel; use PHPExcel_IOFactory; use PHPExcel_Style_Alignment; use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Style_NumberFormat;
use ZipArchive;
use Barryvdh\DomPDF\Facade as PDF;
use PHPExcel_Shared_ZipArchive; use Session;

use App\Empresa;
use App\User;
use App\VentasExternas;
use App\TipoIdentificacion;
use App\AsociadosContacto;
use App\TipoEmpresa;
use App\Vendedor;
use App\Campos;
use App\Canal;
use App\Oficina;

class VentasExternasController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['inicio' => 'master', 'seccion' => 'ventas-externas', 'title' => 'Ventas Externas', 'icon' => 'fas fa-hand-holding-usd']);
    }
    
    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $tabla = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->where('campos_usuarios.id_modulo', 14)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();
        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
        $canales = Canal::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        view()->share(['middel' => true]);
        return view('ventas_externas.index')->with(compact('tabla', 'vendedores', 'canales'));
    }

    public function ventas(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $contactos = VentasExternas::query();

        if ($request->filtro == true) {
            if($request->identificacion){
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('nit', 'like', "%{$request->identificacion}%");
                });
            }
            if($request->nombre){
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('nombre', 'like', "%{$request->nombre}%");
                    $query->orWhere('apellido1', 'like', "%{$request->nombre}%");
                    $query->orWhere('apellido2', 'like', "%{$request->nombre}%");
                });
            }
            if($request->celular){
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('celular', 'like', "%{$request->celular}%");
                });
            }
            if($request->direccion){
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('direccion', 'like', "%{$request->direccion}%");
                });
            }
            if($request->barrio){
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('barrio', 'like', "%{$request->barrio}%");
                });
            }
            if($request->email){
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('email', 'like', "%{$request->email}%");
                });
            }
            if($request->estrato){
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('estrato', 'like', "%{$request->estrato}%");
                });
            }
            if($request->vendedor){
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('vendedor_externa', $request->vendedor);
                });
            }
            if($request->canal){
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('canal_externa', $request->canal);
                });
            }
        }

        $contactos->where('empresa', auth()->user()->empresa);
        $contactos->where('tipo_contacto', 3);
        $contactos->where('contactos.status', 1);
        $contactos->where('contactos.venta_externa', 1);

        if(Auth::user()->empresa()->oficina){
            if(auth()->user()->oficina){
                $contactos->where('contactos.oficina', auth()->user()->oficina);
            }
        }

        return datatables()->eloquent($contactos)
            ->editColumn('nombre', function (VentasExternas $contacto) {
                return $contacto->nombre();
                //return "<a href=" . route('contactos.show', $contacto->id) . ">{$contacto->nombre()}</div></a>";
            })
            ->editColumn('nit', function (VentasExternas $contacto) {
                return "{$contacto->tip_iden('mini')} {$contacto->nit}";
            })
            ->editColumn('telefono1', function (VentasExternas $contacto) {
                return $contacto->celular ? $contacto->celular : $contacto->telefono1;
            })
            ->editColumn('email', function (VentasExternas $contacto) {
                return $contacto->email;
            })
            ->editColumn('direccion', function (VentasExternas $contacto) {
                return $contacto->direccion;
            })
            ->editColumn('barrio', function (VentasExternas $contacto) {
                return $contacto->barrio;
            })
            ->editColumn('estrato', function (VentasExternas $contacto) {
                return ($contacto->estrato) ? $contacto->estrato : 'N/A';
            })
            ->editColumn('canal', function (VentasExternas $contacto) {
                return $contacto->canal_externa();
            })
            ->editColumn('vendedor', function (VentasExternas $contacto) {
                return $contacto->vendedor_externa();
            })
            ->addColumn('acciones', $modoLectura ?  "" : "ventas_externas.acciones")
            ->rawColumns(['acciones','nombre','nit','telefono1','email','direccion','barrio','estrato','canal','vendedor'])
            ->toJson();
    }

    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        $identificaciones=TipoIdentificacion::all();
        $paises  =DB::table('pais')->where('codigo', 'CO')->get();
        $departamentos = DB::table('departamentos')->get();
        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
        $canales = Canal::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $oficinas = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Oficina::where('id', Auth::user()->oficina)->get() : Oficina::where('empresa', Auth::user()->empresa)->where('status', 1)->get();

        view()->share(['icon' =>'', 'title' => 'Nueva Venta Externa', 'subseccion' => 'ventas-externas', 'middel'=>true]);

        return view('ventas_externas.create')->with(compact('identificaciones', 'paises', 'departamentos', 'vendedores', 'canales', 'oficinas'));
    }
    
    public function store(Request $request){
        $contacto = VentasExternas::where('nit', $request->nit)->where('empresa', Auth::user()->empresa)->first();
        
        if ($contacto) {
            $errors= (object) array();
            $errors->nit='LA IDENTIFICACIÓN ESTA REGISTRADA PARA OTRO USUARIO';
            return back()->withErrors($errors)->withInput();
        }

        $contacto = new VentasExternas;
        $contacto->empresa           = Auth::user()->empresa;
        $contacto->tip_iden          = $request->tip_iden;
        $contacto->dv                = $request->dvoriginal;
        $contacto->nit               = $request->nit;
        $contacto->nombre            = $request->nombre;
        $contacto->apellido1         = $request->apellido1;
        $contacto->apellido2         = $request->apellido2;
        $contacto->ciudad            = ucwords(mb_strtolower($request->ciudad));
        $contacto->barrio            = $request->barrio;
        $contacto->direccion         = $request->direccion;
        $contacto->email             = mb_strtolower($request->email);
        $contacto->telefono1         = $request->telefono1;
        $contacto->telefono2         = $request->telefono2;
        $contacto->fax               = $request->fax;
        $contacto->celular           = $request->celular;
        $contacto->estrato           = $request->estrato;
        $contacto->observaciones     = $request->observaciones;
        $contacto->tipo_contacto     = 3;
        $contacto->fk_idpais         = $request->pais;
        $contacto->fk_iddepartamento = $request->departamento;
        $contacto->fk_idmunicipio    = $request->municipio;
        $contacto->cod_postal        = $request->cod_postal;
        $contacto->tipo_empresa      = $request->tipo_empresa;
        $contacto->lista_precio      = $request->lista_precio;
        $contacto->venta_externa     = 1;
        $contacto->canal_externa     = $request->canal;
        $contacto->vendedor_externa  = $request->vendedor;
        $contacto->oficina           = $request->oficina;

        if ($request->tipo_persona == null) { 
            $contacto->tipo_persona   = 1;
            $contacto->responsableiva = 2;
        }else{
            $contacto->tipo_persona   = $request->tipo_persona;
            $contacto->responsableiva = $request->responsable;
        }

        $contacto->save();

        $mensaje = 'SE HA CREADO SATISFACTORIAMENTE LA VENTA EXTERNA';
        return redirect('empresa/ventas-externas')->with('success', $mensaje);
    }

    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $contacto = VentasExternas::where('id',$id)->where('empresa',Auth::user()->empresa)->first();
        
        if ($contacto) {
            $identificaciones = TipoIdentificacion::all();
            $paises           = DB::table('pais')->where('codigo', 'CO')->get();
            $departamentos    = DB::table('departamentos')->get();
            $vendedores       = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
            $canales          = Canal::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
            $oficinas = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Oficina::where('id', Auth::user()->oficina)->get() : Oficina::where('empresa', Auth::user()->empresa)->where('status', 1)->get();

            view()->share(['title' => 'Editar Venta Externa']);
            return view('ventas_externas.edit')->with(compact('identificaciones', 'paises', 'departamentos','vendedores','canales', 'contacto', 'oficinas'));
        }
        return redirect('empresa/ventas-externas')->with('danger', 'VENTA EXTERNA NO ENCONTRADA, INTENTE NUEVAMENTE');
    }

    public function update(Request $request, $id){
        $contacto = VentasExternas::where('id',$id)->where('empresa',Auth::user()->empresa)->first();
        if ($contacto) {
            $contacto->empresa           = Auth::user()->empresa;
            $contacto->tip_iden          = $request->tip_iden;
            $contacto->dv                =  $request->dvoriginal;
            $contacto->nit               = $request->nit;
            $contacto->ciudad            = ucwords(mb_strtolower($request->ciudad));
            $contacto->nombre            = $request->nombre;
            $contacto->apellido1         = $request->apellido1;
            $contacto->apellido2         = $request->apellido2;
            $contacto->barrio            = $request->barrio;
            $contacto->direccion         = $request->direccion;
            $contacto->email             = mb_strtolower($request->email);
            $contacto->telefono1         = $request->telefono1;
            $contacto->telefono2         = $request->telefono2;
            $contacto->fax               = $request->fax;
            $contacto->celular           = $request->celular;
            $contacto->estrato           = $request->estrato;
            $contacto->observaciones     = $request->observaciones;
            $contacto->fk_idpais         = $request->pais;
            $contacto->fk_iddepartamento = $request->departamento;
            $contacto->fk_idmunicipio    = $request->municipio;
            $contacto->cod_postal        = $request->cod_postal;
            $contacto->venta_externa     = 1;
            $contacto->canal_externa     = $request->canal;
            $contacto->vendedor_externa  = $request->vendedor;
            $contacto->oficina           = $request->oficina;
            $contacto->save();

            return redirect('empresa/ventas-externas')->with('success', 'SE HA MODIFICADO SATISFACTORIAMENTE LA VENTA EXTERNA');
        }
        return redirect('empresa/ventas-externas')->with('danger', 'VENTA EXTERNA NO ENCONTRADA, INTENTE NUEVAMENTE');
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $contacto = VentasExternas::find($id);
        if ($contacto) {
            view()->share(['title' => 'Ver Venta Externa']);
            return view('ventas_externas.show')->with(compact('contacto', 'id'));
        }
        return redirect('empresa/ventas-externas')->with('danger', 'VENTA EXTERNA NO ENCONTRADA, INTENTE NUEVAMENTE');
    }

    public function destroy(Request $request, $id){
        $contacto = VentasExternas::where('id',$id)->where('empresa',Auth::user()->empresa)->first();
        if ($contacto) {
            $contacto->delete();
            return redirect('empresa/ventas-externas')->with('success', 'SE HA ELIMINADO SATISFACTORIAMENTE LA VENTA EXTERNA');
        }else{
            return redirect('empresa/ventas-externas')->with('danger', 'VENTA EXTERNA NO ENCONTRADA, INTENTE NUEVAMENTE');
        }
    }

    public function aprobar(Request $request, $id){
        $contacto = VentasExternas::where('id',$id)->where('empresa',Auth::user()->empresa)->first();
        if ($contacto) {
            $contacto->tipo_contacto = 0;
            $contacto->save();

            return redirect('empresa/ventas-externas')->with('success', 'SE HA APROBADO LA VENTA EXTERNA Y SE HA REGISTRADO EL CLIENTE SATISFACTORIAMENTE');
        }
        return redirect('empresa/ventas-externas')->with('danger', 'VENTA EXTERNA NO ENCONTRADA, INTENTE NUEVAMENTE');
    }

    public function destroy_lote($ventas){
        $this->getAllPermissions(Auth::user()->id);

        $succ = 0; $fail = 0;

        $ventas = explode(",", $ventas);

        for ($i=0; $i < count($ventas) ; $i++) {
            $venta = VentasExternas::find($ventas[$i]);
            if ($venta) {
                $venta->delete();
                $succ++;
            }else{
                $fail++;
            }
        }

        return response()->json([
            'success'   => true,
            'fallidos'  => $fail,
            'correctos' => $succ,
            'state'     => 'eliminadas'
        ]);
    }

    public function state_lote($ventas){
        $this->getAllPermissions(Auth::user()->id);

        $succ = 0; $fail = 0;

        $ventas = explode(",", $ventas);

        for ($i=0; $i < count($ventas) ; $i++) {
            $venta = VentasExternas::find($ventas[$i]);
            if ($venta) {
                $venta->tipo_contacto = 0;
                $venta->save();
                $succ++;
            }else{
                $fail++;
            }
        }

        return response()->json([
            'success'   => true,
            'fallidos'  => $fail,
            'correctos' => $succ,
            'state'     => 'aprobadas'
        ]);
    }
}
