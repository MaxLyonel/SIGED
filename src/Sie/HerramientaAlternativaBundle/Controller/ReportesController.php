<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;

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
        if((int)$this->session->get('ie_subcea')>0){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_sie100_hoja1_caratula_subcea_v1_rcm.rptdesign&__format=pdf&gestion_id='.$this->session->get('ie_gestion').'&cod_ue='.$this->session->get('ie_id').'&periodo='.$this->session->get('ie_per_cod').'&sucursal='.$this->session->get('ie_subcea').'&&__format=pdf&'));        
        } else {
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_sie100_hoja1_caratula_v1_rcm.rptdesign&__format=pdf&gestion_id='.$this->session->get('ie_gestion').'&cod_ue='.$this->session->get('ie_id').'&periodo='.$this->session->get('ie_per_cod').'&sucursal='.$this->session->get('ie_subcea').'&&__format=pdf&'));        
        }
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
        $gestion= $this->session->get('ie_gestion');
        if($gestion>=2019){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_sie100_hoja4_maestro_v2_ma.rptdesign&__format=pdf&gestion='.$this->session->get('ie_gestion').'&codigo_ue='.$this->session->get('ie_id').'&periodo='.$this->session->get('ie_per_cod').'&sucursal='.$this->session->get('ie_subcea').'&&__format=pdf&'));
        }else{
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_sie100_hoja4_maestro_v1_rcm.rptdesign&__format=pdf&gestion='.$this->session->get('ie_gestion').'&codigo_ue='.$this->session->get('ie_id').'&periodo='.$this->session->get('ie_per_cod').'&sucursal='.$this->session->get('ie_subcea').'&&__format=pdf&'));
        }
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
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_Estudiantes_v3_vcjm.rptdesign&__format=pdf&gestion_tipo_id='.$this->session->get('ie_gestion').'&institucioneducativa_id='.$this->session->get('ie_id').'&periodo_tipo_id='.$this->session->get('ie_per_cod').'&sucursal_tipo_id='.$this->session->get('ie_subcea').'&&__format=pdf&'));
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
        //2024
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

    public function cerradoexitosooperativoAction() {
        $em = $this->getDoctrine()->getManager();
        

        $arch = 'ACTA_CIERRE_OPERATIVO'.$this->session->get('ie_id').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        $ies = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($this->session->get('ie_suc_id'));            
        $iest = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursalTramite')->findByInstitucioneducativaSucursal($ies);

        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_cea_Cierre_Operativo_v1_vcjm.rptdesign&__format=pdf&CEA='.$this->session->get('ie_id').'&Gestion='.$this->session->get('ie_gestion').'&SUCURSAL='.$this->session->get('ie_subcea').'&Periodo='.$this->session->get('ie_per_cod').'&operativoId='.$iest[0]->getTramiteEstado()->getId().'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function reportesEstudiantesExcelAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        /*if ($id_usuario == 2){//EN CASO DE QUE USUARIO ES PROFESOR
            $db = $em->getConnection();
            $usuario = $em->getRepository('SieAppWebBundle:Usuario')->findByPersona($this->session->get('userId'));
            $idPersona = $usuario[0]->getPersona()->getId();

            $arch = 'LISTADO_ESTUDIANTES_PROFESOR'.$this->session->get('name').'_'.$this->session->get('lastname').'_'.date('YmdHis') . '.xlsx';
            $response = new Response();
            $response->headers->set('Content-type', 'application/xlsx');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_Estudiantes_Excel_v1_vcjm.rptdesign&__format=xlsx&gestion_tipo_id='.$this->session->get('ie_gestion').'&institucioneducativa_id='.$this->session->get('ie_id').'&sucursal_tipo_id='.$this->session->get('ie_subcea').'&periodo_tipo_id='.$this->session->get('ie_per_cod').'&&__format=xlsx&'));        
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        }else{*/
            $arch = 'LISTADO_ESTUDIANTES_CENTRO'.$this->session->get('ie_id').'_' . date('YmdHis') . '.xlsx';
            $response = new Response();
            $response->headers->set('Content-type', 'application/xlsx');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_Estudiantes_Excel_v1_vcjm.rptdesign&__format=xlsx&gestion_tipo_id='.$this->session->get('ie_gestion').'&institucioneducativa_id='.$this->session->get('ie_id').'&sucursal_tipo_id='.$this->session->get('ie_subcea').'&periodo_tipo_id='.$this->session->get('ie_per_cod').'&&__format=xlsx&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        //}        
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

        $em = $this->getDoctrine()->getManager();

        $infoUe = $request->get('dat');
        $aInfoUeducativa = unserialize($infoUe);
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $ciclo = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
        $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
        $paralelotxt = $aInfoUeducativa['ueducativaInfo']['paralelo'];
        
        $idCurso = $aInfoUeducativa['ueducativaInfoId']['iecId'];


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

            //81710077 Gesion:2024 Estado:2 Nivel:15 Ciclo:2 Grado:1 Paralelo:1 Paralelotxt:A Turno:4 Subcentro:7 Periodo:3
            // tecnica
            //60480060 Gesion:2024 Estado:2 Nivel:22 Ciclo:20 Grado:1 Paralelo:1 Paralelotxt:A Turno:4 Subcentro:0 Periodo:3
        
        if ($nivel == '15'){//CENTRALIZADOR HUMANISTICA
            // FUNCION PARA VERIFICAR SI EL CURSO DE PRIMARIA TRABAJA CON LA NUEVA CURRICULA
            // $primariaNuevo = $this->get('funciones')->validatePrimariaCourse($idCurso);
            $primariaNuevo = $this->get('funciones')->verificarMateriasPrimariaAlternativa($idCurso);

            if($this->session->get('ie_gestion') >= 2016 ){

                        $query = $em->getConnection()->prepare("
                            SELECT
                            institucioneducativa.id, 
                            institucioneducativa_sucursal.id, 
                            institucioneducativa_sucursal.periodo_tipo_id,
                            institucioneducativa_sucursal.gestion_tipo_id, 
                            institucioneducativa_sucursal_tramite.id, 
                            institucioneducativa_sucursal_tramite.institucioneducativa_sucursal_id, 
                            institucioneducativa_sucursal_tramite.periodo_estado_id, 
                            institucioneducativa_sucursal_tramite.tramite_estado_id, 
                            institucioneducativa_sucursal_tramite.tramite_tipo_id
                        FROM
                            institucioneducativa
                            INNER JOIN
                            institucioneducativa_sucursal
                            ON 
                                institucioneducativa.id = institucioneducativa_sucursal.institucioneducativa_id
                            INNER JOIN
                            institucioneducativa_sucursal_tramite
                            ON 
                                institucioneducativa_sucursal.id = institucioneducativa_sucursal_tramite.institucioneducativa_sucursal_id
                                where 
                                institucioneducativa.id = :sie  and gestion_tipo_id = 2024 and  periodo_tipo_id = 3 and tramite_estado_id = 14 
                    ");                
            
                    $query->bindValue(':sie', $this->session->get('ie_id'));
                    $query->execute();
                    $cierre2024 = $query->fetchAll(); 
            
                    $segundosemestre2024cierre = false;
                    if($cierre2024){
                        $segundosemestre2024cierre = true;
                    }
 
                    if ($this->session->get('ie_per_estado') == '0' or $segundosemestre2024cierre == true) {                
                    //VALIDOS
                    if ($primariaNuevo ) { // VERIFICAMOS SI SE UTILIZARA EL NUEVO REPORTE 2018 
                        $reporte = 'alt_lst_EstudiantesBoletinCentralizador_2018_v1_rcm.rptdesign';
                    } else {
                        $reporte = 'alt_lst_EstudiantesBoletinCentralizdor_2016_valido_v1_hcq.rptdesign';
                    }
                    $ciclotxt = $aInfoUeducativa['ueducativaInfo']['ciclo'];
                    $ciclotxt = substr($ciclotxt, 11, 7);
                    $arch = $this->session->get('ie_id').'_'.$ciclotxt.'_'.$grado.'_'.$paralelotxt.'_'.date('Ymd') . '.pdf';
                    $response = new Response();
                    $response->headers->set('Content-type', 'application/pdf');
                    $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $reporte .'&__format=pdf&institucioneducativa_id='.$this->session->get('ie_id').'&gestion_tipo_id='.$this->session->get('ie_gestion').'&sucursal_tipo_id='.$this->session->get('ie_subcea').'&periodo_tipo_id='.$this->session->get('ie_per_cod').'&nivel_id='.$nivel.'&ciclo_id='.$ciclo.'&grado_id='.$grado.'&turno_tipo_id='.$turno.'&paralelo_tipo_id='.$paralelo.'&&__format=pdf&'));
                    $response->setStatusCode(200);
                    $response->headers->set('Content-Transfer-Encoding', 'binary');
                    $response->headers->set('Pragma', 'no-cache');
                    $response->headers->set('Expires', '0');
                }
                else{
                    //NO VALIDOS
                    if ($primariaNuevo) {
                        $reporte = 'alt_lst_EstudiantesBoletinCentralizador_2018_v1_rcm.rptdesign'; // cambiar con el no valido
                    } else {
                        $reporte = 'alt_lst_EstudiantesBoletinCentralizdor_2016_no_valido_v1_hcq.rptdesign';
                    }

                    $ciclotxt = $aInfoUeducativa['ueducativaInfo']['ciclo'];
                    $ciclotxt = substr($ciclotxt, 11, 7);
                    $arch = $this->session->get('ie_id').'_'.$ciclotxt.'_'.$grado.'_'.$paralelotxt.'_'.date('Ymd') . '.pdf';
                    $response = new Response();
                    $response->headers->set('Content-type', 'application/pdf');
                    $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $reporte .'&__format=pdf&institucioneducativa_id='.$this->session->get('ie_id').'&gestion_tipo_id='.$this->session->get('ie_gestion').'&sucursal_tipo_id='.$this->session->get('ie_subcea').'&periodo_tipo_id='.$this->session->get('ie_per_cod').'&nivel_id='.$nivel.'&ciclo_id='.$ciclo.'&grado_id='.$grado.'&turno_tipo_id='.$turno.'&paralelo_tipo_id='.$paralelo.'&&__format=pdf&'));
                    //$link = $this->container->getParameter('urlreportweb') . 'alt_lst_EstudiantesBoletinCentralizdor_2016_no_valido_v1_hcq.rptdesign&__format=pdf&institucioneducativa_id='.$this->session->get('ie_id').'&gestion_tipo_id='.$this->session->get('ie_gestion').'&sucursal_tipo_id='.$this->session->get('ie_subcea').'&periodo_tipo_id='.$this->session->get('ie_per_cod').'&nivel_id='.$nivel.'&ciclo_id='.$ciclo.'&grado_id='.$grado.'&turno_tipo_id='.$turno.'&paralelo_tipo_id='.$paralelo.'&&__format=pdf&';
                    //$response->setContent(file_get_contents($link));                    
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
            if($this->session->get('ie_gestion') >= 2016 ){

                $query = $em->getConnection()->prepare("
                            SELECT
                            institucioneducativa.id, 
                            institucioneducativa_sucursal.id, 
                            institucioneducativa_sucursal.periodo_tipo_id,
                            institucioneducativa_sucursal.gestion_tipo_id, 
                            institucioneducativa_sucursal_tramite.id, 
                            institucioneducativa_sucursal_tramite.institucioneducativa_sucursal_id, 
                            institucioneducativa_sucursal_tramite.periodo_estado_id, 
                            institucioneducativa_sucursal_tramite.tramite_estado_id, 
                            institucioneducativa_sucursal_tramite.tramite_tipo_id
                        FROM
                            institucioneducativa
                            INNER JOIN
                            institucioneducativa_sucursal
                            ON 
                                institucioneducativa.id = institucioneducativa_sucursal.institucioneducativa_id
                            INNER JOIN
                            institucioneducativa_sucursal_tramite
                            ON 
                                institucioneducativa_sucursal.id = institucioneducativa_sucursal_tramite.institucioneducativa_sucursal_id
                                where 
                                institucioneducativa.id = :sie  and gestion_tipo_id = 2024 and  periodo_tipo_id = 3 and tramite_estado_id = 14 
                    ");                
            
                    $query->bindValue(':sie', $this->session->get('ie_id'));
                    $query->execute();
                    $cierre2024 = $query->fetchAll(); 
            
                    $segundosemestre2024cierre = false;
                    if($cierre2024){
                        $segundosemestre2024cierre = true;
                    }

                if ($this->session->get('ie_per_estado') == '0'  or $segundosemestre2024cierre == true ){
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
                else{
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
    private function getLinkEncript($datos){
      $codes = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz+/';

      // Encriptamos los datos
      $result = "";
      $a = 0;
      $b = 0;
      for($i=0;$i<strlen($datos);$i++){
          //$x = strpos($codes, $datos[$i]) ;
          $x = ord($datos[$i]) ;
          $b = $b * 256 + $x;
          $a = $a + 8;

          while ( $a >= 6) {
              $a = $a - 6;
              $x = floor($b/(1 << $a));
              $b = $b % (1 << $a);
              $result = $result.''.substr($codes, $x,1);
          }
      }
      if($a > 0){
          $x = $b << (6 - $a);
          $result = $result.''.substr($codes, $x,1);
      }
      return $result;
    }   
    
    public function siealtlibretasAction($eInsId, $nivel) {
        $em = $this->getDoctrine()->getManager();
        $insobj = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eInsId);      
        $estobjid = $insobj->getEstudiante()->getId();       
        $est = $em->getRepository('SieAppWebBundle:Estudiante')->findById($estobjid);
        $idCurso = $insobj->getInstitucioneducativaCurso()->getId();

        //get info about the course
        $objUeducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getAlterCursosBySieGestSubPerIecid($this->session->get('ie_id'), $this->session->get('ie_gestion'), $this->session->get('ie_subcea'), $this->session->get('ie_per_cod'), $idCurso);
        
        //get the data to do the report
        $user= $this->session->get('userName');
        $sie = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $ciclo = $objUeducativa[0]['cicloId'];
        $grado = $objUeducativa[0]['gradoId'];
        $paralelo = $objUeducativa[0]['paraleloId'];
        $turno = $objUeducativa[0]['turnoId'];

        $data = $est[0]->getCodigoRude().'|'.$eInsId.'|'.$sie.'|'.$gestion.'|'.(int)$nivel.'|'.$ciclo.'|'.$grado.'|'.(int)$paralelo.'|'.$turno;
        $link = 'http://'.$_SERVER['SERVER_NAME'].'/cen/'.$this->getLinkEncript($data);
       
        if ($nivel == '15'){
            // $primariaNuevo = $this->get('funciones')->validatePrimariaCourse($idCurso);
            $primariaNuevo = $this->get('funciones')->verificarMateriasPrimariaAlternativa($idCurso);
            
            if($primariaNuevo){
                $reporte = 'alt_libreta_electronica_v1_ma.rptdesign';
            }else{
                $reporte = 'alt_est_LibretaElectronicaHumanistica2016_v1_hcq.rptdesign';
            }

            $arch = $this->session->get('ie_id').'_'.$est[0]->getCodigoRude().'_'.date('Ymd') . '.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));  

            $link = $this->container->getParameter('urlreportweb') . $reporte . '&__format=pdf&estudiante_inscripcion_id='.$eInsId. '&lk=' . $link .'&&__format=pdf&';
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
            $link = $this->container->getParameter('urlreportweb') . 'alt_est_LibretaElectronicaTecnica2016_v3_vcjm.rptdesign&__format=pdf&estudiante_inscripcion_id='.$eInsId. '&lk=' . $link .'&&__format=pdf&';
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

    public function printrecordcardAction($eInsId, $nivel,$sie,$gestion,$subcea,$periodo,$iecid) {
        $em = $this->getDoctrine()->getManager();
        $insobj = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eInsId);      
        $estobjid = $insobj->getEstudiante()->getId();       
        $est = $em->getRepository('SieAppWebBundle:Estudiante')->findById($estobjid);
        $idCurso = $insobj->getInstitucioneducativaCurso()->getId();

        //get info about the course
        $objUeducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getAlterCursosBySieGestSubPerIecid($sie, $gestion, $subcea, $periodo, $idCurso);
        
        //get the data to do the report
        $user= $this->session->get('userName');
        $sie = $sie;
        $gestion = $gestion;
        $ciclo = $objUeducativa[0]['cicloId'];
        $grado = $objUeducativa[0]['gradoId'];
        $paralelo = $objUeducativa[0]['paraleloId'];
        $turno = $objUeducativa[0]['turnoId'];

        $data = $est[0]->getCodigoRude().'|'.$eInsId.'|'.$sie.'|'.$gestion.'|'.(int)$nivel.'|'.$ciclo.'|'.$grado.'|'.(int)$paralelo.'|'.$turno;
        $link = 'http://'.$_SERVER['SERVER_NAME'].'/cen/'.$this->getLinkEncript($data);
       
        if ($nivel == '15'){
            // $primariaNuevo = $this->get('funciones')->validatePrimariaCourse($idCurso);
            $primariaNuevo = $this->get('funciones')->verificarMateriasPrimariaAlternativa($idCurso);
            
            if($primariaNuevo){
                $reporte = 'alt_libreta_electronica_v1_ma.rptdesign';
            }else{
                $reporte = 'alt_est_LibretaElectronicaHumanistica2016_v1_hcq.rptdesign';
            }

            $arch = $this->session->get('ie_id').'_'.$est[0]->getCodigoRude().'_'.date('Ymd') . '.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));  

            $link = $this->container->getParameter('urlreportweb') . $reporte . '&__format=pdf&estudiante_inscripcion_id='.$eInsId. '&lk=' . $link .'&&__format=pdf&';
//            dump($link);
//            die;
            $response->setContent(file_get_contents($link));            
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');                
        }
        else{
            /*
            $arch = $this->session->get('ie_id').'_'.$est[0]->getCodigoRude().'_'.date('Ymd') . '.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $link = $this->container->getParameter('urlreportweb') . 'alt_est_LibretaElectronicaTecnica2016_v3_vcjm.rptdesign&__format=pdf&estudiante_inscripcion_id='.$eInsId. '&lk=' . $link .'&&__format=pdf&';
//            dump($link);
//            die;
            $response->setContent(file_get_contents($link));            
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0'); */
            
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
        //dump($codrude,$sucursalId,$inscripcionId,$l_id,$c_id,$m_id,$p_id,$d_id);die;
        // if ($idMunicipio != 0){
        //     $query = "select socioeconomico_lugar_recursivo(".$idMunicipio.");";
        //     $stmt = $db->prepare($query);
        //     $params = array();
        //     $stmt->execute($params);        
        //     $po = $stmt->fetchAll();
        // //dump($po);die;
        // //$countdir = count($po);
        
        //     $porciones = explode("|", $po[0]['socioeconomico_lugar_recursivo']);
        // //dump($porciones);die;
        
        //     $dirDep = $porciones[2];        
        //     $dirProv = $porciones[3];
        //     $dirSec = $porciones[4];
        //     $dirLoc = $porciones[6];
        // }else{
        //     $dirDep = 0;        
        //     $dirProv = 0;
        //     $dirSec = 0;
        //     $dirLoc = 0;
        // }
          //get the values of report
//        //create the response object to down load the file
        

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'rudelal_' . $codrude . '_' . $gestion . '.pdf'));
        // $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_rude_socioeconomico_gral_v2_vcj.rptdesign&socioalteId=' . $socioalteId . '&rude=' . $rude . '&sucursalId=' . $sucursalId . '&inscripcionId=' . $inscripcionId . '&dirDep=' . $dirDep . '&dirProv=' . $dirProv . '&dirSec=' . $dirSec . '&dirLoc=' . $dirLoc . '&&__format=pdf&'));
        //$response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_rude_socioeconomico_gral_v3_afv.rptdesign&rude=' . $codrude . '&sucursalId=' . $sucursalId . '&inscripcionId=' . $inscripcionId . '&dirDep=' . $d_id . '&dirProv=' . $p_id . '&dirSec=' . $m_id . '&&__format=pdf&'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_rude_socioeconomico_gral_v3_afv.rptdesign&rude=' . $codrude . '&sucursalId=' . $sucursalId . '&inscripcionId=' . $inscripcionId . '&dirDep=' . $d_id . '&dirProv=' . $p_id . '&dirSec=' . $m_id. '&dirLoc=' . $l_id . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;


        /*$response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'rudelal_' . $rude . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_rude_socioeconomico_gral_v2_vcj.rptdesign&socioalteId=' . $socioalteId . '&rude=' . $rude . '&sucursalId=' . $sucursalId . '&inscripcionId=' . $inscripcionId . '&dirDep=' . $dirDep . '&dirProv=' . $dirProv . '&dirSec=' . $dirSec . '&dirLoc=' . $dirLoc . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;*/
    } 


    ///********************************MARCELO */
    public function reportEspAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
       // dump($arrDataInfo);
       // dump($reporte);die;
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $sie = $arrDataInfo['sie'];
        $gestion = $arrDataInfo['gestion'];
        $sucursalId = $arrDataInfo['subcea'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE SOLDADO MARINERO ESPECIALIDADES ';

        $response = new Response();
     //   dump($periodo);dump($gestion);dump($lugar);die;
     //      dump($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_distrito_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo.'&DisId=' . $lugar. '&&__format=pdf&');die;
        // por defecto
        $response->headers->set('Content-type', 'application/pdf');
       // dump($roluser);die;

        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=pdf&'));

        }elseif ($roluser== 7)
        {
            dump($periodo);dump($gestion);dump($lugar);die;
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DEPARTAMENTAL' . $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_departamento_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&DeptoId=' . $lugar. '&&__format=pdf&'));

        }elseif ($roluser== 10)
        {
    //        dump($periodo);dump($gestion);dump($lugar);die;
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DISTRITAL' . $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_distrito_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo.'&DisId=' . $lugar. '&&__format=pdf&'));

        }elseif ($roluser== 9)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DIRECTOR'. '_'.$sie.'_'. $periodo . '_' . $gestion . '.pdf'));
            //dump(($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Cod_Institucion_Edu=' . $sie . '&Gestion=' . $gestion. '&Periodo=' . $periodo. '&&__format=pdf&'));die;
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $sucursalId. '&&__format=pdf&'));

        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;





//        dump($formatreport   );
//     die;
    }




    public function reportAlterMatriculaGestionPdfAction(Request $request){

        if ($request->isMethod('POST')) {
            $gestion = $request->get('ges');
            $periodo = 2;
        }        
        
        $argum= 'REPORTE_ALTERNATIVA_MATRICULA_GESTION';

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');

        if (isset($_POST['btnGestionMatriculaPdf'])) {
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'_NACIONAL_'. $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_nacional_matricula_gestion_v1_rcm.rptdesign&Gestion=' . $gestion .  '&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        if (isset($_POST['btnGestionMatriculaXls'])) {
            $response->headers->set('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'_NACIONAL_'. $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_nacional_matricula_gestion_v1_rcm.rptdesign&Gestion=' . $gestion .  '&&__format=xlsx&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }
        
        return $response;        
    }


    public function reportAlterPrimariaPdfAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        // dump($arrDataInfo);
        // dump($reporte);die;
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $gestion = $arrDataInfo['gestion'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE ALTERNATIVA PRIMARIA';

        $response = new Response();
        //   dump($periodo);dump($gestion);dump($lugar);die;
         //     dump($this->container->getParameter('urlreportweb') . 'alt_lst_humanistica_maestro_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo.'&DisId=' . $lugar. '&&__format=pdf&');die;
        // por defecto
        $response->headers->set('Content-type', 'application/pdf');
        // dump($roluser);die;

        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_humanistica_maestro_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=pdf&'));

        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;


    }


    //'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    public function reportAlterPrimariaExcelAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        //dump($arrDataInfo);
        $roluser = $arrDataInfo['roluser'];
       // $userId=$arrDataInfo['userId'];
        $gestion = $arrDataInfo['gestion'];
        $periodo=$arrDataInfo['periodo'];
        //$lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE ALTERNATIVA PRIMARIA ';
        $response = new Response();

        $response->headers->set('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

     //   dump(($this->container->getParameter('urlreportweb') . 'alt_lst_humanistica_maestro_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=xlsx&'));die;
        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_humanistica_maestro_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=xlsx&'));

        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }

    public function reportAlterSecundariaPdfAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        // dump($arrDataInfo);
        // dump($reporte);die;
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $gestion = $arrDataInfo['gestion'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE ALTERNATIVA SECUNDARIA';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');

        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_humanistica_esa_maestro_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=pdf&'));
        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function reportAlterSecundariaExcelAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        $roluser = $arrDataInfo['roluser'];
        $gestion = $arrDataInfo['gestion'];
        $periodo=$arrDataInfo['periodo'];
        $argum= 'REPORTE ALTERNATIVA SECUNDARIA ';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_humanistica_esa_maestro_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=xlsx&'));
        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function reportAlterTecnicaPdfAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        // dump($arrDataInfo);
        // dump($reporte);die;
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $gestion = $arrDataInfo['gestion'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE ALTERNATIVA TECNICA';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');

        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_humanistica_eta_maestro_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=pdf&'));
        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function reportAlterTecnicaExcelAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        $roluser = $arrDataInfo['roluser'];
        $gestion = $arrDataInfo['gestion'];
        $periodo=$arrDataInfo['periodo'];
        $argum= 'REPORTE ALTERNATIVA TECNICA ';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_humanistica_eta_maestro_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=xlsx&'));
        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function reportAlterMatriculaModalidadAtencionPdfAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        // dump($arrDataInfo);
        // dump($reporte);die;
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $gestion = $arrDataInfo['gestion'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE ALTERNATIVA TECNICA';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_nacional_matricula_modalidad_atencion_v1_rcm.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    public function reportAlterMatriculaModalidadAtencionExcelAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        $roluser = $arrDataInfo['roluser'];
        $gestion = $arrDataInfo['gestion'];
        $periodo=$arrDataInfo['periodo'];
        $argum= 'REPORTE ALTERNATIVA TECNICA ';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.xlsx'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_nacional_matricula_modalidad_atencion_v1_rcm.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=xlsx&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    
    public function reportEstAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        //dump($arrDataInfo);
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $sie = $arrDataInfo['sie'];
        $gestion = $arrDataInfo['gestion'];
        $sucursalId = $arrDataInfo['subcea'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE SOLDADO MARINERO ESTADISTICAS ';

        $response = new Response();

        $response->headers->set('Content-type', 'application/pdf');
        // dump($roluser);die;

        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_soldado_o_marinero_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=pdf&'));

        }elseif ($roluser== 7)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DEPARTAMENTAL' . $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_soldado_o_marinero_departamento_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&DeptoId=' . $lugar. '&&__format=pdf&'));

        }elseif ($roluser== 10)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DISTRITAL' . $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_soldado_o_marinero_distrito_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo.'&DisId=' . $lugar.'&&__format=pdf&'));

        }elseif ($roluser== 9)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DIRECTOR'. '_'.$sie.'_'. $periodo . '_' . $gestion . '.pdf'));
            //dump(($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Cod_Institucion_Edu=' . $sie . '&Gestion=' . $gestion. '&Periodo=' . $periodo. '&&__format=pdf&'));die;
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $sucursalId. '&&__format=pdf&'));

        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;





//        dump($formatreport   );
//        die;
    }

    public function reportEspEXAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        //dump($arrDataInfo);
       // dump($reporte);die;
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $sie = $arrDataInfo['sie'];
        $gestion = $arrDataInfo['gestion'];
        $sucursalId = $arrDataInfo['subcea'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE SOLDADO MARINERO ESPECIALIDADES ';

        $response = new Response();

        $response->headers->set('Content-type', 'application/xlsx');


        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=xlsx&'));

        }elseif ($roluser== 7)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DEPARTAMENTAL' . $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_departamento_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&DeptoId=' . $lugar. '&&__format=xlsx&'));

        }elseif ($roluser== 10)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DISTRITAL' . $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_distrito_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo.'&DisId=' . $lugar. '&&__format=xlsx&'));

        }elseif ($roluser== 9)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DIRECTOR'. '_'.$sie.'_'. $periodo . '_' . $gestion . '.xlsx'));
            //dump(($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Cod_Institucion_Edu=' . $sie . '&Gestion=' . $gestion. '&Periodo=' . $periodo. '&&__format=xlsx&'));die;
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $sucursalId. '&&__format=xlsx&'));

        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;


//        dump($formatreport   );
//        die;
    }

    public function reportEstEXAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        //dump($arrDataInfo);
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $sie = $arrDataInfo['sie'];
        $gestion = $arrDataInfo['gestion'];
        $sucursalId = $arrDataInfo['subcea'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE SOLDADO MARINERO ESTADISTICAS ';

        $response = new Response();

        $response->headers->set('Content-type', 'application/xlsx');


        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_soldado_o_marinero_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=xlsx&'));

        }elseif ($roluser== 7)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DEPARTAMENTAL' . $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_soldado_o_marinero_departamento_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&DeptoId=' . $lugar. '&&__format=xlsx&'));

        }elseif ($roluser== 10)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DISTRITAL' . $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_soldado_o_marinero_distrito_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo.'&DisId=' . $lugar. '&&__format=xlsx&'));

        }elseif ($roluser== 9)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DIRECTOR'. '_'.$sie.'_'. $periodo . '_' . $gestion . '.xlsx'));
            //dump(($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Cod_Institucion_Edu=' . $sie . '&Gestion=' . $gestion. '&Periodo=' . $periodo. '&&__format=xlsx&'));die;
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $sucursalId. '&&__format=xlsx&'));

        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;


    }

    public function reportEspPeceAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        //dump($arrDataInfo);die;
        // dump($reporte);die;
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $sie = $arrDataInfo['sie'];
        $gestion = $arrDataInfo['gestion'];
        $sucursalId = $arrDataInfo['subcea'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE PERSONAS EN CONTEXTO DE ENCIERRO ESPECIALIDADES ';

        $response = new Response();


        $response->headers->set('Content-type', 'application/pdf');
        // dump($roluser);die;

        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_persona_contexto_encierro_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=pdf&'));

        }elseif ($roluser== 7)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DEPARTAMENTAL' . $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_persona_contexto_encierro_departamento_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&DeptoId=' . $lugar. '&&__format=pdf&'));

        }elseif ($roluser== 10)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DISTRITAL' . $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_persona_contexto_encierro_distrito_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo.'&DisId=' . $lugar. '&&__format=pdf&'));

        }elseif ($roluser== 9)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DIRECTOR'. '_'.$sie.'_'. $periodo . '_' . $gestion . '.pdf'));
            //dump(($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Cod_Institucion_Edu=' . $sie . '&Gestion=' . $gestion. '&Periodo=' . $periodo. '&&__format=pdf&'));die;
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_persona_contexto_encierro_director_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $sucursalId. '&&__format=pdf&'));

        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }

    public function reportEstPeceAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        //dump($arrDataInfo);
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $sie = $arrDataInfo['sie'];
        $gestion = $arrDataInfo['gestion'];
        $sucursalId = $arrDataInfo['subcea'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE PERSONAS EN CONTEXTO DE ENCIERRO ESTADISTICAS ';

        $response = new Response();

        $response->headers->set('Content-type', 'application/pdf');
        // dump($roluser);die;

        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_persona_contexto_encierro_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=pdf&'));

        }elseif ($roluser== 7)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DEPARTAMENTAL' . $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_persona_contexto_encierro_departamento_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&DeptoId=' . $lugar. '&&__format=pdf&'));

        }elseif ($roluser== 10)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DISTRITAL' . $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_persona_contexto_encierro_distrito_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo.'&DisId=' . $lugar.'&&__format=pdf&'));

        }elseif ($roluser== 9)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DIRECTOR'. '_'.$sie.'_'. $periodo . '_' . $gestion . '.pdf'));
            //dump(($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Cod_Institucion_Edu=' . $sie . '&Gestion=' . $gestion. '&Periodo=' . $periodo. '&&__format=pdf&'));die;
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_persona_contexto_encierro_director_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $sucursalId. '&&__format=pdf&'));

        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }

    public function reportEspEXPeceAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        //dump($arrDataInfo);
        // dump($reporte);die;
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $sie = $arrDataInfo['sie'];
        $gestion = $arrDataInfo['gestion'];
        $sucursalId = $arrDataInfo['subcea'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE PERSONAS EN CONTEXTO DE ENCIERRO ESPECIALIDADES ';

        $response = new Response();

        $response->headers->set('Content-type', 'application/xlsx');


        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_persona_contexto_encierro_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=xlsx&'));

        }elseif ($roluser== 7)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DEPARTAMENTAL' . $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_persona_contexto_encierro_departamento_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&DeptoId=' . $lugar. '&&__format=xlsx&'));

        }elseif ($roluser== 10)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DISTRITAL' . $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_persona_contexto_encierro_distrito_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo.'&DisId=' . $lugar. '&&__format=xlsx&'));

        }elseif ($roluser== 9)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DIRECTOR'. '_'.$sie.'_'. $periodo . '_' . $gestion . '.xlsx'));
            //dump(($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Cod_Institucion_Edu=' . $sie . '&Gestion=' . $gestion. '&Periodo=' . $periodo. '&&__format=xlsx&'));die;
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_persona_contexto_encierro_director_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $sucursalId. '&&__format=xlsx&'));

        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }

    public function reportEstEXPeceAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        //dump($arrDataInfo);
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $sie = $arrDataInfo['sie'];
        $gestion = $arrDataInfo['gestion'];
        $sucursalId = $arrDataInfo['subcea'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE PERSONAS EN CONTEXTO DE ENCIERRO ESTADISTICAS ';

        $response = new Response();

        $response->headers->set('Content-type', 'application/xlsx');


        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_persona_contexto_encierro_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=xlsx&'));

        }elseif ($roluser== 7)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DEPARTAMENTAL' . $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_persona_contexto_encierro_departamento_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&DeptoId=' . $lugar. '&&__format=xlsx&'));

        }elseif ($roluser== 10)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DISTRITAL' . $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_persona_contexto_encierro_distrito_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo.'&DisId=' . $lugar. '&&__format=xlsx&'));

        }elseif ($roluser== 9)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DIRECTOR'. '_'.$sie.'_'. $periodo . '_' . $gestion . '.xlsx'));
            //dump(($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Cod_Institucion_Edu=' . $sie . '&Gestion=' . $gestion. '&Periodo=' . $periodo. '&&__format=xlsx&'));die;
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_persona_contexto_encierro_director_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $sucursalId. '&&__format=xlsx&'));

        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }




    public function reportEspThAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        //dump($arrDataInfo);die;
        // dump($reporte);die;
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $sie = $arrDataInfo['sie'];
        $gestion = $arrDataInfo['gestion'];
        $sucursalId = $arrDataInfo['subcea'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE TRABAJADORAS(RES) DEL HOGAR ESPECIALIDADES ';

        $response = new Response();


        $response->headers->set('Content-type', 'application/pdf');
        // dump($roluser);die;

        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_trabajador_del_hogar_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=pdf&'));

        }elseif ($roluser== 7)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DEPARTAMENTAL' . $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_trabajador_del_hogar_departamento_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&DeptoId=' . $lugar. '&&__format=pdf&'));

        }elseif ($roluser== 10)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DISTRITAL' . $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_trabajador_del_hogar_distrito_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo.'&DisId=' . $lugar. '&&__format=pdf&'));

        }elseif ($roluser== 9)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DIRECTOR'. '_'.$sie.'_'. $periodo . '_' . $gestion . '.pdf'));
            //dump(($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Cod_Institucion_Edu=' . $sie . '&Gestion=' . $gestion. '&Periodo=' . $periodo. '&&__format=pdf&'));die;
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_trabajador_del_hogar_director_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $sucursalId. '&&__format=pdf&'));

        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }

    public function reportEstThAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        //dump($arrDataInfo);
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $sie = $arrDataInfo['sie'];
        $gestion = $arrDataInfo['gestion'];
        $sucursalId = $arrDataInfo['subcea'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE TRABAJADORAS(RES) DEL HOGAR ESTADISTICAS ';

        $response = new Response();

        $response->headers->set('Content-type', 'application/pdf');
        // dump($roluser);die;

        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_trabajador_del_hogar_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=pdf&'));

        }elseif ($roluser== 7)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DEPARTAMENTAL' . $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_trabajador_del_hogar_departamento_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&DeptoId=' . $lugar. '&&__format=pdf&'));

        }elseif ($roluser== 10)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DISTRITAL' . $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_trabajador_del_hogar_distrito_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo.'&DisId=' . $lugar.'&&__format=pdf&'));

        }elseif ($roluser== 9)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DIRECTOR'. '_'.$sie.'_'. $periodo . '_' . $gestion . '.pdf'));
            //dump(($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Cod_Institucion_Edu=' . $sie . '&Gestion=' . $gestion. '&Periodo=' . $periodo. '&&__format=pdf&'));die;
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_trabajador_del_hogar_director_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $sucursalId. '&&__format=pdf&'));

        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }

    public function reportEspEXThAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        //dump($arrDataInfo);
        // dump($reporte);die;
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $sie = $arrDataInfo['sie'];
        $gestion = $arrDataInfo['gestion'];
        $sucursalId = $arrDataInfo['subcea'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE TRABAJADORAS(RES) DEL HOGAR ESPECIALIDADES ';

        $response = new Response();

        $response->headers->set('Content-type', 'application/xlsx');


        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_trabajador_del_hogar_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=xlsx&'));

        }elseif ($roluser== 7)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DEPARTAMENTAL' . $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_trabajador_del_hogar_departamento_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&DeptoId=' . $lugar. '&&__format=xlsx&'));

        }elseif ($roluser== 10)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DISTRITAL' . $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_trabajador_del_hogar_distrito_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo.'&DisId=' . $lugar. '&&__format=xlsx&'));

        }elseif ($roluser== 9)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DIRECTOR'. '_'.$sie.'_'. $periodo . '_' . $gestion . '.xlsx'));
            //dump(($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Cod_Institucion_Edu=' . $sie . '&Gestion=' . $gestion. '&Periodo=' . $periodo. '&&__format=xlsx&'));die;
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_trabajador_del_hogar_director_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $sucursalId. '&&__format=xlsx&'));

        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }

    public function reportEstEXThAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        //dump($arrDataInfo);
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $sie = $arrDataInfo['sie'];
        $gestion = $arrDataInfo['gestion'];
        $sucursalId = $arrDataInfo['subcea'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE TRABAJADORAS(RES) DEL HOGAR ESTADISTICAS ';

        $response = new Response();

        $response->headers->set('Content-type', 'application/xlsx');


        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_trabajador_del_hogar_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=xlsx&'));

        }elseif ($roluser== 7)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DEPARTAMENTAL' . $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_trabajador_del_hogar_departamento_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&DeptoId=' . $lugar. '&&__format=xlsx&'));

        }elseif ($roluser== 10)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DISTRITAL' . $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_trabajador_del_hogar_distrito_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo.'&DisId=' . $lugar. '&&__format=xlsx&'));

        }elseif ($roluser== 9)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DIRECTOR'. '_'.$sie.'_'. $periodo . '_' . $gestion . '.xlsx'));
            //dump(($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Cod_Institucion_Edu=' . $sie . '&Gestion=' . $gestion. '&Periodo=' . $periodo. '&&__format=xlsx&'));die;
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_est_edudiv_trabajador_del_hogar_director_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $sucursalId. '&&__format=xlsx&'));

        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }

    public function reportListMaestroInsAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        //dump($arrDataInfo);
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $sie = $arrDataInfo['sie'];
        $gestion = $arrDataInfo['gestion'];
        $sucursalId = $arrDataInfo['subcea'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE LISTA MAESTROS INSTRUCTORES MILITARES ';

        $response = new Response();

        $response->headers->set('Content-type', 'application/pdf');
        // dump($roluser);die;

        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_edudiv_maestro_instructor_militar_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=pdf&'));

        }elseif ($roluser== 7)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DEPARTAMENTAL' . $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_edudiv_maestro_instructor_militar_departamento_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&DeptoId=' . $lugar. '&&__format=pdf&'));

        }elseif ($roluser== 10)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DISTRITAL' . $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_edudiv_maestro_instructor_militar_distrito_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo.'&DisId=' . $lugar. '&&__format=pdf&'));

        }elseif ($roluser== 9)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DIRECTOR'. '_'.$sie.'_'. $periodo . '_' . $gestion . '.pdf'));
            //dump(($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Cod_Institucion_Edu=' . $sie . '&Gestion=' . $gestion. '&Periodo=' . $periodo. '&&__format=pdf&'));die;
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_edudiv_maestro_instructor_militar_director_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $sucursalId. '&&__format=pdf&'));

        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }

    public function reportListMaestroInsExAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        //dump($arrDataInfo);
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $sie = $arrDataInfo['sie'];

        $gestion = $arrDataInfo['gestion'];
        $sucursalId = $arrDataInfo['subcea'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE LISTA MAESTROS INSTRUCTORES MILITARES ';

        $response = new Response();

        $response->headers->set('Content-type', 'application/xlsx');


        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_edudiv_maestro_instructor_militar_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=xlsx&'));

        }elseif ($roluser== 7)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DEPARTAMENTAL' . $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_edudiv_maestro_instructor_militar_departamento_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&DeptoId=' . $lugar. '&&__format=xlsx&'));

        }elseif ($roluser== 10)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DISTRITAL' . $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_edudiv_maestro_instructor_militar_distrito_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo.'&DisId=' . $lugar. '&&__format=xlsx&'));

        }elseif ($roluser== 9)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DIRECTOR'. '_'.$sie.'_'. $periodo . '_' . $gestion . '.xlsx'));
            //dump(($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Cod_Institucion_Edu=' . $sie . '&Gestion=' . $gestion. '&Periodo=' . $periodo. '&&__format=xlsx&'));die;
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_edudiv_maestro_instructor_militar_director_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $sucursalId. '&&__format=xlsx&'));

        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }

    public function reportListMaestroRecPAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        //dump($arrDataInfo);
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $sie = $arrDataInfo['sie'];
        $gestion = $arrDataInfo['gestion'];
        $sucursalId = $arrDataInfo['subcea'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE LISTA MAESTROS RECINTOS PENITENCIARIOS ';

        $response = new Response();

        $response->headers->set('Content-type', 'application/pdf');
        // dump($roluser);die;

        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_edudiv_maestro_recinto_penitenciario_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=pdf&'));

        }elseif ($roluser== 7)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DEPARTAMENTAL' . $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_edudiv_maestro_recinto_penitenciario_departamento_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&DeptoId=' . $lugar. '&&__format=pdf&'));

        }elseif ($roluser== 10)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DISTRITAL' . $periodo . '_' . $gestion . '.pdf'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_edudiv_maestro_recinto_penitenciario_distrito_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo.'&DisId=' . $lugar. '&&__format=pdf&'));

        }elseif ($roluser== 9)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DIRECTOR'. '_'.$sie.'_'. $periodo . '_' . $gestion . '.pdf'));
            //dump(($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Cod_Institucion_Edu=' . $sie . '&Gestion=' . $gestion. '&Periodo=' . $periodo. '&&__format=pdf&'));die;
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_edudiv_maestro_recinto_penitenciario_director_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $sucursalId. '&&__format=pdf&'));

        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }

    public function reportListMaestroRecPExAction(Request $request, $dataInfo){

        $arrDataInfo = unserialize($dataInfo);
        //dump($arrDataInfo);
        $roluser = $arrDataInfo['roluser'];
        $userId=$arrDataInfo['userId'];
        $sie = $arrDataInfo['sie'];
        $gestion = $arrDataInfo['gestion'];
        $sucursalId = $arrDataInfo['subcea'];
        $periodo=$arrDataInfo['periodo'];
        $lugar= $arrDataInfo['lugarid'];
        $argum= 'REPORTE LISTA MAESTROS RECINTOS PENITENCIARIOS ';

        $response = new Response();

        $response->headers->set('Content-type', 'application/xlsx');


        if (($roluser== 8) || ($roluser==20))
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'NACIONAL'. $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_edudiv_maestro_recinto_penitenciario_nacional_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&&__format=xlsx&'));

        }elseif ($roluser== 7)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DEPARTAMENTAL' . $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_edudiv_maestro_recinto_penitenciario_departamento_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo. '&DeptoId=' . $lugar. '&&__format=xlsx&'));

        }elseif ($roluser== 10)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DISTRITAL' . $periodo . '_' . $gestion . '.xlsx'));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_edudiv_maestro_recinto_penitenciario_distrito_v1_ma.rptdesign&Gestion=' . $gestion .  '&Periodo=' . $periodo.'&DisId=' . $lugar. '&&__format=xlsx&'));

        }elseif ($roluser== 9)
        {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'DIRECTOR'. '_'.$sie.'_'. $periodo . '_' . $gestion . '.xlsx'));
            //dump(($this->container->getParameter('urlreportweb') . 'alt_esp_edudiv_soldado_o_marinero_director_v1_ma.rptdesign&Cod_Institucion_Edu=' . $sie . '&Gestion=' . $gestion. '&Periodo=' . $periodo. '&&__format=xlsx&'));die;
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_lst_edudiv_maestro_recinto_penitenciario_director_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' .$gestion .  '&Periodo=' . $periodo. '&Subcea=' . $sucursalId. '&&__format=xlsx&'));

        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }
    ///********************************MARCELO */
    public function reportesCeasPendientesAction(Request $request){

        
        $form = $request->get('form'); 
        //dump($form);die;
        $tipo = $form['tipo']; 
        $rol = $form['rol']; 
        $gestion = $form['gestion1']; 
        $id_usuario = $form['id_usuario'];
        $em = $this->getDoctrine()->getManager();
        if ($rol == 8 )
        {
            $departamento = $form['departamento1'];
            if($departamento!="")
            {
                $em = $this->getDoctrine()->getManager();
                $dep = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($departamento);
            }
            
        }
        $argum= 'CEAS CON PENDIENTES DE REPORTE DE INFORMACIÓN';
        $response = new Response();
        $response->headers->set('Content-type', 'application/' . $tipo);

        //dump($rol);die;
        if ($rol== 8) //NACIONAL
        {   //dump($departamento);die;
            if($gestion!="" and $departamento!="")
            {   
                //dump($departamento);die;
                $nombre_archivo = "alt_lst_CEAS_pendientes_nacional_v2_ma";
                $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum . '_' . strtoupper($dep->getDepartamento()) . '_' . $gestion . '.' . $tipo));
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $nombre_archivo .'.rptdesign&Gestion=' . $gestion .  '&Departamento=' . $departamento. '&&__format='. $tipo .'&'));
            }else{
                if($gestion=='' and $departamento=='')
                {
                    $nombre_archivo = "alt_lst_CEAS_pendientes_nacional_v1_ma";
                    $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum . '.' . $tipo));
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $nombre_archivo .'.rptdesign&&__format='. $tipo .'&'));
                }else{
                    if($gestion)
                    {
                        //dump($departamento);die;
                        $nombre_archivo = "alt_lst_CEAS_pendientes_nacional_v3_ma";
                        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum . '_NACIONAL_' . $gestion . '.' . $tipo));
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $nombre_archivo .'.rptdesign&Gestion=' . $gestion . '&&__format='. $tipo .'&'));
                    }else{

                        $nombre_archivo = "alt_lst_CEAS_pendientes_nacional_v4_ma";
                        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum . '_' . strtoupper($dep->getDepartamento()) . '.' . $tipo));
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $nombre_archivo .'.rptdesign&Departamento=' . $departamento . '&&__format='. $tipo .'&'));
                    }
                }
            }
        }elseif ($rol == 7)//departamental
        {
            $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$id_usuario,'rolTipo'=>$rol));
            //dump($usuariorol);die;
            $idlugarusuario = $usuariorol[0]->getLugarTipo()->getId();
            
            if($gestion)
            {
                $nombre_archivo = "alt_lst_CEAS_pendientes_departamental_v2_ma";
                $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum . '_DEPARTAMENTAL_' . $gestion . '.' . $tipo));
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $nombre_archivo .'.rptdesign&Gestion=' . $gestion . '&Departamento=' . $idlugarusuario . '&&__format='. $tipo .'&'));
            }else{
                $nombre_archivo = "alt_lst_CEAS_pendientes_departamental_v1_ma";
                $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum . '_DEPARTAMENTAL.'.$tipo));
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $nombre_archivo . '.rptdesign&Departamento=' . $idlugarusuario . '&&__format='. $tipo . '&'));
            }
        }elseif ($rol == 10)//DISTRITO
        {
            $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$id_usuario,'rolTipo'=>$rol));
            $distrito = $usuariorol[0]->getLugarTipo()->getCodigo();
            if($gestion)
            {
                $nombre_archivo = "alt_lst_CEAS_pendientes_distrital_v2_ma";
                $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum . '_DISTRITAL_' . $gestion . '.' .$tipo));
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $nombre_archivo .'.rptdesign&Gestion=' . $gestion . '&Distrito=' . $distrito . '&&__format='. $tipo .'&'));
            }else{
                $nombre_archivo = "alt_lst_CEAS_pendientes_distrital_v1_ma";
                $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum . '_DISTRITAL.'. $tipo));
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $nombre_archivo .'.rptdesign&&__format='. $tipo .'&'));
            }
        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    public function reportesCeasOperativosAction(Request $request){

        //dump();die;
        $form = $request->get('form'); 
        //dump($form);die;
        $tipo = $form['tipo']; 
        $rol = $form['rol']; 
        $gestion = $form['gestion1']; 
        $id_usuario = $form['id_usuario']; 
        $em = $this->getDoctrine()->getManager();
        if ($rol == 8 )
        {
            $departamento = $form['departamento1'];
            if($departamento!="")
            {
                $em = $this->getDoctrine()->getManager();
                $dep = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($departamento);
            }
        }
        $argum= 'CONTROL DE OPERATIVOS POR CEA';
        $response = new Response();
        $response->headers->set('Content-type', 'application/' . $tipo);
       // dump($roluser);die;
        if ($rol== 8) //NACIONAL
        {
            if($gestion!= "" and $departamento!="")
            {
                $nombre_archivo = "alt_lst_panel_control_operativos_nacional_v2_ma";
                $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum . '_' . strtoupper($dep->getDepartamento()) . '_' . $gestion . '.' . $tipo));
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $nombre_archivo .'.rptdesign&Gestion=' . $gestion .  '&Departamento=' . $departamento. '&&__format='. $tipo .'&'));
            }else{
                if($gestion=="" and $departamento=="")
                {
                    $nombre_archivo = "alt_lst_panel_control_operativos_nacional_v1_ma";
                    $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum . '.' . $tipo));
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $nombre_archivo .'.rptdesign&&__format='. $tipo .'&'));
                }else{
                    if($gestion)
                    {
                        $nombre_archivo = "alt_lst_panel_control_operativos_nacional_v3_ma";
                        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum . '_NACIONAL_' . $gestion . '.' . $tipo));
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $nombre_archivo .'.rptdesign&Gestion=' . $gestion . '&&__format='. $tipo .'&'));
                    }else{
                        $nombre_archivo = "alt_lst_panel_control_operativos_nacional_v4_ma";
                        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum . '_' . strtoupper($dep->getDepartamento()) . '.' . $tipo));
                        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $nombre_archivo .'.rptdesign&Departamento=' . $departamento . '&&__format='. $tipo .'&'));
                    }
                }
            }
            
        }elseif ($rol == 7)//departamental
        {
            $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$id_usuario,'rolTipo'=>$rol));
            //dump($usuariorol);die;
            $idlugarusuario = $usuariorol[0]->getLugarTipo()->getId();
            
            if($gestion)
            {
                $nombre_archivo = "alt_lst_panel_control_operativos_departamental_v2_ma";
                $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum . '_DEPARTAMENTAL_' . $gestion . '.' . $tipo));
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $nombre_archivo .'.rptdesign&Gestion=' . $gestion . '&Departamento=' . $idlugarusuario . '&&__format='. $tipo .'&'));
            }else{
                $nombre_archivo = "alt_lst_panel_control_operativos_departamental_v1_ma";
                $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum . '_DEPARTAMENTAL.'.$tipo));
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $nombre_archivo . '.rptdesign&Departamento=' . $idlugarusuario . '&&__format='. $tipo . '&'));
            }
        }elseif ($rol == 10)//DISTRITO
        {
            $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$id_usuario,'rolTipo'=>$rol));
            $distrito = $usuariorol[0]->getLugarTipo()->getCodigo();
            if($gestion)
            {
                $nombre_archivo = "alt_lst_panel_control_operativos_distrital_v2_ma";
                $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum . '_DISTRITAL_' . $gestion . '.' .$tipo));
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $nombre_archivo .'.rptdesign&Gestion=' . $gestion . '&Distrito=' . $distrito . '&&__format='. $tipo .'&'));
            }else{
                $nombre_archivo = "alt_lst_panel_control_operativos_distrital_v1_ma";
                $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum . '_DISTRITAL.'. $tipo));
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $nombre_archivo .'.rptdesign&&__format='. $tipo .'&'));
            }
        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

     public function reportCertificadosPrimariaAction(Request $request) {
        

      //get the data send to the report
        $infoUe = $request->get('data');
        $aInfoUeducativa = unserialize($infoUe);
        
        $sie = $this->session->get('ie_id');
        $idCurso = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $gestion = $this->session->get('ie_gestion');
        $subcea = $this->session->get('ie_subcea');
        $periodo = $this->session->get('ie_per_cod');

        //get the values of report
        //create the response object to down load the file
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'certificadoMID_' . $sie . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'alt_cert_capacitacion_v1_ma.rptdesign&Sie=' . $sie . '&Gestion=' . $gestion . '&Periodo=' . $periodo . '&Subcea=' . $subcea . '&Idcurso=' . $idCurso . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
}
