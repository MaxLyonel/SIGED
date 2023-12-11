<?php

namespace Sie\PermanenteBundle\Controller;
 
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
use Symfony\Component\HttpFoundation\Response;


class EstadisticasPermanenteController extends Controller
{
    private $session;
    
    public function __construct() {        
        $this->session = new Session();        
    }
    
    public function indexAction(){
        return $this->redirect($this->generateUrl('login'));

        $id_usuario = $this->session->get('userId');
       
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SiePermanenteBundle:Default:index.html.twig');
    }
    
    public function deptoestadsiticasAction(){
        $id_usuario = $this->session->get('userId');
       
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SiePermanenteBundle:Default:index.html.twig');
    }
    
    public function ceasstdcierreAction(Request $request) {
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //$sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
        $query = "   select  distrito_cod, des_dis, count(distinct institucioneducativa_id) as centros, sum(cast(primaria as integer)) as primaria,sum(cast(secundaria as integer)) as secundaria,sum(cast(basico as integer)) as basico,sum(cast(auxiliar as integer)) as auxiliar,sum(cast(medio as integer))as medio from (
                        select distrito_cod, des_dis,institucioneducativa_id,nivel_id,facultad_area,ciclo_id,especialidad, grado_id, acreditacion,
                        case
                           when nivel_id ='15' and ciclo_id = '1' then
                                    cast(sum(totins) as character varying)
                           else '0'      
                        end as primaria,

                        case  
                           when nivel_id ='15' and ciclo_id = '2' then
                                    cast(sum(totins) as character varying)
                           else '0'     
                        end as secundaria,

                        case  
                           when nivel_id in ('18','19','20','21','22','23','24','25') and grado_id = '1' then
                                    cast(sum(totins) as character varying)
                           else '0'     
                        end as basico,

                        case 
                           when nivel_id in ('18','19','20','21','22','23','24','25') and grado_id = '2' then 	 
                                    cast(sum(totins) as character varying)
                           else '0'      
                        end as auxiliar,

                        case
                           when nivel_id in ('18','19','20','21','22','23','24','25') and grado_id = '3' then	 
                                    cast(sum(totins) as character varying)
                           else '0'     
                        end as medio
                        from (
                        select distrito_cod,des_dis,ieue,institucioneducativa,gestion_tipo_id,periodo_tipo_id,institucioneducativa_id,nivel_id,facultad_area,ciclo_id,especialidad, grado_id, acreditacion, sum(matricula) as totins from(
                                select * from (                                   
                                                           select
                                                            k.lugar,
                                                            b.distrito_cod,            
                                                            b.des_dis,
                                                            d.id as ieue,	
                                                            d.institucioneducativa
                                                            from jurisdiccion_geografica a 
                                                                    inner join (
                                                                            select id, lugar_tipo_id, codigo as distrito_cod, lugar as des_dis 
                                                                                    from lugar_tipo
                                                                            where lugar_nivel_id=7 
                                                                    ) b 	
                                                                    on a.lugar_tipo_id_distrito = b.id
                                                                    inner join (

                                                                            select a.id, a.institucioneducativa, a.le_juridicciongeografica_id				
                                                                            from institucioneducativa a
                                                                                inner join institucioneducativa_sucursal z on a.id = z.institucioneducativa_id 
                                                                                inner join institucioneducativa_sucursal_tramite w on w.institucioneducativa_sucursal_id = z.id                      
                                                                            where a.orgcurricular_tipo_id = 2
                                                                            and z.gestion_tipo_id = '2016'
                                                                            and z.periodo_tipo_id = '2'
                                                                            and w.tramite_estado_id in (8,14) 
                                                                    ) d	
                                                                    on a.id=d.le_juridicciongeografica_id
                                                                    inner join lugar_tipo k on k.id = b.lugar_tipo_id
                                                            group by k.id, k.lugar, b.distrito_cod, b.des_dis, d.id, d.institucioneducativa
                                                            order by k.lugar, d.id) auxuno

                        inner join 
                        (                              
                        select gestion_tipo_id,periodo_tipo_id,institucioneducativa_id,nivel_id,facultad_area,ciclo_id,especialidad,grado_id,acreditacion,count(codigo_rude) as matricula from (
                        select f.gestion_tipo_id,f.periodo_tipo_id,e.institucioneducativa_id,a.codigo as nivel_id, a.facultad_area,b.codigo as ciclo_id,b.especialidad,d.codigo as grado_id,d.acreditacion,j.codigo_rude
                         from superior_facultad_area_tipo a  
                            inner join superior_especialidad_tipo b on a.id=b.superior_facultad_area_tipo_id 
                                inner join superior_acreditacion_especialidad c on b.id=c.superior_especialidad_tipo_id 
                                    inner join superior_acreditacion_tipo d on c.superior_acreditacion_tipo_id=d.id 
                                        inner join superior_institucioneducativa_acreditacion e on e.acreditacion_especialidad_id=c.id 
                                            inner join institucioneducativa_sucursal f on e.institucioneducativa_sucursal_id=f.id 
                                                inner join superior_institucioneducativa_periodo g on g.superior_institucioneducativa_acreditacion_id=e.id 
                                                    inner join institucioneducativa_curso h on h.superior_institucioneducativa_periodo_id=g.id 
                                                        inner join estudiante_inscripcion i on h.id=i.institucioneducativa_curso_id 
                                                            inner join estudiante j on i.estudiante_id=j.id
                                                                inner join superior_modulo_periodo k on g.id=k.institucioneducativa_periodo_id
                                                                        inner join institucioneducativa_curso_oferta m on m.superior_modulo_periodo_id=k.id  
                                                                        and m.insitucioneducativa_curso_id=h.id 
                                                                            inner join estudiante_asignatura n on n.institucioneducativa_curso_oferta_id=m.id 
                                                                            and n.estudiante_inscripcion_id=i.id 
                                                                                inner join estudiante_nota o on o.estudiante_asignatura_id=n.id 
                        inner join paralelo_tipo p on h.paralelo_tipo_id=p.id
                        inner join turno_tipo q on h.turno_tipo_id=q.id
                        where f.gestion_tipo_id=2016 and f.periodo_tipo_id=2
                        group by f.gestion_tipo_id,f.periodo_tipo_id,e.institucioneducativa_id,a.codigo, a.facultad_area,b.codigo,b.especialidad,d.codigo,d.acreditacion,j.codigo_rude
                        )ad
                        group by gestion_tipo_id,periodo_tipo_id,institucioneducativa_id,nivel_id,facultad_area,ciclo_id,especialidad,grado_id,acreditacion
                        ) auxdos ON auxuno.ieue = auxdos.institucioneducativa_id
                        where lugar = 'Pando'
                        order by lugar,distrito_cod,ieue,nivel_id,ciclo_id,grado_id
                        )baseuno
                        group by distrito_cod,des_dis,ieue,institucioneducativa,gestion_tipo_id,periodo_tipo_id,institucioneducativa_id,nivel_id,facultad_area,ciclo_id,especialidad, grado_id, acreditacion
                        order by gestion_tipo_id,distrito_cod,des_dis, ieue, nivel_id,ciclo_id,grado_id
                        ) abfr
                        group by distrito_cod, des_dis,institucioneducativa_id,nivel_id,facultad_area,ciclo_id,especialidad, grado_id, acreditacion
                        order by distrito_cod,des_dis
                        ) baseuno
                        group by distrito_cod, des_dis";        
        $porcentajes = $db->prepare($query);
        $params = array();
        $porcentajes->execute($params);
        $po = $porcentajes->fetchAll();
        //$po = array();                
//        dump($potot);
//        die;
        
        return $this->render($this->session->get('pathSystem') . ':Estadisticas:estadisticasinscritosniveldistrito.html.twig', array(
                'entities' => $po,
            ));
    }





    public function permanenteIndexAction(Request $request) {
//dump($request);die;
        return $this->redirect($this->generateUrl('login'));
        $form = $request->get('form');
       // $fechaini = $form['gestion'] ;
      //  die;
        /*di
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        // $gestionActual = new \DateTime("Y");
        $fechaActual = new \DateTime(date('Y-m-d H:i:s'));
        $fechaEstadistica = $fechaActual->format('d-m-Y H:i:s');
        //$gestionProcesada = $gestionActual; 
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('
              
              select sest.id, sest.especialidad
              from
                      superior_facultad_area_tipo sfat 
	                 inner join superior_especialidad_tipo sest on sfat.id= sest.superior_facultad_area_tipo_id
              where sfat.codigo in (18,19,20,21,22,23,24,25)    
               
        ');

        //$query->bindValue(':idcurso', $idcurso);
        $query->execute();
        $objEspecialidades= $query->fetchAll();

        //dump($objEspecialidades);die;
        //$especialidades = $em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->findAll();
        //dump($objEspecialidadesArray);die;
//        $objEspecialidadesArray = array();
//        foreach ($objEspecialidades as $value) {
//            $objEspecialidadesArray[$value->getId()] = $value->getEspecialidad();
//        }
//
//        dump($objEspecialidadesArray);die;

        $codigo = 0;
        $nivel = 0;

        if ($request->isMethod('POST')) {
            //die;
            $form = $request->get('form');
            if($form)
            {
                $gestion =$form['gestion'] ;
                $periodo=$form['periodo'] ;
                $codigo = 0;
                $rol = 0;
            }else{
                $gestion= $request->get('gestion');
                $periodo= $request->get('periodo');
                $codigo = base64_decode($request->get('codigo'));
                $rol = $request->get('rol');
            }
           // dump($codigo);dump($rol);die;
        } else {
            $gestion= $fechaActual->format('Y');;
            $periodo= 2;
            $codigo = 0;
            $rol = 0;
        }

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $entidad = $this->buscaEntidadRol($codigo,$rol);
   //     dump($gestion);die;
        $subEntidades = $this->buscaSubEntidadRolPermanente($codigo,$rol,$gestion,$periodo);

    //   dump($subEntidades);die;
        // devuelve un array con los diferentes tipos de reportes 1:sexo, 2:dependencia, 3:area
    //    $entityEstadistica = $this->buscaEstadisticaPermanenteAreaRol($codigo,$rol,$gestion,$periodo); temporal fuera de servicio


//dump($entityEstadistica);die;
        if(count($subEntidades)>0 and isset($subEntidades)){
            $totalgeneral=0;
            foreach ($subEntidades as $key => $dato) {
//                if(isset(reset($entityEstadistica)['dato'][0]['cantidad'])){
//                    $subEntidades[$key]['total_general'] = reset($entityEstadistica)['dato'][0]['cantidad'];
//                } else {
//                    $subEntidades[$key]['total_general'] = 0;
//                }
                $totalgeneral=$totalgeneral+ $dato['total_inscrito'];
            }
            foreach ($subEntidades as $key => $dato) {
//                if(isset(reset($entityEstadistica)['dato'][0]['cantidad'])){
//                    $subEntidades[$key]['total_general'] = reset($entityEstadistica)['dato'][0]['cantidad'];
//                } else {
//                    $subEntidades[$key]['total_general'] = 0;
//                }
                $subEntidades[$key]['total_general'] = $totalgeneral;
                $subEntidades[$key]['gestion'] = $gestion;
                $subEntidades[$key]['periodo'] = $periodo;
            }
        } else {
            $subEntidades = null;
        }
      //  dump($subEntidades);die;
        // para seleccionar ti
        
        $chartPoblacion = $this->chartColumn($entityEstadistica[4],"Participantes matriculados según Sub área",$gestion,"Participantes","chartContainerPoblacion");

        //$chartMatricula = $this->chartColumnInformacionGeneral($entityEstadistica,"Matrícula",$gestionProcesada,1,"chartContainerMatricula");
     //   $chartNivel = $this->chartDonutInformacionGeneralNivelGrado($entityEstadistica[3],"Estudiantes matriculados según Etapa/Acreditación",$gestion,"Estudiantes","chartContainerNivel");
  //      $chartDiversa= $this->chartDonutInformacionGeneralNivelGrado($entityEstadistica[1],"Estudiantes matriculados según Nivel",$gestion,"Estudiantes","chartContainerDiversa");
//        //$chartNivelGrado = $this->chartDonutInformacionGeneralNivelGrado($entityEstadistica,"Estudiantes Matriculados según Nivel de Estudio y Año de Escolaridad ",$gestionProcesada,6,"chartContainerEfectivoNivelGrado");
        $chartGenero= $this->chartPie($entityEstadistica[1],"Participantes matriculados según Sexo",$gestion,"Participantes","chartContainerGenero");
//        //$chartArea = $this->chartPyramidInformacionGeneral($entityEstadistica,"Estudiantes Matriculados según Área Geográfica",$gestionProcesada,4,"chartContainerEfectivoArea");
        $chartDependencia = $this->chartSemiPieDonut3d($entityEstadistica[2],"Participantes matriculados según Dependencia",$gestion,"Participantes","chartContainerDependencia");
      //  $chartDependencia1 = $this->chartColumn($entityEstadistica[4],"Estudiantes matriculados según Recintos Penitenciarios",$gestion,"Estudiantes","chartContainerDependencia1");
     //   $chartDependencia2 = $this->chartColumn($entityEstadistica[5],"Estudiantes matriculados según Trabajadoras(es) del Hogar",$gestion,"Estudiantes","chartContainerDependencia2");
   //     $chartOtro = $this->chartResponsive($entityEstadistica[2],"Estudiantes matriculados según Dependencia",$gestion,"Estudiantes","chartContainerOtro");
//        $chartModalidad = $this->chartSemiPieDonut3d($entityEstadistica[4],"Estudiantes matriculados según Modalidad",$gestion,"Estudiantes","chartContainerModalidad");

//dump($chartDependencia);die;
        if(count($subEntidades)>0 and isset($subEntidades)){
            return $this->render('SieAppWebBundle' . ':Reporte:matriculaEducativaPermanente.html.twig', array(
                'infoEntidad'=>$entidad,
                'infoSubEntidad'=>$subEntidades,
                'gestion'=>$gestion,
                'datoGraficoPoblacion'=>$chartPoblacion,
                'datoGraficoDependencia'=>$chartDependencia,
                'datoGraficoGenero'=>$chartGenero,
                'fechaEstadistica'=>$fechaEstadistica,
                'especialidades'=>$objEspecialidades,
            ));
        } else {
            return $this->render('SieAppWebBundle' . ':Reporte:matriculaEducativaPermanente.html.twig', array(
                'infoEntidad'=>$entidad,
                'gestion'=>$gestion,
                'datoGraficoPoblacion'=>$chartPoblacion,
                'datoGraficoDependencia'=>$chartDependencia,
                'datoGraficoGenero'=>$chartGenero,
                'fechaEstadistica'=>$fechaEstadistica,
                'especialidades'=>$objEspecialidades,

            ));
        }
    }

    /**
     * Busca el detalle de estudiantes en funcion al tipo de rol - Educacion Especial
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaEstadisticaPermanenteAreaRol($area,$rol,$gestion,$periodo) {
        /*
         * Define la zona horaria y halla la fecha actual
         */

        
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = $gestion;
      // $gestionProcesada = $this->buscaGestionVistaMaterializadaAlternativa();
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
        with tabla as (
            SELECT ie.dependencia_tipo_id as dependencia_id,ppt.id as programaid,psat.id as subareaid,ppot.id as poblacionid ,pat.id as areatematicaid,gt.id as generoid
            ,count(*) as cantidad
                            from institucioneducativa_curso iec
                            inner join institucioneducativa ie on iec.institucioneducativa_id = ie.id
                            inner join institucioneducativa_sucursal ies on ies.institucioneducativa_id = ie.id and ies.gestion_tipo_id = iec.gestion_tipo_id  and ies.periodo_tipo_id = iec.periodo_tipo_id and ies.sucursal_tipo_id = iec.sucursal_tipo_id
                            inner join institucioneducativa_tipo iet on iet.id=ie.institucioneducativa_tipo_id
                            inner join dependencia_tipo as dt on dt.id = ie.dependencia_tipo_id
                            inner join estudiante_inscripcion esi on iec.id=esi.institucioneducativa_curso_id 
                            inner join estudiante as es on es.id = esi.estudiante_id
                            inner join genero_tipo as gt on gt.id=es.genero_tipo_id
                            inner join permanente_institucioneducativa_cursocorto piecc on piecc.institucioneducativa_curso_id = iec.id
                            inner join permanente_programa_tipo ppt on ppt.id = piecc.programa_tipo_id
                            inner join permanente_sub_area_tipo psat on psat.id = piecc.sub_area_tipo_id
                            inner join permanente_poblacion_tipo ppot on ppot.id = piecc.poblacion_tipo_id
                            inner join permanente_area_tematica_tipo pat on pat.id = piecc.areatematica_tipo_id
                            inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                            left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                            left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                            left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                            left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                            left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                            left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
                            where iet.id =5 and iec.gestion_tipo_id = ".$gestionActual." --and lt4.codigo='".$area."'
                       group by ie.dependencia_tipo_id,ppt.id,psat.id,ppot.id,pat.id,gt.id
                                   )
                                   select 1 as tipo_id, 'Sexo' as tipo_nombre, gt.id, gt.genero as nombre, '' as subnombre
                       , sum(cantidad) as cantidad from tabla as t 
                       inner join genero_tipo as gt on gt.id = t.generoid
                       group by gt.id, gt.genero
                       union all
                       
                       select 2 as tipo_id, 'Dependencia' as tipo_nombre, dt.id, dt.dependencia as nombre, '' as subnombre
                       , sum(cantidad) as cantidad from tabla as t 
                       inner join dependencia_tipo as dt on dt.id = t.dependencia_id
                       group by dt.id, dt.dependencia			
                       
                       union all
                       select 3 as tipo_id, 'Programa' as tipo_nombre, ppt.id, ppt.programa as nombre, '' as subnombre
                       , sum(cantidad) as cantidad from tabla as t 
                       inner join permanente_programa_tipo ppt on ppt.id = t.programaid
                       group by ppt.id, ppt.programa	
                       union all
                       select 4 as tipo_id, 'Subarea' as tipo_nombre, psat.id, psat.sub_area as nombre, '' as subnombre
                       , sum(cantidad) as cantidad from tabla as t 
                       inner join permanente_sub_area_tipo psat on psat.id = t.subareaid
                       group by psat.id, psat.sub_area				
                           union all
                       select 5 as tipo_id, 'Poblacion' as tipo_nombre, port.id, port.organizacion as nombre, '' as subnombre
                       , sum(cantidad) as cantidad from tabla as t 
                       inner join permanente_poblacion_tipo ppot on ppot.id = t.poblacionid
                                   inner join permanente_organizacion_tipo port on port.id = ppot.organizacion_tipo_id
                       group by port.id, port.organizacion					
                           union all
                       select 6 as tipo_id, 'Área Tematica' as tipo_nombre, pat.id, pat.areatematica as nombre, '' as subnombre
                       , sum(cantidad) as cantidad from tabla as t 
                       inner join permanente_area_tematica_tipo pat on pat.id = t.areatematicaid
                               group by pat.id, pat.areatematica					
                                           
                   
        ");

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
            $queryEntidad = $em->getConnection()->prepare("
          
            with tabla as (
                SELECT ie.dependencia_tipo_id as dependencia_id,ppt.id as programaid,psat.id as subareaid,ppot.id as poblacionid ,pat.id as areatematicaid,gt.id as generoid
                ,count(*) as cantidad
                                from institucioneducativa_curso iec
                                inner join institucioneducativa ie on iec.institucioneducativa_id = ie.id
                                inner join institucioneducativa_sucursal ies on ies.institucioneducativa_id = ie.id and ies.gestion_tipo_id = iec.gestion_tipo_id  and ies.periodo_tipo_id = iec.periodo_tipo_id and ies.sucursal_tipo_id = iec.sucursal_tipo_id
                                inner join institucioneducativa_tipo iet on iet.id=ie.institucioneducativa_tipo_id
                                inner join dependencia_tipo as dt on dt.id = ie.dependencia_tipo_id
                                inner join estudiante_inscripcion esi on iec.id=esi.institucioneducativa_curso_id 
                                inner join estudiante as es on es.id = esi.estudiante_id
                                inner join genero_tipo as gt on gt.id=es.genero_tipo_id
                                inner join permanente_institucioneducativa_cursocorto piecc on piecc.institucioneducativa_curso_id = iec.id
                                inner join permanente_programa_tipo ppt on ppt.id = piecc.programa_tipo_id
                                inner join permanente_sub_area_tipo psat on psat.id = piecc.sub_area_tipo_id
                                inner join permanente_poblacion_tipo ppot on ppot.id = piecc.poblacion_tipo_id
                                inner join permanente_area_tematica_tipo pat on pat.id = piecc.areatematica_tipo_id
                                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                                left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                                left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                                left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                                left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                                left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                                left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
                                where iet.id =5 and ies.gestion_tipo_id = ".$gestionActual." and ie.id='".$area."'
                           group by ie.dependencia_tipo_id,ppt.id,psat.id,ppot.id,pat.id,gt.id
                                       )
                                       select 1 as tipo_id, 'Sexo' as tipo_nombre, gt.id, gt.genero as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join genero_tipo as gt on gt.id = t.generoid
                           group by gt.id, gt.genero
                           union all
                           
                           select 2 as tipo_id, 'Dependencia' as tipo_nombre, dt.id, dt.dependencia as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join dependencia_tipo as dt on dt.id = t.dependencia_id
                           group by dt.id, dt.dependencia			
                           
                           union all
                           select 3 as tipo_id, 'Programa' as tipo_nombre, ppt.id, ppt.programa as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join permanente_programa_tipo ppt on ppt.id = t.programaid
                           group by ppt.id, ppt.programa	
                           union all
                           select 4 as tipo_id, 'Subarea' as tipo_nombre, psat.id, psat.sub_area as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join permanente_sub_area_tipo psat on psat.id = t.subareaid
                           group by psat.id, psat.sub_area				
                               union all
                           select 5 as tipo_id, 'Poblacion' as tipo_nombre, port.id, port.organizacion as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join permanente_poblacion_tipo ppot on ppot.id = t.poblacionid
                                       inner join permanente_organizacion_tipo port on port.id = ppot.organizacion_tipo_id
                           group by port.id, port.organizacion					
                               union all
                           select 6 as tipo_id, 'Área Tematica' as tipo_nombre, pat.id, pat.areatematica as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join permanente_area_tematica_tipo pat on pat.id = t.areatematicaid
                                   group by pat.id, pat.areatematica
                
           
            ");
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("    
            with tabla as (
                SELECT ie.dependencia_tipo_id as dependencia_id,ppt.id as programaid,psat.id as subareaid,ppot.id as poblacionid ,pat.id as areatematicaid,gt.id as generoid
                ,count(*) as cantidad
                                from institucioneducativa_curso iec
                                inner join institucioneducativa ie on iec.institucioneducativa_id = ie.id
                                inner join institucioneducativa_sucursal ies on ies.institucioneducativa_id = ie.id and ies.gestion_tipo_id = iec.gestion_tipo_id  and ies.periodo_tipo_id = iec.periodo_tipo_id and ies.sucursal_tipo_id = iec.sucursal_tipo_id
                                inner join institucioneducativa_tipo iet on iet.id=ie.institucioneducativa_tipo_id
                                inner join dependencia_tipo as dt on dt.id = ie.dependencia_tipo_id
                                inner join estudiante_inscripcion esi on iec.id=esi.institucioneducativa_curso_id 
                                inner join estudiante as es on es.id = esi.estudiante_id
                                inner join genero_tipo as gt on gt.id=es.genero_tipo_id
                                inner join permanente_institucioneducativa_cursocorto piecc on piecc.institucioneducativa_curso_id = iec.id
                                inner join permanente_programa_tipo ppt on ppt.id = piecc.programa_tipo_id
                                inner join permanente_sub_area_tipo psat on psat.id = piecc.sub_area_tipo_id
                                inner join permanente_poblacion_tipo ppot on ppot.id = piecc.poblacion_tipo_id
                                inner join permanente_area_tematica_tipo pat on pat.id = piecc.areatematica_tipo_id
                                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                                left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                                left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                                left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                                left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                                left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                                left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
                                where iet.id =5 and ies.gestion_tipo_id = ".$gestionActual." and lt5.codigo='".$area."'
                           group by ie.dependencia_tipo_id,ppt.id,psat.id,ppot.id,pat.id,gt.id
                                       )
                                       select 1 as tipo_id, 'Sexo' as tipo_nombre, gt.id, gt.genero as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join genero_tipo as gt on gt.id = t.generoid
                           group by gt.id, gt.genero
                           union all
                           
                           select 2 as tipo_id, 'Dependencia' as tipo_nombre, dt.id, dt.dependencia as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join dependencia_tipo as dt on dt.id = t.dependencia_id
                           group by dt.id, dt.dependencia			
                           
                           union all
                           select 3 as tipo_id, 'Programa' as tipo_nombre, ppt.id, ppt.programa as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join permanente_programa_tipo ppt on ppt.id = t.programaid
                           group by ppt.id, ppt.programa	
                           union all
                           select 4 as tipo_id, 'Subarea' as tipo_nombre, psat.id, psat.sub_area as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join permanente_sub_area_tipo psat on psat.id = t.subareaid
                           group by psat.id, psat.sub_area				
                               union all
                           select 5 as tipo_id, 'Poblacion' as tipo_nombre, port.id, port.organizacion as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join permanente_poblacion_tipo ppot on ppot.id = t.poblacionid
                                       inner join permanente_organizacion_tipo port on port.id = ppot.organizacion_tipo_id
                           group by port.id, port.organizacion					
                               union all
                           select 6 as tipo_id, 'Área Tematica' as tipo_nombre, pat.id, pat.areatematica as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join permanente_area_tematica_tipo pat on pat.id = t.areatematicaid
                                   group by pat.id, pat.areatematica	
                                       
            ");
        }

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
            with tabla as (
                SELECT ie.dependencia_tipo_id as dependencia_id,ppt.id as programaid,psat.id as subareaid,ppot.id as poblacionid ,pat.id as areatematicaid,gt.id as generoid
                ,count(*) as cantidad
                                from institucioneducativa_curso iec
                                inner join institucioneducativa ie on iec.institucioneducativa_id = ie.id
                                inner join institucioneducativa_sucursal ies on ies.institucioneducativa_id = ie.id and ies.gestion_tipo_id = iec.gestion_tipo_id  and ies.periodo_tipo_id = iec.periodo_tipo_id and ies.sucursal_tipo_id = iec.sucursal_tipo_id
                                inner join institucioneducativa_tipo iet on iet.id=ie.institucioneducativa_tipo_id
                                inner join dependencia_tipo as dt on dt.id = ie.dependencia_tipo_id
                                inner join estudiante_inscripcion esi on iec.id=esi.institucioneducativa_curso_id 
                                inner join estudiante as es on es.id = esi.estudiante_id
                                inner join genero_tipo as gt on gt.id=es.genero_tipo_id
                                inner join permanente_institucioneducativa_cursocorto piecc on piecc.institucioneducativa_curso_id = iec.id
                                inner join permanente_programa_tipo ppt on ppt.id = piecc.programa_tipo_id
                                inner join permanente_sub_area_tipo psat on psat.id = piecc.sub_area_tipo_id
                                inner join permanente_poblacion_tipo ppot on ppot.id = piecc.poblacion_tipo_id
                                inner join permanente_area_tematica_tipo pat on pat.id = piecc.areatematica_tipo_id
                                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                                left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                                left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                                left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                                left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                                left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                                left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
                                where iet.id =5 and ies.gestion_tipo_id = ".$gestionActual." and lt4.codigo='".$area."'
                           group by ie.dependencia_tipo_id,ppt.id,psat.id,ppot.id,pat.id,gt.id
                                       )
                                       select 1 as tipo_id, 'Sexo' as tipo_nombre, gt.id, gt.genero as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join genero_tipo as gt on gt.id = t.generoid
                           group by gt.id, gt.genero
                           union all
                           
                           select 2 as tipo_id, 'Dependencia' as tipo_nombre, dt.id, dt.dependencia as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join dependencia_tipo as dt on dt.id = t.dependencia_id
                           group by dt.id, dt.dependencia			
                           
                           union all
                           select 3 as tipo_id, 'Programa' as tipo_nombre, ppt.id, ppt.programa as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join permanente_programa_tipo ppt on ppt.id = t.programaid
                           group by ppt.id, ppt.programa	
                           union all
                           select 4 as tipo_id, 'Subarea' as tipo_nombre, psat.id, psat.sub_area as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join permanente_sub_area_tipo psat on psat.id = t.subareaid
                           group by psat.id, psat.sub_area				
                               union all
                           select 5 as tipo_id, 'Poblacion' as tipo_nombre, port.id, port.organizacion as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join permanente_poblacion_tipo ppot on ppot.id = t.poblacionid
                                       inner join permanente_organizacion_tipo port on port.id = ppot.organizacion_tipo_id
                           group by port.id, port.organizacion					
                               union all
                           select 6 as tipo_id, 'Área Tematica' as tipo_nombre, pat.id, pat.areatematica as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join permanente_area_tematica_tipo pat on pat.id = t.areatematicaid
                                   group by pat.id, pat.areatematica	
                                     
            ");
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $queryEntidad = $em->getConnection()->prepare("
            with tabla as (
                SELECT ie.dependencia_tipo_id as dependencia_id,ppt.id as programaid,psat.id as subareaid,ppot.id as poblacionid ,pat.id as areatematicaid,gt.id as generoid
                ,count(*) as cantidad
                                from institucioneducativa_curso iec
                                inner join institucioneducativa ie on iec.institucioneducativa_id = ie.id
                                inner join institucioneducativa_sucursal ies on ies.institucioneducativa_id = ie.id and ies.gestion_tipo_id = iec.gestion_tipo_id  and ies.periodo_tipo_id = iec.periodo_tipo_id and ies.sucursal_tipo_id = iec.sucursal_tipo_id
                                inner join institucioneducativa_tipo iet on iet.id=ie.institucioneducativa_tipo_id
                                inner join dependencia_tipo as dt on dt.id = ie.dependencia_tipo_id
                                inner join estudiante_inscripcion esi on iec.id=esi.institucioneducativa_curso_id 
                                inner join estudiante as es on es.id = esi.estudiante_id
                                inner join genero_tipo as gt on gt.id=es.genero_tipo_id
                                inner join permanente_institucioneducativa_cursocorto piecc on piecc.institucioneducativa_curso_id = iec.id
                                inner join permanente_programa_tipo ppt on ppt.id = piecc.programa_tipo_id
                                inner join permanente_sub_area_tipo psat on psat.id = piecc.sub_area_tipo_id
                                inner join permanente_poblacion_tipo ppot on ppot.id = piecc.poblacion_tipo_id
                                inner join permanente_area_tematica_tipo pat on pat.id = piecc.areatematica_tipo_id
                                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                                left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                                left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                                left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                                left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                                left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                                left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
                                where iet.id =5 and ies.gestion_tipo_id = ".$gestionActual." --and lt4.codigo='".$area."'
                           group by ie.dependencia_tipo_id,ppt.id,psat.id,ppot.id,pat.id,gt.id
                                       )
                                       select 1 as tipo_id, 'Sexo' as tipo_nombre, gt.id, gt.genero as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join genero_tipo as gt on gt.id = t.generoid
                           group by gt.id, gt.genero
                           union all
                           
                           select 2 as tipo_id, 'Dependencia' as tipo_nombre, dt.id, dt.dependencia as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join dependencia_tipo as dt on dt.id = t.dependencia_id
                           group by dt.id, dt.dependencia			
                           
                           union all
                           select 3 as tipo_id, 'Programa' as tipo_nombre, ppt.id, ppt.programa as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join permanente_programa_tipo ppt on ppt.id = t.programaid
                           group by ppt.id, ppt.programa	
                           union all
                           select 4 as tipo_id, 'Subarea' as tipo_nombre, psat.id, psat.sub_area as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join permanente_sub_area_tipo psat on psat.id = t.subareaid
                           group by psat.id, psat.sub_area				
                               union all
                           select 5 as tipo_id, 'Poblacion' as tipo_nombre, port.id, port.organizacion as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join permanente_poblacion_tipo ppot on ppot.id = t.poblacionid
                                       inner join permanente_organizacion_tipo port on port.id = ppot.organizacion_tipo_id
                           group by port.id, port.organizacion					
                               union all
                           select 6 as tipo_id, 'Área Tematica' as tipo_nombre, pat.id, pat.areatematica as nombre, '' as subnombre
                           , sum(cantidad) as cantidad from tabla as t 
                           inner join permanente_area_tematica_tipo pat on pat.id = t.areatematicaid
                                   group by pat.id, pat.areatematica	
                                      
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
            $aDato[$dato['tipo_id']]['dato'][0] = array('detalle'=>'Total','subdetalle'=>'', 'cantidad'=>$cantidadParcial);
            $aDato[$dato['tipo_id']]['dato'][$dato['id']] = array('detalle'=>$dato['nombre'],'subdetalle'=>$dato['subnombre'], 'cantidad'=>$dato['cantidad']);
        }
        //dump($aDato);die;
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
            function ".$contenedor."Load() {
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

    public function chartDonut3dAlt($entity,$titulo,$subTitulo,$nombreLabel,$contenedor) {

        $datosTemp = "";
        $subTotal = 0;
        foreach ($entity['dato'] as $key => $dato) {
            $porcentaje = 0;
            if ($key == 0){
                $subTotal = $dato['cantidad'];
            } else {
                $porcentaje = round(((100*$dato['cantidad'])/(($subTotal==0) ? 1: $subTotal)),1);
                $datosTemp = $datosTemp."{name: '".$dato['subdetalle']."', y: ".$porcentaje.", label: ".$dato['cantidad']."},";
            }
        }

        $pointLabel = $nombreLabel;

        $datos = "   
            function ".$contenedor."Load() {
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
                        name: 'Educación Diversa',
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

    public function chartDonutInformacionGeneralDiversa($entity,$titulo,$subTitulo,$tipoReporte,$contenedor) {
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

            function ".$contenedor."Load() {
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
                        name: 'Educación Diversa',
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
            function ".$contenedor."Load() {
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
            function ".$contenedor."Load() {
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
            function ".$contenedor."Load() {
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

    public function chartResponsive($entity,$titulo,$subTitulo,$nombreLabel,$contenedor) {
       // dump ($entity['dato']);die;
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
            function ".$contenedor."Load() {
                 $('#".$contenedor."').highcharts({
                    chart: {
                        type: 'column'
                      },
                    
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
                      legend: {
                        enabled:false
                      },
                    
                      xAxis: {
                        type: 'category'
                      },
                    
                      yAxis: {
                        title: {
                            text: ''
                        }
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
     * Busca el nombre de las entidades pertenecienten a un pais, departamento, distrito en funcion al tipo de rol - Educación Especial
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function buscaSubEntidadRolPermanente($area,$rol,$gestion,$periodo) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = $gestion;
     //   dump($gestionActual);die;
     //   $gestionProcesada = $this->buscaGestionVistaMaterializadaAlternativa();
        //$gestionActual = 2016;

        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
        select  'Departamento' as nombreArea, lt4.codigo as codigo, lt4.lugar  as nombre, 7 as rolUsuario, count(*) as total_inscrito  
        from institucioneducativa_curso iec
        inner join institucioneducativa ie on iec.institucioneducativa_id = ie.id
        inner join institucioneducativa_sucursal ies on ies.institucioneducativa_id = ie.id and ies.gestion_tipo_id = iec.gestion_tipo_id  and ies.periodo_tipo_id = iec.periodo_tipo_id and ies.sucursal_tipo_id = iec.sucursal_tipo_id 
        inner join institucioneducativa_tipo iet on iet.id=ie.institucioneducativa_tipo_id
        inner join permanente_institucioneducativa_cursocorto piecc on piecc.institucioneducativa_curso_id = iec.id
        inner join estudiante_inscripcion esi on iec.id=esi.institucioneducativa_curso_id 
        inner join estudiante as es on es.id = esi.estudiante_id
        inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
        left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
        left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
        left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
        left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
        left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
        left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
        where iet.id = 5 and iec.gestion_tipo_id = ".$gestionActual."
        group by lt4.id, lt4.codigo, lt4.lugar
        ");

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {

        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $queryEntidad = $em->getConnection()->prepare("
                     SELECT 'Centro de Educación Alternativa' as nombreArea, ie.id as codigo, ie.id::varchar||' - '||ie.institucioneducativa as nombre, 9 as rolUsuario, count(*) as total_inscrito  
                     from institucioneducativa_curso iec
                     inner join institucioneducativa ie on iec.institucioneducativa_id = ie.id
                     inner join institucioneducativa_sucursal ies on ies.institucioneducativa_id = ie.id and ies.gestion_tipo_id = iec.gestion_tipo_id  and ies.periodo_tipo_id = iec.periodo_tipo_id and ies.sucursal_tipo_id = iec.sucursal_tipo_id 
                     inner join institucioneducativa_tipo iet on iet.id=ie.institucioneducativa_tipo_id
                     inner join permanente_institucioneducativa_cursocorto piecc on piecc.institucioneducativa_curso_id = iec.id
                    inner join estudiante_inscripcion esi on iec.id=esi.institucioneducativa_curso_id 
                    inner join estudiante as es on es.id = esi.estudiante_id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                    left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                    left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                    left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                    left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                    left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
                     where iet.id = 5 and iec.gestion_tipo_id = ".$gestionActual." and lt5.codigo='".$area."'
                     group by ie.id, ie.institucioneducativa
                    order by ie.id, ie.institucioneducativa
                   -- where f.gestion_tipo_id = ".$gestionActual." and f.periodo_tipo_id = ".$periodo." and lt5.codigo='".$area."'-- and cast(substring(cod_dis,1,1) as integer)=2
                
                ");
        }

        if($rol == 7) // Tecnico Departamental
        {
            $queryEntidad = $em->getConnection()->prepare("
                    SELECT 'Distrito Educativo' as nombreArea, lt5.codigo as codigo, lt5.lugar  as nombre, 10 as rolUsuario, count(*) as total_inscrito  
                     from institucioneducativa_curso iec
                     inner join institucioneducativa ie on iec.institucioneducativa_id = ie.id
                     inner join institucioneducativa_sucursal ies on ies.institucioneducativa_id = ie.id and ies.gestion_tipo_id = iec.gestion_tipo_id  and ies.periodo_tipo_id = iec.periodo_tipo_id and ies.sucursal_tipo_id = iec.sucursal_tipo_id 
                     inner join institucioneducativa_tipo iet on iet.id=ie.institucioneducativa_tipo_id
                     inner join permanente_institucioneducativa_cursocorto piecc on piecc.institucioneducativa_curso_id = iec.id
                    inner join estudiante_inscripcion esi on iec.id=esi.institucioneducativa_curso_id 
                    inner join estudiante as es on es.id = esi.estudiante_id
                    inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                    left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                    left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                    left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                    left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                    left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                    left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
                     where iet.id = 5 and iec.gestion_tipo_id = ".$gestionActual." and lt4.codigo='".$area."'
                     group by lt5.id, lt5.codigo, lt5.lugar
                     order by lt5.id, lt5.codigo, lt5.lugar
                     -- where f.gestion_tipo_id = ".$gestionActual." and f.periodo_tipo_id = ".$periodo." and lt4.codigo='".$area."'-- and cast(substring(cod_dis,1,1) as integer)=2
                            
            ");
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $queryEntidad = $em->getConnection()->prepare("
                 SELECT 'Departamento' as nombreArea, lt4.codigo as codigo, lt4.lugar  as nombre, 7 as rolUsuario, count(*) as total_inscrito  
                 from institucioneducativa_curso iec
                 inner join institucioneducativa ie on iec.institucioneducativa_id = ie.id
                 inner join institucioneducativa_sucursal ies on ies.institucioneducativa_id = ie.id and ies.gestion_tipo_id = iec.gestion_tipo_id  and ies.periodo_tipo_id = iec.periodo_tipo_id and ies.sucursal_tipo_id = iec.sucursal_tipo_id 
                 inner join institucioneducativa_tipo iet on iet.id=ie.institucioneducativa_tipo_id
                 inner join permanente_institucioneducativa_cursocorto piecc on piecc.institucioneducativa_curso_id = iec.id
                 inner join estudiante_inscripcion esi on iec.id=esi.institucioneducativa_curso_id 
                 inner join estudiante as es on es.id = esi.estudiante_id
                 inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                 left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
                 left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
                 left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                 left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                 left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                 left join lugar_tipo as lt5 on lt5.id = jg.lugar_tipo_id_distrito
                 where iet.id =5 and iec.gestion_tipo_id = ".$gestionActual." --and lt4.codigo='".$area."'
                 group by lt4.id, lt4.codigo, lt4.lugar
                 order by lt4.id, lt4.codigo, lt4.lugar
            ");
        }

        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        if (count($objEntidad)>0 and $rol != 9 and $rol != 5){
            return $objEntidad;
        } else {
            return null;
        }
    }

    /**
     * Imprime reportes estadisticos segun el tipo de rol en formato PDF - Educación Especial
     * Jurlan
     * @param Request $request
     * @return type
     */
    public function informacionGeneralPermanentePrintPdfAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
       // dump($request);die;
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
            $periodo= $request->get('periodo');
           // $periodo = 2;
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo= 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'per_est_InformacionEstadistica_nacional_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion));
        

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'per_est_InformacionEstadistica_distrital_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion.'&distrito='.$codigoArea));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'per_est_InformacionEstadistica_departamental_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion.'&departamento='.$codigoArea));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'per_est_InformacionEstadistica_nacional_v1_rcm.rptdesign&__format=pdf&gestion='.$gestion));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function informacionGeneralPermanentePrintXlsAction(Request $request) {
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
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo=2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
     //   dump($this->container->getParameter('urlreportweb') . 'alt_est_nacional_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo);die;
        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'per_est_InformacionEstadistica_nacional_v1_rcm.rptdesign&__format=xlsx&gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'per_est_InformacionEstadistica_distrital_v1_rcm.rptdesign&__format=xlsx&gestion='.$gestion.'&distrito='.$codigoArea));

        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'per_est_InformacionEstadistica_departamentalv1_rcm.rptdesign&__format=xlsx&gestion='.$gestion.'&departamento='.$codigoArea));

        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'per_est_InformacionEstadistica_nacional_v1_rcm.rptdesign&__format=xlsx&gestion='.$gestion));

        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function informacionGeneralAlternativaEspPrintPdfAction(Request $request) {
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
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_distrital_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo.'&Distrito='.$codigoArea));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_departamental_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo.'&Departamento='.$codigoArea));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function informacionGeneralAlternativaEspPrintXlsAction(Request $request) {
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
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo=2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
     //   dump($this->container->getParameter('urlreportweb') . 'alt_est_nacional_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo);die;
        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_distrital_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo.'&Distrito='.$codigoArea));

        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_departamental_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo.'&Departamento='.$codigoArea));

        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo));

        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    // reportes generales completos

    public function informacionCompletaAlternativaPrintPdfAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        // dump($request);die;
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
            $periodo= $request->get('periodo');
            // $periodo = 2;
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo= 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_nacional_centros_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_distrital_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo.'&Distrito='.$codigoArea));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_departamental_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo.'&Departamento='.$codigoArea));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_nacional_centros_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function informacionCompletaAlternativaPrintXlsAction(Request $request) {
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
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo=2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        //   dump($this->container->getParameter('urlreportweb') . 'alt_est_nacional_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo);die;
        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_nacional_centros_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_distrital_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo.'&Distrito='.$codigoArea));

        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_departamental_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo.'&Departamento='.$codigoArea));

        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_nacional_centros_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo));

        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function informacionCompletaAlternativaEspPrintPdfAction(Request $request) {
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
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_centros_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_distrital_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo.'&Distrito='.$codigoArea));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_departamental_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo.'&Departamento='.$codigoArea));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_centros_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function informacionCompletaAlternativaEspPrintXlsAction(Request $request) {
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
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo=2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        //   dump($this->container->getParameter('urlreportweb') . 'alt_est_nacional_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo);die;
        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_centros_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_distrital_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo.'&Distrito='.$codigoArea));

        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_departamental_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo.'&Departamento='.$codigoArea));

        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_centros_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo));

        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }





    public function informacionCentroAlternativaEspPrintPdfAction(Request $request) {
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
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }


        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_cant_centros_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_cant_centros_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_cant_centros_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_cant_centros_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo));
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
    public function informacionCentroAlternativaEspPrintXlsAction(Request $request) {
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
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo=2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        //   dump($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_cant_centros_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo);die;
        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_cant_centros_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_cant_centros_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo.'&Distrito='.$codigoArea));

        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_cant_centros_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo.'&Departamento='.$codigoArea));

        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_cant_centros_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo));

        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function informacionCentroEspecialidadPrintPdfAction(Request $request) {
       // dump($request);die;

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
            $periodo = $request->get('periodo');
            $value = $request->get('idesp');
         //   dump($value);
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');

        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = $request->get('periodo');
            $value = $request->get('idesp');
        }


        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
         //   dump($this->container->getParameter('urlreportweb') . 'alt_est_nacional_por_especialidad_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo.'&idespecialidad='.$value);die;
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_nacional_por_especialidad_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo.'&idespecialidad='.$value));

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
    public function informacionCentroEspecialidadPrintXlsAction(Request $request) {
       // dump($request);die;
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
            $periodo = $request->get('periodo');
            $value = $request->get('idespx');
            //   dump($value);
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');

        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = $request->get('periodo');
            $value = $request->get('idespx');
        }


        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        //   dump($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_cant_centros_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo);die;
        // por defecto

        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_nacional_por_especialidad_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo.'&idespecialidad='.$value));



        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }



    public function  centroAltEspecialidadPrintPdfAction(Request $request) {
        // dump($request);die;

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
            $periodo = $request->get('periodo');
            $sie = $request->get('siecentrop');
            //   dump($value);
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');

        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = $request->get('periodo');
            $sie = $request->get('siecentrop');
        }


        $em = $this->getDoctrine()->getManager();

        $arch = 'CEA_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        //   dump($this->container->getParameter('urlreportweb') . 'alt_est_nacional_por_especialidad_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo.'&idespecialidad='.$value);die;
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_cea_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo.'&CEA='.$sie));

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
    public function centroAltEspecialidadPrintXlsAction(Request $request) {
         //dump($request);die;
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
            $periodo = $request->get('periodo');
            $sie = $request->get('siecentrox');
            //   dump($value);
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');

        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = $request->get('periodo');
            $sie = $request->get('siecentrop');
        }


        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        //   dump($this->container->getParameter('urlreportweb') . 'alt_esp_nacional_cant_centros_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo);die;
        // por defecto

        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_cea_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo.'&CEA='.$sie));



        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


//reportes por estadisticas de centro

    public function  centroAltEstPrintPdfAction(Request $request) {
       // dump($request);die;

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
            $periodo = $request->get('periodo');
            $sie = $request->get('siecentroestp');
            //   dump($value);
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
          //  dump($gestion);dump($periodo);dump($sie);die;
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = $request->get('periodo');
            $sie = $request->get('siecentroestp');
        }

     //   dump($gestion);dump($periodo);dump($sie);
        $em = $this->getDoctrine()->getManager();

        $arch = 'CEA_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
  //dump($this->container->getParameter('urlreportweb') . 'alt_est_cea_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo.'&CEA='.$sie);die;
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_cea_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion.'&Periodo='.$periodo.'&CEA='.$sie));

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
    public function centroAltEstPrintXlsAction(Request $request) {
     //   dump($request);die;
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
            $periodo = $request->get('periodo');
            $sie = $request->get('siecentroestx');
            //   dump($value);
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');

        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = $request->get('periodo');
            $sie = $request->get('siecentroestx');
        }


        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
   //         dump($this->container->getParameter('urlreportweb') . 'alt_est_cea_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo.'&CEA='.$sie);die;
        // por defecto

        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_cea_v1_ma.rptdesign&__format=xlsx&Gestion='.$gestion.'&Periodo='.$periodo.'&CEA='.$sie));



        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }







// corteee





    public function openReportesIndexAction(Request $request) {
        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
        $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$sesion->get('userId'),'rolTipo'=>$sesion->get('roluser')));
        $idlugarusuario = $usuariorol[0]->getLugarTipo()->getId();
        // dump($idlugarusuario);die;


        // dump($request);die;

        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validationremoveInscriptionAction if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        //get and set the variables

        $arrDataReport = array(
            'roluser' => $this->session->get('roluser'),
            'userId' => $this->session->get('userId'),
            'sie' => $this->session->get('ie_id'),
            'gestion' => $this->session->get('ie_gestion'),
            'subcea' => $this->session->get('ie_subcea'),
            'periodo' => $this->session->get('ie_per_cod'),
            'lugarid'=> $idlugarusuario
        );

        $repository = $em->getRepository('SieAppWebBundle:GestionTipo');
        $query = $repository->createQueryBuilder('g')
            ->orderBy('g.id', 'DESC')
            ->where('g.id < 2019 AND g.id > 2013')
            ->getQuery();
        $gestiones = $query->getResult();
        $gestionesArray = array();
        foreach ($gestiones as $g) {
            $gestionesArray[$g->getId()] = $g->getId();
        }
//dump($gestionesArray);die;
        $repository = $em->getRepository('SieAppWebBundle:PeriodoTipo');
        $query = $repository->createQueryBuilder('p')
            ->orderBy('p.id')
            ->where('p.id in (2,3)')
            ->getQuery();
        $periodos = $query->getResult();
        $periodosArray = array();
        foreach ($periodos as $p) {
            $periodosArray[$p->getId()] = $p->getPeriodo();
        }

        $repository = $em->getRepository('SieAppWebBundle:SucursalTipo');
        $query = $repository->createQueryBuilder('s')
            ->orderBy('s.id')
            ->getQuery();
        $sucursales = $query->getResult();
        $sucursalesArray = array();
        foreach ($sucursales as $s) {
            $sucursalesArray[$s->getId()] = $s->getId();
        }
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('estadistica_alternativa_index'))
//            ->add('idInstitucion', 'text', array('label' => 'Código SIE del CEA', 'required' => false, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off', 'maxlength' => 8, 'pattern' => '[0-9]{8}')))
          //  ->add('gestion', 'choice', array('label' => 'Gestión', 'required' => false, 'choices' => $gestionesArray, 'attr' => array('class' => 'form-control')))
  //          ->add('periodo', 'choice', array('label' => 'Periodo', 'required' => false, 'choices' => $periodosArray, 'attr' => array('class' => 'form-control')))
//            ->add('gestionest', 'choice', array('label' => 'Gestión', 'required' => false, 'choices' => $gestionesArray, 'attr' => array('class' => 'form-control')))
//            ->add('periodoest', 'choice', array('label' => 'Periodo', 'required' => false, 'choices' => $periodosArray, 'attr' => array('class' => 'form-control')))
//            ->add('gestionesp', 'choice', array('label' => 'Gestión', 'required' => false, 'choices' => $gestionesArray, 'attr' => array('class' => 'form-control')))
//            ->add('periodoesp', 'choice', array('label' => 'Periodo', 'required' => false, 'choices' => $periodosArray, 'attr' => array('class' => 'form-control')))
//            ->add('subcea', 'choice', array('label' => 'Sub CEA', 'required' => false, 'choices' => $sucursalesArray, 'attr' => array('class' => 'form-control')))
            ->add('crear', 'submit', array('label' => 'Crear', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();




        return $this->render($this->session->get('pathSystem') . ':Reporte:index.html.twig', array(
            'dataReport' => $arrDataReport,
            'dataInfo' => serialize($arrDataReport),
            'form' => $form->createView()
        ));
    }

    public function findCEAReportAction(Request $request )
    {
            dump($request);die;
    }


    public function buscaEntidadRol($codigo,$rol) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        //$gestionProcesada = $this->buscaGestionVistaMaterializadaAlternativa();
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


    /**
     * Busca gestion a la cual pertenece la informacion de la vista materializada
     * Jurlan
     * @param Request $request
     * @return type
     */
//    public function buscaGestionVistaMaterializadaAlternativaInstitucionEducativa() {
//        /*
//         * Define la zona horaria y halla la fecha actual
//         */
//        date_default_timezone_set('America/La_Paz');
//        $fechaActual = new \DateTime(date('Y-m-d'));
//        $gestionActual = date_format($fechaActual,'Y');
//        //$gestionActual = 2016;
//
//        $em = $this->getDoctrine()->getManager();
//
//        $queryEntidad = $em->getConnection()->prepare("
//            select date_part('year', fecha_vista) as gestion from vm_instituciones_educativas limit 1
//        ");
//
//        $queryEntidad->execute();
//        $objEntidad = $queryEntidad->fetchAll();
//
//        if (count($objEntidad)>0){
//            return $objEntidad[0]['gestion'];
//        } else {
//            return 0;
//        }
//    }
//
//
//    public function buscaGestionVistaMaterializadaAlternativa() {
//        /*
//         * Define la zona horaria y halla la fecha actual
//         */
//        date_default_timezone_set('America/La_Paz');
//        $fechaActual = new \DateTime(date('Y-m-d'));
//        $gestionActual = date_format($fechaActual,'Y');
//        //$gestionActual = 2016;
//
//        $em = $this->getDoctrine()->getManager();
//
//        $queryEntidad = $em->getConnection()->prepare("
//            select cast(gestion as integer) as gestion
//            from vm_estudiantes_estadistica_regular limit 1
//        ");
//
//        $queryEntidad->execute();
//        $objEntidad = $queryEntidad->fetchAll();
//
//        if (count($objEntidad)>0){
//            return $objEntidad[0]['gestion'];
//        } else {
//            return 0;
//        }
//    }

    public function reportesIndexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:GestionTipo');
        $query = $repository->createQueryBuilder('g')
            ->orderBy('g.id', 'DESC')
            ->where('g.id > 2013')
            ->getQuery();
        $gestiones = $query->getResult();
        $gestionesArray = array();
        foreach ($gestiones as $g) {
            $gestionesArray[$g->getId()] = $g->getId();
        }
//dump($gestionesArray);die;
        $repository = $em->getRepository('SieAppWebBundle:PeriodoTipo');
        $query = $repository->createQueryBuilder('p')
            ->orderBy('p.id')
            ->where('p.id in (2,3)')
            ->getQuery();
        $periodos = $query->getResult();
        $periodosArray = array();
        foreach ($periodos as $p) {
            $periodosArray[$p->getId()] = $p->getPeriodo();
        }

        $repository = $em->getRepository('SieAppWebBundle:SucursalTipo');
        $query = $repository->createQueryBuilder('s')
            ->orderBy('s.id')
            ->getQuery();
        $sucursales = $query->getResult();
        $sucursalesArray = array();
        foreach ($sucursales as $s) {
            $sucursalesArray[$s->getId()] = $s->getId();
        }

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('sie_alt_reportes'))
     //       ->add('idInstitucion', 'text', array('label' => 'Código SIE del CEA', 'required' => true, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off', 'maxlength' => 8, 'pattern' => '[0-9]{8}')))
            ->add('gestion', 'choice', array('label' => 'Gestión', 'required' => true, 'choices' => $gestionesArray, 'attr' => array('class' => 'form-control')))
            ->add('periodo', 'choice', array('label' => 'Periodo', 'required' => true, 'choices' => $periodosArray, 'attr' => array('class' => 'form-control')))
         //   ->add('subcea', 'choice', array('label' => 'Sub CEA', 'required' => true, 'choices' => $sucursalesArray, 'attr' => array('class' => 'form-control')))
            ->add('crear', 'submit', array('label' => 'Generar Reportes', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();

        return $this->render($this->session->get('pathSystem') . ':Reporte:index.html.twig', array(
            'form' => $form->createView()
        ));

    }




}