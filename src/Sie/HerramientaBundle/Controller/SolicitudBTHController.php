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
        //init the session values
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
            ->andWhere('mins.cargoTipo = :cargo')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargo', '1')
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
    public function nuevasolicitudAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select td.*
                                                from tramite t
                                                join tramite_detalle td on cast(t.tramite as int)=td.id where t.id=".$request->get('id'));
        $query->execute();
        $td = $query->fetchAll();
        if ($td[0]['tramite_detalle_id']){
            $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($td[0]['tramite_detalle_id']);
            if($tramiteDetalle->getTramiteEstado()->getId()== 4){
                $iUE = $tramiteDetalle->getTramite()->getInstitucioneducativa()->getId();
                return $this->redirectToRoute('solicitud_bth_formulario',array('iUE'=>$iUE,'id_tramite'=>$request->get('id')));
            }
        }
        $query = $em->getConnection()->prepare("SELECT tp.id,tp.tramite_tipo FROM tramite_tipo tp WHERE tp.obs='BTH' ");
        $query->execute();
        $tramite_tipo = $query->fetchAll();
        $tramite_tipoArray = array();
        for ($i = 0; $i < count($tramite_tipo); $i++) {
            $tramite_tipoArray[$tramite_tipo[$i]['id']] = $tramite_tipo[$i]['tramite_tipo'];
        }
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
            ->andWhere('mins.cargoTipo = :cargo')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargo', '1')
            ->setMaxResults(1)
            ->getQuery();
        $director = $query->getOneOrNullResult();
        /*Grado para el cual se aplica la ratificacion BTH*/

        $query = $em->getConnection()->prepare("SELECT ieht.institucioneducativa_id,ieht.grado_tipo_id FROM institucioneducativa_humanistico_tecnico ieht 
                                                WHERE ieht.institucioneducativa_id = $institucionid AND ieht.gestion_tipo_id = $gestion");
        $query->execute();
        $grado = $query->fetch();
        /*lista del catalogo de especialidades */
        $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth ORDER BY 1 ");
        $query->execute();
        $especialidad = $query->fetchAll();
        //dump($especialidad);die;

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
            'grado'         => $grado['grado_tipo_id']));

    }
    public function guardasolicitudAction(Request $request){ //dump($request);die;
        $id_Institucion = $request->get('institucionid');
        $gestion =  $request->getSession()->get('currentyear');
        $em = $this->getDoctrine()->getManager();
        $sw = $request->get('sw');
        if($sw==0){
            /**
             * Adecuacion solo para que todas las unidades-BTH puedan realizar su regularizacion
             */
            $query = $em->getConnection()->prepare("SELECT COUNT(ieht.id) as cantidad_ue_plena FROM institucioneducativa_humanistico_tecnico ieht  
                                                WHERE ieht.institucioneducativa_id=$id_Institucion 
                                                ORDER BY 1");
            $query->execute();
            $ue_bth = $query->fetchAll();
            $es_plena=$ue_bth[0]['cantidad_ue_plena'];
            /**
             * Verificicacion de que la UE inicio un tramite
             */
            $query = $em->getConnection()->prepare("SELECT COUNT(tr.id)AS  cantidad_tramite_bth FROM tramite tr  
                                                WHERE tr.flujo_tipo_id = 7 AND tr.institucioneducativa_id = $id_Institucion
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
            if($es_plena>1){
                $es_plena=1;
            }else{
                $es_plena=0;
            }
        }
         /**
         * verificamos el tipo de solicitud 45 tramite nuevo
         */

        if( $request->get('idsolicitud')== 45 ){
            if($tramite_iniciado < 1){ //si tiene tramite iniciado
                if($es_plena==0){ // La ue no se encuentra registrada en la tabla de institucioneducativa_humanistico_tecnico.  si la ue es bth para cualquier gestion
                    $id_rol         = $this->session->get('roluser');
                    $id_usuario     = $this->session->get('userId');
                    $id_Institucion = $request->get('institucionid');
                    $id_distrito    = $request->get('id_distrito');
                    $flujotipo      = 7;//SOLICITUDBTH
                    $tarea          = 34;//Solicita BTH
                    $tabla          = 'institucioneducativa';
                    $datos          = ($request->get('ipt'));
                    $id_tipoTramite = 45;//Registro Nuevo
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
                    $res = 2;//No puede iniciar su trámite como nuevo
                }
            }else{
                $res = 3;
               //return  new Response($res);
            }
        }
        /**
         * verificamos el tipo de solicitud 46 RATIFICACION
         */
        else{
            if($tramite_iniciado < 1) {
                if ($es_plena > 0) {// La ue se encuentra registrada en la tabla de institucioneducativa_humanistico_tecnico y le corresponderia tramite de Ratificacion
                    $id_rol = $this->session->get('roluser');
                    $id_usuario = $this->session->get('userId');
                    $id_Institucion = $request->get('institucionid');
                    $id_distrito = $request->get('id_distrito');
                    $flujotipo = 7;//SOLICITUDBTH
                    $tarea = 34;//Solicita BTH
                    $tabla = 'institucioneducativa';

                    $id_tipoTramite = 46;//Ratificacion
                    //$informacion    = json_decode($request->get('ipt',true)) ;

                    $idTramite = '';

                    $wfTramiteController = new WfTramiteController();
                    $wfTramiteController->setContainer($this->container);
                    $datos = ($request->get('ipt'));
                    if ($sw == 0) {//primer envio de solicitud como ratificacion
                        $mensaje = $wfTramiteController->guardarTramiteNuevo($id_usuario, $id_rol, $flujotipo, $tarea, $tabla, $id_Institucion, '', $id_tipoTramite, '', $idTramite, $datos, '', $id_distrito);
                    } else {// se hizo la devolucion de tramite de ratificacion por el distrital
                        $idTramite = $request->get('id_tramite');
                        $mensaje = $wfTramiteController->guardarTramiteEnviado($id_usuario, $id_rol, $flujotipo, $tarea, $tabla, $id_Institucion, '', '', $idTramite, $datos, '', $id_distrito);
                    }

                    $res = 1;
                } else {
                    $res = 2;//debe iniciar su tramite como nuevo
                }
            }else{
                $res = 3; // esq ya inicio un tramite
            }
        }
        return  new Response($res);
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
        $gestion=2018;//adecuacion a la gestion anterior hasta que pase la etapa de inscripciones
        $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
            ->select('wf')
            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
            ->where('t.id =' . $tramite_id)
            ->orderBy('wf.id', 'desc')
            ->setMaxResults('1')
            ->getQuery()
            ->getResult();
        $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
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
        //validation if the user is logged

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
                                                ORDER BY wfsol.id DESC limit 1 ");

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
        $gestion = 2018;
        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');

        $query = $repository->createQueryBuilder('mins')
            ->select('per.carnet, per.paterno, per.materno, per.nombre')
            ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
            ->where('mins.institucioneducativa = :idInstitucion')
            ->andWhere('mins.gestionTipo = :gestion')
            ->andWhere('mins.cargoTipo = :cargo')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargo', '1')
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
        $institucion_id = $infoUE['institucioneducativa_id'];
        $query = $em->getConnection()->prepare("SELECT espt.id, espt.especialidad,ieth.gestion_tipo_id
                                                FROM institucioneducativa_especialidad_tecnico_humanistico ieth
                                                INNER JOIN especialidad_tecnico_humanistico_tipo espt ON   ieth.especialidad_tecnico_humanistico_tipo_id =  espt.id
                                                WHERE ieth.institucioneducativa_id = $institucion_id AND ieth.gestion_tipo_id = $gestion
                                                ORDER BY 1");
        $query->execute();
        $lista_especialidad = $query->fetchAll();

        $lista_especialidadarray = array();
        for($i=0;$i<count($lista_especialidad);$i++){
            $lista_especialidadarray[]=array('id'=>$lista_especialidad[$i]['id'],'especialidad'=>$lista_especialidad[$i]['especialidad'] );
        }

        //buscar y armar las especialidades
        $lista_especialidadRegNuearray = array();
        $especialidadinfo = array();
        //dump($datos[2]['select_especialidad']);die;
        for($i=0;$i<count($datos[2]['select_especialidad']);$i++){
            $idespecialidad = $datos[2]['select_especialidad'][$i];
            $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
            $query->execute();
            $especialidad = $query->fetch();
            $lista_especialidadRegNuearray[]=array('id'=>$especialidad['id'],'especialidad'=>$especialidad['especialidad'] );
            //$especialidadifno[$i] = $idespecialidad;
            array_push($especialidadinfo, $idespecialidad);
        }
        $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth ORDER BY 1 ");
        $query->execute();
        $especialidadlista = $query->fetchAll();
        $tipoTramite    = $infoUE['tramite_tipo'];
                return $this->render('SieHerramientaBundle:SolicitudBTH:formularioBTHDirec.html.twig',array(
                    'ieducativa' => $infoUe,
                    'institucion' => $institucion,
                    'gestion' => $gestion,
                    'ubicacion' => $ubicacionUe,
                    'director' => $director,
                    'cursos'   => $cursos,
                    'maestros'=>$maestros,
                    'especialidad'=>$lista_especialidadRegNuearray,
                    'especialidadarray'=>$lista_especialidadarray,
                    'especialidadlista' =>$especialidadlista,
                    'especialidadinfo'=>$especialidadinfo,
                    'informe'=>$informe,
                    'id_tramite'=>$id_tramite,
                    'documento'=>$documento,
                    'tipoTramite'=>$tipoTramite
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
        $gestion=$ultima_gestion_registrada[0]['max']; // ultima gestion en kla que fue registrada la UE como BTH
        if($gestion != null){//Si la UE nunca fue declarada como BTH
            $query = $em->getConnection()->prepare("SELECT espt.id, espt.especialidad,ieth.gestion_tipo_id
                                                FROM institucioneducativa_especialidad_tecnico_humanistico ieth
                                                INNER JOIN especialidad_tecnico_humanistico_tipo espt ON   ieth.especialidad_tecnico_humanistico_tipo_id =  espt.id
                                                WHERE ieth.institucioneducativa_id = $id_institucion AND ieth.gestion_tipo_id = $gestion
                                                ORDER BY 1");
            $query->execute();
            $lista_especialidad = $query->fetchAll();
            $lista_especialidadarray = array();
            for($i=0;$i<count($lista_especialidad);$i++){
                $lista_especialidadarray[]=array('id'=>$lista_especialidad[$i]['id'],'especialidad'=>$lista_especialidad[$i]['especialidad'] );
            }
            return new JsonResponse($lista_especialidadarray);
        }else{
            $lista_especialidadarray="";
            return new JsonResponse($lista_especialidadarray);
        }
    }
//Distrital
    public function SolicitidBTHDisAction(Request $request){
        $id_rol= $this->session->get('roluser');
        $id_usuario= $this->session->get('userId');
        $TramiteController = new TramiteRueController();
        $TramiteController->setContainer($this->container);
        $lista = $TramiteController->tramiteTarea(34,35,7,$id_usuario,$id_rol,'');
        return $this->render('SieHerramientaBundle:SolicitudBTH:inicioDistrital.html.twig',array('lista_tramites'=>$lista['tramites']));
    }
    public function  VerSolicitudBTHDisAction(Request $request){

        //validation if the user is logged

        $id_tramite = $request->get('id');//ID de Tramite
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select td.*
                                                from tramite t
                                                join tramite_detalle td on cast(t.tramite as int)=td.id where t.id=".$request->get('id'));
        $query->execute();
        $td = $query->fetchAll();
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($td[0]['tramite_detalle_id']);
            if($tramiteDetalle->getTramiteEstado()->getId()==16){

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
                                                ORDER BY wfsol.id DESC limit 1 ");
        $query->execute();
        $infoUE = $query->fetch();
       //dump($infoUE);die;
        $gestion    = $infoUE['gestion_id'];
        $institucion  = $infoUE['institucioneducativa_id'];
        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
        $query = $repository->createQueryBuilder('inss')
            ->select('max(inss.gestionTipo)')
            ->where('inss.institucioneducativa = :idInstitucion')
            ->setParameter('idInstitucion', $institucion)
            ->getQuery();
        $inss = $query->getResult();
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
        $gestion = 2018;
        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');

        $query = $repository->createQueryBuilder('mins')
            ->select('per.carnet, per.paterno, per.materno, per.nombre')
            ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
            ->where('mins.institucioneducativa = :idInstitucion')
            ->andWhere('mins.gestionTipo = :gestion')
            ->andWhere('mins.cargoTipo = :cargo')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargo', '1')
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

        $especialidadarray = array();

        for($i=0;$i<count($datos[2]['select_especialidad']);$i++) {
            $idespecialidad = $datos[2]['select_especialidad'][$i];
            $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
            $query->execute();
            $especialidad = $query->fetch();
            $especialidadarray[] = array('id' => $especialidad['id'], 'especialidad' => $especialidad['especialidad']);
            $especialidadifno[$i] = $idespecialidad;
        }
        /*if($datos[1]=='select_especialidad'){
            $especialidadarray = array();

            for($i=0;$i<count($datos[1]['select_especialidad']);$i++){
                $idespecialidad = $datos[1]['select_especialidad'][$i];
                $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
                $query->execute();
                $especialidad = $query->fetch();
                $especialidadarray[]=array('id'=>$especialidad['id'],'especialidad'=>$especialidad['especialidad'] );
                $especialidadifno[$i] = $idespecialidad;
            }
        }else{
            $especialidadarray = array();

            for($i=0;$i<count($datos[2]['select_especialidad']);$i++){
                $idespecialidad = $datos[2]['select_especialidad'][$i];
                $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
                $query->execute();
                $especialidad = $query->fetch();
                $especialidadarray[]=array('id'=>$especialidad['id'],'especialidad'=>$especialidad['especialidad'] );
                $especialidadifno[$i] = $idespecialidad;
            }
        }*/

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
            $destination_path = 'uploads/archivos/';
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
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos,trm.tramite_tipo
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$id_tramite
                                                ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $tipoTramite = $query->fetch(); //dump($tipoTramite);die;
         if ($tipoTramite['tramite_tipo']==45)
             $id_tipoTramite=45;
         else $id_tipoTramite=46;
          $flujotipo        = 7;//SOLICITUDBTH
          $tarea            = 35;//RECEPCIONA
          $tabla            = 'institucioneducativa';
          //$id_tipoTramite   = 45;//Registro Nuevo
          $obs           = $request->get('obstxt');
          $datos = json_decode($request->get('ipt'));
          array_push($datos, $imagen);
          $datos = json_encode($datos);
          //ELABORA INFORME
          $tarea1            = 36;//elaborainfrorme y envia BTH
        $wfTramiteController = new WfTramiteController();
        $wfTramiteController->setContainer($this->container);
          try{
              $mensaje = $wfTramiteController->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea,$tabla,$institucionid,$obs,$evaluacion,$id_tramite,$datos,'',$id_distrito);
               //dump($mensaje);die;
              if ($evaluacion=='SI'){
                  /*dump($id_usuario);dump($tarea1);dump($id_tramite);dump($id_rol);dump($flujotipo);
                  dump($tabla);dump($datos);dump($id_distrito);die;*/
                  $mensaje = $wfTramiteController->guardarTramiteRecibido($id_usuario, $tarea1,$id_tramite);
                  $mensaje = $wfTramiteController->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea1,$tabla,$institucionid,'','',$id_tramite,$datos,'',$id_distrito);
                 // dump($mensaje);die;
              }

              $res = 1;
          }
          catch (Exception $exceptione){
              $res = 0;
          }
          return  new Response($res);
      }
    public function FormularioBTHDisAction(Request $request){
        //dump( $request);die;
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
        $gestion    = $infoUE['gestion_id'];
        $institucion  = $infoUE['institucioneducativa_id'];
        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
        $query = $repository->createQueryBuilder('inss')
            ->select('max(inss.gestionTipo)')
            ->where('inss.institucioneducativa = :idInstitucion')
            ->setParameter('idInstitucion', $institucion)
            ->getQuery();
        $inss = $query->getResult();
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
        $gestion = 2018;
        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $repository->createQueryBuilder('mins')
            ->select('per.carnet, per.paterno, per.materno, per.nombre')
            ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
            ->where('mins.institucioneducativa = :idInstitucion')
            ->andWhere('mins.gestionTipo = :gestion')
            ->andWhere('mins.cargoTipo = :cargo')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargo', '1')
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
        $documento= empty($datos[4])?'':$datos[4];
         $especialidadarray = array();
         for($i=0;$i<count($datos[1]['select_especialidad']);$i++){
             $idespecialidad = $datos[1]['select_especialidad'][$i];
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
    public function SolicitidBTHDepAction(Request $request){
        $id_rol= $this->session->get('roluser');
        $id_usuario= $this->session->get('userId');
        $TramiteController = new TramiteRueController();
        $TramiteController->setContainer($this->container);
        $lista = $TramiteController->tramiteTarea(36,37,7,$id_usuario,$id_rol,'');
        return $this->render('SieHerramientaBundle:SolicitudBTH:inicioDepartamental.html.twig',array('lista_tramites'=>$lista['tramites']));
    }
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
        $gestion = 2018;
        $repository = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $repository->createQueryBuilder('mins')
            ->select('per.carnet, per.paterno, per.materno, per.nombre')
            ->innerJoin('SieAppWebBundle:Persona', 'per', 'WITH', 'mins.persona = per.id')
            ->where('mins.institucioneducativa = :idInstitucion')
            ->andWhere('mins.gestionTipo = :gestion')
            ->andWhere('mins.cargoTipo = :cargo')
            ->setParameter('idInstitucion', $institucion)
            ->setParameter('gestion', $gestion)
            ->setParameter('cargo', '1')
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
            $destination_path = 'uploads/archivos/';
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
        $idtramite     = $request->get('id_tramite');
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos,trm.tramite_tipo
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$idtramite
                                                ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $infoUE = $query->fetch();

        if ($infoUE['tramite_tipo']==45)
            $id_tipoTramite=45;
        else $id_tipoTramite=46;
        $datos = json_decode($request->get('ipt'));
        array_push($datos, $imagen);
        $datos = json_encode($datos);
        $obs            = $request->get('obstxt');
        $flujotipo      = 7;//SOLICITUDBTH
        $tarea          = 37;//RECEPCIONA Y VERIFICA DEPARTAMENTO
        $tarea1         = 38;//elaborainfrorme y envia BTH DEPARTAMENTO
        $tabla          = 'institucioneducativa';


        $wfTramiteController = new WfTramiteController();
        $wfTramiteController->setContainer($this->container);

        try{
            $mensaje = $wfTramiteController->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea,$tabla,$institucionid,$obs,$evaluacion,$idtramite,$datos,'',$id_distrito);

            if ($evaluacion=='SI')
            {

                $mensaje = $wfTramiteController->guardarTramiteRecibido($id_usuario, $tarea1,$idtramite);
                $mensaje = $wfTramiteController->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea1,$tabla,$institucionid,'','',$idtramite,$datos,'',$id_distrito);

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
                $query = $em->getConnection()->prepare("SELECT * from institucioneducativa  ie 
                                                        INNER JOIN institucioneducativa_sucursal ies on ies.institucioneducativa_id= ie.id
                                                        WHERE ie.id = $institucionid and ies.gestion_tipo_id= $gestiontipo ");
                $query->execute();
                $datosUe  = $query->fetch();

                /*Una vez finalizado el tramite se registra el la tabla correspondiente 45 = Nuevo Registro 46 = Ratificacion*/
                if($id_tipoTramite== 45){
                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_humanistico_tecnico');")->execute();
                    $entity = new InstitucioneducativaHumanisticoTecnico();
                    $em->getRepository('SieAppWebBundle:GradoTipo')->find(3);
                    $entity->setGestionTipoId($this->session->get('currentyear'));
                    $entity->setInstitucioneducativaId($institucionid);
                    $entity->setInstitucioneducativa($datosUe['institucioneducativa']);
                    $entity->setEsimpreso(false);
                    $entity->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find(3));
                    $entity->setFechaCreacion(new \DateTime($infoUE['fecha_tramite']));
                    $entity->setInstitucioneducativaHumanisticoTecnicoTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnicoTipo')->find(7));
                    $em->persist($entity);
                    $em->flush();
                    for($i=0;$i<count($datos[1]['select_especialidad']);$i++){
                        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_especialidad_tecnico_humanistico');")->execute();
                        $entity = new InstitucioneducativaEspecialidadTecnicoHumanistico();
                        $idespecialidad = $datos[1]['select_especialidad'][$i];
                        $espe = $em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($idespecialidad);
                        $ue = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucionid);
                        $gestiontipo = $em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear'));
                        $entity->setInstitucioneducativa($ue);
                        $entity->setEspecialidadTecnicoHumanisticoTipo($espe);
                        $entity->setGestionTipo($gestiontipo);
                        $entity->setFechaRegistro(new \DateTime($infoUE['fecha_tramite']) );
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


                    $query = $em->getConnection()->prepare("SELECT  MAX(ieht.gestion_tipo_id) as gestion
                                                             FROM institucioneducativa_humanistico_tecnico ieht  
                                                             WHERE ieht.institucioneducativa_id=$institucionid");
                    $query->execute();
                    $ultima_gestion_ue = $query->fetchAll();
                    $ultima_gestion = $ultima_gestion_ue[0]['gestion'];

                    $gestion_actual =  $request->getSession()->get('currentyear');
                     if ($ultima_gestion<$gestion_actual){
                         /**
                          * Si la UE ya se encuentra como BTH en alguna gestion pasada se hace un nuevo registro con la gestion actual
                          */
                         $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_humanistico_tecnico');")->execute();
                         /**
                          * Se obtiene informacion de la UE
                          */
                         $query = $em->getConnection()->prepare("SELECT ieht.grado_tipo_id AS  grado_tipo_id
                                                             FROM institucioneducativa_humanistico_tecnico ieht  
                                                             WHERE ieht.institucioneducativa_id=$institucionid AND ieht.gestion_tipo_id = $ultima_gestion ");
                         $query->execute();
                         $grado_ue = $query->fetch();
                         if($grado_ue['grado_tipo_id']<6){
                             $grado_tipo_id=$grado_ue['grado_tipo_id']+1;
                         }else{
                             $grado_tipo_id=6;
                         }

                         $entity = new InstitucioneducativaHumanisticoTecnico();
                         $entity->setGestionTipoId($this->session->get('currentyear'));
                         $entity->setInstitucioneducativaId($institucionid);
                         $entity->setInstitucioneducativa($datosUe['institucioneducativa']);
                         $entity->setEsimpreso(false);
                         $entity->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($grado_tipo_id));
                         $entity->setFechaCreacion(new \DateTime($infoUE['fecha_tramite']));
                         $entity->setInstitucioneducativaHumanisticoTecnicoTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnicoTipo')->find(7));
                         $em->persist($entity);
                         $em->flush();
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
                             $entity->setFechaRegistro(new \DateTime($infoUE['fecha_tramite']) );
                             $em->persist($entity);
                             $em->flush();
                         }

                     }elseif ($ultima_gestion=$gestion_actual){
                         $query = $em->getConnection()->prepare("SELECT ieht.institucioneducativa_id,ieht.grado_tipo_id,* FROM institucioneducativa_humanistico_tecnico ieht 
                                                        WHERE ieht.institucioneducativa_id = $institucionid AND ieht.gestion_tipo_id = $gestiontipo");
                         $query->execute();
                         $instucioneducativa = $query->fetch();
                         $id_ue=$instucioneducativa['id'];
                         $instucioneducativaHt = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->find($id_ue);
                         $instucioneducativaHt->setEsimpreso(true);
                         $em->persist($instucioneducativaHt);
                         $em->flush();
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
                             $entity->setFechaRegistro(new \DateTime($infoUE['fecha_tramite']) );
                             $em->persist($entity);
                             $em->flush();
                         }
                     }
                }

            }
            $res = 1;
        }
        catch (Exception $exceptione){
            $res = 0;
        }
        return  new Response($res);
    }




}
