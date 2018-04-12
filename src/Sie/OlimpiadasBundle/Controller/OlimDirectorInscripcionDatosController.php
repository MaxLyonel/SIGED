<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\OlimDirectorInscripcionDatos;
use Sie\AppWebBundle\Form\OlimDirectorInscripcionDatosType;

/**
 * OlimDirectorInscripcionDatos controller.
 *
 */
class OlimDirectorInscripcionDatosController extends Controller
{

    /**
     * Lists all OlimDirectorInscripcionDatos entities.
     *
     */
    public function indexAction(){
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:OlimDirectorInscripcionDatos')->findAll();

        return $this->render('SieAppWebBundle:OlimDirectorInscripcionDatos:index.html.twig', array(
            'entities' => $entities,
        ));
    }


    public function regDirectorAction(Request $request){
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        // get send values
        $jsondatainscrption = $request->get('datainscription');
        $arrdataInscription = json_decode($jsondatainscrption, true);
        // get data director            
        $dataDirector = $this->getDirectorData($arrdataInscription);
        // dump($dataDirector);die;
        //get the all data
        $jsonData = json_encode(array_merge($arrdataInscription, $dataDirector)) ;
        // get the data director inscription
        $objDirectorInscripcionDatos = $em->getRepository('SieAppWebBundle:OlimDirectorInscripcionDatos')->findOneBy(array(
            'maestroInscripcion' => $dataDirector['maestroinscriptionid']
        ));
        if(!$objDirectorInscripcionDatos){
            $objDirectorInscripcionDatos = new OlimDirectorInscripcionDatos();
        }
        
        $form = $this->createCreateForm($objDirectorInscripcionDatos, $jsonData);
        // dump('krlos');die;
        return $this->render('SieAppWebBundle:OlimDirectorInscripcionDatos:new.html.twig', array(
            //'entity' => $entity,
            'form'   => $form->createView(),
            'dataDirector'   => $dataDirector,
        ));
    }
    /**
     * [getDirectorData description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function getDirectorData($data){
        // dump($data);die;
        $em = $this->getDoctrine()->getManager();
      
        $query = $em->getConnection()->prepare('
            select m.id maestroinscriptionid, p.nombre, p.paterno, p.materno, p.carnet, m.cargo_tipo_id, p.id as personaid
            from maestro_inscripcion m
            left join persona p on (m.persona_id = p.id)
            where m.institucioneducativa_id = '.$data['sie'].' and m.gestion_tipo_id = '.$data['gestion'].' and m.cargo_tipo_id in (1,12)
        ');

        $query->execute();
        $arrDataDirector = $query->fetch();
        
        return ($arrDataDirector);

        

    }

    /**
     * [saveDataDirectorAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function saveDataDirectorAction(Request $request){
        
        // get the send values
        $form = $request->get('sie_appwebbundle_olimdirectorinscripciondatos');
        
        $jsondata = json_decode($form['datainscription'], true);
    
        $em = $this->getDoctrine()->getManager();

        $objDirectorInscripcionDatos = $em->getRepository('SieAppWebBundle:OlimDirectorInscripcionDatos')->findOneBy(array(
            'maestroInscripcion' => $jsondata['maestroinscriptionid']
        ));

        if(!$objDirectorInscripcionDatos){
            $objDirectorInscripcionDatos = new OlimDirectorInscripcionDatos();
        }
        
        $objDirectorInscripcionDatos->setTelefono1($form['telefono1']);
        $objDirectorInscripcionDatos->setTelefono2($form['telefono2']);
        $objDirectorInscripcionDatos->setCorreoElectronico($form['correoElectronico']);
        $objDirectorInscripcionDatos->setEsVigente('t');
        $objDirectorInscripcionDatos->setFechaRegistro(new \DateTime('now'));
        $objDirectorInscripcionDatos->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find( $jsondata['maestroinscriptionid']) );
        $em->persist($objDirectorInscripcionDatos);
        $em->flush();

        $form = $this->createCreateForm($objDirectorInscripcionDatos, $form['datainscription']);
        // dump('krlos');die;
        return $this->render('SieAppWebBundle:OlimDirectorInscripcionDatos:new.html.twig', array(
            //'entity' => $entity,
            'form'   => $form->createView(),
            'dataDirector'   => $jsondata,
        ));
    }



    /**
     * Creates a new OlimDirectorInscripcionDatos entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new OlimDirectorInscripcionDatos();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('olimdirectordata_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:OlimDirectorInscripcionDatos:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a OlimDirectorInscripcionDatos entity.
     *
     * @param OlimDirectorInscripcionDatos $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(OlimDirectorInscripcionDatos $entity, $jsonData)
    {
        $form = $this->createForm(new OlimDirectorInscripcionDatosType(), $entity, array(
            // 'action' => $this->generateUrl('olimdirectordata_create'),
            'method' => 'POST',
        ));
        $form->add('datainscription', 'text', array('mapped'=>false,'data' => $jsonData));
        $form->add('guardar', 'button', array('label' => 'Guardar', 'attr'=>array('onclick'=>'saveDataDirector()')));

        return $form;
    }







    /**
     * Displays a form to create a new OlimDirectorInscripcionDatos entity.
     *
     */
    public function newAction()
    {
        $entity = new OlimDirectorInscripcionDatos();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:OlimDirectorInscripcionDatos:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a OlimDirectorInscripcionDatos entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimDirectorInscripcionDatos')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimDirectorInscripcionDatos entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:OlimDirectorInscripcionDatos:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing OlimDirectorInscripcionDatos entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimDirectorInscripcionDatos')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimDirectorInscripcionDatos entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:OlimDirectorInscripcionDatos:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a OlimDirectorInscripcionDatos entity.
    *
    * @param OlimDirectorInscripcionDatos $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(OlimDirectorInscripcionDatos $entity)
    {
        $form = $this->createForm(new OlimDirectorInscripcionDatosType(), $entity, array(
            'action' => $this->generateUrl('olimdirectordata_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing OlimDirectorInscripcionDatos entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimDirectorInscripcionDatos')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimDirectorInscripcionDatos entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('olimdirectordata_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:OlimDirectorInscripcionDatos:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a OlimDirectorInscripcionDatos entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:OlimDirectorInscripcionDatos')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find OlimDirectorInscripcionDatos entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('olimdirectordata'));
    }

    /**
     * Creates a form to delete a OlimDirectorInscripcionDatos entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('olimdirectordata_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    /**
     * Displays a form to edit an existing OlimDirectorInscripcionDatos entity.
     *
     */
    public function editManualAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        $entity = $em->getRepository('SieAppWebBundle:OlimDirectorInscripcionDatos')->find($form['datosId']);
        
        if (!$entity) {
            return $this->redirect($this->generateUrl('olimtutor_listTutorBySie'));
        }

        $editForm = $this->createEditManualForm($entity, $form['jsonData']);

        return $this->render('SieOlimpiadasBundle:OlimDirectorInscripcionDatos:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a OlimDirectorInscripcionDatos entity.
    *
    * @param OlimDirectorInscripcionDatos $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditManualForm(OlimDirectorInscripcionDatos $entity,$jsonData){
        
        $arrData = json_decode($jsonData, true);
        // dump($arrData);die;
        $form = $this->createForm(new OlimDirectorInscripcionDatosType(), $entity, array(
            'action' => $this->generateUrl('olimtutor_listTutorBySie'),
            // 'method' => 'PUT',
        ));

        $form->add('sie', 'text', array('data'=>$arrData['sie'] , 'mapped'=>false));
        $form->add('gestion', 'text', array('data'=>$arrData['gestion'] , 'mapped'=>false));
        $form->add('editDirector', 'text', array('data'=>$entity->getId(), 'mapped'=>false));
        $form->add('jsonData', 'text', array('data'=>$jsonData, 'mapped'=>false));
        
        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }

    /**
     * Edits an existing OlimDirectorInscripcionDatos entity.
     *
     */
    public function updateManualAction(Request $request, $id){

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimDirectorInscripcionDatos')->find($id);

        if (!$entity) {
            return $this->redirect($this->generateUrl('olimtutor_listTutorBySie'));
        }

        $editForm = $this->createEditManualForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('olimtutor_listTutorBySie'));
        }

        return $this->render('SieOlimpiadasBundle:OlimDirectorInscripcionDatos:edit.html.twig', array(
            'entity'      => $entity
        ));
    }
}
