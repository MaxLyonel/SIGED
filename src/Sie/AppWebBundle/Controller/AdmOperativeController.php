<?php

namespace Sie\AppWebBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Entity\InstitucioneducativaSucursalTramite;
use Sie\AppWebBundle\Entity\OperativoControl;
use Sie\AppWebBundle\Entity\PeriodoEstadoTipo;
use Sie\AppWebBundle\Entity\TramiteEstado;
use Sie\AppWebBundle\Entity\TramiteTipo;
use Doctrine\ORM\EntityRepository;


use Symfony\Component\HttpKernel\Bundle\Bundle;

class AdmOperativeController extends Controller{

    public $session;
    public $oc;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    /**
     * index the request
     * @param Request $request
     * @return obj with the selected request 
     */
    public function indexAction(Request $request) {
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $gestion = date('Y');
        //dump($gestion);die;
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        
        return $this->render($this->session->get('pathSystem') . ':AdmOperative:index.html.twig', array(
            'form' => $this->operativoForm()->createView(),
        ));
    }

    public function loadDataAction( Request $request ){
    	
    	$response = new JsonResponse();
        // $idInscription = $request->get('idInscription', null);
        
        $em = $this->getDoctrine()->getManager();
        // get parents list
        $arrDeptos = $this->get('permafunctions')->getDeptos();
        $arrOperatives = $this->get('permafunctions')->getOperatives();
    	// get person bjp

    	return $response->setData([
            'status'=>'success',
            'datos'=>array(
                'arrDeptos'=>$arrDeptos,             
                'arrOperatives'=>$arrOperatives,             
            )
        ]);

    }   

    public function requestCentrosAction(Request $request){
    	dump($request);
    	die;
    }


    public function operativoForm()
    {   
        $idlugarusuario = $this->session->get('roluserlugarid');
        $rolusuario = $this->session->get('roluser');
        $this->gestion = date('Y');
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
        ->add('operativo','entity',array('label'=>'Operativo:','required'=>true,'class'=>'SieAppWebBundle:OperativoTipo','query_builder'=>function(EntityRepository $o){
            return $o->createQueryBuilder('o')->where("o.institucioneducativaTipo=2 ");},'property'=>'operaTivo','empty_value' => 'Seleccione operativo','attr'=>array('class'=>'form-control')))
        ->add('fechainicio','text',array('label'=>'Fecha inicio: (dia-mes-año)','required'=>true,'data'=>date('d-m-Y'), 'attr'=>array('class'=>'form-control datepicker','placeholder'=>'dd-mm-AAAA','maxlength'=>10,'minlength'=>10,'autocomplete'=>'off')))
        ->add('fechafin','text',array('label'=>'Fecha fin: (dia-mes-año)','required'=>true,'data'=>date('d-m-Y'), 'attr'=>array('class'=>'form-control datepicker','placeholder'=>'dd-mm-AAAA','maxlength'=>10,'minlength'=>10,'autocomplete'=>'off')))
        ->add('gestion','entity',array('label'=>'Gestión:','required'=>true,'class'=>'SieAppWebBundle:GestionTipo','query_builder'=>function(EntityRepository $g){
            return $g->createQueryBuilder('g')->where('g.id='.$this->gestion)->orderBy('g.id');},'property'=>'gestion','empty_value' => false,'attr'=>array('class'=>'form-control')));
        if($rolusuario == 8){
            $form = $form
            ->add('departamento','entity',array('label'=>'Departamento:','required'=>true,'class'=>'SieAppWebBundle:DepartamentoTipo','query_builder'=>function(EntityRepository $d){
                return $d->createQueryBuilder('d')->where('d.id not in (0)')->orderBy('d.departamento');},'property'=>'departamento','empty_value' => false,'attr'=>array('class'=>'form-control','onchange'=>'distrito(this.value)')));
        }elseif($rolusuario == 7){
            $departamento = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idlugarusuario)->getCodigo();
            $form =$form
            ->add('departamento','hidden',array('data'=>$departamento));
        }elseif($rolusuario == 10){
            $distrito = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idlugarusuario)->getCodigo();
            $form = $form
            ->add('distrito','hidden',array('data'=>$distrito));
        }
        $form = $form
        ->add('buscar', 'button', array('label'=> 'Buscar Ceas', 'attr'=>array('class'=>'form-control btn btn-success','onclick'=>'buscarCeas()')))
        ->getForm();
        return $form;
    }    








    public function index1Action()
    {
        return $this->render('SieAppWebBundle:AdmOperative:index.html.twig', array(
                // ...
            ));    }

}
