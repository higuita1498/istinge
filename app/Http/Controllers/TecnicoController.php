<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TecnicoController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        view()->share(['seccion' => 'Técnicos', 'title' => 'Técnicos', 'icon' =>'fas fa-plus', 'subseccion' => 'tecnico']);
    }

    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $tecnicos = User::where('rol',4)->get();
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
