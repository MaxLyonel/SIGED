<?php

namespace Sie\DiplomaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Date;

/**
 * Vista controller.
 *
 */
class ReporteController extends Controller {

    public function defaultAction() {
        
    } 
    
    /**
     * Reporte Grafico y Detale de la consolidacion por departamento - Educacion Regular
     * Jurlan
     */
    public function nacionalDiplomaImpresoAction(Request $request) {
        if ($request->isMethod('POST')) {
            $gestion = $request->get('gestion');
        } else {
            $gestion = date_format(new \DateTime(),"Y");
        }
        $entity = $this->diplomaBachillerNivelNacional($gestion);
        $datos = $this->chartMapCantidades($entity,$gestion);        
        $entityGestion = $this->gestionesEntidad();
        return $this->render('SieDiplomaBundle:Reporte:nacional.html.twig'
                , array('periodo'=>$gestion
                , 'gestiones'=>$entityGestion
                , 'dato' => $datos
                , 'entity' => $entity
                , 'nivel' => 'Nacional'
                , 'nivelnext' => 'departamental'
            ));
    } 
    
    /**
     * Reporte Grafico y Detale de la consolidacion por distrito - Educacion Regular
     * Jurlan
     */
    public function departamentalDiplomaImpresoAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $id = $request->get('id');
            $nombre = $request->get('nombre');
            $gestion = $request->get('periodo');            
            $entity = $this->diplomaBachillerNivelDepartamental($id,$gestion);            
            $datos = $this->chartColumnCantidades($entity,$gestion);          
            $entityGestion = $this->gestionesEntidad();  
            return $this->render('SieDiplomaBundle:Reporte:nacional.html.twig'
                , array('periodo'=>$gestion
                , 'gestiones'=>$entityGestion
                , 'entity' => $entity
                , 'nivel' => 'Departamental'
                , 'nivelnext' => 'distrital'
                , 'nombre' => $nombre
            ));
        } else {
            return $this->redirectToRoute('sie_diploma_tramite_reporte_nacional_impreso');
        }
    } 
    
    /**
     * Reporte Grafico y Detale de la consolidacion por ue - Educacion Regular
     * Jurlan
     */
    public function distritalDiplomaImpresoAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $id = $request->get('id');
            $nombre = $request->get('nombre');
            $gestion = $request->get('periodo');            
            $entity = $this->diplomaBachillerNivelDistrital($id,$gestion);            
            $datos = $this->chartColumnCantidades($entity,$gestion);            
            $entityGestion = $this->gestionesEntidad();
            return $this->render('SieDiplomaBundle:Reporte:nacional.html.twig'
                , array('periodo'=>$gestion
                , 'gestiones'=>$entityGestion
                , 'entity' => $entity
                , 'nivel' => 'Distrital'
                , 'nivelnext' => 'institucional'
                , 'nombre' => $nombre
            ));
        } else {
            return $this->redirectToRoute('sie_diploma_tramite_reporte_nacional_impreso');
        }
    } 
    
    /**
     * Reporte Grafico y Detale de la consolidacion por estudiante - Educacion Regular
     * Jurlan
     */
    public function institucionalDiplomaImpresoAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $id = $request->get('id');
            $nombre = $request->get('nombre');
            $gestion = $request->get('periodo');            
            $entity = $this->diplomaBachillerNivelInstitucional($id,$gestion);            
            $datos = $this->chartColumnCantidades($entity,$gestion);            
            $entityGestion = $this->gestionesEntidad();
            return $this->render('SieDiplomaBundle:Reporte:nacional.html.twig'
                , array('periodo'=>$gestion
                , 'gestiones'=>$entityGestion
                , 'entity' => $entity
                , 'nivel' => 'Institucional'
                , 'nivelnext' => 'personal'
                , 'nombre' => $nombre
            ));
        } else {
            return $this->redirectToRoute('sie_diploma_tramite_reporte_nacional_impreso');
        }
    } 

    
    
    /**
     * Funcion que retorna el Reporte Grafico Map Jquery de tipo Map - Diplomas de Bachiller
     * Jurlan
     * @param Request $entity
     * @return repr
     */
    public function chartColumnCantidades($entity,$gestion) {
        $total = 0;
        $entTemp = '';
        $datosTemp1 = '';
        $datosTemp2 = '';
        $cantidad1 = 0;
        $cantidad2 = 0;
        $abreviatura = ''; 
        for ($i = 0; $i < count($entity); $i++) {
            $cantidad1 = $entity[$i]['cant1'];
            $cantidad2 = $entity[$i]['cant2'];
            $subtotal = $entity[$i]['total'];
            $total = $total + $subtotal;
            $nombre = $entity[$i]['nombre'];
            $abreviatura = $entity[$i]['abreviatura'];
            if ($i == 0){
                $entTemp = "'".$nombre."'";
                $datosTemp1 = $cantidad1;
            } else {
                $entTemp = ",'".$nombre."'";
                $datosTemp1 = $datosTemp1.",".$cantidad1;
            }
        }    
        
        $datosTemp1 = "{name: 'Regular', data: [".$datosTemp1."]}";
        
        for ($i = 0; $i < count($entity); $i++) {
            $cantidad1 = $entity[$i]['cant1'];
            $cantidad2 = $entity[$i]['cant2'];
            $subtotal = $entity[$i]['total'];
            $total = $total + $subtotal;
            $nombre = $entity[$i]['nombre'];
            $abreviatura = $entity[$i]['abreviatura'];
            if ($i == 0){
                $datosTemp2 = $cantidad2;
            } else {
                $datosTemp2 = $datosTemp2.",".$cantidad2;
            }
        } 
        
        $datosTemp2 = ",{name: 'Alternativa', data: [".$datosTemp2."]}";
        
        //$datos1 = "var data1 = [".$datosTemp1."];";
        
        $datos1 = "var entidades1 = [".$entTemp."];";
        $datos1 = $datos1."var data1 = [".$datosTemp1.$datosTemp2."];";
        
        $datos1 = $datos1."   
            $('#chartContainer1').highcharts({
                chart: {
                    type: 'column',
                    options3d: {
                        enabled: true,
                        alpha: 0,
                        beta: 0,
                        viewDistance: 25,
                        depth: 40
                    },
                    marginTop: 80,
                    marginRight: 40
                },

                title : {
                    text : 'Diplomas de Bachiller'
                },

                subtitle : {
                    text : 'Gestion ".$gestion."'
                },

                xAxis: {
                    categories: entidades1
                },

                yAxis: {
                    allowDecimals: false,
                    min: 0,
                    title: {
                        text: 'Numero de Diplomas de Bachiller'
                    }
                },

                tooltip: {
                    headerFormat: '<b>{point.key}</b><br>',
                    pointFormat: '{series.name}: {point.y} / {point.stackTotal}'
                },

                plotOptions: {
                    column: {
                        stacking: 'normal',
                        depth: 40
                    }
                },

                series: data1
            });
    
            
        ";
        
        $datos = $datos1;
        return $datos;
    }

    
    /**
     * Funcion que retorna el Reporte Grafico Map Jquery de tipo Map - Diplomas de Bachiller
     * Jurlan
     * @param Request $entity
     * @return repr
     */
    public function chartMapCantidades($entity,$gestion) {
        $total = 0;
        $datosTemp = '';
        $cantidad1 = 0;
        $cantidad2 = 0;
        $abreviatura = '';   
        $datosTemp1 = '';  
        $datosTemp2 = '';
        for ($i = 0; $i < count($entity); $i++) {
            $cantidad1 = $entity[$i]['cant1'];
            $cantidad2 = $entity[$i]['cant2'];
            $subtotal = $entity[$i]['total'];
            $total = $total + $subtotal;
            $nombre = $entity[$i]['nombre'];
            $abreviatura = $entity[$i]['abreviatura'];
            if ($i == 0){
                $datosTemp1 = $datosTemp1."{'hc-key': 'bo-".strtolower($abreviatura)."', 'value': ".$cantidad1."}";
                $datosTemp2 = $datosTemp2."{'hc-key': 'bo-".strtolower($abreviatura)."', 'value': ".$cantidad2."}";
            } else {
                $datosTemp1 = $datosTemp1.",{'hc-key': 'bo-".strtolower($abreviatura)."', 'value': ".$cantidad1."}";
                $datosTemp2 = $datosTemp2.",{'hc-key': 'bo-".strtolower($abreviatura)."', 'value': ".$cantidad2."}";
            }
        }     
        
        $datos1 = "var data1 = [".$datosTemp1."];";
        
        $datos1 = $datos1."    
            $('#chartContainer1').highcharts('Map', {

                title : {
                    text : 'Diplomas de Bachiller - Educación Regular'
                },

                subtitle : {
                    text : 'Gestion ".$gestion."'
                },

                mapNavigation: {
                    enabled: true,
                    buttonOptions: {
                        verticalAlign: 'bottom'
                    }
                },

                colorAxis: {
                    min: 0
                },

                series : [{
                    data : data1,
                    mapData: Highcharts.maps['countries/bo/bo-all'],
                    joinBy: 'hc-key',
                    name: 'Cantidad Diplomas',
                    states: {
                        hover: {
                            color: '#BADA55'
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        format: '{point.name}'
                    }
                }]
            }); 
        ";
        
        $datos2 = "var data2 = [".$datosTemp2."];";
        
        $datos2 = $datos2."    
            $('#chartContainer2').highcharts('Map', {

                title : {
                    text : 'Diplomas de Bachiller - Educación Alternativa'
                },

                subtitle : {
                    text : 'Gestion ".$gestion."'
                },

                mapNavigation: {
                    enabled: true,
                    buttonOptions: {
                        verticalAlign: 'bottom'
                    }
                },

                colorAxis: {
                    min: 0,
                    minColor: '#E6E7E8',
                    maxColor: '#005645'
                },

                series : [{
                    data : data2,
                    mapData: Highcharts.maps['countries/bo/bo-all'],
                    joinBy: 'hc-key',
                    name: 'Cantidad Diplomas',
                    states: {
                        hover: {
                            color: '#BADA55'
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        format: '{point.name}'
                    }
                }]
            }); 
        ";
        
        $datos = $datos1.$datos2;
        
        return $datos;
    }

    
    
    /**
     * Funcion que busca los diplomas de bachiller impresos de todo el pais, segun la gestion seleccionada por departamento
     * @return tabla
     */
    public function diplomaBachillerNivelNacional($gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
                select ltd.codigo as codigo, ltd.lugar as nombre,  ltd.obs as abreviatura,
                SUM(CASE nivel_tipo_id WHEN 3 THEN cantidad WHEN 13 THEN cantidad ELSE 0 END) as cant1,
                SUM(CASE nivel_tipo_id WHEN 5 THEN cantidad WHEN 15 THEN cantidad ELSE 0 END) as cant2,
                SUM(cantidad) as total from (
                SELECT 
                    iec.nivel_tipo_id as nivel_tipo_id, 
                    lt4.id as departamento_tipo_id,
                    /*lt5.id as distrito_tipo_id, 
                    ie.id as institucioneducativa_id,*/
                    count(*) as cantidad
                FROM documento as d 
                INNER JOIN documento_serie as ds on ds.id = d.documento_serie_id
                INNER JOIN tramite as t on t.id = d.tramite_id
                INNER JOIN estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id 
                INNER JOIN estudiante as e on e.id = ei.estudiante_id
                INNER JOIN institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id  
                INNER JOIN institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito 
                WHERE ds.gestion_id = ".$gestion." and d.documento_estado_id = 1 and d.documento_tipo_id in (1,3,4,5) and iec.nivel_tipo_id in (3,5,13,15)
                group by lt4.id, /*lt5.id, ie.id, */iec.nivel_tipo_id
                ) as v 
                inner join lugar_tipo as ltd on ltd.id = v.departamento_tipo_id
                group by ltd.codigo, ltd.lugar,  ltd.obs
                ");
        $query->execute();
        $dato = $query->fetchAll();
        return $dato;
    }
    
    /**
     * Funcion que busca los diplomas de bachiller impresos de un departamento, segun la gestion seleccionada por distrito
     * @return tabla
     */
    public function diplomaBachillerNivelDepartamental($depto, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
                select ltd.id as codigo, ltd.lugar as nombre,   ltd.obs as abreviatura,
                SUM(CASE nivel_tipo_id WHEN 3 THEN cantidad WHEN 13 THEN cantidad ELSE 0 END) as cant1,
                SUM(CASE nivel_tipo_id WHEN 5 THEN cantidad WHEN 15 THEN cantidad ELSE 0 END) as cant2,
                SUM(cantidad) as total from (
                SELECT 
                    iec.nivel_tipo_id as nivel_tipo_id, 
                    lt5.id as distrito_tipo_id,
                    /*lt5.id as distrito_tipo_id, 
                    ie.id as institucioneducativa_id,*/
                    count(*) as cantidad
                FROM documento as d 
                INNER JOIN documento_serie as ds on ds.id = d.documento_serie_id
                INNER JOIN tramite as t on t.id = d.tramite_id
                INNER JOIN estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id 
                INNER JOIN estudiante as e on e.id = ei.estudiante_id
                INNER JOIN institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id  
                INNER JOIN institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito 
                WHERE ds.gestion_id = ".$gestion." and d.documento_estado_id = 1 and d.documento_tipo_id in (1,3,4,5) and iec.nivel_tipo_id in (3,5,13,15) and lt4.codigo = '".$depto."'
                group by lt5.id, /*lt5.id, ie.id, */iec.nivel_tipo_id
                ) as v 
                inner join lugar_tipo as ltd on ltd.id = v.distrito_tipo_id
                group by ltd.id, ltd.lugar,  ltd.obs
                ");
        $query->execute();
        $dato = $query->fetchAll();
        return $dato;
    }
    
    /**
     * Funcion que busca los diplomas de bachiller impresos de un distrito, segun la gestion seleccionada por ue
     * @return tabla
     */
    public function diplomaBachillerNivelDistrital($distrito, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
                select ie.id as codigo, ie.institucioneducativa as nombre,  ie.id as abreviatura, 
                SUM(CASE nivel_tipo_id WHEN 3 THEN cantidad WHEN 13 THEN cantidad ELSE 0 END) as cant1,
                SUM(CASE nivel_tipo_id WHEN 5 THEN cantidad WHEN 15 THEN cantidad ELSE 0 END) as cant2,
                SUM(cantidad) as total from (
                SELECT 
                    iec.nivel_tipo_id as nivel_tipo_id, 
                    /*lt5.id as distrito_tipo_id,
                    lt5.id as distrito_tipo_id,*/
                    ie.id as institucioneducativa_id,
                    count(*) as cantidad
                FROM documento as d 
                INNER JOIN documento_serie as ds on ds.id = d.documento_serie_id
                INNER JOIN tramite as t on t.id = d.tramite_id
                INNER JOIN estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id 
                INNER JOIN estudiante as e on e.id = ei.estudiante_id
                INNER JOIN institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id  
                INNER JOIN institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito 
                WHERE ds.gestion_id = ".$gestion." and d.documento_estado_id = 1 and d.documento_tipo_id in (1,3,4,5) and iec.nivel_tipo_id in (3,5,13,15) and lt5.id = '".$distrito."'
                group by /*lt5.id, lt5.id, */ie.id, iec.nivel_tipo_id
                ) as v 
                INNER JOIN institucioneducativa as ie on ie.id = v.institucioneducativa_id
                group by ie.id, ie.institucioneducativa
                ");
        $query->execute();
        $dato = $query->fetchAll();
        return $dato;
    }
    
    /**
     * Funcion que busca los diplomas de bachiller impresos de una ue, segun la gestion seleccionada por estudiante
     * @return tabla
     */
    public function diplomaBachillerNivelInstitucional($ue, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
                select codigo_rude as codigo, estudiante as nombre, '' as abreviatura,
                SUM(CASE nivel_tipo_id WHEN 3 THEN 1 WHEN 13 THEN 1 ELSE 0 END) as cant1,
                SUM(CASE nivel_tipo_id WHEN 5 THEN 1 WHEN 15 THEN 1 ELSE 0 END) as cant2,
                COUNT(*) as total from (
                SELECT 
                        d.documento_serie_id as serie,
                        e.codigo_rude as codigo_rude,
                        e.nombre || ' ' || e.paterno || ' ' || e.materno as estudiante,
                        iec.nivel_tipo_id as nivel_tipo_id, 
                        ie.id as ue_id,
                        ie.institucioneducativa as ue,
                        lt5.id as distrito_tipo_id, 
                        lt5.lugar as distrito,
                        lt4.codigo as departamento_tipo_id,
                        lt4.lugar as departamento,
                        lt4.obs as abreviatura,
                        iec.gestion_tipo_id
                FROM documento as d 
                INNER JOIN documento_serie as ds on ds.id = d.documento_serie_id
                INNER JOIN tramite as t on t.id = d.tramite_id
                INNER JOIN estudiante_inscripcion as ei on ei.id = t.estudiante_inscripcion_id 
                INNER JOIN estudiante as e on e.id = ei.estudiante_id
                INNER JOIN institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id  
                INNER JOIN institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito 
                WHERE ds.gestion_id = ".$gestion." and d.documento_estado_id = 1 and d.documento_tipo_id in (1,3,4,5) and iec.nivel_tipo_id in (3,5,13,15) and ie.id = ".$ue."
                ) as v
                group by codigo_rude, estudiante
                order by estudiante
                ");
        $query->execute();
        $dato = $query->fetchAll();
        return $dato;
    }
    
    /**
     * Funcion que halla las gestiones habilitadas en la base de datos
     * @return tabla
     */
    public function gestionesEntidad(){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select * from gestion_tipo order by id desc");
        $query->execute();
        $entity = $query->fetchAll();
        return $entity;
    }
}