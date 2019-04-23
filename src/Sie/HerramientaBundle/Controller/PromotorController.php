<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class PromotorController extends Controller{
    public $session;
     /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }
    
    public function indexAction(Request $reuest){
       //get the ini values by sie and current year

        $arrCondition = array(
                                'institucioneducativa' => $this->session->get('ie_id'),
                                'gestionTipo'          => $this->session->get('currentyear')
                            );


    // dump($arrCondition);die;
        // check if the user is a director
        if($this->session->get('ie_id')>0){
            return $this->redirectToRoute('aca_promotor_promotor_listPromotor', $arrCondition);
        }else{
            return $this->redirectToRoute('aca_promotor_promotor_selectsie');
        }     
        
    }

    public function selectsieAction(Request  $request){

        //look for the all promotores to this UE
        return $this->render('SieHerramientaBundle:Promotor:selectsie.html.twig', array(
                'form' => $this->formFindPromotor()->createView(),
            ));    

    }

    private function formFindPromotor(){
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('aca_promotor_promotor_listPromotor'))
            ->setMethod('POST')
            ->add('sie', 'text', array('attr'=>array('value'=>'', 'maxlength'=>8, 'class'=>'form-control')))
            ->add('gestion', 'choice', array('mapped' => false, 'label' => 'Gestion', 'choices' => array($this->session->get('currentyear')=>$this->session->get('currentyear')), 'attr' => array('class'=>'form-control')))
            ->add('sendData', 'submit', array('label'=>'Continuar','attr'=>array('class'=>'btn btn-success')))
            ->getForm()
        ;
    }

    public function listPromotorAction(Request  $request){

        //look for the all promotores to this UE

        return $this->render('SieHerramientaBundle:Promotor:listPromotor.html.twig', array(
                // ...
            ));    

    }

    public function findAction(){

        return $this->render('SieHerramientaBundle:Promotor:find.html.twig', array(
                // ...
            ));    
    }

    public function registerAction(){

        return $this->render('SieHerramientaBundle:Promotor:register.html.twig', array(
                // ...
            ));    
    }

}
