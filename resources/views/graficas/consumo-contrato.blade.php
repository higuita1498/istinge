@extends('layouts.app')

@section('boton')
@if(isset($contrato) && $contrato)
    <a href="{{ route('contratos.show',$contrato->id )}}"  class="btn btn-primary" title="Regresar al Detalle"><i class="fas fa-step-backward"></i></i> Regresar al Detalle</a>
@endif
@endsection

@section('style')
    <style>
        .bg-th{
            text-align: center;
        }
        .info th {
            text-align: center;
        }
    </style>
@endsection

@section('content')
    <div class="row card-description">
        @if(isset($contrato) && $contrato)
    	<div class="col-md-12 mb-4">
    		<div class="table-responsive">
    			<table class="table table-striped table-bordered table-sm info">
    				<tbody>
    					<tr>
    						<th class="bg-th">CONTRATO</th>
    						<th class="bg-th">CLIENTE</th>
    						<th class="bg-th">DIRECCIÓN IP</th>
    						<th class="bg-th">INTERFAZ</th>
    						<th class="bg-th">SERVIDOR ASOCIADO</th>
    						<th class="bg-th">CONEXIÓN</th>
    						{{--<th class="bg-th">PLAN</th>--}}
    					</tr>
    					<tr class="text-center">
    						<td>{{ $contrato->nro }}</td>
    						<td>{{ $contrato->cliente()->nombre }} {{ $contrato->cliente()->apellido1 }} {{ $contrato->cliente()->apellido2 }}</td>
    						<td>{{ $contrato->ip }}</td>
    						<td>{{ $contrato->interfaz }}</td>
    						<td>{{ $contrato->servidor()->nombre }}</td>
    						<td>{{ $contrato->conexion() }}</td>
    						{{--<td>{{ $contrato->plan()->name }}</td>--}}
    					</tr>
    				</tbody>
    			</table>
    		</div>
    	</div>
        @endif
    	
        <div class="col-md-3 text-center">
            <a href="http://{{$url}}/daily.gif" target="_blank" class="btn btn-system mb-4">
                <h5 class="pb-0 mb-0 font-weight-bold">GRÁFIO DIARIO</h5><p class="mb-0">(promedio de 5 minutos)</p>
                <div class="mb-4 d-none">
                    <img src="http://{{$url}}/daily.gif" class="d-none img-gafica">
                </div>
            </a>
        </div>
        <div class="col-md-3 text-center">
            <a href="http://{{$url}}/weekly.gif" target="_blank" class="btn btn-system mb-4">
                <h5 class="pb-0 mb-0 font-weight-bold">GRÁFIO SEMANAL</h5><p class="mb-0">(promedio de 30 minutos)</p>
                <div class="mb-4 d-none">
                    <img src="http://{{$url}}/weekly.gif" class="d-none img-gafica">
                </div>
            </a>
        </div>
        <div class="col-md-3 text-center">
            <a href="http://{{$url}}/monthly.gif" target="_blank" class="btn btn-system">
                <h5 class="pb-0 mb-0 font-weight-bold">GRÁFIO MENSUAL</h5><p class="mb-0">(promedio de 2 horas)</p>
                <div class="mb-4 d-none">
                    <img src="http://{{$url}}/monthly.gif" class="d-none img-gafica">
                </div>
            </a>
        </div>
        <div class="col-md-3 text-center">
            <a href="http://{{$url}}/yearly.gif" target="_blank" class="btn btn-system">
                <h5 class="pb-0 mb-0 font-weight-bold">GRÁFIO ANUAL</h5><p class="mb-0">(promedio de 1 día)</p>
                <div class="mb-4 d-none">
                    <img src="http://{{$url}}/yearly.gif" class="d-none img-gafica">
                </div>
            </a>
        </div>
    </div>


    <div id="container" style="min-width: 400px; height: 400px; margin: 0 auto"></div>

    Nombre:	<input name="interface" id="interface" type="text" value="wlan2_58" />

    <select id="type_interface" name="type_interface">
        <option value="0" selected>interfaces</option>
        <option value="1">queues</option>
    </select>

    <div id="trafico"></div>


@endsection

@section('scripts')
<script> 

	function formatBytes(a,b){if(0==a)return"0 Bytes";var c=1024,d=b||2,e=["Bytes","KB","MB","GB","TB","PB","EB","ZB","YB"],f=Math.floor(Math.log(a)/Math.log(c));return parseFloat((a/Math.pow(c,f)).toFixed(d))+" "+e[f]}

	var chart;
	function requestDatta(interface,type_interface) {
		$.ajax({
			url: '/data-grafica?interface='+interface+'&type_interface='+type_interface,
			datatype: "json",
			success: function(data) {
				var midata = JSON.parse(data);
				if( midata.length > 0 ) {
					var TX=parseInt(midata[0].data);
					var RX=parseInt(midata[1].data);
					var x = (new Date()).getTime(); 
					shift=chart.series[0].data.length > 19;
					chart.series[0].addPoint([x, TX], true, shift);
					chart.series[1].addPoint([x, RX], true, shift);
					document.getElementById("trafico").innerHTML=formatBytes(TX) + " / " + formatBytes(RX);
				}else{
					document.getElementById("trafico").innerHTML="- / -";
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) { 
				console.error("Status: " + textStatus + " request: " + XMLHttpRequest); console.error("Error: " + errorThrown); 
			}       
		});
	}	

	$(document).ready(function() {
			Highcharts.createElement('link', {
			    href: 'https://fonts.googleapis.com/css?family=Signika:400,700',
			    rel: 'stylesheet',
			    type: 'text/css'
			}, null, document.getElementsByTagName('head')[0]);

			// Add the background image to the container
			Highcharts.wrap(Highcharts.Chart.prototype, 'getContainer', function (proceed) {
			    proceed.call(this);
			    this.container.style.background =
			        'url(https://www.highcharts.com/samples/graphics/sand.png)';
			});

			Highcharts.setOptions({
				global: {
					useUTC: false
				},
			    colors: ['#40d30e', '#8085e9', '#8d4654', '#7798BF', '#aaeeee',
			        '#ff0066', '#eeaaee', '#55BF3B', '#DF5353', '#7798BF', '#aaeeee'],
			    chart: {
			        backgroundColor: null,
			        style: {
			            fontFamily: 'Signika, serif'
			        }
			    },
			    title: {
			        style: {
			            color: 'black',
			            fontSize: '16px',
			            fontWeight: 'bold'
			        }
			    },
			    subtitle: {
			        style: {
			            color: 'black'
			        }
			    },
			    tooltip: {
			        borderWidth: 0
			    },
			    legend: {
			        itemStyle: {
			            fontWeight: 'bold',
			            fontSize: '13px'
			        }
			    },
			    xAxis: {
			        labels: {
			            style: {
			                color: '#6e6e70'
			            }
			        }
			    },
			    yAxis: {
			        labels: {
			            style: {
			                color: '#6e6e70'
			            }
			        }
			    },
			    plotOptions: {
			        series: {
			            shadow: true
			        },
			        candlestick: {
			            lineColor: '#404048'
			        },
			        map: {
			            shadow: false
			        }
			    },

			    // Highstock specific
			    navigator: {
			        xAxis: {
			            gridLineColor: '#D0D0D8'
			        }
			    },
			    rangeSelector: {
			        buttonTheme: {
			            fill: 'white',
			            stroke: '#C0C0C8',
			            'stroke-width': 1,
			            states: {
			                select: {
			                    fill: '#D0D0D8'
			                }
			            }
			        }
			    },
			    scrollbar: {
			        trackBorderColor: '#C0C0C8'
			    },
			    background2: '#E0E0E8'
			});
	

           chart = new Highcharts.Chart({
			   chart: {
				plotOptions: {
			        areaspline: {
			            fillOpacity: 0.5
			        }
			    },
        		type: 'areaspline',
				renderTo: 'container',
				animation: Highcharts.svg,
				events: {
					load: function () {
						setInterval(function () {
							var e = document.getElementById("type_interface");
							var type_interface = e.options[e.selectedIndex].value;
							requestDatta(document.getElementById("interface").value,type_interface);
						}, 1000);
					}				
			}
		 },
		 title: {
			text: 'Monitoring'
		 },
		 xAxis: {
			type: 'datetime',
				tickPixelInterval: 150,
				maxZoom: 20 * 1000
		 },
		 yAxis: {
			minPadding: 0.2,
				maxPadding: 0.2,
				title: {
					text: 'Trafico',
					margin: 80
				}
		 },
            series: [{
                name: 'Download(TX)',
                data: []
            }, {
                name: 'Upload(RX)',
                data: []
            }]
	  });
  });
</script>

<script type="text/javascript" src="{{ asset('highchart/js/highcharts.js') }}"></script>
<script type="text/javascript" src="{{ asset('highchart/js/modules/exporting.js') }}"></script>
<script type="text/javascript" src="{{ asset('highchart/js/modules/export-data.js') }}"></script>

@endsection
