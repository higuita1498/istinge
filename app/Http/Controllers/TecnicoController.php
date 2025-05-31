<?php

namespace App\Http\Controllers;

use App\User;
use App\AsignarMaterial;
use App\Model\Ingresos\ItemsAsignarMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TecnicoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        view()->share(['seccion' => 'Panel Técnico', 'title' => 'Panel Técnico', 'icon' => 'fas fa-tools']);
    }

    /**
     * Muestra el dashboard del técnico con sus materiales asignados
     */
    public function dashboard()
    {
        $this->getAllPermissions(Auth::user()->id);

        // Obtener materiales asignados
        $materialesAsignados = AsignarMaterial::with(['items.material'])
            ->where('empresa', Auth::user()->empresa)
            ->where('id_tecnico', Auth::user()->id)
            ->orderBy('fecha', 'desc')
            ->get();

        // Calcular totales
        $totalMateriales = 0;
        $materialesAgrupados = [];

        foreach ($materialesAsignados as $asignacion) {
            foreach ($asignacion->items as $item) {
                $totalMateriales += $item->cantidad;

                // Agrupar materiales por tipo
                $materialId = $item->material->id;
                if (!isset($materialesAgrupados[$materialId])) {
                    $materialesAgrupados[$materialId] = [
                        'nombre' => $item->material->producto,
                        'cantidad' => 0,
                        'ref' => $item->material->ref
                    ];
                }
                $materialesAgrupados[$materialId]['cantidad'] += $item->cantidad;
            }
        }

        // Obtener asignaciones recientes (últimos 30 días)
        $asignacionesRecientes = AsignarMaterial::with(['items.material'])
            ->where('empresa', Auth::user()->empresa)
            ->where('id_tecnico', Auth::user()->id)
            ->where('fecha', '>=', Carbon::now()->subDays(30))
            ->orderBy('fecha', 'desc')
            ->get();

        return view('tecnico.dashboard')
            ->with(compact('materialesAsignados', 'materialesAgrupados', 'totalMateriales', 'asignacionesRecientes'));
    }

    public function index(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);
        $tecnicos = User::where('rol', 4)->get();
        return view('tecnicos.index')->with(compact('tecnicos'));
    }

    public function saveLocation(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $position = [
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ];

        $user = User::find(\Illuminate\Support\Facades\Auth::user()->id);

        $user->update([
            "location" => json_encode($position)
        ]);

        return response()->json(['message' => 'Localización guardada exitosamente']);
    }

    public function getLocation(User $tecnico)
    {

        $posicion = json_decode($tecnico->location, true);

        return response()->json([
            'latitude' => $posicion['latitude'],
            'longitude' => $posicion['longitude'],
        ]);
    }
}
