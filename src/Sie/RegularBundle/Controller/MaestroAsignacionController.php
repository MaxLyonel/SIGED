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

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que despliega el fomulario para registrar o modificar la asignacion de un maestro
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function asignarMaestroMateriaAction(Request $request) {
        $validacionProcesoId = $request->get('vp_id');
        $maestroInscripcionId = $request->get('id');
        $gestion = $request->get('gestion');
  
        $em = $this->getDoctrine()->getManager();
        $maestroInscripcionEntidad = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($maestroInscripcionId);
        dump($maestroInscripcionId);
        dump($maestroInscripcionEntidad);
        if($validacionProcesoId > 0){
            $validacionProcesoEntidad = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($validacionProcesoId);
        } else {

        }
        $arr = array(0 => array('id' => 1, 'name' => 'red', 'detail' => 'color rojo'), 1 => array('id' => 2, 'name' => 'blue', 'detail' => 'color azul'), 2 => array('id' => 3, 'name' => 'black', 'detail' => 'color negro'));
        $keys = "";
        foreach($arr[0] as $key => $value){
            $keys = $keys ."-". $key;
        }
        dump($keys);die;
        // $maestros = $this->get('maestroAsignacion')->listar($idCursoOferta);

        return $this->render('SieRegularBundle:MaestroAsignacion:asignacionMateriaIndex.html.twig',array(

        ));
    }
}
