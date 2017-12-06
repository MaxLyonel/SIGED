<?php

namespace Sie\DgesttlaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\TtecAdministrativoCarreraPersona;
use GuzzleHttp\Client;

/**
 * EstudianteInscripcion controller.
 *
 */
class InfoPersonalAdmCarreraController extends Controller {

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

        if ($request->getMethod() == 'POST') {
            $form = $request->get('form');

            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['sie']);
            if (!$institucioneducativa) {
                $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                if($this->session->get('roluser') == 7 || $this->session->get('roluser') == 8 || $this->session->get('roluser') == 10){
                    return $this->redirect($this->generateUrl('dgesttla_info_personal_adm_carr_tsie_index'));
                }
                return $this->redirect($this->generateUrl('dgesttla_info_personal_adm_carr_index'));
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
                // creamos variables de sesion de la institucion educativa y gestion
                $request->getSession()->set('idInstitucion', $institucion);
                $request->getSession()->set('idGestion', $gestion);
            } else {
                $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
                if($this->session->get('roluser') == 7 || $this->session->get('roluser') == 8 || $this->session->get('roluser') == 10){
                    return $this->redirect($this->generateUrl('dgesttla_info_personal_adm_carr_tsie_index'));
                }
                return $this->redirect($this->generateUrl('dgesttla_info_personal_adm_carr_index'));
            }
        } else {
            $institucion = $request->getSession()->get('idInstitucion');
            $gestion = $request->getSession()->get('idGestion');
            $carrera = $request->getSession()->get('idCarrera');
        }

        /*
         * lista de personal registrado en el instituto
         */
        $query = $em->createQuery(
                'SELECT ct FROM SieAppWebBundle:TtecCargoTipo ct ORDER BY ct.id');

        $cargos = $query->getResult();
        $cargosArray = array();
        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getId();
        }

        $query = $em->createQuery(
                        'SELECT ap, per, ct, cdt FROM SieAppWebBundle:TtecAdministrativoCarreraPersona ap
                    INNER JOIN ap.persona per
                    INNER JOIN ap.ttecCargoTipo ct
                    INNER JOIN ap.ttecCargoDesignacionTipo cdt
                    WHERE ap.ttecCarreraTipo = :idCarrera
                    AND ap.gestionTipo = :gestion
                    AND ap.ttecCargoTipo IN (:cargos)
                    ORDER BY per.paterno, per.materno, per.nombre')
                ->setParameter('idCarrera', $carrera)
                ->setParameter('gestion', $gestion)
                ->setParameter('cargos', $cargosArray);
        $personal = $query->getResult();

        /*
         * obtenemos datos de la unidad educativa
         */
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        $carrera = $em->getRepository('SieAppWebBundle:TtecCarreraTipo')->findOneById($carrera);
        $gestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion);
        
        return $this->render($this->session->get('pathSystem') . ':InfoPersonalAdmCarrera:index.html.twig', array(
                'personal' => $personal,
                'institucion' => $institucioneducativa,
                'carrera' => $carrera,
                'gestion' => $gestion,
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
        $carrera = $request->getSession()->get('idCarrera');

        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->getSession()->get('idInstitucion'));
        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['idPersona']);
        $carrera = $em->getRepository('SieAppWebBundle:TtecCarreraTipo')->findOneById($carrera);

        return $this->render($this->session->get('pathSystem') . ':InfoPersonalAdmCarrera:new.html.twig', array(
                    'form' => $this->newForm($form['idInstitucion'], $form['gestion'], $form['idPersona'], $carrera->getId())->createView(),
                    'institucion' => $institucion,
                    'gestion' => $request->getSession()->get('idGestion'),
                    'persona' => $persona,
                    'carrera' => $carrera
        ));
    }

    /*
     * formulario de nuevo maestro
     */

    private function newForm($idInstitucion, $gestion, $idPersona, $idCarrera) {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT ct FROM SieAppWebBundle:TtecCargoTipo ct
            WHERE ct.id NOT IN (:id) ORDER BY ct.cargo')
            ->setParameter('id', array(9));

        $cargos = $query->getResult();
        $cargosArray = array();
        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getCargo();
        }

        $query = $em->createQuery('SELECT cdt FROM SieAppWebBundle:TtecCargoDesignacionTipo cdt ORDER BY cdt.formaDesignacion');

        $designacioncargos = $query->getResult();
        $designacioncargosArray = array();
        foreach ($designacioncargos as $dc) {
            $designacioncargosArray[$dc->getId()] = $dc->getFormaDesignacion();
        }

        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($idPersona);

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('dgesttla_info_personal_adm_carr_create'))
                ->add('institucionEducativa', 'hidden', array('data' => $idInstitucion))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('persona', 'hidden', array('data' => $idPersona))
                ->add('carrera', 'hidden', array('data' => $idCarrera))
                ->add('genero', 'entity', array('class' => 'SieAppWebBundle:GeneroTipo', 'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $persona->getGeneroTipo()->getId()), 'label' => 'Género', 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('celular', 'text', array('label' => 'Nro. de Celular', 'data' => $persona->getCelular(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))
                ->add('correo', 'text', array('label' => 'Correo Electrónico', 'data' => $persona->getCorreo(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))
                ->add('direccion', 'text', array('label' => 'Dirección de Domicilio', 'data' => $persona->getDireccion(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('cargo', 'choice', array('label' => 'Función que desempeña (cargo)', 'required' => true, 'choices' => $cargosArray, 'attr' => array('class' => 'form-control')))
                ->add('cargoDesignacion', 'choice', array('label' => 'Forma de designación', 'required' => true, 'choices' => $designacioncargosArray, 'attr' => array('class' => 'form-control')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

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
            
            $personaId = $form['persona'];

            // Verificar si la persona ya esta registrada
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['persona']);
            $persona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['genero']));
            $persona->setDireccion(mb_strtoupper($form['direccion']), 'utf-8');
            $persona->setCelular($form['celular']);
            $persona->setCorreo(mb_strtolower($form['correo']), 'utf-8');
            $em->persist($persona);
            $em->flush();

            $admPersona = $em->getRepository('SieAppWebBundle:TtecAdministrativoCarreraPersona')->findOneBy(array('ttecCarreraTipo' => $form['carrera'], 'persona' => $personaId, 'gestionTipo' => $form['gestion']));
            // verificamos si el personal administrativo esta registrado ya en tabla maestro inscripcion
            if ($admPersona) { 
                $this->get('session')->getFlashBag()->add('newError', 'No se realizó el registro, la persona ya está registrada.');
                return $this->redirect($this->generateUrl('dgesttla_info_personal_adm_carr_index'));
            }

            // Registro Administrativo Carrera Persona
            $admPersonaNew = new TtecAdministrativoCarreraPersona();
            //$admPersonaNew->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucionEducativa']));
            $admPersonaNew->setTtecCarreraTipo($em->getRepository('SieAppWebBundle:TtecCarreraTipo')->findOneById($form['carrera']));
            $admPersonaNew->setPersona($persona);
            $admPersonaNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($form['gestion']));
            $admPersonaNew->setTtecCargoTipo($em->getRepository('SieAppWebBundle:TtecCargoTipo')->findOneById($form['cargo']));
            $admPersonaNew->setTtecCargoDesignacionTipo($em->getRepository('SieAppWebBundle:TtecCargoDesignacionTipo')->findOneById($form['cargoDesignacion']));
            $admPersonaNew->setFechaRegistro(new \DateTime('now'));
            $admPersonaNew->setEsVigente(1);
            $em->persist($admPersonaNew);
            $em->flush();

            $em->getConnection()->commit();
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('ttec_administrativo_carrera_persona');")->execute();
            
            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente.');
            return $this->redirect($this->generateUrl('dgesttla_info_personal_adm_carr_index'));
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
        return $this->render($this->session->get('pathSystem') . ':InfoPersonalAdmCarrera:open.html.twig', array());
    }

    //Selección del Personal Administrativo
    public function esVigenteAdministrativoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $this->session = new Session();
        
        try {
            // Verificamos si no ha caducado la session
            if (!$this->session->get('userId')) {
                return $this->redirect($this->generateUrl('login'));
            }

            $gestion = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($request->get('gestion'));
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->get('idInstitucion'));
            $admPersona = $em->getRepository('SieAppWebBundle:TtecAdministrativoCarreraPersona')->findOneById($request->get('idAdmPersona'));
            
            $admPersona->setEsVigente(1 - $admPersona->getEsVigente());

            $em->persist($admPersona);
            $em->flush();
            $em->getConnection()->commit();

            return $this->redirect($this->generateUrl('dgesttla_info_personal_adm_carr_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /*
     * Llamar al formulario de edicion
     */

    public function editAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($request->get('idPersona'));
        $admPersona = $em->getRepository('SieAppWebBundle:TtecAdministrativoCarreraPersona')->findOneById($request->get('idAdmPersona'));
        $em->getConnection()->commit();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->getSession()->get('idInstitucion'));
        $carrera = $em->getRepository('SieAppWebBundle:TtecCarreraTipo')->find($request->getSession()->get('idCarrera'));

        return $this->render($this->session->get('pathSystem') . ':InfoPersonalAdmCarrera:edit.html.twig', array(
                    'form' => $this->editForm($request->getSession()->get('idInstitucion'), $carrera->getId(), $request->getSession()->get('idGestion'), $persona, $admPersona)->createView(),
                    'institucion' => $institucion,
                    'gestion' => $request->getSession()->get('idGestion'),
                    'persona' => $persona,
                    'carrera' => $carrera
        ));
    }

    /*
     * formulario de edicion
     */

    private function editForm($idInstitucion, $idCarrera, $gestion, $persona, $admPersona) {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT ct FROM SieAppWebBundle:TtecCargoTipo ct
            WHERE ct.id NOT IN (:id) ORDER BY ct.cargo')
            ->setParameter('id', array(9));

        $cargos = $query->getResult();
        $cargosArray = array();
        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getCargo();
        }

        $query = $em->createQuery('SELECT cdt FROM SieAppWebBundle:TtecCargoDesignacionTipo cdt ORDER BY cdt.formaDesignacion');

        $designacioncargos = $query->getResult();
        $designacioncargosArray = array();
        foreach ($designacioncargos as $dc) {
            $designacioncargosArray[$dc->getId()] = $dc->getFormaDesignacion();
        }

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('dgesttla_info_personal_adm_carr_update'))
                //->add('institucionEducativa', 'hidden', array('data' => $idInstitucion))
                ->add('carrera', 'hidden', array('data' => $idCarrera))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('idPersona', 'hidden', array('data' => $persona->getId()))
                ->add('idAdmPersona', 'hidden', array('data' => $admPersona->getId()))
                ->add('genero', 'entity', array('class' => 'SieAppWebBundle:GeneroTipo', 'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $persona->getGeneroTipo()->getId()), 'label' => 'Género', 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('celular', 'text', array('label' => 'Nro. de Celular', 'required' => true, 'data' => $persona->getCelular(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))
                ->add('correo', 'text', array('label' => 'Correo Electrónico', 'required' => true, 'data' => $persona->getCorreo(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))
                ->add('direccion', 'text', array('label' => 'Dirección de Domicilio', 'required' => true, 'data' => $persona->getDireccion(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('cargo', 'choice', array('label' => 'Función que desempeña (cargo)', 'required' => true, 'choices' => $cargosArray, 'data' => $admPersona->getTtecCargoTipo()->getId(), 'attr' => array('class' => 'form-control')))
                ->add('cargoDesignacion', 'choice', array('label' => 'Designación', 'required' => true, 'choices' => $designacioncargosArray, 'data' => $admPersona->getTtecCargoDesignacionTipo()->getId(), 'attr' => array('class' => 'form-control')))
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
            
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['idPersona']);
            $persona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['genero']));
            $persona->setDireccion(mb_strtoupper($form['direccion']), 'utf-8');
            $persona->setCelular($form['celular']);
            $persona->setCorreo(mb_strtolower($form['correo']), 'utf-8');
            $em->persist($persona);
            $em->flush();

            //Actualizacion en la tabla maestro inscripcion
            $admPersona = $em->getRepository('SieAppWebBundle:TtecAdministrativoCarreraPersona')->findOneById($form['idAdmPersona']);
            
            $admPersona->setTtecCargoTipo($em->getRepository('SieAppWebBundle:TtecCargoTipo')->findOneById($form['cargo']));
            $admPersona->setTtecCargoDesignacionTipo($em->getRepository('SieAppWebBundle:TtecCargoDesignacionTipo')->findOneById($form['cargoDesignacion']));
            $admPersona->setFechaModificacion(new \DateTime('now'));
            $em->persist($admPersona);
            $em->flush();

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('updateOk', 'Datos modificados correctamente.');
            return $this->redirect($this->generateUrl('dgesttla_info_personal_adm_carr_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'Error en la modificacion de datos.');
            return $this->redirect($this->generateUrl('dgesttla_info_personal_adm_carr_index'));
        }
    }

    /*
     * Eliminacion de maestro
     */

    public function deleteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $admPersona = $em->getRepository('SieAppWebBundle:TtecAdministrativoCarreraPersona')->findOneById($request->get('idAdmPersona'));

            //eliminamos el registro de inscripcion del administrativo
            $em->remove($admPersona);
            $em->flush();
            $em->getConnection()->commit();

            $this->get('session')->getFlashBag()->add('eliminarOk', 'El registro fue eliminado exitosamente.');
            return $this->redirect($this->generateUrl('dgesttla_info_personal_adm_carr_index'));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('eliminarError', 'El registro no se pudo eliminar');
            $em->getConnection()->rollback();
            return $this->redirect($this->generateUrl('dgesttla_info_personal_adm_carr_index'));
        }
    }

    public function findAction(Request $request) {

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->getSession()->get('idInstitucion'));

        return $this->render($this->session->get('pathSystem') . ':InfoPersonalAdmCarrera:search.html.twig', array(
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
                $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->getSession()->get('idInstitucion'));
                $gestion = $request->getSession()->get('idGestion');
        
                $repository = $em->getRepository('SieAppWebBundle:Persona');
        
                $query = $repository->createQueryBuilder('p')
                        ->select('p')
                        ->where('p.carnet = :carnet AND p.segipId > :segip')
                        ->setParameter('carnet', $form['carnetIdentidad'])
                        ->setParameter('segip', 0)
                        ->getQuery();
        
                $personas = $query->getResult();
        
                return $this->render($this->session->get('pathSystem') . ':InfoPersonalAdmCarrera:result.html.twig', array(
                    'personas' => $personas,
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
                ->setAction($this->generateUrl('dgesttla_info_personal_adm_carr_result'))
                ->add('carnetIdentidad', 'text', array('label' => 'Carnet de Identidad', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'pattern' => '[0-9]{5,10}', 'maxlength' => '11')))
                ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'attr' => array('class' => 'form-control jonlynumbersletters jupper', 'maxlength' => '2', 'autocomplete' => 'off')))
                ->add('buscar', 'submit', array('label' => 'Buscar coincidencias por C.I.', 'attr' => array('class' => 'btn btn-md btn-facebook')))
                ->getForm();
        return $form;
    }

    public function indexTecSieAction(Request $request) {
        //get the session's values
        $this->session = $request->getSession();

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render($this->session->get('pathSystem') . ':InfoPersonalAdmCarrera:index_tec_sie.html.twig', array(
                'form' => $this->formSearch($request->getSession()->get('currentyear'))->createView(),
        ));
        
    }

    /*
     * formularios de busqueda de institucion educativa
     */

    private function formSearch($gestionactual) {
        
        $gestiones = array($gestionactual => $gestionactual, $gestionactual - 1 => $gestionactual - 1, $gestionactual - 2 => $gestionactual - 2, $gestionactual - 3 => $gestionactual - 3, $gestionactual - 4 => $gestionactual - 4, $gestionactual - 5 => $gestionactual - 5);

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('dgesttla_info_personal_adm_carr_index'))
                ->add('sie', 'text', array('required' => true, 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    public function admHabilitarDDJJAction(Request $request) {
        $form = $request->get('ddjjAdmHabilitar');

        $sie = $form['sie'];
        $gestion = $form['gestion'];

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($sie, $gestion);
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($sie);
        $gestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion);
        $notaTipo = $em->getRepository('SieAppWebBundle:NotaTipo')->findOneById($operativo);
        $rolTipo = $em->getRepository('SieAppWebBundle:RolTipo')->findOneById(9);

        $validacion_personal_aux = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoValidacionpersonal')->findOneBy(array('gestionTipo' => $gestionTipo, 'institucioneducativa' => $sie, 'notaTipo' => $notaTipo, 'rolTipo' => $rolTipo));

        try{
            $em->remove($validacion_personal_aux);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('eliminarOk', 'Se activó satisfactoriamente el operativo.');
            return $this->redirect($this->generateUrl('dgesttla_info_personal_adm_carr_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('eliminarError', 'Error en la habilitación del operativo');
            return $this->redirect($this->generateUrl('dgesttla_info_personal_adm_carr_index'));
        }
    }

}
