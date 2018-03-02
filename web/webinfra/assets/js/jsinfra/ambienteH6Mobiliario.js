
function addAmbMobiliario(){
  
    
     countambmob++;
      var id                                  = 'id'+countambmob;
      var n21Cantidad                         = 'n21Cantidad'+countambmob;
      var n21EstadoTipo                       = 'n21EstadoTipo'+countambmob;
      var n21MobiliarioTipo                   = 'n21MobiliarioTipo'+countambmob;
      var infraestructuraH5Ambientepedagogico = 'infraestructuraH5Ambientepedagogico'+countambmob;
    

    $("#mobiliarioId").append('<tr id="'+countambmob+'">\n\
    <td><button type="button" onclick="eliminarFila('+countambmob+')"><i class="fa fa-trash text-danger" class="form-control"></i></button></td>\n\
    <td><select name="form[n21MobiliarioTipo][]" id="form_'+n21MobiliarioTipo+'" required="true" title="n21MobiliarioTipo Tenencia" class="form-control input-sm mb-15"></select></td>\n\
    <td><input type="text" name="form[n21Cantidad][]" id="form_'+n21Cantidad+'" value="" required="true" size="5" class="form-control"></td>\n\
    <td><select name="form[n21EstadoTipo][]" id="form_'+n21EstadoTipo+'" required="true" class="form-control input-sm mb-15" title="n21EstadoTipo" class="form-control"></select></td>\n\
    </tr>');

    $("#form_" + n21MobiliarioTipo).empty();
    $("#form_" + n21MobiliarioTipo).append('<option value="">Seleccionar...</option>');
    $.each(arrayDataMobiliarioTipo, function (i, value) {
        $("#form_" + n21MobiliarioTipo).append('<option value="' + i + '">' + value + '</option>');
    });

    $("#form_" + n21EstadoTipo).empty();
    $("#form_" + n21EstadoTipo).append('<option value="">Seleccionar...</option>');
    $.each(arrayDataStatusTipo, function (i, value) {
        $("#form_" + n21EstadoTipo).append('<option value="' + i + '">' + value + '</option>');
    });

        // $("#formAmb_" + ambiente).empty();
        // $("#formAmb_" + ambiente).append('<option value="">Seleccionar...</option>');
        // $.each(arrayAmbientesAdm, function (i, value) {
        //     $("#formAmb_" + ambiente).append('<option value="' + i + '">' + value + '</option>');
        // });


};


function eliminarFila(id){
    countambmob--;
    $("#" + id).remove();
}
