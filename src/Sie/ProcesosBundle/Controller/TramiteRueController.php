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


/**
 * FlujoTipo controller.
 *
 */
class TramiteRueController extends Controller
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
      
    }

    public function createRecepcionForm()
    {
    
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('recepcion_distrito_guardar'))
            ->add('flujoproceso', 'hidden', array('data' =>22 ))
            ->add('flujotipo', 'hidden', array('data' =>5 ))
            ->add('estadoinstitucioneducativa','entity',array('label'=>'Estado','read_only'=>true,'required'=>true,'class'=>'SieAppWebBundle:EstadoinstitucionTipo','query_builder'=>function(EntityRepository $ei){
                return $ei->createQueryBuilder('ei')->orderBy('ei.id','ASC');},'property'=>'estadoinstitucion','empty_value' => 'Seleccione Estado'))
            ->add('tramitetipo','entity',array('label'=>'Tipo de trámite','required'=>true,'class'=>'SieAppWebBundle:TramiteTipo','query_builder'=>function(EntityRepository $tr){
                return $tr->createQueryBuilder('tr')->where('tr.obs = :rue')->setParameter('rue','RUE')->orderBy('tr.id','ASC');},'property'=>'tramite_tipo','empty_value' => 'Seleccione trámite'))
            ->add('institucioneducativa','text',array('label'=>'Unidad educativa','read_only'=>true))
            ->add('departamento','text',array('label'=>'Departamento','read_only'=>true))
            ->add('distrito','text',array('label'=>'Distrito','read_only'=>true))
            ->add('observacion','text',array('label'=>'Observación','required'=>false))
            ->add('dependencia','entity',array('label'=>'Dependencia','read_only'=>true,'required'=>true,'class'=>'SieAppWebBundle:DependenciaTipo','query_builder'=>function(EntityRepository $d){
                return $d->createQueryBuilder('d')->where('d.id not in (:id)')->setParameter('id',array(0,4))->orderBy('d.id','ASC');},'property'=>'dependencia','empty_value' => 'Seleccione dependencia'))
            ->add('tipoeducacion','entity',array('label'=>'Tipo de educación','read_only'=>true,'required'=>true,'class'=>'SieAppWebBundle:InstitucioneducativaTipo','query_builder'=>function(EntityRepository $e){
                return $e->createQueryBuilder('e')->where('e.id <> :id')->setParameter('id',0)->orderBy('e.id','ASC');},'property'=>'descripcion','empty_value' => 'Seleccione tipo de educación'))
            ->add('ubicaciongeografica','choice',array('label'=>'Ubicacion geográfica','required'=>true,'choices'=>array('1' => 'Ciudad capital','2' => 'Area dispersa'),'empty_value' => 'Seleccione ubicación'))
            ->add('buscar','button',array('label'=>'Buscar'))
            ->add('idrue','text',array('label'=>'Código RUE'))
            ->add('legal', CheckboxType::class, array('label'=>'Legal','required' => false))
            ->add('administrativo', CheckboxType::class, array('label'=>'Administrativo','required' => true))
            ->add('pedagogico', CheckboxType::class, array('label'=>'Técnico pedagógico','required' => true))
            ->add('infraestructura', CheckboxType::class, array('label'=>'Infraestructura','required' => true))
            ->add('guardar','submit',array('label'=>'Guardar'))
            ->getForm();
        return $form;
    }

    
    public function inboxAction(Request $request)
    {
        //dump($request);die;
        $flujotipo =$request->get('flujotipo');
        $rol= $request->get('rol');
        $usuario=$request->get('usuario');
        //dump($flujotipo);die;
        $em = $this->getDoctrine()->getManager();
        //get grado
        //$tareas = array();
        $flujoArray = array();
        $query = $em->getConnection()->prepare('SELECT fp.id, p.proceso_tipo,fp.es_evaluacion,fp.variable_evaluacion, fp.tarea_ant_id, fp.tarea_sig_id,fp.orden
        FROM flujo_proceso fp join proceso_tipo p on p.id = fp.proceso_id
        WHERE fp.flujo_tipo_id='. $flujotipo .' and fp.rol_tipo_id='. $rol .' order by fp.orden');
        $query->execute();
        $tareas = $query->fetchAll();
        for($i=0;$i<count($tareas);$i++)
        {
            if($tareas[$i]['orden'] == 1)
            {
                $flujoArray[$tareas[$i]['id']] = $tareas[$i]['proceso_tipo'];    
            }else{
                $nro = $this->obtenerNro($tareas[$i]['id'],$tareas[$i]['tarea_ant_id'],$flujotipo,$usuario,$rol);
                $flujoArray[$tareas[$i]['id']] = $tareas[$i]['proceso_tipo'] .' ('. $nro . ')';
            }
        }
        //dump($tareas);die;
        $response = new JsonResponse();
        return $response->setData(array('tareas' => $flujoArray));
    }

    public function obtenerNro($idtarea,$tarea_ant,$flujotipo,$usuario,$rol)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('select lugar_tipo_id from usuario_rol where usuario_id='. $usuario .' and rol_tipo_id=' . $rol);
        $query->execute();
        $lugarTipo = $query->fetchAll();
        $query1 = $em->getConnection()->prepare('select * from flujo_proceso where id=' . $tarea_ant . ' and es_evaluacion=true');
        $query1->execute();
        $evaluacion = $query1->fetchAll();
        if($evaluacion)
        {
            $query2 = $em->getConnection()->prepare('SELECT count(t1.flujo_proceso_id) as nro
            FROM tramite_detalle t1 join usuario_rol ur on ur.usuario_id=t1.usuario_remitente_id
            where t1.id in (select cast(tramite as int) as tramite_detalle from tramite where flujo_tipo_id='. $flujotipo .' and fecha_fin is null) and ur.rol_tipo_id='. $rol .' and ur.lugar_tipo_id='. $lugarTipo[0]['lugar_tipo_id'] .' and t1.flujo_proceso_id='. $tarea_ant .' and t1.valor_evaluacion = (select condicion from wf_tarea_compuerta where flujo_proceso_id='. $tarea_ant .' and condicion_tarea_siguiente='. $idtarea .') group by t1.flujo_proceso_id');
            $query2->execute();
            $nro = $query2->fetchAll();
        }else{
            $query2 = $em->getConnection()->prepare('SELECT count(t1.flujo_proceso_id) as nro
            FROM tramite_detalle t1 join usuario_rol ur on ur.usuario_id=t1.usuario_remitente_id
            where t1.id in (select cast(tramite as int) as tramite_detalle from tramite where flujo_tipo_id='. $flujotipo .' and fecha_fin is null) and ur.lugar_tipo_id='. $lugarTipo[0]['lugar_tipo_id'] .' and t1.flujo_proceso_id='. $tarea_ant .' and ur.rol_tipo_id='. $rol .' group by t1.flujo_proceso_id');
            $query2->execute();
            $nro = $query2->fetchAll();
        }
        if($nro)
        {
            $dato=$nro[0]['nro'];
        }else{
            $dato=0;
        }
        return $dato;
    }

    public function recepcionDistritoAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $data = $this->tramiteTarea(22,22,5,$usuario,$rol);
        return $this->render('SieProcesosBundle:TramiteRue:recepcionDistrito.html.twig', $data);
    }

    public function recepcionDistritoNuevoAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $recepcionForm = $this->createRecepcionForm(); 
        //return $this->render('SieProcesosBundle:FlujoProceso:index1.html.twig');
        return $this->render('SieProcesosBundle:TramiteRue:recepcionDistritoNuevo.html.twig', array(
            'form' => $recepcionForm->createView(),
        ));
    }

    public function recepcionDistritoGuardarAction(Request $request)
    {
        $form = $request->get('form');
        //dump($form);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $tabla = 'institucioneducativa';
        $id_tabla = $form['idrue'];
        $observacion = $form['observacion'];
        $tipotramite = $form['tramitetipo'];
        $uDestinatario = 13834044;
        $mensaje = $this->guardarTramiteDetalle($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$tipotramite,'','');
        $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        
      //$recepcionForm = $this->createRecepcionForm(); 
        //return $this->render('SieProcesosBundle:FlujoProceso:index1.html.twig');
        return $this->redirect($this->generateUrl('tramite_rue_recepcion_distrito'));
    }
    public function informeDistritoAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $data = $this->tramiteTarea(22,23,5,$usuario,$rol);
        return $this->render('SieProcesosBundle:TramiteRue:informeDistrito.html.twig', $data);
    }

    public function createInformeDistritoForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$idtramite)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_rue_informe_distrito_guardar'))
            ->add('flujoproceso', 'hidden', array('data' =>23 ))
            ->add('flujotipo', 'hidden', array('data' =>5 ))
            ->add('tramite', 'hidden', array('data' =>$idtramite ))
            ->add('institucioneducativa','text',array('label'=>'Unidad educativa','data'=>$ie,'read_only'=>true))
            ->add('idrue','text',array('label'=>'Código RUE','data'=>$idrue,'read_only'=>true))
            ->add('tipotramite','text',array('label'=>'Tramite','data'=>$tipotramite,'read_only'=>true))
            ->add('observacion','text',array('label'=>'Observación'))
            //->add('estadoinstitucioneducativa','text',array('label'=>'Estado Unidad educativa','data'=>$estadoinstitucioneducativa))
            ->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'empty_value' => '¿Tiene evaluacion?'))
            ->add('guardar','submit',array('label'=>'Guardar'))
            ->getForm();
        return $form;
    }
    
    public function createRecepcionDepartamentalForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$idtramite)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_rue_recepcion_departamental_guardar'))
            ->add('flujoproceso', 'hidden', array('data' =>24 ))
            ->add('flujotipo', 'hidden', array('data' =>5 ))
            ->add('tramite', 'hidden', array('data' =>$idtramite ))
            ->add('institucioneducativa','text',array('label'=>'Unidad educativa','data'=>$ie,'read_only'=>true))
            ->add('idrue','text',array('label'=>'Código RUE','data'=>$idrue,'read_only'=>true))
            ->add('tipotramite','text',array('label'=>'Tramite','data'=>$tipotramite,'read_only'=>true))
            ->add('observacion','text',array('label'=>'Observación'))
            //->add('estadoinstitucioneducativa','text',array('label'=>'Estado Unidad educativa','data'=>$estadoinstitucioneducativa))
            ->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'empty_value' => '¿Tiene evaluacion?'))
            ->add('guardar','submit',array('label'=>'Guardar'))
            ->getForm();
        return $form;
    }

    public function createJuridicaDepartamentalForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$idtramite)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_rue_juridica_departamental_guardar'))
            ->add('flujoproceso', 'hidden', array('data' =>25 ))
            ->add('flujotipo', 'hidden', array('data' =>5 ))
            ->add('tramite', 'hidden', array('data' =>$idtramite ))
            ->add('institucioneducativa','text',array('label'=>'Unidad educativa','data'=>$ie,'read_only'=>true))
            ->add('idrue','text',array('label'=>'Código RUE','data'=>$idrue,'read_only'=>true))
            ->add('tipotramite','text',array('label'=>'Tramite','data'=>$tipotramite,'read_only'=>true))
            ->add('observacion','text',array('label'=>'Observación'))
            //->add('estadoinstitucioneducativa','text',array('label'=>'Estado Unidad educativa','data'=>$estadoinstitucioneducativa))
            ->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'empty_value' => '¿Tiene evaluacion?'))
            ->add('guardar','submit',array('label'=>'Guardar'))
            ->getForm();
        return $form;
    }
    
    public function informeDistritoNuevoAction(Request $request,$id)
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
        $ie=$tramite->getInstitucioneducativa()->getInstitucioneducativa();
        $idrue = $tramite->getInstitucioneducativa()->getId();
        $tipotramite = $tramite->getTramiteTipo()->getTramiteTipo();
        $estadoinstitucioneducativa = $tramite->getInstitucioneducativa()->getEstadoinstitucionTipo()->getEstadoinstitucion();

        $informeForm = $this->createInformeDistritoForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$id); 
        //return $this->render('SieProcesosBundle:FlujoProceso:index1.html.twig');
        return $this->render('SieProcesosBundle:TramiteRue:informeDistritoNuevo.html.twig', array(
            'form' => $informeForm->createView(),
        ));

    }
    public function informeDistritoGuardarAction(Request $request)
    {
        $form = $request->get('form');
        //dump($form);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $tramite = $form['tramite'];
        $tabla = 'institucioneducativa';
        $id_tabla = $form['idrue'];
        $observacion = $form['observacion'];
        $uDestinatario = 13834044;
        $varevaluacion = $form['varevaluacion'];
        $mensaje = $this->guardarTramiteDetalle($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,'',$varevaluacion,$tramite);
        $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        return $this->redirect($this->generateUrl('tramite_rue_informe_distrito'));
    }

    public function recepcionDepartamentalAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $data = $this->tramiteTarea(23,24,5,$usuario,$rol);
        //
        //dump($data);die;
        return $this->render('SieProcesosBundle:TramiteRue:recepcionDepartamental.html.twig', $data);
    }
    public function recepcionDepartamentalNuevoAction(Request $request,$id)
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
        $ie=$tramite->getInstitucioneducativa()->getInstitucioneducativa();
        $idrue = $tramite->getInstitucioneducativa()->getId();
        $tipotramite = $tramite->getTramiteTipo()->getTramiteTipo();
        $estadoinstitucioneducativa = $tramite->getInstitucioneducativa()->getEstadoinstitucionTipo()->getEstadoinstitucion();

        $recepcionDepartamentalForm = $this->createRecepcionDepartamentalForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$id); 
        //return $this->render('SieProcesosBundle:FlujoProceso:index1.html.twig');
        return $this->render('SieProcesosBundle:TramiteRue:recepcionDepartamentalNuevo.html.twig', array(
            'form' => $recepcionDepartamentalForm->createView(),
        ));

    }

    public function recepcionDepartamentalGuardarAction(Request $request)
    {
        $form = $request->get('form');
        //dump($form);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $tramite = $form['tramite'];
        $tabla = 'institucioneducativa';
        $id_tabla = $form['idrue'];
        $observacion = $form['observacion'];
        $uDestinatario = 13834044;
        $varevaluacion = $form['varevaluacion'];
        $mensaje = $this->guardarTramiteDetalle($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,'',$varevaluacion,$tramite);
        $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        return $this->redirect($this->generateUrl('tramite_rue_recepcion_departamental'));
    }

    public function juridicaDepartamentalAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $data = $this->tramiteTarea(24,25,5,$usuario,$rol);
        //
        //dump($data);die;
        return $this->render('SieProcesosBundle:TramiteRue:juridicaDepartamental.html.twig', $data);
    }
    public function juridicaDepartamentalNuevoAction(Request $request,$id)
    {
        $this->session = $request->getSession();
        //dump($id);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
        $ie=$tramite->getInstitucioneducativa()->getInstitucioneducativa();
        $idrue = $tramite->getInstitucioneducativa()->getId();
        $tipotramite = $tramite->getTramiteTipo()->getTramiteTipo();
        $estadoinstitucioneducativa = $tramite->getInstitucioneducativa()->getEstadoinstitucionTipo()->getEstadoinstitucion();

        $juridicaDepartamentalForm = $this->createJuridicaDepartamentalForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$id); 
        //return $this->render('SieProcesosBundle:FlujoProceso:index1.html.twig');
        return $this->render('SieProcesosBundle:TramiteRue:juridicaDepartamentalNuevo.html.twig', array(
            'form' => $juridicaDepartamentalForm->createView(),
        ));

    }

    public function juridicaDepartamentalGuardarAction(Request $request)
    {
        $form = $request->get('form');
        //dump($form);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $tramite = $form['tramite'];
        $tabla = 'institucioneducativa';
        $id_tabla = $form['idrue'];
        $observacion = $form['observacion'];
        $uDestinatario = 13834044;
        $varevaluacion = $form['varevaluacion'];
        $mensaje = $this->guardarTramiteDetalle($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,'',$varevaluacion,$tramite);
        $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        return $this->redirect($this->generateUrl('tramite_rue_juridica_departamental'));
    }

    public function notificacionDepartamentalAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $data = $this->tramiteTarea(25,26,5,$usuario,$rol);
        //
        //dump($data);die;
        return $this->render('SieProcesosBundle:TramiteRue:notificacionDepartamental.html.twig', $data);
    }
    public function notificacionDepartamentalNuevoAction(Request $request,$id)
    {
        $this->session = $request->getSession();
        //dump($id);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
        $ie=$tramite->getInstitucioneducativa()->getInstitucioneducativa();
        $idrue = $tramite->getInstitucioneducativa()->getId();
        $tipotramite = $tramite->getTramiteTipo()->getTramiteTipo();
        $estadoinstitucioneducativa = $tramite->getInstitucioneducativa()->getEstadoinstitucionTipo()->getEstadoinstitucion();

        $juridicaDepartamentalForm = $this->createJuridicaDepartamentalForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$id); 
        //return $this->render('SieProcesosBundle:FlujoProceso:index1.html.twig');
        return $this->render('SieProcesosBundle:TramiteRue:juridicaDepartamentalNuevo.html.twig', array(
            'form' => $juridicaDepartamentalForm->createView(),
        ));

    }

    public function notificaionDepartamentalGuardarAction(Request $request)
    {
        $form = $request->get('form');
        //dump($form);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $tramite = $form['tramite'];
        $tabla = 'institucioneducativa';
        $id_tabla = $form['idrue'];
        $observacion = $form['observacion'];
        $uDestinatario = 13834044;
        $varevaluacion = $form['varevaluacion'];
        $mensaje = $this->guardarTramiteDetalle($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,'',$varevaluacion,$tramite);
        $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        return $this->redirect($this->generateUrl('tramite_rue_juridica_departamental'));
    }



    public function buscarRueAction(Request $request)
    {
        //dump($request);die;
        $idrue=$request->get('idrue');
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT ie
        FROM SieAppWebBundle:Institucioneducativa ie
        WHERE ie.id = :id
        and ie.institucioneducativaAcreditacionTipo = :ieAcreditacion
        ORDER BY ie.id')
                    ->setParameter('id', $idrue)
                ->setParameter('ieAcreditacion', 1)
                ;
        $entities = $query->getResult();
        $ie=$entities[0]->getInstitucioneducativa();
        $dep=$entities[0]->getDependenciaTipo()->getId();
        $tipo=$entities[0]->getInstitucioneducativaTipo()->getId();
        $estado=$entities[0]->getEstadoinstitucionTipo()->getId();
        $departamento=$entities[0]->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar();
        $distrito=$entities[0]->getLeJuridicciongeografica()->getDistritoTipo()->getDistrito();
        //dump($entities);die;
        $response = new JsonResponse();
        $response->setData(array('ie'=>$ie,'dep'=>$dep,'tipo'=>$tipo,'estado'=>$estado,'departamento'=>$departamento,'distrito'=>$distrito));
        //$response->setData(array('ue' => array('ie'=>$ie,'dep'=>$dep,'tipo'=>$tipo)));
        return $response;
    }

    
    public function tramiteTarea($tarea_ant,$tarea_actual,$flujotipo,$usuario,$rol)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('select lugar_tipo_id from usuario_rol where usuario_id='. $usuario .' and rol_tipo_id=' . $rol);
        $query->execute();
        $lugarTipo = $query->fetchAll();
        $query1 = $em->getConnection()->prepare('select * from flujo_proceso where id=' . $tarea_ant . ' and es_evaluacion=true');
        $query1->execute();
        $evaluacion = $query1->fetchAll();
        if ($evaluacion)
        {
            $query = $em->getConnection()->prepare('select t.id,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre
            from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
            join tramite_tipo tt on t.tramite_tipo=tt.id
            join institucioneducativa ie on t.institucioneducativa_id=ie.id
            join usuario_rol ur on td.usuario_remitente_id=ur.usuario_id
            join usuario u on td.usuario_remitente_id=u.id
            join persona p on p.id=u.persona_id
            where t.flujo_tipo_id='. $flujotipo .' and t.fecha_fin is null and td.flujo_proceso_id='. $tarea_ant .' and ur.rol_tipo_id='. $rol .' and ur.lugar_tipo_id='. $lugarTipo[0]['lugar_tipo_id'] . ' and td.valor_evaluacion = (select condicion from wf_tarea_compuerta where flujo_proceso_id='. $tarea_ant .' and condicion_tarea_siguiente='. $tarea_actual . ')');            
        }else{
            $query = $em->getConnection()->prepare('select t.id,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre
            from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
            join tramite_tipo tt on t.tramite_tipo=tt.id
            join institucioneducativa ie on t.institucioneducativa_id=ie.id
            join usuario_rol ur on td.usuario_remitente_id=ur.usuario_id
            join usuario u on td.usuario_remitente_id=u.id
            join persona p on p.id=u.persona_id
            where t.flujo_tipo_id='. $flujotipo .' and t.fecha_fin is null and td.flujo_proceso_id='. $tarea_ant .' and ur.rol_tipo_id='. $rol .' and ur.lugar_tipo_id='.$lugarTipo[0]['lugar_tipo_id']);
        }
        $query->execute();
        $tramites = $query->fetchAll();
        $data['tramites'] = $tramites;
        return $data;
    }
    
    public function guardarTramiteDetalle($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$tipotramite,$varevaluacion,$idtramite)
    {
        $tramiteDetalle = new TramiteDetalle();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('tramite_detalle');")->execute();
        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea);
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($usuario);
        $tramiteestado = $em->getRepository('SieAppWebBundle:TramiteEstado')->find(1);
        //insert tramite
        if($flujoproceso->getOrden() == 1){
            $tramite = new Tramite();
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('tramite');")->execute();
            $flujotipo = $em->getRepository('SieAppWebBundle:FlujoTipo')->find($flujotipo);
            $tramitetipo = $em->getRepository('SieAppWebBundle:TramiteTipo')->find($tipotramite);
            $tramite->setFlujoTipo($flujotipo);
            $tramite->setTramiteTipo($tramitetipo);
            $tramite->setFechaTramite(new \DateTime(date('Y-m-d')));
            $tramite->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $tramite->setEsactivo(true);
            $tramite->setGestionId((new \DateTime())->format('Y'));
            switch ($tabla) {
                case 'institucioneducativa':
                    $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id_tabla);
                    $tramite->setInstitucioneducativa($institucioneducativa);
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
        }else{
            /*$query = $em->getConnection()->prepare('select * from tramite_detalle where flujo_proceso_id='. $flujoproceso->getTareaAntId());
            $query->execute();
            $tramiteD = $query->fetchAll();*/
            //dump($idtramite);die;
            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
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
        //dump($flujoproceso);die;
        if ($flujoproceso->getEsEvaluacion() == true) 
        {
            $tramiteDetalle->setValorEvaluacion($varevaluacion);
        }
        if($flujoproceso->getWfAsignacionTareaTipo()->getId() == 3) //por seleccion
        {
               if($flujoproceso->getOrden() > 1)
               {
                    
                    $query = $em->getConnection()->prepare('select * from tramite_detalle where flujo_proceso_id='. $flujoproceso->getTareaAntId().' and tramite_id='.$idtramite);
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