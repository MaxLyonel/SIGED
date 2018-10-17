<?php

namespace Sie\RieBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\TtecAreaFormacionTipo;
use Sie\AppWebBundle\Entity\InstitucioneducativaAreaEspecialAutorizado;
use Sie\AppWebBundle\Entity\InstitucioneducativaNivelAutorizado;
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaAreaFormacionAutorizado;
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaSede;
use Sie\AppWebBundle\Entity\TtecInstitucioneducativaCarreraAutorizada;
use Sie\AppWebBundle\Entity\TtecResolucionCarrera;
use Sie\AppWebBundle\Entity\TtecResolucionTipo;
use Sie\AppWebBundle\Entity\TtecDenominacionTituloProfesionalTipo;
use Sie\AppWebBundle\Entity\TtecCarreraTipo;
use Sie\AppWebBundle\Entity\TtecRegimenEstudioTipo;
use Sie\AppWebBundle\Entity\TtecEstadoCarreraTipo;

use Sie\AppWebBundle\Form\InstitucioneducativaType;
use Sie\ProcesosBundle\Controller\TramiteRueController as tramiteController;

/**
 * Institucioneducativa controller.
 *
 */
class SolicitudRITTController extends Controller {
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
     * Muestra formulario de Busqueda de la institución educativa
     */
    public function indexAction(Request $request) {
          //dump($request);die;
        $id_rol= $this->session->get('roluser');
        $id_usuario= $this->session->get('userId');
        //Llamada a la funcion que lista los trámites registrados
        $TramiteController = new tramiteController();
        $TramiteController->setContainer($this->container);
        // public function tramiteTarea($tarea_ant,$tarea_actual,$flujotipo,$usuario,$rol)
        $lista = $TramiteController->tramiteTareaRitt(22,22,5,$id_usuario,$id_rol);
        dump($lista);die;

        return $this->render('SieRieBundle:SolicitudRITT:index.html.twig',array('listaTramites'=>$lista['tramites']));
    }
    public  function guardaTramiteAction(Request $request){
        //dump($request);die;
        $id_rol= $this->session->get('roluser');
        $id_usuario= $this->session->get('userId');
        $idlugar_tipo= $this->session->get('idlugar_tipo');
        $idRie= $request->get('idRie');
        $flujotipo=5;//SOLICITUD RITT
        $tarea=22;//Registra la solicitur RITT
        $tabla = 'institucioneducativa';
        $id_tipoTramite=26;
        $TramiteController = new tramiteController();
        $TramiteController->setContainer($this->container);
        //($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$tipotramite,$varevaluacion,$idtramite,$datos)
        $mensaje = $TramiteController->guardarTramiteDetalle($id_usuario,'',$id_rol,$flujotipo,$tarea,$tabla,$idRie,'',$id_tipoTramite,'','','',$idlugar_tipo);
        dump($mensaje);die;
    }

    public function ListaTramitesNacAction(Request $request){
        //dump($request);die;
        $id_rol= $this->session->get('roluser');
        $id_usuario= $this->session->get('userId');
        $TramiteController = new tramiteController();
        $TramiteController->setContainer($this->container);
        // $lista = $TramiteController->tramiteTarea(59,60,11,$id_usuario,$id_rol);
        // public function tramiteTarea($tarea_ant,$tarea_actual,$flujotipo,$usuario,$rol)
        $lista = $TramiteController->tramiteTareaRitt(22,23,5,$id_usuario,$id_rol);
        return $this->render('SieRieBundle:SolicitudRITT:listaTramitesNac.html.twig',array('listaTramites'=>$lista['tramites']));
        //dump($lista);die;
    }




}
