<?php

namespace Sie\ProcesosBundle\Controller;

use Sie\AppWebBundle\Entity\WfSolicitudTramite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;
use Sie\ProcesosBundle\Controller\TramiteRueController;



/**
 * Solicitud modificacion y adicion calificaciones controller.
 *
 */
class TramiteModificacionCalificacionesController extends Controller {
    public $session;
    public $idInstitucion;
    public $router;
    /**
     * the class constructor
     */
    public function __construct() {

        $this->session = new Session();
    }

    public function indexAction (Request $request) {
        $id_usuario     = $this->session->get('userId');
        $id_rol     = $this->session->get('roluser');
        $ie_id = $this->session->get('ie_id');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        // VERIFICAMOS SI EL ID QUE SE ENVIA ES DEL FLUJO TIPO O DEL TRAMITE

        if ($request->get('tipo') == 'idtramite') {
            $idTramite = $request->get('id');
            $em = $this->getDoctrine()->getManager();
            $tramiteDetalle = $em->createQueryBuilder()
                                ->select('td.id idTramiteDetalle, te.tramiteEstado, wf.datos, ft.id flujoTipo')
                                ->from('SieAppWebBundle:Tramite','t')
                                ->innerJoin('SieAppWebBundle:TramiteDetalle','td','with','td.tramite = t.id')
                                ->innerJoin('SieAppWebBundle:TramiteEstado','te','with','td.tramiteEstado = te.id')
                                ->innerJoin('SieAppWebBundle:WfSolicitudTramite','wf','with','wf.tramiteDetalle = td.id')
                                ->innerJoin('SieAppWebBundle:FlujoTipo','ft','with','t.flujoTipo = ft.id')
                                ->where('t.id = :idTramite')
                                ->orderBy('td.id','DESC')
                                ->setMaxResults(1)
                                ->setParameter('idTramite', $idTramite)
                                ->getQuery()
                                ->getResult();

            $idTramiteDetalle = $tramiteDetalle[0]['idTramiteDetalle'];
            $tramiteEstado = $tramiteDetalle[0]['tramiteEstado'];
            $dataPrev = json_decode($tramiteDetalle[0]['datos'],true);
            $flujoTipo = $tramiteDetalle[0]['flujoTipo'];
            // dump($datos);
            // dump($tramiteDetalle);
            dump($dataPrev);


            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($dataPrev['idInscripcion']);
            $idInscripcion = $inscripcion->getId();
            $estudiante = $inscripcion->getEstudiante();

            // DATOS DE LAS NOTAS DEL ESTUDIANTE
            $data = $this->get('notas')->regular($idInscripcion, 4);
            $dataPrev['archivo'] = '';
            $dataPrev = json_encode($dataPrev);
            dump($dataPrev);
            // die;

            return $this->render('SieProcesosBundle:TramiteModificacionCalificaciones:formularioEditar.html.twig', array(
                'inscripcion'=>$inscripcion,
                'estudiante'=>$estudiante,
                'data'=>$data,
                'idInscripcion'=>$inscripcion->getId(),
                'flujoTipo'=>$flujoTipo,
                'idTramite'=>$idTramite,
                'dataPrev'=>$dataPrev
            ));
        }

        return $this->render('SieProcesosBundle:TramiteModificacionCalificaciones:index.html.twig', array(
            'flujoTipo'=>$request->get('id')
        ));
    }

    public function buscarEstudianteAction(Request $request){

        $response = new JsonResponse();

        $codigoRude = $request->get('codigoRude');
        $flujoTipo = $request->get('flujoTipo');
        $sie = $this->session->get('ie_id');

        $em = $this->getDoctrine()->getManager();
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$codigoRude));

        // SI EL ESTUDIANTE NO EXISTE, DEVOLVEMOS 204 SIN CONTENIDO
        if(!$estudiante){
            $response->setStatusCode(202);
            $response->setData('El estudiante con el código RUDE '. $codigoRude .' no fue encontrado.');
            return $response;
        }

        $inscripciones = $em->createQueryBuilder()
                            ->select('ei.id, ie.id as sie, ie.institucioneducativa, get.gestion, nt.nivel, gt.grado, pt.paralelo, emt.estadomatricula')
                            ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                            ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','iec.institucioneducativa = ie.id')
                            ->innerJoin('SieAppWebBundle:GestionTipo','get','with','iec.gestionTipo = get.id')
                            ->innerJoin('SieAppWebBundle:NivelTipo','nt','with','iec.nivelTipo = nt.id')
                            ->innerJoin('SieAppWebBundle:GradoTipo','gt','with','iec.gradoTipo = gt.id')
                            ->innerJoin('SieAppWebBundle:ParaleloTipo','pt','with','iec.paraleloTipo = pt.id')
                            ->innerJoin('SieAppWebBundle:EstadomatriculaTipo','emt','with','ei.estadomatriculaTipo = emt.id')
                            ->where('e.codigoRude = :rude')
                            ->setParameter('rude', $codigoRude)
                            ->addOrderBy('get.id','DESC')
                            ->addOrderBy('nt.id','DESC')
                            ->addOrderBy('gt.id','DESC')
                            ->getQuery()
                            ->getResult();

        // SI EL ESTUDIANTE NO TIENE INSCRIPCIONES
        if(!$inscripciones){
            $response->setStatusCode(202);
            $response->setData('El estudiante no tiene inscripciones disponibles');
            return $response;
        }


        // VALIDAMOS SI LA UNIDAD EDUCATIVA TIENE TUICION SOBRE EL ESTUDIANTE
        if($inscripciones[0]['sie'] != $sie){
            $response->setStatusCode(202);
            $response->setData('No tiene tuición sobre el estudiante');
            return $response;   
        }

        $inscripcionesArray = [];
        foreach ($inscripciones as $key => $value) {
            $inscripcionesArray[] = array(
                'idInscripcion'=>$value['id'],
                'sie'=>$value['sie'],
                'institucioneducativa'=>$value['institucioneducativa'],
                'gestion'=>$value['gestion'],
                'nivel'=>$value['nivel'],
                'grado'=>$value['grado'],
                'paralelo'=>$value['paralelo'],
                'estadomatricula'=>$value['estadomatricula'],
                'ruta'=>$this->generateUrl('tramite_modificacion_calificaciones_formulario', array('flujoTipo'=>$flujoTipo,'idInscripcion'=>$value['id']))
            );
        }

        $response->setStatusCode(200);
        $response->setData(array(
            'estudiante'=> $estudiante->getNombre().' '.$estudiante->getPaterno().' '.$estudiante->getMaterno(),
            'inscripciones'=>$inscripcionesArray
        ));

        return $response;
    }

    public function formularioAction(Request $request, $idInscripcion, $flujoTipo){

        $em = $this->getDoctrine()->getManager();
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        $estudiante = $inscripcion->getEstudiante();

        $data = $this->get('notas')->regular($idInscripcion, 4);

        return $this->render('SieProcesosBundle:TramiteModificacionCalificaciones:formulario.html.twig', array(
            'inscripcion'=>$inscripcion,
            'estudiante'=>$estudiante,
            'data'=>$data,
            'idInscripcion'=>$inscripcion->getId(),
            'flujoTipo'=>$flujoTipo
        ));
    }

    public function formularioSaveAction(Request $request){
        $response = new JsonResponse();
        try {
            // OBTENEMOS EL ID DE INSCRIPCION
            $idInscripcion = $request->get('idInscripcion');
            $em = $this->getDoctrine()->getManager();
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

            // OBTENEMOS EL ID DEL TRAMITE SI SE TRATA DE UNA MODIFICACION
            $idTramite = $request->get('idTramite');

            // VERIFICAMOS SI EXISTE EL ARCHIVO
            if(isset($_FILES['archivo'])){
                $file = $_FILES['archivo'];

                $type = $file['type'];
                $size = $file['size'];
                $tmp_name = $file['tmp_name'];
                $name = $file['name'];
                $extension = explode('.', $name);
                $extension = $extension[count($extension)-1];
                $new_name = date('YmdHis').'.'.$extension;

                // GUARDAMOS EL ARCHIVO
                $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/modificacionNotas/' . $sie;
                if (!file_exists($directorio)) {
                    mkdir($directorio, 0777, true);
                }

                $archivador = $directorio.'/'.$new_name;
                //unlink($archivador);
                if(!move_uploaded_file($tmp_name, $archivador)){
                    $response->setStatusCode(500);
                    return $response;
                }
                
                // CREAMOS LOS DATOS DE LA IMAGEN
                $archivo = array(
                    'name' => $name,
                    'type' => $type,
                    'tmp_name' => 'nueva_ruta',
                    'size' => $size,
                    'new_name' => $new_name
                );
            }else{
                // SI NO EXISTE EL ARCHIVO LO OBTENEMOS DE LA SOLICITUD SI HUBIERA ID DE SOLICITUD
                if (isset($idTramite)) {
                    $tramiteDetalle = $em->createQueryBuilder()
                                        ->select('td.id idTramiteDetalle, te.tramiteEstado, wf.datos')
                                        ->from('SieAppWebBundle:Tramite','t')
                                        ->innerJoin('SieAppWebBundle:TramiteDetalle','td','with','td.tramite = t.id')
                                        ->innerJoin('SieAppWebBundle:TramiteEstado','te','with','td.tramiteEstado = te.id')
                                        ->innerJoin('SieAppWebBundle:WfSolicitudTramite','wf','with','wf.tramiteDetalle = td.id')
                                        ->where('t.id = :idTramite')
                                        ->orderBy('td.id','DESC')
                                        ->setMaxResults(1)
                                        ->setParameter('idTramite', $idTramite)
                                        ->getQuery()
                                        ->getResult();

                    $datos = json_decode($tramiteDetalle[0]['datos'],true);
                    $archivo = $datos['archivo'];

                }else{
                    $archivo = null;
                }
            }

            // OBTENEMOS LA INFORMACION DEL FORMULARIO
            $flujoTipo = $request->get('flujoTipo');
            $notas = json_decode($request->get('notas'),true);
            $notasCualitativas = json_decode($request->get('notasCualitativas'),true);
            $justificacion = mb_strtoupper($request->get('justificacion'),'utf-8');

            // ARMAMOS EL ARRAY DE LA DATA
            $data = array(
                'idInscripcion'=> $idInscripcion,
                'flujoTipo'=>$flujoTipo,
                'notas'=> $notas,
                'notasCualitativas'=>$notasCualitativas,
                'justificacion'=>$justificacion,
                'archivo'=>$archivo
            );

            // buscamos una solicitud previa
            $solicitudes = $em->createQueryBuilder()
                            ->select('td')
                            ->from('SieAppWebBundle:Tramite','t')
                            ->innerJoin('SieAppWebBundle:TramiteDetalle','td','with','td.tramite = t.id')
                            ->innerJoin('SieAppWebBundle:WfSolicitudTramite','wfst','with','wfst.tramiteDetalle = td.id')
                            ->where('t.institucioneducativa = :sie')
                            ->andWhere('t.flujoTipo = 7')
                            ->andWhere('wfst.datos like :inscripcion')
                            ->setParameter('sie', $sie)
                            ->setParameter('inscripcion', '%"idInscripcion":"'. $idInscripcion .'"%')
                            ->getQuery()
                            ->getResult();

            // OBTENEMOS EL ID DE LA TAREA
            $tarea = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array(
                'flujoTipo'=>$flujoTipo,
                'orden'=>1
            ));

            // OBTENEMOS EL LUGAR DE LA UNIDAD EDUCATIVA
            $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);

            // OBTENEMOS EL TIPO DE TRAMITE
            $tipoTramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs'=>'AMN'));

            // SI EXISTE EL ID DE TRAMITE
            if ($idTramite) {
                // ACTUALIZAMOS EL TRAMITE
                $registroTramite = $this->get('wftramite')->guardarTramiteEnviado(
                    $this->session->get('userId'),
                    $this->session->get('roluser'),
                    $flujoTipo,
                    $tarea->getId(),
                    'institucioneducativa',
                    $sie,
                    '',
                    '',
                    $idTramite,
                    json_encode($data),
                    '',
                    $lugarTipo['lugarTipoIdDistrito']
                );
            }else{
                // SI NO EXISTE
                // REGISTRAMOS UNA NUEVO TRAMITE
                $registroTramite = $this->get('wftramite')->guardarTramiteNuevo(
                    $this->session->get('userId'),
                    $this->session->get('roluser'),
                    $flujoTipo,
                    $tarea->getId(),
                    'institucioneducativa',
                    $sie,
                    '',//$obs,
                    $tipoTramite->getId(),//$tipoTramite,
                    '',//$varevaluacion,
                    '',//$idTramite,
                    json_encode($data),
                    '',//$lugarTipoLocalidad,
                    $lugarTipo['lugarTipoIdDistrito']
                );
            }

            $response->setStatusCode(200);
            $response->setData($registroTramite);
            return $response;

        } catch (Exception $e) {
            $response->setStatusCode(500);
            return $response;
        }
    }

    public function formularioImprimirAction(Request $request){
        dump($request);
        $idTramite = $request->get('idtramite');
        $idTramiteDetalle = $request->get('id_td');
        dump($idTramite);
        dump($idTramiteDetalle);
        die;
    }

    public function formularioObtenerDatosAction(Request $request){
        $idTramite = $request->get('idTramite');
        $em = $this->getDoctrine()->getManager();
        $tramiteDetalle = $em->createQueryBuilder()
                            ->select('td.id idTramiteDetalle, te.tramiteEstado, wf.datos, ft.id flujoTipo, ie.id sie')
                            ->from('SieAppWebBundle:Tramite','t')
                            ->innerJoin('SieAppWebBundle:TramiteDetalle','td','with','td.tramite = t.id')
                            ->innerJoin('SieAppWebBundle:TramiteEstado','te','with','td.tramiteEstado = te.id')
                            ->innerJoin('SieAppWebBundle:WfSolicitudTramite','wf','with','wf.tramiteDetalle = td.id')
                            ->innerJoin('SieAppWebBundle:FlujoTipo','ft','with','t.flujoTipo = ft.id')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','t.institucioneducativa = ie.id')
                            ->where('t.id = :idTramite')
                            ->orderBy('td.id','DESC')
                            ->setMaxResults(1)
                            ->setParameter('idTramite', $idTramite)
                            ->getQuery()
                            ->getResult();

        $idTramiteDetalle = $tramiteDetalle[0]['idTramiteDetalle'];
        $tramiteEstado = $tramiteDetalle[0]['tramiteEstado'];
        $data = json_decode($tramiteDetalle[0]['datos'],true);
        $flujoTipo = $tramiteDetalle[0]['flujoTipo'];

        // DATOS DE LAS NOTAS DEL ESTUDIANTE
        $data['archivoUrl'] = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/modificacionNotas/' . $tramiteDetalle[0]['sie'] .'/'. $data['archivo']['new_name'];
        // $data = json_encode($data);
        // dump($data['notas']);die;

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    /*=========================================================
    =            RECEPCION Y VERIFICACION DESTRITO            =
    =========================================================*/
    
    public function recepcionVerificaDistritoAction(Request $request){
        $idTramite = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->findBy(array('tramite'=>$tramite->getId()));
        // dump($tramiteDetalle);die;
        $tramiteDetalle = $em->createQueryBuilder()
                            ->select('td.id idTramiteDetalle, te.tramiteEstado, wf.datos, td.obs')
                            ->from('SieAppWebBundle:Tramite','t')
                            ->innerJoin('SieAppWebBundle:TramiteDetalle','td','with','td.tramite = t.id')
                            ->innerJoin('SieAppWebBundle:TramiteEstado','te','with','td.tramiteEstado = te.id')
                            ->innerJoin('SieAppWebBundle:WfSolicitudTramite','wf','with','wf.tramiteDetalle = td.id')
                            ->where('t.id = :idTramite')
                            ->orderBy('td.id','DESC')
                            ->setMaxResults(1)
                            ->setParameter('idTramite', $idTramite)
                            ->getQuery()
                            ->getResult();

        $datos = json_decode($tramiteDetalle[0]['datos'],true);
        
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($datos['idInscripcion']);
        $estudiante = $inscripcion->getEstudiante();
        $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        // dump($datos);
        // dump($idTramite);
        // die;
        // dump($request);die;

        return $this->render('SieProcesosBundle:TramiteModificacionCalificaciones:formularioVistaDistrito.html.twig', array(
            'inscripcion'=>$inscripcion,
            'estudiante'=>$estudiante,
            'datos'=>$datos,
            'sie'=>$sie,
            'idtramite'=>$idTramite,
            'obs'=>$tramiteDetalle[0]['obs']
        ));
    }    

    public function derivaDistritoAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $idTramite = $request->get('idTramite');
            $procedente = $request->get('procedente');
            $obs = $request->get('obs');


            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
            $flujoTipo = $tramite->getFlujoTipo()->getId();
            $sie = $tramite->getInstitucioneducativa()->getId();
            $gestion = $tramite->getGestionId();

            // OBTENEMOS EL ID DE LA TAREA
            $tarea = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array(
                'flujoTipo'=>$flujoTipo,
                'orden'=>2
            ));

            $tramiteDetalle = $em->createQueryBuilder()
                                ->select('td.id idTramiteDetalle, te.tramiteEstado, wf.datos')
                                ->from('SieAppWebBundle:Tramite','t')
                                ->innerJoin('SieAppWebBundle:TramiteDetalle','td','with','td.tramite = t.id')
                                ->innerJoin('SieAppWebBundle:TramiteEstado','te','with','td.tramiteEstado = te.id')
                                ->innerJoin('SieAppWebBundle:WfSolicitudTramite','wf','with','wf.tramiteDetalle = td.id')
                                ->where('t.id = :idTramite')
                                ->orderBy('td.id','DESC')
                                ->setMaxResults(1)
                                ->setParameter('idTramite', $idTramite)
                                ->getQuery()
                                ->getResult();

            $datos = json_decode($tramiteDetalle[0]['datos'],true);
            $datos['procedente'] = $procedente;
            $datos['obs'] = $obs;
            $datos = json_encode($datos);

            $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);

            $varevaluacion = ($procedente == 1)?'SI':'NO';

            $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                $this->session->get('userId'),
                $this->session->get('roluser'),
                $flujoTipo,
                $tarea->getId(),
                'institucioneducativa',
                $sie,
                $obs,
                $varevaluacion,
                $idTramite,
                $datos,
                '',
                $lugarTipo['lugarTipoIdDistrito']
            );

            $em->getConnection()->commit();

            $response = new JsonResponse();

            return $response;

        } catch (Exception $e) {
            $em->getConnection()->rollback();
        }
    }
    
    /*=====  End of RECEPCION Y VERIFICACION DESTRITO  ======*/
    


    /*=============================================================
    =            RECEPCION Y VERIFICACION DEPARTAMENTO            =
    =============================================================*/
    
    public function recepcionVerificaDepartamentoAction(Request $request){
        $idTramite = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->findBy(array('tramite'=>$tramite->getId()));
        $tramiteDetalle = $em->createQueryBuilder()
                            ->select('td.id idTramiteDetalle, te.tramiteEstado, wf.datos')
                            ->from('SieAppWebBundle:Tramite','t')
                            ->innerJoin('SieAppWebBundle:TramiteDetalle','td','with','td.tramite = t.id')
                            ->innerJoin('SieAppWebBundle:TramiteEstado','te','with','td.tramiteEstado = te.id')
                            ->innerJoin('SieAppWebBundle:WfSolicitudTramite','wf','with','wf.tramiteDetalle = td.id')
                            ->where('t.id = :idTramite')
                            ->orderBy('td.id','DESC')
                            ->setMaxResults(1)
                            ->setParameter('idTramite', $idTramite)
                            ->getQuery()
                            ->getResult();

        $datos = json_decode($tramiteDetalle[0]['datos'],true);
        
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($datos['idInscripcion']);
        $estudiante = $inscripcion->getEstudiante();
        $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();

        return $this->render('SieProcesosBundle:TramiteModificacionCalificaciones:formularioVistaDepartamento.html.twig', array(
            'inscripcion'=>$inscripcion,
            'estudiante'=>$estudiante,
            'datos'=>$datos,
            'sie'=>$sie,
            'idtramite'=>$idTramite
        ));
    }

    public function derivaDepartamentoAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $idTramite = $request->get('idTramite');
            $procedente = $request->get('procedente');
            $observarTramite = $request->get('observarTramite');
            $obs = $request->get('obs');

            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
            $flujoTipo = $tramite->getFlujoTipo()->getId();
            $sie = $tramite->getInstitucioneducativa()->getId();
            $gestion = $tramite->getGestionId();

            // OBTENEMOS EL ID DE LA TAREA
            $tarea = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array(
                'flujoTipo'=>$flujoTipo,
                'orden'=>3
            ));

            $tramiteDetalle = $em->createQueryBuilder()
                                ->select('td.id idTramiteDetalle, te.tramiteEstado, wf.datos')
                                ->from('SieAppWebBundle:Tramite','t')
                                ->innerJoin('SieAppWebBundle:TramiteDetalle','td','with','td.tramite = t.id')
                                ->innerJoin('SieAppWebBundle:TramiteEstado','te','with','td.tramiteEstado = te.id')
                                ->innerJoin('SieAppWebBundle:WfSolicitudTramite','wf','with','wf.tramiteDetalle = td.id')
                                ->where('t.id = :idTramite')
                                ->orderBy('td.id','DESC')
                                ->setMaxResults(1)
                                ->setParameter('idTramite', $idTramite)
                                ->getQuery()
                                ->getResult();

            $datos = json_decode($tramiteDetalle[0]['datos'],true);
            $datos['procedente'] = $procedente;
            $datos['obs'] = $obs;
            $datos = json_encode($datos);

            $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);

            $varevaluacion = ($procedente == 1)?'SI':'NO';

            // REGISTRAMOS LA PRIMERA TAREA
            $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                        $this->session->get('userId'),
                        $this->session->get('roluser'),
                        $flujoTipo,
                        $tarea->getId(),
                        'institucioneducativa',
                        $sie,
                        '',
                        $varevaluacion,
                        $idTramite,
                        $datos,
                        '',
                        $lugarTipo['lugarTipoIdDistrito']
                    );

            // VERIFICAMOS SI EL TRAMITE ES PROCEDENTE PARA DETERMINAR LA SIGUIENTE TAREA
            if ($procedente == 1) {
                // RECEPCIONAMOS NUEVAMENTE EL TRAMITE PARA MODIFICAR LAS NOTAS EN EL SISTEMA
                $tarea = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array(
                    'flujoTipo'=>$flujoTipo,
                    'orden'=>4
                ));

                $recibirTramite = $this->get('wftramite')->guardarTramiteRecibido(
                            $this->session->get('userId'),
                            $tarea->getId(),
                            $idTramite
                        );

                /*----------  REGISTRAMOS LAS CALIFICACIONES EN EL SISTEMA  ----------*/

                // public function guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion,$idtramite,$datos,$lugarTipoLocalidad_id,$lugarTipoDistrito_id)
                


                /*----------  --------------------------------------------  ----------*/


                // ENVIAMOS EL TRAMITE PARA FINALIZAR EL TRAMITE
                $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                            $this->session->get('userId'),
                            $this->session->get('roluser'),
                            $flujoTipo,
                            $tarea->getId(),
                            'institucioneducativa',
                            $sie,
                            '',
                            '',
                            $idTramite,
                            $datos,
                            '',
                            $lugarTipo['lugarTipoIdDistrito']
                        );
            }else{
                // RECEPCIONAMOS NUEVAMENTE EL TRAMITE PARA OBSERVARLO
                $tarea = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array(
                    'flujoTipo'=>$flujoTipo,
                    'orden'=>5
                ));

                $recibirTramite = $this->get('wftramite')->guardarTramiteRecibido(
                            $this->session->get('userId'),
                            $tarea->getId(),
                            $idTramite
                        );

                $observarTramite = ($observarTramite == 1)?'SI':'NO';

                // ENVIAMOS EL TRAMITE PARA FINALIZAR EL TRAMITE
                $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                            $this->session->get('userId'),
                            $this->session->get('roluser'),
                            $flujoTipo,
                            $tarea->getId(),
                            'institucioneducativa',
                            $sie,
                            $obs,
                            $observarTramite,
                            $idTramite,
                            $datos,
                            '',
                            $lugarTipo['lugarTipoIdDistrito']
                        );   
            }

            $em->getConnection()->commit();

            $response = new JsonResponse();

            return $response;

        } catch (Exception $e) {
            $em->getConnection()->rollback();
        }
    }
    
    /*=====  End of RECEPCION Y VERIFICACION DEPARTAMENTO  ======*/

}
