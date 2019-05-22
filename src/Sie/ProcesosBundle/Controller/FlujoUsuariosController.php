<?php

namespace Sie\ProcesosBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;

use Sie\AppWebBundle\Entity\FlujoProceso;
use Sie\AppWebBundle\Entity\FlujoTipo;
use Sie\AppWebBundle\Entity\ProcesoTipo;
use Sie\AppWebBundle\Entity\RolTipo;
use Sie\AppWebBundle\Entity\WfUsuarioFlujoProceso;

/**
 * FlujoUsuario.
 *
 */
class FlujoUsuariosController extends Controller
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
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $flujotipo = $request->get('flujotipo');        
        $buscarForm = $this->createAsignarUsuarioForm($flujotipo); 
        
        return $this->render('SieProcesosBundle:FlujoUsuarios:nuevoUsuario.html.twig', array(
            'form1' => $buscarForm->createView(),
        ));
        
    }

    public function createAsignarUsuarioForm($flujotipo)
    {
        $em = $this->getDoctrine()->getManager();
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->createQueryBuilder('fp')
                ->select('fp.id,pt.procesoTipo')
                ->innerJoin('SieAppWebBundle:ProcesoTipo', 'pt', 'with', 'pt.id = fp.proceso')
                ->where('fp.flujoTipo='.$flujotipo)
                ->andWhere('fp.rolTipo not in (1,2,3,5,9)') //usuarios individuales como ser estudiante, maestro,director,apoderado
                ->orderBy('fp.id') //usuarios individuales como ser estudiante, maestro,director,apoderado
                ->getQuery()
                ->getResult();
        $tareasArray = array();
        
        foreach($flujoproceso as $fp){
            $tareasArray[$fp['id']] = $fp['procesoTipo'];
        }
        //dump($usuariosArray);die;

        $form = $this->createFormBuilder()
            //->setAction($this->generateUrl('flujoproceso_buscar_usuarios'))
            ->add('tareas', 'choice', array('label'=>'Tarea del proceso:','choices'=>$tareasArray,'required'=>true,'empty_value' => 'Seleccionar tarea', 'attr' => array('class' => 'form-control')))
            ->add('buscar1', 'button', array('label'=>'Buscar','attr'=>array('onclick'=>'buscarUsuarios()')))
            ->getForm();
        return $form;
    }

    public function buscarUsuariosAction(Request $request)
    {
        $flujotipo =$request->get('flujotipo');
        $tarea = $request->get('flujoproceso');
        $em = $this->getDoctrine()->getManager();
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea);
        //dump($flujoproceso);die;
        $query = $em->getConnection()->prepare("select u.id,u.username,p.nombre,p.paterno,p.materno,lt.id as lugar_tipo,lt.lugar,ln.nivel,ur.rol_tipo_id,r.rol
        from usuario u
        join usuario_rol ur on ur.usuario_id=u.id
        join rol_tipo r on ur.rol_tipo_id=r.id
        join lugar_tipo lt on ur.lugar_tipo_id=lt.id
        join lugar_nivel_tipo ln on lt.lugar_nivel_id=ln.id
        join persona p on u.persona_id=p.id
        where ur.rol_tipo_id=". $flujoproceso->getRolTipo()->getId() ." and u.esactivo is true and ur.esactivo is true and u.id not in(select usuario_id from wf_usuario_flujo_proceso where esactivo is true and flujo_proceso_id=". $tarea ." ) order by lt.lugar,p.nombre");
        $query->execute();
        $usuarios = $query->fetchAll();

        return $this->render('SieProcesosBundle:FlujoUsuarios:tablaUsuarios.html.twig', array(
            'usuarios' => $usuarios,'tarea'=>$tarea
        ));
    }

    public function guardarUsuariosAction(Request $request)
    {
        //dump($request);die;
        $usuarios =$request->get('usuario');
        $tarea =$request->get('tarea');
        $em = $this->getDoctrine()->getManager();
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea);
        foreach($usuarios as $u)
        {
            $datos=json_decode($u,true);
            $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($datos['usuario']);
            $wfusuario = $em->getRepository('SieAppWebBundle:WfUsuarioFlujoProceso')->findBy(array('flujoProceso'=>$tarea,'usuario'=>$datos['usuario'],'lugarTipoId'=>$datos['lugar_tipo']));
            if($wfusuario){
                $wfusuario[0]->setEsactivo(true);    
            }else{
                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('wf_usuario_flujo_proceso');")->execute();
                $entity = new WfUsuarioFlujoProceso();
                $entity->setUsuario($usuario);
                $entity->setFlujoProceso($flujoproceso);
                $entity->setEsactivo(true);
                $entity->setLugartipoId($datos['lugar_tipo']);
                $em->persist($entity);
            }
            $em->flush();
            //dump($wfusuario);die;
            $dato[]=$usuario->getPersona()->getNombre().' '.$usuario->getPersona()->getPaterno().' '.$usuario->getPersona()->getMaterno();
        }
        //dump($dato);die;
        $mensaje = 'Los siguientes usuarios fueron asignados correctamente para la tarea: '. $flujoproceso->getProceso()->getProcesoTipo();
        $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);

        foreach ($dato as $d){
            $mensaje = '- '. $d;
            $request->getSession()
                    ->getFlashBag()
                    ->add('exito', $mensaje);
        }
        return $this->redirect($this->generateUrl('flujousuarios_usuarios',array('flujotipo'=>$flujoproceso->getFlujoTipo()->getId())));
        
    }
    public function listaUsuariosFlujoProcesoAction(Request $request)
    {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $flujotipoForm = $this->createFlujoTipoForm(); 
        return $this->render('SieProcesosBundle:FlujoUsuarios:listaUsuarios.html.twig', array(
            'form' => $flujotipoForm->createView(),
        ));
    }

    public function createFlujoTipoForm()
    {
        $form = $this->createFormBuilder()
            ->add('flujotipo','entity',array('label'=>'Tipo de proceso:','required'=>true,'class'=>'SieAppWebBundle:FlujoTipo','query_builder'=>function(EntityRepository $ft){
                return $ft->createQueryBuilder('ft')->where('ft.id > 5')->andWhere("ft.obs like '%ACTIVO%'")->orderBy('ft.flujo','ASC');},'property'=>'flujo','empty_value' => 'Seleccione proceso'))
            ->add('buscar', 'button', array('label'=>'Buscar','attr'=>array('class'=>'btn btn-primary')))
            ->getForm();
        return $form;
    }

    public function listaUsuariosAction(Request $request)
    {
        $flujotipo =$request->get('flujotipo');
        
        $em = $this->getDoctrine()->getManager();
        $wfusuarios = $em->getRepository('SieAppWebBundle:WfUsuarioFlujoProceso')->createQueryBuilder('wfu')
                    ->select('wfu.id,u.username,p.nombre,p.paterno,p.materno,r.rol,pt.procesoTipo,lt.lugar')
                    ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = wfu.flujoProceso')
                    ->innerJoin('SieAppWebBundle:RolTipo', 'r', 'with', 'r.id = fp.rolTipo')
                    ->innerJoin('SieAppWebBundle:ProcesoTipo', 'pt', 'with', 'pt.id = fp.proceso')
                    ->innerJoin('SieAppWebBundle:LugarTipo', 'lt', 'with', 'lt.id = wfu.lugarTipoId')
                    ->innerJoin('SieAppWebBundle:Usuario', 'u', 'with', 'u.id = wfu.usuario')
                    ->innerJoin('SieAppWebBundle:Persona', 'p', 'with', 'p.id = u.persona')
                    ->where('fp.flujoTipo='.$flujotipo)
                    ->andWhere('wfu.esactivo=true')
                    ->orderBy('fp.id')
                    ->getQuery()
                    ->getResult();
        //dump($wfusuarios);die;

        return $this->render('SieProcesosBundle:FlujoUsuarios:tablaListaUsuarios.html.twig', array(
            'usuarios' => $wfusuarios
        ));
    }

    public function eliminarUsuarioAction(Request $request)
    {
        $id =$request->get('id');
        
        $em = $this->getDoctrine()->getManager();
        $wfusuario = $em->getRepository('SieAppWebBundle:WfUsuarioFlujoProceso')->find($id);
        $query = $em->getConnection()->prepare("select td.* from tramite t
            join tramite_detalle td on td.id=cast(t.tramite as int)
            join flujo_proceso fp on td.flujo_proceso_id=fp.id
            where ((fp.tarea_sig_id=". $wfusuario->getFlujoProceso()->getId() ." and fp.es_evaluacion is false and td.tramite_estado_id in(4,15)) or (fp.es_evaluacion is true and (select condicion_tarea_siguiente from wf_tarea_compuerta wf where flujo_proceso_id=fp.id and td.valor_evaluacion=wf.condicion)=". $wfusuario->getFlujoProceso()->getId() ." and td.tramite_estado_id in(4,15)) or (fp.id=". $wfusuario->getFlujoProceso()->getId() ." and td.tramite_estado_id=3)) and t.flujo_tipo_id > 5 and t.fecha_fin is null and td.usuario_destinatario_id =".$wfusuario->getUsuario()->getId());
        
        $query->execute();
        $tramite =$query->fetchAll();
        //dump($tramite);die;
        if($tramite){    //si el usuario tiene tramites pendientes asignados
            $mensaje = 'El/la usuario: '. $wfusuario->getUsuario()->getPersona()->getNombre().' '. $wfusuario->getUsuario()->getPersona()->getPaterno() . ' ' . $wfusuario->getUsuario()->getPersona()->getMaterno() .' no puede ser eliminado, pues tiene tramites asignado pendientes.';
            $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje);
            $mensaje = 'Primero debe DERIVAR sus tramites a otro usuario.';
            $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje);
        }else{
            $wfu = $em->getRepository('SieAppWebBundle:WfUsuarioFlujoProceso')->createQueryBuilder('wfu')
                    ->select('wfu')
                    ->where('wfu.flujoProceso='.$wfusuario->getFlujoProceso()->getId())
                    ->andWhere('wfu.esactivo=true')
                    ->andWhere('not wfu.id ='.$id)
                    ->getQuery()
                    ->getResult();
            //dump($wfu);die;
            if(!$wfu){   //si no existen mas usuarios para la tarea
                $mensaje = 'El/la usuario: '. $wfusuario->getUsuario()->getPersona()->getNombre().' '. $wfusuario->getUsuario()->getPersona()->getPaterno() . ' ' . $wfusuario->getUsuario()->getPersona()->getMaterno() .' no puede ser eliminado/a, antes debe asignar un nuevo usuario para la tarea: '.$wfusuario->getFlujoProceso()->getProceso()->getProcesoTipo();
                $request->getSession()
                        ->getFlashBag()
                        ->add('error', $mensaje);
            }else{    //si el usuario no tiene tramites y hay mas usuarios para la tarea, desactivarlo
                $wfusuario->setEsactivo(false); 
                $em->flush();
                $mensaje = 'El/la usuario: '. $wfusuario->getUsuario()->getPersona()->getNombre().' '. $wfusuario->getUsuario()->getPersona()->getPaterno() . ' ' . $wfusuario->getUsuario()->getPersona()->getMaterno() .' fue eliminado con éxito';
                $request->getSession()
                        ->getFlashBag()
                        ->add('exito', $mensaje);
            }
        }
        $wfusuarios = $em->getRepository('SieAppWebBundle:WfUsuarioFlujoProceso')->createQueryBuilder('wfu')
                    ->select('wfu.id,u.username,p.nombre,p.paterno,p.materno,r.rol,pt.procesoTipo,lt.lugar')
                    ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = wfu.flujoProceso')
                    ->innerJoin('SieAppWebBundle:RolTipo', 'r', 'with', 'r.id = fp.rolTipo')
                    ->innerJoin('SieAppWebBundle:ProcesoTipo', 'pt', 'with', 'pt.id = fp.proceso')
                    ->innerJoin('SieAppWebBundle:LugarTipo', 'lt', 'with', 'lt.id = wfu.lugarTipoId')
                    ->innerJoin('SieAppWebBundle:Usuario', 'u', 'with', 'u.id = wfu.usuario')
                    ->innerJoin('SieAppWebBundle:Persona', 'p', 'with', 'p.id = u.persona')
                    ->where('fp.flujoTipo='.$wfusuario->getFlujoProceso()->getFlujoTipo()->getId())
                    ->andWhere('wfu.esactivo=true')
                    ->orderBy('fp.id')
                    ->getQuery()
                    ->getResult();
        //dump($wfusuarios);die;
        
        return $this->render('SieProcesosBundle:FlujoUsuarios:tablaListaUsuarios.html.twig', array(
            'usuarios' => $wfusuarios
        ));
    }

    public function derivarTramitesAction(Request $request)
    {
        $id =$request->get('id');
        //dump($id);die;
        $em = $this->getDoctrine()->getManager();
        $wfusuario = $em->getRepository('SieAppWebBundle:WfUsuarioFlujoProceso')->find($id);
        $query = $em->getConnection()->prepare("select t.id,td.id as td_id,ft.flujo,pt.proceso_tipo from tramite t
                join tramite_detalle td on td.id=cast(t.tramite as int)
                join flujo_proceso fp on fp.id=td.flujo_proceso_id
                join flujo_tipo ft on ft.id=t.flujo_tipo_id
                join proceso_tipo pt on pt.id=fp.proceso_id
                where ((fp.tarea_sig_id=". $wfusuario->getFlujoProceso()->getId() ." and fp.es_evaluacion is false and td.tramite_estado_id in(4,15)) or (fp.es_evaluacion is true and (select condicion_tarea_siguiente from wf_tarea_compuerta wf where flujo_proceso_id=fp.id and td.valor_evaluacion=wf.condicion)=". $wfusuario->getFlujoProceso()->getId() ." and td.tramite_estado_id in(4,15)) or (fp.id=". $wfusuario->getFlujoProceso()->getId() ." and td.tramite_estado_id=3)) and t.flujo_tipo_id > 5 and t.fecha_fin is null and td.usuario_destinatario_id =".$wfusuario->getUsuario()->getId());
        $query->execute();
        $tramite =$query->fetchAll();
        //dump($tramite);die;

        if(!$tramite){
            $mensaje = 'El/la usuario: '. $wfusuario->getUsuario()->getPersona()->getNombre().' '. $wfusuario->getUsuario()->getPersona()->getPaterno() . ' ' . $wfusuario->getUsuario()->getPersona()->getMaterno() .' no tiene tramites pendientes para derivar.';
            $response = new JsonResponse();
            return $response->setData(array('msg' => $mensaje));
        }else{
            $query = $em->getConnection()->prepare("select wfu.flujo_proceso_id,u.id,u.username,p.nombre,p.paterno,p.materno,lt.id as lugar_tipo,lt.lugar,ln.nivel
                from usuario u
                join wf_usuario_flujo_proceso wfu on u.id=wfu.usuario_id
                join lugar_tipo lt on wfu.lugar_tipo_id=lt.id
                join lugar_nivel_tipo ln on lt.lugar_nivel_id=ln.id
                join persona p on u.persona_id=p.id
                where wfu.esactivo is true and not wfu.id=". $id ." and wfu.flujo_proceso_id=". $wfusuario->getFlujoProceso()->getId() ." and lt.id=". $wfusuario->getLugarTipoId() ."  order by lt.lugar,p.nombre");
            $query->execute();
            $wfu = $query->fetchAll();
            //dump($wfu);die;
            if(!$wfu){
                $lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->find($wfusuario->getLugarTipoId())->getLugar();
                $mensaje = 'No se puede derivar los tramites, pues no existe otro usuario asignado a la tarea: <b>'.$wfusuario->getFlujoProceso()->getProceso()->getProcesoTipo().'</b> de la jurisdicción: <b>'. $lugar .'</b>.</br>Primero <b>ASIGNE UN NUEVO</b> usuario para esta tarea.';
                $response = new JsonResponse();
                return $response->setData(array('msg' => $mensaje));
            }else{
                return $this->render('SieProcesosBundle:FlujoUsuarios:tablaDerivarTramites.html.twig', array(
                    'usuarios' => $wfu,'tarea'=>$wfusuario->getFlujoProceso()->getProceso()->getProcesoTipo(),'tramites'=>$tramite,'idusuario'=>$wfusuario->getUsuario()->getId(),'usuario'=>$wfusuario->getUsuario()->getPersona()->getNombre().' '. $wfusuario->getUsuario()->getPersona()->getPaterno() . ' ' . $wfusuario->getUsuario()->getPersona()->getMaterno()
                ));

            }
        }
    }

    public function derivarTramitesGuardarAction(Request $request)
    {
        $idNuevo =$request->get('id');
        $tarea =$request->get('tarea');
        $usuarioAnterior =$request->get('usuario');
        $tramites = json_decode($request->get('tramites'),true);

        $em = $this->getDoctrine()->getManager();
        $usuarioNuevo = $em->getRepository('SieAppWebBundle:Usuario')->find($idNuevo);
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea);
        $mensaje = 'Los siguientes trámites fueron derivados a: '.$usuarioNuevo->getPersona()->getNombre().' '. $usuarioNuevo->getPersona()->getPaterno().' '. $usuarioNuevo->getPersona()->getMaterno().', para la tarea '. $flujoproceso->getProceso()->getProcesoTipo() .'.</br>';
        
        foreach($tramites as $t){
            $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find($t['td_id']);
            $tramiteDetalle->setUsuarioDestinatario($usuarioNuevo);
            $tramiteDetalle->setFechaModificacion(new \DateTime(date('Y-m-d')));
            $em->flush();
            $mensaje =$mensaje.'-'.$tramiteDetalle->getTramite()->getId().'</br>';
        }
        $response = new JsonResponse();
        return $response->setData(array('msg' => $mensaje));
    }

}