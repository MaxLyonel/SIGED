<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEliminados;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
use Sie\AppWebBundle\Entity\EstudianteHistorialModificacion; 

class NewInscriptionIniPriController extends Controller
{


    public $session;
    public $currentyear;
    public $userlogged;
    public $em;

     /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->currentyear = $this->session->get('currentyear');
        $this->userlogged = $this->session->get('userId');
        
    }
    // index method
    public function indexAction(Request $request){
    	$em = $this->getDoctrine()->getManager();
        //validation if the user is logged
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $arrExpedido = array();
         // this is to the new person
	      $objExpedido = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findAll();

	      foreach ($objExpedido as $value) {
	        $arrExpedido[$value->getId()] = $value->getSigla();
	      }

       	return $this->render('SieHerramientaBundle:NewInscriptionIniPri:index.html.twig', array(
       		'arrExpedido'=>$objExpedido,
               // ...
        ));
    }

    public function checksegipstudentAction(Request $request){
		// dump($request);
    	//ini vars
    	$response = new JsonResponse();
    	$em = $this->getDoctrine()->getManager();
    	//get send values
    	$carnet = $request->get('cifind');
    	$complemento = $request->get('complementofind');
    	$fecNac = $request->get('fecnacfind');
    	$paterno = $request->get('paterno');
    	$materno = $request->get('materno');
    	$nombre = $request->get('nombre');
    	$withoutcifind = ($request->get('withoutcifind')=='false')?false:true;
    	$expedidoIdfind = $request->get('expedidoIdfind');

    	$arrGenero = array();
    	$arrPais = array();
    	
    	// check if the inscription is by ci or not
		if($withoutcifind){
			$answerSegip = true;
		}else{
			// to do the segip validation
	    	$arrParametros = array(
		        'complemento'=>$complemento,
		        'primer_apellido'=>$paterno,
		        'segundo_apellido'=>$materno,
		        'nombre'=>$nombre,
		        'fecha_nacimiento'=>$fecNac
	      	);
	      	
			$answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $carnet,$arrParametros,'prod', 'academico');
		}
		

      // check if the data person is true
      if($answerSegip){
      	// validate the year old on the student
      	$arrYearStudent =$this->get('funciones')->getTheCurrentYear($fecNac, '30-6-'.date('Y'));
        $yearStudent = $arrYearStudent['age'];
        // dump($yearStudent);die;
        // check if the student is on 5 - 8 years old
        if($yearStudent<=8 && $yearStudent>=5){

        	 // get genero data
		     $objGenero = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();
		      foreach ($objGenero as $value) {
		          if($value->getId()<3){
		              $arrGenero[] = array('generoId' => $value->getId(),'genero' => $value->getGenero());
		          }
		      }

		            //get pais data
		      $objPais = $em->getRepository('SieAppWebBundle:PaisTipo')->findAll();
		      foreach ($objPais as $value) {
		        $arrPais[]=array('paisId'=>$value->getId(), 'pais'=>$value->getPais());
		      }


        	$status = 'success';
            $code = 200;
            $message = "Estudiante cumple con los requerimientos!!!";
            $swcreatestudent = true; 


        }else{
        	$status = 'error';
			$code = 400;
			$message = "Estudiante no cumple con la edad requerida 5 a 8";
			$swcreatestudent = false; 
        }
      }else{
			$status = 'error';
			$code = 400;
			$message = "Estudiante no cumple con la validacion segip";
			$swcreatestudent = false; 
      }


       $arrResponse = array(
        'status'          => $status,
        'code'            => $code,
        'message'         => $message,
        'swcreatestudent' => $swcreatestudent,    
        'arrGenero' => $arrGenero,    
        'arrPais' => $arrPais,    
        
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;

      die;
    }

    public function getDeptoAction(Request $request){
    	// dump($request);
    	// die;
        $response = new JsonResponse();
        $paisId = $request->get('paisId');
        $em = $this->getDoctrine()->getManager();
        // get departamento
        if ($paisId == 1) {
            $condition = array('lugarNivel' => 1, 'paisTipoId' => $paisId);
        } else {
            $condition = array('lugarNivel' => 8, 'id' => '79355');
        }
        $objDepto = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy($condition);
        $arrDepto = array();
        foreach ($objDepto as $depto) {
            $arrDepto[]=array('deptoId'=>$depto->getId(),'depto'=>$depto->getLugar());
        }

        $response->setStatusCode(200);
        $response->setData(array(
            'arrDepto' => $arrDepto,
        ));
       
        return $response;        


    }    
    public function getProvinciaAction(Request $request){
    
      $em = $this->getDoctrine()->getManager();
      $response = new JsonResponse();
      $lugarNacTipoId = $request->get('lugarNacTipoId');

      // / get provincias
      $objProv = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $lugarNacTipoId));

      $arrProvincia = array();
      foreach ($objProv as $prov) {
          $arrProvincia[] = array('provinciaId'=>$prov->getid(), 'provincia'=>$prov->getlugar());
      }

        $response->setStatusCode(200);
      $response->setData(array(
          'arrProvincia' => $arrProvincia,
      ));
     
      return $response;

    }

    /**
     * get the paralelto, turno and sie name
     * @param type $id like sie
     * @param type $n like nivel
     * @param type $g like grado
     * @return type
     */
    public function getInfoUeAction(Request $request) {
    	// dump($request);
    	// die;
    	$response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('sie');
        //get the tuicion
        //select * from get_ue_tuicion(137746,82480002)
        /*
          $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT)');
          $query->bindValue(':user_id', $this->session->get('userId'));
          $query->bindValue(':sie', $id);
          $query->execute();
          $aTuicion = $query->fetchAll();
         */
        $aniveles = array();
        // if ($aTuicion[0]['get_ue_tuicion']) {
        //get the IE
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
        if($institucion){
        	
	        $em = $this->getDoctrine()->getManager();
	        //get the Niveles

	        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
	        $query = $entity->createQueryBuilder('iec')
	                ->select('(iec.nivelTipo)')
	                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
	                ->where('iec.institucioneducativa = :sie')
	                ->andwhere('iec.gestionTipo = :gestion')
	                ->andwhere('iec.nivelTipo != :nivel')
	                ->setParameter('sie', $id)
	                ->setParameter('gestion', $this->session->get('currentyear') - 1)
	                ->setParameter('nivel', '13')
	                ->distinct()
	                ->getQuery();
	        $aNiveles = $query->getResult();
	        if($aNiveles){
		        $aniveles = array();
		        foreach ($aNiveles as $nivel) {
		            $aniveles[] = array('id'=> $nivel[1], 'level'=>$em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel[1])->getNivel());
		        }
		        $status = 'success';
				$code = 200;
				$message = "Datos encontrados";
				$swprocess = true; 
				$nombreIE = ($institucion) ? $institucion->getInstitucioneducativa() : "";
	        }else{
	        	$status = 'error';
				$code = 400;
				$message = "no tiene level";
				$swprocess = false; 
				$nombreIE = false; 
	        }
	        

        }else{

        	$status = 'error';
			$code = 400;
			$message = "Unidad Educativa no existe";
			$swprocess = false; 
			$nombreIE = false; 

        }
        
        
        /*     } else {
          $nombreIE = 'No tiene Tuición';
          } */

      $arrResponse = array(
        'status'          => $status,
        'code'            => $code,
        'message'         => $message,
        'swprocessue' => $swprocess,    
        'nombreue' => $nombreIE, 
        'aniveles' => $aniveles
        );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;

        
		$response->setStatusCode(200);
        return $response->setData(array());
    }        





}
