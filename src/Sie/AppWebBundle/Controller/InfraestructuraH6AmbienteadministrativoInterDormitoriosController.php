<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoInterDormitorios;
use Sie\AppWebBundle\Form\InfraestructuraH6AmbienteadministrativoInterDormitoriosType;

/**
 * InfraestructuraH6AmbienteadministrativoInterDormitorios controller.
 *
 */
class InfraestructuraH6AmbienteadministrativoInterDormitoriosController extends Controller
{

    /**
     * Lists all InfraestructuraH6AmbienteadministrativoInterDormitorios entities.
     *
     */
    public function indexAction(Request $request){dump($request);die;
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios')->findAll();

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new InfraestructuraH6AmbienteadministrativoInterDormitorios entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH6AmbienteadministrativoInterDormitorios();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('infrah6interdormitorios_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a InfraestructuraH6AmbienteadministrativoInterDormitorios entity.
     *
     * @param InfraestructuraH6AmbienteadministrativoInterDormitorios $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH6AmbienteadministrativoInterDormitorios $entity)
    {
        $form = $this->createForm(new InfraestructuraH6AmbienteadministrativoInterDormitoriosType(), $entity, array(
            'action' => $this->generateUrl('infrah6interdormitorios_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH6AmbienteadministrativoInterDormitorios entity.
     *
     */
    public function newAction(Request $request){

        dump($request);die;
        $entity = new InfraestructuraH6AmbienteadministrativoInterDormitorios();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a InfraestructuraH6AmbienteadministrativoInterDormitorios entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoInterDormitorios entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH6AmbienteadministrativoInterDormitorios entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoInterDormitorios entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a InfraestructuraH6AmbienteadministrativoInterDormitorios entity.
    *
    * @param InfraestructuraH6AmbienteadministrativoInterDormitorios $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH6AmbienteadministrativoInterDormitorios $entity)
    {
        $form = $this->createForm(new InfraestructuraH6AmbienteadministrativoInterDormitoriosType(), $entity, array(
            'action' => $this->generateUrl('infrah6interdormitorios_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH6AmbienteadministrativoInterDormitorios entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoInterDormitorios entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('infrah6interdormitorios_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a InfraestructuraH6AmbienteadministrativoInterDormitorios entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoInterDormitorios entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('infrah6interdormitorios'));
    }

    /**
     * Creates a form to delete a InfraestructuraH6AmbienteadministrativoInterDormitorios entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('infrah6interdormitorios_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
