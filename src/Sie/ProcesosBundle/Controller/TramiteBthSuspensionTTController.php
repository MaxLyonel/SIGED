<?php

namespace Sie\ProcesosBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\WfSolicitudTramite;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteInscripcionHumnisticoTecnico;
use Sie\AppWebBundle\Entity\BthSuspensionTte;


/**
 * Solicitud BTH Suspencion TT Estudiantes
 *
 */
class TramiteBthSuspensionTTController extends Controller {
    public $session;
    public $idInstitucion;
    public $router;
    /**
     * the class constructor
     */
    public function __construct() {

        $this->session = new Session();
    }

    public function indexAction (Request $request) {
        $id_usuario     = $this->session->get('userId');
        $id_rol     = $this->session->get('roluser');
        $ie_id = $this->session->get('ie_id');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();

        $request_id = $request->get('id');
		$request_id = filter_var($request_id,FILTER_SANITIZE_NUMBER_INT);
		$request_id = is_numeric($request_id)?$request_id:-1;

		$request_tipo = $request->get('tipo');
		$request_tipo = filter_var($request_tipo,FILTER_SANITIZE_STRING);

        $sie = ($this->session->get('ie_id')>0)?$this->session->get('ie_id'):'';
        $institucioneducativa = $this->session->get('ie_nombre');
        $dependencia = $this->session->get('dependencia');
        $gestion = $this->session->get('currentyear');
        $ihtt = [1,7];
        $uebth = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findBy(['gestionTipoId' => $gestion,
        'institucioneducativaId' => $sie,
        'institucioneducativaHumanisticoTecnicoTipo' => $ihtt,
        ]);
        $uebthesp = '';
        if ($uebth) {
            $uebthesp = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->findBy(['gestionTipo' => $gestion,
            'institucioneducativa' => $sie,
            ]);
        }
        return $this->render('SieProcesosBundle:TramiteBthSuspensionTT:index.html.twig',array(
            'uebth'=>$uebth,
            'institucioneducativa'=>$institucioneducativa,
            'uebthespecialidad'=>$uebthesp,  
            'flujo_id' => $request_id,
			'flujo_tipo' => $request_tipo,             
        ));
    }

    public function buscaRudeAction (Request $request) 
    {   $em = $this->getDoctrine()->getManager();
        $gestion = $this->session->get('currentyear');
        $rude = $request->get('rude');
        $flujoTipo = $request->get('flujoTipo');
        $sie = ($this->session->get('ie_id')>0)?$this->session->get('ie_id'):'';
        $gestion = $this->session->get('currentyear');
        $query = $em->getConnection()->prepare("
                    select *
                    from tramite t
                    inner join tramite_detalle td on td.tramite_id = t.id
                    inner join wf_solicitud_tramite wf on wf.tramite_detalle_id = td.id
                    inner join flujo_proceso fp on td.flujo_proceso_id =fp.id
                    where t.fecha_fin is null
                    and wf.datos like '%".$rude."%'
                    and wf.es_valido is true
                    and fp.flujo_tipo_id = ".$flujoTipo);
        $query->execute();
        $tramitePendiente = $query->fetchAll();
        if (count($tramitePendiente) !==0){
            $data = NULL;
            $nivelacion = NULL;
            $msj = 'El estuidante tiene tramites pendientes de suspesion excepcional TTE - BTH, deben ser finalizados antes de iniciar otro trámite';
        } else {
            list($data, $msj) = $this->validacionRudeBth($rude, $gestion, $sie);
         }
        return  new JsonResponse(array('data' => $data, 'msj' => $msj));
        
    }

    private function validacionRudeBth($codigorude, $gestion, $sie)
    {
        $em = $this->getDoctrine()->getManager();
        $msj = '';
		$patron = "/[^A-Za-z0-9]/";
		$reemplazo 	= '';
		$rude = preg_replace($patron, $reemplazo, $codigorude);
        $rudetmp = substr($rude, 0, 12);
        $data = NULL;
        if (strlen($rude) < 13 or !ctype_digit($rudetmp)){
            $msj = 'Registre un código RUDE válido';
            // return  array($data,$msj,$gradoNivelar);
            return  array($data,$msj);
        }

        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(['codigoRude' => $rude]);
        if (count($estudiante) == 0){
            $msj = 'Código RUDE no encontrado';
            return  array($data,$msj);
        }

        $query = $em->getConnection()->prepare("
            select e.codigo_rude, e.nombre, e.paterno, e.materno,
            e.fecha_nacimiento, e.carnet_identidad, e.complemento, e.id as estId,
            ic.gestion_tipo_id, ic.institucioneducativa_id, ic.nivel_tipo_id, nt.nivel, ic.grado_tipo_id, gt.grado,
            ei.id inscId, ei.estadomatricula_tipo_id, ico.asignatura_tipo_id, ea.id eaId, eiht.id eiEspId, etht.especialidad
            from estudiante e 
            inner join estudiante_inscripcion ei on e.id = ei.estudiante_id 
            inner join institucioneducativa_curso ic on ic.id = ei.institucioneducativa_curso_id 
            inner join institucioneducativa_curso_oferta ico on ic.id = ico.insitucioneducativa_curso_id 
            inner join estudiante_asignatura ea on ea.institucioneducativa_curso_oferta_id = ico.id and ea.estudiante_inscripcion_id = ei.id 
            inner join nivel_tipo nt on ic.nivel_tipo_id = nt.id 
            inner join grado_tipo gt on ic.grado_tipo_id = gt.id
            left join estudiante_inscripcion_humnistico_tecnico eiht on eiht.estudiante_inscripcion_id = ei.id
            left join especialidad_tecnico_humanistico_tipo etht on eiht.especialidad_tecnico_humanistico_tipo_id = etht.id
            where e.codigo_rude = '".$rude."'
            and ic.nivel_tipo_id = 13
            and ic.grado_tipo_id in (4,5,6)
            and ic.institucioneducativa_id = ".$sie."
            and ic.gestion_tipo_id = ".$gestion."
            and ico.asignatura_tipo_id in (1039)
            and ei.estadomatricula_tipo_id in (4);");
        $query->execute();
        $estudiantebth = $query->fetchAll();	
        
        if (count($estudiantebth)==0){
            $msj = 'El estudiante no cuenta con el área Técnica Tecnológica Especializada en la presente gestión';
            return  array($data,$msj);
        }

        if (count($estudiantebth)> 1){
            $msj = 'El estudiante presenta insconsistencia en su informacion Técnica Tecnológica Especializada';
            return  array($data,$msj);
        }
        if (($estudiante->getSegipId() == 0) or ($estudiante->getSegipId() === 1 and $estudiante->getCarnetIdentidad() === '')){
            $msj = 'El estuidante no cuenta con CI validado por Segip, no cumple con el requisito para iniciar el trámite.';
            return  array($data,$msj);
        }

        $data = $estudiantebth[0];
        return  array($data,$msj);
    }

    public function enviaSolitudUeAction(Request $request){
        $response = new JsonResponse();
        try {
            $form = $request->request->all();
            $flujo_id = $form['request_flujo_id'];
		    $flujo_id = filter_var($flujo_id,FILTER_SANITIZE_NUMBER_INT);
		    $flujo_id = is_numeric($flujo_id)?$flujo_id:-1;
        
		    $flujo_tipo = $form['request_flujo_tipo'];
		    $flujo_tipo = filter_var($flujo_tipo,FILTER_SANITIZE_STRING);
            // OBTENEMOS EL ID DE INSCRIPCION
            $codigoRude = $form['codigoRude'];
            $sie = $form['sie'];
            $gestion = $this->session->get('currentyear');
            $idTramite = null;
            
            // dump($calificacion);die;
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            list($data, $msj) = $this->validacionRudeBth($codigoRude, $gestion, $sie);
            // dump($data);die;
            if ($msj !== ''){
                $response->setStatusCode(200);
                        $response->setData(array(
                            'msg'=>$msj,
                            'idTramite'=>"",
                            'urlreporte'=>""
                        ));
                return $response; 
            }
            
            $nArcSolicitud = $this->guardarArch($sie, $codigoRude,$gestion,'UESM', $_FILES['filesolicitud']);
            $data['solicitud_ue'] = $nArcSolicitud;
            $nArcCertificado = $this->guardarArch($sie, $codigoRude,$gestion,'UESI', $_FILES['filecertificado']);
            $data['certificadoMedico_ue'] = $nArcCertificado;
            $data['flujoTipo'] = $flujo_id;
            $data['fecha_tramite'] = (new \DateTime('now'))->format('Y-m-d H:i:s');
            $tarea = $this->get('wftramite')->obtieneTarea($flujo_id, 'idflujo');
            $tareaActual = $tarea['tarea_actual'];
            $tareaSiguiente = $tarea['tarea_siguiente'];
            $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);
            
            // OBTENEMOS EL LUGAR DE LA UNIDAD EDUCATIVA
            // OBTENEMOS EL TIPO DE TRAMITE
            $tipoTramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs'=>'BSF'));
                
                // REGISTRAMOS UN NUEVO TRAMITE
                $registroTramite = $this->get('wftramite')->guardarTramiteNuevo(
                    $this->session->get('userId'),
                    $this->session->get('roluser'),
                    $flujo_id,
                    $tareaActual,
                    'institucioneducativa',
                    $sie,
                    '',//$obs,
                    $tipoTramite->getId(),//$tipoTramite,
                    '',//$varevaluacion,
                    '',//$idTramite,
                    json_encode($data, JSON_UNESCAPED_UNICODE),
                    '',//$lugarTipoLocalidad,
                    $lugarTipo['lugarTipoIdDistrito']
                );
                
                if ($registroTramite['dato'] == false) {
                    if ($registroTramite['msg'] == "") {
                        $response->setStatusCode(500);
                        return $response;
                    } else {
                        $response->setStatusCode(200);
                        $response->setData(array(
                            'msg'=>$registroTramite['msg'],
                            'idTramite'=>"",
                            'urlreporte'=>""
                        ));
                        return $response;
                    }

                }

                $idTramite = $registroTramite['idtramite'];

            $response->setStatusCode(200);
            $response->setData(array(
                'msg'=>"",
                'idTramite'=>$idTramite,
                'urlreporte'=> $this->generateUrl('tramite_bth_suspension_tt_vista_imprimir', array('idtramite'=>$idTramite))
            ));

            $em->getConnection()->commit();

            return $response;

        } catch (Exception $e) {
            
            $em->getConnection()->rollback();
            $response->setStatusCode(500);
            return $response;
        }
    }

    private function guardarArch($sie, $codigoRude, $gestion, $prefijo, $file)
    {
        if (isset($file)) {
            $type = $file['type'];
            $size = $file['size'];
            $tmp_name = $file['tmp_name'];
            $name = $file['name'];
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_name = $prefijo.date('YmdHis') . '.' . $extension;
            // GUARDAR EL ARCHIVO
            $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/suspensionTTE/'.$gestion.'/'. $sie . '/' . $codigoRude;
            if (!file_exists($directorio)) {
                mkdir($directorio, 0777, true);
            }
    
            $archivador = $directorio . '/' . $new_name;
    
            if (!move_uploaded_file($tmp_name, $archivador)) {
                return null;
            }
    
            // CREAR DATOS DEL ARCHIVO
            $informe = array(
                'name' => $name,
                'type' => $type,
                'tmp_name' => 'nueva_ruta',
                'size' => $size,
                'new_name' => $new_name
            );
    
            return $new_name;
        } else {
            return null;
        }
    }

    public function datosFormulario($idTramite){
        try {
            $em = $this->getDoctrine()->getManager();
            $tarea = $this->get('wftramite')->obtieneTarea($idTramite, 'idtramite');
            $tareaActual = '';
            foreach ($tarea as $t) {
                $tareaActual = $t['tarea_actual'];
            }

            $query = $em->getConnection()->prepare("
                        select * from tramite t 
                        inner join tramite_detalle td on td.tramite_id = t.id
                        inner join flujo_proceso fp on td.flujo_proceso_id = fp.id
                        inner join proceso_tipo pt on fp.proceso_id = pt.id
                        inner join rol_tipo rt on fp.rol_tipo_id = rt.id
                        left JOIN wf_solicitud_tramite wf on td.id=wf.tramite_detalle_id
                        where ((wf.id is not null and wf.es_valido is true) or td.id is not null) 
                        and td.tramite_id = ". $idTramite ." 
                        and wf.es_valido is true
                        and fp.orden = 1
                        order by td.id desc
                        limit 1
                        ");

            $query->execute();
            $dato = $query->fetchAll();
            // OBTENEMOS EL OBJETO JSON
            $dato = $dato[0]['datos'];

            // CONVERTIMOS EN ARRAY EL OBJETO JSON
            $dato = json_decode($dato,true);

            return $dato;

        } catch (Exception $e) {
            
        }
    }

    public function formularioVistaImprimirAction(Request $request)
    {
        $response = new Response();
        $idTramite = $request->get('idtramite');
        $gestion = $this->session->get('currentyear');
        $codigoQR = 'BTN'.$idTramite.'|'.$gestion;
        $data = $this->session->get('userId').'|'.$gestion.'|'.$idTramite;
     
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'formulario_BSF_'.$idTramite.'_'.$this->session->get('currentyear'). '.pdf'));
        // dump($this->container->getParameter('urlreportweb'));
        // die;
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') .'reg_bth_tramite_suspencion_tte_v0_igg.rptdesign&tramite_id='.$idTramite.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function dddRecibeVerificaAction (Request $request) {
        $id_usuario     = $this->session->get('userId');
        $id_rol     = $this->session->get('roluser');
        $ie_id = $this->session->get('ie_id');
        $idTramite = $request->get('id');
        // dump('ok');die;

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $datos = $this->datosFormulario($idTramite);
        // dump($datos);
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($tramite->getTramite());
        $flujo_orden = $tramiteDetalle->getFlujoProceso()->getOrden();
        $tramite_estado_act = $tramiteDetalle->getTramiteEstado()->getId();
        // dump($flujo_orden);die;

        /*******************si ya realizo este etapa no ingresa*********/
        if ($flujo_orden==2 and $tramite_estado_act==15){
            return $this->redirectToRoute('wf_tramite_index', [
                'tipo' => 2,
            ]);
        }

        $sie = $tramite->getInstitucioneducativa()->getId();

        $gestion = $this->session->get('currentyear');
        $ihtt = [1,7];
        $uebth = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findBy(['gestionTipoId' => $gestion,
        'institucioneducativaId' => $sie,
        'institucioneducativaHumanisticoTecnicoTipo' => $ihtt,
        ]);
    // dump($uebth);die;
        $uebthesp = '';
        if ($uebth) {
            $uebthesp = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->findBy(['gestionTipo' => $gestion,
            'institucioneducativa' => $sie,
            ]);
        }
        return $this->render('SieProcesosBundle:TramiteBthSuspensionTT:distrito.html.twig',array(
            'uebth'=>$uebth,
            // 'institucioneducativa'=>$institucioneducativa,
            'uebthespecialidad'=>$uebthesp,
            'data'=>$datos,
            'tramitenro'=>$idTramite, 
        ));
    }

    public function dddEnviaSolitudAction(Request $request){
        $response = new JsonResponse();
        try {
            $form = $request->request->all();
            $idTramite = $form['request_tramite_nro'];
		    // $informe_distrito = $form['fileinformeDis'];
		    $fotocopia_ci = $form['fotocopiaci'];
            $solicitud_sftt = $form['solicitud'];
            $certificado_medico = $form['certificadomedico'];
            $si_procede = $form['siprocede'];
            $no_procede = $form['noprocede'];
            $observacion = mb_strtoupper($form['observacion'],'UTF-8');
            $gestion = $this->session->get('currentyear');
            $msj= '';
           
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $datos = $this->datosFormulario($idTramite);
            if ($datos['gestion_tipo_id'] != date('Y') && $si_procede == 'true'){
                $msj = 'El trámite procede de gestión diferente, solo puede finalizar ';
                $response->setStatusCode(200);
                        $response->setData(array(
                            'msg'=>$msj,
                            'idTramite'=>"",
                            'urlreporte'=>""
                        ));
                return $response; 
            }
            $sie = $datos['institucioneducativa_id'];
            $codigoRude = $datos['codigo_rude'];
            $flujo_tipo = $datos['flujoTipo'];
            list($data, $msj) = $this->validacionRudeBth($codigoRude, $gestion, $sie);
            if ($msj !== ''){
                $response->setStatusCode(200);
                        $response->setData(array(
                            'msg'=>$msj,
                            'idTramite'=>"",
                            'urlreporte'=>""
                        ));
                return $response; 
            }

            if ($si_procede == 'true' and ($fotocopia_ci == 'false' or $solicitud_sftt == 'false' or $certificado_medico == 'false')){
                $msj = 'La unidad solicitante debe presentar todos los requisitos para continuar con el trámite';
                $response->setStatusCode(200);
                        $response->setData(array(
                            'msg'=>$msj,
                            'idTramite'=>"",
                            'urlreporte'=>""
                        ));
                return $response; 
            }
            /*****SE GUARDA ARCHIVO Y OBTIENE NOMBRE *****/
            $nArcInfDis = $this->guardarArch($sie, $codigoRude,$gestion,'DDDINF', $_FILES['fileinformeDis']);
            $procedente = '';
            $finalizar = '';
            /*****SE VERIFICA SI EL PROCESO SIGUE O NO *****/
            if ($si_procede == 'true') {
                $procedente = 'SI';
            } elseif ($no_procede == 'true') {
                $procedente = 'NO';
                $finalizar = 'SI';
            }
            $datosTr = json_encode(array(
                'sie'=>$sie,
                'fotocopia_ci'=>$fotocopia_ci,
                'solicitud_sftt'=>$solicitud_sftt,
                'certificado_medico'=>$certificado_medico,
                'procedente'=>$procedente,
                'observacion'=>$observacion,
                'informe_ddd'=>$nArcInfDis
            ), JSON_UNESCAPED_UNICODE);

            $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);
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
                $flujo_tipo,
                $tareaActual,
                'institucioneducativa',
                $sie,
                $observacion,
                $procedente,
                $idTramite,
                $datosTr,
                '',
                $lugarTipo['lugarTipoIdDistrito']
            );
               
            if ($enviarTramite['dato'] == false) {
                $em->getConnection()->rollback();
                if ($enviarTramite['msg'] == "") {
                    $response->setStatusCode(500);
                    return $response;
                } else {
                    $response->setStatusCode(200);
                    $response->setData(array(
                        'msg'=>$enviarTramite['msg'],
                        'idTramite'=>"",
                        'urlreporte'=>""
                    ));
                    return $response;
                }

            } else {
                $msg = 'El trámite Nº '. $idTramite. ' se ha derivado a la Dirección Departamental de Educación correspondiente.';
                 // VERIFICAR SI EL TRAMITE NO ES PROCEDENTE PARA REGISTRAR LA TAREA DE OBSERVACION
                 if ($procedente == 'NO') {

                    $recibirTramite = $this->get('wftramite')->guardarTramiteRecibido(
                        $this->session->get('userId'),
                        $tareaSiguienteNo,
                        $idTramite
                    );

                    $datosTr = json_encode(array(
                        'sie'=>$sie,
                        'finalizar'=>$finalizar,
                        'observacion'=>$observacion
                    ), JSON_UNESCAPED_UNICODE);

                    $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                        $this->session->get('userId'),
                        $this->session->get('roluser'),
                        $flujo_tipo,
                        $tareaSiguienteNo,
                        'institucioneducativa',
                        $sie,
                        $observacion,
                        $finalizar,
                        $idTramite,
                        $datosTr,
                        '',
                        $lugarTipo['lugarTipoIdDistrito']
                    );


                    $msg = 'El trámite Nº '. $idTramite. ' fué finalizado.';
                }

                $em->getConnection()->commit();
                $response->setStatusCode(200);
                $response->setData(array(
                    'msg'=>$msg,
                    'idTramite'=>"",
                    'urlreporte'=>""
                ));
                return $response;
            }

        } catch (Exception $e) {
            
            $em->getConnection()->rollback();
            $response->setStatusCode(500);
            return $response;
        }        

    }

    public function ddeRecibeVerificaAction (Request $request) {
        
        $id_usuario     = $this->session->get('userId');
        $id_rol     = $this->session->get('roluser');
        $ie_id = $this->session->get('ie_id');
        $idTramite = $request->get('id');
        
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $datos = $this->datosFormulario($idTramite);
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($tramite->getTramite());
        $flujo_proceso_act = $tramiteDetalle->getFlujoProceso()->getId();
        $tramite_estado_act = $tramiteDetalle->getTramiteEstado()->getId();
        /*******************si ya realizo este etapa no ingresa*********/
        if ($flujo_proceso_act==148 and $tramite_estado_act==15){
            return $this->redirectToRoute('wf_tramite_index', [
                'tipo' => 2,
            ]);
        }


        $sie = $tramite->getInstitucioneducativa()->getId();
        $gestion = $this->session->get('currentyear');
        $ihtt = [1,7];
        $uebth = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findBy(['gestionTipoId' => $gestion,
        'institucioneducativaId' => $sie,
        'institucioneducativaHumanisticoTecnicoTipo' => $ihtt,
        ]);
        $uebthesp = '';
        if ($uebth) {
            $uebthesp = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->findBy(['gestionTipo' => $gestion,
            'institucioneducativa' => $sie,
            ]);
        }
        return $this->render('SieProcesosBundle:TramiteBthSuspensionTT:departamento.html.twig',array(
            'uebth'=>$uebth,
            // 'institucioneducativa'=>$institucioneducativa,
            'uebthespecialidad'=>$uebthesp,
            'data'=>$datos,
            'tramitenro'=>$idTramite,            
        ));
    }

    public function ddeConsolidaSolicitudAction(Request $request){
        $response = new JsonResponse();
        try {
            $form = $request->request->all();
            $idTramite = $form['request_tramite_nro'];
            // $informe_distrito = $form['fileinformeDis'];
		    $fotocopia_ci = $form['fotocopiaci'];
            $solicitud = $form['solicitud'];
            $certificado_medico = $form['certificadomedico'];
            $inf_distrito = $form['InformeDistrito'];
            $si_procede = $form['siprocede'];
            $no_procede = $form['noprocede'];
            $observacion = mb_strtoupper($form['observacion'],'UTF-8');
            $gestion = $this->session->get('currentyear');
            $msj= '';
           
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $datos = $this->datosFormulario($idTramite);
            if ($datos['gestion_tipo_id'] != date('Y') && $si_procede == 'true'){
                $msj = 'El trámite procede de gestión diferente, solo puede finalizar ';
                $response->setStatusCode(200);
                        $response->setData(array(
                            'msg'=>$msj,
                            'idTramite'=>"",
                            'urlreporte'=>""
                        ));
                return $response; 
            }
            $sie = $datos['institucioneducativa_id'];
            $codigoRude = $datos['codigo_rude'];
            $flujo_tipo = $datos['flujoTipo'];
            $est_asig_id = $datos['eaid'];
            $inscripcionId = $datos['inscid'];
            $ei_esp_id = $datos['eiespid'];
            list($data, $msj) = $this->validacionRudeBth($codigoRude, $gestion, $sie);
            if ($msj !== ''){
                $response->setStatusCode(200);
                        $response->setData(array(
                            'msg'=>$msj,
                            'idTramite'=>"",
                            'urlreporte'=>""
                        ));
                return $response; 
            }

            if ($est_asig_id != $data['eaid'] or $inscripcionId != $data['inscid'] or $ei_esp_id != $data['eiespid']){
                $msj = 'Existe insconsistencia en la información solicitada, el trámite no puede continuar.';
                $response->setStatusCode(200);
                        $response->setData(array(
                            'msg'=>$msj,
                            'idTramite'=>"",
                            'urlreporte'=>""
                        ));
                return $response; 
            }

            if ($si_procede == 'true' and ($fotocopia_ci == 'false' or $solicitud == 'false' or $certificado_medico == 'false' or $inf_distrito == 'false')){
                $msj = 'Marque y verifique todos los documentos para que sea procedente el trámite';
                $response->setStatusCode(200);
                        $response->setData(array(
                            'msg'=>$msj,
                            'idTramite'=>"",
                            'urlreporte'=>""
                        ));
                return $response; 
            }
            /*****SE GUARDA ARCHIVO Y OBTIENE NOMBRE *****/
            $nArcInfDep = $this->guardarArch($sie, $codigoRude,$gestion,'DDEINF', $_FILES['fileinformeDep']);
            $procedente = '';
            $finalizar = '';
            /*****SE VERIFICA SI EL PROCESO SIGUE O NO *****/
            if ($si_procede == 'true') {
                $procedente = 'SI';
            } elseif ($no_procede == 'true') {
                $procedente = 'NO';
            }
            $finalizar = 'SI';
            $datosTr = json_encode(array(
                'sie'=>$sie,
                'fotocopia_ci'=>$fotocopia_ci,
                'solicitud'=>$solicitud,
                'certificado_medico'=>$certificado_medico,
                'informe_distrito'=>$inf_distrito,
                'procedente'=>$procedente,
                'observacion'=>$observacion,
                'informe_dde'=>$nArcInfDep
            ), JSON_UNESCAPED_UNICODE);

            $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);
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
                $flujo_tipo,
                $tareaActual,
                'institucioneducativa',
                $sie,
                $observacion,
                $procedente,
                $idTramite,
                $datosTr,
                '',
                $lugarTipo['lugarTipoIdDistrito']
            );
               
            if ($enviarTramite['dato'] == false) {
                $em->getConnection()->rollback();
                if ($enviarTramite['msg'] == "") {
                    $response->setStatusCode(500);
                    return $response;
                } else {
                    $response->setStatusCode(200);
                    $response->setData(array(
                        'msg'=>$enviarTramite['msg'],
                        'idTramite'=>"",
                        'urlreporte'=>""
                    ));
                    return $response;
                }

            } else {
                
                 // VERIFICAR SI EL TRAMITE NO ES PROCEDENTE PARA REGISTRAR LA TAREA DE OBSERVACION
                 if ($procedente == 'NO') {

                    $recibirTramite = $this->get('wftramite')->guardarTramiteRecibido(
                        $this->session->get('userId'),
                        $tareaSiguienteNo,
                        $idTramite
                    );

                    $datosTr = json_encode(array(
                        'sie'=>$sie,
                        'finalizar'=>$finalizar,
                        'observacion'=>$observacion
                    ), JSON_UNESCAPED_UNICODE);

                    $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                        $this->session->get('userId'),
                        $this->session->get('roluser'),
                        $flujo_tipo,
                        $tareaSiguienteNo,
                        'institucioneducativa',
                        $sie,
                        $observacion,
                        $finalizar,
                        $idTramite,
                        $datosTr,
                        '',
                        $lugarTipo['lugarTipoIdDistrito']
                    );
                    if ($enviarTramite['dato'] == false) {
                        $em->getConnection()->rollback();
                        if ($enviarTramite['msg'] == "") {
                            $response->setStatusCode(500);
                            return $response;
                        } else {
                            $response->setStatusCode(200);
                            $response->setData(array(
                                'msg'=>$enviarTramite['msg'],
                                'idTramite'=>"",
                                'urlreporte'=>""
                            ));
                            return $response;
                        }
        
                    } else {
                        $msg = 'El trámite Nº '. $idTramite. ' fué finalizado. NO FUE CONSOLIDADO';
                        $em->getConnection()->commit();
                        $response->setStatusCode(200);
                        $response->setData(array(
                            'msg'=>$msg,
                            'idTramite'=>"",
                            'urlreporte'=>""
                        ));
                        return $response;
                    
                    }
                }

                /******ANTES DE REGISTRAR EL TRAMITE SE VERIFICA SI CUMPLE DATA GUARDAR SUSPENSION**********/
                $query = $em->getConnection()->prepare("select * from estudiante_asignatura ea where ea.id = ". $est_asig_id." ;");
                $query->execute();
                $est_asignatura = $query->fetchAll();  
                
                $query = $em->getConnection()->prepare("select * from estudiante_nota en where en.estudiante_asignatura_id = ". $est_asig_id ." ;");
                $query->execute();
                $est_nota = $query->fetchAll();  
                
                $query = $em->getConnection()->prepare("select * from estudiante_inscripcion_humnistico_tecnico eiht where eiht.id = ". $ei_esp_id ." ;");
                $query->execute();
                $est_insc_carrera = $query->fetchAll();  
                
                if (count($est_asignatura)!=1 or count($est_nota)>4 or count( $est_insc_carrera)>1){
                    $msj = 'Existe insconsistencia en la información solicitada, el trámite no puede continuar.';
                    $response->setStatusCode(200);
                    $response->setData(array(
                        'msg'=>$msj,
                        'idTramite'=>"",
                        'urlreporte'=>""
                    ));
                    return $response;
                } else {
                    $datosBsf = json_encode(array(
                        'estudiante_asignatura'=>$est_asignatura[0],
                        'estudiante_nota'=>$est_nota,
                        'estdiante_inscripcion_humanistico_tecnico'=>$est_insc_carrera[0]
                    ), JSON_UNESCAPED_UNICODE);
                }
                
                
                /******PROCEDEMOS A GUARDAR LA INFORMACIÓN*******/

                if ($procedente == 'SI') {
                    $recibirTramite = $this->get('wftramite')->guardarTramiteRecibido(
                        $this->session->get('userId'),
                        $tareaSiguienteSi,
                        $idTramite
                    );

                    $datosTr = json_encode(array(
                        'sie'=>$sie,
                        'finalizar'=>$finalizar,
                        'observacion'=>$observacion
                    ), JSON_UNESCAPED_UNICODE);

                    $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                        $this->session->get('userId'),
                        $this->session->get('roluser'),
                        $flujo_tipo,
                        $tareaSiguienteSi,
                        'institucioneducativa',
                        $sie,
                        $observacion,
                        $finalizar,
                        $idTramite,
                        $datosTr,
                        '',
                        $lugarTipo['lugarTipoIdDistrito']
                    );
                    if ($enviarTramite['dato'] == false) {
                        $em->getConnection()->rollback();
                        if ($enviarTramite['msg'] == "") {
                            $response->setStatusCode(500);
                            return $response;
                        } else {
                            $response->setStatusCode(200);
                            $response->setData(array(
                                'msg'=>$enviarTramite['msg'],
                                'idTramite'=>"",
                                'urlreporte'=>""
                            ));
                            return $response;
                        }
        
                    } else {
                        try {
                            foreach ($est_nota as $est_nota) {
                                // Busca y elimina la nota
                                $nota=$em->getRepository('SieAppWebBundle:EstudianteNota')->find($est_nota['id']);
                                if ($nota) {
                                    $em->remove($nota);
                                }
                            }

                            $est_asignatura=$em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($est_asig_id);
                            if ($est_asignatura) {
                                $em->remove($est_asignatura);
                            }
                            
                            $est_carrera=$em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->find($ei_esp_id);
                            if ($est_carrera) {
                                $em->remove($est_carrera);
                            }
                            $em->flush();
                            // save nota
                            $estSuspension = new BthSuspensionTte();
                            $estSuspension->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($inscripcionId));
                            $estSuspension->setCodigoRude($codigoRude);
                            $estSuspension->setDatos($datosBsf);
                            $estSuspension->setTramiteId($idTramite);
                            $estSuspension->setEstudianteInscripcionHumnisticoTecnicoId($ei_esp_id);
                            $estSuspension->setEspecialidadTecnicoHumanisticoTipoId($est_insc_carrera[0]['especialidad_tecnico_humanistico_tipo_id']);
                            $estSuspension->setFerchaRegistro(new \DateTime('now'));
                            $em->persist($estSuspension);
                            $em->flush();
                            // save regularization
                            $em->getConnection()->commit();
                            $msg = 'El trámite Nº '. $idTramite. ' se ha consolidado satisfactoriamente. SE REGISTRO LA SUSPENSIÓN EXCEPCIONAL EN LA FORMACIÓN TTE.';
                        } catch (Exception $e) {
                            $msg = 'Se presento algunos problemas...';
                            $em->getConnection()->rollback();
                        }
                    }
                }

                // $em->getConnection()->commit();
                $response->setStatusCode(200);
                $response->setData(array(
                    'msg'=>$msg,
                    'idTramite'=>"",
                    'urlreporte'=>""
                ));
                return $response;
            }

        } catch (Exception $e) {
            
            $em->getConnection()->rollback();
            $response->setStatusCode(500);
            return $response;
        }  
    }

}