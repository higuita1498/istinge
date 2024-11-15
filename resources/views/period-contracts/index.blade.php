@extends('layouts.app')

@section('boton')
    <x-modules-header titleModule='Planes asociados a empresas'  hideActions>
        {{-- description='Aquí puedes revisar todas las empresas relacionadas junto con sus respectivo plan' --}}
        <x-slot name="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path
                    d="M12.0896 2.24994C12.5236 2.24956 12.8806 2.24925 13.1934 2.33305C14.0345 2.55844 14.6915 3.21546 14.9169 4.05662C15.0007 4.36936 15.0004 4.7264 15 5.16038V5.16038L15 7.5C15 8.05153 15.0016 8.38973 15.0345 8.63449C15.0634 8.84963 15.1045 8.88555 15.1094 8.88981L15.1102 8.89055C15.1144 8.89543 15.1503 8.93657 15.3655 8.9655C15.6102 8.9984 15.9484 9 16.5 9L18.8396 8.99994C19.2736 8.99956 19.6306 8.99925 19.9433 9.08305C20.7845 9.30844 21.4415 9.96546 21.6669 10.8066C21.7507 11.1194 21.7504 11.4764 21.75 11.9104V12.0896C21.7504 12.5236 21.7507 12.8806 21.6669 13.1934C21.4415 14.0345 20.7845 14.6916 19.9433 14.9169C19.6306 15.0007 19.2736 15.0004 18.8396 15L16.5 15C15.9484 15 15.6102 15.0016 15.3655 15.0345C15.1503 15.0634 15.1144 15.1046 15.1102 15.1094L15.1094 15.1102C15.1045 15.1144 15.0634 15.1504 15.0345 15.3655C15.0016 15.6103 15 15.9485 15 16.5L15 18.8396C15.0004 19.2736 15.0007 19.6306 14.9169 19.9434C14.6915 20.7845 14.0345 21.4416 13.1934 21.6669C12.8806 21.7507 12.5236 21.7504 12.0896 21.75H11.9104C11.4764 21.7504 11.1194 21.7507 10.8066 21.6669C9.96545 21.4415 9.30842 20.7845 9.08304 19.9434C8.99924 19.6306 8.99955 19.2736 8.99993 18.8396L8.99998 16.5C8.99998 15.9485 8.99839 15.6103 8.96548 15.3655C8.93655 15.1504 8.89542 15.1144 8.89054 15.1102L8.88979 15.1094C8.88553 15.1046 8.84961 15.0634 8.63448 15.0345C8.38971 15.0016 8.05151 15 7.49998 15L5.16037 15H5.16036C4.72639 15.0004 4.36935 15.0007 4.05661 14.9169C3.21545 14.6916 2.55842 14.0345 2.33304 13.1934C2.24924 12.8806 2.24955 12.5236 2.24993 12.0896V11.9104C2.24955 11.4764 2.24924 11.1194 2.33304 10.8066C2.55842 9.96546 3.21545 9.30844 4.05661 9.08305C4.36935 8.99925 4.72639 8.99956 5.16037 8.99994L7.49998 9C8.05151 9 8.38971 8.9984 8.63448 8.9655C8.84961 8.93657 8.88553 8.89543 8.88979 8.89055L8.89054 8.88981C8.89542 8.88555 8.93656 8.84963 8.96548 8.63449C8.99839 8.38973 8.99998 8.05153 8.99998 7.49999L8.99993 5.16038C8.99955 4.72641 8.99924 4.36937 9.08304 4.05662C9.30842 3.21546 9.96545 2.55844 10.8066 2.33305C11.1194 2.24925 11.4764 2.24956 11.9104 2.24994H12.0896Z"
                    fill="var(--gestoru-secundario)" />
            </svg>
        </x-slot>

        <x-slot name="buttonAditional">
            <a class="btn-actions create" href="{{ route('period.contract.create') }}" style="text-decoration: none;">
                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M8 5.25C4.27208 5.25 1.25 8.27208 1.25 12C1.25 15.7279 4.27208 18.75 8 18.75H16C19.7279 18.75 22.75 15.7279 22.75 12C22.75 8.27208 19.7279 5.25 16 5.25H8ZM16 8.25C13.9289 8.25 12.25 9.92893 12.25 12C12.25 14.0711 13.9289 15.75 16 15.75C18.0711 15.75 19.75 14.0711 19.75 12C19.75 9.92893 18.0711 8.25 16 8.25Z"
                        fill="#63ECBC" />
                </svg>
                Activar plan a empresa
            </a>
        </x-slot>
    </x-modules-header>
@endsection

@section('content')
    <style>
        #table-periods-contracts th {
            color: white;
            background: linear-gradient(182deg, #1b3354 31.03%, #001128 99.96%);
        }
    </style>

    @if (Session::has('success'))
        <div class="alert alert-success">
            {{ Session::get('success') }}
        </div>

        <script type="text/javascript">
            setTimeout(function() {
                $('.alert').hide();
            }, 5000);
        </script>
    @endif
    @if (Session::has('info'))
        <div class="alert alert-info">
            {{ Session::get('info') }}
        </div>

        <script type="text/javascript">
            setTimeout(function() {
                $('.alert').hide();
            }, 10000);
        </script>
    @endif

    <div class="">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-body">
                        <table class="table table-striped table-bordered" id="table-periods-contracts">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Empresa</th>
                                    <th>Limite de facturas</th>
                                    <th>Limite de monto</th>
                                    <th>Periodo</th>
                                    <th>Contrato</th>
                                    <th>Estado</th>
                                    <th>Vencimiento</th>
                                    <th>Creacion</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($periodContracts as $periodContract)
                                    <tr>
                                        <td>{{ $periodContract->id }}</td>
                                        <td>{{ $periodContract->contract->company?->nombre ?? 'Sin nombre' }}</td>
                                        <td>{{ $periodContract->limit_invoices }}</td>
                                        <td>{{ $periodContract->limit_billing }}</td>
                                        <td>{{ $periodContract->periodBilling->months }} Meses</td>
                                        <td>{{ $periodContract->contract->id }}</td>
                                        <td>{{ $periodContract->status ? 'activo' : 'inactivo' }}</td>
                                        <td>{{ $periodContract->dueDate }}</td>
                                        <td>{{ $periodContract->created_at }}</td>
                                        <td><a href="{{ route('period.contract.show', $periodContract->id) }}">Ver | <a
                                                    href="{{ route('period.contract.create', ['period_contract' => $periodContract->id, 'contract' => $periodContract->contract->id]) }}">Mejorar</a></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#table-periods-contracts').DataTable();
        });
    </script>
@endsection
