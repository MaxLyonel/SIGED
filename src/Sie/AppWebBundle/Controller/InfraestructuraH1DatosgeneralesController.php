<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\InfraestructuraH1Datosgenerales;
use Sie\AppWebBundle\Entity\InfraestructuraH1Institucioneseducativa;
use Sie\AppWebBundle\Form\InfraestructuraH1DatosgeneralesType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * InfraestructuraH1Datosgenerales controller.
 *
 */
class InfraestructuraH1DatosgeneralesController extends Controller
{

    public $session;

    public function __construct(){
        $this->session = new Session();
    }

    //private URL = __DIR__.'/../../../../web/uploads';

    /**
     * Lists all InfraestructuraH1Datosgenerales entities.
     *
     */
    public function indexAction()
    {
        $this->session->set('infjgid', 13395);

        if($this->session->get('infjgid') == null){
            return $this->redirectToRoute('logout');
        }

        $em = $this->getDoctrine()->getManager();

        /**
         * Verficamos que el registro existe
         */
        $infrah1datosgenerales = $em->getRepository('SieAppWebBundle:InfraestructuraH1Datosgenerales')->findOneBy(array(
            'infraestructuraJuridiccionGeografica'=>$this->session->get('infjgid')
        ));

        if(is_object($infrah1datosgenerales)){
            return $this->redirectToRoute('infraestructurah1datosgenerales_edit', array('id'=>$infrah1datosgenerales->getId()));
        }else{
            return $this->redirectToRoute('infraestructurah1datosgenerales_new');
        }

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
            $entity->setInfraestructuraJuridiccionGeografica($em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($this->session->get('infjgid')));
            $em->persist($entity);
            $em->flush();

            /**
             * Upload de fotografias
             */
            $filePrincipal = $form['filePrincipal']->getData();
            $fileFrontal = $form['fileFrontal']->getData();
            $fileLateral = $form['fileLateral']->getData();
            $filePanoramica = $form['filePanoramica']->getData();
            
            if($filePrincipal != null){
                $fileName = md5(uniqid()).'.'.$filePrincipal->guessExtension();
                $filePrincipal->move(__DIR__.'/../../../../web/uploads/infraestructura/datosgenerales', $fileName);
                $entity->setN21FotografiaPrincipal($fileName);
            }
            if($fileFrontal != null){
                $fileName = md5(uniqid()).'.'.$fileFrontal->guessExtension();
                $fileFrontal->move(__DIR__.'/../../../../web/uploads/infraestructura/datosgenerales', $fileName);
                $entity->setN21FotografiaFrontal($fileName);
            }
            if($fileLateral != null){
                $fileName = md5(uniqid()).'.'.$fileLateral->guessExtension();
                $fileLateral->move(__DIR__.'/../../../../web/uploads/infraestructura/datosgenerales', $fileName);
                $entity->setN21FotografiaLateral($fileName);
            }
            if($filePanoramica != null){
                $fileName = md5(uniqid()).'.'.$filePanoramica->guessExtension();
                $filePanoramica->move(__DIR__.'/../../../../web/uploads/infraestructura/datosgenerales', $fileName);
                $entity->setN21FotografiaPanoramica($fileName);
            }

            /**
             * Registro de datos de unidades educativas que operan en el edificio
             */
            $uid = $request->get('uid');
            $usie = $request->get('usie');
            $ucurricular = $request->get('ucurricular');
            $utelefono = $request->get('utelefono');
            $utenencia = $request->get('utenencia');
            $upersona = ($request->get('upersona'))?$request->get('upersona'):null;
            $ubth = $request->get('ubth');

            //dump($uid);
            //dump($usie);
            //dump($utelefono);
            //dump($utenencia);
            //die;

            for ($i=0; $i < count($uid); $i++) { 
                if($uid[$i] == 'new'){

                    $infraH1Institucion = new InfraestructuraH1Institucioneseducativa();

                    $infraH1Institucion->setInfraestructuraH1Datosgenerales($entity);
                    $infraH1Institucion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($usie[$i]));
                    $infraH1Institucion->setOrgcurricularTipo($em->getRepository('SieAppWebBundle:OrgcurricularTipo')->find($ucurricular[$i]));
                    if($upersona[$i] != null){
                        $infraH1Institucion->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($upersona[$i]));
                    }
                    $infraH1Institucion->setTelefonoJe($utelefono[$i]);
                    $infraH1Institucion->setTenenciaTipo($em->getRepository('SieAppWebBundle:InfraestructuraH1TenenciaTipo')->find($utenencia[$i]));
                    $infraH1Institucion->setBthEspecialidad($ubth[$i]);

                    $em->persist($infraH1Institucion);
                    $em->flush();

                }else{
                    $infraH1Institucion = $em->getRepository('SieAppWebBundle:InfraestructuraH1Institucioneseducativa')->find($uid[$i]);
                    $infraH1Institucion->setOrgcurricularTipo($em->getRepository('SieAppWebBundle:OrgcurricularTipo')->find($ucurricular[$i]));
                    if($upersona[$i] != null){
                        $infraH1Institucion->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($upersona[$i]));
                    }
                    $infraH1Institucion->setTelefonoJe($utelefono[$i]);
                    $infraH1Institucion->setTenenciaTipo($em->getRepository('SieAppWebBundle:InfraestructuraH1TenenciaTipo')->find($utenencia[$i]));
                    $infraH1Institucion->setBthEspecialidad($ubth[$i]);

                    $em->persist($infraH1Institucion);
                    $em->flush();
                }
            }

            return $this->redirect($this->generateUrl('infraestructurah1datosgenerales_edit', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH1Datosgenerales:new.html.twig', array(
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

        //$form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH1Datosgenerales entity.
     *
     */
    public function newAction(Request $request)
    {
        $entity = new InfraestructuraH1Datosgenerales();
        $form   = $this->createCreateForm($entity);

        $em = $this->getDoctrine()->getManager();

        $infraJurisdiccion = $em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($this->session->get('infjgid'));

        //dump($infraJurisdiccion);die;

        return $this->render('SieAppWebBundle:InfraestructuraH1Datosgenerales:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'ubicacion'=> $this->getUbicacion($this->session->get('infjgid')),
            'ues'=>$this->getUes($this->session->get('infjgid'))
        ));
    }


    /**
     * Displays a form to edit an existing InfraestructuraH1Datosgenerales entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH1Datosgenerales')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH1Datosgenerales entity.');
        }

        $editForm = $this->createEditForm($entity);

        return $this->render('SieAppWebBundle:InfraestructuraH1Datosgenerales:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'ubicacion'   => $this->getUbicacion($this->session->get('infjgid')),
            'ues'=>$this->getUes($this->session->get('infjgid'))
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

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH1Datosgenerales entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH1Datosgenerales')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH1Datosgenerales entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {

            /**
             * Upload de fotografias
             */
            $filePrincipal = $editForm['filePrincipal']->getData();
            $fileFrontal = $editForm['fileFrontal']->getData();
            $fileLateral = $editForm['fileLateral']->getData();
            $filePanoramica = $editForm['filePanoramica']->getData();
            
            if($filePrincipal != null){
                $fileName = md5(uniqid()).'.'.$filePrincipal->guessExtension();
                $filePrincipal->move(__DIR__.'/../../../../web/uploads/infraestructura/datosgenerales', $fileName);
                $entity->setN21FotografiaPrincipal($fileName);
            }
            if($fileFrontal != null){
                $fileName = md5(uniqid()).'.'.$fileFrontal->guessExtension();
                $fileFrontal->move(__DIR__.'/../../../../web/uploads/infraestructura/datosgenerales', $fileName);
                $entity->setN21FotografiaFrontal($fileName);
            }
            if($fileLateral != null){
                $fileName = md5(uniqid()).'.'.$fileLateral->guessExtension();
                $fileLateral->move(__DIR__.'/../../../../web/uploads/infraestructura/datosgenerales', $fileName);
                $entity->setN21FotografiaLateral($fileName);
            }
            if($filePanoramica != null){
                $fileName = md5(uniqid()).'.'.$filePanoramica->guessExtension();
                $filePanoramica->move(__DIR__.'/../../../../web/uploads/infraestructura/datosgenerales', $fileName);
                $entity->setN21FotografiaPanoramica($fileName);
            }

            /**
             * Registro de datos de unidades educativas que operan en el edificio
             */
            $uid = $request->get('uid');
            $usie = $request->get('usie');
            $ucurricular = $request->get('ucurricular');
            $utelefono = $request->get('utelefono');
            $utenencia = $request->get('utenencia');
            $upersona = ($request->get('upersona'))?$request->get('upersona'):null;
            $ubth = $request->get('ubth');

            for ($i=0; $i < count($uid); $i++) { 
                if($uid[$i] == 'new'){

                    $infraH1Institucion = new InfraestructuraH1Institucioneseducativa();

                    $infraH1Institucion->setInfraestructuraH1Datosgenerales($entity);
                    $infraH1Institucion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($usie[$i]));
                    $infraH1Institucion->setOrgcurricularTipo($em->getRepository('SieAppWebBundle:OrgcurricularTipo')->find($ucurricular[$i]));
                    if($upersona[$i] != null){
                        $infraH1Institucion->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($upersona[$i]));
                    }
                    $infraH1Institucion->setTelefonoJe($utelefono[$i]);
                    $infraH1Institucion->setTenenciaTipo($em->getRepository('SieAppWebBundle:InfraestructuraH1TenenciaTipo')->find($utenencia[$i]));
                    $infraH1Institucion->setBthEspecialidad($ubth[$i]);

                    $em->persist($infraH1Institucion);
                    $em->flush();

                }else{
                    $infraH1Institucion = $em->getRepository('SieAppWebBundle:InfraestructuraH1Institucioneseducativa')->find($uid[$i]);
                    $infraH1Institucion->setOrgcurricularTipo($em->getRepository('SieAppWebBundle:OrgcurricularTipo')->find($ucurricular[$i]));
                    if($upersona[$i] != null){
                        $infraH1Institucion->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($upersona[$i]));
                    }
                    $infraH1Institucion->setTelefonoJe($utelefono[$i]);
                    $infraH1Institucion->setTenenciaTipo($em->getRepository('SieAppWebBundle:InfraestructuraH1TenenciaTipo')->find($utenencia[$i]));
                    $infraH1Institucion->setBthEspecialidad($ubth[$i]);

                    $em->persist($infraH1Institucion);
                    $em->flush();
                }
            }



            $em->flush();

            return $this->redirect($this->generateUrl('infraestructurah1datosgenerales_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH1Datosgenerales:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'ubicacion'   => $this->getUbicacion($entity->getInfraestructuraJuridiccionGeografica()->getId()),
            'ues'=>$this->getUes($id)
        ));
    }
    

    /**
     * Funcion para obtener los datos de ubicacion del edificio educativo
     */
    public function getUbicacion($InfraJurGeoId){
        $em = $this->getDoctrine()->getManager();

        $infraJurisdiccion = $em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($InfraJurGeoId);

        //$jurisdiccionGeografica = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->find($infraJurisdiccion->getJuridiccionGeografica()->getId());

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
                ->setParameter('idJurGeo', $infraJurisdiccion->getJuridiccionGeografica()->getId())
                ->getQuery()
                ->getResult();


        $ubicacion['codigo'] = $ubicacion[0]['codigo_le'];
        $ubicacion['departamentoCodigo'] = $ubicacion[0]['codigo_departamento'];
        $ubicacion['departamentoNombre'] = $ubicacion[0]['departamento'];
        $ubicacion['provinciaCodigo'] = $ubicacion[0]['codigo_provincia'];
        $ubicacion['provinciaNombre'] = $ubicacion[0]['provincia'];
        $ubicacion['municipioCodigo'] = $ubicacion[0]['codigo_seccion'];
        $ubicacion['municipioNombre'] = $ubicacion[0]['seccion'];
        $ubicacion['cantonCodigo'] = $ubicacion[0]['codigo_canton'];
        $ubicacion['cantonNombre'] = $ubicacion[0]['canton'];
        $ubicacion['ciudadCodigo'] = $ubicacion[0]['codigo_localidad'];
        $ubicacion['ciudadNombre'] = $ubicacion[0]['localidad'];
        $ubicacion['distrito'] =$ubicacion[0]['distrito'];
        $ubicacion['area'] = $ubicacion[0]['area2001'];


        return $ubicacion;
    }

    /**
     * Funcion que obtiene las ues que operan dentro del edificio educativo
     */
    public function getUes($id = null){

        $em = $this->getDoctrine()->getManager();

        //$entity = $em->getRepository('SieAppWebBundle:InfraestructuraH1Datosgenerales')->find($id);

        //$InfraJurGeoId = $entity->getInfraestructuraJuridiccionGeografica()->getId();

        $infraJurisdiccion = $em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($id);
        //dump($infraJurisdiccion);die;
        $jurisdiccionGeografica = $em->getRepository('SieAppWebBundle:JurisdiccionGeografica')->find($infraJurisdiccion->getJuridiccionGeografica()->getId());
        

        $institucioneEducativas = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findBy(array(
            'leJuridicciongeografica'=>$jurisdiccionGeografica->getId()
        ));

        // Clasificador de tenencia Tenencia
        $curricular = $em->getRepository('SieAppWebBundle:OrgcurricularTipo')->findAll();
        $tenencia = $em->getRepository('SieAppWebBundle:InfraestructuraH1TenenciaTipo')->findBy(array('gestionTipoId'=>2017));

        $ues = array();
        foreach ($institucioneEducativas as $ie) {
            $infraH1Datosgenerales = $em->getRepository('SieAppWebBundle:InfraestructuraH1Datosgenerales')->findOneBy(array(
                'infraestructuraJuridiccionGeografica'=>$id
            ));
            if($infraH1Datosgenerales){
                $id = $infraH1Datosgenerales->getId();
            }else{
                $id = null;
            }
            // Datos en hi institucioneducativa
            $infraH1Institucion = $em->getRepository('SieAppWebBundle:InfraestructuraH1Institucioneseducativa')->findOneBy(array(
                'infraestructuraH1Datosgenerales'=>$id,
                'institucioneducativa'=>$ie->getId()
            ));

            if(is_object($infraH1Institucion)){
                // Obtenemos el director
                $directorDatos = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array('institucioneducativa'=>$ie->getId(),'gestionTipo'=>$infraJurisdiccion->getGestionTipo()->getId(),'rolTipo'=>9));
                if($directorDatos){
                    $director = $directorDatos->getPersona()->getNombre().' '.$directorDatos->getPersona()->getPaterno().' '.$directorDatos->getPersona()->getMaterno();
                }else{
                    $director = '';
                }

                if($infraH1Institucion->getPersona() != null){
                    $personaId = $infraH1Institucion->getPersona()->getId();
                    $personaCarnet = $infraH1Institucion->getPersona()->getCarnet();
                    $persona = $infraH1Institucion->getPersona()->getNombre().' '.$infraH1Institucion->getPersona()->getPaterno().' '.$infraH1Institucion->getPersona()->getMaterno();
                }else{
                    $personaId = '';
                    $personaCarnet = '';
                    $persona = '';
                }

                // Generamos el array
                $ues[] = array(
                    'id'=>$infraH1Institucion->getId(),
                    'sie'=>$infraH1Institucion->getInstitucioneducativa()->getId(),
                    'institucion'=>$infraH1Institucion->getInstitucioneducativa()->getId().' - '.$infraH1Institucion->getInstitucioneducativa()->getInstitucioneducativa(),
                    'personaId'=>$personaId,
                    'personaCarnet'=>$personaCarnet,
                    'persona'=>$persona,
                    'telefono'=>$infraH1Institucion->getTelefonoJe(),
                    'tenencia'=>$infraH1Institucion->getTenenciaTipo()->getId(),
                    'orgCurricular'=>$infraH1Institucion->getOrgcurricularTipo()->getId(),
                    'orgCurricularNombre'=>$infraH1Institucion->getOrgcurricularTipo()->getOrgcurricula(),
                    'bthEspecialidad'=>$infraH1Institucion->getBthEspecialidad(),
                    'director'=>$director,
                    'tenenciaTipo'=>$tenencia,
                    'curricularTipo'=>$curricular
                );

            }else{
                $ues[] = array(
                    'id'=>'new',
                    'sie'=>$ie->getId(),
                    'institucion'=>$ie->getId().' - '.$ie->getInstitucioneducativa(),
                    'personaId'=>'',
                    'personaCarnet'=>'',
                    'persona'=>'',
                    'telefono'=>'',
                    'tenencia'=>'',
                    'orgCurricular'=>'',
                    'orgCurricularNombre'=>'',
                    'bthEspecialidad'=>'',
                    'director'=>'',
                    'tenenciaTipo'=>$tenencia,
                    'curricularTipo'=>$curricular
                );
            }
        }

        //dump($ues);die;

        return $ues;
    }

    public function buscarPersonaAction(Request $request){

        $carnet = $request->get('carnet');
        $complemento = ($request->get('complemento') != "")? $request->get('complemento'):0;
        $fechaNacimiento = $request->get('fechaNacimiento');

        $servicioPersona = $this->get('sie_app_web.persona')->buscarPersonaPorCarnetComplementoFechaNacimiento($carnet, $complemento, $fechaNacimiento);

        $servicioPersona = json_encode($servicioPersona);
        $servicioPersona = json_decode($servicioPersona, true);

        if (is_array($servicioPersona['result'])) {

            $fechaNacimiento = $servicioPersona['result'][0]['fecha_nacimiento'];
            $servicioPersona['result'][0]['fecha_nacimiento'] = date_format(new \DateTime($fechaNacimiento), 'd-m-Y');


            $p = array(
                'personaId'=>$servicioPersona['result'][0]['id'],
                'personaCarnet'=>$servicioPersona['result'][0]['carnet'],
                'personaNombre'=>$servicioPersona['result'][0]['nombre'].' '.$servicioPersona['result'][0]['paterno'].' '.$servicioPersona['result'][0]['materno'],
            );

        }else{
            $p = null;
        }

        $response = new JsonResponse();
        
        return $response->setData($p);
    }

}
