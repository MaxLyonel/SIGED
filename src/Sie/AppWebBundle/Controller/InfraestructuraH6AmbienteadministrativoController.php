<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\InfraestructuraH6Ambienteadministrativo;
use Sie\AppWebBundle\Form\InfraestructuraH6AmbienteadministrativoType;
use Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoAmbiente;


/**
 * InfraestructuraH6Ambienteadministrativo controller.
 *
 */
class InfraestructuraH6AmbienteadministrativoController extends Controller
{

    /**
     * Lists all InfraestructuraH6Ambienteadministrativo entities.
     *
     */
    public function indexAction(Request $request){
        //creaet db conexion
        $em = $this->getDoctrine()->getManager();
        //get the send values
        $infraestructuraJuridiccionGeograficaId = 10;
        //get the element data
        $objAmbienteAdministrativo = $em->getRepository('SieAppWebBundle:InfraestructuraH6Ambienteadministrativo')->findOneBy(array(
            'infraestructuraJuridiccionGeografica' => $infraestructuraJuridiccionGeograficaId,
        ));
        //check  if the element exist
        if($objAmbienteAdministrativo){
           $objAmbiente = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoAmbiente')->findBy(array(
            'infraestructuraH6Ambienteadministrativo' => $objAmbienteAdministrativo->getId(),
            )); 
           // dump($objAmbiente);die;
           //check if the exist the ambiente obj
           if($objAmbiente){
            return $this->redirect($this->generateUrl('infrah6ambadmambiente_listambiente', array('id'=>$objAmbienteAdministrativo->getId() )));
           }else{
            return $this->redirect($this->generateUrl('infrah6ambadmambiente_new'));
           }

        }

         // dump($objAmbienteAdministrativo);die;

        // $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH6Ambienteadministrativo')->findAll();

        // return $this->render('SieAppWebBundle:InfraestructuraH6Ambienteadministrativo:index.html.twig', array(
        //     'entities' => $entities,
        // ));
    }

   
    /**
     * Creates a new InfraestructuraH6Ambienteadministrativo entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH6Ambienteadministrativo();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('infrah6ambadm_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH6Ambienteadministrativo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a InfraestructuraH6Ambienteadministrativo entity.
     *
     * @param InfraestructuraH6Ambienteadministrativo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH6Ambienteadministrativo $entity)
    {
        $form = $this->createForm(new InfraestructuraH6AmbienteadministrativoType(), $entity, array(
            'action' => $this->generateUrl('infrah6ambadm_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH6Ambienteadministrativo entity.
     *
     */
    public function newAction()
    {
        $entity = new InfraestructuraH6Ambienteadministrativo();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:InfraestructuraH6Ambienteadministrativo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a InfraestructuraH6Ambienteadministrativo entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6Ambienteadministrativo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6Ambienteadministrativo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH6Ambienteadministrativo:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH6Ambienteadministrativo entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6Ambienteadministrativo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6Ambienteadministrativo entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH6Ambienteadministrativo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a InfraestructuraH6Ambienteadministrativo entity.
    *
    * @param InfraestructuraH6Ambienteadministrativo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH6Ambienteadministrativo $entity)
    {
        $form = $this->createForm(new InfraestructuraH6AmbienteadministrativoType(), $entity, array(
            'action' => $this->generateUrl('infrah6ambadm_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH6Ambienteadministrativo entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6Ambienteadministrativo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6Ambienteadministrativo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('infrah6ambadm_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH6Ambienteadministrativo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a InfraestructuraH6Ambienteadministrativo entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6Ambienteadministrativo')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InfraestructuraH6Ambienteadministrativo entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('infrah6ambadm'));
    }

    /**
     * Creates a form to delete a InfraestructuraH6Ambienteadministrativo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('infrah6ambadm_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
