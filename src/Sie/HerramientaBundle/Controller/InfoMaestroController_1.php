<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\MaestroInscripcion;
use Sie\AppWebBundle\Entity\MaestroInscripcionIdioma;

/**
 * EstudianteInscripcion controller.
 *
 */
class InfoMaestroController extends Controller {

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
        $form = $request->get('form');

        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['sie']);
        if (!$institucioneducativa) {
            $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
            return $this->render('SieAppWebBundle:MaestroGestion:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }

        /*
         * verificamos si tiene tuicion
         */
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
        $query->bindValue(':sie', $form['sie']);
        $query->bindValue(':rolId', $this->session->get('roluser'));
        $query->execute();
        $aTuicion = $query->fetchAll();

        if ($aTuicion[0]['get_ue_tuicion']) {
            $institucion = $form['sie'];
            $gestion = $form['gestion'];
        } else {
            $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
            return $this->render($this->session->get('pathSystem') . ':InfoMaestro:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }

        // creamos variables de sesion de la institucion educativa y gestion
        $request->getSession()->set('idInstitucion', $institucion);
        $request->getSession()->set('idGestion', $gestion);

        /*
         * lista de maestros registrados en la unidad educativa
         */
        //$cargos = $em->getRepository('SieAppWebBundle:CargoTipo')->findBy(array('rolTipo'=>2));
        $queryCargos = $em->createQuery(
                'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                     WHERE ct.rolTipo = 2');
        $cargos = $queryCargos->getResult();
        $cargosArray = array();

        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getId();
        }

        $query = $em->createQuery(
                        'SELECT mi, per, ft FROM SieAppWebBundle:MaestroInscripcion mi
                        JOIN mi.persona per
                        JOIN mi.formacionTipo ft
                        WHERE mi.institucioneducativa = :idInstitucion
                        AND mi.gestionTipo = :gestion
                        AND mi.cargoTipo IN (:cargos)
                        ORDER BY per.paterno, per.materno, per.nombre')
                ->setParameter('idInstitucion', $institucion)
                ->setParameter('gestion', $gestion)
                ->setParameter('cargos', $cargosArray);
        $maestro = $query->getResult();

        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);

        return $this->render($this->session->get('pathSystem') . ':InfoMaestro:index.html.twig', array(
                    'maestro' => $maestro, 'institucion' => $institucion, 'gestion' => $gestion
        ));
    }

    /*
     * Llamamos al formulario para nuevo maestro
     */

    public function newAction(Request $request) {
        //$this->session = new Session();
        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->getSession()->get('idInstitucion'));

        return $this->render($this->session->get('pathSystem') . ':InfoMaestro:new.html.twig', array(
                    'form' => $this->newForm($request->getSession()->get('idInstitucion'), $request->getSession()->get('idGestion'))->createView(),
                    'institucion' => $institucion,
                    'gestion' => $request->getSession()->get('idGestion')
        ));
    }

    /*
     * formulario de nuevo maestro
     */

    private function newForm($idInstitucion, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $query = $em->createQuery(
                        'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                        WHERE ct.rolTipo IN (:id)')
                ->setParameter('id', array(2));

        $cargos = $query->getResult();
        $cargosArray = array();
        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getCargo();
        }

        $financiamiento = $em->createQuery(
                        'SELECT ft FROM SieAppWebBundle:FinanciamientoTipo ft
                WHERE ft.id NOT IN  (:id)')
                ->setParameter('id', array(0, 5))
                ->getResult();
        $financiamientoArray = array();
        foreach ($financiamiento as $f) {
            $financiamientoArray[$f->getId()] = $f->getFinanciamiento();
        }

        $formacion = $em->createQuery(
                        'SELECT ft FROM SieAppWebBundle:FormacionTipo ft
                WHERE ft.id NOT IN (:id)')
                ->setParameter('id', array(0, 22))
                ->getResult();
        $formacionArray = array();
        foreach ($formacion as $fr) {
            $formacionArray[$fr->getId()] = $fr->getFormacion();
        }

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_info_maestro_create'))
                ->add('institucionEducativa', 'hidden', array('data' => $idInstitucion))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('carnetIdentidad', 'text', array('label' => 'Carnet de Identidad', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'pattern' => '[0-9]{5,10}', 'maxlength' => '11', 'onkeyup' => 'verificarExistePersona(this.value)')))
                ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'attr' => array('class' => 'form-control jonlynumbersletters jupper', 'maxlength' => '2', 'autocomplete' => 'off')))
                ->add('funcion', 'choice', array('label' => 'Función que desempeña', 'required' => true, 'choices' => $cargosArray, 'attr' => array('class' => 'form-control')))
                ->add('financiamiento', 'choice', array('label' => 'Fuente de Financiamiento', 'required' => true, 'choices' => $financiamientoArray, 'attr' => array('class' => 'form-control')))
                ->add('formacion', 'choice', array('label' => 'Último grado de formación alcanzado', 'required' => true, 'choices' => $formacionArray, 'attr' => array('class' => 'form-control')))
                ->add('formacionDescripcion', 'text', array('label' => 'Descripción del último grado de formación alcanzado', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '90')))
                ->add('formacion2', 'choice', array('label' => 'Formación Específica', 'required' => true, 'choices' => $formacionArray, 'attr' => array('class' => 'form-control')))
                ->add('formacionDescripcion2', 'text', array('label' => 'Descripción de Formación Específica', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '90')))
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
            // Verificar si la persona ya esta registrada

            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneByCarnet($form['carnetIdentidad']);


            $personaId = $persona->getId();

            // verificamos si el personal administrativo esta registrado ya en tabla maestro inscripcion
            $maestroInscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array('institucioneducativa' => $form['institucionEducativa'], 'persona' => $personaId, 'gestionTipo' => 2015, 'rolTipo' => 2));
            if ($maestroInscripcion) { // Si  el personalAdministrativo ya esta inscrito
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('newError', 'No se realizó el registro, la persona ya esta registrada');
                return $this->redirect($this->generateUrl('maestrogestion'));
            }

            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('maestro_inscripcion');")->execute();

            // Registro Maestro inscripcion
            $maestroinscripcion = new MaestroInscripcion();
            $maestroinscripcion->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->findOneById($form['funcion']));
            $maestroinscripcion->setEspecialidadTipo($em->getRepository('SieAppWebBundle:EspecialidadMaestroTipo')->findOneById(0));
            $maestroinscripcion->setEstadomaestro($em->getRepository('SieAppWebBundle:EstadomaestroTipo')->findOneById(1));
            $maestroinscripcion->setEstudiaIdiomaMaternoId($form['idiomaOriginario']);
            $maestroinscripcion->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById($form['financiamiento']));
            $maestroinscripcion->setFormacionTipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById($form['formacion']));
            $maestroinscripcion->setFormaciondescripcion(mb_strtoupper($form['formacionDescripcion'], 'utf-8'));
            $maestroinscripcion->setFormacion2Tipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById($form['formacion2']));
            $maestroinscripcion->setFormaciondescripcion2(mb_strtoupper($form['formacionDescripcion2'], 'utf-8'));
            $maestroinscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($form['gestion']));
            $maestroinscripcion->setIdiomaMaterno(null);
            $maestroinscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucionEducativa']));
            $maestroinscripcion->setInstitucioneducativaSucursalI($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById(0));
            $maestroinscripcion->setLeeescribebraile((isset($form['leeEscribeBraile'])) ? 1 : 0);
            $maestroinscripcion->setNormalista((isset($form['normalista'])) ? 1 : 0);
            $maestroinscripcion->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneById(1));
            $maestroinscripcion->setPersona($em->getRepository('SieAppWebBundle:Persona')->findOneById($personaId));
            $maestroinscripcion->setRdaPlanillasId(0);
            $maestroinscripcion->setRef(0);
            $maestroinscripcion->setRolTipo($em->getRepository('SieAppWebBundle:RolTipo')->findOneById(2));
            $maestroinscripcion->setItem($form['item']);
            $em->persist($maestroinscripcion);
            $em->flush();

            // Registro de idiomas que habla    

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
                    $maestroIdioma->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($maestroinscripcion->getId()));
                    $em->persist($maestroIdioma);
                    $em->flush();
                }
            }
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente');
            return $this->redirect($this->generateUrl('maestrogestion', array('op' => 'result')));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /*
     * Listar los idiomas que habla y lee el maestro
     * en el formulario nuevo
     */

    public function listaridiomasleehablaescribeAction() {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $idioma = $em->getRepository('SieAppWebBundle:IdiomaMaterno')->findAll();
            $idiomas = array();
            foreach ($idioma as $i) {
                $idiomas[$i->getId()] = $i->getIdiomaMaterno();
            }
            $conoce = $em->getRepository('SieAppWebBundle:IdiomaconoceTipo')->findAll();
            $conocimiento = array();
            foreach ($conoce as $c) {
                $conocimiento[$c->getId()] = $c->getIdiomaConoce();
            }

            $response = new JsonResponse();
            $em->getConnection()->commit();
            return $response->setData(array('idiomas' => $idiomas, 'conoce' => $conocimiento));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /*
     * Listar los idiomas que habla lee el maestro
     * en el formulario edit
     */

    public function listaridiomasleehablaescribeeditAction($idMaestroInscripcion) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $idiomasM = $em->getRepository('SieAppWebBundle:MaestroInscripcionIdioma')->findBy(array('maestroInscripcion' => $idMaestroInscripcion));
            $idiomasMaestro = array();
            foreach ($idiomasM as $im) {
                $idiomasMaestro[$im->getId()] = array('idioma' => $im->getIdiomaMaterno()->getId(), 'lee' => $im->getIdiomaconoceTipoLee()->getId(), 'habla' => $im->getIdiomaconoceTipoHabla()->getId(), 'escribe' => $im->getIdiomaconoceTipoEscribe()->getId());
            }
            //die;
            $idioma = $em->getRepository('SieAppWebBundle:IdiomaMaterno')->findAll();
            $idiomas = array();
            foreach ($idioma as $i) {
                $idiomas[$i->getId()] = $i->getIdiomaMaterno();
            }
            $conoce = $em->getRepository('SieAppWebBundle:IdiomaconoceTipo')->findAll();
            $conocimiento = array();
            foreach ($conoce as $c) {
                $conocimiento[$c->getId()] = $c->getIdiomaConoce();
            }
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('idiomasMaestro' => $idiomasMaestro, 'idiomas' => $idiomas, 'conoce' => $conocimiento));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /*
     * Llamar al formulario de edicion
     */

    public function editAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($request->get('idPersona'));
            $maestroInscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($request->get('idMaestroInscripcion'));
            $idiomas = $em->getRepository('SieAppWebBundle:MaestroInscripcionIdioma')->findBy(array('maestroInscripcion' => $request->get('idMaestroInscripcion')));
            $em->getConnection()->commit();
            return $this->render($this->session->get('pathSystem') . 'InfoMaestro:edit.html.twig', array('form' => $this->editForm($request->getSession()->get('idInstitucion'), $request->getSession()->get('idGestion'), $persona, $maestroInscripcion, $idiomas)->createView()));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /*
     * formulario de edicion
     */

    private function editForm($idInstitucion, $gestion, $persona, $maestroInscripcion, $idiomas) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $this->session = new Session();
            switch ($this->session->get('sysname')) {
                case "PERMANENTE":
                    $query = $em->createQuery(
                                    'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                        WHERE ct.institucioneducativaTipo IN (:id)
                        AND ct.id IN (:ids)')
                            ->setParameter('id', array(4, 0))
                            ->setParameter('ids', array(14, 24));
                    break;
                default:
                    $query = $em->createQuery(
                                    'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                        WHERE ct.rolTipo IN (:id)')
                            ->setParameter('id', array(2));
                    break;
            }
            $cargos = $query->getResult();
            $cargosArray = array();
            foreach ($cargos as $c) {
                $cargosArray[$c->getId()] = $c->getCargo();
            }
            $financiamiento = $em->createQuery(
                            'SELECT ft FROM SieAppWebBundle:FinanciamientoTipo ft
                WHERE ft.id NOT IN  (:id)')
                    ->setParameter('id', array(0, 5))
                    ->getResult();
            $financiamientoArray = array();
            foreach ($financiamiento as $f) {
                $financiamientoArray[$f->getId()] = $f->getFinanciamiento();
            }

            $formacion = $em->createQuery(
                            'SELECT ft FROM SieAppWebBundle:FormacionTipo ft')
                    ->getResult();
            $formacionArray = array();
            foreach ($formacion as $fr) {
                $formacionArray[$fr->getId()] = $fr->getFormacion();
            }

            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('maestrogestion_update'))
                    ->add('institucionEducativa', 'hidden', array('data' => $idInstitucion))
                    ->add('gestion', 'hidden', array('data' => $gestion))
                    ->add('idPersona', 'hidden', array('data' => $persona->getId()))
                    ->add('idMaestroInscripcion', 'hidden', array('data' => $maestroInscripcion->getId()))
                    ->add('carnetIdentidad', 'text', array('label' => 'Carnet de Identidad', 'data' => $persona->getCarnet(), 'required' => true, 'disabled' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'placeholder' => '', 'pattern' => '[0-9]{5,10}', 'maxlength' => '11', 'onkeyup' => 'verificarExistePersona(this.value)')))
                    ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'data' => $persona->getComplemento(), 'attr' => array('class' => 'form-control jonlynumbersletters jupper', 'maxlength' => '2', 'autocomplete' => 'off')))
                    ->add('rda', 'text', array('label' => 'Rda', 'data' => $persona->getRda(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'pattern' => '[0-9]{1,10}', 'maxlength' => '8')))
                    ->add('paterno', 'text', array('label' => 'Apellido Paterno', 'data' => $persona->getPaterno(), 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper', 'pattern' => "[a-zA-Z\sñÑáéíóúÁÉÍÓÚ']{2,45}", 'maxlength' => '45')))
                    ->add('materno', 'text', array('label' => 'Apellido Materno', 'data' => $persona->getMaterno(), 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper', 'pattern' => "[a-zA-Z\sñÑáéíóúÁÉÍÓÚ']{2,45}", 'maxlength' => '45')))
                    ->add('nombre', 'text', array('label' => 'Nombre(s)', 'data' => $persona->getNombre(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper', 'pattern' => "[a-zA-Z\sñÑáéíóúÁÉÍÓÚ']{2,45}", 'maxlength' => '45')))
                    ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento', 'data' => $persona->getFechaNacimiento()->format('d-m-Y'), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'placeholder' => 'dd-mm-aaaa', 'pattern' => '(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012]).[0-9]{4}')))
                    ->add('genero', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:GeneroTipo', 'data' => $persona->getGeneroTipo(), 'attr' => array('class' => 'form-control jupper')))
                    ->add('celular', 'text', array('label' => 'Nro de Celular', 'data' => $persona->getCelular(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))
                    ->add('correo', 'text', array('label' => 'Correo Electrónico', 'data' => $persona->getCorreo(), 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))
                    ->add('direccion', 'text', array('label' => 'Dirección de Domicilio', 'data' => $persona->getDireccion(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                    ->add('funcion', 'choice', array('label' => 'Función que desempeña', 'required' => true, 'choices' => $cargosArray, 'data' => $maestroInscripcion->getCargoTipo()->getId(), 'attr' => array('class' => 'form-control')))
                    ->add('financiamiento', 'choice', array('label' => 'Fuente de Financiamiento', 'required' => true, 'choices' => $financiamientoArray, 'data' => $maestroInscripcion->getFinanciamientoTipo()->getId(), 'attr' => array('class' => 'form-control')))
                    ->add('formacion', 'choice', array('label' => 'Último grado de formación alcanzado', 'required' => true, 'choices' => $formacionArray, 'data' => $maestroInscripcion->getFormacionTipo()->getId(), 'attr' => array('class' => 'form-control')))
                    ->add('formacionDescripcion', 'text', array('label' => 'Descripción del último grado de formación alcanzado', 'required' => false, 'data' => $maestroInscripcion->getFormaciondescripcion(), 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '90')))
                    ->add('formacion2', 'choice', array('label' => 'Formación Específica', 'required' => true, 'choices' => $formacionArray, 'data' => ($maestroInscripcion->getFormacion2Tipo() != NULL) ? $maestroInscripcion->getFormacion2Tipo()->getId() : 0, 'attr' => array('class' => 'form-control')))
                    ->add('formacionDescripcion2', 'text', array('label' => 'Descripción de Formación Específica', 'required' => false, 'data' => $maestroInscripcion->getFormaciondescripcion2(), 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '90')))
                    ->add('normalista', 'checkbox', array('required' => false, 'label' => 'Normalista', 'attr' => array('class' => 'form-control', 'checked' => $maestroInscripcion->getNormalista())))
                    ->add('item', 'text', array('label' => 'Número de Item', 'required' => true, 'data' => $maestroInscripcion->getItem(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'pattern' => '[0-9]{1,10}')))
                    ->add('idiomaOriginario', 'entity', array('class' => 'SieAppWebBundle:IdiomaMaterno', 'data' => ((int) $maestroInscripcion->getEstudiaIdiomaMaternoId()), 'label' => 'Actualmente que idioma originario esta estudiando', 'required' => false, 'attr' => array('class' => 'form-control')))
                    ->add('leeEscribeBraile', 'checkbox', array('required' => false, 'label' => 'Lee y Escribe en Braille', 'attr' => array('class' => 'form-control', 'checked' => $maestroInscripcion->getLeeescribebraile())))
                    ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                    ->getForm();
            $em->getConnection()->commit();
            return $form;
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /*
     * guardar datos de modificacion
     */

    public function updateAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');

            // Actualizacion de datos persona
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['idPersona']);

            $persona->setCelular($form['celular']);
            $persona->setCorreo(mb_strtolower($form['correo'], "utf-8"));
            $persona->setDireccion(mb_strtoupper($form['direccion'], "utf-8"));
            $persona->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
            $persona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['genero']));
            $persona->setPaterno(mb_strtoupper($form['paterno'], "utf-8"));
            $persona->setMaterno(mb_strtoupper($form['materno'], "utf-8"));
            $persona->setNombre(mb_strtoupper($form['nombre'], "utf-8"));
            $persona->setRda($form['rda']);
            $persona->setComplemento(mb_strtoupper($form['complemento'], 'utf-8'));

            $em->persist($persona);
            $em->flush();

            //Actualizacion en la tabla maestro inscripcion
            $maestroinscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($form['idMaestroInscripcion']);

            $maestroinscripcion->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->findOneById($form['funcion']));
            $maestroinscripcion->setEstudiaIdiomaMaternoId(null);
            $maestroinscripcion->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById($form['financiamiento']));
            $maestroinscripcion->setFormacionTipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById($form['formacion']));
            $maestroinscripcion->setFormaciondescripcion(mb_strtoupper($form['formacionDescripcion'], 'utf-8'));
            $maestroinscripcion->setFormacion2Tipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById($form['formacion2']));
            $maestroinscripcion->setFormaciondescripcion2(mb_strtoupper($form['formacionDescripcion2'], 'utf-8'));
            $maestroinscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucionEducativa']));
            $maestroinscripcion->setInstitucioneducativaSucursalI($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById(0));
            $maestroinscripcion->setLeeescribebraile((isset($form['leeEscribeBraile'])) ? 1 : 0);
            $maestroinscripcion->setNormalista((isset($form['normalista'])) ? 1 : 0);
            $maestroinscripcion->setItem($form['item']);
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
                    echo $idioma;
                }
            }
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('updateOk', 'Datos modificados correctamente');
            return $this->redirect($this->generateUrl('maestrogestion', array('op' => 'result')));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'Error en la modificacion de datos');
            return $this->redirect($this->generateUrl('maestrogestion', array('op' => 'result')));
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
                $institucionCursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('maestroInscripcion' => $maestroInscripcion->getId()));
                if ($institucionCursoOferta) {

                    $this->get('session')->getFlashBag()->add('eliminarError', 'El registro no se pudo eliminar, el maestro tiene areas asignadas.');
                    return $this->redirect($this->generateUrl('maestrogestion', array('op' => 'result')));
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

                return $this->redirect($this->generateUrl('herramienta_inbox_open'));
            }
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('eliminarError', 'El registro no se pudo eliminar');
            return $this->redirect($this->generateUrl('herramienta_inbox_open'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /* funcion para verificar si el maestro ya existe 
      con el carnet, metodo ajax */

    public function verificar_existe_personaAction($carnet) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneByCarnet($carnet);
            if ($persona) {
                $paterno = $persona->getPaterno();
                $materno = $persona->getMaterno();
                $nombre = $persona->getNombre();
                $fechaNacimiento = $persona->getFechaNacimiento()->format('d-m-Y');
                $genero = $persona->getGeneroTipo()->getId();
                $direccion = $persona->getDireccion();
                $celular = $persona->getCelular();
                $rda = $persona->getRda();
                $correo = $persona->getCorreo();
                /* Creamos el array para los generos */
                $generos = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();
                $generosArray = array();
                foreach ($generos as $g) {
                    $generosArray[$g->getId()] = $g->getGenero();
                }
                /* Se envia los parametros en formato json */
                $em->getConnection()->commit();
                $response = new JsonResponse();
                return $response->setData(array('encontrado' => 'si', 'paterno' => $paterno, 'materno' => $materno, 'nombre' => $nombre, 'fechaNacimiento' => $fechaNacimiento, 'genero' => $genero, 'direccion' => $direccion, 'celular' => $celular, 'rda' => $rda, 'correo' => $correo, 'generosArray' => $generosArray));
            } else {
                $em->getConnection()->commit();
                $response = new JsonResponse();
                return $response->setData(array('encontrado' => 'no'));
            }
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

}
