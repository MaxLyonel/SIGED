<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\AreaTipo;
use Sie\AppWebBundle\Form\AreaTipoType;

/**
 * AreaTipo controller.
 *
 */
class AreaTipoController extends Controller
{

    /**
     * Lists all AreaTipo entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:AreaTipo')->findAll();

        return $this->render('SieAppWebBundle:AreaTipo:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new AreaTipo entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new AreaTipo();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('areatipo_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:AreaTipo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a AreaTipo entity.
     *
     * @param AreaTipo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(AreaTipo $entity)
    {
        $form = $this->createForm(new AreaTipoType(), $entity, array(
            'action' => $this->generateUrl('areatipo_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new AreaTipo entity.
     *
     */
    public function newAction()
    {
        $entity = new AreaTipo();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:AreaTipo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a AreaTipo entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:AreaTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AreaTipo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:AreaTipo:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing AreaTipo entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:AreaTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AreaTipo entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:AreaTipo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a AreaTipo entity.
    *
    * @param AreaTipo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(AreaTipo $entity)
    {
        $form = $this->createForm(new AreaTipoType(), $entity, array(
            'action' => $this->generateUrl('areatipo_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing AreaTipo entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:AreaTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AreaTipo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('areatipo_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:AreaTipo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a AreaTipo entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:AreaTipo')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find AreaTipo entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('areatipo'));
    }

    /**
     * Creates a form to delete a AreaTipo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('areatipo_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
