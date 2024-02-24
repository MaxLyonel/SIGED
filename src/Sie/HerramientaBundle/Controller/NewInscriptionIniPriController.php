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
use Sie\AppWebBundle\Entity\EstudianteInscripcion; 
use Sie\AppWebBundle\Entity\EstudianteAsignatura; 
use Sie\AppWebBundle\Entity\Estudiante; 
use Sie\AppWebBundle\Entity\EstudianteDocumento; 
use Symfony\Component\Validator\Constraints\DateTime;
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
    // index method

    public function indexAction(Request $request){
     //disabled option by krlos
    //  return $this->redirect($this->generateUrl('login'));
     if (in_array($this->session->get('roluser'), array(8,10,7,9))){
     }else{
     	//to do the ue cal diff
     	if(!($this->esGuanawek($this->session->get('ie_id'),$gestion=2020))){
     		return $this->redirect($this->generateUrl('login'));
     	}
     }
      

      $arrWeenhayec = array(
        61710004,
        61710014,
        61710021,
        61710022,
        61710028,
        61710031,
        61710036,
        61710038,
        61710041,
        61710042,
        61710043,
        61710062,
        61710063,
        61710068,
        61710076,
        61710077,
        61710083,
        61710084,
        61710085,
        61710086,
        61710087,
        61710088,
        61710089,
        61710090,
        61710091,
        61710092,
		61710093,
		80730370,
		81981667,
		61710097,
		61710098
      );

    //   if( in_array($this->session->get('ie_id'), $arrWeenhayec) or $this->session->get('roluser')==7){
    //     //nothing todo
    //   }else{
    //     return $this->redirect($this->generateUrl('principal_web'));
    //   }    	
     //return $this->redirect($this->generateUrl('principal_web'));
    	$em = $this->getDoctrine()->getManager();
        //validation if the user is logged
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }
			$enableoption = true; 
			$message = ''; 
        // this is to check if the ue has registro_consolidacion
         if($this->session->get('roluser')==9){

         	$objRegConsolidation =  $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
         		'unidadEducativa' => $this->session->get('ie_id'),  'gestion' => $this->session->get('currentyear')
         	));
        	//dump($objRegConsolidation);die;
	         if($objRegConsolidation){
	             $status = 'error';
		 		$code = 400;
		 		$message = "No  puede realizar la inscripción debido a que la Unidad Educativa ya consolido el operativo de Inscripciones ".$this->session->get('currentyear')." ";
		 		$enableoption = false; 
	         }
         }       
        
        $arrExpedido = array();
         // this is to the new person
	      $objExpedido = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findAll();

	      foreach ($objExpedido as $value) {
	        $arrExpedido[$value->getId()] = $value->getSigla();
	      }
		  $objCedula = $em->getRepository('SieAppWebBundle:CedulaTipo')->findAll();
		  $arrCedula = array();
		  $arrCedula[0] = array('cedulaTipoId' => 1,'cedulaTipo' => 'Nacional'); 
		  $arrCedula[1] = array('cedulaTipoId' => 2,'cedulaTipo' => 'Extranjero'); 
		
		//dump($this->session->get('pathSystem'));die;
	    $userAllowedOnwithoutCI = in_array($this->session->get('roluser'), array(7,8,10))?true:false;
       	return $this->render($this->session->get('pathSystem') .':NewInscriptionIniPri:index.html.twig', array(
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
    	$arrGenero = array();
    	$arrPais = array();
		$arrStudentExist = false;
		$studentId = false;
		$swci = false;
		$existStudent = '';
		$existHomonimo = '';
    	// check if the inscription is by ci or not

    	// list($day, $month, $year) = explode('-', $fecNac);
			$arrayCondition['paterno'] = mb_strtoupper($paterno,'utf-8');
			$arrayCondition['materno'] = mb_strtoupper($materno,'utf-8');
			$arrayCondition['nombre']  = mb_strtoupper($nombre,'utf-8');
			$arrayCondition['fechaNacimiento'] = new \DateTime(date("Y-m-d", strtotime($fecNac))) ;
			$objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findBy($arrayCondition);
		if($withoutcifind && ($carnet=='' || !$carnet )){	
			// find the student by arrayCondition
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
			        'fecha_nacimiento'=>$fecNac
		      	);
		      	
				if($cedulaTipoId == 2){
					$arrParametros['extranjero'] = 'E';
				}
		      	

				$answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $carnet,$arrParametros,'prod', 'academico');
			}
			if($answerSegip && sizeof($objStudent)>0){
				$existStudent = true;				
			}
		}

		 //dump($objStudent); 
		 //dump($existStudent);die;
		 //die;
		// check if the student exists
		if(!$existStudent){
		      // check if the data person is true
		    if($answerSegip){
		      	// validate the year old on the student
		      	$arrYearStudent =$this->get('funciones')->getTheCurrentYear($fecNac, '30-6-'.date('Y'));
		        $yearStudent = $arrYearStudent['age'];
			// check if the student is on 5 - 8 years old
			 $arrValidationYearOld = in_array($this->session->get('roluser'), array(7,8,10,9))?array(4,5,6,7,8,9,10,11,12,13,14,15,16,17,18):array(4,5,6);
			 
			 //if($yearStudent<=8 && $yearStudent>=4){
			 if(in_array( $yearStudent, $arrValidationYearOld )){
		        		
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
				$message = "Estudiante no cumple con la edad requerida.";
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
						'articuleten'=>0 ,
					);

					if($value->getCarnetIdentidad()===trim($request->get('cifind'))){
						$swci = true;
					}
				}
				
			}

			// $studentId = $objStudent->getId();
			$existStudent = true;

			$status = 'error';
			$code = 400;
			$message = "Estudiante ya tiene registro ";
			$swcreatestudent = false; 

		}

		/* TODO CAMBIAR LOGICA CUANDO ESTUDIANTE HISTORIAL SIN CI Y NUEVA INSCRIPCION CON CI
		$swCurrentInscription = $this->getCurrentInscriptionsByGestoinValida($objStudent->getId(),$this->session->get('currentyear'));
		//dump($swCurrentInscription);die;
		if($swCurrentInscription){
			$status = 'error';
			$code = 400;
			$message = "Estudiante ya cuenta con registro de Inscripción en la presente gestión";
			$swcreatestudent = false; 
		}
		*/

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

		//dump($swci);dump($arrStudentExist);die;
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
        'swci' => $swci,
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;

      //die;
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

    	$arrGenero = array();
    	$arrPais = array();
			$arrStudentExist = false;
			$existStudent = true;
			$answerSegip = true;

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
	
       $arrResponse = array(
        'status'          => $status,
        'code'            => $code,
        'message'         => $message,
        'swcreatestudent' => $swcreatestudent,    
        'arrGenero' => $arrGenero,    
        'arrPais' => $arrPais,    
        'arrStudentExist' => $arrStudentExist,    
        'existStudent' => $existStudent,    
        'swhomonimo' => true,  
        
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
                ->setParameter('id', $id)
                ->setParameter('gestion', $gestion)
                ->setParameter('mat', array( 3,4,5,6,10 ))
                ->orderBy('iec.gestionTipo', 'DESC')
                ->getQuery();

            $objInfoInscription = $query->getResult();
            if(sizeof($objInfoInscription)>=1)
              return true;
            else
              return false;

      }    


    public function goOldInscriptionAction(Request $request){ 
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
      	 $entity = $em->getRepository('SieAppWebBundle:PaisTipo');
	       $query = $entity->createQueryBuilder('pt')
	                ->orderBy('pt.pais', 'ASC')
	                ->getQuery();
	        $objPais = $query->getResult();
	       

	      // $objPais = $em->getRepository('SieAppWebBundle:PaisTipo')->findAll(array('pais'=>'ASC'));
	      foreach ($objPais as $value) {
	        $arrPais[]=array('paisId'=>$value->getId(), 'pais'=>$value->getPais());
	      }
	      $arrData['country'] = ($arrPais) ;

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
	
	            //get the tuicion
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $id);
            $query->bindValue(':roluser', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

        $aniveles = array();
         if ($aTuicion[0]['get_ue_tuicion'] ) {
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
	                ->setParameter('gestion', $this->session->get('currentyear') )
	                ->setParameter('nivel', '13')
	                ->orderBy('iec.nivelTipo', 'ASC')
	                ->distinct()
	                ->getQuery();
	        $aNiveles = $query->getResult(); 
			//mp($aNiveles);
	        if($aNiveles){
				
		        $aniveles = array();
		        foreach ($aNiveles as $nivel) {
		            $aniveles[] = array('id'=> $nivel[1], 'level'=>$em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel[1])->getNivel());
		        }
			//	dump($aniveles);die;
		        $status = 'success';
				$code = 200;
				$message = "Datos encontrados";
				$swprocess = true; 
				$nombreIE = ($institucion) ? $institucion->getInstitucioneducativa() : "";
	        }else{
	        	$status = 'error';
				$code = 400;
				$message = "Información no consolidada - el nivel de la UE no corresponde a Inicial/Primaria";
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
        
        
      } else {
	      $message = 'Usuario No tiene Tuición';
	       $status = 'error';
               $code = 400;
               //$message = "Unidad Educativa no existe";
               $swprocess = false;
               $nombreIE = false;

      } 

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
        	if($idnivel == 12 && $grado[1]==1){
				$agrados[$grado[1]] = array('id'=>$grado[1], 'grado'=>$em->getRepository('SieAppWebBundle:GradoTipo')->find($grado[1])->getGrado() );
			}
			if($idnivel == 11){
				$agrados[$grado[1]] = array('id'=>$grado[1], 'grado'=>$em->getRepository('SieAppWebBundle:GradoTipo')->find($grado[1])->getGrado() );
			}            
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
    public function doInscriptioninipriAction(Request $request) {
    	
    	$arrDatos = json_decode($request->get('datos'), true);
  	// dump($arrDatos);die;
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
        $swnewforeign = $arrDatos['swnewforeign'];
        //$articuleten = $arrDatos['articuleten'];
        //$swrezago = $arrDatos['swrezago'];
        $typeInscription = $arrDatos['typeInscription'];
        $gestion = $this->session->get('currentyear') ;

        $paterno = mb_strtoupper($arrDatos['paterno'], 'utf-8') ;
        $materno = mb_strtoupper($arrDatos['materno'], 'utf-8');
        $nombre = mb_strtoupper($arrDatos['nombre'], 'utf-8');
       
		    $fecNac = $arrDatos['fecnacfind'];

        $withoutcifind = ($arrDatos['withoutcifind']==false)?false:true;

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

            
            // $idStudent = $form ['idStudent'];
            // $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($idStudent);
            //validate the year of student
            // validate the year old on the student
	      	$arrYearStudent =$this->get('funciones')->getTheCurrentYear($fecNac, '30-6-'.date('Y'));
	        $yearStudent = $arrYearStudent['age'];		
		//dump($arrYearStudent);
		switch($typeInscription){
		case 0:
			$swinscription = $this->correctOldYearValidation($yearStudent,$nivel,$grado);
			//dump($swinscription);die;
			break;
		case 1:
			$swinscription = $this->articuletenYearValidation($yearStudent,$nivel,$grado);
			break;
		case 2:
			$swinscription = $this->rezagoYearValidation($yearStudent,$nivel,$grado);
			if($swnewforeign){
				//$swinscription = !$this->getCurrentInscriptionsByGestoinValida($arrDatos['rude'],$this->currentyear-1);
			}
			break;
		default:
			$swinscription = false;
			break;
		}
	
			
		
		/*
		if($articuleten){
	        	$iswinscription = $this->articuletenYearValidation($yearStudent,$nivel,$grado);
		}
		if($swrezago){
			$iswinscription = $this->rezagoYearValidation($yearStudent,$nivel,$grado);
		}
		if(!$articuleten && !$swrezago){
	        	$swinscription = $this->correctOldYearValidation($yearStudent,$nivel,$grado);
		}*/
//dump($yearStudent);
//dump($swinscription);
//die;
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

			             	if($swnewforeign){

			             		  // get info about student
								       /* $fecNac = $arrDatos['fecnacfind'];
								        $paterno = $arrDatos['paterno'];
								        $materno = $arrDatos['materno'];
								        $nombre = $arrDatos['nombre'];*/
						$genero = $arrDatos['generoId'];
						// get info about ubication
						$paisId = $arrDatos['paisId'];
						$localidad = $arrDatos['localidad'];
						$lugarNacTipoId = isset($arrDatos['lugarNacTipoId'])?$arrDatos['lugarNacTipoId']:'';
						$lugarProvNacTipoId = isset($arrDatos['lugarProvNacTipoId'])?$arrDatos['lugarProvNacTipoId']:'';
						$carnet = isset($arrDatos['cifind'])?$arrDatos['cifind']:'';
						$complemento = isset($arrDatos['complementofind'])?$arrDatos['complementofind']:'';
						$expedidoId = $arrDatos['expedidoIdfind'];
						$cedulaTipoId =$arrDatos['cedulaTipoId'];
						//$cedulaTipoId =1;
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
								$estudiante->setCarnetIdentidad($carnet); //se añadio por calidacion CI not null
				                if(!$withoutcifind){
									$estudiante->setCarnetIdentidad($carnet);

					                $estudiante->setComplemento(mb_strtoupper($complemento, 'utf-8'));
					                $estudiante->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($expedidoId));
					                $estudiante->setSegipId(1);
									if($cedulaTipoId>0)
										$estudiante->setCedulaTipo($em->getRepository('SieAppWebBundle:CedulaTipo')->find($cedulaTipoId));
				                }else{
				                	$estudiante->setComplemento('');
                          				$estudiante->setCarnetIdentidad('');
				                	$estudiante->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find(0));
				                }
				                
				                $em->persist($estudiante);
								//dump($estudiante);die;
				                $em->flush();

				                $studentId = $estudiante->getId();
		               		  $oldstudentCodigoRude = $estudiante->getCodigoRude();


			             	}else{
						$studentId = $arrDatos['idStudent'];
						$oldstudentCodigoRude = $arrDatos['rude'];
					}

						$id_usuario = $this->session->get('userId');

			            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
			            $query->execute();
			            //insert a new record with the new selected variables and put matriculaFinID like 5
			            $studentInscription = new EstudianteInscripcion();
			            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
			            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($studentId));
			            $studentInscription->setObservacion(1);
			            $studentInscription->setFechaInscripcion(new \DateTime('now'));
			            $studentInscription->setFechaRegistro(new \DateTime('now'));
			            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objCurso->getId()));
			            $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(59));
						$studentInscription->setUsuarioId($id_usuario);


			            $arrStudent = array(
				            	'rude'=>$oldstudentCodigoRude,
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
		            $new_name = date('YmdHis').'.'.$extension;

		            // GUARDAMOS EL ARCHIVO
		            $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/dataStudentIniPri/' .date('Y');
		            if (!file_exists($directorio)) {
		            	mkdir($directorio, 0775, true);
		            }

		            $directoriomove = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/dataStudentIniPri/' .date('Y').'/'.$studentId;
		            if (!file_exists($directoriomove)) {
		            	mkdir($directoriomove, 0775, true);
		            }

		            $archivador = $directoriomove.'/'.$new_name;
		            //unlink($archivador);
						if(!move_uploaded_file($tmp_name, $archivador)){
						$em->getConnection()->rollback();
								$response->setStatusCode(500);
								return $response;
							}
									//save info extranjero inscription
									$objEstudiantedoc = new EstudianteDocumento();
						
						$objEstudiantedoc->setObservacion($typeInscription);
						$objEstudiantedoc->setFechaRegistro(new \DateTime('now'));
						$objEstudiantedoc->setUrlDocumento($archivador);
						$objEstudiantedoc->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($studentId));
						$objEstudiantedoc->setDocumentoTipo($em->getRepository('SieAppWebBundle:DocumentoTipo')->find(1));

									$em->persist($objEstudiantedoc);
									$em->flush();

							//     // CREAMOS LOS DATOS DE LA IMAGEN
							$informe = array(
							'name' => $name,
							'type' => $type,
							'tmp_name' => 'nueva_ruta',
							'size' => $size,
							'new_name' => $new_name
							);
						}else{
							$informe = null;
							$archivador = 'empty';
						}

			            $em->persist($studentInscription);
			            $em->flush();          
                        //add the areas to the student
                        //$responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $objCurso->getId());    
                        //$query = $em->getConnection()->prepare('SELECT * from sp_genera_estudiante_asignatura(:estudiante_inscripcion_id::VARCHAR)');
						$query = $em->getConnection()->prepare('SELECT * from sp_crea_estudiante_asignatura_regular(:sie::VARCHAR, :estudiante_inscripcion_id::VARCHAR)');
                        $query->bindValue(':estudiante_inscripcion_id', $studentInscription->getId());
						$query->bindValue(':sie', $sie);
                        $query->execute();
                          

			            // Registro de materia curso oferta en el log
			           /* $this->get('funciones')->setLogTransaccion(
			                $studentInscription->getId(),
			                'estudiante_inscripcion',
			                'C',
			                '',
			                '',
			                '',
			                'SIGED',
			                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
			            );*/
			            // Try and commit the transaction
			            $em->getConnection()->commit();

			            $status = 'success';
						$code = 200;
						$message = "Estudiante inscrito Correctamente";
						$swinscription = true; 

				        } catch (Exception $ex) {
				            $em->getConnection()->rollback();
				            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
				        }

				       

            }else{
            	$status = 'error';
				$code = 400;
				$message = "El estudiante no cumple con los requerimientos para la INSCRIPCIÓN (cuenta con $yearStudent años al 30/06)";
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

    private function correctOldYearValidation($yearStudent, $nivel, $grado){
		//dump($yearStudent, $nivel, $grado);
				$swinscription=true;
	            switch ($yearStudent) {
		              case 4:

		                # code...
		                if($nivel=='11' && $grado=='1'){
		                  //good
		                }else{
		                	$status = 'error';
											$code = 400;
											$message = "El estudiante no cumple con lo requerido en edad";
											$swinscription = false; 					
		                }
		                break;
		              case 5:
		                # code...
		                if($nivel=='11' && $grado=='2'){
		                  //good
		                }else{
		                	$status = 'error';
											$code = 400;
											$message = "El estudiante no cumple con lo requerido en edad";
											$swinscription = false;                   
		                }
		                break;
		              //case 6:
		                # code...
		              //  if($nivel=='12' && $grado=='1'){
		                  //good
		               // }else{
		                //	$status = 'error';
						//					$code = 400;
						//					$message = "El estudiante no cumple con lo requerido en edad";
						//					$swinscription = false; 
		               // }
		               // break;
		         //      case 7 or 8:
		         //        if($nivel=='12' && $grado=='1'){
		         //          //good
		         //        }else{
		         //        	$status = 'error';
											// $code = 400;
											// $message = "El estudiante no cumple con lo requerido en edad";
											// $swinscription = false; 
		         //        }
		         //        break;

		              default:
		                # code...
		                	$status = 'error';
											$code = 400;
											$message = "El estudiante no cumple con lo requerido en edad";
											$swinscription = false; 
		                break;
        }
		/***
		 * Cuando el estudiante tiene mayor o igual a 6 años siempre ingresa a  primaria
		*/
				if($yearStudent>=6 && $nivel=='12' && $grado=='1'){
					$swinscription = true; 
			          //good
			    }
			//        break;
			
        return($swinscription);

    }

    private function articuletenYearValidation($yearStudent, $nivel, $grado){
				$swinscription=true;
	            switch ($nivel.$grado) {
		              case 111:
		                # code...
		                if(in_array($yearStudent, array(4,5,6) )){
		                  //good
		                }else{
		                	$status = 'error';
					$code = 400;
					$message = "El estudiante no cumple con lo requerido en edad";
					$swinscription = false; 					
		                }
		                break;
		               case 112:
		                # code...
		                if(in_array($yearStudent, array(5,6,7) )){
		                  //good
		                }else{
		                	$status = 'error';
					$code = 400;
					$message = "El estudiante no cumple con lo requerido en edad";
					$swinscription = false;                   
		                }
		                 case 121:
		                # code...
		                if(in_array($yearStudent, array(6,7,8) )){
		                  //good
		                }else{
		                	$status = 'error';
					$code = 400;
					$message = "El estudiante no cumple con lo requerido en edad";
					$swinscription = false; 
		                }
		               
		                break;
		            

		              default:
		                # code...
		                	$status = 'error';
					$code = 400;
					$message = "El estudiante no cumple con lo requerido en edad";
					$swinscription = false; 
		                break;
        }

        return($swinscription);

    }



  private function rezagoYearValidation($yearStudent, $nivel, $grado){
                    $swinscription=true;
                    switch ($yearStudent) {
                              case 7:
                              case 8:
                              case 9:
                              case 10:
                              case 11:
                              case 12:
                              case 13:
                              case 14:
                                # code...
                                      if($nivel=='12' && $grado=='1'){
                                  //good
                                }else{
                                        $status = 'error';
                                        $code = 400;
                                        $message = "El estudiante no cumple con lo requerido en edad";
                                        $swinscription = false;
                                }
                                break;


                              default:
                                # code...
                                        $status = 'error';
                                        $code = 400;
                                        $message = "El estudiante no cumple con lo requerido en edad";
                                        $swinscription = false;
                                break;
        }

        return($swinscription);

    }






}
