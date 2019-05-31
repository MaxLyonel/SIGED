<?php

namespace Sie\TramitesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Sie\AppWebBundle\Entity\DocumentoFirmaAutorizada;

use Sie\TramitesBundle\Controller\DefaultController as defaultTramiteController;
use Sie\TramitesBundle\Controller\TramiteDetalleController as tramiteProcesoController;
use Sie\TramitesBundle\Controller\TramiteController as tramiteController;
use Sie\TramitesBundle\Controller\DocumentoController as documentoController;

use phpseclib\Crypt\RSA;

class DocumentoFirmaController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca las autorizaciones concedidas para el uso de la firma segun la persona 
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumAutorizaFirmaListaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();
        $route = $request->get('_route');

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // $defaultTramiteController = new defaultTramiteController();
        // $defaultTramiteController->setContainer($this->container);

        // activeMenu = $defaultTramiteController->setActiveMenu($route);

        $documentoController = new documentoController();
        $documentoController->setContainer($this->container);

        // $rolPermitido = array(8,16);

        // $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        // if (!$esValidoUsuarioRol){
        //     $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
        //     return $this->redirect($this->generateUrl('tramite_homepage'));
        // }

        $gestion = $gestionActual->format('Y');
        
        $em = $this->getDoctrine()->getManager();
        $entityUsuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $id_usuario));
        $personaId = $entityUsuario->getPersona()->getId();
        $entityFirmaAutorizada = $documentoController->getFirmaAutorizada($personaId);
        
        $entidadDocumentoFirma = $em->getRepository('SieAppWebBundle:DocumentoFirma')->findBy(array('persona' => $personaId, 'esactivo' => true), array('id' => 'ASC'));

        if(count($entidadDocumentoFirma) > 0){
            $entityPersona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('id' => $personaId));

            $arrayPersona = array();
            foreach ($entityPersona as $dato) {
                $arrayPersona[base64_encode($entityPersona->getId())] = $entityPersona->getNombre().' '.$entityPersona->getPaterno().' '.$entityPersona->getMaterno();
            }

            $entityDocumentoTipo = $em->getRepository('SieAppWebBundle:DocumentoTipo')->findBy(array('id' => array(1,2)));

            $arrayDocumentoTipo = array(''=>'Seleccione el tipo de documento');
            foreach ($entityDocumentoTipo as $dato) {
                $arrayDocumentoTipo[base64_encode($dato->getId())] = $dato->getDocumentoTipo();
            }
            
            return $this->render($this->session->get('pathSystem') . ':DocumentoFirma:AutorizaIndex.html.twig', array(
                'form' => $this->creaFormDipHumAutorizaFirma('tramite_documento_firma_diploma_humanistico_autorizacion_guarda',$arrayPersona,null,'',$arrayDocumentoTipo)->createView(),
                'titulo' => 'Autorización Firma',
                'subtitulo' => 'Diploma Humanísico',
                'firmas' => $entityFirmaAutorizada
            ));
        } else {
            return $this->render($this->session->get('pathSystem') . ':DocumentoFirma:AutorizaIndex.html.twig', array(                
                'titulo' => 'Autorización Firma',
                'subtitulo' => 'Diploma Humanísico',
                'firmas' => $entityFirmaAutorizada
            ));
        }
    }




    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que guarda las autorizaciones concedidas para el uso de la firma segun la persona 
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumAutorizaFirmaGuardaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();
        $fechaRegistro = new \DateTime();
        $route = $request->get('_route');

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }


        $em = $this->getDoctrine()->getManager();

        $form = $request->get('form');
        $personaId = base64_decode($form['persona']);
        $maximo = $form['maximo'];
        $documentoTipoId = base64_decode($form['documentoTipo']);
        $obs = $form['obs'];

        $em->getConnection()->beginTransaction();
        try {            
            $entityPersona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('id' => $personaId));
            $entityDocumentoTipo = $em->getRepository('SieAppWebBundle:DocumentoTipo')->findOneBy(array('id' => $documentoTipoId));
            $entityUsuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $id_usuario));

            $entity = new DocumentoFirmaAutorizada();
            $entity->setPersona($entityPersona);
            $entity->setMaximo($maximo);
            $entity->setUsado(0);
            $entity->setUsuario($entityUsuario);
            $entity->setEsactivo(true);
            $entity->setObs($obs);
            $entity->setFechaRegistro($fechaRegistro);
            $entity->setDocumentoTipo($entityDocumentoTipo);
            $em->persist($entity);
            $em->flush();
            $em->getConnection()->commit();
            $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Firma autorizada'));
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $msg = "Error al autorizar las firma seleccionada, intente nuevamente por favor";
            $em->getConnection()->rollback();
        }

        return $this->redirectToRoute('tramite_documento_firma_diploma_humanistico_autorizacion_lista', ['form' => $form], 307);
        
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que genera un formulario para la busqueda de documentos por numero de serie que sera anulados al reactivar su trámite
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function creaFormDipHumAutorizaFirma($routing, $persona, $maximo, $obs, $documentoTipo) {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl($routing))
            ->add('persona','choice',
                      array('label' => 'Persona',
                            'choices' => $persona,
                            'data' => '', 'attr' => array('class' => 'form-control')))
            ->add('maximo', 'number', array('label' => 'Cantidad Máxima', 'attr' => array('value' => $maximo, 'class' => 'form-control', 'placeholder' => 'Cantidad máxima que se autorizará', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
            ->add('obs', 'textarea', array('label' => 'OBS.', 'attr' => array('value' => $obs, 'class' => 'form-control', 'placeholder' => 'Comentario', 'pattern' => '^@?(\w){1,200}$', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
            ->add('documentoTipo','choice',
                      array('label' => 'Tipo de Documento',
                            'choices' => $documentoTipo,
                            'data' => '', 'attr' => array('class' => 'form-control')))
            ->add('save', 'submit', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();
        return $form;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que anula una determinada autorizacion 
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumAutorizaFirmaCambiaEstadoAction($registro) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $em = $this->getDoctrine()->getManager();
        $respuesta = array('0'=>false, '1'=>'');  ;

        $estado = false;


        $entityDocumentoFirmaAutorizada = $em->getRepository('SieAppWebBundle:DocumentoFirmaAutorizada')->findOneBy(array('id'=>base64_decode($registro)));
        
        if ($entityDocumentoFirmaAutorizada) {
            $utilizado = $entityDocumentoFirmaAutorizada->getUsado();
            $esactivo = $entityDocumentoFirmaAutorizada->getEsactivo();
            $em->getConnection()->beginTransaction();
            try{
                if($esactivo){
                    if($utilizado == 0){
                        $entityDocumentoFirmaAutorizada->setEsactivo(false);
                        $estado = false;
                    } else {
                        $respuesta = array('0'=>$esactivo, '1'=>'No puede cambiar el estado la autorización'); 
                        $estado = $esactivo;
                    }
                } else {
                    $entityDocumentoFirmaAutorizada->setEsactivo(true);
                    $estado = true;
                }
                
                $em->persist($entityDocumentoFirmaAutorizada);
                $em->flush();
                $em->getConnection()->commit();
                $respuesta = array('0'=>$estado, '1'=>'Cambio de estado correcto');  
            } catch (Exception $e) {
                $em->getConnection()->rollback();
                $respuesta = array('0'=>$esactivo, '1'=>'Error, No puede cambiar el estado la autorización, intentelo nuevamente');    
            }
        } 
        $response = new JsonResponse();
        return $response->setData(array('aregistro' => $respuesta));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion accion que lista las firmas activas registradas en el sistema 
    // PARAMETROS: 
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function listaAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select df.id, lt.lugar, pt.persona as tipo, p.nombre, p.paterno, p.materno, p.nombre || ' ' || p.paterno || ' ' || p.materno as persona
            , encode(df.firma,'escape') as firma, df.fecha_registro 
            from documento_firma as df
            inner join persona as p on p.id = df.persona_id
            inner join lugar_tipo as lt on lt.id = df.lugar_tipo_id
            inner join persona_tipo as pt on pt.id = df.persona_tipo_id
            where df.esactivo = true
            order by pt.persona, lt.lugar, p.nombre, p.paterno, p.materno, df.id
        ");
        $query->execute();
        $entity = $query->fetchAll();

        return $this->render($this->session->get('pathSystem') . ':DocumentoFirma:firmaLista.html.twig', array(                
            'titulo' => 'Firma Electrónica',
            'subtitulo' => 'Lista',
            'firmas' => $entity
        ));
    }

    public function modificaAction(Request $request){
        // get the send values
    	$firma = base64_decode($request->get('firma'));
    	$id = $request->get('id');
    	// $arrData = json_decode($jsonData,true);
    	//$arrCouchs = $this->getTheCouch(json_decode($jsonData,true));
    	// $showOptionRegister = sizeof($arrCouchs)>0?false:true;
        $showOptionRegister = true;
    	return $this->render($this->session->get('pathSystem') . ':DocumentoFirma:firmaModifica.html.twig',array(
    		'form'=>$this->creaFormularioFirma($firma, $id)->createView(),
    	));
    }

    private function creaFormularioFirma($firmaId, $id)
    { 
        $form = $this->createFormBuilder()
            ->add('id', 'hidden', array('attr' => array('value' => $id))) 
            ->add('firma', 'hidden', array('attr' => array('value' => base64_encode($firmaId))))            
            ->add('foto', 'file', array('label' => 'Fotografía (.png)', 'required' => true)) 
            ->add('save', 'button', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-blue', 'onclick'=>'confirma()')))
            ->getForm();
        return $form;        
    }

    public function modificaGuardaAction(Request $request){
        $msg_correcto = "";
        $msg_incorrecto = "";
    	// get the send values
    	$firma = base64_decode($request->get('firma'));
       
        $form = $request->get('form');
        $file = $request->files->get('form');
        if($form and $file){
            $foto = $file['foto'];
            $documentoFirmaId = base64_decode($form['firma']);
            $strm = fopen($foto->getRealPath(),'rb');
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try{        
            $entityDocumentoFirma = $em->getRepository('SieAppWebBundle:DocumentoFirma')->findOneBy(array('id' => $documentoFirmaId));
                $entityDocumentoFirma->setFirma(base64_encode(stream_get_contents($strm)));
                $entityDocumentoFirma->setObs('Modificado');
                $em->persist($entityDocumentoFirma);
                $em->flush(); 
                $em->getConnection()->commit();
                $msg_correcto = "Modificado correctamente";
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
                $msg_incorrecto = "Error al realizar la modificación, intente nuevamente";
            }
        } else {
            $msg_incorrecto = "Error al procesar el formulario, intente nuevamente";
        }

        $response = new JsonResponse();
        return $response->setData(
            array(
                'firma1' => (($entityDocumentoFirma->getFirma())), 
                'msg_correcto' => $msg_correcto, 
                'msg_incorrecto' => $msg_incorrecto
            )
        );
    }
}
