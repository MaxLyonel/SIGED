
function disabledButton(id,valor){
    if(valor){
        $('#'+id).empty();
        $('#'+id).append(valor);
    }
    $('#'+id).addClass('disabled');
}

function validarPassword() {
    if ($('#pass1').val() == $('#pass2').val()) {
        $('#textmensaje').val('Correcto');
        $('#textmensaje').css('color', '#009');
    } else {
        $('#textmensaje').val('No coincide los datos');
        $('#textmensaje').css('color', '#900');
    }
}

