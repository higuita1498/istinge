<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Puc;
use Carbon\Carbon;
use Auth;
include_once(app_path() . '/../public/PHPExcel/Classes/PHPExcel.php');
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use DOMDocument;
use DB;

class PucController extends Controller
{
    public function __construct()
    {
      $this->middleware('auth');
      view()->share(['seccion' => 'categorias', 'title' => 'Puc', 'icon' =>'fas fa-list-ul']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->getAllPermissions(Auth::user()->id);
     
        
        $categorias = Puc::where('empresa',Auth::user()->empresa)->whereNull('asociado')->orderBy('codigo','ASC')->get();
        
        view()->share(['title' => 'PUC ']);

 		return view('puc.index')->with(compact('categorias'));   	
    }

    public function create($id){
        $this->getAllPermissions(Auth::user()->id);
        $categoria = Puc::where('empresa',Auth::user()->empresa)->where('codigo', $id)->first();
        return view('puc.create')->with(compact('categoria'));
    }

    /**
  * Registrar un nuevo banco
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){
        
    $request->validate([
        'nombre' => 'required|max:200',
        'asociado' => 'required|numeric',
        ]);
        
        if(!Puc::where('empresa',Auth::user()->empresa)->where('codigo',$request->codigo)->first()){
            $categoria = new Puc;
            $categoria->empresa=Auth::user()->empresa;
            $categoria->nro = $request->codigo;
            $categoria->asociado=$request->asociado;
            $categoria->nombre=$request->nombre;
            $categoria->codigo=$request->codigo;
            $categoria->descripcion=$request->descripcion;
            $categoria->save();
            $mensaje='Se ha creado satisfactoriamente la categoría';
            return redirect('empresa/puc')->with('success', $mensaje);
        }else{
            return redirect('empresa/puc')->with('info', 'el codigo ingresado ya está siendo usado');
        }
    }

    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $categoria = Puc::where('empresa',Auth::user()->empresa)->where('codigo', $id)->first();
        if ($categoria) {        
          return view('puc.edit')->with(compact('categoria'));
        }
        return 'No existe un registro con ese id';
    }

    public function show($codigo){

        $empresa = auth()->user()->empresa;
        $hijos = Puc::where('empresa',$empresa)->where('asociado',$codigo)->get();

        foreach($hijos as $hijo){
            if(strlen($hijo->codigo) == 2){
                $hijo->nivel = 2;
            }elseif(strlen($hijo->codigo) > 2 && strlen($hijo->codigo) < 5){
                $hijo->nivel = 3;
            }else if(strlen($hijo->codigo) > 4 && strlen($hijo->codigo) < 7){
                $hijo->nivel = 4;
            }else if(strlen($hijo->codigo) > 6){
                $hijo->nivel = 5;
            }
        }

        return response()->json([
            'categories' => $hijos
        ]);
        
    }

    /**
    * Funcion para cambiar el estatus de la categoría
    * @param int $id
    * @return redirect
    */
    public function act_desc($id)
    {
        $categoria = Puc::where('empresa', Auth::user()->empresa)->where('nro', $id)->first();
        if ($categoria) {
            if ($categoria->estatus==1) {
                $mensaje='Se ha desactivado la categoría';
                $categoria->estatus=0;
                $categoria->save();
            } else {
                $mensaje='Se ha activado la categoría';
                $categoria->estatus=1;
                $categoria->save();
            }
            return redirect('empresa/puc')->with('success', $mensaje);
        }
        return redirect('empresa/puc')->with('success', 'No existe un registro con ese id');
    }

    /**
    * Modificar los datos del banco
    * @param Request $request
    * @return redirect
    */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|max:200',
        ]);

        $categoria = Puc::find($id);

        if ($categoria) {

            if(!Puc::where('empresa',Auth::user()->empresa)->where('codigo',$request->codigo)->where('id','!=',$id)->first()){
                $categoria->nombre=$request->nombre;
                $categoria->codigo=$request->codigo;
                $categoria->descripcion=$request->descripcion;
                $categoria->save();
                $mensaje='Se ha modificado satisfactoriamente la categoría';
                return redirect('empresa/puc')->with('success', $mensaje);
            }else{
                return redirect('empresa/puc')->with('info', 'Ya hay una cuenta del puc con ese código.');
            }
          
        }
        return redirect('empresa/puc')->with('success', 'No existe un registro con ese id');
    }

    /**
    * Funcion para eliminar un banco
    * @param int $id
    * @return redirect
    */
    public function destroy($id)
    {
        if(!Puc::where('empresa', Auth::user()->empresa)->where('asociado',$id)->first()){
            $categoria = Puc::where('empresa', Auth::user()->empresa)->where('codigo', $id)->first();
            $categoria->delete();
        }else{
            return redirect('empresa/puc')->with('info', 'Esta Categoria tiene cuentas hijas, por lo tanto no se puede eliminar.');
        }

        return redirect('empresa/puc')->with('success', 'Se ha eliminado la categoría');
    }

    /**
     * Importación de la estructura base del puc segun excel madre.
     *
     * @return \Illuminate\Http\Response
     */
    public function import_puc(Request $request){
        
        $request->validate([
            'archivo' => 'required|mimes:xlsx',
        ],[
            'archivo.mimes' => 'El archivo debe ser de extensión xlsx'
        ]);
        $create=0;
        $modf=0;
        $imagen = $request->file('archivo');
        $nombre_imagen = 'archivo.'.$imagen->getClientOriginalExtension();
        $path = public_path() .'/images/Empresas/Empresa'.Auth::user()->empresa;
        $imagen->move($path,$nombre_imagen);
        Ini_set ('max_execution_time', 500);
        $fileWithPath=$path."/".$nombre_imagen;
        //Identificando el tipo de archivo
        $inputFileType = PHPExcel_IOFactory::identify($fileWithPath);
        //Creando el lector.
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        //Cargando al lector de excel el archivo, le pasamos la ubicacion
        $objPHPExcel = $objReader->load($fileWithPath);
        //obtengo la hoja 0
        $sheet = $objPHPExcel->getSheet(0);
        //obtiene el tamaño de filas
        $highestRow = $sheet->getHighestRow();
        //obtiene el tamaño de columnas
        $highestColumn = $sheet->getHighestColumn();

        for ($row = 4; $row <= $highestRow; $row++)
        {
            $request= (object) array();
            //obtengo el A4 desde donde empieza la data
            $codigo=$sheet->getCell("A".$row)->getValue();
            if (empty($codigo)) {
                break;
            }

            $request->nro=$sheet->getCell("A".$row)->getValue();
            $request->nombre=$sheet->getCell("B".$row)->getValue();
            $request->asociado = $sheet->getCell("C".$row)->getValue();
            $request->tercero=$sheet->getCell("D".$row)->getValue();
            $request->grupo=$sheet->getCell("E".$row)->getValue();
            $request->tipo=$sheet->getCell("F".$row)->getValue();
            $request->axi=$sheet->getCell("G".$row)->getValue();
            $request->balance=$sheet->getCell("H".$row)->getValue();
            
            $error=(object) array();

            //Validaciones que no necesitamos por el momento.$row
            /*
            if (!$request->tip_iden) {
                $error->tip_iden="El campo Tipo de identificación es obligatorio";
            }
            if (!$request->telefono1) {
                $error->telefono1="El campo Teléfono es obligatorio";
            }
      
            if (count((array) $error)>0) {
                $fila["error"]='FILA '.$row;
                $error=(array) $error;
                var_dump($error);
                var_dump($fila);

                array_unshift ( $error ,$fila);
                $result=(object) $error;
                //reenvia los errores
                return back()->withErrors($result)->withInput();
            }*/

        }

        for ($row = 4; $row <= $highestRow; $row++)
        {

            $codigo=$sheet->getCell("A".$row)->getValue();
            if (empty($codigo)) {
                break;
            }
            
            $request->nro=$sheet->getCell("A".$row)->getValue();
            $request->nombre=$sheet->getCell("B".$row)->getValue();
            $request->asociado = $sheet->getCell("C".$row)->getValue();
            $request->tercero=$sheet->getCell("D".$row)->getValue();
            $request->grupo=$sheet->getCell("E".$row)->getValue();
            $request->tipo=$sheet->getCell("F".$row)->getValue();
            $request->axi=$sheet->getCell("G".$row)->getValue();
            $request->balance=$sheet->getCell("H".$row)->getValue();

            if(DB::table('puc_grupo')->where('nombre',$request->grupo)->first()){
                $request->id_grupo = DB::table('puc_grupo')->where('nombre',$request->grupo)->first()->id;
            }

            if(DB::table('puc_tipo')->where('nombre',$request->tipo)->first()){
                $request->id_tipo = DB::table('puc_tipo')->where('nombre',$request->tipo)->first()->id;
            }

            if(DB::table('puc_balance')->where('nombre',$request->balance)->first()){
                $request->id_balance = DB::table('puc_balance')->where('nombre',$request->balance)->first()->id;
            }

            $puc =Puc::where('codigo',$codigo)->where('empresa',Auth::user()->empresa)->first();
            if (!$puc) {
                $puc = new Puc;
                $puc->empresa=Auth::user()->empresa;
                $create=$create+1;
            }
            else{
                $modf=$modf+1;
            }

            $puc->nombre=$request->nombre;
            $puc->asociado = $request->asociado;
            $puc->nro = $request->nro;
            $puc->codigo = $codigo;
            $puc->tercero = $request->tercero;
            $puc->axi = $request->axi;
            $puc->id_grupo = $request->id_grupo;
            $puc->id_tipo = $request->id_tipo;
            $puc->id_balance = isset($request->id_balance) ?$request->id_balance : '' ;

            $puc->save();

        }
        $mensaje='Se ha completado exitosamente la carga de datos del sistema';
        if ($create>0) {
            $mensaje.=' Creados: '.$create;
        }
        if ($modf>0) {
            $mensaje.=' Modificados: '.$modf;
        }
        return "importacion hecha, creados: " . $create; 
        return redirect('empresa/contactos')->with('success', $mensaje);
    }

 
}
