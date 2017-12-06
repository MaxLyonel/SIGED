var validarNumero = function (oEvento) {
    var evento = oEvento || window.event;
    var codigo = oEvento.charCode || oEvento.keyCode;
    if (((codigo >= 48) && (codigo <= 57)) || (codigo == 13) || (codigo == 8) || ((codigo >= 96) && (codigo <= 105)) || ((codigo >= 37) && (codigo <= 40)) || (codigo == 46) || (codigo == 110) || (codigo == 190))
        return true;
    else
        return false;
}
var validarNota = function (el)
{
    if (parseInt(el.value) > 100) {
        alert('Debe corregir la nota,\nNo puede ser mayor a 100');
        el.value = 0;
        el.focus();
        return false;
    }
}

var stopRKey = function (evt) {
    var evt = (evt) ? evt : ((event) ? event : null);
    var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
    if ((evt.keyCode == 13) && (node.type == "text")) {
        return false;
    }
}
document.onkeypress = stopRKey;