<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use App\CentroCostos;
use App\CategoriaGeneral;
use App\Categoria;

class ContabilidadController extends Controller
{
    public function __construct()
    {
        $this->middleware('nomina');
        $this->middleware('payrollReadingMode')->only('store_ccosto', 'edit_ccosto', 'update_ccosto', 'destroy_ccosto', 'edit_ctacontable', 'update_ctacontable');
    }

    public function index(Request $request)
    {
        $usuario = auth()->user();
        $this->getAllPermissions($usuario->id);

        view()->share(['seccion' => 'nomina', 'title' => 'Contabilidad', 'icon' => 'fas fa-calculator']);

        $ccostos = CentroCostos::where('fk_idempresa',1)->get();

        $cats_general = CategoriaGeneral::all();
        $modoLectura = (object) $usuario->modoLecturaNomina();


        return view('nomina.contabilidad', compact('ccostos', 'cats_general', 'modoLectura'));
    }

    public function store_ccosto(Request $request)
    {
        if (!$request->nombre) {
            $arrayPost['status']  = 'error';
            $arrayPost['mensaje'] = 'El campo nombre es obligatorio';
            echo json_encode($arrayPost);
            exit;
        }
        $ccostos = CentroCostos::where('nombre', $request->nombre)->first();
        if ($ccostos) {
            $arrayPost['status']  = 'error';
            $arrayPost['mensaje'] = 'Ya hay un Centro de Costo almacenado con ese mismo nombre';
            return json_encode($arrayPost);
            exit;
        } else {
            $ccosto = new CentroCostos();
            $ccosto->nombre = $request->nombre;
            $ccosto->prefijo_contable = $request->prefijo;
            $ccosto->codigo_contable = $request->codigo;
            $ccosto->fk_idempresa = auth()->user()->empresa;
            $ccosto->save();
            if ($ccosto) {
                $arrayPost['status']  = 'OK';
                $arrayPost['id'] = $ccosto->id;
                $arrayPost['nombre'] = $ccosto->nombre;
                $arrayPost['prefijo'] = $ccosto->prefijo_contable;
                $arrayPost['codigo'] = $ccosto->codigo_contable;
                return json_encode($arrayPost);
                exit;
            }
        }
    }

    public function edit_ccosto($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $ccosto = CentroCostos::find($id);

        if ($ccosto) {
            $arrayPost['status']  = 'OK';
            $arrayPost['id'] = $ccosto->id;
            $arrayPost['nombre'] = $ccosto->nombre;
            $arrayPost['prefijo'] = $ccosto->prefijo_contable;
            $arrayPost['codigo'] = $ccosto->codigo_contable;
            return json_encode($arrayPost);
        } else {
            $arrayPost['status']  = 'error';
            $arrayPost['mensaje'] = 'No existe un registro con ese ID';
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function update_ccosto(Request $request)
    {
        if (!$request->nombre) {
            $arrayPost['status']  = 'error';
            $arrayPost['mensaje'] = 'El campo nombre es obligatorio';
            echo json_encode($arrayPost);
            exit;
        }

        $ccosto = CentroCostos::find($request->id);
        $ccosto->nombre = $request->nombre;
        $ccosto->prefijo_contable = $request->prefijo;
        $ccosto->codigo_contable = $request->codigo;
        $ccosto->save();
        if ($ccosto) {
            $arrayPost['status']  = 'OK';
            $arrayPost['id'] = $ccosto->id;
            $arrayPost['nombre'] = $ccosto->nombre;
            $arrayPost['prefijo'] = $ccosto->prefijo_contable;
            $arrayPost['codigo'] = $ccosto->codigo_contable;
            return json_encode($arrayPost);
            exit;
        } else {
            $arrayPost['status']  = 'error';
            $arrayPost['mensaje'] = 'No existe un registro con ese ID';
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function destroy_ccosto($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $ccosto = CentroCostos::find($id);
        if ($ccosto) {

            $uso = $ccosto->uso();

            if (!$uso) {
                $ccosto->delete();
                $arrayPost['status']  = 'OK';
                return json_encode($arrayPost);
                exit;
            } else {
                $arrayPost['status']  = 'error';
                $arrayPost['mensaje'] = 'Imposible borrar, primero retire este centro de costo de las personas asociadas';
                echo json_encode($arrayPost);
                exit;
            }
        } else {
            $arrayPost['status']  = 'error';
            $arrayPost['mensaje'] = 'No existe un registro con ese ID';
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function edit_ctacontable($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $ctacontable = Categoria::find($id);

        if ($ctacontable) {
            $arrayPost['status'] = 'OK';
            $arrayPost['id']     = $ctacontable->id;
            $arrayPost['nombre'] = $ctacontable->nombre;
            $arrayPost['codigo'] = $ctacontable->codigo;
            return json_encode($arrayPost);
        } else {
            $arrayPost['status']  = 'error';
            $arrayPost['mensaje'] = 'No existe un registro con ese ID';
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function update_ctacontable(Request $request)
    {
        if (!$request->codigo) {
            $arrayPost['status']  = 'error';
            $arrayPost['mensaje'] = 'El campo codigo es obligatorio';
            echo json_encode($arrayPost);
            exit;
        }

        $ctacontable = Categoria::find($request->id);
        $ctacontable->codigo = $request->codigo;
        $ctacontable->save();
        if ($ctacontable) {
            $arrayPost['status']  = 'OK';
            $arrayPost['id'] = $ctacontable->id;
            $arrayPost['nombre'] = $ctacontable->nombre;
            $arrayPost['codigo'] = $ctacontable->codigo;
            return json_encode($arrayPost);
            exit;
        } else {
            $arrayPost['status']  = 'error';
            $arrayPost['mensaje'] = 'No existe un registro con ese ID';
            echo json_encode($arrayPost);
            exit;
        }
    }
}
