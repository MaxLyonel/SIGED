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
        $tipotramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs' => 'AA'));
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
        $result = $this->get('wftramite')->guardarTramiteNuevo($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $tipotramite_id, $datos['procede_aceleracion'], $tramite_id, json_encode($datos), $lugarlocalidad_id, $distrito_id);
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
        $restudianteinst = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante'=>$restudiante));
        //dump($restudianteinst->getInstitucioneducativaCurso()->getNivelTipo()->getId());
        $codigo_sie = $restudianteinst->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $nivel_id = $restudianteinst->getInstitucioneducativaCurso()->getNivelTipo()->getId();
        $grado_id = $restudianteinst->getInstitucioneducativaCurso()->getGradoTipo()->getId();
        $paralelo_id = $restudianteinst->getInstitucioneducativaCurso()->getParaleloTipo()->getId();
        $turno_id = $restudianteinst->getInstitucioneducativaCurso()->getTurnoTipo()->getId();

        $objAreasCurrentLevel = array();
        for ($i=0; $i < $datos->grado_cantidad; $i++) {
            $nivel_tipo = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel_id);
            $grado_tipo = $em->getRepository('SieAppWebBundle:GradoTipo')->find($grado_id);
            // dump($nivel_id, $grado_id);
            $objAreasCurrentLevel[] = array(
                'curso' => array(
                    'codigo_sie'=>$codigo_sie, 'nombre_sie'=>$restudianteinst->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativa(),
                    'nivel_id'=>$nivel_id, 'nivel'=>($nivel_tipo)?$nivel_tipo->getNivel():'',//$restudianteinst->getInstitucioneducativaCurso()->getNivelTipo()->getNivel(),
                    'grado_id'=>$grado_id, 'grado'=>($grado_tipo)?$grado_tipo->getGrado():'',//$restudianteinst->getInstitucioneducativaCurso()->getGradoTipo()->getGrado(),
                    'paralelo_id'=>$paralelo_id, 'paralelo'=>$restudianteinst->getInstitucioneducativaCurso()->getParaleloTipo()->getParalelo(),
                    'turno_id'=>$turno_id, 'turno'=>$restudianteinst->getInstitucioneducativaCurso()->getTurnoTipo()->getTurno()),
                'materiasnotas' => $this->getAsignaturasPerStudent($codigo_sie, $nivel_id, $grado_id, $paralelo_id, $turno_id)
            );
            if ($nivel_id == 11) {
                if ($grado_id == 1) {
                    $grado_id++;
                } elseif ($grado_id == 2) {
                    $nivel_id = 12;
                    $grado_id = 1;
                }
            } elseif ($nivel_id == 12) {
                if (in_array($grado_id, [1, 2, 3, 4, 5])) {
                    $grado_id++;
                } elseif ($grado_id == 6) {
                    $nivel_id = 13;
                    $grado_id = 1;
                }
            } elseif ($nivel_id == 13) {
                if (in_array($grado_id, [1, 2, 3, 4, 5])) {
                    $grado_id++;
                } elseif ($grado_id == 6) {
                    $nivel_id = 13;
                    $grado_id = 1;
                }
            }
        }
        // dump($objAreasCurrentLevel);die;
        $estudiante = $restudiante->getNombre().' '.$restudiante->getPaterno().' '.$restudiante->getMaterno();
        $rude = $restudiante->getCodigoRude();
        $informe = $datos->informe;
        if ($datos->procede_aceleracion == "SI") {
            return $this->render('SieHerramientaBundle:TramiteAcelera:supletorio.html.twig', array('tramite_id' => $tramite_id, 'institucioneducativa_id'=>$datos->institucioneducativa_id, 'rude' => $rude, 'estudiante' => $estudiante, 'procede_aceleracion' => $datos->procede_aceleracion, 'grado_cantidad' => $datos->grado_cantidad, 'informe' => $informe, 'solicitud_tutor' => $datos->solicitud_tutor, 'informe_comision' => $datos->informe_comision,
            'cursomateriasnotas' => $objAreasCurrentLevel));
        } else {
            return $this->render('SieHerramientaBundle:TramiteAcelera:observacion.html.twig', array('tramite_id' => $tramite_id, 'institucioneducativa_id'=>$datos->institucioneducativa_id, 'rude' => $rude, 'estudiante' => $estudiante, 'procede_aceleracion' => $datos->procede_aceleracion, 'grado_cantidad' => $datos->grado_cantidad, 'informe' => $informe, 'solicitud_tutor' => $datos->solicitud_tutor, 'informe_comision' => $datos->informe_comision,
            'cursomateriasnotas' => $objAreasCurrentLevel));
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
        $datos['curso_asignatura_notas'] = $request->get('curso_asignatura_notas');
        $datos['acta_supletorio'] = $doc_acta;

        $usuario_id = $request->getSession()->get('userId');
        $rol_id = $request->getSession()->get('roluser');
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($datos['tramite_id']);

        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 2));
        $flujo_tipo = $flujoproceso->getFlujoTipo()->getId();
        $tarea_id = $flujoproceso->getId();
        $tabla = 'institucioneducativa';
        $institucioneducativa_id = $datos['institucioneducativa_id'];
        $tipotramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs' => 'AA'));
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
        // dump($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);die;
        $result = $this->get('wftramite')->guardarTramiteEnviado($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);
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
            $observacion = $datos2==null?'':json_decode($resultDatos[1]->getdatos())->observacion;
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
        // dump($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);die;
        // Evaluar devolucion, es a otra funcion
        $result = $this->get('wftramite')->guardarTramiteEnviado($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);
        if ($result['dato'] == true) {
            // if ($evaluacion == "NO") {
            //     $mensaje = $this->get('wftramite')->guardarTramiteRecibido($id_usuario, $tarea1, $tramite_id);
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
        $datos1 = json_decode($resultDatos[0]->getdatos());
        $datos2 = json_decode($resultDatos[1]->getdatos());//dump(json_decode($datos2->curso_asignatura_notas));die;
        $restudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($datos1->estudiante_id);
        $estudiante = $restudiante->getNombre().' '.$restudiante->getPaterno().' '.$restudiante->getMaterno();
        $rude = $restudiante->getCodigoRude();

        $restudianteinst = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante'=>$restudiante));
        
        $nivel_grado = json_decode($datos2->curso_asignatura_notas)[0]->curso;

        $nivel_id = $nivel_grado->nivel_id;
        $grado_id = $nivel_grado->grado_id;
        $paralelo_id = $restudianteinst->getInstitucioneducativaCurso()->getParaleloTipo()->getId();
        $turno_id = $restudianteinst->getInstitucioneducativaCurso()->getTurnoTipo()->getId();
        $codigo_sie = $nivel_grado->sie;

        $curso = array(
            'sie'=>$codigo_sie,
            'nombre'=>$restudianteinst->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativa(),
            'nivel_id'=>$nivel_id,
            'nivel'=>$restudianteinst->getInstitucioneducativaCurso()->getNivelTipo()->getNivel(),
            'grado_id'=>$grado_id,
            'grado'=>$restudianteinst->getInstitucioneducativaCurso()->getGradoTipo()->getGrado(),
            'paralelo'=>$restudianteinst->getInstitucioneducativaCurso()->getParaleloTipo()->getParalelo(),
            'turno'=>$restudianteinst->getInstitucioneducativaCurso()->getTurnoTipo()->getTurno()
        );
        ///////////////
        $arrayCursoAsignatura = array();
        for ($i=0; $i < $datos1->grado_cantidad; $i++) {
            if ($nivel_id == 11) {
                if ($grado_id == 1) {
                    $grado_id++;
                } elseif ($grado_id == 2) {
                    $nivel_id = 12;
                    $grado_id = 1;
                }
            } elseif ($nivel_id == 12) {
                if (in_array($grado_id, [1, 2, 3, 4, 5])) {
                    $grado_id++;
                } elseif ($grado_id == 6) {
                    $nivel_id = 13;
                    $grado_id = 1;
                }
            } elseif ($nivel_id == 13) {
                if (in_array($grado_id, [1, 2, 3, 4, 5])) {
                    $grado_id++;
                } elseif ($grado_id == 6) {
                    $nivel_id = 13;
                    $grado_id = 1;
                }
            }
            $paralelos = array();
            $queryParalelo = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->createQueryBuilder('iec')
                ->select('IDENTITY(iec.paraleloTipo) as paraleloTipo', 'pt.paralelo as paralelo')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'iec.paraleloTipo = pt.id')
                ->where('iec.institucioneducativa = :id')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('id', $codigo_sie)
                ->setParameter('nivel', $nivel_id)
                ->setParameter('grado', $grado_id)
                ->setParameter('gestion', $this->session->get('currentyear'))
                ->distinct()
                ->orderBy('iec.paraleloTipo', 'ASC')
                ->getQuery();
            $turnos = array();
            $queryTurno = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->createQueryBuilder('iec')
                ->select('IDENTITY(iec.turnoTipo) as turnoTipo', 'pt.turno as turno')
                ->leftjoin('SieAppWebBundle:turnoTipo', 'pt', 'WITH', 'iec.turnoTipo = pt.id')
                ->where('iec.institucioneducativa = :id')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('id', $codigo_sie)
                ->setParameter('nivel', $nivel_id)
                ->setParameter('grado', $grado_id)
                ->setParameter('gestion', $this->session->get('currentyear'))
                ->distinct()
                ->orderBy('iec.turnoTipo', 'ASC')
                ->getQuery();
            $nivel_tipo = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel_id);
            $grado_tipo = $em->getRepository('SieAppWebBundle:GradoTipo')->find($grado_id);
            $arrayCursoAsignatura[] = array(
                'curso' => array(
                    'codigo_sie'=>$codigo_sie, 'nombre_sie'=>$restudianteinst->getInstitucioneducativaCurso()->getInstitucioneducativa()->getInstitucioneducativa(),
                    'nivel_id'=>$nivel_id, 'nivel'=>($nivel_tipo)?$nivel_tipo->getNivel():'',//$restudianteinst->getInstitucioneducativaCurso()->getNivelTipo()->getNivel(),
                    'grado_id'=>$grado_id, 'grado'=>($grado_tipo)?$grado_tipo->getGrado():'',//$restudianteinst->getInstitucioneducativaCurso()->getGradoTipo()->getGrado(),
                    // 'paralelo_id'=>$paralelo_id, 'paralelo'=>$restudianteinst->getInstitucioneducativaCurso()->getParaleloTipo()->getParalelo(),
                    'paralelos'=>$queryParalelo->getResult(),
                    // 'turno_id'=>$turno_id, 'turno'=>$restudianteinst->getInstitucioneducativaCurso()->getTurnoTipo()->getTurno(),
                    'turnos'=>$queryTurno->getResult()),
                'materiasnotas' => $this->getAsignaturasPerStudent($codigo_sie, $nivel_id, $grado_id, $paralelo_id, $turno_id)
            );
        }
        ///////////////
        return $this->render('SieHerramientaBundle:TramiteAcelera:verifica.html.twig', array(
            'tramite_id' => $tramite_id,
            'institucioneducativa_id'=>$datos1->institucioneducativa_id,
            'codigo_sie'=>$datos1->institucioneducativa_id,
            // 'codigo_sie'=>$restudianteinst->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId(),
            'rude' => $rude,
            'estudiante' => $estudiante,
            'procede_aceleracion' => $datos1->procede_aceleracion,
            'grado_cantidad' => $datos1->grado_cantidad,
            'informe' => $datos1->informe,
            'solicitud_tutor' => $datos1->solicitud_tutor,
            'informe_comision' => $datos1->informe_comision,
            'acta_supletorio' => $datos2->acta_supletorio,
            'curso' => $curso,//json_decode($datos2->curso_asignatura_notas)[0]->curso,
            'asignatura_notas' => json_decode($datos2->curso_asignatura_notas)[0]->asignatura_notas,
            'arraycursoasignaturas' => $arrayCursoAsignatura));
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
        $datos['curso_asignatura_notas'] = $request->get('curso_asignatura_notas');

        $usuario_id = $request->getSession()->get('userId');
        $rol_id = $request->getSession()->get('roluser');
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($datos['tramite_id']);

        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 4));
        $flujo_tipo = $flujoproceso->getFlujoTipo()->getId();
        $tarea_id = $flujoproceso->getId();
        $tabla = 'institucioneducativa';
        $institucioneducativa_id = $datos['institucioneducativa_id'];
        $tipotramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs' => 'AA'));
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

        // $cursoasignaturanota = json_decode($datos['curso_asignatura_notas']);
        // foreach ($cursoasignaturanota as $curso) {
        //     dump($curso);
        // }die;
        // dump($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);die;
        $result = $this->get('wftramite')->guardarTramiteEnviado($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);
        if ($result['dato'] == true) {
            if ($evaluacion == "NO") {
                $cursoasignaturanota = json_decode($datos['curso_asignatura_notas']);
                foreach ($cursoasignaturanota as $curso) {
                    # code...
                }
                //Consultar 
                // $datos['curso_asignatura_notas'] y ; $datos['notas'];
                //Aqui debe registrar las notas y realizar la inscripcion
                $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 5));
                $tarea_sig = $flujoproceso->getId();
                $mensaje = $this->get('wftramite')->guardarTramiteRecibido($usuario_id, $tarea_sig, $datos['tramite_id']);
                
                // Se registra la última tarea.
                $resultf = $this->get('wftramite')->guardarTramiteEnviado($usuario_id, $rol_id, $flujo_tipo, $tarea_sig, $tabla, $institucioneducativa_id, $observaciones, $evaluacion, $datos['tramite_id'], json_encode($datos), $lugarlocalidad_id, $distrito_id);
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

    public function buscaIeAction($id, $nivel, $grado) {
        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion(:user_id::INT, :sie::INT, :roluser::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
        $query->bindValue(':sie', $id);
        $query->bindValue(':roluser', $this->session->get('roluser'));
        $query->execute();
        $aTuicion = $query->fetchAll();

        $nivel_id = 0;
        $nombre_nivel = '';
        $grado_id = 0;
        $nombre_grado = '';
        $paralelo = array();
        $turno = array();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
        if ($institucion) {
            if ($aTuicion[0]['get_ue_tuicion']) {
                $nombreIE = $institucion->getInstitucioneducativa();

                $queryParalelo = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->createQueryBuilder('iec')
                        ->select('IDENTITY(iec.paraleloTipo) as paraleloTipo', 'pt.paralelo as paralelo')
                        ->leftjoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'iec.paraleloTipo = pt.id')
                        ->where('iec.institucioneducativa = :id')
                        ->andwhere('iec.nivelTipo = :nivel')
                        ->andwhere('iec.gradoTipo = :grado')
                        ->andwhere('iec.gestionTipo = :gestion')
                        ->setParameter('id', $id)
                        ->setParameter('nivel', $nivel)
                        ->setParameter('grado', $grado)
                        ->setParameter('gestion', $this->session->get('currentyear'))
                        ->distinct()
                        ->orderBy('iec.paraleloTipo', 'ASC')
                        ->getQuery();
                $resultQueryParalelo = $queryParalelo->getResult();
                foreach ($resultQueryParalelo as $info) {
                    $paralelo[$info['paraleloTipo']] = $info['paralelo'];
                }
                $queryTurno = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->createQueryBuilder('iec')
                        ->select('IDENTITY(iec.turnoTipo) as turnoTipo', 'pt.turno as turno')
                        ->leftjoin('SieAppWebBundle:turnoTipo', 'pt', 'WITH', 'iec.turnoTipo = pt.id')
                        ->where('iec.institucioneducativa = :id')
                        ->andwhere('iec.nivelTipo = :nivel')
                        ->andwhere('iec.gradoTipo = :grado')
                        ->andwhere('iec.gestionTipo = :gestion')
                        ->setParameter('id', $id)
                        ->setParameter('nivel', $nivel)
                        ->setParameter('grado', $grado)
                        ->setParameter('gestion', $this->session->get('currentyear'))
                        ->distinct()
                        ->orderBy('iec.turnoTipo', 'ASC')
                        ->getQuery();
                $resultQueryTurno = $queryTurno->getResult();
                foreach ($resultQueryTurno as $info) {
                    $turno[$info['turnoTipo']] = $info['turno'];
                }

                $nivel_tipo = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel);
                $grado_tipo = $em->getRepository('SieAppWebBundle:GradoTipo')->find($grado);
                $nivel_id = ($nivel_tipo)?$nivel_tipo->getId():'';
                $nombre_nivel = ($nivel_tipo)?$nivel_tipo->getNivel():'';
                $grado_id = ($grado_tipo)?$grado_tipo->getId():'';
                $nombre_grado = ($grado_tipo)?$grado_tipo->getGrado():'';
            } else {
                $nombreIE = 'No tiene Tuición sobre la Unidad Educativa';
            }
        } else {
            $nombreIE = 'No existe Unidad Educativa';
        }
        $response = new JsonResponse();

        return $response->setData(array('nombre'=>$nombreIE, 'nivel_id'=>$nivel_id, 'nivel'=>$nombre_nivel, 'grado_id'=>$grado_id, 'grado'=>$nombre_grado, 'paralelo'=>$paralelo, 'turno'=>$turno));
    }

      /**
     * get the asignaturas per student
     * @param type $sie
     * @param type $nivel
     * @param type $grado
     * @param type $paralelo
     * @param type $turno
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    private function getAsignaturasPerStudent($sie, $nivel, $grado, $paralelo, $turno) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('ast.id', 'ast.asignatura, ieco.id as iecoId')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco', 'WITH', 'iec.id=ieco.insitucioneducativaCurso')
                ->leftjoin('SieAppWebBundle:AsignaturaTipo', 'ast', 'WITH', 'ieco.asignaturaTipo=ast.id')
                ->leftjoin('SieAppWebBundle:AreaTipo', 'at', 'WITH', 'ast.areaTipo = at.id')
                ->where('iec.institucioneducativa= :sie')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andwhere('iec.turnoTipo = :turno')
                ->setParameter('sie', $sie)
                ->setParameter('gestion', $this->session->get('currentyear'))
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('turno', $turno)
                ->orderBy('at.id,ast.id')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }
}