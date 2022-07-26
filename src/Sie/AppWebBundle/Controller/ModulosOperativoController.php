<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\ModulosOperativo;
use Sie\AppWebBundle\Form\ModulosOperativoType;

/**
 * ModulosOperativo controller.
 *
 */
class ModulosOperativoController extends Controller
{

    /**
     * Lists all ModulosOperativo entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:ModulosOperativo')->findAll();

        return $this->render('SieAppWebBundle:ModulosOperativo:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new ModulosOperativo entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new ModulosOperativo();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('modulesoperative_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:ModulosOperativo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a ModulosOperativo entity.
     *
     * @param ModulosOperativo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(ModulosOperativo $entity)
    {
        $form = $this->createForm(new ModulosOperativoType(), $entity, array(
            'action' => $this->generateUrl('modulesoperative_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new ModulosOperativo entity.
     *
     */
    public function newAction()
    {
        $entity = new ModulosOperativo();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:ModulosOperativo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a ModulosOperativo entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:ModulosOperativo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ModulosOperativo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:ModulosOperativo:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing ModulosOperativo entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:ModulosOperativo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ModulosOperativo entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:ModulosOperativo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a ModulosOperativo entity.
    *
    * @param ModulosOperativo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(ModulosOperativo $entity)
    {
        $form = $this->createForm(new ModulosOperativoType(), $entity, array(
            'action' => $this->generateUrl('modulesoperative_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing ModulosOperativo entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:ModulosOperativo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ModulosOperativo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('modulesoperative_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:ModulosOperativo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a ModulosOperativo entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:ModulosOperativo')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ModulosOperativo entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('modulesoperative'));
    }

    /**
     * Creates a form to delete a ModulosOperativo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('modulesoperative_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
