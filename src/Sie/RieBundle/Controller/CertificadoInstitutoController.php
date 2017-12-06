<?php
namespace Sie\RieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Form\InstitucioneducativaType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\CertificadoRue;

/**
 * Institucioneducativa controller.
 *
 */
class CertificadoInstitutoController extends Controller {

    /**
     * Listado de certificados
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
    
    		$query = $em->createQuery('SELECT fm
										 FROM SieAppWebBundle:CertificadoRue fm
									    WHERE fm.observacion LIKE :obs
									 ORDER BY fm.fechaRegistro DESC')
									 ->setParameter('obs', 'RIE%');
    		$entities = $query->getResult();

    		return $this->render('SieRieBundle:CertificadoInstituto:index.html.twig', array('entities' => $entities));
    	
    	} catch (Exception $ex) {
    
    	}
    }
    
    /*
     * Mostrando formulario de adición para certificación
     */    
    public function newAction(Request $request) {
    	$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('certificado_rie_create'))
    	->add('fechaCorte', 'text', array('label' => 'Fecha de corte', 'required' => true, 'attr' => array('class' => 'datepicker form-control', 'placeholder' => 'Seleccione una fecha...')))
    	->add('nroCertificadoInicio', 'text', array('label' => 'Nro. certificado inicio', 'required' => true, 'attr' => array('class' => 'form-control jnumbers', 'autocomplete' => 'off','maxlength'=>'15')))
		->add('observacion', 'text', array('label' => 'Observaciones','required' => true,'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
    	->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
    	return $this->render('SieRieBundle:CertificadoInstituto:new.html.twig', array('form' => $form->getForm()->createView()));
	}
	
    /*
     * Guardando datos del nuevo formulario 
     */       
    public function createAction(Request $request) {
    	try {
    		$em = $this->getDoctrine()->getManager();
    		$form = $request->get('form');
			$em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_nivel_autorizado');")->execute();
    		$entity = new CertificadoRue();
    		$entity->setFechaCorte(new \DateTime($form['fechaCorte']));
    		$entity->setFechaRegistro(new \DateTime('now'));
    		$entity->setObservacion('RIE: '.$form['observacion']);
			$entity->setNroCertificadoInicio($form['nroCertificadoInicio']);
    		$em->persist($entity);
    		$em->flush();
    		
    		return $this->redirect($this->generateUrl('certificado_rie'));
    	} catch (Exception $ex) {
     		$this->get('session')->getFlashBag()->add('mensaje', 'Error al registrar los datos de certificacion');
     		return $this->redirect($this->generateUrl('certificado_rie_new'));
    	}
    }

    /*
     * Editando los datos de certificación
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
        	 
        	$form = $this->createFormBuilder()
        	->setAction($this->generateUrl('certificado_rie_update'))
        	->add('idCertificado', 'hidden', array('data' => $entity->getId()))
        	->add('fechaCorte', 'text', array('label' => 'Fecha de corte','data'=>$entity->getFechaCorte()->format('d-m-Y'), 'disabled' => true, 'attr' => array('class' => 'form-control')))
        	->add('nroCertificadoInicio', 'text', array('label' => 'Nro. certificado inicio', 'data' => $entity->getNroCertificadoInicio(), 'disabled' => true, 'attr' => array('class' => 'form-control jnumbers')))
        	->add('observacion', 'text', array('label' => 'Observaciones', 'data' => $entity->getObservacion(),'disabled' => true,'attr' => array('class' => 'form-control')))
			->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')));
        	return $this->render('SieRieBundle:CertificadoInstituto:edit.html.twig', array('entity' => $entity,'form' => $form->getForm()->createView()));
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
    	return $this->redirect($this->generateUrl('certificado_rie_edit'));
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
