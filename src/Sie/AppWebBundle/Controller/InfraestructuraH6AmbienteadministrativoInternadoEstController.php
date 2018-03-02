<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoInternadoEst;
use Sie\AppWebBundle\Form\InfraestructuraH6AmbienteadministrativoInternadoEstType;

/**
 * InfraestructuraH6AmbienteadministrativoInternadoEst controller.
 *
 */
class InfraestructuraH6AmbienteadministrativoInternadoEstController extends Controller
{

    /**
     * Lists all InfraestructuraH6AmbienteadministrativoInternadoEst entities.
     *
     */
    public function indexAction(Request $request){
        
        $em = $this->getDoctrine()->getManager();
        //get send values
        $infraestructuraJuridiccionGeografica = $request->get('infraestructuraJuridiccionGeografica');
        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInternadoEst')->find($infraestructuraJuridiccionGeografica);

        if($entity){
            $jsonelement = json_encode(array('id'=>$infraestructuraJuridiccionGeografica, 'interEstId' => $Entity->getId()));
            // edit the info
            return $this->redirectToRoute('infrah6internadoestudiante_edit', array('id'=>$jsonelement ));
        }else{
            // create new data
            $jsonelement = json_encode(array('id'=>$infraestructuraJuridiccionGeografica, 'interEstId' => false));
            return $this->redirectToRoute('infrah6internadoestudiante_new',  array('id'=>$jsonelement ));
            // return $this->redirect($this->generateUrl('infrah6internadoestudiante_new',  array('id'=>$jsonelement )));

        }

        // dump($entity);die;

        // return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInternadoEst:index.html.twig', array(
        //     'entities' => $entities,
        // ));
    }
    /**
     * Creates a new InfraestructuraH6AmbienteadministrativoInternadoEst entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH6AmbienteadministrativoInternadoEst();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('infrah6internadoestudiante_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInternadoEst:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a InfraestructuraH6AmbienteadministrativoInternadoEst entity.
     *
     * @param InfraestructuraH6AmbienteadministrativoInternadoEst $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH6AmbienteadministrativoInternadoEst $entity, $id){
        $form = $this->createForm(new InfraestructuraH6AmbienteadministrativoInternadoEstType(), $entity, array(
            // 'action' => $this->generateUrl('infrah6internadoestudiante_create'),
            'method' => 'POST',
        ));
        // infraestructuraJuridiccionGeografica
        // $form->add('submit', 'submit', array('label' => 'Create'));
        $form->add('ambienteadministrativoid', 'text', array('mapped'=>false,'label' => 'id', 'data'=>$id));


        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH6AmbienteadministrativoInternadoEst entity.
     *
     */
    public function newAction(Request $request){
        
        //get the send values
        $jsonelement = $request->get('id');
        $arrElement = json_decode($jsonelement, true);
        // dump($arrElement);die;
        //entity to new data
        $entity = new InfraestructuraH6AmbienteadministrativoInternadoEst();
        $form   = $this->createCreateForm($entity, $arrElement['id']);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInternadoEst:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'arrElement'   => $arrElement,

        ));
    }

    /**
     * Finds and displays a InfraestructuraH6AmbienteadministrativoInternadoEst entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInternadoEst')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoInternadoEst entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInternadoEst:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH6AmbienteadministrativoInternadoEst entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInternadoEst')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoInternadoEst entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInternadoEst:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a InfraestructuraH6AmbienteadministrativoInternadoEst entity.
    *
    * @param InfraestructuraH6AmbienteadministrativoInternadoEst $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH6AmbienteadministrativoInternadoEst $entity)
    {
        $form = $this->createForm(new InfraestructuraH6AmbienteadministrativoInternadoEstType(), $entity, array(
            'action' => $this->generateUrl('infrah6internadoestudiante_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH6AmbienteadministrativoInternadoEst entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInternadoEst')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoInternadoEst entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('infrah6internadoestudiante_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInternadoEst:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a InfraestructuraH6AmbienteadministrativoInternadoEst entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInternadoEst')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoInternadoEst entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('infrah6internadoestudiante'));
    }

    /**
     * Creates a form to delete a InfraestructuraH6AmbienteadministrativoInternadoEst entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('infrah6internadoestudiante_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }



   public function saveH6InternadoEstAction(Request $request){
    
    
    //get the send values
    $form = $request->get('sie_appwebbundle_infraestructurah6ambienteadministrativointernadoest');
    
    //create db conexion
    $em = $this->getDoctrine()->getManager();
    $em->getConnection()->beginTransaction();

    try {

        $entity = new InfraestructuraH6AmbienteadministrativoInternadoEst();
        $entity->setN31EsInternadoEst($form['n31EsInternadoEst']);
        $entity->setN32DistMetrosFemMas($form['n32DistMetrosFemMas']);
        $entity->setN33ResponsableTipo($em->getRepository('SieAppWebBundle:InfraestructuraH6ResponsableTipo')->find($form['n33ResponsableTipo']));
        $entity->setInfraestructuraH6Ambienteadministrativo($em->getRepository('SieAppWebBundle:InfraestructuraH6Ambienteadministrativo')->find($form['ambienteadministrativoid']));
        $entity->setFechaRegistro(new \DateTime('now'));

        $em->persist($entity);
        $em->flush();

        $em->getConnection()->commit();
        
    } catch (Exception $e) {
             echo $e->getTraceAsString();
             $em->getConnection()->rollback();
             $em->close();
             throw $e;
    }



    //get send values to show the correct form
    $infraestructuraJuridiccionGeografica = $form['ambienteadministrativoid'];
    $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInternadoEst')->find($infraestructuraJuridiccionGeografica);

        if($entity){
            $jsonelement = json_encode(array('id'=>$infraestructuraJuridiccionGeografica, 'interEstId' => $Entity->getId()));
            // edit the info
            return $this->redirect($this->generateUrl('infrah6internadoestudiante_edit', array('id'=>$jsonelement )));
        }else{
            // create new data
            $jsonelement = json_encode(array('id'=>$infraestructuraJuridiccionGeografica, 'interEstId' => false));
            return $this->redirect($this->generateUrl('infrah6internadoestudiante_new',  array('id'=>$jsonelement )));

        }
    }
}
