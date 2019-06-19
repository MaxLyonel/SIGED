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

                // OBTENEMOS EL ID DE INSCRIPCION
                $idInscripcion = $request->get('idInscripcion');
                $em = $this->getDoctrine()->getManager();
                $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
                $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
                $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

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

                // OBTENEMOS LA INFORMACION DEL FORMULARIO
                $flujoTipo = $request->get('flujoTipo');
                $notas = json_decode($request->get('notas'),true);
                $notasCualitativas = json_decode($request->get('notasCualitativas'),true);
                $justificacion = mb_strtoupper($request->get('justificacion'),'utf-8');

                $data = array(
                    'idInscripcion'=> $idInscripcion,
                    'flujoTipo'=>$flujoTipo,
                    'notas'=> $notas,
                    'notasCualitativas'=>$notasCualitativas,
                    'justificacion'=>$justificacion,
                    'archivo'=>$archivo
                );

                // buscamos un aolicitud previa
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


                $data = json_encode($data);

                // OBTENEMOS EL ID DE LA TAREA
                $tarea = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array(
                    'flujoTipo'=>$flujoTipo,
                    'orden'=>1
                ));

                $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);
// dump($lugarTipo);die;
                $tipoTramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs'=>'AMN'));

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

                // dump($registroTramite);die;

                $response->setStatusCode(200);
                $response->setData($registroTramite);
                return $response;
            }
            $response->setStatusCode(500);
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

    public function recepcionVerificaDistritoAction(Request $request){
        $idTramite = $request->get('id');
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find();
        dump($request);die;
    }
    

}
