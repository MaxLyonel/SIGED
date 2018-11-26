<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

class RegularizationCUTController extends Controller{
    
    private $session;
    
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        
    }



    public function indexAction(){

        return $this->render('SieHerramientaBundle:RegularizationCUT:index.html.twig', array(
            'form' => $this->createSearForm()->createView(),
                // ...
            ));    

    }

    private function createSearForm(){
        return $this->createFormBuilder()
            ->add('codigoRude', 'text', array('mapped' => false, 'label' => 'Rude', 'required' => true, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9a-zA-Z\sñÑ]{10,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'choice', array('mapped' => false, 'label' => 'Gestion', 'choices' => array($this->session->get('currentyear')=>$this->session->get('currentyear')), 'attr' => array('class' => 'form-control')))
                ->add('buscar', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary', 'onclick'=>'gethistory();')))
                ->getForm();
    }



    public function historyAction(Request $request){
        
        //get send data
        $form = $request->get('form');
        //get current inscription
        $historyStudentInscription = $this->get('funciones')->getCurrentInscriptionStudentByRude($form);
        
        return $this->render('SieHerramientaBundle:RegularizationCUT:history.html.twig', array(
            'arrhistory' => $historyStudentInscription,
                // ...
            ));    
    }

    public function regularizationAction(Request  $request){
        //get send values
        $estInsId = $request->get('estInsId');
        // dump($estInsId);
        $arrSelectStudentInscription = $this->get('funciones')->getSelectStudentInscription($estInsId);
        // dump($arrSelectStudentInscription);
        // dump(sizeof($arrSelectStudentInscription) );
        if(sizeof($arrSelectStudentInscription) > 0 ){

        }else{
            dump('no cuenta con historial');
        }

        // die;
        return $this->render('SieHerramientaBundle:RegularizationCUT:regularization.html.twig', array(
            'form' => $this->createNivelationForm($arrSelectStudentInscription[0])->createView(),
                // ...
        ));    
    }


    private function createNivelationForm($data){
        $arrTypeNivelation = array('test1', 'test2');
        $form =  $this->createFormBuilder()
                ->add('data', 'hidden', array('mapped' => false,'data' => json_encode($data) , 'attr' => array()))
                ->add('typeNivelation', 'choice', array('label'=>'Tipo de Nivelación','choices'=>$arrTypeNivelation ,'attr'=>array('class'=>'form-control input-sm')))
                ->add('nota', 'text', array('label' => 'Nota', 'attr'=>array('class'=>'form-control input-sm')))
                ;
        if($data['nivelId']==13 && $data['gradoId']>4){
            $resultSpeciality = $this->get('funciones')->getSpeciality();
            $arrSpeciality = array();
            foreach ($resultSpeciality as $value) {
                // dump($value->getId());
                $arrSpeciality[$value->getId()] = strtoupper($value->getEspecialidad()) ;
            }
           
        }else{
            $arrSpeciality[-1] = 'NO APLICA PARA ESTE NIVEL';
        }

         $form = $form
                 ->add('speciality', 'choice', array('label'=>'Especialidad','choices'=>$arrSpeciality ,'attr'=>array('class'=>'form-control input-sm')))
                 ;

        $form = $form 
                
                ->add('register', 'button', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-info', 'onclick'=>'registerNivelation();')))
                ->getForm()
                ;
        // dump($data);die;
        return $form;
    }

}
