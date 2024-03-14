/**
*   OPCIONES TUTOR
*/
// funciones para ver si se debe actualizar o no los datos del apoderado

// Validomos el buscador
$("#teb_carnet").numeric("positiveInteger");
$("#teb_carnet").attr("maxlength",'10');

// aplicamos las mascaras para las fechas
$("#te_fechaNacimiento").inputmask({ "alias": "dd-mm-yyyy" , 'placeholder':'dd-mm-aaaa'});
$("#teb_fechaNacimiento").inputmask({ "alias": "dd-mm-yyyy" , 'placeholder':'dd-mm-aaaa'});
//$("#teb_complemento").inputmask({mask: "9a"});

// convertimos el complemento en mayusculas
$('#teb_complemento').on('keyup',function(){
    $(this).val($(this).val().toUpperCase());
});

// $('#tb_idioma').select2({width: "100%"});
// $('#te_ocupacion').select2({width: "100%"});
$('#te_idioma').chosen({width: "100%"}); 
$('#te_ocupacion').chosen({width: "100%"}); 

// funcion para validar si tiene o no tutor
var te_validarTieneTutor = function(){
    var tiene = $('input:radio[name=te_tieneTutor]:checked').val();
    te_limpiarBuscador();
    console.log(tiene);
    if(tiene == 1){

        $('#te_divTutor').css('display','block');
        $('#te_carnet').attr('required','required');
        //$('#te_expedido').attr('required','required');
        $('#te_nombre').attr('required','required');
        $('#te_fechaNacimiento').attr('required','required');
        $('#te_genero').attr('required','required');
        $('#te_idioma').attr('required','required');
        $('#te_ocupacion').attr('required','required');
        $('#te_instruccion').attr('required','required');
        $('#te_parentesco').attr('required','required');

        // Validamos si el usuario tiene carnet
        var carnet = $('#te_carnet').val();
        if(carnet.indexOf('SC') != -1){
            $('#te_carnet').val('');
            $('#te_carnet').removeAttr('required');
            $('#te_expedido').attr('disabled','disabled');
        }

    }else{
        $('#te_divTutor').css('display','none');
        $('#te_carnet').removeAttr('required');
        $('#te_expedido').removeAttr('required');
        $('#te_nombre').removeAttr('required');
        $('#te_fechaNacimiento').removeAttr('required');
        $('#te_genero').removeAttr('required');
        $('#te_idioma').removeAttr('required');
        $('#te_ocupacion').removeAttr('required');
        $('#te_instruccion').removeAttr('required');
        $('#te_parentesco').removeAttr('required');

        te_borrarDatos();

        $('#te_genero').val('');
        $('#te_correo').val('');
        $('#te_telefono').val('');
        $('#te_celular').val('');
        $('#te_idioma').val('');
        $('#te_ocupacion').val(0);
        $('#te_instruccion').val(0);
        $('#te_ocupacion').val('');
        $('#te_instruccion').val('');
        $('#te_parentesco').val('');
        $('#te_institucionTrabaja').val('');

        // saveFormTutor(false);
    }
}

te_validarTieneTutor();

// funcion para cambiar el tutor, mostrar la opcion de busqueda
var te_cambiar = function(){
    if(confirm('¿Esta seguro de que desea cambiar los datos del tutor?\nPresione aceptar para confirmar.')){
        $('#te_top1').css('display','none');

        // te_borrarDatos();

        // $('#te_genero').val('');
        // $('#te_correo').val('');
        // $('#te_telefono').val('');
        // $('#te_idioma').val('');
        // $('#te_ocupacion').val('');
        // $('#te_instruccion').val('');
        // $('#te_parentesco').val('');

        $('#te_top2').css('display','block');
    }
}

// Ocultar el campo otra ocupacion si la ocupacion es diferente de otro
var te_ocultarte_ocupacionOtro = function(){
    if($('#te_ocupacion').val() == 10035){
        $('#te_filaOtroOcupacion').css('display','block');                                                                      
        $('#te_ocupacionOtro').css('display','block');                                                                      
        $('#te_ocupacionOtro').attr('required','required');                                                                      
    }else{
        $('#te_filaOtroOcupacion').css('display','none');
        $('#te_ocupacionOtro').css('display','none');
        $('#te_ocupacionOtro').removeAttr('required');
    }
}

te_ocultarte_ocupacionOtro();

$('#te_ocupacion').on('change',function(){
    $('#te_ocupacionOtro').val('');
    te_ocultarte_ocupacionOtro();
});

// CAMBIAR ATRIBUTOS DE REQUERIDO
$('#teb_sinCarnet').on('change', function(){
    if($(this).is(':checked')){
        $('#teb_carnet').val('');
        $('#teb_carnet').attr('disabled','disabled');
        $('#teb_complemento').val('');
        $('#teb_complemento').attr('disabled','disabled');

        $('#teb_paterno').focus();

        $('#te_carnet').removeAttr('required');
    }else{
        $('#teb_carnet').removeAttr('disabled');
        $('#teb_complemento').removeAttr('disabled');
        
        $('#teb_carnet').focus();

        $('#te_carnet').attr('required','required');
    }
});

//verificamos si es extranjero y obligamos a llenar los campos necesarios
var mostrarTipoIdentificacionTutor_te=function ()
{
        $("#teb_es_extranjero").on( 'change', function()
        {
            if( $(this).is(':checked') )
            {
                $('#teb_nro_identidad').focus();
                $(".tediv-identificacion-extranjeros").show();
                $(".tediv-identificacion-nacionales").hide();

                $('#teb_carnet').removeAttr('required');
                $('#teb_complemento').removeAttr('required');

                $('#teb_carnet').attr('disabled','disabled');
                $('#teb_complemento').attr('disabled','disabled');

                $('#teb_nro_identidad').attr('required','required');

                $('#teb_carnet').val('');
                $('#teb_complemento').val('');
            }
            else
            {
                $('#teb_carnet').focus();
                $(".tediv-identificacion-extranjeros").hide();
                $(".tediv-identificacion-nacionales").show();

                $('#teb_carnet').removeAttr('disabled');
                $('#teb_complemento').removeAttr('disabled');

                $('#teb_carnet').attr('required','required');
                $('#teb_nro_identidad').removeAttr('required');

                $('#teb_nro_identidad').val('');
            }
        });
}

// Buscar tutor
var te_buscarTutor = function(){
    var te_carnet = $('#teb_carnet').val();
    var te_complemento = $('#teb_complemento').val();
    var te_paterno = $('#teb_paterno').val();
    var te_materno = $('#teb_materno').val();
    var te_nombre = $('#teb_nombre').val();
    var te_fechaNacimiento = $('#teb_fechaNacimiento').val();
    var pb_es_extranjero_te_segip = $('#pb_es_extranjero_te_segip').is(':checked');

    // Validamos si la fecha de nacimiento es correcta
    var te_df = te_fechaNacimiento.split('-');
    var te_anio = te_df[2];

    var te_fechaActual = new Date();
    var te_anioActual = te_fechaActual.getFullYear();

    if(te_anio < 1900 || (te_anioActual - te_anio) < 15 || (te_anioActual - te_anio) > 100){
        $('#te_mensaje').empty();
        te_cambiarFondoMensaje(3);
        $('#te_mensaje').append('La fecha de nacimiento no es válida, verfique e intentelo nuevamente.');
        return;
    }
    /////////
         //esta seccion de añadio para el caso de extranjeros
         var te_es_extranjero = $('#teb_es_extranjero').is(':checked')?1:0;
         var te_nro_identidad = $('#teb_nro_identidad').val();

    if($('#teb_sinCarnet').is(':checked')){

        if(te_nombre != "" && te_fechaNacimiento != ""){
            $('#teb_carnet').val('');
            var data = {
                id: 'nuevo',
                carnet: '',
                complemento: te_complemento,
                paterno: te_paterno,
                materno: te_materno,
                nombre: te_nombre,
                extranjero_segip: pb_es_extranjero_te_segip,
                fecha_nacimiento: te_fechaNacimiento
            };

            $('#te_expedido').attr('disabled','disabled');
            $('#te_expedido').val('');

            te_cargarDatos(data);

            $('#te_m_mensaje').empty();
            te_m_cambiarFondoMensaje(2);
            $('#te_m_mensaje').append('Datos cargados');

        }else{
            // $('#te_idPersona').val('nuevo');
            $('#te_mensaje').empty();
            te_cambiarFondoMensaje(3);
            $('#te_mensaje').append('Complete los datos de nombre y fecha de nacimiento');
        }
    }else{

        if((te_carnet != "" && te_nombre != "" && te_es_extranjero==0) || (te_nro_identidad.length>0  && te_nombre.length>0 && te_es_extranjero==1))
        {
            
            $.ajax({
                type: 'get',
                url: Routing.generate('info_estudiante_rude_nuevo_buscar_persona',{'carnet':te_carnet, 'complemento':te_complemento, 'paterno':te_paterno, 'materno': te_materno, 'nombre': te_nombre, 'fechaNacimiento': te_fechaNacimiento,'esExtranjero':te_es_extranjero,'documentoNro':te_nro_identidad,'extranjero_segip': pb_es_extranjero_te_segip}),
                beforeSend: function(){
                    $('#te_mensaje').empty();
                    te_cambiarFondoMensaje(1);
                    $('#te_mensaje').append('Buscando...');
                },
                success: function(data){
                    $('#te_expedido').removeAttr('disabled');
                    // Ponemos el id de la tutor en nuevo
                    $('#te_idPersona').val('nuevo');

                    if(data.status == 200){
                        console.log('Encontrado');
                        // Cargamos los datos devueltos por el servicio
                        te_cargarDatos(data.persona);

                        // Ubicamos el cursor en el campo telefono
                        $('#te_genero').focus();

                        // Creamos el mensaje de la busqueda
                        $('#te_mensaje').empty();
                        te_cambiarFondoMensaje(2);
                        $('#te_mensaje').append('La persona fue encontrada');

                    }else{
                        console.log('No encontrado');
                        te_borrarDatos();
                        // Ponemos el id de la tutor en nuevo
                        $('#te_idPersona').val('nuevo');

                        // Creasmos el mensaje de la busqueda
                        $('#te_mensaje').empty();
                        te_cambiarFondoMensaje(3);
                        $('#te_mensaje').append('La persona no fue encontrada');
                    }

                    // Verificar el parentesco con el estudiante
                    // setTimeout("te_validarParentesco()",3000);

                },
                error: function(data){

                    // Ponemos el id de la tutor en nuevo
                    $('#te_idPersona').val('nuevo');

                    te_borrarDatos();
                    
                    $('#te_mensaje').empty();
                    te_cambiarFondoMensaje(4);
                    $('#te_mensaje').append('Los datos introducidos son incorrectos o no hay conexion con el servicio.');
                }
            });
        }else{
            $('#te_mensaje').empty();
            te_cambiarFondoMensaje(3);
            $('#te_mensaje').append('Complete los datos de carnet y fecha de nacimiento para realizar la busqueda');
        }
    }

    
}

// cargar los campos con los datos devueltos por el servicio
var te_cargarDatos = function(data){
    $('#te_idPersona').val(data.id);
    $('#te_carnet').val(data.carnet);
    $('#te_complemento').val(data.complemento);
    $('#te_paterno').val(data.paterno);
    $('#te_materno').val(data.materno);
    $('#te_nombre').val(data.nombre);
    $('#te_fechaNacimiento').val(data.fecha_nacimiento);
    $('#te_cedulaTipoId').val(data.cedula_tipo_id);
}

// borrar los datos si el servicio no devuelve datos
function te_borrarDatos(){
    $('#te_carnet').val('');
    $('#te_complemento').val('');
    $('#te_paterno').val('');
    $('#te_materno').val('');
    $('#te_nombre').val('');
    $('#te_fechaNacimiento').val('');
    $('#te_genero').val('');
    $('#te_correo').val('');
    $('#te_telefono').val('');
    $('#te_celular').val('');
    $('#te_nro_identidad').val('');
    $('#te_institucionTrabaja').val('');
}

var te_cambiarFondoMensaje = function(opcion){
    // Default
    if(opcion == 0){
        $('#te_mensaje').css('background','#E1EBFA');
        $('#te_mensaje').css('color','#6496BA');
    }
    // Info
    if(opcion == 1){
        $('#te_mensaje').css('background','#E9F9FD');
        $('#te_mensaje').css('color','#1cadca');
    }
    // Success
    if(opcion == 2){
        $('#te_mensaje').css('background','#d7e9c3');
        $('#te_mensaje').css('color','#587f2e');
    }
    // Warning
    if(opcion == 3){
        $('#te_mensaje').css('background','#fdf0d4');
        $('#te_mensaje').css('color','#c88a0a');
    }
    // Danger
    if(opcion == 4){
        $('#te_mensaje').css('background','#f9cfc8');
        $('#te_mensaje').css('color','#ae2a14');
    }
}

$('#te_tieneTutor0').on('click', function(){
    alert('Tenga en cuenta que al seleccionar la opcion "No", se eliminaran los datos del tutor al momento de guardar la información.');
    
});

function te_limpiarBuscador(){
    $('#teb_carnet').val('');
    $('#teb_complemento').val('');
    $('#teb_paterno').val('');
    $('#teb_materno').val('');
    $('#teb_nombre').val('');
    $('#teb_fechaNacimiento').val('');
}

function te_saveFormTutor(){
    var data = $('#formTutorExt').serialize();
    var subsistema = $('#te_subsistema').val();
    // data['actualizar'] = recargar;
    $.ajax({
        //ojo revisar esto
        url: Routing.generate('info_estudiante_rude_nuevo_save_formApoderado'),
        type: 'post',
        data: data,
        beforeSend: function(){
            console.log('enviando')
            $('#cortina').css('display','block');
            te_limpiarBuscador();
        },
        success: function(data){
            $('#cortina').css('display','none');

            if(data.status == 200){
                $('#te_id').val(data.id);
                $('#te_idDatos').val(data.idDatos);
                $('#te_idPersona').val(data.idPersona);
                if(subsistema=='especial'){
                    $('#cortina').css('display','none');
                    // ojo revisar esto
                    $('#paso7').parent('li').removeClass('disabled');
                    $('#paso7').attr('data-toggle','tab');
                    $('#paso7').click();
                    $('#tabPadre').click();
                }else{
                    // ojo revisar esto
                    $('#paso5').parent('li').removeClass('disabled');
                    $('#paso5').attr('data-toggle','tab');
                    $('#paso5').click();
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