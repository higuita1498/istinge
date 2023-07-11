@extends('layouts.app')

@section('content')
@if (Auth::user()->empresa==1)
<iframe class="if-glanalytics" height="1650" src="https://datastudio.google.com/embed/reporting/1HJZYmI-U3hFGxkIiUAGUzRytA9zWhKLa/page/6zXD" frameborder="0" style="border:0;width: 100%;" allowfullscreen></iframe>
@elseif(Auth::user()->empresa==16)
<iframe  class="if-glanalytics" height="1650" src="https://datastudio.google.com/embed/reporting/1uW1SvmQ19BjuQJa3msvm_3Via7wtK0eI/page/6zXD" frameborder="0" style="border:0;width:100%;" allowfullscreen></iframe>
@elseif(Auth::user()->empresa==49)
<iframe class="if-glanalytics" height="1650" src="https://datastudio.google.com/embed/reporting/1Snd4WiJv4XoTTO5H2PCxg7leWcR4RqB_/page/6zXD" frameborder="0" style="border:0;width:100%;" allowfullscreen></iframe>
@elseif(Auth::user()->empresa==44)
<iframe class="if-glanalytics" height="1650" src="https://datastudio.google.com/embed/reporting/19V4M_cQWJKaEtMcpZG5WSR_5PC_sd6Z_/page/6zXD" frameborder="0" style="border:0;width:100%;" allowfullscreen></iframe>
@elseif(Auth::user()->empresa == 34)
<iframe width="100%" height="1650" src="https://datastudio.google.com/embed/reporting/8e2ac9b9-0216-40a0-89c4-1ca104d9541b/page/6zXD" frameborder="0" style="border:0" allowfullscreen></iframe>
@elseif(Auth::user()->empresa == 6)
<iframe width="100%" height="1650" src="https://datastudio.google.com/embed/reporting/41ab1cbd-b1cd-4888-99d1-31a3aa23a632/page/6zXD" frameborder="0" style="border:0" allowfullscreen></iframe>
@elseif(Auth::user()->empresa == 21)
<iframe class="if-glanalytics" width="100%" height="1650" src="https://datastudio.google.com/embed/reporting/2161371d-0998-47c8-9020-cab596cfe30d/page/6zXD" frameborder="0" style="border:0" allowfullscreen></iframe>
@elseif(Auth::user()->empresa == 64)
<iframe class="if-glanalytics" width="100%" height="1650" src="https://datastudio.google.com/embed/reporting/54a01aca-7848-403d-a608-5647117c0971/page/6zXD" frameborder="0" style="border:0" allowfullscreen></iframe>
@elseif(Auth::user()->empresa == 13)
<iframe class="if-glanalytics" width="100%" height="1650" src="https://datastudio.google.com/embed/reporting/4a17933a-9266-477a-9411-dac851d60942/page/6zXD" frameborder="0" style="border:0" allowfullscreen></iframe>
@elseif(Auth::user()->empresa == 104)
<iframe class="if-glanalytics" width="100%" height="1650" src="https://datastudio.google.com/embed/reporting/824acb5b-8bc2-4f69-8c98-a1e6a6bb9c32/page/6zXD" frameborder="0" style="border:0" allowfullscreen></iframe>
@elseif(Auth::user()->empresa == 118)
<iframe class="if-glanalytics "width="100%" height="1650" src="https://datastudio.google.com/embed/reporting/8455853e-4490-438e-a01e-8eff19245d0a/page/6zXD" frameborder="0" style="border:0" allowfullscreen></iframe>
@else
  <div class="alert alert-danger" style="margin-left: 2%;margin-right: 2%;">
      Aún no tienes reportes activos en tu página web. Si los deseas activar comunícate con el administrador.
    </div>

@endif



@endsection