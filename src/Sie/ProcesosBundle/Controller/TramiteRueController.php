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
        $lugar_tipo2012 = $em->getRepository('SieAppWebBundle:LugarTipo')->find($institucioneducativa->getLeJuridicciongeografica()->getLugarTipoIdLocalidad2012());
        $institucioneducativaNivel = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa'=>$idrue));
        $inicioForm = $this->createInicioModificacionForm($flujotipo,$tarea->getId(),$tramite,$idrue,$institucioneducativa); 
        //dump($recepcionForm);die;
        return $this->render('SieProcesosBundle:TramiteRue:inicioSolicitudModificacion.html.twig', array(
            'form' => $inicioForm->createView(),
            'institucioneducativa'=>$institucioneducativa,
            'lugarTipo2012'=> $lugar_tipo2012,
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
       
        $this->tramiteTipoArray = array(32,33,44);
        //$this->tramiteTipoArray = array(32,33,39,40,44);
        if(in_array($institucioneducativa->getDependenciaTipo()->getId(),array(0,3,4,5))){
            array_push($this->tramiteTipoArray,36,38,39,40);
            //array_push($this->tramiteTipoArray,36,38);
        }
        //dump($this->tramiteTipoArray);die;
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_rue_inicio_modificacion_guardar'))
            ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
            ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
            ->add('tramite', 'hidden', array('data' =>$tramite?$tramite->getId():$tramite ))
            ->add('idrue', 'hidden', array('data' =>$idrue ))
            ->add('tramitetipo', 'hidden', array('data' =>33 ))
            ->add('area', 'choice', array('label' => 'ÁREA GEOGRÁFICA ESTABLECIDA POR EL MUNICIPIO:','required'=>true,'multiple' => false,'expanded' => true,'choices'=>array('U'=>'Urbano','R'=>'Rural')))
            ->add('tramites','entity',array('label'=>'Tipo de Trámite:','required'=>true,'multiple' => false,'expanded' => false,'attr'=>array('class'=>'form-control','data-placeholder'=>"Seleccionar tipo de trámite"),'class'=>'SieAppWebBundle:TramiteTipo',
                'query_builder'=>function(EntityRepository $tr){
                return $tr->createQueryBuilder('tr')
                    ->where('tr.obs = :rue')
                    ->andWhere('tr.id not in (:tipo)')
                    //->andWhere('tr.id in (34,35,36,37,38,41,42,43,44,45)')
                    ->setParameter('rue','RUE')
                    ->setParameter('tipo',$this->tramiteTipoArray)
                    ->orderBy('tr.tramiteTipo','ASC');},
                'property'=>'tramiteTipo','empty_value' => 'Seleccione tipo de trámite'))
            ->add('tr', 'text')
            ->add('observacion','textarea',array('label'=>'JUSTIFICACIÓN:','required'=>true,'attr'=>array('class'=>'form-control','style' => 'text-transform:uppercase')))
            ->add('guardar','submit',array('label'=>'Enviar Solicitud'))
            ->getForm();
        return $form;
    }

    public function buscarTareaAction(Request $request){
        $id = $request->get('id');
        $tipo = $request->get('tipo');
        $ie = $request->get('ie');

        $em = $this->getDoctrine()->getManager();
        $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie);
        $tramiteTipo = $em->getRepository('SieAppWebBundle:TramiteTipo')->find($id);
        //dump($tramiteTipo);die;
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
                ->add('nivelampliar','entity',array('label'=>'Ampliacion','required'=>false,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:NivelTipo','query_builder'=>function(EntityRepository $nt){
                    return $nt->createQueryBuilder('nt')->where('nt.id in (:id)')->setParameter('id',array(11,12,13))->orderBy('nt.id','ASC');},'property'=>'nivel'))
                ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'ieNivel'=>$ienivel,
                    'tipo' => $tipo
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
                    'tramiteTipo' => $tramiteTipo,
                    'ieNivel'=>$ienivel,
                    'tipo' => $tipo
                );
                break;
            case 36://cambio de dependencia
                if($ie->getDependenciaTipo()->getId()==1){
                    $form = $form
                        ->add('dependencia','entity',array('label'=>'Dependencia','required'=>true,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:DependenciaTipo','query_builder'=>function(EntityRepository $dt){
                            return $dt->createQueryBuilder('dt')->where('dt.id = 2');},'property'=>'dependencia'))
                        ->add('conveniotipo','entity',array('label'=>'Tipo de Convenio:','required'=>true,'multiple' => false,'attr' => array('class' => 'form-control'),'empty_value'=>'Seleccione convenio','class'=>'SieAppWebBundle:ConvenioTipo','query_builder'=>function(EntityRepository $ct){
                            return $ct->createQueryBuilder('ct')->where('ct.id <>99')->orderBy('ct.convenio','ASC');},'property'=>'convenio'))
                        ->getForm();
                }else{
                    $form = $form
                    ->add('dependencia','entity',array('label'=>'Dependencia','required'=>true,'multiple' => true,'expanded' => true,'class'=>'SieAppWebBundle:DependenciaTipo','query_builder'=>function(EntityRepository $dt){
                        return $dt->createQueryBuilder('dt')->where('dt.id = 1');},'property'=>'dependencia'))
                    ->getForm();
                }
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'dependencia'=>$ie->getDependenciaTipo()
                );
                break;
            case 37://cambio de nombre
                $form = $form
                    ->add('nuevo_nombre','text',array('label'=>'Nuevo nombre de la Unidad Educativa:','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'nombre_actual'=>$ie->getInstitucioneducativa()
                );
                break;
            case 38://cambio de jurisdiccion administrativa
                $this->iddep = $ie->getLeJuridicciongeografica()->getDistritoTipo()->getDepartamentoTipo();
                $form = $form
                    ->add('nuevo_distrito','entity',array('label'=>'Nuevo Distrito:','required'=>true,'multiple' => false,'attr' => array('class' => 'form-control'),'empty_value'=>'Seleccione nuevo distrito','class'=>'SieAppWebBundle:DistritoTipo','query_builder'=>function(EntityRepository $dt){
                        return $dt->createQueryBuilder('dt')->where('dt.departamentoTipo = :id')->setParameter('id',$this->iddep)->orderBy('dt.distrito','ASC');},'property'=>'distrito'))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'distrito'=>$ie->getLeJuridicciongeografica()->getDistritoTipo()->getDistrito()
                );
                break;
            case 39://Fusion
                //ampliacion y cierre definitivo
                break;
            case 40://desglose
                break;
            case 41://cambio de infraestructura
                $form = $form
                    ->add('lejurisdiccion', 'text', array('label' => 'Código Edificio Educativo:','required'=>false,'attr' => array('class' => 'form-control validar','maxlength'=>8)))
                    ->add('departamento2012','entity',array('label'=>'Departamento:','required'=>true,'attr' => array('class' => 'form-control'),'class'=>'SieAppWebBundle:LugarTipo','query_builder'=>function(EntityRepository $lt){
                        return $lt->createQueryBuilder('lt')->where('lt.lugarNivel = 8')->andWhere('lt.paisTipoId=1')->orderBy('lt.id','ASC');},'property'=>'lugar','empty_value' => 'Seleccione departamento'))
                    ->add('provincia2012', 'choice', array('label' => 'Provincia:','required'=>true,'attr' => array('class' => 'form-control')))
                    ->add('municipio2012', 'choice', array('label' => 'Municipio:','required'=>true, 'attr' => array('class' => 'form-control')))
                    ->add('comunidad2012', 'choice', array('label' => 'Comunidad:','required'=>true, 'attr' => array('class' => 'form-control')))
                    ->add('departamento2001','entity',array('label'=>'Departamento:','required'=>true,'attr' => array('class' => 'form-control'),'class'=>'SieAppWebBundle:LugarTipo','query_builder'=>function(EntityRepository $lt){
                        return $lt->createQueryBuilder('lt')->where('lt.lugarNivel = 1')->andWhere('lt.paisTipoId=1')->orderBy('lt.id','ASC');},'property'=>'lugar','empty_value' => 'Seleccione departamento'))
                    ->add('provincia2001', 'choice', array('label' => 'Provincia:','required'=>true,'attr' => array('class' => 'form-control')))
                    ->add('municipio2001', 'choice', array('label' => 'Municipio:','required'=>true, 'attr' => array('class' => 'form-control')))
                    ->add('canton2001', 'choice', array('label' => 'Cantón:','required'=>true, 'attr' => array('class' => 'form-control')))
                    ->add('localidad2001', 'choice', array('label' => 'Localidad/Comunidad:','required'=>true,'attr' => array('class' => 'form-control')))
                    ->add('zona', 'text', array('label' => 'Zona:','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
                    ->add('direccion', 'text', array('label' => 'Dirección:','required'=>true,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'lugarTipo2001'=>$ie->getLeJuridicciongeografica()->getLugarTipoLocalidad(),
                    'lugarTipo2012'=>$em->getRepository('SieAppWebBundle:LugarTipo')->find($ie->getLeJuridicciongeografica()->getLugarTipoIdLocalidad2012()),
                    'zona' =>$ie->getLeJuridicciongeografica()->getZona(),
                    'direccion' =>$ie->getLeJuridicciongeografica()->getDireccion()
                );
                break;
            case 42://cierre temporal
            case 43://cierre definitivo
                if($ie->getEstadoinstitucionTipo()->getId() == 10){
                    $form = $form
                        ->add('estadoinstitucion', 'checkbox', array('label' => 'CERRADA','required'  => true));
                    if($tipo == 'fusion'){
                        $form = $form
                            ->add('siefusion', 'text', array('label' => 'Código SIE de la Unidad Educativa a Cerrar definitivamente:','required'=>true,'attr' => array('class' => 'form-control validar','maxlength'=>8)));
                    }
                    $form = $form    
                        ->getForm()
                        ->createView();    
                }else{
                    $form = null;
                }
                $data = array(
                    'form' => $form,
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'estadoinstitucion'=>$ie->getEstadoinstitucionTipo(),
                    'tipo' => $tipo
                );
                break;
            case 44://Reapertura
                if($ie->getEstadoinstitucionTipo()->getId() == 19){
                    $form = $form
                        ->add('estadoinstitucion', 'checkbox', array('label' => 'ABIERTA','required'  => true))
                        ->getForm()
                        ->createView();    
                }else{
                    $form = null;
                }
                $data = array(
                    'form' => $form,
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'estadoinstitucion'=>$ie->getEstadoinstitucionTipo()
                );
                break;
            case 45://nuevo certifcado rue
                $data = array(
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                );
                break;
            case 54://apertura de unidad educativa
                $data = array(
                    'id' => $id,
                    'tramiteTipo' => $tramiteTipo,
                    'tipo' => $tipo
                );
                break;
        }
        
        return $this->render('SieProcesosBundle:TramiteRue:modificaTramite.html.twig', $data);
    }

    public function buscarRequisitosAction(Request $request){
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        if($id != 54){
            $ie = $request->get('ie');
            $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ie);
        }else{
            $dependencia = $request->get('dependencia');
            $constitucion = $request->get('constitucion');
            //dump($request);die;
        }        
        $tramitetipo = $em->getRepository('SieAppWebBundle:TramiteTipo')->find($id);
        $requisitos = array();
        $form = $this->createFormBuilder();
        switch ($id){
            case 34://ampliacion de nivel
                $form = $form
                    ->add('i_solicitud_ampliar', 'file', array('label' => 'Adjuntar Solicitud de Ampliación de Nivel (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar solicitud",'accept'=>"application/pdf,.img,.jpg")))
                    ->add('i_alquiler_ampliar', 'choice', array('label' => 'Infraestructura arrendada:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
                    ->add('i_contrato_ampliar', 'file', array('label' => 'Adjuntar Copia notariada de arrendamiento (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar contrato",'accept'=>"application/pdf,.img,.jpg")))
                    ->add('i_certificado_ampliar', 'checkbox', array('label' => 'Original de Certificado RUE','required'  => false))
                    ->add('ii_planos_ampliar', 'checkbox', array('label' => 'Planos arquitectónicos','required'  => false))
                    ->add('ii_infra_ampliar', 'checkbox', array('label' => 'Actualización de datos de infraestructura en el SIE','required'  => false))
                    ->getForm();
                $requisitos = array('legal'=>true,'infra'=>true,'admi'=>false);
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo()
                    //'ieNivel'=>$ienivel
                );
                break;
            case 35://Reduccion de Nivel
                $form = $form
                    ->add('i_solicitud_reducir', 'file', array('label' => 'Adjuntar Solicitud de Reducción de Nivel (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar solicitud",'accept'=>"application/pdf,.img,.jpg")))
                    ->add('i_alquiler_reducir', 'choice', array('label' => 'Infraestructura arrendada:','required'=>false,'empty_value'=>false,'multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
                    ->add('i_contrato_reducir', 'file', array('label' => 'Adjuntar Copia notariada de arrendamiento (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar contrato",'accept'=>"application/pdf,.img,.jpg")))
                    ->add('i_certificado_reducir', 'checkbox', array('label' => 'Original de Certificado RUE','required'  => false))
                    ->add('ii_planos_reducir', 'checkbox', array('label' => 'Planos arquitectónicos','required'  => false))
                    ->add('ii_infra_reducir', 'checkbox', array('label' => 'Actualizacion de datos de infraestructura en el SIE','required'  => false))
                    ->getForm();
                $requisitos = array('legal'=>true,'infra'=>true,'admi'=>false);
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo()
                );
                break;
            case 36://cambio de dependencia
                $requisitos = array('legal'=>true,'infra'=>false,'admi'=>false);
                if($ie->getDependenciaTipo()->getId()==1){
                    $form = $form
                        ->add('i_solicitud_dependencia', 'file', array('label' => 'Adjuntar Solicitud de Cambio de Dependencia (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar solicitud",'accept'=>"application/pdf,.img,.jpg")))
                        ->add('i_actafundacion', 'file', array('label' => 'Adjuntar Acta de Fundación (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar contrato",'accept'=>"application/pdf,.img,.jpg")))
                        ->add('i_folio_dependencia', 'checkbox', array('label' => 'Folio Real emitido po Derechos Reales a nombre del Gobierno Autónomo Municipal correspondiente.','required'  => false))
                        ->add('i_nrofolio_dependencia', 'text', array('label' => 'Nro. de Folio Real:','required'=>false,'attr' => array('maxlength'=>10)))
                        ->add('i_esalquiler_dependencia', 'choice', array('label' => 'Infraestructura arrendada:','required'=>false,'empty_value'=>false,'multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
                        ->add('i_contrato_dependencia', 'file', array('label' => 'Adjuntar Copia notariada de contrato de arrendamiento (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar contrato",'accept'=>"application/pdf,.img,.jpg")))
                        ->add('i_convenio_dependencia', 'file', array('label' => 'Adjuntar convenio vigente de prestación de servicios (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar contrato",'accept'=>"application/pdf,.img,.jpg")))
                        ->add('i_certificacion_gam', 'file', array('label' => 'Adjuntar Certificación emitida por el Gobierno Autónomo Municipal Correspondiente (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar contrato",'accept'=>"application/pdf,.img,.jpg")))
                        ->add('i_certificacionde_convenio', 'file', array('label' => 'Adjuntar Certificación de convenio emitida por el Responsable de Institución Prestadora de Servicios (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar contrato",'accept'=>"application/pdf,.img,.jpg")))
                        ->add('i_convenio_administracion', 'checkbox', array('label' => 'Convenio de administración de infraestructura firmado entre el Gobierno Autónomo Municipal correspondiente y la Institución prestadora del servicio (si corresponde):','required'  => false))
                        ->add('i_acta_constitucion', 'checkbox', array('label' => 'Copia Notariada del Acta de Constitución y estatutos de la Institución prestadora de servicios:','required'  => false))
                        ->add('i_registro_culto', 'checkbox', array('label' => 'En caso de Institucionaes Religiosas, fotocopia legalizada del certificado de registro de culto emitido por el Ministerio de Relaciones Exteriores:','required'  => false))
                        ->add('i_org_nogubernamental', 'checkbox', array('label' => 'Para instituciones no gubernamentales, fotocopia legalizada del certificado de registro emitido por el Viceministerio de Inversion Pública y Financiamiento Externo:','required'  => false))
                        ->add('i_form_fundaempresa', 'checkbox', array('label' => 'Copia legalizada del formulario de registro de comercio, emitido por FUNDAEMPRESA, (si corresponde):','required'  => false))
                        ->add('nro_fundaempresa', 'text', array('label' => 'Nro. de Matrícula de Comercio:','required'=>false,'attr' => array('class' => 'form-control')))
                        ->add('fecha_fundaempresa', 'text', array('label' => 'Fecha de Inscripción:','required'=>false,'attr' => array('class' => 'form-control date')))
                        ->add('i_fotocopia_nit', 'checkbox', array('label' => 'Fotocopia legalizada del certificado del Número de Identificación Tributaria NIT, (si corresponde):','required'  => false))
                        ->add('nit_dependencia', 'text', array('label' => 'Nro. de N.I.T.:','required'=>false,'attr' => array('class' => 'form-control validar')))
                        ->add('i_balance_apertura', 'checkbox', array('label' => 'Balance de apertura de la unidad educativa, sellado por el Servicio de Impuestos Nacionales SIN (si corresponde).','required'  => false))
                        ->add('i_testimonioconvenio', 'text', array('label' => 'Nro. del Testimonio de personeria jurídica de la entidad prestadora de servicio:','required'=>false,'attr' => array('class' => 'form-control validar')))
                        ->add('fecha_testimonioconvenio', 'text', array('label' => 'Fecha del testimonio :','required'=>false,'attr' => array('class' => 'form-control date')))
                        ->add('i_certificadorue_dependencia', 'checkbox', array('label' => 'Original de Certificado RUE (en caso de extravío respaldado con los informes de justificación correspondiente).','required'  => false))
                        ->getForm();
                }else{
                    $form = $form
                        ->add('i_informe_dependencia', 'file', array('label' => '1.1 Adjuntar Informe estableciendo aspectos técnicos de infraestructura y de cambio de dependencia (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar solicitud",'accept'=>"application/pdf,.img,.jpg")))
                        ->add('i_certificadorue_dependencia', 'checkbox', array('label' => 'Original de Certificado RUE (en caso de extravío respaldado con los informes de justificación correspondiente)','required'  => false))
                        ->getForm();
                }
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo()
                );
                //dump($data);die;
                break;
            case 37://cambio de nombre
                $requisitos = array('legal'=>true,'infra'=>false,'admi'=>false);
                $form = $form
                    ->add('i_solicitud_cn', 'file', array('label' => 'Adjuntar Solicitud de Cambio de Nombre (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar solicitud",'accept'=>"application/pdf,.img,.jpg")))
                    ->add('i_certificadorue_cn', 'checkbox', array('label' => 'Original de Certificado RUE (en caso de extravío respaldado con los informes de justificación correspondiente).','required'  => false))
                    ->add('i_certdefuncion_cn', 'file', array('label' => 'Adjuntar Certificado de defunción, (en caso de que la Unidad Educativa lleve nombre de persona) (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar certificado",'accept'=>"application/pdf,.img,.jpg")));
                if($ie->getDependenciaTipo()->getId() == 1 or $ie->getDependenciaTipo()->getId() == 2){
                    $form = $form
                        ->add('i_ley_cn', 'checkbox', array('label' => 'Ley emitida por el Gobierno Autónomo Municipal que autoriza el cambio de nombre.','required'  => false));
                }
                if($ie->getDependenciaTipo()->getId() == 2 or $ie->getDependenciaTipo()->getId() == 3){
                    $form = $form
                        ->add('i_actaconstitucion_cn', 'checkbox', array('label' => 'Copia Notariada del Acta de Constitución y estatutos de la Unidad Educativa:','required'  => false))
                        ->add('i_copianit_cn', 'checkbox', array('label' => 'Copia computarizada del N.I.T., (si corresponde)','required'  => false))
                        ->add('i_nit_cn', 'text', array('label' => 'Nro. de N.I.T.:','required'=>false,'attr' => array('class' => 'form-control validar')))
                        ->add('i_nrotestimonio_cn', 'text', array('label' => 'Nro. de testimonio de poder de representante legal:','required'=>false,'attr' => array('class' => 'form-control validar')))
                        ->add('i_fecha_testimonio_cn', 'text', array('label' => 'Fecha del testimonio:','required'=>false,'attr' => array('class' => 'form-control date')))
                        ->add('i_licenciafuncionamiento_cn', 'checkbox', array('label' => 'Copia legalizada de la Licencia de Funcionamiento Municipal.','required'  => false));
                }
                if($ie->getDependenciaTipo()->getId() == 3){
                    $form = $form
                        ->add('i_form_fundaempresa_cn', 'checkbox', array('label' => 'Copia del formulario de registro de comercio, emitido por FUNDAEMPRESA.','required'  => false))
                        ->add('i_nro_fundaempresa_cn', 'text', array('label' => 'Nro. de Matrícula de Comercio:','required'=>false,'attr' => array('class' => 'form-control')))
                        ->add('i_fecha_fundaempresa_cn', 'text', array('label' => 'Fecha de Inscripción:','required'=>false,'attr' => array('class' => 'form-control date')));
                }
                $form = $form->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo()
                );
                break;
            case 38://cambio de jurisdiccion administrativa
                $requisitos = array('legal'=>true,'infra'=>true,'admi'=>true);
                $form = $form
                    ->add('i_resolucion_jur', 'file', array('label' => 'Adjuntar Resolucion Administrativa de Autorización de apertura y funcionamiento emitida por la DDE (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar resolución",'accept'=>"application/pdf,.img,.jpg")))
                    ->add('i_certificacion_jur', 'file', array('label' => 'Adjuntar Certificación emitida por el Gobierno Autónomo Municipal correspondiente estableciendo si la unidad educativa cuyo cambio de jurisdicción administrativa es del área rural o urbana (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar certificación",'accept'=>"application/pdf,.img,.jpg")))
                    ->add('i_area_jur', 'choice', array('label' => 'Área geográfica:','required' => false,'empty_value' => false,'multiple' => false,'expanded' => true,'choices'=>array('RURAL'=>'RURAL','URBANA'=>'URBANA')))
                    ->add('i_certificadorue_jur', 'checkbox', array('label' => 'Original de Certificado RUE (en caso de extravío respaldado con los informes de justificación correspondiente).','required'  => false))
                    ->add('ii_planos_jur', 'checkbox', array('label' => '2.1 Planos arquitectónicos (especificando los ambientes), aprobados por el Gobierno Autónomo Municipal para infraestructura','required'  => false))
                    ->add('ii_inventario_jur', 'checkbox', array('label' => 'Inventario del mobiliario y equipamento de la unidad educativa de acuerdo a norma.','required'  => false))
                    ->add('iii_partemensual_jur', 'checkbox', array('label' => 'Parte mensual.','required'  => false));
                if($ie->getDependenciaTipo()->getId() == 2){
                    $form = $form
                        ->add('i_cert_convenio_jur', 'checkbox', array('label' => 'Certificación de convenio emitido por el responsable de la Institución prestadora de Servicios.','required'  => false));
                }
                $form = $form->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo()
                );
                break;
            case 39://Fusion
                break;
            case 40://desglose
                break;
            case 41://cambio de infraestructura F-P-C
                $requisitos = array('legal'=>true,'infra'=>true,'admi'=>false);
                $form = $form
                    ->add('i_solicitud_infra', 'file', array('label' => 'Adjuntar Solicitud de Cambio de Infraestructura (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar solicitud",'accept'=>"application/pdf,.img,.jpg")))
                    ->add('i_certificacion_infra', 'file', array('label' => 'Adjuntar Certificación emitida por el Gobierno Autónomo Municipal correspondiente estableciendo si la unidad educativa cuya solicitud de cambio de infraestructura es del área rural o urbana (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar certificación",'accept'=>"application/pdf,.img,.jpg")));
                if($ie->getDependenciaTipo()->getId() == 2){
                    $form = $form
                        ->add('i_certificacionconvenio_infra', 'file', array('label' => 'Adjuntar Certificación de convenio emitida por el reponsable de la Entidad Prestadora de Servicios (solo convenio) (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar certificación",'accept'=>"application/pdf,.img,.jpg")));
                }
                $form = $form
                    ->add('i_resolucion_infra', 'file', array('label' => 'Adjuntar Fotocopia legalizada de la Resolucion Administrativa de Autorización de funcionamiento emitida por la DDE (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar resolución",'accept'=>"application/pdf,.img,.jpg")))
                    ->add('i_certificadorue_infra', 'checkbox', array('label' => 'Original de Certificado RUE (en caso de extravío respaldado con los informes de justificación correspondiente).','required'  => false))
                    ->add('ii_folio_infra', 'checkbox', array('label' => 'Copia legalizada del testimonio o folio real emitido por Derechos Reales a nombre de la Entidad prestadora de servicio (fiscal o convenio) o propietario (persona natural o jurídica, en caso de Unidad Educativa Provada).','required'  => false))
                    ->add('ii_nrofolio_infra', 'text', array('label' => 'Nro. de Folio:','required'=>false,'attr' => array('class' => 'form-control validar','maxlength'=>10)))
                    ->add('ii_planos_infra', 'checkbox', array('label' => 'Planos arquitectónicos (especificando los ambientes), aprobados por el Gobierno Autónomo Municipal para infraestructura','required'  => false))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo()
                );
                break;
            case 42://cierre temporal
            case 43://cierre definitivo
                $requisitos = array('legal'=>true,'infra'=>false,'admi'=>false);
                $form = $form
                    ->add('i_solicitud_cierre', 'file', array('label' => 'Adjuntar Solicitud de cierre temporal o definitivo (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar solicitud",'accept'=>"application/pdf,.img,.jpg")))
                    ->add('i_resolucion_cierre', 'file', array('label' => 'Adjuntar Fotocopia legalizada de la Resolucion Administrativa de Autorización de apertura y funcionamiento de la unidad educativa (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar resolución",'accept'=>"application/pdf,.img,.jpg")))
                    ->add('i_archivos_cierre', 'checkbox', array('label' => 'Archivos electrónicos e impresos actulizados de toda la documentación d elos estudiantes que cursaron sus estudios en la unidad educativa (RUDE, centralizador de calificaciones, boletines y otros).','required'  => false))
                    ->add('i_certificadorue_cierre', 'checkbox', array('label' => 'Original de Certificado RUE (en caso de extravío respaldado con los informes de justificación correspondiente).','required'  => false))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo()
                );
                break;
            case 44://Reapertura
                $requisitos = array('legal'=>true,'infra'=>false,'admi'=>false);
                $form = $form
                    ->add('i_solicitud_apertura', 'file', array('label' => 'Adjuntar Solicitud de Reapertura (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar solicitud",'accept'=>"application/pdf,.img,.jpg")))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                );
                break;
            case 45://nuevo certifcado rue
                $requisitos = array('legal'=>true,'infra'=>false,'admi'=>false);
                $form = $form
                    ->add('i_solicitud_nuevorue', 'file', array('label' => 'Adjuntar Informe Técnico circunstanciado de extravío del Original del Certificado RUE (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar solicitud",'accept'=>"application/pdf,.img,.jpg")))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $ie->getDependenciaTipo()
                );
                break;
            case 54://apertura
                $requisitos = array('legal'=>true,'infra'=>true,'admi'=>true);
                //$dependencia = 1;  
                if($dependencia == 1){
                    $form = $form
                        ->add('i_solicitud_apertura', 'file', array('label' => 'Adjuntar Solicitud de apertura dirigida a la Dirección Distrital Educativa correspondiente (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar solicitud",'accept'=>"application/pdf,.img,.jpg")))
                        ->add('i_folio_apertura', 'checkbox', array('label' => 'Copia legalizada del Testimonio o Folio Real emitido po Derechos Reales a nombre del Gobierno Autónomo Municipal correspondiente.','required'  => false))
                        ->add('i_actafundacion_apertura', 'file', array('label' => 'Adjuntar Acta de Fundación de la Unidad Educativa (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar contrato",'accept'=>"application/pdf,.img,.jpg")));
                }
                if($dependencia == 2){
                    $form = $form
                        ->add('i_solicitud_apertura', 'file', array('label' => 'Adjuntar Solicitud de apertura dirigida a la Dirección Distrital Educativa correspondiente por la o el representante legal de la entidad prestadora de servicio (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar solicitud",'accept'=>"application/pdf,.img,.jpg")))
                        ->add('i_representante_apertura', 'checkbox', array('label' => 'Copia legalizada del Testimonio de Poder del representante legal.','required'  => false))
                        ->add('i_actafundacion_apertura', 'file', array('label' => 'Adjuntar Copia legalizada del Acta de Fundación (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar contrato",'accept'=>"application/pdf,.img,.jpg")))
                        ->add('i_folio_apertura', 'checkbox', array('label' => 'Copia legalizada del Testimonio o Folio Real emitido po Derechos Reales a nombre de la entidad prestadora de servicio.','required'  => false))
                        ->add('i_convenio_apertura', 'checkbox', array('label' => 'Convenio vigente de prestación de servicios educativos firmado entre la Institución Prestadora de Servicios y el Ministerio de Educación (ME) o el Director Departamental de Educación, según corresponda.','required'  => false))
                        ->add('i_convenioadministracion_apertura', 'checkbox', array('label' => 'Convenio de administración de infraestructura firmado entre el Gobierno Autónomo Municipal correspondiente y la institución prestadora del servicio (si corresponde).','required'  => false))
                        ->add('i_constitucion_apertura', 'checkbox', array('label' => 'Copia notaria actualizada del Acta de Constitución y estatutos de la Institución prestadora de servicios.','required'  => false))
                        ->add('i_registro_culto_apertura', 'checkbox', array('label' => 'En caso de Institucionaes Religiosas, fotocopia legalizada del certificado de registro de culto emitido por el Ministerio de Relaciones Exteriores:','required'  => false))
                        ->add('i_org_nogubernamental_apertura', 'checkbox', array('label' => 'Para organizaciones no gubernamentales, fotocopia legalizada del certificado de registro del Viceministerio de Inversion Pública y Financiamiento Externo.','required'  => false))
                        ->add('i_form_fundaempresa_apertura', 'checkbox', array('label' => 'Copia legalizada del formulario virtual de registro de comercio, emitido por FUNDAEMPRESA, (si corresponde):','required'  => false))
                        ->add('nro_fundaempresa_apertura', 'text', array('label' => 'Nro. de Matrícula de Comercio:','required'=>false,'attr' => array('class' => 'form-control')))
                        ->add('fecha_fundaempresa_apertura', 'text', array('label' => 'Fecha de Inscripción:','required'=>false,'attr' => array('class' => 'form-control date')))
                        ->add('i_fotocopia_nit_apertura', 'checkbox', array('label' => 'Fotocopia legalizada del certificado del Número de Identificación Tributaria NIT, (si corresponde):','required'  => false))
                        ->add('nit_apertura', 'text', array('label' => 'Nro. de N.I.T.:','required'=>false,'attr' => array('class' => 'form-control validar')))
                        ->add('i_balance_apertura', 'checkbox', array('label' => 'Balance de apertura de la unidad educativa, sellado por el Servicio de Impuestos Nacionales SIN (si corresponde).','required'  => false))
                        ->add('i_testimonioconvenio', 'text', array('label' => 'Nro. del Testimonio de personeria jurídica emitido por la entidad correspondiente:','required'=>false,'attr' => array('class' => 'form-control validar')));
                }
                if( $dependencia == 1 or $dependencia == 2){
                    $form = $form
                        ->add('i_certificacion_apertura', 'checkbox', array('label' => 'Certificación  emitida por el Gobierno Autónomo Municipal correspondiente, estableciendo si la unidad educativa cuya apertura y funcionamiento es del área rural o urbana, señalando la dirección exacta de la infraestructura.','required'  => false))
                        ->add('i_area_apertura', 'choice', array('label' => 'Área geográfica:','required' => false,'empty_value' => false,'multiple' => false,'expanded' => true,'choices'=>array('RURAL'=>'RURAL','URBANA'=>'URBANA')))    
                        ->add('i_compromiso_apertura', 'checkbox', array('label' => 'Compromiso Municipal de dotación y mantenimiento de infraestructura y equipamiento para la unidad educativa a crearse (en caso de unidades educativas fiscales y de convenio).','required'  => false));
                }
                if($dependencia == 3){
                    /**
                     * 1 asociaciones o fundaciones sin fines de lucro - ong
                     * 2 instituciones religiosas
                     * 3 s.a. / s.r.l.
                     * 4 unipersonal
                     * 5 cooperativa
                     * 6 de convenio (suscrito entre estados)
                     */
                    $form = $form
                        ->add('i_solicitud_apertura', 'file', array('label' => 'Adjuntar Solicitud de apertura de la unidad educativa privada, dirigida por el propietario o representante legal, a la Dirección Distrital Educativa correspondiente (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar solicitud",'accept'=>"application/pdf,.img,.jpg")))
                        ->add('i_fotocopia_nit_apertura', 'checkbox', array('label' => 'Copia de registro del Número de Identificación Tributaria (NIT).','required'  => false))
                        ->add('nit_apertura', 'text', array('label' => 'Nro. de N.I.T.:','required'=>false,'attr' => array('class' => 'form-control validar')))
                        ->add('i_balance_apertura', 'checkbox', array('label' => 'Copia de Balance de apertura, sellado por el Servicio de Impuestos Nacionales SIN.','required'  => false))
                        ->add('i_representante_apertura', 'checkbox', array('label' => 'Copia legalizada del Testimonio de Poder del representante legal.','required'  => false))
                        ->add('i_copia_ci_apertura', 'checkbox', array('label' => 'Copia de Cedula de Identidad del representante legal.','required'  => false))
                        ->add('ci_apertura', 'text', array('label' => 'Nro. de C.I.:','required'=>false,'attr' => array('class' => 'form-control validar')))
                        ->add('i_funcionamiento_apertura', 'file', array('label' => 'Adjuntar Copia legalizada de la Licencia de Funcionamiento Municipal (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar solicitud",'accept'=>"application/pdf,.img,.jpg")))
                        ->add('i_estatutos_apertura', 'checkbox', array('label' => 'Copia notariada de la escritura de constitución y sus estatutos.','required'  => false))
                        ->add('i_empleadores_apertura', 'file', array('label' => 'Adjuntar Registro obligatorio de empleadores (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar solicitud",'accept'=>"application/pdf,.img,.jpg")))
                        ->add('ii_alquiler_apertura', 'choice', array('label' => 'Infraestructura arrendada:','multiple' => false,'expanded' => true,'choices'=>array('SI'=>'SI','NO'=>'NO')))
                        ->add('ii_folio_apertura', 'checkbox', array('label' => 'Copia legalizada del Testimonio o Folio Real emitido po Derechos Reales estableciendo que la infraestructura esta destinada a la unidad educativa y pertenece a los propietarios de la misma.','required'  => false))
                        ->add('ii_contrato_apertura', 'file', array('label' => 'Adjuntar Copia notariada de compromiso de contrato por un plazo no menor a seis años (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar contrato",'accept'=>"application/pdf,.img,.jpg")))
                        ->add('iii_reglamento_apertura', 'checkbox', array('label' => '3.1 Reglamento interno de funcionamiento de disposiciones administrativas.','required'  => false))
                        ->add('iii_convivencia_apertura', 'checkbox', array('label' => '3.2 Reglamento de convivencia armónica y pacífica que contenga los mecanismos preventivos, medidas reparadoras de solución de conflictos, tipificación de infracciones, procedimientos y sanciones.','required'  => false))
                        ->add('iii_manual_apertura', 'checkbox', array('label' => '3.3 Manual de organización y funciones (misión, objetivos, estructura organizativa, objetivos de áreas, descripción y objetivos de funciones, superviciones y coordinación).','required'  => false))
                        ->add('iii_kardex_apertura', 'checkbox', array('label' => '3.4 Kardex de maestras y maestros y personal administrativo.','required'  => false))
                        ->add('iii_sippase_apertura', 'checkbox', array('label' => '3.5 Cetificado SIPPASE y REJAP del plantel docente y administrativo (en mérito al D.S. Nro. 1302 y 1320).','required'  => false))
                        ->add('iii_contratos_apertura', 'checkbox', array('label' => '3.7 Contratos firmados por el personal docente con pertinencia académica.','required'  => false));
                    if($constitucion != 5 ){
                        $form = $form
                            ->add('i_personeria_apertura', 'checkbox', array('label' => 'Copia legalizada del Testimonio de la Personería Jurídica.','required'  => false));
                    }
                    if($constitucion == 5){
                        $form = $form
                            ->add('i_afcoop_apertura', 'file', array('label' => 'Adjuntar Copia legalizada de la Resolución de Personalidad Jurídica Conferida por AFCOOP e inscrita en el Registro Estatal de Cooperativas (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar solicitud",'accept'=>"application/pdf,.img,.jpg")));
                    }
                    if($constitucion == 2 or $constitucion == 6){
                        $form = $form
                            ->add('i_certificacionculto_apertura', 'file', array('label' => 'Adjuntar Certificación de registro de culto emitido por el Ministerio de Relaciones Exteriores. (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar solicitud",'accept'=>"application/pdf,.img,.jpg")));
                    }
                    if($constitucion == 3 or $constitucion == 4 or $constitucion == 6){
                        $form = $form
                            ->add('i_form_fundaempresa_apertura', 'checkbox', array('label' => 'Copia del formulario virtual de Registro de Comercio, emitida por FUNDAEMPRESA.','required'  => false))
                            ->add('nro_fundaempresa_apertura', 'text', array('label' => 'Nro. de Matrícula de Comercio:','required'=>false,'attr' => array('class' => 'form-control')))
                            ->add('fecha_fundaempresa_apertura', 'text', array('label' => 'Fecha de Inscripción:','required'=>false,'attr' => array('class' => 'form-control date')));
                    }
                    if($constitucion == 6){
                        $form = $form
                            ->add('i_convenio_apertura', 'checkbox', array('label' => 'Copia legalizada de convenio entre estados.','required'  => false));
                    }
                }
                $form = $form
                    ->add('ii_inventario_apertura', 'checkbox', array('label' => '2.1 Inventario de mobiliario y equipamento de la Unidad Educativa de acuerdo a norma.','required'  => false))
                    ->add('ii_planos_apertura', 'checkbox', array('label' => '2.2 Copia legalizada de los Planos arquitectónicos (especificando los ambientes), aprobados por el Gobierno Autónomo Municipal correspondiente, para infraestructura de ambientes propios, que contemplen la adecuación progresiva para la atención de la población con discapacidad leve, en el caso de área dispersa, el cumplimiento de este requisito se incluirá en el informe del Director Distrital de Educación correspondiente.','required'  => false))
                    ->add('iii_poa_apertura', 'checkbox', array('label' => 'Plan Operativo Anual (POA) de la gestión en la que iniarán actividades, firmado por la o el Director Distrital de Educación correspondiente.','required'  => false))
                    ->getForm();
                $data = array(
                    'form' => $form->createView(),
                    'id' => $id,
                    'tramitetipo' => $tramitetipo,
                    'requisitos' => $requisitos,
                    'dependencia' => $em->getRepository('SieAppWebBundle:DependenciaTipo')->find($dependencia),
                    'constitucion' => $constitucion
                );
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

    public function upload($file,$ie){
        // check if the file exists
        if(!empty($file)){
            $new_name = date('YmdHis').rand(1,99).'.'.$file->getClientOriginalExtension();
            $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/'.$ie;
            //$root_bth_path = 'uploads/archivos/flujos/'.$ie;
            if (!file_exists($directorio)) {
                mkdir($directorio, 0775, true);
            }
            $directoriomove = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/'.$ie.'/rue/';
            //$destination_path = 'uploads/archivos/flujos/'.$ie.'/bth/';
            if (!file_exists($directoriomove)) {
                mkdir($directoriomove, 0775, true);
            }
            $file->move($directoriomove, $new_name);
        }else{
            $new_name='default-2x.pdf';
        }
        return $new_name;
    }
    public function inicioSolicitudModificacionGuardarAction(Request $request)
    {
        $this->session = $request->getSession();
        $form = $request->get('form');
        $files = $request->files->get('form');
        //dump($form,$files);die;
        $tramites = json_decode($form['tr'],true);
        //dump($tramites,$form);die;
        $em = $this->getDoctrine()->getManager();

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
        $query = $em->getConnection()->prepare('SELECT ie.id,ie.institucioneducativa,ie.le_juridicciongeografica_id,ie.estadoinstitucion_tipo_id,et.estadoinstitucion,ie.dependencia_tipo_id,dt.dependencia,ie.convenio_tipo_id,ct.convenio,ies.telefono1
                FROM institucioneducativa ie
                join institucioneducativa_sucursal ies on ie.id=ies.institucioneducativa_id
                join estadoinstitucion_tipo et on ie.estadoinstitucion_tipo_id=et.id
                join dependencia_tipo dt on dt.id=ie.dependencia_tipo_id
                left join convenio_tipo ct on ct.id=ie.convenio_tipo_id
                where ies.gestion_tipo_id=' . $this->session->get('currentyear') .'
                and ie.id='. $form['idrue']);
                $query->execute();
        $institucioneducativa = $query->fetchAll();
        $query = $em->getConnection()->prepare('SELECT nt.id,nt.nivel
                FROM institucioneducativa_nivel_autorizado ien
                join nivel_tipo nt on ien.nivel_tipo_id = nt.id
                WHERE ien.institucioneducativa_id='. $form['idrue']);
                $query->execute();
        $ieNivelAutorizado = $query->fetchAll();
        $query = $em->getConnection()->prepare('SELECT le.id,le.zona,le.direccion,le.distrito_tipo_id,dt.distrito,
                lt.id as localidad2001_id,lt.lugar as localidad2001,lt1.id as canton2001_id,lt1.lugar as canton2001,lt2.id as municipio2001_id,lt2.lugar as municipio2001,lt3.id as provincia2001_id,lt3.lugar as provincia2001,lt4.id as departamento2001_id,lt4.lugar as departamento2001,lt.area2001,
                lt5.id as comunidad2012_id,lt5.lugar as comunidad2012,lt6.id as municipio2012_id,lt6.lugar as municipio2012,lt7.id as provincia2012_id,lt7.lugar as provincia2012,lt8.id as departamento2012_id,lt8.lugar as departamento2012
                FROM jurisdiccion_geografica le
                join distrito_tipo dt on dt.id=le.distrito_tipo_id
                join lugar_tipo lt on lt.id=le.lugar_tipo_id_localidad
                join lugar_tipo lt1 on lt1.id=lt.lugar_tipo_id
                join lugar_tipo lt2 on lt2.id=lt1.lugar_tipo_id
                join lugar_tipo lt3 on lt3.id=lt2.lugar_tipo_id
                join lugar_tipo lt4 on lt4.id=lt3.lugar_tipo_id
                join lugar_tipo lt5 on lt5.id=le.lugar_tipo_id_localidad2012
                join lugar_tipo lt6 on lt6.id=lt5.lugar_tipo_id
                join lugar_tipo lt7 on lt7.id=lt6.lugar_tipo_id
                join lugar_tipo lt8 on lt8.id=lt7.lugar_tipo_id
                WHERE le.id='. $institucioneducativa[0]['le_juridicciongeografica_id']);
                $query->execute();
        $le = $query->fetchAll();
        
        $id_tabla = $institucioneducativa[0]['id'];
        $datos = array();
        $datos['institucioneducativa']=$institucioneducativa[0];
        $datos['jurisdiccion_geografica']=$le[0];
        $datos['institucioneducativaNivel']=$ieNivelAutorizado;
        $tramites = $em->getRepository('SieAppWebBundle:TramiteTipo')->createQueryBuilder('tt')
            ->select('tt.id,tt.tramiteTipo as tramite_tipo')
            ->where('tt.id in (:id)')
            ->setParameter('id',$tramites)
            ->getQuery()
            ->getResult();
          
        $datos['tramites'] = $tramites;
        $datos['area'] = $form['area'];
        $datos['justificacion'] = mb_strtoupper($form['observacion'], 'utf-8');
        foreach ($tramites as $tramite){
            switch($tramite['id']){
                case 34://Ampliacion de Nivel
                    $nt = $form['nivelampliar'];
                    foreach($ieNivelAutorizado as $n){
                        $nt[] = $n['id'];
                    }
                    $nivel = $em->getRepository('SieAppWebBundle:NivelTipo')->createQueryBuilder('nt')
                        ->select('nt.id,nt.nivel')
                        ->where('nt.id in (:id)')
                        ->orderBy('nt.id')
                        ->setParameter('id',$nt)
                        ->getQuery()
                        ->getResult();
                    $datos[$tramite['tramite_tipo']]['nivelampliar'] = $nivel;
                    $datos[$tramite['tramite_tipo']]['i_solicitud_ampliar']=$this->upload($files['i_solicitud_ampliar'],$form['idrue']);
                    $datos[$tramite['tramite_tipo']]['i_alquiler_ampliar']=$form['i_alquiler_ampliar'];
                    if($form['i_alquiler_ampliar'] == 'SI'){
                        $datos[$tramite['tramite_tipo']]['i_contrato_ampliar']=$this->upload($files['i_contrato_ampliar'],$form['idrue']);
                    }
                    $datos[$tramite['tramite_tipo']]['i_certificado_ampliar']=$form['i_certificado_ampliar'];
                    $datos[$tramite['tramite_tipo']]['ii_planos_ampliar']=$form['ii_planos_ampliar'];
                    $datos[$tramite['tramite_tipo']]['ii_infra_ampliar']=$form['ii_infra_ampliar'];
                    break;
                case 35: //Reduccion de Nivel
                    $nivel = $em->getRepository('SieAppWebBundle:NivelTipo')->createQueryBuilder('nt')
                        ->select('nt.id,nt.nivel')
                        ->where('nt.id in (:id)')
                        ->setParameter('id',$form['nivelreducir'])
                        ->getQuery()
                        ->getResult();
                    $datos[$tramite['tramite_tipo']]['nivelreducir']=$nivel;
                    $datos[$tramite['tramite_tipo']]['i_alquiler_reducir']=$form['i_alquiler_reducir'];
                    $datos[$tramite['tramite_tipo']]['i_certificado_reducir']=$form['i_certificado_reducir'];
                    $datos[$tramite['tramite_tipo']]['ii_planos_reducir']=$form['ii_planos_reducir'];
                    $datos[$tramite['tramite_tipo']]['ii_infra_reducir']=$form['ii_infra_reducir'];
                    $datos[$tramite['tramite_tipo']]['i_solicitud_reducir']=$this->upload($files['i_solicitud_reducir'],$form['idrue']);
                    if($form['i_alquiler_reducir'] == 'SI'){
                        $datos[$tramite['tramite_tipo']]['i_contrato_reducir']=$this->upload($files['i_contrato_reducir'],$form['idrue']);
                    }
                    break;
                case 36://Cambio de Dependencia
                    $d = $em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneBy(array('id'=>$form['dependencia']));    
                    $datos[$tramite['tramite_tipo']]['dependencia'] = array('id'=>$d->getId(),'dependencia'=>$d->getDependencia());
                    if ($form['dependencia'][0]==2){
                        $c = $em->getRepository('SieAppWebBundle:ConvenioTipo')->find($form['conveniotipo']);    
                        $datos[$tramite['tramite_tipo']]['conveniotipo'] = array('id'=>$c->getId(),'convenio'=>$c->getConvenio());
                        $datos[$tramite['tramite_tipo']]['i_solicitud_dependencia']=$this->upload($files['i_solicitud_dependencia'],$form['idrue']);
                        $datos[$tramite['tramite_tipo']]['i_actafundacion']=$this->upload($files['i_actafundacion'],$form['idrue']);
                        $datos[$tramite['tramite_tipo']]['i_folio_dependencia']=$form['i_folio_dependencia'];
                        $datos[$tramite['tramite_tipo']]['i_nrofolio_dependencia']=$form['i_nrofolio_dependencia'];
                        $datos[$tramite['tramite_tipo']]['i_esalquiler_dependencia']=$form['i_esalquiler_dependencia'];
                        if($form['i_esalquiler_dependencia'] == 'SI'){
                            $datos[$tramite['tramite_tipo']]['i_contrato_dependencia']=$this->upload($files['i_contrato_dependencia'],$form['idrue']);
                        }
                        $datos[$tramite['tramite_tipo']]['i_convenio_dependencia']=$this->upload($files['i_convenio_dependencia'],$form['idrue']);
                        $datos[$tramite['tramite_tipo']]['i_certificacion_gam']=$this->upload($files['i_certificacion_gam'],$form['idrue']);
                        $datos[$tramite['tramite_tipo']]['i_certificacionde_convenio']=$this->upload($files['i_certificacionde_convenio'],$form['idrue']);
                        $datos[$tramite['tramite_tipo']]['i_convenio_administracion']=isset($form['i_convenio_administracion'])?$form['i_convenio_administracion']:0;
                        $datos[$tramite['tramite_tipo']]['i_acta_constitucion']=$form['i_acta_constitucion'];
                        $datos[$tramite['tramite_tipo']]['i_registro_culto']=isset($form['i_registro_culto'])?$form['i_registro_culto']:0;
                        $datos[$tramite['tramite_tipo']]['i_org_nogubernamental']=isset($form['i_org_nogubernamental'])?$form['i_org_nogubernamental']:0;
                        $datos[$tramite['tramite_tipo']]['i_form_fundaempresa']=isset($form['i_form_fundaempresa'])?$form['i_form_fundaempresa']:0;
                        if(isset($form['i_form_fundaempresa'])){
                            $datos[$tramite['tramite_tipo']]['nro_fundaempresa']=$form['nro_fundaempresa'];
                            $datos[$tramite['tramite_tipo']]['fecha_fundaempresa']=$form['fecha_fundaempresa'];
                        }                        
                        $datos[$tramite['tramite_tipo']]['i_fotocopia_nit']=isset($form['i_fotocopia_nit'])?$form['i_fotocopia_nit']:0;
                        if(isset($form['i_fotocopia_nit'])){
                            $datos[$tramite['tramite_tipo']]['nit_dependencia']=$form['nit_dependencia'];
                            $datos[$tramite['tramite_tipo']]['i_balance_apertura']=$form['i_balance_apertura'];
                        }                        
                        $datos[$tramite['tramite_tipo']]['i_testimonioconvenio']=$form['i_testimonioconvenio'];
                        $datos[$tramite['tramite_tipo']]['fecha_testimonioconvenio']=$form['fecha_testimonioconvenio'];
                    }else{
                        $datos[$tramite['tramite_tipo']]['i_informe_dependencia']=$this->upload($files['i_informe_dependencia'],$form['idrue']);
                    }
                    $datos[$tramite['tramite_tipo']]['i_certificadorue_dependencia']=$form['i_certificadorue_dependencia'];
                    break;
                case 37://Cambio de Nombre
                    $datos[$tramite['tramite_tipo']]['nuevo_nombre']=$form['nuevo_nombre'];
                    $datos[$tramite['tramite_tipo']]['i_solicitud_cn']=$this->upload($files['i_solicitud_cn'],$form['idrue']);
                    if($ie->getDependenciaTipo()->getId() == 1 or $ie->getDependenciaTipo()->getId() == 2){
                        $datos[$tramite['tramite_tipo']]['i_ley_cn']=$form['i_ley_cn'];
                    }
                    if($ie->getDependenciaTipo()->getId() == 2 or $ie->getDependenciaTipo()->getId() == 3){
                        $datos[$tramite['tramite_tipo']]['i_actaconstitucion_cn']=$form['i_actaconstitucion_cn'];
                        $datos[$tramite['tramite_tipo']]['i_copianit_cn']=isset($form['i_copianit_cn'])?$form['i_copianit_cn']:0;
                        if(isset($form['i_copianit_cn'])){
                            $datos[$tramite['tramite_tipo']]['i_nit_cn']=$form['i_nit_cn'];
                        }
                        $datos[$tramite['tramite_tipo']]['i_nrotestimonio_cn']=$form['i_nrotestimonio_cn'];
                        $datos[$tramite['tramite_tipo']]['i_fecha_testimonio_cn']=$form['i_fecha_testimonio_cn'];
                        $datos[$tramite['tramite_tipo']]['i_licenciafuncionamiento_cn']=$form['i_licenciafuncionamiento_cn'];
                    }
                    if($ie->getDependenciaTipo()->getId() == 3){
                        $datos[$tramite['tramite_tipo']]['i_form_fundaempresa_cn']=$form['i_form_fundaempresa_cn'];
                        $datos[$tramite['tramite_tipo']]['i_nro_fundaempresa_cn']=$form['i_nro_fundaempresa_cn'];
                        $datos[$tramite['tramite_tipo']]['i_fecha_fundaempresa_cn']=$form['i_fecha_fundaempresa_cn'];
                    }
                    $datos[$tramite['tramite_tipo']]['i_certificadorue_cn']=$form['i_certificadorue_cn'];
                    if(isset($files['i_certdefuncion_cn'])){
                        $datos[$tramite['tramite_tipo']]['i_certdefuncion_cn']=$this->upload($files['i_certdefuncion_cn'],$form['idrue']);
                    }                    
                    break;
                case 38://Cambio de Jurisdiccion
                    $d = $em->getRepository('SieAppWebBundle:DistritoTipo')->find($form['nuevo_distrito']);    
                    $datos[$tramite['tramite_tipo']]['nuevo_distrito'] = array('id'=>$d->getId(),'nuevo_distrito'=>$d->getDistrito());
                    $datos[$tramite['tramite_tipo']]['i_resolucion_jur']=$this->upload($files['i_resolucion_jur'],$form['idrue']);
                    $datos[$tramite['tramite_tipo']]['i_certificacion_jur']=$this->upload($files['i_certificacion_jur'],$form['idrue']);
                    $datos[$tramite['tramite_tipo']]['i_area_jur']=$form['i_area_jur'];
                    if($ie->getDependenciaTipo()->getId() == 2){
                        $datos[$tramite['tramite_tipo']]['i_cert_convenio_jur']=$form['i_cert_convenio_jur'];    
                    }
                    $datos[$tramite['tramite_tipo']]['i_certificadorue_jur']=$form['i_certificadorue_jur'];
                    $datos[$tramite['tramite_tipo']]['ii_planos_jur']=$form['ii_planos_jur'];
                    $datos[$tramite['tramite_tipo']]['ii_inventario_jur']=$form['ii_inventario_jur'];
                    $datos[$tramite['tramite_tipo']]['iii_partemensual_jur']=$form['iii_partemensual_jur'];
                    break;
                case 39://Fusion
                    $iefusion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['siefusion']);
                    $datos[$tramite['tramite_tipo']]['siefusion']['id'] = $iefusion->getId();
                    $datos[$tramite['tramite_tipo']]['siefusion']['institucioneducativa'] = $iefusion->getInstitucioneducativa();
                    //$datos[$tramite['tramite_tipo']]['siefusion'] = $form['siefusion'];
                    break;
                case 40://Desglose
                    $datos['niveldesglose']=$form['niveldesglose'];
                    //$datos['fusioncerrar']=$form['fusioncerrar'];
                    break;
                case 41://Cambio de Infraestructura
                    if($form['lejurisdiccion']){
                        $datos[$tramite['tramite_tipo']]['lejurisdiccion']=$form['lejurisdiccion'];    
                    }else{
                        $datos[$tramite['tramite_tipo']]['zona']=$form['zona'];
                        $datos[$tramite['tramite_tipo']]['direccion']=$form['direccion'];
                        $datos[$tramite['tramite_tipo']]['iddepartamento2001']=$form['departamento2001'];
                        $datos[$tramite['tramite_tipo']]['departamento2001']=$em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento2001'])->getLugar();
                        $datos[$tramite['tramite_tipo']]['idprovincia2001']=$form['provincia2001'];
                        $datos[$tramite['tramite_tipo']]['provincia2001']=$em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia2001'])->getLugar();
                        $datos[$tramite['tramite_tipo']]['idmunicipio2001']=$form['municipio2001'];
                        $datos[$tramite['tramite_tipo']]['municipio2001']=$em->getRepository('SieAppWebBundle:LugarTipo')->find($form['municipio2001'])->getLugar();
                        $datos[$tramite['tramite_tipo']]['idcanton2001']=$form['canton2001'];
                        $datos[$tramite['tramite_tipo']]['canton2001']=$em->getRepository('SieAppWebBundle:LugarTipo')->find($form['canton2001'])->getLugar();
                        $datos[$tramite['tramite_tipo']]['idlocalidad2001']=$form['localidad2001'];
                        $datos[$tramite['tramite_tipo']]['localidad2001']=$em->getRepository('SieAppWebBundle:LugarTipo')->find($form['localidad2001'])->getLugar();
                        $datos[$tramite['tramite_tipo']]['iddepartamento2012']=$form['departamento2012'];
                        $datos[$tramite['tramite_tipo']]['departamento2012']=$em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento2012'])->getLugar();
                        $datos[$tramite['tramite_tipo']]['idprovincia2012']=$form['provincia2012'];
                        $datos[$tramite['tramite_tipo']]['provincia2012']=$em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia2012'])->getLugar();
                        $datos[$tramite['tramite_tipo']]['idmunicipio2012']=$form['municipio2012'];
                        $datos[$tramite['tramite_tipo']]['municipio2012']=$em->getRepository('SieAppWebBundle:LugarTipo')->find($form['municipio2012'])->getLugar();
                        $datos[$tramite['tramite_tipo']]['idcomunidad2012']=$form['comunidad2012'];
                        $datos[$tramite['tramite_tipo']]['comunidad2012']=$em->getRepository('SieAppWebBundle:LugarTipo')->find($form['comunidad2012'])->getLugar();
                        $datos[$tramite['tramite_tipo']]['i_solicitud_infra']=$this->upload($files['i_solicitud_infra'],$form['idrue']);
                        $datos[$tramite['tramite_tipo']]['i_certificacion_infra']=$this->upload($files['i_certificacion_infra'],$form['idrue']);
                        if($ie->getDependenciaTipo()->getId() == 2){
                            $datos[$tramite['tramite_tipo']]['i_certificacionconvenio_infra']=$this->upload($files['i_certificacionconvenio_infra'],$form['idrue']);    
                        }
                        $datos[$tramite['tramite_tipo']]['i_resolucion_infra']=$this->upload($files['i_resolucion_infra'],$form['idrue']);
                        $datos[$tramite['tramite_tipo']]['i_certificadorue_infra']=$form['i_certificadorue_infra'];
                        $datos[$tramite['tramite_tipo']]['ii_folio_infra']=$form['ii_folio_infra'];
                        $datos[$tramite['tramite_tipo']]['ii_nrofolio_infra']=$form['ii_nrofolio_infra'];
                        $datos[$tramite['tramite_tipo']]['ii_planos_infra']=$form['ii_planos_infra'];
                    }
                    break;
                case 42://Cierre Temporal
                case 43://Cierre Definitivo
                    $datos[$tramite['tramite_tipo']]['estadoinstitucion']=$form['estadoinstitucion'];    
                    $datos[$tramite['tramite_tipo']]['i_solicitud_cierre']=$this->upload($files['i_solicitud_cierre'],$form['idrue']);
                    $datos[$tramite['tramite_tipo']]['i_resolucion_cierre']=$this->upload($files['i_resolucion_cierre'],$form['idrue']);                    
                    $datos[$tramite['tramite_tipo']]['i_archivos_cierre']=$form['i_archivos_cierre'];
                    $datos[$tramite['tramite_tipo']]['i_certificadorue_cierre']=$form['i_certificadorue_cierre'];
                    break;
                case 44://Reapertura
                    break;
                case 45://Nuevo Certificado RUE
                    $datos[$tramite['tramite_tipo']]['i_solicitud_nuevorue']=$this->upload($files['i_solicitud_nuevorue'],$form['idrue']);
                    break;
            }
        }
        //dump($datos);die;
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

    /**
     * Reporte de inicio de solicitud
     */
    public function inicioSolicitudModificacionReporteAction(Request $request)
    {
        //dump($request);die;
        $idtramite = $request->get('idtramite');
        $id_td = $request->get('id_td');
        //dump($idtramite,$id_td);die;
        $em = $this->getDoctrine()->getManager();
        //$tramite = $em->getRepository('SieAppWebBundle:Tramite')->find($idtramite);
        $lk="http://libreta.minedu.gob.bo/lib/CpCoDJWtDtmnC3SnC30mEJ8mC3SnC3XyCJ0tCJ0mCp1yCZ0nDNmnCtmsV35yE7mm";
        $file = 'rue_iniciosolicitudModificacion_v1_pvc.rptdesign';    
        $arch = 'FORMULARIO_'.$idtramite.'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . $file . '&idtramite='.$idtramite.'&lk='. $lk .'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;   
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
        //dump($tramite);die;
        $tramiteDetalle = $em->getRepository('SieAppWebBundle:TramiteDetalle')->find((int)$tramite->getTramite());
        /**
         * obtiene datos de los anteriores formularios
         */
        $tareasDatos = $this->obtieneDatos($tramite);
        //dump($tareasDatos);die;
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
        $wfdatos = $em->getRepository('SieAppWebBundle:WfSolicitudTramite')->createQueryBuilder('wf')
                    ->select('wf.datos')
                    ->innerJoin('SieAppWebBundle:TramiteDetalle', 'td', 'WITH', 'td.id=wf.tramiteDetalle')
                    ->innerJoin('SieAppWebBundle:FlujoProceso', 'fp', 'WITH', 'fp.id=td.flujoProceso')
                    ->where('td.tramite = :id')
                    ->andwhere('fp.orden = 1')
                    ->andwhere('wf.esValido = true')
                    ->setParameter('id', $tramite->getId())
                    ->getQuery()
                    ->getResult();
        //dump($wfdatos);die;
        $tramites = json_decode($wfdatos[0]['datos'],true)['tramites'];
        $requisitos = array();
        foreach($tramites as $t){
            switch ($t['id']){
                case 34:
                case 35:
                case 41:
                    if(!isset($requisitos['Requisitos Legales'])){
                        $requisitos['Requisitos Legales'] = 'Requisitos Legales';
                    }
                    if(!isset($requisitos['Requisitos de Infraestructura'])){
                        $requisitos['Requisitos de Infraestructura'] = 'Requisitos de Infraestructura';
                    }
                    break;
                case 36:
                case 37:
                case 42:
                case 43:
                case 44:
                case 45:
                    if(!isset($requisitos['Requisitos Legales'])){
                        $requisitos['Requisitos Legales'] = 'Requisitos Legales';
                    }
                    break;
                case 38:
                    if(!isset($requisitos['Requisitos Legales'])){
                        $requisitos['Requisitos Legales'] = 'Requisitos Legales';
                    }
                    if(!isset($requisitos['Requisitos de Infraestructura'])){
                        $requisitos['Requisitos de Infraestructura'] = 'Requisitos de Infraestructura';
                    }
                    if(!isset($requisitos['Requisitos Administrativos'])){
                        $requisitos['Requisitos Administrativos'] = 'Requisitos Administrativos';
                    }
                    break;
            }
        }
        //dump($tramites,$requisitos);die;

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tramite_rue_recepcion_distrito_guardar'))
            ->add('flujoproceso', 'hidden', array('data' =>$tarea ))
            ->add('flujotipo', 'hidden', array('data' =>$flujotipo ))
            ->add('tramite', 'hidden', array('data' =>$tramite?$tramite->getId():$tramite ))
            ->add('idrue', 'hidden', array('data' =>$idrue ))
            ->add('tramitetipo', 'hidden', array('data' =>5 ))
            ->add('requisitos','choice',array('label'=>'Requisitos:','required'=>true, 'multiple' => true,'expanded' => true,'choices'=>$requisitos))
            ->add('observacion','textarea',array('label'=>'Observación:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
            ->add('varevaluacion1','choice',array('label'=>'¿Observar y devolver?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
            ->add('varevaluacion2','choice',array('label'=>'¿Informe Procedente?','expanded'=>true,'multiple'=>false,'required'=>true,'choices'=>array('SI' => 'SI','NO' => 'NO'),'attr' => array('class' => 'form-control')))
            ->add('informedistrito','text',array('label'=>'CITE del Informe:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase','placeholder'=>'')))
            ->add('fechainformedistrito', 'text', array('label'=>'Fecha del CITE de Informe:','required'=>false,'attr' => array('class' => 'form-control date','placeholder'=>'')))
            ->add('adjuntoinforme', 'file', array('label' => 'Adjuntar Informe (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar Informe",'accept'=>"application/pdf,.img,.jpg")))
            ->add('guardar','submit',array('label'=>'Enviar Solicitud'))
            ->getForm();
        
        return $form;
    }

    public function recepcionDistritoGuardarAction(Request $request)
    {
        
        $form = $request->get('form');
        $file = $request->files->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form,$file);die;
        $datos=array();
        $datos['observacion']=$form['observacion'];
        $datos['varevaluacion1']=$form['varevaluacion1'];
        $datos['requisitos']=$form['requisitos'];
        if($form['varevaluacion1'] == 'SI'){
            $datos['informedistrito']=$form['informedistrito'];
            $datos['fechainformedistrito']=$form['fechainformedistrito'];
            $datos['adjuntoinforme']=$this->upload($file['adjuntoinforme'],$form['idrue']);
        }else{
            $datos['varevaluacion2']=$form['varevaluacion2'];
            $varevaluacion2 = $form['varevaluacion2'];
        }
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
        //dump($tareasDatos);die;
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
        ->add('informesubdireccion','text',array('label'=>'CITE del Informe Subdirección Dirección:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
        ->add('fechainformesubdireccion', 'text', array('label'=>'Fecha de Informe Subdirección:','required'=>false,'attr' => array('class' => 'form-control date')))
        ->add('adjuntoinformesubdireccion', 'file', array('label' => 'Adjuntar Informe Subdireccioón (Máximo permitido 3M):','required'=>false, 'attr' => array('class'=>'form-control-file','title'=>"Adjuntar Informe",'accept'=>"application/pdf,.img,.jpg")))
        ->add('informejuridica','text',array('label'=>'CITE de Informe Legal:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
        ->add('fechainformejuridica', 'text', array('label'=>'Fecha de Informe Legal:','required'=>false,'attr' => array('class' => 'form-control date')))
        ->add('adjuntoinformejuridica', 'file', array('label' => 'Adjuntar Informe Legal (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar Informe",'accept'=>"application/pdf,.img,.jpg")))
        ->add('resolucion','text',array('label'=>'Nro. de Resolución Administrativa:','required'=>false,'attr' => array('class' => 'form-control','style' => 'text-transform:uppercase')))
        ->add('fecharesolucion', 'text', array('label'=>'Fecha de Resolución Administrativa:','required'=>false,'attr' => array('class' => 'form-control date')))
        ->add('adjuntoresolucion', 'file', array('label' => 'Adjuntar Resolución Administrativa (Máximo permitido 3M):','required'=>false, 'attr' => array('title'=>"Adjuntar Resolución",'accept'=>"application/pdf,.img,.jpg")))
        ->add('observacion','textarea',array('label'=>'Observación:','required'=>false,'attr'=>array('class'=>'form-control','style' => 'text-transform:uppercase')))
        ->add('guardar','submit',array('label'=>'Enviar Solicitud'))
        ->getForm();
        return $form;
    }

    public function recepcionDepartamentoGuardarAction(Request $request)
    {
        
        $form = $request->get('form');
        $files = $request->files->get('form');
        $em = $this->getDoctrine()->getManager();
        //dump($form,$files);die;
        $datos=array();
        $datos['observacion']=$form['observacion'];
        $datos['varevaluacion']=$form['varevaluacion'];
        $datos['informesubdireccion']=$form['informesubdireccion'];
        $datos['fechainformesubdireccion']=$form['fechainformesubdireccion'];
        if($form['informesubdireccion']){
            $datos['adjuntoinformesubdireccion']=$this->upload($files['adjuntoinformesubdireccion'],$form['idrue']);
        }
        if($form['varevaluacion'] == 'SI'){
            $datos['informejuridica']=$form['informejuridica'];
            $datos['fechainformejuridica']=$form['fechainformejuridica'];
            $datos['adjuntoinformejuridica']=$this->upload($files['adjuntoinformejuridica'],$form['idrue']);
            $datos['resolucion']=$form['resolucion'];
            $datos['fecharesolucion']=$form['fecharesolucion'];
            $datos['adjuntoresolucion']=$this->upload($files['adjuntoresolucion'],$form['idrue']);
        }
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
        //dump($tareasDatos);die;
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
        $tareasDatos = $this->obtieneDatos($em->getRepository('SieAppWebBundle:Tramite')->find($idtramite));
        //dump($tareasDatos);die;
        //dump($varevaluacion);die;
        //dump($tareasDatos[0]['datos']['tramites']);die;
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
                        /**
                         * Registrar en el RUE
                         */
                        $em->getConnection()->beginTransaction();
                        try{
                            $tareasDatos = $this->obtieneDatos($em->getRepository('SieAppWebBundle:Tramite')->find($idtramite));
                            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id_tabla);
                            $iddistrito = $tareasDatos[0]['datos']['jurisdiccion_geografica']['distrito_tipo_id'];
                            $fusion = 0;
                            foreach($tareasDatos[0]['datos']['tramites'] as $t){
                                if($t['id'] == 39){
                                    $fusion = 1;
                                }
                                if($t['id'] == 34){#ampliacion de nivel
                                    foreach($tareasDatos[0]['datos']['institucioneducativaNivel'] as $n){
                                        $arr[] = $n['id'];
                                    }
                                    //$institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
                                    //$institucioneducativa->setFechaResolucion(new \DateTime($tareasDatos[2]['datos']['fecharesolucion']));
                                    $institucioneducativa->setFechaModificacion(new \DateTime('now'));
                                    $em->flush();
                                    $nuevoNivel = $tareasDatos[0]['datos'][$t['tramite_tipo']]['nivelampliar'];
                                    //adiciona niveles nuevos
                                    foreach($nuevoNivel as $n){
                                        if(!in_array($n['id'],$arr)){
                                            $nivel = new InstitucioneducativaNivelAutorizado();
                                            $nivel->setFechaRegistro(new \DateTime('now'));
                                            $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($n['id']));
                                            $nivel->setInstitucioneducativa($institucioneducativa);
                                            $em->persist($nivel);
                                        }
                                    }
                                    $em->flush();
                                }elseif($t['id'] == 35){#reduccion de nivel
                                    //$institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
                                    //$institucioneducativa->setFechaResolucion(new \DateTime($tareasDatos[2]['datos']['fecharesolucion']));
                                    $institucioneducativa->setFechaModificacion(new \DateTime('now'));
                                    $em->flush();
                                    //elimina los niveles
                                    $nivelesElim = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->findBy(array('institucioneducativa' => $institucioneducativa->getId()));
                                    if($nivelesElim){
                                        foreach ($nivelesElim as $nivel) {
                                            $em->remove($nivel);
                                        }
                                        $em->flush();
                                    }
                                    $nuevoNivel = $tareasDatos[0]['datos'][$t['tramite_tipo']]['nivelreducir'];
                                    //adiciona niveles nuevos
                                    foreach($nuevoNivel as $n){
                                        //dump($n);die;
                                        $nivel = new InstitucioneducativaNivelAutorizado();
                                        $nivel->setFechaRegistro(new \DateTime('now'));
                                        $nivel->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById($n['id']));
                                        $nivel->setInstitucioneducativa($institucioneducativa);
                                        $em->persist($nivel);
                                    }
                                    $em->flush();
                                }elseif($t['id'] == 36){#cambio de dependencia
                                    //$institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
                                    //$institucioneducativa->setFechaResolucion(new \DateTime($tareasDatos[2]['datos']['fecharesolucion']));
                                    $institucioneducativa->setDependenciaTipo($em->getRepository('SieAppWebBundle:DependenciaTipo')->findOneById($tareasDatos[0]['datos'][$t['tramite_tipo']]['dependencia']['id']));
                                    $institucioneducativa->setFechaModificacion(new \DateTime('now'));
                                    if($tareasDatos[0]['datos'][$t['tramite_tipo']]['dependencia']['id'] == 2){
                                        $institucioneducativa->setConvenioTipo($em->getRepository('SieAppWebBundle:ConvenioTipo')->findOneById($tareasDatos[0]['datos'][$t['tramite_tipo']]['conveniotipo']['id']));
                                    }
                                    $em->flush();
                                }elseif($t['id'] == 37){#cambio de nombre
                                    //$institucioneducativa->setInstitucioneducativa(mb_strtoupper($tareasDatos[0]['datos'][$t['tramite_tipo']]['nuevo_nombre'], 'utf-8'));
                                    //$institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
                                    $institucioneducativa->setFechaResolucion(new \DateTime($tareasDatos[2]['datos']['fecharesolucion']));
                                    $institucioneducativa->setDesUeAntes(mb_strtoupper($tareasDatos[0]['datos']['institucioneducativa']['institucioneducativa'], 'utf-8'));
                                    $institucioneducativa->setFechaModificacion(new \DateTime('now'));
                                    $em->flush();
                                }elseif($t['id'] == 38){#cambio de jurisdiccion administrativa
                                    $iddistrito = $tareasDatos[0]['datos'][$t['tramiteTipo']]['nuevo_distrito']['id'];
                                    //$institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
                                    //$institucioneducativa->setFechaResolucion(new \DateTime($tareasDatos[2]['datos']['fecharesolucion']));
                                    $institucioneducativa->setFechaModificacion(new \DateTime('now'));
                                    $jurisdicciongeografica = $institucioneducativa->getLeJuridiccionGeografica();
                                    $jurisdicciongeografica->setLugarTipoIdDistrito($em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('lugarNivel' => 7, 'codigo' => $tareasDatos[0]['datos'][$t['tramite_tipo']]['nuevo_distrito']['id']))->getId());
                                    $jurisdicciongeografica->setDistritoTipo($em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($tareasDatos[0]['datos'][$t['tramite_tipo']]['nuevo_distrito']['id']));
                                    $jurisdicciongeografica->setFechaModificacion(new \DateTime('now'));
                                    $em->flush();
                                }elseif($t['id'] == 39){#Fusion
                                    $iefusion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($tareasDatos[0]['datos'][$t['tramite_tipo']]['siefusion']['id']);
                                    //$iefusion->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
                                    //$iefusion->setFechaResolucion(new \DateTime($tareasDatos[2]['datos']['fecharesolucion']));
                                    $iefusion->setEstadoinstitucionTipo($em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById(19));
                                    $iefusion->setFechaModificacion(new \DateTime('now'));
                                    $iefusion->setFechaCierre((new \DateTime('now'))->format('Y-m-d'));
                                    $em->flush();
                                }elseif($t['id'] == 41){#cambio de infraestructura
                                    //$institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
                                    //$institucioneducativa->setFechaResolucion(new \DateTime($tareasDatos[2]['datos']['fecharesolucion']));
                                    $institucioneducativa->setFechaModificacion(new \DateTime('now'));
                                    if(isset($tareasDatos[0]['datos'][$t['tramite_tipo']]['lejurisdiccion'])){
                                        $institucioneducativa->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($tareasDatos[0]['datos'][$t['tramite_tipo']]['lejurisdiccion']));
                                    }else{
                                        $institucioneducativa->setLeJuridicciongeografica($em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->findOneById($this->obtieneCodigoLe($tareasDatos[0]['datos'][$t['tramite_tipo']],$iddistrito,$usuario)));
                                    }
                                    $em->flush();
                                }elseif($t['id'] == 42 or $t['id'] == 43){#cierre temporal
                                    if($fusion == 0 ){
                                        //$institucioneducativa->setNroResolucion(mb_strtoupper($tareasDatos[2]['datos']['resolucion'], 'utf-8'));
                                        //$institucioneducativa->setFechaResolucion(new \DateTime($tareasDatos[2]['datos']['fecharesolucion']));
                                        $institucioneducativa->setEstadoinstitucionTipo($em->getRepository('SieAppWebBundle:EstadoinstitucionTipo')->findOneById(19));
                                        $institucioneducativa->setFechaModificacion(new \DateTime('now'));
                                        $institucioneducativa->setFechaCierre(new \DateTime('now'));
                                        $em->flush();
                                    }
                                }
                            }
                            $em->getConnection()->commit();
                            $request->getSession()
                                ->getFlashBag()
                                ->add('exito', $mensaje['msg']); 
                        }catch (Exception $ex) {
                            $em->getConnection()->rollback();
                            $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
                            $a = $this->get('wftramite')->eliminarTramiteRecibido($idtramite);
                            $b = $this->get('wftramite')->eliminarTramiteEnviado($idtramite,$usuario);
                            $request->getSession()
                                ->getFlashBag()
                                ->add('error', $mensaje['msg']);
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

    
    public function obtieneCodigoLe($le,$iddistrito,$id_usuario){
        try {
            //dump($le);die;
            $em = $this->getDoctrine()->getManager();
            
    		$sec = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($le['idmunicipio2001']);
    		$secCod = $sec->getCodigo();
    		$proCod = $sec->getLugarTipo()->getCodigo();
            $depCod = $sec->getLugarTipo()->getLugarTipo()->getCodigo();
            
    		$dis = $em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($iddistrito);
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
            $entity->setLugarTipoLocalidad($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($le['idlocalidad2001']));
            $entity->setLugarTipoIdLocalidad2012($le['idcomunidad2012']);
            $entity->setLugarTipoIdDistrito($distrito->getId());
            $entity->setDistritoTipo($dis);
            $entity->setValidacionGeograficaTipo($em->getRepository('SieAppWebBundle:ValidacionGeograficaTipo')->findOneById(0));
            $entity->setZona(mb_strtoupper($le['zona'], 'utf-8'));
            $entity->setDireccion(mb_strtoupper($le['direccion'], 'utf-8'));
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
                AND ie.institucioneducativaTipo = 1
                AND ie.dependenciaTipo in (1,2)
                AND le.lugarTipoIdDistrito = :lugar_id')
                ->setParameter('id', $idsiefusion)
                ->setParameter('lugar_id', $iddistrito);
        $institucioneducativa = $query->getResult();
        //dump($institucioneducativa);die;
        $response = new JsonResponse();
        if($institucioneducativa){
            $iefusion = array('idsiefusion'=>$idsiefusion,'institucioneducativa'=>$idsiefusion.'-'.$institucioneducativa[0]['institucioneducativa']);
            $response->setData(array('ie'=>$iefusion));
        }else{
            $response->setData(array('msg'=>'El código SIE es incorrecto.'));
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
            $tareasDatos[] = array('flujoProceso'=>$wfd->getTramiteDetalle()->getFlujoProceso(),'datos'=>$datos);
        }
        //dump($tareasDatos);die;
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
        $departamento2012 = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 8, 'paisTipoId' =>1));
        $departamento2001 = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' =>1));
        $lugarArray = array();
    	foreach($departamento2012 as $d){
            if($d->getLugar() != "NO EXISTE EN CNPV 2001"){
                $dep[$d->getid()] = $d->getlugar();
            }
        }
        $lugarArray['c2012']['dep']['lista']=$dep;
        foreach($departamento2001 as $d){
            if($d->getLugar() != "NO EXISTE EN CNPV 2001"){
                $dep1[$d->getid()] = $d->getlugar();
            }
        }
        $lugarArray['c2001']['dep']['lista']=$dep1;
        
        if ($edificio and $edificio->getLugarTipoIdLocalidad2012())
        {
            $comunidad = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneBy(array('id'=>$edificio->getLugarTipoIdLocalidad2012(),'lugarNivel'=>11));
            $lugarArray['zona'] = $edificio->getZona();
            $lugarArray['direccion'] = $edificio->getDireccion();
            $lugarArray['c2012']['comu']['id'] = $comunidad->getId();
            $lugarArray['c2012']['mun']['id'] = $comunidad->getLugarTipo()->getId();
            $lugarArray['c2012']['prov']['id'] = $comunidad->getLugarTipo()->getLugarTipo()->getId();
            $lugarArray['c2012']['dep']['id'] = $comunidad->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
            
            $provincia = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 9, 'lugarTipo' => $comunidad->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId()));
    	    foreach($provincia as $p){
                if($p->getLugar() != "NO EXISTE EN CNPV 2001"){
                    $lugarArray['c2012']['prov']['lista'][$p->getid()] = $p->getlugar();
                }
            }
            
            $municipio = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 10, 'lugarTipo' => $comunidad->getLugarTipo()->getLugarTipo()->getId()));
        	foreach($municipio as $m){
                if($m->getLugar() != "NO EXISTE EN CNPV 2001"){
                    $lugarArray['c2012']['mun']['lista'][$m->getid()] = $m->getlugar();
                }
            }
            
            $comunidad = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' =>11, 'lugarTipo' => $comunidad->getLugarTipo()->getId()));
    	    foreach($comunidad as $c){
                if($c->getLugar() != "NO EXISTE EN CNPV 2001"){
                    $lugarArray['c2012']['comu']['lista'][$c->getid()] = $c->getlugar();
                }
            }
            
            $lugarArray['c2001']['loc']['id'] = $edificio->getLugarTipoLocalidad()->getId();
            $lugarArray['c2001']['can']['id'] = $edificio->getLugarTipoLocalidad()->getLugarTipo()->getId();
            $lugarArray['c2001']['mun']['id'] = $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getId();
            $lugarArray['c2001']['prov']['id'] = $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
            $lugarArray['c2001']['dep']['id'] = $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId();
            
            $provincia = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId()));
    	    foreach($provincia as $p){
                if($p->getLugar() != "NO EXISTE EN CNPV 2001"){
                    $lugarArray['c2001']['prov']['lista'][$p->getid()] = $p->getlugar();
                }
            }
            
            $municipio = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 3, 'lugarTipo' => $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getId()));
        	foreach($municipio as $m){
                if($m->getLugar() != "NO EXISTE EN CNPV 2001"){
                    $lugarArray['c2001']['mun']['lista'][$m->getid()] = $m->getlugar();
                }
            }
            
            $canton = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' =>4, 'lugarTipo' => $edificio->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getId()));
    	    foreach($canton as $c){
                if($c->getLugar() != "NO EXISTE EN CNPV 2001"){
                    $lugarArray['c2001']['can']['lista'][$c->getid()] = $c->getlugar();
                }
            }
            
            $localidad = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' =>5, 'lugarTipo' => $edificio->getLugarTipoLocalidad()->getLugarTipo()->getId()));
    	    foreach($localidad as $l){
                if($l->getLugar() != "NO EXISTE EN CNPV 2001"){
                    $lugarArray['c2001']['loc']['lista'][$l->getid()] = $l->getlugar();
                }
            }
            return $response->setData(array(
                'lugar' => $lugarArray,
            ));
        }else{
            //dump($dep);die;
            $mensaje = "¡Código de Edificio Educativo incorrecto!";
            //dump($mensaje);die;
            return $response->setData(array(
                'msg'=>$mensaje,
                'lugar' => $lugarArray,
            ));
        }
        
    }

    public function provinciasAction($idDepartamento,$censo){
        //dump($idDepartamento);die;
        $em = $this->getDoctrine()->getManager();
        if($censo == 2001){
            $nivel = 2;
        }else{
            $nivel = 9;
        }
        
        $prov = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => $nivel, 'lugarTipo' => $idDepartamento));
    	$provincia = array();
    	foreach($prov as $p){
            if($p->getLugar() != "NO EXISTE EN CNPV 2001"){
                $provincia[$p->getid()] = $p->getlugar();
            }
        }
        
        /* *
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

    public function municipiosAction($idProvincia,$censo){
    	$em = $this->getDoctrine()->getManager();
        if($censo == 2001){
            $nivel = 3;
        }else{
            $nivel = 10;
        }
        $mun = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => $nivel, 'lugarTipo' => $idProvincia));
    	$municipio = array();
    	foreach($mun as $m){
            if($m->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $municipio[$m->getid()] = $m->getlugar();
            }
    	}
    	$response = new JsonResponse();
    	return $response->setData(array('municipio' => $municipio));
    }

    public function comunidadAction($idMunicipio,$censo){
        //dump($idMunicipio,$censo,'entra');die;
        $em = $this->getDoctrine()->getManager();
        if($censo == 2012){
            $nivel = 11;
        }
    	$com = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => $nivel, 'lugarTipo' => $idMunicipio));
    	$canton = array();
    	foreach($com as $c){
            if($c->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $comunidad[$c->getid()] = $c->getlugar();
            }
    	}
    	$response = new JsonResponse();
    	return $response->setData(array('comunidad' => $comunidad));
    }
    public function cantonesAction($idMunicipio,$censo){
        //dump($idMunicipio,$censo);die;
        $em = $this->getDoctrine()->getManager();
        if($censo == 2001){
            $nivel = 4;
        }
    	$can = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => $nivel, 'lugarTipo' => $idMunicipio));
    	$canton = array();
    	foreach($can as $c){
            if($c->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $canton[$c->getid()] = $c->getlugar();
            }
    	}
    	$response = new JsonResponse();
    	return $response->setData(array('canton' => $canton));
    }

    public function localidadesAction($idCanton,$censo){
        $em = $this->getDoctrine()->getManager();
        if($censo == 2001){
            $nivel = 5;
        }
    	$loc = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => $nivel, 'lugarTipo' => $idCanton));
    	$localidad = array();
    	foreach($loc as $l) {
            if($l->getLugar() != "NO EXISTE EN CNPV 2001"){
    		    $localidad[$l->getid()] = $l->getlugar();
            }
    	}
    	$response = new JsonResponse();
    	return $response->setData(array('localidad' => $localidad));
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
   
}