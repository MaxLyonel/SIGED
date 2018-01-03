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
                ->leftJoin('SieAppWebBundle:Documento', 'd', 'WITH', 'd.tramite = t.id and d.documentoEstado = 1')
                ->leftJoin('SieAppWebBundle:DocumentoEstado', 'de', 'WITH', 'de.id = d.documentoEstado')
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
                ->leftJoin('SieAppWebBundle:Documento', 'd', 'WITH', 'd.tramite = t.id and d.documentoEstado = 1')
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
                ->leftJoin('SieAppWebBundle:Documento', 'd', 'WITH', 'd.tramite = t.id and d.documentoEstado = 1')
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
    // Controlador que busca una unidad educativa
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

        $rolPermitido = array(8,10,13);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramites_homepage'));
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
                    return $this->redirect($this->generateUrl('sie_tramites_homepage'));
                }
            }
        } */
        //else {
        //    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar información, intente nuevamente'));
        //    return $this->redirect($this->generateUrl('sie_tramites_homepage'));
        //}
        
        return $this->render($this->session->get('pathSystem') . ':Tramite:index.html.twig', array(
            'formBusqueda' => $this->creaFormBuscaInstitucionEducativa('sie_tramite_certificado_tecnico_registro_lista',0,0,0,0)->createView(),
            'titulo' => 'Registro',
            'subtitulo' => 'Trámite',
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
    // Funcion que genera un formulario par ala busqueda de unidades educativas por gestion
    // PARAMETROS: institucionEducativaId, gestionId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function creaFormBuscaInstitucionEducativa($routing, $institucionEducativaId, $gestionId, $especialidadId, $nivelId) {
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
    // Controlador que muestra el listado de participantes para el registro de su tramite
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
                    $verTuicionUnidadEducativa = $this->verTuicionUnidadEducativa($id_usuario, $sie, $usuarioRol, 'TRAMITES');

                    if ($verTuicionUnidadEducativa != ''){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $verTuicionUnidadEducativa));
                        return $this->redirect($this->generateUrl('sie_tramite_certificado_tecnico_registro_busca'));
                    }

                    $entityAutorizacionCentro = $this->getAutorizacionCentroEducativoTecnica($sie); 
                    if(count($entityAutorizacionCentro)<=0){
                        $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => 'No es posible encontrar la información del centro de educación, intente nuevamente'));
                    }

                    $entityParticipantes = $this->getEstudiantesAlternativaTecnica($sie,$gestion,$especialidad,$nivel);    

                    //$i = 0;
                    //foreach ($entityParticipantes as $participante){
                    //    $i = $i + 1;
                    //}

                    $datosBusqueda = base64_encode(serialize($form));

                    return $this->render($this->session->get('pathSystem') . ':TramiteDetalle:registroIndex.html.twig', array(
                        'formBusqueda' => $this->creaFormBuscaInstitucionEducativa('sie_tramite_certificado_tecnico_registro_lista',$sie,$gestion,$especialidad,$nivel)->createView(),
                        'titulo' => 'Registro',
                        'subtitulo' => 'Trámite',
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionCentro' => $entityAutorizacionCentro, 
                        'datosBusqueda' => $datosBusqueda,                   
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('sie_tramite_certificado_tecnico_registro_busca'));
                }  
            }  else {          
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('sie_tramite_certificado_tecnico_registro_busca'));
            }
        } else {                 
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramite_certificado_tecnico_registro_busca'));
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
                    return $this->redirectToRoute('sie_tramite_certificado_tecnico_registro_busca');
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
            return $this->redirectToRoute('sie_tramite_certificado_tecnico_registro_lista', ['form' => $formBusqueda], 307);
        } else {                 
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramite_certificado_tecnico_registro_busca'));
        }  
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Controlador que lista los trámites recepcionados por la direccion distrital en formato pdf
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
           
            $arch = $sie.'_'.$ges.'_legalizacion'.date('YmdHis').'.pdf';
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
            return $this->redirectToRoute('sie_tramite_detalle_certificado_tecnico_registro_lista', ['form' => $form], 307);
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
            select distinct on (sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre)
            ei.id as estudiante_inscripcion_id,ei.estudiante_id as estudiante_id, sat.codigo as nivel, ies.periodo_tipo_id as periodo, iec.periodo_tipo_id as per, siea.institucioneducativa_id as institucioneducativa
            , sest.id as especialidad_id, ies.gestion_tipo_id,ies.periodo_tipo_id,siea.institucioneducativa_id, sfat.codigo as nivel_id, sfat.facultad_area, sest.codigo as ciclo_id
            ,sest.especialidad,sat.codigo as grado_id,sat.acreditacion,ei.id as estudiante_inscripcion,e.codigo_rude, e.nombre, e.paterno, e.materno, to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento
            , cast(e.carnet_identidad as varchar)||(case when complemento is null then '' when complemento = '' then '' else '-'||complemento end) as carnet_identidad
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
            where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sest.id = ".$especialidadId." and sat.codigo = ".$nivelId." and sfat.codigo in (18,19,20,21,22,23,24,25) and ei.estadomatricula_tipo_id in (4) 
            order by sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, ies.periodo_tipo_id desc
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Funcion que muestra los roles del usuario
    // PARAMETROS: gestionId (gestion cuando se inicio el sistema)
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
            , smp.horas_modulo, smt.modulo, en.nota_cuantitativa, ies.gestion_tipo_id as gestion, pt.periodo as periodo
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
            where sest.id = ".$especialidadId." and sat.codigo = ".$nivelId." and en.nota_tipo_id::integer = 22
            order by smt.id
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
            select distinct on (sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre)
            ei.id as estudiante_inscripcion_id,ei.estudiante_id as estudiante_id, sat.codigo as nivel, ies.periodo_tipo_id as periodo, iec.periodo_tipo_id as per, siea.institucioneducativa_id as institucioneducativa
            , sest.id as especialidad_id, ies.gestion_tipo_id,ies.periodo_tipo_id,siea.institucioneducativa_id, sfat.codigo as nivel_id, sfat.facultad_area, sest.codigo as ciclo_id
            ,sest.especialidad,sat.codigo as grado_id,sat.acreditacion,ei.id as estudiante_inscripcion,e.codigo_rude, e.nombre, e.paterno, e.materno, to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento
            , cast(e.carnet_identidad as varchar)||(case when complemento is null then '' when complemento = '' then '' else '-'||complemento end) as carnet_identidad
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
            where e.id = ".$estudianteId." and sest.id = ".$especialidadId." and sat.codigo = ".$nivelId." and t.gestion_id = ".$gestionId." and sfat.codigo in (18,19,20,21,22,23,24,25) and ei.estadomatricula_tipo_id in (4) 
            order by sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, ies.periodo_tipo_id desc
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); 
        return $objEntidad;
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO: 
    // Funcion que lista los tramites en curso de un estudiante segun su especialidad y nivel
    // PARAMETROS: estudianteInscripcionId, especialidadId, nivelId
    // AUTOR: RCANAVIRI
    //****************************************************************************************************
    public function getCertTecTramiteEspecialidadNivelEstudiante($estudianteId, $especialidadId, $nivelId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
            select distinct on (sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre)
            ei.id as estudiante_inscripcion_id,ei.estudiante_id as estudiante_id, sat.codigo as nivel, ies.periodo_tipo_id as periodo, iec.periodo_tipo_id as per, siea.institucioneducativa_id as institucioneducativa
            , sest.id as especialidad_id, ies.gestion_tipo_id,ies.periodo_tipo_id,siea.institucioneducativa_id, sfat.codigo as nivel_id, sfat.facultad_area, sest.codigo as ciclo_id
            ,sest.especialidad,sat.codigo as grado_id,sat.acreditacion,ei.id as estudiante_inscripcion,e.codigo_rude, e.nombre, e.paterno, e.materno, to_char(e.fecha_nacimiento,'DD/MM/YYYY') as fecha_nacimiento
            , cast(e.carnet_identidad as varchar)||(case when complemento is null then '' when complemento = '' then '' else '-'||complemento end) as carnet_identidad
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
            where e.id = ".$estudianteId." and sest.id = ".$especialidadId." and sat.codigo = ".$nivelId." and sfat.codigo in (18,19,20,21,22,23,24,25) and ei.estadomatricula_tipo_id in (4) 
            order by sfat.codigo, sfat.facultad_area, sest.id, sest.especialidad, sat.codigo, sat.acreditacion, e.paterno, e.materno, e.nombre, ies.periodo_tipo_id desc
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
    // Funcion que valida el proceso de registro de un trámite segun el participante, especialidad y nivel
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

        $documentoController = new documentoController();
        $documentoController->setContainer($this->container);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramites_homepage'));
        }
        
        return $this->render($this->session->get('pathSystem') . ':Tramite:reactivaIndex.html.twig', array(
            'formBusqueda' => $documentoController->creaFormAnulaDocumentoSerie('sie_tramite_reactiva_lista','','')->createView(),
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
                        return $this->redirect($this->generateUrl('sie_tramite_reactiva_busca'));
                    }

                    $entityDocumento = $documentoController->getDocumentoSerieEstado($serie,1);

                    if(count($entityDocumento)<=0){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'El documento con número de serie '));
                        return $this->redirect($this->generateUrl('sie_tramite_reactiva_busca'));
                    }

                    $tramiteProcesoController = new tramiteProcesoController();
                    $tramiteProcesoController->setContainer($this->container);

                    $valUltimoProcesoFlujoTramite = $tramiteProcesoController->valUltimoProcesoFlujoTramite($entityDocumento['tramite']);

                    $entityTramiteDetalle = $tramiteProcesoController->getTramiteDetalle($entityDocumento['tramite']);


                    return $this->render($this->session->get('pathSystem') . ':Seguimiento:tramiteDetalle.html.twig', array(
                        'formBusqueda' => $documentoController->creaFormAnulaDocumentoSerie('sie_tramite_reactiva_lista','','')->createView(),
                        'titulo' => 'Seguimiento',
                        'subtitulo' => 'Trámite',
                        'msgReactivaTramite' => $valUltimoProcesoFlujoTramite,
                        'listaDocumento' => $entityDocumento,
                        'listaTramiteDetalle' => $entityTramiteDetalle,
                        'arrayForm' => array('serie'=>$serie, 'obs'=>$obs),
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('sie_tramite_reactiva_busca'));
                }  
            }  else {          
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('sie_tramite_reactiva_busca'));
            }
        } else {                 
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramite_reactiva_busca'));
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
                        return $this->redirect($this->generateUrl('sie_tramite_reactiva_busca'));
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

                    $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'Trámite '.$tramiteId.' con documento nro. '.$serie.' reactivado, el trámite se encuentra en la bandeja de impresión'));

                    $formBusqueda = array('serie'=>$serie,'obs'=>$obs);
                    //return $this->redirectToRoute('sie_tramite_reactiva_lista', ['form' => $formBusqueda], 307);
                    return $this->redirect($this->generateUrl('sie_tramite_reactiva_busca'));

                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    return $this->redirect($this->generateUrl('sie_tramite_reactiva_busca'));
                }  
            } else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                return $this->redirect($this->generateUrl('sie_tramite_reactiva_busca'));
            }
        } else {                 
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            return $this->redirect($this->generateUrl('sie_tramite_reactiva_busca'));
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

}


