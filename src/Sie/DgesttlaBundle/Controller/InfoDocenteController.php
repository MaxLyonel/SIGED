<?php

namespace Sie\DgesttlaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\TtecDocentePersona;
use GuzzleHttp\Client;

/**
 * EstudianteInscripcion controller.
 *
 */
class InfoDocenteController extends Controller {

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
                $this->get('session')->getFlashBag()->add('noSearch', 'El código ingresado no es válido');
                return $this->redirect($this->generateUrl('dgesttla_info_docente_index'));
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
                return $this->redirect($this->generateUrl('dgesttla_info_docente_index'));
            }
        } else {
            $institucion = $request->getSession()->get('idInstitucion');
            $gestion = $request->getSession()->get('idGestion');
        }

        
        $repository = $em->getRepository('SieAppWebBundle:TtecDocentePersona');

        $query = $repository->createQueryBuilder('tdp')
                ->select('p.id perId, p.carnet, p.paterno, p.materno, p.nombre, tdp.id tdpId, tdp.fechaRegistro, tdp.fechaModificacion, tdp.esVigente')
                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'tdp.persona = p.id')
                ->where('tdp.institucioneducativa = :idInstitucion')
                ->setParameter('idInstitucion', $institucion)
                ->distinct()
                ->orderBy('p.paterno')
                ->addOrderBy('p.materno')
                ->addOrderBy('p.nombre')
                ->getQuery();

        $docente = $query->getResult();

        /*
         * obtenemos datos de la institución educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        $gestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion);

        return $this->render($this->session->get('pathSystem') . ':InfoDocente:index.html.twig', array(
                'docente' => $docente,
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
                ->setAction($this->generateUrl('dgesttla_info_docente_result'))
                ->add('carnetIdentidad', 'text', array('label' => 'Carnet de Identidad', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'pattern' => '[0-9]{5,10}', 'maxlength' => '11')))
                ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'attr' => array('class' => 'form-control jonlynumbersletters jupper', 'maxlength' => '2', 'autocomplete' => 'off')))
                ->add('buscar', 'submit', array('label' => 'Buscar coincidencias', 'attr' => array('class' => 'btn btn-md btn-facebook')))
                ->getForm();
        return $form;
    }

    public function findAction(Request $request) {
        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->getSession()->get('idInstitucion'));

        return $this->render($this->session->get('pathSystem') . ':InfoDocente:search.html.twig', array(
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

        return $this->render($this->session->get('pathSystem') . ':InfoDocente:result.html.twig', array(
            'personas' => $personas,
            'institucion' => $institucion,
            'gestion' => $gestion
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

        return $this->render($this->session->get('pathSystem') . ':InfoDocente:new.html.twig', array(
                    'form' => $this->newForm($form['idInstitucion'], $form['gestion'], $form['idPersona'])->createView(),
                    'institucion' => $institucion,
                    'gestion' => $request->getSession()->get('idGestion'),
                    'persona' => $persona
        ));
    }

    /*
     * formulario de nueva/o docente
     */
    private function newForm($idInstitucion, $gestion, $idPersona) {
        $em = $this->getDoctrine()->getManager();

        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($idPersona);

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('dgesttla_info_docente_create'))
            ->add('institucionEducativa', 'hidden', array('data' => $idInstitucion))
            ->add('gestion', 'hidden', array('data' => $gestion))
            ->add('persona', 'hidden', array('data' => $idPersona))
            ->add('genero', 'entity', array('class' => 'SieAppWebBundle:GeneroTipo', 'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $persona->getGeneroTipo()->getId()), 'label' => 'Género', 'required' => true, 'attr' => array('class' => 'form-control')))
            ->add('celular', 'text', array('label' => 'Nro. de Celular', 'data' => $persona->getCelular(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))
            ->add('correo', 'text', array('label' => 'Correo Electrónico', 'data' => $persona->getCorreo(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))
            ->add('direccion', 'text', array('label' => 'Dirección de Domicilio', 'data' => $persona->getDireccion(), 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
            ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();
            
        return $form;
    }

    /*
     * registramos la/el nueva/o docente
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

            $docentePersona = $em->getRepository('SieAppWebBundle:TtecDocentePersona')->findOneBy(array('institucioneducativa' => $form['institucionEducativa'], 'persona' => $personaId));
            // verificamos si la/el docente ya fue registrada/o
            if ($docentePersona) {
                $this->get('session')->getFlashBag()->add('newError', 'No se realizó el registro, la persona ya se encuentra registrada.');
                return $this->redirect($this->generateUrl('dgesttla_info_docente_index'));
            }

            // Registro Docente Persona
            $docentePersonaNew = new TtecDocentePersona();
            $docentePersonaNew->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucionEducativa']));
            $docentePersonaNew->setPersona($persona);
            $docentePersonaNew->setFechaRegistro(new \DateTime('now'));
            $docentePersonaNew->setEsVigente(1);
            $em->persist($docentePersonaNew);
            $em->flush();
            
            $em->getConnection()->commit();
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('ttec_docente_persona');")->execute();

            $this->get('session')->getFlashBag()->add('newOk', 'Los datos fueron registrados correctamente.');
            return $this->redirect($this->generateUrl('dgesttla_info_docente_index'));
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
        $docentePersona = $em->getRepository('SieAppWebBundle:TtecDocentePersona')->findOneById($request->get('idDocentePersona'));
        $em->getConnection()->commit();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($request->getSession()->get('idInstitucion'));

        return $this->render($this->session->get('pathSystem') . ':InfoDocente:edit.html.twig', array(
                    'form' => $this->editForm($request->getSession()->get('idInstitucion'), $request->getSession()->get('idGestion'), $persona, $docentePersona)->createView(),
                    'institucion' => $institucion,
                    'gestion' => $request->getSession()->get('idGestion'),
                    'persona' => $persona
        ));
    }

    /*
     * formulario de edicion
     */

    private function editForm($idInstitucion, $gestion, $persona, $docentePersona) {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('dgesttla_info_docente_update'))
                ->add('institucionEducativa', 'hidden', array('data' => $idInstitucion))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('idPersona', 'hidden', array('data' => $persona->getId()))
                ->add('idDocentePersona', 'hidden', array('data' => $docentePersona->getId()))
                ->add('genero', 'entity', array('class' => 'SieAppWebBundle:GeneroTipo', 'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $persona->getGeneroTipo()->getId()), 'label' => 'Género', 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('celular', 'text', array('label' => 'Nro. de Celular', 'required' => true, 'data' => $persona->getCelular(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jcell', 'pattern' => '[0-9]{8}')))
                ->add('correo', 'text', array('label' => 'Correo Electrónico', 'required' => true, 'data' => $persona->getCorreo(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jemail')))
                ->add('direccion', 'text', array('label' => 'Dirección de Domicilio', 'required' => true, 'data' => $persona->getDireccion(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbersletters jupper')))
                ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

        return $form;
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

            //Actualizacion en la tabla TtecDocentePersona
            $docentePersona = $em->getRepository('SieAppWebBundle:TtecDocentePersona')->findOneById($form['idDocentePersona']);

            $docentePersona->setFechaModificacion(new \DateTime('now'));
            $em->persist($docentePersona);
            $em->flush();

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('updateOk', 'Datos modificados correctamente');
            return $this->redirect($this->generateUrl('dgesttla_info_docente_index', array('op' => 'result')));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'Error en la modificacion de datos');
            return $this->redirect($this->generateUrl('dgesttla_info_docente_index', array('op' => 'result')));
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
     * Eliminacion de maestro
     */

    public function deleteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        
        try {
            $docentePersona = $em->getRepository('SieAppWebBundle:TtecDocentePersona')->findOneById($request->get('idDocentePersona'));            
            //eliminamos el registro de inscripcion del maestro
            $em->remove($docentePersona);
            $em->flush();
            $em->getConnection()->commit();
            
            $this->get('session')->getFlashBag()->add('eliminarOk', 'El registro fue eliminado exitosamente.');
            return $this->redirect($this->generateUrl('dgesttla_info_docente_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('eliminarError', 'El registro no se pudo eliminar.');
            return $this->redirect($this->generateUrl('dgesttla_info_docente_index'));
        }
    }

    /* funcion para verificar si el maestro ya existe
      con el carnet, metodo ajax */

    public function verificar_existe_personaAction($carnet) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
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

    //Selección del Personal Administrativo
    public function esVigenteDocenteAction(Request $request) {
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
            $docentePersona = $em->getRepository('SieAppWebBundle:TtecDocentePersona')->findOneBy(array('id' => $request->get('idDocentePersona'), 'institucioneducativa' => $institucion));

            $docentePersona->setEsVigente(1 - $docentePersona->getEsVigente());

            $em->persist($docentePersona);
            $em->flush();
            $em->getConnection()->commit();

            return $this->redirect($this->generateUrl('dgesttla_info_docente_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    public function indexTecSieAction(Request $request) {
        //get the session's values
        $this->session = $request->getSession();

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render($this->session->get('pathSystem') . ':InfoDocente:index_tec_sie.html.twig', array(
                'form' => $this->formSearch($request->getSession()->get('currentyear'))->createView(),
        ));

    }

    /*
     * formularios de busqueda de institucion educativa
     */

    private function formSearch($gestionactual) {

        $gestiones = array($gestionactual => $gestionactual, $gestionactual - 1 => $gestionactual - 1, $gestionactual - 2 => $gestionactual - 2, $gestionactual - 3 => $gestionactual - 3, $gestionactual - 4 => $gestionactual - 4, $gestionactual - 5 => $gestionactual - 5);

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('dgesttla_info_docente_index'))
                ->add('sie', 'text', array('required' => true, 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    public function maestroHabilitarDDJJAction(Request $request) {
        $form = $request->get('ddjjMaestroHabilitar');

        $sie = $form['sie'];
        $gestion = $form['gestion'];

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($sie, $gestion);
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($sie);
        $gestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion);
        $notaTipo = $em->getRepository('SieAppWebBundle:NotaTipo')->findOneById($operativo);
        $rolTipo = $em->getRepository('SieAppWebBundle:RolTipo')->findOneById(2);

        $validacion_personal_aux = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoValidacionpersonal')->findOneBy(array('gestionTipo' => $gestionTipo, 'institucioneducativa' => $sie, 'notaTipo' => $notaTipo, 'rolTipo' => $rolTipo));

        try{
            $em->remove($validacion_personal_aux);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('eliminarOk', 'Se activó satisfactoriamente el operativo.');
            return $this->redirect($this->generateUrl('dgesttla_info_docente_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('eliminarError', 'Error en la habilitación del operativo');
            return $this->redirect($this->generateUrl('dgesttla_info_docente_index'));
        }
    }

}
