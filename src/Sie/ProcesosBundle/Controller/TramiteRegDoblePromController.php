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
 * Solicitud modification y adicion calificaciones controller.
 *
 */
class TramiteRegDoblePromController extends Controller {
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

        return $this->render('SieProcesosBundle:TramiteRegDobleProm:index.html.twig', array(
            'flujoTipo'=>$flujoTipo,
            'historial'=>$historial,
            'idTramite'=>$idTramite,
            'sieActual'=>$ie_id
        ));
    }

    public function buscarEstudianteAction(Request $request){
      
        $response = new JsonResponse();

        $codigoRude = $request->get('codigoRude');
        $flujoTipo = $request->get('flujoTipo');
        $sie = $this->session->get('ie_id');
        $rol = $this->session->get('roluser');
        $rolLugarId = $this->session->get('roluserlugarid');
        
        //VALIDAMOS QUE EL USUARIO QUE ESTA REALIZANDO LA SOLICITUD SEA CON ROL DE TECNICO DISTRITO
        //PARA EVITAR ERRORES CUANDO SE SOBREESCRIBEN LAS SESIONES
        if ($rol != 10) {
             $response->setStatusCode(202);
             $response->setData('El usuario actual no tiene rol de Tecnico Distrito, cierre sesion e ingrese nuevamente al sistema.');
             return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$codigoRude));

        // VALIDAMOS QUE EL ESTUDIANTE NO TENGA DOCUMENTOS EMITIDOS
        $documentos = $this->get('funciones')->validarDocumentoEstudiante($codigoRude);
        if (count($documentos) > 0) {
            $response->setStatusCode(202);
            $response->setData('El estudiante con el código RUDE '. $codigoRude .' tiene documentos emitidos, por esto no puede realizar la solicitud!');
            return $response;
        }
        // dump($documentos);die;

        // SI EL ESTUDIANTE NO EXISTE, DEVOLVEMOS 204 SIN CONTENIDO
        if(!$estudiante){
            $response->setStatusCode(202);
            $response->setData('El estudiante con el código RUDE '. $codigoRude .' no fue encontrado.');
            return $response;
        }

        $query = $em->getConnection()->prepare("select * from sp_ver_dobles_promocion('" . $codigoRude . "') ;");
        $query->execute();
        $inscripciones = $query->fetchAll();

        // SI EL ESTUDIANTE NO TIENE INSCRIPCIONES
        if(!$inscripciones){
            $response->setStatusCode(202);
            $response->setData('El estudiante no tiene inscripciones registradas');
            return $response;
        }

        $inscripcionesArray = [];
        foreach ($inscripciones as $key => $value) {
            $inscripcionesArray[] = array(
                'idInscripcion'=>$value['idei'],
                'sie'=>$value['sie'],
                'institucioneducativa'=>$value['institucioneducativa'],
                'gestion'=>$value['gestion'],
                'nivel'=>$value['nivel'],
                'grado'=>$value['grado'],
                'paralelo'=>$value['paralelo'],
                'turno'=>$value['turno'],
                'estadomatricula'=>$value['estadomatricula'],
                'estadomatriculaid'=>$value['estadomatriculaid'],
                'idnivel'=>$value['idnivel'],
                'departamento'=>$value['departamento'],
                'iddistrito'=>$value['iddistrito'],
                'distrito'=>$value['distrito'],
                'ban'=>$value['ban']
            );
        }

        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $response->setStatusCode(200);
        $response->setData(array(
            'codigoRude'=>$estudiante->getCodigoRude(),
            'rolLugarId'=> $rolLugarId,
            'estudiante'=> $estudiante->getNombre().' '.$estudiante->getPaterno().' '.$estudiante->getMaterno(),
            'carnet'=>($estudiante->getSegipId() == 1)?$estudiante->getCarnetIdentidad():'',
            'complemento'=>($estudiante->getSegipId() == 1)?$estudiante->getComplemento():'',
            'inscripciones'=>$inscripcionesArray
        ));
        return $response;
    }

    public function buscarInscripcionAction(Request $request){
        $idInscripcion = $request->get('idInscripcion');
        $idTramite = $request->get('idTramite');
        $flujoTipo = $request->get('flujoTipo');
        $em = $this->getDoctrine()->getManager();
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

        if ($idTramite == "") {
            $idTramite = 0;
        }

        // VALIDAMOS QUE LA INSCRIPCION NO TENGA UN TRAMITE PENDIENTE
        $query = $em->getConnection()->prepare("
                    select *
                    from tramite t
                    inner join tramite_detalle td on td.tramite_id = t.id
                    inner join wf_solicitud_tramite wf on wf.tramite_detalle_id = td.id
                    inner join flujo_proceso fp on td.flujo_proceso_id =fp.id
                    where t.fecha_fin is null
                    and t.id != ". $idTramite ."
                    and wf.datos like '%".$idInscripcion."%'
                    and t.institucioneducativa_id = ". $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId() ."
                    and wf.es_valido is true
                    and fp.flujo_tipo_id = ".$flujoTipo);

        $query->execute();
        $tramitePendiente = $query->fetchAll();

        $datos = [];
        
        // ESTADOS MATRICULAS
        $estados = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(4,5,11,28)));
        $arrayEstados = [];
        foreach ($estados as $key => $value) {
             $arrayEstados[] = array(
                 'id'=>$value->getId(),
                 'estadoMatricula'=>$value->getEstadomatricula()
             );
        }

        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData(array(
            'tramitePendiente'=>$tramitePendiente,
            'estadosMatricula'=>$arrayEstados//,
        ));

        return $response;

    }

    public function formularioSaveAction(Request $request){
        $response = new JsonResponse();
        try {
            // OBTENEMOS EL ID DE INSCRIPCION
            $idTramite = $request->get('idTramite');
            $idInscripcion = $request->get('idInscripcion');

            // dump($$response);
            // dump($idTramite);
            // dump($idInscripcion); die;          
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            // $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            // $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
            $sie = $request->get('sie');
            // $sie = $this->session->get('ie_id');
            // dump($sie);die;
            $gestion = $this->session->get('currentyear');
           
            // OBTENEMOS EL ID DEL TRAMITE SI SE TRATA DE UNA MODIFICACION
            $idTramite = $request->get('idTramite');
            // VERIFICAMOS SI EXISTE EL ARCHIVO
            if(isset($_FILES['informeue']) and isset($_FILES['informe'])){
                //ARCHIVO UE
                $file = $_FILES['informeue'];
                $type = $file['type'];
                $size = $file['size'];
                $tmp_name = $file['tmp_name'];
                $name = $file['name'];
                $extension = explode('.', $name);
                $extension = $extension[count($extension)-1];
                $new_name = 'ue'.date('YmdHis').'.'.$extension;

                // GUARDAMOS EL ARCHIVO UE
                $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/dobleProm/' . $sie;
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
                $informeue = array(
                    'name' => $name,
                    'type' => $type,
                    'tmp_name' => 'nueva_ruta',
                    'size' => $size,
                    'new_name' => $new_name
                );
                // dump($informeue);
                // ARCHIVO DDE
                $file = $_FILES['informe'];
                $type = $file['type'];
                $size = $file['size'];
                $tmp_name = $file['tmp_name'];
                $name = $file['name'];
                $extension = explode('.', $name);
                $extension = $extension[count($extension)-1];
                $new_name = 'ddd'.date('YmdHis').'.'.$extension;

                // GUARDAMOS EL ARCHIVO DDE
                $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/dobleProm/' . $sie;
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
                // dump($informe); 
            }else{
                $informe = null;
            }

            // OBTENEMOS LA INFORMACION DEL FORMULARIO
            $codigoRude = $request->get('codigoRude');
            $estudiante = $request->get('estudiante');
            $carnet = $request->get('carnet');
            $complemento = $request->get('complemento');
            $sieInscripcion = $request->get('sie');
            $institucioneducativa = $request->get('institucioneducativa');
            $nivelInscripcion = $request->get('nivel');
            $gradoInscripcion = $request->get('grado');
            $paraleloInscripcion = $request->get('paralelo');
            $turnoInscripcion = $request->get('turno');
            $gestionInscripcion = $request->get('gestion');
            $departamentoInscripcion = $request->get('departamento');
            $distritoInscripcion = $request->get('distrito');
                      
            $flujoTipo = $request->get('flujoTipo');
            $estadomatricula = $request->get('estadomatricula');
            $estadomatriculaid = $request->get('estadomatriculaid');
            $checkSolicitud = $request->get('checkSolicitud');
            $checkFotocopiaci = $request->get('checkFotocopiaci');
            $checkInformeue = $request->get('checkInformeue');
            $checkInforme = $request->get('checkInforme');
            // $datosFormulario = '';

            $dataInscription = array(
                "gestion"=>$gestionInscripcion ,
                "nivelId"=>$inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId(),
                "nivel"=>$nivelInscripcion,
                "gradoId"=>$inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId(),
                "grado"=>$gradoInscripcion,
                "paraleloId"=>$inscripcion->getInstitucioneducativaCurso()->getParaleloTipo()->getId(),
                "paralelo"=>$paraleloInscripcion,
                "turnoId"=>$inscripcion->getInstitucioneducativaCurso()->getTurnoTipo()->getId(),
                "turno"=>$turnoInscripcion,
                "keyId"=>"",
                "idTramite"=>"",
                "flujoTipo"=>$flujoTipo,
                "sie"=>$sieInscripcion,
                "posSelected"=>0,
                "codigoRude"=>$codigoRude
            );

            // ARMAMOS EL ARRAY DE LA DATA
            $data = array(
                'idInscripcion'=> $idInscripcion,
                'codigoRude' => $codigoRude,
                'estudiante' => $estudiante,
                'carnet' => $carnet,
                'complemento' => $complemento,
                'sie' => $sieInscripcion,
                'institucioneducativa' => $institucioneducativa,
                'nivel' => $nivelInscripcion,
                'grado' => $gradoInscripcion,
                'paralelo' => $paraleloInscripcion,
                'turno' => $turnoInscripcion,
                'gestion' => $gestionInscripcion,
                'departamento' => $departamentoInscripcion,
                'distrito' => $distritoInscripcion,
                'flujoTipo'=>$flujoTipo,
                'dataInscription'=>array($dataInscription),
                'estadomatricula'=> $estadomatricula,
                'estadomatriculaid'=> $estadomatriculaid,
                'checkSolicitud'=>$checkSolicitud,
                'checkFotocopiaci'=>$checkFotocopiaci,
                'checkInformeue'=>$checkInformeue,
                'checkInforme'=>$checkInforme,
                'informeue'=>$informeue,
                'informe'=>$informe
            );

            // dump($data); die;
            $tarea = $this->get('wftramite')->obtieneTarea($flujoTipo, 'idflujo');
            $tareaActual = $tarea['tarea_actual'];
            $tareaSiguiente = $tarea['tarea_siguiente'];

            //dump($tareaActual);
            // dump($tareaSiguiente); die; 
            // OBTENEMOS EL LUGAR DE LA UNIDAD EDUCATIVA
            $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);

            // OBTENEMOS EL TIPO DE TRAMITE
            $tipoTramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs'=>'MEP'));
           
            if ($idTramite == null) {
                
                // OBTENEMOS OPERATIVO ACTUAL Y LO AGREGAMOS AL ARRAY DE DATOS           
                $data['operativoActual'] = $this->get('funciones')->obtenerOperativo($sieInscripcion,$gestionInscripcion);

                // REGISTRAMOS UN NUEVO TRAMITE
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
                    json_encode($data, JSON_UNESCAPED_UNICODE),
                    '',//$lugarTipoLocalidad,
                    $lugarTipo['lugarTipoIdDistrito']
                );

                if ($registroTramite['dato'] == false) {
                    $response->setStatusCode(500);
                    return $response;
                }

                $idTramite = $registroTramite['idtramite'];

                // $msg = "El Tramite ".$registroTramite['msg']." fue guardado y enviado exitosamente";

            }else{           
                
                // RECUPERAMOS EL OPERATIVO DONDE SE INICIO EL TRAMITE Y LO AGREGAMOS AL ARRAY DE DATOS
                // $datosFormulario = $this->datosFormulario($idTramite);
                // $data['operativoActual'] = $datosFormulario['operativoActual'];

                // // NUEVAMENTE ENVIAMOS EL TRAMITE
                // $registroTramite = $this->get('wftramite')->guardarTramiteEnviado(
                //     $this->session->get('userId'),
                //     $this->session->get('roluser'),
                //     $flujoTipo,
                //     $tareaActual,
                //     'institucioneducativa',
                //     $sie,
                //     '',
                //     '',
                //     $idTramite,
                //     json_encode($data, JSON_UNESCAPED_UNICODE),
                //     '',
                //     $lugarTipo['lugarTipoIdDistrito']
                // );

                // if ($registroTramite['dato'] == false) {
                //     $response->setStatusCode(500);
                //     return $response;
                // }

                $msg = "El Tramite error";

                $request->getSession()
                        ->getFlashBag()
                        ->add('exito', $msg);
            }

            // $datos = $this->datosFormulario($idTramite);
            // $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$datos['codigoRude']));

            $response->setStatusCode(200);
            $response->setData(array(
                'idTramite'=>$idTramite,
                'urlreporte'=> $this->generateUrl('tramite_add_mod_download_requet', array('idTramite'=>$idTramite))
            ));

            $em->getConnection()->commit();

            return $response;

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $response->setStatusCode(500);
            return $response;
        }
    }

    public function formularioImprimirAction(Request $request, $idTramite){
        
        $response = new Response();
        $gestion = $this->session->get('currentyear');
        $codigoQR = 'FMEP'.$idTramite.'|'.$gestion;

        $data = $this->session->get('userId').'|'.$gestion.'|'.$idTramite;
        //$link = 'http://'.$_SERVER['SERVER_NAME'].'/sie/'.$this->getLinkEncript($codigoQR);
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'requestProcess'.$idTramite.'_'.$this->session->get('currentyear'). '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') .'reg_est_cert_cal_solicitud_tramite_sin_calif_v1.rptdesign&tramite_id='.$idTramite.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }     
   /*=====  End of RECEPCION Y VERIFICACION DESTRITO  ======*/
    
   public function forumlarioDescargaMEPAction(Request $request){

    $response = new Response();
    $idTramite = $request->get('idtramite');
    $gestion = $this->session->get('currentyear');
    $codigoQR = 'FMEP'.$idTramite.'|'.$gestion;
    $data = $this->session->get('userId').'|'.$gestion.'|'.$idTramite;
    //$link = 'http://'.$_SERVER['SERVER_NAME'].'/sie/'.$this->getLinkEncript($codigoQR);
    $response->headers->set('Content-type', 'application/pdf');
    $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'requestProcess'.$idTramite.'_'.$this->session->get('currentyear'). '.pdf'));
    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') .'reg_est_cert_cal_solicitud_tramite_sin_calif_v1.rptdesign&tramite_id='.$idTramite.'&&__format=pdf&'));
    $response->setStatusCode(200);
    $response->headers->set('Content-Transfer-Encoding', 'binary');
    $response->headers->set('Pragma', 'no-cache');
    $response->headers->set('Expires', '0');
    return $response;
}  


    /*=============================================================
    =            RECEPCION Y VERIFICACION DEPARTAMENTO            =
    =============================================================*/
    
    public function recepcionVerificaDepartamentoAction(Request $request){
        $idTramite = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
        
        $historial = $this->historial($idTramite);
        // dump($idTramite);
        // dump($historial);
        // die;
        return $this->render('SieProcesosBundle:TramiteRegDobleProm:formularioVistaDepartamento.html.twig', array(
            'idTramite'=>$idTramite,
            'historial'=>$historial
        ));
    }

    public function anulaDepartamentoAction(Request $request){
        try {
            
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            
            $idTramite = $request->get('idTramite');
            // dump($idTramite);
            // die;
            /*----------  VERIFICACION  ----------*/
            // VERIFICAMOS SI EL NUEVO ESTADO ES PROMOVIDO Y POSTERIORMENTE VERIFICAMOS SI EXISTE OTRA INSCRIPCION SIMILAR DEL MISMO NIVEL Y GRADO
            // PARA EVITAR DOBLE PROMOCION
            // $respuesta = $this->calcularNuevoEstado($idTramite);
            // if ($respuesta['nuevoEstado'] == 5) {
            //     $inscripcionSimilar = $this->get('funciones')->existeInscripcionSimilarAprobado($respuesta['idInscripcion']);
            //     if ($inscripcionSimilar) {
            //         $request->getSession()
            //                 ->getFlashBag()
            //                 ->add('errorTAMC', 'El trámite no puede ser aprobado, porque la solicitud modificará el estado de matrícula de la inscripción a promovido, pero el estudiante ya tiene otra inscripción del mismo nivel y grado con estado promovido o efectivo.');
            //         return $this->redirectToRoute('tramite_modificacion_calificaciones_recepcion_verifica_departamento', array('id'=>$idTramite, 'tipo'=>'idtramite'));
            //     }
            // }
            /*=====  End of VERIFICACION  ======*/
            $historial = $this->historial($idTramite);
            // dump($historial);
            $idInscripcion = null;
            $sieInscripcion = null;
            $gestionInscripcion = null;
            foreach ($historial as $h) {
                if($h['orden'] == 1){
                    $idInscripcion = $h['datos']['idInscripcion'];
                    $sieInscripcion = $h['datos']['sie'];
                    $gestionInscripcion = $h['datos']['gestion'];
                    $estadomatriculaid = $h['datos']['estadomatriculaid'];
                }
            }
            
            $aprueba = $request->get('aprueba');
            $checkSolicitud = $request->get('checkSolicitud');
            $checkFotocopiaci = $request->get('checkFotocopiaci');
            $checkInformeue = $request->get('checkInformeue');
            $checkInformeDistrito = $request->get('checkInformeDistrito');
            $observacion = mb_strtoupper($request->get('observacion'),'UTF-8');

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
            
            // VERIFICAMOS SI EXISTE EL ARCHIVO DE LA RESOLUCION ADMINISTRATIVA
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
                    $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/dobleProm/' . $sie;
                    if (!file_exists($directorio)) {
                        mkdir($directorio, 0777, true);
                    }

                    $archivador = $directorio.'/'.$new_name;
                    
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

            // VERIFICAMOS SI EXISTE EL ARCHIVO DEL INFORME
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
                    'aprueba'=>$aprueba,
                    'checkSolicitud'=>($checkSolicitud == null)?false:true,
                    'checkFotocopiaci'=>($checkFotocopiaci == null)?false:true,
                    'checkInformeue'=>($checkInformeue == null)?false:true,
                    'checkInformeDistrito'=>($checkInformeDistrito == null)?false:true,
                    'observacion'=>$observacion,
                    'resAdm'=>$resAdm,
                    'informe'=>$informe
                    
                ), JSON_UNESCAPED_UNICODE);
            // }
            

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

                // $this->modificarCalificacionesSIE($idTramite);
                if ($estadomatriculaid == 5){
                // $observacionins = 'Regularizado tramite MEP: '.$idTramite.', MATRICULA ANTERIOR ID: '.$estadomatriculaid;
                $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($idInscripcion);
                $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(105));
                $inscripcion->setObservacion('Regularizado tramite MEP: '.$idTramite.', MATRICULA ANTERIOR ID: '.$estadomatriculaid);
                $em->persist($inscripcion);
                $em->flush();   
                }

                /*----------  FIN MODIFICACION DE CALIFICACIONES EN EL SIE  ----------*/                
                

                // ARMAMOS EL ARRAY DE DATOS
                $datos = json_encode(array(
                    'observacion'=>$observacion
                ), JSON_UNESCAPED_UNICODE);

                // NUEVAMENTE ENVIAMOS EL TRAMITE
                $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                    $this->session->get('userId'),
                    $this->session->get('roluser'),
                    $flujoTipo,
                    $tareaSiguiente,
                    'institucioneducativa',
                    $sie,
                    $observacion,
                    '',
                    $idTramite,
                    $datos,
                    '',
                    $lugarTipo['lugarTipoIdDistrito']
                );

                $request->getSession()
                        ->getFlashBag()
                        ->add('exito', "El Tramite ". $idTramite ." fue aprobado y finalizado exitosamente");

            }else{
                $request->getSession()
                    ->getFlashBag()
                    ->add('exito', "El Tramite ". $idTramite ." fue enviado exitosamente");
            }

            $em->getConnection()->commit();

            return $this->redirectToRoute('wf_tramite_index', array('tipo'=>2));

        } catch (Exception $e) {
            $em->getConnection()->rollback();
        }
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
                        select * from tramite t 
                        inner join tramite_detalle td on td.tramite_id = t.id
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

}
