<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

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
    
    public function mallatecnicaAction() {
        $arch = 'MALLA_TECNICA_'.$this->session->get('ie_id').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_sie100_hoja2_mallatecnica_v1_hcq.rptdesign&__format=pdf&Institucioneducativa_id='.$this->session->get('ie_id').'&gestion_tipo_id='.$this->session->get('ie_gestion').'&Institucioneducativa_sucursal_id='.$this->session->get('ie_subcea').'&Periodo_tipo_id='.$this->session->get('ie_per_cod').'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    
    public function observacionesoperativoAction() {
        $arch = 'LISTA_OBSERVACIONES'.$this->session->get('ie_id').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_InconsistenciasOperativoInicio_gral_v1_vcj.rptdesign&__format=pdf&ue='.$this->session->get('ie_id').'&gestion='.$this->session->get('ie_gestion').'&subcea='.$this->session->get('ie_subcea').'&periodo='.$this->session->get('ie_per_cod').'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    
    public function observacionesoperativoinicioAction() {
        $arch = 'LISTA_OBSERVACIONES'.$this->session->get('ie_id').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_InconsistenciasOperativoInicioSinSE_gral_v1_vcj.rptdesign&__format=pdf&ue='.$this->session->get('ie_id').'&gestion='.$this->session->get('ie_gestion').'&subcea='.$this->session->get('ie_subcea').'&periodo='.$this->session->get('ie_per_cod').'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    
    public function centralizadoresAction(Request $request) {        
        $infoUe = $request->get('dat');
        $aInfoUeducativa = unserialize($infoUe);
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $ciclo = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
        $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
        $paralelotxt = $aInfoUeducativa['ueducativaInfo']['paralelo'];


            /*print_r($this->session->get('ie_id'));
            print_r(' Gesion:'.$this->session->get('ie_gestion'));
            print_r(' Estado:'.$this->session->get('ie_per_estado'));
            print_r(' Nivel:'.$nivel);
            print_r(' Ciclo:'.$ciclo);
            print_r(' Grado:'.$grado); 
            print_r(' Paralelo:'.$paralelo);            
            print_r(' Paralelotxt:'.$paralelotxt);            
            print_r(' Turno:'.$turno); 
            print_r(' Subcentro:'.$this->session->get('ie_subcea'));
            print_r(' Periodo:'.$this->session->get('ie_per_cod'));
            die;*/

        
        if ($nivel == '15'){//CENTRALIZADOR HUMANISTICA
            if (($this->session->get('ie_gestion') == '2016') || ($this->session->get('ie_gestion') == '2017')) {
                if ($this->session->get('ie_per_estado') == '3'){
                    //NO VALIDOS
                    $ciclotxt = $aInfoUeducativa['ueducativaInfo']['ciclo'];
                    $ciclotxt = substr($ciclotxt, 11, 7);
                    $arch = $this->session->get('ie_id').'_'.$ciclotxt.'_'.$grado.'_'.$paralelotxt.'_'.date('Ymd') . '.pdf';
                    $response = new Response();
                    $response->headers->set('Content-type', 'application/pdf');
                    $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
                    $link = $this->container->getParameter('urlreportweb') . 'alt_lst_EstudiantesBoletinCentralizdor_2016_no_valido_v1_hcq.rptdesign&__format=pdf&institucioneducativa_id='.$this->session->get('ie_id').'&gestion_tipo_id='.$this->session->get('ie_gestion').'&sucursal_tipo_id='.$this->session->get('ie_subcea').'&periodo_tipo_id='.$this->session->get('ie_per_cod').'&nivel_id='.$nivel.'&ciclo_id='.$ciclo.'&grado_id='.$grado.'&turno_tipo_id='.$turno.'&paralelo_tipo_id='.$paralelo.'&&__format=pdf&';
                    $response->setContent(file_get_contents($link));                    
                    $response->setStatusCode(200);
                    $response->headers->set('Content-Transfer-Encoding', 'binary');
                    $response->headers->set('Pragma', 'no-cache');
                    $response->headers->set('Expires', '0');
                }
                else{
                    //VALIDOS
                    $ciclotxt = $aInfoUeducativa['ueducativaInfo']['ciclo'];
                    $ciclotxt = substr($ciclotxt, 11, 7);
                    $arch = $this->session->get('ie_id').'_'.$ciclotxt.'_'.$grado.'_'.$paralelotxt.'_'.date('Ymd') . '.pdf';
                    $response = new Response();
                    $response->headers->set('Content-type', 'application/pdf');
                    $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_EstudiantesBoletinCentralizdor_2016_valido_v1_hcq.rptdesign&__format=pdf&institucioneducativa_id='.$this->session->get('ie_id').'&gestion_tipo_id='.$this->session->get('ie_gestion').'&sucursal_tipo_id='.$this->session->get('ie_subcea').'&periodo_tipo_id='.$this->session->get('ie_per_cod').'&nivel_id='.$nivel.'&ciclo_id='.$ciclo.'&grado_id='.$grado.'&turno_tipo_id='.$turno.'&paralelo_tipo_id='.$paralelo.'&&__format=pdf&'));
                    $response->setStatusCode(200);
                    $response->headers->set('Content-Transfer-Encoding', 'binary');
                    $response->headers->set('Pragma', 'no-cache');
                    $response->headers->set('Expires', '0');
                }
            }
            else{
                //GESTIONES PASADAS HUMANISTICA      
                $ciclotxt = $aInfoUeducativa['ueducativaInfo']['ciclo'];
                $ciclotxt = substr($ciclotxt, 11, 7);
                $arch = $this->session->get('ie_id').'_'.$ciclotxt.'_'.$grado.'_'.$paralelotxt.'_'.date('Ymd') . '.pdf';
                $response = new Response();
                $response->headers->set('Content-type', 'application/pdf');
                $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_EstudiantesBoletinCentralizdor_v1_hcq.rptdesign&__format=pdf&institucioneducativa_id='.$this->session->get('ie_id').'&gestion_tipo_id='.$this->session->get('ie_gestion').'&sucursal_tipo_id='.$this->session->get('ie_subcea').'&periodo_tipo_id='.$this->session->get('ie_per_cod').'&nivel_id='.$nivel.'&ciclo_id='.$ciclo.'&grado_id='.$grado.'&turno_tipo_id='.$turno.'&paralelo_tipo_id='.$paralelo.'&&__format=pdf&'));
                $response->setStatusCode(200);
                $response->headers->set('Content-Transfer-Encoding', 'binary');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');
            }    
        }
        else{//CENTRALIZADOR TECNICA
            if (($this->session->get('ie_gestion') == '2016') || ($this->session->get('ie_gestion') == '2017')) {
                if ($this->session->get('ie_per_estado') == '3'){
                    //NO VALIDOS
                    $ciclotxt = $aInfoUeducativa['ueducativaInfo']['ciclo'];
                    $ciclotxt = substr($ciclotxt, 0, 6);
                    $gradotxt = $aInfoUeducativa['ueducativaInfo']['grado'];
                    $gradotxt = substr($gradotxt, 9, 4);
                    $arch = $this->session->get('ie_id').'_'.$ciclotxt.'_'.$gradotxt.'_'.$paralelotxt.'_'.date('Ymd') . '.pdf';
                    $response = new Response();
                    $response->headers->set('Content-type', 'application/pdf');
                    $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_EstudiantesBoletinCentralizdor_tecnica_2016_no_valido_v1_hcq.rptdesign&__format=pdf&institucioneducativa_id='.$this->session->get('ie_id').'&gestion_tipo_id='.$this->session->get('ie_gestion').'&sucursal_tipo_id='.$this->session->get('ie_subcea').'&periodo_tipo_id='.$this->session->get('ie_per_cod').'&nivel_id='.$nivel.'&ciclo_id='.$ciclo.'&grado_id='.$grado.'&turno_tipo_id='.$turno.'&paralelo_tipo_id='.$paralelo.'&&__format=pdf&'));
                    $response->setStatusCode(200);
                    $response->headers->set('Content-Transfer-Encoding', 'binary');
                    $response->headers->set('Pragma', 'no-cache');
                    $response->headers->set('Expires', '0');
                }
                else{
                    //VALIDOS
                    $ciclotxt = $aInfoUeducativa['ueducativaInfo']['ciclo'];
                    $ciclotxt = substr($ciclotxt, 0, 6);
                    $gradotxt = $aInfoUeducativa['ueducativaInfo']['grado'];
                    $gradotxt = substr($gradotxt, 9, 4);
                    $arch = $this->session->get('ie_id').'_'.$ciclotxt.'_'.$gradotxt.'_'.$paralelotxt.'_'.date('Ymd') . '.pdf';
                    $response = new Response();
                    $response->headers->set('Content-type', 'application/pdf');
                    $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));                    
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_EstudiantesBoletinCentralizdor_tecnica_2016_valido_v1_hcq.rptdesign&__format=pdf&institucioneducativa_id='.$this->session->get('ie_id').'&gestion_tipo_id='.$this->session->get('ie_gestion').'&sucursal_tipo_id='.$this->session->get('ie_subcea').'&periodo_tipo_id='.$this->session->get('ie_per_cod').'&nivel_id='.$nivel.'&ciclo_id='.$ciclo.'&grado_id='.$grado.'&turno_tipo_id='.$turno.'&paralelo_tipo_id='.$paralelo.'&&__format=pdf&'));
                    $response->setStatusCode(200);
                    $response->headers->set('Content-Transfer-Encoding', 'binary');
                    $response->headers->set('Pragma', 'no-cache');
                    $response->headers->set('Expires', '0');
                }
            }
            else{
                //GESTIONES PASADAS TECNICA
                $ciclotxt = $aInfoUeducativa['ueducativaInfo']['ciclo'];
                $ciclotxt = substr($ciclotxt, 0, 6);
                $gradotxt = $aInfoUeducativa['ueducativaInfo']['grado'];
                $gradotxt = substr($gradotxt, 9, 4);
                $arch = $this->session->get('ie_id').'_'.$ciclotxt.'_'.$gradotxt.'_'.$paralelotxt.'_'.date('Ymd') . '.pdf';
                $response = new Response();
                $response->headers->set('Content-type', 'application/pdf');
                $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_EstudiantesBoletinCentralizdor_tecnica_v1_hcq.rptdesign&__format=pdf&institucioneducativa_id='.$this->session->get('ie_id').'&gestion_tipo_id='.$this->session->get('ie_gestion').'&sucursal_tipo_id='.$this->session->get('ie_subcea').'&periodo_tipo_id='.$this->session->get('ie_per_cod').'&nivel_id='.$nivel.'&ciclo_id='.$ciclo.'&grado_id='.$grado.'&turno_tipo_id='.$turno.'&paralelo_tipo_id='.$paralelo.'&&__format=pdf&'));
                $response->setStatusCode(200);
                $response->headers->set('Content-Transfer-Encoding', 'binary');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');
            }
        }
        return $response;
    }    
    
    public function siealtlibretasAction($eInsId, $nivel) {
        $em = $this->getDoctrine()->getManager();
        $insobj = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eInsId);        
        $estobjid = $insobj->getEstudiante()->getId();       
        $est = $em->getRepository('SieAppWebBundle:Estudiante')->findById($estobjid);
        
//        dump($est[0]->getCodigoRude());
//        die;
        
        if ($nivel == '15'){
            $arch = $this->session->get('ie_id').'_'.$est[0]->getCodigoRude().'_'.date('Ymd') . '.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));            
            $link = $this->container->getParameter('urlreportweb') . 'alt_est_LibretaElectronicaHumanistica2016_v1_hcq.rptdesign&__format=pdf&estudiante_inscripcion_id='.$eInsId.'&&__format=pdf&';
//            dump($link);
//            die;
            $response->setContent(file_get_contents($link));            
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');                
        }
        else{
            
            $arch = $this->session->get('ie_id').'_'.$est[0]->getCodigoRude().'_'.date('Ymd') . '.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $link = $this->container->getParameter('urlreportweb') . 'alt_est_LibretaElectronicaTecnica2016_v3_vcjm.rptdesign&__format=pdf&estudiante_inscripcion_id='.$eInsId.'&&__format=pdf&';
//            dump($link);
//            die;
            $response->setContent(file_get_contents($link));            
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0'); 
            
        }
        return $response;
    }
    
    public function ddjjStudentWebHumAction(Request $request) {

      //get the data send to the report
      $rude = $request->get('rude');
      $gestion = $request->get('gestion');
      $sie = $request->get('sie');
        //get the values of report
        //create the response object to down load the file
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'ddjj_' . $rude . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_dj_DeclaracionJurada_Estudiante_gral_v1.rptdesign&rude=' . $rude . '&gestion=' . $gestion . '&sie=' . $sie . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    
    public function ddjjtecAction(Request $request) {
      //get the data send to the report
      $rude = $request->get('rude');
      $gestion = $request->get('gestion');
      $insId = $request->get('insId');
//      dump($insId);
//      die('g');
        //get the values of report
        //create the response object to down load the file
        //$response = new Response();
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'ddjj_' . $rude . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_dj_DeclaracionJurada_Estudiante_alt_tec_gral_v1_pmm.rptdesign&Idinscripcion='.$insId.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    
    public function ddjjhumAction(Request $request) {

      //get the data send to the report
      $rude = $request->get('rude');
      $gestion = $request->get('gestion');
      $sie = $request->get('sie');
        //get the values of report
        //create the response object to down load the file
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'ddjj_' . $rude . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_dj_DeclaracionJurada_Estudiante_alt_hum_gral_v1_pmm.rptdesign&rude=' . $rude . '&gestion=' . $gestion . '&sie=' . $sie . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    
    
    public function socioeconomicoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        //get the data send to the report           
        $gestion = $this->session->get('gestion');
        $sucursalId = $this->session->get('ie_suc_id');
        $rude = $request->get('rude');
        $inscripcionId = $request->get('inscripcionId');
        $socioalteIdEntity = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAlternativa')->findByEstudianteInscripcion($inscripcionId);
        $socioalteId = $socioalteIdEntity[0]->getId();

        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();    
        
        $idlocalidad = $socioalteIdEntity[0]->getSeccioniiiLocalidadTipo()->getId();
        //dump($idlocalidad);die;
        
        $query = "select socioeconomico_lugar_recursivo(".$idlocalidad.");";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);        
        $po = $stmt->fetchAll();
        //$countdir = count($po);
        
        $porciones = explode("|", $po[0]['socioeconomico_lugar_recursivo']);
        //dump($porciones);die;
        
        $dirDep = $porciones[2];        
        $dirProv = $porciones[3];
        $dirSec = $porciones[4];
        $dirLoc = $porciones[6];
//        //get the values of report
//        //create the response object to down load the file
        
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'rudelal_' . $rude . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_rude_socioeconomico_gral_v1_vcj.rptdesign&socioalteId=' . $socioalteId . '&rude=' . $rude . '&sucursalId=' . $sucursalId . '&inscripcionId=' . $inscripcionId . '&dirDep=' . $dirDep . '&dirProv=' . $dirProv . '&dirSec=' . $dirSec . '&dirLoc=' . $dirLoc . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

}
