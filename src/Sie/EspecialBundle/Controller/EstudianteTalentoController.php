<?php

namespace Sie\EspecialBundle\Controller;

use Sie\AppWebBundle\Entity\EstudianteInscripcionEspecialTalento;
use Sie\AppWebBundle\Entity\EstudianteTalento;
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

use Sie\SieAppWebBundle\Entity\FlujoProceso;

use Sie\AppWebBundle\DBAL\Types\GradoParentescoTipo;
use Sie\AppWebBundle\DBAL\Types\GradoTalentoTipo;
use Sie\AppWebBundle\DBAL\Types\OrigenDiscapacidadTipo;
use Sie\AppWebBundle\DBAL\Types\TalentoTipo;
use Sie\AppWebBundle\DBAL\Types\ViveConTipo;
use Sie\AppWebBundle\DBAL\Types\DeteccionTipo;
use Sie\AppWebBundle\DBAL\Types\DificultadAprendizajeTipo;

use Sie\ProcesosBundle\Controller\TramiteRueController;
use Sie\AppWebBundle\Controller\WfTramiteController;

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
        $flujotipo_id = isset($_GET['id']) == false ? 10 : $_GET['id'];
        $ieducativa_result = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->getSession()->get('ie_id'));
        $centro_inscripcion = $ieducativa_result == null ? '' : $ieducativa_result->getId();
        $centro = $ieducativa_result == null ? '' : $ieducativa_result->getInstitucioneducativa();
        return $this->render('SieEspecialBundle:EstudianteTalento:new.html.twig', array('centro_inscripcion' => $centro_inscripcion, 'centro' => $centro, 'flujotipo_id' => $flujotipo_id));
    }

    public function searchStudentAction(Request $request) {
        $msg = "existe";
        $rude = trim($request->get('rude'));
        $flujotipo_id = trim($request->get('flujotipo_id'));
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $estudiante_result = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));
        $estudiante = array();
        if (!empty($estudiante_result)){
            $einscripcion_result = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $estudiante_result), array('id' => 'DESC'));
            if (!empty($einscripcion_result)){
                $estudianteinscripcion_id = $einscripcion_result->getId();
                $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                    ->select('wfd')
                    ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                    ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'td.flujoProceso = fp.id')
                    ->where('fp.flujoTipo='.$flujotipo_id)
                    ->andWhere('fp.orden=1')
                    ->andWhere("wfd.esValido=true")
                    ->orderBy("td.flujoProceso")
                    ->getQuery()
                    ->getResult();
                $valida = false;
                foreach ($resultDatos as $item) {
                    $datos = json_decode($item->getdatos());
                    if ($datos->estudiante_id == $estudiante_result->getId()) {
                        $valida = true;
                    }
                }
                if ($valida == true) {
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
        $estado = 200;
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $datos = $request->get('solicitud');
        $usuario_id = $request->getSession()->get('userId');
        $rol_id = $request->getSession()->get('roluser');
        //$flujotipo= $em->getRepository('SieAppWebBundle:FlujoTipo')->findOneById($datos['flujotipo_id']);
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $datos['flujotipo_id'], 'orden' => 1));
        $flujo_tipo = $flujoproceso->getFlujoTipo()->getId(); //10 Talento Extraordinario
        $tarea_id = $flujoproceso->getId();//59 Solicita Talento, flujo_proceso
        $tabla = 'institucioneducativa';
        $centroinscripcion_id = $request->getSession()->get('ie_id');
        //$centroinscripcion_id = $request->getSession()->get('ie_id');
        $observaciones = 'Inicio solicitud de talento';
        $tipotramite_id = 47;//Verificar en la BD si cambia a otro valor
        $tramite_id = ''; //Tadavia el trámite no existe
        $distrito_id = 0;
        $lugarlocalidad_id = 0;
        $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($centroinscripcion_id);
        if ($ieducativa) {
            $distrito_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
            $lugarlocalidad_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
        }
        $wfTramiteController = new WfTramiteController();
        $wfTramiteController->setContainer($this->container);
        $result = $wfTramiteController->guardarTramiteNuevo($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $centroinscripcion_id, $observaciones, $tipotramite_id,'', $tramite_id, json_encode($datos), $lugarlocalidad_id, $distrito_id);
        if ($result['dato'] == true) {
            $msg = $result['msg'];
        } else {
            $estado = 500;
            $msg = $result['msg'];
        }
        $em->getConnection()->commit();
        $response = new JsonResponse();
        return $response->setData(array('estado' => $estado, 'msg' => $msg));
    }

    public function upreportAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $tramite_id = $_GET['id'];
        $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'td.flujoProceso = fp.id')
            ->where('td.tramite='.$tramite_id)
            ->andWhere('fp.orden=1')
            ->andWhere("wfd.esValido=true")
            ->orderBy("td.flujoProceso")
            ->getQuery()
            ->getSingleResult();
        $datos = json_decode($resultDatos->getdatos());
        $restudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($datos->estudiante_id);
        $estudiante = $restudiante->getNombre().' '.$restudiante->getPaterno().' '.$restudiante->getMaterno();
        return $this->render('SieEspecialBundle:EstudianteTalento:process.html.twig', array('tramite_id' => $tramite_id, 'estudiante' => $estudiante));
    }

    public function updateAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $documento = $request->files->get('informe');
        if(!empty($documento)){
            $destination_path = 'uploads/talento/';//Verificar la ruta
            $docpdf = date('YmdHis').'.'.$documento->getClientOriginalExtension();
            $documento->move($destination_path, $docpdf);
        }else{
            $docpdf='default-2x.pdf';
        }
        $datos = array();
        $datos['tramite_id'] = $request->get('tramite_id');
        $datos['es_talento'] = $request->get('es_talento');
        $datos['tipo_talento'] = $request->get('tipo_talento');
        $datos['acelera'] = $request->get('acelera');
        $datos['nro_informe'] = $request->get('nro_informe');
        $datos['informe'] = $docpdf;

        $usuario_id = $request->getSession()->get('userId');
        $rol_id = $request->getSession()->get('roluser');
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($datos['tramite_id']);

        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 2));
        $flujo_tipo = $flujoproceso->getFlujoTipo()->getId(); //10 Talento Extraordinario
        $tarea_id = $flujoproceso->getId();//60 Determina talento, flujo_proceso = tarea
        $tabla = 'institucioneducativa';
        $centroinscripcion_id = $request->getSession()->get('ie_id');
        $observaciones = 'Informe psicopedagógico';
        $evaluacion = $request->get('es_talento');
        $tramite_id = $datos['tramite_id'];
        $distrito_id = 0;
        $lugarlocalidad_id = 0;

        $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($centroinscripcion_id);
        if ($ieducativa) {
            $distrito_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
            $lugarlocalidad_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
        }
        $wfTramiteController = new WfTramiteController();
        $wfTramiteController->setContainer($this->container);
        $result = $wfTramiteController->guardarTramiteEnviado($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $centroinscripcion_id, $observaciones, $evaluacion, $tramite_id, json_encode($datos), $lugarlocalidad_id, $distrito_id);
        if ($result['dato'] == true) {
            $estado = 200;
            $msg = $result['msg'];
        } else {
            $estado = 500;
            $msg = $result['msg'];
        }
        $em->getConnection()->commit();
        $response = new JsonResponse();
        return $response->setData(array('estado' => $estado, 'msg' => $msg));
    }

    public function checkAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $tramite_id = $_GET['id'];

        $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->where('td.tramite='.$tramite_id)
            ->andWhere("wfd.esValido=true")
            ->orderBy("td.flujoProceso")
            ->getQuery()
            ->getResult();
        $datos0 = json_decode($resultDatos[0]->getdatos());
        $restudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($datos0->estudiante_id);
        $estudiante = $restudiante->getNombre().' '.$restudiante->getPaterno().' '.$restudiante->getMaterno();

        $datos1 = json_decode($resultDatos[1]->getdatos());
        $es_talento = $datos1->es_talento;
        $tipo_talento = $datos1->tipo_talento;
        return $this->render('SieEspecialBundle:EstudianteTalento:confirm.html.twig', array('tramite_id' => $tramite_id, 'estudiante' => $estudiante, 'es_talento' => $es_talento, 'tipo_talento' => $tipo_talento));
    }

    public function confirmAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $usuario_id = $request->getSession()->get('userId');
        $rol_id = $request->getSession()->get('roluser');
        $tramite_id = $request->get('tramite_id');

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($tramite_id);

        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 3));
        $flujo_tipo = $flujoproceso->getFlujoTipo()->getId(); //10 Talento Extraordinario
        $tarea_id = $flujoproceso->getId();//61 Finaliza tramite, flujo_proceso = tarea
        $observaciones = 'Confirmado de registro';
        $evaluacion = '';

        $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->where('td.tramite='.$tramite_id)
            ->andWhere("wfd.esValido=true")
            ->orderBy("td.flujoProceso")
            ->getQuery()
            ->getResult();
        $tareasDatos = array();
        foreach($resultDatos as $wfd) {
            $datos = json_decode($wfd->getdatos(),true);
            $tareasDatos[] = array('flujoProceso'=>$wfd->getTramiteDetalle()->getFlujoProceso()->getId(), 'datos'=>$datos);
        }
        $tabla = 'institucioneducativa';
        $centroinscripcion_id = $tareasDatos[0]['datos']['centro_inscripcion'];
        $distrito_id = 0;
        $lugarlocalidad_id = 0;

        $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($centroinscripcion_id);
        if ($ieducativa) {
            $distrito_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
            $lugarlocalidad_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
        }

        $wfTramiteController = new WfTramiteController();
        $wfTramiteController->setContainer($this->container);
        $result = $wfTramiteController->guardarTramiteEnviado($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $centroinscripcion_id, $observaciones, $evaluacion, $tramite_id, json_encode($tareasDatos), $lugarlocalidad_id, $distrito_id);
        if ($result['dato'] == true) {
            if ($tareasDatos[1]['datos']['es_talento'] == 'SI') {
                $estudianteTalento = new EstudianteTalento();
                $estudianteTalento->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->findOneById($tareasDatos[0]['datos']['estudiante_id']));
                $estudianteTalento->setTalentoTipo($tareasDatos[1]['datos']['tipo_talento']);
                $estudianteTalento->setAcelera($tareasDatos[1]['datos']['acelera']);
                $estudianteTalento->setFechaRegistro(new \DateTime(date('Y-m-d')));
                //$estudianteTalento->setFechaModificacion(new \DateTime(date('Y-m-d')));
                $estudianteTalento->setUsuarioRegistro($em->getRepository('SieAppWebBundle:Usuario')->findOneById($usuario_id));
                //$estudianteTalento->setUsuarioModificacion($em->getRepository('SieAppWebBundle:Usuario')->findOneById($usuario_id));
                $estudianteTalento->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($centroinscripcion_id));
                $em->persist($estudianteTalento);
                $em->flush();
                $em->flush();
            }
            $estado = 200;
            $msg = $result['msg'];
        } else {
            $estado = 500;
            $msg = $result['msg'];
        }
        $em->getConnection()->commit();
        $response = new JsonResponse();
        return $response->setData(array('estado' => $estado, 'msg' => $msg));
    }
}
