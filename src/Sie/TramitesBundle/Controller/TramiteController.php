<?php

namespace Sie\TramitesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Sie\AppWebBundle\Entity\Tramite;

use Sie\TramitesBundle\Controller\DefaultController as defaultTramiteController;
use Sie\TramitesBundle\Controller\TramiteDetalleController as tramiteProcesoController;
use Sie\TramitesBundle\Controller\DocumentoController as documentoController;

class TramiteController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista un tramite en funcion al numero de trámite
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getTramite($tramite) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Tramite');
        $query = $entity->createQueryBuilder('t')
                ->select("t.id as id, t.id as tramite, ds.id as serie, d.fechaImpresion as fechaemision, dept.departamento as departamentoemision, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.documentoTipo as documentoTipo, (case e.complemento when '' then e.carnetIdentidad when 'null' then e.carnetIdentidad else CONCAT(CONCAT(e.carnetIdentidad,'-'),e.complemento) end) as carnetIdentidad, tt.tramiteTipo as tramiteTipo")
                ->innerJoin('SieAppWebBundle:TramiteTipo', 'tt', 'WITH', 'tt.id = t.tramiteTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.id = t.estudianteInscripcion')
                ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->innerJoin('SieAppWebBundle:PaisTipo', 'pt', 'WITH', 'pt.id = e.paisTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'ltp.id = e.lugarProvNacTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp    .lugarTipo')
                ->leftJoin('SieAppWebBundle:Documento', 'd', 'WITH', 'd.tramite = t.id and d.documentoEstado = 1 and d.documentoTipo in (1,3,4,5,6,7,8,9)')
                ->leftJoin('SieAppWebBundle:DocumentoEstado', 'de', 'WITH', 'de.id = d.documentoEstado AND de.id = 1')
                ->leftJoin('SieAppWebBundle:DocumentoTipo', 'dt', 'WITH', 'dt.id = d.documentoTipo')
                ->leftJoin('SieAppWebBundle:DocumentoSerie', 'ds', 'WITH', 'ds.id = d.documentoSerie')
                ->leftJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = ds.gestion')
                ->leftJoin('SieAppWebBundle:DepartamentoTipo', 'dept', 'WITH', 'dept.id = ds.departamentoTipo')
                ->where('t.id = :codTramite')
                ->setParameter('codTramite', $tramite);
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista un tramite en funcion al codigo rude
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getTramiteRude($rude) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Tramite');
        $query = $entity->createQueryBuilder('t')
                ->select("t.id as id, t.id as tramite, ds.id as serie, d.fechaImpresion as fechaemision, dept.departamento as departamentoemision, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.documentoTipo as documentoTipo, (case e.complemento when '' then e.carnetIdentidad when 'null' then e.carnetIdentidad else CONCAT(CONCAT(e.carnetIdentidad,'-'),e.complemento) end) as carnetIdentidad, tt.tramiteTipo as tramiteTipo")
                ->innerJoin('SieAppWebBundle:TramiteTipo', 'tt', 'WITH', 'tt.id = t.tramiteTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.id = t.estudianteInscripcion')
                ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->innerJoin('SieAppWebBundle:PaisTipo', 'pt', 'WITH', 'pt.id = e.paisTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'ltp.id = e.lugarProvNacTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp    .lugarTipo')
                ->leftJoin('SieAppWebBundle:Documento', 'd', 'WITH', 'd.tramite = t.id and d.documentoEstado = 1 and d.documentoTipo in (1,3,4,5,6,7,8,9)')
                ->leftJoin('SieAppWebBundle:DocumentoEstado', 'de', 'WITH', 'de.id = d.documentoEstado')
                ->leftJoin('SieAppWebBundle:DocumentoTipo', 'dt', 'WITH', 'dt.id = d.documentoTipo')
                ->leftJoin('SieAppWebBundle:DocumentoSerie', 'ds', 'WITH', 'ds.id = d.documentoSerie')
                ->leftJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = ds.gestion')
                ->leftJoin('SieAppWebBundle:DepartamentoTipo', 'dept', 'WITH', 'dept.id = ds.departamentoTipo')
                ->where('e.codigoRude = :codRude')
                ->setParameter('codRude', $rude);
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista un tramite en funcion a la cédula de identidad
    // PARAMETROS: id
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getTramiteCedula($cedula) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Tramite');
        $query = $entity->createQueryBuilder('t')
                ->select("t.id as id, t.id as tramite, ds.id as serie, d.fechaImpresion as fechaemision, dept.departamento as departamentoemision, e.codigoRude as rude, e.paterno as paterno, e.materno as materno, e.nombre as nombre, ie.id as sie, ie.institucioneducativa as institucioneducativa, gt.id as gestion, e.fechaNacimiento as fechanacimiento, (case pt.id when 1 then ltd.lugar else '' end) as departamentonacimiento, pt.pais as paisnacimiento, pt.id as codpaisnacimiento, dt.documentoTipo as documentoTipo, (case e.complemento when '' then e.carnetIdentidad when 'null' then e.carnetIdentidad else CONCAT(CONCAT(e.carnetIdentidad,'-'),e.complemento) end) as carnetIdentidad, tt.tramiteTipo as tramiteTipo")
                ->innerJoin('SieAppWebBundle:TramiteTipo', 'tt', 'WITH', 'tt.id = t.tramiteTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ei.id = t.estudianteInscripcion')
                ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->innerJoin('SieAppWebBundle:PaisTipo', 'pt', 'WITH', 'pt.id = e.paisTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'ltp.id = e.lugarProvNacTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp    .lugarTipo')
                ->leftJoin('SieAppWebBundle:Documento', 'd', 'WITH', 'd.tramite = t.id and d.documentoEstado = 1 and d.documentoTipo in (1,3,4,5,6,7,8,9)')
                ->leftJoin('SieAppWebBundle:DocumentoEstado', 'de', 'WITH', 'de.id = d.documentoEstado')
                ->leftJoin('SieAppWebBundle:DocumentoTipo', 'dt', 'WITH', 'dt.id = d.documentoTipo')
                ->leftJoin('SieAppWebBundle:DocumentoSerie', 'ds', 'WITH', 'ds.id = d.documentoSerie')
                ->leftJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = ds.gestion')
                ->leftJoin('SieAppWebBundle:DepartamentoTipo', 'dept', 'WITH', 'dept.id = ds.departamentoTipo')
                ->where('e.carnetIdentidad = :cedula')
                ->setParameter('cedula', $cedula);
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que busca un centro de educacion alternativa tecnica
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecRegistroBuscaAction(Request $request) {
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

        $rolPermitido = array(8,10,13);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        /*
        if ($request->isMethod('POST')) {
            $form = $request->get('form');
            if ($form) {
                $sie = $form['sie'];
                $gestion = $form['gestion'];
            }  else {
                $sie = $request->get('sie');
                $gestion = $request->get('gestion');
                if ($sie == ''){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_homepage'));
                }
            }
        } */
        //else {
        //    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar información, intente nuevamente'));
        //    return $this->redirect($this->generateUrl('tramite_homepage'));
        //}

        return $this->render($this->session->get('pathSystem') . ':Tramite:index.html.twig', array(
            'formBusqueda' => $this->creaFormBuscaCentroEducacionAlternativaTecnica('tramite_certificado_tecnico_registro_lista',0,0,0,0)->createView(),
            'titulo' => 'Registro',
            'subtitulo' => 'Trámite - Certificación Técnica Alternativa',
            ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que muestra los roles del usuario
    // PARAMETROS: gestionId (gestion cuando se inicio el sistema)
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    function getGestiones($gestionId) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:GestionTipo');
        $query = $entity->createQueryBuilder('gt')
                ->where('gt.id >= :id')
                ->setParameter('id', $gestionId)
                ->getQuery();
        try {
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $exc) {
            return array();
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que genera un formulario para la busqueda de centros de educacion alternativa tecnica por gestion
    // PARAMETROS: institucionEducativaId, gestionId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function creaFormBuscaCentroEducacionAlternativaTecnica($routing, $institucionEducativaId, $gestionId, $especialidadId, $nivelId) {
        $em = $this->getDoctrine()->getManager();
        $entidadGestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $gestionId));
        $entidadEspecialidadTipo = $this->getEspecialidadCentroEducativoTecnica($institucionEducativaId, $gestionId);
        $entidadEspecialidad = array();
        foreach ($entidadEspecialidadTipo as $esp)
        {
           $entidadEspecialidad[$esp['especialidad_id']] = $esp['especialidad'];
        }
        $entidadNivelTipo = $this->getNivelCentroEducativoTecnica($institucionEducativaId, $gestionId, $especialidadId);
        $entidadNivel = array();
        foreach ($entidadNivelTipo as $niv)
        {
           $entidadNivel[$niv['nivel_id']] = $niv['nivel'];
        }
        if($institucionEducativaId==0){
            $institucionEducativaId = "";
        }
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('sie', 'number', array('label' => 'SIE', 'attr' => array('value' => $institucionEducativaId, 'class' => 'form-control', 'placeholder' => 'Código de institución educativa', 'onInput' => 'valSie()', ' onchange' => 'valSieFocusOut()', 'pattern' => '[0-9]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'entity', array('data' => $entidadGestionTipo, 'empty_value' => 'Seleccione Gestión', 'attr' => array('class' => 'form-control', 'onchange' => 'listar_especialidad(this.value)'), 'disabled' => 'disabled', 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('gt')
                                ->where('gt.id > 2008')
                                ->orderBy('gt.id', 'DESC');
                    },
                ))
                ->add('especialidad', 'choice', array('label' => 'Especialidad', 'empty_value' => 'Seleccione Especialidad', 'choices' => $entidadEspecialidad, 'data' => $especialidadId, 'attr' => array('class' => 'form-control', 'disabled' => 'disabled', 'onchange' => 'listar_nivel(this.value)', 'required' => true)))
                ->add('nivel', 'choice', array('label' => 'Nivel', 'empty_value' => 'Seleccione Nivel', 'choices' => $entidadNivel, 'data' => $nivelId, 'attr' => array('class' => 'form-control', 'required' => true, 'disabled' => 'disabled', 'onchange' => 'habilitarSubmit()')))
                ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue', 'disabled' => 'disabled')))
                ->getForm();
        return $form;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra el listado de participantes para el registro de su tramite certificacion tecnica alternativa
    // PARAMETROS: institucionEducativaId, gestionId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecRegistroListaAction(Request $request) {
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
                $sie = $form['sie'];
                $gestion = $form['gestion'];
                $especialidad = $form['especialidad'];
                $nivel = $form['nivel'];

                try {

                    $usuarioRol = $this->session->get('roluser');
                    $verTuicionUnidadEducativa = $this->verTuicionUnidadEducativa($id_usuario, $sie, $usuarioRol, 'DIPLOMAS');

                    if ($verTuicionUnidadEducativa != ''){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $verTuicionUnidadEducativa));
                        return $this->redirect($this->generateUrl('tramite_diploma_humanistico_regular_registro_busca'));
                    }

                    $entityAutorizacionCentro = $this->getAutorizacionCentroEducativoTecnica($sie);
                    if(count($entityAutorizacionCentro)<=0){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información de la unidad educativa, intente nuevamente'));
                    }

                    $entityParticipantes = $this->getEstudiantesAlternativaTecnica($sie,$gestion,$especialidad,$nivel);

                    //$i = 0;
                    //foreach ($entityParticipantes as $participante){
                    //    $i = $i + 1;
                    //}

                    $datosBusqueda = base64_encode(serialize($form));

                    return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:registroIndex.html.twig', array(
                        'formBusqueda' => $this->creaFormBuscaCentroEducacionAlternativaTecnica('tramite_certificado_tecnico_registro_lista',$sie,$gestion,$especialidad,$nivel)->createView(),
                        'titulo' => 'Registro',
                        'subtitulo' => 'Trámite',
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionCentro' => $entityAutorizacionCentro,
                        'datosBusqueda' => $datosBusqueda,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_certificado_tecnico_registro_busca'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_certificado_tecnico_registro_busca'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_certificado_tecnico_registro_busca'));
        }
        return $this->redirect($this->generateUrl('login'));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra el trámite de los participantes selecionados
    // PARAMETROS: estudiantes[], boton
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecRegistroGuardaAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $institucioneducativaId = 0;
        $gestionId = $gestionActual->format('Y');
        $especialidadId = 0;
        $nivelId = 0;
        $flujoTipoId = 4;
        $tramiteTipoId = 0;
        $flujoSeleccionado = '';

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $participantes = $request->get('participantes');
                if (isset($_POST['botonAceptar'])) {
                    $flujoSeleccionado = 'Adelante';
                }
                $token = $request->get('_token');
                if (!$this->isCsrfTokenValid('registrar', $token)) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                    return $this->redirectToRoute('tramite_certificado_tecnico_registro_busca');
                }

                $messageCorrecto = "";
                $messageError = "";
                foreach ($participantes as $participante) {
                    $estudianteInscripcionId = (Int) base64_decode($participante);

                    $entidadEstudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
                    $participante = trim($entidadEstudianteInscripcion->getEstudiante()->getPaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getMaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getNombre());
                    $participanteId =  $entidadEstudianteInscripcion->getEstudiante()->getId();
                    $msgContenido = "";
                    if(count($entidadEstudianteInscripcion)>0){
                        $nivelId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getAcreditacionEspecialidad()->getSuperiorAcreditacionTipo()->getCodigo();
                        $especialidadId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getAcreditacionEspecialidad()->getSuperiorEspecialidadTipo()->getId();
                        $institucionEducativaId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getInstitucioneducativa()->getId();
                        $gestionId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getSuperiorInstitucioneducativaPeriodo()->getSuperiorInstitucioneducativaAcreditacion()->getInstitucioneducativaSucursal()->getGestionTipo()->getId();

                        $msg = array('0'=>true, '1'=>$participante);
                        $msgContenido = $this->getCertTecValidacion($participanteId, $especialidadId, $nivelId, $gestionId);

                        // VALIDACION DE SOLO UN TRAMITE POR ESTUDIANTE (RUDE)
                        $valCertTecTramiteEspNivel = $this->getCertTecTramiteEspecialidadNivelEstudiante($participanteId, $especialidadId, $nivelId);
                        if(count($valCertTecTramiteEspNivel) > 0){
                            $msgContenido = ($msgContenido=="") ? 'ya cuenta con el trámite '.$valCertTecTramiteEspNivel[0]['tramite_id'] : $msgContenido.', ya cuenta con el trámite '.$valCertTecTramiteEspNivel[0]['tramite_id'];
                        }

                        if($msgContenido != ""){
                            $msg = array('0'=>false, '1'=>$participante.' ('.$msgContenido.')');
                        }

                    } else {
                        $msg = array('0'=>false, '1'=>'participante no encontrado');
                    }

                    if ($msg[0]) {
                        switch ($nivelId) {
                            case 1:
                                $tramiteTipoId = 6;
                                break;
                           case 2:
                                $tramiteTipoId = 7;
                                break;
                           case 3:
                                $tramiteTipoId = 8;
                                break;
                            default:
                                $tramiteTipoId = 0;
                                break;
                        }

                        $tramiteId = $this->setTramiteEstudiante($estudianteInscripcionId, $gestionId, $tramiteTipoId, $flujoTipoId, $em);

                        $tramiteProcesoController = new tramiteProcesoController();
                        $tramiteProcesoController->setContainer($this->container);

                        $tramiteDetalleId = $tramiteProcesoController->setProcesaTramiteInicio($tramiteId, $id_usuario, 'REGISTRO DEL TRÁMITE', $em);

                        $messageCorrecto = ($messageCorrecto == "") ? $msg[1] : $messageCorrecto.'; '.$msg[1];
                    } else {
                        $messageError = ($messageError == "") ? $msg[1] : $messageError.'; '.$msg[1];
                    }
                }
                if($messageCorrecto!=""){
                    $em->getConnection()->commit();
                    $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => $messageCorrecto));
                }
                if($messageError!=""){
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $messageError));
                }
            } catch (\Doctrine\ORM\NoResultException $exc) {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
            }

            //dump($messageError);
            //die();

            $entityAutorizacionCentro = $this->getAutorizacionCentroEducativoTecnica($institucionEducativaId);
            $entityParticipantes = $this->getEstudiantesAlternativaTecnica($institucionEducativaId,$gestionId,$especialidadId,$nivelId);
            /*
            return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:registroIndex.html.twig', array(
                'formBusqueda' => $this->creaFormBuscaInstitucionEducativa('sie_tramite_certificado_tecnico_registro_lista',$institucionEducativaId,$gestionId,$especialidadId,$nivelId)->createView(),
                'titulo' => 'Registro',
                'subtitulo' => 'Trámite',
                'listaParticipante' => $entityParticipantes,
                'infoAutorizacionCentro' => $entityAutorizacionCentro,
            ));
            */

            $formBusqueda = array('sie'=>$institucionEducativaId,'gestion'=>$gestionId,'especialidad'=>$especialidadId,'nivel'=>$nivelId);
            return $this->redirectToRoute('tramite_certificado_tecnico_registro_lista', ['form' => $formBusqueda], 307);
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_certificado_tecnico_registro_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que despliega un formulario para buscar una unidad educativa humanistica
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumRegularRegistroBuscaAction(Request $request) {
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

        $rolPermitido = array(8,10,13);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Tramite:dipHumIndex.html.twig', array(
            'formBusqueda' => $this->creaFormBuscaUnidadEducativaHumanistica('tramite_diploma_humanistico_regular_registro_lista',0,0)->createView(),
            'titulo' => 'Registro',
            'subtitulo' => 'Trámite - Diploma Humanístico Regular',
            ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que despliega un formulario para buscar un centro de educacioón alternativa humanistica
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumAlternativaRegistroBuscaAction(Request $request) {
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

        $rolPermitido = array(8,10,13);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Tramite:dipHumIndex.html.twig', array(
            'formBusqueda' => $this->creaFormBuscaUnidadEducativaHumanistica('tramite_diploma_humanistico_alternativa_registro_lista',0,0)->createView(),
            'titulo' => 'Registro',
            'subtitulo' => 'Trámite - Diploma Humanístico Alternativa',
            ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que genera un formulario par ala busqueda de unidades educativas humanistica por gestion
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function creaFormBuscaUnidadEducativaHumanistica($routing, $institucionEducativaId, $gestionId) {
        $em = $this->getDoctrine()->getManager();
        $entidadGestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $gestionId));
        if($institucionEducativaId==0){
            $institucionEducativaId = "";
        }
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('sie', 'number', array('label' => 'SIE', 'attr' => array('value' => $institucionEducativaId, 'class' => 'form-control', 'placeholder' => 'Código de institución educativa', 'onInput' => 'valSie()', ' onchange' => 'valSieFocusOut()', 'pattern' => '[0-9]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'entity', array('data' => $entidadGestionTipo, 'empty_value' => 'Seleccione Gestión', 'attr' => array('class' => 'form-control', 'onchange' => 'listar_especialidad(this.value)'), 'disabled' => 'disabled', 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('gt')
                                ->where('gt.id > 2008')
                                ->orderBy('gt.id', 'DESC');
                    },
                ))
                ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue', 'disabled' => 'disabled')))
                ->getForm();
        return $form;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra el listado de bachilleres para el registro de su tramite diplomas humanistico regular
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumRegularRegistroListaAction(Request $request) {
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
                $sie = $form['sie'];
                $gestion = $form['gestion'];

                try {
                    $usuarioRol = $this->session->get('roluser');
                    $verTuicionUnidadEducativa = $this->verTuicionUnidadEducativa($id_usuario, $sie, $usuarioRol, 'TRAMITES');

                    if ($verTuicionUnidadEducativa != ''){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $verTuicionUnidadEducativa));
                        return $this->redirect($this->generateUrl('tramite_diploma_humanistico_regular_registro_busca '));
                    }

                    $entityAutorizacionUnidadEducativa = $this->getAutorizacionUnidadEducativa($sie);
                    if(count($entityAutorizacionUnidadEducativa)<=0){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información de la unidad educativa, intente nuevamente'));
                    }

                    $entityParticipantes = $this->getEstudiantesRegularHumanistica($sie,$gestion);

                    $datosBusqueda = base64_encode(serialize($form));

                    return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:dipHumRegularRegistroIndex.html.twig', array(
                        'formBusqueda' => $this->creaFormBuscaUnidadEducativaHumanistica('tramite_diploma_humanistico_regular_registro_lista',$sie,$gestion)->createView(),
                        'titulo' => 'Registro',
                        'subtitulo' => 'Trámite',
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionUnidadEducativa' => $entityAutorizacionUnidadEducativa,
                        'datosBusqueda' => $datosBusqueda,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_diploma_humanistico_regular_registro_busca'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_diploma_humanistico_regular_registro_busca'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_diploma_humanistico_regular_registro_busca'));
        }
        //return $this->redirect($this->generateUrl('login'));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que muestra el listado de bachilleres para el registro de su tramite diplomas humanistico alternativa
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumAlternativaRegistroListaAction(Request $request) {
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
                $sie = $form['sie'];
                $gestion = $form['gestion'];

                try {
                    $usuarioRol = $this->session->get('roluser');
                    $verTuicionUnidadEducativa = $this->verTuicionUnidadEducativa($id_usuario, $sie, $usuarioRol, 'TRAMITES');

                    if ($verTuicionUnidadEducativa != ''){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $verTuicionUnidadEducativa));
                        return $this->redirect($this->generateUrl('tramite_diploma_humanistico_alternativa_registro_busca '));
                    }

                    $entityAutorizacionUnidadEducativa = $this->getAutorizacionUnidadEducativa($sie);
                    if(count($entityAutorizacionUnidadEducativa)<=0){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información de la unidad educativa, intente nuevamente'));
                    }

                    $entityParticipantes = $this->getEstudiantesAlternativaHumanistica($sie,$gestion);

                    $datosBusqueda = base64_encode(serialize($form));

                    return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:dipHumAlternativaRegistroIndex.html.twig', array(
                        'formBusqueda' => $this->creaFormBuscaUnidadEducativaHumanistica('tramite_diploma_humanistico_alternativa_registro_lista',$sie,$gestion)->createView(),
                        'titulo' => 'Registro',
                        'subtitulo' => 'Trámite - Diploma Humanístico Alternativa',
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionUnidadEducativa' => $entityAutorizacionUnidadEducativa,
                        'datosBusqueda' => $datosBusqueda,
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_diploma_humanistico_alternativa_registro_busca'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_diploma_humanistico_alternativa_registro_busca'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_diploma_humanistico_alternativa_registro_busca'));
        }
        //return $this->redirect($this->generateUrl('login'));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra el trámite de los bachilleres humanisticos regular selecionados
    // PARAMETROS: estudiantes[], boton
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumRegularRegistroGuardaAction(Request $request) {
      date_default_timezone_set('America/La_Paz');
      $fechaActual = new \DateTime(date('Y-m-d'));
      $gestionActual = new \DateTime();

      $sesion = $request->getSession();
      $id_usuario = $sesion->get('userId');

      //validation if the user is logged
      if (!isset($id_usuario)) {
          return $this->redirect($this->generateUrl('login'));
      }

      $institucioneducativaId = 0;
      $gestionId = $gestionActual->format('Y');
      $flujoTipoId = 1;
      $tramiteTipoId = 1;
      $flujoSeleccionado = '';

      if ($request->isMethod('POST')) {
          $em = $this->getDoctrine()->getManager();
          $em->getConnection()->beginTransaction();
          try {
              $participantes = $request->get('participantes');
              if (isset($_POST['botonAceptar'])) {
                  $flujoSeleccionado = 'Adelante';
              }
              $token = $request->get('_token');
              if (!$this->isCsrfTokenValid('registrar', $token)) {
                  $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                  return $this->redirectToRoute('tramite_diploma_humanistico_regular_registro_busca');
              }

              $messageCorrecto = "";
              $messageError = "";

              foreach ($participantes as $participante) {
                  $estudianteInscripcionId = (Int) base64_decode($participante);

                  $entidadEstudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
                  $participanteNombre = trim($entidadEstudianteInscripcion->getEstudiante()->getPaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getMaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getNombre());
                  $participanteId =  $entidadEstudianteInscripcion->getEstudiante()->getId();
                  $msgContenido = "";
                  if(count($entidadEstudianteInscripcion)>0){
                      $institucionEducativaId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
                      $gestionId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

                      $msg = array('0'=>true, '1'=>$participanteNombre);
                      $msgContenido = $this->getDipHumRegularValidacion($participanteId, $estudianteInscripcionId, $gestionId);

                      if($msgContenido != ""){
                          $msg = array('0'=>false, '1'=>$participanteNombre.' ('.$msgContenido.')');
                      }

                  } else {
                      $msg = array('0'=>false, '1'=>'estudiante no encontrado');
                  }

                  if ($msg[0]) {

                      $tramiteId = $this->setTramiteEstudiante($estudianteInscripcionId, $gestionId, $tramiteTipoId, $flujoTipoId, $em);

                      $tramiteProcesoController = new tramiteProcesoController();
                      $tramiteProcesoController->setContainer($this->container);

                      $tramiteDetalleId = $tramiteProcesoController->setProcesaTramiteInicio($tramiteId, $id_usuario, 'REGISTRO DEL TRÁMITE', $em);

                      $messageCorrecto = ($messageCorrecto == "") ? $msg[1] : $messageCorrecto.'; '.$msg[1];
                  } else {
                      $messageError = ($messageError == "") ? $msg[1] : $messageError.'; '.$msg[1];
                  }
              }
              if($messageCorrecto!=""){
                  $em->getConnection()->commit();
                  $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => $messageCorrecto));
              }
              if($messageError!=""){
                  $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $messageError));
              }
          } catch (\Doctrine\ORM\NoResultException $exc) {
              $em->getConnection()->rollback();
              $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
          }

          $formBusqueda = array('sie'=>$institucionEducativaId,'gestion'=>$gestionId);
          return $this->redirectToRoute('tramite_diploma_humanistico_regular_registro_lista', ['form' => $formBusqueda], 307);
      } else {
          $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
          return $this->redirect($this->generateUrl('tramite_diploma_humanistico_regular_registro_busca'));
      }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra el trámite de los bachilleres humanisticos alternativa selecionados
    // PARAMETROS: estudiantes[], boton
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumAlternativaRegistroGuardaAction(Request $request) {
      date_default_timezone_set('America/La_Paz');
      $fechaActual = new \DateTime(date('Y-m-d'));
      $gestionActual = new \DateTime();

      $sesion = $request->getSession();
      $id_usuario = $sesion->get('userId');

      //validation if the user is logged
      if (!isset($id_usuario)) {
          return $this->redirect($this->generateUrl('login'));
      }

      $institucioneducativaId = 0;
      $gestionId = $gestionActual->format('Y');
      $flujoTipoId = 1;
      $tramiteTipoId = 1;
      $flujoSeleccionado = '';

      if ($request->isMethod('POST')) {
          $em = $this->getDoctrine()->getManager();
          $em->getConnection()->beginTransaction();
          try {
              $participantes = $request->get('participantes');
              if (isset($_POST['botonAceptar'])) {
                  $flujoSeleccionado = 'Adelante';
              }
              $token = $request->get('_token');
              if (!$this->isCsrfTokenValid('registrar', $token)) {
                  $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                  return $this->redirectToRoute('tramite_diploma_humanistico_alternativa_registro_busca');
              }

              $messageCorrecto = "";
              $messageError = "";

              foreach ($participantes as $participante) {
                  $estudianteInscripcionId = (Int) base64_decode($participante);

                  $entidadEstudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
                  $participanteNombre = trim($entidadEstudianteInscripcion->getEstudiante()->getPaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getMaterno().' '.$entidadEstudianteInscripcion->getEstudiante()->getNombre());
                  $participanteId =  $entidadEstudianteInscripcion->getEstudiante()->getId();
                  $msgContenido = "";
                  if(count($entidadEstudianteInscripcion)>0){
                      $institucionEducativaId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
                      $gestionId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

                      $msg = array('0'=>true, '1'=>$participanteNombre);
                      $msgContenido = $this->getDipHumAlternativaValidacion($participanteId, $estudianteInscripcionId);

                      if($msgContenido != ""){
                          $msg = array('0'=>false, '1'=>$participanteNombre.' ('.$msgContenido.')');
                      }

                  } else {
                      $msg = array('0'=>false, '1'=>'estudiante no encontrado');
                  }
                  if ($msg[0]) {

                      $tramiteId = $this->setTramiteEstudiante($estudianteInscripcionId, $gestionId, $tramiteTipoId, $flujoTipoId, $em);

                      $tramiteProcesoController = new tramiteProcesoController();
                      $tramiteProcesoController->setContainer($this->container);

                      $tramiteDetalleId = $tramiteProcesoController->setProcesaTramiteInicio($tramiteId, $id_usuario, 'REGISTRO DEL TRÁMITE', $em);

                      $messageCorrecto = ($messageCorrecto == "") ? $msg[1] : $messageCorrecto.'; '.$msg[1];
                  } else {
                      $messageError = ($messageError == "") ? $msg[1] : $messageError.'; '.$msg[1];
                  }
              }
              if($messageCorrecto!=""){
                  $em->getConnection()->commit();
                  $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => $messageCorrecto));
              }
              if($messageError!=""){
                  $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $messageError));
              }
          } catch (\Doctrine\ORM\NoResultException $exc) {
              $em->getConnection()->rollback();
              $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
          }

          $formBusqueda = array('sie'=>$institucionEducativaId,'gestion'=>$gestionId);
          return $this->redirectToRoute('tramite_diploma_humanistico_alternativa_registro_lista', ['form' => $formBusqueda], 307);
      } else {
          $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
          return $this->redirect($this->generateUrl('tramite_diploma_humanistico_alternativa_registro_busca'));
      }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista los trámites de certificacion tecnica registrados por la direccion departamental en formato pdf
    // PARAMETROS: sie, gestion, especialidad, nivel
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecRegistroListaPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $info = $request->get('info');
            $form = unserialize(base64_decode($info));
            $sie = $form['sie'];
            $ges = $form['gestion'];
            $especialidad = $form['especialidad'];
            $nivel = $form['nivel'];

            $arch = $sie.'_'.$ges.'_registro'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'tram_lst_certificacion_tecnica_registro_v1_rcm.rptdesign&sie='.$sie.'&gestion='.$ges.'&especialidad='.$especialidad.'&nivel='.$nivel.'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('tramite_detalle_certificado_tecnico_registro_lista', ['form' => $form], 307);
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista los trámites de diploma de bachiller recepcionados por la direccion distrital en formato pdf
    // PARAMETROS: sie, gestion, especialidad, nivel
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumRegistroListaPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        try {
            $info = $request->get('info');
            $form = unserialize(base64_decode($info));
            $sie = $form['sie'];
            $ges = $form['gestion'];
            $tipLis = 2;
            $ids = "";

            $arch = 'REGISTRO_'.$sie.'_'.$ges.'_'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'tram_lst_diplomaBachiller_humanistico_v1_rcm.rptdesign&sie='.$sie.'&gestion='.$ges.'&tipoLista='.$tipLis.'&ids='.$ids.'&&__format=pdf&'));
            $response->setStatusCode(200);
            $response->headers->set('Content-Transfer-Encoding', 'binary');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return $response;
        } catch (\Doctrine\ORM\NoResultException $exc) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al generar el listado, intente nuevamente'));
            return $this->redirectToRoute('tramite_diploma_humanistico_regular_registro_lista', ['form' => $form], 307);
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que despliega un listado de estudiantes registrados en tecnica alternativa
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    private function getEstudiantesAlternativaTecnica($institucionEducativaId, $gestionId, $especialidadId, $nivelId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct on (sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, e.codigo_rude)
            ei.id as estudiante_inscripcion_id,ei.estudiante_id as estudiante_id, sat.codigo as nivel, ies.periodo_tipo_id as periodo, iec.periodo_tipo_id as per, siea.institucioneducativa_id as institucioneducativa
            , sest.id as especialidad_id, ies.gestion_tipo_id,ies.periodo_tipo_id,siea.institucioneducativa_id, sfat.codigo as nivel_id, sfat.facultad_area, sest.codigo as ciclo_id
            ,sest.especialidad,sat.codigo as grado_id,sat.acreditacion,ei.id as estudiante_inscripcion,e.codigo_rude, e.nombre, e.paterno, e.materno, to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento
            , cast(e.carnet_identidad as varchar)||(case when e.complemento is null then '' when e.complemento = '' then '' else '-'||e.complemento end) as carnet_identidad
            , case pt.id when 1 then lt2.lugar when 0 then '' else pt.pais end as lugar_nacimiento
            --, lt4.id as departamento_id, lt4.lugar as departamento,date_part('year',age(e.fecha_nacimiento)) as edad--,e.genero_tipo_id
            , t.id as tramite_id,ei.estadomatricula_tipo_id, d.id as documento_id, d.documento_serie_id as documento_serie_id, segip_id
            from superior_facultad_area_tipo as sfat
            inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
            inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
            inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id
            inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
            inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
            inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id
            inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id
            inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id
            inner join estudiante as e on ei.estudiante_id=e.id
            left JOIN lugar_tipo as lt1 on lt1.id = e.lugar_prov_nac_tipo_id
            left JOIN lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
            left join pais_tipo pt on pt.id = e.pais_tipo_id
            --inner join institucioneducativa as ie on ie.id = siea.institucioneducativa_id
            --inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
            --left join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_localidad
            --left join lugar_tipo as lt1 on lt1.id = lt.lugar_tipo_id
            --left join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
            --left join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
            --left join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
            --inner join superior_modulo_periodo k on siep.id=k.institucioneducativa_periodo_id
            --inner join institucioneducativa_curso_oferta m on m.superior_modulo_periodo_id=k.id
            --and m.insitucioneducativa_curso_id=iec.id
            --inner join estudiante_asignatura n on n.institucioneducativa_curso_oferta_id=m.id and n.estudiante_inscripcion_id=ei.id
            --inner join estudiante_nota o on o.estudiante_asignatura_id=n.id
            --inner join superior_turno_tipo z on iec.turno_tipo_id=z.id
            --inner join paralelo_tipo p on iec.paralelo_tipo_id=p.id
            --inner join turno_tipo q on iec.turno_tipo_id=q.id ------
            left join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (6,7,8) and (case t.tramite_tipo when 6 then 1 when 7 then 2 when 8 then 3 else 0 end) = sat.codigo and t.esactivo = 't'
            left join documento as d on d.tramite_id = t.id and documento_tipo_id in (6,7,8) and d.documento_estado_id = 1
            where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sest.id = ".$especialidadId." and sat.codigo = ".$nivelId." and sfat.codigo in (18,19,20,21,22,23,24,25) and ei.estadomatricula_tipo_id in (4,5,55)
            order by sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, e.codigo_rude, ies.periodo_tipo_id desc
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que despliega un listado de bachilleres registrados en educacion regular humanistica
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    private function getEstudiantesRegularHumanistica($institucionEducativaId, $gestionId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select * from get_estudiante_bachiller_humanistico_regular(".$institucionEducativaId."::INT,".$gestionId."::INT) as v
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que despliega un listado de bachilleres registrados en educacion alternativa humanistica
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    private function getEstudiantesAlternativaHumanistica($institucionEducativaId, $gestionId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select * from get_estudiante_bachiller_humanistico_alternativa(".$institucionEducativaId."::INT,".$gestionId."::INT) as v
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida la acreditación del nivel autorizado en el centro de educacion especial tecnica
    // PARAMETROS: institucionEducativaId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getAutorizacionCentroEducativoTecnica($institucionEducativaId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                select ie.id, ie.institucioneducativa
                ,sum(case when iena.nivel_tipo_id = 203 then 1 else 0 end) as tec_basico
                ,sum(case when iena.nivel_tipo_id = 204 then 1 else 0 end) as tec_auxiliar
                ,sum(case when iena.nivel_tipo_id = 205 then 1 else 0 end) as tec_medio
                ,string_agg(distinct (case when iena.nivel_tipo_id = 203 then 'técnico básico' when iena.nivel_tipo_id = 204 then 'técnico auxiliar' when iena.nivel_tipo_id = 205 then 'técnico medio' end),', ') as niveles
                from institucioneducativa_nivel_autorizado as iena
                inner join institucioneducativa as ie on ie.id = iena.institucioneducativa_id
                where institucioneducativa_id = ".$institucionEducativaId."
                group by ie.id, ie.institucioneducativa
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        $niveles = array('basico'=>'false','auxiliar'=>'false','medio'=>'false','msg'=>'');
        if (count($objEntidad)>0){
            $msg = "";
            if ($objEntidad[0]['tec_basico'] > 0){
                $niveles['basico'] = true;
           } else {
                if($msg==""){
                    $msg = "básico";
                } else {
                    $msg = $msg.", básico";
                }
          }
            if ($objEntidad[0]['tec_auxiliar'] > 0){
                $niveles['auxiliar'] = true;
            } else {
                if($msg==""){
                    $msg = "auxiliar";
                } else {
                    $msg = $msg.", auxiliar";
                }
            }
            if ($objEntidad[0]['tec_medio'] > 0){
                $niveles['medio'] = true;
           } else {
                if($msg==""){
                   $msg = "medio";
                } else {
                    $msg = $msg.", medio";
                }
            }
            if ($msg!=""){
                $niveles['msg'] = "El centro de educación alternativa no cuenta con la acreditación para el nivel técnico ".$msg.", favor tomar en cuenta";
            }
        }
        return $objEntidad[0];
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida la acreditación del nivel autorizado en la unidad educativa o centro de educacion alternativa humanistica
    // PARAMETROS: institucionEducativaId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getAutorizacionUnidadEducativa($institucionEducativaId){
      $em = $this->getDoctrine()->getManager();
      $queryEntidad = $em->getConnection()->prepare("
          select ie.id, ie.institucioneducativa
          , sum(case iena.nivel_tipo_id when 11 then 1 else 0 end) as inicial
          , sum(case iena.nivel_tipo_id when 12 then 1 else 0 end) as primaria
          , sum(case iena.nivel_tipo_id when 13 then 1 when 202 then 1 else 0 end) as secundaria
          , string_agg(distinct nt.nivel, ', ') as niveles
          from institucioneducativa as ie
          left join institucioneducativa_nivel_autorizado as iena on iena.institucioneducativa_id = ie.id
          left join nivel_tipo as nt on nt.id = iena.nivel_tipo_id
          where ie.id = ".$institucionEducativaId."
          group by ie.id, ie.institucioneducativa
      ");
      $queryEntidad->execute();
      $objEntidad = $queryEntidad->fetchAll();
      $niveles = array('inicial'=>'false','primaria'=>'false','secundaria'=>'false','msg'=>'');
      if (count($objEntidad)>0){
        $msg = "";
        if ($objEntidad[0]['secundaria'] > 0){
          $niveles['secundaria'] = true;
        } else {
          if($msg==""){
            $msg = "secundaria";
          } else {
            $msg = $msg.", secundaria";
          }
        }
        if ($msg!=""){
          $niveles['msg'] = "La unidad educativa no cuenta con la acreditación para el nivel ".$msg.", favor tomar en cuenta";
        }
      }
      return $objEntidad[0];
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que halla el subsistema al cual pertenece la institucion educativa
    // PARAMETROS: institucionEducativaId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getSubSistemaInstitucionEducativa($institucionEducativaId){
      $em = $this->getDoctrine()->getManager();
      $queryEntidad = $em->getConnection()->prepare("
          select ie.id as codigo, ie.institucioneducativa as institucioneducativa, oct.id as orgcurricular_id, oct.orgcurricula
          , (case oct.id when 2 then (case iena.nivel_tipo_id when 6 then 'Especial' else oct.orgcurricula end) else oct.orgcurricula end) as subsistema
          from institucioneducativa as ie
          inner join orgcurricular_tipo as oct ON oct.id = ie.orgcurricular_tipo_id
          left join (select distinct institucioneducativa_id, nivel_tipo_id from institucioneducativa_nivel_autorizado where nivel_tipo_id = 6) as iena on iena.institucioneducativa_id = ie.id
          where ie.institucioneducativa_acreditacion_tipo_id = 1 and ie.id = ".$institucionEducativaId." and ie.estadoinstitucion_tipo_id = 10
      ");
      $queryEntidad->execute();
      $objEntidad = $queryEntidad->fetchAll();
      $obj = array('id'=>'0','subsistema'=>'','msg'=>'');
      if (count($objEntidad)>0){
          $obj['id'] = $objEntidad[0]['orgcurricular_id'];
          $obj['subsistema'] = $objEntidad[0]['orgcurricula'];
          $obj['msg'] = '';
      } else{
          $obj['msg'] = "La institución educativa se encuentra cerrada, favor tomar en cuenta";
      }
      return $obj;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista las especialidades de un centro de educacion alternativa segun la gestión
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getEspecialidadCentroEducativoTecnica($institucionEducativaId, $gestionId) {
        date_default_timezone_set('America/La_Paz');
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                    select distinct sest.id as especialidad_id, sest.especialidad as especialidad
                    from superior_facultad_area_tipo as sfat
                    inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
                    inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
                    inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
                    inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
                    where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sfat.codigo in (18,19,20,21,22,23,24,25)
                    order by sest.especialidad
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los niveles de un centro de educacion alternativa segun la gestión y especialidad
    // PARAMETROS: institucionEducativaId, gestionId, $especialidadId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getNivelCentroEducativoTecnica($institucionEducativaId, $gestionId, $especialidadId) {
        date_default_timezone_set('America/La_Paz');
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                select distinct sat.codigo as nivel_id, sat.acreditacion as nivel
                from superior_facultad_area_tipo as sfat
                inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
                inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
                inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id
                inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
                inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
                where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sest.id = ".$especialidadId." and sfat.codigo in (18,19,20,21,22,23,24,25)
                order by sat.codigo
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista las especialidades de un centro de educacion alternativa segun la gestión
    // PARAMETROS: por POST  institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecBuscaListaEspecialidadAction(Request $request) {
        try {
            $response = new JsonResponse();
            return $response->setData(array(
                'especialidades' => $this->getEspecialidadCentroEducativoTecnica($_POST['sie'],$_POST['gestion']),
            ));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
            return $response->setData(array());
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista los niveles de un centro de educacion alternativa segun la gestión
    // PARAMETROS: por POST  institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecBuscaListaNivelAction(Request $request) {
        try {
            $response = new JsonResponse();
            return $response->setData(array(
                'niveles' => $this->getNivelCentroEducativoTecnica($_POST['sie'],$_POST['gestion'],$_POST['especialidad']),
            ));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
            return $response->setData(array());
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida la carga horaria de un participante, especialidad y nivel
    // PARAMETROS: participanteId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getCertTecCargaHorariaEstudiante($participanteId, $especialidadId, $nivelId) {
        $msg = array('0'=>true, '1'=>'');
        $nivel = '';
        $objCargaHoraria = $this->getCertTecCargaHorariaEspecialidadNivel($participanteId, $especialidadId, $nivelId);
        if ($nivelId == 1) {
            $nivel = 'Técnico Básico';
        } elseif ($nivelId == 2) {
            $nivel = 'Técnico Auxiliar';
        } elseif ($nivelId == 3) {
            $nivel = 'Técnico Medio';
        } else {
            $nivel = '';
        }


        if(count($objCargaHoraria)>0){
            $cargaHoraria = $objCargaHoraria[0]['carga_horaria'];
            $verCargaHorariaNivel = $this->certTecCargaHorariaNivelMinimo($nivelId,$cargaHoraria);
            if ($verCargaHorariaNivel != "") {
                $msg = array('0'=>false, '1'=>$verCargaHorariaNivel);
            } else {
                $cargaHoraria = $this->certTecCargaHorariaNivelExcedente($nivelId,$cargaHoraria);
                $msg = array('0'=>true, '1'=>$cargaHoraria);
            }
        } else {
            // homologacion
            $objCargaHorariaHomologacion = $this->getCertTecCargaHorariaHomologadoEstudiante($participanteId, $especialidadId, $nivelId);


            if(count($objCargaHorariaHomologacion)>0){
                $cargaHoraria = $objCargaHorariaHomologacion[0]['carga_horaria'];
                $verCargaHorariaNivel = $this->certTecCargaHorariaNivelMinimo($nivelId,$cargaHoraria);
                if ($verCargaHorariaNivel!="") {
                    $msg = array('0'=>false, '1'=>$participante.$verCargaHorariaNivel);
                }  else {
                    $cargaHoraria = $this->certTecCargaHorariaNivelExcedente($nivelId,$cargaHoraria);
                    $msg = array('0'=>true, '1'=>$cargaHoraria);
                }
            } else {
                $msg = array('0'=>false, '1'=>'No cuenta con carga horaria en el nivel '.$nivel);
            }
        }

        return $msg;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // funcion que compara las minimas cargas horarias segun nivel tecnico de alternativa
    // PARAMETROS: nivelId, cargaHoraria
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecCargaHorariaNivelMinimo($nivelId,$cargaHoraria) {
        $msg = '';
        if ($nivelId == 1) {
            if ($cargaHoraria < 800){
                $msg = 'Solo cuenta con '.$cargaHoraria.' de 800 horas minimas en Técnico Básico';
            }
        } elseif ($nivelId == 2) {
            if ($cargaHoraria < 400){
                $msg = 'Solo cuenta con '.$cargaHoraria.' de 400 horas minimas en Técnico Auxiliar';
            }
        } elseif ($nivelId == 3) {
            if ($cargaHoraria < 500){
                $msg = 'Solo cuenta con '.$cargaHoraria.' de 500 horas minimas en Técnico Medio';
            }
        } else {
            $msg = 'Nivel no encontrado';
        }
        return $msg;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // funcion que elimina las cargas horarias excedentes segun nivel tecnico de alternativa
    // PARAMETROS: nivelId, cargaHoraria
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecCargaHorariaNivelExcedente($nivelId,$cargaHoraria) {
        $msg = 0;
        if ($nivelId == 1) {
            if ($cargaHoraria > 1000){
                $msg = 1000;
            } else {
                $msg = $cargaHoraria;
            }
        } elseif ($nivelId == 2) {
            if ($cargaHoraria > 500){
                $msg = 500;
            } else {
                $msg = $cargaHoraria;
            }
        } elseif ($nivelId == 3) {
            if ($cargaHoraria > 800){
                $msg = 800;
            } else {
                $msg = $cargaHoraria;
            }
        } else {
            $msg = 0;
        }
        return $msg;
    }


    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que halla los modulos observados de un estudiante segun su especialidad y nivel
    // PARAMETROS: participanteId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getCertTecModuloObsEstudiante($participanteId, $especialidadId, $nivelId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                select estudiante_id, codigo_rude, participante, especialidad_id, especialidad, nivel_id, acreditacion, string_agg(distinct modulo, ',') as modulos from (
                    select e.id as estudiante_id, e.codigo_rude, e.paterno||' '||e.materno||' '||e.nombre as participante, sest.id as especialidad_id, sest.especialidad, sat.codigo as nivel_id, sat.acreditacion
                    , smp.horas_modulo, smt.modulo
                    from superior_facultad_area_tipo as sfat
                    inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
                    inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
                    inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id
                    inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
                    inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
                    inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id
                    inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id
                    inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id
                    inner join (select * from estudiante where id = ".$participanteId.") as e on ei.estudiante_id=e.id
                    inner join superior_modulo_periodo as smp ON smp.institucioneducativa_periodo_id = siep.id
                    inner join superior_modulo_tipo smt ON smt.id = smp.superior_modulo_tipo_id
                    inner join institucioneducativa_curso_oferta as ieco on ieco.superior_modulo_periodo_id = smp.id and ieco.insitucioneducativa_curso_id = iec.id
                    inner join estudiante_asignatura as ea on ea.institucioneducativa_curso_oferta_id = ieco.id and ea.estudiante_inscripcion_id = ei.id
                    inner join estudiante_nota as en on en.estudiante_asignatura_id = ea.id
                    where sest.id = ".$especialidadId." and sat.codigo = ".$nivelId."
                    and en.nota_tipo_id::integer = 22 AND CASE WHEN ies.gestion_tipo_id <= 2015::double precision THEN en.nota_cuantitativa >=36 ELSE en.nota_cuantitativa >=51 END
                group by e.id, e.codigo_rude, e.paterno, e.materno, e.nombre, sest.id, sest.especialidad, sat.codigo, sat.acreditacion
                , smp.horas_modulo, smt.modulo having count(*) > 1
            ) as v
            group by estudiante_id, codigo_rude, participante, especialidad_id, especialidad, nivel_id, acreditacion
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que halla las calificaciones observadas de un estudiante de educacion regular humanistica
    // PARAMETROS: participanteId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDipHumRegularCalificacionObsEstudiante($participanteId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
          select v.codigo_rude, v.carnet_identidad, v.paterno, v.materno, v.nombre
          , string_agg(distinct v.asignatura||' ('||v.grado_tipo_id||'° '||v.paralelo||' / '||v.gestion_tipo_id||'-'||v.institucioneducativa_id||')', ', ') as asignaturas from (
            SELECT
                estudiante.codigo_rude,
                cast(estudiante.carnet_identidad as varchar)||(case when estudiante.complemento is null then '' when estudiante.complemento = '' then '' else '-'||estudiante.complemento end) as carnet_identidad,
                estudiante.paterno,
                estudiante.materno,
                estudiante.nombre,
                institucioneducativa_curso.institucioneducativa_id,
                institucioneducativa_curso.grado_tipo_id,
                institucioneducativa_curso.paralelo_tipo_id,
                paralelo_tipo.paralelo,
                institucioneducativa_curso.gestion_tipo_id,
                estudiante_inscripcion.estadomatricula_tipo_id,
                institucioneducativa_curso_oferta.asignatura_tipo_id,
            asignatura_tipo.asignatura,
                institucioneducativa_curso.turno_tipo_id,
                Sum(case when estudiante_nota.nota_tipo_id = 1 then estudiante_nota.nota_cuantitativa end) AS b1,
                Sum(case when estudiante_nota.nota_tipo_id = 2 then estudiante_nota.nota_cuantitativa end) AS b2,
                Sum(case when estudiante_nota.nota_tipo_id = 3 then estudiante_nota.nota_cuantitativa end) AS b3,
                Sum(case when estudiante_nota.nota_tipo_id = 4 then estudiante_nota.nota_cuantitativa end) AS b4,
                Sum(case when estudiante_nota.nota_tipo_id = 5 then estudiante_nota.nota_cuantitativa end) AS b5,
                Sum(case when estudiante_nota.nota_tipo_id = 6 then estudiante_nota.nota_cuantitativa end) AS t1,
                Sum(case when estudiante_nota.nota_tipo_id = 7 then estudiante_nota.nota_cuantitativa end) AS t2,
                Sum(case when estudiante_nota.nota_tipo_id = 8 then estudiante_nota.nota_cuantitativa end) AS t3,
                Sum(case when estudiante_nota.nota_tipo_id = 9 then estudiante_nota.nota_cuantitativa end) AS t4,
                Sum(case when estudiante_nota.nota_tipo_id = 10 then estudiante_nota.nota_cuantitativa end) AS t5,
                Sum(case when estudiante_nota.nota_tipo_id = 11 then estudiante_nota.nota_cuantitativa end) AS t6
                FROM
                estudiante
                INNER JOIN  estudiante_inscripcion ON  estudiante_inscripcion.estudiante_id =  estudiante.id
                INNER JOIN  institucioneducativa_curso ON  estudiante_inscripcion.institucioneducativa_curso_id =  institucioneducativa_curso.id
                INNER JOIN  estudiante_asignatura ON  estudiante_asignatura.estudiante_inscripcion_id =  estudiante_inscripcion.id
                LEFT JOIN  estudiante_nota ON  estudiante_nota.estudiante_asignatura_id =  estudiante_asignatura.id
                INNER JOIN  institucioneducativa_curso_oferta ON  institucioneducativa_curso_oferta.insitucioneducativa_curso_id =  institucioneducativa_curso.id AND  estudiante_asignatura.institucioneducativa_curso_oferta_id =  institucioneducativa_curso_oferta.id
            INNER JOIN  asignatura_tipo ON  institucioneducativa_curso_oferta.asignatura_tipo_id =  asignatura_tipo.id
                INNER JOIN  paralelo_tipo ON  institucioneducativa_curso.paralelo_tipo_id =  paralelo_tipo.id
            LEFT JOIN  estudiante_inscripcion_humnistico_tecnico ON estudiante_inscripcion_humnistico_tecnico.estudiante_inscripcion_id = estudiante_inscripcion.id
                LEFT JOIN  especialidad_tecnico_humanistico_tipo ON estudiante_inscripcion_humnistico_tecnico.especialidad_tecnico_humanistico_tipo_id = especialidad_tecnico_humanistico_tipo.id
                WHERE
                estudiante.id = ".$participanteId." and estudiante_inscripcion.estadomatricula_tipo_id in (4,5,55) and institucioneducativa_curso.nivel_tipo_id in (3,13)
                AND case when institucioneducativa_curso.gestion_tipo_id > 2010 then institucioneducativa_curso.ciclo_tipo_id in (2,3) else true end
            GROUP BY
                estudiante.codigo_rude,
                estudiante.carnet_identidad,
                estudiante.complemento,
                estudiante.paterno,
                estudiante.materno,
                estudiante.nombre,
                institucioneducativa_curso.institucioneducativa_id,
                institucioneducativa_curso.grado_tipo_id,
                institucioneducativa_curso.paralelo_tipo_id,
                paralelo_tipo.paralelo,
                institucioneducativa_curso.gestion_tipo_id,
                estudiante_inscripcion.estadomatricula_tipo_id,
                institucioneducativa_curso_oferta.asignatura_tipo_id,
            asignatura_tipo.asignatura,
                institucioneducativa_curso.turno_tipo_id,
                especialidad_tecnico_humanistico_tipo.especialidad
             ) as v
           where
           case
            when (gestion_tipo_id::double precision = date_part('year',current_date)::double precision)
            then (b1 is null or b1 = 0)
            when ((gestion_tipo_id > 2013) or (gestion_tipo_id > 2013 and grado_tipo_id = 1) and gestion_tipo_id < date_part('year',current_date))
            then (b1 is null or b1 = 0 or b2 is null or b2 = 0 or b3 is null or b3 = 0 or b4 is null or b4 = 0 or b5 is null or b5 = 0)
            else (b1 is null or t1 = 0 or t2 is null or t2 = 0 or t3 is null or t3 = 0 or t4 is null or t4 = 0)
          end
          group by
          v.codigo_rude,
          v.carnet_identidad,
          v.paterno,
          v.materno,
          v.nombre
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que halla las calificaciones observadas de un estudiante de educacion alternativa humanistica
    // PARAMETROS: participanteId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDipHumAlternativaCalificacionObsEstudiante($participanteId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
          select
          v.codigo_rude, v.paterno, v.materno, v.nombre
          , string_agg(distinct v.asignatura||' ('||v.gestion_tipo_id||' / '||v.periodo||' / '||v.institucioneducativa_id||' / '||v.paralelo||')', ', ') as asignaturas
          from (
          select e.id as estudiante_id, e.codigo_rude, e.paterno, e.materno, e.nombre, sest.id as especialidad_id, sest.especialidad
          , sat.codigo as grado_tipo_id, sat.acreditacion as grado, ie.id as institucioneducativa_id, ie.institucioneducativa, pt.paralelo
          , ei.estadomatricula_tipo_id, smt.id as asignatura_tipo_id, smt.modulo as asignatura, ies.gestion_tipo_id
          , sast.id as area_tipo_id, sast.area_superior as area, tt.id as turno_tipo_id, tt.turno, pet.id as periodo_tipo_id, pet.periodo as periodo
          , SUM(case en.nota_tipo_id when 23 then en.nota_cuantitativa else 0 end) as n1
          , SUM(case en.nota_tipo_id when 24 then en.nota_cuantitativa else 0 end) as n2
          , SUM(case en.nota_tipo_id when 25 then en.nota_cuantitativa else 0 end) as n3
          , SUM(case en.nota_tipo_id when 26 then en.nota_cuantitativa else 0 end) as n4
          from superior_facultad_area_tipo as sfat
          inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
          inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
          inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id
          inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
          inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
          inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id
          inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id
          inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id
          inner join (select * from estudiante where id = ".$participanteId.") as e on ei.estudiante_id=e.id
          inner join superior_modulo_periodo as smp ON smp.institucioneducativa_periodo_id = siep.id
          inner join superior_modulo_tipo as smt ON smt.id = smp.superior_modulo_tipo_id
          inner join superior_area_saberes_tipo as sast on sast.id = smt.superior_area_saberes_tipo_id
          inner join institucioneducativa_curso_oferta as ieco on ieco.superior_modulo_periodo_id = smp.id and ieco.insitucioneducativa_curso_id = iec.id
          inner join institucioneducativa as ie on ie.id = siea.institucioneducativa_id
          inner join paralelo_tipo as pt on pt.id = iec.paralelo_tipo_id
          inner join turno_tipo as tt on tt.id = iec.turno_tipo_id
          inner join periodo_tipo as pet on pet.id = ies.periodo_tipo_id
          left join estudiante_asignatura as ea on ea.institucioneducativa_curso_oferta_id = ieco.id and ea.estudiante_inscripcion_id = ei.id
          left join estudiante_nota as en on en.estudiante_asignatura_id = ea.id
          where  sfat.codigo in (15) and sat.codigo in (2,3) and sest.codigo in (2)
          and ei.estadomatricula_tipo_id in (4,5,55) and en.nota_tipo_id::integer in (23,24,25,26)
          group by e.id, e.codigo_rude, e.paterno, e.materno, e.nombre, ei.estadomatricula_tipo_id, sest.id, sest.especialidad
          , sat.codigo, sat.acreditacion, ie.id, ie.institucioneducativa, tt.id, tt.turno, pt.paralelo, smt.id, smt.modulo
          , ies.gestion_tipo_id, sast.id, sast.area_superior, pet.id, pet.periodo
          order by ies.gestion_tipo_id desc, pet.id desc, sat.codigo, sast.id, smt.id
          ) as v
          where
          case
          when (gestion_tipo_id::double precision != date_part('year',current_date)::double precision)
          then
            case
            when (gestion_tipo_id::double precision > 2015) then (n4 is null or n4 = 0)
            else (n1 is null or n1 = 0 or n2 is null or n2 = 0 or n3 is null or n3 = 0 or n4 is null or n4 = 0)
            end
          else
            true
          end
          group by v.codigo_rude, v.paterno, v.materno, v.nombre
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que halla la carga horaria  de un estudiante segun su especialidad y nivel
    // PARAMETROS: participanteId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getCertTecCargaHorariaEspecialidadNivel($participanteId, $especialidadId, $nivelId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                select estudiante_id, codigo_rude, participante, especialidad_id, especialidad, nivel_id, acreditacion, string_agg(distinct modulo, ',') as modulos, sum(horas_modulo) as carga_horaria from (
                        select e.id as estudiante_id, e.codigo_rude, e.paterno||' '||e.materno||' '||e.nombre as participante, sest.id as especialidad_id, sest.especialidad, sat.codigo as nivel_id, sat.acreditacion
                        , smp.horas_modulo, smt.modulo
                        from superior_facultad_area_tipo as sfat
                        inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
                        inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
                        inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id
                        inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
                        inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
                        inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id
                        inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id
                        inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id
                        inner join (select * from estudiante where id = ".$participanteId.") as e on ei.estudiante_id=e.id
                        inner join superior_modulo_periodo as smp ON smp.institucioneducativa_periodo_id = siep.id
                        inner join superior_modulo_tipo smt ON smt.id = smp.superior_modulo_tipo_id
                        inner join institucioneducativa_curso_oferta as ieco on ieco.superior_modulo_periodo_id = smp.id and ieco.insitucioneducativa_curso_id = iec.id
                        inner join estudiante_asignatura as ea on ea.institucioneducativa_curso_oferta_id = ieco.id and ea.estudiante_inscripcion_id = ei.id
                        inner join estudiante_nota as en on en.estudiante_asignatura_id = ea.id
                        where sest.id = ".$especialidadId." and sat.codigo = ".$nivelId."
                        and en.nota_tipo_id::integer = 22 AND CASE WHEN ies.gestion_tipo_id <= 2015::double precision THEN en.nota_cuantitativa >=36 ELSE en.nota_cuantitativa >=51 END
                    group by e.id, e.codigo_rude, e.paterno, e.materno, e.nombre, sest.id, sest.especialidad, sat.codigo, sat.acreditacion
                    , smp.horas_modulo, smt.modulo having count(*) < 2
                ) as v
                group by estudiante_id, codigo_rude, participante, especialidad_id, especialidad, nivel_id, acreditacion
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }



    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que halla la carga horaria  de un estudiante segun su inscripción
    // PARAMETROS: participanteId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getCertTecCargaHorariaInscripcion($participanteId, $especialidadId, $nivelId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select e.id as estudiante_id, e.codigo_rude, e.paterno||' '||e.materno||' '||e.nombre as participante, sest.id as especialidad_id, sest.especialidad, sat.codigo as nivel_id, sat.acreditacion
            , smp.horas_modulo, smt.modulo, en.nota_cuantitativa, ies.gestion_tipo_id as gestion, pt.periodo as periodo, ie.id as institucioneducativa_id, ie.institucioneducativa
            from superior_facultad_area_tipo as sfat
            inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
            inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
            inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id
            inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
            inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
            inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id
            inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id
            inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id
            inner join (select * from estudiante where id = ".$participanteId.") as e on ei.estudiante_id=e.id
            inner join superior_modulo_periodo as smp ON smp.institucioneducativa_periodo_id = siep.id
            inner join superior_modulo_tipo smt ON smt.id = smp.superior_modulo_tipo_id
            inner join institucioneducativa_curso_oferta as ieco on ieco.superior_modulo_periodo_id = smp.id and ieco.insitucioneducativa_curso_id = iec.id
            inner join estudiante_asignatura as ea on ea.institucioneducativa_curso_oferta_id = ieco.id and ea.estudiante_inscripcion_id = ei.id
            inner join estudiante_nota as en on en.estudiante_asignatura_id = ea.id
            inner join periodo_tipo as pt on pt.id = ies.periodo_tipo_id
            inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
            where sest.id = ".$especialidadId." and sat.codigo = ".$nivelId." and en.nota_tipo_id::integer = 22
            order by smt.id
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que halla el historial de un estudiante de educación regular humanistica segun su inscripción
    // PARAMETROS: participanteId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDipHumRegularHistorial($participanteId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
        SELECT
        estudiante.codigo_rude,
        cast(estudiante.carnet_identidad as varchar)||(case when estudiante.complemento is null then '' when estudiante.complemento = '' then '' else '-'||estudiante.complemento end) as carnet_identidad,
        estudiante.paterno,
        estudiante.materno,
        estudiante.nombre,
        institucioneducativa_curso.institucioneducativa_id,
        institucioneducativa.institucioneducativa,
        institucioneducativa_curso.grado_tipo_id,
        grado_tipo.grado,
        paralelo_tipo.paralelo,
        institucioneducativa_curso.gestion_tipo_id,
        estudiante_inscripcion.estadomatricula_tipo_id,
        institucioneducativa_curso_oferta.asignatura_tipo_id,
        -- asignatura_tipo.asignatura,
        (case WHEN institucioneducativa_curso_oferta.asignatura_tipo_id = 1039 then upper(asignatura_tipo.asignatura ||' '||especialidad_tecnico_humanistico_tipo.especialidad) else asignatura_tipo.asignatura end) as asignatura,
        asignatura_tipo.area_tipo_id,
        UPPER(area_tipo.area) as area,
        turno_tipo.turno,
        Sum(case when estudiante_nota.nota_tipo_id = 1 then estudiante_nota.nota_cuantitativa end) AS b1,
        Sum(case when estudiante_nota.nota_tipo_id = 2 then estudiante_nota.nota_cuantitativa end) AS b2,
        Sum(case when estudiante_nota.nota_tipo_id = 3 then estudiante_nota.nota_cuantitativa end) AS b3,
        Sum(case when estudiante_nota.nota_tipo_id = 4 then estudiante_nota.nota_cuantitativa end) AS b4,
        Sum(case when estudiante_nota.nota_tipo_id = 5 then estudiante_nota.nota_cuantitativa end) AS b5,
        Sum(case when estudiante_nota.nota_tipo_id = 6 then estudiante_nota.nota_cuantitativa end) AS t1,
        Sum(case when estudiante_nota.nota_tipo_id = 7 then estudiante_nota.nota_cuantitativa end) AS t2,
        Sum(case when estudiante_nota.nota_tipo_id = 8 then estudiante_nota.nota_cuantitativa end) AS t3,
        Sum(case when estudiante_nota.nota_tipo_id = 9 then estudiante_nota.nota_cuantitativa end) AS t4,
        Sum(case when estudiante_nota.nota_tipo_id = 10 then estudiante_nota.nota_cuantitativa end) AS t5,
        Sum(case when estudiante_nota.nota_tipo_id = 11 then estudiante_nota.nota_cuantitativa end) AS t6
        FROM
        estudiante
        INNER JOIN  estudiante_inscripcion ON  estudiante_inscripcion.estudiante_id =  estudiante.id
        INNER JOIN  institucioneducativa_curso ON  estudiante_inscripcion.institucioneducativa_curso_id =  institucioneducativa_curso.id
        LEFT JOIN  estudiante_asignatura ON  estudiante_asignatura.estudiante_inscripcion_id =  estudiante_inscripcion.id
        LEFT JOIN  estudiante_nota ON  estudiante_nota.estudiante_asignatura_id =  estudiante_asignatura.id
        LEFT JOIN  institucioneducativa_curso_oferta ON  institucioneducativa_curso_oferta.insitucioneducativa_curso_id =  institucioneducativa_curso.id AND  estudiante_asignatura.institucioneducativa_curso_oferta_id =  institucioneducativa_curso_oferta.id
        LEFT JOIN  asignatura_tipo ON  institucioneducativa_curso_oferta.asignatura_tipo_id =  asignatura_tipo.id
        LEFT JOIN  area_tipo ON  asignatura_tipo.area_tipo_id =  area_tipo.id
        INNER JOIN  grado_tipo ON  institucioneducativa_curso.grado_tipo_id =  grado_tipo.id
        INNER JOIN  paralelo_tipo ON  institucioneducativa_curso.paralelo_tipo_id =  paralelo_tipo.id
        LEFT JOIN  turno_tipo ON turno_tipo.id = institucioneducativa_curso.turno_tipo_id
        INNER JOIN  institucioneducativa ON  institucioneducativa_curso.institucioneducativa_id =  institucioneducativa.id
        LEFT JOIN  estudiante_inscripcion_humnistico_tecnico ON estudiante_inscripcion_humnistico_tecnico.estudiante_inscripcion_id = estudiante_inscripcion.id
        LEFT JOIN  especialidad_tecnico_humanistico_tipo ON estudiante_inscripcion_humnistico_tecnico.especialidad_tecnico_humanistico_tipo_id = especialidad_tecnico_humanistico_tipo.id
        WHERE
        estudiante.id = ".$participanteId." and estudiante_inscripcion.estadomatricula_tipo_id in (4,5,55,10,11) and institucioneducativa_curso.nivel_tipo_id in (3,13)
        AND case when institucioneducativa_curso.gestion_tipo_id > 2010 then institucioneducativa_curso.ciclo_tipo_id in (2,3) else true end
        -- AND case when institucioneducativa_curso.gestion_tipo_id > 2013 or (institucioneducativa_curso.gestion_tipo_id > 2013 and grado_tipo.id = 1) then grado_tipo.id in (3,4,5,6) else grado_tipo.id in (1,2,3,4) end
        -- AND institucioneducativa_curso.nivel_tipo_id = (case when (2017 <= 2010) then 3 else 13 end)
        -- AND (case when (2017 <= 2010) then (institucioneducativa_curso.grado_tipo_id in (1,2,3,4)) else (institucioneducativa_curso.grado_tipo_id in (3,4,5,6)) end)
        GROUP BY
        estudiante.codigo_rude,
        estudiante.carnet_identidad,
        estudiante.complemento,
        estudiante.paterno,
        estudiante.materno,
        estudiante.nombre,
        institucioneducativa_curso.institucioneducativa_id,
        institucioneducativa.institucioneducativa,
        institucioneducativa_curso.grado_tipo_id,
        grado_tipo.grado,
        paralelo_tipo.paralelo,
        institucioneducativa_curso.gestion_tipo_id,
        estudiante_inscripcion.estadomatricula_tipo_id,
        institucioneducativa_curso_oferta.asignatura_tipo_id,
        turno_tipo.turno,
        asignatura_tipo.area_tipo_id,
        area_tipo.area,
        asignatura_tipo.asignatura,
        especialidad_tecnico_humanistico_tipo.especialidad
        ORDER BY
        institucioneducativa_curso.grado_tipo_id desc,asignatura_tipo.area_tipo_id, institucioneducativa_curso_oferta.asignatura_tipo_id
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que halla el historial de un estudiante de educación alternativa humanistica segun su inscripción
    // PARAMETROS: participanteId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDipHumAlternativaHistorial($participanteId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select e.id as estudiante_id, e.codigo_rude, e.paterno, e.materno, e.nombre, sest.id as especialidad_id, sest.especialidad
            , sat.codigo as grado_tipo_id, sat.acreditacion as grado, ie.id as institucioneducativa_id, ie.institucioneducativa, pt.paralelo
            , ei.estadomatricula_tipo_id, smt.id as asignatura_tipo_id, smt.modulo as asignatura, ies.gestion_tipo_id
            , sast.id as area_tipo_id, sast.area_superior as area, tt.id as turno_tipo_id, tt.turno, pet.id as periodo_tipo_id, pet.periodo as periodo
            , SUM(case en.nota_tipo_id when 23 then en.nota_cuantitativa else 0 end) as n1
            , SUM(case en.nota_tipo_id when 24 then en.nota_cuantitativa else 0 end) as n2
            , SUM(case en.nota_tipo_id when 25 then en.nota_cuantitativa else 0 end) as n3
            , SUM(case en.nota_tipo_id when 26 then en.nota_cuantitativa else 0 end) as n4
            from superior_facultad_area_tipo as sfat
            inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
            inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
            inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id
            inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
            inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
            inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id
            inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id
            inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id
            inner join (select * from estudiante where id = ".$participanteId.") as e on ei.estudiante_id=e.id
            inner join superior_modulo_periodo as smp ON smp.institucioneducativa_periodo_id = siep.id
            inner join superior_modulo_tipo as smt ON smt.id = smp.superior_modulo_tipo_id
            inner join superior_area_saberes_tipo as sast on sast.id = smt.superior_area_saberes_tipo_id
            inner join institucioneducativa_curso_oferta as ieco on ieco.superior_modulo_periodo_id = smp.id and ieco.insitucioneducativa_curso_id = iec.id
            inner join institucioneducativa as ie on ie.id = siea.institucioneducativa_id
            inner join paralelo_tipo as pt on pt.id = iec.paralelo_tipo_id
            inner join turno_tipo as tt on tt.id = iec.turno_tipo_id
            inner join periodo_tipo as pet on pet.id = ies.periodo_tipo_id
            left join estudiante_asignatura as ea on ea.institucioneducativa_curso_oferta_id = ieco.id and ea.estudiante_inscripcion_id = ei.id
            left join estudiante_nota as en on en.estudiante_asignatura_id = ea.id
            where  sfat.codigo in (15) and sat.codigo in (2,3) and sest.codigo in (2)
            and ei.estadomatricula_tipo_id in (4,5,55,10,11) and en.nota_tipo_id::integer in (23,24,25,26)
            group by e.id, e.codigo_rude, e.paterno, e.materno, e.nombre, ei.estadomatricula_tipo_id, sest.id, sest.especialidad
            , sat.codigo, sat.acreditacion, ie.id, ie.institucioneducativa, tt.id, tt.turno, pt.paralelo, smt.id, smt.modulo
            , ies.gestion_tipo_id, sast.id, sast.area_superior, pet.id, pet.periodo
            order by ies.gestion_tipo_id desc, pet.id desc, sat.codigo, sast.id, smt.id
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que halla la carga horaria de un estudiante que homologo su certificado de gestiones pasadas segun su especialidad y nivel
    // PARAMETROS: participanteId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getCertTecCargaHorariaHomologadoEstudiante($participanteId, $especialidadId, $nivelId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select e.id as estudiante_id, e.codigo_rude, e.paterno||' '||e.materno||' '||e.nombre as participante
            , sest.id as especialidad_id, sest.especialidad, h.grado_id as nivel_id
            , (case h.grado_id when 1 then 'TÉCNICO BÁSICO' when 2 then 'TÉCNICO AUXILIAR' when 3 then 'TÉCNICO MEDIO' else '' end) as acreditacion
            , h.carga_horaria as carga_horaria, 'homologacion' as modulo
            from homologacion as h
            inner join superior_especialidad_tipo as sest on sest.id = h.ciclo_id
            inner join estudiante as e on e.id = h.estudiante_id
            where h.estudiante_id = ".$participanteId." and h.ciclo_id = ".$especialidadId." and h.grado_id = ".$nivelId."
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los certificados extendidos de un estudiante segun su especialidad y nivel
    // PARAMETROS: estudianteInscripcionId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getCertTecDocumentoEspecialidadNivelEstudiante($estudianteId, $especialidadId, $nivelId, $gestionId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct on (sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, e.codigo_rude)
            ei.id as estudiante_inscripcion_id,ei.estudiante_id as estudiante_id, sat.codigo as nivel, ies.periodo_tipo_id as periodo, iec.periodo_tipo_id as per, siea.institucioneducativa_id as institucioneducativa
            , sest.id as especialidad_id, ies.gestion_tipo_id,ies.periodo_tipo_id,siea.institucioneducativa_id, sfat.codigo as nivel_id, sfat.facultad_area, sest.codigo as ciclo_id
            ,sest.especialidad,sat.codigo as grado_id,sat.acreditacion,ei.id as estudiante_inscripcion,e.codigo_rude, e.nombre, e.paterno, e.materno, to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento
            , cast(e.carnet_identidad as varchar)||(case when e.complemento is null then '' when e.complemento = '' then '' else '-'||e.complemento end) as carnet_identidad
            , case pt.id when 1 then lt2.lugar when 0 then '' else pt.pais end as lugar_nacimiento
            , t.id as tramite_id,ei.estadomatricula_tipo_id/*, d.id as documento_id, d.documento_serie_id as documento_serie_id*/, segip_id, dt.documento_tipo
            from superior_facultad_area_tipo as sfat
            inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
            inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
            inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id
            inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
            inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
            inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id
            inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id
            inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id
            inner join estudiante as e on ei.estudiante_id=e.id
            left JOIN lugar_tipo as lt1 on lt1.id = e.lugar_prov_nac_tipo_id
            left JOIN lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
            left join pais_tipo pt on pt.id = e.pais_tipo_id
            inner join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (6,7,8) and (case t.tramite_tipo when 6 then 1 when 7 then 2 when 8 then 3 else 0 end) = sat.codigo and t.esactivo = 't'
            inner join documento as d on d.tramite_id = t.id and documento_tipo_id in (6,7,8) and d.documento_estado_id = 1
            inner join documento_tipo as dt on dt.id = d.documento_tipo_id
            where e.id = ".$estudianteId." and sest.id = ".$especialidadId." and sat.codigo = ".$nivelId." and t.gestion_id = ".$gestionId." and sfat.codigo in (18,19,20,21,22,23,24,25) and ei.estadomatricula_tipo_id in (4,5,55)
            order by sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, e.codigo_rude, ies.periodo_tipo_id desc
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los tramites en curso de un participante en técnica alternativa segun especialidad y nivel
    // PARAMETROS: estudianteId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getCertTecTramiteEspecialidadNivelEstudiante($estudianteId, $especialidadId, $nivelId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct on (sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, e.codigo_rude)
            ei.id as estudiante_inscripcion_id,ei.estudiante_id as estudiante_id, sat.codigo as nivel, ies.periodo_tipo_id as periodo, iec.periodo_tipo_id as per, siea.institucioneducativa_id as institucioneducativa
            , sest.id as especialidad_id, ies.gestion_tipo_id,ies.periodo_tipo_id,siea.institucioneducativa_id, sfat.codigo as nivel_id, sfat.facultad_area, sest.codigo as ciclo_id
            ,sest.especialidad,sat.codigo as grado_id,sat.acreditacion,ei.id as estudiante_inscripcion,e.codigo_rude, e.nombre, e.paterno, e.materno, to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento
            , cast(e.carnet_identidad as varchar)||(case when e.complemento is null then '' when e.complemento = '' then '' else '-'||e.complemento end) as carnet_identidad
            , case pt.id when 1 then lt2.lugar when 0 then '' else pt.pais end as lugar_nacimiento
            , t.id as tramite_id,ei.estadomatricula_tipo_id/*, d.id as documento_id, d.documento_serie_id as documento_serie_id*/, segip_id
            from superior_facultad_area_tipo as sfat
            inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
            inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
            inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id
            inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
            inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
            inner join superior_institucioneducativa_periodo as siep on siep.superior_institucioneducativa_acreditacion_id = siea.id
            inner join institucioneducativa_curso as iec on iec.superior_institucioneducativa_periodo_id = siep.id
            inner join estudiante_inscripcion as ei on iec.id=ei.institucioneducativa_curso_id
            inner join estudiante as e on ei.estudiante_id=e.id
            left JOIN lugar_tipo as lt1 on lt1.id = e.lugar_prov_nac_tipo_id
            left JOIN lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
            left join pais_tipo pt on pt.id = e.pais_tipo_id
            inner join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (6,7,8) and (case t.tramite_tipo when 6 then 1 when 7 then 2 when 8 then 3 else 0 end) = sat.codigo and t.esactivo = 't'
            where e.id = ".$estudianteId." and sest.id = ".$especialidadId." and sat.codigo = ".$nivelId." and sfat.codigo in (18,19,20,21,22,23,24,25) and ei.estadomatricula_tipo_id in (4,5,55)
            order by sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, e.codigo_rude, ies.periodo_tipo_id desc
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los tramites en curso de un estudiante en educación regular humanisitca
    // PARAMETROS: estudianteId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDipHumTramiteEstudiante($estudianteId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
          SELECT
          dt.id as dependencia_tipo_id, dt.dependencia,
          oct.id as orgcurricular_tipo_id,  oct.orgcurricula,
          ie.le_juridicciongeografica_id, ie.id as institucioneducativa_id, ie.institucioneducativa,
          iec.gestion_tipo_id, nt.id as nivel_tipo_id, nt.nivel, ct.id as ciclo_tipo_id,  ct.ciclo,
          iec.grado_tipo_id, pt.id as paralelo_tipo_id, pt.paralelo, tt.id as turno_tipo_id, tt.turno,
          pet.id as periodo_tipo_id, pet.periodo,
          e.id as estudiante_id, e.codigo_rude, e.carnet_identidad as carnet, e.complemento,
          cast(e.carnet_identidad as varchar)||(case when complemento is null then '' when complemento = '' then '' else '-'||complemento end) as carnet_identidad,
          e.pasaporte, e.paterno,  e.materno, e.nombre,
          gt.id as genero_tipo_id, gt.genero, to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento,  e.localidad_nac,
          emt.id as estadomatricula_tipo_id, emt.estadomatricula, ei.id as estudiante_inscripcion_id, e.segip_id,
          case pat.id when 1 then lt2.lugar when 0 then '' else pat.pais end as lugar_nacimiento,
          CASE
          WHEN iec.nivel_tipo_id = 13 THEN
          'Regular Humanística'
          WHEN iec.nivel_tipo_id = 15 THEN
          'Alternativa Humanística'
          WHEN iec.nivel_tipo_id > 17 THEN
          'Alternativa Técnica'
          END AS subsistema,
          e.lugar_prov_nac_tipo_id as lugar_nacimiento_id, lt2.codigo as depto_nacimiento_id, lt2.lugar as depto_nacimiento,
          t.id as tramite_id--, d.id as documento_id, d.documento_serie_id as documento_serie_id
          FROM estudiante as e
          INNER JOIN estudiante_inscripcion as ei on ei.estudiante_id = e.id
          INNER JOIN institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
          INNER JOIN institucioneducativa as ie on ie.id = iec.institucioneducativa_id
          INNER JOIN estadomatricula_tipo as emt on emt.id = ei.estadomatricula_tipo_id
          INNER JOIN dependencia_tipo as dt on dt.id = ie.dependencia_tipo_id
          INNER JOIN orgcurricular_tipo as oct on oct.id = ie.orgcurricular_tipo_id
          INNER JOIN nivel_tipo as nt on nt.id = iec.nivel_tipo_id
          INNER JOIN ciclo_tipo as ct on ct.id = iec.ciclo_tipo_id
          INNER JOIN paralelo_tipo as pt on pt.id = iec.paralelo_tipo_id
          INNER JOIN turno_tipo as tt on tt.id = iec.turno_tipo_id
          INNER JOIN periodo_tipo as pet on pet.id = iec.periodo_tipo_id
          INNER JOIN genero_tipo as gt on gt.id = e.genero_tipo_id
          LEFT JOIN lugar_tipo as lt1 on lt1.id = e.lugar_prov_nac_tipo_id
          LEFT JOIN lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
          left join pais_tipo as pat on pat.id = e.pais_tipo_id
          inner join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (1) and t.esactivo = 't'
          --left join documento as d on d.tramite_id = t.id and documento_tipo_id in (1,9) and d.documento_estado_id = 1
          WHERE e.id = ".$estudianteId." -- and ei.estadomatricula_tipo_id in (4,5,55)
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista los documentos (Diplomas de Bachiller Humanistico) vigente de un estudiante en educación regular humanisitca
    // PARAMETROS: estudianteId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDipHumDocumentoEstudiante($estudianteId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
          SELECT
          dt.id as dependencia_tipo_id, dt.dependencia,
          oct.id as orgcurricular_tipo_id,  oct.orgcurricula,
          ie.le_juridicciongeografica_id, ie.id as institucioneducativa_id, ie.institucioneducativa,
          iec.gestion_tipo_id, nt.id as nivel_tipo_id, nt.nivel, ct.id as ciclo_tipo_id,  ct.ciclo,
          iec.grado_tipo_id, pt.id as paralelo_tipo_id, pt.paralelo, tt.id as turno_tipo_id, tt.turno,
          pet.id as periodo_tipo_id, pet.periodo,
          e.id as estudiante_id, e.codigo_rude, e.carnet_identidad as carnet, e.complemento,
          cast(e.carnet_identidad as varchar)||(case when complemento is null then '' when complemento = '' then '' else '-'||complemento end) as carnet_identidad,
          e.pasaporte, e.paterno,  e.materno, e.nombre,
          gt.id as genero_tipo_id, gt.genero, to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento,  e.localidad_nac,
          emt.id as estadomatricula_tipo_id, emt.estadomatricula, ei.id as estudiante_inscripcion_id, e.segip_id,
          case pat.id when 1 then lt2.lugar when 0 then '' else pat.pais end as lugar_nacimiento,
          CASE
          WHEN iec.nivel_tipo_id = 13 THEN
          'Regular Humanística'
          WHEN iec.nivel_tipo_id = 15 THEN
          'Alternativa Humanística'
          WHEN iec.nivel_tipo_id > 17 THEN
          'Alternativa Técnica'
          END AS subsistema,
          e.lugar_prov_nac_tipo_id as lugar_nacimiento_id, lt2.codigo as depto_nacimiento_id, lt2.lugar as depto_nacimiento,
          t.id as tramite_id, d.id as documento_id, d.documento_serie_id as documento_serie_id
          FROM estudiante as e
          INNER JOIN estudiante_inscripcion as ei on ei.estudiante_id = e.id
          INNER JOIN institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
          INNER JOIN institucioneducativa as ie on ie.id = iec.institucioneducativa_id
          INNER JOIN estadomatricula_tipo as emt on emt.id = ei.estadomatricula_tipo_id
          INNER JOIN dependencia_tipo as dt on dt.id = ie.dependencia_tipo_id
          INNER JOIN orgcurricular_tipo as oct on oct.id = ie.orgcurricular_tipo_id
          INNER JOIN nivel_tipo as nt on nt.id = iec.nivel_tipo_id
          INNER JOIN ciclo_tipo as ct on ct.id = iec.ciclo_tipo_id
          INNER JOIN paralelo_tipo as pt on pt.id = iec.paralelo_tipo_id
          INNER JOIN turno_tipo as tt on tt.id = iec.turno_tipo_id
          INNER JOIN periodo_tipo as pet on pet.id = iec.periodo_tipo_id
          INNER JOIN genero_tipo as gt on gt.id = e.genero_tipo_id
          LEFT JOIN lugar_tipo as lt1 on lt1.id = e.lugar_prov_nac_tipo_id
          LEFT JOIN lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
          left join pais_tipo as pat on pat.id = e.pais_tipo_id
          inner join tramite as t on t.estudiante_inscripcion_id = ei.id and tramite_tipo in (1) and t.esactivo = 't'
          inner join documento as d on d.tramite_id = t.id and documento_tipo_id in (1,9) and d.documento_estado_id = 1
          WHERE e.id = ".$estudianteId." -- and ei.estadomatricula_tipo_id in (4,5,55)
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que registra los trámites del participante
    // PARAMETROS: estudianteInscripcionId, gestionId, tramiteTipoId, flujoTipoId, em
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function setTramiteEstudiante($estudianteInscripcionId, $gestionId, $tramiteTipoId, $flujoTipoId, $em) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $entityTramite = new Tramite();
        /*
         * Forma las entidades para ingresar sus valores (solo en caso de campos relacionados) - Tramite
         */
        $entityTramiteTipo = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('id' => $tramiteTipoId));
        $entityFlujoTipo = $em->getRepository('SieAppWebBundle:FlujoTipo')->findOneBy(array('id' => $flujoTipoId));
        $entityEstudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));

        /*
         * Define el conjunto de valores a ingresar - Tramite
         */
        $entityTramite->setTramite($entityTramite->getId());
        $entityTramite->setEstudianteInscripcion($entityEstudianteInscripcion);
        $entityTramite->setFlujoTipo($entityFlujoTipo);
        $entityTramite->setTramiteTipo($entityTramiteTipo);
        $entityTramite->setFechaTramite($fechaActual);
        $entityTramite->setFechaRegistro($fechaActual);
        $entityTramite->setEsactivo('1');
        $entityTramite->setGestionId($gestionId);
        $em->persist($entityTramite);
        $em->flush();

        $entityTramite->setTramite($entityTramite->getId());
        $em->persist($entityTramite);
        $em->flush();

        /*
         * Extra el id del registro ingresado de la tabla tramite
         */
        $tramiteId = $entityTramite->getId();

        return $tramiteId;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida el proceso de registro de un trámite certificado tecnico alternativa segun el participante, especialidad y nivel
    // PARAMETROS: estudianteId, gestionId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getCertTecValidacion($participanteId, $especialidadId, $nivelId, $gestionId) {
        $msgContenido = "";
        $cargaHorariaTotal = 0;

        if ($nivelId == 1) {
            $nivel = 'Técnico Básico';
        } elseif ($nivelId == 2) {
            $nivel = 'Técnico Auxiliar';
        } elseif ($nivelId == 3) {
            $nivel = 'Técnico Medio';
        } else {
            $nivel = '';
        }

        // VALIDACION DE MODULOS REPETIDOS POR ESTUDIANTE SEGUN MODULOS APROBADOS (36 O 51)
        $objModulosObservados = $this->getCertTecModuloObsEstudiante($participanteId, $especialidadId, $nivelId);
        if(count($objModulosObservados)>0){
            $msgContenido = ($msgContenido=="") ? "cuenta con módulos duplicados en ".$nivel.": ".$objModulosObservados[0]['modulos'] : $msgContenido.", cuenta con módulos duplicados: ".$objModulosObservados[0]['modulos'];
        }

        // VALIDACION DE CARGA HORARIA POR ESTUDIANTE SEGUN MODULOS APROBADOS (MAYORES A 36 O 51)
        $valCertTecCargaHoraria = $this->getCertTecCargaHorariaEstudiante($participanteId, $especialidadId, $nivelId);
        $cargaHoraria = 0;
        if(!$valCertTecCargaHoraria[0]){
            $msgContenido = ($msgContenido=="") ? $valCertTecCargaHoraria[1] : $msgContenido.', '.$valCertTecCargaHoraria[1];
        } else {
            $cargaHoraria = $valCertTecCargaHoraria[1];
        }

        // VALIDACION DE UNA CERTIFICACION ANTERIOR PARA CONTINUAR CON EL SIGUIENTE NIVEL
        // TECNICO MEDIO
        if($nivelId == 3){
            $valCertTecCargaHorariaAuxiliar = $this->getCertTecCargaHorariaEstudiante($participanteId, $especialidadId, 2);
            // VALIDACION DE MODULOS REPETIDOS POR ESTUDIANTE SEGUN MODULOS APROBADOS (36 O 51)
            $objModulosObservados = $this->getCertTecModuloObsEstudiante($participanteId, $especialidadId, 2);
            if(count($objModulosObservados)>0){
                $msgContenido = ($msgContenido=="") ? "cuenta con módulos duplicados en nivel auxiliar: ".$objModulosObservados[0]['modulos'] : $msgContenido.", cuenta con módulos duplicados: ".$objModulosObservados[0]['modulos'];
            }
            $valCertTecCargaHorariaBasico = $this->getCertTecCargaHorariaEstudiante($participanteId, $especialidadId, 1);
            // VALIDACION DE MODULOS REPETIDOS POR ESTUDIANTE SEGUN MODULOS APROBADOS (36 O 51)
            $objModulosObservados = $this->getCertTecModuloObsEstudiante($participanteId, $especialidadId, 1);
            if(count($objModulosObservados)>0){
                $msgContenido = ($msgContenido=="") ? "cuenta con módulos duplicados en nivel básico: ".$objModulosObservados[0]['modulos'] : $msgContenido.", cuenta con módulos duplicados: ".$objModulosObservados[0]['modulos'];
            }
            $cargaHorariaAuxiliar = 0;
            $cargaHorariaBasico = 0;
            if(!$valCertTecCargaHorariaAuxiliar[0]){
                $msgContenido = ($msgContenido=="") ? $valCertTecCargaHorariaAuxiliar[1] : $msgContenido.', '.$valCertTecCargaHorariaAuxiliar[1];
            } else {
                $cargaHorariaAuxiliar = $valCertTecCargaHorariaAuxiliar[1];
            }
            if(!$valCertTecCargaHorariaBasico[0]){
                $msgContenido = ($msgContenido=="") ? $valCertTecCargaHorariaBasico[1] : $msgContenido.', '.$valCertTecCargaHorariaBasico[1];
            }  else {
                $cargaHorariaBasico = $valCertTecCargaHorariaBasico[1];
            }

            $cargaHorariaTotal = $cargaHoraria + $cargaHorariaAuxiliar + $cargaHorariaBasico;
            $valCertTecCargaHorarianivel = $this->certTecCargaHorariaNivel($nivelId,$cargaHorariaTotal);
            if ($valCertTecCargaHorarianivel == ''){
                $msgContenido = ($msgContenido=="") ? $valCertTecCargaHorarianivel : $msgContenido.', '.$valCertTecCargaHorarianivel;
            }
        }

        // TECNICO AUXILIAR
        if($nivelId == 2){
            $valCertTecCargaHorariaBasico = $this->getCertTecCargaHorariaEstudiante($participanteId, $especialidadId, 1);
            // VALIDACION DE MODULOS REPETIDOS POR ESTUDIANTE SEGUN MODULOS APROBADOS (36 O 51)
            $objModulosObservados = $this->getCertTecModuloObsEstudiante($participanteId, $especialidadId, 1);
            if(count($objModulosObservados)>0){
                $msgContenido = ($msgContenido=="") ? "cuenta con módulos duplicados en nivel básico: ".$objModulosObservados[0]['modulos'] : $msgContenido.", cuenta con módulos duplicados: ".$objModulosObservados[0]['modulos'];
            }
            $cargaHorariaBasico = 0;
            if(!$valCertTecCargaHorariaBasico[0]){
                $msgContenido = ($msgContenido=="") ? $valCertTecCargaHorariaBasico[1] : $msgContenido.', '.$valCertTecCargaHorariaBasico[1];
            }  else {
                $cargaHorariaBasico = $valCertTecCargaHorariaBasico[1];
            }

            $cargaHorariaTotal = $cargaHoraria + $cargaHorariaBasico;

            $valCertTecCargaHorarianivel = $this->certTecCargaHorariaNivel($nivelId,$cargaHorariaTotal);
            if ($valCertTecCargaHorarianivel == ''){
                $msgContenido = ($msgContenido=="") ? $valCertTecCargaHorarianivel : $msgContenido.', '.$valCertTecCargaHorarianivel;
            }
        }

        // VALIDACION DE SOLO UN TIPO DE CERTIFICACION POR ESTUDIANTE (RUDE)
        $valCertTecDocumentoEspNivel = $this->getCertTecDocumentoEspecialidadNivelEstudiante($participanteId, $especialidadId, $nivelId, $gestionId);
        if(count($valCertTecDocumentoEspNivel) > 0){
            $msgContenido = ($msgContenido=="") ? 'ya cuenta con la '.$valCertTecDocumentoEspNivel[0]['documento_tipo'] : $msgContenido.', ya cuenta con la '.$valCertTecDocumentoEspNivel[0]['documento_tipo'];
        }

        return $msgContenido;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida el proceso de registro de un trámite diploma humanistico regular segun el participante y gestion
    // PARAMETROS: estudianteId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDipHumRegularValidacion($participanteId, $estudianteInscripcionId, $gestionId) {
        $msgContenido = "";
        $cargaHorariaTotal = 0;
        $gestionServidor= new \DateTime();
        $gestionActual = $gestionServidor->format('Y');

        // VALIDACION DE ASIGNATURAS CON NOTAS COMPLETAS (4 BIMESTRAS O 3 TRIMESTRES EN GESTION ANTERIORES  Y 1ER BIMESTRE EN GESTION ACTUAL )
        $objCalificacionesObservados = $this->getDipHumRegularCalificacionObsEstudiante($participanteId);
        if(count($objCalificacionesObservados)>0){
            $msgContenido = ($msgContenido=="") ? "cuenta con asignaturas sin calificaciones en: ".$objCalificacionesObservados[0]['asignaturas'] : $msgContenido.", cuenta con asignaturas sin calificaciones en: ".$objCalificacionesObservados[0]['asignaturas'];
        }

        // VALIDACION DE SOLO UN TRAMITE POR ESTUDIANTE (RUDE)
        $valTramiteEstudiante = $this->getDipHumTramiteEstudiante($participanteId);
        if(count($valTramiteEstudiante) > 0){
            $msgContenido = ($msgContenido=="") ? 'ya cuenta con el trámite '.$valTramiteEstudiante[0]['tramite_id'] : $msgContenido.', ya cuenta con el trámite '.$valTramiteEstudiante[0]['tramite_id'];
        }

        // VALIDACION DE SOLO UN DIPLOMA BACHILLER HUMANISTICO POR ESTUDIANTE (RUDE)
        $valDocumentoEstudiante = $this->getDipHumDocumentoEstudiante($participanteId);
        if(count($valDocumentoEstudiante) > 0){
            $msgContenido = ($msgContenido=="") ? 'ya cuenta con el Diploma de Bachiller Humanístico '.$valDocumentoEstudiante[0]['documento_serie_id'] : $msgContenido.', ya cuenta con el Diploma de Bachiller Humanístico '.$valDocumentoEstudiante[0]['documento_serie_id'];
        }

        return $msgContenido;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que valida el proceso de registro de un trámite diploma humanistico regular segun el participante y gestion
    // PARAMETROS: estudianteId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDipHumAlternativaValidacion($participanteId, $estudianteInscripcionId) {
        $msgContenido = "";
        $cargaHorariaTotal = 0;
        $gestionServidor= new \DateTime();
        $gestionActual = $gestionServidor->format('Y');

        // VALIDACION DE ASIGNATURAS CON NOTAS COMPLETAS (4 BIMESTRAS O 3 TRIMESTRES EN GESTION ANTERIORES  Y 1ER BIMESTRE EN GESTION ACTUAL )
        $objCalificacionesObservados = $this->getDipHumAlternativaCalificacionObsEstudiante($participanteId);
        if(count($objCalificacionesObservados)>0){
            $msgContenido = ($msgContenido=="") ? "cuenta con asignaturas sin calificaciones en: ".$objCalificacionesObservados[0]['asignaturas'] : $msgContenido.", cuenta con asignaturas sin calificaciones en: ".$objCalificacionesObservados[0]['asignaturas'];
        }

        // VALIDACION DE SOLO UN TRAMITE POR ESTUDIANTE (RUDE)
        $valTramiteEstudiante = $this->getDipHumTramiteEstudiante($participanteId);
        if(count($valTramiteEstudiante) > 0){
            $msgContenido = ($msgContenido=="") ? 'ya cuenta con el trámite '.$valTramiteEstudiante[0]['tramite_id'] : $msgContenido.', ya cuenta con el trámite '.$valTramiteEstudiante[0]['tramite_id'];
        }

        // VALIDACION DE SOLO UN TRAMITE POR ESTUDIANTE (RUDE)
        $valDocumentoEstudiante = $this->getDipHumDocumentoEstudiante($participanteId);
        if(count($valDocumentoEstudiante) > 0){
            $msgContenido = ($msgContenido=="") ? 'ya cuenta con el Diploma de Bachiller Humanístico '.$valDocumentoEstudiante[0]['documento_serie_id'] : $msgContenido.', ya cuenta con el Diploma de Bachiller Humanístico '.$valDocumentoEstudiante[0]['documento_serie_id'];
        }

        return $msgContenido;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // funcion que compara las cargas horarias segun nivel tecnico de alternativa
    // PARAMETROS: nivelId, cargaHoraria
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecCargaHorariaNivel($nivelId,$cargaHoraria) {
        $msg = '';
        if ($nivelId == 1) {
            if ($cargaHoraria < 800){
                $msg = 'Solo cuenta con '.$cargaHoraria.' de 800 horas';
            }
        } elseif ($nivelId == 2) {
            if ($cargaHoraria < 1200){
                $msg = 'Solo cuenta con '.$cargaHoraria.' de 1200 horas';
            }
        } elseif ($nivelId == 3) {
            if ($cargaHoraria < 2000){
                $msg = 'Solo cuenta con '.$cargaHoraria.' de 2000 horas';
            }
        } else {
            $msg = 'Nivel no encontrado';
        }
        return $msg;
    }


    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que genera un formulario de busqueda para reactivar el tramite de un determinado documento
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function reactivaBuscaAction(Request $request) {
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

        $rolPermitido = array(8,16);

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $activeMenu = $defaultTramiteController->setActiveMenu();

        $documentoController = new documentoController();
        $documentoController->setContainer($this->container);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        return $this->render($this->session->get('pathSystem') . ':Tramite:reactivaIndex.html.twig', array(
            'formBusqueda' => $documentoController->creaFormAnulaDocumentoSerie('tramite_reactiva_lista','','')->createView(),
            'titulo' => 'Reactivar',
            'subtitulo' => 'Trámite',
        ));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que lista el detalle del documento requerido para su reactivación
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function reactivaListaAction(Request $request) {
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

        if ($request->isMethod('POST')) {
            $form = $request->get('form');
            if ($form) {
                $serie = $form['serie'];
                $obs = $form['obs'];
                try {
                    $documentoController = new documentoController();
                    $documentoController->setContainer($this->container);

                    $msgContenido = "";
                    $valNumeroSerie = $documentoController->getNumeroSerie($serie);
                    if($valNumeroSerie != "" and $msgContenido == ""){
                        $msgContenido = ($msgContenido=="") ? $valNumeroSerie : $msgContenido.", ".$valNumeroSerie;
                    }
                    $valNumeroSerieActivo = $documentoController->validaNumeroSerieActivo($serie);
                    if($valNumeroSerieActivo != "" and $msgContenido == ""){
                        $msgContenido = ($msgContenido=="") ? $valNumeroSerieActivo : $msgContenido.", ".$valNumeroSerieActivo;
                    }
                    $valNumeroSerieAsignado = $documentoController->validaNumeroSerieAsignado($serie);
                    if($valNumeroSerieAsignado != "" and $msgContenido == ""){
                        $msgContenido = ($msgContenido=="") ? $valNumeroSerieAsignado : $msgContenido.", ".$valNumeroSerieAsignado;
                    }
                    $departamentoCodigo = $documentoController->getCodigoLugarRol($id_usuario,16);
                    if ($departamentoCodigo == 0 and $msgContenido == ""){
                        $msgContenido = ($msgContenido=="") ? "el usuario no cuenta con autorizacion para imprimir el documento ".$serie : $msgContenido.", "."el usuario no cuenta con autorizacion para imprimir el documento ".$serie;
                    } else {
                        // VALIDACION DE TUICION DEL CARTON
                        $valSerieTuicion = $documentoController->validaNumeroSerieTuicion($serie, $departamentoCodigo);
                        if($valSerieTuicion != "" and $msgContenido == ""){
                            $msgContenido = ($msgContenido=="") ? $valSerieTuicion : $msgContenido.", ".$valSerieTuicion;
                        }
                    }

                    if($msgContenido == ""){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msgContenido));
                        return $this->redirect($this->generateUrl('tramite_reactiva_busca'));
                    }

                    $entityDocumento = $documentoController->getDocumentoSerieEstado($serie,1);

                    if(count($entityDocumento)<=0){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'El documento con número de serie '));
                        return $this->redirect($this->generateUrl('tramite_reactiva_busca'));
                    }

                    $tramiteProcesoController = new tramiteProcesoController();
                    $tramiteProcesoController->setContainer($this->container);

                    $valUltimoProcesoFlujoTramite = $tramiteProcesoController->valUltimoProcesoFlujoTramite($entityDocumento['tramite']);

                    $entityTramiteDetalle = $tramiteProcesoController->getTramiteDetalle($entityDocumento['tramite']);


                    return $this->render($this->session->get('pathSystem') . ':Seguimiento:tramiteDetalle.html.twig', array(
                        'formBusqueda' => $documentoController->creaFormAnulaDocumentoSerie('tramite_reactiva_lista','','')->createView(),
                        'titulo' => 'Seguimiento',
                        'subtitulo' => 'Trámite',
                        'msgReactivaTramite' => $valUltimoProcesoFlujoTramite,
                        'listaDocumento' => $entityDocumento,
                        'listaTramiteDetalle' => $entityTramiteDetalle,
                        'arrayForm' => array('serie'=>$serie, 'obs'=>$obs),
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_reactiva_busca'));
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_reactiva_busca'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_reactiva_busca'));
        }
    }


    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra la reactivacion de un documento
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function reactivaGuardaAction(Request $request) {
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

        if ($request->isMethod('POST')) {
            $serie = $request->get('serie');
            $obs = $request->get('obs');
            if ($serie != "" and $obs != ""){
                try {
                    $documentoController = new documentoController();
                    $documentoController->setContainer($this->container);

                    $entityDocumento = $documentoController->getDocumentoSerieEstado($serie,1);

                    if(count($entityDocumento)<=0){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Documento no encontrado, intente nuevamente'));
                        return $this->redirect($this->generateUrl('tramite_reactiva_busca'));
                    }

                    $tramiteProcesoController = new tramiteProcesoController();
                    $tramiteProcesoController->setContainer($this->container);

                    $tramiteId = $entityDocumento['tramite'];
                    $documentoId = $entityDocumento['documento'];

                    $em = $this->getDoctrine()->getManager();
                    $entityTramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id' => $tramiteId));

                    $entityFlujoProceso = $tramiteProcesoController->getImpresionProcesoFlujo($entityTramite->getFlujoTipo()->getId());
                    $entityFlujoProcesoDetalle = $em->getRepository('SieAppWebBundle:FlujoProcesoDetalle')->findOneBy(array('id' => $entityFlujoProceso->getId()));

                    $valProcesaTramite = $tramiteProcesoController->setProcesaTramite($tramiteId,$entityFlujoProcesoDetalle->getFlujoProcesoAnt()->getId(),$id_usuario,$obs);

                    $documentoId = $documentoController->setDocumentoEstado($documentoId, 2);

                    $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Trámite '.$tramiteId.' con documento nro. '.$serie.' reactivado, el trámite se encuentra en la BANDEJA DE IMPRESIÓN'));

                    $formBusqueda = array('serie'=>$serie,'obs'=>$obs);
                    //return $this->redirectToRoute('sie_tramite_reactiva_lista', ['form' => $formBusqueda], 307);
                    return $this->redirect($this->generateUrl('tramite_reactiva_busca'));

                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('tramite_reactiva_busca'));
                }
            } else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_reactiva_busca'));
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_reactiva_busca'));
        }
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que verifica la tuicion sobre una institucion educativa segun el usuario, rol y subsistema
    // PARAMETROS: usuarioId, sie, rol, sysName
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function verTuicionUnidadEducativa($usuarioId, $sie, $rol, $sysName){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
        $query->bindValue(':user_id', $usuarioId);
        $query->bindValue(':sie', $sie);
        if($sysName == 'DIPLOMAS' or $sysName == 'TRAMITES'){
            $defaultTramiteController = new defaultTramiteController();
            $defaultTramiteController->setContainer($this->container);
            $rolOpcionTramite = 0;

            $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($usuarioId,8);
            if($esValidoUsuarioRol and $rolOpcionTramite == 0){
                $rolOpcionTramite = 8;
            }
            $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($usuarioId,16);
            if($esValidoUsuarioRol and $rolOpcionTramite == 0){
                $rolOpcionTramite = 16;
            }
            $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($usuarioId,15);
            if($esValidoUsuarioRol and $rolOpcionTramite == 0){
                $rolOpcionTramite = 15;
            }
            $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($usuarioId,14);
            if($esValidoUsuarioRol and $rolOpcionTramite == 0){
                $rolOpcionTramite = 14;
            }
            $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($usuarioId,13);
            if($esValidoUsuarioRol and $rolOpcionTramite == 0){
                $rolOpcionTramite = 13;
            }
            $query->bindValue(':roluser', $rolOpcionTramite);
        } else {
            $query->bindValue(':roluser', $rol);
        }
        $query->execute();
        $aTuicion = $query->fetchAll();

        if (!$aTuicion[0]['get_ue_tuicion']) {
            $msg = 'No tiene tuición sobre la Unidad Educativa';
        } else {
            $msg = '';
        }
        return $msg;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que genera un formulario para la busqueda de tramite por numero de trámite
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function creaFormBuscaTramite($routing, $tramite) {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl($routing))
            ->add('tramite', 'number', array('label' => 'TRÁMITE', 'attr' => array('value' => $tramite, 'class' => 'form-control', 'placeholder' => 'Número de trámite', 'pattern' => '[0-9]{1,15}', 'maxlength' => '15', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
            ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();
        return $form;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que genera un formulario para la busqueda de tramite por código rude
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function creaFormBuscaTramiteRude($routing, $rude) {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl($routing))
            ->add('rude', 'text', array('label' => 'Rude', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $rude, 'style' => 'text-transform:uppercase', 'placeholder' => 'Código Rude' , 'maxlength' => 20, 'required' => true, 'class' => 'form-control')))
            ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();
        return $form;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que genera un formulario para la busqueda de tramite por cédula de identidad
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function creaFormBuscaTramiteCedula($routing, $cedula) {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl($routing))
            ->add('cedula', 'text', array('label' => 'Cedula', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $cedula, 'style' => 'text-transform:uppercase', 'placeholder' => 'Cédula de Identidad' , 'maxlength' => 10, 'required' => true, 'class' => 'form-control')))
            ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();
        return $form;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que genera el historial academico de un participante en funcion a la especialidad y nivel
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function certTecRegistroInscripcionHistorialAction(Request $request) {

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

        $estudianteId = $request->get('inscripcion');
        $especialidadId = $request->get('especialidad');
        $nivelId = $request->get('nivel');

        $entityCargaHorariaInscripcion = $this->getCertTecCargaHorariaInscripcion(base64_decode($estudianteId), base64_decode($especialidadId), base64_decode($nivelId));

        $gestion = $gestionActual->format('Y');

        return $this->render($this->session->get('pathSystem') . ':Tramite:participanteHistorial.html.twig', array(
            'titulo' => 'Registro',
            'subtitulo' => 'Trámite',
            'listaModuloCargaHoraria' => $entityCargaHorariaInscripcion,
            ));
    }




    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que genera el historial academico de un participante de educacion regular en funcion a su inscripcion
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipHumEstudianteInscripcionHistorialAction(Request $request) {

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

        $estudianteInscripcionId = base64_decode($request->get('inscripcion'));

        $em = $this->getDoctrine()->getManager();
        $entidadEstudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $estudianteInscripcionId));
        $institucionEducativaId = $entidadEstudianteInscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $estudianteId =  $entidadEstudianteInscripcion->getEstudiante()->getId();

        $entitySubsistemaInstitucionEducativa = $this->getSubSistemaInstitucionEducativa($institucionEducativaId);
        if($entitySubsistemaInstitucionEducativa['msg'] != ''){
            $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => $entitySubsistemaInstitucionEducativa['msg']));
            return $this->redirect($this->generateUrl('tramite_detalle_diploma_humanistico_regular_autorizacion_busca '));
        }

        if($entitySubsistemaInstitucionEducativa['id']==1){
          $listaHistorial = $this->getDipHumRegularEstudianteInscripcionHistorial($estudianteId);
          return $this->render($this->session->get('pathSystem') . ':Tramite:estudianteRegularHistorial.html.twig', array(
              'titulo' => 'Registro',
              'subtitulo' => 'Trámite',
              'listaHistorial' => $listaHistorial,
              ));
        } else {
          $listaHistorial = $this->getDipHumAlternativaEstudianteInscripcionHistorial($estudianteId);
          return $this->render($this->session->get('pathSystem') . ':Tramite:estudianteAlternativaHistorial.html.twig', array(
              'titulo' => 'Registro',
              'subtitulo' => 'Trámite',
              'listaHistorial' => $listaHistorial,
              ));
        }
    }


    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que genera el historial academico de un participante de educacion regular en funcion a su inscripcion
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDipHumRegularEstudianteInscripcionHistorial($estudianteId) {

        $entityInscripcion = $this->getDipHumRegularHistorial($estudianteId);

        $gradoId = 0;
        $i = 0;
        $j = 0;
        foreach ($entityInscripcion as $registro)
        {
          if ($gradoId != $registro['grado_tipo_id']) {
            $gradoId = $registro['grado_tipo_id'];
            $i = 0;
          }
          if(($registro['gestion_tipo_id'] > 2013) or ($registro['gestion_tipo_id'] == 2013 and $registro['grado_tipo_id'] == 1)){
            $listaHistorial[$registro['grado_tipo_id']][$i] = array(
                                                                      'codigo_rude'=>$registro['codigo_rude'],
                                                                      'paterno'=>$registro['paterno'],
                                                                      'materno'=>$registro['materno'],
                                                                      'nombre'=>$registro['nombre'],
                                                                      'institucioneducativa_id'=>$registro['institucioneducativa_id'],
                                                                      'institucioneducativa'=>$registro['institucioneducativa'],
                                                                      'turno'=>$registro['turno'],
                                                                      'grado_tipo_id'=>$registro['grado_tipo_id'],
                                                                      'grado'=>$registro['grado'],
                                                                      'paralelo'=>$registro['paralelo'],
                                                                      'gestion_tipo_id'=>$registro['gestion_tipo_id'],
                                                                      'estadomatricula_tipo_id'=>$registro['estadomatricula_tipo_id'],
                                                                      'asignatura_tipo_id'=>$registro['asignatura_tipo_id'],
                                                                      'asignatura'=>$registro['asignatura'],
                                                                      'area_tipo_id'=>$registro['area_tipo_id'],
                                                                      'area'=>$registro['area'],
                                                                      'calendarioId'=>1,
                                                                      'calendario'=>'Bimestral',
                                                                      'n1'=>$registro['b1'],
                                                                      'n2'=>$registro['b2'],
                                                                      'n3'=>$registro['b3'],
                                                                      'n4'=>$registro['b4'],
                                                                      'n5'=>$registro['b5'],
                                                                      'n6'=>null
                                                                    );
          } else {
            $listaHistorial[$registro['grado_tipo_id']][$i] = array(
                                                                      'codigo_rude'=>$registro['codigo_rude'],
                                                                      'paterno'=>$registro['paterno'],
                                                                      'materno'=>$registro['materno'],
                                                                      'nombre'=>$registro['nombre'],
                                                                      'institucioneducativa_id'=>$registro['institucioneducativa_id'],
                                                                      'institucioneducativa'=>$registro['institucioneducativa'],
                                                                      'turno'=>$registro['turno'],
                                                                      'grado_tipo_id'=>$registro['grado_tipo_id'],
                                                                      'grado'=>$registro['grado'],
                                                                      'paralelo'=>$registro['paralelo'],
                                                                      'gestion_tipo_id'=>$registro['gestion_tipo_id'],
                                                                      'estadomatricula_tipo_id'=>$registro['estadomatricula_tipo_id'],
                                                                      'asignatura_tipo_id'=>$registro['asignatura_tipo_id'],
                                                                      'asignatura'=>$registro['asignatura'],
                                                                      'area_tipo_id'=>$registro['area_tipo_id'],
                                                                      'area'=>$registro['area'],
                                                                      'calendarioId'=>2,
                                                                      'calendario'=>'Trimestral',
                                                                      'n1'=>$registro['t1'],
                                                                      'n2'=>$registro['t2'],
                                                                      'n3'=>$registro['t3'],
                                                                      'n4'=>$registro['t4'],
                                                                      'n5'=>$registro['t5'],
                                                                      'n6'=>$registro['t6']
                                                                    );
          }

          $i++;
          // $listaHistorial[$i] = $entidadEspecialidadTipo['grado_tipo_id'];
        }
        //dump($listaHistorial);
        //die;
        return $listaHistorial;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // funcion que genera el historial academico de un participante de educacion alternativa en funcion a su inscripcion
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getDipHumAlternativaEstudianteInscripcionHistorial($estudianteId) {

        $entityCargaHorariaInscripcion = $this->getDipHumAlternativaHistorial($estudianteId);

        $gestionId = 0;
        $periodoId = 0;
        $i = 0;
        $j = 0;
        foreach ($entityCargaHorariaInscripcion as $registro)
        {
          if ($gestionId != $registro['gestion_tipo_id'] or $periodoId != $registro['periodo_tipo_id']) {
            $gestionId = $registro['gestion_tipo_id'];
            $periodoId = $registro['periodo_tipo_id'];
            $i = 0;
          }
          if(($registro['gestion_tipo_id'] > 2015)){
            $listaHistorial[$registro['gestion_tipo_id']][$registro['periodo_tipo_id']][$i] = array(
                                                                      'codigo_rude'=>$registro['codigo_rude'],
                                                                      'paterno'=>$registro['paterno'],
                                                                      'materno'=>$registro['materno'],
                                                                      'nombre'=>$registro['nombre'],
                                                                      'institucioneducativa_id'=>$registro['institucioneducativa_id'],
                                                                      'institucioneducativa'=>$registro['institucioneducativa'],
                                                                      'turno'=>$registro['turno'],
                                                                      'grado_tipo_id'=>$registro['grado_tipo_id'],
                                                                      'grado'=>$registro['grado'],
                                                                      'paralelo'=>$registro['paralelo'],
                                                                      'periodo_tipo_id'=>$registro['periodo_tipo_id'],
                                                                      'periodo'=>$registro['periodo'],
                                                                      'gestion_tipo_id'=>$registro['gestion_tipo_id'],
                                                                      'estadomatricula_tipo_id'=>$registro['estadomatricula_tipo_id'],
                                                                      'asignatura_tipo_id'=>$registro['asignatura_tipo_id'],
                                                                      'asignatura'=>$registro['asignatura'],
                                                                      'area_tipo_id'=>$registro['area_tipo_id'],
                                                                      'area'=>$registro['area'],
                                                                      'calendarioId'=>1,
                                                                      'calendario'=>'Unica',
                                                                      'n1'=>null,
                                                                      'n2'=>null,
                                                                      'n3'=>null,
                                                                      'n4'=>$registro['n4']
                                                                    );
          } else {
            $listaHistorial[$registro['gestion_tipo_id']][$registro['periodo_tipo_id']][$i] = array(
                                                                      'codigo_rude'=>$registro['codigo_rude'],
                                                                      'paterno'=>$registro['paterno'],
                                                                      'materno'=>$registro['materno'],
                                                                      'nombre'=>$registro['nombre'],
                                                                      'institucioneducativa_id'=>$registro['institucioneducativa_id'],
                                                                      'institucioneducativa'=>$registro['institucioneducativa'],
                                                                      'turno'=>$registro['turno'],
                                                                      'grado_tipo_id'=>$registro['grado_tipo_id'],
                                                                      'grado'=>$registro['grado'],
                                                                      'paralelo'=>$registro['paralelo'],
                                                                      'periodo_tipo_id'=>$registro['periodo_tipo_id'],
                                                                      'periodo'=>$registro['periodo'],
                                                                      'gestion_tipo_id'=>$registro['gestion_tipo_id'],
                                                                      'estadomatricula_tipo_id'=>$registro['estadomatricula_tipo_id'],
                                                                      'asignatura_tipo_id'=>$registro['asignatura_tipo_id'],
                                                                      'asignatura'=>$registro['asignatura'],
                                                                      'area_tipo_id'=>$registro['area_tipo_id'],
                                                                      'area'=>$registro['area'],
                                                                      'calendarioId'=>2,
                                                                      'calendario'=>'Criterio',
                                                                      'n1'=>$registro['n1'],
                                                                      'n2'=>$registro['n2'],
                                                                      'n3'=>$registro['n3'],
                                                                      'n4'=>$registro['n4']
                                                                    );
          }

          $i++;
          // $listaHistorial[$i] = $entidadEspecialidadTipo['grado_tipo_id'];
        }
        //dump($listaHistorial);
        //die;

        return $listaHistorial;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que genera el formulario para regularizar diplomas tecnicos de gestiones pasadas
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipTecRecepcionAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        $activeMenu = $defaultTramiteController->setActiveMenu();

        $rolPermitido = array(16);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        }

        $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('tramite_tecnico_humanistico_registro'))
                    ->add('rude', 'text', array('label' => 'Rude', 'invalid_message' => 'campo obligatorio', 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 20, 'required' => true, 'class' => 'form-control')))
                    ->add('gestion', 'entity', array('data' => '', 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                        'query_builder' => function(EntityRepository $er) {
                            return $er->createQueryBuilder('gt')
                                    ->where('gt.id > 2008')
                                    ->orderBy('gt.id', 'DESC');
                        },
                    ))
                    ->add('diploma','choice',
                      array('label' => 'Modalidad',
                            'choices' => array( '3' => 'Agropecuario'
                                                ,'4' => 'Industrial'
                                                ,'5' => 'Comercial'
                                                ),
                            'data' => '', 'attr' => array('class' => 'form-control')))
                    ->add('serie', 'text', array('label' => 'Nro. Serie', 'invalid_message' => 'campo obligatorio', 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 8, 'required' => true, 'class' => 'form-control')))
                    ->add('submit', 'submit', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-blue')))
                    ->getForm()->createView();

        return $this->render($this->session->get('pathSystem') . ':Tramite:dipTecIndex.html.twig', array(
            'form' => $form
            , 'titulo' => 'Registro'
            , 'subtitulo' => 'Diploma Técnico'
        ));
    }



    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que registra la regularización de diplomas tecnicos de gestiones pasadas
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipTecRegistroAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rolPermitido = 16;

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        if ($form) {
            $rude = $form['rude'];
            $ges = $form['gestion'];
            $tipoDiploma = $form['diploma'];
            $serie = $form['serie'];

            $em->getConnection()->beginTransaction();
            try {
                /*
                 * Extrae los datos de estudiante en funcion a su codigo rude y gestion de inscripcion
                 */
                $query = $em->getConnection()->prepare("
                    select e.id as estudianteId, ei.id as estudianteInscripcionId, iec.gestion_tipo_id from estudiante as e
                    inner join estudiante_inscripcion as ei on ei.estudiante_id = e.id
                    inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                    where e.codigo_rude = '".$rude."' and iec.gestion_tipo_id = ".$ges."
                    and case when iec.gestion_tipo_id >=2011 then (iec.nivel_tipo_id=13 and iec.grado_tipo_id=6) or (iec.nivel_tipo_id=15 and iec.grado_tipo_id=3) when iec.gestion_tipo_id <= 2010 then (iec.nivel_tipo_id=3 and iec.grado_tipo_id=4) or (iec.nivel_tipo_id=5 and iec.grado_tipo_id=2) else iec.nivel_tipo_id=13 and iec.grado_tipo_id=6 end
                ");
                $query->execute();
                $entityEstudianteInscripcion = $query->fetchAll();

                if (count($entityEstudianteInscripcion) > 0){
                    $estudianteInscripcionId = $entityEstudianteInscripcion[0]["estudianteinscripcionid"];
                    /*
                     * Verifica si ya cuenta con un diploma de nivel tecnico
                     */
                    $query = $em->getConnection()->prepare("
                        select * from documento as d
                        inner join tramite as t on t.id = d.tramite_id
                        inner join estudiante_inscripcion as ei on ei.estudiante_id = t.estudiante_inscripcion_id
                        inner join estudiante as e on e.id = ei.estudiante_id
                        where e.codigo_rude = '".$rude."' and t.tramite_tipo = 2 and d.documento_tipo_id = ".$tipoDiploma." and d.documento_estado_id = 1
                    ");
                    $query->execute();
                    $entityEstudianteDiplomaTecnico = $query->fetchAll();
                    if (count($entityEstudianteDiplomaTecnico) == 0){
                        $entityTramite = new Tramite();
                        /*
                         * Verifica si el numero de serie esta disponible y asignado a su departamento
                         */

                        $documentoController = new documentoController();
                        $documentoController->setContainer($this->container);

                        if ($tipoDiploma == 3 or $tipoDiploma == 4 or $tipoDiploma == 5){
                            $tipoDocumento = 1;
                        } else {
                            $tipoDocumento = $tipoDiploma;
                        }

                        $msgContenidoDocumento = $documentoController->getDocumentoValidación($serie, '', $fechaActual, $id_usuario, $rolPermitido, $tipoDocumento);
                        if($msgContenidoDocumento != ""){
                            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msgContenidoDocumento));
                            return $this->redirectToRoute('tramite_tecnico_humanistico_recepcion');
                        } 
                        
                        //$numeroSerie = $entitySerie[0]["numero_serie"];
                        //$tipoSerie = $entitySerie[0]["tipo_serie"];
                        //$gestionSerie = $entitySerie[0]["gestion_serie"];


                        $tramiteProcesoController = new tramiteProcesoController();
                        $tramiteProcesoController->setContainer($this->container);

                        /*REGISTRAR EL DOCUMENTO*/  
                        $tramiteId = $this->setTramiteEstudiante($estudianteInscripcionId, $ges, 2, 2, $em);

                        // $error = $this->procesaTramite($tramiteId, $id_usuario, 'Adelante','');
                        $tramiteDetalleId = $tramiteProcesoController->setProcesaTramiteInicio($tramiteId, $id_usuario, 'REGISTRO DEL TRÁMITE', $em);

                        $idDocumento = $documentoController->setDocumento($tramiteId, $id_usuario, $tipoDiploma, $serie, '', $fechaActual); 

                        $em->getConnection()->commit();
                        $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Diploma Técnico Registrado'));

                        return $this->redirectToRoute('tramite_tecnico_humanistico_recepcion');

                    } else {
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, ya cuenta con un diploma técnico"'. $serie .'" no existe o no tiene tuición sobre el mismo, intente nuevamente'));
                        return $this->redirectToRoute('tramite_tecnico_humanistico_recepcion');
                    }
                } else {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, no cuenta con una inscripción válida para otorgar al estudiante un diploma"'. $serie .'" no existe o no tiene tuición sobre el mismo, intente nuevamente'));
                    return $this->redirectToRoute('tramite_tecnico_humanistico_recepcion');
                }
                $em->getConnection()->commit();
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, intente nuevamente'));
                return $this->redirectToRoute('tramite_tecnico_humanistico_recepcion');
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Dificultades al realizar el registro, intente nuevamente'));
            return $this->redirectToRoute('tramite_tecnico_humanistico_recepcion');
        }
    }

    ///****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Controlador que genera el formulario para la impresion de los diplomas técnicos regularizados de gestiones pasadas
    // PARAMETROS: request
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function dipTecImpresionAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $gestionActual = new \DateTime("Y");
        $this->session->set('save', false);
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':Tramite:dipTecImpresion.html.twig', array(
                    'form' => $this->creaFormularioImpresion('tramite_tecnico_humanistico_impresion_pdf', '', $gestionActual, '')->createView()
                    , 'titulo' => 'Impresión'
                    , 'subtitulo' => 'Diplomas Técnicos'
        ));
    }

    private function creaFormularioImpresion($routing, $value1, $value2, $value3) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('sie', 'text', array('label' => 'Código S.I.E.', 'attr' => array('value' => $value1, 'class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'placeholder' => 'Código de institución educativa', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'entity', array('label' => 'Gestión Cartón', 'data' => $value2, 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('gt')
                                ->where('gt.id < 2017')
                                ->andWhere('gt.id > 2008')
                                ->orderBy('gt.id', 'DESC');
                    },
                ))
                ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                ->getForm();
        return $form;
    }

    public function dipTecImpresionPdfAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        $em = $this->getDoctrine()->getManager();
        /*
         * Recupera datos del formulario
         */
        $form = $request->get('form');
        if ($form) {
            $ue = $form['sie'];
            $gestion = $form['gestion'];
        } else {
            $ue = $request->get('sie');
            $gestion = $request->get('gestion');
        }

        /*
         * Halla el departamento de la Unidad Educativa
         */
        $query = $em->getConnection()->prepare("
                select dt.departamento_tipo_id as codigo from institucioneducativa as ie
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join distrito_tipo as dt on dt.id = jg.distrito_tipo_id
                where ie.id = :sie::INT
                ");
        $query->bindValue(':sie', $ue);
        $query->execute();
        $entityDepto = $query->fetchAll();
        if (count($entityDepto)>0){
            $depto = $entityDepto[0]['codigo'];
        } else {
            $depto = 1;
        }


        $arch = $ue.'_'.$gestion.'_DIPLOMA_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

        switch ($gestion) {
            case 2009:
                if ($depto == 1) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 2) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_lp_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 3) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_cba_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 4) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_or_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 5) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pt_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 6) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_tj_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 7) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_scz_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 8) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_bn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 9) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }else {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }
                break;
            case 2010:
                if ($depto == 1) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 2) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_lp_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 3) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_cba_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 4) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_or_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 5) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pt_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 6) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_tj_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 7) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_scz_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 8) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_bn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 9) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }else {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }
                break;
            case 2011:
                if ($depto == 1) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 2) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_lp_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 3) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_cba_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 4) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_or_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 5) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pt_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 6) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_tj_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 7) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_scz_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 8) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_bn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 9) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }else {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }
                break;
            case 2012 :
                if ($depto == 1) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2012_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 2) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2012_lp_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 3) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2012_cba_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 4) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2012_or_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 5) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2012_pt_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 6) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2012_tj_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 7) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2012_scz_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 8) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2012_bn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 9) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2012_pn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }else {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2012_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }
                break;
            case 2013 :
                if ($depto == 1) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 2) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2013_lp_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 3) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2013_cba_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 4) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_or_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 5) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pt_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 6) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_tj_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 7) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2013_scz_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 8) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_bn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 9) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }else {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }
                break;
            default :
                if ($depto == 1) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 2) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2015_lp_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 3) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2015_cba_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 4) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_or_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 5) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pt_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 6) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_tj_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 7) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2015_scz_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 8) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_bn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                } elseif ($depto == 9) {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_pn_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }else {
                    $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'gen_dpl_diplomaTecnicoEstudiante_unidadeducativa_2011_ch_v2.rptdesign&__format=pdf&unidadeducativa='.$ue.'&gestion_id='.$gestion.'&tipo=2'));
                }
                break;
        }


        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
}
