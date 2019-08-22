$(function () {
    $('[data-toggle="tooltip"]').tooltip();
		$('[data-toggle="popover"]').popover();
})

/* INICIALIZAR VARIABLES*/

var map;
var myLatLng;
var marker;
var contMarker = 1;
var infobox;

var image = L.icon({
    iconUrl: '../../images/marker23.png',
    iconSize: [40, 40]
});

var image2 = L.icon({
    iconUrl: '../../images/marker24.png',
    iconSize: [40, 40]
});

var contentString;
var latitud = -16.50666;
var longitud = -68.12793;
var latitudPreview;
var longitudPreview;
var codigoEdificio;

var tipoBusqueda;
var codigo;
var ue;
var ues = '';

var idComunidadNext = 'Ninguno';
var idComunidad = 'Ninguno';
var actualizarGeo = false;
var zoom = 6;
var coordenadas;

/* FUNCIONES PARA MANIPULAR EL MENU */

function mostrarMenu(){
	$('.menu').fadeIn(400);
	$('.btn-menu').fadeOut(300);
}
function ocultarMenu(){
	$('.menu').fadeOut(400);
	$('.btn-menu').fadeIn(500);
}

// CUANDO SE CAMBIA OPCION DE BUSQUEDA
function limpiarDatos(){
	if(marker){
		// marker.destroy();
		map.removeLayer(marker);
		marker = null;
	}
	$('#result').empty();
	$('#detalle').empty();
}

$( ".nav-item" ).click(function() {
		limpiarDatos();
		$('#sie').val('');
		$('#institucion').val('');
		$('#edificio').val('');

});

/* INICIALIZAR MAPA */

function initMap() {

	$('#map').empty();

	// CARGAMOS LAS CAPAS - opcion https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png
	var openstreet = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
		satelital = L.tileLayer('https://api.mapbox.com/v4/mapbox.satellite/{z}/{x}/{y}.jpg90?access_token=pk.eyJ1Ijoic2lnZWUiLCJhIjoiY2p5ZnZsODM3MGFrMjNtbmpmdHpnOW9xbiJ9.p4csjOmfBTl1Yun4PCeXNg');
		// arcgis = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}');

	map = L.map('map', {
	    center: [-16.50666, -68.12793],
	    zoom: 6,
	    minZoom: 3,
	    layers: [openstreet]
	});

	var baseMaps = {
	    "Mapa": openstreet,
	    "Satélite": satelital
	    // "Satélite-2": arcgis
	};

	L.control.layers(baseMaps).addTo(map);

}

initMap();

function validar(){
	var latitud = $('#latitud').val();
	var longitud = $('#longitud').val();
	if(!confirm('Las coordenadas que se registrarán son:\n \n Latitud: '+latitud+'\n Longitud: '+longitud+'\n \nEsta seguro de guardar los cambios?')){
		return false;
	}
}

/**
 * VALIDACION DE TIPOS DE DATOS
 */
$('#sie').numeric();
$('#edificio').numeric();

/* BUSQUEDA POR SIE */
$('#sie').on('keyup', function(){
	if(this.value.length < 8){
		limpiarDatos();
	}
});

$('#sie').autocomplete({
    minLength: 8,
    source: function(request, response){
        $.ajax({
            url: Routing.generate('sie_gis_searching',{'sie':request.term, 'tipo':'sie'}),
            dataType: "json",
            beforeSend: function(){
            	$('#result').empty();
            	$('#detalle').empty();
            	$('#result').append('<div class="loading" style="text-align:center; padding:20px 0 20px 0; color:#5DABFB; font-size:13px;"><img src="../../img/loading.gif" width="20px"/> <br> Cargando... </div>');
            },
            success: function(data){
            	tipoBusqueda = 'sie';
        		$('#result').empty();
        		$('#detalle').empty();
        		if (data.length === 0) {
        			$('#result').append('<li> <i class="fa fa-info-circle"></i> No hay resultados</li>');
        		}else{
            	    response(
            	        $.each(data, function(i, value){
            	            $('#result').append('<a href="#" onclick="seleccionarUnidad('+i+');"><li> <i class="fa fa-home" style="font-size:1.5em; float:left;"></i> <div style="margin-left:30px; font-size:0.8em"><div>SIE: <b style="color:#299AFF">'+value.sie+'</b> <small style="float:right">'+value.departamento+'</small> </div><div> I.E.: '+value.institucioneducativa+'</div></div></li></a>');
            	        }));
        		}
            },
            error: function(data){
            	$('#result').empty();
            	$('#result').append('<div class="alert-danger"><small> <i class="fa fa-warning"></i> No se puede completar la petición. </small> </div>');
            }		        	
        });
    }
});

/* BUSQUEDA POR NOMBRE DE INSTITUCION */

$('#institucion').on('keyup', function(){
	if(this.value == ""){
		limpiarDatos();
	}
});

$('#institucion').autocomplete({
    minLength: 3,
    source: function(request, response){
        $.ajax({
            url: Routing.generate('sie_gis_searching',{'institucion':request.term,'tipo':'nombre'}),
            dataType: "json",
            beforeSend: function(){
            	$('#result').empty();
            	$('#detalle').empty();
            	$('#result').append('<div class="loading" style="text-align:center; padding:20px 0 20px 0; color:#5DABFB; font-size:13px;"><img src="../../img/loading.gif" width="20px"/> <br> Cargando... </div>');
            },
            success: function(data){
            	tipoBusqueda = 'nombre';
        		$('#result').empty();
        		$('#detalle').empty();
        		if (data.length === 0) {
        			$('#result').append('<li> <i class="fa fa-info-circle"></i> No hay resultados</li>');
        		}else{
        			//$('#result').append('<small style="text-align:right">'+  +' resultados</small>');
            	    response(
            	        $.each(data, function(i, value){
            	            $('#result').append('<a href="#"><li onclick="seleccionarUnidad('+value.sie+');" data-container="body" data-toggle="popover" data-placement="right" data-content="Zona: '+value.zona+' Dirección: '+value.direccion+'" data-trigger="hover"><i class="fa fa-home" style="font-size:1.5em; float:left;"></i> <div style="margin-left:30px; font-size:0.8em"><div>SIE: <b style="color:#299AFF">'+value.sie+'</b> <small style="float:right">'+value.departamento+'</small> </div><div> I.E.: '+value.institucioneducativa+'</div></div></li></a>');
            	        }));
        		}

        		$('[data-toggle="popover"]').popover();
            },
            error: function(data){
            	$('#result').empty();
            	$('#result').append('<div class="alert-danger"><small> <i class="fa fa-warning"></i> No se puede completar la petición. </small> </div>');
            }
        });
    }
});

/* BUSQUEDA POR CODIGO DE EDIFICIO */
$('#edificio').on('keyup', function(){
	if(this.value.length < 8){
		limpiarDatos();
	}
});

$('#edificio').autocomplete({
    minLength: 8,
    source: function(request, response){
        $.ajax({
            url: Routing.generate('sie_gis_searching',{'edificio':request.term, 'tipo':'edificio'}),
            dataType: "json",
            beforeSend: function(){
            	$('#result').empty();
            	$('#detalle').empty();
            	$('#result').append('<div class="loading" style="text-align:center; padding:20px 0 20px 0; color:#5DABFB; font-size:13px;"><img src="../../img/loading.gif" width="20px"/> <br> Cargando... </div>');
            },
            success: function(data){
            	tipoBusqueda = 'edificio';
        		$('#result').empty();
        		$('#detalle').empty();
        		if (data.length === 0) {
        			$('#result').append('<li> <i class="fa fa-info-circle"></i> No hay resultados</li>');
        		}else{
            	    response(
            	        $.each(data, function(i, value){
            	            $('#result').append('<a href="#" onclick="seleccionarEdificio('+i+');"><li> <i class="fa fa-home" style="font-size:1.5em; float:left;"></i> <div style="margin-left:30px; font-size:0.8em"><div>COD. EDIFICIO: <b style="color:#299AFF">'+value.edificio+'</b> <small style="float:right">'+value.departamento+'</small> </div></div></li></a>');
            	        }));
        		}
            },
            error: function(data){
            	$('#result').empty();
            	$('#result').append('<div class="alert-danger"><small> <i class="fa fa-warning"></i> No se puede completar la petición. </small> </div>');
            }		        	
        });
    }
});

// SELECCIONAR UNIDAD EDUCATIVA Y EDIFICIO EDUCATIVO
function seleccionarUnidad(i){
	$.ajax({
		type:'post',
		url: Routing.generate('sie_gis_seleccionar_unidad',{'ie':i}),
		beforeSend: function(){
			$('#detalle').empty();
			$('#detalle').append('<div style="text-align:center; padding:20px 0 20px 0; color:#5DABFB; font-size:13px;"><img src="../../img/loading.gif" width="20px"/> <br> Cargando... </div>');
		},
		success: function(data){

			ue = '';

			codigoEdificio = data.codEdificio;
			idComunidad = data.idComunidad;
			idComunidadNext = data.idComunidad;

			// latitud = data.latitud;
			// longitud = data.longitud;

			codigo = i;
			ue = data.institucioneducativa;

			$('#detalle').empty();
			if(marker){
				map.removeLayer(marker);
				marker = null;
			}
			if(data.coordenadas == true){

			    coordenadas = L.latLng(data.latitud, data.longitud);
			    
			    marker = L.marker(coordenadas, {
			    	title: 'Edificio educativo',
			    	draggable: false,
			    	icon: image
			    });
			    map.addLayer(marker);
			    map.setView(coordenadas);

			    contentString = '<div>Código SIE</div>'+
								'<b>'+data.id+'</b>'+
								'<div>Institución Educativa</div>'+
								'<b>'+data.institucioneducativa+'</b>';
			    marker.bindPopup(contentString, {className: 'popup'}).openPopup();
			}

			// map.setCenter({latitud, longitud} ,6);

			$('#detalle').append('<h6 style="padding-top:10px"><i class="fa fa-file-text-o"></i> Información</h6>'+
								  '<div><small>Código de Edificio</small></div>'+
								  '<div><b>'+data.codEdificio+'</b></div>'+
								  '<div><small>Zona</small></div>'+
								  '<div><b>'+data.zona+'</b></div>'+
								  '<div><small>Dirección</small></div>'+
								  '<div><b>'+data.direccion+'</b></div>'+
								  '<hr>'+
							      '<div><small>Código SIE</small></div>'+
								  '<div><b>'+data.id+'</b></div>'+
								  '<div><small>Institución Educativa</small></div>'+
								  '<div><b>'+data.institucioneducativa+'</b></div>'+
								  '<hr>'+
								  '<h6><i class="fa fa-map-o"></i> Ubicación Geográfica</h6>'+
								  '<div><small>Departamento</small></div>'+
								  '<div class="jupper"><b>'+data.departamento+'</b></div>'+
								  '<div><small>Provincia</small></div>'+
								  '<div class="jupper"><b>'+data.provincia+'</b></div>'+
								  '<div><small>Municipio</small></div>'+
								  '<div class="jupper"><b>'+data.municipio+'</b></div>'+
								  '<div><small>Comunidad</small></div>'+
								  '<div class="jupper"><b>'+data.comunidad+'</b></div>'+
								  '<div class="text-right" id="actGeografica"></div>'+
								  '<hr>'+
								  '<h6> <i class="fa fa-map-marker"></i> Ubicación Georeferencial</h6>'+
								  '<div id="validacionMsg"></div>'+
								  '<div><small>Latitud</small></div>'+
								  '<div><b>'+data.latitud+'</b></div>'+
								  '<div><small>Longitud</small></div>'+
								  '<div><b>'+data.longitud+'</b></div>'+
								  '<div class="text-right" id="actGeoreferencial"></div>'
								  );

			if(data.actualizar == true && data.validacion != 1){

				$('#validacionMsg').empty();
				$('#validacionMsg').append('<div style="color:#888888; margin-bottom:5px; padding:2px; border:1px solid #96BDE7" class="text-center"><small>Estado: '+data.validacionMsg+'</small>');

				$('#actGeografica').append('<div style="color:#888888; margin-bottom:5px;" class="text-center"><small>Última fecha de modificación: '+data.fechaModificacionLocalizacion+'</small></div>');
				$('#actGeografica').append('<button class="btn btn-success btn-sm btn-editar" onclick="actualizarGeografica('+data.codEdificio+');" data-container="body" data-toggle="popover" data-placement="top" data-content="Editar Ubicación Geográfica" data-trigger="hover">Editar</button>');

				$('#actGeoreferencial').append('<div style="color:#888888; margin-bottom:5px;" class="text-center"><small>Última fecha de modificación: '+data.fechaModificacionCoordenada+'</small></div>');
				if(data.municipio == 'Ninguno'){
					$('#actGeoreferencial').append('<button class="btn btn-success btn-sm" disabled="disabled">Editar</button>');
				}else{
					$('#actGeoreferencial').append('<button class="btn btn-success btn-sm btn-editar" onclick="actualizarGeoreferencial('+data.codEdificio+', false);" data-container="body" data-toggle="popover" data-placement="top" data-content="Editar Ubicación Georeferencial" data-trigger="hover">Editar</button>');
				}
			}
			if(data.actualizar == true && data.municipio != 'Ninguno' && data.latitud != 'Ninguno'){
				$('#validacionMsg').empty();
				$('#validacionMsg').append('<div style="color:#888888; margin-bottom:5px; padding:2px; border:1px solid #96BDE7" class="text-center"><small>Estado: '+data.validacionMsg+'</small>');

				var ruta = Routing.generate('download_reporte_gis', {'codEdificio': data.codEdificio});
				if (data.validacion == 1) {
					$('#detalle').append('<hr><a href="'+ruta+'" class="btn btn-info btn-block" data-container="body" data-toggle="popover" data-placement="top" data-content="Imprimir reporte" data-trigger="hover"> <i class="fa fa-print"></i> Reporte</a>');
				} else {
					$('#detalle').append('<hr><a href="'+ruta+'" class="btn btn-info btn-block" onclick="return imprimirReporte('+data.codEdificio+');" data-container="body" data-toggle="popover" data-placement="top" data-content="Imprimir reporte" data-trigger="hover"> <i class="fa fa-print"></i> Reporte</a>');
				}
			}

			$('[data-toggle="popover"]').popover();
		},
		error: function(data){
			$('#detalle').empty();
			$('#detalle').append('<div class="alert alert-danger">Error</div>')
		}
	});
}

function seleccionarEdificio(i){
	$.ajax({
		type:'post',
		url: Routing.generate('sie_gis_seleccionar_edificio',{'edificio':i}),
		beforeSend: function(){
			$('#detalle').empty();
			$('#detalle').append('<div style="text-align:center; padding:20px 0 20px 0; color:#5DABFB; font-size:13px;"><img src="../../img/loading.gif" width="20px"/> <br> Cargando... </div>');
		},
		success: function(data){
			codigoEdificio = data.codEdificio;
			idComunidad = data.idComunidad;
			idComunidadNext = data.idComunidad;
			codigo = i;
			$('#detalle').empty();

			if(marker){
				map.removeLayer(marker);
				marker = null;
			}

			if(data.coordenadas == true){
				// latitud = data.latitud;
				// longitud = data.longitud;

			    coordenadas = L.latLng(data.latitud, data.longitud);
			    
			    marker = L.marker(coordenadas, {
			    	title: 'Edificio educativo',
			    	draggable: false,
			    	icon: image
			    });
			    map.addLayer(marker);
			    map.setView(coordenadas);

			    ues = '';
	    		$.each(data.unidadesEducativas, function(i, value){
	                ues = ues + '<li>'+i+' - '+value.institucioneducativa + '</li>';
	            });

			    contentString = '<div>Código de Edificio</div>'+
								'<b>'+data.codEdificio+'</b>'+
								'<div>Unidades educativas</div>'+
								'<ul>'+ ues +'</ul>';
			    marker.bindPopup(contentString, {className: 'popup'}).openPopup();
			}

			$('#detalle').append('<h6 style="padding-top:10px"><i class="fa fa-file-text-o"></i> Información</h6>'+
								  '<div><small>Código de Edificio</small></div>'+
								  '<div><b>'+data.codEdificio+'</b></div>'+
								  '<div><small>Zona</small></div>'+
								  '<div><b>'+data.zona+'</b></div>'+
								  '<div><small>Dirección</small></div>'+
								  '<div><b>'+data.direccion+'</b></div>'+
								  '<hr>'+
								  '<h6><i class="fa fa-map-o"></i> Ubicación Geográfica</h6>'+
								  '<div><small>Departamento</small></div>'+
								  '<div class="jupper"><b>'+data.departamento+'</b></div>'+
								  '<div><small>Provincia</small></div>'+
								  '<div class="jupper"><b>'+data.provincia+'</b></div>'+
								  '<div><small>Municipio</small></div>'+
								  '<div class="jupper"><b>'+data.municipio+'</b></div>'+
								  '<div><small>Comunidad</small></div>'+
								  '<div class="jupper"><b>'+data.comunidad+'</b></div>'+
								  '<div class="text-right" id="actGeografica"></div>'+
								  '<hr>'+
								  '<h6> <i class="fa fa-map-marker"></i> Ubicación Georeferencial</h6>'+
								  '<div id="validacionMsg"></div>'+
								  '<div><small>Latitud</small></div>'+
								  '<div><b>'+data.latitud+'</b></div>'+
								  '<div><small>Longitud</small></div>'+
								  '<div><b>'+data.longitud+'</b></div>'+
								  '<div class="text-right" id="actGeoreferencial"></div>'+
								  '<hr>'+
								  '<h6>Unidades Educativas que operan en el Edificio Educativo</h6>'
								  );

			if(data.actualizar == true && data.validacion != 1){
				$('#validacionMsg').empty();
				$('#validacionMsg').append('<div style="color:#888888; margin-bottom:5px; padding:2px; border:1px solid #96BDE7" class="text-center"><small>Estado: '+data.validacionMsg+'</small>');

				$('#actGeografica').append('<div style="color:#888888; margin-bottom:5px;" class="text-center"><small>Última fecha de modificación: '+data.fechaModificacionLocalizacion+'</small></div>');
				$('#actGeografica').append('<button class="btn btn-success btn-sm btn-editar" onclick="actualizarGeografica('+data.codEdificio+');" data-container="body" data-toggle="popover" data-placement="top" data-content="Editar Ubicación Geográfica" data-trigger="hover">Editar</button>');

				$('#actGeoreferencial').append('<div style="color:#888888; margin-bottom:5px;" class="text-center"><small>Última fecha de modificación: '+data.fechaModificacionCoordenada+'</small></div>');
				if(data.municipio == 'Ninguno'){
					$('#actGeoreferencial').append('<button class="btn btn-success btn-sm" disabled="disabled">Editar</button>');
				}else{
					$('#actGeoreferencial').append('<button class="btn btn-success btn-sm btn-editar" onclick="actualizarGeoreferencial('+data.codEdificio+', false);" data-container="body" data-toggle="popover" data-placement="top" data-content="Editar Ubicación Georeferencial" data-trigger="hover">Editar</button>');
				}
			}

			$('#detalle').append('<small><table class="table table-sm table-bordered"><thead><tr class="table-info">'+
								'<th>SIE</th>'+
								'<th>Institución Educativa</th>'+
								'</tr></thead><tbody id="listaues"></tbody></table></small>'
								);
			$.each(data.unidadesEducativas, function(i, value){
	            $('#listaues').append('<tr><td>'+i+'</td><td>'+value.institucioneducativa+'</td></tr>');
	        });

	        if(data.actualizar == true && data.municipio != 'Ninguno' && data.latitud != 'Ninguno'){
	        	$('#validacionMsg').empty();
				$('#validacionMsg').append('<div style="color:#888888; margin-bottom:5px; padding:2px; border:1px solid #96BDE7" class="text-center"><small>Estado: '+data.validacionMsg+'</small>');

				var ruta = Routing.generate('download_reporte_gis', {'codEdificio': data.codEdificio});

				if (data.validacion == 1) {
					$('#detalle').append('<hr><a href="'+ruta+'" class="btn btn-info btn-block" data-container="body" data-toggle="popover" data-placement="top" data-content="Imprimir reporte" data-trigger="hover"> <i class="fa fa-print"></i> Reporte</a>');
				} else {
					$('#detalle').append('<hr><a href="'+ruta+'" class="btn btn-info btn-block" onclick="return imprimirReporte('+data.codEdificio+');" data-container="body" data-toggle="popover" data-placement="top" data-content="Imprimir reporte" data-trigger="hover"> <i class="fa fa-print"></i> Reporte</a>');
				}
				
			}

			$('[data-toggle="popover"]').popover();

		},
		error: function(data){
			$('#detalle').empty();
			$('#detalle').append('<div class="alert alert-danger">Error</div>')
		}
	});
}

// ACTUALIZAR UBICACION GEOGRAFICA
function actualizarGeografica(codEdificio){
	$.ajax({
		type: 'post',
		url: Routing.generate('sie_gis_datos_edit_geografica', {'codEdificio':codEdificio}),
		beforeSend: function(){

		},
		success: function(data){
			$('#modal').empty();
			$('#modal').append(data);
			$('#modal').modal('show');
		},
		error: function(data){

		}
	});
}

// ACTUALIZAR UBICACION GEOREFERENCIAL - COORDENADAS
function actualizarGeoreferencial(codEdificio, actuGeo){
	$.ajax({
		type: 'post',
		url: Routing.generate('sie_gis_datos_edit_georeferencial', {'codEdificio':codEdificio}),
		beforeSend: function(){
			$('#numeroPaso').empty();
			$('#msgActualizacion').empty();
			if(actuGeo == true){
				$('#numeroPaso').append('Paso 2: ');
				$('#msgActualizacion').append('<div style="padding:15px; color:#0E434D; background-color:#C7E8EE; border-radius:5px;"><small><i class="fa fa-info-circle"></i> Para completar la edicion debe actualizar las coordenadas.</small></div>');
			}
		},
		success: function(data){
			/* Asignamos el valor de las coordenadas */

			actualizarGeo = actuGeo;

			$('.latitud').empty();
			$('.latitud').text(data.latitud);
			$('.longitud').empty();
			$('.longitud').text(data.longitud);
			$('.coordenadasCodEdificio').empty();
			$('.coordenadasCodEdificio').text(data.codEdificio);

			codigoEdificio = data.codEdificio;

			/* Mostramos el div de actualizacion de coordenadas y ocultamos el menu*/

			$('.coordenadas').fadeIn();
			$('.menu').fadeOut(400);
			$('.btn-menu').fadeOut(400);

			/* Asignamos las variables */

			latitudPreview = data.latitud;
			longitudPreview = data.longitud;

			latitud = data.latitud;
			longitud = data.longitud;

		    /*  eliminamos el marcador previo */
		    if(marker){

		    	// marker.destroy();
		    	map.removeLayer(marker);
		    	marker = null;
		    }

		    var lat, lng;

		    zoom = map.getZoom();

		    if(latitudPreview == 'Ninguno' || longitudPreview == 'Ninguno'){
		    	
	    		lat = data.latitudMunicipio;
	    		lng = data.longitudMunicipio;

	    		$('.latitud').empty();
	    		$('.latitud').text(data.latitudMunicipio);
	    		$('.longitud').empty();
	    		$('.longitud').text(data.longitudMunicipio);
		    }else{

		    	lat = latitud;
		    	lng = longitud;
		    }
	    	
	    	/* Creamos un marcador */

	        coordenadas = L.latLng(lat, lng);
	        
	        marker = L.marker(coordenadas, {
	        	title: 'Edificio educativo',
	        	draggable: true,
	        	icon: image2
	        });
	        map.addLayer(marker);
	        map.setView(coordenadas);

	        contentString = '<div>Código Edificio</div>'+
		    				'<b>'+data.codEdificio+'</b>';

		    marker.bindPopup(contentString, {className: 'popup'});

			marker.on('drag', function(){
				$('.latitud').text(marker.getLatLng().lat);
		    	$('.longitud').text(marker.getLatLng().lng);
		    	latitud = marker.getLatLng().lat;
		    	longitud = marker.getLatLng().lng;
			});

			marker.on('dragend', function(){
				// $('.latitud').text(marker.getLatLng().lat);
		  		// $('.longitud').text(marker.getLatLng().lng);
			});

		},
		error: function(data){
			alert('error');
		}
	});
}

function ingresarCoordenadas(){
	$('#bLatitud').val('');
	$('#bLongitud').val('');
	$('#modal2').modal('show');
}

function buscarCoordenadas(){
	var bLatitud = $('#bLatitud').val();
	var bLongitud = $('#bLongitud').val();
	if(bLatitud != "" && bLongitud != ""){

		coordenadas = L.latLng(bLatitud, bLongitud);
		latitud = bLatitud;
		longitud = bLongitud;

		marker.setLatLng(coordenadas);
		map.setView(coordenadas);

		$('.latitud').empty();
		$('.longitud').empty();
		$('.latitud').text(bLatitud);
		$('.longitud').text(bLongitud);
		$('#modal2').modal('hide');
	}else{
		$.notifyClose();
		$.notify({
			icon: 'fa fa-check',
			message: 'Debe registrar las coordenadas'
		},{
			type: 'warning'
		});
	}
}

function cerrarGeoreferencial(i){

	idComunidadNext = idComunidad;
	actualizarGeo = false;
	$('.coordenadas').fadeOut(200);
	if(latitudPreview == 'Ninguno' || longitudPreview == 'Ninguno'){
		if(marker){
			map.removeLayer(marker);
			marker = null;
		}
	}else{
		if(i == 0){
			latitud = latitudPreview;
			longitud = longitudPreview;
		}

		coordenadas = L.latLng(latitud, longitud);

		marker.setLatLng(coordenadas);
		map.setView(coordenadas);

		marker.dragging.disable();
		marker.setIcon(image);

		if (tipoBusqueda == 'edificio'){
		    contentString = '<div>Código de Edificio</div>'+
							'<b>'+codigo+'</b>'+
							'<div>Unidades educativas</div>'+
							'<ul>'+ ues +'</ul>';
		    marker.bindPopup(contentString, {className: 'popup'}).openPopup();
		}else{
		    contentString = '<div>Código SIE</div>'+
							'<b>'+codigo+'</b>'+
							'<div>Institución Educativa</div>'+
							'<b>'+ue+'</b>';
		    marker.bindPopup(contentString, {className: 'popup'}).openPopup();
		}
	}
	
	mostrarMenu();
}

function updateGeoreferencial(){
	$.ajax({
		type: 'post',
		url: Routing.generate('sie_gis_datos_update_georeferencial',{'codEdificio' : codigoEdificio, 'latitud':marker.getLatLng().lat, 'longitud':marker.getLatLng().lng,'idComunidad': idComunidadNext, 'actualizarGeografica': actualizarGeo}),
		beforeSend: function(){
			console.log('guardando....');
			// latitud = marker.getLatLng().lat;
			// longitud = marker.getLatLng().lng;
			coordenadas = L.latLng(latitud,longitud);
			map.setView(coordenadas);
		},
		success: function(data){
			if (data.status == 200) {

				idComunidad = idComunidadNext;

    			console.log('Guardado');

    			if (tipoBusqueda == 'edificio'){
    				seleccionarEdificio(codigo);
    			}else{
    				seleccionarUnidad(codigo);
    			}
    			cerrarGeoreferencial(1);
    			$.notifyClose();
				$.notify({
					icon: 'fa fa-check',
					message: data.msg
				},{
					type: 'success'
				});
			} else {
    			$.notifyClose();
				$.notify({
					icon: 'fa fa-check',
					message: data.msg
				},{
					type: 'warning'
				});
			}
		},
		error: function(data){
			$.notifyClose();
			$.notify({
				icon: 'fa fa-warning',
				message: 'Ocurrio un error, actualización no realizada.'
			},{
				type: 'danger'
			});
		}
	});
}

var peticion1;
//$('#form_turno').on('change',function(){
function listarProvincias(idDpto) {
    if (peticion1 && peticion1.readyState != 4) {
        peticion1.abort();
    }

    peticion1 = $.ajax({
        type: 'get',
        url: Routing.generate('sie_gis_listar_provincias', {'dpto': idDpto}),
        beforeSend: function () {

        },
        success: function (data) {
            $('#form_provincia').empty();
            $('#form_municipio').empty();
            $('#form_comunidad').empty();
            $("#form_provincia").append('<option value="">Seleccionar...</option>');
            $("#form_municipio").append('<option value="">Seleccionar...</option>');
            $("#form_comunidad").append('<option value="">Seleccionar...</option>');
            $.each(data.listaprovincias, function (i, value) {
                $("#form_provincia").append('<option value="' + i + '">' + value + '</option>');
            });
        }
    });
}

var peticion2;
//$('#form_turno').on('change',function(){
function listarMunicipios(idProv) {
    if (peticion2 && peticion2.readyState != 4) {
        peticion2.abort();
    }

    peticion2 = $.ajax({
        type: 'get',
        url: Routing.generate('sie_gis_listar_municipios', {'prov': idProv}),
        beforeSend: function () {

        },
        success: function (data) {
            $('#form_municipio').empty();
            $('#form_comunidad').empty();
            $("#form_municipio").append('<option value="">Seleccionar...</option>');
            $("#form_comunidad").append('<option value="">Seleccionar...</option>');
            $.each(data.listamunicipios, function (i, value) {
                $("#form_municipio").append('<option value="' + i + '">' + value + '</option>');
            });
        }
    });
}

var peticion3;
//$('#form_turno').on('change',function(){
function listarComunidades(idMuni) {
    if (peticion3 && peticion3.readyState != 4) {
        peticion3.abort();
    }

    peticion3 = $.ajax({
        type: 'get',
        url: Routing.generate('sie_gis_listar_comunidades', {'mun': idMuni}),
        beforeSend: function () {

        },
        success: function (data) {
            $('#form_comunidad').empty();
            $("#form_comunidad").append('<option value="">Seleccionar...</option>');
            $.each(data.listacomunidades, function (i, value) {
                $("#form_comunidad").append('<option value="' + i + '">' + value + '</option>');
            });
        }
    });
}

function imprimirReporte(codEdificio){
	if (confirm('Al generar este reporte usted no podra realizar mas modificaciones de la Ubicación Geográfica y de la Ubicación Georeferencial, verifique la información.\n¿Esta seguro de generar el reporte? Presione aceptar para continuar.')) {
    	$.notifyClose();
		$.notify({
			icon: 'fa fa-info-circle',
			message: 'Generando Reporte...'
		},{
			type: 'info'
		});

		$('.btn-editar').css('display','none');
		$('#validacionMsg').empty();
		$('#validacionMsg').append('<div style="color:#888888; margin-bottom:5px; padding:2px; border:1px solid #96BDE7" class="text-center"><small>Estado: Coordenada validada en operativo</small>');
		return true;
	}

	return false;
}