@extends('layouts.app')

@section('content')

    <div class="section3">
        <center>
            <div class="enlamitad">
                <div class="barra-princi-pagos">
                    <h1>
                        <i class="fas fa-shopping-cart"></i> !Bien Hecho!, has escogido el {{$tipo}}
                        {{Auth()->user()->email}}
                    </h1>
                    <input type="hidden" name="plan" id="plan" value="{{$plan->id}}">
                    <input type="hidden" id="personalPlan" value="{{$personalizado}}">
                    <input type="hidden" name="meses" id="p_meses" value="{{$plan->meses}}">
                </div>
                <div id="ocult-planfre" style="display: none;">
                    <form method="POST" action="">
                        {{ csrf_field() }}
                        <div class="sections-in-gral-payment">
                            <div class="sct-1forpayment fact-table">
                                <table class="tableforpayment"  style="min-width: 517px;">
                                    <tr class="theadforpayment">
                                        <th style="font-size: large">Período</th>
                                        <th style="font-size: large">Precio</th>
                                    </tr>
                                    <tr class="trforpayment">
                                            <div style="display: none">
                                                <p><input class="rd-left" type="radio" name="optradio" id="optradio1" value="{{$plan->precio}}"  checked readonly>Pago</p>
                                            </div>
                                        <td style="font-size: large">
                                            <p style="font-size: large">
                                                {!!  ($plan->meses > 1) ? "<span class='font-weight-bold'>$plan->meses</span> meses" : "<span class='font-weight-bold'>$plan->meses</span> mes"!!}
                                            </p>
                                        </td>
                                        <td>
                                            <p style="font-size: large">COP $ <span class="font-weight-bold">{{\App\Funcion::Parsear($plan->precio)}}</span></p>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="sct-2forpayment">
                                <div class="sct2-all">
                                    <div class="sct2-ttl">
                                        <h2>Total a Pagar</h2>
                                    </div>
                                    <div class="sct2-value">
                                        <h4 id="totalvalue"></h4>
                                    </div>
                                </div>
                                <div class="sct2-btn">
                                    {{--	<button type="submit" class="btn-obtener-pln btn-alarged">Pagar</button>--}}
                                    <div id="idPayuButtonContainer">

                                    </div>

                                </div>
                            </div>

                        </div>

                    </form>
                </div>
            </div>
        </center>
    </div>



@endsection
