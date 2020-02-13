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
use Sie\AppWebBundle\Entity\Estudiante; 

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
	    $userAllowedOnwithoutCI = in_array($this->session->get('roluser'), array(7,8,10))?true:false;
       	return $this->render('SieHerramientaBundle:NewInscriptionIniPri:index.html.twig', array(
       		'arrExpedido'=>$objExpedido,
       		'allowwithoutci' => $userAllowedOnwithoutCI,
               // ...
        ));
    }

    public function checksegipstudentAction(Request $request){
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
	                ->andwhere('iec.nivelTipo != :nivel')
	                ->setParameter('sie', $id)
	                ->setParameter('gestion', $this->session->get('currentyear') )
	                ->setParameter('nivel', '13')
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
     * todo the registration of traslado
     * @param Request $request
     *
     */
    public function doInscriptioninipriAction(Request $request) {

    	 // ini vars
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();        
        // get the send values
        //get info ABOUT UE
        $sie = $request->get('sie');
        $nivel = $request->get('nivelId');
        $grado = $request->get('gradoId');
        $paralelo = $request->get('paraleloId');
        $turno = $request->get('turnoId');
        $gestion = $this->session->get('currentyear') ;
        // get info about student
        $fecNac = $request->get('fecnacfind');
        $paterno = $request->get('paterno');
        $materno = $request->get('materno');
        $nombre = $request->get('nombre');
        $genero = $request->get('generoId');
        // get info about ubication
        $paisId = $request->get('paisId');
        $localidad = $request->get('localidad');
        $lugarNacTipoId = $request->get('lugarNacTipoId');
        $lugarProvNacTipoId = $request->get('lugarProvNacTipoId');

        $withoutcifind = ($request->get('withoutcifind')=='false')?false:true;

       

            // validation if the ue is over 4 operativo
            $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);
            
			$swinscription=true;
            if($operativo >= 4){
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
              case 6:
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
              case 7 or 8:
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

                // create rude code to the student
                
                $query = $em->getConnection()->prepare('SELECT get_estudiante_nuevo_rude(:sie::VARCHAR,:gestion::VARCHAR)');
                $query->bindValue(':sie', $sie);            
                $query->bindValue(':gestion', $gestion+1);
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
                if ($paisId === '1'){                    
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

	            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
	            $query->execute();
	            //insert a new record with the new selected variables and put matriculaFinID like 5
	            $studentInscription = new EstudianteInscripcion();
	            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));
	            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
	            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
	            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($estudiante->getId()));
	            $studentInscription->setObservacion(1);
	            $studentInscription->setFechaInscripcion(new \DateTime('now'));
	            $studentInscription->setFechaRegistro(new \DateTime('now'));
	            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objCurso->getId()));
	            $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(59));

	            $arrStudent = array(
	            	'rude'=>$estudiante->getCodigoRude(),
	            	'nombre'=>$estudiante->getNombre(),
	            	'paterno'=>$estudiante->getPaterno(),
	            	'materno'=>$estudiante->getMaterno(),
	            	'fecNac'=>$estudiante->getFechaNacimiento()->format('d-m-Y')
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









}
