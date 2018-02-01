<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\InfraestructuraH4ServicioVestuarios;
use Sie\AppWebBundle\Form\InfraestructuraH4ServicioVestuariosType;

/**
 * InfraestructuraH4ServicioVestuarios controller.
 *
 */
class InfraestructuraH4ServicioVestuariosController extends Controller
{

    /**
     * Lists all InfraestructuraH4ServicioVestuarios entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH4ServicioVestuarios')->findAll();

        return $this->render('SieAppWebBundle:InfraestructuraH4ServicioVestuarios:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new InfraestructuraH4ServicioVestuarios entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH4ServicioVestuarios();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('infraestructurah4serviciovestuarios_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH4ServicioVestuarios:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a InfraestructuraH4ServicioVestuarios entity.
     *
     * @param InfraestructuraH4ServicioVestuarios $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH4ServicioVestuarios $entity)
    {
        $form = $this->createForm(new InfraestructuraH4ServicioVestuariosType(), $entity, array(
            'action' => $this->generateUrl('infraestructurah4serviciovestuarios_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH4ServicioVestuarios entity.
     *
     */
    public function newAction()
    {
        $entity = new InfraestructuraH4ServicioVestuarios();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:InfraestructuraH4ServicioVestuarios:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a InfraestructuraH4ServicioVestuarios entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH4ServicioVestuarios')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH4ServicioVestuarios entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH4ServicioVestuarios:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH4ServicioVestuarios entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH4ServicioVestuarios')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH4ServicioVestuarios entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH4ServicioVestuarios:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a InfraestructuraH4ServicioVestuarios entity.
    *
    * @param InfraestructuraH4ServicioVestuarios $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH4ServicioVestuarios $entity)
    {
        $form = $this->createForm(new InfraestructuraH4ServicioVestuariosType(), $entity, array(
            'action' => $this->generateUrl('infraestructurah4serviciovestuarios_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH4ServicioVestuarios entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH4ServicioVestuarios')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH4ServicioVestuarios entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('infraestructurah4serviciovestuarios_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH4ServicioVestuarios:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a InfraestructuraH4ServicioVestuarios entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH4ServicioVestuarios')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InfraestructuraH4ServicioVestuarios entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('infraestructurah4serviciovestuarios'));
    }

    /**
     * Creates a form to delete a InfraestructuraH4ServicioVestuarios entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('infraestructurah4serviciovestuarios_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
