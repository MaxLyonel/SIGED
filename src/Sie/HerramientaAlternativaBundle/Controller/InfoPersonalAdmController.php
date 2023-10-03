<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\MaestroInscripcion;
use Sie\AppWebBundle\Entity\MaestroInscripcionIdioma;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Form\VerificarPersonaSegipType;
use Sie\AppWebBundle\Form\PersonaDatosType;

/**
 * EstudianteInscripcion controller.
 *
 */
class InfoPersonalAdmController extends Controller {

    public $session;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    /**
     * list of request
     *
     */
    public function indexAction(Request $request) {
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

        $institucion = $this->session->get('ie_id');
        $gestion = $this->session->get('ie_gestion');
        $sucursal = $this->session->get('ie_suc_id');
        $periodo = $this->session->get('ie_per_cod');

        /*
         * lista de maestros registrados en la unidad educativa
         */

        $query = $em->createQuery(
                        'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                        WHERE ct.id NOT IN (:id) ORDER BY ct.id')
                ->setParameter('id', array(0, 70));
        $cargosArray = $query->getResult();

        $query = $em->createQuery(
                        'SELECT mi, per, ft FROM SieAppWebBundle:MaestroInscripcion mi
                    INNER JOIN mi.persona per
                    INNER JOIN mi.formacionTipo ft
                    WHERE mi.institucioneducativa = :idInstitucion
                    AND mi.gestionTipo = :gestion
                    AND mi.cargoTipo IN (:cargos)
                    AND mi.periodoTipo = :periodo
                    AND mi.institucioneducativaSucursal = :sucursal
                    ORDER BY per.paterno, per.materno, per.nombre')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('cargos', $cargosArray)
                ->setParameter('periodo', $periodo)
                ->setParameter('sucursal', $sucursal);
        $personal = $query->getResult();

        $query = $em->createQuery(
                        'SELECT count(mi.id) FROM SieAppWebBundle:MaestroInscripcion mi
                    WHERE mi.institucioneducativa = :idInstitucion
                    AND mi.gestionTipo = :gestion
                    AND mi.cargoTipo IN (:cargos)
                    AND mi.periodoTipo = :periodo
                    AND mi.institucioneducativaSucursal = :sucursal')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('cargos', array(1, 12))
                ->setParameter('periodo', $periodo)
                ->setParameter('sucursal', $sucursal);
        $contador = $query->getResult();

        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);

        return $this->render($this->session->get('pathSystem') . ':InfoPersonalAdm:index.html.twig', array(
                    'personal' => $personal,
                    'institucion' => $institucion,
                    'gestion' => $gestion,
                    'contador' => $contador[0][1]
        ));
    }

    /*
     * Llamamos al formulario para nuevo maestro
     */



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
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id'));
        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['idPersona']);

        return $this->render($this->session->get('pathSystem') . ':InfoPersonalAdm:new.html.twig', array(
                    'form' => $this->newForm($form['idInstitucion'], $form['gestion'], $form['idPersona'])->createView(),
                    'institucion' => $institucion,
                    'gestion' => $this->session->get('ie_gestion'),
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
                        WHERE ct.rolTipo IN (:id) ORDER BY ct.cargo')
                ->setParameter('id', array(5, 9));

        $cargos = $query->getResult();
        $cargosArray = array();
        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getCargo();
        }

        $financiamiento = $em->createQuery(
                        "SELECT ft FROM SieAppWebBundle:FinanciamientoTipo ft
                WHERE ft.id NOT IN (:id) AND ft.financiamiento<>'TGN OTROS' ORDER BY ft.id")
                ->setParameter('id', array(0, 5))
                ->getResult();
        $financiamientoArray = array();
        foreach ($financiamiento as $f) {
            $financiamientoArray[$f->getId()] = $f->getFinanciamiento();
        }

        $formacion = $em->createQuery(
                        'SELECT ft FROM SieAppWebBundle:FormacionTipo ft
                WHERE ft.id NOT IN (:id) ORDER BY ft.formacion')
                ->setParameter('id', array(0, 22))
                ->getResult();
        $formacionArray = array();
        foreach ($formacion as $fr) {
            $formacionArray[$fr->getId()] = $fr->getFormacion();
        }

        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($idPersona);

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramientalt_info_personal_adm_create'))
                ->add('institucionEducativa', 'hidden', array('data' => $idInstitucion))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('persona', 'hidden', array('data' => $idPersona))
                ->add('genero', 'entity', array('class' => 'SieAppWebBundle:GeneroTipo', 'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $persona->getGeneroTipo()->getId()), 'label' => 'Género', 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('celular', 'text', array('label' => 'Nro. de Celular', 'data' => $persona->getCelular(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))
                ->add('correo', 'text', array('label' => 'Correo Electrónico', 'data' => $persona->getCorreo(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))
                ->add('direccion', 'text', array('label' => 'Dirección de Domicilio', 'data' => $persona->getDireccion(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('funcion', 'choice', array('label' => 'Función que desempeña (cargo)', 'required' => true, 'choices' => $cargosArray, 'attr' => array('class' => 'chosen-select form-control', 'data-placeholder' => 'Seleccionar...')))
                ->add('financiamiento', 'choice', array('label' => 'Fuente de Financiamiento', 'required' => true, 'choices' => $financiamientoArray, 'attr' => array('class' => 'form-control')))
                ->add('formacion', 'choice', array('label' => 'Último grado de formación alcanzado', 'required' => true, 'choices' => $formacionArray, 'attr' => array('class' => 'chosen-select form-control', 'data-placeholder' => 'Seleccionar...')))
                ->add('formacionDescripcion', 'text', array('label' => 'Descripción del último grado de formación alcanzado', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '90')))
                ->add('normalista', 'checkbox', array('required' => false, 'label' => 'Normalista', 'attr' => array('class' => 'checkbox')))
                ->add('item', 'text', array('label' => 'Número de Item', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'pattern' => '[0-9]{1,10}')))
                ->add('idiomaOriginario', 'entity', array('class' => 'SieAppWebBundle:IdiomaMaterno', 'data' => $em->getReference('SieAppWebBundle:IdiomaMaterno', 97), 'label' => 'Actualmente que idioma originario esta estudiando', 'required' => false, 'attr' => array('class' => 'chosen-select form-control', 'data-placeholder' => 'Seleccionar...', 'data-placeholder' => 'Seleccionar...')))
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
            // Verificar si la persona ya esta registrada

            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['persona']);

            $personaId = $persona->getId();

            $institucion = $this->session->get('ie_id');
            $gestion = $this->session->get('ie_gestion');
            $sucursal = $this->session->get('ie_suc_id');
            $periodo = $this->session->get('ie_per_cod');

            $queryIns = $em->createQuery(
                            'SELECT mi FROM SieAppWebBundle:MaestroInscripcion mi
                    WHERE mi.institucioneducativa = :idInstitucion
                    AND mi.gestionTipo = :gestion
                    AND mi.cargoTipo = :cargos
                    AND mi.persona = :pers
                    AND mi.periodoTipo = :periodo
                    AND mi.institucioneducativaSucursal = :sucursal')
                    ->setParameter('idInstitucion', $institucion)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('cargos', $form['funcion'])
                    ->setParameter('pers', $persona)
                    ->setParameter('periodo', $periodo)
                    ->setParameter('sucursal', $sucursal);

            $maestroInscripcion = $queryIns->getResult();

            if ($maestroInscripcion) { // Si  el personalAdministrativo ya esta inscrito
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('newError', 'No se realizó el registro, la persona ya esta registrada.');
                return $this->redirect($this->generateUrl('herramientalt_info_personal_adm_index'));
            }

            //Actualizamos datos de la persona
            $persona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['genero']));
            $persona->setDireccion(mb_strtoupper($form['direccion']), 'utf-8');
            $persona->setCelular($form['celular']);
            $persona->setCorreo(mb_strtolower($form['correo']), 'utf-8');
            $em->persist($persona);
            $em->flush();

            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('maestro_inscripcion');")->execute();

            // Registro Maestro inscripcion
            $maestroinscripcion = new MaestroInscripcion();
            $maestroinscripcion->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->findOneById($form['funcion']));
            $maestroinscripcion->setEspecialidadTipo($em->getRepository('SieAppWebBundle:EspecialidadMaestroTipo')->findOneById(0));
            $maestroinscripcion->setEstadomaestro($em->getRepository('SieAppWebBundle:EstadomaestroTipo')->findOneById(1));
            $maestroinscripcion->setEstudiaiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById($form['idiomaOriginario']));
            $maestroinscripcion->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById($form['financiamiento']));
            $maestroinscripcion->setFormacionTipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById($form['formacion']));
            $maestroinscripcion->setFormaciondescripcion(mb_strtoupper($form['formacionDescripcion'], 'utf-8'));
            $maestroinscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($form['gestion']));
            $maestroinscripcion->setIdiomaMaternoId(null);
            $maestroinscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucionEducativa']));
            $maestroinscripcion->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($this->session->get('ie_suc_id')));
            $maestroinscripcion->setLeeescribebraile((isset($form['leeEscribeBraile'])) ? 1 : 0);
            $maestroinscripcion->setNormalista((isset($form['normalista'])) ? 1 : 0);
            $maestroinscripcion->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneById($this->session->get('ie_per_cod')));
            $maestroinscripcion->setPersona($em->getRepository('SieAppWebBundle:Persona')->findOneById($personaId));
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
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente.');
            return $this->redirect($this->generateUrl('herramientalt_info_personal_adm_index'));
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
        return $this->render($this->session->get('pathSystem') . ':InfoPersonalAdm:open.html.twig', array());
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
            $periodo = $this->session->get('ie_per_cod');
            $maestroInscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array('id' => $request->get('idMaestroInscripcion'), 'gestionTipo' => $gestion, 'institucioneducativa' => $institucion, 'periodoTipo' => $periodo));
       
            $maestroInscripcion->setEsVigenteAdministrativo(1 - $maestroInscripcion->getEsVigenteAdministrativo());

            $em->persist($maestroInscripcion);
            $em->flush();

            return $this->redirect($this->generateUrl('herramientalt_info_personal_adm_index'));
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
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id'));

        return $this->render($this->session->get('pathSystem') . ':InfoPersonalAdm:edit.html.twig', array(
                    'form' => $this->editForm($this->session->get('ie_id'), $this->session->get('ie_gestion'), $persona, $maestroInscripcion, $idiomas)->createView(),
                    'institucion' => $institucion,
                    'gestion' => $this->session->get('ie_gestion'),
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
                        WHERE ct.rolTipo IN (:id) ORDER BY ct.cargo')
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
                WHERE ft.id NOT IN (:id) ORDER BY ft.formacion')
                ->setParameter('id', array(0, 22))
                ->getResult();
        $formacionArray = array();
        foreach ($formacion as $fr) {
            $formacionArray[$fr->getId()] = $fr->getFormacion();
        }

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramientalt_info_personal_adm_update'))
                ->add('institucionEducativa', 'hidden', array('data' => $idInstitucion))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('idPersona', 'hidden', array('data' => $persona->getId()))
                ->add('idMaestroInscripcion', 'hidden', array('data' => $maestroInscripcion->getId()))
                ->add('genero', 'entity', array('class' => 'SieAppWebBundle:GeneroTipo', 'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $persona->getGeneroTipo()->getId()), 'label' => 'Género', 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('celular', 'text', array('label' => 'Nro. de Celular', 'required' => true, 'data' => $persona->getCelular(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))
                ->add('correo', 'text', array('label' => 'Correo Electrónico', 'required' => true, 'data' => $persona->getCorreo(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))
                ->add('direccion', 'text', array('label' => 'Dirección de Domicilio', 'required' => true, 'data' => $persona->getDireccion(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('funcion', 'choice', array('label' => 'Función que desempeña', 'required' => true, 'choices' => $cargosArray, 'data' => $maestroInscripcion->getCargoTipo()->getId(), 'attr' => array('class' => 'chosen-select form-control', 'data-placeholder' => 'Seleccionar...')))
                ->add('financiamiento', 'choice', array('label' => 'Fuente de Financiamiento', 'required' => true, 'choices' => $financiamientoArray, 'data' => $maestroInscripcion->getFinanciamientoTipo()->getId(), 'attr' => array('class' => 'form-control')))
                ->add('formacion', 'choice', array('label' => 'Último grado de formación alcanzado', 'required' => true, 'choices' => $formacionArray, 'data' => $maestroInscripcion->getFormacionTipo()->getId(), 'attr' => array('class' => 'chosen-select form-control', 'data-placeholder' => 'Seleccionar...')))
                ->add('formacionDescripcion', 'text', array('label' => 'Descripción del último grado de formación alcanzado', 'required' => false, 'data' => $maestroInscripcion->getFormaciondescripcion(), 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '90')))
                ->add('normalista', 'checkbox', array('required' => false, 'label' => 'Normalista', 'attr' => array('class' => 'form-control', 'checked' => $maestroInscripcion->getNormalista())))
                ->add('item', 'text', array('label' => 'Número de Item', 'required' => true, 'data' => $maestroInscripcion->getItem(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'pattern' => '[0-9]{1,10}')))
                ->add('idiomaOriginario', 'entity', array('class' => 'SieAppWebBundle:IdiomaMaterno', 'data' => ($maestroInscripcion->getEstudiaiomaMaterno()), 'label' => 'Actualmente que idioma originario esta estudiando', 'required' => false, 'attr' => array('class' => 'chosen-select form-control', 'data-placeholder' => 'Seleccionar...', 'data-placeholder' => 'Seleccionar...')))
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

            if ($form['idiomaOriginario']) {
                $estudiaioma = $form['idiomaOriginario'];
            } else {
                $estudiaioma = 0;
            }

            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['idPersona']);
            $persona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['genero']));
            $persona->setDireccion(mb_strtoupper($form['direccion']), 'utf-8');
            $persona->setCelular($form['celular']);
            $persona->setCorreo(mb_strtolower($form['correo']), 'utf-8');
            $em->persist($persona);
            $em->flush();
            //Actualizacion en la tabla maestro inscripcion
            $maestroinscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($form['idMaestroInscripcion']);

            $maestroinscripcion->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->findOneById($form['funcion']));
            $maestroinscripcion->setEstudiaiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById($estudiaioma));
            $maestroinscripcion->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById($form['financiamiento']));
            $maestroinscripcion->setFormacionTipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById($form['formacion']));
            $maestroinscripcion->setFormaciondescripcion(mb_strtoupper($form['formacionDescripcion'], 'utf-8'));
            $maestroinscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucionEducativa']));
            $maestroinscripcion->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($this->session->get('ie_suc_id')));
            $maestroinscripcion->setLeeescribebraile((isset($form['leeEscribeBraile'])) ? 1 : 0);
            $maestroinscripcion->setNormalista((isset($form['normalista'])) ? 1 : 0);
            $maestroinscripcion->setItem($form['item']);
            $maestroinscripcion->setFechaModificacion(new \DateTime('now'));
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
            $this->get('session')->getFlashBag()->add('updateOk', 'Los datos fueron modificados correctamente.');
            return $this->redirect($this->generateUrl('herramientalt_info_personal_adm_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'Error en la modificación de datos.');
            return $this->redirect($this->generateUrl('herramientalt_info_personal_adm_index'));
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
                //dump($institucionCursoOfertaMaestro);die;
                if ($institucionCursoOfertaMaestro) {

                    $this->get('session')->getFlashBag()->add('eliminarError', 'El registro no se pudo eliminar, el maestro tiene areas asignadas.');
                    return $this->redirect($this->generateUrl('herramientalt_info_personal_adm_index', array('op' => 'result')));
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
                $this->get('session')->getFlashBag()->add('eliminarOk', 'El registro fue eliminado exitosamente.');

                return $this->redirect($this->generateUrl('herramientalt_info_personal_adm_index'));
            }
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('eliminarError', 'El registro no se pudo eliminar.');
            return $this->redirect($this->generateUrl('herramientalt_info_personal_adm_index'));
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
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id'));

        return $this->render($this->session->get('pathSystem') . ':InfoPersonalAdm:search.html.twig', array(
                    'form' => $this->searchForm($this->session->get('ie_id'), $this->session->get('ie_gestion'))->createView(),
                    'institucion' => $institucion,
                    'gestion' => $this->session->get('ie_gestion')
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
        
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id'));
        $data = [
            'carnet' => $form['carnet'],
            'complemento' => $form['complemento'],
        ];

        $formVerificarPersona = $this->createForm(new VerificarPersonaSegipType(), 
            null,
            array(
                'action' => $this->generateUrl('herramientalt_info_personal_adm_segip_verificar_persona'),
                'method' => 'POST'
            )
        );

        return $this->render($this->session->get('pathSystem') . ':InfoPersonalAdm:result.html.twig', array(
            'data' => $data,        
            'persona' => $persona,
            'institucion' => $institucion,
            'gestion' => $this->session->get('ie_gestion'),
            'form_verificar' => $formVerificarPersona->createView()
        ));
    }

    public function verificarPersonaAction(Request $request){
        $form = $request->get('sie_verificar_persona_segip');

        $data = [
            'carnet' => $form['carnet'],
            'complemento' => $form['complemento'],
            'primer_apellido' => $form['primer_apellido'],
            'segundo_apellido' => $form['segundo_apellido'],
            'nombre' => $form['nombre'],
            'fecha_nacimiento' => $form['fecha_nacimiento']
        ];
        if($request->get('nacionalidad') == 'EX'){
            $data['extranjero'] = 'E';
        }
        $resultado = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($form['carnet'], $data, $form['entorno'], 'academico');
        $persona = array();

        if($resultado) {
            $persona = [
                'carnet' => $form['carnet'],
                'complemento' => $form['complemento'] ? $form['complemento'] : '0',
                'primer_apellido' => $form['primer_apellido'],
                'segundo_apellido' => $form['segundo_apellido'],
                'nombre' => $form['nombre'],
                'fecha_nacimiento' => $form['fecha_nacimiento'],
                'nacionalidad' => $request->get('nacionalidad')
            ];
        }

        $formPersonaDatos = $this->createForm(new PersonaDatosType(), 
            null,
            array(
                'action' => $this->generateUrl('herramientalt_info_personal_adm_persona_datos'),
                'method' => 'POST'
            )
        );

        return $this->render($this->session->get('pathSystem') . ':InfoPersonalAdm:formulario_persona_new.html.twig',array(
            'form_datos' => $formPersonaDatos->createView(),
            'resultado'=>$resultado,
            'persona' => serialize($persona),
            'institucion' => $form['institucion'],
            'gestion' => $form['gestion'],
        ));
    }
    public function registrarPersonaAction(Request $request)
    {
        //NO PERMITIR REGISTRO DE PERSONAS
        //return $this->redirect($this->generateUrl('login'));
        
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('sie_persona_datos');
        $persona = unserialize($form['persona']);
        $persona_validada = null;
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucion']);
        $gestion = $form['gestion'];

        $fecha = str_replace('-','/',$persona['fecha_nacimiento']);
        $complemento = $persona['complemento'] == '0'? '':$persona['complemento'];
        $arrayDatosPersona = array(
            //'carnet'=>$form['carnet'],
            'complemento'=>$complemento,
            'paterno'=>$persona['primer_apellido'],
            'materno'=>$persona['segundo_apellido'],
            'nombre'=>$persona['nombre'],
            'fecha_nacimiento' => $fecha
        );

        $personaValida = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($persona['carnet'], $arrayDatosPersona, 'prod', 'academico');

        if( $personaValida )
        {
            $arrayDatosPersona['carnet']=$persona['carnet'];
            unset($arrayDatosPersona['fecha_nacimiento']);
            $arrayDatosPersona['fechaNacimiento']=$persona['fecha_nacimiento'];
            if($persona['nacionalidad'] == 'EX'){
                $arrayDatosPersona['extranjero'] = 'E';
            }            
            $personaEncontrada = $this->get('buscarpersonautils')->buscarPersonav2($arrayDatosPersona,$conCI=true, $segipId=1);

            if($personaEncontrada == null)
            {
                $newPersona = new Persona();
                $newPersona->setCarnet($persona['carnet']);
                $newPersona->setComplemento(mb_strtoupper($complemento, 'utf-8'));
                $newPersona->setPaterno(mb_strtoupper($persona['primer_apellido'], 'utf-8'));
                $newPersona->setMaterno(mb_strtoupper($persona['segundo_apellido'], 'utf-8'));
                $newPersona->setNombre(mb_strtoupper($persona['nombre'], 'utf-8'));
                $newPersona->setFechaNacimiento(new \DateTime($persona['fecha_nacimiento']));
                $newPersona->setCelular($form['celular']);
                $newPersona->setCorreo(mb_strtolower($form['correo']), 'utf-8');
                $newPersona->setDireccion(mb_strtoupper($form['direccion']), 'utf-8');
                $newPersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneById($form['departamentoTipo']));
                $newPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['generoTipo']));
                $newPersona->setSegipId(1);
                $newPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById(0));
                $newPersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById(0));
                $newPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadocivilTipo')->findOneById(0));
                $newPersona->setRda('0');
                $newPersona->setEsvigente('t');
                $newPersona->setActivo('t');

                $newPersona->setCedulaTipo($em->getRepository('SieAppWebBundle:CedulaTipo')->find(($persona['nacionalidad'] == 'EX')?2:1));

                $em->persist($newPersona);
                $em->flush();
                $persona_validada = $newPersona;
            }
            // else existe la persona no se registra
        }
        else
        {
            $persona_validada = null;
        }

    /*
        //Verificar si la persona ya fue registrada
        $repository = $em->getRepository('SieAppWebBundle:Persona');
        if($persona['complemento'] == '0'){
            $query = $repository->createQueryBuilder('p')
                ->select('p')
                ->where('p.carnet = :carnet AND p.segipId = :valor')
                ->setParameter('carnet', $persona['carnet'])
                ->setParameter('valor', 1)
                ->getQuery();
        }
        else{
            $query = $repository->createQueryBuilder('p')
                ->select('p')
                ->where('p.carnet = :carnet AND p.complemento = :complemento AND p.segipId = :valor')
                ->setParameter('carnet', $persona['carnet'])
                ->setParameter('complemento', $persona['complemento'])
                ->setParameter('valor', 1)
                ->getQuery();
        }
        $personas = $query->getResult();

        if($personas) {
            $persona_validada = $personas[0];
        } else {
            //Buscar personas asociadas al nro de carnet y complemento con segip_id=0 y actualizar
            $repository = $em->getRepository('SieAppWebBundle:Persona');
            if($persona['complemento'] == '0'){
                $query = $repository->createQueryBuilder('p')
                    ->select('p')
                    ->where('p.carnet = :carnet AND p.segipId = :valor')
                    ->setParameter('carnet', $persona['carnet'])
                    ->setParameter('valor', 0)
                    ->getQuery();
            }
            else{
                $query = $repository->createQueryBuilder('p')
                    ->select('p')
                    ->where('p.carnet = :carnet AND p.complemento = :complemento AND p.segipId = :valor')
                    ->setParameter('carnet', $persona['carnet'])
                    ->setParameter('complemento', $persona['complemento'])
                    ->setParameter('valor', 0)
                    ->getQuery();
            }
            $personas = $query->getResult();
            
            $repository = $em->getRepository('SieAppWebBundle:Usuario');
            $query = $repository->createQueryBuilder('u')
                ->select('u')
                ->where('u.persona in (:personas)')
                ->setParameter('personas', $personas)
                ->getQuery();

            $usuarios = $query->getResult();

            //Actualizar CI, complemento y username
            foreach ($personas as $key => $value) {
                $value->setCarnet($value->getCarnet().'±');
                $em->persist($value);
                $em->flush();
            }

            foreach ($usuarios as $key => $value) {
                $value->setUsername($value->getUsername().'±');
                $em->persist($value);
                $em->flush();
            }

            if($persona['complemento'] == '0') {
                $persona['complemento'] = '';
            }
            $newPersona = new Persona();
            $newPersona->setCarnet($persona['carnet']);
            $newPersona->setComplemento(mb_strtoupper($persona['complemento'], 'utf-8'));
            $newPersona->setPaterno(mb_strtoupper($persona['primer_apellido'], 'utf-8'));
            $newPersona->setMaterno(mb_strtoupper($persona['segundo_apellido'], 'utf-8'));
            $newPersona->setNombre(mb_strtoupper($persona['nombre'], 'utf-8'));
            $newPersona->setFechaNacimiento(new \DateTime($persona['fecha_nacimiento']));
            $newPersona->setCelular($form['celular']);
            $newPersona->setCorreo(mb_strtolower($form['correo']), 'utf-8');
            $newPersona->setDireccion(mb_strtoupper($form['direccion']), 'utf-8');
            $newPersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneById($form['departamentoTipo']));
            $newPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['generoTipo']));
            $newPersona->setSegipId(1);
            $newPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->findOneById(0));
            $newPersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById(0));
            $newPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadocivilTipo')->findOneById(0));
            $newPersona->setRda('0');
            $newPersona->setEsvigente('t');
            $newPersona->setActivo('t');

            $em->persist($newPersona);
            $em->flush();
            $persona_validada = $newPersona;
        }
    */

        return $this->render($this->session->get('pathSystem') . ':InfoPersonalAdm:result_newpersona.html.twig',array(
            'persona'=>$persona_validada,
            'institucion' => $institucion,
            'gestion' => $gestion
        ));
    }

    /**
     * Crea un formulario para buscar una persona por C.I.
     *
     */
    private function searchForm() {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramientalt_info_personal_adm_result'))
                //->add('carnetIdentidad', 'text', array('label' => 'Carnet de Identidad', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'pattern' => '[0-9]{5,10}', 'maxlength' => '11')))
                //->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'attr' => array('class' => 'form-control jonlynumbersletters jupper', 'maxlength' => '2', 'autocomplete' => 'off')))
                ->add('buscar', 'submit', array('label' => 'Buscar coincidencias', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        return $form;
    }

}
