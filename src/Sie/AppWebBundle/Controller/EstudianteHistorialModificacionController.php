<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\EstudianteHistorialModificacion;
use Sie\AppWebBundle\Form\EstudianteHistorialModificacionType;

/**
 * EstudianteHistorialModificacion controller.
 *
 */
class EstudianteHistorialModificacionController extends Controller{

    public $session;
    public $currentyear;
    public $userlogged;
     /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->currentyear = $this->session->get('currentyear');
        $this->userlogged = $this->session->get('userId');
    }

    /**
     * Lists all EstudianteHistorialModificacion entities.
     *
     */
    public function indexAction(){
        //validation if the user is logged
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:EstudianteHistorialModificacion')->findBy(array('usuario'=>$this->session->get('userId')));

        return $this->render('SieAppWebBundle:EstudianteHistorialModificacion:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new EstudianteHistorialModificacion entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new EstudianteHistorialModificacion();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('estudiantehistorialmodificacion_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:EstudianteHistorialModificacion:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a EstudianteHistorialModificacion entity.
     *
     * @param EstudianteHistorialModificacion $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(EstudianteHistorialModificacion $entity)
    {
        $form = $this->createForm(new EstudianteHistorialModificacionType(), $entity, array(
            'action' => $this->generateUrl('estudiantehistorialmodificacion_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new EstudianteHistorialModificacion entity.
     *
     */
    public function newAction()
    {
        $entity = new EstudianteHistorialModificacion();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:EstudianteHistorialModificacion:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a EstudianteHistorialModificacion entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:EstudianteHistorialModificacion')->find($id);
        //  dump(json_decode( $entity->getDatoAnterior(),true));die;
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find EstudianteHistorialModificacion entity.');
        }
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($entity->getEstudiante()->getId());
// dump($objStudent);die;
        // $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:EstudianteHistorialModificacion:show.html.twig', array(
            'entity'         => $entity,
            'objStudent'     => $objStudent,
            'oldDataStudent' => json_decode( $entity->getDatoAnterior(),true),
            // 'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing EstudianteHistorialModificacion entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:EstudianteHistorialModificacion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find EstudianteHistorialModificacion entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:EstudianteHistorialModificacion:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a EstudianteHistorialModificacion entity.
    *
    * @param EstudianteHistorialModificacion $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(EstudianteHistorialModificacion $entity)
    {
        $form = $this->createForm(new EstudianteHistorialModificacionType(), $entity, array(
            'action' => $this->generateUrl('estudiantehistorialmodificacion_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing EstudianteHistorialModificacion entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:EstudianteHistorialModificacion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find EstudianteHistorialModificacion entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('estudiantehistorialmodificacion_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:EstudianteHistorialModificacion:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a EstudianteHistorialModificacion entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:EstudianteHistorialModificacion')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find EstudianteHistorialModificacion entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('estudiantehistorialmodificacion'));
    }

    /**
     * Creates a form to delete a EstudianteHistorialModificacion entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('estudiantehistorialmodificacion_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
