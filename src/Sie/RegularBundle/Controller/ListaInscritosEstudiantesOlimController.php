<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\Estudiante;
use Symfony\Component\HttpFoundation\Session\Session;

class ListaInscritosEstudiantesOlimController extends Controller{
	public function __construct() {
      $this->session = new Session();
  }

    public function indexAction(){

    $em = $this->getDoctrine()->getManager();
      //check if the user is logged
      $id_usuario = $this->session->get('userId');
      if (!isset($id_usuario)) {
          return $this->redirect($this->generateUrl('login'));
      }
      $coddis = '';
       if($this->session->get('roluser') == 10){
            //call find sie template
            $modeview = 0;
            $query = $em->getConnection()->prepare('select * from get_usuario_lugar(:userid)');
            $query->bindValue(':userid', $this->session->get('userId'));
            $query->execute();
            $dataDistrito = $query->fetchAll();
            // dump($dataDistrito[0]['get_usuario_lugar']);die;
            $arrDataDistrito = explode('|', $dataDistrito[0]['get_usuario_lugar']);
            // dump($arrDataDistrito);die;
            $coddis = $arrDataDistrito[0];
        }else{
            $modeview = 1;
        }
        
        return $this->render('SieRegularBundle:ListaInscritosEstudiantesOlim:index.html.twig', array(
                'form' => $this->createSearchForm($coddis)->createView(),
                'modeview' => $modeview
            ));    
    }

    /**
     * Creates a form to search the users of student selected
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchForm($coddis) {
        $form = $this->createFormBuilder()
                // ->setAction($this->generateUrl('history_new_inscription_index'))
                ->add('codigoDistrito', 'text', array('label' => 'Código Distrito', 'invalid_message' => 'campo obligatorio', 'data'=> $coddis, 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 8, 'required' => true, 'class' => 'form-control')))
                ->add('gestion', 'hidden', array('label' => 'Gestion', 'invalid_message' => 'campo obligatorio','data'=> $this->session->get('currentyear'), 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 4, 'required' => true, 'class' => 'form-control')))
                ->add('buscar', 'button', array('label' => 'Generar', 'attr'=> array('onclick'=>'lookdistrito()')))
                ->getForm();
        return $form;
    }

    public function lookdistritoAction(Request $request){

    	//get the send values
    	$form = $request->get('form');

    	 return $this->render('SieRegularBundle:ListaInscritosEstudiantesOlim:lookdistrito.html.twig', array(
               'data' => $form,
            ));    
    }

}
