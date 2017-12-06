


function agregarFilaMobiliario(){
    cont++;
    var equipamiento = 'equipamiento' + cont;
    var aulas = 'aulas' + cont;
    var talleres = 'talleres' + cont;
    var laboratorios = 'laboratorios' + cont;
    var bibliotecas = 'bibliotecas' + cont;
    var salas = 'salas' + cont;
    var estado = 'estado' + cont;

    $("#pedagogicoId").append('<tr id="'+cont+'">\n\
        <td><button type="button" onclick="eliminarFila('+cont+')"><i class="fa fa-remove text-danger"></i></button></td>\n\
        <td><select            name="formMobiliario['+cont+'][]" id="formMobiliario_'+equipamiento+'" required="true"></select>\n\
        <td><input type="text" name="formMobiliario['+cont+'][]" id="formMobiliario_'+aulas+'" value="" size="5" maxlength="4" required="true"></td>\n\
        <td><input type="text" name="formMobiliario['+cont+'][]" id="formMobiliario_'+talleres+'" value="" size="5" maxlength="4" required="true"></td>\n\
        <td><input type="text" name="formMobiliario['+cont+'][]" id="formMobiliario_'+laboratorios+'" value="" size="5" maxlength="4" required="true"></td>\n\
        <td><input type="text" name="formMobiliario['+cont+'][]" id="formMobiliario_'+bibliotecas+'" value="" size="5" maxlength="4" required="true"></td>\n\
        <td><input type="text" name="formMobiliario['+cont+'][]" id="formMobiliario_'+salas+'" value="" size="5" maxlength="4" required="true"></td>\n\
        <td><select            name="formMobiliario['+cont+'][]" id="formMobiliario_'+estado+'" required="true"></select>\n\
        </tr>');

        $("#formMobiliario_" + equipamiento).empty();
        $("#formMobiliario_" + equipamiento).append('<option value="">Seleccionar...</option>');
        $.each(arrayMobiliarioType, function (i, value) {
            $("#formMobiliario_" + equipamiento).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#formMobiliario_" + estado).empty();
        $("#formMobiliario_" + estado).append('<option value="">Seleccionar...</option>');
        $.each(arrayEstadoPiso, function (i, value) {
            $("#formMobiliario_" + estado).append('<option value="' + i + '">' + value + '</option>');
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
