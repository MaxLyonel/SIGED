<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Entity\EmparejaSiePlanilla;
use Sie\AppWebBundle\Entity\SolucionComparacionPlanillaTipo;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoValidacionpersonal;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\NuevoMaestroInscripcion;

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
        
        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        
        $institucion = $this->session->get('ie_id');
        $gestion = $request->getSession()->get('currentyear');
        $mes = 6;
        /*
        * verificamos si tiene tuicion
        */
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
        $query->bindValue(':sie', $this->session->get('ie_id'));
        $query->bindValue(':rolId', $this->session->get('roluser'));
        $query->execute();
        $aTuicion = $query->fetchAll();
        
        if ($aTuicion[0]['get_ue_tuicion']) {
            // creamos variables de sesion de la institucion educativa y gestion
            $request->getSession()->set('idInstitucion', $institucion);
            $request->getSession()->set('idGestion', $gestion);
            $request->getSession()->set('idPlanillaMes',$mes);
        } else {
            //$this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
            //return $this->redirect($this->generateUrl('herramienta_info_maestro_migrar_index'));
            $request->getSession()->set('idInstitucion', $institucion);
            $request->getSession()->set('idGestion', $gestion);
            $request->getSession()->set('idPlanillaMes', $mes);
        }
        
        //$operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($institucion, $request->getSession()->get('currentyear'));
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        $gestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($request->getSession()->get('currentyear'));
        
        $query = $em->getConnection()->prepare("            
                    select * 
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
                    where a.institucioneducativa_id=".$institucion." and a.gestion_tipo_id=".$gestion." and a.mes_tipo_id=".$mes." 
                    and nuevo_maestro_inscripcion_id is not null and a.maestro_inscripcion_id_sie is null and a.planilla_pago_comparativo_id_sie is null
                    and a.cargo_tipo_id=0
                    ) a
                    order by cargo_sie,apellidos_nombre;");
            
        $query->execute();
        $maestro = $query->fetchAll();	
        //  dump($maestro);die;
        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        $mestipo = $em->getRepository('SieAppWebBundle:MesTipo')->findOneById($mes);
        return $this->render($this->session->get('pathSystem') . ':InfoMaestroPlanilla:index.html.twig', array(
                'maestro' => $maestro,
                'institucion' => $institucion,
                'gestion' => $gestion,
                'mesid' => $mestipo->getId(),
                'mes' => $mestipo->getMes(),
        ));
        
    }


    public function confirmaAction (Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $finciamiento = $request->request->get('financiamiento');
        
        // Obtener la entidad EmparejaSiePlanilla por su id
        
        $emparejaSiePlanilla = $em->getRepository(EmparejaSiePlanilla::class)->find($id);
        
        $institucion = $emparejaSiePlanilla->getInstitucioneducativa()->getId();
        $gestion = $emparejaSiePlanilla->getGestionTipoId();
        $mes = $request->getSession()->get('idPlanillaMes');;
        // dump($institucion);
        // dump($gestion);
        // dump($emparejaSiePlanilla);die;
        // Verificar si se encontró la entidad
        if (!$emparejaSiePlanilla) {
            return new JsonResponse(['error' => 'La entidad no existe'], 404);
        }

        // Obtener el nuevo valor para el campo solucionComparacionPlanillaTipo desde la solicitud AJAX
        $justificacion = '';
        $nuevoValorSolucion =$em->getRepository(SolucionComparacionPlanillaTipo::class)->findOneById(1);
        $emparejaSiePlanilla->setSolucionComparacionPlanillaTipo($nuevoValorSolucion);
        $emparejaSiePlanilla->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById($finciamiento));
        $emparejaSiePlanilla->setObservacion($justificacion);
        // Persistir los cambios en la base de datos
        $em->flush();
        
        $financiamientoTipo = $em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById($finciamiento);
        $financiamientotxt = $financiamientoTipo->getFinanciamiento();

        $maestro = [
            'id' => $emparejaSiePlanilla->getId(),
            'solucion_comparacion_planilla_tipo_id' => $emparejaSiePlanilla->getSolucionComparacionPlanillaTipo()->getId(),
            'financimientonew' => $financiamientotxt,
            'observacion' => $emparejaSiePlanilla->getObservacion(),
        ];
        return new JsonResponse($maestro);
    }

    public function eliminaAction (Request $request, $id){
        // dump('ok');die;
        $em = $this->getDoctrine()->getManager();
        $justificacion = $request->request->get('justificacion');
        
        // Obtener la entidad EmparejaSiePlanilla por su id
        $emparejaSiePlanilla = $em->getRepository(EmparejaSiePlanilla::class)->find($id);
        
        $institucion = $emparejaSiePlanilla->getInstitucioneducativa()->getId();
        $gestion = $emparejaSiePlanilla->getGestionTipoId();
        $mes = $request->getSession()->get('idPlanillaMes');;
        // Verificar si se encontró la entidad
        if (!$emparejaSiePlanilla) {
            return new JsonResponse(['error' => 'La entidad no existe'], 404);
        }
        // Obtener el nuevo valor para el campo solucionComparacionPlanillaTipo desde la solicitud AJAX
        $nuevoValorSolucion =$em->getRepository(SolucionComparacionPlanillaTipo::class)->findOneById(2);
        // Actualizar el campo solucionComparacionPlanillaTipo en la entidad
        $emparejaSiePlanilla->setSolucionComparacionPlanillaTipo($nuevoValorSolucion);
        $emparejaSiePlanilla->setObservacion($justificacion);


        // Persistir los cambios en la base de datos
        $em->flush();

        $maestro = [
            'id' => $emparejaSiePlanilla->getId(),
            'solucion_comparacion_planilla_tipo_id' => $emparejaSiePlanilla->getSolucionComparacionPlanillaTipo()->getId(),
            'observacion' => $emparejaSiePlanilla->getObservacion(),
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
        ->add('turno', 'choice', array('label' => 'Turno', 'empty_value' => 'Turno', 'choices' => $turnoTipoArray, 'data' => $turnoId, 'attr' => array('class' => 'form-control', 'onchange' => 'listar_nivel()', 'required' => true)))
        ->add('nivel', 'choice', array('label' => 'Nivel', 'empty_value' => 'Nivel', 'choices' => $nivelTipoEntidad, 'data' => $nivelId, 'attr' => array('class' => 'form-control', 'required' => true, 'onchange' => 'listar_grado_asignatura()')))
        ->add('grado', 'choice', array('label' => 'Grado', 'empty_value' => 'Grado', 'choices' => $gradoTipoEntidad, 'data' => $gradoId, 'attr' => array('class' => 'form-control', 'required' => true, 'onchange' => 'listar_paralelo()')))
        ->add('asignatura', 'choice', array('label' => 'Area', 'empty_value' => 'Áreas', 'choices' => $asignaturaTipoEntidad, 'data' => $asignaturaId, 'attr' => array('class' => 'form-control', 'required' => true, 'onchange' => 'listar_asignatura()')))
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

    public function getAsiganturaMaestro($emparejaSiePlanillaId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:EmparejaPlanillaAsignaturaTipo');
        $query = $entity->createQueryBuilder('epat')
            ->select("epat.id,tt.turno,CASE WHEN epat.nivelTipo = 11 THEN 'INICIAL' WHEN epat.nivelTipo = 12 THEN 'PRIMARIA'  WHEN epat.nivelTipo = 13 THEN 'SECUNDARIA' ELSE '' END AS nivel, gt.id AS grado, pt.paralelo, ati.asignatura")
            ->innerJoin('SieAppWebBundle:TurnoTipo', 'tt', 'WITH', 'tt.id = epat.turnoTipo')
            ->innerJoin('SieAppWebBundle:GradoTipo', 'gt', 'WITH', 'gt.id = epat.gradoTipo')
            ->innerJoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'pt.id = epat.paraleloTipo')
            ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'ati', 'WITH', 'ati.id = epat.asignaturaTipo')
            ->where('epat.emparejaSiePlanilla = :emparejaSiePlanillaId')
            ->setParameter('emparejaSiePlanillaId', $emparejaSiePlanillaId)
            ->orderBy('epat.id,tt.turno,epat.nivelTipo,gt.id,pt.paralelo,ati.asignatura', 'ASC'); 
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    public function validaAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('complemento');
        // $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['personaId']);
        $fechaNacimiento = new \DateTime($request->get('fechaNacimiento'));
        // if($persona){
            $datos = array(
                'complemento'=>$request->get('complemento'),
                'primer_apellido'=>$request->get('apellidoPaterno'),
                'segundo_apellido'=>$request->get('apellidoMaterno'),
                'nombre'=>$request->get('nombres'),
                'fecha_nacimiento' => $fechaNacimiento->format('d-m-Y')
            );
            
            $cedulaTipoId = $request->get('tipoCarnet');
            $cedula = $request->get('ci');
            if($cedulaTipoId == 2){
                $datos["extranjero"] = 'E';
            }
            if($cedula){
                $resultadoPersona = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($cedula,$datos,'prod','academico');
                $mensaje = '';
                if(!$resultadoPersona){
                    $mensaje = "No se realizó la validación con SEGIP.";
                    // $this->addFlash('updateError', $mensaje);
                }                
            } else {
                $mensaje = "No se realizó la validación con SEGIP. Actualice el C.I. de la persona.";
                // $this->addFlash('updateError', $mensaje);
            }
            $financiamiento = $em->createQuery(
                'SELECT ft FROM SieAppWebBundle:FinanciamientoTipo ft
                    WHERE ft.id NOT IN  (:id)')
                    ->setParameter('id', array(0, 5, 12))
                    ->getResult();
                    $financiamientoArray = array();
            foreach ($financiamiento as $f) {
                $financiamientoArray[$f->getId()] = $f->getFinanciamiento();
            }
            // dump($financiamientoArray); die;
            return new JsonResponse(array(
                'financiamiento' => $financiamientoArray,
                'mensaje' => $mensaje,
            ));
    }


    public function guardaMaestroAction(Request $request){
        // dump('ok guarda');
        // dump($request);
        $institucion = $this->session->get('ie_id');
        $gestion = $request->getSession()->get('currentyear');
        $planillaMes = $request->getSession()->get('idPlanillaMes');
        // dump($institucion);
        // dump($gestion);
        
        $ci = $request->request->get('ci');
        $complemento = $request->request->get('complemento');
        $complemento = $complemento !== null ? $complemento : '';
        $paterno = $request->request->get('apellidoPaterno');
        $materno = $request->request->get('apellidoMaterno');
        $nombre = $request->request->get('nombres');
        $fechaNacimiento = new \DateTime($request->request->get('fechaNacimiento'));
        $financiamientoTipoId = (int) $request->request->get('financiamiento');
        $observacion = $request->request->get('observacion');
        // dump($complemento);
        // die;

        $em = $this->getDoctrine()->getManager();
        // Crear una nueva instancia de la entidad NuevoMaestroInscripcion
        // dump($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucion));die;
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
        // Obtener el Entity Manager para persistir y guardar la entidad
        $em->persist($nuevoMaestro);
        $em->flush();

         // Crear una nueva instancia de la entidad EmparejaSiePlanilla
        $emparejaSiePlanilla = new EmparejaSiePlanilla();
        $emparejaSiePlanilla->setGestionTipoId($gestion);
        $emparejaSiePlanilla->setMesTipoId($planillaMes);
        $emparejaSiePlanilla->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion));
        $emparejaSiePlanilla->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById($financiamientoTipoId));
        $emparejaSiePlanilla->setCargoTipoId(0);
        $emparejaSiePlanilla->setNuevoMaestroInscripcion($nuevoMaestro);
        $emparejaSiePlanilla->setSolucionComparacionPlanillaTipo($em->getRepository('SieAppWebBundle:SolucionComparacionPlanillaTipo')->findOneById(3));
        // Establecer otros campos necesarios en EmparejaSiePlanilla si es necesario

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
}
