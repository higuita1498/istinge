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
        'nro', 'plan_id', 'client_id', 'server_configuration_id', 'state', 'ip', 'fecha_corte', 'fecha_suspension', 'usuario', 'password', 'interfaz', 'conexion', 'status', 'id_vlan', 'name_vlan', 'grupo_corte', 'created_at', 'updated_at', 'puerto_conexion', 'factura_individual', 'contrato_permanencia'
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
            return "<a href=".route('facturas.show', $factura->nro)." target='_blank'>$factura->codigo</a>";
        }
        return 'N/A';
    }

    public function puerto(){
        return Puerto::find($this->puerto_conexion)->nombre;
    }

    public function vendedor(){
        return Vendedor::find($this->vendedor);
    }

    public function canal(){
        return Canal::find($this->canal);
    }
}