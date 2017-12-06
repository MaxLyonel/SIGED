<?php

namespace Sie\JuegosBundle\Controller;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sie\AppWebBundle\Entity\EstudianteInscripcionJuegos;

class SeguimientoController extends Controller {

    public $session;
    public $idInstitucion;
    private $nivelId;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        //$this->aCursos = $this->fillCursos();
    }

    public function indexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select jds.id, dt.departamento
            , jds.estudiante_cantidad_llegada , jds.estudiante_cantidad_salida
            , jds.delegado_cantidad_llegada, jds.delegado_cantidad_salida 
            from juegos_datos_seguimiento as jds 
            inner join departamento_tipo as dt on dt.id = jds.departamento_tipo_id
            where jds.nivel_tipo_id = 13 and jds.gestion_tipo_id = 2017
            order by dt.id
        ");
        $query->execute();
        $verInsPru = $query->fetchAll();

        return $this->render('SieJuegosBundle:Seguimiento:index.html.twig', array(
                    'entity' => $verInsPru
        ));
    }


        /**
     * get request
     * @param type $request, $fase
     * return list of students
     */
    public function saveAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d H:i:s'));
        if ($request->isMethod('POST')) {
            // Recupera datos del formulario            
            $id = $request->get('id');         
            $estSalida = $request->get('est_salida_'.$id);         
            $estRetorno = $request->get('est_retorno_'.$id);         
            $delSalida = $request->get('del_salida_'.$id);         
            $delRetorno = $request->get('del_retorno_'.$id);

            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            try { 
                $entidadJuegosDatosSeguimiento = $em->getRepository('SieAppWebBundle:JuegosDatosSeguimiento')->findOneBy(array('id' => $id)); 
                $entidadJuegosDatosSeguimiento->setEstudianteCantidadSalida($estSalida);
                $entidadJuegosDatosSeguimiento->setEstudianteCantidadLlegada($estRetorno);
                $entidadJuegosDatosSeguimiento->setDelegadoCantidadSalida($delSalida);
                $entidadJuegosDatosSeguimiento->setDelegadoCantidadLlegada($delRetorno);
                $entidadJuegosDatosSeguimiento->setFechaModificacion($fechaActual);
                $entidadJuegosDatosSeguimiento->setUsuarioModificacion('13833121');

                $em->persist($entidadJuegosDatosSeguimiento);
                $em->flush();

                $em->getConnection()->commit(); 
                $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => "Registro guardado correctamente"));
            } catch (\Doctrine\ORM\NoResultException $exc) {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
            }

            return $this->redirectToRoute('sie_juegos_seguimiento_index');
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Envío de datos incorrecto, intente nuevamente'));  
            return $this->redirect($this->generateUrl('sie_juegos_seguimiento_index'));
        }
    }

    /**
     * get estudiante
     * @param type $inscripcionEstudiante
     * return array validacion
     */
    public function fichaPersonalEstudiante(Request $request){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select e.paterno, e.materno, e.nombre, e.carnet_identidad, e.complemento, ie.id as sie, ie.institucioneducativa, to_char(e.fecha_nacimiento, 'yyyy') as gestion_nacimiento
            from estudiante_inscripcion as ei
            inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
            inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
            inner join estudiante as e on e.id = ei.estudiante_id
            where ei.id = ". $inscripcionEstudiante ."
        ");
        $query->execute();
        $verInsPru = $query->fetchAll();

        if (count($verInsPru) > 0){
            return array('gestion_nacimiento'=>$verInsPru[0]["gestion_nacimiento"],'sie'=>$verInsPru[0]["sie"],'ue'=>$verInsPru[0]["institucioneducativa"],'nombre'=>$verInsPru[0]["nombre"].' '.$verInsPru[0]["paterno"].' '.$verInsPru[0]["materno"], 'ci'=>$verInsPru[0]["carnet_identidad"].(($verInsPru[0]["complemento"] == "") ? "-".$verInsPru[0]["complemento"] : ""));
        } else {
            return '';
        }
    }
}
