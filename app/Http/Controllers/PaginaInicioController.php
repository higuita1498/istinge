<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaginaInicioController extends Controller
{
    public function contactanos()
    {
    	return view('PaginaInicio.footer.contactanos.index');
    }
    
         public function modulos()
    {
    	return view('PaginaInicio.footer.sic.modulos');
    }

    public function planes()
    {
    	return view('PaginaInicio.footer.sic.planes');
    }
    
    public function registrarse()
    {
        return view('PaginaInicio.footer.sic.registrarse');
    }
}
