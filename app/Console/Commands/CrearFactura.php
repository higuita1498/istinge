<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Model\Ingresos\FacturaRecurrente; 
use App\NumeracionFactura;
use App\Factura;  
use App\Model\Inventario\Bodega; 
use  App\Model\Ingresos\ItemsFacturaRecurrente;
use App\Model\Inventario\ProductosBodega; 
use App\ItemsFactura; 

class CrearFactura extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facturas:end';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea facturas de facturas recurrentes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {        
        $recurrentes = FacturaRecurrente::Where('proxima', date('Y-m-d'))->where(function ($query) {
          $query->where('vencimiento', '>=', date('Y-m-d'))
          ->orwhereNull('vencimiento');
        } )->get();

        foreach ($recurrentes as $recurrente) {
          $nro=NumeracionFactura::where('empresa',$recurrente->empresa)->where('preferida',1)->where('estado',1)->first();

          $factura = new Factura;    
          $factura->nro=Factura::where('empresa',$recurrente->empresa)->where('tipo',1)->count()+1;
          $factura->codigo=$nro->prefijo.$nro->inicio;
          $factura->numeracion=$nro->id;
          $factura->plazo=$recurrente->plazo;
          $factura->term_cond=$recurrente->term_cond;
          $factura->facnotas=$recurrente->notas;      
          $factura->empresa=$recurrente->empresa;
          $factura->cliente=$recurrente->cliente;
          $factura->fecha=date('Y-m-d');
          $factura->vencimiento=date('Y-m-d', strtotime("+".$recurrente->plazo(true)." days", strtotime($factura->fecha)));      
          $factura->observaciones=mb_strtolower('Realizada de facturas recuerrentes');
          $factura->lista_precios=$recurrente->lista_precios;
          $factura->bodega=$recurrente->bodega; 
          $factura->save();
     
          $bodega = Bodega::where('empresa',$recurrente->empresa)->where('status', 1)->where('id', $recurrente->bodega)->first();
          if (!$bodega) { //Si el valor seleccionado para bodega no existe, tomara la primera activa registrada
            $bodega = Bodega::where('empresa',$recurrente->empresa)->where('status', 1)->first();
          }
          $items = ItemsFacturaRecurrente::where('factura_recurrente',$recurrente->id)->get();     
          foreach ($items as $item) {
            if ($item->producto()->tipo_producto==1) {
              $ajuste=ProductosBodega::where('empresa', $recurrente->empresa)->where('bodega', $bodega->id)->where('producto', $item->producto)->first();
              if ($ajuste) {
                $ajuste->nro-=$item->cant;
                $ajuste->save();
              }
            }

            $item_reg = new ItemsFactura;
            $item_reg->factura=$factura->id;
            $item_reg->producto=$item->producto;
            $item_reg->ref=$item->ref;
            $item_reg->precio=$item->precio; 
            $item_reg->descripcion=$item->descripcion;
            $item_reg->id_impuesto=$item->id_impuesto;
            $item_reg->impuesto=$item->impuesto;
            $item_reg->cant=$item->cant;
            $item_reg->desc=$item->desc;
            $item_reg->save();
          }

          $fecha=$factura->fecha;
          while (true) {
            $fecha=date('Y-m-d', strtotime("+".$recurrente->frecuencia." month", strtotime($factura->fecha)));
            if ($fecha>date('Y-m-d')) { break; }
          }
          $recurrente->proxima=$fecha;
          $recurrente->save();
        }

        echo "se han creado las facturas";
    }
    

}
