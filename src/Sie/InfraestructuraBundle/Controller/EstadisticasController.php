<?php

namespace Sie\InfraestructuraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
use Symfony\Component\HttpFoundation\Response;


class EstadisticasController extends Controller
{
    private $session;
    
    public function __construct() {        
        $this->session = new Session();        
    }
    
    public function indexAction(){
        $id_usuario = $this->session->get('userId');
       
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieInfraestructuraBundle:Default:index.html.twig');
    }
    
    public function deptoestadsiticasAction(){
        $id_usuario = $this->session->get('userId');
       
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieInfraestructuraBundle:Default:index.html.twig');
    }
 
  public function infraestructuraIndexAction(){
        $id_usuario = $this->session->get('userId');
       
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieInfraestructuraBundle:Default:index.html.twig');
    }




    public function reportesGerencialesPdfAction(Request $request) {
     
       // dump($request);die;
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
            $periodo= $request->get('periodo');
           // $periodo = 2;
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo= 2;
        }

        $em = $this->getDoctrine()->getManager();
        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_reporte_gerencial_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function reportesAmbientesPdfAction(Request $request) {
     
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
       
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_ambientes_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_ambientes_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_ambientes_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_ambientes_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function reportesAmbientesXlsAction(Request $request) {
         date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
       
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_ambientes_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_ambientes_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_ambientes_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_ambientes_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    // reportes generales completos

    public function reportesAmbientesPedagogicosPdfAction(Request $request) {
         date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
       
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_pedagogicos_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_pedagogicos_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_pedagogicos_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_pedagogicos_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function reportesAmbientesPedagogicosXlsAction(Request $request) {
         date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
       
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_pedagogicos_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_pedagogicos_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_pedagogicos_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_pedagogicos_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function reportesAmbientesNoPedagogicosPdfAction(Request $request) {
          date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
       
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_no_pedagogicos_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_no_pedagogicos_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_no_pedagogicos_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_no_pedagogicos_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function reportesAmbientesNoPedagogicosXlsAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
       
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_no_pedagogicos_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_no_pedagogicos_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_no_pedagogicos_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_no_pedagogicos_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }



    public function reportesAmbientesOtrosPdfAction(Request $request) {
         date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
       
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_otros_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_otros_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_otros_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_otros_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

 
    public function reportesAmbientesOtrosXlsAction(Request $request) {
         date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
       
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_otros_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_otros_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_otros_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_otros_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function reportesAmbientesPedagogicosRecreativosPdfAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
       
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_pedagogicos_recreativos_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_pedagogicos_recreativos_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_pedagogicos_recreativos_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_pedagogicos_recreativos_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

 
    public function reportesAmbientesPedagogicosRecreativosXlsAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
       
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_pedagogicos_recreativos_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_pedagogicos_recreativos_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_pedagogicos_recreativos_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_ambientes_pedagogicos_recreativos_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }



    public function  reportesServiciosPdfAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
       
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_servicios_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_servicios_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_servicios_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_servicios_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

  
    public function reportesServiciosXlsAction(Request $request) {
       date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
       
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_servicios_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_servicios_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_servicios_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_servicios_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function  reportesEventosPdfAction(Request $request) {
         date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
       
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_eventos_riesgos_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_eventos_riesgos_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_eventos_riesgos_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_eventos_riesgos_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

  
    public function reportesEventosXlsAction(Request $request) {
         date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
       
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_eventos_riesgos_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_eventos_riesgos_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_eventos_riesgos_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_eventos_riesgos_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

 public function  reportesRiesgosPdfAction(Request $request) {
         date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
       
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_riesgo_proximo_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_riesgo_proximo_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_riesgo_proximo_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_riesgo_proximo_nacional_v1_ma.rptdesign&__format=pdf&Gestion='.$gestion));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

public function reportesRiesgosXlsAction(Request $request) {
         date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        if ($request->isMethod('POST')) {
       
            $gestion = $request->get('gestion');
            $codigoArea = base64_decode($request->get('codigo'));
            $rol = $request->get('rol');
            $periodo= $request->get('periodo');
        } else {
            $gestion = $gestionActual;
            $codigoArea = 0;
            $rol = 0;
            $periodo = 2;
        }

        $em = $this->getDoctrine()->getManager();

        $arch = 'MinEdu_'.$codigoArea.'_'.$gestion.'_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        // por defecto
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_riesgo_proximo_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));

        if($rol == 9 or $rol == 5) // Director o Administrativo
        {
        }

        if($rol == 10 or $rol == 11) // Distrital o Tecnico Distrito
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_riesgo_proximo_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        if($rol == 7) // Tecnico Departamental
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_riesgo_proximo_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        if($rol == 8 or $rol == 20) // Tecnico Nacional
        {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'inf_est_riesgo_proximo_nacional_v1_ma.rptdesign&__format=xls&Gestion='.$gestion));
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }









}