
  
  <!-- Modal -->
  <div class="modal fade" id="modal-edit-etiqueta" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Crear nueva etiqueta</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <label for="edit-nombre" class="form-label">Nombre</label>
            <input type="text" name="nombre" id="edit-nombre" class="form-control">
            <br>
            <label for="edit-color" class="form-label">Seleccione un color</label>
            <input type="text" name="color" id="edit-color" class="" autocomplete="off">
            <input type="hidden" id="id-etiqueta">
          ...
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" onclick="updateEtiqueta()">Guardar</button>
        </div>
      </div>
    </div>
  </div>


  <script>

    function editEtiqueta(id, nombre, color){
        $('#modal-edit-etiqueta').modal('show');
        $('#id-etiqueta').val(id);
        $('#edit-nombre').val(nombre);
        $('#edit-color').val(color);
    }

    function updateEtiqueta(){

        var _token = $('meta[name="csrf-token"]').attr('content');

        if(!(nombre = $('#edit-nombre').val())){
            return '';
        }

        if(!(color = $('#edit-color').val())){
            return '';
        }

        let data = {
            _method: 'PUT',
            _token: _token,
            nombre: nombre,
            color: color, 
        }
    
        $.post('{{URL::to('/')}}/empresa/etiqueta'+'/'+($('#id-etiqueta').val()), data, function(response){
           let etiqueta = response;
          $('#modal-edit-etiqueta').modal('hide');
          $('#rw-'+etiqueta.id).remove();

          $('#modal-etiqueta').modal('hide');
          $('#data-etiquetas').prepend(`
          <tr>
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