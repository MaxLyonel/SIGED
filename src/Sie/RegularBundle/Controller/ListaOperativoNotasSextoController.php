<?php

namespace Sie\RegularBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Modals\Login;
use \Sie\AppWebBundle\Entity\Usuario;
use \Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\UploadFileControl;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;
use Sie\AppWebBundle\Entity\Institucioneducativa;

class ListaOperativoNotasSextoController extends Controller{
    public $session;
    public $arrOperativos;
     /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $this->arrOperativos = array(99=>'Seleccionar',1=>'Inicio',2=>'1er Bim',3=>'2do Bim',4=>'3ro Bim',5=>'4to Bim',0=>'Rude');
    }

    public function indexAction(Request $request){
        //get the user session id
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SieRegularBundle:ListaOperativoNotasSexto:index.html.twig', array(
                'form' => $this->distritoform()->createView()
            ));    
    }

    public function distritoform(){
         $form = $this->createFormBuilder()
            // ->add('data', 'hidden', array('data'=>$data))
            ->add('find','button',array('label'=>'buscar', 'attr'=>array('class'=>'btn btn-success mr-5', 'onclick'=> 'findSieOperativo()') ))
            ->add('distrito', 'text', array('label' => 'Distrito', 'attr' => array('maxlength' => 4, 'class' => 'form-control popovers','placeholder'=>'Introduzca Distrito')))
            ->add('gestion', 'hidden', array('data' => $this->session->get('currentyear')  ) )
            ->getForm();
        return $form;
    }

    public function findAction(Request $request){ 
        //get the user session id
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //get the send values
        $form = $request->get('form');
        
        // create db c onexion
        $em = $this->getDoctrine()->getManager();
       
        $objUeOperativoCerrado = $this->getListaUeOperativoCerrado($form);
       // dump($objUeOperativoCerrado);die;
        $objDistrito = $em->getRepository('SieAppWebBundle:DistritoTipo')->find($form['distrito']);
        return $this->render('SieRegularBundle:ListaOperativoNotasSexto:find.html.twig', array(
                'objUeOperativoCerrado' => $objUeOperativoCerrado,
                'dataDistrito' => $objDistrito
            ));    
    }

     public function getListaUeOperativoCerrado($data) {
         $gestion = $data['gestion'];
         $distrito = $data['distrito'];
        
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT i.id,jg.distrito_tipo_id, i.institucioneducativa,ilog.gestion_tipo_id,ilog.fecha_registro, ilog.institucioneducativa_operativo_log_tipo_id,
            CASE WHEN(ilog.institucioneducativa_operativo_log_tipo_id = 10)THEN '1' ELSE '0' END as estado
            FROM institucioneducativa i 
            INNER JOIN jurisdiccion_geografica jg  on i.le_juridicciongeografica_id = jg.id
            INNER JOIN institucioneducativa_operativo_log ilog on i.id = ilog.institucioneducativa_id
            WHERE jg.distrito_tipo_id = $distrito and ilog.gestion_tipo_id >= $gestion");
        $query->execute();
        $infoUE = $query->fetchAll();
        return($infoUE); 


        // $query = $em->getConnection()->prepare("SELECT * from institucioneducativa i 
        // INNER JOIN institucioneducativa_operativo_log ilog on i.id = ilog.institucioneducativa_id
        // INNER JOIN jurisdiccion_geografica ju  on i.le_juridicciongeografica_id = ju.id  
        // WHERE ilog.institucioneducativa_operativo_log_tipo_id = 10
        // AND ilog.gestion_tipo_id >=  $gestion  and distrito_tipo_id =$distrito");
        // $query->execute();
        // $infoUE = $query->fetchAll();
        // return($infoUE);
        



        // $em = $this->getDoctrine()->getManager();
        // $Datos = $this->em->getRepository('SieAppWebBundle:Institucioneducativa')->createQueryBuilder('i')
        //         ->select('i.id, i.institucioneducativa')
        //         ->innerjoin('SieAppWebBundle:InstitucioneducativaOperativoLog', 'io', 'WITH', 'i.id = io.institucioneducativa')
        //         ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'jg', 'WITH', 'i.leJuridicciongeografica = jg.id')
        //         ->where('io.InstitucioneducativaOperativoLog = 10')
        //         ->andwhere('io.gestion = :gestion')
        //         ->andwhere('jg.distritoTipo = :id')
        //         ->setParameter('gestion',  $data['gestion'])
        //         ->setParameter('id', $data['distrito'])
        //         ->orderby('i.id')
        //         ->getQuery()
        //         ->getResult();
        // dump($Datos);die;
        //return $Datos->getQuery()->getResult();

    }
    public function downloadFileAction(){

        return $this->render('SieRegularBundle:ReviewUpSieFile:downloadFile.html.twig', array(
                // ...
            ));    }

}
