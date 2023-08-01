<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modulo; use App\Soporte; use Carbon\Carbon; use Mail;  
use Validator; use Illuminate\Validation\Rule;  use Auth;
use Session;

class SoporteController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['seccion' => 'soporte', 'title' => 'Soporte', 'icon' =>'far fa-life-ring']);
  }

  public function index(){
      $this->getAllPermissions(Auth::user()->id);
    if (Auth::user()->rol==1) {
      $tickets = Soporte::whereNull('asociada')->where('estatus',1)->get();
      return view('soporte.masterindex')->with(compact('tickets'));     
    }

    $tickets = Soporte::where('empresa',Auth::user()->empresa)->whereNull('asociada')->get();
    return view('soporte.index')->with(compact('tickets'));     
    
 	}

  /**
  * Formulario para crear un nuevo banco
  * @return view
  */
  public function create(){
      $this->getAllPermissions(Auth::user()->id);
    $categoria=Modulo::all();
    view()->share(['title' => 'Nuevo Ticket']);
    return view('soporte.create')->with(compact('categoria' )); 
  }

  /**
  * Registrar un nuevo banco
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){
      
      
       if( Soporte::where('empresa',auth()->user()->empresa)->count() > 0){
       //Tomamos el tiempo en el que se crea el registro
    Session::put('posttimer', Soporte::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
    $sw = 1;

    //Recorremos la sesion para obtener la fecha
    foreach (Session::get('posttimer') as $key) {
      if ($sw == 1) {
        $ultimoingreso = $key;
        $sw=0;
      }
    }

//Tomamos la diferencia entre la hora exacta acutal y hacemos una diferencia con la ultima creaci��n
    $diasDiferencia = Carbon::now()->diffInseconds($ultimoingreso);

//Si el tiempo es de menos de 30 segundos mandamos al listado general
    if ($diasDiferencia <= 10) {
      $mensaje = "El formulario ya ha sido enviado.";
     return redirect('empresa/soporte')->with('success', $mensaje);
    }
       }
      
    $request->validate([
      'titulo' => 'required|max:250',
      'modulo' => 'required|numeric',
      'error' => 'required'
    ]); 
    if ($request->imagen) {        
      $request->validate([
          'imagen'=>'mimes:jpeg,jpg,png| max:1000'
      ],['imagen.mimes' => 'La extensión de la imagen debe ser jpeg, jpg, png',
        'imagen.max' => 'El peso máximo para el logo es de 1000KB',
      ]);
      
    }

    $soporte = new Soporte;
    $soporte->empresa=Auth::user()->empresa;
    $soporte->titulo=$request->titulo;
    $soporte->modulo=$request->modulo;
    $soporte->usuario=Auth::user()->id;
    $soporte->error=$request->error;
    $soporte->save();

    if ($request->imagen) {
      $imagen = $request->file('imagen');
      $nombre_imagen = $soporte->id.'imagen.'.$imagen->getClientOriginalExtension();     
      $request->imagen=$nombre_imagen;
      $path = public_path() .'/images/Empresas/Empresa'.Auth::user()->empresa.'/soporte/'. $soporte->id;        
      $imagen->move($path,$nombre_imagen);
      $soporte->imagen=$request->imagen;
      $request->imagen=$soporte->imagen;
      $soporte->save();

    }

    $data=$request;

    if ($request->imagen) {
      $data['nombre_imagen']=$nombre_imagen;
    }
    $data['empresa']=Auth::user()->empresa()->nombre;
    $data['usuario']=Auth::user()->nombres;
    $data['email']=Auth::user()->email;
    $data['modulo']=$soporte->modulo();
    $data['email']=Auth::user()->email;
    $data['empresaid']=Auth::user()->empresa;
    $data['soporte']=$soporte->id;

    self::sendMail('emails.soporte',  ['data' => $data], function($msj) use ($data){
        $msj->subject($data->titulo);
        $msj->to('monicadategeek@gmail.com');
    });

    $mensaje='Se ha creado satisfactoriamente el ticket';
    return redirect('empresa/soporte')->with('success', $mensaje)->with('soporte_id', $soporte->id);
  }


  /**
  * Ver un banco
  * @param int $id
  * @return view
  */
  public function show($id){
      $this->getAllPermissions(Auth::user()->id);
    if (Auth::user()->rol==1) {
      $soporte = Soporte::where('id', $id)->first();    
    }
    else{
      $soporte = Soporte::where('empresa',Auth::user()->empresa)->where('id', $id)->first(); 
    }

    if ($soporte) {        
      $tickets = Soporte::where('asociada', $id)->orderBy('id', 'desc')->get();  
      view()->share(['title' => 'Detalles del Ticket']);
      return view('soporte.show')->with(compact('soporte', 'tickets'));
    }
    
    return redirect('empresa/soporte')->with('success', 'No existe un registro con ese id');
  }



  /**
  * Modificar los datos del banco
  * @param Request $request
  * @return redirect
  */
  public function update(Request $request, $id){
    if (!Auth::user()->empresa) {
      $soporte = Soporte::where('id', $id)->first();    
    }
    else{
      $soporte = Soporte::where('empresa',Auth::user()->empresa)->where('id', $id)->first(); 
    }

    if ($soporte) {
      $request->validate([
      'error' => 'required'
      ]); 
      if ($request->imagen) {        
        $request->validate([
            'imagen'=>'mimes:jpeg,jpg,png| max:1000'
        ],['imagen.mimes' => 'La extensión de la imagen debe ser jpeg, jpg, png',
          'imagen.max' => 'El peso máximo para el logo es de 1000KB',
        ]);
        
      }
        $repli = new Soporte;
        $repli->empresa=$soporte->empresa;
        $repli->titulo="Re: ".$soporte->titulo;
        $repli->modulo=$soporte->modulo;
        $repli->usuario=Auth::user()->id;
        $repli->error=$request->error;
        $repli->asociada=$soporte->id;
        $repli->save();

      if ($request->imagen) {
        $imagen = $request->file('imagen');
        $nombre_imagen = $repli->id.'imagen.'.$imagen->getClientOriginalExtension();     
        $request->imagen=$nombre_imagen;
        $path = public_path() .'/images/Empresas/Empresa'.$soporte->empresa.'/soporte/'. $soporte->id;        
        $imagen->move($path,$nombre_imagen);
        $repli->imagen=$request->imagen;
        $request->imagen=$repli->imagen;
        $repli->save();

      }
      
      if (Auth::user()->empresa) {
        $soporte->estatus=1;
      }
      else{
        $soporte->estatus=2;
      }

      $soporte->save();

      $data=$request;
      
      $data['empresa']=$soporte->empresa()->nombre;
      if ($request->imagen) {
        $data['nombre_imagen']=$nombre_imagen;
      }
      $data['empresaid']=$soporte->empresa()->id;
      $data['soporte']=$soporte->id;
      $data['titulo']=$repli->titulo;
      if (Auth::user()->empresa) {
        $data['usuario']=Auth::user()->nombres;
        $data['email']=Auth::user()->email;
        self::sendMail('emails.soporte',  ['data' => $data], function($msj) use ($data){
          $msj->subject($data->titulo);
          $msj->to('monicadategeek@gmail.com');
        });
      }
      else{
        $data['usuario']=$soporte->usuario();
        $data['email']=$soporte->usuario(true)->email;
        $data['modulo']=$soporte->modulo();
        
        self::sendMail('emails.soporte',  ['data' => $data], function($msj) use ($data){
            $msj->subject($data->titulo);
            $msj->to($data->email);
      });

      }
      


      $mensaje='Se ha respondido el ticket';
      if (Auth::user()->rol==1) {
        return redirect('master/atencionsoporte')->with('success', $mensaje);
      }
      else{
        return redirect('empresa/soporte')->with('success', $mensaje);
      }


    }

    if (!Auth::user()->empresa) {
      return redirect('master/atencionsoporte')->with('success', 'No existe un registro con ese id');
    }
    else{
      return redirect('empresa/soporte')->with('success', 'No existe un registro con ese id');
    }
  }

  /**
  * Funcion para eliminar un banco
  * @param int $id
  * @return redirect
  */
  public function destroy($id){      
    $banco=Banco::find($id);   
    if ($banco) {        
      $banco->delete();
    }    
    return redirect('empresa/bancos')->with('success', 'Se ha eliminado el banco');
  }

  


 

}