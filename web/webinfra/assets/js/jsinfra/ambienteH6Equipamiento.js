
function addAmbEquipamiento(){
  
    
     countambequip++;
      var id                                  = 'id'+countambequip;
      var n63Cantidad                         = 'n63Cantidad'+countambequip;
      var n63EstadoTipo                       = 'n63EstadoTipo'+countambequip;
      var n63EquipamientoTipo                   = 'n63EquipamientoTipo'+countambequip;
      var infraestructuraH5Ambientepedagogico = 'infraestructuraH5Ambientepedagogico'+countambequip;
    

    $("#equipamientoId").append('<tr id="'+countambequip+'">\n\
    <td><button type="button" onclick="eliminarFila('+countambequip+')"><i class="fa fa-trash text-danger" class="form-control"></i></button></td>\n\
    <td><select name="form[n63EquipamientoTipo][]" id="form_'+n63EquipamientoTipo+'" required="true" title="n63EquipamientoTipo Tenencia" class="form-control input-sm mb-15"></select></td>\n\
    <td><input type="text" name="form[n63Cantidad][]" id="form_'+n63Cantidad+'" value="" required="true" size="5" class="form-control"></td>\n\
    <td><select name="form[n63EstadoTipo][]" id="form_'+n63EstadoTipo+'" required="true" class="form-control input-sm mb-15" title="n63EstadoTipo" class="form-control"></select></td>\n\
    </tr>');

    $("#form_" + n63EquipamientoTipo).empty();
    $("#form_" + n63EquipamientoTipo).append('<option value="">Seleccionar...</option>');
    $.each(arrayDataEquipamientoTipo, function (i, value) {
        $("#form_" + n63EquipamientoTipo).append('<option value="' + i + '">' + value + '</option>');
    });

    $("#form_" + n63EstadoTipo).empty();
    $("#form_" + n63EstadoTipo).append('<option value="">Seleccionar...</option>');
    $.each(arrayDataStatusTipo, function (i, value) {
        $("#form_" + n63EstadoTipo).append('<option value="' + i + '">' + value + '</option>');
    });

  
};


function eliminarFila(id){
    countambequip--;
    $("#" + id).remove();
}
