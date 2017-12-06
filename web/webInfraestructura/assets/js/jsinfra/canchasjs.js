


function agregarFilaCanchas(){
    cont++;
    contCancha++;
    var canchas = 'canchas' + cont;
    var destinado = 'destinado' + cont;
    var materialpiso = 'materialpiso' + cont;
    var estadopiso = 'estadopiso' + cont;
    var techado = 'techado' + cont;
    var graderias = 'graderias' + cont;
    var capacidad = 'capacidad' + cont;
    var area = 'area' + cont;


    $("#canchasId").append('<tr id="'+cont+'">\n\
        <td><button type="button" onclick="eliminarFila('+cont+')"><i class="fa fa-remove text-danger"></i></button></td>\n\
        <td>'+contCancha+'</td>\n\
        <td>Canchas</td>\n\
        <td><input type="text"  name="formCanchas['+cont+'][]" id="formCanchas_'+destinado+'" value="" size="10" maxlength="4" required="true"></td>\n\
        <td><select             name="formCanchas['+cont+'][]" id="formCanchas_'+materialpiso+'" required="true"></select>\n\
        <td><select             name="formCanchas['+cont+'][]" id="formCanchas_'+estadopiso+'" required="true"></select>\n\
        <td><select             name="formCanchas['+cont+'][]" id="formCanchas_'+techado+'"     required="true"></td>\n\
        <td><select             name="formCanchas['+cont+'][]" id="formCanchas_'+graderias+'" required="true"></td>\n\
        <td><input type="text"  name="formCanchas['+cont+'][]" id="formCanchas_'+capacidad+'" value="" size="5" maxlength="4" required="true"></td>\n\
        <td><input type="text"  name="formCanchas['+cont+'][]" id="formCanchas_'+area+'" value="" size="5" maxlength="4" required="true"></td>\n\
        </tr>');

        $("#formCanchas_" + materialpiso).empty();
        $("#formCanchas_" + materialpiso).append('<option value="">Seleccionar...</option>');
        $.each(arrayMaterialPiso, function (i, value) {
            $("#formCanchas_" + materialpiso).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#formCanchas_" + estadopiso).empty();
        $("#formCanchas_" + estadopiso).append('<option value="">Seleccionar...</option>');
        $.each(arrayEstadoPiso, function (i, value) {
            $("#formCanchas_" + estadopiso).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#formCanchas_" + techado).empty();
        $("#formCanchas_" + techado).append('<option value="">Seleccionar...</option>');
        $.each(arrayYesNoBoll, function (i, value) {
            $("#formCanchas_" + techado).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#formCanchas_" + graderias).empty();
        $("#formCanchas_" + graderias).append('<option value="">Seleccionar...</option>');
        $.each(arrayYesNoBoll, function (i, value) {
            $("#formCanchas_" + graderias).append('<option value="' + i + '">' + value + '</option>');
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
