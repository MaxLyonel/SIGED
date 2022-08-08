<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\OperativoSieTipo;
use Sie\AppWebBundle\Form\OperativoSieTipoType;

/**
 * OperativoSieTipo controller.
 *
 */
class OperativoSieTipoController extends Controller
{

    /**
     * Lists all OperativoSieTipo entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:OperativoSieTipo')->findAll();

        return $this->render('SieAppWebBundle:OperativoSieTipo:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new OperativoSieTipo entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new OperativoSieTipo();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('operativesie_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:OperativoSieTipo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a OperativoSieTipo entity.
     *
     * @param OperativoSieTipo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(OperativoSieTipo $entity)
    {
        $form = $this->createForm(new OperativoSieTipoType(), $entity, array(
            'action' => $this->generateUrl('operativesie_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new OperativoSieTipo entity.
     *
     */
    public function newAction()
    {
        $entity = new OperativoSieTipo();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:OperativoSieTipo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a OperativoSieTipo entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OperativoSieTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OperativoSieTipo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:OperativoSieTipo:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing OperativoSieTipo entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OperativoSieTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OperativoSieTipo entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:OperativoSieTipo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a OperativoSieTipo entity.
    *
    * @param OperativoSieTipo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(OperativoSieTipo $entity)
    {
        $form = $this->createForm(new OperativoSieTipoType(), $entity, array(
            'action' => $this->generateUrl('operativesie_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing OperativoSieTipo entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OperativoSieTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OperativoSieTipo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('operativesie_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:OperativoSieTipo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a OperativoSieTipo entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:OperativoSieTipo')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find OperativoSieTipo entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('operativesie'));
    }

    /**
     * Creates a form to delete a OperativoSieTipo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('operativesie_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
