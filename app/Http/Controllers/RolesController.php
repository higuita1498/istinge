<?php

namespace App\Http\Controllers;

use App\Roles;
use Illuminate\Http\Request;
use Auth;

class RolesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        view()->share(['seccion' => 'Tipos Usuarios', 'title' => 'Tipos Usuarios', 'icon' =>'fas fa-cogs']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Tipos Usuarios', 'icon' =>'']);
        $roles = Roles::where('id_empresa', Auth::user()->empresa)->whereNotIn('id',[1,2])->get();

        return view('configuracion.tipousuario.index')->with(compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $this->getAllPermissions(Auth::user()->id);
        return view('configuracion.tipousuario.create');

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
        $this->getAllPermissions(Auth::user()->id);
        $request->validate([
          'rol' => 'required'
        ]);
        $nroCorrelativo = Roles::where('id_empresa', Auth::user()->empresa)->get()->last();



        if(!$nroCorrelativo){
            $nro = 1;
        }else{
            $nro = $nroCorrelativo->nro+1;
        }

        $rol = new Roles();
        $rol->nro = $nro;
        $rol->rol = $request->rol;
        $rol->id_empresa = Auth::user()->empresa;
        $rol->save();

    return redirect('empresa/configuracion/roles')->with('success', 'Se ha creado el tipo de usuario satisfactoriamente.');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->getAllPermissions(Auth::user()->id);
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
        $this->getAllPermissions(Auth::user()->id);
        $rol = Roles::find($id);
        return view('configuracion.tipousuario.edit')->with(compact('rol'));
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
        $request->validate([
          'rol' => 'required'
        ]);

         $rol = Roles::find($id);

         if($rol){
            $rol->rol = $request->rol;
            $rol->save();
         }

     return redirect('empresa/configuracion/roles')->with('success', 'Se ha modificado el tipo de usuario satisfactoriamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
    }

    public function eliminar(Request $request)
    {
        $rol = Roles::where('id_empresa',Auth::user()->empresa)->where('id', $request->idRol)->first();

        if($rol){
            $rol->delete();
            $arrayPost['status'] = 'ok';
            echo json_encode($arrayPost);
            exit;
        }
       //return redirect('empresa/configuracion/roles')->with('success','Se ha eliminado el tipo de usuario');
    }
}
