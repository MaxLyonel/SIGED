<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\OlimRegistroOlimpiada;
use Sie\AppWebBundle\Form\OlimRegistroOlimpiadaType;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * OlimRegistroOlimpiada controller.
 *
 */
class OlimRegistroOlimpiadaController extends Controller
{

    public $session;

    public function __construct(){
        $this->session = new Session();
    }
    /**
     * Lists all OlimRegistroOlimpiada entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->findAll();

        return $this->render('SieOlimpiadasBundle:OlimRegistroOlimpiada:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new OlimRegistroOlimpiada entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new OlimRegistroOlimpiada();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_registro_olimpiada');")->execute();
            $em->persist($entity);
            $em->flush();
            $this->session->set('olimpiadaId', $entity->getId());
            return $this->redirectToRoute('olimregistroolimpiada');
        }

        return $this->render('SieOlimpiadasBundle:OlimRegistroOlimpiada:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a OlimRegistroOlimpiada entity.
     *
     * @param OlimRegistroOlimpiada $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(OlimRegistroOlimpiada $entity)
    {
        $form = $this->createForm(new OlimRegistroOlimpiadaType(), $entity, array(
            'action' => $this->generateUrl('olimregistroolimpiada_create'),
            'method' => 'POST',
        ));

        // $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new OlimRegistroOlimpiada entity.
     *
     */
    public function newAction()
    {
        $entity = new OlimRegistroOlimpiada();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieOlimpiadasBundle:OlimRegistroOlimpiada:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing OlimRegistroOlimpiada entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimRegistroOlimpiada entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieOlimpiadasBundle:OlimRegistroOlimpiada:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a OlimRegistroOlimpiada entity.
    *
    * @param OlimRegistroOlimpiada $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(OlimRegistroOlimpiada $entity)
    {
        $form = $this->createForm(new OlimRegistroOlimpiadaType(), $entity, array(
            'action' => $this->generateUrl('olimregistroolimpiada_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        // $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing OlimRegistroOlimpiada entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimRegistroOlimpiada entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $this->session->set('olimpiadaId', $entity->getId());
            $em->flush();

            return $this->redirectToRoute('olimregistroolimpiada');
        }

        return $this->render('SieOlimpiadasBundle:OlimRegistroOlimpiada:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a OlimRegistroOlimpiada entity.
     *
     */
    public function deleteAction(Request $request)
    {
        $id = $request->get('id');
    
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:OlimRegistroOlimpiada')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimRegistroOlimpiada entity.');
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirectToRoute('olimregistroolimpiada');
    }

    /**
     * Creates a form to delete a OlimRegistroOlimpiada entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('olimregistroolimpiada_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
