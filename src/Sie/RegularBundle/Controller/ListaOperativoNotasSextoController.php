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
        // $query = $em->getConnection()->prepare("SELECT i.id, jg.distrito_tipo_id, i.institucioneducativa
        //     FROM institucioneducativa i 
        //     INNER JOIN jurisdiccion_geografica jg  on i.le_juridicciongeografica_id = jg.id
        //     WHERE jg.distrito_tipo_id = $distrito
        //     AND i.orgcurricular_tipo_id = 1
        //     AND i.estadoinstitucion_tipo_id = 10
        //     ORDER BY i.id ASC");
        // $query->execute();
        // $infoUE = $query->fetchAll();

        // $array = [];
        // foreach ($infoUE as $value) {
        //     $operativoLog = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLog')->findOneBy(array(
        //         'gestionTipoId'=>$gestion,
        //         'institucioneducativa'=>$value['id'],
        //         'institucioneducativaOperativoLogTipo'=>10
        //     ));

        //     if ($operativoLog) {
        //         $array[] = array(
        //             'id'=>$value['id'],
        //             'distrito_tipo_id'=>$value['distrito_tipo_id'],
        //             'institucioneducativa'=>$value['institucioneducativa'],
        //             'gestion_tipo_id'=>$gestion,
        //             'fecha_registro'=>$operativoLog->getFechaRegistro(),
        //             'estado'=>1
        //         );
        //     }else{
        //         $array[] = array(
        //             'id'=>$value['id'],
        //             'distrito_tipo_id'=>$value['distrito_tipo_id'],
        //             'institucioneducativa'=>$value['institucioneducativa'],
        //             'gestion_tipo_id'=>$gestion,
        //             'fecha_registro'=>'',
        //             'estado'=>0
        //         );
        //     }
        // }

        // $infoUE = $array;
        $query = $em->getConnection()->prepare("
            select distinct on (ie.id, ie.institucioneducativa)
            ie.id,
            ie.institucioneducativa,
            jg.distrito_tipo_id,
            ieol.gestion_tipo_id,
            ieol.fecha_registro,
            ieol.institucioneducativa_operativo_log_tipo_id as estado
            from institucioneducativa ie
            inner join jurisdiccion_geografica jg on ie.le_juridicciongeografica_id = jg.id
            left join institucioneducativa_operativo_log ieol on ieol.institucioneducativa_id = ie.id
            where jg.distrito_tipo_id = :distrito
            and ie.orgcurricular_tipo_id = 1
            and ie.estadoinstitucion_tipo_id = 10
            and ieol.gestion_tipo_id = :gestion
            order by ie.id asc, ie.institucioneducativa, ieol.institucioneducativa_operativo_log_tipo_id desc");
        $query->bindValue('gestion', $gestion);
        $query->bindValue('distrito', $distrito);
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
