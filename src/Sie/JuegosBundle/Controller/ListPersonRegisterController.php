<?php

namespace Sie\JuegosBundle\Controller;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sie\JuegosBundle\Controller\EstudianteInscripcionJuegosController as estudianteInscripcionJuegosController;
use Sie\JuegosBundle\Controller\ReglaController as reglaController;
use Sie\AppWebBundle\Entity\JdpEstudianteInscripcionJuegos as EstudianteInscripcionJuegos;
use Sie\AppWebBundle\Entity\JdpEquipoEstudianteInscripcionJuegos as EquipoEstudianteInscripcionJuegos;
use Sie\AppWebBundle\Entity\JdpPersonaInscripcionJuegos;
use Sie\AppWebBundle\Entity\ComisionJuegosDatos;
use Sie\AppWebBundle\Entity\JdpDelegadoInscripcionJuegos;
use Sie\AppWebBundle\Entity\Persona;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Author: Carlos Pacha Cordova <pckrlos@gmail.com>
 * Description:This is a class for reporting the commission data
 * Date: 14-08-2019
 *
 *
 * RegistryPersonComissionController.php
 *
 * Email bugs/suggestions to <pckrlos@gmail.com>
 */
class ListPersonRegisterController extends Controller{


    public $session;
    public $currentyear;
    public $userlogged;
    // public $comisionTipo;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->currentyear = $this->session->get('currentyear');
        $this->userlogged = $this->session->get('userId');
    }
    
    public function indexAction(Request $request){

        $em = $this->getDoctrine()->getManager();

         //validation if the user is logged
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // get all deletados
        $entities = $this->delegadoInfo(NULl);
        
        return $this->render('SieJuegosBundle:ListPersonRegister:index.html.twig', array(
                'entities' => $entities,
            ));    
    }

    
    public function editAction(Request $request){
        // get the send values
        $jsonDelegadoId = $request->get('id');
        $arrDelegadoId = json_decode($jsonDelegadoId,true);
        //get the delegado info
        $infoDelegado = $this->delegadoInfo($arrDelegadoId['delegadoId']);
        // dump($infoDelegado);die;
        //info about the picture
        // list($pathSever,$pathImg) = explode('web', $infoDelegado[0]['rutaImagen']);
        $pathToShowImg = $infoDelegado[0]['rutaImagen'];
        // $form = 
        return $this->render('SieJuegosBundle:ListPersonRegister:edit.html.twig', array(
                'entity' => $infoDelegado[0],
                'pathToShowImg' => $pathToShowImg,
                'form' => $this->formCommission($infoDelegado)->createView(),
            ));    
    }

    private function formCommission($data){
        // create db conexion
        $em=$this->getDoctrine()->getManager();
        $arrDepto=array();
        $form = $this->createFormBuilder()
        
        ->add('departamento', 'entity', array('label'=>'departamento','class' => 'SieAppWebBundle:LugarTipo', 'empty_value'=>'Selecionar Departamento',
            'query_builder' => function(EntityRepository $e){
                return $e->createQueryBuilder('lt')
                        ->where('lt.paisTipoId = :paisId')
                        ->andwhere('lt.lugarNivel = :levelId')
                        ->setParameter('paisId','1')
                        ->setParameter('levelId','1')
                        ->orderBy('lt.lugar', 'ASC');
            }, 'property' => 'lugar','attr'=>array('class'=>'form-control'),
                'data' => $em->getReference("SieAppWebBundle:LugarTipo",$data[0]['lugarTipoId'])
                ))
        ->add('comisionTipo', 'entity', array('label'=>'Comisión','class' => 'SieAppWebBundle:ComisionTipo', 'empty_value'=>'Selecionar Comisión',
            'query_builder' => function(EntityRepository $e){
                return $e->createQueryBuilder('ct')
                        ->where('ct.nivelTipoId = :levelId')
                        ->setParameter('levelId','12')
                        ->orderBy('ct.comision', 'ASC');
            }, 'property' => 'comision', 'attr'=>array('class'=>'form-control'),
                'data' => $em->getReference("SieAppWebBundle:ComisionTipo",$data[0]['comisionTipoId'])
            ))
        ->add('photoperson', 'file', array('label' => 'Fotografía', 'required' => true))
        ->add('personId', 'hidden', array('data'=>$data[0]['personId'] , 'mapped'=>false))
        
        ->add('regCommission','button',array('label'=>'Regstrar Comisión','attr'=>array('class'=>'btn btn-info','onclick'=>'updateCommission()')))
        
        ->getForm();
        return $form;
    }


    private function delegadoInfo($delegadoId=null){
        $em = $this->getDoctrine()->getManager();
         $entity = $em->getRepository('SieAppWebBundle:Persona');
        $query = $entity->createQueryBuilder('per')
                ->select('per.id as personId, per.carnet, per.complemento, per.paterno, per.materno,per.nombre,jdij.id as id, IDENTITY(jdij.comisionTipo) as comisionTipoId, IDENTITY(jdij.lugarTipo) as lugarTipoId, ct.comision as comisionTipo, lt.lugar as lugarTipo, jdij.rutaImagen')  
                ->join('SieAppWebBundle:JdpDelegadoInscripcionJuegos', 'jdij', 'WITH', 'per.id = jdij.persona')
                ->join('SieAppWebBundle:ComisionTipo', 'ct', 'WITH', 'jdij.comisionTipo = ct.id')
                ->join('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jdij.lugarTipo = lt.id');
         if($delegadoId){
          $query = $query  
                 ->where('jdij.id = :delegadoId')
                 ->setParameter('delegadoId', $delegadoId);
         }
         $query = $query
                ->getQuery();
                // dump($query->getSQL());die;
        $entities = $query->getResult();
        return $entities;
    }

    public function updateCommissionAction(Request $request){

         // check the users session
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // get the send values
        $form = $request->get('form');
        $form['photoData'] = $request->files->get('form');
        // create db conexin 
        $em = $this->getDoctrine()->getManager();

        try {
            // save the commission data
            $swSaveCommission = $this->updateCommissionData($form);
            // check if it was donce the save
            if($swSaveCommission){
                // save message
                $message = 'Datos registrados';
                $this->addFlash('messageCommission', $message);
                $typeMessage = 'success';
            }else{
                // no save message
                $message = 'Datos no registrados';
                $this->addFlash('messageCommission', $message);
                $typeMessage = 'danger';
            }
            return $this->render('SieJuegosBundle:RegistryPersonComission:saveCommission.html.twig',array(
                'typeMessage' => $typeMessage,
            ));
            
        } catch (Exception $e) {
            
        }

    }

     private function updateCommissionData($form){
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        $swAnswer = false;
        try {
            //look for person
            $objPerson = $em->getRepository('SieAppWebBundle:Persona')->find($form['personId']);
            // create the img path
            $dirtmp = $this->get('kernel')->getRootDir() . '/../web/uploads/documento_persona/'.$objPerson->getCarnet();
                if (!file_exists($dirtmp)) {
                mkdir($dirtmp, 0775);
            }
            // get info about the img
            $imgExtension = $form['photoData']['photoperson']->getMimeType();
            list($typeImg, $extensionImg) = explode('/', $imgExtension);
            $namePhoto = $objPerson->getCarnet().'_fotografía_'.$form['personId'].'.'.$extensionImg;
            //move the file on the img path
            $movefile = $form['photoData']['photoperson']->move($dirtmp, $namePhoto);

            // save the commision to the person choose
            // $objComisionJuegosDatos = new JdpDelegadoInscripcionJuegos();
            $objComisionJuegosDatos = $em->getRepository('SieAppWebBundle:JdpDelegadoInscripcionJuegos')->findOneBy(array('persona'=>$form['personId']));
            $objComisionJuegosDatos->setFaseTipo($em->getRepository('SieAppWebBundle:JdpFaseTipo')->find(4));
            // $objComisionJuegosDatos->setPersona($objPerson);
            $objComisionJuegosDatos->setComisionTipo($em->getRepository('SieAppWebBundle:JdpComisionTipo')->find($form['comisionTipo']));
            $objComisionJuegosDatos->setLugarTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento']));
            $objComisionJuegosDatos->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->currentyear));
            $objComisionJuegosDatos->setRutaImagen($objPerson->getCarnet()."/".$namePhoto);
            
            $em->persist($objComisionJuegosDatos);
            $em->flush(); 
            $swAnswer = true;
            
        } catch (Exception $e) {
            $swAnswer = false;
        }

        return $swAnswer;
    }

    public function newAction(){

        return $this->render('SieJuegosBundle:ListPersonRegister:new.html.twig', array(
                // ...
            ));    
    }






}
