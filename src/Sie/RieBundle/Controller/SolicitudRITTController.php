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
use Sie\AppWebBundle\Entity\DocumentoSerie;
use Sie\AppWebBundle\Entity\Documento;
use Sie\AppWebBundle\Form\InstitucioneducativaType;
use Sie\ProcesosBundle\Controller\TramiteRueController ;
use Sie\TramitesBundle\Controller\DocumentoController ;
use phpseclib\Crypt\RSA;
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
        $id_rol= $this->session->get('roluser');
        $id_usuario= $this->session->get('userId');
        //Llamada a la funcion que lista los trámites registrados

        $TramiteController = new  TramiteRueController();
        $TramiteController->setContainer($this->container);
        // public function tramiteTarea($tarea_ant,$tarea_actual,$flujotipo,$usuario,$rol)
        $lista = $TramiteController->tramiteTareaRitt(22,22,5,$id_usuario,$id_rol);
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
        $mensaje = $TramiteController->guardarTramiteDetalle($id_usuario,'',$id_rol,$flujotipo,$tarea,$tabla,$idRie,'',$id_tipoTramite,'',$idTramite,'','');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Tramite')->findBy(array('institucioneducativa'=>$idRie));
        
        $tramite = $entity[0]->getId();

        $mensajeEnvio="El trámite fue enviado correctamente";
        $request->getSession()
            ->getFlashBag()
            ->add('exito', $mensajeEnvio);

        /**imprime comprobante del envio de la solicitud */
        
        $arch = 'TRAMITE_'.$tramite.'_'.$idRie.'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'ritt_infoGral_porCodRitt_v1_afv.rptdesign&cod_ritt='.$idRie.'&nro_tramite='.$tramite.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function ListaTramitesNacAction(Request $request){
        $id_rol= $this->session->get('roluser');
        $id_usuario= $this->session->get('userId');
        $TramiteController = new  TramiteRueController();
        $TramiteController->setContainer($this->container);
        $lista = $TramiteController->tramiteTareaRitt(22,23,5,$id_usuario,$id_rol);
        return $this->render('SieRieBundle:SolicitudRITT:listaTramitesNac.html.twig',array('listaTramites'=>$lista['tramites']));

    }
    public function guardaTramiteNacAction(Request $request){
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
        $id_rol= $this->session->get('roluser');
        $id_usuario= $this->session->get('userId');
        $TramiteController = new  TramiteRueController();
        $TramiteController->setContainer($this->container);
        $lista = $TramiteController->tramiteTareaRitt(23,24,5,$id_usuario,$id_rol);
        return $this->render('SieRieBundle:SolicitudRITT:listaTramitesCertificadosNac.html.twig',array('listaTramites'=>$lista['tramites']));
    }
    public function guardaTramiteNacImprimeAction(Request $request){
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

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
        $TramiteController = new TramiteRueController();
        $TramiteController->setContainer($this->container);
        $DocumentoController = new DocumentoController();
        $DocumentoController->setContainer($this->container);
        $mensaje = $TramiteController->guardarTramiteDetalle($id_usuario,'',$id_rol,$flujotipo,$tarea,$tabla,$idRie,$obs,$id_tipoTramite,$evaluacion,$idTramite,'','');
        /**
         * acreaditar instituto y local educativo
         */
        $em = $this->getDoctrine()->getManager();
        
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($idRie);
        $entityLe = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($entity->getLeJuridicciongeografica()->getId());
        
        $entity->setInstitucioneducativaAcreditacionTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaAcreditacionTipo')->find(2));
        $entityLe->setJuridiccionAcreditacionTipo($em->getRepository('SieAppWebBundle:JurisdiccionGeograficaAcreditacionTipo')->find(2));

        $entityDocumentoTipo = $em->getRepository('SieAppWebBundle:DocumentoTipo')->findOneById(11);
        $entityDocumentoEstado = $em->getRepository('SieAppWebBundle:DocumentoEstado')->findOneById(1);
        $entityUsuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneById($id_usuario);
        $entityTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($idTramite);

        $query = $em->getConnection()->prepare("
        SELECT
        inst.id AS ie_id,
        inst.institucioneducativa,
        inst.fecha_resolucion,
        inst.nro_resolucion,
        instipo.descripcion AS tipo,
        string_agg(nivt.nivel,', ') as nivelautorizado,
        lt4.lugar AS departamento,
        case when dept.id = 3 then 'PRIVADO' else dept.dependencia end as dependencia,
        inst.le_juridicciongeografica_id,
        jurg.zona,
        jurg.direccion,
        case when dependencia_tipo_id = '3' then TO_CHAR(inst.fecha_resolucion + interval '6 year', 'dd/mm/yyyy') else 'INDEFINIDO' end as vigente,
        case when sede.sede <> inst.id then 'SUBSEDE' END AS subsede
        FROM
        institucioneducativa AS inst
        INNER JOIN ttec_institucioneducativa_sede AS sede ON sede.institucioneducativa_id = inst.id
        INNER JOIN institucioneducativa_tipo AS instipo ON inst.institucioneducativa_tipo_id = instipo.id
        INNER JOIN jurisdiccion_geografica AS jurg ON inst.le_juridicciongeografica_id = jurg.id
        LEFT JOIN lugar_tipo AS lt ON lt.id = jurg.lugar_tipo_id_localidad
        LEFT JOIN lugar_tipo AS lt1 ON lt1.id = lt.lugar_tipo_id
        LEFT JOIN lugar_tipo AS lt2 ON lt2.id = lt1.lugar_tipo_id
        LEFT JOIN lugar_tipo AS lt3 ON lt3.id = lt2.lugar_tipo_id
        LEFT JOIN lugar_tipo AS lt4 ON lt4.id = lt3.lugar_tipo_id
        INNER JOIN dependencia_tipo AS dept ON inst.dependencia_tipo_id = dept.id
        INNER JOIN institucioneducativa_nivel_autorizado AS instnivaut ON instnivaut.institucioneducativa_id = inst.id
        INNER JOIN nivel_tipo AS nivt ON instnivaut.nivel_tipo_id = nivt.id
        WHERE inst.id = '".$idRie."' AND
        inst.institucioneducativa_tipo_id IN (7,8,9)
        GROUP BY
        inst.id,
        inst.institucioneducativa,
        inst.fecha_resolucion,
        inst.nro_resolucion,
        instipo.descripcion,
        lt4.lugar,
        dept.id,
        inst.le_juridicciongeografica_id,
        jurg.zona,
        jurg.direccion,
        subsede
        ");
        $query->execute();
        $ritt = $query->fetchAll();

        $serie = 1;

        $query = $em->getConnection()->prepare("
        SELECT max(id::INTEGER) as maximo
        FROM documento_serie a
        WHERE a.documento_tipo_id = 11");
        $query->execute();
        $maxSerie = $query->fetchAll();
        
        if ($maxSerie[0]) {
            $serie = $maxSerie[0]['maximo'] + 1;
        }
        
        $entityDocumentoSerie = new DocumentoSerie();
        $entityDocumentoSerie->setId($serie);
        $entityDocumentoSerie->setGestion($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($this->session->get('currentyear')));
        $entityDocumentoSerie->setDepartamentoTipo($em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneById(0));
        $entityDocumentoSerie->setEsanulado(false);
        $entityDocumentoSerie->setObservacionAnulado('false');
        $entityDocumentoSerie->setObs('Serie Certificado RITT');
        $entityDocumentoSerie->setDocumentoTipo($entityDocumentoTipo);
        $em->persist($entityDocumentoSerie);

        $entityDocumento = new Documento();
        $entityDocumento->setDocumento('');
        $entityDocumento->setDocumentoTipo($entityDocumentoTipo);
        $entityDocumento->setObs($entityDocumentoTipo->getDocumentoTipo() . ' generado');
        $entityDocumento->setDocumentoSerie($entityDocumentoSerie);
        $entityDocumento->setUsuario($entityUsuario);
        $entityDocumento->setFechaImpresion($fechaActual);
        $entityDocumento->setFechaRegistro($fechaActual);
        $entityDocumento->setTramite($entityTramite);
        $entityDocumento->setDocumentoEstado($entityDocumentoEstado);
        $em->persist($entityDocumento);

        $datos = array(
            'tramite'=>$entityTramite->getId(),
            'serie'=>$entityDocumentoSerie->getId(),
            'documento'=>$entityDocumento->getId(),
            'impresionusuario'=>$entityUsuario->getId(),
            'impresionfecha'=>date_format($fechaActual, 'd/m/Y'),
            'ie_id'=>$ritt[0]['ie_id'],
            'le_juridicciongeografica_id'=>$ritt[0]['le_juridicciongeografica_id'],
            'subsede'=>$ritt[0]['subsede'] ? $ritt[0]['subsede'] : '',
        );
        $keys = $DocumentoController->getEncodeRSA($datos);

        $entityDocumento->setTokenPublico($keys['keyPublica']);
        $entityDocumento->setTokenPrivado($keys['keyPrivada']);
        $entityDocumento->setTokenImpreso($keys['token']);
        $em->persist($entityDocumento);
        
        $em->flush();

        $arch = 'CERTIFICADO_'.$idRie.'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rie_cert_certificadottec_v3_afv.rptdesign&documento_id='.$entityDocumento->getId().'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;

    }
    public function vistaPreviaCertificadoAction(Request $request){
        $idRie= $request->get('idRie');

        $arch = 'CERTIFICADO_VP_'.$idRie.'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rie_cert_certificadottec_vp_v3_afv.rptdesign&institucioneducativa_id='.$idRie.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;

    }
    public function TramiteObsAction(Request $request){
        $id = $request->get('td_id')   ;
        $id_rie= $request->get('id_rie');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT tramite_detalle.obs,tramite_detalle.id, tramite_detalle.fecha_registro from tramite_detalle where tramite_detalle.id=$id");
        $query->execute();
        $obs= $query->fetch();

        $response = new JsonResponse();
        return $response->setData(array('obs'=>$obs['obs'],'idtram'=>$obs['id'],'fecha'=>$obs['fecha_registro'],'idrie'=>$id_rie));

    }

    public function listaTramitesConcluidosAction(Request $request){
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)){
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
        SELECT t.id,ie.id as codrie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_fin,'CONCLUIDO' as estado,lt4.lugar
        FROM tramite t
        JOIN institucioneducativa ie ON t.institucioneducativa_id=ie.id
        JOIN jurisdiccion_geografica le ON ie.le_juridicciongeografica_id=le.id
        LEFT JOIN lugar_tipo lt ON lt.id = le.lugar_tipo_id_localidad
        LEFT JOIN lugar_tipo lt1 ON lt1.id = lt.lugar_tipo_id
        LEFT JOIN lugar_tipo lt2 ON lt2.id = lt1.lugar_tipo_id
        LEFT JOIN lugar_tipo lt3 ON lt3.id = lt2.lugar_tipo_id
        LEFT JOIN lugar_tipo lt4 ON lt4.id = lt3.lugar_tipo_id
        JOIN tramite_tipo tt ON t.tramite_tipo=tt.id
        WHERE t.flujo_tipo_id=5 AND t.fecha_fin IS NOT NULL");
        $query->execute();
        $lista= $query->fetchAll();
        return $this->render('SieRieBundle:SolicitudRITT:listaTramitesConcluidos.html.twig',array('listaTramites'=>$lista));
    }

    public function verificarCertificadoAction(Request $request, $qr){
        $em = $this->getDoctrine()->getManager();
        $DocumentoController = new DocumentoController();
        $DocumentoController->setContainer($this->container);
        $datos = str_replace(' ', '+', $qr);
        $datos = str_replace('%20', '+', $datos);
        
        $response = new Response();

        $entityDocumento = $em->getRepository('SieAppWebBundle:Documento')->findOneBy(array('tokenImpreso' => $datos));
        if($entityDocumento){
            $keyPrivada = $entityDocumento->getTokenPrivado();
            $datos = $DocumentoController->getDecodeRSA($datos, $keyPrivada);
            $datos = unserialize($datos);
            
            $arch = 'CERTIFICADO_VP_'.$datos['ie_id'].'_' . date('YmdHis') . '.pdf';
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rie_cert_certificadottec_vp_v3_afv.rptdesign&institucioneducativa_id='.$datos['ie_id'].'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }
        
        return $response;
    }
}
