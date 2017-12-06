


function agregarFilaMobiliarioAdicional(){
    cont++;
    contParque++;
    var equipamiento = 'equipamiento' + cont;
    var cantidad = 'cantidad' + cont;
    var estado = 'estado' + cont;


    $("#pedagogicoAdicionalId").append('<tr id="'+cont+'">\n\
        <td><button type="button" onclick="eliminarFila('+cont+')"><i class="fa fa-remove text-danger"></i></button></td>\n\
        <td><select             name="formMobiliarioAdicional['+cont+'][]" id="formMobiliarioAdicional_'+equipamiento+'" required="true"></select>\n\
        <td><input type="text"  name="formMobiliarioAdicional['+cont+'][]" id="formMobiliarioAdicional_'+cantidad+'" value="" size="5" maxlength="4" required="true"></td>\n\
        <td><select             name="formMobiliarioAdicional['+cont+'][]" id="formMobiliarioAdicional_'+estado+'" required="true"></select>\n\
        </tr>');

        $("#formMobiliarioAdicional_" + equipamiento).empty();
        $("#formMobiliarioAdicional_" + equipamiento).append('<option value="">Seleccionar...</option>');
        $.each(arrayMobiliarioAdiType, function (i, value) {
            $("#formMobiliarioAdicional_" + equipamiento).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#formMobiliarioAdicional_" + estado).empty();
        $("#formMobiliarioAdicional_" + estado).append('<option value="">Seleccionar...</option>');
        $.each(arrayEstadoPiso, function (i, value) {
            $("#formMobiliarioAdicional_" + estado).append('<option value="' + i + '">' + value + '</option>');
        });


    // $("#"+nombre).alphanum({allowOtherCharSets : true, allow:'-.,'});
    // $("#"+area).numeric("positiveInteger");
    // $("#"+nroPlantas).numeric("positiveInteger");
    // $("#"+nroAmbPed).numeric("positiveInteger");
    // $("#"+nroAmbNoPed).numeric("positiveInteger");
    // $("#"+nroTotalAmb).numeric("positiveInteger");
}

function eliminarFila(id){
    cont--;
    $("#" + id).remove();
}
