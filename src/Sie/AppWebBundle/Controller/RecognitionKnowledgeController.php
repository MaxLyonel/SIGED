<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\RsInscripcion;
use Sie\AppWebBundle\Entity\RsInscripcionAcreditacion;
use Sie\AppWebBundle\Entity\RsInscripcionDocumento;

class RecognitionKnowledgeController extends Controller{


    public $session;

    public function __construct() {
        $this->session = new Session();
    }	

    public function indexAction(){

        return $this->render('SieAppWebBundle:RecognitionKnowledge:index.html.twig', array(
                // ...
            ));    
    }

    private function validarSegip($form, $carnet){

        $arrParametros = array(       
        	'complemento'=>$form['complemento'],
            'primer_apellido'=>$form['paterno'],
            'segundo_apellido'=>$form['materno'],
            'nombre'=>$form['nombre'],
            'fecha_nacimiento'=>$form['fechaNacimiento']);

        if(strlen($form['extranjero'])!=0)
            $arrParametros['extranjero'] = $form['extranjero'];

        $answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $form['carnet'],$arrParametros,'prod', 'academico');    	

        if($answerSegip){
            return true;
        }

        return false;
    }    

    public function lookforCompetitorAction(Request $request){
        $response = new JsonResponse();
        // dump($request);die;
        // $apoderado = $request->get('apoderado', null);
        $estudiante = $request->get('estudiante', null);
        // dump($estudiante);
        $nivel = $estudiante['nivel'];
        $grado = $estudiante['grado'];
        $opcion = $request->get('opcion', null);

        $em = $this->getDoctrine()->getManager();
        // VALIDAMOS DATOS DEL ESTUDIANTE
        
        switch ($opcion) {
            case 1:
                $codigoRude = mb_strtoupper($estudiante['codigoRude']);
                $estudianteObj = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$codigoRude));
                break;
            case 2:
                $carnet = $estudiante['carnet'];
                $complemento = $estudiante['complemento'];
                $estudianteObj = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('carnetIdentidad'=>$carnet, 'complemento'=>$complemento));
                break;
            case 3:
                $nombre = mb_strtoupper($estudiante['nombre'], 'utf-8');
                $paterno = mb_strtoupper($estudiante['paterno'], 'utf-8');
                $materno = mb_strtoupper($estudiante['materno'], 'utf-8');
                $fechaNacimiento = $estudiante['fechaNacimiento'];

                $estudianteObj = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array(
                    'nombre'=>$nombre,
                    'paterno'=>$paterno,
                    'materno'=>$materno,
                    'fechaNacimiento'=>new \DateTime($fechaNacimiento)
                ));
                break;
        }

		$dataInscriptionR = array();
		$dataInscriptionA = array();
		$dataInscriptionE = array();
		$sendStudent = array();
		$existRsInsc = false;
		$studentSpeciality = '';
        if ($opcion == 1 && (!is_object($estudianteObj)))  {
            return $response->setData([
                'status'=>'error',
                'msg'=>'Los datos del participante no son válidos'
            ]);
        }else{
        }

        // VERIFICAMOS SI LA OPCION ES POR CARNET PARA VALIDAR CON EL SEGIP
        if ($opcion == 3 && (!is_object($estudianteObj)) ) {
        	
            $validarSegip = $this->validarSegip($estudiante, $estudiante['carnet'].$estudiante['complemento']);

            if (!$validarSegip) {
                return $response->setData([
                    'status'=>'error',
                    'msg'=>'Los datos del participante no son válidos por segip'
                ]);
            }else{
            	$sendStudent = $estudiante;
            	$sendStudent['codigoRude'] = '';
            	$dataInscriptionR = array();
            	$dataInscriptionA = array();
            	$dataInscriptionE = array();
            }
        }

        if(is_object($estudianteObj)){
            $query = $em->getConnection()->prepare("select * from sp_genera_estudiante_historial('" . $estudianteObj->getCodigoRude() . "') order by gestion_tipo_id_raep desc, estudiante_inscripcion_id_raep desc;");
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
	                    break;
	        }
	        // get the RS inscription
	        $objstudentRsInscription = $em->getRepository('SieAppWebBundle:RsInscripcion')->findOneBy(array('estudiante'=>$estudianteObj->getId()));
	        if(sizeof($objstudentRsInscription)>0){
	        	// set exist rs inscription on student choose
	        	$existRsInsc = true;
	        	// get Speciality
	        	$studentSpeciality = $em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->find($objstudentRsInscription->getSuperiorInstitucioneducativaAcreditacion()->getId())->getEspecialidad();

	        }
        }

        $dataInscriptionR = (sizeof($dataInscriptionR)>0)?$dataInscriptionR:false;
        $dataInscriptionA = (sizeof($dataInscriptionA)>0)?$dataInscriptionA:false;
        $dataInscriptionE = (sizeof($dataInscriptionE)>0)?$dataInscriptionE:false;
        // $dataInscriptionP = (sizeof($dataInscriptionP)>0)?$dataInscriptionP:false;

            $sendStudent['id'] = $estudianteObj->getId();
            $sendStudent['codigoRude'] = $estudianteObj->getCodigoRude();
            $sendStudent['paterno'] = $estudianteObj->getPaterno();
            $sendStudent['materno'] = $estudianteObj->getMaterno();
            $sendStudent['nombre'] = $estudianteObj->getNombre();
            $sendStudent['carnet'] = $estudianteObj->getCarnetIdentidad();
            $sendStudent['complemento'] = $estudianteObj->getComplemento();
            $sendStudent['fechaNacimiento'] = $estudianteObj->getFechaNacimiento()->format('d-m-Y');        	
        }

        return $response->setData([
            'status'=>'success',
            'datos'=>array(
                'statusApoderado'=>'success',
                'msgApoderado'=>'informacion del participante',
                'statusEstudiante'=>'success',
                'sendStudent'=>$sendStudent,
                'dataInscriptionR'=>$dataInscriptionR,
                'dataInscriptionA'=>$dataInscriptionA,
                'dataInscriptionE'=>$dataInscriptionE,
                'existRsInsc'=>$existRsInsc,
                'studentSpeciality'=>$studentSpeciality,
            )
        ]);
    }  

    //****************************************************************************************************
    // method description:
    // fucntion to get the speciality and documents required by SIE and year
    // parameters: sie, year
    // AUTOR: krlos
    //****************************************************************************************************
    public function infoCentroAction(Request $request){
  		$response = new JsonResponse();

  		$institucionEducativaId = $this->session->get('ie_id');
  		$gestionId = $this->session->get('currentyear');
  		
  		$entidadEspecialidadTipo = $this->getEspecialidadCentroEducativoTecnica($institucionEducativaId, $gestionId);
  		$objDocRequired = $this->getDocsRequired(array(10,15,16,17,18,19,20));
  		$objSucursal = $this->getSucursales();

        return $response->setData([
            'status'=>'success',
            'datos'=>array(                
                'entidadEspecialidadTipo'=>$entidadEspecialidadTipo,
                'objDocRequired'=>$objDocRequired,
                'objSucursal'=>$objSucursal,
            )
        ]);    	

    }

    //****************************************************************************************************
    // method description:
    // fucntion to get the acreditions level by SIE, speciality and year
    // parameters: sie, speciality and year
    // AUTOR: krlos
    //****************************************************************************************************
    public function getlevelAcreditationAction(Request $request){
  		$response = new JsonResponse();

  		$institucionEducativaId = $this->session->get('ie_id');
  		$gestionId = $this->session->get('currentyear');
  		$specialityId = $request->get('specialityId', null);
  		
  		$entidadAcredition = $this->getNivelCentroEducativoTecnica($institucionEducativaId,$gestionId,$specialityId);

        return $response->setData([
            'status'=>'success',
            'datos'=>array(                
                'entidadAcredition'=>$entidadAcredition,
            )
        ]);    	

    }
	
	public function saveRecognitionKnowledgeAction(Request $request){
        $response = new JsonResponse();
        $estudiante = $request->get('estudiante', null);
        $option = $request->get('opcion', null);

        switch ($option) {
        	case 1:
        		# code...
        		break;
        	case 3:
        		# cretae a rude code to the student
        		// $newStudentData = $this->createNewStudent($estudiante['dataStudent']);
        		// $estudiante['dataStudent']['id'] = $newStudentData['idStudent'];
        		// $estudiante['dataStudent']['codigoRude'] = $newStudentData['codigoRude'];
        		break;
        	
        	default:
        		# code...
        		break;
        }
// dump($estudiante);die;
        // register RS inscription
        $swrs = $this->saveRecogKnowledge($estudiante, $option)?true:false;

        return $response->setData([
            'status'=>'success',
            'datos'=>array(
                // 'statusApoderado'=>'success',
                // 'msgApoderado'=>'informacion del participante',
                // 'statusEstudiante'=>'success',
                'swRS'=>$swrs,
                'studentRS'=>$estudiante['dataStudent'],
                
            )
        ]);		
	}

	private function saveRecogKnowledge($data, $option){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

		try {
			if($option == 3){
				$dataStudent = $data['dataStudent'];

		        $query = $em->getConnection()->prepare('SELECT get_estudiante_nuevo_rude(:sie::VARCHAR,:gestion::VARCHAR)');
		        $query->bindValue(':sie', $this->session->get('ie_id'));            
		        $query->bindValue(':gestion', $this->session->get('currentyear'));
		        $query->execute();
		        $codigorude = $query->fetchAll();

		        $codigoRude = $codigorude[0]["get_estudiante_nuevo_rude"];  

				$newestudiante = new Estudiante();
		        // set the new student
		        $newestudiante->setCodigoRude($codigoRude);               
		        $newestudiante->setPaterno(mb_strtoupper($dataStudent['paterno'], 'utf-8'));
		        $newestudiante->setMaterno(mb_strtoupper($dataStudent['materno'], 'utf-8'));
		        $newestudiante->setNombre(mb_strtoupper($dataStudent['nombre'], 'utf-8'));                        
		        $newestudiante->setFechaNacimiento(new \DateTime($dataStudent['fechaNacimiento']));            
		        $newestudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($dataStudent['genero']));
		        //no Bolivia
		        $newestudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find(0));
		        $newestudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find('11'));
		        $newestudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find('11'));
		        $newestudiante->setLocalidadNac('');    
		   
		        $newestudiante->setCarnetIdentidad($dataStudent['carnet']);
		        $newestudiante->setComplemento(mb_strtoupper($dataStudent['complemento'], 'utf-8'));
		        $newestudiante->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find(0));
		        $newestudiante->setSegipId(1);
		   
		        
		        $em->persist($newestudiante);
				$em->flush();
		        $data['dataStudent']['id'] = $newestudiante->getId();
        		$data['dataStudent']['codigoRude'] = $newestudiante->getCodigoRude();
        		
			}

			$newRsInscription = new RsInscripcion();
			$newRsInscription->setFechaRegistro(new \DateTime('now'));
			$newRsInscription->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->find($this->session->get('userId')));
			$newRsInscription->setSuperiorInstitucioneducativaAcreditacion($em->getRepository('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion')->find($data['especialidad']));
			$newRsInscription->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($data['sucursal']));
			$newRsInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($data['dataStudent']['id']));
            $em->persist($newRsInscription);

            foreach ($data['documentType'] as $value) {
            	$newDocumentType = new RsInscripcionDocumento();
            	$newDocumentType->setFechaRegistro(new \DateTime('now'));
            	$newDocumentType->setUsuarioId($this->session->get('userId'));
            	$newDocumentType->setDocumentoTipo($em->getRepository('SieAppWebBundle:DocumentoTipo')->find($value));
            	$newDocumentType->setRsInscripcion($em->getRepository('SieAppWebBundle:RsInscripcion')->find($newRsInscription->getId()));
            	$em->persist($newDocumentType);
            }

            foreach ($data['acredition'] as $value) {
            	$RsInscripcionAcreditacion = new RsInscripcionAcreditacion();
            	$RsInscripcionAcreditacion->setFechaRegistro(new \DateTime('now'));
            	$RsInscripcionAcreditacion->setRsInscripcionId($newRsInscription->getId());
            	$RsInscripcionAcreditacion->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->find($this->session->get('userId')));
            	$RsInscripcionAcreditacion->setSuperiorAcreditacionTipo($em->getRepository('SieAppWebBundle:SuperiorAcreditacionTipo')->find($value));
            	$em->persist($RsInscripcionAcreditacion);
            }

	        $em->flush();  
			// Try and commit the transaction
			$em->getConnection()->commit();
			return true;
			
		} catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }


	}

	private function createNewStudent($dataStudent){

		$em = $this->getDoctrine()->getManager();
		$em->getConnection()->beginTransaction();
        $query = $em->getConnection()->prepare('SELECT get_estudiante_nuevo_rude(:sie::VARCHAR,:gestion::VARCHAR)');
        $query->bindValue(':sie', $this->session->get('ie_id'));            
        $query->bindValue(':gestion', $this->session->get('currentyear'));
        $query->execute();
        $codigorude = $query->fetchAll();

        $codigoRude = $codigorude[0]["get_estudiante_nuevo_rude"];  
        
        // set the data person to the student table
        try {
			$estudiante = new Estudiante();
	        // set the new student
	        $estudiante->setCodigoRude($codigoRude);               
	        $estudiante->setPaterno(mb_strtoupper($dataStudent['paterno'], 'utf-8'));
	        $estudiante->setMaterno(mb_strtoupper($dataStudent['materno'], 'utf-8'));
	        $estudiante->setNombre(mb_strtoupper($dataStudent['nombre'], 'utf-8'));                        
	        $estudiante->setFechaNacimiento(new \DateTime($dataStudent['fechaNacimiento']));            
	        $estudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($dataStudent['genero']));
	        //no Bolivia
	        $estudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find(0));
	        $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find('11'));
	        $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find('11'));
	        $estudiante->setLocalidadNac('');    
	   
	        $estudiante->setCarnetIdentidad($dataStudent['carnet']);
	        $estudiante->setComplemento(mb_strtoupper($dataStudent['complemento'], 'utf-8'));
	        $estudiante->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find(0));
	        $estudiante->setSegipId(1);
	   
	        
	        $em->persist($estudiante);
	        $em->flush();
	        $em->getConnection()->commit();
	        $responseData = array('idStudent'=>$estudiante->getId(), 'codigoRude'=>$estudiante->getCodigoRude());
	        return $responseData;        	
        	
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }


	}

    private function getDocsRequired($data){

        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("select * from Documento_Tipo where id in (".join(",", $data).") ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;    	

    }

    private function getSucursales(){
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("select id, sucursal_tipo_id from institucioneducativa_sucursal where institucioneducativa_id = ".$this->session->get('ie_id')." and gestion_tipo_id = ".$this->session->get('currentyear')." ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;   	
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista las especialidades de un centro de educacion alternativa segun la gestión
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getEspecialidadCentroEducativoTecnica($institucionEducativaId, $gestionId) {
        date_default_timezone_set('America/La_Paz');
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                    select distinct sest.id as especialidad_id, sest.especialidad as especialidad
                    from superior_facultad_area_tipo as sfat
                    inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
                    inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
                    inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
                    inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
                    where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sfat.codigo in (18,19,20,21,22,23,24,25)
                    order by sest.especialidad
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }
    public function getNivelCentroEducativoTecnica($institucionEducativaId, $gestionId, $especialidadId) {
        date_default_timezone_set('America/La_Paz');
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                select distinct sat.codigo as nivel_id, sat.acreditacion as nivel, sat.id 
                from superior_facultad_area_tipo as sfat
                inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
                inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
                inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id
                inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
                inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
                where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sest.id = ".$especialidadId." and sfat.codigo in (18,19,20,21,22,23,24,25)
                order by sat.codigo
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }        


}
