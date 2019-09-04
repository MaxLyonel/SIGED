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
use Sie\JuegosBundle\Controller\EstudianteInscripcionJuegosController;
use Sie\JuegosBundle\Controller\ReglaController;
use Sie\AppWebBundle\Entity\JdpEstudianteInscripcionJuegos;
use Sie\AppWebBundle\Entity\JdpEquipoEstudianteInscripcionJuegos;
use Sie\AppWebBundle\Entity\JdpPersonaInscripcionJuegos;
use Sie\AppWebBundle\Entity\ComisionJuegosDatos;
use Sie\AppWebBundle\Entity\JdpDelegadoInscripcionJuegos;
use Sie\AppWebBundle\Entity\Persona;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Config\Definition\Exception\Exception;



/**
 * Author: krlos Pacha C. <pckrlos@cgmail.com>
 * Description: This is a class for reporting the student,acompaniante & delegadfo credencial; if exist the correct permissions
 * Date: 02-09-2019
 *
 *
 * class: PrintCredencialController
 *
 * Email bugs/suggestions to pckrlos@cgmail.com
 */
class PrintCredencialController extends Controller{

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
     * Function historyAction
     *
     * @author krlos Pacha C. <pckrlos@cgmail.com>
     * @access public
     * @param string codigoRude
     * @return form
     */
    public function indexAction(Request $request){

        //validation if the user is logged
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }        
        return $this->render('SieJuegosBundle:PrintCredencial:index.html.twig', array(
                'form' =>  $this->indexForm()->createView(),
            ));    
    }


    private function indexForm(){
        $arrCriteria = array('Estudiante','Acompañante','Delegado, Jefe de mision y organizador');
    
    return $this->createFormBuilder()
        ->add('carnetRude', 'text', array('label'=>'CI/RUDE:', 'attr'=>array('class'=>'form-control','placeholder'=>'Carnet Identidad/Rude')))
        ->add('typeOption','choice',
                      array('label' => 'Tipo',
                            'choices' => ($arrCriteria),
                            'required' => true,
                            'empty_value' => 'Seleccionar valor',
                            'attr' => array('class' => 'form-control')))
        // ->add('jsonIdInscription',           'hidden', array('attr'=>array('value'=>$jsonData)))     
        ->add('complemento', 'text', array('label' => 'complemento', 'attr' => array('placeholder' => 'Ingresar Complemento','class' => 'form-control', 'required' => false,'style' => 'text-transform:uppercase', 'maxlength'=>'2')))
        ->add('find', 'button', array('label'=>'Buscar', 'attr'=>array('onclick'=>'lookForDataPerson()','class'=>'btn btn-success')))
        ->getForm()
        ;
    }

    /**
     * Function historyAction
     *
     * @author krlos Pacha C. <pckrlos@cgmail.com>
     * @access public
     * @param string codigoRude
     * @return form
     */    
    public function lookforCredencialAction(Request $request){
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        // get the send values
        $form = $request->get('form');
        
        $objComisionJuegosDatos = false;
        $entity = false;
        $pathToShowImg = false;
        $typeMessage = 'success';
        
        switch ($form['typeOption']) {
            case 0:
                //get student data
                $entity = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['carnetRude']));
                $objComisionJuegosDatos = $this->getCurrentInscriptionsByGestoinValida($form['carnetRude'],$this->session->get('currentyear'));                
                break;
            case 1:
                $objComisionJuegosDatos = array();
                $entity = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('carnet'=>$form['carnetRude'], 'complemento'=>$form['complemento']));
                $objDatosAll = $em->getRepository('SieAppWebBundle:JdpPersonaInscripcionJuegos')->findBy(array('persona'=>$entity->getId()));
                // dump($this->session->get('currentyear'));
                foreach ($objDatosAll as $key => $value) {
                    $objComisionJuegosDatos[] = $this->getJuegosInscriptionsByGestoinValida($value->getEstudianteInscripcionJuegos()->getId(),$this->session->get('currentyear'));
                }
                break;
            case 2:
                # code...
                $message = 'Datos existentes';
                $typeMessage = 'success';
                $this->addFlash('lookForDataMessage', $message);
                $data = $this->getDelegadoData($form);
                $entity = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('carnet'=>$form['carnetRude'], 'complemento'=>$form['complemento']));
                $objComisionJuegosDatos = $em->getRepository('SieAppWebBundle:JdpDelegadoInscripcionJuegos')->findOneBy(array('persona'=>$entity->getId()));
                if($objComisionJuegosDatos){
                    // list($pathSever,$pathImg) = explode('web', $objComisionJuegosDatos->getRutaImagen());
                    $pathToShowImg = $objComisionJuegosDatos->getRutaImagen();
                }
                break;
            
            default:
                # code...
                break;
        }

        // dump($form['typeOption']);
        // dump($objComisionJuegosDatos);
        // die;
        return $this->render('SieJuegosBundle:PrintCredencial:lookforCredencial.html.twig', array(
                'entity' => $entity,
                'form' => $form,
                'dataCommission' => array(),
                'objComisionJuegosDatos' => $objComisionJuegosDatos,
                'typeMessage' => $typeMessage,
                'pathToShowImg' => $pathToShowImg,
                // 'typeOption' => $form['typeOption'],
            ));    
    }
    private function getJuegosInscriptionsByGestoinValida($id, $gestion) {
        

        //$session = new Session();
        $swInscription = false;
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('i,jeij,e')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')

                ->leftjoin('SieAppWebBundle:JdpEstudianteInscripcionJuegos', 'jeij', 'WITH', 'ei.id = jeij.estudianteInscripcion')

                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('jeij.id = :id')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('ei.estadomatriculaTipo IN (:mat)')
                ->andwhere('jeij.faseTipo = :faseTipo')
                ->setParameter('id', $id)
                ->setParameter('gestion', $gestion)
                ->setParameter('mat', array( 3,4,5,6,10 ))
                ->setParameter('faseTipo', 2)
                ->orderBy('iec.gestionTipo', 'DESC')
                ->getQuery();

            $objInfoInscription = $query->getResult();
            if(sizeof($objInfoInscription)>=1)
              return $objInfoInscription;
            else
              return false;

    }    

    private function getCurrentInscriptionsByGestoinValida($id, $gestion) {
        

        //$session = new Session();
        $swInscription = false;
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('i,jeij')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')

                ->leftjoin('SieAppWebBundle:JdpEstudianteInscripcionJuegos', 'jeij', 'WITH', 'ei.id = jeij.estudianteInscripcion')

                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('e.codigoRude = :id')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('ei.estadomatriculaTipo IN (:mat)')
                ->andwhere('jeij.faseTipo = :faseTipo')
                ->setParameter('id', $id)
                ->setParameter('gestion', $gestion)
                ->setParameter('mat', array( 3,4,5,6,10 ))
                ->setParameter('faseTipo', 2)
                ->orderBy('iec.gestionTipo', 'DESC')
                ->getQuery();

            $objInfoInscription = $query->getResult();
            if(sizeof($objInfoInscription)>=1)
              return $objInfoInscription;
            else
              return false;

    }

    private function getDelegadoData($data){
        $em = $this->getDoctrine()->getManager();
         $entity = $em->getRepository('SieAppWebBundle:Persona');
        $query = $entity->createQueryBuilder('per')
                ->select('per.id as personId, per.carnet, per.complemento, per.paterno, per.materno,per.nombre,jdij.id as id, IDENTITY(jdij.comisionTipo) as comisionTipoId, IDENTITY(jdij.lugarTipo) as lugarTipoId, ct.comision as comisionTipo, lt.lugar as lugarTipo, jdij.rutaImagen, jdij.obs')  
                ->join('SieAppWebBundle:JdpDelegadoInscripcionJuegos', 'jdij', 'WITH', 'per.id = jdij.persona')
                ->join('SieAppWebBundle:ComisionTipo', 'ct', 'WITH', 'jdij.comisionTipo = ct.id')
                ->join('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jdij.lugarTipo = lt.id');
         
        $query = $query  
                 ->where('per.carnet = :carnet')
                 ->setParameter('carnet', $data['carnetRude']);
        if($data['complemento']){
            $query = $query  
                 ->where('per.complemento = :complemento')
                 ->setParameter('complemento', $data['complemento']);
        }
        $query = $query
                ->orderBy('lt.lugar', 'ASC')
                ->getQuery();
                // dump($query->getSQL());die;
        $entities = $query->getResult();
        return $entities;
    }

    /**
     * Function historyAction
     *
     * @author krlos Pacha C. <pckrlos@cgmail.com>
     * @access public
     * @param string codigoRude
     * @return form
     */
    public function donwloadAction(Request $request, $selectedReport, $id){
        //che the type report to download
        switch ($selectedReport) {
            // STUDENT
            case 0:
                # code...
                $reportDownload = 'reg_lst_EstudiantesInscritos_UnidadEducativa_gral_v1.rptdesign&ue=40730460&gestion=2019&&__format=pdf&';
                break;
            // acompaniante    
            case 1:
                # code...
                $reportDownload = 'reg_lst_EstudiantesInscritos_UnidadEducativa_gral_v1.rptdesign&ue=40730460&gestion=2019&&__format=pdf&';
                break;
            // Delegado
            case 2:
                # code...
                $reportDownload = 'reg_lst_EstudiantesInscritos_UnidadEducativa_gral_v1.rptdesign&ue=40730460&gestion=2019&&__format=pdf&';            
                break;
            
            default:
                # code...
                break;
        }

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'list_ue_3333_2019.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $reportDownload));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;



        // return $this->render('SieJuegosBundle:PrintCredencial:donwload.html.twig', array(
        //         // ...
        //     ));    
    }

}
