<?php

namespace Sie\RueBundle\Controller;

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

    public function indexAction(Request $request) {
		$em = $this->getDoctrine()->getManager();
    	$dep = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findAll();
		$depArray = array();
		//$depArray[-1] = '-Todos-';
    	foreach ($dep as $de) {
			if($de->getId() != 0){
				$depArray[$de->getId()] = $de->getDepartamento();
			}
		}
		
		$query = $em->createQuery(
		'SELECT DISTINCT eie.id,eie.estadoinstitucion
			FROM SieAppWebBundle:EstadoinstitucionTipo eie
			WHERE eie.id in (:id)
			ORDER BY eie.id ASC'
		)->setParameter('id', array(10,19));
		$estados = $query->getResult();
		$estadosArray = array();
		//$estadosArray[-1] = '-Todos-';
		for($i=0;$i<count($estados);$i++){
			$estadosArray[$estados[$i]['id']] = $estados[$i]['estadoinstitucion'];
		}

		$query = $em->createQuery(
		'SELECT DISTINCT iet.id,iet.descripcion
			FROM SieAppWebBundle:InstitucioneducativaTipo iet
			WHERE iet.id in (:id)
			ORDER BY iet.id ASC'
		)->setParameter('id', array(1,2,4,5,6));
		$tipos = $query->getResult();
		$tiposArray = array();
		//$tiposArray[-1] = '-Todos-';
		for($i=0;$i<count($tipos);$i++){
			$tiposArray[$tipos[$i]['id']] = $tipos[$i]['descripcion'];
		}

		$query = $em->createQuery(
		'SELECT DISTINCT det.id,det.dependencia
			FROM SieAppWebBundle:DependenciaTipo det
			WHERE det.id in (:id)
			ORDER BY det.id ASC'
		)->setParameter('id', array(1,2,3));
		$dependencias = $query->getResult();
		$dependenciasArray = array();
		//$dependenciasArray[-1] = '-Todos-';
		for($i=0;$i<count($dependencias);$i++){
			$dependenciasArray[$dependencias[$i]['id']] = $dependencias[$i]['dependencia'];
		}
        
		$form = $this->createFormBuilder()
    	->setAction($this->generateUrl('reporte_rue_uno'))
    	->add('localizacion', 'hidden', array('data' => 'localizacionDepartamento'))
    	->add('departamento', 'choice', array('label' => 'Departamento', 'required' => true,'choices'=>$depArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'jupper')))
		->add('institucioneducativaDependencia', 'choice', array('label' => 'Dependencia', 'disabled' => false,'choices' => $dependenciasArray,'empty_value' => 'Seleccionar estado...', 'attr' => array('class' => 'form-control')))
		->add('institucioneducativaEstado', 'choice', array('label' => 'Estado', 'disabled' => false,'choices' => $estadosArray,'empty_value' => 'Seleccionar estado...', 'attr' => array('class' => 'form-control')))
		->add('institucioneducativaTipo', 'choice', array('label' => 'Tipo', 'disabled' => false,'choices' => $tiposArray,'empty_value' => 'Seleccionar tipo...', 'attr' => array('class' => 'form-control')))
		->add('reporteuno', 'submit', array('label' => 'Descargar .XLS'));
		
		$form2 = $this->createFormBuilder()
    	->setAction($this->generateUrl('reporte_rue_dos'))
    	->add('localizacion', 'hidden', array('data' => 'localizacionDistrito'))
    	->add('departamento', 'choice', array('label' => 'Departamento', 'required' => true,'choices'=>$depArray ,'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'jupper')))
		->add('distrito', 'choice', array('label' => 'Distrito', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'jupper')))
    	->add('reportedos', 'submit', array('label' => 'Descargar .XLS'));
		
		return $this->render('SieRueBundle:Reportes:search.html.twig', array(
			'form' => $form->getForm()->createView(), 
			'form2' => $form2->getForm()->createView(), 
		));
	}
    
    public function rptUnoAction(Request $request) {
		$form = $request->get('form');
		
        $arch = 'REPORTE_DEPARTAMENTO_' . date('YmdHis') . '.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
		$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rue_lst1_institucioneducativa_v1_afv.rptdesign&id_departamento='.$form['departamento'].'&dependencia='.$form['institucioneducativaDependencia'].'&estado='.$form['institucioneducativaEstado'].'&tipo='.$form['institucioneducativaTipo'].'&&__format=xls&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
	}

	public function rptDosAction(Request $request) {
		$form = $request->get('form');
		
        $arch = 'REPORTE_DISTRITO_' . date('YmdHis') . '.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
		$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rue_lst_por_distrito_institucioneducativa_v1_afv.rptdesign&id_distrito='.$form['distrito'].'&&__format=xls&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
	}
	
	public function repinfoGralAction(Request $request) {
		$form = $request->get('form');

        $arch = 'REPORTE_INFOGRAL_'.$form['cod_rue'].'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rue_infoGral_porCodRue_v1_afv.rptdesign&cod_rue='.$form['cod_rue'].'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
}
