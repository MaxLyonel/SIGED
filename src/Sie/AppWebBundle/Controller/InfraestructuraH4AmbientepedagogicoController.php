<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\InfraestructuraH4Ambientepedagogico;
use Sie\AppWebBundle\Form\InfraestructuraH4AmbientepedagogicoType;

/**
 * InfraestructuraH4Ambientepedagogico controller.
 *
 */
class InfraestructuraH4AmbientepedagogicoController extends Controller
{

    /**
     * Lists all InfraestructuraH4Ambientepedagogico entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH4Ambientepedagogico')->findAll();

        return $this->render('SieAppWebBundle:InfraestructuraH4Ambientepedagogico:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new InfraestructuraH4Ambientepedagogico entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH4Ambientepedagogico();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->setInfraestructuraJuridiccionGeografica($em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find(1));
            $entity->setFecharegistro(new \DateTime('now'));
            $em->persist($entity);
            $em->flush();
            die('ok');
            return $this->redirect($this->generateUrl('infraestructurah4ambientepedagogico_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH4Ambientepedagogico:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a InfraestructuraH4Ambientepedagogico entity.
     *
     * @param InfraestructuraH4Ambientepedagogico $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH4Ambientepedagogico $entity)
    {
        $form = $this->createForm(new InfraestructuraH4AmbientepedagogicoType(), $entity, array(
            'action' => $this->generateUrl('infraestructurah4ambientepedagogico_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH4Ambientepedagogico entity.
     *
     */
    public function newAction()
    {
        $entity = new InfraestructuraH4Ambientepedagogico();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:InfraestructuraH4Ambientepedagogico:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a InfraestructuraH4Ambientepedagogico entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH4Ambientepedagogico')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH4Ambientepedagogico entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH4Ambientepedagogico:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH4Ambientepedagogico entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH4Ambientepedagogico')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH4Ambientepedagogico entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH4Ambientepedagogico:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a InfraestructuraH4Ambientepedagogico entity.
    *
    * @param InfraestructuraH4Ambientepedagogico $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH4Ambientepedagogico $entity)
    {
        $form = $this->createForm(new InfraestructuraH4AmbientepedagogicoType(), $entity, array(
            'action' => $this->generateUrl('infraestructurah4ambientepedagogico_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH4Ambientepedagogico entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH4Ambientepedagogico')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH4Ambientepedagogico entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('infraestructurah4ambientepedagogico_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH4Ambientepedagogico:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a InfraestructuraH4Ambientepedagogico entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH4Ambientepedagogico')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InfraestructuraH4Ambientepedagogico entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('infraestructurah4ambientepedagogico'));
    }

    /**
     * Creates a form to delete a InfraestructuraH4Ambientepedagogico entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('infraestructurah4ambientepedagogico_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
