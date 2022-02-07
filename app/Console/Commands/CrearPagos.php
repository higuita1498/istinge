<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Model\Gastos\GastosRecurrentesCategoria; 
use App\Model\Gastos\GastosRecurrentes;
use App\Model\Gastos\Gastos;
use App\Model\Gastos\GastosCategoria;
use App\Movimiento;
class CrearPagos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pagos:end';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea pagos de pagos recurrentes';

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
      $recurrentes = GastosRecurrentes::Where('proxima', date('Y-m-d'))->where(function ($query) {
        $query->where('vencimiento', '>=', date('Y-m-d'))
        ->orwhereNull('vencimiento');
      } )->get();

      foreach ($recurrentes as $recurrente) {
        $gasto = new Gastos;
        $gasto->nro=Gastos::where('empresa',$recurrente->empresa)->count()+1;
        $gasto->empresa=$recurrente->empresa;
        $gasto->beneficiario=$recurrente->beneficiario;
        $gasto->cuenta=$recurrente->cuenta;
        $gasto->metodo_pago=$recurrente->metodo_pago;
        $gasto->notas=$recurrente->notas;
        $gasto->tipo=2;
        $gasto->fecha=date('Y-m-d');
        $gasto->observaciones=mb_strtolower('Realizada de pagos recuerrentes');
        $gasto->save();
        $items=GastosRecurrentesCategoria::where('gasto_recurrente',$recurrente->id)->get();
        foreach ($items as $key => $value) {
          $items = new GastosCategoria;
          $items->valor=$value->valor;
          $items->id_impuesto=$value->id_impuesto;
          $items->impuesto=$value->impuesto;
          $items->gasto=$gasto->id;
          $items->categoria=$value->categoria;
          $items->cant=$value->cant;
          $items->descripcion=$value->descripcion;
          $items->save(); 
        }

         $fecha=$gasto->fecha;
        while (true) {
          $fecha=date('Y-m-d', strtotime("+".$recurrente->frecuencia." month", strtotime($gasto->fecha)));
          if ($fecha>date('Y-m-d')) { break; }
        }
        $recurrente->proxima=$fecha;
        $recurrente->save();


        $movimiento=new Movimiento;
        $movimiento->empresa=$recurrente->empresa;
        $movimiento->banco=$recurrente->cuenta;
        $movimiento->contacto=$recurrente->beneficiario;
        $movimiento->tipo=2;
        $movimiento->saldo=$recurrente->total()->total;
        $movimiento->fecha=date('Y-m-d');
        $movimiento->modulo=3;
        $movimiento->id_modulo=$gasto->id;
        $movimiento->descripcion=mb_strtolower('Realizada de pagos recuerrentes');;
        $movimiento->save();

      }
      echo "se han creado los pagos";
    }
    

}
