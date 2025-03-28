<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEliminados;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteHistorialModificacion; 
use Sie\AppWebBundle\Entity\EstudianteInscripcion; 
use Sie\AppWebBundle\Entity\Estudiante; 
use Sie\AppWebBundle\Entity\EstudianteDocumento; 
use Symfony\Component\Validator\Constraints\DateTime;
use Sie\AppWebBundle\Entity\UnificacionRude;
use Sie\AppWebBundle\Entity\EstudianteBack;
use Sie\AppWebBundle\Entity\ApoderadoInscripcion;
use Sie\AppWebBundle\Entity\InstitucioneducativaPlataforma;
use Sie\AppWebBundle\Entity\Persona;

class UnidadEducativaPlataformaController extends Controller{

    private $session;
    public $currentyear;
    public $userlogged;
    public $arrDataRequestPlataforma;
    public $arrPlataforma;
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $this->currentyear = $this->session->get('currentyear');
        $this->userlogged = $this->session->get('userId');
        $this->arrDataRequestPlataforma = array();
        $this->arrPlataforma = array(
            1 => 'Dominio',
            2 => 'Moodle',
            3 => 'Classroom',
            4 => 'Microsoft Teams',
        );    
    }   	
    
    public function indexAction(Request $request){

        //validation if the user is logged
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }        
        // db conexion
        $em = $this->getDoctrine()->getManager();
        $swCompleteRequest = 0;  	
        $swresponse = 0; 
        $endRequest = 0; 
        $arrRequestDominioInfo = array(
                    'requestPlataforma'=> '',            
                    'requestDominio'=> '',            
                    'requestIp'=> '',
        );

        // get info about UE
        
    	$objPlataforma = $em->getRepository('SieAppWebBundle:InstitucioneducativaPlataforma')->findOneBy(array(
    		'institucioneducativa' => $this->session->get('ie_id')
    	));
     

    	$arrPersonaDirector=array();
    	$arrPersonaResponsable=array(
    		'carnet'=>'',
    		'complemento'=>'',
    		'paterno'=>'',
			'materno'=>'',
			'nombre'=>'',
			'correo'=>'',
			'celular'=>'',
			'personId'=>'',
			'fecnac'=>'',
    	);
    	if(sizeof($objPlataforma)>0){

    		// $objPersonaDirector = $em->getRepository('SieAppWebBundle:Persona')->find($objPlataforma->getDirectorPersona());
    		// $arrPersonaDirector=array(
    		// 	'paterno'=>$objPersonaDirector->getPaterno(),
    		// 	'materno'=>$objPersonaDirector->getMaterno(),
    		// 	'nombre'=>$objPersonaDirector->getNombre(),
    		// 	'correo'=>$objPersonaDirector->getCorreo(),
    		// 	'celular'=>$objPersonaDirector->getCelular(),
    		// );
    		$objPersonaResponsable = $em->getRepository('SieAppWebBundle:Persona')->find($objPlataforma->getResponsablePersona());
    		$arrPersonaResponsable=array(
    			'carnet'=>$objPersonaResponsable->getCarnet(),
    			'complemento'=>$objPersonaResponsable->getComplemento(),
    			'paterno'=>$objPersonaResponsable->getPaterno(),
    			'materno'=>$objPersonaResponsable->getMaterno(),
    			'nombre'=>$objPersonaResponsable->getNombre(),
    			'correo'=>$objPersonaResponsable->getCorreo(),
    			'celular'=>$objPersonaResponsable->getCelular(),
    			'personId'=>$objPersonaResponsable->getId(),
    			'fecnac'=>$objPersonaResponsable->getFechaNacimiento()->format('m-d-Y'),
    		);

    		$swCompleteRequest = $objPlataforma->getEstado();

             //if($objPlataforma->getPlataforma()==1){
             if( in_array($objPlataforma->getPlataforma(), array(3,4) ) ){
                        $requestPlataforma = $this->arrPlataforma[$objPlataforma->getPlataforma()];
                        $ip = $objPlataforma->getIp();
                    }else{
                        $requestPlataforma = $this->arrPlataforma[$objPlataforma->getPlataforma()];    
                        $ip = '';
                    }
                    $endRequest = ($objPlataforma->getEstado()==1)?1:0;
                    $dominio = $objPlataforma->getDominio();
                    $arrRequestDominioInfo = array(
                        'requestPlataforma'=> $requestPlataforma,            
                        'requestDominio'=> $dominio,            
                        'requestIp'=> $ip,
                    );
                    
    		$swresponse = true;
    	}
    	
        
        $objInstitucionEducativa =  $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id'));
        $objJurisdiccionGeografica =  $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->find($objInstitucionEducativa->getLeJuridicciongeografica());
        //dump($objJurisdiccionGeografica->getDistritoTipo()->getId());

        // $arrInstitucionEducativa = array(
        // 	'sie'=>$objInstitucionEducativa->getId(),
        // 	'institucioneducativa'=>$objInstitucionEducativa->getInstitucioneducativa(),
        // );
        $dataUe = array(
        	'sie'=>$objInstitucionEducativa->getId(),
        	'gestion'=>$this->currentyear,
        	'rol'=>array(1,12),

        );
        $dataDir =  $this->getDirector($dataUe);
        $dataDir[0]['requestSite'] = mb_strtolower(substr(str_replace(' ', '', $objInstitucionEducativa->getInstitucioneducativa()), 0,8),'UTF-8').''.$objJurisdiccionGeografica->getDistritoTipo()->getId().'.edu.bo';

        // dump($objInstitucionEducativa);die;

        // dump($swCompleteRequest);die;
        return $this->render('SieHerramientaBundle:UnidadEducativaPlataforma:index.html.twig', array(
                'DataInstitucioneducativa' => $objInstitucionEducativa,
                'dataDir' => $dataDir[0],
                // 'arrPersonaDirector' => $arrPersonaDirector,
                'arrPersonaResponsable' => $arrPersonaResponsable,
                'swresponse' => $swresponse,
                'swCompleteRequest' => $swCompleteRequest,
                'arrRequestDominioInfo' => $arrRequestDominioInfo,
                'endRequest' => $endRequest,
            ));    
    }

    private function getDirector($data){

        // db conexion
        $em = $this->getDoctrine()->getManager();    	


            $directors = $em->createQueryBuilder()
                   ->select('mi.id, p.paterno, p.materno, p.nombre, p.carnet, p.complemento,rt.id as rolID, p.correo, p.celular, p.id as personId, ie.id as sie')
                   ->from('SieAppWebBundle:MaestroInscripcion','mi')
                   ->innerJoin('SieAppWebBundle:Persona','p','with','mi.persona = p.id')
                   ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','mi.institucioneducativa = ie.id')
                   ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','mi.gestionTipo = gt.id')
                   ->innerJoin('SieAppWebBundle:CargoTipo','ct','with','mi.cargoTipo = ct.id')
                   ->innerJoin('SieAppWebBundle:RolTipo','rt','with','ct.rolTipo = rt.id')
                   ->where('ie.id = :idInstitucion')
                   ->andWhere('gt.id = :gestion')
                   ->andWhere('mi.cargoTipo IN (:cargos)')
                   ->setParameter('idInstitucion',$data['sie'])
                   ->setParameter('gestion',$data['gestion'])
                   ->setParameter('cargos',array(1,12))
                   ->orderBy('p.paterno','asc')
                   ->addOrderBy('p.materno','asc')
                   ->addOrderBy('p.nombre','asc')
                   ->getQuery()
                   ->getResult();
           return ($directors);
           
    }

	public function checkSegipResponsableAction(Request $request){
		 // ini vars
        $response = new JsonResponse();
        $newpersona = 0;
        $swUpdateSegip = 0;
        $responsablePersonaId = 0;
        
        $form = array(
        	'paterno'=>$request->get('paterno'),
        	'materno'=>$request->get('materno'),
        	'nombre'=>$request->get('nombre'),
        	'complemento'=>$request->get('complemento'),
        	'fechaNacimiento'=>$request->get('fecnac'),
        	'ci'=>$request->get('carnet'),
        );
// dump($form);
        if($form['ci']){        	

            $arrParametros = array('complemento'=>$form['complemento'],
                'primer_apellido'=>$form['paterno'],
                'segundo_apellido'=>$form['materno'],
                'nombre'=>$form['nombre'],
                'fecha_nacimiento'=>$form['fechaNacimiento']);

            $answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $form['ci'],$arrParametros,'prod', 'academico');

        }
// dump($answerSegip);die;
        // check validations answerSegip
        if($answerSegip){

        	// check if the person is on SIE DB

        	$em = $this->getDoctrine()->getManager();
	        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
	            'carnet'=>$form['ci'],
	            'complemento'=>mb_strtoupper($form['complemento'],'utf-8'),
	            'paterno'=>mb_strtoupper($form['paterno']),
	            'materno'=>mb_strtoupper($form['materno']),
	            'nombre'=>mb_strtoupper($form['nombre']),
	            'fechaNacimiento'=>new \DateTime($form['fechaNacimiento']),
	        ));

	        if(is_object($persona)){
	        	// // do update only fono and email
	        	// $persona->setCorreo($request->get('correo'));
	        	// $persona->setCelular($request->get('celular'));
	        	// $em->persist($persona);
	        	// $em->flush();
	        	// $personaId = $persona->getId();
				$responsablePersonaId = $persona->getId();

	        	if(!$persona->getSegipId()){
			        $swUpdateSegip = 1;
	        	}
	        }else{
	        	$newpersona = 1;
	        }

        	$swresponse=true;
            $status='success';
            $code = 200;
            $message = 'correct';

        }else{
        	$swresponse=false;
            $status='error';
            $code = 404;
            $message = 'wrong';
        }

        
        $arrResponse = array(
            'status'          => $status,
            'code'            => $code,
            'message'         => $message,
            'swresponse' => $swresponse,
            'swUpdateSegip' => $swUpdateSegip,
            'newpersona' => $newpersona,
            'responsablePersonaId' => $responsablePersonaId,
                      
        );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
	}

	public function enterDataRequestAction(Request $request){

		// dump($request);die;
    	 // ini vars
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();  
		// get the send data
		$this->arrDataRequestPlataforma = array(
			'dataDirector'=>$request->get('dataDirector'),
			'dataResponsable'=>$request->get('dataResponsable'),
			'dataDominio'=>$request->get('dataDominio')
		);
		 //dump($this->arrDataRequestPlataforma); die;
		try {
		

		// save the responsable data

			if($this->arrDataRequestPlataforma['dataResponsable']['newpersona']){

	            $newpersona = new Persona();
	            $newpersona->setActivo(1);
	            $newpersona->setCarnet($this->arrDataRequestPlataforma['dataResponsable']['carnet']);
	            $newpersona->setComplemento('');            
	            $newpersona->setDireccion('dir');
	            $newpersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findOneById(0));
	            $newpersona->setFechaNacimiento(new \DateTime($this->arrDataRequestPlataforma['dataResponsable']['fecnac']));
	            $newpersona->setFoto('');
	            $newpersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find(3));
	            $newpersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find(97));
	            $newpersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find(0));
	            $newpersona->setLibretaMilitar('');
	            $newpersona->setPaterno(mb_strtoupper($this->arrDataRequestPlataforma['dataResponsable']['paterno'], "utf-8"));
	            $newpersona->setMaterno(mb_strtoupper($this->arrDataRequestPlataforma['dataResponsable']['materno'], "utf-8"));
	            $newpersona->setNombre(mb_strtoupper($this->arrDataRequestPlataforma['dataResponsable']['nombre'], "utf-8"));
	            $newpersona->setPasaporte('');
	            $newpersona->setEsvigente('t');
	            $newpersona->setRda(0);
	            $newpersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById(0));
	        
			}
			if($this->arrDataRequestPlataforma['dataResponsable']['personId']>0){
				$newpersona = $em->getRepository('SieAppWebBundle:Persona')->find($this->arrDataRequestPlataforma['dataResponsable']['personId']);
			}
			$newpersona->setCelular($this->arrDataRequestPlataforma['dataResponsable']['celular']);
			$newpersona->setCorreo($this->arrDataRequestPlataforma['dataResponsable']['correo']);
			if($this->arrDataRequestPlataforma['dataResponsable']['swUpdateSegip']){
				$newpersona->setSegipId(1);	
			}
            
            $em->persist($newpersona);
            // check if existe request about dominio to the UE
            
	    	$objPlataforma = $em->getRepository('SieAppWebBundle:InstitucioneducativaPlataforma')->findOneBy(array(
	    		'institucioneducativa' => $this->session->get('ie_id')
	    	));            	
            if(!is_object( $objPlataforma )  ){
            	$objPlataforma = new InstitucioneducativaPlataforma();
            }			
			
			//$objPlataforma->setPlataforma($this->arrDataRequestPlataforma['dataDominio']['plataforma']=='false'?0:1);
            /*if($this->arrDataRequestPlataforma['dataDominio']['opcion']==1){
                $objPlataforma->setPlataforma($this->arrDataRequestPlataforma['dataDominio']['opcion']);
            }else{*/
                $objPlataforma->setPlataforma($this->arrDataRequestPlataforma['dataDominio']['opcionreqdomi']);
            //}
			
			$objPlataforma->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
			$objPlataforma->setDirectorPersona($em->getRepository('SieAppWebBundle:Persona')->find($this->arrDataRequestPlataforma['dataDirector']['personId']));
			$objPlataforma->setResponsablePersona($em->getRepository('SieAppWebBundle:Persona')->find($newpersona->getId()));
            if($this->arrDataRequestPlataforma['dataDominio']['opcion']==1){
                $objPlataforma->setDominio($this->arrDataRequestPlataforma['dataDominio']['dominio']);
                $objPlataforma->setIP($this->arrDataRequestPlataforma['dataDominio']['ip']);
            }else{
                 $objPlataforma->setDominio($this->arrDataRequestPlataforma['dataDominio']['requestSite']);
                 $objPlataforma->setIP('');
            }
			
			$objPlataforma->setCelDirector($this->arrDataRequestPlataforma['dataDirector']['celular']);
			$objPlataforma->setCelResponsable($this->arrDataRequestPlataforma['dataResponsable']['celular']);
			$objPlataforma->setDocumento('.');
			$objPlataforma->setEstado(0);
			$objPlataforma->setFechaRegistro(new \DateTime('now'));

			$em->persist($objPlataforma);
			$em->flush();

	            // Try and commit the transaction
	            $em->getConnection()->commit();		

		 } catch (Exception $ex) {
		            $em->getConnection()->rollback();
		            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
		 }


            $swattach=true;
            $swhistory=true;
            $status='success';
            $code = 200;
            $message = 'this is a test';


        $arrResponse = array(
            'status'          => $status,
            'code'            => $code,
            'message'         => $message,
            'swattach' 		  => $swattach,
                      
        );

        // dump($arrResponse);die;

      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;		
		die;
	}

  public function generateRequestDominioPDFAction(Request $request)
  {
	    // get the send values
	    $sie = $request->get('sie');
		$em = $this->getDoctrine()->getManager();
		$objGestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById(2020);
	    $objPlataforma = $em->getRepository('SieAppWebBundle:InstitucioneducativaPlataforma')->findOneBy(array(
	      'institucioneducativa' => $sie
	    ));

		$objInstitucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($sie);
	    $objPersonaDirector = $em->getRepository('SieAppWebBundle:Persona')->find($objPlataforma->getDirectorPersona());
	    $objPersonaResponsable = $em->getRepository('SieAppWebBundle:Persona')->find($objPlataforma->getResponsablePersona());
		

		$objMaestroInscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array(
			'persona' => $objPlataforma->getDirectorPersona(),
			'gestionTipo' => $objGestionTipo,
			'institucioneducativa' => $objInstitucioneducativa,
			'esVigenteAdministrativo' => true
		));

		$nombreCompleto =  $objPersonaDirector->getNombre() . " " . $objPersonaDirector->getMaterno() . " " . $objPersonaDirector->getPaterno();
		$carnet = $objPersonaDirector->getCarnet();

	    $html = '<div>
	      <div style="text-align: left;">
	        <p>La Paz, 20 de julio de 2020</p>
	      </div>
	      
	      <div style="text-align: left;">
	        <p>Señor Lic. José Luis Machicado Moya<br />
	        <strong>DIRECTOR EJECUTIVO DE LA ADSIB a.i.</strong><br />
	        <u>Presente.-</u></p>
	      </div>

	      <div style="text-align: right;">
	        <p><strong><u>Ref. : Solicitud de registro de nombre de dominio con extensión .edu.bo</u></strong></p>
	      </div>

	      <div style="text-align: justify;">
	        <p>De mi consideración:</p>

	        <p>Mediante la presente, solicito a su autoridad el registro de nombre de dominio de la Institución Educativa <strong>' . $objPlataforma->getInstitucioneducativa()->getInstitucioneducativa() . '</strong>.</p>

	        <p>Mi persona <strong>' . $nombreCompleto . '</strong> con C.I. N° <strong>' . $carnet . '</strong> en calidad de <strong>' . $objMaestroInscripcion->getCargoTipo()->getCargo() . '</strong>, se compromete a que el uso de dicho  domino por parte de la Institución Educativa <strong>' . $objPlataforma->getInstitucioneducativa()->getInstitucioneducativa() . '</strong>, se realizará de acuerdo a protocolos establecidos.</p>

	        <p>Sin otro particular, saludo a usted con las correspondientes consideraciones.</p>
		  </div>
		  
		  <div style="text-align: center;">
			<p><strong>
				FIRMA<br />
				' . $nombreCompleto . '<br />
				C.I. N° ' . $carnet . '<br />
			</strong></p>
		  </div>
		  
		  <div style="text-align: left;">
			<p><small><i>c.c.Archivo/Personal</i></small></p>
	      </div>
	    </div>';

	    $pdf = $this->get("white_october.tcpdf")->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', true);
	    $pdf->SetAuthor('Ministerio de Educación, Deportes y Culturas');
	    $pdf->SetTitle(('Solicitud de Dominio'));
	    $pdf->SetSubject('Solicitud de Dominio');
	    $pdf ->SetPrintHeader(false);
	    $pdf ->SetPrintFooter(false);
	    $pdf->setFontSubsetting(true);
	    $pdf->SetFont('helvetica', '', 11, '', true);
	    $pdf->SetMargins(25,20,20, true);
	    $pdf->AddPage();
	    
	    $filename = 'solicitud_dominio_' . $sie . '_' . date('YmdHis');
	    
	    $pdf->writeHTML($html, true, false, true, false, '');
	    // $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
	    $pdf->Output($filename.".pdf",'I');    
  	}
	
	public function sendRequestAction(Request $request){
 		// ini vars
        $response = new JsonResponse();		

		$allData = json_decode($request->get('datos'), true);
		$dataDirector=$allData['dataDirector'];
        /*
		$documentocartaSolicitud = $_FILES['documentocartaSolicitud'];
		$documentoidoneidad      = $_FILES['documentoidoneidad'];
		$documentocarnet = $_FILES['documentocarnet'];
		$documentofotcopiaRM = $_FILES['documentofotcopiaRM'];
        */
		$em = $this->getDoctrine()->getManager();

		// move all files to the server
        $arrPathsDocs = array();
        /*
		$pathdocumentocartaSolicitud = $this->saveDocument($documentocartaSolicitud, $dataDirector['sie']);
		$pathdocumentoidoneidad = $this->saveDocument($documentoidoneidad, $dataDirector['sie']);
		$pathdocumentocarnet = $this->saveDocument($documentocarnet, $dataDirector['sie']);
		$pathdocumentofotcopiaRM = $this->saveDocument($documentofotcopiaRM, $dataDirector['sie']);
        
		// get all files to save
		$arrPathsDocs = array(
			'cartasolicitud'=>$pathdocumentocartaSolicitud,
			'documentoidoneidad'=>$pathdocumentoidoneidad,
			'documentocarnet'=>$pathdocumentocarnet,
			'documentofotcopiaRM'=>$pathdocumentofotcopiaRM,
		);
        */
		try {


			     $objPlataforma = $em->getRepository('SieAppWebBundle:InstitucioneducativaPlataforma')->findOneBy(array(
		    		'institucioneducativa' => $dataDirector['sie']
		    	));

                    if( in_array($objPlataforma->getPlataforma(), array(3,4) ) ){
                        $requestPlataforma = $this->arrPlataforma[$objPlataforma->getPlataforma()];
                        $ip = $objPlataforma->getIp();
                    }else{
                        $requestPlataforma = $this->arrPlataforma[$objPlataforma->getPlataforma()];    
                        $ip = '';
                    }
                    $endRequest = ($objPlataforma->getEstado()==1)?1:0;
                    $dominio = $objPlataforma->getDominio();
                    $arrRequestDominioInfo = array(
                        'requestPlataforma'=> $requestPlataforma,            
                        'requestDominio'=> $dominio,            
                        'requestIp'=> $ip,
                    );


		    	$objPlataforma->setEstado(1);
		    	$objPlataforma->setJson(json_encode($arrPathsDocs));
		    	$em->persist($objPlataforma);
		    	$em->flush();

		 		$swCompleteRequest=true;
		        $status='success';
		        $code = 200;
		        $message = 'this is a test';
		      
			
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }

		

	    $arrResponse = array(
	        'status'            => $status,
	        'code'              => $code,
	        'message'           => $message,
	        'swCompleteRequest' => $swCompleteRequest,
            'arrRequestDominioInfo' => $arrRequestDominioInfo,
	                  
	    );

	    $response->setStatusCode(200);
		$response->setData($arrResponse);	

      return $response;			
		

	}

	private function saveDocument($document, $id){

                // check if the file exists
    	if(isset($document)){
            $file = $document;
            $type = $file['type'];
            $size = $file['size'];
            $tmp_name = $file['tmp_name'];
            $name = $file['name'];
            $extension = explode('.', $name);
            $extension = $extension[count($extension)-1];
            $new_name = $id.'_'.$name.'_'.date('YmdHis').'.'.$extension;
            // GUARDAMOS EL ARCHIVO
            $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/solicitudDominio/' .date('Y');

            if (!file_exists($directorio)) {
                mkdir($directorio, 0775, true);
            }
            $directoriomove = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/solicitudDominio/' .date('Y').'/'.$id;
            if (!file_exists($directoriomove)) {
                mkdir($directoriomove, 0775, true);
            }

            $archivador = $directoriomove.'/'.$new_name;
            //unlink($archivador);
            if(!move_uploaded_file($tmp_name, $archivador)){
    			echo 'Excepción capturada: ', $ex->getMessage(), "\n";
    			return false;
    			die;
            }
              
        }else{
            $informe = null;
            $archivador = 'empty';
            return false;
        }
    	return $new_name;
	}

}
