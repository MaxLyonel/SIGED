<?php

namespace Sie\ProcesosBundle\Controller;

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
use Sie\AppWebBundle\Entity\WfFlujoInstitucioneducativaTipo;

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
     * principal
     */
    public function indexAction(Request $request)
    {
        
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $pathSystem = $this->session->get('pathSystem');
        $tipo = $request->get('tipo');
        
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        if($tipo == 2){
            $data = $this->listaRecibidos($rol,$usuario);
        }elseif($tipo == 3){
            $data = $this->listaEnviados($rol,$usuario);
        }else{
            $data = $this->listaNuevos($pathSystem);
        }
        
        return $this->render('SieProcesosBundle:WfTramite:index.html.twig', $data);
    }
   
    public function listaAction(Request $request)
    {
        
        $this->session = $request->getSession();
        //dump($request);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $roluserlugarid = $this->session->get('roluserlugarid');
        $pathSystem = $this->session->get('pathSystem');
        $tipo = $request->get('tipo');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        switch ($tipo) {
            case 1:
                $data = $this->listaNuevos($pathSystem);
                break;
            case 2:
                $data = $this->listaRecibidos($rol,$usuario);
                break;
            case 3:
                $data = $this->listaEnviados($rol,$usuario);
                break;
            case 4:
                $data = $this->listaConcluidos($rol,$roluserlugarid);
                break;
            case 5:
                $data = $this->listaReactivadosBth($roluserlugarid);
                break;
            case 6:
                $data = $this->listaPendientes($rol,$roluserlugarid);
                break;
            default:
                $data = $this->listaNuevos($pathSystem);
                break;
        }
                
        return $this->render('SieProcesosBundle:WfTramite:contenido.html.twig', $data);
    }

    /**
     * Redireccion al formulario de inicio de tramite segun el flujo
     */
    public function nuevoAction(Request $request)
    {
        //dump($id);die;
        $this->session = $request->getSession();
        //dump($this->session);die;
        $idUsuario = $this->session->get('userId');
        $idlugarusuario = $this->session->get('roluserlugarid');
        $rol = $this->session->get('roluser');
        $id = $request->get('id');
        //validation if the user is logged
        if (!isset($idUsuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findBy(array('flujoTipo'=>$id,'orden'=>1));

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
                return $this->redirectToRoute($flujoproceso[0]->getRutaFormulario(),array('id'=>$id,'tipo'=>'idflujo'));
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
                if ($aTuicion[0]['get_ue_tuicion']) {
                    return $this->redirectToRoute($flujoproceso[0]->getRutaFormulario(),array('id'=>$id,'tipo'=>'idflujo'));  
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

        if($tramiteDetalle->getTramiteEstado()->getId() == 15 or $tramiteDetalle->getTramiteEstado()->getId() == 4){ //enviado o devuelto
            $idtramite = $id;
            if($tramiteDetalle->getFlujoProceso()->getEsEvaluacion() == true){
                $t = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$tramiteDetalle->getFlujoProceso()->getId(),'condicion'=>$tramiteDetalle->getValorEvaluacion()));
                $tarea = $t[0]->getCondicionTareaSiguiente();
            }else{
                $tarea = $tramiteDetalle->getFlujoProceso()->getTareaSigId();
            }
            $mensaje = $this->get('wftramite')->guardarTramiteRecibido($usuario,$tarea,$idtramite);
            if($mensaje['dato'] == true){
                $request->getSession()
                    ->getFlashBag()
                    ->add('recibido', $mensaje['msg']);
            }else{
                $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje['msg']);
            }
        }
        
        return $this->redirectToRoute('wf_tramite_index',array('tipo'=>2));
    }

    /**
     * Redireccion del tramite a su formularios correspondiente
     */
    public function recibidosEnviarAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $id = $request->get('id');
        //dump($id);die;
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
            //dump($flujoproceso->getRutaFormulario());die;
            if($flujoproceso->getRutaFormulario()){
                return $this->redirectToRoute($flujoproceso->getRutaFormulario(),array('id' => $tramite->getId(),'tipo'=>'idtramite'));
            }
        }else{
            $request->getSession()
                    ->getFlashBag()
                    ->add('error', "No tiene tuición para este tramite");
                    return $this->redirectToRoute('wf_tramite_index',array('tipo'=>2));
        }  
    }

    /**
     * Impresion de formularios como comprobantes
     */
    public function reporteFormularioAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $pathSystem = $this->session->get('pathSystem');
        $idtramite =  $request->get('idtramite');
        $id_td =  $request->get('id_td');
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
                    return $this->redirectToRoute('wf_tramite_index',array('tipo'=>3));
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
        return $this->render('SieProcesosBundle:WfTramite:detalle.html.twig', array(
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
        $usuarios = $em->getRepository('SieAppWebBundle:WfUsuarioFlujoProceso')->findBy(array('flujoProceso'=>$tarea,'lugarTipoId'=>$lugarTipoUsuario,'esactivo'=>true));
        //dump($usuarios);die;
        $usuario = array();
        foreach($usuarios as $u){
            $usuario[$u->getUsuario()->getid()] = $u->getUsuario()->getPersona()->getNombre()." ".$u->getUsuario()->getPersona()->getPaterno()." ".$u->getUsuario()->getPersona()->getMaterno();
        }

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('wf_tramite_recibido_derivar_guardar'))
            ->add('usuario','choice',array('label'=>'Usuario:','required'=>true,'choices'=>$usuario,'empty_value' => 'Seleccione usuario','attr' => array('class' => 'form-control')))
            ->add('idtramite','hidden',array('data'=>$idtramite,'required'=>false))
            ->add('idtd','hidden',array('data'=>$tramiteDetalle->getId(),'required'=>false))
            ->add('guardar','submit',array('label'=>'Derivar'))
            ->getForm();
        
        return $this->render('SieProcesosBundle:WfTramite:derivar.html.twig', array(
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
        return $this->redirectToRoute('wf_tramite_index');
        
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
        return $this->render('SieProcesosBundle:WfTramite:flujoSeguimiento.html.twig', array(
            'form' => $flujoSeguimientoForm->createView(),
        ));
        
    }

    public function createFlujoSeguimientoForm()
    {
        $form = $this->createFormBuilder()
            //->setAction($this->generateUrl('flujoproceso_guardar'))
            ->add('proceso','entity',array('label'=>'Trámite','required'=>true,'attr' => array('class' => 'form-control'),'class'=>'SieAppWebBundle:FlujoTipo','query_builder'=>function(EntityRepository $ft){
                return $ft->createQueryBuilder('ft')->where('ft.id > 5')->orderBy('ft.flujo','ASC');},'property'=>'flujo','empty_value' => 'Seleccione trámite'))
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
        }
        return $this->render('SieProcesosBundle:WfTramite:flujo.html.twig',$data);
        
    }

     /**
     * Listado de los tipo de flujos para iniciar un tramite
     */
    public function listaNuevos($pathSystem){
        
        $rol = $this->session->get('roluser');
        $em = $this->getDoctrine()->getManager();
        $iet = 0;
        switch ($pathSystem){
            case 'SieRegularBundle':
            case 'SieHerramientaBundle':
                $iet = 1;
                break;
            case 'SieHerramientaAlternativaBundle':
                $iet = 2;
                break;
            case 'SiePermanenteBundle':
                $iet = 5;
                break;
            case 'SieEspecialBundle':
                $iet = 4;
                break;
            case 'SieDgesttlaBundle':
                $iet = 1;
                break;
            case 'SiePnpBundle':
                $iet = 10;
                break;
            case 'SieRieBundle':
                $iet = 9;
                break;
        }
        $flujotipo = $em->getRepository('SieAppWebBundle:FlujoTipo')->createQueryBuilder('ft')
            ->select('ft')
            ->innerJoin('SieAppWebBundle:WfFlujoInstitucioneducativaTipo','wf','with','wf.flujoTipo=ft.id')
            ->innerJoin('SieAppWebBundle:FlujoProceso','fp','with','fp.flujoTipo=ft.id')
            ->where('wf.institucioneducativaTipo =' . $iet)
            ->andWhere("ft.obs like '%ACTIVO%'")
            ->andWhere("fp.orden = 1")
            ->andWhere("fp.rolTipo = ".$rol)
            ->getQuery()
            ->getResult();

        $data['entities'] = $flujotipo;
        $data['titulo'] = "Nuevo trámite";
        $data['tipo'] = 1;
        return $data;
    }

    /**
     * Listado de los tramites recibidos
     */
   
    public function listaRecibidos($rol,$usuario){
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->getConnection()->prepare("select distinct t.id,case when (ie.id is not null) then 'SIE:'||ie.id when (ie.id is null and ft.id=6) then 'SIE:' when ei.id is not null then 'RUDE: '|| e.codigo_rude when mi.id is not null then 'CI: '||pm.carnet when ai.id is not null then 'CI: '||pa.carnet end as codigo_tabla ,case when ie.id is not null then 'Institucion Educativa: '||ie.institucioneducativa when (ie.id is null and ft.id=6) then 'Institucion Educativa: ' when ei.id is not null then 'Estudiante: '||e.nombre||' '||e.paterno||' '||e.materno when mi.id is not null then 'Maestro: '||pm.nombre||' '||pm.paterno||' '||pm.materno when ai.id is not null then 'Apoderado: '||pa.nombre||' '||pa.paterno||' '||pa.materno end as nombre_tabla,ft.flujo,ft.id as idflujo,case when te.id=3 then pt.proceso_tipo when (te.id=15 or te.id=4)  and (fp.es_evaluacion is false) then ptsig.proceso_tipo when (te.id=15 or te.id=4) and (fp.es_evaluacion is true) then ptc.proceso_tipo  end as proceso_tipo,pt.proceso_tipo as tarea_actual,tt.tramite_tipo,te.tramite_estado,case when te.id = 3 then td.fecha_recepcion else td.fecha_envio end as fecha_estado,te.id as id_estado,td.obs,fp.plazo,case when te.id = 3 then td.fecha_recepcion + fp.plazo else null end as fecha_vencimiento,p.nombre||' '||p.paterno||' '||p.materno as nombre,fp.ruta_formulario
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
        order by ft.flujo,te.tramite_estado,fecha_estado,t.id,proceso_tipo,tt.tramite_tipo,id_estado,td.obs,nombre");
        $query->execute();
        $data['entities'] = $query->fetchAll();
        $data['titulo'] = "Listado de trámites recibidos";
        $data['tipo'] = 2;

        return $data;
    }
    /**
     * Listado de trámites envidos
     */
    
    public function listaEnviados($rol,$usuario){
        
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->getConnection()->prepare("select t.id,td.id as id_td,case when (ie.id is not null) then 'SIE:'||ie.id when (ie.id is null and ft.id=6) then 'SIE:' when ei.id is not null then 'RUDE: '|| e.codigo_rude when mi.id is not null then 'CI: '||pm.carnet when ai.id is not null then 'CI: '||pa.carnet end as codigo_tabla ,case when (ie.id is not null) then 'Institucion Educativa: '||ie.institucioneducativa when (ie.id is null and ft.id=6) then 'Institucion Educativa:' when ei.id is not null then 'Estudiante: '||e.nombre||' '||e.paterno||' '||e.materno when mi.id is not null then 'Maestro: '||pm.nombre||' '||pm.paterno||' '||pm.materno when ai.id is not null then 'Apoderado: '||pa.nombre||' '||pa.paterno||' '||pa.materno end as nombre_tabla,fp.ruta_reporte,ft.flujo,tt.tramite_tipo,pt.proceso_tipo,te.tramite_estado,td.fecha_envio,td.fecha_recepcion,td.obs,fp.plazo,case when fp.plazo is not null then td.fecha_recepcion + fp.plazo else null end as fecha_vencimiento,p.nombre||' '||p.paterno||' '||p.materno as nombre
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
            and td.usuario_remitente_id=". $usuario ." order by fecha_envio DESC");
        $query->execute();
        $data['entities'] = $query->fetchAll();
        $data['titulo'] = "Listado de trámites enviados";
        $data['tipo'] = 3;

        return $data;
    }
    
    /**
     * Listado de trámites concluidos
     */
    public function listaConcluidos($rol,$roluserlugarid)
    {
        $em = $this->getDoctrine()->getManager();
        $lugarNivelid = $em->getRepository('SieAppWebBundle:LugarTipo')->find($roluserlugarid)->getLugarNivel()->getId();
        //dump($roluserlugarid,$lugarNivelid);die;
        
        /* $query = $em->getConnection()->prepare("select t.id,ft.id as idflujo,ft.flujo,tt.id as tramite_tipo_id,tt.tramite_tipo,t.fecha_fin,t.fecha_registro,t.fecha_fin-t.fecha_registro as duracion,case when (ie.id is not null) then 'SIE:'||ie.id when (ie.id is null and ft.id=6) then 'SIE:' when ei.id is not null then 'RUDE: '|| e.codigo_rude when mi.id is not null then 'CI: '||p.carnet when ai.id is not null then 'CI: '||pa.carnet end as codigo_tabla,case when ie.id is not null then 'Institucion Educativa: '||ie.institucioneducativa when ei.id is not null then 'Estudiante: '||e.nombre||' '||e.paterno||' '||e.materno when mi.id is not null then 'Maestro: '||p.nombre||' '||p.paterno||' '||p.materno when ai.id is not null then 'Apoderado: '||pa.nombre||' '||pa.paterno||' '||pa.materno end as nombre,'CONCLUIDO' as estado
        from tramite t
        join tramite_tipo tt on t.tramite_tipo=tt.id and t.flujo_tipo_id > 5 and t.fecha_fin is not null
        join flujo_tipo ft on t.flujo_tipo_id = ft.id
        left join institucioneducativa ie on t.institucioneducativa_id=ie.id
        left join estudiante_inscripcion ei on t.estudiante_inscripcion_id=ei.id
        left join estudiante e on ei.estudiante_id=e.id
        left join maestro_inscripcion mi on t.maestro_inscripcion_id=mi.id
        left join persona p on mi.persona_id=p.id
        left join apoderado_inscripcion ai on t.apoderado_inscripcion_id=ai.id
        left join persona pa on ai.persona_id=pa.id
        order by ft.flujo,t.id,t.fecha_fin"); */
        if($rol == 9){

        }else{
            
        }
        $id_usuario = $this->session->get('userId');
        $query = $em->getConnection()->prepare("select t.id,ft.id as idflujo,ft.flujo,tt.id as tramite_tipo_id,tt.tramite_tipo,t.fecha_fin,t.fecha_registro,t.fecha_fin-t.fecha_registro as duracion,'SIE:'||ie.id as codigo_tabla,
        'Institucion Educativa: '||ie.institucioneducativa as nombre,
        'CONCLUIDO' as estado
        from tramite t
        join tramite_tipo tt on t.tramite_tipo=tt.id and t.flujo_tipo_id >5 and t.fecha_fin is not null
        join flujo_tipo ft on t.flujo_tipo_id = ft.id
        join institucioneducativa ie on t.institucioneducativa_id=ie.id
        join jurisdiccion_geografica le on (ie.le_juridicciongeografica_id=le.id)
        join lugar_tipo lt on lt.id=le.lugar_tipo_id_distrito
        where case when ". $rol ."=9 then ie.id= ". $this->session->get('ie_id') ." when ". $lugarNivelid ." in (1,6,8) then lt.lugar_tipo_id=". $roluserlugarid ." when ". $lugarNivelid ." = 7 then lt.id=". $roluserlugarid ." when ". $lugarNivelid ."=0 then 1=1 end 
        
        union all
        select t.id,ft.id as idflujo,ft.flujo,tt.id as tramite_tipo_id,tt.tramite_tipo,t.fecha_fin,t.fecha_registro,t.fecha_fin-t.fecha_registro as duracion,'RUDE: '|| e.codigo_rude as codigo_tabla,
        'Estudiante: '||e.nombre||' '||e.paterno||' '||e.materno as nombre,
        'CONCLUIDO' as estado
        from tramite t
        join tramite_tipo tt on t.tramite_tipo=tt.id and t.flujo_tipo_id >5 and t.fecha_fin is not null
        join flujo_tipo ft on t.flujo_tipo_id = ft.id
        join estudiante_inscripcion ei on t.estudiante_inscripcion_id=ei.id
        join estudiante e on ei.estudiante_id=e.id
        join institucioneducativa_curso iec on iec.id=ei.institucioneducativa_curso_id
        join institucioneducativa ie on iec.institucioneducativa_id=ie.id
        join jurisdiccion_geografica le on (ie.le_juridicciongeografica_id=le.id)
        join lugar_tipo lt on lt.id=le.lugar_tipo_id_distrito
        where case when ". $rol ."=9 then ie.id= ". $this->session->get('ie_id') ." when ". $lugarNivelid ." in (1,6,8) then lt.lugar_tipo_id=". $roluserlugarid ." when ". $lugarNivelid ." = 7 then lt.id=". $roluserlugarid ." when ". $lugarNivelid ."=0 then 1=1 end 
        
        union all
        select t.id,ft.id as idflujo,ft.flujo,tt.id as tramite_tipo_id,tt.tramite_tipo,t.fecha_fin,t.fecha_registro,t.fecha_fin-t.fecha_registro as duracion,'CI: '||p.carnet as codigo_tabla,
        'Maestro: '||p.nombre||' '||p.paterno||' '||p.materno as nombre,
        'CONCLUIDO' as estado
        from tramite t
        join tramite_tipo tt on t.tramite_tipo=tt.id and t.flujo_tipo_id >5 and t.fecha_fin is not null
        join flujo_tipo ft on t.flujo_tipo_id = ft.id
        join maestro_inscripcion mi on t.maestro_inscripcion_id=mi.id
        join persona p on mi.persona_id=p.id
        join institucioneducativa ie on mi.institucioneducativa_id=ie.id
        join jurisdiccion_geografica le on (ie.le_juridicciongeografica_id=le.id)
        join lugar_tipo lt on lt.id=le.lugar_tipo_id_distrito
        where case when ". $rol ."=9 then ie.id= ". $this->session->get('ie_id') ." when ". $lugarNivelid ." in (1,6,8) then lt.lugar_tipo_id=". $roluserlugarid ." when ". $lugarNivelid ." = 7 then lt.id=". $roluserlugarid ." when ". $lugarNivelid ."=0 then 1=1 end 
        
        union all
        select t.id,ft.id as idflujo,ft.flujo,tt.id as tramite_tipo_id,tt.tramite_tipo,t.fecha_fin,t.fecha_registro,t.fecha_fin-t.fecha_registro as duracion,'CI: '||pa.carnet as codigo_tabla,
        'Apoderado: '||pa.nombre||' '||pa.paterno||' '||pa.materno as nombre,
        'CONCLUIDO' as estado
        from tramite t
        join tramite_tipo tt on t.tramite_tipo=tt.id and t.flujo_tipo_id >5 and t.fecha_fin is not null
        join flujo_tipo ft on t.flujo_tipo_id = ft.id
        join apoderado_inscripcion ai on t.apoderado_inscripcion_id=ai.id
        join persona pa on ai.persona_id=pa.id
        join estudiante_inscripcion ei on ai.estudiante_inscripcion_id=ei.id
        join institucioneducativa_curso iec on iec.id=ei.institucioneducativa_curso_id
        join institucioneducativa ie on iec.institucioneducativa_id=ie.id
        join jurisdiccion_geografica le on (ie.le_juridicciongeografica_id=le.id)
        join lugar_tipo lt on lt.id=le.lugar_tipo_id_distrito
        where case when ". $rol ."=9 then ie.id= ". $this->session->get('ie_id') ." when ". $lugarNivelid ." in (1,6,8) then lt.lugar_tipo_id=". $roluserlugarid ." when ". $lugarNivelid ." = 7 then lt.id=". $roluserlugarid ." when ". $lugarNivelid ."=0 then 1=1 end 

        union all
        select t.id,ft.id as idflujo,ft.flujo,tt.id as tramite_tipo_id,tt.tramite_tipo,t.fecha_fin,t.fecha_registro,t.fecha_fin-t.fecha_registro as duracion,'SIE:' as codigo_tabla,
        'Institucion Educativa: '||(wf.datos::json->>'institucionEducativa')::VARCHAR as nombre,
        'CONCLUIDO' as estado
        from tramite t
        join tramite_tipo tt on t.tramite_tipo=tt.id and t.flujo_tipo_id >5 and t.fecha_fin is not null
        join flujo_tipo ft on t.flujo_tipo_id = ft.id
        join tramite_detalle td on td.tramite_id=t.id
        join flujo_proceso fp on fp.id=td.flujo_proceso_id and fp.orden=1
        join wf_solicitud_tramite wf on wf.tramite_detalle_id=td.id and wf.es_valido is true
        join lugar_tipo lt on lt.id=wf.lugar_tipo_distrito_id
        where case when ". $rol ."=9 then t.institucioneducativa_id NOTNULL when ". $lugarNivelid ." in (1,6,8) then lt.lugar_tipo_id=". $roluserlugarid ." when ". $lugarNivelid ." = 7 then lt.id=". $roluserlugarid ." when ". $lugarNivelid ."=0 then 1=1 end 
        and t.estudiante_inscripcion_id ISNULL and t.maestro_inscripcion_id ISNULL and t.apoderado_inscripcion_id ISNULL and t.institucioneducativa_id ISNULL
        ;");
        $query->execute();
        $data['entities'] = $query->fetchAll();;
        $data['titulo'] = "Listado de trámites concluidos";
        $data['tipo'] = 4;
        return $data;
    }

    /**
     * Listado de trámites reactivados BTH
     */
    public function listaReactivadosBTh($roluserlugarid)
    {
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->getConnection()->prepare("select t.id,ft.id as idflujo,ft.flujo,tt.id as tramite_tipo_id,tt.tramite_tipo,ie.id as codigo_sie,ie.institucioneducativa,case when r.fecha_fin is null then'PENDIENTE' else 'CONCLUIDO' end as estado,r.fecha_inicio,r.fecha_fin as fecha_conclusion,r.obs,p.nombre || ' '|| p.paterno || ' '||p.materno as usuario
            from tramite t
            join tramite_tipo tt on t.tramite_tipo=tt.id
            join flujo_tipo ft on t.flujo_tipo_id = ft.id
            join institucioneducativa ie on t.institucioneducativa_id=ie.id
            join jurisdiccion_geografica jg on ie.le_juridicciongeografica_id=jg.id
            join lugar_tipo lt on lt.id = jg.lugar_tipo_id_distrito
            join rehabilitacion_bth r on r.tramite_id=t.id
            join usuario u on r.usuario_registro_id=u.id
            join persona p on p.id=u.persona_id
            where ft.id=6 and t.tramite_tipo=31");
        $query->execute();
        $data['entities'] = $query->fetchAll();;
        $data['titulo'] = "Listado de trámites reactivados para BTH";
        $data['tipo'] = 5;
        return $data;
    }

    /**
     * Listado de trámites pendientes
     */
    public function listaPendientes($rol,$roluserlugarid)
    {
        $em = $this->getDoctrine()->getManager();
        $lugarNivelid = $em->getRepository('SieAppWebBundle:LugarTipo')->find($roluserlugarid)->getLugarNivel()->getId();
        //dump($roluserlugarid,$lugarNivelid);die;
        
        $id_usuario = $this->session->get('userId');
        $query = $em->getConnection()->prepare("select t.id,ft.id as idflujo,ft.flujo,tt.id as tramite_tipo_id,tt.tramite_tipo,pt.proceso_tipo,td.obs,td.tramite_estado_id,te.tramite_estado,pr.nombre||' '||pr.paterno||' '||pr.materno as usuario_remitente,td.fecha_recepcion,pd.nombre||' '||pd.paterno||' '||pd.materno as usuario_destinatario,td.fecha_envio,'SIE:'||ie.id as codigo_tabla,
        'Institucion Educativa: '||ie.institucioneducativa as nombre,'PENDIENTE' as estado
        from tramite t
        join tramite_detalle td on t.tramite::int=td.id
        join tramite_estado te on te.id=td.tramite_estado_id
        join flujo_proceso fp on fp.id=td.flujo_proceso_id
        join proceso_tipo pt on pt.id=fp.proceso_id
        join usuario ur on ur.id=td.usuario_remitente_id
        join persona pr on ur.persona_id=pr.id
        join usuario ud on ud.id=td.usuario_destinatario_id
        join persona pd on ud.persona_id=pd.id
        join tramite_tipo tt on t.tramite_tipo=tt.id and t.flujo_tipo_id >5 and t.fecha_fin isnull
        join flujo_tipo ft on t.flujo_tipo_id = ft.id
        join institucioneducativa ie on t.institucioneducativa_id=ie.id
        join jurisdiccion_geografica le on (ie.le_juridicciongeografica_id=le.id)
        join lugar_tipo lt on lt.id=le.lugar_tipo_id_distrito
        and ". $rol ." in(select rol_tipo_id from flujo_proceso where flujo_tipo_id=ft.id)
        where case when ". $rol ."=9 then ie.id= ". $this->session->get('ie_id') ." when ". $lugarNivelid ." in (1,6,8) then lt.lugar_tipo_id=". $roluserlugarid ." when ". $lugarNivelid ." = 7 then lt.id=". $roluserlugarid ." when ". $lugarNivelid ."=0 then 1=1 end 
        
        union all
        select t.id,ft.id as idflujo,ft.flujo,tt.id as tramite_tipo_id,tt.tramite_tipo,pt.proceso_tipo,td.obs,td.tramite_estado_id,te.tramite_estado,pr.nombre||' '||pr.paterno||' '||pr.materno as usuario_remitente,td.fecha_recepcion,pd.nombre||' '||pd.paterno||' '||pd.materno as usuario_destinatario,td.fecha_envio,'RUDE: '|| e.codigo_rude as codigo_tabla,
        'Estudiante: '||e.nombre||' '||e.paterno||' '||e.materno as nombre,'PENDIENTE' as estado
        from tramite t
        join tramite_detalle td on t.tramite::int=td.id
        join tramite_estado te on te.id=td.tramite_estado_id
        join flujo_proceso fp on fp.id=td.flujo_proceso_id
        join proceso_tipo pt on pt.id=fp.proceso_id
        join usuario ur on ur.id=td.usuario_remitente_id
        join persona pr on ur.persona_id=pr.id
        join usuario ud on ud.id=td.usuario_destinatario_id
        join persona pd on ud.persona_id=pd.id        
        join tramite_tipo tt on t.tramite_tipo=tt.id and t.flujo_tipo_id >5 and t.fecha_fin isnull
        join flujo_tipo ft on t.flujo_tipo_id = ft.id
        join estudiante_inscripcion ei on t.estudiante_inscripcion_id=ei.id
        join estudiante e on ei.estudiante_id=e.id
        join institucioneducativa_curso iec on iec.id=ei.institucioneducativa_curso_id
        join institucioneducativa ie on iec.institucioneducativa_id=ie.id
        join jurisdiccion_geografica le on (ie.le_juridicciongeografica_id=le.id)
        join lugar_tipo lt on lt.id=le.lugar_tipo_id_distrito
        and ". $rol ." in(select rol_tipo_id from flujo_proceso where flujo_tipo_id=ft.id)
        where case when ". $rol ."=9 then ie.id= ". $this->session->get('ie_id') ." when ". $lugarNivelid ." in (1,6,8) then lt.lugar_tipo_id=". $roluserlugarid ." when ". $lugarNivelid ." = 7 then lt.id=". $roluserlugarid ." when ". $lugarNivelid ."=0 then 1=1 end 
        
        union all
        select t.id,ft.id as idflujo,ft.flujo,tt.id as tramite_tipo_id,tt.tramite_tipo,pt.proceso_tipo,td.obs,td.tramite_estado_id,te.tramite_estado,pr.nombre||' '||pr.paterno||' '||pr.materno as usuario_remitente,td.fecha_recepcion,pd.nombre||' '||pd.paterno||' '||pd.materno as usuario_destinatario,td.fecha_envio,'CI: '||p.carnet as codigo_tabla,
        'Maestro: '||p.nombre||' '||p.paterno||' '||p.materno as nombre,'PENDIENTE' as estado
        from tramite t
        join tramite_detalle td on t.tramite::int=td.id
        join tramite_estado te on te.id=td.tramite_estado_id
        join flujo_proceso fp on fp.id=td.flujo_proceso_id
        join proceso_tipo pt on pt.id=fp.proceso_id
        join usuario ur on ur.id=td.usuario_remitente_id
        join persona pr on ur.persona_id=pr.id
        join usuario ud on ud.id=td.usuario_destinatario_id
        join persona pd on ud.persona_id=pd.id
        join tramite_tipo tt on t.tramite_tipo=tt.id and t.flujo_tipo_id >5 and t.fecha_fin isnull
        join flujo_tipo ft on t.flujo_tipo_id = ft.id
        join maestro_inscripcion mi on t.maestro_inscripcion_id=mi.id
        join persona p on mi.persona_id=p.id
        join institucioneducativa ie on mi.institucioneducativa_id=ie.id
        join jurisdiccion_geografica le on (ie.le_juridicciongeografica_id=le.id)
        join lugar_tipo lt on lt.id=le.lugar_tipo_id_distrito
        and ". $rol ." in(select rol_tipo_id from flujo_proceso where flujo_tipo_id=ft.id)
        where case when ". $rol ."=9 then ie.id= ". $this->session->get('ie_id') ." when ". $lugarNivelid ." in (1,6,8) then lt.lugar_tipo_id=". $roluserlugarid ." when ". $lugarNivelid ." = 7 then lt.id=". $roluserlugarid ." when ". $lugarNivelid ."=0 then 1=1 end 
        
        union all
        select t.id,ft.id as idflujo,ft.flujo,tt.id as tramite_tipo_id,tt.tramite_tipo,pt.proceso_tipo,td.obs,td.tramite_estado_id,te.tramite_estado,pr.nombre||' '||pr.paterno||' '||pr.materno as usuario_remitente,td.fecha_recepcion,pd.nombre||' '||pd.paterno||' '||pd.materno as usuario_destinatario,td.fecha_envio,'CI: '||pa.carnet as codigo_tabla,
        'Apoderado: '||pa.nombre||' '||pa.paterno||' '||pa.materno as nombre,'PENDIENTE' as estado
        from tramite t
        join tramite_detalle td on t.tramite::int=td.id
        join tramite_estado te on te.id=td.tramite_estado_id
        join flujo_proceso fp on fp.id=td.flujo_proceso_id
        join proceso_tipo pt on pt.id=fp.proceso_id
        join usuario ur on ur.id=td.usuario_remitente_id
        join persona pr on ur.persona_id=pr.id
        join usuario ud on ud.id=td.usuario_destinatario_id
        join persona pd on ud.persona_id=pd.id
        join tramite_tipo tt on t.tramite_tipo=tt.id and t.flujo_tipo_id >5 and t.fecha_fin isnull
        join flujo_tipo ft on t.flujo_tipo_id = ft.id
        join apoderado_inscripcion ai on t.apoderado_inscripcion_id=ai.id
        join persona pa on ai.persona_id=pa.id
        join estudiante_inscripcion ei on ai.estudiante_inscripcion_id=ei.id
        join institucioneducativa_curso iec on iec.id=ei.institucioneducativa_curso_id
        join institucioneducativa ie on iec.institucioneducativa_id=ie.id
        join jurisdiccion_geografica le on (ie.le_juridicciongeografica_id=le.id)
        join lugar_tipo lt on lt.id=le.lugar_tipo_id_distrito
        and ". $rol ." in(select rol_tipo_id from flujo_proceso where flujo_tipo_id=ft.id)
        where case when ". $rol ."=9 then ie.id= ". $this->session->get('ie_id') ." when ". $lugarNivelid ." in (1,6,8) then lt.lugar_tipo_id=". $roluserlugarid ." when ". $lugarNivelid ." = 7 then lt.id=". $roluserlugarid ." when ". $lugarNivelid ."=0 then 1=1 end 

        union all
        select t.id,ft.id as idflujo,ft.flujo,tt.id as tramite_tipo_id,tt.tramite_tipo,pt.proceso_tipo,td.obs,td.tramite_estado_id,te.tramite_estado,pr.nombre||' '||pr.paterno||' '||pr.materno as usuario_remitente,td.fecha_recepcion,pd.nombre||' '||pd.paterno||' '||pd.materno as usuario_destinatario,td.fecha_envio,'SIE:' as codigo_tabla,
        'Institucion Educativa: '||(wf.datos::json->>'institucionEducativa')::VARCHAR as nombre,'PENDIENTE' as estado
        from tramite t
        join tramite_detalle td on t.tramite::int=td.id
        join tramite_estado te on te.id=td.tramite_estado_id
        join flujo_proceso fp on fp.id=td.flujo_proceso_id
        join proceso_tipo pt on pt.id=fp.proceso_id
        join usuario ur on ur.id=td.usuario_remitente_id
        join persona pr on ur.persona_id=pr.id
        join usuario ud on ud.id=td.usuario_destinatario_id
        join persona pd on ud.persona_id=pd.id
        join tramite_tipo tt on t.tramite_tipo=tt.id and t.flujo_tipo_id >5 and t.fecha_fin is not null
        join flujo_tipo ft on t.flujo_tipo_id = ft.id
        join tramite_detalle td1 on td1.tramite_id=t.id
        join flujo_proceso fp1 on fp1.id=td1.flujo_proceso_id and fp1.orden=1
        join wf_solicitud_tramite wf on wf.tramite_detalle_id=td1.id and wf.es_valido is true
        join lugar_tipo lt on lt.id=wf.lugar_tipo_distrito_id
        and ". $rol ." in(select rol_tipo_id from flujo_proceso where flujo_tipo_id=ft.id)
        where case when ". $rol ."=9 then t.institucioneducativa_id NOTNULL when ". $lugarNivelid ." in (1,6,8) then lt.lugar_tipo_id=". $roluserlugarid ." when ". $lugarNivelid ." = 7 then lt.id=". $roluserlugarid ." when ". $lugarNivelid ."=0 then 1=1 end 
        and t.estudiante_inscripcion_id ISNULL and t.maestro_inscripcion_id ISNULL and t.apoderado_inscripcion_id ISNULL and t.institucioneducativa_id ISNULL
        ;");
        $query->execute();
        $data['entities'] = $query->fetchAll();;
        $data['titulo'] = "Listado de trámites pendientes";
        $data['tipo'] = 6;
        return $data;
    }

    /**
     * funcion que lista el flujo de un tramite para el diagrama
     */
    public function listarF($flujotipo,$idtramite)
    {
        $em = $this->getDoctrine()->getManager();

        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->findOneBy(array('id'=>$idtramite,'flujoTipo'=>$flujotipo));
        $data['tramite_tipo'] = $tramite->getTramiteTipo()->getTramiteTipo();
        if($tramite->getEstudianteInscripcion()){
            /**
            * TRAMITE PARA ESTUDIANTES
            */
            $data['tipo'] = "ESTUDIANTE: ";
            $data['nombre'] = $tramite->getEstudianteInscripcion()->getEstudiante()->getNombre() . ' ' . $tramite->getEstudianteInscripcion()->getEstudiante()->getPaterno() . ' ' . $tramite->getEstudianteInscripcion()->getEstudiante()->getMaterno();
            $data['codsie'] = $tramite->getEstudianteInscripcion()->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();

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
            $data['codsie'] = $tramite->getMaestroInscripcion()->getInstitucioneducativa()->getId();

        }elseif($tramite->getApoderadoInscripcion()){
            $data['tipo'] = "APODERADO: ";
            $data['nombre'] = $tramite->getApoderadoInscripcion()->getPersona()->getNombre() . ' ' . $tramite->getApoderadoInscripcion()->getPersona()->getPaterno() . ' ' . $tramite->getApoderadoInscripcion()->getPersona()->getMaterno();
            $data['codsie'] = $tramite->getApoderadoInscripcion()->getEstudianteInscripcion()->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        
        }else{
            /**
            * TRAMITE PARA UNIDADES EDUCATIVAS
            */
            $data['tipo'] = "UNIDAD EDUCATIVA: ";
            $data['codsie'] = $tramite->getInstitucioneducativa()?$tramite->getInstitucioneducativa()->getId():'';
            if($tramite->getInstitucioneducativa()){
                $data['nombre'] = $tramite->getInstitucioneducativa()->getInstitucioneducativa();
            }else{
                $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                    ->select('wfd')
                    ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                    ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = td.flujoProceso')
                    ->where('td.tramite='.$idtramite)
                    ->andWhere("fp.orden=1")
                    ->andWhere("wfd.esValido=true")
                    ->getQuery()
                    ->getResult();
    
                $datos = json_decode($wfdatos[0]->getDatos(),true);
                //dump($datos);die;
                $index = strpos('Apertura de Unidad Educativa', $wfdatos[0]->getDatos()) === false ? 'Apertura de Centro de Educacion Alternativa' : 'Apertura de Unidad Educativa';
                $data['nombre'] = $datos[$index]['institucioneducativa'];
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

        $query = $em->getConnection()->prepare("select p.id, p.flujo,d.institucioneducativa, p.proceso_tipo, p.orden, p.es_evaluacion,p.variable_evaluacion,d.valor_evaluacion, p.plazo, p.tarea_ant_id, p.tarea_sig_id, p.rol_tipo_id,d.id as td_id,d.tramite_id, d.flujo_proceso_id,d.fecha_recepcion,d.fecha_envio,d.usuario_remitente,d.usuario_destinatario,d.obs,d.tramite_estado,d.tramite_estado_id,d.fecha_envio-d.fecha_recepcion as duracion,case when p.plazo is not null then d.fecha_recepcion + p.plazo else null end as fecha_vencimiento,d.fecha_fin
        from
        (SELECT 
          fp.id, f.flujo, p.proceso_tipo, fp.orden, fp.es_evaluacion,fp.variable_evaluacion,fp.plazo, fp.tarea_ant_id, fp.tarea_sig_id, fp.rol_tipo_id
        FROM 
          flujo_tipo f join flujo_proceso fp on f.id = fp.flujo_tipo_id
          join proceso_tipo p on p.id = fp.proceso_id
        WHERE 
           f.id=". $flujotipo ." order by fp.orden)p
        LEFT JOIN
        (SELECT 
          t1.id,t1.tramite_id, t1.flujo_proceso_id,t.fecha_fin,te.tramite_estado,te.id as tramite_estado_id,t1.fecha_recepcion,t1.fecha_envio,pr.nombre||' '||pr.paterno||' '||pr.materno as usuario_remitente,pd.nombre||' '||pd.paterno||' '||pd.materno as usuario_destinatario,i.institucioneducativa,t1.valor_evaluacion,t1.obs
        FROM 
          tramite_detalle t1 join tramite t on t1.tramite_id=t.id
          join tramite_estado te on t1.tramite_estado_id=te.id
          left join usuario ur on t1.usuario_remitente_id=ur.id
          left join persona pr on ur.persona_id=pr.id
          left join usuario ud on t1.usuario_destinatario_id=ud.id
          left join persona pd on ud.persona_id=pd.id
          left join institucioneducativa i on t.institucioneducativa_id=i.id
        where t1.tramite_id=". $idtramite ." order by t1.id)d
        ON p.id=d.flujo_proceso_id order by d.id,p.id");

        $query->execute();
        $arrData = $query->fetchAll();
        //dump($arrData);die;
        $detalle['detalle']=$arrData;
        $detalle['fecha_fin']=$arrData[0]['fecha_fin'];
        return $detalle;
        
    }
}