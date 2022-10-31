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
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\JurisdiccionGeografica;
use Sie\AppWebBundle\Entity\InstitucioneducativaNivelAutorizado;
use Sie\AppWebBundle\Entity\InstitucioneducativaAreaEspecialAutorizado;



/**
 * FlujoTipo controller.
 *
 */
class TramiteRueController extends Controller
{
    public $session;
    public $iddep;
    public $idprov;
    public $idmun;
    public $idcan;
    public $idloc;
    public $iddis;
 
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
    /***
     * Formulario de recepcion distrital
     */
    public function createRecepcionForm($flujotipo,$tarea)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('tramite_rue_recepcion_distrito_guardar'))
        ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
        ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
        //->add('idlugarusuario', 'hidden', array('data' =>$idlugarusuario ))
       ->add('tipoeducacion','entity',array('label'=>'Tipo de educación:','required'=>true,'attr'=>array('class'=>'form-control'),'class'=>'SieAppWebBundle:InstitucioneducativaTipo','query_builder'=>function(EntityRepository $e){
            return $e->createQueryBuilder('e')->where('e.id not in (:id)')->setParameter('id',array(0,3,7,8,9,10))->orderBy('e.id','ASC');},'property'=>'descripcion','empty_value' => 'Seleccione tipo de educación'))
        ->add('tramitetipo','entity',array('label'=>'Tipo de trámite:','required'=>true,'attr'=>array('class'=>'form-control'),'class'=>'SieAppWebBundle:TramiteTipo','query_builder'=>function(EntityRepository $tr){
            return $tr->createQueryBuilder('tr')->where('tr.obs = :rue')->setParameter('rue','RUE')->orderBy('tr.tramiteTipo','ASC');},'property'=>'tramiteTipo','empty_value' => 'Seleccione trámite'))
        ->add('idrue','text',array('label'=>'Código RUE:','required'=>false,'attr'=>array('placeholder'=>'Introduzca código RUE')))
        ->add('dependenciaTipo','entity',array('label'=>'Dependencia:','required'=>true,'attr'=>array('class'=>'form-control'),'class'=>'SieAppWebBundle:DependenciaTipo','query_builder'=>function(EntityRepository $dt){
            return $dt->createQueryBuilder('dt')->where('dt.id in (:id)')->setParameter('id',array(1,2,3))->orderBy('dt.id','ASC');},'property'=>'dependencia','empty_value' => 'Seleccione dependencia'))
        /*->add('turnotipo','entity',array('label'=>'Turnos','required'=>true,'class'=>'SieAppWebBundle:TurnoTipo','query_builder'=>function(EntityRepository $t){
            return $t->createQueryBuilder('t')->where('t.id not in (:id)')->setParameter('id',array(0))->orderBy('t.id','ASC');},'property'=>'turno','empty_value' => 'Seleccione turno'))*/
        ->add('institucionEducativa', 'text', array('label' => 'Nombre de la Unidad Educativa:','required' => true, 'attr' => array('class' => 'form-control', 'maxlength' => '70', 'style' => 'text-transform:uppercase')))
        ->add('requisitos','choice',array('label'=>'Requisitos:','required'=>false, 'multiple' => true,'expanded' => true,'choices'=>array('Legal' => 'Legal','Administrativo' => 'Administrativo','Técnico pedagógico' => 'Técnico pedagógico','Infraestructura'=>'Infraestructura')))
        ->add('observacion','textarea',array('label'=>'Observación:','required'=>false,'attr'=>array('class'=>'form-control','style' => 'text-transform:uppercase')))
        ->add('buscar','button',array('label'=>'Buscar'))
        ->add('guardar','submit',array('label'=>'Enviar Solicitud'))
        ->getForm();
        return $form;
    }
        
    public function recepcionDistritoNuevoAction(Request $request,$id)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $id_usuario = $this->session->get('userId');
        $idlugarusuario = $this->session->get('roluserlugarid');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();

        $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo'=>$id,'orden'=>1));
        //dump($flujoproceso);die;
        $flujotipo = $flujoproceso->getFlujoTipo()->getId();
        $tarea = $flujoproceso->getId();
        $recepcionForm = $this->createRecepcionForm($flujotipo,$tarea); 
        //dump($recepcionForm);die;
        //return $this->render('SieProcesosBundle:FlujoProceso:index1.html.twig');
        return $this->render('SieProcesosBundle:TramiteRue:recepcionDistritoNuevo.html.twig', array(
            'form' => $recepcionForm->createView(),
        ));
    }

    public function recepcionDistritoGuardarAction(Request $request)
    {

        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form);die;
        $datos=array();
        $datos['tramitetipo']=$form['tramitetipo'];
        $datos['idrue']=$form['idrue'];
        $datos['institucionEducativa']=$form['institucionEducativa'];
        $datos['tipoeducacion']=$form['tipoeducacion'];
        $datos['dependenciaTipo']=$form['dependenciaTipo'];
        $datos['observacion']=$form['observacion'];
        $datos['requisitos']=$form['requisitos'];
        $datos = json_encode($datos);
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //$usuariorol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario'=>$usuario,'rolTipo'=>$rol));            
        $idlugarusuario = $this->session->get('roluserlugarid');
        //dump($idlugarusuario);die;
        $ie_lugarlocalidad = "";
        if ($form['idrue']){
            $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['idrue']);
            $ie_lugardistrito = $ie->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
            //dump($ie_lugardistrito);die;
            $ie_lugarlocalidad = $ie->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
        }else{
            $ie_lugardistrito = $idlugarusuario;
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
            $mensaje = $this->get('wftramite')->guardarTramiteNuevo($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$tipotramite,'','',$datos,$ie_lugarlocalidad,$ie_lugardistrito);
            if($mensaje['dato']==true){
                $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje['msg']);
            }else{
                $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje['msg']);
            }
            
        }else{
            $request->getSession()
                ->getFlashBag()
                ->add('error', "La unidad educativa no es de su jurisdicción");
        }
        return $this->redirectToRoute('wf_tramite_index');
    
    }

    public function rueReporteRecepcionDistritoAction(Request $request,$idtramite,$id_td)
    {
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        if($tramite->getInstitucioneducativa()){
            $file = 'rue_recepciondistrito_v1_pv.rptdesign';    
        }else{
            $file = 'rue_recepciondistritonuevo_v1_pv.rptdesign'; 
        }
        $arch = 'FORMULARIO_'.$idtramite.'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $file . '&idtramite='.$idtramite.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;   
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
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                ->select('wfd')
                ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                ->where('td.tramite='.$tramite->getId())
                ->andWhere("wfd.esValido=true")
                ->orderBy("td.flujoProceso")
                ->getQuery()
                ->getResult();
        //dump($wfdatos[0]->getTramiteDetalle()->getFlujoProceso()->getId());die;
        /**
         * obtiene datos de los anteriores formularios
         */
        $tareasDatos = $this->obtieneDatos($wfdatos);
        //dump($tareasDatos);die;
        
        if ($tramite->getInstitucioneducativa()){
            $ie=$tramite->getInstitucioneducativa()->getInstitucioneducativa();
            $idrue = $tramite->getInstitucioneducativa()->getId();
            $tipoeducacion = $tramite->getInstitucioneducativa()->getInstitucioneducativaTipo()->getId();
            $dependenciatipo = $tramite->getInstitucioneducativa()->getDependenciaTipo()->getId();
            
        }else{
            //$wfSolicitudTramite = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->findBy(array('tramiteDetalle'=>$tramiteDetalle->getId(),'esValido'=>true));
            foreach($wfdatos as $d){
                if ($d->getTramiteDetalle()->getFlujoProceso()->getOrden()==1){
                    $datos = json_decode($d->getDatos(),true);
                    //dump($datos);die;
                    $ie = $datos['institucionEducativa'];
                    $idrue = "";
                    $tipoeducacion = $datos['tipoeducacion'];
                    $dependenciatipo = $datos['dependenciaTipo'];
                }
            }
        }
        //dump($flujoproceso);die;
        $flujotipo = $tramite->getFlujoTipo()->getId();
        $tarea = $tramiteDetalle->getFlujoProceso()->getId();
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $informeForm = $this->createInformeDistritoForm($flujotipo,$tarea,$ie,$idrue,$tipotramite,$tipoeducacion,$dependenciatipo,$id); 
        //return $this->render('SieProcesosBundle:FlujoProceso:index1.html.twig');
        return $this->render('SieProcesosBundle:TramiteRue:informeDistritoNuevo.html.twig', array(
            'form' => $informeForm->createView(),'idtramite'=>$id,'idrue'=>$idrue,'datos'=>$tareasDatos
        ));

    }

    /**
     * Formulario de informe del distrito
     */
    public function createInformeDistritoForm($flujotipo,$tarea,$ie,$idrue,$tipotramite,$tipoeducacion,$dependenciatipo,$idtramite)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_rue_informe_distrito_guardar'))
            ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
            ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
            ->add('tramite', 'hidden', array('data' =>$idtramite ))
            ->add('idrue','hidden',array('data'=>$idrue))
            ->add('informe','text',array('label'=>'Nro. de Informe:','attr' => array('data-mask'=>'0000/0000', 'placeholder'=>'0000/AAAA','maxlength' => '9')))
            ->add('fechainforme', 'text', array('label' => 'Fecha de Informe (dia-mes-año):','required' => true, 'attr' => array('placeholder' => 'dd-mm-aaaa')))
            ->add('observacion','textarea',array('label'=>'Observación:','attr'=>array('style' => 'text-transform:uppercase')))
            ->add('guardar','submit',array('label'=>'Enviar Informe'))
            ->getForm();
        return $form;
    }
 
    public function obtienelugar($idtramite)
    {
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        $lugar = array();
        if ($tramite->getInstitucioneducativa()){
            $lugar['idlugarlocalidad'] = $tramite->getInstitucioneducativa()->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
            $lugar['idlugardistrito'] = $tramite->getInstitucioneducativa()->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
            
        }else{
            $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                ->select('wfd')
                ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = td.flujoProceso')
                ->where('td.tramite='.$tramite->getId())
                ->andWhere('wfd.esValido=true')
                ->andWhere('fp.orden=1')
                ->getQuery()
                ->getResult();
            $lugar['idlugarlocalidad'] = $wfdatos[0]->getLugarTipoLocalidadId();
            $lugar['idlugardistrito'] = $wfdatos[0]->getLugarTipoDistritoId();
        }
        return $lugar;
    }
    public function informeDistritoGuardarAction(Request $request)
    {
        
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form);die;
        $datos=array();
        $datos['informe']=$form['informe'];
        $datos['fechainforme']=$form['fechainforme'];
        $datos['observacion']=$form['observacion'];
        $datos = json_encode($datos);
        //dump($datos);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $idtramite = $form['tramite'];
        $tabla = 'institucioneducativa';
        $id_tabla = $form['idrue'];
        $observacion = $form['observacion'];
        $varevaluacion = "";
        $lugar = $this->obtienelugar($idtramite);
        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
        if($mensaje['dato']== true){
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje['msg']);
        }else{
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje['msg']);
        }
        return $this->redirectToRoute('wf_tramite_index',array('tipo'=>2));
    }

    public function rueReporteInformeDistritoAction(Request $request,$idtramite,$id_td)
    {
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
            $file = 'rue_informedistrito_v1_pv.rptdesign';    
        $arch = 'FORMULARIO_'.$idtramite.'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $file . '&idtramite='.$idtramite.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;   
    }

    public function createRecepcionDepartamentalNuevoForm($flujotipo,$tarea,$idtramite,$datos)
    {
     //dump($tramitetipo);die;
        $em = $this->getDoctrine()->getManager();   
        $tipoDoc = array('adjunto'=>'Adjunto','noadjunto'=>'No Adjunto','incompleto'=>'Incompleto');
        $tipoDocInfraestructura = array('adjunto'=>'Adjunto','noadjunto'=>'No Adjunto','noaplica'=>'No Aplica');
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        
        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('tramite_rue_recepcion_departamental_guardar'))
        ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
        ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
        ->add('tramite', 'hidden', array('data' =>$idtramite ))
        ->add('turnotipo','entity',array('label'=>'Turnos','required'=>true,'class'=>'SieAppWebBundle:TurnoTipo','query_builder'=>function(EntityRepository $t){
            return $t->createQueryBuilder('t')->where('t.id not in (:id)')->setParameter('id',array(0))->orderBy('t.id','ASC');},'property'=>'turno','empty_value' => 'Seleccione turno'));
        if ($datos['tipoeducacion'] == 4) {
            $form=$form
            ->add('niveltipo','entity',array('label'=>'Niveles','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
                return $nt->createQueryBuilder('nt')->where('nt.id in (:id)')->setParameter('id',array(11,12,405))->orderBy('nt.id','ASC');},'property'=>'nivel','empty_value' => 'Seleccione turno'))
            ->add('areaEspecialTipo','entity',array('label'=>'Areas','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:EspecialAreaTipo','query_builder'=>function(EntityRepository $et){
                return $et->createQueryBuilder('et')->orderBy('et.id','ASC');},'property'=>'area_especial'));
        }
        elseif ($datos['tipoeducacion'] == 1) {
            $form=$form
            ->add('niveltipo','entity',array('label'=>'Niveles','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
                return $nt->createQueryBuilder('nt')->where('nt.id in (:id)')->setParameter('id',array(11,12,13))->orderBy('nt.id','ASC');},'property'=>'nivel'));
        }elseif($datos['tipoeducacion'] == 2){
            $form=$form
            ->add('niveltipo','entity',array('label'=>'Niveles','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
                return $nt->createQueryBuilder('nt')->where('nt.id between 200 and 210')->orWhere('nt.id between 224 and 229')->orWhere('nt.id between 232 and 299')->orderBy('nt.id','ASC');},'property'=>'nivel'));
        }elseif($datos['tipoeducacion'] == 5){
            $form=$form
            ->add('niveltipo','entity',array('label'=>'Niveles','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
                return $nt->createQueryBuilder('nt')->where('nt.id between 211 and 223')->orWhere('nt.id between 230 and 231')->orderBy('nt.id','ASC');},'property'=>'nivel'));
        }
        if($datos['tipoeducacion']!=1){
            $form = $form
            ->add('participantes', 'text', array('label' => 'Cantidad de participantes','required'=>false,'attr' => array('class' => 'form-control validar')));
        }
        $form=$form
        ->add('institucioneducativa','text',array('label'=>'Nombre del Centro Educativo:','data'=>$datos['institucionEducativa'],'required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
        ->add('lejurisdiccion', 'text', array('label' => 'Código RUE-SIE del Edificio Educativo','required'=>false,'attr' => array('class' => 'form-control validar')))
        ->add('departamento','entity',array('label'=>'Departamento','required'=>true,'attr' => array('class' => 'form-control'),'class'=>'SieAppWebBundle:LugarTipo','query_builder'=>function(EntityRepository $lt){
            return $lt->createQueryBuilder('lt')->where('lt.lugarNivel = 1')->andWhere('lt.paisTipoId=1')->orderBy('lt.id','ASC');},'property'=>'lugar','empty_value' => 'Seleccione departamento'))
    	->add('provincia', 'choice', array('label' => 'Provincia','required'=>false,'attr' => array('class' => 'form-control')))
    	->add('municipio', 'choice', array('label' => 'Municipio','required'=>false, 'attr' => array('class' => 'form-control')))
    	->add('canton', 'choice', array('label' => 'Cantón','required'=>false, 'attr' => array('class' => 'form-control')))
    	->add('localidad', 'choice', array('label' => 'Localidad/Comunidad','required'=>false,'attr' => array('class' => 'form-control')))
    	->add('zona', 'text', array('label' => 'Zona','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
    	->add('direccion', 'text', array('label' => 'Dirección','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
        ->add('distrito', 'choice', array('label' => 'Distrito Educativo','attr' => array('class' => 'form-control')))
        //informacion legal
        ->add('solicitante', 'choice', array('label' => 'La persona jurídica que hace la solicitud corresponde a:','choices'=>array(
            'Asociaciones o Fundaciones sin Fines de Lucro'=>'Asociaciones o Fundaciones sin Fines de Lucro',
            'Director de la Unidad/Centro Educativa/o'=>'Director de la Unidad/Centro Educativa/o',
            'Director Distrital de Educación a nombre del estado'=>'Director Distrital de Educación a nombre del estado',
            'Empresas Unipersonales'=>'Empresas Unipersonales',
            'Iglesia'=>'Iglesia',
            'Sociedad Anónima o Sociedad de Responsabilidad Limitada'=>'Sociedad Anónima o Sociedad de Responsabilidad Limitada',
            'Sociedades Cooperativas'=>'Sociedades Cooperativas',
            'Otro'=>'Otro'),'empty_value'=>'Seleccione solicitante','attr' => array('class' => 'form-control')))
        ->add('solicitante_otro', 'text', array('label' => 'Otro:','required'=>false,'attr' => array('class' => 'form-control')));
        if($datos['dependenciaTipo']==3){ //si es privada
            $form = $form
            ->add('iiia_acta', 'choice', array('label' => 'a) Copia Notariada del Acta de Constitución','multiple' => false,'expanded' => true,'choices'=>$tipoDoc))
            ->add('iiib_estatutos', 'choice', array('label' => 'b) Copia Notariada de los estatutos de la U.E./CEA','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiic_resolucion', 'choice', array('label' => 'c) Copia legalizada de la Resolución que otorga la personeria jurídica','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiid_fundaempresa', 'choice', array('label' => 'd) Fotocopia legalizada de la matricula y resolucion de FUNDAEMPRESA','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiie_nit', 'choice', array('label' => 'e) Fotocopia legalizada del NIT','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiif_ci', 'choice', array('label' => 'f) Fotocopia legalizada del carnet de identidad del propietario','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiig_balance', 'choice', array('label' => 'g) Balance de Apertura Sellado por el Servicio de Impuestos Nacionales','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiih_ultimobalance', 'choice', array('label' => 'h) Último Balance Sellado por el Servicio de Impuestos Nacionales','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiii_acreditacion', 'choice', array('label' => 'i) Acreditación del representante legal','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiij_padron', 'choice', array('label' => 'j) Fotocopia legalizada del Padrón de funcionamiento municipal','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiik_funcionamientoculto', 'choice', array('label' => 'k) Resolución de funcionamiento del Ministerio de Relaciones Exteriores y Culto','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiil_nombrerepresentante', 'text', array('label' => 'l) Nombre del representante legal:','attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
            ->add('iiim_direccionrepresentante', 'text', array('label' => 'm) Dirección del representante legal:','attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')));
        }
        $form = $form
        ->add('iv_areaterreno', 'text', array('label' => 'Área total del terreno en m','attr' => array('class' => 'form-control validar')))
        ->add('iv_areaconstruida', 'text', array('label' => 'Área construida en m','attr' => array('class' => 'form-control validar')))
        ->add('iv_areaesparcimiento', 'text', array('label' => 'Área de esparcimiento en m','attr' => array('class' => 'form-control validar')))
        ->add('iv_areanoconstruida', 'text', array('label' => 'Área no construida en m','attr' => array('class' => 'form-control validar')))
        ->add('iv_areacultivo', 'text', array('label' => 'Área de cultivo (Área libre) en m','attr' => array('class' => 'form-control validar')))
        ->add('iv_edificioes', 'choice', array('label' => 'El edificio es:','choices'=>array('PROPIO'=>'PROPIO','ALQUILADO'=>'ALQUILADO','ANTICRETICO'=>'ANTICREDITO','MUNICIPAL'=>'MUNICIPAL'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('va_folioreal', 'choice', array('label' => 'a) Copia legalizada del Testimonio del derecho propietario o Folio Real que acredite que los ambientes destinados a la unidad educativa pertenecen a la o el propietario o a las o los propietarios.','multiple' => false,'expanded' => true,'choices'=>$tipoDocInfraestructura))
        ->add('vb_copiacontratoalquiler', 'choice', array('label' => 'b) En caso de ser infraestructura arrendada, copia notariada de contrato de arrendamiento de alquiler o antícresis por un plazo no menor a seis años.','multiple' => false,'expanded' => true,'choices'=>$tipoDocInfraestructura,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('vc_planos', 'choice', array('label' => 'c) Planos arquitectónicos firmados por profesional competente (especificando la distribución de los ambientes) aprobados por el Gobierno Municipal para infraestructura o ambientes propios y arrendados que contemplen la adecuación progresiva para la atención a la población con discapacidad leve, acorde a los parámetros establecidos por normativa vigente.','multiple' => false,'expanded' => true,'choices'=>$tipoDocInfraestructura,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('vd_detallemobiliario', 'choice', array('label' => 'd) Detalle y características del mobiliario y equipamiento de acuerdo a las normas establecidas por el Ministerio de Educación.','multiple' => false,'expanded' => true,'choices'=>$tipoDocInfraestructura,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('ve_tallereslaboratorios', 'choice', array('label' => 'e) Las Unidades Educativas que oferten Educación Secundaria Comunitaria Productiva, deben contar con talleres, laboratorios y otros espacios productivos con características de construcción que se encuentren bajo normativas nacionales e internacionales de acuerdo a la implementación del Bachillerato Técnico Humanístico.','multiple' => false,'expanded' => true,'choices'=>$tipoDocInfraestructura,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('vi_modalidadatencion', 'choice', array('label' => 'a) Modalidad de atención:','choices'=>array('PRESENCIAL'=>'PRESENCIAL','SEMIPRESENCIAL'=>'SEMIPRESENCIAL'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('vi_modalidadlengua', 'text', array('label' => 'b) Modalidad de lengua:','attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
        ->add('vi_modalidadaprendizaje', 'text', array('label' => 'c) Modalidad de aprendizaje:','attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
        ->add('vi_modalidaddocencia', 'choice', array('label' => 'd) Modalidad de Docencia:','choices'=>array('UNIDOCENTE'=>'UNIDOCENTE'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('via_teorico', 'choice', array('label' => 'a) Fundamentos Teóricos generales:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vib_intercultural', 'choice', array('label' => 'b) Enfoque intercultural:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vic_propuestalengua', 'choice', array('label' => 'c) Propuesta de modalidad de lengua:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vid_propuestaciclos', 'choice', array('label' => 'd) Propuesta de los niveles y ciclos ofertados:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vie_propuestaintegracion', 'choice', array('label' => 'e) Propuesta de integración educativa:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vif_denominacionnivel', 'choice', array('label' => 'f) Denominación de los niveles y ciclos ofertados:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vig_organizacionciclos', 'choice', array('label' => 'g) Organización de los niveles y ciclos:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vih_temastransversales', 'choice', array('label' => 'h) Incorporación de temas transversales:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vii_areascurriculares', 'choice', array('label' => 'i) Enfoque de las áreas curriculares:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vij_competencias', 'choice', array('label' => 'j) Planteamiento de competencias y objetivos:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vik_indicadores', 'choice', array('label' => 'k) Planteamiento de indicadores:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vil_contenidos', 'choice', array('label' => 'l) Organización de contenidos:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vim_metodologia', 'choice', array('label' => 'm) Propuesta metodológica:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vin_evaluacion', 'choice', array('label' => 'n) Propuesta de evaluación:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vio_horario', 'choice', array('label' => 'o) Organización del horario:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vip_calendario', 'choice', array('label' => 'p) Organización del calendario:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('viq_textos', 'choice', array('label' => 'q) Textos o módulos propuestos para el uso con alumnos / participantes:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vir_curriculo', 'choice', array('label' => 'r) Propuesta de gestión curricular:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vis_areasnoincluidas', 'choice', array('label' => 's) Pertinencia de nuevas áreas no incluidas en el tronco comun:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('via_teoricoEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vib_interculturalEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vic_propuestalenguaEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vid_propuestaciclosEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vie_propuestaintegracionEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vif_denominacionnivelEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vig_organizacionciclosEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vih_temastransversalesEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vii_areascurricularesEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vij_competenciasEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vik_indicadoresEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vil_contenidosEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vim_metodologiaEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vin_evaluacionEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vio_horarioEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vip_calendarioEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('viq_textosEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vir_curriculoEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vis_areasnoincluidasEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vi_planesprogramas', 'choice', array('label' => 'PLANES Y PROGRAMAS:','choices'=>array('APROBADOS'=>'APROBADOS','NO APROBADOS'=>'NO APROBADOS','NO CORRESPONDE SU PRESENTACION'=>'NO CORRESPONDE SU PRESENTACION'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('observacion', 'textarea', array('label' => 'Observación:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
        ->add('guardar','submit',array('label'=>'Enviar Formulario'))
        ->getForm();
        //dump($form);die;
        return $form;
    }

    public function createRecepcionDepartamentalModificacionForm($flujotipo,$tarea,$idtramite,$institucioneducativa)
    {
        //dump($tramitetipo);die;
        $tipoDoc = array('adjunto'=>'Adjunto','noadjunto'=>'No Adjunto','incompleto'=>'Incompleto');
        $tipoDocInfraestructura = array('adjunto'=>'Adjunto','noadjunto'=>'No Adjunto','noaplica'=>'No Aplica');
        $em = $this->getDoctrine()->getManager();
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('tramite_rue_recepcion_departamental_guardar'))
        ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
        ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
        ->add('tramite', 'hidden', array('data' =>$idtramite ))
        ->add('turnotipo','entity',array('label'=>'Turnos:','required'=>true,'attr' => array('class' => 'form-control'),'class'=>'SieAppWebBundle:TurnoTipo','query_builder'=>function(EntityRepository $t){
            return $t->createQueryBuilder('t')->where('t.id not in (:id)')->setParameter('id',array(0))->orderBy('t.id','ASC');},'property'=>'turno','empty_value' => 'Seleccione turno'));
        //informacion legal
        if($institucioneducativa->getInstitucioneducativaTipo()->getId()!=1){
            $form = $form
            ->add('participantes', 'text', array('label' => 'Cantidad de participantes','required'=>false,'attr' => array('class' => 'form-control validar')));
        }
        $form = $form
        ->add('solicitante', 'choice', array('label' => 'La persona jurídica que hace la solicitud corresponde a:','choices'=>array(
            'Asociaciones o Fundaciones sin Fines de Lucro'=>'Asociaciones o Fundaciones sin Fines de Lucro',
            'Director de la Unidad/Centro Educativa/o'=>'Director de la Unidad/Centro Educativa/o',
            'Director Distrital de Educación a nombre del estado'=>'Director Distrital de Educación a nombre del estado',
            'Empresas Unipersonales'=>'Empresas Unipersonales',
            'Iglesia'=>'Iglesia',
            'Sociedad Anónima o Sociedad de Responsabilidad Limitada'=>'Sociedad Anónima o Sociedad de Responsabilidad Limitada',
            'Sociedades Cooperativas'=>'Sociedades Cooperativas',
            'Otro'=>'Otro'),'empty_value'=>'Seleccione solicitante','attr' => array('class' => 'form-control')))
        ->add('solicitante_otro', 'text', array('label' => 'Otro:','required'=>false,'attr' => array('class' => 'form-control')));
        if($institucioneducativa->getDependenciaTipo()->getId()==3){ //si es privada
            $form = $form
            ->add('iiia_acta', 'choice', array('label' => 'a) Copia Notariada del Acta de Constitución','multiple' => false,'expanded' => true,'choices'=>$tipoDoc))
            ->add('iiib_estatutos', 'choice', array('label' => 'b) Copia Notariada de los estatutos de la U.E./CEA','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiic_resolucion', 'choice', array('label' => 'c) Copia legalizada de la Resolución que otorga la personeria jurídica','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiid_fundaempresa', 'choice', array('label' => 'd) Fotocopia legalizada de la matricula y resolucion de FUNDAEMPRESA','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiie_nit', 'choice', array('label' => 'e) Fotocopia legalizada del NIT','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiif_ci', 'choice', array('label' => 'f) Fotocopia legalizada del carnet de identidad del propietario','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiig_balance', 'choice', array('label' => 'g) Balance de Apertura Sellado por el Servicio de Impuestos Nacionales','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiih_ultimobalance', 'choice', array('label' => 'h) Último Balance Sellado por el Servicio de Impuestos Nacionales','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiii_acreditacion', 'choice', array('label' => 'i) Acreditación del representante legal','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiij_padron', 'choice', array('label' => 'j) Fotocopia legalizada del Padrón de funcionamiento municipal','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiik_funcionamientoculto', 'choice', array('label' => 'k) Resolución de funcionamiento del Ministerio de Relaciones Exteriores y Culto','multiple' => false,'expanded' => true,'choices'=>$tipoDoc,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
            ->add('iiil_nombrerepresentante', 'text', array('label' => 'l) Nombre del representante legal:','attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
            ->add('iiim_direccionrepresentante', 'text', array('label' => 'm) Dirección del representante legal:','attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')));
        }
        $form = $form
        ->add('iv_areaterreno', 'text', array('label' => 'Área total del terreno en m','attr' => array('class' => 'form-control validar')))
        ->add('iv_areaconstruida', 'text', array('label' => 'Área construida en m','attr' => array('class' => 'form-control validar')))
        ->add('iv_areaesparcimiento', 'text', array('label' => 'Área de esparcimiento en m','attr' => array('class' => 'form-control validar')))
        ->add('iv_areanoconstruida', 'text', array('label' => 'Área no construida en m','attr' => array('class' => 'form-control validar')))
        ->add('iv_areacultivo', 'text', array('label' => 'Área de cultivo (Área libre) en m','attr' => array('class' => 'form-control validar')))
        ->add('iv_edificioes', 'choice', array('label' => 'El edificio es:','choices'=>array('PROPIO'=>'PROPIO','ALQUILADO'=>'ALQUILADO','ANTICRETICO'=>'ANTICREDITO','MUNICIPAL'=>'MUNICIPAL'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('va_folioreal', 'choice', array('label' => 'a) Copia legalizada del Testimonio del derecho propietario o Folio Real que acredite que los ambientes destinados a la unidad educativa pertenecen a la o el propietario o a las o los propietarios.','multiple' => false,'expanded' => true,'choices'=>$tipoDocInfraestructura))
        ->add('vb_copiacontratoalquiler', 'choice', array('label' => 'b) En caso de ser infraestructura arrendada, copia notariada de contrato de arrendamiento de alquiler o antícresis por un plazo no menor a seis años.','multiple' => false,'expanded' => true,'choices'=>$tipoDocInfraestructura,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('vc_planos', 'choice', array('label' => 'c) Planos arquitectónicos firmados por profesional competente (especificando la distribución de los ambientes) aprobados por el Gobierno Municipal para infraestructura o ambientes propios y arrendados que contemplen la adecuación progresiva para la atención a la población con discapacidad leve, acorde a los parámetros establecidos por normativa vigente.','multiple' => false,'expanded' => true,'choices'=>$tipoDocInfraestructura,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('vd_detallemobiliario', 'choice', array('label' => 'd) Detalle y características del mobiliario y equipamiento de acuerdo a las normas establecidas por el Ministerio de Educación.','multiple' => false,'expanded' => true,'choices'=>$tipoDocInfraestructura,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('ve_tallereslaboratorios', 'choice', array('label' => 'e) Las Unidades Educativas que oferten Educación Secundaria Comunitaria Productiva, deben contar con talleres, laboratorios y otros espacios productivos con características de construcción que se encuentren bajo normativas nacionales e internacionales de acuerdo a la implementación del Bachillerato Técnico Humanístico.','multiple' => false,'expanded' => true,'choices'=>$tipoDocInfraestructura,'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('vi_modalidadatencion', 'choice', array('label' => 'a) Modalidad de atención:','choices'=>array('PRESENCIAL'=>'PRESENCIAL','SEMIPRESENCIAL'=>'SEMIPRESENCIAL'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('vi_modalidadlengua', 'text', array('label' => 'b) Modalidad de lengua:','attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
        ->add('vi_modalidadaprendizaje', 'text', array('label' => 'c) Modalidad de aprendizaje:','attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
        ->add('vi_modalidaddocencia', 'choice', array('label' => 'd) Modalidad de Docencia:','choices'=>array('UNIDOCENTE'=>'UNIDOCENTE'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('via_teorico', 'choice', array('label' => 'a) Fundamentos Teóricos generales:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vib_intercultural', 'choice', array('label' => 'b) Enfoque intercultural:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vic_propuestalengua', 'choice', array('label' => 'c) Propuesta de modalidad de lengua:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vid_propuestaciclos', 'choice', array('label' => 'd) Propuesta de los niveles y ciclos ofertados:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vie_propuestaintegracion', 'choice', array('label' => 'e) Propuesta de integración educativa:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vif_denominacionnivel', 'choice', array('label' => 'f) Denominación de los niveles y ciclos ofertados:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vig_organizacionciclos', 'choice', array('label' => 'g) Organización de los niveles y ciclos:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vih_temastransversales', 'choice', array('label' => 'h) Incorporación de temas transversales:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vii_areascurriculares', 'choice', array('label' => 'i) Enfoque de las áreas curriculares:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vij_competencias', 'choice', array('label' => 'j) Planteamiento de competencias y objetivos:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vik_indicadores', 'choice', array('label' => 'k) Planteamiento de indicadores:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vil_contenidos', 'choice', array('label' => 'l) Organización de contenidos:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vim_metodologia', 'choice', array('label' => 'm) Propuesta metodológica:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vin_evaluacion', 'choice', array('label' => 'n) Propuesta de evaluación:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vio_horario', 'choice', array('label' => 'o) Organización del horario:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vip_calendario', 'choice', array('label' => 'p) Organización del calendario:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('viq_textos', 'choice', array('label' => 'q) Textos o módulos propuestos para el uso con alumnos / participantes:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vir_curriculo', 'choice', array('label' => 'r) Propuesta de gestión curricular:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('vis_areasnoincluidas', 'choice', array('label' => 's) Pertinencia de nuevas áreas no incluidas en el tronco comun:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
        ->add('via_teoricoEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vib_interculturalEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vic_propuestalenguaEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vid_propuestaciclosEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vie_propuestaintegracionEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vif_denominacionnivelEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vig_organizacionciclosEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vih_temastransversalesEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vii_areascurricularesEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vij_competenciasEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vik_indicadoresEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vil_contenidosEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vim_metodologiaEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vin_evaluacionEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vio_horarioEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vip_calendarioEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('viq_textosEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vir_curriculoEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vis_areasnoincluidasEvaluado', 'choice', array('multiple' => false,'expanded' => true,'choices'=>array('CONFORME'=>'CONFORME','NO CONFORME'=>'NO CONFORME')))
        ->add('vi_planesprogramas', 'choice', array('label' => 'PLANES Y PROGRAMAS:','choices'=>array('APROBADOS'=>'APROBADOS','NO APROBADOS'=>'NO APROBADOS','NO CORRESPONDE SU PRESENTACION'=>'NO CORRESPONDE SU PRESENTACION'),'empty_value' => 'Seleccione','attr' => array('class' => 'form-control')))
        ->add('observacion', 'textarea', array('label' => 'Observación:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
        ->add('guardar','submit',array('label'=>'Enviar Formulario'))
        ->getForm();
        //dump($form);die;
        return $form;
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
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                ->select('wfd')
                ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                ->where('td.tramite='.$tramite->getId())
                ->andWhere("wfd.esValido=true")
                ->orderBy("td.flujoProceso")
                ->getQuery()
                ->getResult();
        //dump($wfdatos);die;
        $tareasDatos = $this->obtieneDatos($wfdatos);
        //dump($tareasDatos);die;
        $flujotipo = $tramite->getFlujoTipo()->getId();
        $tarea = $tramiteDetalle->getFlujoProceso()->getId();
        if($tramite->getInstitucioneducativa()){
            $idrue = $tramite->getInstitucioneducativa()->getId();
            $ieAreaEspecial = '';
            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($idrue);
            if($institucioneducativa->getInstitucioneducativaTipo()->getId()==4){
                $ieAreaEspecial = $em->getRepository('SieAppWebBundle:InstitucioneducativaAreaEspecialAutorizado')->findBy(array('institucioneducativa'=>$idrue));
            }
            $institucioneducativaNivel = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa'=>$idrue));
            $recepcionDepartamentalForm = $this->createRecepcionDepartamentalModificacionForm($flujotipo,$tarea,$id,$institucioneducativa); 
            return $this->render('SieProcesosBundle:TramiteRue:recepcionDepartamentalModificacion.html.twig', array(
                'form' => $recepcionDepartamentalForm->createView(),
                'institucioneducativa'=>$institucioneducativa,
                'ieNivel'=>$institucioneducativaNivel,
                'ieAreaEspecial'=>$ieAreaEspecial,
                'tramite'=>$tramite,
                'datos'=>$tareasDatos,
            ));
        }else{
            $datos = json_decode($wfdatos[0]->getDatos(),true);
            //dump($datos);die;
            $recepcionDepartamentalForm = $this->createRecepcionDepartamentalNuevoForm($flujotipo,$tarea,$id,$datos); 
            return $this->render('SieProcesosBundle:TramiteRue:recepcionDepartamentalNuevo.html.twig', array(
                'form' => $recepcionDepartamentalForm->createView(),
                'idtramite'=>$id,
                'dependenciaTipo'=>$em->getRepository('SieAppWebBundle:DependenciaTipo')->find($datos['dependenciaTipo']),
                'tipoeducacion'=>$em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find($datos['tipoeducacion']),
                'tipotramite'=>$tramite->getTramitetipo()->getTramiteTipo(),
                'datos'=>$tareasDatos
            ));
        }
        //return $this->render('SieProcesosBundle:FlujoProceso:index1.html.twig');
    }

    public function recepcionDepartamentalGuardarAction(Request $request)
    {
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $idtramite = $form['tramite'];
        $tabla = 'institucioneducativa';
        $observacion = $form['observacion'];
        $varevaluacion = '';
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        
        /**
         * datos propios de la solicitud del formulario rue
         */
        unset($form['guardar'],$form['_token'],$form['flujotipo'],$form['flujoproceso'],$form['tramite']);
        if ($tramite->getInstitucioneducativa()){
            //$institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($tramite->getInstitucioneducativa()->getId());
            $query = $em->getConnection()->prepare('SELECT * 
                    FROM institucioneducativa ie
                    WHERE ie.id='. $tramite->getInstitucioneducativa()->getId());
                    $query->execute();
            $institucioneducativa = $query->fetchAll();
            $query = $em->getConnection()->prepare('SELECT ien.nivel_tipo_id
                    FROM institucioneducativa_nivel_autorizado ien
                    WHERE ien.institucioneducativa_id='. $tramite->getInstitucioneducativa()->getId());
                    $query->execute();
            $ieNivelAutorizado = $query->fetchAll();
            $query = $em->getConnection()->prepare('SELECT *
                    FROM jurisdiccion_geografica le
                    WHERE le.id='. $tramite->getInstitucioneducativa()->getLeJuridicciongeografica()->getId());
                    $query->execute();
            $le = $query->fetchAll();
            if($institucioneducativa[0]['institucioneducativa_tipo_id']==4){
                $query = $em->getConnection()->prepare('SELECT iea.especial_area_tipo_id
                    FROM institucioneducativa_area_especial_autorizado iea
                    WHERE iea.institucioneducativa_id='. $tramite->getInstitucioneducativa()->getId());
                    $query->execute();
                $ieAreasAutorizado = $query->fetchAll();
                $form['institucioneducativaAreaEspecial']=$ieAreasAutorizado;
            }
            $id_tabla = $institucioneducativa[0]['id'];
            $form['institucioneducativa']=$institucioneducativa[0];
            $form['jurisdiccion_geografica']=$le[0];
            $form['institucioneducativaNivel']=$ieNivelAutorizado;
        }else{
            $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                ->select('wfd')
                ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'with', 'fp.id = td.flujoProceso')
                ->where('td.tramite='.$tramite->getId())
                ->andWhere("wfd.esValido=true")
                ->andWhere("fp.orden=1")
                ->getQuery()
                ->getResult();
            $d = json_decode($wfdatos[0]->getDatos(),true);
            $form['tipoeducacion'] = $d['tipoeducacion'];
            $form['dependenciatipo'] = $d['dependenciaTipo'];
            $id_tabla = '';
        }
        //dump($form);die;
        $datos = json_encode($form);
        //dump($datos);die;
        $lugar = $this->obtienelugar($idtramite);
        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
        if($mensaje['dato'] == true){
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje['msg']);    
        }else{
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje['msg']);
        }
        return $this->redirectToRoute('wf_tramite_index',array('tipo'=>2));
    }

    public function createVerificaSubdireccionDepartamentalForm($flujotipo,$tarea,$idtramite)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_rue_verifica_subdireccion_departamental_guardar'))
            ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
            ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
            ->add('tramite', 'hidden', array('data' =>$idtramite ))
            ->add('observacion1','textarea',array('label'=>'Observación:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
            ->add('varevaluacion1','choice',array('label'=>'¿Observar y devolver?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('NO' => 'SI','SI' => 'NO'),'attr' => array('class' => 'form-control')))
            ->add('observacion2','textarea',array('label'=>'Observación:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
            ->add('varevaluacion2','choice',array('label'=>'¿Informe Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
            ->add('informesubdireccion','text',array('label'=>'Nro. Informe:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase','placeholder'=>'0000/AAAA')))
            ->add('fechainformesubdireccion', 'text', array('label'=>'Fecha de Informe (dia-mes-año):','required'=>false,'attr' => array('class' => 'form-control','placeholder'=>'dd-mm-aaaa')))
            ->add('guardar','submit',array('label'=>'Enviar Informe'))
            ->getForm();
        return $form;
    }

    public function verificaSubdireccionDepartamentalNuevoAction(Request $request,$id)
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
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                ->select('wfd')
                ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                ->where('td.tramite='.$tramite->getId())
                ->andWhere("wfd.esValido=true")
                ->orderBy("td.flujoProceso")
                ->getQuery()
                ->getResult();
        /**
         * obtiene datos de los anteriores formularios
         */
        $tareasDatos = $this->obtieneDatos($wfdatos);
        $flujotipo = $tramite->getFlujoTipo()->getId();
        $tarea = $tramiteDetalle->getFlujoProceso()->getId();
        
        if ($tramite->getInstitucioneducativa()){
            
            
        }else{
            
        }
        $verificasubdireccionDepartamentalForm = $this->createVerificaSubdireccionDepartamentalForm($flujotipo,$tarea,$id); 
        return $this->render('SieProcesosBundle:TramiteRue:verificaSubdireccionDepartamentalNuevo.html.twig', array(
            'form' => $verificasubdireccionDepartamentalForm->createView(),
            'idtramite'=>$id,
            'datos'=>$tareasDatos
        ));

    }

    public function verificaSubdireccionDepartamentalGuardarAction(Request $request)
    {
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $idtramite = $form['tramite'];
        $tabla = 'institucioneducativa';
        $varevaluacion1 = $form['varevaluacion1'];
        $observacion1 = $form['observacion1'];
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        /**
         * datos propios de la solicitud de la tarea verifica subdireccion
         */
        unset($form['guardar'],$form['_token'],$form['flujotipo'],$form['flujoproceso'],$form['tramite']);
        if ($tramite->getInstitucioneducativa()){
            $id_tabla = $tramite->getInstitucioneducativa()->getId();
        }else{
            $id_tabla = '';
        }
        //dump($form);die;
        $datos = json_encode($form);
        $lugar = $this->obtienelugar($idtramite);
        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion1,$varevaluacion1,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
        if($mensaje['dato'] == true){
            if($varevaluacion1=="SI"){
                $wfTareaCompuerta = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$tarea,'condicion'=>'SI'));
                $varevaluacion2 = $form['varevaluacion2'];
                $observacion2 = $form['observacion2'];
                $tarea = $wfTareaCompuerta[0]->getCondicionTareaSiguiente();
                $mensaje = $this->get('wftramite')->guardarTramiteRecibido($usuario,$tarea,$idtramite);
                if($mensaje['dato'] == true){
                    $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion2,$varevaluacion2,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
                    if($mensaje['dato'] == true){
                        $request->getSession()
                                ->getFlashBag()
                                ->add('exito', $mensaje['msg']);            
                    }else{
                        //eliminar tramite recibido
                        $a = $this->get('wftramite')->eliminarTramiteRecibido($idtramite);
                        //eliminar tramite enviado
                        $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
                        $request->getSession()
                                ->getFlashBag()
                                ->add('error', $mensaje['msg']);
                    }
                }else{
                    //eliminar tramite enviado
                    $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
                    $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje['msg']);            
                }
            }else{
                $request->getSession()
                    ->getFlashBag()
                    ->add('exito', $mensaje['msg']);        
            }
        }else{
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje['msg']);
        }
        return $this->redirectToRoute('wf_tramite_index',array('tipo'=>2));
    }

    public function createVerificaJuridicaDepartamentalForm($flujotipo,$tarea,$idtramite)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_rue_verifica_juridica_departamental_guardar'))
            ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
            ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
            ->add('tramite', 'hidden', array('data' =>$idtramite ))
            ->add('observacion1','textarea',array('label'=>'Observación:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
            ->add('varevaluacion1','choice',array('label'=>'¿Observar y devolver?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('NO' => 'SI','SI' => 'NO'),'attr' => array('class' => 'form-control')))
            ->add('observacion2','textarea',array('label'=>'Observación:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
            ->add('varevaluacion2','choice',array('label'=>'¿Informe Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
            ->add('informejuridica','text',array('label'=>'Nro. Informe:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
            ->add('fechainformejuridica', 'text', array('label'=>'Fecha de Informe:','required'=>false,'attr' => array('class' => 'form-control')))
            ->add('resolucion','text',array('label'=>'Nro. de Resolución:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
            ->add('fecharesolucion', 'text', array('label'=>'Fecha de Resolución:','required'=>false,'attr' => array('class' => 'form-control')))
            ->add('guardar','submit',array('label'=>'Enviar Informe'))
            ->getForm();
        return $form;
    }
    
    public function verificaJuridicaDepartamentalNuevoAction(Request $request,$id)
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
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                ->select('wfd')
                ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                ->where('td.tramite='.$tramite->getId())
                ->andWhere("wfd.esValido=true")
                ->orderBy("td.flujoProceso")
                ->getQuery()
                ->getResult();
        //dump($wfdatos);die;
        /**
         * obtiene datos de los anteriores formularios
         */
        $tareasDatos = $this->obtieneDatos($wfdatos);
        $flujotipo = $tramite->getFlujoTipo()->getId();
        $tarea = $tramiteDetalle->getFlujoProceso()->getId();
        if ($tramite->getInstitucioneducativa()){
            
            
        }else{
            
        }
        $verificaJuridicaDepartamentalForm = $this->createVerificaJuridicaDepartamentalForm($flujotipo,$tarea,$id); 
        return $this->render('SieProcesosBundle:TramiteRue:verificajURIDICADepartamentalNuevo.html.twig', array(
            'form' => $verificaJuridicaDepartamentalForm->createView(),
            'idtramite'=>$id,
            'datos'=>$tareasDatos
        ));

    }

    public function verificaJuridicaDepartamentalGuardarAction(Request $request)
    {
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $idtramite = $form['tramite'];
        $tabla = 'institucioneducativa';
        $varevaluacion1 = $form['varevaluacion1'];
        $observacion1 = $form['observacion1'];
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        /**
         * datos propios de la solicitud de la tarea verifica subdireccion
         */
        unset($form['guardar'],$form['_token'],$form['flujotipo'],$form['flujoproceso'],$form['tramite']);
        if ($tramite->getInstitucioneducativa()){
            $id_tabla = $tramite->getInstitucioneducativa()->getId();
        }else{
            $id_tabla = '';
        }
        $datos = json_encode($form);
        $lugar = $this->obtienelugar($idtramite);
        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion1,$varevaluacion1,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
        if($mensaje['dato'] == true){
            if($varevaluacion1=="SI"){
                $wfTareaCompuerta = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$tarea,'condicion'=>'SI'));
                $tarea = $wfTareaCompuerta[0]->getCondicionTareaSiguiente();
                $varevaluacion2 = $form['varevaluacion2'];
                $observacion2 = $form['observacion2'];
                $mensaje = $this->get('wftramite')->guardarTramiteRecibido($usuario,$tarea,$idtramite);
                if($mensaje['dato'] == true){
                    $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion2,$varevaluacion2,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
                    if($mensaje['dato'] == true){
                        if($varevaluacion2 == "SI"){
                            $wfTareaCompuerta = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$tarea,'condicion'=>'SI'));
                            $tarea = $wfTareaCompuerta[0]->getCondicionTareaSiguiente();
                            $varevaluacion = "";
                            $observacion = $observacion2;
                            $mensaje = $this->get('wftramite')->guardarTramiteRecibido($usuario,$tarea,$idtramite);
                            if($mensaje['dato'] == true){
                                $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
                                if ($mensaje['dato'] == true) {
                                    $request->getSession()
                                            ->getFlashBag()
                                            ->add('exito', $mensaje['msg']);
                                }else{
                                    //eliminar tramite recibido
                                    $a = $this->get('wftramite')->eliminarTramiteRecibido($idtramite);
                                    //eliminar tramite enviado
                                    $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
                                    //eliminar tramite recibido
                                    $a = $this->get('wftramite')->eliminarTramiteRecibido($idtramite);
                                    //eliminar tramite enviado
                                    $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
                                    $request->getSession()
                                            ->getFlashBag()
                                            ->add('error', $mensaje['msg']);    
                                }                  
                            }else{
                                //eliminar tramite enviado
                                $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
                                //eliminar tramite recibido
                                $a = $this->get('wftramite')->eliminarTramiteRecibido($idtramite);
                                //eliminar tramite enviado
                                $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
                                $request->getSession()
                                        ->getFlashBag()
                                        ->add('error', $mensaje['msg']);
                            }
                            
                        }else{
                            $request->getSession()
                                    ->getFlashBag()
                                    ->add('exito', $mensaje['msg']);
                        }
                    }else{
                        //eliminar tramite recibido
                        $a = $this->get('wftramite')->eliminarTramiteRecibido($idtramite);
                        //eliminar tramite enviado
                        $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
                        $request->getSession()
                                ->getFlashBag()
                                ->add('error', $mensaje['msg']);
                    }
                }else{
                    //eliminar tramite enviado
                    $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
                    $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje['msg']);  
                }
            }else{
                $request->getSession()
                        ->getFlashBag()
                        ->add('exito', $mensaje['msg']);
            }
        }else{
            $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje['msg']);
        }
        return $this->redirectToRoute('wf_tramite_index',array('tipo'=>2));
    }

    public function obtieneLoalizacion($iddep,$idprov,$idmun,$idcan,$idloc)
    {
        $em = $this->getDoctrine()->getManager();
        
        $ltd = $em->getRepository('SieAppWebBundle:LugarTipo')->find($iddep);
        $lt4 = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel'=>1,'paisTipoId'=>1));
        $lt3 = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel'=>2,'lugarTipo'=>$iddep));
        $lt2 = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel'=>3,'lugarTipo'=>$idprov));
        $lt1 = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel'=>4,'lugarTipo'=>$idmun));
        $lt = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel'=>5,'lugarTipo'=>$idcan));
        $dt = $em->getRepository('SieAppWebBundle:DistritoTipo')->findBy(array('departamentoTipo'=>$ltd->getCodigo()));
        $dep = $em->getRepository('SieAppWebBundle:DependenciaTipo')->createQueryBuilder('dt')
                ->select('dt')
                ->where('dt.id in (:id)')
                ->setParameter('id',array(1,2,3))
                ->orderBy('dt.dependencia','ASC')
                ->getQuery()
                ->getResult();
        $tie = $em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->createQueryBuilder('t')
                ->select('t')
                ->where('t.id not in (:id)')
                ->setParameter('id',array(0,3,7,8,9,10))
                ->orderBy('t.descripcion','ASC')
                ->getQuery()
                ->getResult();
        //dump($institucioneducativa);die;
        $depa =array();
        foreach($lt4 as $p){
            $depa[$p->getId()]= $p->getLugar();
        }
        $prov =array();
        foreach($lt3 as $p){
            $prov[$p->getId()]= $p->getLugar();
        }
        $mun =array();
        foreach($lt2 as $p){
            $mun[$p->getId()]= $p->getLugar();
        }
        $can =array();
        foreach($lt1 as $p){
            $can[$p->getId()]= $p->getLugar();
        }
        $loc =array();
        foreach($lt as $p){
            $loc[$p->getId()]= $p->getLugar();
        }
        $dis =array();
        //dump($dis);die;
        foreach($dt as $d){
            $dis[$d->getId()]= $d->getDistrito();
        }
        $deptipo=array();
        foreach($dep as $p){
            $deptipo[$p->getId()]= $p->getDependencia();
        }
        $tipoie =array();
        //dump($dis);die;
        foreach($tie as $t){
            $tipoie[$t->getId()]= $t->getDescripcion();
        }
        $data['dep'] = $depa;
        $data['prov'] = $prov;
        $data['mun'] = $mun;
        $data['can'] = $can;
        $data['loc'] = $loc;
        $data['distrito'] = $dis;
        $data['dependencia'] = $deptipo;
        $data['tipoeducacion'] = $tipoie;
        return $data;
        
    }
    public function createEnviaFormulariosDepartamentalForm($flujotipo,$tarea,$idtramite,$institucioneducativa,$resolucion,$tramite)
    {
        //dump($institucioneducativa);die;
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_rue_envia_formularios_departamental_guardar'))
            ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
            ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
            ->add('tramite', 'hidden', array('data' =>$idtramite ))
            ->add('formularios','choice',array('expanded'=>true,'multiple'=>true,'required'=>false,'choices'=>array('SIE' => 'Formulario SIE','INFRA' => 'Formulario INFRA','RUE' => 'Formulario RUE','LE' => 'Formulario de localización geográfica'),'attr' => array('class' => 'form-control')))
            ->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
            ->add('observacion','textarea',array('label'=>'Observación:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
            ->add('varevaluacion1','choice',array('label'=>'¿Procedente?','expanded'=>true,'multiple'=>false,'required'=>false,'choices'=>array('SI' => 'JURÍDICA','NO' => 'SUBDIRECCIÓN'),'empty_value'=>false,'attr' => array('class' => 'form-control')))
            ->add('observacion1','textarea',array('label'=>'Observación:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')));
            //FORMULARIO RUE
            if($tramite->getInstitucioneducativa()){
                $tipoeducacion = $institucioneducativa->getInstitucioneducativaTipo()->getId();
                $iddep = $institucioneducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
                $idprov = $institucioneducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
                $idmun = $institucioneducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getId();
                $idcan = $institucioneducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getId();
                $idloc = $institucioneducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
                $iddis = $institucioneducativa->getLeJuridicciongeografica()->getDistritoTipo()->getId();
                $le = $this->obtieneLoalizacion($iddep,$idprov,$idmun,$idcan,$idloc);
                $form = $form
                ->add('idrue','text',array('label'=>'Código de la unidad educativa/centro educativo:','data'=>$institucioneducativa->getId(),'required'=>false,'attr'=>array('class' => 'form-control validar','placeholder'=>'Introduzca código RUE')))
                ->add('institucioneducativa','text',array('label'=>'Nombre del Centro Educativo:','data'=>$institucioneducativa->getInstitucioneducativa(),'required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
                ->add('lejurisdiccion', 'text', array('label' => 'Código de edificio escolar:','data'=>$institucioneducativa->getLeJuridicciongeografica()->getId(),'required'=>false,'attr' => array('class' => 'form-control validar')))
                ->add('departamento','choice',array('label'=>'Departamento','required'=>false,'data'=>$iddep,'choices'=>$le['dep'],'attr' => array('class' => 'form-control')))
                ->add('provincia','choice',array('label'=>'Provincia','required'=>false,'data'=>$idprov,'choices'=>$le['prov'],'attr' => array('class' => 'form-control')))
                ->add('municipio','choice',array('label'=>'Municipio','required'=>false,'data'=>$idmun,'choices'=>$le['mun'],'attr' => array('class' => 'form-control')))
                ->add('canton','choice',array('label'=>'Cantón','required'=>false,'data'=>$idcan,'choices'=>$le['can'],'attr' => array('class' => 'form-control')))
                ->add('localidad','choice',array('label'=>'Localidad','required'=>false,'data'=>$idloc,'choices'=>$le['loc'],'attr' => array('class' => 'form-control')))
                ->add('zona', 'text', array('label' => 'Zona o barrio','data'=>$institucioneducativa->getLeJuridicciongeografica()->getZona(),'required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
                ->add('direccion', 'text', array('label' => 'Dirección','data'=>$institucioneducativa->getLeJuridicciongeografica()->getDireccion(),'required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
                ->add('distrito','choice',array('label'=>'Distrito','required'=>false,'data'=>$iddis,'choices'=>$le['distrito'],'attr' => array('class' => 'form-control')))
                ->add('dependencia','choice',array('label'=>'Dependencia','required'=>false,'data'=>$institucioneducativa->getDependenciaTipo()->getId(),'choices'=>$le['dependencia'],'attr' => array('class' => 'form-control')))
                ->add('tipoeducacion','choice',array('label'=>'Tipo de educación','required'=>false,'data'=>$institucioneducativa->getInstitucioneducativaTipo()->getId(),'choices'=>$le['tipoeducacion'],'attr' => array('class' => 'form-control')));
                
            }else{
                $em = $this->getDoctrine()->getManager();
                $tipoeducacion = $institucioneducativa['tipoeducacion'];
                $le = $this->obtieneLoalizacion($institucioneducativa['departamento'],$institucioneducativa['provincia'],$institucioneducativa['municipio'],$institucioneducativa['canton'],$institucioneducativa['localidad']);
                //sdump($prov);die;
                $form = $form
                ->add('institucioneducativa','text',array('label'=>'Nombre del Centro Educativo:','data'=>$institucioneducativa['institucioneducativa'],'required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
                ->add('lejurisdiccion', 'text', array('label' => 'Código de edificio escolar:','data'=>$institucioneducativa['lejurisdiccion'],'required'=>false,'attr' => array('class' => 'form-control validar')))
                ->add('departamento','choice',array('label'=>'Departamento:','required'=>false,'data'=>$institucioneducativa['departamento'],'choices'=>$le['dep'],'attr' => array('class' => 'form-control'),'empty_value'=>false))
                ->add('provincia','choice',array('label'=>'Provincia:','required'=>false,'data'=>$institucioneducativa['provincia'],'choices'=>$le['prov'],'attr' => array('class' => 'form-control'),'empty_value'=>false))
                ->add('municipio','choice',array('label'=>'Municipio:','required'=>false,'data'=>$institucioneducativa['municipio'],'choices'=>$le['mun'],'attr' => array('class' => 'form-control'),'empty_value'=>false))
                ->add('canton','choice',array('label'=>'Cantón:','required'=>false,'data'=>$institucioneducativa['canton'],'choices'=>$le['can'],'attr' => array('class' => 'form-control'),'empty_value'=>false))
                ->add('localidad','choice',array('label'=>'Localidad:','required'=>false,'data'=>$institucioneducativa['localidad'],'choices'=>$le['loc'],'attr' => array('class' => 'form-control'),'empty_value'=>false))
                ->add('zona', 'text', array('label' => 'Zona o barrio:','data'=>$institucioneducativa['zona'],'required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
                ->add('direccion', 'text', array('label' => 'Dirección:','data'=>$institucioneducativa['direccion'],'required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
                ->add('distrito','choice',array('label'=>'Nombre de distrito educativo:','required'=>false,'data'=>$institucioneducativa['distrito'],'choices'=>$le['distrito'],'attr' => array('class' => 'form-control'),'empty_value'=>false))
                ->add('dependencia','choice',array('label'=>'Dependencia:','required'=>false,'data'=>$institucioneducativa['dependenciatipo'],'choices'=>$le['dependencia'],'attr' => array('class' => 'form-control'),'empty_value'=>false))
                ->add('tipoeducacion','choice',array('label'=>'Tipo de educación:','required'=>false,'data'=>$institucioneducativa['tipoeducacion'],'choices'=>$le['tipoeducacion'],'attr' => array('class' => 'form-control'),'empty_value'=>false))
                ->add('director','text',array('label'=>'Nombre Director o Director encargado de la U.E./Centro:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
                ->add('ci', 'text', array('label' => 'C.I. Director o Director encargado:','required'=>false,'attr' => array('class' => 'form-control validar')))
                ->add('tienecargo', 'choice', array('label' => 'Tiene cargo:','required'=>false, 'attr' => array('class' => 'form-control'),'choices'=>array('SI'=>'SI','NO'=>'NO'),'multiple' => false,'expanded' => true,'empty_value'=>false))
                ->add('telefono', 'text', array('label' => 'Telefono propio de la U.E./Centro:','required'=>false,'attr' => array('class' => 'form-control validar')))
                ->add('fax', 'text', array('label' => 'Fax de la U.E.:','required'=>false,'attr' => array('class' => 'form-control validar')))
                ->add('otrotelefono', 'text', array('label' => 'Otro telefono para comunicarse con la U.E./Centro:','required'=>false,'attr' => array('class' => 'form-control validar')))
                ->add('otropertenece','text',array('label'=>'Pertenece a (cargo o relación):','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
                ->add('email','email',array('label'=>'Correo electrónico de la U.E./Centro:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:lowercase')))
                ->add('casilla', 'text', array('label' => 'Casilla postal de la U.E./Centro:','required'=>false,'attr' => array('class' => 'form-control validar')));
            }
            $form = $form            
            ->add('resolucion','text',array('label'=>'Número de resolución administrativa:','data'=>$resolucion['resolucion'],'required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
            ->add('fecharesolucion', 'text', array('label'=>'Fecha de resolución administrativa:','data'=>$resolucion['fecharesolucion'],'required'=>false,'attr' => array('class' => 'form-control')))
            ->add('tipobachillerato', 'choice', array('label' => 'Tipo de bachillerato:','required'=>false, 'attr' => array('class' => 'form-control'),'choices'=>array('HUMANÍSTICO'=>'HUMANÍSTICO','BTH'=>'BTH'),'multiple' => false,'expanded' => true,'empty_value'=>false));
            if ($tipoeducacion == 4) {
                $form=$form
                ->add('niveltipo','entity',array('label'=>'Niveles','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
                    return $nt->createQueryBuilder('nt')->where('nt.id in (:id)')->setParameter('id',array(6,11,12,405))->orderBy('nt.id','ASC');},'property'=>'nivel','empty_value' => 'Seleccione turno'))
                ->add('areaEspecialTipo','entity',array('label'=>'Areas','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:EspecialAreaTipo','query_builder'=>function(EntityRepository $et){
                    return $et->createQueryBuilder('et')->orderBy('et.id','ASC');},'property'=>'area_especial'));
            }
            elseif ($tipoeducacion == 1) {
                $form=$form
                ->add('niveltipo','entity',array('label'=>'Niveles','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
                    return $nt->createQueryBuilder('nt')->where('nt.id in (:id)')->setParameter('id',array(11,12,13))->orderBy('nt.id','ASC');},'property'=>'nivel'));
            }elseif($tipoeducacion == 2){
                $form=$form
                ->add('niveltipo','entity',array('label'=>'Niveles','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
                    return $nt->createQueryBuilder('nt')->where('nt.id between 200 and 210')->orWhere('nt.id between 224 and 229')->orWhere('nt.id between 232 and 299')->orderBy('nt.id','ASC');},'property'=>'nivel'));
            }elseif($tipoeducacion == 5){
                $form=$form
                ->add('niveltipo','entity',array('label'=>'Niveles','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
                    return $nt->createQueryBuilder('nt')->where('nt.id between 211 and 223')->orWhere('nt.id between 230 and 231')->orderBy('nt.id','ASC');},'property'=>'nivel'));
            }

            $form=$form
            ->add('guardar','submit',array('label'=>'Enviar Formularios'))
            ->getForm();
        return $form;
    }
    
    public function enviaFormulariosDepartamentalNuevoAction(Request $request,$id)
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
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        //dump($tramiteDetalle);die;
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                ->select('wfd')
                ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                ->where('td.tramite='.$tramite->getId())
                ->andWhere("wfd.esValido=true")
                ->orderBy("td.flujoProceso")
                ->getQuery()
                ->getResult();
        //dump($wfdatos);die;
        /**
         * obtiene datos de los anteriores formularios
         */
        $tareasDatos = $this->obtieneDatos($wfdatos);
        //dump($tareasDatos);die;

        $flujotipo = $tramite->getFlujoTipo()->getId();
        $tarea = $tramiteDetalle->getFlujoProceso()->getId();
        if ($tramite->getInstitucioneducativa()){
            $institucioneducativa = $tramite->getInstitucioneducativa();
            $idle = $institucioneducativa->getLeJuridicciongeografica()->getId();
            $nivel = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa'=>$institucioneducativa->getId()));
            $niveltipo=array();
            foreach($nivel as $n){
                $niveltipo[]=$n->getNivelTipo()->getId();
            }
            $tipoeducacion = $institucioneducativa->getInstitucioneducativaTipo()->getId();
            if($tipoeducacion == 4){
                $area = $em->getRepository('SieAppWebBundle:InstitucioneducativaAreaEspecialAutorizado')->findBy(array('institucioneducativa'=>$institucioneducativa->getId()));
                $areaEspecial=array();
                foreach($area as $a){
                    $areaEspecial[]=$a->getEspecialAreaTipo()->getId();
                }    
            }else{
                $areaEspecial="";
            }
        }else{
            foreach($wfdatos as $wfd){
                if ($wfd->getTramiteDetalle()->getFlujoProceso()->getId() == 41){ // datos del form unico de tramite departamental
                    $institucioneducativa = json_decode($wfd->getDatos(),true);
                }
            }
            $idle = $institucioneducativa['lejurisdiccion'];
            $niveltipo = $institucioneducativa['niveltipo'];
            
            $tipoeducacion = $institucioneducativa['tipoeducacion'];
            if($tipoeducacion==4){
                $areaEspecial = $institucioneducativa['areaEspecialTipo'];
            }else {
                $areaEspecial="";
            }
        }

        foreach($wfdatos as $wfd){
            if ($wfd->getTramiteDetalle()->getFlujoProceso()->getId() == 46){ // datos de la resolucion
                $resolucion = json_decode($wfd->getDatos(),true);
            }
        }
        if($idle){
            if ($tramite->getInstitucioneducativa()){
                $comparteLe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->createQueryBuilder('ie')
                    ->select('ie')
                    ->where('ie.leJuridicciongeografica='.$idle)
                    ->andWhere('ie.estadoinstitucionTipo=10')
                    ->andWhere('ie.institucioneducativaAcreditacionTipo=1')
                    ->andWhere('ie.id <> '. $tramite->getInstitucioneducativa()->getId())
                    ->getQuery()
                    ->getResult();
            }else{
                $comparteLe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->createQueryBuilder('ie')
                ->select('ie')
                ->where('ie.leJuridicciongeografica='.$idle)
                ->andWhere('ie.estadoinstitucionTipo=10')
                ->andWhere('ie.institucioneducativaAcreditacionTipo=1')
                ->getQuery()
                ->getResult();
            }
        }else{
            $comparteLe = "";
        }
        $enviaFormulariosDepartamentalForm = $this->createEnviaFormulariosDepartamentalForm($flujotipo,$tarea,$id,$institucioneducativa,$resolucion,$tramite);
        return $this->render('SieProcesosBundle:TramiteRue:enviaFormulariosDepartamentalNuevo.html.twig', array(
            'form' => $enviaFormulariosDepartamentalForm->createView(),
            'idtramite'=>$id,
            'datos'=>$tareasDatos,
            'comparteLe'=>$comparteLe,
            'niveltipo'=>$niveltipo,
            'areaespecial'=>$areaEspecial,
            'tipoeducacion'=>$tipoeducacion,
            'tramite'=>$tramite,
        ));
      
    }

    public function enviaFormulariosDepartamentalGuardarAction(Request $request)
    {
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $idtramite = $form['tramite'];
        $tabla = 'institucioneducativa';
        $varevaluacion = $form['varevaluacion'];
        $observacion = $form['observacion'];
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        /**
         * datos propios de la solicitud de la tarea verifica subdireccion
         */
        if($form['lejurisdiccion']){
            $comparteLe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->createQueryBuilder('ie')
                ->select('ie')
                ->where('ie.leJuridicciongeografica='.$form['lejurisdiccion'])
                ->andWhere('ie.estadoinstitucionTipo=10')
                ->andWhere('ie.institucioneducativaAcreditacionTipo=1')
                ->getQuery()
                ->getResult();

            if($comparteLe){
                $comparteArray = array();
                foreach ($comparteLe as $c){
                    $comparteArray[] = array('idrue'=>$c->getId(),'nombre'=>$c->getInstitucioneducativa());
                }
                $form['comparteLe']=$comparteArray;    
            }else{
                $form['comparteLe'] = "";    
            }
        }else{
            $form['comparteLe'] = "";
        }
        if($varevaluacion == "NO"){
            $datos['varevaluacion'] = $form['varevaluacion'];
            $datos['observacion'] = $form['observacion'];
            $datos['varevaluacion1'] = $form['varevaluacion1'];
            $datos['observacion1'] = $form['observacion1'];
            $datos = json_encode($datos);
        }else{
            unset($form['guardar'],$form['_token'],$form['flujotipo'],$form['flujoproceso'],$form['tramite']);
            $datos = json_encode($form);
        }
        if ($tramite->getInstitucioneducativa()){
            $id_tabla = $tramite->getInstitucioneducativa()->getId();
        }else{
            $id_tabla = '';
        }
        //dump($form);die;
        //dump($datos);die;
        $lugar = $this->obtienelugar($idtramite);
        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
        if($mensaje['dato'] == true){
            if($varevaluacion=="NO"){  //observar
                $wfTareaCompuerta = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$tarea,'condicion'=>'NO'));
                $tarea = $wfTareaCompuerta[0]->getCondicionTareaSiguiente();
                $varevaluacion1 = $form['varevaluacion1'];
                $observacion1 = $form['observacion1'];
                $mensaje = $this->get('wftramite')->guardarTramiteRecibido($usuario,$tarea,$idtramite);
                if($mensaje['dato'] == true){
                    $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion1,$varevaluacion1,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
                    if($mensaje['dato'] == true){
                        $request->getSession()
                                ->getFlashBag()
                                ->add('exito', $mensaje['msg']);
                    }else{
                        //eliminar tramite recibido
                        $a = $this->get('wftramite')->eliminarTramiteRecibido($idtramite);
                        //eliminar tramite enviado
                        $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
                        $request->getSession()
                                ->getFlashBag()
                                ->add('error', $mensaje['msg']);
                    }
                }else{
                    //eliminar tramite enviado
                    $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
                    $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje['msg']); 
                }
            }else{
                $request->getSession()
                        ->getFlashBag()
                        ->add('exito', $mensaje['msg']);
            }
        }else{
            $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje['msg']);
        }
        return $this->redirectToRoute('wf_tramite_index',array('tipo'=>2));
    }

    public function createAnalisisTecnicoMineduForm($flujotipo,$tarea,$idtramite,$institucioneducativa,$idrue)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT DISTINCT et.id,et.estadoinstitucion
                FROM SieAppWebBundle:EstadoinstitucionTipo et
                WHERE et.id in (:id)
                ORDER BY et.id ASC')
                ->setParameter('id', array(10,19));
        $estados = $query->getResult();
        $estadosArray = array();
        for($i=0;$i<count($estados);$i++){
            $estadosArray[$estados[$i]['id']] = $estados[$i]['estadoinstitucion'];
        }
        $query = $em->createQuery('SELECT DISTINCT ct.id,ct.convenio
                FROM SieAppWebBundle:ConvenioTipo ct
                WHERE ct.id not in (:id)
                ORDER BY ct.id ASC')
                ->setParameter('id', array(99));
        $convenios = $query->getResult();
        $conveniosArray = array();
        for($i=0;$i<count($convenios);$i++){
            $conveniosArray[$convenios[$i]['id']] = $convenios[$i]['convenio'];
        }
        if ($idrue){
            $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($idrue);
        }
        $le = $this->obtieneLoalizacion($institucioneducativa['departamento'],$institucioneducativa['provincia'],$institucioneducativa['municipio'],$institucioneducativa['canton'],$institucioneducativa['localidad']);
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_rue_analisis_tecnico_minedu_guardar'))
            ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
            ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
            ->add('tramite', 'hidden', array('data' =>$idtramite ))
            ->add('observacion','textarea',array('label'=>'Observación:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
            ->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')));
            if($idrue){
                $form = $form
                ->add('idrue', 'text', array('label' => 'Código RUE-SIE de la Unidad Educativa:','data'=>$idrue,'required'=>false,'attr' => array('class' => 'form-control validar')));
            }
            $form=$form
            ->add('institucioneducativa','text',array('label'=>'Nombre del Unidda/Centro Educativo:','data'=>$institucioneducativa['institucioneducativa'],'required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')));
            if($idrue){
                $form = $form
                ->add('desUeAntes', 'text', array('label' => 'Nombre anterior', 'data' => $ie->getDesUeAntes(), 'required' => false, 'attr' => array('class' => 'form-control',  'autocomplete' => 'off', 'style' => 'text-transform:uppercase')));
            }else{
                $form = $form
                ->add('desUeAntes', 'text', array('label' => 'Nombre anterior','required' => false, 'attr' => array('class' => 'form-control',  'autocomplete' => 'off', 'style' => 'text-transform:uppercase')));
            }
            $form = $form
            ->add('observacion1','textarea',array('label'=>'Observación:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
            ->add('lejurisdiccion', 'text', array('label' => 'Código de edificio escolar:','data'=>$institucioneducativa['lejurisdiccion'],'required'=>false,'attr' => array('class' => 'form-control validar')))
            ->add('departamento','choice',array('label'=>'Departamento:','required'=>false,'data'=>$institucioneducativa['departamento'],'choices'=>$le['dep'],'attr' => array('class' => 'form-control'),'empty_value'=>false))
            ->add('provincia','choice',array('label'=>'Provincia:','required'=>false,'data'=>$institucioneducativa['provincia'],'choices'=>$le['prov'],'attr' => array('class' => 'form-control'),'empty_value'=>false))
            ->add('municipio','choice',array('label'=>'Municipio:','required'=>false,'data'=>$institucioneducativa['municipio'],'choices'=>$le['mun'],'attr' => array('class' => 'form-control'),'empty_value'=>false))
            ->add('canton','choice',array('label'=>'Cantón:','required'=>false,'data'=>$institucioneducativa['canton'],'choices'=>$le['can'],'attr' => array('class' => 'form-control'),'empty_value'=>false))
            ->add('localidad','choice',array('label'=>'Localidad:','required'=>false,'data'=>$institucioneducativa['localidad'],'choices'=>$le['loc'],'attr' => array('class' => 'form-control'),'empty_value'=>false))
            ->add('zona', 'text', array('label' => 'Zona o barrio:','data'=>$institucioneducativa['zona'],'required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
            ->add('direccion', 'text', array('label' => 'Dirección:','data'=>$institucioneducativa['direccion'],'required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
            ->add('distrito','choice',array('label'=>'Nombre de distrito educativo:','required'=>false,'data'=>$institucioneducativa['distrito'],'choices'=>$le['distrito'],'attr' => array('class' => 'form-control'),'empty_value'=>false))
            ->add('dependencia','choice',array('label'=>'Dependencia:','required'=>false,'data'=>$institucioneducativa['dependencia'],'choices'=>$le['dependencia'],'attr' => array('class' => 'form-control'),'empty_value'=>false))
            ->add('tipoeducacion','choice',array('label'=>'Tipo de educación:','required'=>false,'data'=>$institucioneducativa['tipoeducacion'],'choices'=>$le['tipoeducacion'],'attr' => array('class' => 'form-control'),'empty_value'=>false));
            if($idrue){
                $form = $form
                ->add('convenioTipo', 'choice', array('label' => 'Convenio:','data'=>$ie->getConvenioTipo()->getId(), 'required' => false, 'choices' => $conveniosArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('estadoTipo', 'choice', array('label' => 'Estado:','data'=>$ie->getEstadoinstitucionTipo()->getId(), 'required' => false, 'choices' => $estadosArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')));
            }else{
                $form = $form
                ->add('convenioTipo', 'choice', array('label' => 'Convenio:','data'=>0, 'required' => false, 'choices' => $conveniosArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('estadoTipo', 'choice', array('label' => 'Estado:', 'required' => false, 'choices' => $estadosArray, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')));
            }
            $form = $form
            ->add('resolucion','text',array('label'=>'Número de resolución administrativa:','data'=>$institucioneducativa['resolucion'],'required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
            ->add('fecharesolucion', 'text', array('label'=>'Fecha de resolución administrativa:','data'=>$institucioneducativa['fecharesolucion'],'required'=>false,'attr' => array('class' => 'form-control')));
            if ($institucioneducativa['tipoeducacion'] == 4) {
                $form=$form
                ->add('niveltipo','entity',array('label'=>'Niveles','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
                    return $nt->createQueryBuilder('nt')->where('nt.id in (:id)')->setParameter('id',array(6,11,12,405))->orderBy('nt.id','ASC');},'property'=>'nivel','empty_value' => 'Seleccione turno'))
                ->add('areaEspecialTipo','entity',array('label'=>'Areas','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:EspecialAreaTipo','query_builder'=>function(EntityRepository $et){
                    return $et->createQueryBuilder('et')->orderBy('et.id','ASC');},'property'=>'area_especial'));
            }
            elseif ($institucioneducativa['tipoeducacion'] == 1) {
                $form=$form
                ->add('niveltipo','entity',array('label'=>'Niveles','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
                    return $nt->createQueryBuilder('nt')->where('nt.id in (:id)')->setParameter('id',array(11,12,13))->orderBy('nt.id','ASC');},'property'=>'nivel'));
            }elseif($institucioneducativa['tipoeducacion'] == 2){
                $form=$form
                ->add('niveltipo','entity',array('label'=>'Niveles','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
                    return $nt->createQueryBuilder('nt')->where('nt.id between 200 and 210')->orWhere('nt.id between 224 and 229')->orWhere('nt.id between 232 and 299')->orderBy('nt.id','ASC');},'property'=>'nivel'));
            }elseif($institucioneducativa['tipoeducacion'] == 5){
                $form=$form
                ->add('niveltipo','entity',array('label'=>'Niveles','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
                    return $nt->createQueryBuilder('nt')->where('nt.id between 211 and 223')->orWhere('nt.id between 230 and 231')->orderBy('nt.id','ASC');},'property'=>'nivel'));
            }
            $form = $form
            ->add('guardar','submit',array('label'=>'Guardar','attr' => array('class' => 'form-control')))
            ->getForm();
        return $form;
    }
    
    public function analisisTecnicoMineduNuevoAction(Request $request,$id)
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
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                ->select('wfd')
                ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                ->where('td.tramite='.$tramite->getId())
                ->andWhere("wfd.esValido=true")
                ->orderBy("td.flujoProceso")
                ->getQuery()
                ->getResult();
        foreach($wfdatos as $wfd){
            if ($wfd->getTramiteDetalle()->getFlujoProceso()->getId() == 47){ // datos de la resolucion
                $institucioneducativa = json_decode($wfd->getDatos(),true);
                $niveltipo = $institucioneducativa['niveltipo'];
                $tipoeducacion = $institucioneducativa['tipoeducacion'];
                if($tipoeducacion==4){
                    $areaEspecial = $institucioneducativa['areaEspecialTipo'];
                }else{
                    $areaEspecial = "";
                }
            }
        }
        /**
         * obtiene datos de los anteriores formularios
         */
        $tareasDatos = $this->obtieneDatos($wfdatos);
        $flujotipo = $tramite->getFlujoTipo()->getId();
        $tarea = $tramiteDetalle->getFlujoProceso()->getId();
        if ($tramite->getInstitucioneducativa()){
            $idrue = $tramite->getInstitucioneducativa()->getId();
        }else{
            $idrue = "";
        }
        $analisistecnicomineduForm = $this->createAnalisisTecnicoMineduForm($flujotipo,$tarea,$id,$institucioneducativa,$idrue); 
        return $this->render('SieProcesosBundle:TramiteRue:analisisTecnicoMineduNuevo.html.twig', array(
            'form' => $analisistecnicomineduForm->createView(),
            'idtramite'=>$id,
            'tramite'=>$tramite,
            'niveltipo'=>$niveltipo,
            'areaespecial'=>$areaEspecial,
            'tipoeducacion'=>$tipoeducacion,
            'datos'=>$tareasDatos
        ));

    }

    public function analisisTecnicoMineduGuardarAction(Request $request)
    {
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form);die;
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $idtramite = $form['tramite'];
        $tabla = 'institucioneducativa';
        $varevaluacion = $form['varevaluacion'];
        $observacion = $form['observacion'];
        $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        /**
         * datos propios de la solicitud de la tarea verifica subdireccion
         */
        unset($form['guardar'],$form['_token'],$form['flujotipo'],$form['flujoproceso'],$form['tramite']);
        if ($tramite->getInstitucioneducativa()){
            $id_tabla = $tramite->getInstitucioneducativa()->getId();
        }else{
            $id_tabla = '';
        }
        //dump($form);die;
        $datos = json_encode($form);
        $lugar = $this->obtienelugar($idtramite);
        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
        if($mensaje['dato'] == true){
            if($varevaluacion=="SI"){
                $wfTareaCompuerta = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$tarea,'condicion'=>'SI'));
                $tarea = $wfTareaCompuerta[0]->getCondicionTareaSiguiente();
                $varevaluacion = "";
                $observacion = $form['observacion1'];
                $mensaje = $this->get('wftramite')->guardarTramiteRecibido($usuario,$tarea,$idtramite);
                if($mensaje['dato'] == true){
                    $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
                    if($mensaje['dato'] == true){
                        if($form['lejurisdiccion']==""){
                            $codLe = $this->obtieneCodigoLe($form, $usuario);
                        }
                        /**
                         * Registrar en el RUE
                         */
                        if($id_tabla){
                            $em->getConnection()->beginTransaction();
                            try {
                                    $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_nivel_autorizado');")->execute();
                                    $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($id_tabla);
                                    $entity->setInstitucioneducativa(mb_strtoupper($form['institucioneducativa'], 'utf-8'));
                                    $entity->setFechaResolucion(new \DateTime($form['fecharesolucion']));
                                    $entity->setNroResolucion(mb_strtoupper($form['resolucion'], 'utf-8'));
                                    $entity->setDependenciaTipo($em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneById($form['dependencia']));
                                    $entity->setConvenioTipo(($form['convenioTipo'] != "") ? $em->getRepository('SieAppWebBundle:ConvenioTipo')->findOneById($form['convenioTipo']) : null);
                                    $entity->setEstadoinstitucionTipo($em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById($form['estadoTipo']));
                                    $entity->setInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->findOneById($form['tipoeducacion']));
                                    $entity->setObsRue(mb_strtoupper($form['observacion1'], 'utf-8'));
                                    $entity->setDesUeAntes(mb_strtoupper($form['desUeAntes'], 'utf-8'));
                                    if($form['lejurisdiccion']==""){
                                        $entity->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($codLe));    
                                    }else{
                                        $entity->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($form['lejurisdiccion']));
                                    }
                                    $em->persist($entity);
                                    $em->flush();
                                    //elimina los niveles
                                    $nivelesElim = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa' => $entity->getId()));
                                    if($nivelesElim){
                                        foreach ($nivelesElim as $nivel) {
                                            $em->remove($nivel);
                                        }
                                        $em->flush();
                                    }
                                    //adiciona niveles nuevos
                                    if ($form['tipoeducacion'] == 1 or $form['tipoeducacion'] == 2 or $form['tipoeducacion'] == 4 or $form['tipoeducacion'] == 5 or $form['tipoeducacion'] == 6) {
                                        $niveles = (isset($form['niveltipo']))?$form['niveltipo']:array();
                                        for($i=0;$i<count($niveles);$i++){
                                            $nivel = new InstitucioneducativaNivelAutorizado();
                                            $nivel->setFechaRegistro(new \DateTime('now'));
                                            $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($niveles[$i]));
                                            $nivel->setInstitucioneducativa($entity);
                                            $em->persist($nivel);
                                        }
                                        $em->flush();
                                    }
                                    if ($form['tipoeducacion'] == 4) {
                                        //elimina las areas
                                        $areasElim = $em->getRepository('SieAppWebBundle:InstitucioneducativaAreaEspecialAutorizado')->findBy(array('institucioneducativa' => $entity->getId()));
                                        if($areasElim) {
                                            foreach ($areasElim as $area) {
                                                $em->remove($area);
                                            }
                                            $em->flush();
                                        }
                                        //adiciona areas nuevas
                                        $areas = $form['areaEspecialTipo'];
                                        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_area_especial_autorizado');")->execute();
                                        for($i=0;$i<count($areas);$i++){
                                            $area = new InstitucioneducativaAreaEspecialAutorizado();
                                            $area->setFechaRegistro(new \DateTime('now'));
                                            $area->setEspecialAreaTipo($em->getRepository('SieAppWebBundle:EspecialAreaTipo')->findOneById($areas[$i]));
                                            $area->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($entity->getId()));
                                            $em->persist($area);
                                        }
                                        $em->flush();
                                    }
                                    // Try and commit the transaction
                                    $em->getConnection()->commit();
                                    $mensaje = "El trámite Nro. ". $tramite->getId() ." de ". $tramite->getTramiteTipo()->getTramiteTipo() ." para la Institución Educativa ". $entity->getInstitucioneducativa() ." fue registrada correctamente con el código RUE: ".$entity->getId().", y el código de Local educativo: ".$entity->getLeJuridicciongeografica()->getId();
                                    $request->getSession()
                                        ->getFlashBag()
                                        ->add('exito', $mensaje);
                                } catch (Exception $e) {
                                    //eliminar tramite recibido
                                    $a = $this->get('wftramite')->eliminarTramiteRecibido($idtramite);
                                    //eliminar tramite enviado
                                    $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
                                    $em->getConnection()->rollback();
                                    $mensaje = "Error al registrar la institución educativa. ";
                                    $request->getSession()
                                        ->getFlashBag()
                                        ->add('error', $mensaje);
                            }
                        }else{
                            try {
                                $em->getConnection()->beginTransaction();
                                $form = $request->get('form');
                                // Validar la ie no esta registrada
                                $buscar_institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array(
                                        'institucioneducativa' => $form['institucioneducativa'],
                                        'fechaResolucion' => new \DateTime($form['fecharesolucion']),
                                ));
                                if ($buscar_institucion) {
                                    $this->get('session')->getFlashBag()->add('registroInstitucionError', 'La institución educativa ya existe en el sistema');
                                    return $this->redirect($this->generateUrl('rue'));
                                }
            
                                $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_nivel_autorizado');")->execute();
                                $query = $em->getConnection()->prepare('SELECT get_genera_codigo_ue(:codle)');
                                if($form['lejurisdiccion']==""){
                                    $query->bindValue(':codle', $codLe);
                                }else{
                                    $query->bindValue(':codle', $form['lejurisdiccion']);
                                }
                                $query->execute();
                                $codigoue = $query->fetchAll();
                                //Registro de institucion educativa
                                $entity = new Institucioneducativa();
                                $entity->setId($codigoue[0]["get_genera_codigo_ue"]);
                                $ieducativatipo = $em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->findOneById($form['tipoeducacion']);
                                $entity->setInstitucioneducativa(mb_strtoupper($form['institucioneducativa'], 'utf-8'));
                                $entity->setFechaResolucion(new \DateTime($form['fecharesolucion']));
                                $entity->setFechaCreacion(new \DateTime('now'));
                                $entity->setNroResolucion(mb_strtoupper($form['resolucion'], 'utf-8'));
                                $entity->setDependenciaTipo($em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneById($form['dependencia']));
                                $entity->setConvenioTipo(($form['convenioTipo'] != "") ? $em->getRepository('SieAppWebBundle:ConvenioTipo')->findOneById($form['convenioTipo']) : null);
                                $entity->setEstadoinstitucionTipo($em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById($form['estadoTipo']));
                                $entity->setInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->findOneById($form['tipoeducacion']));
                                $entity->setObsRue(mb_strtoupper($form['observacion1'], 'utf-8'));
                                $entity->setDesUeAntes(mb_strtoupper($form['desUeAntes'], 'utf-8'));
                                if($form['lejurisdiccion']==""){
                                    $entity->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($codLe));    
                                }else{
                                    $entity->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($form['lejurisdiccion']));
                                }
                                $entity->setOrgcurricularTipo($ieducativatipo->getOrgcurricularTipo());
                                $entity->setInstitucioneducativaAcreditacionTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaAcreditacionTipo')->find(1));
                
                                $em->persist($entity);
                                $em->flush();
                                //adiciona niveles nuevos
                                //$niveles = $form['nivelTipo'];
                                $niveles = (isset($form['niveltipo']))?$form['niveltipo']:array();
                                for($i=0;$i<count($niveles);$i++){
                                    $nivel = new InstitucioneducativaNivelAutorizado();
                                    $nivel->setFechaRegistro(new \DateTime('now'));
                                    $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($niveles[$i]));
                                    $nivel->setInstitucioneducativa($entity);
                                    $em->persist($nivel);
                                }
                                $em->flush();
                                if ($form['tipoeducacion'] == 4) {
                                    //adiciona areas nuevas
                                    $areas = $form['areaEspecialTipo'];
                                    for($i=0;$i<count($areas);$i++){
                                        $area = new InstitucioneducativaAreaEspecialAutorizado();
                                        $area->setFechaRegistro(new \DateTime('now'));
                                        $area->setEspecialAreaTipo($em->getRepository('SieAppWebBundle:EspecialAreaTipo')->findOneById($areas[$i]));
                                        $area->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($entity->getId()));
                                        $em->persist($area);
                                    }
                                    $em->flush();
                                }
                                //actualizamos el tramite con la unidad educativa creada
                                $tramite->setInstitucioneducativa($entity);
                                $em->flush();
                                // Try and commit the transaction
                                $em->getConnection()->commit();
                                $mensaje = "El trámite Nro. " . $tramite->getId() . " de CREACIÓN para la institución educativa ". $entity->getInstitucioneducativa() ." fue registrada correctamente con el código RUE: ".$entity->getId().", y el código de Local educativo: ".$entity->getLeJuridicciongeografica()->getId();
                                $request->getSession()
                                        ->getFlashBag()
                                        ->add('exito', $mensaje);
                            } catch (Exception $ex) {
                                //eliminar tramite recibido
                                $a = $this->get('wftramite')->eliminarTramiteRecibido($idtramite);
                                //eliminar tramite enviado
                                $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
                                $em->getConnection()->rollback();
                                $mensaje = "Error al registrar la institución educativa ".$entity->getInstitucioneducativa();
                                $request->getSession()
                                        ->getFlashBag()
                                        ->add('error', $mensaje);
                            }
                        }
                    }else{
                        //eliminar tramite recibido
                        $a = $this->get('wftramite')->eliminarTramiteRecibido($idtramite);
                        //eliminar tramite enviado
                        $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
                        $request->getSession()
                                ->getFlashBag()
                                ->add('error', $mensaje['msg']);
                    }
                }else{
                    //eliminar tramite enviado
                    $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
                    $request->getSession()
                            ->getFlashBag()
                            ->add('error', $mensaje['msg']);   
                }
            }else{
                $request->getSession()
                        ->getFlashBag()
                        ->add('exito', $mensaje['msg']);
            }
        }else{
            $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje['msg']);
        }

        return $this->redirectToRoute('wf_tramite_index',array('tipo'=>2));
    }
    
    public function obtieneCodigoLe($form,$id_usuario){
        try {
            
            $em = $this->getDoctrine()->getManager();
            
    		$sec = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['municipio']);
    		$secCod = $sec->getCodigo();
    		$proCod = $sec->getLugarTipo()->getCodigo();
            $depCod = $sec->getLugarTipo()->getLugarTipo()->getCodigo();
            
    		$dis = $em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($form['distrito']);
    		$distrito = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('lugarNivel' => 7, 'codigo' => $dis->getId()));
            $query = $em->getConnection()->prepare('SELECT get_genera_codigo_le(:dep,:pro,:sec)');
            $query->bindValue(':dep', $depCod);
            $query->bindValue(':pro', $proCod);
            $query->bindValue(':sec', $secCod);
            $query->execute();
            $codigolocal = $query->fetchAll();
            // Registramos el local
    		$entity = new JurisdiccionGeografica();
            $entity->setId($codigolocal[0]["get_genera_codigo_le"]);
            $entity->setLugarTipoLocalidad($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['localidad']));
            $entity->setLugarTipoIdDistrito($distrito->getId());
            $entity->setDistritoTipo($dis);
            $entity->setValidacionGeograficaTipo($em->getRepository('SieAppWebBundle:ValidacionGeograficaTipo')->findOneById(0));
            $entity->setZona(mb_strtoupper($form['zona'], 'utf-8'));
            $entity->setDireccion(mb_strtoupper($form['direccion'], 'utf-8'));
            $entity->setJuridiccionAcreditacionTipo($em->getRepository('SieAppWebBundle:JurisdiccionGeograficaAcreditacionTipo')->find(1));
            $entity->setUsuarioId($id_usuario);
            $entity->setFechaRegistro(new \DateTime('now'));
            $em->persist($entity);
    		$em->flush();
            return $entity->getId();
    	} catch (Exception $ex) {
    		return false;
    	}
    }
    
    public function buscarRueAction(Request $request)
    {
        //dump($request);die;
        
        $idlugarusuario = $this->session->get('roluserlugarid');
        //dump($idlugarusuario);die;
        $idrue=$request->get('idrue');
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT ie
                FROM SieAppWebBundle:Institucioneducativa ie
                JOIN SieAppWebBundle:JurisdiccionGeografica le WITH ie.leJuridicciongeografica = le.id
                WHERE ie.id = :id
                AND ie.estadoinstitucionTipo = 10
                AND ie.institucioneducativaAcreditacionTipo = 1
                AND le.lugarTipoIdDistrito = :lugar_id')
                ->setParameter('id', $idrue)
                ->setParameter('lugar_id', $idlugarusuario);
        $institucioneducativa = $query->getResult();
        //$institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($idrue);
        //dump($institucioneducativa);die;
        $response = new JsonResponse();
        if($institucioneducativa){
            $ie=$institucioneducativa[0]->getInstitucioneducativa();
            $dep=$institucioneducativa[0]->getDependenciaTipo()->getId();
            $tipo=$institucioneducativa[0]->getInstitucioneducativaTipo()->getId();
            $response->setData(array('ie'=>$ie,'dep'=>$dep,'ietipo'=>$tipo));
        }else{
            $response->setData(array('msg'=>'El codigo SIE es incorrecto.'));
        }

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

    public function provinciasAction($idDepartamento){
        //dump($idDepartamento);die;
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
        $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idDepartamento);
        $query = $em->createQuery(
            'SELECT dt
               FROM SieAppWebBundle:DistritoTipo dt
              WHERE dt.id NOT IN (:ids)
                AND dt.departamentoTipo = :dpto
           ORDER BY dt.id')
            ->setParameter('ids', array(1000,2000,3000,4000,5000,6000,7000,8000,9000))
            ->setParameter('dpto', $dep->getcodigo());
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

    public function obtieneDatos($wfdatos)
    {
        $em = $this->getDoctrine()->getManager();

        foreach($wfdatos as $wfd)
        {
            $datos = json_decode($wfd->getdatos(),true);
            switch ($wfd->getTramiteDetalle()->getFlujoProceso()->getId()) {
                case 45:
                    $tarea45['idrue'] = $datos['idrue'];
                    $tarea45['institucioneducativa'] = $datos['institucionEducativa'];
                    $tarea45['tramitetipo'] = $em->getRepository('SieAppWebBundle:TramiteTipo')->find($datos['tramitetipo'])->getTramiteTipo();
                    $tarea45['tipoeducacion'] = $em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find($datos['tipoeducacion'])->getDescripcion();
                    $tarea45['dependenciatipo'] = $em->getRepository('SieAppWebBundle:DependenciaTipo')->find($datos['dependenciaTipo'])->getDependencia();
                    $tarea45['observacion'] = $datos['observacion'];
                    $tarea45['requisitos'] = $datos['requisitos'];
                    //dump($tarea45);die;
                    $tareasDatos[] = array('flujoProceso'=>45,'datos'=>$tarea45,'tarea'=>$wfd->getTramiteDetalle()->getFlujoProceso()->getProceso()->getProcesoTipo());
                    //dump($tareas);die;
                    break;
                case 46:
                    //dump($datos);die;
                    $tarea46['informe'] = $datos['informe'];
                    $tarea46['fechainforme'] = $datos['fechainforme'];
                    $tarea46['observacion'] = $datos['observacion'];
                    $tareasDatos[] = array('flujoProceso'=>46,'datos'=>$tarea46,'tarea'=>$wfd->getTramiteDetalle()->getFlujoProceso()->getProceso()->getProcesoTipo());
                    break;
                case 47:
                    //dump($datos);die;
                    $tarea47['tipotramite'] = $wfd->getTramiteDetalle()->getTramite()->getTramiteTipo();
                    $tarea47['turno'] = $em->getRepository('SieAppWebBundle:TurnoTipo')->find($datos['turnotipo'])->getTurno();
                    $tarea47['participantes'] = '';
                    $tarea47['areaespecial'] = '';
                    $tarea47['resolucion'] = '';
                    $tarea47['iiia_acta'] = '';
                    if ($wfd->getTramiteDetalle()->getTramite()->getInstitucioneducativa()){  //si es modificacion
                        $le = $em->getRepository('SieAppWebBundle:LugarTipo')->find($datos['jurisdiccion_geografica']['lugar_tipo_id_localidad']);
                        //dump($le);die;
                        $tarea47['tipoeducacion'] = $em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find($datos['institucioneducativa']['institucioneducativa_tipo_id'])->getDescripcion();
                        $tarea47['dependenciatipo'] = $em->getRepository('SieAppWebBundle:DependenciaTipo')->find($datos['institucioneducativa']['dependencia_tipo_id'])->getDependencia();
                        $tarea47['niveltipo'] = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa'=>$datos['institucioneducativa']['id']));
                        $tarea47['le'] = $datos['jurisdiccion_geografica']['id'];
                        $tarea47['departamento'] = $le->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar();
                        $tarea47['provincia'] = $le->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar();
                        $tarea47['municipio'] = $le->getLugarTipo()->getLugarTipo()->getLugar();
                        $tarea47['canton'] = $le->getLugarTipo()->getLugar();
                        $tarea47['localidad'] = $le->getLugar();
                        $tarea47['distrito'] = $em->getRepository('SieAppWebBundle:DistritoTipo')->find($datos['jurisdiccion_geografica']['distrito_tipo_id'])->getDistrito();
                        $tarea47['codrue'] = $datos['institucioneducativa']['id'];
                        $tarea47['institucioneducativa'] = $datos['institucioneducativa']['institucioneducativa'];
                        $tarea47['resolucion'] = $wfd->getTramiteDetalle()->getTramite()->getInstitucioneducativa()->getNroResolucion();
                        $tarea47['zona'] = $datos['jurisdiccion_geografica']['zona'];
                        $tarea47['direccion'] = $datos['jurisdiccion_geografica']['direccion'];
                        if($datos['institucioneducativa']['institucioneducativa_tipo_id']==4){
                            $tarea47['areaespecial'] = $em->getRepository('SieAppWebBundle:InstitucioneducativaAreaEspecialAutorizado')->findBy(array('institucioneducativa'=>$datos['institucioneducativa']['id'])); 
                            unset($datos['institucioneducativaAreaEspecial']);
                        }
                        unset($datos['institucioneducativa'],$datos['jurisdiccion_geografica'],$datos['institucioneducativaNivel']);
                    }else{   // si es nuevo
                        $tarea47['tipoeducacion'] = $em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find($datos['tipoeducacion'])->getDescripcion();
                        $tarea47['dependenciatipo'] = $em->getRepository('SieAppWebBundle:DependenciaTipo')->find($datos['dependenciatipo'])->getDependencia();
                        $tarea47['niveltipo'] = $em->getRepository('SieAppWebBundle:NivelTipo')->findBy(array('id'=>$datos['niveltipo']));
                        $tarea47['le'] = $datos['lejurisdiccion'];
                        $tarea47['codrue'] = '';
                        $tarea47['departamento'] = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($datos['departamento'])->getDepartamento();
                        $tarea47['provincia'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($datos['provincia'])->getLugar();
                        $tarea47['municipio'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($datos['municipio'])->getLugar();
                        $tarea47['canton'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($datos['canton'])->getLugar();
                        $tarea47['localidad'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($datos['localidad'])->getLugar();
                        $tarea47['distrito'] = $em->getRepository('SieAppWebBundle:DistritoTipo')->find($datos['distrito'])->getDistrito();
                        $tarea47['institucioneducativa'] = $datos['institucioneducativa'];
                        $tarea47['zona'] = $datos['zona'];
                        $tarea47['direccion'] = $datos['direccion'];
                        unset($datos['tipoeducacion'],$datos['dependenciatipo'],$datos['niveltipo'],$datos['lejurisdiccion'],$datos['departamento'],$datos['provincia'],$datos['municipio'],$datos['canton'],$datos['localidad'],$datos['distrito'],$datos['institucioneducativa'],$datos['zona'],$datos['direccion']);
                    }
                    unset($datos['turnotipo']);
                    $tarea47 = array_merge($tarea47, $datos); 
                    //dump($tarea47);die;
                    $tareasDatos[] = array('flujoProceso'=>47,'datos'=>$tarea47,'tarea'=>$wfd->getTramiteDetalle()->getFlujoProceso()->getProceso()->getProcesoTipo());
                    break;
                case 48:
                    //dump($datos);die;
                    $tareasDatos[] = array('flujoProceso'=>48,'datos'=>$datos,'tarea'=>$wfd->getTramiteDetalle()->getFlujoProceso()->getProceso()->getProcesoTipo());
                    break;
                case 49:
                    //dump($datos);die;
                    $tareasDatos[] = array('flujoProceso'=>49,'datos'=>$datos, 'tarea'=>$wfd->getTramiteDetalle()->getFlujoProceso()->getProceso()->getProcesoTipo());
                    break;
                case 50:
                    //dump($datos);die;
                    if($datos['varevaluacion'] == "SI"){
                        $tarea50['resolucion'] = $datos['resolucion'];
                        $tarea50['fecharesolucion'] = $datos['fecharesolucion'];
                        $tarea50['institucioneducativa'] = $datos['institucioneducativa'];
                        $tarea50['lejurisdiccion'] = $datos['lejurisdiccion'];
                        $tarea50['departamento'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($datos['departamento'])->getLugar();
                        $tarea50['provincia'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($datos['provincia'])->getLugar();
                        $tarea50['municipio'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($datos['municipio'])->getLugar();
                        $tarea50['canton'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($datos['canton'])->getLugar();
                        $tarea50['localidad'] = $em->getRepository('SieAppWebBundle:LugarTipo')->find($datos['localidad'])->getLugar();
                        $tarea50['zona'] = $datos['zona'];
                        $tarea50['direccion'] = $datos['direccion'];
                        $tarea50['distrito'] = $em->getRepository('SieAppWebBundle:DistritoTipo')->find($datos['distrito'])->getDistrito();
                        $tarea50['dependencia'] = $em->getRepository('SieAppWebBundle:DependenciaTipo')->find($datos['dependencia'])->getDependencia();
                        $tarea50['tipoeducacion'] = $em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find($datos['tipoeducacion'])->getDescripcion();
                        $tarea50['niveltipo'] = $em->getRepository('SieAppWebBundle:NivelTipo')->findBy(array('id'=>$datos['niveltipo']));
                        $tarea50['tipobachillerato'] = $datos['tipobachillerato'];
                        $tarea50['comparteLe'] = $datos['comparteLe'];
                        $tarea50['formularios'] = $datos['formularios'];
                        $tarea50['varevaluacion'] = $datos['varevaluacion'];
                        $tarea50['observacion'] = $datos['observacion'];
                        if($datos['tipoeducacion'] == 4){
                            $tarea50['areaEspecialTipo'] = $em->getRepository('SieAppWebBundle:EspecialAreaTipo')->findBy(array('id'=>$datos['areaEspecialTipo'])); 
                        }
                        if ($wfd->getTramiteDetalle()->getTramite()->getInstitucioneducativa()){  //si es modificacion
                            $tarea50['idrue'] = $datos['idrue'];
                        }else{
                            $tarea50['idrue'] = "";
                            $tarea50['director'] = $datos['director'];
                            $tarea50['ci'] = $datos['ci'];
                            $tarea50['tienecargo'] = $datos['tienecargo'];
                            $tarea50['telefono'] = $datos['telefono'];
                            $tarea50['otrotelefono'] = $datos['otrotelefono'];
                            $tarea50['email'] = $datos['email'];
                            $tarea50['fax'] = $datos['fax'];
                            $tarea50['otropertenece'] = $datos['otropertenece'];
                            $tarea50['casilla'] = $datos['casilla'];
                        }
                        $tareasDatos[] = array('flujoProceso'=>50,'datos'=>$tarea50, 'tarea'=>$wfd->getTramiteDetalle()->getFlujoProceso()->getProceso()->getProcesoTipo());
                    }else{
                        $tareasDatos[] = array('flujoProceso'=>50,'datos'=>$datos, 'tarea'=>$wfd->getTramiteDetalle()->getFlujoProceso()->getProceso()->getProcesoTipo());
                    }
                    //dump($tarea47);die;
                    break;
                    
                case 53:
                    //dump($datos);die;
                    $tareasDatos[] = array('flujoProceso'=>53,'datos'=>$datos, 'tarea'=>$wfd->getTramiteDetalle()->getFlujoProceso()->getProceso()->getProcesoTipo());
                    break;
                case 54:
                    break;
            }
        }

        return $tareasDatos;
    }

    public function buscaredificioAction(Request $request)
    {
        $idLe = $request->get('idLe');

        $em = $this->getDoctrine()->getManager();
        if($idLe){
            $edificio = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->find($idLe);
        }else{
            $edificio = "";
        }
                
        //dump($edificio);die;
        $response = new JsonResponse();
        $departamento = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' =>1));
        $depArray = array();
    	foreach($departamento as $d){
            if($d->getLugar() != "NO EXISTE EN CNPV 2001"){
                $depArray[$d->getid()] = $d->getlugar();
            }
        }

        if ($edificio)
        {
            $iddepartamento = $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
            $idprovincia = $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
            $idmunicipio = $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getId();
            $idcanton = $edificio->getLugarTipoLocalidad()->getLugarTipo()->getId();
            $idlocalidad = $edificio->getLugarTipoLocalidad()->getId();
            $zona = $edificio->getZona();
            $direccion = $edificio->getDireccion();
            $iddistrito = $edificio->getDistritoTipo()->getId();
            $provincia = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $iddepartamento));
            $provinciaArray = array();
    	    foreach($provincia as $p){
                if($p->getLugar() != "NO EXISTE EN CNPV 2001"){
                    $provinciaArray[$p->getid()] = $p->getlugar();
                }
            }
            $mun = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 3, 'lugarTipo' => $idprovincia));
        	$municipio = array();
        	foreach($mun as $m){
                if($m->getLugar() != "NO EXISTE EN CNPV 2001"){
    		        $municipio[$m->getid()] = $m->getlugar();
                }
            }
            $can = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 4, 'lugarTipo' => $idmunicipio));
        	$canton = array();
    	    foreach($can as $c){
                if($c->getLugar() != "NO EXISTE EN CNPV 2001"){
    		        $canton[$c->getid()] = $c->getlugar();
                }
            }
            $loc = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 5, 'lugarTipo' => $idcanton));
        	$localidad = array();
        	foreach($loc as $l) {
                if($l->getLugar() != "NO EXISTE EN CNPV 2001"){
    		        $localidad[$l->getid()] = $l->getlugar();
                }
            }
            $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->find($iddepartamento);
            $query = $em->createQuery(
                    'SELECT dt
                    FROM SieAppWebBundle:DistritoTipo dt
                    WHERE dt.id NOT IN (:ids)
                    AND dt.departamentoTipo = :dpto
                    ORDER BY dt.id')
                    ->setParameter('ids', array(1000,2000,3000,4000,5000,6000,7000,8000,9000))
                    ->setParameter('dpto', $dep->getcodigo());
            $distrito = $query->getResult();
            $distritoArray = array();
            foreach($distrito as $c){
                $distritoArray[$c->getId()] = $c->getDistrito();
            }
            $comparteLe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->createQueryBuilder('ie')
                ->select('ie')
                ->where('ie.leJuridicciongeografica='.$idLe)
                ->andWhere('ie.estadoinstitucionTipo=10')
                ->andWhere('ie.institucioneducativaAcreditacionTipo=1')
                ->getQuery()
                ->getResult();
            if($comparteLe){
                foreach($comparteLe as $c){
                    $comparteArray[$c->getId()] = $c->getInstitucioneducativa();
                }
            }else{
                $comparteLe = "";
            }
            return $response->setData(array(
                'iddepartamento' => $iddepartamento,
                'idprovincia' => $idprovincia,
                'idmunicipio' => $idmunicipio,
                'idcanton' => $idcanton,
                'idlocalidad' => $idlocalidad,
                'zona'=>$zona,
                'direccion' => $direccion,
                'iddistrito' => $iddistrito,
                'departamento' => $depArray,
                'provincia' => $provinciaArray,
                'municipio' => $municipio,
                'canton' => $canton,
                'localidad' => $localidad,
                'distrito' => $distritoArray,
                'comparteLe'=>$comparteArray,
            ));
        }else{
            $lt = $this->session->get('roluserlugarid');
            $ltu = $em->getRepository('SieAppWebBundle:LugarTipo')->find($lt);
            //dump($ltu);die;
            $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('codigo'=>$ltu->getCodigo(),'lugarNivel'=>1,'paisTipoId'=>1));
            //dump($dep);die;
            $mensaje = "No existe el Código del Edificio Educativo";
            //dump($mensaje);die;
            return $response->setData(array(
                'msg'=>$mensaje,
                'departamento' => $depArray,
            ));
        }
        
    }
    
}