<?php

namespace Sie\EspecialBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;

/**
 * areas especial controller.
 *
 */
class InfoNotasController extends Controller {

    public $session;
    public $idInstitucion;

    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Formulario para el registro de notas - modal
     */
    public function indexAction(Request $request) {
        try {
            //dump($request);die;
            $idInscripcionEspecial = $request->get('idInscripcionEspecial');
            $em = $this->getDoctrine()->getManager();
            $inscripcionEspecial = $em->getRepository('SieAppWebBundle:EstudianteInscripcionEspecial')->find($idInscripcionEspecial);

            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($inscripcionEspecial->getEstudianteInscripcion()->getId());
            $idInscripcion = $inscripcion->getId();

            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcion->getInstitucioneducativaCurso()->getId());
            $cursoEspecial = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBy(array('institucioneducativaCurso'=>$curso->getId()));

            $sie = $curso->getInstitucioneducativa()->getId();
            $gestion = $curso->getGestionTipo()->getId();
            $nivel = $curso->getNivelTipo()->getId();
            $grado = $curso->getgradoTipo()->getId();
            $gestionActual = $this->session->get('currentyear');

            $operativo = $this->operativo($sie,$gestion);

            $notas = null;
            $vista = 1;
            $discapacidad = $cursoEspecial->getEspecialAreaTipo()->getId();
            //dump($discapacidad);die;
            $estadosMatricula = null;
            switch ($discapacidad) {
                case 1: // Auditiva
                        //if($nivel != 405){
                        if($nivel == 403 or $nivel == 404){
                            $notas = $this->get('notas')->regular($idInscripcion,$operativo);
                            if($notas['tipoNota'] == 'Trimestre'){
                                $template = 'trimestral';
                            }else{
                                $template = 'regular';
                            }
                            $actualizarMatricula = true;
                            if(in_array($nivel, array(1,11,403))){
                                $actualizarMatricula = false;
                            }
                            if($operativo >= 4 or $gestion < $gestionActual){
                                $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(5,11)));
                            }
                        }elseif(in_array($nivel, array(410,411))){
                            $notas = $this->get('notas')->especial_seguimiento($idInscripcion,$operativo);
                            $template = 'especialSeguimiento';
                            $actualizarMatricula = false;
                            if($operativo >= 4 or $gestion < $gestionActual){
                                $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(48,77)));
                            }
                        }
                        break;
                case 2: // Visual
                        $programa = $cursoEspecial->getEspecialProgramaTipo()->getId();
                        if($nivel == 411 and in_array($programa, array(7,8,9,12,14,15))){
                            if($notas['tipoNota'] == 'Trimestre'){
                                $notas = $this->get('notas')->especial_cualitativo($idInscripcion,$operativo);
                                $template = 'especialCualitativoTrimestral';
                            }else{
                                if($gestion < 2019){
                                    $notas = $this->get('notas')->especial_cualitativo($idInscripcion,$operativo);
                                    $template = 'especialCualitativo';
                                }else{
                                    $notas = $this->get('notas')->especial_cualitativo_visual($idInscripcion,$operativo);
                                    $template = 'especialCualitativoVisual';
                                }
                            }
                            $actualizarMatricula = true;
                            if($operativo >= 4 or $gestion < $gestionActual){
                                $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(70,71,72,73)));
                            }
                        }elseif($nivel <> 405){
                            $notas = $this->get('notas')->especial_seguimiento($idInscripcion,$operativo);
                            $template = 'especialSeguimiento';
                            $actualizarMatricula = false;
                            if($operativo >= 4 or $gestion < $gestionActual){
                                $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(48,77)));
                            }
                        }
                        
                        break;
                case 3: //Intelectual
                case 5: //Multiple
                        switch ($nivel) {
                            case 401:
                            case 402:
                                if($grado <= 6){
                                    $notas = $this->get('notas')->especial_cualitativo($idInscripcion,$operativo);
                                    $template = 'especialCualitativo';
                                    $actualizarMatricula = false;
                                    if($operativo >= 4 or $gestion < $gestionActual){
                                        $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(70,71,72,73)));
                                    }
                                    if($notas['tipoNota'] == 'Trimestre'){
                                        $template = 'especialCualitativoTrimestral';
                                    }else{
                                        if($gestion < 2019){
                                            $template = 'especialCualitativo';
                                        }else{
                                            $template = 'especialCualitativo1';
                                        }
                                    }
                                }
                                break;
                            case 410:  // Programas
                            case 411:  //Servicios
                                $notas = $this->get('notas')->especial_seguimiento($idInscripcion,$operativo);
                                $template = 'especialSeguimiento';
                                $actualizarMatricula = false;
                                if($operativo >= 4 or $gestion < $gestionActual){
                                    $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(48,77)));
                                }
                        }
                        break;
                case 4: // Fisico -motora
                case 6: //Dificultades en el aprendizaje
                case 7: // Talento extraordinario
                    $notas = $this->get('notas')->especial_seguimiento($idInscripcion,$operativo);
                    $template = 'especialSeguimiento';
                    $actualizarMatricula = false;
                    if($operativo >= 4 or $gestion < $gestionActual){
                        $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(48,77)));
                    }
                    break;
                case 8: // Sordeceguera
                        break;

                case 9: // Problemas emocionales
                        break;
                case 100: // Modalidad Indirecta
                        break;
            }
            //dump($notas);die;
            if($notas){
                return $this->render('SieEspecialBundle:InfoNotas:notas.html.twig',array('notas'=>$notas,'inscripcion'=>$inscripcion,'vista'=>$vista,'template'=>$template,'actualizar'=>$actualizarMatricula,'operativo'=>$operativo,'estadosMatricula'=>$estadosMatricula,'discapacidad'=>$discapacidad));
            }else{
                return $this->render('SieEspecialBundle:InfoNotas:notas.html.twig',array('iesp'=>$inscripcionEspecial));
            }

        } catch (Exception $e) {
            return null;
        }
    }

    public function createUpdateAction(Request $request){
        try {
            $idInscripcion = $request->get('idInscripcion');
            $discapacidad = $request->get('discapacidad');
            $em = $this->getDoctrine()->getManager();
            // Registramos las notas
            $gestion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion)->getInstitucioneducativaCurso()->getGestionTipo()->getId();
            if($discapacidad == 2 and $gestion > 2018){
                $this->get('notas')->especialVisualRegistro($request,$discapacidad);    
            }else{
                $this->get('notas')->especialRegistro($request,$discapacidad);
            }
            // Verificamos si se actualizara el estado de matrícula
            if($request->get('actualizar') == true){
                $this->get('notas')->actualizarEstadoMatricula($idInscripcion);
            }
            
            // Actualizar estado de matricula de los notas que son cualitativas siempre 
            // y cuando esten en el cuarto bimestre
            if(($request->get('operativo') >= 4 and $request->get('actualizar') == false) or ($discapacidad == 4 and $request->get('actualizar') == false) or ($discapacidad == 6 and $request->get('actualizar') == false) or ($discapacidad == 7 and $request->get('actualizar') == false)){
                $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
                $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($request->get('nuevoEstadomatricula')));
                $em->flush();
            }

            return new JsonResponse(array('msg'=>'ok'));
        } catch (Exception $e) {
            return new JsonResponse(array('msg'=>'error'));
        }
    }

    public function operativo($sie,$gestion){
        $em = $this->getDoctrine()->getManager();
        // Obtenemos el operativo para bloquear los controles
        $registroOperativo = $em->createQueryBuilder()
                        ->select('rc.bim1,rc.bim2,rc.bim3,rc.bim4')
                        ->from('SieAppWebBundle:RegistroConsolidacion','rc')
                        ->where('rc.unidadEducativa = :ue')
                        ->andWhere('rc.gestion = :gestion')
                        ->setParameter('ue',$sie)
                        ->setParameter('gestion',$gestion)
                        ->getQuery()
                        ->getResult();
        //dump($registroOperativo);die;
        if(!$registroOperativo){
            // Si no existe es operativo inicio de gestion
            $operativo = 0;
        }else{
            if($registroOperativo[0]['bim1'] == 0 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 1; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 2; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 3; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 4; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 and $registroOperativo[0]['bim4'] >= 1){
                $operativo = 5; // Fin de gestion o cerrado
            }
        }
        return $operativo;
    }

    public function especialEtapasVisualAction(Request $request){
        
        $arrInfoUe = unserialize($request->get('infoUe'));
        $arrInfoStudent = json_decode($request->get('infoStudent'),true);
        //dump($arrInfoStudent,$arrInfoUe);die;
        $sie = $arrInfoUe['requestUser']['sie'];
        $estInsId = $arrInfoStudent['estInsId'];
        $em = $this->getDoctrine()->getManager();
        
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($estInsId);
        
        $query = $em->getConnection()->prepare("select enc.id as id_nota_cualitativa,nota_tipo_id,nota_cualitativa::json->>'etapa' as etapa, emt.estadomatricula,'Del '|| split_part(nota_cualitativa::json->>'fechaEtapa', '-', 1)||' al '||split_part(nota_cualitativa::json->>'fechaEtapa', '-', 2) as fecha_etapa
                                                from estudiante_nota_cualitativa enc
                                                join estadomatricula_tipo emt on (enc.nota_cualitativa::json->>'estadoEtapa')::INTEGER=emt.id
                                                WHERE estudiante_inscripcion_id=". $estInsId ."
                                                ORDER BY nota_tipo_id");
        $query->execute();
        $etapas = $query->fetchAll();
        
        //dump($etapas);die;
        if($inscripcion){
            return $this->render('SieEspecialBundle:InfoNotas:etapas.html.twig',array('inscripcion'=>$inscripcion,'ueducativaInfo'=>$arrInfoUe['ueducativaInfo'],'etapas'=>$etapas,'infoUe'=>$request->get('infoUe'),'infoStudent'=>$request->get('infoStudent')));
        }else{
            return $this->render('SieEspecialBundle:InfoNotas:etapas.html.twig',array('inscripcion'=>$inscripcion));
        }
    }

    public function especialDownloadLibretaAction(Request $request){
        
        $arrInfoUe = unserialize($request->get('infoUe'));
        $arrInfoStudent = json_decode($request->get('infoStudent'),true);

        //dump($arrInfoStudent);die;
        $sie = $arrInfoUe['requestUser']['sie'];
        $estInsId = $arrInfoStudent['estInsId'];
        $areaEspecialId = $arrInfoUe['ueducativaInfoId']['areaEspecialId'];

        switch ($areaEspecialId){
            case 2:
                $idNotaTipo = $request->get('idNotaTipo');
                //dump($idNotaTipo);die;
                $archivo = "esp_est_LibretaEscolar_Visual_v2_pvc.rptdesign";
                $nombre = 'libreta_especial_visual_' . $arrInfoUe['requestUser']['sie'] . '_' . $arrInfoUe['ueducativaInfoId']['nivelId'] . '_' . $arrInfoUe['requestUser']['gestion'] . '.pdf';
                $data = $arrInfoStudent['estInsId'] .'|'. $arrInfoStudent['codigoRude'] .'|'.$arrInfoUe['requestUser']['sie'].'|'.$arrInfoUe['requestUser']['gestion'].'|'.$arrInfoUe['ueducativaInfoId']['nivelId'].'|'.$arrInfoUe['ueducativaInfoId']['turnoId'].'|'.$arrInfoUe['ueducativaInfoId']['paraleloId'].'|'.$arrInfoStudent['estInsEspId'];
                $link = 'http://libreta.minedu.gob.bo/lib/'.$this->getLinkEncript($data);
                $report = $this->container->getParameter('urlreportweb') . $archivo . '&inscripid=' . $estInsId . '&codue=' . $sie. '&idnotatipo=' . $idNotaTipo .'&lk='. $link . '&&__format=pdf&';
                break;
            case 3:
            case 5:
                $archivo = "esp_est_LibretaEscolar_Intelectual_Multiple_v1_pvc.rptdesign";
                $nombre = 'libreta_especial_intelectual_' . $arrInfoUe['requestUser']['sie'] . '_' . $arrInfoUe['ueducativaInfoId']['nivelId'] . '_' . $arrInfoUe['requestUser']['gestion'] . '.pdf';
                $data = $arrInfoStudent['estInsId'] .'|'. $arrInfoStudent['codigoRude'] .'|'.$arrInfoUe['requestUser']['sie'].'|'.$arrInfoUe['requestUser']['gestion'].'|'.$arrInfoUe['ueducativaInfoId']['nivelId'].'|'.$arrInfoUe['ueducativaInfoId']['turnoId'].'|'.$arrInfoUe['ueducativaInfoId']['paraleloId'].'|'.$arrInfoStudent['estInsEspId'];
                $link = 'http://libreta.minedu.gob.bo/lib/'.$this->getLinkEncript($data);
                $report = $this->container->getParameter('urlreportweb') . $archivo . '&inscripid=' . $estInsId . '&codue=' . $sie .'&lk='. $link . '&&__format=pdf&';
                break;
        }

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $nombre ));
        $response->setContent(file_get_contents($report));
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

}
