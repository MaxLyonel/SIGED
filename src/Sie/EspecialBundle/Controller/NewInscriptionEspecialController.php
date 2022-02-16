<?php

namespace Sie\EspecialBundle\Controller;

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

class NewInscriptionEspecialController extends Controller
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
   

    public function indexAction(Request $request){
     //disabled option by krlos
     //return $this->redirect($this->generateUrl('login'));
     

    	$em = $this->getDoctrine()->getManager();
        //validation if the user is logged
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }
			$enableoption = true; 
			$message = ''; 
       
        
        $arrExpedido = array();
         // this is to the new person
	      $objExpedido = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findAll();

	      foreach ($objExpedido as $value) {
	        $arrExpedido[$value->getId()] = $value->getSigla();
	      }
		
	    $userAllowedOnwithoutCI = in_array($this->session->get('roluser'), array(8,10))?true:false;
       	return $this->render($this->session->get('pathSystem') .':NewInscriptionEspecial:index.html.twig', array(
       		'arrExpedido'=>$objExpedido,
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
    	$arrGenero = array();
    	$arrPais = array();
    	$arrSangreTipo = array();
    	$arrIdiomaMaterno = array();
    	$arrEstadoCivil = array();
		$arrStudentExist = false;
		$studentId = false;
		$swci = false;
		$swhomonimo = false;
		$existStudent = '';
		
    	// check if the inscription is by ci or not
    	
			$arrayCondition['paterno'] = mb_strtoupper($paterno,'utf-8');
			$arrayCondition['materno'] = mb_strtoupper($materno,'utf-8');
			$arrayCondition['nombre']  = mb_strtoupper($nombre,'utf-8');
			$arrayCondition['fechaNacimiento'] = new \DateTime(date("Y-m-d", strtotime($fecNac))) ;
			$objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findBy($arrayCondition);
		
		if($withoutcifind && ($carnet=='' || !$carnet )){	
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
		      	
				$answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $carnet,$arrParametros,'prod', 'academico');
			}

		}
	
		// check if the student exists
		if(!$existStudent){
		      // check if the data person is true
		    if($answerSegip){
				$status = 'success';
				$code = 200;
				$message = "Datos correctos, puede continuar con el registro del estudiante";
				$swcreatestudent = true; 
				$dataGenderAndCountry = $this->getGenderAndCountryAndIdiomaAndGrupoSanguineo();
				///dump($dataGenderAndCountry);die;
		        $arrGenero = $dataGenderAndCountry['gender'];
				$arrPais 	 = $dataGenderAndCountry['country'];
				$arrIdiomaMaterno = $dataGenderAndCountry['idiomaMaterno'];
				$arrSangreTipo = $dataGenderAndCountry['sangreTipo'];
				$arrEstadoCivil = $dataGenderAndCountry['estadoCivil'];
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
				$swhomonimo = true;
			}

			$existStudent = true;

			$status = 'error';
			$code = 400;
			$message = "Estudiante ya tiene registro RUDE";
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

		//dump($swci);dump($arrStudentExist);die;
       $arrResponse = array(
        'status'          => $status,
        'code'            => $code,
        'message'         => $message,
        'swcreatestudent' => $swcreatestudent,    
        'arrGenero' => $arrGenero,    
        'arrPais' => $arrPais,    
		'arrSangreTipo' => $arrSangreTipo,    
		'arrIdiomaMaterno' => $arrIdiomaMaterno,    
        'arrStudentExist' => $arrStudentExist,   
		'arrEstadoCivil' => $arrEstadoCivil,    
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

			$dataGenderAndCountry = $this->getGenderAndCountryAndIdiomaAndGrupoSanguineo();
			$arrGenero = $dataGenderAndCountry['gender'];
			$arrPais 	 = $dataGenderAndCountry['country'];
			$status = 'success';
			$code = 200;
			$message = "Estudiante cumple con los requerimientos!!";
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
			
			$dataGenderAndCountry = $this->getGenderAndCountryAndIdiomaAndGrupoSanguineo();
			$arrGenero = $dataGenderAndCountry['gender'];
			$arrPais 	 = $dataGenderAndCountry['country'];
			$status = 'success';
			$code = 200;
			$message = "Estudiante cumple con los requerimientos!!.";
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



    public function getGenderAndCountryAndIdiomaAndGrupoSanguineo(){
    		$em = $this->getDoctrine()->getManager();
	        	 // get genero data
	     $objGenero = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();
	      foreach ($objGenero as $value) {
	          if($value->getId()<3){
	              $arrGenero[] = array('generoId' => $value->getId(),'genero' => $value->getGenero());
	          }
	      }
	      $arrData['gender'] = $arrGenero;
		
		$objSangreTipo = $em->getRepository('SieAppWebBundle:SangreTipo')->findAll();
	      foreach ($objSangreTipo as $value) {
	         $arrSangreTipo[] = array('sangreTipoId' => $value->getId(),'grupoSanguineo' => $value->getGrupoSanguineo());
	      }
	    	$arrData['sangreTipo'] = $arrSangreTipo;

		$objEstadoCivil = $em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findAll();
	      foreach ($objEstadoCivil as $value) {
	         $arrEstadoCivil[] = array('estadoCivilId' => $value->getId(),'estadoCivil' => $value->getEstadoCivil());
	      }
	    	$arrData['estadoCivil'] = $arrEstadoCivil;

		$objIdiomaMaterno = $em->getRepository('SieAppWebBundle:IdiomaMaterno')->findAll();
	      foreach ($objIdiomaMaterno as $value) {
             $arrIdiomaMaterno[] = array('idiomaMaternoId' => $value->getId(),'idiomaMaterno' => $value->getIdiomaMaterno());
	      }
	      	$arrData['idiomaMaterno'] = $arrIdiomaMaterno;

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
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $id);
            $query->bindValue(':roluser', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

        $aniveles = array();
      if ($aTuicion[0]['get_ue_tuicion']) {
        //get the IE
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array(
			'institucioneducativaTipo'=>4
				));
		
        if($institucion){
			$nombreIE = ($institucion) ? $institucion->getInstitucioneducativa() : "";
			$status = 'success';
            $code = 200;
            $message = "Datos encontrados";
			$swprocess = true; 
        }else{

        	$status = 'error';
			$code = 400;
			$message = "Unidad Educativa no existe o no es un Centro Educativo Especial";
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
        'nombreue' => $nombreIE
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
    public function doInscriptionEspecialAction(Request $request) {
    	
    	$arrDatos = json_decode($request->get('datos'), true);
  	 	//dump($arrDatos['idiomaMaternoId']);die;
    	 // ini vars
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();        
        // get the send values
        //get info ABOUT UE
        $sie = $arrDatos['sie'];
        $gestion = $this->session->get('currentyear') ;
        $paterno = mb_strtoupper($arrDatos['paterno'], 'utf-8') ;
        $materno = mb_strtoupper($arrDatos['materno'], 'utf-8');
        $nombre = mb_strtoupper($arrDatos['nombre'], 'utf-8');
		$fecNac = $arrDatos['fecnacfind'];
        $withoutcifind = ($arrDatos['withoutcifind']==false)?false:true;
  		$genero = $arrDatos['generoId'];
		$estadoCivilId = isset($arrDatos['estadoCivilId'])?$arrDatos['estadoCivilId']:0;
		$oficialia = isset($arrDatos['oficialia'])?$arrDatos['oficialia']:"";
		$libro = isset($arrDatos['libro'])?$arrDatos['libro']:"";
		$partida = isset($arrDatos['partida'])?$arrDatos['partida']:"";
		$folio = isset($arrDatos['folio'])?$arrDatos['folio']:"";
		$sangreTipoId = isset($arrDatos['sangreTipoId'])?$arrDatos['sangreTipoId']:0;
		$idiomaMaternoId = isset($arrDatos['idiomaMaternoId'])?$arrDatos['idiomaMaternoId']:0;
		$correo = isset($arrDatos['correo'])?$arrDatos['correo']:"";
		$celular = isset($arrDatos['celular'])?$arrDatos['celular']:"";
		$codepedis = isset($arrDatos['carnetCodepedis'])?$arrDatos['carnetCodepedis']:"";
		$ibc = isset($arrDatos['carnetIbc'])?$arrDatos['carnetIbc']:"";
			// get info about ubication
		$paisId = $arrDatos['paisId'];
		$localidad = $arrDatos['localidad'];
		$lugarNacTipoId = isset($arrDatos['lugarNacTipoId'])?$arrDatos['lugarNacTipoId']:'';
		$lugarProvNacTipoId = isset($arrDatos['lugarProvNacTipoId'])?$arrDatos['lugarProvNacTipoId']:'';
		$carnet = isset($arrDatos['cifind'])?$arrDatos['cifind']:'';
		$complemento = isset($arrDatos['complementofind'])?$arrDatos['complementofind']:'';
		$expedidoId = $arrDatos['expedidoIdfind'];

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
		$estudiante->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->find($sangreTipoId));
		$estudiante->setEstadoCivil($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find($estadoCivilId));
		$estudiante->setOficialia(mb_strtoupper($oficialia));                        
		$estudiante->setLibro(mb_strtoupper($libro));                        
		$estudiante->setPartida(mb_strtoupper($partida));                        
		$estudiante->setFolio(mb_strtoupper($folio));                        
		$estudiante->setIdiomaMaternoId($idiomaMaternoId);                        
		$estudiante->setCorreo($correo);                        
		$estudiante->setCelular($celular);                        
		$estudiante->setCarnetCodepedis($codepedis);                        
		$estudiante->setCarnetIbc($ibc);  
		$estudiante->setFechaModificacion(new \DateTime('now'));     
		$estudiante->setObservacion("REGISTRO ESPECIAL SIN RUDE");                        
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
		}else{
			$estudiante->setComplemento('');
			$estudiante->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find(0));
		}
		
		$em->persist($estudiante);
		$em->flush();

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
			$directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/dataStudentEspecial/' .date('Y');
			if (!file_exists($directorio)) {
				mkdir($directorio, 0775, true);
			}

			$directoriomove = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/dataStudentEspecial/' .date('Y').'/'.$estudiante->getId();
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
					$objEstudiantedoc->setObservacion(0);
					$objEstudiantedoc->setFechaRegistro(new \DateTime('now'));
					$objEstudiantedoc->setUrlDocumento($archivador);
					$objEstudiantedoc->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($estudiante->getId()));
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
		$em->getConnection()->commit();

		$status = 'success';
		$code = 200;
		$message = "Los datos se registraron correctamente, puede iniciar la inscripcion con el siguiente código Rudees";
		$swinscription = true;
		$arrStudent = array(
			'rude'=>$estudiante->getCodigoRude(),
			'nombre'=>$nombre,
			'paterno'=>$paterno,
			'materno'=>$materno,
			'fecNac'=>$fecNac
	);

	  $arrResponse = array(
        'status'          => $status,
        'code'            => $code,
        'message'         => $message,
        'swinscription'   => $swinscription,       
        'arrStudent'      => $arrStudent 
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
