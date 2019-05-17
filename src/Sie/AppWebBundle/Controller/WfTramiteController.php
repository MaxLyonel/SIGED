<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use Sie\AppWebBundle\Entity\FlujoProceso;
use Sie\AppWebBundle\Entity\FlujoTipo;
use Sie\AppWebBundle\Entity\RolTipo;
use Sie\AppWebBundle\Form\FlujoProcesoType;
use Sie\AppWebBundle\Entity\Tramite;
use Sie\AppWebBundle\Entity\TramiteDetalle;
use Sie\AppWebBundle\Entity\WfSolicitudTramite;
use Sie\AppWebBundle\Entity\WfUsuarioFlujoProceso;



/**
 * WfTramite controller.
 *
 */
class WfTramiteController extends Controller
{
    public $session;
 
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }
    
    /**
     * Listado de los tipo de flujos para iniciar un tramite
     */
    public function indexAction(Request $request)
    {
        
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $pathSystem = $this->session->get('pathSystem');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();

        $flujotipo = $em->getRepository('SieAppWebBundle:FlujoTipo')->createQueryBuilder('ft')
                ->select('ft')
                ->where('ft.id > 5')
                ->andWhere("ft.obs like '%ACTIVO%'")
                ->getQuery()
                ->getResult();
        //dump($flujotipo);die;
        $data['entities'] = $flujotipo;
        $data['titulo'] = "Listado de trámites existentes";
        return $this->render($pathSystem.':WfTramite:index.html.twig', $data);
    }

    /**
     * Redireccion al formulario de inicio de tramite segun el flujo
     */
    public function nuevoAction(Request $request,$id)
    {
        //dump($id);die;
        $this->session = $request->getSession();
        //dump($this->session);die;
        $idUsuario = $this->session->get('userId');
        $idlugarusuario = $this->session->get('roluserlugarid');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($idUsuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findBy(array('flujoTipo'=>$id,'orden'=>1));
        //dump($flujoproceso);die;
        if($flujoproceso[0]->getRolTipo()->getId()!= 9){  //si no es director
            $wfusuario = $em->getRepository('SieAppWebBundle:WfUsuarioFlujoProceso')->createQueryBuilder('wfufp')
                ->select('wfufp')
                ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = wfufp.flujoProceso')
                ->where('fp.orden=1')
                ->andWhere('fp.flujoTipo='.$id)
                ->andWhere('wfufp.usuario='.$idUsuario)
                ->andWhere('wfufp.esactivo=true')
                ->andWhere('wfufp.lugarTipoId='.$idlugarusuario)
                ->andWhere('fp.rolTipo='.$rol)
                ->getQuery()
                ->getResult();
            if($wfusuario){
                return $this->redirectToRoute($flujoproceso[0]->getRutaFormulario(),array('id'=>$id));
            }else{
                $request->getSession()
                        ->getFlashBag()
                        ->add('error', "No tiene tuición para iniciar un nuevo tramite: ". $flujoproceso[0]->getFlujoTipo()->getFlujo());
                return $this->redirectToRoute('wf_tramite_index');
            }    
        }else{
            if($rol == $flujoproceso[0]->getRolTipo()->getId()){
                $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
                $query->bindValue(':user_id', $idUsuario);
                $query->bindValue(':sie', $this->session->get('ie_id'));
                $query->bindValue(':rolId', $rol);
                $query->execute();
                $aTuicion = $query->fetchAll();
                if (!$aTuicion[0]['get_ue_tuicion']) {
                    return $this->redirectToRoute($flujoproceso[0]->getRutaFormulario(),array('id'=>$id));  
                }else{
                    $request->getSession()
                            ->getFlashBag()
                            ->add('error', "No tiene tuición para iniciar un nuevo tramite: ". $flujoproceso[0]->getFlujoTipo()->getFlujo());
                    return $this->redirectToRoute('wf_tramite_index');    
                }        
            }else{
                $request->getSession()
                        ->getFlashBag()
                        ->add('error', "No tiene tuición para iniciar un nuevo tramite: ". $flujoproceso[0]->getFlujoTipo()->getFlujo());
                return $this->redirectToRoute('wf_tramite_index');    
            }
        }
    }

    /**
     * Listado de los tramites recibidos
     */
    public function recibidosAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $pathSystem = $this->session->get('pathSystem');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->getConnection()->prepare("select distinct t.id,case when (ie.id is not null) then 'SIE:'||ie.id when (ie.id is null and ft.id=6) then 'SIE:' when ei.id is not null then 'RUDE: '|| e.codigo_rude when mi.id is not null then 'CI: '||pm.carnet when ai.id is not null then 'CI: '||pa.carnet end as codigo_tabla ,case when ie.id is not null then 'Institucion Educativa: '||ie.institucioneducativa when (ie.id is null and ft.id=6) then 'Institucion Educativa: ' when ei.id is not null then 'Estudiante: '||e.nombre||' '||e.paterno||' '||e.materno when mi.id is not null then 'Maestro: '||pm.nombre||' '||pm.paterno||' '||pm.materno when ai.id is not null then 'Apoderado: '||pa.nombre||' '||pa.paterno||' '||pa.materno end as nombre_tabla,ft.flujo,ft.id as idflujo,case when te.id=3 then pt.proceso_tipo when (te.id=15 or te.id=4)  and (fp.es_evaluacion is false) then ptsig.proceso_tipo when (te.id=15 or te.id=4) and (fp.es_evaluacion is true) then ptc.proceso_tipo  end as proceso_tipo,pt.proceso_tipo as tarea_actual,tt.tramite_tipo,te.tramite_estado,case when te.id = 3 then td.fecha_recepcion else td.fecha_envio end as fecha_estado,te.id as id_estado,td.obs,fp.plazo,case when te.id = 3 then td.fecha_recepcion + fp.plazo else null end as fecha_vencimiento,p.nombre
        from tramite t
        join tramite_detalle td on cast(t.tramite as int)=td.id
        left join institucioneducativa ie on t.institucioneducativa_id=ie.id
        left join estudiante_inscripcion ei on t.estudiante_inscripcion_id=ei.id
        left join estudiante e on ei.estudiante_id=e.id
        left join maestro_inscripcion mi on t.maestro_inscripcion_id=mi.id
        left join persona pm on mi.persona_id=pm.id
        left join apoderado_inscripcion ai on t.apoderado_inscripcion_id=ai.id
        left join persona pa on ai.persona_id=pa.id
        join flujo_proceso fp on td.flujo_proceso_id=fp.id
        left join flujo_proceso fpsig on fp.tarea_sig_id=fpsig.id
        left join flujo_proceso fpant on fp.tarea_ant_id=fpant.id
        left join proceso_tipo ptsig on fpsig.proceso_id=ptsig.id
        join proceso_tipo pt on fp.proceso_id=pt.id
        left join wf_tarea_compuerta wftc on fp.id=wftc.flujo_proceso_id
        left join flujo_proceso fpc on fpc.id=wftc.condicion_tarea_siguiente
        left join proceso_tipo ptc on fpc.proceso_id=ptc.id
        join tramite_tipo tt on t.tramite_tipo=tt.id
        join tramite_estado te on td.tramite_estado_id=te.id
        join flujo_tipo ft on t.flujo_tipo_id = ft.id
        join usuario u on td.usuario_remitente_id=u.id
        join persona p on p.id=u.persona_id
        where ft.id>5 and t.fecha_fin is null and
        ((fpsig.rol_tipo_id=". $rol ." and (te.id=15 or te.id=4) and fp.es_evaluacion is false) or 
        (fp.rol_tipo_id=". $rol ." and te.id=3) or 
        ((select rol_tipo_id from flujo_proceso where id= wftc.condicion_tarea_siguiente)=". $rol ." and (te.id=15 or te.id=4) and fp.es_evaluacion is true and td.valor_evaluacion=wftc.condicion) ) and td.usuario_destinatario_id=".$usuario." 
        order by ft.flujo,te.tramite_estado,fecha_estado,t.id,proceso_tipo,tt.tramite_tipo,id_estado,td.obs,p.nombre");
        $query->execute();
        $data['entities'] = $query->fetchAll();
        $data['titulo'] = "Listado de trámites recibidos";
        //dump($data);die;
        return $this->render($pathSystem.':WfTramite:recibidos.html.twig', $data);
    }

    /**
     * Registro del tramite como recibido
     */
    public function recibidosGuardarAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $id = $request->get('id');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        $idtramite = $id;
        if($tramiteDetalle->getFlujoProceso()->getEsEvaluacion() == true){
            $t = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$tramiteDetalle->getFlujoProceso()->getId(),'condicion'=>$tramiteDetalle->getValorEvaluacion()));
            $tarea = $t[0]->getCondicionTareaSiguiente();
        }else{
            $tarea = $tramiteDetalle->getFlujoProceso()->getTareaSigId();
        }
        $mensaje = $this->guardarTramiteRecibido($usuario,$tarea,$idtramite);
        if($mensaje['dato'] == true){
            $request->getSession()
                ->getFlashBag()
                ->add('recibido', $mensaje['msg']);
        }else{
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje['msg']);
        }
        return $this->redirectToRoute('wf_tramite_recibido');

        //return $this->render('SieHerramientaBundle:WfTramite:recibidos.html.twig');
    }

    /**
     * Redireccion del tramite a su formularios correspondiente
     */
    public function recibidosEnviarAction(Request $request,$id)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        //dump($tramiteDetalle);die;
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($tramiteDetalle->getFlujoProceso()->getId());
        //dump($flujoproceso);die;
        //Verificamos si tiene competencia
        if($rol == $flujoproceso->getRolTipo()->getId()){
            return $this->redirectToRoute($flujoproceso->getRutaFormulario(),array('id' => $tramite->getId()));
        }else{
            $request->getSession()
                    ->getFlashBag()
                    ->add('error', "No tiene tuición para este tramite");
                    return $this->redirectToRoute('wf_tramite_recibido');
        }  
    }
    
    /**
     * funcion general para guardar un nuevo tramite
     */
    public function guardarTramiteNuevo($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$tipotramite,$varevaluacion,$idtramite,$datos,$lugarTipoLocalidad_id,$lugarTipoDistrito_id)
    {

        //dump($lugarTipoLocalidad_id,$lugarTipoDistrito_id);die;
        $em = $this->getDoctrine()->getManager();
        
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea);
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($usuario);
        $tramiteestado = $em->getRepository('SieAppWebBundle:TramiteEstado')->find(15);
        $flujotipo = $em->getRepository('SieAppWebBundle:FlujoTipo')->find($flujotipo);
        $tramitetipo = $em->getRepository('SieAppWebBundle:TramiteTipo')->find($tipotramite);
        
        $em->getConnection()->beginTransaction();
        try {
            /**
            * insert tramite
            */
            $tramite = new Tramite();
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('tramite');")->execute();
            $tramite->setFlujoTipo($flujotipo);
            $tramite->setTramiteTipo($tramitetipo);
            $tramite->setFechaTramite(new \DateTime(date('Y-m-d')));
            $tramite->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $tramite->setEsactivo(true);
            $tramite->setGestionId((new \DateTime())->format('Y'));
            switch ($tabla) {
                case 'institucioneducativa':
                    if ($id_tabla){
                        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id_tabla);
                        $tramite->setInstitucioneducativa($institucioneducativa);
                    }
                    break;
                case 'estudiante_inscripcion':
                    $estudiante = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($id_tabla);
                    $tramite->setestudianteInscripcion($estudiante);
                    break;
                case 'apoderado_inscripcion':
                    $apoderado = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($id_tabla);
                    $tramite->setApoderadoInscripcion($apoderado);
                    break;
                case 'maestro_inscripcion':
                    $maestro = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($id_tabla);
                    $tramite->setMaestroInscripcion($maestro);
                    break;
            }
            $em->persist($tramite);
            $em->flush();
            /**
            * insert tramite_detalle 
            */
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('tramite_detalle');")->execute();
            $tramiteDetalle = new TramiteDetalle();    
            $tramiteDetalle->setTramite($tramite);
            $tramiteDetalle->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $tramiteDetalle->setFechaRecepcion(new \DateTime(date('Y-m-d')));
            $tramiteDetalle->setFechaEnvio(new \DateTime(date('Y-m-d')));
            $tramiteDetalle->setFlujoProceso($flujoproceso);
            $tramiteDetalle->setUsuarioRemitente($usuario);
            $tramiteDetalle->setObs(mb_strtoupper($observacion, 'UTF-8'));
            $tramiteDetalle->setTramiteEstado($tramiteestado);
            $em->persist($tramiteDetalle);
            $em->flush();

            /**
            * insert datos propios de la solicitud
            */
            if ($datos){
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('wf_solicitud_tramite');")->execute();   
                $wfSolicitudTramite = new WfSolicitudTramite();
                $wfSolicitudTramite->setTramiteDetalle($tramiteDetalle);
                $wfSolicitudTramite->setDatos($datos);
                $wfSolicitudTramite->setEsValido(true);
                $wfSolicitudTramite->setFechaRegistro(new \DateTime(date('Y-m-d H:i:s')));
                $wfSolicitudTramite->setLugarTipoLocalidadId((int)$lugarTipoLocalidad_id?$lugarTipoLocalidad_id:null);
                $wfSolicitudTramite->setLugarTipoDistritoId((int)$lugarTipoDistrito_id?$lugarTipoDistrito_id:null);
                //dump($wfSolicitudTramite);die;
                $em->persist($wfSolicitudTramite);
                $em->flush();
            }
            if ($flujoproceso->getEsEvaluacion() == true) 
            {
                $tramiteDetalle->setValorEvaluacion($varevaluacion);
                $wfcondiciontarea = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$flujoproceso->getId(),'condicion'=>$varevaluacion));
                $tarea_sig_id = $wfcondiciontarea[0]->getCondicionTareaSiguiente();
            }else{
                $tarea_sig_id = $flujoproceso->getTareaSigId();
            }
            $uDestinatario = $this->obtieneUsuarioDestinatario($tarea,$tarea_sig_id,$id_tabla,$tabla,$tramite);
            
            if($uDestinatario == false){
                $em->getConnection()->rollback();
                $mensaje['dato'] = false;
                $mensaje['msg'] = '¡Error, no existe usuario destinatario registrado.!';
                return $mensaje;
            }else{
                $tramiteDetalle->setUsuarioDestinatario($uDestinatario);
            }
            $em->flush();
            /**
            * actualizamos ultima tarea del tramite
            */
            $tramite->setTramite($tramiteDetalle->getId());
            $em->flush();
            $em->getConnection()->commit();
            $mensaje['dato'] = true;
            $mensaje['msg'] = 'El trámite Nro. '. $tramite->getId() .' se guardó correctamente';
            $mensaje['idtramite'] = $tramite->getId();
            return $mensaje;
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $mensaje['dato'] = false;
            $mensaje['msg'] = '¡Ocurrio un error al guardar el trámite.!';
            return $mensaje;    
        }
    }

    /**
     * funcion q guarda un tramite como recibido
     */
    public function guardarTramiteRecibido($usuario,$tarea,$idtramite)
    {

        $em = $this->getDoctrine()->getManager();

        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea);
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($usuario);
        $tramiteestado = $em->getRepository('SieAppWebBundle:TramiteEstado')->find(3);
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        
        $em->getConnection()->beginTransaction();
        try {
            /**
             * guarda tramite recibido
            */
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('tramite_detalle');")->execute();
            $tramiteDetalle = new TramiteDetalle();    
            $tramiteDetalle->setTramite($tramite);
            $tramiteDetalle->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $tramiteDetalle->setFechaRecepcion(new \DateTime(date('Y-m-d')));
            $tramiteDetalle->setTramiteEstado($tramiteestado);
            $tramiteDetalle->setFlujoProceso($flujoproceso);
            $tramiteDetalle->setUsuarioRemitente($usuario);
            $tramiteDetalle->setUsuarioDestinatario($usuario);
            /**
            * Guardamos tarea anterior en tramite detalle  
            */
            $td_anterior = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
            $tramiteDetalle->setTramiteDetalle($td_anterior);
            $em->persist($tramiteDetalle);
            $em->flush();
            $tramite->setTramite($tramiteDetalle->getId());
            $em->flush();
            $em->getConnection()->commit();
            $mensaje['dato'] = true;
            $mensaje['msg'] = 'El trámite Nro. '. $tramite->getId() .' se recibió correctamente';
            return $mensaje;
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $mensaje['dato'] = false;
            $mensaje['msg'] = 'Ocurrio un error al guardar el trámite.';
            return $mensaje;
        }
    }
    
    /**
     * funcion general para guardar una tarea de un tramite
     */
    public function guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion,$idtramite,$datos,$lugarTipoLocalidad_id,$lugarTipoDistrito_id)
    {

        $em = $this->getDoctrine()->getManager();

        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea);
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($usuario);
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        $em->getConnection()->beginTransaction();
        try {
            /**
            * asigana usuario destinatario
            */
            if ($flujoproceso->getEsEvaluacion() == true) 
            {
                $tramiteDetalle->setValorEvaluacion($varevaluacion);
                //dump($tramiteDetalle);die;
                $wfcondiciontarea = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$flujoproceso->getId(),'condicion'=>$varevaluacion));
                //dump($wfcondiciontarea);die;
                if ($wfcondiciontarea[0]->getCondicionTareaSiguiente() != null){
                    $tarea_sig_id = $wfcondiciontarea[0]->getCondicionTareaSiguiente();
                    $uDestinatario = $this->obtieneUsuarioDestinatario($tarea,$tarea_sig_id,$id_tabla,$tabla,$tramite);
                    //dump($uDestinatario);die;
                    if($uDestinatario == false){
                        //dump($uDestinatario);die;
                        $em->getConnection()->rollback();
                        $mensaje['dato'] = false;
                        $mensaje['msg'] = '¡Error, no existe usuario destinatario registrado.!';
                        return $mensaje;
                    }else{
                        $tramiteDetalle->setUsuarioDestinatario($uDestinatario);
                    }
                    
                }else{
                    // si despues de la evaluacion termina el tramite
                    $tarea_sig_id = null;
                }
            }else{
                if ($flujoproceso->getTareaSigId() != null){
                    $tarea_sig_id = $flujoproceso->getTareaSigId();
                    $uDestinatario = $this->obtieneUsuarioDestinatario($tarea,$tarea_sig_id,$id_tabla,$tabla,$tramite);
                    if($uDestinatario == false){
                        $em->getConnection()->rollback();
                        $mensaje['dato'] = false;
                        $mensaje['msg'] = '¡Error, no existe usuario destinatario registrado.!';
                        return $mensaje;
                    }else{
                        $tramiteDetalle->setUsuarioDestinatario($uDestinatario);
                    }
                }else{
                    $tarea_sig_id = null;
                }
            }
            /**
            * guarda tramite enviado/devuelto
            */
            if (($flujoproceso->getTareaSigId() != null and $flujoproceso->getEsEvaluacion() == false ) or ($tarea_sig_id != null and $flujoproceso->getEsEvaluacion() == true)){
                if($tarea_sig_id > $flujoproceso->getId()){
                    $tramiteestado = $em->getRepository('SieAppWebBundle:TramiteEstado')->find(15); //enviado
                }else{
                    $tramiteestado = $em->getRepository('SieAppWebBundle:TramiteEstado')->find(4); //devuelto
                }
            }else{
                $tramiteestado = $em->getRepository('SieAppWebBundle:TramiteEstado')->find(15); //enviado
            }
            $tramiteDetalle->setObs(mb_strtoupper($observacion,'UTF-8'));
            $tramiteDetalle->setFechaEnvio(new \DateTime(date('Y-m-d')));
            $tramiteDetalle->setTramiteEstado($tramiteestado);
            $em->flush();
        
            /**
            * inserta datos propios de la solicitud en esta tarea
            */
            if ($datos){
                $wfDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
                            ->select('wf')
                            ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
                            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
                            ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = td.flujoProceso')
                            ->where('fp.id =' . $tarea)
                            ->andwhere('t.id =' . $idtramite)
                            ->andwhere('wf.esValido =true')
                            ->getQuery()
                            ->getResult();
                if($wfDatos){
                    $wfDatos[0]->setEsValido(false);
                    $wfDatos[0]->setFechaModificacion(new \DateTime(date('Y-m-d H:i:s')));
                }
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('wf_solicitud_tramite');")->execute();   
                $wfSolicitudTramite = new WfSolicitudTramite();
                $wfSolicitudTramite->setTramiteDetalle($tramiteDetalle);
                $wfSolicitudTramite->setDatos($datos);
                $wfSolicitudTramite->setEsValido(true);
                $wfSolicitudTramite->setFechaRegistro(new \DateTime(date('Y-m-d H:i:s')));
                $wfSolicitudTramite->setLugarTipoLocalidadId($lugarTipoLocalidad_id?(int)$lugarTipoLocalidad_id:null);
                $wfSolicitudTramite->setLugarTipoDistritoId($lugarTipoDistrito_id?(int)$lugarTipoDistrito_id:null);
                $em->persist($wfSolicitudTramite);
                $em->flush();
            }
            /**
             * si es la ultima tarea del tramite se finaliza el tramite
             */
            if (($flujoproceso->getTareaSigId() == null and $flujoproceso->getEsEvaluacion() == false ) or ($tarea_sig_id == null and $flujoproceso->getEsEvaluacion() == true))
            {
                $tramite->setFechaFin(new \DateTime(date('Y-m-d')));
                $em->flush();
                $mensaje['msg'] = 'TOME NOTA, el trámite Nro. '. $tramite->getId() .' a finalizado.';
            }else{
               $mensaje['msg'] = 'El trámite Nro. '. $tramite->getId() .' se envió correctamente.';
            }
            $em->getConnection()->commit();
            $mensaje['dato'] = true;
            return $mensaje;
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $mensaje['dato'] = false;
            $mensaje['msg'] = '¡Ocurrio un error al enviar el trámite.!';
            return $mensaje;
        }
    }

    /**
     * funcion para asignar el usuario destinatario de la tarea actual
     */
    public function obtieneUsuarioDestinatario($tarea_actual,$tarea_sig_id,$id_tabla,$tabla,$tramite)
    {
        $em = $this->getDoctrine()->getManager();
        
        $flujoprocesoSiguiente = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea_sig_id);
        $nivel = $flujoprocesoSiguiente->getRolTipo()->getLugarNivelTipo();
        //dump($nivel);die;
        switch ($tabla) {
            case 'institucioneducativa':
                if ($tramite->getInstitucioneducativa()){
                    $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id_tabla);
                    $lugar_tipo_distrito = $institucioneducativa->getleJuridicciongeografica()->getLugarTipoIdDistrito();
                    $lugar_tipo_departamento = $institucioneducativa->getleJuridicciongeografica()->getlugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugartipo()->getCodigo();
                }else{
                    $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                        ->select('wfd')
                        ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                        ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = td.flujoProceso')
                        ->where('td.tramite='.$tramite->getId())
                        ->andWhere("fp.orden=1")
                        ->andWhere("wfd.esValido=true")
                        ->getQuery()
                        ->getResult();
                    $lugar_tipo_distrito = $wfdatos[0]->getLugarTipoDistritoId();
                    $lt = $em->getRepository('SieAppWebBundle:LugarTipo')->find($lugar_tipo_distrito);
                    $lugar_tipo_departamento = $lt->getLugarTipo()->getCodigo();
                }
                break;
            case 'estudiante_inscripcion':
                break;
            case 'apoderado_inscripcion':
                break;
            case 'maestro_inscripcion':
                break;
        }

        switch ($nivel->getId()) {
            case 7:   // Distrito
                //dump($lugar_tipo_distrito);die;
                $query = $em->getConnection()->prepare("select * from wf_usuario_flujo_proceso where flujo_proceso_id=". $flujoprocesoSiguiente->getId()." and esactivo is true and  lugar_tipo_id=".$lugar_tipo_distrito);
                $query->execute();
                $uDestinatario = $query->fetchAll();
                if($uDestinatario){
                    if(count($uDestinatario)>1){
                        $uid = $this->asiganaUsuarioDestinatario($tarea_actual,$tarea_sig_id,$uDestinatario[0]['lugar_tipo_id']);
                    }else{
                        $uid = $uDestinatario[0]['usuario_id'];
                    }
                }else{
                    return false;
                }
                
                break;
            case 6:   // Departamento
            case 8:
                //dump($lugar_tipo_departamento);die;
                $query = $em->getConnection()->prepare("select ufp.* from wf_usuario_flujo_proceso ufp join lugar_tipo lt on ufp.lugar_tipo_id=lt.id where ufp.flujo_proceso_id=". $flujoprocesoSiguiente->getId()." and ufp.esactivo is true and cast(lt.codigo as int)=".$lugar_tipo_departamento);
                $query->execute();
                $uDestinatario = $query->fetchAll();
                if($uDestinatario){
                    //dump($uDestinatario);die;
                    if(count($uDestinatario)>1){
                        $uid = $this->asiganaUsuarioDestinatario($tarea_actual,$tarea_sig_id,$uDestinatario[0]['lugar_tipo_id']);
                    }else{
                        $uid = $uDestinatario[0]['usuario_id'];
                    }   
                }else{
                    return false;
                }
                
                break;
            case 0://nivel nacional
                //dump($flujoprocesoSiguiente->getRolTipo()->getId());die;
                if($flujoprocesoSiguiente->getRolTipo()->getId() == 9){  // si es director
                    $query = $em->getConnection()->prepare("select u.* from maestro_inscripcion m
                    join usuario u on m.persona_id=u.persona_id
                    where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=".(new \DateTime())->format('Y')." and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
                    //where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=2018 and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
                    $query->execute();
                    $uDestinatario = $query->fetchAll();
                    //dump($uDestinatario);die;
                    if($uDestinatario){
                        $uid = $uDestinatario[0]['id'];
                    }else{
                        return false;
                    }
                }elseif($flujoprocesoSiguiente->getRolTipo()->getId() == 8){ // si es tecnico nacional
                    $query = $em->getConnection()->prepare("select * from wf_usuario_flujo_proceso ufp where ufp.esactivo is true and ufp.flujo_proceso_id=". $flujoprocesoSiguiente->getId()." and lugar_tipo_id=1");
                    $query->execute();
                    $uDestinatario = $query->fetchAll();
                    if($uDestinatario){
                        //dump(count($uDestinatario));die;
                        if(count($uDestinatario)>1){
                            $uid = $this->asiganaUsuarioDestinatario($tarea_actual,$tarea_sig_id,1);
                        }else{
                            $uid = $uDestinatario[0]['usuario_id'];
                        }
                    }else{
                        return false;
                    }
                }
                break;
        }

        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($uid);
        return $usuario;
    }

    /**
     * funcion que asigna usuario destinatario si la tarea tiene mas de un usuario registrado
     */
    public function asiganaUsuarioDestinatario($tarea_actual_id,$tarea_sig_id,$lugar_tipo)
    {
        $em = $this->getDoctrine()->getManager();
        //dump($lugar_tipo);die;
        $query = $em->getConnection()->prepare("select a.usuario_id,case when b.nro is null then 0 else b.nro end as nro
        from 
        (select usuario_id from wf_usuario_flujo_proceso wf
        where wf.flujo_proceso_id=". $tarea_sig_id ." and wf.esactivo is true and wf.lugar_tipo_id=". $lugar_tipo .")a
        left join 
        (select td.usuario_destinatario_id,count(*) as nro
        from tramite t
        join tramite_detalle td on cast(t.tramite as int)=td.id
        where flujo_proceso_id=". $tarea_actual_id ." and (td.tramite_estado_id=15 or td.tramite_estado_id=4) group by td.usuario_destinatario_id)b on a.usuario_id=b.usuario_destinatario_id  order by b.nro desc");
        $query->execute();
        $usuarios = $query->fetchAll();
        //dump($usuarios);die;
        $uid = $usuarios[0]['usuario_id'];
        //dump($uid);die;
        return $uid;
    }

    public function eliminarTramiteNuevo($idtramite)
    {
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->findOneBy(array('tramiteDetalle'=>$tramiteDetalle->getId()));
        $em->remove($wfSolicitudTramite);
        $em->remove($tramiteDetalle);
        $em->remove($tramite);
        $em->flush();
        return true;
    }
    public function eliminarTramiteRecibido($idtramite)
    {
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        $tramite->setTramite($tramiteDetalle->getTramiteDetalle()->getId());
        $em->flush();
        $em->remove($tramiteDetalle);
        $em->flush();
        return true;
    }

    public function eliminarTramteEnviado($idtramite,$idusuario)
    {
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        $tramiteDetalle->setValorEvaluacion(null);
        $tramiteDetalle->setUsuarioDestinatario($em->getRepository('SieAppWebBundle:Usuario')->find($idusuario));
        $tramiteDetalle->setObs(null);
        $tramiteDetalle->setFechaEnvio(null);
        $tramiteDetalle->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find(3));
        $em->flush();
        $query = $em->getConnection()->prepare("delete from wf_solicitud_tramite where tramite_detalle_id =". $tramiteDetalle->getId());
        $query->execute();
        $wfDatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
                    ->select('wf')
                    ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wf.tramiteDetalle')
                    ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = td.tramite')
                    ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = td.flujoProceso')
                    ->where('fp.id =' . $tramiteDetalle->getFlujoProceso()->getId())
                    ->andwhere('t.id =' . $idtramite)
                    ->andwhere('wf.esValido =false')
                    ->getQuery()
                    ->getResult();
        if($wfDatos){
            $wfDatos[0]->setEsValido(true);
            $wfDatos[0]->setFechaModificacion(null);
        }
        return true;
    }

    /**
     * Listado de trámites envidos
     */
    public function enviadosAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $pathSystem = $this->session->get('pathSystem');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->getConnection()->prepare("select t.id,td.id as id_td,case when (ie.id is not null) then 'SIE:'||ie.id when (ie.id is null and ft.id=6) then 'SIE:' when ei.id is not null then 'RUDE: '|| e.codigo_rude when mi.id is not null then 'CI: '||pm.carnet when ai.id is not null then 'CI: '||pa.carnet end as codigo_tabla ,case when (ie.id is not null) then 'Institucion Educativa: '||ie.institucioneducativa when (ie.id is null and ft.id=6) then 'Institucion Educativa:' when ei.id is not null then 'Estudiante: '||e.nombre||' '||e.paterno||' '||e.materno when mi.id is not null then 'Maestro: '||pm.nombre||' '||pm.paterno||' '||pm.materno when ai.id is not null then 'Apoderado: '||pa.nombre||' '||pa.paterno||' '||pa.materno end as nombre_tabla,fp.ruta_reporte,ft.flujo,tt.tramite_tipo,pt.proceso_tipo,te.tramite_estado,td.fecha_envio,td.fecha_recepcion,td.obs,fp.plazo,case when fp.plazo is not null then td.fecha_recepcion + fp.plazo else null end as fecha_vencimiento,p.nombre
            from tramite t
            join tramite_detalle td on t.id =td.tramite_id
            left join institucioneducativa ie on t.institucioneducativa_id=ie.id
            left join wf_solicitud_tramite wft on td.id=wft.tramite_detalle_id
            left join tramite_detalle td1 on td1.id = wft.tramite_detalle_id
            left join flujo_proceso fp1 on td1.flujo_proceso_id =fp1.id
            left join estudiante_inscripcion ei on t.estudiante_inscripcion_id=ei.id
            left join estudiante e on ei.estudiante_id=e.id
            left join maestro_inscripcion mi on t.maestro_inscripcion_id=mi.id
            left join persona pm on mi.persona_id=pm.id
            left join apoderado_inscripcion ai on t.apoderado_inscripcion_id=ai.id
            left join persona pa on ai.persona_id=pa.id
            join flujo_proceso fp on td.flujo_proceso_id=fp.id
            join proceso_tipo pt on fp.proceso_id=pt.id
            join tramite_tipo tt on t.tramite_tipo=tt.id
            join tramite_estado te on td.tramite_estado_id=te.id
            join flujo_tipo ft on t.flujo_tipo_id = ft.id
            join usuario u on td.usuario_remitente_id=u.id
            join persona p on p.id=u.persona_id
            where ft.id>5 and fp.rol_tipo_id=". $rol ." and (te.id=15 or te.id=4)
            and wft.es_valido is true
            and td.usuario_remitente_id=". $usuario ." order by ft.flujo ASC,fecha_envio DESC");
        $query->execute();
        $data['entities'] = $query->fetchAll();;
        //dump($data);die;
        $data['titulo'] = "Listado de trámites enviados";
        return $this->render($pathSystem.':WfTramite:enviados.html.twig', $data);
    }
    
    /**
     * Listado de trámites concluidos
     */
    public function concluidosAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $pathSystem = $this->session->get('pathSystem');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->getConnection()->prepare("select t.id,ft.id as idflujo,ft.flujo,tt.tramite_tipo,t.fecha_fin,t.fecha_registro,t.fecha_fin-t.fecha_registro as duracion,case when (ie.id is not null) then 'SIE:'||ie.id when (ie.id is null and ft.id=6) then 'SIE:' when ei.id is not null then 'RUDE: '|| e.codigo_rude when mi.id is not null then 'CI: '||p.carnet when ai.id is not null then 'CI: '||pa.carnet end as codigo_tabla,case when ie.id is not null then 'Institucion Educativa: '||ie.institucioneducativa when ei.id is not null then 'Estudiante: '||e.nombre||' '||e.paterno||' '||e.materno when mi.id is not null then 'Maestro: '||p.nombre||' '||p.paterno||' '||p.materno when ai.id is not null then 'Apoderado: '||pa.nombre||' '||pa.paterno||' '||pa.materno end as nombre,'CONCLUIDO' as estado
        from tramite t
        join tramite_tipo tt on t.tramite_tipo=tt.id
        join flujo_tipo ft on t.flujo_tipo_id = ft.id
        left join institucioneducativa ie on t.institucioneducativa_id=ie.id
        left join estudiante_inscripcion ei on t.estudiante_inscripcion_id=ei.id
        left join estudiante e on ei.estudiante_id=e.id
        left join maestro_inscripcion mi on t.maestro_inscripcion_id=mi.id
        left join persona p on mi.persona_id=p.id
        left join apoderado_inscripcion ai on t.apoderado_inscripcion_id=ai.id
        left join persona pa on ai.persona_id=pa.id
        where ft.id>5 and t.fecha_fin is not null 
        order by ft.flujo,t.id,t.fecha_fin");
        $query->execute();
        $data['entities'] = $query->fetchAll();;
        $data['titulo'] = "Listado de trámites concluidos";
        return $this->render($pathSystem.':WfTramite:concluidos.html.twig', $data);
    }
    /**
     * Impresion de formularios como comprobantes
     */
    public function reporteFormularioAction(Request $request,$idtramite,$id_td)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $pathSystem = $this->session->get('pathSystem');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($id_td);
        /**
         * Verificamos si cuenta con una ruta de reporte
         **/
        if($tramiteDetalle->getFlujoProceso()->getRutaReporte()){
            return $this->redirectToRoute($tramiteDetalle->getFlujoProceso()->getRutaReporte(),array('idtramite' => $idtramite,'id_td'=>$id_td));
        }else{
            $request->getSession()
                    ->getFlashBag()
                    ->add('error', "La tarea: ". $flujoproceso->getProcesoTipo()->getProceso() ." correspondiente al tramite Nro. ". $id . "no cuenta con un reporte.");
                    return $this->redirectToRoute('wf_tramite_enviados');
        }
    }
        
    /**
     * lista el detalle d estado de cada tramite
     */
    public function recibidosDetalleAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $id_usuario = $this->session->get('userId');
        $pathSystem = $this->session->get('pathSystem');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $idtramite = $request->get('id');
        $flujotipo = $request->get('flujo');
       
        $detalle = $this->detalle($flujotipo,$idtramite); 
        return $this->render($pathSystem.':WfTramite:detalle.html.twig', array(
            'detalle' => $detalle['detalle'],'fecha_fin'=>$detalle['fecha_fin'],'idtramite'=>$idtramite,
        ));
        
    }

    /**
     * muestra formulario para la derivacion del tramite a otro usuario si es que la tarea  cuanta con mas de un usuario
     */
    public function recibidosDerivarAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $id_usuario = $this->session->get('userId');
        $pathSystem = $this->session->get('pathSystem');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $lugarTipoUsuario = $this->session->get('roluserlugarid');
        $idtramite = $request->get('id');
        $flujotipo = $request->get('flujo');

        $em = $this->getDoctrine()->getManager();

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        //dump($tramiteDetalle->getFlujoProceso()->getTareaSigId());die;
        if($tramiteDetalle->getFlujoProceso()->getEsEvaluacion() == true){
            $t = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$tramiteDetalle->getFlujoProceso()->getId(),'condicion'=>$tramiteDetalle->getValorEvaluacion()));
            $tarea = $t[0]->getCondicionTareaSiguiente();
        }else{
            $tarea = $tramiteDetalle->getFlujoProceso()->getTareaSigId();
        }
        $usuarios = $em->getRepository('SieAppWebBundle:WfUsuarioFlujoProceso')->findBy(array('flujoProceso'=>$tarea,'lugarTipoId'=>$lugarTipoUsuario));
        //dump($usuarios);die;
        $usuario = array();
    	foreach($usuarios as $u){
            $usuario[$u->getUsuario()->getid()] = $u->getUsuario()->getPersona()->getNombre()." ".$u->getUsuario()->getPersona()->getPaterno()." ".$u->getUsuario()->getPersona()->getMaterno();
        }
        //dump($usuario);die;
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('wf_tramite_recibido_derivar_guardar'))
            //->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO')))
            ->add('usuario','choice',array('label'=>'Usuario:','required'=>true,'choices'=>$usuario,'empty_value' => 'Seleccione usuario','attr' => array('class' => 'form-control')))
            ->add('idtramite','hidden',array('data'=>$idtramite,'required'=>false))
            ->add('idtd','hidden',array('data'=>$tramiteDetalle->getId(),'required'=>false))
            ->add('guardar','submit',array('label'=>'Derivar'))
            ->getForm();
        
        return $this->render($pathSystem.':WfTramite:derivar.html.twig', array(
            'form' => $form->createView(),'idtramite'=>$idtramite,
        ));
        
    }
    
    /**
     * guarda la rerivacion del tramite
     */
    public function recibidosDerivarGuardarAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $id_usuario = $this->session->get('userId');
        $pathSystem = $this->session->get('pathSystem');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $form = $request->get('form');
        $id_td = $form['idtd'];
        $idtramite = $form['idtramite'];
        $id_usuario = $form['usuario'];
        $em = $this->getDoctrine()->getManager();

        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($id_td);
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($id_usuario);
        $tramiteDetalle->setUsuariodestinatario($usuario);
        $em->flush();

        $request->getSession()
                ->getFlashBag()
                ->add('exito', "El Tramite Nro.:". $idtramite ." fué Derivado a: ".$usuario->getPersona()->getNombre()." ".$usuario->getPersona()->getPaterno()." ".$usuario->getPersona()->getMaterno());
        return $this->redirectToRoute('wf_tramite_recibido');
        
    }

    /**
     * modulos de seguimiento de un tramite
     */
    public function seguimientoTramiteAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $id_usuario = $this->session->get('userId');
        $pathSystem = $this->session->get('pathSystem');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
       
        $flujoSeguimientoForm = $this->createFlujoSeguimientoForm(); 
        return $this->render($pathSystem.':WfTramite:flujoSeguimiento.html.twig', array(
            'form' => $flujoSeguimientoForm->createView(),
        ));
        
    }

    public function createFlujoSeguimientoForm()
    {
        $form = $this->createFormBuilder()
            //->setAction($this->generateUrl('flujoproceso_guardar'))
            ->add('proceso','entity',array('label'=>'Trámite','required'=>true,'attr' => array('class' => 'form-control'),'class'=>'SieAppWebBundle:FlujoTipo','query_builder'=>function(EntityRepository $ft){
                return $ft->createQueryBuilder('ft')->where('ft.id > 5')->andWhere("ft.obs like '%ACTIVO%'")->orderBy('ft.flujo','ASC');},'property'=>'flujo','empty_value' => 'Seleccione trámite'))
            ->add('tramite','text',array('label'=>'Nro. de Trámite','required'=>true,'attr' => array('placeholder'=>'Nro. de trámite','class'=>'form-control validar')))
            ->getForm();
        return $form;
    }

    public function verFlujoAction(Request $request )
    {
        //dump($request);die;
        $form = $request->get('form');
        $id_usuario = $this->session->get('userId');
        $pathSystem = $this->session->get('pathSystem');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id'=>$form['tramite'],'flujoTipo'=>$form['proceso']));
        //dump($tramite);die;
        $data=array();
        if (!$tramite){
            //dump($data['nombre']);die;
            $mensaje = 'Número de tramite es incorrecto';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);
            
        }else{
            $data = $this->listarF($form['proceso'],$form['tramite']);
            //dump($data);die;
        }
        return $this->render($pathSystem.':WfTramite:flujo.html.twig',$data);
        
    }
        
    /**
     * funcion que lista el flujo de un tramite para el diagrama
     */
    public function listarF($flujotipo,$idtramite)
    {
        $em = $this->getDoctrine()->getManager();

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id'=>$idtramite,'flujoTipo'=>$flujotipo));
        
        if($tramite->getEstudianteInscripcion()){
            /**
            * TRAMITE PARA ESTUDIANTES
            */
            $data['tipo'] = "ESTUDIANTE: ";
            $data['nombre'] = $tramite->getEstudianteInscripcion()->getEstudiante()->getNombre() . ' ' . $tramite->getEstudianteInscripcion()->getEstudiante()->getPaterno() . ' ' . $tramite->getEstudianteInscripcion()->getEstudiante()->getMaterno();

            $query = $em->getConnection()->prepare("select p.id, p.flujo,d.estudiante, p.proceso_tipo, p.orden, p.es_evaluacion,p.variable_evaluacion, p.condicion, p.nombre,d.valor_evaluacion, p.condicion_tarea_siguiente, p.plazo, p.tarea_ant_id, p.tarea_sig_id, p.rol_tipo_id,d.id as td_id,d.tramite_id, d.flujo_proceso_id,d.fecha_registro,d.usuario_remitente_id,d.usuario_destinatario_id
            from
            (SELECT 
              fp.id, f.flujo, p.proceso_tipo, fp.orden, fp.es_evaluacion,fp.variable_evaluacion, wftc.condicion, wfc.nombre, wftc.condicion_tarea_siguiente, fp.plazo, fp.tarea_ant_id, fp.tarea_sig_id, fp.rol_tipo_id
            FROM 
              flujo_tipo f join flujo_proceso fp on f.id = fp.flujo_tipo_id
              join proceso_tipo p on p.id = fp.proceso_id
              left join wf_tarea_compuerta wftc on wftc.flujo_proceso_id = fp.id
              left join wf_compuerta wfc on wftc.wf_compuerta_id=wfc.id
            WHERE 
               f.id=". $flujotipo .")p
            LEFT JOIN
            (SELECT 
              t1.id,t1.tramite_id, t1.flujo_proceso_id,t1.fecha_registro,t1.usuario_remitente_id,t1.usuario_destinatario_id,(e.nombre||' '||e.paterno||' '||e.materno)as estudiante,t1.valor_evaluacion
            FROM 
              tramite_detalle t1 join tramite t on t1.tramite_id=t.id
              join estudiante_inscripcion ei on t.estudiante_inscripcion_id=ei.id
              join estudiante e on ei.estudiante_id=e.id
            where t1.tramite_id=". $idtramite .")d ON p.id=d.flujo_proceso_id order by p.orden,d.fecha_registro
            ");
            
        }elseif($tramite->getMaestroInscripcion()){
            $data['tipo'] = "MAESTRO: ";
            $data['nombre'] = $tramite->getMaestroInscripcion()->getPersona()->getNombre() . ' ' . $tramite->getMaestroInscripcion()->getPersona()->getPaterno() . ' ' . $tramite->getMaestroInscripcion()->getPersona()->getMaterno();

        }elseif($tramite->getApoderadoInscripcion()){
            $data['tipo'] = "APODERADO: ";
            $data['nombre'] = $tramite->getApoderadoInscripcion()->getPersona()->getNombre() . ' ' . $tramite->getApoderadoInscripcion()->getPersona()->getPaterno() . ' ' . $tramite->getApoderadoInscripcion()->getPersona()->getMaterno();
        
        }else{
            /**
            * TRAMITE PARA UNIDADES EDUCATIVAS
            */
            $data['tipo'] = "UNIDAD EDUCATIVA: ";
            if($tramite->getInstitucioneducativa()){
                $data['nombre'] = $tramite->getInstitucioneducativa()->getInstitucioneducativa();
            }else{
                $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                    ->select('wfd')
                    ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                    ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = td.flujoProceso')
                    ->where('td.tramite='.$idramite)
                    ->andWhere("fp.orden=1")
                    ->andWhere("wfd.esValido=true")
                    ->getQuery()
                    ->getResult();
    
                $datos = json_decode($wfdatos[0]->getDatos(),true);
                //dump($datos);die;
                $data['nombre'] = $datos['institucionEducativa'];
            }

            $query = $em->getConnection()->prepare('select p.id, p.flujo,d.institucioneducativa, p.proceso_tipo, p.orden, p.es_evaluacion,p.variable_evaluacion, p.condicion, p.nombre,d.valor_evaluacion, p.condicion_tarea_siguiente, p.plazo, p.tarea_ant_id, p.tarea_sig_id, p.rol_tipo_id,d.id as td_id,d.tramite_id, d.flujo_proceso_id,d.fecha_recepcion,d.fecha_envio,d.usuario_remitente,d.usuario_destinatario,d.obs,d.tramite_estado,d.fecha_envio-d.fecha_recepcion as duracion
                from
                (SELECT fp.id, f.flujo, p.proceso_tipo, fp.orden, fp.es_evaluacion,fp.variable_evaluacion, wftc.condicion, wfc.nombre, wftc.condicion_tarea_siguiente, fp.plazo, fp.tarea_ant_id, fp.tarea_sig_id, fp.rol_tipo_id
                    FROM flujo_tipo f join flujo_proceso fp on f.id = fp.flujo_tipo_id
                    join proceso_tipo p on p.id = fp.proceso_id
                    left join wf_tarea_compuerta wftc on wftc.flujo_proceso_id = fp.id
                    left join wf_compuerta wfc on wftc.wf_compuerta_id=wfc.id
                    WHERE f.id='. $flujotipo .' order by fp.orden)p
                LEFT JOIN
                (SELECT t1.id,t1.tramite_id, t1.flujo_proceso_id,te.tramite_estado,t1.fecha_recepcion,t1.fecha_envio,pr.nombre as usuario_remitente,pd.nombre as usuario_destinatario,i.institucioneducativa,t1.valor_evaluacion,t1.obs
                    FROM tramite_detalle t1 join tramite t on t1.tramite_id=t.id
                    join tramite_estado te on t1.tramite_estado_id=te.id
                    left join wf_solicitud_tramite wfs on wfs.tramite_detalle_id=t1.id
                    left join usuario ur on t1.usuario_remitente_id=ur.id
                    left join persona pr on ur.persona_id=pr.id
                    left join usuario ud on t1.usuario_destinatario_id=ud.id
                    left join persona pd on ud.persona_id=pd.id
                    left join institucioneducativa i on t.institucioneducativa_id=i.id
                    where t1.tramite_id='. $idtramite .' and (wfs.es_valido=true or wfs.id ISNULL) order by t1.id)d
                ON p.id=d.flujo_proceso_id order by p.orden,d.fecha_envio');
        }
        
        $query->execute();
        $arrData = $query->fetchAll();
        //dump($arrData);die;
        $data['flujo']=$arrData;
        $detalle = $this->detalle($flujotipo,$idtramite);
        $data['flujoDetalle'] = $detalle['detalle'];
        $data['fecha_fin'] = $detalle['fecha_fin'];
        $data['flujotipo'] = $flujotipo;
        //dump($data);die;
        return $data;
    }

    /**
     * lista el detalle de un tramite
     */
    public function detalle($flujotipo,$idtramite)
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare('select p.id, p.flujo,d.institucioneducativa, p.proceso_tipo, p.orden, p.es_evaluacion,p.variable_evaluacion,d.valor_evaluacion, p.plazo, p.tarea_ant_id, p.tarea_sig_id, p.rol_tipo_id,d.id as td_id,d.tramite_id, d.flujo_proceso_id,d.fecha_recepcion,d.fecha_envio,d.usuario_remitente,d.usuario_destinatario,d.obs,d.tramite_estado,d.tramite_estado_id,d.fecha_envio-d.fecha_recepcion as duracion,case when p.plazo is not null then d.fecha_recepcion + p.plazo else null end as fecha_vencimiento,d.fecha_fin
        from
        (SELECT 
          fp.id, f.flujo, p.proceso_tipo, fp.orden, fp.es_evaluacion,fp.variable_evaluacion,fp.plazo, fp.tarea_ant_id, fp.tarea_sig_id, fp.rol_tipo_id
        FROM 
          flujo_tipo f join flujo_proceso fp on f.id = fp.flujo_tipo_id
          join proceso_tipo p on p.id = fp.proceso_id
        WHERE 
           f.id='. $flujotipo .' order by fp.orden)p
        LEFT JOIN
        (SELECT 
          t1.id,t1.tramite_id, t1.flujo_proceso_id,t.fecha_fin,te.tramite_estado,te.id as tramite_estado_id,t1.fecha_recepcion,t1.fecha_envio,pr.nombre as usuario_remitente,pd.nombre as usuario_destinatario,i.institucioneducativa,t1.valor_evaluacion,t1.obs
	    FROM 
          tramite_detalle t1 join tramite t on t1.tramite_id=t.id
          join tramite_estado te on t1.tramite_estado_id=te.id
          left join usuario ur on t1.usuario_remitente_id=ur.id
          left join persona pr on ur.persona_id=pr.id
          left join usuario ud on t1.usuario_destinatario_id=ud.id
          left join persona pd on ud.persona_id=pd.id
          left join institucioneducativa i on t.institucioneducativa_id=i.id
        where t1.tramite_id='. $idtramite .' order by t1.id)d
        ON p.id=d.flujo_proceso_id order by d.id,p.id');

        $query->execute();
        $arrData = $query->fetchAll();
        //dump($arrData);die;
        $detalle['detalle']=$arrData;
        $detalle['fecha_fin']=$arrData[0]['fecha_fin'];
        return $detalle;
        
    }
}