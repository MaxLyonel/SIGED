<?php

namespace Sie\RueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class DownloadController extends Controller {

    /**
     * construct function 
     */
    public function __construct() {
        //load the session component
        $this->session = new Session();
    }

    /**
     * build the report download pdf
     * @param Request $request
     * @return object form login
     */
    public function certificadosAction(Request $request, $certificado) {
        //get the values of report
        //create the response object to down load the file
    	$em = $this->getDoctrine()->getManager();
    	$entity = $em->getRepository('SieAppWebBundle:CertificadoRue')->findOneById($certificado);
    	$response = new Response();
    	$response->headers->set('Content-type', 'application/pdf');
    	$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'certificado_' . $certificado . '.pdf'));
    	 
    	if (strpos($entity->getObservacion(), 'FASE') === FALSE) {
    		$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rue_cert_certificadoregular_ue_v1.rptdesign&certificado=' . $certificado . '&&__format=pdf&'));
    	}
    	else {
    		$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rue_cert_certificadoalternativa_ue_v1.rptdesign&certificado=' . $certificado . '&&__format=pdf&'));
    		
    	}
    	$response->setStatusCode(200);
    	$response->headers->set('Content-Transfer-Encoding', 'binary');
    	$response->headers->set('Pragma', 'no-cache');
    	$response->headers->set('Expires', '0');
    	return $response;
    }

//     public function listainstitucioneducativaAction(Request $request) {
//     	//get the values of report
//     	//create the response object to down load the file
//     	$response = new Response();
//     	$response->headers->set('Content-type', 'application/text/html');
// //     	$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'listainstitucioneducativa.xls'));
//     	$response->setContent(file_get_contents('http://localhost:8080/birt/frameset?__report=lista_institucion_educativa.rptdesign'));
//     	$response->setStatusCode(200);
//     	$response->headers->set('Content-Transfer-Encoding', 'binary');
//     	$response->headers->set('Pragma', 'no-cache');
//     	$response->headers->set('Expires', '0');
//     	return $response;
//     }
    
    public function listaAction(Request $request, $certificado) {
    	//get the values of report
    	//create the response object to down load the file
    	$response = new Response();
    	$response->headers->set('Content-type', 'application/pdf');
    	$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'lista_certificado_' . $certificado . '.pdf'));
    	$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rue_lst_certificado_ue_v1.rptdesign&certificado=' . $certificado . '&&__format=pdf&'));
    	$response->setStatusCode(200);
    	$response->headers->set('Content-Transfer-Encoding', 'binary');
    	$response->headers->set('Pragma', 'no-cache');
    	$response->headers->set('Expires', '0');
    	return $response;
    }
    
    public function listaxlsAction(Request $request, $certificado) {
    
    	$response = new Response();
    	$response->headers->set('Content-type', 'application/vnd.ms-excel');
    	$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'lista_certificado_' . $certificado . '.xls'));
    	$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rue_lst_certificado_ue_v1.rptdesign&certificado=' . $certificado . '&&__format=xls&'));
    	$response->setStatusCode(200);
    	$response->headers->set('Content-Transfer-Encoding', 'binary');
    	$response->headers->set('Pragma', 'no-cache');
    	$response->headers->set('Expires', '0');
    
    	return $response;
    }

	public function controlDepartamentoAction(Request $request, $certificado, $tipo) {
    	$response = new Response();
    	$response->headers->set('Content-type', 'application/pdf');
    	$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'control_departamento_' . $certificado . '.pdf'));
    	
		if($tipo == 1) {
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rue_control_entrega_certificado_departamento_regular_v1_rcm.rptdesign&certificado=' . $certificado . '&&__format=pdf&'));
		} else {
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rue_control_entrega_certificado_departamento_altesp_v1_rcm.rptdesign&certificado=' . $certificado . '&&__format=pdf&'));
		}

		$response->setStatusCode(200);
    	$response->headers->set('Content-Transfer-Encoding', 'binary');
    	$response->headers->set('Pragma', 'no-cache');
    	$response->headers->set('Expires', '0');
    	return $response;
    }

	public function controlDistritoAction(Request $request, $certificado, $tipo) {
    	$response = new Response();
    	$response->headers->set('Content-type', 'application/pdf');
    	$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'control_distrito_' . $certificado . '.pdf'));
    	
		if($tipo == 1) {
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rue_control_entrega_certificado_distrito_regular_v1_rcm.rptdesign&certificado=' . $certificado . '&&__format=pdf&'));
		} else {
			$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rue_control_entrega_certificado_distrito_altesp_v1_rcm.rptdesign&certificado=' . $certificado . '&&__format=pdf&'));
		}

    	$response->setStatusCode(200);
    	$response->headers->set('Content-Transfer-Encoding', 'binary');
    	$response->headers->set('Pragma', 'no-cache');
    	$response->headers->set('Expires', '0');
    	return $response;
    }

	public function listaResumenAction(Request $request, $certificado) {
    
    	$response = new Response();
    	$response->headers->set('Content-type', 'application/vnd.ms-excel');
    	$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'lista_control_certificado_' . $certificado . '.xls'));
    	$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rue_control_certificados_v1_rcm.rptdesign&certificado=' . $certificado . '&&__format=xls&'));
    	$response->setStatusCode(200);
    	$response->headers->set('Content-Transfer-Encoding', 'binary');
    	$response->headers->set('Pragma', 'no-cache');
    	$response->headers->set('Expires', '0');
    
    	return $response;
    }
    
}
