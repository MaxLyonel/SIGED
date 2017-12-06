<?php

namespace Sie\InfraestructuraBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\InfraestructuraH1Datosgenerales;
use Sie\AppWebBundle\Form\InfraestructuraH1DatosgeneralesType;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\InfraestructuraH1Institucioneseducativa;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * InfraestructuraH1Datosgenerales controller.
 *
 */
class InfraestructuraH1DatosgeneralesController extends Controller
{

    public $session;
    public $infJurGeoId;
    public $institucionesEducativas;
    public function __construct() {
        $this->session = new Session();
        $this->infJurGeoId = $this->session->get('infJurGeoId');
    }

    /**
     * Lists all InfraestructuraH1Datosgenerales entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH1Datosgenerales')->findOneBy(array('infraestructuraJuridiccionGeografica'=>$this->infJurGeoId));

        if($entities){
            return $this->redirectToRoute('infraestructurah1datosgenerales_edit');
        }else{
            return $this->redirectToRoute('infraestructurah1datosgenerales_new');
        }

        /*return $this->render('SieInfraestructuraBundle:InfraestructuraH1Datosgenerales:index.html.twig', array(
            'entities' => $entities,
        ));*/

    }
    /**
     * Creates a new InfraestructuraH1Datosgenerales entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH1Datosgenerales();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('infraestructura_h1_datosgenerales');")->execute();
            $entity->setInfraestructuraJuridiccionGeografica($em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($this->infJurGeoId));
            $entity->setFecharegistro(new \DateTime('now'));
            $em->persist($entity);
            $em->flush();

            return $this->redirectToRoute('infra_info_acceder');
        }

        return $this->render('SieInfraestructuraBundle:InfraestructuraH1Datosgenerales:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),

        ));
        
    }

    /**
     * Creates a form to create a InfraestructuraH1Datosgenerales entity.
     *
     * @param InfraestructuraH1Datosgenerales $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH1Datosgenerales $entity)
    {
        $form = $this->createForm(new InfraestructuraH1DatosgeneralesType(), $entity, array(
            'action' => $this->generateUrl('infraestructurah1datosgenerales_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar','attr'=>array('class'=>'btn btn-primary')));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH1Datosgenerales entity.
     *
     */
    public function newAction()
    {
        $entity = new InfraestructuraH1Datosgenerales();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieInfraestructuraBundle:InfraestructuraH1Datosgenerales:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'ubicacion'=>$this->datosUbicacion(),
            'institucionesEducativas'=>null
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH1Datosgenerales entity.
     *
     */
    public function editAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH1Datosgenerales')->find($this->infJurGeoId);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH1Datosgenerales entity.');
        }

        $editForm = $this->createEditForm($entity);
        //dump($this->datosUbicacion());die;
        return $this->render('SieInfraestructuraBundle:InfraestructuraH1Datosgenerales:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'institucionesEducativas'=>null,
            'ubicacion'=>$this->datosUbicacion(),
            'institucionesEducativas'=>$this->institucionesEducativas(),
        ));
    }

    /**
    * Creates a form to edit a InfraestructuraH1Datosgenerales entity.
    *
    * @param InfraestructuraH1Datosgenerales $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH1Datosgenerales $entity)
    {
        $form = $this->createForm(new InfraestructuraH1DatosgeneralesType(), $entity, array(
            'action' => $this->generateUrl('infraestructurah1datosgenerales_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar','attr'=>array('class'=>'btn btn-primary')));

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH1Datosgenerales entity.
     *
     */
    public function updateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH1Datosgenerales')->find($this->infJurGeoId);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH1Datosgenerales entity.');
        }
        //dump($_POST['sie_appwebbundle_infraestructurah1datosgenerales']);die;

        $variables = $_POST['sie_appwebbundle_infraestructurah1datosgenerales'];

        foreach ($variables as $a=>$b) {
            $$a = mb_strtoupper($b,'utf-8');
            $_POST['sie_appwebbundle_infraestructurah1datosgenerales'] = $$a;
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirectToRoute('infra_info_acceder');
        }

        return $this->render('SieInfraestructuraBundle:InfraestructuraH1Datosgenerales:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'institucionesEducativas'=>null,
        ));
    }

    public function datosUbicacion(){
        $em = $this->getDoctrine()->getManager();
        $infJurGeo = $em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($this->infJurGeoId);
        $jurGeo = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->find($infJurGeo->getJuridiccionGeografica()->getId());

        $repository = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica');
        $ubicacion = $repository->createQueryBuilder('jg')
                ->select('lt4.codigo AS codigo_departamento,
                        lt4.lugar AS departamento,
                        lt3.codigo AS codigo_provincia,
                        lt3.lugar AS provincia,
                        lt2.codigo AS codigo_seccion,
                        lt2.lugar AS seccion,
                        lt1.codigo AS codigo_canton,
                        lt1.lugar AS canton,
                        lt.codigo AS codigo_localidad,
                        lt.lugar AS localidad,
                        dist.id AS codigo_distrito,
                        dist.distrito,
                        jg.id AS codigo_le,
                        lt.area2001')
                ->join('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'jg.lugarTipoLocalidad = lt.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt1', 'WITH', 'lt.lugarTipo = lt1.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt2', 'WITH', 'lt1.lugarTipo = lt2.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt3', 'WITH', 'lt2.lugarTipo = lt3.id')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'lt4', 'WITH', 'lt3.lugarTipo = lt4.id')
                ->join('SieAppWebBundle:DistritoTipo', 'dist', 'WITH', 'jg.distritoTipo = dist.id')
                ->where('jg.id = :idJurGeo')
                ->setParameter('idJurGeo', $jurGeo->getId())
                ->getQuery()
                ->getResult();

        return $ubicacion;
    }

    public function institucionesEducativas(){
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH1Datosgenerales')->findOneBy(array('infraestructuraJuridiccionGeografica'=>$this->infJurGeoId));
        $instituciones = $em->getRepository('SieAppWebBundle:InfraestructuraH1Institucioneseducativa')->findby(array('infraestructuraH1Datosgenerales'=>$entities->getId()));

        $instituciones = $em->getRepository('SieAppWebBundle:InfraestructuraH1Institucioneseducativa')
                            ->createQueryBuilder('iie')
                            ->select('iie')
                            ->join('SieAppWebBundle:InfraestructuraH1Datosgenerales','idg','with','iie.infraestructuraH1Datosgenerales = idg.id')
                            ->leftJoin('SieAppWebBundle:Institucioneducativa','ie','with','iie.institucioneducativa = ie.id')
                            ->leftJoin('SieAppWebBundle:Persona','p','with','iie.persona = p.id')
                            ->leftJoin('SieAppWebBundle:InfraestructuraH1TenenciaTipo','tt','with','iie.tenenciaTipo = tt.id')
                            ->leftJoin('SieAppWebBundle:OrgcurricularTipo','oct','with','iie.orgcurricularTipo = oct.id')
                            ->where('idg.id = :idDatosgenerales')
                            ->setParameter('idDatosgenerales',$entities->getId())
                            ->getQuery()
                            ->getResult();

        return $instituciones;
    }

    /*
        Gestion de unidades educativas que operan en el edificio escolar
    */

    public function uenewAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $form = $this->createFormBuilder()
                        ->add('datosGeneralesId','hidden',array('data'=>$request->get('id')))
                        ->add('codUe','text',array('label'=>'Cod.UE','attr'=>array('class'=>'form-control')))
                        ->add('unidadEducativa','text',array('label'=>'Unidad Educativa','attr'=>array('class'=>'form-control','disabled'=>'disabled')))
                        ->add('personaId','hidden',array('data'=>'12345'))
                        ->add('persona','text',array('label'=>'Junta Escolar','attr'=>array('class'=>'form-control')))
                        ->add('telefono','text',array('label'=>'Teléfono','attr'=>array('class'=>'form-control')))
                        ->add('tenencia','entity',array('label'=>'Tenencia','class'=>'SieAppWebBundle:InfraestructuraH1TenenciaTipo','attr'=>array('class'=>'form-control')))
                        ->add('orgCurricular','entity',array('label'=>'Organización Curricular','class'=>'SieAppWebBundle:OrgcurricularTipo','attr'=>array('class'=>'form-control')))
                        ->getForm();

            return $this->render('SieInfraestructuraBundle:InfraestructuraH1Datosgenerales:uenew.html.twig',array('form'=>$form->createView()));   
        } catch (Exception $e) {
            
        }
    }

    public function uecreateAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('infraestructura_h1_institucioneseducativa');")->execute();
            $form = $request->get('form');
            $newInstitucion = new InfraestructuraH1Institucioneseducativa;
            $newInstitucion->setInfraestructuraH1datosgenerales($em->getRepository('SieAppWebBundle:InfraestructuraH1Datosgenerales')->find($form['datosGeneralesId']));
            $newInstitucion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['codUe']));
            $newInstitucion->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($form['personaId']));
            $newInstitucion->setTelefonoJe($form['telefono']);
            $newInstitucion->setTenenciaTipo($em->getRepository('SieAppWebBundle:InfraestructuraH1TenenciaTipo')->find($form['tenencia']));
            $newInstitucion->setOrgcurricularTipo($em->getRepository('SieAppWebBundle:OrgcurricularTipo')->find($form['orgCurricular']));
            $em->persist($newInstitucion);
            $em->flush();
            $em->getConnection()->commit();
            return $this->render('SieInfraestructuraBundle:InfraestructuraH1Datosgenerales:ueasociadas.html.twig',array('institucionesEducativas'=>$this->institucionesEducativas()));
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            return new JsonResponse(array('mensaje'=>'Error'));
        }
    }

    public function ueeditAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $infue = $em->getRepository('SieAppWebBundle:InfraestructuraH1Institucioneseducativa')->find($request->get('id'));
            $form = $this->createFormBuilder()
                        ->add('id','hidden',array('data'=>$request->get('id')))
                        ->add('codUe','text',array('label'=>'Cod.UE','data'=>$infue->getInstitucioneducativa()->getId(),'attr'=>array('class'=>'form-control')))
                        ->add('unidadEducativa','text',array('label'=>'Unidad Educativa','attr'=>array('class'=>'form-control','disabled'=>'disabled')))
                        ->add('personaId','hidden',array('data'=>$infue->getPersona()->getId()))
                        ->add('persona','text',array('label'=>'Junta Escolar','attr'=>array('class'=>'form-control')))
                        ->add('telefono','text',array('label'=>'Teléfono','data'=>$infue->getTelefonoJe(),'attr'=>array('class'=>'form-control')))
                        ->add('tenencia','entity',array('label'=>'Tenencia','class'=>'SieAppWebBundle:InfraestructuraH1TenenciaTipo','data'=>$em->getReference('SieAppWebBundle:InfraestructuraH1TenenciaTipo',$infue->getTenenciaTipo()->getId()),'attr'=>array('class'=>'form-control')))
                        ->add('orgCurricular','entity',array('label'=>'Organización Curricular','class'=>'SieAppWebBundle:OrgcurricularTipo','data'=>$em->getReference('SieAppWebBundle:OrgcurricularTipo',$infue->getOrgcurricularTipo()->getId()),'attr'=>array('class'=>'form-control')))
                        ->getForm();

            return $this->render('SieInfraestructuraBundle:InfraestructuraH1Datosgenerales:ueedit.html.twig',array('form'=>$form->createView()));   
        } catch (Exception $e) {
            
        }
    }

    public function ueupdateAction(Request $request){
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $form = $request->get('form');
            $newInstitucion = $em->getRepository('SieAppWebBundle:InfraestructuraH1Institucioneseducativa')->find($form['id']);
            $newInstitucion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['codUe']));
            $newInstitucion->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($form['personaId']));
            $newInstitucion->setTelefonoJe($form['telefono']);
            $newInstitucion->setTenenciaTipo($em->getRepository('SieAppWebBundle:InfraestructuraH1TenenciaTipo')->find($form['tenencia']));
            $newInstitucion->setOrgcurricularTipo($em->getRepository('SieAppWebBundle:OrgcurricularTipo')->find($form['orgCurricular']));
            $em->persist($newInstitucion);
            $em->flush();
            $em->getConnection()->commit();
            return $this->render('SieInfraestructuraBundle:InfraestructuraH1Datosgenerales:ueasociadas.html.twig',array('institucionesEducativas'=>$this->institucionesEducativas()));
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            return new JsonResponse(array('mensaje'=>'Error'));
        }
    }

    public function uedeleteAction(Request $request){
        try{
            $id = $request->get('id');
            $em = $this->getDoctrine()->getManager();
            $institucion = $em->getRepository('SieAppWebBundle:InfraestructuraH1Institucioneseducativa')->find($id);
            $em->remove($institucion);
            $em->flush();
            return $this->render('SieInfraestructuraBundle:InfraestructuraH1Datosgenerales:ueasociadas.html.twig',array('institucionesEducativas'=>$this->institucionesEducativas()));
        }catch(Exception $e){

        }
    }
    
}
