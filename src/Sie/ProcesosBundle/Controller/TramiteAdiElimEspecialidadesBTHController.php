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
        //$flujotipo = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select td.*
                                                from tramite t
                                                join tramite_detalle td on cast(t.tramite as int)=td.id where t.id=".$request->get('id'));
        $query->execute();
        $td = $query->fetchAll();
        if (isset($td[0]['tramite_detalle_id'])){//verifica si el tramite fue devuelto
            $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($td[0]['tramite_detalle_id']);
            if($tramiteDetalle->getTramiteEstado()->getId()== 4){
                $iUE = $tramiteDetalle->getTramite()->getInstitucioneducativa()->getId();
                return $this->redirectToRoute('tramite_solicitud_bth_formulario_dev',array('iUE'=>$iUE,'id_tramite'=>$request->get('id')));
            }
        }else{
            $query = $em->getConnection()->prepare("SELECT tp.id,tp.tramite_tipo FROM tramite_tipo tp WHERE tp.obs='BTH-ESP'");
            $query->execute();
            $tramite_tipo = $query->fetchAll();//tramites del flujo
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
            //lista de las especialidades menos las especialidades que ya tiene la UE
            $obtieneespecialidadesrestantes=$this->obtieneespecialidadesrestantes($id_Institucion,$gestion);
            //lista de las especialidades con las que cuenta la UE
            $especialidades_ue = $this->obtieneespecialidaes($id_Institucion,$gestion);
            $infoUe_distrito = $this->obtieneinforue($id_Institucion,$gestion);
            $form= $this->createFormBuilder()
                ->add('solicitud', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $tramite_tipoArray, 'attr' => array('class' => 'form-control chosen-select','onchange' => 'validarsolicitud()')))
                ->getForm();
            return $this->render('SieProcesosBundle:TramiteAdiElimEspecialidadesBTH:index.html.twig',array( 'form' => $form->createView(),
                'id_institucion'=>$id_Institucion,
                'idflujo'=>$request->get('id'),
                'objespecialidades'=>$obtieneespecialidadesrestantes,
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
    public  function obtieneespecialidaes($id_institucion,$gestion){//obtiene las especialidades de la gestion actual de la UE
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
    public function obtieneespecialidadesrestantes($id_institucion,$gestion){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad
                     FROM especialidad_tecnico_humanistico_tipo eth
                     WHERE eth.es_vigente is TRUE AND eth.id NOT in  		
                        (SELECT espt.id
                    FROM institucioneducativa_especialidad_tecnico_humanistico ieth
                    INNER JOIN especialidad_tecnico_humanistico_tipo espt ON ieth.especialidad_tecnico_humanistico_tipo_id = espt.id
                    WHERE ieth.institucioneducativa_id = $id_institucion AND ieth.gestion_tipo_id = $gestion
                    )ORDER BY 1");
        $query->execute();
        $especialidades_restantes = $query->fetchAll();
        return $especialidades_restantes;
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
            $respuesta = 0;//no inicio tramite de ningun tipo
        }else{
            if($solicitud=='Adicionar Especialidades'){
                $respuesta = 1; //inicio el tramite de ADDEsp
            }else{
                $respuesta = 2;//incio el tramite de Elim ESp
            }
        }
        return new JsonResponse(array('respuesta' => $respuesta));
    }
    public function guardasolicitudEspecialidadesAction(Request $request){ //dump($request);die;
        $esp=$request->get('ipt');
        $especia=$esp['select_especialidad'];
        $iddistrito     = $request->get('iddistrito');
        $idinstitucion  = $request->get('institucionid');
        $idsolicitud    = $request->get('idsolicitud');
        $solicitud      = $request->get('solicitud');
        $idflujotipo    = $request->get('idflujotipo');
        $textDirector   = $request->get('textDirector');
        $sw             = $request->get('sw');
        $datos          = json_encode($request->get('ipt'));


        $gestion        =  $request->getSession()->get('currentyear');
        //$datos          = json_decode($ipt);
        $em = $this->getDoctrine()->getManager();
            /**
             * Verifiacion del usuario como director y vigente para la gestion actual
             */
            $id_rol               = $this->session->get('roluser');
            $id_Institucion       = $request->get('institucionid');
            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id_Institucion);
            $query                = $em->getConnection()->prepare("select u.* from maestro_inscripcion m
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
                    /**
                     * Obtenemos el flujo_proceso y obtenemos la tarea
                     */
                    $flujoproceso   = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $idflujotipo , 'orden' => 1));
                    $tarea          = $flujoproceso->getId();// Solicita Espe.
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
        }
        /**
         * verificamos el tipo de solicitud Eliminacion de especialidades
         */
        elseif($solicitud == 'Eliminar Especialidades'){
            $verificaespecialidades=$this->verificaespecialidades($idinstitucion,$especia,$gestion);
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
    public function verificaespecialidades($sie,$especialida,$gestio){
       // dump($sie);die;
        for($i=0;$i<count($especialida);$i++) {
            $idespe=$especialida[$i];
            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare("SELECT count (*) conteo
                        FROM estudiante_inscripcion a
                        INNER JOIN estudiante_inscripcion_humnistico_tecnico b ON b.estudiante_inscripcion_id = a.id
                        INNER JOIN institucioneducativa_curso c ON a.institucioneducativa_curso_id = c.id
                        where 
                        c.institucioneducativa_id = $sie  and
                        c.gestion_tipo_id = $gestio and
                        b.especialidad_tecnico_humanistico_tipo_id =$idespe");
            $query->execute();
            $contador = $query->fetch();
            if ($contador==0)
            {
                return false;
            }
        }
        return true;

    }
    public function imprimirDirectorAction(Request $request){
        $tramite_id = $request->get('idtramite');
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('SieAppWebBundle:Tramite')->find($tramite_id);
        $idUE       = $repository->getInstitucioneducativa()->getId();
        $gestion    =  $request->getSession()->get('currentyear');
        $arch = 'FORMULARIO_'.$request->get('idUE').'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_adicion_eliminacion_especialidad_v1_ma.rptdesign&idUE='.$idUE.'&gestion='.$gestion.'&idtramite='.$tramite_id.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    public function formularioDirectorEspecialidadesAction(Request $request){
        $id_tramite = $request->get('id_tramite');//ID de Tramite
        $gestion =  $request->getSession()->get('currentyear');
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos,trm.tramite_tipo
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$id_tramite
                                                ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $infoUE = $query->fetch();
        $id_solicitud =  $infoUE['tramite_tipo'];
        $query = $em->getConnection()->prepare("SELECT tramite_tipo FROM tramite_tipo WHERE id = $id_solicitud");
        $query->execute();
        $tramite_solicitud = $query->fetch();
        $solicitud =$tramite_solicitud['tramite_tipo'];
        $id_Institucion  = $infoUE['institucioneducativa_id'];
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
        $documento= $datos['imagen']; //dump($documento);die;
        $especialidadarray = array();
        for($i=0;$i<count($datos['select_especialidad']);$i++) {
            $idespecialidad = $datos['select_especialidad'][$i];
            $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
            $query->execute();
            $especialidad = $query->fetch();
            $especialidadarray[] = array('id' => $especialidad['id'], 'especialidad' => $especialidad['especialidad']);
        }
        $infoUe_distrito = $this->obtieneinforue($id_Institucion,$gestion);
        $especialidades_ue = $this->obtieneespecialidaes($id_Institucion,$gestion);
        //lista de las especialidades menos las especialidades que ya tiene la UE
        $especialidades_adicionar=$this->obtieneespecialidadesrestantes($id_Institucion,$gestion);
        $tipoTramite    = $infoUE['tramite_tipo'];
        $tramite_tipo   = $em->getRepository('SieAppWebBundle:TramiteTipo')->find($tipoTramite)->getTramiteTipo();
        $flujotipo      = $em->getRepository('SieAppWebBundle:Tramite')->find($id_tramite)->getFlujoTipo();
               return $this->render('SieProcesosBundle:TramiteAdiElimEspecialidadesBTH:FormularioDirectorDev.html.twig',array(
                    'id_institucion'=>$id_Institucion,
                    'id_tramite'=>$id_tramite,
                    'objespecialidades'=>$especialidadarray,
                    'objespecialidades_ue'=>$especialidades_ue,
                    'ieducativa'=>$infoUe,
                    'iddistrito'=> $infoUe_distrito['codigo_distrito'],
                    'solicitud'=>$solicitud,
                    'especialidades_adicionar'=>$especialidades_adicionar,
                   'flujotipo'     => $flujotipo->getId(),
                   'documento' =>$documento
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
                 return $this->redirectToRoute('tramite_solicitud_especialidades_bth_formularioDis_dev',array('id'=>$request->get('id')));
             }
        /*
         *Obtenemios la informacion de la UE
         * */
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos,trm.tramite_tipo
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$id_tramite
                                                ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $infoUE = $query->fetch();
         $id_Institucion  = $infoUE['institucioneducativa_id'];
         $id_solicitud =  $infoUE['tramite_tipo'];

         $query = $em->getConnection()->prepare("SELECT tramite_tipo FROM tramite_tipo WHERE id = $id_solicitud");
         $query->execute();
         $tramite_solicitud = $query->fetch();
         $solicitud =$tramite_solicitud['tramite_tipo'];

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

        $especialidadarray = array();
        for($i=0;$i<count($datos['select_especialidad']);$i++) {
            $idespecialidad = $datos['select_especialidad'][$i];
            $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
            $query->execute();
            $especialidad = $query->fetch();
            $especialidadarray[] = array('id' => $especialidad['id'], 'especialidad' => $especialidad['especialidad']);
        }
        $especialidades_ue = $this->obtieneespecialidaes($id_Institucion,$gestion);
        return $this->render('SieProcesosBundle:TramiteAdiElimEspecialidadesBTH:FormularioDisEspecialidades.html.twig',array(
            'id_institucion'=>$id_Institucion,
            'ieducativa'=>$infoUe,
            'iddistrito'=> $infoUe_distrito['codigo_distrito'],
            'objespecialidades_ue'=>$especialidades_ue,
            'objespecialidades_solicitadas'=>$especialidadarray,
            'id_tramite'=>$id_tramite,
            'solicitud'=>$solicitud,
            'idespecialidades'=> json_encode($datos['select_especialidad']),
            'textDirector'=> json_encode($datos['textDirector'])
            ));
    }
     public function  guardasolicitudEspecialidadesDisAction(Request $request){ //dump($request);die;
         $datos = array();
         $documento = $request->files->get('docpdf');
        if(!empty($documento)){
            /*$dirtmp = $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $aName[0];
            */
            $root_bth_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/'.$request->get('institucionid');
            if (!file_exists($root_bth_path)) {
                mkdir($root_bth_path, 0777);
            }
            $destination_path = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/'.$request->get('institucionid').'/addremovespeciality/';
            // $destination_path = 'uploads/archivos/flujos/'.$request->get('institucionid').'/adielimespec/';
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
          $textDirector     = $request->get('textDirector');
          $datos['institucionid'] = $institucionid;
          $datos['select_especialidad'] = explode(",", $request->get('ipt'));
          $datos['evaluacion'] = $evaluacion;
          $datos['obs'] = $obs;
          $datos['imagen'] = $imagen;
          $datos['textDirector'] = $textDirector;
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
     public function formularioDistritoEspecialidadesAction(Request $request){
         $id_tramite = $request->get('id');//ID de Tramite
         $gestion =  $request->getSession()->get('currentyear');
         $em = $this->getDoctrine()->getManager();
          /*
          *Obtenemios la informacion de la UE
          * */
         $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos,trm.tramite_tipo
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$id_tramite
                                                ORDER BY wfsol.id DESC limit 1");
         $query->execute();
         $infoUE = $query->fetch();
         $id_Institucion  = $infoUE['institucioneducativa_id'];
         $id_solicitud =  $infoUE['tramite_tipo'];

         //dump($id_solicitud);die;

         $query = $em->getConnection()->prepare("SELECT tramite_tipo FROM tramite_tipo WHERE id = $id_solicitud");
         $query->execute();
         $tramite_solicitud = $query->fetch();
         $solicitud =$tramite_solicitud['tramite_tipo'];

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
         $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);// dump($datos);die;

         $especialidadarray = array();
         for($i=0;$i<count($datos['select_especialidad']);$i++) {
             $idespecialidad = $datos['select_especialidad'][$i];
             $query = $em->getConnection()->prepare("SELECT eth.id,eth.especialidad FROM especialidad_tecnico_humanistico_tipo eth WHERE eth. id=$idespecialidad");
             $query->execute();
             $especialidad = $query->fetch();
             $especialidadarray[] = array('id' => $especialidad['id'], 'especialidad' => $especialidad['especialidad']);
         }
         $especialidades_ue = $this->obtieneespecialidaes($id_Institucion,$gestion);

         //$documento= empty($datos[4])?'':$datos[4];
         return $this->render('SieProcesosBundle:TramiteAdiElimEspecialidadesBTH:FormularioDisEspecialidadesDev.html.twig',array(
             'id_institucion'=>$id_Institucion,
             'ieducativa'=>$infoUe,
             'iddistrito'=> $infoUe_distrito['codigo_distrito'],
             'objespecialidades_ue'=>$especialidades_ue,
             'objespecialidades_solicitadas'=>$especialidadarray,
             'id_tramite'=>$id_tramite,
             'solicitud'=>$solicitud,
             'idespecialidades'=> json_encode($datos['select_especialidad']),
             'textDirector'=>json_encode($datos['textDirector'])
         ));





        /*return $this->render('SieHerramientaBundle:SolicitudBTH:FormularioBTHDis.html.twig',array(
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
        ));*/
    }
//Departamental
    public function VerSolicitudEspecialidadesBTHDepAction(Request $request){
        $id_tramite = $request->get('id');//ID de Tramite
        $gestion =  $request->getSession()->get('currentyear');
        $em = $this->getDoctrine()->getManager();
        /*
        *Obtenemios la informacion de la UE
        * */
        $query = $em->getConnection()->prepare("SELECT trm.institucioneducativa_id, trm.fecha_tramite,trm.gestion_id,wfsol.datos,tramite_tipo
                                                FROM tramite trm 
                                                INNER JOIN tramite_detalle td  ON trm.id=td.tramite_id
                                                INNER JOIN wf_solicitud_tramite wfsol ON td.id=wfsol.tramite_detalle_id
                                                WHERE trm.id=$id_tramite
                                                ORDER BY wfsol.id DESC limit 1");
        $query->execute();
        $infoUE = $query->fetch();
        $id_Institucion  = $infoUE['institucioneducativa_id'];
        $id_solicitud =  $infoUE['tramite_tipo'];

        $query = $em->getConnection()->prepare("SELECT tramite_tipo FROM tramite_tipo WHERE id = $id_solicitud");
        $query->execute();
        $tramite_solicitud = $query->fetch();
        $solicitud =$tramite_solicitud['tramite_tipo'];

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
        $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true); //dump($datos);die;
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
            'textDirector'=> json_encode($datos['textDirector']),
            'documento'=>$documento,
            'solicitud'=>$solicitud
        ));

    }
    public function guardasolicitudEspecialidadesDepAction(Request $request){ //dump($request);die;
        $datos = array();
        $documento = $request->files->get('docpdf');
        if(!empty($documento)){
            /*$dirtmp = $this->get('kernel')->getRootDir() . '/../web/empfiles/' . $aName[0];
            */
            $destination_path = 'uploads/archivos/flujos/'.$request->get('institucionid').'/adielimespec/';
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
        $idtramite      = $request->get('id_tramite');
        $obs            = $request->get('obstxt');
        $textDirector   = $request->get('textDirector');
        //armado de %datos
        $datos['institucionid']         = $institucionid;
        $datos['select_especialidad']   = explode(",", $request->get('ipt'));
        $datos['evaluacion']            = $evaluacion;
        $datos['evaluacion2']           = $evaluacion2;
        $datos['obs']                   = $obs;
        $datos['imagen']                = $imagen;
        $datos['textDirector']          = $textDirector;
        $datos                          = json_encode($datos); //dump($datos);die;

        $tramite        = $em->getRepository('SieAppWebBundle:Tramite')->findOneById($idtramite);
        $flujoproceso   = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 4));
        $flujotipo      = $flujoproceso->getFlujoTipo()->getId(); //7//SOLICITUDBTH
        $tarea          = $flujoproceso->getId();//RECEPCIONA
        $flujoproceso   = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 5));
        $tarea1         = $flujoproceso->getId();//elaborainfrorme y envia BTH DEPARTAMENTO - ORDEN 5
        $tabla          = 'institucioneducativa';
    try{
    $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea,$tabla,$institucionid,$obs,$evaluacion,$idtramite,$datos,'',$id_distrito);
    if ($evaluacion=='SI') {
        $mensaje = $this->get('wftramite')->guardarTramiteRecibido($id_usuario, $tarea1,$idtramite);
        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea1,$tabla,$institucionid,$obs,'',$idtramite,$datos,'',$id_distrito);
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
                if($tramite->getTramiteTipo()->getTramiteTipo() == 'Adicionar Especialidades'){
                    $ue = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucionid);
                    for($i=0;$i<count($datos['select_especialidad']);$i++){
                        $entity = new InstitucioneducativaEspecialidadTecnicoHumanistico();
                        $idespecialidad = $datos['select_especialidad'][$i];
                        $espe = $em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($idespecialidad);
                        $gestiontipo = $em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear'));
                        $entity->setInstitucioneducativa($ue);
                        $entity->setEspecialidadTecnicoHumanisticoTipo($espe);
                        $entity->setGestionTipo($gestiontipo);
                        $entity->setObs("Trámite Especialidades - Adicionar Especialidades");
                        $entity->setFechaRegistro(new \DateTime(date('Y-m-d H:i:s')));
                        $em->persist($entity);
                        $em->flush();
                    }

                }
                else{// En caso de Eliminacion de tramitwesa
                    $gestion =$this->session->get('currentyear');
                    $especialidades_eliminar =array_map('intval',$datos['select_especialidad']);
                    for($i=0;$i<count($datos['select_especialidad']);$i++){
                        $idespecialidad = $datos['select_especialidad'][$i];
                        $query = $em->getConnection()->prepare("delete FROM institucioneducativa_especialidad_tecnico_humanistico
                          WHERE institucioneducativa_id=$institucionid and gestion_tipo_id=$gestion AND especialidad_tecnico_humanistico_tipo_id=$idespecialidad");
                        $query->execute();
                    }
                    //DELETE SieAppWebBundlgsdfge:InstitucioneducativaEspecialidadTecnicoHumanistico ietu WHERE ietu.Institucioneducativa=:institucionid and ietu.GestionTipo =: gestion AND ietu.EspecialidadTecnicoHumanisticoTipo in (:especialidades)
                    /*$query=$em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->createQueryBuilder('esp')
//                        ->delete()
                        ->where('esp.Institucioneducativa = :institucionid')
                        ->andWhere('esp.GestionTipo = :gestion')
                        ->andWhere('esp.EspecialidadTecnicoHumanisticoTipo  in (:institucionid)')
                        ->setParameter("institucionid",$ue)
                        ->setParameter("gestion",$gestion)
                        ->setParameter("especiallidades",$especialidades_eliminar)
                        ->getQuery();
                    $result = $query->getResult();
                    dump($result);die;*/
                }
                ///// FIN DE volcado a la base de datoa
            }//si la repsuesta 1 fue NO
            else{

                 $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo' => $tramite->getFlujoTipo(), 'orden' => 6));
                 $tarea2   = $flujoproceso->getId();//6 realiza observacion
                 $mensaje = $this->get('wftramite')->guardarTramiteRecibido($id_usuario, $tarea2,$idtramite);
                 $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea2,$tabla,$institucionid,$obs,$evaluacion2,$idtramite,$datos,'',$id_distrito);
                   
                    if ($mensaje['dato']==true){
                        $res = 1;
                    }else{
                        $res = 2;
                    }
                     //dump($res);die;

                /*if($evaluacion2 =='SI'){
                    $mensaje = $this->get('wftramite')->guardarTramiteRecibido($id_usuario, $tarea2,$idtramite);
                    $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea2,$tabla,$institucionid,$obs,$evaluacion2,$idtramite,$datos,'',$id_distrito);
                    if ($mensaje['dato']==true){
                        $res = 1;
                    }else{
                        $res = 2;
                    }
                } else{
                    //$mensaje = $this->get('wftramite')->guardarTramiteRecibido($id_usuario, $tarea2,$idtramite);
                    $mensaje = $this->get('wftramite')->guardarTramiteEnviado($id_usuario,$id_rol,$flujotipo,$tarea2,$tabla,$institucionid,$obs,$evaluacion2,$idtramite,$datos,'',$id_distrito);
                    if ($mensaje['dato']==true){
                        $res = 1;
                    }else{
                        $res = 2;
                    }

                }*/

            }
            $res = 1;
        }
        catch (Exception $exceptione){
            $res = 0;
        }
       // dump($res);die;
        return  new JsonResponse(array('estado' => $res, 'msg' => $mensaje));
        //return  new Response($res);
    }
}
