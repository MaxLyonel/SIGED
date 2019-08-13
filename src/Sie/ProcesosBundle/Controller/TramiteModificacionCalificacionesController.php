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

        $id = $request->get('id');
        $tipo = $request->get('tipo');

        $idTramite = null;
        $historial = null;
        $flujoTipo = null;

        if($tipo == 'idtramite'){
            $idTramite = $id;
            $em = $this->getDoctrine()->getManager();
            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
            $flujoTipo = $tramite->getFlujoTipo()->getId();
            $historial = $this->historial($idTramite);
        }else{
            $flujoTipo = $request->get('id');
        }
        // dump($historial);die;

        // VERIFICAMOS SI EL ID QUE SE ENVIA ES DEL FLUJO TIPO O DEL TRAMITE

        // if ($request->get('tipo') == 'idtramite') {
        //     $idTramite = $request->get('id');
        //     $em = $this->getDoctrine()->getManager();
        //     $tramiteDetalle = $em->createQueryBuilder()
        //                         ->select('td.id idTramiteDetalle, te.tramiteEstado, wf.datos, ft.id flujoTipo')
        //                         ->from('SieAppWebBundle:Tramite','t')
        //                         ->innerJoin('SieAppWebBundle:TramiteDetalle','td','with','td.tramite = t.id')
        //                         ->innerJoin('SieAppWebBundle:TramiteEstado','te','with','td.tramiteEstado = te.id')
        //                         ->innerJoin('SieAppWebBundle:WfSolicitudTramite','wf','with','wf.tramiteDetalle = td.id')
        //                         ->innerJoin('SieAppWebBundle:FlujoTipo','ft','with','t.flujoTipo = ft.id')
        //                         ->where('t.id = :idTramite')
        //                         ->orderBy('td.id','DESC')
        //                         ->setMaxResults(1)
        //                         ->setParameter('idTramite', $idTramite)
        //                         ->getQuery()
        //                         ->getResult();

        //     $idTramiteDetalle = $tramiteDetalle[0]['idTramiteDetalle'];
        //     $tramiteEstado = $tramiteDetalle[0]['tramiteEstado'];
        //     $dataPrev = json_decode($tramiteDetalle[0]['datos'],true);
        //     $flujoTipo = $tramiteDetalle[0]['flujoTipo'];
        //     // dump($datos);
        //     // dump($tramiteDetalle);
        //     dump($dataPrev);


        //     $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($dataPrev['idInscripcion']);
        //     $idInscripcion = $inscripcion->getId();
        //     $estudiante = $inscripcion->getEstudiante();

        //     // DATOS DE LAS NOTAS DEL ESTUDIANTE
        //     $data = $this->get('notas')->regular($idInscripcion, 4);
        //     $dataPrev['archivo'] = '';
        //     $dataPrev = json_encode($dataPrev);
        //     dump($dataPrev);
        //     // die;

        //     return $this->render('SieProcesosBundle:TramiteModificacionCalificaciones:formularioEditar.html.twig', array(
        //         'inscripcion'=>$inscripcion,
        //         'estudiante'=>$estudiante,
        //         'data'=>$data,
        //         'idInscripcion'=>$inscripcion->getId(),
        //         'flujoTipo'=>$flujoTipo,
        //         'idTramite'=>$idTramite,
        //         'dataPrev'=>$dataPrev
        //     ));
        // }

        return $this->render('SieProcesosBundle:TramiteModificacionCalificaciones:index.html.twig', array(
            'flujoTipo'=>$flujoTipo,
            'historial'=>$historial,
            'idTramite'=>$idTramite
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
                            ->select('ei.id, ie.id as sie, ie.institucioneducativa, get.gestion, nt.id idNivel, nt.nivel, gt.grado, pt.paralelo, tt.turno, emt.estadomatricula, emt.id estadomatriculaId')
                            ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                            ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','iec.institucioneducativa = ie.id')
                            ->innerJoin('SieAppWebBundle:GestionTipo','get','with','iec.gestionTipo = get.id')
                            ->innerJoin('SieAppWebBundle:NivelTipo','nt','with','iec.nivelTipo = nt.id')
                            ->innerJoin('SieAppWebBundle:GradoTipo','gt','with','iec.gradoTipo = gt.id')
                            ->innerJoin('SieAppWebBundle:ParaleloTipo','pt','with','iec.paraleloTipo = pt.id')
                            ->innerJoin('SieAppWebBundle:TurnoTipo','tt','with','iec.turnoTipo = tt.id')
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
                'turno'=>$value['turno'],
                'estadomatricula'=>$value['estadomatricula'],
                'estadomatriculaId'=>$value['estadomatriculaId'],
                'idNivel'=>$value['idNivel']
                // 'ruta'=>$this->generateUrl('tramite_modificacion_calificaciones_formulario', array('flujoTipo'=>$flujoTipo,'idInscripcion'=>$value['id']))
            );
        }

        $response->setStatusCode(200);
        $response->setData(array(
            'codigoRude'=>$estudiante->getCodigoRude(),
            'estudiante'=> $estudiante->getNombre().' '.$estudiante->getPaterno().' '.$estudiante->getMaterno(),
            'inscripciones'=>$inscripcionesArray
        ));

        return $response;
    }

    public function buscarCalificacionesAction(Request $request){
        $idInscripcion = $request->get('idInscripcion');
        $idTramite = $request->get('idTramite');
        $em = $this->getDoctrine()->getManager();
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

        if ($idTramite == "") {
            $idTramite = 0;
        }

        // VALIDAMOS QUE LA INSCRIPCION NO TENGA UN TRAMITE PENDIENTE
        $query = $em->getConnection()->prepare("
                    select *
                    from tramite t
                    inner join tramite_detalle td on td.tramite_id = t.id
                    inner join wf_solicitud_tramite wf on wf.tramite_detalle_id = td.id
                    where t.fecha_fin is null
                    and t.id != ". $idTramite ."
                    and wf.datos like '%".$idInscripcion."%'
                    and t.institucioneducativa_id = ". $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId() ."
                    and wf.es_valido is true
                    ");
        $query->execute();
        $tramitePendiente = $query->fetchAll();

        $datos = [];
        if(count($tramitePendiente)<=0) {
            $datos = $this->get('notas')->regular($idInscripcion, 4);
        }


        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData(array(
            'datos'=>$datos,
            'tramitePendiente'=>$tramitePendiente
        ));

        return $response;

        // return $this->render('SieProcesosBundle:TramiteModificacionCalificaciones:formulario.html.twig', array(
        //     'inscripcion'=>$inscripcion,
        //     'data'=>$data
        // ));
    }

    // public function formularioAction(Request $request, $idInscripcion, $flujoTipo){

    //     $em = $this->getDoctrine()->getManager();
    //     $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
    //     $estudiante = $inscripcion->getEstudiante();

    //     $data = $this->get('notas')->regular($idInscripcion, 4);

    //     return $this->render('SieProcesosBundle:TramiteModificacionCalificaciones:formulario.html.twig', array(
    //         'inscripcion'=>$inscripcion,
    //         'estudiante'=>$estudiante,
    //         'data'=>$data,
    //         'idInscripcion'=>$inscripcion->getId(),
    //         'flujoTipo'=>$flujoTipo
    //     ));
    // }
    // dxvyfxvy

    public function formularioSaveAction(Request $request){
        $response = new JsonResponse();
        try {
            // OBTENEMOS EL ID DE INSCRIPCION
            $idTramite = $request->get('idTramite');
            $idInscripcion = $request->get('idInscripcion');
            $em = $this->getDoctrine()->getManager();
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

            // OBTENEMOS EL ID DEL TRAMITE SI SE TRATA DE UNA MODIFICACION
            // $idTramite = $request->get('idTramite');

            // VERIFICAMOS SI EXISTE EL ARCHIVO
            if(isset($_FILES['informe'])){
                $file = $_FILES['informe'];

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
                $informe = array(
                    'name' => $name,
                    'type' => $type,
                    'tmp_name' => 'nueva_ruta',
                    'size' => $size,
                    'new_name' => $new_name
                );
            }else{
                $informe = null;
            }

            // OBTENEMOS LA INFORMACION DEL FORMULARIO
            $codigoRude = $request->get('codigoRude');
            $estudiante = $request->get('estudiante');
            $sie = $request->get('sie');
            $institucioneducativa = $request->get('institucioneducativa');
            $nivel = $request->get('nivel');
            $grado = $request->get('grado');
            $paralelo = $request->get('paralelo');
            $turno = $request->get('turno');
            $gestion = $request->get('gestion');

            $flujoTipo = $request->get('flujoTipo');
            $notas = json_decode($request->get('notas'),true);
            $notasCualitativas = json_decode($request->get('notasCualitativas'),true);
            $justificacion = mb_strtoupper($request->get('justificacion'),'utf-8');

            // ARMAMOS EL ARRAY DE LA DATA
            $data = array(
                'idInscripcion'=> $idInscripcion,
                'codigoRude' => $codigoRude,
                'estudiante' => $estudiante,
                'sie' => $sie,
                'institucioneducativa' => $institucioneducativa,
                'nivel' => $nivel,
                'grado' => $grado,
                'paralelo' => $paralelo,
                'turno' => $turno,
                'gestion' => $gestion,
                'flujoTipo'=>$flujoTipo,
                'notas'=> $notas,
                'notasCualitativas'=>$notasCualitativas,
                'justificacion'=>$justificacion,
                'informe'=>$informe
            );

            $tarea = $this->get('wftramite')->obtieneTarea($flujoTipo, 'idflujo');
            $tareaActual = $tarea['tarea_actual'];
            $tareaSiguiente = $tarea['tarea_siguiente'];

            // OBTENEMOS EL LUGAR DE LA UNIDAD EDUCATIVA
            $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);

            // OBTENEMOS EL TIPO DE TRAMITE
            $tipoTramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs'=>'AMN'));

            if ($idTramite == null) {
                // REGISTRAMOS UNA NUEVO TRAMITE
                $registroTramite = $this->get('wftramite')->guardarTramiteNuevo(
                    $this->session->get('userId'),
                    $this->session->get('roluser'),
                    $flujoTipo,
                    $tareaActual,
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

                $msg = "El Tramite ".$registroTramite['msg']." fue guardado y enviado exitosamente";
            }else{
                // NUEVAMENTE ENVIAMOS EL TRAMITE
                $registroTramite = $this->get('wftramite')->guardarTramiteEnviado(
                    $this->session->get('userId'),
                    $this->session->get('roluser'),
                    $flujoTipo,
                    $tareaActual,
                    'institucioneducativa',
                    $sie,
                    '',
                    '',
                    $idTramite,
                    json_encode($data),
                    '',
                    $lugarTipo['lugarTipoIdDistrito']
                );

                $msg = "El Tramite ".$registroTramite['msg']." fue enviado exitosamente";
            }

            $request->getSession()
                    ->getFlashBag()
                    ->add('exito', $msg);

            $response->setStatusCode(200);
            $response->setData($registroTramite);
            return $response;

        } catch (Exception $e) {
            $response->setStatusCode(500);
            return $response;
        }
    }

    public function formularioImprimirAction(Request $request){

        $idTramite = $request->get('idtramite');
        // dump($idTramite);die;
        $idTramiteDetalle = $request->get('id_td');

        return $this->redirectToRoute('download_tramite_modificacion_calificaciones', array('idTramite'=>$idTramite));
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

    public function historial($idTramite){
        try {
            $em = $this->getDoctrine()->getManager();
            $tarea = $this->get('wftramite')->obtieneTarea($idTramite, 'idtramite');
            $tareaActual = '';
            foreach ($tarea as $t) {
                $tareaActual = $t['tarea_actual'];
            }

            $query = $em->getConnection()->prepare("
                        select * from tramite_detalle td
                        inner join flujo_proceso fp on td.flujo_proceso_id = fp.id
                        inner join proceso_tipo pt on fp.proceso_id = pt.id
                        inner join rol_tipo rt on fp.rol_tipo_id = rt.id
                        left JOIN wf_solicitud_tramite wf on td.id=wf.tramite_detalle_id
                        where ((wf.id is not null and wf.es_valido is true) or td.id is not null) 
                        and td.tramite_id = ". $idTramite ." 
                        and wf.es_valido is true
                        order by fp.orden asc
                        ");
            $query->execute();
            $dato = $query->fetchAll();

            $array = [];
            foreach ($dato as $key => $value) {
                if ($value['datos'] != null) {
                    $dato[$key]['datos'] = json_decode($value['datos'], true);
                }
            }
            // dump($dato);die;
            return $dato;

        } catch (Exception $e) {
            
        }
    }

    public function formularioVistaImprimirLibretaAction(Request $request){
        try {

            $idTramite = $request->get('id');
            $em = $this->getDoctrine()->getManager();
            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
            $flujoTipo = $tramite->getFlujoTipo()->getId();
            $historial = $this->historial($idTramite);

            foreach ($historial as $h) {
                if($h['orden'] == 1){
                    $idInscripcion = $h['datos']['idInscripcion'];
                    $datosNotas = $h['datos'];
                }
            }

            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

            $rude = $inscripcion->getEstudiante()->getcodigoRude();
            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
            $paralelo = $inscripcion->getInstitucioneducativaCurso()->getParaleloTipo()->getId();
            $turno = $inscripcion->getInstitucioneducativaCurso()->getTurnoTipo()->getId();
            $ciclo = $inscripcion->getInstitucioneducativaCurso()->getCicloTipo()->getId();

            return $this->render('SieProcesosBundle:TramiteModificacionCalificaciones:formularioVistaImprimirLibreta.html.twig', array(
                'historial'=>$historial,
                'datos'=>$datosNotas,
                'idTramite'=>$idTramite,
                'idInscripcion' => $idInscripcion,
                'rude' => $rude,
                'sie' => $sie,
                'gestion' => $gestion,
                'nivel' => $nivel,
                'grado' => $grado,
                'paralelo' => $paralelo,
                'turno' => $turno,
                'ciclo' => $ciclo
            ));
        } catch (Exception $e) {
            
        }
    }

    public function formularioVistaImprimirFinalizarAction(Request $request){
        try {
            $idTramite = $request->get('idTramite');
            $em = $this->getDoctrine()->getManager();
            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
            $flujoTipo = $tramite->getFlujoTipo()->getId();
            $sie = $tramite->getInstitucioneducativa()->getId();
            $gestion = $tramite->getGestionId();

            $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);

            // OBTENEMOS LA TAREA ACTUAL Y SIGUIENTE
            $tarea = $this->get('wftramite')->obtieneTarea($idTramite, 'idtramite');
            $tareaActual = $tarea['tarea_actual'];

            // ENVIAMOS EL TRAMITE
            $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                $this->session->get('userId'),
                $this->session->get('roluser'),
                $flujoTipo,
                $tareaActual,
                'institucioneducativa',
                $sie,
                'Libreta impresa',
                '',
                $idTramite,
                '',
                '',
                $lugarTipo['lugarTipoIdDistrito']
            );

            $request->getSession()
                    ->getFlashBag()
                    ->add('exito', "Tramite Nro. ". $idTramite ." finalizado");

            return $this->redirectToRoute('wf_tramite_index', array('tipo'=>3));

        } catch (Exception $e) {
            
        }

    }

    /*=========================================================
    =            RECEPCION Y VERIFICACION DESTRITO            =
    =========================================================*/
    
    public function recepcionVerificaDistritoAction(Request $request){
        $idTramite = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
        return $this->render('SieProcesosBundle:TramiteModificacionCalificaciones:formularioVistaDistrito.html.twig', array(
            'idTramite'=>$idTramite,
            'historial'=>$this->historial($idTramite)
        ));
    }    

    public function derivaDistritoAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $idTramite = $request->get('idTramite');
            $procedente = $request->get('procedente');
            $observacion = $request->get('observacion');
            $subsanable = $request->get('subsanable');

            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
            $flujoTipo = $tramite->getFlujoTipo()->getId();
            $sie = $tramite->getInstitucioneducativa()->getId();
            $gestion = $tramite->getGestionId();

            $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);

            // OBTENEMOS LA TAREA ACTUAL Y SIGUIENTE
            $tarea = $this->get('wftramite')->obtieneTarea($idTramite, 'idtramite');
            $tareaActual = '';
            $tareaSiguiente = '';
            foreach ($tarea as $t) {
                $tareaActual = $t['tarea_actual'];
                if ($t['condicion'] == 'NO') {
                    $tareaSiguiente = $t['tarea_siguiente'];
                }
            }

            // VERIFICAMOS SI EXISTE EL INFORME
            if(isset($_FILES['informe'])){
                $file = $_FILES['informe'];

                if ($file['name'] != "") {
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
                    $informe = array(
                        'name' => $name,
                        'type' => $type,
                        'tmp_name' => 'nueva_ruta',
                        'size' => $size,
                        'new_name' => $new_name
                    );
                }else{
                    $informe = null;
                }
            }else{
                $informe = null;
            }

            $datos = json_encode(array(
                'sie'=>$sie,
                'procedente'=>$procedente,
                'subsanable'=>$subsanable,
                'observacion'=>$observacion,
                'informe'=>$informe
            ));

            // ENVIAMOS EL TRAMITE
            $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                $this->session->get('userId'),
                $this->session->get('roluser'),
                $flujoTipo,
                $tareaActual,
                'institucioneducativa',
                $sie,
                $observacion,
                $procedente,
                $idTramite,
                $datos,
                '',
                $lugarTipo['lugarTipoIdDistrito']
            );

            // VERIFICAR SI EL TRAMITE NO ES PROCEDENTE PARA REGISTRAR LA TAREA DE OBSERVACION
            if ($procedente == 'NO') {

                $recibirTramite = $this->get('wftramite')->guardarTramiteRecibido(
                    $this->session->get('userId'),
                    $tareaSiguiente,
                    $idTramite
                );

                $datos = json_encode(array(
                    'subsanable'=>$subsanable,
                    'observacion'=>$observacion
                ));

                // NUEVAMENTE ENVIAMOS EL TRAMITE
                $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                    $this->session->get('userId'),
                    $this->session->get('roluser'),
                    $flujoTipo,
                    $tareaSiguiente,
                    'institucioneducativa',
                    $sie,
                    $observacion,
                    $subsanable,
                    $idTramite,
                    $datos,
                    '',
                    $lugarTipo['lugarTipoIdDistrito']
                );
            }

            $em->getConnection()->commit();

            $request->getSession()
                    ->getFlashBag()
                    ->add('exito', "El Tramite ". $idTramite ." fue enviado exitosamente");

            return $this->redirectToRoute('wf_tramite_index', array('tipo'=>3));

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

        $historial = $this->historial($idTramite);
        $idInscripcion = null;
        $sie = null;
        $gestion = null;
        foreach ($historial as $h) {
            if($h['orden'] == 1){
                $idInscripcion = $h['datos']['idInscripcion'];
                $sie = $h['datos']['sie'];
                $gestion = $h['datos']['gestion'];
            }
        }

        $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);

        $msg = '';
        if($operativo >= 4){
            $msg = 'Se derivará el trámite a la unidad educativa, para que imprima la nueva libreta';
        }else{
            $msg = "";
        }

        return $this->render('SieProcesosBundle:TramiteModificacionCalificaciones:formularioVistaDepartamento.html.twig', array(
            'idTramite'=>$idTramite,
            'historial'=>$historial,
            'msg'=>$msg
        ));
    }

    public function derivaDepartamentoAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $idTramite = $request->get('idTramite');
            $aprueba = $request->get('aprueba');
            $observacion = $request->get('observacion');

            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
            $flujoTipo = $tramite->getFlujoTipo()->getId();
            $sie = $tramite->getInstitucioneducativa()->getId();
            $gestion = $tramite->getGestionId();

            $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);

            // OBTENEMOS LA TAREA ACTUAL Y SIGUIENTE
            $tarea = $this->get('wftramite')->obtieneTarea($idTramite, 'idtramite');
            $tareaActual = '';
            $tareaSiguiente = '';
            foreach ($tarea as $t) {
                $tareaActual = $t['tarea_actual'];
                if ($t['condicion'] == 'SI') {
                    $tareaSiguiente = $t['tarea_siguiente'];
                }
            }

            // OBTENEMOS EL OPERATIVO DE LA INSCRIPCION SI ES GESTION CONSOLIDADA
            $historial = $this->historial($idTramite);
            $datosNotas = null;
            $idInscripcion = null;
            $sie = null;
            $gestion = null;
            foreach ($historial as $h) {
                if($h['orden'] == 1){
                    $idInscripcion = $h['datos']['idInscripcion'];
                    $sie = $h['datos']['sie'];
                    $gestion = $h['datos']['gestion'];
                    $datosNotas = $h['datos'];
                }
            }
// dump($datosNotas);die;
            // VERIFICAMOS SI EXISTE EL INFORME
            if(isset($_FILES['resAdm'])){
                $file = $_FILES['resAdm'];

                if ($file['name'] != "") {
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
                    $resAdm = array(
                        'name' => $name,
                        'type' => $type,
                        'tmp_name' => 'nueva_ruta',
                        'size' => $size,
                        'new_name' => $new_name
                    );
                }else{
                    $resAdm = null;
                }
            }else{
                $resAdm = null;
            }

            // VERIFICAMOS SI LA GESTION ES CONSOLIDADA OPERATIVO >= 4 O LA GESTION PERMITE LA IMPRESION DE LA LIBRETA ELECTRONICA
            $gestionConsolidada = 'NO';
            if($gestion >= 2015){
                $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);
                if($operativo >= 4 ){
                    $gestionConsolidada = 'SI';
                }
            }

            // ARMAMOS EL ARRAY DE LOS DATOS

            $datos = json_encode(array(
                'sie'=>$sie,
                'aprueba'=>$aprueba,
                'gestionConsolidada'=>$gestionConsolidada,
                'observacion'=>$observacion,
                'resAdm'=>$resAdm
            ));

            // ENVIAMOS EL TRAMITE
            $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                $this->session->get('userId'),
                $this->session->get('roluser'),
                $flujoTipo,
                $tareaActual,
                'institucioneducativa',
                $sie,
                $observacion,
                $aprueba,
                $idTramite,
                $datos,
                '',
                $lugarTipo['lugarTipoIdDistrito']
            );

            // VERIFICAR SI EL TRAMITE ES APROBADO LO RECEPCIONAMOS PARA REGISTRAR LAS CALIFICACIONES
            if ($aprueba == 'SI') {
                // RECIBIMOS LA SIGUIENTE TAREA
                $recibirTramite = $this->get('wftramite')->guardarTramiteRecibido(
                    $this->session->get('userId'),
                    $tareaSiguiente,
                    $idTramite
                );

                /*----------  MODIFICAMOS LAS CALIFICACIONES EN EL SISTEMA  ----------*/

                $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
                $insGestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
                $insNivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
                $insGrado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
                // REGISTRAMOS LAS NOTAS CUANTITATIVAS
                if(count($datosNotas['notas']) > 0){
                    foreach ($datosNotas['notas'] as $n) {
                        if ($n['idEstudianteNota'] == 'nuevo') {
                            // REGISTRAMOS LA NUEVA CALIFICACION
                            $datoNota = $this->get('notas')->registrarNota($n['idNotaTipo'], $n['idEstudianteAsignatura'],$n['notaNueva'], '');
                        }else{
                            // ACTUALIZAMOS LA CALIFICACION
                            $datoNota = $this->get('notas')->modificarNota($n['idEstudianteNota'],$n['notaNueva'], '');
                        }

                        // CALCULAMOS LOS PROMEDIOS
                        switch ($gestion) {
                            case 2008:
                            case 2009:
                            case 2010:
                            case 2011:
                            case 2012:  // Notas trimestrales
                                        $this->get('notas')->calcularPromediosTrimestrales($datoNota->getEstudianteAsignatura()->getId());
                                        break;
                            case 2013:
                                        if($insGrado != 1){
                                            $this->get('notas')->calcularPromediosTrimestrales($datoNota->getEstudianteAsignatura()->getId());
                                        }else{
                                            $this->get('notas')->calcularPromedioBimestral($datoNota->getEstudianteAsignatura()->getId());
                                        }
                                        break;
                            default:
                                        $this->get('notas')->calcularPromedioBimestral($datoNota->getEstudianteAsignatura()->getId());
                                        break;
                        }
                    }
                }

                // REGISTRAMOS LAS NOTAS CUALITATIVAS
                if(count($datosNotas['notasCualitativas']) > 0){
                    foreach ($datosNotas['notasCualitativas'] as $nc) {
                        if ($nc['idEstudianteNotaCualitativa'] == 'nuevo') {
                            // REGISTRAMOS LA NUEVA VALORACION CUALITATIVA
                            $nuevaNotaCualitativa = $this->get('notas')->registrarNotaCualitativa($nc['idNotaTipo'], $nc['idInscripcion'],$nc['notaNuevaCualitativa'], 0);
                        }else{
                            // ACTUALIZAMOS LA VALORACION CUALITATIVA
                            $nuevaNotaCualitativa = $this->get('notas')->modificarNotaCualitativa($nc['idEstudianteNotaCualitativa'],$nc['notaNuevaCualitativa'], 0);
                        }
                    }
                }

                // ACTUALIZAMOS EL ESTADO DE MATRICULA
                $this->get('notas')->actualizarEstadoMatricula($idInscripcion);

                /*----------  FIN MODIFICACION DE CALIFICACIONES EN EL SIE  ----------*/
                

                // ARMAMOS EL ARRAY DE DATOS
                $datos = json_encode(array(
                    'gestionConsolidada'=>$gestionConsolidada,
                    'observacion'=>$observacion
                ));

                // NUEVAMENTE ENVIAMOS EL TRAMITE
                $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                    $this->session->get('userId'),
                    $this->session->get('roluser'),
                    $flujoTipo,
                    $tareaSiguiente,
                    'institucioneducativa',
                    $sie,
                    $observacion,
                    $gestionConsolidada,
                    $idTramite,
                    $datos,
                    '',
                    $lugarTipo['lugarTipoIdDistrito']
                );
            }

            $em->getConnection()->commit();

            $request->getSession()
                    ->getFlashBag()
                    ->add('exito', "El Tramite ". $idTramite ."fue enviado exitosamente");

            return $this->redirectToRoute('wf_tramite_index', array('tipo'=>3));

        } catch (Exception $e) {
            $em->getConnection()->rollback();
        }
    }
    
    /*=====  End of RECEPCION Y VERIFICACION DEPARTAMENTO  ======*/

}
