<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\PreinsInstitucioneducativaCursoCupo;
use Sie\AppWebBundle\Form\PreinsInstitucioneducativaCursoCupoType;

/**
 * PreinsInstitucioneducativaCursoCupo controller.
 *
 */
class PreinsInstitucioneducativaCursoCupoController extends Controller
{

    /**
     * Lists all PreinsInstitucioneducativaCursoCupo entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo')->findAll();

        return $this->render('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new PreinsInstitucioneducativaCursoCupo entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new PreinsInstitucioneducativaCursoCupo();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('uealtademanda_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a PreinsInstitucioneducativaCursoCupo entity.
     *
     * @param PreinsInstitucioneducativaCursoCupo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(PreinsInstitucioneducativaCursoCupo $entity)
    {
        $form = $this->createForm(new PreinsInstitucioneducativaCursoCupoType(), $entity, array(
            'action' => $this->generateUrl('uealtademanda_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new PreinsInstitucioneducativaCursoCupo entity.
     *
     */
    public function newAction()
    {
        $entity = new PreinsInstitucioneducativaCursoCupo();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a PreinsInstitucioneducativaCursoCupo entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PreinsInstitucioneducativaCursoCupo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing PreinsInstitucioneducativaCursoCupo entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PreinsInstitucioneducativaCursoCupo entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a PreinsInstitucioneducativaCursoCupo entity.
    *
    * @param PreinsInstitucioneducativaCursoCupo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(PreinsInstitucioneducativaCursoCupo $entity)
    {
        $form = $this->createForm(new PreinsInstitucioneducativaCursoCupoType(), $entity, array(
            'action' => $this->generateUrl('uealtademanda_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing PreinsInstitucioneducativaCursoCupo entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PreinsInstitucioneducativaCursoCupo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('uealtademanda_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a PreinsInstitucioneducativaCursoCupo entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find PreinsInstitucioneducativaCursoCupo entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('uealtademanda'));
    }

    /**
     * Creates a form to delete a PreinsInstitucioneducativaCursoCupo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('uealtademanda_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    public function editarAction(Request $request){
        dump($request);
        die;

    }    
}
