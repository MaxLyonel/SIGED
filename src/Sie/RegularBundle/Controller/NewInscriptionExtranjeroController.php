<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEliminados;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
use Sie\AppWebBundle\Entity\EstudianteHistorialModificacion; 
use Sie\AppWebBundle\Entity\EstudianteInscripcion; 

use Sie\AppWebBundle\Entity\Estudiante; 
use Sie\AppWebBundle\Entity\EstudianteInscripcionExtranjero; 
use Symfony\Component\Validator\Constraints\DateTime;

class NewInscriptionExtranjeroController extends Controller{
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
    private function esGuanawek($ie_id,$gestion){
      $return=false;
      $tecnico_humanistico=4; //institucioneducativa_humanistico_tecnico_tipo_id 
      $departamentos=array();
      $em = $this->getDoctrine()->getManager();
      $db = $em->getConnection();
      $query = '
      select * 
      from 
      institucioneducativa_humanistico_tecnico 
      where 
      institucioneducativa_humanistico_tecnico_tipo_id = ? 
      and gestion_tipo_id = ?
      and institucioneducativa_id = ?';

      $stmt = $db->prepare($query);
      $params = array($tecnico_humanistico,$gestion,$ie_id);
      $stmt->execute($params);
      $guanawek=$stmt->fetchAll();

      if($guanawek)
        $return=true;

      return $return;
    }      

    public function indexAction(Request $request){
      //disabled option by krlos
      //return $this->redirect($this->generateUrl('login'));
      if (in_array($this->session->get('roluser'), array(9))){
          return $this->redirect($this->generateUrl('login'));
      }
     if (in_array($this->session->get('roluser'), array(8,7,10))){

     }else{
      //to do the ue cal diff
      if(!($this->esGuanawek($this->session->get('ie_id'),$gestion=2020))){
        return $this->redirect($this->generateUrl('login'));
      }
     }

    	$em = $this->getDoctrine()->getManager();
        //validation if the user is logged
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }
			$enableoption = true; 
			$message = ''; 
         //this is to check if the ue has registro_consolidacion
         if($this->session->get('roluser')==9){

        	$objRegConsolidation =  $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
         		'unidadEducativa' => $this->session->get('ie_id'),  'gestion' => $this->session->get('currentyear')
         	));
        	
	         if($objRegConsolidation){
	             $status = 'error';
              $code = 400;
              $message = "No se puede realizar la inscripción debido a que la Unidad Educativa ya consolidó el operativo de Inscripción  ". $this->session->get('currentyear')." ";
              $enableoption = false; 
	         }
         }       
        
        $arrExpedido = array();
         // this is to the new person
	      $objExpedido = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findAll();

	      foreach ($objExpedido as $value) {
	        $arrExpedido[$value->getId()] = $value->getSigla();
	      }
          // get CedulaTipo
        $objCedula = $em->getRepository('SieAppWebBundle:CedulaTipo')->findAll();
        $arrCedula = array();
        $arrCedula[0] = array('cedulaTipoId' => 1,'cedulaTipo' => 'Nacional'); 
        $arrCedula[1] = array('cedulaTipoId' => 2,'cedulaTipo' => 'Extranjero'); 
	   
	    $userAllowedOnwithoutCI = in_array($this->session->get('roluser'), array(7,8,10))?true:false;
      //HerramientaBundle/NewInscriptionEstranjero:index.html.twig
      
       	return $this->render($this->session->get('pathSystem') .':NewInscriptionExtranjero:index.html.twig', array(
       		'arrExpedido'=>$objExpedido,
          'arrCedula' => $arrCedula,
       		'allowwithoutci' => $userAllowedOnwithoutCI,
       		'enableoption' => $enableoption,
       		'message' => $message,
               // ...
        ));    	
          
    }

    public function checksegipstudentAction(Request $request){
    	
    	//ini vars
    	$response = new JsonResponse();
    	$em = $this->getDoctrine()->getManager();
    	//get send values
    	$carnet = trim($request->get('cifind'));
    	$complemento = trim($request->get('complementofind'));
    	$fecNac = trim($request->get('fecnacfind'));
    	$paterno = trim($request->get('paterno'));
    	$materno = trim($request->get('materno'));
    	$nombre = trim($request->get('nombre'));
    	$withoutcifind = ($request->get('withoutcifind')=='false')?false:true;
    	$expedidoIdfind = $request->get('expedidoIdfind');
      $cedulaTipoId = $request->get('cedulaTipoId');

      //dcastillo 2402
      // para validacion segip
      $tipo_persona = $request->get('tipo_persona');

    	$arrGenero = array();
    	$arrPais = array();
		$arrStudentExist = false;
		$studentId = false;
		$existStudent = '';
    	// check if the inscription is by ci or not

    	// list($day, $month, $year) = explode('-', $fecNac);
			$arrayCondition['paterno'] = mb_strtoupper($paterno,'utf-8');
			$arrayCondition['materno'] = mb_strtoupper($materno,'utf-8');
			$arrayCondition['nombre']  = mb_strtoupper($nombre,'utf-8');
			$arrayCondition['fechaNacimiento'] = new \DateTime(date("Y-m-d", strtotime($fecNac))) ;
			$objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findBy($arrayCondition);

		if($withoutcifind && ($carnet=='' || !$carnet )){	

			// find the student by arrayCondition
			$objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findBy($arrayCondition);
			$existStudent = false;
			if(sizeof($objStudent)>0){
				$existStudent=true;				
			}

			$answerSegip = true;
		}else{
			$arrayCondition['carnetIdentidad'] = $carnet;
			if($complemento){
				$arrayCondition['complemento'] = $complemento;
			}else{
				$arrayCondition['complemento'] = "";
			}
			// dump($arrayCondition);die;

			// find the student by arrayCondition
			$objStudentCi = $em->getRepository('SieAppWebBundle:Estudiante')->findBy($arrayCondition);
			// dump($objStudent);die;
			$existStudent = false;
			if(sizeof($objStudentCi)>0){
				$existStudent=true;		
        $answerSegip = true;				
			}
			if(!$existStudent){
				// to do the segip validation
		    	$arrParametros = array(
			        'complemento'=>$complemento,
			        'primer_apellido'=>$paterno,
			        'segundo_apellido'=>$materno,
			        'nombre'=>$nombre,
			        'fecha_nacimiento'=>$fecNac,
              'tipo_persona' => $tipo_persona
		      	);
		      if($cedulaTipoId == 2){
              $arrParametros['extranjero'] = 'E';
          }
				$answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $carnet,$arrParametros,'prod', 'academico');
			}
      if($answerSegip && sizeof($objStudent)>0){
				$existStudent=true;				
			}

		}
    if(sizeof($objStudent)>0){
			$existStudent=true;				
		}
		// dump($objStudent);
		// die;
		// check if the student exists
		if(!$existStudent){
		      // check if the data person is true
		      if($answerSegip){
		      	// validate the year old on the student
		      	$arrYearStudent =$this->get('funciones')->getTheCurrentYear($fecNac, '30-6-'.date('Y'));
		        $yearStudent = $arrYearStudent['age'];
            //dump($yearStudent);die;
		        // check if the student is on 5 - 8 years old
              if( $yearStudent > 3 ){
                  $dataGenderAndCountry = $this->getGenderAndCountry();
                  $arrGenero = $dataGenderAndCountry['gender'];
                  $arrPais 	 = $dataGenderAndCountry['country'];
                  $status = 'success';
                  $code = 200;
                  $message = "Estudiante cumple con los requerimientos!!!";
                  $swcreatestudent = true; 
              }else{
                $status = 'error';
                $code = 400;
                $message = "Estudiante no cumple con la edad requerida";
                 $swcreatestudent = false; 
              }
		        
		      }else{
					$status = 'error';
					$code = 400;
					$message = "Estudiante no cumple con la validacion segip";
					$swcreatestudent = false; 
		      }

		}else{

			$arrStudentExist = array();
			if(sizeof($objStudent)>0){
				foreach ($objStudent as $value) {
					$arrStudentExist[] = array(
						'paterno'=>$value->getPaterno(),
						'materno'=>$value->getMaterno(),
						'nombre'=>$value->getNombre(),
						'carnet'=>$value->getCarnetIdentidad(),
						'complemento'=>$value->getComplemento(),
						'fecNac'=>$value->getFechaNacimiento()->format('d-m-Y') ,
						'rude'=>$value->getCodigoRude() ,
						'idStudent'=>$value->getId() ,
					);
				}
				
			}

			// $studentId = $objStudent->getId();
			$existStudent = true;

			$status = 'error';
			$code = 400;
			$message = "Estudiante ya tiene registro, favor realizar la inscripción por el modulo de Omitidos/Transferencia";
			$swcreatestudent = false; 

		}

      if($this->session->get('roluser')==9){ //si es director verificar que el operativo no este cerrado
        $objRegConsolidation =  $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
              'unidadEducativa' => $this->session->get('ie_id'),  'gestion' => $this->session->get('currentyear')
            ));
          if($objRegConsolidation){
              $status = 'error';
              $code = 400;
              $message = "No se puede realizar la inscripción debido a que la Unidad Educativa ya cerró su operativo de Inscripción";
            $swcreatestudent = false; 
          }
      }
		
       $arrResponse = array(
        'status'          => $status,
        'code'            => $code,
        'message'         => $message,
        'swcreatestudent' => $swcreatestudent,    
        'arrGenero' => $arrGenero,    
        'arrPais' => $arrPais,    
        'arrStudentExist' => $arrStudentExist,    
        'existStudent' => $existStudent,    
        'swhomonimo' => $withoutcifind,
        
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;

      die;
    }

    public function lookforrudeAction(Request $request){
	    //dump($request);die;
	    //create db conexion
	    $em = $this->getDoctrine()->getManager();
	     $response = new JsonResponse();
   // get the send values 
	    $rude = $request->get('rude');
	    $arrGenero = array();
	    $arrPais = array();
	    $withoutcifind=0;
   	 $arrayCondition['codigoRude'] = $rude;
                        // find the student by arrayCondition
                        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy($arrayCondition);
                        //dump($objStudent);die;
                        $existStudent = false;
                        if(sizeof($objStudent)>0){
                                $existStudent=true;
                        }


  
                          $arrStudentExist = array();
                          if(sizeof($objStudent)>0){
                                          $arrStudentExist[] = array(
                                                  'paterno'=>$objStudent->getPaterno(),
                                                  'materno'=>$objStudent->getMaterno(),
                                                  'nombre'=>$objStudent->getNombre(),
                                                 'carnet'=>$objStudent->getCarnetIdentidad(),
                                                  'complemento'=>$objStudent->getComplemento(),
                                                  'fecNac'=>$objStudent->getFechaNacimiento()->format('d-m-Y') ,
                                                  'fecnacfind'=>$objStudent->getFechaNacimiento()->format('d-m-Y') ,
                                                  'rude'=>$objStudent->getCodigoRude() ,
                                                  'idStudent'=>$objStudent->getId() ,
                                          );
                                 
  
			  }
  
                          // $studentId = $objStudent->getId();
                          $existStudent = true;
  
                          $status = 'error';
                          $code = 400;
                          $message = "Estudiante ya tiene registro, favor realizar la inscripción por el modulo de Omitidos/Transferencia";
                          $swcreatestudent = false;
 
  
         $arrResponse = array(
          'status'          => $status,
          'code'            => $code,
          'message'         => $message,
          'swcreatestudent' => $swcreatestudent,
          'arrGenero' => $arrGenero,
          'arrPais' => $arrPais,
          'arrStudentExist' => $arrStudentExist,
          'existStudent' => $existStudent,
          'swhomonimo' => $withoutcifind,
  
        );
  
        $response->setStatusCode(200);
        $response->setData($arrResponse);
  
        return $response;

    }

    private function getCurrentInscriptionsByGestoinValida($id, $gestion) {
    //$session = new Session();
        $swInscription = false;
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
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
                ->andWhere('i.institucioneducativaTipo = :typeUE')
                ->setParameter('id', $id)
                ->setParameter('gestion', $gestion)
                ->setParameter('mat', array( 3,4,5,6,10 ))
                ->setParameter('typeUE', 1)
                ->orderBy('iec.gestionTipo', 'DESC')
                ->getQuery();

            $objInfoInscription = $query->getResult();
            if(sizeof($objInfoInscription)>=1)
              return true;
            else
              return false;

      }    


    public function gohomonimoAction(Request $request){
    	$response = new JsonResponse();
    	$em = $this->getDoctrine()->getManager();
    	//get send values
    	$carnet = $request->get('cifind');
    	$complemento = $request->get('complementofind');
    	$fecNac = $request->get('fecnacfind');
    	$paterno = $request->get('paterno');
    	$materno = $request->get('materno');
    	$nombre = $request->get('nombre');
    	$withoutcifind = true;
    	$expedidoIdfind = $request->get('expedidoIdfind');
		
		$swnewforeign = $request->get('swnewforeign');
		$swCurrentInscription = false;
		if($swnewforeign == 0){
			
			/*validate students inscripción in current year*/
			$rude = $request->get('rude');
			$swCurrentInscription = $this->getCurrentInscriptionsByGestoinValida($rude,$this->currentyear);
			//$swCurrentInscription = $this->getCurrentInscriptionsByGestoinValida($rude,$this->currentyear-1);
		

		}else{

		}
		


    	$arrGenero = array();
    	$arrPais = array();
		$arrStudentExist = false;
		$existStudent = true;
		$answerSegip = true;

		if(!$swCurrentInscription){
			
			$dataGenderAndCountry = $this->getGenderAndCountry();
			$arrGenero = $dataGenderAndCountry['gender'];
			$arrPais 	 = $dataGenderAndCountry['country'];
			$status = 'success';
			$code = 200;
			$message = "Estudiante cumple con los requerimientos!!!";
			$swcreatestudent = true; 

			$arrStudentExist = array(
					'paterno'=>$request->get('paterno'),
					'materno'=>$request->get('materno'),
					'nombre'=>$request->get('nombre'),
					'carnet'=>$request->get('cifind'),
					'complemento'=>$request->get('complementofind'),
					'fecNac'=>$request->get('fecnacfind'),
					'rude'=>'' ,
			);
		}else{

			$status = 'error';
			$code = 200;
			$message = "ESTUDIANTE YA CUENTA CON INSCRIPCIÓN EN LA GESTION ACTUAL";
			$swcreatestudent = false; 

		}

	
       $arrResponse = array(
        'status'          => $status,
        'code'            => $code,
        'message'         => $message,
        'swcreatestudent' => $swcreatestudent,    
        'arrGenero' => $arrGenero,    
        'arrPais' => $arrPais,    
        'arrStudentExist' => $arrStudentExist,    
        'existStudent' => $existStudent,    
        'swhomonimo' => !$swCurrentInscription,  
        
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;    	
				
    }

    public function getGenderAndCountry(){
    		$em = $this->getDoctrine()->getManager();
	        	 // get genero data
	     $objGenero = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();
	      foreach ($objGenero as $value) {
	          if($value->getId()<3){
	              $arrGenero[] = array('generoId' => $value->getId(),'genero' => $value->getGenero());
	          }
	      }
	      $arrData['gender'] = $arrGenero;

	            //get pais data
	      $objPais = $em->getRepository('SieAppWebBundle:PaisTipo')->findAll();
	      foreach ($objPais as $value) {
	        $arrPais[]=array('paisId'=>$value->getId(), 'pais'=>$value->getPais());
	      }
	      $arrData['country'] = $arrPais;

	      return $arrData;
    }

    public function getDeptoAction(Request $request){
    	
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
	                //->andwhere('iec.nivelTipo != :nivel')
	                ->setParameter('sie', $id)
	                ->setParameter('gestion', $this->session->get('currentyear') )
	                //->setParameter('nivel', '13')
	                ->orderBy('iec.nivelTipo', 'ASC')
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
            $message = "Información no consolidada -- no tiene level";
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

    /**
     * get grado
     * @param type $idnivel
     * @param type $sie
     * return list of grados
     */
    public function setGradoAction(Request  $request) {
    	// ini vars
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        // get the send values
        $sie = $request->get('sie');
        $idnivel = $request->get('nivelId');
        $gestionselected = $this->session->get('currentyear') ;

        //get grado
        $agrados = array();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.gradoTipo)')
                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :idnivel')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('idnivel', $idnivel)
                ->setParameter('gestion', $gestionselected)
                ->distinct()
                ->orderBy('iec.gradoTipo', 'ASC')
                ->getQuery();
        $aGrados = $query->getResult();

        $agrados = array();
        
        foreach ($aGrados as $grado) {
        	$agrados[$grado[1]] = array('id'=>$grado[1], 'grado'=>$em->getRepository('SieAppWebBundle:GradoTipo')->find($grado[1])->getGrado() );
        }
      $arrResponse = array(
      	'arrGrado' => $agrados
      );
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
        
    }

    /**
     * get the paralelos
     * @param type $idnivel
     * @param type $sie
     * @return type
     */
    public function setParaleloAction(Request $request) {
    	// ini vars
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        // get the send values
        $sie = $request->get('sie');
        $nivel = $request->get('nivelId');
        $grado = $request->get('gradoId');
        $gestion = $this->session->get('currentyear') ;
		//get paralelo
        $aparalelos = array();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.paraleloTipo)')
                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('gestion', $gestion)
                ->distinct()
                ->orderBy('iec.paraleloTipo', 'ASC')
                ->getQuery();
        $aParalelos = $query->getResult();
        $aparalelos = array();
        foreach ($aParalelos as $paralelo) {
        	$aparalelos[$paralelo[1]] = array('id'=>$paralelo[1], 'paralelo'=>$em->getRepository('SieAppWebBundle:ParaleloTipo')->find($paralelo[1])->getParalelo() );    
        }

      $arrResponse = array(
      	'arrParalelo' => $aparalelos
      );
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
    }

    /**
     * get the turnos
     * @param type $turno
     * @param type $sie
     * @param type $nivel
     * @param type $grado
     * @return type
     */
    public function setTurnoAction(Request $request) {
        // ini vars
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        // get the send values
        $sie = $request->get('sie');
        $nivel = $request->get('nivelId');
        $grado = $request->get('gradoId');
        $paralelo = $request->get('paraleloId');
        $gestion = $this->session->get('currentyear') ;
		//get turno
        $aturnos = array();

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.turnoTipo)')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andWhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('gestion', $gestion)
                ->distinct()
                ->orderBy('iec.turnoTipo', 'ASC')
                ->getQuery();
        $aTurnos = $query->getResult();
        foreach ($aTurnos as $turno) {
            $aturnos[] =array('id'=>$turno[1], 'turno'=> $em->getRepository('SieAppWebBundle:TurnoTipo')->find($turno[1])->getTurno());
        }

      $arrResponse = array(
      	'arrTurno' => $aturnos
      );
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
    }

    /**
     * to add the areas to the student
     * @param type $studentInscrId
     * @param type $newCursoId
     * @return boolean
     */
    private function addAreasToStudent($studentInscrId, $newCursoId) {
        $em = $this->getDoctrine()->getManager();
        $areasEstudiante = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array(
            'estudianteInscripcion' => $studentInscrId,
            'gestionTipo' => $this->session->get('currentyear')
        ));
        //if doesnt have areas we'll fill these
        if (!$areasEstudiante) {
            $objAreas = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array(
                'insitucioneducativaCurso' => $newCursoId
            ));
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');");
            $query->execute();
            foreach ($objAreas as $areas) {
                //print_r($areas->getAsignaturaTipo()->getId());
                $studentAsignatura = new EstudianteAsignatura();
                $studentAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
                $studentAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscrId));
                $studentAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($areas->getId()));
                $studentAsignatura->setFechaRegistro(new \DateTime('now'));
                $em->persist($studentAsignatura);
                $em->flush();
                //echo "<hr>";
            }
        }
        return true;
    }     

    /**
     * todo the registration of traslado
     * @param Request $request
     *
     */
    public function doInscriptionExtAction(Request $request) {
    	// dump($_POST);
    	// dump($request);
    	// dump($_FILES);
    	// die;    	
    	$arrDatos = json_decode($request->get('datos'), true);
    	//dump($arrDatos);    	die;
    	 // ini vars
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();        
        // get the send values
        //get info ABOUT UE
        $sie = $arrDatos['sie'];
        $nivel = $arrDatos['nivelId'];
        $grado = $arrDatos['gradoId'];
        $paralelo = $arrDatos['paraleloId'];
        $turno = $arrDatos['turnoId'];
        $gestion = $this->session->get('currentyear') ;
        // get info about student
       
        $paterno = mb_strtoupper($arrDatos['paterno'], 'utf-8') ;
        $materno = mb_strtoupper($arrDatos['materno'], 'utf-8');
        $nombre = mb_strtoupper($arrDatos['nombre'], 'utf-8');
        

        $carnet = isset($arrDatos['cifind'])?$arrDatos['cifind']:'';
        $complemento = isset($arrDatos['complementofind'])?$arrDatos['complementofind']:'';
        
        $withoutcifind = ($arrDatos['withoutcifind']==false)?false:true;
        $swnewforeign = $arrDatos['swnewforeign'];
          if($swnewforeign == 1){
            $fecNac = $arrDatos['fecnacfind'];
            $cedulaTipoId =$arrDatos['cedulaTipoId'];
          }else{
            $fecNac = $arrDatos['fecNac'];
          }

        // validation if the ue is over 4 operativo
        $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);
            
		$swinscription=true;
        if($operativo >= 3){
            $status = 'error';
			$code = 400;
			$message = "No se puede realizar la inscripción debido a que para la Unidad Educativa seleccionada ya se consolidaron todos los operativos";
			$swinscription = false; 
        }

          //validation inscription in the same U.E
          // $objCurrentInscriptionStudent = $this->getCurrentInscriptionsByGestoinValida($aDataStudent['codigoRude'],$aDataStudent['gestion']);

          //   if($objCurrentInscriptionStudent){
          //     if ($objCurrentInscriptionStudent[0]['sie']==$form['institucionEducativa']){
          //       $this->session->getFlashBag()->add('notiext', 'Estudiante ya cuenta con inscripción en la misma Unidad Educativa, realize el cambio de estado');
          //       return $this->redirect($this->generateUrl('inscription_extranjeros_index'));
          //     }
          //   }
          //check if the user can do the inscription
          //validate allow access
            $arrAllowAccessOption = array(7,8);
            if(!in_array($this->session->get('roluser'),$arrAllowAccessOption)){
              $defaultController = new DefaultCont();
              $defaultController->setContainer($this->container);

              $swAccess = $defaultController->getAccessMenuCtrl(array('sie'=>$sie, 'gestion'=>$gestion));
              //validate if the user download the sie file
              if($swAccess){
                $status = 'error';
                $code = 400;
                $message = "No se puede realizar la inscripción debido a que ya descargo el archivo SIE";
                $swinscription = false; 
              }
            }
         

            $arrStudent=array();
            $arrInscription=array();
            // check the years old validation
            if($swinscription){

		       	$arrQuery = array(
	                'nivelTipo' => $nivel,
	                'gradoTipo' => $grado,
	                'paraleloTipo' => $paralelo,
	                'turnoTipo' => $turno,
	                'institucioneducativa' => $sie,
	                'gestionTipo' => $gestion,
	            );

	            //insert a new record with the new selected variables and put matriculaFinID like 5
	            $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy($arrQuery);

		    try {
			       $institucionEducativaDe = $arrDatos['institucionEducativaDe'];
                               $cursoVencido = $arrDatos['cursoVencido'];
 				$paisId = $arrDatos['paisId'];
					if($swnewforeign == 1){
				        // get info about ubication
				        $paisId = $arrDatos['paisId'];
				        $localidad = mb_strtoupper($arrDatos['localidad'], 'utf-8');
				        $lugarNacTipoId = isset($arrDatos['lugarNacTipoId'])?$arrDatos['lugarNacTipoId']:'';
				        $lugarProvNacTipoId = isset($arrDatos['lugarProvNacTipoId'])?$arrDatos['lugarProvNacTipoId']:'';						
                $fecNac = $arrDatos['fecnacfind'];
                $genero = $arrDatos['generoId'];
                $expedidoId = $arrDatos['expedidoIdfind'];
                $institucionEducativaDe = $arrDatos['institucionEducativaDe'];
                $cursoVencido = $arrDatos['cursoVencido'];
						// create rude code to the student              
		                $query = $em->getConnection()->prepare('SELECT get_estudiante_nuevo_rude(:sie::VARCHAR,:gestion::VARCHAR)');
		                $query->bindValue(':sie', $sie);            
		                $query->bindValue(':gestion', $gestion);
		                $query->execute();
		                $codigorude = $query->fetchAll();

		                $codigoRude = $codigorude[0]["get_estudiante_nuevo_rude"];  
		                
		                // set the data person to the student table
		                $estudiante = new Estudiante();
		                // set the new student
		                $estudiante->setCodigoRude($codigoRude);               
		                $estudiante->setPaterno(mb_strtoupper($paterno, 'utf-8'));
		                $estudiante->setMaterno(mb_strtoupper($materno, 'utf-8'));
		                $estudiante->setNombre(mb_strtoupper($nombre, 'utf-8'));                        
		                $estudiante->setFechaNacimiento(new \DateTime($fecNac));            
		                $estudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($genero));
		                $estudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($paisId));
		                // check if the country is Bolivia
		                if ($paisId == '1'){                    
		                    $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($lugarNacTipoId));
		                    $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($lugarProvNacTipoId));
		                    $estudiante->setLocalidadNac(mb_strtoupper($localidad, 'utf-8'));
		                }else{//no Bolivia
		                    $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find('11'));
		                    $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find('11'));
		                    $estudiante->setLocalidadNac('');
		                }

		                if(!$withoutcifind){
			                $estudiante->setCarnetIdentidad($carnet);
			                $estudiante->setComplemento(mb_strtoupper($complemento, 'utf-8'));
			                $estudiante->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($expedidoId));
			                $estudiante->setSegipId(1);
                      if($cedulaTipoId>0)
                        $estudiante->setCedulaTipo($em->getRepository('SieAppWebBundle:CedulaTipo')->find($cedulaTipoId));
		                }else{
                      			$estudiante->setCarnetIdentidad('');
		                	$estudiante->setComplemento('');
		                	$estudiante->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find(0));
		                }

                    
		                
		                $em->persist($estudiante);
		                $foreignStudentId = $estudiante->getId();
		                $foreignCodigoRude = $estudiante->getCodigoRude();

		               
					}else{
						$foreignStudentId = $arrDatos['idStudent'];
						$foreignCodigoRude = $arrDatos['rude'];
					}


              $id_usuario = $this->session->get('userId');

	            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
	            $query->execute();
	            //insert a new record with the new selected variables and put matriculaFinID like 5
	            $studentInscription = new EstudianteInscripcion();
	           // $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));
	           // $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
	            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
	            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($foreignStudentId));
	            $studentInscription->setObservacion(1);
	            $studentInscription->setFechaInscripcion(new \DateTime('now'));
	            $studentInscription->setFechaRegistro(new \DateTime('now'));
	            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objCurso->getId()));
	            $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(19));
              $studentInscription->setUsuarioId($id_usuario);

	            $arrStudent = array(
	            	'rude'=>$foreignCodigoRude,
	            	'nombre'=>$nombre,
	            	'paterno'=>$paterno,
	            	'materno'=>$materno,
	            	'fecNac'=>$fecNac
	            );

	            $arrInscription = array(
	            	'nivelTipo' => $objCurso->getNivelTipo()->getNivel(),
	                'gradoTipo' => $objCurso->getGradoTipo()->getGrado(),
	                'paraleloTipo' => $objCurso->getParaleloTipo()->getParalelo(),
	                'turnoTipo' => $objCurso->getTurnoTipo()->getTurno(),
	                'sie' => $sie,
	                'institucioneducativa' => $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie)->getInstitucioneducativa() ,
	            );

	

	            $em->persist($studentInscription);
	            $em->flush();  

              //add the areas to the student
              //$responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $objCurso->getId());    
              //$query = $em->getConnection()->prepare('SELECT * from sp_genera_estudiante_asignatura(:estudiante_inscripcion_id::VARCHAR)');
              //$query->bindValue(':estudiante_inscripcion_id', $studentInscription->getId());
             // $query->execute();
                
             $query = $em->getConnection()->prepare('SELECT * from sp_crea_estudiante_asignatura_regular(:sie::VARCHAR, :estudiante_inscripcion_id::VARCHAR)');
             $query->bindValue(':estudiante_inscripcion_id', $studentInscription->getId());
             $query->bindValue(':sie', $sie);
             $query->execute();

	            if($swnewforeign == 1 or $swnewforeign==0){
	            	 // save the file in case if exists
	                    // check if the file exists
			        	if(isset($_FILES['informe'])){
			                $file = $_FILES['informe'];
			                $type = $file['type'];
			                $size = $file['size'];
			                $tmp_name = $file['tmp_name'];
			                $name = $file['name'];
			                $extension = explode('.', $name);
			                $extension = $extension[count($extension)-1];
			                $new_name = $studentInscription->getId().'_'.date('YmdHis').'.'.$extension;
			                // GUARDAMOS EL ARCHIVO
			                $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/insExtranjeros/' .date('Y');

			                if (!file_exists($directorio)) {
			                    mkdir($directorio, 0775, true);
			                }
			                $directoriomove = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/insExtranjeros/' .date('Y').'/'.$studentInscription->getId();
			                if (!file_exists($directoriomove)) {
			                    mkdir($directoriomove, 0775, true);
			                }

			                $archivador = $directoriomove.'/'.$new_name;
			                //unlink($archivador);
			                if(!move_uploaded_file($tmp_name, $archivador)){
			                    $em->getConnection()->rollback();
		            			echo 'Excepción capturada: ', $ex->getMessage(), "\n";
			                }

			                  //move the file emp to the directory temp
		                      // $file = $oFile->move($dirtmp, $originalName);
		                      // $file = $oFile->move($dirtmp, $studentInscription->getId().'_'.$form['gestion']);
		                      //save info extranjero inscription
		                      $objEstudianteInscripcionExtranjero = new EstudianteInscripcionExtranjero();
		                      $objEstudianteInscripcionExtranjero->setInstitucioneducativaOrigen($institucionEducativaDe);
		                      $objEstudianteInscripcionExtranjero->setCursoVencido($cursoVencido);
		                      $objEstudianteInscripcionExtranjero->setRutaImagen($archivador);
		                      $objEstudianteInscripcionExtranjero->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription->getId()));
		                      $objEstudianteInscripcionExtranjero->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($paisId));
		                      $em->persist($objEstudianteInscripcionExtranjero);
		                      $em->flush();
			            
			            }else{
			                $informe = null;
			                $archivador = 'empty';
			            }

	            }        

	            // Registro de materia curso oferta en el log
	            $this->get('funciones')->setLogTransaccion(
	                $studentInscription->getId(),
	                'estudiante_inscripcion',
	                'C',
	                '',
	                '',
	                '',
	                'SIGED',
	                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
	            );
	            // Try and commit the transaction
	            $em->getConnection()->commit();

	            $status = 'success';
				$code = 200;
				$message = "Estudiante inscripto Correctamente";
				$swinscription = true; 

		        } catch (Exception $ex) {
		            $em->getConnection()->rollback();
		            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
		        }

		       

            }else{
            	$status = 'error';
				$code = 400;
				$message = "El estudiante no cumple con lo requerido en edad";
				$swinscription = false; 
            }



	  $arrResponse = array(
        'status'          => $status,
        'code'            => $code,
        'message'         => $message,
        'swinscription'   => $swinscription,       
        'arrStudent'      => $arrStudent,       
        'arrInscription'  => $arrInscription,       
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;

    }

    public function showHistoryAction(Request $request){
      // ini vars
      $response = new JsonResponse();
      $em = $this->getDoctrine()->getManager();
      //get the send data
      $rude = $request->get('rude');
      // set values to response
      $dataInscriptionR = array();
      $dataInscriptionA = array();
      $dataInscriptionE = array();
      $dataInscriptionP = [];

      if($rude){
      // // get all cardex info
        $query = $em->getConnection()->prepare("select * from sp_genera_estudiante_historial('" . $rude . "') order by gestion_tipo_id_raep desc, estudiante_inscripcion_id_raep desc;");
        $query->execute();
        $dataInscription = $query->fetchAll();
        
        foreach ($dataInscription as $key => $inscription) {
            switch ($inscription['institucioneducativa_tipo_id_raep']) {
                case '1':
                    $dataInscriptionR[$key] = $inscription;
                    break;
                case '2':
                    $dataInscriptionA[$key] = $inscription;
                    break;
                case '4':
                    $dataInscriptionE[$key] = $inscription;
                    break;
                case '5':
                if(($inscription['bloque_p'] == 1 && $inscription['parte_p'] == 1) || $inscription['parte_p'] == 14)$bloquep ='Segundo';
                if(($inscription['bloque_p'] == 1 && $inscription['parte_p'] == 2) || $inscription['parte_p'] == 15)$bloquep = 'Tercero';
                if(($inscription['bloque_p'] == 2 && $inscription['parte_p'] == 1) || $inscription['parte_p'] == 16)$bloquep = 'Quinto';
                if(($inscription['bloque_p'] == 2 && $inscription['parte_p'] == 2) || $inscription['parte_p'] == 17)$bloquep = 'Sexto';
                    $dataInscriptionP[] = array(
                      'gestion'=> $inscription['gestion_tipo_id_raep'],
                      'institucioneducativa'=> $inscription['institucioneducativa_raep'],
                      'partp'=> ($inscription['parte_p']==1 ||$inscription['parte_p']==2)?'Antiguo':'Actual',
                      'bloquep'=> $bloquep,
                      'fini'=> $inscription['fech_ini_p'],
                      'ffin'=> $inscription['fech_fin_p'],
                      'curso'=> $inscription['institucioneducativa_curso_id_raep'],
                      'matricula'=> $inscription['estadomatricula_p'],
                    );
                    break;
            }
        }
//         dump($dataInscriptionR);
//         dump($dataInscriptionA);
//         dump($dataInscriptionE);
//         dump($dataInscriptionP);
// die;
        $dataInscriptionR = (sizeof($dataInscriptionR)>0)?$dataInscriptionR:false;
        $dataInscriptionA = (sizeof($dataInscriptionA)>0)?$dataInscriptionA:false;
        $dataInscriptionE = (sizeof($dataInscriptionE)>0)?$dataInscriptionE:false;
        $dataInscriptionP = (sizeof($dataInscriptionP)>0)?$dataInscriptionP:false;

        $status = 'success';
        $code = 200;
        $message = "historial del estudiante!!!";
        $swhistory = true;   
      
      }else{
        $status = 'error';
        $code = 400;
        $message = "No existe estudiante";
        $swhistory = true;   
      }



      $arrResponse = array(
      'status'          => $status,
      'code'            => $code,
      'message'         => $message,
      'swhistory'       => $swhistory,
      'dataInscriptionR' => $dataInscriptionR,
      'dataInscriptionA' => $dataInscriptionA,
      'dataInscriptionE' => $dataInscriptionE,
      'dataInscriptionP' => $dataInscriptionP,
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;

    }    


   
}
