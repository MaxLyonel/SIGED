<?php

namespace Sie\TramitesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Date;

use Sie\TramitesBundle\Controller\DefaultController as defaultTramiteController;
use Sie\AppWebBundle\Controller\ChartController as chartController;

/**
 * Vista controller.
 *
 */
class EstadisticaCertificacionTecnicaController extends Controller {
    private $session;
    
    public function __construct() {        
        $this->session = new Session();        
    }
    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra la cantidad de certificados tecnicos impresos de educacion alternativa a nivel nacional
    // PARAMETROS: gestion
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecEmitidoAction(Request $request){
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = $fechaActual->format('Y');
        $gestionActual = 2018;

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $array_user = $sesion->get('roluser');

        if ($request->isMethod('POST')) {
            $gestionActual = (int)$request->get('gestion');
            $codigoArea = (int)base64_decode($request->get('codigo'));
            $nivelArea = (int)$request->get('nivel');
        } else {
            $gestionActual = (int)$gestionActual;
            $codigoArea = (int)0;
            $nivelArea = (int)$array_user[0]['orden'];
        }

        $entityCertTecAltLista = $this->certificadoTecnicoAlternativaLista($nivelArea, $codigoArea, $gestionActual);

        $entityCertTecAltEstadistica = $this->certificadoTecnicoAlternativaEstadistica($nivelArea, $codigoArea, $gestionActual);

        if(count($entityCertTecAltLista)>0 and isset($entityCertTecAltLista)){
            $totalgeneral = 0;
            foreach ($entityCertTecAltLista as $key => $dato) {
                $totalgeneral= $totalgeneral+ $dato['cantidad'];
            }
            foreach ($entityCertTecAltLista as $key => $dato) {
                $entityCertTecAltLista[$key]['total_general'] = $totalgeneral;
            }
        } else {
            $entityCertTecAltLista = null;
        }

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);
        $entityGestion = $defaultTramiteController->getGestiones(2013);

        // dump($entityCertTecAltLista);dump($entityCertTecAltEstadistica);die;

        $chartController = new chartController();
        $chartController->setContainer($this->container);

        // $chartMatricula = $this->chartColumnInformacionGeneral($entityEstadistica,"Matrícula",$gestionProcesada,1,"chartContainerMatricula");
        // $chartNivel = $this->chartDonutInformacionGeneralNivelGrado($entityEstadistica[3],"Estudiantes matriculados según Etapa/Acreditación",$gestion,"Estudiantes","chartContainerDiscapacidad");
        // $chartNivelGrado = $this->chartDonutInformacionGeneralNivelGrado($entityEstadistica,"Estudiantes Matriculados según Nivel de Estudio y Año de Escolaridad ",$gestionProcesada,6,"chartContainerEfectivoNivelGrado");
        if (isset($entityCertTecAltEstadistica[2])){
            $chartGenero = $chartController->chartPie($entityCertTecAltEstadistica[2][1],"Certificaciones Emitidas según Sexo",$gestionActual,"Estudiantes","chartContainerGenero");
            // $chartArea = $this->chartPyramidInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Área Geográfica",$gestionProcesada,4,"chartContainerEfectivoArea");
            $chartDependencia = $chartController->chartColumn($entityCertTecAltEstadistica[2][2],"Certificaciones Emitidas según Dependencia",$gestionActual,"Estudiantes","chartContainerDependencia");
            $chartNivel = $chartController->chartSemiPieDonut3d($entityCertTecAltEstadistica[2][3],"Certificaciones Emitidas según Acreditación",$gestionActual,"Estudiantes","chartContainerNivel");
        } else {
            $chartGenero = '';
            $chartDependencia = '';
            $chartNivel = '';
        }

        if (isset($entityCertTecAltEstadistica[3])){
            $chartGenero2 = $chartController->chartPie($entityCertTecAltEstadistica[3][1],"Certificaciones Emitidas según Sexo",$gestionActual,"Estudiantes","chartContainerGenero2");
            $chartDependencia2 = $chartController->chartColumn($entityCertTecAltEstadistica[3][2],"Certificaciones Emitidas según Dependencia",$gestionActual,"Estudiantes","chartContainerDependencia2");
            $chartNivel2 = $chartController->chartSemiPieDonut3d($entityCertTecAltEstadistica[3][3],"Certificaciones Emitidas según Acreditación",$gestionActual,"Estudiantes","chartContainerNivel2");
        } else {
            $chartGenero2 = '';
            $chartDependencia2 = '';
            $chartNivel2 = '';
        }  
        
        // dump($chartGenero);dump($chartDependencia);dump($chartNivel);die;

        if ($nivelArea == 4){
            $entityCertTecAltLista = array();
        }

        if(count($entityCertTecAltLista)>0 and isset($entityCertTecAltLista)){
            return $this->render($this->session->get('pathSystem') . ':Estadistica:certificacionTecnicaAlternativa.html.twig', array(
                'infoEntidad'=>$entityCertTecAltEstadistica,
                'infoSubEntidad'=>$entityCertTecAltLista,
                'gestion'=>$gestionActual,
                'datoGraficoNivel'=>$chartNivel,
                'datoGraficoGenero'=>$chartGenero,
                'datoGraficoDependencia'=>$chartDependencia,
                'datoGraficoNivel2'=>$chartNivel2,
                'datoGraficoGenero2'=>$chartGenero2,
                'datoGraficoDependencia2'=>$chartDependencia2,
                'fechaEstadistica'=>$fechaActual,
            ));
        } else {
            return $this->render($this->session->get('pathSystem') . ':Estadistica:certificacionTecnicaAlternativa.html.twig', array(
                'infoEntidad'=>$entityCertTecAltEstadistica,
                'gestion'=>$gestionActual,
                'datoGraficoNivel'=>$chartNivel,
                'datoGraficoGenero'=>$chartGenero,
                'datoGraficoDependencia'=>$chartDependencia,
                'datoGraficoNivel2'=>$chartNivel2,
                'datoGraficoGenero2'=>$chartGenero2,
                'datoGraficoDependencia2'=>$chartDependencia2,
                'fechaEstadistica'=>$fechaActual,
            ));
        }

        return $entity;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que muestra los roles del usuario
    // PARAMETROS: gestionId (gestion cuando se inicio el sistema)
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    function getGestiones($gestionId) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:GestionTipo');
        $query = $entity->createQueryBuilder('gt')
                ->where('gt.id >= :id')
                ->setParameter('id', $gestionId)
                ->getQuery();
        try {
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $exc) {
            return array();
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista la cantidad de certificados tecnicos impresos de educacion alternativa por area segun el id y nivel de desagregacion enviado
    // PARAMETROS: nivelArea, codigoArea, gestion
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certificadoTecnicoAlternativaLista($nivelArea, $codigoArea, $gestion) {
        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare("
            select :gestion::int as gestion_tipo_id, dt.id, dt.id as codigo, dt.departamento as nombre, coalesce(v.cantidad,0) as cantidad, :nivel::int as nivel 
            from (
                select ies.gestion_tipo_id, lt14.id as id, lt14.codigo::int as codigo, lt14.lugar as nombre, count(*) as cantidad
                from (superior_facultad_area_tipo as sfat  
                inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id 
                inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id 
                inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id 
                inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id 
                inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id 
                inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id 
                inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id 
                inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id )
                inner join periodo_tipo as pert on pert.id = ies.periodo_tipo_id
                inner join institucioneducativa as ie on ie.id = siea.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (6,7,8) and (case t.tramite_tipo when 6 then 1 when 7 then 2 when 8 then 3 else 0 end) = sat.codigo and t.esactivo = 't'
                inner join documento as d on d.tramite_id = t.id and documento_tipo_id in (6,7,8) and d.documento_estado_id = 1 
                inner join lugar_tipo as lt15 on lt15.id = jg.lugar_tipo_id_distrito
                inner join lugar_tipo as lt10 on lt10.id = jg.lugar_tipo_id_localidad
                inner join lugar_tipo as lt11 on lt11.id = lt10.lugar_tipo_id
                inner join lugar_tipo as lt12 on lt12.id = lt11.lugar_tipo_id
                inner join lugar_tipo as lt13 on lt13.id = lt12.lugar_tipo_id
                inner join lugar_tipo as lt14 on lt14.id = lt13.lugar_tipo_id
                where sat.codigo IN (1,2,3) and ies.gestion_tipo_id = :gestion::double PRECISION
                and sfat.codigo in (18,19,20,21,22,23,24,25) 
                group by ies.gestion_tipo_id, lt14.id, lt14.codigo, lt14.lugar 
            ) as v
            right join (select * from departamento_tipo where id != 0) as dt on dt.id = v.codigo 
            order by id
        ");

        if ($nivelArea == 2){
            $query = $em->getConnection()->prepare("
                select ies.gestion_tipo_id, lt15.id as id, lt15.codigo::int as codigo, lt15.lugar as nombre, count(*) as cantidad, :nivel::int as nivel
                from (superior_facultad_area_tipo as sfat  
                inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id 
                inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id 
                inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id 
                inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id 
                inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id 
                inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id 
                inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id 
                inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id )
                inner join periodo_tipo as pert on pert.id = ies.periodo_tipo_id
                inner join institucioneducativa as ie on ie.id = siea.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (6,7,8) and (case t.tramite_tipo when 6 then 1 when 7 then 2 when 8 then 3 else 0 end) = sat.codigo and t.esactivo = 't'
                inner join documento as d on d.tramite_id = t.id and documento_tipo_id in (6,7,8) and d.documento_estado_id = 1 
                left join lugar_tipo as lt10 on lt10.id = jg.lugar_tipo_id_localidad
                left join lugar_tipo as lt11 on lt11.id = lt10.lugar_tipo_id
                left join lugar_tipo as lt12 on lt12.id = lt11.lugar_tipo_id
                left join lugar_tipo as lt13 on lt13.id = lt12.lugar_tipo_id
                left join lugar_tipo as lt14 on lt14.id = lt13.lugar_tipo_id
                left join lugar_tipo as lt15 on lt15.id = jg.lugar_tipo_id_distrito
                where sat.codigo IN (1,2,3) and lt14.codigo::integer = :area::integer and ies.gestion_tipo_id = :gestion::double PRECISION
                and sfat.codigo in (18,19,20,21,22,23,24,25) 
                group by ies.gestion_tipo_id, lt15.id, lt15.codigo, lt15.lugar
                order by ies.gestion_tipo_id, lt15.id
            ");
            $query->bindValue(':area', $codigoArea);
        }

        if ($nivelArea == 3){
            $query = $em->getConnection()->prepare("
                select ies.gestion_tipo_id, ie.id as id, ie.id as codigo, ie.institucioneducativa as nombre, count(*) as cantidad, :nivel::int as nivel
                from (superior_facultad_area_tipo as sfat  
                inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id 
                inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id 
                inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id 
                inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id 
                inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id 
                inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id 
                inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id 
                inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id )
                inner join periodo_tipo as pert on pert.id = ies.periodo_tipo_id
                inner join institucioneducativa as ie on ie.id = siea.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (6,7,8) and (case t.tramite_tipo when 6 then 1 when 7 then 2 when 8 then 3 else 0 end) = sat.codigo and t.esactivo = 't'
                inner join documento as d on d.tramite_id = t.id and documento_tipo_id in (6,7,8) and d.documento_estado_id = 1 
                left join lugar_tipo as lt10 on lt10.id = jg.lugar_tipo_id_localidad
                left join lugar_tipo as lt11 on lt11.id = lt10.lugar_tipo_id
                left join lugar_tipo as lt12 on lt12.id = lt11.lugar_tipo_id
                left join lugar_tipo as lt13 on lt13.id = lt12.lugar_tipo_id
                left join lugar_tipo as lt14 on lt14.id = lt13.lugar_tipo_id
                left join lugar_tipo as lt15 on lt15.id = jg.lugar_tipo_id_distrito
                where sat.codigo IN (1,2,3) and lt15.codigo::integer = :area::integer and ies.gestion_tipo_id = :gestion::double PRECISION
                and sfat.codigo in (18,19,20,21,22,23,24,25) 
                group by ies.gestion_tipo_id, ie.id, ie.institucioneducativa
                order by ies.gestion_tipo_id, ie.id, ie.institucioneducativa
            ");
            $query->bindValue(':area', $codigoArea);
        }

        if ($nivelArea == 4){
            $query = $em->getConnection()->prepare("
                select ies.gestion_tipo_id, ie.id as id, ie.id as codigo, ie.institucioneducativa as nombre, count(*) as cantidad, :nivel::int as nivel
                from (superior_facultad_area_tipo as sfat  
                inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id 
                inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id 
                inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id 
                inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id 
                inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id 
                inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id 
                inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id 
                inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id )
                inner join periodo_tipo as pert on pert.id = ies.periodo_tipo_id
                inner join institucioneducativa as ie on ie.id = siea.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (6,7,8) and (case t.tramite_tipo when 6 then 1 when 7 then 2 when 8 then 3 else 0 end) = sat.codigo and t.esactivo = 't'
                inner join documento as d on d.tramite_id = t.id and documento_tipo_id in (6,7,8) and d.documento_estado_id = 1 
                left join lugar_tipo as lt10 on lt10.id = jg.lugar_tipo_id_localidad
                left join lugar_tipo as lt11 on lt11.id = lt10.lugar_tipo_id
                left join lugar_tipo as lt12 on lt12.id = lt11.lugar_tipo_id
                left join lugar_tipo as lt13 on lt13.id = lt12.lugar_tipo_id
                left join lugar_tipo as lt14 on lt14.id = lt13.lugar_tipo_id
                left join lugar_tipo as lt15 on lt15.id = jg.lugar_tipo_id_distrito
                where sat.codigo IN (1,2,3) and ie.id = :area and ies.gestion_tipo_id = :gestion::double PRECISION
                and sfat.codigo in (18,19,20,21,22,23,24,25) 
                group by ies.gestion_tipo_id, ie.id, ie.institucioneducativa
                order by ies.gestion_tipo_id, ie.id, ie.institucioneducativa
            ");
            $query->bindValue(':area', $codigoArea);
        }

        $query->bindValue(':nivel', ($nivelArea + 1));
        $query->bindValue(':gestion', $gestion);
        $query->execute();
        $dato = $query->fetchAll();
        return $dato;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que genera la estadistica de la cantidad de certificados tecnicos impresos de educacion alternativa por area segun el id y nivel de desagregacion enviado
    // PARAMETROS: nivelArea, codigoArea, gestion
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certificadoTecnicoAlternativaEstadistica($nivelArea, $codigoArea, $gestion) {
        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare("
            with tabla as (
                select ies.gestion_tipo_id, pert.periodo as periodo, pert.id as periodo_id
                , sfat.id as area_id, sfat.codigo as area_codigo, sfat.facultad_area as area
                , sat.id as acreditacion_id, sat.codigo as acreditacion_codigo, sat.acreditacion
                , dt.id as dependencia_id, dt.dependencia, gt.id as genero_id, gt.genero, count(*) as cantidad, :nivel::int as nivel
                from (superior_facultad_area_tipo as sfat  
                inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id 
                inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id 
                inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id 
                inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id 
                inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id 
                inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id 
                inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id 
                inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id )
                inner join estudiante as e on ei.estudiante_id=e.id
                inner join genero_tipo as gt on gt.id = e.genero_tipo_id
                inner join periodo_tipo as pert on pert.id = ies.periodo_tipo_id
                inner join institucioneducativa as ie on ie.id = siea.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (6,7,8) and (case t.tramite_tipo when 6 then 1 when 7 then 2 when 8 then 3 else 0 end) = sat.codigo and t.esactivo = 't'
                inner join documento as d on d.tramite_id = t.id and documento_tipo_id in (6,7,8) and d.documento_estado_id = 1 
                inner join lugar_tipo as lt10 on lt10.id = jg.lugar_tipo_id_localidad
                inner join lugar_tipo as lt11 on lt11.id = lt10.lugar_tipo_id
                inner join lugar_tipo as lt12 on lt12.id = lt11.lugar_tipo_id
                inner join lugar_tipo as lt13 on lt13.id = lt12.lugar_tipo_id
                inner join lugar_tipo as lt14 on lt14.id = lt13.lugar_tipo_id
                inner join lugar_tipo as lt15 on lt15.id = jg.lugar_tipo_id_distrito
                inner join dependencia_tipo as dt on dt.id = ie.dependencia_tipo_id
                where sat.codigo IN (1,2,3) and ies.gestion_tipo_id = :gestion::double PRECISION
                and sfat.codigo in (18,19,20,21,22,23,24,25) 
                group by ies.gestion_tipo_id, pert.periodo, pert.id, lt14.id -- , lt14.codigo, lt14.lugar -- , lt15.id, lt15.codigo, lt15.lugar
                , sfat.id, sfat.codigo, sfat.facultad_area -- , sest.id, sest.especialidad
                , sat.id, sat.codigo, sat.acreditacion
                , dt.id, dt.dependencia
                , gt.id, gt.genero
            )

            select 0 as lugar_id, '0' as lugar_codigo, 'Bolivia' as lugar_nombre, 1 as tipo_id, 'Sexo' as tipo_nombre, periodo_id, periodo
            , genero_id as id, genero as nombre, 0 as sub_id, '' as sub_nombre, sum(cantidad) as cantidad
            from tabla as t 
            group by periodo_id, periodo, genero_id, genero
            
            union all
            
            select 0 as lugar_id, '0' as lugar_codigo, 'Bolivia' as lugar_nombre, 2 as tipo_id, 'Dependencia' as tipo_nombre, periodo_id, periodo
            , dependencia_id as id, dependencia as nombre, 0 as sub_id, '' as sub_nombre, sum(cantidad) as cantidad
            from tabla as t 
            group by periodo_id, periodo, dependencia_id, dependencia
            
            union all
            
            select 0 as lugar_id, '0' as lugar_codigo, 'Bolivia' as lugar_nombre, 3 as tipo_id, 'Acreditación' as tipo_nombre, periodo_id, periodo
            , acreditacion_codigo as id, acreditacion as nombre, 0 as sub_id, '' as sub_nombre, sum(cantidad) as cantidad
            from tabla as t 
            group by periodo_id, periodo, acreditacion_codigo, acreditacion
                        
            order by tipo_id, id
        ");

        if ($nivelArea == 2){
            dump($nivelArea);dump($codigoArea);dump($gestion);
            $query = $em->getConnection()->prepare("
                with tabla as (
                    select lt14.id as departamento_id, lt14.codigo as departamento_codigo, lt14.lugar as departamento
                    -- , lt15.id as distrito_id, lt15.codigo as distrito_codigo, lt15.lugar as distrito
                    , ies.gestion_tipo_id, pert.periodo as periodo, pert.id as periodo_id
                    , sfat.id as area_id, sfat.codigo as area_codigo, sfat.facultad_area as area
                    , sat.id as acreditacion_id, sat.codigo as acreditacion_codigo, sat.acreditacion
                    , dt.id as dependencia_id, dt.dependencia, gt.id as genero_id, gt.genero, count(*) as cantidad, :nivel::int as nivel
                    from (superior_facultad_area_tipo as sfat  
                    inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id 
                    inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id 
                    inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id 
                    inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id 
                    inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id 
                    inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id 
                    inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id 
                    inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id )
                    inner join estudiante as e on ei.estudiante_id=e.id
                    inner join genero_tipo as gt on gt.id = e.genero_tipo_id
                    inner join periodo_tipo as pert on pert.id = ies.periodo_tipo_id
                    inner join institucioneducativa as ie on ie.id = siea.institucioneducativa_id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (6,7,8) and (case t.tramite_tipo when 6 then 1 when 7 then 2 when 8 then 3 else 0 end) = sat.codigo and t.esactivo = 't'
                    inner join documento as d on d.tramite_id = t.id and documento_tipo_id in (6,7,8) and d.documento_estado_id = 1 
                    inner join lugar_tipo as lt10 on lt10.id = jg.lugar_tipo_id_localidad
                    inner join lugar_tipo as lt11 on lt11.id = lt10.lugar_tipo_id
                    inner join lugar_tipo as lt12 on lt12.id = lt11.lugar_tipo_id
                    inner join lugar_tipo as lt13 on lt13.id = lt12.lugar_tipo_id
                    inner join lugar_tipo as lt14 on lt14.id = lt13.lugar_tipo_id
                    inner join lugar_tipo as lt15 on lt15.id = jg.lugar_tipo_id_distrito
                    inner join dependencia_tipo as dt on dt.id = ie.dependencia_tipo_id
                    where sat.codigo IN (1,2,3) and ies.gestion_tipo_id = :gestion::double PRECISION
                    and sfat.codigo in (18,19,20,21,22,23,24,25)  and lt14.codigo::integer = :area::integer
                    group by ies.gestion_tipo_id, pert.periodo, pert.id, lt14.id, lt14.codigo, lt14.lugar -- , lt15.id, lt15.codigo, lt15.lugar
                    , sfat.id, sfat.codigo, sfat.facultad_area -- , sest.id, sest.especialidad
                    , sat.id, sat.codigo, sat.acreditacion
                    , dt.id, dt.dependencia
                    , gt.id, gt.genero
                )

                select departamento_id as lugar_id, departamento_codigo as lugar_codigo, departamento as lugar_nombre, 1 as tipo_id, 'Sexo' as tipo_nombre, periodo_id, periodo
                , genero_id as id, genero as nombre, 0 as sub_id, '' as sub_nombre, sum(cantidad) as cantidad
                from tabla as t 
                group by departamento_id, departamento_codigo, departamento, periodo_id, periodo, genero_id, genero
                
                union all
                
                select departamento_id as lugar_id, departamento_codigo as lugar_codigo, departamento as lugar_nombre, 2 as tipo_id, 'Dependencia' as tipo_nombre, periodo_id, periodo
                , dependencia_id as id, dependencia as nombre, 0 as sub_id, '' as sub_nombre, sum(cantidad) as cantidad
                from tabla as t 
                group by departamento_id, departamento_codigo, departamento, periodo_id, periodo, dependencia_id, dependencia
                
                union all
                
                select departamento_id as lugar_id, departamento_codigo as lugar_codigo, departamento as lugar_nombre, 3 as tipo_id, 'Acreditación' as tipo_nombre, periodo_id, periodo
                , acreditacion_codigo as id, acreditacion as nombre, 0 as sub_id, '' as sub_nombre, sum(cantidad) as cantidad
                from tabla as t 
                group by departamento_id, departamento_codigo, departamento, periodo_id, periodo, acreditacion_codigo, acreditacion
                            
                order by tipo_id, id
            ");
            $query->bindValue(':area', $codigoArea);
        }

        if ($nivelArea == 3){
            $query = $em->getConnection()->prepare("
                with tabla as (
                    select lt15.id as distrito_id, lt15.codigo as distrito_codigo, lt15.lugar as distrito
                    , ies.gestion_tipo_id, pert.periodo as periodo, pert.id as periodo_id
                    , sfat.id as area_id, sfat.codigo as area_codigo, sfat.facultad_area as area
                    , sat.id as acreditacion_id, sat.codigo as acreditacion_codigo, sat.acreditacion
                    , dt.id as dependencia_id, dt.dependencia, gt.id as genero_id, gt.genero, count(*) as cantidad, :nivel::int as nivel
                    from (superior_facultad_area_tipo as sfat  
                    inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id 
                    inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id 
                    inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id 
                    inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id 
                    inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id 
                    inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id 
                    inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id 
                    inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id )
                    inner join estudiante as e on ei.estudiante_id=e.id
                    inner join genero_tipo as gt on gt.id = e.genero_tipo_id
                    inner join periodo_tipo as pert on pert.id = ies.periodo_tipo_id
                    inner join institucioneducativa as ie on ie.id = siea.institucioneducativa_id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (6,7,8) and (case t.tramite_tipo when 6 then 1 when 7 then 2 when 8 then 3 else 0 end) = sat.codigo and t.esactivo = 't'
                    inner join documento as d on d.tramite_id = t.id and documento_tipo_id in (6,7,8) and d.documento_estado_id = 1 
                    inner join lugar_tipo as lt10 on lt10.id = jg.lugar_tipo_id_localidad
                    inner join lugar_tipo as lt11 on lt11.id = lt10.lugar_tipo_id
                    inner join lugar_tipo as lt12 on lt12.id = lt11.lugar_tipo_id
                    inner join lugar_tipo as lt13 on lt13.id = lt12.lugar_tipo_id
                    inner join lugar_tipo as lt14 on lt14.id = lt13.lugar_tipo_id
                    inner join lugar_tipo as lt15 on lt15.id = jg.lugar_tipo_id_distrito
                    inner join dependencia_tipo as dt on dt.id = ie.dependencia_tipo_id
                    where sat.codigo IN (1,2,3) and ies.gestion_tipo_id = :gestion::double PRECISION
                    and sfat.codigo in (18,19,20,21,22,23,24,25)  and lt15.codigo::integer = :area::integer
                    group by ies.gestion_tipo_id, pert.periodo, pert.id , lt15.id, lt15.codigo, lt15.lugar
                    , sfat.id, sfat.codigo, sfat.facultad_area --, sest.id, sest.especialidad
                    , sat.id, sat.codigo, sat.acreditacion
                    , dt.id, dt.dependencia
                    , gt.id, gt.genero
                )

                select distrito_id as lugar_id, distrito_codigo as lugar_codigo, distrito as lugar_nombre, 1 as tipo_id, 'Sexo' as tipo_nombre, periodo_id, periodo
                , genero_id as id, genero as nombre, 0 as sub_id, '' as sub_nombre, sum(cantidad) as cantidad
                from tabla as t 
                group by distrito_id, distrito_codigo, distrito, periodo_id, periodo, genero_id, genero
                
                union all
                
                select distrito_id as lugar_id, distrito_codigo as lugar_codigo, distrito as lugar_nombre, 2 as tipo_id, 'Dependencia' as tipo_nombre, periodo_id, periodo
                , dependencia_id as id, dependencia as nombre, 0 as sub_id, '' as sub_nombre, sum(cantidad) as cantidad 
                from tabla as t 
                group by distrito_id, distrito_codigo, distrito, periodo_id, periodo, dependencia_id, dependencia
                
                union all
                
                select distrito_id as lugar_id, distrito_codigo as lugar_codigo, distrito as lugar_nombre, 3 as tipo_id, 'Acreditación' as tipo_nombre, periodo_id, periodo
                , acreditacion_codigo as id, acreditacion as nombre, 0 as sub_id, '' as sub_nombre, sum(cantidad) as cantidad 
                from tabla as t 
                group by distrito_id, distrito_codigo, distrito, periodo_id, periodo, acreditacion_codigo, acreditacion
                            
                order by tipo_id, id
            ");
            $query->bindValue(':area', $codigoArea);
        }

        if ($nivelArea == 4){
            $query = $em->getConnection()->prepare("
                with tabla as (
                    select ie.id as institucioneducativa_id, ie.institucioneducativa as institucioneducativa
                    , ies.gestion_tipo_id, pert.periodo as periodo, pert.id as periodo_id
                    , sfat.id as area_id, sfat.codigo as area_codigo, sfat.facultad_area as area
                    , sat.id as acreditacion_id, sat.codigo as acreditacion_codigo, sat.acreditacion
                    , dt.id as dependencia_id, dt.dependencia, gt.id as genero_id, gt.genero, count(*) as cantidad, :nivel::int as nivel
                    , coalesce(jg.cordx,-16.2256989) as cordx, coalesce(jg.cordy,-68.0441409) as cordy
                    from (superior_facultad_area_tipo as sfat  
                    inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id 
                    inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id 
                    inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id 
                    inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id 
                    inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id 
                    inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id 
                    inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id 
                    inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id )
                    inner join estudiante as e on ei.estudiante_id=e.id
                    inner join genero_tipo as gt on gt.id = e.genero_tipo_id
                    inner join periodo_tipo as pert on pert.id = ies.periodo_tipo_id
                    inner join institucioneducativa as ie on ie.id = siea.institucioneducativa_id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    inner join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (6,7,8) and (case t.tramite_tipo when 6 then 1 when 7 then 2 when 8 then 3 else 0 end) = sat.codigo and t.esactivo = 't'
                    inner join documento as d on d.tramite_id = t.id and documento_tipo_id in (6,7,8) and d.documento_estado_id = 1 
                    inner join lugar_tipo as lt10 on lt10.id = jg.lugar_tipo_id_localidad
                    inner join lugar_tipo as lt11 on lt11.id = lt10.lugar_tipo_id
                    inner join lugar_tipo as lt12 on lt12.id = lt11.lugar_tipo_id
                    inner join lugar_tipo as lt13 on lt13.id = lt12.lugar_tipo_id
                    inner join lugar_tipo as lt14 on lt14.id = lt13.lugar_tipo_id
                    inner join lugar_tipo as lt15 on lt15.id = jg.lugar_tipo_id_distrito
                    inner join dependencia_tipo as dt on dt.id = ie.dependencia_tipo_id
                    where sat.codigo IN (1,2,3) and ies.gestion_tipo_id = :gestion::double PRECISION
                    and sfat.codigo in (18,19,20,21,22,23,24,25)  and ie.id::integer = :area::integer
                    group by ies.gestion_tipo_id, pert.periodo, pert.id
                    , ie.id, ie.institucioneducativa, jg.cordx, jg.cordy 
                    , sfat.id, sfat.codigo, sfat.facultad_area --, sest.id, sest.especialidad
                    , sat.id, sat.codigo, sat.acreditacion
                    , dt.id, dt.dependencia
                    , gt.id, gt.genero
                )

                select institucioneducativa_id as lugar_id, institucioneducativa_id as lugar_codigo, institucioneducativa_id as lugar_nombre, 1 as tipo_id, 'Sexo' as tipo_nombre, periodo_id, periodo
                , genero_id as id, genero as nombre, 0 as sub_id, '' as sub_nombre, sum(cantidad) as cantidad, cordx, cordy  
                from tabla as t 
                group by institucioneducativa_id, institucioneducativa, periodo_id, periodo, genero_id, genero, cordx, cordy 
                
                union all
                
                select institucioneducativa_id as lugar_id, institucioneducativa_id as lugar_codigo, institucioneducativa_id as lugar_nombre, 2 as tipo_id, 'Dependencia' as tipo_nombre, periodo_id, periodo
                , dependencia_id as id, dependencia as nombre, 0 as sub_id, '' as sub_nombre, sum(cantidad) as cantidad, cordx, cordy  
                from tabla as t 
                group by institucioneducativa_id, institucioneducativa_id, periodo_id, periodo, dependencia_id, dependencia, cordx, cordy 
                
                union all
                
                select institucioneducativa_id as lugar_id, institucioneducativa_id as lugar_codigo, institucioneducativa_id as lugar_nombre, 3 as tipo_id, 'Acreditación' as tipo_nombre, periodo_id, periodo
                , acreditacion_codigo as id, acreditacion as nombre, 0 as sub_id, '' as sub_nombre, sum(cantidad) as cantidad, cordx, cordy  
                from tabla as t 
                group by institucioneducativa_id, institucioneducativa_id, periodo_id, periodo, acreditacion_codigo, acreditacion, cordx, cordy 
                            
                order by tipo_id, id
            ");
            $query->bindValue(':area', $codigoArea);
        }

        $query->bindValue(':nivel', ($nivelArea + 1));
        $query->bindValue(':gestion', $gestion);
        $query->execute();
        $objEntidad = $query->fetchAll();

        $aDato = array();

        foreach ($objEntidad as $key => $dato) {
            $aDato[0]['nombre'] = $dato['lugar_nombre'];
            $aDato[0]['nivel_actual'] = $nivelArea;
            $aDato[0]['nivel'] = $nivelArea + 1;
            $aDato[0]['codigo'] = $codigoArea;
            if ($nivelArea == 4){
                $aDato[0]['cordx'] = $dato['cordx'];
                $aDato[0]['cordy'] = $dato['cordy'];
            }
            $aDato[$dato['periodo_id']][$dato['tipo_id']]['tipo'] = $dato['tipo_nombre'];
            if (isset($aDato[$dato['periodo_id']][$dato['tipo_id']]['dato'][0]['cantidad'])){
                $cantidadParcial = $aDato[$dato['periodo_id']][$dato['tipo_id']]['dato'][0]['cantidad'] + $dato['cantidad'];
            } else {
                $cantidadParcial = $dato['cantidad'];
            }
            $aDato[$dato['periodo_id']][$dato['tipo_id']]['dato'][0] = array('detalle'=>'Total','subdetalle'=>'', 'cantidad'=>$cantidadParcial);
            $aDato[$dato['periodo_id']][$dato['tipo_id']]['dato'][$dato['id']] = array('detalle'=>$dato['nombre'],'subdetalle'=>$dato['sub_nombre'], 'cantidad'=>$dato['cantidad']);
        }

        return $aDato;
    }
}
