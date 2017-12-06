<?php

namespace Sie\EspecialBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;

/**
 * areas especial controller.
 *
 */
class EstudianteNotasController extends Controller {

    public $session;
    public $idInstitucion;

    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Formulario para el registro de notas - modal
     */
    public function indexAction(Request $request) {
        try {
            $idInscripcionEspecial = $request->get('idInscripcion');
            $em = $this->getDoctrine()->getManager();
            $inscripcionEspecial = $em->getRepository('SieAppWebBundle:EstudianteInscripcionEspecial')->find($idInscripcionEspecial);

            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($inscripcionEspecial->getEstudianteInscripcion()->getId());
            $idInscripcion = $inscripcion->getId();

            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcion->getInstitucioneducativaCurso()->getId());
            $cursoEspecial = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBy(array('institucioneducativaCurso'=>$curso->getId()));

            $sie = $curso->getInstitucioneducativa()->getId();
            $gestion = $curso->getGestionTipo()->getId();
            $nivel = $curso->getNivelTipo()->getId();
            $grado = $curso->getgradoTipo()->getId();
            $gestionActual = $this->session->get('currentyear');

            $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);

            // Para gestiones pasadas restamos el operativo como regularizacion
            if($gestion < $gestionActual){
                $operativo = $operativo - 1;
            }
            //$operativo = 4;

            $notas = null;
            $vista = 1;
            switch ($cursoEspecial->getEspecialAreaTipo()->getId()) {
                case 1: // Auditiva
                        $notas = $this->get('notas')->regular($idInscripcion,$operativo);
                        $template = 'regular';
                        $actualizarMatricula = true;
                        break;
                case 2: // Visual
                        $notas = $this->get('notas')->especial_cualitativo($idInscripcion,$operativo);
                        $template = 'especialCualitativo';
                        $actualizarMatricula = false;
                        break;
                case 3: //Intelectual
                        switch ($nivel) {
                            case 401:
                                if($grado  <= 5){
                                    $notas = $this->get('notas')->especial_cualitativo($idInscripcion,$operativo);
                                    $template = 'especialCualitativo';
                                    $actualizarMatricula = false;
                                }
                                break;
                            
                            case 402:
                                if($grado <= 3){
                                    $notas = $this->get('notas')->especial_cualitativo($idInscripcion,$operativo);
                                    $template = 'especialCualitativo';
                                    $actualizarMatricula = false;
                                }
                                break;
                        }
                case 4: // Fisico -motora
                        break;
                case 5: //Multiple
                        break;
                case 6: //Dificultades en el aprendizaje
                        break;
                case 7: // Talento extraordinario
                        break;
                case 8: // Sordeceguera
                        break;
                case 9: // Problemas emocionales
                        break;
                case 100: // Modalidad Indirecta
                        break;
            }

            if($notas){
                $estadosMatricula = null;
                if($operativo >= 4){
                    $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(5,11)));
                }
                return $this->render('SieEspecialBundle:EstudianteNotas:notas.html.twig',array('notas'=>$notas,'inscripcion'=>$inscripcion,'vista'=>$vista,'template'=>$template,'actualizar'=>$actualizarMatricula,'operativo'=>$operativo,'estadosMatricula'=>$estadosMatricula));
            }else{
                return $this->render('SieEspecialBundle:EstudianteNotas:notas.html.twig',array('iesp'=>$inscripcionEspecial));
            }

        } catch (Exception $e) {
            return null;
        }
    }

    public function createUpdateAction(Request $request){
        try {
            
            $idInscripcion = $request->get('idInscripcion');

            // Registramos las notas
            $this->get('notas')->especialRegistro($request);

            // Verificamos si se actualizara el estado de matrícula
            if($request->get('actualizar') == true){
                $this->get('notas')->actualizarEstadoMatricula($idInscripcion);
            }

            if($request->get('operativo') >= 4){
                $em = $this->getDoctrine()->getManager();
                $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
                $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($request->get('nuevoEstadomatricula')));
                $em->flush();
            }

            return new JsonResponse(array('msg'=>'ok'));
        } catch (Exception $e) {
            return new JsonResponse(array('msg'=>'error'));
        }
    }

}
