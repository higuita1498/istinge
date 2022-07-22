<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Auth;
use DB;

use App\User;

class CRMLOG extends Model
{
    protected $table = "log_crm";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'id_crm', 'accion', 'created_by', 'created_at', 'updated_at'
    ];

    public function created_by(){
        return User::find($this->created_by);
    }
}
