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
        $this->baseData = array('sedeId' => 64,  'userId' => 13820843);
    }

    public function indexAction(){        
        
        return $this->render('SieUniversityBundle:ExecutiveStaff:index.html.twig', array(
                'sedeId' => $this->baseData['sedeId']
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
        

        $jsonData = $request->get('datos');
        $arrData = json_decode($jsonData,true);
        dump($arrData);
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

                $directoriomove = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/dataUniversity/' .date('Y').'/krlos';
                if (!file_exists($directoriomove)) {
                    mkdir($directoriomove, 0775, true);
                }

                $archivador = $directoriomove.'/'.$new_name;
                //unlink($archivador);
                if(!move_uploaded_file($tmp_name, $archivador)){
                    $response->setStatusCode(500);
                    return $response;
                }

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
dump($informe);
        die;
    }

}
