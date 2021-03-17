<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoModalidadAtencion;

/**
 * ModalidadCurso controller.
 *
 */
class ModalidadCursoController extends Controller {

    public $session;
    public $month;
    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request){

    	$infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        $cursoId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($cursoId);
        if(!$entity){
        	$entity = new InstitucioneducativaCurso();
        }
        
        $objInstitucioneducativaCursoModalidadAtencion = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoModalidadAtencion')->findby(array('institucioneducativaCurso'=>$cursoId, 'mes'=>getDate()['mon']));
        $arrModalidades = array('option_1'=>false,'option_2'=>false,'option_3'=>false);
        if($objInstitucioneducativaCursoModalidadAtencion){
            foreach($objInstitucioneducativaCursoModalidadAtencion as $value){
                $arrModalidades['option_'.$value->getModalidadAtencionTipo()->getId()]=true;
            }
        }

        $form = $this->createFormCurso($entity);
		$form->handleRequest($request);

    	return $this->render('SieHerramientaBundle:ModalidadCurso:index.html.twig', array(
    		'curso' => $entity,
            'form' => $form->createView(),
            'arrModalidades'=>$arrModalidades,
    		'arrDate' => array('mon'=>getDate()['mon'], 'today' => date("M/Y"))
    	));
    }

    private function createFormCurso($entity){
    	$form = $this->createFormBuilder($entity)
    				->add('id','hidden',array('data'=>$entity->getId()))                    
                        ->getForm();
    	return $form;
    }

    public function saveAction(Request $request){
    	$em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        
        $objIecurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($form['id']);
    	if($objIecurso){
    		
            $objInstitucioneducativaCursoModalidadAtencion = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoModalidadAtencion')->findby(array('institucioneducativaCurso'=>$form['id'], 'mes'=>$form['mon']));
            if($objInstitucioneducativaCursoModalidadAtencion){
                foreach ($objInstitucioneducativaCursoModalidadAtencion as $value){
                    $em->remove($value);
                }
                $em->flush();
            }
            $arrModalidades = $form['opcion'];
            if($arrModalidades !==NULL && count($arrModalidades)>0){
                foreach ($arrModalidades as $value){
                        $value=filter_var($value,FILTER_VALIDATE_INT);
                        $modalidadAtencion= new InstitucioneducativaCursoModalidadAtencion();
                        $modalidadAtencion->setFechaRegistro(new \DateTime());
                        $modalidadAtencion->setObservacion('no');
                        $modalidadAtencion->setMes($form['mon']);
                        $modalidadAtencion->setInstitucioneducativaCurso($objIecurso);
                        $modalidadAtencion->setModalidadAtencionTipo($em->getRepository('SieAppWebBundle:ModalidadAtencionTipo')->find($value));
                        $em->persist($modalidadAtencion);
                        $tipos[]=$value;
                    
                }
                $em->flush();
            }
       
    		$em->flush();

    		$data = array(
    			'msg'=> 'Datos registrados correctamente',
    			'status' => 201
    		);
    	}else{
    		$data = array(
    			'msg' => 'Los datos ingresados son incorrectos',
    			'status' => 500
    		);
    	}

    	$response = new JsonResponse();

    	return $response->setData($data);
    }

}
