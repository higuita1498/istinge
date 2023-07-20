@if(session()->has('notify'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {!!  Session::get('notify')!!}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    {{Session::forget('notify')}}
@endif

@if(session()->has('suscripcion'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {!!  Session::get('suscripcion')!!}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    {{Session::forget('suscripcion')}}
@endif

@if(session()->has('soporte'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {!!  Session::get('soporte')!!}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    {{Session::forget('soporte')}}
@endif



@section('js')
    <script !src="">
        $(document).ready(function(){
            setTimeout(function () {
                $('.alert').alert('close');
            }, 30);
        });
    </script>
@endsection
