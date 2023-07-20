<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Contacto;
use App\User;

class Wifi extends Model
{
    //
    protected $table = "wifi";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_cliente', 'red_antigua', 'red_nueva', 'pass_antigua', 'pass_nueva', 'ip', 'mac', 'fecha', 'status', 'oculta', 'created_at', 'updated_at'
    ];

    public function cliente()
    {
        return Contacto::where('id', $this->id_cliente)->first();
    }
    
    public function estatus($class=false)
    {
        if ($class) {
            return $this->status==0 ? 'success' : 'danger';
        }
        
        return $this->status==0 ? 'Realizado' : 'Pendiente';
    }
    
    public function created_by()
    {
        $created_by = User::where('id', $this->created_by)->first();
        if ($created_by) {
            return $created_by;
        } else {
            return '';
        }
    }
    
    public function oculta()
    {
        return $this->oculta==0 ? 'No' : 'Si';
    }
}