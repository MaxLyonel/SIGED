


function agregarFilaAlimentacion(){
    cont++;
    contColiseo++;
    var servicio = 'servicio' + cont;
    var nroambientes = 'nroambientes' + cont;
    var estadoambiente = 'estadoambiente' + cont;
    var area = 'area' + cont;

    $("#alimentacionId").append('<tr id="'+cont+'">\n\
        <td><button type="button" onclick="eliminarFila('+cont+')"><i class="fa fa-remove text-danger"></i></button></td>\n\
        <td><select             name="formServicios['+cont+'][]" id="formServicios_'+servicio+'" required="true"></select>\n\
        <td><input type="text"  name="formServicios['+cont+'][]" id="formServicios_'+nroambientes+'" value="" size="5" maxlength="4" required="true"></td>\n\
        <td><select             name="formServicios['+cont+'][]" id="formServicios_'+estadoambiente+'" required="true"></select>\n\
        <td><input type="text"  name="formServicios['+cont+'][]" id="formServicios_'+area+'" value="" size="5" maxlength="4" required="true"></td>\n\
        </tr>');

        $("#formServicios_" + servicio).empty();
        $("#formServicios_" + servicio).append('<option value="">Seleccionar...</option>');
        $.each(arrayServicioType, function (i, value) {
            $("#formServicios_" + servicio).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#formServicios_" + estadoambiente).empty();
        $("#formServicios_" + estadoambiente).append('<option value="">Seleccionar...</option>');
        $.each(arrayEstadoPiso, function (i, value) {
            $("#formServicios_" + estadoambiente).append('<option value="' + i + '">' + value + '</option>');
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
