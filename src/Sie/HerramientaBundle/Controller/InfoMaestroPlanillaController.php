<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Entity\EmparejaSiePlanilla;
use Sie\AppWebBundle\Entity\EmparejaPlanillaAsignaturaTipo;
use Sie\AppWebBundle\Entity\SolucionComparacionPlanillaTipo;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoValidacionpersonal;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\NuevoMaestroInscripcion;
use Sie\AppWebBundle\Entity\EliminaRegistroPlanillaTipo;

/**
 * EstudianteInscripcion controller.
 *
 */
class InfoMaestroPlanillaController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    /**
     * list of request
     *
     */
    public function indexAction(Request $request) {
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $institucion = $this->session->get('ie_id');
        $gestion = $request->getSession()->get('currentyear');
        // $mes = 6;
        /*
        * verificamos si tiene tuicion
        */
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
        $query->bindValue(':sie', $this->session->get('ie_id'));
        $query->bindValue(':rolId', $this->session->get('roluser'));
        $query->execute();
        $aTuicion = $query->fetchAll();
        
        /*********ASIGNAMOS MESES***********/
        $mesaux = $em->getRepository('SieAppWebBundle:RegistroConsolidacionOperativoPlanilla')->findBy(['gestion' => $gestion,
        'institucioneducativaId' => 99999999,]);
        $maxMes = 0; 
        foreach ($mesaux as $registro) {
            if ($registro->getMes() > $maxMes) {
                $maxMes = $registro->getMes();
            }
        }
        if (!$request->getSession()->get('idPlanillaMes')){
            $mes = $maxMes;
        } else {
            $mes = $request->getSession()->get('idPlanillaMes');
        }

        if ($aTuicion[0]['get_ue_tuicion']) {
            $request->getSession()->set('idInstitucion', $institucion);
            $request->getSession()->set('idGestion', $gestion);
            $request->getSession()->set('idPlanillaMes',$mes);
        } else {
            $request->getSession()->set('idInstitucion', $institucion);
            $request->getSession()->set('idGestion', $gestion);
            $request->getSession()->set('idPlanillaMes', $mes);
        }

        $swop = $this->vercierreop($gestion,$institucion,$mes);
        
        if ($swop === 0){
            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
            $gestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($request->getSession()->get('currentyear'));
            
            $query = $em->getConnection()->prepare("  
                        select a.id, a.ci, a.apellidos_nombre, a.financiamiento_sie, a.cargo_sie, coalesce(a.servicio,'') servicio, coalesce(a.item,'') item, coalesce (a.func_doc,'') func_doc, a.solucion_comparacion_planilla_tipo_id, coalesce(a.observacion,'') observacion
                        from (
                        select a.id,b.ci_pla||case when trim(coalesce(b.comp_pla,''))='' then '' else '-'||b.comp_pla end as ci
                        ,trim(trim(coalesce(b.paterno,''))||' '||trim(coalesce(b.materno,'')))||' '||trim(trim(coalesce(b.nombre1,''))||' '||trim(coalesce(b.nombre2,''))) as apellidos_nombre,
                        (select financiamiento from financiamiento_tipo where id=a.financiamiento_tipo_id) as financiamiento_sie,(select cargo from cargo_tipo where id=a.cargo_tipo_id) as cargo_sie
                        ,b.servicio,b.item,(select descripcion from planillas_funcioncargo_tipo where id=b.cod_func) as func_doc,a.solucion_comparacion_planilla_tipo_id,a.observacion
                        from public.empareja_sie_planilla a 
                            inner join planilla_pago_comparativo b on a.planilla_pago_comparativo_id_sie=b.id
                        where institucioneducativa_id=".$institucion."
                        and gestion_tipo_id=".$gestion." 
                        and mes_tipo_id= ".$mes."
                        and a.maestro_inscripcion_id_sie is not null and a.planilla_pago_comparativo_id_sie is not null and nuevo_maestro_inscripcion_id is null
                        and a.cargo_tipo_id=0
                        union 
                        select a.id,b.ci_pla||case when trim(coalesce(b.comp_pla,''))='' then '' else '-'||b.comp_pla end as ci
                        ,trim(trim(coalesce(b.paterno,''))||' '||trim(coalesce(b.materno,'')))||' '||trim(trim(coalesce(b.nombre1,''))||' '||trim(coalesce(b.nombre2,''))) as apellidos_nombre,
                        (select financiamiento from financiamiento_tipo where id=a.financiamiento_tipo_id) as financiamiento_sie,(select cargo from cargo_tipo where id=a.cargo_tipo_id) as cargo_sie
                        ,b.servicio,b.item,(select descripcion from planillas_funcioncargo_tipo where id=b.cod_func) as func_doc,a.solucion_comparacion_planilla_tipo_id,a.observacion
                        from public.empareja_sie_planilla a 
                            inner join planilla_pago_comparativo b on a.planilla_pago_comparativo_id_sie=b.id
                        where institucioneducativa_id=".$institucion." 
                        and gestion_tipo_id=".$gestion." 
                        and mes_tipo_id= ".$mes."
                        and nuevo_maestro_inscripcion_id is null and a.maestro_inscripcion_id_sie is null and a.planilla_pago_comparativo_id_sie is not null 
                        and b.cod_func=2
                        union 
                        select a.id,b.ci||case when trim(coalesce(b.complemento,''))='' then '' else '-'||b.complemento end as ci
                        ,trim(trim(coalesce(b.paterno,''))||' '||trim(coalesce(b.materno,'')))||' '||trim(coalesce(b.nombre,'')) as apellidos_nombre,
                        (select financiamiento from financiamiento_tipo where id=a.financiamiento_tipo_id) as financiamiento_sie,(select cargo from cargo_tipo where id=a.cargo_tipo_id) as cargo_sie
                        ,null::character varying(10) as servicio,null::character varying(7) as item,null::character varying(50) as func_doc,a.solucion_comparacion_planilla_tipo_id,a.observacion
                        from empareja_sie_planilla  a
                            inner join nuevo_maestro_inscripcion b on a.nuevo_maestro_inscripcion_id=b.id
                        where a.institucioneducativa_id=".$institucion." 
                        and a.gestion_tipo_id=".$gestion." 
                        and a.mes_tipo_id= ".$mes." 
                        and nuevo_maestro_inscripcion_id is not null and a.maestro_inscripcion_id_sie is null and a.planilla_pago_comparativo_id_sie is null
                        and a.cargo_tipo_id=0
                union
                select a.id,d.carnet||case when trim(coalesce(d.complemento,''))='' then '' else '-'||d.complemento end as ci
                ,trim(trim(coalesce(d.paterno,''))||' '||trim(coalesce(d.materno,'')))||' '||trim(coalesce(d.nombre,'')) as apellidos_nombre,
                (select financiamiento from financiamiento_tipo where id=b.financiamiento_tipo_id) as financiamiento_sie,(select cargo from cargo_tipo where id=b.cargo_tipo_id) as cargo_sie
                ,null::character varying(10) as servicio,null::character varying(7) as item,null::character varying(50) as func_doc,
                a.solucion_comparacion_planilla_tipo_id as solucion_comparacion_planilla_tipo_id
                ,a.observacion
                from empareja_sie_planilla  a 
                inner join maestro_inscripcion b on a.maestro_inscripcion_id_sie=b.id
                inner join institucioneducativa c on b.institucioneducativa_id=c.id
                inner join persona d on b.persona_id=d.id
                where a.institucioneducativa_id=".$institucion."
                and a.gestion_tipo_id=".$gestion." 
                and a.mes_tipo_id= ".$mes."
                and c.estadoinstitucion_tipo_id=10 and c.dependencia_tipo_id=3 and c.orgcurricular_tipo_id=1 
                and a.cargo_tipo_id=0
                        ) a
                        order by cargo_sie,apellidos_nombre;");
                
            $query->execute();
            $maestro = $query->fetchAll();	            
        } else {
            $maestro = '';
        }   
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        $eliminacionTipo = $em->getRepository('SieAppWebBundle:EliminaRegistroPlanillaTipo')->findAll();
        $mestipo = $em->getRepository('SieAppWebBundle:MesTipo')->findOneById($mes);
        $meses = $em->getRepository('SieAppWebBundle:MesTipo')->findBy(['id' => range(1, $maxMes)]);
              
        return $this->render($this->session->get('pathSystem') . ':InfoMaestroPlanilla:index.html.twig', array(
                'maestro' => $maestro,
                'institucion' => $institucion,
                'gestion' => $gestion,
                'mesid' => $mestipo->getId(),
                'mes' => $mestipo->getMes(),
                'meses' => $meses,
                'eliminaTipo'=>$eliminacionTipo,
                'sw'=>$swop,
        ));
        
    }

    public function planillaMesAction(Request $request){
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $mes = $request->request->get('mes');
        $request->getSession()->set('idPlanillaMes',$mes);
        $em = $this->getDoctrine()->getManager();
        
        $institucion = $this->session->get('ie_id');
        $gestion = $request->getSession()->get('currentyear');
        
        $swop = $this->vercierreop($gestion,$institucion,$mes);
        
        if ($swop === 0){
            $query = $em->getConnection()->prepare("  
                        select a.id, a.ci, a.apellidos_nombre, a.financiamiento_sie, a.cargo_sie, coalesce(a.servicio,'') servicio, coalesce(a.item,'') item, coalesce (a.func_doc,'') func_doc, a.solucion_comparacion_planilla_tipo_id, coalesce(a.observacion,'') observacion
                        from (
                        select a.id,b.ci_pla||case when trim(coalesce(b.comp_pla,''))='' then '' else '-'||b.comp_pla end as ci
                        ,trim(trim(coalesce(b.paterno,''))||' '||trim(coalesce(b.materno,'')))||' '||trim(trim(coalesce(b.nombre1,''))||' '||trim(coalesce(b.nombre2,''))) as apellidos_nombre,
                        (select financiamiento from financiamiento_tipo where id=a.financiamiento_tipo_id) as financiamiento_sie,(select cargo from cargo_tipo where id=a.cargo_tipo_id) as cargo_sie
                        ,b.servicio,b.item,(select descripcion from planillas_funcioncargo_tipo where id=b.cod_func) as func_doc,a.solucion_comparacion_planilla_tipo_id,a.observacion
                        from public.empareja_sie_planilla a 
                            inner join planilla_pago_comparativo b on a.planilla_pago_comparativo_id_sie=b.id
                        where institucioneducativa_id=".$institucion."
                        and gestion_tipo_id=".$gestion." 
                        and mes_tipo_id= ".$mes."
                        and a.maestro_inscripcion_id_sie is not null and a.planilla_pago_comparativo_id_sie is not null and nuevo_maestro_inscripcion_id is null
                        and a.cargo_tipo_id=0
                        union 
                        select a.id,b.ci_pla||case when trim(coalesce(b.comp_pla,''))='' then '' else '-'||b.comp_pla end as ci
                        ,trim(trim(coalesce(b.paterno,''))||' '||trim(coalesce(b.materno,'')))||' '||trim(trim(coalesce(b.nombre1,''))||' '||trim(coalesce(b.nombre2,''))) as apellidos_nombre,
                        (select financiamiento from financiamiento_tipo where id=a.financiamiento_tipo_id) as financiamiento_sie,(select cargo from cargo_tipo where id=a.cargo_tipo_id) as cargo_sie
                        ,b.servicio,b.item,(select descripcion from planillas_funcioncargo_tipo where id=b.cod_func) as func_doc,a.solucion_comparacion_planilla_tipo_id,a.observacion
                        from public.empareja_sie_planilla a 
                            inner join planilla_pago_comparativo b on a.planilla_pago_comparativo_id_sie=b.id
                        where institucioneducativa_id=".$institucion." 
                        and gestion_tipo_id=".$gestion." 
                        and mes_tipo_id= ".$mes."
                        and nuevo_maestro_inscripcion_id is null and a.maestro_inscripcion_id_sie is null and a.planilla_pago_comparativo_id_sie is not null 
                        and b.cod_func=2
                        union 
                        select a.id,b.ci||case when trim(coalesce(b.complemento,''))='' then '' else '-'||b.complemento end as ci
                        ,trim(trim(coalesce(b.paterno,''))||' '||trim(coalesce(b.materno,'')))||' '||trim(coalesce(b.nombre,'')) as apellidos_nombre,
                        (select financiamiento from financiamiento_tipo where id=a.financiamiento_tipo_id) as financiamiento_sie,(select cargo from cargo_tipo where id=a.cargo_tipo_id) as cargo_sie
                        ,null::character varying(10) as servicio,null::character varying(7) as item,null::character varying(50) as func_doc,a.solucion_comparacion_planilla_tipo_id,a.observacion
                        from empareja_sie_planilla  a
                            inner join nuevo_maestro_inscripcion b on a.nuevo_maestro_inscripcion_id=b.id
                        where a.institucioneducativa_id=".$institucion." 
                        and a.gestion_tipo_id=".$gestion." 
                        and a.mes_tipo_id= ".$mes." 
                        and nuevo_maestro_inscripcion_id is not null and a.maestro_inscripcion_id_sie is null and a.planilla_pago_comparativo_id_sie is null
                        and a.cargo_tipo_id=0
                union
                select a.id,d.carnet||case when trim(coalesce(d.complemento,''))='' then '' else '-'||d.complemento end as ci
                ,trim(trim(coalesce(d.paterno,''))||' '||trim(coalesce(d.materno,'')))||' '||trim(coalesce(d.nombre,'')) as apellidos_nombre,
                (select financiamiento from financiamiento_tipo where id=b.financiamiento_tipo_id) as financiamiento_sie,(select cargo from cargo_tipo where id=b.cargo_tipo_id) as cargo_sie
                ,null::character varying(10) as servicio,null::character varying(7) as item,null::character varying(50) as func_doc,
                a.solucion_comparacion_planilla_tipo_id as solucion_comparacion_planilla_tipo_id
                ,a.observacion
                from empareja_sie_planilla  a 
                inner join maestro_inscripcion b on a.maestro_inscripcion_id_sie=b.id
                inner join institucioneducativa c on b.institucioneducativa_id=c.id
                inner join persona d on b.persona_id=d.id
                where a.institucioneducativa_id=".$institucion."
                and a.gestion_tipo_id=".$gestion." 
                and a.mes_tipo_id= ".$mes."
                and c.estadoinstitucion_tipo_id=10 and c.dependencia_tipo_id=3 and c.orgcurricular_tipo_id=1 
                and a.cargo_tipo_id=0
                        ) a
                        order by cargo_sie,apellidos_nombre;");
                
            $query->execute();
            $maestro = $query->fetchAll();	
        } else {
            $maestro = '';
        }  
        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        $mestipo = $em->getRepository('SieAppWebBundle:MesTipo')->findOneById($mes);
        
        return  new JsonResponse(array(
                'maestro' => $maestro,
                'mesid' => $mestipo->getId(),
                'mes' => $mestipo->getMes(),
                'sw' => $swop,
        ));
    }

    public function vercierreop ($gestion, $institucioneducativaId, $mes){
        $em = $this->getDoctrine()->getManager();
        $mescierre = $em->getRepository('SieAppWebBundle:RegistroConsolidacionOperativoPlanilla')->findBy([
            'gestion' => $gestion,
            'institucioneducativaId' => 99999999,
            'mes' => $mes,
        ]);
        
        $mescierreop = $mescierre[0]->getFechaCierreOperativo(); 
                
        $mesop = $em->getRepository('SieAppWebBundle:RegistroConsolidacionOperativoPlanilla')->findBy(['gestion' => $gestion,
        'institucioneducativaId' => $institucioneducativaId,
        'mes' => $mes,
        ]);
        
        $swop = 0;
        if (!empty($mesop)) {
            $swop = 1; //cerro operativo
        }
        if ($mescierreop !== null && $swop === 0) {
            $mescierreopFormatted = $mescierreop->format('Y-m-d H:i:s'); 
            $fechaActual = new \DateTime();
            if ($fechaActual > $mescierreop) {
                $swop = 2; //finalizo operativo
            }
        }

        return $swop;
    }

    public function confirmaAction (Request $request, $id){
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $finciamiento = $request->request->get('financiamiento');
        
        // Obtener la entidad EmparejaSiePlanilla por su id
        
        $emparejaSiePlanilla = $em->getRepository(EmparejaSiePlanilla::class)->find($id);
        $institucion = $emparejaSiePlanilla->getInstitucioneducativa()->getId();
        $gestion = $emparejaSiePlanilla->getGestionTipoId();
        $mes = $request->getSession()->get('idPlanillaMes');
        // dump($institucion);
        // dump($gestion);
        // dump($emparejaSiePlanilla);die;
        // Verificar si se encontró la entidad
        if (!$emparejaSiePlanilla) {
            return new JsonResponse(['error' => 'La entidad no existe'], 404);
        }
        $justificacion = '';
        $nuevoValorSolucion =$em->getRepository(SolucionComparacionPlanillaTipo::class)->findOneById(1);
        
        // if ($emparejaSiePlanilla->getplanillaPagoComparativoSie()===null){
        //     $justificacion = $emparejaSiePlanilla->getobservacion();
        //     $nuevoValorSolucion =$emparejaSiePlanilla->getsolucionComparacionPlanillaTipo();
        // } 
        if ($emparejaSiePlanilla->getnuevoMaestroInscripcion()!==null){
            $justificacion = $emparejaSiePlanilla->getnuevoMaestroInscripcion()->getobservacion();
            $nuevoValorSolucion =$em->getRepository(SolucionComparacionPlanillaTipo::class)->findOneById(3);
        } 
        $emparejaSiePlanilla->setSolucionComparacionPlanillaTipo($nuevoValorSolucion);
        $emparejaSiePlanilla->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById($finciamiento));
        $emparejaSiePlanilla->setObservacion($justificacion);
        $fechaActual = new \DateTime();
        $emparejaSiePlanilla->setFechaModificacion($fechaActual);
        // Persistir los cambios en la base de datos
        $em->persist($emparejaSiePlanilla);
        $em->flush();
        
        $financiamientoTipo = $em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById($finciamiento);
        $financiamientotxt = $financiamientoTipo->getFinanciamiento();
        $mescierre = $em->getRepository('SieAppWebBundle:RegistroConsolidacionOperativoPlanilla')->findBy(['gestion' => $gestion,
        'institucioneducativaId' => 99999999,
        'mes' => $mestipo->getId(),
        ]);
        $mesop = $em->getRepository('SieAppWebBundle:RegistroConsolidacionOperativoPlanilla')->findBy(['gestion' => $gestion,
        'institucioneducativaId' => $institucion,
        'mes' => $mestipo->getId(),
        ]);
        // dump($mescierre );die;
        if (!isset($mesop)){
            if ($mescierre->getFechaCierreOperativo() != null){
                $fechaActual = new \DateTime();
                if ($fechaActual > $mescierre->getFechaCierreOperativo()){ $swop = 2;} else { $swop = 0;}
            } else{
                $swop = 0;
            }
        } else {
            $swop = 1;
        }
        $maestro = [
            'id' => $emparejaSiePlanilla->getId(),
            'solucion_comparacion_planilla_tipo_id' => $emparejaSiePlanilla->getSolucionComparacionPlanillaTipo()->getId(),
            'financimientonew' => $financiamientotxt,
            'observacion' => $emparejaSiePlanilla->getObservacion(),
            'sw' => $swop,
        ];
        return new JsonResponse($maestro);
    }

    public function eliminaAction (Request $request, $id){
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $justificacion = $request->request->get('justificacion');
        $tipoEliminacion = $request->request->get('tipoEliminacion');
        // dump($em->getRepository('SieAppWebBundle:EliminaRegistroPlanillaTipo')->findById($tipoEliminacion));die;
        $emparejaSiePlanilla = $em->getRepository(EmparejaSiePlanilla::class)->find($id);
        
        $institucion = $emparejaSiePlanilla->getInstitucioneducativa()->getId();
        $gestion = $emparejaSiePlanilla->getGestionTipoId();
        $mes = $request->getSession()->get('idPlanillaMes');;
        if (!$emparejaSiePlanilla) {
            return new JsonResponse(['error' => 'La entidad no existe'], 404);
        }
        $nuevoValorSolucion =$em->getRepository(SolucionComparacionPlanillaTipo::class)->findOneById(2);
        $emparejaSiePlanilla->setSolucionComparacionPlanillaTipo($nuevoValorSolucion);
        $emparejaSiePlanilla->setEliminaRegistroPlanillaTipo($em->getRepository(EliminaRegistroPlanillaTipo::class)->findOneById($tipoEliminacion));
        $emparejaSiePlanilla->setObservacion($justificacion);
        $fechaActual = new \DateTime();
        $emparejaSiePlanilla->setFechaModificacion($fechaActual);

        $em->persist($emparejaSiePlanilla);
        $em->flush();
        // dump($emparejaSiePlanilla);die;
        $maestro = [
            'id' => $emparejaSiePlanilla->getId(),
            'solucion_comparacion_planilla_tipo_id' => $emparejaSiePlanilla->getSolucionComparacionPlanillaTipo()->getId(),
            'observacion' => $emparejaSiePlanilla->getEliminaRegistroPlanillaTipo()->getRazonElimina(), //getObservacion(),
        ];
        return new JsonResponse($maestro);
    }

    public function areasMaestroAction (Request $request, $id){
        $turnoId = null;
        $nivelId = null;
        $gradoId = null;
        $paraleloId = null;
        $asignaturaId = null;
        $turnoTipoEntidad = array();
        $nivelTipoEntidad = array();
        $gradoTipoEntidad = array();
        $paraleloTipoEntidad = array();
        $asignaturaTipoEntidad = array();
        $em = $this->getDoctrine()->getManager();
        $emparejaSiePlanilla = $em->getRepository(EmparejaSiePlanilla::class)->findOneById($id);
        $institucionEducativaId = $emparejaSiePlanilla->getInstitucioneducativa()->getId();
        $gestionId = $emparejaSiePlanilla->getGestionTipoId();
        $turnoTipoEntidad = $this->getTurnoInstitucionEducativaCurso($institucionEducativaId,$gestionId);
        $turnoTipoArray = array();
        foreach ($turnoTipoEntidad as $data) {
            $turnoTipoArray[$data['id']] = $data['turno'];
        }
        $asignaturamaestro = $this->getAsiganturaMaestro($id);
        $form = $this->createFormBuilder()
        ->add('turno', 'choice', array('label' => 'Turno', 'empty_value' => 'Sel. Turno', 'choices' => $turnoTipoArray, 'data' => $turnoId, 'attr' => array('class' => 'form-control', 'onchange' => 'cargarNiveles()', 'required' => true)))
        ->add('nivel', 'choice', array('label' => 'Nivel', 'empty_value' => 'Sel. Nivel', 'choices' => $nivelTipoEntidad, 'data' => $nivelId, 'attr' => array('class' => 'form-control', 'required' => true, 'onchange' => 'cargarGrados()')))
        ->add('grado', 'choice', array('label' => 'Grado', 'empty_value' => 'Sel. Grado', 'choices' => $gradoTipoEntidad, 'data' => $gradoId, 'attr' => array('class' => 'form-control', 'required' => true, 'onchange' => 'cargarAsignaturas()')))
        ->add('asignatura', 'choice', array('label' => 'Area', 'empty_value' => 'Sel. Áreas', 'choices' => $asignaturaTipoEntidad, 'data' => $asignaturaId, 'attr' => array('class' => 'form-control', 'required' => true, 'onchange' => 'cargarParalelos()')))
        ->getForm()->createView();
        return $this->render($this->session->get('pathSystem').':InfoMaestroPlanilla:formulario_areas.html.twig',array(
            'form' => $form,
            'areasasig' => $asignaturamaestro
            )
        );
    }
    
    public function getTurnoInstitucionEducativaCurso($institucionEducativaId, $gestionId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select("distinct tt.id, tt.turno")
                ->innerJoin('SieAppWebBundle:TurnoTipo', 'tt', 'WITH', 'tt.id = iec.turnoTipo')
                ->where('iec.gestionTipo = :gestionTipoId')
                ->andWhere('iec.institucioneducativa = :institucioneducativaId')
                ->andWhere('iec.nivelTipo in (11,12,13)')
                ->setParameter('gestionTipoId', $gestionId)
                ->setParameter('institucioneducativaId', $institucionEducativaId)
                ->orderBy('tt.id', 'ASC');
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    public function getNivelInstitucionEducativaCurso($institucionEducativaId, $gestionId, $turnoId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select("distinct nt.id, nt.nivel")
                ->innerJoin('SieAppWebBundle:NivelTipo', 'nt', 'WITH', 'nt.id = iec.nivelTipo')
                ->where('iec.gestionTipo = :gestionTipoId')
                ->andWhere('iec.institucioneducativa = :institucioneducativaId')
                ->andWhere('iec.turnoTipo = :turnoId')
                ->andWhere('nt.id in (11,12,13)')
                ->setParameter('gestionTipoId', $gestionId)
                ->setParameter('institucioneducativaId', $institucionEducativaId)
                ->setParameter('turnoId', $turnoId)
                ->orderBy('nt.id', 'ASC');
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    public function getGradoInstitucionEducativaCurso($institucionEducativaId, $gestionId, $turnoId, $nivelId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select("distinct gt.id, gt.grado")
                ->innerJoin('SieAppWebBundle:GradoTipo', 'gt', 'WITH', 'gt.id = iec.gradoTipo')
                ->where('iec.gestionTipo = :gestionTipoId')
                ->andWhere('iec.institucioneducativa = :institucioneducativaId')
                ->andWhere('iec.turnoTipo = :turnoId')
                ->andWhere('iec.nivelTipo = :nivelId')
                ->setParameter('gestionTipoId', $gestionId)
                ->setParameter('institucioneducativaId', $institucionEducativaId)
                ->setParameter('turnoId', $turnoId)
                ->setParameter('nivelId', $nivelId)
                ->orderBy('gt.id', 'ASC');
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    public function getAsignaturaInstitucionEducativaCurso($institucionEducativaId, $gestionId, $turnoId, $nivelId, $gradoId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select("distinct at.id, at.asignatura")
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco', 'WITH', 'ieco.insitucioneducativaCurso = iec.id')
                ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'at.id = ieco.asignaturaTipo')
                ->where('iec.gestionTipo = :gestionTipoId')
                ->andWhere('iec.institucioneducativa = :institucioneducativaId')
                ->andWhere('iec.turnoTipo = :turnoId')
                ->andWhere('iec.nivelTipo = :nivelId')
                ->andWhere('iec.gradoTipo = :gradoId')
                ->setParameter('gestionTipoId', $gestionId)
                ->setParameter('institucioneducativaId', $institucionEducativaId)
                ->setParameter('turnoId', $turnoId)
                ->setParameter('nivelId', $nivelId)
                ->setParameter('gradoId', $gradoId)
                ->orderBy('at.id', 'ASC');
        
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    public function getParaleloInstitucionEducativaCurso($institucionEducativaId, $gestionId, $turnoId, $nivelId, $gradoId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select("distinct pt.id, pt.paralelo")
                ->innerJoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'pt.id = iec.paraleloTipo')
                ->where('iec.gestionTipo = :gestionTipoId')
                ->andWhere('iec.institucioneducativa = :institucioneducativaId')
                ->andWhere('iec.turnoTipo = :turnoId')
                ->andWhere('iec.nivelTipo = :nivelId')
                ->andWhere('iec.gradoTipo = :gradoId')
                ->setParameter('gestionTipoId', $gestionId)
                ->setParameter('institucioneducativaId', $institucionEducativaId)
                ->setParameter('turnoId', $turnoId)
                ->setParameter('nivelId', $nivelId)
                ->setParameter('gradoId', $gradoId)
                ->orderBy('pt.id', 'ASC');
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    public function getAsiganturaMaestro($emparejaSiePlanillaId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:EmparejaPlanillaAsignaturaTipo');
        $query = $entity->createQueryBuilder('epat')
            ->select("epat.id,tt.turno,CASE WHEN epat.nivelTipo = 11 THEN 'INICIAL' WHEN epat.nivelTipo = 12 THEN 'PRIMARIA'  WHEN epat.nivelTipo = 13 THEN 'SECUNDARIA' ELSE '' END AS nivel, gt.id AS grado, pt.paralelo, ati.asignatura,ft.financiamiento,epat.horas")
            ->innerJoin('SieAppWebBundle:TurnoTipo', 'tt', 'WITH', 'tt.id = epat.turnoTipo')
            ->innerJoin('SieAppWebBundle:GradoTipo', 'gt', 'WITH', 'gt.id = epat.gradoTipo')
            ->innerJoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'pt.id = epat.paraleloTipo')
            ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'ati', 'WITH', 'ati.id = epat.asignaturaTipo')
            ->innerJoin('SieAppWebBundle:FinanciamientoTipo', 'ft', 'WITH', 'ft.id = epat.financiamientoTipo')
            ->where('epat.emparejaSiePlanilla = :emparejaSiePlanillaId')
            ->setParameter('emparejaSiePlanillaId', $emparejaSiePlanillaId)
            ->orderBy('tt.turno,epat.nivelTipo,gt.id,ati.asignatura,pt.paralelo', 'ASC'); 
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    public function validaAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('complemento');
        
        $cedula = ltrim(rtrim($request->request->get('ci')));
        $complemento = strtoupper(ltrim(rtrim($request->get('complemento'))));
        $complemento = strtoupper(ltrim(rtrim($complemento !== null ? $complemento : '')));
        $paterno = strtoupper(ltrim(rtrim($request->get('apellidoPaterno'))));
        $materno = strtoupper(ltrim(rtrim($request->get('apellidoMaterno'))));
        $nombre = strtoupper(ltrim(rtrim($request->get('nombres'))));
        $fechaNacimiento = new \DateTime($request->get('fechaNacimiento'));

            $datos = array(
                'complemento'=>$complemento,
                'primer_apellido'=>$paterno,
                'segundo_apellido'=>$materno,
                'nombre'=>$nombre,
                'fecha_nacimiento' => $fechaNacimiento->format('d-m-Y')
            );
            
            $cedulaTipoId = $request->get('tipoCarnet');
            
            
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
                'carnet'=>$cedula,
                'complemento'=>$complemento,
                'paterno'=>$paterno,
                'materno'=>$materno,
                'nombre'=>$nombre,
                'fechaNacimiento'=>$fechaNacimiento,
                'segipId'=> 1,
            ));
            if (!is_object($persona)) {
            
                if($cedulaTipoId == 2){
                    $datos["extranjero"] = 'E';
                }

                if($cedula){
                    $resultadoPersona = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($cedula,$datos,'prod','academico');
                    $mensaje = '';
                    if(!$resultadoPersona){
                        $mensaje = "No se realizó la validación con SEGIP.";
                    }                
                } else {
                    $mensaje = "No se realizó la validación con SEGIP. Actualice el C.I. de la persona.";
                }
            } else {
                $resultadoPersona = true;
                $mensaje = '';
            }

            $financiamiento = $em->createQuery(
                'SELECT ft FROM SieAppWebBundle:FinanciamientoTipo ft
                    WHERE ft.id NOT IN  (:id)')
                    ->setParameter('id', array(0, 5, 12, 11))
                    ->getResult();
                    $financiamientoArray = array();
            foreach ($financiamiento as $f) {
                $financiamientoArray[$f->getId()] = $f->getFinanciamiento();
            }
            return new JsonResponse(array(
                'financiamiento' => $financiamientoArray,
                'mensaje' => $mensaje,
            ));
    }


    public function guardaMaestroAction(Request $request){
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $institucion = $this->session->get('ie_id');
        $gestion = $request->getSession()->get('currentyear');
        $planillaMes = $request->getSession()->get('idPlanillaMes');
        
        $ci = ltrim(rtrim($request->request->get('ci')));
        $complemento = strtoupper(ltrim(rtrim($request->request->get('complemento'))));
        $complemento = strtoupper(ltrim(rtrim($complemento !== null ? $complemento : '')));
        $paterno = strtoupper(ltrim(rtrim($request->request->get('apellidoPaterno'))));
        $materno = strtoupper(ltrim(rtrim($request->request->get('apellidoMaterno'))));
        $nombre = strtoupper(ltrim(rtrim($request->request->get('nombres'))));
        $fechaNacimiento = new \DateTime($request->request->get('fechaNacimiento'));
        $financiamientoTipoId = (int) $request->request->get('financiamiento');
        $observacion = ltrim(rtrim($request->request->get('observacion')));

        $em = $this->getDoctrine()->getManager();
        $nuevoMaestro = new NuevoMaestroInscripcion();
        $nuevoMaestro->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion));
        $nuevoMaestro->setMesTipo($em->getRepository('SieAppWebBundle:MesTipo')->findOneById($planillaMes));
        $nuevoMaestro->setCi($ci);
        $nuevoMaestro->setComplemento($complemento);
        $nuevoMaestro->setPaterno($paterno);
        $nuevoMaestro->setMaterno($materno);
        $nuevoMaestro->setNombre($nombre);
        $nuevoMaestro->setFechaNacimiento($fechaNacimiento);
        $nuevoMaestro->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById($financiamientoTipoId));
        $nuevoMaestro->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->findOneById(0));
        $nuevoMaestro->setObservacion($observacion);
        $nuevoMaestro->setInstitucioneducativaId($institucion);
        $fechaActual = new \DateTime();
        $nuevoMaestro->setFechaCreacion($fechaActual);
        $em->persist($nuevoMaestro);
        $em->flush();
        $emparejaSiePlanilla = new EmparejaSiePlanilla();
        $emparejaSiePlanilla->setGestionTipoId($gestion);
        $emparejaSiePlanilla->setMesTipoId($planillaMes);
        $emparejaSiePlanilla->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion));
        $emparejaSiePlanilla->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById($financiamientoTipoId));
        $emparejaSiePlanilla->setCargoTipoId(0);
        $emparejaSiePlanilla->setNuevoMaestroInscripcion($nuevoMaestro);
        $emparejaSiePlanilla->setObservacion($observacion);
        $emparejaSiePlanilla->setSolucionComparacionPlanillaTipo($em->getRepository('SieAppWebBundle:SolucionComparacionPlanillaTipo')->findOneById(3));

        // Establecer la fecha de creación con la fecha y hora actual
        $fechaActual = new \DateTime();
        $emparejaSiePlanilla->setFechaCreacion($fechaActual);

        // Persistir y guardar la entidad EmparejaSiePlanilla
        $em->persist($emparejaSiePlanilla);
        $em->flush();

        $emparejaSiePlanillaid = $emparejaSiePlanilla->getId();
        // dump($emparejaSiePlanillaid); die;
        $query = $em->getConnection()->prepare("
                    select a.id,b.ci||case when trim(coalesce(b.complemento,''))='' then '' else '-'||b.complemento end as ci
                    ,trim(trim(coalesce(b.paterno,''))||' '||trim(coalesce(b.materno,'')))||' '||trim(coalesce(b.nombre,'')) as apellidos_nombre,
                    (select financiamiento from financiamiento_tipo where id=a.financiamiento_tipo_id) as financiamiento_sie,(select cargo from cargo_tipo where id=a.cargo_tipo_id) as cargo_sie
                    ,null::character varying(10) as servicio,null::character varying(7) as item,null::character varying(50) as func_doc,a.solucion_comparacion_planilla_tipo_id,a.observacion
                    from empareja_sie_planilla  a
                        inner join nuevo_maestro_inscripcion b on a.nuevo_maestro_inscripcion_id=b.id
                    where a.institucioneducativa_id=".$institucion." 
                    and a.gestion_tipo_id=".$gestion." 
                    and a.mes_tipo_id=".$planillaMes." 
                    and a.id = ".$emparejaSiePlanillaid." 
                    and nuevo_maestro_inscripcion_id is not null and a.maestro_inscripcion_id_sie is null and a.planilla_pago_comparativo_id_sie is null
                    and a.cargo_tipo_id=0");
            
                    $query->execute();
                    $maestronew = $query->fetchAll();

        return new JsonResponse($maestronew);
    }
   
    public function validaConsolidacionAction(Request $request){
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $sie = $request->request->get('sie');
        $gestion = $request->request->get('gestion');
        $mesplanilla = $request->request->get('mesid');
        $em = $this->getDoctrine()->getManager();
        $query = $query = $em->getConnection()->prepare("select * from public.sp_validacion_cierre_operativo_comparacion_sie_planilla('".$gestion."','".$mesplanilla."','".$sie."');");
        $query->execute();
        $arrayObs = $query->fetchAll();
        $mensaje = 1;
        if(sizeof($arrayObs)>0){
            $mensaje = 0;
        }else{ 
          $arrayObs = array();
        }
        return new JsonResponse(array(
            'observacion' => $arrayObs,
            'estado' => $mensaje,
        ));
    }

    public function financiamientoAction($id){
        $em = $this->getDoctrine()->getManager();
        $financiamiento = $em->createQuery(
            'SELECT ft FROM SieAppWebBundle:FinanciamientoTipo ft
                WHERE ft.id NOT IN  (:id)')
                ->setParameter('id', array(0, 5, 12))
                ->getResult();
                $financiamientoArray = array();
        foreach ($financiamiento as $f) {
            $financiamientoArray[$f->getId()] = $f->getFinanciamiento();
        }
        $financiamientomae = $em->getRepository(EmparejaSiePlanilla::class)->findOneById($id)->getFinanciamientoTipo();
        $financiamientoant = array(
            'id' => $financiamientomae->getId(),
            'financiamiento' => $financiamientomae->getFinanciamiento(),
        );
        // dump($financiamientomae);
        // dump($financiamientoant);
        // die;
        return new JsonResponse(array(
            'financiamiento' => $financiamientoArray,
            'financiamientoant' => $financiamientoant,
        ));
    }

    public function listaNivelAction(Request $request) {
        $turnoId = $request->get('turno'); 
        $institucion = $this->session->get('ie_id');
        $gestion = $request->getSession()->get('currentyear');   
                        
        $response = new JsonResponse();
        return $response->setData(array(
            'niveles' => $this->getNivelInstitucionEducativaCurso($institucion, $gestion, $turnoId),
        ));
    }

    public function listaGradosAction(Request $request) {
        $turnoId = $request->get('turno');       
        $nivelId = $request->get('nivel');        
        $institucion = $this->session->get('ie_id');
        $gestion = $request->getSession()->get('currentyear');  
                
        $response = new JsonResponse();
        return $response->setData(array(
                'grados' => $this->getGradoInstitucionEducativaCurso($institucion, $gestion, $turnoId, $nivelId),
            ));
    }

    public function listaAsignaturasAction(Request $request) {
        $turnoId = $request->get('turno');       
        $nivelId = $request->get('nivel'); 
        $gradoId = $request->get('grado'); 
        $institucion = $this->session->get('ie_id');
        $gestion = $request->getSession()->get('currentyear'); 
                
        $response = new JsonResponse();
        return $response->setData(array(
                'asignatura' => $this->getAsignaturaInstitucionEducativaCurso($institucion, $gestion, $turnoId, $nivelId, $gradoId),
            ));
    }

    public function listaParalelosAction(Request $request) {
        $turnoId = $request->get('turno');       
        $nivelId = $request->get('nivel'); 
        $gradoId = $request->get('grado'); 
        $institucion = $this->session->get('ie_id');
        $gestion = $request->getSession()->get('currentyear'); 
        $em = $this->getDoctrine()->getManager();
        $financiamiento = $em->createQuery(
            'SELECT ft FROM SieAppWebBundle:FinanciamientoTipo ft
                WHERE ft.id NOT IN  (:id)')
                ->setParameter('id', array(0, 5, 12, 11))
                ->getResult();
                $financiamientoArray = array();
        foreach ($financiamiento as $f) {
            $financiamientoArray[$f->getId()] = $f->getFinanciamiento();
        }
        
        $response = new JsonResponse();
        return $response->setData(array(
                'paralelos' => $this->getParaleloInstitucionEducativaCurso($institucion, $gestion, $turnoId, $nivelId, $gradoId),
                'financiamiento_par' => $financiamientoArray,
            ));
    }

    public function guardaParaleloAction(Request $request){
        
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $espId = $request->get('esp');       
        $turnoId = $request->get('turno');
        $nivelId = $request->get('nivel'); 
        $gradoId = $request->get('grado');
        $asignaturaId = $request->get('asignatura');
        $paralelos = $request->get('paralelos');
        $institucion = $this->session->get('ie_id');
        $gestion = $request->getSession()->get('currentyear'); 
        $em = $this->getDoctrine()->getManager();
        
        foreach ($paralelos as $paralelo) {
            $paraleloId = $paralelo['id'];
            $paralelofin = $paralelo['financiamiento'];
            $paralelohoras = $paralelo['horas'];
            $existeDato = $em->getRepository('SieAppWebBundle:EmparejaPlanillaAsignaturaTipo')->findOneBy(array(
                'emparejaSiePlanilla' => $espId,
                'turnoTipo' => $turnoId,
                'paraleloTipo' => $paraleloId,
                'nivelTipo' => $nivelId,
                'gradoTipo' => $gradoId,
                'asignaturaTipo' => $asignaturaId,
                'financiamientoTipo' => $paralelofin,
            ));
            if (!$existeDato) {
                $nuevoAsignatura = new EmparejaPlanillaAsignaturaTipo();
                $nuevoAsignatura->setEmparejaSiePlanilla($em->getRepository('SieAppWebBundle:EmparejaSiePlanilla')->findOneById($espId));
                $nuevoAsignatura->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->findOneById($turnoId));
                $nuevoAsignatura->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->findOneById($paraleloId));
                $nuevoAsignatura->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->findOneById($gradoId));
                $nuevoAsignatura->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($nivelId));
                $nuevoAsignatura->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->findOneById($asignaturaId));
                $nuevoAsignatura->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById($paralelofin));
                $nuevoAsignatura->setHoras($paralelohoras);
                $fechaActual = new \DateTime();
                $nuevoAsignatura->setFechaCreacion($fechaActual); 
                $em->persist($nuevoAsignatura);
                $em->flush();
            }  
        }
        
        // $em->persist($nuevoAsignatura);
        // $em->flush();
        $asignaturamaestro = $this->getAsiganturaMaestro($espId);
        $response = new JsonResponse();
        return $response->setData(array(
            'asignaturamaestro' => $asignaturamaestro
        ));
    }

    public function eliminaParaleloAction(Request $request){
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $areaid = $request->get('areaid');
        $espId = $request->get('esp');       
        
        $em = $this->getDoctrine()->getManager();
        $area = $em->getRepository('SieAppWebBundle:EmparejaPlanillaAsignaturaTipo')->findOneBy(array('id' => $areaid, 'emparejaSiePlanilla' => $espId,));
        // Verificar si el registro existe
        if (!$area) {
            return new JsonResponse(['success' => false, 'message' => 'El registro no existe.']);
        }

        try {
            // Eliminar el registro
            $em->remove($area);
            $em->flush();
            return new JsonResponse(['success' => true, 'message' => 'El registro se ha eliminado correctamente.']);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Ha ocurrido un error al eliminar el registro.']);
        }   
        
    }
}
