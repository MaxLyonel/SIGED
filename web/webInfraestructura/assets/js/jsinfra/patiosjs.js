


function agregarFilaPatios(){
    cont++;
    contPatio++;
    var patios = 'patios' + cont;
    var materialpiso = 'materialpiso' + cont;
    var estadopiso = 'estadopiso' + cont;
    var area = 'area' + cont;


    $("#patiosId").append('<tr id="'+cont+'">\n\
        <td><button type="button" onclick="eliminarFila('+cont+')"><i class="fa fa-remove text-danger"></i></button></td>\n\
        <td>'+contPatio+'</td>\n\
        <td>Patios</td>\n\
        <td><select             name="formPatios['+cont+'][]" id="formPatios_'+materialpiso+'" required="true"></select>\n\
        <td><select             name="formPatios['+cont+'][]"   id="formPatios_'+estadopiso+'" required="true"></select>\n\
        <td><input type="text"  name="formPatios['+cont+'][]"         id="formPatios_'+area+'" value="" size="5" maxlength="4" required="true"></td>\n\
        </tr>');

        $("#formPatios_" + materialpiso).empty();
        $("#formPatios_" + materialpiso).append('<option value="">Seleccionar...</option>');
        $.each(arrayMaterialPiso, function (i, value) {
            $("#formPatios_" + materialpiso).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#formPatios_" + estadopiso).empty();
        $("#formPatios_" + estadopiso).append('<option value="">Seleccionar...</option>');
        $.each(arrayEstadoPiso, function (i, value) {
            $("#formPatios_" + estadopiso).append('<option value="' + i + '">' + value + '</option>');
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
