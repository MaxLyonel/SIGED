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
        

        $infraestructuraJuridiccionGeograficaId = $request->get('infraestructuraJuridiccionGeograficaId');
// dump($infraestructuraJuridiccionGeograficaId);die;
        $entity = new InfraestructuraH5Ambientepedagogico();
        $form   = $this->createCreateForm($entity, $infraestructuraJuridiccionGeograficaId);

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
    private function createCreateForm(InfraestructuraH5Ambientepedagogico $entity, $infraestructuraJuridiccionGeograficaId)
    {
        $form = $this->createForm(new InfraestructuraH5AmbientepedagogicoType(), $entity, array(
            // 'action' => $this->generateUrl('infraestructurah5ambientepedagogico_create'),
            'method' => 'POST',
        ));

        // $form->add('submit', 'submit', array('label' => 'Create'));
        $form->add('infraestructuraJuridiccionGeograficaId', 'text', array('mapped'=>false,'data' => $infraestructuraJuridiccionGeograficaId));

        return $form;
    }

    public function saveNewPedagogicoAction(Request $request){
        
        // cretae db conexion var
        $em =  $this->getDoctrine()->getManager();

        //get the send data
        $form = $request->get('sie_appwebbundle_infraestructurah5ambientepedagogico');
        // dump($form);die;
        $entity = new InfraestructuraH5Ambientepedagogico();

            // $entity->setN51AmbienteTipo($em->getRepository('SieAppWebBundle:InfraestructuraH5AmbienteTipo')->find($form['n51AmbienteTipo']) );
            // $entity->setN51NroBloque($form['n51NroBloque']);
            // $entity->setN51NroPiso($form['n51NroPiso']);
            // $entity->setN51AmbienteAnchoMts($form['n51AmbienteAnchoMts']);
            // $entity->setN51AmbienteLargoMts($form['n51AmbienteLargoMts']);
            // $entity->setN51AmbienteAltoMts($form['n51AmbienteAltoMts']);
            // $entity->setN51CapacidadAmbiente($form['n51CapacidadAmbiente']);
            // $entity->setN51EsUsoAmbiente($form['n51EsUsoAmbiente']);
            // $entity->setN51EsUsoUniversal($form['n51EsUsoUniversal']);
            // $entity->setN51EsUsoBth($form['n51EsUsoBth']);
            // $entity->setN51EsAmbienteTecho($form['n51EsAmbienteTecho']);
            // $entity->setN51EsIluminacionElectrica($form['n51EsIluminacionElectrica']);
            // $entity->setN51EsIluminacionNatural($form['n51EsIluminacionNatural']);
            // $entity->setN51EsAmbienteCieloFal($form['n51EsAmbienteCieloFal']);
            // $entity->setN51AmbienteCieloFalTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n51AmbienteCieloFalTipo']) );
            // $entity->setN51EsAmbienteMuros($form['n51EsAmbienteMuros']);
            // $entity->setN51AmbienteMuroMatTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMurosMaterialTipo')->find($form['n51AmbienteMuroMatTipo']) );
            // $entity->setN51AmbienteMuroCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMurosCaracTipo')->find($form['n51AmbienteMuroCaracTipo']) );
            // $entity->setN51EsAmbientePuerta($form['n51EsAmbientePuerta']);
            // $entity->setN511SeguroTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPuertasSeguroTipo')->find($form['n511SeguroTipo']) );
            // $entity->setN512AbreTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPuertasAbreTipo')->find($form['n512AbreTipo']) );
            // $entity->setN51EsAmbienteRevestimiento($form['n51EsAmbienteRevestimiento']);
            // $entity->setN51AmbienteRevestCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n51AmbienteRevestCaracTipo']) );
            // $entity->setN51AmbienteRevestMatTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenRevestimientoMaterialTipo')->find($form['n51AmbienteRevestMatTipo']) );
            // $entity->setN51EsAmbienteVentana($form['n51EsAmbienteVentana']);
            // $entity->setN51AmbienteVentanaTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenVentanasCaracTipo')->find($form['n51AmbienteVentanaTipo']) );
            // $entity->setN51EsAmbientePiso($form['n51EsAmbientePiso']);
            // $entity->setN51AmbientePisoCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n51AmbientePisoCaracTipo']) );
            // $entity->setN51AmbientePisoMatTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPisosMaterialTipo')->find($form['n51AmbientePisoMatTipo']) );
            // $entity->setInfraestructuraJuridiccionGeografica($em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($form['infraestructuraJuridiccionGeograficaId']) );

            /*$em->persist($entity);
            $em->flush();*/


             
            $entitiesH5Ambientepedagogico = $em->getRepository('SieAppWebBundle:InfraestructuraH5Ambientepedagogico')->findBy(array(
                'infraestructuraJuridiccionGeografica'=> $form['infraestructuraJuridiccionGeograficaId']
            ));

            return $this->render('SieAppWebBundle:InfraestructuraH5Ambientepedagogico:index.html.twig', array(
                'entities' => $entitiesH5Ambientepedagogico,
                'infraestructuraJuridiccionGeograficaId' => $form['infraestructuraJuridiccionGeograficaId'],
            ));
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
