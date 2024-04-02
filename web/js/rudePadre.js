/**
*   OPCIONES PADRE
*/
// funciones para ver si se debe actualizar o no los datos del apoderado

// Validomos el buscador
$("#pb_carnet").numeric("positiveInteger");
$("#pb_carnet").attr("maxlength",'10');

// aplicamos las mascaras para las fechas
$("#p_fechaNacimiento").inputmask({ "alias": "dd-mm-yyyy" ,'placeholder':'dd-mm-aaaa'});
$("#pb_fechaNacimiento").inputmask({ "alias": "dd-mm-yyyy" ,'placeholder':'dd-mm-aaaa'});
//$("#pb_complemento").inputmask({mask: "9a"});

// convertimos el complemento en mayusculas
$('#pb_complemento').on('keyup',function(){
    $(this).val($(this).val().toUpperCase());
});

$('#pb_fechaNacimiento').on('change', function(){

});

// $('#p_idioma').select2({width: "100%"});
// $('#p_ocupacion').select2({width: "100%"});

// $('#p_idioma').chosen({width: "100%"}); 
// $('#p_ocupacion').chosen({width: "100%"}); 

// funcion para validar si tiene o no padre
var p_validarTienePadre = function(){    
    var tiene = $('input:radio[name=p_tienePadre]:checked').val();
    p_limpiarBuscador();
    if(tiene == 1){
        $('#p_divPadre').css('display','block');
        $('#p_carnet').attr('required','required');
        //$('#p_expedido').attr('required','required');
        $('#p_expedido').removeAttr('required');
        $('#p_nombre').attr('required','required');
        $('#p_fechaNacimiento').attr('required','required');
        $('#p_genero').attr('required','required');
        $('#p_idioma').attr('required','required');
        $('#p_ocupacion').attr('required','required');
        $('#p_instruccion').attr('required','required');
        $('#p_parentesco').attr('required','required');

        // Validamos si el usuario tiene carnet
        var carnet = $('#p_carnet').val();
        if(carnet.indexOf('SC') != -1){
            $('#p_carnet').val('');
            $('#p_carnet').removeAttr('required');
            $('#p_expedido').attr('disabled','disabled');
        }

    }else{
        $('#p_divPadre').css('display','none');
        $('#p_carnet').removeAttr('required');
        $('#p_expedido').removeAttr('required');
        $('#p_nombre').removeAttr('required');
        $('#p_fechaNacimiento').removeAttr('required');
        $('#p_genero').removeAttr('required');
        $('#p_idioma').removeAttr('required');
        $('#p_ocupacion').removeAttr('required');
        $('#p_instruccion').removeAttr('required');
        $('#p_parentesco').removeAttr('required');

        p_borrarDatos();

        $('#p_genero').val('');
        $('#p_correo').val('');
        $('#p_telefono').val('');
        $('#p_celular').val('');
        $('#p_idioma').val('');
        $('#p_ocupacion').val(0);
        $('#p_instruccion').val(0);
        $('#p_ocupacion').val('');
        $('#p_instruccion').val('');
        $('#p_parentesco').val('');

        // saveFormPadre(false);
    }
}

p_validarTienePadre();

// funcion para cambiar el padre, mostrar la opcion de busqueda
var p_cambiar = function(){
    if(confirm('¿Esta seguro de que desea cambiar los datos del padre?\nPresione aceptar para confirmar.')){
        $('#p_top1').css('display','none');

        // p_borrarDatos();

        // $('#p_genero').val('');
        // $('#p_correo').val('');
        // $('#p_telefono').val('');
        // $('#p_idioma').val('');
        // $('#p_ocupacion').val('');
        // $('#p_instruccion').val('');
        // $('#p_parentesco').val('');

        $('#p_top2').css('display','block');
    }
}

// Ocultar el campo otra ocupacion si la ocupacion es diferente de otro
var ocultarp_ocupacionOtro = function(){
    if($('#p_ocupacion').val() == 10035){
        $('#p_filaOtroOcupacion').css('display','block');                                                                      
        $('#p_ocupacionOtro').css('display','block');                                                                      
        $('#p_ocupacionOtro').attr('required','required');                                                                      
    }else{
        $('#p_filaOtroOcupacion').css('display','none');
        $('#p_ocupacionOtro').css('display','none');
        $('#p_ocupacionOtro').removeAttr('required');
    }
}

ocultarp_ocupacionOtro();

$('#p_ocupacion').on('change',function(){
    $('#p_ocupacionOtro').val('');
    ocultarp_ocupacionOtro();
});

// CAMBIAR ATRIBUTOS DE REQUERIDO
$('#pb_sinCarnet').on('change', function(){
    if($(this).is(':checked')){
        $('#pb_carnet').val('');
        $('#pb_carnet').attr('disabled','disabled');
        $('#pb_complemento').val('');
        $('#pb_complemento').attr('disabled','disabled');

        $('#pb_paterno').focus();

        $('#p_carnet').removeAttr('required');
    }else{
        $('#pb_carnet').removeAttr('disabled');
        $('#pb_complemento').removeAttr('disabled');
        
        $('#pb_carnet').focus();

        $('#p_carnet').attr('required','required');
    }
});

//verificamos si es extranjero y obligamos a llenar los campos necesarios
var mostrarTipoIdentificacion=function ()
{
        $("#pb_es_extranjero").on( 'change', function()
        {
            if( $(this).is(':checked') )
            {
                $('#pb_nro_identidad').focus();
                $(".div-identificacion-extranjeros").show();
                $(".div-identificacion-nacionales").hide();

                $('#pb_carnet').removeAttr('required');
                $('#pb_complemento').removeAttr('required');

                $('#pb_carnet').attr('disabled','disabled');
                $('#pb_complemento').attr('disabled','disabled');

                $('#pb_nro_identidad').attr('required','required');

                $('#pb_carnet').val('');
                $('#pb_complemento').val('');
            }
            else
            {
                $('#pb_carnet').focus();
                $(".div-identificacion-extranjeros").hide();
                $(".div-identificacion-nacionales").show();

                $('#pb_carnet').removeAttr('disabled');
                $('#pb_complemento').removeAttr('disabled');

                $('#pb_carnet').attr('required','required');
                $('#pb_nro_identidad').removeAttr('required');

                $('#pb_nro_identidad').val('');
            }
        });
}

// Buscar padre
var p_buscarPadre = function(){
    var p_carnet = $('#pb_carnet').val();
    var p_complemento = $('#pb_complemento').val();
    var p_paterno = $('#pb_paterno').val();
    var p_materno = $('#pb_materno').val();
    var p_nombre = $('#pb_nombre').val();
    var p_fechaNacimiento = $('#pb_fechaNacimiento').val();
    var pb_es_extranjero_p_segip = $('#pb_es_extranjero_p_segip').is(':checked');
    // alert(pb_es_extranjero_p_segip)
    // Validamos si la fecha de nacimiento es correcta
    var p_df = p_fechaNacimiento.split('-');
    var p_anio = p_df[2];

    var p_fechaActual = new Date();
    var p_anioActual = p_fechaActual.getFullYear();

    if(p_anio < 1900 || (p_anioActual - p_anio) < 15 || (p_anioActual - p_anio) > 100){
        $('#p_mensaje').empty();
        p_cambiarFondoMensaje(3);
        $('#p_mensaje').append('La fecha de nacimiento no es válida, verfique e intentelo nuevamente.');
        return;
    }
    /////////
    //esta seccion de añadio para el caso de extranjeros
    var p_es_extranjero = $('#pb_es_extranjero').is(':checked')?1:0; //diplomatico
    var p_nro_identidad = $('#pb_nro_identidad').val();

    if($('#pb_sinCarnet').is(':checked'))
    {
        if(p_nombre != "" && p_fechaNacimiento != "")
        {
            $('#pb_carnet').val('');
            var data = {
                id: 'nuevo',
                carnet: '',
                complemento: p_complemento,
                paterno: p_paterno,
                materno: p_materno,
                nombre: p_nombre,
                extranjero_segip: pb_es_extranjero_p_segip,
                fecha_nacimiento: p_fechaNacimiento
            };

            $('#p_expedido').attr('disabled','disabled');
            $('#p_expedido').val('');

            p_cargarDatos(data);

            $('#p_mensaje').empty();
            p_cambiarFondoMensaje(2);
            $('#p_mensaje').append('Datos cargados');

        }
        else
        {
            // $('#p_idPersona').val('nuevo');
            $('#p_mensaje').empty();
            p_cambiarFondoMensaje(3);
            $('#p_mensaje').append('Complete los datos de nombre y fecha de nacimiento');
        }
    }
    else
    {
        if((p_carnet != "" && p_nombre != "" && p_es_extranjero==0) || (p_nro_identidad.length>0  && p_nombre.length>0 && p_es_extranjero==1))
        {
            $.ajax({
                type: 'get',
                url: Routing.generate('info_estudiante_rude_nuevo_buscar_persona',
                {'carnet':p_carnet, 'complemento':p_complemento, 'paterno':p_paterno, 'materno': p_materno, 'nombre': p_nombre, 'fechaNacimiento': p_fechaNacimiento,'esExtranjero':p_es_extranjero,'documentoNro':p_nro_identidad,'extranjero_segip':pb_es_extranjero_p_segip}),
                beforeSend: function(){
                    $('#p_mensaje').empty();
                    p_cambiarFondoMensaje(1);
                    $('#p_mensaje').append('Buscando...');
                },
                success: function(data){
                    $('#p_expedido').removeAttr('disabled');
                    // Ponemos el id de la padre en nuevo
                    $('#p_idPersona').val('nuevo');

                    if(data.status == 200){
                        console.log('Encontrado...');
                        
                        // Cargamos los datos devueltos por el servicio
                        p_cargarDatos(data.persona);

                        // Ubicamos el cursor en el campo telefono
                        $('#p_genero').focus();

                        // Creamos el mensaje de la busqueda
                        $('#p_mensaje').empty();
                        p_cambiarFondoMensaje(2);
                        $('#p_mensaje').append('La persona fue encontrada');

                    }else{
                        console.log('No encontrado');
                        p_borrarDatos();
                        // Ponemos el id de la padre en nuevo
                        $('#p_idPersona').val('nuevo');

                        // Creasmos el mensaje de la busqueda
                        $('#p_mensaje').empty();
                        p_cambiarFondoMensaje(3);
                        $('#p_mensaje').append('La persona no fue encontrada');
                    }

                    // Verificar el parentesco con el estudiante
                    // setTimeout("p_validarParentesco()",3000);

                },
                error: function(data){

                    // Ponemos el id de la padre en nuevo
                    $('#p_idPersona').val('nuevo');

                    p_borrarDatos();
                    
                    $('#p_mensaje').empty();
                    p_cambiarFondoMensaje(4);
                    $('#p_mensaje').append('Los datos introducidos son incorrectos o no hay conexion con el servicio.');
                }
            });
        }
        else
        {
            $('#p_mensaje').empty();
            p_cambiarFondoMensaje(3);
            if($('#pb_es_extranjero').is(':checked'))
                $('#p_mensaje').append('Complete los datos de nro de identidad y fecha de nacimiento para realizar la busqueda');
            else
                $('#p_mensaje').append('Complete los datos de carnet y fecha de nacimiento para realizar la busqueda');
        }
    }
}

// cargar los campos con los datos devueltos por el servicio
var p_cargarDatos = function(data){
    
    $('#p_idPersona').val(data.id); //id
    $('#p_carnet').val(data.carnet);
    $('#p_complemento').val(data.complemento);
    $('#p_paterno').val(data.paterno);
    $('#p_materno').val(data.materno);
    $('#p_nombre').val(data.nombre);
    $('#p_fechaNacimiento').val(data.fecha_nacimiento);
    $('#p_cedulaTipoId').val(data.cedula_tipo_id);
}

// borrar los datos si el servicio no devuelve datos
function p_borrarDatos(){
    $('#p_carnet').val('');
    $('#p_complemento').val('');
    $('#p_paterno').val('');
    $('#p_materno').val('');
    $('#p_nombre').val('');
    $('#p_fechaNacimiento').val('');
    $('#p_genero').val('');
    $('#p_correo').val('');
    $('#p_telefono').val('');
    $('#p_celular').val('');
    $('#p_nro_identidad').val('');
}

var p_cambiarFondoMensaje = function(opcion){
    // Default
    if(opcion == 0){
        $('#p_mensaje').css('background','#E1EBFA');
        $('#p_mensaje').css('color','#6496BA');
    }
    // Info
    if(opcion == 1){
        $('#p_mensaje').css('background','#E9F9FD');
        $('#p_mensaje').css('color','#1cadca');
    }
    // Success
    if(opcion == 2){
        $('#p_mensaje').css('background','#d7e9c3');
        $('#p_mensaje').css('color','#587f2e');
    }
    // Warning
    if(opcion == 3){
        $('#p_mensaje').css('background','#fdf0d4');
        $('#p_mensaje').css('color','#c88a0a');
    }
    // Danger
    if(opcion == 4){
        $('#p_mensaje').css('background','#f9cfc8');
        $('#p_mensaje').css('color','#ae2a14');
    }
}

$('#tienePadre0').on('click', function(){
    alert('Tenga en cuenta que al seleccionar la opcion "No", se eliminaran los datos del padre al momento de guardar la información.');
    
});

function p_limpiarBuscador(){
    $('#pb_carnet').val('');
    $('#pb_complemento').val('');
    $('#pb_paterno').val('');
    $('#pb_materno').val('');
    $('#pb_nombre').val('');
    $('#pb_fechaNacimiento').val('');
}

function saveFormPadre(){
    var data = $('#formPadre').serialize();
    var subsistema = $('#subsistema').val();
    // data['actualizar'] = recargar;
    $.ajax({
        url: Routing.generate('info_estudiante_rude_nuevo_save_formApoderado'),
        type: 'post',
        data: data,
        beforeSend: function(){
            console.log('enviando')
            $('#cortina').css('display','block');
            p_limpiarBuscador();
        },
        success: function(data){
            $('#cortina').css('display','none');

            if (data.status == 200) {
                $('#p_id').val(data.id);
                $('#p_idDatos').val(data.idDatos);
                $('#p_idPersona').val(data.idPersona);

                $('#tabMadre').click();
                // Pasar a la siguiente pagina
                // if(recargar){
                //     $('#paso5').parent('li').removeClass('disabled');
                //     $('#paso5').attr('data-toggle','tab');
                //     $('#paso5').click();
                // }
                if(subsistema=='especial'){
                    $('#cortina').css('display','none');
                    $('#paso7').parent('li').removeClass('disabled');
                    $('#paso7').attr('data-toggle','tab');
                    $('#paso7').click();
                    $('#tabPadre').click();
                }
            }else{
                alert(data.msg);
            }
        },
        error: function(){
            $('#cortina').css('display','none');
        }
    });
}