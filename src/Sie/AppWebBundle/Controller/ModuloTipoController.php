<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\ModuloTipo;
use Sie\AppWebBundle\Form\ModuloTipoType;

/**
 * ModuloTipo controller.
 *
 */
class ModuloTipoController extends Controller
{

    /**
     * Lists all ModuloTipo entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:ModuloTipo')->findAll();

        return $this->render('SieAppWebBundle:ModuloTipo:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new ModuloTipo entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new ModuloTipo();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('modulotipo_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:ModuloTipo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a ModuloTipo entity.
     *
     * @param ModuloTipo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(ModuloTipo $entity)
    {
        $form = $this->createForm(new ModuloTipoType(), $entity, array(
            'action' => $this->generateUrl('modulotipo_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new ModuloTipo entity.
     *
     */
    public function newAction()
    {
        $entity = new ModuloTipo();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:ModuloTipo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a ModuloTipo entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:ModuloTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ModuloTipo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:ModuloTipo:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing ModuloTipo entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:ModuloTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ModuloTipo entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:ModuloTipo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a ModuloTipo entity.
    *
    * @param ModuloTipo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(ModuloTipo $entity)
    {
        $form = $this->createForm(new ModuloTipoType(), $entity, array(
            'action' => $this->generateUrl('modulotipo_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing ModuloTipo entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:ModuloTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ModuloTipo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('modulotipo_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:ModuloTipo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a ModuloTipo entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:ModuloTipo')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ModuloTipo entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('modulotipo'));
    }

    /**
     * Creates a form to delete a ModuloTipo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('modulotipo_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
