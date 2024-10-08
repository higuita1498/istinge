<?php

namespace App\Http\Controllers;

use App\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OltController extends Controller
{
    public function unConfiguredOnus(){

        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Olt - Onu Unconfigured', 'icon' => '', 'seccion'=>'']);
        $empresa = Empresa::Find(Auth::user()->empresa);
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/onu/unconfigured_onus',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'X-Token: ' . $empresa->smartOLT
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response,true);

        if(isset($response['response'])){
            $onus = $response['response'];
        }
        else{
            $onus = [];
        }

        return view('olt.unconfigured',compact('onus'));
    }
}
