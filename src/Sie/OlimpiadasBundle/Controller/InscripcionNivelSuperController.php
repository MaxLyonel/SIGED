<?php

namespace Sie\OlimpiadasBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sie\AppWebBundle\Entity\OlimEstudianteInscripcion;
use Sie\AppWebBundle\Entity\OlimInscripcionGrupoProyecto;
use Sie\AppWebBundle\Form\OlimEstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * OlimEstudianteInscripcion controller.
 *
 */
/**
 * Author: Carlos Pacha Cordova <pckrlos@gmail.com>
 * Description:This is a class for load the inscription data; if exist in a particular proccess
 * Date: 03-04-2018
 *
 *
 * OlimEstudianteInscripcionController.php
 *
 * Email bugs/suggestions to <pckrlos@gmail.com>
 */

class InscripcionNivelSuperController extends Controller{

    private $sesion;

    public function __construct(){
        $this->session = new Session();
    }

    private function doExternalInscriptionForm($jsonDataInscription){
        return $this->createFormBuilder()
                ->add('jsonDataInscription','hidden', array('data'=>$jsonDataInscription) )
                ->add('doInscription', 'button', array('label'=>'Inscribir','attr'=>array('class'=>'btn btn-warning btn-xs')))
                ->getForm()
                ;

    }

    /*ublic function indexAction()
    {
        return $this->render('SieOlimpiadasBundle:InscripcionNivelSuper:index.html.twig', array(
                // ...
            ));    }

    public function findSudentInscriptionAction()
    {
        return $this->render('SieOlimpiadasBundle:InscripcionNivelSuper:findSudentInscription.html.twig', array(
                // ...
            ));    }*/


   

    /**
     * [selectInscriptionAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function indexAction(Request $request){
        // get the send values
        $form = $request->get('form');
        // dump($form);die;
        $objTutorSelected = $this->get('olimfunctions')->getTutor2($form);
        // dump($objTutorSelected);die;
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:specialInscription.html.twig', array(
            'form' => $this->specialInscriptionForm($form)->createView(),
            'tutor' => $objTutorSelected

        ));
    }



    private function specialInscriptionForm($data){
        // dump($data);die;
        $arrAreas = $this->get('olimfunctions')->getAllowedAreasByOlim();
        // dump($arrAreas);die;
        $newform = $this->createFormBuilder()
                ->setAction($this->generateUrl('oliminscriptionsnivelsuperior_findStudentRule'))
                ->add('codigoRude', 'text', array('label'=>'Codigo Rude', ))
                ->add('olimtutorid', 'hidden', array('data'=>$data['olimtutorid']))
                ->add('gestion', 'hidden', array('mapped' => false, 'label' => 'Gestion', 'attr' => array('class' => 'form-control', 'value'=>$data['gestiontipoid'])))
                ->add('buscar', 'submit', array('label'=>'Buscar', )) 
                ;
        // if($this->session->get('roluser')==8){
            $newform = $newform
                ->add('institucionEducativa', 'hidden', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control', 'value'=>$data['institucioneducativaid'])))
                ;
        // }

        $newform = $newform->getForm();
        return $newform;

    }


    public function findStudentRuleAction(Request $request){
        //create db conexino
        $em = $this->getDoctrine()->getManager();
        // get the send data
        $form = $request->get('form');
        $form['gestiontipoid']= $form['gestion'];
        
        // dump($studentToInscription);
        // get the olim inforamtion
        $objOlimRegistroOlimpiada = $em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->findOneBy(array(
            'gestionTipo'=>$form['gestion']
        ));
        // get the materias into olim
        $objMaterias = $em->getRepository('SieAppWebBundle:OlimMateriaTipo')->findBy(array(
            'olimRegistroOlimpiada'=>$objOlimRegistroOlimpiada->getId()
        ));
        // dump($objMaterias);die;
        //get materia and category
        $objOlimMaterias = $this->getOlimMaterias($objMaterias);
        // dump($objOlimMaterias);die;
        $objStudentInscription = $this->get('olimfunctions')->lookForOlimStudentByRudeGestion($form);
        $jsonDataInscription = json_encode($form);
        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:findStudentRule.html.twig', array(
            'inscripForm' => $this->doExternalInscriptionForm($jsonDataInscription)->createView(),
            'objStudentInscription' => $objStudentInscription,
            'entities' => $objOlimMaterias
        ));
    }



  private function getOlimMaterias($entities){
    $em = $this->getDoctrine()->getManager();
        $array = array();
        $cont = 0;
        foreach ($entities as $en) {

            $array[$cont] = array(
                'id'=>$en->getId(),
                'materia'=>$en->getMateria(),
                'fechaInsIni'=>$en->getFechaInsIni()->format('d-m-Y'),
                'fechaInsFin'=>$en->getFechaInsFin()->format('d-m-Y')
            );

            $categorias = $em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->findBy(array('olimMateriaTipo'=>$en->getId()));
            if(count($categorias)>0){
                foreach ($categorias as $ca) {
                    $array[$cont]['categorias'][] = array(
                        'id'=>$ca->getId(),
                        'categoria'=>$ca->getCategoria(),
                        'modalidad'=>$ca->getModalidadParticipacionTipo()->getModalidad()
                    );
                    
                    
                }
            }else{
                $array[$cont]['categorias'] = array();
            }
            $cont++;
        }

        return $array;
    }
 


    



}
