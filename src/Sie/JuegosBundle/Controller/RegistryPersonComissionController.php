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
use Sie\AppWebBundle\Entity\Persona;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Config\Definition\Exception\Exception;

class RegistryPersonComissionController extends Controller{

    public $session;
    public $currentyear;
    // public $comisionTipo;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->currentyear = $this->session->get('currentyear')-1;
    }

    public function indexAction(){
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
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

    public function lookForDataAction(Request $request){
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
        return $this->render('SieJuegosBundle:RegistryPersonComission:newPerson.html.twig', array(
                // ...
            ));    
    }


    public function registerCommissionAction(Request $request){
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

    public function saveCommissionAction(Request $request){
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

    public function lookForDataBySegipAction(Request $request){
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
        $answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $form['ci'],$arrParametros,'prod', 'academico');

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
            $objComisionJuegosDatos = new ComisionJuegosDatos();
            $objComisionJuegosDatos->setDepartamentoTipo($form['departamento']);
            // $objComisionJuegosDatos->setPruebaTipo($entityPruebaTipo);
            $objComisionJuegosDatos->setFaseTipo($em->getRepository('SieAppWebBundle:FaseTipo')->find(3));
            $objComisionJuegosDatos->setGestionTipoId($this->session->get('currentyear')); 
            // $objComisionJuegosDatos->setInstitucioneducativa($entityInstitucioneducativa);  
            // $objComisionJuegosDatos->setFoto($foto);  
            $objComisionJuegosDatos->setCarnetIdentidad('000');  
            $objComisionJuegosDatos->setNombre('usertest');  
            $objComisionJuegosDatos->setPaterno('usertest');  
            $objComisionJuegosDatos->setMaterno('usertest');  
            $objComisionJuegosDatos->setCelular('110');  
            // $objComisionJuegosDatos->setCorreo($correo);  
            $objComisionJuegosDatos->setComisionTipoId($form['comisionTipo']);  
            // $objComisionJuegosDatos->setEsentrenador($esEntrenador);  
            // $objComisionJuegosDatos->setAvc($avc);
            // $objComisionJuegosDatos->setGeneroTipo($generoPersonaId);  
            // $objComisionJuegosDatos->setPosicion($posicion);                                                           
            
            $em->persist($objComisionJuegosDatos);
            $em->flush(); 
            $swAnswer = true;
            
        } catch (Exception $e) {
            $swAnswer = false;
        }

        return $swAnswer;
    }

    private function savePerson($form){
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        $swAnswer = false;
        try {
            // $objPerson = new Persona();
            // $objPerson->setCarnet();
            // $objPerson->setComplemento();
            // $objPerson->setFechaNacimiento();
            // $objPerson->setNombre();
            // $objPerson->setPaterno();
            // $objPerson->setMaterno();

            // $em->persist($objPerson);
            // $em->flush();            
            
            // $swAnswer = $objPerson->getId();
            $swAnswer = 111111;
        } catch (Exception $e) {
            $swAnswer = false;
        }
        return $swAnswer;
    }

}
