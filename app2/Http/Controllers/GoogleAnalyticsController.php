<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modulo; use App\Soporte; use Carbon\Carbon; use Mail;  
use Validator; use Illuminate\Validation\Rule;  use Auth; 

class GoogleAnalyticsController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['seccion' => 'Analisis de Pagina Web', 'title' => 'Google Analytics', 'icon' =>'far fa-life-ring']);
  }

  public function index(){
      $this->getAllPermissions(Auth::user()->id);
    return view('google.index');
 	}

  /**
  * Formulario para crear un nuevo banco
  * @return view
  */
  public function create(){  
 
  }

  /**
  * Registrar un nuevo banco
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){
    
  }


  /**
  * Ver un banco
  * @param int $id
  * @return view
  */
  public function show($id){

  }



  /**
  * Modificar los datos del banco
  * @param Request $request
  * @return redirect
  */
  public function update(Request $request, $id){
    
  }

  /**
  * Funcion para eliminar un banco
  * @param int $id
  * @return redirect
  */
  public function destroy($id){      

  }

  


 

}