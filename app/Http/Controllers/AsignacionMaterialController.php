<?php

namespace App\Http\Controllers;

use App\Model\Ingresos\ItemsAsignarMaterial;
use App\Model\Inventario\ProductosBodega;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use App\AsignarMaterial;
use App\Model\Inventario\Inventario;
use App\Model\Inventario\Bodega;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

include_once(app_path() . '/../public/PHPExcel/Classes/PHPExcel.php');

use App\Mikrotik;

include_once(app_path() . '/../public/routeros_api.class.php');

use App\Campos;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use PHPMailer\PHPMailer\Exception;
use PDF;

class AsignacionMaterialController extends Controller
{

    protected $url;

    public function __construct()
    {
        $this->middleware('auth');
        view()->share(['seccion' => 'Asignación de Material', 'title' => 'Asignación de Material', 'icon' => 'fas fa-plus', 'subseccion' => 'inventario']);
    }

    public function index(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);
        $materiales = AsignarMaterial::where('empresa', Auth::user()->empresa)->get();
        return view('asignacionMaterial.index')->with(compact('materiales'));
    }

    public function create()
    {
        $this->getAllPermissions(Auth::user()->id);
        $empresa = Auth::user()->empresaObj;

        //se obtiene la fecha de hoy
        $fecha = date('d-m-Y');

        $bodega = Bodega::where('empresa', $empresa->id)->where('status', 1)->first();
        $inventario = Inventario::select(
            'inventario.id',
            'inventario.tipo_producto',
            'inventario.type',
            'inventario.producto',
            'inventario.ref',
            DB::raw('(Select nro from productos_bodegas where bodega=' . $bodega->id . ' and producto=inventario.id) as nro')
        )
            ->where('empresa', $empresa->id)
            ->where('status', 1)
            ->where('type', 'MATERIAL')
            ->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega=' . $bodega->id . '), true)')
            ->orderBy('producto', 'ASC')
            ->get();

        $tecnicos = User::where('rol', 4)->get();

        $title = "Asignación material";
        $seccion = "Inventario";
        $subseccion = "inventario";

        return view('asignacionMaterial.create')->with(compact(
            'inventario',
            'fecha',
            'title',
            'seccion',
            'subseccion',
            'empresa',
            'tecnicos'
        ));
    }

    /**
     * Registrar una nueva factura
     * Si hay items inventariable resta los valores al inventario
     * @param Request $request
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $items = $request->item;
            $cant = $request->cant;

            // Formatear la fecha correctamente (de d-m-Y a Y-m-d)
            $fecha = Carbon::createFromFormat('d-m-Y', $request->fecha)->format('Y-m-d');

            // Validar que todos los materiales existan en la bodega antes de proceder
            foreach ($items as $key => $value) {
                $material = ProductosBodega::where("producto", $value)->first();
                if (!$material) {
                    throw new \Exception("El material con ID {$value} no existe en la bodega o no está disponible.");
                }
                if ($material->nro < $cant[$key]) {
                    throw new \Exception("No hay suficiente cantidad disponible del material {$value}. Disponible: {$material->nro}, Solicitado: {$cant[$key]}");
                }
            }

            $asignacion_material = AsignarMaterial::create([
                "referencia" => $request->referencia,
                "empresa" => Auth::user()->empresa,
                "id_tecnico" => $request->id_tecnico,
                "notas" => $request->notas,
                "fecha" => $fecha,
                "created_at" => Carbon::now()
            ]);

            foreach ($items as $key => $value) {
                ItemsAsignarMaterial::create([
                    "id_asignacion_material" => $asignacion_material->id,
                    "id_material" => $value,
                    "cantidad" => $cant[$key],
                    "created_at" => Carbon::now()
                ]);

                $material = ProductosBodega::where("producto", $value)->first();
                $material->update([
                    "nro" => round($material->nro) - $cant[$key]
                ]);
            }

            DB::commit();
            return redirect('empresa/asignacion_material')->with('success', "Materiales asignados correctamente");
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return redirect('empresa/asignacion_material')->with('error', $e->getMessage());
        }
    }

    /**
     * Formulario para modificar los datos de una factura
     * @param int $id
     * @return Application|Factory|View
     */
    public function edit($asignar_material)
    {

        $this->getAllPermissions(Auth::user()->id);
        $empresa = Auth::user()->empresaObj;

        //se obtiene la fecha de hoy
        $fecha = date('d-m-Y');

        $asignar_material = AsignarMaterial::find($asignar_material);

        $bodega = Bodega::where('empresa', $empresa->id)->where('status', 1)->first();
        $inventario = Inventario::select(
            'inventario.id',
            'inventario.tipo_producto',
            'inventario.type',
            'inventario.producto',
            'inventario.ref',
            DB::raw('(Select nro from productos_bodegas where bodega=' . $bodega->id . ' and producto=inventario.id) as nro')
        )
            ->where('empresa', $empresa->id)
            ->where('status', 1)
            ->where('type', 'MATERIAL')
            ->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega=' . $bodega->id . '), true)')
            ->orderBy('producto', 'ASC')
            ->get();

        $tecnicos = User::where('rol', 4)->get();

        $title = "Asignación material";
        $seccion = "Inventario";
        $subseccion = "inventario";

        return view('asignacionMaterial.edit')->with(compact(
            'inventario',
            'fecha',
            'title',
            'seccion',
            'subseccion',
            'empresa',
            'tecnicos',
            'asignar_material'
        ));
    }

    /**
     * Modificar los datos de la factura
     * @param Request $request
     * @return redirect
     */
    public function update(Request $request, $id)
    {

        DB::beginTransaction();
        try {
            $items = $request->item;
            $cant = $request->cant;

            $material_asignado = AsignarMaterial::find($id);

            $material_asignado->update([
                "id_tecnico" => $request->id_tecnico,
                "notas" => $request->notas,
                "updated_at" => Carbon::now()
            ]);

            foreach ($request->itemId as $key => $item) {
                if ($item != null) {
                    $item_asignar = ItemsAsignarMaterial::find($item);

                    $material = ProductosBodega::where("producto", $items[$key])->first();

                    $cantidad = round($material->nro) + $item_asignar->cantidad;

                    $item_asignar->update([
                        "id_material" => $items[$key],
                        "cantidad" => $cant[$key],
                    ]);

                    $material->update([
                        "nro" => $cantidad - $cant[$key]
                    ]);
                }
            }

            foreach ($items as $key => $value) {
                if ($key + 1 > count($request->itemId)) {
                    ItemsAsignarMaterial::create([
                        "id_asignacion_material" => $material_asignado->id,
                        "id_material" => $value,
                        "cantidad" => $cant[$key],
                        "created_at" => Carbon::now()
                    ]);

                    $material = ProductosBodega::where("producto", $value)->first();

                    $material->update([
                        "nro" => round($material->nro) - $cant[$key]
                    ]);
                }
            }
            DB::commit();
            return redirect('empresa/asignacion_material')->with('success', "Asignación actualizada correctamente");
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return redirect('empresa/asignacion_material')->with('error', $e->getMessage());
        }
    }

    /**
     * Ver los datos de una factura
     * @param int $id
     * @return Application|Factory|View
     */
    public function show($id)
    {
        $this->getAllPermissions(Auth::user()->id);

        $material_asignado = AsignarMaterial::find($id);

        $empresa = Auth::user()->empresaObj;

        $bodega = Bodega::where('empresa', $empresa->id)->where('status', 1)->first();

        $inventario = Inventario::select(
            'inventario.id',
            'inventario.tipo_producto',
            'inventario.type',
            'inventario.producto',
            'inventario.ref',
            DB::raw('(Select nro from productos_bodegas where bodega=' . $bodega->id . ' and producto=inventario.id) as nro')
        )
            ->where('empresa', $empresa->id)
            ->where('status', 1)
            ->where('type', 'MATERIAL')
            ->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega=' . $bodega->id . '), true)')
            ->orderBy('producto', 'ASC')
            ->get();

        return view('asignacionMaterial.show')->with(compact(
            'material_asignado',
            'inventario'
        ));
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $material_asignado = AsignarMaterial::find($id);

            foreach ($material_asignado->items as $item) {
                $material = ProductosBodega::where("producto", $item->id_material)->first();
                $material->update([
                    "nro" => $material->nro + $item->cantidad
                ]);

                $item->delete();
            }

            $material_asignado->delete();

            DB::commit();
            return redirect('empresa/asignacion_material')->with('success', "Asignación eliminada correctamente");
        } catch (\Exception $exception) {
            Log::error($exception);
            DB::rollBack();
            return redirect('empresa/asignacion_material')->with('error', $exception->getMessage());
        }
    }

    public function delete_item($id)
    {
        try {
            DB::beginTransaction();
            $item = ItemsAsignarMaterial::find($id);
            $material = ProductosBodega::where("producto", $item->id_material)->first();

            $material->update([
                "nro" => $material->nro + $item->cantidad
            ]);

            $item->delete();

            DB::commit();
            return response()->json([
                "message" => "item eliminado correctamente"
            ]);
        } catch (\Exception $exception) {
            Log::error($exception);
            DB::rollBack();
            return response()->json([
                "message" => "Error al eliminar el item " . $exception->getMessage()
            ], 500);
        }
    }

    /**
     * Generar PDF de la asignación de material
     * @param int $id
     * @return mixed
     */
    public function pdf($id)
    {
        /**
         * toma en cuenta que para ver los mismos
         * datos debemos hacer la misma consulta
         **/
        $empresa = Auth::user()->empresaObj;
        view()->share(['title' => 'Imprimir Asignación de Material']);

        $asignacion = AsignarMaterial::with(['tecnico', 'materiales'])->findOrFail($id);

        if ($asignacion) {
            $items = ItemsAsignarMaterial::where('id_asignacion_material', $asignacion->id)
                ->join('inventario', 'inventario.id', '=', 'items_asignar_materials.id_material')
                ->select('items_asignar_materials.*', 'inventario.producto as nombre', 'inventario.descripcion')
                ->get();

            $itemscount = $items->count();

            $pdf = PDF::loadView('pdf.asignacion_material', compact('items', 'asignacion', 'itemscount', 'empresa'));

            // Configurar el PDF
            $pdf->setPaper('letter', 'portrait');

            // Retornar el PDF para descarga
            return response($pdf->stream('asignacion_material_' . $asignacion->referencia . '.pdf'))
                ->withHeaders(['Content-Type' => 'application/pdf']);
        }

        return redirect('empresa/asignacion_material')->with('error', 'No se encontró la asignación de material');
    }
}
