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
        $arch = 'CARATULA_CEA_PERMANENTE_'.$this->session->get('ie_id').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'per_car_hoja1_caratula_v1_ma.rptdesign&__format=pdf&gestion_id='.$this->session->get('ie_gestion').'&cod_ue='.$this->session->get('ie_id').'&periodo='.$this->session->get('ie_per_cod').'&sucursal='.$this->session->get('ie_subcea').'&&__format=pdf&'));
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
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'per_car_hoja4_administrativo_v1_ma.rptdesign&__format=pdf&gestion='.$this->session->get('ie_gestion').'&codigo_ue='.$this->session->get('ie_id').'&periodo='.$this->session->get('ie_per_cod').'&sucursal='.$this->session->get('ie_subcea').'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    
    public function siemaestrosAction() {
        $arch = 'CARATULA_CEA_FACILITADORES_'.$this->session->get('ie_id').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'per_lst_facilitadores_cursos_v1_ma.rptdesign&__format=pdf&Gestion='.$this->session->get('ie_gestion').'&Sie='.$this->session->get('ie_id').'&Subcea='.$this->session->get('ie_subcea').'&&__format=pdf&'));
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

    public function reportParticipantesCLAction(Request $request){

        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        $idcurso=$aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
   //     dump($idcurso);die;
        $sie = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $periodo = $this->session->get('ie_per_cod');
        $suc=$this->session->get('ie_subcea');
        $argum= 'REPORTE PARTICIPANTES CURSOS LARGOS';
        $response = new Response();

        $response->headers->set('Content-type', 'application/pdf');

        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'_'. $periodo . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'per_lst_participantes_curso_largo_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $suc. '&Idcurso=' . $idcurso. '&&__format=pdf&'));

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }

    public function reportCentralizadorCLAction(Request $request){

        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        $idcurso=$aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
        //     dump($idcurso);die;
        $sie = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $periodo = $this->session->get('ie_per_cod');
        $suc=$this->session->get('ie_subcea');
        $argum= 'REPORTE CENTRALIZADOR CURSOS LARGOS';
        $response = new Response();

        $response->headers->set('Content-type', 'application/pdf');

        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'_'. $periodo . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'per_lst_centralizador_curso_largo_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $suc. '&Idcurso=' . $idcurso. '&&__format=pdf&'));

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }

    public function reportCertParticipantesAction(Request $request){
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = array();// unserialize($infoUe);
        $idcurso= $request->get('infoUe'); //$aInfoUeducativa['ueducativaInfo']['ueducativaInfoId']['iecid'];
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





    public function reportCursosCortosFechaAction(Request $request){

        $fechainicio = $request->get('fechaInicio');
        $fechafin = $request->get('fechaFin');
       // dump($fechainicio);dump($fechafin);die;
        $fechainicio = new \DateTime(date($fechainicio));
        $fechafin = new \DateTime(date($fechafin));
        $fechainicio = date_format($fechainicio,'Y-m-d');
        $fechafin = date_format($fechafin,'Y-m-d');

      //  dump($fechainicio);dump($fechafin);die;

        $sie = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $periodo = $this->session->get('ie_per_cod');
        $suc=$this->session->get('ie_subcea');

        $argum= 'REPORTE DE CURSOS CORTOS POR FECHAS';
        $response = new Response();

        $response->headers->set('Content-type', 'application/pdf');

        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'_'. $sie . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'per_lst_cursos_cortos_por_fechas_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion . '&Subcea=' . $suc.  '&fechaini=' . $fechainicio. '&fechafin=' . $fechafin.'&&__format=pdf&'));

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
        $codrude = $request->get('rude');
        $inscripcionId = $request->get('inscripcionId');
        $rude = $em->getRepository('SieAppWebBundle:Rude')->findOneBy(array('estudianteInscripcion'=>$inscripcionId));

        $db = $em->getConnection();
        
        //dump($codrude,$sucursalId,$inscripcionId);die;
        //si el estudiante no es inmigrante
        //if($rude->getMunicipioLugarTipo() != null and $rude->getMunicipioLugarTipo() != 0){
        //dump($rude->getLocalidadLugarTipo());die;
        if($rude->getLocalidadLugarTipo() != null and $rude->getLocalidadLugarTipo()->getId() != 0){
                $lt5_id = $rude->getLocalidadLugarTipo()->getLugarTipo();
                //$lt5_id = $rude->getMunicipioLugarTipo()->getLugarTipo();
                $lt4_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt5_id)->getLugarTipo();
                $lt3_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt4_id)->getLugarTipo();
                $lt2_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt3_id)->getLugarTipo();
                $lt1_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt2_id)->getLugarTipo();

                //$m_id = $rude->getMunicipioLugarTipo()->getId();
                $l_id = $rude->getLocalidadLugarTipo()->getId();
                $c_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt5_id)->getId();
                $m_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt4_id)->getId();
                $p_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt3_id)->getId();
                $d_id = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lt2_id)->getId();
        }else{
            $l_id = 0;
            $c_id = 0;
            $m_id = 0;
            $p_id = 0;
            $d_id = 0;
        }

        /*----------  OBTENEMOS EL PROGRAMA DEL CURSO  ----------*/
        $datosCurso = $em->createQueryBuilder()
                        ->select('cct.cursocorto, ppt.programa, cct.id as idCursocorto, ppt.id as idPrograma')
                        ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                        ->innerJoin('SieAppWebBundle:PermanenteInstitucioneducativaCursocorto','piecc','with','piecc.institucioneducativaCurso = iec.id')
                        // ->innerJoin('SieAppWebBundle:PermanenteAreaTematicaTipo','att','with','piecc.areatematicaTipo = att.id')
                        ->innerJoin('SieAppWebBundle:PermanenteCursocortoTipo','cct','with','piecc.cursocortoTipo = cct.id')
                        ->innerJoin('SieAppWebBundle:PermanenteProgramaTipo','ppt','with','piecc.programaTipo = ppt.id')
                        // ->innerJoin('SieAppWebBundle:PermanenteSubAreaTipo','sat','with','piecc.subAreaTipo = sat.id')
                        ->where('ei.id = :idInscripcion')
                        ->setParameter('idInscripcion', $inscripcionId)
                        ->getQuery()
                        ->getResult();

        // DECLARAMOS LAS VARIABLES QUE SE ENVIARAN AL REPORTE
        $facilitadorComunitario = '';
        $educacionProductiva = '';
        $otros = '';
        $curso = '';

        // VERIFICAMOS SI SE ENCONTRARON LOS DATOS DEL CURSO
        if (count($datosCurso) > 0) {

            switch ($datosCurso[0]['idPrograma']) {
                case 1: $facilitadorComunitario = 'X'; break;
                case 2: $educacionProductiva = 'X'; break;
                default: $otros = 'X'; break;
            }

            // VERIFICAMOS SI SE TRATA DE UN CURSO CORTO
            if ($datosCurso[0]['idCursocorto'] != 99) {
                // ASIGNAMOS EL NOMBRE DEL CURSO CORTO
                $curso = $datosCurso[0]['cursocorto'];
            }else{
                // SI ES UN CURSO LARGO OBTENEMOS EL NOMBRE DE LA ESPECIALIDAD
                $cursoLargo = $em->createQueryBuilder()
                                ->select('set.especialidad')
                                ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                                ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaPeriodo','sip','with','iec.superiorInstitucioneducativa_periodo = sip.id')
                                ->innerJoin('SieAppWebBundle:SuperiorInstitucioneducativaAcreditacion','sia','with','sip.superiorInstitucioneducativaAcreditacion = sia.id')
                                ->innerJoin('SieAppWebBundle:SuperiorAcreditacionEspecialidad','sae','with','sia.acreditacionEspecialidad = sae.id')
                                ->innerJoin('SieAppWebBundle:SuperiorEspecialidadTipo','set','with','sae.superiorEspecialidadTipo = set.id')
                                ->where('ei.id = :idInscripcion')
                                ->setParameter('idInscripcion', $inscripcionId)
                                ->getQuery()
                                ->getResult();

                if (count($cursoLargo)>0) {
                    // ASIGNAMOS EL NOMBRE DE LA ESPECIALIDAD AL CURSO
                    $curso = $cursoLargo[0]['especialidad'];
                }
            }
        }
        // dump($codrude);
        // dump($sucursalId);
        // dump($inscripcionId);
        // dump($d_id);
        // dump($p_id);
        // dump($m_id);
        // dump($l_id);
        // die;

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'rudeal_' . $codrude . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'per_rude_socioeconomico_gral_v1_jqc.rptdesign&rude=' . $codrude . '&sucursalId=' . $sucursalId . '&inscripcionId=' . $inscripcionId . '&dirDep=' . $d_id . '&dirProv=' . $p_id . '&dirSec=' . $m_id. '&dirLoc=' . $l_id . '&fc=' . $facilitadorComunitario .'&ep=' . $educacionProductiva . '&otros=' . $otros . '&curso=' . $curso .'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

}
