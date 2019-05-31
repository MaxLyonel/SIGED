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
class TramiteAdiElimEspecialidadesBTHController extends Controller {
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
        $id_Institucion =  $request->getSession()->get('ie_id');
        $gestion =  $request->getSession()->get('currentyear');
        $flujotipo = $request->get('id');
        $verificarinicioTramite = $this->verificatramite($id_Institucion,$gestion,$flujotipo);
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
            $query = $em->getConnection()->prepare("SELECT tp.id,tp.tramite_tipo FROM tramite_tipo tp WHERE tp.obs='BTH-ESP'");
            $query->execute();
            $tramite_tipo = $query->fetchAll();
            $tramite_tipoArray = array();
            for ($i = 0; $i < count($tramite_tipo); $i++) {
                $tramite_tipoArray[$tramite_tipo[$i]['id']] = trim($tramite_tipo[$i]['tramite_tipo']);
            }
            //Informacion de la Unidad Educativa
            $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
            $query = $repository->createQueryBuilder('ie')
                ->select('ie, ies')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
                ->where('ie.id = :idInstitucion')
                ->andWhere('ies.gestionTipo in (:gestion)')
                ->setParameter('idInstitucion', $id_Institucion)
                ->setParameter('gestion', $gestion)
                ->getQuery();
            $infoUe = $query->getResult();
            //lista de las especialidades
            $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad
                     FROM especialidad_tecnico_humanistico_tipo eth
                     WHERE eth.es_vigente is TRUE AND eth.id NOT in  		
                        (SELECT espt.id
                    FROM institucioneducativa_especialidad_tecnico_humanistico ieth
                    INNER JOIN especialidad_tecnico_humanistico_tipo espt ON ieth.especialidad_tecnico_humanistico_tipo_id = espt.id
                    WHERE ieth.institucioneducativa_id = $id_Institucion AND ieth.gestion_tipo_id = $gestion
                    )ORDER BY 1");
            $query->execute();
            $especialidades = $query->fetchAll();
            //lista de las especialidades de la Unidades Educativas
            $query = $em->getConnection()->prepare("SELECT espt.id,espt.especialidad
                                                   FROM institucioneducativa_especialidad_tecnico_humanistico ieth
                                                   INNER JOIN especialidad_tecnico_humanistico_tipo espt ON ieth.especialidad_tecnico_humanistico_tipo_id = espt.id
                                                   WHERE ieth.institucioneducativa_id = $id_Institucion AND ieth.gestion_tipo_id = $gestion
                                                   ORDER BY 1");
            $query->execute();
            $especialidades_ue = $query->fetchAll();
            $infoUe_distrito = $this->obtieneinforue($id_Institucion,$gestion);

            $form= $this->createFormBuilder()
                ->add('solicitud', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $tramite_tipoArray, 'attr' => array('class' => 'form-control chosen-select','onchange' => 'validarsolicitud()')))
                ->getForm();
            return $this->render('SieProcesosBundle:TramiteAdiElimEspecialidadesBTH:index.html.twig',array( 'form' => $form->createView(),
                'id_institucion'=>$id_Institucion,
                'idflujo'=>$request->get('id'),
                'objespecialidades'=>$especialidades,
                'objespecialidades_ue'=>$especialidades_ue,
                'ieducativa'=>$infoUe,
                'iddistrito'=> $infoUe_distrito['codigo_distrito'],
                //'estado'=>$verificarinicioTramite
                'estado'=>0
            ));
        }

    }
    public function obtieneinforue($id_institucion,$gestion){
        $em = $this->getDoctrine()->getManager();
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
            ->setParameter('idInstitucion', $id_institucion)
            ->setParameter('gestion', $gestion)
            ->getQuery();
        $ubicacionUe = $query->getSingleResult();
        return $ubicacionUe;
        /*$response = new JsonResponse();
        return $response->setData(array('infoUe' => $infoUe, 'ubicacionUe' => $ubicacionUe));*/

    }
    public  function obtieneespecialidaes($id_institucion,$gestion){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT espt.id,espt.especialidad
        FROM institucioneducativa_especialidad_tecnico_humanistico ieth
        INNER JOIN especialidad_tecnico_humanistico_tipo espt ON ieth.especialidad_tecnico_humanistico_tipo_id = espt.id
        WHERE ieth.institucioneducativa_id = $id_institucion AND ieth.gestion_tipo_id = $gestion
        ORDER BY 1");
        $query->execute();
        $especialidades = $query->fetchAll();
        return $especialidades;

    }
    public function validariniciosolicitudespecialidadesbthAction(Request $request){
       /**
        * Verifica si el director ya inicio su tramite de  una salicitud X
        */
        $idinstitucion  = $request->get('institucionid');
        $idsolicitud    = $request->get('idsolicitud'); //dump($idsolicitud);die;
        $solicitud      = $request->get('solicitud');
        $idflujotipo    = $request->get('idflujotipo');
        $gestion        =  $request->getSession()->get('currentyear');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT COUNT(tr.id)AS  cantidad_tramite_bth FROM tramite tr  
                                                    WHERE tr.flujo_tipo_id = $idflujotipo AND tr.institucioneducativa_id = $idinstitucion
                                                    AND tr.gestion_id = $gestion and tr.tramite_tipo= $idsolicitud" );
        $query->execute();
        $tramite = $query->fetchAll();
        $tramite_iniciado=$tramite[0]['cantidad_tramite_bth'];
        if((int)$tramite_iniciado==0){
            $respuesta = 0;
        }else{
            if($solicitud=='Adicionar Especialidades'){
                $respuesta = 1;
            }else{
                $respuesta = 2;
            }
        }
        return new JsonResponse(array('respuesta' => $respuesta));
    }
    public function verificatramite($id_Institucion,$gestion,$flujotipo){
        /**
         * Verificicacion de que la UE inicio un tramite
         */
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT COUNT(tr.id)AS  cantidad_tramite_bth FROM tramite tr  
                                                    WHERE tr.flujo_tipo_id = $flujotipo AND tr.institucioneducativa_id = $id_Institucion
                                                    AND tr.gestion_id = $gestion");
        $query->execute();
        $tramite_ue = $query->fetchAll(); //dump($tramite_ue);die;
        $estado = 0;
        $tramite_iniciado=$tramite_ue[0]['cantidad_tramite_bth'];
        if($tramite_iniciado==0){
            /**
             * Verificar si la unidad educativa que inicio el tramite que la UE sea plena de grado 5-6
             */
            $query = $em->getConnection()->prepare("SELECT count(*) as ue_plena FROM institucioneducativa_humanistico_tecnico iht   
            WHERE iht.institucioneducativa_id = $id_Institucion AND grado_tipo_id in(5,6) 
            AND iht.institucioneducativa_humanistico_tecnico_tipo_id=1 AND gestion_tipo_id =$gestion");
            $query->execute();
            $ue_plena = $query->fetchAll();
            if($ue_plena < 1) { //la Unidad educativa no es plena
                $estado  = 1; //
            }
        }else{
            $estado  = 2; //tramite Iniciado
            return $estado;
        }
        return $estado;
    }
    public function guardasolicitudEspecialidadesAction(Request $request){
        $iddistrito     = $request->get('iddistrito');
        $idinstitucion  = $request->get('institucionid');
        $idsolicitud    = $request->get('idsolicitud');
        $solicitud      = $request->get('solicitud');
        $idflujotipo    = $request->get('idflujotipo');
        $sw             = $request->get('sw');
        $datos          = json_encode($request->get('ipt'));
        $gestion        =  $request->getSession()->get('currentyear');
        //$datos          = json_decode($ipt);
        $em = $this->getDoctrine()->getManager();
            /**
             * Verificicacion de que la UE inicio un tramite
             */
            $query = $em->getConnection()->prepare("SELECT COUNT(tr.id)AS  cantidad_tramite_bth FROM tramite tr  
                                                    WHERE tr.flujo_tipo_id = $idflujotipo AND tr.institucioneducativa_id = $idinstitucion
                                                    AND tr.gestion_id = $gestion");
            $query->execute();
            $tramite_ue = $query->fetchAll();
            $tramite_iniciado = $tramite_ue[0]['cantidad_tramite_bth'];
            /**
             * Verifiacion del usuario como director y vigente para la gestion actual
             */
            $id_rol         = $this->session->get('roluser');
            $id_Institucion = $request->get('institucionid');
            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id_Institucion);
            $query = $em->getConnection()->prepare("select u.* from maestro_inscripcion m
                        join usuario u on m.persona_id=u.persona_id
                        where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=".(new \DateTime())->format('Y')." and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
            $query->execute();
            $uDestinatario = $query->fetchAll();
            if($uDestinatario){
                $id_usuario = $uDestinatario[0]['id'];
            }else{
                $mensaje="Verificar si el Director de la Unidad Educativa es vigente";
                return  new JsonResponse(array('estado' => 4, 'msg' => $mensaje));
            }
        if( $solicitud == 'Adicionar Especialidades' ){//////////verificar validacion de tramite
            if((int)$tramite_iniciado < 1){ //si es <1 no tiene tramites iniciados para este flujo en la gestion actual
                    /**
                     * Obtenemos el flujo_proceso y obtenemos la tarea
                     */
                    $flujoproceso   = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $idflujotipo , 'orden' => 1));
                    $tarea          = $flujoproceso->getId();// Solicita BTH
                    $tabla          = 'institucioneducativa';
                    $id_tipoTramite = $idsolicitud; //Adicion de especialidades
                    $idTramite='';
                    if($sw == 0){//primer envio de solicitud
                        $mensaje = $this->get('wftramite')->guardarTramiteNuevo($id_usuario,$id_rol,$idflujotipo,$tarea,$tabla,$id_Institucion,'',$id_tipoTramite,'',$idTramite,$datos,'',$iddistrito);
                    }
                    else{ //devuelto
                        $idTramite = $request->get('id_tramite');
                        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$idflujotipo,$tarea,$tabla,$id_Institucion,'','',$idTramite,$datos,'',$iddistrito);
                    }
                    if ($mensaje['dato']==true){
                        $res = 1;//se guardo el tramite
                    }else{
                        $res = 2;//ocurrio error al guardar el trámite
                    }

            }else{
                $res = 3; // tiene tramite iniciado
            }
        }
        /**
         * verificamos el tipo de solicitud Eliminacion de especialidades
         */
        elseif($solicitud == 'Eliminar Especialidades'){
            //PENDIENTEEEEEEEEEE
            $especialidades          = json_decode($datos);
            dump($especialidades);die;
            $verificaespecialidades=$this->verificaespecialidades();
            if($verificaespecialidades== true){
                $flujoproceso   = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $idflujotipo , 'orden' => 1));
                $tarea          = $flujoproceso->getId();// Solicita BTH
                $tabla          = 'institucioneducativa';
                $id_tipoTramite = $idsolicitud; //Eliminacion de especialidades
                $idTramite='';
                if($sw == 0){//primer envio de solicitud
                    $mensaje = $this->get('wftramite')->guardarTramiteNuevo($id_usuario,$id_rol,$idflujotipo,$tarea,$tabla,$id_Institucion,'',$id_tipoTramite,'',$idTramite,$datos,'',$iddistrito);
                }
                else{ //devuelto
                    $idTramite = $request->get('id_tramite');
                    $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$idflujotipo,$tarea,$tabla,$id_Institucion,'','',$idTramite,$datos,'',$iddistrito);
                }
                if ($mensaje['dato']==true){
                    $res = 1;//se guardo el tramite
                }else{
                    $res = 2;//ocurrio error al guardar el trámite
                }
            }else{
                $mensaje="Verificar las especialidades a eliminar";
                return  new JsonResponse(array('estado' => 5, 'msg' => $mensaje));
            }

        }

        if(isset($mensaje['msg'])){
            $mensaje = $mensaje['msg'];
        }else{
            $mensaje = '';
        }
        return  new JsonResponse(array('estado' => $res, 'msg' => $mensaje));
    }
    public function verificaespecialidades(){
        ///PENDIENTE
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
        /// EXTRAER LA ULTIMA GESTION REGISTRADAD COMO BTH ....
        $query = $em->getConnection()->prepare("SELECT gestion_tipo_id as gestion
                                                FROM institucioneducativa_humanistico_tecnico ieht  
                                                WHERE ieht.institucioneducativa_id=$institucion_id
                                                ORDER BY gestion_tipo_id DESC LIMIT 2");
        $query->execute();
        $ultima_gestion_ue = $query->fetchAll();

        if(count($ultima_gestion_ue)==2){
            $gestion_especialidades = $ultima_gestion_ue[1]['gestion'];
        } else{
            $gestion_especialidades = $ultima_gestion_ue[0]['gestion'];
        }

        ////////
        $query = $em->getConnection()->prepare("SELECT espt.id, espt.especialidad,ieth.gestion_tipo_id
                                                FROM institucioneducativa_especialidad_tecnico_humanistico ieth
                                                INNER JOIN especialidad_tecnico_humanistico_tipo espt ON   ieth.especialidad_tecnico_humanistico_tipo_id =  espt.id
                                                WHERE ieth.institucioneducativa_id = $institucion_id AND ieth.gestion_tipo_id = $gestion_especialidades
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

//Distrital
     public function  VerSolicitudEspecialidadBTHDisAction(Request $request){
         $id_tramite = $request->get('id');//ID de Tramite
         $gestion =  $request->getSession()->get('currentyear');
         $em = $this->getDoctrine()->getManager();
         $query = $em->getConnection()->prepare("select td.*
                                                 from tramite t
                                                 join tramite_detalle td on cast(t.tramite as int)=td.id where t.id=".$request->get('id'));
         $query->execute();
         $td = $query->fetchAll();
         $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($td[0]['tramite_detalle_id']);
             if($tramiteDetalle->getTramiteEstado()->getId()==4){//tramite devuelto
                 return $this->redirectToRoute('solicitud_bth_formularioDis',array('lista_tramites_id'=>$request->get('id')));
             }
        /*
         *Obtenemios la informacion de la UE
         * */
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$id_tramite
                                                ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $infoUE = $query->fetch();
         $id_Institucion  = $infoUE['institucioneducativa_id'];
        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
        $query = $repository->createQueryBuilder('ie')
            ->select('ie, ies')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
            //->innerJoin('SieAppWebBundle:DependenciaTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
            ->where('ie.id = :idInstitucion')
            ->andWhere('ies.gestionTipo in (:gestion)')
            ->setParameter('idInstitucion', $id_Institucion)
            ->setParameter('gestion', $gestion)
            ->getQuery();
        $infoUe = $query->getResult();
        $infoUe_distrito = $this->obtieneinforue($id_Institucion,$gestion);

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
        //dump(count($datos['select_especialidad']));die;
        $especialidadarray = array();
        for($i=0;$i<count($datos['select_especialidad']);$i++) {
            $idespecialidad = $datos['select_especialidad'][$i];
            $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
            $query->execute();
            $especialidad = $query->fetch();
            $especialidadarray[] = array('id' => $especialidad['id'], 'especialidad' => $especialidad['especialidad']);

        }
        $especialidades_ue = $this->obtieneespecialidaes($id_Institucion,$gestion);
        //dump($especialidades_ue);die;
        return $this->render('SieProcesosBundle:TramiteAdiElimEspecialidadesBTH:FormularioDisEspecialidades.html.twig',array(
            'id_institucion'=>$id_Institucion,
            'idflujo'=>$request->get('id'),
            'ieducativa'=>$infoUe,
            'iddistrito'=> $infoUe_distrito['codigo_distrito'],
            'objespecialidades_ue'=>$especialidades_ue,
            'objespecialidades_solicitadas'=>$especialidadarray,
            'id_tramite'=>$id_tramite,
            'idespecialidades'=> json_encode($datos['select_especialidad'])
            ));
    }
     public function  guardasolicitudEspecialidadesDisAction(Request $request){
         $datos = array();
         $documento = $request->files->get('docpdf');
        if(!empty($documento)){
            /*$dirtmp = $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $aName[0];
            */
            $destination_path = 'uploads/archivos/flujos/'.$request->get('institucionid').'/bth/';
            if (!file_exists($destination_path)) {
                mkdir($destination_path, 0777);
            }
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
        $flujotipo = $flujoproceso->getFlujoTipo()->getId();
        $tarea   = $flujoproceso->getId();///RECEPCIONA
          $tabla            = 'institucioneducativa';
          $obs              = $request->get('obstxt');
          $datos['institucionid'] = $institucionid;
          $datos['select_especialidad'] = explode(",", $request->get('ipt'));
          $datos['evaluacion'] = $evaluacion;
          $datos['obs'] = $obs;
          $datos['imagen'] = $imagen;
          $datos = json_encode($datos);
          //ELABORA INFORME
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 3));
        $tarea1   = $flujoproceso->getId();//elaborainfrorme y envia BTH
          try{
              $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea,$tabla,$institucionid,$obs,$evaluacion,$id_tramite,$datos,'',$id_distrito);
              if ($evaluacion=='SI'){
                  $mensaje = $this->get('wftramite')->guardarTramiteRecibido($id_usuario, $tarea1,$id_tramite);
                  $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea1,$tabla,$institucionid,'','',$id_tramite,$datos,'',$id_distrito);
              }
              if ($mensaje['dato']==true){
                  $res = 1;
              }else{
                  $res = 4;
              }
          }
          catch (Exception $exceptione){
              $res = 0;
          }

         if(isset($mensaje['msg'])){
             $mensaje = $mensaje['msg'];
         }else{
             $mensaje = '';
         }
         return  new JsonResponse(array('estado' => $res, 'msg' => $mensaje));

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
    public function VerSolicitudEspecialidadesBTHDepAction(Request $request){
        $id_tramite = $request->get('id');//ID de Tramite
        $gestion =  $request->getSession()->get('currentyear');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$id_tramite
                                                ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $infoUE = $query->fetch();
        $id_Institucion  = $infoUE['institucioneducativa_id'];
        /*
        *Obtenemios la informacion de la UE
        * */
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$id_tramite
                                                ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $infoUE = $query->fetch();
        $id_Institucion  = $infoUE['institucioneducativa_id'];

        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');
        $query = $repository->createQueryBuilder('ie')
            ->select('ie, ies')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursal', 'ies', 'WITH', 'ies.institucioneducativa = ie.id')
            //->innerJoin('SieAppWebBundle:DependenciaTipo', 'ft', 'WITH', 'mi.formacionTipo = ft.id')
            ->where('ie.id = :idInstitucion')
            ->andWhere('ies.gestionTipo in (:gestion)')
            ->setParameter('idInstitucion', $id_Institucion)
            ->setParameter('gestion', $gestion)
            ->getQuery();
        $infoUe = $query->getResult();
        $infoUe_distrito = $this->obtieneinforue($id_Institucion,$gestion);
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
        $documento= empty($datos['imagen'])?'':$datos['imagen'];
        $especialidadarray = array();
        for($i=0;$i<count($datos['select_especialidad']);$i++) {
            $idespecialidad = $datos['select_especialidad'][$i];
            $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
            $query->execute();
            $especialidad = $query->fetch();
            $especialidadarray[] = array('id' => $especialidad['id'], 'especialidad' => $especialidad['especialidad']);
        }
        $especialidades_ue = $this->obtieneespecialidaes($id_Institucion,$gestion);

        return $this->render('SieProcesosBundle:TramiteAdiElimEspecialidadesBTH:FormularioDepEspecialidades.html.twig',array(
            'id_institucion'=>$id_Institucion,
            'idflujo'=>$request->get('id'),
            'ieducativa'=>$infoUe,
            'iddistrito'=> $infoUe_distrito['codigo_distrito'],
            'objespecialidades_ue'=>$especialidades_ue,
            'objespecialidades_solicitadas'=>$especialidadarray,
            'id_tramite'=>$id_tramite,
            'idespecialidades'=> json_encode($datos['select_especialidad']),
            'documento'=>$documento
        ));

    }
    public function guardasolicitudEspecialidadesDepAction(Request $request){
        $datos = array();
        $documento = $request->files->get('docpdf');
        if(!empty($documento)){
            /*$dirtmp = $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $aName[0];
            */
            $destination_path = 'uploads/archivos/flujos/'.$request->get('institucionid').'/bth/';
            if (!file_exists($destination_path)) {
                mkdir($destination_path, 0777);
            }
            $imagen = date('YmdHis').'.'.$documento->getClientOriginalExtension();
            $documento->move($destination_path, $imagen);
        }else{
            $imagen='default-2x.pdf';
        }

        $em = $this->getDoctrine()->getManager();
        $id_rol         = $this->session->get('roluser');
        $id_usuario     = $this->session->get('userId');
        $institucionid  = $request->get('institucionid');
        $id_distrito    = (int)$request->get('id_distrito');
        $evaluacion     = $request->get('evaluacion');
        $evaluacion2    = $request->get('evaluacion2');
        $idtramite     = $request->get('id_tramite');
        $obstxt     = $request->get('obstxt');
        //armado de %datos
        $datos['institucionid'] = $institucionid;
        $datos['select_especialidad'] = explode(",", $request->get('ipt'));
        $datos['evaluacion'] = $evaluacion;
        $datos['evaluacion2'] = $evaluacion2;
        $datos['obs'] = $obstxt;
        $datos['imagen'] = $imagen;
        $datos = json_encode($datos);

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($idtramite);
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 4));
        $flujotipo = $flujoproceso->getFlujoTipo()->getId(); //7//SOLICITUDBTH
        $tarea   = $flujoproceso->getId();//RECEPCIONA

        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos,trm.tramite_tipo
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$idtramite
                                                ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $infoUE = $query->fetch();


        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 5));
        $tarea1   = $flujoproceso->getId();//elaborainfrorme y envia BTH DEPARTAMENTO - ORDEN 5
        $tabla          = 'institucioneducativa';

        try{
            $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea,$tabla,$institucionid,$obs,$evaluacion,$idtramite,$datos,'',$id_distrito);

            if ($evaluacion=='SI') {   //dump($tarea1);die;
                $mensaje = $this->get('wftramite')->guardarTramiteRecibido($id_usuario, $tarea1,$idtramite);
                $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea1,$tabla,$institucionid,$obs,'',$idtramite,$datos,'',$id_distrito);
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
                $mensaje = $wfTramiteController->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea2,$tabla,$institucionid,$obs,$evaluacion2,$idtramite,$datos,'',$id_distrito);
                /**
                 * Al dar como respuesta NO se elimina el registro de la UE de la tabla InstitucioneducativaHumanisticoTecnico y
                 * no se registran las especialidades
                 */
                if($evaluacion2 =='NO'){
                    $institucionBth = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId'=>$institucionid,'gestionTipoId'=>$this->session->get('currentyear')));
                    if($institucionBth){
                        $em->remove($institucionBth);
                        $em->flush();
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
