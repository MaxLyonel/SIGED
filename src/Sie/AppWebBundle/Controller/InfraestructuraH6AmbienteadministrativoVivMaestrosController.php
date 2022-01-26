<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoVivMaestros;
use Sie\AppWebBundle\Form\InfraestructuraH6AmbienteadministrativoVivMaestrosType;

/**
 * InfraestructuraH6AmbienteadministrativoVivMaestros controller.
 *
 */
class InfraestructuraH6AmbienteadministrativoVivMaestrosController extends Controller{

    /**
     * Lists all InfraestructuraH6AmbienteadministrativoVivMaestros entities.
     *
     */
    public function indexAction(Request $request){
        $em = $this->getDoctrine()->getManager();

         //get the send values
        $infraestructuraJuridiccionGeograficaId = $request->get('infraestructuraJuridiccionGeografica');
         // $infraestructuraJuridiccionGeograficaId = 10;
        //get the element data
        $objAmbienteAdministrativo = $em->getRepository('SieAppWebBundle:InfraestructuraH6Ambienteadministrativo')->findOneBy(array(
            'infraestructuraJuridiccionGeografica' => $infraestructuraJuridiccionGeograficaId,
        ));
        // dump($objAmbienteAdministrativo);die;
        //check  if the element exist
        if($objAmbienteAdministrativo){
           //check if the exist the ambiente obj
            return $this->redirect($this->generateUrl('infrah6viviendasmaestros_listviviendas', array('id'=>$objAmbienteAdministrativo->getId() )));
           }else{
            return $this->redirect($this->generateUrl('infrah6viviendasmaestros_listviviendas', array('id'=>'' )));
           }
    }

    /**
     * [listviviendasAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function listviviendasAction(Request $request){
        //get the send values
        $ambienteAdministrativoId = $request->get('id');

       // create DB conexion
        $em = $this->getDoctrine()->getManager();

          //get the send values
        $infraestructuraJuridiccionGeograficaId = $request->get('infraestructuraJuridiccionGeografica');

        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoVivMaestros')->findBy(array(
            'infraestructuraH6Ambienteadministrativo' =>  $ambienteAdministrativoId,
        ));

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoVivMaestros:index.html.twig', array(
            'entities' => $entities,
            'ambienteAdministrativoId' => $ambienteAdministrativoId,
        ));
    }

     /**
     * [addnewh6ambienteAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function addnewh6viviendaAction(Request $request){
        
        //get the send data
        $ambienteAdministrativoId = $request->get('ambienteAdministrativoId');

        $entity = new InfraestructuraH6AmbienteadministrativoVivMaestros();
        $form   = $this->createCreateForm($entity, $ambienteAdministrativoId);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoVivMaestros:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));

    }


    /**
     * [saveNewh6ViviendaAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function saveNewh6ViviendaAction(Request $request){
        // cretae db conexion var
        $em =  $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        //get the send data
        $form = $request->get('sie_appwebbundle_infraestructurah6ambienteadministrativovivmaestros');
        
        
        try {

            $entity = new InfraestructuraH6AmbienteadministrativoVivMaestros();

            $entity->setN21NumeroAmbientes($form['n21NumeroAmbientes']);
            $entity->setN21NumeroHabitantes($form['n21NumeroHabitantes']);
            $entity->setN21NumeroBanios($form['n21NumeroBanios']);
            $entity->setN21NumeroDuchas($form['n21NumeroDuchas']);
            $entity->setN21NumeroCocinas($form['n21NumeroCocinas']);
            $entity->setN21MetrosArea($form['n21MetrosArea']);
            $entity->setEstadoTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadoGeneral')->find($form['estadoTipo']) );
            $entity->setN21EsAmbienteCieloFal($form['n21EsAmbienteCieloFal']);
            $entity->setN21AmbienteCieloFalTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n21AmbienteCieloFalTipo']) );
            $entity->setN21EsAmbienteMuros($form['n21EsAmbienteMuros']);
            $entity->setN21AmbienteMuroMatTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMurosMaterialTipo')->find($form['n21AmbienteMuroMatTipo']) );
            $entity->setN21AmbienteMuroCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMurosCaracTipo')->find($form['n21AmbienteMuroCaracTipo']) );
            $entity->setN21EsAmbientePuerta($form['n21EsAmbientePuerta']);
            $entity->setN211SeguroTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPuertasSeguroTipo')->find($form['n211SeguroTipo']) );
            $entity->setN212AbreTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPuertasAbreTipo')->find($form['n212AbreTipo']) );
            // $entity->setN612AbreTipoId($form['n612AbreTipoId']);
            $entity->setN21EsAmbienteRevestimiento($form['n21EsAmbienteRevestimiento']);
            $entity->setN21AmbienteRevestMatTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenRevestimientoMaterialTipo')->find($form['n21AmbienteRevestMatTipo']) );
            $entity->setN21AmbienteRevestCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n21AmbienteRevestCaracTipo']) );
            $entity->setN21EsAmbienteVentana($form['n21EsAmbienteVentana']);
            $entity->setN21AmbienteVentanaTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenVentanasCaracTipo')->find($form['n21AmbienteVentanaTipo']) );
            $entity->setN21EsAmbientePiso($form['n21EsAmbientePiso']);
            $entity->setN21AmbientePisoMatTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPisosMaterialTipo')->find($form['n21AmbientePisoMatTipo']) );
            $entity->setN21AmbientePisoCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n21AmbientePisoCaracTipo']) );  
            $entity->setN21EsAmbienteTecho($form['n21EsAmbienteTecho']);
            $entity->setInfraestructuraH6Ambienteadministrativo($em->getRepository('SieAppWebBundle:InfraestructuraH6Ambienteadministrativo')->find($form['ambienteAdministrativoId']) );
            

            $em->persist($entity);
            $em->flush();

            $em->getConnection()->commit();

            
        } catch (Exception $e) {
             echo $e->getTraceAsString();
             $em->getConnection()->rollback();
             $em->close();
             throw $e;
        }
   
        return $this->redirect($this->generateUrl('infrah6viviendasmaestros_listviviendas', array('id'=>$form['ambienteAdministrativoId'] )));
    }






    /**
     * Creates a new InfraestructuraH6AmbienteadministrativoVivMaestros entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH6AmbienteadministrativoVivMaestros();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('infrah6viviendasmaestros_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoVivMaestros:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a InfraestructuraH6AmbienteadministrativoVivMaestros entity.
     *
     * @param InfraestructuraH6AmbienteadministrativoVivMaestros $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH6AmbienteadministrativoVivMaestros $entity, $ambienteAdministrativoId)
    {
        $form = $this->createForm(new InfraestructuraH6AmbienteadministrativoVivMaestrosType(), $entity, array(
            // 'action' => $this->generateUrl('infrah6viviendasmaestros_create'),
            'method' => 'POST',
        ));

        // $form->add('submit', 'submit', array('label' => 'Create'));\
        $form->add('ambienteAdministrativoId', 'text', array('mapped'=>false,'data' => $ambienteAdministrativoId));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH6AmbienteadministrativoVivMaestros entity.
     *
     */
    public function newAction()
    {
        $entity = new InfraestructuraH6AmbienteadministrativoVivMaestros();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoVivMaestros:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a InfraestructuraH6AmbienteadministrativoVivMaestros entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoVivMaestros')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoVivMaestros entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoVivMaestros:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH6AmbienteadministrativoVivMaestros entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoVivMaestros')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoVivMaestros entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoVivMaestros:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a InfraestructuraH6AmbienteadministrativoVivMaestros entity.
    *
    * @param InfraestructuraH6AmbienteadministrativoVivMaestros $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH6AmbienteadministrativoVivMaestros $entity)
    {
        $form = $this->createForm(new InfraestructuraH6AmbienteadministrativoVivMaestrosType(), $entity, array(
            'action' => $this->generateUrl('infrah6viviendasmaestros_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH6AmbienteadministrativoVivMaestros entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoVivMaestros')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoVivMaestros entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('infrah6viviendasmaestros_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoVivMaestros:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a InfraestructuraH6AmbienteadministrativoVivMaestros entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoVivMaestros')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoVivMaestros entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('infrah6viviendasmaestros'));
    }

    /**
     * Creates a form to delete a InfraestructuraH6AmbienteadministrativoVivMaestros entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('infrah6viviendasmaestros_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
