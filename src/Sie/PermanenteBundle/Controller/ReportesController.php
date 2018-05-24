<?php

namespace Sie\PermanenteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

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
    
    public function siecaratulaAction() {
        $arch = 'CARATULA_CEA_SIE100_'.$this->session->get('ie_id').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_sie100_hoja1_caratula_v1_rcm.rptdesign&__format=pdf&gestion_id='.$this->session->get('ie_gestion').'&cod_ue='.$this->session->get('ie_id').'&periodo='.$this->session->get('ie_per_cod').'&sucursal='.$this->session->get('ie_subcea').'&&__format=pdf&'));        
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    
    public function sieadministrativosAction() {
        $arch = 'CARATULA_CEA_ADMINISTRATIVOS_'.$this->session->get('ie_id').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_sie100_hoja4_administrativo_v1_rcm.rptdesign&__format=pdf&gestion='.$this->session->get('ie_gestion').'&codigo_ue='.$this->session->get('ie_id').'&periodo='.$this->session->get('ie_per_cod').'&sucursal='.$this->session->get('ie_subcea').'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    
    public function siemaestrosAction() {
        $arch = 'CARATULA_CEA_DOCENTES_'.$this->session->get('ie_id').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_sie100_hoja4_maestro_v1_rcm.rptdesign&__format=pdf&gestion='.$this->session->get('ie_gestion').'&codigo_ue='.$this->session->get('ie_id').'&periodo='.$this->session->get('ie_per_cod').'&sucursal='.$this->session->get('ie_subcea').'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    
    public function sieparticipantesAction() {
        $arch = 'CARATULA_CEA_PARTICIPANTES_'.$this->session->get('ie_id').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_Estudiantes_v2_vcjm.rptdesign&__format=pdf&gestion_tipo_id='.$this->session->get('ie_gestion').'&institucioneducativa_id='.$this->session->get('ie_id').'&periodo_tipo_id='.$this->session->get('ie_per_cod').'&sucursal_tipo_id='.$this->session->get('ie_subcea').'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }    
    
    public function reportParticipantesAction(Request $request){
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        $idcurso=$aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
        $sie = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $periodo = $this->session->get('ie_per_cod');
        $suc=$this->session->get('ie_subcea');
        $argum= 'REPORTE PARTICIPANTES CURSOS CORTOS';
        $response = new Response();

        $response->headers->set('Content-type', 'application/pdf');

        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'_'. $periodo . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'per_lst_participantes_por_curso_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $suc. '&Idcurso=' . $idcurso. '&&__format=pdf&'));

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }

    public function reportCertParticipantesAction(Request $request){
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        $idcurso=$aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
        $sie = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $periodo = $this->session->get('ie_per_cod');
        $suc=$this->session->get('ie_subcea');
        $argum= 'CERTIFICADOS PARTICIPANTES CURSOS CORTOS';
        $response = new Response();

        $response->headers->set('Content-type', 'application/pdf');

        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'_'. $periodo . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'per_cert_participantes_por_curso_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $suc. '&Idcurso=' . $idcurso. '&&__format=pdf&'));

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }



}
