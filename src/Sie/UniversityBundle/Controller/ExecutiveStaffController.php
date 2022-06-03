<?php

namespace Sie\UniversityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;
use Sie\AppWebBundle\Entity\UnivAutoridadUniversidad;
use Sie\AppWebBundle\Entity\Persona;


class ExecutiveStaffController extends Controller{
    public $session;
    public $idInstitucion;
    public $router;
    public $baseData;
    /**
     * the class constructor
     */
    public function __construct() {

        $this->session = new Session();
        $this->baseData = array('sedeId' => $this->session->get('sedeId'),  'userId' => $this->session->get('userId'));
    }
    /** krlos
     * the method to decrypt
     */
    private function kdecrypt($data){
        $data = hex2bin($data);
        return unserialize($data);
    }

    public function indexAction(Request $request){        
        $form = $request->get('form');
        
        $data = ($this->kdecrypt($form['data']));
        return $this->render('SieUniversityBundle:ExecutiveStaff:index.html.twig', array(
                'sedeId' => $data['sedeId']
            ));    
    }

    public function getAllStaffAction(Request $request){

    	$response = new JsonResponse();
    	// $arrRequest = array('sedeId' => 64,  'userId' => 13820843, 'yearSelected' => $request->get('year'));
        $this->baseData['yearSelected'] = $request->get('year');
        
    	$arrRegisteredStaff = $this->get('univfunctions')->getAllStaff($this->baseData);

    	// check if the staffs exists
    	if(sizeof($arrRegisteredStaff)>0){

    	}else{
    		$arrRegisteredStaff = array();
    	}

		$arrResponse = array(
		    'swgetinfostaff'           => true,
		    'arrRegisteredStaff'       => $arrRegisteredStaff,
		    // 'swperson'                 => $swperson,
		);
		// dump($arrResponse);die;
		$response->setStatusCode(200);
		$response->setData($arrResponse);        
		return $response;
    }

    public function lookforpersonAction(Request $request){
      //ini json var
      	$response = new JsonResponse();    	
    	$swperson = false;
    	$em = $this->getDoctrine()->getManager();

        $arrayCondition2['carnet'] = $request->get('cibuscar');
        $arrayCondition2['segipId'] = 1;
        if($request->get('complementoval')){
          $arrayCondition2['complemento'] = $request->get('complementoval');
        }else{
          $arrayCondition2['complemento'] = "";
        }
        $objPerson = $em->getRepository('SieAppWebBundle:Persona')->findOneBy($arrayCondition2);  
        // check if the person was in sede
        $existsPersonStaffRegistered = false;
        if(sizeof($objPerson)>0){
            
            $arrSearchStaff = array('yearchoose'=>$request->get('yearchoose'),'sedeId'=>$request->get('sedeId'),'personId'=>$objPerson->getId());
            $existsPersonStaffRegistered = $this->get('univfunctions')->getPersonalStaff($arrSearchStaff);
        }
        $arrPosition = array();
        $arrTraining = array();
        if(!$existsPersonStaffRegistered){
            // get the information classifiers
            $arrPosition = $this->get('univfunctions')->getPosition();
            $arrTraining = $this->get('univfunctions')->getTraining();
        }

        $arrPerson = array(
	          'paterno'     =>'',
	          'materno'     =>'',
	          'nombre'      =>'',
	          'fecNac'      =>'',
	          'carnet'      => $request->get('cibuscar'),
	          'genero'      =>'',
	          'generoId'    =>'',
	          'complemento' => $request->get('complementoval'),
	          'expedido'    =>'',
	          'expedidoId'  =>'',
	          'expedidoId2' =>'',
	          'personId'    =>'',
	          'foreign'    =>'',
              'newperson'    =>false,
        );
        if($objPerson){
        	$swperson=true;
        	$arrPerson = array(
	          'paterno'     =>$objPerson->getPaterno(),
	          'materno'     =>$objPerson->getMaterno(),
	          'nombre'     =>$objPerson->getNombre(),
	          'fecNac'      =>$objPerson->getFechaNacimiento()->format('d-m-Y'),
	          'carnet'      =>$objPerson->getCarnet(),
	          'genero'      =>$objPerson->getGeneroTipo()->getGenero(),
	          'generoId'    =>$objPerson->getGeneroTipo()->getId(),
	          'complemento' =>$objPerson->getComplemento(),
	          'expedido'    =>$objPerson->getExpedido()->getSigla(),
	          'expedidoId'  =>$objPerson->getExpedido()->getId(),
	          'expedidoId2'  =>$objPerson->getExpedido()->getId(),
	          'personId'    =>$objPerson->getId(),  
	          'foreign'    =>'',     		
              'newperson'    =>false,
        	);
        }

	      $arrResponse = array(
	            'answer'                      => true,
	            'arrPerson'                   => $arrPerson,
                'arrPosition'                 => $arrPosition,
                'arrTraining'                 => $arrTraining,
	            'swperson'                    => $swperson,
                'existsPersonStaffRegistered' => $existsPersonStaffRegistered,
	      );
	      // dump($arrResponse);die;
	      $response->setStatusCode(200);
	      $response->setData($arrResponse);        
	      return $response;
    }

    public function getAllOperativeAction(Request $request){
        $response = new JsonResponse();             

        $arrData = array('sedeId'=> $request->get('sedeId'));
        $arrOperative = $this->get('univfunctions')->getAllOperative($arrData);
          $arrResponse = array(             
            'arrOperative' => $arrOperative,
          );
          // dump($arrResponse);die;
          $response->setStatusCode(200);
          $response->setData($arrResponse);        
          return $response;        
    }

    public function validateDataSegipAction(Request $request){

        $response = new JsonResponse();             

        
        $arrParametros = array(
            'complemento'=>mb_strtoupper($request->get('complemento'), 'utf-8'),
            'primer_apellido'=>mb_strtoupper($request->get('paterno'), 'utf-8'),
            'segundo_apellido'=>mb_strtoupper($request->get('materno'), 'utf-8'),
            'nombre'=>mb_strtoupper($request->get('nombre'), 'utf-8'),
            'fecha_nacimiento'=>$request->get('fecNac')
        );
        if($request->get('extranjero') == 1){
            $arrParametros['extranjero'] = 'e';
        }      
        
        // get info segip
        $answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $request->get('carnet'),$arrParametros,'prod', 'academico');        
        
          $arrResponse = array(
                'swperson' => $answerSegip,
                'swsegip'  => !$answerSegip,
                'disabledbutton'  => true,
          );        

        $response->setStatusCode(200);
        $response->setData($arrResponse);        
        return $response;                    

    }

    public function savestaffAction(Request $request){
        
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $jsonData = $request->get('datos');
        $arrData = json_decode($jsonData,true);
        $arrData['pathDirectory'] = '';
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
                $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/dataUniversity/' .date('Y');
                if (!file_exists($directorio)) {
                    mkdir($directorio, 0775, true);
                }

                $directoriomove = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/dataUniversity/' .date('Y').'/'.$arrData['sedeId'];
                if (!file_exists($directoriomove)) {
                    mkdir($directoriomove, 0775, true);
                }

                $archivador = $directoriomove.'/'.$new_name;
                //unlink($archivador);
                if(!move_uploaded_file($tmp_name, $archivador)){
                    $response->setStatusCode(500);
                    return $response;
                }
                $arrData['pathDirectory'] = 'uploads/archivos/dataUniversity/' .date('Y').'/'.$arrData['sedeId'].'/'.$new_name;
                // CREAMOS LOS DATOS DE LA IMAGEN
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

            if($arrData['newperson'] == true){
                $newPerson = $this->saveNewPerson($arrData);
                $arrData['personId'] = $newPerson->getId();
            }

            $registerProcess = $this->saveUpdatePersonalInfo($arrData, 0);


            $this->baseData['yearSelected'] = $arrData["yearchoose"];

            $arrRegisteredStaff = $this->get('univfunctions')->getAllStaff($this->baseData);

            // check if the staffs exists
            if(sizeof($arrRegisteredStaff)>0){

            }else{
                $arrRegisteredStaff = array();
            }

            $arrResponse = array(
                'swgetinfostaff'           => true,
                'arrRegisteredStaff'       => $arrRegisteredStaff,
                // 'swperson'                 => $swperson,
            );
            // dump($arrResponse);die;
            $response->setStatusCode(200);
            $response->setData($arrResponse);        
            return $response;            
            
    }

    private function saveUpdatePersonalInfo($arrData,$flag){

        $em = $this->getDoctrine()->getManager();
        $arrData["personId"] = $arrData["personId"];
        $arrData["cargo"] = $arrData["cargo"];
        $arrData["formacion"] = $arrData["formacion"];
        $arrData["ref"] = $arrData["ref"];
        $arrData["telefono"] = $arrData["telefono"];
        $arrData["fax"] = $arrData["fax"];
        $arrData["casilla"] = $arrData["casilla"];
        $arrData["email"] = $arrData["email"];
        // $arrData["descripcion"] = $arrData["descripcion"];
        $arrData["formaciondescripcion"] = $arrData["formaciondescripcion"];
        $arrData["gestion_nombramiento_id"] = $arrData["gestion_nombramiento_id"];
        $arrData["ratificacion_anio_ini"] = $arrData["ratificacion_anio_ini"];
        $arrData["ratificacion_anio_fin"] = $arrData["ratificacion_anio_fin"];
        $arrData["fecha_registro_firma"] = $arrData["fecha_registro_firma"];
        $arrData["yearchoose"] = $arrData["yearchoose"];
        
        if($flag){
            $entity = $em->getRepository('SieAppWebBundle:UnivAutoridadUniversidad')->find($arrData["personId"]);
            $entity->setFechaActualizacion(new \DateTime('now'));
            if($arrData['pathDirectory']){
                $entity->setDocumentosAcad($arrData['pathDirectory']);
            }
        }else{

            $entity = new UnivAutoridadUniversidad();
            $arrData["sedeId"]     = $arrData["sedeId"];
            $entity->setUnivSede($em->getRepository('SieAppWebBundle:UnivSede')->find($arrData["sedeId"]));
            $entity->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($arrData["personId"]));
            $entity->setDocumentosAcad($arrData['pathDirectory']);
        }

        $entity->setRef($arrData["ref"]);
        $entity->setTelefono($arrData["telefono"]);
        $entity->setFax($arrData["fax"]);
        $entity->setCasilla($arrData["casilla"]);
        $entity->setEmail($arrData["email"]);
        $entity->setFormaciondescripcion($arrData["formaciondescripcion"]);
        
        $entity->setRatificacionAnioIni($arrData["ratificacion_anio_ini"]);
        $entity->setRatificacionAnioFin($arrData["ratificacion_anio_fin"]);        
        $entity->setFechaRegistroFirma(new \DateTime($arrData["fecha_registro_firma"]));
        $entity->setFechaCreacion(new \DateTime('now'));
        $entity->setGestionNombramiento($em->getRepository('SieAppWebBundle:GestionTipo')->find($arrData["gestion_nombramiento_id"]));
        
        $entity->setUnivFormacionTipo($em->getRepository('SieAppWebBundle:UnivFormacionTipo')->find($arrData["formacion"]));
        $entity->setUnivCargoJerarquicoTipo($em->getRepository('SieAppWebBundle:UnivCargoJerarquicoTipo')->find($arrData["cargo"]));
        $entity->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($arrData["yearchoose"]));
        

        $em->persist($entity);
        $em->flush();        

        return $entity;

    }

    private function saveNewPerson($data){
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        // create new person object
        $newpersona = new Persona();            
        $newpersona->setPaterno(mb_strtoupper($data['paterno'], "utf-8"));
        $newpersona->setMaterno(mb_strtoupper($data['materno'], "utf-8"));    
        $newpersona->setNombre(mb_strtoupper($data['nombre'], "utf-8"));    
        $newpersona->setCarnet($data['carnet']);  
        $newpersona->setComplemento(mb_strtoupper($data['complemento'], "utf-8"));
        $newpersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find(0));
        $newpersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById(0));
        $newpersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findOneById(0));

        $newpersona->setFechaNacimiento(new \DateTime($data['fecNac']));            
        $newpersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($data['generoId']));
        $newpersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find(0));
        $newpersona->setRda('0');
        $newpersona->setActivo('1');
        $newpersona->setSegipId('1');            
        $newpersona->setEsVigente('1');
        $newpersona->setCountEdit('1');
        $newpersona->setEsvigenteApoderado('1');
        $em->persist($newpersona);
        $em->flush();

        return $newpersona;


    }

    public function deletePersonalAction(Request $request){

        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();

        $objPerson = $em->getRepository('SieAppWebBundle:UnivAutoridadUniversidad')->find($request->get('id'));

        $em->remove($objPerson);

        // $em->persist($objPerson);
        $em->flush();
        
        $this->baseData['yearSelected'] = $request->get('yearSelected');

        $arrRegisteredStaff = $this->get('univfunctions')->getAllStaff($this->baseData);

        // check if the staffs exists
        if(sizeof($arrRegisteredStaff)>0){

        }else{
            $arrRegisteredStaff = array();
        }

        $arrResponse = array(
            'swgetinfostaff'           => true,
            'arrRegisteredStaff'       => $arrRegisteredStaff,
            // 'swperson'                 => $swperson,
        );
        // dump($arrResponse);die;
        $response->setStatusCode(200);
        $response->setData($arrResponse);        
        return $response;          

    }

    public function editPersonalAction(Request $request){

        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        // get the personal sede info
        $objPersonalStaff = $em->getRepository('SieAppWebBundle:UnivAutoridadUniversidad')->find($request->get('id'));
        // dump(($objPersonalStaff->getPersona()->getId()));
        $objPerson = $em->getRepository('SieAppWebBundle:Persona')->find($objPersonalStaff->getPersona()->getId());

        
        // dump($objPersonalStaff->getUnivFormacionTipo()->getId());
        // dump($objPersonalStaff->getUnivCargoJerarquicoTipo()->getId());
        // // dump($objPerson);
        // die;


        // build the array personal staff data 
        $arrPerson = array(
            "ref" => $objPersonalStaff->getRef(),
            "carnet" => $objPerson->getCarnet(),
            "fax" => $objPersonalStaff->getFax(),
            "nombre" => $objPerson->getNombre(),
            "materno" => $objPerson->getMaterno(),            
            "paterno" => $objPerson->getPaterno(),
            "email" => $objPersonalStaff->getEmail(),
            "personId" => $objPersonalStaff->getId(),            
            "casilla" => $objPersonalStaff->getCasilla(),
            "complemento" => $objPerson->getComplemento(),
            "telefono" => $objPersonalStaff->getTelefono(),            
            "documentos_acad" => $objPersonalStaff->getDocumentosAcad(),
            "descripcion" => $objPersonalStaff->getFormaciondescripcion(),
            "fecNac" => $objPerson->getFechaNacimiento()->format('d-m-Y'),
            "formacion" => $objPersonalStaff->getUnivFormacionTipo()->getId(),            
            "cargo" => $objPersonalStaff->getUnivCargoJerarquicoTipo()->getId(),
            "ratificacion_anio_fin" => $objPersonalStaff->getRatificacionAnioFin(),
            "ratificacion_anio_ini" => $objPersonalStaff->getRatificacionAnioIni(),
            "formaciondescripcion" => $objPersonalStaff->getFormaciondescripcion(),
            "gestion_nombramiento_id" => $objPersonalStaff->getGestionNombramiento()->getId(),
            "fecha_registro_firma" => $objPersonalStaff->getFechaRegistroFirma()->format('d-m-Y'),
            "yearchoose" => $request->get("yearSelected"),
            "sedeId"     => $request->get("sedeId"),
                        

        );
        // dump($arrPerson);die;

        // $em->persist($objPerson);
        // $em->flush();
        
        $this->baseData['yearSelected'] = $request->get('yearSelected');
        //$arrRegisteredStaff = $this->get('univfunctions')->getAllStaff($this->baseData);
        $arrPosition = $this->get('univfunctions')->getPosition();
        $arrTraining = $this->get('univfunctions')->getTraining();
        // check if the staffs exists
        // if(sizeof($arrRegisteredStaff)>0){

        // }else{
        //     $arrRegisteredStaff = array();
        // }
          $arrResponse = array(
                'answer'                      => true,
                'arrPersonEdit'                   => $arrPerson,
                'arrPosition'                 => $arrPosition,
                'arrTraining'                 => $arrTraining,
                'swpersonedit'                    => true,
                'existsPersonStaffRegistered' => false,
          );
          // dump($arrResponse);die;
          $response->setStatusCode(200);
          $response->setData($arrResponse);        
          return $response;         

    }   

    public function saveEditPersonalAction(Request $request){
        // ini vars to update process
        $response = new JsonResponse();
        $jsonData = $request->get('datos');
        $arrData = json_decode($jsonData, true);
        

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
                $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/dataUniversity/' .date('Y');
                if (!file_exists($directorio)) {
                    mkdir($directorio, 0775, true);
                }

                $directoriomove = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/dataUniversity/' .date('Y').'/'.$arrData['sedeId'];
                if (!file_exists($directoriomove)) {
                    mkdir($directoriomove, 0775, true);
                }

                $archivador = $directoriomove.'/'.$new_name;
                //unlink($archivador);
                if(!move_uploaded_file($tmp_name, $archivador)){
                    $response->setStatusCode(500);
                    return $response;
                }
                $arrData['pathDirectory'] = 'uploads/archivos/dataUniversity/' .date('Y').'/'.$arrData['sedeId'].'/'.$new_name;
                // CREAMOS LOS DATOS DE LA IMAGEN
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
        if($informe){
            $arrData['pathDirectory'] = 'uploads/archivos/dataUniversity/' .date('Y').'/'.$arrData['sedeId'].'/'.$new_name;
        }else{
            $arrData['pathDirectory'] = '';
        }
        // update the personal info
        $registerProcess = $this->saveUpdatePersonalInfo($arrData, 1);

        // update personal information
            $this->baseData['yearSelected'] = $arrData["yearchoose"];
            $this->baseData['sedeId'] = $arrData["sedeId"];

            $arrRegisteredStaff = $this->get('univfunctions')->getAllStaff($this->baseData);

            // check if the staffs exists
            if(sizeof($arrRegisteredStaff)>0){

            }else{
                $arrRegisteredStaff = array();
            }

            $arrResponse = array(
                'swgetinfostaff'           => true,
                'arrRegisteredStaff'       => $arrRegisteredStaff,
                // 'swperson'                 => $swperson,
            );
            // dump($arrResponse);die;
            $response->setStatusCode(200);
            $response->setData($arrResponse);        
            return $response;  

    }





}
