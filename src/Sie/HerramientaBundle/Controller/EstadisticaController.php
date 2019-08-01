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
        $chartGrado = $chartController->chartDonut3d($entityEstadistica[1],"Unidades Educativas según el Máximo Año de Escolaridad Autorizado",$gestionProcesada,"Unidades Educativas","chartContainerGrado");
        $chartDependencia = $chartController->chartColumn($entityEstadistica[2],"Unidades Educativas según Dependencia",$gestionProcesada,"Unidades Educativas","chartContainerDependencia");
        $chartArea = $chartController->chartSemiPieDonut3d($entityEstadistica[3],"Unidades Educativas según Área Geográfica",$gestionProcesada,"Unidades Educativas","chartContainerArea");
        
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
            return $this->render('SieHerramientaBundle:Estadistica:bthUnidadEducativa.html.twig', array(
                'infoEntidad'=>$entidad,
                'infoSubEntidad'=>$subEntidades, 
                'infoEstadistica'=>$entityEstadistica,
                'datoGraficoGrado'=>$chartGrado,
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
                return $this->render('SieHerramientaBundle:Estadistica:bthUnidadEducativa.html.twig', array(
                    'infoEntidad'=>$entidad, 
                    'infoEstadistica'=>$entityEstadistica,                
                    'datoGraficoGrado'=>$chartGrado,
                    'datoGraficoDependencia'=>$chartDependencia,
                    'datoGraficoArea'=>$chartArea,
                    'gestion'=>$gestionProcesada,
                     'link'=>$link,
                     'mensaje'=>$mensaje,
                    'fechaEstadistica'=>$fechaEstadisticaRegular,
                    'form' => $defaultController->createLoginForm()->createView()
                ));  
            } else {
                return $this->render('SieHerramientaBundle:Estadistica:bthUnidadEducativa.html.twig', array(
                    'infoSubEntidad'=>$subEntidades, 
                    'infoEstadistica'=>$entityEstadistica,                    
                    'datoGraficoGrado'=>$chartGrado,
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
        
        $chartDependencia = $chartController->chartPie($entityEstadistica[2],"Unidades Educativas según Dependencia",$gestionProcesada,"Unidades Educativas","chartContainerDependencia");
        $chartArea = $chartController->chartSemiPieDonut3d($entityEstadistica[3],"Unidades Educativas según Área Geográfica",$gestionProcesada,"Unidades Educativas","chartContainerArea");
        
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
     * Busca el detalle de estudiantes en funcion al tipo de rol - Educacion Especial
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaEstadisticaBthUnidadEducativaAreaRol($area,$rol,$gestion) {

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
     * Jurlan
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
        } 

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    /**
     * Imprime reportes estadisticos segun el tipo de rol en formato XLSX - BTH
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function bthUnidadEducativaXlsxAction(Request $request) {
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

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xlsx';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        
        // por defecto
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
        } 

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
}
