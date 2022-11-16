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

class TramiteRegularizacionHistorialController extends Controller{

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
        return $this->render('SieProcesosBundle:TramiteRegularizacionHistorial:index.html.twig', array(
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

        return $this->render('SieProcesosBundle:TramiteRegularizacionHistorial:index.html.twig', array(
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
       /* borrar -.- if ($rol != 9) {
            $response->setStatusCode(202);
            $response->setData('El usuario actual no tiene rol de Director, cierre sesion e ingrese nuevamente al sistema.');
            return $response;
        }
        */
        $em = $this->getDoctrine()->getManager();
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$codigoRude));

        // VALIDAMOS QUE EL ESTUDIANTE NO TENGA DOCUMENTOS EMITIDOS
       /* $documentos = $this->get('funciones')->validarDocumentoEstudiante($codigoRude);
        if (count($documentos) > 0) {
            $response->setStatusCode(202);
            $response->setData('El estudiante con el código RUDE '. $codigoRude .' tiene documentos emitidos, por esto no puede realizar la solicitud!');
            return $response;
        }*/
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
                            //dump($inscripciones);die;
        // SI EL ESTUDIANTE NO TIENE INSCRIPCIONES
       /* if(!$inscripciones){
            $response->setStatusCode(202);
            $response->setData('El estudiante no tiene inscripciones registradas en esta Unidad Educativa');
            return $response;
        }*/


        // VALIDAMOS SI LA UNIDAD EDUCATIVA TIENE TUICION SOBRE EL ESTUDIANTE
        // if($inscripciones[0]['sie'] != $sie){
        //     $response->setStatusCode(202);
        //     $response->setData('No tiene tuición sobre el estudiante');
        //     return $response;   
        // }
        /*
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
*/
        /*$queryUbication = "select    
                        d.id as sie,
                        d.institucioneducativa as nombreUE,
                        substring(f.codigo,1,1) as departamento,
                        f.lugar as distrito,
                        e.zona  || ' - ' || e.direccion as direccion,
                        g.obs_cerrada                        
                        from  institucioneducativa d
                        inner join jurisdiccion_geografica e on d.le_juridicciongeografica_id=e.id
                        inner join lugar_tipo f on e.lugar_tipo_id_distrito=f.id
                        INNER JOIN estadoinstitucion_tipo g on     d.estadoinstitucion_tipo_id=g.id
                        where d.id='".$sie."'";
                        

        $query = $em->getConnection()->prepare($queryUbication);        
        $query->execute();
        $dataUbication = $query->fetchAll();*/
        
        $inscripcionesArray[] = array(
                'idInscripcion'=>'',
                'sie'=>$this->session->get('ie_id'),
                'institucioneducativa'=>'',
                'gestion'=>'',
                'nivel'=>'',
                'grado'=>'',
                'paralelo'=>'',
                'turno'=>'',
                'estadomatricula'=>'',
                'estadomatriculaId'=>'',
                'idNivel'=>'',
                'departamento'=>'',
                'distrito'=>''
                // 'ruta'=>$this->generateUrl('tramite_modificacion_calificaciones_formulario', array('flujoTipo'=>$flujoTipo,'idInscripcion'=>$value['id']))
            );        
        //dump($inscripcionesArray);die;
        // OBTENEMOS EL DATO DEL DIRECTOR
       
        $response->setStatusCode(200);
        $response->setData(array(
            'estudianteId'=>$estudiante->getId(),
            'codigoRude'=>$estudiante->getCodigoRude(),
            'estudiante'=> $estudiante->getNombre().' '.$estudiante->getPaterno().' '.$estudiante->getMaterno(),
            'carnet'=>($estudiante->getSegipId() == 1)?$estudiante->getCarnetIdentidad():'',
            'complemento'=>($estudiante->getSegipId() == 1)?$estudiante->getComplemento():'',
            'inscripciones'=>$inscripcionesArray,
       
        ));

        return $response;
    }

    public function loadLevelAction(Request $request){
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $sieActual = $request->get('sieActual');
        $gestionSelected = $request->get('gestionSelected');
        $rude = $request->get('rude');
        $estudianteId = $request->get('estudianteId');
        
       
        //BUSCAMOS SI EL ESTUDIANTE TIENE INSCRIPCION EN ESA GESTION Y UnidadEducativa
        $dato_insc = $em->createQueryBuilder()
        ->select('iec.id')
        ->from('SieAppWebBundle:InstitucioneducativaCurso','iec')
        ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','iec.id = ei.institucioneducativaCurso')
        ->innerJoin('SieAppWebBundle:Estudiante','e','with','e.id = ei.estudiante')
        ->where('e.id = :estudianteId')
        ->andwhere('iec.gestionTipo = :gestion')
        ->setParameter('estudianteId', $estudianteId)
        ->setParameter('gestion', $gestionSelected)
        ->getQuery()
        ->getResult();
           // dump($dato_insc);die;
        if(!$dato_insc){
            $dato_ue = $em->createQueryBuilder()
                ->select(' ie.institucioneducativa, dep.departamento, dt.distrito')
                ->from('SieAppWebBundle:Institucioneducativa','ie')
                ->innerJoin('SieAppWebBundle:JurisdiccionGeografica','jg','with','ie.leJuridicciongeografica = jg.id')
                ->innerJoin('SieAppWebBundle:DistritoTipo','dt','with','jg.distritoTipo = dt.id')
                ->innerJoin('SieAppWebBundle:DepartamentoTipo','dep','with','dt.departamentoTipo = dep.id')
                ->where('ie.id = :sie')
                ->setParameter('sie', $sieActual)
                ->getQuery()
                ->getResult();

            // dump($dato_ue);die;
                //TODO verificar tuicion
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

                //TODO mensaje si no hya resultados
                $arrLevel = array();
                foreach ($aqLevel as $level) {
                    if($level[1] != 11){
                        $arrLevel[$level[1]] = array('id'=>$level[1], 'level'=>$em->getRepository('SieAppWebBundle:NivelTipo')->find($level[1])->getNivel() );    
                    }           
                }

            $arrResponse = array(
                'arrLevel' => $arrLevel,
                'nombreUe' => $dato_ue[0]["institucioneducativa"],
                'departamento' => $dato_ue[0]["departamento"],
                'distrito' => $dato_ue[0]["distrito"],
            );

            $response->setStatusCode(200);
            
        }else{
            $arrResponse = array(
                'mensaje' => 'El/la estudiante ya cuenta con inscripción en la gestión '.$gestionSelected.', no puede aplicar este trámite',
            );

            $response->setStatusCode(202);
        }


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


    public function buscarCalificacionesAction(Request $request){ //dump($request->get('sie'));die;
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
                            ->andwhere('ie.institucioneducativaTipo = :tipoUE')
                            ->andwhere('ei.estadomatriculaTipo = :matriculaId')
                            // ->andWhere('ie.id = :sie')
                            ->setParameter('rude', $codigoRude)
                            ->setParameter('tipoUE', 1)
                            ->setParameter('matriculaId', 5)
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
        $materiasnotas = $this->getAsignaturasPerStudent($sie, $gestion, $nivelId, $gradoId, $paraleloId, $turnoId);
        //dump($materiasnotas ); die;
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
//dump($datos);
//dump($tramitePendiente);
//dump($arrayEstados);
//dump($promedioGeneral);
//die;
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData(array(
            'datos'=>$datos,
            'tramitePendiente'=>$tramitePendiente,
            'estadosMatricula'=>$arrayEstados,
            'promedioGeneral'=>$promedioGeneral,
            'materiasnotas'=>$materiasnotas,
        ));

        return $response;

        // return $this->render('SieProcesosBundle:TramiteRegularizacionHistorial:formulario.html.twig', array(
        //     'inscripcion'=>$inscripcion,
        //     'data'=>$data
        // ));
    }

    public function formularioSaveAction(Request $request){
      /*  dump("aquiii");
        
        dump($request);
        dump("fin"); die;*/
        $response = new JsonResponse();
        try {
            // OBTENEMOS EL ID DE INSCRIPCION
            $idTramite = $request->get('idTramite');
           // $idInscripcion = -1 ;//$request->get('idInscripcion');
            
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

          //  $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion);
            // $sie = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
            // $gestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();

            $sie = $request->get('sie');
            $gestion = $request->get('gestion');

            // OBTENEMOS EL ID DEL TRAMITE SI SE TRATA DE UNA MODIFICACION
            
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
            
            $flujoTipo = $request->get('flujoTipo');
            $notas = json_decode($request->get('notas'),true);
            $dataInscription = json_decode($request->get('dataInscription'),true);

           /* quitar
            $materiasnotas = $this->getAsignaturasPerStudent($dataInscription[0]["sie"], $dataInscription[0]["gestion"], $dataInscription[0]["nivelId"],  $dataInscription[0]["gradoId"], $dataInscription[0]["paraleloId"], $dataInscription[0]["turnoId"]);
            $notas = array();
            foreach ($materiasnotas as $key => $item) {
                //$notas['ieco'] = 
                dump($item);
            }
            die;*/

            $datos = array();
            $datos['institucioneducativa_id'] = $request->get('sie');
            $datos['institucioneducativa'] = $request->get('institucioneducativa');
            $datos['gestion'] = $request->get('gestion');
            $datos['estudiante'] = $request->get('estudiante');
            $datos['carnet'] = $request->get('carnet');
            $datos['departamento'] = $request->get('departamento');
            $datos['distrito'] = $request->get('distrito');
            $datos['complemento'] = $request->get('complemento');
            $datos['inscripcion_nueva'] =$dataInscription;
            $datos['asignaturas_notas'] =  $notas;
            $datos['informe'] =  $informe;
            $datos['flujo_tipo'] =  $flujoTipo;
           
            $tarea = $this->get('wftramite')->obtieneTarea($flujoTipo, 'idflujo');
            
            $tareaActual = $tarea['tarea_actual'];
            $tareaSiguiente = $tarea['tarea_siguiente'];
            $observaciones = 'Llenado del Acta Supletorio';
            // OBTENEMOS EL LUGAR DE LA UNIDAD EDUCATIVA
            $lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);

            // OBTENEMOS EL TIPO DE TRAMITE
            $tipoTramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs'=>'RHA'));

            if ($idTramite == null) {
                
                // OBTENEMOS OPERATIVO ACTUAL Y LO AGREGAMOS AL ARRAY DE DATOS           
               // $data['operativoActual'] = $this->get('funciones')->obtenerOperativo($sieInscripcion,$gestionInscripcion);

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
                    json_encode($datos, JSON_UNESCAPED_UNICODE),
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
                'urlreporte'=> $this->generateUrl('tramite_regularizacion_historial_formulario_imprimir', array('idTramite'=>$idTramite))
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

    public function formularioImprimirAction(Request $requestm, $idTramite){
        $this->generaPdfSolicitud($idTramite);
    }

    public function rptSolicitudRegularizacionHistorialAction(Request $request) {
        $tramite_id = $request->get('idtramite');
        $this->generaPdfSolicitud($tramite_id);
    }

    public function generaPdfSolicitud($tramite_id){

        $pdf = $this->container->get("white_october.tcpdf")->create(
            'PORTRATE', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', true
        );
        $pdf->SetAuthor('Cristina Vallejos');
        $pdf->SetTitle('Acta Supletorio');
        $pdf->SetSubject('Report PDF');
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(true, -10);
        $pdf->SetKeywords('TCPDF, PDF, SOLICITUD REGULARIZACION');
        $pdf->setFontSubsetting(true);
        $pdf->SetMargins(10, 28, 10, true);
        $pdf->SetAutoPageBreak(true, 28);

        $em = $this->getDoctrine()->getManager();
        
        // $tareadetalle_id = $request->get('id_td');
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($tramite_id);
        $institucioneducativa_id = $tramite->getInstitucioneducativa()->getId();
        $gestion_id = $tramite->getGestionId();

        $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'td.flujoProceso = fp.id')
            ->where('td.tramite='.$tramite_id)
            ->andWhere("wfd.esValido=true")
            ->orderBy("td.flujoProceso")
            ->getQuery()
            ->getResult();

        $datos = json_decode($resultDatos[0]->getdatos());
       $fecha = date('Y-m-d H:i:s');
        $pdf->SetFont('helvetica', '', 9, '', true);
        
        $pdf->SetAutoPageBreak(true, 28);
        $pdf->AddPage('P', array(215.9, 274.4));//'P', 'LETTER'
       
        $pdf->Image('images/logo-min-edu.png', 4, 4, 85, 25 ,'', '', '', false, 0, '', false, false, 0);

        $cabecera = '<table border="0">';
        $cabecera .='<tr>';
            $cabecera .='<td width="15%" align="center" style="font-size: 6px"></td>';
            $cabecera .='<td width="70%" align="center"><h2>COMPROBANTE DE SOLICITUD DE </h2><br><h2>REGULARIZACIÓN DE HISTORIAL ACADÉMICO</h2><BR><b><h3>Nro. Trámite: '.$tramite_id.'</h3></b></td>';
        $cabecera .='</tr>';
        
        $cabecera .='<tr>';
            $cabecera .='<td width="50%"><b>Fecha de Trámite: </b>'.$resultDatos[0]->getFechaRegistro()->format('d/m/Y').'</td>';
        
        $cabecera .='</tr>';
        $cabecera .='</table>';
        $pdf->writeHTML($cabecera, true, false, true, false, '');
        $datosTramite='<table border="0" cellpadding="1.8" style="font-size: 8px">'; 
        // Datos del estudiante
        $datosTramite.='<tr style="background-color:#ddd;"><td colspan="4" height="14" style="line-height: 14px;"><b>1. DATOS DEL ESTUDIANTE</b></td></tr>';
        $datosTramite.='<tr><td><b>Código RUDE:</b></td><td colspan="3">'.$datos->inscripcion_nueva[0]->codigoRude.'</td></tr>';
        $datosTramite.='<tr><td><b>Nombre:</b></td><td colspan="3">'.$datos->estudiante.'</td></tr>';
        $datosTramite.='<tr><td><b>Cédula de Identidad:</b></td><td>'.$datos->carnet.' </td></tr>';
        // Datos de la unidad educativa
        $datosTramite.='<tr style="background-color:#ddd;"><td colspan="4" height="14" style="line-height: 14px;"><b>2. DATOS DE LA INSCRIPCIÓN A REGULARIZAR </b></td></tr>';
        $datosTramite.='<tr><td  width="80"><b>Unidad Educativa:</b></td><td colspan="3">'.$datos->institucioneducativa_id.' - '.$datos->institucioneducativa.'</td></tr>';
        // Localtizacion
        $datosTramite.='<tr><td width="80"><b>Gestión:</b></td><td width="50">'.$datos->inscripcion_nueva[0]->gestion.'</td><td width="80"><b>Departamento:</b></td><td width="50">'.$datos->departamento.'</td><td width="50"><b>Distrito:</b></td><td width="50">'.$datos->distrito.'</td></tr>';
        $datosTramite.='<tr><td width="80"><b>Nivel:</b></td><td width="140">'.$datos->inscripcion_nueva[0]->nivel.'</td>
                            <td width="50"><b>Grado:</b></td><td width="50">'.$datos->inscripcion_nueva[0]->grado.'</td>
                            <td width="50"><b>Paralelo:</b></td><td width="50">'.$datos->inscripcion_nueva[0]->paralelo.'</td>
                            <td width="50"><b>Turno:</b></td><td width="50">'.$datos->inscripcion_nueva[0]->turno.'</td>
                            </tr>';
        
        $datosTramite.='</table><br>';
        
        $datosTramite.='<table>
        <tr style="background-color:#ddd;"><td colspan="4" height="14" style="line-height: 14px;"><b>3. REGISTRO DE CALIFICACIONES A REGULARIZAR</b></td></tr>
        </table><br><br>';
        $pdf->writeHTML($datosTramite, false, false, true, false, '');
        
        if ($datos->asignaturas_notas) {
            $actaSupletorio='<table><tr><td width="100"></td><td>';

            $actaSupletorio.='<table border="0.5" cellpadding="2" style="font-size: 8px">
                            <tr style="background-color:#ddd;">
                            <td align="center" width="200"><b>ASIGNATURA</b></td>
                            <td align="center" width="100"><b>PROMEDIO ANUAL</b></td>
                            </tr>';
              foreach ($datos->asignaturas_notas as $item ) {
                $actaSupletorio.='<tr>';
                    $actaSupletorio.='<td align="left" width="200">'.$item->asignatura.'</td>';
                    $actaSupletorio.='<td align="center" width="100">'.$item->nota.'</td>';
                $actaSupletorio.='</tr>';
            }
            $actaSupletorio.='</table>
            </td>
            </tr>
            </table>
            ';
            $pdf->writeHTML($actaSupletorio, true, false, true, false, '');
        } else {
            $pdf->SetAutoPageBreak(true, 10);
        } 
       
        $firmas='<table cellpadding="0.5" style="font-size: 8px;">';
        $firmas.='<tr>
        <td align="center" width="40%">
        <br/><br/><br/><br/><br/><br/><br/><br/><br/>_____________________________<br/>Registrado Por<br/>Dirección Distrital</td>
        </tr>
        <tr>
       
        <td align="center" width="60%"><br/><br/>
        <font size="6"><p align="justify">Este documento debe ser presentado para efectos de revisión del trámite.

            </p> </font>
        </td></tr>
        ';
        
        $firmas.='</table>';
        $pdf->writeHTML($firmas, true, false, true, false, '');
        
        $pdf->Output("SolicitudRha_".date('YmdHis').".pdf", 'I');
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

            return $this->render('SieProcesosBundle:TramiteRegularizacionHistorial:formularioVistaImprimirLibreta.html.twig', array(
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

        return $this->render('SieProcesosBundle:TramiteRegularizacionHistorial:formularioVistaDistrito.html.twig', array(
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
        //dump($historial);
        //dump($historial[0]["datos"][0]["sie"]);
        //die;
        return $this->render('SieProcesosBundle:TramiteRegularizacionHistorial:formularioVistaDepartamento.html.twig', array(
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
    public function derivaDepartamentoAntes0Action(Request $request){

        $em = $this->getDoctrine()->getManager();
       // $em->getConnection()->beginTransaction();

        $idTramite = $request->get('idTramite');
        // this is the new
        $historial = $this->historial($idTramite);
        //dump($historial);die();

        $datosNotas = null;
        $idInscripcion = null;
        foreach ($historial as $h) {
            if($h['orden'] == 1){
                
                $datosNotas = $h['datos']['asignaturas_notas'];
                $codigoRude = $h['datos']['inscripcion_nueva'][0]['codigoRude'];
                $dataInscription = $h['datos']['inscripcion_nueva'][0];

            }
        }
        
        //get the data request
        $sie = $dataInscription['sie'];
        $gestionRequest = $dataInscription['gestion'];
        $nivel = $dataInscription['nivelId'];
        $grado = $dataInscription['gradoId'];
        $paralelo = $dataInscription['paraleloId'];
        $turno = $dataInscription['turnoId'];

        $aprueba = $request->get('aprueba');
        $observacion = mb_strtoupper($request->get('observacion'),'UTF-8');

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
        $flujoTipo = $tramite->getFlujoTipo()->getId();
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
            $datos = json_encode(array(
                'sie'=>$sie,
                'aprueba'=>$aprueba,
                'observacion'=>$observacion,
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
            $aprueba,
            $idTramite,
            $datos,
            '',
            $lugarTipo['lugarTipoIdDistrito']
        );
        //$em->getConnection()->commit();

        return $this->redirectToRoute('wf_tramite_index', array('tipo'=>3));

    }


    public function derivaDepartamentoAction(Request $request){
        
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $idTramite = $request->get('idTramite');
            // this is the new
            $historial = $this->historial($idTramite);
            //dump($historial);die();

            $datosNotas = null;
            $idInscripcion = null;
            foreach ($historial as $h) {
                if($h['orden'] == 1){
                    
                    $datosNotas = $h['datos']['asignaturas_notas'];
                    $codigoRude = $h['datos']['inscripcion_nueva'][0]['codigoRude'];
                    $dataInscription = $h['datos']['inscripcion_nueva'][0];

                }
            }
            
            //get the data request
            $sie = $dataInscription['sie'];
            $gestionRequest = $dataInscription['gestion'];
            $nivel = $dataInscription['nivelId'];
            $grado = $dataInscription['gradoId'];
            $paralelo = $dataInscription['paraleloId'];
            $turno = $dataInscription['turnoId'];

            $aprueba = $request->get('aprueba');
            $observacion = mb_strtoupper($request->get('observacion'),'UTF-8');

            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
            $flujoTipo = $tramite->getFlujoTipo()->getId();
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
                    if ($t['condicion'] == 'NO') {
                        $tareaSiguienteNo = $t['tarea_siguiente'];
                    }
                }
                $datos = json_encode(array(
                    'sie'=>$sie,
                    'aprueba'=>$aprueba,
                    'observacion'=>$observacion,
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
                
                $arrInforCourse = array(
                     'nivelTipo' => $nivel,
                     'gradoTipo' => $grado,
                     'paraleloTipo' => $paralelo,
                     'turnoTipo' => $turno,
                     'institucioneducativa' => $sie,
                     'gestionTipo' => $gestionRequest
                 );
              
                 // TODO revision
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

                $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 3));
                $tarea_sig = $flujoproceso->getId();
                $mensaje = $this->get('wftramite')->guardarTramiteRecibido($this->session->get('userId'), $tareaSiguienteNo, $idTramite);
                if ($mensaje['dato'] == true) {
                    $msg = $mensaje['msg'];
                    $observaciones = 'Observación historial del trámite INC';
                    $evaluacion = "NO";
                    $resultobs  = $this->get('wftramite')->guardarTramiteEnviado(
                        $this->session->get('userId'),
                        $this->session->get('roluser'),
                        $flujoTipo,
                        $tareaSiguienteNo,
                        'institucioneducativa',
                        $sie,
                        $observacion,
                        $aprueba,
                        $idTramite,
                        $datos,
                        '',
                        $lugarTipo['lugarTipoIdDistrito']
                    );
                    if ($resultobs['dato'] == true) {
                        $msg = $resultobs['msg'];
                    }
                } 
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
    public function setInscriptionAndCalifications($idTramite, $codigoRude){
        
            $em = $this->getDoctrine()->getManager();
            // this is the new
            $historial = $this->historial($idTramite);
            $nota_tipo = 9;

            $datosNotas = null;
            $idInscripcion = null;
            foreach ($historial as $h) {
                if($h['orden'] == 1){
                    $datosNotas = $h['datos']['asignaturas_notas'];
                    $codigoRude = $h['datos']['inscripcion_nueva'][0]['codigoRude'];
                    $dataInscription = $h['datos']['inscripcion_nueva'][0];
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

            if($gestionRequest==2013 && $grado == 1)
                $nota_tipo = 5;

            if (in_array($gestionRequest, [2014, 2015,2016, 2017, 2018,2019])) {
                $nota_tipo = 5;
            }
            //get the id of course
                $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy($arrInforCourse);
                 
            // get info about the student
                $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $codigoRude ));      
                
                 // do the new inscription
                 $studentInscription = new EstudianteInscripcion();
                 $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(104));
                 $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($student->getId()));
                 $studentInscription->setObservacion('RHA');
                 $studentInscription->setObservacionId(6);
                 $studentInscription->setFechaInscripcion(new \DateTime('now'));
                 $studentInscription->setFechaRegistro(new \DateTime('now'));
                 $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objCurso->getId()));
                 $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(103));
                 $studentInscription->setCodUeProcedenciaId($sie);
                 $studentInscription->setNumMatricula(0);
                 $em->persist($studentInscription);
                 
                 //add the areas to the student
                 //dump($datosNotas);die;
                 
                //Registra las asignaturas y notas
                foreach ($datosNotas as $key => $value) {
                    // Registra estudiante_asignatura en caso de no tener de aceleración
                    $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array('estudianteInscripcion'=>$studentInscription, 'institucioneducativaCursoOferta'=>$value['iecoId'], 'gestionTipo'=>$gestionRequest));
                    if (empty($estudianteAsignatura)) {
                        $estudianteAsignatura = new EstudianteAsignatura();
                        $estudianteAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestionRequest));
                        $estudianteAsignatura->setFechaRegistro(new \DateTime('now'));
                        $estudianteAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription));
                        $estudianteAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($value['iecoId']));
                        $em->persist($estudianteAsignatura);
                    }
                    // Registra las notas con NotaTipo = 5 "Promedio Final" de aceleración
                    $estudianteNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('notaTipo'=>$nota_tipo, 'estudianteAsignatura'=>$estudianteAsignatura));
                    if (empty($estudianteNota)) {
                        $estudianteNota = new EstudianteNota();
                        $estudianteNota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($nota_tipo));
                        $estudianteNota->setEstudianteAsignatura($estudianteAsignatura);
                        $estudianteNota->setNotaCuantitativa($value['nota']);
                        $estudianteNota->setNotaCualitativa('');
                        $estudianteNota->setRecomendacion('');
                        $estudianteNota->setUsuarioId($this->session->get('userId'));
                        $estudianteNota->setFechaRegistro(new \DateTime('now'));
                        $estudianteNota->setFechaModificacion(new \DateTime('now'));
                        $estudianteNota->setObs('');
                        $em->persist($estudianteNota);
                    }
                }
                $em->flush();
              
        return true;
    }

    public function rptCertificadoNotasAction(Request $request) {
        $pdf = $this->container->get("white_october.tcpdf")->create(
            'PORTRATE', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', true
        );
        $pdf->SetAuthor('Cristina Vallejos');
        $pdf->SetTitle('Acta Supletorio');
        $pdf->SetSubject('Report PDF');
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(true, -10);
        $pdf->SetKeywords('TCPDF, PDF, ACTA SUPLETORIO');
        $pdf->setFontSubsetting(true);
        $pdf->SetMargins(10, 28, 10, true);
        $pdf->SetAutoPageBreak(true, 28);

        $em = $this->getDoctrine()->getManager();
        $tramite_id = $request->get('idtramite');//1670899;
        // $tareadetalle_id = $request->get('id_td');
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($tramite_id);
        $institucioneducativa_id = $tramite->getInstitucioneducativa()->getId();
        $gestion_id = $tramite->getGestionId();

        $resultDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
            ->select('wfd')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'td.flujoProceso = fp.id')
            ->where('td.tramite='.$tramite_id)
            ->andWhere("wfd.esValido=true")
            ->orderBy("td.flujoProceso")
            ->getQuery()
            ->getResult();

        $datos = json_decode($resultDatos[0]->getdatos());
       // $datos2 = json_decode($resultDatos[1]->getdatos());
        /*dump($datos);
        dump($datos->institucioneducativa);
        foreach($datos->asignaturas_notas as $item){
            dump($item->asignatura);
        }
        dump($datos->inscripcion_nueva[0]->sie);
        die;*/
        $pdf->SetFont('helvetica', '', 9, '', true);
        
        $pdf->SetAutoPageBreak(true, 28);
        $pdf->AddPage('P', array(215.9, 274.4));//'P', 'LETTER'
        //$image_path = base64_decode('/9j/4AAQSkZJRgABAQEBLAEsAAD/2wBDAAoHBwgHBgoICAgLCgoLDhgQDg0NDh0VFhEYIx8lJCIfIiEmKzcvJik0KSEiMEExNDk7Pj4+JS5ESUM8SDc9Pjv/2wBDAQoLCw4NDhwQEBw7KCIoOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozv/wAARCADoASwDASIAAhEBAxEB/8QAHAABAAIDAQEBAAAAAAAAAAAAAAUGAwQHAQII/8QAQRAAAQMDAwMCAwYDBwEIAwAAAQIDBAAFEQYSIRMxQVFhFCJxBxUygZGhQrHBFiMkM1LR8JIXNkRidIKywnLh8f/EABoBAQACAwEAAAAAAAAAAAAAAAABAgMEBQb/xAAwEQACAQIEBAQFBQEBAAAAAAAAAQIDEQQSITFBUWHwE3Gh4QUUIoHRIzKRsfHBBv/aAAwDAQACEQMRAD8A7NSlKAUpSgFKUoBSlKAUpSgFKUoBSlKAUrQud5iWpAVJUoZ7YSf59hVfH2l2HfsWXmlg4UlwJBH7/wDM1ZRb2KucVuy0S5keCyXpLqWmx3Uo/wDPSsjLoeZQ6EqSFpCgFDBH1HiudSNWQdSamgtB9lq3xHuorrOBGVAZBPPPnH1FdFacbdbC21pWhXIUk5Bqmt9USpJ7H3SvlSkpGVEAe9Vqf9oml7c6pp66tFxJwUoyvB/LNSSWJclht1LS3kJWoEpSVYJAx/uP1rLXJ7xrDR13vkOc7OdSmO4FLQlpSg5tBx4GO/PqKvNs1nZLslJiySQo4+ZBHPpRKT4EXRP0r4adbeQFtLStJ8pOa+6EilKUApSlAKUpQClKUApSlAKUpQClKUApSlAKUpQClKUApSlAKUpQCvDwKEgd6d6AxR5TMpsraWFYJSoeUkdwR4NZVEgZAyfSqrqhL9qkovER1KEIwuQ0kkLdCfAx3BHfOcY45qZsNzavFmjTmnUuB1GSUn8J8j6jtUX4E2NvqsvNBSgClR2kKHY9sH8+KrWovs9s1/SpZZEeQRw63wc/89c1KXXNvWqcEFyM4NstoDOR23geo7H1GPQV92u4pcV8G48l1ezqMug5D7R7KB8kZAP6+atqtiuj3Pz7f9K3LT01/pufEssObFPNZBbPjcO6fY9j4NbWmte3KxPcvOqbPdJVkH6g1dpL0uxa0W/MbceZdcKnFJcSlDiQFJSCSQM4I4PkGp/UOkrHqaxLWm2pjT+mVJLaB1EKHhRTnP059qpTq33KuF1ciNY/aDGnaA+Ltbym3pa+gpOcKaPdX7efeuUWCwzNQ3BuLFCdy1bSVHAr5ur6EIbtccOJYiqVu6qdq1uHhSiPHYDHjH1rof2SWe3uvfeC5jSnGsnpKcSFBQ87c5xjycVk0DuZHdBsaShonTYiblnlSE5BScjjI9Rn9K6baIcNi3sSW4SYq1tBSgo5UgEZwSeaqerNSPJucdi1v9YvhAS0OUlSXMnI8cD9DVndmJnSVRt6URooCpiyrCc4zsz6eT7cetYoTc5PXQJJEkXmW2eucJCsc45Pp+tZkklIJGCRyPSom3uqu8gXA5ERGRGQf4/VZH8h4589pVa0toK1KCUpGSScACrsugtaW0Fa1BKUjJJOAK+WXkPtJdbJKFcg4xketUm1XGRq25oWZDKrcwQ042pf+cpOSVJT6H5Qcjt271eQMCoTuD2leZr2pApSlAKUpQClKUApSlAKUpQClKUApSlAKUpQClKUArwqCcZIGeBXtY32GpLKmXkBbaxhSVDg0B9LQlxCkLSFJUMEHzVZm3G5admtoWHJNr3BSnloK1NJORtyCCSFYAyCSFD0Oda5XC76YeU64t2VbEA4UtQUvn8IHG4lJznPjHnip2K/atQQmpSEx5aSnjKQrbkcgg9qq2Tbiasy+MOaMk3dDiFI+FWoFJyM4IA/WuV6G1VKsT6WSCuOtCFKQT+IFI5Hoe9aKFzExJloW+tMN4kEIPBweOPrSEygxUNp5cYGwnGM44zWjUr31W6O7hMFZrPtJdo7pEmRbtBDzCw6y6kg/wBQRXE37lc7VscZWrpxty0lLqk9EnOdvoOTV90S4uBA3OHl9W7b4x2FQD+mbow++XIokwGnMoWDlTwJ+VO0ZI8ZPgZ71jhjY1/pi9Vuv+mOjTo4epUUvsyRvqHb9oy3XlUR5mT00LLqhtWCD+Igc4VjIxzyKr1o1hNtdwL89t6U6G1pZAWkJ3Yx8xxkjGO58g1ZYV8dZL8h25KcfCk5a6m5DJA5QMdx3z68c8ZqJ1OtiXdBJbtiYawgKUQlIKyccnA59Oa28PFYmo4w0a5nCxVX5aOaXHgjn39mrzckv3Eso6a3VEuKcSAo91Y57DP71DKbcjuJO4pWPQ8prp6rhHVa0t9UqkpdyQclITjH09K3bO/GLbkm422HOaiqSrLzCVODJwMKxn8jxW54M1GUpOyTt99tOjb0NVY2Dko2eqv9upt/ZIq/uQZMq5ISLe4nLL72eoogDsf9GB+vbzWFDqlaVh21tTr3xjjZexkKfcK0rUkZ7jAJUScc+9Slxv6LlDfWmaqC0CAgcANpBH4hyD2wR6HzivuwW3464OXZTPTnNLzl1WW+mUlIKCB2ICTgcCtWnXhmcLWsb0oPKpJ7ltgSm0x+iWy0IzadxWQMDH19q53r7WypMdyBbiegr5SocF0/7VZ7ujqWqTGDu56QMqWBjcR2H0/3rmLqEJSp58fh8Edq0njoVpOFPVL1Ox8PwsZRdSW69Op0r7MZyZGkuj5iSXWj/wBRV/8AavprVU68SWo1pZBSoBL0gNlaWln3yBgDJzgg4x3rlcaRPtlqkxob6/8AGr6jjf8ADnnA/eut6MgwYWlIMhTbTaktlSnSAn+I9zW5TqKX0o08ThpUbN8SwxYyYrIbStbh7qccOVLPqTWbIzjPNU1ep596nGNp9BcZbUpDz4CcDJwgjPjuTxnBGKtMGEiEzt3rdcVy464cqWfUn+nis6dzTasbVKUqSBSlKAUpSgFKUoBSlKAUpSgFKUoBSlKAUpSgFKUoCM1A2g2G4LKElQiOgEjkfKa4hbH5tnX8RbJC0lI5CDzXZNbzU2/Rt0fWf/DqSPqr5R+5rjDClQnw6EhxCudu7AP51pYltNWO18KgpKo7X0MkGal9xTawAFcpPrxVr0pGZLkgqjZR0wh0KAwslROf2qmsORJ6wI6VMqCRjdjv5x7V0LQyv8DIS8nDnUCSo9lAD/8Atcf4g8tFtaM35u+DjLiicQwx8MhpsbUNjCcd0j3qB1NeFWaI2l9r4mO+4EuN7sbk98g+COMGrDIY6KVPNr2BIJOe2K5HKuMq7PiK6tam1PqWhs87dxycVp/CMNOtXz8t+tzzOPxSowst36E0uAw6mRcY0pLiFBKtqiQspOMHb688+/ap+zaak3qIzLuL2WEjDDKjjI9T7elQ0cSbiuJbFNpCEnj5MK29+9XgQ5DTYQlwDanhAPIFemxeMeBy0XC7V2rcFsteuv8ARp4OjHFyddyetl999unufI0vHQnaFNgelR9y0c2qK4qI8lDmM7ArhZHtX2q5RUzExFTU9ZYyBng/n2rdEZ485Ua0Jf8AoJ6fps6L+HUZJrMUBVmffih9wdFhLgBUpWAeccj0qbsF+jvz3rXbt62wgKW+on++UDjgeEjIwPPc81qakhORbgAdxQtO9IPZJzzVWRLftE95URxTS1pIBT32q78+K62Koyx2E+Z0TmrcrefXmcGlifk6vy6u1Hnx8uh11toIytZ3K8k9hVf1DBhotgLcXLKXgt0AZK85Hn3Nb+nHF3Kxw3lK+UNhJHnI4P8AKsupS2zp6UkJyQkEAd85FeDp5qVdQ62f9HrMPNTcbP8Adb1OaTHvhIW0DLpTwD9KO3O4XK3MxlSFphsjCEk4ArFIDbBEibuWSoZQnvj2r5XIRNZQ3FaLbQJ25PJGTjjxXqVotEdySUsTGO9kdW+zSKwjSTGEIUW3l7VY5GferhVF+yuYhyzTYSVAriyeQPQoTj+Rq8106bvBHkqytUklzPaUpWQxClKUApSlAKUpQClKUApSlAKUpQClKUApSlAKUpQFZ17GbkaWkKkSehHa+d07c7h2x7d64426yuFKSysrQkKLec5xzjvXX/tHkNsaLlIcH+cttscdvnB/kDXJPu1CoylMuclPBFaGIUVJM73wmEnCUl5dTWgM9SKgoO15ArpujS2qyJDuOotZKv5f0rn0eKVMtyGFbVY5Hg1ftK7HbKkOjpOJWoBQ/X+tcT4o70vub2KpuGHS8iVvgcbsU7Cso+Hc+o+U1yyzOtC8suOFaA1lRUkAkfTP1q/6qcnR9Ny9q0rQQEkj0JArnVtUozFbhz0z/MV0P/OUlOLi3o3b0PB/F5ONVSW6V/U6Dp6TFcvb8pbinGmY4CFLSAoc9sDA896+NQanU+4lEfhChtUUJUoY9CU98/XH1qJsbTT7zrbzQeG0HYpWEnnuR5qXctKirLCQlPkBeB+nip+JONHEuhwjbly74I3sFUdTDqpxd/7MEKVbGHUKehoZLQBDiFgg8cn68YxUzDkFrMiDL6zKjuLSjuSPUd/l/Ko+TFjsx0ktpS4gZUtxQUDVVmAJypCEIWtZw5nbuwM4xnn08VrU0p7GadRx3LdqadBmNwpDjLgU0tQcbHkEds/UD0qhXx9DtwTIDKWgtOAhPYAVvl9Ula3VvFwlsfi79x35NRV1OHGcd/m/pXpsJSvg5VZbrTpuntzPPY2rmxHhrZ6+j4nQdCKcXp4BHCesrn9KmLq2x92SW1nKltkZJ5ziqp9n8mcuHMjoCQ2hxKgT4JBB/wDiKss5CGoEhx1fVcS0ogDsDg14DHRy42Xn7np/h7vQp2OWPtlYW/IPH8KawQVhu1LUr5VblAH2z4rfXFcknc98qE87RWtHgB9lThVhBUdo8DmvSJrLZnrpU2qikup0P7Ko0f4eVLhTA42SlLreDkL2jnJrodc5+yZTTRusVsglKm15HvuH9K6NXRopKCsePxMXCtKL4HtKUrMa4pSlAKUpQClKUApSlAKUpQClKUApSlAKUpQClKUBWftDCDoi4KcTkIDauR6OJrkjDcZaErbWUA8lINd2utuYu9qlW6TnoyWlNrx3AI7j3r8/w7fMgqcb3BS2nFNqCvUHFaeKjonc7fwirJSdNLQ3G0KhPqaYWHW0KKcZ5x4P6VfNKTo6rUppwfMhw8Y8ED/91z1TQkSVKePRfIGNvYgAD+lStjuUm0TsuqLjCxtXt5PtXHxlF1aTitzrV6cqlDK+H/C535ESZapEVtwoceTtQM4BVnj98VXRp6NbLE4ta90zeCVEd05xxVqittOIVPmkLj4LamyP8s+vHnH/ADmqdrO6uQ5IhIWHVuEbSeCff8/I7eafDHUw04K/G9v+nl8Vh6daEr72tc1JjyrW51YjyXcJAUpPbnx+tT1miX69WkzoslhCclKUOEgqI9KglojLhpQrqB1RIcBHAHt717btR3OwwvhmuSw4slBHBSNiuPyKjn0r0eNw0ZzVSSzX4tWvbiedwju/DbslwT2JvU8ifZdPwojrqUy5BWZGADkZ4GfbiqX8Q/JYe3uFQZRvGfUqSn+tWJ28RtQaleROaUVqZ2xumjOON2SDxnH5Zz7VX58uKH5MKIwUKS30EHB3vKC08nHng1pRa8Tw8uxuVMPKX6inptY3bG22uK47IdKA68lG7GcJSCVfzTUg3ZGLrFkBDh67S8NLA8eSfbtWCMhiLbzGeSVOoQNq0H5d2cqJ9fT6Vn0fdFSbm5EBS0sLJJPJGOx/LA49a3sdUnQweVXW35uuPJGvgKEK2IblZpJ/i39sm9HwWrbCkIluqDqnyFAHjCeB++am7nMhx7TKUnGekoD6kYH86+ZTDSUCfCO1hCQ3txkun1+vvVT1Je3JyERIBygHLiiMc+B+VeLq4epVxWZ8dbnrMFh03GnHZEJKecXlIIabAyVk44rxcFphHRW+rCANyQexxkj9c1qyIg2Hrul1xfCUA8E17ORLlIWpSUtjk4TxXbtqtT0MpSUm0tl3/R0H7Jegpq7OMpx/etoz9Af966JVU+zWxpsujoxVkvzf8S8o+SoDH6JCf3q2V14RyxSPGVqjqVHJ8RSlKsYhSlKAUpSgFKUoBSlKAUpSgFKUoBSlKAUpSgFKUoDyuc/aLpBSWXb/AGhJQ8k7pTKeyx5WPQjufbJ+vR607pHmSoSmYMpEV1RA6qmw5geeDVZRUlZmWlVlSmpRdj8+vRZs6Pu34UnlJSea+2WXYgblCR1VNqC0g+oParzqfRsOwQW34dzWZSztTGdTuVJUT/AEjIPPpjtVUuFuukJXWk2yZEcb+bq9FWzjznGK58qc46NaHoliKFaLknra2rsbqvtACmVsR2VKUlG3YoHBT/pJ9RzyfFY7dttsc3KYMznjtaQ6nIbB8g+fpVaUtx6cuX/eLkOKKytvuT64FeJmuLfU2ht1TwBUep3GO5OaxvDK1oLfc5sIq/6krIszM5iFIQ4j+8Ks5LyRtKj7Un21UqI2+tXTCiQhaf5H9f3ppC0xLpaLlqO9hxcC3ghDIVtD6wM7c+nKRxjk17p6FNvDcpwLbAbWNqMYAUrJIHoO37V1KVeFGiliXZaa6vTgv5e/I4PxDDwlWzYS+t9OvF+3M024dybmGU3IQHSnaXQsZIxjzz2FbcK2krelFaXZCElSlnjA/wDL6mtl2U8v4u5COhKbc6lpxKUjBOSBgeR8pry+W+dHtS7gXGmitadyEcfKr0/biruphKTjJyWaW3HW+2m/82NF4fFNNSTyr7cN+02a0u4IluoZc+UNI2ktJGfbP9a+pu26Rvj4g2zo42qS0gjqADgn0ra/s9DvGhnNQWsqauEBBTLYQPldUjBKseCU4Vxx7VSk3IlpD6krSlSsBaT5rSxUJ1amZaJbLvmeiwdPDwoKKl9XFviW5v7QA0w2xJbUhWzYEpBO0ece57Z5x/OIU27cHHZXV6K3VFZQOycnOKiutmUiWVOdZKgtK3O+R2Pzd6lYaX7hIWtDUic8pRWtDDZVyfXArW8GMP2I6mEtGTc2v5sa0aJMbWuQHepzhBPbHqKteh9LP6nmuybkpQt8ZQBSkkF5X+nPgY7/AFFfdl06qZeW4N/W5aw4kKZZUjaZH/lCuwPbjvzXR7Hp9ywurZjT3HLcQS3FdSCW1E54X3x34NbNKnJvNNFMXjIRp+FRk/P3JltCWm0toSEpSMJA7AV9UpW4cMUpSgFKUoBSlKAUpSgFKUoBSlY3324zDj7ywhtpJWtR7JA5JoDJSohjVVjkvoYZubC3HFBKEg8knxUtmpcXHdFYyjLZ3PaVgYmR5LrzbLqVrYVscA/hPfBrNmo2JTue0rzNYPjY5nGCHU/EBvqlvztzjP60F0jYpXmaZ4zQk9ryolnVdhfcDbd1jFauACvGf1rfamx3pL0Zt1KnWNvUQO6cjIz9RVnGS3RVTi9mRdvsEa1SH7rOkmXNUCVy38DYjk4SOyQB6VoEPazfI+dmwtn0KVTSP3CP5/yskuKxOirjSWw6y4MKQexr6HTjMYAS222nsBgJAFY7cC9zn98j3e1vy1/CtOpfbLUbYAEoAztSk/w8YG09znB9a9oj7P3L1YJd2nEokTFKQ22vKQWx357pJUO/PA5BzXXW3YtzhBaCh+O+njIylaT7HxWSOw1EjojsICGm0hKEjsAKq4fU7k520iDtekrfE0W1puUyl+OWdr4J/Gonco5//LkenFc+tGmZNnQ4y8tUWU4+sRkrUSpDYQs5PqMgf9IrsOa1F22Kq4feBaBk9PpbiSfl9MdqxYmjKtDJF2EGlLMzkdtUh77O77NWrJXKa3kjByVjx/769NokXiyJaRJU5MkRULipOUkALVuTj1xt59QKv9t01GtmjXrbOS0d7alSV/wkjsr2wAMemK3bLaY33RaVPdKQ7EZHSebyE5IGSPrWKphZTyuLtY262IjOLi+fpa3/AA+NIWCNYdMxobcMR3HG0rkoJ3FTpSN2T5qmSvsjb+5Lkwy6A7vcdiBJyVEElAJPCRj5Tjv3z4rqGad63Gk9zSTscX0ezdZtkZgphkPl3clwp+dKUnuP9PIPKvI4B5x0GVY7lGSxebetoXhDSUy208NSwByD259FYH5eLBGhxoYWI7KGg4srXsTjco9yayPvtx2FvOqCG20lS1HsAOSaiMLItKd9WQ7Tlq1laFNvsbgDh1hwYcYWP3B96kLVActtvREcmOyy2SEuvcqKc8AnzgYGa9iMQlOruEVtvfLQkqeQOXEj8OfXvW1mrJcyt+R7SsESbHnNqcjOpdQlZQSnwodxWbNTsQmnse0rWnXCJbY/xEx9DLWQneo8ZrFAvNuum74GYy+U/iCFZI/Kpyu17aEZo3tfU3qV5mtd6fGYkIjuvJS6tClpSTyUjufyqFqS2lubNKwxpTMyOiRGcS404MpWnsRXzJmxofS+IeS31nA03u/iWewHvU2d7C6tc2KV5mlQSe0pSgFRuo/+7N0/9G7/APA1JVqXWIqfaZkNCglUhhbYUewJBGf3q0XaSZWavFpFdsM94w7c1/Zt9Kem2n4khGMYHzd8481rwb3dZd36DlxYYf8AiVNrt7zW3a3zhSFfxHz71J22BqeGmLHdmW5UVkJQoJaXvKBgd898VrK03dpD8RibPZfhw5QkNuqSTIVgkhJPbHvWxeF3e3fmalqlla/fkREaXdLK/eZImolOiYlkNFgJDzqkgJVkH5QPQelSV4OqbNaH7j97R5KkJy40YwSEe6TnnHvW67pcymrs0++EidIS+0tH4miAMH9RWpcbFqe7W1dvmXKElop5W02oKdx23eAPXFWzwlJPTrp5Fck4xaV+mvmZTOu95ubkC3y24TcRptT75aDilrWncAAeAMVFfGzrJqu4S7q43JcjWnKHG0bOqOoNuR4OTipt6x3KJP8Aj7PKYQ660huS1ISS24UjAVxyDjitdjSs2Tc5cy8TGpAmQzHWhtJTs+YEbc+Bj9aiMoJcLW+5Mo1G+N7/AG6HjidVsW03VdyjKcS31VQvhwEbcZKQrOc4qw2+ai42xiY2kpS+0FgHuMioE2XUrkH7qdusX4Mp6an0tnrqb7Y9M44zViixWocNqKwna20gIQPQAYrFUcbcPtyM1JSvxt15nN7ZNiytFItTdmkypjiFoQtMb5AoqODv9s1tuS51hj6gebdAmRmoKCsjdlWxKVd/zq36dtTlmsce3uuJcWznKkdjkk/1rSmaY+PdvYfdSGrmloI290FCcAn8wDWZ1oOUlw90YFQmoRa39mbOprhIttnEiKsJc6zaMkA8KUAf2NRfxd5m3m7tImNNQbeofIWQpTmUZ258D3968m2HUt0iNRJtwhBplaF5bQrc6Ukfiz2/LzUrFs7zEi8OqdQRcFAoAz8vybeaoskY20b/AMMjzzlxS/0r8K5XmULJBt8hiKJUIuuK6AIRg90p4+mO3NbovVxsU2bDur6JyWYRltOpbDaiAdpSQOO571s2rTkiBLtjy3m1CFDVHUBn5iTnI9qzzrB94XtyW+tJjOwFRFtj8XKs5qZTpuVuHuVjCoo34+xHhGqjbTdPvKN1C31RC+HGzGM7d2c5rBCvt3u7Nst8R9pmXIimTJkrb3bEbto2p7ZJra+5dSi3m1fekT4Tb0w/01dbZ2x6ZxxmvGtLTYUS3PQZbTdxhMllS1JJbdQTnafPepvC2tumhFql9L246/0fT677bo85matq4RvhHFok9JKChQSflWnOCDWvDuN0uaYNrtrzMPbBbfkP9IKxuHCUp4ArdFlu8wSnbnPbLrsdbDLLG4NI3DG455Uax/2cuEL4KVbJbLcxiKmM8l1JLTyU9u3IOfNQnDja/oWcZ30vb1NeRd7vZ/joE2S1JdTCXJiykthJO3uFJ7Z81jekaniWFF+cuUdwJaS8uH8OAkoODjdnOcVtq03cJqJ8m5S2XJ0mMqM0G0kNMpPpnk8+akpdpdk6WXaQ4hLiowZ3nOMgAZpngrbddBkqO++2mpHOXC6Xq7uwrVKRBYitNreeU0HFqUsZCQDxjHmtCVNvLb15tNyktSGm7Q66hxtsIK/GSPB7jHapJyxXOHOTPs8phDzjKGpDUhJLbm0YCuOQa10aZur0y4zJ09h16bAXFAQghLZPbHsP1qYyguVvW5WUaj4O/pYmLGtLWmoDijhKYiCfptFQ8BzUd8hi6sXJiE07lTEUsBYKc8blZzk+1WC3QzDtUaG6QssspbUR2OBioOPZNQWtlUC13CIIOT0i+glxkHwMcH86xxau9r9TLJStHe1uBAW7UD9psTUbqsRZMyc/vfdBUhkA/MceTk4FSMDVBYvMSIb2xdmJaumSlnpraUex44IJrZi6QlxLaylqckXCLIceZfKcghXdKh7jvUjDt96duDcq6TWEtsg7Y8MKCVk+VE8n6VlnKm7vz72MMIVVZeXvxNTXpIsLJCN5ExohH+rntUY3L6Gr2p0+2KtCUw3AlPCuuRyQSnjgDNWLUtokXm2IjxXW23UPIdCnASPlOfFabdguc+4sSr7MjvIjBXSYjtlKSVDBJJ57VWE4qnZ9e+RepCbqXS5eRrwzqe7wEXZi5R4oeT1GYhYCk7fAUrOckVpt3U3a62i4FsNrXBk70HkBSeCPpkVvs2TUVviG22+5RRCGUtOOtkvNJ9Bjg496ytaW+FfgfDOgMxIzrJ353KUv+L9cmpzQT4dPKxXLUaW/C9+d1t6mjp68TG3LO1ILQiXCIrppQ2EBDqTkgY8EVhlXmXNdjS/7lcNy9tRoyVNJV8gyFLBPknsfFSD+lX3tIxLSmQhEuIUqafGcJUCefXsTWeTpom2WaDGcQhNtlNPKKh+MIzn8yTmmane/fmTkq2t35GtFfvmoHJEuFcWrfDaeU0wn4cOKd2nBUok8DPpW5pefcZv3i1c1Nl6LKLQ6ScJwEjt9c55rAmzXu2SZP3LLiCLJcLvSlIUeko99pHcexrb03ZpNnRM+LlCU5Jf6xcAwSSADkfUVSbjldrdOZeCnnV79eRNUpStc2hSlKA8pXtKA8qv3HVbVu1PFsrzBxJSkh/fwkkkAYx6j181YKoGqbUbxrCRGQP75Nq6jJHcLSvI/2/Os1CMZSaltY18ROUIpw3uW+93dmyWl+e8NwbHyozgqUeAKjrTqd66i3LbtjoamhwrdCipLO0kDJxjnHtVZduitZC3wiCG4kdUmaMd3EggD8yM/RVadm/Dpz/00z/7VnWHSh9W/s/wa8sTJz+n9vuvydPLzSVJQpxIUr8IJGTWk7cn274xb0wXVsutFapQzsQRn5TxjPHr5qjWHSlsnaGVcnkLMsturQ4FkbCkqxgdvH718NSXpk+0POyww85Z3U/ELVjYfnAUT+lV8CN2k9i/zErJtWvZ7nSUutrKghaVFPBAOcH3qO0/ek3y3GYGej/eKRsKt3Y96olktURu7Qrfc4bjBlsKT1WX97U4Yzknx4PHtUUzHDNugJjRVuKnSXWngh0oLyUqThvJ4GassNHVX7169CjxUk07d6dOp2JDrboy24lYBwSk5rSvFzXbbe5IYjLmPJxtjt/iXkgHGATxnPaqCy3LtF/trkSxqs/VdDbiFTAsPpJAPB8jPitCNZoY+zeXdygqllYQlRUcJT1EjAHaoWHjdNvTTvcl4mTTSWqv3qjrHXQloOOqDQIGdxxj2rIkhQyOR61RIFujao1JckXhSnUQQhtiNvKQEkfi4/wCc/St3SwNt1LdbJGdU5AYSlxsKVu6SjjKc/n+1YpUkk9dVqZo1m2tNHoW1a0NpKlqCUjuScAVFv3xDWoIVqS0HBLaU4HgvgbQfHntUFdI7d/10LTclK+Cjxeq2yFFIdUT398f0qJucCNpnVqDaVEFEF91LJUVdNWxXr64zVqdGL0b1tcpUryWqWl7do6MXWw4Gy4neRkJzz+lfdclYgvSrIZq9PPvvLSXTczPwQf8AVjGBj0romlpUmZpyG9LUFPKRhSgoHdgkA5HqAKrVo5Fe/f8AJejX8R2atx70IOJri5T21OwtMyJDSVFG9D3GR/7atiJA+FQ9IwwVJBUlasbSR2zXPdHQtQv2h1drurEVj4hYKHGQs7uMnOPpW9FtrWpNU3Fm+OF8wEobaY3FKTkcrwPU8/n9Ky1aUMzS0S5Xv6mGlVnlTerfOyXoWRq+Jd1M9ZehjpRw/wBXfwckDGPzqVyMZrlU+2x7ZdtQw4TiltItuQkq3Fv50Epz7VZb84j/ALK0kKGFRI4HPflFVnQjeOV72LQrytLMtrsty3UNpK3FpQkdyo4FfQIUMg5HrVBtcBjU2oJUe7lTjUBhlMeNvKRgp5Vx+X6ita8pGno8u2Wy8YjPvttuM8lURKs5O7PY1HgLNlvr5FvmGo57aeZ0VLzS1KCHEqKeFAHOPrRbrbaN7i0oSPKjgVz7V+k7NadMLlwtzTyShIV1CetkjIPP5/lWa22+Pqa/y2LuVuNwGGUx428pGCnlXHfx+tR4MXHOnp5eX5HjzUsjjr5+3Qt13uLtut/xMaG5NXuSA01nJBPfgHtWC+39NmZZCYrsmRIJDTaBgEj1V4HNUy/wY9otlxt8O5B5hL7KxEOSqOSr/V6H0qRt9qiapv8Ad3LwpTxiPdFmPvKQ2gdjgetXVKCWZ7f5+SrrTcskd/8Ab/0XCC7JdgtOTWksvqTlaEqyEn61mS62takJWlSk/iAOSPrXKruFRYF1srL63YcScz0FFWenuCspz7f0qdvOn4OmXbVcLWHGX/jG2nDvJ6iVd85PtUOgrrXfbT3CxErPTbfX2L3Sgr2tU3TyvaUoBSlKAUpSgFKUoDytH7ojfff3vlfxHQ6Hf5duc9vWt+lSm1sQ0nuRULTtvt6pyo7akmeol3ntnPA9Bya1o+kbbFEQNl7/AAaHENZX4Xndnj3qepVvEnzKeFDkRsGyxbdZvulkr+H2rT8xyrCiSefzNabekLU2qOSlxxMeOqOlK1ZBQrOc/wDUay6skOxtNTHWXVNOYSlK0HBSSoDIP51WbtfLgvSjEVl9bdwa6glLSohSQz+I59zt/WstONSWqe7MNSVODs1siwWzR9ttc1uW2uQ8tlJSwH3dwZB77R4r6GkrV90G1rQ4tjqF1Kir50KPkEdq+HdQzFvPNW62iX8K2lb6lPdPBUndtSMHJxWMarL0hpMWH1I6oiZjr6nNvTbJIVxjkjHbzT9Z639R+gtLehmtukrfbpiZhdky5CBtbckulZQPavpOlLcjTy7GkvfCLVuPzfNncFd8eorDG1JKU5Ccl2z4eLPUEsOh7crJGUhSccZA9TWpH1hPksQn0WUdOeotx/8AEjJWM5z8vA4PPt2pas3e/qE6CVrejN+56SttzkIklT8aQlIR1o7mxSgPX1rcs9jg2OOpmGhWXFbnHFq3LWfUmq9cb5LmIty24hblMXT4d2Ol75VKCTxux25B7VvHVLzAfjyrdsntvNsoYQ7uS4XASkhWOBwc8eKONVxy3CnSUnKxv3rTkC+KackdRt9n/LeZXtWkema1Lfoy12+eichUh2QkKSpbzm/eCMHOfasUjVirciS3coKWJTIQpDaXgpLoWcAhRAwM98jisbOtEONPIEZt2Uhxtttth8LQ6V5x82OMYOeKJVstlsHKg5Xe59q0FaFOKCXJaI6lblRUPkNE/SrGyy3HZQyyhLbaEhKUpGAAPFVx7VUuGicmZa0tuwkNKKUSNwXvXtGDt4xUhKvS490fgJjoUpqH8SFrd2A/MU7Tkcds5qs1VlbMWg6Uf26d+xGI+z60t5Dcme2CckIkYGf0rbuGj7bcHGniuQxIaQG+uw5tWpIGBuPmtRnWiVwJ75itrXCLYPRf3tqCzgHdjgDnPFb8a+uuyrfGdjshU1Di97L/AFEpCMdjjnOfyq8nXWrZSKw7Vku7mO3aNtNsfedZS64X2Sy6HV7gtJIJz7nFaavs8s6kFpT84s90NF/5UfQYqatFzN0Zfc6PS6MhxnG7Odpxnt5qDkaiNoud4U+ouj4lhqO0tzalJU2CeT+EdyaiMqzk7PUSjRUU2tDfuOkbbcVtOlT8d9pAbS8w5sWUjgA+tfcXSloi21+B8OXW5PLynVFS3D4JPt7VHI1qpyOAxBbkSfikxum1IBQSpJKVBWO3GPbms7mpZ6USnG7Sl1uAAJZEjBCtuVBAx82M9+M0arWs36hSoXvb0MKvs9tDjZbdkTnUDhtK38hv6cVu3HSNtuLjTxU/HkNIDYeYc2LKQMAH1rWumsEW1bLhZYXFdShYPxGHSlWOQjHjPrX1DvV1XdLu27EYLEMjZl8J2/LkZO3z3z496m9b9zYtQvlS9DKnRloRa3ICUO7XXA446V5cWoHjJNfVz0hbrlMMwrkRZChtW5Gc2FY9/WvqwaiTen5UcttJcjbSVMu9RCgrOMHA9KnKxynUjLV6mSMKU46LQgDo60C0i2IbcQ11Q6pQV861DyT5qQudpjXZpluSV4YeS8nacfMntmt+lUzyve5dU4JWseAYr2lKqZBSlKAUpSgFKUoBSlKAUpSgFKUoDSu1uTdbeqGtwtpUtCiQM/hUFY/ao53SsVyTdXw4tKrm0W1DAw3kYJHucA/lU9SrKcoqyZSVOMndogV6acS44uJcno3xDSWpG1IO/anaFDP4TjzWaPpyJGeJRksmEmH0iOCgEnJPqc1L1Bz9QfdNyfZuCUojfDl+O4nusp/Eg5/i7Y+tXUpy0RSUKcNWeRdNKaXDTIuD0mPBVujsrSkYOMAqI/FgHivYumW4sS1xxIWoW11TiSUj5854P/VX1D1EyhLDF0WmPNdxubShW1BVylJURjOCPNbLt/trU8QDJzI3BJQlClbSewJAwPzqW6mxVKla5oP6WDpUpua6ysz1TUrSkEpUU7cc+KK0ql5D7sia6ua68h4SQkJKFIyE4HbAyf1r509quLcY0dqXIbTOdUtOxKSE5CjgZ7ZwAcZrf/tHaviXIxklLrYUSktqGdv4scc4x4qW6qdiEqMlc03dKpliQ5NmOvS3tm18JSktbDlO0eOeT6183G0PfdTnxUiRKcQ4hxpUVhCXG1JPCgPPfmpZF1guGIEPhRmpKmMAneAMk+3HrUTP1MqFdJsEMpddQloRW0/idWvdx9Bjv4FIuo2JRpJXNCBY5N2cuy7gqUlqY222hbyUocygk7gkdgDjGa3H9IrmKkuTbm8+5IjBgq2JSEgKChgfUfua3nL5GtiGm7s+hqSUBTnTbUUJzx3xwM8ZNfUvU1ngyFR5MwIdRgqTtUdoPYnA7e9TnqN6IhQpJfUzXZ0/KYdlvpuajIlJbSpZZTtSEZ4Ce2CDWFrSYitRVRZrjMmM44sOhCSD1PxDb2A7YFSEbUVqmLeSxLSssILi8A/gHdQ9R7ivqNf7ZLbfW1KAEdO53qJKNifU7gOOO9VzVF/hZRo8/Ux2yzrtdteiszFrcdcW51lpBIUrzjsa01aVQ5HcL011cxckSfigkApWBgADtjHGKx3LVsVEJuVAfSpCZLTbqnG1ABCickZxngHmtp/UcV22uSrfIZUWnUNr6wUkJ3KA5GM+eOKm1VO/Mi9F6cj6Njde+GVKm9RcaSJCSlpKAcDG3A+tYpWmlPOzOhPejsTzmQ0lKTk4wSkn8OR3rO5qizMylRXJqUuoc6awUqwlXoTjArM7fraxPEJ2RseUoIAKFbdx7DdjGT9areov8LWpNb+pEzNGtyEymWZrkePKDe9sIST8gASATzjgcVsTtMCY7cP8Y4hm4JT1WwkHCk4wQfy7VPV7UeLPmW8GHIibXZXIE+RNelqkOyG0II6YQEhOcYA+tS1KVSUnJ3ZeMVFWQpSlQWFKUoBSlKAUpSgFKUoBSlKAUpSgFKUoBSlKAVDagtrlx+7uk0lwx5zbq92OEDOe9TNeVMW4u6KyipKzKberPebhLkAsuPJ+IbXHWJIQ2hsFJIKPKuD3qRtse5WqbMY+BEhiVLU+mQl1I2hZ5Cgecj271KqucdN0FuXuS+prqo3DCVpBwcH1HpXtvuUe5sF+MVFoLUhKyMBeDgkeo96yucstmtDAqcFO6epX4tinNWKzxVMpD0WeHnRuHCd6iTnzwRWsxZ7w5dYEmeyta48ha331SQWykhQGxHgYI8Zq1O3CM18QN+9cZG91tv5lpGCRwOecHFZGnUSoyHNp2OoB2rTg4I7EH+VPFkuHbHgwdte0VbSUHdcpMhLgdhQlLjwlDsUqVuUQfOOE59qz3HTTs+9XCcAGnekyYUgHlDick8enYGpi2TIjq5MOKz0UwneiUhISnOAeAPHNb+fekqslJsmNKLgkym3WBqC7MupkQ1kOxAhDTcoIQ27yFFWD8wPBHf3rOmxTizegphO6Zb2mWsqHK0tkEe3JFWKVcGIb8Zh4qC5TnTbwM84J5/IV8wLpHuKn/htym2F7C7j5FEd9p849anxJ5dFp3+CPChm1evf5IKTabqlcR2ClDbzFrXHCyofK58uB+xqNd0xc5xmZZcY60NDaVSZPVK3EuBZzycA4xxxV64pkVCryRLw8XuyuT2bnd4kVty2fDKYlsOqBeSoEA5VjHpx9c1gu9jny5d0cYaSUyVRS38wGdisq/arVketMjPeoVVx2XfaLOipbvvtlVlWOc7ZL/GQykvTpanWRuHzJ+THPjsawXa0XqdMf3MuPJEptcdYk7W0NpKSRszyrg9x+dXHIzivRUqtJd+X4KuhF9+f5A7V7SlYTYFKUoBSlKAUpSgFKUoBSlKAUpSgFKUoBSlKAUpSgFKUoBSlKArmtYwVaW5SFLbfZeSlDiOFALIQofmD+wqvapUxEcfhxENRF2+O30FKWvqL8jpgEDjHJ5966GRmvChJ8VmhVy202NepRzttPcocliIxdtQOOEtynLeHY2VkFRLa95Azzz+lexHITj7CdQvrQwLfHVE6jikIV8nznIPKs496ve0Zzim1J8VPjaWsV8DW9ygTIDKmNS3FCnUSIr4UwtDihtIQk5x2rHfLh1Lit9stRpUd9lIytZecHy5UkZ2hGD6HNdD2j0ptTnOKlV7brvQh4fSyfepWNYxFzn7RFafWwp2SpPUQOUgoVUa7cmWbGxZ5UVll+M+mO8HFqQ03wSHDtIJSoeMjk1etorwoSRgiqxq2STWxeVFuTknuc9gBU2Pbojz63GPvV5odNakhTewkAZOdv1PavURExoMuW26/1oV5EeOS6o7Gt6flxnt8xroO0ele7R6Vfx3yKLDabnPpE4L1EzIYLUZ4XMMra3rU8pOcEqydoSfAx6c16mMGbULqhx4TEXYoSvqq4R1tu3GcYxV/2JznFe7R6VHj6WSJ+X1bbOfqmhzVEZ5ktx3Tciy40FrU6pPIJVk4CT4GPzroArzYnOcV7WOc81tNjLTpuF7vc9pSlYzKKUpQClKUApSlAKUpQClKUApSlAKUpQClKUApSlAKUpQClKUApSlAKUpQClKUApSlAKUpQClKUApSlAKUpQClKUApSlAKUpQClKUApSlAf/9k=');
        //$pdf->Image('@'.$image_path, 9, 9, 30, 24, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
        
        //$image_path = $this->getRequest()->getUriForPath('/images/escudo.jpg');
       // $image_path = str_replace("/app_dev.php", "", $image_path);
       $pdf->Image('images/logo-min-edu.png', 4, 4, 85, 25, '', '', '', false, 0, '', false, false, 0);
        $cabecera = '<table border="0">';
        $cabecera .='<tr>';
            $cabecera .='<td width="15%" align="center" style="font-size: 6px"><br><br><br><span>Estado Plurinacional de Bolivia</span><br><span>Ministerio de Educación</span></td>';
            $cabecera .='<td width="70%" align="center"><h2>CERTIFICADO DE NOTAS</h2><br><h3>REGULARIZACIÓN DE HISTORIAL ACADÉMICO</h3></td>';
        $cabecera .='</tr>';
        $cabecera .='<tr><td></td></tr>';
        $cabecera .='<tr><td></td></tr>';

        $cabecera .='<tr>';
            $cabecera .='<td width="50%"><b>Fecha de Trámite: </b>'.$resultDatos[1]->getFechaRegistro()->format('d/m/Y').'</td>';
            $cabecera .='<td width="50%" align="right"><b>Nro. Trámite: </b>'.$tramite_id.'&nbsp;&nbsp;&nbsp;&nbsp;</td>';
        $cabecera .='</tr>';
        $cabecera .='</table>';
        $pdf->writeHTML($cabecera, true, false, true, false, '');
        $datosTramite='<table border="0" cellpadding="1.8" style="font-size: 8px">'; 
        // Datos del estudiante
        $datosTramite.='<tr style="background-color:#ddd;"><td colspan="4" height="14" style="line-height: 14px;"><b>1. DATOS DEL ESTUDIANTE</b></td></tr>';
        $datosTramite.='<tr><td><b>Código RUDE:</b></td><td colspan="3">'.$datos->inscripcion_nueva[0]->codigoRude.'</td></tr>';
        $datosTramite.='<tr><td><b>Nombre:</b></td><td colspan="3">'.$datos->estudiante.'</td></tr>';
        $datosTramite.='<tr><td><b>Cédula de Identidad:</b></td><td>'.$datos->carnet.' </td></tr>';
        // Datos de la unidad educativa
        $datosTramite.='<tr style="background-color:#ddd;"><td colspan="4" height="14" style="line-height: 14px;"><b>2. DATOS DE LA INSCRIPCIÓN A REGULARIZAR </b></td></tr>';
        $datosTramite.='<tr><td  width="80"><b>Unidad Educativa:</b></td><td colspan="3">'.$datos->institucioneducativa_id.' - '.$datos->institucioneducativa.'</td></tr>';
        // Localtizacion
        $datosTramite.='<tr><td width="80"><b>Gestión:</b></td><td width="50">'.$datos->inscripcion_nueva[0]->gestion.'</td><td width="80"><b>Departamento:</b></td><td width="50">'.$datos->departamento.'</td><td width="50"><b>Distrito:</b></td><td width="50">'.$datos->distrito.'</td></tr>';
        $datosTramite.='<tr><td width="80"><b>Nivel:</b></td><td width="140">'.$datos->inscripcion_nueva[0]->nivel.'</td>
                            <td width="50"><b>Grado:</b></td><td width="50">'.$datos->inscripcion_nueva[0]->grado.'</td>
                            <td width="50"><b>Paralelo:</b></td><td width="50">'.$datos->inscripcion_nueva[0]->paralelo.'</td>
                            <td width="50"><b>Turno:</b></td><td width="50">'.$datos->inscripcion_nueva[0]->turno.'</td>
                            </tr>';
        
        $datosTramite.='</table><br>';
        
        $datosTramite.='<table>
        <tr style="background-color:#ddd;"><td colspan="4" height="14" style="line-height: 14px;"><b>3. CALIFICACIONES A REGULARIZAR </b></td></tr>
        </table><br><br>';
        $pdf->writeHTML($datosTramite, false, false, true, false, '');
        
        if ($datos->asignaturas_notas) {
            $actaSupletorio='<table><tr><td width="100"></td><td>';

            $actaSupletorio.='<table border="0.5" cellpadding="2" style="font-size: 8px">
                            <tr style="background-color:#ddd;">
                            <td align="center" width="200"><b>ASIGNATURA</b></td>
                            <td align="center" width="100"><b>PROMEDIO ANUAL</b></td>
                            </tr>';
              foreach ($datos->asignaturas_notas as $item ) {
                $actaSupletorio.='<tr>';
                    $actaSupletorio.='<td align="left" width="200">'.$item->asignatura.'</td>';
                    $actaSupletorio.='<td align="center" width="100">'.$item->nota.'</td>';
                $actaSupletorio.='</tr>';
            }
            $actaSupletorio.='</table>
            </td>
            </tr>
            </table>
            ';
            $pdf->writeHTML($actaSupletorio, true, false, true, false, '');
        } else {
            $pdf->SetAutoPageBreak(true, 10);
        } 
       
        $pdf->writeHTML('<span>Para su consideración y fines consiguientes, firman los responsables del proceso de Regularización de Historial Académico con documentación de respaldo.</span>', true, false, true, false, '');
        
      
        $firmas='<table cellpadding="0.5" style="font-size: 8px;">';
        $firmas.='<tr>
        <td align="center" width="40%"><br/><br/><br/><br/><br/><br/><br/><br/><br/>_____________________________<br/>Dirección Departamental</td>
        <td align="center" width="60%"><br/><br/>
        <font size="6"><p align="justify"><b>Requisitos presentados:</b><ul>
            <li> Solicitud escrita del estudiante, madre, padre o tutor dirigida al Director Distrital de Educación, especificando el trámite<br></li>
            <li> Fotocopia legalizada de los boletines centralizadores y/o libreta y/o registro pedagógico original firmado por las autoridades educativas<br></li>
            <li> Fotocopia simple de Cedula de Identidad del estudiante<br></li>
            </ul>
            </p> </font>
        </td></tr>';
        
        $firmas.='</table>';
        $pdf->writeHTML($firmas, true, false, true, false, '');
        
        $pdf->Output("Rha_".date('YmdHis').".pdf", 'I');
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

    private function getAsignaturasPerStudent($sie, $gestion, $nivel, $grado, $paralelo, $turno) {
        $em = $this->getDoctrine()->getManager();
        try {
            $especialidades = ['1039'];//, '1038'
            $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
            $query = $entity->createQueryBuilder('iec')
                ->select('ast.id', 'ast.asignatura, ieco.id as iecoId, 0 as nota')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco', 'WITH', 'iec.id=ieco.insitucioneducativaCurso')
                ->leftjoin('SieAppWebBundle:AsignaturaTipo', 'ast', 'WITH', 'ieco.asignaturaTipo=ast.id')
                ->leftjoin('SieAppWebBundle:AreaTipo', 'at', 'WITH', 'ast.areaTipo = at.id')
                ->where('iec.institucioneducativa= :sie')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andwhere('iec.turnoTipo = :turno')
                ->andwhere('ast.id not in (:especialidades)')
                ->setParameter('sie', $sie)
                ->setParameter('gestion',$gestion)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('turno', $turno)
                ->setParameter('especialidades', $especialidades)
                ->orderBy('at.id,ast.id')
                ->getQuery();
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }
    
    /*=====  End of FUNCIONES COMPLEMENTARIAS  ======*/
    

}

