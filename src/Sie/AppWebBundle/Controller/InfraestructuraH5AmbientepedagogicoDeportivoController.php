<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoDeportivo;
use Sie\AppWebBundle\Form\InfraestructuraH5AmbientepedagogicoDeportivoType;

/**
 * InfraestructuraH5AmbientepedagogicoDeportivo controller.
 *
 */
class InfraestructuraH5AmbientepedagogicoDeportivoController extends Controller
{

    /**
     * Lists all InfraestructuraH5AmbientepedagogicoDeportivo entities.
     *
     */
    public function pedagogicoDeportivoAction(Request $request){
        //get the data send
        $infraestructuraJuridiccionGeograficaId = 11392;

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivo')->findBy(array(
            'infraestructuraJuridiccionGeografica'=> $infraestructuraJuridiccionGeograficaId

        ));

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivo:index.html.twig', array(
            'entities' => $entities,
            'infraestructuraJuridiccionGeograficaId' => $infraestructuraJuridiccionGeograficaId,
        ));
    }
    /**
     * Lists all InfraestructuraH5AmbientepedagogicoDeportivo entities.
     *
     */
    public function newAmbPedagogicoDeportivoAction(Request $request){
        // dump($request);die;
         // get the data send   
        $h5AmbientepedagogicoId = $request->get('H5AmbientepedagogicoId');

        $entity = new InfraestructuraH5AmbientepedagogicoDeportivo();
        $form   = $this->createCreateForm($entity, $h5AmbientepedagogicoId);
        
        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }
    /**
     * Lists all InfraestructuraH5AmbientepedagogicoDeportivo entities.
     *
     */
    public function indexAction(Request $request){
        dump($request);die;
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivo')->find();

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivo:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new InfraestructuraH5AmbientepedagogicoDeportivo entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH5AmbientepedagogicoDeportivo();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('infrah5ambpedagogicodeportivo_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }
   /**
     * Creates a new InfraestructuraH5AmbientepedagogicoDeportivo entity.
     *
     */
    public function newSaveAmbPedagogicoDeportivoAction(Request $request){
        // get the data send
        $form = $request->get('sie_appwebbundle_infraestructurah5ambientepedagogicodeportivo');

        // create db cnoexion
        $em = $this->getDoctrine()->getManager();
        // $em->begin()->transaction();

        try {

  
        $entity = new InfraestructuraH5AmbientepedagogicoDeportivo();

        $entity->setN53EsDeportivo($form['n53EsDeportivo']);
        $entity->setN53EsRecreativo($form['n53EsRecreativo']);
        $entity->setN53EsCultural($form['n53EsCultural']);
        $entity->setN53EsUsoUniversal($form['n53EsUsoUniversal']);
        $entity->setN53AmbienteTipo($em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivoAmbienteTipo')->find($form['n53AmbienteTipo']) );
        $entity->setN53AmbienteAreaMts($form['n53AmbienteAreaMts']);
        $entity->setN53AmbienteCapacidad($form['n53AmbienteCapacidad']);
        $entity->setN53EsTechado($form['n53EsTechado']);
        $entity->setN53EsGraderia($form['n53EsGraderia']);
        $entity->setN53AmbienteGraderiaTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n53AmbienteGraderiaTipo']) );
        $entity->setN53EsIluminacionElectrica($form['n53EsIluminacionElectrica']);
        $entity->setN53EsIluminacionNatural($form['n53EsIluminacionNatural']);
        $entity->setN53EsAmbienteCieloFal($form['n53EsAmbienteCieloFal']);
        $entity->setN53AmbienteCieloFalTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n53AmbienteCieloFalTipo']) );
        $entity->setN53EsAmbienteMuros($form['n53EsAmbienteMuros']);
        $entity->setN53AmbienteMuroMatTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMurosMaterialTipo')->find($form['n53AmbienteMuroMatTipo']) );
        $entity->setN53AmbienteMuroCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenMurosCaracTipo')->find($form['n53AmbienteMuroCaracTipo']) );
        $entity->setN53EsAmbientePuerta($form['n53EsAmbientePuerta']);
        $entity->setN511SeguroTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPuertasSeguroTipo')->find($form['n511SeguroTipo']) );
        $entity->setN512AbreTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPuertasAbreTipo')->find($form['n512AbreTipo']) );
        $entity->setN53EsAmbienteRevestimiento($form['n53EsAmbienteRevestimiento']);
        $entity->setN53AmbienteRevestMatTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenRevestimientoMaterialTipo')->find($form['n53AmbienteRevestMatTipo']) );
        $entity->setN53AmbienteRevestCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n53AmbienteRevestCaracTipo']) );
        $entity->setN53EsAmbienteVentana($form['n53EsAmbienteVentana']);
        $entity->setN53AmbienteVentanaTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenVentanasCaracTipo')->find($form['n53AmbienteVentanaTipo']) );
        $entity->setN53EsAmbientePiso($form['n53EsAmbientePiso']);
        $entity->setN53AmbientePisoMatTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenPisosMaterialTipo')->find($form['n53AmbientePisoMatTipo']) );
        $entity->setN53AmbientePisoCaracTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenCaracteristicasInfraTipo')->find($form['n53AmbientePisoCaracTipo']) );
        $entity->setEstadoTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadoGeneral')->find($form['estadoTipo']) );
        $entity->setInfraestructuraJuridiccionGeografica($em->getRepository('SieAppWebBundle:InfraestructuraJuridiccionGeografica')->find($form['h5AmbientepedagogicoId']) );

        $em->persist($entity);
        $em->flush();
        
            
        } catch (Exception $e) {
            
        }

           $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivo')->findBy(array(
            'infraestructuraJuridiccionGeografica'=> $form['h5AmbientepedagogicoId']

        ));

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivo:index.html.twig', array(
            'entities' => $entities,
            'infraestructuraJuridiccionGeograficaId' => $form['h5AmbientepedagogicoId'],
        ));

    }

    /**
     * Creates a form to create a InfraestructuraH5AmbientepedagogicoDeportivo entity.
     *
     * @param InfraestructuraH5AmbientepedagogicoDeportivo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH5AmbientepedagogicoDeportivo $entity, $h5AmbientepedagogicoId){
        $form = $this->createForm(new InfraestructuraH5AmbientepedagogicoDeportivoType(), $entity, array(
            // 'action' => $this->generateUrl('infrah5ambpedagogicodeportivo_create'),
            'method' => 'POST',
        ));

        // $form->add('submit', 'submit', array('label' => 'Create'));
        $form->add('h5AmbientepedagogicoId', 'text', array('mapped'=>false,'data' => $h5AmbientepedagogicoId));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH5AmbientepedagogicoDeportivo entity.
     *
     */
    public function newAction(Request $request){
        // dump($request);die;
        //create DB conexion
        // $em= $this->getDoctrine()->getManager();
        // get the send values
        // $h5AmbientepedagogicoId = $request->get('H5AmbientepedagogicoId');
        $entity = new InfraestructuraH5AmbientepedagogicoDeportivo();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a InfraestructuraH5AmbientepedagogicoDeportivo entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH5AmbientepedagogicoDeportivo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivo:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH5AmbientepedagogicoDeportivo entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH5AmbientepedagogicoDeportivo entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a InfraestructuraH5AmbientepedagogicoDeportivo entity.
    *
    * @param InfraestructuraH5AmbientepedagogicoDeportivo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH5AmbientepedagogicoDeportivo $entity)
    {
        $form = $this->createForm(new InfraestructuraH5AmbientepedagogicoDeportivoType(), $entity, array(
            'action' => $this->generateUrl('infrah5ambpedagogicodeportivo_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH5AmbientepedagogicoDeportivo entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH5AmbientepedagogicoDeportivo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('infrah5ambpedagogicodeportivo_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a InfraestructuraH5AmbientepedagogicoDeportivo entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivo')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InfraestructuraH5AmbientepedagogicoDeportivo entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('infrah5ambpedagogicodeportivo'));
    }

    /**
     * Creates a form to delete a InfraestructuraH5AmbientepedagogicoDeportivo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('infrah5ambpedagogicodeportivo_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
