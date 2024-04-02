/**
*   OPCIONES MADRE
*/
// funciones para ver si se debe actualizar o no los datos del apoderado

// Validomos el buscador
$("#mb_carnet").numeric("positiveInteger");
$("#mb_carnet").attr("maxlength",'10');

// aplicamos las mascaras para las fechas
$("#m_fechaNacimiento").inputmask({ "alias": "dd-mm-yyyy" ,'placeholder':'dd-mm-aaaa'});
$("#mb_fechaNacimiento").inputmask({ "alias": "dd-mm-yyyy" ,'placeholder':'dd-mm-aaaa'});
//$("#mb_complemento").inputmask({mask: "9a"});

// convertimos el complemento en mayusculas
$('#mb_complemento').on('keyup',function(){
    $(this).val($(this).val().toUpperCase());
});

// $('#m_idioma').select2({width: "100%"});
// $('#m_ocupacion').select2({width: "100%"});
// $('#m_idioma').chosen({width: "100%"}); 
// $('#m_ocupacion').chosen({width: "100%"}); 

// funcion para validar si tiene o no madre
var m_validarTieneMadre = function(){    
    var tiene = $('input:radio[name=m_tieneMadre]:checked').val();
    m_limpiarBuscador();
    if(tiene == 1){
        $('#m_divMadre').css('display','block');
        $('#m_carnet').attr('required','required');
        //$('#m_expedido').attr('required','required');
        $('#m_nombre').attr('required','required');
        $('#m_fechaNacimiento').attr('required','required');
        $('#m_genero').attr('required','required');
        $('#m_idioma').attr('required','required');
        $('#m_ocupacion').attr('required','required');
        $('#m_instruccion').attr('required','required');
        $('#m_parentesco').attr('required','required');

        // Validamos si el usuario tiene carnet
        var carnet = $('#m_carnet').val();
        if(carnet.indexOf('SC') != -1){
            $('#m_carnet').val('');
            $('#m_carnet').removeAttr('required');
            $('#m_expedido').attr('disabled','disabled');
        }

    }else{
        $('#m_divMadre').css('display','none');
        $('#m_carnet').removeAttr('required');
        $('#m_expedido').removeAttr('required');
        $('#m_nombre').removeAttr('required');
        $('#m_fechaNacimiento').removeAttr('required');
        $('#m_genero').removeAttr('required');
        $('#m_idioma').removeAttr('required');
        $('#m_ocupacion').removeAttr('required');
        $('#m_instruccion').removeAttr('required');
        $('#m_parentesco').removeAttr('required');

        m_borrarDatos();

        $('#m_genero').val('');
        $('#m_correo').val('');
        $('#m_telefono').val('');
        $('#m_celular').val('');
        $('#m_idioma').val('');
        $('#m_ocupacion').val(0);
        $('#m_instruccion').val(0);
        $('#m_ocupacion').val('');
        $('#m_instruccion').val('');
        $('#m_parentesco').val('');

        // saveFormMadre(false);
    }
}

m_validarTieneMadre();

// funcion para cambiar la madre, mostrar la opcion de busqueda
var m_cambiar = function(){
    if(confirm('¿Esta seguro de que desea cambiar los datos de la madre?\nPresione aceptar para confirmar.')){
        $('#m_top1').css('display','none');

        // m_borrarDatos();

        // $('#m_genero').val('');
        // $('#m_correo').val('');
        // $('#m_telefono').val('');
        // $('#m_idioma').val('');
        // $('#m_ocupacion').val('');
        // $('#m_instruccion').val('');
        // $('#m_parentesco').val('');

        $('#m_top2').css('display','block');
    }
}

// Ocultar el campo otra ocupacion si la ocupacion es diferente de otro
var ocultarm_ocupacionOtro = function(){
    if($('#m_ocupacion').val() == 10035){
        $('#m_filaOtroOcupacion').css('display','block');                                                                      
        $('#m_ocupacionOtro').css('display','block');                                                                      
        $('#m_ocupacionOtro').attr('required','required');                                                                      
    }else{
        $('#m_filaOtroOcupacion').css('display','none');
        $('#m_ocupacionOtro').css('display','none');
        $('#m_ocupacionOtro').removeAttr('required');
    }
}

ocultarm_ocupacionOtro();

$('#m_ocupacion').on('change',function(){
    $('#m_ocupacionOtro').val('');
    ocultarm_ocupacionOtro();
});

// CAMBIAR ATRIBUTOS DE REQUERIDO
$('#mb_sinCarnet').on('change', function(){
    if($(this).is(':checked')){
        $('#mb_carnet').val('');
        $('#mb_carnet').attr('disabled','disabled');
        $('#mb_complemento').val('');
        $('#mb_complemento').attr('disabled','disabled');

        $('#mb_paterno').focus();

        $('#m_carnet').removeAttr('required');
    }else{
        $('#mb_carnet').removeAttr('disabled');
        $('#mb_complemento').removeAttr('disabled');
        $('#mb_carnet').removeAttr('disabled');
        $('#mb_complemento').removeAttr('disabled');
        
        $('#mb_carnet').focus();

        $('#m_carnet').attr('required','required');
    }
});

//verificamos si es extranjero y obligamos a llenar los campos necesarios
var mostrarTipoIdentificacionMadre=function ()
{
        $("#mb_es_extranjero").on( 'change', function()
        {
            if( $(this).is(':checked') )
            {
                $('#mb_nro_identidad').focus();
                $(".mdiv-identificacion-extranjeros").show();
                $(".mdiv-identificacion-nacionales").hide();

                $('#mb_carnet').removeAttr('required');
                $('#mb_complemento').removeAttr('required');

                $('#mb_carnet').attr('disabled','disabled');
                $('#mb_complemento').attr('disabled','disabled');

                $('#mb_nro_identidad').attr('required','required');

                $('#mb_carnet').val('');
                $('#mb_complemento').val('');
            }
            else
            {
                $('#mb_carnet').focus();
                $(".mdiv-identificacion-extranjeros").hide();
                $(".mdiv-identificacion-nacionales").show();

                $('#mb_carnet').removeAttr('disabled');
                $('#mb_complemento').removeAttr('disabled');

                $('#mb_carnet').attr('required','required');
                $('#mb_nro_identidad').removeAttr('required');

                $('#mb_nro_identidad').val('');
            }
        });
}



// Buscar madre
var m_buscarMadre = function(){
    var m_carnet = $('#mb_carnet').val();
    var m_complemento = $('#mb_complemento').val();
    var m_paterno = $('#mb_paterno').val();
    var m_materno = $('#mb_materno').val();
    var m_nombre = $('#mb_nombre').val();
    var m_fechaNacimiento = $('#mb_fechaNacimiento').val();
    var pb_es_extranjero_m_segip = $('#pb_es_extranjero_m_segip').is(':checked');
    // Validamos si la fecha de nacimiento es correcta
    var m_df = m_fechaNacimiento.split('-');
    var m_anio = m_df[2];

    var m_fechaActual = new Date();
    var m_anioActual = m_fechaActual.getFullYear();

    if(m_anio < 1900 || (m_anioActual - m_anio) < 15 || (m_anioActual - m_anio) > 100){
        $('#m_mensaje').empty();
        m_cambiarFondoMensaje(3);
        $('#m_mensaje').append('La fecha de nacimiento no es válida, verfique e intentelo nuevamente.');
        return;
    }
    /////////
    //esta seccion de añadio para el caso de extranjeros
    var m_es_extranjero = $('#mb_es_extranjero').is(':checked')?1:0;
    var m_nro_identidad = $('#mb_nro_identidad').val();

    if($('#mb_sinCarnet').is(':checked')){

        if(m_nombre != "" && m_fechaNacimiento != ""){
            $('#mb_carnet').val('');
            var data = {
                id: 'nuevo',
                carnet: '',
                complemento: m_complemento,
                paterno: m_paterno,
                materno: m_materno,
                nombre: m_nombre,
                extranjero_segip: pb_es_extranjero_m_segip,
                fecha_nacimiento: m_fechaNacimiento
            };

            $('#m_expedido').attr('disabled','disabled');
            $('#m_expedido').val('');

            m_cargarDatos(data);

            $('#m_mensaje').empty();
            m_cambiarFondoMensaje(2);
            $('#m_mensaje').append('Datos cargados');

        }else{
            // $('#m_idPersona').val('nuevo');
            $('#m_mensaje').empty();
            m_cambiarFondoMensaje(3);
            $('#m_mensaje').append('Complete los datos de nombre y fecha de nacimiento');
        }
    }else{
        if((m_carnet != "" && m_nombre != "" && m_es_extranjero==0) || (m_nro_identidad.length>0  && m_nombre.length>0 && m_es_extranjero==1))
        {

            $.ajax({
                type: 'get',
                url: Routing.generate('info_estudiante_rude_nuevo_buscar_persona',{'carnet':m_carnet, 'complemento':m_complemento, 'paterno':m_paterno, 'materno': m_materno, 'nombre': m_nombre, 'fechaNacimiento': m_fechaNacimiento,'esExtranjero':m_es_extranjero,'documentoNro':m_nro_identidad,'extranjero_segip':pb_es_extranjero_m_segip}),
                beforeSend: function(){
                    $('#m_mensaje').empty();
                    m_cambiarFondoMensaje(1);
                    $('#m_mensaje').append('Buscando...');
                },
                success: function(data){
                    $('#m_expedido').removeAttr('disabled');
                    // Ponemos el id de la madre en nuevo
                    $('#m_idPersona').val('nuevo');

                    if(data.status == 200){
                        console.log('Encontrado');
                        // Cargamos los datos devueltos por el servicio
                        m_cargarDatos(data.persona);

                        // Ubicamos el cursor en el campo telefono
                        $('#m_genero').focus();

                        // Creamos el mensaje de la busqueda
                        $('#m_mensaje').empty();
                        m_cambiarFondoMensaje(2);
                        $('#m_mensaje').append('La persona fue encontrada');

                    }else{
                        console.log('No encontrado');
                        m_borrarDatos();
                        // Ponemos el id del madre en nuevo
                        $('#m_idPersona').val('nuevo');

                        // Creasmos el mensaje de la busqueda
                        $('#m_mensaje').empty();
                        m_cambiarFondoMensaje(3);
                        $('#m_mensaje').append('La persona no fue encontrada');
                    }

                    // Verificar el parentesco con el estudiante
                    // setTimeout("m_validarParentesco()",3000);

                },
                error: function(data){

                    // Ponemos el id de la madre en nuevo
                    $('#m_idPersona').val('nuevo');

                    m_borrarDatos();
                    
                    $('#m_mensaje').empty();
                    m_cambiarFondoMensaje(4);
                    $('#m_mensaje').append('Los datos introducidos son incorrectos o no hay conexion con el servicio.');
                }
            });
        }else{
            $('#m_mensaje').empty();
            m_cambiarFondoMensaje(3);
            if($('#mb_es_extranjero').is(':checked'))
                $('#m_mensaje').append('Complete los datos de nro de identidad y fecha de nacimiento para realizar la busqueda');
            else
                $('#m_mensaje').append('Complete los datos de carnet y fecha de nacimiento para realizar la busqueda');
        }
    }
}

// cargar los campos con los datos devueltos por el servicio
var m_cargarDatos = function(data){
    
    $('#m_idPersona').val(data.id);
    $('#m_carnet').val(data.carnet);
    $('#m_complemento').val(data.complemento);
    $('#m_paterno').val(data.paterno);
    $('#m_materno').val(data.materno);
    $('#m_nombre').val(data.nombre);
    $('#m_fechaNacimiento').val(data.fecha_nacimiento);
    $('#m_cedulaTipoId').val(data.cedula_tipo_id);
}

// borrar los datos si el servicio no devuelve datos
function m_borrarDatos(){
    $('#m_carnet').val('');
    $('#m_complemento').val('');
    $('#m_paterno').val('');
    $('#m_materno').val('');
    $('#m_nombre').val('');
    $('#m_fechaNacimiento').val('');
    $('#m_genero').val('');
    $('#m_correo').val('');
    $('#m_telefono').val('');
    $('#m_celular').val('');
    $('#m_nro_identidad').val('');
}

var m_cambiarFondoMensaje = function(opcion){
    // Default
    if(opcion == 0){
        $('#m_mensaje').css('background','#E1EBFA');
        $('#m_mensaje').css('color','#6496BA');
    }
    // Info
    if(opcion == 1){
        $('#m_mensaje').css('background','#E9F9FD');
        $('#m_mensaje').css('color','#1cadca');
    }
    // Success
    if(opcion == 2){
        $('#m_mensaje').css('background','#d7e9c3');
        $('#m_mensaje').css('color','#587f2e');
    }
    // Warning
    if(opcion == 3){
        $('#m_mensaje').css('background','#fdf0d4');
        $('#m_mensaje').css('color','#c88a0a');
    }
    // Danger
    if(opcion == 4){
        $('#m_mensaje').css('background','#f9cfc8');
        $('#m_mensaje').css('color','#ae2a14');
    }
}

$('#tieneMadre0').on('click', function(){
    alert('Tenga en cuenta que al seleccionar la opcion "No", se eliminaran los datos de la madre al momento de guardar la información.');
    
});

function m_limpiarBuscador(){
    $('#mb_carnet').val('');
    $('#mb_complemento').val('');
    $('#mb_paterno').val('');
    $('#mb_materno').val('');
    $('#mb_nombre').val('');
    $('#mb_fechaNacimiento').val('');
}

function saveFormMadre(){
    var data = $('#formMadre').serialize();
    var subsistema = $('#subsistema').val();
    
    // data['actualizar'] = recargar;
    $.ajax({
        url: Routing.generate('info_estudiante_rude_nuevo_save_formApoderado'),
        type: 'post',
        data: data,
        beforeSend: function(){
            console.log('enviando')
            $('#cortina').css('display','block');
            m_limpiarBuscador();
        },
        success: function(data){
            $('#cortina').css('display','none');

            if (data.status == 200) {

                $('#m_id').val(data.id);
                $('#m_idDatos').val(data.idDatos);
                $('#m_idPersona').val(data.idPersona);

                // Pasar a la siguiente pagina
                // if(recargar){
                //     $('#paso5').parent('li').removeClass('disabled');
                //     $('#paso5').attr('data-toggle','tab');
                //     $('#paso5').click();
                // }
                $('#tabTutor').click();
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