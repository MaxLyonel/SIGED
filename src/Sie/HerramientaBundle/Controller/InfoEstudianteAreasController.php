<?php

namespace Sie\HerramientaBundle\Controller;

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
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\RegistroConsolidacion;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * EstudianteInscripcion controller.
 *
 */
class InfoEstudianteAreasController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    public function indexAction(Request $request){
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $em = $this->getDoctrine()->getManager();
        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecId);
        $operativo = $this->get('funciones')->obtenerOperativo($curso->getInstitucioneducativa()->getId(),$curso->getGestionTipo()->getId());
        $areas = $this->get('areas')->getAreas($iecId);
        if($areas){
            return $this->render('SieHerramientaBundle:InfoEstudianteAreas:index.html.twig',array(
                'infoUe'=>$infoUe,
                'curso'=>$curso,
                'operativo'=>$operativo,
                'areas'=>$areas
            ));
        }
        return $this->render('SieHerramientaBundle:InfoEstudianteAreas:index.html.twig',array(
                'areas'=>null
        ));
    }

    public function newAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $idAsignatura = $request->get('ida');
            $idCurso = $request->get('idCurso');
            //dump($idAsignatura .' '.$idCurso);die;

            $new = $this->get('areas')->nuevo($idCurso,$idAsignatura);
            
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
            $sie = $curso->getInstitucioneducativa()->getId();
            $gestion = $curso->getGestionTipo()->getId();

            $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);

            $areas = $this->get('areas')->getAreas($idCurso);

            $em->getConnection()->commit();
            if($areas){
                return $this->render('SieHerramientaBundle:InfoEstudianteAreas:index.html.twig',array(
                    'curso'=>$curso,
                    'operativo'=>$operativo,
                    'areas'=>$areas
                ));
            }
        } catch (Exception $e) {
            $em->getConnection()->rollback();
        }        
    }

    /**
     * [deleteAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function deleteAction(Request $request){
        try {
            $idCursoOferta = $request->get('idco');
        
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($idCursoOferta);
            //dump($idCursoOferta);die;
            // Mostramos nuevamente las areas del curso
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($cursoOferta->getInsitucioneducativaCurso()->getId());

            $operativo = $this->get('funciones')->obtenerOperativo($curso->getInstitucioneducativa()->getId(),$curso->getGestionTipo()->getId());

            // Eliminamos el registro de curso oferta
            $delete = $this->get('areas')->delete($idCursoOferta);


            $areas = $this->get('areas')->getAreas($curso->getId());
            $em->getConnection()->commit();
            if($areas){
                return $this->render('SieHerramientaBundle:InfoEstudianteAreas:index.html.twig',array(
                    'curso'=>$curso,
                    'operativo'=>$operativo,
                    'areas'=>$areas
                ));
            }
            return null;   
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            return null;
        }
    }

    // Listado y asignacion de maestros

    public function maestrosAction(Request $request){
        $idCursoOferta = $request->get('idco');
        
        $maestros = $this->get('maestroAsignacion')->listar($idCursoOferta);
        
        return $this->render('SieHerramientaBundle:InfoEstudianteAreas:maestros.html.twig',array(
            'maestros'=>$maestros,
            'ieco'=>$idCursoOferta
        ));

    }

    public function maestrosAsignarAction(Request $request){
        
        $idCursoOferta = $request->get('cursoOfertaId');
        $response1 = $this->get('maestroAsignacion')->asignar($request);
        $response = new JsonResponse();
        return $response->setData(array('ieco'=>$idCursoOferta));
    }
}
