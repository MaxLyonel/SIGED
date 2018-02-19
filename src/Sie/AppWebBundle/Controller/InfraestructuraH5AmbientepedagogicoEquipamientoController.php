<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoEquipamiento;
use Sie\AppWebBundle\Form\InfraestructuraH5AmbientepedagogicoEquipamientoType;

/**
 * InfraestructuraH5AmbientepedagogicoEquipamiento controller.
 *
 */
class InfraestructuraH5AmbientepedagogicoEquipamientoController extends Controller
{

    /**
     * Lists all InfraestructuraH5AmbientepedagogicoEquipamiento entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoEquipamiento')->findAll();

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoEquipamiento:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * [fillDataEquipamientoAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function fillDataEquipamientoAction(Request $request){
         // create db conexion
        $em= $this->getDoctrine()->getManager();
        // get the send values
        $h5AmbientepedagogicoId = $request->get('H5AmbientepedagogicoId');

        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoEquipamiento')->findBy(array(
            'infraestructuraH5Ambientepedagogico'=>$h5AmbientepedagogicoId
        ));

        $arrDataEquipamiento = array();
        if($entities){
            foreach ($entities as $equipamiento) {
                $arrDataEquipamiento[] = array(
                                        'id'                                  =>  $equipamiento->getId(),
                                        'n53Cantidad'                         =>  $equipamiento->getN53Cantidad(),
                                        'n53EstadoTipo'                       =>  $equipamiento->getN53EstadoTipo()->getId(),
                                        'n53EquiposTipo'                      =>  $equipamiento->getN53EquiposTipo()->getId(),
                                        'infraestructuraH5Ambientepedagogico' =>  $equipamiento->getInfraestructuraH5Ambientepedagogico()->getId()
                );


            }
        }

         // set the status data 
        $objStatusTipo = $em->getRepository('SieAppWebBundle:InfraestructuraGenEstadoMobEquipTipo')->findAll();
        $arrDataStatusTipo = array();
        if($objStatusTipo){
            foreach ($objStatusTipo as $statusType) {
                $arrDataStatusTipo[$statusType->getId()] = $statusType->getDescripcion();
            }
        }

        $objInfraestructuraH5AmbientepedagogicoEquipamientoTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoEquipamientoTipo')->findAll();
        $arrDataEquiposTipo = array();

        if($objInfraestructuraH5AmbientepedagogicoEquipamientoTipo){
            foreach ($objInfraestructuraH5AmbientepedagogicoEquipamientoTipo as $equipoTipo) {
                $arrDataEquiposTipo[$equipoTipo->getId()] = $equipoTipo->getDescripcion();
            }
        }

         //return the json data
        $response = new JsonResponse();
        return $response->setData(array(
            'arrDataEquipamiento' => $arrDataEquipamiento,
            'arrDataEquiposTipo' => $arrDataEquiposTipo,
            'arrDataStatusTipo' => $arrDataStatusTipo,
        ));



        // dump($entities);die;
    }
    /**
     * Creates a new InfraestructuraH5AmbientepedagogicoEquipamiento entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH5AmbientepedagogicoEquipamiento();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('infrah5ambpedagogicoequipamiento_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoEquipamiento:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a InfraestructuraH5AmbientepedagogicoEquipamiento entity.
     *
     * @param InfraestructuraH5AmbientepedagogicoEquipamiento $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH5AmbientepedagogicoEquipamiento $entity)
    {
        $form = $this->createForm(new InfraestructuraH5AmbientepedagogicoEquipamientoType(), $entity, array(
            'action' => $this->generateUrl('infrah5ambpedagogicoequipamiento_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH5AmbientepedagogicoEquipamiento entity.
     *
     */
    public function newAction(Request $request){
        // create db conexion
        $em= $this->getDoctrine()->getManager();
        // get the send values
        $h5AmbientepedagogicoId = $request->get('H5AmbientepedagogicoId');
        //get the values about the row selected
        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5Ambientepedagogico')->find($h5AmbientepedagogicoId);
        // $form   = $this->createCreateForm($entity);
        $form   = $this->newEquipamientoForm($h5AmbientepedagogicoId);

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoEquipamiento:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }
    /**
     * [newEquipamientoForm description]
     * @param  [type] $h5AmbientepedagogicoId [description]
     * @return [type]                         [description]
     */
    private function newEquipamientoForm($h5AmbientepedagogicoId){
        return $this->createFormBuilder()
                ->add('h5AmbientepedagogicoId', 'text', array('mapped'=>false, 'data'=> $h5AmbientepedagogicoId))
                ->getForm()
                ;

    }

    /**
     * Finds and displays a InfraestructuraH5AmbientepedagogicoEquipamiento entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoEquipamiento')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH5AmbientepedagogicoEquipamiento entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoEquipamiento:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH5AmbientepedagogicoEquipamiento entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoEquipamiento')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH5AmbientepedagogicoEquipamiento entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoEquipamiento:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a InfraestructuraH5AmbientepedagogicoEquipamiento entity.
    *
    * @param InfraestructuraH5AmbientepedagogicoEquipamiento $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH5AmbientepedagogicoEquipamiento $entity)
    {
        $form = $this->createForm(new InfraestructuraH5AmbientepedagogicoEquipamientoType(), $entity, array(
            'action' => $this->generateUrl('infrah5ambpedagogicoequipamiento_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH5AmbientepedagogicoEquipamiento entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoEquipamiento')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH5AmbientepedagogicoEquipamiento entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('infrah5ambpedagogicoequipamiento_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoEquipamiento:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a InfraestructuraH5AmbientepedagogicoEquipamiento entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoEquipamiento')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InfraestructuraH5AmbientepedagogicoEquipamiento entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('infrah5ambpedagogicoequipamiento'));
    }

    /**
     * Creates a form to delete a InfraestructuraH5AmbientepedagogicoEquipamiento entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('infrah5ambpedagogicoequipamiento_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

     /**
     * Creates a new InfraestructuraH5AmbientepedagogicoMobiliario entity.
     *
     */
    public function savenewAction(Request $request){
        
        //cretae the db conexion
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        
        try {

                // get the send values
                $form = $request->get('form');
                // dump($form);
                // dump($request);
                // die;
                // get the id of mobiliario
                $h5AmbientepedagogicoId = $form['h5AmbientepedagogicoId'];
                //find the mobiliario data before to save
                $objAmbPedagogicoEquipamiento = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoEquipamiento')->findBy(array(
                    'infraestructuraH5Ambientepedagogico' => $h5AmbientepedagogicoId,

                ));
                // remove the element to the mobiliario data
                foreach ($objAmbPedagogicoEquipamiento as $elementAmbPedagogicoEquipamiento) {
                    $em->remove($elementAmbPedagogicoEquipamiento);
                }        
                $em->flush();        
                //create the limit to do the save    
                $limitSave = sizeof($form['n53EquiposTipo']);
                /*dump($limitSave);
                dump($form);die;*/
                $ind=0;
                while ($ind < $limitSave) {
                    $entity = new InfraestructuraH5AmbientepedagogicoEquipamiento();

                    $entity->setN53Cantidad($form['n53Cantidad'][$ind]);
                    $entity->setN53EstadoTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadoMobEquipTipo')->find($form['n53EstadoTipo'][$ind]));
                    $entity->setN53EquiposTipo($em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoEquiposTipo')->find($form['n53EquiposTipo'][$ind]));
                    $entity->setInfraestructuraH5Ambientepedagogico($em->getRepository('SieAppWebBundle:InfraestructuraH5Ambientepedagogico')->find($form['h5AmbientepedagogicoId']));

                    $em->persist($entity);    

                    $ind++;
                }

                $em->flush();    

                $em->getConnection()->commit();
                
                 die('done');
            
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }





        /*$entity = new InfraestructuraH5AmbientepedagogicoMobiliario();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('h5mobiliario_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoMobiliario:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));*/
    }
}
