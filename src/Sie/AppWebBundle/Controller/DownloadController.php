<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use Sie\AppWebBundle\Entity\Usuario;
use Sie\AppWebBundle\Entity\BjpValidacionueProcesoApertura;
use Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityRepository;

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
    public function ddjjoneAction(Request $request, $rude, $gestion, $sie) {
        //get the values of report
        //create the response object to down load the file
        $response = new Response();
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

    /**
     * get DDJJJ per UE
     * @param Request $request
     * @param type $gestion
     * @param type $sie
     * @return Response
     */
    public function ddjjgroupAction(Request $request, $gestion, $sie) {


        $response = new Response();
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'ddjj_' . $sie . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_dj_DeclaracionJurada_Unidadeducativa_gral_v1.rptdesign&gestion=' . $gestion . '&sie=' . $sie . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * get the studens per course
     * @param Request $request
     * @param type $ue
     * @param type $gestion
     * @param type $nivel
     * @param type $grado
     * @param type $paralelo
     * @param type $turno
     * @return Response pdf list with studens per course
     */
    public function listStudentPerCourseAction(Request $request, $ue, $gestion, $nivel, $grado, $paralelo, $turno) {

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'list_C_' . $ue . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesInscritos_Curso_gral_v1.rptdesign&ue=' . $ue . '&gestion=' . $gestion . '&nivel=' . $nivel . '&grado=' . $grado . '&turno=' . $turno . '&Paralelo=' . $paralelo . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function listStudentPerCourseSegAction(Request $request, $ue, $gestion, $nivel, $grado, $paralelo, $turno) {

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'list_C_' . $ue . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesInscritos_Curso_gral_v1_ma.rptdesign&ue=' . $ue . '&gestion=' . $gestion . '&nivel=' . $nivel . '&grado=' . $grado . '&turno=' . $turno . '&Paralelo=' . $paralelo . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * get the list of students per UE
     * @param Request $request
     * @param type $ue
     * @param type $gestion
     * @return Response a pdf list students per UE
     */
    public function listStudentPerUeAction(Request $request, $ue, $gestion) {

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'list_ue_' . $ue . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesInscritos_UnidadEducativa_gral_v1.rptdesign&ue=' . $ue . '&gestion=' . $gestion . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function listStudentPerUeSegAction(Request $request, $ue, $gestion) {

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'list_ue_' . $ue . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesInscritos_UnidadEducativa_gral_v1_ma.rptdesign&ue=' . $ue . '&gestion=' . $gestion . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * get the list of students per UE
     * @param Request $request
     * @param type $ue
     * @param type $gestion
     * @return Response a pdf list students per UE
     */
    public function listStudentPerUeUnAcreditedAction(Request $request, $sie) {

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'list_UeNoAcreditado' . $sie . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_dj_Estudiantes_UnidadEducativaNoAcreditada_gral_v1.rptdesign&uena=' . $sie . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * get the list of students per UE
     * @param Request $request
     * @param type $ue
     * @param type $gestion
     * @return Response a pdf list students per UE
     */
    public function listStudentPerUeTecnicoHumanisticoAction(Request $request, $uetu, $gestion) {

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'list_UeTecHumanistico' . $uetu . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_dj_EstudiantesEspecialidad_UnidadEducativa_gral_v1.rptdesign&uetu=' . $uetu . '&gestion=' . $gestion . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * get the list of students per Distrito
     * @param Request $request
     * @param type $ue
     * @param type $gestion
     * @return Response a pdf list students per UE
     */
    public function downloaddistritoAction(Request $request, $file) {

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'list_distrito' . $file . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_dj_EstudiantesEstadoBJP_distrito_gral_v2_vcj.rptdesign&COD_DIS=' . $file . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * get the list of students per Distrito
     * @param Request $request
     * @param type $ue
     * @param type $gestion
     * @return Response a pdf list students per UE
     */
    public function downloaddistritoxlsAction(Request $request, $file) {

        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'list_distrito' . $file . '.xls'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_dj_EstudiantesEstadoBJP_distrito_gral_v2_vcj.rptdesign&COD_DIS=' . $file . '&&__format=xls&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * reporte info inscrito
     * @param Request $request
     * @param type $file
     * @return Response
     */
    public function downloadinfoInscritosAction() {

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'list_EstudiantesNal.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_cantidadinscritosestado_null_gral_v1_vcj.rptdesign&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * reporte Solcitud de modificaion de calificaciones
     * @param Request $request
     * @param type $file
     * @return Response
     */
    public function solicitudModificacionCalificacionesAction(Request $request, $idSolicitud) {
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'dwj_solicitud_modificacion_S' . $idSolicitud . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_dj_SolicitudModificacionCalificaciones_ue_gral_v1_jqc.rptdesign&p_codsol=' . $idSolicitud . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * reporte Solcitud de adicion de calificaciones
     * @param Request $request
     * @param type $file
     * @return Response
     */
    public function solicitudAdicionCalificacionesAction(Request $request, $idSolicitud) {
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename=dj_solicitud_adicion_S' . $idSolicitud . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_dj_SolicitudAdicionCalificaciones_ue_gral_v1_jqc.rptdesign&p_codsol=' . $idSolicitud . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * reporte students list TEC - HUM
     * @param Request $request
     * @param type $file
     * @return Response
     */
    public function listgraltechumAction(Request $request) {

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename=list_gral_estudiantes_tec_hum2015.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'tec_hum_list_estudiantes_especialidad.rptdesign&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * reporte students list TEC - HUM
     * @param Request $request
     * @param type $ue
     * @param type $gestion
     * @return Response a pdf list students per UE tec-hum
     */
    public function listgraltechumxlsAction(Request $request) {

        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'list_gral_estudiantes_tec_hum2015.xls'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'tec_hum_list_estudiantes_especialidad.rptdesign&&__format=xls&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * reporte students list TEC - HUM
     * @param Request $request
     * @param type $file
     * @return Response
     */
    public function resumentechumAction(Request $request) {

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename=list_resumen_estudiantes_tec_hum2015.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'tec_hum_resumen_certificado.rptdesign&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * reporte cantidad de inscritos por departamento
     * @param Request $request
     * @param type $file
     * @return Response
     */
    public function departamentoreporteAction(Request $request) {

        $form = $request->get('sie_formdepto');
        $gestion = $form['gestion'];
        $depto = $form['depto'];

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $arch = 'REPORTE_CANTIDAD_INSCRITOS_' . $depto . '_' . $gestion . '_' . date('YmdHis') . '.pdf';
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_cantidadinscritosestado_dep_gral_v1_vcj.rptdesign&gestion=' . $gestion . '&depto=' . $depto . '&&__format=pdf'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * reporte cantidad de inscritos por distrito
     * @param Request $request
     * @param type $file
     * @return Response
     */
    public function distritoreporteAction(Request $request) {

        $form = $request->get('sie_formdistrito');
        $gestion = $form['gestion'];
        $coddis = $form['coddis'];

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $arch = 'REPORTE_CANTIDAD_INSCRITOS_' . $coddis . '_' . $gestion . '_' . date('YmdHis') . '.pdf';
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_cantidadinscritosestado_dis_gral_v1_vcj.rptdesign&gestion=' . $gestion . '&dist=' . $coddis . '&&__format=pdf'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

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

    /**
     * get the list of students per UE
     * @param Request $request
     * @param type $ue
     * @param type $gestion
     * @return Response a pdf list students per UE
     */
    public function boletinPromoPerUeAction(Request $request, $ue, $nivel, $ciclo, $grado, $paralelo, $turno, $gestion, $itemsUe) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = $fechaActual->format('Y');
        $em=$this->getDoctrine()->getManager();
        $aDataUe = explode(',', $itemsUe);

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'boletin_centralizador_' . $ue . '_' . $nivel . '_' . trim($aDataUe[1]) . '_' . trim($aDataUe[2]) . '_' . $gestion . '.pdf'));
        //get the data to do the report
        $user= $this->session->get('userName');//strtolower(str_replace(' ','_', $this->session->get('name').' '.$this->session->get('lastname').' '.$this->session->get('lastname2')));
        $sie = $ue;
        $data = $user.'|'.$sie.'|'.$gestion.'|'.$nivel.'|'.$ciclo.'|'.$grado.'|'.$paralelo.'|'.$turno;
        $link = 'http://'.$_SERVER['SERVER_NAME'].'/cen/'.$this->getLinkEncript($data);
        $boletin_oficial = false;

        $objRegistroConsolidacion = $em->createQueryBuilder()
            ->select('rc.bim1,rc.bim2,rc.bim3,rc.bim4')
            ->from('SieAppWebBundle:RegistroConsolidacion', 'rc')
            ->where('rc.unidadEducativa = :ue')
            ->andWhere('rc.gestion = :gestion')
            ->setParameter('ue',$sie)
            ->setParameter('gestion',$gestion)
            ->getQuery()
            ->getResult();
        
        if ($gestion >= 2020){
            if($objRegistroConsolidacion[0]['bim1'] > 0 and $objRegistroConsolidacion[0]['bim2'] > 0 and $objRegistroConsolidacion[0]['bim3'] > 0){
                $boletin_oficial = true;
            }
        } else {
            if ($gestion > 2013){
                if($objRegistroConsolidacion[0]['bim1'] > 0 and $objRegistroConsolidacion[0]['bim2'] > 0 and $objRegistroConsolidacion[0]['bim3'] > 0 and $objRegistroConsolidacion[0]['bim4'] > 0){
                    $boletin_oficial = true;
                }
            }
        }

        switch ($nivel) {
            case 1:
            case 11:
                if($gestion == $gestionActual and !$boletin_oficial){
                    if($gestion >= 2020){
                        $report = $this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesBoletinPromocion_inicial_v3_no_valido.rptdesign&usuario=' . $user . '&lk=' . $link .'&institucioneducativa_id='. $sie .'&nivel_tipo_id=' . $nivel . '&ciclo_tipo_id=' . $ciclo . '&grado_tipo_id=' . $grado . '&paralelo_tipo_id=' . $paralelo . '&turno_tipo_id=' . $turno . '&gestion_tipo_id=' . $gestion . '&&__format=pdf&';
                    } else {
                        $report = $this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesBoletinPromocion_inicial_v2_no_valido.rptdesign&usuario=' . $user . '&lk=' . $link .'&institucioneducativa_id='. $sie .'&nivel_tipo_id=' . $nivel . '&ciclo_tipo_id=' . $ciclo . '&grado_tipo_id=' . $grado . '&paralelo_tipo_id=' . $paralelo . '&turno_tipo_id=' . $turno . '&gestion_tipo_id=' . $gestion . '&&__format=pdf&';
                    }
                } else {
                    if($gestion >= 2020){
                        $report = $this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesBoletinPromocion_inicial_v3.rptdesign&usuario=' . $user . '&lk=' . $link .'&institucioneducativa_id='. $sie .'&nivel_tipo_id=' . $nivel . '&ciclo_tipo_id=' . $ciclo . '&grado_tipo_id=' . $grado . '&paralelo_tipo_id=' . $paralelo . '&turno_tipo_id=' . $turno . '&gestion_tipo_id=' . $gestion . '&&__format=pdf&';
                    } else {
                        $report = $this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesBoletinPromocion_inicial_v2.rptdesign&usuario=' . $user . '&lk=' . $link .'&institucioneducativa_id='. $sie .'&nivel_tipo_id=' . $nivel . '&ciclo_tipo_id=' . $ciclo . '&grado_tipo_id=' . $grado . '&paralelo_tipo_id=' . $paralelo . '&turno_tipo_id=' . $turno . '&gestion_tipo_id=' . $gestion . '&&__format=pdf&';                        
                    }
                }
                break;
            case 2:
            case 3:
            case 12:
            case 13:
                if($gestion == $gestionActual and !$boletin_oficial){
                    if($gestion >= 2020) {
                        if($gestion == 2020) {                        
                            if ($nivel == 12 and $grado == 1) {
                                $report = $this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesBoletinPromocion_inicial_v3_no_valido.rptdesign&usuario=' . $user . '&lk=' . $link .'&institucioneducativa_id='. $sie .'&nivel_tipo_id=' . $nivel . '&ciclo_tipo_id=' . $ciclo . '&grado_tipo_id=' . $grado . '&paralelo_tipo_id=' . $paralelo . '&turno_tipo_id=' . $turno . '&gestion_tipo_id=' . $gestion . '&&__format=pdf&';
                            } else  {
                                $report = $this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesBoletinPromocion_v3_no_valido.rptdesign&usuario=' . $user . '&lk=' . $link .'&institucioneducativa_id='. $sie .'&nivel_tipo_id=' . $nivel . '&ciclo_tipo_id=' . $ciclo . '&grado_tipo_id=' . $grado . '&paralelo_tipo_id=' . $paralelo . '&turno_tipo_id=' . $turno . '&gestion_tipo_id=' . $gestion . '&&__format=pdf&';
                            }
                        }else{
                                $report = $this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesBoletinPromocion_v3_no_valido.rptdesign&usuario=' . $user . '&lk=' . $link .'&institucioneducativa_id='. $sie .'&nivel_tipo_id=' . $nivel . '&ciclo_tipo_id=' . $ciclo . '&grado_tipo_id=' . $grado . '&paralelo_tipo_id=' . $paralelo . '&turno_tipo_id=' . $turno . '&gestion_tipo_id=' . $gestion . '&&__format=pdf&';                            
                        }
                    } else {
                        $report = $this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesBoletinPromocion_v2_no_valido.rptdesign&usuario=' . $user . '&lk=' . $link .'&institucioneducativa_id='. $sie .'&nivel_tipo_id=' . $nivel . '&ciclo_tipo_id=' . $ciclo . '&grado_tipo_id=' . $grado . '&paralelo_tipo_id=' . $paralelo . '&turno_tipo_id=' . $turno . '&gestion_tipo_id=' . $gestion . '&&__format=pdf&';
                    }
                } else {
                    if($gestion >= 2020) {
                        if($gestion == 2020) {
                            if ($nivel == 12 and $grado == 1) {
                                $report = $this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesBoletinPromocion_inicial_v3.rptdesign&usuario=' . $user . '&lk=' . $link .'&institucioneducativa_id='. $sie .'&nivel_tipo_id=' . $nivel . '&ciclo_tipo_id=' . $ciclo . '&grado_tipo_id=' . $grado . '&paralelo_tipo_id=' . $paralelo . '&turno_tipo_id=' . $turno . '&gestion_tipo_id=' . $gestion . '&&__format=pdf&';
                            } else {
                                $report = $this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesBoletinPromocion_v3.rptdesign&usuario=' . $user . '&lk=' . $link .'&institucioneducativa_id='. $sie .'&nivel_tipo_id=' . $nivel . '&ciclo_tipo_id=' . $ciclo . '&grado_tipo_id=' . $grado . '&paralelo_tipo_id=' . $paralelo . '&turno_tipo_id=' . $turno . '&gestion_tipo_id=' . $gestion . '&&__format=pdf&';
                            }
                        }else{
                                $report = $this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesBoletinPromocion_v3.rptdesign&usuario=' . $user . '&lk=' . $link .'&institucioneducativa_id='. $sie .'&nivel_tipo_id=' . $nivel . '&ciclo_tipo_id=' . $ciclo . '&grado_tipo_id=' . $grado . '&paralelo_tipo_id=' . $paralelo . '&turno_tipo_id=' . $turno . '&gestion_tipo_id=' . $gestion . '&&__format=pdf&';                            
                        }
                    } else {
                        $report = $this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesBoletinPromocion_v2.rptdesign&usuario=' . $user . '&lk=' . $link .'&institucioneducativa_id='. $sie .'&nivel_tipo_id=' . $nivel . '&ciclo_tipo_id=' . $ciclo . '&grado_tipo_id=' . $grado . '&paralelo_tipo_id=' . $paralelo . '&turno_tipo_id=' . $turno . '&gestion_tipo_id=' . $gestion . '&&__format=pdf&';
                    }                    
                }
                break;
        }

        $response->setContent(file_get_contents($report));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    /**
     * get the list of students per UE
     * @param Request $request
     * @param type $ue
     * @param type $gestion
     * @return Response a pdf list students per UE
     */
    public function boletinPromoPerUeNewVersionAction(Request $request, $version, $ue, $nivel, $ciclo, $grado, $paralelo, $turno, $gestion, $itemsUe) {

        $aDataUe = explode(',', $itemsUe);

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'boletin_centralizador_' . $ue . '_' . $nivel . '_' . trim($aDataUe[1]) . '_' . trim($aDataUe[2]) . '_' . $gestion . '.pdf'));
        //get the data to do the report
        $user= $this->session->get('userName');//strtolower(str_replace(' ','_', $this->session->get('name').' '.$this->session->get('lastname').' '.$this->session->get('lastname2')));
        $sie = $ue;
        $data = $user.'|'.$sie.'|'.$gestion.'|'.$nivel.'|'.$ciclo.'|'.$grado.'|'.$paralelo.'|'.$turno;
        $link = 'http://'.$_SERVER['SERVER_NAME'].'/cen/'.$this->getLinkEncript($data);
        switch ($version) {
            case 1:
                # code...
                $nameReportPrimSecc = 'reg_lst_EstudiantesBoletinPromocionPeriodo_v1';
                $nameReportInicial  = 'reg_lst_EstudiantesBoletinPromocion_inicialPeriodo_v1';

                break;
            case 2:
                # code...
                $nameReportPrimSecc = 'reg_lst_EstudiantesBoletinPromocionPeriodo_v2';
                 $nameReportInicial = 'reg_lst_EstudiantesBoletinPromocion_inicialPeriodo_v2';
            
                break;
            
            default:
                # code...
                $nameReportPrimSecc  = 'reg_lst_EstudiantesBoletinPromocionPeriodo_v1';
                $nameReportInicial   = 'reg_lst_EstudiantesBoletinPromocion_inicialPeriodo_v1';
                break;
        }
        switch ($nivel) {
            case 1:
            case 11:
                $report = $this->container->getParameter('urlreportweb') . $nameReportInicial.'.rptdesign&usuario=' . $user . '&lk=' . $link .'&institucioneducativa_id='. $sie .'&nivel_tipo_id=' . $nivel . '&ciclo_tipo_id=' . $ciclo . '&grado_tipo_id=' . $grado . '&paralelo_tipo_id=' . $paralelo . '&turno_tipo_id=' . $turno . '&gestion_tipo_id=' . $gestion . '&&__format=pdf&';
                break;
            case 2:
            case 3:
            case 12:
            case 13:
                $report = $this->container->getParameter('urlreportweb') . $nameReportPrimSecc.'.rptdesign&usuario=' . $user . '&lk=' . $link .'&institucioneducativa_id='. $sie .'&nivel_tipo_id=' . $nivel . '&ciclo_tipo_id=' . $ciclo . '&grado_tipo_id=' . $grado . '&paralelo_tipo_id=' . $paralelo . '&turno_tipo_id=' . $turno . '&gestion_tipo_id=' . $gestion . '&&__format=pdf&';
                break;
          }
        
        $response->setContent(file_get_contents($report));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * get the list of maestros per UE
     * @param Request $request
     * @param type $ue
     * @param type $gestion
     * @return Response a pdf list maestros per UE
     */
    public function listMaestrosPerUeAction(Request $request, $ue, $nivel, $ciclo, $grado, $paralelo, $turno, $gestion, $itemsUe) {

        $aDataUe = explode(',', $itemsUe);

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'Lista_Maestros_' . $ue . '_' . $nivel . '_' . trim($aDataUe[1]) . '_' . trim($aDataUe[2]) . '_' . $gestion . '.pdf'));

        if($gestion >= 2020) {
            $report = $this->container->getParameter('urlreportweb') . 'reg_lst_MaestrosBoletinCentralizdor_v2.rptdesign&ue=' . $ue . '&nivel=' . $nivel . '&ciclo=' . $ciclo . '&grado=' . $grado . '&paralelo=' . $paralelo . '&turno=' . $turno . '&gestion=' . $gestion . '&&__format=pdf&';
        } else {
            $report = $this->container->getParameter('urlreportweb') . 'reg_lst_MaestrosBoletinCentralizdor_v1.rptdesign&ue=' . $ue . '&nivel=' . $nivel . '&ciclo=' . $ciclo . '&grado=' . $grado . '&paralelo=' . $paralelo . '&turno=' . $turno . '&gestion=' . $gestion . '&&__format=pdf&';
        }

        $response->setContent(file_get_contents($report));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * build the DDJJ - student report download pdf
     * @param Request $request
     * @return object form login
     */
    public function ddjjStudentWebAction(Request $request) {

      //get the data send to the report
      $rude = $request->get('rude');
      $gestion = $request->get('gestion');
      $sie = $request->get('sie');
        //get the values of report
        //create the response object to down load the file
        $response = new Response();
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

    /**
     * libreta tecnica tecnologica
     * @param Request $request
     * @return object libreta
     */
    public function downloadLibretaAction(Request $request) {
        
        $idInscripcion = $request->get('idInscripcion');
        $rude = $request->get('rude');
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        $nivel = $request->get('nivel');
        $grado = $request->get('grado');
        $paralelo = $request->get('paralelo');
        $turno = $request->get('turno');
        $ciclo = $request->get('ciclo');

        /*
            para seleccion de idioma
        */
        $idiomaId = $request->get('idioma');
        if (!isset($idiomaId)) {
            $idiomaId = 48; //castellano        
        }
        
        $em = $this->getDoctrine()->getManager();
        $informacion = $em->createQueryBuilder()
                    ->select('pt.id as periodo, st.id as sucursal')
                    ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                    ->innerJoin('SieAppWebBundle:PeriodoTipo','pt','with','iec.periodoTipo = pt.id')
                    ->innerJoin('SieAppWebBundle:SucursalTipo','st','with','iec.sucursalTipo = st.id')
                    ->where('ei.id = :idInscripcion')
                    ->setParameter('idInscripcion',$idInscripcion)
                    ->getQuery()
                    ->getResult();
        $periodo = $informacion[0]['periodo'];
        $sucursal = $informacion[0]['sucursal'];
        
        //$datos = "2869471|624600252014167A|62460025|2015|11|2|1|1|0";
        $datos = $idInscripcion.'|'.$rude.'|'.$sie.'|'.$gestion.'|'.$nivel.'|'.$grado.'|'.$periodo.'|'.$turno.'|'.$sucursal;

        // Cadena de seguridad
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

        // generanos el link, para codigo QR
        $link = 'http://libreta.minedu.gob.bo/lib/'.$result;

        // Validamos que tipo de libreta se ha de imprimir
        // Modular y plena
        $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);
        
        if($this->session->get('ue_tecteg') == false){
            if( !in_array($this->session->get('roluser'), array(7,8,10)) ){
                $operativo = $operativo - 1;
            }
            if($gestion == 2021 or $gestion == 2022 or $gestion == 2023){
                if($idiomaId == 48)
                {
                    if($gestion >= 2022){
                        switch ($nivel) {
                            case 11: $reporte = 'reg_est_LibretaEscolar_inicial_v5_rcm.rptdesign'; break;
                            case 12: $reporte = 'reg_est_LibretaEscolar_primaria_v5_1_rcm.rptdesign'; break;
                            case 13: $reporte = 'reg_est_LibretaEscolar_secundaria_v5_rcm.rptdesign'; break;
                        }
                    }else{
                        switch ($nivel) {
                            case 11: $reporte = 'reg_est_LibretaEscolar_inicial_v5_rcm.rptdesign'; break;
                            case 12: $reporte = 'reg_est_LibretaEscolar_primaria_v5_rcm.rptdesign'; break;
                            case 13: $reporte = 'reg_est_LibretaEscolar_secundaria_v5_rcm.rptdesign'; break;
                        }
                    }                

                }else{
                    switch ($nivel) {
                        case 11: $reporte = 'reg_est_LibretaEscolar_inicial_v6_rcm.rptdesign'; break;
                        case 12: $reporte = 'reg_est_LibretaEscolar_primaria_v6_rcm.rptdesign'; break;
                        case 13: $reporte = 'reg_est_LibretaEscolar_secundaria_v6_rcm.rptdesign'; break;
                    }
                }
            }else{
                if($gestion == 2019){
                    switch ($nivel) {
                        case 11: $reporte = 'reg_est_LibretaEscolar_inicial_v2_rcm.rptdesign'; break;
                        case 12: $reporte = 'reg_est_LibretaEscolar_primaria_v2_rcm.rptdesign'; break;
                        case 13: $reporte = 'reg_est_LibretaEscolar_secundaria_v2_rcm.rptdesign'; break;

                        /*case 11: $reporte = 'reg_est_LibretaEscolar_inicial_v4_rcm.rptdesign'; break;
                        case 12: 
                            if($grado == 1) {
                                $reporte = 'reg_est_LibretaEscolar_primaria_v4_rcm.rptdesign';
                            } else {
                                $reporte = 'reg_est_LibretaEscolar_primaria_v4_1_rcm.rptdesign';
                            }
                            break;
                        case 13: $reporte = 'reg_est_LibretaEscolar_secundaria_v4_rcm.rptdesign'; break;
                        */
                    }                    

                }else{
                    if($gestion == 2020){
                        switch ($nivel) {
                            case 11: $reporte = 'reg_est_LibretaEscolar_inicial_v4_rcm.rptdesign'; break;
                            case 12: 
                                if($grado == 1) {
                                    $reporte = 'reg_est_LibretaEscolar_primaria_v4_rcm.rptdesign';
                                } else {
                                    $reporte = 'reg_est_LibretaEscolar_primaria_v4_1_rcm.rptdesign';
                                }
                                break;
                            case 13: $reporte = 'reg_est_LibretaEscolar_secundaria_v4_rcm.rptdesign'; break;
                        }
                    } else {
                        if($gestion == $this->session->get('currentyear') and $operativo >= 1 and $operativo <= 3){
                            switch ($nivel) {
                                case 11: $reporte = 'reg_est_LibretaEscolar_inicial_b'.$operativo.'_v1_rcm.rptdesign'; break;
                                case 12: $reporte = 'reg_est_LibretaEscolar_primaria_b'.$operativo.'_v1_rcm.rptdesign'; break;
                                case 13: $reporte = 'reg_est_LibretaEscolar_secundaria_b'.$operativo.'_v1_rcm.rptdesign'; break;
                            }
                        }else{
                            switch ($nivel) {
                                case 11: $reporte = 'reg_est_LibretaEscolar_inicial_v1_rcm.rptdesign'; break;
                                case 12: $reporte = 'reg_est_LibretaEscolar_primaria_v1_rcm.rptdesign'; break;
                                case 13:
                                        if($sie == '80730460'){
                                            if($gestion == 2014 and $nivel == 13 and $grado >= 4 and $paralelo >= 6){
                                                $reporte = 'reg_est_CertificadoNotas_UnidadesEducativasTecnologicas2016_v1_ivg.rptdesign';
                                            }else{
                                                if($sie == '80730460' and $gestion == 2015 and $nivel == 13 and $grado >= 5 and $paralelo >= 6){
                                                    $reporte = 'reg_est_CertificadoNotas_UnidadesEducativasTecnologicas2016_v1_ivg.rptdesign';
                                                }else{
                                                    if($sie == '80730460' and $gestion == 2016 and $nivel == 13 and $grado >= 6 and $paralelo >= 6){
                                                        $reporte = 'reg_est_CertificadoNotas_UnidadesEducativasTecnologicas2016_v1_ivg.rptdesign';
                                                    }else{
                                                        $reporte = 'reg_est_LibretaEscolar_secundaria_v1_rcm.rptdesign';
                                                    }
                                                }
                                            }
                                        }else{
                                            $reporte = 'reg_est_LibretaEscolar_secundaria_v1_rcm.rptdesign';
                                        }
                                        break;
                            }
                        }
                    }                    

                }

            }
        }else{
            // Tecnica tecnologica
            if($this->session->get('ue_tecteg') == true){
                if($gestion >= 2009 and $gestion <= 2013){
                    $reporte = 'reg_est_CertificadoNotas_UnidadesEducativasTecnologicas2013_trimestral_v1_ivg.rptdesign';
                }else{
                    $reporte = 'reg_est_CertificadoNotas_UnidadesEducativasTecnologicas2016_v1_ivg.rptdesign';
                }
            }
        }
        // dump($this->container->getParameter('urlreportweb').$reporte.'&inscripid=' . $idInscripcion .'&codue=' . $sie .'&lk=' . $link . '&&__format=pdf&');die;
        // Generamos el reporte

        $response = new Response();
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'libreta_' . $rude . '_' . $gestion . '.pdf'));
        
        if($gestion == 2021 or $gestion == 2022 or $gestion == 2023){
            // dump($idiomaId);
            // dump($reporte);
            // dump($idInscripcion); die;
                if($idiomaId == 48)
                {   
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb').$reporte.'&inscripid=' . $idInscripcion .'&codue=' . $sie .'&lk=' . $link . '&trimestre=9&&__format=pdf&'));
                }else{
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb').$reporte.'&inscripid=' . $idInscripcion .'&codue=' . $sie .'&lk=' . $link . '&trimestre=9&idioma='.$idiomaId.'&&__format=pdf&'));
                }
        }else{

            if($gestion == 2019){
                // VERIFICAMOS SI EL CURSO ES DE SEXTO DE SECUNDARIA Y CERRO OPERATIVO DE SEXTO 
                // PARA IMPRIMIR LIBRETA COMO OPERATIVO CERRADO HASTA 4TO BIMESTRE
                if($operativo == 3 and $nivel == 13 and $grado == 6){
                    $validacionSexto = $this->get('funciones')->verificarSextoSecundariaCerrado($sie, $gestion);
                    if($validacionSexto){
                        $operativo = 4;
                    }
                }
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb').$reporte.'&inscripid=' . $idInscripcion .'&codue=' . $sie .'&lk=' . $link . '&bimestre=' . $operativo . '&&__format=pdf&'));
            }else{
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb').$reporte.'&inscripid=' . $idInscripcion .'&codue=' . $sie .'&lk=' . $link . '&&__format=pdf&'));
            }            

        }

        
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * build the CUT report - cut report download pdf
     * @param Request $request
     * @return object cut report
     */
    public function downloadCutAction(Request $request) {

        $idInscripcion = $request->get('idInscripcion');
        $rude = $request->get('rude');
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        $nivel = $request->get('nivel');
        $grado = $request->get('grado');
        $paralelo = $request->get('paralelo');
        $turno = $request->get('turno');
        $ciclo = $request->get('ciclo');

        $em = $this->getDoctrine()->getManager();
        $informacion = $em->createQueryBuilder()
                    ->select('IDENTITY(eiht.especialidadTecnicoHumanisticoTipo) as especialidad')
                    ->from('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico','eiht')
                    ->where('eiht.estudianteInscripcion = :idInscripcion')
                    ->setParameter('idInscripcion',$idInscripcion)
                    ->getQuery()
                    ->getResult();
        $especialidad = $informacion[0]['especialidad'];
        

        //$datos = "2869471|624600252014167A|62460025|2015|11|2|1|1|0";
        $datos = $idInscripcion.'|'.$rude.'|'.$sie.'|'.$turno.'|'.$grado.'|'.$paralelo.'|'.$especialidad.'|'.$gestion;

        // Cadena de seguridad
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

        // generanos el link, para codigo QR
        $link = 'http://academico.sie.gob.bo/bth/'.$result;

        // Validamos que tipo de libreta se ha de imprimir
        // Modular y plena

        //if($this->session->get('ue_plena')){
           $reporte = 'reg_cert_cut_v1_ma.rptdesign';
        //}
        //dump($this->container->getParameter('urlreportweb').$reporte.'&inscripid=' . $idInscripcion .'&codue=' . $sie .'&lk=' . $link . '&&__format=pdf&');die;
        // Generamos el reporte

        $response = new Response();
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'cut_' . $rude . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb').$reporte.'&rude=' . $rude.'&inscripcion=' . $idInscripcion .'&lk=' . $link . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * build the DDJJ SPECIALITY - student report download pdf
     * @param Request $request
     * @return object form login
     */
    public function ddjjSpecialityStudentAction(Request $request) {
        //get the values to build the report
        $idInscripcion = $request->get('idInscripcion');
        $rude = $request->get('rude');
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        $nivel = $request->get('nivel');
        $grado = $request->get('grado');
        $paralelo = $request->get('paralelo');
        $turno = $request->get('turno');
        $ciclo = $request->get('ciclo');

        //get the values of report
        //create the response object to down load the file
        $response = new Response();
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'libreta_tecnica_' . $rude . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'dpl_dj_DeclaracionJurada_EstudianteTecnicoMedio_gral_v1.rptdesign&rude=' . $rude .'&gestion=' . $gestion .'&sie=' . $sie . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * build the DDJJ list students no acreditados - student report download pdf
     * @param Request $request
     * @return object form login
     */
    public function listStudentsNoAcreditadosAction(Request $request) {
        //get the values to build the report
        $form = $request->get('dataInfo');
        $dataInfo = $request->get('dataInfo');
        $arrDataInfo = json_decode($dataInfo, true);

        //get the values of report
        //create the response object to down load the file
        $response = new Response();
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'libreta_tecnica_' . $arrDataInfo['sie'] . '_' . $this->session->get('currentyear') . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesInscritos_UnidadEducativa_procesoApertura_v1.rptdesign&ue=' . $arrDataInfo['sie'] .'&gestion=' . $this->session->get('currentyear') . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * DESCARGA DE RUDE - ACADEMICO
     */
    public function downloadRudeAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        //get the data send to the report
        //get the values to build the report
        $codue = $request->get('codue');
        $rude = $request->get('rude');
        $gestion = $request->get('gestion');
        $eins = $request->get('eins');

        $socioregIdEntity = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegular')->findOneByEstudianteInscripcion($eins);

        $idlocalidad = $socioregIdEntity->getSeccionivLocalidadTipo()->getId();

        $query = "select socioeconomico_lugar_recursivo(".$idlocalidad.");";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
        $countdir = count($po);

        $porciones = explode("|", $po[0]['socioeconomico_lugar_recursivo']);

        $dirProv = $porciones[3];
        $dirMun = $porciones[4];
        //get the values of report
        //create the response object to down load the file
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'RUDE_' .$rude. '_' .$gestion. '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rude2017.rptdesign&codue=' . $codue .'&rude='. $rude .'&gestion=' . $gestion .'&eins='. $eins .'&dirMun='. $dirMun .'&dirProv='. $dirProv . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * download the list of ue that close the rude operative
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function reportRudeOperatvioAction(Request $request) {
        //get the values to build the report
        $codDistrito = base64_decode($request->get('codDistrito'));

        //get the values of report
        //create the response object to down load the file
        $response = new Response();
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'libreta_tecnica_' . $codDistrito . '_' . $this->session->get('currentyear') . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'siged_rep_rude_porDistrito_v1_afv.rptdesign&codigo_distrito=' . $codDistrito . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * download reporte gis
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function downloadReporteGisAction(Request $request) {
        //get the values to build the report
        $codEdificio = $request->get('codEdificio');

        $em = $this->getDoctrine()->getManager();
        $jurisdiccion = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->find($codEdificio);
        $jurisdiccion->setValidacionGeograficaTipo($em->getRepository('SieAppWebBundle:ValidacionGeograficaTipo')->find(1));
        $jurisdiccion->setFechaRegistro(new \DateTime('now'));
        $em->persist($jurisdiccion);
        $em->flush();

        //get the values of report
        //create the response object to down load the file
        $response = new Response();
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'reporte_gis_' . $codEdificio . '_' . $this->session->get('currentyear') . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rue_dj_LocalizacionGeografica_EdificioEducativo_v1_rcm.rptdesign&id=' . $codEdificio . '&&__format=pdf&'));

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }



    /**
     * get the list of students per UE
     * @param Request $request
     * @param type $ue
     * @param type $gestion
     * @return Response a pdf list students per UE
     * by krlos
     */
    public function especialLibretaAction(Request $request, $infoUe, $gestion, $estInsEspId, $estInsId, $codigoRude) {
 

      $arrInfoUe = unserialize($infoUe);

        // $aDataUe = explode(',', $itemsUe);

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'libreta_especial_' . $arrInfoUe['requestUser']['sie'] . '_' . $arrInfoUe['ueducativaInfoId']['nivelId'] . '_' . trim($arrInfoUe['ueducativaInfoId']['cicloId']) . '_' . trim($arrInfoUe['ueducativaInfoId']['gradoId']) . '_' . $gestion . '.pdf'));
        //get the data to do the report
        $user= $this->session->get('userName');//strtolower(str_replace(' ','_', $this->session->get('name').' '.$this->session->get('lastname').' '.$this->session->get('lastname2')));
        $sie = $arrInfoUe['requestUser']['sie'];

        $data = $estInsId .'|'. $codigoRude .'|'.$arrInfoUe['requestUser']['sie'].'|'.$arrInfoUe['requestUser']['gestion'].'|'.$arrInfoUe['ueducativaInfoId']['nivelId'].'|'.$arrInfoUe['ueducativaInfoId']['gradoId'].'|'.$arrInfoUe['ueducativaInfoId']['cicloId'].'|'.$arrInfoUe['ueducativaInfoId']['turnoId'].'|'.$arrInfoUe['ueducativaInfoId']['paraleloId'].'|'.$estInsEspId;

        $link = 'http://libreta.minedu.gob.bo/lib/'.$this->getLinkEncript($data);

        $report = $this->container->getParameter('urlreportweb') .  'esp_est_LibretaEscolar_intelectual_v1_jaq.rptdesign&inscripid=' . $estInsId . '&codue=' . $sie .'&lk='. $link . '&&__format=pdf&';
// dump($report);die;
        $response->setContent(file_get_contents($report));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function repFinProcesoAperturaAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $idLugar = $request->get('idLugar');
        $gestion = $em->getRepository('SieAppWebBundle:GestionTipo')->find($request->get('gestion'));
        $lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idLugar);

        $distrito = $em->getRepository('SieAppWebBundle:DistritoTipo')->find(intval($lugar->getCodigo()));

        $bjpValidacion = $em->getRepository('SieAppWebBundle:BjpValidacionueProcesoApertura')->findBy(array('distritoTipo' => intval($lugar->getCodigo()), 'gestionTipo' => $gestion));

        if(!$bjpValidacion){
            $bjpValidacion = new BjpValidacionueProcesoApertura();
            $bjpValidacion->setGestionTipo($gestion);
            $bjpValidacion->setDistritoTipo($distrito);
            $bjpValidacion->setEstado(1);
            $bjpValidacion->setFechaRegistro(new \DateTime('now'));
            $bjpValidacion->setFechaModificacion(new \DateTime('now'));
            $em->persist($bjpValidacion);
            $em->flush();
        }

        $arch = 'REPORTE_FinProcApertura_'.$lugar->getLugar().'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_esp_lst_distrital_proc_insc_v1_ma.rptdesign&gestion='.$gestion.'&distritoid='.intval($lugar->getCodigo()).'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function repProcesoAperturaAction(Request $request) {
        $idLugar = $request->get('idLugar');
        $gestion = $request->get('gestion');
        $roluser = $request->get('roluser');

        $em = $this->getDoctrine()->getManager();
        $lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idLugar);

        $arch = 'REPORTE_ProcApertura_'.$lugar->getLugar().'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        switch ($roluser) {
            case 7:
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_esp_lst_departamental_proc_insc_v1_ma.rptdesign&gestion='.$gestion.'&dpto='.intval($lugar->getCodigo()).'&&__format=pdf&'));
                break;
            case 8:
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_esp_lst_nacional_proc_insc_v1_ma.rptdesign&gestion='.$gestion.'&&__format=pdf&'));
                break;
            case 10:
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_esp_lst_distrital_proc_insc_v1_ma.rptdesign&gestion='.$gestion.'&distritoid='.intval($lugar->getCodigo()).'&&__format=pdf&'));
                break;
        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function repProcesoAperturaDdeAction(Request $request) {
        $idLugar = $request->get('idLugar');
        $gestion = $request->get('gestion');
        $roluser = $request->get('roluser');

        $em = $this->getDoctrine()->getManager();
        $lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idLugar);

        $arch = 'REPORTE_ListaPersonal_DDE_'.$lugar->getLugar().'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        switch ($roluser) {
            case 7:
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_departamental_personal_dde_v1_ma.rptdesign&gestion='.$gestion.'&dpto='.intval($lugar->getCodigo()).'&&__format=pdf&'));
                break;
            case 8:
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_nacional_personal_dde_v1_ma.rptdesign&gestion='.$gestion.'&&__format=pdf&'));
                break;
        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function buildArchsOlimpiadasTxtAction(Request $request, $gestion) {
        set_time_limit(500);

        $em = $this->getDoctrine()->getManager();
        $gestion = $gestion;
        $directorio = $this->get('kernel')->getRootDir().'/../web/empfiles/';
        $archivo = "archsOlimpiadasTxt.zip";

        // Generamos Archivo
        $query = $em->getConnection()->prepare("select * from sp_genera_archs_olimpiadas_txt('".$gestion."')");
        $query->execute();
        $result = $query->fetchAll();
        $porciones = explode(";", $result[0]['sp_genera_archs_olimpiadas_txt']);

        system('zip '.$directorio.$archivo.' '.$directorio.$porciones[0].' '.$directorio.$porciones[1]);
        system('rm '.$directorio.$porciones[0]);
        system('rm '.$directorio.$porciones[1]);

        $response = new Response();
        return $response;
    }

    public function downloadArchsOlimpiadasTxtAction(Request $request) {
        $directorio = $this->get('kernel')->getRootDir().'/../web/empfiles/';
        $archivo = "archsOlimpiadasTxt.zip";

        //create response to donwload the file
        $response = new Response();
        //then send the headers to foce download the zip file
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $archivo));
        $response->setContent(file_get_contents($directorio) . $archivo);
        $response->headers->set('Pragma', "no-cache");
        $response->headers->set('Expires', "0");
        $response->headers->set('Content-Transfer-Encoding', "binary");
        $response->sendHeaders();
        $response->setContent(readfile($directorio . $archivo));

        system('rm '.$directorio.$archivo);

        return $response;
    }

    //ROBÓTICA
    public function buildArchsOlimpiadasRoboticaTxtAction(Request $request, $gestion) {
        set_time_limit(500);

        $em = $this->getDoctrine()->getManager();
        $gestion = $gestion;
        $directorio = $this->get('kernel')->getRootDir().'/../web/empfiles/';
        $archivo = "archsOlimpiadasRoboticaTxt.zip";

        // Generamos Archivo
        $query = $em->getConnection()->prepare("select * from sp_genera_archs_olimpiadas_robotica_txt('".$gestion."')");
        $query->execute();
        $result = $query->fetchAll();
        $porciones = explode(";", $result[0]['sp_genera_archs_olimpiadas_robotica_txt']);

        system('zip '.$directorio.$archivo.' '.$directorio.$porciones[0]);
        system('rm '.$directorio.$porciones[0]);

        $response = new Response();
        return $response;
    }

    public function downloadArchsOlimpiadasRoboticaTxtAction(Request $request) {
        $directorio = $this->get('kernel')->getRootDir().'/../web/empfiles/';
        $archivo = "archsOlimpiadasRoboticaTxt.zip";

        //create response to donwload the file
        $response = new Response();
        //then send the headers to foce download the zip file
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $archivo));
        $response->setContent(file_get_contents($directorio) . $archivo);
        $response->headers->set('Pragma', "no-cache");
        $response->headers->set('Expires', "0");
        $response->headers->set('Content-Transfer-Encoding', "binary");
        $response->sendHeaders();
        $response->setContent(readfile($directorio . $archivo));

        system('rm '.$directorio.$archivo);

        return $response;
    }

      /**
     * get the studens per course
     * @param Request $request
     * @param type $ue
     * @return Response pdf list with studens from OCEPB
     */
    public function listainscritosestudiantesolimAction(Request $request, $codges, $coddis) {
// dump($codges);die;
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'list_est_' . $coddis . '_' . $codges . '.xls'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'oli_lst_Estudiantes_Participaciones_Distrito_f1_v1.rptdesign&codges=' . $codges . '&coddis=' . $coddis . '&&__format=xls&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }

    /**
     * Descarga de CUt para bachilleres con tecnica tecnologica
     * @param Request $request
     * @return object CUT
     */
    // public function downloadCutAction(Request $request) {

    //     $idInscripcion = $request->get('idInscripcion');

    //     /**
    //      * completar con la consulta si es que hubiera
    //      * por el momento solo mandamos el id de inscripcion
    //      * cambiar el nombre del reporte
    //      */

    //     $response = new Response();
    //     $response->headers->set('Content-type', 'application/pdf');
    //     $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'CUT_' . $rude . '_' . $gestion . '.pdf'));
    //     $response->setContent(file_get_contents($this->container->getParameter('urlreportweb').'reporte_cut.rptdesign&inscripid=' . $idInscripcion . '&&__format=pdf&'));
    //     $response->setStatusCode(200);
    //     $response->headers->set('Content-Transfer-Encoding', 'binary');
    //     $response->headers->set('Pragma', 'no-cache');
    //     $response->headers->set('Expires', '0');
    //     return $response;
    // }
    

    /**
     * get DDJJJ per UE
     * @param Request $request
     * @param type $gestion
     * @param type $sie
     * @return Response
     */
    public function nnatsAction(Request $request, $gestion, $sie) {
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'nnats_' . $sie . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_centro_nnats_v1_ma.rptdesign&gestion=' . $gestion . '&sie=' . $sie . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function tramiteModificacionCalificacionesAction(Request $request, $idTramite) {
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'tramite_modificacion_calificaciones_' . $idTramite . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_dj_tramite_modificacion_calificaciones_ue_gral_v1_jqc.rptdesign&id_tramite=' . $idTramite . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function downstudentHistModificationAction(Request $request, $id, $studentId){
        $response = new Response();

        $data = $this->session->get('userId').'|'.$this->session->get('currentyear').'|'.$id.'|'.$studentId;
        $link = 'http://'.$_SERVER['SERVER_NAME'].'/sie/'.$this->getLinkEncript($data);


        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'mod_student_'.$id.'_'.$studentId. '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_modificacion_datos_v2_pvc.rptdesign&id=' . $id.'&estudiante_id='. $studentId.'&lk='. $link . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
   

     public function bthEspecialidadesAction(Request $request, $ue, $gestion,$gradoId) {
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'list_espe_mod_' . $ue . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_especialidades_bth_modificacion_v1_ma.rptdesign&ue=' . $ue . '&gestion=' . $gestion . '&gradoId='.$gradoId. '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    public function bthEspecialidadesEliminadasAction(Request $request, $ue, $gestion,$gradoId){
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'list_espe_elim_' . $ue . '_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_especialidades_bth_eliminacion_v1_ma.rptdesign&ue=' . $ue . '&gestion=' . $gestion . '&gradoId='.$gradoId. '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    /*
    report nivelation bth per course
    */
    public function boletinNivelacionBthAction(Request $request, $iecId, $codue){

        $response = new Response();

        $data = $this->session->get('userId').'|'.$this->session->get('currentyear').'|'.$iecId.'|'.$codue;
        $link = 'http://'.$_SERVER['SERVER_NAME'].'/sie/'.$this->getLinkEncript($data);

        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'bth_niv_'.$codue.'_'.$iecId. '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesBoletinCentralizadorNivelacionBTH_TTG_v1_ma.rptdesign&inscripid=' . $iecId.'&codue='. $codue.'&lk='. $link . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }
    
    /*
    report nivelation bth ttg per course
    */
    public function boletinNivelacionTTGBthAction(Request $request, $iecId, $codue){

        $response = new Response();

        $data = $this->session->get('userId').'|'.$this->session->get('currentyear').'|'.$iecId.'|'.$codue;
        $link = 'http://'.$_SERVER['SERVER_NAME'].'/sie/'.$this->getLinkEncript($data);

        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'bth_niv_'.$codue.'_'.$iecId. '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesBoletinCentralizadorNivelacionBTH_TTG_v1_ma.rptdesign&inscripid=' . $iecId.'&codue='. $codue.'&lk='. $link . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }

    /*
    report nivelation bth ttg per course
    */
    public function boletinNivelacionTTEBthAction(Request $request, $iecId, $codue){

        $response = new Response();

        $data = $this->session->get('userId').'|'.$this->session->get('currentyear').'|'.$iecId.'|'.$codue;
        $link = 'http://'.$_SERVER['SERVER_NAME'].'/sie/'.$this->getLinkEncript($data);

        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'bth_niv_'.$codue.'_'.$iecId. '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesBoletinCentralizadorNivelacionBTH_TTE_v1_ma.rptdesign&inscripid=' . $iecId.'&codue='. $codue.'&lk='. $link . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }    

    public function listadoEstudiantesNivelacionAction(Request $request, $sie, $gestion){

        $data = $this->session->get('userId').'|'.$sie.'|'.$gestion;
        $link = 'http://'.$_SERVER['SERVER_NAME'].'/sie/'.$this->getLinkEncript($data);
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'list_est_nivelacion_' . $sie . '_' . $gestion . '.pdf'));

        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesBoletinCentralizadorNivelacionBTH_TTE_v1_ma.rptdesign&codue=' .$sie. '&lk='.$link.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    } 
    public function listadoEstudiantesNivelacionttgAction(Request $request, $sie, $gestion){

        $data = $this->session->get('userId').'|'.$sie.'|'.$gestion;
        $link = 'http://'.$_SERVER['SERVER_NAME'].'/sie/'.$this->getLinkEncript($data);
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'list_est_nivelacion_' . $sie . '_' . $gestion . '.pdf'));

        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesBoletinCentralizadorNivelacionBTH_TTG_v1_ma.rptdesign&codue=' .$sie. '&lk='.$link.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

    }

    public function ddjjStudentsQuipusAction(Request $request, $sie){

        $response = new Response();

        $data = $this->session->get('userId').'|'.$this->session->get('currentyear').'|'.$sie;
        $link = 'http://'.$_SERVER['SERVER_NAME'].'/sie/'.$this->getLinkEncript($data);

        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'students_quipus_'.$sie.'_'.$this->session->get('currentyear'). '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_dj_EstudianteQuipus_unidadeducativa_regular_v1_afv.rptdesign&codue=' . $sie.'&gestion='. $this->session->get('currentyear').'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    //Reporte para el formulario RUDE funcionalidad añadida el 26 de febrero del 2021
    /**
     * libreta tecnica tecnologica
     * @param Request $request
     * @return object libreta
     */
    public function downloadFormularioRudeAction(Request $request)
    {
        $idInscripcion = $request->get('idInscripcion');
        $rude = $request->get('rude');
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        $nivel = $request->get('nivel');
        $grado = $request->get('grado');
        $paralelo = $request->get('paralelo');
        $turno = $request->get('turno');
        $ciclo = $request->get('ciclo');

        $em = $this->getDoctrine()->getManager();
        $informacion = $em->createQueryBuilder()
                    ->select('r.id as rude_id')
                    ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                    ->innerJoin('SieAppWebBundle:Rude','r','with','ei.id = r.estudianteInscripcion')
                    ->where('ei.id = :idInscripcion')
                    ->setParameter('idInscripcion',$idInscripcion)
                    ->getQuery()
                    ->getResult();
        //$periodo = $informacion[0]['periodo'];
        $rude_id=-1;
        if($informacion)
        {
            $rude_id=$informacion[0]['rude_id'];
        }
        
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'Reporte-Formulario-Rude' .'.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_rude1172_2023_v1_igg.rptdesign&rude_id=' . $rude_id . '&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    public function calificacionesFormularioAction(Request $request, $idTramite, $codigoQR){

        $response = new Response();

        $data = $this->session->get('userId').'|'.$this->session->get('currentyear').'|'.$idTramite;
        $link = 'http://'.$_SERVER['SERVER_NAME'].'/sie/'.$this->getLinkEncript($codigoQR);

        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'students_quipus_'.$sie.'_'.$this->session->get('currentyear'). '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_dj_EstudianteQuipus_unidadeducativa_regular_v1_afv.rptdesign&codue=' . $sie.'&gestion='. $this->session->get('currentyear').'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }    

    public function requestInsCalYearOldAction(Request $request, $idStudent,$sie,$idTramite, $codigoRude){

        $response = new Response();

        $codigoQR = 'FICGP'.$idTramite.'|'.$codigoRude.'|'.$sie.'|'.$gestion;

        $data = $this->session->get('userId').'|'.$this->session->get('currentyear').'|'.$idTramite;
        //$link = 'http://'.$_SERVER['SERVER_NAME'].'/sie/'.$this->getLinkEncript($codigoQR);
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'requestProcess'.$sie.'_'.$this->session->get('currentyear'). '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_cert_cal_solicitud_tramite_V1_eee.rptdesign&estudiante_id=' . $idStudent.'&institucioneducativa_id='. $sie.'&tramite_id='.$idTramite.' &&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }   

    public function downloadVulnerabilityAction(Request $request, $gestion, $distrito){

        $response = new Response();

        // $codigoQR = 'FICGP'.$idTramite.'|'.$codigoRude.'|'.$sie.'|'.$gestion;

        $data = $this->session->get('userId').'|'.$this->session->get('currentyear').'|'.$distrito;
        //$link = 'http://'.$_SERVER['SERVER_NAME'].'/sie/'.$this->getLinkEncript($codigoQR);
        
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'requestVul_'.$distrito.'_'.$gestion. '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_est_listado_quipus_V1_eea.rptdesign&distrito_id=' . $distrito.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }   


           
}
