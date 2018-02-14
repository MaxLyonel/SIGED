<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoMobiliario;
use Sie\AppWebBundle\Form\InfraestructuraH5AmbientepedagogicoMobiliarioType;

/**
 * InfraestructuraH5AmbientepedagogicoMobiliario controller.
 *
 */
class InfraestructuraH5AmbientepedagogicoMobiliarioController extends Controller
{

    /**
     * Lists all InfraestructuraH5AmbientepedagogicoMobiliario entities.
     *
     */
    public function indexAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        //get the send values
        $h5AmbientepedagogicoId = $request->get('H5AmbientepedagogicoId');


        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoMobiliario')->findOneBy(array(
            'infraestructuraH5Ambientepedagogico'=>$h5AmbientepedagogicoId
        ));

        return $this->redirect($this->generateUrl('h5mobiliario_new', array('id'=> $h5AmbientepedagogicoId)));
        
        // if(!$entity){
        //     // edit the mobiliario data
        //     return $this->redirect($this->generateUrl('h5mobiliario_edit',  array('id' => $entity->getId() ) ));
        // }else{
        //     // create new data  mobiliario
        //     return $this->redirect($this->generateUrl('h5mobiliario_new', array('id'=> $h5AmbientepedagogicoId)));
        // }

        // return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoMobiliario:index.html.twig', array(
        //     'entities' => $entities,
        // ));
    }

    public function fillDataMobiliarioAction(Request $request){

        //crete the db conextion db
        $em = $this->getDoctrine()->getManager();
        //get the send values
        $h5AmbientepedagogicoId = $request->get('H5AmbientepedagogicoId');
        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoMobiliario')->findBy(array(
            'infraestructuraH5Ambientepedagogico'=>$h5AmbientepedagogicoId
        ));
        // set the mobiliario type data            
        $objMobiliarioTipo = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoMobiliarioTipo')->findAll();
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


        $arrDataMobiliario = array();
        if($entities){
            foreach ($entities as $mobiliario) {
                $arrDataMobiliario[] = array(
                                        'id'                                  =>  $mobiliario->getId(),
                                        'n52Cantidad'                         =>  $mobiliario->getN52Cantidad(),
                                        'n52EstadoTipo'                       =>  $mobiliario->getN52EstadoTipo()->getId(),
                                        'n52MobiliarioTipo'                   =>  $mobiliario->getN52MobiliarioTipo()->getId(),
                                        'infraestructuraH5Ambientepedagogico' =>  $mobiliario->getInfraestructuraH5Ambientepedagogico()->getId()
                );


            }
        }
        
        //return the json data
        $response = new JsonResponse();
        return $response->setData(array(
            'arrDataMobiliario' => $arrDataMobiliario,
            'arrDataMobiliarioTipo' => $arrDataMobiliarioTipo,
            'arrDataStatusTipo' => $arrDataStatusTipo,
        ));
        
    }
    /**
     * Creates a new InfraestructuraH5AmbientepedagogicoMobiliario entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH5AmbientepedagogicoMobiliario();
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
        ));
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
                // get the id of mobiliario
                $h5AmbientepedagogicoId = $form['h5AmbientepedagogicoId'];
                //find the mobiliario data before to save
                $objAmbPedagogicoMobiliario = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoMobiliario')->findBy(array(
                    'infraestructuraH5Ambientepedagogico' => $h5AmbientepedagogicoId,

                ));
                // remove the element to the mobiliario data
                foreach ($objAmbPedagogicoMobiliario as $elementAmbPedagogicoMobiliario) {
                    $em->remove($elementAmbPedagogicoMobiliario);
                }        
                $em->flush();        
                //create the limit to do the save    
                $limitSave = sizeof($form['n52MobiliarioTipo']);
                /*dump($limitSave);
                dump($form);die;*/
                $ind=0;
                while ($ind < $limitSave) {
                    $entity = new InfraestructuraH5AmbientepedagogicoMobiliario();

                    $entity->setN52Cantidad($form['n52Cantidad'][$ind]);
                    $entity->setN52EstadoTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadoMobEquipTipo')->find($form['n52EstadoTipo'][$ind]));
                    $entity->setN52MobiliarioTipo($em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoMobiliarioTipo')->find($form['n52MobiliarioTipo'][$ind]));
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

    /**
     * Creates a new InfraestructuraH5AmbientepedagogicoMobiliario entity.
     *
     */
    public function updateMobiliarioAction(Request $request){
        
        //cretae the db conexion
        $em = $this->getDoctrine()->getManager();
        // get the send values
        $form = $request->get('sie_appwebbundle_infraestructurah5ambientepedagogicomobiliario');
        // dump($form);die;
        // $entity = new InfraestructuraH5AmbientepedagogicoMobiliario();
        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoMobiliario')->find($form['id']);
         //    ->add('n52Cantidad')
         //    ->add('n52EstadoTipo')
         //    ->add('n52MobiliarioTipo')

         $entity->setN52Cantidad($form['n52Cantidad']);
         $entity->setN52EstadoTipo($em->getRepository('SieAppWebBundle:InfraestructuraGenEstadoMobEquipTipo')->find($form['n52EstadoTipo']));
         $entity->setN52MobiliarioTipo($em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoMobiliarioTipo')->find($form['n52MobiliarioTipo']));
         // $entity->setInfraestructuraH5Ambientepedagogico($em->getRepository('SieAppWebBundle:InfraestructuraH5Ambientepedagogico')->find($form['h5AmbientepedagogicoId']));

         $em->persist($entity);
         $em->flush();
         die('done');
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

    /**
     * Creates a form to create a InfraestructuraH5AmbientepedagogicoMobiliario entity.
     *
     * @param InfraestructuraH5AmbientepedagogicoMobiliario $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH5AmbientepedagogicoMobiliario $entity, $h5AmbientepedagogicoId)
    {
        $form = $this->createForm(new InfraestructuraH5AmbientepedagogicoMobiliarioType(), $entity, array(
            // 'action' => $this->generateUrl('h5mobiliario_create'),
            // 'method' => 'POST',
        ));

        $form->add('h5AmbientepedagogicoId', 'text', array('mapped'=>false,'data' => $h5AmbientepedagogicoId));
        // $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH5AmbientepedagogicoMobiliario entity.
     *
     */
    public function newAction(Request $request){
        // get the send values
        $h5AmbientepedagogicoId = $request->get('id');
        // dump($h5AmbientepedagogicoId);die;
        // $entity = new InfraestructuraH5AmbientepedagogicoMobiliario();
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5Ambientepedagogico')->find($h5AmbientepedagogicoId);
        // $form   = $this->createCreateForm($entity, $h5AmbientepedagogicoId);
        $form   = $this->newMobiliarioForm($h5AmbientepedagogicoId);

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoMobiliario:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * [newMobiliarioForm description]
     * @param  [type] $h5AmbientepedagogicoId [description]
     * @return [type]                         [description]
     */
    private function newMobiliarioForm($h5AmbientepedagogicoId){
        return $this->createFormBuilder()
                ->add('h5AmbientepedagogicoId', 'hidden', array('mapped'=>false, 'data'=> $h5AmbientepedagogicoId))
                ->getForm()
                ;
    }


    /**
     * Finds and displays a InfraestructuraH5AmbientepedagogicoMobiliario entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoMobiliario')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH5AmbientepedagogicoMobiliario entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoMobiliario:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH5AmbientepedagogicoMobiliario entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoMobiliario')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH5AmbientepedagogicoMobiliario entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoMobiliario:edit.html.twig', array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a InfraestructuraH5AmbientepedagogicoMobiliario entity.
    *
    * @param InfraestructuraH5AmbientepedagogicoMobiliario $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH5AmbientepedagogicoMobiliario $entity)
    {
        $form = $this->createForm(new InfraestructuraH5AmbientepedagogicoMobiliarioType(), $entity, array(
            //'action' => $this->generateUrl('h5mobiliario_update', array('id' => $entity->getId())),
            //'method' => 'PUT',
        ));

        $form->add('id', 'text', array('mapped'=>false,'data' => $entity->getId()));
        // $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH5AmbientepedagogicoMobiliario entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoMobiliario')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH5AmbientepedagogicoMobiliario entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('h5mobiliario_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoMobiliario:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a InfraestructuraH5AmbientepedagogicoMobiliario entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH5AmbientepedagogicoMobiliario')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InfraestructuraH5AmbientepedagogicoMobiliario entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('h5mobiliario'));
    }

    /**
     * Creates a form to delete a InfraestructuraH5AmbientepedagogicoMobiliario entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('h5mobiliario_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
