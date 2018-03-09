<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoInterDormitorios;
use Sie\AppWebBundle\Form\InfraestructuraH6AmbienteadministrativoInterDormitoriosType;

/**
 * InfraestructuraH6AmbienteadministrativoInterDormitorios controller.
 *
 */
class InfraestructuraH6AmbienteadministrativoInterDormitoriosController extends Controller{

    /**
     * Lists all InfraestructuraH6AmbienteadministrativoInterDormitorios entities.
     *
     */
    public function indexAction(Request $request){
        // dump($request);die;
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios')->findAll();

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new InfraestructuraH6AmbienteadministrativoInterDormitorios entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH6AmbienteadministrativoInterDormitorios();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('infrah6interdormitorios_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a InfraestructuraH6AmbienteadministrativoInterDormitorios entity.
     *
     * @param InfraestructuraH6AmbienteadministrativoInterDormitorios $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH6AmbienteadministrativoInterDormitorios $entity, $interEstId)
    {
        $form = $this->createForm(new InfraestructuraH6AmbienteadministrativoInterDormitoriosType(), $entity, array(
            // 'action' => $this->generateUrl('infrah6interdormitorios_create'),
            'method' => 'POST',
        ));
        // $form->add('submit', 'submit', array('label' => 'Create'));
        $form->add('interEstId', 'text', array('mapped'=>false,'label' => 'id', 'data'=>$interEstId));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH6AmbienteadministrativoInterDormitorios entity.
     *
     */
    public function newAction(Request $request){

        dump($request);die;
        $entity = new InfraestructuraH6AmbienteadministrativoInterDormitorios();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a InfraestructuraH6AmbienteadministrativoInterDormitorios entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoInterDormitorios entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH6AmbienteadministrativoInterDormitorios entity.
     *
     */
    public function editAction($id){
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoInterDormitorios entity.');
        }
        
        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a InfraestructuraH6AmbienteadministrativoInterDormitorios entity.
    *
    * @param InfraestructuraH6AmbienteadministrativoInterDormitorios $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH6AmbienteadministrativoInterDormitorios $entity)
    {
        $form = $this->createForm(new InfraestructuraH6AmbienteadministrativoInterDormitoriosType(), $entity, array(
            'action' => $this->generateUrl('infrah6interdormitorios_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH6AmbienteadministrativoInterDormitorios entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoInterDormitorios entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('infrah6interdormitorios_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a InfraestructuraH6AmbienteadministrativoInterDormitorios entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoInterDormitorios entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('infrah6interdormitorios'));
    }

    /**
     * Creates a form to delete a InfraestructuraH6AmbienteadministrativoInterDormitorios entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('infrah6interdormitorios_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    public function addInternadoEstudianteAction(Request $request){
        
        //get the send values
        $interEstId = $request->get('interEstId');
        // dump($interEstId);die;
        $entity = new InfraestructuraH6AmbienteadministrativoInterDormitorios();
        // $form   = $this->createCreateForm($entity, $interEstId);
        $form   = $this->createCreateForm($entity, $interEstId);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    public function saveNewInternadoEstudianteAction(Request $request){
        //get the send values
        $form = $request->get('sie_appwebbundle_infraestructurah6ambienteadministrativointerdormitorios');
        
         //create db conexion
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {

            //get send values to show the correct form
            // $ambienteadministrativoid = $form['ambienteadministrativoid'];
            // check if the data exist to do the save or update            
            $entity = new InfraestructuraH6AmbienteadministrativoInterDormitorios();
            
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('infraestructura_h6_ambienteadministrativo_inter_dormitorios');")->execute();
            $entity->setN34AmbienteTipo($em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteGeneroTipo')->find($form['n34AmbienteTipo']));
            $entity->setN34AmbienteCantidad($form['n34AmbienteCantidad']);
            $entity->setN34AmbienteArea($form['n34AmbienteArea']);
            $entity->setEstadoTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadoGeneral')->find($form['estadoTipo']));
            // $entity->setFechaRegistro(new \DateTime('now'));
            $entity->setN34AmbienteCamaLiteras($form['n34AmbienteCamaLiteras']);
            $entity->setN34AmbienteCamaSimples($form['n34AmbienteCamaSimples']);
            $entity->setN34AmbienteCamaOtros($form['n34AmbienteCamaOtros']);
            $entity->setN34EsAmbienteCieloFal($form['n34EsAmbienteCieloFal']);
            $entity->setN34AmbienteCieloFalTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n34AmbienteCieloFalTipo']));
            $entity->setN34EsAmbienteMuros($form['n34EsAmbienteMuros']);
             $entity->setN34AmbienteMuroMatTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMurosMaterialTipo')->find($form['n34AmbienteMuroMatTipo']));
             $entity->setN34AmbienteMuroCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMurosCaracTipo')->find($form['n34AmbienteMuroCaracTipo']));
             $entity->setN34EsAmbientePuerta($form['n34EsAmbientePuerta']);
             $entity->setN341SeguroTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPuertasSeguroTipo')->find($form['n341SeguroTipo']));
             $entity->setN342AbreTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPuertasAbreTipo')->find($form['n342AbreTipo']));
             $entity->setN34EsAmbienteRevestimiento($form['n34EsAmbienteRevestimiento']);
             $entity->setN34AmbienteRevestMatTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenRevestimientoMaterialTipo')->find($form['n34AmbienteRevestMatTipo']));
             $entity->setN34AmbienteRevestCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n34AmbienteRevestCaracTipo']));
            $entity->setN34EsAmbienteVentana($form['n34EsAmbienteVentana']);
            $entity->setN34AmbienteVentanaTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenVentanasCaracTipo')->find($form['n34AmbienteVentanaTipo']));
            $entity->setN34EsAmbientePiso($form['n34EsAmbientePiso']);
            $entity->setN34AmbientePisoMatTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPisosMaterialTipo')->find($form['n34AmbientePisoMatTipo']));
            $entity->setN34AmbientePisoCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n34AmbientePisoCaracTipo']));
            $entity->setN34EsAmbienteTecho($form['n34EsAmbienteTecho']);
            $entity->setInfraestructuraH6AmbienteadministrativoInternadoEst($em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInternadoEst')->find($form['interEstId']));

            

            $em->persist($entity);
            $em->flush();

            $em->getConnection()->commit();

            $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios')->findBy(array(
                'infraestructuraH6AmbienteadministrativoInternadoEst'=> $form['interEstId']
            ));
            return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoInterDormitorios:index.html.twig', array(
                'entities' => $entities,
            ));
            
        } catch (Exception $e) {
                 echo $e->getTraceAsString();
                 $em->getConnection()->rollback();
                 $em->close();
                 throw $e;
        }
    }
}
