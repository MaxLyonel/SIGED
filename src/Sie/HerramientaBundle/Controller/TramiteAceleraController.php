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

    public function newAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $flujotipo_id = $request->get('id');
        $rol = $request->getSession()->get('roluser');
        if ($rol != 9 || $flujotipo_id==null) {
            return $this->redirect($this->generateUrl('wf_tramite_index'));
        }//dump($request->getSession()->get('pathSystem'));die;
        $institucioneducativa_id = $request->getSession()->get('ie_id');
        return $this->render('SieHerramientaBundle:TramiteAcelera:new.html.twig', array('institucioneducativa_id' => $institucioneducativa_id, 'flujotipo_id' => $flujotipo_id));
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
            $estudiante_talento = $em->getRepository('SieAppWebBundle:EstudianteTalento')->findOneBy(array('estudiante' => $estudiante_result));
            if(empty($estudiante_talento)){
                return $response->setData(array('msg' => 'notalento'));
            }
            $einscripcion_result = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $estudiante_result, 'estadomatriculaTipo' => 4), array('id' => 'DESC'));//Evaluar 'estadomatriculaTipo' => 4
            if (!empty($einscripcion_result)){
                $estudianteinscripcion_id = $einscripcion_result->getId();
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
                    'institucioneducativa_id' => $ieducativa_result==null?'':$ieducativa_result->getId(),
                    'institucion_educativa' => $ieducativa_result==null?'':$ieducativa_result->getInstitucioneducativa(),
                    'tipo_talento' => $estudiante_talento->getTalentoTipo(),
                    'puede_acelerar' => $estudiante_talento->getAcelera()==true?'Si':'No'
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
        $destination_path = 'uploads/archivos/flujos/tramite/aceleracion/';
        $documentoSol = $request->files->get('solicitud_tutor');
        if(!empty($documentoSol)) {
            // $path = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
            /* if(!file_exists($destination_path)) { 
                mkdir($destination_path, 0777, true);
            } */
            $doc_sol = date('YmdHis').'1.'.$documentoSol->getClientOriginalExtension();
            $documentoSol->move($destination_path, $doc_sol);
        }else{
            $doc_sol='default-2x.pdf';
        }
        $documentoCom = $request->files->get('informe_comision');
        if(!empty($documentoCom)) {            
            $doc_com = date('YmdHis').'2.'.$documentoCom->getClientOriginalExtension();
            //$documentoSol->move($destination_path, $doc_com);
        }else{
            $doc_com='default-2x.pdf';
        }
        $datos = array();
        $datos['estudiante_id'] = $request->get('estudiante_id');
        $datos['estudiante_ins_id'] = $request->get('estudiante_ins_id');
        $datos['flujotipo_id'] = $request->get('flujotipo_id');
        $datos['institucioneducativa_id'] = $request->get('institucioneducativa_id');
        $datos['fecha_solicitud'] = $request->get('fecha_solicitud');
        $datos['grado_cantidad'] = $request->get('grado_cantidad');
        $datos['solicitud_tutor'] = $doc_sol;
        $datos['informe_comision'] = $doc_com;

        //$datos = json_decode($request->get('solicitud'));//dump($datos);die;

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
        $observaciones = 'Inicio solicitud de talento';
        $tipotramite_id = $tipotramite->getId();
        $tramite_id = '';
        $distrito_id = 0;
        $lugarlocalidad_id = 0;
        $ieducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucioneducativa_id);
        if ($ieducativa) {
            $distrito_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
            $lugarlocalidad_id = $ieducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
        }
        $wfTramiteController = new WfTramiteController();
        $wfTramiteController->setContainer($this->container);
        //dump($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $tipotramite_id,'', $tramite_id, json_encode($datos), $lugarlocalidad_id, $distrito_id);die;
        $result = $wfTramiteController->guardarTramiteNuevo($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $institucioneducativa_id, $observaciones, $tipotramite_id,'SI', $tramite_id, json_encode($datos), $lugarlocalidad_id, $distrito_id);
        if ($result['dato'] == true) {
            $msg = $result['msg'];
        } else {
            $estado = 500;
            $msg = $result['msg'];
        }
        $em->getConnection()->commit();
        return $response->setData(array('estado' => $estado, 'msg' => $msg));
    }
}