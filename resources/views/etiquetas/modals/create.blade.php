

  <!-- Modal -->
  <div class="modal fade" id="modal-etiqueta" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Crear nueva etiqueta</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <form action="{{ route('etiqueta.store') }}" id="store-etiqueta">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" name="nombre" id="nombre" placeholder="Nombre" class="form-control">
            <br>
            <label for="color" class="form-label">Seleccione un color</label>
            <input type="color" name="color" id="color" placeholder="Color" class="" autocomplete="off">
          ...
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" onclick="guardarEtiqueta()">Guardar</button>
        </div>
      </div>
    </div>
  </div>


  <script>



    function guardarEtiqueta(){

        var _token = $('meta[name="csrf-token"]').attr('content');

        if(!(nombre = $('#nombre').val())){
            return '';
        }

        if(!(color = $('#color').val())){
            return '';
        }

        let data = {
            method: 'POST',
            _token: _token,
            nombre: nombre,
            color: color,
        }

        $.post($('#store-etiqueta').attr('action'), data, function(response){
           let etiqueta = response;
          $('#modal-etiqueta').modal('hide');
          $('#data-etiquetas').prepend(`
          <tr id="rw-${etiqueta.id}">
            <td>${etiqueta.nombre}</td>
            <td style="background-color:${etiqueta.color}">${etiqueta.color}</td>
            <td>${etiqueta.fecha}</td>
            <td>${etiqueta.acciones}</td>
          </tr>
          `);

          $('#edit-nombre').val('');
          $('#edit-color').val('');

        });

    }

  </script>
