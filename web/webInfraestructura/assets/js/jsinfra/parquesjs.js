


function agregarFilaParques(){
    cont++;
    contParque++;
    var parques = 'parques' + cont;
    var materialpiso = 'materialpiso' + cont;
    var estadopiso = 'estadopiso' + cont;
    var area = 'area' + cont;


    $("#parquesId").append('<tr id="'+cont+'">\n\
        <td><button type="button" onclick="eliminarFila('+cont+')"><i class="fa fa-remove text-danger"></i></button></td>\n\
        <td>'+contParque+'</td>\n\
        <td>Parques</td>\n\
        <td><select             name="formParques['+cont+'][]" id="formParques_'+materialpiso+'" required="true"></select>\n\
        <td><select             name="formParques['+cont+'][]" id="formParques_'+estadopiso+'" required="true"></select>\n\
        <td><input type="text"  name="formParques['+cont+'][]" id="formParques_'+area+'" value="" size="5" maxlength="4" required="true"></td>\n\
        </tr>');

        $("#formParques_" + materialpiso).empty();
        $("#formParques_" + materialpiso).append('<option value="">Seleccionar...</option>');
        $.each(arrayMaterialPiso, function (i, value) {
            $("#formParques_" + materialpiso).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#formParques_" + estadopiso).empty();
        $("#formParques_" + estadopiso).append('<option value="">Seleccionar...</option>');
        $.each(arrayEstadoPiso, function (i, value) {
            $("#formParques_" + estadopiso).append('<option value="' + i + '">' + value + '</option>');
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
