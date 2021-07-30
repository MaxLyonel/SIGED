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

class TramiteInscriptionScoreController extends Controller{




    public $session;
    public $idInstitucion;
    public $router;
    /**
     * the class constructor
     */
    public function __construct() {

        $this->session = new Session();
    } 

    public function index1Action(){
        return $this->render('SieProcesosBundle:TramiteInscriptionScore:index.html.twig', array(
                // ...
            ));    
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

        return $this->render('SieProcesosBundle:TramiteInscriptionScore:index.html.twig', array(
            'flujoTipo'=>$flujoTipo,
            'historial'=>$historial,
            'idTramite'=>$idTramite,
            'sieActual'=>$ie_id
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
        if ($rol != 9) {
            $response->setStatusCode(202);
            $response->setData('El usuario actual no tiene rol de Director, cierre sesion e ingrese nuevamente al sistema.');
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$codigoRude));

        // VALIDAMOS QUE EL ESTUDIANTE NO TENGA DOCUMENTOS EMITIDOS
        $documentos = $this->get('funciones')->validarDocumentoEstudiante($codigoRude);
        if (count($documentos) > 0) {
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
        foreach ($inscripciones as $key => $value) {
            $inscripcionesArray[] = array(
                'idInscripcion'=>$value['id'],
                'sie'=>$this->session->get('ie_id'),
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
        }

        // OBTENEMOS EL DATO DEL DIRECTOR
        $user = $this->container->get('security.context')->getToken()->getUser();
        $directorNombre = $user->getPersona()->getNombre().' '.$user->getPersona()->getPaterno().' '.$user->getPersona()->getMaterno();
        $directorCarnet = ($user->getPersona()->getSegipId() == 1)?$user->getPersona()->getCarnet():'';
        $directorComplemento = ($user->getPersona()->getSegipId() == 1)?$user->getPersona()->getComplemento():'';

        $response->setStatusCode(200);
        $response->setData(array(
            'codigoRude'=>$estudiante->getCodigoRude(),
            'estudiante'=> $estudiante->getNombre().' '.$estudiante->getPaterno().' '.$estudiante->getMaterno(),
            'carnet'=>($estudiante->getSegipId() == 1)?$estudiante->getCarnetIdentidad():'',
            'complemento'=>($estudiante->getSegipId() == 1)?$estudiante->getComplemento():'',
            'inscripciones'=>$inscripcionesArray,
            'directorNombre'=>$directorNombre,
            'directorCarnet'=>$directorCarnet,
            'directorComplemento'=>$directorComplemento
        ));

        return $response;
    }

    public function loadLevelAction(Request $request){
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $sieActual = $request->get('sieActual');
        $gestionSelected = $request->get('gestionSelected');

        //get level
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.nivelTipo)')
                ->where('iec.institucioneducativa = :sie')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sieActual)                
                ->setParameter('gestion', $gestionSelected)
                ->distinct()
                ->orderBy('iec.nivelTipo', 'ASC')
                ->getQuery();
        $aqLevel = $query->getResult();

        $arrLevel = array();
        foreach ($aqLevel as $level) {
            if($level[1] != 11){
                $arrLevel[$level[1]] = array('id'=>$level[1], 'level'=>$em->getRepository('SieAppWebBundle:NivelTipo')->find($level[1])->getNivel() );    
            }           
        }

      $arrResponse = array(
        'arrLevel' => $arrLevel
      );

      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
    }

    /**
     * get grado
     * @param type $idnivel
     * @param type $sie
     * return list of grados
     */
    public function loadGradoAction(Request  $request) {
        // ini vars
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        // get the send values
        $sie = $request->get('sieActual');
        $nivelId = $request->get('nivelId');
        $gestionSelected = $request->get('gestionSelected');

        //get grado
        $agrados = array();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.gradoTipo)')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :idnivel')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('idnivel', $nivelId)
                ->setParameter('gestion', $gestionSelected)
                ->distinct()
                ->orderBy('iec.gradoTipo', 'ASC')
                ->getQuery();
        $aGrados = $query->getResult();

        $agrados = array();
        
        foreach ($aGrados as $grado) {
            $agrados[$grado[1]] = array('id'=>$grado[1], 'grado'=>$em->getRepository('SieAppWebBundle:GradoTipo')->find($grado[1])->getGrado() );           
        }
      $arrResponse = array(
        'arrGrado' => $agrados
      );
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;  
    }

    /**
     * get the paralelos
     * @param type $idnivel
     * @param type $sie
     * @return type
     */
    public function loadParaleloAction(Request $request) {
        // ini vars
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        // get the send values
        $sie = $request->get('sieActual');
        $gradoId = $request->get('gradoId');
        $nivelId = $request->get('nivelId');
        $gestionSelected = $request->get('gestionSelected');
        //get paralelo
        $aparalelos = array();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.paraleloTipo)')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivelId)
                ->setParameter('grado', $gradoId)
                ->setParameter('gestion', $gestionSelected)
                ->distinct()
                ->orderBy('iec.paraleloTipo', 'ASC')
                ->getQuery();
        $aParalelos = $query->getResult();

        $aparalelos = array();
        foreach ($aParalelos as $paralelo) {
            $aparalelos[$paralelo[1]] = array('id'=>$paralelo[1], 'paralelo'=>$em->getRepository('SieAppWebBundle:ParaleloTipo')->find($paralelo[1])->getParalelo() );    
        }

      $arrResponse = array(
        'arrParalelo' => $aparalelos
      );
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
    }     
    /**
     * get the turnos
     * @param type $turno
     * @param type $sie
     * @param type $nivel
     * @param type $grado
     * @return type
     */
    public function loadTurnoAction(Request $request) {

        // ini vars
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        // get the send values
        $sie = $request->get('sieActual');
        $gradoId = $request->get('gradoId');
        $nivelId = $request->get('nivelId');
        $paraleloId = $request->get('paraleloId');
        $gestionSelected = $request->get('gestionSelected');
        //get turno
        $aturnos = array();

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.turnoTipo)')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andWhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivelId)
                ->setParameter('grado', $gradoId)
                ->setParameter('paralelo', $paraleloId)
                ->setParameter('gestion', $gestionSelected)
                ->distinct()
                ->orderBy('iec.turnoTipo', 'ASC')
                ->getQuery();
        $aTurnos = $query->getResult();
        
        foreach ($aTurnos as $turno) {
            $aturnos[] =array('id'=>$turno[1], 'turno'=> $em->getRepository('SieAppWebBundle:TurnoTipo')->find($turno[1])->getTurno());
        }

      $arrResponse = array(
        'arrTurno' => $aturnos
      );
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
    }      


    public function buscarCalificacionesAction(Request $request){
        $idInscripcion = -1;
        $idTramite = $request->get('idTramite');
        $flujoTipo = $request->get('flujoTipo');
        
        $nivelId = $request->get('nivelId');
        $gradoId = $request->get('gradoId');
        $paraleloId = $request->get('paraleloId');
        $turnoId = $request->get('turnoId');
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        $codigoRude = $request->get('codigoRude');
        
        $em = $this->getDoctrine()->getManager();

        if ($idTramite == "") {
            $idTramite = 0;
        }
        //get the id course
        $objCourseInfo = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
            'institucioneducativa'=>$sie,
            'nivelTipo'=>$nivelId,
            'gradoTipo'=>$gradoId,
            'paraleloTipo'=>$paraleloId,
            'turnoTipo'=>$turnoId,
            'gestionTipo'=>$gestion,           
        ));

        // VALIDAMOS QUE LA INSCRIPCION NO TENGA UN TRAMITE PENDIENTE
        /*$query = $em->getConnection()->prepare("
                    select *
                    from tramite t
                    inner join tramite_detalle td on td.tramite_id = t.id
                    inner join wf_solicitud_tramite wf on wf.tramite_detalle_id = td.id
                    inner join flujo_proceso fp on td.flujo_proceso_id =fp.id
                    where t.fecha_fin is null
                    and t.id != ". $idTramite ."
                    and wf.datos like '%".$idInscripcion."%'
                    and t.institucioneducativa_id = ". $sie ."
                    and wf.es_valido is true
                    and fp.flujo_tipo_id = ".$flujoTipo);

        $query->execute();
        $tramitePendiente = $query->fetchAll();*/
        $tramitePendiente = array();

        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);

        $operativo = $this->get('funciones')->obtenerOperativo($sie, $gestion);
        $arrDataInfro = array(
                    'sie'=>$sie,
                    'nivelId'=>$nivelId,
                    'gradoId'=>$gradoId,
                    'paraleloId'=>$paraleloId,
                    'turnoId'=>$turnoId,
                    'gestion'=>$gestion,
                    'operativo'=>$operativo-1,
                    'idInscripcion'=>-1,
                    'idCourse'=>$objCourseInfo->getId(),
                );
        $datos = [];
        $promedioGeneral = ''; // PARA PRIMARIA 2019
        //to validate if the student has the choose inscription 
       $inscripciones = $em->createQueryBuilder()
                            ->select('ei.id, ie.id as sie, ie.institucioneducativa, get.gestion, nt.id idNivel, nt.nivel, gt.id idGrado,gt.grado, pt.paralelo, tt.turno, emt.estadomatricula, emt.id estadomatriculaId, dep.departamento, dt.distrito')
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
                            
        $swhasInscription = true;
        while (($inscription = current($inscripciones)) !== FALSE && $swhasInscription) {
            if($inscription['idNivel']==$nivelId && $inscription['idGrado']==$gradoId ){
                $swhasInscription = false;
            }
            next($inscripciones);
        } 
        if(!$swhasInscription){
         $datos =array(
              "cuantitativas" => '',
              "cualitativas" => '',
              "operativo" => 0,
              "operativoTrue" => 0,
              "nivel" => "13",
              "estadoMatricula" => "newsofar",
              "gestionActual" => "-1",
              "idInscripcion" => -1,
              "gestion" => "",
              "grado" => "0",
              "tipoNota" => "",
              "estadosPermitidos" => '',
              "cantidadRegistrados" => 0,
              "cantidadFaltantes" => 0,
              "tipoSubsistema" => "1",
              "titulosNotas" => '',
         );
        
        }else{
            $datos = $this->get('notas')->regularRegularize($arrDataInfro);
        }

        if($datos['gestion'] >= 2019 and $datos['nivel'] == 12){
            foreach ($datos['cualitativas'] as $key => $value) {
                if ($value['idNotaTipo'] == 5) {
                    $promedioGeneral = $value['notaCuantitativa'];
                }
            }
        }
        
//dump($datos);die;
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
            'promedioGeneral'=>$promedioGeneral
        ));

        return $response;

        // return $this->render('SieProcesosBundle:TramiteInscriptionScore:formulario.html.twig', array(
        //     'inscripcion'=>$inscripcion,
        //     'data'=>$data
        // ));
    }

    public function formularioSaveAction(Request $request){

        $response = new JsonResponse();
        try {
            // OBTENEMOS EL ID DE INSCRIPCION
            $idTramite = $request->get('idTramite');
            $idInscripcion = -1 ;//$request->get('idInscripcion');
            
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            // $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            // $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

            $sie = $this->session->get('ie_id');
            $gestion = $this->session->get('currentyear');

            // OBTENEMOS EL ID DEL TRAMITE SI SE TRATA DE UNA MODIFICACION
            $idTramite = $request->get('idTramite');
            // VERIFICAMOS SI EXISTE EL ARCHIVO
            if(isset($_FILES['informe'])){
                $file = $_FILES['informe'];

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
            $dataInscription = json_decode($request->get('dataInscription'),true);
            $justificacion = mb_strtoupper($request->get('justificacion'),'utf-8');
            $checkInforme = $request->get('checkInforme');
            $checkCuaderno = $request->get('checkCuaderno');
            $checkFormulario = $request->get('checkFormulario');

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
                'dataInscription'=>$dataInscription,
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
            $tipoTramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs'=>'IGP'));

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
            
            //$codigoQR = 'FICGP'.$idTramite.'|'.$codigoRude.'|'.$sie.'|'.$gestion;

            $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$codigoRude));

            $sendDataRequest = array(
                'idTramite'=>$idTramite,
                'urlreporte'=> $this->generateUrl('tramite_download_inscriptionScore_yearOld', array('idTramite'=>$idTramite))
            );

            $response->setStatusCode(200);
            $response->setData($sendDataRequest);

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

            return $this->render('SieProcesosBundle:TramiteInscriptionScore:formularioVistaImprimirLibreta.html.twig', array(
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
     * @return vista                formulario de recepcion distrito
     */
    public function recepcionVerificaDistritoAction(Request $request){
        $idTramite = $request->get('id');
        $em = $this->getDoctrine()->getManager();

        $aprobarDistrito = $this->verificarBimestreAnterior($idTramite);

        return $this->render('SieProcesosBundle:TramiteInscriptionScore:formularioVistaDistrito.html.twig', array(
            'idTramite'=>$idTramite,
            'historial'=>$this->historial($idTramite),
            'aprobarDistrito'=>$aprobarDistrito
        ));
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
            //$respuesta = $this->calcularNuevoEstado($idTramite);
            /*if ($respuesta['nuevoEstado'] == 5) {
                $inscripcionSimilar = $this->get('funciones')->existeInscripcionSimilarAprobado($respuesta['idInscripcion']);
                if ($inscripcionSimilar) {
                    $request->getSession()
                            ->getFlashBag()
                            ->add('errorTAMC', 'El trámite no puede ser aprobado, porque la solicitud modificará el estado de matrícula de la inscripción a promovido, pero el estudiante ya tiene otra inscripción del mismo nivel y grado con estado promovido o efectivo.');
                    return $this->redirectToRoute('tramite_modificacion_calificaciones_recepcion_verifica_departamento', array('id'=>$idTramite, 'tipo'=>'idtramite'));
                }
            }*/
            /*=====  End of VERIFICACION  ======*/

            $procedente = $request->get('procedente');
            $observacion = mb_strtoupper($request->get('observacion'),'UTF-8');
            $checkInforme = $request->get('checkInforme');
            //$checkCuaderno = $request->get('checkCuaderno');
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
                $aprobarDistrito = 'NO';
                /*$recibirTramite = $this->get('wftramite')->guardarTramiteRecibido(
                    $this->session->get('userId'),
                    $tareaSiguienteSi,
                    $idTramite
                );*/
                // VERIFICAMOS SI EL DISTRITO PUEDE APROBAR

                // ARMAMOS EL ARRAY DE DATOS QUE SE GUARDARA EN FORMATO JSON
                $datos = json_encode(array(
                    'sie'=>$sie,
                    'finalizar'=>$finalizar,
                    'observacion'=>$observacion
                ), JSON_UNESCAPED_UNICODE);

                /*$enviarTramite = $this->get('wftramite')->guardarTramiteEnviado(
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
                );*/

                /*----------  VERIFICAMOS SI EL DISTRITO APRUEBA LA MODIFICACION  ----------*/


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
    
    /*=====  End of RECEPCION Y VERIFICACION DESTRITO  ======*/
    


    /*=============================================================
    =            RECEPCION Y VERIFICACION DEPARTAMENTO            =
    =============================================================*/
    
    public function recepcionVerificaDepartamentoAction(Request $request){
        
        $idTramite = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);

        $historial = $this->historial($idTramite);

        return $this->render('SieProcesosBundle:TramiteInscriptionScore:formularioVistaDepartamento.html.twig', array(
            'idTramite'=>$idTramite,
            'historial'=>$historial
        ));
    }
    /**
     * to add the areas to the student
     * @param type $studentInscrId
     * @param type $newCursoId
     * @return boolean
     */
    private function addAreasToStudent($studentInscrId, $newCursoId, $gestionIns) {
        $em = $this->getDoctrine()->getManager();
        //put the id seq with the current data
        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');");
        $query->execute();
        $areasEstudiante = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array(
            'estudianteInscripcion' => $studentInscrId,
            'gestionTipo' => $gestionIns
        ));
        //if doesnt have areas we'll fill these
        if (!$areasEstudiante) {
            $objAreas = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array(
                'insitucioneducativaCurso' => $newCursoId
            ));

            foreach ($objAreas as $areas) {
                //print_r($areas->getAsignaturaTipo()->getId());
                $studentAsignatura = new EstudianteAsignatura();
                $studentAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestionIns));
                $studentAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscrId));
                $studentAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($areas->getId()));
                $studentAsignatura->setFechaRegistro(new \DateTime('now'));
                $em->persist($studentAsignatura);
                $em->flush();
                //echo "<hr>";
            }
        }
        return true;
    }    

    public function derivaDepartamentoAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $idTramite = $request->get('idTramite');

            // this is the new
            $historial = $this->historial($idTramite);
            
            $datosNotas = null;
            $idInscripcion = null;
            foreach ($historial as $h) {
                if($h['orden'] == 1){
                    
                    $idInscripcion = $h['datos']['idInscripcion'];
                    $datosNotas = $h['datos']['notas'];
                    $codigoRude = $h['datos']['codigoRude'];
                    $dataInscription = $h['datos']['dataInscription'][0];

                }
            }
            
            //get the data request
            $sie = $dataInscription['sie'];
            $gestionRequest = $dataInscription['gestion'];
            $nivel = $dataInscription['nivelId'];
            $grado = $dataInscription['gradoId'];
            $paralelo = $dataInscription['paraleloId'];
            $turno = $dataInscription['turnoId'];
        
            //check if the student has a the same inscription
             // dump($studentInscription->getId());
             // dump($asignaturas);
             /*dump($datosNotas);
             die;*/

            /*---------- NEED CHECK IT  VERIFICACION  ----------*/
            // VERIFICAMOS SI EL NUEVO ESTADO ES PROMOVIDO Y POSTERIORMENTE VERIFICAMOS SI EXISTE OTRA INSCRIPCION SIMILAR DEL MISMO NIVEL Y GRADO
            // PARA EVITAR DOBLE PROMOCION
            // $respuesta = $this->calcularNuevoEstado($idTramite);
            // if ($respuesta['nuevoEstado'] == 5) {
            //     $inscripcionSimilar = $this->get('funciones')->existeInscripcionSimilarAprobado($respuesta['idInscripcion']);
            //     if ($inscripcionSimilar) {
            //         $request->getSession()
            //                 ->getFlashBag()
            //                 ->add('errorTAMC', 'El trámite no puede ser aprobado, porque la solicitud modificará el estado de matrícula de la inscripción a promovido, pero el estudiante ya tiene otra inscripción del mismo nivel y grado con estado promovido o efectivo.');
            //         return $this->redirectToRoute('tramite_modificacion_calificaciones_recepcion_verifica_departamento', array('id'=>$idTramite, 'tipo'=>'idtramite'));
            //     }
            // }
            /*=====  End of VERIFICACION  ======*/
            

            $aprueba = $request->get('aprueba');
            $checkInforme = $request->get('checkInforme');
            // $checkCuaderno = $request->get('checkCuaderno');
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
                    // 'checkCuaderno'=>($checkCuaderno == null)?false:true,
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

                /*----------  do the new inscription and register the califications  ----------*/

                $arrInforCourse = array(
                     'nivelTipo' => $nivel,
                     'gradoTipo' => $grado,
                     'paraleloTipo' => $paralelo,
                     'turnoTipo' => $turno,
                     'institucioneducativa' => $sie,
                     'gestionTipo' => $gestionRequest
                 );
                $this->setInscriptionAndCalifications($idTramite, $codigoRude);

                /*----------  end do the new inscription and register the califications  ----------*/                
                

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
    private function setInscriptionAndCalifications($idTramite, $codigoRude){
            $em = $this->getDoctrine()->getManager();
            // this is the new
            $historial = $this->historial($idTramite);
            
            $datosNotas = null;
            $idInscripcion = null;
            foreach ($historial as $h) {
                if($h['orden'] == 1){
                    
                    $idInscripcion = $h['datos']['idInscripcion'];
                    $datosNotas = $h['datos']['notas'];
                    $codigoRude = $h['datos']['codigoRude'];
                    $dataInscription = $h['datos']['dataInscription'][0];
                    $datosReqNotas = $h['datos'];

                }
            }
            
            //get the data request
            $sie = $dataInscription['sie'];
            $gestionRequest = $dataInscription['gestion'];
            $nivel = $dataInscription['nivelId'];
            $grado = $dataInscription['gradoId'];
            $paralelo = $dataInscription['paraleloId'];
            $turno = $dataInscription['turnoId'];
            $arrInforCourse = array(
                 'nivelTipo' => $nivel,
                 'gradoTipo' => $grado,
                 'paraleloTipo' => $paralelo,
                 'turnoTipo' => $turno,
                 'institucioneducativa' => $sie,
                 'gestionTipo' => $gestionRequest
             );            

                //no inscription, so do the inscription
                 //get the id of course
                
                 $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy($arrInforCourse);
                 
                 // get info about the student
                 $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $codigoRude ));            
                 // do the new inscription
                 $studentInscription = new EstudianteInscripcion();
                 $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));
                 $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestionRequest));
                 $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
                 $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($student->getId()));
                 $studentInscription->setObservacion('IGP');
                 $studentInscription->setObservacionId(6);
                 $studentInscription->setFechaInscripcion(new \DateTime('now'));
                 $studentInscription->setFechaRegistro(new \DateTime('now'));
                 $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objCurso->getId()));
                 $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(31));
                 $studentInscription->setCodUeProcedenciaId($sie);
                 $studentInscription->setNumMatricula(0);
                 $em->persist($studentInscription);
                 $em->flush();
                 //add the areas to the student
                 $responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $objCurso->getId(), $gestionRequest);    

                // do the registre califications

                 foreach ($datosNotas as $key => $value) {
                    // get the asignaturas ID
                    $asignaturaTipoId = substr_replace($value['idFila'] ,"", -1);
                    $notaTipoId = $value['idNotaTipo'];                    
                    $objAsignaturas = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('estudianteInscripcion'=>$studentInscription->getId()));
                    //dump($objAsignaturas[0]->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getId());
                    $sw = true;
                    //look for the student-califications
                    //dump($asignaturaTipoId);
                    while($sw &&  ($eleAsignatura = current($objAsignaturas))){
                        if($asignaturaTipoId == $eleAsignatura->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getId()){
                            //dump('here the same->'.$eleAsignatura->getId());
                            $objStudentNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array(
                                'estudianteAsignatura'=> $eleAsignatura->getId(),
                                'notaTipo' => $notaTipoId
                            ));
                            if(!$objStudentNota){
                              $sw=false;
                              $insstudentAsignaturaId = $eleAsignatura->getId();
                              $insnotaTipoId = $value['idNotaTipo'];
                              $insNotaNueva = $value['notaNueva'];
                            }
                        }

                        next($objAsignaturas);
                    }
                    // if doesnt exist we put the califications
                    if(!$sw){
                        $datoNota = $this->get('notas')->registrarNota($insnotaTipoId, $insstudentAsignaturaId, $insNotaNueva, '');
                    }
                   // dump($this->get('notas')->calcularPromedioTrim2020($datoNota->getEstudianteAsignatura()->getId())) ;
                    //dump($datoNota);
                    
                    // check the avg
                    switch ($gestionRequest) {
                            case 2008:
                            case 2009:
                            case 2010:
                            case 2011:
                            case 2012:  // Notas trimestrales
                                        $this->get('notas')->calcularPromediosTrimestrales($datoNota->getEstudianteAsignatura()->getId());
                                        break;
                            case 2013:
                                        if($grado != 1){
                                            $this->get('notas')->calcularPromediosTrimestrales($datoNota->getEstudianteAsignatura()->getId());
                                        }else{
                                            $this->get('notas')->calcularPromedioBimestral($datoNota->getEstudianteAsignatura()->getId());
                                        }
                                        break;
                            case 2020:
                                        $this->get('notas')->calcularPromedioTrim2020($datoNota->getEstudianteAsignatura()->getId());                            
                                        break;
                            default:
                                        $this->get('notas')->calcularPromedioBimestral($datoNota->getEstudianteAsignatura()->getId());
                                        break;
                    }

                    
                 }


                // REGISTRAMOS LAS NOTAS CUALITATIVAS
                if(count($datosReqNotas['notasCualitativas']) > 0){
                    foreach ($datosReqNotas['notasCualitativas'] as $nc) {
                        if ($nc['idEstudianteNotaCualitativa'] == 'nuevo') {
                            // REGISTRAMOS LA NUEVA VALORACION CUALITATIVA
                            $nuevaNotaCualitativa = $this->get('notas')->registrarNotaCualitativa($nc['idNotaTipo'], $studentInscription->getId(),$nc['notaNuevaCualitativa'], 0);
                        }else{
                            // ACTUALIZAMOS LA VALORACION CUALITATIVA
                            //$nuevaNotaCualitativa = $this->get('notas')->modificarNotaCualitativa($nc['idEstudianteNotaCualitativa'],$nc['notaNuevaCualitativa'], 0);
                        }
                    }
                }                 
                 
                // ACTUALIZAMOS EL ESTADO DE MATRICULA
                $this->get('notas')->actualizarEstadoMatriculaIGP($studentInscription->getId());                    


        return true;
    }

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

        // ACTUALIZAMOS EL ESTADO DE MATRICULA
        $this->get('notas')->actualizarEstadoMatricula($idInscripcion);

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
                    $dataInscription = $h['datos']['dataInscription'][0];



                }
            }
            $sie = $dataInscription['sie'];
            $gestion = $dataInscription['gestion'];
            $nivel = $dataInscription['nivelId'];
            $grado = $dataInscription['gradoId'];

            $em = $this->getDoctrine()->getManager();
            /*$inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
            $nivel = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
            $grado = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();*/
            
            // OOBTENEMOS EL TIPO DE NOTA BIMESTRE O TRIMESTRE
            $tipo = $this->get('notas')->getTipoNota($sie,$gestion,$nivel,$grado);
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
            //$nuevoEstado = $inscripcion->getEstadomatriculaTipo()->getId();
            $nuevoEstado = 4;
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
                // 'nuevoEstado'=>$nuevoEstado,
                'nuevoEstado'=>5,
                'idInscripcion'=>$idInscripcion
            );

        } catch (Exception $e) {
            
        }
    }


    public function requestInsCalYearOldAction(Request $request,$idTramite){

        $response = new Response();
        $gestion = $this->session->get('currentyear');
        $codigoQR = 'FICGP'.$idTramite.'|'.$gestion;

        $data = $this->session->get('userId').'|'.$gestion.'|'.$idTramite;
        //$link = 'http://'.$_SERVER['SERVER_NAME'].'/sie/'.$this->getLinkEncript($codigoQR);
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'requestProcess'.$idTramite.'_'.$this->session->get('currentyear'). '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') .'reg_est_cert_cal_solicitud_tramite_regu_ins_pas_calif_V2_eea.rptdesign&tramite_id='.$idTramite.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }       
    
    /*=====  End of FUNCIONES COMPLEMENTARIAS  ======*/
    

}

