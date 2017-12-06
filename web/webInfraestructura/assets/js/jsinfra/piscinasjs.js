


function agregarFilaPiscinas(){
    cont++;
    contPiscina++;
    var piscinas = 'piscinas' + cont;
    var destinado = 'destinado' + cont;
    var materialpiso = 'materialpiso' + cont;
    var estadopiso = 'estadopiso' + cont;
    var techado = 'techado' + cont;

    var capacidad = 'capacidad' + cont;
    var area = 'area' + cont;


    $("#piscinasId").append('<tr id="'+cont+'">\n\
        <td><button type="button" onclick="eliminarFila('+cont+')"><i class="fa fa-remove text-danger"></i></button></td>\n\
        <td>'+contPiscina+'</td>\n\
        <td>Piscinas</td>\n\
        <td><select             name="formPiscinas['+cont+'][]" id="formPiscinas_'+materialpiso+'" required="true"></select>\n\
        <td><select             name="formPiscinas['+cont+'][]" id="formPiscinas_'+estadopiso+'" required="true"></select>\n\
        <td><select             name="formPiscinas['+cont+'][]" id="formPiscinas_'+techado+'"     required="true"></td>\n\
        <td><input type="text"  name="formPiscinas['+cont+'][]" id="formPiscinas_'+capacidad+'" value="" size="5" maxlength="4" required="true"></td>\n\
        <td><input type="text"  name="formPiscinas['+cont+'][]" id="formPiscinas_'+area+'" value="" size="5" maxlength="4" required="true"></td>\n\
        </tr>');

        $("#formPiscinas_" + materialpiso).empty();
        $("#formPiscinas_" + materialpiso).append('<option value="">Seleccionar...</option>');
        $.each(arrayMaterialPiso, function (i, value) {
            $("#formPiscinas_" + materialpiso).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#formPiscinas_" + estadopiso).empty();
        $("#formPiscinas_" + estadopiso).append('<option value="">Seleccionar...</option>');
        $.each(arrayEstadoPiso, function (i, value) {
            $("#formPiscinas_" + estadopiso).append('<option value="' + i + '">' + value + '</option>');
        });

        $("#formPiscinas_" + techado).empty();
        $("#formPiscinas_" + techado).append('<option value="">Seleccionar...</option>');
        $.each(arrayYesNoBoll, function (i, value) {
            $("#formPiscinas_" + techado).append('<option value="' + i + '">' + value + '</option>');
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
