<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\User;
use App\Mikrotik;
use App\Contrato;

class PlanesVelocidad extends Model
{
    protected $table = "planes_velocidad";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'price', 'download', 'upload', 'type', 'address_list', 'mikrotik', 'created_by', 'updated_by', 'created_at', 'updated_at'
    ];

    public function updated_by(){
        return User::where('id', $this->updated_by)->first();
    }
    
    public function created_by(){
        return User::where('id', $this->created_by)->first();
    }
    
    public function mikrotik(){
        return Mikrotik::where('id', $this->mikrotik)->first();
    }

    public function status($class = false){
        if($class){
            if($this->status == 0){
                $status = 'danger';
            }elseif($this->status == 1){
                $status = 'success';
            }
            return $status;
        }
        
        if($this->status == 0){
            $status = 'Deshabilitado';
        }elseif($this->status == 1){
            $status = 'Habilitado';
        }
        return $status;
    }
    
    public function type($class = false){
        if($class){
            if($this->type == 0){
                $status = 'info';
            }elseif($this->type == 1){
                $status = 'dark';
            }
            return $status;
        }
        
        if($this->type == 0){
            return 'Plan Queue Simple';
        }elseif($this->type == 1){
            return 'Plan PCQ';
        }
        return $status;
    }
    
    public function uso(){
        return Contrato::where('plan_id', $this->id)->count();
    }
}