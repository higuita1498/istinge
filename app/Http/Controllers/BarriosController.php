<?php

namespace App\Http\Controllers;

use App\Barrios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BarriosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['icon'=>'fas fa-file-contract', 'title' => 'Barrios', 'seccion'=> '']);
        $barrios = Barrios::where('status',1)->orderby('id','desc')->get();
        return view('barrios.index',compact('barrios'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Barrios  $barrios
     * @return \Illuminate\Http\Response
     */
    public function show(Barrios $barrios)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Barrios  $barrios
     * @return \Illuminate\Http\Response
     */
    public function edit(Barrios $barrio)
    {
      if($barrio){
        return response()->json($barrio);
      }else return false;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Barrios  $barrios
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if(isset($request->barrio_id)){
            $barrio = Barrios::Find($request->barrio_id);
            $barrio->nombre = $request->nombre;
            $barrio->created_by = Auth::user()->id;
            $barrio->save();
            return 200;
        }else{
            return 400;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Barrios  $barrios
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        if(isset($id)){
            Barrios::Find($id)->delete();
            return 200;
        }else{
            return 400;
        }
    }
}
