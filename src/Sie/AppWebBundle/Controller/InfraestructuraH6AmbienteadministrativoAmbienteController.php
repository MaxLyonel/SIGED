<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoAmbiente;
use Sie\AppWebBundle\Form\InfraestructuraH6AmbienteadministrativoAmbienteType;

/**
 * InfraestructuraH6AmbienteadministrativoAmbiente controller.
 *
 */
class InfraestructuraH6AmbienteadministrativoAmbienteController extends Controller
{

    /**
     * Lists all InfraestructuraH6AmbienteadministrativoAmbiente entities.
     *
     */
    // public function indexAction()
    // {
    //     $em = $this->getDoctrine()->getManager();

    //     $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoAmbiente')->findAll();

    //     return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoAmbiente:index.html.twig', array(
    //         'entities' => $entities,
    //     ));
    // }
      public function indexAction(Request $request){
        
        //creaet db conexion
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
            return $this->redirect($this->generateUrl('infrah6ambadmambiente_listambiente', array('id'=>$objAmbienteAdministrativo->getId() )));
           }else{
            return $this->redirect($this->generateUrl('infrah6ambadmambiente_listambiente', array('id'=>'' )));
           }
        }

     /**
     * Lists all InfraestructuraH6AmbienteadministrativoAmbiente entities.
     *
     */
    public function listambienteAction(Request $request){
        
        $ambienteAdministrativoId = $request->get('id');
       
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoAmbiente')->findBy(array(
            'infraestructuraH6Ambienteadministrativo' => $ambienteAdministrativoId,
        ));

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoAmbiente:index.html.twig', array(
            'entities' => $entities,
            'ambienteAdministrativoId' => $ambienteAdministrativoId,
        ));
    }
    /**
     * [addnewh6ambienteAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function addnewh6ambienteAction(Request $request){
        
        //get the send data
        $ambienteAdministrativoId = $request->get('ambienteAdministrativoId');
        $entity = new InfraestructuraH6AmbienteadministrativoAmbiente();
        $form   = $this->createCreateForm($entity, $ambienteAdministrativoId);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoAmbiente:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));

    }
    /**
     * [saveNewPedagogicoAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function saveNewh6AmbienteAction(Request $request){
        // cretae db conexion var
        $em =  $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        //get the send data
        $form = $request->get('sie_appwebbundle_infraestructurah6ambienteadministrativoambiente');
        
        // dump($form);die;
        
        try {

            $entity = new InfraestructuraH6AmbienteadministrativoAmbiente();

            $entity->setN11AmbienteadministrativoTipo($em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoTipo')->find($form['n11AmbienteadministrativoTipo']) );
            $entity->setN61AmbienteAreaAdm($form['n61AmbienteAreaAdm']);
            $entity->setEstadoTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadoGeneral')->find($form['estadoTipo']) );
            $entity->setN61EsAmbienteCieloFal($form['n61EsAmbienteCieloFal']);
            $entity->setN61AmbienteCieloFalTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n61AmbienteCieloFalTipo']) );
            $entity->setN61EsAmbienteMuros($form['n61EsAmbienteMuros']);
            $entity->setN61AmbienteMuroMatTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMurosMaterialTipo')->find($form['n61AmbienteMuroMatTipo']) );
            $entity->setN61AmbienteMuroCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMurosCaracTipo')->find($form['n61AmbienteMuroCaracTipo']) );
            $entity->setN61EsAmbientePuerta($form['n61EsAmbientePuerta']);
            $entity->setN611SeguroTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPuertasSeguroTipo')->find($form['n611SeguroTipo']) );
            // $entity->setN612AbreTipoId($em->getRepository('SieAppWebBundle:InfraestructuraGenPuertasAbreTipo')->find($form['n512AbreTipo']) );
            $entity->setN612AbreTipoId($form['n612AbreTipoId']);
            $entity->setN61EsAmbienteRevestimiento($form['n61EsAmbienteRevestimiento']);
            $entity->setN61AmbienteRevestMatTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenRevestimientoMaterialTipo')->find($form['n61AmbienteRevestMatTipo']) );
            $entity->setN61AmbienteRevestCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n61AmbienteRevestCaracTipo']) );
            $entity->setN61EsAmbienteVentana($form['n61EsAmbienteVentana']);
            $entity->setN61AmbienteVentanaTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenVentanasCaracTipo')->find($form['n61AmbienteVentanaTipo']) );
            $entity->setN61EsAmbientePiso($form['n61EsAmbientePiso']);
            $entity->setN61AmbientePisoMatTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPisosMaterialTipo')->find($form['n61AmbientePisoMatTipo']) );
            $entity->setN61AmbientePisoCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n61AmbientePisoCaracTipo']) );           
            $entity->setN61EsAmbienteTecho($form['n61EsAmbienteTecho']);
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
   
        return $this->redirect($this->generateUrl('infrah6ambadmambiente_listambiente', array('id'=>$form['ambienteAdministrativoId'] )));
    }








    /**
     * Creates a new InfraestructuraH6AmbienteadministrativoAmbiente entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH6AmbienteadministrativoAmbiente();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('infrah6ambadmambiente_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoAmbiente:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a InfraestructuraH6AmbienteadministrativoAmbiente entity.
     *
     * @param InfraestructuraH6AmbienteadministrativoAmbiente $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH6AmbienteadministrativoAmbiente $entity, $ambienteAdministrativoId)
    {
        $form = $this->createForm(new InfraestructuraH6AmbienteadministrativoAmbienteType(), $entity, array(
            // 'action' => $this->generateUrl('infrah6ambadmambiente_create'),
            'method' => 'POST',
        ));

        // $form->add('submit', 'submit', array('label' => 'Create'));
        $form->add('ambienteAdministrativoId', 'text', array('mapped'=>false,'data' => $ambienteAdministrativoId));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH6AmbienteadministrativoAmbiente entity.
     *
     */
    public function newAction()
    {
        $entity = new InfraestructuraH6AmbienteadministrativoAmbiente();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoAmbiente:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a InfraestructuraH6AmbienteadministrativoAmbiente entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoAmbiente')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoAmbiente entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoAmbiente:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH6AmbienteadministrativoAmbiente entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoAmbiente')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoAmbiente entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoAmbiente:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a InfraestructuraH6AmbienteadministrativoAmbiente entity.
    *
    * @param InfraestructuraH6AmbienteadministrativoAmbiente $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH6AmbienteadministrativoAmbiente $entity)
    {
        $form = $this->createForm(new InfraestructuraH6AmbienteadministrativoAmbienteType(), $entity, array(
            'action' => $this->generateUrl('infrah6ambadmambiente_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH6AmbienteadministrativoAmbiente entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoAmbiente')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoAmbiente entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('infrah6ambadmambiente_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoAmbiente:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a InfraestructuraH6AmbienteadministrativoAmbiente entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoAmbiente')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoAmbiente entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('infrah6ambadmambiente'));
    }

    /**
     * Creates a form to delete a InfraestructuraH6AmbienteadministrativoAmbiente entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('infrah6ambadmambiente_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
