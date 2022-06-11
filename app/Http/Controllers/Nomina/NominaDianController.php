<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

use Auth;
use App\Funcion;
use App\Empresa;
use App\Model\Nomina\Nomina;
use App\Model\Nomina\NominaPeriodos;
use App\Model\Nomina\NominaPreferenciaPago;
use App\Model\Nomina\NominaDetalleUno;
use App\Model\Nomina\NominaPrestacionSocial;
use App\Model\Nomina\NominaConfiguracionCalculos;
use App\Model\Nomina\NominaCalculoFijo;
use App\NumeracionFactura;
use App\Model\Nomina\NominaCuentasGeneralDetalle;
use App\Services\NominaService;
use Illuminate\Support\Facades\Response;

class NominaDianController extends Controller
{

    protected $nominaService;

    public function __construct(NominaService $nominaService)
    {
        $this->nominaService = $nominaService;
    }

    /**
     * Vista para habilitarse ante la dian en la nómina.
     *
     * @return view
     */
    public function asistente_DIAN()
    {
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['seccion' => 'nomina', 'title' => 'Asistente de habilitación DIAN', 'icon' => '']);

        $empresa = auth()->user()->empresaObj;

        return view('nomina.dian.asistente', compact('empresa'));
    }

    public function eliminarNominaEmpresa()
    {
        // $year = "2021";
        // $mes = "12";
        // $empresa = Auth::user()->empresa;

        // //obtenemos las nominas del periodo y año pasado por parametro.
        // $nominas = Nomina::where('fk_idempresa',$empresa)->where('year',$year)->where('periodo',$mes)->get();

        // foreach($nominas as $nomina){
        //     $nominasPeriodos = NominaPeriodos::where('fk_idnomina', $nomina->id)->get()->keyBy('id')->keys()->all();
        //         NominaCuentasGeneralDetalle::whereIn('fk_nominaperiodo', $nominasPeriodos)->delete();
        //         NominaDetalleUno::whereIn('fk_nominaperiodo', $nominasPeriodos)->delete();
        //         NominaCalculoFijo::whereIn('fk_nominaperiodo', $nominasPeriodos)->delete();
        //         NominaPrestacionSocial::where('fk_idnomina', $nomina->id)->delete();
        //         NominaPeriodos::where('fk_idnomina', $nomina->id)->delete();
        //         $nomina->delete();
        // }

        // return "nominas eliminadas";
    }

    /**
     * Método que realiza la habiitación de los clientes a la nomina con la dian.
     *
     * @return json
     */
    public function procesoHabilitacion(Request $request)
    {

        /* >>> Construimos primero las 3 nominas individuales <<< */
        $cant = 4;
        $prefijo = "AA";
        $rangoInd = 3000; //desde el número que comienza la emisión individual.
        $rangoAjus = $rangoInd + $cant + 1; //desde el número que comienza la emisión de ajuste.
        $empresa = Empresa::find(Auth::user()->empresaObj->id);
        $empresa->test_nomina = $request->settestid;
        $empresa->save();

        set_time_limit(120);

        if ($request->tipo == 1) {
            $pruebaIndividual = 0;
            $pruebaAjuste = 1;
        } else if ($request->tipo == 2) {
            $pruebaIndividual = 1; //0= para mandar habilitacion a la DIAN, 1 es para no mandar las pruebas a la DIAN
            $pruebaAjuste = 0;
        }

        $nominasIndividuales = $this->nominasIndividuales($cant, $rangoInd, $prefijo, $pruebaIndividual, $empresa);

        $nominasAjuste  =  $this->nominasAjuste($nominasIndividuales, $prefijo, $rangoInd, $rangoAjus, $pruebaAjuste, $empresa);

        /* >>> Si el contador marca 4 nominas de ajuste acepatdas es por que el set de pruebas ha sido habilitado <<< */
        $respuestaAjuste = json_decode($nominasIndividuales['response'], 1);

        if (isset($respuestaAjuste['validos'])) {
            if ($respuestaAjuste['validos'] >= 4) {
                $empresa->nomina_dian = 1;
            }
        }

        if ($pruebaIndividual == 0) {
            $empresa->json_nomina_ind = isset($nominasIndividuales['response']) ? json_decode($nominasIndividuales['response'], 1) : null;
        }
        if ($pruebaAjuste == 0) {
            $empresa->json_nomina_ajus = isset($nominasAjuste['response']) ? json_decode($nominasAjuste['response'], 1) : null;
        }
        $empresa->save();

        // return redirect()->back()->with('success','Emisiones Realizadas Correctamente.');

        return response()->json(1);
    }
    /**
     * construcción de nóminas de prueba (habilitación) de tipo individuales.
     *
     * @return json
     */
    public function nominasIndividuales($cantidad, $rango, $prefijo, $prueba, $empresa)
    {

        $arrayGralInd = [];
        $jsonResponseNominaGral = [];
        $cunes = [];
        $validos = [];
        $fallidos = [];
        $resp = [];

        for ($i = 1; $i <= $cantidad; $i++) {

            /*>>> Construcción del CUNE <<<*/

            $NumNE = $prefijo . $rango;
            $FecNE = date('Y-m-d');
            $HorNE = "12:00:00-05:00";
            $ValDev = "2010000.00";
            $ValDed = "240000.00";
            $ValTolNE = "1770000.00";
            $NitNE = $empresa->nit;
            $DocEmp = '2132313';
            $TipoXML = 102; //tipo 102 es nomina individual y 103 es nomina individual de ajuste
            $SoftwarePin = 75315;
            $TipAmb = 2;

            $cune = $NumNE . $FecNE . $HorNE . $ValDev . $ValDed . $ValTolNE . $NitNE . $DocEmp . $TipoXML .  $SoftwarePin . $TipAmb;
            // dd($cune);
            $cuneHasheado = hash('sha384', $cune);

            array_push($cunes, $cuneHasheado);

            $arrayInd = array(
                "Tipo" => "1",
                // "Novedad" => [
                //     "Novedad"=> "true",
                //     "CUNENov"=> $cuneHasheado
                // ],
                "Periodo" => [
                    "FechaIngreso" => "2021-04-12",
                    "FechaLiquidacionInicio" => "2021-07-01",
                    "FechaLiquidacionFin" => "2021-07-31",
                    "TiempoLaborado" => "30",
                    "FechaGen" => date('Y-m-d')
                ],
                "NumeroSecuenciaXML" => [
                    "CodigoTrabajador" => "2132313",
                    "Prefijo" => $prefijo,
                    "Consecutivo" => "" . $rango . "",
                    "Numero" => $prefijo . $rango
                ],
                "LugarGeneracionXML" => [
                    "Pais" => "CO",
                    "DepartamentoEstado" => "05",
                    "MunicipioCiudad" => "05001",
                    "Idioma" => "es"
                ],
                // "CodigoQR"=> "https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey=".$cuneHasheado,
                "InformacionGeneral" => [
                    "Version" => "V1.0: Documento Soporte de Pago de Nómina Electrónica",
                    "Ambiente" => "2",
                    "TipoXML" => "102",
                    "FechaGen" => date('Y-m-d'),
                    "HoraGen" => "12:00:00-05:00",
                    "PeriodoNomina" => "4",
                    "TipoMoneda" => "COP"
                ],
                "Empleador" => [
                    "RazonSocial" => $empresa->nombre,
                    "PrimerApellido" => "Apellido",
                    "SegundoApellido" => "",
                    "PrimerNombre" => $empresa->nombre,
                    "OtrosNombres" => $empresa->nombre,
                    "NIT" => $empresa->nit,
                    "DV" => $empresa->dv,
                    "Pais" => "CO",
                    "DepartamentoEstado" => "05",
                    "MunicipioCiudad" => "05001",
                    "Direccion" => $empresa->direccion
                ],
                "Trabajador" => [
                    "TipoTrabajador" => "18",
                    "SubTipoTrabajador" => "00",
                    "AltoRiesgoPension" => "false",
                    "TipoDocumento" => "13",
                    "NumeroDocumento" => "2132313",
                    "PrimerApellido" => "Perea",
                    "SegundoApellido" => " ",
                    "PrimerNombre" => "Gonzalo",
                    "OtrosNombres" => " ",
                    "LugarTrabajoPais" => "CO",
                    "LugarTrabajoDepartamentoEstado" => "05",
                    "LugarTrabajoMunicipioCiudad" => "05001",
                    "LugarTrabajoDireccion" => "CALLE 32C 66C-64, Belen, Medellín, Antioquia",
                    "SalarioIntegral" => "false",
                    "TipoContrato" => "2",
                    "Sueldo" => "1770000.00",
                    "CodigoTrabajador" => "10001"
                ],
                "Pago" => [
                    "Forma" => "1",
                    "Metodo" => "10",
                    "Banco" => " ",
                    "TipoCuenta" => " ",
                    "NumeroCuenta" => " "
                ],
                "FechasPagos" => [
                    "FechaPago" => [
                        "2021-11-30"
                    ]
                ],
                "Devengados" => [

                    "Basico" => [
                        "DiasTrabajados" => "30",
                        "SueldoTrabajado" => "2000000.00"
                    ],
                    "Transporte" => [
                        [
                            "AuxilioTransporte" => "10000.00"
                        ]
                    ],
                    "HEDs" => [
                        "HED" => [
                            [
                                "Cantidad" => "0",
                                "Porcentaje" => "25.00",
                                "Pago" => "0.00"
                            ]
                        ]
                    ],
                    "HENs" => [
                        "HEN" => [
                            [
                                "Cantidad" => "0",
                                "Porcentaje" => "75.00",
                                "Pago" => "0.00"
                            ]
                        ]
                    ],
                    "HRNs" => [
                        "HRN" => [
                            [
                                "Cantidad" => "0",
                                "Porcentaje" => "35.00",
                                "Pago" => "0.00"
                            ]
                        ]
                    ],
                    "HEDDFs" => [
                        "HEDDF" => [
                            [
                                "Cantidad" => "0",
                                "Porcentaje" => "100.00",
                                "Pago" => "0.00"
                            ]
                        ]
                    ],
                    "HRDDFs" => [
                        "HRDDF" => [
                            [
                                "Cantidad" => "0",
                                "Porcentaje" => "75.00",
                                "Pago" => "0.00"
                            ]
                        ]
                    ],
                    "HENDFs" => [
                        "HENDF" => [
                            [
                                "Cantidad" => "0",
                                "Porcentaje" => "150.00",
                                "Pago" => "0.00"
                            ]
                        ]
                    ],
                    "HRNDFs" => [
                        "HRNDF" => [
                            [
                                "Cantidad" => "0",
                                "Porcentaje" => "110.00",
                                "Pago" => "0.00"
                            ]
                        ]
                    ],
                    "Vacaciones" => [
                        "VacacionesComunes" => [],
                        "VacacionesCompensadas" => []
                    ],
                    "Incapacidades" => [
                        "Incapacidad" => []
                    ],
                    "Licencias" => [
                        "LicenciaMP" => [],
                        "LicenciaR" => [],
                        "LicenciaNR" => []
                    ],
                    "Bonificaciones" => [
                        "Bonificacion" => []
                    ],
                    "Auxilios" => [
                        "Auxilio" => []
                    ],
                    "HuelgasLegales" => [
                        "HuelgaLegal" => []
                    ],
                    "OtrosConceptos" => [
                        "OtroConcepto" => []
                    ],
                    "Compensaciones" => [
                        "Compensacion" => []
                    ],
                    "BonoEPCTVs" => [
                        "BonoEPCTV" => []
                    ],
                    "Comisiones" => [
                        "Comision" => []
                    ],
                    "PagosTerceros" => [
                        "PagoTercero" => []
                    ],
                    "Anticipos" => [
                        "Anticipo" => []
                    ],
                    "Dotacion" => "00.00",
                    "ApoyoSost" => "00.00",
                    "Teletrabajo" => "00.00",
                    "BonifRetiro" => "00.00",
                    "Indemnizacion" => "00.00",
                    "Reintegro" => "00.00"
                ],
                "Deducciones" => [
                    "Salud" => [
                        "Porcentaje" => "4.00",
                        "Deduccion" => "80000.00"
                    ],
                    "FondoPension" => [
                        "Porcentaje" => "8.00",
                        "Deduccion" => "160000.00"
                    ],
                    "FondoSP" => [
                        "Porcentaje" => "0.00",
                        "DeduccionSP" => "0.00",
                        "PorcentajeSub" => "0.00",
                        "DeduccionSub" => "0.00"
                    ],
                    "Sindicatos" => [
                        "Sindicato" => [
                            [
                                "Porcentaje" => "0.00",
                                "Deduccion" => "0.00"
                            ]
                        ]
                    ],
                    "Sanciones" => [
                        "Sancion" => [
                            [
                                "SancionPublic" => "0.00",
                                "SancionPriv" => "0.00"
                            ]
                        ]
                    ],
                    "Libranzas" => [
                        "Libranza" => [
                            [
                                "Descripcion" => " ",
                                "Deduccion" => "0.00"
                            ]
                        ]
                    ],
                    "PagosTerceros" => [
                        "PagoTercero" => []
                    ],
                    "Anticipos" => [
                        "Anticipo" => []
                    ],
                    "OtrasDeducciones" => [
                        "OtraDeduccion" => []
                    ],
                    "PensionVoluntaria" => "00.00",
                    "RetencionFuente" => "0.00",
                    "AFC" => "00.00",
                    "Cooperativa" => "00.00",
                    "EmbargoFiscal" => "00.00",
                    "PlanComplementarios" => "00.00",
                    "Educacion" => "00.00",
                    "Reintegro" => "00.00",
                    "Deuda" => "00.00"
                ],
                "Redondeo" => "00.00",
                "DevengadosTotal" => "2010000.00",
                "DeduccionesTotal" => "240000.00",
                "ComprobanteTotal" => "1770000.00",
            );

            //retornar array para pruebas
            // return $arrayInd;
            if (!$prueba) {
                $res = $this->enviarJsonDianApi($arrayInd, 3);

                if ($res['statusCode'] == '200') {
                    $validos[] = ([
                        "statusCode" => $res['statusCode'],
                        "trackId" => isset($res['trackId']) ? $res['trackId'] : null,
                        "cune" => isset($res['cune']) ? $res['cune'] : null,
                        "statusMessage" => isset($res['statusMessage']) ? $res['statusMessage'] : null,
                        "statusDescription" => isset($res['statusDescription']) ? $res['statusDescription'] : null,
                        "warnings" => isset($res['warnings']) ? $res['warnings'] : null
                    ]);
                } else {
                    $fallidos[] = ([
                        "statusCode" => $res['statusCode'],
                        "trackId" => isset($res['trackId']) ? $res['trackId'] : null,
                        "cune" => isset($res['cune']) ? $res['cune'] : null,
                        "errorMessage" => isset($res['errorMessage']) ? $res['errorMessage'] : null,
                        "errorReason" =>  isset($res['errorReason']) ? $res['errorReason'] : null,
                        "statusDescription" => isset($res['statusDescription']) ? $res['statusDescription'] : null,
                        "warnings" => isset($res['warnings']) ? $res['warnings'] : null
                    ]);
                }
            }

            array_push($arrayGralInd, $arrayInd);
            $rango = $rango + 1;
        }

        if (!$prueba) {
            $resp = array('validos' => count($validos), 'data_validos' => $validos, 'fallidos' => count($fallidos), 'data_fallidos' => $fallidos);
        }

        return $arrayFinal = [
            'json' => $arrayGralInd,
            'cune' => $cunes,
            'response' => json_encode($resp)
        ];
    }

    /**
     * construcción de nóminas de prueba (habilitación) de tipo ajuste (Eliminar).
     *
     * @return json
     */
    public function nominasAjuste($nominasIndividuales, $prefijo, $rangoInd, $rangoAjus, $prueba, $empresa)
    {

        $arrayGralInd = [];
        $validos = [];
        $fallidos = [];
        $resp = [];
        $contValidos = 0;

        for ($i = 0; $i < count($nominasIndividuales['json']); $i++) {

            /*>>> Construcción del CUNE de la nómina a eliminar no de la predecesora<<<*/
            $NumNE = "" . $prefijo . $rangoAjus . "";
            $FecNE = date('Y-m-d');
            $HorNE = "12:00:00-05:00";
            $ValDev = "2010000.00";
            $ValDed = "240000.00";
            $ValTolNE = "1770000.00";
            $NitNE = $empresa->nit;
            $DocEmp = '2132313';
            $TipoXML = 103; //tipo 102 es nomina individual y 103 es nomina individual de ajuste
            $SoftwarePin = 75315;
            $TipAmb = 2;

            $cune = $NumNE . $FecNE . $HorNE . $ValDev . $ValDed . $ValTolNE . $NitNE . $DocEmp . $SoftwarePin . $TipAmb;
            $cuneHasheado = hash('sha384', $cune);


            $arrayAjuste = array(

                "Tipo" => "2",
                "TipoNota" => "2",
                "Eliminar" => [
                    "EliminandoPredecesor" => [
                        "NumeroPred" => $prefijo . $rangoInd,
                        "CUNEPred" => $nominasIndividuales['cune'][$i],
                        "FechaGenPred" => $nominasIndividuales['json'][$i]['Periodo']['FechaGen']
                    ],
                    "NumeroSecuenciaXML" => [
                        "Prefijo" => $nominasIndividuales['json'][$i]['NumeroSecuenciaXML']['Prefijo'],
                        "Consecutivo" => "" . $rangoAjus . "",
                        "Numero" => $prefijo . $rangoAjus,
                    ],
                    "LugarGeneracionXML" => [
                        "Pais" => $nominasIndividuales['json'][$i]['LugarGeneracionXML']['Pais'],
                        "DepartamentoEstado" => $nominasIndividuales['json'][$i]['LugarGeneracionXML']['DepartamentoEstado'],
                        "MunicipioCiudad" => $nominasIndividuales['json'][$i]['LugarGeneracionXML']['MunicipioCiudad'],
                        "Idioma" => $nominasIndividuales['json'][$i]['LugarGeneracionXML']['Idioma']
                    ],
                    // "CodigoQR"=> "https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey={$cuneHasheado}",
                    "InformacionGeneral" => [
                        "Version" => "V1.0: Nota de Ajuste de Documento Soporte de Pago de Nómina Electrónica",
                        "Ambiente" => "2",
                        "TipoXML" => "103",
                        "CUNE" => $cuneHasheado,
                        "EncripCUNE" => "CUNE-SHA384",
                        "FechaGen" => date('Y-m-d'),
                        "HoraGen" => "12:00:00-05:00",
                    ],
                    "Notas" => [],
                    "Empleador" => [
                        "RazonSocial" => $nominasIndividuales['json'][$i]['Empleador']['RazonSocial'],
                        "PrimerApellido" => $nominasIndividuales['json'][$i]['Empleador']['PrimerApellido'],
                        "SegundoApellido" => $nominasIndividuales['json'][$i]['Empleador']['SegundoApellido'],
                        "PrimerNombre" => $nominasIndividuales['json'][$i]['Empleador']['PrimerNombre'],
                        "OtrosNombres" => $nominasIndividuales['json'][$i]['Empleador']['OtrosNombres'],
                        "NIT" => $nominasIndividuales['json'][$i]['Empleador']['NIT'],
                        "DV" => $nominasIndividuales['json'][$i]['Empleador']['DV'],
                        "Pais" => $nominasIndividuales['json'][$i]['Empleador']['Pais'],
                        "DepartamentoEstado" => $nominasIndividuales['json'][$i]['Empleador']['DepartamentoEstado'],
                        "MunicipioCiudad" => $nominasIndividuales['json'][$i]['Empleador']['MunicipioCiudad'],
                        "Direccion" => $nominasIndividuales['json'][$i]['Empleador']['Direccion']
                    ]
                ]
            );

            if (!$prueba) {

                $res = $this->enviarJsonDianApi($arrayAjuste, 3);

                if ($res['statusCode'] == '200') {
                    $validos[] = ([
                        "statusCode" => $res['statusCode'],
                        "trackId" => isset($res['trackId']) ? $res['trackId'] : null,
                        "cune" => isset($res['cune']) ? $res['cune'] : null,
                        "statusMessage" => isset($res['statusMessage']) ? $res['statusMessage'] : null,
                        "statusDescription" => isset($res['statusDescription']) ? $res['statusDescription'] : null,
                        "warnings" => isset($res['warnings']) ? $res['warnings'] : null
                    ]);
                    $contValidos++;
                } else {
                    $fallidos[] = ([
                        "statusCode" => $res['statusCode'],
                        "trackId" => isset($res['trackId']) ? $res['trackId'] : null,
                        "cune" => isset($res['cune']) ? $res['cune'] : null,
                        "errorMessage" => isset($res['errorMessage']) ? $res['errorMessage'] : null,
                        "errorReason" =>  isset($res['errorReason']) ? $res['errorReason'] : null,
                        "statusDescription" => isset($res['statusDescription']) ? $res['statusDescription'] : null,
                        "warnings" => isset($res['warnings']) ? $res['warnings'] : null
                    ]);
                }
            }

            array_push($arrayGralInd, $arrayAjuste);

            $rangoInd++;
            $rangoAjus++;
        }

        if (!$prueba) {
            $resp = array('validos' => count($validos), 'data_validos' => $validos, 'fallidos' => count($fallidos), 'data_fallidos' => $fallidos);
        }
        return $arrayFinal = [
            'json' => $arrayGralInd,
            'response' => json_encode($resp)
        ];
    }

    /**
     * 
     * Primer metodo que se llama a l aohra de emitir una nómina a la dian, retorna alguna validación faltante
     * antes de que la información viaje a la dian.
     *
     * @return json
     */
    public function validate_dian(Request $request)
    {
        $data = $this->emitirJson($request->id);
        return response()->json($data, 200);
    }


    /**
     * Método que transofmra en un json la información de las nominas que se van a emitir y las envia a la DIAN.
     * recibe como parametros el id de la nomina a enviar a la dian.
     *
     * @return json
     */
    public function emitirJson($nominaId)
    {
        $nomina = Nomina::findOrFail($nominaId);

        $rechazada = json_decode($nomina->json_dian);

    
        $numeracionFactura = $this->obtenerNumeracion($nomina);

        if (!$numeracionFactura) {
            return "nomina-vencida";
        } elseif ($numeracionFactura->inicio == $numeracionFactura->final) {
            return 'nomina-consecutivo-limite';
        }


        /*>>> Validacion de que la nómina esté dentro de los primeros 10 días de emision hábiles <<<*/
        $fechaEmisionHabil = $nomina->diasHabilesEmision();
        if (!$fechaEmisionHabil) {
            // return 'plazo-vencido';
        }

        $json = "";
        $jsonEdit = $this->nominaElectronica($nomina);

        /*>>> Generamos el consecutivo de la dian en este punto al momento de comenzar una emisión <<<*/
        if ($nomina->codigo_dian == null) {
            $numeroNumeracion = $numeracionFactura->inicio;
            $nomina->codigo_dian = $numeracionFactura->prefijo . $numeroNumeracion;
            $nomina->fk_idnumeracion = $numeracionFactura->id;
            $nomina->save();
            $numeracionFactura->inicio = $numeroNumeracion + 1;
            $numeracionFactura->save();
        } else {
            $repetidaDian =  Nomina::where('id', '!=', $nominaId)->where('fk_idempresa', auth()->user()->empresa)->where('codigo_dian', $nomina->codigo_dian)->first();
            if ($repetidaDian) {
                $nomina->codigo_dian = null;
                $nomina->update();
                return 'codigo-repetido';
            }
        }

        /*>>> Generamos el numero de secuencia XML con los datos generales para cualquier tipo de nomina a emitir <<<*/
        $jsonEdit['NumeroSecuenciaXML']['CodigoTrabajador'] = $jsonEdit['Empleador']['NIT'];
        $jsonEdit['NumeroSecuenciaXML']['Prefijo'] = $numeracionFactura->prefijo;
        $jsonEdit['NumeroSecuenciaXML']['Consecutivo'] = preg_replace('/[^0-9]+/', '', $nomina->codigo_dian);
        $jsonEdit['NumeroSecuenciaXML']['Numero'] = $nomina->codigo_dian;

        /*>>> Construcción del CUNE <<<*/
        $NumNE = "" . $nomina->codigo_dian . "";
        $FecNE = "" . $jsonEdit['InformacionGeneral']['FechaGen'];
        $HorNE = "" . $jsonEdit['InformacionGeneral']['HoraGen'];
        $ValDev = "" . number_format($jsonEdit['DevengadosTotal'], 2, '.', '') . "";
        $ValDed = "" . number_format($jsonEdit['DeduccionesTotal'], 2, '.', '') . "";
        $ValTolNE = "" . number_format($jsonEdit['ComprobanteTotal'], 2, '.', '') . "";
        $NitNE = "" . $jsonEdit['Empleador']['NIT'] . "";
        $DocEmp = "" . $jsonEdit['Trabajador']['NumeroDocumento'] . "";
        $TipoXML = 102; //tipo 102 es nomina individual y 103 es nomina individual de ajuste
        $SoftwarePin = 75315;
        $TipAmb = 1;

        $cune = $NumNE . $FecNE . $HorNE . $ValDev . $ValDed . $ValTolNE . $NitNE . $DocEmp . $SoftwarePin . $TipAmb;
        $cuneHasheado = hash('sha384', $cune);

        //metodo para retornar el json sin necesidad de ingresar a ningun metodo
        // /empresa/nominadian/validatedian?id=$id
        // if(Auth::user()->empresa == 1){
        //     $jsonIndividual = array(
        //         "Tipo" => "1",
        //         "Novedad" => [
        //             "Novedad" => "false",
        //             "CUNENov" => $cuneHasheado,
        //         ],
        //     );

        //     /*>>> Construcción del QR <<<*/
        //     $jsonEdit['CodigoQR'] = "https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey={$cuneHasheado}";
        //     $json = array_merge($jsonIndividual, $jsonEdit);
        //     dd($json);
        // }

        if ($nomina->emitida == Nomina::NO_EMITIDA) {

            $jsonIndividual = array(
                "Tipo" => "1",
                "Novedad" => [
                    "Novedad" => "false",
                    "CUNENov" => $cuneHasheado,
                ],
            );

            /*>>> Construcción del QR <<<*/
            $jsonEdit['CodigoQR'] = "https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey={$cuneHasheado}";
            $json = array_merge($jsonIndividual, $jsonEdit);

            // Return solamente para mirar el json.
            // return $json;

        } elseif ($nomina->emitida == Nomina::AJUSTE_SIN_EMITIR) {

            /*>>>  Obtenemos la nomina asociada al ajuste.  <<<*/
            $nominaAsociada = Nomina::where('cune', $nomina->cune_relacionado)->where('emitida', 3)->first();

            $jsonIndividual = array(
                "ReemplazandoPredecesor" => array(
                    "NumeroPred" =>  preg_replace('/[^0-9]+/', '', $nominaAsociada->codigo_dian),
                    "CUNEPred" => $nominaAsociada->cune,
                    "FechaGenPred" => date('Y-m-d', strtotime($nominaAsociada->fecha_emision)),
                )
            );

            /*>>> Construcción del QR <<<*/
            // $jsonEdit['CodigoQR'] = "https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey={$nominaAsociada->cune}";
            unset($jsonEdit['CodigoQR']);
            $json = array_merge($jsonIndividual, $jsonEdit);

            $newJson = array_merge($jsonIndividual, $jsonEdit);
            
            $newJson['InformacionGeneral']['Version'] = "V1.0: Nota de Ajuste de Documento Soporte de Pago de Nómina Electrónica";

            $json = array(
                "Tipo" => "2",
                "TipoNota" => "1",
                "Reemplazar" => $newJson,
            );

            // Return solamente para mirar el json.
            // return $json;
            
        } elseif ($nomina->emitida == Nomina::EMITIDA) {

            if ($nomina->cune != 409 && $nomina->cune_relacionado != null) {
                $nomina->cune_relacionado = $nomina->cune;
            }
            $json = $this->nominaAjusteTipoEliminar($nomina);

            // Return solamente para mirar el json.
            // return $json;
        }

        /*>>> ----------------------------------------------------------------- <<<*/
        /*>>> EMPEZAMOS A MANDAR LOS DATOS A LA DIAN POR MEDIO DEL JSON <<<*/
        $response = $this->enviarJsonDianApi($json, 1);
        // $response['statusCode'] = 200;


        if (isset($response['statusCode'])) {
            if ($response['statusCode'] == 200) {

                if ($nomina->emitida == Nomina::AJUSTE_SIN_EMITIR) {
                    $nomina->emitida = Nomina::AJUSTE_EMITIDO;
                } else if ($nomina->emitida == Nomina::EMITIDA) {

                    $nomina->emitida = Nomina::ELIMINADA;
                    $nroEliminada = $numeracionFactura->inicio;
                    $nomina->codigo_dian_eliminado = $numeracionFactura->prefijo . $nroEliminada;
                    $numeracionFactura->inicio = $nroEliminada + 1;
                    $numeracionFactura->save();
                } else {
                    $nomina->emitida = Nomina::EMITIDA;
                }

                $nomina->cune = $cuneHasheado;
            }

            /*>>>  Guardamos respuesta de dian y retornamos la nomina emitida  <<<*/
            if ($response['statusCode'] == 409 || $response['statusCode'] == 400 || $response['statusCode'] == 504 || $response['statusCode'] == 500) {
                $arrayResponseSave = array(
                    'statusCode' => $response['statusCode'],
                    'statusMessage' => isset($response['statusMessage']) ? $response['statusMessage'] : '',
                    'warnings' => isset($response['warnings']) ? $response['warnings'] : '',
                    'rechazada' => 1
                );

                /*>>>  cuando el cune tiene una valor igual a 409 es por que se rechazó  <<<*/
                $nomina->cune = 409;
            } else {
                $arrayResponseSave = array(
                    'statusCode' => $response['statusCode']
                );
            }
        } else {
            if ($response['message'] == 'Too Many Requests') {
                return 'mucha-solicitud';
            }
        }


        $nomina->json_dian = json_encode($arrayResponseSave);
        $nomina->save();

        if($nomina->estado() == 'Rechazada'){
            if($nomina->validarRechazada() == true){
                /* mensaje personalizado */
                return '';
            }
        }

        return $response;
    }

    /**
     *
     * Método que se encar de obetner la numeración de una nómina
     *
     * @return json
     */
    public function obtenerNumeracion($nomina)
    {

        $currentDate = date('Y-m-d');

        if ($nomina->tipo == 2) {
            return NumeracionFactura::where('nomina', 1)
                ->orderByDesc('id')
                ->where('preferida', 1)
                ->where('tipo_nomina', 2)
                ->where('empresa', auth()->user()->empresa)
                ->first();
        }

        return NumeracionFactura::where('nomina', 1)
            ->orderByDesc('id')
            ->where('preferida', 1)
            ->where('tipo_nomina', 1)
            ->where('empresa', auth()->user()->empresa)
            ->first();
    }

    /**
     * Método en el cual se construye la estructura madre del json a enviar a la Dian.
     *
     * @return json
     */
    public function nominaElectronica(Nomina $nomina)
    {

        $total_sueldo = 0;
        $TiempoLaborado = 0;
        foreach ($nomina->nominaperiodos as $nominaPeriodo) {
            $total_sueldo += $nominaPeriodo->valor_total;
            $TiempoLaborado += $nominaPeriodo->diasTrabajados();
            $date = Carbon::parse($nominaPeriodo->fecha_hasta)->locale('es');
        }

        $empresa = Auth::user()->empresa();
        $usuario = $empresa->usuario();
        $metodo  = $nomina->persona->metodo_pago_codigo();


        $json = array(
            "Periodo" => [
                "FechaIngreso" => $nomina->persona->fecha_contratacion,
                "FechaLiquidacionInicio" => $date->startOfMonth()->format('Y-m-d'),
                "FechaLiquidacionFin" => $date->endOfMonth()->format('Y-m-d'),
                "TiempoLaborado" => "$TiempoLaborado",
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
                "DepartamentoEstado" => $nomina->persona->departamento()->codigo,
                "MunicipioCiudad" => $nomina->persona->municipio()->codigo_completo,
                "Idioma" => "es"
            ],
            "CodigoQR" => "",
            "InformacionGeneral" => [
                "Version" => "V1.0: Documento Soporte de Pago de Nómina Electrónica",
                "Ambiente" => config('app.ambiente_nomina'),
                "TipoXML" => $nomina->emitida == 4 ? "103" : "102",
                "FechaGen" => date('Y-m-d'),
                "HoraGen" => date('H:i:sP'),
                "PeriodoNomina" => "4",
                "TipoMoneda" => "COP"
            ],
            "Empleador" => [
                "RazonSocial" => $empresa->nombre,
                "PrimerApellido" => $usuario->primer_apellido,
                "SegundoApellido" => $usuario->segundo_apellido,
                "PrimerNombre" => $usuario->primer_nombre,
                "OtrosNombres" => $usuario->segundo_nombre,
                "NIT" => $empresa->nit,
                "DV" => $empresa->dv,
                "Pais" => $empresa->fk_idpais,
                "DepartamentoEstado" => $empresa->departamento()->codigo,
                "MunicipioCiudad" => $empresa->municipio()->codigo_completo,
                "Direccion" => $empresa->direccion
            ],
            "Trabajador" => [
                "TipoTrabajador" => $nomina->persona->nomina_tipo_contrato->codigo,
                "SubTipoTrabajador" => ($nomina->persona->nomina_tipo_contrato->id == 17 ? "01" : "00"),
                "AltoRiesgoPension" => "false",
                "TipoDocumento" => $nomina->persona->tipo_documento('codigo'),
                "NumeroDocumento" => $nomina->persona->nro_documento,
                "PrimerApellido" => $nomina->persona->primer_apellido,
                "SegundoApellido" => $nomina->persona->segundo_apellido,
                "PrimerNombre" => $nomina->persona->primer_nombre,
                "OtrosNombres" => $nomina->persona->segundo_nombre,
                "LugarTrabajoPais" => $empresa->fk_idpais,
                "LugarTrabajoDepartamentoEstado" => $empresa->departamento()->codigo,
                "LugarTrabajoMunicipioCiudad" => $empresa->municipio()->codigo_completo,
                "LugarTrabajoDireccion" => $empresa->direccion,
                "SalarioIntegral" => "false",
                "TipoContrato" => $nomina->persona->terminoContrato->codigo,
                "Sueldo" => number_format($total_sueldo, 2, '.', ''),
                "CodigoTrabajador" => "10001"
            ],
            "Pago" => [
                "Forma" => "1",
                "Metodo" => $metodo,
                "Banco" => ($nomina->persona->fk_metodo_pago == 1) ? " " : $nomina->persona->banco(),
                "TipoCuenta" => ($nomina->persona->fk_metodo_pago == 1) ? " " : $nomina->persona->tipo_cuenta(),
                "NumeroCuenta" => ($nomina->persona->fk_metodo_pago == 1) ? " " : $nomina->persona->nro_cuenta
            ],
            "FechasPagos" => [
                "FechaPago" => [
                    Carbon::now()->endOfMonth()->format('Y-m-d')
                ],
            ],
            "Devengados" => [], //END DEVENGADOS

            "Deducciones" => [],
            "Redondeo" => "00.00",
            "DevengadosTotal" => "00.00",
            "DeduccionesTotal" => "00.00",
            "ComprobanteTotal" => "00.00"
        );


        $json["Devengados"] = $this->estructuraJsonDevengados($nomina->id);
        $json["Deducciones"] = $this->estructuraJsonDeducciones($nomina->id);


        $devengadosTotal = $this->sumaDevengadosTotal($json['Devengados']);
        $deduccionesTotal = $this->sumaDeducionesTotal($json['Deducciones']);

        $json['DevengadosTotal'] = number_format($devengadosTotal, 2, '.', '');
        $json['DeduccionesTotal'] = number_format($deduccionesTotal, 2, '.', '');

        $comprobanteTotal = $devengadosTotal - $deduccionesTotal;
        $json['ComprobanteTotal'] = number_format($comprobanteTotal, 2, '.', '');

        $nomina->fecha_emision = Carbon::now();
        $nomina->save();
        
        if($metodo == 10){
            unset($json['Pago']['Banco'], $json['Pago']['TipoCuenta'], $json['Pago']['NumeroCuenta']);
        }

        return $json;
    }

    /**
     * Estructura del json a enviar a la dian de tipo eliminar (es distinto al de ajuste reemplazar y nomina individual).
     *
     * @return json
     */
    public function nominaAjusteTipoEliminar(Nomina $nomina)
    {
        $empresa = Auth::user()->empresa();
        return array(
            "Tipo" => "2",
            "TipoNota" => "2",
            "Eliminar" => [
                "EliminandoPredecesor" => [
                    "NumeroPred" =>  preg_replace('/[^0-9]+/', '', $nomina->codigo_dian),
                    "CUNEPred" => $nomina->cune_relacionado,
                    "FechaGenPred" => date('Y-m-d', strtotime($nomina->fecha_emision)),
                ],
                "NumeroSecuenciaXML" => [
                    "Prefijo" => $nomina->numeracionfactura->prefijo,
                    "Consecutivo" => preg_replace('/[^0-9]+/', '', $nomina->codigo_dian),
                    "Numero" => $nomina->codigo_dian
                ],
                "LugarGeneracionXML" => [
                    "Pais" => "CO",
                    "DepartamentoEstado" => $nomina->persona->departamento()->codigo,
                    "MunicipioCiudad" => $nomina->persona->municipio()->codigo,
                    "Idioma" => "es"
                ],
                "CodigoQR" => "https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey={$nomina->cune_relacionado}",
                "InformacionGeneral" => [
                    "Version" => "V1.0=> Documento Soporte de Pago de Nómina Electrónica",
                    "Ambiente" => config('app.ambiente_nomina'),
                    "TipoXML" => "103",
                    "CUNE" => $nomina->cune_relacionado,
                    "EncripCUNE" => "CUNE-SHA384",
                    "FechaGen" => date('Y-m-d'),
                    "HoraGen" => date('H:i:sP'),
                ],
                "Notas" => [],
                "Empleador" => [
                    "RazonSocial" => $empresa->nombre,
                    "PrimerApellido" => "Apellido",
                    "SegundoApellido" => "",
                    "PrimerNombre" => $empresa->nombre,
                    "OtrosNombres" => "",
                    "NIT" => $empresa->nit,
                    "DV" => $empresa->dv,
                    "Pais" => $empresa->fk_idpais,
                    "DepartamentoEstado" => $empresa->departamento()->codigo,
                    "MunicipioCiudad" => $empresa->municipio()->codigo_completo,
                    "Direccion" => $empresa->direccion
                ],
            ]
        );
    }

    /**
     * Método de la vista de emitir nomina.
     * recibe como parametros el periodo a consultar, el año a consultar y el tipo (0=mensual,1=quincena 1, 2=quincena 2)
     *
     * @return view
     */
    public function emitir($periodo, $year, $tipo = null)
    {
        $usuario = auth()->user();
        $this->getAllPermissions($usuario->id);
        $empresa = auth()->user()->empresaObj;

        // $guiasVistas = DB::connection('mysql')->table('tips_modulo_usuario')
        //     ->select('tips_modulo_usuario.*')
        //     ->join('permisos_modulo', 'permisos_modulo.id', '=', 'tips_modulo_usuario.fk_idpermiso_modulo')
        //     ->where('permisos_modulo.nombre_modulo', 'Nomina')
        //     ->where('fk_idusuario', $usuario->id)
        //     ->get();


        /* >>> si la primer nomina recuperada en el get tiene 2 periodos si o si todas las nominas traidas de ese año y periodo deben ser
        quincenales <<< */
        $variosPeriodos = Nomina::with('nominaperiodos')
            ->where('year', $year)
            ->where('periodo', $periodo)
            ->first()
            ->nominaperiodos;


        /* >>> si el tipo es igual a null las nominas con los periodos que traeremos serán del primer periodo, sea mensual o quincenal <<< */
        if ($tipo == null) {
            if (count($variosPeriodos) > 1) {
                $tipoA = $variosPeriodos->first()->periodo;
                $tipoB = $variosPeriodos->last()->periodo;
            } else {
                $tipoA = $variosPeriodos->first()->periodo;
                $tipoB = null;
            }
        }

        //obtenemos las nominas del periodo actual y si tenemos un miniperiodo de la quincena nos traremos esa nomina con ese periodo.
        $nominas = Nomina::with('persona')
            ->with([
                'nominaperiodos' => function ($query) use ($tipoA, $tipoB) {
                    $query->orWhere('periodo', $tipoA);
                    $query->orWhere('periodo', $tipoB);
                }
            ])
            ->where('ne_nomina.year', $year)
            ->where('ne_nomina.periodo', $periodo)
            ->where('ne_nomina.fk_idempresa', $usuario->empresa)
            // ->where('emitida', '<>', 6)
            ->where('estado_nomina', 1)
            ->orderBy('id', 'asc')
            ->get();


        $idNominas = $nominas->keyBy('id')->keys();

        $devengadosTotalV = 0;
        $deduccionesTotalV = 0;
        $totalPagoV = 0;


        foreach ($idNominas as $idNomina) {

            $jsonDevengados = $this->estructuraJsonDevengados($idNomina);

            $devengadosTotal = $this->sumaDevengadosTotal($jsonDevengados);

            $devengadosTotalV += $devengadosTotal;

            $jsonDeducciones = $this->estructuraJsonDeducciones($idNomina);
            $deduccionesTotal = $this->sumaDeducionesTotal($jsonDeducciones);

            $deduccionesTotalV += $deduccionesTotal;

            $totalPago = $devengadosTotal - $deduccionesTotal;
            $totalPagoV += $totalPago;
        }

        $costoPeriodo = self::costoPeriodo($tipo, $idNominas);

        $preferencia = NominaPreferenciaPago::where('empresa', $usuario->empresa)->first();

        $mensajePeriodo = $preferencia->periodo($periodo, $year, $tipo);
        $date = Carbon::create($year, $periodo, 1)->locale('es');

        view()->share([
            'seccion' => 'nomina',
            'title' => 'Emitir Nómina | ' . ucfirst($date->monthName) . ' ' . $date->format('Y'),
            'icon' => 'fas fa-sitemap'
        ]);
        $i = 0;
        $swUnaVez = 0;
        $emitidas = 0;
        $personas = 0;


        foreach ($nominas as $nomina) {
            $ingresos = 0;
            $extras = 0;
            $vacaciones = 0;
            $deducciones = 0;
            $total = 0;
            $estado = '';
            $prestacionesSociales = $nomina->prestacionesSociales;
            
            foreach ($nomina->nominaperiodos as $nominaPeriodo) {
                $total += $nominaPeriodo->valor_total;
            }

            foreach($prestacionesSociales as $p){
                $total += $p->valor_pagar;
                $totalPagoV += $p->valor_pagar;
                $devengadosTotalV += $p->valor_pagar;
            }


            $detalles[$i]['idnomina'] = $nomina->id;
            $detalles[$i]['idpersona'] = $nomina->persona->id;
            $detalles[$i]['persona'] = $nomina->persona->nombre();
            $detalles[$i]['identificacion'] = $nomina->persona->nro_documento;
            $detalles[$i]['total'] = $total;
            $detalles[$i]['estado'] = $nomina->estado();
            $detalles[$i]['emitida'] = $nomina->emitida;
            $detalles[$i]['text'] = $nomina->estado(true);
            $detalles[$i]['periodo'] = $nomina->periodo;
            $detalles[$i]['year'] = $nomina->year;
            $detalles[$i]['nota'] = $nomina->nota;
            $detalles[$i]['codigo_dian'] = $nomina->codigo_dian;
            $detalles[$i]['codigo_dian_eliminado'] = $nomina->codigo_dian_eliminado;

            if ($detalles[$i]['estado'] == 'Emitida' || $detalles[$i]['estado'] == 'Anulada emitida') {
                $emitidas++;
            }
            $personas++;
            $i++;

            /* >>> Ingresamos solo una vez a consultar los estados de todas las nominas en el modelo Nomina <<< */
            if ($swUnaVez == 0) {
                $estadosNomina = $nomina->estadosNomina();
            }
            $swUnaVez = 1;
        }

        $i = 0;
        $pagoTotal = 0;
        $totalDeducciones = 0;

        foreach ($nominas as $nomina) {
            $nominas = NominaPeriodos::with('nomina')->where('fk_idnomina', $nomina->id)->get();

            foreach ($nominas as $nomina) {

                $resumenTotal = $nomina->resumenTotal();

                $pagoTotal += $resumenTotal['pago']['total'];
                $totalDeducciones += $resumenTotal['deducciones']['total'];
                $i++;
            }
        }

        if ($personas == $emitidas) {
            $isFinalizado = true;
        } else {
            $isFinalizado = false;
        }

        $modoLectura = (object) $usuario->modoLecturaNomina();


        return view(
            'nomina.emision',
            [
                'nominas' => $nominas,
                'moneda' => $usuario->empresaObj->moneda,
                'costoPeriodo' => $costoPeriodo,
                'date' => $date,
                'detalles' => $detalles,
                'pagoTotal' => $pagoTotal,
                'devengadosTotal' => $devengadosTotalV, //totalDeducciones
                'deduccionesTotal' => $deduccionesTotalV,
                'ingresosTotal' => $totalPagoV,
                'periodo' => $periodo,
                'year' => $year,
                'estadosNomina' => $estadosNomina,
                'emitidas' => $emitidas,
                'personas' => $personas,
                'isFinalizado' => $isFinalizado,
                'modoLectura' => $modoLectura,
                'empresa' => $empresa
            ]
        );
    }


    public function estructuraJsonDevengados($idNomina)
    {
        $nomina = Nomina::find($idNomina);

        $data = $this->calculoCompletoNomina($idNomina);
        $totalDetallesNomina = $this->obtenerTotalDetalleNomina($nomina);

        $totalidad = $data['totalidad'];
        $totalidad['pago']['salario'];
        $totalidad['salarioSubsidio']['subsidioTransporte'];

        $heds = $totalDetallesNomina->where('nombre', 'HORA EXTRA ORDINARIA')->first();
        $hens = $totalDetallesNomina->where('nombre', 'HORA EXTRA NOCTURNA')->first();
        $hrns = $totalDetallesNomina->where('nombre', 'RECARGO NOCTURNO')->first();
        $heddfs = $totalDetallesNomina->where('nombre', 'HORA EXTRA ORDINARIA DOMINICAL')->first();
        $hrddfs = $totalDetallesNomina->where('nombre', 'RECARGO DOMINICAL')->first();
        $hendfs = $totalDetallesNomina->where('nombre', 'HORA EXTRA NOCTURNA DOMINICAL')->first();
        $hrndfs = $totalDetallesNomina->where('nombre', 'RECARGO NOCTURNO DOMINICAL')->first();

        $vacacionesMes = $this->obtenerAlgunosNominaDetalles($nomina);
        $primas = NominaPrestacionSocial::where('fk_idnomina', $nomina->id)->where('nombre', 'prima')->first();
        $cesantias = NominaPrestacionSocial::where('fk_idnomina', $nomina->id)->get();
        $incapacidadesMes = $this->obtenerIncapacidadesMes($nomina);
        $licenciaMaternidadPaternidad = $this->obtenerLicenciaMP($nomina);
        $licenciaRemunerada = $this->obtenerAlgunosNominaDetalles($nomina, 'LICENCIA REMUNERADA');
        $licenciaNoRemunerada = $this->obtenerAlgunosNominaDetalles($nomina, 'LICENCIA NO REMUNERADA');
        $bonificaciones = $this->bonificaciones($nomina);
        $auxilio = $this->auxilio($nomina);
        $otrosConceptos = $this->otrosConceptos($nomina);
        $comisiones = $this->comisiones($nomina);
        
        /* >>> SueldoTrabajado se toma de ese elemento ya que es el pago neto de lo que gana el empleado x los días trabajados. <<< */ 
        $json = [
            "Basico" => [
                "DiasTrabajados" => strval($totalidad['diasTrabajados']['total']),
                "SueldoTrabajado" => number_format($totalidad['ibcSeguridadSocial']['salario'], 2, '.', '')
            ],
        ];

        if ($totalidad['salarioSubsidio']['subsidioTransporte'] != 0) {
            $json['Transporte'] = [
                [
                    "AuxilioTransporte" => number_format(
                        $totalidad['salarioSubsidio']['subsidioTransporte'],
                        2,
                        '.',
                        ''
                    )
                ]
            ];
        }

        $jsonPart2 = [
            "HEDs" => [
                "HED" => [
                    [
                        "Cantidad" => strval($heds['numero_horas']),
                        "Porcentaje" => "25.00",
                        "Pago" => number_format($heds['valor_categoria'], 2, '.', '')
                    ]
                ]
            ],
            "HENs" => [
                "HEN" => [
                    [
                        "Cantidad" => strval($hens['numero_horas']),
                        "Porcentaje" => "75.00",
                        "Pago" => number_format($hens['valor_categoria'], 2, '.', '')
                    ]
                ]
            ],
            "HRNs" => [
                "HRN" => [
                    [
                        "Cantidad" => strval($hrns['numero_horas']),
                        "Porcentaje" => "35.00",
                        "Pago" => number_format($hrns['valor_categoria'], 2, '.', '')
                    ]
                ]
            ],
            "HEDDFs" => [
                "HEDDF" => [
                    [
                        "Cantidad" => strval($heddfs['numero_horas']),
                        "Porcentaje" => "100.00",
                        "Pago" => number_format($heddfs['valor_categoria'], 2, '.', '')
                    ]
                ]
            ],
            "HRDDFs" => [
                "HRDDF" => [
                    [
                        "Cantidad" => strval($hrddfs['numero_horas']),
                        "Porcentaje" => "75.00",
                        "Pago" => number_format($hrddfs['valor_categoria'], 2, '.', '')
                    ]
                ]
            ],
            "HENDFs" => [
                "HENDF" => [
                    [
                        "Cantidad" => strval($hendfs['numero_horas']),
                        "Porcentaje" => "150.00",
                        "Pago" => number_format($hendfs['valor_categoria'], 2, '.', '')
                    ]
                ]
            ],
            "HRNDFs" => [
                "HRNDF" => [
                    [
                        "Cantidad" => strval($hrndfs['numero_horas']),
                        "Porcentaje" => "110.00",
                        "Pago" => number_format($hrndfs['valor_categoria'], 2, '.', '')
                    ]
                ]
            ],
            "Vacaciones" => [
                "VacacionesComunes" => [],
                "VacacionesCompensadas" => []
            ],
            "Primas" => [],
            "Cesantias" => [],
            "Incapacidades" => [
                "Incapacidad" => []
            ],
            "Licencias" => [
                "LicenciaMP" => [],
                "LicenciaR" => [],
                "LicenciaNR" => [],
            ],
            "Bonificaciones" => [
                "Bonificacion" => []
            ],
            "Auxilios" => [
                "Auxilio" => []
            ],
            "HuelgasLegales" => [
                "HuelgaLegal" => []
            ],
            "OtrosConceptos" => [
                "OtroConcepto" => []
            ],
            "Compensaciones" => [
                "Compensacion" => []
            ],
            "BonoEPCTVs" => [
                "BonoEPCTV" => []
            ],
            "Comisiones" => [
                "Comision" => []
            ],
            "PagosTerceros" => [
                "PagoTercero" => []
            ],
            "Anticipos" => [
                "Anticipo" => []
            ],
            "Dotacion" => "00.00",
            "ApoyoSost" => "00.00",
            "Teletrabajo" => "00.00",
            "BonifRetiro" => "00.00",
            "Indemnizacion" => "00.00",
            "Reintegro" => "00.00",
        ];

        $json = array_merge($json, $jsonPart2);

        foreach ($vacacionesMes as $key => $value) {
            $json['Vacaciones']['VacacionesComunes'][$key] = [
                "FechaInicio" => $value->fecha_inicio,
                "FechaFin" => $value->fecha_fin,
                "Cantidad" => strval($value->dias_vacaciones),
                "Pago" => number_format($value->valor_categoria, 2, '.', ''),
            ];
        }


        if ($primas) {
            $json["Primas"] = [
                "Cantidad" => $primas->dias_trabajados,
                "Pago" => number_format($primas->valor_pagar, 2, '.', ''),
                "PagoNS" => "00.00",

            ];
        } else {
            unset($json["Primas"]);
        }


        if ($cesantias) {
            $valorCesantia = 0;
            $valorInteresCesantia = 0;

            foreach ($cesantias as $cesantia) {
                if ($cesantia->nombre == "cesantia") {
                    $valorCesantia = $cesantia->valor_pagar;
                } else if ($cesantia->nombre == "intereses_cesantia") {
                    $valorInteresCesantia = $cesantia->valor_pagar;
                }
            }

            $json["Cesantias"] =
                [
                    "Pago" => number_format($valorCesantia, 2, '.', ''),
                    "Porcentaje" => "12.00",
                    "PagoIntereses" => number_format($valorInteresCesantia, 2, '.', '')
                ];
        } else {
            unset($json["Cesantias"]);
        }


        /**=================================
         *    INCAPACIDADES MES
         *================================**/
        foreach ($incapacidadesMes as $key => $value) {
            $json['Incapacidades']['Incapacidad'][$key] = [
                "FechaInicio" => $value->fecha_inicio,
                "FechaFin" => $value->fecha_fin,
                "Cantidad" => strval($value->dias_vacaciones),
                "Tipo" => $value->tipo_incapacidad,
                "Pago" => number_format($value->valor_categoria, 2, '.', ''),
            ];
        }

        /**======================================
         *    LICENCIA MATERNIDAD Y PATERNIDAD
         *=====================================**/
        foreach ($licenciaMaternidadPaternidad as $key => $value) {
            $json['Licencias']['LicenciaMP'][$key] = [
                "FechaInicio" => $value->fecha_inicio,
                "FechaFin" => $value->fecha_fin,
                "Cantidad" => strval($value->dias_vacaciones),
                "Pago" => number_format($value->valor_categoria, 2, '.', ''),
            ];
        }

        /**=================================
         *    LICENCIA REMUNERADA
         *================================**/
        foreach ($licenciaRemunerada as $key => $value) {
            $json['Licencias']['LicenciaR'][$key] = [
                "FechaInicio" => $value->fecha_inicio,
                "FechaFin" => $value->fecha_fin,
                "Cantidad" => strval($value->dias_vacaciones),
                "Pago" => number_format($value->valor_categoria, 2, '.', '')
            ];
        }


        /**=================================
         *    LICENCIA NO REMUNERADA
         *================================**/
        foreach ($licenciaNoRemunerada as $key => $value) {
            $json['Licencias']['LicenciaNR'][$key] = [
                "FechaInicio" => $value->fecha_inicio,
                "FechaFin" => $value->fecha_fin,
                "Cantidad" => strval($value->dias_vacaciones)
                // "Pago" => number_format($value->valor_categoria, 2, '.', '')
            ];
        }


        /**=================================
         *    Bonificaciones
         *================================**/
        if ($bonificaciones->isNotEmpty()) {
            $json['Bonificaciones']['Bonificacion'] = [
                [
                    $bonificaciones[0]['tipo_nomina_cuenta'] => number_format($bonificaciones[0]['valor'], 2, '.', ''),
                    $bonificaciones[0]['tipo_nomina_cuenta'] => number_format($bonificaciones[0]['valor'], 2, '.', ''),
                ]
            ];
        }

        /**=================================
         *    AUXILIOS
         *================================**/
        if ($auxilio) {
            $json['Auxilios']['Auxilio'] = [
                [
                    "AuxilioNS" => number_format(strval($auxilio), 2, '.', '')
                ]
            ];
        }

        /**=================================
         *    OTROS CONCEPTOS
         *================================**/
        if ($otrosConceptos->isNotEmpty()) {
            foreach ($otrosConceptos as $key => $value) {
                $json['OtrosConceptos']['OtroConcepto'][$key] = [
                    "DescripcionConcepto" => $value->nombre,
                    "ConceptoNS" => number_format($value->valor_categoria, 2, '.', '')
                ];
            }
        }

        /**=================================
         *    COMISIONES
         *================================**/
        if ($comisiones > 0) {
            $json['Comisiones']['Comision'][0] = number_format($comisiones, 2, '.', '');
        }

        return $json;
    }

    public function sumaDevengadosTotal($devengados)
    {
        if (!isset($devengados['Transporte'][0]['AuxilioTransporte'])) {
            $devengados['Transporte'][0]['AuxilioTransporte'] = 0;
        }

        $total = $devengados['Basico']['SueldoTrabajado'] + $devengados['Transporte'][0]['AuxilioTransporte'] +
            $devengados['HEDs']['HED'][0]['Pago'] + $devengados['HENs']['HEN'][0]['Pago'] + $devengados['HRNs']['HRN'][0]['Pago'] +
            $devengados['HEDDFs']['HEDDF'][0]['Pago'] + $devengados['HRDDFs']['HRDDF'][0]['Pago'] + $devengados['HENDFs']['HENDF'][0]['Pago'] +
            $devengados['HRNDFs']['HRNDF'][0]['Pago'];

        if (count($devengados['Vacaciones']['VacacionesComunes']) > 0) {
            foreach ($devengados['Vacaciones']['VacacionesComunes'] as $item) {
                $total += $item['Pago'];
            }
        }

        if (isset($devengados["Devengados"]['Primas']['Pago'])) {
            $total += $devengados["Devengados"]['Primas']['Pago'];
        }

        if (isset($devengados["Devengados"]['Cesantias']['Pago'])) {
            $total += $devengados["Devengados"]['Cesantias']['Pago'];
        }


        if (count($devengados['Incapacidades']['Incapacidad']) > 0) {
            foreach ($devengados['Incapacidades']['Incapacidad'] as $item) {
                $total += $item['Pago'];
            }
        }

        if (count($devengados['Bonificaciones']['Bonificacion']) > 0) {

            if (isset($devengados['Bonificaciones']['Bonificacion'][0]['BonificacionS'])) {
                $total += $devengados['Bonificaciones']['Bonificacion'][0]['BonificacionS'];
            }


            if (isset($devengados['Bonificaciones']['Bonificacion'][0]['BonificacionNS'])) {
                $total += $devengados['Bonificaciones']['Bonificacion'][0]['BonificacionNS'];
            }
        }


        if (count($devengados['Auxilios']['Auxilio']) > 0) {
            $total += $devengados['Auxilios']['Auxilio'][0]['AuxilioNS'];
        }


        if (count($devengados['OtrosConceptos']['OtroConcepto']) > 0) {
            foreach ($devengados['OtrosConceptos']['OtroConcepto'] as $item) {
                $total += $item['ConceptoNS'];
            }
        }


        if (count($devengados['Comisiones']['Comision']) > 0) {
            foreach ($devengados['Comisiones']['Comision'] as $item) {
                $total += $item;
            }
        }
        
        if(isset($devengados['Primas']['Pago'])){
            $total+=$devengados['Primas']['Pago'];
        }
        
        if(isset($devengados['Cesantias']['PagoIntereses'])){
            $total+=$devengados['Cesantias']['PagoIntereses'];
        }
        
        if(isset($devengados['Cesantias']['Pago'])){
            $total+=$devengados['Cesantias']['Pago'];
        }
        
        if (count($devengados['Licencias']['LicenciaR']) > 0) {
            foreach ($devengados['Licencias']['LicenciaR'] as $item) {
                $total += $item['Pago'];
            }
        }

        return $total;
    }

    public function estructuraJsonDeducciones($idNomina)
    {
        $nomina = Nomina::find($idNomina);
        $json = [
            "Salud" => [
                "Porcentaje" => "00.00",
                "Deduccion" => "00.00"
            ],
            "FondoPension" => [
                "Porcentaje" => "00.00",
                "Deduccion" => "00.00"
            ],
            "FondoSP" => [
                "Porcentaje" => "0.00",
                "DeduccionSP" => "0.00",
                "PorcentajeSub" => "0.00",
                "DeduccionSub" => "0.00"
            ],
            "Sindicatos" => [
                "Sindicato" => [
                    [
                        "Porcentaje" => "0.00",
                        "Deduccion" => "0.00",
                    ]
                ]
            ],
            "Sanciones" => [
                "Sancion" => [
                    [
                        "SancionPublic" => "0.00",
                        "SancionPriv" => "0.00"
                    ]
                ]
            ],
            "Libranzas" => [
                "Libranza" => [
                    [
                        "Descripcion" => " ",
                        "Deduccion" => "0.00",
                    ]
                ]
            ],
            "PagosTerceros" => [
                "PagoTercero" => []
            ],
            "Anticipos" => [
                "Anticipo" => []
            ],
            "OtrasDeducciones" => [
                "OtraDeduccion" => []
            ],
            "PensionVoluntaria" => "00.00",
            "RetencionFuente" => "00.00",
            "AFC" => "00.00",
            "Cooperativa" => "00.00",
            "EmbargoFiscal" => "00.00",
            "PlanComplementarios" => "00.00",
            "Educacion" => "00.00",
            "Reintegro" => "00.00",
            "Deuda" => "00.00",
        ];
        
        $idnominaPeriodos = NominaPeriodos::where('fk_idnomina', $nomina->id)->get()->pluck('id');
        
        /**==========================================
        *   SUMA DE PENSION VOLUNTARIA.
        *========================================**/
        $pensionVoluntaria = NominaDetalleUno::whereIn('fk_nominaperiodo', $idnominaPeriodos)
        ->where(function ($query) {
            $query->where('nombre', 'APORTES VOLUNTARIOS A FONDOS DE PENSIONES');
        })->get();
        $totalPensionVoluntaria = $pensionVoluntaria->sum('valor_categoria');
        if($totalPensionVoluntaria > 0){
                    $json['PensionVoluntaria'] = $totalPensionVoluntaria.'';
        }

        $otrasDeduciones = $this->otrasDeducciones($nomina);
        $retencionFuente = $this->retencionFuente($nomina);
        $anticipos       = $this->anticipos($nomina);


        /**==========================================
         *   CALCULAR RETEN EN SALUD  Y RETEN PENSION
         *========================================**/
        $retenSalud = NominaConfiguracionCalculos::where('fk_idempresa', $nomina->fk_idempresa)
            ->where('nro', 2)->first();
        $retenPension = NominaConfiguracionCalculos::where('fk_idempresa', $nomina->fk_idempresa)
            ->where('nro', 3)->first();

        $porcentajeSal = $retenSalud->porcDecimal() * 100;
        $porcentajePen = $retenPension->porcDecimal() * 100;

        $salud = NominaCalculoFijo::whereIn('fk_nominaperiodo', $idnominaPeriodos)
            ->where('tipo', 'reten_salud')->sum('valor');
        $pension = NominaCalculoFijo::whereIn('fk_nominaperiodo', $idnominaPeriodos)
            ->where('tipo', 'reten_pension')->sum('valor');


        $json['Salud']['Porcentaje'] = number_format($porcentajeSal, 2, '.', '');
        $json['Salud']['Deduccion'] = number_format($salud, 2, '.', '');

        $json['FondoPension']['Porcentaje'] = number_format($porcentajePen, 2, '.', '');
        $json['FondoPension']['Deduccion'] = number_format($pension, 2, '.', '');
        // end

        /**==========================================
         *   ANTICIPOS
         *========================================**/
        if ($anticipos > 0) {
            $json['Anticipos']['Anticipo'][0] = number_format($anticipos, 2, '.', '');
        }

        /**==========================================
         *   OTRAS DEDUCCIONES
         *========================================**/

        if ($otrasDeduciones > 0) {
            $json['OtrasDeducciones']['OtraDeduccion'][0] = number_format($otrasDeduciones, 2, '.', '');
        }



        $json['RetencionFuente'] = number_format($retencionFuente, 2, '.', '');


        return $json;
    }


    public function sumaDeducionesTotal($deducciones)
    {

        $salud = $deducciones['Salud']['Deduccion'];
        $pension = $deducciones['FondoPension']['Deduccion'];
        $retencionFuente = $deducciones['RetencionFuente'];

        $otrasDeduciones = 0;
        if (count($deducciones['OtrasDeducciones']['OtraDeduccion']) > 0) {
            $otrasDeduciones = $deducciones['OtrasDeducciones']['OtraDeduccion'][0];
        }


        $deduccionesTotal = $salud + $pension + $retencionFuente + $otrasDeduciones;
        return $deduccionesTotal;
    }

    public static function costoPeriodo($tipo, $nominas = [])
    {
        if (!$nominas) {

            $nominas = Nomina::with('persona')
                ->with([
                    'nominaperiodos' => function ($query) use ($tipo) {
                        $query->where('periodo', $tipo);
                    }
                ])
                ->where('ne_nomina.year', request()->year)
                ->where('ne_nomina.periodo', request()->periodo)
                ->where('ne_nomina.fk_idempresa', Auth::user()->empresa)
                ->get();
            $nominas = $nominas->keyBy('id')->keys();
        }

        $costo = new \stdClass();
        $costo->pagoEmpleados = Funcion::parsear(floatval(DB::table('ne_nomina_periodos')
            ->select(DB::raw('SUM(valor_total) as total_pago'))
            ->where('periodo', $tipo)
            ->whereIn('fk_idnomina', $nominas)
            ->first()->total_pago ?? 0));

        $costo->costoEmpresa = 0;

        $nominasPeriodo = NominaPeriodos::whereIn('fk_idnomina', $nominas)->where('periodo', $tipo)->get();
        foreach ($nominasPeriodo->chunk(3) as $nominaChunk) {
            foreach ($nominaChunk as $nomina) {
                $totalidad = $nomina->resumenTotal();
                $costo->costoEmpresa = $costo->costoEmpresa + $totalidad['salarioSubsidio']['total'] + ($totalidad['provisionPrestacion']['total'] + $totalidad['seguridadSocial']['total'] + $totalidad['parafiscales']['total']);
            }
        }

        $costo->costoEmpresa = Funcion::parsear($costo->costoEmpresa);

        if (request()->ajax()) {
            return response()->json(['costo' => $costo]);
        } else {
            return $costo;
        }
    }

    public function calculoCompletoNomina($nominaId)
    {
        $nominas = NominaPeriodos::with('nomina')->where('fk_idnomina', $nominaId)->get();

        $moneda = Auth::user()->empresaObj->moneda;
        $i = 0;

        if (!$nominas) {
            return back();
        }

        $periodo = null;

        $totalidad = [
            'pago' => [
                'salario' => 0,
                'subsidioDeTransporte' => 0,
                'retencionesDeducciones' => 0,
                'total' => 0,
                'extrasOrdinariasRecargos' => 0,
                'vacaciones' => 0,
                'ingresosAdicionales' => 0
            ],
            'diasTrabajados' => ['diasPeriodo' => 0, 'total' => 0],
            'salarioSubsidio' => ['salario' => 0, 'subsidioTransporte' => 0, 'total' => 0],
            'ibcSeguridadSocial' => [
                'salario' => 0,
                'total' => 0,
                'vacaciones' => 0,
                'ingresosyExtras' => 0,
                'incapacidades' => 0,
                'salarioParcial' => 0,
                'licencias' => 0
            ],
            'retenciones' => [
                'salud' => 0,
                'pension' => 0,
                'total' => 0,
                'porcentajeSalud' => 0,
                'porcentajePension' => 0
            ],
            'seguridadSocial' => ['pension' => 0, 'riesgo1' => 0, 'total' => 0],
            'parafiscales' => ['cajaCompensacion' => 0, 'total' => 0],
            'provisionPrestacion' => [
                'cesantias' => 0,
                'interesesCesantias' => 0,
                'primaServicios' => 0,
                'vacaciones' => 0,
                'total' => 0
            ],
            'deducciones' => ['total' => 0]
        ];

        foreach ($nominas as $nomina) {

            $resumenT = $nomina->resumenTotal();

            $totalidad['pago']['salario'] += $resumenT['pago']['salario'];
            $totalidad['pago']['subsidioDeTransporte'] += $resumenT['pago']['subsidioDeTransporte'];
            $totalidad['pago']['retencionesDeducciones'] += $resumenT['pago']['retencionesDeducciones'];
            $totalidad['pago']['total'] += $resumenT['pago']['total'];
            $totalidad['pago']['extrasOrdinariasRecargos'] += $resumenT['pago']['extrasOrdinariasRecargos'];
            $totalidad['pago']['vacaciones'] += $resumenT['pago']['vacaciones'];
            $totalidad['pago']['ingresosAdicionales'] += $resumenT['pago']['ingresosAdicionales'];

            $totalidad['diasTrabajados']['diasPeriodo'] += $resumenT['diasTrabajados']['diasPeriodo'];
            $totalidad['diasTrabajados']['total'] += $resumenT['diasTrabajados']['total'];
            $totalidad['diasTrabajados']['ausencia'] = $nomina->diasAusenteDetalle();

            $totalidad['salarioSubsidio']['salario'] += $resumenT['salarioSubsidio']['salario'];
            $totalidad['salarioSubsidio']['subsidioTransporte'] += $resumenT['salarioSubsidio']['subsidioTransporte'];
            $totalidad['salarioSubsidio']['total'] += $resumenT['salarioSubsidio']['total'];
            $totalidad['salarioSubsidio']['salarioCompleto'] = $resumenT['salarioSubsidio']['salarioCompleto'];
            $totalidad['salarioSubsidio']['valorDia'] = $resumenT['salarioSubsidio']['valorDia'];

            $totalidad['ibcSeguridadSocial']['salario'] += $resumenT['ibcSeguridadSocial']['salario'];
            $totalidad['ibcSeguridadSocial']['total'] += $resumenT['ibcSeguridadSocial']['total'];
            $totalidad['ibcSeguridadSocial']['vacaciones'] += $resumenT['ibcSeguridadSocial']['vacaciones'];
            $totalidad['ibcSeguridadSocial']['ingresosyExtras'] += $resumenT['ibcSeguridadSocial']['ingresosyExtras'];
            $totalidad['ibcSeguridadSocial']['incapacidades'] += $resumenT['ibcSeguridadSocial']['incapacidades'];
            $totalidad['ibcSeguridadSocial']['salarioParcial'] += $resumenT['ibcSeguridadSocial']['salarioParcial'];
            $totalidad['ibcSeguridadSocial']['licencias'] += $resumenT['ibcSeguridadSocial']['licencias'];

            $totalidad['retenciones']['salud'] += $resumenT['retenciones']['salud'];
            $totalidad['retenciones']['pension'] += $resumenT['retenciones']['pension'];
            $totalidad['retenciones']['total'] += $resumenT['retenciones']['total'];
            $totalidad['retenciones']['porcentajeSalud'] = $resumenT['retenciones']['porcentajeSalud'];
            $totalidad['retenciones']['porcentajePension'] = $resumenT['retenciones']['porcentajePension'];

            $totalidad['seguridadSocial']['pension'] += $resumenT['seguridadSocial']['pension'];
            $totalidad['seguridadSocial']['riesgo1'] += $resumenT['seguridadSocial']['riesgo1'];
            $totalidad['seguridadSocial']['total'] += $resumenT['seguridadSocial']['total'];

            $totalidad['parafiscales']['cajaCompensacion'] += $resumenT['parafiscales']['cajaCompensacion'];
            $totalidad['parafiscales']['total'] += $resumenT['parafiscales']['total'];

            $totalidad['provisionPrestacion']['cesantias'] += $resumenT['provisionPrestacion']['total'];
            $totalidad['provisionPrestacion']['interesesCesantias'] += $resumenT['provisionPrestacion']['interesesCesantias'];
            $totalidad['provisionPrestacion']['primaServicios'] += $resumenT['provisionPrestacion']['primaServicios'];
            $totalidad['provisionPrestacion']['vacaciones'] += $resumenT['provisionPrestacion']['vacaciones'];
            $totalidad['provisionPrestacion']['total'] += $resumenT['provisionPrestacion']['total'];

            $totalidad['deducciones']['total'] += $resumenT['deducciones']['total'];

            //$totalidad[$i] = $nomina->resumenTotal();
            $persona = $nomina->nomina->persona;
            $periodo = $nomina->nomina->periodo;
            $year = $nomina->nomina->year;
            $i++;
        }

        $preferencia = NominaPreferenciaPago::where('empresa', auth()->user()->empresa)->first();
        $mensajePeriodo = $preferencia->periodoCompleto($periodo, $year);

        return [
            'nominas' => $nominas,
            'moneda' => $moneda,
            'totalidad' => $totalidad,
            'persona' => $persona,
            'mensajePeriodo' => $mensajePeriodo,
        ];
    }

    /**
     * 
     * Obtenemos el total detallado de horas extras de la nomina enviada por parámetro.
     *
     * @return collect
     */
    public function obtenerTotalDetalleNomina(Nomina $nomina)
    {
        $idsNominasPeriodo = $nomina->nominaperiodos()->pluck('id');
        $detallesNomina = NominaDetalleUno::whereIn('fk_nominaperiodo', $idsNominasPeriodo)->get();

        $detallesNominaTotal = $detallesNomina->groupBy('nombre')->map(function ($data, $nombre) {

            $horas = 0;
            foreach ($data as $detalle) {
                if ($detalle->fecha_inicio) {
                    $horas += $detalle->horas();
                }
            }

            return [
                "nombre" => $nombre,
                "numero_horas" => $data->sum('numero_horas'),
                "valor_categoria" => $data->sum('valor_categoria'),
                "fk_nomina_cuenta_tipo" => $data[0]['fk_nomina_cuenta_tipo'],
                "fk_nomina_cuenta" => $data[0]['fk_nomina_cuenta'],
                "fecha_inicio" => $data[0]['fecha_inicio'],
                "horas" => $horas
            ];
        })->values();

        return $detallesNominaTotal;
    }

    /**
     * 
     * Obtenemos los detalles por defecto de las VACACIONES de la nomina enviada por parámetro.
     *
     * @return collect
     */
    public function obtenerAlgunosNominaDetalles(Nomina $nomina, $tipo = 'VACACIONES')
    {
        $idsNominasPeriodo = $nomina->nominaperiodos()->pluck('id');
        $data = NominaDetalleUno::select('nombre', 'valor_categoria', 'fecha_inicio', 'fecha_fin')
            ->whereIn('fk_nominaperiodo', $idsNominasPeriodo)
            ->where('nombre', $tipo)
            ->where('fecha_inicio', '<>', null)
            ->where('fecha_fin', '<>', null)
            ->get();
        return $data;
    }

    /**
     * 
     * Obtenemos las incapacidades del mes para la construcción principamente del 
     * json de devengados de la nomina enviada por parámetro.
     *
     * @return collect
     */
    public function obtenerIncapacidadesMes(Nomina $nomina)
    {
        $idsNominasPeriodo = $nomina->nominaperiodos()->pluck('id');
        $data = NominaDetalleUno::select('codigo', 'nombre', 'valor_categoria', 'fecha_inicio', 'fecha_fin', 'tipo_incapacidad')
            ->whereIn('fk_nominaperiodo', $idsNominasPeriodo)
            ->where('fecha_inicio', '<>', null)
            ->where('fecha_fin', '<>', null)
            ->where(function ($q) {
                $q->where('nombre', 'INCAPACIDAD GENERAL')
                    ->orWhere('nombre', 'INCAPACIDAD LABORAL')
                    ->orWhere('nombre', 'AUSENCIA INJUSTIFICADA')
                    ->orWhere('nombre', 'SUSPENSION');
            })->get();
        return $data;
    }

    /**
     * 
     * Obtenemos las licencias de maternidad y paternidad  del mes para la construcción principamente 
     * del json de devengados de la nomina enviada por parámetro.
     *
     * @return collect
     */
    public function obtenerLicenciaMP(Nomina $nomina)
    {
        $idsNominasPeriodo = $nomina->nominaperiodos()->pluck('id');
        $data = NominaDetalleUno::select('nombre', 'valor_categoria', 'fecha_inicio', 'fecha_fin')
            ->whereIn('fk_nominaperiodo', $idsNominasPeriodo)
            ->where(function ($q) {
                $q->where('nombre', 'LICENCIA DE PATERNIDAD')
                    ->orWhere('nombre', 'LICENCIA DE MATERNIDAD');
            })
            ->where('fecha_inicio', '<>', null)
            ->where('fecha_fin', '<>', null)
            ->get();
        return $data;
    }

    /**
     * 
     * Obtenemos las bonificaciones  del mes para la construcción principamente 
     * del json de devengados de la nomina enviada por parámetro.
     *
     * @return collect
     */
    public function bonificaciones(Nomina $nomina)
    {
        $idsNominasPeriodo = $nomina->nominaperiodos()->pluck('id');
        $ingresosAdicionales = NominaDetalleUno::whereIn('fk_nominaperiodo', $idsNominasPeriodo)
            ->where('nombre', 'like', 'BONIFICACION%')
            ->whereIn('fk_nomina_cuenta_tipo', [7, 8])
            ->get();


        $ingreAgrupados = $ingresosAdicionales->groupBy('fk_nomina_cuenta_tipo')->map(function ($data, $tipoCuenta) {
            return [
                'tipo_nomina_cuenta' => $tipoCuenta == 7 ? 'BonificacionS' : 'BonificacionNS',
                'valor' => $data->sum('valor_categoria'),
            ];
        })->values();

        return $ingreAgrupados;
    }

    /**
     * 
     * Obtenemos el auxilio del mes para la construcción principamente 
     * del json de devengados de la nomina enviada por parámetro.
     *
     * @return collect
     */
    public function auxilio(Nomina $nomina)
    {
        $idsNominasPeriodo = $nomina->nominaperiodos()->pluck('id');
        $ingresosAdicionales = NominaDetalleUno::whereIn('fk_nominaperiodo', $idsNominasPeriodo)
            ->where('nombre', 'like', 'AUXILIO%')
            ->get();

        return $ingresosAdicionales->sum('valor_categoria');
    }

    /**
     * 
     * Obtenemos los otros conceptos  del mes para la construcción principamente 
     * del json de devengados de la nomina enviada por parámetro.
     *
     * @return collect
     */
    public function otrosConceptos(Nomina $nomina)
    {
        $idsNominasPeriodo = $nomina->nominaperiodos()->pluck('id');
        $ingresosAdicionales = NominaDetalleUno::select('nombre', 'valor_categoria')
            ->whereIn('fk_nominaperiodo', $idsNominasPeriodo)
            ->where(function ($query) {
                $query->where('nombre', 'AUXILIO DE ESTUDIO')
                    ->orWhere('nombre', 'HONORARIOS');
            })->get();

        return $ingresosAdicionales;
    }

    /**
     * 
     * Obtenemos las comisiones  del mes para la construcción principamente 
     * del json de devengados de la nomina enviada por parámetro.
     *
     * @return collect
     */
    public function comisiones(Nomina $nomina)
    {
        $idsNominasPeriodo = $nomina->nominaperiodos()->pluck('id');
        $ingresosAdicionales = NominaDetalleUno::whereIn('fk_nominaperiodo', $idsNominasPeriodo)
            ->where('nombre', 'like', 'COMISIONES')
            ->where('fk_nomina_cuenta_tipo', 7)
            ->get();

        return $ingresosAdicionales->groupBy('fk_nomina_cuenta_tipo')->flatten()->sum('valor_categoria');
    }

    /**
     * 
     * Obtenemos las oras deducciones  del mes para la construcción principamente 
     * del json de devengados de la nomina enviada por parámetro.
     *
     * @return collect
     */
    public function otrasDeducciones(Nomina $nomina)
    {
        $idsNominasPeriodo = $nomina->nominaperiodos()->pluck('id');
        $ingresosAdicionales = NominaDetalleUno::whereIn('fk_nominaperiodo', $idsNominasPeriodo)
            ->where(function ($query) {
                $query->where('nombre', 'APORTES VOLUNTARIOS A CUENTAS AFC')
                    ->orwhere('nombre', 'APORTES VOLUNTARIOS A CUENTAS AVC')
                    ->orwhere('nombre', 'APORTES VOLUNTARIOS A FONDOS DE PENSIONES')
                    ->orwhere('nombre', 'CELULAR 320617')
                    ->orwhere('nombre', 'OTRAS DEDUCCIONES');
            })->get();

        return $ingresosAdicionales->sum('valor_categoria');
    }

    /**
     * 
     * Obtenemos la retencion en la fuente  del mes para la construcción principamente 
     * del json de devengados de la nomina enviada por parámetro.
     *
     * @return collect
     */
    public function retencionFuente(Nomina $nomina)
    {
        $idsNominasPeriodo = $nomina->nominaperiodos()->pluck('id');
        $ingresosAdicionales = NominaDetalleUno::select('nombre', 'valor_categoria')
            ->whereIn('fk_nominaperiodo', $idsNominasPeriodo)
            ->where('nombre', 'RETENCION EN LA FUENTE')
            ->sum('valor_categoria');

        return $ingresosAdicionales;
    }

    /**
     * 
     * Obtenemos los anticipos  del mes para la construcción principamente 
     * del json de devengados de la nomina enviada por parámetro.
     *
     * @return collect
     */
    public function anticipos(Nomina $nomina)
    {
        $idsNominasPeriodo = $nomina->nominaperiodos()->pluck('id');
        $anticipos = NominaDetalleUno::select('nombre', 'valor_categoria')
            ->whereIn('fk_nominaperiodo', $idsNominasPeriodo)
            ->where('nombre', 'PRESTAMO')
            ->sum('valor_categoria');

        return $anticipos;
    }

    /**
     * Método encargado de disparar el evento a la DIAN con el json a enviar sea de producción, pruebas o habilitación.
     * Tipo 1 = Entorno de producción
     * Tipo 2 = Entorno de pruebas
     * Tipo 3 = Entorno de habilitación.
     * 
     * @return respuesta de la dian
     */
    public function enviarJsonDianApi($json, $tipo = 2)
    {
        $testId = Auth::user()->empresaObj->test_nomina;

        switch ($tipo) {
            case 1:
                $url = "https://apine.efacturacadena.com/v1/ne/documentos/proceso/sincrono";
                $nominaToken = "01cff6f2-ae91-4a58-b606-fbce231dcb66";
                break;
            case 2:
                $url = "https://apine.efacturacadena.com/staging/ne/documentos/proceso/sincrono";
                $nominaToken = "42e5b496-d882-4041-97ec-e3e91750805f990ef12f-36ff-454b-b020-fb19e953c37397478011-edaf-4f66-9945-81b691a718b118213614-da33-4e7b-9c22-ce78e0d55cf0";

                break;
            case 3:
                $url = "https://apine.efacturacadena.com/staging/ne/documentos/proceso/habilitacion";
                $nominaToken = "97e77f62-eccd-4bde-937b-0521d6338fe9";
                break;
            default:
                break;
        }

        try {

            if ($tipo == 3) {
                $response = Http::withHeaders([
                    "nominaAuthorizationToken" => $nominaToken,
                    "nitAlianza" => "1128464945",
                    "Set-Test-Id" => $testId,
                ])->post($url, $json)->json();
            } else {
                $response = Http::withHeaders([
                    "nominaAuthorizationToken" => $nominaToken,
                    "nitAlianza" => "1128464945",
                ])->post($url, $json)->json();
            }

            return $response;
        } catch (Exception $e) {

            return $e->getMessage();
        }
    }


    public function xmlNominaEmitida(Nomina $nomina)
    {
        $empresa = auth()->user()->empresaObj;

        $nomina->load('persona', 'numeracionfactura');

        $numeracion = $nomina->numeracionfactura
            ->where('nomina', 1)
            ->where('empresa', $empresa->id)
            ->first();

        $numeroNomina = "{$numeracion->prefijo}{$nomina->nro}";


        $response = $this->nominaService->electronicPayrollStatus($empresa->nit, $numeroNomina);

        if ($response->statusCode == 200) {
            $xmlNomina = base64_decode($response->XMLdocument);

            return response($xmlNomina, 200, [
                'Content-Type' => 'application/xml', // use your required mime type
                'Content-Disposition' => "attachment; filename={$nomina->persona->nro_documento}.xml",
            ]);
        }

        return back()->withErrors("{$response->statusCode}. {$response->errorReason}");
    }
}
