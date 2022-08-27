<?php

namespace Sie\TecnicaEstBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;
use Sie\AppWebBundle\Entity\EstTecAutoridadUniversidad;
use Sie\AppWebBundle\Entity\Persona;


class MainInfoTecEstController extends Controller{
    public $session;
    public $idInstitucion;
    public $router;
    public $baseData;
    /**
     * the class constructor
     */
    public function __construct() {

        $this->session = new Session();
        $this->baseData = array('sedeId' => $this->session->get('sedeId'),  'userId' => $this->session->get('userId'));
    }
    /** krlos
     * create close operativo form
     * @return type obj form
     */    
    public function indexAction(){
    	$em      = $this->getDoctrine()->getManager();
    	$data    = bin2hex(serialize($this->baseData));
    	$objSede = $em->getRepository('SieAppWebBundle:EstTecSede')->find($this->baseData['sedeId']);
        $enablePersonalStaffOption = ($objSede->getEstTecSedeTipo()->getId()==1)?true:false;
        
        if (!isset($this->baseData['sedeId'])) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SieTecnicaEstBundle:MainInfoEstTec:index.html.twig', array(
            'tuicion'                   => true,
            'enablePersonalStaffOption' => $enablePersonalStaffOption,
            'uni_staff'          		=> $this->buildOptionUni('staff_index', 'Personal Ejecutivo', $data)->createView(),
            'uni_infosede'       		=> $this->buildOptionUni('sie_university_sede_index', 'Informacion Sede/sub Sede Central', $data)->createView(),
            'uni_statisticssede' 		=> $this->buildOptionUni('sie_university_sede_docenteadministrativo_index', 'Estadisitica Sede/sub Sede Central', $data)->createView(),
            'uni_statistics'     		=> $this->buildOptionUni('carreras_index', 'Estadisticas', $data)->createView(),

            ));    
    }

    /** krlos
     * create close operativo form
     * @return type obj form
     */
    private function buildOptionUni($goToPath, $nextButton, $data) {
        $form =  $this->createFormBuilder()
                        ->setAction($this->generateUrl($goToPath))
                        ->add('data', 'hidden', array('data' => $data));
        $form =$form->add('next', 'submit', array('label' => "$nextButton", 'attr' => array('class' => 'btn btn-primary btn-md btn-block')));
        $form = $form->getForm();
        return $form;
    }    

}
