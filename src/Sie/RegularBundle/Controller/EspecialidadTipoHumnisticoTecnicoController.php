<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EspecialidadTipoHumnisticoTecnico;
use Sie\AppWebBundle\Form\EspecialidadTipoHumnisticoTecnicoType;

/**
 * EspecialidadTipoHumnisticoTecnico controller.
 *
 */
class EspecialidadTipoHumnisticoTecnicoController extends Controller {

    /**
     * Lists all EspecialidadTipoHumnisticoTecnico entities.
     *
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:EspecialidadTipoHumnisticoTecnico')->findAll();

        return $this->render('SieRegularBundle:EspecialidadTipoHumnisticoTecnico:index.html.twig', array(
                    'entities' => $entities,
        ));
    }

    /**
     * Creates a new EspecialidadTipoHumnisticoTecnico entity.
     *
     */
    public function createAction(Request $request) {
        $entity = new EspecialidadTipoHumnisticoTecnico();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $message = "Datos actualizados correctamente";
            $this->addFlash('successeth', $message);
            return $this->redirectToRoute('sie_especialidadHumanisticoTecnico');
            //return $this->redirect($this->generateUrl('sie_especialidadHumanisticoTecnico_show', array('id' => $entity->getId())));
        }

        return $this->render('SieRegularBundle:EspecialidadTipoHumnisticoTecnico:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a EspecialidadTipoHumnisticoTecnico entity.
     *
     * @param EspecialidadTipoHumnisticoTecnico $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(EspecialidadTipoHumnisticoTecnico $entity) {
        $form = $this->createForm(new EspecialidadTipoHumnisticoTecnicoType(), $entity, array(
            'action' => $this->generateUrl('sie_especialidadHumanisticoTecnico_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-default')));

        return $form;
    }

    /**
     * Displays a form to create a new EspecialidadTipoHumnisticoTecnico entity.
     *
     */
    public function newAction() {
        $entity = new EspecialidadTipoHumnisticoTecnico();
        $form = $this->createCreateForm($entity);



        return $this->render('SieRegularBundle:EspecialidadTipoHumnisticoTecnico:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a EspecialidadTipoHumnisticoTecnico entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:EspecialidadTipoHumnisticoTecnico')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find EspecialidadTipoHumnisticoTecnico entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieRegularBundle:EspecialidadTipoHumnisticoTecnico:show.html.twig', array(
                    'entity' => $entity,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing EspecialidadTipoHumnisticoTecnico entity.
     *
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:EspecialidadTipoHumnisticoTecnico')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find EspecialidadTipoHumnisticoTecnico entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieRegularBundle:EspecialidadTipoHumnisticoTecnico:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a EspecialidadTipoHumnisticoTecnico entity.
     *
     * @param EspecialidadTipoHumnisticoTecnico $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(EspecialidadTipoHumnisticoTecnico $entity) {
        $form = $this->createForm(new EspecialidadTipoHumnisticoTecnicoType(), $entity, array(
            'action' => $this->generateUrl('sie_especialidadHumanisticoTecnico_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn btn-default')));

        return $form;
    }

    /**
     * Edits an existing EspecialidadTipoHumnisticoTecnico entity.
     *
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:EspecialidadTipoHumnisticoTecnico')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find EspecialidadTipoHumnisticoTecnico entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            $message = "Dato actualizados correctamente";
            $this->addFlash('successeth', $message);
            return $this->redirectToRoute('sie_especialidadHumanisticoTecnico');
            //return $this->redirect($this->generateUrl('sie_especialidadHumanisticoTecnico_edit', array('id' => $id)));
        }

        return $this->render('SieRegularBundle:EspecialidadTipoHumnisticoTecnico:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a EspecialidadTipoHumnisticoTecnico entity.
     *
     */
    public function deleteAction(Request $request, $id) {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:EspecialidadTipoHumnisticoTecnico')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find EspecialidadTipoHumnisticoTecnico entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('sie_especialidadHumanisticoTecnico'));
    }

    /**
     * Creates a form to delete a EspecialidadTipoHumnisticoTecnico entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('sie_especialidadHumanisticoTecnico_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

}
