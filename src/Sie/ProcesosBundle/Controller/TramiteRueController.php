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
    public $tramiteTipoArray;
    public $nivelArray;
 
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /***
     * Formulario inicio de solicitud
     */
    public function inicioSolicitudModificacionAction(Request $request)
    {
        $this->session = $request->getSession();
        //dump($this->session);die;
        $id_usuario = $this->session->get('userId');
        $idlugarusuario = $this->session->get('roluserlugarid');
        $id = $request->get('id'); 
        $tipo = $request->get('tipo'); 
        $idrue = $this->session->get('ie_id');
        //dump($id,$tipo);die;
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        if($tipo =='idtramite'){
            $tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($id);
            $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
            $tipotramite = $tramite->getTramiteTipo()->getId();
            $tareasDatos = $this->obtieneDatos($tramite);
            $flujotipo = $tramite->getFlujoTipo()->getId();
            $tarea = $tramiteDetalle->getFlujoProceso();
        }else{
            $tramite = null;
            $tareasDatos =null;
            $flujoproceso = $em->getRepository('SieAppWebBundle:FlujoProceso')->findOneBy(array('flujoTipo'=>$id,'orden'=>1));
            $flujotipo = $flujoproceso->getFlujoTipo()->getId();
            $tarea = $flujoproceso;    
        }
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($idrue);
        $institucioneducativaNivel = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa'=>$idrue));
        $inicioForm = $this->createInicioModificacionForm($flujotipo,$tarea->getId(),$tramite,$idrue,$institucioneducativa); 
        //dump($recepcionForm);die;
        return $this->render('SieProcesosBundle:TramiteRue:inicioSolicitudModificacion.html.twig', array(
            'form' => $inicioForm->createView(),
            'institucioneducativa'=>$institucioneducativa,
            'sucursal'=> $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa'=>$idrue,'gestionTipo'=>$this->session->get('currentyear'))),
            'ieNivel'=>$institucioneducativaNivel,
            'tramite'=>$tramite,
            'datos'=>$tareasDatos,
            'tarea'=>$tarea,
        ));
        //return $this->render('SieProcesosBundle:FlujoProceso:index1.html.twig');
    }

    public function createInicioModificacionForm($flujotipo,$tarea,$tramite,$idrue,$institucioneducativa)
    {
        $em = $this->getDoctrine()->getManager();
        $iddep = $institucioneducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
        $idprov = $institucioneducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
        $idmun = $institucioneducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getId();
        $idcan = $institucioneducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getId();
        $idloc = $institucioneducativa->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
        $iddis = $institucioneducativa->getLeJuridicciongeografica()->getDistritoTipo()->getId();
        $le = $this->obtieneLoalizacion($iddep,$idprov,$idmun,$idcan,$idloc);
        $this->tramiteTipoArray = array(32,33);
        if(in_array($institucioneducativa->getDependenciaTipo()->getId(),array(0,3,4,5))){
            array_push($this->tramiteTipoArray,36,38,39,40);
        }
        //dump($this->tramiteTipoArray);die;
        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('tramite_rue_inicio_modificacion_guardar'))
        ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
        ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
        ->add('tramite', 'hidden', array('data' =>$tramite?$tramite->getId():$tramite ))
        ->add('idrue', 'hidden', array('data' =>$idrue ))
        ->add('tramitetipo', 'hidden', array('data' =>33 ))
        ->add('tramites','entity',array('label'=>'Tipo de Trámite:','required'=>true,'multiple' => false,'expanded' => false,'attr'=>array('class'=>'form-control','data-placeholder'=>"Seleccionar tipo de trámite"),'class'=>'SieAppWebBundle:TramiteTipo',
            'query_builder'=>function(EntityRepository $tr){
            return $tr->createQueryBuilder('tr')
                ->where('tr.obs = :rue')
                //->andWhere('tr.id not in (:tipo)')
                ->andWhere('tr.id in (34,35)')
                ->setParameter('rue','RUE')
                //->setParameter('tipo',$this->tramiteTipoArray)
                ->orderBy('tr.tramiteTipo','ASC');},
            'property'=>'tramiteTipo','empty_value' => 'Seleccione tipo de trámite'))
        ->add('observacion','textarea',array('label'=>'JUSTIFICACIÓN:','required'=>false,'attr'=>array('class'=>'form-control','style' => 'text-transform:uppercase')))
        ->add('guardar','submit',array('label'=>'Enviar Solicitud'))
        ->getForm();
        return $form;
    }

    public function buscarTareaAction(Request $request){
        $id = $request->get('id');
        $tramitetipo = $request->get('tramitetipo');
        $ie = $request->get('ie');
        $em = $this->getDoctrine()->getManager();
        $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie);
        $form = $this->createFormBuilder();
        switch ($id){
            case 34://ampliacion de nivel
                $ienivel = $em->getRepository('SieAppWebBundle:NivelTipo')->createQueryBuilder('nt')
                    ->select('nt.id,nt.nivel')
                    ->leftJoin('SieAppWebBundle:InstitucioneducativaNivelAutorizado', 'ien', 'with', 'nt.id = ien.nivelTipo')
                    ->where('ien.institucioneducativa ='.$ie->getId())
                    ->getQuery()
                    ->getResult();
                $this->nivelArray = array(11,12,13);
                foreach($ienivel as $n){
                    if(in_array($n['id'],$this->nivelArray)){
                        $this->nivelArray = array_diff($this->nivelArray, array($n['id']));
                        
                    }
                }
                //dump($nivelArray);die;
                $form = $form
                ->add('form_34', 'hidden')
                ->add('nivelampliar','entity',array('label'=>'Ampliacion','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
                    return $nt->createQueryBuilder('nt')->where('nt.id in (:id)')->setParameter('id',$this->nivelArray)->orderBy('nt.id','ASC');},'property'=>'nivel'))
                ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'ieNivel'=>$ienivel
                );
                break;
            case 35://Reduccion de Nivel
                $ienivel = $em->getRepository('SieAppWebBundle:NivelTipo')->createQueryBuilder('nt')
                    ->select('nt.id,nt.nivel')
                    ->leftJoin('SieAppWebBundle:InstitucioneducativaNivelAutorizado', 'ien', 'with', 'nt.id = ien.nivelTipo')
                    ->where('ien.institucioneducativa ='.$ie->getId())
                    ->getQuery()
                    ->getResult();
                $this->idcan = $ie->getId();
                $form = $form
                    ->add('form_35', 'hidden')
                    ->add('nivelreducir','entity',array('label'=>'Reduccion','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
                        return $nt->createQueryBuilder('nt')
                            ->select('nt')
                            ->leftJoin('SieAppWebBundle:InstitucioneducativaNivelAutorizado', 'ien', 'with', 'nt.id = ien.nivelTipo')
                            ->where('ien.institucioneducativa ='.$this->idcan)
                            ->orderBy('nt.id','ASC');},'property'=>'nivel'))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'ieNivel'=>$ienivel
                );
                break;
            case 36://cambio de dependencia
                $form = $form
                ->add('dependencia','choice',array('label'=>'Dependencia:','required'=>false,'data'=>$institucioneducativa->getDependenciaTipo()->getId(),'choices'=>$le['dependencia'],'empty_value'=>false,'attr' => array('class' => 'form-control')))
                ->add('conveniotipo','entity',array('label'=>'Tipo de Convenio:','required'=>false,'multiple' => false,'attr' => array('class' => 'form-control'),'empty_value'=>'Seleccione convenio','class'=>'SieAppWebBundle:ConvenioTipo','query_builder'=>function(EntityRepository $ct){
                    return $ct->createQueryBuilder('ct')->where('ct.id <>99')->orderBy('ct.convenio','ASC');},'property'=>'convenio'));
                break;
            case 37://cambio de nombre
                $form = $form
                ->add('institucioneducativa','text',array('label'=>'Nombre de la Unidad Educativa:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')));//
                break;
            case 38://cambio de jurisdiccion administrativa
                $form = $form
                ->add('distrito','choice',array('label'=>'Distrito:','required'=>false,'data'=>$iddis,'choices'=>$le['distrito'],'empty_value'=>false,'attr' => array('class' => 'form-control')));
                break;
            case 39://Fusion
                break;
            case 40://desglose
                break;
            case 41://cambio de infraestructura
                $form = $form
                ->add('lejurisdiccion', 'text', array('label' => 'Código Edificio Educativo:','required'=>false,'attr' => array('class' => 'form-control validar','maxlength'=>8)))
                ->add('departamento','entity',array('label'=>'Departamento:','required'=>false,'attr' => array('class' => 'form-control'),'class'=>'SieAppWebBundle:LugarTipo','query_builder'=>function(EntityRepository $lt){
                    return $lt->createQueryBuilder('lt')->where('lt.lugarNivel = 8')->andWhere('lt.paisTipoId=1')->orderBy('lt.id','ASC');},'property'=>'lugar','empty_value' => 'Seleccione departamento'))
                ->add('provincia', 'choice', array('label' => 'Provincia:','required'=>false,'attr' => array('class' => 'form-control')))
                ->add('municipio', 'choice', array('label' => 'Municipio:','required'=>false, 'attr' => array('class' => 'form-control')))
                ->add('comunidad', 'choice', array('label' => 'Comunidad:','required'=>false, 'attr' => array('class' => 'form-control')))
                /* ->add('canton', 'choice', array('label' => 'Cantón:','required'=>false, 'attr' => array('class' => 'form-control')))
                ->add('localidad', 'choice', array('label' => 'Localidad/Comunidad:','required'=>false,'attr' => array('class' => 'form-control'))) */
                ->add('zona', 'text', array('label' => 'Zona:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))//
                ->add('direccion', 'text', array('label' => 'Dirección:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')));//
                break;
            case 42://cierre temporal
                break;
            case 43://cierre definitivo
                break;
            case 44://Reapertura
                break;
            case 45://nuevo certifcado rue
                break;
        }
        
        return $this->render('SieProcesosBundle:TramiteRue:modificaTramite.html.twig', $data);
    }

    public function buscarRequisitosAction(Request $request){
        $id = $request->get('id');
        //dump($tramitetipo);die;
        $ie = $request->get('ie');
        $em = $this->getDoctrine()->getManager();
        $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie);
        $tramitetipo = $em->getRepository('SieAppWebBundle:TramiteTipo')->find($id);
        $requisitos = array();
        $form = $this->createFormBuilder();
        switch ($id){
            case 34://ampliacion de nivel
                $form = $form
                ->add('i_solicitud_ampliar', 'file', array('label' => 'Adjuntar Solicitud (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar solicitud",'accept'=>"application/pdf,.img,.jpg")))
                ->add('i_alquiler_ampliar', 'choice', array('label' => 'Infraestructura arrendada:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
                ->add('i_contrato_ampliar', 'file', array('label' => 'Adjuntar Copia notariada de arrendamiento (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar contrato",'accept'=>"application/pdf,.img,.jpg")))
                ->add('i_certificado_ampliar', 'checkbox', array('label' => 'Original de Certificado RUE','required'  => true))
                ->add('ii_planos_ampliar', 'checkbox', array('label' => 'Planos arquitectónicos','required'  => true))
                ->add('ii_infra_ampliar', 'checkbox', array('label' => 'Actualizacion de datos de infraestructura en el SIE','required'  => true))
                ->getForm();
                $requisitos = array('legal'=>true,'infra'=>true,'admi'=>false);
                $data = array(
                    'form_34' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos
                    //'ieNivel'=>$ienivel
                );
                break;
            case 35://Reduccion de Nivel
                $form = $form
                    ->add('i_solicitud_reducir', 'file', array('label' => 'Adjuntar Solicitud (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar solicitud",'accept'=>"application/pdf,.img,.jpg")))
                    ->add('i_alquiler_reducir', 'choice', array('label' => 'Infraestructura arrendada:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
                    ->add('i_contrato_reducir', 'file', array('label' => 'Adjuntar Copia notariada de arrendamiento (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar contrato",'accept'=>"application/pdf,.img,.jpg")))
                    ->add('i_certificado_reducir', 'checkbox', array('label' => 'Original de Certificado RUE','required'  => true))
                    ->add('ii_planos_reducir', 'checkbox', array('label' => 'Planos arquitectónicos','required'  => true))
                    ->add('ii_infra_reducir', 'checkbox', array('label' => 'Actualizacion de datos de infraestructura en el SIE','required'  => true))
                    ->getForm();
                $requisitos = array('legal'=>true,'infra'=>true,'admi'=>false);
                $data = array(
                    'form_35' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos
                    //'ieNivel'=>$ienivel
                );
                break;
            case 36://cambio de dependencia
                
                break;
            case 37://cambio de nombre
                $form = $form
                ->add('institucioneducativa','text',array('label'=>'Nombre de la Unidad Educativa:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')));//
                break;
            case 38://cambio de jurisdiccion administrativa
                $form = $form
                ->add('distrito','choice',array('label'=>'Distrito:','required'=>false,'data'=>$iddis,'choices'=>$le['distrito'],'empty_value'=>false,'attr' => array('class' => 'form-control')));
                break;
            case 39://Fusion
                break;
            case 40://desglose
                break;
            case 41://cambio de infraestructura
                $form = $form
                ->add('lejurisdiccion', 'text', array('label' => 'Código Edificio Educativo:','required'=>false,'attr' => array('class' => 'form-control validar','maxlength'=>8)))
                ->add('departamento','entity',array('label'=>'Departamento:','required'=>false,'attr' => array('class' => 'form-control'),'class'=>'SieAppWebBundle:LugarTipo','query_builder'=>function(EntityRepository $lt){
                    return $lt->createQueryBuilder('lt')->where('lt.lugarNivel = 8')->andWhere('lt.paisTipoId=1')->orderBy('lt.id','ASC');},'property'=>'lugar','empty_value' => 'Seleccione departamento'))
                ->add('provincia', 'choice', array('label' => 'Provincia:','required'=>false,'attr' => array('class' => 'form-control')))
                ->add('municipio', 'choice', array('label' => 'Municipio:','required'=>false, 'attr' => array('class' => 'form-control')))
                ->add('comunidad', 'choice', array('label' => 'Comunidad:','required'=>false, 'attr' => array('class' => 'form-control')))
                /* ->add('canton', 'choice', array('label' => 'Cantón:','required'=>false, 'attr' => array('class' => 'form-control')))
                ->add('localidad', 'choice', array('label' => 'Localidad/Comunidad:','required'=>false,'attr' => array('class' => 'form-control'))) */
                ->add('zona', 'text', array('label' => 'Zona:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))//
                ->add('direccion', 'text', array('label' => 'Dirección:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')));//
                break;
            case 42://cierre temporal
                break;
            case 43://cierre definitivo
                break;
            case 44://Reapertura
                break;
            case 45://nuevo certifcado rue
                break;
        }
        
        return $this->render('SieProcesosBundle:TramiteRue:requisitoTramite.html.twig', $data);
    }

    public function createInicioNuevoForm($flujotipo,$tarea,$tramite,$institucioneducativa)
    {
        $em = $this->getDoctrine()->getManager();   
        
        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('tramite_rue_recepcion_departamental_guardar'))
        ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
        ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
        ->add('tramite', 'hidden', array('data' =>$idtramite ));
        $form=$form
        ->add('institucioneducativa','text',array('label'=>'Nombre del Centro Educativo:','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
        ->add('lejurisdiccion', 'text', array('label' => 'Código RUE-SIE del Edificio Educativo','required'=>false,'attr' => array('class' => 'form-control validar')))
        ->add('departamento','entity',array('label'=>'Departamento','required'=>true,'attr' => array('class' => 'form-control'),'class'=>'SieAppWebBundle:LugarTipo','query_builder'=>function(EntityRepository $lt){
            return $lt->createQueryBuilder('lt')->where('lt.lugarNivel = 1')->andWhere('lt.paisTipoId=1')->orderBy('lt.id','ASC');},'property'=>'lugar','empty_value' => 'Seleccione departamento'))
    	->add('provincia', 'choice', array('label' => 'Provincia','required'=>true,'attr' => array('class' => 'form-control')))
    	->add('municipio', 'choice', array('label' => 'Municipio','required'=>true, 'attr' => array('class' => 'form-control')))
    	->add('canton', 'choice', array('label' => 'Cantón','required'=>true, 'attr' => array('class' => 'form-control')))
    	->add('localidad', 'choice', array('label' => 'Localidad/Comunidad','required'=>true,'attr' => array('class' => 'form-control')))
    	->add('zona', 'text', array('label' => 'Zona','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
    	->add('direccion', 'text', array('label' => 'Dirección','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
        ->add('distrito', 'choice', array('label' => 'Distrito Educativo','required'=>true,'attr' => array('class' => 'form-control')))
        ->add('dependencia','choice',array('label'=>'Dependencia:','required'=>true,'data'=>$institucioneducativa->getDependenciaTipo()->getId(),'choices'=>$le['dependencia'],'empty_value'=>false,'attr' => array('class' => 'form-control')))
        ->add('conveniotipo','entity',array('label'=>'Tipo de Convenio:','required'=>false,'multiple' => false,'attr' => array('class' => 'form-control'),'empty_value'=>'Seleccione convenio','class'=>'SieAppWebBundle:ConvenioTipo','query_builder'=>function(EntityRepository $ct){
            return $ct->createQueryBuilder('ct')->where('ct.id <>99')->orderBy('ct.convenio','ASC');},'property'=>'convenio'))
        ->add('niveltipo','entity',array('label'=>'Ampliacion','required'=>true,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
            return $nt->createQueryBuilder('nt')->where('nt.id in (:id)')->setParameter('id',array(11,12,13))->orderBy('nt.id','ASC');},'property'=>'nivel'))
        ->add('cantidad_11_1', 'text', array('label' => '1ro','required'=>false,'attr' => array('class' => 'form-control validar')))
        ->add('cantidad_11_2', 'text', array('label' => '2ro','required'=>false,'attr' => array('class' => 'form-control validar')))
        ->add('cantidad_12_1', 'text', array('label' => '1ro','required'=>false,'attr' => array('class' => 'form-control validar')))
        ->add('cantidad_12_2', 'text', array('label' => '2ro','required'=>false,'attr' => array('class' => 'form-control validar')))
        ->add('cantidad_12_3', 'text', array('label' => '3ro','required'=>false,'attr' => array('class' => 'form-control validar')))
        ->add('cantidad_12_4', 'text', array('label' => '4ro','required'=>false,'attr' => array('class' => 'form-control validar')))
        ->add('cantidad_12_5', 'text', array('label' => '5ro','required'=>false,'attr' => array('class' => 'form-control validar')))
        ->add('cantidad_12_6', 'text', array('label' => '6ro','required'=>false,'attr' => array('class' => 'form-control validar')))
        ->add('cantidad_13_1', 'text', array('label' => '1ro','required'=>false,'attr' => array('class' => 'form-control validar')))
        ->add('cantidad_13_2', 'text', array('label' => '2ro','required'=>false,'attr' => array('class' => 'form-control validar')))
        ->add('cantidad_13_3', 'text', array('label' => '3ro','required'=>false,'attr' => array('class' => 'form-control validar')))
        ->add('cantidad_13_4', 'text', array('label' => '4ro','required'=>false,'attr' => array('class' => 'form-control validar')))
        ->add('cantidad_13_5', 'text', array('label' => '5ro','required'=>false,'attr' => array('class' => 'form-control validar')))
        ->add('cantidad_13_6', 'text', array('label' => '6ro','required'=>false,'attr' => array('class' => 'form-control validar')))
        ->add('cantidad_adm', 'text', array('label' => '7.1 Adminidtrativo','required'=>true,'attr' => array('class' => 'form-control validar')))
        ->add('cantidad_maestro', 'text', array('label' => '7.2 Docente (Maestro)','required'=>true,'attr' => array('class' => 'form-control validar')))
        ->getForm();
        //dump($form);die;
        return $form;
    }

    public function inicioSolicitudModificacionGuardarAction(Request $request)
    {

        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        dump($form);die;
        parse_str($form['form_34'],$array); 
        
        $datos=array();
        $usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $flujotipo = $form['flujotipo'];
        $tarea = $form['flujoproceso'];
        $tabla = 'institucioneducativa';
        $id_tabla = $form['idrue'];
        $observacion = $form['observacion'];
        $tipotramite = $form['tramitetipo'];
        $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['idrue']);
        $ie_lugardistrito = $ie->getLeJuridicciongeografica()->getLugarTipoIdDistrito();
        $ie_lugarlocalidad = $ie->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getId();
        /**
         * datos propios de la solicitud del formulario rue
         */
        
        //dump($form);die;
        $datos = array();
        foreach ($form['tramites'] as $tramite){
            switch($tramite){
                case 34://Ampliacion de Nivel
                    $datos['nivelampliar']=$form['nivelampliar'];
                    break;
                case 35: //Reduccion de Nivel
                    $datos['nivelreducir']=$form['nivelreducir'];
                    break;
                case 36://Cambio de Dependencia
                    $datos['dependencia']=$form['dependencia'];
                    if ($form['dependencia']==2){
                        $datos['conveniotipo']=$form['conveniotipo'];
                    }
                    break;
                case 37://Cambio de Nombre
                    $datos['nuevonombre']=$form['institucioneducativa'];
                    break;
                case 38://Cambio de Jurisdiccion
                    $datos['distrito']=$form['distrito'];
                    break;
                case 39://Fusion
                    $datos['siefusion']=$form['siefusion'];
                    $datos['fusioncerrar']=$form['fusioncerrar'];
                    $datos['nivelfusion']=$form['nivelfusion'];
                    break;
                case 40://Desglose
                    $datos['niveldesglose']=$form['niveldesglose'];
                    //$datos['fusioncerrar']=$form['fusioncerrar'];
                    break;
                case 41://Cambio de Infraestructura
                    if($form['lejurisdiccion']){
                        $datos['lejurisdiccion']=$form['lejurisdiccion'];    
                    }else{
                        $datos['comunidad']=$form['comunidad'];
                        $datos['zona']=$form['zona'];
                        $datos['direccion']=$form['direccion'];
                    }
                    break;
                case 42://Cierre Temporal
                    break;
                case 43://Cierre Definitivo
                    break;
                case 44://Reapertura
                    break;
                case 45://Nuevo Certificado RUE
                    break;
            }
        }
        //$tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        $query = $em->getConnection()->prepare('SELECT * 
                FROM institucioneducativa ie
                WHERE ie.id='. $tramite->getInstitucioneducativa()->getId());
                $query->execute();
        $institucioneducativa = $query->fetchAll();
        $query = $em->getConnection()->prepare('SELECT ien.nivel_tipo_id,nt.nivel
                FROM institucioneducativa_nivel_autorizado ien
                join nivel_tipo nt on ien.nivel_tipo_id = nt.id
                WHERE ien.institucioneducativa_id='. $form['idrue']);
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
                WHERE iea.institucioneducativa_id='. $form['idrue']);
                $query->execute();
            $ieAreasAutorizado = $query->fetchAll();
            $form['institucioneducativaAreaEspecial']=$ieAreasAutorizado;
        }
        $id_tabla = $institucioneducativa[0]['id'];
        $form['institucioneducativa']=$institucioneducativa[0];
        $form['jurisdiccion_geografica']=$le[0];
        $form['institucioneducativaNivel']=$ieNivelAutorizado;
        
        $datos = json_encode($datos);

        if ($form['idrue']){
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
    /***
     * Formulario Distrito
     */
    public function recepcionDistritoAction(Request $request)
    {
        
        $this->session = $request->getSession();
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
        /**
         * obtiene datos de los anteriores formularios
         */
        $tareasDatos = $this->obtieneDatos($tramite);
        $flujotipo = $tramite->getFlujoTipo()->getId();
        $tarea = $tramiteDetalle->getFlujoProceso()->getId();
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $distritoForm = $this->createDistritoForm($flujotipo,$tarea,$tramite,$tramite->getInstitucioneducativa()->getId()); 
        return $this->render('SieProcesosBundle:TramiteRue:recepcionDistrito.html.twig', array(
            'form' => $distritoForm->createView(),
            'tramite'=>$tramite,
            'datos'=>$tareasDatos,
            'tarea'=>$tramiteDetalle->getFlujoProceso()->getProceso()->getProcesoTipo()
        ));

    }

    public function createDistritoForm($flujotipo,$tarea,$tramite,$idrue)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('tramite_rue_recepcion_distrito_guardar'))
        ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
        ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
        ->add('tramite', 'hidden', array('data' =>$tramite?$tramite->getId():$tramite ))
        ->add('idrue', 'hidden', array('data' =>$idrue ))
        ->add('tramitetipo', 'hidden', array('data' =>5 ))
        ->add('requisitos','choice',array('label'=>'Requisitos:','required'=>false, 'multiple' => true,'expanded' => true,'choices'=>array('Requisitos Legales' => 'Requisitos Legales','Requisitos de Infraestructura'=>'Requisitos de Infraestructura','Requisitos Administrativos' => 'Requisitos Administrativos')))
        ->add('observacion','textarea',array('label'=>'Observación:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
        ->add('varevaluacion1','choice',array('label'=>'¿Observar y devolver?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
        ->add('varevaluacion2','choice',array('label'=>'¿Informe Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
        ->add('informedistrito','text',array('label'=>'Nro. Informe:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase','placeholder'=>'')))
        ->add('fechainformedistrito', 'text', array('label'=>'Fecha de Informe:','required'=>false,'attr' => array('class' => 'form-control date','placeholder'=>'')))
        ->add('adjuntoinforme', 'file', array('label' => 'Adjuntar Informe (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar Informe",'accept'=>"application/pdf,.img,.jpg")))
        ->add('guardar','submit',array('label'=>'Enviar Solicitud'))
        ->getForm();
        
        return $form;
    }

    public function recepcionDistritoGuardarAction(Request $request)
    {
        
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form);die;
        $datos=array();
        $datos['observacion']=$form['observacion'];
        $datos['varevaluacion1']=$form['varevaluacion1'];
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
        $varevaluacion1 = $form['varevaluacion1'];
        $lugar = $this->obtienelugar($idtramite);
        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion1,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
        if($mensaje['dato']== true){
            if($varevaluacion1=="NO"){
                $wfTareaCompuerta = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$tarea,'condicion'=>'NO'));
                $tarea = $wfTareaCompuerta[0]->getCondicionTareaSiguiente();
                $mensaje = $this->get('wftramite')->guardarTramiteRecibido($usuario,$tarea,$idtramite);
                if($mensaje['dato'] == true){
                    $datos['varevaluacion2']=$form['varevaluacion2'];
                    $varevaluacion2 = $form['varevaluacion2'];
                    $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion2,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
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
    /***
     * Formulario Departamento
     */
    public function recepcionDepartamentoAction(Request $request)
    {
        
        $this->session = $request->getSession();
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
        /**
         * obtiene datos de los anteriores formularios
         */
        $tareasDatos = $this->obtieneDatos($tramite);
        $flujotipo = $tramite->getFlujoTipo()->getId();
        $tarea = $tramiteDetalle->getFlujoProceso()->getId();
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $departamentoForm = $this->createDepartamentoForm($flujotipo,$tarea,$tramite,$tramite->getInstitucioneducativa()->getId()); 
        return $this->render('SieProcesosBundle:TramiteRue:recepcionDepartamento.html.twig', array(
            'form' => $departamentoForm->createView(),
            'tramite'=>$tramite,
            'datos'=>$tareasDatos,
            'tarea'=>$tramiteDetalle->getFlujoProceso()->getProceso()->getProcesoTipo()
        ));

    }

    public function createDepartamentoForm($flujotipo,$tarea,$tramite,$idrue)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('tramite_rue_recepcion_departamento_guardar'))
        ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
        ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
        ->add('idrue', 'hidden', array('data' =>$idrue ))
        ->add('tramite', 'hidden', array('data' =>$tramite->getId()))
        ->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'empty_value'=>false,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
        ->add('informesubdireccion','text',array('label'=>'Nro. Informe Sub Dirección:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
        ->add('fechainformesubdireccion', 'text', array('label'=>'Fecha de Informe Subdirección:','required'=>false,'attr' => array('class' => 'form-control date')))
        ->add('adjuntoinformesubdireccion', 'file', array('label' => 'Adjuntar Informe (Máximo permitido 3M):','required'=>false, 'attr' => array('class'=>'form-control-file','title'=>"Adjuntar Informe",'accept'=>"application/pdf,.img,.jpg")))
        ->add('informejuridica','text',array('label'=>'Nro. Informe Legal:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
        ->add('fechainformejuridica', 'text', array('label'=>'Fecha de Informe Legal:','required'=>false,'attr' => array('class' => 'form-control date')))
        ->add('adjuntoinformejuridica', 'file', array('label' => 'Adjuntar Informe (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar Informe",'accept'=>"application/pdf,.img,.jpg")))
        ->add('resolucion','text',array('label'=>'Nro. de Resolución:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
        ->add('fecharesolucion', 'text', array('label'=>'Fecha de Resolución:','required'=>false,'attr' => array('class' => 'form-control date')))
        ->add('adjuntoresolucion', 'file', array('label' => 'Adjuntar Resolución (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar Resolución",'accept'=>"application/pdf,.img,.jpg")))
        ->add('observacion','textarea',array('label'=>'Observación:','required'=>false,'attr'=>array('class'=>'form-control','style' => 'text-transform:uppercase')))
        ->add('guardar','submit',array('label'=>'Enviar Solicitud'))
        ->getForm();
        return $form;
    }

    public function recepcionDepartamentoGuardarAction(Request $request)
    {
        
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form);die;
        $datos=array();
        //$datos['observacion']=$form['observacion'];
        //$datos['varevaluacion']=$form['varevaluacion'];
        $datos = $form;
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
        $varevaluacion = $form['varevaluacion'];
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

    /***
     * Formulario Minedu
     */
    public function recepcionMineduAction(Request $request)
    {
        
        $this->session = $request->getSession();
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
        /**
         * obtiene datos de los anteriores formularios
         */
        $tareasDatos = $this->obtieneDatos($tramite);
        $flujotipo = $tramite->getFlujoTipo()->getId();
        $tarea = $tramiteDetalle->getFlujoProceso()->getId();
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $mineduForm = $this->createMineduForm($flujotipo,$tarea,$tramite,$tramite->getInstitucioneducativa()->getId()); 
        return $this->render('SieProcesosBundle:TramiteRue:recepcionMinedu.html.twig', array(
            'form' => $mineduForm->createView(),
            'tramite'=>$tramite,
            'datos'=>$tareasDatos,
            'tarea'=>$tramiteDetalle->getFlujoProceso()->getProceso()->getProcesoTipo()
        ));

    }

    public function createMineduForm($flujotipo,$tarea,$tramite,$idrue)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('tramite_rue_recepcion_minedu_guardar'))
        ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
        ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
        ->add('tramite', 'hidden', array('data' =>$tramite?$tramite->getId():$tramite ))
        ->add('idrue', 'hidden', array('data' =>$idrue ))
        ->add('tramitetipo', 'hidden', array('data' =>5 ))
        ->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
        ->add('observacion','textarea',array('label'=>'Observación:','required'=>false,'attr'=>array('class'=>'form-control','style' => 'text-transform:uppercase')))
        ->add('guardar','submit',array('label'=>'Registrar Modificación'))
        ->getForm();
        return $form;
    }

    public function recepcionMineduGuardarAction(Request $request)
    {
        
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form);die;
        $datos=array();
        $datos['observacion']=$form['observacion'];
        $datos['varevaluacion']=$form['varevaluacion'];
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
        $varevaluacion = $form['varevaluacion'];
        $lugar = $this->obtienelugar($idtramite);
        //dump($varevaluacion);die;
        $mensaje = $this->get('wftramite')->guardarTramiteEnviado($usuario,$rol,$flujotipo,$tarea,$tabla,$id_tabla,$observacion,$varevaluacion,$idtramite,$datos,$lugar['idlugarlocalidad'],$lugar['idlugardistrito']);
        if($mensaje['dato'] == true){
            if($varevaluacion=="SI"){
                $wfTareaCompuerta = $em->getRepository('SieAppWebBundle:WfTareaCompuerta')->findBy(array('flujoProceso'=>$tarea,'condicion'=>'SI'));
                $varevaluacion2 = "";
                $observacion2 = $form['observacion'];
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

    /***
     * Formulario envio certificados
     */
    public function enviaCertificadoMineduAction(Request $request)
    {
        
        $this->session = $request->getSession();
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
        /**
         * obtiene datos de los anteriores formularios
         */
        $tareasDatos = $this->obtieneDatos($tramite);
        $flujotipo = $tramite->getFlujoTipo()->getId();
        $tarea = $tramiteDetalle->getFlujoProceso()->getId();
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $enviaCertificadoMinedu = $this->createEnviaCertificadoMineduForm($flujotipo,$tarea,$tramite,$tramite->getInstitucioneducativa()->getId()); 
        return $this->render('SieProcesosBundle:TramiteRue:enviaCertificadoMinedu.html.twig', array(
            'form' => $enviaCertificadoMinedu->createView(),
            'tramite'=>$tramite,
            'datos'=>$tareasDatos,
            'tarea'=>$tramiteDetalle->getFlujoProceso()->getProceso()->getProcesoTipo()
        ));

    }

    public function createEnviaCertificadoMineduForm($flujotipo,$tarea,$tramite,$idrue)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('tramite_rue_envia_certificado_minedu_guardar'))
        ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
        ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
        ->add('tramite', 'hidden', array('data' =>$tramite?$tramite->getId():$tramite ))
        ->add('idrue', 'hidden', array('data' =>$idrue ))
        ->add('tramitetipo', 'hidden', array('data' =>5 ))
        //->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
        ->add('observacion','textarea',array('label'=>'Observación:','required'=>false,'attr'=>array('class'=>'form-control','style' => 'text-transform:uppercase')))
        ->add('guardar','submit',array('label'=>'Enviar Solicitud'))
        ->getForm();
        return $form;
    }

    public function enviaCertificadoMineduGuardarAction(Request $request)
    {
        
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form);die;
        $datos=array();
        $datos['observacion']=$form['observacion'];
        //$datos['varevaluacion']=$form['varevaluacion'];
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

    /***
     * Formulario recepcion y envio certificados departamento
     */
    public function enviaCertificadoDepartamentoAction(Request $request)
    {
        
        $this->session = $request->getSession();
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
        /**
         * obtiene datos de los anteriores formularios
         */
        $tareasDatos = $this->obtieneDatos($tramite);
        $flujotipo = $tramite->getFlujoTipo()->getId();
        $tarea = $tramiteDetalle->getFlujoProceso()->getId();
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $enviaCertificadoDepartamentoForm = $this->createEnviaCertificadoDepartamentoForm($flujotipo,$tarea,$tramite,$tramite->getInstitucioneducativa()->getId()); 
        return $this->render('SieProcesosBundle:TramiteRue:enviaCertificadoDepartamento.html.twig', array(
            'form' => $enviaCertificadoDepartamentoForm->createView(),
            'tramite'=>$tramite,
            'datos'=>$tareasDatos,
            'tarea'=>$tramiteDetalle->getFlujoProceso()->getProceso()->getProcesoTipo()
        ));

    }

    public function createEnviaCertificadoDepartamentoForm($flujotipo,$tarea,$tramite,$idrue)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('tramite_rue_entrega_certificado_distrito_guardar'))
        ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
        ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
        ->add('tramite', 'hidden', array('data' =>$tramite?$tramite->getId():$tramite ))
        ->add('idrue', 'hidden', array('data' =>$idrue ))
        ->add('tramitetipo', 'hidden', array('data' =>5 ))
        //->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
        ->add('observacion','textarea',array('label'=>'Observación:','required'=>false,'attr'=>array('class'=>'form-control','style' => 'text-transform:uppercase')))
        ->add('guardar','submit',array('label'=>'Enviar Solicitud'))
        ->getForm();
        return $form;
    }

    public function enviaCertificadoDepartamentoGuardarAction(Request $request)
    {
        
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form);die;
        $datos=array();
        $datos['observacion']=$form['observacion'];
        //$datos['varevaluacion']=$form['varevaluacion'];
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

    /***
     * Formulario recepcion y entraga certificados Distrito
     */
    public function entregaCertificadoDistritoAction(Request $request)
    {
        
        $this->session = $request->getSession();
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
        /**
         * obtiene datos de los anteriores formularios
         */
        $tareasDatos = $this->obtieneDatos($tramite);
        $flujotipo = $tramite->getFlujoTipo()->getId();
        $tarea = $tramiteDetalle->getFlujoProceso()->getId();
        $tipotramite = $tramite->getTramiteTipo()->getId();
        $entregaCertificadoDistritoForm = $this->createEntregaCertificadoDistritoForm($flujotipo,$tarea,$tramite,$tramite->getInstitucioneducativa()->getId()); 
        return $this->render('SieProcesosBundle:TramiteRue:entregaCertificadoDistrito.html.twig', array(
            'form' => $entregaCertificadoDistritoForm->createView(),
            'tramite'=>$tramite,
            'datos'=>$tareasDatos,
            'tarea'=>$tramiteDetalle->getFlujoProceso()->getProceso()->getProcesoTipo()
        ));

    }

    public function createEntregaCertificadoDistritoForm($flujotipo,$tarea,$tramite,$idrue)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('tramite_rue_entrega_certificado_distrito_guardar'))
        ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
        ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
        ->add('tramite', 'hidden', array('data' =>$tramite?$tramite->getId():$tramite ))
        ->add('idrue', 'hidden', array('data' =>$idrue ))
        ->add('tramitetipo', 'hidden', array('data' =>5 ))
        //->add('varevaluacion','choice',array('label'=>'¿Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
        ->add('observacion','textarea',array('label'=>'Observación:','required'=>false,'attr'=>array('class'=>'form-control','style' => 'text-transform:uppercase')))
        ->add('guardar','submit',array('label'=>'Enviar Solicitud'))
        ->getForm();
        return $form;
    }

    public function entregaCertificadoDistritoGuardarGuardarAction(Request $request)
    {
        
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form);die;
        $datos=array();
        $datos['observacion']=$form['observacion'];
        //$datos['varevaluacion']=$form['varevaluacion'];
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
                ->setParameter('id',array(1,2))
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
        $iddistrito=$request->get('iddistrito');
        $idsiefusion=$request->get('idsiefusion');
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT ie.id,ie.institucioneducativa,nt.id as nivel_tipo_id,nt.nivel
                FROM SieAppWebBundle:Institucioneducativa ie
                JOIN SieAppWebBundle:JurisdiccionGeografica le WITH ie.leJuridicciongeografica = le.id
                JOIN SieAppWebBundle:InstitucioneducativaNivelAutorizado iena WITH iena.institucioneducativa = ie.id
                JOIN SieAppWebBundle:NivelTipo nt WITH nt.id = iena.nivelTipo
                WHERE ie.id = :id
                AND ie.estadoinstitucionTipo = 10
                AND ie.institucioneducativaAcreditacionTipo = 1
                AND le.lugarTipoIdDistrito = :lugar_id')
                ->setParameter('id', $idsiefusion)
                ->setParameter('lugar_id', $iddistrito);
        $institucioneducativa = $query->getResult();
        //dump($institucioneducativa);die;
        $response = new JsonResponse();
        if($institucioneducativa){
            foreach ($institucioneducativa as $nt){
                $nivel[]=$nt['nivel_tipo_id'];
            }
            $iefusion = array('idsiefusion'=>$idsiefusion,'institucioneducativa'=>$idsiefusion.'-'.$institucioneducativa[0]['institucioneducativa']);
            $response->setData(array('ie'=>$iefusion,'nivel'=>$nivel));
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

    

    public function obtieneDatos($tramite)
    {
        $em = $this->getDoctrine()->getManager();
        $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wfd')
                ->select('wfd')
                ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'with', 'td.id = wfd.tramiteDetalle')
                ->where('td.tramite='.$tramite->getId())
                ->andWhere("wfd.esValido=true")
                ->orderBy("td.flujoProceso")
                ->getQuery()
                ->getResult();
        $tareasDatos = array();
        foreach($wfdatos as $wfd)
        {
            $datos = json_decode($wfd->getdatos(),true);
            //dump($datos);die;
            switch ($wfd->getTramiteDetalle()->getFlujoProceso()->getId()) {
                case 64:
                    $tarea = array();
                    $institucioneducativa = $wfd->getTramiteDetalle()->getTramite()->getInstitucioneducativa();
                    $tarea['tramitetipo'] = $em->getRepository('SieAppWebBundle:TramiteTipo')->findBy(array('id'=>$datos['tramites']));
                    $tarea['idrue'] = $institucioneducativa->getId();
                    $tarea['institucioneducativa'] = $institucioneducativa->getInstitucioneducativa();
                    $tarea['codigoLe'] = $institucioneducativa->getLeJuridicciongeografica()->getId();
                    $tarea['estado'] = $institucioneducativa->getEstadoInstitucionTipo()->getEstadoinstitucion();
                    $tarea['dependencia'] = $institucioneducativa->getDependenciaTipo()->getDependencia();
                    if($institucioneducativa->getDependenciaTipo()->getID()==2){
                        $tarea['convenio'] = $institucioneducativa->getConvenioTipo()->getConvenio();
                    }else{
                        $tarea['convenio'] = '';
                    }
                    $tarea['distrito'] = $institucioneducativa->getLeJuridicciongeografica()->getDistritoTipo()->getDistrito();
                    $tarea['fechafundacion'] = $institucioneducativa->getFechaResolucion();
                    $tarea['telefono'] = '';
                    $nivel = $wfdatos = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->createQueryBuilder('na')
                        ->select('nt')
                        ->innerJoin('SieAppWebBundle:NivelTipo', 'nt', 'with', 'nt.id = na.nivelTipo')
                        ->where('na.institucioneducativa='.$institucioneducativa->getId())
                        ->orderBy("nt.id")
                        ->getQuery()
                        ->getResult();
                    $tarea['nivel'] = $nivel;
                    $tarea['justificacion'] = $datos['observacion'];
                    $tareasDatos[] = array('flujoProceso'=>$wfd->getTramiteDetalle()->getFlujoProceso(),'datos'=>$tarea);
                    break;
                case 65:
                    //dump($datos);die;
                    /* $tarea46['informe'] = $datos['informe'];
                    $tarea46['fechainforme'] = $datos['fechainforme'];
                    $tarea46['observacion'] = $datos['observacion'];
                    $tareasDatos[] = array('flujoProceso'=>46,'datos'=>$tarea46,'tarea'=>$wfd->getTramiteDetalle()->getFlujoProceso()->getProceso()->getProcesoTipo()); */
                    break;
                case 66:
                    //dump($datos);die;
                    /* $tarea47['tipotramite'] = $wfd->getTramiteDetalle()->getTramite()->getTramiteTipo();
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
                    break; */
                case 67:
                    //dump($datos);die;
                    /* $tareasDatos[] = array('flujoProceso'=>48,'datos'=>$datos,'tarea'=>$wfd->getTramiteDetalle()->getFlujoProceso()->getProceso()->getProcesoTipo()); */
                    break;
                case 68:
                    //dump($datos);die;
                    /* $tareasDatos[] = array('flujoProceso'=>49,'datos'=>$datos, 'tarea'=>$wfd->getTramiteDetalle()->getFlujoProceso()->getProceso()->getProcesoTipo()); */
                    break;
                case 69:
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
                    
                case 70:
                    //dump($datos);die;
                    $tareasDatos[] = array('flujoProceso'=>53,'datos'=>$datos, 'tarea'=>$wfd->getTramiteDetalle()->getFlujoProceso()->getProceso()->getProcesoTipo());
                    break;
                case 71:
                    break;
                case 71:
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
        $departamento = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 8, 'paisTipoId' =>1));
        $depArray = array();
    	foreach($departamento as $d){
            if($d->getLugar() != "NO EXISTE EN CNPV 2001"){
                $depArray[$d->getid()] = $d->getlugar();
            }
        }

        if ($edificio and $edificio->getLugarTipoIdLocalidad2012())
        {
            $comunidad = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id'=>$edificio->getLugarTipoIdLocalidad2012(),'lugarNivel'=>11));
            //dump($comunidad->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId());die;
            $zona = $edificio->getZona();
            $direccion = $edificio->getDireccion();
            $iddistrito = $edificio->getDistritoTipo()->getId();
            $idcomunidad = $comunidad->getId();
            $idmunicipio = $comunidad->getLugarTipo()->getId();
            $idprovincia = $comunidad->getLugarTipo()->getLugarTipo()->getId();
            $iddepartamento = $comunidad->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
            
            $provincia = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 9, 'lugarTipo' => $iddepartamento));
            $provinciaArray = array();
    	    foreach($provincia as $p){
                if($p->getLugar() != "NO EXISTE EN CNPV 2001"){
                    $provinciaArray[$p->getid()] = $p->getlugar();
                }
            }
            $municipio = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 10, 'lugarTipo' => $idprovincia));
        	$municipioArray = array();
        	foreach($municipio as $m){
                if($m->getLugar() != "NO EXISTE EN CNPV 2001"){
    		        $municipioArray[$m->getid()] = $m->getlugar();
                }
            }
            $comunidad = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' =>11, 'lugarTipo' => $idmunicipio));
            //dump($comunidad);die;
        	$comunidadArray = array();
    	    foreach($comunidad as $c){
                if($c->getLugar() != "NO EXISTE EN CNPV 2001"){
    		        $comunidadArray[$c->getid()] = $c->getlugar();
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
                    ->setParameter('dpto', (int)$dep->getcodigo());
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
                $comparteArray = array();
            }
            return $response->setData(array(
                'iddepartamento' => $iddepartamento,
                'idprovincia' => $idprovincia,
                'idmunicipio' => $idmunicipio,
                'idcomunidad' => $idcomunidad,
                'zona'=>$zona,
                'direccion' => $direccion,
                'iddistrito' => $iddistrito,
                'departamento' => $depArray,
                'provincia' => $provinciaArray,
                'municipio' => $municipioArray,
                'comunidad' => $comunidadArray,
                'distrito' => $distritoArray,
                'comparteLe'=>$comparteArray,
            ));
        }else{
            //dump($dep);die;
            $mensaje = "No existe el Código del Edificio Educativo";
            //dump($mensaje);die;
            return $response->setData(array(
                'msg'=>$mensaje,
                'departamento' => $depArray,
            ));
        }
        
    }

    public function provinciasAction($idDepartamento){
        //dump($idDepartamento);die;
    	$em = $this->getDoctrine()->getManager();
        //$prov = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $idDepartamento));
        $prov = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 9, 'lugarTipo' => $idDepartamento));
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
            ->setParameter('dpto', (int)$dep->getcodigo());
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
        //$mun = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 3, 'lugarTipo' => $idProvincia));
        $mun = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 10, 'lugarTipo' => $idProvincia));
    	$municipio = array();
    	foreach($mun as $m){
            if($m->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $municipio[$m->getid()] = $m->getlugar();
            }
    	}
    	$response = new JsonResponse();
    	return $response->setData(array('municipio' => $municipio));
    }

    public function comunidadAction($idMunicipio){
    	$em = $this->getDoctrine()->getManager();
    	$can = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 11, 'lugarTipo' => $idMunicipio));
    	$canton = array();
    	foreach($can as $c){
            if($c->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $canton[$c->getid()] = $c->getlugar();
            }
    	}
    	$response = new JsonResponse();
    	return $response->setData(array('comunidad' => $canton));
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