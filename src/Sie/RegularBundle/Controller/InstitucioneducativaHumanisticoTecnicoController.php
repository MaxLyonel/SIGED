<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico;
use Sie\AppWebBundle\Form\InstitucioneducativaHumanisticoTecnicoType;

/**
 * InstitucioneducativaHumanisticoTecnico controller.
 *
 */
class InstitucioneducativaHumanisticoTecnicoController extends Controller {

    /**
     * Lists all InstitucioneducativaHumanisticoTecnico entities.
     *
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findAll();

        return $this->render('SieRegularBundle:InstitucioneducativaHumanisticoTecnico:index.html.twig', array(
                    'entities' => $entities,
        ));
    }

    /**
     * Creates a new InstitucioneducativaHumanisticoTecnico entity.
     *
     */
    public function createAction(Request $request) {
        $entity = new InstitucioneducativaHumanisticoTecnico();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('sie_ueHumTec_show', array('id' => $entity->getId())));
        }

        return $this->render('SieRegularBundle:InstitucioneducativaHumanisticoTecnico:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a InstitucioneducativaHumanisticoTecnico entity.
     *
     * @param InstitucioneducativaHumanisticoTecnico $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InstitucioneducativaHumanisticoTecnico $entity) {
        $form = $this->createForm(new InstitucioneducativaHumanisticoTecnicoType(), $entity, array(
            'action' => $this->generateUrl('sie_ueHumTec_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new InstitucioneducativaHumanisticoTecnico entity.
     *
     */
    public function newAction() {
        $entity = new InstitucioneducativaHumanisticoTecnico();
        $form = $this->createCreateForm($entity);

        return $this->render('SieRegularBundle:InstitucioneducativaHumanisticoTecnico:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a InstitucioneducativaHumanisticoTecnico entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InstitucioneducativaHumanisticoTecnico entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieRegularBundle:InstitucioneducativaHumanisticoTecnico:show.html.twig', array(
                    'entity' => $entity,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InstitucioneducativaHumanisticoTecnico entity.
     *
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InstitucioneducativaHumanisticoTecnico entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieRegularBundle:InstitucioneducativaHumanisticoTecnico:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a InstitucioneducativaHumanisticoTecnico entity.
     *
     * @param InstitucioneducativaHumanisticoTecnico $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(InstitucioneducativaHumanisticoTecnico $entity) {
        $form = $this->createForm(new InstitucioneducativaHumanisticoTecnicoType(), $entity, array(
            'action' => $this->generateUrl('sie_ueHumTec_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn btn-success')));

        return $form;
    }

    /**
     * Edits an existing InstitucioneducativaHumanisticoTecnico entity.
     *
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InstitucioneducativaHumanisticoTecnico entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('sie_ueHumTec_edit', array('id' => $id)));
        }

        return $this->render('SieRegularBundle:InstitucioneducativaHumanisticoTecnico:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a InstitucioneducativaHumanisticoTecnico entity.
     *
     */
    public function deleteAction(Request $request, $id) {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InstitucioneducativaHumanisticoTecnico entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('sie_ueHumTec'));
    }

    /**
     * Creates a form to delete a InstitucioneducativaHumanisticoTecnico entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('sie_ueHumTec_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

}
