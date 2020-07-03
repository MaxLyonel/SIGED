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
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $this->currentyear = $this->session->get('currentyear');
        $this->userlogged = $this->session->get('userId');
        $this->arrDataRequestPlataforma = array();
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
    		$swresponse = true;
    	}
    	
        
        $objInstitucionEducativa =  $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id'));
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
        // dump($dataDir);die;

        // dump($objInstitucionEducativa);die;

        // dump($swCompleteRequest);die;
        return $this->render('SieHerramientaBundle:UnidadEducativaPlataforma:index.html.twig', array(
                'DataInstitucioneducativa' => $objInstitucionEducativa,
                'dataDir' => $dataDir[0],
                // 'arrPersonaDirector' => $arrPersonaDirector,
                'arrPersonaResponsable' => $arrPersonaResponsable,
                'swresponse' => $swresponse,
                'swCompleteRequest' => $swCompleteRequest,
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
		// dump($this->arrDataRequestPlataforma); die;

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
			
			$objPlataforma->setPlataforma($this->arrDataRequestPlataforma['dataDominio']['plataforma']=='false'?0:1);
			
			$objPlataforma->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
			$objPlataforma->setDirectorPersona($em->getRepository('SieAppWebBundle:Persona')->find($this->arrDataRequestPlataforma['dataDirector']['personId']));
			$objPlataforma->setResponsablePersona($em->getRepository('SieAppWebBundle:Persona')->find($newpersona->getId()));
			$objPlataforma->setDominio($this->arrDataRequestPlataforma['dataDominio']['dominio']);
			$objPlataforma->setIP($this->arrDataRequestPlataforma['dataDominio']['ip']);
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

    public function generateRequestDominioPDFAction(Request $request){

    	
    	// get the send values
    	$sie = $request->get('sie');
    	
    	$em = $this->getDoctrine()->getManager();
    	$objPlataforma = $em->getRepository('SieAppWebBundle:InstitucioneducativaPlataforma')->findOneBy(array(
    		'institucioneducativa' => $sie
    	));

    	$objPersonaDirector = $em->getRepository('SieAppWebBundle:Persona')->find($objPlataforma->getDirectorPersona());
    	$objPersonaResponsable = $em->getRepository('SieAppWebBundle:Persona')->find($objPlataforma->getResponsablePersona());

    
        $pdf = $this->container->get("white_october.tcpdf")->create(
            'PORTRATE', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', true
        );
        // $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetAuthor('krlos');
        $pdf->SetTitle('krlos test');
        $pdf->SetSubject('Report PDF');
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(true, -10);
        // $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 058', PDF_HEADER_STRING, array(10,10,0), array(255,255,255));
        $pdf->SetKeywords('TCPDF, PDF, krlos world');
        $pdf->setFontSubsetting(true);
        $pdf->SetMargins(10, 10, 10, true);
        $pdf->SetAutoPageBreak(true, 8);

        $pdf->SetFont('helvetica', '', 9, '', true);
        $pdf->startPageGroup();
        $pdf->AddPage('P', array(215.9, 274.4));//'P', 'LETTER'



        $solicitudHtml = '


<!DOCTYPE html><html><head><title>Carta_Solicitud_de_Admision</title><link rel="shortcut icon" href="https://ssl.gstatic.com/docs/documents/images/kix-favicon7.ico"><meta name="referrer" content="strict-origin-when-cross-origin"><style type="text/css" nonce="wp9nnTWldFccI6ZgdEP77Q">
      @import url("https://fonts.googleapis.com/css?family=Google+Sans");
      @import url("https://fonts.googleapis.com/css?family=Roboto");

      body {
        font-family: Roboto, arial, sans, sans-serif;
        margin: 0;
      }

      
      #header {
        align-items: center;
        background: white;
        border-bottom: 1px #ccc solid;
        display: flex;
        height: 60px;
        justify-content: space-between;
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 100;
      }

      #header #title {
        font-family: "Google Sans";
        font-size: large;
        margin: auto 0 auto 20px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 70%;
      }

      #header #interval {
        margin: auto 25px auto 0;
        font-family: Roboto;
        font-size: small;;
      }

      #footer {
        background: #f0f0f0;
        border-bottom: 1px #ccc solid;
        bottom: 0;
        font-family: Roboto;
        font-size: small;
        padding: 10px 10px;
        position: fixed;
        text-align: center;
        width: 100%;
      }

      #contents {
        padding: 100px 20% 50px 20%;
      }

      @media only screen and (max-device-width: 800px) {
        #header {
          border-bottom-width: 5px;
          height: auto;
          display: block;
        }

        #header #title {
          font-size: 3em;
          margin: auto 0 auto 20px;
          width: 90%;
        }

        #header #interval {
          font-size: 1.5em;
          margin: 10px 0 auto 25px;
        }

        #contents {
          padding: 150px 5% 80px;
        }

        #footer {
          font-size: 2em;
        }
      }

      .dash {
        padding: 0 6px;
      }
    </style></head><body><div id="header">
    
    </div><div id="contents">
    <style type="text/css">@import url(https://themes.googleusercontent.com/fonts/css?kit=fpjTOVmNbO4Lz34iLyptLUXza5VhXqVC6o75Eld_V98);ol{margin:0;padding:0}table td,table th{padding:0}.c1{color:#000000;font-weight:400;text-decoration:none;vertical-align:baseline;font-size:11pt;font-family:"Calibri";font-style:normal}.c8{padding-top:0pt;padding-bottom:10pt;line-height:1.1500000000000001;orphans:2;widows:2;text-align:center}.c0{padding-top:0pt;padding-bottom:0pt;line-height:1.1500000000000001;orphans:2;widows:2;text-align:left}.c2{padding-top:0pt;padding-bottom:10pt;line-height:1.1500000000000001;orphans:2;widows:2;text-align:left}.c7{padding-top:0pt;padding-bottom:10pt;line-height:1.1500000000000001;orphans:2;widows:2;text-align:right}.c3{padding-top:0pt;padding-bottom:10pt;line-height:1.1500000000000001;orphans:2;widows:2;text-align:justify}.c11{text-decoration:none;font-size:11pt;font-family:"Calibri";font-style:normal}.c12{background-color:#ffffff;max-width:441.9pt;padding:70.8pt 85pt 70.8pt 85pt}.c6{height:11pt}.c4{vertical-align:baseline}.c5{color:#ff0000}.c10{font-weight:700}.c9{color:#000000}.title{padding-top:24pt;color:#000000;font-weight:700;font-size:36pt;padding-bottom:6pt;font-family:"Calibri";line-height:1.1500000000000001;page-break-after:avoid;orphans:2;widows:2;text-align:left}.subtitle{padding-top:18pt;color:#666666;font-size:24pt;padding-bottom:4pt;font-family:"Georgia";line-height:1.1500000000000001;page-break-after:avoid;font-style:italic;orphans:2;widows:2;text-align:left}li{color:#000000;font-size:11pt;font-family:"Calibri"}p{margin:0;color:#000000;font-size:11pt;font-family:"Calibri"}h1{padding-top:24pt;color:#000000;font-weight:700;font-size:24pt;padding-bottom:6pt;font-family:"Calibri";line-height:1.1500000000000001;page-break-after:avoid;orphans:2;widows:2;text-align:left}h2{padding-top:18pt;color:#000000;font-weight:700;font-size:18pt;padding-bottom:4pt;font-family:"Calibri";line-height:1.1500000000000001;page-break-after:avoid;orphans:2;widows:2;text-align:left}h3{padding-top:14pt;color:#000000;font-weight:700;font-size:14pt;padding-bottom:4pt;font-family:"Calibri";line-height:1.1500000000000001;page-break-after:avoid;orphans:2;widows:2;text-align:left}h4{padding-top:12pt;color:#000000;font-weight:700;font-size:12pt;padding-bottom:2pt;font-family:"Calibri";line-height:1.1500000000000001;page-break-after:avoid;orphans:2;widows:2;text-align:left}h5{padding-top:11pt;color:#000000;font-weight:700;font-size:11pt;padding-bottom:2pt;font-family:"Calibri";line-height:1.1500000000000001;page-break-after:avoid;orphans:2;widows:2;text-align:left}h6{padding-top:10pt;color:#000000;font-weight:700;font-size:10pt;padding-bottom:2pt;font-family:"Calibri";line-height:1.1500000000000001;page-break-after:avoid;orphans:2;widows:2;text-align:left}</style>
    <p class="c8">
    	<span class="c4 c10">Carta solicitud de Plataforma</span>
    	</p><p class="c2 c6"><span class="c4 c10 c9 c11"></span></p><p class="c7"><span class="c1">31/10/2018                         </span></p><p class="c2 c6"><span class="c1"></span></p><p class="c0"><span class="c4">Coordinador DOMINIOS </span><span>Educativos</span></p><p class="c0"><span class="c4">Ministerio </span><span>de Educación, Cultura y Deporte </span><span class="c1">, A.C.</span></p><p class="c0"><span class="c1">Presente:</span></p><p class="c0 c6"><span class="c1"></span></p><p class="c2 c6"><span class="c1"></span></p><p class="c3">
    		<span class="c4">Por medio de la presente me dirijo a ustedes para expresar mi interés en la solicitud de la habilitación </span>
    		<span class="c4">de la Plataforma Educativa.</p>
    		<p class="c2"><span class="c1">Desarrollo de  interés y razones:</span></p>
    		<p class="c2">
    		<span>
    		Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Dapibus ultrices in iaculis nunc sed augue lacus. Quam nulla porttitor massa id neque aliquam. Ultrices mi tempus imperdiet nulla malesuada. Eros in cursus turpis massa tincidunt dui ut ornare lectus. Egestas sed sed risus pretium. Lorem dolor sed viverra ipsum. Gravida rutrum quisque non tellus. Rutrum tellus pellentesque eu tincidunt tortor. Sed blandit libero volutpat sed cras ornare. Et netus et malesuada fames ac. Ultrices eros in cursus turpis massa tincidunt dui ut ornare. Lacus sed viverra tellus in. Sollicitudin ac orci phasellus egestas. Purus in mollis nunc sed. Sollicitudin ac orci phasellus egestas tellus rutrum tellus pellentesque. Interdum consectetur libero id faucibus nisl tincidunt eget.
    		</span>
    		<p class="c3"><span class="c1">Así mismo me comprometo a dedicarme de tiempo completo a la correcta gestión de la Herramienta ...</span></p>
    		<p class="c2"><span class="c1">Atentamente</span></p><p class="c2 c6"><span class="c1"></span></p><p class="c2 c6"><span class="c1"></span></p>
    		<p class="c2 c6"><span class="c1"></span></p><p class="c8">
    		<span class="c1">'.$objPersonaDirector->getNombre().' '.$objPersonaDirector->getPaterno().' '.$objPersonaDirector->getMaterno().'</span></p>
    		<p class="c8"><span class="c1">CI: '.$objPersonaDirector->getCarnet().'</span></p><p class="c2"><span class="c1"> </span></p></div>

    	</body></html>

        ';  
        $pdf->writeHTML($solicitudHtml, false, false, true, false, '');      



        $pdf->Output("krlossss_".date('YmdHis').".pdf", 'I');

    }	
	
	public function sendRequestAction(Request $request){

 		// ini vars
        $response = new JsonResponse();		

		$dataDirector = $request->get('dataDirector');
		$em = $this->getDoctrine()->getManager();
    	$objPlataforma = $em->getRepository('SieAppWebBundle:InstitucioneducativaPlataforma')->findOneBy(array(
    		'institucioneducativa' => $dataDirector['sie']
    	));
    	$objPlataforma->setEstado(1);
    	$em->persist($objPlataforma);
    	$em->flush();



 		$swCompleteRequest=true;
        $status='success';
        $code = 200;
        $message = 'this is a test';

        $arrResponse = array(
            'status'            => $status,
            'code'              => $code,
            'message'           => $message,
            'swCompleteRequest' => $swCompleteRequest,
                      
        );

        // dump($arrResponse);die;

      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;			
		

	}    	   	

}
