var map;
var myLatLng;
var marker;
var image = L.icon({
    iconUrl: '/../../images/marker23.png',
    iconSize: [40, 40]
});
var latitud = -16.50626;
var longitud = -68.12794;

function initMap() {
    $('#map').empty();
    $('#form_latitud').val(latitud);
    $('#form_longitud').val(longitud);

    // CARGAMOS LAS CAPAS - opcion https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png
    var openstreet = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
        satelital = L.tileLayer('https://api.mapbox.com/v4/mapbox.satellite/{z}/{x}/{y}.jpg90?access_token=pk.eyJ1Ijoic2lnZWUiLCJhIjoiY2p5ZnZsODM3MGFrMjNtbmpmdHpnOW9xbiJ9.p4csjOmfBTl1Yun4PCeXNg');
        // arcgis = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}');

    map = L.map('map', {
        center: [latitud, longitud],
        zoom: 13,
        minZoom: 3,
        layers: [openstreet]
    });

    var baseMaps = {
        "Mapa": openstreet,
        "Satélite": satelital
        // "Satélite-2": arcgis
    };

    L.control.layers(baseMaps).addTo(map);

    coordenadas = L.latLng(latitud, longitud);
    marker = L.marker(coordenadas, {
        title: 'Edificio educativo',
        draggable: true,
        icon: image
    });
    map.addLayer(marker);
    map.setView(coordenadas);

    marker.on('drag', function(){
        $('#form_latitud').val(marker.getLatLng().lat);
        $('#form_longitud').val(marker.getLatLng().lng);
    });
}

function initMap2(lt,lg,div) {
    //alert(lt+lg+div);

    $('#'+div).empty();

    // CARGAMOS LAS CAPAS - opcion https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png
    var openstreet = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
        satelital = L.tileLayer('https://api.mapbox.com/v4/mapbox.satellite/{z}/{x}/{y}.jpg90?access_token=pk.eyJ1Ijoic2lnZWUiLCJhIjoiY2p5ZnZsODM3MGFrMjNtbmpmdHpnOW9xbiJ9.p4csjOmfBTl1Yun4PCeXNg');
        // arcgis = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}');

    map = L.map(div, {
        center: [lt, lg],
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

    coordenadas = L.latLng(lt, lg);
    marker = L.marker(coordenadas, {
        title: 'Edificio educativo',
        draggable: false,
        icon: image
    });
    map.addLayer(marker);
    map.setView(coordenadas);

}

//initMap();