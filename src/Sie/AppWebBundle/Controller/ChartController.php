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
     * Pagina Inicial - Información General - Educacion Regular
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function informacionGeneralRegularAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $this->buscaGestionVistaMaterializadaRegular();
        //$gestionActual = 2016;
        $idUsuario = $this->session->get('userId');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $codigoArea = $request->get('codigo');
            $rolUsuario = $request->get('rol');
        } else {
            $codigoArea = 0;
            $rolUsuario = 0;
        }

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $entidad = $this->buscaEntidadRol($codigoArea,$rolUsuario);
        $subEntidades = $this->buscaSubEntidadRol($codigoArea,$rolUsuario);
        $entityEstadistica = $this->buscaEstadisticaAreaRol($codigoArea,$rolUsuario);
        //$entityEstadisticaUE = $this->buscaEstadisticaUERol($codigoArea,$rolUsuario);
        //$entityEstadisticaEE = $this->buscaEstadisticaEERol($codigoArea,$rolUsuario);
        
        $fechaEstadisticaRegular = $this->buscaFechaVistaMaterializadaRegular($gestionProcesada);
       
        $chartMatricula = $this->chartColumnInformacionGeneral($entityEstadistica,"Matrícula",$gestionProcesada,1,"chartContainerMatricula");
        $chartNivel = $this->chartDonut3dInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Nivel de Estudio",$gestionProcesada,2,"chartContainerEfectivoNivel");
        $chartNivelGrado = $this->chartDonutInformacionGeneralNivelGrado($entityEstadistica,"Estudiantes Matriculados según Nivel de Estudio y Año de Escolaridad ",$gestionProcesada,6,"chartContainerEfectivoNivelGrado");
        $chartGenero = $this->chartPieInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Sexo",$gestionProcesada,3,"chartContainerEfectivoGenero");
        $chartArea = $this->chartPyramidInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Área Geográfica",$gestionProcesada,4,"chartContainerEfectivoArea");
        $chartDependencia = $this->chartColumnInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Dependencia",$gestionProcesada,5,"chartContainerEfectivoDependencia");
        
        if ($entidad != '' and $subEntidades != ''){
            return $this->render('SieAppWebBundle:Reporte:matriculaEducativaRegular.html.twig', array(
                'infoEntidad'=>$entidad,
                'infoSubEntidad'=>$subEntidades, 
                'infoEstadistica'=>$entityEstadistica,
                //'infoEstadisticaUE'=>$entityEstadisticaUE,
                //'infoEstadisticaEE'=>$entityEstadisticaEE,
                'datoGraficoMatricula'=>$chartMatricula,
                'datoGraficoNivel'=>$chartNivel,
                'datoGraficoNivelGrado'=>$chartNivelGrado,
                'datoGraficoGenero'=>$chartGenero,
                'datoGraficoArea'=>$chartArea,
                'datoGraficoDependencia'=>$chartDependencia,
                'gestion'=>$gestionProcesada,
                'mensaje'=>'$("#modal-bootstrap-tour").modal("hide");',
                'fechaEstadisticaRegular'=>$fechaEstadisticaRegular,
                'form' => $defaultController->createLoginForm()->createView()
            ));    
        } else {
            if ($entidad != ''){
                return $this->render('SieAppWebBundle:Reporte:matriculaEducativaRegular.html.twig', array(
                    'infoEntidad'=>$entidad, 
                    'infoEstadistica'=>$entityEstadistica,
                    'datoGraficoMatricula'=>$chartMatricula,
                    'datoGraficoNivel'=>$chartNivel,
                    'datoGraficoNivelGrado'=>$chartNivelGrado,
                    'datoGraficoGenero'=>$chartGenero,
                    'datoGraficoArea'=>$chartArea,
                    'datoGraficoDependencia'=>$chartDependencia,
                    'gestion'=>$gestionProcesada,
                    'mensaje'=>'$("#modal-bootstrap-tour").modal("hide");',
                    'fechaEstadisticaRegular'=>$fechaEstadisticaRegular,
                    'form' => $defaultController->createLoginForm()->createView()
                ));  
            } else {
                return $this->render('SieAppWebBundle:Reporte:matriculaEducativaRegular.html.twig', array(
                    'infoSubEntidad'=>$subEntidades, 
                    'infoEstadistica'=>$entityEstadistica,
                    'infoEstadisticaUE'=>$entityEstadisticaUE,
                    'infoEstadisticaEE'=>$entityEstadisticaEE,
                    'datoGraficoMatricula'=>$chartMatricula,
                    'datoGraficoNivel'=>$chartNivel,
                    'datoGraficoNivelGrado'=>$chartNivelGrado,
                    'datoGraficoGenero'=>$chartGenero,
                    'datoGraficoArea'=>$chartArea,
                    'datoGraficoDependencia'=>$chartDependencia,
                    'gestion'=>$gestionProcesada,
                    'mensaje'=>'$("#modal-bootstrap-tour").modal("Efectivos");',
                    'fechaEstadisticaRegular'=>$fechaEstadisticaRegular,
                    'form' => $defaultController->createLoginForm()->createView()
                ));  
            }            
        }        
    }  

    /**
     * Pagina Inicial - Información General - Educacion Regular
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function informacionGeneralRegularInstitucionEducativaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $gestionProcesada = $this->buscaGestionVistaMaterializadaRegular();
        //$gestionActual = 2016;
        $idUsuario = $this->session->get('userId');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $codigoArea = $request->get('codigo');
            $rolUsuario = $request->get('rol');
        } else {
            $codigoArea = 0;
            $rolUsuario = 0;
        }

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $entidad = $this->buscaEntidadRol($codigoArea,$rolUsuario);
        $subEntidades = $this->buscaSubEntidadRegularInstitucionEducativaAreaRol($codigoArea,$rolUsuario);
        $entityEstadistica = $this->buscaEstadisticaRegularInstitucionEducativaAreaRol($codigoArea,$rolUsuario);
        //$entityEstadisticaUE = $this->buscaEstadisticaUERol($codigoArea,$rolUsuario);
        //$entityEstadisticaEE = $this->buscaEstadisticaEERol($codigoArea,$rolUsuario);
        
        $fechaEstadisticaRegular = $this->buscaFechaVistaMaterializadaRegularInstitucionEducativa($gestionProcesada);
       
        $chartNivel = $this->chartDonut3dInformacionGeneral($entityEstadistica,"Unidades Educativas según Nivel de Estudio",$gestionProcesada,11,"chartContainerInstitucionNivel");
        $chartDependencia = $this->chartColumnInformacionGeneral($entityEstadistica,"Unidades Educativas según Dependencia",$gestionProcesada,11,"chartContainerInstitucionDependencia");

        $link = true;
        if ($rolUsuario == 10){
            $link = false;
        }

        if ($entidad != '' and $subEntidades != ''){
            return $this->render('SieAppWebBundle:Reporte:institucionEducativaRegular.html.twig', array(
                'infoEntidad'=>$entidad,
                'infoSubEntidad'=>$subEntidades, 
                'infoEstadistica'=>$entityEstadistica,
                'datoGraficoNivel'=>$chartNivel,
                'datoGraficoDependencia'=>$chartDependencia,
                'gestion'=>$gestionProcesada,
                'link'=>$link,
                'mensaje'=>'$("#modal-bootstrap-tour").modal("hide");',
                'fechaEstadisticaRegular'=>$fechaEstadisticaRegular,
                'form' => $defaultController->createLoginForm()->createView()
            ));    
        } else {
            if ($entidad != ''){
                return $this->render('SieAppWebBundle:Reporte:institucionEducativaRegular.html.twig', array(
                    'infoEntidad'=>$entidad, 
                    'infoEstadistica'=>$entityEstadistica,
                    'datoGraficoNivel'=>$chartNivel,
                    'datoGraficoDependencia'=>$chartDependencia,
                    'gestion'=>$gestionProcesada,
                     'link'=>$link,
                    'mensaje'=>'$("#modal-bootstrap-tour").modal("hide");',
                    'fechaEstadisticaRegular'=>$fechaEstadisticaRegular,
                    'form' => $defaultController->createLoginForm()->createView()
                ));  
            } else {
                return $this->render('SieAppWebBundle:Reporte:institucionEducativaRegular.html.twig', array(
                    'infoSubEntidad'=>$subEntidades, 
                    'infoEstadistica'=>$entityEstadistica,                    
                    'datoGraficoNivel'=>$chartNivel,
                    'datoGraficoDependencia'=>$chartDependencia,
                    'gestion'=>$gestionProcesada,
                    'link'=>$link,
                    'mensaje'=>'$("#modal-bootstrap-tour").modal("Efectivos");',
                    'fechaEstadisticaRegular'=>$fechaEstadisticaRegular,
                    'form' => $defaultController->createLoginForm()->createView()
                ));  
            }            
        }        
    }  



    /**
     * Busca el detalle de estudiantes en funcion al tipo de rol - Educacion Especial
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaEstadisticaEspecialAreaRol($area,$rol) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y') - 1;
        $gestionProcesada = $this->buscaGestionVistaMaterializadaRegular();
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
            with tabla as (
                SELECT eat.id as area_tipo_id, ie.dependencia_tipo_id, e.genero_tipo_id, count(*) as cantidad
                , case eat.id when 99 then 1 when 100 then 1 else 2 end as modalidad_id
                , case eat.id when 99 then 'Indirecta' when 100 then 'Indirecta' else 'Directa' end as modalidad
                FROM estudiante AS e
                INNER JOIN estudiante_inscripcion AS ei ON ei.estudiante_id = e.id
                INNER JOIN estudiante_inscripcion_especial AS eie ON eie.estudiante_inscripcion_id = ei.id
                INNER JOIN especial_area_tipo AS eat ON eie.especial_area_tipo_id = eat.id
                INNER JOIN institucioneducativa_curso AS iec ON ei.institucioneducativa_curso_id = iec.id
                INNER JOIN institucioneducativa AS ie ON iec.institucioneducativa_id = ie.id
                WHERE
                iec.gestion_tipo_id IN (".$gestionActual.") AND
                ie.institucioneducativa_tipo_id = 4
                GROUP BY
                eat.id, ie.dependencia_tipo_id, e.genero_tipo_id
            ) 
            
            select 1 as tipo_id, 'Sexo' as tipo_nombre, gt.id, gt.genero as nombre
            , sum(cantidad) as cantidad from tabla as t 
            inner join genero_tipo as gt on gt.id = t.genero_tipo_id
            group by gt.id, gt.genero
            
            union all
            
            select 2 as tipo_id, 'Dependencia' as tipo_nombre, dt.id, dt.dependencia as nombre
            , sum(cantidad) as cantidad from tabla as t 
            inner join dependencia_tipo as dt on dt.id = t.dependencia_tipo_id
            group by dt.id, dt.dependencia
            
            union all
            
            select 3 as tipo_id, 'Área de Atención' as tipo_nombre, eat.id, eat.area_especial as nombre
            , sum(cantidad) as cantidad from tabla as t 
            inner join especial_area_tipo as eat on eat.id = t.area_tipo_id
            group by eat.id, eat.area_especial
            
            union all
            
            select 4 as tipo_id, 'Modalidad' as tipo_nombre, t.modalidad_id, t.modalidad as nombre
            , sum(cantidad) as cantidad from tabla as t 
            group by t.modalidad_id, t.modalidad
            
            order by tipo_id, id
             
        "); 

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
            $queryEntidad = $em->getConnection()->prepare("
                with tabla as (
                    SELECT eat.id as area_tipo_id, ie.dependencia_tipo_id, e.genero_tipo_id, count(*) as cantidad
                    , case eat.id when 99 then 1 when 100 then 1 else 2 end as modalidad_id
                    , case eat.id when 99 then 'Indirecta' when 100 then 'Indirecta' else 'Directa' end as modalidad
                    FROM estudiante AS e
                    INNER JOIN estudiante_inscripcion AS ei ON ei.estudiante_id = e.id
                    INNER JOIN estudiante_inscripcion_especial AS eie ON eie.estudiante_inscripcion_id = ei.id
                    INNER JOIN especial_area_tipo AS eat ON eie.especial_area_tipo_id = eat.id
                    INNER JOIN institucioneducativa_curso AS iec ON ei.institucioneducativa_curso_id = iec.id
                    INNER JOIN institucioneducativa AS ie ON iec.institucioneducativa_id = ie.id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    WHERE
                    iec.gestion_tipo_id IN (".$gestionActual.") AND ie.institucioneducativa_tipo_id = 4 AND ie.id = ".$area."
                    GROUP BY
                    eat.id, ie.dependencia_tipo_id, e.genero_tipo_id
                ) 
                
                select 1 as tipo_id, 'Sexo' as tipo_nombre, gt.id, gt.genero as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join genero_tipo as gt on gt.id = t.genero_tipo_id
                group by gt.id, gt.genero
                
                union all
                
                select 2 as tipo_id, 'Dependencia' as tipo_nombre, dt.id, dt.dependencia as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join dependencia_tipo as dt on dt.id = t.dependencia_tipo_id
                group by dt.id, dt.dependencia
                
                union all
                
                select 3 as tipo_id, 'Área de Atención' as tipo_nombre, eat.id, eat.area_especial as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join especial_area_tipo as eat on eat.id = t.area_tipo_id
                group by eat.id, eat.area_especial
                
                union all
                
                select 4 as tipo_id, 'Modalidad' as tipo_nombre, t.modalidad_id, t.modalidad as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.modalidad_id, t.modalidad
                
                order by tipo_id, id
            ");     
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("    
                with tabla as (
                    SELECT eat.id as area_tipo_id, ie.dependencia_tipo_id, e.genero_tipo_id, count(*) as cantidad
                    , case eat.id when 99 then 1 when 100 then 1 else 2 end as modalidad_id
                    , case eat.id when 99 then 'Indirecta' when 100 then 'Indirecta' else 'Directa' end as modalidad
                    FROM estudiante AS e
                    INNER JOIN estudiante_inscripcion AS ei ON ei.estudiante_id = e.id
                    INNER JOIN estudiante_inscripcion_especial AS eie ON eie.estudiante_inscripcion_id = ei.id
                    INNER JOIN especial_area_tipo AS eat ON eie.especial_area_tipo_id = eat.id
                    INNER JOIN institucioneducativa_curso AS iec ON ei.institucioneducativa_curso_id = iec.id
                    INNER JOIN institucioneducativa AS ie ON iec.institucioneducativa_id = ie.id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
                    WHERE
                    iec.gestion_tipo_id IN (".$gestionActual.") AND ie.institucioneducativa_tipo_id = 4 AND lt5.codigo = '".$area."'
                    GROUP BY
                    eat.id, ie.dependencia_tipo_id, e.genero_tipo_id
                ) 
                
                select 1 as tipo_id, 'Sexo' as tipo_nombre, gt.id, gt.genero as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join genero_tipo as gt on gt.id = t.genero_tipo_id
                group by gt.id, gt.genero
                
                union all
                
                select 2 as tipo_id, 'Dependencia' as tipo_nombre, dt.id, dt.dependencia as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join dependencia_tipo as dt on dt.id = t.dependencia_tipo_id
                group by dt.id, dt.dependencia
                
                union all
                
                select 3 as tipo_id, 'Área de Atención' as tipo_nombre, eat.id, eat.area_especial as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join especial_area_tipo as eat on eat.id = t.area_tipo_id
                group by eat.id, eat.area_especial
                
                union all
                
                select 4 as tipo_id, 'Modalidad' as tipo_nombre, t.modalidad_id, t.modalidad as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.modalidad_id, t.modalidad
                
                order by tipo_id, id
            ");  
        }  

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                with tabla as (
                    SELECT eat.id as area_tipo_id, ie.dependencia_tipo_id, e.genero_tipo_id, count(*) as cantidad
                    , case eat.id when 99 then 1 when 100 then 1 else 2 end as modalidad_id
                    , case eat.id when 99 then 'Indirecta' when 100 then 'Indirecta' else 'Directa' end as modalidad
                    FROM estudiante AS e
                    INNER JOIN estudiante_inscripcion AS ei ON ei.estudiante_id = e.id
                    INNER JOIN estudiante_inscripcion_especial AS eie ON eie.estudiante_inscripcion_id = ei.id
                    INNER JOIN especial_area_tipo AS eat ON eie.especial_area_tipo_id = eat.id
                    INNER JOIN institucioneducativa_curso AS iec ON ei.institucioneducativa_curso_id = iec.id
                    INNER JOIN institucioneducativa AS ie ON iec.institucioneducativa_id = ie.id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                    left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                    left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                    left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                    left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                    WHERE
                    iec.gestion_tipo_id IN (".$gestionActual.") AND ie.institucioneducativa_tipo_id = 4 AND lt4.codigo = '".$area."'
                    GROUP BY
                    eat.id, ie.dependencia_tipo_id, e.genero_tipo_id
                ) 
                
                select 1 as tipo_id, 'Sexo' as tipo_nombre, gt.id, gt.genero as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join genero_tipo as gt on gt.id = t.genero_tipo_id
                group by gt.id, gt.genero
                
                union all
                
                select 2 as tipo_id, 'Dependencia' as tipo_nombre, dt.id, dt.dependencia as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join dependencia_tipo as dt on dt.id = t.dependencia_tipo_id
                group by dt.id, dt.dependencia
                
                union all
                
                select 3 as tipo_id, 'Área de Atención' as tipo_nombre, eat.id, eat.area_especial as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join especial_area_tipo as eat on eat.id = t.area_tipo_id
                group by eat.id, eat.area_especial
                
                union all
                
                select 4 as tipo_id, 'Modalidad' as tipo_nombre, t.modalidad_id, t.modalidad as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.modalidad_id, t.modalidad
                
                order by tipo_id, id
            ");  
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {            
            $queryEntidad = $em->getConnection()->prepare("
                with tabla as (
                    SELECT eat.id as area_tipo_id, ie.dependencia_tipo_id, e.genero_tipo_id, count(*) as cantidad
                    , case eat.id when 99 then 1 when 100 then 1 else 2 end as modalidad_id
                    , case eat.id when 99 then 'Indirecta' when 100 then 'Indirecta' else 'Directa' end as modalidad
                    FROM estudiante AS e
                    INNER JOIN estudiante_inscripcion AS ei ON ei.estudiante_id = e.id
                    INNER JOIN estudiante_inscripcion_especial AS eie ON eie.estudiante_inscripcion_id = ei.id
                    INNER JOIN especial_area_tipo AS eat ON eie.especial_area_tipo_id = eat.id
                    INNER JOIN institucioneducativa_curso AS iec ON ei.institucioneducativa_curso_id = iec.id
                    INNER JOIN institucioneducativa AS ie ON iec.institucioneducativa_id = ie.id
                    WHERE
                    iec.gestion_tipo_id IN (".$gestionActual.") AND
                    ie.institucioneducativa_tipo_id = 4
                    GROUP BY
                    eat.id, ie.dependencia_tipo_id, e.genero_tipo_id
                ) 
                
                select 1 as tipo_id, 'Sexo' as tipo_nombre, gt.id, gt.genero as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join genero_tipo as gt on gt.id = t.genero_tipo_id
                group by gt.id, gt.genero
                
                union all
                
                select 2 as tipo_id, 'Dependencia' as tipo_nombre, dt.id, dt.dependencia as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join dependencia_tipo as dt on dt.id = t.dependencia_tipo_id
                group by dt.id, dt.dependencia
                
                union all
                
                select 3 as tipo_id, 'Área de Atención' as tipo_nombre, eat.id, eat.area_especial as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join especial_area_tipo as eat on eat.id = t.area_tipo_id
                group by eat.id, eat.area_especial
                
                union all
                
                select 4 as tipo_id, 'Modalidad' as tipo_nombre, t.modalidad_id, t.modalidad as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.modalidad_id, t.modalidad
                
                order by tipo_id, id
            ");    
        } 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 

        $aDato = array();

        foreach ($objEntidad as $key => $dato) {
            $aDato[$dato['tipo_id']]['tipo'] = $dato['tipo_nombre'];
            if (isset($aDato[$dato['tipo_id']]['dato'][0]['cantidad'])){
                $cantidadParcial = $aDato[$dato['tipo_id']]['dato'][0]['cantidad'] + $dato['cantidad'];
            } else {
                $cantidadParcial = $dato['cantidad'];
            }    
            $aDato[$dato['tipo_id']]['dato'][0] = array('detalle'=>'Total', 'cantidad'=>$cantidadParcial);    
            $aDato[$dato['tipo_id']]['dato'][$dato['id']] = array('detalle'=>$dato['nombre'], 'cantidad'=>$dato['cantidad']);  
        }
        return $aDato;
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


    /**
     * Imprime reportes estadisticos segun el tipo de rol en formato PDF - Educación Especial
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function informacionGeneralEspecialPrintPdfAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        
        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadistica_Nacional_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {              
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadistica_Distrital_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion.'&coddis='.$codigoArea));
        }  

        if($rol == 7) // Tecnico Departamental
        { 
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadistica_Departamental_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion.'&coddep='.$codigoArea));
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {  
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadistica_Nacional_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion));
        } 

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * Imprime reportes estadisticos segun el tipo de rol en formato EXCEL - Educación Especial
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function informacionGeneralEspecialPrintXlsAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadistica_Nacional_v1_rcm.rptdesign&__format=xls&gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {              
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadistica_Distrital_v1_rcm.rptdesign&__format=xls&gestion='.$gestion.'&coddis='.$codigoArea));
        }  

        if($rol == 7) // Tecnico Departamental
        { 
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadistica_Departamental_v1_rcm.rptdesign&__format=xls&gestion='.$gestion.'&coddep='.$codigoArea));
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {  
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadistica_Nacional_v1_rcm.rptdesign&__format=xls&gestion='.$gestion));
        } 

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
}
