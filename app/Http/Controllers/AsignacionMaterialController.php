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
        $material_asignado = AsignarMaterial::with(['items.material', 'tecnico'])->find($id);
        $empresa = Auth::user()->empresaObj;

        $pdf = new \FPDF('P', 'mm', 'Letter');

        // Establecer márgenes iguales en ambos lados
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();

        // Calcular el ancho disponible
        $pageWidth = $pdf->GetPageWidth();
        $contentWidth = $pageWidth - 20; // 20mm total margins (10mm each side)

        // Logo y encabezado
        $logo = public_path() . '/images/Empresas/Empresa' . Auth::user()->empresa . '/' . $empresa->logo;
        if (file_exists($logo)) {
            $pdf->Image($logo, 10, 10, 45);
        }

        // Información de la empresa (centrada)
        $pdf->SetY(10);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell($contentWidth, 6, utf8_decode($empresa->nombre), 0, 1, 'C');

        // Dividir la dirección en partes usando el guion como separador
        $direccion_partes = explode('-', $empresa->direccion);

        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell($contentWidth, 4, utf8_decode($empresa->tip_iden('mini') . ' ' . $empresa->nit . ($empresa->dv ? ' - ' . $empresa->dv : '')), 0, 1, 'C');

        // Imprimir cada parte de la dirección en una nueva línea
        foreach ($direccion_partes as $parte) {
            $pdf->Cell($contentWidth, 4, utf8_decode(trim($parte)), 0, 1, 'C');
        }

        $pdf->Cell($contentWidth, 4, utf8_decode($empresa->telefono), 0, 1, 'C');
        if ($empresa->web) {
            $pdf->Cell($contentWidth, 4, utf8_decode($empresa->web), 0, 1, 'C');
        }
        $pdf->Cell($contentWidth, 4, utf8_decode($empresa->email), 0, 1, 'C');

        // Título del documento
        $pdf->Ln(5);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell($contentWidth, 8, utf8_decode('ASIGNACIÓN DE MATERIAL'), 0, 1, 'R');
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell($contentWidth, 6, utf8_decode('No. ' . $material_asignado->referencia), 0, 1, 'R');
        $pdf->Ln(5);

        // Información del técnico (tabla)
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->SetDrawColor(180, 180, 180);

        // Primera tabla con el mismo ancho que la tabla de materiales
        $col1 = 30;
        $col2 = ($contentWidth - $col1 - 50); // Ancho para el nombre/email
        $col3 = 50; // Ancho para la fecha

        // Primera fila
        $pdf->Cell($col1, 7, utf8_decode('TÉCNICO:'), 1, 0, 'R', true);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell($col2, 7, utf8_decode($material_asignado->tecnico->nombres), 1, 0, 'L');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($col3, 7, utf8_decode('FECHA DE EXPEDICIÓN'), 1, 1, 'C', true);

        // Segunda fila
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($col1, 7, utf8_decode('EMAIL:'), 1, 0, 'R', true);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell($col2, 7, utf8_decode($material_asignado->tecnico->email), 1, 0, 'L');
        $pdf->Cell($col3, 7, date('d/m/Y', strtotime($material_asignado->fecha)), 1, 1, 'C');

        $pdf->Ln(5);

        // Tabla de materiales
        $pdf->SetFillColor(220, 220, 220);
        $pdf->SetFont('Arial', 'B', 9);

        // Usar el mismo ancho total que la tabla anterior
        $col1 = $contentWidth * 0.5; // 50% para Material
        $col2 = $contentWidth * 0.3; // 30% para Referencia
        $col3 = $contentWidth * 0.2; // 20% para Cantidad

        $pdf->Cell($col1, 8, 'Material', 1, 0, 'C', true);
        $pdf->Cell($col2, 8, 'Referencia', 1, 0, 'C', true);
        $pdf->Cell($col3, 8, 'Cantidad', 1, 1, 'C', true);

        // Contenido de la tabla con colores alternados
        $pdf->SetFont('Arial', '', 9);
        $fill = false;
        foreach ($material_asignado->items as $item) {
            $pdf->SetFillColor(245, 245, 245);
            $pdf->Cell($col1, 7, utf8_decode($item->material->producto), 1, 0, 'L', $fill);
            $pdf->Cell($col2, 7, utf8_decode($item->material->ref), 1, 0, 'L', $fill);
            $pdf->Cell($col3, 7, $item->cantidad, 1, 1, 'C', $fill);
            $fill = !$fill;
        }

        // Notas con borde y fondo
        if ($material_asignado->notas) {
            $pdf->Ln(5);
            $pdf->SetFillColor(245, 245, 245);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(30, 7, 'Notas:', 0);
            $pdf->SetFont('Arial', '', 9);
            $pdf->MultiCell($contentWidth - 30, 7, utf8_decode($material_asignado->notas), 1, 'L', true);
        }

        $pdf->Ln(20);

        // Firmas con líneas más estilizadas
        $pdf->SetDrawColor(100, 100, 100);
        $pdf->SetFont('Arial', '', 9);

        // Ajustar las posiciones de las líneas para que coincidan con el ancho de contenido
        $leftMargin = 25;
        $rightMargin = $pageWidth - 25;
        $middlePoint = $pageWidth / 2;

        $pdf->Line($leftMargin, $pdf->GetY(), $middlePoint - 15, $pdf->GetY());
        $pdf->Line($middlePoint + 15, $pdf->GetY(), $rightMargin, $pdf->GetY());

        $pdf->Ln(1);
        $pdf->Cell($contentWidth / 2, 5, utf8_decode('Firma del Técnico'), 0, 0, 'C');
        $pdf->Cell($contentWidth / 2, 5, utf8_decode('ACEPTADA, FIRMA Y/O SELLO Y FECHA'), 0, 1, 'C');

        // Generar el PDF en memoria
        $pdfContent = $pdf->Output('', 'S');

        // Retornar la respuesta con los headers correctos
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Asignacion_Material_' . $material_asignado->referencia . '.pdf"')
            ->header('Content-Length', strlen($pdfContent));
    }
}
