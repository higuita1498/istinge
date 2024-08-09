<?php

namespace App\Http\Controllers;

use App\Campos;
use App\Contacto;
use App\Contrato;
use App\CRM;
use App\Empresa;
use App\Funcion;
use App\Mikrotik;
use App\Model\Ingresos\Factura;
use App\Model\Inventario\ListaPrecios;
use App\Oficina;
use App\TipoEmpresa;
use App\TipoIdentificacion;
use App\Vendedor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPExcel;

use Exception;
use Illuminate\Support\Facades\Auth;

include_once app_path().'/../public/PHPExcel/Classes/PHPExcel.php';
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use RouterosAPI;

include_once app_path().'/../public/routeros_api.class.php';
use Session;

class ContactosController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['inicio' => 'master', 'seccion' => 'contactos', 'title' => 'Clientes', 'icon' => 'fas fa-users']);
    }

    public function index(Request $request)
    {
        // return 'ok';
        $this->getAllPermissions(Auth::user()->id);
        $tabla = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->where('campos_usuarios.id_modulo', 1)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();

        view()->share(['invert' => true]);

        return view('contactos.indexnew');
    }

    public function contactos(Request $request, $tipo_usuario)
    {
        $municipio = DB::table('municipios')->select('id')->where('nombre', '=', $request->municipio)->first();
        $modoLectura = auth()->user()->modo_lectura();
        $contactos = Contacto::query();


        if ($request->filtro == true) {
            if ($request->identificacion) {
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('nit', 'like', "%{$request->identificacion}%");
                });
            }
            if ($request->nombre) {
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('nombre', 'like', "%{$request->nombre}%");
                });
            }
            if ($request->municipio) {

                $contactos->where(function ($query) use ($municipio) {
                    $query->orWhere('fk_idmunicipio', 'like', "%{$municipio->id}%");
                });
            }
            if ($request->apellido) {
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('apellido1', 'like', "%{$request->apellido}%");
                    $query->orWhere('apellido2', 'like', "%{$request->apellido}%");
                    $query->orWhere('nombre', 'like', "%{$request->apellido}%");
                });
            }
            if ($request->celular) {
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('celular', 'like', "%{$request->celular}%");
                });
            }
            if ($request->direccion) {

                $direccion = $request->direccion;
                $direccion = explode(' ', $direccion);
                $direccion = array_reverse($direccion);

                foreach ($direccion as $dir) {
                    $dir = strtolower($dir);
                    $dir = str_replace('#', '', $dir);
                    //$dir = str_replace("-","",$dir);
                    //$dir = str_replace("/","",$dir);

                    $contactos->where(function ($query) use ($dir) {
                        $query->orWhere('direccion', 'like', "%{$dir}%");
                    });
                }

                /*
                $contactos->where(function ($query) use ($request) {
                   // $query->orWhere('direccion', 'like', "%{$request->direccion}%");
                });
                */
            }
            if ($request->barrio) {
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('barrio', 'like', "%{$request->barrio}%");
                });
            }
            if ($request->vereda) {
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('vereda', 'like', "%{$request->vereda}%");
                });
            }
            if ($request->email) {
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('email', 'like', "%{$request->email}%");
                });
            }
            if ($request->serial_onu) {
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('serial_onu', 'like', "%{$request->serial_onu}%");
                });
            }
            if ($request->estrato) {
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('estrato', 'like', "%{$request->estrato}%");
                });
            }
            if ($request->otra_opcion && $request->otra_opcion == "opcion_1") {
                $contactos->where(function ($query) use ($request) {
                    $query->orWhere('saldo_favor', '>', 0);
                });
            }
            if ($request->t_contrato == 1) {
                $contactos->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('contracts')
                        ->whereRaw('contactos.id = contracts.client_id');
                });
            } elseif ($request->t_contrato == 2) {
                $contactos->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('contracts')
                        ->whereRaw('contactos.id = contracts.client_id');
                });
            }
        }

        $contactos->where('contactos.empresa', auth()->user()->empresa);
        $contactos->whereIn('tipo_contacto', [$tipo_usuario, 2]);
        $contactos->where('contactos.status', 1);



        // if (Auth::user()->empresa()->oficina) {
        //     if (auth()->user()->oficina) {
        //         $contactos->where('contactos.oficina', auth()->user()->oficina);
        //     }
        // }
        return datatables()->eloquent($contactos)
             ->editColumn('serial_onu', function (Contacto $contacto) {

                 return $contacto->serial_onu;
             })
            ->editColumn('nombre', function (Contacto $contacto) {
                return '<a href='.route('contactos.show', $contacto->id).">{$contacto->nombre} {$contacto->apellidos()}</div></a>";
            })
            ->editColumn('nit', function (Contacto $contacto) {
                return "{$contacto->tip_iden('mini')} {$contacto->nit}";
            })
            ->editColumn('telefono1', function (Contacto $contacto) {
                return $contacto->celular ? $contacto->celular : $contacto->telefono1;
            })
            ->editColumn('email', function (Contacto $contacto) {
                return $contacto->email;
            })
            ->editColumn('direccion', function (Contacto $contacto) {
                return $contacto->direccion;
            })
            ->editColumn('barrio', function (Contacto $contacto) {
                return $contacto->barrio;
            })
            ->editColumn('vereda', function (Contacto $contacto) {
                return $contacto->vereda;
            })
            ->editColumn('contrato', function (Contacto $contacto) {
                return $contacto->contract();
            })
            ->editColumn('fecha_contrato', function (Contacto $contacto) {
                return ($contacto->fecha_contrato) ? date('d-m-Y g:i:s A', strtotime($contacto->fecha_contrato)) : '- - - -';
            })
            ->editColumn('fk_idmunicipio', function (Contacto $contacto) {
                return $contacto->municipio()->nombre;
            })
            ->editColumn('radicado', function (Contacto $contacto) {
                return $contacto->radicados();
            })
            ->editColumn('ip', function (Contacto $contacto) {
                dd($contacto->contract('true'));
                if ($contacto->contract('true') != 'N/A') {
                    $puerto = $contacto->contrato()->puerto ? ':'.$contacto->contrato()->puerto->nombre : '';
                }

                return 'N/A';

                // return ($contacto->contract('true') == 'N/A') ? 'N/A' : '<a href="http://'.$contacto->contract('true').''.$puerto.'" target="_blank">'.$contacto->contract('true').''.$puerto.' <i class="fas fa-external-link-alt"></i></a>';
            })
            ->editColumn('estrato', function (Contacto $contacto) {
                return ($contacto->estrato) ? $contacto->estrato : 'N/A';
            })

            ->addColumn('acciones', $modoLectura ? '' : 'contactos.acciones-contactos')
            ->rawColumns(['acciones', 'nombre', 'contrato', 'ip'])
            ->toJson();
    }

    public function clientes(Request $request)
    {

        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Clientes', 'subseccion' => 'clientes']);
        $busqueda = false;
        if ($request->name_1 || $request->name_2 || $request->name_3 || isset($request->name_4) || $request->name_5) {
            $busqueda = 'contactos.clientes';
        }
        $tipo = '/0';
        $tipos_empresa = TipoEmpresa::where('empresa', Auth::user()->empresa)->get();
        // $contactos = $this->busqueda($request, [0, 2]);
        $totalContactos = Contacto::where('empresa', Auth::user()->empresa)->count();
        // $contactos = Contacto::where('empresa', Auth::user()->empresa)->get();
        $contactos = DB::table('contactos')->join('municipios', 'contactos.fk_idmunicipio', '=', 'municipios.id')->select('contactos.*', 'municipios.nombre as nombre_municipio')->get();
        $tipo_usuario = 0;
        $tabla = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->where('campos_usuarios.id_modulo', 1)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();

        view()->share(['invert' => true]);

        return view('contactos.indexnew')->with(compact('contactos', 'totalContactos', 'tipo_usuario', 'tabla'));
    }

    public function proveedores(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);
        $busqueda = false;
        if ($request->name_1 || $request->name_2 || $request->name_3 || isset($request->name_4) || $request->name_5) {
            $busqueda = 'contactos.proveedores';
        }
        $tipo = '/1';
        view()->share(['title' => 'Proveedores', 'subseccion' => 'proveedores']);
        $tipos_empresa = TipoEmpresa::where('empresa', Auth::user()->empresa)->get();
        $contactos = $this->busqueda($request, [1, 2]);
        $totalContactos = Contacto::where('empresa', Auth::user()->empresa)->count();
        $tipo_usuario = 1;
        $tabla = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->where('campos_usuarios.id_modulo', 1)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();
        view()->share(['invert' => true]);

        return view('contactos.indexnew')->with(compact('contactos', 'tipo', 'request', 'busqueda', 'tipos_empresa', 'totalContactos', 'tipo_usuario', 'tabla'));
    }

    public function busqueda($request, $tipo = false)
    {

        $this->getAllPermissions(Auth::user()->id);
        $campos = [
            'id',
            'contactos.nombre',
            'contactos.nit',
            'contactos.telefono1',
            'contactos.tipo_contacto',
            'te.nombre',
        ];
        if (! $request->orderby) {
            $request->orderby = 0;
            $request->order = 1;
        }
        $orderby = $campos[$request->orderby];
        $order = $request->order == 1 ? 'DESC' : 'ASC';
        $appends = ['orderby' => $request->orderby, 'order' => $request->order];
        $contactos = Contacto::join('tipos_empresa as te', 'te.id', '=', 'contactos.tipo_empresa')
            ->leftJoin('vendedores as v', 'contactos.vendedor', '=', 'v.id')
            ->select(
                'contactos.*',
                'te.nombre as tipo_emp',
                DB::raw('v.nombre as nombrevendedor', 'count(contactos.id) as total')
            )
            ->where('contactos.empresa', Auth::user()->empresa)->where('lectura', 0);
        if ($request->name_1) {
            $appends['name_1'] = $request->name_1;
            $contactos = $contactos->where('contactos.nombre', 'like', '%'.$request->name_1.'%');
        }
        if ($request->name_2) {
            $appends['name_2'] = $request->name_2;
            $contactos = $contactos->where('contactos.nit', 'like', '%'.$request->name_2.'%');
        }
        if ($request->name_3) {
            $appends['name_3'] = $request->name_3;
            $contactos = $contactos->where('contactos.telefono1', 'like', '%'.$request->name_3.'%');
        }
        if (isset($request->name_4)) {
            $appends['name_4'] = $request->name_4;
            $contactos = $contactos->where('contactos.tipo_contacto', $request->name_4);
        }
        if ($tipo) {
            $contactos = $contactos->whereIn('contactos.tipo_contacto', $tipo);
        }
        if ($request->name_5) {
            $appends['name_5'] = $request->name_5;
            $contactos = $contactos->where('contactos.tipo_empresa', $request->name_5);
        }
        if ($request->name_6) {
            $appends['name_6'] = $request->name_6;
            $contactos = $contactos->where('v.nombre', 'like', '%'.$request->name_6.'%');
        }
        $contactos = $contactos->OrderBy($orderby, $order)->paginate(25)->appends($appends);

         return $contactos;
    }

    public function show($id)
    {

        $this->getAllPermissions(Auth::user()->id);

        $contacto = Contacto::join('tipos_identificacion AS I', 'I.id', '=', 'contactos.tip_iden')->where('contactos.id', $id)->where('contactos.empresa', Auth::user()->empresa)->select('contactos.*', 'I.identificacion')->first();

        if ($contacto) {
            if ($contacto->tipo_contacto == 0) {
                view()->share(['title' => $contacto->nombre.' '.$contacto->apellidos(), 'subseccion' => 'clientes', 'middel' => true]);
            } else {
                view()->share(['title' => $contacto->nombre.' '.$contacto->apellidos(), 'subseccion' => 'proveedores', 'middel' => true]);
            }

            $user_app = DB::table('usuarios_app')->where('id_cliente', $contacto->id)->where('status', 1)->first();
            $contratos = Contrato::where('client_id', $contacto->id)->where('status', 1)->get();

            return view('contactos.show')->with(compact('contacto', 'id', 'user_app', 'contratos'));
        }

        return redirect('empresa/contactos')->with('danger', 'CLIENTE NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function create()
    {

        $this->getAllPermissions(Auth::user()->id);
        $identificaciones = TipoIdentificacion::all();
        $paises = DB::table('pais')->where('codigo', 'CO')->get();
        $departamentos = DB::table('departamentos')->get();
        $oficinas = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Oficina::where('id', Auth::user()->oficina)->get() : Oficina::where('empresa', Auth::user()->empresa)->where('status', 1)->get();

        view()->share(['icon' => '', 'title' => 'Nuevo Contacto', 'subseccion' => 'clientes', 'middel' => true]);

        return view('contactos.create')->with(compact('identificaciones', 'paises', 'departamentos', 'oficinas'));
    }

    public function createp()
    {
        $this->getAllPermissions(Auth::user()->id);
        $identificaciones = TipoIdentificacion::all();

        $vendedores = Vendedor::where('empresa', Auth::user()->empresa)
            ->where('estado', 1)
            ->get();

        $listas = ListaPrecios::where('empresa', Auth::user()->empresa)
            ->where('status', 1)
            ->get();

        $tipos_empresa = TipoEmpresa::where('empresa', Auth::user()->empresa)
            ->get();

        $prefijos = DB::table('prefijos_telefonicos')->get();

        $paises = DB::table('pais')->get();

        $departamentos = DB::table('departamentos')->get();

        $transportadora_id = TipoEmpresa::where('empresa', auth()->user()->empresa)
            ->where('nombre', 'TRANSPORTADORA')
            ->first();

        if ($transportadora_id) {
            $transportadoras = Contacto::where('empresa', auth()->user()->empresa)
                ->where('tipo_empresa', $transportadora_id->id)
                ->get();
        } else {
            $transportadoras = null;
        }

        $oficinas = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Oficina::where('id', Auth::user()->oficina)->get() : Oficina::where('empresa', Auth::user()->empresa)->where('status', 1)->get();

        view()->share(['title' => 'Nuevo Proveedor', 'subseccion' => 'proveedores', 'middel' => true]);

        return view('contactos.createp')->with(compact(
            'identificaciones',
            'tipos_empresa',
            'prefijos',
            'vendedores',
            'listas',
            'paises',
            'departamentos',
            'transportadoras',
            'oficinas'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_contacto' => 'required',
        ]);
        $contacto = Contacto::where('nit', $request->nit)->where('status', 1)->where('empresa', Auth::user()->empresa)->first();

        if ($contacto) {
            $errors = (object) [];
            $errors->nit = 'La Identificación esta registrada para otro contacto';

            return back()->withErrors($errors)->withInput();
        }

        $contacto = new Contacto;
        $contacto->empresa = Auth::user()->empresa;
        $contacto->tip_iden = $request->tip_iden;
        $contacto->dv = $request->dvoriginal;
        $contacto->nit = $request->nit;
        $contacto->nombre = $request->nombre;
        $contacto->apellido1 = $request->apellido1;
        $contacto->apellido2 = $request->apellido2;
        $contacto->ciudad = ucwords(mb_strtolower($request->ciudad));
        $contacto->barrio = $request->barrio;
        $contacto->vereda = $request->vereda;
        $contacto->direccion = $request->direccion;
        $contacto->email = mb_strtolower($request->email);
        $contacto->telefono1 = $request->telefono1;
        $contacto->telefono2 = $request->telefono2;
        $contacto->fax = $request->fax;
        $contacto->celular = $request->celular;
        $contacto->estrato = $request->estrato;
        $contacto->observaciones = $request->observaciones;
        $contacto->tipo_contacto = count($request->tipo_contacto) == 2 ? 2 : $request->tipo_contacto[0];
        $contacto->plan_velocidad    = 0;
        $contacto->costo_instalacion = "a";

        $contacto->fk_idpais = $request->pais;
        $contacto->fk_iddepartamento = $request->departamento;
        $contacto->fk_idmunicipio = $request->municipio;
        $contacto->cod_postal = $request->cod_postal;
        if (empty($request->boton_emision)) {
            // Asignar el valor "1" si está vacío o no está definido
            $request->boton_emision = 0;
        }
        $contacto->boton_emision = $request->boton_emision;
        //nuevos cmapos agregados
        $contacto->monitoreo = $request->monitoreo;
        $contacto->refiere = $request->refiere;
        $contacto->combo_int_tv = $request->combo_int_tv;
        $contacto->referencia_1 = $request->referencia_1;
        $contacto->referencia_2 = $request->referencia_2;
        $contacto->cierra_venta = $request->cierra_venta;

        if ($request->tipo_persona == null) {
            $contacto->tipo_persona = 1;
            $contacto->responsableiva = 2;
        } else {
            $contacto->tipo_persona = $request->tipo_persona;
            $contacto->responsableiva = $request->responsable;
        }

        $contacto->tipo_empresa = $request->tipo_empresa;
        $contacto->lista_precio = $request->lista_precio;
        $contacto->vendedor = $request->vendedor;
        $contacto->oficina = $request->oficina;

        $contacto->save();

        if ($contacto->tipo_contacto == 0) {
            $mensaje = 'SE HA CREADO SATISFACTORIAMENTE EL CLIENTE';

            return redirect('empresa/contactos/clientes')->with('success', $mensaje);
        } else {
            $mensaje = 'SE HA CREADO SATISFACTORIAMENTE EL PROVEEDOR';

            return redirect('empresa/contactos/proveedores')->with('success', $mensaje);
        }
    }

    public function storeBack(Request $request)
    {
        $contacto = Contacto::where('nit', $request->nit)->where('empresa', Auth::user()->empresa)->first();
        if ($contacto) {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'La Identificación esta registrada para otro contacto';
            echo json_encode($arrayPost);
            exit;
        }

        if (! $request->tipo_contacto) {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'El Tipo de Contacto es requerido';
            echo json_encode($arrayPost);
            exit;
        }

        $contacto = new Contacto;
        $contacto->empresa = Auth::user()->empresa;
        $contacto->tip_iden = $request->tip_iden;
        $contacto->dv = $request->dvoriginal;
        $contacto->nit = $request->nit;
        $contacto->nombre = $request->nombre;
        $contacto->apellido1 = $request->apellido1;
        $contacto->apellido2 = $request->apellido2;
        $contacto->ciudad = ucwords(mb_strtolower($request->ciudad));
        $contacto->barrio = $request->barrio;
        $contacto->vereda = $request->vereda;
        $contacto->direccion = $request->direccion;
        $contacto->email = mb_strtolower($request->email);
        $contacto->telefono1 = $request->telefono1;
        $contacto->telefono2 = $request->telefono2;
        $contacto->fax = $request->fax;
        $contacto->celular = $request->celular;
        $contacto->estrato = $request->estrato;
        $contacto->tipo_contacto = count($request->tipo_contacto) == 2 ? 2 : $request->tipo_contacto[0];
        $contacto->observaciones = $request->observaciones;

        $contacto->fk_idpais = $request->pais;
        $contacto->fk_iddepartamento = $request->departamento;
        $contacto->fk_idmunicipio = $request->municipio;
        $contacto->cod_postal = $request->cod_postal;
        $contacto->oficina = $request->oficina;

        if ($request->tipo_persona == null) {
            $contacto->tipo_persona = 1;
            $contacto->responsableiva = 2;
        } else {
            $contacto->tipo_persona = $request->tipo_persona;
            $contacto->responsableiva = $request->responsable;
        }

        $contacto->save();

        $contacId = Contacto::all()->last()->id;
        $contac = Contacto::all()->last()->nombre;
        $contacNit = Contacto::all()->last()->nit;

        if ($contacto) {
            $arrayPost['status'] = 'OK';
            $arrayPost['id'] = $contacId;
            $arrayPost['contacto'] = $contac;
            $arrayPost['nit'] = $contacNit;
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function edit($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $contacto = Contacto::where('id', $id)->where('empresa', Auth::user()->empresa)->first();

        if ($contacto) {
            $identificaciones = TipoIdentificacion::all();
            $paises = DB::table('pais')->get();
            $departamentos = DB::table('departamentos')->get();

            $vendedores = Vendedor::where('empresa', Auth::user()->empresa)->where('estado', 1)->get();
            $listas = ListaPrecios::where('empresa', Auth::user()->empresa)->where('status', 1)->get();
            $tipos_empresa = TipoEmpresa::where('empresa', Auth::user()->empresa)->get();
            $oficinas = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Oficina::where('id', Auth::user()->oficina)->get() : Oficina::where('empresa', Auth::user()->empresa)->where('status', 1)->get();

            session(['url_search' => url()->previous()]);

            if ($contacto->tipo_contacto == 0) {
                view()->share(['title' => 'Editar: '.$contacto->nombre.' '.$contacto->apellidos(), 'subseccion' => 'clientes', 'middel' => true, 'icon' => '']);
            } else {
                view()->share(['title' => 'Editar: '.$contacto->nombre.' '.$contacto->apellidos(), 'subseccion' => 'proveedores', 'middel' => true, 'icon' => '']);
            }

            return view('contactos.edit')->with(compact('contacto', 'identificaciones', 'paises', 'departamentos', 'vendedores', 'listas', 'tipos_empresa', 'oficinas'));
        }

        return redirect('empresa/contactos')->with('danger', 'CLIENTE NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo_contacto' => 'required',
        ]);
        $empresa = Auth::user()->empresa;
        $error = Contacto::where('nit', $request->nit)->where('tip_iden', $request->tip_iden)
            ->where('id', '<>', $id)
            ->where('empresa', $empresa)
            ->where('status', 1)
            ->first();

        if ($error) {
            $errors = (object) [];
            $errors->nit = 'La Identificación esta registrada para otro contacto';

            return back()->withErrors($errors)->withInput();
        }
        $contacto = Contacto::where('id', $id)->where('empresa', $empresa)->first();
        if ($contacto) {
            $contacto->empresa = $empresa;
            $contacto->tip_iden = $request->tip_iden;
            $contacto->dv = $request->dvoriginal;
            $contacto->nit = $request->nit;
            $contacto->ciudad = ucwords(mb_strtolower($request->ciudad));
            $contacto->nombre = $request->nombre;
            $contacto->apellido1 = $request->apellido1;
            $contacto->apellido2 = $request->apellido2;
            $contacto->barrio = $request->barrio;
            $contacto->vereda = $request->vereda;
            $contacto->direccion = $request->direccion;
            $contacto->email = mb_strtolower($request->email);
            $contacto->telefono1 = $request->telefono1;
            $contacto->telefono2 = $request->telefono2;
            $contacto->fax = $request->fax;
            $contacto->celular = $request->celular;
            $contacto->estrato = $request->estrato;
            $contacto->observaciones = $request->observaciones;
            $contacto->serial_onu = $request->serial_onu;
            $contacto->tipo_contacto = count($request->tipo_contacto) == 2 ? 2 : $request->tipo_contacto[0];
            $contacto->fk_idpais = $request->pais;
            $contacto->fk_iddepartamento = $request->departamento;
            $contacto->fk_idmunicipio = $request->municipio;
            $contacto->cod_postal = $request->cod_postal;
            $contacto->tipo_empresa = $request->tipo_empresa;
            $contacto->lista_precio = $request->lista_precio;
            $contacto->vendedor = $request->vendedor;
            $contacto->oficina = $request->oficina;
            $contacto->router = $request->router;
            $contacto->boton_emision = $request->boton_emision;
            //nuevos cmapos agregados
            $contacto->monitoreo = $request->monitoreo;
            $contacto->refiere = $request->refiere;
            $contacto->combo_int_tv = $request->combo_int_tv;
            $contacto->referencia_1 = $request->referencia_1;
            $contacto->referencia_2 = $request->referencia_2;
            $contacto->cierra_venta = $request->cierra_venta;
            $contacto->plan_velocidad    = 0;
            $contacto->costo_instalacion = "a";

            $contacto->save();

            $contrato = Contrato::where('client_id', $contacto->id)->where('status', 1)->first();

            if ($contrato) {
                $mikrotik = Mikrotik::find($contrato->server_configuration_id);
                $servicio = $this->normaliza($contacto->nombre.' '.$contacto->apellido1.' '.$contacto->apellido2).'-'.$contrato->nro;
                if ($mikrotik) {
                    $API = new RouterosAPI();
                    $API->port = $mikrotik->puerto_api;
                    //$API->debug = true;

                    if ($API->connect($mikrotik->ip, $mikrotik->usuario, $mikrotik->clave)) {
                        /*PPPOE*/
                        if ($contrato->conexion == 1) {
                            $API->comm("ppp/secrets\n=find\n=name=$contrato->servicio\n=[set\n=remote-address=$request->ip]");
                        }

                        /*DHCP*/
                        if ($contrato->conexion == 2) {

                        }

                        /*IP ESTÁTICA*/
                        if ($contrato->conexion == 3) {
                            $name = $API->comm('/queue/simple/getall', [
                                '?comment' => $contrato->servicio,
                            ]
                            );

                            if ($name) {
                                $API->comm('/queue/simple/set', [
                                    '.id' => $name[0]['.id'],
                                    'name' => $servicio,       // NOMBRE CLIENTE
                                    'comment' => $servicio,       // NOMBRE CLIENTE
                                ]
                                );
                            }
                        }

                        /*VLAN*/
                        if ($contrato->conexion == 4) {

                        }
                    }

                    $contrato->servicio = $servicio;
                    $contrato->save();
                    $API->disconnect();
                }
            }

            if ($contacto->tipo_contacto == 0) {
                $mensaje = 'SE HA MODIFICADO SATISFACTORIAMENTE EL CLIENTE';

                return redirect('empresa/contactos/clientes')->with('success', $mensaje);
            } else {
                $mensaje = 'SE HA MODIFICADO SATISFACTORIAMENTE EL PROVEEDOR';

                return redirect('empresa/contactos/proveedores')->with('success', $mensaje);
            }
        }

        return redirect('empresa/contactos')->with('danger', 'CLIENTE NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function destroy($id)
    {
        $contacto = Contacto::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        $contrato = Contrato::where('client_id', $contacto->id)->first();
        $empresa = Empresa::find(1);
        if ($contacto) {
            $tipo_usuario = $contacto->tipo_usuario;
            $contacto->status = 0;
            $contacto->save();
            if ($contrato) {
                $contrato->status = 0;
                $contrato->state = 'disabled';
                $contrato->save();
            }
            $mensaje = 'SE HA ELIMINADO EL CLIENTE Y SU CONTRATO RELACIONADO';

            $tipo_usuario = ($tipo_usuario == 0) ? 'clientes' : 'proveedores';

            return redirect('empresa/contactos/'.$tipo_usuario)->with('success', $mensaje);
        } else {
            return redirect('empresa/contactos')->with('danger', 'CLIENTE NO ENCONTRADO, INTENTE NUEVAMENTE');
        }
    }

    /*
    * Generar un json con los datos del contacto
    */
    public function json($id = false, $type = false)
    {

        if (! $id) {
            $contactos = Contacto::where('empresa', Auth::user()->empresa)->whereIn('tipo_contacto', [0, 2])->get();
            if ($contactos) {
                return json_encode($contactos);
            }
        }

        $contacto = Contacto::join('contracts as cs', 'contactos.id', '=', 'cs.client_id')->where('contactos.id', $id)->first();
        if (isset($contacto->plan_id)) {
            if ($contacto->plan_id) {
                $contacto = DB::select("SELECT C.id, C.nombre, C.boton_emision ,C.apellido1, C.apellido2, C.nit, C.tip_iden, C.telefono1, C.celular, C.estrato,
                C.saldo_favor, C.saldo_favor2 ,CS.public_id as contrato, CS.facturacion, I.id as plan,
                 GC.fecha_corte, GC.fecha_suspension, CS.servicio_tv, CS.servicio_otro FROM contactos AS C INNER JOIN contracts AS CS ON (C.id = CS.client_id)
                 INNER JOIN planes_velocidad AS P ON (P.id = CS.plan_id) INNER JOIN inventario AS I ON (I.id = P.item)
                 INNER JOIN grupos_corte AS GC ON (GC.id = CS.grupo_corte)
                 WHERE C.status = '1' AND  C.id = '".$id."'");
            }
        }

        if ($contacto) {
            return json_encode($contacto);
        } else {
            return json_encode(Contacto::find($id));
        }
    }

    /*
    * Generar un archivo xml de los contactos
    */
    public function exportar($tipo = 2)
    {
        $objPHPExcel = new PHPExcel();
        $tituloReporte = 'Reporte de Contactos de '.Auth::user()->empresa()->nombre;
        $titulosColumnas = ['Nombres', 'Apellido1', 'Apellido2', 'Tipo de identificacion', 'Identificacion', 'DV', 'Pais', 'Departamento', 'Municipio', 'Codigo postal', 'Telefono', 'Celular', 'Direccion', 'Verada/Corregimiento', 'Barrio', 'Ciudad', 'Correo Electronico', 'Estrato', 'Observaciones', 'Tipo de Contacto'];
        $letras = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

        $objPHPExcel->getProperties()->setCreator('Sistema') // Nombre del autor
            ->setLastModifiedBy('Sistema') //Ultimo usuario que lo modific���
            ->setTitle('Reporte Excel Contactos') // Titulo
            ->setSubject('Reporte Excel Contactos') //Asunto
            ->setDescription('Reporte de Contactos') //Descripci���n
            ->setKeywords('reporte Contactos') //Etiquetas
            ->setCategory('Reporte excel'); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:D1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', $tituloReporte);
        // Titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A2:C2');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2', 'Fecha '.date('d-m-Y')); // Titulo del reporte

        $estilo = ['font' => ['bold' => true, 'size' => 12, 'name' => 'Times New Roman'], 'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ]];
        $objPHPExcel->getActiveSheet()->getStyle('A1:T3')->applyFromArray($estilo);

        $estilo = [
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => substr(Auth::user()->empresa()->color, 1)],
            ],
            'font' => [
                'bold' => true,
                'size' => 12,
                'name' => 'Times New Roman',
                'color' => [
                    'rgb' => 'FFFFFF',
                ],
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $objPHPExcel->getActiveSheet()->getStyle('A3:T3')->applyFromArray($estilo);

        for ($i = 0; $i < count($titulosColumnas); $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $i = 4;
        $letra = 0;
        $contactos = Contacto::where('empresa', Auth::user()->empresa)->where('status', 1)->get();
        if ($tipo != 2) {
            $contactos = $contactos->whereIn('tipo_contacto', [$tipo, 2]);
        }

        $empresa = Empresa::find(Auth::user()->empresa);

        foreach ($contactos as $contacto) {

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $contacto->nombre)
                ->setCellValue($letras[1].$i, $contacto->apellido1)
                ->setCellValue($letras[2].$i, $contacto->apellido2)
                ->setCellValue($letras[3].$i, $contacto->tip_iden())
                ->setCellValue($letras[4].$i, $contacto->nit)
                ->setCellValue($letras[5].$i, $contacto->dv)
                ->setCellValue($letras[6].$i, $contacto->pais()->nombre)
                ->setCellValue($letras[7].$i, $contacto->departamento()->nombre)
                ->setCellValue($letras[8].$i, $contacto->municipio()->nombre)
                ->setCellValue($letras[9].$i, $contacto->cod_postal)
                ->setCellValue($letras[10].$i, $contacto->telefono1)
                ->setCellValue($letras[11].$i, $contacto->celular)
                ->setCellValue($letras[12].$i, $contacto->direccion)
                ->setCellValue($letras[13].$i, $contacto->vereda)
                ->setCellValue($letras[14].$i, $contacto->barrio)
                ->setCellValue($letras[15].$i, $contacto->ciudad)
                ->setCellValue($letras[16].$i, $contacto->email)
                ->setCellValue($letras[17].$i, $contacto->estrato)
                ->setCellValue($letras[18].$i, $contacto->observaciones)
                ->setCellValue($letras[19].$i, $contacto->tipo_contacto());
            $i++;
        }

        $estilo = ['font' => ['size' => 12, 'name' => 'Times New Roman'],
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                ],
            ], 'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER]];
        $objPHPExcel->getActiveSheet()->getStyle('A3:T'.$i)->applyFromArray($estilo);

        for ($i = 'A'; $i <= $letras[20]; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(true);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de Contactos');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A5');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0, 4);
        $objPHPExcel->setActiveSheetIndex(0);
        header('Pragma: no-cache');
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Contactos.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * Vista para importar los contactos
     *
     * @return view
     */
    public function importar()
    {
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Importar Contactos desde Excel', 'subseccion' => 'clientes']);

        $identificaciones = TipoIdentificacion::all();

        return view('contactos.importar')->with(compact('identificaciones'));
    }

    /**
     * Registrar o modificar los datos del contacto
     *
     * @return redirect
     */
    public function cargando(Request $request)
    {
        // TODO: Este método no implementa o casi no implementa chequeos de
        // valores nulos, lo más probable es que falle en cualquier momento
        // de forma inesperada.
        try {
            DB::beginTransaction();
            $request->validate([
                'archivo' => 'required|mimes:xlsx',
            ], [
                'archivo.mimes' => 'El archivo debe ser de extensión xlsx',
            ]);

            $create = 0;
            $modf = 0;
            $imagen = $request->file('archivo');
            $nombre_imagen = 'archivo.'.$imagen->getClientOriginalExtension();
            $path = public_path().'/images/Empresas/Empresa'.Auth::user()->empresa;
            $imagen->move($path, $nombre_imagen);
            ini_set('max_execution_time', 500);
            $fileWithPath = $path.'/'.$nombre_imagen;
            //Identificando el tipo de archivo
            $inputFileType = PHPExcel_IOFactory::identify($fileWithPath);
            //Creando el lector.
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            //Cargando al lector de excel el archivo, le pasamos la ubicacion
            $objPHPExcel = $objReader->load($fileWithPath);
            //obtengo la hoja 0
            $sheet = $objPHPExcel->getSheet(0);
            //obtiene el tamaño de filas
            $highestRow = $sheet->getHighestRow();
            //obtiene el tamaño de columnas
            $highestColumn = $sheet->getHighestColumn();

            for ($row = 4; $row <= $highestRow; $row++) {
                $request = (object) [];
                //obtengo el A4 desde donde empieza la data
                $nombre = $sheet->getCell('A'.$row)->getValue();
                if (empty($nombre)) {
                    break;
                }

                $request->apellido1 = $sheet->getCell('B'.$row)->getValue();
                $request->apellido2 = $sheet->getCell('C'.$row)->getValue();
                $request->tip_iden = $sheet->getCell('D'.$row)->getValue();
                $request->nit = $sheet->getCell('E'.$row)->getValue();
                $request->dv = $sheet->getCell('F'.$row)->getValue();
                $request->fk_idpais = $sheet->getCell('G'.$row)->getValue();
                $request->fk_iddepartamento = $sheet->getCell('H'.$row)->getValue();
                $request->fk_idmunicipio = $sheet->getCell('I'.$row)->getValue();
                $request->codigopostal = $sheet->getCell('J'.$row)->getValue();
                $request->telefono1 = $sheet->getCell('K'.$row)->getValue();
                $request->celular = $sheet->getCell('L'.$row)->getValue();
                $request->direccion = $sheet->getCell('M'.$row)->getValue();
                $request->vereda = $sheet->getCell('N'.$row)->getValue();
                $request->barrio = $sheet->getCell('O'.$row)->getValue();
                $request->ciudad = $sheet->getCell('P'.$row)->getValue();
                $request->email = $sheet->getCell('Q'.$row)->getValue();
                $request->observaciones = $sheet->getCell('R'.$row)->getValue();
                $request->tipo_contacto = $sheet->getCell('S'.$row)->getValue();
                $request->estrato = $sheet->getCell('T'.$row)->getValue();
                $error = (object) [];

                if (! $request->tip_iden) {
                    $error->tip_iden = 'El campo Tipo de identificación es obligatorio';
                }
                if (! $request->celular && ! $request->telefono1) {
                    $error->celular = 'Debe indicar un nro celular o de teléfono';
                }
                if (! $request->tipo_contacto) {
                    $error->tipo_contacto = 'El campo Tipo de Contacto es obligatorio';
                }

                if (auth()->user()->empresa()->estado_dian == 1) {
                    if (! $request->fk_idpais) {
                        $error->fk_idpais = 'El campo pais es obligatorio para facturadores electrónicos';
                    }
                    if (! $request->fk_iddepartamento) {
                        $error->fk_iddepartamento = 'El campo departamento es obligatorio para facturadores electrónicos';
                    }
                    if (! $request->fk_idmunicipio) {
                        $error->fk_idmunicipio = 'El campo municipio es obligatorio para facturadores electrónicos';
                    }
                } else {
                    if ($request->fk_idpais != '') {
                        if (DB::table('pais')->where('nombre', $request->fk_idpais)->count() == 0) {
                            $error->fk_idpais = 'El nombre del pais ingresado no se encuentra en nuestra base de datos';
                        }
                    }

                    if ($request->fk_iddepartamento != '') {
                        if (DB::table('departamentos')->where('nombre', $request->fk_iddepartamento)->count() == 0) {
                            $error->fk_iddepartamento = 'El nombre del departamento ingresado no se encuentra en nuestra base de datos';
                        }
                    }

                    if ($request->fk_idmunicipio != '') {
                        if (DB::table('municipios')->where('nombre', $request->fk_idmunicipio)->count() == 0) {
                            $error->fk_idmunicipio = 'El nombre del municipio ingresado no se encuentra en nuestra base de datos';
                        }
                    }
                }

                if (count((array) $error) > 0) {
                    $fila['error'] = 'FILA '.$row;
                    $error = (array) $error;
                    var_dump($error);
                    var_dump($fila);

                    array_unshift($error, $fila);
                    $result = (object) $error;
                    //reenvia los errores
                    return back()->withErrors($result)->withInput();
                }
            }

            $tipo = 2;
            $tipo_identifi = 1;

            for ($row = 4; $row <= $highestRow; $row++) {
                $tipo = 2;
                $tipo_identifi = 1;
                $nombre = $sheet->getCell('A'.$row)->getValue();
                if (empty($nombre)) {
                    break;
                }
                $request = (object) [];
                $request->nombre = $nombre;
                $request->apellido1 = $sheet->getCell('B'.$row)->getValue();
                $request->apellido2 = $sheet->getCell('C'.$row)->getValue();
                $request->tip_iden = $sheet->getCell('D'.$row)->getValue();
                $request->nit = $sheet->getCell('E'.$row)->getValue();
                $request->dv = $sheet->getCell('F'.$row)->getValue();
                $request->fk_idpais = $sheet->getCell('G'.$row)->getValue();
                $request->fk_iddepartamento = $sheet->getCell('H'.$row)->getValue();
                $request->fk_idmunicipio = $sheet->getCell('I'.$row)->getValue();
                $request->codigopostal = $sheet->getCell('J'.$row)->getValue();
                $request->telefono1 = $sheet->getCell('K'.$row)->getValue();
                $request->celular = $sheet->getCell('L'.$row)->getValue();
                $request->direccion = $sheet->getCell('M'.$row)->getValue();
                $request->vereda = $sheet->getCell('N'.$row)->getValue();
                $request->barrio = $sheet->getCell('O'.$row)->getValue();
                $request->ciudad = $sheet->getCell('P'.$row)->getValue();
                $request->email = $sheet->getCell('Q'.$row)->getValue();
                $request->observaciones = $sheet->getCell('R'.$row)->getValue();
                $request->tipo_contacto = $sheet->getCell('S'.$row)->getValue();
                $request->estrato = $sheet->getCell('T'.$row)->getValue();
                if (strtolower($request->tipo_contacto) == 'cliente') {
                    $tipo = 0;
                } elseif (strtolower($request->tipo_contacto) == 'proveedor') {
                    $tipo = 1;
                }
                $request->tipo_contacto = $tipo;

                // TODO: No se está revisando si existen los países, departamentos
                // o municipios, más arriba se está revisando si el `estado_dian`
                // es igual a uno, pero aquí no se tiene eso en cuenta y arroja
                // un error por ello.
                if ($request->fk_idpais != '') {
                    $request->fk_idpais = DB::table('pais')->where('nombre', $request->fk_idpais)->first()->codigo;
                }

                if ($request->fk_iddepartamento != '') {
                    $request->fk_iddepartamento = DB::table('departamentos')->where('nombre', $request->fk_iddepartamento)->first()->id;
                }

                if ($request->fk_idmunicipio != '') {
                    $request->fk_idmunicipio = DB::table('municipios')->where('nombre', $request->fk_idmunicipio)->first()->id;
                }

                $tipo_identifi_arr = TipoIdentificacion::where('identificacion', 'like', '%'.$request->tip_iden.'%')->first();
                if ($tipo_identifi_arr) {
                    $tipo_identifi = $tipo_identifi_arr->id;
                }
                $request->tip_iden = $tipo_identifi;
                $contacto = Contacto::where('nit', $request->nit)->where('empresa', Auth::user()->empresa)->where('status', 1)->first();
                if (! $contacto) {
                    $contacto = new Contacto;
                    $contacto->empresa = Auth::user()->empresa;
                    $contacto->nit = $request->nit;
                    $create = $create + 1;
                } else {
                    $modf = $modf + 1;
                }

                $contacto->nombre = ucwords(mb_strtolower($request->nombre));
                $contacto->apellido1 = ucwords(mb_strtolower($request->apellido1));
                $contacto->apellido2 = ucwords(mb_strtolower($request->apellido2));
                $contacto->tip_iden = $request->tip_iden;
                $contacto->ciudad = ucwords(mb_strtolower($request->ciudad));
                $contacto->direccion = ucwords(mb_strtolower($request->direccion));
                $contacto->vereda = ucwords(mb_strtolower($request->vereda));
                $contacto->barrio = ucwords(mb_strtolower($request->barrio));
                $contacto->email = mb_strtolower($request->email);
                $contacto->telefono1 = $request->telefono1;
                $contacto->celular = $request->celular;
                $contacto->tipo_contacto = $request->tipo_contacto;
                $contacto->observaciones = ucwords(mb_strtolower($request->observaciones));
                $contacto->fk_idpais = $request->fk_idpais;
                $contacto->fk_iddepartamento = $request->fk_iddepartamento;
                $contacto->fk_idmunicipio = $request->fk_idmunicipio;
                $contacto->cod_postal = $request->codigopostal;
                $contacto->estrato = $request->estrato;

                if ($request->dv) {
                    $contacto->dv = $request->dv;
                }
                $contacto->save();
            }

            $mensaje = 'SE HA COMPLETADO EXITOSAMENTE LA CARGA DE DATOS DEL SISTEMA';

            if ($create > 0) {
                $mensaje .= ' CREADOS: '.$create;
            }
            if ($modf > 0) {
                $mensaje .= ' MODIFICADOS: '.$modf;
            }

            DB::commit();

            return redirect('empresa/contactos/clientes')->with('success', $mensaje);
        } catch (Exception $e) {
            DB::rollback();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /*
    * Retorna una archivo xml con las columnas especificas
    * para cargar
    */
    public function ejemplo()
    {
        $objPHPExcel = new PHPExcel();
        $tituloReporte = 'Reporte de Contactos de '.Auth::user()->empresa()->nombre;
        $titulosColumnas = ['Nombres', 'Apellido1', 'Apellido2', 'Tipo de identificacion', 'Identificacion', 'DV', 'Pais', 'Departamento', 'Municipio', 'Codigo postal', 'Telefono', 'Celular', 'Direccion', 'Corregimiento/Vereda', 'Barrio', 'Ciudad', 'Correo Electronico', 'Observaciones', 'Tipo de Contacto', 'Estrato'];
        $letras = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

        $objPHPExcel->getProperties()->setCreator('Sistema') // Nombre del autor
            ->setLastModifiedBy('Sistema') //Ultimo usuario que lo modific���
            ->setTitle('Reporte Excel Contactos') // Titulo
            ->setSubject('Reporte Excel Contactos') //Asunto
            ->setDescription('Reporte de Contactos') //Descripci���n
            ->setKeywords('reporte Contactos') //Etiquetas
            ->setCategory('Reporte excel'); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:N1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $tituloReporte);
        // Titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:N2');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', 'Fecha '.date('d-m-Y')); // Titulo del reporte

        $estilo = [
            'font' => [
                'bold' => true,
                'size' => 12,
                'name' => 'Times New Roman',
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ],
        ];

        $objPHPExcel->getActiveSheet()->getStyle('A1:T3')->applyFromArray($estilo);

        $estilo = [
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => substr(Auth::user()->empresa()->color, 1)],
            ],
            'font' => [
                'bold' => true,
                'size' => 12,
                'name' => 'Times New Roman',
                'color' => [
                    'rgb' => 'FFFFFF',
                ],
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ],
        ];

        $objPHPExcel->getActiveSheet()->getStyle('A3:T3')->applyFromArray($estilo);

        for ($i = 0; $i < count($titulosColumnas); $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        //$contactos = Contacto::all();
        $j = 4;

        /*foreach($contactos as $contacto){
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[0].$j, $contacto->nombre)
            ->setCellValue($letras[1].$j, $contacto->apellido1)
            ->setCellValue($letras[2].$j, $contacto->apellido2)
            ->setCellValue($letras[3].$j, $contacto->tip_iden())
            ->setCellValue($letras[4].$j, $contacto->nit)
            ->setCellValue($letras[5].$j, $contacto->dv)
            ->setCellValue($letras[6].$j, $contacto->pais()->nombre)
            ->setCellValue($letras[7].$j, $contacto->departamento()->nombre)
            ->setCellValue($letras[8].$j, $contacto->municipio()->nombre)
            ->setCellValue($letras[9].$j, $contacto->cod_postal)
            ->setCellValue($letras[10].$j, $contacto->telefono1)
            ->setCellValue($letras[11].$j, $contacto->celular)
            ->setCellValue($letras[12].$j, $contacto->direccion)
            ->setCellValue($letras[13].$j, $contacto->vereda)
            ->setCellValue($letras[14].$j, $contacto->barrio)
            ->setCellValue($letras[15].$j, $contacto->ciudad)
            ->setCellValue($letras[16].$j, $contacto->email)
            ->setCellValue($letras[17].$j, $contacto->observaciones)
            ->setCellValue($letras[18].$j, $contacto->tipo_contacto())
            ->setCellValue($letras[19].$j, $contacto->estrato);
            $j++;
        }*/

        $objPHPExcel->getActiveSheet()->getComment('D3')->setAuthor('Integra Colombia')->getText()->createTextRun('Utilizar los tipos de identificación como se indican en el sistema');
        $objPHPExcel->getActiveSheet()->getComment('S3')->setAuthor('Integra Colombia')->getText()->createTextRun('Escribir Cliente, Proveedor o Cliente/Proveedor.');

        $estilo = ['font' => ['size' => 12, 'name' => 'Times New Roman'],
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ],
        ];

        $objPHPExcel->getActiveSheet()->getStyle('A3:T'.$j)->applyFromArray($estilo);

        for ($i = 'A'; $i <= $letras[20]; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(true);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de Contactos');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A5');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0, 5);
        $objPHPExcel->setActiveSheetIndex(0);
        header('Pragma: no-cache');
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Archivo_Importacion_Contactos.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function contactoModal()
    {
        $identificaciones = TipoIdentificacion::all();
        $vendedores = Vendedor::where('empresa', Auth::user()->empresa)->where('estado', 1)->get();
        $listas = ListaPrecios::where('empresa', Auth::user()->empresa)->where('status', 1)->get();
        $tipos_empresa = TipoEmpresa::where('empresa', Auth::user()->empresa)->get();
        $prefijos = DB::table('prefijos_telefonicos')->get();
        $paises = DB::table('pais')->get();
        $departamentos = DB::table('departamentos')->get();

        return view('contactos.modal.modal')->with(compact('identificaciones', 'paises', 'departamentos', 'tipos_empresa', 'prefijos', 'vendedores', 'listas'));
    }

    public function searchMunicipality(Request $request)
    {
        $municipios = DB::table('municipios')->where('departamento_id', $request->departamento_id)->get();

        return response()->json($municipios);
    }

    public function getDataClient($id)
    {
        $identificaciones = TipoIdentificacion::all();
        $contacto = Contacto::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        $paises = DB::table('pais')->get();
        $departamentos = DB::table('departamentos')->get();

        return view('contactos.modal.updatedatos', compact('contacto', 'paises', 'departamentos', 'identificaciones'));
    }

    public function updatedirection(Request $request)
    {
        $contacto = Contacto::where('id', $request->cliente_id)->where('empresa', Auth::user()->empresa)->first();
        if ($request->cod_postal != null) {
            $contacto->cod_postal = $request->cod_postal;
        }
        if ($request->pais != null) {
            $contacto->fk_idpais = $request->pais;
        }
        if ($request->departamento != null) {
            $contacto->fk_iddepartamento = $request->departamento;
        }
        if ($request->municipio != null) {
            $contacto->fk_idmunicipio = $request->municipio;
        }
        if ($request->direccion != null) {
            $contacto->direccion = $request->direccion;
        }
        if ($request->nit != null) {
            $contacto->nit = $request->nit;
        }
        if ($request->dv != null) {
            $contacto->dv = $request->dv;
        }
        if ($contacto->email == null) {
            $contacto->email = $request->email;
        }
        if ($contacto->tip_iden != 6) { //-- Si es diferente del nit entra
            if ($request->responsable == '') {
                $contacto->tipo_persona = 1; //-- Persona Natural
                $contacto->responsableiva = 2; //-- No responsable de iva
            }
        } else {
            if ($request->tipo_persona != null) {
                $contacto->tipo_persona = $request->tipo_persona;
            }
            if ($request->responsable != null) {
                $contacto->responsableiva = $request->responsable;
            }
        }
        $contacto->save();

        return response()->json($contacto);
    }

    public function modalGuiaEnvio($facturaid, $clienteid)
    {
        $prefijos = DB::table('prefijos_telefonicos')->get();
        $identificaciones = TipoIdentificacion::all();
        $paises = DB::table('pais')->get();
        $departamentos = DB::table('departamentos')->get();
        $transportadoras = DB::table('transportadoras')->get();

        //1 data de guia_envio_factura, 2= data de guia_envio_contacto
        $tipo = 0;

        if (DB::table('guia_envio_factura')->where('factura_id', $facturaid)->count() > 0) {
            $guia_envio = DB::table('guia_envio_factura')->where('factura_id', $facturaid)->first();
            $tipo = 1;
        } else {
            if (DB::table('guia_envio_contacto')->where('contacto_id', $clienteid)->count() > 0) {
                $guia_envio = DB::table('guia_envio_contacto')->where('contacto_id', $clienteid)->first();
                $tipo = 2;
            } else {
                $guia_envio = null;
            }
        }

        return view('contactos.modal.guiaenvio', compact('prefijos', 'identificaciones', 'paises', 'departamentos', 'transportadoras', 'guia_envio', 'facturaid', 'tipo'));
    }

    public function desasociar($id)
    {
        DB::table('usuarios_app')->where('id_cliente', $id)->delete();

        return redirect('empresa/contactos/clientes')->with('success', 'Cliente Desasociado de la APP');
    }

    public function eliminarAdjunto($id, $archivo)
    {
        $contacto = Contacto::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        if ($contacto) {
            switch ($archivo) {
                case 'imgA':
                    $contacto->imgA = null;
                    break;
                case 'imgB':
                    $contacto->imgB = null;
                    break;
                case 'imgC':
                    $contacto->imgC = null;
                    break;
                case 'imgD':
                    $contacto->imgD = null;
                    break;
                default:
                    break;
            }
            $contacto->save();

            return response()->json([
                'success' => true,
                'type' => 'success',
                'title' => 'Archivo Adjunto Eliminado',
                'text' => '',
            ]);
        }

        return response()->json([
            'success' => false,
            'type' => 'error',
            'title' => 'Archivo no eliminado',
            'text' => 'Inténtelo Nuevamente',
        ]);
    }

    public function updateFechaIsp($id, Request $request)
    {

        if (! $request->fecha_isp) {
            return false;
        }

        $contacto = Contacto::where('id', $id)->where('empresa', auth()->user()->empresa)->first();
        $contacto->fecha_isp = Carbon::parse($request->fecha_isp)->format('Y-m-d');
        $contacto->update();

        return response()->json([
            'succes' => true,
            'fecha_isp' => date('d-m-Y', strtotime($contacto->fecha_isp)),
        ]);
    }

    public function editSaldo($contactoId)
    {
        $contacto = Contacto::find($contactoId);
        if ($contacto) {
            return response()->json($contacto);
        }
    }

    public function storeSaldo(Request $request)
    {
        $contacto = Contacto::find($request->contactoId);

        //Programacion para guardar registro de quien hizo el cambio en tabla de historial.
        DB::table('log_saldos')->insert([
            'id_contacto' => $contacto->id,
            'accion' => 'modificó el saldo anterior: '.Funcion::Parsear($contacto->saldo_favor).' al actual: '.$request->saldo_favor,
            'created_by' => Auth::user()->id,
            'fecha' => Carbon::now()->format('Y-m-d'),
            'created_at' => Carbon::now(),
        ]);

        $contacto->saldo_favor = $request->saldo_favor;
        $contacto->save();

        return response()->json($contacto);
    }

    public function historialSaldo($contactoId)
    {
        $historial = DB::table('log_saldos')->join('usuarios as u', 'u.id', 'log_saldos.created_by')
            ->select('log_saldos.*', 'u.nombres as nombre')->where('id_contacto', $contactoId)->get();

        return response()->json($historial);
    }

    //funcion que redirecciona
    public function cambiares($id)
    {
        //##################################################################
        $count = DB::table('crm')->where('cliente', $id)->count();
        if ($count != 0) {
            return redirect('/empresa/crm');
        } else {
            $fec = Carbon::create(date('Y'), date('m'), date('d'))->format('Y-m-d');
            $t = DB::table('factura')
                ->where('cliente', $id)
                ->selectRaw('MAX(pago_oportuno) AS fechamax')->get();
            //sacar la factura el numero una vez obtenido la fecha maxima
            $numfac = DB::table('factura')
                ->where('pago_oportuno', $t[0]->fechamax)
                ->where('cliente', $id)
                ->select('id as idfac')->get();
            //##################################################
            //validar la fecha para que deje guardar en el crm
            if ($fec > $t[0]->fechamax) {
                //si la factura esta vencida entonces verificar el cmr y sino registrar
                $datos = DB::table('contactos')->where('id', $id)->get();
                $Reg = new CRM();
                $Reg->cliente = $datos[0]->id;
                $Reg->estado = 0;
                $Reg->factura = $numfac[0]->idfac;
                $Reg->notificacion = 0;
                $Reg->grupo_corte = 1;
                $Reg->save();

                return redirect('/empresa/crm');
            } else {
                Session::flash('novence', 'Esta factura aun no vence');

                return back();
            }

        }

    }

    //metodo para añadir mas campos al formulario de contacto
    public function indexcampos(){

         $modoLectura = auth()->user()->modo_lectura();
         $this->getAllPermissions(Auth::user()->id);
         $identificaciones = TipoIdentificacion::all();
         $paises = DB::table('pais')->where('codigo', 'CO')->get();
         $departamentos = DB::table('departamentos')->get();
         $oficinas = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Oficina::where('id', Auth::user()->oficina)->get() : Oficina::where('empresa', Auth::user()->empresa)->where('status', 1)->get();

         view()->share(['icon' => '', 'title' => 'Añadir campos a Contacto', 'subseccion' => 'clientes', 'middel' => true]);

         return view('contactos.newcamposcreatep')->with(compact('identificaciones', 'paises', 'departamentos', 'oficinas'));
       }

    //    metodo para ya crear los campos en base de datos
    public function newcampos(Request $request){
        dd($request);
    }

    public function clientes_contratos(Request $request){

        $contratos = Contrato::where('client_id',$request->id)->get();
        return response()->json($contratos);
    }
}
