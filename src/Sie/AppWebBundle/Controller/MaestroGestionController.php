<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Controller\FuncionesController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Entity\MaestroInscripcion;
use Sie\AppWebBundle\Entity\MaestroInscripcionIdioma;

/**
 * MaestroGestion controller.
 *
 */
class MaestroGestionController extends Controller {

    protected $session;

    /*
     * Index maestros en la unidad educativa
     */
    public function indexAction(Request $request) {

         $this->session = $request->getSession();
         $idUsuario = $this->session->get('userId');
         $rolUsuario = $this->session->get('roluser');
         $currentyear = $this->session->get('currentyear');

        //Validación si el usuario está loggeado
        if (!isset($idUsuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$idUsuario) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SieAppWebBundle:MaestroGestion:search.html.twig', array('form' => $this->formSearch($currentyear)->createView()));
        
    }

    /*
     * formulario de búsqueda de institucion educativa
     */
    private function formSearch($gestionactual) {
        
        $gestiones = array($gestionactual => $gestionactual, $gestionactual - 1 => $gestionactual - 1, $gestionactual - 2 => $gestionactual - 2, $gestionactual - 3 => $gestionactual - 3, $gestionactual - 4 => $gestionactual - 4);

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('maestrogestion_list'))
                ->add('sie', 'text', array('required' => true, 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /*
     * Lista de maestros en la unidad educativa
     */
    public function listAction(Request $request) {

         $this->session = $request->getSession();
         $idUsuario = $this->session->get('userId');
         $rolUsuario = $this->session->get('roluser');

        //Validación - usuario loggeado
        if (!isset($idUsuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$idUsuario) {
            return $this->redirect($this->generateUrl('login'));
        }

        $servicioMaestroInscripcion = $this->get('sie_app_web.maestro_inscripcion');
        $servicioFunciones = $this->get('sie_app_web.funciones');

        $form = $request->get('form');

        $em = $this->getDoctrine()->getManager();

        /*
         * verificamos si tiene tuicion
         */
        $tuicion = $servicioFunciones->verificaTuicion($form['sie'], $idUsuario, $rolUsuario);
        
        if ($tuicion['tuicion']) {
            $sie = $form['sie'];
            $gestion = $form['gestion'];
            $request->getSession()->set('idInstitucion', $sie);
            $request->getSession()->set('idGestion', $gestion);
        } else {
            $this->get('session')->getFlashBag()->add('error', 'No tiene tuición sobre la unidad educativa.');
            return $this->render('SieAppWebBundle:MaestroGestion:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }

        /*
         * lista de maestros registrados en la unidad educativa
         */
        $maestro = $servicioMaestroInscripcion->listar($sie, $gestion)['maestro'];
        $maestro_no_idioma = $servicioMaestroInscripcion->listar($sie, $gestion)['maestro_no_idioma'];
        $maestro_no_genero = $servicioMaestroInscripcion->listar($sie, $gestion)['maestro_no_genero'];

        /* 
         * Activar o desactivar acciones
         */
        $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($sie, $gestion);
        $notaTipo = $em->getRepository('SieAppWebBundle:NotaTipo')->findOneById($operativo);
        $rolTipo = $em->getRepository('SieAppWebBundle:RolTipo')->findOneById(2);

        $validacion_personal_aux = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoValidacionpersonal')->findOneBy(array('gestionTipo' => $gestion, 'institucioneducativa' => $sie, 'notaTipo' => $notaTipo, 'rolTipo' => $rolTipo));

        $activar_acciones = true;
        if($validacion_personal_aux){
            $activar_acciones = false;
        }

        /*
         * Obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($sie);

        return $this->render('SieAppWebBundle:MaestroGestion:list.html.twig', array(
                    'maestro' => $maestro,
                    'maestro_no_idioma' => $maestro_no_idioma,
                    'maestro_no_genero' => $maestro_no_genero,
                    'activar_acciones' => $activar_acciones,
                    'institucion' => $institucion,
                    'gestion' => $gestion
        ));
    }

    /*
     * formulario para nuevo maestro
     */

    public function newAction(Request $request) {
        return $this->render('SieAppWebBundle:MaestroGestion:new.html.twig', array('form' => $this->newForm($request->getSession()->get('idInstitucion'), $request->getSession()->get('idGestion'))->createView()));
    }

    /*
     * formulario de nuevo maestro
     */
    private function newForm($idInstitucion, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $this->session = new Session();
            $query = $em->createQuery(
                            'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                        WHERE ct.rolTipo IN (:id) ORDER BY ct.id')
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
                    ->setAction($this->generateUrl('maestrogestion_create'))
                    ->add('institucionEducativa', 'hidden', array('data' => $idInstitucion))
                    ->add('gestion', 'hidden', array('data' => $gestion))
                    ->add('carnetIdentidad', 'text', array('label' => 'Carnet de Identidad', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'placeholder' => '', 'pattern' => '[0-9]{5,10}', 'maxlength' => '11', 'onkeyup' => 'verificarExistePersona(this.value)')))
                    ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'attr' => array('class' => 'form-control jonlynumbersletters jupper', 'maxlength' => '2', 'autocomplete' => 'off', 'disabled' => 'disabled')))
                    ->add('rda', 'text', array('label' => 'Rda', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'pattern' => '[0-9]{1,10}', 'maxlength' => '8')))
                    ->add('paterno', 'text', array('label' => 'Apellido Paterno', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper', 'disabled' => 'disabled')))
                    ->add('materno', 'text', array('label' => 'Apellido Materno', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper', 'disabled' => 'disabled')))
                    ->add('nombre', 'text', array('label' => 'Nombre(s)', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper', 'disabled' => 'disabled')))
                    ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'disabled' => 'disabled')))
                    ->add('genero', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:GeneroTipo', 'attr' => array('class' => 'form-control jupper', 'disabled' => 'disabled')))
                    ->add('celular', 'text', array('label' => 'Nro de Celular', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))
                    ->add('correo', 'text', array('label' => 'Correo Electrónico', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))
                    ->add('direccion', 'text', array('label' => 'Dirección de Domicilio', 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                    ->add('funcion', 'choice', array('label' => 'Función que desempeña', 'required' => true, 'choices' => $cargosArray, 'attr' => array('class' => 'form-control')))
                    ->add('financiamiento', 'choice', array('label' => 'Fuente de Financiamiento', 'required' => true, 'choices' => $financiamientoArray, 'attr' => array('class' => 'form-control')))
                    ->add('formacion', 'choice', array('label' => 'Último grado de formación alcanzado', 'required' => true, 'choices' => $formacionArray, 'attr' => array('class' => 'form-control')))
                    ->add('formacionDescripcion', 'text', array('label' => 'Descripción del último grado de formación alcanzado', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '90')))
                    ->add('normalista', 'checkbox', array('required' => false, 'label' => 'Normalista', 'attr' => array('class' => 'form-control')))
                    ->add('item', 'text', array('label' => 'Número de Item', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'pattern' => '[0-9]{1,10}')))
                    ->add('idiomaOriginario', 'entity', array('class' => 'SieAppWebBundle:IdiomaMaterno', 'data' => $em->getReference('SieAppWebBundle:IdiomaMaterno', 97), 'label' => 'Actualmente que idioma originario esta estudiando', 'required' => false, 'attr' => array('class' => 'form-control')))
                    ->add('leeEscribeBraile', 'checkbox', array('required' => false, 'label' => 'Lee y Escribe en Braille', 'attr' => array('class' => 'form-control')))
                    ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                    ->getForm();
            $em->getConnection()->commit();
            return $form;
        } catch (exception $ex) {
            $em->getConnection()->rollback();
        }
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
            
            $query = $em->createQuery(
                            'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                        WHERE ct.rolTipo IN (:id) ORDER BY ct.id')
                    ->setParameter('id', array(2));

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

            $estudia = $em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById($form['idiomaOriginario']);
            // Registro Maestro inscripcion
            $maestroinscripcion = new MaestroInscripcion();
            $maestroinscripcion->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->findOneById($form['funcion']));
            $maestroinscripcion->setEspecialidadTipo($em->getRepository('SieAppWebBundle:EspecialidadMaestroTipo')->findOneById(0));
            $maestroinscripcion->setEstadomaestro($em->getRepository('SieAppWebBundle:EstadomaestroTipo')->findOneById(1));
            $maestroinscripcion->setEstudiaiomaMaterno($estudia);
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
            $maestroinscripcion->setRolTipo($em->getRepository('SieAppWebBundle:RolTipo')->findOneById(2));
            $maestroinscripcion->setItem($form['item']);
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
            return $this->render('SieAppWebBundle:MaestroGestion:edit.html.twig', array('form' => $this->editForm($request->getSession()->get('idInstitucion'), $request->getSession()->get('idGestion'), $persona, $maestroInscripcion, $idiomas)->createView()));
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
            
            $query = $em->createQuery(
                            'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                        WHERE ct.rolTipo IN (:id) ORDER BY ct.id')
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
                    ->add('carnetIdentidad', 'text', array('label' => 'Carnet de Identidad', 'data' => $persona->getCarnet(), 'required' => true, 'disabled' => 'disabled', 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'placeholder' => '', 'pattern' => '[0-9]{5,10}', 'maxlength' => '11', 'onkeyup' => 'verificarExistePersona(this.value)')))
                    ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'data' => $persona->getComplemento(), 'attr' => array('class' => 'form-control jonlynumbersletters jupper', 'maxlength' => '2', 'autocomplete' => 'off', 'readonly' => 'readonly')))
                    ->add('rda', 'text', array('label' => 'Rda', 'data' => $persona->getRda(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'pattern' => '[0-9]{1,10}', 'maxlength' => '8')))
                    ->add('paterno', 'text', array('label' => 'Apellido Paterno', 'data' => $persona->getPaterno(), 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper', 'pattern' => "[a-zA-Z\sñÑáéíóúÁÉÍÓÚ']{2,45}", 'maxlength' => '45', 'readonly' => 'readonly')))
                    ->add('materno', 'text', array('label' => 'Apellido Materno', 'data' => $persona->getMaterno(), 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper', 'pattern' => "[a-zA-Z\sñÑáéíóúÁÉÍÓÚ']{2,45}", 'maxlength' => '45', 'readonly' => 'readonly')))
                    ->add('nombre', 'text', array('label' => 'Nombre(s)', 'data' => $persona->getNombre(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jname jupper', 'pattern' => "[a-zA-Z\sñÑáéíóúÁÉÍÓÚ']{2,45}", 'maxlength' => '45', 'readonly' => 'readonly')))
                    ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento', 'data' => $persona->getFechaNacimiento()->format('d-m-Y'), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'placeholder' => 'dd-mm-aaaa', 'pattern' => '(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012]).[0-9]{4}', 'readonly' => 'readonly')))
                    ->add('genero', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:GeneroTipo', 'data' => $persona->getGeneroTipo(), 'attr' => array('class' => 'form-control jupper', 'readonly' => 'readonly')))
                    ->add('celular', 'text', array('label' => 'Nro de Celular', 'data' => $persona->getCelular(), 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))
                    ->add('correo', 'text', array('label' => 'Correo Electrónico', 'data' => $persona->getCorreo(), 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))
                    ->add('direccion', 'text', array('label' => 'Dirección de Domicilio', 'data' => $persona->getDireccion(), 'required' => false, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                    ->add('funcion', 'choice', array('label' => 'Función que desempeña', 'required' => true, 'choices' => $cargosArray, 'data' => $maestroInscripcion->getCargoTipo()->getId(), 'attr' => array('class' => 'form-control')))
                    ->add('financiamiento', 'choice', array('label' => 'Fuente de Financiamiento', 'required' => true, 'choices' => $financiamientoArray, 'data' => $maestroInscripcion->getFinanciamientoTipo()->getId(), 'attr' => array('class' => 'form-control')))
                    ->add('formacion', 'choice', array('label' => 'Último grado de formación alcanzado', 'required' => true, 'choices' => $formacionArray, 'data' => $maestroInscripcion->getFormacionTipo()->getId(), 'attr' => array('class' => 'form-control')))
                    ->add('formacionDescripcion', 'text', array('label' => 'Descripción del último grado de formación alcanzado', 'required' => false, 'data' => $maestroInscripcion->getFormaciondescripcion(), 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '90')))
                    ->add('normalista', 'checkbox', array('required' => false, 'label' => 'Normalista', 'attr' => array('class' => 'form-control', 'checked' => $maestroInscripcion->getNormalista())))
                    ->add('item', 'text', array('label' => 'Número de Item', 'required' => true, 'data' => $maestroInscripcion->getItem(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'pattern' => '[0-9]{1,10}')))
                    ->add('idiomaOriginario', 'entity', array('class' => 'SieAppWebBundle:IdiomaMaterno', 'data' => ($maestroInscripcion->getEstudiaiomaMaterno()), 'label' => 'Actualmente que idioma originario esta estudiando', 'required' => false, 'attr' => array('class' => 'form-control')))
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
            //$persona->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
            //$persona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['genero']));
            //$persona->setPaterno(mb_strtoupper($form['paterno'], "utf-8"));
            //$persona->setMaterno(mb_strtoupper($form['materno'], "utf-8"));
            //$persona->setNombre(mb_strtoupper($form['nombre'], "utf-8"));
            $persona->setRda($form['rda']);
            //$persona->setComplemento(mb_strtoupper($form['complemento'],'utf-8'));

            $em->persist($persona);
            $em->flush();

            //Actualizacion en la tabla maestro inscripcion
            $maestroinscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($form['idMaestroInscripcion']);

            $maestroinscripcion->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->findOneById($form['funcion']));
            $maestroinscripcion->setEstudiaiomaMaterno(null);
            $maestroinscripcion->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById($form['financiamiento']));
            $maestroinscripcion->setFormacionTipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById($form['formacion']));
            $maestroinscripcion->setFormaciondescripcion(mb_strtoupper($form['formacionDescripcion'], 'utf-8'));
            $maestroinscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucionEducativa']));
            $maestroinscripcion->setInstitucioneducativaSucursal($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById(0));
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
                // Verificamos si el maestro tinee asignados algunas areas o asignaturas
                $institucionCursoOfertaMaestro = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('maestroInscripcion' => $maestroInscripcion->getId()));

                if ($institucionCursoOfertaMaestro) {
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
                // Desactivamos el estado de la persona 
                //$persona = $em->getRepository('SieAppWebBundle:Persona')->find($maestroInscripcion->getPersona()->getId());
                //$persona->setActivo('f');
                //$em->flush();
                //eliminamos el registro de inscripcion del maestro
                $em->remove($maestroInscripcion);
                $em->flush();
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('eliminarOk', 'El registro fue eliminado exitosamente');
                return $this->redirect($this->generateUrl('maestrogestion', array('op' => 'result')));
            }
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('eliminarError', 'El registro no se pudo eliminar');
            return $this->redirect($this->generateUrl('maestrogestion', array('op' => 'result')));
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
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('carnet' => $carnet, 'esvigente' => 't'));
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
