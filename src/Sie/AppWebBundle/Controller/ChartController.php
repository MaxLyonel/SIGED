<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Vista controller.
 *
 */
class ChartController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }


    /**
     * Funcion que retorna el Reporte Grafico Donut 3d Chart - Higcharts
     * Jurlan
     * @param Request $entity
     * @return chart
     */
    public function chartDonut3d($entity,$titulo,$subTitulo,$nombreLabel,$contenedor) {

        $datosTemp = "";
        $subTotal = 0;
        foreach ($entity['dato'] as $key => $dato) {
            $porcentaje = 0;
            if ($key == 0){
                $subTotal = $dato['cantidad'];
            } else {
                $porcentaje = round(((100*$dato['cantidad'])/(($subTotal==0) ? 1: $subTotal)),1);
                $datosTemp = $datosTemp."{name: '".$dato['detalle']."', y: ".$porcentaje.", label: ".$dato['cantidad']."},";
            }
        }
        
        $pointLabel = $nombreLabel;

        $datos = "   
            var ".$contenedor."Load = function() {
                 $('#".$contenedor."').highcharts({
                    chart: {
                        type: 'pie',
                        options3d: {
                            enabled: true,
                            alpha: 45
                        }
                    },
                    //colors: ['#7cb5ec', '#434348', '#90ed7d', '#f7a35c', '#8085e9', '#f15c80', '#e4d354', '#2b908f', '#f45b5b', '#91e8e1'],
                    colors: ['#89B440', '#D7AF29', '#E98E25', '#F2774D', '#DB3F30', '#2C4853', '#688F9E', '#0F88B7', '#34B0AE', '#36B087'],
                    title: {
                        text: '".$titulo."'
                    },
                    subtitle: {
                        text: '".$subTitulo."'
                    },
                    plotOptions: {
                        pie: {
                            innerSize: 100,
                            depth: 45,
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.label:,.0f}</b> ({point.percentage:.1f}%)',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
                            },
                            showInLegend: true,
                        }
                    },
                    tooltip: {
                        shared: true,
                        useHTML: true,
                        headerFormat: '<span style=&#39;font-size:11px&#39;>{series.name}</span><br>',
                        pointFormat: '<span style=&#39;color:{point.color}&#39;>{point.name}</span>: <b>{point.label:,.0f} ".$pointLabel."</b> del total<br/>'
                    },
                    series: [{
                        name: '".$entity['tipo']."',
                        colorByPoint: true,
                        data: [".$datosTemp."]
                    }]
                });
            }
        ";   
        return $datos;
    }

    /**
     * Funcion que retorna el Reporte Grafico Pie Chart - Higcharts
     * Jurlan
     * @param Request $entity
     * @return chart
     */
    public function chartPie($entity,$titulo,$subTitulo,$nombreLabel,$contenedor) {
        
        $datosTemp = "";
        $subTotal = 0;
        foreach ($entity['dato'] as $key => $dato) {
            $porcentaje = 0;
            if ($key == 0){
                $subTotal = $dato['cantidad'];
            } else {
                $porcentaje = round(((100*$dato['cantidad'])/(($subTotal==0) ? 1: $subTotal)),1);
                $datosTemp = $datosTemp."{name: '".$dato['detalle']."', y: ".$porcentaje.", label: ".$dato['cantidad']."},";
            }
        }                   

        $datos = "   
            var ".$contenedor."Load = function() {
                 $('#".$contenedor."').highcharts({
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    colors: ['#0F88B7', '#34B0AE', '#36B087', '#89B440', '#D7AF29', '#E98E25', '#F2774D', '#DB3F30', '#2C4853', '#688F9E'],
                    title: {
                        text: '".$titulo."'
                    },
                    subtitle: {
                        text: '".$subTitulo."'
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.label:,.0f}</b> ({point.percentage:.1f}%)',
                                style: {
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                }
                            },
                            showInLegend: true,
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style=&#39;font-size:11px&#39;>{series.name}</span><br>',
                        pointFormat: '<span style=&#39;color:{point.color}&#39;>{point.name}</span>: <b>{point.label:,.0f} ".$nombreLabel."</b> del total<br/>'
                    },
                    series: [{
                        name: '".$entity['tipo']."',
                        colorByPoint: true,
                        data: [".$datosTemp."]
                    }]
                });
            }
        ";   
        return $datos;
    }

    /**
     * Funcion que retorna el Reporte Grafico Bar - Higcharts
     * Jurlan
     * @param Request $entity
     * @return chart
     */
    public function chartColumn($entity,$titulo,$subTitulo,$nombreLabel,$contenedor) {
        
        $datosTemp = "";
        $subTotal = 0;
        foreach ($entity['dato'] as $key => $dato) {
            $porcentaje = 0;
            if ($key == 0){
                $subTotal = $dato['cantidad'];
            } else {
                $porcentaje = round(((100*$dato['cantidad'])/(($subTotal==0) ? 1: $subTotal)),1);
                $datosTemp = $datosTemp."{name: '".$dato['detalle']."', y: ".$porcentaje.", label: ".$dato['cantidad']."},";
            }
        }   

        $pointLabel = $nombreLabel;

        $datos = "   
            var ".$contenedor."Load = function() {
                 $('#".$contenedor."').highcharts({
                    chart: {
                        type: 'column'
                    },
                    colors: ['#E98E25', '#F2774D', '#DB3F30', '#2C4853', '#688F9E', '#0F88B7', '#34B0AE', '#36B087', '#89B440', '#D7AF29'],
                    title: {
                        text: '".$titulo."'
                    },
                    subtitle: {
                        text: '".$subTitulo."'
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: {
                        title: {
                            text: ''
                        }

                    },
                    legend: {
                        enabled: false
                    },
                    plotOptions: {
                        series: {
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                format: '{point.label:,.0f} ({point.y:.1f}%)'
                            }
                        }
                    },        
                    tooltip: {
                        headerFormat: '<span style=&#39;font-size:11px&#39;>{series.name}</span><br>',
                        pointFormat: '<span style=&#39;color:{point.color}&#39;>{point.name}</span>: <b>{point.label:,.0f} ".$pointLabel." </b> del total<br/>'
                    },

                    series: [{
                        name: '".$entity['tipo']."',
                        colorByPoint: true,
                        data: [".$datosTemp."]
                    }]
                });
            }
        ";        
        return $datos;
    }

    /**
     * Funcion que retorna el Reporte Grafico Semi Pie Donut 3d - Higcharts
     * Jurlan
     * @param Request $entity
     * @return chart
     */
    public function chartSemiPieDonut3d($entity,$titulo,$subTitulo,$nombreLabel,$contenedor) {
        
        $datosTemp = "";
        $subTotal = 0;
        foreach ($entity['dato'] as $key => $dato) {
            $porcentaje = 0;
            if ($key == 0){
                $subTotal = $dato['cantidad'];
            } else {
                $porcentaje = round(((100*$dato['cantidad'])/(($subTotal==0) ? 1: $subTotal)),1);
                $datosTemp = $datosTemp."{name: '".$dato['detalle']."', y: ".$porcentaje.", label: ".$dato['cantidad']."},";
            }
        }   

        $pointLabel = $nombreLabel;

        $datos = "   
            var ".$contenedor."Load = function() {
                 $('#".$contenedor."').highcharts({
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: 0,
                        plotShadow: false
                    },
                    colors: ['#2C4853', '#688F9E', '#0F88B7', '#34B0AE', '#36B087', '#89B440', '#D7AF29', '#E98E25', '#F2774D', '#DB3F30'],
                    title: {
                        text: '".$titulo."',
                        align: 'center',
                    },
                    subtitle: {
                        text: '".$subTitulo."'
                    },
                    tooltip: {
                        headerFormat: '<span style=&#39;font-size:11px&#39;>{series.name}</span><br>',
                        pointFormat: '<span style=&#39;color:{point.color}&#39;>{point.name}</span>: <b>{point.label:,.0f} ".$pointLabel." </b> del total<br/>'
                    },
                    plotOptions: {
                        pie: {
                            dataLabels: {
                                enabled: true,
                                style: {
                                    fontWeight: 'bold',
                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                },
                                format: '{point.label:,.0f} ({point.y:.1f}%)'
                            },
                            startAngle: -90,
                            endAngle: 90,
                            center: ['50%', '75%'],
                            allowPointSelect: true,
                            cursor: 'pointer',
                            showInLegend: true,
                        },
                    },
                    series: [{
                        type: 'pie',
                        name: '".$entity['tipo']."',
                        innerSize: '50%',
                        data: [".$datosTemp."]
                    }]
                });
            }
        ";        
        return $datos;
    }


    public function chartDonutInformacionGeneralNivelGrado($entity,$titulo,$subTitulo,$tipoReporte,$contenedor) {
        //dump($entity);die;
        $aData = array();
        foreach ($entity['dato'] as $key => $dato) {
            if ($key != 0) {
                $aData[$dato['detalle']][$dato['subdetalle']] = $dato['cantidad'];
            }
        }
        $data = "";
        $categoria = "";
        $cantidad = 0;
        $subCategoria = "";
        $subCantidad = 0;
        $subPorcentaje = 0;
        $total = $entity['dato'][0]['cantidad'];
        $count = 1;
        foreach ($aData as $key => $nivel) {
            $nombre = $key;
            if ($categoria == "") {
                $categoria = "'".$key."'";
            } else {
                $categoria = $categoria . ",'" . $key . "'";
            }

            $cantidad = 0;
            foreach ($nivel as $key => $dato) {

                $cantidad = $cantidad + $dato;
                $porcentaje = round(($dato*100)/($total), 1);
          //      dump($porcentaje);die;
                if ($subCategoria == "") {
                    $subCategoria = "'" . $key . "'";
                } else {
                    $subCategoria = $subCategoria . ",'" . $key . "'";
                }
                if ($subPorcentaje == "") {
                    $subPorcentaje = $porcentaje;
                } else {
                    $subPorcentaje = $subPorcentaje . "," . $porcentaje;
                }
                if ($subCantidad == "") {
                    $subCantidad = $dato;
                } else {
                    $subCantidad = $subCantidad . "," . $dato;
                }
            }

            if ($data == "") {
                $data = "{
                            y: " . $cantidad . ", 
                            color: colors[".$count."], 
                            drilldown: {
                                name: '" . $nombre . "',
                                labels:[" . $subCategoria . "],
                                categories: [" . $subCantidad . "],
                                data: [" . $subPorcentaje . "]
                            }
                        }";
            } else {
                $data = $data.",{
                            y: " . $cantidad . ", 
                            color: colors[".$count."],  
                            drilldown: {
                                name: '" . $nombre . "',
                                labels:[" . $subCategoria . "],
                                categories: [" . $subCantidad . "],
                                data: [" . $subPorcentaje . "]
                            }
                        }";
            }
            $subCategoria = "";
            $subCantidad = "";
            $subPorcentaje = "";
            $count++;
        }

        $datos = " 
            function ".$contenedor."Load() {

                var colors = ['#0F88B7', '#34B0AE', '#36B087', '#89B440', '#D7AF29', '#E98E25', '#F2774D', '#DB3F30', '#2C4853', '#688F9E'],
                
                categories = [".$categoria."],            
                data = [".$data."],
                nivelData = [],
                gradoData = [],
                i,
                j,
                dataLen = data.length,
                drillDataLen,
                brightness;

                for (i = 0; i < dataLen; i += 1) {

                    nivelData.push({
                        name: categories[i],
                        y: data[i].y,
                        label: data[i].y,
                        color: data[i].color
                    });

                    drillDataLen = data[i].drilldown.data.length;
                    for (j = 0; j < drillDataLen; j += 1) {
                        brightness = 0.2 - (j / drillDataLen) / 5;
                        gradoData.push({
                            label: data[i].drilldown.categories[j],
                            y: data[i].drilldown.data[j],
                            name: data[i].drilldown.labels[j],
                            color: Highcharts.Color(data[i].color).brighten(brightness).get()
                        });
                    }
                }

                $('#".$contenedor."').highcharts({                    
                    chart: {
                        type: 'pie'
                    },
                    colors: ['#0F88B7', '#34B0AE', '#36B087', '#89B440', '#D7AF29', '#E98E25', '#F2774D', '#DB3F30', '#2C4853', '#688F9E'],
                    title: {
                        text: '".$titulo."'
                    },
                    subtitle: {
                        text: '".$subTitulo."'
                    },
                    yAxis: {
                        title: {
                            text: ''
                        }
                    },
                    plotOptions: {
                        pie: {
                            shadow: false,
                            center: ['50%', '50%']
                        }
                    },
                    tooltip: {
                        //valueSuffix: '%',
                        shared: true,
                        useHTML: true,
                        headerFormat: '<span style=&#39;font-size:11px&#39;>{series.name}</span><br>',
                        pointFormat: '<span style=&#39;color:{point.color}&#39;>{point.name}</span>: <b>{point.label:,.0f} Participantes</b> del total<br/>'
                    },
                    series: [{
                        name: 'Etapa/Acreditación',
                        data: nivelData,
                        size: '60%',
                        dataLabels: {
                            formatter: function () {
                                return this.y > 0 ? this.point.name : null;
                            },
                            color: '#ffffff',
                            distance: -30
                        }
                    }, {
                        name: 'Cantidad',
                        data: gradoData,
                        size: '80%',
                        innerSize: '60%',
                        dataLabels: {
                            formatter: function () {
                                return this.y > 0 ? '' + this.point.name + ' - ' + (this.point.label).toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.') + ' (' + this.y + '%)' : null;
                            }
                        }
                    }]

                });
            }
        ";
      //  dump($datos);die;
        return $datos;
    }


    /**
     * Busca el nombre de la entidad en funcion al tipo de rol
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaEntidadRol($codigo,$rol) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
                select lt.codigo as id, lt.lugar as nombre, 0 as rolActual, -16.2256989 as cordx, -68.0441409 as cordy from lugar_tipo as lt 
                where cast(lt.codigo as integer) = ".$codigo." and lugar_nivel_id = 0 and lugar_tipo_id = 0
            "); 


        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
            $queryEntidad = $em->getConnection()->prepare("
                select ie.id as id, 'U.E.: '|| cast(ie.id as varchar) ||' - '|| ie.institucioneducativa as nombre, ".$rol." as rolActual, coalesce(jg.cordx,-16.2256989) as cordx, coalesce(jg.cordy,-68.0441409) as cordy from institucioneducativa as ie
                left join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                where ie.id = ".$codigo."
            "); 
            /*
            $queryEntidad = $em->getConnection()->prepare("
                    select ie.id, ie.id||' - '||ie.institucioneducativa as nombre from usuario_rol as ur
                    inner join usuario as u on u.id = ur.usuario_id
                    inner join persona as p on p.id = u.persona_id
                    inner join maestro_inscripcion as mi on mi.persona_id = p.id
                    inner join institucioneducativa as ie on ie.id = mi.institucioneducativa_id
                    where ur.usuario_id = ".$usuario." and ur.rol_tipo_id = ".$rol ." and mi.gestion_tipo_id = ".$gestionActual." and mi.es_vigente_administrativo = 1
                ");      
            */  
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("
                select lt.codigo as id, 'DISTRITO: '||lt.lugar as nombre, ".$rol." as rolActual, coalesce(jg.cordx,-16.2256989) as cordx, coalesce(jg.cordy,-68.0441409) as cordy from lugar_tipo as lt 
                left join jurisdiccion_geografica as jg on jg.lugar_tipo_id_distrito = lt.id
                where cast(lt.codigo as integer) = ".$codigo." and lugar_nivel_id = 7 and lt.codigo not in ('1000','2000','3000','4000','5000','6000','7000','8000','9000')
            "); 
            /*
            $queryEntidad = $em->getConnection()->prepare("
                    select lt.id, lt.lugar as nombre from usuario as u 
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
                    where u.id = ".$usuario." and rol_tipo_id = ".$rol."
                ");     
            */
        }  

        if($rol == 7) // Tecnico Departamental
        {   
            $queryEntidad = $em->getConnection()->prepare("
                select lt.codigo as id, 'Departamento: '||lt.lugar as nombre, ".$rol." as rolActual, -16.2256989 as cordx, -68.0441409 as cordy from lugar_tipo as lt 
                where cast(lt.codigo as integer) = ".$codigo." and lugar_nivel_id = 1
            "); 
            /*
            $queryEntidad = $em->getConnection()->prepare("
                    select lt.codigo as id, lt.lugar as nombre from usuario as u 
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
                    where u.id = ".$usuario." and rol_tipo_id = ".$rol."
                ");    
            */ 
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $queryEntidad = $em->getConnection()->prepare("
                select lt.codigo as id, lt.lugar as nombre, ".$rol." as rolActual, coalesce(jg.cordx,-16.2256989) as cordx, coalesce(jg.cordy,-68.0441409) as cordy from lugar_tipo as lt 
                left join jurisdiccion_geografica as jg on jg.lugar_tipo_id_localidad = lt.id
                where cast(lt.codigo as integer) = ".$codigo." and lugar_nivel_id = 0 and lugar_tipo_id = 0
            ");   
            /*
            $queryEntidad = $em->getConnection()->prepare("
                    select lt.codigo as id, lt.lugar as nombre from usuario as u 
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
                    where u.id = ".$usuario." and rol_tipo_id = ".$rol ."
                ");    
            */
        } 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 
        if (count($objEntidad)>0){
            return $objEntidad[0];
        } else {
            return '';
        }
    }

}
