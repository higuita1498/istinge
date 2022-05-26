<?php

namespace App\Model\Inventario;

use Illuminate\Database\Eloquent\Model; 
use App\Funcion; use App\Impuesto;
use App\Categoria;
use App\ProductoCuenta;
use Auth;  use App\NotaCredito; 
use App\Model\Ingresos\ItemsFactura;  
use App\Model\Ingresos\Factura;
use App\Model\Ingresos\ItemsNotaCredito; 
use App\Model\Ingresos\ItemsRemision; 
use App\Model\Ingresos\Remision;
use App\Model\Ingresos\Cotizacion; 
use App\Model\Inventario\ProductosBodega;
use App\Model\Inventario\ProductosTransferencia;
use App\Model\Inventario\ListaPrecios; 
use App\Model\Inventario\AjusteInventario; 
use App\Model\Gastos\ItemsFacturaProv;
use DB; 
class Inventario extends Model
{
    protected $table = "inventario";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     * 
     * @var array
     */
    protected $fillable = [
        'empresa', 'producto', 'ref', 'precio', 'descripcion', 'impuesto', 'id_impuesto', 'imagen', 'nro', 'categoria',
        'inicial', 'unidad', 'status',  'created_at', 'updated_at', 'tipo_producto', 'publico', 'costo_unidad', 'lista', 'link', 'type'
    ];
    public function status(){
      return $this->status==1?'Activo':'Inactivo';
    }
    public function precio(){
      return ListaPrecios::where('empresa', Auth::user()->empresa)->first()->nombre;
    }


    public function impuesto($valor=false){
      if (!$valor) {
        if ($this->impuesto==0) {
          return 'Ninguno';
        }
        return $this->impuesto."%";
      } 

      return Impuesto::find($this->id_impuesto);
    }

    public function categoria(){
      if ($this->categoria) {
      return Categoria::where('id',$this->categoria)->first()->nombre;
      }  
    }

    public function unidad($tipo=false){
        if ($tipo) {
           $tipo= DB::table('unidades_medida')->where('id',$this->unidad)->first()->unidad;
           if (count(explode('(', $tipo))>1) {
              $tipo=explode('(', $tipo)[1];
              $tipo=explode(')', $tipo)[0];
              return $tipo;
           }
           return '';
        }
        return DB::table('unidades_medida')->where('id',$this->unidad)->first()->unidad;
    }

    public function uso(){ 
        return ItemsFactura::where('producto',$this->id)->count()+ItemsNotaCredito::where('producto',$this->id)->count()+ItemsRemision::where('producto',$this->id)->count()+DB::table('imagenesxinventario')->where('producto', $this->id)->count()+ProductosPrecios::where('empresa', Auth::user()->empresa)->where('producto', $this->id)->count()+ProductosTransferencia::where('producto', $this->id)->count()+AjusteInventario::where('producto', $this->id)->count() + ProductosTransferencia::where('producto', $this->id)->count() + ItemsFacturaProv::where('producto', $this->id)->count();
    }


    public function campoExt($campo)
    {
        /*$campo=DB::table('inventario_meta')->select('meta_value')->where('empresa', Auth::user()->empresa)
            ->where('meta_key', $campo)->where('id_producto', $this->id)->first();*/

        $campo = DB::table('inventario_meta')->selectRaw('GROUP_CONCAT(meta_value) as meta_value')
            ->where('empresa', Auth::user()->empresa)->where('meta_key', $campo)
            ->where('id_producto', $this->id)->first();

        if ($campo) {
            return $campo->meta_value;
        }
    }

    public function campoExt2($campo)
    {
        $campo=DB::table('inventario_meta')->select('meta_value')->where('empresa', Auth::user()->empresa)
            ->where('meta_key', $campo)->where('id_producto', $this->id)->first();

        /*$campo = DB::table('inventario_meta')->selectRaw('GROUP_CONCAT(meta_value) as meta_value')
            ->where('empresa', Auth::user()->empresa)->where('meta_key', $campo)
            ->where('id_producto', $this->id)->first();*/

        if ($campo) {
            return $campo->meta_value;
        }
    }

    public function imagenes(){
      return DB::table('imagenesxinventario')->where('producto', $this->id)->orderBy('id', 'desc')->get(); 
    }

  public function notas_credito($count=false){
    
   $nota=DB::table('items_notas')->where('producto', $this->id)->groupBy('nota')->select('nota'); 
    if ($count) {
      return $nota->count();
    }
    $nota=$nota->get();
    $id=array();
    foreach ($nota as $key => $value) {
      $id[]=$value->nota;
    }
    return NotaCredito::whereIn('id', $id)->orderBy('id', 'desc')->get();


  }


  public function cotizaciones($count=false){
    $facturas=DB::table('items_factura')->where('producto', $this->id)->where('tipo_inventario', 1)->groupBy('factura')->select('factura'); 
    if ($count) {
      return $facturas->count();
    }
    $facturas=$facturas->get();
    $id=array();
    foreach ($facturas as $key => $value) {
      $id[]=$value->factura;
    }
    return Cotizacion::whereIn('id', $id)->where('tipo', 2)->orderBy('id', 'desc')->get();
  }

  public function remisiones($count=false){
    $remisiones=DB::table('items_remision')->where('producto', $this->id)->groupBy('remision')->select('remision'); 
    if ($count) {
      return $remisiones->count();
    }
    $remisiones=$remisiones->get();
    $id=array();
    foreach ($remisiones as $key => $value) {
      $id[]=$value->remision;
    }
    return Remision::whereIn('id', $id)->orderBy('id', 'desc')->get();
  }

  

  public function bodegas(){
    return ProductosBodega::where('empresa', Auth::user()->empresa)->where('producto', $this->id)->get();
  }
  public function precios(){
    return ProductosPrecios::where('empresa', Auth::user()->empresa)->where('producto', $this->id)->get();
  }

  public function inventario($tipo='nro'){
    return ProductosBodega::where('empresa', Auth::user()->empresa)->where('producto', $this->id)->sum($tipo);
  }

  public function web($class=null){
    if ($class) {
      return $this->publico==1?'text-success':'';
    }

    return $this->publico==1?'Público':'';

  }

  //Obtener el tipo de producto

    public function esInventariable()
    {
        return $this->tipo_producto == 1;
    }

    public function inventarioBodega($idBodega)
    {
        return ProductosBodega::where('empresa', Auth::user()->empresa)
            ->where('producto', $this->id)
            ->where('bodega', $idBodega)
            ->first()
            ->nro;
    }


    public function categoriaId()
    {
        return Categoria::where('id',$this->categoria)->first();
    }

    public function lista ()
    {

        switch ($this->lista)
        {
            case 0:
                return "Ninguna";
                break;
            case 1:
                return "Más vendidos";
                break;
            case 2:
                return "Recientes";
                break;
            case 3:
                return "Oferta";
                break;
        }

    }

    public function cuentas(){
      return ProductoCuenta::where('inventario_id',$this->id)->get();
    }

    public function booleanCuentas(){
      $cuentas = ProductoCuenta::where('inventario_id',$this->id)->get();

      if($cuentas->count() > 0){
        return 1;
      }else{
        return 0;
      }
    }

}
