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
 * TramiteTalentoController controller.
 *
 */
class TramiteTalentoController extends Controller
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

    //Es posible que no se utilice esta funcion saveAction, si no directamente la funcion de especial
    public function saveAction(Request $request) {
        $estado = 200;
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        // $em->getConnection()->beginTransaction();
        
        $datos = $request->get('solicitud');
        $usuario_id = $request->getSession()->get('userId');
        $rol_id = $request->getSession()->get('roluser');
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $datos['flujotipo_id'], 'orden' => 1));
        $flujo_tipo = $flujoproceso->getFlujoTipo()->getId();
        $tarea_id = $flujoproceso->getId();//59 Solicita Talento, flujo_proceso
        $tabla = 'institucioneducativa';
        $centroinscripcion_id = $datos['centro_inscripcion'];//$request->getSession()->get('ie_id');
        $tipotramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs' => 'TE'));
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
        $result = $this->get('wftramite')->guardarTramiteNuevo($usuario_id, $rol_id, $flujo_tipo, $tarea_id, $tabla, $centroinscripcion_id, $observaciones, $tipotramite_id,'', $tramite_id, json_encode($datos), $lugarlocalidad_id, $distrito_id);
        if ($result['dato'] == true) {
            $msg = $result['msg'];
        } else {
            $estado = 500;
            $msg = $result['msg'];
        }
        // $em->getConnection()->commit();
        return $response->setData(array('estado' => $estado, 'msg' => $msg));
    }
}