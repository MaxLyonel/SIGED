<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoMobiliario;
use Sie\AppWebBundle\Form\InfraestructuraH6AmbienteadministrativoMobiliarioType;

/**
 * InfraestructuraH6AmbienteadministrativoMobiliario controller.
 *
 */
class InfraestructuraH6AmbienteadministrativoMobiliarioController extends Controller
{

    /**
     * Lists all InfraestructuraH6AmbienteadministrativoMobiliario entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoMobiliario')->findAll();

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoMobiliario:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new InfraestructuraH6AmbienteadministrativoMobiliario entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH6AmbienteadministrativoMobiliario();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('infrah6ambadmmobiliario_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoMobiliario:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a InfraestructuraH6AmbienteadministrativoMobiliario entity.
     *
     * @param InfraestructuraH6AmbienteadministrativoMobiliario $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH6AmbienteadministrativoMobiliario $entity)
    {
        $form = $this->createForm(new InfraestructuraH6AmbienteadministrativoMobiliarioType(), $entity, array(
            'action' => $this->generateUrl('infrah6ambadmmobiliario_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH6AmbienteadministrativoMobiliario entity.
     *
     */
    public function newAction(Request $request){
        // create DB conexion
        $em = $this->getDoctrine()->getManager();
        // $entity = new InfraestructuraH6AmbienteadministrativoMobiliario();
        // get the send values
        $H6AmbienteAdmAmbienteId = $request->get('H6AmbienteAdmAmbienteId');
        // $form   = $this->createCreateForm($entity);
        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoAmbiente')->find($H6AmbienteAdmAmbienteId);
        $form   = $this->newMobiliarioForm($H6AmbienteAdmAmbienteId);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoMobiliario:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

     /**
     * [newMobiliarioForm description]
     * @param  [type] $h5AmbientepedagogicoId [description]
     * @return [type]                         [description]
     */
    private function newMobiliarioForm($H6AmbienteAdmAmbienteId){
        return $this->createFormBuilder()
                ->add('H6AmbienteAdmAmbienteId', 'text', array('mapped'=>false, 'data'=> $H6AmbienteAdmAmbienteId))
                ->getForm()
                ;
    }

    /**
     * Finds and displays a InfraestructuraH6AmbienteadministrativoMobiliario entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoMobiliario')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoMobiliario entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoMobiliario:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH6AmbienteadministrativoMobiliario entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoMobiliario')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoMobiliario entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoMobiliario:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a InfraestructuraH6AmbienteadministrativoMobiliario entity.
    *
    * @param InfraestructuraH6AmbienteadministrativoMobiliario $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH6AmbienteadministrativoMobiliario $entity)
    {
        $form = $this->createForm(new InfraestructuraH6AmbienteadministrativoMobiliarioType(), $entity, array(
            'action' => $this->generateUrl('infrah6ambadmmobiliario_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH6AmbienteadministrativoMobiliario entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoMobiliario')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoMobiliario entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('infrah6ambadmmobiliario_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoMobiliario:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a InfraestructuraH6AmbienteadministrativoMobiliario entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoMobiliario')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InfraestructuraH6AmbienteadministrativoMobiliario entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('infrah6ambadmmobiliario'));
    }

    /**
     * Creates a form to delete a InfraestructuraH6AmbienteadministrativoMobiliario entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('infrah6ambadmmobiliario_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    public function fillDatah6AmbienteMobiliarioAction(Request $request){

        // dump($request);die;
        // create DB conexion
        $em = $this->getDoctrine()->getManager();
        // get the send values
        $H6AmbienteAdmAmbienteId = $request->get('H6AmbienteAdmAmbienteId');
        //find the mobiliario to this ambiente
        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoMobiliario')->findBy(array(
            'infraestructuraH6AmbienteadministrativoAmbiente' => $H6AmbienteAdmAmbienteId,
        ));

        $arrH6Mobiliario = array();
        if($entities){
            foreach ($entities as $mobiliario) {
                $arrH6Mobiliario[] = array(
                    'id'                                                  => $mobiliario->getId(),
                    'n21Cantidad'                                         => $mobiliario->getN21Cantidad(),
                    'n21MobiliarioTipo'                                   => $mobiliario->getN21MobiliarioTipo()->getId(),
                    'n21EstadoTipo'                                       => $mobiliario->getN21EstadoTipo()->getId(),
                    'infraestructuraH6AmbienteadministrativoAmbiente'     => $mobiliario->getInfraestructuraH6AmbienteadministrativoAmbiente(),

                );
            }
        }

         // set the mobiliario type data            
        $objMobiliarioTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoMobiliarioTipo')->findAll();
        $arrDataMobiliarioTipo = array();
        if($objMobiliarioTipo){
            foreach ($objMobiliarioTipo as $mobiliarioType) {
                $arrDataMobiliarioTipo[$mobiliarioType->getId()] = $mobiliarioType->getDescripcion();
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
            'arrH6Mobiliario' => $arrH6Mobiliario,
            'arrDataMobiliarioTipo' => $arrDataMobiliarioTipo,
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
                     
                $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoMobiliario')->findBy(array(
                    'infraestructuraH6AmbienteadministrativoAmbiente' => $H6AmbienteAdmAmbienteId,
                ));
                
                // remove the element to the mobiliario data
                foreach ($entities as $elementAmbAdmMobiliario) {
                    $em->remove($elementAmbAdmMobiliario);
                }        
                $em->flush();        
                //create the limit to do the save    
                $limitSave = sizeof($form['n21MobiliarioTipo']);
                /*dump($limitSave);
                dump($form);die;*/
                $ind=0;
                while ($ind < $limitSave) {
                    $entity = new InfraestructuraH6AmbienteadministrativoMobiliario();

                    $entity->setN21Cantidad($form['n21Cantidad'][$ind]);
                    $entity->setN21EstadoTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadoMobEquipTipo')->find($form['n21EstadoTipo'][$ind]));
                    $entity->setN21MobiliarioTipo($em->getRepository('SieAppWebBundle:InfraestructuraH6AmbienteadministrativoMobiliarioTipo')->find($form['n21MobiliarioTipo'][$ind]));
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
