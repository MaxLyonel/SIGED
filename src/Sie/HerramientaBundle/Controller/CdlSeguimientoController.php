<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\CdlClubLectura;
use Sie\AppWebBundle\Entity\CdlEventos;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
use Sie\AppWebBundle\Controller\ChartController as ChartController;

class CdlSeguimientoController extends Controller
{
    public function indexAction(Request $request)
    { 
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $lista = array();
        $lista[0]['nombre'] = "CLUBES DE LECTURA POR UNIDAD EDUCATIVA";
        $lista[0]['ruta'] = "cdl_seguimiento_porsie";
        $lista[1]['nombre'] = "Unidades Educativas de su Jurisdicción que cuentan con Clubs de Lectura.";
        $lista[1]['ruta'] = "cdl_seguimiento_porjurisdiccion";
        /* $lista[2]['nombre'] = "CANTIDAD DE UNIDADES EDUCATIVAS QUE CUENTAN CON AL MENOS UN CLUB DE LECTURA";
        $lista[2]['ruta'] = "cdl_seguimiento_con_cdl";
        $lista[3]['nombre'] = "CANTIDAD DE UNIDADES EDUCATIVAS QUE NO CUENTAN CON CLUBES DE LECTURA";
        $lista[3]['ruta'] = "cdl_seguimiento_sin_cdl";
        $lista[4]['nombre'] = "CANTIDAD DE CLUBES DE LECTURA POR JURISDICCIÓN";
        $lista[4]['ruta'] = "cdl_seguimiento_clubes_lectura";
        $lista[5]['nombre'] = "CANTIDAD DE ESTUDIANTES QUE PARTICIPAN DE UN CLUB DE LECTURA POR AÑO DE ESCOLARIDAD";
        $lista[5]['ruta'] = "cdl_seguimiento_estudiantes_clubes_lectura";
        $lista[6]['nombre'] = "LISTADO DE LECTURAS Y PRODUCTOS DE LOS CLUBES DE LECTURA";
        $lista[6]['ruta'] = "cdl_seguimiento_lecturas_productos"; */
        return $this->render('SieHerramientaBundle:CdlSeguimiento:index.html.twig', array('lista'=>$lista,
            ));
    }
    
    public function cdlPorSieAction(Request $request)
    { 
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
                    ->add('codsie','text',array('label'=>'SIE:', 'required'=>true, 'attr'=>array('maxlength' => '8','class'=>'form-control validar')))
                    ->add('gestion','entity',array('label'=>'Gestión:','required'=>true,'class'=>'SieAppWebBundle:GestionTipo','query_builder'=>function(EntityRepository $g){
                        return $g->createQueryBuilder('g')->where('g.id>=2019')->orderBy('g.id','DESC');},'property'=>'gestion','empty_value' => false,'attr'=>array('class'=>'form-control')))
                    ->add('buscar', 'button', array('label'=> 'Buscar', 'attr'=>array('class'=>'form-control btn btn-primary','onclick'=>'buscarCdl()')))
                    ->getForm();
        
        return $this->render('SieHerramientaBundle:CdlSeguimiento:cdlPorSie.html.twig', array('form'=>$form->createView(),
            ));
    }

    /* public function cdlPorSieListaAction(Request $request)
    { 
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $rol = $this->session->get('roluser');
        $form = $request->get('form');
        //dump($form);die;
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $id_usuario);
        $query->bindValue(':sie', $form['codsie']);
        $query->bindValue(':rolId', $rol);
        $query->execute();
        $aTuicion = $query->fetchAll();
        if ($aTuicion[0]['get_ue_tuicion']) {
            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['codsie']);
            $query = $em->getConnection()->prepare("SELECT a.id as cdl_club_lectura_id,a.nombre_club,COUNT(DISTINCT b.id) as nro_eventos,COUNT(DISTINCT c.id) as nro_integrantes
                                                    FROM cdl_club_lectura a
                                                    JOIN institucioneducativa_sucursal ies ON a.institucioneducativasucursal_id=ies.id
                                                    LEFT JOIN cdl_eventos b on a.id=b.cdl_club_lectura_id
                                                    LEFT JOIN cdl_integrantes c ON a.id=c.cdl_club_lectura_id
                                                    WHERE ies.institucioneducativa_id=". $form['codsie'] ." and gestion_tipo_id=". $form['gestion'] ."
                                                    GROUP BY a.id,a.nombre_club");
            $query->execute();
            $data = $query->fetchAll();
            $msg="ok";
        }else{
            $data = array();
            $msg="No tiene tuición sobre la unidad educativa";
        }        
        return $this->render('SieHerramientaBundle:CdlSeguimiento:cdlPorSieLista.html.twig', array('data'=>$data,'msg'=>$msg,'institucioneducativa'=>$institucioneducativa
                    ));
    } */

    /**
     * Pagina Inicial - CANTIDAD DE UNIDADES EDUCATIVAS QUE CUENTAN CON AL MENOS UN CLUB DE LECTURA
     * Pavrgas
     * @param Request $request
     * @return type
     */
    public function uesConClubesDeLecturaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime();
        $gestionActual = date_format($fechaActual,'Y');
        //$gestionActual = 2016;

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
        $subEntidades = $this->buscaSubEntidadClubesAreaRol($codigoArea,$rolUsuario,$gestionActual);
        $entityEstadistica = $this->buscaEstadisticaClubesDeLecturaAreaRol($codigoArea,$rolUsuario,$gestionActual);
        //dump($entityEstadistica,$subEntidades,$entidad);
        if(count($subEntidades)>0 and isset($subEntidades)){
            foreach ($subEntidades as $key => $dato) {
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
            $chartDependencia = null;
            $chartArea = null;
        }else{
            $chartDependencia = $chartController->chartColumn($entityEstadistica[1],"Unidades Educativas según Dependencia",$gestionProcesada,1,"chartContainerDependencia");
            $chartArea = $chartController->chartSemiPieDonut3d($entityEstadistica[2],"Unidades Educativas según Área Geográfica",$gestionProcesada,1,"chartContainerArea");
        }
        //dump($chartDependencia);die;
        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $mensaje = "";
        if ($rolUsuario != 0){
            $mensaje = '$("#modal-bootstrap-tour").modal("hide");';
        }

        if ($entidad != '' and $subEntidades != ''){
            return $this->render('SieHerramientaBundle:CdlSeguimiento:UnidadEducativaConCdl.html.twig', array(
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
                return $this->render('SieHerramientaBundle:CdlSeguimiento:UnidadEducativaConCdl.html.twig', array(
                    'infoEntidad'=>$entidad, 
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
                return $this->render('SieHerramientaBundle:CdlSeguimiento:UnidadEducativaConCdl.html.twig', array(
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
            }            
        }        
    }

    /**
     * Busca el nombre de las entidades pertenecienten a un pais, departamento, distrito en funcion al tipo de rol
     * Pvargas
     * @param Request $request
     * @return type
     */
    public function buscaSubEntidadClubesAreaRol($area,$rol,$gestion) {

        $em = $this->getDoctrine()->getManager();
        
        if($rol == 8 or $rol == 20 or $rol == 0){
            $query = $em->getConnection()->prepare("SELECT 'Departamento' as nombreArea, dpt.id as codigo, dpt.departamento  as nombre, 7 as rolUsuario,coalesce(COUNT(DISTINCT ie.id),0) as cantidad
            FROM cdl_club_lectura a
            JOIN institucioneducativa_sucursal ies on a.institucioneducativasucursal_id=ies.id AND ies.gestion_tipo_id=". $gestion ."
            JOIN institucioneducativa ie on ie.id=ies.institucioneducativa_id
            JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            join distrito_tipo ddt on ddt.id=jg.distrito_tipo_id
            JOIN departamento_tipo dpt on dpt.id=ddt.departamento_tipo_id
            GROUP BY dpt.id,dpt.departamento");
        }elseif($rol == 7){
            $query = $em->getConnection()->prepare("SELECT 'Distrito' as nombreArea, ddt.id as codigo, ddt.distrito  as nombre, 10 as rolUsuario,coalesce(COUNT(DISTINCT ie.id),0) as cantidad
            FROM cdl_club_lectura a
            JOIN institucioneducativa_sucursal ies on a.institucioneducativasucursal_id=ies.id AND ies.gestion_tipo_id=". $gestion ."
            JOIN institucioneducativa ie on ie.id=ies.institucioneducativa_id
            JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            join distrito_tipo ddt on ddt.id=jg.distrito_tipo_id
            JOIN departamento_tipo dpt on dpt.id=ddt.departamento_tipo_id
            where dpt.id=". $area ."
            GROUP BY ddt.id,ddt.distrito");
        }elseif($rol == 10){
            $query = $em->getConnection()->prepare("SELECT 'Unidad Educativa' as nombreArea, ie.id as codigo, ie.id || ' - ' ||ie.institucioneducativa  as nombre, 9 as rolUsuario,coalesce(COUNT(DISTINCT ie.id),0) as cantidad
            FROM cdl_club_lectura a
            JOIN institucioneducativa_sucursal ies on a.institucioneducativasucursal_id=ies.id AND ies.gestion_tipo_id=". $gestion ."
            JOIN institucioneducativa ie on ie.id=ies.institucioneducativa_id
            JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            join distrito_tipo ddt on ddt.id=jg.distrito_tipo_id
            JOIN departamento_tipo dpt on dpt.id=ddt.departamento_tipo_id
            where ddt.id=". $area ."
            GROUP BY ie.id,ie.institucioneducativa");

        }elseif($rol == 9){
            $query = $em->getConnection()->prepare("SELECT ie.id || '-'||ie.institucioneducativa as jurisdiccion,'Club de Lectura' as nombreArea, a.id as codigo, a.nombre_club  as nombre,1 as cantidad,0 as rolUsuario, 1 as total
            FROM cdl_club_lectura a
            JOIN institucioneducativa_sucursal ies on a.institucioneducativasucursal_id=ies.id AND ies.gestion_tipo_id=". $gestion ."
            JOIN institucioneducativa ie on ie.id=ies.institucioneducativa_id
            JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            JOIN lugar_tipo lt on lt.id=jg.lugar_tipo_id_distrito
            JOIN lugar_tipo lt1 on lt1.id=lt.lugar_tipo_id
            WHERE ie.id=". $area);
        }
        
        $query->execute();
        $data = $query->fetchAll();
        if (count($data)>0){
            return $data;
        } else {
            return array();
        }
    }

    /**
     * Busca el detalle de Unidades Educativas que cuentan con clubes de lectura
     * Pvargas
     * @param Request $request
     * @return type
     */
    public function buscaEstadisticaClubesDeLecturaAreaRol($area,$rol,$gestion) {

        $em = $this->getDoctrine()->getManager();

        
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
                select ie.id,ie.institucioneducativa,ie.dependencia_tipo_id,dt.dependencia,case WHEN lt.area2001 ='R' then 'RURAL'ELSE 'URBANA'END as area2001,case WHEN lt.area2001 ='R' then 1 ELSE 2 END as area_id
                FROM institucioneducativa ie
                JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id AND ies.gestion_tipo_id=". $gestion ."
                join cdl_club_lectura a on a.institucioneducativasucursal_id=ies.id
                JOIN jurisdiccion_geografica jurg ON ie.le_juridicciongeografica_id = jurg.id
                JOIN lugar_tipo lt ON lt.id = jurg.lugar_tipo_id_localidad
                join distrito_tipo ddt on ddt.id=jurg.distrito_tipo_id and CASE WHEN ". $area ." > 9 THEN  ddt.id = ". $area ." else ddt.id > 0 END
                join dependencia_tipo dt on dt.id=ie.dependencia_tipo_id
                join departamento_tipo dpt on ddt.departamento_tipo_id=dpt.id and CASE WHEN ". $area ." <> 0 and ". $area ."<10 THEN  dpt.id = ". $area ." ELSE dpt.id IN (1,2,3,4,5,6,7,8,9) END
                WHERE ie.institucioneducativa_tipo_id =1
                AND ie.estadoinstitucion_tipo_id = 10
                AND ie.institucioneducativa_acreditacion_tipo_id = 1
                )
                select 0 as tipo_id, 'total' as tipo_nombre,'total_general' as detalle,0 as id,count(DISTINCT id) as cantidad,count(DISTINCT id) as total
                from tabla
                
                UNION ALL
                select 1 as tipo_id, 'Dependencia' as tipo_nombre,dependencia as detalle,dependencia_tipo_id as id,count(DISTINCT id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from tabla
                GROUP BY dependencia_tipo_id,dependencia
                
                UNION ALL
                select 2 as tipo_id, 'Area' as tipo_nombre,area2001 as detalle,area_id as id,count(DISTINCT id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from tabla
                GROUP BY area2001,area_id
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

    /* public function cdlPorSieListaAction(Request $request)
    { 
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $rol = $this->session->get('roluser');
        $form = $request->get('form');
        //dump($form);die;
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $id_usuario);
        $query->bindValue(':sie', $form['codsie']);
        $query->bindValue(':rolId', $rol);
        $query->execute();
        $aTuicion = $query->fetchAll();
        if ($aTuicion[0]['get_ue_tuicion']) {
            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['codsie']);
            $query = $em->getConnection()->prepare("SELECT a.id as cdl_club_lectura_id,a.nombre_club,COUNT(DISTINCT b.id) as nro_eventos,COUNT(DISTINCT c.id) as nro_integrantes
                                                    FROM cdl_club_lectura a
                                                    JOIN institucioneducativa_sucursal ies ON a.institucioneducativasucursal_id=ies.id
                                                    LEFT JOIN cdl_eventos b on a.id=b.cdl_club_lectura_id
                                                    LEFT JOIN cdl_integrantes c ON a.id=c.cdl_club_lectura_id
                                                    WHERE ies.institucioneducativa_id=". $form['codsie'] ." and gestion_tipo_id=". $form['gestion'] ."
                                                    GROUP BY a.id,a.nombre_club");
            $query->execute();
            $data = $query->fetchAll();
            $msg="ok";
        }else{
            $data = array();
            $msg="No tiene tuición sobre la unidad educativa";
        }        
        return $this->render('SieHerramientaBundle:CdlSeguimiento:cdlPorSieLista.html.twig', array('data'=>$data,'msg'=>$msg,'institucioneducativa'=>$institucioneducativa
                    ));
    } */

    /**
     * Pagina Inicial - CANTIDAD DE UNIDADES EDUCATIVAS QUE CUENTAN CON AL MENOS UN CLUB DE LECTURA
     * Pavrgas
     * @param Request $request
     * @return type
     */
    public function uesSinClubesDeLecturaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime();
        $gestionActual = date_format($fechaActual,'Y');
        //$gestionActual = 2016;

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
        $subEntidades = $this->buscaSubEntidadSinClubesAreaRol($codigoArea,$rolUsuario,$gestionActual);
        $entityEstadistica = $this->buscaEstadisticaSinClubesDeLecturaAreaRol($codigoArea,$rolUsuario,$gestionActual);
        //dump($entityEstadistica,$subEntidades,$entidad);
        if(count($subEntidades)>0 and isset($subEntidades)){
            foreach ($subEntidades as $key => $dato) {
                $subEntidades[$key]['total_general'] = $entityEstadistica[0][0]['total'];
            }
        } else {
            $subEntidades = null;
        }

        $fechaEstadistica = $fechaActual->format('d-m-Y H:i:s');
        $gestionProcesada = $gestionActual;
        $link = true;
        if ($rolUsuario == 10){
            $link = false;
        }
        $chartDependencia = $chartController->chartColumn($entityEstadistica[1],"Unidades Educativas según Dependencia",$gestionProcesada,1,"chartContainerDependencia");
        $chartArea = $chartController->chartSemiPieDonut3d($entityEstadistica[2],"Unidades Educativas según Área Geográfica",$gestionProcesada,1,"chartContainerArea");
        //dump($chartDependencia);die;
        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $mensaje = "";
        if ($rolUsuario != 0){
            $mensaje = '$("#modal-bootstrap-tour").modal("hide");';
        }

        if ($entidad != '' and $subEntidades != ''){
            return $this->render('SieHerramientaBundle:CdlSeguimiento:UnidadEducativaSinCdl.html.twig', array(
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
                return $this->render('SieHerramientaBundle:CdlSeguimiento:UnidadEducativaSinCdl.html.twig', array(
                    'infoEntidad'=>$entidad, 
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
                return $this->render('SieHerramientaBundle:CdlSeguimiento:UnidadEducativaSinCdl.html.twig', array(
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
            }            
        }        
    }

    /**
     * Busca el nombre de las entidades pertenecienten a un pais, departamento, distrito en funcion al tipo de rol
     * Pvargas
     * @param Request $request
     * @return type
     */
    public function buscaSubEntidadSinClubesAreaRol($area,$rol,$gestion) {

        $em = $this->getDoctrine()->getManager();
        
        if($rol == 8 or $rol == 20 or $rol == 0){
            $query = $em->getConnection()->prepare("SELECT 'Departamento' as nombreArea, dpt.id as codigo, dpt.departamento  as nombre, 7 as rolUsuario,coalesce(COUNT(DISTINCT ie.id),0) as cantidad
            FROM institucioneducativa ie
            JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id AND ies.gestion_tipo_id=". $gestion ."
            LEFT JOIN cdl_club_lectura a on a.institucioneducativasucursal_id=ies.id
            JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            join distrito_tipo ddt on ddt.id=jg.distrito_tipo_id
            JOIN departamento_tipo dpt on dpt.id=ddt.departamento_tipo_id
            WHERE a.id ISNULL
            GROUP BY dpt.id,dpt.departamento");
        }elseif($rol == 7){
            $query = $em->getConnection()->prepare("SELECT 'Distrito' as nombreArea, ddt.id as codigo, ddt.distrito  as nombre, 10 as rolUsuario,coalesce(COUNT(DISTINCT ie.id),0) as cantidad
            FROM institucioneducativa ie
            JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id AND ies.gestion_tipo_id=". $gestion ."
            LEFT JOIN cdl_club_lectura a on a.institucioneducativasucursal_id=ies.id
            JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            join distrito_tipo ddt on ddt.id=jg.distrito_tipo_id
            JOIN departamento_tipo dpt on dpt.id=ddt.departamento_tipo_id
            where dpt.id=". $area ." AND a.id ISNULL
            GROUP BY ddt.id,ddt.distrito");
        }elseif($rol == 10){
            $query = $em->getConnection()->prepare("SELECT 'Unidad Educativa' as nombreArea, ie.id as codigo, ie.id || ' - ' ||ie.institucioneducativa  as nombre, 9 as rolUsuario,coalesce(COUNT(DISTINCT ie.id),0) as cantidad
            FROM institucioneducativa ie
            JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id AND ies.gestion_tipo_id=". $gestion ."
            LEFT JOIN cdl_club_lectura a on a.institucioneducativasucursal_id=ies.id
            JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            join distrito_tipo ddt on ddt.id=jg.distrito_tipo_id
            JOIN departamento_tipo dpt on dpt.id=ddt.departamento_tipo_id
            where ddt.id=". $area ." AND a.id ISNULL
            GROUP BY ie.id,ie.institucioneducativa");

        }elseif($rol == 9){
            $query = $em->getConnection()->prepare("SELECT ie.id || '-'||ie.institucioneducativa as jurisdiccion,'Club de Lectura' as nombreArea, a.id as codigo, a.nombre_club  as nombre,1 as cantidad,0 as rolUsuario, 1 as total
            FROM cdl_club_lectura a
            JOIN institucioneducativa_sucursal ies on a.institucioneducativasucursal_id=ies.id AND ies.gestion_tipo_id=". $gestion ."
            JOIN institucioneducativa ie on ie.id=ies.institucioneducativa_id
            JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            JOIN lugar_tipo lt on lt.id=jg.lugar_tipo_id_distrito
            JOIN lugar_tipo lt1 on lt1.id=lt.lugar_tipo_id
            WHERE ie.id=". $area);
        }
        
        $query->execute();
        $data = $query->fetchAll();
        if (count($data)>0){
            return $data;
        } else {
            return array();
        }
    }

    /**
     * Busca el detalle de Unidades Educativas que no cuentan con clubes de lectura
     * Pvargas
     * @param Request $request
     * @return type
     */
    public function buscaEstadisticaSinClubesDeLecturaAreaRol($area,$rol,$gestion) {

        $em = $this->getDoctrine()->getManager();

        
        if ($rol == 9){
            $query = $em->getConnection()->prepare("
            ");
            $query->execute();
            $aDato = $query->fetchAll();
        }else{
            $queryEntidad = $em->getConnection()->prepare("with tabla as (
                select ie.id,ie.institucioneducativa,ie.dependencia_tipo_id,dt.dependencia,case WHEN lt.area2001 ='R' then 'RURAL'ELSE 'URBANA'END as area2001,case WHEN lt.area2001 ='R' then 1 ELSE 2 END as area_id
                FROM institucioneducativa ie
                JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id AND ies.gestion_tipo_id=". $gestion ."
                LEFT join cdl_club_lectura a on a.institucioneducativasucursal_id=ies.id
                JOIN jurisdiccion_geografica jurg ON ie.le_juridicciongeografica_id = jurg.id
                JOIN lugar_tipo lt ON lt.id = jurg.lugar_tipo_id_localidad
                join distrito_tipo ddt on ddt.id=jurg.distrito_tipo_id and CASE WHEN ". $area ." > 9 THEN  ddt.id = ". $area ." else ddt.id > 0 END
                join dependencia_tipo dt on dt.id=ie.dependencia_tipo_id
                join departamento_tipo dpt on ddt.departamento_tipo_id=dpt.id and CASE WHEN ". $area ." <> 0 and ". $area ."<10 THEN  dpt.id = ". $area ." ELSE dpt.id IN (1,2,3,4,5,6,7,8,9) END
                WHERE ie.institucioneducativa_tipo_id =1
                AND ie.estadoinstitucion_tipo_id = 10
                AND ie.institucioneducativa_acreditacion_tipo_id = 1
                AND a.id ISNULL
                )
                select 0 as tipo_id, 'total' as tipo_nombre,'total_general' as detalle,0 as id,count(DISTINCT id) as cantidad,count(DISTINCT id) as total
                from tabla
                
                UNION ALL
                select 1 as tipo_id, 'Dependencia' as tipo_nombre,dependencia as detalle,dependencia_tipo_id as id,count(DISTINCT id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from tabla
                GROUP BY dependencia_tipo_id,dependencia
                
                UNION ALL
                select 2 as tipo_id, 'Area' as tipo_nombre,area2001 as detalle,area_id as id,count(DISTINCT id) as cantidad,(select count(DISTINCT id) as total
                from tabla)
                from tabla
                GROUP BY area2001,area_id
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

    public function clubesLecturaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime();
        $gestionActual = date_format($fechaActual,'Y');
        //$gestionActual = 2016;

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
        $subEntidades = $this->buscaSubEntidadClubesLecturaAreaRol($codigoArea,$rolUsuario,$gestionActual);
        $entityEstadistica = $this->buscaEstadisticaClubesLecturaAreaRol($codigoArea,$rolUsuario,$gestionActual);
        //dump($entityEstadistica,$subEntidades,$entidad);
        if(count($subEntidades)>0 and isset($subEntidades)){
            foreach ($subEntidades as $key => $dato) {
                $subEntidades[$key]['total_general'] = $entityEstadistica[0][0]['total'];
            }
        } else {
            $subEntidades = null;
        }

        $fechaEstadistica = $fechaActual->format('d-m-Y H:i:s');
        $gestionProcesada = $gestionActual;
        $link = true;
        if ($rolUsuario == 10){
            $link = false;
        }
        $chartDependencia = $chartController->chartColumn($entityEstadistica[1],"Unidades Educativas según Dependencia",$gestionProcesada,1,"chartContainerDependencia");
        $chartArea = $chartController->chartSemiPieDonut3d($entityEstadistica[2],"Unidades Educativas según Área Geográfica",$gestionProcesada,1,"chartContainerArea");
        //dump($chartDependencia);die;
        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $mensaje = "";
        if ($rolUsuario != 0){
            $mensaje = '$("#modal-bootstrap-tour").modal("hide");';
        }

        if ($entidad != '' and $subEntidades != ''){
            return $this->render('SieHerramientaBundle:CdlSeguimiento:ClubesLectura.html.twig', array(
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
                return $this->render('SieHerramientaBundle:CdlSeguimiento:ClubesLectura.html.twig', array(
                    'infoEntidad'=>$entidad, 
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
                return $this->render('SieHerramientaBundle:CdlSeguimiento:ClubesLectura.html.twig', array(
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
            }            
        }        
    }

    /**
     * Busca el nombre de las entidades pertenecienten a un pais, departamento, distrito en funcion al tipo de rol
     * Pvargas
     * @param Request $request
     * @return type
     */
    public function buscaSubEntidadClubesLecturaAreaRol($area,$rol,$gestion) {

        $em = $this->getDoctrine()->getManager();
        
        if($rol == 8 or $rol == 20 or $rol == 0){
            $query = $em->getConnection()->prepare("SELECT 'Departamento' as nombreArea, dpt.id as codigo, dpt.departamento  as nombre, 7 as rolUsuario,coalesce(COUNT(*),0) as cantidad
            FROM institucioneducativa ie
            JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id AND ies.gestion_tipo_id=". $gestion ."
            JOIN cdl_club_lectura a on a.institucioneducativasucursal_id=ies.id
            JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            join distrito_tipo ddt on ddt.id=jg.distrito_tipo_id
            JOIN departamento_tipo dpt on dpt.id=ddt.departamento_tipo_id
            GROUP BY dpt.id,dpt.departamento");
        }elseif($rol == 7){
            $query = $em->getConnection()->prepare("SELECT 'Distrito' as nombreArea, ddt.id as codigo, ddt.distrito  as nombre, 10 as rolUsuario,coalesce(COUNT(*),0) as cantidad
            FROM institucioneducativa ie
            JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id AND ies.gestion_tipo_id=". $gestion ."
            JOIN cdl_club_lectura a on a.institucioneducativasucursal_id=ies.id
            JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            join distrito_tipo ddt on ddt.id=jg.distrito_tipo_id
            JOIN departamento_tipo dpt on dpt.id=ddt.departamento_tipo_id
            where dpt.id=". $area ."
            GROUP BY ddt.id,ddt.distrito");
        }elseif($rol == 10){
            $query = $em->getConnection()->prepare("SELECT 'Unidad Educativa' as nombreArea, ie.id as codigo, ie.id || ' - ' ||ie.institucioneducativa  as nombre, 9 as rolUsuario,coalesce(COUNT(*),0) as cantidad
            FROM institucioneducativa ie
            JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id AND ies.gestion_tipo_id=". $gestion ."
            JOIN cdl_club_lectura a on a.institucioneducativasucursal_id=ies.id
            JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            join distrito_tipo ddt on ddt.id=jg.distrito_tipo_id
            JOIN departamento_tipo dpt on dpt.id=ddt.departamento_tipo_id
            where ddt.id=". $area ."
            GROUP BY ie.id,ie.institucioneducativa");

        }elseif($rol == 9){
            $query = $em->getConnection()->prepare("SELECT ie.id || '-'||ie.institucioneducativa as jurisdiccion,'Club de Lectura' as nombreArea, a.id as codigo, a.nombre_club  as nombre,1 as cantidad,0 as rolUsuario, 1 as total
            FROM cdl_club_lectura a
            JOIN institucioneducativa_sucursal ies on a.institucioneducativasucursal_id=ies.id AND ies.gestion_tipo_id=". $gestion ."
            JOIN institucioneducativa ie on ie.id=ies.institucioneducativa_id
            JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            JOIN lugar_tipo lt on lt.id=jg.lugar_tipo_id_distrito
            JOIN lugar_tipo lt1 on lt1.id=lt.lugar_tipo_id
            WHERE ie.id=". $area);
        }
        
        $query->execute();
        $data = $query->fetchAll();
        if (count($data)>0){
            return $data;
        } else {
            return array();
        }
    }

    /**
     * Busca el detalle de Unidades Educativas que no cuentan con clubes de lectura
     * Pvargas
     * @param Request $request
     * @return type
     */
    public function buscaEstadisticaClubesLecturaAreaRol($area,$rol,$gestion) {

        $em = $this->getDoctrine()->getManager();

        
        if ($rol == 9){
            $query = $em->getConnection()->prepare("
            ");
            $query->execute();
            $aDato = $query->fetchAll();
        }else{
            $queryEntidad = $em->getConnection()->prepare("with tabla as (
                select ie.id,ie.institucioneducativa,ie.dependencia_tipo_id,dt.dependencia,case WHEN lt.area2001 ='R' then 'RURAL'ELSE 'URBANA'END as area2001,case WHEN lt.area2001 ='R' then 1 ELSE 2 END as area_id
                FROM institucioneducativa ie
                JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id AND ies.gestion_tipo_id=". $gestion ."
                join cdl_club_lectura a on a.institucioneducativasucursal_id=ies.id
                JOIN jurisdiccion_geografica jurg ON ie.le_juridicciongeografica_id = jurg.id
                JOIN lugar_tipo lt ON lt.id = jurg.lugar_tipo_id_localidad
                join distrito_tipo ddt on ddt.id=jurg.distrito_tipo_id and CASE WHEN ". $area ." > 9 THEN  ddt.id = ". $area ." else ddt.id > 0 END
                join dependencia_tipo dt on dt.id=ie.dependencia_tipo_id
                join departamento_tipo dpt on ddt.departamento_tipo_id=dpt.id and CASE WHEN ". $area ." <> 0 and ". $area ."<10 THEN  dpt.id = ". $area ." ELSE dpt.id IN (1,2,3,4,5,6,7,8,9) END
                WHERE ie.institucioneducativa_tipo_id =1
                AND ie.estadoinstitucion_tipo_id = 10
                AND ie.institucioneducativa_acreditacion_tipo_id = 1
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

    public function estudiantesClubesLecturaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime();
        $gestionActual = date_format($fechaActual,'Y');
        //$gestionActual = 2016;

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
        $subEntidades = $this->buscaSubEntidadEstudiantesClubesLecturaAreaRol($codigoArea,$rolUsuario,$gestionActual);
        $entityEstadistica = $this->buscaEstadisticaEstudiantesClubesLecturaAreaRol($codigoArea,$rolUsuario,$gestionActual);
        //dump($entityEstadistica[5]);die;
        if(count($subEntidades)>0 and isset($subEntidades)){
            foreach ($subEntidades as $key => $dato) {
                $subEntidades[$key]['total_general'] = $entityEstadistica[0][0]['total'];
            }
        } else {
            $subEntidades = null;
        }

        $fechaEstadistica = $fechaActual->format('d-m-Y H:i:s');
        $gestionProcesada = $gestionActual;
        $link = true;
        if ($rolUsuario == 10){
            $link = false;
        }
        $chartDependencia = $chartController->chartColumn($entityEstadistica[1],"Estudiantes en clubes de lectura según Dependencia",$gestionProcesada,3,"chartContainerDependencia");
        $chartArea = $chartController->chartSemiPieDonut3d($entityEstadistica[2],"Estudiantes en clubes de lectura según Área Geográfica",$gestionProcesada,2,"chartContainerArea");
        $chartGenero = $chartController->chartPie($entityEstadistica[3],"Estudiantes en clubes de lectura según Género",$gestionProcesada,1,"chartContainerGenero");
        $chartNivel = $chartController->chartDonut3d($entityEstadistica[4],"Estudiantes en clubes de lectura según nivel",$gestionProcesada,2,"chartContainerNivel");
        $chartNivelGrado = $chartController->chartDonutInformacionGeneralNivelGrado($entityEstadistica[5],"Estudiantes en clubes de lectura según Nivel de Estudio y Año de Escolaridad ",$gestionProcesada,1,"chartContainerNivelGrado");
        //dump($chartNivelGrado);die;
        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $mensaje = "";
        if ($rolUsuario != 0){
            $mensaje = '$("#modal-bootstrap-tour").modal("hide");';
        }

        if ($entidad != '' and $subEntidades != ''){
            return $this->render('SieHerramientaBundle:CdlSeguimiento:EstudiantesClubesLectura.html.twig', array(
                'infoEntidad'=>$entidad,
                'infoSubEntidad'=>$subEntidades, 
                'infoEstadistica'=>$entityEstadistica,
                'datoGraficoDependencia'=>$chartDependencia,
                'datoGraficoArea'=>$chartArea,
                'datoGraficoGenero'=>$chartGenero,
                'datoGraficoNivel'=>$chartNivel,
                'datoGraficoNivelGrado'=>$chartNivelGrado,
                'gestion'=>$gestionProcesada,
                'link'=>$link,
                'mensaje'=>$mensaje,
                'fechaEstadistica'=>$fechaEstadistica,
                'form' => $defaultController->createLoginForm()->createView()
            ));    
        } else {
            if ($entidad != ''){
                return $this->render('SieHerramientaBundle:CdlSeguimiento:EstudiantesClubesLectura.html.twig', array(
                    'infoEntidad'=>$entidad, 
                    'infoEstadistica'=>$entityEstadistica,                
                    'datoGraficoDependencia'=>$chartDependencia,
                    'datoGraficoArea'=>$chartArea,
                    'datoGraficoGenero'=>$chartGenero,
                    'datoGraficoNivel'=>$chartNivel,
                    'datoGraficoNivelGrado'=>$chartNivelGrado,
                    'gestion'=>$gestionProcesada,
                    'link'=>$link,
                    'mensaje'=>$mensaje,
                    'fechaEstadistica'=>$fechaEstadistica,
                    'form' => $defaultController->createLoginForm()->createView()
                ));  
            } else {
                return $this->render('SieHerramientaBundle:CdlSeguimiento:EstudiantesClubesLectura.html.twig', array(
                    'infoSubEntidad'=>$subEntidades, 
                    'infoEstadistica'=>$entityEstadistica,                    
                    'datoGraficoDependencia'=>$chartDependencia,
                    'datoGraficoArea'=>$chartArea,
                    'datoGraficoGenero'=>$chartGenero,
                    'datoGraficoNivel'=>$chartNivel,
                    'datoGraficoNivelGrado'=>$chartNivelGrado,
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
     * Busca el nombre de las entidades pertenecienten a un pais, departamento, distrito en funcion al tipo de rol
     * Pvargas
     * @param Request $request
     * @return type
     */
    public function buscaSubEntidadEstudiantesClubesLecturaAreaRol($area,$rol,$gestion) {

        $em = $this->getDoctrine()->getManager();
        
        if($rol == 8 or $rol == 20 or $rol == 0){
            $query = $em->getConnection()->prepare("SELECT 'Departamento' as nombreArea, dpt.id as codigo, dpt.departamento  as nombre, 7 as rolUsuario,coalesce(COUNT(*),0) as cantidad
            FROM institucioneducativa ie
            JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id AND ies.gestion_tipo_id=". $gestion ."
            join cdl_club_lectura a on a.institucioneducativasucursal_id=ies.id
            join cdl_integrantes b on a.id=b.cdl_club_lectura_id
            join estudiante_inscripcion ei on ei.id=b.estudiante_inscripcion_id
            join estudiante e on e.id=ei.estudiante_id
            JOIN jurisdiccion_geografica jurg ON ie.le_juridicciongeografica_id = jurg.id
            join distrito_tipo ddt on ddt.id=jurg.distrito_tipo_id 
            join departamento_tipo dpt on ddt.departamento_tipo_id=dpt.id
            WHERE ie.institucioneducativa_tipo_id =1
            AND ie.estadoinstitucion_tipo_id = 10
            AND ie.institucioneducativa_acreditacion_tipo_id = 1
            GROUP BY dpt.id,dpt.departamento");
        }elseif($rol == 7){
            $query = $em->getConnection()->prepare("SELECT 'Distrito' as nombreArea, ddt.id as codigo, ddt.distrito  as nombre, 10 as rolUsuario,coalesce(COUNT(*),0) as cantidad
            FROM institucioneducativa ie
            JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id AND ies.gestion_tipo_id=". $gestion ."
            join cdl_club_lectura a on a.institucioneducativasucursal_id=ies.id
            join cdl_integrantes b on a.id=b.cdl_club_lectura_id
            join estudiante_inscripcion ei on ei.id=b.estudiante_inscripcion_id
            join estudiante e on e.id=ei.estudiante_id
            JOIN jurisdiccion_geografica jurg ON ie.le_juridicciongeografica_id = jurg.id
            join distrito_tipo ddt on ddt.id=jurg.distrito_tipo_id 
            join departamento_tipo dpt on ddt.departamento_tipo_id=dpt.id
            where dpt.id=". $area ."
            GROUP BY ddt.id,ddt.distrito");
        }elseif($rol == 10){
            $query = $em->getConnection()->prepare("SELECT 'Unidad Educativa' as nombreArea, ie.id as codigo, ie.id || ' - ' ||ie.institucioneducativa  as nombre, 9 as rolUsuario,coalesce(COUNT(*),0) as cantidad
            FROM institucioneducativa ie
            JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id AND ies.gestion_tipo_id=". $gestion ."
            join cdl_club_lectura a on a.institucioneducativasucursal_id=ies.id
            join cdl_integrantes b on a.id=b.cdl_club_lectura_id
            join estudiante_inscripcion ei on ei.id=b.estudiante_inscripcion_id
            join estudiante e on e.id=ei.estudiante_id
            JOIN jurisdiccion_geografica jurg ON ie.le_juridicciongeografica_id = jurg.id
            join distrito_tipo ddt on ddt.id=jurg.distrito_tipo_id 
            join departamento_tipo dpt on ddt.departamento_tipo_id=dpt.id
            where ddt.id=". $area ."
            GROUP BY ie.id,ie.institucioneducativa");

        }elseif($rol == 9){
            $query = $em->getConnection()->prepare("SELECT ie.id || '-'||ie.institucioneducativa as jurisdiccion,'Club de Lectura' as nombreArea, a.id as codigo, a.nombre_club  as nombre,1 as cantidad,0 as rolUsuario, 1 as total
            FROM cdl_club_lectura a
            JOIN institucioneducativa_sucursal ies on a.institucioneducativasucursal_id=ies.id AND ies.gestion_tipo_id=". $gestion ."
            JOIN institucioneducativa ie on ie.id=ies.institucioneducativa_id
            JOIN jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            JOIN lugar_tipo lt on lt.id=jg.lugar_tipo_id_distrito
            JOIN lugar_tipo lt1 on lt1.id=lt.lugar_tipo_id
            WHERE ie.id=". $area);
        }
        
        $query->execute();
        $data = $query->fetchAll();
        if (count($data)>0){
            return $data;
        } else {
            return array();
        }
    }

    /**
     * Busca el detalle de Unidades Educativas que no cuentan con clubes de lectura
     * Pvargas
     * @param Request $request
     * @return type
     */
    public function buscaEstadisticaEstudiantesClubesLecturaAreaRol($area,$rol,$gestion) {

        $em = $this->getDoctrine()->getManager();

        
        if ($rol == 9){
            $query = $em->getConnection()->prepare("
            ");
            $query->execute();
            $aDato = $query->fetchAll();
        }else{
            $queryEntidad = $em->getConnection()->prepare("with tabla as (
                select ie.id,ie.institucioneducativa,ie.dependencia_tipo_id,dt.dependencia,case WHEN lt.area2001 ='R' then 1 ELSE 2 END as area_id,case WHEN lt.area2001 ='R' then 'RURAL'ELSE 'URBANA'END as area2001,e.genero_tipo_id,g.genero,iec.nivel_tipo_id,nt.nivel,iec.grado_tipo_id,gt.grado
                FROM institucioneducativa ie
                JOIN institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id AND ies.gestion_tipo_id=". $gestion ."
                join cdl_club_lectura a on a.institucioneducativasucursal_id=ies.id
                join cdl_integrantes b on a.id=b.cdl_club_lectura_id
                join estudiante_inscripcion ei on ei.id=b.estudiante_inscripcion_id
                join estudiante e on e.id=ei.estudiante_id
                join genero_tipo g on g.id=e.genero_tipo_id
                join institucioneducativa_curso iec on iec.id=ei.institucioneducativa_curso_id
                join nivel_tipo nt on nt.id=iec.nivel_tipo_id
                join grado_tipo gt on gt.id=iec.grado_tipo_id
                JOIN jurisdiccion_geografica jurg ON ie.le_juridicciongeografica_id = jurg.id
                JOIN lugar_tipo lt ON lt.id = jurg.lugar_tipo_id_localidad
                join distrito_tipo ddt on ddt.id=jurg.distrito_tipo_id and CASE WHEN ". $area ." > 9 THEN  ddt.id = ". $area ." else ddt.id > 0 END
                join dependencia_tipo dt on dt.id=ie.dependencia_tipo_id
                join departamento_tipo dpt on ddt.departamento_tipo_id=dpt.id and CASE WHEN ". $area ." <> 0 and ". $area ."<10 THEN  dpt.id = ". $area ." ELSE dpt.id IN (1,2,3,4,5,6,7,8,9) END
                WHERE ie.institucioneducativa_tipo_id =1
                AND ie.estadoinstitucion_tipo_id = 10
                AND ie.institucioneducativa_acreditacion_tipo_id = 1
                )
                select 0 as tipo_id, 'total' as tipo_nombre,'total_general' as detalle,'0' as id,count(*) as cantidad,count(*) as total
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
                (select 3 as tipo_id, 'Genero' as tipo_nombre,genero as detalle,genero_tipo_id as id,count(*) as cantidad,(select count(*) as total
                from tabla)
                from tabla
                GROUP BY genero_tipo_id,genero)
                
                UNION ALL
                (select 4 as tipo_id, 'Nivel' as tipo_nombre,nivel as detalle,nivel_tipo_id as id,count(*) as cantidad,(select count(*) as total
                from tabla)
                from tabla
                GROUP BY nivel_tipo_id,nivel
                ORDER BY id)
                
                UNION ALL
                (select 5 as tipo_id, case when nivel_tipo_id=11 then 'Inicial' when nivel_tipo_id=12 then 'Primaria' when nivel_tipo_id=13 then 'Secundaria' end  as tipo_nombre,grado as detalle,grado_tipo_id as id,count(*) as cantidad,(select count(*) as total
                from tabla)
                from tabla
                GROUP BY nivel_tipo_id,nivel,grado_tipo_id,grado
                ORDER BY tipo_nombre,id)
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
}
