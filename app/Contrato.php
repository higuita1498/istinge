<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
use App\Mikrotik;
use App\Model\Ingresos\Ingreso;
use App\Model\Ingresos\IngresosFactura;
use App\Model\Ingresos\Factura;
use App\Nodo;
use App\AP;
use App\GrupoCorte;
use App\Puerto;
use App\Ping;
use App\PlanesVelocidad;
use App\Model\Inventario\Inventario;
use App\Vendedor;
use App\Canal;
use App\Oficina;
use stdClass;

class Contrato extends Model
{
    protected $table = "contracts";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $timestamps = false;
    protected $fillable = [
        'nro', 'plan_id', 'client_id', 'server_configuration_id', 'state', 'ip', 'fecha_corte', 'fecha_suspension',
        'usuario', 'password', 'interfaz', 'conexion', 'status', 'id_vlan', 'name_vlan', 'grupo_corte', 'created_at',
        'updated_at', 'puerto_conexion', 'factura_individual', 'contrato_permanencia', 'contrato_permanencia_meses',
        'costo_reconexion', 'tipo_contrato', 'observaciones','tipo_nosuspension','fecha_hasta_nosuspension','fecha_desde_nosuspension'
    ];

    protected $appends = ['status'];

    public function getStatusAttribute()
    {
        return $this->status();
    }

    public function status($class=false){
        if($class){
            return $this->state == 'enabled' ? 'success' : 'danger';
        }
        return $this->state == 'enabled' ? 'Habilitado' : 'Deshabilitado';
    }

    public function cliente(){
        return Contacto::where('id', $this->client_id)->first();
    }

	public function plan($tv = false){
        if($tv){
            return Inventario::find($this->servicio_tv);
        }
		return PlanesVelocidad::where('id', $this->plan_id)->first();
	}

    public function usado(){
        $tmp        = 0;
        $tmp        += Factura::where('cliente', $this->id)->count();
        return $tmp;
    }

    public function servidor(){
        return Mikrotik::find($this->server_configuration_id);
    }

    public function conexion(){
        if($this->conexion == 1){
            return 'PPPOE';
        } elseif($this->conexion == 2){
            return 'DHCP';
        } elseif($this->conexion == 3){
            return 'IP Estática';
        } elseif($this->conexion == 4){
            return 'VLAN';
        }
    }

    public function corte(){
        if($this->fecha_corte == 0 || $this->fecha_corte == '' || $this->fecha_corte == null){
            return 'No Asignada';
        } elseif($this->fecha_corte == 50){
            return 'Plan Gratis';
        } else{
            return $this->fecha_corte.' de cada mes';
        }
    }

    public function pago($id){
        return Ingreso::where('cliente', $id)->where('tipo', 1)->get()->last();
    }

    public static function tipos()
    {
        $tipos = array(array('state'=>'enabled', 'nombre'=>'CLIENTES HABILITADOS'), array('state'=>'disabled', 'nombre'=>'CLIENTES DESHABILITADOS'));
        $cont=0;
        $nuevos=array();
        foreach ($tipos as $tipo) {
            $cont=Contrato::where('state', $tipo["state"])->count();
            if ($cont>0) {
                $nuevos[]=$tipo;
            }
        }
        return (object) $nuevos;
    }

    public function nodo(){
        if($this->nodo){
            return Nodo::find($this->nodo);
        }
        return 'N/A';
    }

    public function ap(){
        if($this->ap){
            return AP::find($this->ap);
        }
        return 'N/A';
    }

    public function marca_antena(){
        return DB::table('marcas')->where('id', $this->marca_antena)->first();
    }

    public function marca_router(){
        return DB::table('marcas')->where('id', $this->marca_router)->first();
    }

    public function grupo_corte($class=false){
        if($class){
            $grupo = GrupoCorte::find($this->grupo_corte);
            if($grupo){
                return $grupo->nombre.'(CORTE '.$grupo->fecha_corte.' - SUSPENSIÓN '.$grupo->fecha_suspension.')';
            }else{
                return 'SIN GRUPO ASOCIADO';
            }
        }
        return GrupoCorte::find($this->grupo_corte);
    }

    public function plug($class=false){
        if($this->ip){
            $ping = Ping::where('ip', $this->ip)->first();
            if($ping){
                if($class){
                    return 'danger';
                }
                return 'Desconectado';
            }else{
                if($class){
                    return 'primary';
                }
                return 'Conectado';
            }
        }

        if($class){
            return ($this->state == 'disabled') ? 'danger' : 'primary';
        }else{
            return ($this->state == 'disabled') ? 'Desconectado' : 'Conectado';
        }
    }

    public function factura(){
        $factura = Factura::where('cliente', $this->c_id)->get()->last();
        if($factura){
            return "<a href=".route('facturas.show', $factura->id)." target='_blank'>$factura->codigo</a>";
        }
        return 'N/A';
    }

    public function lastFactura(){
        $factura = Factura::where('cliente', $this->client_id)->where('contrato_id', $this->id)->latest()->first();
        return $factura;
    }

    public function puerto(){
        return $this->belongsTo(Puerto::class, 'puerto_conexion', 'id');
        //return Puerto::find($this->puerto_conexion)->nombre;
    }

    public function vendedor(){
        return Vendedor::find($this->vendedor);
    }

    public function canal(){
        return Canal::find($this->canal);
    }

    public function tecnologia(){
        if($this->tecnologia == 1){
            return 'Fibra';
        } elseif($this->tecnologia == 2){
            return 'Inalámbrica';
        } else{
            return 'N/A';
        }
    }

    public function facturacion(){
        if($this->facturacion == 1){
            return 'Estándar';
        } elseif($this->facturacion == 3){
            return 'Electrónica';
        } else{
            return 'N/A';
        }
    }

    public function oficina(){
        return Oficina::find($this->oficina);
    }

    public function contrato_permanencia($completa = false){
        if($completa){
            if($this->contrato_permanencia == 1){
                return 'Si ('.$this->contrato_permanencia_meses.' meses)';
            }else{
                return 'No';
            }
        }
        return $this->contrato_permanencia == 1 ? 'Si' : 'No';
    }

    // Este metodo devuelve al exportar de contratos los item segun la estructura pedida.
    public function producto_exportar($name){

        $coleccion = new stdClass;

        if($name == "plan_id" && $this->plan_id != null ){
            $plan = PlanesVelocidad::Find($this->plan_id);
            $item = Inventario::Find($plan->item);

            $coleccion->nombre =  $plan->name;
            $coleccion->precio = $item->precio;

            // return $plan->name . " - $" . number_format($item->precio, 0, ',', '.');
        }
        else if($name == "servicio_tv" && $this->servicio_tv != null){
            $item = Inventario::Find($this->servicio_tv);

            $coleccion->nombre =  $item->producto;
            $coleccion->precio = $item->precio;

            // return $item->producto . " - $" . number_format($item->precio, 0, ',', '.');
        }

        else if($name == "servicio_otro" && $this->servicio_otro != null){
            $item = Inventario::Find($this->servicio_otro);

            $coleccion->nombre =  $item->producto;
            $coleccion->precio = $item->precio;

            // return $item->producto . " - $" . number_format($item->precio, 0, ',', '.');
        }

        return $coleccion;
    }
}
