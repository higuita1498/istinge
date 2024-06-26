@extends('layouts.app')

@section('boton')
    <a href="{{ route('contratos.show',$contrato->id )}}"  class="btn btn-primary" title="Regresar al Detalle"><i class="fas fa-step-backward"></i></i> Regresar al Detalle</a>
@endsection

@section('content')

    <input type="hidden" id="type_interface" name="type_interface" value="">
    <input type="hidden" id="interface" name="interface" value="">
    <input type="hidden" id="id" name="id" value="{{$contrato->id}}">

	<div id="container" style="min-width: 400px; height: 400px; margin: 0 auto"></div>

    <center><div id="trafico" style="font-weight: 600;font-family: Signika, serif;font-size: 1.5em;"></div></center>
@endsection

@section('scripts')
<script type="text/javascript" src="{{asset('vendors/highchart/js/highcharts.js')}}"></script>
	<script type="text/javascript" src="{{asset('vendors/highchart/js/modules/exporting.js')}}"></script>
	<script type="text/javascript" src="{{asset('vendors/highchart/js/modules/export-data.js')}}"></script>
<script> 
	function formatBytes(a,b){if(0==a)return"0 Bytes";var c=1024,d=b||2,e=["Bytes","KB","MB","GB","TB","PB","EB","ZB","YB"],f=Math.floor(Math.log(a)/Math.log(c));return parseFloat((a/Math.pow(c,f)).toFixed(d))+" "+e[f]}

	var chart;
	function requestDatta(interface,type_interface) {
	    var id = $("#id").val();
		$.ajax({
			url: 'graficajson',
			datatype: "json",
			success: function(data) {
				if(data.success==false){
					window.stop();
				}else{
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
			});

			Highcharts.setOptions({
				global: {
					useUTC: false
				},
				credits: {
				    enabled: false
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
							requestDatta(document.getElementById("interface").value,document.getElementById("type_interface").value);
						}, 1000);
					}				
			}
		 },
		 title: {
			text: 'Gráfica de Conexión en Tiempo Real'
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
