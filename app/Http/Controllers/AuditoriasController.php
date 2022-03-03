<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Auditoria;
use App\User;
use App\Funcion;
use Validator;
use Auth;
use DB;
use Carbon\Carbon;
use Session;
use Illuminate\Support\Facades\Storage;

class AuditoriasController extends Controller
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
    view()->share(['seccion' => 'configuracion', 'title' => 'Auditorías Contratos', 'icon' =>'fas fa-eye']);
  }

  public function index(){
    $this->getAllPermissions(Auth::user()->id);
    $auditorias = Auditoria::where('empresa', Auth::user()->empresa)->get();

    return view('auditorias.index')->with(compact('auditorias'));
  }
}
