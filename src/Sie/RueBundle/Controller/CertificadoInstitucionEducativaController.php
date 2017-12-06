<?php

namespace Sie\RueBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Form\InstitucioneducativaType;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\AreaEspecialTipo;
use Sie\AppWebBundle\Entity\InstitucioneducativaAreaEspecialAutorizado;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InstitucioneducativaNivelAutorizado;
use Sie\AppWebBundle\Entity\CertificadoRue;

/**
 * Institucioneducativa controller.
 *
 */
class CertificadoInstitucionEducativaController extends Controller {

    /**
     * Lists all Institucioneducativa entities.
     *
     */
    public function indexAction(Request $request) {
    	$sesion = $request->getSession();
    	$id_usuario = $sesion->get('userId');
    	if (!isset($id_usuario)) {
    		return $this->redirect($this->generateUrl('login'));
    	}
    	 
    	try{
    		$em = $this->getDoctrine()->getManager();
    		$this->session = new Session();
    
    		/*
    		 * Lista de procesos de certificacion ordenada desc
    		 */
    
    		$query = $em->createQuery(
    				'SELECT fm
                FROM SieAppWebBundle:CertificadoRue fm
                ORDER BY fm.fechaRegistro DESC');
    		$entities = $query->getResult();

    	return $this->render('SieRueBundle:CertificadoInstitucionEducativa:index.html.twig', array('entities' => $entities));
    		
    	
    	} catch (Exception $ex) {
    
    	}
    }
    
    
    
    /**
     * Find the institucion educativa.
     *
     */    
    public function newAction(Request $request) {
    	 
    	$formulario = $this->createNewForm();
    	return $this->render('SieRueBundle:CertificadoInstitucionEducativa:new.html.twig', array('form' => $formulario->createView()));
    }
    
    private function createNewForm() {
//     	$entity = new Institucioneducativa();
//     	$em = $this->getDoctrine()->getManager();
    	
    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('certificado_rue_create'))
    	->add('fechaCorte', 'text', array('label' => 'Fecha de corte', 'required' => true, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'Seleccione una fecha...')))
    	->add('nroCertificadoInicio', 'text', array('label' => 'Nro. certificado inicio', 'required' => true, 'attr' => array('class' => 'form-control jnumbers', 'autocomplete' => 'off','maxlength'=>'15')))
    	->add('observacion', 'text', array('label' => 'Observaciones','required' => true,'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
    	->add('tipoCertificado', 'choice', array('label' => 'Tipo', 'choices'  => array('RUE'=>'RUE','FASE'=>'FASE'),  'required' => true  , 'expanded' => true,'attr' => array('class' => 'form-control')))
    	->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
    	
		return $form->getForm();
		
// 		->add('orgCurricularTipo', 'entity', array('label' => 'Org. Curricular','class' => 'SieAppWebBundle:OrgcurricularTipo', 'property' => 'orgcurricula', 'attr' => array('class' => 'form-control')))
		
    }
    
    public function createAction(Request $request) {
    	try {
    		$em = $this->getDoctrine()->getManager();
    		$form = $request->get('form');
//     		$cad = $form['tipoCertificado'] .': '. $form['observacion'];
			$em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_nivel_autorizado');")->execute();

    		$entity = new CertificadoRue();
    
    		
//     		$entity->setId($form['rue']);
    		$entity->setFechaCorte(new \DateTime($form['fechaCorte']));
//     		$entity->setFechaCorte(new \DateTime('now'));
    		$entity->setFechaRegistro(new \DateTime('now'));
    		$entity->setObservacion($form['tipoCertificado'] .': '. $form['observacion']);
			$entity->setNroCertificadoInicio($form['nroCertificadoInicio']);
			//dump($form);die;
    		$em->persist($entity);
        
    		$em->flush();
    		
    		return $this->redirect($this->generateUrl('certificado_rue'));
    		
//     		$this->get('session')->getFlashBag()->add('mensaje', 'La institucion educativa fue registrada correctamente');
//     		return $this->redirect($this->generateUrl('rue'));
    	} catch (Exception $ex) {
//     		$this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar la institucion educativa');
//     		return $this->redirect($this->generateUrl('rue_new'));
    	}
    }

    
    
    

    /**
     * Displays a form to edit an existing Institucioneducativa entity.
     *
     */
    public function editAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:CertificadoRue')->findOneById($request->get('idCertificado'));
        	if (!$entity) {
        		$idCertificado = $request->getSession()->get('idCertificado');
        		$entity = $em->getRepository('SieAppWebBundle:CertificadoRue')->findOneById($idCertificado);
        	}
        	else {
        		$request->getSession()->set('idCertificado', $entity->getId());
        		
        	}

        	if (!$entity) {
        		throw $this->createNotFoundException('No se puede encontrar proceso de certificados.');
        	}
        	 
//         	->setAction($this->generateUrl('certificado_rue_update'))
//         	->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
        	 
        	$form = $this->createFormBuilder()
        	->setAction($this->generateUrl('certificado_rue_update'))
        	->add('idCertificado', 'hidden', array('data' => $entity->getId()))
        	->add('fechaCorte', 'text', array('label' => 'Fecha de corte','data'=>$entity->getFechaCorte()->format('d-m-Y'), 'disabled' => true, 'attr' => array('class' => 'form-control')))
        	->add('nroCertificadoInicio', 'text', array('label' => 'Nro. certificado inicio', 'data' => $entity->getNroCertificadoInicio(), 'disabled' => true, 'attr' => array('class' => 'form-control jnumbers')))
        	->add('observacion', 'text', array('label' => 'Observaciones', 'data' => $entity->getObservacion(),'disabled' => true,'attr' => array('class' => 'form-control')))
        	->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
        	     
        	;
        	return $this->render('SieRueBundle:CertificadoInstitucionEducativa:edit.html.twig', array('entity' => $entity,'form' => $form->getForm()->createView()));
        	 
        	
    }


    public function updateAction(Request $request) {
    	$this->session = new Session();
    	$form = $request->get('form');
    	$em = $this->getDoctrine()->getManager();
    	$entity = $em->getRepository('SieAppWebBundle:CertificadoRue')->findOneById($form['idCertificado']);
    	$nro = $entity->getNroCertificadoInicio(); 
    	foreach ($entity->getCertificados() as $certificado) {
    		$certificado->setNroCertificado($nro);
    		$em->persist($certificado);
    		$nro = $nro + 1;
    	}
    	$em->flush();
    	$this->get('session')->getFlashBag()->add('mensaje', 'Los datos se guardaron correctamente');
    	return $this->redirect($this->generateUrl('certificado_rue_edit'));
    	 
    }
    

    public function deleteAction(Request $request) {
    	try{
        	 
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $certificado = $em->getRepository('SieAppWebBundle:CertificadoRueInstitucioneducativa')->find($request->get('idCertificadoRue'));
            $em->remove($certificado);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('deleteOk', 'Se elimino correctamente');
            return $this->redirect($this->generateUrl('certificado_rue_edit'));
        }catch(Exception $ex){
        	$em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('deleteError', $ex->getMessage());
            return $this->redirect($this->generateUrl('certificado_rue_edit'));
        }
    }

    

}
