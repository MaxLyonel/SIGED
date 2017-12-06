/**
*   OPCIONES madre
*/
// funciones para ver si se debe actualizar o no los datos del apoderado

// Validomos el buscador
$("#mb_carnet").numeric("positiveInteger");
$("#mb_carnet").attr("maxlength",'10');

// aplicamos las mascaras para las fechas
$("#m_fechaNacimiento").inputmask({ "alias": "dd-mm-yyyy" });
$("#mb_fechaNacimiento").inputmask({ "alias": "dd-mm-yyyy" });
$("#mb_complemento").inputmask({mask: "9a"});

// convertimos el complemento en mayusculas
$('#mb_complemento').on('keyup',function(){
    $(this).val($(this).val().toUpperCase());
});

$('#m_idioma').chosen({width: "100%"});
$('#m_ocupacion').chosen({width: "100%"});
$('#m_bloqueOcupacion .chosen-search input').autocomplete({
    minLength: 3,
    source: function(request, response){
        $.ajax({
            url: Routing.generate('estudianteSocioeconomico_buscar_ocupaciones',{'texto':request.term}),
            dataType: "json",
            beforeSend: function(){ $('#m_bloqueOcupacion ul.chosen-results').empty(); $('#m_ocupacion').empty(); }
        }).done(function(data){
            console.log('madre');
            response(
                $.each(data, function(i, value){
                    $('#m_ocupacion').append('<option value="'+i+'">'+value+'</option>');
                }));
            var valor = $('#m_bloqueOcupacion .chosen-search input').val();
            $('#m_ocupacion').trigger("chosen:updated");
            $('#m_bloqueOcupacion .chosen-search input').val(valor);
        });
    }
});

// Verificar si la persona es nueva para pedir la fotocopia de carnet
var m_verificarPersonaNuevo = function(){
    var idPersona = $('#m_idPersona').val();
    if(idPersona == 'nuevo'){
        //$('#m_documento').attr('required','required');
    }else{
        $('#m_documento').removeAttr('required');        
    }
}

m_verificarPersonaNuevo();

// Validar el tipo de archivo subido
$('#m_documento').change(function(){
    var archivo = $('#m_documento').val();
    var extensionesPermitidas = new Array('.jpg','.jpeg','.png','.bmp');
    miError = "";
    if(!archivo){

    }else{
        extension = (archivo.substring(archivo.lastIndexOf("."))).toLowerCase();
        permitida = false;
        var mensaje = 'El archivo no es válido, solo puede subir imagenes con formato .jpg .jpeg .png .bmp';
        for(var i = 0; i < extensionesPermitidas.length; i++){
            if(extensionesPermitidas[i] == extension){
                // Verificamos el tamño
                var fileSize = $('#m_documento')[0].files[0].size;
                console.log(fileSize);
                var sizeKiloByte = parseInt(fileSize / 1024);
                console.log(sizeKiloByte);
                if(sizeKiloByte < 1024){
                    permitida = true
                }else{
                    var mensaje = 'El tamaño de la imagen supera el límite permitido!\n(Tamaño máximo permitido 1024 KB)';
                }
                break;
            }
        }
        if(!permitida){
            alert(mensaje);
            $('#m_documento').val('');
        }
    }
});

// funcion para validar si tiene o no madre
var m_validarTieneMadre = function(){    
    var tiene = $('input:radio[name=m_tieneMadre]:checked').val();
    if(tiene == 1){
        $('#m_divMadre').css('display','block');
        $('#m_carnet').attr('required','required');
        $('#m_nombre').attr('required','required');
        $('#m_fechaNacimiento').attr('required','required');
        $('#m_genero').attr('required','required');
        $('#m_idioma').attr('required','required');
        $('#m_ocupacion').attr('required','required');
        $('#m_instruccion').attr('required','required');
        $('#m_parentesco').attr('required','required');

        // ejecutamos funcion para poner en requerido o no el campo documento file
        m_verificarPersonaNuevo();

    }else{
        $('#m_divMadre').css('display','none');
        $('#m_carnet').removeAttr('required');
        $('#m_carnet').removeAttr('required');
        $('#m_nombre').removeAttr('required');
        $('#m_fechaNacimiento').removeAttr('required');
        $('#m_genero').removeAttr('required');
        $('#m_idioma').removeAttr('required');
        $('#m_ocupacion').removeAttr('required');
        $('#m_instruccion').removeAttr('required');
        $('#m_parentesco').removeAttr('required');
        $('#m_documento').removeAttr('required');
    }
}

m_validarTieneMadre();

// funcion para cambiar madre, mostrar la opcion de busqueda
var m_cambiar = function(){
    if(confirm('¿Esta seguro de que desea cambiar la madre?\nPresione aceptar para confirmar.')){
        $('#mop1').css('display','none');

        m_borrarDatos();

        $('#m_paterno').removeAttr('readonly');
        $('#m_materno').removeAttr('readonly');
        $('#m_nombre').removeAttr('readonly');

        $('#m_genero').val('');
        $('#m_correo').val('');
        $('#m_telefono').val('');
        $('#m_idioma').val('');
        $('#m_ocupacion').val('');
        $('#m_instruccion').val('');
        $('#m_parentesco').val('');

        $('#mop2').css('display','block');

        $('#m_fotoCarnet').css('display','none');
    }
}

// Ocultar el campo otra ocupacion si la ocupacion es diferente de otro
var ocultarm_ocupacionOtro = function(){
    if($('#m_ocupacion').val() == 10004){
        $('#m_ocupacionOtro').css('display','block');
        $('#m_ocupacionOtro').attr('required','required');
    }else{
        $('#m_ocupacionOtro').css('display','none');
        $('#m_ocupacionOtro').removeAttr('required');
    }
}

ocultarm_ocupacionOtro();

$('#m_ocupacion').on('change',function(){
    $('#m_ocupacionOtro').val('');
    ocultarm_ocupacionOtro();
});

// Buscar madre
var m_buscarMadre = function(){
    var m_carnet = $('#mb_carnet').val();
    var m_complemento = $('#mb_complemento').val();
    var m_fechaNacimiento = $('#mb_fechaNacimiento').val();

    if(m_carnet != "" && m_fechaNacimiento != ""){

        $.ajax({
            type: 'get',
            url: Routing.generate('estudianteSocioeconomico_buscar_persona',{'carnet':m_carnet, 'complemento':m_complemento, 'fechaNacimiento': m_fechaNacimiento}),
            beforeSend: function(){
                $('#m_mensaje').empty();
                cambiarFondoMensaje(1);
                $('#m_mensaje').append('Buscando...');
            },
            success: function(data){
                if(data.result != 'null'){
                    console.log('Encontrado');
                    // Cargamos los datos devueltos por el servicio
                    m_cargarDatos(data.result[0]);
                    // Ponemos el id de madre en nuevo
                    $('#m_idPersona').val(data.result[0].id);

                    // Verificamos si el dato devuelto esta validado por el segip para bloquear determinados controles
                    if(data.result[0].segip_id >= 1){
                        $('#m_carnet').attr('readonly','readonly');
                        $('#m_complemento').attr('readonly','readonly');
                        $('#m_paterno').attr('readonly','readonly');
                        $('#m_materno').attr('readonly','readonly');
                        $('#m_nombre').attr('readonly','readonly');
                        $('#m_fechaNacimiento').attr('readonly','readonly');
                    }else{
                        $('#m_carnet').attr('readonly','readonly');
                        $('#m_complemento').attr('readonly','readonly');
                        $('#m_paterno').removeAttr('readonly');
                        $('#m_materno').removeAttr('readonly');
                        $('#m_nombre').removeAttr('readonly');
                        $('#m_fechaNacimiento').removeAttr('readonly');
                    }

                    // Ocultamos el campo de file documento y le quitamos el attr requerido
                    $('#m_documento').removeAttr('required');
                    $('#m_filaDocumento').css('display','none');

                    // Ubicamos el cursor en el campo telefono
                    $('#m_correo').focus();

                    // Creamos el mensaje de la busqueda
                    $('#m_mensaje').empty();
                    cambiarFondoMensaje(2);
                    $('#m_mensaje').append('La persona fue encontrada');
                }else{
                    console.log('No encontrado');
                    m_borrarDatos();
                    // Ponemos el id de madre en nuevo
                    $('#m_idPersona').val('nuevo');

                    // Asignamos los datos de la busqueda y se bloquean y desbloquean algunos controles
                    $('#m_carnet').val(m_carnet);
                    $('#m_carnet').attr('readonly','readonly');

                    $('#m_complemento').val(m_complemento);
                    $('#m_complemento').attr('readonly','readonly');

                    $('#m_fechaNacimiento').val(m_fechaNacimiento);
                    $('#m_fechaNacimiento').attr('readonly','readonly');

                    $('#m_paterno').removeAttr('readonly');
                    $('#m_materno').removeAttr('readonly');
                    $('#m_nombre').removeAttr('readonly');

                    // Mostramos el campo de file documento y le asignamos el attr requerido
                    //$('#m_documento').attr('required','required');
                    $('#m_filaDocumento').css('display','table-row');

                    // Ubicamos el cursor en el campo paterno
                    $('#m_paterno').focus();

                    // Creasmos el mensaje de la busqueda
                    $('#m_mensaje').empty();
                    cambiarFondoMensaje(3);
                    $('#m_mensaje').append('La persona no fue encontrada');
                }

                // Verificar el parentesco con el estudiante
                setTimeout("m_validarParentesco()",3000);

            },
            error: function(data){
                m_borrarDatos();
                //$('#m_carnet').val(m_carnet);
                $('#m_carnet').attr('readonly','readonly');

                //$('#m_complemento').val(m_complemento);
                $('#m_complemento').attr('readonly','readonly');

                //$('#m_fechaNacimiento').val(m_fechaNacimiento);
                $('#m_fechaNacimiento').attr('readonly','readonly');

                $('#m_paterno').removeAttr('readonly');
                $('#m_materno').removeAttr('readonly');
                $('#m_nombre').removeAttr('readonly');

                $('#m_mensaje').empty();
                cambiarFondoMensaje(4);
                $('#m_mensaje').append('Los datos introducidos son incorrectos o no hay conexion con el servicio.');
            }
        });
    }else{
        $('#m_mensaje').empty();
        cambiarFondoMensaje(3);
        $('#m_mensaje').append('Complete los datos de carnet y fecha de nacimiento para realizar la busqueda');
    }
}

// cargar los campos con los datos devueltos por el servicio
var m_cargarDatos = function(data){
    $('#m_carnet').val(data.carnet);
    $('#m_complemento').val(data.complemento);
    $('#m_paterno').val(data.paterno);
    $('#m_materno').val(data.materno);
    $('#m_nombre').val(data.nombre);
    $('#m_fechaNacimiento').val(data.fecha_nacimiento);
    $('#m_fechaNacimiento').val(data.fecha_nacimiento);
    $('#m_genero').val(data.genero_tipo_id);
    $('#m_correo').val(data.correo);
    $('#m_segipId').val(data.segipId);
}


// borrar los datos si el servicio no devuelve datos
var m_borrarDatos = function(){
    $('#m_carnet').val('');
    $('#m_complemento').val('');
    $('#m_paterno').val('');
    $('#m_materno').val('');
    $('#m_nombre').val('');
    $('#m_fechaNacimiento').val('');
    $('#m_genero').val('');
    $('#m_correo').val('');
    $('#m_segipId').val('');
}

// VAlidar los apellidos del estudiante y del padre para mostrar el mensaje
var m_validarParentesco = function(){
    var parentesco = $('#m_parentesco').val();
    if(parentesco == 1){ // si el parentesco es padre
        var apellidoPaternoHijo = $('#apellidoPaternoHijo').val();
        var apellidoPaternoPadre = $('#m_paterno').val().toUpperCase();
        if(apellidoPaternoHijo != apellidoPaternoPadre){
            $('#m_mensaje').empty();
            cambiarFondoMensaje(3);
            $('#m_mensaje').append('El apellido paterno de la madre no coincide con el apellido materno del estudiante');
        }else{
            $('#m_mensaje').empty();
            cambiarFondoMensaje(0);
            $('#m_mensaje').append('Opciones');
        }
    }else{
        $('#m_mensaje').empty();
        cambiarFondoMensaje(0);
        $('#m_mensaje').append('Opciones');
    }
}

$('#m_parentesco').on('change',function(){
    m_validarParentesco();
});

var cambiarFondoMensaje = function(opcion){
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
    // Succes
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