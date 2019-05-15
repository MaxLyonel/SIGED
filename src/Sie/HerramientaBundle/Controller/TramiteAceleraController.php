<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;

use Sie\AppWebBundle\Entity\FlujoProceso;
use Sie\AppWebBundle\Entity\FlujoTipo;
use Sie\AppWebBundle\Entity\RolTipo;
use Sie\AppWebBundle\Form\FlujoProcesoType;
use Sie\AppWebBundle\Entity\Tramite;
use Sie\AppWebBundle\Entity\TramiteDetalle;
use Sie\AppWebBundle\Entity\WfSolicitudTramite;
use Sie\AppWebBundle\Controller\WfTramiteController;
use Sie\AppWebBundle\Entity\Institucioneducativa;

/**
 * TramiteAceleraController controller.
 *
 */
class TramiteAceleraController extends Controller
{
    public $session;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
    }

    public function nuevoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $flujotipo_id = $request->get('id');
        $rol = $request->getSession()->get('roluser');
        if ($rol != 9 or $flujotipo_id==null) {
            return $this->redirect($this->generateUrl('wf_tramite_index'));
        }//dump($request->getSession()->get('pathSystem'));die;
        $institucioneducativa_id = $request->getSession()->get('ie_id');
        return $this->render('SieHerramientaBundle:TramiteAcelera:nuevo.html.twig', array('institucioneducativa_id' => $institucioneducativa_id, 'flujotipo_id' => $flujotipo_id));
    }

    public function buscaEstudianteAction(Request $request) {
        $msg = "existe";
        $rude = trim($request->get('rude'));
        $flujotipo_id = trim($request->get('flujotipo_id'));
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $estudiante_result = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));
        $estudiante = array();
        if (!empty($estudiante_result)){
            $estudiante_talento = $em->getRepository('SieAppWebBundle:EstudianteTalento')->findOneBy(array('estudiante' => $estudiante_result));
            if(empty($estudiante_talento)){
                return $response->setData(array('msg' => 'notalento'));
            }
            $einscripcion_result = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $estudiante_result, 'estadomatriculaTipo' => 4), array('id' => 'DESC'));//Evaluar 'estadomatriculaTipo' => 4
            if (!empty($einscripcion_result)){
                if ($einscripcion_result->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativaTipo()->getId() != 1) {
                    return $response->setData(array('msg' => 'noregular'));
                }
                $estudianteinscripcion_id = $einscripcion_result->getId();
                $institucioneducativa = $einscripcion_result->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativa();
                $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                    ->select('wfd')
                    ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                    ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'td.flujoProceso = fp.id')
                    ->where('fp.flujoTipo='.$flujotipo_id)
                    ->andWhere('fp.orden=1')
                    // ->andWhere('YEAR(wfd.fechaRegistro)='. 2019)//Pasar la fecha
                    ->andWhere("wfd.esValido=true")
                    ->orderBy("td.flujoProceso")
                    ->getQuery()
                    ->getResult();
                $valida = false;
                // dump($resultDatos);die;
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
                    'institucion_educativa' => $institucioneducativa,
                    'tipo_talento' => $estudiante_talento->getTalentoTipo(),
                    'puede_acelerar' => $estudiante_talento->getAcelera()==true?'Si':'No',
                    'informe' => $estudiante_talento->getInforme() //"20190411210929.pdf"
                );
            } else {
                $msg = 'noins';
            }
        } else {
            $msg = 'noest';
        }
        return $response->setData(array('msg' => $msg, 'estudiante' => $estudiante));
    }

    public function guardaNuevoAction(Request $request) {
        $estado = 200;
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        // $path = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
        $destination_path = 'uploads/archivos/flujos/tramite/aceleracion/';
        if(!file_exists($destination_path)) { 
            mkdir($destination_path, 0777, true);
        }
        $cant = count($request->files->get('documento'));
        for($i = 0; $i < $cant; $i++) {
            if($i == 0) {
                $doc_sol = date('YmdHis').$i.'.'.$request->files->get('documento')[$i]->getClientOriginalExtension();
                $request->files->get('documento')[$i]->move($destination_path, $doc_sol);
            } else {
                $doc_com = date('YmdHis').$i.'.'.$request->files->get('documento')[$i]->getClientOriginalExtension();
                $request->files->get('documento')[$i]->move($destination_path, $doc_com);
            }
        }
        // $documentoSol = $request->files->get('solicitud_tutor');
        // if(!empty($documentoSol)) {
        //     $doc_sol = date('YmdHis').'1.'.$documentoSol->getClientOriginalExtension();
        //     $documentoSol->move($destination_path, $doc_sol);
        // }else{
        //     $doc_sol='default-2x.pdf';
        // }
        $datos = array();
        $datos['estudiante_id'] = $request->get('estudiante_id');
        $datos['estudiante_ins_id'] = $request->get('estudiante_ins_id');
        $datos['flujotipo_id'] = $request->get('flujotipo_id');
        $datos['institucioneducativa_id'] = $request->get('institucioneducativa_id');
        $datos['fecha_solicitud'] = $request->get('fecha_solicitud');
        $datos['grado_cantidad'] = $request->get('grado_cantidad');
        $datos['procede_aceleracion'] = $request->get('procede_aceleracion');
        $datos['informe'] = $request->get('informe');
        $datos['solicitud_tutor'] = $doc_sol;
        $datos['informe_comision'] = $doc_com;
        $usuario_id = $request->getSession()->get('userId');
        $rol_id = $request->getSession()->get('roluser');
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $datos['flujotipo_id'], 'orden' => 1));
        $flujo_tipo = $flujoproceso->getFlujoTipo()->getId();
        $tarea_id = $flujoproceso->getId();
        $tabla = 'institucioneducativa';
        $institucioneducativa_id = $datos['institucioneducativa_id'];
        $tipotramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs' => 'TA'));
        if ($tipotramite == null) {
            $estado = 500;
            return $response->setData(array('estado' => $estado, 'msg' => 'Tipo de Trámite no habilitado.'));
        }
        $observaciones = 'Inicio solicitud de aceleracion educativa';
        $tipotramite_id = $tipotramite->getId();
        $tramite_id = '';
        $distrito_id = 0;
        $lugarlocalidad_id = 0;
        $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->getSession()->get('ie_id'));//$institucioneducativa_id
        if ($ieducativa) {
            $distrito_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
            $lugarlocalidad_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
        }
        $wfTramiteController = new WfTramiteController();
        $wfTramiteController->setContainer($this->container);
        $result = $wfTramiteController->guardarTramiteNuevo($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $tipotramite_id, $datos['procede_aceleracion'], $tramite_id, json_encode($datos), $lugarlocalidad_id, $distrito_id);
        if ($result['dato'] == true) {
            $msg = $result['msg'];
        } else {
            $estado = 500;
            $msg = $result['msg'];
        }
        return $response->setData(array('estado' => $estado, 'msg' => $msg));
    }

    public function supletorioAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $tramite_id = $request->get('id');
        $rol = $request->getSession()->get('roluser');
        if ($rol != 9 or $tramite_id==null) {
            return $this->redirect($this->generateUrl('wf_tramite_index'));
        }
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
        $informe = $datos->informe;
        if ($datos->procede_aceleracion == "SI") {
            return $this->render('SieHerramientaBundle:TramiteAcelera:supletorio.html.twig', array('tramite_id' => $tramite_id, 'institucioneducativa_id'=>$datos->institucioneducativa_id, 'rude' => $rude, 'estudiante' => $estudiante, 'procede_aceleracion' => $datos->procede_aceleracion, 'grado_cantidad' => $datos->grado_cantidad, 'informe' => $informe, 'solicitud_tutor' => $datos->solicitud_tutor, 'informe_comision' => $datos->informe_comision));
        } else {
            return $this->render('SieHerramientaBundle:TramiteAcelera:observacion.html.twig', array('tramite_id' => $tramite_id, 'institucioneducativa_id'=>$datos->institucioneducativa_id, 'rude' => $rude, 'estudiante' => $estudiante, 'procede_aceleracion' => $datos->procede_aceleracion, 'grado_cantidad' => $datos->grado_cantidad, 'informe' => $informe, 'solicitud_tutor' => $datos->solicitud_tutor, 'informe_comision' => $datos->informe_comision));
        }
    }

    public function guardaSupleAction(Request $request) {
        $estado = 200;
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $destination_path = 'uploads/archivos/flujos/tramite/aceleracion/';
        $documentoActa = $request->files->get('acta_supletorio');
        if(!empty($documentoActa)) {
            if(!file_exists($destination_path)) { 
                mkdir($destination_path, 0777, true);
            }
            $doc_acta = date('YmdHis').'.'.$documentoActa->getClientOriginalExtension();
            $documentoActa->move($destination_path, $doc_acta);
        }else{
            $doc_acta = 'default-2x.pdf';
        }
        $datos = array();
        $datos['tramite_id'] = $request->get('tramite_id');
        $datos['institucioneducativa_id'] = $request->get('institucioneducativa_id');
        $datos['notas'] = $request->get('notas');
        $datos['acta_supletorio'] = $doc_acta;

        $usuario_id = $request->getSession()->get('userId');
        $rol_id = $request->getSession()->get('roluser');
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($datos['tramite_id']);

        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 2));
        $flujo_tipo = $flujoproceso->getFlujoTipo()->getId();
        $tarea_id = $flujoproceso->getId();
        $tabla = 'institucioneducativa';
        $institucioneducativa_id = $datos['institucioneducativa_id'];
        $tipotramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs' => 'TA'));
        if ($tipotramite == null) {
            $estado = 500;
            return $response->setData(array('estado' => $estado, 'msg' => 'Tipo de Trámite no habilitado.'));
        }
        $observaciones = 'Adjuntado de acta supletorio';
        $tipotramite_id = $tipotramite->getId();
        $evaluacion = '';
        $distrito_id = 0;
        $lugarlocalidad_id = 0;
        $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucioneducativa_id);
        if ($ieducativa) {
            $distrito_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
            $lugarlocalidad_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
        }
        $wfTramiteController = new WfTramiteController();
        $wfTramiteController->setContainer($this->container);
        // dump($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);die;
        $result = $wfTramiteController->guardarTramiteEnviado($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);
        if ($result['dato'] == true) {
            $msg = $result['msg'];
        } else {
            $estado = 500;
            $msg = $result['msg'];
        }
        return $response->setData(array('estado' => $estado, 'msg' => $msg));
    }

    public function observacionAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $tramite_id = $request->get('id');
        $rol = $request->getSession()->get('roluser');
        if ($rol == 9 or $tramite_id==null) {
            return $this->redirect($this->generateUrl('wf_tramite_index'));
        }
        $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'td.flujoProceso = fp.id')
            ->where('td.tramite='.$tramite_id)
            ->andWhere('fp.orden IN (1, 4)')
            ->andWhere("wfd.esValido=true")
            ->orderBy("td.flujoProceso")
            ->getQuery()
            ->getResult();
        // dump($resultDatos);die;
        $datos = json_decode($resultDatos[0]->getdatos());
        if (count($resultDatos)>1){
            $datos2 = json_decode($resultDatos[1]->getdatos());
            $observacion = $datos2==null?'':'sa';//json_decode($resultDatos[1]->getdatos())->observacion;
        } else {
            $observacion = '';
        }
        $restudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($datos->estudiante_id);
        $estudiante = $restudiante->getNombre().' '.$restudiante->getPaterno().' '.$restudiante->getMaterno();
        $rude = $restudiante->getCodigoRude();
        return $this->render('SieHerramientaBundle:TramiteAcelera:observacion.html.twig', array('tramite_id' => $tramite_id, 'institucioneducativa_id'=>$datos->institucioneducativa_id, 'rude' => $rude, 'estudiante' => $estudiante, 'procede_aceleracion' => $datos->procede_aceleracion, 'grado_cantidad' => $datos->grado_cantidad, 'informe' => $datos->informe, 'solicitud_tutor' => $datos->solicitud_tutor, 'informe_comision' => $datos->informe_comision, 'observacion' => $observacion));
    }

    public function guardaObsAction(Request $request) {
        $estado = 200;
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();

        $datos = array();
        $datos['tramite_id'] = $request->get('tramite_id');
        $datos['institucioneducativa_id'] = $request->get('institucioneducativa_id');
        $datos['procede_obs'] = $request->get('procede_obs');
        $datos['observacion'] = $request->get('observacion');

        $usuario_id = $request->getSession()->get('userId');
        $rol_id = $request->getSession()->get('roluser');
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($datos['tramite_id']);

        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 3));
        $flujo_tipo = $flujoproceso->getFlujoTipo()->getId();
        $tarea_id = $flujoproceso->getId();
        $tabla = 'institucioneducativa';
        $institucioneducativa_id = $datos['institucioneducativa_id'];
        $tipotramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs' => 'TA'));
        if ($tipotramite == null) {
            $estado = 500;
            return $response->setData(array('estado' => $estado, 'msg' => 'Tipo de Trámite no habilitado.'));
        }
        $observaciones = 'Observación del trámite';
        $tipotramite_id = $tipotramite->getId();
        $evaluacion = $datos['procede_obs'];
        $tramite_id = $datos['tramite_id'];
        $distrito_id = 0;
        $lugarlocalidad_id = 0;
        $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucioneducativa_id);
        if ($ieducativa) {
            $distrito_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
            $lugarlocalidad_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
        }
        $wfTramiteController = new WfTramiteController();
        $wfTramiteController->setContainer($this->container);
        // dump($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);die;
        // Evaluar devolucion, es a otra funcion
        $result = $wfTramiteController->guardarTramiteEnviado($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);
        if ($result['dato'] == true) {
            // if ($evaluacion == "NO") {
            //     $mensaje = $wfTramiteController->guardarTramiteRecibido($id_usuario, $tarea1, $tramite_id);
            // }
            $msg = $result['msg'];
        } else {
            $estado = 500;
            $msg = $result['msg'];
        }
        return $response->setData(array('estado' => $estado, 'msg' => $msg));
    }

    public function verificaAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $tramite_id = $request->get('id');
        $rol = $request->getSession()->get('roluser');
        if ($rol == 9 or $tramite_id==null) {
            return $this->redirect($this->generateUrl('wf_tramite_index'));
        }
        $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->where('td.tramite='.$tramite_id)
            ->andWhere("wfd.esValido=true")
            ->orderBy("td.flujoProceso")
            ->getQuery()
            ->getResult();
        $datos1 = json_decode($resultDatos[0]->getdatos());//dump($datos);die;
        $datos2 = json_decode($resultDatos[1]->getdatos());
        $restudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($datos1->estudiante_id);
        $estudiante = $restudiante->getNombre().' '.$restudiante->getPaterno().' '.$restudiante->getMaterno();
        $rude = $restudiante->getCodigoRude();
        return $this->render('SieHerramientaBundle:TramiteAcelera:verifica.html.twig', array('tramite_id' => $tramite_id, 'institucioneducativa_id'=>$datos1->institucioneducativa_id, 'rude' => $rude, 'estudiante' => $estudiante, 'procede_aceleracion' => $datos1->procede_aceleracion, 'grado_cantidad' => $datos1->grado_cantidad, 'informe' => $datos1->informe, 'solicitud_tutor' => $datos1->solicitud_tutor, 'informe_comision' => $datos1->informe_comision, 'acta_supletorio' => $datos2->acta_supletorio));
    }

    public function guardaVerificaAction(Request $request) {
        $estado = 200;
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();

        $datos = array();
        $datos['tramite_id'] = $request->get('tramite_id');
        $datos['institucioneducativa_id'] = $request->get('institucioneducativa_id');
        $datos['tiene_obs'] = $request->get('tiene_obs');
        $datos['observacion'] = $request->get('observacion');

        $usuario_id = $request->getSession()->get('userId');
        $rol_id = $request->getSession()->get('roluser');
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($datos['tramite_id']);

        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 4));
        $flujo_tipo = $flujoproceso->getFlujoTipo()->getId();
        $tarea_id = $flujoproceso->getId();
        $tabla = 'institucioneducativa';
        $institucioneducativa_id = $datos['institucioneducativa_id'];
        $tipotramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs' => 'TA'));
        if ($tipotramite == null) {
            $estado = 500;
            return $response->setData(array('estado' => $estado, 'msg' => 'Tipo de Trámite no habilitado.'));
        }
        $observaciones = 'Datos verificados para aceleración educativa';
        $tipotramite_id = $tipotramite->getId();
        $evaluacion = $datos['tiene_obs'];
        $distrito_id = 0;
        $lugarlocalidad_id = 0;
        $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucioneducativa_id);
        if ($ieducativa) {
            $distrito_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
            $lugarlocalidad_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
        }
        $wfTramiteController = new WfTramiteController();
        $wfTramiteController->setContainer($this->container);
        // dump($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);die;
        $result = $wfTramiteController->guardarTramiteEnviado($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);
        if ($result['dato'] == true) {
            if ($evaluacion == "NO") {
                //Aqui debe registrar las notas y realizar la inscripcion
                $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 5));
                $tarea_sig = $flujoproceso->getId();
                $mensaje = $wfTramiteController->guardarTramiteRecibido($usuario_id, $tarea_sig, $datos['tramite_id']);
                // Se registra la última tarea.
                $resultf = $wfTramiteController->guardarTramiteEnviado($usuario_id, $rol_id, $flujo_tipo, $tarea_sig, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);
                if ($resultf['dato'] == true) {
                    $msg = $resultf['msg'];
                } else {
                    //Eliminar el trámite anterior
                    $estado = 500;
                    $msg = $resultf['msg'];
                }
            }
            $msg = $result['msg'];
        } else {
            $estado = 500;
            $msg = $result['msg'];
        }
        return $response->setData(array('estado' => $estado, 'msg' => $msg));
    }
}