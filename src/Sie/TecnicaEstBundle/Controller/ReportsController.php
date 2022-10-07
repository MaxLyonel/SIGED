<?php

namespace Sie\TecnicaEstBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;
use Sie\AppWebBundle\Entity\EstTecAutoridadInstituto;
use Sie\AppWebBundle\Entity\Persona;

class ReportsController extends Controller{
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

    public function index2Action(){
        return $this->render('SieTecnicaEstBundle:Reports:index.html.twig', array(
                // ...
            ));    }

    /** krlos
     * the method to decrypt
     */
    private function kdecrypt($data){
        $data = hex2bin($data);
        return unserialize($data);
    }

    public function indexAction(Request $request){
        // $form = $request->get('form');
        
        // $data = ($this->kdecrypt($form['data']));
        return $this->render('SieTecnicaEstBundle:Reports:index.html.twig', array(
                'sedeId' => $this->baseData['sedeId']
            ));    
    }

    public function getAllOperativeAction(Request $request){
        $response = new JsonResponse();             

        $arrData = array('sedeId'=> $request->get('sedeId'));
        
        $arrOperative = $this->get('tecestfunctions')->getAllOperative($arrData);
          $arrResponse = array(             
            'arrOperative' => $arrOperative,
          );
          
          $response->setStatusCode(200);
          $response->setData($arrResponse);        
          return $response;        
    }

    public function getAllReportsAction(Request $request){ 

    	$response = new JsonResponse();
    	// $arrRequest = array('sedeId' => 64,  'userId' => 13820843, 'yearSelected' => $request->get('year'));
        $this->baseData['yearSelected'] = $request->get('year');

    	$arrRegisteredStaff = array();//$this->get('univfunctions')->getAllStaff($this->baseData);
		
      $this->baseData['urlreporte'] = $this->generateUrl('tecest_reports_downloadreportgeneral', array('gestion'=>$this->baseData['yearSelected'],'id_sede'=>$this->baseData['sedeId'],'statusope'=> ($this->get('tecestfunctions')->getOperativeStatus($this->baseData)== null || $this->get('tecestfunctions')->getOperativeStatus($this->baseData)== false)?0:1 ));
	
        $this->baseData['reportDetail'] = ($this->get('tecestfunctions')->getOperativeStatus($this->baseData)== null || $this->get('tecestfunctions')->getOperativeStatus($this->baseData)== false)?'Reporte Oficial':'Reporte Preliminar' ;
	      $response->setStatusCode(200);
          $response->setData(array(
          	'swgetinfostaff' => true,
          	'datareports'         => array($this->baseData)
          ));


            return $response;		
		

    }

    public function downloadreportgeneralAction(Request $request, $gestion, $id_sede, $statusope){
    	
        $response = new Response();
        
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'reporteGeneral'.$id_sede.'_'.$this->session->get('currentyear'). '.pdf'));

        $nameReport = ($statusope)?'est_tec_reporte_estadistico_ejea_v1_borrador':'est_tec_reporte_estadistico_ejea_v1';
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') .$nameReport.'.rptdesign&gestion='.$gestion.'&id_sede='.$id_sede.'&&__format=pdf&'));

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }     


}
