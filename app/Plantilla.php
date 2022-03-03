<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Auth;
use App\User;

class Plantilla extends Model
{
    protected $table = "plantillas";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'nro', 'tipo', 'clasificacion', 'title', 'contenido', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at'
    ];
    
    public function created_by()
    {
        return User::find($this->created_by);
    }
    
    public function updated_by()
    {
        return User::find($this->update_by);
    }

    public function status($class = false)
    {
        if($class){
            return ($this->status == 1) ? 'success font-weight-bold' : 'danger font-weight-bold';
        }
        return ($this->status == 1) ? 'Activa' : 'Desactivada';
    }
    
    public function tipo()
    {
        return ($this->tipo == 0) ? 'SMS' : 'EMAIL';
    }
}