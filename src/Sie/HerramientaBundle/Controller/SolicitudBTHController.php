<?php

namespace Sie\HerramientaBundle\Controller;

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
use Sie\ProcesosBundle\Controller\WfTramiteController;
use Sie\AppWebBundle\Entity\InstitucioneducativaEspecialidadTecnicoHumanistico;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnicoTipo;
use Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo;
use Sie\AppWebBundle\Entity\GestionTipo;




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
    public function indexAction (Request $request) {

        //get the session's values
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
        //dump($gestion);die;
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
        //dump($lista);die;
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
                        inss.direccion,
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
    public function guardasolicitudAction(Request $request){
//dump($request);die;
        $id_Institucion = $request->get('institucionid');
        $gestion =  $request->getSession()->get('currentyear');
       // dump($id_Institucion); dump($gestion);die;
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT ieht.id,ieht.gestion_tipo_id,ieht.gestion_tipo_id FROM institucioneducativa_humanistico_tecnico ieht  
                                                WHERE ieht.institucioneducativa_id=$id_Institucion AND ieht.gestion_tipo_id = $gestion 
                                                ORDER BY 1");
        $query->execute();
        $instucioneducativa = $query->fetchAll();
        $cant = count($instucioneducativa);
        //dump($cant);die;




        if( $request->get('idsolicitud')==45   ){
            if($cant==0){
                $id_rol         = $this->session->get('roluser');
                $id_usuario     = $this->session->get('userId');
                $id_Institucion = $request->get('institucionid');
                $id_distrito    = $request->get('id_distrito');
                $flujotipo      = 7;//SOLICITUDBTH
                $tarea          = 34;//Solicita BTH
                $tabla          = 'institucioneducativa';
                /* if($request->get('idsolicitud')== 45)
                     $id_tipoTramite = 45;//Registro Nuevo
                 else*/
                $id_tipoTramite = 45;//Registro Nuevo
                $informacion    = json_decode($request->get('ipt',true)) ;

                if ($request->get('id_tramite'))
                {$idTramite=$request->get('id_tramite');}
                else{
                    $idTramite='';
                }
                $TramiteController = new TramiteRueController();
                $TramiteController->setContainer($this->container);
                $datos = ($request->get('ipt'));
                $mensaje = $TramiteController->guardarTramiteDetalle($id_usuario,'',$id_rol,$flujotipo,$tarea,$tabla,$id_Institucion,'',$id_tipoTramite,'',$idTramite,$datos,$id_distrito);
                $res = 1;

            }
            else{
                $res = 2;//no puede como nuevo
            }


        }
        else{

            if($cant>0){
                $id_rol         = $this->session->get('roluser');
                $id_usuario     = $this->session->get('userId');
                $id_Institucion = $request->get('institucionid');
                $id_distrito    = $request->get('id_distrito');
                $flujotipo      = 7;//SOLICITUDBTH
                $tarea          = 34;//Solicita BTH
                $tabla          = 'institucioneducativa';
                /* if($request->get('idsolicitud')== 45)
                     $id_tipoTramite = 45;//Registro Nuevo
                 else*/
                $id_tipoTramite = 46;//Ratificacion
                $informacion    = json_decode($request->get('ipt',true)) ;

                if ($request->get('id_tramite'))
                {$idTramite=$request->get('id_tramite');}
                else{
                    $idTramite='';
                }
                $TramiteController = new TramiteRueController();
                $TramiteController->setContainer($this->container);
                $datos = ($request->get('ipt'));
                $mensaje = $TramiteController->guardarTramiteDetalle($id_usuario,'',$id_rol,$flujotipo,$tarea,$tabla,$id_Institucion,'',$id_tipoTramite,'',$idTramite,$datos,$id_distrito);
                $res = 1;

            }else{
                $res = 3;
            }
        }

        return  new Response($res);
    }
    public function imprimirDirectorAction(Request $request){

        $tramite_id = $request->get('tramite_id');
        $idUE       = $request->get('idUE');

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal');
        $query = $repository->createQueryBuilder('inss')
            ->select('max(inss.gestionTipo)')
            ->where('inss.institucioneducativa = :idInstitucion')
            ->setParameter('idInstitucion', $idUE )
            ->getQuery();
        $inss = $query->getResult();
        $gestion = $inss[0][1];

        $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
            ->select('wf')
            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = wf.tramite')
            ->where('t.id =' . $tramite_id)
            ->getQuery()
            ->getResult();
        $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
        $documento= $datos;

        //dump($datos[2]['select_especialidad']);die;

        $arch = 'FORMULARIO_'.$request->get('idUE').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'rie_cert_certificadottec_v2_afv.rptdesign&idUE='.$idUE.'&gestion='.$gestion.'&idtramite='.$tramite_id.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    public function formularioDirectorAction(Request $request){
       // dump( $request);die;
        $id_tramite = $request->get('id_tramite');//ID de Tramite

        /*
         * Obtenemios la informacion de la UE
         * */

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos,trm.tramite_tipo
                                                FROM tramite trm INNER JOIN wf_solicitud_tramite wfsol ON trm.id=wfsol.tramite_id
                                                WHERE wfsol.tramite_id=$id_tramite");
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
            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = wf.tramite')
            ->where('t.id =' . $id_tramite)
            ->getQuery()
            ->getResult();
        //dump($wfSolicitudTramite);die;
        $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
        //dump($datos[2]['select_especialidad']);die;
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
        //$especialidadifno = ();
        for($i=0;$i<count($datos[2]['select_especialidad']);$i++){
            $idespecialidad = $datos[2]['select_especialidad'][$i];
            $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
            $query->execute();
            $especialidad = $query->fetch();
            $lista_especialidadRegNuearray[]=array('id'=>$especialidad['id'],'especialidad'=>$especialidad['especialidad'] );
            $especialidadifno[$i] = $idespecialidad;
           // $especialidadifno.array_push($idespecialidad);
        }
     // dump($especialidadifno);die;
        $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth ORDER BY 1 ");
        $query->execute();
        $especialidadlista = $query->fetchAll();
       // dump($especialidad);die;
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
                    'informe'=>$informe,
                    'id_tramite'=>$id_tramite,
                    'documento'=>$documento,
                    'tipoTramite'=>$tipoTramite
                ));


    }
    public function ListaEspecialidadesAction(Request $request){
        $id_institucion = $request->get('institucionid');
        $gestion =  $request->getSession()->get('currentyear');
        $em = $this->getDoctrine()->getManager();
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
    }
//Distrital
    public  function SolicitidBTHDisAction(Request $request){
        $id_rol= $this->session->get('roluser');
        $id_usuario= $this->session->get('userId');
        $TramiteController = new TramiteRueController();
        $TramiteController->setContainer($this->container);

        $lista = $TramiteController->tramiteTarea(34,35,7,$id_usuario,$id_rol,'');
        // dump($lista);die;
        //$dia = 5;
        return $this->render('SieHerramientaBundle:SolicitudBTH:inicioDistrital.html.twig',array('lista_tramites'=>$lista['tramites']));
    }
    public function  VerSolicitudBTHDisAction(Request $request){

        $id_tramite = $request->get('lista_tramites_id');//ID de Tramite

       /*
        * Obtenemios la informacion de la UE
        * */

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos
                                                FROM tramite trm INNER JOIN wf_solicitud_tramite wfsol ON trm.id=wfsol.tramite_id
                                                WHERE wfsol.tramite_id=$id_tramite");
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
        $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($institucion, $gestion);
        $notaTipo = $em->getRepository('SieAppWebBundle:NotaTipo')->findOneById($operativo);
        $control_operativo_menus = $em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus')->findOneBy(array('gestionTipoId' => $gestion, 'institucioneducativa' => $institucion, 'notaTipo' => $notaTipo));
        if($control_operativo_menus) {
            if($control_operativo_menus->getEstadoMenu() == 1) {
                return $this->redirect($this->generateUrl('sie_app_web_close_modules_index', array(
                    'sie' => $institucion->getId(),
                    'gestion' => $gestion,
                    'operativo' => $operativo
                )));
            }
        }

        $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
            ->select('wf')
            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = wf.tramite')
            ->where('t.id =' . $id_tramite)
            ->getQuery()
            ->getResult();
        $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
        //dump($datos);die;
        $informe= $datos[0]['informe'];
        $especialidadarray = array();
        for($i=0;$i<count($datos[2]['select_especialidad']);$i++){
            $idespecialidad = $datos[2]['select_especialidad'][$i];
            $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
            $query->execute();
            $especialidad = $query->fetch();
            $especialidadarray[]=array('id'=>$especialidad['id'],'especialidad'=>$especialidad['especialidad'] );
            $especialidadifno[$i] = $idespecialidad;
        }
        return $this->render('SieHerramientaBundle:SolicitudBTH:SolicitudBTHDis.html.twig',array(
            'ieducativa' => $infoUe,
            'institucion' => $institucion,
            'gestion' => $gestion,
            'ubicacion' => $ubicacionUe,
            'director' => $director,
            'cursos'   => $cursos,
            'maestros'=>$maestros,
            'especialidadarray'=>$especialidadarray,
            'informe'=>$informe,
            'id_tramite'=>$id_tramite
            ));

    }
    public function  guardasolicitudDepAction(Request $request){
        //dump($request);die;
        $documento = $request->files->get('docpdf');
        if(!empty($documento)){
            $destination_path = 'uploads/archivos/';
            $imagen = date('YmdHis').'.'.$documento->getClientOriginalExtension();
            $documento->move($destination_path, $imagen);
        }else{
            $imagen='default-2x.pdf';
        }
           //dump($request);die;

        $em = $this->getDoctrine()->getManager();


          $id_rol= $this->session->get('roluser');
          $id_usuario= $this->session->get('userId');
          $institucionid    = $request->get('institucionid');
          $id_distrito      = $request->get('id_distrito');
          $evaluacion       = $request->get('evaluacion');
          $id_tramite       = $request->get('id_tramite');
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos,trm.tramite_tipo
                                                FROM tramite trm INNER JOIN wf_solicitud_tramite wfsol ON trm.id=wfsol.tramite_id
                                                WHERE wfsol.tramite_id=$id_tramite");
        $query->execute();
        $tipoTramite = $query->fetch();
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

          $TramiteController = new TramiteRueController();
          $TramiteController->setContainer($this->container);
          //$mensaje = $TramiteController->guardarTramiteDetalle($id_usuario,'',$id_rol,$flujotipo,$tarea,$tabla,$institucionid,'',$id_tipoTramite,$evaluacion,$id_tramite ,$datos,$id_distrito);

          try{
              $mensaje = $TramiteController->guardarTramiteDetalle($id_usuario,'',$id_rol,$flujotipo,$tarea,$tabla,$institucionid,$obs,$id_tipoTramite,$evaluacion,$id_tramite ,$datos,$id_distrito);
              if ($evaluacion=='SI')
              $mensaje = $TramiteController->guardarTramiteDetalle($id_usuario,'',$id_rol,$flujotipo,$tarea1,$tabla,$institucionid,'',$id_tipoTramite,'',$id_tramite ,$datos,$id_distrito);
              $res = 1;
          }
          catch (Exception $exceptione){
              $res = 0;
          }

          return  new Response($res);

      }
    function FormularioBTHDisAction(Request $request){
        //dump( $request);die;
        $id_tramite = $request->get('lista_tramites_id');//ID de Tramite

        /*
         * Obtenemios la informacion de la UE
         * */

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos
                                                FROM tramite trm INNER JOIN wf_solicitud_tramite wfsol ON trm.id=wfsol.tramite_id
                                                WHERE wfsol.tramite_id=$id_tramite");
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
        $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($institucion, $gestion);



        $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
            ->select('wf')
            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = wf.tramite')
            ->where('t.id =' . $id_tramite)
            ->getQuery()
            ->getResult();
        //dump($wfSolicitudTramite);die;
        $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
       // dump($datos);die;
        $informe= $datos[0]['informe'];
        $documento= empty($datos[4])?'':$datos[4];

         $especialidad   = $datos[1]['select_especialidad'];
        //dump($especialidad); die;

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
    public  function SolicitidBTHDepAction(Request $request){
        $id_rol= $this->session->get('roluser');
        $id_usuario= $this->session->get('userId');
        $TramiteController = new TramiteRueController();
        $TramiteController->setContainer($this->container);
        // $lista = $TramiteController->tramiteTarea(34,34,7,$id_usuario,$id_rol,$ie_id);
        $lista = $TramiteController->tramiteTarea(36,37,7,$id_usuario,$id_rol,'');
        return $this->render('SieHerramientaBundle:SolicitudBTH:inicioDepartamental.html.twig',array('lista_tramites'=>$lista['tramites']));
    }
    public function VerSolicitudBTHDepAction(Request $request){
        //dump($request);die;
        $id_tramite = $request->get('lista_tramites_id');//ID de Tramite

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos
                                                FROM tramite trm INNER JOIN wf_solicitud_tramite wfsol ON trm.id=wfsol.tramite_id
                                                WHERE wfsol.tramite_id=$id_tramite");
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
        $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($institucion, $gestion);


        $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
            ->select('wf')
            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = wf.tramite')
            ->where('t.id =' . $id_tramite)
            ->getQuery()
            ->getResult();
        //dump($wfSolicitudTramite);die;
        $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
        //dump($datos);die;
        $informe= $datos[0]['informe'];
        $documento= empty($datos[4])?'':$datos[4];
        //$especialidad   = $datos[2]['select_especialidad'];
        //dump($datos[2]['select_especialidad'][3]);die;
        //
        $especialidadarray = array();
      //  dump($datos[2]['select_especialidad']);die;
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
        //dump($request);die;
        $documento = $request->files->get('docpdf');
        if(!empty($documento)){
            $destination_path = 'uploads/archivos/';
            $imagen = date('YmdHis').'.'.$documento->getClientOriginalExtension();
            $documento->move($destination_path, $imagen);
        }else{
            $imagen='default-2x.pdf';
        }
        //dump($request);die;
        $em = $this->getDoctrine()->getManager();
        $id_rol         = $this->session->get('roluser');
        $id_usuario     = $this->session->get('userId');
        $institucionid  = $request->get('institucionid');
        $id_distrito    = $request->get('id_distrito');
        $evaluacion     = $request->get('evaluacion');
        $idtramite     = $request->get('id_tramite');
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos,trm.tramite_tipo
                                                FROM tramite trm INNER JOIN wf_solicitud_tramite wfsol ON trm.id=wfsol.tramite_id
                                                WHERE wfsol.tramite_id=$idtramite");
        $query->execute();
        $tipoTramite = $query->fetch();
        if ($tipoTramite['tramite_tipo']==45)
            $id_tipoTramite=45;
        else $id_tipoTramite=46;

        $datos = json_decode($request->get('ipt'));
        array_push($datos, $imagen);
        $datos = json_encode($datos);
        $obs            = $request->get('obstxt');
        $flujotipo      = 7;//SOLICITUDBTH
        $tarea          = 37;//RECEPCIONA Y VERIFICA DEPARTAMENTO
        $tabla          = 'institucioneducativa';
        //$id_tipoTramite = 45;//Registro Nuevo

        $TramiteController = new TramiteRueController();
        $TramiteController->setContainer($this->container);

        try{
                              //rdarTramiteDetalle($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$tipotramite,$varevaluacion,$idtramite,$datos,$lugarTipo_id)
            $mensaje = $TramiteController->guardarTramiteDetalle($id_usuario,'',$id_rol,$flujotipo,$tarea,$tabla,$institucionid,$obs,$id_tipoTramite,$evaluacion,$idtramite ,$datos,$id_distrito);

            $res = 1;
        }
          catch (Exception $exceptione){
            $res = 0;
        }
          return  new Response($res);
    }
    function  SolicitidBTHDeplistaImprimeAction(Request $request){

        $id_rol= $this->session->get('roluser');
        $id_usuario= $this->session->get('userId');

        $TramiteController = new TramiteRueController();
        $TramiteController->setContainer($this->container);
        // $lista = $TramiteController->tramiteTarea(34,34,7,$id_usuario,$id_rol,$ie_id);
        $lista = $TramiteController->tramiteTarea(37,38,7,$id_usuario,$id_rol,'');

        return $this->render('SieHerramientaBundle:SolicitudBTH:inicioDepartamental_imprime.html.twig',array('lista_tramites'=>$lista['tramites']));

    }
    function SolicitidBTHDepImprimeAction(Request $request){
 //dump($request);die;
        $id_tramite     = $request->get('id_tramite');
        $gestion= $this->session->get('currentyear');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos,wfsol.lugar_tipo_id,trm.tramite_tipo
                                                FROM tramite trm INNER JOIN wf_solicitud_tramite wfsol ON trm.id=wfsol.tramite_id
                                                WHERE wfsol.tramite_id=$id_tramite");
        $query->execute();
        $infoUE = $query->fetch();
//dump($infoUE['datos']);die;

        $id_rol         = $this->session->get('roluser');
        $id_usuario     = $this->session->get('userId');
        $institucionid  = $request->get('codUe');
        $id_distrito    = $infoUE['lugar_tipo_id'];
        $evaluacion     = '';

        $datosdep          = $infoUE['datos'];
        $flujotipo      = 7;//SOLICITUDBTH
        $tarea          = 38;//RECEPCIONA Y VERIFICA DEPARTAMENTO
        $tabla          = 'institucioneducativa';

        if ($infoUE['tramite_tipo']==45)
            $id_tipoTramite=45;
        else $id_tipoTramite=46;

        $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
            ->select('wf')
            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = wf.tramite')
            ->where('t.id =' . $id_tramite)
            ->getQuery()
            ->getResult();
        $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
        $TramiteController = new TramiteRueController();
        $TramiteController->setContainer($this->container);

        try{
            $mensaje = $TramiteController->guardarTramiteDetalle($id_usuario,'',$id_rol,$flujotipo,$tarea,$tabla,$institucionid,'',$id_tipoTramite,$evaluacion,$id_tramite ,$datosdep,$id_distrito);

            /*Una vez finalizado el tramite se registra el la tabla correspondiente 45 = Nuevo Registro 46 = Ratificacion*/
            if($id_tipoTramite== 45){
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_humanistico_tecnico');")->execute();
            $entity = new InstitucioneducativaHumanisticoTecnico();
            $em->getRepository('SieAppWebBundle:GradoTipo')->find(3);
            $entity->setGestionTipoId($this->session->get('currentyear'));
            $entity->setInstitucioneducativaId($institucionid);
            $entity->setInstitucioneducativa($request->get('nombreUE'));
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
            else{
                $query = $em->getConnection()->prepare("SELECT ieht.institucioneducativa_id,ieht.grado_tipo_id,* FROM institucioneducativa_humanistico_tecnico ieht 
                                                        WHERE ieht.institucioneducativa_id = $institucionid AND ieht.gestion_tipo_id = $gestion");
                $query->execute();
                $instucioneducativa = $query->fetch();
                $id_ue=$instucioneducativa['id'];
                $instucioneducativaHt = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->find($id_ue);
                $instucioneducativaHt->setEsimpreso(true);
                $em->persist($instucioneducativaHt);
                $em->flush();
            }

            $res = 1;

        }
        catch (Exception $exceptione){
            $res = 0;
        }
        return  new Response($res);





    }
 //Seguimiento de Tramites
    public function createSeguimientoformAction()
    {
        $form = $this->createFormBuilder()
            //->setAction($this->generateUrl('flujoproceso_guardar'))
            ->add('tramite','text',array('label'=>'Tramite','required'=>true,'attr' => array('placeholder'=>'Nro. de trámite')))
            ->getForm();

        return $this->render('SieHerramientaBundle:SolicitudBTH:seguimientoTramiteBTH.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    public function verFlujoAction(Request $request )
    {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)){
            return $this->redirect($this->generateUrl('login'));
        }
        //dump($request);die;
        $form = $request->get('form');
        //dump($id);die;
        $data = $this->listarF(7,$form['tramite']);
        //dump($data);die;
        //if (($form['proceso'] == 5 && !$data['nombre']) || ($form['proceso'] == 14 && !$data['nombre_ie']) || ($form['proceso'] == 6 && !$data['estudiante']) || ($form['proceso'] == 7 && !$data['estudiante']))
        if (!$data['nombre'])
        {
            //dump($data['nombre_ie']);die;
            $mensaje = 'Número de tramite es incorrecto';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);
        }
        return $this->render('SieHerramientaBundle:SolicitudBTH:flujoTramitesBTH.html.twig',$data);
    }
    public function listarF($flujotipo,$tramite)
    {
        $em = $this->getDoctrine()->getManager();

        if($flujotipo == 7)
        {
            $query = $em->getConnection()->prepare('select p.id, p.flujo,d.institucioneducativa, p.proceso_tipo, p.orden, p.es_evaluacion,p.variable_evaluacion, p.condicion, p.nombre,d.valor_evaluacion, p.condicion_tarea_siguiente, p.plazo, p.tarea_ant_id, p.tarea_sig_id, p.rol_tipo_id,d.id as td_id,d.tramite_id,d.obs, d.flujo_proceso_id,d.fecha_registro,d.usuario_remitente_id,d.usuario_destinatario_id,d.datos
        from
        (SELECT 
          fp.id, f.flujo, p.proceso_tipo, fp.orden, fp.es_evaluacion,fp.variable_evaluacion, wftc.condicion, wfc.nombre, wftc.condicion_tarea_siguiente, fp.plazo, fp.tarea_ant_id, fp.tarea_sig_id, fp.rol_tipo_id
        FROM 
          flujo_tipo f join flujo_proceso fp on f.id = fp.flujo_tipo_id
          join proceso_tipo p on p.id = fp.proceso_id
          left join wf_tarea_compuerta wftc on wftc.flujo_proceso_id = fp.id
          left join wf_compuerta wfc on wftc.wf_compuerta_id=wfc.id
        WHERE 
           f.id='. $flujotipo .' order by fp.orden)p
        LEFT JOIN
        (SELECT 
          t1.id,t1.obs,t1.tramite_id, t1.flujo_proceso_id,t1.fecha_registro,t1.usuario_remitente_id,t1.usuario_destinatario_id,i.institucioneducativa,t1.valor_evaluacion
        ,wfsol.datos
        FROM 
          tramite_detalle t1 join tramite t on t1.tramite_id=t.id
          left join wf_solicitud_tramite wfsol on t."id"=wfsol.tramite_id
          join institucioneducativa i on t.institucioneducativa_id=i.id
        where t1.tramite_id='. $tramite .' order by t1.id)d
        ON p.id=d.flujo_proceso_id order by d.id,d.fecha_registro');

        }
        $query->execute();
        $arrData = $query->fetchAll();
        //dump($arrData[0]['datos']);die;
        $data['flujo']=$arrData;
        $data['flujotipo'] = $flujotipo;
        $data['nombre']=$arrData[0]['institucioneducativa'];
        return $data;
    }


}
