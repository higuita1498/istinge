<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Auth;
use App\Contrato;
use App\Servidor;
use App\Mikrotik;
use App\routeros_api;

class GraficosController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['seccion' => 'contratos', 'subseccion' => 'listado', 'title' => 'Contratos de Servicio', 'icon' =>'fas fa-file-contract']);
    }

    public function index($id = null){
        $this->getAllPermissions(Auth::user()->id);
        $contrato = Contrato::find($id);
        $url = '';
        if ($contrato) {
            $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();
            
            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;
            //$API->debug = true;
            
            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                $API->write("/tool/graph/interface/print",true);
                $ARRAYS = $API->read();
                if(count($ARRAYS)>0){
                    view()->share(['icon'=>'', 'title' => 'Gráfica de Consumo | Contrato: '.$contrato->nro]);
                    $url = $mikrotik->ip.':'.$mikrotik->puerto_web.'/graphs/queue/'.str_replace(' ','%20',$contrato->servicio);
                    return view('contratos.grafica-consumo')->with(compact('contrato', 'url'));
                }
                return redirect('empresa/contratos/'.$contrato->id)->with('danger', 'EL SERVIDOR NO TIENE HABILITADA LA VISUALIZACIÓN DE LOS GRÁFICOS');
            }
        }
        view()->share(['icon'=>'', 'title' => 'Gráfica de Consumo']);

        return view('graficas.consumo-contrato', compact('contrato', 'url'));
    }


    public function data(){
        $IpRouter = "192.168.88.1";
        $User="tu_usuario";
        $Pass="tu_contrasena";
        $Port=8728;

        $Interface = request()->interface; //"<pppoe-nombreusuario>";
        $Type = request()->type_interface; //   0=interfaces     1=queues
        $ConnectedFlag = false;
            $API = new routeros_api();
            $API->debug = false;
            if ($API->connect($IpRouter , $User , $Pass, $Port)) {
                $rows = array(); $rows2 = array();	

                if ($Type==0) {  // Interfaces
                    $API->write("/interface/monitor-traffic",false);
                    $API->write("=interface=".$Interface,false);  
                    $API->write("=once=",true);
                    $READ = $API->read(false);
                    $ARRAY = $API->parse_response($READ);
                        if(count($ARRAY)>0){  
                            $rx = ($ARRAY[0]["rx-bits-per-second"]);
                            $tx = ($ARRAY[0]["tx-bits-per-second"]);
                            $rows['name'] = 'Tx';
                            $rows['data'][] = $tx;
                            $rows2['name'] = 'Rx';
                            $rows2['data'][] = $rx;
                        }else{  
                            echo $ARRAY['!trap'][0]['message'];	 
                        } 
                    }else if($Type==1){ //  Queues
                    $API->write("/queue/simple/print",false);
                    $API->write("=stats",false);
                    $API->write("?name=".$Interface,true);  
                    $READ = $API->read(false);
                    $ARRAY = $API->parse_response($READ);
                    //print_r($ARRAY[0]);
                        if(count($ARRAY)>0){  
                            $rx = explode("/",$ARRAY[0]["rate"])[0];
                            $tx = explode("/",$ARRAY[0]["rate"])[1];
                            $rows['name'] = 'Tx';
                            $rows['data'][] = $tx;
                            $rows2['name'] = 'Rx';
                            $rows2['data'][] = $rx;
                        }else{  
                            echo $ARRAY['!trap'][0]['message'];	 
                        } 
                    }
                    $ConnectedFlag = true;
            }else{
                echo "<font color='#ff0000'>La conexion ha fallado. Verifique si el Api esta activo.</font>";
            }

            if ($ConnectedFlag) {
                $result = array();
                array_push($result,$rows);
                array_push($result,$rows2);
                print json_encode($result, JSON_NUMERIC_CHECK);
            }
            $API->disconnect();
    }

}
