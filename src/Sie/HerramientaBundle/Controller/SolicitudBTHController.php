<?php

namespace Sie\HerramientaBundle\Controller;

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
use Sie\AppWebBundle\Controller\WfTramiteController;
use Sie\AppWebBundle\Entity\InstitucioneducativaEspecialidadTecnicoHumanistico;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnicoTipo;
use Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo;
use Sie\AppWebBundle\Entity\GestionTipo;
use Sie\AppWebBundle\Entity\TramiteDetalle;
use Sie\AppWebBundle\Entity\Tramite;
use Sie\AppWebBundle\Entity\TramiteTipo;




/**
 * ChangeMatricula controller.
 *
 */
class SolicitudBTHController extends Controller {
    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {

        $this->session = new Session();
    }
//Director
    public function indexAction (Request $request) {
        $this->session  = $request->getSession();
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
        $institucion = $request->getSession()->get('ie_id');
        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
        $query = $repository->createQueryBuilder('inss')
            ->select('max(inss.gestionTipo)')
            ->where('inss.institucioneducativa = :idInstitucion')
            ->setParameter('idInstitucion', $institucion)
            ->getQuery();
        $inss = $query->getResult();
        $gestion = $inss[0][1];
        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
        $query = $repository->createQueryBuilder('ie')
            ->select('ie, ies')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
            //->innerJoin('SieAppWebBundle:DependenciaTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
            ->where('ie.id = :idInstitucion')
            ->andWhere('ies.gestionTipo in (:gestion)')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $inss)
            ->getQuery();
        $infoUe = $query->getResult();
        $repository = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica');
        $query = $repository->createQueryBuilder('jg')
            ->select('lt4.codigo AS codigo_departamento,
                        lt4.lugar AS departamento,
                        lt3.codigo AS codigo_provincia,
                        lt3.lugar AS provincia,
                        lt2.codigo AS codigo_seccion,
                        lt2.lugar AS seccion,
                        lt1.codigo AS codigo_canton,
                        lt1.lugar AS canton,
                        lt.codigo AS codigo_localidad,
                        lt.lugar AS localidad,
                        dist.id AS codigo_distrito,
                        dist.distrito,
                        orgt.orgcurricula,
                        dept.dependencia,
                        jg.id AS codigo_le,
                        inst.id,
                        inst.institucioneducativa,
                        lt.area2001,
                        estt.estadoinstitucion,
                        jg.direccion,
                        jg.zona')
            ->join('SieAppWebBundle:Institucioneducativa', 'inst', 'WITH', 'inst.leJuridicciongeografica = jg.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jg.lugarTipoLocalidad = lt.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt1', 'WITH', 'lt.lugarTipo = lt1.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt2', 'WITH', 'lt1.lugarTipo = lt2.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt3', 'WITH', 'lt2.lugarTipo = lt3.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt4', 'WITH', 'lt3.lugarTipo = lt4.id')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'inss', 'WITH', 'inss.institucioneducativa = inst.id')
            ->innerJoin('SieAppWebBundle:EstadoinstitucionTipo', 'estt', 'WITH', 'inst.estadoinstitucionTipo = estt.id')
            ->join('SieAppWebBundle:DistritoTipo', 'dist', 'WITH', 'jg.distritoTipo = dist.id')
            ->join('SieAppWebBundle:OrgcurricularTipo', 'orgt', 'WITH', 'inst.orgcurricularTipo = orgt.id')
            ->join('SieAppWebBundle:DependenciaTipo', 'dept', 'WITH', 'inst.dependenciaTipo = dept.id')
            ->where('inst.id = :idInstitucion')
            ->andWhere('inss.gestionTipo in (:gestion)')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $inss)
            ->getQuery();
        $ubicacionUe = $query->getSingleResult();

        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);


        // Lista de cursos institucioneducativa
        $query = $em->createQuery(
            'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    WHERE iec.institucioneducativa = :idInstitucion
                    AND iec.gestionTipo = :gestion
                    AND iec.nivelTipo IN (:niveles)
                    ORDER BY iec.turnoTipo, iec.nivelTipo, iec.gradoTipo, iec.paraleloTipo')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('niveles', array(1, 2, 3, 11, 12, 13));
        $cursos = $query->getResult();
        /*
         * Listasmos los maestros inscritos en la unidad educativa
         */
        $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'cargoTipo' => 0));

        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');

        $query = $repository->createQueryBuilder('mins')
            ->select('per.carnet, per.paterno, per.materno, per.nombre')
            ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
            ->where('mins.institucioneducativa = :idInstitucion')
            ->andWhere('mins.gestionTipo = :gestion')
            ->andWhere('mins.cargoTipo IN (:cargo)')
            ->andWhere('mins.esVigenteAdministrativo = :esvigente')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargo', array(1,12))
            ->setParameter('esvigente', true)
            ->setMaxResults(1)
            ->getQuery();

        $director = $query->getOneOrNullResult();

        //llamada a la  funcion que devuelve las tareas pendientes
        $TramiteController = new TramiteRueController();
        $TramiteController->setContainer($this->container);

        $lista = $TramiteController->tramiteTarea(34,34,7,$id_usuario,$id_rol,$ie_id);

        return $this->render($this->session->get('pathSystem') . ':SolicitudBTH:index.html.twig', array(
            'ieducativa' => $infoUe,
            'institucion' => $institucion,
            'gestion' => $gestion,
            'ubicacion' => $ubicacionUe,
            'director' => $director,
            'cursos'   => $cursos,
            'maestros'=>$maestros,
            'listatareas'=>$lista['tramites']
        ));
    }
    public function nuevasolicitudAction(Request $request){ //dump($request);die;
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select td.*
                                                from tramite t
                                                join tramite_detalle td on cast(t.tramite as int)=td.id where t.id=".$request->get('id'));
        $query->execute();
        $td = $query->fetchAll();
        if (isset($td[0]['tramite_detalle_id'])){
            $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($td[0]['tramite_detalle_id']);
            if($tramiteDetalle->getTramiteEstado()->getId()== 4){
                $iUE = $tramiteDetalle->getTramite()->getInstitucioneducativa()->getId();
                return $this->redirectToRoute('solicitud_bth_formulario',array('iUE'=>$iUE,'id_tramite'=>$request->get('id')));
            }
        }else{
            $query = $em->getConnection()->prepare("SELECT tp.id,tp.tramite_tipo FROM tramite_tipo tp WHERE tp.obs='BTH'");
            $query->execute();
            $tramite_tipo = $query->fetchAll();
            $tramite_tipoArray = array();
            for ($i = 0; $i < count($tramite_tipo); $i++) {
                $tramite_tipoArray[$tramite_tipo[$i]['id']] = trim($tramite_tipo[$i]['tramite_tipo']);
            }
            //Informacion de la U.E. y del director
            $institucion = $request->getSession()->get('ie_id');
            $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
            $query = $repository->createQueryBuilder('inss')
                ->select('max(inss.gestionTipo)')
                ->where('inss.institucioneducativa = :idInstitucion')
                ->setParameter('idInstitucion', $institucion)
                ->getQuery();
            $inss = $query->getResult();
            $gestion = $inss[0][1];
            //$gestion = 2018;
            $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
            $query = $repository->createQueryBuilder('ie')
                ->select('ie, ies')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
                ->where('ie.id = :idInstitucion')
                ->andWhere('ies.gestionTipo in (:gestion)')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $inss)
                ->getQuery();
            $infoUe = $query->getResult();
            $repository = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica');
            $query = $repository->createQueryBuilder('jg')
                ->select('lt4.codigo AS codigo_departamento,
                        lt4.lugar AS departamento,
                        lt3.codigo AS codigo_provincia,
                        lt3.lugar AS provincia,
                        lt2.codigo AS codigo_seccion,
                        lt2.lugar AS seccion,
                        lt1.codigo AS codigo_canton,
                        lt1.lugar AS canton,
                        lt.codigo AS codigo_localidad,
                        lt.lugar AS localidad,
                        dist.id AS codigo_distrito,
                        dist.distrito,
                        orgt.orgcurricula,
                        dept.dependencia,
                        jg.id AS codigo_le,
                        inst.id,
                        inst.institucioneducativa,
                        lt.area2001,
                        estt.estadoinstitucion,
                        inss.direccion,
                        jg.direccion,
                        jg.zona,
                        jg.lugarTipoIdDistrito')
                ->join('SieAppWebBundle:Institucioneducativa', 'inst', 'WITH', 'inst.leJuridicciongeografica = jg.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jg.lugarTipoLocalidad = lt.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt1', 'WITH', 'lt.lugarTipo = lt1.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt2', 'WITH', 'lt1.lugarTipo = lt2.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt3', 'WITH', 'lt2.lugarTipo = lt3.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt4', 'WITH', 'lt3.lugarTipo = lt4.id')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'inss', 'WITH', 'inss.institucioneducativa = inst.id')
                ->innerJoin('SieAppWebBundle:EstadoinstitucionTipo', 'estt', 'WITH', 'inst.estadoinstitucionTipo = estt.id')
                ->join('SieAppWebBundle:DistritoTipo', 'dist', 'WITH', 'jg.distritoTipo = dist.id')
                ->join('SieAppWebBundle:OrgcurricularTipo', 'orgt', 'WITH', 'inst.orgcurricularTipo = orgt.id')
                ->join('SieAppWebBundle:DependenciaTipo', 'dept', 'WITH', 'inst.dependenciaTipo = dept.id')
                ->where('inst.id = :idInstitucion')
                ->andWhere('inss.gestionTipo in (:gestion)')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $inss)
                ->getQuery();
            $ubicacionUe = $query->getSingleResult();
            /*
             * obtenemos datos de la unidad educativa
             */
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
            $institucionid = $request->getSession()->get('ie_id');
            // Lista de cursos institucioneducativa
            $query = $em->getConnection()->prepare("SELECT  iue.grado_tipo_id,gt.grado  
                                                FROM institucioneducativa_curso iue 
		                                        INNER JOIN grado_tipo gt ON iue.grado_tipo_id=gt.id  
                                                WHERE iue.institucioneducativa_id=$institucionid
                                                AND iue.gestion_tipo_id=$gestion
                                                AND iue.nivel_tipo_id = 3");
            $query->execute();
            $cursos = $query->fetchAll();
            /*
             * Listamos los maestros inscritos en la unidad educativa
             */
            $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'cargoTipo' => 0));
            $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
            $query = $repository->createQueryBuilder('mins')
                ->select('per.carnet, per.paterno, per.materno, per.nombre')
                ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
                ->where('mins.institucioneducativa = :idInstitucion')
                ->andWhere('mins.gestionTipo = :gestion')
                ->andWhere('mins.cargoTipo IN (:cargo)')
                ->andWhere('mins.esVigenteAdministrativo = :esvigente')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('cargo', array(1,12))
                ->setParameter('esvigente', true)
                ->setMaxResults(1)
                ->getQuery();
            $director = $query->getOneOrNullResult();
            /*Grado para el cual se aplica la ratificacion BTH*/
            /**
             * Se aplica a las gestion  2018 mientras pase las inscripciones $gestion = 2018
             */
            //$gestion = 2018;
            /*$query = $em->getConnection()->prepare("SELECT ieht.institucioneducativa_id,ieht.grado_tipo_id FROM institucioneducativa_humanistico_tecnico ieht
                                                WHERE ieht.institucioneducativa_id = $institucionid AND ieht.gestion_tipo_id = $gestion");
            $query->execute();*/
            $query = $em->getConnection()->prepare("SELECT ieht.institucioneducativa_id,ieht.grado_tipo_id FROM institucioneducativa_humanistico_tecnico ieht 
                                                    WHERE ieht.institucioneducativa_id = $institucionid ORDER BY gestion_tipo_id DESC limit 1");
            $query->execute();
            $grado = $query->fetch();
            if ((int)$grado['grado_tipo_id'] < 6){
                $grado = (int)$grado['grado_tipo_id']+1;
            }else{
                $grado = (int)$grado['grado_tipo_id'];
            }
            /** Adecuacion a las equivalencias de especialidades */
            $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad
                                                FROM especialidad_tecnico_humanistico_tipo eth
                                                WHERE eth.es_vigente is TRUE
                                                ORDER BY 2");
            $query->execute();
            $especialidad = $query->fetchAll();
            $form= $this->createFormBuilder()
                ->add('solicitud', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $tramite_tipoArray, 'attr' => array('class' => 'form-control chosen-select','onchange' => 'cargarFormularioSolicitud()')))
                ->getForm();
            return $this->render('SieHerramientaBundle:SolicitudBTH:SolicitudBTHDirec.html.twig',array( 'form' => $form->createView(),'ieducativa' => $infoUe,
                'institucion'   => $institucion,
                'gestion'       => $gestion,
                'ubicacion'     => $ubicacionUe,
                'director'      => $director,
                'cursos'        => $cursos,
                'maestros'      => $maestros,
                'especialidad'  => $especialidad,
                'grado'         => $grado,
                'idflujo'=>$request->get('id')
            ));
        }
    }
    public function guardasolicitudAction(Request $request){
        $id_Institucion = $request->get('institucionid');
        $gestion =  $request->getSession()->get('currentyear');
        $idsolicitud = $request->get('idsolicitud');
        $flujotipo = $request->get('idflujotipo');
        $em = $this->getDoctrine()->getManager();
        $sw = $request->get('sw');
        if($sw==0){//nuevo

            /**
             * Adecuacion solo para que todas las unidades-BTH puedan realizar su regularizacion
             */
            $query = $em->getConnection()->prepare("SELECT COUNT(ieht.id) as cantidad_ue_plena FROM institucioneducativa_humanistico_tecnico ieht
                                                INNER JOIN institucioneducativa  ie on ieht.institucioneducativa_id=ie.id 
                                                INNER JOIN institucioneducativa_nivel_autorizado iena ON iena.institucioneducativa_id = ieht.institucioneducativa_id  
                                                WHERE ieht.institucioneducativa_id=$id_Institucion AND ie.institucioneducativa_tipo_id=1 AND ie.estadoinstitucion_tipo_id=10 AND iena.nivel_tipo_id = 13                                                
                                                AND ieht.institucioneducativa_humanistico_tecnico_tipo_id in (1,7)
                                                ORDER BY 1");
            $query->execute();
            $ue_bth = $query->fetchAll();
            $es_plena=$ue_bth[0]['cantidad_ue_plena'];
            /**
             * Verificicacion de que la UE inicio un tramite
             */
            $query = $em->getConnection()->prepare("SELECT COUNT(tr.id)AS  cantidad_tramite_bth FROM tramite tr  
                                                    WHERE tr.flujo_tipo_id = $flujotipo AND tr.institucioneducativa_id = $id_Institucion
                                                    AND tr.gestion_id = $gestion");
            $query->execute();
            $tramite_ue = $query->fetchAll();
            $tramite_iniciado=$tramite_ue[0]['cantidad_tramite_bth'];
        } else{//tramite devuelto
            $tramite_iniciado=0;
            $query = $em->getConnection()->prepare("SELECT COUNT(ieht.id) as cantidad_ue_plena FROM institucioneducativa_humanistico_tecnico ieht  
                                                WHERE ieht.institucioneducativa_id=$id_Institucion 
                                                ORDER BY 1");
            $query->execute();
            $ue_bth = $query->fetchAll();
            $es_plena=$ue_bth[0]['cantidad_ue_plena'];
            if($es_plena>=1){
                $es_plena=1;
            }else{
                $es_plena=0;
            }
        }
         /**
         * verificamos el tipo de solicitud 45 tramite nuevo
         */

        if( trim($request->get('solicitud')) == 'Registro Nuevo' ){
            if($tramite_iniciado < 1){ //si tiene tramite iniciado
                /**
                 * Verificacion de las especialiade de la U.E
                 */
                $query = $em->getConnection()->prepare("SELECT COUNT(*) AS cantidad_especialidades
                                                FROM institucioneducativa_especialidad_tecnico_humanistico ieth
                                                INNER JOIN especialidad_tecnico_humanistico_tipo espt ON   ieth.especialidad_tecnico_humanistico_tipo_id =  espt.id
                                                WHERE ieth.institucioneducativa_id = $id_Institucion 
                                                ORDER BY 1");
                $query->execute();
                $especialidades = $query->fetchAll();
                $cantidad_especialidades= $especialidades[0]['cantidad_especialidades'];
                $query = $em->getConnection()->prepare("SELECT grado_tipo_id FROM institucioneducativa_humanistico_tecnico WHERE institucioneducativa_id = $id_Institucion order by gestion_tipo_id desc limit 1");
                $query->execute();
                $grado = $query->fetch();
                $grado_id = $grado['grado_tipo_id'];
                if($es_plena==0 or $cantidad_especialidades==0 or $grado_id==4){ // si la ue se  encuentra registrada en la tabla de institucioneducativa_humanistico_tecnico.  si la ue es bth para cualquier gestion

                    $id_rol         = $this->session->get('roluser');
                    //$id_usuario     = $this->session->get('userId');
                    $id_Institucion = $request->get('institucionid');
                    $id_distrito    = $request->get('id_distrito');
                    /***
                     * Adecuacion para obtener el usuario del director.
                     * 2018-  where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=2018 and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
                     * where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=".(new \DateTime())->format('Y')." and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
                     */
                    $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id_Institucion);
                    $query = $em->getConnection()->prepare("select u.* from maestro_inscripcion m
                    join usuario u on m.persona_id=u.persona_id
                    where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=".(new \DateTime())->format('Y')." and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
                    $query->execute();
                    $uDestinatario = $query->fetchAll();
                    if($uDestinatario){
                        $id_usuario = $uDestinatario[0]['id'];
                    }else{
                        return false;
                    }
                   // dump($uid);die;
                    /**
                     * Obtenemos el flujo_proceso y obtenemos la tarea
                     */
                    $flujoproceso   = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $flujotipo , 'orden' => 1));
                    $tarea          = $flujoproceso->getId();//34 Solicita BTH
                    $tabla          = 'institucioneducativa';
                    $datos          = ($request->get('ipt'));
                    $id_tipoTramite = $idsolicitud; //Registro Nuevo
                    $idTramite='';
                    $wfTramiteController = new WfTramiteController();
                    $wfTramiteController->setContainer($this->container);
                    if($sw == 0){//primer envio de solicitud
                        $mensaje = $wfTramiteController->guardarTramiteNuevo($id_usuario,$id_rol,$flujotipo,$tarea,$tabla,$id_Institucion,'',$id_tipoTramite,'',$idTramite,$datos,'',$id_distrito);
                    }
                    else{//se hizo la devolucion por el distrital
                        $idTramite = $request->get('id_tramite');
                        $mensaje = $wfTramiteController->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea,$tabla,$id_Institucion,'','',$idTramite,$datos,'',$id_distrito);
                    }
                    $res = 1;
                }
                else{
                    $res = 2;  //No puede iniciar su trámite como nuevo
                }
            }else{
                $res = 3;
            }
        }
        /**
         * verificamos el tipo de solicitud 46 RATIFICACION
         */
        else{ //dump($tramite_iniciado);dump($es_plena);die;
            if($tramite_iniciado < 1) {
                $query = $em->getConnection()->prepare("SELECT grado_tipo_id FROM institucioneducativa_humanistico_tecnico WHERE institucioneducativa_id = $id_Institucion order by gestion_tipo_id desc limit 1");
                $query->execute();
                $grado = $query->fetch();
                $grado_id = $grado['grado_tipo_id'];
                if ($es_plena > 0 and $grado_id != 4) {// La ue se encuentra registrada en la tabla de institucioneducativa_humanistico_tecnico y le corresponderia tramite de Ratificacion
                    /**
                     * Verificacion de las especialiade de la U.E
                     */
                    $query = $em->getConnection()->prepare("SELECT COUNT(*) AS cantidad_especialidades
                                                FROM institucioneducativa_especialidad_tecnico_humanistico ieth                                               
                                                WHERE ieth.institucioneducativa_id = $id_Institucion 
                                                ORDER BY 1");
                    $query->execute();
                    $especialidades = $query->fetchAll();
                    $cantidad_especialidades = $especialidades[0]['cantidad_especialidades'];
                    if ($cantidad_especialidades > 0){
                        $id_rol = $this->session->get('roluser');
                       // $id_usuario = $this->session->get('userId');
                        $id_Institucion = $request->get('institucionid');
                        $id_distrito = $request->get('id_distrito');
                        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $flujotipo , 'orden' => 1));
                        $tarea   = $flujoproceso->getId();//34 Solicita BTH
                        $tabla = 'institucioneducativa';
                        $id_tipoTramite = $idsolicitud;//Ratificacion
                        $idTramite = '';
                        $wfTramiteController = new WfTramiteController();
                        $wfTramiteController->setContainer($this->container);
                        $datos = ($request->get('ipt')); //dump ($datos);DIE;
                        /***
                         * Adecuacion para obtener el usuario del director.
                         * 2018 ---> where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=2018 and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
                         * where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=".(new \DateTime())->format('Y')." and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
                        */
                        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id_Institucion);
                        $query = $em->getConnection()->prepare("select u.* from maestro_inscripcion m
                        join usuario u on m.persona_id=u.persona_id
                        where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=".(new \DateTime())->format('Y')." and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
                        $query->execute();
                        $uDestinatario = $query->fetchAll();
                        if($uDestinatario){
                            $id_usuario = $uDestinatario[0]['id'];
                        }else{
                            return false;
                        }
                        if ($sw == 0) {//primer envio de solicitud como ratificacion
                            $mensaje = $wfTramiteController->guardarTramiteNuevo($id_usuario, $id_rol, $flujotipo, $tarea, $tabla, $id_Institucion, '', $id_tipoTramite, '', $idTramite, $datos, '', $id_distrito);
                        } else {// se hizo la devolucion de tramite de ratificacion por el distrital
                            $idTramite = $request->get('id_tramite');
                            $mensaje = $wfTramiteController->guardarTramiteEnviado($id_usuario, $id_rol, $flujotipo, $tarea, $tabla, $id_Institucion, '', '', $idTramite, $datos, '', $id_distrito);
                            //dump($mensaje);die;
                        }
                        $res = 1;
                    }else{
                        $res = 2;//debe iniciar como  Nuevo ya que es BTH pero no tiene especialidades
                    }
                } else {
                    $res = 2;//debe iniciar su tramite como nuevo
                }
            }else{
                $res = 3; // esq ya inicio un tramite
            }
        }

        /**
         * Adecuacion: se regustra comoo plena a las unidades q inicien su tramite.
         *
         */
        if ($res == 1) {
            $institucioneducativa = $em->getRepository('SieAppWebBundle:InstitucionEducativa')->find($id_Institucion);
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_humanistico_tecnico');")->execute();
            $entity = new InstitucioneducativaHumanisticoTecnico();
            $entity->setGestionTipoId($this->session->get('currentyear'));
            $entity->setInstitucioneducativaId($id_Institucion);
            $entity->setInstitucioneducativa($institucioneducativa->getInstitucioneducativa());
            $entity->setEsimpreso(false);
            $entity->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(0));//3
            $entity->setFechaCreacion(new \DateTime(date('Y-m-d H:i:s')));
            $entity->setInstitucioneducativaHumanisticoTecnicoTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnicoTipo')->find(5));//7
            $em->persist($entity);
            $em->flush();
        }
        if(isset($mensaje['msg'])){
            $mensaje = $mensaje['msg'];
        }else{
            $mensaje = '';
        }
        return  new JsonResponse(array('estado' => $res, 'msg' => $mensaje));
    }
    public function imprimirDirectorAction(Request $request){
        $tramite_id = $request->get('idtramite');
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('SieAppWebBundle:Tramite')->find($tramite_id);
        $idUE       = $repository->getInstitucioneducativa()->getId();
        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
        $query = $repository->createQueryBuilder('inss')
            ->select('max(inss.gestionTipo)')
            ->where('inss.institucioneducativa = :idInstitucion')
            ->setParameter('idInstitucion', $idUE )
            ->getQuery();
        $inss = $query->getResult();
        $gestion = $inss[0][1];
        //$gestion=2018;//adecuacion a la gestion anterior hasta que pase la etapa de inscripciones
        /*$wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
            ->select('wf')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
            ->where('t.id =' . $tramite_id)
            ->orderBy('wf.id', 'desc')
            ->setMaxResults('1')
            ->getQuery()
            ->getResult();
        $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);*/
        //dump($idUE);dump($gestion);dump($tramite_id);die;
        $arch = 'FORMULARIO_'.$request->get('idUE').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'bth_infoue_v1_gq.rptdesign&idUE='.$idUE.'&gestion='.$gestion.'&idtramite='.$tramite_id.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    public function formularioDirectorAction(Request $request){
        $id_tramite = $request->get('id_tramite');//ID de Tramite
        /*
         * Obtenemios la informacion de la UE
         * */
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos,trm.tramite_tipo
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$id_tramite
                                                ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $infoUE = $query->fetch();
       // $gestion    = $infoUE['gestion_id'];
        $institucion  = $infoUE['institucioneducativa_id'];
        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
        $query = $repository->createQueryBuilder('inss')
            ->select('max(inss.gestionTipo)')
            ->where('inss.institucioneducativa = :idInstitucion')
            ->setParameter('idInstitucion', $institucion)
            ->getQuery();
        $inss = $query->getResult();
        $gestion = $inss[0][1];
        //$gestion = 2018;
        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
        $query = $repository->createQueryBuilder('ie')
            ->select('ie, ies')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
            //->innerJoin('SieAppWebBundle:DependenciaTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
            ->where('ie.id = :idInstitucion')
            ->andWhere('ies.gestionTipo in (:gestion)')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $inss)
            ->getQuery();
        $infoUe = $query->getResult();
        $repository = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica');
        $query = $repository->createQueryBuilder('jg')
            ->select('lt4.codigo AS codigo_departamento,
                        lt4.lugar AS departamento,
                        lt3.codigo AS codigo_provincia,
                        lt3.lugar AS provincia,
                        lt2.codigo AS codigo_seccion,
                        lt2.lugar AS seccion,
                        lt1.codigo AS codigo_canton,
                        lt1.lugar AS canton,
                        lt.codigo AS codigo_localidad,
                        lt.lugar AS localidad,
                        dist.id AS codigo_distrito,
                        dist.distrito,
                        orgt.orgcurricula,
                        dept.dependencia,
                        jg.id AS codigo_le,
                        inst.id,
                        inst.institucioneducativa,
                        lt.area2001,
                        estt.estadoinstitucion,
                        jg.direccion,
                        jg.zona')
            ->join('SieAppWebBundle:Institucioneducativa', 'inst', 'WITH', 'inst.leJuridicciongeografica = jg.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jg.lugarTipoLocalidad = lt.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt1', 'WITH', 'lt.lugarTipo = lt1.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt2', 'WITH', 'lt1.lugarTipo = lt2.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt3', 'WITH', 'lt2.lugarTipo = lt3.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt4', 'WITH', 'lt3.lugarTipo = lt4.id')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'inss', 'WITH', 'inss.institucioneducativa = inst.id')
            ->innerJoin('SieAppWebBundle:EstadoinstitucionTipo', 'estt', 'WITH', 'inst.estadoinstitucionTipo = estt.id')
            ->join('SieAppWebBundle:DistritoTipo', 'dist', 'WITH', 'jg.distritoTipo = dist.id')
            ->join('SieAppWebBundle:OrgcurricularTipo', 'orgt', 'WITH', 'inst.orgcurricularTipo = orgt.id')
            ->join('SieAppWebBundle:DependenciaTipo', 'dept', 'WITH', 'inst.dependenciaTipo = dept.id')
            ->where('inst.id = :idInstitucion')
            ->andWhere('inss.gestionTipo in (:gestion)')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $inss)
            ->getQuery();
        $ubicacionUe = $query->getSingleResult();
        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        // Lista de cursos institucioneducativa
        $query = $em->createQuery(
            'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    WHERE iec.institucioneducativa = :idInstitucion
                    AND iec.gestionTipo = :gestion
                    AND iec.nivelTipo IN (:niveles)
                    ORDER BY iec.turnoTipo, iec.nivelTipo, iec.gradoTipo, iec.paraleloTipo')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('niveles', array(1, 2, 3, 11, 12, 13));
        $cursos = $query->getResult();
        /*
         * Listasmos los maestros inscritos en la unidad educativa
         */
        $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'cargoTipo' => 0));
        //no hay maestros inscritos para la gestion 2019

        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $repository->createQueryBuilder('mins')
            ->select('per.carnet, per.paterno, per.materno, per.nombre')
            ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
            ->where('mins.institucioneducativa = :idInstitucion')
            ->andWhere('mins.gestionTipo = :gestion')
            ->andWhere('mins.cargoTipo IN (:cargo)')
            ->andWhere('mins.esVigenteAdministrativo = :esvigente')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargo', array(1,12))
            ->setParameter('esvigente', true)
            ->setMaxResults(1)
            ->getQuery();
        $director = $query->getOneOrNullResult();
        $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
            ->select('wf')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
            ->where('t.id =' . $id_tramite)
            ->orderBy('wf.id', 'desc')
            ->setMaxResults('1')
            ->getQuery()
            ->getResult();
        $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
        $informe= $datos[0]['informe'];
        $documento= $datos[4];
      ///obtenemos la lista de las especialidades de la unidad educativa
        $institucion_id = $infoUE['institucioneducativa_id'];///revisar
        $query = $em->getConnection()->prepare("SELECT espt.id, espt.especialidad,ieth.gestion_tipo_id
                                                FROM institucioneducativa_especialidad_tecnico_humanistico ieth
                                                INNER JOIN especialidad_tecnico_humanistico_tipo espt ON   ieth.especialidad_tecnico_humanistico_tipo_id =  espt.id
                                                WHERE ieth.institucioneducativa_id = $institucion_id AND ieth.gestion_tipo_id = $gestion
                                                ORDER BY 1");
        $query->execute();
        $lista_especialidad = $query->fetchAll();
        $lista_especialidadarray = array();
        for($i=0;$i<count($lista_especialidad);$i++){
            /**
             * Verificacion de especialidades
             */
            if ($lista_especialidad[$i]['id'] == 55 ) {
                $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=3 ORDER BY 1");
                $query->execute();
                $resultado_new = $query->fetch();
                if (!empty($resultado_new))
                    $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>true);
            } elseif ($lista_especialidad[$i]['id'] == 45 ) {
                $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=62 ORDER BY 1");
                $query->execute();
                $resultado_new = $query->fetch();
                if (!empty($resultado_new))
                    $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>false);
            } elseif ($lista_especialidad[$i]['id'] == 44 ) {
                $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=63 ORDER BY 1");
                $query->execute();
                $resultado_new = $query->fetch();
                if (!empty($resultado_new))
                    $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>false);
            } elseif ($lista_especialidad[$i]['id'] == 2 ) {
                $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=61 ORDER BY 1");
                $query->execute();
                $resultado_new = $query->fetch();
                if (!empty($resultado_new))
                    $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>false);
            } elseif ($lista_especialidad[$i]['id'] == 9 ) {
                $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=66 ORDER BY 1");
                $query->execute();
                $resultado_new = $query->fetch();
                if (!empty($resultado_new))
                    $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>false);
            } elseif ($lista_especialidad[$i]['id'] == 6 ) {
                $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=4 ORDER BY 1");
                $query->execute();
                $resultado_new = $query->fetch();
                if (!empty($resultado_new))
                    $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>true);
            } else {
                $lista_especialidadarray[]=array('id'=>$lista_especialidad[$i]['id'],'especialidad'=>$lista_especialidad[$i]['especialidad'], 'activo'=>false );
            }
        }
        //buscar y armar las especialidades
        $lista_especialidadRegNuearray = array();
        $especialidadinfo = array();
        for($i=0;$i<count($datos[2]['select_especialidad']);$i++){
            $idespecialidad = $datos[2]['select_especialidad'][$i];
            $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
            $query->execute();
            $especialidad = $query->fetch();
            $lista_especialidadRegNuearray[]=array('id'=>$especialidad['id'],'especialidad'=>$especialidad['especialidad'] );
            array_push($especialidadinfo, $idespecialidad);
        } //dump($especialidadinfo);die;
        /**
         * Cuanto el Trámite es nuevo, envia todas las especialidades que esten vigentes.
         */
        $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth.es_vigente IS TRUE ORDER BY 2 ");
        $query->execute();
        $especialidadlista = $query->fetchAll();
        $tipoTramite    = $infoUE['tramite_tipo'];
        $tramite_tipo   = $em->getRepository('SieAppWebBundle:TramiteTipo')->find($tipoTramite)->getTramiteTipo();
        $flujotipo      = $em->getRepository('SieAppWebBundle:Tramite')->find($id_tramite)->getFlujoTipo();
                return $this->render('SieHerramientaBundle:SolicitudBTH:formularioBTHDirec.html.twig',array(
                    'ieducativa'    => $infoUe,
                    'institucion'   => $institucion,
                    'gestion'       => $gestion,
                    'ubicacion'     => $ubicacionUe,
                    'director'      => $director,
                    'cursos'        => $cursos,
                    'maestros'      =>$maestros,
                    'especialidad'  =>$lista_especialidadRegNuearray,
                    'especialidadarray' =>$lista_especialidadarray,
                    'especialidadlista' =>$especialidadlista,
                    'especialidadinfo'  =>$especialidadinfo,
                    'informe'       =>$informe,
                    'id_tramite'    =>$id_tramite,
                    'documento'     =>$documento,
                    'tipoTramite'   =>$tipoTramite,
                    'tramite_tipo'  =>trim($tramite_tipo),
                    'flujotipo'     => $flujotipo->getId()
                ));

    }
    public function ListaEspecialidadesAction(Request $request){
        /**
         * funcion que permite obtener las especialedades en caso de que el tramite sea Ratificacion
         * se obtiene las especialidades de la UE de la ultima gestion registrada en la tabla de
         * institucioneducativa_humanistico_tecnico.
         */
        $id_institucion = $request->get('institucionid');
        $em = $this->getDoctrine()->getManager();
        /**
         * Obtenemos la ultima gestion en la que la UE fue registrada en la tabla
         * institucioneducativa_humanistico_tecnico
         */
        $query = $em->getConnection()->prepare("SELECT  MAX(ieht.gestion_tipo_id)
                                                FROM institucioneducativa_humanistico_tecnico ieht  
                                                 WHERE ieht.institucioneducativa_id=$id_institucion");
        $query->execute();
        $ultima_gestion_registrada = $query->fetchAll();
        $gestion=$ultima_gestion_registrada[0]['max']; // ultima gestion en que fue registrada la UE como BTH

        if($gestion != null){//Si la UE nunca fue declarada como BTH
            $query = $em->getConnection()->prepare("SELECT espt.id, espt.especialidad, ieth.gestion_tipo_id
                                                FROM institucioneducativa_especialidad_tecnico_humanistico ieth
                                                INNER JOIN especialidad_tecnico_humanistico_tipo espt ON   ieth.especialidad_tecnico_humanistico_tipo_id =  espt.id
                                                WHERE ieth.institucioneducativa_id = $id_institucion AND ieth.gestion_tipo_id = $gestion
                                                ORDER BY 1");
            $query->execute();
            $lista_especialidad = $query->fetchAll();
            $lista_especialidadarray = array();
            //dump($lista_especialidad);die;
            for($i=0;$i<count($lista_especialidad);$i++) {
                if ($lista_especialidad[$i]['id'] == 55 ) {
                    $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=3 ORDER BY 1");
                    $query->execute();
                    $resultado_new = $query->fetch();
                    if (!empty($resultado_new))
                        $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>true);
                } elseif ($lista_especialidad[$i]['id'] == 45 ) {
                    $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=62 ORDER BY 1");
                    $query->execute();
                    $resultado_new = $query->fetch();
                    if (!empty($resultado_new))
                        $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>false);
                } elseif ($lista_especialidad[$i]['id'] == 44 ) {
                    $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=63 ORDER BY 1");
                    $query->execute();
                    $resultado_new = $query->fetch();
                    if (!empty($resultado_new))
                        $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>false);
                } elseif ($lista_especialidad[$i]['id'] == 2 ) {
                    $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=61 ORDER BY 1");
                    $query->execute();
                    $resultado_new = $query->fetch();
                    if (!empty($resultado_new))
                        $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>false);
                } elseif ($lista_especialidad[$i]['id'] == 9 ) {
                    $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=66 ORDER BY 1");
                    $query->execute();
                    $resultado_new = $query->fetch();
                    if (!empty($resultado_new))
                        $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>false);
                } elseif ($lista_especialidad[$i]['id'] == 6 ) {
                    $query = $em->getConnection()->prepare("SELECT id, especialidad FROM especialidad_tecnico_humanistico_tipo WHERE id=4 ORDER BY 1");
                    $query->execute();
                    $resultado_new = $query->fetch();
                    if (!empty($resultado_new))
                        $lista_especialidadarray[]=array('id'=>$resultado_new['id'],'especialidad'=>$resultado_new['especialidad'], 'activo'=>true);
                } else {
                    $lista_especialidadarray[]=array('id'=>$lista_especialidad[$i]['id'],'especialidad'=>$lista_especialidad[$i]['especialidad'], 'activo'=>false );
                }
            }
            $lista_especialidadarray = array_unique($lista_especialidadarray, SORT_REGULAR);
            return new JsonResponse($lista_especialidadarray);
        }else{
             $lista_especialidadarray=array();
             return new JsonResponse($lista_especialidadarray);
        }
     }
//Distrital
     public function  VerSolicitudBTHDisAction(Request $request){
         $id_tramite = $request->get('id');//ID de Tramite
         $em = $this->getDoctrine()->getManager();
         $query = $em->getConnection()->prepare("select td.*
                                                 from tramite t
                                                 join tramite_detalle td on cast(t.tramite as int)=td.id where t.id=".$request->get('id'));
         $query->execute();
         $td = $query->fetchAll();
         $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($td[0]['tramite_detalle_id']);
             if($tramiteDetalle->getTramiteEstado()->getId()==4){
                 return $this->redirectToRoute('solicitud_bth_formularioDis',array('lista_tramites_id'=>$request->get('id')));
             }
        /*
         *Obtenemios la informacion de la UE
         * */
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$id_tramite
                                                ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $infoUE = $query->fetch();
        $institucion  = $infoUE['institucioneducativa_id'];
        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
        $query = $repository->createQueryBuilder('inss')
            ->select('max(inss.gestionTipo)')
            ->where('inss.institucioneducativa = :idInstitucion')
            ->setParameter('idInstitucion', $institucion)
            ->getQuery();
        $inss = $query->getResult();
         $gestion = $inss[0][1];
        // $gestion = 2018;
        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
        $query = $repository->createQueryBuilder('ie')
            ->select('ie, ies')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
            //->innerJoin('SieAppWebBundle:DependenciaTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
            ->where('ie.id = :idInstitucion')
            ->andWhere('ies.gestionTipo in (:gestion)')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $inss)
            ->getQuery();
        $infoUe = $query->getResult();
        $repository = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica');
        $query = $repository->createQueryBuilder('jg')
            ->select('lt4.codigo AS codigo_departamento,
                        lt4.lugar AS departamento,
                        lt3.codigo AS codigo_provincia,
                        lt3.lugar AS provincia,
                        lt2.codigo AS codigo_seccion,
                        lt2.lugar AS seccion,
                        lt1.codigo AS codigo_canton,
                        lt1.lugar AS canton,
                        lt.codigo AS codigo_localidad,
                        lt.lugar AS localidad,
                        dist.id AS codigo_distrito,
                        dist.distrito,
                        orgt.orgcurricula,
                        dept.dependencia,
                        jg.id AS codigo_le,
                        inst.id,
                        inst.institucioneducativa,
                        lt.area2001,
                        estt.estadoinstitucion,
                        jg.direccion,
                        jg.zona')
            ->join('SieAppWebBundle:Institucioneducativa', 'inst', 'WITH', 'inst.leJuridicciongeografica = jg.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jg.lugarTipoLocalidad = lt.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt1', 'WITH', 'lt.lugarTipo = lt1.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt2', 'WITH', 'lt1.lugarTipo = lt2.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt3', 'WITH', 'lt2.lugarTipo = lt3.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt4', 'WITH', 'lt3.lugarTipo = lt4.id')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'inss', 'WITH', 'inss.institucioneducativa = inst.id')
            ->innerJoin('SieAppWebBundle:EstadoinstitucionTipo', 'estt', 'WITH', 'inst.estadoinstitucionTipo = estt.id')
            ->join('SieAppWebBundle:DistritoTipo', 'dist', 'WITH', 'jg.distritoTipo = dist.id')
            ->join('SieAppWebBundle:OrgcurricularTipo', 'orgt', 'WITH', 'inst.orgcurricularTipo = orgt.id')
            ->join('SieAppWebBundle:DependenciaTipo', 'dept', 'WITH', 'inst.dependenciaTipo = dept.id')
            ->where('inst.id = :idInstitucion')
            ->andWhere('inss.gestionTipo in (:gestion)')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $inss)
            ->getQuery();
        $ubicacionUe = $query->getSingleResult();
        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        // Lista de cursos institucioneducativa
        $query = $em->createQuery(
            'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    WHERE iec.institucioneducativa = :idInstitucion
                    AND iec.gestionTipo = :gestion
                    AND iec.nivelTipo IN (:niveles)
                    ORDER BY iec.turnoTipo, iec.nivelTipo, iec.gradoTipo, iec.paraleloTipo')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('niveles', array(1, 2, 3, 11, 12, 13));
        $cursos = $query->getResult();
        /*
         * Listasmos los maestros inscritos en la unidad educativa
         */
        //$maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'cargoTipo' => 0));
        //dump ($maestros);die;
           //no hay maestros inscritos para la gestion 2019
         //$gestion=2018;
        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $repository->createQueryBuilder('mins')
            ->select('per.carnet, per.paterno, per.materno, per.nombre')
            ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
            ->where('mins.institucioneducativa = :idInstitucion')
            ->andWhere('mins.gestionTipo = :gestion')
            ->andWhere('mins.cargoTipo IN (:cargo)')
            ->andWhere('mins.esVigenteAdministrativo = :esvigente')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargo', array(1, 12))
            ->setParameter('esvigente',true)
            ->setMaxResults(1)
            ->getQuery();
        $director = $query->getOneOrNullResult();
//dump($director);die;
        $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
            ->select('wf')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
            ->where('t.id =' . $id_tramite)
            ->orderBy('wf.id', 'desc')
            ->setMaxResults('1')
            ->getQuery()
            ->getResult();

        $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
        //dump($datos);die;
        $informe= $datos[0]['informe'];
        $especialidadarray = array();
        for($i=0;$i<count($datos[2]['select_especialidad']);$i++) {
            $idespecialidad = $datos[2]['select_especialidad'][$i];
            $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
            $query->execute();
            $especialidad = $query->fetch();
            $especialidadarray[] = array('id' => $especialidad['id'], 'especialidad' => $especialidad['especialidad']);
            $especialidadifno[$i] = $idespecialidad;
        }
        return $this->render('SieHerramientaBundle:SolicitudBTH:SolicitudBTHDis.html.twig',array(
            'ieducativa' => $infoUe,
            'institucion' => $institucion,
            'gestion' => $gestion,
            'ubicacion' => $ubicacionUe,
            'director' => $director,
            'cursos'   => $cursos,
            //'maestros'=>$maestros,
            'especialidadarray'=>$especialidadarray,
            'informe'=>$informe,
            'id_tramite'=>$id_tramite
            ));

    }
     public function  guardasolicitudDepAction(Request $request){
        $documento = $request->files->get('docpdf');
        if(!empty($documento)){
            /*$dirtmp = $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $aName[0];
            if (!file_exists($dirtmp)) {
                mkdir($dirtmp, 0777);
            }else{
                system('rm -fr ' . $dirtmp.'/*');
            }

            //move the file emp to the directory temp
            $file = $oFile->move($dirtmp, $originalName);*/
            $destination_path = 'uploads/archivos/flujos/'.$request->get('institucionid').'/bth/';
            $imagen = date('YmdHis').'.'.$documento->getClientOriginalExtension();
            $documento->move($destination_path, $imagen);
        }else{
            $imagen='default-2x.pdf';
        }
        $em = $this->getDoctrine()->getManager();
          $id_rol= $this->session->get('roluser');
          $id_usuario= $this->session->get('userId');
          $institucionid    = $request->get('institucionid');
          $id_distrito      = (int)$request->get('id_distrito');
          $evaluacion       = $request->get('evaluacion');
          $id_tramite       = $request->get('id_tramite');

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($id_tramite);
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 2));
        $flujotipo = $flujoproceso->getFlujoTipo()->getId(); //7//SOLICITUDBTH
        $tarea   = $flujoproceso->getId();//35//RECEPCIONA
          $tabla            = 'institucioneducativa';
          $obs              = $request->get('obstxt');
          $datos = json_decode($request->get('ipt'));
          array_push($datos, $imagen);
          $datos = json_encode($datos);
          //ELABORA INFORME
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 3));
        $tarea1   = $flujoproceso->getId();//elaborainfrorme y envia BTH
        $wfTramiteController = new WfTramiteController();
        $wfTramiteController->setContainer($this->container);
          try{
              $mensaje = $wfTramiteController->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea,$tabla,$institucionid,$obs,$evaluacion,$id_tramite,$datos,'',$id_distrito);
              if ($evaluacion=='SI'){
                  /*dump($id_usuario);dump($tarea1);dump($id_tramite);dump($id_rol);dump($flujotipo);
                  dump($tabla);dump($datos);dump($id_distrito);die;*/
                  $mensaje = $wfTramiteController->guardarTramiteRecibido($id_usuario, $tarea1,$id_tramite);
                  $mensaje = $wfTramiteController->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea1,$tabla,$institucionid,'','',$id_tramite,$datos,'',$id_distrito);
              }
              $res = 1;
          }
          catch (Exception $exceptione){
              $res = 0;
          }
         /*if(isset($mensaje['msg'])){
             $mensaje = $mensaje['msg'];
         }else{
             $mensaje = '';
         }*/
         return  new JsonResponse(array('estado' => $res, 'msg' => $mensaje['msg']));
      }
    public function FormularioBTHDisAction(Request $request){
        $id_tramite = $request->get('lista_tramites_id');//ID de Tramite
        /**
         * Obtenemos la informacion de la Unidad Educativa
         */
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$id_tramite
                                                ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $infoUE = $query->fetch();
        //$gestion    = $infoUE['gestion_id'];
        $institucion  = $infoUE['institucioneducativa_id'];
        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
        $query = $repository->createQueryBuilder('inss')
            ->select('max(inss.gestionTipo)')
            ->where('inss.institucioneducativa = :idInstitucion')
            ->setParameter('idInstitucion', $institucion)
            ->getQuery();
        $inss = $query->getResult();
        $gestion = $inss[0][1];
        //$gestion = 2018;
        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
        $query = $repository->createQueryBuilder('ie')
            ->select('ie, ies')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
            ->where('ie.id = :idInstitucion')
            ->andWhere('ies.gestionTipo in (:gestion)')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $inss)
            ->getQuery();
        $infoUe = $query->getResult();
        $repository = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica');
        $query = $repository->createQueryBuilder('jg')
            ->select('lt4.codigo AS codigo_departamento,
                        lt4.lugar AS departamento,
                        lt3.codigo AS codigo_provincia,
                        lt3.lugar AS provincia,
                        lt2.codigo AS codigo_seccion,
                        lt2.lugar AS seccion,
                        lt1.codigo AS codigo_canton,
                        lt1.lugar AS canton,
                        lt.codigo AS codigo_localidad,
                        lt.lugar AS localidad,
                        dist.id AS codigo_distrito,
                        dist.distrito,
                        orgt.orgcurricula,
                        dept.dependencia,
                        jg.id AS codigo_le,
                        inst.id,
                        inst.institucioneducativa,
                        lt.area2001,
                        estt.estadoinstitucion,
                        jg.direccion,
                        jg.zona')
            ->join('SieAppWebBundle:Institucioneducativa', 'inst', 'WITH', 'inst.leJuridicciongeografica = jg.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jg.lugarTipoLocalidad = lt.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt1', 'WITH', 'lt.lugarTipo = lt1.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt2', 'WITH', 'lt1.lugarTipo = lt2.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt3', 'WITH', 'lt2.lugarTipo = lt3.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt4', 'WITH', 'lt3.lugarTipo = lt4.id')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'inss', 'WITH', 'inss.institucioneducativa = inst.id')
            ->innerJoin('SieAppWebBundle:EstadoinstitucionTipo', 'estt', 'WITH', 'inst.estadoinstitucionTipo = estt.id')
            ->join('SieAppWebBundle:DistritoTipo', 'dist', 'WITH', 'jg.distritoTipo = dist.id')
            ->join('SieAppWebBundle:OrgcurricularTipo', 'orgt', 'WITH', 'inst.orgcurricularTipo = orgt.id')
            ->join('SieAppWebBundle:DependenciaTipo', 'dept', 'WITH', 'inst.dependenciaTipo = dept.id')
            ->where('inst.id = :idInstitucion')
            ->andWhere('inss.gestionTipo in (:gestion)')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $inss)
            ->getQuery();
        $ubicacionUe = $query->getSingleResult();
        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        // Lista de cursos institucioneducativa
        $query = $em->createQuery(
            'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    WHERE iec.institucioneducativa = :idInstitucion
                    AND iec.gestionTipo = :gestion
                    AND iec.nivelTipo IN (:niveles)
                    ORDER BY iec.turnoTipo, iec.nivelTipo, iec.gradoTipo, iec.paraleloTipo')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('niveles', array(1, 2, 3, 11, 12, 13));
        $cursos = $query->getResult();
        /*
         * Listasmos los maestros inscritos en la unidad educativa
         */
        $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'cargoTipo' => 0));
        //no hay maestros inscritos para la gestion 2019
        //$gestion = 2018; //borrar
        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $repository->createQueryBuilder('mins')
            ->select('per.carnet, per.paterno, per.materno, per.nombre')
            ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
            ->where('mins.institucioneducativa = :idInstitucion')
            ->andWhere('mins.gestionTipo = :gestion')
            ->andWhere('mins.cargoTipo IN (:cargo)')
            ->andWhere('mins.esVigenteAdministrativo = :esvigente')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargo', array(1,12))
            ->setParameter('esvigente', true)
            ->setMaxResults(1)
            ->getQuery();
        $director = $query->getOneOrNullResult();
        $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
            ->select('wf')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
            ->where('t.id =' . $id_tramite)
            ->orderBy('wf.id', 'desc')
            ->setMaxResults('1')
            ->getQuery()
            ->getResult();
        $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
        /**
         * EL DEPARTAMENTO YA NO ENVIA UN INFORME EN CASO DE DEVOLUCION)
         */
         $informe= $datos[0]['informe'];
         $documento= empty($datos[4])?'':$datos[4];
         $especialidadarray = array();
         for($i=0;$i<count($datos[2]['select_especialidad']);$i++){
             $idespecialidad = $datos[2]['select_especialidad'][$i];
             $query = $em->getConnection()->prepare("SELECT eth.id, eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
             $query->execute();
             $especialidad = $query->fetch();
             $especialidadarray[]=array('id'=>$especialidad['id'],'especialidad'=>$especialidad['especialidad'] );
             $especialidadifno[$i] = $idespecialidad;
         }
        /*lista del catalogo de especialidades */
        $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth ORDER BY 1 ");
        $query->execute();
        $especialidad = $query->fetchAll();
        return $this->render('SieHerramientaBundle:SolicitudBTH:FormularioBTHDis.html.twig',array(
            'ieducativa' => $infoUe,
            'institucion' => $institucion,
            'gestion' => $gestion,
            'ubicacion' => $ubicacionUe,
            'director' => $director,
            'cursos'   => $cursos,
            'maestros'=>$maestros,
            'especialidadarray'=>$especialidadarray,
            'informe'=>$informe,
            'id_tramite'=>$id_tramite,
            'documento'=>$documento
        ));
    }
//Departamental
    public function VerSolicitudBTHDepAction(Request $request){
        $id_tramite = $request->get('id');//ID de Tramite
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$id_tramite
                                                ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $infoUE = $query->fetch();
        $gestion    = $infoUE['gestion_id'];
        $institucion  = $infoUE['institucioneducativa_id'];
        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
        $query = $repository->createQueryBuilder('inss')
            ->select('max(inss.gestionTipo)')
            ->where('inss.institucioneducativa = :idInstitucion')
            ->setParameter('idInstitucion', $institucion)
            ->getQuery();
        $inss = $query->getResult();
        $gestion = $inss[0][1];
        //$gestion = 2018;
        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
        $query = $repository->createQueryBuilder('ie')
            ->select('ie, ies')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
            //->innerJoin('SieAppWebBundle:DependenciaTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
            ->where('ie.id = :idInstitucion')
            ->andWhere('ies.gestionTipo in (:gestion)')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $inss)
            ->getQuery();
        $infoUe = $query->getResult();
        $repository = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica');
        $query = $repository->createQueryBuilder('jg')
            ->select('lt4.codigo AS codigo_departamento,
                        lt4.lugar AS departamento,
                        lt3.codigo AS codigo_provincia,
                        lt3.lugar AS provincia,
                        lt2.codigo AS codigo_seccion,
                        lt2.lugar AS seccion,
                        lt1.codigo AS codigo_canton,
                        lt1.lugar AS canton,
                        lt.codigo AS codigo_localidad,
                        lt.lugar AS localidad,
                        dist.id AS codigo_distrito,
                        dist.distrito,
                        orgt.orgcurricula,
                        dept.dependencia,
                        jg.id AS codigo_le,
                        inst.id,
                        inst.institucioneducativa,
                        lt.area2001,
                        estt.estadoinstitucion,
                        jg.direccion,
                        jg.zona')
            ->join('SieAppWebBundle:Institucioneducativa', 'inst', 'WITH', 'inst.leJuridicciongeografica = jg.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jg.lugarTipoLocalidad = lt.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt1', 'WITH', 'lt.lugarTipo = lt1.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt2', 'WITH', 'lt1.lugarTipo = lt2.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt3', 'WITH', 'lt2.lugarTipo = lt3.id')
            ->leftJoin('SieAppWebBundle:LugarTipo', 'lt4', 'WITH', 'lt3.lugarTipo = lt4.id')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'inss', 'WITH', 'inss.institucioneducativa = inst.id')
            ->innerJoin('SieAppWebBundle:EstadoinstitucionTipo', 'estt', 'WITH', 'inst.estadoinstitucionTipo = estt.id')
            ->join('SieAppWebBundle:DistritoTipo', 'dist', 'WITH', 'jg.distritoTipo = dist.id')
            ->join('SieAppWebBundle:OrgcurricularTipo', 'orgt', 'WITH', 'inst.orgcurricularTipo = orgt.id')
            ->join('SieAppWebBundle:DependenciaTipo', 'dept', 'WITH', 'inst.dependenciaTipo = dept.id')
            ->where('inst.id = :idInstitucion')
            ->andWhere('inss.gestionTipo in (:gestion)')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $inss)
            ->getQuery();
        $ubicacionUe = $query->getSingleResult();
        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        // Lista de cursos institucioneducativa
        $query = $em->createQuery(
            'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    WHERE iec.institucioneducativa = :idInstitucion
                    AND iec.gestionTipo = :gestion
                    AND iec.nivelTipo IN (:niveles)
                    ORDER BY iec.turnoTipo, iec.nivelTipo, iec.gradoTipo, iec.paraleloTipo')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('niveles', array(1, 2, 3, 11, 12, 13));
        $cursos = $query->getResult();
        /*
         * Listasmos los maestros inscritos en la unidad educativa
         */
        $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'cargoTipo' => 0));
        //no hay maestros inscritos para la gestion 2019
        //$gestion = 2018; //borrar
        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $repository->createQueryBuilder('mins')
            ->select('per.carnet, per.paterno, per.materno, per.nombre')
            ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
            ->where('mins.institucioneducativa = :idInstitucion')
            ->andWhere('mins.gestionTipo = :gestion')
            ->andWhere('mins.cargoTipo IN (:cargo)')
            ->andWhere('mins.esVigenteAdministrativo = :esvigente')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargo', array(1,12))
            ->setParameter('esvigente', true)
            ->setMaxResults(1)
            ->getQuery();
        $director = $query->getOneOrNullResult();
        $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
            ->select('wf')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
            ->where('t.id =' . $id_tramite)
            ->orderBy('wf.id', 'desc')
            ->setMaxResults('1')
            ->getQuery()
            ->getResult();
        $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
        //dump($datos);die;
        $informe= $datos[0]['informe'];
        $documento= empty($datos[4])?'':$datos[4];
        $especialidadarray = array();
        for($i=0;$i<count($datos[2]['select_especialidad']);$i++){
            $idespecialidad = $datos[2]['select_especialidad'][$i];
            $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
            $query->execute();
            $especialidad = $query->fetch();
            $especialidadarray[]=array('id'=>$especialidad['id'],'especialidad'=>$especialidad['especialidad'] );
            $especialidadifno[$i] = $idespecialidad;
        }
        return $this->render('SieHerramientaBundle:SolicitudBTH:SolicitudBTHDep.html.twig',array(
            'ieducativa' => $infoUe,
            'institucion' => $institucion,
            'gestion' => $gestion,
            'ubicacion' => $ubicacionUe,
            'director' => $director,
            'cursos'   => $cursos,
            'maestros'=>$maestros,
            'especialidadarray'=>$especialidadarray,
            'informe'=>$informe,
            'id_tramite'=>$id_tramite,
            'documento'=>$documento
        ));

    }
    public function guardasolicitudDepartamentalAction(Request $request){
        $documento = $request->files->get('docpdf');
        if(!empty($documento)){
            $destination_path = 'uploads/archivos/flujos/'.$request->get('institucionid').'/bth/';
            $imagen = date('YmdHis').'.'.$documento->getClientOriginalExtension();
            $documento->move($destination_path, $imagen);
        }else{
            $imagen='default-2x.pdf';
        }
        $em = $this->getDoctrine()->getManager();
        $id_rol         = $this->session->get('roluser');
        $id_usuario     = $this->session->get('userId');
        $institucionid  = $request->get('institucionid');
        $id_distrito    = $request->get('id_distrito');
        $evaluacion     = $request->get('evaluacion');
        $evaluacion2     = $request->get('evaluacion2');
        $idtramite     = $request->get('id_tramite');
       // dump($evaluacion,$evaluacion2);die;

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($idtramite);
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 4));
        $flujotipo = $flujoproceso->getFlujoTipo()->getId(); //7//SOLICITUDBTH
        $tarea   = $flujoproceso->getId();//35//RECEPCIONA
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos,trm.tramite_tipo
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$idtramite
                                                ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $infoUE = $query->fetch();
        $datos = json_decode($request->get('ipt'));
        array_push($datos, $imagen);
        $datos = json_encode($datos);
        $obs            = $request->get('obstxt');
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 5));
        $tarea1   = $flujoproceso->getId();//elaborainfrorme y envia BTH DEPARTAMENTO - ORDEN 5
        $tabla          = 'institucioneducativa';
        $wfTramiteController = new WfTramiteController();
        $wfTramiteController->setContainer($this->container);

        try{
            $mensaje = $wfTramiteController->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea,$tabla,$institucionid,$obs,$evaluacion,$idtramite,$datos,'',$id_distrito);

            if ($evaluacion=='SI') {   //dump($tarea1);die;
                $mensaje = $wfTramiteController->guardarTramiteRecibido($id_usuario, $tarea1,$idtramite);
                $mensaje = $wfTramiteController->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea1,$tabla,$institucionid,'','',$idtramite,$datos,'',$id_distrito);
                //dump($mensaje);die;
                /////volcado a la base de datoa
                /*Recuperamos los datos del tramite*/
                $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
                    ->select('wf')
                    ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
                    ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
                    ->where('t.id =' . $idtramite)
                    ->orderBy('wf.id', 'desc')
                    ->setMaxResults('1')
                    ->getQuery()
                    ->getResult();
                $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
                /*Recuperamos datos de la UE*/
                $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
                $query = $repository->createQueryBuilder('inss')
                    ->select('max(inss.gestionTipo)')
                    ->where('inss.institucioneducativa = :idInstitucion')
                    ->setParameter('idInstitucion', $institucionid)
                    ->getQuery();
                $inss = $query->getResult();
                $gestiontipo = $inss[0][1];
                //$gestiontipo=2018; //pase las inscripciones
                $query = $em->getConnection()->prepare("SELECT * from institucioneducativa  ie 
                                                        INNER JOIN institucioneducativa_sucursal ies on ies.institucioneducativa_id= ie.id
                                                        WHERE ie.id = $institucionid and ies.gestion_tipo_id= $gestiontipo ");
                $query->execute();
                $datosUe  = $query->fetch();
                /*Una vez finalizado el tramite se registra el la tabla correspondiente 45 = Nuevo Registro 46 = Ratificacion*/
                if($tramite->getTramiteTipo()->getTramiteTipo() == 'Registro Nuevo'){
                    /*modificacion par el grado*/
                  /*  $query = $em->getConnection()->prepare("SELECT  MAX(ieht.gestion_tipo_id) as gestion
                                                             FROM institucioneducativa_humanistico_tecnico ieht
                                                             WHERE ieht.institucioneducativa_id=$institucionid");
                    $query->execute();*/
                    $query = $em->getConnection()->prepare("SELECT grado_tipo_id
                                                FROM institucioneducativa_humanistico_tecnico ieht  
                                                WHERE ieht.institucioneducativa_id=$institucionid
                                                ORDER BY gestion_tipo_id DESC LIMIT 2");
                    $query->execute();
                    $ultima_gestion_ue = $query->fetchAll();
                    //RESPALDO DEL ANTERIOR CODIGO
                    /*if($ultima_gestion_ue and $ultima_gestion_ue[0]['gestion']!=null){
                        $ultima_gestion = $ultima_gestion_ue[0]['gestion']-1;
                        $query = $em->getConnection()->prepare("SELECT ieht.grado_tipo_id AS  grado_tipo_id
                                                                     FROM institucioneducativa_humanistico_tecnico ieht
                                                                     WHERE ieht.institucioneducativa_id=$institucionid AND ieht.gestion_tipo_id = $ultima_gestion ");
                        $query->execute();
                        $grado_ue = $query->fetch();
                        $grado_tipo_id = $grado_ue['grado_tipo_id']+1;
                        if ($grado_tipo_id == 3 or $grado_tipo_id == 4) {
                            $estado_grado_tipo = 7;
                        } elseif ($grado_tipo_id == 5 or $grado_tipo_id == 6) {
                            $estado_grado_tipo = 1;
                        }
                        if($grado_tipo_id==7){
                            $grado_tipo_id=6;
                            $estado_grado_tipo = 1;
                        }
                    } else{
                        $estado_grado_tipo = 7;
                        $grado_tipo_id = 3;
                    }*/
                    if(count($ultima_gestion_ue)==2){

                        $grado_tipo_id = $ultima_gestion_ue[1]['grado_tipo_id']+1;
                        if ($grado_tipo_id == 3 or $grado_tipo_id == 4) {
                            $estado_grado_tipo = 7;
                        } elseif ($grado_tipo_id == 5 or $grado_tipo_id == 6) {
                            $estado_grado_tipo = 1;
                        }
                        if($grado_tipo_id==7){
                            $grado_tipo_id=6;
                            $estado_grado_tipo = 1;
                        }
                    } else{
                        $estado_grado_tipo = 7;
                        $grado_tipo_id = 3;
                    }
                    /**
                     * Adecuacion: se actualizan los datos de la Unidad Educativa al finalizar el tramite
                     */
                    $institucionBth = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId'=>$institucionid,'gestionTipoId'=>$this->session->get('currentyear')));
                    /*$institucionBth->setGestionTipoId($this->session->get('currentyear'));
                    $institucionBth->setInstitucioneducativaId($institucionid);
                    $institucionBth->setInstitucioneducativa($datosUe['institucioneducativa']);
                    $institucionBth->setEsimpreso(false);*/
                    $institucionBth->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($grado_tipo_id));//3
                    $institucionBth->setFechaCreacion(new \DateTime(date('Y-m-d H:i:s')));
                    $institucionBth->setInstitucioneducativaHumanisticoTecnicoTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnicoTipo')->find($estado_grado_tipo));//7
                    $em->persist($institucionBth);
                    $em->flush();
                    /*$em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_humanistico_tecnico');")->execute();
                    $entity = new InstitucioneducativaHumanisticoTecnico();
                    $entity->setGestionTipoId($this->session->get('currentyear'));
                    $entity->setInstitucioneducativaId($institucionid);
                    $entity->setInstitucioneducativa($datosUe['institucioneducativa']);
                    $entity->setEsimpreso(false);
                    $entity->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($grado_tipo_id));//3
                    $entity->setFechaCreacion(new \DateTime(date('Y-m-d H:i:s')));
                    $entity->setInstitucioneducativaHumanisticoTecnicoTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnicoTipo')->find($estado_grado_tipo));//7
                    $em->persist($entity);
                    $em->flush();*/
                    for($i=0;$i<count($datos[2]['select_especialidad']);$i++){
                        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_especialidad_tecnico_humanistico');")->execute();
                        $entity = new InstitucioneducativaEspecialidadTecnicoHumanistico();
                        $idespecialidad = $datos[2]['select_especialidad'][$i];
                        $espe = $em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($idespecialidad);
                        $ue = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucionid);
                        $gestiontipo = $em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear'));
                        $entity->setInstitucioneducativa($ue);
                        $entity->setEspecialidadTecnicoHumanisticoTipo($espe);
                        $entity->setGestionTipo($gestiontipo);
                        $entity->setFechaRegistro(new \DateTime(date('Y-m-d H:i:s')));
                        $em->persist($entity);
                        $em->flush();
                    }
                }
                else{// En caso de ratificacion
                    /**
                     * Se consulta si la UE quiere ratificar para una gestion nueva o en la misma gestion
                     * Si es en la misma gestion solo se actualiza el campo de esimpreso en la tabla de
                     * institucioneducativa_humanistico_tecnico
                     * Si es en una gestion distinta se hace un nuevo registro en la misma tabla pero
                     * con la gestion actual
                     **/

                    /***
                     * Query que obtiene la ultima gestion donde la UE hizo su registro como BTH
                     */
                    /*$query = $em->getConnection()->prepare("SELECT  MAX(ieht.gestion_tipo_id) as gestion
                                                             FROM institucioneducativa_humanistico_tecnico ieht  
                                                             WHERE ieht.institucioneducativa_id=$institucionid");
                    $query->execute();
                    $ultima_gestion_ue = $query->fetchAll();*/

                    $query = $em->getConnection()->prepare("SELECT gestion_tipo_id as gestion, grado_tipo_id
                                                FROM institucioneducativa_humanistico_tecnico ieht  
                                                WHERE ieht.institucioneducativa_id=$institucionid
                                                ORDER BY gestion_tipo_id DESC LIMIT 2");
                    $query->execute();
                    $grado_ue = $query->fetchAll();

                    $ultima_gestion = $grado_ue[1]['gestion'];
                    $gestion_actual =  $request->getSession()->get('currentyear');
                    if ($ultima_gestion<$gestion_actual) {
                        /**
                         * Si la UE ya se encuentra como BTH en alguna gestion pasada se hace un nuevo registro con la gestion actual
                         */
                        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_humanistico_tecnico');")->execute();
                        /**
                         * Se obtiene informacion de la UE
                         */
                       /* $query = $em->getConnection()->prepare("SELECT ieht.grado_tipo_id AS  grado_tipo_id
                                                             FROM institucioneducativa_humanistico_tecnico ieht  
                                                             WHERE ieht.institucioneducativa_id=$institucionid AND ieht.gestion_tipo_id = $ultima_gestion ");
                        $query->execute();
                        $grado_ue = $query->fetch();*/
                        if ($grado_ue[1]['grado_tipo_id'] < 6) {
                            $grado_tipo_id = $grado_ue[1]['grado_tipo_id'] + 1;

                            if ($grado_tipo_id == 3 or $grado_tipo_id == 4) {
                                $estado_grado_tipo = 7;
                            } else {
                                if ($grado_tipo_id == 5 or $grado_tipo_id == 6) {
                                    $estado_grado_tipo = 1;
                                }
                            }
                        } else {
                            $grado_tipo_id = 6;
                            $estado_grado_tipo = 1;
                        }
                        /**
                         * Adecuacion: se actualizan los datos de la Unidad Educativa al finalizar el tramite
                         */
                        $institucionBth = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId'=>$institucionid,'gestionTipoId'=>$this->session->get('currentyear')));
                        $institucionBth->setGestionTipoId($this->session->get('currentyear'));
                        $institucionBth->setInstitucioneducativaId($institucionid);
                        $institucionBth->setInstitucioneducativa($datosUe['institucioneducativa']);
                        $institucionBth->setEsimpreso(false);
                        $institucionBth->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($grado_tipo_id));//3
                        $institucionBth->setFechaCreacion(new \DateTime(date('Y-m-d H:i:s')));
                        $institucionBth->setInstitucioneducativaHumanisticoTecnicoTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnicoTipo')->find($estado_grado_tipo));//7
                        $em->persist($institucionBth);
                        $em->flush();

                        /*$entity = new InstitucioneducativaHumanisticoTecnico();
                        $entity->setGestionTipoId($this->session->get('currentyear'));
                        $entity->setInstitucioneducativaId($institucionid);
                        $entity->setInstitucioneducativa($datosUe['institucioneducativa']);
                        $entity->setEsimpreso(false);
                        $entity->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($grado_tipo_id));
                        $entity->setFechaCreacion(new \DateTime(date('Y-m-d H:i:s')));
                        $entity->setInstitucioneducativaHumanisticoTecnicoTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnicoTipo')->find($estado_grado_tipo));
                        $em->persist($entity);
                        $em->flush();*/

                        for ($i = 0; $i < count($datos[2]['select_especialidad']); $i++) {
                            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_especialidad_tecnico_humanistico');")->execute();
                            $entity = new InstitucioneducativaEspecialidadTecnicoHumanistico();
                            $idespecialidad = (int)$datos[2]['select_especialidad'][$i];
                            $espe = $em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($idespecialidad);
                            $ue = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucionid);
                            $gestiontipo = $em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear'));
                            $entity->setInstitucioneducativa($ue);
                            $entity->setEspecialidadTecnicoHumanisticoTipo($espe);
                            $entity->setGestionTipo($gestiontipo);
                            $entity->setFechaRegistro(new \DateTime(date('Y-m-d H:i:s')));
                            $em->persist($entity);
                            $em->flush();
                        }
                    }elseif ($ultima_gestion==$gestion_actual){
                        /**
                         * Adecuacion: se actualizan los datos de la Unidad Educativa al finalizar el tramite
                         */
                        /*$query = $em->getConnection()->prepare("SELECT ieht.grado_tipo_id AS  grado_tipo_id
                                                             FROM institucioneducativa_humanistico_tecnico ieht  
                                                             WHERE ieht.institucioneducativa_id=$institucionid AND ieht.gestion_tipo_id = $ultima_gestion ");
                        $query->execute();
                        $grado_ue = $query->fetch();*/
                        if ($grado_ue[1]['grado_tipo_id'] < 6) {
                            $grado_tipo_id = $grado_ue[1]['grado_tipo_id'] + 1;

                            if ($grado_tipo_id == 3 or $grado_tipo_id == 4) {
                                $estado_grado_tipo = 7;
                            } else {
                                if ($grado_tipo_id == 5 or $grado_tipo_id == 6) {
                                    $estado_grado_tipo = 1;
                                }
                            }
                        } else {
                            $grado_tipo_id = 6;
                            $estado_grado_tipo = 1;
                        }
                        $institucionBth = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId'=>$institucionid,'gestionTipoId'=>$this->session->get('currentyear')));
                        $institucionBth->setGestionTipoId($this->session->get('currentyear'));
                        $institucionBth->setInstitucioneducativaId($institucionid);
                        $institucionBth->setInstitucioneducativa($datosUe['institucioneducativa']);
                        $institucionBth->setEsimpreso(false);
                        $institucionBth->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($grado_tipo_id));//3
                        $institucionBth->setFechaCreacion(new \DateTime(date('Y-m-d H:i:s')));
                        $institucionBth->setInstitucioneducativaHumanisticoTecnicoTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnicoTipo')->find($estado_grado_tipo));//7
                        $em->persist($institucionBth);
                        $em->flush();
                        /*$query = $em->getConnection()->prepare("SELECT ieht.institucioneducativa_id,ieht.grado_tipo_id,* FROM institucioneducativa_humanistico_tecnico ieht
                                                        WHERE ieht.institucioneducativa_id = $institucionid AND ieht.gestion_tipo_id = $gestiontipo");
                        $query->execute();
                        $instucioneducativa = $query->fetch();
                        $id_ue=$instucioneducativa['id'];
                        $instucioneducativaHt = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->find($id_ue);
                        $instucioneducativaHt->setEsimpreso(true);
                        $em->persist($instucioneducativaHt);
                        $em->flush();*/
                        for($i=0;$i<count($datos[2]['select_especialidad']);$i++){
                            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_especialidad_tecnico_humanistico');")->execute();
                            $entity = new InstitucioneducativaEspecialidadTecnicoHumanistico();
                            $idespecialidad = $datos[2]['select_especialidad'][$i];
                            $espe = $em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($idespecialidad);
                            $ue = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucionid);
                            $gestiontipo = $em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear'));
                            $entity->setInstitucioneducativa($ue);
                            $entity->setEspecialidadTecnicoHumanisticoTipo($espe);
                            $entity->setGestionTipo($gestiontipo);
                            $entity->setFechaRegistro(new \DateTime(date('Y-m-d H:i:s')) );
                            $em->persist($entity);
                            $em->flush();
                        }
                    }
                }
                ///// FIN DE volcado a la base de datoa
            }
            else{
                $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 6));
                $tarea2   = $flujoproceso->getId();//6 realiza observacion
                $mensaje = $wfTramiteController->guardarTramiteRecibido($id_usuario, $tarea2,$idtramite);
                $mensaje = $wfTramiteController->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea2,$tabla,$institucionid,'',$evaluacion2,$idtramite,$datos,'',$id_distrito);
            }
            $res = 1;
        }
        catch (Exception $exceptione){
            $res = 0;
        }
        return  new Response($res);
    }
}
