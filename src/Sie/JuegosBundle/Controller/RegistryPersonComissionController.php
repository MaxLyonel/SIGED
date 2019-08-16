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
class RegistryPersonComissionController extends Controller{

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
    /**
     * to index function to show the main page
     * by krlos pacha pckrlos.a.gmail.dot.com
     * @param type
     * @return void
     */
    public function indexAction(){
        // $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SieJuegosBundle:RegistryPersonComission:index.html.twig', array(
                'form'=> $this->formFindPerson()->createView()
            ));    
    }

    private function formFindPerson(){
        $form = $this->createFormBuilder()
                ->add('ci', 'text', array('label' => 'carnet Identidad', 'attr' => array('placeholder' => 'Ingresar CI', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '10', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('complemento', 'text', array('label' => 'complemento', 'attr' => array('placeholder' => 'Ingresar Complemento','class' => 'form-control', 'required' => false,'style' => 'text-transform:uppercase', 'maxlength'=>'2')))
                ->add('find','button',array('label'=>'Buscar','attr'=>array('class'=>'btn btn-success','onclick'=>'lookForData()')))
                ->getForm();
        return $form;
    }

    /**
     * to look for person data 
     * by krlos pacha pckrlos.a.gmail.dot.com
     * @param form array by post
     * @return template
     */
    public function lookForDataAction(Request $request){
        // check the users session
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        // get send data        
        $form = $request->get('form');
        $dataPerson = $this->getPerson($form);
        $entity = false;
        $form = false;
        if($dataPerson){    
            $entity = $em->getRepository('SieAppWebBundle:Persona')->find($dataPerson['id']);
            $form = $this->formRegisterCommission($entity)->createView();
        }else{
            $form = $this->formFindPersonBySegip()->createView();
        }
        // die;
        return $this->render('SieJuegosBundle:RegistryPersonComission:lookForData.html.twig', array(
                'entity' => $entity,
                'form' => $form,
                'dataCommission' => array(),
            ));    
    }
    private function formRegisterCommission($entity){
        $form = $this->createFormBuilder()
        ->add('personId', 'text', array('data'=>$entity->getId() , 'mapped'=>false))
        ->add('regPerson','button',array('label'=>'Regstrar Comision','attr'=>array('class'=>'btn btn-warning','onclick'=>'registerCommission()')))
                ->getForm();
        return $form;
    }

    private function formFindPersonBySegip(){
        $form = $this->createFormBuilder()
                ->add('ci', 'text', array('label' => 'Carnet Identidad', 'attr' => array('placeholder' => 'Ingresar CI', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '10', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('complemento', 'text', array('label' => 'complemento', 'attr' => array('placeholder' => 'Ingresar Complemento','class' => 'form-control', 'required' => false,'style' => 'text-transform:uppercase', 'maxlength'=>'2')))
                ->add('fechaNacimiento', 'text', array('label' => 'Fecha Nacimiento', 'attr' => array('placeholder' => 'Ingresar Fecha Nacimiento', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '10', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('nombre', 'text', array('label' => 'Nombre', 'attr' => array('placeholder' => 'Ingresar Nombre', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '50', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('paterno', 'text', array('label' => 'Paterno', 'attr' => array('placeholder' => 'Ingresar Paterno', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '50', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('materno', 'text', array('label' => 'Materno', 'attr' => array('placeholder' => 'Ingresar Materno', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '50', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('regPerson','button',array('label'=>'Regstrar Persona','attr'=>array('class'=>'btn btn-success','onclick'=>'lookForDataBySegip()')))

                ->add('generoTipo', 'entity', array('label' => 'Género', 'attr' => array('class' => 'form-control'),
                    'mapped' => false, 'class' => 'SieAppWebBundle:GeneroTipo',
                    'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('gt')
                                ->where('gt.id IN (:arrGenero)')
                                ->setParameter('arrGenero', array(1,2))
                                // ->orderBy('lt.codigo', 'ASC')
                        ;
                    }, 'property' => 'genero'
                ))

                ->getForm();
        return $form;
    }




    private function getPerson($data){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select * from persona 
        where carnet = '".$data['ci']."' and complemento = '".$data['complemento']."'");
        $query->execute();
        return $query->fetch();
        // $entity = $em->getRepository('SieAppWebBundle:Persona');
        // $query = $entity->createQueryBuilder('per')
        //         ->select('per')                
        //         ->where('per.carnet = :ci');
        // if($data['complemento']){
        //         $query = $query
        //         ->andwhere('per.complemento = :complemento')
        //         ->setParameter('complemento', trim($data['complemento']));            
        // }
        // $query = $query
        //         ->setParameter('ci', trim($data['ci']))
        //         ->getQuery();
        //         // dump($query->getSQL());die;
        // $objPerson = $query->getResult();

         //look for the tutores in persona table


    }

    public function newPersonAction(){
        // check the users session
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieJuegosBundle:RegistryPersonComission:newPerson.html.twig', array(
                // ...
            ));    
    }

    /**
     * to registry the commission data 
     * by krlos pacha pckrlos.a.gmail.dot.com
     * @param form array by post
     * @return template
     */
    public function registerCommissionAction(Request $request){
        // check the users session
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // get the send values
        $personId = $request->get('personId');
        $form = $this->formCommission($personId)->createView();
        return $this->render('SieJuegosBundle:RegistryPersonComission:registerCommission.html.twig',array(
            'form'=>$form,
        ));
    }

    private function formCommission($personId){
        // create db conexion
        $em=$this->getDoctrine()->getManager();
        $arrDepto=array();
        $form = $this->createFormBuilder()
        
        ->add('departamento', 'entity', array('label'=>'departamento','class' => 'SieAppWebBundle:DepartamentoTipo', 'property' => 'departamento','attr'=>array('class'=>'form-control')))
        ->add('comisionTipo', 'entity', array('label'=>'Comisión','class' => 'SieAppWebBundle:ComisionTipo', 'empty_value'=>'Selecionar Comisión',
            'query_builder' => function(EntityRepository $e){
                return $e->createQueryBuilder('ct')
                        ->where('ct.nivelTipoId = :levelId')
                        ->setParameter('levelId','12')
                        ->orderBy('ct.comision', 'ASC');
            }, 'property' => 'comision','attr'=>array('class'=>'form-control')))
        ->add('personId', 'text', array('data'=>$personId , 'mapped'=>false))
        ->add('regCommission','button',array('label'=>'Regstrar Comisión','attr'=>array('class'=>'btn btn-info','onclick'=>'saveCommission()')))
        
        ->getForm();
        return $form;
    }

    /**
     * to save the commission info
     * by krlos pacha pckrlos.a.gmail.dot.com
     * @param form array by post
     * @return template
     */
    public function saveCommissionAction(Request $request){
        // check the users session
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // get the send values
        $form = $request->get('form');
        // create db conexin 
        $em = $this->getDoctrine()->getManager();

        try {
            // save the commission data
            $swSaveCommission = $this->saveCommissionData($form);
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
    /**
     * to look for the person data by segip method
     * by krlos pacha pckrlos.a.gmail.dot.com
     * @param form array by post
     * @return template
     */
    public function lookForDataBySegipAction(Request $request){
        // check the users session
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // get the data send
        $form = $request->get('form');
        
        $arrParametros = array('complemento'=>$form['complemento'],
                'primer_apellido'=>strtoupper($form['paterno']) ,
                'segundo_apellido'=>strtoupper($form['materno']),
                'nombre'=>strtoupper($form['nombre']),
                'fecha_nacimiento'=>$form['fechaNacimiento']);
        // dump($form);
        // dump($arrParametros);
        // die;
        // $answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $form['ci'],$arrParametros,'prod', 'academico');
        $answerSegip = true;
        if($answerSegip){
            // save the person data
            $swSavePerson = $this->savePerson($form);
            // dump($swSavePerson);
            // die;
            // check if it was donce the save
            if($swSavePerson){
                // save message
                $message = 'Datos de la Persona Validados por Segip y Registrados Correctamente';
                $this->addFlash('messageCommission', $message);
                $typeMessage = 'success';
                // $personId = $request->get('personId');
                $personId = $swSavePerson;
                $form = $this->formCommission($personId)->createView();
                return $this->render('SieJuegosBundle:RegistryPersonComission:registerCommission.html.twig',array(
                    'form'=>$form,
                    'typeMessage' => $typeMessage,
                ));
            }else{
                // no save message
            }


        }else{
            $message = 'Datos no registrados, validados por Segip';
            $this->addFlash('messageCommission', $message);
            $typeMessage = 'danger';
            return $this->render('SieJuegosBundle:RegistryPersonComission:saveCommission.html.twig',array(
                'typeMessage' => $typeMessage,
            ));
        }

        
    }

    private function saveCommissionData($form){
        
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        $swAnswer = false;
        try {

            // save the commision to the person choose
            $objComisionJuegosDatos = new JdpDelegadoInscripcionJuegos();
            $objComisionJuegosDatos->setFaseTipo($em->getRepository('SieAppWebBundle:JdpFaseTipo')->find(4));
            $objComisionJuegosDatos->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($form['personId']));
            $objComisionJuegosDatos->setComisionTipo($em->getRepository('SieAppWebBundle:JdpComisionTipo')->find($form['comisionTipo']));
            $objComisionJuegosDatos->setLugarTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento']));
            $objComisionJuegosDatos->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->currentyear));
            
            $em->persist($objComisionJuegosDatos);
            $em->flush(); 
            $swAnswer = true;
            
        } catch (Exception $e) {
            $swAnswer = false;
        }

        return $swAnswer;
    }

    private function savePerson($form){
        // dump($form);
        // die;
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        $swAnswer = false;
        try {
            $objPerson = new Persona();
            $objPerson->setSegipId(1);
            $objPerson->setCarnet($form['ci']);
            $objPerson->setComplemento($form['complemento']);
            $objPerson->setNombre(mb_strtoupper($form['nombre'], 'utf8'));
            $objPerson->setPaterno(mb_strtoupper($form['paterno'], 'utf8'));
            $objPerson->setMaterno(mb_strtoupper($form['materno'], 'utf8'));
            $objPerson->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
            $objPerson->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['generoTipo']));
            // need ask about these values 
            $objPerson->setRda(0);
            $objPerson->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->find(0));
            $objPerson->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->find(0));
            $objPerson->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find(0));
            $objPerson->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find(0));

            $em->persist($objPerson);
            $em->flush();            
            
            // $swAnswer = $objPerson->getId();
            $swAnswer = $objPerson->getId();
        } catch (Exception $e) {
            $swAnswer = false;
        }
        return $swAnswer;
    }

}