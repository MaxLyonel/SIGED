function addAmbPedagogicoEquipamiento(){
	
    countequipo++;
        var id                                  = 'id'+countequipo;
        var n53Cantidad                         = 'n52Cantidad'+countequipo;
        var n53EstadoTipo                       = 'n52EstadoTipo'+countequipo;
        var n53EquiposTipo                   = 'n52MobiliarioTipo'+countequipo;
        var infraestructuraH5Ambientepedagogico = 'infraestructuraH5Ambientepedagogico'+countequipo;
        // alert(valores.id);
        $("#equipamientoId").append('<tr id="'+countequipo+'">\n\
          <td><button type="button" onclick="eliminarFila('+countequipo+')"><i class="fa fa-trash text-danger" class="form-control"></i></button></td>\n\
          <td><select name="form[n53EquiposTipo][]" id="form_'+n53EquiposTipo+'" required="true" class="form-control input-sm mb-15" title="n52MobiliarioTipo Tenencia" class="form-control"></select></td>\n\
          <td><input type="text" name="form[n53Cantidad][]" id="form_'+n53Cantidad+'" value="" required="true" size="5" class="form-control"></td>\n\
          <td><select name="form[n53EstadoTipo][]" id="form_'+n53EstadoTipo+'" required="true" class="form-control imput-sm mb-10" title="n52EstadoTipo" class="form-control"></select></td>\n\
          </tr>');

        $("#form_" + n53EquiposTipo).empty();
        $("#form_" + n53EquiposTipo).append('<option value="">Seleccionar...</option>');
        $.each(arrayDataEquipamientoTipo, function (i, value) {
            $("#form_" + n53EquiposTipo).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#form_" + n53EstadoTipo).empty();
        $("#form_" + n53EstadoTipo).append('<option value="">Seleccionar...</option>');
        $.each(arrayDataStatusTipo, function (i, value) {
            $("#form_" + n53EstadoTipo).append('<option value="' + i + '" >' + value + '</option>');
        });

    
};


function eliminarFila(id){
    countequipo--;
    $("#" + id).remove();
};

