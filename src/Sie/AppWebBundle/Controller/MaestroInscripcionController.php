<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\MaestroInscripcion;
use Sie\AppWebBundle\Form\MaestroInscripcionType;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Form\PersonaType;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * MaestroInscripcion controller.
 *
 */
class MaestroInscripcionController extends Controller {

    /**
     * Lists all MaestroInscripcion entities.
     * @return object form
     */
    public function indexAction() {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('sie_login_homepage'));
        }
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findAll();

        return $this->render('SieAppWebBundle:MaestroInscripcion:index.html.twig', array(
                    'entities' => $entities,
        ));
    }

    /**
     * Creates a new MaestroInscripcion entity.
     *
     */
    public function createAction(Request $request) {

        //$entity = new MaestroInscripcion();
        //$form = $this->createCreateForm($entity);
        //$form->handleRequest($request);
        try {
            $form = $request->get('form');
            //print_r($form);die;
            $em = $this->getDoctrine()->getManager();
            $persona = new Persona();
            $persona->setCarnet($form['carnet']);
            $persona->setRda(0);
            $persona->setLibretaMilitar('');
            $persona->setPasaporte('');
            $persona->setPaterno($form['paterno']);
            $persona->setMaterno($form['materno']);
            $persona->setNombre($form['nombre']);
            $persona->setFechaNacimiento(new \DateTime('now'));
            $persona->setSegipId(0);
            $persona->setComplemento('');
            $persona->setActivo('t');
            $idioma = $em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneById($form['idiomaMaterno']);
            $persona->setIdiomaMaterno($idioma);
            $persona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['generoTipo']));
            $persona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById($form['sangreTipo']));
            $persona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findOneById($form['estadocivilTipo']));

            $em->persist($persona);
            $em->flush($persona);
            //registro maestro inscipcion

            $maestroInscipcion = new MaestroInscripcion();
            $maestroInscipcion->setRdaPlanillasId('0');
            $maestroInscipcion->setRef('2259035');
            $maestroInscipcion->setIdioma('castellano');
            $maestroInscipcion->setLee('si');
            $maestroInscipcion->setHabla('si');
            $maestroInscipcion->setEscribe('si');
            $maestroInscipcion->setEstudia('si');
            $maestroInscipcion->setCargoTipo($em->getRepository('SieAppWebBundle:CargoTipo')->findOneById($form['cargoTipo']));
            $maestroInscipcion->setEspecialidadTipo($em->getRepository('SieAppWebBundle:EspecialidadMaestroTipo')->findOneById($form['especialidadTipo']));
            $maestroInscipcion->setFinanciamientoTipo($em->getRepository('SieAppWebBundle:FinanciamientoTipo')->findOneById($form['financiamientoTipo']));
            $maestroInscipcion->setFormacionTipo($em->getRepository('SieAppWebBundle:FormacionTipo')->findOneById($form['formacionTipo']));
            $maestroInscipcion->setPersona($em->getRepository('SieAppWebBundle:Persona')->findOneById($persona->getId()));
            $maestroInscipcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($form['gestionTipo']));
            $maestroInscipcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']));
            $maestroInscipcion->setInstitucioneducativaSucursalI($em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneById($form['institucioneducativaSucursalI']));
            $maestroInscipcion->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneById($form['periodoTipo']));
            $maestroInscipcion->setEstadomaestro($em->getRepository('SieAppWebBundle:EstadomaestroTipo')->findOneById($form['estadomaestro']));

            $em->persist($maestroInscipcion);
            $em->flush();

            return $this->redirect($this->generateUrl('institucioneducativa_view'));
        } catch (Exception $ex) {
            //echo "error";die;
            //echo $ex-> getMessage();
            throw $this->createNotFoundException('No se realizo el registro');
        }
    }

    /**
     * build a form to create a MaestroInscripcion entity.
     *
     * @param MaestroInscripcion $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(MaestroInscripcion $entity) {
        $form = $this->createForm(new MaestroInscripcionType(), $entity, array(
            'action' => $this->generateUrl('maestroinscripcion_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    private function createPersonalForm($sie, $entity) {
        $em = $this->getDoctrine()->getManager();

        return $this->createFormBuilder($entity)
                        ->setAction($this->generateUrl('maestroinscripcion_create'))
                        ->add('carnet', 'text', array("mapped" => false, 'required' => 'required', 'disabled' => false))
                        ->add('paterno', 'text', array("mapped" => false, 'required' => 'required', 'disabled' => false))
                        ->add('materno', 'text', array("mapped" => false, 'required' => 'required', 'disabled' => false))
                        ->add('nombre', 'text', array("mapped" => false, 'required' => 'required', 'disabled' => false))
                        ->add('idiomaMaterno', 'entity', array("mapped" => false, 'class' => 'SieAppWebBundle:IdiomaMaterno', 'property' => 'idiomaMaterno'))
                        ->add('generoTipo', 'entity', array('mapped' => false, 'class' => 'SieAppWebBundle:GeneroTipo', 'property' => 'genero'))
                        ->add('sangreTipo', 'entity', array("mapped" => false, 'class' => 'SieAppWebBundle:SangreTipo', 'property' => 'grupoSanguineo'))
                        ->add('estadocivilTipo', 'entity', array("mapped" => false, 'class' => 'SieAppWebBundle:EstadoCivilTipo', 'property' => 'estadoCivil'))
                        ->add('cargoTipo', 'entity', array('class' => 'SieAppWebBundle:CargoTipo', 'property' => 'cargo'))
                        ->add('especialidadTipo', 'entity', array('class' => 'SieAppWebBundle:EspecialidadMaestroTipo', 'property' => 'especialidad'))
                        ->add('financiamientoTipo', 'entity', array('class' => 'SieAppWebBundle:FinanciamientoTipo', 'property' => 'financiamiento'))
                        ->add('formacionTipo', 'entity', array('class' => 'SieAppWebBundle:FormacionTipo', 'property' => 'formacion'))
                        ->add('gestionTipo', 'hidden', array('data' => '2015'))
                        ->add('institucioneducativa', 'hidden', array('data' => $sie))
                        ->add('institucioneducativaSucursalI', 'hidden', array("mapped" => false, 'data' => '15845'))
                        ->add('periodoTipo', 'entity', array('class' => 'SieAppWebBundle:PeriodoTipo', 'property' => 'periodo'))
                        ->add('estadomaestro', 'entity', array('class' => 'SieAppWebBundle:EstadomaestroTipo', 'property' => 'descripcion'))
                        ->add('aceptar', 'submit', array('label' => 'Aceptar'))
                        ->getForm();
    }

    /**
     * Displays a form to create a new MaestroInscripcion entity.
     *
     */
    public function newAction(Request $request) {
        $codsie = $request->get('idInstitucion');
        $entity = new MaestroInscripcion();
        $form = $this->createPersonalForm($codsie, $entity);
        return $this->render('SieAppWebBundle:MaestroInscripcion:new.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a MaestroInscripcion entity.
     *
     */
    public function showAction(Request $request) {

        //echo $request->get('idPersona');
        $em = $this->getDoctrine()->getManager();
        $person = $em->getRepository('SieAppWebBundle:Persona')->find($request->get('idPersona'));

        $edit_form = $this->createForm(new PersonaType(), $person, array(
            'action' => $this->generateUrl('maestroinscripcion_update', array('id' => $request->get('idPersona'))),
            'method' => 'POST',
        ));

        return $this->render('SieAppWebBundle:MaestroInscripcion:show.html.twig', array(
                    'person' => $person,
                    'edit_form' => $edit_form->createView(),
        ));
//        print_R($person);
//        die;

        /*
          $id = $request->get('');
          $em = $this->getDoctrine()->getManager();

          $entity = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($id);

          if (!$entity) {
          throw $this->createNotFoundException('Unable to find MaestroInscripcion entity.');
          }

          $deleteForm = $this->createDeleteForm($id); */
    }

    /**
     * Deletes a MaestroInscripcion entity.
     *
     */
    public function deleteAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($request->get('maestroInsId'));

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MaestroInscripcion entity.');
        }
        $em->remove($entity);
        $em->flush();

        //return $this->redirect($this->generateUrl('maestroinscripcion'));
        return $this->redirect($this->generateUrl('institucioneducativa_view'));
    }

    /**
     * Deletes a MaestroInscripcion entity.
     *
     */
    public function delete1Action(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($request->get('maestroInsId'));

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MaestroInscripcion entity.');
        }
        $em->remove($entity);
        $em->flush();

        //return $this->redirect($this->generateUrl('maestroinscripcion'));
        return $this->redirect($this->generateUrl('institucioneducativa_view'));
    }

    /**
     * Displays a form to edit an existing MaestroInscripcion entity.
     *
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MaestroInscripcion entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:MaestroInscripcion:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a MaestroInscripcion entity.
     *
     * @param MaestroInscripcion $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm1(MaestroInscripcion $entity) {
        $form = $this->createForm(new MaestroInscripcionType(), $entity, array(
            'action' => $this->generateUrl('maestroinscripcion_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Creates a form to edit a Persona entity.
     *
     * @param Persona $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Persona $entity) {
        $form = $this->createForm(new PersonaType(), $entity, array(
            'action' => $this->generateUrl('persona_main_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar Cambios'));

        return $form;
    }

    /**
     * Edits an existing Persona entity.
     *
     */
    public function updateAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Persona')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No se encontro el registro');
        }
        $form = $request->get('sie_appwebbundle_persona');

        $entity->setCarnet($form['carnet']);
        $entity->setLibretaMilitar($form['libretaMilitar']);
        $entity->setPasaporte($form['pasaporte']);
        $entity->setPaterno($form['paterno']);
        $entity->setMaterno($form['materno']);
        $entity->setNombre($form['nombre']);
        $entity->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
        $entity->setComplemento($form['complemento']);
        $entity->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneByid($form['generoTipo']));
        $entity->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findOneByid($form['estadocivilTipo']));
        $entity->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneByid($form['sangreTipo']));
        $entity->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneByid($form['idiomaMaterno']));
        $em->persist($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('msgUpdate', 'Datos Actualizados');
        return $this->redirect($this->generateUrl('institucioneducativa_view'));
    }

    /**
     * Edits an existing MaestroInscripcion entity.
     *
     */
    public function updateAction1(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MaestroInscripcion entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('maestroinscripcion_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:MaestroInscripcion:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to delete a MaestroInscripcion entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('maestroinscripcion_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

}
