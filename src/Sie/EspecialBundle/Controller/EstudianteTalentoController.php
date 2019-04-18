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

    public function newAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $flujotipo_id = $request->get('id');
        $ieducativa_result = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->getSession()->get('ie_id'));
        $centro_inscripcion = $ieducativa_result == null ? '' : $ieducativa_result->getId();//dump($ieducativa_result, $ieducativa_result->getInstitucioneducativa());die;
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
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        // $em->getConnection()->beginTransaction();
        
        $datos = $request->get('solicitud');
        $usuario_id = $request->getSession()->get('userId');
        $rol_id = $request->getSession()->get('roluser');
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $datos['flujotipo_id'], 'orden' => 1));
        $flujo_tipo = $flujoproceso->getFlujoTipo()->getId(); //10 Talento Extraordinario
        $tarea_id = $flujoproceso->getId();//59 Solicita Talento, flujo_proceso
        $tabla = 'institucioneducativa';
        $centroinscripcion_id = $request->getSession()->get('ie_id');
        $tipotramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs' => 'TL'));
        if ($tipotramite == null) {
            $estado = 500;
            return $response->setData(array('estado' => $estado, 'msg' => 'Tipo de Trámite no habilitado.'));
        }
        $observaciones = 'Inicio solicitud de talento';
        $tipotramite_id = $tipotramite->getId();
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
        // $em->getConnection()->commit();
        return $response->setData(array('estado' => $estado, 'msg' => $msg));
    }

    public function upreportAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $tramite_id = $request->get('id');
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
        $rude = $restudiante->getCodigoRude();
        return $this->render('SieEspecialBundle:EstudianteTalento:process.html.twig', array('tramite_id' => $tramite_id, 'rude' => $rude, 'estudiante' => $estudiante));
    }

    public function updateAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        // $em->getConnection()->beginTransaction();

        $documento = $request->files->get('informe');
        if(!empty($documento)) {
            // $path = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
            $destination_path = 'uploads/archivos/flujos/'.$request->getSession()->get('ie_id').'/talento/';
            /* if(!file_exists($destination_path)) { 
                mkdir($destination_path, 0777, true);
            } */
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
        // $em->getConnection()->commit();
        $response = new JsonResponse();
        return $response->setData(array('estado' => $estado, 'msg' => $msg));
    }

    public function checkAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $tramite_id = $request->get('id');

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
        $rude = $restudiante->getCodigoRude();
        $institucion = $datos0->centro_inscripcion;

        $datos1 = json_decode($resultDatos[1]->getdatos());
        $es_talento = $datos1->es_talento;
        $tipo_talento = $datos1->tipo_talento;
        $documento = $datos1->informe;
        return $this->render('SieEspecialBundle:EstudianteTalento:confirm.html.twig', array('tramite_id' => $tramite_id, 'rude' => $rude, 'estudiante' => $estudiante, 'es_talento' => $es_talento, 'tipo_talento' => $tipo_talento, 'institucion' => $institucion, 'documento' => $documento));
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
                try {
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
                    $em->getConnection()->commit();
                } catch (Exception $e) {
                    $em->getConnection()->rollback();
                }
            }
            $estado = 200;
            $msg = $result['msg'];
        } else {
            $estado = 500;
            $msg = $result['msg'];
        }
        $response = new JsonResponse();
        return $response->setData(array('estado' => $estado, 'msg' => $msg));
    }

    public function rptSolicitudAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $tramite_id = $request->get('idtramite');
        $tareadetalle_id = $request->get('id_td');

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($tramite_id);
        $institucioneducativa_id = $tramite->getInstitucioneducativa()->getId();
        $gestion_id = $tramite->getGestionId();

        $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->where('td.id='.$tareadetalle_id)
            ->getQuery()
            ->getSingleResult();
        $estudiante_id = json_decode($resultDatos->getdatos())->estudiante_id;
        $filename = $institucioneducativa_id.'TE_Solicitud_'.$gestion_id. '_'.date('YmdHis').'.pdf';

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'talento_extraordinario_solicitud_v1a.rptdesign&&institucioneducativa_id='.$institucioneducativa_id.'&&gestion_id='.$gestion_id.'&&estudiante_id='.$estudiante_id.'&&tramite_id='.$tramite_id.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function rptInformeAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $tramite_id = $request->get('idtramite');
        $tareadetalle_id = $request->get('id_td');

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($tramite_id);
        $institucioneducativa_id = $tramite->getInstitucioneducativa()->getId();
        $gestion_id = $tramite->getGestionId();

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
        $estudiante_id = json_decode($resultDatos->getdatos())->estudiante_id;
        $filename = $institucioneducativa_id.'TE_Solicitud_'.$gestion_id. '_'.date('YmdHis').'.pdf';
        //dump($estudiante_id);die;
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'talento_extraordinario_informe_v1a.rptdesign&&institucioneducativa_id='.$institucioneducativa_id.'&&gestion_id='.$gestion_id.'&&estudiante_id='.$estudiante_id.'&&tramite_id='.$tramite_id.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
}
