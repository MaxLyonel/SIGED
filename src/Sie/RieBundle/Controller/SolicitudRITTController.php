<?php

namespace Sie\RieBundle\Controller;

use Sie\EsquemaBundle\Entity\turnoSuperiorTipo;
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
use Sie\ProcesosBundle\Controller\TramiteRueController ;

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

        $TramiteController = new  TramiteRueController();
        $TramiteController->setContainer($this->container);
        // public function tramiteTarea($tarea_ant,$tarea_actual,$flujotipo,$usuario,$rol)
        $lista = $TramiteController->tramiteTareaRitt(22,22,5,$id_usuario,$id_rol);
        //dump($lista);die;
        return $this->render('SieRieBundle:SolicitudRITT:index.html.twig',array('listaTramites'=>$lista['tramites']));
    }
    public  function guardaTramiteAction(Request $request){
        $id_rol= $this->session->get('roluser');
        $id_usuario= $this->session->get('userId');
        $idlugar_tipo= '';
        $idRie= $request->get('idRie');
        if ($request->get('idTramite_'))
        {$idTramite=$request->get('idTramite_');}
        else{
            $idTramite='';
        }
        $flujotipo=5;//SOLICITUD RITT
        $tarea=22;//Registra la solicitur RITT
        $tabla = 'institucioneducativa';
        $id_tipoTramite=26;
        $TramiteController = new  TramiteRueController();
        $TramiteController->setContainer($this->container);
        //($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$tipotramite,$varevaluacion,$idtramite,$datos)
        $mensaje = $TramiteController->guardarTramiteDetalle($id_usuario,'',$id_rol,$flujotipo,$tarea,$tabla,$idRie,'',$id_tipoTramite,'',$idTramite,'','');

        $mensajeEnvio="El trámite fue enviado correctamente";
        $request->getSession()
            ->getFlashBag()
            ->add('exito', $mensajeEnvio);

        /**imprime comprobante del envio de la solicitud */

        $arch = 'CERTIFICADOS_'.'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rie_certificados_itt_v1_oyq.rptdesign&idCertificados='.$request->get('idRie').'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

        //return $this->redirectToRoute('solicitud_ritt_index');

    }

    public function ListaTramitesNacAction(Request $request){
        //dump($request);die;
        $id_rol= $this->session->get('roluser');
        $id_usuario= $this->session->get('userId');
        $TramiteController = new  TramiteRueController();
        $TramiteController->setContainer($this->container);
        $lista = $TramiteController->tramiteTareaRitt(22,23,5,$id_usuario,$id_rol);
        return $this->render('SieRieBundle:SolicitudRITT:listaTramitesNac.html.twig',array('listaTramites'=>$lista['tramites']));

    }
    public function guardaTramiteNacAction(Request $request){
        //dump($request);die;
        $id_rol= $this->session->get('roluser');
        $id_usuario= $this->session->get('userId');
        $idRie= $request->get('idRie');
        $obs= $request->get('obstxt');
        $evaluacion= $request->get('evaluacion');
        $idTramite= $request->get('id');
        $idlugar_tipo= '';
        $flujotipo=5;//SOLICITUD RITT
        $tarea=23;//RECEPCIONA Y VERIFICA
        $tabla = 'institucioneducativa';
        $id_tipoTramite=26;
        $TramiteController = new TramiteRueController();
        $TramiteController->setContainer($this->container);
        //($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$tipotramite,$varevaluacion,$idtramite,$datos)
        $mensaje = $TramiteController->guardarTramiteDetalle($id_usuario,'',$id_rol,$flujotipo,$tarea,$tabla,$idRie,$obs,$id_tipoTramite,$evaluacion,$idTramite,'','');

        if($evaluacion=='NO'){
            $mensajeEnvio="Se realizó la devolución del trámite";
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensajeEnvio);
        }
        else{
            $mensajeEnvio="El trámite es procedente";
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensajeEnvio);
        }
        $TramiteController = new TramiteRueController();
        $TramiteController->setContainer($this->container);
        $lista = $TramiteController->tramiteTareaRitt(22,23,5,$id_usuario,$id_rol);
        return $this->render('SieRieBundle:SolicitudRITT:lisTramitesNac.html.twig',array('listaTramites'=>$lista['tramites'],'evaluacion'=>$evaluacion));

    }
    public function ListaTramitesCertificadoNacAction(Request $request){
        //dump($request);die;
        $id_rol= $this->session->get('roluser');
        $id_usuario= $this->session->get('userId');
        $TramiteController = new  TramiteRueController();
        $TramiteController->setContainer($this->container);
        $lista = $TramiteController->tramiteTareaRitt(23,24,5,$id_usuario,$id_rol);
        return $this->render('SieRieBundle:SolicitudRITT:listaTramitesCertificadosNac.html.twig',array('listaTramites'=>$lista['tramites']));
    }
    public  function guardaTramiteNacImprimeAction(Request $request){
        //dump($request);die;
        $id_rol= $this->session->get('roluser');
        $id_usuario= $this->session->get('userId');
        $idRie= $request->get('idRie');
        $obs= '';
        $evaluacion= '';
        $idTramite= $request->get('idTramite_');
        $idlugar_tipo= '';
        $flujotipo=5;//SOLICITUD RITT
        $tarea=24;//EMITE CERTIFICADO
        $tabla = 'institucioneducativa';
        $id_tipoTramite=26;
        $TramiteController = new  TramiteRueController();
        $TramiteController->setContainer($this->container);
        //($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$tipotramite,$varevaluacion,$idtramite,$datos)
        $mensaje = $TramiteController->guardarTramiteDetalle($id_usuario,'',$id_rol,$flujotipo,$tarea,$tabla,$idRie,$obs,$id_tipoTramite,$evaluacion,$idTramite,'','');
        /**
         * acreaditar instituto y local educativo
         */
        $em = $this->getDoctrine()->getManager();
        
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($idRie);
        $entityLe = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($entity->getLeJuridicciongeografica()->getId());
        
        $entity->setInstitucioneducativaAcreditacionTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaAcreditacionTipo')->find(2));
        $entityLe->setJuridiccionAcreditacionTipo($em->getRepository('SieAppWebBundle:JurisdiccionGeograficaAcreditacionTipo')->find(2));
        
        $em->flush();
        /*$request->getSession()
            ->getFlashBag()
            ->add('exito', $mensaje);*/

        //dump($this->container->getParameter('urlreportweb') .'rie_certificados_itt_v1_oyq.rptdesign&idCertificados='.$request->get('idRie').'&&__format=pdf&');die;

        $arch = 'CERTIFICADOS_'.'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rie_certificados_itt_v1_oyq.rptdesign&idCertificados='.$request->get('idRie').'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        //return ( $this->redirectToRoute('solicitud_ritt_guarda_tramite_imprime'));

        return $response;

    }
    public function TramiteObsAction(Request $request){
        //dump($request);
        $id = $request->get('td_id')   ;
        $id_rie= $request->get('id_rie');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT tramite_detalle.obs,tramite_detalle.id, tramite_detalle.fecha_registro from tramite_detalle where tramite_detalle.id=$id");
        $query->execute();
        $obs= $query->fetch();//dump($obs);die;

        $response = new JsonResponse();
        return $response->setData(array('obs'=>$obs['obs'],'idtram'=>$obs['id'],'fecha'=>$obs['fecha_registro'],'idrie'=>$id_rie));

    }



}
