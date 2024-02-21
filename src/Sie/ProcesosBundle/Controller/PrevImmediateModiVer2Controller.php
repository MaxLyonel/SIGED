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
use Sie\ProcesosBundle\Controller\TramiteRueController;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEliminados;
/**
 * Reqyes previous immediate management modification controller
 * by krlos
 *
 */

class PrevImmediateModiVer2Controller extends Controller{

    public $session;
    public $idInstitucion;
    public $router;
    /**
     * the class constructor
     */
    public function __construct() {

        $this->session = new Session();
    }

    public function indexoldAction(){
        return $this->render('SieProcesosBundle:PrevImmediateModiVer2:index.html.twig', array(
                // ...
            ));    
    }

    public function indexAction (Request $request) {
        $id_usuario     = $this->session->get('userId');
        $id_rol     = $this->session->get('roluser');
        $ie_id = $this->session->get('ie_id');
        $gestionAnterior = $this->session->get('currentyear')-1;

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $id = $request->get('id');
        $tipo = $request->get('tipo');

        $idTramite = null;
        $historial = null;
        $flujoTipo = null;

        if($tipo == 'idtramite'){
            $idTramite = $id;
            $em = $this->getDoctrine()->getManager();
            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
            $flujoTipo = $tramite->getFlujoTipo()->getId();
            $historial = $this->historial($idTramite);
        }else{
            $flujoTipo = $request->get('id');
        }

        return $this->render('SieProcesosBundle:PrevImmediateModiVer2:index.html.twig', array(
            'flujoTipo'=>$flujoTipo,
            'historial'=>$historial,
            'idTramite'=>$idTramite,
            'sieActual'=>$ie_id,
            'gestant'=>$gestionAnterior
        ));
    }

    public function buscarEstudianteAction(Request $request){

        $response = new JsonResponse();

        $codigoRude = $request->get('codigoRude');
        $flujoTipo = $request->get('flujoTipo');
        $sie = $this->session->get('ie_id');
        $rol = $this->session->get('roluser');

        // VALIDAMOS QUE EL USUARIO QUE ESTA REALIZANDO LA SOLICITUD SEA CON ROL DE DIRECTOR
        // PARA EVITAR ERRORES CUANDO SE SOBREESCRIBEN LAS SESIONES
        if ($rol != 10) {
            $response->setStatusCode(202);
            $response->setData('El usuario actual no tiene rol de Director, cierre sesion e ingrese nuevamente al sistema.');
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$codigoRude));

        //start addded to 13-6 and stasus 5
       $inscriptionval = $em->createQueryBuilder()
                    ->select('IDENTITY(ei.estadomatriculaTipo)')
                    ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                    ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                    ->where('e.codigoRude = :rude')
                    ->andWhere('iec.gestionTipo = :gestion')
                    ->andWhere('ei.estadomatriculaTipo = :mat')
                    // ->andWhere('iec.nivelTipo = :nivel')
                    // ->andWhere('iec.gradoTipo = :grado')
                    ->setParameter('rude', $codigoRude)
                    ->setParameter('mat', 5)
                    // ->setParameter('nivel', 13)
                    // ->setParameter('grado', 6)
                    ->setParameter('gestion', $this->session->get('currentyear')-1)
                    ->getQuery()
                    ->getResult();
        //end addded to 13-6 and stasus 5

        // VALIDAMOS QUE EL ESTUDIANTE NO TENGA DOCUMENTOS EMITIDOS
        $documentos = $this->get('funciones')->validarDocumentoEstudiante($codigoRude);
        if (count($documentos) > 0  && (!(sizeof( $inscriptionval)>0))) {
            $response->setStatusCode(202);
            $response->setData('El estudiante con el código RUDE '. $codigoRude .' tiene documentos emitidos, por esto no puede realizar la solicitud!');
            return $response;
        }
        // dump($documentos);die;

        // SI EL ESTUDIANTE NO EXISTE, DEVOLVEMOS 204 SIN CONTENIDO
        if(!$estudiante){
            $response->setStatusCode(202);
            $response->setData('El estudiante con el código RUDE '. $codigoRude .' no fue encontrado.');
            return $response;
        }

        $inscripciones = $em->createQueryBuilder()
                            ->select('ei.id, ie.id as sie, ie.institucioneducativa, get.gestion, nt.id idNivel, nt.nivel, gt.grado, pt.paralelo, tt.turno, emt.estadomatricula, emt.id estadomatriculaId, dep.departamento, dt.distrito')
                            ->from('SieAppWebBundle:EstudianteInscripcion','ei')
                            ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','iec.institucioneducativa = ie.id')
                            ->innerJoin('SieAppWebBundle:GestionTipo','get','with','iec.gestionTipo = get.id')
                            ->innerJoin('SieAppWebBundle:NivelTipo','nt','with','iec.nivelTipo = nt.id')
                            ->innerJoin('SieAppWebBundle:GradoTipo','gt','with','iec.gradoTipo = gt.id')
                            ->innerJoin('SieAppWebBundle:ParaleloTipo','pt','with','iec.paraleloTipo = pt.id')
                            ->innerJoin('SieAppWebBundle:TurnoTipo','tt','with','iec.turnoTipo = tt.id')
                            ->innerJoin('SieAppWebBundle:EstadomatriculaTipo','emt','with','ei.estadomatriculaTipo = emt.id')
                            ->innerJoin('SieAppWebBundle:JurisdiccionGeografica','jg','with','ie.leJuridicciongeografica = jg.id')
                            ->innerJoin('SieAppWebBundle:DistritoTipo','dt','with','jg.distritoTipo = dt.id')
                            ->innerJoin('SieAppWebBundle:DepartamentoTipo','dep','with','dt.departamentoTipo = dep.id')
                            ->where('e.codigoRude = :rude')
                            // ->andWhere('ie.id = :sie')
                            ->setParameter('rude', $codigoRude)
                            // ->setParameter('sie', $sie)
                            ->addOrderBy('get.id','DESC')
                            ->addOrderBy('nt.id','DESC')
                            ->addOrderBy('gt.id','DESC')
                            ->getQuery()
                            ->getResult();

        // SI EL ESTUDIANTE NO TIENE INSCRIPCIONES
        if(!$inscripciones){
            $response->setStatusCode(202);
            $response->setData('El estudiante no tiene inscripciones registradas en esta Unidad Educativa');
            return $response;
        }


        // VALIDAMOS SI LA UNIDAD EDUCATIVA TIENE TUICION SOBRE EL ESTUDIANTE
        // if($inscripciones[0]['sie'] != $sie){
        //     $response->setStatusCode(202);
        //     $response->setData('No tiene tuición sobre el estudiante');
        //     return $response;   
        // }

        $inscripcionesArray = [];
        $sieselected = 1;
        foreach ($inscripciones as $key => $value) {
            $inscripcionesArray[] = array(
                'idInscripcion'=>$value['id'],
                'sie'=>$value['sie'],
                'institucioneducativa'=>$value['institucioneducativa'],
                'gestion'=>$value['gestion'],
                'nivel'=>$value['nivel'],
                'grado'=>$value['grado'],
                'paralelo'=>$value['paralelo'],
                'turno'=>$value['turno'],
                'estadomatricula'=>$value['estadomatricula'],
                'estadomatriculaId'=>$value['estadomatriculaId'],
                'idNivel'=>$value['idNivel'],
                'departamento'=>$value['departamento'],
                'distrito'=>$value['distrito']
                // 'ruta'=>$this->generateUrl('tramite_modificacion_calificaciones_formulario', array('flujoTipo'=>$flujoTipo,'idInscripcion'=>$value['id']))
            );

            if($value['gestion'] == $this->session->get('currentyear')-1 and (in_array($value['estadomatriculaId'], array(4,5,11))) ){
                $sieselected = $value['sie'];
            }
        }

        /////start get dir info
        if(sizeof($inscripcionesArray)>0){
	         $query = $em->getConnection()->prepare("
	         		select pe.* 
					from maestro_inscripcion mi 
					inner join persona pe on (mi.persona_id = pe.id)
					where mi.institucioneducativa_id = ".$sieselected." and gestion_tipo_id = ".$inscripcionesArray[0]['gestion']." and cargo_tipo_id in (1,12)
	            ");
	            $query->execute();

	            $dirinfo = $query->fetchAll();
                
	            if(sizeof($dirinfo)>0){
			   		$directorNombre = $dirinfo[0]['nombre'].' '.$dirinfo[0]['paterno'].' '.$dirinfo[0]['materno'];
			        $directorCarnet = $dirinfo[0]['carnet'];
			        $directorComplemento = $dirinfo[0]['complemento'];
	            }else{
			   		$directorNombre = $user->getPersona()->getNombre().' '.$user->getPersona()->getPaterno().' '.$user->getPersona()->getMaterno();
			        $directorCarnet = ($user->getPersona()->getSegipId() == 1)?$user->getPersona()->getCarnet():'';
			        $directorComplemento = ($user->getPersona()->getSegipId() == 1)?$user->getPersona()->getComplemento():'';  	            	
	            }     

        }else{

	   		$directorNombre = $user->getPersona()->getNombre().' '.$user->getPersona()->getPaterno().' '.$user->getPersona()->getMaterno();
	        $directorCarnet = ($user->getPersona()->getSegipId() == 1)?$user->getPersona()->getCarnet():'';
	        $directorComplemento = ($user->getPersona()->getSegipId() == 1)?$user->getPersona()->getComplemento():'';        	
        }
        /////ebd get dir info

        // OBTENEMOS EL DATO DEL DIRECTOR
        $user = $this->container->get('security.context')->getToken()->getUser();
     

        $response->setStatusCode(200);
        $response->setData(array(
            'codigoRude'=>$estudiante->getCodigoRude(),
            'estudiante'=> $estudiante->getNombre().' '.$estudiante->getPaterno().' '.$estudiante->getMaterno(),
            'carnet'=>($estudiante->getSegipId() == 1)?$estudiante->getCarnetIdentidad():'',
            'complemento'=>($estudiante->getSegipId() == 1)?$estudiante->getComplemento():'',
            'inscripciones'=>$inscripcionesArray,
            'directorNombre'=>$directorNombre,
            'directorCarnet'=>$directorCarnet,
            'directorComplemento'=>$directorComplemento,
            'yearflow'=>$this->session->get('currentyear')-1,

        ));

        return $response;
    }

    public function buscarCalificacionesAction(Request $request){
        $idInscripcion = $request->get('idInscripcion');
        $idTramite = $request->get('idTramite');
        $flujoTipo = $request->get('flujoTipo');
        $em = $this->getDoctrine()->getManager();
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();


	    //START get the tuicion
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
        $query->bindValue(':sie', $sie);
        $query->bindValue(':roluser', $this->session->get('roluser'));
        $query->execute();
        $aTuicion = $query->fetchAll();

        $aniveles = array();
         if ($aTuicion[0]['get_ue_tuicion']) {
         }else{
	        $response = new JsonResponse();
	        $response->setStatusCode(200);
	        $response->setData(array(
	            'datos'=>array(),
	            'tramitePendiente'=>array(),
	            'estadosMatricula'=>array(),
	            'promedioGeneral'=>array(),
	            'swCTuicionUe' => false,
	        ));

	        return $response;         	

         }        
         //END get the tuicion
        if ($idTramite == "") {
            $idTramite = 0;
        }

        // VALIDAMOS QUE LA INSCRIPCION NO TENGA UN TRAMITE PENDIENTE
        $query = $em->getConnection()->prepare("
                    select *
                    from tramite t
                    inner join tramite_detalle td on td.tramite_id = t.id
                    inner join wf_solicitud_tramite wf on wf.tramite_detalle_id = td.id
                    inner join flujo_proceso fp on td.flujo_proceso_id =fp.id
                    where t.fecha_fin is null
                    and t.id != ". $idTramite ."
                    and wf.datos like '%".$idInscripcion."%'
                    and t.institucioneducativa_id = ". $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId() ."
                    and wf.es_valido is true
                    and fp.flujo_tipo_id = ".$flujoTipo);

        $query->execute();
        $tramitePendiente = $query->fetchAll();

        $operativo = $this->get('funciones')->obtenerOperativo($sie, $gestion);

        $datos = [];
        $promedioGeneral = ''; // PARA PRIMARIA 2019
        if(count($tramitePendiente)<=0) {
            $datos = $this->get('notas')->regularDB($idInscripcion, $operativo);
            
            // dump($operativo);
            // dump($datos['cualitativas']);
            // die;
            if($datos['gestion'] >= 2019 and $datos['nivel'] == 12){
                foreach ($datos['cualitativas'] as $key => $value) {
                    if ($value['idNotaTipo'] == 9) {
                        $promedioGeneral = $value['notaCuantitativa'];
                    }
                }
            }
        }

        // ESTADOS MATRICULAS
        $estados = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>array(4,5,11,28)));
        $arrayEstados = [];
        foreach ($estados as $key => $value) {
            $arrayEstados[] = array(
                'id'=>$value->getId(),
                'estadoMatricula'=>$value->getEstadomatricula()
            );
        }

        // dump($arrayEstados);die;

        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData(array(
            'datos'=>$datos,
            'tramitePendiente'=>$tramitePendiente,
            'estadosMatricula'=>$arrayEstados,
            'promedioGeneral'=>$promedioGeneral,
            'swCTuicionUe' => true,
        ));

        return $response;

        // return $this->render('SieProcesosBundle:PrevImmediateModiVer2:formulario.html.twig', array(
        //     'inscripcion'=>$inscripcion,
        //     'data'=>$data
        // ));
    }

    public function formularioSaveAction(Request $request){

        $response = new JsonResponse();
        try {
            // OBTENEMOS EL ID DE INSCRIPCION
            $idTramite = $request->get('idTramite');
            $idInscripcion = $request->get('idInscripcion');
            
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

            // $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            // $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

            // $sie = $this->session->get('ie_id');
            $sie = $request->get('sie');
            $gestion = $this->session->get('currentyear');


            //get infor about UE
            $objInstEduCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcion->getInstitucioneducativaCurso()->getId());
            $sie = $objInstEduCurso->getInstitucioneducativa()->getId();
            
            // OBTENEMOS EL ID DEL TRAMITE SI SE TRATA DE UNA MODIFICACION
            $idTramite = $request->get('idTramite');
            $informe = '';
            // VERIFICAMOS SI EXISTE EL ARCHIVO
            // if(isset($_FILES['informe'])){
            //     $file = $_FILES['informe'];

            //     $type = $file['type'];
            //     $size = $file['size'];
            //     $tmp_name = $file['tmp_name'];
            //     $name = $file['name'];
            //     $extension = explode('.', $name);
            //     $extension = $extension[count($extension)-1];
            //     $new_name = date('YmdHis').'.'.$extension;

            //     // GUARDAMOS EL ARCHIVO
            //     $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/modificacionNotas/' . $sie;
            //     if (!file_exists($directorio)) {
            //         mkdir($directorio, 0777, true);
            //     }

            //     $archivador = $directorio.'/'.$new_name;
            //     //unlink($archivador);
            //     if(!move_uploaded_file($tmp_name, $archivador)){
            //         $response->setStatusCode(500);
            //         return $response;
            //     }

            //     // CREAMOS LOS DATOS DE LA IMAGEN
            //     $informe = array(
            //         'name' => $name,
            //         'type' => $type,
            //         'tmp_name' => 'nueva_ruta',
            //         'size' => $size,
            //         'new_name' => $new_name
            //     );
            // }else{
            //     $informe = null;
            // }

            // OBTENEMOS LA INFORMACION DEL FORMULARIO
            $codigoRude = $request->get('codigoRude');
            $estudiante = $request->get('estudiante');
            $carnet = $request->get('carnet');
            $complemento = $request->get('complemento');
            $sieInscripcion = $request->get('sie');
            $institucioneducativa = $request->get('institucioneducativa');
            $nivelInscripcion = $request->get('nivel');
            $gradoInscripcion = $request->get('grado');
            $paraleloInscripcion = $request->get('paralelo');
            $turnoInscripcion = $request->get('turno');
            $gestionInscripcion = $request->get('gestion');
            $departamentoInscripcion = $request->get('departamento');
            $distritoInscripcion = $request->get('distrito');
            
            $directorNombre = $request->get('directorNombre');
            $directorCarnet = $request->get('directorCarnet');
            $directorComplemento = $request->get('directorComplemento');

            $flujoTipo = $request->get('flujoTipo');
            $notas = json_decode($request->get('notas'),true);
            $notasCualitativas = json_decode($request->get('notasCualitativas'),true);
            $justificacion = mb_strtoupper($request->get('justificacion'),'utf-8');
            $checkInforme = $request->get('checkInforme');
            $checkCuaderno = $request->get('checkCuaderno');
            $checkFormulario = $request->get('checkFormulario');



            $dataInscription = array(
                "gestion"=>$gestionInscripcion ,
                "nivelId"=>$inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId(),
                "nivel"=>$nivelInscripcion,
                "gradoId"=>$inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId(),
                "grado"=>$gradoInscripcion,
                "paraleloId"=>$inscripcion->getInstitucioneducativaCurso()->getParaleloTipo()->getId(),
                "paralelo"=>$paraleloInscripcion,
                "turnoId"=>$inscripcion->getInstitucioneducativaCurso()->getTurnoTipo()->getId(),
                "turno"=>$turnoInscripcion,
                "keyId"=>"",
                "notasRequest"=>"",
                "idTramite"=>"",
                "flujoTipo"=>$flujoTipo,
                "sie"=>$sieInscripcion,
                "posSelected"=>0,
                "codigoRude"=>$codigoRude
            );

            // ARMAMOS EL ARRAY DE LA DATA
            $data = array(
                'idInscripcion'=> $idInscripcion,
                'codigoRude' => $codigoRude,
                'estudiante' => $estudiante,
                'carnet' => $carnet,
                'complemento' => $complemento,
                'sie' => $sieInscripcion,
                'institucioneducativa' => $institucioneducativa,
                'nivel' => $nivelInscripcion,
                'grado' => $gradoInscripcion,
                'paralelo' => $paraleloInscripcion,
                'turno' => $turnoInscripcion,
                'gestion' => $gestionInscripcion,
                'departamento' => $departamentoInscripcion,
                'distrito' => $distritoInscripcion,
                'directorNombre'=>$directorNombre,
                'directorCarnet'=>$directorCarnet,
                'directorComplemento'=>$directorComplemento,
                'flujoTipo'=>$flujoTipo,
                'notas'=> $notas,
                'notasCualitativas'=>$notasCualitativas,
                'dataInscription'=>array($dataInscription),
                'justificacion'=>$justificacion,
                'checkInforme'=>$checkInforme,
                'checkCuaderno'=>$checkCuaderno,
                'checkFormulario'=>$checkFormulario,
                'informe'=>$informe
            );

            $tarea = $this->get('wftramite')->obtieneTarea($flujoTipo, 'idflujo');
            $tareaActual = $tarea['tarea_actual'];
            $tareaSiguiente = $tarea['tarea_siguiente'];

            // OBTENEMOS EL LUGAR DE LA UNIDAD EDUCATIVA
            $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);

            // OBTENEMOS EL TIPO DE TRAMITE
            $tipoTramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs'=>'PIM'));


            if ($idTramite == null) {
                
                // OBTENEMOS OPERATIVO ACTUAL Y LO AGREGAMOS AL ARRAY DE DATOS           
                $data['operativoActual'] = $this->get('funciones')->obtenerOperativo($sieInscripcion,$gestionInscripcion);

                // REGISTRAMOS UN NUEVO TRAMITE
                $registroTramite = $this->get('wftramite')->guardarTramiteNuevo(
                    $this->session->get('userId'),
                    $this->session->get('roluser'),
                    $flujoTipo,
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
                    $response->setStatusCode(500);
                    return $response;
                }

                $idTramite = $registroTramite['idtramite'];

                // $msg = "El Tramite ".$registroTramite['msg']." fue guardado y enviado exitosamente";

            }else{
                // RECUPERAMOS EL OPERATIVO DONDE SE INICIO EL TRAMITE Y LO AGREGAMOS AL ARRAY DE DATOS
                $datosFormulario = $this->datosFormulario($idTramite);
                $data['operativoActual'] = $datosFormulario['operativoActual'];

                // NUEVAMENTE ENVIAMOS EL TRAMITE
                $registroTramite = $this->get('wftramite')->guardarTramiteEnviado(
                    $this->session->get('userId'),
                    $this->session->get('roluser'),
                    $flujoTipo,
                    $tareaActual,
                    'institucioneducativa',
                    $sie,
                    '',
                    '',
                    $idTramite,
                    json_encode($data, JSON_UNESCAPED_UNICODE),
                    '',
                    $lugarTipo['lugarTipoIdDistrito']
                );

                if ($registroTramite['dato'] == false) {
                    $response->setStatusCode(500);
                    return $response;
                }

                $msg = "El Tramite ".$registroTramite['msg']." fue enviado exitosamente";

                $request->getSession()
                        ->getFlashBag()
                        ->add('exito', $msg);
            }

            $datos = $this->datosFormulario($idTramite);
            //$codigoQR = 'FTMC'.$idTramite.'|'.$datos['codigoRude'].'|'.$datos['sie'].'|'.$datos['gestion'];
            $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$datos['codigoRude']));

            $response->setStatusCode(200);
            $response->setData(array(
                'idTramite'=>$idTramite,
                'urlreporte'=> $this->generateUrl('tramite_add_mod_download_requet', array('idTramite'=>$idTramite))
            ));

            $em->getConnection()->commit();

            return $response;

        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $response->setStatusCode(500);
            return $response;
        }
    }

    public function formularioImprimirAction(Request $request){

        $idTramite = $request->get('idtramite');
        // dump($idTramite);die;
        $idTramiteDetalle = $request->get('id_td');

        $datos = $this->datosFormulario($idTramite);
        $codigoQR = 'FTMC'.$idTramite.'|'.$datos['codigoRude'].'|'.$datos['sie'].'|'.$datos['gestion'];

        return $this->redirectToRoute('download_tramite_modificacion_calificaciones_formulario', array('idTramite'=>$idTramite, 'codigoQR'=>$codigoQR));
    }

    public function formularioObtenerDatosAction(Request $request){
        $idTramite = $request->get('idTramite');
        $em = $this->getDoctrine()->getManager();
        $tramiteDetalle = $em->createQueryBuilder()
                            ->select('td.id idTramiteDetalle, te.tramiteEstado, wf.datos, ft.id flujoTipo, ie.id sie')
                            ->from('SieAppWebBundle:Tramite','t')
                            ->innerJoin('SieAppWebBundle:TramiteDetalle','td','with','td.tramite = t.id')
                            ->innerJoin('SieAppWebBundle:TramiteEstado','te','with','td.tramiteEstado = te.id')
                            ->innerJoin('SieAppWebBundle:WfSolicitudTramite','wf','with','wf.tramiteDetalle = td.id')
                            ->innerJoin('SieAppWebBundle:FlujoTipo','ft','with','t.flujoTipo = ft.id')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','t.institucioneducativa = ie.id')
                            ->where('t.id = :idTramite')
                            ->orderBy('td.id','DESC')
                            ->setMaxResults(1)
                            ->setParameter('idTramite', $idTramite)
                            ->getQuery()
                            ->getResult();

        $idTramiteDetalle = $tramiteDetalle[0]['idTramiteDetalle'];
        $tramiteEstado = $tramiteDetalle[0]['tramiteEstado'];
        $data = json_decode($tramiteDetalle[0]['datos'],true);
        $flujoTipo = $tramiteDetalle[0]['flujoTipo'];

        // DATOS DE LAS NOTAS DEL ESTUDIANTE
        $data['archivoUrl'] = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/modificacionNotas/' . $tramiteDetalle[0]['sie'] .'/'. $data['archivo']['new_name'];
        // $data = json_encode($data);
        // dump($data['notas']);die;

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    public function historial($idTramite){
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
                        order by fp.orden asc
                        ");

            // $query = $em->getConnection()->prepare("
            //             select * from tramite t 
            //             inner join tramite_detalle td on td.tramite_id = t.id
            //             inner join flujo_proceso fp on td.flujo_proceso_id = fp.id
            //             inner join proceso_tipo pt on fp.proceso_id = pt.id
            //             inner join rol_tipo rt on fp.rol_tipo_id = rt.id
            //             left JOIN wf_solicitud_tramite wf on td.id=wf.tramite_detalle_id
            //             where ((wf.id is not null and wf.es_valido is true) or td.id is not null) 
            //             and td.tramite_id = ". $idTramite ."
            //             and td.tramite_estado_id != 3
            //             order by td.id asc
            //             ");

            $query->execute();
            $dato = $query->fetchAll();

            $array = [];
            foreach ($dato as $key => $value) {
                if ($value['datos'] != null) {
                    $dato[$key]['datos'] = json_decode($value['datos'], true);
                }
            }
            // dump($dato);die;
            return $dato;

        } catch (Exception $e) {
            
        }
    }

    // OBTENEMOS DATOS DEL FORMULARIO DE LA UNIDAD EDUCATIVA
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

    public function formularioVistaImprimirLibretaAction(Request $request){
        try {
            // OBTENEMOS LAS VARIABLES
            $idTramite = $request->get('id');
            $em = $this->getDoctrine()->getManager();
            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
            $flujoTipo = $tramite->getFlujoTipo()->getId();

            $sie = $tramite->getInstitucioneducativa()->getId();
            $gestion = $tramite->getGestionId();

            // OBTENEMOS EL LUGAR TIPO
            $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);

            // OBTENEMOS LA TAREA ACTUAL Y SIGUIENTE
            $tarea = $this->get('wftramite')->obtieneTarea($idTramite, 'idtramite');
            $tareaActual = $tarea['tarea_actual'];

            // ENVIAMOS EL TRAMITE
            $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                $this->session->get('userId'),
                $this->session->get('roluser'),
                $flujoTipo,
                $tareaActual,
                'institucioneducativa',
                $sie,
                'Libreta impresa',
                '',
                $idTramite,
                '',
                '',
                $lugarTipo['lugarTipoIdDistrito']
            );

            // CRAEMOS EL MENSAJE DE TRAMITE FINALIZADO
            $request->getSession()
                    ->getFlashBag()
                    ->add('exito', "Tramite Nro. ". $idTramite ." finalizado");

            // OBTENEMOS EL HISTORIAL DEL TRAMITE
            $historial = $this->historial($idTramite);

            foreach ($historial as $h) {
                if($h['orden'] == 1){
                    $idInscripcion = $h['datos']['idInscripcion'];
                    $datosNotas = $h['datos'];
                }
            }

            // CREAMOS LAS VARIABLES PARA LA IMPRESION DE LA LIBRETA
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

            $rude = $inscripcion->getEstudiante()->getcodigoRude();
            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
            $paralelo = $inscripcion->getInstitucioneducativaCurso()->getParaleloTipo()->getId();
            $turno = $inscripcion->getInstitucioneducativaCurso()->getTurnoTipo()->getId();
            $ciclo = $inscripcion->getInstitucioneducativaCurso()->getCicloTipo()->getId();

            return $this->render('SieProcesosBundle:PrevImmediateModiVer2:formularioVistaImprimirLibreta.html.twig', array(
                'historial'=>$historial,
                'datos'=>$datosNotas,
                'idTramite'=>$idTramite,
                'idInscripcion' => $idInscripcion,
                'rude' => $rude,
                'sie' => $sie,
                'gestion' => $gestion,
                'nivel' => $nivel,
                'grado' => $grado,
                'paralelo' => $paralelo,
                'turno' => $turno,
                'ciclo' => $ciclo
            ));
        } catch (Exception $e) {
            
        }
    }

    public function formularioVistaImprimirFinalizarAction(Request $request){
        try {
            // $idTramite = $request->get('idTramite');
            // $em = $this->getDoctrine()->getManager();
            // $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
            // $flujoTipo = $tramite->getFlujoTipo()->getId();
            // $sie = $tramite->getInstitucioneducativa()->getId();
            // $gestion = $tramite->getGestionId();

            // $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);

            // // OBTENEMOS LA TAREA ACTUAL Y SIGUIENTE
            // $tarea = $this->get('wftramite')->obtieneTarea($idTramite, 'idtramite');
            // $tareaActual = $tarea['tarea_actual'];

            // // ENVIAMOS EL TRAMITE
            // $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
            //     $this->session->get('userId'),
            //     $this->session->get('roluser'),
            //     $flujoTipo,
            //     $tareaActual,
            //     'institucioneducativa',
            //     $sie,
            //     'Libreta impresa',
            //     '',
            //     $idTramite,
            //     '',
            //     '',
            //     $lugarTipo['lugarTipoIdDistrito']
            // );

            // $request->getSession()
            //         ->getFlashBag()
            //         ->add('exito', "Tramite Nro. ". $idTramite ." finalizado");

            // return $this->redirectToRoute('wf_tramite_index', array('tipo'=>3));

        } catch (Exception $e) {
            
        }

    }

    /*=========================================================
    =            RECEPCION Y VERIFICACION DESTRITO            =
    =========================================================*/

    public function verificarBimestreAnterior($idTramite){
        $em = $this->getDoctrine()->getManager();
        $historial = $this->historial($idTramite);
        // OBTENEMOS GESTION ACTUAL
        $gestionActual = $this->session->get('currentyear');
        // VERIFICAMOS SI EL TRAMITE ES DE LA GESTION ACTUAL Y BIMESTRE ANTERIOR
        $notasMasDeUnBimestreAtras = 0;
        foreach ($historial as $key => $value) {
            if ($value['orden'] == 1 and $value['datos']['gestion'] == $gestionActual) {

                // RECUPERAMOS EL OPERATIVO DONDE LA AUNIDAD EDUCATIVA INICIO EL TRAMITE
                $datosFormulario = $this->datosFormulario($idTramite);
                $operativo = $datosFormulario['operativoActual'];

                // VERIFICAMOS SI EL OPERATIVO SE ENCUENTRA ENTRE PRIMER Y TERCER BIMESTRE
                if ($operativo > 0 and $operativo < 4) {
                    // RECORREMOS LAS NOTAS PARA VER A QUE BIMESTRE PERTENECEN
                    foreach ($value['datos']['notas'] as $n) {
                        if ($n['idNotaTipo'] == ($operativo - 1)) {
                        }else{
                            // SI EXISTE UNA NOTA QUE PERTENECE A MAS DE UN OPERATIVO ATRAS
                            $notasMasDeUnBimestreAtras++;
                        }
                    }
                }else{
                    // SI EL OPERATIVO ES MAYOR O IGUAL A 4 O CUANDO LA GESTION ACTUAL YA HA SIDO CERRADA
                    $notasMasDeUnBimestreAtras++;
                }

                /*----------  VALIDACION GESTION 2019  ----------*/
                // SI ES GESTION 2019 Y OPERATIVO MENOR A 4TO BIMESTRE RESETEAMOS LA VARIABLE notasMasDeUnBimestreAtras Y
                // CON ESTO HACEMOS QUE LA SOLICITUD SEA APROBADA POR EL DISTRITO DIRECTAMENTE
                // 
                if ($value['datos']['gestion'] == 2019 and ($operativo - 1) < 4) {
                    $notasMasDeUnBimestreAtras = 0;
                }

                /*=====  End of VALIDACION GESTION 2019  ======*/
                

            }else{
                // VERIFICAMOS SI ES DE ORDEN 1 Y GESTION DIFERENTE A LA ACTUAL
                if ($value['orden'] == 1) {
                    $notasMasDeUnBimestreAtras++;
                }
            }
        }

        // VERIFICAMOS SI LA SOLICITUD TIENE NOTAS DE MAS DE UN BIMESTRE ATRAS
        // O EL OPERATIVO ES MAYOR O IGUAL A 4
        // O ES GESTION PASADA
        if ($notasMasDeUnBimestreAtras == 0) {
            $aprobarDistrito = true;
        }else{
            $aprobarDistrito = false;
        }
        return $aprobarDistrito;
    }
    
    /**
     * Recepcion y despliegue del formulario del distrito
     * @param  integer idTramite    id del tramite
     * @return vista                formulario de recepcion UE
     */
    public function recepcionVerificaDistritoAction(Request $request){

        $idTramite = $request->get('id');
        $em = $this->getDoctrine()->getManager();

        $aprobarDistrito = $this->verificarBimestreAnterior($idTramite);
//dump($this->historial($idTramite));die;
        return $this->render('SieProcesosBundle:PrevImmediateModiVer2:formularioVistaUE.html.twig', array(
            'idTramite'=>$idTramite,
            'historial'=>$this->historial($idTramite),
            'aprobarDistrito'=>$aprobarDistrito,
            'urlreporte'=> $this->generateUrl('tramite_add_mod_download_requet', array('idTramite'=>$idTramite))            
        ));
    }

    /**
     * Recepcion y despliegue del formulario del distrito
     * @param  integer idTramite    id del tramite
     * @return vista                formulario de recepcion distrito
     */
    public function recepcionVerificaDistritoNewAction(Request $request){
        $idTramite = $request->get('id');
        $em = $this->getDoctrine()->getManager();

        $aprobarDistrito = $this->verificarBimestreAnterior($idTramite);

        return $this->render('SieProcesosBundle:PrevImmediateModiVer2:formularioVistaDistrito.html.twig', array(
            'idTramite'=>$idTramite,
            'historial'=>$this->historial($idTramite),
            'aprobarDistrito'=>$aprobarDistrito
        ));
    }          
    private function getTramite($tramite_id)
    {
        $em         = $this->getDoctrine()->getManager();
        $db         = $em->getConnection();

        $tramite= null;
        $tramite_id=is_numeric($tramite_id)?$tramite_id:-1;
        $query = '
            select t1.id as tramite_id, t2.id as tramite_detalle_id, t3.id as solicitud_tramite, t3.datos,t1.flujo_tipo_id,t1.tramite_tipo,to_char(t1.fecha_tramite,\'DD/MM/YYYY\') as fecha_tramite
            from tramite t1
            INNER JOIN tramite_detalle t2 on  (t2.id::INT) = (t1.tramite::INT)
            INNER JOIN wf_solicitud_tramite t3 on (t3.tramite_detalle_id::INT)=(t2.tramite_detalle_id::INT)
            --INNER JOIN wf_solicitud_tramite t3 on (t3.tramite_detalle_id::INT)=(t2.id::INT)
            where t1.id=?
            limit 1';
        $stmt = $db->prepare($query);
        $params = array($tramite_id);
        $stmt->execute($params);
        $tramite=$stmt->fetch();
        return $tramite;
    }
    /**
     * derivacion del formulario de distrito
     * @param  Request $request datos formulario distrito
     * @return msg              respuesta en formato json
     */
    public function derivaDistritoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {

            $idTramite = $request->get('idTramite');

            /*----------  VERIFICACION  ----------*/
            // VERIFICAMOS SI EL NUEVO ESTADO ES PROMOVIDO Y POSTERIORMENTE VERIFICAMOS SI EXISTE OTRA INSCRIPCION SIMILAR DEL MISMO NIVEL Y GRADO
            // PARA EVITAR DOBLE PROMOCION
            $respuesta = $this->calcularNuevoEstado($idTramite);
            /*if ($respuesta['nuevoEstado'] == 5) {
                $inscripcionSimilar = $this->get('funciones')->existeInscripcionSimilarAprobado($respuesta['idInscripcion']);
                if ($inscripcionSimilar) {
                    $request->getSession()
                            ->getFlashBag()
                            ->add('errorTAMC', 'El trámite no puede ser aprobado, porque la solicitud modificará el estado de matrícula de la inscripción a promovido, pero el estudiante ya tiene otra inscripción del mismo nivel y grado con estado promovido o efectivo.');
                    return $this->redirectToRoute('previmmediatemodiver2_recepcion_verifica_ueducativa', array('id'=>$idTramite, 'tipo'=>'idtramite'));
                }
            }*/
            $tramite= $this->getTramite($idTramite);
            //dump($tramite);
            //die;
            /*=====  End of VERIFICACION  ======*/

            $procedente = $request->get('procedente');
            $observacion = mb_strtoupper($request->get('observacion'),'UTF-8');
            $checkInforme = $request->get('checkInforme');
            $checkCuaderno = $request->get('checkCuaderno');
            $checkFormulario = $request->get('checkFormulario');
            $finalizar = $request->get('finalizar');

            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
            //dump($tramite);
            
            $flujoTipo = $tramite->getFlujoTipo()->getId();
            $sie = $tramite->getInstitucioneducativa()->getId();
            $gestion = $tramite->getGestionId();

            // OBTENEMOS EL LUGAR TIPO DEL TRAMITE
            $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);

            // OBTENEMOS LA TAREA ACTUAL Y SIGUIENTE
            $tarea = $this->get('wftramite')->obtieneTarea($idTramite, 'idtramite');
           
            $tareaActual = '';
            $tareaSiguienteSi = '';
            $tareaSiguienteNo = '';
            /*foreach ($tarea as $t) {
                $tareaActual = $t['tarea_actual'];
                if ($t['condicion'] == 'SI') {
                    $tareaSiguienteSi = $t['tarea_siguiente'];
                }
                if ($t['condicion'] == 'NO') {
                    $tareaSiguienteNo = $t['tarea_siguiente'];
                }
            }*/
            $tareaActual = $tarea['tarea_actual'];
            $tareaSiguienteSi = $tarea['tarea_siguiente'];
            // VERIFICAMOS SI EXISTE EL INFORME
            if(isset($_FILES['informe'])){
                $file = $_FILES['informe'];

                if ($file['name'] != "") {
                    $type = $file['type'];
                    $size = $file['size'];
                    $tmp_name = $file['tmp_name'];
                    $name = $file['name'];
                    $extension = explode('.', $name);
                    $extension = $extension[count($extension)-1];
                    $new_name = date('YmdHis').'.'.$extension;

                    // GUARDAMOS EL ARCHIVO
                    $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/modificacionNotas/' . $sie;
                    if (!file_exists($directorio)) {
                        mkdir($directorio, 0777, true);
                    }

                    $archivador = $directorio.'/'.$new_name;
                    //unlink($archivador);
                    if(!move_uploaded_file($tmp_name, $archivador)){
                        $response->setStatusCode(500);
                        return $response;
                    }
                    
                    // CREAMOS LOS DATOS DE LA IMAGEN
                    $informe = array(
                        'name' => $name,
                        'type' => $type,
                        'tmp_name' => 'nueva_ruta',
                        'size' => $size,
                        'new_name' => $new_name
                    );
                }else{
                    $informe = null;
                }
            }else{
                $informe = null;
            }

            // CREAMOS EL ARRAY DE DATOS QUE SE GUARDARA EN FORMATO JSON
            $datos = json_encode(array(
                'sie'=>$sie,
                'procedente'=>$procedente,
                'finalizar'=>$finalizar,
                'observacion'=>$observacion,
                'checkInforme'=>($checkInforme == null)?false:true,
                'checkCuaderno'=>($checkCuaderno == null)?false:true,
                'checkFormulario'=>($checkFormulario == null)?false:true,
                'informe'=>$informe
            ), JSON_UNESCAPED_UNICODE);

            // ENVIAMOS EL TRAMITE
            $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                $this->session->get('userId'),
                $this->session->get('roluser'),
                $flujoTipo,
                $tareaActual,
                'institucioneducativa',
                $sie,
                $observacion,
                $procedente,
                $idTramite,
                $datos,
                '',
                $lugarTipo['lugarTipoIdDistrito']
            );

            // VERIFICAMOS SI EL TRAMITE ES PROCEDENTE PARA REGISTRAR LA VERIFICACION DE GESTION Y BIMESTRE
 
            $em->getConnection()->commit();

            $request->getSession()
                    ->getFlashBag()
                    ->add('exito', "El Tramite ". $idTramite ." fue enviado exitosamente");

            return $this->redirectToRoute('wf_tramite_index', array('tipo'=>3));

        } catch (Exception $e) {
            $em->getConnection()->rollback();
        }
    }



    /**
     * derivacion del formulario de distrito
     * @param  Request $request datos formulario distrito
     * @return msg              respuesta en formato json
     */
    public function derivaVerificaDistritofirstAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {

            $idTramite = $request->get('idTramite');

            /*----------  VERIFICACION  ----------*/
            // VERIFICAMOS SI EL NUEVO ESTADO ES PROMOVIDO Y POSTERIORMENTE VERIFICAMOS SI EXISTE OTRA INSCRIPCION SIMILAR DEL MISMO NIVEL Y GRADO
            // PARA EVITAR DOBLE PROMOCION
            $respuesta = $this->calcularNuevoEstado($idTramite);
            if ($respuesta['nuevoEstado'] == 5) {
                $inscripcionSimilar = $this->get('funciones')->existeInscripcionSimilarAprobado($respuesta['idInscripcion']);
                if ($inscripcionSimilar) {
                    $request->getSession()
                            ->getFlashBag()
                            ->add('errorTAMC', 'El trámite no puede ser aprobado, porque la solicitud modificará el estado de matrícula de la inscripción a promovido, pero el estudiante ya tiene otra inscripción del mismo nivel y grado con estado promovido o efectivo.');
                    //return $this->redirectToRoute('tramite_modificacion_calificaciones_recepcion_verifica_departamento', array('id'=>$idTramite, 'tipo'=>'idtramite'));
                    return $this->redirectToRoute('tramite_add_mod_cal_recepcion_verifica_departamento', array('id'=>$idTramite, 'tipo'=>'idtramite'));
                }
            }
            /*=====  End of VERIFICACION  ======*/

            $procedente = $request->get('procedente');
            $observacion = mb_strtoupper($request->get('observacion'),'UTF-8');
            $checkInforme = $request->get('checkInforme');
            $checkCuaderno = $request->get('checkCuaderno');
            $checkFormulario = $request->get('checkFormulario');
            $finalizar = $request->get('finalizar');

            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
            $flujoTipo = $tramite->getFlujoTipo()->getId();
            $sie = $tramite->getInstitucioneducativa()->getId();
            $gestion = $tramite->getGestionId();

            // OBTENEMOS EL LUGAR TIPO DEL TRAMITE
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

            // VERIFICAMOS SI EXISTE EL INFORME
            if(isset($_FILES['informe'])){
                $file = $_FILES['informe'];

                if ($file['name'] != "") {
                    $type = $file['type'];
                    $size = $file['size'];
                    $tmp_name = $file['tmp_name'];
                    $name = $file['name'];
                    $extension = explode('.', $name);
                    $extension = $extension[count($extension)-1];
                    $new_name = date('YmdHis').'.'.$extension;

                    // GUARDAMOS EL ARCHIVO
                    $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/modificacionNotas/' . $sie;
                    if (!file_exists($directorio)) {
                        mkdir($directorio, 0777, true);
                    }

                    $archivador = $directorio.'/'.$new_name;
                    //unlink($archivador);
                    if(!move_uploaded_file($tmp_name, $archivador)){
                        $response->setStatusCode(500);
                        return $response;
                    }
                    
                    // CREAMOS LOS DATOS DE LA IMAGEN
                    $informe = array(
                        'name' => $name,
                        'type' => $type,
                        'tmp_name' => 'nueva_ruta',
                        'size' => $size,
                        'new_name' => $new_name
                    );
                }else{
                    $informe = null;
                }
            }else{
                $informe = null;
            }

            // CREAMOS EL ARRAY DE DATOS QUE SE GUARDARA EN FORMATO JSON
            $datos = json_encode(array(
                'sie'=>$sie,
                'procedente'=>$procedente,
                'finalizar'=>$finalizar,
                'observacion'=>$observacion,
                'checkInforme'=>($checkInforme == null)?false:true,
                'checkCuaderno'=>($checkCuaderno == null)?false:true,
                'checkFormulario'=>($checkFormulario == null)?false:true,
                'informe'=>$informe
            ), JSON_UNESCAPED_UNICODE);

            // ENVIAMOS EL TRAMITE
            $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                $this->session->get('userId'),
                $this->session->get('roluser'),
                $flujoTipo,
                $tareaActual,
                'institucioneducativa',
                $sie,
                $observacion,
                $procedente,
                $idTramite,
                $datos,
                '',
                $lugarTipo['lugarTipoIdDistrito']
            );

            // VERIFICAMOS SI EL TRAMITE ES PROCEDENTE PARA REGISTRAR LA VERIFICACION DE GESTION Y BIMESTRE
            if ($procedente == 'SI') {
                $recibirTramite = $this->get('wftramite')->guardarTramiteRecibido(
                    $this->session->get('userId'),
                    $tareaSiguienteSi,
                    $idTramite
                );
                // VERIFICAMOS SI EL DISTRITO PUEDE APROBAR
                $aprobarDistrito = ($this->verificarBimestreAnterior($idTramite))?'SI':'NO';

                // ARMAMOS EL ARRAY DE DATOS QUE SE GUARDARA EN FORMATO JSON
                $datos = json_encode(array(
                    'sie'=>$sie,
                    'finalizar'=>$finalizar,
                    'observacion'=>$observacion
                ), JSON_UNESCAPED_UNICODE);

                $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                    $this->session->get('userId'),
                    $this->session->get('roluser'),
                    $flujoTipo,
                    $tareaSiguienteSi,
                    'institucioneducativa',
                    $sie,
                    $observacion,
                    $aprobarDistrito,
                    $idTramite,
                    $datos,
                    '',
                    $lugarTipo['lugarTipoIdDistrito']
                );

                /*----------  VERIFICAMOS SI EL DISTRITO APRUEBA LA MODIFICACION  ----------*/

                if ($aprobarDistrito == 'SI') {

                    // OBTENEMOS EL ID DE LA TAREA SIGUIENTE
                    $tarea = $this->get('wftramite')->obtieneTarea($idTramite, 'idtramite');
                    $tareaActual = '';
                    $tareaSiguienteSi = '';
                    foreach ($tarea as $t) {
                        $tareaActual = $t['tarea_actual'];
                        if ($t['condicion'] == 'SI') {
                            $tareaSiguienteSi = $t['tarea_siguiente'];
                        }
                    }

                    // RECIBIMOS EL TRAMITE
                    $recibirTramite = $this->get('wftramite')->guardarTramiteRecibido(
                        $this->session->get('userId'),
                        $tareaSiguienteSi,
                        $idTramite
                    );
                    
                    /*----------  MODIFICAMOS LAS CALIFICACIONES EN EL SISTEMA  ----------*/

                    $this->modificarCalificacionesSIE($idTramite);

                    /*----------  FIN MODIFICACION DE CALIFICACIONES EN EL SIE  ----------*/

                    // ARMAMOS EL ARRAY DE DATOS QUE SE GUARDARA EN FORMATO JSON
                    $datos = json_encode(array(
                        'sie'=>$sie,
                        'aprobarDistrito'=>$aprobarDistrito,
                        'observacion'=>$observacion
                    ), JSON_UNESCAPED_UNICODE);

                    // ENVIAMOS EL TRAMITE
                    $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                        $this->session->get('userId'),
                        $this->session->get('roluser'),
                        $flujoTipo,
                        $tareaSiguienteSi,
                        'institucioneducativa',
                        $sie,
                        $observacion,
                        $aprobarDistrito,
                        $idTramite,
                        $datos,
                        '',
                        $lugarTipo['lugarTipoIdDistrito']
                    );
                }

            }

            // VERIFICAR SI EL TRAMITE NO ES PROCEDENTE PARA REGISTRAR LA TAREA DE OBSERVACION
            if ($procedente == 'NO') {

                $recibirTramite = $this->get('wftramite')->guardarTramiteRecibido(
                    $this->session->get('userId'),
                    $tareaSiguienteNo,
                    $idTramite
                );

                $datos = json_encode(array(
                    'sie'=>$sie,
                    'finalizar'=>$finalizar,
                    'observacion'=>$observacion
                ), JSON_UNESCAPED_UNICODE);

                $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                    $this->session->get('userId'),
                    $this->session->get('roluser'),
                    $flujoTipo,
                    $tareaSiguienteNo,
                    'institucioneducativa',
                    $sie,
                    $observacion,
                    $finalizar,
                    $idTramite,
                    $datos,
                    '',
                    $lugarTipo['lugarTipoIdDistrito']
                );
            }

            $em->getConnection()->commit();

            $request->getSession()
                    ->getFlashBag()
                    ->add('exito', "El Tramite ". $idTramite ." fue enviado exitosamente");

            return $this->redirectToRoute('wf_tramite_index', array('tipo'=>3));

        } catch (Exception $e) {
            $em->getConnection()->rollback();
        }
    }
    public function derivaDepartamentoAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $idTramite = $request->get('idTramite');

            /*----------  VERIFICACION  ----------*/
            // VERIFICAMOS SI EL NUEVO ESTADO ES PROMOVIDO Y POSTERIORMENTE VERIFICAMOS SI EXISTE OTRA INSCRIPCION SIMILAR DEL MISMO NIVEL Y GRADO
            // PARA EVITAR DOBLE PROMOCION
            $respuesta = $this->calcularNuevoEstado($idTramite);
            $inscripcionSimilar = $this->get('funciones')->getcurrentInscriptinoValidation($respuesta['idInscripcion']);
            // dump($inscripcionSimilar);die;
            if(sizeof($inscripcionSimilar)>0){
                
                    $this->get('funciones')->setLogTransaccion(
                        $inscripcionSimilar['inscripcion']['studentid'],
                        'estudiante_inscripcion',
                        'D',
                        '',
                        '',
                        $inscripcionSimilar,
                        'academico',
                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                    );     

                //$this->removeDobleInscription($inscripcionSimilar['inscripcion']['id'],$inscripcionSimilar['inscripcion']['gestion'],$idTramite);
                
            }
            /*=====  End of VERIFICACION  ======*/
            

            $aprueba = $request->get('aprueba');
            $checkInforme = $request->get('checkInforme');
            $checkCuaderno = $request->get('checkCuaderno');
            $checkFormulario = $request->get('checkFormulario');
            $checkInformeDistrito = $request->get('checkInformeDistrito');
            $observacion = mb_strtoupper($request->get('observacion'),'UTF-8');

            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);

            $flujoTipo = $tramite->getFlujoTipo()->getId();
            $sie = $tramite->getInstitucioneducativa()->getId();
            $gestion = $tramite->getGestionId();

            $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);

            // OBTENEMOS LA TAREA ACTUAL Y SIGUIENTE
            $tarea = $this->get('wftramite')->obtieneTarea($idTramite, 'idtramite');
          
            $tareaActual = '';
            $tareaSiguiente = '';

        	$tareaActual = $tarea['tarea_actual'];

            // foreach ($tarea as $t) {
            	
            // 	$tareaActual = $t['tarea_actual'];
            //     if ($t['condicion'] == 'SI') {
            //         $tareaSiguiente = $t['tarea_siguiente'];
            //     }
            // }


            // VERIFICAMOS SI EXISTE EL ARCHIVO DE LA RESOLUCION ADMINISTRATIVA
            if(isset($_FILES['resAdm'])){
                $file = $_FILES['resAdm'];

                if ($file['name'] != "") {
                    $type = $file['type'];
                    $size = $file['size'];
                    $tmp_name = $file['tmp_name'];
                    $name = $file['name'];
                    $extension = explode('.', $name);
                    $extension = $extension[count($extension)-1];
                    $new_name = date('YmdHis').'.'.$extension;

                    // GUARDAMOS EL ARCHIVO
                    $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/modificacionNotas/' . $sie;
                    if (!file_exists($directorio)) {
                        mkdir($directorio, 0777, true);
                    }

                    $archivador = $directorio.'/'.$new_name;
                    //unlink($archivador);
                    if(!move_uploaded_file($tmp_name, $archivador)){
                        $response->setStatusCode(500);
                        return $response;
                    }
                    
                    // CREAMOS LOS DATOS DE LA IMAGEN
                    $resAdm = array(
                        'name' => $name,
                        'type' => $type,
                        'tmp_name' => 'nueva_ruta',
                        'size' => $size,
                        'new_name' => $new_name
                    );
                }else{
                    $resAdm = null;
                }
            }else{
                $resAdm = null;
            }

            // VERIFICAMOS SI EXISTE EL ARCHIVO DEL INFORME
            if(isset($_FILES['informe'])){
                $file = $_FILES['informe'];

                if ($file['name'] != "") {
                    $type = $file['type'];
                    $size = $file['size'];
                    $tmp_name = $file['tmp_name'];
                    $name = $file['name'];
                    $extension = explode('.', $name);
                    $extension = $extension[count($extension)-1];
                    $new_name = date('YmdHis').'.'.$extension;

                    // GUARDAMOS EL ARCHIVO
                    $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/modificacionNotas/' . $sie;
                    if (!file_exists($directorio)) {
                        mkdir($directorio, 0777, true);
                    }

                    $archivador = $directorio.'/'.$new_name;
                    //unlink($archivador);
                    if(!move_uploaded_file($tmp_name, $archivador)){
                        $response->setStatusCode(500);
                        return $response;
                    }
                    
                    // CREAMOS LOS DATOS DE LA IMAGEN
                    $informe = array(
                        'name' => $name,
                        'type' => $type,
                        'tmp_name' => 'nueva_ruta',
                        'size' => $size,
                        'new_name' => $new_name
                    );
                }else{
                    $informe = null;
                }
            }else{
                $informe = null;
            }


                $datos = json_encode(array(
                    'sie'=>$sie,
                    'aprueba'=>$aprueba,
                    'checkInforme'=>($checkInforme == null)?false:true,
                    'checkCuaderno'=>($checkCuaderno == null)?false:true,
                    'checkFormulario'=>($checkFormulario == null)?false:true,
                    'checkInformeDistrito'=>($checkInformeDistrito == null)?false:true,
                    'observacion'=>$observacion,
                    'resAdm'=>$resAdm,
                    'informe'=>$informe,
                    'nroResAdm'=>$request->get('nroResAdm'),
                    'fechaResAdm'=>$request->get('fechaResAdm')
                ), JSON_UNESCAPED_UNICODE);
            
            // dump(
            //     $this->session->get('userId'),
            //     $this->session->get('roluser'),
            //     $flujoTipo,
            //     $tareaActual,
            //     'institucioneducativa',
            //     $sie,
            //     $observacion,
            //     $aprueba,
            //     $idTramite,
            //     $datos,
            //     '',
            //     $lugarTipo['lugarTipoIdDistrito']
            // );die;
            // ENVIAMOS EL TRAMITE
            $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                $this->session->get('userId'),
                $this->session->get('roluser'),
                $flujoTipo,
                $tareaActual,
                'institucioneducativa',
                $sie,
                $observacion,
                $aprueba,
                $idTramite,
                $datos,
                '',
                $lugarTipo['lugarTipoIdDistrito']
            );

            // VERIFICAR SI EL TRAMITE ES APROBADO LO RECEPCIONAMOS PARA REGISTRAR LAS CALIFICACIONES
            if ($aprueba == 'SI') {
                // RECIBIMOS LA SIGUIENTE TAREA
                // $recibirTramite = $this->get('wftramite')->guardarTramiteRecibido(
                //     $this->session->get('userId'),
                //     $tareaSiguiente,
                //     $idTramite
                // );

                /*----------  MODIFICAMOS LAS CALIFICACIONES EN EL SISTEMA  ----------*/

                $this->modificarCalificacionesSIE($idTramite);

                /*----------  FIN MODIFICACION DE CALIFICACIONES EN EL SIE  ----------*/                
                

                // ARMAMOS EL ARRAY DE DATOS
                $datos = json_encode(array(
                    'observacion'=>$observacion
                ), JSON_UNESCAPED_UNICODE);

                // NUEVAMENTE ENVIAMOS EL TRAMITE
                // $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                //     $this->session->get('userId'),
                //     $this->session->get('roluser'),
                //     $flujoTipo,
                //     $tareaSiguiente,
                //     'institucioneducativa',
                //     $sie,
                //     $observacion,
                //     '',
                //     $idTramite,
                //     $datos,
                //     '',
                //     $lugarTipo['lugarTipoIdDistrito']
                // );

                $request->getSession()
                        ->getFlashBag()
                        ->add('exito', "El Tramite ". $idTramite ." fue aprobado y finalizado exitosamente");

            }else{
                $request->getSession()
                    ->getFlashBag()
                    ->add('exito', "El Tramite ". $idTramite ." fue finalizado exitosamente");
            }

            $em->getConnection()->commit();

            // se redireccion a ventana principal
            return $this->redirectToRoute('wf_tramite_index', array('tipo'=>3));

        } catch (Exception $e) {
            $em->getConnection()->rollback();
        }
    }


// remove inscription method
    private function removeDobleInscription($eiid,$gestion,$idTramite){
        // $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {

            $objJuegos = $em->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos')->findOneBy(array('estudianteInscripcion' => $eiid));
            if ($objJuegos) {
                $message = "No se puede eliminar por que el estudiante esta registrado en el sistema de Juegos Plurinacionales";
                
                $request->getSession()
                        ->getFlashBag()
                        ->add($message);
                return $this->redirectToRoute('previmmediatemodiver2_recepcion_verifica_departamento', array('id'=>$idTramite, 'tipo'=>'idtramite'));           
            }

            //verificamos si esta en la tabla de olim_estudiante_inscripcion
            $objolim_estudiante_inscripcion = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->findBy(array('estudianteInscripcion' => $eiid ));
            if ($objolim_estudiante_inscripcion) {
                $message = "No se puede eliminar por que el estudiante esta registrado en el sistema de Olimpiadas";
                $request->getSession()
                        ->getFlashBag()
                        ->add($message);
                return $this->redirectToRoute('previmmediatemodiver2_recepcion_verifica_departamento', array('id'=>$idTramite, 'tipo'=>'idtramite'));
            }

            //get the student's inscription
            $objEstudiantInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eiid);
            //get institucioneducativaCurso info
            $objInsctitucionEducativaCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objEstudiantInscripcion->getInstitucioneducativaCurso()->getId());
            $arrAllowAccessOption = array(7,8);
            if(!in_array($this->session->get('roluser'),$arrAllowAccessOption)){

              $defaultController = new DefaultCont();
              $defaultController->setContainer($this->container);
              //get flag to do the operation
              $swAccess = $defaultController->getAccessMenuCtrl(array('sie'=>$objInsctitucionEducativaCurso->getInstitucioneducativa()->getId(), 'gestion'=>$gestion));
              //validate if the user download the sie file
              if($swAccess){
                $message = ' No se puede realizar la transacción debido a que ya descargo el archivo SIE, esta operación debe realizarlo con el Tec. de Departamento';
                $request->getSession()
                        ->getFlashBag()
                        ->add($message);
                return $this->redirectToRoute('previmmediatemodiver2_recepcion_verifica_departamento', array('id'=>$idTramite, 'tipo'=>'idtramite'));
              }

            }

            //step 1 remove the inscription observado
            $objStudentInscriptionObservados = $em->getRepository('SieAppWebBundle:EstudianteInscripcionObservacion')->findBy(array('estudianteInscripcion' => $eiid));
            //dump($objStudentInscriptionObservados);
            //die;
            if ($objStudentInscriptionObservados){
                foreach ($objStudentInscriptionObservados as $element) {
                    $em->remove($element);
                }
                $em->flush();
                //$em->remove($objStudentInscriptionObservados);
                //$em->flush();
                $obs = $element->getObs();
            }
            else{
                $obs = '';
            }

            $objEstudianteInscripcionCambioestado = $em->getRepository('SieAppWebBundle:EstudianteInscripcionCambioestado')->findBy(array('estudianteInscripcion' => $eiid));
            foreach ($objEstudianteInscripcionCambioestado as $element) {
                $em->remove($element);
            }
            $em->flush();            

//            step 2 delete nota
            $objEstAsig = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('estudianteInscripcion' => $eiid, 'gestionTipo' => $gestion ));

            //dump($objEstAsig);die;
//            foreach ($objEstAsig as $asig) {
//                $objNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura' => $asig->getId()));
//                if ($objNota) {
//
//                }
//            }

            //step 3 delete asignatura
            foreach ($objEstAsig as $element) {
                $objNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura' => $element));
                foreach($objNota as $element2)
                {
                    $em->remove($element2);
                }
                $em->remove($element);
            }
            $em->flush();
          //remove back up data
          // $query = $em->getConnection()->prepare("
          //     DELETE FROM __estudiante_nota_cualitativa_aux WHERE estudiante_inscripcion_id = " . $eiid . "
          // ");
          // $query->execute();


            //dump($objEstAsig);die;
            $objNotaC = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array('estudianteInscripcion' => $eiid));
            foreach ($objNotaC as $element) {
                $em->remove($element);
            }
            $em->flush();

//            array_walk($objEstAsig, array($this, 'deleteEntity'), $em);
            //step 3 delete nota cualitativa
//            $objNotaCualitativa = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array(
//                'estudianteInscripcion' => $eiid
//            ));
//            array_walk($objNotaCualitativa, array($this, 'deleteEntity'), $em);

            //step 4 delete socio economico data
            $objSocioEco = $em->getRepository('SieAppWebBundle:SocioeconomicoRegular')->findBy(array('estudianteInscripcion' => $eiid ));
            //dump($objSocioEco);die;
            foreach ($objSocioEco as $element) {
                $em->remove($element);
            }
            $em->flush();

            //step 5 delete apoderado_inscripcion data
            $objApoIns = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $eiid ));
            //dump($objApoIns);die;

            foreach ($objApoIns as $element) {
                $objApoInsDat = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findBy(array('apoderadoInscripcion' => $element->getId()));
                foreach ($objApoInsDat as $element1){
                    $em->remove($element1);
                }
                $em->remove($element);
            }
            $em->flush();

            //dump($objApoIns);die;
            //remove attached file
            $objStudentInscriptionExtranjero = $em->getRepository('SieAppWebBundle:EstudianteInscripcionExtranjero')->findOneBy(array('estudianteInscripcion'=>$eiid));
            if($objStudentInscriptionExtranjero){
              $em->remove($objStudentInscriptionExtranjero);
              $em->flush();
            }

           //paso 6 borrando apoderados
            $apoderados = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $eiid ));
            foreach ($apoderados as $element) {
                $em->remove($element);
            }
            $em->flush();

             //paso X borrando objHumanistico
            $objHumanistico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findBy(array('estudianteInscripcion' => $eiid ));
            foreach ($objHumanistico as $element) {
                $em->remove($element);
            }
            $em->flush();

            //step 6 copy data to control table and remove teh inscription
            $objStudentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eiid);

            //to remove all info about RUDE
            $objRude = $em->getRepository('SieAppWebBundle:Rude')->findOneBy(array('estudianteInscripcion' => $eiid ));

            if($objRude){

                $objRudeAbandono = $em->getRepository('SieAppWebBundle:RudeAbandono')->findBy(array('rude' => $objRude->getId() ));

                foreach ($objRudeAbandono as $element) {
                    $em->remove($element);
                }
                $em->flush();


                $objRudeAccesoInternet = $em->getRepository('SieAppWebBundle:RudeAccesoInternet')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeAccesoInternet as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeActividad = $em->getRepository('SieAppWebBundle:RudeActividad')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeActividad as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeCentroSalud = $em->getRepository('SieAppWebBundle:RudeCentroSalud')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeCentroSalud as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeDiscapacidadGrado = $em->getRepository('SieAppWebBundle:RudeDiscapacidadGrado')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeDiscapacidadGrado as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeEducacionDiversa = $em->getRepository('SieAppWebBundle:RudeEducacionDiversa')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeEducacionDiversa as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeIdioma = $em->getRepository('SieAppWebBundle:RudeIdioma')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeIdioma as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeMedioTransporte = $em->getRepository('SieAppWebBundle:RudeMedioTransporte')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeMedioTransporte as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeMediosComunicacion = $em->getRepository('SieAppWebBundle:RudeMediosComunicacion')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeMediosComunicacion as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeRecibioPago = $em->getRepository('SieAppWebBundle:RudeRecibioPago')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeRecibioPago as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeServicioBasico = $em->getRepository('SieAppWebBundle:RudeServicioBasico')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeServicioBasico as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeTurnoTrabajo = $em->getRepository('SieAppWebBundle:RudeTurnoTrabajo')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeTurnoTrabajo as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeMesesTrabajados = $em->getRepository('SieAppWebBundle:RudeMesesTrabajados')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeMesesTrabajados as $element) {
                    $em->remove($element);
                }
                $em->flush();

            }


            $objRudeApoderadoInscripcion = $em->getRepository('SieAppWebBundle:RudeApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $eiid ));
            foreach ($objRudeApoderadoInscripcion as $element) {
                $em->remove($element);
            }
            $em->flush();

            $objCdlInscripcion = $em->getRepository('SieAppWebBundle:CdlIntegrantes')->findBy(array('estudianteInscripcion' => $eiid ));
            foreach ($objCdlInscripcion as $element) {
                $em->remove($element);
            }
            $em->flush();            


            if ($objRude) {
                $em->remove($objRude);
            }
            $em->flush();
            
            //eliminados los datos de la tabla bjp_apoderado_inscripcion 
            $bjpApoderadoInscripcion = $em->getRepository('SieAppWebBundle:BjpApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $eiid ));
            foreach ($bjpApoderadoInscripcion as $element)
            {
                $em->remove($element);
            }
            $em->flush();


            //END to remove all info about RUDE

            $studentInscription = new EstudianteInscripcionEliminados();
            $studentInscription->setEstudianteInscripcionId($objStudentInscription->getId());
            $studentInscription->setEstadomatriculaTipoId($objStudentInscription->getEstadoMatriculaTipo()->getId());
            $studentInscription->setEstudianteId($objStudentInscription->getEstudiante()->getId());
            $studentInscription->setNumMatricula($objStudentInscription->getNumMatricula());
            $studentInscription->setObservacionId($objStudentInscription->getObservacionId());
            $studentInscription->setObservacion($objStudentInscription->getObservacion());
            $studentInscription->setFechaInscripcion($objStudentInscription->getFechaInscripcion());
            $studentInscription->setApreciacionFinal($objStudentInscription->getApreciacionFinal());
            $studentInscription->setOperativoId($objStudentInscription->getOperativoId());
            $studentInscription->setFechaRegistro($objStudentInscription->getFechaRegistro());
            $studentInscription->setOrganizacion($objStudentInscription->getOrganizacion());
            $studentInscription->setFacilitadorpermanente($objStudentInscription->getFacilitadorpermanente());
            $studentInscription->setModalidadTipoId($objStudentInscription->getModalidadTipoId());
            $studentInscription->setAcreditacionnivelTipoId($objStudentInscription->getAcreditacionnivelTipoId());
            $studentInscription->setPermanenteprogramaTipoId($objStudentInscription->getPermanenteprogramaTipoId());
            $studentInscription->setFechaInicio($objStudentInscription->getFechaInicio());
            $studentInscription->setFechaFin($objStudentInscription->getFechaFin());
            $studentInscription->setCursonombre($objStudentInscription->getCursonombre());
            $studentInscription->setLugar($objStudentInscription->getLugar());
            $studentInscription->setLugarcurso($objStudentInscription->getLugarcurso());
            $studentInscription->setFacilitadorcurso($objStudentInscription->getFacilitadorcurso());
            $studentInscription->setFechainiciocurso($objStudentInscription->getFechainiciocurso());
            $studentInscription->setFechafincurso($objStudentInscription->getFechafincurso());
            $studentInscription->setCodUeProcedenciaId($objStudentInscription->getCodUeProcedenciaId());
            $studentInscription->setInstitucioneducativaCursoId($objStudentInscription->getInstitucioneducativaCurso()->getId());
            if(($objStudentInscription->getEstadomatriculaInicioTipo()))
              $studentInscription->setEstadomatriculaInicioTipoId($objStudentInscription->getEstadomatriculaInicioTipo()->getId());
            $studentInscription->setObsEliminacion($obs);
            $studentInscription->setUsuarioId($this->session->get('userId'));
            $studentInscription->setFechaEliminacion(new \DateTime('now'));
            $studentInscription->setDoc('false');
            $em->persist($studentInscription);
            $em->flush();

            $em->remove($objStudentInscription);
            $em->flush();

            // Try and commit the transaction
            $em->getConnection()->commit();
            $message = "Proceso realizado exitosamente.";
            $this->addFlash('successremoveins', $message);
            // return $this->redirectToRoute('remove_inscription_sie_index');
            return true;
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $message = "Proceso detenido! Se ha detectado inconsistencia de datos. \n".$ex->getMessage();
            $request->getSession()
                    ->getFlashBag()
                    ->add($message);
            return $this->redirectToRoute('previmmediatemodiver2_recepcion_verifica_departamento', array('id'=>$idTramite, 'tipo'=>'idtramite'));
        }


    }

    
    /*=====  End of RECEPCION Y VERIFICACION DESTRITO  ======*/
    


    /*=============================================================
    =            RECEPCION Y VERIFICACION DEPARTAMENTO            =
    =============================================================*/
    
    public function recepcionVerificaDepartamentoAction(Request $request){
        $idTramite = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);

        $historial = $this->historial($idTramite);

        return $this->render('SieProcesosBundle:PrevImmediateModiVer2:formularioVistaDepartamento.html.twig', array(
            'idTramite'=>$idTramite,
            'historial'=>$historial
        ));
    }

    public function derivaDepartamentocurrentAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $idTramite = $request->get('idTramite');

            /*----------  VERIFICACION  ----------*/
            // VERIFICAMOS SI EL NUEVO ESTADO ES PROMOVIDO Y POSTERIORMENTE VERIFICAMOS SI EXISTE OTRA INSCRIPCION SIMILAR DEL MISMO NIVEL Y GRADO
            // PARA EVITAR DOBLE PROMOCION
            $respuesta = $this->calcularNuevoEstado($idTramite);
            if ($respuesta['nuevoEstado'] == 5) {
                $inscripcionSimilar = $this->get('funciones')->existeInscripcionSimilarAprobado($respuesta['idInscripcion']);
                if ($inscripcionSimilar) {
                    $request->getSession()
                            ->getFlashBag()
                            ->add('errorTAMC', 'El trámite no puede ser aprobado, porque la solicitud modificará el estado de matrícula de la inscripción a promovido, pero el estudiante ya tiene otra inscripción del mismo nivel y grado con estado promovido o efectivo.');
                    return $this->redirectToRoute('previmmediatemodiver2_recepcion_verifica_ueducativa', array('id'=>$idTramite, 'tipo'=>'idtramite'));
                }
            }
            /*=====  End of VERIFICACION  ======*/
            

            $aprueba = $request->get('aprueba');
            $checkInforme = $request->get('checkInforme');
            $checkCuaderno = $request->get('checkCuaderno');
            $checkFormulario = $request->get('checkFormulario');
            $checkInformeDistrito = $request->get('checkInformeDistrito');
            $observacion = mb_strtoupper($request->get('observacion'),'UTF-8');

            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
            $flujoTipo = $tramite->getFlujoTipo()->getId();
            $sie = $tramite->getInstitucioneducativa()->getId();
            $gestion = $tramite->getGestionId();

            $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);

            // OBTENEMOS LA TAREA ACTUAL Y SIGUIENTE
            $tarea = $this->get('wftramite')->obtieneTarea($idTramite, 'idtramite');
            $tareaActual = '';
            $tareaSiguiente = '';
            foreach ($tarea as $t) {
                $tareaActual = $t['tarea_actual'];
                if ($t['condicion'] == 'SI') {
                    $tareaSiguiente = $t['tarea_siguiente'];
                }
            }

            // // OBTENEMOS EL OPERATIVO DE LA INSCRIPCION SI ES GESTION CONSOLIDADA
            // $historial = $this->historial($idTramite);
            // $datosNotas = null;
            // $idInscripcion = null;
            // $sieInscripcion = null;
            // $gestionInscripcion = null;
            // foreach ($historial as $h) {
            //     if($h['orden'] == 1){
            //         $idInscripcion = $h['datos']['idInscripcion'];
            //         $sieInscripcion = $h['datos']['sie'];
            //         $gestionInscripcion = $h['datos']['gestion'];
            //         $datosNotas = $h['datos'];
            //     }
            // }

            // VERIFICAMOS SI EXISTE EL ARCHIVO DE LA RESOLUCION ADMINISTRATIVA
            if(isset($_FILES['resAdm'])){
                $file = $_FILES['resAdm'];

                if ($file['name'] != "") {
                    $type = $file['type'];
                    $size = $file['size'];
                    $tmp_name = $file['tmp_name'];
                    $name = $file['name'];
                    $extension = explode('.', $name);
                    $extension = $extension[count($extension)-1];
                    $new_name = date('YmdHis').'.'.$extension;

                    // GUARDAMOS EL ARCHIVO
                    $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/modificacionNotas/' . $sie;
                    if (!file_exists($directorio)) {
                        mkdir($directorio, 0777, true);
                    }

                    $archivador = $directorio.'/'.$new_name;
                    //unlink($archivador);
                    if(!move_uploaded_file($tmp_name, $archivador)){
                        $response->setStatusCode(500);
                        return $response;
                    }
                    
                    // CREAMOS LOS DATOS DE LA IMAGEN
                    $resAdm = array(
                        'name' => $name,
                        'type' => $type,
                        'tmp_name' => 'nueva_ruta',
                        'size' => $size,
                        'new_name' => $new_name
                    );
                }else{
                    $resAdm = null;
                }
            }else{
                $resAdm = null;
            }

            // VERIFICAMOS SI EXISTE EL ARCHIVO DEL INFORME
            if(isset($_FILES['informe'])){
                $file = $_FILES['informe'];

                if ($file['name'] != "") {
                    $type = $file['type'];
                    $size = $file['size'];
                    $tmp_name = $file['tmp_name'];
                    $name = $file['name'];
                    $extension = explode('.', $name);
                    $extension = $extension[count($extension)-1];
                    $new_name = date('YmdHis').'.'.$extension;

                    // GUARDAMOS EL ARCHIVO
                    $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/modificacionNotas/' . $sie;
                    if (!file_exists($directorio)) {
                        mkdir($directorio, 0777, true);
                    }

                    $archivador = $directorio.'/'.$new_name;
                    //unlink($archivador);
                    if(!move_uploaded_file($tmp_name, $archivador)){
                        $response->setStatusCode(500);
                        return $response;
                    }
                    
                    // CREAMOS LOS DATOS DE LA IMAGEN
                    $informe = array(
                        'name' => $name,
                        'type' => $type,
                        'tmp_name' => 'nueva_ruta',
                        'size' => $size,
                        'new_name' => $new_name
                    );
                }else{
                    $informe = null;
                }
            }else{
                $informe = null;
            }

            // VERIFICAMOS SI LA GESTION ES CONSOLIDADA OPERATIVO >= 4 O LA GESTION PERMITE LA IMPRESION DE LA LIBRETA ELECTRONICA
            // $gestionConsolidada = 'NO';
            // if($gestionInscripcion >= 2015){
            //     $operativo = $this->get('funciones')->obtenerOperativo($sieInscripcion,$gestionInscripcion);
            //     if($operativo >= 4 ){
            //         $gestionConsolidada = 'SI';
            //     }
            // }

            // ARMAMOS EL ARRAY DE LOS DATOS
            // VERIFICAMOS SI EL ESTADO DE MATRICULA ES IGUAL A 4 EFECTIVO PARA NO REGISTRA RESOLUCION ADMINISTRATIVA
            // if ($estadomatricula == 4) {
            //     $datos = json_encode(array(
            //         'sie'=>$sie,
            //         'aprueba'=>$aprueba,
            //         'gestionConsolidada'=>$gestionConsolidada,
            //         'observacion'=>$observacion,
            //         'estadomatricula'=>$estadomatricula,
            //         'resAdm'=>$resAdm,
            //         'nroResAdm'=>'',
            //         'fechaResAdm'=>''
            //     ), JSON_UNESCAPED_UNICODE);
            // }else{
                $datos = json_encode(array(
                    'sie'=>$sie,
                    'aprueba'=>$aprueba,
                    'checkInforme'=>($checkInforme == null)?false:true,
                    'checkCuaderno'=>($checkCuaderno == null)?false:true,
                    'checkFormulario'=>($checkFormulario == null)?false:true,
                    'checkInformeDistrito'=>($checkInformeDistrito == null)?false:true,
                    'observacion'=>$observacion,
                    'resAdm'=>$resAdm,
                    'informe'=>$informe,
                    'nroResAdm'=>$request->get('nroResAdm'),
                    'fechaResAdm'=>$request->get('fechaResAdm')
                ), JSON_UNESCAPED_UNICODE);
            // }
            

            // ENVIAMOS EL TRAMITE
            $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                $this->session->get('userId'),
                $this->session->get('roluser'),
                $flujoTipo,
                $tareaActual,
                'institucioneducativa',
                $sie,
                $observacion,
                $aprueba,
                $idTramite,
                $datos,
                '',
                $lugarTipo['lugarTipoIdDistrito']
            );

            // VERIFICAR SI EL TRAMITE ES APROBADO LO RECEPCIONAMOS PARA REGISTRAR LAS CALIFICACIONES
            if ($aprueba == 'SI') {
                // RECIBIMOS LA SIGUIENTE TAREA
                $recibirTramite = $this->get('wftramite')->guardarTramiteRecibido(
                    $this->session->get('userId'),
                    $tareaSiguiente,
                    $idTramite
                );

                /*----------  MODIFICAMOS LAS CALIFICACIONES EN EL SISTEMA  ----------*/

                $this->modificarCalificacionesSIE($idTramite);

                /*----------  FIN MODIFICACION DE CALIFICACIONES EN EL SIE  ----------*/                
                

                // ARMAMOS EL ARRAY DE DATOS
                $datos = json_encode(array(
                    'observacion'=>$observacion
                ), JSON_UNESCAPED_UNICODE);

                // NUEVAMENTE ENVIAMOS EL TRAMITE
                $enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
                    $this->session->get('userId'),
                    $this->session->get('roluser'),
                    $flujoTipo,
                    $tareaSiguiente,
                    'institucioneducativa',
                    $sie,
                    $observacion,
                    '',
                    $idTramite,
                    $datos,
                    '',
                    $lugarTipo['lugarTipoIdDistrito']
                );

                $request->getSession()
                        ->getFlashBag()
                        ->add('exito', "El Tramite ". $idTramite ." fue aprobado y finalizado exitosamente");

            }else{
                $request->getSession()
                    ->getFlashBag()
                    ->add('exito', "El Tramite ". $idTramite ." fue enviado exitosamente");
            }

            $em->getConnection()->commit();

            return $this->redirectToRoute('wf_tramite_index', array('tipo'=>3));

        } catch (Exception $e) {
            $em->getConnection()->rollback();
        }
    }

    public function actaImprimirAction(Request $request){

        $idTramite = $request->get('idtramite');
        // dump($idTramite);die;
        $idTramiteDetalle = $request->get('id_td');

        $em = $this->getDoctrine()->getManager();
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
                    and fp.orden = 6
                    limit 1
                    ");

        $query->execute();
        $dato = $query->fetchAll();

        $nroResAdm = 'NULL';
        $fechaResAdm = 'NULL';

        if ($dato) {
            $datos = json_decode($dato[0]['datos'], true);
            $nroResAdm = str_replace('/', '_', $datos['nroResAdm']);
            $fechaResAdm = str_replace('-', '', $datos['fechaResAdm']);
        }

        $codigoQR = 'acta_tramite_modificacion_calificaciones|'.$idTramite.'|'.$nroResAdm.'|'.$fechaResAdm;

        return $this->redirectToRoute('download_tramite_modificacion_calificaciones_acta', array('idTramite'=>$idTramite, 'codigoQR'=>$codigoQR));
    }
    
    /*=====  End of RECEPCION Y VERIFICACION DEPARTAMENTO  ======*/

    /*=================================================
    =            FUNCIONES COMPLEMENTARIAS            =
    =================================================*/
    
    /**
     * Funcion para consolidar las calificaciones del tramite a la base de datos
     * @param  integer $idTramite       id del trámite
     * @return boolean                  true cuando la modificacion es exitosa y false si da error
     */
    private function modificarCalificacionesSIE($idTramite){
        $em = $this->getDoctrine()->getManager();
        $historial = $this->historial($idTramite);
        $datosNotas = null;
        $idInscripcion = null;
        $sieInscripcion = null;
        $gestionInscripcion = null;
        foreach ($historial as $h) {
            if($h['orden'] == 1){
                $idInscripcion = $h['datos']['idInscripcion'];
                $sieInscripcion = $h['datos']['sie'];
                $gestionInscripcion = $h['datos']['gestion'];
                $datosNotas = $h['datos'];
            }
        }

        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
        $insGestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
        $insNivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
        $insGrado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
        
        // REGISTRAMOS LAS NOTAS CUANTITATIVAS
        if(count($datosNotas['notas']) > 0){
            foreach ($datosNotas['notas'] as $n) {
                if ($n['idEstudianteNota'] == 'nuevo') {
                    // REGISTRAMOS LA NUEVA CALIFICACION
                    $datoNota = $this->get('notas')->registrarNota($n['idNotaTipo'], $n['idEstudianteAsignatura'],$n['notaNueva'], '');
                }else{
                    // ACTUALIZAMOS LA CALIFICACION
                    $datoNota = $this->get('notas')->modificarNota($n['idEstudianteNota'],$n['notaNueva'], '');
                }

                // CALCULAMOS LOS PROMEDIOS
                switch ($gestionInscripcion) {
                    case 2008:
                    case 2009:
                    case 2010:
                    case 2011:
                    case 2012:  // Notas trimestrales
                                $this->get('notas')->calcularPromediosTrimestrales($datoNota->getEstudianteAsignatura()->getId());
                                break;
                    case 2013:
                                if($insGrado != 1){
                                    $this->get('notas')->calcularPromediosTrimestrales($datoNota->getEstudianteAsignatura()->getId());
                                }else{
                                    $this->get('notas')->calcularPromedioBimestral($datoNota->getEstudianteAsignatura()->getId());
                                }
                                break;
                    case 2020:
                                $this->get('notas')->calcularPromedioTrim2020($datoNota->getEstudianteAsignatura()->getId());                            
                                break;                                
                    case 2021:
                    case 2022:
                                $this->get('notas')->calcularPromedioTrim2020($datoNota->getEstudianteAsignatura()->getId());                            
                                break;                                                                
                    default:
                                $this->get('notas')->calcularPromedioBimestral($datoNota->getEstudianteAsignatura()->getId());
                                break;
                }
            }
        }

        // REGISTRAMOS LAS NOTAS CUALITATIVAS
        if(count($datosNotas['notasCualitativas']) > 0){
            foreach ($datosNotas['notasCualitativas'] as $nc) {
                if ($nc['idEstudianteNotaCualitativa'] == 'nuevo') {
                    // REGISTRAMOS LA NUEVA VALORACION CUALITATIVA
                    $nuevaNotaCualitativa = $this->get('notas')->registrarNotaCualitativa($nc['idNotaTipo'], $nc['idInscripcion'],$nc['notaNuevaCualitativa'], 0);
                }else{
                    // ACTUALIZAMOS LA VALORACION CUALITATIVA
                    $nuevaNotaCualitativa = $this->get('notas')->modificarNotaCualitativa($nc['idEstudianteNotaCualitativa'],$nc['notaNuevaCualitativa'], 0);
                }
            }
        }

        // update the average
        if($insNivel==12){
            $this->get('notas')->updateAveragePrim($idInscripcion);
        }
        // ACTUALIZAMOS EL ESTADO DE MATRICULA
        // $this->get('notas')->actualizarEstadoMatricula($idInscripcion);
        $this->evaluarEstadomatricula($idInscripcion);    

        return true;
    }

    /**
     * Funcion para calcular el nuevo estado de matricula con las notas de la solicitud
     * @param  integer $idTramite     id del tramite
     * @return array                  [idInscripcion: inscripcion del estudiante, nuevoEstado: nuevo estado de matricula]
     */
    public function calcularNuevoEstado($idTramite){
        try {
            $historial = $this->historial($idTramite);
            $datosNotas = null;
            $idInscripcion = null;
            foreach ($historial as $h) {
                if($h['orden'] == 1){
                    $idInscripcion = $h['datos']['idInscripcion'];
                    $datosNotas = $h['datos']['notas'];
                }
            }

            $em = $this->getDoctrine()->getManager();
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
            $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
            
            // OOBTENEMOS EL TIPO DE NOTA BIMESTRE O TRIMESTRE
            $tipo = $this->get('notas')->getTipoNota($sie,$gestion,$nivel,$grado, 'no');
            $array = [];
            $arrayPromedios = [];
            $cont = 0;
            $asignaturas = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('estudianteInscripcion'=>$idInscripcion));
            $notas = [];
            if ($tipo == 'Bimestre') {
                /*----------  NOTAS BIMESTRALES  ----------*/
                $notas = array(1,2,3,4);
                $notaMinima = 51;
            }
            if ($tipo == 'Trimestre') {
                /*----------  NOTAS TRIMESTRALES  ----------*/
                $notas = array(30,27,31,28,32,29,10);
                $notaMinima = 36;
            }

            // RECORREMOS LAS ASIGNATURAS Y VERIFICAMOS LAS CALIFICACIONES
            foreach ($asignaturas as $a) {
                $suma = 0;
                $array[$cont] = array('id'=>$a->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getId(),'asignatura'=>$a->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getAsignatura());                
                // GENERAMOS UN ARRAY CON LAS CALIFICACIONES
                foreach ($notas as $n) {
                    $nota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$a->getId(),'notaTipo'=>$n));
                    if ($nota) {
                        // VERIFICAMOS SI EXISTE UNA NOTA PARA EDITAR EN EL TRAMITE
                        $notaSolicitud = 0;
                        foreach ($datosNotas as $dn) {
                            if ($dn['idEstudianteNota'] == $nota->getId()) {
                                $notaSolicitud = $dn['notaNueva'];
                            }
                        }

                        if ($notaSolicitud == 0) {
                            // VERIFICAMOS SI EXISTE UNA NOTA PARA ADICIONAR
                            foreach ($datosNotas as $dn) {
                                if ($dn['idEstudianteAsignatura'] == $a->getId() and $dn['idNotaTipo'] == $n) {
                                    $notaSolicitud = $dn['notaNueva'];
                                }
                            }
                        }
                    }else{
                        $notaSolicitud = 0;
                        // VERIFICAMOS SI EXISTE UNA NOTA PARA ADICIONAR
                        foreach ($datosNotas as $dn) {
                            if ($dn['idEstudianteAsignatura'] == $a->getId() and $dn['idNotaTipo'] == $n) {
                                $notaSolicitud = $dn['notaNueva'];
                            }
                        }
                    }

                    if ($notaSolicitud != 0) {
                        $array[$cont][$n] = $notaSolicitud;
                    }else{
                        if ($nota) {
                            $array[$cont][$n] = $nota->getNotaCuantitativa();
                        }else{
                            $array[$cont][$n] = 0;
                        }
                    }
                }

                // GENERAMOS EL ARRAY DE PROMEDIOS BIMESTRALES
                if ($tipo == 'Bimestre') {
                    if ($array[$cont]['1'] != 0 and $array[$cont]['2'] != 0 and $array[$cont]['3'] != 0 and $array[$cont]['4'] != 0) {
                        $suma = $array[$cont]['1'] + $array[$cont]['2'] + $array[$cont]['3'] + $array[$cont]['4'];
                        $promedio = round($suma/4);
                        if ($gestion != 2018 or ($gestion == 2018 and $a->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getId() != 1052 and $a->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getId() != 1053)) {
                                $arrayPromedios[] = $promedio;
                        }
                    }
                }

                // GENERAMOS EL ARRAY DE PROMEDIOS TRIMESTRALES
                if ($tipo == 'Trimestre') {
                    $prompt = 0;
                    $promst = 0;
                    $promtt = 0;
                    $promAnual = 0;
                    $promFinal = 0;

                    $prompt = $array[$cont]['30'] + $array[$cont]['27'];
                    $promst = $array[$cont]['31'] + $array[$cont]['28'];
                    $promtt = $array[$cont]['32'] + $array[$cont]['29'];

                    $promAnual = round(($prompt + $promst + $promtt) / 3);
                    if ($promAnual < 36 and $array[$cont]['10'] != 0) {
                        $promFinal = $promAnual + $array[$cont]['10'];
                        $arrayPromedios[] = $promFinal;
                    }else{
                        $arrayPromedios[] = $promAnual;
                    }
                }

                $cont++;
            }

            // VERIFICAMOS LOS PROMEDIOS
            $nuevoEstado = $inscripcion->getEstadomatriculaTipo()->getId();
            if ((count($asignaturas) == count($arrayPromedios)) or ($gestion == 2018 and (count($asignaturas) == count($arrayPromedios) - 2))) {
                $nuevoEstado = 5; // APROBADO
                $contadorReprobados = 0;
                foreach ($arrayPromedios as $ap) {
                    if ($ap < $notaMinima) {
                        $contadorReprobados++;
                    }
                }
                if ($contadorReprobados > 0) {
                    $nuevoEstado == 11; // REPROBADO
                }
            }

            return array(
                'nuevoEstado'=>$nuevoEstado,
                'idInscripcion'=>$idInscripcion
            );

        } catch (Exception $e) {
            
        }
    }

    public function requestInsCalYearOldAction(Request $request, $idTramite){

        $response = new Response();
        $gestion = $this->session->get('currentyear');
        $codigoQR = 'FICGP'.$idTramite.'|'.$gestion;

        $data = $this->session->get('userId').'|'.$gestion.'|'.$idTramite;
        //$link = 'http://'.$_SERVER['SERVER_NAME'].'/sie/'.$this->getLinkEncript($codigoQR);
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'requestProcess'.$idTramite.'_'.$this->session->get('currentyear'). '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') .'reg_est_cert_cal_solicitud_tramite_mod_calif_V2_eea.rptdesign&tramite_id='.$idTramite.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }     

    public function formularioVistaImprimirSolModAntAction(Request $request){

        $response = new Response();
        $idTramite = $request->get('idtramite');
        $gestion = $this->session->get('currentyear');
        $codigoQR = 'FICGP'.$idTramite.'|'.$gestion;

        $data = $this->session->get('userId').'|'.$gestion.'|'.$idTramite;
        //$link = 'http://'.$_SERVER['SERVER_NAME'].'/sie/'.$this->getLinkEncript($codigoQR);
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'requestProcess'.$idTramite.'_'.$this->session->get('currentyear'). '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') .'reg_est_cert_cal_solicitud_tramite_mod_calif_V2_eea.rptdesign&tramite_id='.$idTramite.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }  
    
    /*=====  End of FUNCIONES COMPLEMENTARIAS  ======*/

    private function evaluarEstadomatricula($inscripcionid) {
        $em = $this->getDoctrine()->getManager();            
        
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($inscripcionid);
        
        $igestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
        $iinstitucioneducativa_id = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $inivel_tipo_id = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
        $igrado_tipo_id = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
        $iturno_tipo_id = $inscripcion->getInstitucioneducativaCurso()->getTurnoTipo()->getId();
        $iparalelo_tipo_id = $inscripcion->getInstitucioneducativaCurso()->getParaleloTipo()->getId();
        $icodigo_rude = $inscripcion->getEstudiante()->getCodigoRude();
        $complementario = "";
        $estado_inicial = $inscripcion->getEstadomatriculaTipo()->getEstadomatricula();

        if($igestion >= 2022) {            
            $complementario = "'(6,7)','(6,7,8)','(9)','51'";            
        }

        $query = $em->getConnection()->prepare("select * from sp_genera_evaluacion_estado_estudiante_regular('".$igestion."','".$iinstitucioneducativa_id."','".$inivel_tipo_id."','".$igrado_tipo_id."','".$iturno_tipo_id."','".$iparalelo_tipo_id."','".$icodigo_rude."',".$complementario.")");
        $query->execute();        
        $resultado = $query->fetchAll();

        return $resultado;
    }


}
