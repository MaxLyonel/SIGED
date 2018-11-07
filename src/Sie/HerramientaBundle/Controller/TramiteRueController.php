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
use Sie\HerramientaBundle\Controller\WfTramiteController;



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

    public function createRecepcionForm()
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('recepcion_distrito_guardar'))
        ->add('flujoproceso', 'hidden', array('data' =>39 ))
        ->add('flujotipo', 'hidden', array('data' =>6 ))
       ->add('tipoeducacion','entity',array('label'=>'Tipo de educación','required'=>true,'class'=>'SieAppWebBundle:InstitucioneducativaTipo','query_builder'=>function(EntityRepository $e){
            return $e->createQueryBuilder('e')->where('e.id not in (:id)')->setParameter('id',array(0,3,7,8,9,10))->orderBy('e.id','ASC');},'property'=>'descripcion','empty_value' => 'Seleccione tipo de educación'))
        ->add('tramitetipo','entity',array('label'=>'Tipo de trámite','required'=>true,'class'=>'SieAppWebBundle:TramiteTipo','query_builder'=>function(EntityRepository $tr){
            return $tr->createQueryBuilder('tr')->where('tr.obs = :rue')->setParameter('rue','RUE')->orderBy('tr.id','ASC');},'property'=>'tramite_tipo','empty_value' => 'Seleccione trámite'))
        ->add('idrue','text',array('label'=>'Código RUE','required'=>false))
        ->add('dependenciaTipo','entity',array('label'=>'Dependencia','required'=>true,'class'=>'SieAppWebBundle:DependenciaTipo','query_builder'=>function(EntityRepository $dt){
            return $dt->createQueryBuilder('dt')->where('dt.id in (:id)')->setParameter('id',array(1,2,3))->orderBy('dt.id','ASC');},'property'=>'dependencia','empty_value' => 'Seleccione dependencia'))
        /*->add('turnotipo','entity',array('label'=>'Turnos','required'=>true,'class'=>'SieAppWebBundle:TurnoTipo','query_builder'=>function(EntityRepository $t){
            return $t->createQueryBuilder('t')->where('t.id not in (:id)')->setParameter('id',array(0))->orderBy('t.id','ASC');},'property'=>'turno','empty_value' => 'Seleccione turno'))*/
        ->add('institucionEducativa', 'text', array('label' => 'Nombre de la Unidad Educativa','required' => true, 'attr' => array('class' => 'form-control', 'maxlength' => '69', 'style' => 'text-transform:uppercase')))
        ->add('requisitos','choice',array('label'=>'Requisitos','required'=>false, 'multiple' => true,'expanded' => true,'choices'=>array('legal' => 'Legal','administrativo' => 'Administrativo','pedagogico' => 'Técnico pedagógico','infra'=>'Infraestructura')))
        ->add('observacion','textarea',array('label'=>'Observación'))
        ->add('buscar','button',array('label'=>'Buscar'))
        ->add('guardar','submit',array('label'=>'Enviar'))
        ->getForm();
        return $form;
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

        $data = $this->tramiteTarea(39,39,6,$usuario,$rol,'');
        return $this->render('SieHerramientaBundle:TramiteRue:recepcionDistrito.html.twig', $data);
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
        //return $this->render('SieHerramientaBundle:FlujoProceso:index1.html.twig');
        return $this->render('SieHerramientaBundle:TramiteRue:recepcionDistritoNuevo.html.twig', array(
            'form' => $recepcionForm->createView(),
        ));
    }

    public function formNuevoAction(Request $request)
    {
        //dump($request);die;
        $this->session = $request->getSession();
        //dump($this->session);die;
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $recepcionFormNuevo = $this->createRecepcionForm1($request->get('tramitetipo'),$request->get('tipoeducacion')); 
        return $this->render('SieHerramientaBundle:TramiteRue:recepcionDistritoFormNuevo.html.twig', array(
            'form_nuevo' => $recepcionFormNuevo->createView(),'tipoeducacion'=>$request->get('tipoeducacion')
        ));
        
    }

    public function recepcionDistritoGuardarAction(Request $request)
    {

        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form);die;
        $datos = json_encode($form);
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$usuario,'rolTipo'=>$rol));            
        $idlugarusuario = $usuariorol[0]->getLugarTipo()->getId();
        //dump($idlugarusuario);die;
        if ($form['idrue']){
            $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['idrue']);
            $ie_lugardistrito = $ie->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
        }
        //dump($ie_lugardistrito);die;
        if (!$form['idrue'] or ($ie_lugardistrito == $idlugarusuario)){
            $flujotipo = $form['flujotipo'];
            $tarea = $form['flujoproceso'];
            $tabla = 'institucioneducativa';
            $id_tabla = $form['idrue'];
            $observacion = $form['observacion'];
            $tipotramite = $form['tramitetipo'];
            //$uDestinatario = 13834044;
            //dump($datos);die;
            $WfTramiteController = new WfTramiteController();
            $WfTramiteController->setContainer($this->container);
            $mensaje = $WfTramiteController->guardarTramiteNuevo($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$tipotramite,'','',$datos,$idlugarusuario);
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        }else{

            $request->getSession()
                ->getFlashBag()
                ->add('error', "La unidad educativa no es de su jurisdicción");
        }
        
        //$recepcionForm = $this->createRecepcionForm(); 
        //return $this->render('SieHerramientaBundle:FlujoProceso:index1.html.twig');
        return $this->redirectToRoute('wf_tramite_index');
        //return $this->redirect($this->generateUrl('tramite_rue_recepcion_distrito'));
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

        $data = $this->tramiteTarea(39,40,6,$usuario,$rol,'');
        return $this->render('SieHerramientaBundle:TramiteRue:informeDistrito.html.twig', $data);
    }
    public function informeDistritoNuevoAction(Request $request)
    {
        
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
        if ($tramite->getInstitucioneducativa()){
            $ie=$tramite->getInstitucioneducativa()->getInstitucioneducativa();
            $idrue = $tramite->getInstitucioneducativa()->getId();
            $tipoeducacion = $tramite->getInstitucioneducativa()->getInstitucioneducativaTipo()->getId();
            $dependenciatipo = $tramite->getInstitucioneducativa()->getDependenciaTipo()->getId();
            
        }else{
            $wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->findBy(array('tramite'=>$id));
            $datos = json_decode($wfSolicitudTramite[0]->getDatos(),true);
            //sdump($datos);die;
            $ie = $datos['institucionEducativa'];
            $idrue = "";
            $tipoeducacion = $datos['tipoeducacion'];
            $dependenciatipo = $datos['dependenciaTipo'];
            
        }
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $informeForm = $this->createInformeDistritoForm($ie,$idrue,$tipotramite,$tipoeducacion,$dependenciatipo,$id); 
        //return $this->render('SieHerramientaBundle:FlujoProceso:index1.html.twig');
        return $this->render('SieHerramientaBundle:TramiteRue:informeDistritoNuevo.html.twig', array(
            'form' => $informeForm->createView(),'idtramite'=>$id,'idrue'=>$idrue,
        ));

    }

    public function createInformeDistritoForm($ie,$idrue,$tipotramite,$tipoeducacion,$dependenciatipo,$idtramite)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_rue_informe_distrito_guardar'))
            ->add('flujoproceso', 'hidden', array('data' =>40 ))
            ->add('flujotipo', 'hidden', array('data' =>6 ))
            ->add('tramite', 'hidden', array('data' =>$idtramite ))
            ->add('institucioneducativa','hidden',array('label'=>'Unidad educativa','data'=>$ie,'read_only'=>true))
            ->add('idrue','hidden',array('label'=>'Código RUE','data'=>$idrue,'read_only'=>true))
            ->add('tipotramite','hidden',array('label'=>'Tipo de Tramite','data'=>$tipotramite,'read_only'=>true))
            ->add('tipoeducacion','hidden',array('label'=>'Tipo de Educación','data'=>$tipoeducacion,'read_only'=>true))
            ->add('dependenciaTipo','hidden',array('label'=>'Dependencia','data'=>$dependenciatipo,'read_only'=>true))
            //->add('requisitos','choice',array('label'=>'Requisitos','required'=>false,'read_only'=>true, 'multiple' => true,'expanded' => true,'choices'=>array('legal' => 'Legal','administrativo' => 'Administrativo','pedagogico' => 'Técnico pedagógico','infra'=>'Infraestructura')))
            ->add('informe','text',array('label'=>'Nro. de Informe','attr' => array('data-mask'=>'0000/0000', 'placeholder'=>'0000/YYYY','maxlength' => '9')))
            ->add('fechainforme', 'text', array('label' => 'Fecha de Informe','required' => true, 'attr' => array('placeholder' => 'dd-mm-yyyy')))
            ->add('observacion','textarea',array('label'=>'Observación'))
            ->add('guardar','submit',array('label'=>'Enviar Informe'))
            ->getForm();
        return $form;
    }
 
    public function informeDistritoGuardarAction(Request $request)
    {
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        $datos = json_encode($form);
        //dump($form);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $idtramite = $form['tramite'];
        $tabla = 'institucioneducativa';
        $id_tabla = $form['idrue'];
        $observacion = $form['observacion'];
        $uDestinatario = 13834044;
        $varevaluacion = "";
        $WfTramiteController = new WfTramiteController();
        $WfTramiteController->setContainer($this->container);
        $mensaje = $WfTramiteController->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion,$idtramite,$datos);
        $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        //$response = new JsonResponse();
        //return $response->setData(array('mensaje' => $mensaje));   
        return $this->redirectToRoute('wf_tramite_recibido');
        //return $this->redirect($this->generateUrl('tramite_rue_informe_distrito'));
    }

    public function createRecepcionDepartamentalNuevoForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$idtramite)
    {
     //dump($tramitetipo);die;
        $tipoDoc = array('adjunto'=>'Adjunto','noadjunto'=>'No Adjunto','incompleto'=>'Incompleto');
        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('tramite_rue_recepcion_departamental_guardar'))
        ->add('flujoproceso', 'hidden', array('data' =>41 ))
        ->add('flujotipo', 'hidden', array('data' =>6 ))
        ->add('tipoeducacion1', 'hidden', array('data' =>$tipoeducacion ))
        ->add('tramitetipo1', 'hidden', array('data' =>$tipotramite ))
        ->add('observacion','textarea',array('label'=>'Observación','required'=>false))
    	->add('institucionEducativa', 'text', array('label' => 'Nombre de la Unidad Educativa','required' => true, 'attr' => array('class' => 'form-control', 'maxlength' => '69', 'style' => 'text-transform:uppercase')))
        ->add('dependenciaTipo','entity',array('label'=>'Dependencia','required'=>true,'class'=>'SieAppWebBundle:DependenciaTipo','query_builder'=>function(EntityRepository $dt){
            return $dt->createQueryBuilder('dt')->where('dt.id in (:id)')->setParameter('id',array(1,2,3))->orderBy('dt.id','ASC');},'property'=>'dependencia','empty_value' => 'Seleccione dependencia'))
        /*->add('convenioTipo','entity',array('label'=>'Convenio','required'=>true,'class'=>'SieAppWebBundle:ConvenioTipo','query_builder'=>function(EntityRepository $ct){
            return $ct->createQueryBuilder('ct')->where('ct.id not in (:id)')->setParameter('id',array(0,99))->orderBy('ct.id','ASC');},'property'=>'convenio','empty_value' => 'Seleccione convenio'))*/
        ->add('turnotipo','entity',array('label'=>'Turnos','required'=>true,'class'=>'SieAppWebBundle:TurnoTipo','query_builder'=>function(EntityRepository $t){
            return $t->createQueryBuilder('t')->where('t.id not in (:id)')->setParameter('id',array(0))->orderBy('t.id','ASC');},'property'=>'turno','empty_value' => 'Seleccione turno'))
        ->add('departamento','entity',array('label'=>'Departamento','required'=>true,'attr' => array('class' => 'form-control jupper'),'class'=>'SieAppWebBundle:LugarTipo','query_builder'=>function(EntityRepository $lt){
            return $lt->createQueryBuilder('lt')->where('lt.lugarNivel = 1')->andWhere('lt.paisTipoId=1')->orderBy('lt.id','ASC');},'property'=>'lugar','empty_value' => 'Seleccione departamento'))
    	->add('provincia', 'choice', array('label' => 'Provincia','required'=>false,'attr' => array('class' => 'form-control jupper')))
    	->add('municipio', 'choice', array('label' => 'Municipio','required'=>false, 'attr' => array('class' => 'form-control jupper')))
    	->add('canton', 'choice', array('label' => 'Cantón','required'=>false, 'attr' => array('class' => 'form-control jupper')))
    	->add('localidad', 'choice', array('label' => 'Localidad/Comunidad','required'=>false,'attr' => array('class' => 'form-control')))
    	->add('zona', 'text', array('label' => 'Zona','required'=>false,'attr' => array('class' => 'form-control')))
    	->add('direccion', 'text', array('label' => 'Dirección','required'=>false,'attr' => array('class' => 'form-control')))
        ->add('distrito', 'choice', array('label' => 'Distrito Educativo','attr' => array('class' => 'form-control')));
        if ($tipoeducacion == 4) {
            $form->add('niveltipo','entity',array('label'=>'Niveles','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
                return $nt->createQueryBuilder('nt')->where('nt.id in (:id)')->setParameter('id',array(11,12,405))->orderBy('nt.id','ASC');},'property'=>'nivel','empty_value' => 'Seleccione turno'));
            $form->add('areaEspecialTipo','entity',array('label'=>'Areas','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:EspecialAreaTipo','query_builder'=>function(EntityRepository $et){
                return $et->createQueryBuilder('et')->orderBy('et.id','ASC');},'property'=>'area_especial'));
		}
		elseif ($tipoeducacion == 1) {
			$form->add('niveltipo','entity',array('label'=>'Niveles','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
                return $nt->createQueryBuilder('nt')->where('nt.id in (:id)')->setParameter('id',array(11,12,13))->orderBy('nt.id','ASC');},'property'=>'nivel'));
		}elseif($tipoeducacion == 2 or $tipoeducacion == 5){
            $form->add('niveltipo','entity',array('label'=>'Niveles','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
                return $nt->createQueryBuilder('nt')->where('nt.id between 200 and 299')->orderBy('nt.id','ASC');},'property'=>'nivel'));
        }
        //informacion legal
        $form = $form
        ->add('participantes', 'text', array('label' => 'Cantidad de participantes','attr' => array('class' => 'form-control')))
        ->add('solicitante', 'choice', array('label' => 'La persona jurídica que hace la solicitud corresponde a:','choices'=>array(
            'Director Distrital de Educación a nombre del estado'=>'Director Distrital de Educación a nombre del estado',
            'Sociedad Anónima o Sociedad de Responsabilidad Limitada'=>'Sociedad Anónima o Sociedad de Responsabilidad Limitada',
            'Sociedades Cooperativas'=>'Sociedades Cooperativas',
            'Asociaciones o Fundaciones sin Fines de Lucro','Asociaciones o Fundaciones sin Fines de Lucro',
            'Empresas Unipersonales'=>'Empresas Unipersonales',
            'Iglesia','Iglesia'),'attr' => array('class' => 'form-control')))
        ->add('solicitante_otro', 'text', array('label' => 'Otro:','attr' => array('class' => 'form-control')))
        ->add('acta', 'choice', array('label' => 'Copia Notariada del Acta de Constitución','choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('estatutos', 'choice', array('label' => 'Copia Notariada de los estatutos de la U.E./CEA','choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('resolucion', 'choice', array('label' => 'Copia legalizada de la Resolución que otorga la personeria jurídica','choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('fundaempresa', 'choice', array('label' => 'Fotocopia legalizada de la matricula y resolucion de FUNDAEMPRESA','choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('nit', 'choice', array('label' => 'Fotocopia legalizada del NIT','choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('ci', 'choice', array('label' => 'Fotocopia legalizada del carnet de identidad del propietario','choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('balance', 'choice', array('label' => 'Balance de Apertura Sellado por el Servicio de Impuestos Nacionales','choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('ultimobalance', 'choice', array('label' => 'Último Balance Sellado por el Servicio de Impuestos Nacionales','choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('acreditacion', 'choice', array('label' => 'Acreditación del representante legal','choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('padron', 'choice', array('label' => 'Fotocopia legalizada del Padrón de funcionamiento municipal','choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('funcionamientoculto', 'choice', array('label' => 'Resolución de funcionamiento del Ministerio de Relaciones Exteriores y Culto','choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('nombrerepresentante', 'text', array('label' => 'Nombre del representante legal','attr' => array('class' => 'form-control')))
        ->add('direccionrepresentante', 'text', array('label' => 'Dirección del representante legal','attr' => array('class' => 'form-control')))
        ->add('requisitos','choice',array('label'=>'Requisitos','required'=>false, 'multiple' => true,'expanded' => true,'choices'=>array('legal' => 'Legal','administrativo' => 'Administrativo','pedagogico' => 'Técnico pedagógico','infra'=>'Infraestructura')))
        ->add('areaterreno', 'text', array('label' => 'Área total del terreno en m2','attr' => array('class' => 'form-control')))
        ->add('areaconstruida', 'text', array('label' => 'Área construida en m2','attr' => array('class' => 'form-control')))
        ->add('areaesparcimiento', 'text', array('label' => 'Área de esparcimiento en m2','attr' => array('class' => 'form-control')))
        ->add('areanoconstruida', 'text', array('label' => 'Área no construida en m2','attr' => array('class' => 'form-control')))
        ->add('areacultivo', 'text', array('label' => 'Área de cultivo (Área libre) en m2','attr' => array('class' => 'form-control')))
        ->add('edificioes', 'choice', array('label' => 'El edificio es:','choices'=>array('PROPIO'=>'PROPIO','ALQUILADO'=>'ALQUILADO','ANTICRETICO'=>'ANTICREDITO','MUNICIPAL'=>'MUNICIPAL'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('modalidadatencion', 'choice', array('label' => 'Modalidad de atención','choices'=>array('PRESENCIAL'=>'PRESENCIAL','SEMIPRESENCIAL'=>'SEMIPRESENCIAL'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('modalidadlengua', 'text', array('label' => 'Modalidad de lengua','attr' => array('class' => 'form-control')))
        ->add('modalidaddocencia', 'choice', array('label' => 'Modalidad de Docencia','choices'=>array('UNIDOCENTE'=>'UNIDOCENTE'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('modalidadaprendizaje', 'text', array('label' => 'Modalidad de aprendizaje','attr' => array('class' => 'form-control')))
        ->add('teorico', 'choice', array('label' => 'Fundamentos Teóricos generales','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('intercultural', 'choice', array('label' => 'Enfoque intercultural','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('propuestalengua', 'choice', array('label' => 'Propuesta de modalidad de lengua','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('propuestaciclos', 'choice', array('label' => 'Propuesta de los niveles y ciclos ofertados','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('propuestaintegracion', 'choice', array('label' => 'Propuesta de integración educativa','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('denominacionnivel', 'choice', array('label' => 'Denominación de los niveles y ciclos ofertados','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('organizacionciclos', 'choice', array('label' => 'Organización de los niveles y ciclos','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('temastransversales', 'choice', array('label' => 'Incorporación de temas transversales','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('areascurriculares', 'choice', array('label' => 'Enfoque de las áreas curriculares','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('competencias', 'choice', array('label' => 'Planteamiento de competencias y objetivos','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('indicadores', 'choice', array('label' => 'Planteamiento de indicadores','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('contenidos', 'choice', array('label' => 'Organización de contenidos','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('metodologia', 'choice', array('label' => 'Propuesta metodológica','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('evaluacion', 'choice', array('label' => 'Propuesta de evaluación','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('horario', 'choice', array('label' => 'Organización del horario','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('calendario', 'choice', array('label' => 'Organización del calendario','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('textos', 'choice', array('label' => 'Textos o módulos propuestos para el uso con alumnos / participantes','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('curriculo', 'choice', array('label' => 'Propuesta de gestión curricular','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('areasnoincluidas', 'choice', array('label' => 'Pertinencia de nuevas áreas no incluidas en el tronco comun','choices'=>array('SI'=>'SI','NO'=>'NO'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('planesprogramas', 'choice', array('label' => 'PLANES Y PROGRAMAS','choices'=>array('APROBADOS'=>'APROBADOS','NO APROBADOS'=>'NO APROBADOS','NO CORRESPONDE SU PRESENTACION'=>'NO CORRESPONDE SU PRESENTACION'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('guardar','submit',array('label'=>'Enviar Formulario'))
        ->getForm();
        //dump($form);die;
        return $form;
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

        $data = $this->tramiteTarea(40,41,6,$usuario,$rol,'');
        //
        //dump($data);die;
        return $this->render('SieHerramientaBundle:TramiteRue:recepcionDepartamental.html.twig', $data);
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

        $recepcionDepartamentalForm = $this->createRecepcionDepartamentalNuevoForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$id); 
        //return $this->render('SieHerramientaBundle:FlujoProceso:index1.html.twig');
        return $this->render('SieHerramientaBundle:TramiteRue:recepcionDepartamentalNuevo.html.twig', array(
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
        $datos= '{"rojo":"#f00","verde":"#0f0","azul":"#00f","cyan":"#0ff","magenta":"#f0f","amarillo":"#ff0","negro":"#000"}';
        $mensaje = $this->guardarTramiteDetalle($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,'',$varevaluacion,$tramite,$datos);
        $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        return $this->redirect($this->generateUrl('tramite_rue_recepcion_departamental'));
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

        $data = $this->tramiteTarea(24,25,5,$usuario,$rol,'');
        //
        //dump($data);die;
        return $this->render('SieHerramientaBundle:TramiteRue:juridicaDepartamental.html.twig', $data);
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
        //return $this->render('SieHerramientaBundle:FlujoProceso:index1.html.twig');
        return $this->render('SieHerramientaBundle:TramiteRue:juridicaDepartamentalNuevo.html.twig', array(
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
        $datos= '{"rojo":"#f00","verde":"#0f0","azul":"#00f","cyan":"#0ff","magenta":"#f0f","amarillo":"#ff0","negro":"#000"}';
        $mensaje = $this->guardarTramiteDetalle($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,'',$varevaluacion,$tramite,$datos);
        $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        return $this->redirect($this->generateUrl('tramite_rue_juridica_departamental'));
    }

    public function createNotificacionDepartamentalForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$idtramite)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_rue_notificacion_departamental_guardar'))
            ->add('flujoproceso', 'hidden', array('data' =>26 ))
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

        $data = $this->tramiteTarea(25,26,5,$usuario,$rol,'');
        //
        //dump($data);die;
        return $this->render('SieHerramientaBundle:TramiteRue:notificacionDepartamental.html.twig', $data);
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

        $notificacionDepartamentalForm = $this->createNotificacionDepartamentalForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$id); 
        //return $this->render('SieHerramientaBundle:FlujoProceso:index1.html.twig');
        return $this->render('SieHerramientaBundle:TramiteRue:notificacionDepartamentalNuevo.html.twig', array(
            'form' => $notificacionDepartamentalForm->createView(),
        ));

    }

    public function notificacionDepartamentalGuardarAction(Request $request)
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
        $datos= '{"rojo":"#f00","verde":"#0f0","azul":"#00f","cyan":"#0ff","magenta":"#f0f","amarillo":"#ff0","negro":"#000"}';
        $mensaje = $this->guardarTramiteDetalle($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,'',$varevaluacion,$tramite,$datos);
        $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        return $this->redirect($this->generateUrl('tramite_rue_notificacion_departamental'));
    }

    public function createResolucionDepartamentalForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$idtramite)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_rue_resolucion_departamental_guardar'))
            ->add('flujoproceso', 'hidden', array('data' =>27 ))
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
    public function resolucionDepartamentalAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $data = $this->tramiteTarea(25,27,5,$usuario,$rol,'');
        //
        //dump($data);die;
        return $this->render('SieHerramientaBundle:TramiteRue:resolucionDepartamental.html.twig', $data);
    }
    public function resolucionDepartamentalNuevoAction(Request $request,$id)
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

        $resolucionDepartamentalForm = $this->createResolucionDepartamentalForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$id); 
        //return $this->render('SieHerramientaBundle:FlujoProceso:index1.html.twig');
        return $this->render('SieHerramientaBundle:TramiteRue:resolucionDepartamentalNuevo.html.twig', array(
            'form' => $resolucionDepartamentalForm->createView(),
        ));

    }

    public function resolucionDepartamentalGuardarAction(Request $request)
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
        $datos= '{"rojo":"#f00","verde":"#0f0","azul":"#00f","cyan":"#0ff","magenta":"#f0f","amarillo":"#ff0","negro":"#000"}';
        $mensaje = $this->guardarTramiteDetalle($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,'',$varevaluacion,$tramite,$datos);
        $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        return $this->redirect($this->generateUrl('tramite_rue_resolucion_departamental'));
    }

    public function createFormulariosDepartamentalForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$idtramite)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_rue_formularios_departamental_guardar'))
            ->add('flujoproceso', 'hidden', array('data' =>28 ))
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
    public function formulariosDepartamentalAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $data = $this->tramiteTarea(27,28,5,$usuario,$rol,'');
        //
        //dump($data);die;
        return $this->render('SieHerramientaBundle:TramiteRue:formulariosDepartamental.html.twig', $data);
    }
    public function formulariosDepartamentalNuevoAction(Request $request,$id)
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

        $formulariosDepartamentalForm = $this->createFormulariosDepartamentalForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$id); 
        //return $this->render('SieHerramientaBundle:FlujoProceso:index1.html.twig');
        return $this->render('SieHerramientaBundle:TramiteRue:formulariosDepartamentalNuevo.html.twig', array(
            'form' => $formulariosDepartamentalForm->createView(),
        ));

    }

    public function formulariosDepartamentalGuardarAction(Request $request)
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
        $datos= '{"rojo":"#f00","verde":"#0f0","azul":"#00f","cyan":"#0ff","magenta":"#f0f","amarillo":"#ff0","negro":"#000"}';
        $mensaje = $this->guardarTramiteDetalle($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,'',$varevaluacion,$tramite,$datos);
        $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        return $this->redirect($this->generateUrl('tramite_rue_formularios_departamental'));
    }

    public function createRevisarMineduForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$idtramite)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_rue_reviar_minedu_guardar'))
            ->add('flujoproceso', 'hidden', array('data' =>29 ))
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
    public function revisarMineduAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $data = $this->tramiteTarea(28,29,5,$usuario,$rol,'');
        //
        //dump($data);die;
        return $this->render('SieHerramientaBundle:TramiteRue:revisarMinedu.html.twig', $data);
    }
    public function revisarMineduNuevoAction(Request $request,$id)
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

        $revisarMineduForm = $this->createrevisarMineduForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$id); 
        //return $this->render('SieHerramientaBundle:FlujoProceso:index1.html.twig');
        return $this->render('SieHerramientaBundle:TramiteRue:revisarMineduNuevo.html.twig', array(
            'form' => $revisarMineduForm->createView(),
        ));

    }

    public function revisarMineduGuardarAction(Request $request)
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
        $datos= '{"rojo":"#f00","verde":"#0f0","azul":"#00f","cyan":"#0ff","magenta":"#f0f","amarillo":"#ff0","negro":"#000"}';
        $mensaje = $this->guardarTramiteDetalle($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,'',$varevaluacion,$tramite,$datos);
        $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        return $this->redirect($this->generateUrl('tramite_rue_revisar_minedu'));
    }

    public function createNotificacionMineduForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$idtramite)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_rue_notificacion_minedu_guardar'))
            ->add('flujoproceso', 'hidden', array('data' =>30 ))
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
    public function notificacionMineduAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $data = $this->tramiteTarea(29,30,5,$usuario,$rol,'');
        //
        //dump($data);die;
        return $this->render('SieHerramientaBundle:TramiteRue:notificacionMinedu.html.twig', $data);
    }
    public function notificacionMineduNuevoAction(Request $request,$id)
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

        $notificacionMineduForm = $this->createNotificacionMineduForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$id); 
        //return $this->render('SieHerramientaBundle:FlujoProceso:index1.html.twig');
        return $this->render('SieHerramientaBundle:TramiteRue:notificacionMineduNuevo.html.twig', array(
            'form' => $notificacionMineduForm->createView(),
        ));

    }

    public function notificacionMineduGuardarAction(Request $request)
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
        $datos= '{"rojo":"#f00","verde":"#0f0","azul":"#00f","cyan":"#0ff","magenta":"#f0f","amarillo":"#ff0","negro":"#000"}';
        $mensaje = $this->guardarTramiteDetalle($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,'',$varevaluacion,$tramite,$datos);
        $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        return $this->redirect($this->generateUrl('tramite_rue_notificacion_minedu'));
    }

    public function createRegistrarMineduForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$idtramite)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_rue_notificacion_minedu_guardar'))
            ->add('flujoproceso', 'hidden', array('data' =>31 ))
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
    public function registrarMineduAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $data = $this->tramiteTarea(29,31,5,$usuario,$rol,'');
        //
        //dump($data);die;
        return $this->render('SieHerramientaBundle:TramiteRue:registrarMinedu.html.twig', $data);
    }
    
    public function registrarMineduNuevoAction(Request $request,$id)
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

        $registrarMineduForm = $this->createRegistrarMineduForm($ie,$idrue,$tipotramite,$estadoinstitucioneducativa,$id); 
        //return $this->render('SieHerramientaBundle:FlujoProceso:index1.html.twig');
        return $this->render('SieHerramientaBundle:TramiteRue:registrarMineduNuevo.html.twig', array(
            'form' => $registrarMineduForm->createView(),
        ));

    }

    public function registrarMineduGuardarAction(Request $request)
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
        $datos= '[{"rojo":"#f00","verde":"#0f0","azul":"#00f","cyan":"#0ff","magenta":"#f0f","amarillo":"#ff0","negro":"#000"}]';
        $mensaje = $this->guardarTramiteDetalle($usuario,$uDestinatario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,'',$varevaluacion,$tramite,$datos);
        $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        return $this->redirect($this->generateUrl('tramite_rue_registrar_minedu'));
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
        //dump($entities);die;
        $response = new JsonResponse();
        $response->setData(array('ie'=>$ie,'dep'=>$dep));
        //$response->setData(array('ue' => array('ie'=>$ie,'dep'=>$dep,'tipo'=>$tipo)));
        return $response;
    }

    public function tramiteTarea($tarea_ant,$tarea_actual,$flujotipo,$usuario,$rol,$id_ie)
    {
        $em = $this->getDoctrine()->getManager();
        /**   
         * id del lugar usuario
        */
        $query = $em->getConnection()->prepare('select lugar_tipo_id from usuario_rol where usuario_id='. $usuario .' and rol_tipo_id=' . $rol);
        $query->execute();
        $lugarTipo = $query->fetchAll();

        /**
         * tareas devueltas por condicion
         */
        $wftareac = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->createQueryBuilder('wf')
                ->select('fp.id,wf.condicion')
                ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = wf.flujoProceso')
                ->where('wf.condicionTareaSiguiente =' . $tarea_actual)
                ->getQuery()
                ->getResult();
        
        //dump($wftareac);die;
        /**
         * tareas devueltas
         */
        $fp = $em->getRepository('SieAppWebBundle:FlujoProceso')->createQueryBuilder('fp')
                ->select('fp.id')
                ->where('fp.tareaSigId =' . $tarea_actual)
                ->getQuery()
                ->getResult();
        /**
         * tarea anterior
         */
        $tarea = 'td.flujo_proceso_id='. $tarea_ant;

        if($wftareac and $fp){
            $tarea = "(" . $tarea . " or (td.flujo_proceso_id=". $wftareac[0]['id'] ." and td.valor_evaluacion='". $wftareac[0]['condicion'] ."') or td.flujo_proceso_id=". $fp[0]['id']. ")";
        }elseif ($wftareac){
            $tarea = "(" . $tarea . " or (td.flujo_proceso_id=". $wftareac[0]['id'] ." and td.valor_evaluacion='". $wftareac[0]['condicion'] ."'))";
        }elseif ($fp){
            $tarea = "(" . $tarea . " or td.flujo_proceso_id=". $fp[0]['id']. ")";
        }
        /**
         * si tiene condicion la tarea anterior
         */
        $query1 = $em->getConnection()->prepare('select * from flujo_proceso where id=' . $tarea_ant . ' and es_evaluacion=true');
        $query1->execute();
        $evaluacion = $query1->fetchAll();
        
        if($rol == 9){ //DIRECTOR
            $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                join institucioneducativa ie on t.institucioneducativa_id=ie.id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea ." and ie.id=". $id_ie);
        }elseif ($rol == 10) { //DISTRITO
            if ($evaluacion)
            {
                $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                left join institucioneducativa ie on t.institucioneducativa_id=ie.id
                left join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
                left join wf_solicitud_tramite wfst on t.id=wfst.tramite_id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea ." and ((t.institucioneducativa_id is not null and le.lugar_tipo_id_distrito=". $lugarTipo[0]['lugar_tipo_id'] .") or (t.institucioneducativa_id is null and wfst.lugar_tipo_id=". $lugarTipo[0]['lugar_tipo_id'] .")) and td.valor_evaluacion = (select condicion from wf_tarea_compuerta where flujo_proceso_id=". $tarea_ant ." and condicion_tarea_siguiente=". $tarea_actual . ")");
            }else{
                $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                left join institucioneducativa ie on t.institucioneducativa_id=ie.id
                left join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
                left join wf_solicitud_tramite wfst on t.id=wfst.tramite_id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea ." and ((t.institucioneducativa_id is not null and le.lugar_tipo_id_distrito=". $lugarTipo[0]['lugar_tipo_id'] .") or (t.institucioneducativa_id is null and wfst.lugar_tipo_id=". $lugarTipo[0]['lugar_tipo_id'] ."))");
            }
        }elseif ($rol == 7) { //DEPARTAMENTO
            if ($evaluacion)
            {
                $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                left join institucioneducativa ie on t.institucioneducativa_id=ie.id
                left join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
                left join distrito_tipo dt on le.distrito_tipo_id=dt.id
                left join wf_solicitud_tramite wfst on t.id=wfst.tramite_id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea ." and ((t.institucioneducativa_id is not null and cast(dt.tipo as int)=". $lugarTipo[0]['lugar_tipo_id'] .") or (t.institucioneducativa_id is null and wfst.lugar_tipo_id in (select id from lugar_tipo where lugar_tipo_id=". $lugarTipo[0]['lugar_tipo_id'] ."))) and td.valor_evaluacion = (select condicion from wf_tarea_compuerta where flujo_proceso_id=". $tarea_ant ." and condicion_tarea_siguiente=". $tarea_actual . ")");
            }else{
                $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                left join institucioneducativa ie on t.institucioneducativa_id=ie.id
                left join jurisdiccion_geografica le on ie.le_juridicciongeografica_id=le.id
                left join distrito_tipo dt on le.distrito_tipo_id=dt.id
                left join wf_solicitud_tramite wfst on t.id=wfst.tramite_id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea ." and ((t.institucioneducativa_id is not null and cast(dt.tipo as int)=". $lugarTipo[0]['lugar_tipo_id'] .") or (t.institucioneducativa_id is null and wfst.lugar_tipo_id in (select id from lugar_tipo where lugar_tipo_id=". $lugarTipo[0]['lugar_tipo_id'] .")))");
            }
        }elseif ($rol == 8) { //NACIONAL
            if ($evaluacion)
            {
                $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                left join institucioneducativa ie on t.institucioneducativa_id=ie.id
                join usuario u on td.usuario_remitente_id=u.id
                join persona p on p.id=u.persona_id
                where t.flujo_tipo_id=". $flujotipo ." and t.fecha_fin is null and ". $tarea ." and td.valor_evaluacion = (select condicion from wf_tarea_compuerta where flujo_proceso_id=". $tarea_ant ." and condicion_tarea_siguiente=". $tarea_actual . ")");
            }else{
                $query = $em->getConnection()->prepare("select t.id,ie.id as cod_sie,ie.institucioneducativa,tt.tramite_tipo,t.fecha_registro,td.obs,p.nombre,case when td.flujo_proceso_id = ". $tarea_ant ." then 'ENVIADO' else 'DEVUELTO' end as estado
                from tramite t join tramite_detalle td on cast(t.tramite as int)=td.id
                join tramite_tipo tt on t.tramite_tipo=tt.id
                left join institucioneducativa ie on t.institucioneducativa_id=ie.id
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
    
    public function guardarTramiteNuevo($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$tipotramite,$varevaluacion,$idtramite,$datos,$lugarTipo_id,$idtramiteestado)
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
            $uDestinatario = $this->obtieneUsuarioDestinatario($tarea_sig_id.$id_tabla.$tabla);
        }
        $tramiteDetalle->setUsuarioDestinatario($uDestinatario);
        $em->persist($tramiteDetalle);
        $em->flush();
        $tramite->setTramite($tramiteDetalle->getId());
        $em->flush();
        $mensaje = 'El trámite se guardo correctamente';
        return $mensaje;
    }

    public function guardarTramiteRecibido($usuario,$tarea,$tabla,$id_tabla,$idtramite,$idtramiteestado)
    {

        $em = $this->getDoctrine()->getManager();

        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea);
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($usuario);
        $tramiteestado = $em->getRepository('SieAppWebBundle:TramiteEstado')->find($tramiteestado);
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
        $mensaje = 'El trámite se guardo correctamente';
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
        $flujoprocesoSiguiente = $em->getRepository('SieAppWebBundle:FlujoProceso')->find($tarea_sig_id);
        $nivel = $flujoprocesoSiguiente->getRolTipo()->getLugarNivelTipo();
        switch ($tabla) {
            case 'institucioneducativa':
                if ($id_tabla){
                    $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id_tabla);
                    switch ($nivel->getId()) {
                        case 7:   // Distrito
                            $lugar_tipo_distrito = $institucioneducativa->getLeJuridicciongeografica()->getLugarTipoDistritoId();
                            $uDestinatario = $em->getRepository('SieAppWebBundle:UsuarioFlujoProceso')->findBy(array('flujoProceso'=>$flujoprocesoSiguiente->getId(),'lugarTipoId'=>$lugar_tipo_distrito));
                            break;
                        case 6:   // Departamento
                            $lugar_tipo_departamento = $institucioneducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugraTipo()->getId();
                            $uDestinatario = $em->getRepository('SieAppWebBundle:UsuarioFlujoProceso')->findBy(array('flujoProceso'=>$flujoprocesoSiguiente->getId(),'lugarTipoId'=>$lugar_tipo_departamento));
                            break;
                        case 0:
                            if($flujoprocesoSiguiente->getRolTipo()->getId() == 9){
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
        return $uDestinatario[0]->getUsuario();
    }

    public function verFlujoRueAction(Request $request)
    {
        //dump($request);die;

        $proceso = $request->get('proceso');
        $tramite = $request->get('tramite');
        //dump($id);die;
        $data = $this->listarF($proceso,$tramite);
        //dump($data);die;
        return $this->render('SieHerramientaBundle:TramiteRue:flujo.html.twig',$data);
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

    public function provinciasAction($idDepartamento){
    	$em = $this->getDoctrine()->getManager();
    	$prov = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $idDepartamento));
    	$provincia = array();
    	foreach($prov as $p){
            if($p->getLugar() != "NO EXISTE EN CNPV 2001"){
                $provincia[$p->getid()] = $p->getlugar();
            }
        }
        
        /**
         * distitos
         */
        $query = $em->createQuery(
            'SELECT dt
               FROM SieAppWebBundle:DistritoTipo dt
              WHERE dt.id NOT IN (:ids)
                AND dt.departamentoTipo = :dpto
           ORDER BY dt.id')
            ->setParameter('ids', array(1000,2000,3000,4000,5000,6000,7000,8000,9000))
            ->setParameter('dpto', $idDepartamento);
            $distrito = $query->getResult();
            $distritoArray = array();
            foreach($distrito as $c){
                $distritoArray[$c->getId()] = $c->getDistrito();
            }

    	$response = new JsonResponse();
    	return $response->setData(array('provincia' => $provincia, 'distrito' => $distritoArray));
    }

    public function municipiosAction($idProvincia){
    	$em = $this->getDoctrine()->getManager();
    	$mun = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 3, 'lugarTipo' => $idProvincia));
    	$municipio = array();
    	foreach($mun as $m){
            if($m->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $municipio[$m->getid()] = $m->getlugar();
            }
    	}
    	$response = new JsonResponse();
    	return $response->setData(array('municipio' => $municipio));
    }

    public function cantonesAction($idMunicipio){
    	$em = $this->getDoctrine()->getManager();
    	$can = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 4, 'lugarTipo' => $idMunicipio));
    	$canton = array();
    	foreach($can as $c){
            if($c->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $canton[$c->getid()] = $c->getlugar();
            }
    	}
    	$response = new JsonResponse();
    	return $response->setData(array('canton' => $canton));
    }

    public function localidadesAction($idCanton){
    	$em = $this->getDoctrine()->getManager();
    	$loc = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 5, 'lugarTipo' => $idCanton));
    	$localidad = array();
    	foreach($loc as $l) {
            if($l->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $localidad[$l->getid()] = $l->getlugar();
            }
    	}
    	$response = new JsonResponse();
    	return $response->setData(array('localidad' => $localidad));
    }

    
}