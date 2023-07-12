<?php

namespace App\Http\Controllers;

use App\Banco;
use App\Contacto;
use App\Empresa;
use App\Model\Gastos\FacturaProveedores;
use App\Model\Ingresos\Factura;
use App\Model\Inventario\Bodega;
use App\Model\Inventario\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestController extends Controller
{
    public function index()
    {
       return view('test.test');
    }


    public function getAllData()
    {
        $banks = Banco::where('empresa',Auth::user()->empresa)->get();
        $contact = Contacto::where('empresa',Auth::user()->empresa)->get();
        $billIn = Factura::where('empresa',Auth::user()->empresa)->get();
        $billOut = FacturaProveedores::where('empresa',Auth::user()->empresa)->get();
        $wareHouse = Bodega::where('empresa',Auth::user()->empresa)->get();
        $inventory = Inventario::where('empresa',Auth::user()->empresa)->get();

        $data = array(
            'bank'          => $banks,
            'contact'       => $contact,
            'billIn'        => $billIn,
            'billOut'       => $billOut,
            'warehouse'     => $wareHouse,
            'inventory'     => $inventory
        );

        return json_encode($data);

    }

}
