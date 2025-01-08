<?php

namespace App\Http\Controllers;

use App\Empresa;
use Carbon\Carbon;
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

    public static function moveOnuSpecified($olt_id,$board,$port,$sn){
        $empresa = Empresa::Find(Auth::user()->empresa);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/onu/move/'.$sn,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
        'olt_id' => $olt_id,
        'board' => $board,
        'port' => $port,
        ),
        CURLOPT_HTTPHEADER => array(
            'X-Token: ' . $empresa->smartOLT
        ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response,true);

        curl_close($curl);

        return $response;
    }

    public function moveOnu(Request $request){

        $response = $this->moveOnuSpecified($request->olt_id,$request->board,$request->port,$request->sn);

        if(isset($response['response']) && $response['status'] == true){
            return response()->json([
                'status' => 200
            ]);
        }else{
            return response()->json([
                'status' => 400
            ]);
        }

    }

    public function resyncOnuConfig($sn){
        $empresa = Empresa::Find(Auth::user()->empresa);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/onu/resync_config/'.$sn,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(),
        CURLOPT_HTTPHEADER => array(
            'X-Token: ' . $empresa->smartOLT
        ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response,true);

        curl_close($curl);

        return $response;
    }

    public function getFullOnuSignal($sn){
        $empresa = Empresa::Find(Auth::user()->empresa);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/onu/get_onu_full_status_info/'.$sn,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS => array(),
        CURLOPT_HTTPHEADER => array(
            'X-Token: ' . $empresa->smartOLT
        ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response,true);

        curl_close($curl);

        return $response;
    }

    public function rebootOnu($sn){
        $empresa = Empresa::Find(Auth::user()->empresa);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/onu/reboot/'.$sn,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(),
        CURLOPT_HTTPHEADER => array(
            'X-Token: ' . $empresa->smartOLT
        ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response,true);

        curl_close($curl);

        return $response;
    }

    public function restoreFactory($sn){
        $empresa = Empresa::Find(Auth::user()->empresa);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/onu/restore_factory_defaults/'.$sn,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(),
        CURLOPT_HTTPHEADER => array(
            'X-Token: ' . $empresa->smartOLT
        ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response,true);

        curl_close($curl);

        return $response;
    }

    public function resyncConfig(Request $request){
        $response = $this->resyncOnuConfig($request->sn);

        if(isset($response['response']) && $response['status'] == true){
            return response()->json([
                'status' => 200
            ]);
        }else{
            return response()->json([
                'status' => 400
            ]);
        }

    }

    public function disableOnu($sn){
        $empresa = Empresa::Find(Auth::user()->empresa);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/onu/disable/'.$sn,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(),
        CURLOPT_HTTPHEADER => array(
            'X-Token: ' . $empresa->smartOLT
        ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response,true);

        curl_close($curl);

        return $response;
    }

    public function deleteOnu($sn){
        $empresa = Empresa::Find(Auth::user()->empresa);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/onu/delete/'.$sn,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(),
        CURLOPT_HTTPHEADER => array(
            'X-Token: ' . $empresa->smartOLT
        ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response,true);

        curl_close($curl);

        return $response;
    }

    public function rebootOnuResponse(Request $request){
        $response = $this->rebootOnu($request->sn);

        if(isset($response['response']) && $response['status'] == true){
            return response()->json([
                'status' => 200
            ]);
        }else{
            return response()->json([
                'status' => 400
            ]);
        }
    }

    public function restoreFactoryResponse(Request $request){
        $response = $this->restoreFactory($request->sn);

        if(isset($response['response']) && $response['status'] == true){
            return response()->json([
                'status' => 200
            ]);
        }else{
            return response()->json([
                'status' => 400
            ]);
        }
    }

    public function restoreDefaultResponse(Request $request){
        $response = $this->restoreDefault($request->sn);

        if(isset($response['response']) && $response['status'] == true){
            return response()->json([
                'status' => 200
            ]);
        }else{
            return response()->json([
                'status' => 400
            ]);
        }
    }

    public function disableOnuResponse(Request $request){
        $response = $this->disableOnu($request->sn);

        if(isset($response['response']) && $response['status'] == true){
            return response()->json([
                'status' => 200
            ]);
        }else{
            return response()->json([
                'status' => 400
            ]);
        }
    }

    public function deleteOnuResponse(Request $request){
        $response = $this->deleteOnu($request->sn);

        if(isset($response['response']) && $response['status'] == true){
            return response()->json([
                'status' => 200
            ]);
        }else{
            return response()->json([
                'status' => 400
            ]);
        }
    }

    public function onu_type_image($onu_type_id){
        $empresa = Empresa::Find(Auth::user()->empresa);

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/system/get_onu_type_image/'.$onu_type_id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS => array(),
        CURLOPT_HTTPHEADER => array(
            'X-Token: ' . $empresa->smartOLT
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getOnuDetailsBySn($sn){
        $empresa = Empresa::Find(Auth::user()->empresa);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/onu/get_onus_details_by_sn/'.$sn,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS => array(),
        CURLOPT_HTTPHEADER => array(
            'X-Token: ' . $empresa->smartOLT
        ),
        ));

        $response = curl_exec($curl);
        $response = json_decode($response,true);

        curl_close($curl);

        return $response;
    }

    public function onu_traffic_image($sn){
        $empresa = Empresa::Find(Auth::user()->empresa);
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/onu/get_onu_traffic_graph/'.$sn.'/daily',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS => array(),
        CURLOPT_HTTPHEADER => array(
            'X-Token: ' . $empresa->smartOLT
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function onu_signal_image($sn){
        $empresa = Empresa::Find(Auth::user()->empresa);

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $empresa->adminOLT.'/api/onu/get_onu_signal_graph/'.$sn.'/daily',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS => array(),
        CURLOPT_HTTPHEADER => array(
            'X-Token: ' . $empresa->smartOLT
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function viewOnu(Request $request){

        $sn = $request->sn;
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => $sn , 'icon' => '', 'seccion'=>'']);

        if($sn){
            $details = $this->getOnuDetailsBySn($sn);
            if($details['status'] != true){
                return redirect('Olt/unconfigured-onus')->with('error', 'Error al mirar la informacion de la onu');
            }
        }else{
            return redirect('Olt/unconfigured-onus')->with('error', 'No hay una sn seleccionada');
        }

        $details = $details['onus'][0];

        $image_onu_type = null;
        if(isset($details['onu_type_id'])){
            $image_onu_type = $this->onu_type_image($details['onu_type_id']);
            $imagenBase64 = base64_encode($image_onu_type);
            $image_onu_type = 'data:image/png;base64,' . $imagenBase64;

        }

        $onu_traffic_graph = null;
        if(isset($details['onu_type_id'])){
            $onu_traffic_graph = $this->onu_traffic_image($details['sn']);
            $imagenBase64 = base64_encode($onu_traffic_graph);
            $onu_traffic_graph = 'data:image/png;base64,' . $imagenBase64;

        }
        $onu_signal_graph = null;
        if(isset($details['onu_type_id'])){
            $onu_signal_graph = $this->onu_signal_image($details['sn']);
            $imagenBase64 = base64_encode($onu_signal_graph);
            $onu_signal_graph = 'data:image/png;base64,' . $imagenBase64;

        }

        $signalOnu = $this->getFullOnuSignal($sn);
        $signalOnu = $signalOnu['full_status_json'];

        if(isset($signalOnu['ONU details']['Online Duration'])){
            // Extrae solo las horas
            preg_match('/(\d+)h/', $signalOnu['ONU details']['Online Duration'], $matches);

            // Extrae solo las horas
            preg_match('/(\d+)h/', $signalOnu['ONU details']['Online Duration'], $matches);

            if (!empty($matches[1])) {
                $hours = (int) $matches[1]; // Convierte a entero
                $days = floor($hours / 24); // Calcula los días completos
                $remainingHours = $hours % 24; // Horas restantes después de contar los días

                $diferenciaDias= $days . ' dias ' . $remainingHours . ' horas';
            } else {
                echo "Formato de duración no reconocido.";
            }

            if (!empty($matches[1])) {
                $hours = (int) $matches[1]; // Convierte a entero
                echo "Horas: " . $hours;
            } else {
                $diferenciaDias = null;
            }
        }else{
            $diferenciaDias = null;
        }

        $ethernetPorts = [
            ['name' => 'eth_0/1', 'adminState' => 'Enabled', 'mode' => 'LAN', 'dhcp' => 'No control'],
            ['name' => 'eth_0/2', 'adminState' => 'Enabled', 'mode' => 'LAN', 'dhcp' => 'No control'],
            ['name' => 'eth_0/3', 'adminState' => 'Enabled', 'mode' => 'LAN', 'dhcp' => 'No control'],
            ['name' => 'eth_0/4', 'adminState' => 'Enabled', 'mode' => 'LAN', 'dhcp' => 'No control'],
        ];

        return view('olt.view-onu',compact('details','image_onu_type','ethernetPorts','onu_traffic_graph',
        'onu_signal_graph','signalOnu','diferenciaDias'));
    }
}
