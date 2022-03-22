<?php

namespace App\Http\Controllers;

use App\FormaPago;
use Illuminate\Http\Request;
use Auth;

class FormaPagoController extends Controller
{
    public function index(){
        view()->share(['seccion' => 'categorias', 'title' => 'Formas de Pago', 'icon' =>'fas fa-list-ul']);
        $this->getAllPermissions(Auth::user()->id);
        return view('formapago.index');
    }

    public function store(Request $request){
        return "ok";
    }
}
