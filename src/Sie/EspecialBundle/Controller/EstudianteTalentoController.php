<?php

namespace Sie\EspecialBundle\Controller;

use Sie\AppWebBundle\Entity\EstudianteInscripcionEspecialTalento;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEspecial;
use Sie\AppWebBundle\Entity\SocioeconomicoEspecial;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoEspecial;
use Sie\AppWebBundle\DBAL\Types\ModalidadAtencionTipo;
use Sie\AppWebBundle\Entity\EspecialAreaTipo;
use Sie\AppWebBundle\DBAL\Types\DiscapacidadEspecialTipo;

use Sie\AppWebBundle\DBAL\Types\GradoParentescoTipo;
use Sie\AppWebBundle\DBAL\Types\GradoTalentoTipo;
use Sie\AppWebBundle\DBAL\Types\OrigenDiscapacidadTipo;
use Sie\AppWebBundle\DBAL\Types\TalentoTipo;
use Sie\AppWebBundle\DBAL\Types\ViveConTipo;
use Sie\AppWebBundle\DBAL\Types\DeteccionTipo;
use Sie\AppWebBundle\DBAL\Types\DificultadAprendizajeTipo;

/**
 * EstudianteTalento controller.
 *
 */
class EstudianteTalentoController extends Controller {

    public $session;
    public $idInstitucion;
    /**
     * Lista de estudiantes registrados para talento extraordinario
     *
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT * FROM estudiante_inscripcion_especial_talento');
        $query->execute();
        $estudiantes = $query->fetchAll();
        return $this->render('SieEspecialBundle:EstudianteTalento:index.html.twig', array('estudiantes' => $estudiantes));
    }

    public function dtlistAction(Request $request){
        $estudiantes = array(
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => true, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12')
        );
        return $this->render('SieEspecialBundle:EstudianteTalento:dt_list.html.twig', array(
            'estudiantes' => $estudiantes,
        ));
    }

    public function newAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT * FROM estudiante_inscripcion_especial_talento');
        $query->execute();
        $estudiantes = $query->fetchAll();
        return $this->render('SieEspecialBundle:EstudianteTalento:new.html.twig', array('estudiantes' => $estudiantes));
    }

    public function searchStudentAction(Request $request) {
        $msg = "existe";
        $rude = trim($request->get('rude'));
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        //dump($request->getSession());die;
        $gestion_actual = $request->getSession()->get('currentyear');
        /*$query = $em->getConnection()->prepare('SELECT id FROM estudiante_inscripcion_especial_talento');
        $query->execute();
        $estudiante = $query->fetch();
        if (!empty($estudiante) and $estudiante->id > 0) {
            return $response->setData(array('msg' => $msg));
        }*/
        $estudiante_result = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));
        $estudiante = array();
        if (!empty($estudiante_result)){
            $einscripcion_result = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $estudiante_result), array('id' => 'DESC'), 1);
            if (!empty($einscripcion_result)){
                $institucioneducativa_curso_id = $einscripcion_result->getInstitucioneducativaCurso();
                $iecurso_result = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('id' => $institucioneducativa_curso_id));//'gestion_tipo_id' => $gestion_actual
                if (!empty($iecurso_result)){
                    $ieducativa_result = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $iecurso_result->getInstitucioneducativa()));
                    $estudiante = array(
                        'id' => $estudiante_result->getId(),
                        'nombre' => $estudiante_result->getNombre(),
                        'paterno' => $estudiante_result->getPaterno(),
                        'materno' => $estudiante_result->getMaterno(),
                        'cedula' => $estudiante_result->getCarnetIdentidad(),
                        'complemento' => $estudiante_result->getComplemento(),
                        'fecha_nacimiento' => $estudiante_result->getFechaNacimiento()==null?array(date=>''):$estudiante_result->getFechaNacimiento(),
                        'estudiante_ins_id' => $einscripcion_result->getId(),
                        'institucion_educativa' => $ieducativa_result->getInstitucioneducativa()==null?'':$ieducativa_result->getInstitucioneducativa(),
                    );
                    $msg = 'exito';
                } else {
                    $msg = 'nocurso';
                }
            } else {
                $msg = 'noins';
            }
        } else {
            $msg = 'noest';
        }
        return $response->setData(array('msg' => $msg, 'estudiante' => $estudiante));
    }

    public function saveAction(Request $request) {
        $msg = "exito";
        /*$nombre = trim($request->get('nombre'));
        $paterno = trim($request->get('paterno'));*/
        $estudiante_inscripcion_id = $request->get('estudiante_ins_id');
        $institucioneducativa_id = 80730661;//$request->get('institucioneducativa_id');
        $fecha_solicitud = date('Y-m-d', strtotime($request->get('fecha_solicitud')));
//        dump($fecha_solicitud, $request->get('fecha_solicitud'));die;
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //dump($request->getSession());die;
        $gestion_actual = $request->getSession()->get('currentyear');
        try { // 22/22/2918 = 22/22/2918
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_especial_talento');")->execute();
            $estudianteTalento = new EstudianteInscripcionEspecialTalento();
            $estudianteTalento->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($estudiante_inscripcion_id));
            //$estudianteTalento->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucioneducativa_id));
            $estudianteTalento->setFechaSolicitud(new \DateTime('2019-02-18'));
            $estudianteTalento->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $estudianteTalento->setFechaModificacion(new \DateTime(date('Y-m-d')));
            $estudianteTalento->setUsuarioRegistro($em->getRepository('SieAppWebBundle:Usuario')->findOneById($request->getSession()->get('userId')));
            $estudianteTalento->setUsuarioModificacion($em->getRepository('SieAppWebBundle:Usuario')->findOneById($request->getSession()->get('userId')));
            $em->persist($estudianteTalento);
            $em->flush();
        } catch (Exception $ex) {
            $msg = "error";
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
        $em->getConnection()->commit();
        $response = new JsonResponse();
        return $response->setData(array('msg' => $msg));
    }
}
