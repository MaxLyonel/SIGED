<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * EstudianteInscripcion controller.
 *
 */
class InboxRequestController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    /**
     * list of request
     *
     */
    public function indexAction(Request $request) {
        //create the db connexion
        $em=$this->getDoctrine()->getManager();
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //get the sie and the name of sie
        $arrSieInfo = $this->getUserSie($this->session->get('personaId'), $this->session->get('currentyear'));
        //get the ue plena info
        $objValidateUePlena=array();
        if($arrSieInfo)
          $objValidateUePlena = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId' => $arrSieInfo[0]['id']));
        else
          $arrSieInfo = $this->getUserInfo($this->session->get('personaId'), $this->session->get('currentyear'));
        //get the current year
        $arrSieInfo[0]['gestion']=$this->session->get('currentyear');

        //get the fuill ue info
        $arrFullUeInfo=array();
        $arrFullUeInfo['sieinfo'] =$arrSieInfo[0];
        //$arrFullUeInfo['ueplenainfo'] =$objValidateUePlena;

        //$objValidateUePlena = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId' => $data['sie']));
        return $this->render($this->session->get('pathSystem') . ':InboxRequest:index.html.twig', array(
            'objValidateUePlena'=>($objValidateUePlena)?1:0,
            'arrSieInfo'=>$arrSieInfo[0],
            'gestion'=>$this->session->get('currentyear'),
            'form'=> $this->formUePlena(serialize($arrFullUeInfo))->createView()
        ));
    }
    /**
    *buill the ue plena form
    */
    private function formUePlena($data){
      return $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_inbox_open'))
            ->add('data', 'hidden', array('data'=>$data))
            ->add('goplena', 'submit', array('label' => "Ingrese Opciòn Unidad Educativa Plena", 'attr' => array('class' => 'btn btn-success')))
            ->getForm();
    }
    /**
     * get info about user
     * parameters: codigo user
     * @author krlos
     */
    private function getUserInfo($persona, $gestion){

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Persona');
        $query = $entity->createQueryBuilder('p')
                ->select('p.carnet as id, p.paterno as datainfo')
                ->where('p.id = :persona')
                ->setParameter('persona', $persona)
                ->getQuery();
        //print_r($query);die;
        try {
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $exc) {
            //echo $exc->getTraceAsString();
            return array();
        }
    }
    /**
     * get info about UE
     * parameters: codigo user
     * @author krlos
     */
    private function getUserSie($persona, $gestion) {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $entity->createQueryBuilder('mi')
                ->select('i.id as id, i.institucioneducativa as datainfo')
                ->leftJoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'mi.institucioneducativa = i.id')
                ->where('mi.persona = :persona')
                ->andwhere('mi.gestionTipo = :gestion')
                ->setParameter('persona', $persona)
                ->setParameter('gestion', $gestion)
                ->getQuery();
        //print_r($query);die;
        try {
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $exc) {
            //echo $exc->getTraceAsString();
            return array();
        }
    }
    /**
     * open the request
     * @param Request $request
     * @return obj with the selected request
     */
    public function openAction(Request $request) {
        //get session data
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //get the values
        $form = $request->get('form');
        $data = unserialize($form['data']);

        //get the sie and the name of sie
        $arrSieInfo = $this->getUserSie($this->session->get('personaId'), $this->session->get('currentyear'));

        if(!$arrSieInfo)
          $arrSieInfo = $this->getUserSieUe($this->session->get('personaId'), '2015');
        /*if(!$arrSieInfo)
          $arrSieInfo = array('id'=>'81940034');*/
        $dataUe = $arrSieInfo[0];
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':InboxRequest:open.html.twig', array(
                    'uEducativaform' => $this->InfoStudentForm('herramienta_ieducativa_index', 'Unidad Educativa', $dataUe)->createView(),
                    'personalAdmform' => $this->InfoStudentForm('herramienta_info_personal_adm_index', 'Personal Administrativo',$dataUe)->createView(),
                    'infoMaestroform' => $this->InfoStudentForm('herramienta_info_maestro_index', 'Información Maestro',$dataUe)->createView(),
                    'infotStudentform' => $this->InfoStudentForm('herramienta_info_estudianterequest_index', 'Insformación Estudiante',$dataUe)->createView(),
                    'mallaCurricularform' => $this->InfoStudentForm('herramienta_change_paralelo_sie_index', 'Cambio de Paralelo', $dataUe)->createView()
        ));
    }

    /**
     * get info about UE
     * parameters: codigo user
     * @author krlos
     */
    private function getUserSieUe($persona, $gestion) {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $entity->createQueryBuilder('mi')
                ->select('i.id as id, i.institucioneducativa as datainfo')
                ->leftJoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'mi.institucioneducativa = i.id')
                ->where('mi.persona = :persona')
                ->andwhere('mi.gestionTipo = :gestion')
                ->setParameter('persona', $persona)
                ->setParameter('gestion', $gestion)
                ->getQuery();
        //print_r($query);die;
        try {
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $exc) {
            //echo $exc->getTraceAsString();
            return array();
        }
    }
    /**
     * create form Student Info to send values
     * @return type obj form
     */
    private function InfoStudentForm($goToPath, $nextButton, $data) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl($goToPath))
                        ->add('gestion', 'hidden', array('data' => '2015'))
                        ->add('sie', 'hidden', array('data' => $data['id']))//81880091
                        ->add('next', 'submit', array('label' => "$nextButton", 'attr' => array('class' => 'btn btn-link')))
                        ->getForm()
        ;
    }
    
}
