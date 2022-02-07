<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Contrato;
use App\Contacto;
use App\User;

class Auditoria extends Model
{
    protected $table = "auditorias";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_cliente', 'id_contrato', 'accion', 'responsable', 'created_at', 'updated_at'  
    ];
    
    public function cliente()
    {
        return Contacto::where('id', $this->id_cliente)->first();
    }
    
    public function contrato()
    {
        return Contrato::where('public_id', $this->id_contracto)->first();
    }
    
    public function accion($class = false)
    {
        if ($class) {
            return ($this->accion == 0) ? 'danger' : 'success';
        }
        return ($this->accion == 0) ? 'Deshabilitar' : 'Habilitar';
    }
    
    public function responsable()
    {
        return User::where('id', $this->responsable)->first();
    }

}
