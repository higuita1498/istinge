@extends('layouts.app')

@section('boton')
    <a href="{{ route('mikrotik.show',$mikrotik->id )}}"  class="btn btn-danger btn-sm" title="Regresar"><i class="fas fa-step-backward"></i></i> Regresar al detalle</a>
@endsection

@section('style')
    <style>
        .info th {
            text-align: center;
        }
    </style>
@endsection

@section('content')
    @if(Session::has('danger'))
		<div class="alert alert-danger" >
			{{Session::get('danger')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 10000);
		</script>
	@endif
	
	<div class="row card-description">
    	<div class="col-md-12">
    	    <div class="table-responsive">
    			<table class="table table-striped table-bordered table-sm info">
    				<tbody>
    					<tr>
    						<th class="bg-th">Nombre</th>
    						<th class="bg-th">IP</th>
    						<th class="bg-th">Puerto API</th>
    						<th class="bg-th">Puerto WEB</th>
    						<th class="bg-th">Segmento</th>
    						<th class="bg-th">Interfaz WAN</th>
    						<th class="bg-th">Estado</th>
    					</tr>
    					<tr class="text-center">
    						<td>{{ $mikrotik->nombre }}</td>
    						<td>{{ $mikrotik->ip }}</td>
    						<td>{{ $mikrotik->puerto_api }}</td>
    						<td>{{ $mikrotik->puerto_web }}</td>
    						<td>@foreach($segmentos as $segmento)
						            {{$segmento->segmento}}<br>
						        @endforeach
						    </td>
						    <td>{{ $mikrotik->interfaz }}</td>
    						<td><span class="font-weight-bold text-{{$mikrotik->status('true')}}">{{ $mikrotik->status() }}</span></td>
    					</tr>
    				</tbody>
    			</table>
    		</div>
    	</div>

    	<div class="col-md-12">
    		<div class="row text-center">
		        <input type="hidden" id="id" name="id" value="{{$mikrotik->id}}">
		    	<div class="col-md-4 form-group mt-5 offset-md-4">
		            <div class="input-group">
		                <select class="form-control selectpicker" name="interfaz" id="interfaz" required="" title="Seleccione la Interfaz" data-live-search="true" data-size="5">
		                </select>
		            </div>
		        </div>
	        </div>
        </div>

    	<div class="col-md-12 d-none" id="div_grafica">
    	    <div id="container" style="min-width: 400px; height: 400px; margin: 30px auto"></div>
    	    <center><div id="trafico" style="font-weight: 600;font-family: Signika, serif;font-size: 1.5em;"></div></center>
    	</div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript" src="{{asset('vendors/highchart/js/highcharts.js')}}"></script>
	<script type="text/javascript" src="{{asset('vendors/highchart/js/modules/exporting.js')}}"></script>
	<script type="text/javascript" src="{{asset('vendors/highchart/js/modules/export-data.js')}}"></script>
    <script> 
    	function formatBytes(a,b){if(0==a)return"0 Bytes";var c=1024,d=b||2,e=["Bytes","KB","MB","GB","TB","PB","EB","ZB","YB"],f=Math.floor(Math.log(a)/Math.log(c));return parseFloat((a/Math.pow(c,f)).toFixed(d))+" "+e[f]}
    
    	var chart;

    	function requestDatta(interfaz) {
    	    if(interfaz){
	    		$.ajax({
	    			url: 'graficajson',
	    			datatype: "json",
	    			success: function(data) {
	    				var midata = JSON.parse(data);
	    				$("#div_grafica").removeClass('d-none');
	    				if( midata.length > 0 ) {
	    					var RX=parseInt(midata[0].data);
	    					var TX=parseInt(midata[1].data);
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
	    				$("#div_grafica").addClass('d-none');
	    				Swal.fire({
		                    type: 'error',
		                    title: errorThrown,
		                    html: '',
		                })
		                $('#interfaz').val('').selectpicker('refresh');
	    				console.error("Status: " + textStatus + " request: " + XMLHttpRequest); console.error("Error: " + errorThrown);
	    			}
	    		});
    		}
    	}	
    
    	$(document).ready(function() {
    		getInterfaces({{$mikrotik->id}});
			Highcharts.createElement('link', {
			    href: 'https://fonts.googleapis.com/css?family=Signika:400,700',
			    rel: 'stylesheet',
			    type: 'text/css'
			}, null, document.getElementsByTagName('head')[0]);
			
			Highcharts.wrap(Highcharts.Chart.prototype, 'getContainer', function (proceed) {
			    proceed.call(this);
			});
			
			Highcharts.setOptions({
				global: {
					useUTC: false
				},
				credits: {
				    enabled: false
				},
				colors: ['#40d30e', '#8085e9', '#8d4654', '#7798BF', '#aaeeee', '#ff0066', '#eeaaee', '#55BF3B', '#DF5353', '#7798BF', '#aaeeee'],
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
							requestDatta(document.getElementById("interfaz").value,);
						}, 1000);
					}				
				}
    		 },
    		 title: {
    			text: 'Gráfica de Consumo en Tiempo Real'
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
    					text: 'Tráfico',
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
@endsection
