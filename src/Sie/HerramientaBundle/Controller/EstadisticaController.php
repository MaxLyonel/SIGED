<?php

namespace Sie\HerramientaBundle\Controller;

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
use Sie\AppWebBundle\Controller\ChartController as ChartController;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Vista controller.
 *
 */
class EstadisticaController extends Controller {
    private $route_anterior;
    private $var_anterior;
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Pagina Inicial - Información General de Unidades Educativa - Bachillerato Tecnico Humanístico
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function bthUnidadEducativaIndexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime();
        $gestionActual = date_format($fechaActual,'Y');
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
        
        $chartController = new ChartController();
        $chartController->setContainer($this->container);

        $entidad = $chartController->buscaEntidadRol($codigoArea,$rolUsuario);
        $subEntidades = $this->buscaSubEntidadBthUnidadEducativaAreaRol($codigoArea,$rolUsuario,$gestionActual);
        $entityEstadistica = $this->buscaEstadisticaBthUnidadEducativaAreaRol($codigoArea,$rolUsuario,$gestionActual);
        //dump($entityEstadistica,$subEntidades,$entidad);die;
        if(count($subEntidades)>0 and isset($subEntidades)){
            foreach ($subEntidades as $key => $dato) {
                /* if(isset(reset($entityEstadistica)['dato'][0]['cantidad'])){             
                    $subEntidades[$key]['total_general'] = reset($entityEstadistica)['dato'][0]['cantidad'];
                } else {          
                    $subEntidades[$key]['total_general'] = 0;
                }  */
                $subEntidades[$key]['total_general'] = $entityEstadistica[0][0]['total'];
            }
        } else {
            $subEntidades = null;
        }

        $fechaEstadistica = $fechaActual->format('d-m-Y H:i:s');
        $gestionProcesada = $gestionActual;
        $link = true;
        if ($rolUsuario == 9){
            $link = false;
            $chartGrado = null;
            $chartDependencia = null;
            $chartEspecialidad = null;
            $chartArea = null;
        }else{
            $chartGrado = $chartController->chartDonut3d($entityEstadistica[3],"Unidades Educativas Plenas según el Máximo Año de Escolaridad Autorizado",$gestionProcesada,1,"chartContainerGrado");
            $chartDependencia = $chartController->chartColumn($entityEstadistica[1],"Unidades Educativas según Dependencia",$gestionProcesada,1,"chartContainerDependencia");
            $chartEspecialidad = $chartController->chartColumn($entityEstadistica[4],"Frecuencia de Especialidades en Unidades Educativas Plenas",$gestionProcesada,2,"chartContainerEspecialidad");
            $chartArea = $chartController->chartSemiPieDonut3d($entityEstadistica[2],"Unidades Educativas según Área Geográfica",$gestionProcesada,1,"chartContainerArea");
        }
        
        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $mensaje = "";
        if ($rolUsuario != 0){
            $mensaje = '$("#modal-bootstrap-tour").modal("hide");';
        }

        if ($entidad != '' and $subEntidades != ''){
            return $this->render('SieHerramientaBundle:Estadistica:bthUnidadEducativa.html.twig', array(
                'infoEntidad'=>$entidad,
                'infoSubEntidad'=>$subEntidades, 
                'infoEstadistica'=>$entityEstadistica,
                'datoGraficoGrado'=>$chartGrado,
                'datoGraficoDependencia'=>$chartDependencia,
                'datoGraficoArea'=>$chartArea,
                'datoGraficoEspecialidad'=>$chartEspecialidad,
                'gestion'=>$gestionProcesada,
                'link'=>$link,
                'mensaje'=>$mensaje,
                'fechaEstadistica'=>$fechaEstadistica,
                'form' => $defaultController->createLoginForm()->createView()
            ));    
        } else {
            if ($entidad != ''){
                return $this->render('SieHerramientaBundle:Estadistica:bthUnidadEducativa.html.twig', array(
                    'infoEntidad'=>$entidad, 
                    'infoEstadistica'=>$entityEstadistica,                
                    'datoGraficoGrado'=>$chartGrado,
                    'datoGraficoDependencia'=>$chartDependencia,
                    'datoGraficoArea'=>$chartArea,
                    'datoGraficoEspecialidad'=>$chartEspecialidad,
                    'gestion'=>$gestionProcesada,
                     'link'=>$link,
                     'mensaje'=>$mensaje,
                    'fechaEstadistica'=>$fechaEstadistica,
                    'form' => $defaultController->createLoginForm()->createView()
                ));  
            } else {
                return $this->render('SieHerramientaBundle:Estadistica:bthUnidadEducativa.html.twig', array(
                    'infoSubEntidad'=>$subEntidades, 
                    'infoEstadistica'=>$entityEstadistica,                    
                    'datoGraficoGrado'=>$chartGrado,
                    'datoGraficoDependencia'=>$chartDependencia,
                    'datoGraficoArea'=>$chartArea,
                    'datoGraficoEspecialidad'=>$chartEspecialidad,
                    'gestion'=>$gestionProcesada,
                    'link'=>$link,
                    'mensaje'=>$mensaje,
                    'fechaEstadistica'=>$fechaEstadistica,
                    'form' => $defaultController->createLoginForm()->createView()
                ));  
            }            
        }        
    }
    
    /**
     * Pagina Inicial - Información General de Matricula Educativa - Bachillerato Tecnico Humanístico
     * Pvargas
     * @param Request $request
     * @return type
     */
    public function bthMatriculaIndexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime();
        $gestionActual = date_format($fechaActual,'Y');
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
        
        $chartController = new ChartController();
        $chartController->setContainer($this->container);

        $entidad = $chartController->buscaEntidadRol($codigoArea,$rolUsuario);
        $subEntidades = $this->buscaSubEntidadBthMatriculaAreaRol($codigoArea,$rolUsuario,$gestionActual);
        //dump($entidad,$subEntidades);die;
        $entityEstadistica = $this->buscaEstadisticaBthMatriculaAreaRol($codigoArea,$rolUsuario,$gestionActual);
        //dump($entityEstadistica,$subEntidades,$entidad);die;
        if(count($subEntidades)>0 and isset($subEntidades)){
            foreach ($subEntidades as $key => $dato) {
                $subEntidades[$key]['total_general'] = $entityEstadistica[0][0]['total'];
            }
        } else {
            $subEntidades = null;
        }

        $fechaEstadistica = $fechaActual->format('d-m-Y H:i:s');
        $gestionProcesada = $gestionActual;

        $chartGrado = $chartController->chartDonut3d($entityEstadistica[3],"Estudiantes Matriculados en Unidades Educativas Plenas según grado de Escolaridad",$gestionProcesada,2,"chartContainerGrado");
        $chartGenero = $chartController->chartPie($entityEstadistica[4],"Estudiantes Matriculados en Unidades Educativas Plenas según Género",$gestionProcesada,1,"chartContainerGenero");
        $chartDependencia = $chartController->chartColumn($entityEstadistica[1],"Estudiantes Matriculados según Dependencia",$gestionProcesada,3,"chartContainerDependencia");
        $chartEspecialidad = $chartController->chartColumn($entityEstadistica[5],"Estudiantes Matriculados segun Frecuencia de Especialidades en Unidades Educativas Plenas",$gestionProcesada,4,"chartContainerEspecialidad");
        $chartArea = $chartController->chartSemiPieDonut3d($entityEstadistica[2],"Estudiantes Matriculados según Área Geográfica",$gestionProcesada,2,"chartContainerArea");
        $link = true;
        if ($rolUsuario == 10){
            $link = false;
        }
        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $mensaje = "";
        if ($rolUsuario != 0){
            $mensaje = '$("#modal-bootstrap-tour").modal("hide");';
        }

        if ($entidad != '' and $subEntidades != ''){
            return $this->render('SieHerramientaBundle:Estadistica:bthMatriculaEducativa.html.twig', array(
                'infoEntidad'=>$entidad,
                'infoSubEntidad'=>$subEntidades, 
                'infoEstadistica'=>$entityEstadistica,
                'datoGraficoGrado'=>$chartGrado,
                'datoGraficoDependencia'=>$chartDependencia,
                'datoGraficoArea'=>$chartArea,
                'datoGraficoEspecialidad'=>$chartEspecialidad,
                'datoGraficoGenero'=>$chartGenero,
                'gestion'=>$gestionProcesada,
                'link'=>$link,
                'mensaje'=>$mensaje,
                'fechaEstadistica'=>$fechaEstadistica,
                'form' => $defaultController->createLoginForm()->createView()
            ));    
        } else {
            if ($entidad != ''){
                return $this->render('SieHerramientaBundle:Estadistica:bthMatriculaEducativa.html.twig', array(
                    'infoEntidad'=>$entidad, 
                    'infoEstadistica'=>$entityEstadistica,                
                    'datoGraficoGrado'=>$chartGrado,
                    'datoGraficoDependencia'=>$chartDependencia,
                    'datoGraficoArea'=>$chartArea,
                    'datoGraficoEspecialidad'=>$chartEspecialidad,
                    'datoGraficoGenero'=>$chartGenero,
                    'gestion'=>$gestionProcesada,
                     'link'=>$link,
                     'mensaje'=>$mensaje,
                    'fechaEstadistica'=>$fechaEstadistica,
                    'form' => $defaultController->createLoginForm()->createView()
                ));  
            } else {
                return $this->render('SieHerramientaBundle:Estadistica:bthMatriculaEducativa.html.twig', array(
                    'infoSubEntidad'=>$subEntidades, 
                    'infoEstadistica'=>$entityEstadistica,                    
                    'datoGraficoGrado'=>$chartGrado,
                    'datoGraficoDependencia'=>$chartDependencia,
                    'datoGraficoArea'=>$chartArea,
                    'datoGraficoEspecialidad'=>$chartEspecialidad,
                    'datoGraficoGenero'=>$chartGenero,
                    'gestion'=>$gestionProcesada,
                    'link'=>$link,
                    'mensaje'=>$mensaje,
                    'fechaEstadistica'=>$fechaEstadistica,
                    'form' => $defaultController->createLoginForm()->createView()
                ));  
            }            
        }        
    }


    /**
     * Pagina Inicial - Información General de Unidades Educativa - Bachillerato Modular Multigrado
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function modularUnidadEducativaIndexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime();
        $gestionActual = date_format($fechaActual,'Y');
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

        $chartController = new ChartController();
        $chartController->setContainer($this->container);

        $entidad = $chartController->buscaEntidadRol($codigoArea,$rolUsuario);
        $subEntidades = $this->buscaSubEntidadModularUnidadEducativaAreaRol($codigoArea,$rolUsuario,$gestionActual);
        $entityEstadistica = $this->buscaEstadisticaModularUnidadEducativaAreaRol($codigoArea,$rolUsuario,$gestionActual);
        //dump($entityEstadistica);dump($subEntidades);
        if(count($subEntidades)>0 and isset($subEntidades)){
            foreach ($subEntidades as $key => $dato) {
                if(isset(reset($entityEstadistica)['dato'][0]['cantidad'])){             
                    $subEntidades[$key]['total_general'] = reset($entityEstadistica)['dato'][0]['cantidad'];
                } else {          
                    $subEntidades[$key]['total_general'] = 0;
                } 
            }
        } else {
            $subEntidades = null;
        }

        //dump($subEntidades);die;
        
        $fechaEstadistica = $fechaActual->format('d-m-Y H:i:s');;
        $gestionProcesada = $gestionActual;
        
        if(count($entityEstadistica)>0){
            $chartDependencia = $chartController->chartPie($entityEstadistica[2],"Unidades Educativas según Dependencia",$gestionProcesada,"Unidades Educativas","chartContainerDependencia");
            $chartArea = $chartController->chartSemiPieDonut3d($entityEstadistica[3],"Unidades Educativas según Área Geográfica",$gestionProcesada,"Unidades Educativas","chartContainerArea");
        } else {
            $chartDependencia = "";
            $chartArea = "";
        }
        
        $link = true;
        if ($rolUsuario == 10){
            $link = false;
        }

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $mensaje = "";
        if ($rolUsuario != 0){
            $mensaje = '$("#modal-bootstrap-tour").modal("hide");';
        }

        if ($entidad != '' and $subEntidades != ''){
            return $this->render('SieHerramientaBundle:Estadistica:modularUnidadEducativa.html.twig', array(
                'infoEntidad'=>$entidad,
                'infoSubEntidad'=>$subEntidades, 
                'infoEstadistica'=>$entityEstadistica,
                'datoGraficoDependencia'=>$chartDependencia,
                'datoGraficoArea'=>$chartArea,
                'gestion'=>$gestionProcesada,
                'link'=>$link,
                'mensaje'=>$mensaje,
                'fechaEstadistica'=>$fechaEstadistica,
                'form' => $defaultController->createLoginForm()->createView()
            ));    
        } else {
            if ($entidad != ''){
                return $this->render('SieHerramientaBundle:Estadistica:modularUnidadEducativa.html.twig', array(
                    'infoEntidad'=>$entidad, 
                    'infoEstadistica'=>$entityEstadistica,      
                    'datoGraficoDependencia'=>$chartDependencia,
                    'datoGraficoArea'=>$chartArea,
                    'gestion'=>$gestionProcesada,
                     'link'=>$link,
                     'mensaje'=>$mensaje,
                    'fechaEstadistica'=>$fechaEstadisticaRegular,
                    'form' => $defaultController->createLoginForm()->createView()
                ));  
            } else {
                return $this->render('SieHerramientaBundle:Estadistica:modularUnidadEducativa.html.twig', array(
                    'infoSubEntidad'=>$subEntidades, 
                    'infoEstadistica'=>$entityEstadistica,    
                    'datoGraficoDependencia'=>$chartDependencia,
                    'datoGraficoArea'=>$chartArea,
                    'gestion'=>$gestionProcesada,
                    'link'=>$link,
                    'mensaje'=>$mensaje,
                    'fechaEstadistica'=>$fechaEstadisticaRegular,
                    'form' => $defaultController->createLoginForm()->createView()
                ));  
            }            
        }        
    }  

    /**
     * Busca el nombre de las entidades pertenecienten a un pais, departamento, distrito en funcion al tipo de rol
     * Pvargas
     * @param Request $request
     * @return type
     */
     public function buscaSubEntidadBthMatriculaAreaRol($area,$rol,$gestion) {

        $em = $this->getDoctrine()->getManager();

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {                 
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("                    
            SELECT 'Unidad Educativa' as nombreArea, f.cod_ue_id as codigo,f.cod_ue_id||' - ' ||f.desc_ue as nombre,9 as rolUsuario, coalesce(count(a.id),0) as cantidad
            FROM estudiante a
            INNER JOIN estudiante_inscripcion b ON b.estudiante_id = a.id
            INNER JOIN estudiante_asignatura c ON c.estudiante_inscripcion_id = b.id
            INNER JOIN institucioneducativa_curso d ON b.institucioneducativa_curso_id = d.id
            INNER JOIN institucioneducativa_curso_oferta e ON e.insitucioneducativa_curso_id = d.id AND c.institucioneducativa_curso_oferta_id = e.id
            INNER JOIN (
            select  a.id as cod_ue_id,a.institucioneducativa as desc_ue,a.estadoinstitucion_tipo_id
            ,d.cod_dep as id_departamento,d.des_dep as desc_departamento
            ,d.area2001 as tipo_area,d.cod_dis as cod_distrito,d.des_dis as distrito, f.grado_tipo_id
            from institucioneducativa a 
            inner join 
            (select a.id as cod_le,cod_dep,des_dep,area2001,cod_dis,des_dis
            from jurisdiccion_geografica a inner join 
            (select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001
            from (select id,codigo as cod_dep,lugar_tipo_id,lugar
            from lugar_tipo
            where lugar_nivel_id=1) a inner join (
            select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo
            where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id inner join (
            select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo
            where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id inner join (
            select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo
            where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id inner join (
            select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo
            where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id) b on a.lugar_tipo_id_localidad=b.id
            inner join 
            (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo
            where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id) d on a.le_juridicciongeografica_id=d.cod_le
            INNER JOIN (select institucioneducativa_id, grado_tipo_id
            FROM institucioneducativa_humanistico_tecnico
            where gestion_tipo_id = ". $gestion ." and
            grado_tipo_id in (3,4,5,6) and 
            institucioneducativa_humanistico_tecnico_tipo_id in (1,7)) f on a.id = f.institucioneducativa_id 
            WHERE a.institucioneducativa_tipo_id =1
            AND a.estadoinstitucion_tipo_id = 10
            AND a.institucioneducativa_acreditacion_tipo_id = 1 ) f on f.cod_ue_id = d.institucioneducativa_id
            WHERE d.gestion_tipo_id = ". $gestion ." AND
            e.asignatura_tipo_id IN (1038, 1039) AND
            d.nivel_tipo_id = 13 AND
            d.grado_tipo_id IN (3, 4, 5, 6) AND
            b.estadomatricula_tipo_id IN (4,5,55)
            AND f.cod_distrito='". $area ."'
            group by f.cod_ue_id,f.desc_ue
            order by codigo
            ");
        }  

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
            SELECT 'Distrito Educativo' as nombreArea, f.cod_distrito as codigo,f.distrito as nombre,10 as rolUsuario, coalesce(count(a.id),0) as cantidad
            FROM estudiante a
            INNER JOIN estudiante_inscripcion b ON b.estudiante_id = a.id
            INNER JOIN estudiante_asignatura c ON c.estudiante_inscripcion_id = b.id
            INNER JOIN institucioneducativa_curso d ON b.institucioneducativa_curso_id = d.id
            INNER JOIN institucioneducativa_curso_oferta e ON e.insitucioneducativa_curso_id = d.id AND c.institucioneducativa_curso_oferta_id = e.id
            INNER JOIN (
            select  a.id as cod_ue_id,a.institucioneducativa as desc_ue,a.estadoinstitucion_tipo_id
            ,d.cod_dep as id_departamento,d.des_dep as desc_departamento
            ,d.area2001 as tipo_area,d.cod_dis as cod_distrito,d.des_dis as distrito, f.grado_tipo_id
            from institucioneducativa a 
            inner join 
            (select a.id as cod_le,cod_dep,des_dep,area2001,cod_dis,des_dis
            from jurisdiccion_geografica a inner join 
            (select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001
            from (select id,codigo as cod_dep,lugar_tipo_id,lugar
            from lugar_tipo
            where lugar_nivel_id=1) a inner join (
            select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo
            where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id inner join (
            select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo
            where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id inner join (
            select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo
            where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id inner join (
            select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo
            where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id) b on a.lugar_tipo_id_localidad=b.id
            inner join 
            (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo
            where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id) d on a.le_juridicciongeografica_id=d.cod_le
            INNER JOIN (select institucioneducativa_id, grado_tipo_id
            FROM institucioneducativa_humanistico_tecnico
            where gestion_tipo_id = ". $gestion ." and
            grado_tipo_id in (3,4,5,6) and 
            institucioneducativa_humanistico_tecnico_tipo_id in (1,7)) f on a.id = f.institucioneducativa_id 
            WHERE a.institucioneducativa_tipo_id =1
            AND a.estadoinstitucion_tipo_id = 10
            AND a.institucioneducativa_acreditacion_tipo_id = 1 ) f on f.cod_ue_id = d.institucioneducativa_id
            WHERE d.gestion_tipo_id = ". $gestion ." AND
            e.asignatura_tipo_id IN (1038, 1039) AND
            d.nivel_tipo_id = 13 AND
            d.grado_tipo_id IN (3, 4, 5, 6) AND
            b.estadomatricula_tipo_id IN (4,5,55)
            AND f.id_departamento='". $area ."'
            group by f.cod_distrito,f.distrito
            order by codigo
            ");  
        } 

        if($rol == 8 or $rol == 20 or $rol == 0) // Tecnico Nacional
        {
            
            $queryEntidad = $em->getConnection()->prepare("
            SELECT 'Departamento' as nombreArea, f.id_departamento as codigo,f.desc_departamento as nombre,7 as rolUsuario, count(a.id) as cantidad
            FROM estudiante a
            INNER JOIN estudiante_inscripcion b ON b.estudiante_id = a.id
            INNER JOIN estudiante_asignatura c ON c.estudiante_inscripcion_id = b.id
            INNER JOIN institucioneducativa_curso d ON b.institucioneducativa_curso_id = d.id
            INNER JOIN institucioneducativa_curso_oferta e ON e.insitucioneducativa_curso_id = d.id AND c.institucioneducativa_curso_oferta_id = e.id
            INNER JOIN (
            select  a.id as cod_ue_id,a.institucioneducativa as desc_ue,a.estadoinstitucion_tipo_id
            ,d.cod_dep as id_departamento,d.des_dep as desc_departamento
            ,d.area2001 as tipo_area,d.cod_dis as cod_distrito,d.des_dis as distrito, f.grado_tipo_id
            from institucioneducativa a 
            inner join 
            (select a.id as cod_le,cod_dep,des_dep,area2001,cod_dis,des_dis
            from jurisdiccion_geografica a inner join 
            (select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001
            from (select id,codigo as cod_dep,lugar_tipo_id,lugar
            from lugar_tipo
            where lugar_nivel_id=1) a inner join (
            select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo
            where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id inner join (
            select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo
            where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id inner join (
            select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo
            where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id inner join (
            select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo
            where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id) b on a.lugar_tipo_id_localidad=b.id
            inner join 
            (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo
            where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id) d on a.le_juridicciongeografica_id=d.cod_le
            INNER JOIN (select institucioneducativa_id, grado_tipo_id
            FROM institucioneducativa_humanistico_tecnico
            where gestion_tipo_id = ". $gestion ." and
            grado_tipo_id in (3,4,5,6) and 
            institucioneducativa_humanistico_tecnico_tipo_id in (1,7)) f on a.id = f.institucioneducativa_id 
            WHERE a.institucioneducativa_tipo_id =1
            AND a.estadoinstitucion_tipo_id = 10
            AND a.institucioneducativa_acreditacion_tipo_id = 1 ) f on f.cod_ue_id = d.institucioneducativa_id
            WHERE d.gestion_tipo_id = ". $gestion ." AND
            e.asignatura_tipo_id IN (1038, 1039) AND
            d.nivel_tipo_id = 13 AND
            d.grado_tipo_id IN (3, 4, 5, 6) AND
            b.estadomatricula_tipo_id IN (4,5,55)
            group by f.id_departamento,f.desc_departamento
            order by codigo
            ");    
        } 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 
        if (count($objEntidad)>0 and $rol != 9 and $rol != 5){
            return $objEntidad;
        } else {
            return array();
        }
    }

    /**
     * Busca el nombre de las entidades pertenecienten a un pais, departamento, distrito en funcion al tipo de rol
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaSubEntidadBthUnidadEducativaAreaRol($area,$rol,$gestion) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
            select 'Departamento' as nombreArea, dt.id as codigo, dt.departamento as nombre, 7 as rolUsuario, coalesce(v.cantidad,0) as cantidad from (
                select  d.cod_dep as id_departamento, d.des_dep as desc_departamento, count(institucioneducativa_id) as cantidad
                from institucioneducativa a  
                inner join  (
                    select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona 
                    from jurisdiccion_geografica a 
                    inner join  (
                        select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001 
                        from (select id,codigo as cod_dep,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=1) a 
                        inner join ( select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                        inner join ( select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                        inner join ( select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id 
                        inner join ( select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                    ) b on a.lugar_tipo_id_localidad=b.id 
                    inner join  (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                ) d on a.le_juridicciongeografica_id=d.cod_le 
                INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id 
                INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id 
                INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id 
                INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id 
                inner join (
                    select * from institucioneducativa_humanistico_tecnico 
                    where gestion_tipo_id = ".$gestion." and institucioneducativa_humanistico_tecnico_tipo_id in (1,7)
                ) i on a.id = i.institucioneducativa_id 
                WHERE a.institucioneducativa_acreditacion_tipo_id = 1 AND a.estadoinstitucion_tipo_id = 10
                group by d.cod_dep, d.des_dep
            ) as v
            right join (select * from departamento_tipo where id not in (0)) as dt on dt.id = v.id_departamento::integer
            order by dt.id, dt.departamento
        "); 

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {                 
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("                    
                    select 'Unidad Educativa' as nombreArea, a.id as codigo, a.id::varchar||' - '||a.institucioneducativa  as nombre, 9 as rolUsuario, coalesce(count(institucioneducativa_id),0) as cantidad
                    from institucioneducativa a  
                    inner join  (
                        select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona 
                        from jurisdiccion_geografica a 
                        inner join  (
                            select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001 
                            from (select id,codigo as cod_dep,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=1) a 
                            inner join ( select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                            inner join ( select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                            inner join ( select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id 
                            inner join ( select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                        ) b on a.lugar_tipo_id_localidad=b.id 
                        inner join  (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                    ) d on a.le_juridicciongeografica_id=d.cod_le 
                    INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id 
                    INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id 
                    INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id 
                    INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id 
                    inner join (
                        select * from institucioneducativa_humanistico_tecnico 
                        where gestion_tipo_id = ".$gestion." and institucioneducativa_humanistico_tecnico_tipo_id in (1,7)
                    ) i on a.id = i.institucioneducativa_id 
                    WHERE a.institucioneducativa_acreditacion_tipo_id = 1 AND a.estadoinstitucion_tipo_id = 10 and d.cod_dis = '".$area."'
                    group by a.id, a.institucioneducativa
                    order by a.id, a.institucioneducativa
                ");  
        }  

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 'Distrito Educativo' as nombreArea, d.cod_dis as codigo, d.des_dis as nombre, 10 as rolUsuario, coalesce(count(institucioneducativa_id),0) as cantidad
                    from institucioneducativa a  
                    inner join  (
                        select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona 
                        from jurisdiccion_geografica a 
                        inner join  (
                            select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001 
                            from (select id,codigo as cod_dep,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=1) a 
                            inner join ( select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                            inner join ( select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                            inner join ( select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id 
                            inner join ( select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                        ) b on a.lugar_tipo_id_localidad=b.id 
                        inner join  (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                    ) d on a.le_juridicciongeografica_id=d.cod_le 
                    INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id 
                    INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id 
                    INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id 
                    INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id 
                    inner join (
                        select * from institucioneducativa_humanistico_tecnico 
                        where gestion_tipo_id = ".$gestion." and institucioneducativa_humanistico_tecnico_tipo_id in (1,7)
                    ) i on a.id = i.institucioneducativa_id 
                    WHERE a.institucioneducativa_acreditacion_tipo_id = 1 AND a.estadoinstitucion_tipo_id = 10 and d.cod_dep = '".$area."'
                    group by d.cod_dis, d.des_dis
                    order by d.cod_dis, d.des_dis
                ");  
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $queryEntidad = $em->getConnection()->prepare("
                select 'Departamento' as nombreArea, dt.id as codigo, dt.departamento as nombre, 7 as rolUsuario, coalesce(v.cantidad,0) as cantidad from (
                    select  d.cod_dep as id_departamento, d.des_dep as desc_departamento, count(institucioneducativa_id) as cantidad
                    from institucioneducativa a  
                    inner join  (
                        select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona 
                        from jurisdiccion_geografica a 
                        inner join  (
                            select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001 
                            from (select id,codigo as cod_dep,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=1) a 
                            inner join ( select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                            inner join ( select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                            inner join ( select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id 
                            inner join ( select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                        ) b on a.lugar_tipo_id_localidad=b.id 
                        inner join  (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                    ) d on a.le_juridicciongeografica_id=d.cod_le 
                    INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id 
                    INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id 
                    INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id 
                    INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id 
                    inner join (
                        select * from institucioneducativa_humanistico_tecnico 
                        where gestion_tipo_id = ".$gestion." and institucioneducativa_humanistico_tecnico_tipo_id in (1,7)
                    ) i on a.id = i.institucioneducativa_id 
                    WHERE a.institucioneducativa_acreditacion_tipo_id = 1 AND a.estadoinstitucion_tipo_id = 10
                    group by d.cod_dep, d.des_dep
                ) as v
                right join (select * from departamento_tipo where id not in (0)) as dt on dt.id = v.id_departamento::integer
                order by dt.id, dt.departamento
            ");    
        } 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 
        if (count($objEntidad)>0 and $rol != 9 and $rol != 5){
            return $objEntidad;
        } else {
            return array();
        }
    }


    /**
     * Busca el detalle de Unidades Educativas BTH plenas en funcion al tipo de rol - Educacion Especial
     * Jurlan edit Pvargas
     * @param Request $request
     * @return type
     */
    public function buscaEstadisticaBthUnidadEducativaAreaRol($area,$rol,$gestion) {

        $em = $this->getDoctrine()->getManager();

        /* $queryEntidad = $em->getConnection()->prepare("
            with tabla as (
                select a.dependencia_tipo_id as dependencia_id, e.dependencia as dependencia, d.area2001 as area, i.grado_tipo_id as grado_id, count(a.id) as cantidad
                from institucioneducativa a  
                inner join  (
                    select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona 
                    from jurisdiccion_geografica a 
                    inner join  (
                        select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001 
                        from (select id,codigo as cod_dep,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=1) a 
                        inner join ( select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                        inner join ( select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                        inner join ( select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id 
                        inner join ( select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                    ) b on a.lugar_tipo_id_localidad=b.id 
                    inner join  (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                ) d on a.le_juridicciongeografica_id=d.cod_le 
                INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id 
                INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id 
                INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id 
                INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id 
                inner join (
                    select * from institucioneducativa_humanistico_tecnico 
                    where gestion_tipo_id = ".$gestion." and institucioneducativa_humanistico_tecnico_tipo_id in (1,7)
                ) i on a.id = i.institucioneducativa_id 
                WHERE a.institucioneducativa_acreditacion_tipo_id = 1 AND a.estadoinstitucion_tipo_id = 10 -- and d.cod_dis = '2085'
                GROUP BY a.dependencia_tipo_id, e.dependencia, d.area2001, i.grado_tipo_id
            )
            
            select 1 as tipo_id, 'Grado Máximo Autorizado' as tipo_nombre, gt.id as id, gt.grado as nombre
            , sum(cantidad) as cantidad from tabla as t 
            inner join grado_tipo as gt on gt.id = t.grado_id
            group by gt.id, gt.grado
            
            union all
            
            select 2 as tipo_id, 'Dependencia' as tipo_nombre, t.dependencia_id as id, t.dependencia as nombre
            , sum(cantidad) as cantidad from tabla as t 
            group by t.dependencia_id, t.dependencia
            
            union all
            
            select 3 as tipo_id, 'Área Geográfica' as tipo_nombre, case t.area when 'U' then 1 when 'R' then 2 else 0 end as id, case t.area when 'U' then 'Urbano' when 'R' then 'Rural' else '' end as nombre
            , sum(cantidad) as cantidad from tabla as t 
            group by t.area
            
            order by tipo_id, id
             
        "); 

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
            $queryEntidad = $em->getConnection()->prepare("
                with tabla as (
                    select a.dependencia_tipo_id as dependencia_id, e.dependencia as dependencia, d.area2001 as area, i.grado_tipo_id as grado_id, count(a.id) as cantidad
                    from institucioneducativa a  
                    inner join  (
                        select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona 
                        from jurisdiccion_geografica a 
                        inner join  (
                            select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001 
                            from (select id,codigo as cod_dep,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=1) a 
                            inner join ( select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                            inner join ( select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                            inner join ( select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id 
                            inner join ( select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                        ) b on a.lugar_tipo_id_localidad=b.id 
                        inner join  (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                    ) d on a.le_juridicciongeografica_id=d.cod_le 
                    INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id 
                    INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id 
                    INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id 
                    INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id 
                    inner join (
                        select * from institucioneducativa_humanistico_tecnico 
                        where gestion_tipo_id = ".$gestion." and institucioneducativa_humanistico_tecnico_tipo_id in (1,7)
                    ) i on a.id = i.institucioneducativa_id 
                    WHERE a.institucioneducativa_acreditacion_tipo_id = 1 AND a.estadoinstitucion_tipo_id = 10 and a.id = ".$area."
                    GROUP BY a.dependencia_tipo_id, e.dependencia, d.area2001, i.grado_tipo_id
                )
                
                select 1 as tipo_id, 'Grado Máximo Autorizado' as tipo_nombre, gt.id as id, gt.grado as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join grado_tipo as gt on gt.id = t.grado_id
                group by gt.id, gt.grado
                
                union all
                
                select 2 as tipo_id, 'Dependencia' as tipo_nombre, t.dependencia_id as id, t.dependencia as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.dependencia_id, t.dependencia
                
                union all
                
                select 3 as tipo_id, 'Área Geográfica' as tipo_nombre, case t.area when 'U' then 1 when 'R' then 2 else 0 end as id, case t.area when 'U' then 'Urbano' when 'R' then 'Rural' else '' end as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.area
                
                order by tipo_id, id
            ");     
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("    
                with tabla as (
                    select a.dependencia_tipo_id as dependencia_id, e.dependencia as dependencia, d.area2001 as area, i.grado_tipo_id as grado_id, count(a.id) as cantidad
                    from institucioneducativa a  
                    inner join  (
                        select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona 
                        from jurisdiccion_geografica a 
                        inner join  (
                            select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001 
                            from (select id,codigo as cod_dep,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=1) a 
                            inner join ( select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                            inner join ( select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                            inner join ( select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id 
                            inner join ( select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                        ) b on a.lugar_tipo_id_localidad=b.id 
                        inner join  (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                    ) d on a.le_juridicciongeografica_id=d.cod_le 
                    INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id 
                    INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id 
                    INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id 
                    INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id 
                    inner join (
                        select * from institucioneducativa_humanistico_tecnico 
                        where gestion_tipo_id = ".$gestion." and institucioneducativa_humanistico_tecnico_tipo_id in (1,7)
                    ) i on a.id = i.institucioneducativa_id 
                    WHERE a.institucioneducativa_acreditacion_tipo_id = 1 AND a.estadoinstitucion_tipo_id = 10 and d.cod_dis = '".$area."'
                    GROUP BY a.dependencia_tipo_id, e.dependencia, d.area2001, i.grado_tipo_id
                )
                
                select 1 as tipo_id, 'Grado Máximo Autorizado' as tipo_nombre, gt.id as id, gt.grado as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join grado_tipo as gt on gt.id = t.grado_id
                group by gt.id, gt.grado
                
                union all
                
                select 2 as tipo_id, 'Dependencia' as tipo_nombre, t.dependencia_id as id, t.dependencia as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.dependencia_id, t.dependencia
                
                union all
                
                select 3 as tipo_id, 'Área Geográfica' as tipo_nombre, case t.area when 'U' then 1 when 'R' then 2 else 0 end as id, case t.area when 'U' then 'Urbano' when 'R' then 'Rural' else '' end as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.area
                
                order by tipo_id, id
            ");  
        }  

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                with tabla as (
                    select a.dependencia_tipo_id as dependencia_id, e.dependencia as dependencia, d.area2001 as area, i.grado_tipo_id as grado_id, count(a.id) as cantidad
                    from institucioneducativa a  
                    inner join  (
                        select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona 
                        from jurisdiccion_geografica a 
                        inner join  (
                            select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001 
                            from (select id,codigo as cod_dep,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=1) a 
                            inner join ( select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                            inner join ( select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                            inner join ( select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id 
                            inner join ( select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                        ) b on a.lugar_tipo_id_localidad=b.id 
                        inner join  (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                    ) d on a.le_juridicciongeografica_id=d.cod_le 
                    INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id 
                    INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id 
                    INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id 
                    INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id 
                    inner join (
                        select * from institucioneducativa_humanistico_tecnico 
                        where gestion_tipo_id = ".$gestion." and institucioneducativa_humanistico_tecnico_tipo_id in (1,7)
                    ) i on a.id = i.institucioneducativa_id 
                    WHERE a.institucioneducativa_acreditacion_tipo_id = 1 AND a.estadoinstitucion_tipo_id = 10 and d.cod_dep = '".$area."'
                    GROUP BY a.dependencia_tipo_id, e.dependencia, d.area2001, i.grado_tipo_id
                )
                
                select 1 as tipo_id, 'Grado Máximo Autorizado' as tipo_nombre, gt.id as id, gt.grado as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join grado_tipo as gt on gt.id = t.grado_id
                group by gt.id, gt.grado
                
                union all
                
                select 2 as tipo_id, 'Dependencia' as tipo_nombre, t.dependencia_id as id, t.dependencia as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.dependencia_id, t.dependencia
                
                union all
                
                select 3 as tipo_id, 'Área Geográfica' as tipo_nombre, case t.area when 'U' then 1 when 'R' then 2 else 0 end as id, case t.area when 'U' then 'Urbano' when 'R' then 'Rural' else '' end as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.area
                
                order by tipo_id, id
            ");  
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {            
            $queryEntidad = $em->getConnection()->prepare("
                with tabla as (
                    select a.dependencia_tipo_id as dependencia_id, e.dependencia as dependencia, d.area2001 as area, i.grado_tipo_id as grado_id, count(a.id) as cantidad
                    from institucioneducativa a  
                    inner join  (
                        select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona 
                        from jurisdiccion_geografica a 
                        inner join  (
                            select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001 
                            from (select id,codigo as cod_dep,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=1) a 
                            inner join ( select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                            inner join ( select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                            inner join ( select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id 
                            inner join ( select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                        ) b on a.lugar_tipo_id_localidad=b.id 
                        inner join  (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                    ) d on a.le_juridicciongeografica_id=d.cod_le 
                    INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id 
                    INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id 
                    INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id 
                    INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id 
                    inner join (
                        select * from institucioneducativa_humanistico_tecnico 
                        where gestion_tipo_id = ".$gestionActual." and institucioneducativa_humanistico_tecnico_tipo_id in (1,7)
                    ) i on a.id = i.institucioneducativa_id 
                    WHERE a.institucioneducativa_acreditacion_tipo_id = 1 AND a.estadoinstitucion_tipo_id = 10 -- and d.cod_dis = '2085'
                    GROUP BY a.dependencia_tipo_id, e.dependencia, d.area2001, i.grado_tipo_id
                )
                
                select 1 as tipo_id, 'Grado Máximo Autorizado' as tipo_nombre, gt.id as id, gt.grado as nombre
                , sum(cantidad) as cantidad from tabla as t 
                inner join grado_tipo as gt on gt.id = t.grado_id
                group by gt.id, gt.grado
                
                union all
                
                select 2 as tipo_id, 'Dependencia' as tipo_nombre, t.dependencia_id as id, t.dependencia as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.dependencia_id, t.dependencia
                
                union all
                
                select 3 as tipo_id, 'Área Geográfica' as tipo_nombre, case t.area when 'U' then 1 when 'R' then 2 else 0 end as id, case t.area when 'U' then 'Urbano' when 'R' then 'Rural' else '' end as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.area
                
                order by tipo_id, id
            ");    
        }  */
        if ($rol == 9){
            $query = $em->getConnection()->prepare("
            select distinct ie.id as codigo, ie.institucioneducativa as institucioneducativa, det.dependencia, eit.id as estadoinstitucion_id, eit.estadoinstitucion, dep.lugar as departamento, dis.lugar as distrito, jg.direccion as direccion
            , 'Educación '||(case oct.id when 2 then (case iena.nivel_tipo_id when 6 then 'Especial' else oct.orgcurricula end) else oct.orgcurricula end) as orgcurricular, loc.lugar as localidad, can.lugar as canton, sec.lugar as seccion
            , pro.lugar as provincia, jg.zona, c.director, jg.cordx, jg.cordy, loc.area, ien.nivel_autorizado
            , replace(replace(replace(replace(iet.turno, 'M', 'Mañana'), 'T', 'Tarde'), 'N', 'Noche'), '-', ', ') as turno,
                            esp.especialidad,esp.grado_autorizado
            from institucioneducativa as ie
            inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
            inner join ( SELECT id,codigo,lugar,lugar_tipo_id,area2001 AS area FROM lugar_tipo WHERE lugar_nivel_id = 5) loc ON loc.id = jg.lugar_tipo_id_localidad
            inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 4) can ON can.id = loc.lugar_tipo_id
            inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 3) sec ON sec.id = can.lugar_tipo_id
            inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 2) pro ON pro.id = sec.lugar_tipo_id
            inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 1) dep ON dep.id = pro.lugar_tipo_id
            inner join ( SELECT id,codigo,lugar,lugar_tipo_id FROM lugar_tipo WHERE lugar_tipo.lugar_nivel_id = 7) dis ON dis.id = jg.lugar_tipo_id_distrito  
            inner join dependencia_tipo as det on det.id = ie.dependencia_tipo_id
            inner join estadoinstitucion_tipo as eit on eit.id = ie.estadoinstitucion_tipo_id
            inner join orgcurricular_tipo as oct ON oct.id = ie.orgcurricular_tipo_id
            left join (select distinct institucioneducativa_id, nivel_tipo_id from institucioneducativa_nivel_autorizado where nivel_tipo_id = 6) as iena on iena.institucioneducativa_id = ie.id
            left join (select institucioneducativa_id, carnet as ci_director,paterno||' '||materno||' '||nombre as director,cargo_tipo_id as item_director from maestro_inscripcion a 
                inner join persona b on a.persona_id=b.id 
                where a.gestion_tipo_id in (select max(gestion_tipo_id) as gestion_tipo_id from maestro_inscripcion where institucioneducativa_id= ". $area ." and cargo_tipo_id in (1,12) and es_vigente_administrativo = 'true') and a.institucioneducativa_id= ". $area ." and cargo_tipo_id in (1,12) and a.es_vigente_administrativo = 'true'
            ) c on ie.id = c.institucioneducativa_id
            left join (
                select string_agg(distinct nt1.nivel,', ') as nivel_autorizado, institucioneducativa_id
                from institucioneducativa_nivel_autorizado as iena1
                left join nivel_tipo as nt1 on nt1.id = iena1.nivel_tipo_id
                where iena1.institucioneducativa_id = ". $area ."   
                group by iena1.institucioneducativa_id
            ) as ien on ien.institucioneducativa_id = ie.id
                            left join (
                select string_agg(distinct et.especialidad,', ') as especialidad, ieht.institucioneducativa_id,gt.grado as grado_autorizado
                from institucioneducativa_humanistico_tecnico ieht 
                                    inner join grado_tipo gt on gt.id=ieht.grado_tipo_id
                inner join institucioneducativa_especialidad_tecnico_humanistico ieeth on ieeth.institucioneducativa_id=ieht.institucioneducativa_id
                                    inner join especialidad_tecnico_humanistico_tipo et on et.id=ieeth.especialidad_tecnico_humanistico_tipo_id
                where ieht.institucioneducativa_id = ". $area ."   and ieht.institucioneducativa_humanistico_tecnico_tipo_id in (1,7) and ieht.gestion_tipo_id = ". $gestion ."
                group by ieht.institucioneducativa_id,gt.grado
            ) as esp on esp.institucioneducativa_id = ie.id
            left join (
                select string_agg(cast(vv.turno_id as varchar), '-' order by vv.turno_id) as turno_id, string_agg(vv.turno,'-' order by vv.turno_id) as turno, vv.institucioneducativa_id from (
                    select v.turno, case v.turno when 'M' then 1 when 'T' then 2 when 'N' then 3 else 0 end as turno_id, v.institucioneducativa_id from (
                    select unnest(string_to_array(string_agg(distinct case tt1.abrv when 'MTN' then 'M-T-N' when '.' then '' when 'MN' then 'M-N' else tt1.abrv end,'-'),'-','')) as turno, iec1.institucioneducativa_id from institucioneducativa_curso as iec1 
                    inner join estudiante_inscripcion as ei1 on ei1.institucioneducativa_curso_id = iec1.id
                    inner join turno_tipo as tt1 on tt1.id = iec1.turno_tipo_id
                    where iec1.gestion_tipo_id in (select max(iec2.gestion_tipo_id) as gestion_tipo_id from institucioneducativa_curso as iec2 where iec2.institucioneducativa_id = iec1.institucioneducativa_id ) 
                    group by iec1.institucioneducativa_id
                    ) as v
                    group by institucioneducativa_id, turno
                    order by turno_id
                ) as vv
                group by institucioneducativa_id
            ) as iet on iet.institucioneducativa_id = ie.id 
            where ie.institucioneducativa_acreditacion_tipo_id = 1 and ie.id = ". $area ."  
            order by orgcurricular, departamento, estadoinstitucion, seccion
            ");
            $query->execute();
            $aDato = $query->fetchAll();
        }else{
            $queryEntidad = $em->getConnection()->prepare("with tabla as (
                select ie.id,ie.institucioneducativa,ie.dependencia_tipo_id,dt.dependencia,case WHEN lt.area2001 ='R' then 'RURAL'ELSE 'URBANA'END as area2001,case WHEN lt.area2001 ='R' then 1 ELSE 2 END as area_id,ieht.grado_tipo_id,grado,et.especialidad,et.id as especialidad_id
                FROM institucioneducativa ie
                INNER JOIN jurisdiccion_geografica jurg ON ie.le_juridicciongeografica_id = jurg.id
                inner JOIN lugar_tipo lt ON lt.id = jurg.lugar_tipo_id_localidad
                inner join distrito_tipo ddt on ddt.id=jurg.distrito_tipo_id and CASE WHEN ". $area ." > 9 THEN  ddt.id = ". $area ." else ddt.id > 0 END
                inner join dependencia_tipo dt on dt.id=ie.dependencia_tipo_id
                inner join departamento_tipo dpt on ddt.departamento_tipo_id=dpt.id and CASE WHEN ". $area ." <> 0 and ". $area ."<10 THEN  dpt.id = ". $area ." ELSE dpt.id IN (1,2,3,4,5,6,7,8,9) END
                inner join institucioneducativa_humanistico_tecnico ieht on ie.id=ieht.institucioneducativa_id
                inner join grado_tipo gt on gt.id=ieht.grado_tipo_id
                inner join institucioneducativa_especialidad_tecnico_humanistico eth on ie.id=eth.institucioneducativa_id and ieht.gestion_tipo_id=eth.gestion_tipo_id
                inner join especialidad_tecnico_humanistico_tipo et on et.id=eth.especialidad_tecnico_humanistico_tipo_id
                WHERE ie.institucioneducativa_tipo_id =1
                AND ie.estadoinstitucion_tipo_id = 10
                AND ie.institucioneducativa_acreditacion_tipo_id = 1
                AND ieht.gestion_tipo_id=". $gestion ." and ieht.institucioneducativa_humanistico_tecnico_tipo_id in (1,7)
                
                )
                
                select 0 as tipo_id, 'total' as tipo_nombre,'total_general' as detalle,0 as id,count(DISTINCT id) as cantidad,count(DISTINCT id) as total
                from tabla
    
                UNION ALL
                select 1 as tipo_id, 'Dependencia' as tipo_nombre,dependencia as detalle,dependencia_tipo_id as id,count(DISTINCT id) as cantidad,(select count(DISTINCT id) as total from tabla)
                from tabla
                GROUP BY dependencia_tipo_id,dependencia
    
                UNION ALL
                select 2 as tipo_id, 'Area' as tipo_nombre,area2001 as detalle,area_id as id,count(DISTINCT id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from tabla
                GROUP BY area2001,area_id
    
                UNION ALL
                select 3 as tipo_id, 'Grado Máximo Autorizado' as tipo_nombre,grado as detalle,grado_tipo_id as id,count(DISTINCT id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from tabla
                GROUP BY grado_tipo_id,grado
    
                UNION ALL
                (select 4 as tipo_id, 'Especialidad' as tipo_nombre,especialidad as detalle,especialidad_id as id,count(id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from tabla
                GROUP BY especialidad,especialidad_id
                ORDER BY cantidad desc
                limit 20)

                UNION ALL
                select 4  as tipo_id, 'Especialidad' as tipo_nombre,'Otras' as detalle,0 as id,case when sum(case when id NOTNULL then 1 else 0 end) isnull then 0 else sum(case when id NOTNULL then 1 else 0 end) end as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from tabla
                where especialidad_id not in (select a.id from (select especialidad_id as id,count(id) as cantidad
                from tabla
                GROUP BY especialidad,especialidad_id
                ORDER BY cantidad desc
                limit 20)a)
    
                ORDER BY tipo_id
                ");
    
            $queryEntidad->execute();
            $objEntidad = $queryEntidad->fetchAll(); 
            $aDato = array();
            foreach ($objEntidad as $key => $dato) {
                $aDato[$dato['tipo_id']][]= $dato;
            }
        }
        
        /* foreach ($objEntidad as $key => $dato) {
            $aDato[$dato['tipo_id']]['tipo'] = $dato['tipo_nombre'];
            if (isset($aDato[$dato['tipo_id']]['dato'][0]['cantidad'])){
                $cantidadParcial = $aDato[$dato['tipo_id']]['dato'][0]['cantidad'] + $dato['cantidad'];
            } else {
                $cantidadParcial = $dato['cantidad'];
            }    
            $aDato[$dato['tipo_id']]['dato'][0] = array('detalle'=>'Total', 'cantidad'=>$cantidadParcial);    
            $aDato[$dato['tipo_id']]['dato'][$dato['id']] = array('detalle'=>$dato['nombre'], 'cantidad'=>$dato['cantidad']);  
        } */
        //dump($aDato);die;
        return $aDato;
    }

    /**
     * Busca el detalle de matricula BTH
     * Pvargas
     * @param Request $request
     * @return type
     */
     public function buscaEstadisticaBthMatriculaAreaRol($area,$rol,$gestion) {

        $em = $this->getDoctrine()->getManager();

        if ($rol != 9){
            $queryEntidad = $em->getConnection()->prepare("
            with tabla as (
                SELECT 
                f.cod_ue_id id,f.desc_ue institucioneducativa,f.cod_dependencia_id dependencia_tipo_id, f.dependencia, case WHEN f.tipo_area ='R' then 'RURAL'ELSE 'URBANA'END as area2001, case WHEN f.tipo_area ='R' then 1 ELSE 2 END as area_id, 
                a.genero_tipo_id, case when a.genero_tipo_id = 1 then 'MASCULINO' else 'FEMENINO' end genero, d.grado_tipo_id, g.grado,
                h.especialidad_tecnico_humanistico_tipo_id especialidad_id, i.especialidad
                FROM
                estudiante a
                INNER JOIN estudiante_inscripcion b ON b.estudiante_id = a.id
                INNER JOIN estudiante_asignatura c ON c.estudiante_inscripcion_id = b.id
                INNER JOIN institucioneducativa_curso d ON b.institucioneducativa_curso_id = d.id
                INNER JOIN institucioneducativa_curso_oferta e ON e.insitucioneducativa_curso_id = d.id AND c.institucioneducativa_curso_oferta_id = e.id
                INNER JOIN (
                select  a.id as cod_ue_id,a.institucioneducativa as desc_ue,a.estadoinstitucion_tipo_id
                ,a.dependencia_tipo_id as cod_dependencia_id, e.dependencia,d.cod_dep as id_departamento,d.des_dep as desc_departamento
                ,d.area2001 as tipo_area,d.cod_dis as cod_distrito,d.des_dis as distrito, f.grado_tipo_id
                from institucioneducativa a 
                inner join 
                (select a.id as cod_le,cod_dep,des_dep,area2001,cod_dis,des_dis
                from jurisdiccion_geografica a inner join 
                (select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001
                from (select id,codigo as cod_dep,lugar_tipo_id,lugar
                from lugar_tipo
                where lugar_nivel_id=1) a inner join (
                select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo
                where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id inner join (
                select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo
                where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id inner join (
                select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo
                where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id inner join (
                select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo
                where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id) b on a.lugar_tipo_id_localidad=b.id
                inner join 
                (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo
                where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id) d on a.le_juridicciongeografica_id=d.cod_le
                INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id
                INNER JOIN (select institucioneducativa_id, grado_tipo_id
                FROM institucioneducativa_humanistico_tecnico
                where gestion_tipo_id = ". $gestion ." and
                grado_tipo_id in (3,4,5,6) and 
                institucioneducativa_humanistico_tecnico_tipo_id in (1,7)) f on a.id = f.institucioneducativa_id 
                WHERE a.institucioneducativa_tipo_id =1
                AND a.estadoinstitucion_tipo_id = 10
                AND a.institucioneducativa_acreditacion_tipo_id = 1 ) f on f.cod_ue_id = d.institucioneducativa_id
                inner join grado_tipo g on g.id=d.grado_tipo_id
                left JOIN estudiante_inscripcion_humnistico_tecnico h on b.id = h.estudiante_inscripcion_id
                left join especialidad_tecnico_humanistico_tipo i on i.id=h.especialidad_tecnico_humanistico_tipo_id
                WHERE
                d.gestion_tipo_id = ". $gestion ." AND
                e.asignatura_tipo_id IN (1038, 1039) AND
                d.nivel_tipo_id = 13 AND
                d.grado_tipo_id IN (3, 4, 5, 6) AND
                b.estadomatricula_tipo_id IN (4,5,55) AND
                case when ". $area ."=0 then 1=1 when ". $area .">0 and ". $area ."<=9 then f.id_departamento = '". $area ."' when ". $area .">9 then f.cod_distrito='". $area ."' end
                )
                
                select 0 as tipo_id, 'total' as tipo_nombre,'total_general' as detalle,0 as id,count(*) as cantidad,count(*) as total
                from tabla
                
                UNION ALL
                select 1 as tipo_id, 'Dependencia' as tipo_nombre,dependencia as detalle,dependencia_tipo_id as id,count(*) as cantidad,(select count(*) as total
                from tabla)
                from tabla
                GROUP BY dependencia_tipo_id,dependencia
                
                UNION ALL
                select 2 as tipo_id, 'Area' as tipo_nombre,area2001 as detalle,area_id as id,count(*) as cantidad,(select count(*) as total
                from tabla)
                from tabla
                GROUP BY area2001,area_id
                
                UNION ALL
                (select 3 as tipo_id, 'Grado' as tipo_nombre,grado as detalle,grado_tipo_id as id,count(*) as cantidad,(select count(*) as total
                from tabla)
                from tabla
                GROUP BY grado_tipo_id,grado
                order by id)
                
                UNION ALL
                select 4 as tipo_id, 'Genero' as tipo_nombre,genero as detalle,genero_tipo_id as id,count(*) as cantidad,(select count(*) as total
                from tabla)
                from tabla
                GROUP BY genero_tipo_id,genero
                
                UNION ALL
                (select 5 as tipo_id, 'Especialidad' as tipo_nombre,especialidad as detalle,especialidad_id as id,count(*) as cantidad,(select count(*) as total
                from tabla)
                from tabla
                where grado_tipo_id in (5,6)
                GROUP BY especialidad,especialidad_id
                ORDER BY cantidad desc
                limit 20)
                
                UNION ALL 
                (select 5  as tipo_id, 'Especialidad' as tipo_nombre,'Otros' as detalle,0 as id,case when sum(case when id NOTNULL then 1 else 0 end) isnull then 0 else sum(case when id NOTNULL then 1 else 0 end) end as cantidad,(select count(*) as total
                from tabla)
                from tabla
                where especialidad_id not in (select a.id from (select especialidad_id as id,count(*) as cantidad
                from tabla
                where grado_tipo_id in (5,6)
                GROUP BY especialidad,especialidad_id
                ORDER BY cantidad desc
                limit 20)a) and grado_tipo_id in (5,6))
                
                ORDER BY tipo_id
                ");
    
            $queryEntidad->execute();
            $objEntidad = $queryEntidad->fetchAll(); 
            $aDato = array();
            foreach ($objEntidad as $key => $dato) {
                $aDato[$dato['tipo_id']][]= $dato;
            }
        }
        
        return $aDato;
    }



    /**
     * Busca el nombre de las entidades pertenecienten a un pais, departamento, distrito en funcion al tipo de rol
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaSubEntidadModularUnidadEducativaAreaRol($area,$rol,$gestion) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        //$gestion = 2021;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
            select 'Departamento' as nombreArea, dt.id as codigo, dt.departamento as nombre, 7 as rolUsuario, coalesce(v.cantidad,0) as cantidad from (
                select  d.cod_dep as id_departamento, d.des_dep as desc_departamento, count(institucioneducativa_id) as cantidad
                from institucioneducativa a  
                inner join  (
                    select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona 
                    from jurisdiccion_geografica a 
                    inner join  (
                        select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001 
                        from (select id,codigo as cod_dep,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=1) a 
                        inner join ( select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                        inner join ( select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                        inner join ( select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id 
                        inner join ( select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                    ) b on a.lugar_tipo_id_localidad=b.id 
                    inner join  (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                ) d on a.le_juridicciongeografica_id=d.cod_le 
                INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id 
                INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id 
                INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id 
                INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id 
                inner join (
                    select * from institucioneducativa_humanistico_tecnico 
                    where gestion_tipo_id = ".$gestion." and institucioneducativa_humanistico_tecnico_tipo_id in (3)
                ) i on a.id = i.institucioneducativa_id 
                WHERE a.institucioneducativa_acreditacion_tipo_id = 1 AND a.estadoinstitucion_tipo_id = 10
                group by d.cod_dep, d.des_dep
            ) as v
            right join (select * from departamento_tipo where id not in (0)) as dt on dt.id = v.id_departamento::integer
            order by dt.id, dt.departamento
        "); 

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {                 
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("                    
                    select 'Unidad Educativa' as nombreArea, a.id as codigo, a.id::varchar||' - '||a.institucioneducativa  as nombre, 9 as rolUsuario, coalesce(count(institucioneducativa_id),0) as cantidad
                    from institucioneducativa a  
                    inner join  (
                        select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona 
                        from jurisdiccion_geografica a 
                        inner join  (
                            select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001 
                            from (select id,codigo as cod_dep,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=1) a 
                            inner join ( select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                            inner join ( select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                            inner join ( select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id 
                            inner join ( select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                        ) b on a.lugar_tipo_id_localidad=b.id 
                        inner join  (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                    ) d on a.le_juridicciongeografica_id=d.cod_le 
                    INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id 
                    INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id 
                    INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id 
                    INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id 
                    inner join (
                        select * from institucioneducativa_humanistico_tecnico 
                        where gestion_tipo_id = ".$gestion." and institucioneducativa_humanistico_tecnico_tipo_id in (3)
                    ) i on a.id = i.institucioneducativa_id 
                    WHERE a.institucioneducativa_acreditacion_tipo_id = 1 AND a.estadoinstitucion_tipo_id = 10 and d.cod_dis = '".$area."'
                    group by a.id, a.institucioneducativa
                    order by a.id, a.institucioneducativa
                ");  
        }  

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                    select 'Distrito Educativo' as nombreArea, d.cod_dis as codigo, d.des_dis as nombre, 10 as rolUsuario, coalesce(count(institucioneducativa_id),0) as cantidad
                    from institucioneducativa a  
                    inner join  (
                        select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona 
                        from jurisdiccion_geografica a 
                        inner join  (
                            select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001 
                            from (select id,codigo as cod_dep,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=1) a 
                            inner join ( select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                            inner join ( select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                            inner join ( select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id 
                            inner join ( select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                        ) b on a.lugar_tipo_id_localidad=b.id 
                        inner join  (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                    ) d on a.le_juridicciongeografica_id=d.cod_le 
                    INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id 
                    INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id 
                    INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id 
                    INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id 
                    inner join (
                        select * from institucioneducativa_humanistico_tecnico 
                        where gestion_tipo_id = ".$gestion." and institucioneducativa_humanistico_tecnico_tipo_id in (3)
                    ) i on a.id = i.institucioneducativa_id 
                    WHERE a.institucioneducativa_acreditacion_tipo_id = 1 AND a.estadoinstitucion_tipo_id = 10 and d.cod_dep = '".$area."'
                    group by d.cod_dis, d.des_dis
                    order by d.cod_dis, d.des_dis
                ");  
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $queryEntidad = $em->getConnection()->prepare("
                select 'Departamento' as nombreArea, dt.id as codigo, dt.departamento as nombre, 7 as rolUsuario, coalesce(v.cantidad,0) as cantidad from (
                    select  d.cod_dep as id_departamento, d.des_dep as desc_departamento, count(institucioneducativa_id) as cantidad
                    from institucioneducativa a  
                    inner join  (
                        select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona 
                        from jurisdiccion_geografica a 
                        inner join  (
                            select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001 
                            from (select id,codigo as cod_dep,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=1) a 
                            inner join ( select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                            inner join ( select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                            inner join ( select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id 
                            inner join ( select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                        ) b on a.lugar_tipo_id_localidad=b.id 
                        inner join  (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                    ) d on a.le_juridicciongeografica_id=d.cod_le 
                    INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id 
                    INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id 
                    INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id 
                    INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id 
                    inner join (
                        select * from institucioneducativa_humanistico_tecnico 
                        where gestion_tipo_id = ".$gestion." and institucioneducativa_humanistico_tecnico_tipo_id in (3)
                    ) i on a.id = i.institucioneducativa_id 
                    WHERE a.institucioneducativa_acreditacion_tipo_id = 1 AND a.estadoinstitucion_tipo_id = 10
                    group by d.cod_dep, d.des_dep
                ) as v
                right join (select * from departamento_tipo where id not in (0)) as dt on dt.id = v.id_departamento::integer
                order by dt.id, dt.departamento
            ");    
        } 

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 
        if (count($objEntidad)>0 and $rol != 9 and $rol != 5){
            return $objEntidad;
        } else {
            return array();
        }
    }


        /**
     * Busca el detalle de estudiantes en funcion al tipo de rol - Educacion Especial
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaEstadisticaModularUnidadEducativaAreaRol($area,$rol,$gestion) {
        //$gestion = 2021; 
        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
            with tabla as (
                select a.dependencia_tipo_id as dependencia_id, e.dependencia as dependencia, d.area2001 as area, i.grado_tipo_id as grado_id, count(a.id) as cantidad
                from institucioneducativa a  
                inner join  (
                    select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona 
                    from jurisdiccion_geografica a 
                    inner join  (
                        select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001 
                        from (select id,codigo as cod_dep,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=1) a 
                        inner join ( select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                        inner join ( select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                        inner join ( select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id 
                        inner join ( select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                    ) b on a.lugar_tipo_id_localidad=b.id 
                    inner join  (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                ) d on a.le_juridicciongeografica_id=d.cod_le 
                INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id 
                INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id 
                INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id 
                INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id 
                inner join (
                    select * from institucioneducativa_humanistico_tecnico 
                    where gestion_tipo_id = ".$gestion." and institucioneducativa_humanistico_tecnico_tipo_id in (3)
                ) i on a.id = i.institucioneducativa_id 
                WHERE a.institucioneducativa_acreditacion_tipo_id = 1 AND a.estadoinstitucion_tipo_id = 10 -- and d.cod_dis = '2085'
                GROUP BY a.dependencia_tipo_id, e.dependencia, d.area2001, i.grado_tipo_id
            )
                        
            select 2 as tipo_id, 'Dependencia' as tipo_nombre, t.dependencia_id as id, t.dependencia as nombre
            , sum(cantidad) as cantidad from tabla as t 
            group by t.dependencia_id, t.dependencia
            
            union all
            
            select 3 as tipo_id, 'Área Geográfica' as tipo_nombre, case t.area when 'U' then 1 when 'R' then 2 else 0 end as id, case t.area when 'U' then 'Urbano' when 'R' then 'Rural' else '' end as nombre
            , sum(cantidad) as cantidad from tabla as t 
            group by t.area
            
            order by tipo_id, id
             
        "); 

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
            $queryEntidad = $em->getConnection()->prepare("
                with tabla as (
                    select a.dependencia_tipo_id as dependencia_id, e.dependencia as dependencia, d.area2001 as area, i.grado_tipo_id as grado_id, count(a.id) as cantidad
                    from institucioneducativa a  
                    inner join  (
                        select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona 
                        from jurisdiccion_geografica a 
                        inner join  (
                            select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001 
                            from (select id,codigo as cod_dep,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=1) a 
                            inner join ( select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                            inner join ( select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                            inner join ( select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id 
                            inner join ( select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                        ) b on a.lugar_tipo_id_localidad=b.id 
                        inner join  (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                    ) d on a.le_juridicciongeografica_id=d.cod_le 
                    INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id 
                    INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id 
                    INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id 
                    INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id 
                    inner join (
                        select * from institucioneducativa_humanistico_tecnico 
                        where gestion_tipo_id = ".$gestion." and institucioneducativa_humanistico_tecnico_tipo_id in (3)
                    ) i on a.id = i.institucioneducativa_id 
                    WHERE a.institucioneducativa_acreditacion_tipo_id = 1 AND a.estadoinstitucion_tipo_id = 10 and a.id = ".$area."
                    GROUP BY a.dependencia_tipo_id, e.dependencia, d.area2001, i.grado_tipo_id
                )
                                
                select 2 as tipo_id, 'Dependencia' as tipo_nombre, t.dependencia_id as id, t.dependencia as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.dependencia_id, t.dependencia
                
                union all
                
                select 3 as tipo_id, 'Área Geográfica' as tipo_nombre, case t.area when 'U' then 1 when 'R' then 2 else 0 end as id, case t.area when 'U' then 'Urbano' when 'R' then 'Rural' else '' end as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.area
                
                order by tipo_id, id
            ");     
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("    
                with tabla as (
                    select a.dependencia_tipo_id as dependencia_id, e.dependencia as dependencia, d.area2001 as area, i.grado_tipo_id as grado_id, count(a.id) as cantidad
                    from institucioneducativa a  
                    inner join  (
                        select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona 
                        from jurisdiccion_geografica a 
                        inner join  (
                            select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001 
                            from (select id,codigo as cod_dep,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=1) a 
                            inner join ( select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                            inner join ( select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                            inner join ( select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id 
                            inner join ( select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                        ) b on a.lugar_tipo_id_localidad=b.id 
                        inner join  (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                    ) d on a.le_juridicciongeografica_id=d.cod_le 
                    INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id 
                    INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id 
                    INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id 
                    INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id 
                    inner join (
                        select * from institucioneducativa_humanistico_tecnico 
                        where gestion_tipo_id = ".$gestion." and institucioneducativa_humanistico_tecnico_tipo_id in (3)
                    ) i on a.id = i.institucioneducativa_id 
                    WHERE a.institucioneducativa_acreditacion_tipo_id = 1 AND a.estadoinstitucion_tipo_id = 10 and d.cod_dis = '".$area."'
                    GROUP BY a.dependencia_tipo_id, e.dependencia, d.area2001, i.grado_tipo_id
                )
                                
                select 2 as tipo_id, 'Dependencia' as tipo_nombre, t.dependencia_id as id, t.dependencia as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.dependencia_id, t.dependencia
                
                union all
                
                select 3 as tipo_id, 'Área Geográfica' as tipo_nombre, case t.area when 'U' then 1 when 'R' then 2 else 0 end as id, case t.area when 'U' then 'Urbano' when 'R' then 'Rural' else '' end as nombre
                , sum(cantidad) as cantid ad from tabla as t 
                group by t.area
                
                order by tipo_id, id
            ");  
        }  

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                with tabla as (
                    select a.dependencia_tipo_id as dependencia_id, e.dependencia as dependencia, d.area2001 as area, i.grado_tipo_id as grado_id, count(a.id) as cantidad
                    from institucioneducativa a  
                    inner join  (
                        select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona 
                        from jurisdiccion_geografica a 
                        inner join  (
                            select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001 
                            from (select id,codigo as cod_dep,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=1) a 
                            inner join ( select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                            inner join ( select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                            inner join ( select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id 
                            inner join ( select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                        ) b on a.lugar_tipo_id_localidad=b.id 
                        inner join  (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                    ) d on a.le_juridicciongeografica_id=d.cod_le 
                    INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id 
                    INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id 
                    INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id 
                    INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id 
                    inner join (
                        select * from institucioneducativa_humanistico_tecnico 
                        where gestion_tipo_id = ".$gestion." and institucioneducativa_humanistico_tecnico_tipo_id in (3)
                    ) i on a.id = i.institucioneducativa_id 
                    WHERE a.institucioneducativa_acreditacion_tipo_id = 1 AND a.estadoinstitucion_tipo_id = 10 and d.cod_dep = '".$area."'
                    GROUP BY a.dependencia_tipo_id, e.dependencia, d.area2001, i.grado_tipo_id
                )
                                
                select 2 as tipo_id, 'Dependencia' as tipo_nombre, t.dependencia_id as id, t.dependencia as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.dependencia_id, t.dependencia
                
                union all
                
                select 3 as tipo_id, 'Área Geográfica' as tipo_nombre, case t.area when 'U' then 1 when 'R' then 2 else 0 end as id, case t.area when 'U' then 'Urbano' when 'R' then 'Rural' else '' end as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.area
                
                order by tipo_id, id
            ");  
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {            
            $queryEntidad = $em->getConnection()->prepare("
                with tabla as (
                    select a.dependencia_tipo_id as dependencia_id, e.dependencia as dependencia, d.area2001 as area, i.grado_tipo_id as grado_id, count(a.id) as cantidad
                    from institucioneducativa a  
                    inner join  (
                        select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona 
                        from jurisdiccion_geografica a 
                        inner join  (
                            select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001 
                            from (select id,codigo as cod_dep,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=1) a 
                            inner join ( select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id 
                            inner join ( select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id 
                            inner join ( select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id 
                            inner join ( select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id
                        ) b on a.lugar_tipo_id_localidad=b.id 
                        inner join  (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id
                    ) d on a.le_juridicciongeografica_id=d.cod_le 
                    INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id 
                    INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id 
                    INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id 
                    INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id 
                    inner join (
                        select * from institucioneducativa_humanistico_tecnico 
                        where gestion_tipo_id = ".$gestionActual." and institucioneducativa_humanistico_tecnico_tipo_id in (3)
                    ) i on a.id = i.institucioneducativa_id 
                    WHERE a.institucioneducativa_acreditacion_tipo_id = 1 AND a.estadoinstitucion_tipo_id = 10 -- and d.cod_dis = '2085'
                    GROUP BY a.dependencia_tipo_id, e.dependencia, d.area2001, i.grado_tipo_id
                )
                                
                select 2 as tipo_id, 'Dependencia' as tipo_nombre, t.dependencia_id as id, t.dependencia as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.dependencia_id, t.dependencia
                
                union all
                
                select 3 as tipo_id, 'Área Geográfica' as tipo_nombre, case t.area when 'U' then 1 when 'R' then 2 else 0 end as id, case t.area when 'U' then 'Urbano' when 'R' then 'Rural' else '' end as nombre
                , sum(cantidad) as cantidad from tabla as t 
                group by t.area
                
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
     * Imprime reportes estadisticos segun el tipo de rol en formato PDF - BTH
     * Jurlan edit Pvargas
     * @param Request $request
     * @return type
     */
    public function bthUnidadEducativaPdfAction(Request $request) {
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
            $codigoArea = $request->get('codigo');
            $rol = $request->get('rol');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
        }

        $em = $this->getDoctrine()->getManager();
        //dump($gestion,$codigoArea);die;
        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionUe_bth_v1_pvc.rptdesign&__format=pdf&gestion='.$gestion.'&codigo='.$codigoArea));
        /* // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_bth_unidadeducativa_nacional_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion.'&codigo='.$codigoArea));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {              
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_bth_unidadeducativa_distrital_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion.'&codigo='.$codigoArea));
        }  

        if($rol == 7) // Tecnico Departamental
        { 
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_bth_unidadeducativa_departamental_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion.'&codigo='.$codigoArea));
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {  
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_bth_unidadeducativa_nacional_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion.'&codigo='.$codigoArea));
        } */ 

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    /**
     * Imprime reportes estadisticos segun el tipo de rol en formato XLSX - BTH
     * Jurlan edit Pvargas
     * @param Request $request
     * @return type
     */
    public function bthUnidadEducativaXlsxAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        //dump($request);die;
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $gestion = $request->get('gestion');
            $codigoArea = $request->get('codigo');
            $rol = $request->get('rol');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/xls');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_InformacionUe_bth_v1_pvc.rptdesign&__format=xls&gestion='.$gestion.'&codigo='.$codigoArea));
        /* // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_bth_unidadeducativa_nacional_v1_rcm.rptdesign&__format=xlsx&gestion='.$gestion.'&codigo='.$codigoArea));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {              
        }  

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_bth_unidadeducativa_distrital_v1_rcm.rptdesign&__format=xlsx&gestion='.$gestion.'&codigo='.$codigoArea));
        }  

        if($rol == 7) // Tecnico Departamental
        { 
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_bth_unidadeducativa_departamental_v1_rcm.rptdesign&__format=xlsx&gestion='.$gestion.'&codigo='.$codigoArea));
        } 

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {  
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_bth_unidadeducativa_nacional_v1_rcm.rptdesign&__format=xlsx&gestion='.$gestion.'&codigo='.$codigoArea));
        } */ 

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    
    /**
     * Imprime reportes estadisticos de matricula educativa segun el area en formato PDF - BTH
     * Pvargas
     * @param Request $request
     * @return type
     */
    public function bthMatriculaPdfAction(Request $request) {
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
            $codigoArea = $request->get('codigo');
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
        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $nomArchivo = 'reg_est_InformacionEstadistica_bth_Distrito_v1_pvc.rptdesign';
        }  

        if($rol == 7) // Tecnico Departamental
        { 
            $nomArchivo = 'reg_est_InformacionEstadistica__bth_Departamento_v1_pvc.rptdesign';
        } 

        if($rol == 8 or $rol == 20 or $rol == 0) // Tecnico Nacional
        {  
            $nomArchivo = 'reg_est_InformacionEstadistica_bth_Nacional_v1_pvc.rptdesign';
        } 
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $nomArchivo .'&__format=pdf&gestion='.$gestion.'&codigo='.$codigoArea));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    /**
     * Imprime reportes estadisticos de matricula educativa segun el area en formato XLSX - BTH
     * Pvargas
     * @param Request $request
     * @return type
     */
    public function bthMatriculaXlsxAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        //dump($request);die;
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            /*
             * Recupera datos del formulario
             */
            $gestion = $request->get('gestion');
            $codigoArea = $request->get('codigo');
            $rol = $request->get('rol');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/xls');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        
        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $nomArchivo = 'reg_est_InformacionEstadistica_bth_Distrito_v1_pvc.rptdesign';
        }  

        if($rol == 7) // Tecnico Departamental
        { 
            $nomArchivo = 'reg_est_InformacionEstadistica__bth_Departamento_v1_pvc.rptdesign';
        } 

        if($rol == 8 or $rol == 20 or $rol == 0) // Tecnico Nacional
        {  
            $nomArchivo = 'reg_est_InformacionEstadistica_bth_Nacional_v1_pvc.rptdesign';
        } 

        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $nomArchivo .'&__format=xls&gestion='.$gestion.'&codigo='.$codigoArea));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
}
