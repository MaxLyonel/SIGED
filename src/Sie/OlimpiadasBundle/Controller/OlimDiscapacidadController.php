<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\OlimDiscapacidadTipo;
use Symfony\Component\Form\Extension\Core\Type\TextType;


/**
 * OlimArchivo controller.
 *
 */
class OlimDiscapacidadController extends Controller {

	public function indexAction(){
		
		$em = $this->getDoctrine()->getManager();
		$discapacidad = $em->getRepository('SieAppWebBundle:OlimDiscapacidadTipo')->findAll();
		//dump($discapacidad);die;
		return $this->render('SieOlimpiadasBundle:OlimDiscapacidad:index.html.twig',array(
			'discapacidad'=>$discapacidad	
		));
	}
	public function nuevoAction(){
				
		$em = $this->getDoctrine()->getManager();
		//dump($discapacidad);die;
		$form=$this->createFormBuilder()
		->setAction($this->generateUrl('olimdiscapacidad_guardar'))
		->add('discapacidad','text', array('label'=>'Discapacidad','required'=>true) )
		->add('guardar', 'submit', array('label'=>'Guardar','attr'=>array('class'=>'btn btn-warning btn-xs')))
		->getForm()
		;
		return $this->render('SieOlimpiadasBundle:OlimDiscapacidad:nuevoDiscapacidad.html.twig',array(
			'form'=>$form->createView()	
		));
	}
	public function guardarAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		//dump($request);die;

        $form = $request->get('form');
        $discapacidad = $form['discapacidad'];
        $query = $em->getConnection()->prepare('
		select * from olim_discapacidad_tipo where discapacidad =:discapacidad');
		$query->bindValue(':discapacidad', $discapacidad);
        $query->execute();
        $disc = $query->fetchAll();
        //dump($disc);die;
        // $reinicio = true;
		//$query = $em->getConnection()->prepare('
		//select id from olim_discapacidad_tipo order  by id desc limit 1');
		//$query->execute();
		//$id=$query->fetch();
		//dump($id);die;
		$em->getConnection()->beginTransaction();
        if ($disc == null || $disc == "") {
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_discapacidad_tipo');")->execute();
            $discapacidadTipo = new OlimDiscapacidadTipo();
			$discapacidadTipo->setDiscapacidad($form['discapacidad']);
			//$discapacidadTipo->setId($id++);
            //dump($discapacidadTipo);die;
            $em->persist($discapacidadTipo);
            $em->flush();
            $em->getConnection()->commit();
            $mensaje = 'La discapacidad ' . $form['discapacidad'] . ' fué registrada con éxito';
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
            return $this->redirect($this->generateUrl('olimdiscapacidad_index'));
        } else {
            $em->getConnection()->rollback();
            $mensaje = 'La discapacidad ' . $form['discapacidad'] . ' no fué registrada';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);
            return $this->redirect($this->generateUrl('olimdiscapacidad_index'));
        }
	}
	public function editarAction(Request $request)
    {
		//dump($request);die;
        $em = $this->getDoctrine()->getManager();
    	$em->getConnection()->beginTransaction();
		$id = $request->get('disc');
		//dump($id);die;
    	$discapacidad =$em->getRepository('SieAppWebBundle:OlimDiscapacidadTipo')->find($id);
      	$dics=$discapacidad->getDiscapacidad();
		//dump($disc);die;
	    $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('olimdiscapacidad_edit'))
        ->add('id', 'hidden', array('data' => $id))
        ->add('discapacidad', 'text', array('required' => true, 'data' =>$dics ,'attr' => array('class' => 'form-control', 'enabled' => true)))
        ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary', 'disbled' => true)))
        ->getForm();
    return $this->render('SieOlimpiadasBundle:OlimDiscapacidad:editarDiscapacidad.html.twig', array(

        'form' => $form->createView()

    ));
}
public function editAction(Request $request)
{
	//dump($request);die;
	$em = $this->getDoctrine()->getManager();
    $form = $request->get('form');
    $discapacidad=$form['discapacidad'];
    $id=$form['id'];
    //
    //dump($form);die;
    $em->getConnection()->beginTransaction();
    if ($discapacidad != null || $discapacidad != "") {
        $disc =$em->getRepository('SieAppWebBundle:OlimDiscapacidadTipo')->find($id);
        $disc->setDiscapacidad($discapacidad);
        //  dump($cursocorto);die;
        $em->persist($disc);
        $em->flush();
        $em->getConnection()->commit();
        $mensaje = 'La discapacidad ' . $disc->getDiscapacidad() . ' fue actualizada con éxito';
        $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        return $this->redirect($this->generateUrl('olimdiscapacidad_index'));
    } else {
        $em->getConnection()->rollback();
        $mensaje = 'La discapacidad ' . $form['discapacidad'] . ' no fué actualizada';
        $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);
        return $this->redirect($this->generateUrl('olimdiscapacidad_index'));
    }
}



	public function eliminarAction(Request $request, $id)
    {
        $em=$this->getDoctrine()->getManager();
        $discapacidad=$em->getRepository('SieAppWebBundle:OlimDiscapacidadTipo')->find($id);
        if(!isset($discapacidad)){
            throw $this->createNotFoundation('No existe el registro');
        }
        $em->remove($discapacidad);
        $em->flush();
        $mensaje = 'La discapacidad ' . $discapacidad->getDiscapacidad() . ' fue eliminado con exito';
        $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        return $this->redirectToRoute('olimdiscapacidad_index');
        //return new REsponse('ID: ' . $id);
    }

	
}