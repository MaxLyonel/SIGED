/**
*   OPCIONES TUTOR
*/
// funciones para ver si se debe actualizar o no los datos del apoderado

// Validomos el buscador
$("#tb_carnet").numeric("positiveInteger");
$("#tb_carnet").attr("maxlength",'10');

// aplicamos las mascaras para las fechas
$("#t_fechaNacimiento").inputmask({ "alias": "dd-mm-yyyy" });
$("#tb_fechaNacimiento").inputmask({ "alias": "dd-mm-yyyy" });
$("#tb_complemento").inputmask({mask: "9a"});

// convertimos el complemento en mayusculas
$('#tb_complemento').on('keyup',function(){
    $(this).val($(this).val().toUpperCase());
});

$('#t_idioma').chosen({width: "100%"});
$('#t_ocupacion').chosen({width: "100%"});
$('#t_bloqueOcupacion .chosen-search input').autocomplete({
    minLength: 3,
    source: function(request, response){
        $.ajax({
            url: Routing.generate('estudianteSocioeconomico_buscar_ocupaciones',{'texto':request.term}),
            dataType: "json",
            beforeSend: function(){ $('#t_bloqueOcupacion ul.chosen-results').empty(); $('#t_ocupacion').empty(); }
        }).done(function(data){
            console.log('tutor');
            response(
                $.each(data, function(i, value){
                    $('#t_ocupacion').append('<option value="'+i+'">'+value+'</option>');
                }));

            var valor = $('#t_bloqueOcupacion .chosen-search input').val();
            $('#t_ocupacion').trigger("chosen:updated");
            $('#t_bloqueOcupacion .chosen-search input').val(valor);
        });
    }
});

// Verificar si la persona es nueva para pedir la fotocopia de carnet
var t_verificarPersonaNuevo = function(){
    var idPersona = $('#t_idPersona').val();
    if(idPersona == 'nuevo'){
        //$('#t_documento').attr('required','required');
    }else{
        $('#t_documento').removeAttr('required');        
    }
}

t_verificarPersonaNuevo();

// Validar el tipo de archivo subido
$('#t_documento').change(function(){
    var archivo = $('#t_documento').val();
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
                var fileSize = $('#t_documento')[0].files[0].size;
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
            $('#t_documento').val('');
        }
    }
});

// funcion para validar si tiene o no tutor
var t_validarTieneTutor = function(){    
    var tiene = $('input:radio[name=t_tieneTutor]:checked').val();
    if(tiene == 1){
        $('#t_divTutor').css('display','block');
        $('#t_carnet').attr('required','required');
        $('#t_nombre').attr('required','required');
        $('#t_fechaNacimiento').attr('required','required');
        $('#t_genero').attr('required','required');
        $('#t_idioma').attr('required','required');
        $('#t_ocupacion').attr('required','required');
        $('#t_instruccion').attr('required','required');
        $('#t_parentesco').attr('required','required');

        // ejecutamos funcion para poner en requerido o no el campo documento file
        t_verificarPersonaNuevo();

    }else{
        $('#t_divTutor').css('display','none');
        $('#t_carnet').removeAttr('required');
        $('#t_carnet').removeAttr('required');
        $('#t_nombre').removeAttr('required');
        $('#t_fechaNacimiento').removeAttr('required');
        $('#t_genero').removeAttr('required');
        $('#t_idioma').removeAttr('required');
        $('#t_ocupacion').removeAttr('required');
        $('#t_instruccion').removeAttr('required');
        $('#t_parentesco').removeAttr('required');
        $('#t_documento').removeAttr('required');
    }
}

t_validarTieneTutor();

// funcion para cambiar el tutor, mostrar la opcion de busqueda
var t_cambiar = function(){
    if(confirm('¿Esta seguro de que desea cambiar el padre o tutor?\nPresione aceptar para confirmar.')){
        $('#top1').css('display','none');

        t_borrarDatos();

        $('#t_paterno').removeAttr('readonly');
        $('#t_materno').removeAttr('readonly');
        $('#t_nombre').removeAttr('readonly');

        $('#t_genero').val('');
        $('#t_correo').val('');
        $('#t_telefono').val('');
        $('#t_idioma').val('');
        $('#t_ocupacion').val('');
        $('#t_instruccion').val('');
        $('#t_parentesco').val('');

        $('#top2').css('display','block');

        $('#t_fotoCarnet').css('display','none');
    }
}

// Ocultar el campo otra ocupacion si la ocupacion es diferente de otro
var ocultart_ocupacionOtro = function(){
    if($('#t_ocupacion').val() == 10004){
        $('#t_ocupacionOtro').css('display','block');                                                                      
        $('#t_ocupacionOtro').attr('required','required');                                                                      
    }else{
        $('#t_ocupacionOtro').css('display','none');
        $('#t_ocupacionOtro').removeAttr('required');
    }
}

ocultart_ocupacionOtro();

$('#t_ocupacion').on('change',function(){
    $('#t_ocupacionOtro').val('');
    ocultart_ocupacionOtro();
});

// Buscar tutor
var t_buscarTutor = function(){
    var t_carnet = $('#tb_carnet').val();
    var t_complemento = $('#tb_complemento').val();
    var t_fechaNacimiento = $('#tb_fechaNacimiento').val();

    if(t_carnet != "" && t_fechaNacimiento != ""){

        $.ajax({
            type: 'get',
            url: Routing.generate('estudianteSocioeconomico_buscar_persona',{'carnet':t_carnet, 'complemento':t_complemento, 'fechaNacimiento': t_fechaNacimiento}),
            beforeSend: function(){
                $('#t_mensaje').empty();
                t_cambiarFondoMensaje(1);
                $('#t_mensaje').append('Buscando...');
            },
            success: function(data){
                if(data.result != 'null'){
                    console.log('Encontrado');
                    // Cargamos los datos devueltos por el servicio
                    t_cargarDatos(data.result[0]);
                    // Ponemos el id de tutor en nuevo
                    $('#t_idPersona').val(data.result[0].id);

                    // Verificamos si el dato devuelto esta validado por el segip para bloquear determinados controles
                    if(data.result[0].segip_id >= 1){
                        $('#t_carnet').attr('readonly','readonly');
                        $('#t_complemento').attr('readonly','readonly');
                        $('#t_paterno').attr('readonly','readonly');
                        $('#t_materno').attr('readonly','readonly');
                        $('#t_nombre').attr('readonly','readonly');
                        $('#t_fechaNacimiento').attr('readonly','readonly');
                    }else{
                        $('#t_carnet').attr('readonly','readonly');
                        $('#t_complemento').attr('readonly','readonly');
                        $('#t_paterno').removeAttr('readonly');
                        $('#t_materno').removeAttr('readonly');
                        $('#t_nombre').removeAttr('readonly');
                        $('#t_fechaNacimiento').removeAttr('readonly');
                    }

                    // Ocultamos el campo de file documento y le quitamos el attr requerido
                    $('#t_documento').removeAttr('required');
                    $('#t_filaDocumento').css('display','none');

                    // Ubicamos el cursor en el campo telefono
                    $('#t_correo').focus();

                    // Creamos el mensaje de la busqueda
                    $('#t_mensaje').empty();
                    t_cambiarFondoMensaje(2);
                    $('#t_mensaje').append('La persona fue encontrada');
                }else{
                    console.log('No encontrado');
                    t_borrarDatos();
                    // Ponemos el id de tutor en nuevo
                    $('#t_idPersona').val('nuevo');

                    // Asignamos los datos de la busqueda y se bloquean y desbloquean algunos controles
                    $('#t_carnet').val(t_carnet);
                    $('#t_carnet').attr('readonly','readonly');

                    $('#t_complemento').val(t_complemento);
                    $('#t_complemento').attr('readonly','readonly');

                    $('#t_fechaNacimiento').val(t_fechaNacimiento);
                    $('#t_fechaNacimiento').attr('readonly','readonly');

                    $('#t_paterno').removeAttr('readonly');
                    $('#t_materno').removeAttr('readonly');
                    $('#t_nombre').removeAttr('readonly');

                    // Mostramos el campo de file documento y le asignamos el attr requerido
                    //$('#t_documento').attr('required','required');
                    $('#t_filaDocumento').css('display','table-row');

                    // Ubicamos el cursor en el campo paterno
                    $('#t_paterno').focus();

                    // Creasmos el mensaje de la busqueda
                    $('#t_mensaje').empty();
                    t_cambiarFondoMensaje(3);
                    $('#t_mensaje').append('La persona no fue encontrada');
                }

                // Verificar el parentesco con el estudiante
                setTimeout("t_validarParentesco()",3000);

            },
            error: function(data){
                t_borrarDatos();
                //$('#t_carnet').val(t_carnet);
                $('#t_carnet').attr('readonly','readonly');

                //$('#t_complemento').val(t_complemento);
                $('#t_complemento').attr('readonly','readonly');

                //$('#t_fechaNacimiento').val(t_fechaNacimiento);
                $('#t_fechaNacimiento').attr('readonly','readonly');

                $('#t_paterno').removeAttr('readonly');
                $('#t_materno').removeAttr('readonly');
                $('#t_nombre').removeAttr('readonly');

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

// cargar los campos con los datos devueltos por el servicio
var t_cargarDatos = function(data){
    $('#t_carnet').val(data.carnet);
    $('#t_complemento').val(data.complemento);
    $('#t_paterno').val(data.paterno);
    $('#t_materno').val(data.materno);
    $('#t_nombre').val(data.nombre);
    $('#t_fechaNacimiento').val(data.fecha_nacimiento);
    $('#t_genero').val(data.genero_tipo_id);
    $('#t_correo').val(data.correo);
    $('#t_segipId').val(data.segipId);
}


// borrar los datos si el servicio no devuelve datos
var t_borrarDatos = function(){
    $('#t_carnet').val('');
    $('#t_complemento').val('');
    $('#t_paterno').val('');
    $('#t_materno').val('');
    $('#t_nombre').val('');
    $('#t_fechaNacimiento').val('');
    $('#t_genero').val('');
    $('#t_correo').val('');
    $('#t_segipId').val('');
}

// VAlidar los apellidos del estudiante y del padre para mostrar el mensaje
var t_validarParentesco = function(){
    var parentesco = $('#t_parentesco').val();
    if(parentesco == 1){ // si el parentesco es padre
        var apellidoPaternoHijo = $('#apellidoPaternoHijo').val();
        console.log(apellidoPaternoHijo);
        var apellidoPaternoPadre = $('#t_paterno').val().toUpperCase();
        console.log(apellidoPaternoPadre);
        if(apellidoPaternoHijo != apellidoPaternoPadre){
            $('#t_mensaje').empty();
            t_cambiarFondoMensaje(3);
            $('#t_mensaje').append('El apellido paterno del padre o tutor no coincide con el apellido paterno del estudiante');
        }else{
            $('#t_mensaje').empty();
            t_cambiarFondoMensaje(0);
            $('#t_mensaje').append('Opciones');
        }
    }else{
        $('#t_mensaje').empty();
        t_cambiarFondoMensaje(0);
        $('#t_mensaje').append('Opciones');
    }
}

$('#t_parentesco').on('change',function(){
    t_validarParentesco();
});

$('#t_paterno').on('keyup',function(){
    var parentesco = $('#t_parentesco').val();
    if(parentesco == 1){
        t_validarParentesco();
    }
});

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
    alert('Tenga en cuenta que al seleccionar la opcion "No", se eliminaran los datos del padre o tutor al momento de guardar la información.');
    
});