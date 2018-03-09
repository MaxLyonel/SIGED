<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoServAlimentacion;
use Sie\AppWebBundle\Form\InfraestructuraH6AmbienteadministrativoServAlimentacionType;

/**
 * InfraestructuraH6AmbienteadministrativoServAlimentacion controller.
 *
 */
class InfraestructuraH6AmbienteadministrativoServAlimentacionController extends Controller{

    /**
     * Lists all InfraestructuraH6AmbienteadministrativoServAlimentacion entities.
     *
     */
    public function indexAction(Request $request){

        $em = $this->getDoctrine()->getManager();

        $infraestructuraJuridiccionGeografica = $request->get('infraestructuraJuridiccionGeografica');

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6Ambienteadministrativo')->findOneBy(
        array('infraestructuraJuridiccionGeografica' => $infraestructuraJuridiccionGeografica));

        if($entity){
            return $this->redirectToRoute('infrah6ambadmservalimentacion_listservalimento', array('id'=> $entity->getId()));
        }else{
            return $this->redirectToRoute('infrah6ambadmservalimentacion_listservalimento', array('id'=> ''));
        }

    }

    public function listservalimentoAction(Request $request){

        // get the send values
        $ambienteAdministrativoId = $request->get('id');
        // create db conexion
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoServAlimentacion')->findBy(array(
        'infraestructuraH6Ambienteadministrativo' => $ambienteAdministrativoId
        ));
        // dump($entities);die;
        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoServAlimentacion:index.html.twig', array(
        'entities' => $entities,
        'ambienteAdministrativoId' => $ambienteAdministrativoId
        ));
    }

    public function addnewservalimentacionAction (Request $request){

        // get the values send
        $ambienteAdministrativoId = $request->get('ambienteAdministrativoId');
        // dump($request);die;

        $entity = new InfraestructuraH6AmbienteadministrativoServAlimentacion();
        $form   = $this->createCreateForm($entity, $ambienteAdministrativoId);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoServAlimentacion:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }


     public function saveNewservalimentacionAction(Request $request){
        // cretae db conexion var
        $em =  $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //get the send data
        $form = $request->get('sie_appwebbundle_infraestructurah6ambienteadministrativoservalimentacion');
        // try to save the data       
        try {

            $entity = new InfraestructuraH6AmbienteadministrativoServAlimentacion();

            
            $entity->setN41AmbienteAlimentacionTipo($em->getRepository('SieAppWebBundle:InfraestructuraH6ServicioAlimentacionTipo')->find($form['n41AmbienteAlimentacionTipo']) );
            $entity->setN41NumeroAmbientes($form['n41NumeroAmbientes']);
            $entity->setN41MetrosArea($form['n41MetrosArea']);
            $entity->setEstadoTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadoGeneral')->find($form['estadoTipo']) );
            $entity->setN41EsAmbienteCieloFal($form['n41EsAmbienteCieloFal']);
            $entity->setN41AmbienteCieloFalTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n41AmbienteCieloFalTipo']) );
            $entity->setN41EsAmbienteMuros($form['n41AmbienteMuroMatTipo']);
            $entity->setN41AmbienteMuroMatTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMurosMaterialTipo')->find($form['n41AmbienteMuroMatTipo']) );
            $entity->setN41AmbienteMuroCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMurosCaracTipo')->find($form['n41AmbienteMuroCaracTipo']) );
            $entity->setN41EsAmbientePuerta($form['n41EsAmbientePuerta']);
            $entity->setN411SeguroTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPuertasSeguroTipo')->find($form['n411SeguroTipo']) );
            $entity->setN412AbreTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPuertasAbreTipo')->find($form['n412AbreTipo']) );

            $entity->setN41EsAmbienteRevestimiento($form['n41EsAmbienteRevestimiento']);

            $entity->setN41AmbienteRevestMatTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenRevestimientoMaterialTipo')->find($form['n41AmbienteRevestMatTipo']) );
            $entity->setN41AmbienteRevestCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n41AmbienteRevestCaracTipo']) );

            $entity->setN41EsAmbienteVentana($form['n41EsAmbienteVentana']);

            $entity->setN41AmbienteVentanaTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenVentanasCaracTipo')->find($form['n41AmbienteVentanaTipo']) );

            $entity->setN41EsAmbientePiso($form['n41EsAmbientePiso']);

            $entity->setN41AmbientePisoMatTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPisosMaterialTipo')->find($form['n41AmbientePisoMatTipo']) );

            $entity->setN41AmbientePisoCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n41AmbientePisoCaracTipo']) );           

            $entity->setN41EsAmbienteTecho($form['n41EsAmbienteTecho']);
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
   
        return $this->redirect($this->generateUrl('infrah6ambadmservalimentacion_listservalimento', array('id'=>$form['ambienteAdministrativoId'] )));
    }


    /**
     * Creates a new InfraestructuraH6AmbienteadministrativoServAlimentacion entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH6AmbienteadministrativoServAlimentacion();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('infrah6ambadmservalimentacion_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoServAlimentacion:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a InfraestructuraH6AmbienteadministrativoServAlimentacion entity.
     *
     * @param InfraestructuraH6AmbienteadministrativoServAlimentacion $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH6AmbienteadministrativoServAlimentacion $entity, $ambienteAdministrativoId){
        $form = $this->createForm(new InfraestructuraH6AmbienteadministrativoServAlimentacionType(), $entity, array(
            // 'action' => $this->generateUrl('infrah6ambadmservalimentacion_create'),
            'method' => 'POST',
        ));

        // $form->add('submit', 'submit', array('label' => 'Create'));
        $form->add('ambienteAdministrativoId', 'text', array('mapped'=>false,'data' => $ambienteAdministrativoId));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH6AmbienteadministrativoServAlimentacion entity.
     *
     */
    public function newAction()
    {
        $entity = new InfraestructuraH6AmbienteadministrativoServAlimentacion();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoServAlimentacion:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a InfraestructuraH6AmbienteadministrativoServAlimentacion entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoServAlimentacion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoServAlimentacion entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoServAlimentacion:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH6AmbienteadministrativoServAlimentacion entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoServAlimentacion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoServAlimentacion entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoServAlimentacion:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a InfraestructuraH6AmbienteadministrativoServAlimentacion entity.
    *
    * @param InfraestructuraH6AmbienteadministrativoServAlimentacion $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH6AmbienteadministrativoServAlimentacion $entity)
    {
        $form = $this->createForm(new InfraestructuraH6AmbienteadministrativoServAlimentacionType(), $entity, array(
            'action' => $this->generateUrl('infrah6ambadmservalimentacion_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH6AmbienteadministrativoServAlimentacion entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoServAlimentacion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoServAlimentacion entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('infrah6ambadmservalimentacion_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoServAlimentacion:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a InfraestructuraH6AmbienteadministrativoServAlimentacion entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoServAlimentacion')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoServAlimentacion entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('infrah6ambadmservalimentacion'));
    }

    /**
     * Creates a form to delete a InfraestructuraH6AmbienteadministrativoServAlimentacion entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('infrah6ambadmservalimentacion_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
