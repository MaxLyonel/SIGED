<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\InfraestructuraH5Ambientepedagogico;
use Sie\AppWebBundle\Form\InfraestructuraH5AmbientepedagogicoType;

/**
 * InfraestructuraH5Ambientepedagogico controller.
 *
 */
class InfraestructuraH5AmbientepedagogicoController extends Controller
{

    /**
     * Lists all InfraestructuraH5Ambientepedagogico entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH5Ambientepedagogico')->findBy(array(), array('id'=>'DESC'),10);

        return $this->render('SieAppWebBundle:InfraestructuraH5Ambientepedagogico:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * [accessAction description]
     * @return [type] [description]
     */
    public function pedagogicoAction(){

         $infraestructuraJuridiccionGeograficaId = 11392;
         $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH5Ambientepedagogico')->findBy(array(
            'infraestructuraJuridiccionGeografica'=> $infraestructuraJuridiccionGeograficaId
        ));

        return $this->render('SieAppWebBundle:InfraestructuraH5Ambientepedagogico:index.html.twig', array(
            'entities' => $entities,
            'infraestructuraJuridiccionGeograficaId' => $infraestructuraJuridiccionGeograficaId,
        ));

        // return $this->render('SieInfraBundle:SheetFive:access.html.twig', array(
        //         // ...
        // ));    
    }
    public function newAmbPedagogicoAction(Request $request){


        $entity = new InfraestructuraH5Ambientepedagogico();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:InfraestructuraH5Ambientepedagogico:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }
    /**
     * Creates a new InfraestructuraH5Ambientepedagogico entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH5Ambientepedagogico();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('infraestructurah5ambientepedagogico_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH5Ambientepedagogico:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a InfraestructuraH5Ambientepedagogico entity.
     *
     * @param InfraestructuraH5Ambientepedagogico $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH5Ambientepedagogico $entity)
    {
        $form = $this->createForm(new InfraestructuraH5AmbientepedagogicoType(), $entity, array(
            'action' => $this->generateUrl('infraestructurah5ambientepedagogico_create'),
            'method' => 'POST',
        ));

        // $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH5Ambientepedagogico entity.
     *
     */
    public function newAction()
    {
        $entity = new InfraestructuraH5Ambientepedagogico();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:InfraestructuraH5Ambientepedagogico:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a InfraestructuraH5Ambientepedagogico entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5Ambientepedagogico')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH5Ambientepedagogico entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH5Ambientepedagogico:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH5Ambientepedagogico entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5Ambientepedagogico')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH5Ambientepedagogico entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH5Ambientepedagogico:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a InfraestructuraH5Ambientepedagogico entity.
    *
    * @param InfraestructuraH5Ambientepedagogico $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH5Ambientepedagogico $entity)
    {
        $form = $this->createForm(new InfraestructuraH5AmbientepedagogicoType(), $entity, array(
            'action' => $this->generateUrl('infraestructurah5ambientepedagogico_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH5Ambientepedagogico entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5Ambientepedagogico')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH5Ambientepedagogico entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('infraestructurah5ambientepedagogico_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH5Ambientepedagogico:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a InfraestructuraH5Ambientepedagogico entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5Ambientepedagogico')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InfraestructuraH5Ambientepedagogico entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('infraestructurah5ambientepedagogico'));
    }

    /**
     * Creates a form to delete a InfraestructuraH5Ambientepedagogico entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('infraestructurah5ambientepedagogico_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
