<?php

namespace Sie\EspecialBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
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
            $estadosMatricula = null;

            switch ($discapacidad) {
                case 1: // Auditiva
                        if($nivel != 405){
                            $notas = $this->get('notas')->regular($idInscripcion,$operativo);
                            if($notas['tipoNota'] == 'Trimestre'){
                                $template = 'trimestral';
                            }else{
                                $template = 'regular';
                            }
                            $actualizarMatricula = true;
                        }
                        if(in_array($nivel, array(1,11,403))){
                            $actualizarMatricula = false;
                        }
                        if($operativo >= 4 or $gestion < $gestionActual){
                            $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(5,11)));
                        }
                        break;
                case 2: // Visual
                        $notas = $this->get('notas')->especial_cualitativo($idInscripcion,$operativo);
                        if($notas['tipoNota'] == 'Trimestre'){
                            $template = 'especialCualitativoTrimestral';
                        }else{
                            $template = 'especialCualitativo';
                        }
                        $actualizarMatricula = false;
                        if($operativo >= 4 or $gestion < $gestionActual){
                            $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(70,71,72,73)));
                        }
                        break;
                case 3: //Intelectual
                        switch ($nivel) {
                            case 401:
                                if($grado <= 5){
                                    $notas = $this->get('notas')->especial_cualitativo($idInscripcion,$operativo);
                                    $template = 'especialCualitativo';
                                    $actualizarMatricula = false;
                                    if($operativo >= 4 or $gestion < $gestionActual){
                                        $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(70,71,72,73)));
                                    }
                                    if($notas['tipoNota'] == 'Trimestre'){
                                        $template = 'especialCualitativoTrimestral';
                                    }else{
                                        $template = 'especialCualitativo';
                                    }
                                }
                                break;
                            
                            case 402:
                                if($grado <= 3){
                                    $notas = $this->get('notas')->especial_cualitativo($idInscripcion,$operativo);
                                    $template = 'especialCualitativo';
                                    $actualizarMatricula = false;
                                    if($operativo >= 4 or $gestion < $gestionActual){
                                        $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(70,71,72,73)));
                                    }
                                    if($notas['tipoNota'] == 'Trimestre'){
                                        $template = 'especialCualitativoTrimestral';
                                    }else{
                                        $template = 'especialCualitativo';
                                    }
                                }
                                break;
                        }
                        break;
                case 4: // Fisico -motora
                        break;
                case 5: //Multiple
                        break;
                case 6: //Dificultades en el aprendizaje
                    $notas = $this->get('notas')->especial_cualitativo($idInscripcion,$operativo);
                    if($notas['tipoNota'] == 'Trimestre'){
                        $template = 'especialCualitativoTrimestral';
                    }else{
                        $template = 'especialCualitativo';
                    }
                    $actualizarMatricula = false;
                    if($operativo >= 4 or $gestion < $gestionActual){
                        $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(5,11)));
                    }
                    break;
                case 7: // Talento extraordinario
                        break;
                case 8: // Sordeceguera
                        break;
                case 9: // Problemas emocionales
                        break;
                case 100: // Modalidad Indirecta
                        break;
            }

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
            // Registramos las notas
            $this->get('notas')->especialRegistro($request,$discapacidad);

            // Verificamos si se actualizara el estado de matrícula
            if($request->get('actualizar') == true){
                $this->get('notas')->actualizarEstadoMatricula($idInscripcion);
            }

            // Actualizar estado de matricula de los notas que son cualitativas siempre 
            // y cuando esten en el cuarto bimestre
            if($request->get('operativo') >= 4 and $request->get('actualizar') == false){
                $em = $this->getDoctrine()->getManager();
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

}
