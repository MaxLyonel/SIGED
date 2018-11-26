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
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findBy(array('flujoTipo'=>$id,'orden'=>1));
        //dump($flujoproceso);die;
        if($flujoproceso[0]->getRolTipo()->getId()!= 9){
            $wfusuario = $em->getRepository('SieAppWebBundle:WfUsuarioFlujoProceso')->createQueryBuilder('wfufp')
                ->select('wfufp')
                ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = wfufp.flujoProceso')
                ->where('fp.orden=1')
                ->andWhere('fp.flujoTipo='.$id)
                ->andWhere('wfufp.usuario='.$usuario)
                ->andWhere('wfufp.esactivo=true')
                ->andWhere('fp.rolTipo='.$rol)
                ->getQuery()
                ->getResult();
            if($wfusuario){
                return $this->redirectToRoute($flujoproceso[0]->getRutaFormulario(),array('id'=>$id));
            }else{
                $request->getSession()
                        ->getFlashBag()
                        ->add('error', "No tiene tuición para un nuevo tramite");
                return $this->redirectToRoute('wf_tramite_index');
            }    
        }else{
            if($rol == $flujoproceso[0]->getRolTipo()->getId()){
                return $this->redirectToRoute($flujoproceso[0]->getRutaFormulario(),array('id'=>$id));    
            }else{
                $request->getSession()

                    ->getFlashBag()
                    ->add('error', "No tiene tuición para un nuevo tramite");
                    return $this->redirectToRoute('wf_tramite_index');    
            }
        }
        //dump($wfusuario);die;
        //Verificamos si tiene competencia para un nuevo tramite
        
        /*switch ($id) {
            case 5: //RITT
            
                break;
            case 6: //RUE
                if($rol == 10){
                    return $this->redirectToRoute('tramite_rue_recepcion_distrito_nuevo');
                }else{
                    $request->getSession()
                    ->getFlashBag()
                    ->add('error', "No tiene tuición para un nuevo tramite RUE");
                    return $this->redirectToRoute('wf_tramite_index');
                }    
                break;
            case 7://BTH
                if($rol == 9){
                    return $this->redirectToRoute('solicitud_bth_nuevasolicitud');
                }else{
                    $request->getSession()
                    ->getFlashBag()
                    ->add('error', "No tiene tuición para un nuevo tramite BTH");
                    return $this->redirectToRoute('wf_tramite_index');
                }
                break;
        }*/
        
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
        
        $query = $em->getConnection()->prepare("select distinct t.id,ft.flujo,ft.id as idflujo,case when te.id=3 then pt.proceso_tipo when te.id=15 or te.id=16  and fp.es_evaluacion is false then ptsig.proceso_tipo when te.id=15 or te.id=16 and fp.es_evaluacion is true then ptc.proceso_tipo  end as proceso_tipo,pt.proceso_tipo as tarea_actual,tt.tramite_tipo,te.tramite_estado,case when te.id = 3 then td.fecha_recepcion else td.fecha_envio end as fecha_estado,te.id as id_estado,td.obs,fp.plazo,case when te.id = 3 then td.fecha_recepcion + fp.plazo else null end as fecha_vencimiento,p.nombre
        from tramite t
        join tramite_detalle td on cast(t.tramite as int)=td.id
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
        where ft.id>4 and t.fecha_fin is null and 
        ((fpsig.rol_tipo_id=". $rol ." and (te.id=15 or te.id=16) and fp.es_evaluacion is false) or 
        (fp.rol_tipo_id=". $rol ." and te.id=3) or 
        ((select rol_tipo_id from flujo_proceso where id= wftc.condicion_tarea_siguiente)=". $rol ." and (te.id=15 or te.id=16) and fp.es_evaluacion is true and td.valor_evaluacion=wftc.condicion) ) and td.usuario_destinatario_id=".$usuario." 
        order by ft.flujo,te.tramite_estado,fecha_estado,t.id,proceso_tipo,tt.tramite_tipo,id_estado,td.obs,p.nombre");
        $query->execute();
        $data['entities'] = $query->fetchAll();;
        $data['titulo'] = "Listado de trámites recibidos";
        return $this->render($pathSystem.':WfTramite:recibidos.html.twig', $data);
    }

    /**
     * Registro del tramite como recibido
     */
    public function recibidosGuardarAction(Request $request,$id)
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
        $idtramite = $id;
        if($tramiteDetalle->getFlujoProceso()->getEsEvaluacion() == true){
            $t = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$tramiteDetalle->getFlujoProceso()->getId(),'condicion'=>$tramiteDetalle->getValorEvaluacion()));
            $tarea = $t[0]->getCondicionTareaSiguiente();
        }else{
            $tarea = $tramiteDetalle->getFlujoProceso()->getTareaSigId();
        }

        $mensaje = $this->guardarTramiteRecibido($usuario,$tarea,$idtramite);
        $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
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
        //Verificamos si tiene competencia para un nuevo tramite
        if($rol == $flujoproceso->getRolTipo()->getId()){
            //return $this->redirectToRoute('tramite_rue_informe_distrito_nuevo', array('id' => $tramite->getId()));
            return $this->redirectToRoute($flujoproceso->getRutaFormulario(),array('id' => $tramite->getId()));
        }else{
            $request->getSession()
                    ->getFlashBag()
                    ->add('error', "No tiene tuición para un nuevo tramite RUE");
                    return $this->redirectToRoute('wf_tramite_recibido');
        }  

        /*if($tramite->getFlujoTipo()->getId() == 6){
            switch ($tramiteDetalle->getFlujoProceso()->getId()) {
                case 40:
                    return $this->redirectToRoute('tramite_rue_informe_distrito_nuevo', array('id' => $tramite->getId()));
                    //return $this->redirectToRoute('wf_tramite_index');
                    break;
                case 41:
                    return $this->redirectToRoute('tramite_rue_recepcion_departamental_nuevo', array('id' => $tramite->getId()));
                    break;
                case 42:
                    return $this->redirectToRoute('tramite_rue_verifica_subdireccion_departamental_nuevo', array('id' => $tramite->getId()));
                    break;
                case 44:
                    return $this->redirectToRoute('tramite_rue_verifica_juridica_departamental_nuevo', array('id' => $tramite->getId()));
                    break;
                case 47:
                    return $this->redirectToRoute('tramite_rue_envia_formularios_departamental_nuevo', array('id' => $tramite->getId()));
                    break;
            }

        }elseif ($tramite->getFlujoTipo()->getId() == 7){
            switch ($tramiteDetalle->getFlujoProceso()->getId()) {
                case 5:
                    return $this->redirectToRoute('wf_tramite_index', array('idtramite' => $tramite->getId(),'idtarea'=>$tramiteDetalle->getFlujoProceso()->getId()));
                    //return $this->redirectToRoute('wf_tramite_index');
                    break;
                case 6:
                    return $this->redirectToRoute('wf_tramite_index');
                    break;
                case 7:
                    break;
            }
        }*/

        //return $this->redirectToRoute('wf_tramite_recibido');

        //return $this->render('SieHerramientaBundle:WfTramite:recibidos.html.twig');
    }
    
    //public function guardarTramiteNuevo($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$tipotramite,$varevaluacion,$idtramite,$datos,$lugarTipoLocalidad_id,$lugarTipoDistrito_id)
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
        $tramiteDetalle->setObs($observacion);
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
            $tarea_sig_id = $wfcondiciontarea[0]->condicionTareaSiguiente();
            $uDestinatario = $this->obtieneUsuarioDestinatario($tarea,$tarea_sig_id,$id_tabla,$tabla,$tramite);
        }else{
            $tarea_sig_id = $flujoproceso->getTareaSigId();
            $uDestinatario = $this->obtieneUsuarioDestinatario($tarea,$tarea_sig_id,$id_tabla,$tabla,$tramite);
        }
        $tramiteDetalle->setUsuarioDestinatario($uDestinatario);
        $em->flush();

        /**
         * actualizamos ultima tarea del tramite
         */
        $tramite->setTramite($tramiteDetalle->getId());
        $em->flush();
        $mensaje = 'El trámite Nro. '. $tramite->getId() .' se guardo correctamente';
        return $mensaje;
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
        $mensaje = 'El trámite Nro. '. $tramite->getId() .' se recibió correctamente';
        return $mensaje;
    }
    
    
    //public function guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion,$idtramite,$datos,$lugarTipoLocalidad_id,$lugarTipoDistrito_id)
    /**
     * funcion general para guardar una tarea de un tramite
     */
    public function guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion,$idtramite,$datos,$lugarTipoLocalidad_id,$lugarTipoDistrito_id)
    {

        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('tramite_detalle');")->execute();
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea);
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($usuario);
        //$tramiteestado = $em->getRepository('SieAppWebBundle:TramiteEstado')->find(15);
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        /**
         * Modificacion de datos propios de la solicitud
         */
        /*if ($datos){
            $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
                ->select('wf')
                ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = wf.tramite')
                ->where('t.id =' . $tramite->getId())
                ->getQuery()
                ->getResult();
            if($wfSolicitudTramite){
                //datos de la solicitud
                $wfSolicitudTramite[0]->setDatos($datos);
                $wfSolicitudTramite[0]->setEsValido(true);
                $wfSolicitudTramite[0]->setFechaModificacion(new \DateTime(date('Y-m-d H:i:s')));
                $em->flush();
            }
        }*/
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
         * asigana usuario destinatario
         */
        //dump($flujoproceso);die;
        if ($flujoproceso->getEsEvaluacion() == true) 
        {
            $tramiteDetalle->setValorEvaluacion($varevaluacion);
            $wfcondiciontarea = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$flujoproceso->getId(),'condicion'=>$varevaluacion));
            //dump($wfcondiciontarea);die;
            if ($wfcondiciontarea[0]->getCondicionTareaSiguiente() != null){
                $tarea_sig_id = $wfcondiciontarea[0]->getCondicionTareaSiguiente();
                //
                $uDestinatario = $this->obtieneUsuarioDestinatario($tarea,$tarea_sig_id,$id_tabla,$tabla,$tramite);
                //dump($uDestinatario);die;
                $tramiteDetalle->setUsuarioDestinatario($uDestinatario);
            }else{
                /**
                 * si despues de la evaluacion termina el tramite
                 */
                $tramite->setFechaFin(new \DateTime(date('Y-m-d')));    
            }
        }else{
            if ($flujoproceso->getTareaSigId() != null){
                $tarea_sig_id = $flujoproceso->getTareaSigId();
                $uDestinatario = $this->obtieneUsuarioDestinatario($tarea,$tarea_sig_id,$id_tabla,$tabla,$tramite);
                $tramiteDetalle->setUsuarioDestinatario($uDestinatario);
            }
        }
        /**
         * si el tramite es devuelto
         */
        if ($flujoproceso->getTareaSigId() != null){
            if($tarea_sig_id > $flujoproceso->getId()){
                $tramiteestado = $em->getRepository('SieAppWebBundle:TramiteEstado')->find(15); //enviado
            }else{
                $tramiteestado = $em->getRepository('SieAppWebBundle:TramiteEstado')->find(16); //devuelto
            }
        }else{
            $tramiteestado = $em->getRepository('SieAppWebBundle:TramiteEstado')->find(15); //enviado
        }
        //dump($tramiteestado);die;

         /**
         * guarda tramite enviado/devuelto
         */
        $tramiteDetalle->setObs($observacion);
        $tramiteDetalle->setFechaEnvio(new \DateTime(date('Y-m-d')));
        $tramiteDetalle->setTramiteEstado($tramiteestado);
        //dump($tramiteDetalle->getTramiteEstado());die;
        $em->flush();
        //dump($tramiteDetalle->getTramiteEstado());die;
      
        /**
         * si es la ultima tarea del tramite se finaliza el tramite
         */
        if ($flujoproceso->getTareaSigId() == null)
        {
            $tramite->setFechaFin(new \DateTime(date('Y-m-d')));
            $em->flush();
        }
        $mensaje = 'El trámite Nro. '. $tramite->getId() .' se envió correctamente';
        //dump($tramiteDetalle->getTramiteEstado());die;
        return $mensaje;
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
                    //dump($lugar_tipo_departamento);die;
                    
                }
                switch ($nivel->getId()) {
                    case 7:   // Distrito
                        //dump($lugar_tipo_distrito);die;
                        $query = $em->getConnection()->prepare("select * from wf_usuario_flujo_proceso where flujo_proceso_id=". $flujoprocesoSiguiente->getId()." and esactivo is true and  lugar_tipo_id=".$lugar_tipo_distrito);
                        $query->execute();
                        $uDestinatario = $query->fetchAll();
                        if(count($uDestinatario)>1){
                            $uid = $this->asiganaUsuarioDestinatario($tarea_actual,$tarea_sig_id,$lugar_tipo_distrito);
                        }else{
                            $uid = $uDestinatario[0]['usuario_id'];
                        }
                        //$uDestinatario = $em->getRepository('SieAppWebBundle:UsuarioFlujoProceso')->findBy(array('flujoProceso'=>$flujoprocesoSiguiente->getId(),'lugarTipoId'=>$lugar_tipo_distrito));
                        break;
                    case 6:   // Departamento
                    case 8:
                        //dump($lugar_tipo_departamento);die;
                        $query = $em->getConnection()->prepare("select * from wf_usuario_flujo_proceso ufp join lugar_tipo lt on ufp.lugar_tipo_id=lt.id where ufp.flujo_proceso_id=". $flujoprocesoSiguiente->getId()." and ufp.esactivo is true and cast(lt.codigo as int)=".$lugar_tipo_departamento);
                        $query->execute();
                        $uDestinatario = $query->fetchAll();
                        if(count($uDestinatario)>1){
                            $uid = $this->asiganaUsuarioDestinatario($tarea_actual,$tarea_sig_id,$lugar_tipo_departamento);
                        }else{
                            $uid = $uDestinatario[0]['usuario_id'];
                        }
                        //$uDestinatario = $em->getRepository('SieAppWebBundle:UsuarioFlujoProceso')->findBy(array('flujoProceso'=>$flujoprocesoSiguiente->getId(),'lugarTipoId'=>$lugar_tipo_departamento));
                        break;
                    case 0://nivel nacional
                        //dump($flujoprocesoSiguiente->getRolTipo()->getId());die;
                        if($flujoprocesoSiguiente->getRolTipo()->getId() == 9){  // si es director
                            $query = $em->getConnection()->prepare("select u.* from maestro_inscripcion m
                            join usuario u on m.persona_id=u.persona_id
                            where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=".(new \DateTime())->format('Y')." and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
                            $query->execute();
                            $uDestinatario = $query->fetchAll();
                            //dump($uDestinatario);die;
                            $uid = $uDestinatario[0]['id'];
                            //$uDestinatario = $em->getRepository('SieAppWebBundle:UsuarioFlujoProceso')->findBy(array('flujoProceso'=>$flujoprocesoSiguiente->getId(),'lugarTipoId'=>1));
                        }elseif($flujoprocesoSiguiente->getRolTipo()->getId() == 8){ // si es tecnico nacional
                            $query = $em->getConnection()->prepare("select * from wf_usuario_flujo_proceso ufp where ufp.esactivo is true and ufp.flujo_proceso_id=". $flujoprocesoSiguiente->getId()." and lugar_tipo_id=1");
                            $query->execute();
                            $uDestinatario = $query->fetchAll();
                            //dump(count($uDestinatario));die;
                            if(count($uDestinatario)>1){
                                $uid = $this->asiganaUsuarioDestinatario($tarea_actual,$tarea_sig_id,1);
                            }else{
                                $uid = $uDestinatario[0]['usuario_id'];
                            }
                            //$uDestinatario = $em->getRepository('SieAppWebBundle:UsuarioFlujoProceso')->findBy(array('flujoProceso'=>$flujoprocesoSiguiente->getId(),'lugarTipoId'=>1));
                        }
                        break;
                }
                break;
            case 'estudiante_inscripcion':
                break;
            case 'apoderado_inscripcion':
                break;
            case 'maestro_inscripcion':
                break;
        }
        
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($uid);
        //dump($usuario);die;
        return $usuario;
    }

    /**
     * funcion que asigna usuario destinatario si la tarea tiene mas de un usuario registrado
     */
    public function asiganaUsuarioDestinatario($tarea_actual_id,$tarea_sig_id,$lugar_tipo)
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare("select a.usuario_id,case when b.nro is null then 0 else b.nro end as nro
        from 
        (select usuario_id from wf_usuario_flujo_proceso wf
        where wf.flujo_proceso_id=". $tarea_sig_id ." and wf.esactivo is true and wf.lugar_tipo_id=". $lugar_tipo .")a
        left join 
        (select td.usuario_destinatario_id,count(*) as nro
        from tramite t
        join tramite_detalle td on cast(t.tramite as int)=td.id
        where flujo_proceso_id=". $tarea_actual_id ." and td.tramite_estado_id=15 group by td.usuario_destinatario_id)b on a.usuario_id=b.usuario_destinatario_id  order by b.nro desc");
        $query->execute();
        $usuarios = $query->fetchAll();
        //dump($usuarios);die;
        $uid = $usuarios[0]['usuario_id'];
        //dump($uid);die;
        return $uid;
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
        
        $query = $em->getConnection()->prepare("select t.id,ft.flujo,tt.tramite_tipo,pt.proceso_tipo,te.tramite_estado,td.fecha_envio,td.fecha_recepcion,td.obs,fp.plazo,case when fp.plazo is not null then td.fecha_recepcion + fp.plazo else null end as fecha_vencimiento,p.nombre
            from tramite t
            join tramite_detalle td on t.id =td.tramite_id
            join flujo_proceso fp on td.flujo_proceso_id=fp.id
            join proceso_tipo pt on fp.proceso_id=pt.id
            join tramite_tipo tt on t.tramite_tipo=tt.id
            join tramite_estado te on td.tramite_estado_id=te.id
            join flujo_tipo ft on t.flujo_tipo_id = ft.id
            join usuario u on td.usuario_remitente_id=u.id
            join persona p on p.id=u.persona_id
            where ft.id>4 and fp.rol_tipo_id=". $rol ." and (te.id=15 or te.id=16)
            and td.usuario_remitente_id=". $usuario ." order by ft.flujo,t.id,fecha_envio,fp.orden");
        $query->execute();
        $data['entities'] = $query->fetchAll();;
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
        
        $query = $em->getConnection()->prepare("select t.id,ft.id as idflujo,ft.flujo,tt.tramite_tipo,t.fecha_fin,t.fecha_registro,t.fecha_fin-t.fecha_registro as duracion,case when ie.id is not null then 'Institucion Educativa: '||ie.institucioneducativa when ei.id is not null then 'Estudiante: '||e.nombre||' '||e.paterno||' '||e.materno when mi.id is not null then 'Maestro: '||p.nombre||' '||p.paterno||' '||p.materno when ai.id is not null then 'Apoderado: '||pa.nombre||' '||pa.paterno||' '||pa.materno end as nombre,'CONCLUIDO' as estado
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
        where ft.id>4 and t.fecha_fin is not null 
        order by ft.flujo,t.id,t.fecha_fin");
        $query->execute();
        $data['entities'] = $query->fetchAll();;
        $data['titulo'] = "Listado de trámites concluidos";
        return $this->render($pathSystem.':WfTramite:concluidos.html.twig', $data);
    }

    /**
     * funcion del listado de tramites recibidos especificao para el flujo ritt
     */
    /*public function tramiteTareaRitt($tarea_ant,$tarea_actual,$flujotipo,$usuario,$rol)
    {
        $em = $this->getDoctrine()->getManager();
        //dump($lugarTipo);die;
        $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$usuario,'rolTipo'=>$rol));            
        $idlugarusuario = $usuariorol[0]->getLugarTipo()->getCodigo();
        //dump($usuariorol);die;
        //dump((int)$idlugarusuario);die;
         //tareas devuelta por condicion
        $wftareac = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->createQueryBuilder('wf')
                ->select('fp.id,wf.condicion')
                ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = wf.flujoProceso')
                ->where('wf.condicionTareaSiguiente =' . $tarea_actual)
                ->getQuery()
                ->getResult();
        //tarea devuelta
        $fp = $em->getRepository('SieAppWebBundle:FlujoProceso')->createQueryBuilder('fp')
                ->select('fp.id')
                ->where('fp.tareaSigId =' . $tarea_actual)
                ->getQuery()
                ->getResult();
        //tarea anterior
        $tarea = 'td.flujo_proceso_id='. $tarea_ant;
        if($wftareac and $fp){
            $tarea = "(" . $tarea . " or (td.flujo_proceso_id=". $wftareac[0]['id'] ." and td.valor_evaluacion='". $wftareac[0]['condicion'] ."') or td.flujo_proceso_id=". $fp[0]['id']. ")";
        }elseif ($wftareac){
            $tarea = "(" . $tarea . " or (td.flujo_proceso_id=". $wftareac[0]['id'] ." and td.valor_evaluacion='". $wftareac[0]['condicion'] ."'))";
        }elseif ($fp){
            $tarea = "(" . $tarea . " or td.flujo_proceso_id=". $fp[0]['id']. ")";
        }
        //dump($wftareac);die;
        //si la tarea anterior tiene evaluacion
        $query1 = $em->getConnection()->prepare('select * from flujo_proceso where id=' . $tarea_ant . ' and es_evaluacion=true');
        $query1->execute();
        $evaluacion = $query1->fetchAll();
        if($rol == 7){ // departamental
            if ($evaluacion)
            {
                
                $query = $em->getConnection()->prepare("select t.id,t.td_id,ie.institucioneducativa_id,ie.institucioneducativa,ie.sede,t.tramite_tipo,t.fecha_registro,t.obs,t.nombre,t.estado
                from
                (select se.institucioneducativa_id, se.sede,ie.institucioneducativa
                from ttec_institucioneducativa_sede se
                join institucioneducativa ie on se.institucioneducativa_id=ie.id
                join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
                left join tramite t on ie.id=t.institucioneducativa_id
                where t.fecha_fin is null and se.estado =true and ie.institucioneducativa_tipo_id in (7,8,9) and ie.estadoinstitucion_tipo_id=10 and le.lugar_tipo_id_localidad in (select id from lugar_tipo where lugar_tipo_id in (select id from lugar_tipo where lugar_tipo_id in (select id from lugar_tipo where lugar_tipo_id in(select id from lugar_tipo where lugar_tipo_id in (select id from lugar_tipo where codigo='". (int)$idlugarusuario ."' and lugar_nivel_id=1))))))ie
                left join
                (select t.id,td.id as td_id,t.institucioneducativa_id,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t
                join tramite_detalle td on cast(t.tramite as int)=td.id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea ." and td.valor_evaluacion = (select condicion from wf_tarea_compuerta where flujo_proceso_id=". $tarea_ant ." and condicion_tarea_siguiente=". $tarea_actual . "))t on ie.institucioneducativa_id=t.institucioneducativa_id");
            }else{
                $query = $em->getConnection()->prepare("select t.id,t.td_id,ie.institucioneducativa_id,ie.institucioneducativa,ie.sede,t.tramite_tipo,t.fecha_registro,t.obs,t.nombre,t.estado
                from
                (select se.institucioneducativa_id, se.sede,ie.institucioneducativa
                from ttec_institucioneducativa_sede se
                join institucioneducativa ie on se.institucioneducativa_id=ie.id
                join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
                left join tramite t on ie.id=t.institucioneducativa_id
                where t.fecha_fin is null and se.estado =true and ie.institucioneducativa_tipo_id in (7,8,9) and ie.estadoinstitucion_tipo_id=10 and le.lugar_tipo_id_localidad in (select id from lugar_tipo where lugar_tipo_id in (select id from lugar_tipo where lugar_tipo_id in (select id from lugar_tipo where lugar_tipo_id in(select id from lugar_tipo where lugar_tipo_id in (select id from lugar_tipo where codigo='". (int)$idlugarusuario ."' and lugar_nivel_id=1))))))ie
                left join
                (select t.id,td.id as td_id,t.institucioneducativa_id,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t
                join tramite_detalle td on cast(t.tramite as int)=td.id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea .")t on ie.institucioneducativa_id=t.institucioneducativa_id");
            }
        }elseif($rol == 8){ 
            if ($evaluacion)
            {
                
                $query = $em->getConnection()->prepare("select t.id,ie.id as codrie,ie.institucioneducativa,lt4.lugar,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
                join institucioneducativa ie on t.institucioneducativa_id=ie.id
                join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
                left join lugar_tipo lt on lt.id = le.lugar_tipo_id_localidad
                left join lugar_tipo lt1 on lt1.id = lt.lugar_tipo_id
                left join lugar_tipo lt2 on lt2.id = lt1.lugar_tipo_id
                left join lugar_tipo lt3 on lt3.id = lt2.lugar_tipo_id
                left join lugar_tipo lt4 on lt4.id = lt3.lugar_tipo_id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea ." and td.valor_evaluacion = (select condicion from wf_tarea_compuerta where flujo_proceso_id=". $tarea_ant ." and condicion_tarea_siguiente=". $tarea_actual . ")");
            }else{
                $query = $em->getConnection()->prepare("select t.id,ie.id as codrie,ie.institucioneducativa,lt4.lugar,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
                join institucioneducativa ie on t.institucioneducativa_id=ie.id
                join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
                left join lugar_tipo lt on lt.id = le.lugar_tipo_id_localidad
                left join lugar_tipo lt1 on lt1.id = lt.lugar_tipo_id
                left join lugar_tipo lt2 on lt2.id = lt1.lugar_tipo_id
                left join lugar_tipo lt3 on lt3.id = lt2.lugar_tipo_id
                left join lugar_tipo lt4 on lt4.id = lt3.lugar_tipo_id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea);
            }
        }
        $query->execute();
        $tramites = $query->fetchAll();
        //dump($tramites);die;
        $data['tramites'] = $tramites;
        return $data;
    }*/
    
    /**
     * funcion anterior que guarda la tarea de cada tramite, actualmente utilizada para el tramite ritt desde el bundle procesos
     */
    public function guardarTramiteDetalle($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$tipotramite,$varevaluacion,$idtramite,$datos,$lugarTipo_id)
    {

        //dump($datos);die;
        $tramiteDetalle = new TramiteDetalle();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('tramite_detalle');")->execute();
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea);
        
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($usuario);
        $tramiteestado = $em->getRepository('SieAppWebBundle:TramiteEstado')->find(1);
        
        //insert tramite
        if($flujoproceso->getOrden() == 1 and $idtramite == ""){
            
            $tramite = new Tramite();
            $wfSolicitudTramite = new WfSolicitudTramite();
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('tramite');")->execute();
            $flujotipo = $em->getRepository('SieAppWebBundle:FlujoTipo')->find($flujotipo);
            $tramitetipo = $em->getRepository('SieAppWebBundle:TramiteTipo')->find($tipotramite);
            //dump($tramitetipo);die;
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
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('wf_solicitud_tramite');")->execute();
            //dump($tramite);die;
            if ($datos){
                //datos propios de la solicitud
                $wfSolicitudTramite->setTramite($tramite);
                $wfSolicitudTramite->setDatos($datos);
                $wfSolicitudTramite->setEsValido(true);
                $wfSolicitudTramite->setFechaRegistro(new \DateTime(date('Y-m-d H:i:s')));
                $wfSolicitudTramite->setLugarTipoId($lugarTipo_id);
                $em->persist($wfSolicitudTramite);
                $em->flush();
            }
        }else{
            /*$query = $em->getConnection()->prepare('select * from tramite_detalle where flujo_proceso_id='. $flujoproceso->getTareaAntId());
            $query->execute();
            $tramiteD = $query->fetchAll();*/
            //dump($idtramite);die;
            //Modificacion de datos propios de la solicitud
            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
            $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
                ->select('wf')
                ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = wf.tramite')
                ->where('t.id =' . $tramite->getId())
                ->getQuery()
                ->getResult();
            //dump($wfSolicitudTramite);die;
            if($wfSolicitudTramite){
                //datos de la solicitud
                $wfSolicitudTramite[0]->setDatos($datos);
                $wfSolicitudTramite[0]->setEsValido(true);
                $wfSolicitudTramite[0]->setFechaModificacion(new \DateTime(date('Y-m-d H:i:s')));
                $em->flush();
            }
            //dump($tramite);die;
            //$tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($tramiteD[0]->getTramite()->getId());
        }
        //insert tramite_detalle
        //dump($tramiteD);die;
        $tramiteDetalle->setObs($observacion);
        $tramiteDetalle->setTramite($tramite);
        $tramiteDetalle->setTramiteEstado($tramiteestado);
        $tramiteDetalle->setFlujoProceso($flujoproceso);
        $tramiteDetalle->setFechaRegistro(new \DateTime(date('Y-m-d')));
        $tramiteDetalle->setFechaEnvio(new \DateTime(date('Y-m-d')));
        $tramiteDetalle->setFechaRecepcion(new \DateTime(date('Y-m-d')));
        $tramiteDetalle->setUsuarioRemitente($usuario);
        /** */
        if ($idtramite!="")
        {
            $td_anterior = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
            $tramiteDetalle->setTramiteDetalle($td_anterior);
        }
        //dump($flujoproceso);die;
        if ($flujoproceso->getEsEvaluacion() == true) 
        {
            $tramiteDetalle->setValorEvaluacion($varevaluacion);
        }
        if($flujoproceso->getWfAsignacionTareaTipo()->getId() == 3) //asignacion por seleccion
        {
               if($idtramite != "")
               {
                    $query = $em->getConnection()->prepare('select * from tramite_detalle where id='. (int)$tramite->getTramite().' and tramite_id='.$idtramite);
                    $query->execute();
                    $td = $query->fetchAll();
                    $tramiteD = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($td[0]['id']);
                    $tramiteD->setUsuarioDestinatario($usuario);
                    //$em->persist($tramiteD);
                    $em->flush();
               }
        }else{ //si es directa o randomica
            //dump($uDestinatario);die;
            $uDestinatario = $em->getRepository('SieAppWebBundle:Usuario')->find($uDestinatario);
            //dump($uDestinatario);die;
            $tramiteDetalle->setUsuarioDestinatario($uDestinatario);
        }
        $em->persist($tramiteDetalle);
        $em->flush();
        if ($flujoproceso->getTareaSigId() == null)
        {
            $tramite->setFechaFin(new \DateTime(date('Y-m-d')));
        }
        $tramite->setTramite($tramiteDetalle->getId());
        //$em->persist($tramite);
        $em->flush();
        //dump((new \DateTime())->format('Y'));die;
        //guardar datos del propios del tramite
        $mensaje = 'El trámite se guardo correctamente';
        return $mensaje;
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
            'detalle' => $detalle,'idtramite'=>$idtramite,
        ));
        
    }

    /**
     * muestra formulario para la rerivacion del tramite a otro usuario si es que la tarea  cuanta con mas de un usuario
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
        //$usuarios = $em->getRepository('SieAppWebBundle:WfUsuarioFlujoProceso')->findBy('flujoProceso'=>1);
        $usuarios = $em->getRepository('SieAppWebBundle:WfUsuarioFlujoProceso')->findBy(array('flujoProceso'=>$tarea,'lugarTipoId'=>$lugarTipoUsuario));
        //$usuarios = $em->getRepository('SieAppWebBundle:WfUsuarioFlujoProceso')->findBy(array('flujoProceso'=>48,'lugarTipoId'=>1));
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
                return $ft->createQueryBuilder('ft')->where('ft.id > 4')->orderBy('ft.flujo','ASC');},'property'=>'flujo','empty_value' => 'Seleccione trámite'))
            ->add('tramite','text',array('label'=>'Nro. de Trámite','required'=>true,'attr' => array('placeholder'=>'Nro. de trámite')))
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
       
        //dump($id);die;
        $data = $this->listarF($form['proceso'],$form['tramite']);
        //dump($data);die;
        //dump($data);die;
        //if (($form['proceso'] == 5 && !$data['nombre']) || ($form['proceso'] == 14 && !$data['nombre_ie']) || ($form['proceso'] == 6 && !$data['estudiante']) || ($form['proceso'] == 7 && !$data['estudiante'])) 
        //if (($form['proceso'] == 5 || $form['proceso'] == 6 || $form['proceso'] == 7 || $form['proceso'] == 14 )  && !$data['nombre']) 
        if (($form['proceso'] == 5 || $form['proceso'] == 6 || $form['proceso'] == 7)  && !$data['nombre']) 
        {
            //dump($data['nombre']);die;
            $mensaje = 'Número de tramite es incorrecto';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);
        }
        return $this->render($pathSystem.':WfTramite:flujo.html.twig',$data);
        
    }
        
    /**
     * funcion que lista el flujo de un tramite para el diagrama
     */
    public function listarF($flujotipo,$tramite)
    {
        $em = $this->getDoctrine()->getManager();
        /**
         * TRAMITE RUE
         */
        if($flujotipo == 5 || $flujotipo == 6 || $flujotipo == 7) 
        {
            $query = $em->getConnection()->prepare('select p.id, p.flujo,d.institucioneducativa, p.proceso_tipo, p.orden, p.es_evaluacion,p.variable_evaluacion, p.condicion, p.nombre,d.valor_evaluacion, p.condicion_tarea_siguiente, p.plazo, p.tarea_ant_id, p.tarea_sig_id, p.rol_tipo_id,d.id as td_id,d.tramite_id, d.flujo_proceso_id,d.fecha_recepcion,d.fecha_envio,d.usuario_remitente,d.usuario_destinatario,d.obs,d.tramite_estado,d.fecha_envio-d.fecha_recepcion as duracion
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
          t1.id,t1.tramite_id, t1.flujo_proceso_id,te.tramite_estado,t1.fecha_recepcion,t1.fecha_envio,pr.nombre as usuario_remitente,pd.nombre as usuario_destinatario,i.institucioneducativa,t1.valor_evaluacion,t1.obs
        FROM 
          tramite_detalle t1 join tramite t on t1.tramite_id=t.id
          join tramite_estado te on t1.tramite_estado_id=te.id
          left join usuario ur on t1.usuario_remitente_id=ur.id
          left join persona pr on ur.persona_id=pr.id
          left join usuario ud on t1.usuario_destinatario_id=ud.id
          left join persona pd on ud.persona_id=pd.id
          left join institucioneducativa i on t.institucioneducativa_id=i.id
        where t1.tramite_id='. $tramite .' order by t1.id)d
        ON p.id=d.flujo_proceso_id order by p.orden,d.fecha_envio');
            
        }
        /**
         * TRAMITE RITT NUEVOS
         */
        if($flujotipo == 14)
        {
            //dump($nombre_instituto);die;
            $query = $em->getConnection()->prepare('select p.id, p.flujo, p.proceso_tipo, p.orden, p.es_evaluacion,p.variable_evaluacion, p.condicion, p.nombre,d.valor_evaluacion, p.condicion_tarea_siguiente, p.plazo, p.tarea_ant_id, p.tarea_sig_id, p.rol_tipo_id,d.id as td_id,d.tramite_id, d.flujo_proceso_id,d.fecha_registro,d.usuario_remitente_id,d.usuario_destinatario_id
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
          t1.id,t1.tramite_id, t1.flujo_proceso_id,t1.fecha_registro,t1.usuario_remitente_id,t1.usuario_destinatario_id,t1.valor_evaluacion
        FROM 
          tramite_detalle t1 join tramite t on t1.tramite_id=t.id
        where t1.tramite_id='. $tramite .' order by t1.id)d
        ON p.id=d.flujo_proceso_id order by p.orden,d.fecha_registro');
        
        }
        /**
         * TRAMITE PARA ESTUDIANTES
         */
        if($flujotipo == 66 || $flujotipo == 27 )
        {
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
            where t1.tramite_id=". $tramite .")d ON p.id=d.flujo_proceso_id order by p.orden,d.fecha_registro
            ");
        }
        $query->execute();
        $arrData = $query->fetchAll();
        //dump($arrData);die;
        $data['flujo']=$arrData;
        $data['flujoDetalle']=$this->detalle($flujotipo,$tramite);
        $data['flujotipo'] = $flujotipo;
        if($flujotipo == 5 || $flujotipo == 6 || $flujotipo == 7)
        {
            if($arrData[0]['institucioneducativa']){
                $data['nombre']=$arrData[0]['institucioneducativa'];
            }else{
                $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                    ->select('wfd')
                    ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                    ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = td.flujoProceso')
                    ->where('td.tramite='.$tramite)
                    ->andWhere("fp.orden=1")
                    ->andWhere("wfd.esValido=true")
                    ->getQuery()
                    ->getResult();

            $datos = json_decode($wfdatos[0]->getDatos(),true);
            //dump($datos);die;
            $data['nombre'] = $datos['institucionEducativa'];
            }
        }elseif($flujotipo == 66 || $flujotipo == 27 ){
            $data['nombre'] = $arrData[0]['estudiante'];

        }elseif($flujotipo == 14){
            $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
                ->select('wf')
                ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 't.id = wf.tramite')
                ->where('t.id =' . $tramite)
                ->getQuery()
                ->getResult();
            //dump($wfSolicitudTramite);die;
            $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
            //dump($datos);die;
            $nombre_instituto = $datos[10]['nom_instituto'];
            $data['nombre'] = $nombre_instituto;
        }
        //dump($data);die;
        return $data;
    }

    /**
     * lista el detalle de un tramite
     */
    public function detalle($flujotipo,$tramite)
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare('select p.id, p.flujo,d.institucioneducativa, p.proceso_tipo, p.orden, p.es_evaluacion,p.variable_evaluacion,d.valor_evaluacion, p.plazo, p.tarea_ant_id, p.tarea_sig_id, p.rol_tipo_id,d.id as td_id,d.tramite_id, d.flujo_proceso_id,d.fecha_recepcion,d.fecha_envio,d.usuario_remitente,d.usuario_destinatario,d.obs,d.tramite_estado,d.tramite_estado_id,d.fecha_envio-d.fecha_recepcion as duracion,case when p.plazo is not null then d.fecha_recepcion + p.plazo else null end as fecha_vencimiento
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
          t1.id,t1.tramite_id, t1.flujo_proceso_id,te.tramite_estado,te.id as tramite_estado_id,t1.fecha_recepcion,t1.fecha_envio,pr.nombre as usuario_remitente,pd.nombre as usuario_destinatario,i.institucioneducativa,t1.valor_evaluacion,t1.obs
	    FROM 
          tramite_detalle t1 join tramite t on t1.tramite_id=t.id
          join tramite_estado te on t1.tramite_estado_id=te.id
          left join usuario ur on t1.usuario_remitente_id=ur.id
          left join persona pr on ur.persona_id=pr.id
          left join usuario ud on t1.usuario_destinatario_id=ud.id
          left join persona pd on ud.persona_id=pd.id
          left join institucioneducativa i on t.institucioneducativa_id=i.id
        where t1.tramite_id='. $tramite .' order by t1.id)d
        ON p.id=d.flujo_proceso_id order by d.id,p.id');

        $query->execute();
        $arrData = $query->fetchAll();
        //dump($arrData);die;
        return $arrData;
        
    }
}