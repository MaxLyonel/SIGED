<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Entity\InstitucioneducativaSucursalTramite;
use Sie\AppWebBundle\Entity\OperativoControl;
use Doctrine\ORM\EntityRepository;


/**
 * Operativo Controller
 */
class OperativoController extends Controller {

    public $session;
    public $oc;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    /**
     * index the request
     * @param Request $request
     * @return obj with the selected request 
     */
    public function indexAction(Request $request) {
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $gestion = date('Y');
        //dump($gestion);die;
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        
        return $this->render($this->session->get('pathSystem') . ':Operativo:index.html.twig', array(
            'form' => $this->operativoForm()->createView(),
        ));
    }

    public function bucarOperativoCeasAction(Request $request) {
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //$gestion = date('Y');
        $gestion = 2018;
        //dump($gestion);die;
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $request->get('form');
        $idlugarusuario = $this->session->get('roluserlugarid');
        $em = $this->getDoctrine()->getManager();
        
        switch($form['operativo']){
            case 1:
                $entities = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->createQueryBuilder('a')
                            ->select('d.id as gestionTipo, b.id as SucursalIE, a.id as IEsucursalId,c.id as id_ie,c.institucioneducativa, a.periodoTipoId, h.id as teid, h.tramiteEstado as te, h.obs as observacion')
                            ->innerJoin('SieAppWebBundle:SucursalTipo', 'b', 'WITH', 'b.id = a.sucursalTipo')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa', 'c', 'WITH', 'c.id = a.institucioneducativa')
                            ->innerJoin('SieAppWebBundle:GestionTipo', 'd', 'WITH', 'd.id = a.gestionTipo') 
                            ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'e', 'WITH', 'c.leJuridicciongeografica = e.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursalTramite', 'g', 'WITH', 'g.institucioneducativaSucursal = a.id')
                            ->innerJoin('SieAppWebBundle:TramiteEstado', 'h', 'WITH', 'h.id = g.tramiteEstado')
                            ->where('a.periodoTipoId = 3')
                            ->andWhere('a.gestionTipo = '. ($gestion-1))
                            ->andWhere('e.lugarTipoIdDistrito = '. $idlugarusuario)
                            ->andWhere('h.id in(8,14)')
                            ->orderBy('d.id, b.id, a.id')
                            ->getQuery()
                            ->getResult();
                break;
            case 2:
                $entities = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->createQueryBuilder('a')
                            ->select('d.id as gestionTipo, b.id as SucursalIE, a.id as IEsucursalId,c.id as id_ie,c.institucioneducativa, a.periodoTipoId, h.id as teid, h.tramiteEstado as te, h.obs as observacion')
                            ->innerJoin('SieAppWebBundle:SucursalTipo', 'b', 'WITH', 'b.id = a.sucursalTipo')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa', 'c', 'WITH', 'c.id = a.institucioneducativa')
                            ->innerJoin('SieAppWebBundle:GestionTipo', 'd', 'WITH', 'd.id = a.gestionTipo') 
                            ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'e', 'WITH', 'c.leJuridicciongeografica = e.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursalTramite', 'g', 'WITH', 'g.institucioneducativaSucursal = a.id')
                            ->innerJoin('SieAppWebBundle:TramiteEstado', 'h', 'WITH', 'h.id = g.tramiteEstado')
                            ->where('a.periodoTipoId = 2')
                            ->andWhere('a.gestionTipo = '. $gestion)
                            ->andWhere('e.lugarTipoIdDistrito = '. $idlugarusuario)
                            ->andWhere('h.id in(9,12)')
                            ->orderBy('d.id, b.id, a.id')
                            ->getQuery()
                            ->getResult();
                break;
            case 3:
                $entities = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->createQueryBuilder('a')
                            ->select('d.id as gestionTipo, b.id as SucursalIE, a.id as IEsucursalId,c.id as id_ie,c.institucioneducativa,e.distritoTipo, a.periodoTipoId, h.id as teid, h.tramiteEstado as te, h.obs as observacion')
                            ->innerJoin('SieAppWebBundle:SucursalTipo', 'b', 'WITH', 'b.id = a.sucursalTipo')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa', 'c', 'WITH', 'c.id = a.institucioneducativa')
                            ->innerJoin('SieAppWebBundle:GestionTipo', 'd', 'WITH', 'd.id = a.gestionTipo') 
                            ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'e', 'WITH', 'c.leJuridicciongeografica = e.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursalTramite', 'g', 'WITH', 'g.institucioneducativaSucursal = a.id')
                            ->innerJoin('SieAppWebBundle:TramiteEstado', 'h', 'WITH', 'h.id = g.tramiteEstado')
                            ->where('a.periodoTipoId = 2')
                            ->andWhere('a.gestionTipo = '. $gestion)
                            ->andWhere('e.lugarTipoIdDistrito = '. $idlugarusuario)
                            ->andWhere('h.id in(8,14)')
                            ->orderBy('d.id, b.id, a.id')
                            ->getQuery()
                            ->getResult();
                break;
            case 4:
                $oc = $em->getRepository('SieAppWebBundle:OperativoControl')->createQueryBuilder('oc')
                            ->select('oc')
                            ->where('oc.operativoTipo = 4')
                            ->andWhere("oc.fechaInicio = '".$form['fechainicio']."'")
                            ->andWhere("oc.fechaFin = '".$form['fechafin']."'")
                            ->getQuery()
                            ->getResult();
                $iesArray = array();
                if($oc){
                    $datos = json_decode($oc[0]->getObs(),true);
                    foreach ($datos as $d){
                        $ies = json_decode($d,true)['ies'];
                        $iesArray[] =$ies;
                    }
                }
                //dump($oc);die;
                $entities = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->createQueryBuilder('a')
                            ->select('d.id as gestionTipo, b.id as SucursalIE, a.id as IEsucursalId,c.id as id_ie,c.institucioneducativa,f.id as idDistrito,f.distrito, a.periodoTipoId,i.periodo, h.id as teid, h.tramiteEstado as te, h.obs as observacion')
                            ->innerJoin('SieAppWebBundle:SucursalTipo', 'b', 'WITH', 'b.id = a.sucursalTipo')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa', 'c', 'WITH', 'c.id = a.institucioneducativa')
                            ->innerJoin('SieAppWebBundle:GestionTipo', 'd', 'WITH', 'd.id = a.gestionTipo') 
                            ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'e', 'WITH', 'c.leJuridicciongeografica = e.id')
                            ->innerJoin('SieAppWebBundle:DistritoTipo', 'f', 'WITH', 'e.distritoTipo = f.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursalTramite', 'g', 'WITH', 'g.institucioneducativaSucursal = a.id')
                            ->innerJoin('SieAppWebBundle:TramiteEstado', 'h', 'WITH', 'h.id = g.tramiteEstado')
                            ->innerJoin('SieAppWebBundle:PeriodoTipo', 'i', 'WITH', 'i.id = a.periodoTipoId')
                            ->where('a.periodoTipoId = 3');
                if($oc){
                    $entities = $entities->andWhere('a.id not in (:id_ies)')
                                        ->setParameter('id_ies', $iesArray);
                }
                $entities =$entities                            
                            ->andWhere('a.gestionTipo = '. $gestion)
                            ->andWhere('e.lugarTipoIdDistrito = '. $idlugarusuario)
                            ->andWhere('h.id in(9,12)')
                            ->orderBy('d.id, b.id, a.id')
                            ->getQuery()
                            ->getResult();
                break;
            case 5:

                break;
            case 6:

                break;
        }

        //dump($entities);die;
        return $this->render($this->session->get('pathSystem') . ':Operativo:tablaOperativo.html.twig', array(
            'entities' => $entities,
            'operativo'=>$form['operativo'],
            'fechainicio'=>$form['fechainicio'],
            'fechafin'=>$form['fechafin'],
            'distrito'=>$form['distrito'],
        ));
    }
    
    public function index1Action(Request $request) {
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $gestion = date('Y');
        //dump($gestion);die;
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        
        $entities = $em->getRepository('SieAppWebBundle:OperativoControl')->createQueryBuilder('oc')
                ->select('oc')
                ->innerjoin('SieAppWebBundle:OperativoTipo', 'ot', 'WITH', 'oc.operativoTipo=ot.id' )
                ->where('ot.institucioneducativaTipo=2')
                ->andWhere("ot.esvigente='1'")
                ->andWhere("ot.gestionTipoId=". $gestion)
                ->orderBy('oc.gestionTipoId','DESC')
                ->orderBy('oc.id','ASC')
                ->getQuery()
                ->getResult();
        dump($entities);die;
        return $this->render($this->session->get('pathSystem') . ':Operativo:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    public function operativoForm()
    {   
        $idlugarusuario = $this->session->get('roluserlugarid');
        $this->gestion = date('Y');
        $em = $this->getDoctrine()->getManager();
        $distrito = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idlugarusuario)->getCodigo();

        $form = $this->createFormBuilder()
        ->add('operativo','entity',array('label'=>'Operativo:','required'=>true,'class'=>'SieAppWebBundle:OperativoTipo','query_builder'=>function(EntityRepository $o){
            return $o->createQueryBuilder('o')->where("o.institucioneducativaTipo=2 and o.esvigente='1' and o.gestionTipoId=". $this->gestion);},'property'=>'operaTivo','empty_value' => 'Seleccione operativo','attr'=>array('class'=>'form-control')))
        ->add('distrito','hidden',array('data'=>$distrito))
        ->add('fechainicio','text',array('label'=>'Fecha inicio:','required'=>true, 'attr'=>array('class'=>'form-control')))
        ->add('fechafin','text',array('label'=>'Fecha fin:','required'=>true, 'attr'=>array('class'=>'form-control')))
        ->add('buscar', 'button', array('label'=> 'Buscar Ceas', 'attr'=>array('class'=>'form-control btn btn-success','onclick'=>'buscarCeas()')))
        ->getForm();
        return $form;
    }

    public function operativoDistritoAction(Request $request,$tipo) {
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol = $this->session->get('roluser');
        $em = $this->getDoctrine()->getManager();
        
        $operativoForm = $this->operativoForm($rol,$tipo);
        return $this->render($this->session->get('pathSystem') . ':Operativo:registroOperativo.html.twig', array(
            'form' => $operativoForm->createView(),
            'tipo' => $tipo,
        ));
    }

    public function operativo1Form($rol,$tipo)
    {   
        $idlugarusuario = $this->session->get('roluserlugarid');
        $this->gestion = date('Y');
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('alternativa_operativo_guardar'));
        if($rol == 7){
            $form=$form
            ->add('distrito','entity',array('label'=>'Distrito:','required'=>true,'class'=>'SieAppWebBundle:DistritoTipo','query_builder'=>function(EntityRepository $d){
                return $d->createQueryBuilder('d')->where('d.departamentoTipo=2');},'property'=>'distrito','empty_value' => 'Seleccione distrito','attr'=>array('class'=>'form-control')));
        }elseif($rol == 8){
            $form=$form
            ->add('departamento','entity',array('label'=>'Departamento:','required'=>true,'class'=>'SieAppWebBundle:DepartamentoTipo','query_builder'=>function(EntityRepository $dep){
                return $dep->createQueryBuilder('d')->where('dep.id is not 0');},'property'=>'distrito','empty_value' => 'Seleccione distrito','attr'=>array('class'=>'form-control')))
            ->add('distrito','entity',array('label'=>'Distrito:','required'=>true,'class'=>'SieAppWebBundle:DistritoTipo','query_builder'=>function(EntityRepository $d){
                return $d->createQueryBuilder('d')->where('d.departamentoTipo=2');},'property'=>'distrito','empty_value' => 'Seleccione distrito','attr'=>array('class'=>'form-control')));
        }elseif($rol == 10){
            if($tipo == 'distrito'){
                $distrito = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idlugarusuario)->getCodigo();
                $form=$form
                ->add('distrito','hidden',array('data'=>$distrito));    
            }else{
                $distrito = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idlugarusuario)->getCodigo();
                $form=$form
                ->add('distrito','hidden',array('data'=>$distrito))
                ->add('codsie','text',array('label'=>'Cod. SIE:', 'attr'=>array('maxlength' => '8','class'=>'form-control validar','onblur'=>'verificaCea(this.value)')));
            }
        }
        $form=$form
        ->add('fechainicio','text',array('label'=>'Fecha inicio:','required'=>true, 'attr'=>array('class'=>'form-control')))
        ->add('fechafin','text',array('label'=>'Fecha fin:','required'=>true, 'attr'=>array('class'=>'form-control')))
        ->add('obs','textarea',array('label'=>'Observación:','required'=>false, 'attr'=>array('class'=>'form-control','style' => 'text-transform:uppercase')))
        ->add('operativo','entity',array('label'=>'Operativo:','required'=>true,'class'=>'SieAppWebBundle:OperativoTipo','query_builder'=>function(EntityRepository $o){
            return $o->createQueryBuilder('o')->where("o.institucioneducativaTipo=2 and o.esvigente='1' and o.gestionTipoId=". $this->gestion);},'property'=>'operaTivo','empty_value' => 'Seleccione operativo','attr'=>array('class'=>'form-control')))
        ->add('guardar', 'submit', array('label'=> 'Guardar', 'attr'=>array('class'=>'form-control btn btn-success')))
        ->getForm();
        return $form;
    }

    public function operativoGuardarAction(Request $request)
    {
        //dump($request);die;
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        $obs = json_encode($request->get('ies'));
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');
            //registramos el orperativo
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('operativo_control');")->execute();
            $operativoControl = New OperativoControl();
            $operativoTipo = $em->getRepository('SieAppWebBundle:OperativoTipo')->find($request->get('operativo'));
            $distritoTipo = $em->getRepository('SieAppWebBundle:DistritoTipo')->find($request->get('distrito'));
            $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($id_usuario);
            
            $operativoControl->setOperativoTipo($operativoTipo);
            $operativoControl->setDistritoTipo($distritoTipo);
            $operativoControl->setUsuarioRegistro($usuario);
            $operativoControl->setFechaInicio(new \DateTime($request->get('fechainicio')));
            $operativoControl->setFechaFin(new \DateTime($request->get('fechafin')));
            $operativoControl->setObs($obs);
            $operativoControl->setFechaRegistro(new \DateTime('now'));
            $em->persist($operativoControl);
            $em->flush();

            //dump($operativoControl);die;
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('exito', 'Los datos fueron registrados correctamente.');

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('error', 'Error al registrar los datos.');

        }
        return $this->redirect($this->generateUrl('alternativa_operativo_home'));
    }
    
    /*public function operativoGuardarAction(Request $request)
    {
        //dump($request);die;
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');
            dump($form);die;

            //registramos el orperativo
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('operativo_control');")->execute();
            $operativoControl = New OperativoControl();
            $operativoTipo = $em->getRepository('SieAppWebBundle:OperativoTipo')->find($form['operativo']);
            $distritoTipo = $em->getRepository('SieAppWebBundle:DistritoTipo')->find($form['distrito']);
            $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($id_usuario);
            
            $operativoControl->setOperativoTipo($operativoTipo);
            $operativoControl->setDistritoTipo($distritoTipo);
            $operativoControl->setUsuarioRegistro($usuario);
            $operativoControl->setFechaInicio(new \DateTime($form['fechainicio']));
            $operativoControl->setFechaFin(new \DateTime($form['fechafin']));
            $operativoControl->setObs(strtoupper($form['obs']));
            $operativoControl->setFechaRegistro(new \DateTime('now'));
            $em->persist($operativoControl);
            $em->flush();

            //dump($operativoControl);die;
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('exito', 'Los datos fueron registrados correctamente.');

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('error', 'Error en el registro de los datos.');

        }
        return $this->redirect($this->generateUrl('alternativa_operativo_home'));
    }*/

    public function operativoEditAction(Request $request,$id) {
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol = $this->session->get('roluser');
        $em = $this->getDoctrine()->getManager();
        
        $operativoEditForm = $this->operativoEditForm($rol,$id);
        return $this->render($this->session->get('pathSystem') . ':Operativo:editOperativo.html.twig', array(
            'form' => $operativoEditForm->createView(),
        ));
    }

    public function operativoEditForm($rol,$id)
    {   
        
        $idlugarusuario = $this->session->get('roluserlugarid');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:OperativoControl')->find($id);
        $this->oc = $entity;
        //dump($entity);die;
        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('alternativa_operativo_update'));
        if($rol == 7){
            
            $form=$form
            ->add('distrito','entity',array('label'=>'Distrito:','required'=>true,'class'=>'SieAppWebBundle:DistritoTipo','query_builder'=>function(EntityRepository $d){
                return $d->createQueryBuilder('d')->where('d.departamentoTipo=2');},'property'=>'distrito','empty_value' => 'Seleccione distrito','attr'=>array('class'=>'form-control')));
        }elseif($rol == 8){
            $form=$form
            ->add('distrito','entity',array('label'=>'Distrito:','required'=>true,'class'=>'SieAppWebBundle:DistritoTipo','query_builder'=>function(EntityRepository $d){
                return $d->createQueryBuilder('d')->where('d.departamentoTipo=2');},'property'=>'distrito','empty_value' => 'Seleccione distrito','attr'=>array('class'=>'form-control')));
        }elseif($rol == 10){
            $distrito = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idlugarusuario)->getCodigo();
            $form=$form
            ->add('distrito','hidden',array('data'=>$entity->getdistritotipo()->getId()));
        }
        $form=$form
        ->add('id','hidden',array('data'=>$id))
        ->add('fechainicio','text',array('label'=>'Fecha inicio:','data'=>$entity->getfechaInicio()->format('Y-m-d'),'required'=>true, 'attr'=>array('class'=>'form-control')))
        ->add('fechafin','text',array('label'=>'Fecha fin:','data'=>$entity->getfechaFin()->format('Y-m-d'),'required'=>true, 'attr'=>array('class'=>'form-control')))
        ->add('obs','textarea',array('label'=>'Observación:','data'=>$entity->getObs(),'required'=>false, 'attr'=>array('class'=>'form-control','style' => 'text-transform:uppercase')))
        ->add('operativo','entity',array('label'=>'Operativo:','data'=>$this->oc->getOperativoTipo()->getId(),'required'=>true,'class'=>'SieAppWebBundle:OperativoTipo','query_builder'=>function(EntityRepository $o){
            return $o->createQueryBuilder('o')->where("o.institucioneducativaTipo=2 and o.esvigente='1' and o.gestionTipoId=".$this->oc->getOperativoTipo()->getGestionTipoId() ." and o.periodoTipo=". $this->oc->getOperativoTipo()->getPeriodoTipo()->getId());},'property'=>'operativo','empty_value' => false,'attr'=>array('class'=>'form-control')))
        ->add('guardar', 'submit', array('label'=> 'Guardar', 'attr'=>array('class'=>'form-control btn btn-success')))
        ->getForm();
        return $form;
    }

    public function operativoUpdateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id_usuario = $this->session->get('userId');
        $form = $request->get('form');
        //dump($form);die;

        $entity = $em->getRepository('SieAppWebBundle:OperativoControl')->find($form['id']);

        if (!$entity) {
            $this->get('session')->getFlashBag()->add('error', 'Operativo no encontrado.');
        }
        $em->getConnection()->beginTransaction();
        try {
            //modificamos el orperativo
            $operativoTipo = $em->getRepository('SieAppWebBundle:OperativoTipo')->find($form['operativo']);
            $distritoTipo = $em->getRepository('SieAppWebBundle:DistritoTipo')->find($form['distrito']);
            $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($id_usuario);
            $entity->setOperativoTipo($operativoTipo);
            $entity->setDistritoTipo($distritoTipo);
            $entity->setUsuarioModificacion($usuario);
            $entity->setFechaInicio(new \DateTime($form['fechainicio']));
            $entity->setFechaFin(new \DateTime($form['fechafin']));
            $entity->setObs(strtoupper($form['obs']));
            $entity->setFechaModificacion(new \DateTime('now'));
            $em->flush();
            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('exito', 'Los datos fueron modificados correctamente.');
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('error', 'Ocurrio un error al modificar el operativo.');
        }
        
        return $this->redirect($this->generateUrl('alternativa_operativo_home'));
    }

    public function operativoDeleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OperativoControl')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('error', 'Operativo no encontrado.');
        }else{
            $em->remove($entity);
            $em->flush();
            $mensaje = 'El operativo fué eliminado correctamente.';
            $request->getSession()
                    ->getFlashBag()
                    ->add('exito', $mensaje);           
        }

        return $this->redirect($this->generateUrl('alternativa_operativo_home'));
    }
    
    public function operativoBucarCeaAction(Request $request)
    {
        $idcea = $request->get('idcea');
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Inst')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('error', 'Operativo no encontrado.');
        }else{
            $em->remove($entity);
            $em->flush();
            $mensaje = 'El operativo fué eliminado correctamente.';
            $request->getSession()
                    ->getFlashBag()
                    ->add('exito', $mensaje);           
        }

        return $this->redirect($this->generateUrl('alternativa_operativo_home'));
    }
}