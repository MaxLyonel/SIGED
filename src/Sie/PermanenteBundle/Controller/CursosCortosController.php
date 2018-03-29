<?php

namespace Sie\PermanenteBundle\Controller;

use Sie\AppWebBundle\Entity\InstitucioneducativaCursoCorto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
/**
 * EstudianteInscripcion controller.
 *
 */
class CursosCortosController extends Controller {

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
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $objUeducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getAlterCursosBySieGestSubPer($this->session->get('ie_id'), $this->session->get('ie_gestion'), $this->session->get('ie_subcea'), $this->session->get('ie_per_cod'));
        //dump($objUeducativa);die;
        $exist = true;
        $aInfoUnidadEductiva = array();
        if ($objUeducativa) {
            foreach ($objUeducativa as $uEducativa) {

                $sinfoUeducativa = serialize(array(
                    'ueducativaInfo' => array('ciclo' => $uEducativa['ciclo'], 'nivel' => $uEducativa['nivel'], 'grado' => $uEducativa['grado'], 'paralelo' => $uEducativa['paralelo'], 'turno' => $uEducativa['turno']),
                    'ueducativaInfoId' => array('nivelId' => $uEducativa['nivelId'], 'cicloId' => $uEducativa['cicloId'], 'gradoId' => $uEducativa['gradoId'], 'turnoId' => $uEducativa['turnoId'], 'paraleloId' => $uEducativa['paraleloId'], 'iecId' => $uEducativa['iecId'], 'setCodigo'=>$uEducativa['setCodigo'], 'satCodigo'=>$uEducativa['satCodigo'])
                ));

                $aInfoUnidadEductiva[$uEducativa['turno']][$uEducativa['ciclo']][$uEducativa['grado']][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa, 'nivelId' => $uEducativa['nivelId']);

            }
        } else {
            $message = 'No existe información del Centro de Educación Alternativa para la gestión seleccionada.';
            $this->addFlash('warninresult', $message);
            $exist = false;
        }

        $areatematica = $em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->findAll();
        $cursosCortos = $em->getRepository("SieAppWebBundle:PermanenteInstitucioneducativaCursocorto")->findAll();

        return $this->render('SiePermanenteBundle:CursosCortos:index.html.twig', array(
            'areatematica' => $areatematica,
            'cursosCortos' => $cursosCortos

        ));
    }
    public function newCursoCortoAction(Request $request){

        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $objUeducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getAlterCursosBySieGestSubPer($this->session->get('ie_id'), $this->session->get('ie_gestion'), $this->session->get('ie_subcea'), $this->session->get('ie_per_cod'));
        //dump($objUeducativa);die;
        $exist = true;
        $aInfoUnidadEductiva = array();
        if ($objUeducativa) {
            foreach ($objUeducativa as $uEducativa) {

                $sinfoUeducativa = serialize(array(
                    'ueducativaInfo' => array('ciclo' => $uEducativa['ciclo'], 'nivel' => $uEducativa['nivel'], 'grado' => $uEducativa['grado'], 'paralelo' => $uEducativa['paralelo'], 'turno' => $uEducativa['turno']),
                    'ueducativaInfoId' => array('nivelId' => $uEducativa['nivelId'], 'cicloId' => $uEducativa['cicloId'], 'gradoId' => $uEducativa['gradoId'], 'turnoId' => $uEducativa['turnoId'], 'paraleloId' => $uEducativa['paraleloId'], 'iecId' => $uEducativa['iecId'], 'setCodigo'=>$uEducativa['setCodigo'], 'satCodigo'=>$uEducativa['satCodigo'])
                ));

                $aInfoUnidadEductiva[$uEducativa['turno']][$uEducativa['ciclo']][$uEducativa['grado']][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa, 'nivelId' => $uEducativa['nivelId']);

            }
        } else {
            $message = 'No existe información del Centro de Educación Alternativa para la gestión seleccionada.';
            $this->addFlash('warninresult', $message);
            $exist = false;
        }
        $areatematica = $em->getRepository('SieAppWebBundle:AreaTematicaTipo')->findAll();
        $cursosCortos = $em->getRepository("SieAppWebBundle:InstitucioneducativaCursocorto")->findAll();

        return $this->render('SiePermanenteBundle:CursosCortos:new.html.twig', array(
            'areatematica' => $areatematica,
            'cursosCortos' => $cursosCortos

        ));

    }


    public function editCursoCortoAction(Request $request){

        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $objUeducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getAlterCursosBySieGestSubPer($this->session->get('ie_id'), $this->session->get('ie_gestion'), $this->session->get('ie_subcea'), $this->session->get('ie_per_cod'));
        //dump($objUeducativa);die;
        $exist = true;
        $aInfoUnidadEductiva = array();
        if ($objUeducativa) {
            foreach ($objUeducativa as $uEducativa) {

                $sinfoUeducativa = serialize(array(
                    'ueducativaInfo' => array('ciclo' => $uEducativa['ciclo'], 'nivel' => $uEducativa['nivel'], 'grado' => $uEducativa['grado'], 'paralelo' => $uEducativa['paralelo'], 'turno' => $uEducativa['turno']),
                    'ueducativaInfoId' => array('nivelId' => $uEducativa['nivelId'], 'cicloId' => $uEducativa['cicloId'], 'gradoId' => $uEducativa['gradoId'], 'turnoId' => $uEducativa['turnoId'], 'paraleloId' => $uEducativa['paraleloId'], 'iecId' => $uEducativa['iecId'], 'setCodigo'=>$uEducativa['setCodigo'], 'satCodigo'=>$uEducativa['satCodigo'])
                ));

                $aInfoUnidadEductiva[$uEducativa['turno']][$uEducativa['ciclo']][$uEducativa['grado']][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa, 'nivelId' => $uEducativa['nivelId']);

            }
        } else {
            $message = 'No existe información del Centro de Educación Alternativa para la gestión seleccionada.';
            $this->addFlash('warninresult', $message);
            $exist = false;
        }
        $areatematica = $em->getRepository('SieAppWebBundle:AreaTematicaTipo')->findAll();
        $cursosCortos = $em->getRepository("SieAppWebBundle:InstitucioneducativaCursocorto")->findAll();

        return $this->render('SiePermanenteBundle:CursosCortos:new.html.twig', array(
            'areatematica' => $areatematica,
            'cursosCortos' => $cursosCortos

        ));

    }

    public function deleteCursoCortoAction(Request $request){

        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $objUeducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getAlterCursosBySieGestSubPer($this->session->get('ie_id'), $this->session->get('ie_gestion'), $this->session->get('ie_subcea'), $this->session->get('ie_per_cod'));
        //dump($objUeducativa);die;
        $exist = true;
        $aInfoUnidadEductiva = array();
        if ($objUeducativa) {
            foreach ($objUeducativa as $uEducativa) {

                $sinfoUeducativa = serialize(array(
                    'ueducativaInfo' => array('ciclo' => $uEducativa['ciclo'], 'nivel' => $uEducativa['nivel'], 'grado' => $uEducativa['grado'], 'paralelo' => $uEducativa['paralelo'], 'turno' => $uEducativa['turno']),
                    'ueducativaInfoId' => array('nivelId' => $uEducativa['nivelId'], 'cicloId' => $uEducativa['cicloId'], 'gradoId' => $uEducativa['gradoId'], 'turnoId' => $uEducativa['turnoId'], 'paraleloId' => $uEducativa['paraleloId'], 'iecId' => $uEducativa['iecId'], 'setCodigo'=>$uEducativa['setCodigo'], 'satCodigo'=>$uEducativa['satCodigo'])
                ));

                $aInfoUnidadEductiva[$uEducativa['turno']][$uEducativa['ciclo']][$uEducativa['grado']][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa, 'nivelId' => $uEducativa['nivelId']);

            }
        } else {
            $message = 'No existe información del Centro de Educación Alternativa para la gestión seleccionada.';
            $this->addFlash('warninresult', $message);
            $exist = false;
        }
        $areatematica = $em->getRepository('SieAppWebBundle:PermanenteAreaTematicaTipo')->findAll();
        $cursosCortos = $em->getRepository("SieAppWebBundle:PermanenteInstitucioneducativaCursoCorto")->findAll();

        return $this->render('SiePermanenteBundle:CursosCortos:new.html.twig', array(
            'areatematica' => $areatematica,
            'cursosCortos' => $cursosCortos

        ));

    }


}
