


function agregarFilaColiseos(){
    cont++;
    contColiseo++;
    var coliseos = 'coliseos' + cont;
    var destinado = 'destinado' + cont;
    var materialpiso = 'materialpiso' + cont;
    var estadopiso = 'estadopiso' + cont;
    var techado = 'techado' + cont;

    var capacidad = 'capacidad' + cont;
    var area = 'area' + cont;


    $("#coliseosId").append('<tr id="'+cont+'">\n\
        <td><button type="button" onclick="eliminarFila('+cont+')"><i class="fa fa-remove text-danger"></i></button></td>\n\
        <td>'+contColiseo+'</td>\n\
        <td>Coliseos</td>\n\
        <td><select             name="formColiseos['+cont+'][]" id="formColiseos_'+materialpiso+'" required="true"></select>\n\
        <td><select             name="formColiseos['+cont+'][]" id="formColiseos_'+estadopiso+'" required="true"></select>\n\
        <td><select             name="formColiseos['+cont+'][]" id="formColiseos_'+techado+'"     required="true"></td>\n\
        <td><input type="text"  name="formColiseos['+cont+'][]" id="formColiseos_'+capacidad+'" value="" size="5" maxlength="4" required="true"></td>\n\
        <td><input type="text"  name="formColiseos['+cont+'][]" id="formColiseos_'+area+'" value="" size="5" maxlength="4" required="true"></td>\n\
        </tr>');

        $("#formColiseos_" + materialpiso).empty();
        $("#formColiseos_" + materialpiso).append('<option value="">Seleccionar...</option>');
        $.each(arrayMaterialPiso, function (i, value) {
            $("#formColiseos_" + materialpiso).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#formColiseos_" + estadopiso).empty();
        $("#formColiseos_" + estadopiso).append('<option value="">Seleccionar...</option>');
        $.each(arrayEstadoPiso, function (i, value) {
            $("#formColiseos_" + estadopiso).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#formColiseos_" + techado).empty();
        $("#formColiseos_" + techado).append('<option value="">Seleccionar...</option>');
        $.each(arrayYesNoBoll, function (i, value) {
            $("#formColiseos_" + techado).append('<option value="' + i + '">' + value + '</option>');
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
