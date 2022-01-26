


function agregarFilaEquipo(){
    cont++;
    var equipamiento = 'equipamiento' + cont;
    var bueno = 'bueno' + cont;
    var regular = 'regular' + cont;
    var malo = 'malo' + cont;

    $("#equipamientoId").append('<tr id="'+cont+'">\n\
        <td><button type="button" onclick="eliminarFila('+cont+')"><i class="fa fa-remove text-danger"></i></button></td>\n\
        <td><select            name="formEquipa['+cont+'][]" id="formEquipa_'+equipamiento+'" required="true"></select>\n\
        <td><input type="text" name="formEquipa['+cont+'][]" id="formEquipa_'+bueno+'" value="" size="5" maxlength="4" required="true"></td>\n\
        <td><input type="text" name="formEquipa['+cont+'][]" id="formEquipa_'+regular+'" value="" size="5" maxlength="4" required="true"></td>\n\
        <td><input type="text" name="formEquipa['+cont+'][]" id="formEquipa_'+malo+'" value="" size="5" maxlength="4" required="true"></td>\n\
        </tr>');

        $("#formEquipa_" + equipamiento).empty();
        $("#formEquipa_" + equipamiento).append('<option value="">Seleccionar...</option>');
        $.each(arrayEquipamientoAdm, function (i, value) {
            $("#formEquipa_" + equipamiento).append('<option value="' + i + '">' + value + '</option>');
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
