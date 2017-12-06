


function agregarFila(){
    cont++;
    var paredes = 'paredes' + cont;
    var techo = 'techo' + cont;
    var piso = 'piso' + cont;
    var cieloraso = 'cieloraso' + cont;
    var ambientes = 'ambientes' + cont;
    var habitantes = 'habitantes' + cont;
    var toilet = 'toilet' + cont;
    var ducha = 'ducha' + cont;
    var cocina = 'cocina' + cont;
    var area = 'area' + cont;

    $("#viviendasId").append('<tr id="'+cont+'">\n\
        <td><button type="button" onclick="eliminarFila('+cont+')"><i class="fa fa-remove text-danger"></i></button></td>\n\
        <td><select            name="formVivienda['+cont+'][]" id="formVivienda_'+paredes+'" required="true"></select>\n\
        <td><select            name="formVivienda['+cont+'][]" id="formVivienda_'+techo+'" required="true"></select>\n\
        <td><select            name="formVivienda['+cont+'][]" id="formVivienda_'+piso+'" required="true"></select>\n\
        <td><select            name="formVivienda['+cont+'][]" id="formVivienda_'+cieloraso+'" required="true"></select>\n\
        <td><input type="text" name="formVivienda['+cont+'][]" id="formVivienda_'+ambientes+'" value="" size="5" maxlength="4" required="true"></td>\n\
        <td><input type="text" name="formVivienda['+cont+'][]" id="formVivienda_'+habitantes+'" value="" size="5" maxlength="4" required="true"></td>\n\
        <td><select            name="formVivienda['+cont+'][]" id="formVivienda_'+toilet+'" required="true"></select>\n\
        <td><select            name="formVivienda['+cont+'][]" id="formVivienda_'+ducha+'" required="true"></select>\n\
        <td><select            name="formVivienda['+cont+'][]" id="formVivienda_'+cocina+'" required="true"></select>\n\
        <td><input type="text" name="formVivienda['+cont+'][]" id="formVivienda_'+area+'" value="" size="5" maxlength="4" required="true"></td>\n\
        </tr>');

        $("#formVivienda_" + paredes).empty();
        $("#formVivienda_" + paredes).append('<option value="">Seleccionar...</option>');
        $.each(arrayEstadoPiso, function (i, value) {
            $("#formVivienda_" + paredes).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#formVivienda_" + techo).empty();
        $("#formVivienda_" + techo).append('<option value="">Seleccionar...</option>');
        $.each(arrayEstadoPiso, function (i, value) {
            $("#formVivienda_" + techo).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#formVivienda_" + piso).empty();
        $("#formVivienda_" + piso).append('<option value="">Seleccionar...</option>');
        $.each(arrayEstadoPiso, function (i, value) {
            $("#formVivienda_" + piso).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#formVivienda_" + cieloraso).empty();
        $("#formVivienda_" + cieloraso).append('<option value="">Seleccionar...</option>');
        $.each(arrayEstadoPiso, function (i, value) {
            $("#formVivienda_" + cieloraso).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#formVivienda_" + toilet).empty();
        $("#formVivienda_" + toilet).append('<option value="">Seleccionar...</option>');
        $.each(arrayYesNoBoll, function (i, value) {
            $("#formVivienda_" + toilet).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#formVivienda_" + ducha).empty();
        $("#formVivienda_" + ducha).append('<option value="">Seleccionar...</option>');
        $.each(arrayYesNoBoll, function (i, value) {
            $("#formVivienda_" + ducha).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#formVivienda_" + cocina).empty();
        $("#formVivienda_" + cocina).append('<option value="">Seleccionar...</option>');
        $.each(arrayYesNoBoll, function (i, value) {
            $("#formVivienda_" + cocina).append('<option value="' + i + '">' + value + '</option>');
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
