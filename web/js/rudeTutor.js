/**
*   OPCIONES TUTOR
*/
// funciones para ver si se debe actualizar o no los datos del apoderado

// Validomos el buscador
$("#tb_carnet").numeric("positiveInteger");
$("#tb_carnet").attr("maxlength",'10');

// aplicamos las mascaras para las fechas
$("#t_fechaNacimiento").inputmask({ "alias": "dd-mm-yyyy" , 'placeholder':'dd-mm-aaaa'});
$("#tb_fechaNacimiento").inputmask({ "alias": "dd-mm-yyyy" , 'placeholder':'dd-mm-aaaa'});
//$("#tb_complemento").inputmask({mask: "9a"});

// convertimos el complemento en mayusculas
$('#tb_complemento').on('keyup',function(){
    $(this).val($(this).val().toUpperCase());
});

// $('#t_idioma').select2({width: "100%"});
// $('#t_ocupacion').select2({width: "100%"});
// $('#t_idioma').chosen({width: "100%"}); 
// $('#t_ocupacion').chosen({width: "100%"}); 

// funcion para validar si tiene o no tutor
var t_validarTieneTutor = function(){
    var tiene = $('input:radio[name=t_tieneTutor]:checked').val();
    t_limpiarBuscador();
    if(tiene == 1){

        $('#t_divTutor').css('display','block');
        $('#t_carnet').attr('required','required');
        //$('#t_expedido').attr('required','required');
        $('#t_nombre').attr('required','required');
        $('#t_fechaNacimiento').attr('required','required');
        $('#t_genero').attr('required','required');
        $('#t_idioma').attr('required','required');
        $('#t_ocupacion').attr('required','required');
        $('#t_instruccion').attr('required','required');
        $('#t_parentesco').attr('required','required');

        // Validamos si el usuario tiene carnet
        var carnet = $('#t_carnet').val();
        if(carnet.indexOf('SC') != -1){
            $('#t_carnet').val('');
            $('#t_carnet').removeAttr('required');
            $('#t_expedido').attr('disabled','disabled');
        }

    }else{
        $('#t_divTutor').css('display','none');
        $('#t_carnet').removeAttr('required');
        $('#t_expedido').removeAttr('required');
        $('#t_nombre').removeAttr('required');
        $('#t_fechaNacimiento').removeAttr('required');
        $('#t_genero').removeAttr('required');
        $('#t_idioma').removeAttr('required');
        $('#t_ocupacion').removeAttr('required');
        $('#t_instruccion').removeAttr('required');
        $('#t_parentesco').removeAttr('required');

        t_borrarDatos();

        $('#t_genero').val('');
        $('#t_correo').val('');
        $('#t_telefono').val('');
        $('#t_celular').val('');
        $('#t_idioma').val('');
        $('#t_ocupacion').val(0);
        $('#t_instruccion').val(0);
        $('#t_ocupacion').val('');
        $('#t_instruccion').val('');
        $('#t_parentesco').val('');

        // saveFormTutor(false);
    }
}

t_validarTieneTutor();

// funcion para cambiar el tutor, mostrar la opcion de busqueda
var t_cambiar = function(){
    if(confirm('¿Esta seguro de que desea cambiar los datos del tutor?\nPresione aceptar para confirmar.')){
        $('#t_top1').css('display','none');

        // t_borrarDatos();

        // $('#t_genero').val('');
        // $('#t_correo').val('');
        // $('#t_telefono').val('');
        // $('#t_idioma').val('');
        // $('#t_ocupacion').val('');
        // $('#t_instruccion').val('');
        // $('#t_parentesco').val('');

        $('#t_top2').css('display','block');
    }
}

// Ocultar el campo otra ocupacion si la ocupacion es diferente de otro
var ocultart_ocupacionOtro = function(){
    if($('#t_ocupacion').val() == 10035){
        $('#t_filaOtroOcupacion').css('display','block');                                                                      
        $('#t_ocupacionOtro').css('display','block');                                                                      
        $('#t_ocupacionOtro').attr('required','required');                                                                      
    }else{
        $('#t_filaOtroOcupacion').css('display','none');
        $('#t_ocupacionOtro').css('display','none');
        $('#t_ocupacionOtro').removeAttr('required');
    }
}

ocultart_ocupacionOtro();

$('#t_ocupacion').on('change',function(){
    $('#t_ocupacionOtro').val('');
    ocultart_ocupacionOtro();
});

// CAMBIAR ATRIBUTOS DE REQUERIDO
$('#tb_sinCarnet').on('change', function(){
    if($(this).is(':checked')){
        $('#tb_carnet').val('');
        $('#tb_carnet').attr('disabled','disabled');
        $('#tb_complemento').val('');
        $('#tb_complemento').attr('disabled','disabled');

        $('#tb_paterno').focus();

        $('#t_carnet').removeAttr('required');
    }else{
        $('#tb_carnet').removeAttr('disabled');
        $('#tb_complemento').removeAttr('disabled');
        
        $('#tb_carnet').focus();

        $('#t_carnet').attr('required','required');
    }
});

//verificamos si es extranjero y obligamos a llenar los campos necesarios
var mostrarTipoIdentificacionTutor=function ()
{
        $("#tb_es_extranjero").on( 'change', function()
        {
            if( $(this).is(':checked') )
            {
                $('#tb_nro_identidad').focus();
                $(".tdiv-identificacion-extranjeros").show();
                $(".tdiv-identificacion-nacionales").hide();

                $('#tb_carnet').removeAttr('required');
                $('#tb_complemento').removeAttr('required');

                $('#tb_carnet').attr('disabled','disabled');
                $('#tb_complemento').attr('disabled','disabled');

                $('#tb_nro_identidad').attr('required','required');

                $('#tb_carnet').val('');
                $('#tb_complemento').val('');
            }
            else
            {
                $('#tb_carnet').focus();
                $(".tdiv-identificacion-extranjeros").hide();
                $(".tdiv-identificacion-nacionales").show();

                $('#tb_carnet').removeAttr('disabled');
                $('#tb_complemento').removeAttr('disabled');

                $('#tb_carnet').attr('required','required');
                $('#tb_nro_identidad').removeAttr('required');

                $('#tb_nro_identidad').val('');
            }
        });
}

// Buscar tutor
var t_buscarTutor = function(){
    var t_carnet = $('#tb_carnet').val();
    var t_complemento = $('#tb_complemento').val();
    var t_paterno = $('#tb_paterno').val();
    var t_materno = $('#tb_materno').val();
    var t_nombre = $('#tb_nombre').val();
    var t_fechaNacimiento = $('#tb_fechaNacimiento').val();
    var pb_es_extranjero_t_segip = $('#pb_es_extranjero_t_segip').is(':checked');

    // Validamos si la fecha de nacimiento es correcta
    var t_df = t_fechaNacimiento.split('-');
    var t_anio = t_df[2];

    var t_fechaActual = new Date();
    var t_anioActual = t_fechaActual.getFullYear();

    if(t_anio < 1900 || (t_anioActual - t_anio) < 15 || (t_anioActual - t_anio) > 100){
        $('#t_mensaje').empty();
        t_cambiarFondoMensaje(3);
        $('#t_mensaje').append('La fecha de nacimiento no es válida, verfique e intentelo nuevamente.');
        return;
    }
    /////////
         //esta seccion de añadio para el caso de extranjeros
         var t_es_extranjero = $('#tb_es_extranjero').is(':checked')?1:0;
         var t_nro_identidad = $('#tb_nro_identidad').val();

    if($('#tb_sinCarnet').is(':checked')){

        if(t_nombre != "" && t_fechaNacimiento != ""){
            $('#tb_carnet').val('');
            var data = {
                id: 'nuevo',
                carnet: '',
                complemento: t_complemento,
                paterno: t_paterno,
                materno: t_materno,
                nombre: t_nombre,
                extranjero_segip: pb_es_extranjero_t_segip,
                fecha_nacimiento: t_fechaNacimiento
            };

            $('#t_expedido').attr('disabled','disabled');
            $('#t_expedido').val('');

            t_cargarDatos(data);

            $('#m_mensaje').empty();
            m_cambiarFondoMensaje(2);
            $('#m_mensaje').append('Datos cargados');

        }else{
            // $('#t_idPersona').val('nuevo');
            $('#t_mensaje').empty();
            t_cambiarFondoMensaje(3);
            $('#t_mensaje').append('Complete los datos de nombre y fecha de nacimiento');
        }
    }else{

        if((t_carnet != "" && t_nombre != "" && t_es_extranjero==0) || (t_nro_identidad.length>0  && t_nombre.length>0 && t_es_extranjero==1))
        {
            
            $.ajax({
                type: 'get',
                url: Routing.generate('info_estudiante_rude_nuevo_buscar_persona',{'carnet':t_carnet, 'complemento':t_complemento, 'paterno':t_paterno, 'materno': t_materno, 'nombre': t_nombre, 'fechaNacimiento': t_fechaNacimiento,'esExtranjero':t_es_extranjero,'documentoNro':t_nro_identidad,'extranjero_segip': pb_es_extranjero_t_segip}),
                beforeSend: function(){
                    $('#t_mensaje').empty();
                    t_cambiarFondoMensaje(1);
                    $('#t_mensaje').append('Buscando...');
                },
                success: function(data){
                    $('#t_expedido').removeAttr('disabled');
                    // Ponemos el id de la tutor en nuevo
                    $('#t_idPersona').val('nuevo');

                    if(data.status == 200){
                        console.log('Encontrado');
                        // Cargamos los datos devueltos por el servicio
                        t_cargarDatos(data.persona);

                        // Ubicamos el cursor en el campo telefono
                        $('#t_genero').focus();

                        // Creamos el mensaje de la busqueda
                        $('#t_mensaje').empty();
                        t_cambiarFondoMensaje(2);
                        $('#t_mensaje').append('La persona fue encontrada');

                    }else{
                        console.log('No encontrado');
                        t_borrarDatos();
                        // Ponemos el id de la tutor en nuevo
                        $('#t_idPersona').val('nuevo');

                        // Creasmos el mensaje de la busqueda
                        $('#t_mensaje').empty();
                        t_cambiarFondoMensaje(3);
                        $('#t_mensaje').append('La persona no fue encontrada');
                    }

                    // Verificar el parentesco con el estudiante
                    // setTimeout("t_validarParentesco()",3000);

                },
                error: function(data){

                    // Ponemos el id de la tutor en nuevo
                    $('#t_idPersona').val('nuevo');

                    t_borrarDatos();
                    
                    $('#t_mensaje').empty();
                    t_cambiarFondoMensaje(4);
                    $('#t_mensaje').append('Los datos introducidos son incorrectos o no hay conexion con el servicio.');
                }
            });
        }else{
            $('#t_mensaje').empty();
            t_cambiarFondoMensaje(3);
            $('#t_mensaje').append('Complete los datos de carnet y fecha de nacimiento para realizar la busqueda');
        }
    }

    
}

// cargar los campos con los datos devueltos por el servicio
var t_cargarDatos = function(data){
    $('#t_idPersona').val(data.id);
    $('#t_carnet').val(data.carnet);
    $('#t_complemento').val(data.complemento);
    $('#t_paterno').val(data.paterno);
    $('#t_materno').val(data.materno);
    $('#t_nombre').val(data.nombre);
    $('#t_fechaNacimiento').val(data.fecha_nacimiento);
    $('#t_cedulaTipoId').val(data.cedula_tipo_id);
}

// borrar los datos si el servicio no devuelve datos
function t_borrarDatos(){
    $('#t_carnet').val('');
    $('#t_complemento').val('');
    $('#t_paterno').val('');
    $('#t_materno').val('');
    $('#t_nombre').val('');
    $('#t_fechaNacimiento').val('');
    $('#t_genero').val('');
    $('#t_correo').val('');
    $('#t_telefono').val('');
    $('#t_celular').val('');
    $('#t_nro_identidad').val('');
}

var t_cambiarFondoMensaje = function(opcion){
    // Default
    if(opcion == 0){
        $('#t_mensaje').css('background','#E1EBFA');
        $('#t_mensaje').css('color','#6496BA');
    }
    // Info
    if(opcion == 1){
        $('#t_mensaje').css('background','#E9F9FD');
        $('#t_mensaje').css('color','#1cadca');
    }
    // Success
    if(opcion == 2){
        $('#t_mensaje').css('background','#d7e9c3');
        $('#t_mensaje').css('color','#587f2e');
    }
    // Warning
    if(opcion == 3){
        $('#t_mensaje').css('background','#fdf0d4');
        $('#t_mensaje').css('color','#c88a0a');
    }
    // Danger
    if(opcion == 4){
        $('#t_mensaje').css('background','#f9cfc8');
        $('#t_mensaje').css('color','#ae2a14');
    }
}

$('#tieneTutor0').on('click', function(){
    alert('Tenga en cuenta que al seleccionar la opcion "No", se eliminaran los datos del tutor al momento de guardar la información.');
    
});

function t_limpiarBuscador(){
    $('#tb_carnet').val('');
    $('#tb_complemento').val('');
    $('#tb_paterno').val('');
    $('#tb_materno').val('');
    $('#tb_nombre').val('');
    $('#tb_fechaNacimiento').val('');
}

function saveFormTutor(){
    var data = $('#formTutor').serialize();
    var subsistema = $('#subsistema').val();
    // data['actualizar'] = recargar;
    $.ajax({
        url: Routing.generate('info_estudiante_rude_nuevo_save_formApoderado'),
        type: 'post',
        data: data,
        beforeSend: function(){
            console.log('enviando')
            $('#cortina').css('display','block');
            t_limpiarBuscador();
        },
        success: function(data){
            $('#cortina').css('display','none');

            if(data.status == 200){
                $('#t_id').val(data.id);
                $('#t_idDatos').val(data.idDatos);
                $('#t_idPersona').val(data.idPersona);
                
                if(subsistema=='especial'){
                    $('#cortina').css('display','none');
                    $('#paso7').parent('li').removeClass('disabled');
                    $('#paso7').attr('data-toggle','tab');
                    $('#paso7').click();
                    $('#tabPadre').click();
                } else{
                    $('#tabTutorExt').click();
                //     $('#paso5').parent('li').removeClass('disabled');
                //     $('#paso5').attr('data-toggle','tab');
                //     $('#paso5').click();
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