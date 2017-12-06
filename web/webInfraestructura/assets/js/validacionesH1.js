function enaDisEletrica(){
    if($('#form_n11EsEnergiaelectrica_0').checked == true){
        $('#f12').attr('disabled','disabled');
    else{
        $('#f12').removeAttr('disabled');
    }
    /*
    $('#form_n12FuenteElectricaTipo').selectmenu().selectmenu('disable').selectmenu('refresh', true);
    $("#form_n12FuenteElectricaTipo").attr('readonly','readonly');
    $("#form_n12FuenteElectricaTipo").attr('onfocus','bloquear(this)');
    $("#form_n12FuenteElectricaOtro").attr('readonly','readonly');
    $("#form_n13DisponibilidadTipo").attr('readonly','readonly'); 
    $("#form_n14NumeroAmbientesPedagogicos").attr('readonly','readonly'); 
    $("#form_n14NumeroAmbientesNoPedagogicos").attr('readonly','readonly');    
    $("#form_n14NumeroBanios").attr('readonly','readonly');
    $("#form_n15NumeroMedidores").attr('readonly','readonly');     
    $("#form_n16NumeroMedidoresFuncionan").attr('readonly','readonly');       
    $("#form_n16NumeroMedidoresNoFuncionan").attr('readonly','readonly');         */
}

function bloquear(elemento){
    elemento.blur();
}