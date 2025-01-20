<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Entity\InstitucioneducativaSucursalTramite;
use Sie\AppWebBundle\Entity\OperativoControl;
use Sie\AppWebBundle\Entity\PeriodoEstadoTipo;
use Sie\AppWebBundle\Entity\TramiteEstado;
use Sie\AppWebBundle\Entity\TramiteTipo;
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
        // dump($gestion);die;
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
        //dump(new \DateTime('17-01-2019'));die;
        //$gestion = 2018;
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
        $rol = $this->session->get('roluser');
        $form = $request->get('form');
        $gestion = $form['gestion'];
        if($rol == 8 or $rol == 7){
            $distrito = "";
        }else{
            $distrito = $form['distrito'];
        }
        //dump($distrito,$rol,$form);die;
        switch($form['operativo']){
            case 1: //INSCRIPCIONES PRIMER SEMENSTRE
                if($rol == 8 or $rol == 7){
                    $oc = $em->getRepository('SieAppWebBundle:OperativoControl')->createQueryBuilder('oc')
                        ->select('oc')
                        ->innerJoin('SieAppWebBundle:DistritoTipo', 'd', 'WITH', 'd.id = oc.distritoTipo')
                        ->where('oc.operativoTipo = 1')
                        ->andWhere('d.departamentoTipo = ' .$form['departamento'])
                        ->andWhere('oc.gestionTipo = ' . $gestion)
                        ->getQuery()
                        ->getResult();
                    
                }else{
                    $oc = $em->getRepository('SieAppWebBundle:OperativoControl')->createQueryBuilder('oc')
                        ->select('oc')
                        ->where('oc.operativoTipo = 1')
                        ->andWhere('oc.distritoTipo = ' .$form['distrito'])
                        ->andWhere('oc.gestionTipo = ' . $gestion)
                        ->getQuery()
                        ->getResult();
                }
                $iesArray = array();
                if($oc){
                    foreach($oc as $o){
                        $datos = json_decode($o->getObs(),true);
                        foreach ($datos as $d){
                            $ies = json_decode($d,true)['ies'];
                            if($ies){
                                $iesArray[] =$ies;
                            }
                        }
                    }
                }

                $entities = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->createQueryBuilder('a')
                            ->select('d.id as gestionTipo, b.id as SucursalIE, a.id as IEsucursalId,c.id as id_ie,c.institucioneducativa,f.id as idDistrito,f.distrito,j.departamento, a.periodoTipoId,i.periodo, h.id as teid, h.tramiteEstado as te, h.obs as observacion')
                            ->innerJoin('SieAppWebBundle:SucursalTipo', 'b', 'WITH', 'b.id = a.sucursalTipo')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa', 'c', 'WITH', 'c.id = a.institucioneducativa')
                            ->innerJoin('SieAppWebBundle:GestionTipo', 'd', 'WITH', 'd.id = a.gestionTipo') 
                            ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'e', 'WITH', 'c.leJuridicciongeografica = e.id')
                            ->innerJoin('SieAppWebBundle:DistritoTipo', 'f', 'WITH', 'e.distritoTipo = f.id')
                            ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'j', 'WITH', 'f.departamentoTipo = j.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursalTramite', 'g', 'WITH', 'g.institucioneducativaSucursal = a.id')
                            ->innerJoin('SieAppWebBundle:TramiteEstado', 'h', 'WITH', 'h.id = g.tramiteEstado')
                            ->innerJoin('SieAppWebBundle:PeriodoTipo', 'i', 'WITH', 'i.id = a.periodoTipoId')
                            ->where('a.periodoTipoId = 3');
                if($oc){
                    $entities = $entities->andWhere('a.id not in (:id_ies)')
                                        ->setParameter('id_ies', $iesArray);
                }
                $entities =$entities 
                            ->andWhere('a.gestionTipo = '. ($gestion-1));
                if($rol == 8 or $rol == 7){
                    $entities =$entities 
                                ->andWhere('f.departamentoTipo = '. $form['departamento']);
                }else{
                    $entities =$entities 
                                ->andWhere('e.distritoTipo = '. $form['distrito']);
                }
                $entities =$entities                             
                            ->andWhere('h.id in(8,14)')
                            ->orderBy('d.id, b.id, a.id')
                            ->getQuery()
                            ->getResult();
                            
                break;
            case 2: //NOTAS PRIMER SEMESTRE
                $entities = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->createQueryBuilder('a')
                            ->select('d.id as gestionTipo, b.id as SucursalIE, a.id as IEsucursalId,c.id as id_ie,c.institucioneducativa,f.id as idDistrito,f.distrito,j.departamento, a.periodoTipoId,i.periodo, h.id as teid, h.tramiteEstado as te, h.obs as observacion')
                            ->innerJoin('SieAppWebBundle:SucursalTipo', 'b', 'WITH', 'b.id = a.sucursalTipo')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa', 'c', 'WITH', 'c.id = a.institucioneducativa')
                            ->innerJoin('SieAppWebBundle:GestionTipo', 'd', 'WITH', 'd.id = a.gestionTipo') 
                            ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'e', 'WITH', 'c.leJuridicciongeografica = e.id')
                            ->innerJoin('SieAppWebBundle:DistritoTipo', 'f', 'WITH', 'e.distritoTipo = f.id')
                            ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'j', 'WITH', 'f.departamentoTipo = j.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursalTramite', 'g', 'WITH', 'g.institucioneducativaSucursal = a.id')
                            ->innerJoin('SieAppWebBundle:TramiteEstado', 'h', 'WITH', 'h.id = g.tramiteEstado')
                            ->innerJoin('SieAppWebBundle:PeriodoTipo', 'i', 'WITH', 'i.id = a.periodoTipoId')
                            ->where('a.periodoTipoId = 2')
                            ->andWhere('a.gestionTipo = '. $gestion);
                if($rol == 8 or $rol == 7){
                    $entities =$entities 
                                ->andWhere('f.departamentoTipo = '. $form['departamento']);
                }else{
                    $entities =$entities 
                                ->andWhere('e.distritoTipo = '. $form['distrito']);
                }
                $entities =$entities                             
                            ->andWhere('h.id in(9,12)')
                            ->orderBy('d.id, b.id, a.id')
                            ->getQuery()
                            ->getResult();
                break;
            case 3: //INSCRIPCIONES SEGUNDO SEMESTRE
                if($rol == 8 or $rol == 7){
                    $oc = $em->getRepository('SieAppWebBundle:OperativoControl')->createQueryBuilder('oc')
                            ->select('oc')
                            ->innerJoin('SieAppWebBundle:DistritoTipo', 'd', 'WITH', 'd.id = oc.distritoTipo')
                            ->where('oc.operativoTipo = 3')
                            ->andWhere('d.departamentoTipo = ' .$form['departamento'])
                            ->andWhere('oc.gestionTipo = ' . $gestion)
                            ->getQuery()
                            ->getResult();
            
                }else{
                    $oc = $em->getRepository('SieAppWebBundle:OperativoControl')->createQueryBuilder('oc')
                            ->select('oc')
                            ->where('oc.operativoTipo = 3')
                            ->andWhere('oc.distritoTipo = ' .$form['distrito'])
                            ->andWhere('oc.gestionTipo = ' . $gestion)
                            ->getQuery()
                            ->getResult();
                }
                $iesArray = array();
                if($oc){
                    foreach($oc as $o){
                        $datos = json_decode($o->getObs(),true);
                            foreach ($datos as $d){
                                $ies = json_decode($d,true)['ies'];
                                if($ies){
                                    $iesArray[] =$ies;
                                }
                            }
                        }
                }
                $entities = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->createQueryBuilder('a')
                            ->select('d.id as gestionTipo, b.id as SucursalIE, a.id as IEsucursalId,c.id as id_ie,c.institucioneducativa,f.id as idDistrito,f.distrito,j.departamento, a.periodoTipoId,i.periodo, h.id as teid, h.tramiteEstado as te, h.obs as observacion')
                            ->innerJoin('SieAppWebBundle:SucursalTipo', 'b', 'WITH', 'b.id = a.sucursalTipo')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa', 'c', 'WITH', 'c.id = a.institucioneducativa')
                            ->innerJoin('SieAppWebBundle:GestionTipo', 'd', 'WITH', 'd.id = a.gestionTipo') 
                            ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'e', 'WITH', 'c.leJuridicciongeografica = e.id')
                            ->innerJoin('SieAppWebBundle:DistritoTipo', 'f', 'WITH', 'e.distritoTipo = f.id')
                            ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'j', 'WITH', 'f.departamentoTipo = j.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursalTramite', 'g', 'WITH', 'g.institucioneducativaSucursal = a.id')
                            ->innerJoin('SieAppWebBundle:TramiteEstado', 'h', 'WITH', 'h.id = g.tramiteEstado')
                            ->innerJoin('SieAppWebBundle:PeriodoTipo', 'i', 'WITH', 'i.id = a.periodoTipoId')
                            ->where('a.periodoTipoId = 2');
                if($oc){
                    $entities = $entities->andWhere('a.id not in (:id_ies)')
                                        ->setParameter('id_ies', $iesArray);
                }
                $entities =$entities 
                            ->andWhere('a.gestionTipo = '. $gestion);
                if($rol == 8 or $rol == 7){
                    $entities =$entities 
                                ->andWhere('f.departamentoTipo = '. $form['departamento']);
                }else{
                    $entities =$entities 
                                ->andWhere('e.distritoTipo = '. $form['distrito']);
                }
                $entities =$entities    
                            ->andWhere('h.id in(8,14)')
                            ->orderBy('d.id, b.id, a.id')
                            ->getQuery()
                            ->getResult();
                break;
            case 4: //NOTAS SEGUNDO SEMESTRE
                $entities = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->createQueryBuilder('a')
                            ->select('d.id as gestionTipo, b.id as SucursalIE, a.id as IEsucursalId,c.id as id_ie,c.institucioneducativa,f.id as idDistrito,f.distrito,j.departamento, a.periodoTipoId,i.periodo, h.id as teid, h.tramiteEstado as te, h.obs as observacion')
                            ->innerJoin('SieAppWebBundle:SucursalTipo', 'b', 'WITH', 'b.id = a.sucursalTipo')
                            ->innerJoin('SieAppWebBundle:Institucioneducativa', 'c', 'WITH', 'c.id = a.institucioneducativa')
                            ->innerJoin('SieAppWebBundle:GestionTipo', 'd', 'WITH', 'd.id = a.gestionTipo') 
                            ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'e', 'WITH', 'c.leJuridicciongeografica = e.id')
                            ->innerJoin('SieAppWebBundle:DistritoTipo', 'f', 'WITH', 'e.distritoTipo = f.id')
                            ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'j', 'WITH', 'f.departamentoTipo = j.id')
                            ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursalTramite', 'g', 'WITH', 'g.institucioneducativaSucursal = a.id')
                            ->innerJoin('SieAppWebBundle:TramiteEstado', 'h', 'WITH', 'h.id = g.tramiteEstado')
                            ->innerJoin('SieAppWebBundle:PeriodoTipo', 'i', 'WITH', 'i.id = a.periodoTipoId')
                            ->where('a.periodoTipoId = 3')
                            ->andWhere('a.gestionTipo = '. $gestion);
                if($rol == 8 or $rol == 7){
                    $entities =$entities 
                                ->andWhere('f.departamentoTipo = '. $form['departamento']);
                }else{
                    $entities =$entities 
                                ->andWhere('e.distritoTipo = '. $form['distrito']);
                }
                $entities =$entities  
                            ->andWhere('h.id in(9,12)')
                            ->orderBy('d.id, b.id, a.id')
                            ->getQuery()
                            ->getResult();
                break;
        }

        //dump($entities);die;
        return $this->render($this->session->get('pathSystem') . ':Operativo:tablaOperativo.html.twig', array(
            'entities' => $entities,
            'operativo'=>$form['operativo'],
            'fechainicio'=>$form['fechainicio'],
            'fechafin'=>$form['fechafin'],
            'distrito'=>$distrito,
            'gestion'=>$gestion,
        ));
    }
    
    public function operativoAmpliarAction(Request $request) {
        
        //dump($request);die;
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        if($request->getMethod() =='GET' ){
            $gestion = date('Y');
        }else{
            //dump($request->get('form'));die;
            $form = $request->get('form');
            $gestion = $form['gestion'];
        }
        
        $em = $this->getDoctrine()->getManager();
        if($rol == 7 or $rol == 10){
            $idlugarusuario = $this->session->get('roluserlugarid');
            $idLugarTipo = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idlugarusuario)->getCodigo();
        }else{
            $idLugarTipo = "";
        }
        
        $data = $this->listaAmpliar($rol,$gestion,$idLugarTipo);
        $form = $this->ampliarGestionForm();
        return $this->render($this->session->get('pathSystem') . ':Operativo:ampliar.html.twig', array(
            'entities' => $data['entities'],
            'nro' => $data['nro'],
            'form' => $form->createView(),
            'gestion' =>$gestion
        ));
    }

    public function ampliarGestionForm()
    {   
        $em = $this->getDoctrine()->getManager();
       
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('alternativa_operativo_ampliar'))
            ->add('gestion','entity',array('label'=>'Gestión:','required'=>true,'class'=>'SieAppWebBundle:GestionTipo','query_builder'=>function(EntityRepository $g){
                return $g->createQueryBuilder('g')->where('g.id>=2019')->orderBy('g.id','DESC');},'property'=>'gestion','empty_value' => 'Seleccionar gestión','attr'=>array('class'=>'form-control')))
            /* ->add('operativo','entity',array('label'=>'Operativo:','required'=>true,'class'=>'SieAppWebBundle:OperativoTipo','query_builder'=>function(EntityRepository $o){
                return $o->createQueryBuilder('o')->where("o.institucioneducativaTipo=2 and o.esvigente=true");},'property'=>'operaTivo','empty_value' => 'Todos','attr'=>array('class'=>'form-control'))) */
            ->getForm();
        return $form;
    }

    public function operativoForm()
    {   
        $idlugarusuario = $this->session->get('roluserlugarid');
        $rolusuario = $this->session->get('roluser');
        $this->gestion = date('Y');
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
        ->add('operativo','entity',array('label'=>'Operativo:','required'=>true,'class'=>'SieAppWebBundle:OperativoTipo','query_builder'=>function(EntityRepository $o){
            return $o->createQueryBuilder('o')->where("o.institucioneducativaTipo=2 ");},'property'=>'operaTivo','empty_value' => 'Seleccione operativo','attr'=>array('class'=>'form-control')))
        ->add('fechainicio','text',array('label'=>'Fecha inicio: (dia-mes-año)','required'=>true,'data'=>date('d-m-Y'), 'attr'=>array('class'=>'form-control datepicker','placeholder'=>'dd-mm-AAAA','maxlength'=>10,'minlength'=>10,'autocomplete'=>'off')))
        ->add('fechafin','text',array('label'=>'Fecha fin: (dia-mes-año)','required'=>true,'data'=>'20-02-2025', 'attr'=>array('class'=>'form-control','placeholder'=>'dd-mm-AAAA','maxlength'=>10,'minlength'=>10,'autocomplete'=>'off', 'readonly'=>true)))
        ->add('gestion','entity',array('label'=>'Gestión:','required'=>true,'class'=>'SieAppWebBundle:GestionTipo','query_builder'=>function(EntityRepository $g){
            return $g->createQueryBuilder('g')->where('g.id='.$this->gestion)->orderBy('g.id');},'property'=>'gestion','empty_value' => false,'attr'=>array('class'=>'form-control')));
        if($rolusuario == 8){
            $form = $form
            ->add('departamento','entity',array('label'=>'Departamento:','required'=>true,'class'=>'SieAppWebBundle:DepartamentoTipo','query_builder'=>function(EntityRepository $d){
                return $d->createQueryBuilder('d')->where('d.id not in (0)')->orderBy('d.departamento');},'property'=>'departamento','empty_value' => false,'attr'=>array('class'=>'form-control','onchange'=>'distrito(this.value)')));
        }elseif($rolusuario == 7){
            $departamento = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idlugarusuario)->getCodigo();
            $form =$form
            ->add('departamento','hidden',array('data'=>$departamento));
        }elseif($rolusuario == 10){
            $distrito = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idlugarusuario)->getCodigo();
            $form = $form
            ->add('distrito','hidden',array('data'=>$distrito));
        }
        $form = $form
        ->add('buscar', 'button', array('label'=> 'Buscar Ceas', 'attr'=>array('class'=>'form-control btn btn-success','onclick'=>'buscarCeas()')))
        ->getForm();
        return $form;
    }

    public function bucarDistritoAction(Request $request)
    {
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
        $id_departamento =$request->get('depto');
        $em = $this->getDoctrine()->getManager();
        $distrito = $em->getRepository('SieAppWebBundle:DistritoTipo')->findBy(array('departamentoTipo'=>$id_departamento));
        $distritoArray = array();
        foreach($distrito as $d){
            $distritoArray[$d->getId()]=$d->getDistrito();
        }
        $response = new JsonResponse();
        return $response->setData(array('distrito' => $distritoArray));
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

    /*public function operativo1Form($rol,$tipo)
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
    }*/

    public function operativoGuardarAction(Request $request)
    {
        // dump($request);die;
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
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
        //$gestion = (new \DateTime($request->get('fechainicio')))->format('Y');
        $gestion = (int)$request->get('gestion');
        $fechainicio = (new \DateTime($request->get('fechainicio')))->format('Y-m-d');
        $fechafin = (new \DateTime($request->get('fechafin')))->format('Y-m-d');
        //dump($request->get('ies'));die;
        $iesOperativo = $request->get('ies');
        try {
            if($rol == 8 or $rol == 7){
                foreach($iesOperativo as $op){
                    $distrito=json_decode($op,true)['dis'];
                    //dump($distrito);die;
                    $oc = $em->getRepository('SieAppWebBundle:OperativoControl')->createQueryBuilder('oc')
                        ->select('oc')
                        ->where('oc.operativoTipo = '.$request->get('operativo'))
                        ->andWhere("oc.fechaInicio = '".$fechainicio."'")
                        ->andWhere("oc.fechaFin = '".$fechafin."'")
                        ->andWhere("oc.distritoTipo = " . $distrito) 
                        ->getQuery()
                        ->getResult();

                    
                   if($oc){
                        //dump($op);die;
                        $datos=json_decode($oc[0]->getObs(),true);
                        $obs = array_merge($datos,array($op));
                        $obs = json_encode($obs);
                        $oc[0]->setObs($obs);
                        $em->flush();
                    }else{
                        $obs = json_encode(array($op));
                        //registramos el orperativo
                        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('operativo_control');")->execute();
                        $operativoControl = New OperativoControl();
                        $operativoTipo = $em->getRepository('SieAppWebBundle:OperativoTipo')->find($request->get('operativo'));
                        $distritoTipo = $em->getRepository('SieAppWebBundle:DistritoTipo')->find($distrito);
                        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($id_usuario);
                        $operativoControl->setOperativoTipo($operativoTipo);
                        $operativoControl->setDistritoTipo($distritoTipo);
                        $operativoControl->setUsuarioRegistro($usuario);
                        $operativoControl->setFechaInicio(new \DateTime($request->get('fechainicio')));
                        $operativoControl->setFechaFin(new \DateTime($request->get('fechafin')));
                        $operativoControl->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                        $operativoControl->setObs($obs);
                        $operativoControl->setFechaRegistro(new \DateTime('now'));
                        $em->persist($operativoControl);
                        $em->flush();
                    }
                    
                }
            }else{
                $oc = $em->getRepository('SieAppWebBundle:OperativoControl')->createQueryBuilder('oc')
                    ->select('oc')
                    ->where('oc.operativoTipo = '.$request->get('operativo'))
                    ->andWhere("oc.fechaInicio = '".$fechainicio."'")
                    ->andWhere("oc.fechaFin = '".$fechafin."'")
                    ->andWhere("oc.distritoTipo = " .$request->get('distrito')) 
                    ->getQuery()
                    ->getResult();
                //dump($oc);die;
                if($oc){
                    $datos=json_decode($oc[0]->getObs(),true);
                    $obs = array_merge($datos,$request->get('ies'));
                    $obs = json_encode($obs);
                    //dump($obs);die;
                    $oc[0]->setObs($obs);
                    $em->flush();
                }else{
                    $obs = json_encode($request->get('ies'));
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
                    $operativoControl->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                    $operativoControl->setObs($obs);
                    $operativoControl->setFechaRegistro(new \DateTime('now'));
                    $em->persist($operativoControl);
                    $em->flush();
                }
            }
            
            if($request->get('operativo') == 2 or $request->get('operativo') == 4){ //apertura notas primer y segundo semestre
                //dump($request->get('ies'));die;
                $iesucursal = $request->get('ies');
                foreach($iesucursal as $ies){
                    $idsucursal = json_decode($ies,true)['ies'];
                    $idie = json_decode($ies,true)['ie'];
                    $ie = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($idie);
                    $ies = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($idsucursal);
                    $iest = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursalTramite')->findBy(array('institucioneducativaSucursal'=>$idsucursal));
                    $iest[0]->setPeriodoEstado($em->getRepository('SieAppWebBundle:PeriodoEstadoTipo')->find('2'));//fin de periodo
                    $iest[0]->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('13'));//Aceptación de apertura Fin de Semestre
                    $iest[0]->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find('4'));//Fin de semestre
                    $iest[0]->setFechainicio(new \DateTime('now'));
                    $iest[0]->setUsuarioIdInicio($this->session->get('userId'));
                    $em->flush();
                }
            }
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
        //dump($request);die;

        $rol = $this->session->get('roluser');
        $em = $this->getDoctrine()->getManager();
        
        //$operativoEditForm = $this->operativoEditForm($request->get('id'));
        $operativoEditForm = $this->operativoEditForm($id);
        return $this->render($this->session->get('pathSystem') . ':Operativo:editOperativo.html.twig', array(
            'form' => $operativoEditForm->createView(),
        ));
    }

    public function operativoEditForm($id)
    {   
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:OperativoControl')->find($id);
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('alternativa_operativo_update'))
            ->add('id','hidden',array('data'=>$id))
            ->add('distrito','text',array('label'=>'Distrito:','data'=>$entity->getDistritoTipo()->getDistrito(),'disabled'=>true, 'attr'=>array('class'=>'form-control')))
            ->add('operativo','text',array('label'=>'Operativo:','data'=>$entity->getOperativoTipo()->getOperativo(),'disabled'=>true, 'attr'=>array('class'=>'form-control')))
            ->add('fechainicio','text',array('label'=>'Fecha inicio:','data'=>$entity->getfechaInicio()->format('d-m-Y'),'disabled'=>true, 'attr'=>array('class'=>'form-control')))
            ->add('fechafin','text',array('label'=>'Fecha fin (dia-mes-año):','data'=>$entity->getfechaFin()->format('d-m-Y'),'required'=>true, 'attr'=>array('class'=>'form-control datepicker','maxlength'=>10,'minlength'=>10,'autocomplete'=>'off')))
            //->add('guardar', 'button', array('label'=> 'Guardar Cambios', 'attr'=>array('class'=>'form-control btn btn-success','onclick'=>'guardarCambios()')))
            ->add('guardar', 'submit', array('label'=> 'Guardar Cambios', 'attr'=>array('class'=>'form-control btn btn-success')))
            ->getForm();
        return $form;
    }

    public function operativoUpdateAction(Request $request)
    {
        //dump($request);die;
        $em = $this->getDoctrine()->getManager();
        $id_usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $gestionActual = date('Y');
        if($rol == 7 or $rol == 10){
            $idlugarusuario = $this->session->get('roluserlugarid');
            $idLugarTipo = $em->getRepository('SieAppWebBundle:LugarTipo')->find($idlugarusuario)->getCodigo();
        }else{
            $idLugarTipo = "";
        }
        //dump($request);die;
        $form = $request->get('form');
        //dump($form);die;
        $entity = $em->getRepository('SieAppWebBundle:OperativoControl')->find($form['id']);
        if (!$entity) {
            $mensaje = '¡Error, Operativo no encontrado.!';
            $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje);   
        }else{
            $em->getConnection()->beginTransaction();
            try {
                //modificamos el orperativo
                $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($id_usuario);
                $entity->setUsuarioModificacion($usuario);
                $entity->setFechaFin(new \DateTime($form['fechafin']));
                $entity->setFechaModificacion(new \DateTime('now'));
                $em->flush();
                $em->getConnection()->commit();

                $mensaje = 'Los datos fueron modificados correctamente.';
                $request->getSession()
                    ->getFlashBag()
                    ->add('exito', $mensaje);   
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
                $mensaje = '¡Ocurrio un error al modificar el operativo.!';
                $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje);   
            }
        }

        return $this->redirect($this->generateUrl('alternativa_operativo_ampliar'));
        /*return $this->render($this->session->get('pathSystem') . ':Operativo:tablaAmpliar.html.twig', array(
            'entities' => $this->listaAmpliar($rol,$gestionActual,$idLugarTipo),
            'mensaje'=> $mensaje,
        ));*/
    }

    public function listaAmpliar($rol,$gestion,$idLugarTipo)
    {
        $em = $this->getDoctrine()->getManager();
        if($rol == 8){
            $entities = $em->getRepository('SieAppWebBundle:OperativoControl')->createQueryBuilder('oc')
                        ->select('oc')
                        ->innerjoin('SieAppWebBundle:OperativoTipo', 'ot', 'WITH', 'oc.operativoTipo=ot.id' )
                        ->where('ot.institucioneducativaTipo=2')
                        ->andWhere('ot.esvigente=true')
                        ->andWhere('oc.gestionTipo='. $gestion)
                        ->orderBy('oc.distritoTipo,oc.id')
                        ->getQuery()
                        ->getResult();
        }elseif($rol == 7){
            $entities = $em->getRepository('SieAppWebBundle:OperativoControl')->createQueryBuilder('oc')
                        ->select('oc')
                        ->innerjoin('SieAppWebBundle:OperativoTipo', 'ot', 'WITH', 'oc.operativoTipo=ot.id' )
                        ->innerjoin('SieAppWebBundle:DistritoTipo', 'dt', 'WITH', 'oc.distritoTipo=dt.id' )
                        ->where('ot.institucioneducativaTipo=2')
                        ->andWhere('ot.esvigente=true')
                        ->andWhere('oc.gestionTipo='. $gestion)
                        ->andWhere('dt.departamentoTipo='. $idLugarTipo)
                        ->orderBy('oc.distritoTipo,oc.id')
                        ->getQuery()
                        ->getResult();
        }elseif($rol == 10){
            $entities = $em->getRepository('SieAppWebBundle:OperativoControl')->createQueryBuilder('oc')
                        ->select('oc')
                        ->innerjoin('SieAppWebBundle:OperativoTipo', 'ot', 'WITH', 'oc.operativoTipo=ot.id' )
                        ->where('ot.institucioneducativaTipo=2')
                        ->andWhere('ot.esvigente=true')
                        ->andWhere('oc.gestionTipo='. $gestion)
                        ->andWhere('oc.distritoTipo='. $idLugarTipo)
                        ->orderBy('oc.distritoTipo,oc.id')
                        ->getQuery()
                        ->getResult();
        }

        $ceasArray = array();
        foreach ($entities as $e) {
            $ceasArray[$e->getId()]= count(json_decode($e->getObs(),true));
        }

        $data['entities'] = $entities;
        $data['nro'] = $ceasArray;
        return $data;
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
        $form = $request->get('form');
        
        $em = $this->getDoctrine()->getManager();

        switch($form['operativo']){
            case 1: //INSCRIPCIONES PRIMER SEMESTRE
                $periodo = 2;
                $tramiteEstado = 12;
                break;
            case 2: //NOTAS PRIMER SEMESTRE
                $periodo = 2;
                $tramiteEstado = 14;
                break;
            case 3: //INSCRIPCIONES SEGUNDO SEMESTRE
                $periodo = 3;
                $tramiteEstado = 12;
                break;
            case 4: //NOTAS SEGUNDO SEMESTRE
                $periodo = 3;
                $tramiteEstado = 14;
                break;
        }
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
                        ->where('a.periodoTipoId = '. $periodo)
                        ->andWhere('a.institucioneducativa = '. $form['codsie'])
                        ->andWhere('a.gestionTipo = '. $form['gestion'])
                        ->andWhere('g.tramiteEstado = '. $tramiteEstado)
                        ->getQuery()
                        ->getResult();    
        //dump($entities);die;
        return $this->render($this->session->get('pathSystem') . ':Operativo:tablaRegularizar.html.twig', array(
            'entities' => $entities,
        ));
    }

    public function operativoBuscarGestionPasadaAction(Request $request)
    {
        $form = $request->get('form');
        //dump($form);die;
        
        $em = $this->getDoctrine()->getManager();

        switch($form['operativo']){
            case 1: //INSCRIPCIONES PRIMER SEMESTRE
                $periodo = 3;
                $tramiteEstado = array(14,8);
                break;
            case 2: //NOTAS PRIMER SEMESTRE
                $periodo = 2;
                $tramiteEstado = array(12,9);
                break;
            case 3: //INSCRIPCIONES SEGUNDO SEMESTRE
                $periodo = 2;
                $tramiteEstado = array(14,8);
                break;
            case 4: //NOTAS SEGUNDO SEMESTRE
                $periodo = 3;
                $tramiteEstado = array(12,9);
                break;
        }
        $entities = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->createQueryBuilder('a')
                        ->select('d.id as gestionTipo, b.id as SucursalIE, a.id as IEsucursalId,c.id as id_ie,c.institucioneducativa,f.id as idDistrito,f.distrito, a.periodoTipoId,i.periodo, h.id as teid, h.tramiteEstado as te, h.obs as observacion,g.id as id_iest')
                        ->innerJoin('SieAppWebBundle:SucursalTipo', 'b', 'WITH', 'b.id = a.sucursalTipo')
                        ->innerJoin('SieAppWebBundle:Institucioneducativa', 'c', 'WITH', 'c.id = a.institucioneducativa')
                        ->innerJoin('SieAppWebBundle:GestionTipo', 'd', 'WITH', 'd.id = a.gestionTipo') 
                        ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'e', 'WITH', 'c.leJuridicciongeografica = e.id')
                        ->innerJoin('SieAppWebBundle:DistritoTipo', 'f', 'WITH', 'e.distritoTipo = f.id')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaSucursalTramite', 'g', 'WITH', 'g.institucioneducativaSucursal = a.id')
                        ->innerJoin('SieAppWebBundle:TramiteEstado', 'h', 'WITH', 'h.id = g.tramiteEstado')
                        ->innerJoin('SieAppWebBundle:PeriodoTipo', 'i', 'WITH', 'i.id = a.periodoTipoId')
                        ->where('a.periodoTipoId = '. $periodo)
                        ->andWhere('a.institucioneducativa = '. $form['codsie'])
                        ->andWhere('a.gestionTipo = '. $form['gestion'])
                        ->andWhere('g.tramiteEstado in (:estado)')
                        ->setParameter('estado',$tramiteEstado)
                        ->getQuery()
                        ->getResult();    
        //dump($entities);die;
        return $this->render($this->session->get('pathSystem') . ':Operativo:tablaGestionPasada.html.twig', array(
            'entities' => $entities,
        ));
    }

    public function operativoGestionPasadaGuardarAction(Request $request)
    {
        //dump($request->get('iest'));die;
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursalTramite')->find($request->get('iest'));
        //dump($entity);die;
        if (!$entity) {
            $mensaje['tipo'] = 'error';
            $mensaje['msg'] = 'No existe el registro.';
        }else{
            $em->getConnection()->beginTransaction();
            try {
                $entity->setPeriodoEstado($em->getRepository('SieAppWebBundle:PeriodoEstadoTipo')->find('2'));//fin de periodo
                $entity->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('13'));//Aceptación de apertura Fin de Semestre
                $entity->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find('4'));//Fin de semestre
                $entity->setFechainicio(new \DateTime('now'));
                $entity->setUsuarioIdInicio($this->session->get('userId'));
                $em->flush();
                $em->getConnection()->commit();
                $mensaje['tipo'] = 'exito';
                $mensaje['msg'] = 'el operativo fué habilitado con exito para el CEA.';
            
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
                $mensaje['tipo'] = 'error';
                $mensaje['msg'] = 'Ocurrió un error al habilitar el operativo.';
            }
        }
        
        $response = new JsonResponse();
        return $response->setData($mensaje);
    }

    public function operativoVerCeasAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:OperativoControl')->find($request->get('id'));
        //dump($entity);die;
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('error', 'Operativo no encontrado.');
            return $this->redirect($this->generateUrl('alternativa_operativo_ampliar'));
        }else{
            $datos = json_decode($entity->getObs(),true);
            //dump($datos);die;
            $ieSucursalArray = array(); 
            foreach($datos as $d){
                $ieSucursalArray[]=json_decode($d,true)['ies'];
            }
            $ies = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findBy(array('id'=>$ieSucursalArray));
            
            return $this->render($this->session->get('pathSystem') . ':Operativo:operativoVerCeas.html.twig', array(
                'ies' => $ies,
            ));
        }
    }

    public function operativoRegularizarAction(Request $request) {
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
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
        
        return $this->render($this->session->get('pathSystem') . ':Operativo:regularizar.html.twig', array(
            'form' => $this->operativoRegularizarForm()->createView(),
        ));
    }

    public function operativoGestionPasadaAction(Request $request) {
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
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
        
        return $this->render($this->session->get('pathSystem') . ':Operativo:gestionPasada.html.twig', array(
            'form' => $this->operativoGestionPasadaForm()->createView(),
        ));
    }

    public function operativoGestionPasadaForm()
    {   
        $idlugarusuario = $this->session->get('roluserlugarid');
        $rolusuario = $this->session->get('roluser');
        $this->gestion = date('Y');
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder();
        if($this->session->get('userName')=='4926577'){

            $form =$form->add('codsie','text',array('label'=>'Cod. SIE:', 'required'=>true, 'attr'=>array('maxlength' => '8','class'=>'form-control validar')))
            ->add('operativo','entity',array('label'=>'Operativo:','required'=>true,'class'=>'SieAppWebBundle:OperativoTipo','query_builder'=>function(EntityRepository $o){
                return $o->createQueryBuilder('o')->where("o.institucioneducativaTipo=2 and o.esvigente=true ");},'property'=>'operaTivo','empty_value' => false,'attr'=>array('class'=>'form-control')))
            ->add('gestion','entity',array('label'=>'Gestión:','required'=>true,'class'=>'SieAppWebBundle:GestionTipo','query_builder'=>function(EntityRepository $g){
                return $g->createQueryBuilder('g')->where('g.id>=2009')->andWhere('g.id<=2019')->orderBy('g.id','DESC');},'property'=>'gestion','empty_value' => false,'attr'=>array('class'=>'form-control')));
        }else{
            $form =$form->add('codsie','text',array('label'=>'Cod. SIE:', 'required'=>true, 'attr'=>array('maxlength' => '8','class'=>'form-control validar')))
        ->add('operativo','entity',array('label'=>'Operativo:','required'=>true,'class'=>'SieAppWebBundle:OperativoTipo','query_builder'=>function(EntityRepository $o){
            return $o->createQueryBuilder('o')->where("o.institucioneducativaTipo=2 and o.esvigente=true and o.id in (2,4)");},'property'=>'operaTivo','empty_value' => false,'attr'=>array('class'=>'form-control')))
        ->add('gestion','entity',array('label'=>'Gestión:','required'=>true,'class'=>'SieAppWebBundle:GestionTipo','query_builder'=>function(EntityRepository $g){
            return $g->createQueryBuilder('g')->where('g.id>=2009')->andWhere('g.id<=2018')->orderBy('g.id','DESC');},'property'=>'gestion','empty_value' => false,'attr'=>array('class'=>'form-control')));
        }
        $form =$form->add('buscar', 'button', array('label'=> 'Buscar', 'attr'=>array('class'=>'form-control btn btn-success','onclick'=>'buscarCeaGestionPasada()')))
        ->getForm();
        return $form;
    }
    
    public function operativoRegularizarForm()
    {   
        $idlugarusuario = $this->session->get('roluserlugarid');
        $rolusuario = $this->session->get('roluser');
        $this->gestion = date('Y');
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
        ->add('codsie','text',array('label'=>'Cod. SIE:', 'required'=>true, 'attr'=>array('maxlength' => '8','class'=>'form-control validar')))
        ->add('operativo','entity',array('label'=>'Operativo:','required'=>true,'class'=>'SieAppWebBundle:OperativoTipo','query_builder'=>function(EntityRepository $o){
            return $o->createQueryBuilder('o')->where("o.institucioneducativaTipo=2 and o.esvigente=true");},'property'=>'operaTivo','empty_value' => false,'attr'=>array('class'=>'form-control')));
        if($rolusuario == 8){
            $form = $form
            ->add('gestion','entity',array('label'=>'Gestión:','required'=>true,'class'=>'SieAppWebBundle:GestionTipo','query_builder'=>function(EntityRepository $g){
                return $g->createQueryBuilder('g')->where('g.id>=2009')->orderBy('g.id');},'property'=>'gestion','empty_value' => false,'attr'=>array('class'=>'form-control')));
        }else{
            $form = $form
            ->add('gestion','entity',array('label'=>'Gestión:','required'=>true,'class'=>'SieAppWebBundle:GestionTipo','query_builder'=>function(EntityRepository $g){
                return $g->createQueryBuilder('g')->where('g.id>=2018')->orderBy('g.id');},'property'=>'gestion','empty_value' => false,'attr'=>array('class'=>'form-control')));
        }
        $form = $form
        ->add('buscar', 'button', array('label'=> 'Buscar', 'attr'=>array('class'=>'form-control btn btn-success','onclick'=>'buscarCea()')))
        ->getForm();
        return $form;
    }

    public function operativoRegularizarGuardarAction(Request $request)
    {
        //dump($request->get('id'));die;
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $codsie = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->find($request->get('id'))->getInstitucioneducativa()->getId();
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $id_usuario);
        $query->bindValue(':sie', $codsie);
        $query->bindValue(':rolId', $rol);
        $query->execute();
        $aTuicion = $query->fetchAll();        
        //dump($aTuicion);die;
        if ($aTuicion[0]['get_ue_tuicion']) {

            $em->getConnection()->beginTransaction();
            try {
                $iest = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursalTramite')->findBy(array('institucioneducativaSucursal'=>$request->get('id')));            
                //dump($iest);die;
                $estado = $iest[0]->getTramiteEstado()->getId();
                //dump($estado);die;
                if ($estado == '14'){                                                           
                    $iest[0]->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('6'));//EN REGULARIZACIÓN FIN DE SEMESTRE                
                    $iest[0]->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find('24')); 
                }    
                if ($estado == '12'){
                    $iest[0]->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('7'));//EN REGULARIZACIÓN INICIO DE SEMESTRE
                    $iest[0]->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find('23'));                                
                }
                $iest[0]->setFechaModificacion(new \DateTime('now'));
                $iest[0]->setUsuarioIdModificacion($this->session->get('userId'));            
                $em->persist($iest[0]);
                $em->flush();
            
                $em->getConnection()->commit();
            
                $this->get('session')->getFlashBag()->add('exito', 'El CEA: '. $iest[0]->getInstitucioneducativaSucursal()->getInstitucioneducativa()->getInstitucioneducativa() . ' ,fué habilitado para regularizar el operativo.');
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
                $this->get('session')->getFlashBag()->add('error', 'Ocurrio un error, el CEA no fué habilitado para regularizar el operativo');
            }
        }else{
            $this->get('session')->getFlashBag()->add('error', 'No tiene tuición sobre el Centro de Educación Alternativa.');    
        }
        
        return $this->redirect($this->generateUrl('alternativa_operativo_regularizar'));
    }

    public function operativoNuevoReaperturaAction(Request $request) {
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
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
        
        return $this->render($this->session->get('pathSystem') . ':Operativo:nuevoReapertura.html.twig', array(
            'form' => $this->operativoNuevoReaperturaForm()->createView(),
        ));
    }

    public function operativoNuevoReaperturaForm()
    {   
        $idlugarusuario = $this->session->get('roluserlugarid');
        $rolusuario = $this->session->get('roluser');
        $this->gestion = date('Y');
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
        ->setAction($this->generateUrl('alternativa_operativo_nuevo_reapertura_guardar'))
        ->add('codsie','text',array('label'=>'Cod. SIE:', 'required'=>true, 'attr'=>array('maxlength' => '8','class'=>'form-control validar')))
        ->add('operativo','entity',array('label'=>'Operativo:','required'=>true,'class'=>'SieAppWebBundle:OperativoTipo','query_builder'=>function(EntityRepository $o){
            return $o->createQueryBuilder('o')->where("o.institucioneducativaTipo=2 and o.esvigente=true and o.id in (1,3)");},'property'=>'operaTivo','empty_value' => false,'attr'=>array('class'=>'form-control')))
        ->add('fechainicio','text',array('label'=>'Fecha inicio: (dia-mes-año)','required'=>true,'data'=>date('d-m-Y'), 'attr'=>array('class'=>'form-control datepicker','placeholder'=>'dd-mm-AAAA','maxlength'=>10,'minlength'=>10,'autocomplete'=>'off')))
        ->add('fechafin','text',array('label'=>'Fecha fin: (dia-mes-año)','required'=>true,'data'=>date('d-m-Y'), 'attr'=>array('class'=>'form-control datepicker','placeholder'=>'dd-mm-AAAA','maxlength'=>10,'minlength'=>10,'autocomplete'=>'off')))
        ->add('habilitar', 'submit', array('label'=> 'Habilitar', 'attr'=>array('class'=>'form-control btn btn-success')))
        ->getForm();
        return $form;
    }

    public function operativoNuevoReaperturaGuardarAction(Request $request)
    {
        //dump($request);die;
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $rol = $this->session->get('roluser');
        $form = $request->get('form');
        //dump($form);die;
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $gestion = date('Y');
        $em = $this->getDoctrine()->getManager();
        $codsie = $form['codsie'];
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $id_usuario);
        $query->bindValue(':sie', $codsie);
        $query->bindValue(':rolId', $rol);
        $query->execute();
        $aTuicion = $query->fetchAll();        
        //dump($aTuicion);die;
        if ($aTuicion[0]['get_ue_tuicion']) {
            switch($form['operativo']){
                case 1: //INSCRIPCIONES PRIMER SEMESTRE
                    $periodo = 2;
                    break;
                case 3: //INSCRIPCIONES SEGUNDO SEMESTRE
                    $periodo = 3;
                    break;
            }
            $sw = 2;

            $query = $em->getConnection()->prepare("SELECT ". $gestion ." as gestion_tipo_id,0 as sucursal_tipo_id,'' as ies_id,ie.id,ie.institucioneducativa,dt.id as id_distrito,dt.distrito,dp.departamento,'' as periodo_tipo_id,'' as te_id,'' as tramite_estado
                FROM institucioneducativa ie
                LEFT JOIN institucioneducativa_sucursal ies ON ie.id=ies.institucioneducativa_id
                JOIN jurisdiccion_geografica jg ON jg.id=ie.le_juridicciongeografica_id
                JOIN distrito_tipo dt ON jg.distrito_tipo_id=dt.id
                JOIN departamento_tipo dp ON dp.id=dt.departamento_tipo_id
                WHERE ies.id IS NULL
                AND ie.institucioneducativa_tipo_id=2
                AND ie.estadoinstitucion_tipo_id = 10
                AND ie.institucioneducativa_acreditacion_tipo_id=1
                AND ie.fecha_creacion > '01-01-1900'
                AND ie.id=" . $codsie);      
            $query->execute();
            $entity = $query->fetchAll();
            //dump($entity);die;
            if(!$entity){
                $query = $em->getConnection()->prepare("SELECT ies.gestion_tipo_id,ies.sucursal_tipo_id,ies.id as ies_id,ie.id,ie.institucioneducativa,dt.id as id_distrito,dt.distrito,dp.departamento,ies.periodo_tipo_id,te.id as te_id,te.tramite_estado
                    FROM institucioneducativa ie
                    JOIN institucioneducativa_sucursal ies ON ie.id=ies.institucioneducativa_id
                    LEFT JOIN institucioneducativa_sucursal_tramite iest ON ies.id=iest.institucioneducativa_sucursal_id
                    JOIN jurisdiccion_geografica jg ON jg.id=ie.le_juridicciongeografica_id
                    JOIN distrito_tipo dt ON jg.distrito_tipo_id=dt.id
                    JOIN departamento_tipo dp ON dp.id=dt.departamento_tipo_id
                    LEFT JOIN tramite_estado te ON iest.tramite_estado_id=te.id
                    WHERE ies.institucioneducativa_id=". $codsie ."
                    AND ie.institucioneducativa_tipo_id=2
                    AND ie.estadoinstitucion_tipo_id = 10
                    AND ie.institucioneducativa_acreditacion_tipo_id=1
                    ORDER BY gestion_tipo_id DESC, periodo_tipo_id  DESC LIMIT 1");      
                $query->execute();
                $entity = $query->fetchAll();
                //dump($entity);die;
                //dump($entity[0]['periodo_tipo_id'] );die;
                if($entity){
                    if($periodo == 2){
                        if($entity[0]['gestion_tipo_id'] == ($gestion - 1) and $entity[0]['periodo_tipo_id'] == 2){
                            $sw = 1;
                        }elseif($entity[0]['gestion_tipo_id'] < ($gestion - 1)){
                            $sw = 1;
                        }else{
                            $sw = 2;
                        }
                    }
                    if($periodo == 3){
                        if($entity[0]['gestion_tipo_id'] == ($gestion - 1) and $entity[0]['periodo_tipo_id'] == 3){
                            $sw = 1;
                        }elseif($entity[0]['gestion_tipo_id'] < ($gestion - 1)){
                            $sw = 1;
                        }else{
                            $sw = 2;
                        }
                    }
                }
            }else{
                $sw = 0;
            }
            //dump($sw);die;
            if($entity and ($sw == 0 or $sw == 1)){
                
                $ies[] = '{"ies":"'. $entity[0]['ies_id'] .'","ie":"'. $entity[0]['id'] .'","suc":"'. $entity[0]['sucursal_tipo_id'] .'","dis":"'. $entity[0]['id_distrito'] .'"}';
                //dump($ies);die;
                $fechainicio = (new \DateTime($form['fechainicio']))->format('Y-m-d');
                $fechafin = (new \DateTime($form['fechafin']))->format('Y-m-d');
                $em->getConnection()->beginTransaction();
                //dump($entity);die;
                try {
                    $oc = $em->getRepository('SieAppWebBundle:OperativoControl')->createQueryBuilder('oc')
                        ->select('oc')
                        ->where('oc.operativoTipo = '.$form['operativo'])
                        ->andWhere("oc.fechaInicio = '".$fechainicio."'")
                        ->andWhere("oc.fechaFin = '".$fechafin."'")
                        ->andWhere("oc.distritoTipo =" .$entity[0]['id_distrito']) 
                        ->getQuery()
                        ->getResult();
                    //dump($oc);die;
                    if($oc){
                        $datos=json_decode($oc[0]->getObs(),true);
                        //dump($datos);die;
                        $obs = array_merge($datos,$ies);
                        $obs = json_encode($obs);
                        //dump($obs);die;
                        $oc[0]->setObs($obs);
                        $em->flush();
                    }else{
                        $obs = json_encode($ies);
                        //registramos el operativo
                        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('operativo_control');")->execute();
                        $operativoControl = New OperativoControl();
                        $operativoTipo = $em->getRepository('SieAppWebBundle:OperativoTipo')->find($form['operativo']);
                        $distritoTipo = $em->getRepository('SieAppWebBundle:DistritoTipo')->find($entity[0]['id_distrito']);
                        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($id_usuario);
                        $operativoControl->setOperativoTipo($operativoTipo);
                        $operativoControl->setDistritoTipo($distritoTipo);
                        $operativoControl->setUsuarioRegistro($usuario);
                        $operativoControl->setFechaInicio(new \DateTime($fechainicio));
                        $operativoControl->setFechaFin(new \DateTime($fechafin));
                        $operativoControl->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                        $operativoControl->setObs($obs);
                        $operativoControl->setFechaRegistro(new \DateTime('now'));
                        $em->persist($operativoControl);
                        $em->flush();
                    }
                    /**
                     * CREACION DE LA SUCURSAL PARA CEAS NUEVAS O REAPERTURADAS
                     */
                    $query = $em->getConnection()->prepare('SELECT sp_genera_institucioneducativa_sucursal(:sie, :subcea, :gestion, :periodo)');
                    $query->bindValue(':sie', $entity[0]['id']);
                    $query->bindValue(':gestion', $gestion);
                    $query->bindValue(':periodo', $periodo);
                    $query->bindValue(':subcea', $entity[0]['sucursal_tipo_id']);
                    $query->execute();
                    $iesnew = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa'=>$entity[0]['id'],'sucursalTipo'=>0,'gestionTipo'=>$gestion,'periodoTipoId'=>$periodo));
                    $iestnew = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursalTramite')->findOneBy(array('institucioneducativaSucursal'=>$iesnew->getId()));
                    if (!$iestnew){
                        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_sucursal_tramite');")->execute();  
                        $iest = new InstitucioneducativaSucursalTramite();
                        $iest->setInstitucioneducativaSucursal($iesnew);            
                        $iest->setPeriodoEstado($em->getRepository('SieAppWebBundle:PeriodoEstadoTipo')->find('1'));
                        $iest->setTramiteEstado($em->getRepository('SieAppWebBundle:TramiteEstado')->find('11'));//Aceptación de apertura Inicio de Semestre
                        $iest->setTramiteTipo($em->getRepository('SieAppWebBundle:TramiteTipo')->find('5'));
                        $iest->setDistritoCod($entity[0]['id_distrito']);
                        $iest->setFechainicio(new \DateTime('now'));
                        $iest->setUsuarioIdInicio($this->session->get('userId'));
                        $em->persist($iest);
                        $em->flush();
                    }
                    $em->getConnection()->commit();
                    $this->get('session')->getFlashBag()->add('exito', 'El CEA: '. $entity[0]['institucioneducativa'] . ' ,fué habilitado para el operativo.');
                } catch (Exception $ex) {
                    $em->getConnection()->rollback();
                    $this->get('session')->getFlashBag()->add('error', 'Ocurrio un error, el CEA no fué habilitado para el operativo');
                }
            }else{
                $this->get('session')->getFlashBag()->add('error', 'CEA no encontrado para este operativo.');    
            }
            
        }else{
            $this->get('session')->getFlashBag()->add('error', 'No tiene tuición sobre el Centro de Educación Alternativa.');    
        }
        
        return $this->redirect($this->generateUrl('alternativa_operativo_nuevo_reapertura'));
    }

}