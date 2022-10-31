/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//datepicker calendario
$('#sandbox-container input').datepicker({
    autoclose: true,
    todayHighlight: true,
    format: 'dd-mm-yyyy',
    language: "es"
});

// Data table
$('.dataTable').DataTable({
    responsive: true,
     "bSort": false,
    "oLanguage": {
        "sProcessing": "Procesando...",
        "sLengthMenu": 'Mostrar <select>' +
                '<option value="10">10</option>' +
                '<option value="20">20</option>' +
                '<option value="30">30</option>' +
                '<option value="40">40</option>' +
                '<option value="50">50</option>' +
                '<option value="-1">Todos</option>' +
                '</select> registros',
        "sZeroRecords": "No se encontraron resultados",
        "sEmptyTable": "Ning&uacute;n dato disponible en esta tabla",
        "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
        "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
        "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
        "sInfoPostFix": "",
        "sSearch": "Buscar:",
        "sUrl": "",
        "sInfoThousands": ",",
        "sLoadingRecords": "Cargando...",
        "oPaginate": {
            "sFirst": "Primero",
            "sLast": "Ultimo",
            "sNext": "Siguiente",
            "sPrevious": "Anterior"
        },
        "oAria": {
            "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
        }

    }
});
/*  Funcion para validar los inputs
 *   que solo acepte valores numericos
 *   aplicacndo la clase soloNumero
 */
$(function () {
    $(".soloNumero").keydown(function (event) {
        if (event.shiftKey) {
            event.preventDefault();
        }
        if (event.keyCode == 46 || event.keyCode == 8) {
        } else {
            if (event.keyCode < 95) {
                if (event.keyCode < 48 || event.keyCode > 57) {
                    event.preventDefault();
                }
            } else {
                if (event.keyCode < 96 || event.keyCode > 105) {
                    event.preventDefault();
                }
            }
        }
    });

    $(".jnumbers").keypress(function (key) {
        var key = key || window.event;
        var k = key.charCode || key.keyCode;
        if ((k < 48 || k > 57) //NUmeros
                && (k != 13) //ENTER
                && (k != 8) //retroceso
                && (k != 9) //tab
                )
            return false;
    });

    $(".jletters").keypress(function (key) {
        var key = key || window.event;
        var k = key.charCode || key.keyCode;
        //window.console.log(ek)
        if ((k < 97 || k > 122)//letras minusculas
                && (k < 65 || k > 90) //letras mayusculas
                && (k != 13) //ENTER
                && (k != 8) //retroceso
                && (k != 9) //tab
                && (k != 241) //ñ
                && (k != 209) //Ñ
                && (k != 32) //espacio
                && (k != 225) //á
                && (k != 233) //é
                && (k != 237) //í
                && (k != 243) //ó
                && (k != 250) //ú
                && (k != 193) //Á
                && (k != 201) //É
                && (k != 205) //Í
                && (k != 211) //Ó
                && (k != 218) //Ú
                //&& (ek != 37) //flecha izquierda y %
                //&& (ek != 38) //flecha arriba y & 
                //&& (ek != 39) //flecha derecha y '
                //&& (ek != 40) //flecha abajo y (
                //&& (String.fromCharCode(ek) == %)
                // si tuviera que elgir -  eva luna montaner
                )
            return false;
    });

    $(".jlettersupper").keypress(function (key) {
        var key = key || window.event;
        var k = key.charCode || key.keyCode;
        if ((k < 65 || k > 90)//letras mayusculas
                && (k != 13) //ENTER
                && (k != 8) //retroceso
                && (k != 9) //tab
                && (k != 209) //Ñ
                && (k != 32) //espacio
                && (k != 193) //Á
                && (k != 201) //É
                && (k != 205) //Í
                && (k != 211) //Ó
                && (k != 218) //Ú

                )
            return false;
    });

    $(".jnumbersletters").keypress(function (key) {
        var key = key || window.event;
        var k = key.charCode || key.keyCode;
        if ((k < 97 || k > 122)//letras minusculas
                && (k < 65 || k > 90) //letras mayusculas
                && (k < 48 || k > 57) //NUmeros
                && (k != 13) //ENTER
                && (k != 8) //retroceso
                && (k != 9) //tab
                && (k != 241) //ñ
                && (k != 209) //Ñ
                && (k != 32) //espacio
                && (k != 225) //á
                && (k != 233) //é
                && (k != 237) //í
                && (k != 243) //ó
                && (k != 250) //ú
                && (k != 193) //Á
                && (k != 201) //É
                && (k != 205) //Í
                && (k != 211) //Ó
                && (k != 218) //Ú
                && (k != 44) //coma ,
                && (k != 46) //punto .
                )
            return false;
    });

    $(".jemail").keypress(function (key) {
        var key = key || window.event;
        var k = key.charCode || key.keyCode;
        if ((k < 97 || k > 122)//letras minusculas
                && (k < 65 || k > 90) //letras mayusculas
                && (k < 48 || k > 57) //NUmeros
                && (k != 13) //ENTER
                && (k != 8) //retroceso
                && (k != 9) //tab
                && (k != 45) //-
                && (k != 46) //.
                && (k != 95) //_
                && (k != 64) //@
                )
            return false;
    });
    /* Celular */
    $(".jcell").keypress(function (key) {
        var key = key || window.event;
        var k = key.charCode || key.keyCode;
        if ((k < 48 || k > 57) //NUmeros
                && (k != 13) //ENTER
                && (k != 8) //retroceso
                && (k != 9) //tab
                )
            return false;
    });
    $(".jcell").attr({
        pattern: '[0-9]{8}',
        maxlength: '8'
    });


    /*  Asignacion de pattern's a los inputs mediante clases */
    $(".jemail").attr('pattern', '^[_a-zA-Z0-9-]+(.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(.[a-zA-Z0-9-]+)*[.]([a-zA-Z]{2,3})$'); // correo electronico

});             
// tooltip
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

$(function () {
    $('[data-toggle="popover"]').popover();
});