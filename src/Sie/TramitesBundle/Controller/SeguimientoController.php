<?php

namespace Sie\TramitesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Sie\AppWebBundle\Entity\TramiteDetalle;

use Sie\TramitesBundle\Controller\DefaultController as defaultTramiteController;
use Sie\TramitesBundle\Controller\TramiteController as tramiteController;
use Sie\TramitesBundle\Controller\DocumentoController as documentoController;
use Sie\TramitesBundle\Controller\TramiteDetalleController as tramiteProcesoController;

class SeguimientoController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca un documento en funcion al numero de serie para su seguimiento
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function tramiteSeguimientoDocumentoBuscaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $activeMenu = $defaultTramiteController->setActiveMenu();

        $documentoController = new documentoController();
        $documentoController->setContainer($this->container);

        $rolPermitido = array(8,12,13,14,15,16,17,20,32,33);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Seguimiento:documentoSerieIndex.html.twig', array(
            'formBusqueda' => $documentoController->creaFormBuscaDocumentoSerie('tramite_seguimiento_documento_lista','')->createView(),
            'titulo' => 'Seguimiento',
            'subtitulo' => 'Número de Serie',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista los documento buscados
    // PARAMETROS: request, serie
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function tramiteSeguimientoDocumentoListaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $serie = '';

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if ($request->isMethod('POST')) {
            $form = $request->get('form');
            if ($form) {
                $serie = $form['serie'];
            }  else {
                $serie = $request->get('serie');
            }

            if($serie != ''){
                $documentoController = new documentoController();
                $documentoController->setContainer($this->container);
                try {
                    $entityDocumento = $documentoController->getDocumentoSerie($serie);
                    if(count($entityDocumento)<=0){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información del número de serie ingresado'));
                    }

                    return $this->render($this->session->get('pathSystem') . ':Seguimiento:documentoSerieIndex.html.twig', array(
                        'formBusqueda' => $documentoController->creaFormBuscaDocumentoSerie('tramite_seguimiento_documento_lista',$serie)->createView(),
                        'titulo' => 'Seguimiento',
                        'subtitulo' => 'Número de Serie',
                        'listaBusqueda' => $entityDocumento,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_seguimiento_documento_busca'));
                }
            } else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_seguimiento_documento_busca'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_seguimiento_documento_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista el detalle del tramite en funcion al documento
    // PARAMETROS: request, documentoId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function tramiteSeguimientoDocumentoDetalleAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if ($request->isMethod('POST')) {
            $codigo = $request->get('codigo');
            if($codigo == ""){
                $codigo = 0;
            } else {
                $codigo = base64_decode($codigo);
            }

            $documentoController = new documentoController();
            $documentoController->setContainer($this->container);

            try {
                $entityDocumento = $documentoController->getDocumento($codigo);
                if(count($entityDocumento)<=0){
                    $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información del número documento'));
                    return $this->redirect($this->generateUrl('tramite_seguimiento_documento_busca'));
                }

                $tramiteProcesoController = new tramiteProcesoController();
                $tramiteProcesoController->setContainer($this->container);

                $entityTramiteDetalle = $tramiteProcesoController->getTramiteDetalle($entityDocumento['tramite']);

                $documentoController = new documentoController();
                $documentoController->setContainer($this->container);

                $entityDocumentoDetalle = $documentoController->getDocumentoDetalle($entityDocumento['tramite']);

                return $this->render($this->session->get('pathSystem') . ':Seguimiento:tramiteDetalle.html.twig', array(
                    'titulo' => 'Seguimiento',
                    'subtitulo' => 'Trámite',
                    'listaDocumento' => $entityDocumento,
                    'listaTramiteDetalle' => $entityTramiteDetalle,
                    'listaDocumentoDetalle' => $entityDocumentoDetalle,
                ));
            } catch (\Doctrine\ORM\NoResultException $exc) {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_seguimiento_documento_busca'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_seguimiento_documento_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca un tramite en funcion al numero de tramite para su seguimiento
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function tramiteSeguimientoBuscaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $activeMenu = $defaultTramiteController->setActiveMenu();

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $rolPermitido = array(8,12,13,14,15,16,17,20,32,33);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Seguimiento:tramiteNumeroIndex.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaTramite('tramite_seguimiento_lista','')->createView(),
            'titulo' => 'Seguimiento',
            'subtitulo' => 'Número de Trámite',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista los tramites buscados
    // PARAMETROS: request, serie
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function tramiteSeguimientoListaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if ($request->isMethod('POST')) {
            $form = $request->get('form');
            if ($form) {
                $tramite = $form['tramite'];

                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                try {
                    $entityTramite = $tramiteController->getTramite($tramite);

                    if(count($entityTramite)<=0){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información del número de trámite ingresado'));
                    }

                    return $this->render($this->session->get('pathSystem') . ':Seguimiento:tramiteNumeroIndex.html.twig', array(
                        'formBusqueda' => $tramiteController->creaFormBuscaTramite('tramite_seguimiento_lista',$tramite)->createView(),
                        'titulo' => 'Seguimiento',
                        'subtitulo' => 'Número de Trámite',
                        'listaBusqueda' => $entityTramite,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_seguimiento_busca'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_seguimiento_busca'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_seguimiento_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista el detalle del tramite en funcion al numero de tramite
    // PARAMETROS: request, documentoId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function tramiteSeguimientoDetalleAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if ($request->isMethod('POST')) {
            $codigo = $request->get('codigo');;
            if($codigo == ""){
                $codigo = 0;
            } else {
                $codigo = base64_decode($codigo);
            }

            $tramiteController = new tramiteController();
            $tramiteController->setContainer($this->container);

            try {
                $entityTramite = $tramiteController->getTramite($codigo);
                if(count($entityTramite)<=0){
                    $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información del número de trámite'));
                    return $this->redirect($this->generateUrl('tramite_seguimiento_documento_busca'));
                }

                $entityTramite = $entityTramite[0];

                $tramiteProcesoController = new tramiteProcesoController();
                $tramiteProcesoController->setContainer($this->container);

                $entityTramiteDetalle = $tramiteProcesoController->getTramiteDetalle($entityTramite['tramite']);

                $documentoController = new documentoController();
                $documentoController->setContainer($this->container);

                $entityDocumentoDetalle = $documentoController->getDocumentoDetalle($entityTramite['tramite']);

                return $this->render($this->session->get('pathSystem') . ':Seguimiento:tramiteDetalle.html.twig', array(
                    'titulo' => 'Seguimiento',
                    'subtitulo' => 'Trámite',
                    'listaDocumento' => $entityTramite,
                    'listaTramiteDetalle' => $entityTramiteDetalle,
                    'listaDocumentoDetalle' => $entityDocumentoDetalle,
                ));
            } catch (\Doctrine\ORM\NoResultException $exc) {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_seguimiento_documento_busca'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_seguimiento_documento_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca un tramite en funcion al numero de tramite para su seguimiento
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function tramiteSeguimientoRudeBuscaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $activeMenu = $defaultTramiteController->setActiveMenu();

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $rolPermitido = array(8,12,13,14,15,16,17,20,32,33);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Seguimiento:tramiteRudeIndex.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaTramiteRude('tramite_seguimiento_rude_lista','')->createView(),
            'titulo' => 'Seguimiento',
            'subtitulo' => 'Número de Trámite',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista los tramites buscados
    // PARAMETROS: request, serie
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function tramiteSeguimientoRudeListaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if ($request->isMethod('POST')) {
            $form = $request->get('form');
            if ($form) {
                $rude = $form['rude'];

                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                try {
                    $entityTramiteRude = $tramiteController->getTramiteRude($rude);

                    if(count($entityTramiteRude)<=0){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información del código rude ingresado'));
                        return $this->redirect($this->generateUrl('tramite_seguimiento_rude_busca'));
                    }

                    return $this->render($this->session->get('pathSystem') . ':Seguimiento:tramiteRudeIndex.html.twig', array(
                        'formBusqueda' => $tramiteController->creaFormBuscaTramiteRude('tramite_seguimiento_rude_lista',$rude)->createView(),
                        'titulo' => 'Seguimiento',
                        'subtitulo' => 'Número de Trámite',
                        'listaBusqueda' => $entityTramiteRude,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_seguimiento_rude_busca'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_seguimiento_rude_busca'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_seguimiento_rude_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista el detalle del tramite en funcion al codigo rude
    // PARAMETROS: request, tramiteId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function tramiteSeguimientoRudeDetalleAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if ($request->isMethod('POST')) {
            $codigo = $request->get('codigo');;
            if($codigo == ""){
                $codigo = 0;
            } else {
                $codigo = base64_decode($codigo);
            }

            $tramiteController = new tramiteController();
            $tramiteController->setContainer($this->container);

            try {
                $entityTramite = $tramiteController->getTramite($codigo);
                if(count($entityTramite)<=0){
                    $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información del código rude'));
                    return $this->redirect($this->generateUrl('tramite_seguimiento_rude_busca'));
                }

                $entityTramite = $entityTramite[0];

                $tramiteProcesoController = new tramiteProcesoController();
                $tramiteProcesoController->setContainer($this->container);

                $entityTramiteDetalle = $tramiteProcesoController->getTramiteDetalle($entityTramite['tramite']);

                $documentoController = new documentoController();
                $documentoController->setContainer($this->container);

                $entityDocumentoDetalle = $documentoController->getDocumentoDetalle($entityTramite['tramite']);

                return $this->render($this->session->get('pathSystem') . ':Seguimiento:tramiteDetalle.html.twig', array(
                    'titulo' => 'Seguimiento',
                    'subtitulo' => 'Trámite',
                    'listaDocumento' => $entityTramite,
                    'listaTramiteDetalle' => $entityTramiteDetalle,
                    'listaDocumentoDetalle' => $entityDocumentoDetalle,
                ));
            } catch (\Doctrine\ORM\NoResultException $exc) {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_seguimiento_rude_busca'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_seguimiento_rude_busca'));
        }
    }

        //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca un tramite en funcion a la cedula de identidad del bachiller para su seguimiento
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function tramiteSeguimientoCedulaBuscaAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $activeMenu = $defaultTramiteController->setActiveMenu();

        $tramiteController = new tramiteController();
        $tramiteController->setContainer($this->container);

        $rolPermitido = array(8,12,13,14,15,16,17,20,32,33);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Seguimiento:tramiteCedulaIndex.html.twig', array(
            'formBusqueda' => $tramiteController->creaFormBuscaTramiteCedula('tramite_seguimiento_cedula_lista','')->createView(),
            'titulo' => 'Seguimiento',
            'subtitulo' => 'Número de Cédula de Identidad',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista los tramites buscados en función a la cedula de identidad
    // PARAMETROS: request, serie
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function tramiteSeguimientoCedulaListaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if ($request->isMethod('POST')) {
            $form = $request->get('form');
            if ($form) {
                $cedula = $form['cedula'];

                $tramiteController = new tramiteController();
                $tramiteController->setContainer($this->container);

                try {
                    $entityTramiteCedula = $tramiteController->getTramiteCedula($cedula);

                    if(count($entityTramiteCedula)<=0){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la cédula de identidad ingresada'));
                        return $this->redirect($this->generateUrl('tramite_seguimiento_cedula_busca'));
                    }

                    return $this->render($this->session->get('pathSystem') . ':Seguimiento:tramiteCedulaIndex.html.twig', array(
                        'formBusqueda' => $tramiteController->creaFormBuscaTramiteCedula('tramite_seguimiento_cedula_lista',$cedula)->createView(),
                        'titulo' => 'Seguimiento',
                        'subtitulo' => 'Número de Cédula de Indentidad',
                        'listaBusqueda' => $entityTramiteCedula,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_seguimiento_cedula_busca'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_seguimiento_cedula_busca'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_seguimiento_cedula_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista el detalle del tramite en funcion al cedula de identidad
    // PARAMETROS: request, tramiteId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function tramiteSeguimientoCedulaDetalleAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if ($request->isMethod('POST')) {
            $codigo = $request->get('codigo');;
            if($codigo == ""){
                $codigo = 0;
            } else {
                $codigo = base64_decode($codigo);
            }

            $tramiteController = new tramiteController();
            $tramiteController->setContainer($this->container);

            try {
                $entityTramite = $tramiteController->getTramite($codigo);

                if(count($entityTramite)<=0){
                    $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la cédula de identidad'));
                    return $this->redirect($this->generateUrl('tramite_seguimiento_cedula_busca'));
                }

                $entityTramite = $entityTramite[0];

                $tramiteProcesoController = new tramiteProcesoController();
                $tramiteProcesoController->setContainer($this->container);

                $entityTramiteDetalle = $tramiteProcesoController->getTramiteDetalle($entityTramite['tramite']);

                $documentoController = new documentoController();
                $documentoController->setContainer($this->container);

                $entityDocumentoDetalle = $documentoController->getDocumentoDetalle($entityTramite['tramite']);

                return $this->render($this->session->get('pathSystem') . ':Seguimiento:tramiteDetalle.html.twig', array(
                    'titulo' => 'Seguimiento',
                    'subtitulo' => 'Trámite',
                    'listaDocumento' => $entityTramite,
                    'listaTramiteDetalle' => $entityTramiteDetalle,
                    'listaDocumentoDetalle' => $entityDocumentoDetalle,
                ));
            } catch (\Doctrine\ORM\NoResultException $exc) {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_seguimiento_cedula_busca'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_seguimiento_cedula_busca'));
        }
    }
}
