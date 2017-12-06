<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
use Sie\AppWebBundle\Entity\InstitucioneducativaEspecialidadTecnicoHumanistico;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * EstudianteInscripcion controller.
 *
 */
class InfoEstudianteCargaHorariaPlenaController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    /**
     * Formulario para registro de horas
     * @param  Request $request [description]
     * @return [type]           [description]
     */

    public function indexAction(Request $request){

        $infoUe = $request->get('infoUe');
        $aInfoUe = unserialize($infoUe);

        $infoStudent = $request->get('infoStudent');
        $aInfoStudent = json_decode($infoStudent,true);

        $idInscripcion = $aInfoStudent['eInsId'];
        //dump($aInfoUe['requestUser']['gestion']);die;
        $em = $this->getDoctrine()->getManager();
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

        $estudianteInscripcionHumanisticoTecnico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array('estudianteInscripcion' => $idInscripcion));
        
        $hora = (is_object($estudianteInscripcionHumanisticoTecnico))?$estudianteInscripcionHumanisticoTecnico->getHoras():0;
        $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
        $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
        $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();

        return $this->render('SieHerramientaBundle:InfoEstudianteCargaHorariaPlena:index.html.twig',array(
            'hora'=>$hora,
            'grado'=>$grado,
            'inscripcion'=>$inscripcion,
            'infoUe'=>$infoUe,
            'infoStudent'=>$infoStudent
        ));

    }

    public function newAction(Request $request){

    }

    /**
     * [deleteAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function deleteAction(Request $request){

    }

    /**
     * Registra las horas ingresadas de un determinado estudiante
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function createUpdateAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $infoStudent = $request->get('infoStudent');
        $infoUe = $request->get('infoUe');
        $hora = $request->get('hora');
        $jInfoStudent = json_decode($infoStudent,true);

        //dump($hora);die;
        $idInscripcion = $jInfoStudent['eInsId'];
        $codigoRude = $jInfoStudent['codigoRude'];

        $estudianteInscripcionHumanisticoTecnico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array('estudianteInscripcion' => $idInscripcion));

        if (count($estudianteInscripcionHumanisticoTecnico) > 0){
            if ($hora > 1000){
                $message = 'Registrado correctamente';
                $this->addFlash('goodinscription', $message);
                $estudianteInscripcionHumanisticoTecnico->setHoras($hora);
                $em->flush();
                $em->getConnection()->commit();
            } else {
                $message = 'El estudiante '.$codigoRude.' no cuenta con carga horaria, registre correctamente';
                $this->addFlash('noinscription', $message);
            }
        } else {
            $message = 'El estudiante '.$codigoRude.' no cuenta con especialidad';
            $this->addFlash('noinscription', $message);
        }

        //return $this->redirect($this->generateUrl('herramienta_info_estudiante_see_students', array(
        //                   'infoUe' => $infoUe
        //)));
        $response = new JsonResponse();
        //$response->setData(array('infoUe'=>$infoUe, 'infoStudent'=>$infoStudent));
        $response->setData(array('hora'=>$hora));
        return $response;
    }


    /**
     * Solicita confirmacion para finalizar y bloquear el registro de horas ingresadas
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function finalizeCreateUpdateAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();


        //get the info ue
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);

        //get the values throght the infoUe
        $sie = $aInfoUeducativa['requestUser']['sie'];
        $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
        $ciclo = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
        $gestion = $aInfoUeducativa['requestUser']['gestion'];
        $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
        $gradoname = $aInfoUeducativa['ueducativaInfo']['grado'];
        $paraleloname = $aInfoUeducativa['ueducativaInfo']['paralelo'];

        $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourse($sie, $gestion, $nivel, $grado, $paralelo, $turno);

        $studentsHoraCero = array();
        $posicion = 0;
        foreach ($objStudents as $student)
        {
           if ($student['horasPlena'] < 1 and $student['conespecialidad'] and ($student['estadomatriculaId'] == 4 or $student['estadomatriculaId'] == 5 or $student['estadomatriculaId'] == 55)){
               $studentsHoraCero[$posicion] = $student['codigoRude'].' - '.$student['nombre'].' '.$student['paterno'].' '.$student['materno'];
               $posicion = $posicion + 1;
           }
        }

        $studentsSinEspecialidad = array();
        $posicion = 0;
        foreach ($objStudents as $student)
        {
           if (!$student['conespecialidad'] and ($student['estadomatriculaId'] == 4 or $student['estadomatriculaId'] == 5 or $student['estadomatriculaId'] == 55)){
               $studentsSinEspecialidad[$posicion] = $student['codigoRude'].' - '.$student['nombre'].' '.$student['paterno'].' '.$student['materno'];
               $posicion = $posicion + 1;
           }
        }

        return $this->render('SieHerramientaBundle:InfoEstudianteCargaHorariaPlena:finalizaRegistroHoras.html.twig',array(
            'infoUe'=>$infoUe,
            'studentsHoraCero'=>$studentsHoraCero,
            'studentsSinEspecialidad'=>$studentsSinEspecialidad
        ));
    }

    /**
     * Finaliza y bloquea el registro de horas ingresadas
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function finalizeCreateUpdateConfirmAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();


        //get the info ue
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);

        //get the values throght the infoUe
        $sie = $aInfoUeducativa['requestUser']['sie'];
        $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
        $ciclo = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
        $gestion = $aInfoUeducativa['requestUser']['gestion'];
        $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
        $gradoname = $aInfoUeducativa['ueducativaInfo']['grado'];
        $paraleloname = $aInfoUeducativa['ueducativaInfo']['paralelo'];

        $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourse($sie, $gestion, $nivel, $grado, $paralelo, $turno);

        //var_dump($objStudents);

        foreach($objStudents as $student){
            $estInsId = $student['eInsId'];
            $estudianteInscripcionHumanisticoTecnico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array('estudianteInscripcion' => $estInsId));
            if (count($estudianteInscripcionHumanisticoTecnico) > 0){
                $estudianteInscripcionHumanisticoTecnico->setEsvalido('true');
                $em->flush();
            }
        }

        $em->getConnection()->commit();

        //return $this->redirect($this->generateUrl('herramienta_info_estudiante_see_students', array(
        //                   'infoUe' => $infoUe
        //)));
        $response = new JsonResponse();
        //$response->setData(array('infoUe'=>$infoUe, 'infoStudent'=>$infoStudent));
        $response->setData(array('infoUe'=>$infoUe));
        return $response;
    }

}
