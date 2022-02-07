<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Empresa; use App\Categoria; use Carbon\Carbon; 
use Validator; use Illuminate\Validation\Rule;  use Auth; 
use Session;

class CategoriasController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['seccion' => 'categorias', 'title' => 'Categorías', 'icon' =>'fas fa-list-ul']);
  }

  public function index(){
      $this->getAllPermissions(Auth::user()->id);
    $defecto = Categoria::where('empresa',Auth::user()->empresa)->where('id', Auth::user()->empresa()->categoria_default)->first();
    $default='';
    if ($defecto) {
      $default.='Categoria por Defecto: '.$defecto->nombre;
    }
 		$categorias = Categoria::where('empresa',Auth::user()->empresa)->whereNull('asociado')->orderBy('codigo','ASC')->paginate(10);
    view()->share(['title' => 'Categorías ']);

 		return view('categorias.index')->with(compact('categorias', 'default'));   		
 	}

  /**
  * Formulario para crear un nuevo banco
  * @return view
  */
  public function create($id){
      $this->getAllPermissions(Auth::user()->id);
    $categoria = Categoria::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    return view('categorias.create')->with(compact('categoria')); 
  }

  /**
  * Registrar un nuevo banco
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){
      
      //Tomamos el tiempo en el que se crea el registro
    Session::put('posttimer', Categoria::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
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
    return redirect('empresa/categorias')->with('success', $mensaje);
    }
      
    $request->validate([
      'nombre' => 'required|max:200',
      'asociado' => 'required|numeric',
    ]);

    $nro = Categoria::where('empresa',Auth::user()->empresa)->get()->last()->nro;
    
    $categoria = new Categoria;
    $categoria->empresa=Auth::user()->empresa;
    $categoria->nro = $nro+1;
    $categoria->asociado=$request->asociado;
    $categoria->nombre=$request->nombre;
    $categoria->codigo=$request->codigo;
    $categoria->descripcion=$request->descripcion;
    $categoria->save();
    $mensaje='Se ha creado satisfactoriamente la categoría';
    return redirect('empresa/categorias')->with('success', $mensaje);
  }

  /**
  * Formulario para modificar los datos de un banco
  * @param int $id
  * @return view
  */
  public function edit($id){
      $this->getAllPermissions(Auth::user()->id);
    $categoria = Categoria::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($categoria) {        
      return view('categorias.edit')->with(compact('categoria'));
    }
    return 'No existe un registro con ese id';
  }

  /**
  * Modificar los datos del banco
  * @param Request $request
  * @return redirect
  */
  public function update(Request $request, $id){
    $categoria = Categoria::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($categoria) {
      $request->validate([
      'nombre' => 'required|max:200',
      ]);
      $categoria->nombre=$request->nombre;
      $categoria->codigo=$request->codigo;
      $categoria->descripcion=$request->descripcion;
      $categoria->save();
      $mensaje='Se ha modificado satisfactoriamente la categoría';
      return redirect('empresa/categorias')->with('success', $mensaje);

    }
    return redirect('master/categorias')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Funcion para eliminar un banco
  * @param int $id
  * @return redirect
  */
  public function destroy($id){      
    $categoria = Categoria::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
   
    if ($categoria) {        
      if ($categoria->usado() == 0 && $categoria->catUsadaEnPago()== 0) {
             $categoria->delete();
      }else{
          return redirect('empresa/categorias')->with('info', 'Esta Categoria esta Siendo Usada en Factura!');
      }
    }    
    return redirect('empresa/categorias')->with('success', 'Se ha eliminado la categoría');
  }

  /**
  * Ver un banco
  * @param int $id
  * @return view
  */
  public function show($id){
      $this->getAllPermissions(Auth::user()->id);

    $banco = Banco::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($banco) {        
      view()->share(['title' => 'Ver Cuenta: '.$banco->nombre]);
      return view('bancos.show')->with(compact('banco'));
    }
    return redirect('master/empresas')->with('success', 'No existe un registro con ese id');
  }


  /**
  * Funcion para cambiar el estatus de la categoría
  * @param int $id
  * @return redirect
  */
  public function act_desc($id){
    $categoria = Categoria::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($categoria) {        
        if ($categoria->estatus==1) {
          $mensaje='Se ha desactivado la categoría';
          $categoria->estatus=0;
          $categoria->save();
        }
        else{
          $mensaje='Se ha activado la categoría';
          $categoria->estatus=1;
          $categoria->save();
        }
      return redirect('empresa/categorias')->with('success', $mensaje);
    }
    return redirect('empresa/categorias')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Funcion para cambiar el estatus de la categoría
  * @param int $id
  * @return redirect
  */
  public function default($id){
    $categoria = Categoria::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($categoria) {        
      $empresa=  Auth::user()->empresa();
      $empresa->categoria_default=$categoria->id;
      $empresa->save();

        $mensaje='Se tomado la categoría "'.$categoria->nombre.'" por defecto';
      return redirect('empresa/categorias')->with('success', $mensaje);
    }
    return redirect('empresa/categorias')->with('success', 'No existe un registro con ese id');
  }

  

  public function quitar(){
    $empresa=  Auth::user()->empresa();
    $empresa->categoria_default=null;
    $empresa->save();
    return redirect('empresa/categorias');
  }

}