


function agregarFilaDormitorio(){
    cont++;
    var dormitorios = 'dormitorios' + cont;
    var cantidad = 'cantidad' + cont;
    var paredes = 'paredes' + cont;
    var techo = 'techo' + cont;
    var piso = 'piso' + cont;
    var cieloraso = 'cieloraso' + cont;
    var ambientes = 'ambientes' + cont;
    var camaslitera = 'camaslitera' + cont;
    var camassimples = 'camassimples' + cont;
    var camasotro = 'camasotro' + cont;
    var area = 'area' + cont;

    $("#dormitoriosId").append('<tr id="'+cont+'">\n\
        <td><button type="button" onclick="eliminarFila('+cont+')"><i class="fa fa-remove text-danger"></i></button></td>\n\
        <td><select            name="formDormitorios['+cont+'][]" id="formDormitorios_'+dormitorios+'" required="true"></select>\n\
        <td><input type="text" name="formDormitorios['+cont+'][]" id="formDormitorios_'+cantidad+'" value="" size="5" maxlength="4" required="true"></td>\n\
        <td><select            name="formDormitorios['+cont+'][]" id="formDormitorios_'+paredes+'" required="true"></select>\n\
        <td><select            name="formDormitorios['+cont+'][]" id="formDormitorios_'+techo+'" required="true"></select>\n\
        <td><select            name="formDormitorios['+cont+'][]" id="formDormitorios_'+piso+'" required="true"></select>\n\
        <td><select            name="formDormitorios['+cont+'][]" id="formDormitorios_'+cieloraso+'" required="true"></select>\n\
        <td><input type="text" name="formDormitorios['+cont+'][]" id="formDormitorios_'+camaslitera+'" value="" size="5" maxlength="4" required="true"></td>\n\
        <td><input type="text" name="formDormitorios['+cont+'][]" id="formDormitorios_'+camassimples+'" value="" size="5" maxlength="4" required="true"></td>\n\
        <td><input type="text" name="formDormitorios['+cont+'][]" id="formDormitorios_'+camasotro+'" value="" size="5" maxlength="4" required="true"></td>\n\
        <td><input type="text" name="formDormitorios['+cont+'][]" id="formDormitorios_'+area+'" value="" size="5" maxlength="4" required="true"></td>\n\
        </tr>');


                $("#formDormitorios_" + dormitorios).empty();
                $("#formDormitorios_" + dormitorios).append('<option value="">Seleccionar...</option>');
                $.each(arrayGeneroDataType, function (i, value) {
                    $("#formDormitorios_" + dormitorios).append('<option value="' + i + '">' + value + '</option>');
                });

                $("#formDormitorios_" + paredes).empty();
                $("#formDormitorios_" + paredes).append('<option value="">Seleccionar...</option>');
                $.each(arrayEstadoPiso, function (i, value) {
                    $("#formDormitorios_" + paredes).append('<option value="' + i + '">' + value + '</option>');
                });

                $("#formDormitorios_" + techo).empty();
                $("#formDormitorios_" + techo).append('<option value="">Seleccionar...</option>');
                $.each(arrayEstadoPiso, function (i, value) {
                    $("#formDormitorios_" + techo).append('<option value="' + i + '">' + value + '</option>');
                });

                $("#formDormitorios_" + piso).empty();
                $("#formDormitorios_" + piso).append('<option value="">Seleccionar...</option>');
                $.each(arrayEstadoPiso, function (i, value) {
                    $("#formDormitorios_" + piso).append('<option value="' + i + '">' + value + '</option>');
                });

                $("#formDormitorios_" + cieloraso).empty();
                $("#formDormitorios_" + cieloraso).append('<option value="">Seleccionar...</option>');
                $.each(arrayEstadoPiso, function (i, value) {
                    $("#formDormitorios_" + cieloraso).append('<option value="' + i + '">' + value + '</option>');
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
