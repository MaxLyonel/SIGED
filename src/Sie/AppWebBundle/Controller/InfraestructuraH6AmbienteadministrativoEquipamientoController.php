<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoEquipamiento;
use Sie\AppWebBundle\Form\InfraestructuraH6AmbienteadministrativoEquipamientoType;

/**
 * InfraestructuraH6AmbienteadministrativoEquipamiento controller.
 *
 */
class InfraestructuraH6AmbienteadministrativoEquipamientoController extends Controller
{

    /**
     * Lists all InfraestructuraH6AmbienteadministrativoEquipamiento entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoEquipamiento')->findAll();

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoEquipamiento:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new InfraestructuraH6AmbienteadministrativoEquipamiento entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH6AmbienteadministrativoEquipamiento();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('infrah6ambadmequipamiento_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoEquipamiento:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a InfraestructuraH6AmbienteadministrativoEquipamiento entity.
     *
     * @param InfraestructuraH6AmbienteadministrativoEquipamiento $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH6AmbienteadministrativoEquipamiento $entity)
    {
        $form = $this->createForm(new InfraestructuraH6AmbienteadministrativoEquipamientoType(), $entity, array(
            'action' => $this->generateUrl('infrah6ambadmequipamiento_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH6AmbienteadministrativoEquipamiento entity.
     *
     */
    public function newAction(Request $request){
         // create DB conexion
        $em = $this->getDoctrine()->getManager();
         // get the send values
        $H6AmbienteAdmAmbienteId = $request->get('H6AmbienteAdmAmbienteId');
        //look for the data choose
        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoAmbiente')->find($H6AmbienteAdmAmbienteId);
        // cretae form to create the rows
        $form   = $this->newEquipamientoForm($H6AmbienteAdmAmbienteId);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoEquipamiento:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }
    /**
     * [newEquipamientoForm description]
     * @param  [type] $H6AmbienteAdmAmbienteId [description]
     * @return [type]                          [description]
     */
    private function newEquipamientoForm($H6AmbienteAdmAmbienteId){
        return $this->createFormBuilder()
                ->add('H6AmbienteAdmAmbienteId', 'text', array('mapped'=>false, 'data'=> $H6AmbienteAdmAmbienteId))
                ->getForm()
                ;
    }


    /**
     * Finds and displays a InfraestructuraH6AmbienteadministrativoEquipamiento entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoEquipamiento')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoEquipamiento entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoEquipamiento:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH6AmbienteadministrativoEquipamiento entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoEquipamiento')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoEquipamiento entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoEquipamiento:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a InfraestructuraH6AmbienteadministrativoEquipamiento entity.
    *
    * @param InfraestructuraH6AmbienteadministrativoEquipamiento $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH6AmbienteadministrativoEquipamiento $entity)
    {
        $form = $this->createForm(new InfraestructuraH6AmbienteadministrativoEquipamientoType(), $entity, array(
            'action' => $this->generateUrl('infrah6ambadmequipamiento_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH6AmbienteadministrativoEquipamiento entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoEquipamiento')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoEquipamiento entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('infrah6ambadmequipamiento_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoEquipamiento:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a InfraestructuraH6AmbienteadministrativoEquipamiento entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoEquipamiento')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoEquipamiento entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('infrah6ambadmequipamiento'));
    }

    /**
     * Creates a form to delete a InfraestructuraH6AmbienteadministrativoEquipamiento entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('infrah6ambadmequipamiento_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
    /**
     * [fillDatah6AmbienteMobiliarioAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function fillDatah6AmbienteEquipamientoAction(Request $request){

        // dump($request);die;
        // create DB conexion
        $em = $this->getDoctrine()->getManager();
        // get the send values
        $H6AmbienteAdmAmbienteId = $request->get('H6AmbienteAdmAmbienteId');
        //find the mobiliario to this ambiente
        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoEquipamiento')->findBy(array(
            'infraestructuraH6AmbienteadministrativoAmbiente' => $H6AmbienteAdmAmbienteId,
        ));

        $arrH6Equipamiento = array();
        if($entities){
            foreach ($entities as $equipamiento) {
                $arrH6Equipamiento[] = array(
                    'id'                                                  => $equipamiento->getId(),
                    'n63Cantidad'                                         => $equipamiento->getN63Cantidad(),
                    'n63EquipamientoTipo'                                 => $equipamiento->getN63EquipamientoTipo()->getId(),
                    'n63EstadoTipo'                                       => $equipamiento->getN63EstadoTipo()->getId(),
                    'infraestructuraH6AmbienteadministrativoAmbiente'     => $equipamiento->setInfraestructuraH6AmbienteadministrativoAmbiente(),

                );
            }
        }

         // set the mobiliario type data            
        $objEquipamientoTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoEquipamientoTipo')->findAll();
        $arrDataEquipamientoTipo = array();
        if($objEquipamientoTipo){
            foreach ($objEquipamientoTipo as $equipamientoType) {
                $arrDataEquipamientoTipo[$equipamientoType->getId()] = $equipamientoType->getDescripcion();
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

        //return the json data
        $response = new JsonResponse();
        return $response->setData(array(
            'arrH6Equipamiento' => $arrH6Equipamiento,
            'arrDataEquipamientoTipo' => $arrDataEquipamientoTipo,
            'arrDataStatusTipo' => $arrDataStatusTipo,
        ));

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

                // get the id of mobiliario
                $H6AmbienteAdmAmbienteId = $form['H6AmbienteAdmAmbienteId'];
                //find the mobiliario data before to save
                     
                $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoEquipamiento')->findBy(array(
                    'infraestructuraH6AmbienteadministrativoAmbiente' => $H6AmbienteAdmAmbienteId,
                ));
                
                // remove the element to the mobiliario data
                foreach ($entities as $element) {
                    $em->remove($element);
                }        
                $em->flush();        
                //create the limit to do the save    
                $limitSave = sizeof($form['n63EquipamientoTipo']);
                /*dump($limitSave);
                dump($form);die;*/
                $ind=0;
                while ($ind < $limitSave) {
                    $entity = new InfraestructuraH6AmbienteadministrativoEquipamiento();

                    $entity->setN63Cantidad($form['n63Cantidad'][$ind]);
                    $entity->setN63EstadoTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadoMobEquipTipo')->find($form['n63EstadoTipo'][$ind]));
                    $entity->setN63EquipamientoTipo($em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoEquipamientoTipo')->find($form['n63EquipamientoTipo'][$ind]));
                    $entity->setInfraestructuraH6AmbienteadministrativoAmbiente($em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoAmbiente')->find($form['H6AmbienteAdmAmbienteId']));

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
