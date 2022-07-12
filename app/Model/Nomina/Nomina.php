<?php

namespace App\Model\Nomina;

use App\Empresa;
use Illuminate\Database\Eloquent\Model;
use App\Model\Nomina\NominaPeriodos;
use App\Model\Nomina\Persona;
use App\NumeracionFactura;
use Carbon\Carbon;
use App\Traits\Funciones;
use GuzzleHttp\Client;
use Auth;

class Nomina extends Model
{
    use Funciones;

    // ESTADOS DE UNA NOMINA 1= emitida, 2=no emitida, 3= anulada emitida, 4= ajuste sin emitir,  5= ajuste emitido, 6=eliminada'
    const EMITIDA = 1;
    const NO_EMITIDA = 2;
    const ANULADA_EMITIDA = 3;
    const AJUSTE_SIN_EMITIR = 4;
    const AJUSTE_EMITIDO = 5;
    const ELIMINADA = 6;


    protected $table = "ne_nomina";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nro',
        'periodo',
        'nota',
        'estado_nomina',
        'fk_idempresa',
        'fk_idpersona',
        'emitida',
        'tipo',
        'isPagado',
        'created_at',
        'updated_at',
    ];


    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'fk_idempresa');
    }


    public function persona()
    {
        return $this->belongsTo(Persona::class, 'fk_idpersona');
    }

    public function nominaperiodos()
    {
        return $this->hasMany(NominaPeriodos::class, 'fk_idnomina', 'id');
    }

    public function numeracionfactura()
    {
        return $this->belongsTo(NumeracionFactura::class, 'fk_idnumeracion')->where('nomina',1);
    }

    public function periodo()
    {
        $date = Carbon::create($this->year, $this->periodo, 1)->locale('es');
        return ucfirst($date->monthName) . ' ' . $this->year;
    }

    /**
     * Cantidad de empleados donde no tengan el estado 4,5 y 6 que representan ajustes de nomina y nominas elminadas.
     *
     * return count
     */
    public function empleados()
    {
        return Nomina::join('ne_personas as p','p.id','=','ne_nomina.fk_idpersona')
        ->where('periodo',$this->periodo)
        ->where('fk_idempresa', $this->fk_idempresa)
        ->where('p.status',1)
        ->whereIn('emitida', [1, 5, 6])
        ->whereNotIn('emitida', [4,7])
        ->orWhere('periodo',$this->periodo)
        ->where('fk_idempresa', $this->fk_idempresa)
        ->whereIn('emitida', [1, 5, 6])->count();
    }

    /**
     * Cantidad de nominas aceptadas, rechazadas y en espera de ser emitidas.
     *
     * return count
     */
    public function estadosNomina()
    {
        $arrayEstados = ['aceptadas' => 0, 'rechazadas' => 0, 'enEspera' => 0];
        $empresa = auth()->user()->empresa;


        $arrayEstados['aceptadas'] = Nomina::
        join('ne_personas as p','p.id','=','ne_nomina.fk_idpersona')
        // ->where('p.status',1)
        ->where('fk_idempresa', $empresa)->where('estado_nomina', 1)
        ->where('periodo', $this->periodo)
        ->whereIn('emitida', [1, 5, 6])
        ->count();

        $arrayEstados['enEspera'] = Nomina::
        join('ne_personas as p','p.id','=','ne_nomina.fk_idpersona')
        ->where('p.status',1)
        ->where('fk_idempresa', $empresa)
        ->where('estado_nomina', 1)
        ->where('periodo', $this->periodo)
        ->whereIn('emitida', [2, 4])
        ->count();

        $arrayEstados['rechazadas'] = Nomina::
        join('ne_personas as p','p.id','=','ne_nomina.fk_idpersona')
        ->where('p.status',1)
        ->where('fk_idempresa', $empresa)
        ->where('estado_nomina', 1)
        ->where('periodo', $this->periodo)
        ->where('cune', 409)
        ->whereNotIn('emitida', [1, 5])
        ->count();

        return (object) $arrayEstados;
    }

    public function prestacionesSociales()
    {
        return $this->hasMany(NominaPrestacionSocial::class, 'fk_idnomina');
    }

    public function prima()
    {
        return $this->hasOne(NominaPrestacionSocial::class, 'fk_idnomina')->where('nombre', 'prima');
    }

    public function cesantia()
    {
        return $this->hasOne(NominaPrestacionSocial::class, 'fk_idnomina')->where('nombre', 'cesantia');
    }

    public function interesesCesantia()
    {
        return $this->hasOne(NominaPrestacionSocial::class, 'fk_idnomina')->where('nombre', 'intereses_cesantia');
    }

    public function estado($class = false)
    {

        if ($class) {
            if ($this->emitida == 2 || $this->emitida == 0) {
                return 'danger';
            } else if ($this->emitida == 3) {
                return 'warning';
            } else if ($this->emitida == 1) {
                return 'success';
            } else if ($this->emitida == 4) {
                return 'primary';
            } else {
                return 'dark';
            }
        } else {
            $estado = '';

            /* >>> Validamos si la nomina fue rechazada mediante el cune igual a 409, si no seguimos el proceso normal <<< */
            if ($this->cune == 409 && $this->emitida != 1 && $this->emitida != 5) {
                $estado = 'Rechazada';
            } else {
                if ($this->emitida == 1) {
                    $estado = 'Emitida';
                } elseif ($this->emitida == 2) {
                    $estado = 'No emitida';
                } elseif ($this->emitida == 3) {
                    $estado = 'Anulada emitida';
                } elseif ($this->emitida == 4) {
                    $estado = 'Ajuste sin emitir';
                } elseif ($this->emitida == 5) {
                    $estado = 'Ajuste emitido';
                } elseif ($this->emitida == 6) {
                    $estado = 'Eliminada';
                }
            }


            return $estado;
        }
    }

    public function validarRechazada(){
        $curl = curl_init();

        $nitEmpleador = auth()->user()->empresaObj->nit;
        $numeroNomina = $this->codigo_dian;
    
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://apine.efacturacadena.com/v1/ne/consulta/documentos?empleadorNit={$nitEmpleador}&numeroNomina={$numeroNomina}&tipoXml=102",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'nominaAuthorizationToken: 42e5b496-d882-4041-97ec-e3e91750805f990ef12f-36ff-454b-b020-fb19e953c37397478011-edaf-4f66-9945-81b691a718b118213614-da33-4e7b-9c22-ce78e0d55cf0',
            'nitAlianza: 1128464945'
        ),
        ));

        $response = curl_exec($curl);
       
        if($response){
            
            $response = json_decode($response);
                if(is_object($response)){
                    if($response->statusCode == 200 || $response->statusCode == 409){
                        if($response->statusCodeDIAN == "RDI" || $response->statusCode == 409){
                            foreach($response->errorMessage as $err){
                                if($err == 'Regla: 90, Rechazo: Documento procesado anteriormente.'){
                                        $this->emitida = 1;
                                        $this->update();
                                        return true;
                                }
                            }
                            foreach($response->warnings as $err){
                                if($err == 'Regla: 90, Rechazo: Documento procesado anteriormente.'){
                                        $this->emitida = 1;
                                        $this->update();
                                        return true;
                                }
                            }
                        }
                    }
                   
                }
            
        }
       
        return false;
    }


    public function createJson($nomina)
    {
        $total_sueldo = 0;
        $TiempoLaborado = 0;
        foreach ($nomina->nominaperiodos as $nominaPeriodo) {
            $total_sueldo += $nominaPeriodo->valor_total;
            $TiempoLaborado += $nominaPeriodo->diasTrabajados();
            $date = Carbon::create($nominaPeriodo->fecha_hasta)->locale('es');
        }

        $empresa = Auth::user()->empresa();
        $url = "https://apine.efacturacadena.com";

        // $json =  new NominaResource($nomina);
        $json = array(
            "Tipo" => "1",
            "Periodo" => [
                "FechaIngreso" => $nomina->persona->fecha_contratacion,
                "FechaLiquidacionInicio" => $date->startOfMonth()->format('Y-m-d'),
                "FechaLiquidacionFin" => $date->endOfMonth()->format('Y-m-d'),
                "TiempoLaborado" => $TiempoLaborado,
                "FechaGen" => date('Y-m-d')
            ],
            "NumeroSecuenciaXML" => [
                "CodigoTrabajador" => "10001",
                "Prefijo" => "C",
                "Consecutivo" => "100001",
                "Numero" => "C100001"
            ],
            "LugarGeneracionXML" => [
                "Pais" => "CO",
                "DepartamentoEstado" => "05",
                "MunicipioCiudad" => "05001",
                "Idioma" => "es"
            ],
            "InformacionGeneral" => [
                "Version" => "V1.0=> Documento Soporte de Pago de Nómina Electrónica",
                "Ambiente" => "2",
                "TipoXML" => "102",
                "FechaGen" => date('Y-m-d'),
                "HoraGen" => date('H=>i=>s-05=>00'),
                "PeriodoNomina" => "4",
                "TipoMoneda" => "COP"
            ],
            "Empleador" => [
                "RazonSocial" => $empresa->nombre,
                "NIT" => $empresa->nit,
                "DV" => $empresa->dv,
                "Pais" => $empresa->fk_idpais,
                "DepartamentoEstado" => $empresa->departamento()->codigo,
                "MunicipioCiudad" => $empresa->municipio()->codigo_completo,
                "Direccion" => $empresa->direccion
            ],
            "Trabajador" => [
                "TipoTrabajador" => "01",
                "SubTipoTrabajador" => "00",
                "AltoRiesgoPension" => "false",
                "TipoDocumento" => "13",
                "NumeroDocumento" => $nomina->persona->nro_documento,
                "PrimerApellido" => $nomina->persona->apellido,
                "SegundoApellido" => "",
                "PrimerNombre" => $nomina->persona->nombre,
                "LugarTrabajoPais" => $empresa->fk_idpais,
                "LugarTrabajoDepartamentoEstado" => $empresa->departamento()->codigo,
                "LugarTrabajoMunicipioCiudad" => $empresa->municipio()->codigo_completo,
                "LugarTrabajoDireccion" => $empresa->direccio,
                "SalarioIntegral" => "false",
                "TipoContrato" => $nomina->persona->fk_tipo_contrato,
                "Sueldo" => $total_sueldo,
                "CodigoTrabajador" => "10001"
            ],
            "Pago" => [
                "Forma" => "1",
                "Metodo" => $nomina->persona->fk_metodo_pago,
                "Banco" => ($nomina->persona->fk_metodo_pago == 1) ? '' : $nomina->persona->banco(),
                "TipoCuenta" => ($nomina->persona->fk_metodo_pago == 1) ? '' : $nomina->persona->tipo_cuenta(),
                "NumeroCuenta" => ($nomina->persona->fk_metodo_pago == 1) ? '' : $nomina->persona->nro_cuenta
            ],
            "FechasPagos" => [
                "FechaPago" => [
                    "2021-08-31"
                ]
            ],
            "Devengados" => [
                "Basico" => [
                    "DiasTrabajados" => "31",
                    "SueldoTrabajado" => "00.00"
                ],
                "Cesantias" => [
                    "Pago" => "0.00",
                    "Porcentaje" => "00.00",
                    "PagoIntereses" => "00.00"
                ]
            ],
            "Deducciones" => [
                "Salud" => [
                    "Porcentaje" => "4.00",
                    "Deduccion" => "00.00"
                ],
                "FondoPension" => [
                    "Porcentaje" => "4.00",
                    "Deduccion" => "00.00"
                ],
                "FondoSP" => [
                    "Porcentaje" => "0.00",
                    "DeduccionSP" => "0.00",
                    "PorcentajeSub" => "0.00",
                    "DeduccionSub" => "0.00"
                ],
                "PagosTerceros" => [
                    "PagoTercero" => [
                        "00.00"
                    ]
                ],
                "OtrasDeducciones" => [
                    "OtraDeduccion" => [
                        "00.00"
                    ]
                ],
                "RetencionFuente" => "00.00",
                "AFC" => "00.00",
                "Cooperativa" => "00.00"
            ],
            "DevengadosTotal" => "00.00",
            "DeduccionesTotal" => "00.00",
            "ComprobanteTotal" => "00.00"
        );

        return $json;

        $client = new Client([
            'base_uri' => $url,
        ]);

        try {
            $response = $client->request('POST', 'staging/ne/documentos/proceso/sincrono', [
                'headers' => [
                    "nominaAuthorizationToken" => "42e5b496-d882-4041-97ec-e3e91750805f990ef12f-36ff-454b-b020-fb19e953c37397478011-edaf-4f66-9945-81b691a718b118213614-da33-4e7b-9c22-ce78e0d55cf0",
                    "nitAlianza" => "1128464945"
                ],
                'json' => $json,
                'verify' => false, //only needed if you are facing SSL certificate issue
            ]);

            return $body = $response->getBody();
            $arr_body = json_decode($body);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function diasHabilesEmision() {

        $diasferiados = array();

        $diasferiados = [
                         '2022-01-10','2022-03-21','2022-04-14','2022-04-15','2022-05-01','2022-05-17',
                         '2022-06-20','2022-06-27','2022-07-04','2022-07-20','2022-08-07','2022-08-15',
                         '2022-10-17','2022-11-07','2022-11-14','2022-12-08','2022-12-25',
                         '2023-01-01','2023-01-09','2023-03-20','2023-04-06','2023-04-07','2023-05-01',
                         '2023-05-22','2023-06-12','2023-06-19','2023-07-03','2023-07-20','2023-08-07',
                         '2023-08-21','2023-10-16','2023-11-06','2023-11-13','2023-12-08','2023-12-25'
        ];
        
        $fechaActual = Carbon::now();
        $yearActual = date("Y", strtotime($fechaActual));
        $mesActual = date("m", strtotime($fechaActual));

        
         /*>>> Acomodamos los topes posibles de emisión de nominas a la Dian <<<*/
         $fechaInicioHabil = $yearActual . "-" . $mesActual . "-1";
         $fechaFinMesHabil = $yearActual . "-" . $mesActual . "-25";
        
        // Convirtiendo en timestamp las fechas
        $fechainicio = strtotime($fechaInicioHabil);
        $fechafin = strtotime($fechaFinMesHabil);

        // Incremento en 1 dia
        $diainc = 24*60*60;
       
        // Arreglo de dias habiles, inicianlizacion
        $diashabiles = array();    
       
        // Se recorre desde la fecha de inicio a la fecha fin, incrementando en 1 dia
        $contDias=$diaTope=0;
        for ($midia = $fechainicio; $midia <= $fechafin; $midia += $diainc) {
                // Si el dia indicado, no es sabado o domingo es habil
                if (!in_array(date('N', $midia), array(6,7))) { // DOC: http://www.php.net/manual/es/function.date.php
                        // Si no es un dia feriado entonces es habil
                        if (!in_array(date('Y-m-d', $midia), $diasferiados)) {
                                array_push($diashabiles, date('Y-m-d', $midia));
                                $contDias++;
                                if($contDias==11){
                                    $diaTope = $contDias;
                                }
                        }
                }
        }

        $fechaDesdePermiso = $diashabiles[0];
        $fechaHastaPermiso = $diashabiles[$diaTope-1];
        
        if ($fechaActual >= $fechaDesdePermiso && $fechaActual <= $fechaHastaPermiso) {
            return true;
        } else {
            return false;
        }
       
        return $diashabiles;
    }
}
