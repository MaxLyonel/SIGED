<?php

namespace Sie\EspecialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaSucursalEspecialCierre;

/**
 * EstudianteInscripcion controller.
 *
 */
class ReportesController extends Controller {

    public $session;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }
    
    public function repUnoAction(Request $request) {
        $arch = 'REPORTE_Adm_'.$request->get('idInstitucion').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_lst_Administrativos_v1_cc.rptdesign&institucioneducativa_id='.$request->get('idInstitucion').'&gestion_tipo_id='.$request->get('gestion').'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    
    public function repDosAction(Request $request) {
        $arch = 'REPORTE_Doc_'.$request->get('idInstitucion').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_lst_Docentes_v1_cc.rptdesign&institucioneducativa_id='.$request->get('idInstitucion').'&gestion_tipo_id='.$request->get('gestion').'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    
    public function repTresAction(Request $request) {
        $arch = 'REPORTE_Gral_'.$request->get('idInstitucion').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_caratula_CEE_gral_v1_cc.rptdesign&cod_ue='.$request->get('idInstitucion').'&gestion_id='.$request->get('gestion').'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    
    public function repCuatroAction(Request $request) {
        $arch = 'REPORTE_Part_'.$request->get('idInstitucion').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_lst_Participantes_v3.rptdesign&institucioneducativa_id='.$request->get('idInstitucion').'&gestion_tipo_id='.$request->get('gestion').'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function finalizaInscripcionAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        
        $ies = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa' => $request->get('idInstitucion'), 'gestionTipo' => $request->get('gestion'), 'sucursalTipo' => 0));

        $iesec = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursalEspecialCierre')->findOneBy(array('institucioneducativaSucursal' => $ies, 'esactivo' => 't'));

        if($iesec){
            return $this->redirect($this->generateUrl('estudianteinscripcion_especial'));
        }      
        
        $newIesec = new InstitucioneducativaSucursalEspecialCierre();
        $newIesec->setInstitucioneducativaSucursal($ies);
        $newIesec->setFecha(new \DateTime('now'));
        $newIesec->setObs("");
        $newIesec->setEsactivo(1);

        $em->persist($newIesec);
        $em->flush();

        $arch = 'REPORTE_Part_'.$request->get('idInstitucion').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_lst_Participantes_v3.rptdesign&institucioneducativa_id='.$request->get('idInstitucion').'&gestion_tipo_id='.$request->get('gestion').'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    
    public function informacionGeneralEspecialPrintPdfAction(Request $request) {
    	/*
    	 * Define la zona horaria y halla la fecha actual
    	 */
    	date_default_timezone_set('America/La_Paz');
    	$fechaActual = new \DateTime(date('Y-m-d'));
    	//$gestionActual = date_format($fechaActual,'Y');
    	$gestionActual = 2016;
    
    	if ($request->isMethod('POST')) {
    		/*
    		 * Recupera datos del formulario
    		 */
    		$gestion = $request->get('gestion');
    		$lugarId = $request->get('lugar_id');
    		$nivelLugarId = $request->get('nivel_lugar_id');
    		$fechaVista = $request->get('fecha_vista');
//     		dump($lugarId);dump($nivelLugarId);die;
    	} else {
    		$gestion = $gestionActual;
    		$lugarId = 0;
    		$nivelLugarId = 0;
    		$fechaVista = $request->get('fecha_vista');
    	}
    
    	$em = $this->getDoctrine()->getManager();
    
    	$arch = 'MinEdu_'.$lugarId.'_'.$gestion.'_'.date('YmdHis').'.pdf';
    	$response = new Response();
    	$response->headers->set('Content-type', 'application/pdf');
    	$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
    
    	// por defecto
    	$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadistica_v1_jaq.rptdesign&__format=pdf&gestion='.$gestion.'&nivel_lugar='.$nivelLugarId.'&lugar_id='.$lugarId));
    	 
    	$response->setStatusCode(200);
    	$response->headers->set('Content-Transfer-Encoding', 'binary');
    	$response->headers->set('Pragma', 'no-cache');
    	$response->headers->set('Expires', '0');
    	return $response;
    }
    
    public function informacionGeneralEspecialPrintXlsAction(Request $request) {
    	/*
    	 * Define la zona horaria y halla la fecha actual
    	 */

    	date_default_timezone_set('America/La_Paz');
    	$fechaActual = new \DateTime(date('Y-m-d'));
    	//$gestionActual = date_format($fechaActual,'Y');
    	$gestionActual = 2016;
    	
    	if ($request->isMethod('POST')) {
    		/*
    		 * Recupera datos del formulario
    		 */
    		$gestion = $request->get('gestion');
    		$lugarId = $request->get('lugar_id');
    		$nivelLugarId = $request->get('nivel_lugar_id');
    		$fechaVista = $request->get('fecha_vista');
    		//     		if ($nivelLugarId != 1) {
//     			dump($fechaVista);die;
//     		}
    		
    	} else {
    		$gestion = $gestionActual;
    		$lugarId = 0;
    		$nivelLugarId = 0;
    		$fechaVista = $request->get('fecha_vista');
    		
    	}
    	
    	$em = $this->getDoctrine()->getManager();
    	
    	$arch = 'MinEdu_'.$lugarId.'_'.$gestion.'_'.date('YmdHis').'.xls';
    	$response = new Response();
    	$response->headers->set('Content-type', 'application/vnd.ms-excel');
    	$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
    	
    	// por defecto
//     	$cad = 'http://172.20.0.117:8080/birt-viewer/frameset?__report=siged/esp_est_InformacionEstadistica_v1_jaq.rptdesign&__format=xls&gestion='.$gestion.'&nivel_lugar='.$nivelLugarId.'&lugar_id='.$lugarId;
//     	dump($cad);die;
    	$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadistica_v1_jaq.rptdesign&__format=xls&gestion='.$gestion.'&nivel_lugar='.$nivelLugarId.'&lugar_id='.$lugarId));
    	 
//     	$response->setContent(file_get_contents('http://172.20.0.117:8080/birt-viewer/frameset?__report=siged/esp_est_InformacionEstadistica_v1_jaq.rptdesign&__format=xls&gestion='.$gestion.'&nivel_lugar='.$nivelLugarId.'&lugar_id='.$lugarId.'&fecha_vista='.$fechaVista));
    	
    	
    	$response->setStatusCode(200);
    	$response->headers->set('Content-Transfer-Encoding', 'binary');
    	$response->headers->set('Pragma', 'no-cache');
    	$response->headers->set('Expires', '0');
    	return $response;
    }
    

    public function impresionUECaratulaAction(Request $request) {
    
    	$sie = $request->get('sie');
    	$ges = $request->get('gestion');
    	$arch = $sie.'caratula_'.$ges. '_'.date('YmdHis').'.pdf';
    
    	$response = new Response();
    	$response->headers->set('Content-type', 'application/pdf');
    	$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
    	$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionInstitucion_v1_jaq.rptdesign&&institucioneducativa_id='.$sie.'&&gestion='.$ges.'&&__format=pdf&'));
    	$response->setStatusCode(200);
    	$response->headers->set('Content-Transfer-Encoding', 'binary');
    	$response->headers->set('Pragma', 'no-cache');
    	$response->headers->set('Expires', '0');
    	return $response;
    }
    
    public function impresionUEInformacionEstadisticaAction(Request $request) {
    
    	$sie = $request->get('sie');
    	$ges = $request->get('gestion');
    	$arch = $sie.'infestaditica_'.$ges. '_'.date('YmdHis').'.pdf';
    	$response = new Response();
    	$response->headers->set('Content-type', 'application/pdf');
    	$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
    	$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionEstadisticaInstitucion_v1_jaq.rptdesign&&institucioneducativa_id='.$sie.'&&gestion='.$ges.'&&__format=pdf&'));
    	$response->setStatusCode(200);
    	$response->headers->set('Content-Transfer-Encoding', 'binary');
    	$response->headers->set('Pragma', 'no-cache');
    	$response->headers->set('Expires', '0');
    	return $response;
    }
    
    
    public function impresionUEDocenteAction(Request $request) {
    
    	$sie = $request->get('sie');
    	$ges = $request->get('gestion');
    	$arch = $sie.'docente_'.$ges. '_'.date('YmdHis').'.pdf';
    
    	$response = new Response();
    	$response->headers->set('Content-type', 'application/pdf');
    	$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
    	$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionDocente_v1_jaq.rptdesign&&institucioneducativa_id='.$sie.'&&gestion='.$ges.'&&__format=pdf&'));
    	$response->setStatusCode(200);
    	$response->headers->set('Content-Transfer-Encoding', 'binary');
    	$response->headers->set('Pragma', 'no-cache');
    	$response->headers->set('Expires', '0');
    	return $response;
    }
    
    public function impresionUEAdministrativoAction(Request $request) {
    
    	$sie = $request->get('sie');
    	$ges = $request->get('gestion');
    	$arch = $sie.'administrativo_'.$ges. '_'.date('YmdHis').'.pdf';
    
    	$response = new Response();
    	$response->headers->set('Content-type', 'application/pdf');
    	$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
    	$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'esp_est_InformacionAdministrativo_v1_jaq.rptdesign&&institucioneducativa_id='.$sie.'&&gestion='.$ges.'&&__format=pdf&'));
    	$response->setStatusCode(200);
    	$response->headers->set('Content-Transfer-Encoding', 'binary');
    	$response->headers->set('Pragma', 'no-cache');
    	$response->headers->set('Expires', '0');
    	return $response;
    }
    
    
    
    
}
