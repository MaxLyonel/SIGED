<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\PersonaAdministrativoInscripcion;
use Sie\AppWebBundle\Entity\MaestroInscripcion;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Entity\DistritoControlOperativoMenus;

/**
 * PersonalAdministrativoGestion controller.
 *
 */
class PersonalAdministrativoInscripcionController extends Controller {

    public $session;
    public $idInstitucion;
    public $codDistrito;

    public $lugarInfo;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->codDistrito = 0;
        $this->lugarInfo = 0;
    }

    /**
     * Muestra el formulario de búsqueda.
     */
    public function indexAction(Request $request) {

        $id_usuario = $this->session->get('userId');
        $id_rol = $this->session->get('roluser');

        $datosUser=$this->getDatosUsuario($id_usuario,$id_rol);
        $this->lugarInfo = $datosUser;
        $this->codDistrito=$datosUser['cod_dis'];
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SieRegularBundle:PersonalAdministrativoInscripcion:search.html.twig', array(
                    'form' => $this->formSearch($request->getSession()->get('currentyear'))->createView(),
        ));
    }

    private function getDatosUsuario($userId,$userRol){
        
        $userId=($userId)?$userId:-1;
        $userRol=($userRol)?$userRol:-1;

        $user=NULL;
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = '
        select *
        from (
        select b.rol_tipo_id,(select rol from rol_tipo where id=b.rol_tipo_id) as rol,a.persona_id,c.codigo as cod_dis,dt2.id as cod_dep,a.esactivo,a.id as user_id
        from usuario a 
          inner join usuario_rol b on a.id=b.usuario_id 
            inner join lugar_tipo c on b.lugar_tipo_id=c.id
               inner join distrito_tipo dt on c.codigo::INTEGER = dt.id
                inner join departamento_tipo dt2 on dt.departamento_tipo_id = dt2.id                
        where c.codigo not in (\'04\') and b.rol_tipo_id not in (2,3,9,29,26,21,14,39,6) and a.esactivo=\'t\'
        union all
        select f.rol_tipo_id,(select rol from rol_tipo where id=f.rol_tipo_id) as rol,a.persona_id,d.codigo as cod_dis,dt2.id as cod_dep,e.esactivo,e.id as user_id
        from maestro_inscripcion a
          inner join institucioneducativa b on a.institucioneducativa_id=b.id
            inner join jurisdiccion_geografica c on b.le_juridicciongeografica_id=c.id
              inner join lugar_tipo d on d.lugar_nivel_id=7 and c.lugar_tipo_id_distrito=d.id
                inner join usuario e on a.persona_id=e.persona_id
                  inner join usuario_rol f on e.id=f.usuario_id
                    inner join distrito_tipo dt on d.codigo::INTEGER = dt.id
                      inner join departamento_tipo dt2 on dt.departamento_tipo_id = dt2.id                  
        where a.gestion_tipo_id=2021 and cargo_tipo_id in (1,12) and periodo_tipo_id=1 and f.rol_tipo_id=9 and e.esactivo=\'t\') a
        where user_id = ?
        and rol_tipo_id = ?
        ORDER BY cod_dis
        LIMIT 1
        ';
        $stmt = $db->prepare($query);
        $params = array($userId,$userRol);
        $stmt->execute($params);
        $user=$stmt->fetch();
        return $user;
    }    

    /**
     * list of request
     *
     */
    public function listAction(Request $request) {
        //get the session's values

        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $id_rol = $this->session->get('roluser');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $id_usuario = $this->session->get('userId');
        $id_rol = $this->session->get('roluser');

        $this->lugarInfo=$this->getDatosUsuario($id_usuario,$id_rol); 
        
        ////////////////////////////////////////////////////
        $em = $this->getDoctrine()->getManager();

        if ($request->getMethod() == 'POST') {
            $form = $request->get('form');

            $institucion = $form['institucioneducativa'];
            $gestion = $form['gestion'];
            $request->getSession()->set('idInstitucion', $institucion);
            $request->getSession()->set('idGestion', $gestion);

            // creamos variables de sesion de la institucion educativa y gestion
        } else {
            $institucion = $request->getSession()->get('idInstitucion');
            $gestion = $request->getSession()->get('idGestion');
        }

        $institucioneducativa = $em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($institucion);
        
        if (!$institucioneducativa) {
            $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
            return $this->render('SieRegularBundle:PersonalAdministrativoInscripcion:index.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }

        $query = $em->createQuery(
                        'SELECT mi, per, at FROM SieAppWebBundle:PersonaAdministrativoInscripcion mi
                    INNER JOIN mi.persona per
                    INNER JOIN mi.personaAdministrativoInscripcionTipo at
                    WHERE mi.distritoTipo = :idDistrito
                    AND mi.gestionTipo = :gestion
                    ORDER BY per.paterno, per.materno, per.nombre')
                ->setParameter('idDistrito', $institucion)
                ->setParameter('gestion', $gestion);
        $personal = $query->getResult();

        
        $objDistritoControlOperativoMenus = $em->getRepository('SieAppWebBundle:DistritoControlOperativoMenus')->findOneBy(array('departamentoTipoId'=>$this->lugarInfo['cod_dep'],'distritoTipo'=>$this->lugarInfo['cod_dis'],'gestionTipoId'=>$gestion));
        $swCloseOperative = false;
        if(sizeof($objDistritoControlOperativoMenus) > 0){
            $swCloseOperative = true;
        }

        return $this->render('SieRegularBundle:PersonalAdministrativoInscripcion:index.html.twig', array(
                    'personal' => $personal,
                    'institucion' => $institucioneducativa,
                    'gestion' => $gestion,
                    'swCloseOperative' => $swCloseOperative,
                    'lugarInfo' => $this->lugarInfo
        ));
    }

    /*
     * Llamamos al formulario para nuevo maestro
     */

    public function newAction(Request $request) {

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $request->get('form');

        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:DistritoTipo')->find($request->getSession()->get('idInstitucion'));
        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['idPersona']);

        return $this->render('SieRegularBundle:PersonalAdministrativoInscripcion:new.html.twig', array(
                    'form' => $this->newForm($form['idInstitucion'], $form['gestion'], $form['idPersona'])->createView(),
                    'institucion' => $institucion,
                    'gestion' => $request->getSession()->get('idGestion'),
                    'persona' => $persona
        ));
    }

    /*
     * formulario de nuevo maestro
     */

    private function newForm($idInstitucion, $gestion, $idPersona) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $query = $em->createQuery('SELECT ct FROM SieAppWebBundle:PersonaAdministrativoInscripcionTipo ct ORDER BY ct.personaAdministrativoInscripcion');
        $cargos = $query->getResult();
        $cargosArray = array();
        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getPersonaAdministrativoInscripcion();
        }
        //get salud information
        $query = $em->createQuery('SELECT st FROM SieAppWebBundle:EstadosaludTipo st where st.esactivo=:istrue ORDER BY st.id')->setParameter('istrue', 1);
        $healthType = $query->getResult();
        $healthTypeArray = array();
        foreach ($healthType as $v) {
            $healthTypeArray[$v->getId()] = $v->getEstadosalud();
        }
        $arrVacuna = array('NO VACUNADO','SI VACUNADO');
        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($idPersona);

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('personaladministrativoinscripcion_create'))
                ->add('distrito', 'hidden', array('data' => $idInstitucion))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('persona', 'hidden', array('data' => $idPersona))
                ->add('tipo', 'choice', array('label' => 'Cargo', 'required' => true, 'choices' => $cargosArray, 'attr' => array('class' => 'form-control')))
                ->add('health', 'choice', array('label' => 'Estado Salud', 'required' => true, 'choices' => $healthTypeArray, 'attr' => array('class' => 'form-control')))
                ->add('vacuna', 'choice', array('label' => 'Vacuna', 'required' => true, 'choices' => $arrVacuna, 'attr' => array('class' => 'form-control')))
                ->add('obs', 'textarea', array('label' => 'Observación', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '90')))
                ->add('celular', 'text', array('label' => 'Nro de Celular', 'required' => false, 'data' => $persona->getCelular(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))
                ->add('correo', 'text', array('label' => 'Correo Electrónico', 'required' => false, 'data' => $persona->getCorreo(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))
                ->add('direccion', 'text', array('label' => 'Dirección de Domicilio', 'required' => false, 'data' => $persona->getDireccion(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        $em->getConnection()->commit();
        return $form;
    }

    /*
     * registramos el nuevo maestro
     */

    public function createAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');

            // Verificar si la persona ya esta registrada            
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['persona']);

            // verificamos si el personal administrativo esta registrado ya en tabla maestro inscripcion
            $queryIns = $em->createQuery(
                            'SELECT mi FROM SieAppWebBundle:PersonaAdministrativoInscripcion mi
                    WHERE mi.distritoTipo = :idInstitucion
                    AND mi.gestionTipo = :gestion
                    AND mi.persona = :pers')
                    ->setParameter('idInstitucion', $form['distrito'])
                    ->setParameter('gestion', $form['gestion'])
                    ->setParameter('pers', $persona);
            $maestroInscripcion = $queryIns->getResult();

            if ($maestroInscripcion) { // Si  el personalAdministrativo ya esta inscrito
                $this->get('session')->getFlashBag()->add('newError', 'No se realizó el registro, la persona ya esta registrada');
                return $this->redirect($this->generateUrl('personaladministrativoinscripcion_list'));
            }

            //$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('persona_administrativo_inscripcion');")->execute();
            //Actualizar datos persona
            $persona->setDireccion(mb_strtoupper($form['direccion'], 'utf-8'));
            $persona->setCorreo($form['correo']);
            $persona->setCelular($form['celular']);
            $em->persist($persona);
            $em->flush();

            // Registro Maestro inscripcion
            $maestroinscripcion = new PersonaAdministrativoInscripcion();
            $maestroinscripcion->setPersonaAdministrativoInscripcionTipo($em->getRepository('SieAppWebBundle:PersonaAdministrativoInscripcionTipo')->findOneById($form['tipo']));
            $maestroinscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($form['gestion']));
            $maestroinscripcion->setDistritoTipo($em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($form['distrito']));
            $maestroinscripcion->setEstadosaludTipo($em->getRepository('SieAppWebBundle:EstadosaludTipo')->find($form['health']));
            $maestroinscripcion->setEsvacuna(($form['vacuna']));
            $maestroinscripcion->setObs(mb_strtoupper($form['obs'], 'utf-8'));
            $maestroinscripcion->setPersona($persona);
            $maestroinscripcion->setEsactivo(1);
            $em->persist($maestroinscripcion);
            $em->flush();

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente');
            return $this->redirect($this->generateUrl('personaladministrativoinscripcion_list'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /**
     * open the request
     * @param Request $request
     * @return obj with the selected request 
     */
    public function openAction(Request $request) {
        //get session data
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieRegularBundle:PersonalAdministrativoInscripcion:open.html.twig', array());
    }

    //Selección del Personal Administrativo
    public function esVigenteAdministrativoAction(Request $request) {
        try {
            $this->session = new Session();

            // Verificamos si no ha caducado la session
            if (!$this->session->get('userId')) {
                return $this->redirect($this->generateUrl('login'));
            }

            $em = $this->getDoctrine()->getManager();
            $gestion = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($request->get('gestion'));
            $institucion = $em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($request->get('idInstitucion'));
            $maestroInscripcion = $em->getRepository('SieAppWebBundle:PersonaAdministrativoInscripcion')->findOneBy(array('id' => $request->get('idMaestroInscripcion'), 'gestionTipo' => $gestion, 'distritoTipo' => $institucion));

            $maestroInscripcion->setEsactivo(1 - $maestroInscripcion->getEsactivo());

            $em->persist($maestroInscripcion);
            $em->flush();

            return $this->redirect($this->generateUrl('personaladministrativoinscripcion_list'));
        } catch (Exception $ex) {
            
        }
    }

    /*
     * Llamar al formulario de edicion
     */

    public function editAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($request->get('idPersona'));
        $maestroInscripcion = $em->getRepository('SieAppWebBundle:PersonaAdministrativoInscripcion')->findOneById($request->get('idMaestroInscripcion'));

        $institucion = $em->getRepository('SieAppWebBundle:DistritoTipo')->find($request->getSession()->get('idInstitucion'));

        return $this->render('SieRegularBundle:PersonalAdministrativoInscripcion:edit.html.twig', array(
                    'form' => $this->editForm($request->getSession()->get('idInstitucion'), $request->getSession()->get('idGestion'), $persona, $maestroInscripcion)->createView(),
                    'institucion' => $institucion,
                    'gestion' => $request->getSession()->get('idGestion'),
                    'persona' => $persona
        ));
    }

    /*
     * formulario de edicion
     */

    private function editForm($idInstitucion, $gestion, $persona, $maestroInscripcion) {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT ct FROM SieAppWebBundle:PersonaAdministrativoInscripcionTipo ct ORDER BY ct.personaAdministrativoInscripcion');

        $cargos = $query->getResult();
        $cargosArray = array();
        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getPersonaAdministrativoInscripcion();
        }
        //get health information
        $query = $em->createQuery('SELECT st FROM SieAppWebBundle:EstadosaludTipo st where st.esactivo=:istrue ORDER BY st.id')->setParameter('istrue', 1);
        $healthType = $query->getResult();
        $healthTypeArray = array();
        foreach ($healthType as $v) {
            $healthTypeArray[$v->getId()] = $v->getEstadosalud();
        }        
        $arrVacuna = array('NO VACUNADO','SI VACUNADO');

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('personaladministrativoinscripcion_update'))
                ->add('distrito', 'hidden', array('data' => $idInstitucion))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('persona', 'hidden', array('data' => $persona->getId()))
                ->add('idMaestroInscripcion', 'hidden', array('data' => $maestroInscripcion->getId()))
                ->add('tipo', 'choice', array('label' => 'Administrativo Tipo', 'required' => true, 'choices' => $cargosArray, 'data' => $maestroInscripcion->getPersonaAdministrativoInscripcionTipo()->getId(), 'attr' => array('class' => 'form-control')))
                ->add('health', 'choice', array('label' => 'Estado Salud', 'required' => true, 'choices' => $healthTypeArray,'data' => $maestroInscripcion->getEstadosaludTipo()->getId(), 'attr' => array('class' => 'form-control')))
                ->add('vacuna', 'choice', array('label' => 'Vacuna', 'required' => true, 'choices' => $arrVacuna,'data' => $maestroInscripcion->getEsvacuna(), 'attr' => array('class' => 'form-control')))                
                ->add('obs', 'textarea', array('label' => 'Observación', 'required' => false, 'data' => $maestroInscripcion->getObs(), 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '90')))
                ->add('celular', 'text', array('label' => 'Nro de Celular', 'required' => false, 'data' => $persona->getCelular(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))
                ->add('correo', 'text', array('label' => 'Correo Electrónico', 'required' => false, 'data' => $persona->getCorreo(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))
                ->add('direccion', 'text', array('label' => 'Dirección de Domicilio', 'required' => false, 'data' => $persona->getDireccion(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

        return $form;
    }

    /*
     * guardar datos de modificacion
     */

    public function updateAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');

            // Verificar si la persona ya esta registrada            
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['persona']);

            //Actualizar datos persona
            $persona->setDireccion(mb_strtoupper($form['direccion'], 'utf-8'));
            $persona->setCorreo($form['correo']);
            $persona->setCelular($form['celular']);
            $em->persist($persona);
            $em->flush();

            // Registro Maestro inscripcion
            $maestroinscripcion = $em->getRepository('SieAppWebBundle:PersonaAdministrativoInscripcion')->findOneById($form['idMaestroInscripcion']);
            $maestroinscripcion->setPersonaAdministrativoInscripcionTipo($em->getRepository('SieAppWebBundle:PersonaAdministrativoInscripcionTipo')->findOneById($form['tipo']));
            $maestroinscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($form['gestion']));
            $maestroinscripcion->setDistritoTipo($em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($form['distrito']));
            $maestroinscripcion->setEstadosaludTipo($em->getRepository('SieAppWebBundle:EstadosaludTipo')->find($form['health']));
            $maestroinscripcion->setEsvacuna(($form['vacuna']));
            $maestroinscripcion->setObs(mb_strtoupper($form['obs'], 'utf-8'));
            $maestroinscripcion->setPersona($persona);
            $maestroinscripcion->setEsactivo(1);
            $em->persist($maestroinscripcion);
            $em->flush();

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('updateOk', 'Datos modificados correctamente');
            return $this->redirect($this->generateUrl('personaladministrativoinscripcion_list'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'Error en la modificacion de datos');
            return $this->redirect($this->generateUrl('personaladministrativoinscripcion_list'));
        }
    }

    /*
     * Eliminacion de maestro
     */

    public function deleteAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $maestroInscripcion = $em->getRepository('SieAppWebBundle:PersonaAdministrativoInscripcion')->findOneById($request->get('idMaestroInscripcion'));

            if ($maestroInscripcion) { // Sie existe el maestro inscrito
                //eliminamos el registro de inscripcion del maestro
                $em->remove($maestroInscripcion);
                $em->flush();
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('eliminarOk', 'El registro fue eliminado exitosamente');

                return $this->redirect($this->generateUrl('personaladministrativoinscripcion_list'));
            }
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('eliminarError', 'El registro no se pudo eliminar');
            return $this->redirect($this->generateUrl('personaladministrativoinscripcion_list'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    public function findAction(Request $request) {

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:DistritoTipo')->find($request->getSession()->get('idInstitucion'));

        return $this->render('SieRegularBundle:PersonalAdministrativoInscripcion:search_persona.html.twig', array(
                    'form' => $this->searchForm($request->getSession()->get('idInstitucion'), $request->getSession()->get('idGestion'))->createView(),
                    'institucion' => $institucion,
                    'gestion' => $request->getSession()->get('idGestion')
        ));
    }

    public function resultAction(Request $request) {

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        $persona = $this->get('sie_app_web.persona')->buscarPersonaPorCarnetComplemento($form);

        $institucion = $em->getRepository('SieAppWebBundle:DistritoTipo')->find($request->getSession()->get('idInstitucion'));

        return $this->render('SieRegularBundle:PersonalAdministrativoInscripcion:result.html.twig', array(
                    'persona' => $persona,
                    'institucion' => $institucion,
                    'gestion' => $request->getSession()->get('idGestion')
        ));
    }

    /**
     * Crea el formulario para buscar una persona por C.I.
     *
     */
    private function searchForm() {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('personaladministrativoinscripcion_result'))
                ->add('carnet', 'text', array('label' => 'Carnet de Identidad', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'pattern' => '[0-9]{5,10}', 'maxlength' => '11')))
                ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'attr' => array('class' => 'form-control jonlynumbersletters jupper', 'maxlength' => '2', 'autocomplete' => 'off')))
                ->add('buscar', 'submit', array('label' => 'Buscar coincidencias por C.I.', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        return $form;
    }

    /*
     * formularios de busqueda de institucion educativa
     */

    private function formSearch($gestionactual) {
        $gestiones = array($gestionactual => $gestionactual);
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('personaladministrativoinscripcion_list'))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'));
        if($this->codDistrito != 0){
            $form = $form 
                    ->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'on','readonly'=>true,'value'=>$this->codDistrito, 'maxlength' => 4)));
        }else{
            $form = $form 
                    ->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'on', 'maxlength' => 4)));            

        }
        $form = $form 
                ->getForm();
        return $form;
    }

    public function newpersonaAction(Request $request) {

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $request->get('form');

        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:DistritoTipo')->find($request->getSession()->get('idInstitucion'));

        return $this->render('SieRegularBundle:PersonalAdministrativoInscripcion:newpersona.html.twig', array(
                    'form' => $this->newpersonaForm($form['idInstitucion'], $form['gestion'])->createView(),
                    'institucion' => $institucion,
                    'gestion' => $request->getSession()->get('idGestion'),
        ));
    }

    /*
     * formulario de nuevo maestro
     */

    private function newpersonaForm($idInstitucion, $gestion) {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT ct FROM SieAppWebBundle:PersonaAdministrativoInscripcionTipo ct ORDER BY ct.personaAdministrativoInscripcion');

        $cargos = $query->getResult();
        $cargosArray = array();
        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getPersonaAdministrativoInscripcion();
        }
        //get salud information
        $query = $em->createQuery('SELECT st FROM SieAppWebBundle:EstadosaludTipo st where st.esactivo=:istrue ORDER BY st.id')->setParameter('istrue', 1);
        $healthType = $query->getResult();
        $healthTypeArray = array();
        foreach ($healthType as $v) {
            $healthTypeArray[$v->getId()] = $v->getEstadosalud();
        }
        $arrVacuna = array('NO VACUNADO','SI VACUNADO');        
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('personaladministrativoinscripcion_persona_create'))
                ->add('distrito', 'hidden', array('data' => $idInstitucion))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('tipo', 'choice', array('label' => 'Administrativo Tipo', 'required' => true, 'choices' => $cargosArray, 'attr' => array('class' => 'form-control')))
                ->add('health', 'choice', array('label' => 'Estado Salud', 'required' => true, 'choices' => $healthTypeArray, 'attr' => array('class' => 'form-control')))  
                ->add('vacuna', 'choice', array('label' => 'Vacuna', 'required' => true, 'choices' => $arrVacuna, 'attr' => array('class' => 'form-control')))                                 
                ->add('obs', 'textarea', array('label' => 'Observación', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '90')))
                ->add('carnet', 'text', array('label' => 'Carnet de Identidad', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'placeholder' => '', 'pattern' => '[0-9]{5,10}', 'maxlength' => '11')))
                ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'attr' => array('class' => 'form-control jonlynumbersletters jupper', 'maxlength' => '2', 'autocomplete' => 'off')))
                ->add('paterno', 'text', array('label' => 'Apellido Paterno', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper')))
                ->add('materno', 'text', array('label' => 'Apellido Materno', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper')))
                ->add('nombre', 'text', array('label' => 'Nombre(s)', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper')))
                ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control')))
                ->add('genero', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:GeneroTipo', 'attr' => array('class' => 'form-control jupper')))
                ->add('celular', 'text', array('label' => 'Nro de Celular', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))
                ->add('correo', 'text', array('label' => 'Correo Electrónico', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))
                ->add('direccion', 'text', array('label' => 'Dirección de Domicilio', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

        return $form;
    }

    /*
     * registramos el nuevo maestro
     */

    public function createpersonaAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        
            $form = $request->get('form');

            
            $form['complemento'] = ($form['complemento'] != "") ? mb_strtoupper($form['complemento'], 'utf-8') : '';
            $parametros = array(
                'complemento'=>$form['complemento'],
                'primer_apellido'=>$form['paterno'],
                'segundo_apellido'=>$form['materno'],
                'nombre'=>$form['nombre'],
                'fecha_nacimiento'=>$form['fechaNacimiento']
            );
            $persona = $this->get('sie_app_web.segip')->buscarPersonaPorCarnet($form['carnet'], $parametros, 'prod', 'academico');

            if(isset($persona['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson']) && $persona['ConsultaDatoPersonaEnJsonResult']['DatosPersonaEnFormatoJson'] !== "null"){

                $this->get('session')->getFlashBag()->add('newError', 'VALIDACION SEGIP: Los datos NO fueron registrados ');
                return $this->redirect($this->generateUrl('personaladministrativoinscripcion_list'));                                

            }else{

                $entity = $em->getRepository('SieAppWebBundle:Persona');
                $query = $entity->createQueryBuilder('p');
                
                if($form['complemento']){
                    $query = $query->where('p.complemento = :complemento');
                    $query = $query->setParameter('complemento', $form['complemento']);
                } else{
                    $query = $query->where('p.complemento IS NULL');   
                    $query = $query->orwhere('p.complemento = :complemento');
                    $query = $query->setParameter('complemento', '');

                }
                $query = $query->andwhere('p.carnet = :carnet');
                $query = $query->setParameter('carnet', $form['carnet']);
                $query = $query->getQuery();                

                $resultPerson = $query->getResult();    
                if(sizeof($resultPerson)>0){
                    // no save
                    $this->get('session')->getFlashBag()->add('newError', 'DATOS EXISTEN EN SIGED: Los datos NO fueron registrados ');
                    return $this->redirect($this->generateUrl('personaladministrativoinscripcion_list'));                     
                }else{
                    // yes save
                    try {
                            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('persona');")->execute();
                            
                            //Nueva persona
                            $persona = new Persona();
                            $persona->setCarnet($form['carnet']);
                            $persona->setComplemento($form['complemento']);
                            $persona->setPaterno(mb_strtoupper($form['paterno'], 'utf-8'));
                            $persona->setMaterno(mb_strtoupper($form['materno'], 'utf-8'));
                            $persona->setNombre(mb_strtoupper($form['nombre'], 'utf-8'));
                            $persona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['genero']));
                            $persona->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
                            $persona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById(0));
                            $persona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById(0));
                            $persona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findOneById(0));
                            $persona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneById(0));
                            $persona->setRda('0');
                            $persona->setSegipId('0');
                            $persona->setDireccion(mb_strtoupper($form['direccion'], 'utf-8'));
                            $persona->setCorreo($form['correo']);
                            $persona->setCelular($form['celular']);
                            $persona->setActivo(1);
                            $persona->setEsvigente(1);
                            $em->persist($persona);
                            $em->flush();
                            
                            // Registro Persona inscripcion
                            $maestroinscripcion = new PersonaAdministrativoInscripcion();
                            $maestroinscripcion->setPersonaAdministrativoInscripcionTipo($em->getRepository('SieAppWebBundle:PersonaAdministrativoInscripcionTipo')->findOneById($form['tipo']));
                            $maestroinscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($form['gestion']));
                            $maestroinscripcion->setDistritoTipo($em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($form['distrito']));
                            $maestroinscripcion->setEsvacuna(($form['vacuna']));
                            $maestroinscripcion->setObs(mb_strtoupper($form['obs'], 'utf-8'));
                            $maestroinscripcion->setPersona($persona);
                            $maestroinscripcion->setEsactivo(1);
                            $em->persist($maestroinscripcion);
                            $em->flush();

                            $em->getConnection()->commit();
                            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente');
                            return $this->redirect($this->generateUrl('personaladministrativoinscripcion_list'));                
                       } catch (Exception $ex) {
                                $em->getConnection()->rollback();
                       }                      
                }
              
            }
     
    }


    public function findPersonaAction(Request $request) {

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->get('idInstitucion'));

        $maestroinscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findoneBy(array('institucioneducativa' => $request->get('idInstitucion'), 'gestionTipo' => $request->get('gestion')));

        return $this->render('SieRegularBundle:PersonalAdministrativoInscripcion:search_persona_director.html.twig', array(
                    'form' => $this->searchPersonaForm($institucion->getId(), $request->get('gestion'))->createView(),
                    'institucion' => $institucion,
                    'gestion' => $request->get('gestion'),
                    'maestroinscripcion' => $maestroinscripcion,
        ));
    }

    public function resultPersonaAction(Request $request) {

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        $persona = $this->get('sie_app_web.persona')->buscarPersonaPorCarnetComplemento($form);


        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['idInstitucion']);

        return $this->render('SieRegularBundle:PersonalAdministrativoInscripcion:result_persona.html.twig', array(
                    'persona' => $persona,
                    'institucion' => $institucion,
                    'gestion' => $form['gestion']
        ));
    }

    /**
     * Crea el formulario para buscar una persona por C.I.
     *
     */
    private function searchPersonaForm($idInstitucion, $gestion) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('personaladministrativoinscripcion_result_persona'))
                ->add('idInstitucion', 'hidden', array('data' => $idInstitucion))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('carnet', 'text', array('label' => 'Carnet de Identidad', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'pattern' => '[0-9]{5,10}', 'maxlength' => '11')))
                ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'attr' => array('class' => 'form-control jonlynumbersletters jupper', 'maxlength' => '2', 'autocomplete' => 'off')))
                ->add('buscar', 'submit', array('label' => 'Buscar coincidencias por C.I.', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        return $form;
    }

    /*
     * Llamamos al formulario para nuevo maestro
     */

    public function newdirectorAction(Request $request) {

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $request->get('form');

        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['idInstitucion']);
        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['idPersona']);

        return $this->render('SieRegularBundle:PersonalAdministrativoInscripcion:newdirector.html.twig', array(
                    'form' => $this->newdirectorForm($form['idInstitucion'], $form['gestion'], $form['idPersona'])->createView(),
                    'institucion' => $institucion,
                    'gestion' => $form['gestion'],
                    'persona' => $persona
        ));
    }

    /*
     * formulario de nuevo maestro
     */

    private function newdirectorForm($idInstitucion, $gestion, $idPersona) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $query = $em->createQuery('SELECT ct FROM SieAppWebBundle:CargoTipo ct WHERE ct.id IN (1,12) ORDER BY ct.id');

        $cargos = $query->getResult();
        $cargosArray = array();
        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getCargo();
        }

        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($idPersona);

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('personaladministrativoinscripcion_director_create'))
                ->add('idInstitucion', 'hidden', array('data' => $idInstitucion))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('idPersona', 'hidden', array('data' => $idPersona))
                ->add('tipo', 'choice', array('label' => 'Cargo', 'required' => true, 'choices' => $cargosArray, 'attr' => array('class' => 'form-control')))
                ->add('obs', 'text', array('label' => 'Observación', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '90')))
                ->add('celular', 'text', array('label' => 'Nro de Celular', 'required' => false, 'data' => $persona->getCelular(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))
                ->add('correo', 'text', array('label' => 'Correo Electrónico', 'required' => false, 'data' => $persona->getCorreo(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))
                ->add('direccion', 'text', array('label' => 'Dirección de Domicilio', 'required' => false, 'data' => $persona->getDireccion(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        $em->getConnection()->commit();
        return $form;
    }

    /*
     * registramos el nuevo maestro
     */

    public function createdirectorAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');

            // Verificar si la persona ya esta registrada            
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['idPersona']);

            // verificamos si el personal administrativo esta registrado ya en tabla maestro inscripcion
            $queryIns = $em->createQuery(
                            'SELECT mi FROM SieAppWebBundle:MaestroInscripcion mi
                    WHERE mi.institucioneducativa = :idInstitucion
                    AND mi.gestionTipo = :gestion
                    AND mi.persona = :pers')
                    ->setParameter('idInstitucion', $form['idInstitucion'])
                    ->setParameter('gestion', $form['gestion'])
                    ->setParameter('pers', $form['idPersona']);
            $maestroInscripcion = $queryIns->getResult();

            if ($maestroInscripcion) { // Si  el personalAdministrativo ya esta inscrito
                $this->get('session')->getFlashBag()->add('newError', 'No se realizó el registro, la persona ya esta registrada');
                return $this->redirect($this->generateUrl('bjp_rue'));
            }

            //$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('persona_administrativo_inscripcion');")->execute();
            //Actualizar datos persona
            $persona->setDireccion(mb_strtoupper($form['direccion'], 'utf-8'));
            $persona->setCorreo(mb_strtolower($form['correo'], 'utf-8'));
            $persona->setCelular($form['celular']);
            $em->persist($persona);
            $em->flush();

            // Registro Maestro inscripcion
            $maestroinscripcion = new MaestroInscripcion();
            $maestroinscripcion->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->findOneById($form['tipo']));
            $maestroinscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($form['gestion']));
            $maestroinscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['idInstitucion']));
            $maestroinscripcion->setPersona($persona);
            $maestroinscripcion->setEsVigenteAdministrativo(1);
            $maestroinscripcion->setFormacionTipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById(0));
            $maestroinscripcion->setRdaPlanillasId(0);
            $maestroinscripcion->setEspecialidadTipo($em->getRepository('SieAppWebBundle:EspecialidadMaestroTipo')->findOneById(0));
            $maestroinscripcion->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById(0));
            $maestroinscripcion->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneById(1));
            $maestroinscripcion->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array('institucioneducativa' => $form['idInstitucion'], 'gestionTipo' => $form['gestion'])));

            $em->persist($maestroinscripcion);
            $em->flush();

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente');
            return $this->redirect($this->generateUrl('bjp_rue'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /*
     * registramos el nuevo maestro
     */

    public function closeOperativeAction(Request $request) {
        

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        
        $idDepto =  $request->get('idDepto');
        $idDistrito =  $request->get('idDistrito');
        $gestion =  $request->get('gestion');

        try {


            $objDistritoControlOperativoMenus = $em->getRepository('SieAppWebBundle:DistritoControlOperativoMenus')->findOneBy(array('departamentoTipoId'=>$idDepto,'distritoTipo'=>$idDistrito,'gestionTipoId'=>$gestion));

            if(sizeof($objDistritoControlOperativoMenus) > 0){
                // do update
            }else{
                // do new insert
                $objDistritoControlOperativoMenus = new DistritoControlOperativoMenus();
                $objDistritoControlOperativoMenus->setDepartamentoTipoId($idDepto);
                $objDistritoControlOperativoMenus->setGestionTipoId($gestion);
                $objDistritoControlOperativoMenus->setEstadoMenu(0);
                $objDistritoControlOperativoMenus->setDistritoTipo($em->getRepository('SieAppWebBundle:DistritoTipo')->find($idDistrito));
                $objDistritoControlOperativoMenus->setNotaTipo($em->getRepository('SieAppWebBundle:notaTipo')->find(0));
                $em->persist($objDistritoControlOperativoMenus);
                $em->flush();
                $em->getConnection()->commit();

            }

            $this->get('session')->getFlashBag()->add('newOk', 'Cierre Exitoso');
            return $this->redirect($this->generateUrl('personaladministrativoinscripcion_list'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }


}
