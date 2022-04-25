@extends('layouts.app')

@section('style')

@endsection

@section('content')
    @if(Session::has('danger'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            @php echo Session::get('danger') @endphp
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
	@endif

    @if(Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            @php echo Session::get('success') @endphp
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
	
	<form method="POST" action="{{ route('ingresos.efecty_store') }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-contrato" enctype="multipart/form-data">
	    @csrf

        <div class="row card-description p-0">
            <div class="col-md-12 mt-3">
                <ul class="nav nav-pills" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="adjuntos-tab" data-toggle="tab" href="#adjuntos" role="tab" aria-controls="adjuntos" aria-selected="false">CARGA DE ARCHIVO</a>
                    </li>
                </ul>
                <hr style="border-top: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}; margin: .5rem 0rem 2rem;">
                <div class="tab-content fact-table" id="myTabContent">
                    <div class="tab-pane fade show active" id="adjuntos" role="tabpanel" aria-labelledby="adjuntos-tab">
                        <div class="row">
                            <div class="col-md-6 offset-md-3 form-group">
                                <label class="control-label">Archivo Efecty</label>
                                <input type="file" class="form-control"  id="archivo_efecty" name="archivo_efecty" value="{{old('archivo_efecty')}}" accept=".txt, .TXT">
                                <span style="color: red;">
                                    <strong>{{ $errors->first('archivo_efecty') }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
        <hr>
        
        <div class="row">
            <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
                <a href="{{route('ingresos.index')}}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        $(document).on('change','input[type="file"]',function(){
            var fileName = this.files[0].name;
            var fileSize = this.files[0].size;

            if(fileSize > 20480000){
                this.value = '';
                Swal.fire({
                    title: 'La documentación adjuntada no puede exceder 20MB',
                    text: 'Intente nuevamente',
                    type: 'error',
                    showCancelButton: false,
                    showConfirmButton: false,
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'Cancelar',
                    timer: 10000
                });
            }else{
                var ext = fileName.split('.').pop();
                switch (ext) {
                    case 'txt':
                    case 'TXT':
                        break;
                    default:
                        this.value = '';
                    Swal.fire({
                        title: 'La documentación adjuntada debe poseer una extensión apropiada. Sólo se aceptan archivos txt',
                        text: 'Intente nuevamente',
                        type: 'error',
                        showCancelButton: false,
                        showConfirmButton: false,
                        cancelButtonColor: '#d33',
                        cancelButtonText: 'Cancelar',
                        timer: 10000
                    });
                }
            }
        });
    </script>
@endsection
