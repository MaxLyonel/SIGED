<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\MaestroInscripcion;
use Sie\AppWebBundle\Entity\MaestroInscripcionIdioma;

/**
 * PersonalAdministrativoGestion controller.
 *
 */
class PersonalAdministrativoGestionController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    /**
     * Muestra el formulario de búsqueda.
     */
    public function indexAction(Request $request) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SieAppWebBundle:PersonalAdministrativoGestion:search.html.twig', array(
                    'form' => $this->formSearch($request->getSession()->get('currentyear'))->createView(),
        ));
    }

    /**
     * list of request
     *
     */
    public function listAction(Request $request) {
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

        ////////////////////////////////////////////////////
        $em = $this->getDoctrine()->getManager();

        if ($request->getMethod() == 'POST') {
            $form = $request->get('form');

            /*
             * verificamos si tiene tuicion
             */
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $form['institucioneducativa']);
            $query->bindValue(':rolId', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();
            if ($aTuicion[0]['get_ue_tuicion']) {
                $institucion = $form['institucioneducativa'];
                $gestion = $form['gestion'];
                $request->getSession()->set('idInstitucion', $institucion);
                $request->getSession()->set('idGestion', $gestion);
            } else {
                $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
                return $this->render('SieAppWebBundle:PersonalAdministrativoGestion:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }
            // creamos variables de sesion de la institucion educativa y gestion
        } else {
            $institucion = $request->getSession()->get('idInstitucion');
            $gestion = $request->getSession()->get('idGestion');
        }

        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        if (!$institucioneducativa) {
            $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
            return $this->render('SieAppWebBundle:PersonalAdministrativoGestion:index.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }

        /*
         * lista de personal registrados en la unidad educativa
         */
        $query = $em->createQuery(
                        'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                        WHERE ct.rolTipo IN (:id) ORDER BY ct.id')
                ->setParameter('id', array(5, 9));
        $cargosArray = $query->getResult();

        $query = $em->createQuery(
                        'SELECT mi, per, ft FROM SieAppWebBundle:MaestroInscripcion mi
                    INNER JOIN mi.persona per
                    INNER JOIN mi.formacionTipo ft
                    WHERE mi.institucioneducativa = :idInstitucion
                    AND mi.gestionTipo = :gestion
                    AND mi.cargoTipo IN (:cargos)
                    ORDER BY per.paterno, per.materno, per.nombre')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('cargos', $cargosArray);
        $personal = $query->getResult();

        $query = $em->createQuery(
                        'SELECT count(mi.id) FROM SieAppWebBundle:MaestroInscripcion mi
                    WHERE mi.institucioneducativa = :idInstitucion
                    AND mi.gestionTipo = :gestion
                    AND mi.cargoTipo IN (:cargos)')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('cargos', array(1, 12));
        $contador = $query->getResult();

        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);


        return $this->render('SieAppWebBundle:PersonalAdministrativoGestion:index.html.twig', array(
                    'personal' => $personal,
                    'institucion' => $institucion,
                    'gestion' => $gestion,
                    'contador' => $contador[0][1]
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
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->getSession()->get('idInstitucion'));
        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['idPersona']);

        return $this->render('SieAppWebBundle:PersonalAdministrativoGestion:new.html.twig', array(
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

        $query = $em->createQuery(
                        'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                        WHERE ct.rolTipo IN (:id) ORDER BY ct.id')
                ->setParameter('id', array(5, 9));

        $cargos = $query->getResult();
        $cargosArray = array();
        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getCargo();
        }

        $financiamiento = $em->createQuery(
                        'SELECT ft FROM SieAppWebBundle:FinanciamientoTipo ft
                WHERE ft.id NOT IN  (:id) ORDER BY ft.id')
                ->setParameter('id', array(0, 5))
                ->getResult();
        $financiamientoArray = array();
        foreach ($financiamiento as $f) {
            $financiamientoArray[$f->getId()] = $f->getFinanciamiento();
        }

        $formacion = $em->createQuery(
                        'SELECT ft FROM SieAppWebBundle:FormacionTipo ft
                WHERE ft.id NOT IN (:id) ORDER BY ft.id')
                ->setParameter('id', array(0, 22))
                ->getResult();
        $formacionArray = array();
        foreach ($formacion as $fr) {
            $formacionArray[$fr->getId()] = $fr->getFormacion();
        }

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('personaladministrativogestion_create'))
                ->add('institucionEducativa', 'hidden', array('data' => $idInstitucion))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('persona', 'hidden', array('data' => $idPersona))
                ->add('funcion', 'choice', array('label' => 'Función que desempeña (cargo)', 'required' => true, 'choices' => $cargosArray, 'attr' => array('class' => 'form-control')))
                ->add('financiamiento', 'choice', array('label' => 'Fuente de Financiamiento', 'required' => true, 'choices' => $financiamientoArray, 'attr' => array('class' => 'form-control')))
                ->add('formacion', 'choice', array('label' => 'Último grado de formación alcanzado', 'required' => true, 'choices' => $formacionArray, 'attr' => array('class' => 'form-control')))
                ->add('formacionDescripcion', 'text', array('label' => 'Descripción del último grado de formación alcanzado', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '90')))
                ->add('normalista', 'checkbox', array('required' => false, 'label' => 'Normalista', 'attr' => array('class' => 'checkbox')))
                ->add('item', 'text', array('label' => 'Número de Item', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'pattern' => '[0-9]{1,10}')))
                ->add('idiomaOriginario', 'entity', array('class' => 'SieAppWebBundle:IdiomaMaterno', 'data' => $em->getReference('SieAppWebBundle:IdiomaMaterno', 97), 'label' => 'Actualmente que idioma originario esta estudiando', 'required' => false, 'attr' => array('class' => 'form-control')))
                ->add('leeEscribeBraile', 'checkbox', array('required' => false, 'label' => 'Lee y Escribe en Braille', 'attr' => array('class' => 'checkbox')))
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

            $idiomaestudia = $em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById($form['idiomaOriginario']);

            // Verificar si la persona ya esta registrada            
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['persona']);
            //$personaId = $persona->getId();
            // verificamos si el personal administrativo esta registrado ya en tabla maestro inscripcion

            $query = $em->createQuery(
                            'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                        WHERE ct.rolTipo IN (:id) ORDER BY ct.id')
                    ->setParameter('id', array(5, 9));

            $cargos = $query->getResult();
            $cargosArray = array();
            foreach ($cargos as $c) {
                $cargosArray[$c->getId()] = $c->getCargo();
            }

            $queryIns = $em->createQuery(
                            'SELECT mi FROM SieAppWebBundle:MaestroInscripcion mi
                    WHERE mi.institucioneducativa = :idInstitucion
                    AND mi.gestionTipo = :gestion
                    AND mi.cargoTipo = :cargos
                    AND mi.persona = :pers')
                    ->setParameter('idInstitucion', $form['institucionEducativa'])
                    ->setParameter('gestion', $form['gestion'])
                    ->setParameter('cargos', $form['funcion'])
                    ->setParameter('pers', $persona);
            $maestroInscripcion = $queryIns->getResult();

            if ($maestroInscripcion) { // Si  el personalAdministrativo ya esta inscrito
                $this->get('session')->getFlashBag()->add('newError', 'No se realizó el registro, la persona ya esta registrada');
                return $this->redirect($this->generateUrl('personaladministrativogestion_list'));
            }

            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('maestro_inscripcion');")->execute();

            if ($form['funcion'] == 1 || $form['funcion'] == 12) {
                $maestroInscripcion_aux = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('cargoTipo' => $form['funcion'], 'gestionTipo' => $form['gestion'], 'institucioneducativa' => $form['institucionEducativa']));
                foreach ($maestroInscripcion_aux as $aux) {
                    $aux->setEsVigenteAdministrativo(0);
                    $em->persist($aux);
                    $em->flush();
                }
            }

            // Registro Maestro inscripcion
            $maestroinscripcion = new MaestroInscripcion();
            $maestroinscripcion->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->findOneById($form['funcion']));
            $maestroinscripcion->setEspecialidadTipo($em->getRepository('SieAppWebBundle:EspecialidadMaestroTipo')->findOneById(0));
            $maestroinscripcion->setEstadomaestro($em->getRepository('SieAppWebBundle:EstadomaestroTipo')->findOneById(1));
            $maestroinscripcion->setEstudiaiomaMaterno($idiomaestudia);
            $maestroinscripcion->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById($form['financiamiento']));
            $maestroinscripcion->setFormacionTipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById($form['formacion']));
            $maestroinscripcion->setFormaciondescripcion(mb_strtoupper($form['formacionDescripcion'], 'utf-8'));
            $maestroinscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($form['gestion']));
            $maestroinscripcion->setIdiomaMaternoId(null);
            $maestroinscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucionEducativa']));
            $maestroinscripcion->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById(0));
            $maestroinscripcion->setLeeescribebraile((isset($form['leeEscribeBraile'])) ? 1 : 0);
            $maestroinscripcion->setNormalista((isset($form['normalista'])) ? 1 : 0);
            $maestroinscripcion->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneById(1));
            $maestroinscripcion->setPersona($em->getRepository('SieAppWebBundle:Persona')->findOneById($persona));
            $maestroinscripcion->setRdaPlanillasId(0);
            $maestroinscripcion->setRef(0);
            $maestroinscripcion->setRolTipo($em->getRepository('SieAppWebBundle:RolTipo')->findOneById(5));
            $maestroinscripcion->setItem($form['item']);
            $maestroinscripcion->setFechaRegistro(new \DateTime('now'));
            $maestroinscripcion->setEsVigenteAdministrativo(1);
            $em->persist($maestroinscripcion);
            $em->flush();

            // Registro de idiomas que habla    
            for ($i = 1; $i < 10; $i++) {
                $idioma = $request->get('idioma' . $i);
                $lee = $request->get('lee' . $i);
                $habla = $request->get('habla' . $i);
                $escribe = $request->get('escribe' . $i);

                if ($idioma) {
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('maestro_inscripcion_idioma');")->execute();
                    $maestroIdioma = new MaestroInscripcionIdioma();
                    $maestroIdioma->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById($idioma));
                    $maestroIdioma->setIdiomaconoceTipoLee($em->getRepository('SieAppWebBundle:IdiomaconoceTipo')->findOneById($lee));
                    $maestroIdioma->setIdiomaconoceTipoHabla($em->getRepository('SieAppWebBundle:IdiomaconoceTipo')->findOneById($habla));
                    $maestroIdioma->setIdiomaconoceTipoEscribe($em->getRepository('SieAppWebBundle:IdiomaconoceTipo')->findOneById($escribe));
                    $maestroIdioma->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($maestroinscripcion->getId()));
                    $em->persist($maestroIdioma);
                    $em->flush();
                }
            }

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente');
            return $this->redirect($this->generateUrl('personaladministrativogestion_list'));
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
        return $this->render('SieAppWebBundle:PersonalAdministrativoGestion:open.html.twig', array());
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
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idInstitucion'));
            $maestroInscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array('id' => $request->get('idMaestroInscripcion'), 'gestionTipo' => $gestion, 'institucioneducativa' => $institucion));


            if ($request->get('idCargo') == 1 || $request->get('idCargo') == 12) {
                $maestroInscripcion_aux = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('cargoTipo' => $request->get('idCargo'), 'gestionTipo' => $gestion, 'institucioneducativa' => $institucion));
                foreach ($maestroInscripcion_aux as $aux) {
                    $aux->setEsVigenteAdministrativo(0);
                }
            }

            $cargo = $em->getRepository('SieAppWebBundle:CargoTipo')->findOneById($request->get('idCargo'));
            $maestroInscripcion->setCargotipo($cargo);
            $maestroInscripcion->setEsVigenteAdministrativo(1 - $maestroInscripcion->getEsVigenteAdministrativo());

            $em->persist($maestroInscripcion);
            $em->flush();

            return $this->redirect($this->generateUrl('personaladministrativogestion_list'));
        } catch (Exception $ex) {
            
        }
    }

    /*
     * Llamar al formulario de edicion
     */

    public function editAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($request->get('idPersona'));
        $maestroInscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($request->get('idMaestroInscripcion'));
        $idiomas = $em->getRepository('SieAppWebBundle:MaestroInscripcionIdioma')->findBy(array('maestroInscripcion' => $request->get('idMaestroInscripcion')));
        $em->getConnection()->commit();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->getSession()->get('idInstitucion'));

        return $this->render('SieAppWebBundle:PersonalAdministrativoGestion:edit.html.twig', array(
                    'form' => $this->editForm($request->getSession()->get('idInstitucion'), $request->getSession()->get('idGestion'), $persona, $maestroInscripcion, $idiomas)->createView(),
                    'institucion' => $institucion,
                    'gestion' => $request->getSession()->get('idGestion'),
                    'persona' => $persona
        ));
    }

    /*
     * formulario de edicion
     */

    private function editForm($idInstitucion, $gestion, $persona, $maestroInscripcion, $idiomas) {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
                        'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                        WHERE ct.rolTipo IN (:id) ORDER BY ct.id')
                ->setParameter('id', array(5, 9));

        $cargos = $query->getResult();
        $cargosArray = array();
        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getCargo();
        }

        $financiamiento = $em->createQuery(
                        'SELECT ft FROM SieAppWebBundle:FinanciamientoTipo ft
                WHERE ft.id NOT IN  (:id) ORDER BY ft.id')
                ->setParameter('id', array(0, 5))
                ->getResult();
        $financiamientoArray = array();
        foreach ($financiamiento as $f) {
            $financiamientoArray[$f->getId()] = $f->getFinanciamiento();
        }

        $formacion = $em->createQuery(
                        'SELECT ft FROM SieAppWebBundle:FormacionTipo ft
                WHERE ft.id NOT IN (:id) ORDER BY ft.id')
                ->setParameter('id', array(0, 22))
                ->getResult();
        $formacionArray = array();
        foreach ($formacion as $fr) {
            $formacionArray[$fr->getId()] = $fr->getFormacion();
        }

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('personaladministrativogestion_update'))
                ->add('institucionEducativa', 'hidden', array('data' => $idInstitucion))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('idPersona', 'hidden', array('data' => $persona->getId()))
                ->add('idMaestroInscripcion', 'hidden', array('data' => $maestroInscripcion->getId()))
                ->add('funcion', 'choice', array('label' => 'Función que desempeña', 'required' => true, 'choices' => $cargosArray, 'data' => $maestroInscripcion->getCargoTipo()->getId(), 'attr' => array('class' => 'form-control')))
                ->add('financiamiento', 'choice', array('label' => 'Fuente de Financiamiento', 'required' => true, 'choices' => $financiamientoArray, 'data' => $maestroInscripcion->getFinanciamientoTipo()->getId(), 'attr' => array('class' => 'form-control')))
                ->add('formacion', 'choice', array('label' => 'Último grado de formación alcanzado', 'required' => true, 'choices' => $formacionArray, 'data' => $maestroInscripcion->getFormacionTipo()->getId(), 'attr' => array('class' => 'form-control')))
                ->add('formacionDescripcion', 'text', array('label' => 'Descripción del último grado de formación alcanzado', 'required' => false, 'data' => $maestroInscripcion->getFormaciondescripcion(), 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '90')))
                ->add('normalista', 'checkbox', array('required' => false, 'label' => 'Normalista', 'attr' => array('class' => 'form-control', 'checked' => $maestroInscripcion->getNormalista())))
                ->add('item', 'text', array('label' => 'Número de Item', 'required' => true, 'data' => $maestroInscripcion->getItem(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'pattern' => '[0-9]{1,10}')))
                ->add('idiomaOriginario', 'entity', array('class' => 'SieAppWebBundle:IdiomaMaterno', 'data' => ($maestroInscripcion->getEstudiaiomaMaterno()), 'label' => 'Actualmente que idioma originario esta estudiando', 'required' => false, 'attr' => array('class' => 'form-control')))
                ->add('leeEscribeBraile', 'checkbox', array('required' => false, 'label' => 'Lee y Escribe en Braille', 'attr' => array('class' => 'form-control', 'checked' => $maestroInscripcion->getLeeescribebraile())))
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

            if ($form['funcion'] == 1 || $form['funcion'] == 12) {
                $maestroInscripcion_aux = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('cargoTipo' => $form['funcion'], 'gestionTipo' => $form['gestion'], 'institucioneducativa' => $form['institucionEducativa']));
                foreach ($maestroInscripcion_aux as $aux) {
                    $aux->setEsVigenteAdministrativo(0);
                    $em->persist($aux);
                    $em->flush();
                }
            }

            $idiomaestudia = $em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById($form['idiomaOriginario']);
            //Actualizacion en la tabla maestro inscripcion
            $maestroinscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($form['idMaestroInscripcion']);

            $maestroinscripcion->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->findOneById($form['funcion']));
            $maestroinscripcion->setEstudiaiomaMaterno($idiomaestudia);
            $maestroinscripcion->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById($form['financiamiento']));
            $maestroinscripcion->setFormacionTipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById($form['formacion']));
            $maestroinscripcion->setFormaciondescripcion(mb_strtoupper($form['formacionDescripcion'], 'utf-8'));
            $maestroinscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucionEducativa']));
            $maestroinscripcion->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById(0));
            $maestroinscripcion->setLeeescribebraile((isset($form['leeEscribeBraile'])) ? 1 : 0);
            $maestroinscripcion->setNormalista((isset($form['normalista'])) ? 1 : 0);
            $maestroinscripcion->setItem($form['item']);
            $maestroinscripcion->setFechaModificacion(new \DateTime('now'));
            $maestroinscripcion->setEsVigenteAdministrativo(1);
            $em->persist($maestroinscripcion);
            $em->flush();


            //Actualizacion de idiomas
            //1ro eliminacion de los anteriores registros
            $idiomas = $em->getRepository('SieAppWebBundle:MaestroInscripcionIdioma')->findBy(array('maestroInscripcion' => $form['idMaestroInscripcion']));
            foreach ($idiomas as $idioma) {
                $maestroIdioma = $em->getRepository('SieAppWebBundle:MaestroInscripcionIdioma')->findOneById($idioma->getId());
                $em->remove($maestroIdioma);
                $em->flush();
            }
            // 2do registro nuevos idiomas
            for ($i = 1; $i < 10; $i++) {
                $idioma = $request->get('idioma' . $i);
                $lee = $request->get('lee' . $i);
                $habla = $request->get('habla' . $i);
                $escribe = $request->get('escribe' . $i);
                if ($idioma) {
                    $maestroIdioma = new MaestroInscripcionIdioma();
                    $maestroIdioma->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById($idioma));
                    $maestroIdioma->setIdiomaconoceTipoLee($em->getRepository('SieAppWebBundle:IdiomaconoceTipo')->findOneById($lee));
                    $maestroIdioma->setIdiomaconoceTipoHabla($em->getRepository('SieAppWebBundle:IdiomaconoceTipo')->findOneById($habla));
                    $maestroIdioma->setIdiomaconoceTipoEscribe($em->getRepository('SieAppWebBundle:IdiomaconoceTipo')->findOneById($escribe));
                    $maestroIdioma->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($form['idMaestroInscripcion']));
                    $em->persist($maestroIdioma);
                    $em->flush();
                }
            }
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('updateOk', 'Datos modificados correctamente');
            return $this->redirect($this->generateUrl('personaladministrativogestion_list'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'Error en la modificacion de datos');
            return $this->redirect($this->generateUrl('personaladministrativogestion_list'));
        }
    }

    /*
     * Eliminacion de maestro
     */

    public function deleteAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $maestroInscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($request->get('idMaestroInscripcion'));

            if ($maestroInscripcion) { // Sie existe el maestro inscrito
                // Verificamos si el maestro tine asignados algunas areas o asignaturas
                $institucionCursoOfertaMaestro = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('maestroInscripcion' => $maestroInscripcion->getId()));

                if ($institucionCursoOfertaMaestro) {

                    $this->get('session')->getFlashBag()->add('eliminarError', 'El registro no se pudo eliminar, el maestro tiene areas asignadas.');
                    return $this->redirect($this->generateUrl('personaladministrativogestion_list'));
                }
                //eliminar idiomas del maestro
                $idiomas = $em->getRepository('SieAppWebBundle:MaestroInscripcionIdioma')->findBy(array('maestroInscripcion' => $maestroInscripcion->getId()));

                if ($idiomas) {
                    foreach ($idiomas as $id) {
                        $em->remove($em->getRepository('SieAppWebBundle:MaestroInscripcionIdioma')->findOneById($id->getId()));
                        $em->flush();
                    }
                }

                //eliminamos el registro de inscripcion del maestro
                $em->remove($maestroInscripcion);
                $em->flush();
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('eliminarOk', 'El registro fue eliminado exitosamente');

                return $this->redirect($this->generateUrl('personaladministrativogestion_list'));
            }
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('eliminarError', 'El registro no se pudo eliminar');
            return $this->redirect($this->generateUrl('personaladministrativogestion_list'));
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
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->getSession()->get('idInstitucion'));

        return $this->render('SieAppWebBundle:PersonalAdministrativoGestion:search_persona.html.twig', array(
                    'form' => $this->searchForm($request->getSession()->get('idInstitucion'), $request->getSession()->get('idGestion'))->createView(),
                    'institucion' => $institucion,
                    'gestion' => $request->getSession()->get('idGestion')
        ));
    }

    public function resultAction(Request $request) {

        // Verificamos si no ha caducado la sesión
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $request->get('form');

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:Persona');

        $query = $repository->createQueryBuilder('per')
                ->select('per')
                //->innerJoin('SieAppWebBundle:ApoderadoInscripcion', 'apoi', 'WITH', 'apoi.persona = per.id')
                ->where('per.carnet = :carnet')
                ->andWhere('per.esvigente = :vigente OR per.esvigenteApoderado > :apoderado')
                ->setParameter('carnet', $form['carnetIdentidad'])
                ->setParameter('vigente', 't')
                ->setParameter('apoderado', 0)
                ->getQuery();

        $personas = $query->getResult();

        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->getSession()->get('idInstitucion'));

        return $this->render('SieAppWebBundle:PersonalAdministrativoGestion:result.html.twig', array(
                    'personas' => $personas,
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
                ->setAction($this->generateUrl('personaladministrativogestion_result'))
                ->add('carnetIdentidad', 'text', array('label' => 'Carnet de Identidad', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'pattern' => '[0-9]{5,10}', 'maxlength' => '11')))
                ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'attr' => array('class' => 'form-control jonlynumbersletters jupper', 'maxlength' => '2', 'autocomplete' => 'off')))
                ->add('buscar', 'submit', array('label' => 'Buscar coincidencias por C.I.', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        return $form;
    }

    /*
     * formularios de busqueda de institucion educativa
     */

    private function formSearch($gestionactual) {
        
        $gestiones = array($gestionactual - 1 => $gestionactual - 1, $gestionactual - 2 => $gestionactual - 2);
        if($this->session->get('roluser') == 8 || $this->session->get('roluser') == 7){
            $gestiones = array($gestionactual => $gestionactual, $gestionactual - 1 => $gestionactual - 1, $gestionactual - 2 => $gestionactual - 2, $gestionactual - 3 => $gestionactual - 3, $gestionactual - 4 => $gestionactual - 4, $gestionactual - 5 => $gestionactual - 5);
        }

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('personaladministrativogestion_list'))
                ->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

}
