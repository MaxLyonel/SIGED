
function addAmbPedagogico(){
  // count ++;
  // <select id="sie_appwebbundle_infraestructurah5ambientepedagogicomobiliario_n52MobiliarioTipo" name="sie_appwebbundle_infraestructurah5ambientepedagogicomobiliario[n52MobiliarioTipo]" class="form-countrol"><option value=""></option><option value="1">BANCOS BIPERSONALES</option><option value="2">BANCOS UNIPERSONALES</option><option value="3">CABALLETE</option><option value="4">CASILLEROS</option><option value="5">ESCRITORIO</option><option value="6">ESTANTE</option><option value="7" selected="selected">GABETEROS</option><option value="8">MESA DE TRABAJO GRUPAL</option><option value="9">MESAS INDIVIDUALES</option><option value="10">MESAS PARA BANCO DE TRABAJO</option><option value="11">MESAS PARA NIÑOS (AS)</option><option value="12">MESONES</option><option value="13">MODULARES</option><option value="14">MUEBLES DE TV/VIDEO</option><option value="15">MUEBLES PARA COMPUTADORAS </option><option value="16">PIZARRA</option><option value="17">SILLAS GIRATORIAS</option><option value="18">SILLAS INDIVIDUALES</option><option value="19">SILLAS PARA NIÑOS (AS) </option><option value="20">TABLEROS DE DIBUJOS</option><option value="21">TABURETES</option><option value="22">VITRINA</option><option value="23">MOB. ANTROPOMETRICAMENTE ADECUADO A PERSONAS CON DISCAPACIDAD</option></select>
  // <input id="sie_appwebbundle_infraestructurah5ambientepedagogicomobiliario_n52Cantidad" name="sie_appwebbundle_infraestructurah5ambientepedagogicomobiliario[n52Cantidad]" class="form-countrol" value="10" type="number">
  // <select id="sie_appwebbundle_infraestructurah5ambientepedagogicomobiliario_n52EstadoTipo" name="sie_appwebbundle_infraestructurah5ambientepedagogicomobiliario[n52EstadoTipo]" class="form-countrol"><option value=""></option><option value="1" selected="selected">NUEVO</option><option value="2">CON DESGASTE</option><option value="3">EN DESUSO</option></select>
    // var mobiliario = 'mobiliario' + count;
    // var cantidad = 'cantidad' + count;
    // var estado = 'estado' + count;
    
     count++;
      var id                                  = 'id'+count;
      var n52Cantidad                         = 'n52Cantidad'+count;
      var n52EstadoTipo                       = 'n52EstadoTipo'+count;
      var n52MobiliarioTipo                   = 'n52MobiliarioTipo'+count;
      var infraestructuraH5Ambientepedagogico = 'infraestructuraH5Ambientepedagogico'+count;
    

    $("#mobiliarioId").append('<tr id="'+count+'">\n\
    <td><button type="button" onclick="eliminarFila('+count+')"><i class="fa fa-trash text-danger" class="form-control"></i></button></td>\n\
    <td><select name="form[n52MobiliarioTipo][]" id="form_'+n52MobiliarioTipo+'" required="true" class="jupper" title="n52MobiliarioTipo Tenencia" class="form-control"></select></td>\n\
    <td><input type="text" name="form[n52Cantidad][]" id="form_'+n52Cantidad+'" value="" required="true" size="5" class="form-control"></td>\n\
    <td><select name="form[n52EstadoTipo][]" id="form_'+n52EstadoTipo+'" required="true" class="jupper" title="n52EstadoTipo" class="form-control"></select></td>\n\
    </tr>');

    $("#form_" + n52MobiliarioTipo).empty();
    $("#form_" + n52MobiliarioTipo).append('<option value="">Seleccionar...</option>');
    $.each(arrayDataMobiliarioTipo, function (i, value) {
        $("#form_" + n52MobiliarioTipo).append('<option value="' + i + '">' + value + '</option>');
    });

    $("#form_" + n52EstadoTipo).empty();
    $("#form_" + n52EstadoTipo).append('<option value="">Seleccionar...</option>');
    $.each(arrayDataStatusTipo, function (i, value) {
        $("#form_" + n52EstadoTipo).append('<option value="' + i + '">' + value + '</option>');
    });

        // $("#formAmb_" + ambiente).empty();
        // $("#formAmb_" + ambiente).append('<option value="">Seleccionar...</option>');
        // $.each(arrayAmbientesAdm, function (i, value) {
        //     $("#formAmb_" + ambiente).append('<option value="' + i + '">' + value + '</option>');
        // });


};


function eliminarFila(id){
    count--;
    $("#" + id).remove();
}
