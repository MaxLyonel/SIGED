<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro;

/**
 * MaestroAsignacion controller.
 *
 */
class MaestroAsignacionController extends Controller {

    public $session;
    public $idInstitucion;

    public function __construct() {
        $this->session = new Session();
    }

    /**
     */
    public function indexAction(Request $request) {
        if ($request->getMethod() == 'POST'){
            $form = $request->get('form');
            $idCursoOferta = $form['llave'];
            $idValidacion = $form['idDetalle'];
            $this->session->set('idCursoOferta',$idCursoOferta);
            $this->session->set('idValidacion',$idValidacion);
        }else{
            $idCursoOferta = $this->session->get('idCursoOferta');
            $idValidacion = $this->session->get('idValidacion');
        }
        $em = $this->getDoctrine()->getManager();
        $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($idCursoOferta);
        $maestros = $this->get('maestroAsignacion')->listar($idCursoOferta);

        return $this->render('SieRegularBundle:MaestroAsignacion:index.html.twig',array(
            'cursoOferta'=>$cursoOferta,
            'maestros'=>$maestros,
            'idValidacion'=>$idValidacion
        ));
    }

    public function asignarMaestroAction(Request $request){
        $idCursoOferta = $request->get('cursoOfertaId');
        $idValidacion = $request->get('idValidacion');

        $response = $this->get('maestroAsignacion')->asignar($request);

        // Actualizamos el regitro de validación_proceso

        $em = $this->getDoctrine()->getManager();
        $regValidacion = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($idValidacion);
        $regValidacion->setEsActivo(true);
        $em->flush();

        return $this->redirectToRoute('maestroAsignacion');
    }
}
