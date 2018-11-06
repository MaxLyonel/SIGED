<?php

namespace Sie\HerramientaBundle\Controller;

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
    
    public function indexAction(Request $request)
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

        $flujotipo = $em->getRepository('SieAppWebBundle:FlujoTipo')->createQueryBuilder('ft')
                ->select('ft')
                ->where('ft.id > 4')
                ->getQuery()
                ->getResult();
        //dump($flujotipo);die;
        $data['entities'] = $flujotipo;
        $data['titulo'] = "Listado de trámites existentes";
        return $this->render('SieHerramientaBundle:WfTramite:index.html.twig', $data);
    }

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
        switch ($id) {
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
        }
        
    }

    public function recibidosAction(Request $request)
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
        
        $query = $em->getConnection()->prepare("select t.id,ft.flujo,tt.tramite_tipo,td.fecha_envio,td.obs,p.nombre
        from tramite t
        join tramite_detalle td on cast(t.tramite as int)=td.id
        join tramite_tipo tt on t.tramite_tipo=tt.id
        join flujo_tipo ft on t.flujo_tipo_id = ft.id
        join usuario u on td.usuario_remitente_id=u.id
        join persona p on p.id=u.persona_id
        where ft.id>4 and t.fecha_fin is null and td.tramite_estado_id=15 and td.usuario_destinatario_id=".$usuario." order by ft.flujo,td.fecha_envio");
        $query->execute();
        /*$tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->createQueryBuilder('td')
            ->select('td')
            ->innerJoin('SieAppWebBundle:Tramite', 't', 'with', 'cast(t.tramite as int) = td.id')
            ->innerJoin('SieAppWebBundle:FlujoTipo', 'ft', 'with', 'ft.id = t.flujoTipo')
            ->innerJoin('SieAppWebBundle:TramiteEstado', 'te', 'with', 'te.id = td.tramiteEstado')
            ->innerJoin('SieAppWebBundle:Usuario', 'u', 'with', 'u.id = td.usuarioDestinatario')
            ->where('ft.id > 4')
            ->andWhere('t.fechaFin is null')
            ->andWhere('te.id = 15')
            ->andWhere('u.id = '.$usuario)
            ->getQuery()
            ->getResult();*/

        //dump($flujotipo);die;
        $data['entities'] = $query->fetchAll();;
        $data['titulo'] = "Listado de trámites recibidos";
        return $this->render('SieHerramientaBundle:WfTramite:recibidos.html.twig', $data);
    }

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
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDEtalle')->find((int)$tramite->getTramite());
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
        if($tramite->getFlujoTipo()->getId() == 6){
            switch ($tramiteDetalle->getFlujoProceso()->getId()) {
                case 39:
                    return $this->redirectToRoute('tramite_rue_informe_distrito_nuevo', array('idtramite' => $tramite->getId(),'idtarea'=>$tramiteDetalle->getFlujoProceso()->getId()));
                    //return $this->redirectToRoute('wf_tramite_index');
                    break;
                case 40:
                    return $this->redirectToRoute('wf_tramite_index');
                    break;
                case 41:
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
        }

        return $this->redirectToRoute('wf_tramite_recibido');

        //return $this->render('SieHerramientaBundle:WfTramite:recibidos.html.twig');
    }
    
    public function guardarTramiteNuevo($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$tipotramite,$varevaluacion,$idtramite,$datos,$lugarTipo_id)
    {

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
         * insert datos propios de la solicitud
         */
        if ($datos){
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('wf_solicitud_tramite');")->execute();   
            $wfSolicitudTramite = new WfSolicitudTramite();
            $wfSolicitudTramite->setTramite($tramite);
            $wfSolicitudTramite->setDatos($datos);
            $wfSolicitudTramite->setEsValido(true);
            $wfSolicitudTramite->setFechaRegistro(new \DateTime(date('Y-m-d H:i:s')));
            $wfSolicitudTramite->setLugarTipoId($lugarTipo_id);
            $em->persist($wfSolicitudTramite);
            $em->flush();
        }
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
        if ($flujoproceso->getEsEvaluacion() == true) 
        {
            $tramiteDetalle->setValorEvaluacion($varevaluacion);
            $wfcondiciontarea = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$flujoproceso->getId(),'condicion'=>$varevaluacion));
            $tarea_sig_id = $wfcondiciontarea[0]->condicionTareaSiguiente();
            $uDestinatario = $this->obtieneUsuarioDestinatario($tarea_sig_id,$id_tabla,$tabla);
        }else{
            $tarea_sig_id = $flujoproceso->getTareaSigId();
            $uDestinatario = $this->obtieneUsuarioDestinatario($tarea_sig_id,$id_tabla,$tabla);
        }
        $tramiteDetalle->setUsuarioDestinatario($uDestinatario);
        $em->persist($tramiteDetalle);
        $em->flush();
        $tramite->setTramite($tramiteDetalle->getId());
        $em->flush();
        $mensaje = 'El trámite se guardo correctamente';
        return $mensaje;
    }

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
        /**
         * Guardamos tarea anterior en tramite detalle  
         */
        $td_anterior = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        $tramiteDetalle->setTramiteDetalle($td_anterior);
        $em->persist($tramiteDetalle);
        $em->flush();
        $tramite->setTramite($tramiteDetalle->getId());
        $em->flush();
        $mensaje = 'El trámite se recibió correctamente';
        return $mensaje;
    }
    
    public function guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$tipotramite,$varevaluacion,$idtramite,$datos,$lugarTipo_id,$idtramiteestado)
    {

        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('tramite_detalle');")->execute();
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea);
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($usuario);
        $tramiteestado = $em->getRepository('SieAppWebBundle:TramiteEstado')->find($tramiteestado);
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        /**
         * Modificacion de datos propios de la solicitud
         */
        if ($datos){
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
        }
        /**
         * guarda tramite enviado
         */
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        $tramiteDetalle->setObs($observacion);
        $tramiteDetalle->setFechaEnvio(new \DateTime(date('Y-m-d')));
        $tramiteDetalle->setTramiteEstado($tramiteestado);

        if ($flujoproceso->getEsEvaluacion() == true) 
        {
            $tramiteDetalle->setValorEvaluacion($varevaluacion);
            $wfcondiciontarea = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$flujoproceso->getId(),'condicion'=>$varevaluacion));
            if ($wfcondiciontarea[0]->condicionTareaSiguiente() != null){
                $tarea_sig_id = $wfcondiciontarea[0]->condicionTareaSiguiente();
                $uDestinatario = $this->obtieneUsuarioDestinatario($tarea_sig_id,$id_tabla,$tabla);
                $tramiteDetalle->setUsuarioDestinatario($uDestinatario);
            }else{
                $tramite->setFechaFin(new \DateTime(date('Y-m-d')));    
            }
        }else{
            if ($flujoproceso->getTareaSigId() != null){
                $tarea_sig_id = $flujoproceso->getTareaSigId();
                $uDestinatario = $this->obtieneUsuarioDestinatario($tarea_sig_id.$id_tabla.$tabla);
                $tramiteDetalle->setUsuarioDestinatario($uDestinatario);
            }
        }
        if ($flujoproceso->getTareaSigId() == null)
        {
            $tramite->setFechaFin(new \DateTime(date('Y-m-d')));
        }
        $em->flush();
        $mensaje = 'El trámite se guardo correctamente';
        return $mensaje;
    }

    public function obtieneUsuarioDestinatario($tarea_sig_id,$id_tabla,$tabla)
    {
        $em = $this->getDoctrine()->getManager();
        
        $flujoprocesoSiguiente = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea_sig_id);
        $nivel = $flujoprocesoSiguiente->getRolTipo()->getLugarNivelTipo();
        switch ($tabla) {
            case 'institucioneducativa':
                if ($id_tabla){
                    $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id_tabla);
                    switch ($nivel->getId()) {
                        case 7:   // Distrito
                            //dump($institucioneducativa);die;
                            $lugar_tipo_distrito = $institucioneducativa->getleJuridicciongeografica()->getLugarTipoIdDistrito();
                            //dump($lugar_tipo_distrito);die;
                            $query = $em->getConnection()->prepare("select * from wf_usuario_flujo_proceso where flujo_proceso_id=". $flujoprocesoSiguiente->getId()." and lugar_tipo_id=".$lugar_tipo_distrito);
                            $query->execute();
                            $uDestinatario = $query->fetchAll();
                            //$uDestinatario = $em->getRepository('SieAppWebBundle:UsuarioFlujoProceso')->findBy(array('flujoProceso'=>$flujoprocesoSiguiente->getId(),'lugarTipoId'=>$lugar_tipo_distrito));
                            break;
                        case 6:   // Departamento
                            $lugar_tipo_departamento = $institucioneducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugraTipo()->getId();
                            $query = $em->getConnection()->prepare("select * from wf_usuario_flujo_proceso where flujo_proceso_id=". $flujoprocesoSiguiente->getId()." and lugar_tipo_id=".$lugar_tipo_departamento);
                            $query->execute();
                            $uDestinatario = $query->fetchAll();
                            //$uDestinatario = $em->getRepository('SieAppWebBundle:UsuarioFlujoProceso')->findBy(array('flujoProceso'=>$flujoprocesoSiguiente->getId(),'lugarTipoId'=>$lugar_tipo_departamento));
                            break;
                        case 0:
                            if($flujoprocesoSiguiente->getRolTipo()->getId() == 9){
                                $query = $em->getConnection()->prepare("select u.* from maestro_inscripcion m
                                join usuario u on m.persona_id=u.persona_id
                                where m.institucioneducativa_id=".$institucioneducativa->getId()." and m.gestion_tipo_id=".(new \DateTime())->format('Y')." and (m.cargo_tipo_id=1 or m.cargo_tipo_id=12) and m.es_vigente_administrativo is true and u.esactivo is true");
                                $query->execute();
                                $uDestinatario = $query->fetchAll();
                                //$uDestinatario = $em->getRepository('SieAppWebBundle:UsuarioFlujoProceso')->findBy(array('flujoProceso'=>$flujoprocesoSiguiente->getId(),'lugarTipoId'=>1));
                            }elseif($flujoprocesoSiguiente->getRolTipo->getId() == 8){
                                $uDestinatario = $em->getRepository('SieAppWebBundle:UsuarioFlujoProceso')->findBy(array('flujoProceso'=>$flujoprocesoSiguiente->getId(),'lugarTipoId'=>1));
                            }
                            break;
                    }
                }
                break;
            case 'estudiante_inscripcion':
                break;
            case 'apoderado_inscripcion':
                break;
            case 'maestro_inscripcion':
                break;
        }
        //dump($uDestinatario);die;
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($uDestinatario[0]['usuario_id']);
        //return $uDestinatario[0]->getUsuario();
        return $usuario;
    }
    
    public function tramiteTareaRitt($tarea_ant,$tarea_actual,$flujotipo,$usuario,$rol)
    {
        $em = $this->getDoctrine()->getManager();
        //dump($lugarTipo);die;
        $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$usuario,'rolTipo'=>$rol));            
        $idlugarusuario = $usuariorol[0]->getLugarTipo()->getCodigo();
        //dump($usuariorol);die;
        //dump((int)$idlugarusuario);die;
         /**tareas devuelta por condicion**/
        $wftareac = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->createQueryBuilder('wf')
                ->select('fp.id,wf.condicion')
                ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = wf.flujoProceso')
                ->where('wf.condicionTareaSiguiente =' . $tarea_actual)
                ->getQuery()
                ->getResult();
        /**tarea devuelta**/
        $fp = $em->getRepository('SieAppWebBundle:FlujoProceso')->createQueryBuilder('fp')
                ->select('fp.id')
                ->where('fp.tareaSigId =' . $tarea_actual)
                ->getQuery()
                ->getResult();
        /**tarea anterior**/
        $tarea = 'td.flujo_proceso_id='. $tarea_ant;
        if($wftareac and $fp){
            $tarea = "(" . $tarea . " or (td.flujo_proceso_id=". $wftareac[0]['id'] ." and td.valor_evaluacion='". $wftareac[0]['condicion'] ."') or td.flujo_proceso_id=". $fp[0]['id']. ")";
        }elseif ($wftareac){
            $tarea = "(" . $tarea . " or (td.flujo_proceso_id=". $wftareac[0]['id'] ." and td.valor_evaluacion='". $wftareac[0]['condicion'] ."'))";
        }elseif ($fp){
            $tarea = "(" . $tarea . " or td.flujo_proceso_id=". $fp[0]['id']. ")";
        }
        //dump($wftareac);die;
        /**si la tarea anterior tiene evaluacion **/
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
    }
    
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
    
    public function verFlujoRueAction(Request $request)
    {
        //dump($request);die;

        $proceso = $request->get('proceso');
        $tramite = $request->get('tramite');
        //dump($id);die;
        $data = $this->listarF($proceso,$tramite);
        //dump($data);die;
        return $this->render('SieProcesosBundle:TramiteRue:flujo.html.twig',$data);
    }

    public function listarF($flujotipo,$tramite)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('select p.id, p.flujo,d.institucioneducativa, p.proceso_tipo, p.orden, p.es_evaluacion,p.variable_evaluacion, p.condicion, p.nombre,d.valor_evaluacion, p.condicion_tarea_siguiente, p.plazo, p.tarea_ant_id, p.tarea_sig_id, p.rol_tipo_id,d.id as td_id,d.tramite_id, d.flujo_proceso_id,d.fecha_registro,d.usuario_remitente_id,d.usuario_destinatario_id
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
          t1.id,t1.tramite_id, t1.flujo_proceso_id,t1.fecha_registro,t1.usuario_remitente_id,t1.usuario_destinatario_id,i.institucioneducativa,t1.valor_evaluacion
        FROM 
          tramite_detalle t1 join tramite t on t1.tramite_id=t.id
          join institucioneducativa i on t.institucioneducativa_id=i.id
        where t1.tramite_id='. $tramite .' order by t1.id)d
        ON p.id=d.flujo_proceso_id ');
        $query->execute();
        $arrData = $query->fetchAll();
        $data['flujo']=$arrData;
        $data['flujotipo'] = $flujotipo;
        $data['nombre_ie']=$arrData[0]['institucioneducativa'];
        return $data;
    }

}