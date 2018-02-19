
function addAmbPedagogicoDeportivoEquipamiento(){
  // countdeportivo ++;
  
    
     countdeportivo++;
      var id                                           = 'id'+countdeportivo;
      var n531Cantidad                                 = 'n531Cantidad'+countdeportivo;
      var n531EstadoEquipamientoTipo                   = 'n531EstadoEquipamientoTipo'+countdeportivo;
      var n531EquipamientoTipo                         = 'n531EquipamientoTipo'+countdeportivo;
      var infraestructuraH5AmbientepedagogicoDeportivo = 'infraestructuraH5AmbientepedagogicoDeportivo'+countdeportivo;
    

    $("#deportivoEquipamientoId").append('<tr id="'+countdeportivo+'">\n\
    <td><button type="button" onclick="eliminarFila('+countdeportivo+')"><i class="fa fa-trash text-danger" class="form-control"></i></button></td>\n\
    <td><select name="form[n531EstadoEquipamientoTipo][]" id="form_'+n531EstadoEquipamientoTipo+'" required="true" class="form-control input-sm mb-15" title="n531EstadoEquipamientoTipo" class="form-control"></select></td>\n\
    <td><input type="text" name="form[n531Cantidad][]" id="form_'+n531Cantidad+'" value="" required="true" size="5" class="form-control"></td>\n\
    <td><select name="form[n531EquipamientoTipo][]" id="form_'+n531EquipamientoTipo+'" required="true" title="n531EquipamientoTipo Tenencia" class="form-control input-sm mb-15"></select></td>\n\
    </tr>');

    $("#form_" + n531EstadoEquipamientoTipo).empty();
    $("#form_" + n531EstadoEquipamientoTipo).append('<option value="">Seleccionar...</option>');
    $.each(arrayDataDeportivoEquiposTipo, function (i, value) {
        $("#form_" + n531EstadoEquipamientoTipo).append('<option value="' + i + '">' + value + '</option>');
    });

    $("#form_" + n531EquipamientoTipo).empty();
    $("#form_" + n531EquipamientoTipo).append('<option value="">Seleccionar...</option>');
    $.each(arrayDataStatusTipo, function (i, value) {
        $("#form_" + n531EquipamientoTipo).append('<option value="' + i + '">' + value + '</option>');
    });

      
};


function eliminarFila(id){
    countdeportivo--;
    $("#" + id).remove();
}
