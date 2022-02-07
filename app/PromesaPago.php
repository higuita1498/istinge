<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Model\Ingresos\Factura; 
use App\Contacto; 
use App\User; 

class PromesaPago extends Model
{
    protected $table = "promesa_pago";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'factura', 'cliente', 'fecha', 'vencimiento', 'created_by', 'updated_by', 'created_at', 'updated_at'
    ];
    
    public function factura(){
        return Factura::find($this->factura);
    }
    
    public function cliente(){
        return Contacto::find($this->cliente);
    }
    
    public function usuario(){
        return User::find($this->created_by);
    }

}
