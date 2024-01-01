<?php

namespace Sie\ProcesosBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
// use Symfony\Component\Security\Core\User\User;

use Sie\AppWebBundle\Entity\WfSolicitudTramite;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
use Sie\AppWebBundle\Entity\BthEstudianteNivelacion;

// use Sie\AppWebBundle\Form\EstudianteInscripcionType;


/**
 * Solicitud BTH Registro Nivelación.
 *
 */
class TramiteBthNivelacionController extends Controller {
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
        return $this->render('SieProcesosBundle:TramiteBthNivelacion:index.html.twig',array(
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
            $msj = 'El estuidante tiene tramites pendientes de nivelación BTH, deben ser finalizados antes de iniciar otro trámite';
        } else {
            list($data, $msj, $nivelacion) = $this->validacionRudeBth($rude, $gestion, $sie);
         }
        return  new JsonResponse(array('data' => $data, 'msj' => $msj, 'gradoniv' => $nivelacion));
        
    }

    private function validacionRudeBth($codigorude, $gestion, $sie)
    {
        $em = $this->getDoctrine()->getManager();
        $rude = $codigorude;
        $msj = '';
		$patron = "/[^A-Za-z0-9]/";
		$reemplazo 	= '';
		$rude = preg_replace($patron, $reemplazo, $rude);
        $rudetmp = substr($rude, 0, 12);
        $data = NULL;
        $gradoNivelar = NULL;
        if (strlen($rude) < 13 or !ctype_digit($rudetmp)){
            $msj = 'Registre un código RUDE válido';
            return  array($data,$msj,$gradoNivelar);
        }

        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(['codigoRude' => $rude]);
        if (count($estudiante) == 0){
            $msj = 'Código RUDE no encontrado';
            return  array($data,$msj,$gradoNivelar);
        }

        $query = $em->getConnection()->prepare("select e.codigo_rude, e.nombre, e.paterno, e.materno,
                e.fecha_nacimiento, e.carnet_identidad, e.complemento, e.id as estId,
                ic.gestion_tipo_id, ic.institucioneducativa_id, ic.nivel_tipo_id, 
                ic.grado_tipo_id, ei.id inscId, ei.estadomatricula_tipo_id, ico.asignatura_tipo_id
                from estudiante e 
                inner join estudiante_inscripcion ei on e.id = ei.estudiante_id 
                inner join institucioneducativa_curso ic on ic.id = ei.institucioneducativa_curso_id 
                inner join institucioneducativa_curso_oferta ico on ic.id = ico.insitucioneducativa_curso_id 
                inner join estudiante_asignatura ea on ea.institucioneducativa_curso_oferta_id = ico.id and ea.estudiante_inscripcion_id = ei.id 
                where e.codigo_rude = '".$rude."'
                and ic.nivel_tipo_id = 13
                and ic.grado_tipo_id in (3,4,5,6)
                and ico.asignatura_tipo_id in (1038,1039)
                and ei.estadomatricula_tipo_id in (4,5)
                order by ic.grado_tipo_id desc ; ");
        $query->execute();
        $historialbth = $query->fetchAll();	

        if (count($historialbth)==0){
            $msj = 'El estuidante no cuenta con historial BTH';
            return  array($data,$msj,$gradoNivelar);
        }

        if (($estudiante->getSegipId() == 0) or ($estudiante->getSegipId() === 1 and $estudiante->getCarnetIdentidad() === '')){
            $msj = 'El estuidante no cuenta con CI validado por Segip, no cumple con el requisito para iniciar el trÁmite.';
            return  array($data,$msj,$gradoNivelar);
        }

        $hbthDatos = $historialbth[0];
        if ($this->session->get('roluser') == 9 && !($hbthDatos['gestion_tipo_id'] == $gestion && $hbthDatos['institucioneducativa_id'] == $sie && $hbthDatos['estadomatricula_tipo_id'] == 4)) {
            $msj = 'El estudiante no es efectivo, o no pertenece a la Unidad Educativa solicitante o no cuenta con el área TTG o TTE en la presente gestión';
            return array($data, $msj, $gradoNivelar);
        } 
                
        $grados = array_column($historialbth, 'grado_tipo_id');
        $gradoCounts = array_count_values($grados);
        $gradosRepetidos = max($gradoCounts) > 1;
        
        if ($gradosRepetidos) 
        {
            $msj = 'El historial del estudiante presenta incosistencia';
            return  array($data,$msj,$gradoNivelar);
        }

        $gradosnoaplica = [3, 6];
        if (in_array($hbthDatos['grado_tipo_id'], $gradosnoaplica)) 
        {
            $msj = 'El Estudiante con codigo Rude '.$rude.' de '.$hbthDatos['grado_tipo_id'].' año de escolaridad del nivel secundario no corresponde realizar el trámite de nivelación BTH';
            return  array($data,$msj,$gradoNivelar);
        }
        
        $gradoTipoId = $hbthDatos['grado_tipo_id'];
        $GradoTipoIdAnt = isset($historialbth[1]['grado_tipo_id']) ? $historialbth[1]['grado_tipo_id'] : null;

        switch ($gradoTipoId) {
            case 4:
                if ($GradoTipoIdAnt === null) {
                    $gradoNivelar = 3;
                    $data = $hbthDatos;
                } elseif ($GradoTipoIdAnt === 3){
                    $msj = 'No requiere iniciar TrÁmite de NivelaciÓn BTH, ya cuenta con el Área TTG en 3ro Secundaria.';
                }
                break;
            case 5:
                if ($GradoTipoIdAnt === 3) {
                    $gradoNivelar = 4;
                    $data = $hbthDatos;
                } elseif ($GradoTipoIdAnt === 4){
                    $msj = 'No puede iniciar TrÁmite de NivelaciÓn BTH, ya cuenta con el Área TTG EN 4to Secundaria.';
                } elseif ($GradoTipoIdAnt === null){
                    $msj = 'No cuenta con el Área TTG en 3ro de Secundaria, no puede iniciar el Tramite de NivelaciÓn BTH.';
                }
                break;
            default:
                $msj = 'Error Inconsistencia';
                break;
        }
        
        return  array($data,$msj,$gradoNivelar);
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
            $gradoNivelar = $form['gradoNivelar'];
            $sie = $form['sie'];
            $calificacion = $form['calificacion'];
            if (is_numeric($calificacion) && !(intval($calificacion) >= 51 && intval($calificacion) <= 100)) {
                // La calificación está en el rango válido (51-100)
                $msj = 'La calificacion debe estar ser mayor o igual a 51 y menor o igual a 100';
                $response->setStatusCode(200);
                        $response->setData(array(
                            'msg'=>$msj,
                            'idTramite'=>"",
                            'urlreporte'=>""
                        ));
                return $response; 
            }
            $gestion = $this->session->get('currentyear');
            $idTramite = null;
            
            // dump($calificacion);die;
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            list($data, $msj, $nivelacion) = $this->validacionRudeBth($codigoRude, $gestion, $sie);
            if ($msj !== ''){
                $response->setStatusCode(200);
                        $response->setData(array(
                            'msg'=>$msj,
                            'idTramite'=>"",
                            'urlreporte'=>""
                        ));
                return $response; 
            }
            // dump($gradoNivelar);
            // dump($nivelacion);die;
            if ((int)$gradoNivelar !== $nivelacion){
                $msj = 'Existe diferencia en el año de escolaridad a nivelar';
                $response->setStatusCode(200);
                        $response->setData(array(
                            'msg'=>$msj,
                            'idTramite'=>"",
                            'urlreporte'=>""
                        ));
                return $response; 
            }
            
            $nArcMemo = $this->guardarArch($sie, $codigoRude,$gestion,'UESM', $_FILES['filememo']);
            $data['memo_ue'] = $nArcMemo;
            $nArcInfDir = $this->guardarArch($sie, $codigoRude,$gestion,'UESI', $_FILES['fileinformeDir']);
            $data['informe_ue'] = $nArcInfDir;
            $data['grado_nivelar'] = $nivelacion;
            $data['calificacion'] = $calificacion;
            $data['flujoTipo'] = $flujo_id;
            $tarea = $this->get('wftramite')->obtieneTarea($flujo_id, 'idflujo');
            $tareaActual = $tarea['tarea_actual'];
            $tareaSiguiente = $tarea['tarea_siguiente'];
            $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);
            
            // OBTENEMOS EL LUGAR DE LA UNIDAD EDUCATIVA
            // OBTENEMOS EL TIPO DE TRAMITE
            $tipoTramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs'=>'BTN'));
            
            // dump($flujo_id);
            // dump($tareaActual);
            // dump($tareaSiguiente);
            // dump($lugarTipo);
            // dump($data);
            // die;
                
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
            // $datos = $this->datosFormulario($idTramite);
            // $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$datos['codigoRude']));

            $response->setStatusCode(200);
            $response->setData(array(
                'msg'=>"",
                'idTramite'=>$idTramite,
                'urlreporte'=> $this->generateUrl('tramite_bth_nivelacion_vista_imprimir', array('idtramite'=>$idTramite))
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
            $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/nivelacionBth/'.$gestion.'/'. $sie . '/' . $codigoRude;
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
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'formulario_BTN_'.$idTramite.'_'.$this->session->get('currentyear'). '.pdf'));
        // dump($this->container->getParameter('urlreportweb'));
        // die;
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') .'reg_bth_tramite_nivelacion_gestion_anterior_v0.rptdesign&tramite_id='.$idTramite.'&&__format=pdf&'));
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

        // $tarea = $this->get('wftramite')->obtieneTarea($idTramite, 'idtramite');
        // dump($tarea);die;
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
        if ($flujo_proceso_act==146 and $tramite_estado_act==15){
            return $this->redirectToRoute('wf_tramite_index', [
                'tipo' => 2,
            ]);
        }

        $sie = $tramite->getInstitucioneducativa()->getId();

        // $institucioneducativa = $this->session->get('ie_nombre');
        // $dependencia = $this->session->get('dependencia');
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
        return $this->render('SieProcesosBundle:TramiteBthNivelacion:distrito.html.twig',array(
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
		    $cuaderno = $form['cuaderno'];
            $inf_nivelacion = $form['infNivelacion'];
            $form_solicitud = $form['solicitud'];
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
            list($data, $msj, $nivelacion) = $this->validacionRudeBth($codigoRude, $gestion, $sie);
            if ($msj !== ''){
                $response->setStatusCode(200);
                        $response->setData(array(
                            'msg'=>$msj,
                            'idTramite'=>"",
                            'urlreporte'=>""
                        ));
                return $response; 
            }

            if ($si_procede == 'true' and ($cuaderno == 'false' or $inf_nivelacion == 'false' or $form_solicitud == 'false')){
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
                'registro_pedagogico'=>$cuaderno,
                'informe_nivelacion'=>$inf_nivelacion,
                'formulario_nivelacion'=>$form_solicitud,
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
        // $institucioneducativa = $this->session->get('ie_nombre');
        // $dependencia = $this->session->get('dependencia');
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
        return $this->render('SieProcesosBundle:TramiteBthNivelacion:departamento.html.twig',array(
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
		    $cuaderno = $form['cuaderno'];
            $inf_nivelacion = $form['infNivelacion'];
            $form_solicitud = $form['solicitud'];
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
            $calificacionNiv = $datos['calificacion'];
            $inscripcionId = $datos['inscid'];
            $gradoNivelar = $datos['grado_nivelar'];
            list($data, $msj, $nivelacion) = $this->validacionRudeBth($codigoRude, $gestion, $sie);
            if ($msj !== ''){
                $response->setStatusCode(200);
                        $response->setData(array(
                            'msg'=>$msj,
                            'idTramite'=>"",
                            'urlreporte'=>""
                        ));
                return $response; 
            }

            if ($si_procede == 'true' and ($cuaderno == 'false' or $inf_nivelacion == 'false' or $form_solicitud == 'false' or $inf_distrito == 'false')){
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
                'registro_pedagogico'=>$cuaderno,
                'informe_nivelacion'=>$inf_nivelacion,
                'formulario_nivelacion'=>$form_solicitud,
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
                            $observacionNiv = 'BTN-Aprobado: tramite Nº'.$idTramite;
                            $asignatura = 1038;
                            // save nota
                            $objEstudianteNotaCualitativa = new EstudianteNotaCualitativa();
                            $objEstudianteNotaCualitativa->setNotaCuantitativa($calificacionNiv);
                            $objEstudianteNotaCualitativa->setUsuarioId($this->session->get('userId'));
                            $objEstudianteNotaCualitativa->setFechaRegistro(new \DateTime('now'));
                            $objEstudianteNotaCualitativa->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($inscripcionId));
                            $objEstudianteNotaCualitativa->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(33));
                            $em->persist($objEstudianteNotaCualitativa);
                            $em->flush();
                            // save regularization
                            $objRegister = new BthEstudianteNivelacion();        
                            $objRegister->setObs($observacion);
                            $objRegister->setFechaRegistro(new \DateTime('now'));
                            $objRegister->setEstudianteNotaCualitativa($em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($objEstudianteNotaCualitativa->getId()));
                            // $objRegister->setEspecialidadTecnicoHumanisticoTipo($em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($form['speciality']));
                            $objRegister->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($gradoNivelar));
                            $objRegister->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find($asignatura));
                            $objRegister->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($inscripcionId));
                            $objRegister->setTramiteId($idTramite);
                            $em->persist($objRegister);
                            $em->flush();
                
                            $em->getConnection()->commit();
                            $msg = 'El trámite Nº '. $idTramite. ' se ha consolidado satisfactoriamente. SE REGISTRO LA NIVELACIÓN.';
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