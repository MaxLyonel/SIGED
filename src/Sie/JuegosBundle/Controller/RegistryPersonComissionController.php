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
use Sie\JuegosBundle\Controller\ClasificacionController as ClasificacionController;
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

    /**
     * to index function to show the main page
     * by krlos pacha pckrlos.a.gmail.dot.com
     * @param type
     * @return void
     */
    public function indexDelegacionDepartamentalAction(){
        // $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $nivelId = 13;

        return $this->render('SieJuegosBundle:RegistryPersonComission:indexDelegacionDepartamental.html.twig', array(
                'form'=> $this->formFindPersonDelegado($nivelId)->createView()
            ));    
    }

    private function formFindPerson(){
        $form = $this->createFormBuilder()
                ->add('cifind', 'text', array('label' => 'carnet Identidad', 'attr' => array('placeholder' => 'Ingresar CI', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '10', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('complemento', 'text', array('label' => 'complemento', 'attr' => array('placeholder' => 'Ingresar Complemento','class' => 'form-control', 'required' => false,'style' => 'text-transform:uppercase', 'maxlength'=>'2')))
                ->add('find','button',array('label'=>'Buscar','attr'=>array('class'=>'btn btn-success','onclick'=>'lookForData()')))
                ->getForm();
        return $form;
    }

    private function formFindPersonDelegado($nivelId){
        $form = $this->createFormBuilder()
                ->add('cifind', 'text', array('label' => 'carnet Identidad', 'attr' => array('placeholder' => 'Ingresar CI', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '10', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('complemento', 'text', array('label' => 'complemento', 'attr' => array('placeholder' => 'Ingresar Complemento','class' => 'form-control', 'required' => false,'style' => 'text-transform:uppercase', 'maxlength'=>'2')))
                ->add('nivel', 'hidden', array('attr'=>array('value'=>$nivelId)))  
                ->add('find','button',array('label'=>'Buscar','attr'=>array('class'=>'btn btn-success','onclick'=>'buscaDelegado()')))
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
        
        $objComisionJuegosDatos = false;
        $entity = false;
        $pathToShowImg = false;
        $form = false;
        $ratificar = false;

        if($dataPerson){    
            $entity = $em->getRepository('SieAppWebBundle:Persona')->find($dataPerson['id']);
            
            if($objComisionJuegosDatos){
                $message = 'Datos existentes';
                $this->addFlash('lookForDataMessage', $message);
                $typeMessage = 'success';
            }else{
                // $objComisionJuegosDatos = $em->getRepository('SieAppWebBundle:JdpDelegadoInscripcionJuegos')->findOneBy(array('persona'=>$entity->getId()));
                $jdij = $em->getRepository('SieAppWebBundle:JdpDelegadoInscripcionJuegos');
                $query = $jdij->createQueryBuilder('jdij')
                    ->select('jdij')
                    ->join('SieAppWebBundle:JdpComisionTipo', 'ct', 'WITH', 'jdij.comisionTipo = ct.id')
                    ->where('jdij.persona = :persona')
                    ->andWhere('ct.nivelTipoId = :nivelId')
                    ->setParameter('persona', $entity)
                    ->setParameter('nivelId', 13)
                    ->getQuery();

                $objComisionJuegosDatos = $query->getResult();

                if($objComisionJuegosDatos){
                    // list($pathSever,$pathImg) = explode('web', $objComisionJuegosDatos->getRutaImagen());
                    $pathToShowImg = $objComisionJuegosDatos[0]->getRutaImagen();
                    $ratificar = false;
                } else {
                    
                    $jdij = $em->getRepository('SieAppWebBundle:JdpDelegadoInscripcionJuegos');
                    $query = $jdij->createQueryBuilder('jdij')
                    ->select('jdij')
                    ->join('SieAppWebBundle:JdpComisionTipo', 'ct', 'WITH', 'jdij.comisionTipo = ct.id')
                    ->where('jdij.persona = :persona')
                    ->andWhere('ct.nivelTipoId = :nivelId')
                    ->setParameter('persona', $entity)
                    ->setParameter('nivelId', 12)
                    ->getQuery();

                    $objComisionJuegosDatos = $query->getResult();
                    
                    $ratificar = true;
                    
                }

                $message = 'Registre datos de comision';
                $this->addFlash('lookForDataMessage', $message);
                $typeMessage = 'warning';

            }
            $form = $this->formRegisterCommission($entity)->createView();
        }else{
            $message = 'Registre datos de la Persona';
            $this->addFlash('lookForDataMessage', $message);
            $typeMessage = 'warning';
            $form = $this->formFindPersonBySegip()->createView();
        }
        
        if($objComisionJuegosDatos){
            $objComisionJuegosDatos = $objComisionJuegosDatos[0];
            $pathToShowImg = $objComisionJuegosDatos[0]->getRutaImagen();
        }
        
        return $this->render('SieJuegosBundle:RegistryPersonComission:lookForData.html.twig', array(
                'entity' => $entity,
                'form' => $form,
                'dataCommission' => array(),
                'objComisionJuegosDatos' => $objComisionJuegosDatos,
                'typeMessage' => $typeMessage,
                'pathToShowImg' => $pathToShowImg,
                'ratificar' => $ratificar,
            ));    
    }

    /**
     * to look for person data 
     * by krlos pacha pckrlos.a.gmail.dot.com
     * @param form array by post
     * @return template
     */
    public function buscaDelegadoAction(Request $request){
        // check the users session
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        // get send data        
        $form = $request->get('form');
        $formData = $request->get('form');
        $dataPerson = $this->getPerson($form);
        
        $objComisionJuegosDatos = false;
        $entity = false;
        $pathToShowImg = false;
        $form = false;

        if($dataPerson){    
            $entity = $em->getRepository('SieAppWebBundle:Persona')->find($dataPerson['id']);
            
            
            if($objComisionJuegosDatos){
                $message = 'Datos existentes';
                $this->addFlash('lookForDataMessage', $message);
                $typeMessage = 'success';
            }else{
                $objComisionJuegosDatos = $em->getRepository('SieAppWebBundle:JdpDelegadoInscripcionJuegos')->findBy(array('persona'=>$entity->getId()));
                //dump($objComisionJuegosDatos);die;
                if (count($objComisionJuegosDatos)>0){
                    $val = true;
                    $datos = array();
                    foreach ($objComisionJuegosDatos as $registro) {
                        $nivelId = $registro->getComisionTipo()->getNivelTipoId();
                        //dump($nivelId);dump((int)$formData['nivel']);die;
                        if ($nivelId == (int)$formData['nivel']){
                            $val = false;
                            $datos = $registro;
                        }
                    }                 
                    if($val){
                        $objComisionJuegosDatos = array();
                    } else {
                        $objComisionJuegosDatos = $datos;
                    }
                }
                
                
                // if($objComisionJuegosDatos){
                //     list($pathSever,$pathImg) = explode('web', $objComisionJuegosDatos->getRutaImagen());
                //     $pathToShowImg = '../..'.$pathImg;
                // }

                $message = 'Registre datos de comision';
                $this->addFlash('lookForDataMessage', $message);
                $typeMessage = 'warning';

            }
            $form = $this->formRegisterDelegado($entity)->createView();
        }else{
            $message = 'Registre datos de la Persona';
            $this->addFlash('lookForDataMessage', $message);
            $typeMessage = 'warning';
            $form = $this->formFindPersonBySegip()->createView();
        }
        // die;
        return $this->render('SieJuegosBundle:RegistryPersonComission:buscaDelegado.html.twig', array(
                'entity' => $entity,
                'form' => $form,
                'dataCommission' => array(),
                'objComisionJuegosDatos' => $objComisionJuegosDatos,
                'typeMessage' => $typeMessage,
                'pathToShowImg' => $pathToShowImg,

            ));    
    }

    private function formRegisterCommission($entity){
        $form = $this->createFormBuilder()
        ->add('personId', 'hidden', array('data'=>$entity->getId() , 'mapped'=>false))
        ->add('regPerson','button',array('label'=>'Regstrar Comision','attr'=>array('class'=>'btn btn-warning','onclick'=>'registerCommission()')))
                ->getForm();
        return $form;
    }

    private function formRegisterDelegado($entity){
        $form = $this->createFormBuilder()
        ->add('personId', 'hidden', array('data'=>$entity->getId() , 'mapped'=>false))
        ->add('regPerson','button',array('label'=>'Regstrar Comision','attr'=>array('class'=>'btn btn-warning','onclick'=>'registerDelegado()')))
                ->getForm();
        return $form;
    }

    private function formFindPersonBySegip(){
        $form = $this->createFormBuilder()
                ->add('ci', 'text', array('label' => 'Carnet Identidad', 'attr' => array('placeholder' => 'Ingresar CI', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '10', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('complemento', 'text', array('label' => 'complemento', 'attr' => array('placeholder' => 'Ingresar Complemento','class' => 'form-control', 'required' => false,'style' => 'text-transform:uppercase', 'maxlength'=>'2')))
                ->add('fechaNacimiento', 'text', array('label' => 'Fecha Nacimiento', 'attr' => array('placeholder' => 'Ingresar Fecha Nacimiento', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '10', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('nombre', 'text', array('label' => 'Nombre', 'attr' => array('placeholder' => 'Ingresar Nombre', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '50', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('paterno', 'text', array('label' => 'Paterno', 'attr' => array('required'=>false,'placeholder' => 'Ingresar Paterno', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '50', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('materno', 'text', array('label' => 'Materno', 'attr' => array('required'=>false,'placeholder' => 'Ingresar Materno', 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '50', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
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
        where carnet = '".$data['cifind']."' and complemento = '".$data['complemento']."'");
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

    /**
     * to registry the commission data 
     * by krlos pacha pckrlos.a.gmail.dot.com
     * @param form array by post
     * @return template
     */
    public function registerDelegacionDepartamentalAction(Request $request){
        // check the users session
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $fase = 3; // nombre de la fase actual

        $clasificacionController = new ClasificacionController();
        $clasificacionController->setContainer($this->container);

        $objEntidad = $clasificacionController->buscaEntidadFase($fase,$this->userlogged);

        if (!$objEntidad) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe información del Departamento'));
            return $this->redirect($this->generateUrl('registrypersoncomission_registerDelegacionDepartamental'));
        }

        $codigoEntidad = $objEntidad[0]['id'];

        $personId = $request->get('personId');
        $form = $this->formDelegacionDepartamental($personId, $codigoEntidad)->createView();
        return $this->render('SieJuegosBundle:RegistryPersonComission:registerDelegacionDepartamental.html.twig',array(
            'form'=>$form,
        ));
    }

    private function formCommission($personId){
        // create db conexion

        $em=$this->getDoctrine()->getManager();
        $arrDepto=array();
        $form = $this->createFormBuilder()
        
        ->add('departamento', 'entity', array('label'=>'Lugar comision','class' => 'SieAppWebBundle:LugarTipo', 'empty_value'=>'Selecionar Departamento',
            'query_builder' => function(EntityRepository $e){
                return $e->createQueryBuilder('lt')
                        ->where('lt.paisTipoId = :paisId')
                        ->andwhere('lt.lugarNivel = :levelId')
                        ->orwhere('lt.id = :lugarId')
                        ->setParameter('paisId','1')
                        ->setParameter('levelId','1')
                        ->setParameter('lugarId','1')
                        ->orderBy('lt.id', 'ASC');
            }, 'property' => 'lugar','attr'=>array('class'=>'form-control')
        ))
        ->add('comisionTipo', 'entity', array('label'=>'Comisión','class' => 'SieAppWebBundle:JdpComisionTipo', 'empty_value'=>'Selecionar Comisión',

            'query_builder' => function(EntityRepository $e){
                return $e->createQueryBuilder('ct')
                        ->where('ct.nivelTipoId = :levelId')
                        ->andWhere('ct.esactivo = true')
                        ->setParameter('levelId','13')
                        ->orderBy('ct.comision', 'ASC');
            }, 'property' => 'comision','attr'=>array('class'=>'form-control')))
        ->add('photoperson', 'file', array('label' => 'Fotografía', 'required' => true))
        ->add('obs', 'text', array('label' => 'Observacion', 'required' => true, 'attr'=> array('class'=>'form-control')))
        ->add('personId', 'hidden', array('data'=>$personId , 'mapped'=>false))        
        ->add('regCommission','button',array('label'=>'Regstrar Comisión','attr'=>array('class'=>'btn btn-info','onclick'=>'saveCommission()')))
        ->getForm();
        return $form;
    }

    public function formDelegacionDepartamental($personId, $departamentoId){
        // dump($personId);dump($departamentoId);die;
        // create db conexion
        $em=$this->getDoctrine()->getManager();
        $arrOption=array('personId'=>$personId, 'departamentoId' => $departamentoId);
        $nivelId = 13;
        if ($nivelId == 12){
            $form = $this->createFormBuilder()        
            ->add('departamento', 'entity', array('label'=>'departamento','class' => 'SieAppWebBundle:LugarTipo', 'empty_value'=>'Selecionar Departamento',
                'query_builder' => function(EntityRepository $e)use ($arrOption){
                    return $e->createQueryBuilder('lt')
                            ->where('lt.paisTipoId = :paisId')
                            ->andwhere('lt.lugarNivel = :levelId')
                            ->andwhere('lt.codigo = :departamentoId')
                            ->setParameter('paisId','1')
                            ->setParameter('levelId','6')
                            ->setParameter('departamentoId',$arrOption['departamentoId'])
                            ->orderBy('lt.lugar', 'ASC');
                }, 'property' => 'lugar','attr'=>array('class'=>'form-control')
            ))
            ->add('comisionTipo', 'entity', array('label'=>'Comisión','class' => 'SieAppWebBundle:JdpComisionTipo', 'empty_value'=>'Selecionar Comisión',
                'query_builder' => function(EntityRepository $e){
                    return $e->createQueryBuilder('ct')
                            ->where('ct.nivelTipoId = :levelId')
                            ->andWhere('ct.esactivo = true')
                            ->andWhere('ct.id in (103,105,106,107)')
                            ->setParameter('levelId','12')
                            ->orderBy('ct.comision', 'ASC');
                }, 'property' => 'comision','attr'=>array('class'=>'form-control', 'required' => true)))
            ->add('photoperson', 'file', array('label' => 'Fotografía', 'required' => true))
            ->add('personId', 'hidden', array('data'=>$personId , 'mapped'=>false))        
            ->add('regCommission','button',array('label'=>'Regstrar Comisión','attr'=>array('class'=>'btn btn-primary','onclick'=>'saveDelegado()')))
            ->getForm();
        } else {
            $form = $this->createFormBuilder()        
            ->add('departamento', 'entity', array('label'=>'departamento','class' => 'SieAppWebBundle:LugarTipo', 'empty_value'=>'Selecionar Departamento',
                'query_builder' => function(EntityRepository $e)use ($arrOption){
                    return $e->createQueryBuilder('lt')
                            ->where('lt.paisTipoId = :paisId')
                            ->andwhere('lt.lugarNivel = :levelId')
                            ->andwhere('lt.codigo = :departamentoId')
                            ->setParameter('paisId','1')
                            ->setParameter('levelId','6')
                            ->setParameter('departamentoId',$arrOption['departamentoId'])
                            ->orderBy('lt.lugar', 'ASC');
                }, 'property' => 'lugar','attr'=>array('class'=>'form-control')
            ))
            ->add('comisionTipo', 'entity', array('label'=>'Comisión','class' => 'SieAppWebBundle:JdpComisionTipo', 'empty_value'=>'Selecionar Comisión',
                'query_builder' => function(EntityRepository $e){
                    return $e->createQueryBuilder('ct')
                            ->where('ct.nivelTipoId = :levelId')
                            ->andWhere('ct.esactivo = true')
                            ->andWhere('ct.id in (146,154,150,156,152,153,122,151)')
                            ->setParameter('levelId','13')
                            ->orderBy('ct.comision', 'ASC');
                }, 'property' => 'comision','attr'=>array('class'=>'form-control', 'required' => true)))
            ->add('photoperson', 'file', array('label' => 'Fotografía', 'required' => true))
            ->add('personId', 'hidden', array('data'=>$personId , 'mapped'=>false))        
            ->add('regCommission','button',array('label'=>'Regstrar Comisión','attr'=>array('class'=>'btn btn-primary','onclick'=>'saveDelegado()')))
            ->getForm();
        }
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
        $form['photoData'] = $request->files->get('form');
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
     * to save the commission info
     * by krlos pacha pckrlos.a.gmail.dot.com
     * @param form array by post
     * @return template
     */
    public function saveDelegacionDepartamentalAction(Request $request){
        // check the users session
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // get the send values
        $form = $request->get('form');
        $form['photoData'] = $request->files->get('form');
        // create db conexin 
        $em = $this->getDoctrine()->getManager();
        $pruebaEntity = $em->getRepository('SieAppWebBundle:JdpDelegadoInscripcionJuegos')->findBy(array('lugarTipo' => $form['departamento'], 'comisionTipo' => $form['comisionTipo']));
        try {
            // save the commission data

            if(count($pruebaEntity)>0){
                if(count($pruebaEntity) >= ($pruebaEntity[0]->getComisionTipo()->getCantidad())){
                    $message = 'Datos no registrados, ya no cuenta con cupo para el tipo de delegado seleccionado';
                    $this->addFlash('messageCommission', $message);
                    $typeMessage = 'danger';
                } else {
                    $typeMessage = 'success';

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
                }
            } else {
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
        //$answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $form['ci'],$arrParametros,'prod', 'academico');
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
            //look for person
            $objPerson = $em->getRepository('SieAppWebBundle:Persona')->find($form['personId']);

            $personaId = $form['personId'];
            $cedula = $objPerson->getCarnet();
            $complemento = $objPerson->getComplemento();
            $ci = '';
            if ($complemento == ""){
                $ci = $cedula;
            } else {
                $ci = $cedula.'-'.$complemento;
            }
            
            $namePhoto = "";

            if($form['photoData']['photoperson'] != null) {
                // create the img path
                // $dirtmp = $this->get('kernel')->getRootDir() . '/../web/uploads/documento_persona/'.$objPerson->getCarnet();
                $dirtmp = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/documento_persona/'.$ci.'/';

                // if (!file_exists($dirtmp)) {
                //     mkdir($dirtmp, 0775);
                // }

                // get info about the img
                $imgExtension = $form['photoData']['photoperson']->getMimeType();
                list($typeImg, $extensionImg) = explode('/', $imgExtension);
                // $namePhoto = $objPerson->getCarnet().'_fotografía_'.$form['personId'].'.'.$extensionImg;
                $namePhoto = $ci.'_fotografia_'.$personaId.'.'.$extensionImg;

                //move the file on the img path
                $movefile = $form['photoData']['photoperson']->move($dirtmp, $namePhoto);
            }

            // save the commision to the person choose
            $objComisionJuegosDatos = new JdpDelegadoInscripcionJuegos();
            $objComisionJuegosDatos->setFaseTipo($em->getRepository('SieAppWebBundle:JdpFaseTipo')->find(4));
            $objComisionJuegosDatos->setPersona($objPerson);
            $objComisionJuegosDatos->setComisionTipo($em->getRepository('SieAppWebBundle:JdpComisionTipo')->find($form['comisionTipo']));
            $objComisionJuegosDatos->setLugarTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento']));
            $objComisionJuegosDatos->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->currentyear));
            // $objComisionJuegosDatos->setRutaImagen($dirtmp.'/'.$namePhoto);            
            $objComisionJuegosDatos->setRutaImagen($ci.'/'.$namePhoto);            
            $objComisionJuegosDatos->setObs(isset($form['obs'])?strtoupper(mb_strtoupper($form['obs'], 'utf8')):'');
            $em->persist($objComisionJuegosDatos);

            $objPerson->setFoto($ci.'/'.$namePhoto);            
            $em->persist($objPerson);

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
            $objPerson->setSegipId(0);
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
