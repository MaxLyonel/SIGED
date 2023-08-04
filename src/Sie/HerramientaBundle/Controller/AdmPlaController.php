<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Entity\EmparejaSiePlanilla;
use Sie\AppWebBundle\Entity\SolucionComparacionPlanillaTipo;
use Sie\AppWebBundle\Entity\NuevoMaestroInscripcion;

class AdmPlaController extends Controller{
    public function index111Action(){
        return $this->render('SieHerramientaBundle:AdmPla:index.html.twig', array(
                // ...
            ));    
    }

    public $session;
    public $limitDay;
    public $month;
    public function __construct() {
        $this->session = new Session();
        // $this->limitDay = '30-06-2023';
        $this->month = [
        	array('id'=>1, 	'month'=>'enero'),
        	array('id'=>2, 	'month'=>'febrero'),
        	array('id'=>3, 	'month'=>'marzo'),
        	array('id'=>4, 	'month'=>'abril'),
        	array('id'=>5, 	'month'=>'mayo'),
        	array('id'=>6, 	'month'=>'junio'),
        	array('id'=>7, 	'month'=>'julio'),
        	array('id'=>8, 	'month'=>'agosto'),
        	array('id'=>9, 	'month'=>'septiembre'),
        	array('id'=>10, 'month'=>'octubre'),
        	array('id'=>11, 'month'=>'noviembre'),
        	array('id'=>12, 'month'=>'diciembre'),
        ]     ;        
    }       

    public function indexAction(){
        
        $ie_id=$this->session->get('ie_id');
        
            $swregistry = false;
            $id_usuario = $this->session->get('userId');
            if (!isset($id_usuario)) {
                return $this->redirect($this->generateUrl('login'));
            }  
                  
            $disableElement=0;
            if(in_array($this->session->get('roluser'),array(9))){
                $sie=$ie_id=$this->session->get('ie_id');
                $disableElement=1;
            }else{
                if(in_array($this->session->get('roluser'),array(7,8,10))){
                    $sie='';
                }
            }
			

            return $this->render('SieHerramientaBundle:AdmPla:index.html.twig',array(
                'codsie'=>$sie,
                'disableElement'=>$disableElement,                
            ));        
    }

    public function getMainInfoAction(Request $request){
        $response = new JsonResponse();             

    
        $em = $this->getDoctrine()->getManager();
        // set the place info
        $departamento = 1;
        $distrito = 1;
        // get the user data
        $userId = $this->session->get('userId');
        // $userRol = $this->session->get('roluser');
        $userRol = $request->get('userRol');
        $sie = ($this->session->get('ie_id')>0)?$this->session->get('ie_id'):'';

        // $datosUser=$this->getDatosUsuario($userId,$userRol);

          $arrResponse = array(             
            'rol' => $userRol,
            'sie' => $sie,
            'arrYears'=>[array('id'=>2023, 'gestion'=>2023)],
            // 'arrMonths'=>$this->month,
            'arrMonths'=>$this->get('herrafunctions')->getAllowMonth($this->session->get('currentyear'), $this->month),
            'currentyear'=> $this->session->get('currentyear')            
          );
          
          $response->setStatusCode(200);
          $response->setData($arrResponse);        
          return $response;        
    } 


    public function findUEDataAction(Request $request){
    	
        // get the vars send        
        $sie = $request->get('sie');
        // create db conexion
        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
        $query->bindValue(':sie', $sie);
        $query->bindValue(':roluser', $this->session->get('roluser'));
        $query->execute();
        $aTuicion = $query->fetchAll();
        

       	// check if the exists UE
        $existUE = 0;
        $institucioneducativa=0;
        $objUE = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);
        
        if ($objUE){
            $existUE=1;
            $institucioneducativa = $objUE->getInstitucioneducativa();
        }

        // get cargo and financiamiento
        $arrCargo = $this->get('herrafunctions')->getCargo();
        $arrFinanciamiento = $this->get('herrafunctions')->getFinanciamiento();


        $arrResponse = array(
        	'sie' 				   => $sie,
            'existUE'              => $existUE,
            'arrCargo'             => $arrCargo,
            'arrFinanciamiento'    => $arrFinanciamiento,
            'institucioneducativa' => $institucioneducativa,
        ); 
        
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;        
    }

    public function getAllAdmiAction(Request $request){
        // create db conexion
        $em = $this->getDoctrine()->getManager();    	
    	// dump($request);die;
    	$arrData = [
    		'sie'=>$request->get('sie'),
    		'gestion'=>$request->get('gestion'),
    		'idMounth'=>$request->get('idMounth'),
    	];
        $arrAllAdm = $this->get('herrafunctions')->getAllAdm($arrData);
        // dump($arrAllAdm);die;
        $arrResponse = array(
        	'sie' 		=> $request->get('sie'),
            'existUE'   => 1,
            'arrAllAdm' => $arrAllAdm,
        ); 
        
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;          	
    }

    public function ratifyAction(Request $request){
        // create db conexion
        $em = $this->getDoctrine()->getManager();    	
        $idModify = $request->get('idModify');
        
        // $objEmparejaSiePlanilla = $em->getRepository('SieAppWebBundle:EmparejaSiePlanilla')->find($idModify);
        
    	$objEmpSiePlan = $em->getRepository('SieAppWebBundle:EmparejaSiePlanilla')->find($idModify);
    	$objEmpSiePlan->setSolucionComparacionPlanillaTipo($em->getRepository('SieAppWebBundle:SolucionComparacionPlanillaTipo')->find(1));
        $objEmpSiePlan->setFechaModificacion(new \DateTime('now'));

    	$em->persist($objEmpSiePlan);
        $em->flush(); 
    	$arrData = [
    		'sie'=>$request->get('sie'),
    		'gestion'=>$request->get('gestion'),
    		'idMounth'=>$request->get('idMounth'),
    	];
        $arrAllAdm = $this->get('herrafunctions')->getAllAdm($arrData);
        // dump($arrAllAdm);die;
        $arrResponse = array(
        	'sie' 		=> $request->get('sie'),
            'existUE'   => 1,
            'arrAllAdm' => $arrAllAdm,
        ); 
        
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response; 
    }

    public function deleteAdmAction(Request $request){
        // create db conexion
        $em = $this->getDoctrine()->getManager();    	
        $idModify = $request->get('idModify');
        
        // $objEmparejaSiePlanilla = $em->getRepository('SieAppWebBundle:EmparejaSiePlanilla')->find($idModify);
        
    	$objEmpSiePlan = $em->getRepository('SieAppWebBundle:EmparejaSiePlanilla')->find($idModify);
    	$objEmpSiePlan->setSolucionComparacionPlanillaTipo($em->getRepository('SieAppWebBundle:SolucionComparacionPlanillaTipo')->find(2));
    	$objEmpSiePlan->setObservacion($request->get('observation'));
        $objEmpSiePlan->setFechaModificacion(new \DateTime('now'));
    	$em->persist($objEmpSiePlan);
        $em->flush(); 
    	$arrData = [
    		'sie'=>$request->get('sie'),
    		'gestion'=>$request->get('gestion'),
    		'idMounth'=>$request->get('idMounth'),
    	];
        $arrAllAdm = $this->get('herrafunctions')->getAllAdm($arrData);
        // dump($arrAllAdm);die;
        $arrResponse = array(
        	'sie' 		=> $request->get('sie'),
            'existUE'   => 1,
            'arrAllAdm' => $arrAllAdm,
        ); 
        
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response; 
    }

    public function saveNewAdmAction(Request $request){

        // create db conexion
        $em = $this->getDoctrine()->getManager();    	
        // validate data with segip
        $arrParametros = array(
            'complemento'=>mb_strtoupper($request->get('complnew'), 'utf-8'),
            'primer_apellido'=>mb_strtoupper($request->get('patnew'), 'utf-8'),
            'segundo_apellido'=>mb_strtoupper($request->get('matnew'), 'utf-8'),
            'nombre'=>mb_strtoupper($request->get('nomnew'), 'utf-8'),
            'fecha_nacimiento'=>$request->get('fecnacnew')
        );
        if($request->get('extranjero') == 1){
            $arrParametros['extranjero'] = 'e';
        }      
        
        // get info segip
        $answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $request->get('cinew'),$arrParametros,'prod', 'academico');

        $swsegip = 0;
        if($answerSegip){
			$swsegip=1;
			$objNewMaster = new NuevoMaestroInscripcion();
			$objNewMaster->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($request->get('gestion')));
			$objNewMaster->setMesTipo($em->getRepository('SieAppWebBundle:MesTipo')->find($request->get('idMounth')));			
			$objNewMaster->setCi($request->get('cinew'));
			$objNewMaster->setComplemento($request->get('complnew'));
			$objNewMaster->setPaterno(mb_strtoupper( $request->get('patnew'), "utf-8") );
			$objNewMaster->setMaterno(mb_strtoupper( $request->get('matnew'), "utf-8") );
			$objNewMaster->setNombre(mb_strtoupper( $request->get('nomnew'), "utf-8") );
			$objNewMaster->setFechaNacimiento(new \DateTime($request->get('fecnacnew')));            
			
			$objNewMaster->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->find($request->get('financiamientoId')));
			$objNewMaster->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->find($request->get('cargoId')));
			$objNewMaster->setFechaCreacion(new \DateTime('now'));
            $objNewMaster->setInstitucioneducativaId($request->get('sie'));
            $objNewMaster->setObservacion(mb_strtoupper( $request->get('obsnew'), "utf-8") );

			$em->persist($objNewMaster);

			$objEmparejaSiePlanilla = new EmparejaSiePlanilla();
			$objEmparejaSiePlanilla->setGestionTipoId($request->get('gestion'));
			$objEmparejaSiePlanilla->setMesTipoId($request->get('idMounth'));
			$objEmparejaSiePlanilla->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->get('sie')));
			$objEmparejaSiePlanilla->setNuevoMaestroInscripcion($em->getRepository('SieAppWebBundle:NuevoMaestroInscripcion')->find($objNewMaster->getId()));
			$objEmparejaSiePlanilla->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->find($request->get('financiamientoId')));
			$objEmparejaSiePlanilla->setCargoTipoId(($request->get('cargoId')));
			$objEmparejaSiePlanilla->setSolucionComparacionPlanillaTipo($em->getRepository('SieAppWebBundle:SolucionComparacionPlanillaTipo')->find(3));

            $objEmparejaSiePlanilla->setFechaCreacion(new \DateTime('now'));
            
			$em->persist($objEmparejaSiePlanilla);

			$em->flush();

        }else{
        	$swsegip=0;
            $message = 'validacion segip: Los datos registrados no coinciden';
        }        

        
        
        // update info to show
    	// $objEmpSiePlan = $em->getRepository('SieAppWebBundle:EmparejaSiePlanilla')->find($idModify);
    	// $objEmpSiePlan->setSolucionComparacionPlanillaTipo($em->getRepository('SieAppWebBundle:SolucionComparacionPlanillaTipo')->find(3));
    	// $objEmpSiePlan->setObservacion($request->get('observation'));
    	// $em->persist($objEmpSiePlan);
     //    $em->flush(); 
    	$arrData = [
    		'sie'=>$request->get('sie'),
    		'gestion'=>$request->get('gestion'),
    		'idMounth'=>$request->get('idMounth'),
    	];
        $arrAllAdm = $this->get('herrafunctions')->getAllAdm($arrData);
        // dump($arrAllAdm);die;
        $arrResponse = array(
        	'sie' 		=> $request->get('sie'),
            'existUE'   => 1,
            'arrAllAdm' => $arrAllAdm,
            'swsegip' => $swsegip,
        ); 
        
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response; 
    }

    public function closeOpeAction(Request $request){
        // create db conexion
        $em = $this->getDoctrine()->getManager();    	
      
    	$arrData = [
    		'sie'=>$request->get('sie'),
    		'gestion'=>$request->get('gestion'),
    		'idMounth'=>$request->get('idMounth'),
    	];
        $arrDataObs = $this->get('herrafunctions')->closeOperative($arrData);
        // dump($arrDataObs);die;
        $arrResponse = array(
        	'sie' 		=> $request->get('sie'),
            'swrequest'   => 1,
            'arrDataObs' => $arrDataObs,
        ); 
        
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response; 
    }



}
