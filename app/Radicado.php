<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Radicado extends Model
{
    protected $table = 'radicados';
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['cliente', 'fecha', 'nombre', 'telefono', 'correo', 'direccion', 'contrato', 'desconocido', 'estatus', 'codigo', 'empresa', 'firma', 'prioridad', 'barrio'];

    protected $appends = ['session'];

    public function getSessionAttribute()
    {
        return $this->getAllPermissions(\Auth::user()->id);
    }

    public function getAllPermissions($id)
    {
        if (\Auth::user()->rol >= 2) {
            if (
                \DB::table('permisos_usuarios')
                    ->select('id_permiso')
                    ->where('id_usuario', $id)
                    ->count() > 0
            ) {
                $permisos = \DB::table('permisos_usuarios')
                    ->select('id_permiso')
                    ->where('id_usuario', $id)
                    ->get();
                foreach ($permisos as $key => $value) {
                    $_SESSION['permisos'][$permisos[$key]->id_permiso] = '1';
                }

                return $_SESSION['permisos'];
            } else {
                return null;
            }
        }
    }

    public function tecnico()
    {
        return User::where('id', $this->tecnico)->first();
    }

    public function tecnico_reporte()
    {
        $tecnico = User::where('id', $this->tecnico)->first();
        if ($tecnico) {
            return $tecnico->nombres;
        }

        return 'No asociado';
    }

    public function responsable()
    {
        return User::where('id', $this->responsable)->first();
    }

    public function servicio()
    {
        return Servicio::where('id', $this->servicio)->first();
    }

    public function estatus($class = false)
    {
        if ($class) {
            if (0 == $this->estatus || 2 == $this->estatus) {
                return 'danger';
            } elseif (1 == $this->estatus || 3 == $this->estatus) {
                return 'success';
            }
        }

        if (0 == $this->estatus) {
            $status = 'Pendiente';
        } elseif (1 == $this->estatus) {
            $status = 'Solventado';
        } elseif (2 == $this->estatus) {
            $status = 'Escalado / Pendiente';
        } elseif (3 == $this->estatus) {
            $status = 'Escalado / Solventado';
        }

        return $status;
    }

    public function show_url()
    {
        return route('radicados.show', $this->id);
    }

    public function nro_radicados()
    {
        $temp = 0;
        $temp += Radicado::where('cliente', $this->cliente)->count();

        return $temp;
    }

    public function creado()
    {
        return 1 == $this->creado ? 'NetworkSoft' : 'APP';
    }

    public function prioridad($class = false)
    {
        if ($class) {
            if (1 == $this->prioridad) {
                return 'info';
            } elseif (2 == $this->prioridad) {
                return 'warning';
            } elseif (3 == $this->prioridad) {
                return 'danger';
            }
        }

        if (1 == $this->prioridad) {
            return 'Baja';
        } elseif (2 == $this->prioridad) {
            return 'Media';
        } elseif (3 == $this->prioridad) {
            return 'Alta';
        }
    }

    public function oficina()
    {
        return Oficina::find($this->oficina);
    }

    public function duracion()
    {
        $inicio = Carbon::parse($this->tiempo_ini);
        $cierre = Carbon::parse($this->tiempo_fin);
        $duracion = $inicio->diffInMinutes($cierre);

        return $duracion . ' minutos';
    }

    public function cliente()
    {
        return Contacto::find($this->cliente);
    }

    public function contrato()
    {
        return Contrato::find($this->contrato);
    }

    /**
     * Regresa el número consecutivo siguiente de los códigos en la base de
     * datos. Este método es inseguro, ya que no se bloquea la base de datos
     * en el momento de la creación, y se pueden generar dos radicados con
     * el mismo código en determinado momento.
     *
     * Se debería optar por una secuencia en la base de datos, para almacenar
     * el código.
     */
    public static function getNextConsecutiveCodeNumber()
    {
        return Radicado::max('codigo') + 1;
    }
}
