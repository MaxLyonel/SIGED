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
            array('id' => 1, 'nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('id' => 2, 'nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('id' => 3, 'nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('id' => 4, 'nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('id' => 5, 'nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('id' => 6, 'nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('id' => 7, 'nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('id' => 8, 'nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('id' => 9, 'nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('id' => 10, 'nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('id' => 11, 'nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('id' => 12, 'nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('id' => 13, 'nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('id' => 14, 'nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('id' => 15, 'nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('id' => 16, 'nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('id' => 17, 'nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => false, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12'),
            array('id' => 18, 'nombres' => 'Alberto', 'paterno' => 'Canedo', 'materno' => 'Miranda', 'centro' => 'CEREFE', 'es_talento' => true, 'nro_informe' => '12345', 'informe' => 'archivo.pdf', 'fecha_registro' => '2018-03-12')
        );
        return $this->render('SieEspecialBundle:EstudianteTalento:dt_list.html.twig', array(
            'estudiantes' => $estudiantes,
        ));
    }

    public function newAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $centros = array();
//        dump($request->getSession()->get('ie_id'));die;
        $centro_actual = $request->getSession()->get('ie_id');
        if (!empty($centro_actual) and $centro_actual != -1) {
            $ieducativa_result = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $centro_actual));
            $centros[] = array(
                'id' => $ieducativa_result->getId(),
                'institucioneducativa' => $ieducativa_result->getInstitucioneducativa()
            );
        } else {
            $ieducativa_result = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findBy(array('institucioneducativaTipo' => 4));
            foreach ($ieducativa_result as $institucioneducativa){
                $centros[] = array(
                    'id' => $institucioneducativa->getId(),
                    'institucioneducativa' => $institucioneducativa->getInstitucioneducativa()
                );
            }
        }
        return $this->render('SieEspecialBundle:EstudianteTalento:new.html.twig', array('centros' => $centros));
    }

    public function searchStudentAction(Request $request) {
        $msg = "existe";
        $rude = trim($request->get('rude'));
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $estudiante_result = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));
        $estudiante = array();
        if (!empty($estudiante_result)){
            $einscripcion_result = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $estudiante_result), array('id' => 'DESC'));
            if (!empty($einscripcion_result)){
                $estudianteinscripcion_id = $einscripcion_result->getId();
                $query = $em->getConnection()->prepare('SELECT id FROM tramite WHERE estudiante_inscripcion_id=:estudianteins AND flujo_tipo_id=:flujo');
                $query->bindValue('estudianteins', $estudianteinscripcion_id);
                $query->bindValue('flujo', 10);
                $query->execute();
                $tramite = $query->fetch();
                if (!empty($tramite) and $tramite['id'] > 0) {
                    return $response->setData(array('msg' => $msg));
                } else {
                    $msg = "exito";
                }
                $institucioneducativa_curso_id = $einscripcion_result->getInstitucioneducativaCurso();
                $iecurso_result = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('id' => $institucioneducativa_curso_id));//'gestion_tipo_id' => $gestion_actual
                if (!empty($iecurso_result)){
                    $ieducativa_result = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $iecurso_result->getInstitucioneducativa()));
                } else {
                    $ieducativa_result = null;
                }
                $estudiante = array(
                    'id' => $estudiante_result->getId(),
                    'nombre' => $estudiante_result->getNombre(),
                    'paterno' => $estudiante_result->getPaterno(),
                    'materno' => $estudiante_result->getMaterno(),
                    'cedula' => $estudiante_result->getCarnetIdentidad(),
                    'complemento' => $estudiante_result->getComplemento(),
                    'fecha_nacimiento' => $estudiante_result->getFechaNacimiento()==null?array(date=>''):$estudiante_result->getFechaNacimiento(),
                    'estudiante_ins_id' => $estudianteinscripcion_id,
                    'estudiante_id' => $estudiante_result->getId(),
                    'institucion_educativa' => $ieducativa_result==null?'':$ieducativa_result->getInstitucioneducativa(),
                );
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
        $estudiante_inscripcion_id = $request->get('estudiante_ins_id');
        $institucioneducativa_id = $request->get('centro_inscripcion');
        $fecha_solicitud = substr($request->get('fecha_solicitud'), 6, 4).'-'.substr($request->get('fecha_solicitud'), 3, 2).'-'.substr($request->get('fecha_solicitud'), 0, 2);

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //dump($request->getSession());die;
        $gestion_actual = $request->getSession()->get('currentyear');
        try {
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_especial_talento');")->execute();
            $estudianteTalento = new EstudianteInscripcionEspecialTalento();
            $estudianteTalento->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($estudiante_inscripcion_id));
            $estudianteTalento->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucioneducativa_id));
            $estudianteTalento->setFechaSolicitud(new \DateTime($fecha_solicitud));
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

    public function updateAction(Request $request) {
        $msg = "exito";
        $es_talento = $request->get('es_talento');
        $nro_informe = $request->get('nro_informe');
        $informe = $request->get('informe');//dump($es_talento, $nro_informe);die;
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //try {
            $estudianteTalento = $em->getRepository('SieAppWebBundle:EstudianteInscripcionEspecialTalento')->find($request->get('talento_id'));
            $estudianteTalento->setEsTalento($es_talento);
            $estudianteTalento->setNroInforme($nro_informe);
            $estudianteTalento->setInforme('informe.pdf');
            $estudianteTalento->setFechaInforme(new \Date(date('Y-m-d')));
            //$estudianteTalento->setFechaSolicitud(new \DateTime(date('Y-m-d')));
            //$estudianteTalento->setFechaModificacion(new \DateTime(date('Y-m-d')));
            $estudianteTalento->setUsuarioModificacion($em->getRepository('SieAppWebBundle:Usuario')->findOneById($request->getSession()->get('userId')));
//            $em->persist($estudianteTalento);
            $em->flush();
        /*} catch (Exception $ex) {
            $msg = "error";
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }*/
        $em->getConnection()->commit();
        $response = new JsonResponse();
        return $response->setData(array('msg' => $msg));
    }
}
