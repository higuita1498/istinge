<?php

namespace App\Http\Controllers;

use App\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OltController extends Controller
{
    public function unConfiguredOnus_view(Request $request){

        $this->getAllPermissions(Auth::user()->id);

        view()->share(['title' => 'Olt - Onu Unconfigured', 'icon' => '', 'seccion'=>'']);

        // ****** Get olts ****** //
        $olts = $this->getOlts();
        if(!isset($request->olt)){
            $olt_default = null;
        }else{
            $olt_default = $request->olt;
        }
        if(isset($olts['response'])){
            $olts = $olts['response'];
            if($olt_default == null){$olt_default = $olts[0]['id'];}
        }else{
            $olts = [];
        }

        if($olt_default != null){
            $vlan = $this->get_VLAN($olt_default);
            if(isset($vlan['response'])){
                $vlan = $vlan['response'];
                // Usamos usort para ordenar el array según la clave 'vlan'
                usort($vlan, function ($a, $b) {
                    return (int)$a['vlan'] - (int)$b['vlan'];
                });
            }else{
                $vlan = [];
            }
        }else{
            $vlan = [];
        }
        // ****** Get olts ****** //

        // ****** Get onus by olt ****** //
        $response = $this->unconfiguredOnusOlt($olt_default);
        if(isset($response['response'])){
            $onus = $response['response'];
        }
        else{
            $onus = [];
        }
        // ****** Get onus by olt ****** //

        return view('olt.unconfigured',compact('onus','olts','olt_default'));
    }

    public static function unconfiguredOnus(){
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

        return $response;
    }

    public static function unconfiguredOnusOlt($olt){
        $empresa = Empresa::Find(Auth::user()->empresa);
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/onu/unconfigured_onus_for_olt/'.$olt,
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

        return $response;
    }

    public static function onuTypes(){
        $empresa = Empresa::Find(Auth::user()->empresa);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/system/get_onu_types',
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

        $response = json_decode(curl_exec($curl),true);
        curl_close($curl);

        return $response;
    }

    public static function getOlts(){
        $empresa = Empresa::Find(Auth::user()->empresa);

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/system/get_olts',
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

        $response = json_decode(curl_exec($curl),true);
        curl_close($curl);

        return $response;

    }

    public static function get_VLAN($olt){
        $curl = curl_init();
        $empresa = Empresa::Find(Auth::user()->empresa);

        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/olt/get_vlans/'.$olt,
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

        $response = json_decode(curl_exec($curl),true);

        curl_close($curl);
        return $response;

    }

    public function getZones(){
        $curl = curl_init();
        $empresa = Empresa::Find(Auth::user()->empresa);

        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/system/get_zones',
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

        $response = json_decode(curl_exec($curl),true);

        curl_close($curl);
        return $response;
    }

    public function ODBlist($zone){

        $curl = curl_init();
        $empresa = Empresa::Find(Auth::user()->empresa);

        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/system/get_odbs/'.$zone,
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


        $response = json_decode(curl_exec($curl),true);
        curl_close($curl);
        return $response;
    }

    public function getSpeedProfiles(){
        $curl = curl_init();
        $empresa = Empresa::Find(Auth::user()->empresa);

        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/system/get_speed_profiles',
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

        $response = json_decode(curl_exec($curl),true);

        curl_close($curl);
        return $response;
    }


    public function formAuthorizeOnu(Request $request){

        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Olt - Formulario Authorizacion Onu', 'icon' => '', 'seccion'=>'']);

        $onu_types = $this->onuTypes();

        if(isset($onu_types['response'])){
            $onu_types = $onu_types['response'];
        }else{
            $onu_types = [];
        }

        $olts = $this->getOlts();
        $olt_default = $request->olt_id;
        if(isset($olts['response'])){
            $olts = $olts['response'];
        }else{
            $olts = [];
        }

        if($olt_default != null){
            $vlan = $this->get_VLAN($olt_default);
            if(isset($vlan['response'])){
                $vlan = $vlan['response'];

                // Usamos usort para ordenar el array según la clave 'vlan'
                usort($vlan, function ($a, $b) {
                    return (int)$a['vlan'] - (int)$b['vlan'];
                });
            }else{
                $vlan = [];
            }
        }else{
            $vlan = [];
        }

        $zones = $this->getZones();
        $default_zone = 0;

        if(isset($zones['response'])){
            $zones = $zones['response'];
            $default_zone = $zones[0]['id'];
        }else{
            $zones = [];
        }

        if($default_zone != 0){
            $odbList = $this->ODBlist($default_zone);
            if(isset($odbList['response'])){
                $odbList = $odbList['response'];
            }else{
                $odbList = [];
            }
        }else{
            $odbList = [];
        }

        $speedProfiles = $this->getSpeedProfiles();

        if(isset($speedProfiles['response'])){
            $speedProfiles = $speedProfiles['response'];
        }else{
            $speedProfiles = [];
        }

        return view('olt.form-authorized-onu',compact('request','onu_types','olts','vlan','zones',
        'olt_default','default_zone','odbList','speedProfiles'));
    }

    public function authorizedOnus(Request $request){

        $empresa = Empresa::Find(Auth::user()->empresa);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/onu/authorize_onu',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('olt_id' => $request->olt_id,
        'pon_type' => $request->pon_type,
        'board' => $request->board,
        'port' => $request->port,
        'sn' => $request->sn,
        'onu_type' => $request->onu_type,
        'custom_profile' => '',
        'onu_mode' => $request->onu_mode,
        'cvlan' => '',
        'svlan' => '',
        'tag_transform_mode' => '',
        'use_other_all_tls_vlan' => '',
        'vlan' => $request->user_vlan_id,
        'zone' => $request->zone,
        'odb' => $request->odb_splitter,
        'name' => $request->name,
        'address_or_comment' => $request->address_comment,
        'onu_external_id' => $request->sn,
        'upload_speed_profile_name' => $request->upload_speed,
        'download_speed_profile_name' => $request->download_speed
        ),
        CURLOPT_HTTPHEADER => array(
            'X-Token: ' . $empresa->smartOLT
        ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response,true);

        curl_close($curl);

        if($response['status'] == 200){
            $mensaje = "Onu autorizada con exito";
            return redirect('Olt/unconfigured-onus')->with('success', $mensaje);
        }else{
            $mensaje = "Onu no ha sido autorizada";
            return redirect('Olt/unconfigured-onus')->with('error', $mensaje);
        }
    }
}
