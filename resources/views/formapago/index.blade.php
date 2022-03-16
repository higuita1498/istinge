@extends('layouts.app')

@section('content')

<style>
    .table-forma1{
        border:none;width:98%;height:auto;
        margin:10px;
    }

    .table-forma1 thead{
        background-color:#ccc;
    }

    .forma-check{
        margin-left: 10px;
    }
</style>


    <table class="table-forma1">
    <thead>
        <tr>
        <th>En uso</th>
        <th>Código</th>
        <th>Nombre</th>
        <th>Relacionado con</th>
        <th>cuenta contable</th>
        <th>Medio de pago<th>
        </tr>
    </thead>
    <tr>
      <td>
        <div class="">
            <label class="form-check-label">
                <input type="checkbox" class="forma-check" name="contacto[]" value="0" checked="">
                <i class="input-helper"></i>
            </label>
        </div>
      </td>
      <td>1</td>
      <td>Efectivo</td>
      <td>Cartera / Proveedores</td>
      <td>015645465 - Caja general</td>
      <td>Efectivo</td>
    </tr>
  </table>


@endsection
