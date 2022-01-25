<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento;
use Sie\AppWebBundle\Form\InfraestructuraH5AmbientepedagogicoDeportivoEquipamientoType;

/**
 * InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento controller.
 *
 */
class InfraestructuraH5AmbientepedagogicoDeportivoEquipamientoController extends Controller
{

    /**
     * Lists all InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento entities.
     *
     */
    public function indexAction(Request $request){
        //create db conexion
        $em = $this->getDoctrine()->getManager();
                //get the send values
        $h5AmbientepedagogicoId = $request->get('H5AmbientepedagogicoId');


        return $this->redirect($this->generateUrl('h5ambpedagogicodeportivoequipamiento_new', array('id'=>$h5AmbientepedagogicoId)));

        // $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento')->findAll();

        // return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento:index.html.twig', array(
        //     'entities' => $entities,
        // ));
    }
    /**
     * Creates a new InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('h5ambpedagogicodeportivoequipamiento_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento entity.
     *
     * @param InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento $entity)
    {
        $form = $this->createForm(new InfraestructuraH5AmbientepedagogicoDeportivoEquipamientoType(), $entity, array(
            'action' => $this->generateUrl('h5ambpedagogicodeportivoequipamiento_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento entity.
     *
     */
    public function newAction(Request $request){

        //get the send values
        $h5AmbientepedagogicoId = $request->get('id');

        $em= $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivo')->find($h5AmbientepedagogicoId);
        
        // $form   = $this->createCreateForm($entity);
        $form   = $this->newdDeportivoEquipamientoForm($h5AmbientepedagogicoId);

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    private function newdDeportivoEquipamientoForm($h5AmbientepedagogicoId){
        return $this->createFormBuilder()
                ->add('h5AmbientepedagogicoId', 'text', array('mapped'=>false, 'data'=> $h5AmbientepedagogicoId))
                ->getForm()
                ;

    }

    /**
     * Finds and displays a InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento entity.
    *
    * @param InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento $entity)
    {
        $form = $this->createForm(new InfraestructuraH5AmbientepedagogicoDeportivoEquipamientoType(), $entity, array(
            'action' => $this->generateUrl('h5ambpedagogicodeportivoequipamiento_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('h5ambpedagogicodeportivoequipamiento_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('h5ambpedagogicodeportivoequipamiento'));
    }

    /**
     * Creates a form to delete a InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('h5ambpedagogicodeportivoequipamiento_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
    /**
     * [fillDataEquipamientoAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function fillDataDeportivoEquipamientoAction(Request $request){
         // create db conexion
        $em= $this->getDoctrine()->getManager();
        // get the send values
        $h5AmbientepedagogicoId = $request->get('H5AmbientepedagogicoId');

        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento')->findBy(array(
            'infraestructuraH5AmbientepedagogicoDeportivo'=>$h5AmbientepedagogicoId
        ));

        $arrDataDeportivoEquipamiento = array();
        if($entities){
            foreach ($entities as $equipamiento) {
                $arrDataDeportivoEquipamiento[] = array(
                                        'id'                                           =>  $equipamiento->getId(),
                                        'n531Cantidad'                                  =>  $equipamiento->getN531Cantidad(),
                                        'n531EstadoEquipamientoTipo'                   =>  $equipamiento->getN531EstadoEquipamientoTipo()->getId(),
                                        'n531EquipamientoTipo'                         =>  $equipamiento->getN531EquipamientoTipo()->getId(),
                                        'infraestructuraH5AmbientepedagogicoDeportivo' =>  $equipamiento->getInfraestructuraH5AmbientepedagogicoDeportivo()->getId()
                );


            }
        }
// dump($arrDataDeportivoEquipamiento);die;
         // set the status data 
        $objStatusTipo = $em->getRepository('SieAppWebBundle:InfraestructuraGenEstadoMobEquipTipo')->findAll();
        $arrDataStatusTipo = array();
        if($objStatusTipo){
            foreach ($objStatusTipo as $statusType) {
                $arrDataStatusTipo[$statusType->getId()] = $statusType->getDescripcion();
            }
        }

        $objDeportivoEquipamientoTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH4AmbientepedagogicoDeportivoEquimientoTipo')->findAll();
        $arrDataDeportivoEquiposTipo = array();

        if($objDeportivoEquipamientoTipo){
            foreach ($objDeportivoEquipamientoTipo as $equipoTipo) {
                $arrDataDeportivoEquiposTipo[$equipoTipo->getId()] = $equipoTipo->getInfraestructuraAmbiente();
            }
        }
        // dump($arrDataStatusTipo);
        // dump($arrDataDeportivoEquiposTipo);
        // die;

         //return the json data
        $response = new JsonResponse();
        return $response->setData(array(
            'arrDataDeportivoEquipamiento' => $arrDataDeportivoEquipamiento,
            'arrDataDeportivoEquiposTipo' => $arrDataDeportivoEquiposTipo,
            'arrDataStatusTipo' => $arrDataStatusTipo,
        ));



        // dump($entities);die;
    }

      /**
     * [savenewAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function savenewAction(Request $request){

        
        //cretae the db conexion
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        
        try {
            
                // get the send values
                $form = $request->get('form');
                // dump($form);die;
                // dump($request);
                // die;
                // get the id of mobiliario
                $h5AmbientepedagogicoId = $form['h5AmbientepedagogicoId'];
                //find the mobiliario data before to save
                $objAmbPedagogicoEquipamiento = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento')->findBy(array(
                    'infraestructuraH5AmbientepedagogicoDeportivo' => $h5AmbientepedagogicoId,

                ));
                // remove the element to the mobiliario data
                foreach ($objAmbPedagogicoEquipamiento as $elementAmbPedagogicoEquipamiento) {
                    $em->remove($elementAmbPedagogicoEquipamiento);
                }        
                $em->flush();        
                //create the limit to do the save    
                $limitSave = sizeof($form['n531EstadoEquipamientoTipo']);
                /*dump($limitSave);
                dump($form);die;*/
                $ind=0;
                while ($ind < $limitSave) {
                    $entity = new InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento();

                    $entity->setN531EstadoEquipamientoTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadoMobEquipTipo')->find($form['n531EstadoEquipamientoTipo'][$ind]) );
                    $entity->setN531Cantidad($form['n531Cantidad'][$ind]);
                    $entity->setN531EquipamientoTipo($em->getRepository('SieAppWebBundle:InfraestructuraH4AmbientepedagogicoDeportivoEquimientoTipo')->find($form['n531EquipamientoTipo'][$ind]));
                    $entity->setInfraestructuraH5AmbientepedagogicoDeportivo($em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoDeportivo')->find($form['h5AmbientepedagogicoId']));

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
    }
}
