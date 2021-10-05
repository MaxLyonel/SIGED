<?php

namespace Sie\ProcesosBundle\Controller;

use Sie\AppWebBundle\Entity\WfSolicitudTramite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;
use Sie\AppWebBundle\Entity\InstitucioneducativaEspecialidadTecnicoHumanistico;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnicoTipo;
use Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo;
use Sie\AppWebBundle\Entity\GestionTipo;
use Sie\AppWebBundle\Entity\TramiteDetalle;
use Sie\AppWebBundle\Entity\Tramite;
use Sie\AppWebBundle\Entity\SuperiorEspecialidadTipo;
use Sie\AppWebBundle\Entity\SuperiorAcreditacionTipo;
use Sie\AppWebBundle\Entity\TramiteTipo;
use Sie\AppWebBundle\Entity\CertificadoPermanente;







class TramiteCertificacionesPermanenteController extends Controller {
    public $session;
    public $idInstitucion;
    protected $em; 
    
    public function __construct() {
        $this->session = new Session();      
    }
    

    /**
     * Definicion de funciones
     */

    //FORMULARIO PARA EL DIRECTOR DEL CENTRO
    public function indexAction (Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();
        $route = $request->get('_route');       

        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        $sie= $this->session->get('ie_id');
        $gestion=$this->session->get('ie_gestion');

       /*  //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

        //$activeMenu = $defaultTramiteController->setActiveMenu($route);

        // $rolPermitido = array(8,13);
        $rolPermitido = array(9,8);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        } */
        //$routing, $institucionEducativaId, $gestionId, $especialidadId, $nivelId
        $gestion = $gestionActual->format('Y');

        //Se obtiene el idFlujo en caso de ser nuevo envio si es devolucion devuelve idTramite 
        $flujotipo='';
        $idTramite='';
        if ($request->get('tipo') == 'idflujo') {
            $flujotipo = $request->get('id');
            //LA FUNCION creaFormBuscaCentroEducacionPermanente CREA EL FORMULARIO DE BUSQUEDAD DE ESTUDIANTES PARA UN CENTRO (RUTA,SIE, GESTION,MENCION,NIVEL,FLUJOtIPO, IDTRAMITE)
            return $this->render('SieProcesosBundle:TramiteCertificacionCursosLargos:index.html.twig', array(
                'formBusqueda' => $this->creaFormBuscaCentroEducacionPermanente('tramite_certificado_permanente_registro_lista',$sie,0,0,0,$flujotipo,$idTramite)->createView(),
                'titulo' => 'Registro',
                'subtitulo' => 'Trámite - Certificación Técnica Alternativa'           
            ));
        }else{//SE CONSIDERA COMO TRAMITE DEVUELTO
            $idTramite = $request->get('id');
            $em = $this->getDoctrine()->getManager();
            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
            $flujoTipo = $tramite->getflujoTipo()->getId();
            
            //OBTENEMOS LOS DATOS DEL TRAMITE RECEPCIONADO        
            $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                    ->select('wfd')
                    ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                    ->where('td.tramite='.$tramite->getId())
                    ->andWhere("wfd.esValido=true")
                    ->orderBy("td.flujoProceso")
                    ->getQuery()
                    ->getResult();
            foreach($wfdatos as $d){
                if ($d->getTramiteDetalle()->getFlujoProceso()->getOrden()==1){
                    $datos = json_decode($d->getDatos(),true);                
                    $sie = $datos['sie'];
                    $idInscripcion=$datos['idInscripcion'];
                    $idRue = $datos['rude'];
                    $idEspecialidad = $datos['especialidad_id'];
                    $idNivel = $datos['nivel_id'];
                }
            }
            //OBTENEMOS LA OBSERVACION           
            $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                    ->select('wfd')
                    ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                    ->where('td.tramite='.$tramite->getId())
                    ->andWhere("wfd.esValido=true")
                    ->orderBy("td.flujoProceso")
                    ->getQuery()
                    ->getResult();
            $tareasDatos = array();
            foreach($wfdatos as $wfd)
            {                
                $tareasDatos[] = array('tramiteDetalle'=>$wfd->getTramiteDetalle());
            }       
            //dump($tareasDatos[1]['tramiteDetalle']->getObs());die;
            $datosParticipante = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->createQueryBuilder('ei')
                                ->select('e,ei')
                                ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'with', 'ei.estudiante = e.id')
                                ->where('ei.id='.$idInscripcion)
                                ->getQuery()
                                ->getResult();
            $sieDatos = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);                               
            $mencion = $em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->find($idEspecialidad);
            $nivel = $em->getRepository('SieAppWebBundle:SuperiorAcreditacionTipo')->find($idNivel);            
            return $this->render('SieProcesosBundle:TramiteCertificacionCursosLargos:formularioCentroDev.html.twig', array(
                'datosParticipante'=>$datosParticipante[1],
                'datosInscripcionParticipante'=>$datosParticipante[0],
                'mencion'=>$mencion,
                'idMencion'=>$idEspecialidad,            
                'nivel'=>$nivel,
                'idNivel'=>$idNivel,
                'idtramite'=>$idTramite,
                'sie'=>$sie,
                'sieDatos'=>$sieDatos,
                'flujoTipo'=>$flujoTipo,
                //'observacion'=> $datos[1]->getObs()
                'observacion'=> $tareasDatos[1]['tramiteDetalle']->getObs()
            ));
        }
        
               
    }
    /**
     * Funcion para crear el formulario de busqueda de estudiantes a certificar
     * Gestion  Mencion y Nivel
     */
    public function getMenciondCentroEducativoPermanente($institucionEducativaId, $gestionId){
        date_default_timezone_set('America/La_Paz');
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                    SELECT DISTINCT seti.id,seti.especialidad from superior_institucioneducativa_acreditacion sia
                    INNER JOIN institucioneducativa ie  ON sia.institucioneducativa_id = ie.id and ie.estadoinstitucion_tipo_id = 10
                    INNER JOIN superior_acreditacion_especialidad sae ON sia.acreditacion_especialidad_id = sae.id
                    INNER JOIN superior_especialidad_tipo seti ON sae.superior_especialidad_tipo_id= seti.id
                    where sia.gestion_tipo_id = ".$gestionId."::double precision and sia.institucioneducativa_id = ".$institucionEducativaId."
                    order by seti.especialidad");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll(); //dump($objEntidad);die;
        return $objEntidad; 
    }
    /**
     * Funcion que permite obtener los niveles de cada especialidad
     * 
     */
    public function getNivelCentroEducativoTecnica($institucionEducativaId, $gestionId, $especialidadId) {
        date_default_timezone_set('America/La_Paz');
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("
                select distinct sat.id as nivel_id, sat.acreditacion as nivel
                from superior_facultad_area_tipo as sfat
                inner join superior_especialidad_tipo as sest on sfat.id = sest.superior_facultad_area_tipo_id
                inner join superior_acreditacion_especialidad as sae on sest.id = sae.superior_especialidad_tipo_id
                inner join superior_acreditacion_tipo as sat on sae.superior_acreditacion_tipo_id=sat.id
                inner join superior_institucioneducativa_acreditacion as siea on siea.acreditacion_especialidad_id = sae.id
                inner join institucioneducativa_sucursal as ies on siea.institucioneducativa_sucursal_id = ies.id
                where ies.gestion_tipo_id = ".$gestionId."::double precision and siea.institucioneducativa_id = ".$institucionEducativaId." and sest.id = ".$especialidadId." 
                order by sat.id
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }
    /**
     * Funcionq que pemite listar las especialidades o menciones de un centro
     */
    public function certTecBuscaListaEspecialidadAction(Request $request) {
        try {
            $response = new JsonResponse();          
            return $response->setData(array(
                'especialidades' => $this->getMenciondCentroEducativoPermanente($_POST['sie'],$_POST['gestion']),
            ));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
            return $response->setData(array());
        }
    }

    public function creaFormBuscaCentroEducacionPermanente($routing, $institucionEducativaId, $gestionId, $especialidadId, $nivelId,$flujotipo,$idTramite) {        
        $em = $this->getDoctrine()->getManager();
        $entidadGestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $gestionId));
        $entidadMencionTipo = $this->getMenciondCentroEducativoPermanente($institucionEducativaId, $gestionId);   
        $entidadMencion = array();
        foreach ($entidadMencionTipo as $esp)
        {
           $entidadMencion[$esp['id']] = $esp['especialidad'];
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
                ->add('sie', 'number', array('label' => 'SIE', 'attr' => array('value' => $institucionEducativaId, 'class' => 'form-control','disabled' => 'disabled','placeholder' => 'Código de institución educativa', 'onInput' => 'valSie()', ' onchange' => 'valSieFocusOut()', 'pattern' => '[0-9]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))                
                ->add('gestion', 'entity', array('data' => $entidadGestionTipo, 'empty_value' => 'Seleccione Gestión', 'attr' => array('class' => 'form-control', 'onchange' => 'listar_especialidad(this.value)'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('gt')
                                ->where('gt.id > 2020')
                                ->orderBy('gt.id', 'DESC');
                    },
                ))
                ->add('especialidad', 'choice', array('label' => 'Especialidad', 'empty_value' => 'Seleccione Mención', 'choices' => $entidadMencion, 'data' => $especialidadId, 'attr' => array('class' => 'form-control', 'disabled' => 'disabled', 'onchange' => 'listar_nivel(this.value)', 'required' => true)))
                ->add('nivel', 'choice', array('label' => 'Nivel', 'empty_value' => 'Seleccione Nivel', 'choices' => $entidadNivel, 'data' => $nivelId, 'attr' => array('class' => 'form-control', 'required' => true, 'disabled' => 'disabled', 'onchange' => 'habilitarSubmit()')))
                ->add('flujotipo', 'hidden', array('data' => $flujotipo))
                ->add('idTramite', 'hidden', array('data' => $idTramite))
                ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue', 'disabled' => 'disabled')))
                ->getForm();
        return $form;
    }
    
    /**
     * Funcion que permite lista los niveles de un centro de educacion alternativa segun la gestión
     * 
     */
   
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
    /**
     * Funcion que permite lista estudiantes segun sie, gestion, mencion y nivel
     * 
     */
    public function certTecRegistroListaAction(Request $request) {//dump($request);die;
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
                $sie = $this->session->get('ie_id');
                $gestion = $form['gestion'];
                $especialidad = $form['especialidad'];
                $nivel = $form['nivel'];                
                try {
                    $entityParticipantes = $this->getEstudiantesPermanente($sie,$gestion,$especialidad,$nivel);  //dump($entityParticipantes);die;  
                    $datosBusqueda = base64_encode(serialize($form));
                    return $this->render('SieProcesosBundle:TramiteCertificacionCursosLargos:listaParticipantes.html.twig', array(
                        'formBusqueda' => $this->creaFormBuscaCentroEducacionPermanente('tramite_certificado_permanente_registro_lista',$sie,$gestion,$especialidad,$nivel,$form['flujotipo'],$form['idTramite'])->createView(),
                        'titulo' => 'Registro',
                        'subtitulo' => 'Trámite',
                        'listaParticipante' => $entityParticipantes,
                        //'infoAutorizacionCentro' => $entityAutorizacionCentro,
                        'infoAutorizacionCentro' => $sie,
                        'datosBusqueda' => $datosBusqueda,
                        'flujotipo'=>$form['flujotipo'],
                        'idTramite'=>$form['idTramite'],
                        'especialidad'=>$especialidad,
                        'nivel'=>$nivel
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
    private function getEstudiantesPermanente($institucionEducativaId, $gestionId, $especialidadId, $nivelId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("select ei.id,e.carnet_identidad,e.complemento,e.codigo_rude,e.nombre,e.paterno,e.materno,e.fecha_nacimiento,e.segip_id,seti.id as especialidad_id,sat.id as nivel,cp.estado
            from institucioneducativa_curso ic
            INNER JOIN permanente_institucioneducativa_cursocorto ico on ic.id = ico.institucioneducativa_curso_id
            INNER JOIN estudiante_inscripcion ei on ei.institucioneducativa_curso_id = ic.id
            INNER JOIN estudiante e on e.id = ei.estudiante_id
            INNER JOIN superior_institucioneducativa_periodo sip on sip.id = ic.superior_institucioneducativa_periodo_id
            INNER JOIN superior_institucioneducativa_acreditacion sia ON sia.id = sip.superior_institucioneducativa_acreditacion_id
            INNER JOIN superior_acreditacion_especialidad sae ON sae.id = sia.acreditacion_especialidad_id
            INNER JOIN superior_especialidad_tipo seti ON seti.id = sae.superior_especialidad_tipo_id
            INNER JOIN superior_acreditacion_tipo sat ON sat.id=sae.superior_acreditacion_tipo_id
            LEFT JOIN  certificado_permanente cp ON cp.estudiante_inscripcion_id = ei.id
            WHERE ic.institucioneducativa_id = ".$institucionEducativaId." and ic.gestion_tipo_id = ".$gestionId."::double precision and nivel_tipo_id = 231 and ei.estadomatricula_tipo_id = 76 and seti.id=".$especialidadId." and sat.id = ".$nivelId."
            ORDER BY superior_institucioneducativa_periodo_id
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }
    /**
     * Funcion que permite obtener el historial de un estudiante
     */
    public function certPerRegistroInscripcionHistorialAction(Request $request) { 
       
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

         //calculo de carga horaria por estudiante
         $total_horas=$this->getCargaHoraria(base64_decode($estudianteId));         
         return $this->render('SieProcesosBundle:TramiteCertificacionCursosLargos:notasParticipante.html.twig', array(
             'titulo' => 'Registro',
             'subtitulo' => 'Trámite',
             'listaModuloCargaHoraria' => $entityCargaHorariaInscripcion,
             'total_horas' =>$total_horas
             ));
     }
    public function getCertTecCargaHorariaInscripcion($participanteId, $especialidadId, $nivelId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("select ei.id,e.codigo_rude, e.carnet_identidad,e.complemento,e.nombre,e.paterno,e.materno,smt.modulo,eno.nota_cuantitativa,
        (e.nombre||' '||e.paterno||' '||e.materno)as participante,smp.horas_modulo
        from estudiante e
        INNER JOIN estudiante_inscripcion ei ON e.id = ei.estudiante_id
        INNER JOIN estudiante_asignatura eas on ei.id= eas.estudiante_inscripcion_id 
        INNER JOIN estudiante_nota eno ON eas.id=eno.estudiante_asignatura_id
        INNER JOIN institucioneducativa_curso_oferta ico on eas.institucioneducativa_curso_oferta_id = ico.id
        INNER JOIN superior_modulo_periodo smp ON ico.superior_modulo_periodo_id = smp.id
        INNER JOIN superior_modulo_tipo smt ON smp.superior_modulo_tipo_id=smt.id
            where ei.id = ".$participanteId." --and sat.codigo = ".$nivelId."            
        ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();        
        return $objEntidad;
    }
    private function getCargaHoraria($participanteId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("select  ei.id,sum(smp.horas_modulo)as total_horas
        from estudiante e
        INNER JOIN estudiante_inscripcion ei ON e.id = ei.estudiante_id
        INNER JOIN estudiante_asignatura eas on ei.id= eas.estudiante_inscripcion_id 
        INNER JOIN estudiante_nota eno ON eas.id=eno.estudiante_asignatura_id
        INNER JOIN institucioneducativa_curso_oferta ico on eas.institucioneducativa_curso_oferta_id = ico.id
        INNER JOIN superior_modulo_periodo smp ON ico.superior_modulo_periodo_id = smp.id
        INNER JOIN superior_modulo_tipo smt ON smp.superior_modulo_tipo_id=smt.id
        WHERE ei.id = ".$participanteId."
        GROUP BY ei.id");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }
    /**
     * FUNCION QUE GUARDA EL TRAMITE SOLICITADO POR EL CENTRO
     */
    
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
        //RECURPERAMOS VARIABLES DEL FORMULARIO 
        $id_rol = $this->session->get('roluser');
        $idTramite =  $request->get('_idTramite');
        $flujotipo = (Int)$request->get('_flujotipo');
        $especialidad =  $request->get('_especialidad');
        $nivel =  $request->get('_nivel');
        $institucion_id=(Int)$request->get('_idsie');
        $id_tipoTramite =58; //58	Certificacion permanente	PER -- MODIFICAR SEGUN LA BD
        //Se obtiene el id de distrito de la Ue
        $em = $this->getDoctrine()->getManager();
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucion_id);
        $id_distrito = $institucioneducativa->getleJuridicciongeografica()->getLugarTipoIdDistrito();
        //Se obtiene la primera tarea del flujo de certificaciones
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $flujotipo , 'orden' => 1));
        $tarea = $flujoproceso->getId();        
        $tabla = 'institucioneducativa';
        //VERIFICAMOS SI ES UN TRAMITE NUEVO O TRAMITE DEVUELTO
        if ($idTramite=== '' || $idTramite== null){
            $idTramite = '';
             /**Solo  para verificar como llegan los datos */
                /*$participantes = $request->get('participantes');
                foreach ($participantes as $participante) {
                    $estudianteInscripcionId = (Int) base64_decode($participante);
                    $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($estudianteInscripcionId);
                    $rude = $estudianteInscripcion->getEstudiante()->getCodigoRude();   
                    dump($rude);
                }die;*/
                /**fin*/ 
                
                /*
                PARA ELIMINAR UN TRAMITE NUEVO INGRESANDO EL ID DE TRAMITE
                $mensaje = $this->get('wftramite')->eliminarTramiteNuevo(2255455); 
                dump($mensaje); die;
                */  
            if ($request->isMethod('POST')) {
                $em = $this->getDoctrine()->getManager();
                //$em->getConnection()->beginTransaction();
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
                    //SE GENERA LOS TRAMITES PARA CADA PARTICIPANTE SELECCIONADO POR EL CENTRO
                    $nivelDetalle = $em->getRepository('SieAppWebBundle:SuperiorAcreditacionTipo')->find($nivel);               
                    $mencionDetalle = $em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->find($especialidad);
                    $lista=array();
                    foreach ($participantes as $participante) {
                        $estudianteInscripcionId = (Int) base64_decode($participante);  
                        /*Obtenemos el rude del <estudiante></estudiante>  */                    
                        $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($estudianteInscripcionId);
                        $rude = $estudianteInscripcion->getEstudiante()->getCodigoRude();
    
                        /* Se arma los datos para regitrar en los tramites */
                        $datos =  array('sie' => $institucion_id,'idInscripcion'=> $estudianteInscripcionId,'rude'=>$rude,'especialidad_id'=>$especialidad,'nivel_id'=>$nivel);
                        
                        $mensaje = $this->get('wftramite')->guardarTramiteNuevo($id_usuario, $id_rol, $flujotipo, $tarea, $tabla, $institucion_id,$nivelDetalle->getAcreditacion(), $id_tipoTramite, '', $idTramite, json_encode($datos, JSON_UNESCAPED_UNICODE), '', $id_distrito); 
                       
                        //VERIFICAMOS SI GUARDO CORRECTAMENTE
                        //$mensaje['dato']= true;
                        if($mensaje['dato'] === true){// $mensaje['idtramite']=123;
                            $gestionActual=2021;
                            //GUARDAR UN REGISTRO EN LA TABLA CERTIFICADO_PERMANENTE
                            //dump($institucion_id,$estudianteInscripcionId,$gestionActual,$especialidad,$nivel,$mensaje['idtramite']);die;
                            $mensajeCertificadoPermanente = $this->guardaCertificadopermanente($institucion_id,$estudianteInscripcionId,$gestionActual,1,$especialidad,$nivel,$mensaje['idtramite'],'','');
                           // dump($mensajeCertificadoPermanente);die;
                            if($mensajeCertificadoPermanente['dato']=== true){
                                $nombre = $estudianteInscripcion->getEstudiante()->getNombre();
                                $paterno = $estudianteInscripcion->getEstudiante()->getPaterno();
                                $materno = $estudianteInscripcion->getEstudiante()->getMaterno();
                                $ci = $estudianteInscripcion->getEstudiante()->getCarnetIdentidad();
                                $complemento = $estudianteInscripcion->getEstudiante()->getComplemento();
                                $lista[] = array(
                                    'rude' => $rude,
                                    'idInscripcion' => $estudianteInscripcionId,
                                    'nombre' =>$nombre,
                                    'paterno' =>$paterno,
                                    'materno' =>$materno,
                                    'ci' =>$ci,
                                    'complemento' =>$complemento,
                                    'institucion_id' =>$institucion_id,
                                    'idTramite'=>$mensaje['idtramite']                                
                                );
                                $respuesta = 1;
                                $msg = $mensajeCertificadoPermanente['msg'];
                            }else {
                                $respuesta = 0;
                                $msg = $mensajeCertificadoPermanente['msg'];
                            }
                            $respuesta = 1;
                            $msg = $mensaje['msg'];                            
                        }else{
                            $respuesta = 0;
                            $msg = $mensaje['msg'];
                        }
                        //return  new JsonResponse(array('estado' => $respuesta, 'msg' => $msg));
                    }
                    $institucioneducativaNombre = $institucioneducativa->getInstitucioneducativa();
                    return $this->render('SieProcesosBundle:TramiteCertificacionCursosLargos:comprobanteTramite.html.twig', array('lista'=>$lista,
                    'sie' =>$institucion_id,
                    'mencion' =>$mencionDetalle->getEspecialidad(),
                    'nivel' =>$nivelDetalle->getAcreditacion(),
                    'idMencion' => $especialidad ,
                    'idNivel' => $nivel,
                    'institucioneducativa' =>$institucioneducativaNombre
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    //$em->getConnection()->rollback();
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                }
                $gestionId = $gestionActual->format('Y');
                $gestionInscripcionId = $gestionActual->format('Y');           
                $sie= $institucion_id;
                $entityParticipantes= $participantes;
                $formBusqueda = array('sie'=>$institucion_id,'gestion'=>$gestionId,'especialidad'=>$especialidad,'nivel'=>$nivel,'flujotipo'=>$flujotipo,'idTramite'=>$idTramite);            
                return $this->redirectToRoute('tramite_certificado_permanente_registro_lista', ['form' => $formBusqueda],307);
            } else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                return $this->redirect($this->generateUrl('tramite_certificado_tecnico_registro_busca'));
            }
        }else{//SE CONSIDERA COMO TRAMITE DEVUELTO
            $estudianteInscripcionId = (Int)$request->get('idParticipante');            
            /*Obtenemos el rude del estudiante  */                    
            $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($estudianteInscripcionId);
            $rude = $estudianteInscripcion->getEstudiante()->getCodigoRude();
            $datos =  array('sie' => $institucion_id,'idInscripcion'=> $estudianteInscripcionId,'rude'=>$rude,'especialidad_id'=>$especialidad,'nivel_id'=>$nivel);
            $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario, $id_rol, $flujotipo, $tarea, $tabla, $institucion_id, '', '', $idTramite, json_encode($datos, JSON_UNESCAPED_UNICODE), '', $id_distrito);            
            $mensaje['dato']= true;
            if ($mensaje['dato'] == true){
                //OBTENEMOS LA INFORMACION DEL TRAMITE DEVUELTO
                $em = $this->getDoctrine()->getManager();
                //$tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
                //$flujoTipo = $tramite->getflujoTipo()->getId();
                
                //OBTENEMOS LOS DATOS DEL TRAMITE RECEPCIONADO        
                $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                        ->select('wfd')
                        ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                        ->where('td.tramite='.$idTramite)
                        ->andWhere("wfd.esValido=true")
                        ->orderBy("td.flujoProceso")
                        ->getQuery()
                        ->getResult();
                foreach($wfdatos as $d){
                    if ($d->getTramiteDetalle()->getFlujoProceso()->getOrden()==1){
                        $datos = json_decode($d->getDatos(),true);                
                        $sie = $datos['sie'];
                        $idInscripcion=$datos['idInscripcion'];
                        $idRue = $datos['rude'];
                        $idEspecialidad = $datos['especialidad_id'];
                        $idNivel = $datos['nivel_id'];
                    }
                }
                $sieDatos = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);                               
                $mencion = $em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->find($especialidad);
                $nivel = $em->getRepository('SieAppWebBundle:SuperiorAcreditacionTipo')->find($nivel);
                $institucioneducativaNombre = $institucioneducativa->getInstitucioneducativa();
                //obtenemos los datos del estudiante

                $datosParticipante = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->createQueryBuilder('ei')
                            ->select('e.codigoRude,e.nombre,e.paterno,e.materno,e.carnetIdentidad,e.complemento')
                            ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'with', 'ei.estudiante = e.id')
                            ->where('ei.id='.$datos['idInscripcion'])
                            ->getQuery()
                            ->getResult();
                $respuesta = 1;
                //$mensaje['msg']="ok";
                $msg = $mensaje['msg'];
                return  new JsonResponse(array('estado' => $respuesta, 'msg' => $msg));
            }else{
                $respuesta = 0;
                $msg = $mensaje['msg'];
                return  new JsonResponse(array('estado' => $respuesta, 'msg' => $msg));
            }
        }             
    }
    /**
     * FUNCION QUE REGISTRA EL TRAMITE NUEVO EN LA TABLA DE CERTIFICADO_PERMANENTE
     */
    function guardaCertificadopermanente($idInstitucion,$idEstudianteInscripcion,$idGestion,$estado,$idMencion,$idNivel,$idTramite,$nroCertificado,$obs){
        //dump($idInstitucion,$idEstudianteInscripcion,$idGestion,$estado,$idMencion,$idNivel,$idTramite,$nroCertificado,$obs);die;
        $em = $this->getDoctrine()->getManager();
        $institucion    = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($idInstitucion);
        $estudiante     = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idEstudianteInscripcion);
        $gestion        = $em->getRepository('SieAppWebBundle:GestionTipo')->find($idGestion);
        $mencion        = $em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->find($idMencion);
        $nivel          = $em->getRepository('SieAppWebBundle:SuperiorAcreditacionTipo')->find($idNivel);
        $tramite        = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
        if($nroCertificado){
            $nroCertificado=$nroCertificado;
        }else{
            $nroCertificado='';
        }
        if($obs){
            $obs=$obs;
        }else{
            $obs='';
        }
        //$em->getConnection()->beginTransaction();
        try {
            /**
            * insert certificado_permanente
            */
            $certificadoPermanente =  new CertificadoPermanente();
            $certificadoPermanente ->setInstitucioneducativa($institucion);
            $certificadoPermanente ->setEstudianteInscripcion($estudiante);
            $certificadoPermanente ->setGestionTipo($gestion);
            $certificadoPermanente ->setEstado(1);
            $certificadoPermanente ->setSuperiorEspecialidadTipo($mencion);
            $certificadoPermanente ->setSuperiorAcreditacionTipo($nivel);
            $certificadoPermanente ->setTramite($tramite);
            $certificadoPermanente ->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $certificadoPermanente ->setObs($obs);
            $em->persist($certificadoPermanente);
            $em->flush();
            $mensaje['dato'] = true;
            $mensaje['msg'] = 'Se guardó correctamente';
            return $mensaje;
        } catch (\Exception $ex) {
            //$em->getConnection()->rollback();
            $mensaje['dato'] = false;
            $mensaje['msg'] = '¡Ocurrio un error al guardar la informacion en la tabla certificado_permanente.!</br>'.$ex->getMessage();
            return $mensaje;    
        }   


    }

    /**
     * FORMULARIO PARA TRAMITES DEL DEPARTAMENTO
     */
    public function formularioDepartamentalAction(Request $request){
        /** variables que llegan de tramite recepcionado
         *  "id" => "2255486"
         * "tipo" => "idtramite"
         */
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
        $flujoTipo = $tramite->getflujoTipo()->getId();
        
        //OBTENEMOS LOS DATOS DEL TRAMITE RECEPCIONADO        
        $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                ->select('wfd')
                ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                ->where('td.tramite='.$tramite->getId())
                ->andWhere("wfd.esValido=true")
                ->orderBy("td.flujoProceso")
                ->getQuery()
                ->getResult();
        foreach($wfdatos as $d){
            if ($d->getTramiteDetalle()->getFlujoProceso()->getOrden()==1){
                $datos = json_decode($d->getDatos(),true);                
                $sie = $datos['sie'];
                $idInscripcion=$datos['idInscripcion'];
                $idRue = $datos['rude'];
                $idEspecialidad = $datos['especialidad_id'];
                $idNivel = $datos['nivel_id'];
            }
        }
        $datosParticipante = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->createQueryBuilder('ei')
                            ->select('e,ei')
                            ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'with', 'ei.estudiante = e.id')
                            ->where('ei.id='.$idInscripcion)
                            ->getQuery()
                            ->getResult();
        $sieDatos = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);                               
        $mencion = $em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->find($idEspecialidad);
        $nivel = $em->getRepository('SieAppWebBundle:SuperiorAcreditacionTipo')->find($idNivel);
        //OBTENEMOS LA OBSERVACION DEL TRAMITE          
        $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
        ->select('wfd')
        ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
        ->where('td.tramite='.$tramite->getId())
        ->andWhere("wfd.esValido=true")
        ->orderBy("td.flujoProceso")
        ->getQuery()
        ->getResult();
        $tareasDatos = array(); dump($tareasDatos);
        if($tareasDatos){
            foreach($wfdatos as $wfd)
            {                
                $tareasDatos[] = array('tramiteDetalle'=>$wfd->getTramiteDetalle());
            }
            $obs=$tareasDatos[3]['tramiteDetalle']->getObs();
        }$obs = empty($obs)?'':$obs;
       // dump($obs);die;
        return $this->render('SieProcesosBundle:TramiteCertificacionCursosLargos:formularioDepartamento.html.twig', array(
            'datosParticipante'=>$datosParticipante[1],
            'datosInscripcionParticipante'=>$datosParticipante[0],
            'mencion'=>$mencion,
            'idMencion'=>$idEspecialidad,            
            'nivel'=>$nivel,
            'idNivel'=>$idNivel,
            'idtramite'=>$id,
            'sie'=>$sie,
            'sieDatos'=>$sieDatos,
            'flujoTipo'=>$flujoTipo,
            'observacion'=>$obs
        ));  
    }
     //FUNCION PARA GUARDAR EL TRAMITE CUANDO SE ENCUENTRA EN LA DEPARTAMENTAL certPerRegistroGuardaDep
    public function certPerRegistroGuardaDepAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $sesion = $request->getSession();
        $usuario=$sesion->get('userId');
        $rol=$this->session->get('roluser');
        $flujotipo=$request->get('flujoTipo');
        $tabla= 'institucioneducativa';
        $id_tabla=$request->get('sie');
        $observacion='';
        $varevaluacion=$request->get('varevaluacion');
        $idNivel=$request->get('idNivel');
        $idTramite=$request->get('idTramite');
        $idMencion=$request->get('idMencion');
        //Armar el mjson de datos que se guardara en el tramite
        $estudianteInscripcionId = (Int)$request->get('idParticipante');            
        /*Obtenemos el rude del <estudiante></estudiante>  */                    
        $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($estudianteInscripcionId);
        $rude = $estudianteInscripcion->getEstudiante()->getCodigoRude();
        $datos =  array('sie' => $id_tabla,'idInscripcion'=> $estudianteInscripcionId,'rude'=>$rude,'especialidad_id'=>$idMencion,'nivel_id'=>$idNivel);
        
        //SE OBTIENE EL ID DE DISTRITO DEL CENTRO PERMANENTE      
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->get('sie'));
        $lugarTipoDistrito_id = $institucioneducativa->getleJuridicciongeografica()->getLugarTipoIdDistrito();
        //obtenemos los datos del tramite de orden 1
        $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
        ->select('wfd')
        ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
        ->where('td.tramite='.$idTramite)
        ->andWhere("wfd.esValido=true")
        ->orderBy("td.flujoProceso")
        ->getQuery()
        ->getResult();
        foreach($wfdatos as $d){
            if ($d->getTramiteDetalle()->getFlujoProceso()->getOrden()==1){
                $datos = $d->getDatos();
            }
        }
        // OBTENEMOS LA TAREA ACTUAL Y SIGUIENTE
        $tarea = $this->get('wftramite')->obtieneTarea($idTramite, 'idtramite'); //dump($tarea);
        $tareaActual = '';
        $tareaSiguienteSi = '';
        $tareaSiguienteNo = '';
        foreach ($tarea as $t) {
            $tareaActual = $t['tarea_actual'];
            if ($t['condicion'] == 'SI') {
                $tareaSiguienteSi = $t['tarea_siguiente'];
            }
            if ($t['condicion'] == 'NO') {
                $tareaSiguienteNo = $t['tarea_siguiente'];
            }
        }
        /* dump("*****PRIMERA TAREA ***");
        dump($tarea, $varevaluacion);
        dump($tareaActual);
        dump($tareaSiguienteSi);
        dump($tareaSiguienteNo);
        dump($this->session->get('userId')); */
        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tareaActual,$tabla,$id_tabla,$observacion,$varevaluacion,$idTramite,$datos,'',$lugarTipoDistrito_id);        
        if ($varevaluacion==='SI'){
            $tarea = $this->get('wftramite')->obtieneTarea($idTramite, 'idtramite');
            $tareaActual = '';
            $tareaSiguienteSi2 = '';
            $tareaSiguienteNo2 = '';
            foreach ($tarea as $t) {
                $tareaActual = $t['tarea_actual'];
                if ($t['condicion'] == 'SI') {
                    $tareaSiguienteSi2 = $t['tarea_siguiente'];
                }
                if ($t['condicion'] == 'NO') {
                    $tareaSiguienteNo2 = $t['tarea_siguiente'];
                }
            }
            $recibirTramite = $this->get('wftramite')->guardarTramiteRecibido(
                $this->session->get('userId'),
                $tareaSiguienteSi2,
                $idTramite
            );
            if($idNivel == 1 || $idNivel == 20){ //en caso de que sea de nivel basico o auxiliaf se finaliza el tranmite
                // OBTENEMOS LA TAREA ACTUAL Y SIGUIENTE 114
                $tarea = $this->get('wftramite')->obtieneTarea($idTramite, 'idtramite'); 
                $tareaActual = '';
                $tareaSiguienteSi = '';
                $tareaSiguienteNo = '';
                foreach ($tarea as $t) {
                    $tareaActual = $t['tarea_actual'];
                    if ($t['condicion'] == 'SI') {
                        $tareaSiguienteSi = $t['tarea_siguiente'];
                    }
                    if ($t['condicion'] == 'NO') {
                        $tareaSiguienteNo = $t['tarea_siguiente'];
                    }
                }              
                //GUARDAR TAREA DE EVALUAR NIVEL    
                $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                    $this->session->get('userId'),
                    $this->session->get('roluser'),
                    $flujotipo,
                    $tareaActual,
                    'institucioneducativa',
                    $id_tabla,
                    'Trámite Finalizado por ser Nivel BASICO o AUXILIAR',
                    $varevaluacion,
                    $idTramite,
                    $datos,
                    '',
                    $lugarTipoDistrito_id
                );
                if ($enviarTramite['dato'] == true){
                    //SE ACTUALIZA EL REGISTRO L ESTADO 2=CONCLUIDO
                    $respuesta = 1;
                    $msg = $enviarTramite['msg'];
                }else{
                    $respuesta = 0;
                    $msg = $enviarTramite['msg'];
                }
               return  new JsonResponse(array('estado' => $respuesta, 'msg' => $msg)); 
                 
            }else{//EL TRAMITE CORRESPONDE DE NIVEL MEDIO Y CORRESPONDE ENVIAR A LA DGA               
                // OBTENEMOS LA TAREA ACTUAL Y SIGUIENTE
                $tarea = $this->get('wftramite')->obtieneTarea($idTramite, 'idtramite'); 
                $tareaActual = '';
                $tareaSiguienteSi = '';
                $tareaSiguienteNo = '';
                foreach ($tarea as $t) {
                    $tareaActual = $t['tarea_actual'];
                    if ($t['condicion'] == 'SI') {
                        $tareaSiguienteSi = $t['tarea_siguiente'];
                    }
                    if ($t['condicion'] == 'NO') {
                        $tareaSiguienteNo = $t['tarea_siguiente'];
                    }
                }
                $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                    $this->session->get('userId'),
                    $this->session->get('roluser'),
                    $flujotipo,
                    $tareaActual,
                    'institucioneducativa',
                    $id_tabla,
                    'Trámite Enviado a la DGA por ser Nivel MEDIO',
                    'NO',
                    $idTramite,
                    $datos,
                    '',
                    $lugarTipoDistrito_id
                );
                if ($enviarTramite['dato'] == true){
                    $respuesta = 1;
                    $msg = $enviarTramite['msg'];
                }else{
                    $respuesta = 0;
                    $msg = $enviarTramite['msg'];
                }
                return  new JsonResponse(array('estado' => $respuesta, 'msg' => $msg));
            }            
        }else{ // EL TRAMITE ES RECHAZADO Y ES DEVUELTO AL CENTRO             
            //OBTENER LA OBSERVACION CUANDO DEVUELVE EL TRAMITE
            $observacion = trim(mb_strtoupper($request->get('observacion'), 'utf-8'));
            $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                $this->session->get('userId'),
                $this->session->get('roluser'),
                $flujotipo,
                $tareaActual,
                'institucioneducativa',
                $id_tabla,
                $observacion,
                $varevaluacion,
                $idTramite,
                json_encode($datos, JSON_UNESCAPED_UNICODE),
                '',
                $lugarTipoDistrito_id
            );
            if ($enviarTramite['dato'] == true){
                $respuesta = 1;
                $msg = $enviarTramite['msg'];
            }else{
                $respuesta = 0;
                $msg = $enviarTramite['msg'];
            }
           return  new JsonResponse(array('estado' => $respuesta, 'msg' => $msg));  
        }
    }

     //NACIONAL
     //====================FORMULARIO NACIONAL===========================
    public function formularioNacionalAction(Request $request){ //dump($request);die;
        $em = $this->getDoctrine()->getManager();
        $idTramite = $id = $request->get('id');
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
        $flujoTipo = $tramite->getflujoTipo()->getId();
        //OBTENEMOS LOS DATOS DL TRAMITE RECEPCIONADO
        $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                ->select('wfd')
                ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                ->where('td.tramite='.$tramite->getId())
                ->andWhere("wfd.esValido=true")
                ->orderBy("td.flujoProceso")
                ->getQuery()
                ->getResult();
        foreach($wfdatos as $d){
            if ($d->getTramiteDetalle()->getFlujoProceso()->getOrden()==1){
                $datos = json_decode($d->getDatos(),true);              
                $sie = $datos['sie'];
                $idInscripcion=$datos['idInscripcion'];
                $idRue = $datos['rude'];
                $idEspecialidad = $datos['especialidad_id'];
                $idNivel = $datos['nivel_id'];
            }
        }        
        $datosParticipante = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->createQueryBuilder('ei')
                            ->select('e,ei')
                            ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'with', 'ei.estudiante = e.id')
                            ->where('ei.id='.$idInscripcion)
                            ->getQuery()
                            ->getResult();
        $sieDatos = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);                               
        $mencion = $em->getRepository('SieAppWebBundle:SuperiorEspecialidadTipo')->find($idEspecialidad);
        $nivel = $em->getRepository('SieAppWebBundle:SuperiorAcreditacionTipo')->find($idNivel);
        return $this->render('SieProcesosBundle:TramiteCertificacionCursosLargos:formularioNacional.html.twig', array(
            'datosParticipante'=>$datosParticipante[1],
            'datosInscripcionParticipante'=>$datosParticipante[0],
            'mencion'=>$mencion,
            'idMencion'=>$idEspecialidad,            
            'nivel'=>$nivel,
            'idNivel'=>$idNivel,
            'idtramite'=>$id,
            'sie'=>$sie,
            'sieDatos'=>$sieDatos,
            'flujoTipo'=>$flujoTipo 
        )); 
    }
    public function certPerRegistroGuardaNacAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        //RECUPERAMOS INFORMACION ENVIADA
        $sesion = $request->getSession();
        $usuario=$sesion->get('userId');
        $rol=$this->session->get('roluser');
        $flujotipo=$request->get('flujoTipo');
        $tabla= 'institucioneducativa';
        $id_tabla=$request->get('sie');
        $varevaluacion=$request->get('varevaluacion');
        $idNivel=$request->get('idNivel');
        $idTramite=$request->get('idTramite');
        $idMencion=$request->get('idMencion');
        $observacion = '';
        //Armar elmjson de datos que se guardara en el tramite NACIONAL        
        $estudianteInscripcionId = (Int)$request->get('idParticipante');            
        /*Obtenemos el rude del <estudiante></estudiante>  */                    
        $estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($estudianteInscripcionId);
        $rude = $estudianteInscripcion->getEstudiante()->getCodigoRude();
        $datos =  array('sie' => $id_tabla,'idInscripcion'=> $estudianteInscripcionId,'rude'=>$rude,'especialidad_id'=>$idMencion,'nivel_id'=>$idNivel);
        //Se obtiene el id de distrito de la Ue
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->get('sie'));
        $lugarTipoDistrito_id = $institucioneducativa->getleJuridicciongeografica()->getLugarTipoIdDistrito();
        // OBTENEMOS LA TAREA ACTUAL Y SIGUIENTE
        $tarea = $this->get('wftramite')->obtieneTarea($idTramite, 'idtramite');
        $tareaActual = '';
        $tareaSiguienteSi = '';
        $tareaSiguienteNo = '';
        foreach ($tarea as $t) {
            $tareaActual = $t['tarea_actual'];
            if ($t['condicion'] == 'SI') {
                $tareaSiguienteSi = $t['tarea_siguiente'];
            }
            if ($t['condicion'] == 'NO') {
                $tareaSiguienteNo = $t['tarea_siguiente'];
            }
        }//dump($tareaSiguienteSi,$tareaSiguienteNo);die;
        /* dump("*****PRIMERA TAREA ***");
        dump($tarea, $varevaluacion);
        dump($tareaActual);
        dump($tareaSiguienteSi);
        dump($tareaSiguienteNo);
        dump($this->session->get('userId')); */
        
        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tareaActual,$tabla,$id_tabla,'',$varevaluacion,$idTramite,json_encode($datos, JSON_UNESCAPED_UNICODE),'',$lugarTipoDistrito_id);
        if($mensaje['dato']=== true){
            if ($varevaluacion==='SI'){
                $tarea = $this->get('wftramite')->obtieneTarea($idTramite, 'idtramite');
                $tareaActual = '';
                $tareaSiguienteSi2 = '';
                $tareaSiguienteNo2 = '';
                foreach ($tarea as $t) {
                    $tareaActual = $t['tarea_actual'];
                    if ($t['condicion'] == 'SI') {
                        $tareaSiguienteSi2 = $t['tarea_siguiente'];
                    }
                    if ($t['condicion'] == 'NO') {
                        $tareaSiguienteNo2 = $t['tarea_siguiente'];
                    }
                }
                $recibirTramite = $this->get('wftramite')->guardarTramiteRecibido(
                    $this->session->get('userId'),
                    $tareaActual,
                    $idTramite
                );
                if($recibirTramite['dato'] === true){
                    $respuesta = 1;
                    $msg = $recibirTramite['msg'];
                }else{
                    $respuesta = 0;
                    $msg = $recibirTramite['msg'];
                }
                return  new JsonResponse(array('estado' => $respuesta, 'msg' => $msg));
                
            }else{//SE DEVE DEVOLVER EL TRAMITE AL DEPARTAMENTAL
                //SE RECUPERA LA OBSERVACION DE LA DEVOLUCION DEL TRAMITE
                $observacion = trim(mb_strtoupper($request->get('observacion'), 'utf-8'));
                $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                    $this->session->get('userId'),
                    $this->session->get('roluser'),
                    $flujotipo,
                    $tareaActual,
                    'institucioneducativa',
                    $id_tabla,
                    $observacion,
                    $varevaluacion,
                    $idTramite,
                    json_encode($datos, JSON_UNESCAPED_UNICODE),
                    '',
                    $lugarTipoDistrito_id
                );
                if ($enviarTramite['dato'] == true){
                    $respuesta = 1;
                    $msg = $enviarTramite['msg'];
                }else{
                    $respuesta = 0;
                    $msg = $enviarTramite['msg'];
                }
                return  new JsonResponse(array('estado' => $respuesta, 'msg' => $msg));
            }
        }else{
            if ($mensaje['dato'] == true){
                $respuesta = 1;
                $msg = $mensaje['msg'];
            }else{
                $respuesta = 0;
                $msg = $mensaje['msg'];
            }
            return  new JsonResponse(array('estado' => $respuesta, 'msg' => $msg));
        }
    }
    //IMPRESION
    //====================IMPRESION DE PARTICIPANTES HABILITADOS (QUE SU TRAMITE ESTE CONCLUIDO)================
    public function formularioHabilitadosImpresionAction(Request $request){
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
        /* $defaultTramiteController = new defaultTramiteController();
        $defaultTramiteController->setContainer($this->container);

      
        $rolPermitido = array(9,8);

        $esValidoUsuarioRol = $defaultTramiteController->isRolUsuario($id_usuario,$rolPermitido);

        if (!$esValidoUsuarioRol){
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No puede acceder al módulo, revise sus roles asignados e intente nuevamente'));
            return $this->redirect($this->generateUrl('tramite_homepage'));
        } */
        $gestionId = $gestionActual->format('Y');
        return $this->render('SieProcesosBundle:TramiteCertificacionCursosLargos:impresionesCertificados.html.twig', array(
            'formBusqueda' => $this->creaFormBuscaCentroEducacionPermanenteImpresion('tramite_impresion_certificado_permanente_lista_habilitados',0,0,0,0)->createView(),
        ));
    }
    public function creaFormBuscaCentroEducacionPermanenteImpresion($routing, $institucionEducativaId, $gestionId, $especialidadId, $nivelId) {       
        $em = $this->getDoctrine()->getManager();
        $entidadGestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $gestionId));
        $entidadMencionTipo = $this->getMenciondCentroEducativoPermanente($institucionEducativaId, $gestionId);   
        $entidadMencion = array();
        foreach ($entidadMencionTipo as $esp)
        {
           $entidadMencion[$esp['id']] = $esp['especialidad'];
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
        //dump()
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))                
                ->add('sie', 'number', array('label' => 'SIE', 'attr' => array('value' => $institucionEducativaId, 'class' => 'form-control', 'placeholder' => 'Código de institución educativa', 'onInput' => 'valSie()', ' onchange' => 'valSieFocusOut()', 'pattern' => '[0-9]{6,8}', 'maxlength' => '8', 'autocomplete' => 'on', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'entity', array('data' => $entidadGestionTipo, 'empty_value' => 'Seleccione Gestión', 'attr' => array('class' => 'form-control', 'onchange' => 'listar_especialidad(this.value)'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('gt')
                                ->where('gt.id > 2019')
                                ->orderBy('gt.id', 'DESC');
                    },
                ))
                ->add('especialidad', 'choice', array('label' => 'Especialidad', 'empty_value' => 'Seleccione Mención', 'choices' => $entidadMencion, 'data' => $especialidadId, 'attr' => array('class' => 'form-control', 'disabled' => 'disabled', 'onchange' => 'listar_nivel(this.value)', 'required' => true)))
                ->add('nivel', 'choice', array('label' => 'Nivel', 'empty_value' => 'Seleccione Nivel', 'choices' => $entidadNivel, 'data' => $nivelId, 'attr' => array('class' => 'form-control', 'required' => true, 'disabled' => 'disabled', 'onchange' => 'habilitarSubmit()')))
                ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue', 'disabled' => 'disabled')))
                ->getForm();
        return $form;
    }
    public function certTecRegistroListaHabilitadosAction(Request $request){
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = new \DateTime();
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        /* if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        } */

        if ($request->isMethod('POST')) {
            $form = $request->get('form');            
            if ($form) {
                $sie = $form['sie'];
                $gestion = $form['gestion'];
                $especialidad = $form['especialidad'];
                $nivel = $form['nivel'];                
                try {
                    //SE OBTIENE LA LISTA DE PARTICIPANTES CON TRAMITE CONLUIDO
                    $entityParticipantes = $this->getEstudiantesHabilitadosPermanente($sie,$gestion,$especialidad,$nivel);
                    $datosBusqueda = base64_encode(serialize($form));
                    return $this->render('SieProcesosBundle:TramiteCertificacionCursosLargos:listaParticipantesHabilitados.html.twig', array(
                        'formBusqueda' => $this->creaFormBuscaCentroEducacionPermanenteImpresion('tramite_impresion_certificado_permanente_lista_habilitados',$sie,$gestion,$especialidad,$nivel)->createView(),
                        'titulo' => 'Registro',
                        'subtitulo' => 'Trámite',
                        'listaParticipante' => $entityParticipantes,
                        'infoAutorizacionCentro' => $sie,
                        'datosBusqueda' => $datosBusqueda,                       
                        'especialidad'=>$especialidad,
                        'nivel'=>$nivel
                    ));
                } catch (\Doctrine\ORM\NoResultException $exc) {
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al procesar la información, intente nuevamente'));
                    /* return $this->redirect($this->generateUrl('tramite_certificado_tecnico_registro_busca')); */
                }
            }  else {
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
                /* return $this->redirect($this->generateUrl('tramite_certificado_tecnico_registro_busca')); */
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'Error al enviar el formulario, intente nuevamente'));
            /* return $this->redirect($this->generateUrl('tramite_certificado_tecnico_registro_busca')); */
        }
        return $this->redirect($this->generateUrl('login'));
    }    
    private function getEstudiantesHabilitadosPermanente($institucionEducativaId, $gestionId, $especialidadId, $nivelId) {
        $em = $this->getDoctrine()->getManager();
        $queryEntidad = $em->getConnection()->prepare("select ei.id,e.carnet_identidad,e.complemento,e.codigo_rude,e.nombre,e.paterno,e.materno,e.fecha_nacimiento,e.segip_id,seti.id as especialidad_id,sat.id as nivel,cp.estado
            from institucioneducativa_curso ic
            INNER JOIN permanente_institucioneducativa_cursocorto ico on ic.id = ico.institucioneducativa_curso_id
            INNER JOIN estudiante_inscripcion ei on ei.institucioneducativa_curso_id = ic.id
            INNER JOIN estudiante e on e.id = ei.estudiante_id
            INNER JOIN superior_institucioneducativa_periodo sip on sip.id = ic.superior_institucioneducativa_periodo_id
            INNER JOIN superior_institucioneducativa_acreditacion sia ON sia.id = sip.superior_institucioneducativa_acreditacion_id
            INNER JOIN superior_acreditacion_especialidad sae ON sae.id = sia.acreditacion_especialidad_id
            INNER JOIN superior_especialidad_tipo seti ON seti.id = sae.superior_especialidad_tipo_id
            INNER JOIN superior_acreditacion_tipo sat ON sat.id=sae.superior_acreditacion_tipo_id
            LEFT JOIN certificado_permanente cp ON cp.estudiante_inscripcion_id = ei.id
            WHERE ic.institucioneducativa_id = ".$institucionEducativaId." and ic.gestion_tipo_id = ".$gestionId."::double precision and nivel_tipo_id = 231 and ei.estadomatricula_tipo_id = 76 and seti.id=".$especialidadId." and sat.id = ".$nivelId." and cp.estado=2
            ORDER BY superior_institucioneducativa_periodo_id
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();
        return $objEntidad;
    }
    public function imprimirAction(Request $request){ dump($request);die;
        $argum= 'CERTIFICADOS PARTICIPANTES CURSOS LARGOS';
        $gestion=2021;
        $name= 'file.pdf';
        $response = new Response();

        $response->headers->set('Content-type', 'application/pdf');

        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $argum.'_' . $gestion . '.pdf'));
        $response->setContent(file_get_contents($name));

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    //COMPROBANTES PARA DIRECTOR DE TRAMITES
    public function rptComprobanteDirectorAction(Request $request){ //dump($request);die;
        
        //RECUPERAMOS LOS DATOS DEL TRAMITE INICIADO
        $em = $this->getDoctrine()->getManager();
        $sie = $request->get('sie');
        $gestionId = 2021; //pendiente
        //RECUPERAMOS LOS DATOS DEL DIRECTOR  O ENCARGADO DL CENTRO
        $queryMaestroUE = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->createQueryBuilder('jg')
            ->select("lt4.lugar AS departamento, lt3.lugar AS provincia, lt2.lugar AS seccion, lt1.lugar AS canton, lt.lugar AS localidad,
                        dist.distrito, orgt.orgcurricula,
                        inst.id as sie, inst.institucioneducativa,
                        jg.direccion, jg.zona, CONCAT(prs.paterno, ' ', prs.materno, ' ', prs.nombre) AS maestro, prs.carnet, dep.sigla as expedido, prs.complemento")
            ->join('SieAppWebBundle:Institucioneducativa', 'inst', 'WITH', 'inst.leJuridicciongeografica = jg.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jg.lugarTipoLocalidad = lt.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt1', 'WITH', 'lt.lugarTipo = lt1.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt2', 'WITH', 'lt1.lugarTipo = lt2.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt3', 'WITH', 'lt2.lugarTipo = lt3.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt4', 'WITH', 'lt3.lugarTipo = lt4.id')
            ->innerJoin('SieAppWebBundle:MaestroInscripcion', 'mi', 'WITH', 'mi.institucioneducativa = inst.id')
            ->innerJoin('SieAppWebBundle:Persona', 'prs', 'WITH', 'mi.persona = prs.id')
            ->join('SieAppWebBundle:DepartamentoTipo', 'dep', 'WITH', 'prs.expedido = dep.id')
            ->join('SieAppWebBundle:DistritoTipo', 'dist', 'WITH', 'jg.distritoTipo = dist.id')
            ->join('SieAppWebBundle:OrgcurricularTipo', 'orgt', 'WITH', 'inst.orgcurricularTipo = orgt.id')
            ->where('inst.id = :idInstitucion')
            ->andWhere('mi.gestionTipo = :gestion')
            ->andWhere('mi.cargoTipo in (:cargos)')
            ->andWhere('mi.esVigenteAdministrativo = :estado')
            ->setParameter('idInstitucion', $sie)
            ->setParameter('gestion', $gestionId)
            ->setParameter('cargos', array(1,12))
            ->setParameter('estado', 't')
            ->orderBy("mi.cargoTipo")
            ->getQuery()
            ->getSingleResult();
         //dump($queryMaestroUE['maestro']);die;   
        
        
        $pdf = $this->container->get("white_october.tcpdf")->create(
            'PORTRATE', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', true
        );
        // $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetAuthor('Lupita');
        $pdf->SetTitle('Comprobante de Trámite');
        $pdf->SetSubject('Report PDF');
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(true, -10);
        // $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 058', PDF_HEADER_STRING, array(10,10,0), array(255,255,255));
        $pdf->SetKeywords('TCPDF, PDF, COMPROBANTE');
        $pdf->setFontSubsetting(true);
        $pdf->SetMargins(10, 15, 10, true);
        $pdf->SetAutoPageBreak(true, 8);


        $pdf->SetFont('helvetica', '', 9, '', true);
        $pdf->startPageGroup();
        $pdf->AddPage('P', array(215.9, 274.4));//'P', 'LETTER'
        $image_path = base64_decode('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');
        $pdf->Image('@'.$image_path, 9, 9, 30, 24, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
        
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->Cell(0, 2, 'COMPROBANTE DE INICIO DE TRAMITE', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(55, 55, 55);
        $pdf->Cell(0, 2, 'CERTIFICACIÓN PERMANENTE (CURSOS LARGOS)', 0, 1, 'C');

        $pdf->Ln(5);
        $pdf->SetFont('', '', 7);
        $pdf->SetTextColor(20, 20, 20);
        //$pdf->Cell(0, 6, date('d/m/Y'), 0, "", 'R', false);
        $pdf->SetFillColor(221, 221, 221);
        $pdf->SetFont('', '', 9);
        $pdf->Ln(2);
        $pdf->Cell(33, 6, 'Código SIE:', 0, "", 'R', true);
        $pdf->Cell(162, 6, $sie, 0, 1, 'L', false);
        $pdf->Ln(1);
        $pdf->Cell(33, 6, 'Centro:', 0, "", 'R', true);
        $pdf->Cell(162, 6, ($request->get('centro') ? $request->get('centro') : ''), 0, 1, 'L', false);
        $pdf->Ln(1);
        $pdf->Cell(33, 6, 'Mención:', 0, "", 'R', true);
        $pdf->Cell(162, 6, ($request->get('mencion') ? $request->get('mencion') : ''), 0, 1, 'L', false);
        $pdf->Ln(1);
        $pdf->Cell(33, 6, 'Nivel:', 0, "", 'R', true);
        $pdf->Cell(162, 6, ($request->get('nivel') ? $request->get('nivel') : ''), 0, 1, 'L', false);
        $pdf->Ln(6);
        $pdf->SetFont('', 'B', 9);
        $pdf->SetFillColor(221, 221, 221);
        
        $pdf->Cell(0, 8, 'LISTA DE PARTICIPANTES', 1, 1, "", 'L', 0, '', 0, true);
       //$titulo = '<h3>1. Datos del Centro Permanente</h3><br>';
        //$contenido = '<b>SIE: </b>'.$sie.'&nbsp;&nbsp;&nbsp;&nbsp;';
        $tramites = json_decode($request->get('datos'), true);
        $pdf->SetFont('', '', 9);
        $contenido = '<table border="1" cellpadding="1.5">';
        $contenido .= '<tr style="background-color:#ddd;">
            <td alignt="center" height="10" style="line-height: 14px;" width="5%"><b>Nro.</b></td>
            <td alignt="center" height="14" style="line-height: 14px;" width="13%"><b>Trámite</b></td>
            <td alignt="center" height="14" style="line-height: 14px;" width="25%"><b>RUDE</b></td>
            <td alignt="center" height="14" style="line-height: 14px;" width="18%"><b>C.I.</b></td>
            <td alignt="center" height="14" style="line-height: 14px;" width="39%"><b>Nombres y Apellidos</b></td>
            </tr>';
            foreach($tramites as $index => $item) {
                 //OBETENEMOS LOS DATOS DE ESTUDIANTE
                $datosParticipante = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->createQueryBuilder('ei')
                ->select('e.codigoRude,e.paterno,e.materno,e.nombre,e.carnetIdentidad,e.complemento')
                ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'with', 'ei.estudiante = e.id')
                ->where('ei.id='.$item['idinscripcion'])
                ->getQuery()
                ->getResult(); //dump($item['']);die;
                $ci = $datosParticipante[0]['carnetIdentidad'].($datosParticipante[0]['complemento'] == '' ? '' : '-'.$datosParticipante[0]['complemento']);
                $contenido .= '<tr>
                    <td>'.($index + 1).'</td>
                    <td alignt="center">'.$item['idtramite'].'</td>
                    <td>'.$datosParticipante[0]['codigoRude'].'</td>
                    <td>'.$ci.'</td>
                    <td>'.($datosParticipante[0]['nombre'].' '.$datosParticipante[0]['paterno'].' '.$datosParticipante[0]['materno']).'</td>
                    </tr>';
            }
        $contenido .= '</table><br><br>';        
        $firmas='<br><br><br><br><br><br><br><br><table cellpadding="0.5" style="font-size: 8px;">';
        $firmas.='<tr><td align="center" width="36%"><br/></td>
        <td align="center" width="36%"><br/><br/><br/><br/>_____________________________<br/>Directora(or) Unidad Educativa<br>'.$queryMaestroUE['maestro'].'<br>Sello y Firma</td>
        <td align="center" width="28%"><br/><br/><table border="1"><tr><td><br/><br/><br/><br/><br/><br/>VoBo<br/>Directora(or) Distrital de Educación</td></tr></table></td></tr>';
        // $firmas.='<tr><td align="right" colspan="3"><br/><span style="font-size: 6px;"><br/>Fecha de Impresión: '.date('d/m/Y H:i:s').'</span></td></tr>';
        $firmas.='</table>';
        $pdf->writeHTML($contenido, true, false, true, false, '');
        $pdf->writeHTML($firmas, true, false, true, false, '');
        //$pdf->writeHTML($titulo, true, false, true, false, '');
        // print a block of text using Write()
        //$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);     
        $pdf->Output("Comprobante.pdf", 'I');
        return true;
    }
    public function rptComprobanteDirectorDevueltoAction(Request $request){// dump($request);die;
       //RECUPERAMOS LOS DATOS DEL TRAMITE INICIADO
       $em = $this->getDoctrine()->getManager();
       $sie = $request->get('sie');
       $gestionId = 2021; //pendiente
       //RECUPERAMOS LOS DATOS DEL DIRECTOR  O ENCARGADO DL CENTRO
       $queryMaestroUE = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->createQueryBuilder('jg')
           ->select("lt4.lugar AS departamento, lt3.lugar AS provincia, lt2.lugar AS seccion, lt1.lugar AS canton, lt.lugar AS localidad,
                       dist.distrito, orgt.orgcurricula,
                       inst.id as sie, inst.institucioneducativa,
                       jg.direccion, jg.zona, CONCAT(prs.paterno, ' ', prs.materno, ' ', prs.nombre) AS maestro, prs.carnet, dep.sigla as expedido, prs.complemento")
           ->join('SieAppWebBundle:Institucioneducativa', 'inst', 'WITH', 'inst.leJuridicciongeografica = jg.id')
           ->leftJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jg.lugarTipoLocalidad = lt.id')
           ->leftJoin('SieAppWebBundle:LugarTipo', 'lt1', 'WITH', 'lt.lugarTipo = lt1.id')
           ->leftJoin('SieAppWebBundle:LugarTipo', 'lt2', 'WITH', 'lt1.lugarTipo = lt2.id')
           ->leftJoin('SieAppWebBundle:LugarTipo', 'lt3', 'WITH', 'lt2.lugarTipo = lt3.id')
           ->leftJoin('SieAppWebBundle:LugarTipo', 'lt4', 'WITH', 'lt3.lugarTipo = lt4.id')
           ->innerJoin('SieAppWebBundle:MaestroInscripcion', 'mi', 'WITH', 'mi.institucioneducativa = inst.id')
           ->innerJoin('SieAppWebBundle:Persona', 'prs', 'WITH', 'mi.persona = prs.id')
           ->join('SieAppWebBundle:DepartamentoTipo', 'dep', 'WITH', 'prs.expedido = dep.id')
           ->join('SieAppWebBundle:DistritoTipo', 'dist', 'WITH', 'jg.distritoTipo = dist.id')
           ->join('SieAppWebBundle:OrgcurricularTipo', 'orgt', 'WITH', 'inst.orgcurricularTipo = orgt.id')
           ->where('inst.id = :idInstitucion')
           ->andWhere('mi.gestionTipo = :gestion')
           ->andWhere('mi.cargoTipo in (:cargos)')
           ->andWhere('mi.esVigenteAdministrativo = :estado')
           ->setParameter('idInstitucion', $sie)
           ->setParameter('gestion', $gestionId)
           ->setParameter('cargos', array(1,12))
           ->setParameter('estado', 't')
           ->orderBy("mi.cargoTipo")
           ->getQuery()
           ->getSingleResult();
       $pdf = $this->container->get("white_october.tcpdf")->create(
           'PORTRATE', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', true
       );
       // $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
       $pdf->SetAuthor('Lupita');
       $pdf->SetTitle('Comprobante de Trámite');
       $pdf->SetSubject('Report PDF');
       $pdf->SetPrintHeader(false);
       $pdf->SetPrintFooter(true, -10);
       // $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 058', PDF_HEADER_STRING, array(10,10,0), array(255,255,255));
       $pdf->SetKeywords('TCPDF, PDF, COMPROBANTE');
       $pdf->setFontSubsetting(true);
       $pdf->SetMargins(10, 15, 10, true);
       $pdf->SetAutoPageBreak(true, 8);


       $pdf->SetFont('helvetica', '', 9, '', true);
       $pdf->startPageGroup();
       $pdf->AddPage('P', array(215.9, 274.4));//'P', 'LETTER'
       $image_path = base64_decode('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');
       $pdf->Image('@'.$image_path, 9, 9, 30, 24, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
       
       $pdf->Ln(2);
       $pdf->SetFont('helvetica', 'B', 13);
       $pdf->Cell(0, 2, 'COMPROBANTE DE INICIO DE TRAMITE', 0, 1, 'C');
       $pdf->SetFont('helvetica', 'B', 11);
       $pdf->SetTextColor(55, 55, 55);
       $pdf->Cell(0, 2, 'CERTIFICACIÓN PERMANENTE (CURSOS LARGOS)', 0, 1, 'C');

       $pdf->Ln(5);
       $pdf->SetFont('', '', 7);
       $pdf->SetTextColor(20, 20, 20);
       //$pdf->Cell(0, 6, date('d/m/Y'), 0, "", 'R', false);
       $pdf->SetFillColor(221, 221, 221);
       $pdf->SetFont('', '', 9);
       $pdf->Ln(2);
       $pdf->Cell(33, 6, 'Código SIE:', 0, "", 'R', true);
       $pdf->Cell(162, 6, $sie, 0, 1, 'L', false);
       $pdf->Ln(1);
       $pdf->Cell(33, 6, 'Centro:', 0, "", 'R', true);
       $pdf->Cell(162, 6, ($request->get('centro') ? $request->get('centro') : ''), 0, 1, 'L', false);
       $pdf->Ln(1);
       $pdf->Cell(33, 6, 'Mención:', 0, "", 'R', true);
       $pdf->Cell(162, 6, ($request->get('mencion') ? $request->get('mencion') : ''), 0, 1, 'L', false);
       $pdf->Ln(1);
       $pdf->Cell(33, 6, 'Nivel:', 0, "", 'R', true);
       $pdf->Cell(162, 6, ($request->get('nivel') ? $request->get('nivel') : ''), 0, 1, 'L', false);
       $pdf->Ln(6);
       $pdf->SetFont('', 'B', 9);
       $pdf->SetFillColor(221, 221, 221);
       
       $pdf->Cell(0, 8, 'DATOS DEL PARTICIPANTE', 1, 1, "", 'L', 0, '', 0, true);
      //$titulo = '<h3>1. Datos del Centro Permanente</h3><br>';
       //$contenido = '<b>SIE: </b>'.$sie.'&nbsp;&nbsp;&nbsp;&nbsp;';
       $tramites = json_decode($request->get('datos'), true);
       $pdf->SetFont('', '', 9);
       $contenido = '<table border="1" cellpadding="1.5">';
       $contenido .= '<tr style="background-color:#ddd;">
           <td alignt="center" height="10" style="line-height: 14px;" width="5%"><b>Nro.</b></td>
           <td alignt="center" height="14" style="line-height: 14px;" width="13%"><b>Trámite</b></td>
           <td alignt="center" height="14" style="line-height: 14px;" width="25%"><b>RUDE</b></td>
           <td alignt="center" height="14" style="line-height: 14px;" width="18%"><b>C.I.</b></td>
           <td alignt="center" height="14" style="line-height: 14px;" width="39%"><b>Nombres y Apellidos</b></td>
           </tr>';
          
                //OBETENEMOS LOS DATOS DE ESTUDIANTE
               $datosParticipante = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->createQueryBuilder('ei')
               ->select('e.codigoRude,e.paterno,e.materno,e.nombre,e.carnetIdentidad,e.complemento')
               ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'with', 'ei.estudiante = e.id')
               ->where('ei.id='.$request->get('idInscripcion'))
               ->getQuery()
               ->getResult(); //dump($item['']);die;
               $ci = $datosParticipante[0]['carnetIdentidad'].($datosParticipante[0]['complemento'] == '' ? '' : '-'.$datosParticipante[0]['complemento']);
               $contenido .= '<tr>
                   <td> 1 </td>
                   <td alignt="center">'.$request->get('idTramite').'</td>
                   <td>'.$datosParticipante[0]['codigoRude'].'</td>
                   <td>'.$ci.'</td>
                   <td>'.($datosParticipante[0]['nombre'].' '.$datosParticipante[0]['paterno'].' '.$datosParticipante[0]['materno']).'</td>
                   </tr>';
           
       $contenido .= '</table><br><br>';        
       $firmas='<br><br><br><br><br><br><br><br><table cellpadding="0.5" style="font-size: 8px;">';
       $firmas.='<tr><td align="center" width="36%"><br/></td>
       <td align="center" width="36%"><br/><br/><br/><br/>_____________________________<br/>Directora(or) Unidad Educativa<br>'.$queryMaestroUE['maestro'].'<br>Sello y Firma</td>
       <td align="center" width="28%"><br/><br/><table border="1"><tr><td><br/><br/><br/><br/><br/><br/>VoBo<br/>Directora(or) Distrital de Educación</td></tr></table></td></tr>';
       // $firmas.='<tr><td align="right" colspan="3"><br/><span style="font-size: 6px;"><br/>Fecha de Impresión: '.date('d/m/Y H:i:s').'</span></td></tr>';
       $firmas.='</table>';
       $pdf->writeHTML($contenido, true, false, true, false, '');
       $pdf->writeHTML($firmas, true, false, true, false, '');
       //$pdf->writeHTML($titulo, true, false, true, false, '');
       // print a block of text using Write()
       //$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);     
       $pdf->Output("Comprobante.pdf", 'I');
       return true;


    }

    
}
