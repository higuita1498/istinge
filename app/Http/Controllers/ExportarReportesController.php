<?php

namespace App\Http\Controllers;

use App\Categoria;
use App\Banco;
use App\Contacto;
use App\Contrato;
use App\Empresa;
use App\Model\Gastos\FacturaProveedores;
use App\Model\Gastos\Gastos;
use App\Model\Gastos\GastosCategoria;
use App\Model\Gastos\ItemsFacturaProv;
use App\Model\Ingresos\Factura;
use App\Funcion;
use App\Model\Ingresos\Ingreso;
use App\Model\Ingresos\IngresosCategoria;
use App\Model\Ingresos\IngresosFactura;
use App\Model\Ingresos\ItemsFactura;
use App\Model\Ingresos\Remision;
use App\Model\Inventario\Bodega;
use App\Model\Inventario\Inventario;
use App\Model\Inventario\ProductosBodega;
use App\Movimiento;
use App\Vendedor;
use App\Radicado;
use App\PucMovimiento;
use Illuminate\Http\Request; use Carbon\Carbon;
use App\NumeracionFactura;
use App\Model\Ingresos\NotaCredito;
use App\Model\Nomina\Nomina;
use App\Model\Nomina\PrestacionSocial;
use stdClass;
use App\Model\Nomina\Persona;
use App\Model\Nomina\NominaPeriodos;
use DB;
include_once(app_path() .'/../public/PHPExcel/Classes/PHPExcel.php');
include_once(app_path() .'/../public/Spout/Autoloader/autoload.php');
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PHPExcel; use PHPExcel_IOFactory; use PHPExcel_Style_Alignment; use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Style_NumberFormat;
use ZipArchive;
use PHPExcel_Shared_ZipArchive;
class ExportarReportesController extends Controller
{
	/**
   * Create a new controller instance.
   *
   * @return void
   */
	public function __construct()
	{
		$this->middleware('auth');
		view()->share(['seccion' => 'reportes', 'title' => 'Exportar Reportes', 'icon' =>'fas fa-chart-line']);
	}

    public function facturasElec(Request $request){
        //Acá se obtiene la información a impimir
        DB::enableQueryLog();
        //Si es remisones se ejecuta el metodo remisiones
        $comprobacionFacturas = Factura::where('factura.empresa',Auth::user()->empresa)
            ->join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->select('factura.id', 'factura.codigo', 'factura.nro','factura.cot_nro', DB::raw('c.nombre as nombrecliente'),
                    'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.empresa')
            ->where('factura.tipo', 2)
            ->where('factura.estatus',0);

            // if($comprobacionFacturas->count() >2100){
            //     return $this->bigVentas($request);
            // }

            $objPHPExcel = new PHPExcel();
            $tituloReporte = "Reporte de Facturas electrónicas desde ".$request->fecha." hasta ".$request->hasta;

            $titulosColumnas = array('Cliente', 'nit', 'fecha', 'vencimiento', 'producto','referencia','email','precio','impuesto','direccion','telefono','codigo','Cantidad');
            $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
            ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
            ->setTitle("Reporte de Facturas Pagadas")
            ->setSubject("Reporte de Facturas Pagadas")
            ->setDescription("Reporte de Facturas Pagadas")
            ->setKeywords("Reporte de Facturas Pagadas")
            ->setCategory("Reporte excel"); //Categorias
            // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
            $objPHPExcel->setActiveSheetIndex(0)
                ->mergeCells('A1:M1');
            // Se agregan los titulos del reporte
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1',$tituloReporte);
            $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ));
            $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->applyFromArray($estilo);
            $estilo =array('fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'd08f50')));
            $objPHPExcel->getActiveSheet()->getStyle('A3:M3')->applyFromArray($estilo);


            for ($i=0; $i <count($titulosColumnas) ; $i++) {

                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
            }

            $facturas = Factura::where('factura.empresa',Auth::user()->empresa)
            ->join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->select('factura.id', 'factura.codigo', 'factura.nro','factura.cot_nro', DB::raw('c.nombre as nombrecliente'),
                    'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.empresa')
            ->where('factura.tipo', 2);

            $ides=array();
            $factures=$facturas->get();

            $facturas=$facturas->OrderBy('factura.id', 'ASC')->paginate(1000000)->appends(['fechas'=>$request->fechas, 'nro'=>$request->nro, 'fecha'=>$request->fecha, 'hasta'=>$request->hasta]);

            foreach ($factures as $factura) {
                $ides[]=$factura->id;
            }

            Log::debug(DB::getQueryLog());

            $subtotal=$total=0;
            if ($ides) {
                $result=DB::table('items_factura')->whereIn('factura', $ides)->select(DB::raw("SUM((`cant`*`precio`)) as 'total', SUM((precio*(`desc`/100)*`cant`)+0)  as 'descuento', SUM((precio-(precio*(if(`desc`,`desc`,0)/100)))*(`impuesto`/100)*cant) as 'impuesto'  "))->first();
                $subtotal=$this->precision($result->total-$result->descuento);
                $total=$this->precision((float)$subtotal+$result->impuesto);
            }

            // Aquí se escribe en el archivo
            $i=4;
            foreach ($facturas as $factura) {

                if($factura->porpagar() == 0 && $factura->estatus == 1){
                    $factura->estatus = 0;
                    $factura->save();
                }
                
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($letras[0].$i, $factura->cliente()->nombre.' '.$factura->cliente()->apellidos())
                    ->setCellValue($letras[1].$i, $factura->cliente()->nit)
                    ->setCellValue($letras[2].$i, $factura->fecha)
                    ->setCellValue($letras[3].$i, $factura->vencimiento)
                    ->setCellValue($letras[4].$i, $factura->itemsFactura->first()->ref)
                    ->setCellValue($letras[5].$i, $factura->itemsFactura->first()->ref)
                    ->setCellValue($letras[6].$i, $factura->cliente()->email)
                    ->setCellValue($letras[7].$i, $factura->itemsFactura->first()->precio)
                    ->setCellValue($letras[8].$i, $factura->itemsFactura->first()->impuesto)
                    ->setCellValue($letras[9].$i, $factura->cliente()->direccion)
                    ->setCellValue($letras[10].$i, $factura->cliente()->celular)
                    ->setCellValue($letras[11].$i, $factura->codigo)
                    ->setCellValue($letras[12].$i, $factura->itemsFactura->first()->cant);
                $i++;
            }
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[3].$i, "TOTAL: ")
                ->setCellValue($letras[4].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($total));


            $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
            $objPHPExcel->getActiveSheet()->getStyle('A3:M'.$i)->applyFromArray($estilo);


            for($i = 'A'; $i <= $letras[20]; $i++){
                $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
            }

            // Se asigna el nombre a la hoja
            $objPHPExcel->getActiveSheet()->setTitle('Reporte de Facturas Pagadas');

            // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
            $objPHPExcel->setActiveSheetIndex(0);

            // Inmovilizar paneles
            $objPHPExcel->getActiveSheet(0)->freezePane('A2');
            $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
            $objPHPExcel->setActiveSheetIndex(0);
            header("Pragma: no-cache");
            header('Content-type: application/vnd.ms-excel');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Reporte_Facturas_Pagadas.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            exit;
    }

    public function facturasElectronicas(Request $request){
        //Acá se obtiene la información a impimir
        DB::enableQueryLog();

        $comprobacionFacturas = Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')
        ->select('factura.id', 'factura.codigo', 'factura.nro','factura.cot_nro', DB::raw('c.nombre as nombrecliente'),
            'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.empresa', 'factura.emitida')
        ->where('factura.tipo',2)
        ->where('factura.empresa',Auth::user()->empresa)
        ->where('emitida',$request->tipo)
        ->groupBy('factura.id');
        
        $dates = $this->setDateRequest($request);
        $comprobacionFacturas->where('fecha','>=', $dates['inicio'])->where('fecha','<=', $dates['fin']);
        if($comprobacionFacturas->count() >2100){
            return $this->bigVentas($request);
        }


        $objPHPExcel = new PHPExcel();
        $tituloReporte = "Reporte de Facturas Electrónicas desde ".$request->fecha." hasta ".$request->hasta;

        $titulosColumnas = array('Nro. Factura', 'Cliente', 'Cedula', 'Estrato', 'Municipio','Direccion','Creacion','Vencimiento','Dian','Estatus','Subtotal','Iva','Total');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte de Facturas Electrónicas")
        ->setSubject("Reporte de Facturas Electrónicas")
        ->setDescription("Reporte de Facturas Electrónicas")
        ->setKeywords("Reporte de Facturas Electrónicas")
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:M1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:M3')->applyFromArray($estilo);


        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $facturas = Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')
        ->select('factura.id', 'factura.codigo', 'factura.nro','factura.cot_nro', DB::raw('c.nombre as nombrecliente'),
            'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.empresa', 'factura.emitida')
        ->where('factura.tipo',2)
        ->where('factura.empresa',Auth::user()->empresa)
        ->where('emitida',$request->tipo)
        ->groupBy('factura.id');
        $dates = $this->setDateRequest($request);

        /*if ($request->nro>0) {
            $facturas=$facturas->where('numeracion', $request->nro);
        }*/
        if($request->input('fechas') != 8 || (!$request->has('fechas'))){
            $facturas=$facturas->where('factura.fecha','>=', $dates['inicio'])->where('factura.fecha','<=', $dates['fin']);
        }
    
        $ides=array();
        $factures=$facturas->get();
        $facturas=$facturas->OrderBy('factura.id', 'DESC')->paginate(1000000)->appends(['fechas'=>$request->fechas, 'nro'=>$request->nro, 'fecha'=>$request->fecha, 'hasta'=>$request->hasta]);

        foreach ($factures as $factura) {
            $ides[]=$factura->id;
        }

        Log::debug(DB::getQueryLog());

        $subtotal=$total=0;
        if ($ides) {
            $result=DB::table('items_factura')->whereIn('factura', $ides)->select(DB::raw("SUM((`cant`*`precio`)) as 'total', SUM((precio*(`desc`/100)*`cant`)+0)  as 'descuento', SUM((precio-(precio*(if(`desc`,`desc`,0)/100)))*(`impuesto`/100)*cant) as 'impuesto'  "))->first();
            $subtotal=$this->precision($result->total-$result->descuento);
            $total=$this->precision((float)$subtotal+$result->impuesto);
        }

        // Aquí se escribe en el archivo
        $i=4;
        $moneda = Auth::user()->empresa()->moneda;
        foreach ($facturas as $factura) {
            if($factura->porpagar() == 0 && $factura->estatus == 1){
                $factura->estatus = 0;
                $factura->save();
            }
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $factura->codigo)
                ->setCellValue($letras[1].$i, $factura->cliente()->nombre.' '.$factura->cliente()->apellidos())
                ->setCellValue($letras[2].$i, $factura->cliente()->nit)
                ->setCellValue($letras[3].$i, $factura->cliente()->estrato)
                ->setCellValue($letras[4].$i, $factura->cliente()->municipio()->nombre)
                ->setCellValue($letras[5].$i, $factura->cliente()->direccion)
                ->setCellValue($letras[6].$i, date('d-m-Y', strtotime($factura->fecha)))
                ->setCellValue($letras[7].$i, date('d-m-Y', strtotime($factura->vencimiento)))
                ->setCellValue($letras[8].$i, $factura->emitida == 1 ? 'Emitida' : 'No Emitida')
                ->setCellValue($letras[9].$i, $factura->estatus())
                ->setCellValue($letras[10].$i, $factura->total()->subtotal)
                ->setCellValue($letras[11].$i, $factura->total()->valImpuesto)
                ->setCellValue($letras[12].$i,$factura->total()->total);
            $i++;
        }
 
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[11].$i, "TOTAL: ")
            ->setCellValue($letras[12].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($total));

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:M'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Facturas Electrónicas');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Facturas_Electronicas.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function facturasEstandar(Request $request){
        //Acá se obtiene la información a impimir
        DB::enableQueryLog();

        $comprobacionFacturas = Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')
        ->select('factura.id', 'factura.codigo', 'factura.nro','factura.cot_nro', DB::raw('c.nombre as nombrecliente'),
            'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.empresa', 'factura.emitida')
        ->where('factura.tipo',1)
        ->where('factura.empresa',Auth::user()->empresa)
        ->groupBy('factura.id');
        
        $dates = $this->setDateRequest($request);
        $comprobacionFacturas->where('fecha','>=', $dates['inicio'])->where('fecha','<=', $dates['fin']);
        if($comprobacionFacturas->count() >2100){
            return $this->bigVentas($request);
        }


        $objPHPExcel = new PHPExcel();
        $tituloReporte = "Reporte de Facturas Estándar desde ".$request->fecha." hasta ".$request->hasta;

        $titulosColumnas = array('Nro. Factura', 'Cliente', 'Cedula', 'Estrato', 'Municipio', 'Creacion','Vencimiento','Estatus','Subtotal','Iva','Total');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte de Facturas Estándar")
        ->setSubject("Reporte de Facturas Estándar")
        ->setDescription("Reporte de Facturas Estándar")
        ->setKeywords("Reporte de Facturas Estándar")
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:L1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:K3')->applyFromArray($estilo);


        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $facturas = Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')
        ->select('factura.id', 'factura.codigo', 'factura.nro','factura.cot_nro', DB::raw('c.nombre as nombrecliente'),
            'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.empresa', 'factura.emitida')
        ->where('factura.tipo',1)
        ->where('factura.empresa',Auth::user()->empresa)
        ->groupBy('factura.id');
        $dates = $this->setDateRequest($request);

        /*if ($request->nro>0) {
            $facturas=$facturas->where('numeracion', $request->nro);
        }*/
        if($request->input('fechas') != 8 || (!$request->has('fechas'))){
            $facturas=$facturas->where('factura.fecha','>=', $dates['inicio'])->where('factura.fecha','<=', $dates['fin']);
        }
    
        $ides=array();
        $factures=$facturas->get();
        $facturas=$facturas->OrderBy('factura.id', 'DESC')->paginate(1000000)->appends(['fechas'=>$request->fechas, 'nro'=>$request->nro, 'fecha'=>$request->fecha, 'hasta'=>$request->hasta]);

        foreach ($factures as $factura) {
            $ides[]=$factura->id;
        }

        Log::debug(DB::getQueryLog());

        $subtotal=$total=0;
        if ($ides) {
            $result=DB::table('items_factura')->whereIn('factura', $ides)->select(DB::raw("SUM((`cant`*`precio`)) as 'total', SUM((precio*(`desc`/100)*`cant`)+0)  as 'descuento', SUM((precio-(precio*(if(`desc`,`desc`,0)/100)))*(`impuesto`/100)*cant) as 'impuesto'  "))->first();
            $subtotal=$this->precision($result->total-$result->descuento);
            $total=$this->precision((float)$subtotal+$result->impuesto);
        }

        // Aquí se escribe en el archivo
        $i=4;
        $moneda = Auth::user()->empresa()->moneda;
        foreach ($facturas as $factura) {
            if($factura->porpagar() == 0 && $factura->estatus == 1){
                $factura->estatus = 0;
                $factura->save();
            }
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $factura->codigo)
                ->setCellValue($letras[1].$i, $factura->cliente()->nombre.' '.$factura->cliente()->apellidos())
                ->setCellValue($letras[2].$i, $factura->cliente()->nit)
                ->setCellValue($letras[3].$i, $factura->cliente()->estrato)
                ->setCellValue($letras[4].$i, $factura->cliente()->municipio()->nombre)
                ->setCellValue($letras[5].$i, date('d-m-Y', strtotime($factura->fecha)))
                ->setCellValue($letras[6].$i, date('d-m-Y', strtotime($factura->vencimiento)))
                ->setCellValue($letras[7].$i, $factura->estatus())
                ->setCellValue($letras[8].$i, $factura->total()->subtotal)
                ->setCellValue($letras[9].$i, $factura->total()->valImpuesto)
                ->setCellValue($letras[10].$i,$factura->total()->total);
            $i++;
        }
 
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[9].$i, "TOTAL: ")
            ->setCellValue($letras[10].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($total));

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:K'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Facturas Estándar');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Facturas_Estandar.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

	public function ventas(Request $request){
        //Acá se obtiene la información a impimir
        DB::enableQueryLog();
        //Si es remisones se ejecuta el metodo remisiones
        if ($request->nro == 'remisiones'){


            $this->remisiones($request);


        }else{

            $comprobacionFacturas = Factura::where('factura.empresa',Auth::user()->empresa)
            ->leftjoin('contracts', 'contracts.id', '=', 'factura.contrato_id')
            ->join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->join('items_factura as if', 'factura.id', '=', 'if.factura')
            ->join('ingresos_factura as ig', 'factura.id', '=', 'ig.factura')
            ->join('ingresos as i', 'ig.ingreso', '=', 'i.id')
            ->select('factura.id', 'factura.codigo', 'factura.nro','factura.cot_nro', DB::raw('c.nombre as nombrecliente'),
                    'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.empresa', 'i.fecha as pagada')
            ->whereIn('factura.tipo', [1,2])
            ->where('factura.estatus',0);
            $dates = $this->setDateRequest($request);
            $comprobacionFacturas->where('i.fecha','>=', $dates['inicio'])->where('i.fecha','<=', $dates['fin']);


            if(Auth::user()->rol > 1 && auth()->user()->rol == 8){
                $comprobacionFacturas->whereIn('i.cuenta', Auth::user()->cuentas());
            }

            if($request->grupo){
                $comprobacionFacturas=$comprobacionFacturas->where('contracts.grupo_corte', $request->grupo);
            }

            if($comprobacionFacturas->count() >2100){
                return $this->bigVentas($request);
            }


            $objPHPExcel = new PHPExcel();
            $tituloReporte = "Reporte de Facturas Pagadas desde ".$request->fecha." hasta ".$request->hasta;

            $titulosColumnas = array('Nro. Factura', 'Cliente', 'Pagada', 'Caja','Estado','Total');
            $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
            ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
            ->setTitle("Reporte de Facturas Pagadas")
            ->setSubject("Reporte de Facturas Pagadas")
            ->setDescription("Reporte de Facturas Pagadas")
            ->setKeywords("Reporte de Facturas Pagadas")
            ->setCategory("Reporte excel"); //Categorias
            // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
            $objPHPExcel->setActiveSheetIndex(0)
                ->mergeCells('A1:E1');
            // Se agregan los titulos del reporte
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1',$tituloReporte);
            $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ));
            $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->applyFromArray($estilo);
            $estilo =array('fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'd08f50')));
            $objPHPExcel->getActiveSheet()->getStyle('A3:F3')->applyFromArray($estilo);


            for ($i=0; $i <count($titulosColumnas) ; $i++) {

                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
            }

            $facturas = Factura::where('factura.empresa',Auth::user()->empresa)
            ->leftjoin('contracts', 'contracts.id', '=', 'factura.contrato_id')
            ->join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->join('ingresos_factura as ig', 'factura.id', '=', 'ig.factura')
            ->join('ingresos as i', 'ig.ingreso', '=', 'i.id')
            ->select('factura.id', 'factura.codigo', 'factura.nro','factura.cot_nro', DB::raw('c.nombre as nombrecliente'),
                    'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.empresa', 'i.fecha as pagada', 'i.cuenta')
            ->whereIn('factura.tipo', [1,2])
            ->where('factura.estatus','<>',2);
            $dates = $this->setDateRequest($request);

            /*if ($request->nro>0) {
                $facturas=$facturas->where('numeracion', $request->nro);
            }*/
            if($request->input('fechas') != 8 || (!$request->has('fechas'))){
                $facturas=$facturas->where('i.fecha','>=', $dates['inicio'])->where('i.fecha','<=', $dates['fin']);
            }
            if($request->caja){
                $facturas=$facturas->where('i.cuenta',$request->caja);
            }else{
                if(Auth::user()->rol > 1 && auth()->user()->rol == 8){
                    $facturas=$facturas->whereIn('i.cuenta', Auth::user()->cuentas());
                }
            }
            if($request->grupo){
                $facturas=$facturas->where('contracts.grupo_corte', $request->grupo);
            }
            $ides=array();
            $factures=$facturas->get();
            $facturas=$facturas->OrderBy('factura.id', 'DESC')->paginate(1000000)->appends(['fechas'=>$request->fechas, 'nro'=>$request->nro, 'fecha'=>$request->fecha, 'hasta'=>$request->hasta]);

            foreach ($factures as $factura) {
                $ides[]=$factura->id;
            }

            Log::debug(DB::getQueryLog());

            $subtotal=$total=0;
            if ($ides) {
                $result=DB::table('items_factura')->whereIn('factura', $ides)->select(DB::raw("SUM((`cant`*`precio`)) as 'total', SUM((precio*(`desc`/100)*`cant`)+0)  as 'descuento', SUM((precio-(precio*(if(`desc`,`desc`,0)/100)))*(`impuesto`/100)*cant) as 'impuesto'  "))->first();
                $subtotal=$this->precision($result->total-$result->descuento);
                $total=$this->precision((float)$subtotal+$result->impuesto);
            }

            // Aquí se escribe en el archivo
            $i=4;
            foreach ($facturas as $factura) {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($letras[0].$i, $factura->codigo)
                    ->setCellValue($letras[1].$i, $factura->cliente()->nombre.' '.$factura->cliente()->apellidos())
                    ->setCellValue($letras[2].$i, date('d-m-Y', strtotime($factura->pagada)))
                    ->setCellValue($letras[3].$i, $factura->banco()->nombre)
                    ->setCellValue($letras[4].$i, $factura->estatus())
                    ->setCellValue($letras[5].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($factura->total()->total));
                $i++;
            }
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[4].$i, "TOTAL: ")
                ->setCellValue($letras[5].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($total));


            $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
            $objPHPExcel->getActiveSheet()->getStyle('A3:F'.$i)->applyFromArray($estilo);


            for($i = 'A'; $i <= $letras[20]; $i++){
                $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
            }

            // Se asigna el nombre a la hoja
            $objPHPExcel->getActiveSheet()->setTitle('Reporte de Facturas Pagadas');

            // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
            $objPHPExcel->setActiveSheetIndex(0);

            // Inmovilizar paneles
            $objPHPExcel->getActiveSheet(0)->freezePane('A2');
            $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
            $objPHPExcel->setActiveSheetIndex(0);
            header("Pragma: no-cache");
            header('Content-type: application/vnd.ms-excel');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Reporte_Facturas_Pagadas.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            exit;

        }

    }

    private function bigVentas(&$request)
    {
        //
        ini_set('memory_limit', '1024M');set_time_limit(0);
        //
        // Borders
        $borderT = (new BorderBuilder())
            ->setBorderTop(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
            ->build();
        $borderB = (new BorderBuilder())
            ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
            ->build();
        $borderR = (new BorderBuilder())
            ->setBorderRight(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
            ->build();
        $borderL = (new BorderBuilder())
            ->setBorderLeft(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
            ->build();
        //
        //Styles
        $styleHeader = (new StyleBuilder())
            ->setFontBold()
            ->setBorder($borderT)
            ->setFontName('Times New Roman')
            ->setFontSize(11)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setBackgroundColor(Color::ORANGE)
            ->build();
        $style = (new StyleBuilder())
            ->setFontName('Times New Roman')
            ->setFontSize(11)
            ->setCellAlignment(CellAlignment::CENTER)
            ->build();
        $styleR = (new StyleBuilder())
            ->setBorder($borderR)
            ->setFontName('Times New Roman')
            ->setFontSize(11)
            ->setCellAlignment(CellAlignment::CENTER)
            ->build();
        $styleB = (new StyleBuilder())
            ->setFontBold()
            ->setBorder($borderB)
            ->setFontName('Times New Roman')
            ->setFontSize(11)
            ->setCellAlignment(CellAlignment::CENTER)
            ->build();
        $styleL = (new StyleBuilder())
            ->setBorder($borderL)
            ->setFontName('Times New Roman')
            ->setFontSize(11)
            ->setCellAlignment(CellAlignment::CENTER)
            ->build();
        //
        $facturas = Factura::where('empresa',Auth::user()->empresa)
            ->where('tipo','<>',2)
            ->where('estatus','<>',2);
        $dates = $this->setDateRequest($request, true);
        $facturas = $facturas->where('fecha','>=', $dates['inicio'])->where('fecha','<=', $dates['fin'])->get();

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToBrowser('Reportde_ventas_total.xlsx');
        $cells = [
            WriterEntityFactory::createCell('Nro. Factura', $styleHeader),
            WriterEntityFactory::createCell('Cliente',  $styleHeader),
            WriterEntityFactory::createCell('Creacion',  $styleHeader),
            WriterEntityFactory::createCell('Vencimiento',  $styleHeader),
            WriterEntityFactory::createCell('Subtotal',  $styleHeader),
            WriterEntityFactory::createCell('IVA',  $styleHeader),
            WriterEntityFactory::createCell('Retencion',  $styleHeader),
            WriterEntityFactory::createCell('Total',  $styleHeader),
        ];
        $singleRow = WriterEntityFactory::createRow($cells);
        $writer->addRow($singleRow);
        $rows = array();
        $subtotal = 0;
        $total = 0;
        foreach ($facturas as $factura){
            $ides[] = $factura->id;
            $cells = [
                WriterEntityFactory::createCell($factura->codigo, $styleL),
                WriterEntityFactory::createCell("te", $style),
                WriterEntityFactory::createCell(date('d-m-Y', strtotime($factura->fecha)), $style),
                WriterEntityFactory::createCell(date('d-m-Y', strtotime($factura->vencimiento)), $style),
                WriterEntityFactory::createCell(Auth::user()->empresa()->moneda ." ". Funcion::Parsear($factura->total()->subsub), $style),
                WriterEntityFactory::createCell(Auth::user()->empresa()->moneda ." ". Funcion::Parsear($factura->impuestos_totales()), $style),
                WriterEntityFactory::createCell(Auth::user()->empresa()->moneda ." ". Funcion::Parsear($factura->retenido(true)), $style),
                WriterEntityFactory::createCell(Auth::user()->empresa()->moneda." ".Funcion::Parsear($factura->total()->total), $styleR),
            ];
            $rows[] = WriterEntityFactory::createRow($cells);
        }
        $writer->addRows($rows);

        $result = DB::table('items_factura')->whereIn('factura', $ides)->select(DB::raw("SUM((`cant`*`precio`)) as 'total', SUM((precio*(`desc`/100)*`cant`)+0)  as 'descuento', SUM((precio-(precio*(if(`desc`,`desc`,0)/100)))*(`impuesto`/100)*cant) as 'impuesto'  "))->first();
        $subtotal = $this->precision($result->total-$result->descuento);
        $total = $this->precision((float)$subtotal+$result->impuesto);
        $cells = [
            WriterEntityFactory::createCell('.', $styleB),
            WriterEntityFactory::createCell('.', $styleB),
            WriterEntityFactory::createCell('.', $styleB),
            WriterEntityFactory::createCell('.', $styleB),
            WriterEntityFactory::createCell('.', $styleB),
            WriterEntityFactory::createCell('TOTAL:', $styleB),
            WriterEntityFactory::createCell(Auth::user()->empresa()->moneda." ".Funcion::Parsear($subtotal), $styleB),
            WriterEntityFactory::createCell(Auth::user()->empresa()->moneda." ".Funcion::Parsear($total), $styleB),
        ];
        $singleRow = WriterEntityFactory::createRow($cells);
        $writer->addRow($singleRow);
        $writer->close();

    }

    private function remisiones($request)
    {

        $objPHPExcel = new PHPExcel();
        $tituloReporte = "Reporte de Ventas-Remisiones desde ".$request->fecha." hasta ".$request->hasta;

        $titulosColumnas = array('Numero', 'Cliente', 'Creacion', 'Antes de Impuestos', 'Despues de Impuestos');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Ventas Remisiones") // Titulo
        ->setSubject("Reporte Excel Ventas Remisiones") //Asunto
        ->setDescription("Reporte de Ventas Remisiones") //Descripci���n
        ->setKeywords("reporte Ventas Remisiones") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:E1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:E3')->applyFromArray($estilo);


        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $dates = $this->setDateRequest($request);

        $campos=array('', 'remisiones.id', 'nombrecliente', 'remisiones.fecha', 'remisiones.vencimiento', 'total', 'pagado', 'porpagar', 'remisiones.estatus');
        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';

        $facturas=Remision::join('contactos as c', 'remisiones.cliente', '=', 'c.id')
            ->join('items_remision as if', 'remisiones.id', '=', 'if.remision')
            ->select('remisiones.id', 'remisiones.nro', DB::raw('c.nombre as nombrecliente'), 'remisiones.cliente',
                'remisiones.fecha', 'remisiones.vencimiento', 'remisiones.estatus',
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                DB::raw('((Select SUM(pago) from ingresosr_remisiones where remision=remisiones.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where remision=remisiones.id)) as pagado'),
                DB::raw('(SUM(
          (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) -  ((Select SUM(pago) from ingresosr_remisiones where remision=remisiones.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where remision=remisiones.id)) )    as porpagar'))
            ->whereIn('estatus', [0, 1])
            ->where('remisiones.empresa',Auth::user()->empresa);

        if($request->input('fechas') != 8 || (!$request->has('fechas'))){
            $facturas=$facturas->where('fecha','>=', $dates['inicio'])->where('fecha','<=', $dates['fin']);
        }
        $appends=array('orderby'=>$request->orderby, 'order'=>$request->order);
        $facturas=$facturas->groupBy('if.remision');
        $facturas=$facturas->OrderBy($orderby, $order)->get();
        $totales = $this->totalRemisiones($dates);

        $i=4;
        foreach ($facturas as $factura) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $factura->nro)
                ->setCellValue($letras[1].$i, $factura->cliente()->nombre.' '.$factura->cliente()->apellidos())
                ->setCellValue($letras[2].$i, date('d-m-Y', strtotime($factura->fecha)))
                ->setCellValue($letras[3].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($factura->total()->subsub))
                ->setCellValue($letras[4].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($factura->total()->total));
            $i++;
        }
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[2].$i, "TOTAL: ")
            ->setCellValue($letras[3].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($totales['subtotal']))
            ->setCellValue($letras[4].$i, Auth::user()->empresa()->moneda. " ".Funcion::Parsear($totales['total']));


        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:E'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de Ventas-Remisiones');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Ventas_Remisiones.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;


    }

    public function contactos($tipo=2, Request $request){
        $objPHPExcel = new PHPExcel();
        $tituloReporte = "Reporte de Contactos de ".Auth::user()->empresa()->nombre;
        $titulosColumnas = array('Nombres', 'Tipo de identificacion', 'Identificacion','Pais','Departamento','Municipio',
            'Codigo postal','Telefono', 'Telefono 2', 'Fax', 'Celular', 'Direccion','Ciudad', 'Correo Electronico', 'Observaciones',
            'Tipo de Empresa', 'Tipo de Contacto', 'Vendedor', 'DV', 'Tipo persona', 'Responsabilidad');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Contactos") // Titulo
        ->setSubject("Reporte Excel Contactos") //Asunto
        ->setDescription("Reporte de Contactos") //Descripci���n
        ->setKeywords("reporte Contactos") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:D1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        // Titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A2:C2');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2','Fecha '.date('d-m-Y')); // Titulo del reporte

        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:U3')->applyFromArray($estilo);

        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:U3')->applyFromArray($estilo);


        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $i=4;
        $letra=0;
        $dates = $this->setDateRequest($request);
        $contactos = Contacto::where('empresa',Auth::user()->empresa)
            ->where('created_at', '>=', $dates['inicio'])
            ->where('created_at', '<=', $dates['fin'])
            ->get();
        if ($tipo<>2) {
            $contactos=$contactos->whereIn('tipo_contacto',[$tipo,2]);
        }
        $empresa        = Empresa::find(Auth::user()->empresa);
        foreach ($contactos as $contacto) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $contacto->nombre)
                ->setCellValue($letras[1].$i, $contacto->tip_iden())
                ->setCellValue($letras[2].$i, $contacto->nit)
                ->setCellValue($letras[3].$i, $contacto->paisName)
                ->setCellValue($letras[4].$i, $contacto->departamentoName)
                ->setCellValue($letras[5].$i, $contacto->municipioName)
                ->setCellValue($letras[6].$i, $contacto->cod_municipio)
                ->setCellValue($letras[7].$i, $contacto->telefono1)
                ->setCellValue($letras[8].$i, $contacto->telefono2)
                ->setCellValue($letras[9].$i, $contacto->fax)
                ->setCellValue($letras[10].$i, $contacto->celular)
                ->setCellValue($letras[11].$i, $contacto->direccion)
                ->setCellValue($letras[12].$i, $contacto->ciudad)
                ->setCellValue($letras[13].$i, $contacto->email)
                ->setCellValue($letras[14].$i, $contacto->observaciones)
                ->setCellValue($letras[15].$i, $contacto->tipo_empresa())
                ->setCellValue($letras[16].$i, $contacto->tipo_contacto())
                ->setCellValue($letras[17].$i, $contacto->vendedor())
                ->setCellValue($letras[18].$i, $contacto->dv)
                ->setCellValue($letras[19].$i, $contacto->tipo_persona())
                ->setCellValue($letras[20].$i, $contacto->responsableIva());
            $i++;
        }

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:U'.$i)->applyFromArray($estilo);

        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de Contactos');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A5');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Contactos.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    private function totalRemisiones($dates)
    {
        $facturas=Remision::join('contactos as c', 'remisiones.cliente', '=', 'c.id')
            ->join('items_remision as if', 'remisiones.id', '=', 'if.remision')
            ->select('remisiones.id', 'remisiones.nro', DB::raw('c.nombre as nombrecliente'), 'remisiones.cliente',
                'remisiones.fecha', 'remisiones.vencimiento', 'remisiones.estatus',
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                DB::raw('((Select SUM(pago) from ingresosr_remisiones where remision=remisiones.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where remision=remisiones.id)) as pagado'),
                DB::raw('(SUM(
          (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) -  ((Select SUM(pago) from ingresosr_remisiones where remision=remisiones.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where remision=remisiones.id)) )    as porpagar'))
            ->where('remisiones.empresa',Auth::user()->empresa)
            ->where('fecha','>=', $dates['inicio'])
            ->where('fecha','<=', $dates['fin'])
            ->whereIn('estatus', [0, 1])
            ->groupBy('if.remision')
            ->get();

        $totales = array(
            'total' => 0,
            'subtotal' => 0,
        );

        foreach ($facturas as $factura) {
            $totales['total']+= $factura->total()->total;
            $totales['subtotal']+= $factura->total()->subsub;
        }

        return $totales;
    }

    public function ventasItem(Request $request){
        $objPHPExcel = new PHPExcel();
        if($request->input('fechas') == 8){
            $tituloReporte = "Reporte de Ventas de ítem";
        }else{
            $tituloReporte = "Reporte de Ventas de ítem desde ".$request->fecha." hasta ".$request->hasta;
        }

        $titulosColumnas = array('Ítem', 'Referencia', 'Numero de ítems', 'Antes de Impuestos', 'Despues de Impuestos');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Ventas De Item") // Titulo
        ->setSubject("Reporte Excel Ventas De Item") //Asunto
        ->setDescription("Reporte de Ventas de Item") //Descripci���n
        ->setKeywords("reporte Ventas de ítem") //Etiquetas
        ->setCategory("Reporte excel de ítem"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:D1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:E3')->applyFromArray($estilo);


        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        //Acá se obtiene la información a impimir

        //Si no hay fecha establecida dentro de la url se establece desde el 1 al 30/31 del mes actual
        if (!$request->fecha) {
            $month = date('m');
            $year = date('Y');
            $day = date("d", mktime(0,0,0, $month+1, 0, $year));
            $fin= date('Y-m-d', mktime(0,0,0, $month, $day, $year));
            $request->hasta=date('d-m-Y', mktime(0,0,0, $month, $day, $year));
            $month = date('m');
            $year = date('Y');
            $inicio=  date('Y-m-d', mktime(0,0,0, $month, 1, $year));
            $request->fecha=date('d-m-Y', mktime(0,0,0, $month, 1, $year));
        }
        else{
            if($request->input('fechas') != 8 || (!$request->has('fechas'))){
                $inicio = date('Y-m-d', strtotime($request->fecha));
                $fin    = date('Y-m-d', strtotime($request->hasta));
            }else{
                $inicio = Carbon::now()->subYear('4')->format('Y-m-d');
                $fin    = Carbon::now()->format('Y-m-d');
            }

        }
        $user = Auth::user()->empresa;
        //Se cuenta cuantas veces se repiten las facturas con un mismo producto
        $sqlRepeticiones =
            "SELECT SUM(cant) as rep, producto FROM items_factura WHERE items_factura.factura IN
	            (
		            SELECT id FROM factura
			            WHERE factura.fecha >= '$inicio'
				            AND factura.fecha <= '$fin'
				            AND factura.empresa = '$user'
				            AND factura.tipo != 2
			                ORDER BY factura.id DESC
	            )
            GROUP BY (producto)";

        $repeticones = DB::select($sqlRepeticiones);
        //dd($repeticones);
        //Subconsulta para obtener todos los productos según su item factura
        $productos = DB::table('inventario')
            ->select('id', 'producto', 'ref', 'precio', DB::raw('precio+(precio*(impuesto/100)) as total'))
            ->whereIn('id', function ($query) use ($inicio, $fin, $user){
                $query->select('producto')
                    ->from(with(new ItemsFactura)->getTable())
                    ->whereIn('factura', function ($sql) use ($inicio, $fin, $user){
                        $sql->select('id')
                            ->from(with(new Factura)->getTable())
                            ->where('fecha', ">=", $inicio)
                            ->where('fecha', "<=", $fin)
                            ->where('empresa', $user)
                            ->where('tipo','!=', 2);
                    });
            })->get();

        //Subconsulta para determinar todos los precios de los productos
        $productosTotal = DB::table('inventario')
            ->select('precio', DB::raw('precio+(precio*(impuesto/100)) as total'))
            ->whereIn('id', function ($query) use ($inicio, $fin, $user){
                $query->select('producto')
                    ->from(with(new ItemsFactura)->getTable())
                    ->whereIn('factura', function ($sql) use ($inicio, $fin, $user){
                        $sql->select('id')
                            ->from(with(new Factura)->getTable())
                            ->where('fecha', ">=", $inicio)
                            ->where('fecha', "<=", $fin)
                            ->where('empresa', $user)
                            ->where('tipo', 1);
                    });
            })->get();

        //Se agregan las veces que se repiten y se determina el gran total
        $total = 0;
        $subtotal = 0;
        foreach ( $productos as $key => $producto ){


            $producto->rep = $repeticones[$i]->rep;
            $producto->precio = $producto->precio * $producto->rep;
            $producto->total = $producto->total * $producto->rep;
            $total += $producto->total;
            $subtotal += $producto->precio;
        }
        foreach ($productosTotal as $productoTotal){
            $total      += $productoTotal->total;
            $subtotal   += $productoTotal->precio;
        }

        // Aquí se escribe en el archivo
        $i=4;

        foreach ($productos as $producto) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $producto->producto)
                ->setCellValue($letras[1].$i, $producto->ref)
                ->setCellValue($letras[2].$i, $producto->rep)
                ->setCellValue($letras[3].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($producto->precio))
                ->setCellValue($letras[4].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($producto->total));
            $i++;
        }
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[2].$i, "TOTAL: ")
            ->setCellValue($letras[3].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($subtotal))
            ->setCellValue($letras[4].$i, Auth::user()->empresa()->moneda. " ".Funcion::Parsear($total));


        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:E'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de Ventas de ítem');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Ventas_Item.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function ventasCliente(Request $request)
    {

        $objPHPExcel = new PHPExcel();
        if($request->input('fechas') == 8){
            $tituloReporte = "Reporte de Ventas por cliente";
        }else{
            $tituloReporte = "Reporte de Ventas por cliente desde ".$request->fecha." hasta ".$request->hasta;
        }

        $titulosColumnas = array('Cliente', 'Numero de facturas', 'Antes de Impuestos', 'Despues de Impuestos');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Ventas Por Cliente") // Titulo
        ->setSubject("Reporte Excel Ventas Por Cliente") //Asunto
        ->setDescription("Reporte de Ventas Por CLiente") //Descripci���n
        ->setKeywords("reporte Ventas por cliente") //Etiquetas
        ->setCategory("Reporte excel de ventas por clientes"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:D1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:D3')->applyFromArray($estilo);


        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }


        //Si no hay fecha establecida dentro de la url se establece desde el 1 al 30/31 del mes actual
        if (!$request->fecha) {
            $month = date('m');
            $year = date('Y');
            $day = date("d", mktime(0,0,0, $month+1, 0, $year));
            $fin= date('Y-m-d', mktime(0,0,0, $month, $day, $year));
            $request->hasta=date('d-m-Y', mktime(0,0,0, $month, $day, $year));
            $month = date('m');
            $year = date('Y');
            $inicio=  date('Y-m-d', mktime(0,0,0, $month, 1, $year));
            $request->fecha=date('d-m-Y', mktime(0,0,0, $month, 1, $year));
        }
        else{

            if($request->input('fechas') != 8 || (!$request->has('fechas'))){
                $inicio = date('Y-m-d', strtotime($request->fecha));
                $fin    = date('Y-m-d', strtotime($request->hasta));
            }else{
                $inicio = Carbon::now()->subYear('10')->format('Y-m-d');
                $fin    = Carbon::now()->format('Y-m-d');
            }

        }

        $user = Auth::user()->empresa;

        $sqlNroFacturaCliente = "SELECT factura.id as factura, contactos.id, contactos.nombre FROM factura
	                                INNER JOIN  contactos ON factura.cliente = contactos.id
                                    WHERE factura.fecha >= '$inicio'
                                    AND factura.fecha <= '$fin'
                                    AND factura.empresa = '$user'
                                    AND factura.tipo = 1";


        $datoFacturas = DB::table('items_factura')
            ->select('id', DB::raw('SUM(precio) as precio'), 'factura', DB::raw('COUNT(factura)'),
                DB::raw('SUM(precio)+(SUM(precio)*(impuesto/100)) as total'))
            ->whereIn('factura', function ($query) use ($inicio, $fin, $user){
                $query->select('id')
                    ->from(with(new Factura)->getTable())
                    ->where('fecha', ">=", $inicio)
                    ->where('fecha', "<=", $fin)
                    ->where('empresa', $user)
                    ->where('tipo', 1)
                    ->whereIn('cliente', function ($sql) use ($inicio, $fin, $user){
                        $sql->select('id')
                            ->from(with(new Contacto)->getTable())
                            ->whereIn('id', function ($sqlQuery) use ($inicio, $fin, $user){
                                $sqlQuery->select('cliente')
                                    ->from(with(new Contacto)->getTable())
                                    ->where('fecha', ">=", $inicio)
                                    ->where('fecha', "<=", $fin)
                                    ->where('empresa', $user)
                                    ->where('tipo', 1);
                            });
                    });
            })
            ->groupby('factura')
            ->get();

        $nroFacturas = DB::select($sqlNroFacturaCliente);

        $i = 0;
        $clientes= array();
        $subtotal = 0;
        $total= 0;
        foreach ($datoFacturas as $datoFactura){

            if(!isset($clientes[$nroFacturas[$i]->id])){
                $clientes[$nroFacturas[$i]->id]['nombre'] = $nroFacturas[$i]->nombre;
                $clientes[$nroFacturas[$i]->id]['subtotal'] = $datoFactura->precio;
                $clientes[$nroFacturas[$i]->id]['total'] = $datoFactura->total;
                $clientes[$nroFacturas[$i]->id]['rep'] = 1;
                $subtotal += $datoFactura->precio;
                $total += $datoFactura->total;

            }else{

                $clientes[$nroFacturas[$i]->id]['subtotal']+= $datoFactura->precio;
                $clientes[$nroFacturas[$i]->id]['total'] += $datoFactura->total;
                $clientes[$nroFacturas[$i]->id]['rep']+=1;
                $subtotal+= $datoFactura->precio;
                $total+= $datoFactura->total;
            }

            $i++;
        }


        $i=4;
        foreach ($clientes as $cliente) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $cliente['nombre'])
                ->setCellValue($letras[1].$i, $cliente['rep'])
                ->setCellValue($letras[2].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($cliente['subtotal']))
                ->setCellValue($letras[3].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($cliente['total']));
            $i++;
        }
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[2].$i, "Subtotal: " . Auth::user()->empresa()->moneda." ".Funcion::Parsear($subtotal))
            ->setCellValue($letras[3].$i, "Total: " . Auth::user()->empresa()->moneda. " ".Funcion::Parsear($total));

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:D'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de ventas por cliente');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Ventas_Clientes.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;


    }

    public function remisionesCliente(Request $request)
    {

        $objPHPExcel = new PHPExcel();
        if($request->input('fechas') == 8){
            $tituloReporte = "Reporte de Remisiones por cliente ";
        }else{
            $tituloReporte = "Reporte de Remisiones por cliente desde ".$request->fecha." hasta ".$request->hasta;
        }

        $titulosColumnas = array('Cliente', 'Numero de facturas', 'Antes de Impuestos', 'Despues de Impuestos');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Ventas Por Cliente") // Titulo
        ->setSubject("Reporte Excel Ventas Por Cliente") //Asunto
        ->setDescription("Reporte de Ventas Por CLiente") //Descripci���n
        ->setKeywords("reporte Ventas por cliente") //Etiquetas
        ->setCategory("Reporte excel de ventas por clientes"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:D1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:D3')->applyFromArray($estilo);


        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }


        //Si no hay fecha establecida dentro de la url se establece desde el 1 al 30/31 del mes actual
        if (!$request->fecha) {
            $month = date('m');
            $year = date('Y');
            $day = date("d", mktime(0,0,0, $month+1, 0, $year));
            $fin= date('Y-m-d', mktime(0,0,0, $month, $day, $year));
            $request->hasta=date('d-m-Y', mktime(0,0,0, $month, $day, $year));
            $month = date('m');
            $year = date('Y');
            $inicio=  date('Y-m-d', mktime(0,0,0, $month, 1, $year));
            $request->fecha=date('d-m-Y', mktime(0,0,0, $month, 1, $year));
        }
        else{
            if($request->input('fechas') != 8 || (!$request->has('fechas'))){
                $inicio = date('Y-m-d', strtotime($request->fecha));
                $fin    = date('Y-m-d', strtotime($request->hasta));
            }else{
                $inicio = Carbon::now()->subYear('10')->format('Y-m-d');
                $fin    = Carbon::now()->format('Y-m-d');
            }

        }

        $user = Auth::user()->empresa;

        $sqlNroFacturaCliente = "SELECT remisiones.id as remision, contactos.id, contactos.nombre FROM remisiones
	                                INNER JOIN  contactos ON remisiones.cliente = contactos.id
                                    WHERE remisiones.fecha >= '$inicio'
                                    AND remisiones.fecha <= '$fin'
                                    AND remisiones.empresa = '$user'
                                    AND remisiones.estatus IN (0, 1)
                                    AND remisiones.documento = 1";


        $datoRemisiones = DB::table('items_remision')
            ->select('id', DB::raw('SUM(precio) as precio'), 'remision', DB::raw('COUNT(remision)'),
                DB::raw('SUM(precio)+(SUM(precio)*(impuesto/100)) as total'))
            ->whereIn('remision', function ($query) use ($inicio, $fin, $user){
                $query->select('id')
                    ->from(with(new Remision)->getTable())
                    ->where('fecha', ">=", $inicio)
                    ->where('fecha', "<=", $fin)
                    ->where('empresa', $user)
                    ->where('documento', 1)
                    ->whereIn('estatus', [0, 1])
                    ->whereIn('cliente', function ($sql) use ($inicio, $fin, $user){
                        $sql->select('id')
                            ->from(with(new Contacto)->getTable())
                            ->whereIn('id', function ($sqlQuery) use ($inicio, $fin, $user){
                                $sqlQuery->select('cliente')
                                    ->from(with(new Contacto)->getTable())
                                    ->where('fecha', ">=", $inicio)
                                    ->where('fecha', "<=", $fin)
                                    ->where('empresa', $user)
                                    ->where('documento','=', 1);
                            });
                    });
            })
            ->groupby('remision')
            ->get();

        $nroRemisiones = DB::select($sqlNroFacturaCliente);


        $i = 0;
        $clientes= array();
        $subtotal = 0;
        $total= 0;
        foreach ($datoRemisiones as $datoRemision){

            if(!isset($clientes[$nroRemisiones[$i]->id])){
                $clientes[$nroRemisiones[$i]->id]['nombre'] = $nroRemisiones[$i]->nombre;
                $clientes[$nroRemisiones[$i]->id]['id'] = $nroRemisiones[$i]->id;
                $clientes[$nroRemisiones[$i]->id]['subtotal'] = $datoRemision->precio;
                $clientes[$nroRemisiones[$i]->id]['total'] = $datoRemision->total;
                $clientes[$nroRemisiones[$i]->id]['rep'] = 1;
                $subtotal += $datoRemision->precio;
                $total += $datoRemision->total;

            }else{

                $clientes[$nroRemisiones[$i]->id]['subtotal']+= $datoRemision->precio;
                $clientes[$nroRemisiones[$i]->id]['total'] += $datoRemision->total;
                $clientes[$nroRemisiones[$i]->id]['rep']+=1;
                $subtotal+= $datoRemision->precio;
                $total+= $datoRemision->total;

            }

            $i++;
        }


        $i=4;
        foreach ($clientes as $cliente) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $cliente['nombre'])
                ->setCellValue($letras[1].$i, $cliente['rep'])
                ->setCellValue($letras[2].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($cliente['subtotal']))
                ->setCellValue($letras[3].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($cliente['total']));
            $i++;
        }
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[2].$i, "Subtotal: " . Auth::user()->empresa()->moneda." ".Funcion::Parsear($subtotal))
            ->setCellValue($letras[3].$i, "Total: " . Auth::user()->empresa()->moneda. " ".Funcion::Parsear($total));

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:D'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de remisiones cliente');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Remisiones_Clientes.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;


    }

    public function cuentasCobrar(Request $request)
    {

        $objPHPExcel = new PHPExcel();
        if($request->input('fechas') == 8){
            $tituloReporte = "Reporte de cuentas por cobrar ";
        }else{
            $tituloReporte = "Reporte de cuentas por cobrar ".$request->fecha." hasta ".$request->hasta;
        }

        $titulosColumnas = array('Numero', 'Cliente', 'Creacion', 'Vencimiento', 'Total', 'Pagado', 'Por Pagar');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Cuentas Por Cobrar") // Titulo
        ->setSubject("Reporte Excel Cuentas Por Cobrar") //Asunto
        ->setDescription("Reporte Excel Cuentas Por Cobrar") //Descripci���n
        ->setKeywords("reporte cuentas por cobrar") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:F1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:G3')->applyFromArray($estilo);


        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        if($request->nro == 'remisiones'){
            $tituloReporte = "Reporte de cuentas por cobrar remisiones ".$request->fecha." hasta ".$request->hasta;
            $this->remisionesCobrar($request);
        }else {

            //Aquí se obtienen los datos

            //Pendiente sustituir esto por setDateRequest
            //Si no hay fecha establecida dentro de la url se establece desde el 1 al 30/31 del mes actual
            if (!$request->fecha) {
                $month = date('m');
                $year = date('Y');
                $day = date("d", mktime(0, 0, 0, $month + 1, 0, $year));
                $fin = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
                $request->hasta = date('d-m-Y', mktime(0, 0, 0, $month, $day, $year));
                $month = date('m');
                $year = date('Y');
                $inicio = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
                $request->fecha = date('d-m-Y', mktime(0, 0, 0, $month, 1, $year));
            } else {
                if($request->input('fechas') != 8 || (!$request->has('fechas'))){
                    $inicio = date('Y-m-d', strtotime($request->fecha));
                    $fin    = date('Y-m-d', strtotime($request->hasta));
                }else{
                    $inicio = Carbon::now()->subYear('10')->format('Y-m-d');
                    $fin    = Carbon::now()->format('Y-m-d');
                }
            }


            //Código base de  FacturasController@index

            $campos = array('', 'factura.id', 'nombrecliente', 'factura.fecha', 'factura.vencimiento', 'total', 'pagado', 'porpagar', 'factura.estatus');
            if (!$request->orderby) {
                $request->orderby = 1;
                $request->order = 1;
            }
            $orderby = $campos[$request->orderby];
            $order = $request->order == 1 ? 'DESC' : 'ASC';

            $facturas = Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')
                ->join('items_factura as if', 'factura.id', '=', 'if.factura')
                ->select('factura.id', 'factura.codigo', 'factura.nro', 'factura.cot_nro' , DB::raw('c.nombre as nombrecliente'), 'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus',
                    DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                    DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) as pagado'),
                    DB::raw('(SUM(
          (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) -  ((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) - (Select if(SUM(pago), SUM(pago), 0) from notas_factura where factura=factura.id) )    as porpagar'))
                ->where('factura.empresa', Auth::user()->empresa)
                ->where('factura.estatus', 1);
            $appends = array('orderby' => $request->orderby, 'order' => $request->order);

            //Filtrado por fecha
            if ($request->fecha) {
                $appends['fecha'] = $request->fecha;
                $facturas = $facturas->where('factura.fecha', ">=", $inicio);
            }
            if ($request->fecha) {
                $appends['hasta'] = $request->hasta;
                $facturas = $facturas->where('factura.fecha', "<=", $fin);
            }

            $facturas = $facturas->groupBy('if.factura');

            if ($request->name_5) {
                $busqueda = true;
                $appends['name_5'] = $request->name_5;
                $appends['name_5_simb'] = $request->name_5_simb;
                $facturas = $facturas->havingRaw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) ' . $request->name_5_simb . ' ?', [$request->name_5]);
            }
            $facturas = $facturas->OrderBy($orderby, $order)->get();


            //Se determina el gran total
            $totalPagar = 0;
            foreach ($facturas as $factura) {
                $totalPagar += $factura->porPagar();
            }

            $i = 4;
            foreach ($facturas as $factura) {
                if (Funcion::Parsear($factura->porpagar()) > 0) {
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue($letras[0] . $i, ($factura->codigo == null) ? $factura->cot_nro : $factura->codigo)
                        ->setCellValue($letras[1] . $i, $factura->nombrecliente)
                        ->setCellValue($letras[2] . $i, date('d-m-Y', strtotime($factura->fecha)))
                        ->setCellValue($letras[3] . $i, date('d-m-Y', strtotime($factura->vencimiento)))
                        ->setCellValue($letras[4] . $i, Auth::user()->empresa()->moneda . " " . Funcion::Parsear($factura->total()->total))
                        ->setCellValue($letras[5] . $i, Auth::user()->empresa()->moneda . " " . Funcion::Parsear($factura->pagado()))
                        ->setCellValue($letras[6] . $i, Auth::user()->empresa()->moneda . " " . Funcion::Parsear($factura->porpagar()));
                    $i++;
                }
            }

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[6] . $i, "TOTAL A COBRAR: ")
                ->setCellValue($letras[7] . $i, Auth::user()->empresa()->moneda . " " . Funcion::Parsear($totalPagar));


            $estilo = array('font' => array('size' => 12, 'name' => 'Times New Roman'),
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
            $objPHPExcel->getActiveSheet()->getStyle('A3:G' . $i)->applyFromArray($estilo);


            for ($i = 'A'; $i <= $letras[20]; $i++) {
                $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
            }

            // Se asigna el nombre a la hoja
            $objPHPExcel->getActiveSheet()->setTitle('Reporte de cuentas por cobrar');

            // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
            $objPHPExcel->setActiveSheetIndex(0);

            // Inmovilizar paneles
            $objPHPExcel->getActiveSheet(0)->freezePane('A2');
            $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0, 4);
            $objPHPExcel->setActiveSheetIndex(0);
            header("Pragma: no-cache");
            header('Content-type: application/vnd.ms-excel');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Reporte_Cuentas_Cobrar.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            exit;
        }
    }

    public function remisionesCobrar($request)
    {

        $objPHPExcel = new PHPExcel();
        $tituloReporte = "Reporte de cuentas por cobrar remisiones ".$request->fecha." hasta ".$request->hasta;

        $titulosColumnas = array('Numero', 'Cliente', 'Creacion', 'Vencimiento', 'Total', 'Pagado', 'Por Pagar');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Cuentas Por Cobrar") // Titulo
        ->setSubject("Reporte Excel Cuentas Por Cobrar") //Asunto
        ->setDescription("Reporte Excel Cuentas Por Cobrar") //Descripci���n
        ->setKeywords("reporte cuentas por cobrar") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:F1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:G3')->applyFromArray($estilo);


        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $dates = $this->setDateRequest($request);
        $remisiones = Remision::where('empresa', Auth::user()->empresa)
            ->where('fecha', '<=', $dates['fin'])
            ->where('fecha', '>=', $dates['inicio'])
            ->whereIn('estatus', [0, 1])
            ->get();

        $totalPagar = 0;
        $remisionesCobrar = array();
        foreach ($remisiones as $remision){
            if($remision->porpagar() > 0 ){
                $remision->clienteNombre = $remision->cliente()->nombre;
                $remision->clienteId = $remision->cliente()->id;
                $remisionesCobrar[] = $remision;
                $totalPagar += $remision->porPagar();
            }
        }
        $facturas = array();
        if(count($remisiones) > 0){
            $facturas = $this->orderMultiDimensionalArray($remisionesCobrar, 'nro', true);
        }

        $i = 4;
        foreach ($facturas as $factura) {
            if (Funcion::Parsear($factura->porpagar()) > 0) {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($letras[0] . $i, $factura->nro)
                    ->setCellValue($letras[1] . $i, $factura->clienteNombre)
                    ->setCellValue($letras[2] . $i, date('d-m-Y', strtotime($factura->fecha)))
                    ->setCellValue($letras[3] . $i, date('d-m-Y', strtotime($factura->vencimiento)))
                    ->setCellValue($letras[4] . $i, Auth::user()->empresa()->moneda . " " . Funcion::Parsear($factura->total()->total))
                    ->setCellValue($letras[5] . $i, Auth::user()->empresa()->moneda . " " . Funcion::Parsear($factura->pagado()))
                    ->setCellValue($letras[6] . $i, Auth::user()->empresa()->moneda . " " . Funcion::Parsear($factura->porpagar()));
                $i++;
            }
        }

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[6] . $i, "TOTAL A COBRAR: ")
            ->setCellValue($letras[7] . $i, Auth::user()->empresa()->moneda . " " . Funcion::Parsear($totalPagar));


        $estilo = array('font' => array('size' => 12, 'name' => 'Times New Roman'),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:G' . $i)->applyFromArray($estilo);


        for ($i = 'A'; $i <= $letras[20]; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de cuentas por cobrar');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0, 4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Cuentas_Cobrar.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function cuentasPagar(Request $request)
    {

        $objPHPExcel = new PHPExcel();
        if($request->input('fechas') == 8){
            $tituloReporte = "Reporte de cuentas por pagar";
        }else{
            $tituloReporte = "Reporte de cuentas por pagar ".$request->fecha." hasta ".$request->hasta;
        }

        $titulosColumnas = array('Numero', 'Factura', 'Proveedor', 'Creación', 'Vencimiento', 'Total', 'Pagado', 'Por Pagar');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Cuentas Por Pargar") // Titulo
        ->setSubject("Reporte Excel Cuentas Por Pargar") //Asunto
        ->setDescription("Reporte Excel Cuentas Por Pargar") //Descripci���n
        ->setKeywords("reporte cuentas por pagar") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:H1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:H3')->applyFromArray($estilo);


        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        //Si no hay fecha establecida dentro de la url se establece desde el 1 al 30/31 del mes actual
        if (!$request->fecha) {
            $month = date('m');
            $year = date('Y');
            $day = date("d", mktime(0,0,0, $month+1, 0, $year));
            $fin= date('Y-m-d', mktime(0,0,0, $month, $day, $year));
            $request->hasta=date('d-m-Y', mktime(0,0,0, $month, $day, $year));
            $month = date('m');
            $year = date('Y');
            $inicio=  date('Y-m-d', mktime(0,0,0, $month, 1, $year));
            $request->fecha=date('d-m-Y', mktime(0,0,0, $month, 1, $year));
        }
        else{
            if($request->input('fechas') != 8 || (!$request->has('fechas'))){
                $inicio = date('Y-m-d', strtotime($request->fecha));
                $fin    = date('Y-m-d', strtotime($request->hasta));
            }else{
                $inicio = Carbon::now()->subYear('10')->format('Y-m-d');
                $fin    = Carbon::now()->format('Y-m-d');
            }
        }

        //Aquí se obtienen los datos para imprimir en .xls
        //Código base tomado de FacturaspController@index

        $campos=array('', 'factura_proveedores.nro', 'factura_proveedores.codigo', 'nombrecliente', 'factura_proveedores.fecha_factura','factura_proveedores.vencimiento_factura',  'total',  'total',  'total');
        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }

        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';
        $facturas=FacturaProveedores::leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')
            ->join('items_factura_proveedor as if', 'factura_proveedores.id', '=', 'if.factura')
            ->select('factura_proveedores.id', 'factura_proveedores.tipo',  'factura_proveedores.codigo', 'factura_proveedores.nro', DB::raw('c.nombre as nombrecliente'), 'factura_proveedores.proveedor', 'factura_proveedores.fecha_factura', 'factura_proveedores.vencimiento_factura', 'factura_proveedores.estatus',
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id)) as pagado'),
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant)-((Select if(SUM(pago), SUM(pago), 0) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id))  as porpagar'))
            ->where('factura_proveedores.empresa',Auth::user()->empresa)->where('factura_proveedores.tipo',1)
            ->where('factura_proveedores.fecha_factura', ">=", $inicio)
            ->where('factura_proveedores.fecha_factura', "<=", $fin)
            ->groupBy('if.factura')->OrderBy($orderby, $order)->get();

        /*
         * ->appends(['orderby'=>$request->orderby, 'order'=>$request->order])
         * SE COMENTA ESTA LINEA DEBIDO A ERROR AL EXPORTAR EXCEL HASTA POSIBLE SOLICION DEL ERROR
         * */

        //Se determina el gran total
        $totalPagar = 0;
        foreach ($facturas as $factura){
            $totalPagar += $factura->porPagar();
        }

        $i=4;
        foreach ($facturas as $factura) {

            if(Funcion::Parsear($factura->porpagar()) > 0 ){
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($letras[0].$i, $factura->nro)
                    ->setCellValue($letras[1].$i, $factura->codigo)
                    ->setCellValue($letras[2].$i, $factura->proveedor()->nombre)
                    ->setCellValue($letras[3].$i, date('d-m-Y', strtotime($factura->fecha_factura)))
                    ->setCellValue($letras[4].$i, date('d-m-Y', strtotime($factura->vencimiento_factura)))
                    ->setCellValue($letras[5].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($factura->total()->total))
                    ->setCellValue($letras[6].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($factura->pagado()))
                    ->setCellValue($letras[7].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($factura->porpagar()));
                $i++;
            }
        }

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[6].$i, "TOTAL A PAGAR: ")
            ->setCellValue($letras[7].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($totalPagar));

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:H'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de cuentas por pagar');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Cuentas_Pagar.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;

    }

    public function compras(Request $request)
    {

        $objPHPExcel = new PHPExcel();
        $tituloReporte = "Reporte de compras ".$request->fecha." hasta ".$request->hasta;

        $titulosColumnas = array('Numero', 'Factura', 'Proveedor', 'Creacion', 'Vencimiento', 'Subtotal', 'IVA',
            'Retencion', 'Total');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Compras") // Titulo
        ->setSubject("Reporte Excel Compras") //Asunto
        ->setDescription("Reporte Excel Compras") //Descripci���n
        ->setKeywords("reporte cuentas compras") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:I1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:I3')->applyFromArray($estilo);


        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }


        $dates = $this->setDateRequest($request);
        if ($request->input('fechas') == 8)
            $dates = $this->setDateRequest($request, true);

        //Código base tomado de FacturaspController@index

        $campos=array('', 'factura_proveedores.nro', 'factura_proveedores.codigo', 'nombrecliente', 'factura_proveedores.fecha_factura','factura_proveedores.vencimiento_factura',  'total',  'total',  'total');
        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';
        $facturas=FacturaProveedores::leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')
            ->join('items_factura_proveedor as if', 'factura_proveedores.id', '=', 'if.factura')
            ->select('factura_proveedores.id', 'factura_proveedores.tipo',  'factura_proveedores.codigo', 'factura_proveedores.nro', DB::raw('c.nombre as nombrecliente'), 'factura_proveedores.proveedor', 'factura_proveedores.fecha_factura', 'factura_proveedores.vencimiento_factura', 'factura_proveedores.estatus',
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id)) as pagado'),
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant)-((Select if(SUM(pago), SUM(pago), 0) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id))  as porpagar'))
            ->where('factura_proveedores.empresa',Auth::user()->empresa)
            ->where('factura_proveedores.fecha_factura', ">=", $dates['inicio'])
            ->where('factura_proveedores.fecha_factura', "<=", $dates['fin'])
            ->where('factura_proveedores.estatus','!=','3')
            ->where('factura_proveedores.tipo',1)->groupBy('if.factura')
            ->OrderBy($orderby, $order)->paginate(1000000)
            ->appends(['orderby'=>$request->orderby, 'order'=>$request->order]);

        //Se determina el gran total
        $totalPagar = 0;
        foreach ($facturas as $factura){
            $totalPagar += $factura->total()->total;
        }


        $i=4;
        foreach ($facturas as $factura) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $factura->nro)
                ->setCellValue($letras[1].$i, $factura->codigo)
                ->setCellValue($letras[2].$i, $factura->proveedor()->nombre)
                ->setCellValue($letras[3].$i, date('d-m-Y', strtotime($factura->fecha_factura)))
                ->setCellValue($letras[4].$i, date('d-m-Y', strtotime($factura->vencimiento)))
                ->setCellValue($letras[5].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($factura->total()->subsub))
                ->setCellValue($letras[6].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($factura->impuestos_totales()))
                ->setCellValue($letras[7].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($factura->retenido()))
                ->setCellValue($letras[8].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($factura->total()->total));
            $i++;
        }

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[3].$i, "TOTAL: ")
            ->setCellValue($letras[4].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($totalPagar));

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:I'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de compras');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Compras.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;

    }

    public function estadoCliente(Request $request)
    {

        //Se obtienen los datos del cliente
        $client = Contacto::find($request->client);
        $nombre = $client->nombre;
        $nit = $client->nit;

        $objPHPExcel = new PHPExcel();
        if($request->input('fechas') == 8){
            $tituloReporte = "Reporte de estado de cuenta cliente $nombre - $nit ";
        }else{
            $tituloReporte = "Reporte de estado de cuenta cliente $nombre - $nit || ".$request->fecha." hasta ".$request->hasta;
        }

        $titulosColumnas = array('Numero', 'Tipo documen', 'Creación', 'Vencimiento', 'Dias vencidos', 'Estado', 'Total',
            'Pagado', 'Por pagar');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Estado Cuenta") // Titulo
        ->setSubject("Reporte Excel Estado Cuenta") //Asunto
        ->setDescription("Reporte Excel Estado Cuenta") //Descripci���n
        ->setKeywords("reporte cuentas estado cuenta") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:I1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:I3')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        //Se empieza con la recolecta de datos
        $dates = $this->setDateRequest($request);
        if($request->input('fechas') == 8)
            $dates = $this->setDateRequest($request, true);

        $facturas=Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->join('items_factura as if', 'factura.id', '=', 'if.factura')
            ->select('factura.id', 'factura.codigo', 'factura.tipo', 'factura.nro', DB::raw('c.nombre as nombrecliente'), 'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus',
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) as pagado'),
                DB::raw('(SUM(
          (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) -  ((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) - (Select if(SUM(pago), SUM(pago), 0) from notas_factura where factura=factura.id) )    as porpagar'))
            ->where('factura.empresa',Auth::user()->empresa)
            ->where('factura.fecha', ">=", $dates['inicio'])
            ->where('factura.fecha', "<=", $dates['fin'])
            ->where('factura.cliente', $client->id)
            ->groupBy('if.factura')
            ->get();
        $i=4;
        foreach ($facturas as $factura) {
            $tipo = $factura->tipo == 1 ? "Factura" : "Cotización";
            $time = \App\Funcion::diffDates(date('Y-m-d'), $factura->vencimiento);
            $pay = $factura->porpagar() - $factura->pagado() > 0  ? "Por pagar" : "Pagado";
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $factura->codigo)
                ->setCellValue($letras[1].$i, $tipo)
                ->setCellValue($letras[2].$i, date('d-m-Y', strtotime($factura->fecha)))
                ->setCellValue($letras[3].$i, date('d-m-Y', strtotime($factura->vencimiento)))
                ->setCellValue($letras[4].$i, $pay)
                ->setCellValue($letras[5].$i, $time)
                ->setCellValue($letras[6].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($factura->total()->total))
                ->setCellValue($letras[7].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($factura->pagado()))
                ->setCellValue($letras[8].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($factura->porpagar()));
            $i++;
        }

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:I'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de estado de cuenta');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Estado_Cliente.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;

    }

    public function reporteDiario(Request $request)
    {
        $formatDate = date('Y-m-d H:i:s', strtotime($request->date));
        $Ifacturas = IngresosFactura::all();
        $first;
        $last;
        $nombreEmpresa = Empresa::where('id', Auth::user()->empresa)->first()->nombre;
        $nitEmpresa = Empresa::where('id', Auth::user()->empresa)->first()->nit;
        $metodosPagoIngreso = array(
            'Efectivo'          => 0,
            'Transferencia'     => 0,
            'Consignación'      => 0,
            'Cheque'            => 0,
            'Tarjeta crédito'   => 0,
            'Tarjeta débito'    => 0,
        );
        $total = 0;
        $gravada = 0;
        $NoGravada = 0;
        //Se obtienen los ingresos del día sacados de ingresos_factura e ingresos
        if(count($Ifacturas) > 0 ){
            foreach ($Ifacturas as $factura){
                $ingreso = Ingreso::where('id', $factura->ingreso)
                    ->where('empresa', Auth::user()->empresa)
                    ->where('fecha', $formatDate)
                    ->first();
                if(isset($ingreso)){
                    $metodosPagoIngreso[$ingreso->metodo_pago()] += $factura->pago();
                    $total += $factura->pago();
                }
                /*
                foreach ($factura->itemFactura() as $itemFactura){
                    if($itemFactura->impuesto > 0 ){
                    }
                }
                */
            }

        }else{
            return redirect()->route('reportes.index')->with('notify', "No hay ventas registradas el $formatDate");
        }

        $facturas = Factura::where('fecha', "=", $formatDate)
                        ->where('empresa', Auth::user()->empresa)->get();

        //Se obtinene las facturas del día
        if(count($facturas) > 0){

            if (count($facturas) == 1){
                $first = $facturas->first()->codigo;
                $last = $facturas->first()->codigo;

            }else{
                $first = $facturas->first()->codigo;
                $last = $facturas->last()->codigo;
            }

        }else{
            $first = 0;
            $last = 0;
        }

        $objPHPExcel = new PHPExcel();

        $tituloReporte = "Reporte diario de ventas $nombreEmpresa - $nitEmpresa || FECHA: $formatDate";
        $titulosColumnas = array('Efectivo','Transferencia', 'Consignacion', 'Cheque', 'Tarjeta de credito', 'Tarjeta de debito');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Ventas Diarias") // Titulo
        ->setSubject("Reporte Excel Ventas Diarias") //Asunto
        ->setDescription("Reporte Excel Ventas Cliente") //Descripci���n
        ->setKeywords("reporte cuentas ventas diarias") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta H1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:H1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        //Estilo cabecera
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($estilo);
        //Estilo titulos
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:F3')->applyFromArray($estilo);
        $objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray($estilo);
        $objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($estilo);
        $objPHPExcel->getActiveSheet()->getStyle('D6')->applyFromArray($estilo);
        $objPHPExcel->getActiveSheet()->getStyle('D7')->applyFromArray($estilo);
        $objPHPExcel->getActiveSheet()->getStyle('D8')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $i=4;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[0].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($metodosPagoIngreso['Efectivo']))
            ->setCellValue($letras[1].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($metodosPagoIngreso['Transferencia']))
            ->setCellValue($letras[2].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($metodosPagoIngreso['Consignación']))
            ->setCellValue($letras[3].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($metodosPagoIngreso['Cheque']))
            ->setCellValue($letras[4].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($metodosPagoIngreso['Tarjeta crédito']))
            ->setCellValue($letras[5].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($metodosPagoIngreso['Tarjeta débito']));
        $i++;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[0].$i, " ")
            ->setCellValue($letras[1].$i, " ")
            ->setCellValue($letras[2].$i, " ")
            ->setCellValue($letras[3].$i, " ")
            ->setCellValue($letras[4].$i, " ");
        $i++;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[0].$i, "Factura Inicial: ")
            ->setCellValue($letras[1].$i, $first)
            ->setCellValue($letras[3].$i, "Ventas gravadas: " )
            ->setCellValue($letras[4].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear(0));
        $i++;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[0].$i, "Factura final: ")
            ->setCellValue($letras[1].$i, $last)
            ->setCellValue($letras[3].$i, "Ventas no gravadas: ")
            ->setCellValue($letras[4].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear(0));
        $i++;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[3].$i, "Total: ")
            ->setCellValue($letras[4].$i, Auth::user()->empresa()->moneda ." ". Funcion::Parsear($total));



        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:I'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de ventas día');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Ventas_Dia.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;

    }

    public function ventasVendedor(Request $request)
    {

        $objPHPExcel = new PHPExcel();

        if($request->input('fechas') == 8){
            $tituloReporte = "Reporte de ventas por vendedor";
        }else{
            $tituloReporte = "Reporte de ventas por vendedor desde ".$request->fecha." hasta ".$request->hasta;
        }

        $titulosColumnas = array('Vendedor', 'Numero de facturas', 'Pagado', 'Antes de impuestos', 'Total');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Ventas Vendedor") // Titulo
        ->setSubject("Reporte Excel Ventas Vendedor") //Asunto
        ->setDescription("Reporte Excel Ventas Vendedor") //Descripci���n
        ->setKeywords("reporte cuentas ventas vendedor") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:D1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:E3')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $dates = $this->setDateRequest($request);
        if($request->input('fechas') == 8)
            $dates      = $this->setDateRequest($request, true);

        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->get();
        $totales = array(
            'pagado'    => 0,
            'subtotal'  => 0,
            'total'     => 0,
        );
        foreach ($vendedores as $vendedore){
            $totales['pagado'] += $vendedore->pagosFecha($dates['inicio'], $dates['fin']);
            $totales['subtotal'] += $vendedore->montoTotal($dates['inicio'], $dates['fin'])['subtotal'];
            $totales['total'] += $vendedore->montoTotal($dates['inicio'], $dates['fin'])['total'];
        }

        $i=4;
        foreach ($vendedores as $vendedor) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $vendedor->nombre)
                ->setCellValue($letras[1].$i, $vendedor->nroFacturas($dates['inicio'], $dates['fin']))
                ->setCellValue($letras[2].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($vendedor->pagosFecha($dates['inicio'], $dates['fin'])))
                ->setCellValue($letras[3].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($vendedor->montoTotal($dates['inicio'], $dates['fin'])['subtotal']))
                ->setCellValue($letras[4].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($vendedor->montoTotal($dates['inicio'], $dates['fin'])['total']));
            $i++;
        }

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[2].$i, "Pagado: " . Auth::user()->empresa()->moneda.' '.Funcion::Parsear($totales['pagado']))
            ->setCellValue($letras[3].$i, "Subtotal: " . Auth::user()->empresa()->moneda.' '.Funcion::Parsear($totales['subtotal']))
            ->setCellValue($letras[4].$i, "Total: " . Auth::user()->empresa()->moneda.' '.Funcion::Parsear($totales['total']));

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:E'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de ventas por vendedor');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Ventas_Vendedor.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function rentabilidadItem(Request $request)
    {

        $objPHPExcel = new PHPExcel();
        if($request->input('fechas') == 8){
            $tituloReporte = "Reporte de rentabilidad de items";
        }else {
            $tituloReporte = "Reporte de rentabilidad de items " . $request->fecha . " hasta " . $request->hasta;
        }
        $titulosColumnas = array('Item', 'Referencia', 'Total vendido', 'Costo total', 'Rentabilidad', 'Porcentaje');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Rentabilidad Items") // Titulo
        ->setSubject("Reporte Excel Rentabilidad Items") //Asunto
        ->setDescription("Reporte Excel Rentabilidad Items") //Descripci���n
        ->setKeywords("reporte cuentas rentabilidad items") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:D1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:F3')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $dates = $this->setDateRequest($request);
        $items = Inventario::where('empresa', Auth::user()->empresa)
            ->where('tipo_producto', 1)
            ->get();
        $totales = array(
            'totalVendidos'         => 0,
            'costosTotales'         => 0,
            'rentabilidadtotal'     => 0,
        );

        foreach ($items as $item){
            $facturas = Factura::where('empresa', Auth::user()->empresa)
                ->whereNull('cot_nro');
            if($request->input('fechas') != 8 || (!$request->has('fechas'))){
                $facturas=$facturas->where('fecha','>=', $dates['inicio'])->where('fecha','<=', $dates['fin']);
            }
            $facturas = $facturas->get();

            $item->totalVendido = 0;
            $item->vendidos = count($facturas);

            if(!count($facturas) == 0){
                foreach ($facturas as $factura){
                    $itemFacturas = ItemsFactura::where('factura', $factura->id)
                        ->where('producto', $item->id)
                        ->get();

                    if(!count($itemFacturas) == 0){
                        foreach ($itemFacturas as $itemFactura){
                            $item->totalVendido+= $itemFactura->totalImp();
                        }
                    }

                }
            }

            if($item->totalVendido == 0){
                $item->costoTotal   = 0;
                $item->rentabilidad = 0;
                $item->porcentaje   = 0;

            }else{
                $item->costoTotal   = $item->vendidos * $item->costo_unidad;
                $item->rentabilidad = $item->totalVendido - $item->costoTotal;
                $item->porcentaje   = ($item->rentabilidad/$item->totalVendido)*100;

            }

            $totales['totalVendidos']        += $item->totalVendido;
            $totales['rentabilidadtotal']    += $item->rentabilidad;
            $totales['costosTotales']        += $item->costoTotal;


        }
        $items = $this->orderMultiDimensionalArray($items, 'totalVendido', true);

        $i=4;
        foreach ($items as $item) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, substr($item->producto, 0, 31))
                ->setCellValue($letras[1].$i, $item->ref)
                ->setCellValue($letras[2].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($item->totalVendido))
                ->setCellValue($letras[3].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($item->costoTotal))
                ->setCellValue($letras[4].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($item->rentabilidad))
                ->setCellValue($letras[5].$i, Funcion::Parsear($item->porcentaje)."%");
            $i++;
        }

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[2].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($totales['totalVendidos']))
            ->setCellValue($letras[3].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($totales['costosTotales']))
            ->setCellValue($letras[4].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($totales['rentabilidadtotal']));

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:F'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de ventas por vendedor');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Ventas_Vendedor.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;

    }

    public function transacciones(Request $request)
    {
        $objPHPExcel = new PHPExcel();

        $tituloReporte = "Reporte de transacciones ".$request->fecha." hasta ".$request->hasta;

        $titulosColumnas = array('Fecha', 'Comprobante', 'Cuenta', 'Categoría', 'Estado', 'Salida', 'Entrada');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Transacciones") // Titulo
        ->setSubject("Reporte Excel Transacciones") //Asunto
        ->setDescription("Reporte Excel Transacciones") //Descripci���n
        ->setKeywords("reporte cuentas Transacciones") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:D1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:G3')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $dates = $this->setDateRequest($request);
        if ($request->input('fechas') == 8)
            $dates = $this->setDateRequest($request, true);

        //Código base tomado de datatable_movimientos

        $movimientos= Movimiento::leftjoin('contactos as c', 'movimientos.contacto', '=', 'c.id')
            ->select('movimientos.*', DB::raw('if(movimientos.contacto,c.nombre,"") as nombrecliente'))
            ->where('fecha', '>=', $dates['inicio'])
            ->where('fecha', '<=', $dates['fin'])
            ->where('movimientos.empresa',Auth::user()->empresa);



        $movimientos=  $movimientos->orderBy('fecha', 'DESC')->get();

        $totales = array(
            'salida'    => 0,
            'entrada'   => 0
        );

        foreach ($movimientos as $movimientoT){
            $totales['salida']  += $movimientoT->tipo==2?$movimientoT->saldo:0;
            $totales['entrada']  += $movimientoT->tipo==1?$movimientoT->saldo:0;
        }

        $i=4;
        foreach ($movimientos as $movimiento) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, date('d-m-Y', strtotime($movimiento->fecha)))
                ->setCellValue($letras[1].$i, $movimiento->id)
                ->setCellValue($letras[2].$i, $movimiento->banco()->nombre)
                ->setCellValue($letras[3].$i, $movimiento->categoria())
                ->setCellValue($letras[4].$i, $movimiento->estatus())
                ->setCellValue($letras[5].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($movimiento->tipo==2?$movimiento->saldo:0))
                ->setCellValue($letras[6].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($movimiento->tipo==1?$movimiento->saldo:0));
            $i++;
        }

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[5].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($totales['salida']))
            ->setCellValue($letras[6].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($totales['entrada']));

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:G'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de transacciones');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Transacciones.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function valorActual(Request $request)
    {

        $objPHPExcel = new PHPExcel();

        $tituloReporte = "Reporte de valor actual del inventario";

        $titulosColumnas = array('Item', 'Referencia', 'Descripcion', 'Cantidad', 'Unidad', 'Estado', 'Costo', 'Total');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Valor Actual") // Titulo
        ->setSubject("Reporte Excel Valor Actual") //Asunto
        ->setDescription("Reporte Excel Valor Actual") //Descripci���n
        ->setKeywords("reporte cuentas Valor Actual") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:H1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:H3')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }
        //Inyeccion de datos
        $bodega = Bodega::where('empresa',Auth::user()->empresa)
            ->where('status', 1)
            ->where('id', $request->bodega)->first();
        $bodegas = Bodega::where('empresa',Auth::user()->empresa)
            ->where('status', 1)->get();
        if (!$bodega) {
            $bodega = Bodega::where('empresa',Auth::user()->empresa)
                ->where('status', 1)
                ->first();
        }
        if(!$request->bodega || $request->bodega == "all"){
            $request->bodega = 'all';
            $productos = Inventario::select('*')
                ->whereIn('id', function ($query){
                    $query->select('producto')
                        ->from(with(new ProductosBodega)->getTable())
                        ->where('empresa', Auth::user()->empresa);
                })->get();

        }else{
            $productos = Inventario::select('*')
                ->whereIn('id', function ($query) use ($bodega){
                    $query->select('producto')
                        ->from(with(new ProductosBodega)->getTable())
                        ->where('bodega', $bodega->id)
                        ->where('empresa', Auth::user()->empresa);
                })->get();

        }

        $total = 0;
        foreach ($productos as $producto){
            $producto->precio = $this->precision($producto->precio);
            $producto->costo_unidad=$this->precision($producto->costo_unidad);
            $producto->inventario = $request->bodega != "all" ? $producto->inventarioBodega($bodega->id) : $producto->inventario();
            $producto->total = $producto->costo_unidad * $producto->inventario;
            $total += $producto->costo_unidad * $producto->inventario;
        }

        $productos = $this->orderMultiDimensionalArray($productos,'total', true);
        $i=4;
        foreach ($productos as $producto) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $producto->producto)
                ->setCellValue($letras[1].$i, $producto->ref)
                ->setCellValue($letras[2].$i, $producto->descripcion)
                ->setCellValue($letras[3].$i, $producto->inventario)
                ->setCellValue($letras[4].$i, $producto->unidad())
                ->setCellValue($letras[5].$i, $producto->status())
                ->setCellValue($letras[6].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($producto->costo_unidad))
                ->setCellValue($letras[7].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($producto->costo_unidad * $producto->inventario));
            $i++;
        }

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[7].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($total));

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:H'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte valor actual');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Valor_Actual.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function ingresosEgresos(Request $request)
    {

        $objPHPExcel = new PHPExcel();

        $tituloReporte = "Reporte de ingresos/egresos";

        $titulosColumnas = array('Categoria', 'Total');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Ingresos Egresos") // Titulo
        ->setSubject("Reporte Excel Ingresos Egresos") //Asunto
        ->setDescription("Reporte Excel Ingresos Egresos") //Descripci���n
        ->setKeywords("reporte cuentas Ingresos Egresos") //Etiquetas
        ->setCategory("Reporte excel ingresos egresos"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:B1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:B1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:B3')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $dates = $this->setDateRequest($request);
        if ($request->input('fechas') == 8)
            $dates = $this->setDateRequest($request, true);

        $gastos     = $this->egresosTEST($dates);
        $ingresos   = $this->ingresosTEST($dates);

        $i=4;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[0].$i, "Total Ingresos")
            ->setCellValue($letras[1].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($ingresos));
        $i++;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[1].$i, "Ingresos: ".Auth::user()->empresa()->moneda.' '.Funcion::Parsear($ingresos['ingresos']));
        $i+=2;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[0].$i, "Categoria")
            ->setCellValue($letras[1].$i, "Ingresos");
        $objPHPExcel->getActiveSheet()->getStyle($letras[0].$i)->applyFromArray($estilo);
        $objPHPExcel->getActiveSheet()->getStyle($letras[1].$i)->applyFromArray($estilo);
        $i++;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[0].$i, "Total Egresos")
            ->setCellValue($letras[1].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($gastos));
        $i++;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[1].$i, "Egresos: ".Auth::user()->empresa()->moneda.' '.Funcion::Parsear($gastos));

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:B'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte ingreso egresos');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Ingresos_Egresos.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;

    }

    private function ingresosEgresosBCK(Request $request)
    {

        $objPHPExcel = new PHPExcel();

        $tituloReporte = "Reporte de ingresos/egresos";

        $titulosColumnas = array('Categoria', 'Total');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Ingresos Egresos") // Titulo
        ->setSubject("Reporte Excel Ingresos Egresos") //Asunto
        ->setDescription("Reporte Excel Ingresos Egresos") //Descripci���n
        ->setKeywords("reporte cuentas Ingresos Egresos") //Etiquetas
        ->setCategory("Reporte excel ingresos egresos"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:B1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:B1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:B3')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $dates = $this->setDateRequest($request);

        $gastos     = $this->egresos($dates);
        $ingresos   = $this->ingresos($dates);

        $i=4;
        foreach ($ingresos as $ingreso) {
            if(!empty($ingreso['nombre'])){
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($letras[0].$i, $ingreso['nombre'])
                    ->setCellValue($letras[1].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($ingreso['total']));
                $i++;
            }

        }

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[1].$i, "Ingresos: ".Auth::user()->empresa()->moneda.' '.Funcion::Parsear($ingresos['ingresos']));
        $i+=2;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[0].$i, "Categoria")
            ->setCellValue($letras[1].$i, "Ingresos");
        $objPHPExcel->getActiveSheet()->getStyle($letras[0].$i)->applyFromArray($estilo);
        $objPHPExcel->getActiveSheet()->getStyle($letras[1].$i)->applyFromArray($estilo);
        $i++;
        foreach ($gastos as $gasto) {
            if(!empty($gasto['nombre'])){
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($letras[0].$i, $gasto['nombre'])
                    ->setCellValue($letras[1].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($gasto['total']));
                $i++;
            }
        }
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[1].$i, "Egresos: ".Auth::user()->empresa()->moneda.' '.Funcion::Parsear($gastos['gasto']));

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:B'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte ingreso egresos');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Ingresos_Egresos.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;

    }

    private function egresos($dates, $noData = false)
    {
        //Se obtienen todas las facturas de proveedores dentro de la fecha correspondinete
        $itemsFacturas = ItemsFacturaProv::select('*')
            ->whereIn('factura', function($query) use ($dates){
                $query->select('id')
                    ->from(with(new FacturaProveedores)->getTable())
                    ->where('fecha_factura', '<=', $dates['fin'])
                    ->where('fecha_factura', '>=', $dates['inicio'])
                    ->where('empresa', Auth::user()->empresa);
            })->get();
        $gastosItem = GastosCategoria::select('*')
            ->whereIn('gasto', function ($query) use ($dates){
                $query->select('id')
                    ->from(with(new Gastos)->getTable())
                    ->where('fecha' ,'<=', $dates['fin'])
                    ->where('fecha' ,'>=', $dates['inicio'])
                    ->where('empresa', Auth::user()->empresa);
            })->get();
        $categoriaGasto = array();
        $categoriaGasto ['gasto'] = 0;
        //Se filtra por tipo de item y se agrupan su total por categoria
        foreach ($itemsFacturas as $itemsFactura){
            if($itemsFactura->tipo_item == 1) {
                $categoria = $itemsFactura->productoTotal()->categoriaId();
            }
            else{
                $categoria = $itemsFactura->producto(true);
            }
            if(!isset($categoriaGasto[$categoria->id])){
                $categoriaGasto[$categoria->id]['nombre']        = $categoria->nombre;
                $categoriaGasto[$categoria->id]['descripcion']   = $categoria->descripcion;
                $categoriaGasto[$categoria->id]['total']         = $itemsFactura->totalImp();
                $categoriaGasto[$categoria->id]['id']         = $categoria->id;
                $categoriaGasto ['gasto']                        += $itemsFactura->totalImp();
            }else{
                $categoriaGasto[$categoria->id]['total'] += $itemsFactura->totalImp();
                $categoriaGasto ['gasto']                += $itemsFactura->totalImp();
            }

        }
        if(count($gastosItem) > 0 )
        {
            foreach ($gastosItem as $gastoItem)
            {
                if(!isset($categoriaGasto[$gastoItem->categoria])){
                    $categoriaGasto[$gastoItem->categoria]['nombre']        = $gastoItem->categoria(true);
                    $categoriaGasto[$gastoItem->categoria]['descripcion']   = $gastoItem->detalleCat()->descripcion;
                    $categoriaGasto[$gastoItem->categoria]['total']         = $gastoItem->pago();
                    $categoriaGasto ['gasto']                               += $gastoItem->pago();
                }else{
                    $categoriaGasto[$gastoItem->categoria]['total'] += $gastoItem->pago();
                    $categoriaGasto ['gasto']                += $gastoItem->pago();
                }
            }
        }
        return $categoriaGasto;
    }

    private function ingresos($dates)
    {
        //Se obtienen todas las facturas dentro de la fecha correspondinete
        $itemsFacturas = ItemsFactura::select('*')
            ->whereIn('factura', function($query) use ($dates){
                $query->select('id')
                    ->from(with(new Factura)->getTable())
                    ->where('fecha', '<=', $dates['fin'])
                    ->where('fecha', '>=', $dates['inicio'])
                    ->where('empresa', Auth::user()->empresa);
            })->get();
        $ingresosItem = IngresosCategoria::select('*')
            ->whereIn('ingreso', function ($query) use ($dates){
                $query->select('id')
                    ->from(with(new Ingreso)->getTable())
                    ->where('fecha', '<=', $dates['fin'])
                    ->where('fecha', '>=', $dates['inicio'])
                    ->where('empresa', Auth::user()->empresa);
            })->get();

        $categoriaGanancia = array();
        $categoriaGanancia ['ingresos'] = 0;
        //Se filtra por tipo de item y se agrupan su total por categoria
        foreach ($itemsFacturas as $itemsFactura){
            if($itemsFactura->tipo_inventario == 1){
                $categoria = $itemsFactura->productoTotal()->categoriaId();
                if($categoria){
                    if(!isset($categoriaGanancia[$categoria->id])){
                        $categoriaGanancia[$categoria->id]['nombre']        = $categoria->nombre;
                        $categoriaGanancia[$categoria->id]['descripcion']   = $categoria->descripcion;
                        $categoriaGanancia[$categoria->id]['total']         = $itemsFactura->totalImp();
                        $categoriaGanancia ['ingresos']                     += $itemsFactura->totalImp();
                    }else{
                        $categoriaGanancia[$categoria->id]['total'] += $itemsFactura->totalImp();
                        $categoriaGanancia ['ingresos']             += $itemsFactura->totalImp();
                    }
                }
            }
        }

        if (count($ingresosItem) > 0)
        {
            foreach ($ingresosItem as $ingresoItem)
            {
                if(!isset($categoriaGanancia[$ingresoItem->categoria])){
                    $categoriaGanancia[$ingresoItem->categoria]['nombre']        = $ingresoItem->categoria(true);
                    $categoriaGanancia[$ingresoItem->categoria]['descripcion']   = $ingresoItem->categoria()->descripcion;
                    $categoriaGanancia[$ingresoItem->categoria]['total']         = $ingresoItem->pago();
                    $categoriaGanancia ['ingresos']                              += $ingresoItem->pago();
                }else{
                    $categoriaGanancia[$ingresoItem->categoria]['total'] += $ingresoItem->pago();
                    $categoriaGanancia ['ingresos']                += $ingresoItem->pago();
                }
            }
        }
        return $categoriaGanancia;
    }

    private function egresosTEST($dates, $noData = false)
    {
        $this->getAllPermissions(Auth::user()->id);

        $proveedorFacturas = FacturaProveedores::where('fecha_factura', '<=', $dates['fin'])
            ->where('fecha_factura', '>=', $dates['inicio'])
            ->where('empresa', Auth::user()->empresa)
            ->get();
        $categoriaGasto = 0;
        foreach ($proveedorFacturas as $proveedorFactura){
            $categoriaGasto += $proveedorFactura->porpagar();
        }
        return $categoriaGasto;
    }

    private function ingresosTEST($dates)
    {
        //Se obtienen todas las facturas dentro de la fecha correspondinete
        $facturas = Factura::where('fecha', '<=', $dates['fin'])
            ->where('fecha', '>=', $dates['inicio'])
            ->where('empresa', Auth::user()->empresa)
            ->get();
        $categoriaGanancia = 0;
        foreach ($facturas as $factura) {
            $categoriaGanancia += $factura->pagado();
        }
        return $categoriaGanancia;
    }

    public function categorias(Request $request)
    {
        $dates      = $this->setDateRequest($request);
        if ($request->input('fechas') == 8)
            $dates = $this->setDateRequest($request, true);
        $categorias = Categoria::where('empresa',Auth::user()->empresa)
            ->where('estatus', 1)
            ->get();

        if($request->categoria)
        {
            $inventario = Inventario::where('empresa', Auth::user()->empresa)
                ->where('categoria', $request->categoria)
                ->get();
        }
        else
        {
            $inventario = Inventario::where('empresa', Auth::user()->empresa)
                ->where('categoria', Auth::user()->empresa()->categoria_default)
                ->get();
            $request->categoria = Categoria::where('empresa', Auth::user()->empresa)->where('nombre', 'Activos')
                ->get()->first()->id;
        }

        $ingresos           = $this->ingresos($dates);
        $egresos            = $this->egresos($dates);
        $categoriadata      = Categoria::find($request->categoria);
        $cantidadInventario = $inventario->count();
        $codigo             = $categoriadata == '' ? '...' : Categoria::find($request->categoria)->codigo;

        if(!isset($ingresos[$request->categoria]))
        {
            $ingresos[$request->categoria]['total']  = 0;

        }
        if(!isset($egresos[$request->categoria]))
        {
            $egresos[$request->categoria]['total'] = 0;

        }


        $objPHPExcel = new PHPExcel();

        $tituloReporte = "Reporte de categoria: $categoriadata->nombre";
        $titulosColumnas = array('Referencia','Producto', 'Precio', 'Disp', 'Estatus web');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Categoria") // Titulo
        ->setSubject("Reporte Excel Categoria") //Asunto
        ->setDescription("Reporte Excel Categoria") //Descripci���n
        ->setKeywords("reporte cuentas categoria") //Etiquetas
        ->setCategory("Reporte excel categoria"); //Categorias
        // Se combinan las celdas A1 hasta H1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:E1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        //Estilo cabecera
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->applyFromArray($estilo);
        //Estilo titulos
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A7:F7')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'7', utf8_decode($titulosColumnas[$i]));
        }
        $i=4;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[0].$i, "CODIGO" )
            ->setCellValue($letras[1].$i, "NOMBRE")
            ->setCellValue($letras[2].$i, "# ITEMS")
            ->setCellValue($letras[3].$i, "INGRESOS")
            ->setCellValue($letras[4].$i, "EGRESOS");
        $i=5;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[0].$i, $codigo )
            ->setCellValue($letras[1].$i, $categoriadata->nombre)
            ->setCellValue($letras[2].$i, $cantidadInventario)
            ->setCellValue($letras[3].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($ingresos[$request->categoria]['total']))
            ->setCellValue($letras[4].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($egresos[$request->categoria]['total']));
        $i=8;
        foreach ($inventario as $producto) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $producto->ref)
                ->setCellValue($letras[1].$i, $producto->producto)
                ->setCellValue($letras[3].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($producto->precio))
                ->setCellValue($letras[4].$i, ($producto->tipo_producto==1)?$producto->inventario()." ".$producto->unidad(true):'N/A')
                ->setCellValue($letras[4].$i, (Auth::user()->empresa()->carrito==1 ? $producto->web() : ""));
            $i++;
        }

        $i++;
        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:E'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de categoria');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_categoria.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    function orderMultiDimensionalArray ($toOrderArray, $field, $inverse = false) {
        $position = array();
        $newRow = array();
        foreach ($toOrderArray as $key => $row) {
            $position[$key]  = $row[$field];
            $newRow[$key] = $row;
        }
        if ($inverse) {
            arsort($position);
        }
        else {
            asort($position);
        }
        $returnArray = array();
        foreach ($position as $key => $pos) {
            $returnArray[] = $newRow[$key];
        }
        return $returnArray;
    }

    private function setDateRequest(&$request, $all = false)
    {

        //Si no hay fecha establecida dentro de la url se establece desde el 1 al 30/31 del mes actual
        if (!$request->fecha) {
            $month = date('m');
            $year = date('Y');
            $day = date("d", mktime(0,0,0, $month+1, 0, $year));
            $fin= date('Y-m-d', mktime(0,0,0, $month, $day, $year));
            $request->hasta=date('d-m-Y', mktime(0,0,0, $month, $day, $year));
            $month = date('m');
            $year = date('Y');
            $inicio=  date('Y-m-d', mktime(0,0,0, $month, 1, $year));
            $request->fecha=date('d-m-Y', mktime(0,0,0, $month, 1, $year));
        }
        else{
            $inicio= date('Y-m-d', strtotime($request->fecha));
            $fin= date('Y-m-d', strtotime($request->hasta));
        }

        if($all){
            $inicio = Carbon::now()->subYear('10')->format('Y-m-d');
            $fin    = Carbon::now()->format('Y-m-d');
        }

        return array(
            'inicio'    => $inicio,
            'fin'       => $fin
        );
    }
    
    public function cajas(Request $request) {
        $objPHPExcel = new PHPExcel();

        if($request->caja){
            $banco = Banco::where('id',$request->caja)->first();
            $tituloReporte = "Reporte de caja ".$request->fecha." hasta ".$request->hasta." | ".$banco->nombre;
            $caja = $banco->nombre;
        }else{
            $tituloReporte = "Reporte de caja ".$request->fecha." hasta ".$request->hasta;
            $caja = 'DE_CAJAS';
        }

        $titulosColumnas = array('Fecha', 'Comprobante', 'Contacto','Identificacion','Realizado por','Cuenta', 'Concepto', 'Estado', 'Observaciones','notas','Salida', 'Entrada');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Cajas") // Titulo
        ->setSubject("Reporte Excel Cajas") //Asunto
        ->setDescription("Reporte Excel Cajas") //Descripci���n
        ->setKeywords("reporte cuentas cajas") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:L1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:L3')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $dates = $this->setDateRequest($request);
        /*if ($request->input('fechas') == 8)
            $dates = $this->setDateRequest($request, true);*/

        //Código base tomado de datatable_movimientos
        
        if(!isset($request->servidor) ||  $request->servidor == 0){
        $movimientos= Movimiento::leftjoin('contactos as c', 'movimientos.contacto', '=', 'c.id')
            ->select('movimientos.*', DB::raw('if(movimientos.contacto,c.nombre,"") as nombrecliente'))
            ->where('fecha', '>=', $dates['inicio'])
            ->where('fecha', '<=', $dates['fin'])
            ->where('movimientos.empresa',Auth::user()->empresa);
        }else{
            $movimientos= Movimiento::leftjoin('contactos as c', 'movimientos.contacto', '=', 'c.id')
            ->leftjoin('ingresos_factura as if','if.ingreso','movimientos.id_modulo')
            ->leftjoin('factura as f','f.id','if.factura')
            ->leftjoin('contracts as co','co.id','f.contrato_id')
            ->select('movimientos.*', DB::raw('if(movimientos.contacto,c.nombre,"") as nombrecliente'))
            ->where('movimientos.fecha', '>=', $dates['inicio'])
            ->where('movimientos.fecha', '<=', $dates['fin'])
            ->where('movimientos.modulo',1)
            ->where('co.server_configuration_id',$request->servidor)
            ->where('movimientos.empresa',Auth::user()->empresa);

        }

        if($request->caja){
            $movimientos->where('banco',$banco->id);
        }
        if($request->tipo>0){
            $movimientos->where('movimientos.tipo',$request->tipo);
        }



        $movimientos=  $movimientos->orderBy('fecha', 'DESC')->get();

        $totales = array(
            'salida'    => 0,
            'entrada'   => 0
        );

        foreach ($movimientos as $movimientoT){
            $totales['salida']  += $movimientoT->tipo==2?$movimientoT->saldo:0;
            $totales['entrada']  += $movimientoT->tipo==1?$movimientoT->saldo:0;
        }

        $i=4;

        $estilo =array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => substr(Auth::user()->empresa()->color,1))
            ),
            'font'  => array(
                'bold'  => true,
                'size'  => 12,
                'name'  => 'Times New Roman',
                'color' => array(
                    'rgb' => 'FFFFFF'
                ),
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:L3')->applyFromArray($estilo);

        foreach ($movimientos as $movimiento) {
            $identificacion = '';

            if(isset($movimiento->contacto)){
                $identificacion .= $movimiento->cliente()->tip_iden('corta').' '.$movimiento->cliente()->nit;
            }

            $nombres ="";
            if($movimiento->cliente()){
                $nombres.=$movimiento->cliente()->nombre . " " . $movimiento->cliente()->apellidos() ;
            }

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, date('d-m-Y', strtotime($movimiento->fecha)))
                ->setCellValue($letras[1].$i, $movimiento->id_modulo)
                ->setCellValue($letras[2].$i, $nombres)
                ->setCellValue($letras[3].$i, $identificacion)
                ->setCellValue($letras[4].$i, $movimiento->padre() ? $movimiento->padre()->created_by()->nombres : '')
                ->setCellValue($letras[5].$i, $movimiento->banco()->nombre)
                ->setCellValue($letras[6].$i, $movimiento->categoria())
                ->setCellValue($letras[7].$i, $movimiento->estatus())
                ->setCellValue($letras[8].$i, $movimiento->observaciones())
                ->setCellValue($letras[9].$i, $movimiento->notas())
                ->setCellValue($letras[10].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($movimiento->tipo==2?$movimiento->saldo:0))
                ->setCellValue($letras[11].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($movimiento->tipo==1?$movimiento->saldo:0));
            $i++;
        }

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[9].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($totales['salida']))
            ->setCellValue($letras[10].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($totales['entrada']));

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:L'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de Cajas');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="REPORTE_'.$caja.'.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    
    public function facturasImpagas(Request $request){
        //Acá se obtiene la información a impimir
        DB::enableQueryLog();
        //Si es remisones se ejecuta el metodo remisiones
        if ($request->nro == 'remisiones'){
            $this->remisiones($request);
        }else{
            $comprobacionFacturas = Factura::where('factura.empresa',Auth::user()->empresa)
            ->leftjoin('contracts', 'contracts.id', '=', 'factura.contrato_id')
            ->leftjoin('mikrotik', 'mikrotik.id', '=', 'contracts.server_configuration_id')
            ->join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->select('factura.id', 'factura.codigo', 'factura.nro','factura.cot_nro', DB::raw('c.nombre as nombrecliente'),
                    'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.empresa', 'c.nit', 'c.direccion', DB::raw('c.celular as celularcliente'))
            // ->where('factura.tipo','<>',2)
            ->where('factura.estatus',1)
            ->where('c.status',1);

            if($request->servidor){
                $comprobacionFacturas=$comprobacionFacturas->where('mikrotik.id', $request->servidor);
            }
            if($request->grupo){
                $comprobacionFacturas=$comprobacionFacturas->where('contracts.grupo_corte', $request->grupo);
            }
            if($request->nro && $request->nro != 0){
                $comprobacionFacturas=$comprobacionFacturas->where('factura.numeracion', $request->nro);
            }
           

            $dates = $this->setDateRequest($request);
            $comprobacionFacturas->where('factura.fecha','>=', $dates['inicio'])->where('factura.fecha','<=', $dates['fin']);
            if($comprobacionFacturas->count() >2100){
                //return $this->bigVentas($request);
                ini_set('memory_limit', '50000M');
                set_time_limit(0);
            }

            $objPHPExcel = new PHPExcel();
            $tituloReporte = "Reporte de Facturas Impagas desde ".$request->fecha." hasta ".$request->hasta;

            $titulosColumnas = array('Nro. Factura', 'Creacion', 'Vencimiento', 'Monto', 'Cliente', 'Celular', 'Identificacion', 'Direccion', 'Corte', 'Servidor', 'IP', 'MAC');
            $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
            ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
            ->setTitle("Reporte de Facturas Impagas")
            ->setSubject("Reporte de Facturas Impagas")
            ->setDescription("Reporte de Facturas Impagas")
            ->setKeywords("Reporte de Facturas Impagas")
            ->setCategory("Reporte excel"); //Categorias
            // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
            $objPHPExcel->setActiveSheetIndex(0)
                ->mergeCells('A1:L1');
            // Se agregan los titulos del reporte
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1',$tituloReporte);
            $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ));
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->applyFromArray($estilo);
            $estilo =array('fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'd08f50')));
            $objPHPExcel->getActiveSheet()->getStyle('A3:L3')->applyFromArray($estilo);


            for ($i=0; $i <count($titulosColumnas) ; $i++) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
            }

            $facturas = Factura::where('factura.empresa',Auth::user()->empresa)
            ->leftjoin('contracts', 'contracts.id', '=', 'factura.contrato_id')
            ->leftjoin('mikrotik', 'mikrotik.id', '=', 'contracts.server_configuration_id')
            ->join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->select('factura.id', 'factura.codigo', 'factura.nro','factura.cot_nro', DB::raw('c.nombre as nombrecliente'),
                    'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.empresa', 'c.nit', 'c.direccion', DB::raw('c.celular as celularcliente'))
            // ->where('factura.tipo','<>',2)
            ->where('factura.estatus',1)
            ->where('c.status',1);
            $dates = $this->setDateRequest($request);


            if($request->servidor){
                $facturas=$facturas->where('mikrotik.id', $request->servidor);
            }
            if($request->grupo){
                $facturas=$facturas->where('contracts.grupo_corte', $request->grupo);
            }
            if($request->nro && $request->nro != 0){
                $facturas=$facturas->where('numeracion', $request->nro);
            }
            if($request->input('fechas') != 8 || (!$request->has('fechas'))){
                $facturas=$facturas->where('factura.fecha','>=', $dates['inicio'])->where('factura.fecha','<=', $dates['fin']);
            }
            $ides=array();
            
            $factures=$facturas->get();
            $facturas=$facturas->groupBy('factura.id');
            $facturas=$facturas->OrderBy('c.nombre', 'ASC')->paginate(1000000)->appends(['fechas'=>$request->fechas, 'nro'=>$request->nro, 'fecha'=>$request->fecha, 'hasta'=>$request->hasta]);

            foreach ($factures as $factura) {
                $ides[]=$factura->id;
            }

            Log::debug(DB::getQueryLog());

            $subtotal=$total=0;
            if ($ides) {
                $result=DB::table('items_factura')->whereIn('factura', $ides)->select(DB::raw("SUM((`cant`*`precio`)) as 'total', SUM((precio*(`desc`/100)*`cant`)+0)  as 'descuento', SUM((precio-(precio*(if(`desc`,`desc`,0)/100)))*(`impuesto`/100)*cant) as 'impuesto'  "))->first();
                $subtotal=$this->precision($result->total-$result->descuento);
                $total=$this->precision((float)$subtotal+$result->impuesto);
            }

            // Aquí se escribe en el archivo
            $i=4;
            
            foreach ($facturas as $factura) {
                if($factura->porpagar() == 0 && $factura->estatus == 1){
                    $factura->estatus = 0;
                    $factura->save();
                }
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($letras[0].$i, $factura->codigo)
                    ->setCellValue($letras[1].$i, date('d-m-Y', strtotime($factura->fecha)))
                    ->setCellValue($letras[2].$i, date('d-m-Y', strtotime($factura->vencimiento)))
                    ->setCellValue($letras[3].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($factura->total()->total))
                    ->setCellValue($letras[4].$i, $factura->cliente()->nombre.' '.$factura->cliente()->apellidos())
                    ->setCellValue($letras[5].$i, $factura->cliente()->celular)
                    ->setCellValue($letras[6].$i, $factura->cliente()->nit)
                    ->setCellValue($letras[7].$i, $factura->cliente()->direccion)
                    ->setCellValue($letras[8].$i, ($factura->cliente()->contrato()) ? $factura->cliente()->contrato()->grupo_corte('true') : '')
                    ->setCellValue($letras[9].$i, ($factura->cliente()->contrato()) ? ($factura->cliente()->contrato()->servidor()->nombre ?? '') : '')
                    ->setCellValue($letras[10].$i, ($factura->cliente()->contrato()) ? $factura->cliente()->contrato()->ip : '')
                    ->setCellValue($letras[11].$i, ($factura->cliente()->contrato()) ? $factura->cliente()->contrato()->mac : '');
                $i++;
            }
            /*$objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[2].$i, "TOTAL: ")
                ->setCellValue($letras[3].$i, Auth::user()->empresa()->moneda." ".Funcion::Parsear($total));*/


            $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
            $objPHPExcel->getActiveSheet()->getStyle('A3:L'.$i)->applyFromArray($estilo);


            for($i = 'A'; $i <= $letras[20]; $i++){
                $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
            }

            // Se asigna el nombre a la hoja
            $objPHPExcel->getActiveSheet()->setTitle('Reporte de Facturas Impagas');

            // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
            $objPHPExcel->setActiveSheetIndex(0);

            // Inmovilizar paneles
            $objPHPExcel->getActiveSheet(0)->freezePane('A2');
            $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
            $objPHPExcel->setActiveSheetIndex(0);
            header("Pragma: no-cache");
            header('Content-type: application/vnd.ms-excel');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Reporte_Facturas_Impagas.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            exit;

        }

    }
    
    public function recargas(Request $request) {
        $objPHPExcel = new PHPExcel();
        $tituloReporte = "Reporte de Recargas ".$request->fecha." hasta ".$request->hasta;

        $titulosColumnas = array('Nro', 'Fecha', 'Usuario', 'Monto');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Recargas") // Titulo
        ->setSubject("Reporte Excel Recargas") //Asunto
        ->setDescription("Reporte Excel Recargas") //Descripci���n
        ->setKeywords("reporte cuentas recargas") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:D1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:D3')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $dates = $this->setDateRequest($request);
        /*if ($request->input('fechas') == 8)
            $dates = $this->setDateRequest($request, true);*/

        //Código base tomado de datatable_movimientos

        $movimientos = DB::table('recargas_usuarios')->join('usuarios as u', 'u.id', '=', 'recargas_usuarios.usuario')->select('recargas_usuarios.id', 'recargas_usuarios.recarga','recargas_usuarios.fecha','u.nombres')->where('fecha', '>=', $dates['inicio'])->where('fecha', '<=', $dates['fin']);

        if($request->usuario){
            $movimientos->where('recargas_usuarios.usuario',$request->usuario);
        }

        $movimientos=  $movimientos->orderBy('fecha', 'DESC')->get();

        $totales = array(
            'recargas'    => 0
        );

        foreach ($movimientos as $movimientoT){
            $totales['recargas']  += $movimientoT->recarga;
        }

        $i=4;
        foreach ($movimientos as $movimiento) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $movimiento->id)
                ->setCellValue($letras[1].$i, date('d-m-Y', strtotime($movimiento->fecha)))
                ->setCellValue($letras[2].$i, $movimiento->nombres)
                ->setCellValue($letras[3].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($movimiento->recarga));
            $i++;
        }

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[3].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($totales['recargas']));

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:D'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de Recargas');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="REPORTE_RECARGAS.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    
    public function puntoVenta(Request $request) {
        $objPHPExcel = new PHPExcel();

        if($request->caja){
            $banco = Banco::where('id',$request->caja)->first();
            $tituloReporte = "Reporte de Punto de Venta ".$request->fecha." hasta ".$request->hasta." | ".$banco->nombre." (Ganancias)";
            $caja = $banco->nombre;
        }else{
            $tituloReporte = "Reporte de Puntos de Ventas ".$request->fecha." hasta ".$request->hasta." (Ganancias)";
        }

        $titulosColumnas = array('Fecha', 'Punto de Venta', 'Ganancia');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Puntos de Ventas") // Titulo
        ->setSubject("Reporte Excel Puntos de Ventas") //Asunto
        ->setDescription("Reporte Excel Puntos de Ventas") //Descripci���n
        ->setKeywords("reporte cuentas puntos de ventas") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:C1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => '267eb5')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:C3')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $dates = $this->setDateRequest($request);
        /*if ($request->input('fechas') == 8)
            $dates = $this->setDateRequest($request, true);*/

        //Código base tomado de datatable_movimientos
        
        $cajas = Banco::where('estatus',1)->where('tipo_cta',4)->get();
        $puntos = [];

        foreach($cajas as $caja){
            array_push($puntos, $caja->id);
        }

        $movimientos= Movimiento::leftjoin('contactos as c', 'movimientos.contacto', '=', 'c.id')
            ->select('movimientos.*', DB::raw('if(movimientos.contacto,c.nombre,"") as nombrecliente'))
            ->where('fecha', '>=', $dates['inicio'])
            ->where('fecha', '<=', $dates['fin'])
            ->where('movimientos.empresa',Auth::user()->empresa)
            // ->whereIn('movimientos.banco', [$puntos])
            ->groupBy('movimientos.fecha')
            ->groupBy('movimientos.banco');

        if($request->caja){
            $movimientos->where('banco',$banco->id);
        }
        if($request->tipo>0){
            $movimientos->where('movimientos.tipo',$request->tipo);
        }

        $movimientos=  $movimientos->orderBy('fecha', 'DESC')->get();

        $i=4;
        foreach ($movimientos as $movimiento) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, date('d-m-Y', strtotime($movimiento->fecha)))
                ->setCellValue($letras[1].$i, $movimiento->banco()->nombre)
                ->setCellValue($letras[2].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($movimiento->saldo()));
            $i++;
        }

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:C'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de Puntos de Ventas');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="REPORTE_PTOS_DE_VENTAS_GANANCIAS.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    
    public function puntoVentaRecaudo(Request $request) {
        $objPHPExcel = new PHPExcel();

        if($request->caja){
            $banco = Banco::where('id',$request->caja)->first();
            $tituloReporte = "Reporte de Punto de Venta ".$request->fecha." hasta ".$request->hasta." | ".$banco->nombre. "(Recaudos)";
            $caja = $banco->nombre;
        }else{
            $tituloReporte = "Reporte de Puntos de Ventas ".$request->fecha." hasta ".$request->hasta. "(Recaudos)";
        }

        $titulosColumnas = array('Fecha', 'Punto de Venta', 'Recaudos');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Puntos de Ventas") // Titulo
        ->setSubject("Reporte Excel Puntos de Ventas") //Asunto
        ->setDescription("Reporte Excel Puntos de Ventas") //Descripci���n
        ->setKeywords("reporte cuentas puntos de ventas") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:C1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => '267eb5')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:C3')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $dates = $this->setDateRequest($request);
        /*if ($request->input('fechas') == 8)
            $dates = $this->setDateRequest($request, true);*/

        //Código base tomado de datatable_movimientos
        
        $cajas = Banco::where('estatus',1)->where('tipo_cta',4)->get();
        $puntos = [];

        foreach($cajas as $caja){
            array_push($puntos, $caja->id);
        }

        $movimientos= Movimiento::leftjoin('contactos as c', 'movimientos.contacto', '=', 'c.id')
            ->select('movimientos.*', DB::raw('if(movimientos.contacto,c.nombre,"") as nombrecliente'))
            ->where('fecha', '>=', $dates['inicio'])
            ->where('fecha', '<=', $dates['fin'])
            ->where('movimientos.empresa',Auth::user()->empresa)
            // ->whereIn('movimientos.banco', [$puntos])
            ->groupBy('movimientos.fecha')
            ->groupBy('movimientos.banco');

        if($request->caja){
            $movimientos->where('banco',$banco->id);
        }
        if($request->tipo>0){
            $movimientos->where('movimientos.tipo',$request->tipo);
        }

        $movimientos=  $movimientos->orderBy('fecha', 'DESC')->get();

        $i=4;
        foreach ($movimientos as $movimiento) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, date('d-m-Y', strtotime($movimiento->fecha)))
                ->setCellValue($letras[1].$i, $movimiento->banco()->nombre)
                ->setCellValue($letras[2].$i, Auth::user()->empresa()->moneda.' '.Funcion::Parsear($movimiento->saldo('true')));
            $i++;
        }

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:C'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de Puntos de Ventas');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="REPORTE_PTOS_DE_VENTAS_RECAUDOS.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function ivas(Request $request){
        $objPHPExcel = new PHPExcel();

        $tituloReporte = "Reporte de Ivas ".$request->fecha." hasta ".$request->hasta;

        $titulosColumnas = array('Nro', 'Cliente', 'Fecha', 'Iva');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Ivas") // Titulo
        ->setSubject("Reporte Excel Ivas") //Asunto
        ->setDescription("Reporte Excel Ivas") //Descripci���n
        ->setKeywords("reporte cuentas Ivas") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:D1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        for ($i = 0; $i < count($titulosColumnas); $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i] . '3',
                utf8_decode($titulosColumnas[$i]));
        }
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($estilo);
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => '267eb5')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:D3')->applyFromArray($estilo);

        $dates = $this->setDateRequest($request);
        
        $empresa = Auth::user()->empresa;

        if(!isset($request->documento)){
            $request->documento = 2;
        }

        if($request->fecha){
            $appends['fecha']=$request->fecha;
        }
        if($request->fecha){
            $appends['hasta']=$request->hasta;
        }
        if($request->documento == 1){
            $documentos = Factura::leftjoin('contactos as c', 'cliente', '=', 'c.id')
            ->select('factura.*', 'c.nombre', 'factura.codigo as nro')
            ->where('fecha', '>=', $dates['inicio'])
            ->where('fecha', '<=', $dates['fin'])
            ->where('tipo',2)
            ->where('factura.empresa',$empresa);
        }elseif($request->documento == 2){
            $documentos = NotaCredito::leftjoin('contactos as c', 'cliente', '=', 'c.id')
            ->select('notas_credito.*', 'c.nombre')
            ->where('fecha', '>=', $dates['inicio'])
            ->where('fecha', '<=', $dates['fin'])
            ->where('notas_credito.empresa',$empresa);
        }

        $documentos = $documentos->orderBy('fecha', 'DESC')->get();

        $totalIva = 0;
        foreach($documentos as $doc){
            $totalIva+=$doc->impuestos_totales();
        }

        $i=4;
        foreach ($documentos as $documento) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $documento->nro)
                ->setCellValue($letras[1].$i, $documento->cliente()->nombre)
                ->setCellValue($letras[2].$i, date('d-m-Y', strtotime($documento->fecha)))
                ->setCellValue($letras[3].$i, $documento->impuestos_totales());
            $i++;
        }
        
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue($letras[2] . $i, "TOTAL: ")
        ->setCellValue($letras[3] . $i, $totalIva);

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:D'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de Ivas');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if($request->documento == 1){
            header('Content-Disposition: attachment;filename="REPORTE_IVAS.xlsx"');
        }elseif($request->documento == 2){
            header('Content-Disposition: attachment;filename="REPORTE_IVAS_NOTAS_CREDITO.xlsx"');
        }
        
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function radicados(Request $request) {
        $this->getAllPermissions(Auth::user()->id);
        $objPHPExcel = new PHPExcel();
        $tituloReporte = "Reporte de Radicados";
        $titulosColumnas = array('Codigo', 'Fecha', 'Cliente', 'Identificacion', 'Celular', 'Correo Electronico', 'Direccion', 'Contrato', 'Direccion IP', 'Direccion MAC', 'Servicio', 'Tecnico', 'Estimado', 'Iniciado', 'Finalizado', 'Duracion', 'Prioridad', 'Estado');

        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific�1�7�1�7�1�7
        ->setTitle("Reporte Excel Radicados") // Titulo
        ->setSubject("Reporte Excel Radicados") //Asunto
        ->setDescription("Reporte de Radicados") //Descripci�1�7�1�7�1�7n
        ->setKeywords("reporte Radicados") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah�1�7�1�7�1�7 el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:R1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        // Titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A2:R2');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2','Fecha '.date('d-m-Y')); // Titulo del reporte

        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:R3')->applyFromArray($estilo);

        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:R3')->applyFromArray($estilo);

        $estilo =array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => substr(Auth::user()->empresa()->color,1))
            ),
            'font'  => array(
                'bold'  => true,
                'size'  => 12,
                'name'  => 'Times New Roman',
                'color' => array(
                    'rgb' => 'FFFFFF'
                ),
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A3:R3')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $i=4;
        $letra=0;

        $dates = $this->setDateRequest($request);

        $empresa = Auth::user()->empresa;
        $radicados = Radicado::where('id', '>', 0)->where('fecha', '>=', $dates['inicio'])->where('fecha', '<=', $dates['fin']);

        if($request->fecha){
            $appends['fecha']=$request->fecha;
        }
        if($request->fecha){
            $appends['hasta']=$request->hasta;
        }
        if($request->tecnico){
            $radicados->where('radicados.tecnico',$request->tecnico);
        }
        if($request->servicio){
            $radicados->where('radicados.servicio',$request->servicio);
        }
        if($request->estatus){
            $radicados->where('radicados.estatus',$request->estatus);
        }

        $radicados=  $radicados->orderBy('fecha', 'DESC')->get();

        $i=4;
        foreach ($radicados as $radicado) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $radicado->codigo)
                ->setCellValue($letras[1].$i, date('d-m-Y', strtotime($radicado->fecha)))
                ->setCellValue($letras[2].$i, $radicado->nombre)
                ->setCellValue($letras[3].$i, $radicado->identificacion)
                ->setCellValue($letras[4].$i, $radicado->telefono)
                ->setCellValue($letras[5].$i, $radicado->correo)
                ->setCellValue($letras[6].$i, $radicado->direccion)
                ->setCellValue($letras[7].$i, ($radicado->contrato) ? $radicado->contrato : '')
                ->setCellValue($letras[8].$i, ($radicado->ip) ? $radicado->ip : '')
                ->setCellValue($letras[9].$i, ($radicado->mac_address) ? $radicado->mac_address : '')
                ->setCellValue($letras[10].$i, ($radicado->servicio) ? $radicado->servicio()->nombre : '')
                ->setCellValue($letras[11].$i, ($radicado->tecnico) ? $radicado->tecnico()->nombres : '')
                ->setCellValue($letras[12].$i, ($radicado->tiempo_est) ? $radicado->tiempo_est.' min' : '')
                ->setCellValue($letras[13].$i, ($radicado->tiempo_ini) ? date('d-m-Y g:i:s A', strtotime($radicado->tiempo_ini)) : '')
                ->setCellValue($letras[14].$i, ($radicado->tiempo_fin) ? date('d-m-Y g:i:s A', strtotime($radicado->tiempo_fin)) : '')
                ->setCellValue($letras[15].$i, ($radicado->tiempo_ini && $radicado->tiempo_fin) ? $radicado->duracion() : '')
                ->setCellValue($letras[16].$i, $radicado->prioridad())
                ->setCellValue($letras[17].$i, $radicado->estatus());
            $i++;
        }

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:R'.$i)->applyFromArray($estilo);


        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de Radicados');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="REPORTE_RADIADOS.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function notasCredito(Request $request){
        //Acá se obtiene la información a impimir
        DB::enableQueryLog();

        $objPHPExcel = new PHPExcel();
        $tituloReporte = "Reporte de notas crédito desde " . $request->fecha . " hasta " . $request->hasta;

        $titulosColumnas = array(
            'Nro.',
            'Factura',
            'Cliente',
            'Creacion',
            'Subtotal',
            'IVA',
            'Retencion',
            'Total'
        );
        $letras = array(
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z'
        );
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Notas Crédito") // Titulo
        ->setSubject("Reporte Excel Notas Crédito") //Asunto
        ->setDescription("Reporte de Notas Crédito") //Descripci���n
        ->setKeywords("reporte Notas Crédito") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:I1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', $tituloReporte);
        $estilo = array(
            'font' => array('bold' => true, 'size' => 12, 'name' => 'Times New Roman'),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray($estilo);
        $estilo = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'd08f50')
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:H3')->applyFromArray($estilo);


        for ($i = 0; $i < count($titulosColumnas); $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i] . '3', utf8_decode($titulosColumnas[$i]));
        }

        if($request->fechas != 8){
            if (!$request->fecha) {
                $arrayDate = $this->setDateRequest($request);
                $desde = $arrayDate['inicio'];
                $hasta = $arrayDate['fin'];
            } else {
                $desde = Carbon::parse($request->fecha)->format('Y-m-d');
                $hasta = Carbon::parse($request->hasta)->format('Y-m-d');
            }
        }else{
            $desde = '2000-01-01';
            $hasta = now()->format('Y-m-d');
        }

        $notasc = NotaCredito::join('contactos as c', 'notas_credito.cliente', '=', 'c.id')
            ->join('notas_factura as nf', 'nf.nota', '=', 'notas_credito.id')
            ->join('factura as f', 'f.id', '=', 'nf.factura')
            ->where('notas_credito.empresa', auth()->user()->empresa)
            ->where('notas_credito.fecha', '>=', $desde)->where('notas_credito.fecha', '<=', $hasta)
            ->select('notas_credito.id as id', 'notas_credito.nro', 'c.id as cliente', 'c.nombre',
                'notas_credito.fecha', 'f.id as fid', 'f.codigo', 'f.nro as fnro')
            ->orderBy('id', 'DESC')->groupBy('notas_credito.id');

        $notasc = $notasc->paginate(1000000)->appends([
            'fechas' => $request->fechas,
            'nro' => $request->nro,
            'fecha' => $request->fecha,
            'hasta' => $request->hasta
        ]);

        //variable la cual identifica lo que se debe descontar por devoluciones pagadas.
        $devoluciones = 0;
        $retenciones = 0;
        $subtotal = 0;
        $total = 0;
        $iva = 0;
        $saldosR = 0;

        foreach ($notasc as $nota) {
            $subtotal = $subtotal + $nota->subtotal = ($nota->total()->subtotal - $nota->total()->descuento);
            $iva = $iva + $nota->iva = $nota->impuestos_totales();
            $retenciones = $retenciones + $nota->retenido = $nota->retenido_factura();
            $total = $total + $nota->total = $nota->total()->total;
            $saldosR = $saldosR + $nota->saldoRestante = $nota->por_aplicar();
        }

        // Aquí se escribe en el archivo
        $i = 4;
        foreach ($notasc as $nota) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0] . $i, $nota->nro)
                ->setCellValue($letras[1] . $i, $nota->codigo)
                ->setCellValue($letras[2] . $i, $nota->cliente()->nombre)
                ->setCellValue($letras[3] . $i, date('d-m-Y', strtotime($nota->fecha)))
                ->setCellValue($letras[4] . $i, $nota->subtotal)
                ->setCellValue($letras[5] . $i, $nota->iva)
                ->setCellValue($letras[6] . $i, $nota->retenido)
                ->setCellValue($letras[7] . $i, $nota->total);
            $i++;
        }
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[3] . $i, "TOTAL: ")
            ->setCellValue($letras[4] . $i, $subtotal)
            ->setCellValue($letras[5] . $i, $iva)
            ->setCellValue($letras[6] . $i, $retenciones)
            ->setCellValue($letras[7] . $i, $total);


        $estilo = array(
            'font' => array('size' => 12, 'name' => 'Times New Roman'),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:H' . $i)->applyFromArray($estilo);


        for ($i = 'A'; $i <= $letras[20]; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(true);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de Notas Crédito');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0, 4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_notascredito.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function balance(Request $request){
         //Acá se obtiene la información a impimir
         DB::enableQueryLog();

         $objPHPExcel = new PHPExcel();
         $tituloReporte = "Reporte de Balances desde " . $request->fecha . " hasta " . $request->hasta;
 
         $titulosColumnas = array(
             'Nombre',
             'Codigo',
             'Debito',
             'Credito',
             'Saldo Final',
         );
         $letras = array(
             'A',
             'B',
             'C',
             'D',
             'E',
             'F',
             'G',
             'H',
             'I',
             'J',
             'K',
             'L',
             'M',
             'N',
             'O',
             'P',
             'Q',
             'R',
             'S',
             'T',
             'U',
             'V',
             'W',
             'X',
             'Y',
             'Z'
         );
         $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
         ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
         ->setTitle("Reporte Excel Balances") // Titulo
         ->setSubject("Reporte Excel Balances") //Asunto
         ->setDescription("Reporte de Balances") //Descripci���n
         ->setKeywords("reporte Balances") //Etiquetas
         ->setCategory("Reporte excel"); //Categorias
         // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
         $objPHPExcel->setActiveSheetIndex(0)
             ->mergeCells('A1:I1');
         // Se agregan los titulos del reporte
         $objPHPExcel->setActiveSheetIndex(0)
             ->setCellValue('A1', $tituloReporte);
         $estilo = array(
             'font' => array('bold' => true, 'size' => 12, 'name' => 'Times New Roman'),
             'alignment' => array(
                 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
             )
         );
         $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->applyFromArray($estilo);
         $estilo = array(
             'fill' => array(
                 'type' => PHPExcel_Style_Fill::FILL_SOLID,
                 'color' => array('rgb' => 'd08f50')
             )
         );
         $objPHPExcel->getActiveSheet()->getStyle('A3:E3')->applyFromArray($estilo);
 
 
         for ($i = 0; $i < count($titulosColumnas); $i++) {
             $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i] . '3', utf8_decode($titulosColumnas[$i]));
         }
 
         if($request->fechas != 8){
             if (!$request->fecha) {
                 $arrayDate = $this->setDateRequest($request);
                 $desde = $arrayDate['inicio'];
                 $hasta = $arrayDate['fin'];
             } else {
                 $desde = Carbon::parse($request->fecha)->format('Y-m-d');
                 $hasta = Carbon::parse($request->hasta)->format('Y-m-d');
             }
         }else{
             $desde = '2000-01-01';
             $hasta = now()->format('Y-m-d');
         }
             
        $movimientosContables = PucMovimiento::join('puc as p','p.id','puc_movimiento.cuenta_id')           
        ->select('puc_movimiento.*','p.nombre as cuentacontable',
        DB::raw("SUM((`debito`)) as totaldebito"), 
        DB::raw("SUM((`credito`)) as totalcredito"),
        DB::raw("ABS(SUM((`credito`)) -  SUM((`debito`))) as totalfinal"))
        ->orderBy('id', 'DESC')
        ->groupBy('cuenta_id')
        ->get();

 
         // Aquí se escribe en el archivo
         $i = 4;
         foreach ($movimientosContables as $mov) {
             $objPHPExcel->setActiveSheetIndex(0)
                 ->setCellValue($letras[0] . $i, $mov->cuentacontable)
                 ->setCellValue($letras[1] . $i, $mov->codigo_cuenta)
                 ->setCellValue($letras[2] . $i, Auth::user()->empresa()->moneda . Funcion::Parsear($mov->totaldebito))
                 ->setCellValue($letras[3] . $i, Auth::user()->empresa()->moneda . Funcion::Parsear($mov->totalcredito))
                 ->setCellValue($letras[4] . $i, Auth::user()->empresa()->moneda . Funcion::Parsear($mov->totalfinal));
             $i++;
         }
 
         $estilo = array(
             'font' => array('size' => 12, 'name' => 'Times New Roman'),
             'borders' => array(
                 'allborders' => array(
                     'style' => PHPExcel_Style_Border::BORDER_THIN
                 )
             ),
             'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
         );
         $objPHPExcel->getActiveSheet()->getStyle('A3:E' . $i)->applyFromArray($estilo);
 
 
         for ($i = 'A'; $i <= $letras[20]; $i++) {
             $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(true);
         }
 
         // Se asigna el nombre a la hoja
         $objPHPExcel->getActiveSheet()->setTitle('Reporte de Balances');
 
         // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
         $objPHPExcel->setActiveSheetIndex(0);
 
         // Inmovilizar paneles
         $objPHPExcel->getActiveSheet(0)->freezePane('A2');
         $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0, 4);
         $objPHPExcel->setActiveSheetIndex(0);
         header("Pragma: no-cache");
         header('Content-type: application/vnd.ms-excel');
         header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
         header('Content-Disposition: attachment;filename="Reporte_balance.xlsx"');
         header('Cache-Control: max-age=0');
         $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
         $objWriter->save('php://output');
         exit;
    }

    public function descargar_resumen_prestacion_social(Request $request)
    {
        $objPHPExcel = new PHPExcel();
        $year = $request->year;
        $periodo = $request->periodo;
        $desde = $request->desde;
        $hasta = $request->hasta;

        $tituloReporte = "Prestaciones sociales ({$request->title}) " . Auth::user()->empresaObj->nombre;
        $titulosColumnas = array(
            'Numero identificacion',
            'Nombre',
            'Dias trabajados',
            'Valor base',
            'Valor por pagar',
            'Valor total pagado',
            'Valor pagado en el periodo',
            'Valor total prima de servicios'
        );
        $letras = array(
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z'
        );

        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Prestaciones sociales") // Titulo
        ->setSubject("Reporte Excel Prestaciones sociales") //Asunto
        ->setDescription("Reporte Excel Prestaciones sociales") //Descripci���n
        ->setKeywords("Reporte Excel Prestaciones sociales") //Etiquetas
        ->setCategory("Reporte Excel Prestaciones sociales"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:D1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', $tituloReporte);
        // Titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A2:C2');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2', 'Fecha ' . date('d-m-Y')); // Titulo del reporte

        $estilo = array(
            'font' => array('bold' => true, 'size' => 12, 'name' => 'Times New Roman'),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:V1')->applyFromArray($estilo);

        $estilo = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'c6c8cc')
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:V3')->applyFromArray($estilo);


        for ($i = 0; $i < count($titulosColumnas); $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i] . '3', utf8_decode($titulosColumnas[$i]));
        }

        $i = 4;
        $letra = 0;
        $dates = $this->setDateRequest($request);

        $nominas = Nomina::with('nominaperiodos')
            ->where('ne_nomina.year', $year)
            ->where('fk_idempresa', Auth::user()->empresa);


        if ($desde && $hasta) {
            $nominas->where('periodo', '>=', $desde);
            $nominas->where('periodo', '<=', $hasta);
        }

        $nominas = $nominas->get();

        $totalidades = [];

        foreach ($nominas as $nomina) {
            foreach ($nomina->nominaperiodos as $nominaPeriodo) {
                if (!isset($totalidades[$nomina->fk_idpersona])) {
                    $totalidades[$nomina->fk_idpersona] = [];
                }

                $totalidad = $nominaPeriodo->resumenTotal();
                $totalidad['idNomina'] = $nomina->id;

                $totalidades[$nomina->fk_idpersona][] = $totalidad;
            }
        }

        $totalidadesPersonas = collect($totalidades);
        $personas = Persona::whereIn('id', $totalidadesPersonas->keys())->get();

        $tipo = $request->tipo;

        foreach ($personas as $key => $persona) {
            $persona->nominaSeleccionada = $nominas->where('periodo', $periodo)->where('fk_idpersona',
                $persona->id)->first();
            if (!$persona->nominaSeleccionada) {
                unset($personas[$key]);
                continue;
            }
            $persona->prestacionSocial = $persona->nominaSeleccionada->$tipo;
            if (!$persona->prestacionSocial) {
                unset($personas[$key]);
            }
        }

        $empresa = Empresa::find(Auth::user()->empresa);


        foreach ($personas as $persona) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0] . $i, $persona->nro_documento . '')
                ->setCellValue($letras[1] . $i, $persona->nombre())
                ->setCellValue($letras[2] . $i, $persona->prestacionSocial->dias_trabajados)
                ->setCellValue($letras[3] . $i, $persona->prestacionSocial->base)
                ->setCellValue($letras[4] . $i,
                    $persona->prestacionSocial->valor - $persona->prestacionSocial->valor_pagar)
                ->setCellValue($letras[5] . $i, $persona->prestacionSocial->valor_pagar)
                ->setCellValue($letras[6] . $i, $persona->prestacionSocial->valor)
                ->setCellValue($letras[7] . $i, $persona->prestacionSocial->valor);
            $i++;
        }

        $estilo = array(
            'font' => array('size' => 12, 'name' => 'Times New Roman'),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:V' . $i)->applyFromArray($estilo);

        for ($i = 'A'; $i <= $letras[20]; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(true);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle($tipo);

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A5');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0, 4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Contactos.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
}
