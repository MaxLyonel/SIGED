<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\SistemaTipo;
use Sie\AppWebBundle\Form\SistemaTipoType;

/**
 * SistemaTipo controller.
 *
 */
class SistemaTipoController extends Controller
{

    /**
     * Lists all SistemaTipo entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:SistemaTipo')->findAll();

        return $this->render('SieAppWebBundle:SistemaTipo:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new SistemaTipo entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new SistemaTipo();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('sistematipo_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:SistemaTipo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a SistemaTipo entity.
     *
     * @param SistemaTipo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(SistemaTipo $entity)
    {
        $form = $this->createForm(new SistemaTipoType(), $entity, array(
            'action' => $this->generateUrl('sistematipo_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new SistemaTipo entity.
     *
     */
    public function newAction()
    {
        $entity = new SistemaTipo();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:SistemaTipo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a SistemaTipo entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:SistemaTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SistemaTipo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:SistemaTipo:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing SistemaTipo entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:SistemaTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SistemaTipo entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:SistemaTipo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a SistemaTipo entity.
    *
    * @param SistemaTipo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(SistemaTipo $entity)
    {
        $form = $this->createForm(new SistemaTipoType(), $entity, array(
            'action' => $this->generateUrl('sistematipo_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing SistemaTipo entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:SistemaTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SistemaTipo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('sistematipo_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:SistemaTipo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a SistemaTipo entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:SistemaTipo')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find SistemaTipo entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('sistematipo'));
    }

    /**
     * Creates a form to delete a SistemaTipo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('sistematipo_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
