<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\OlimTutor;
use Sie\AppWebBundle\Form\OlimTutorType;

/**
 * OlimTutor controller.
 *
 */
class OlimTutorController extends Controller
{

    /**
     * Lists all OlimTutor entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:OlimTutor')->findAll(array(), array('id'=>'DESC'), 10);
        
        return $this->render('SieOlimpiadasBundle:OlimTutor:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * [listTutorAction description]
     * @return [type] [description]
     */
    public function listTutorAction(Request $request){
        // dump($request);die;
        // get send data
        $jsonDataInscription = $request->get('datainscription');
        $arrDataInscription = json_decode($jsonDataInscription, true);
        // dump($arrDataInscription);die;
        $em = $this->getDoctrine()->getManager();

        // $entities = $em->getRepository('SieAppWebBundle:OlimTutor')->findAll();
        $entities =  $this->get('olimfunctions')->getTutores($arrDataInscription);
        // dump($entities);die;
        return $this->render('SieOlimpiadasBundle:OlimTutor:listTutor.html.twig', array(
            'entities' => $entities,
            'jsonDataInscription' => $jsonDataInscription,
        ));
    }

    public function addTutorAction(Request $request){
        //get the send values
        $jsonDataInscription = $request->get('jsonDataInscription');
    // dump($jsonDataInscription);die;

        return $this->render('SieOlimpiadasBundle:OlimTutor:addTutor.html.twig', array(
            'form' => $this->createAddTutorForm($jsonDataInscription)->createView()
        ));
    }
    /**
     * [createAddTutorForm description]
     * @param  [type] $jsonDataInscription [description]
     * @return [type]                      [description]
     */
    private function createAddTutorForm($jsonDataInscription){
        return $this->createFormBuilder()
            ->add('carnet', 'text', array('label'=>'CI:'))
            ->add('jsonDataInscription', 'text', array('data'=>$jsonDataInscription))
            ->add('buscar', 'button', array('label'=>'Buscar', 'attr'=>array('onclick'=>'buscarTutor()')))
            ->getForm()
            ;

    }
    /**
     * [buscarTutorAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function buscarTutorAction(Request $request){
        //get the send values
        $form = $request->get('form');
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        //get the tutorse
        $resultTutores = $this->get('olimfunctions')->lookForTutores($form);
        
        return $this->render('SieOlimpiadasBundle:OlimTutor:buscarTutor.html.twig', array(
            'resultTutores'       => $resultTutores,
            'jsonDataInscription' => $form['jsonDataInscription'],
            // 'form' => $this->buscarTutorForm($form['jsonDataInscription'])->createView()
        ));

    }
    /**
     * [buscarTutorForm description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function buscarTutorForm($data){
        return $this->createFormBuilder()
                ->add('telefono1', 'text')
                ->add('telefono2', 'text')
                ->add('correoElectronico', 'text')
                ->add('personaid', 'hidden')
                ->add('data', 'hidden', array('data'=>$data))
                ->add('registrar','button', array('label'=>'Registrar', 'attr'=>array('onclick'=>'registroTutor()')))
                ->getForm()
                ;
    }

    public function registrarTutorAction(Request $request){
        //get the send values
        $form = $request->get('form');
        $data = json_decode($form['data'],true);
        
        //create db conexion 
        $em = $this->getDoctrine()->getManager();
        // udpate the indice
        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_tutor');");
        $query->execute();
        // create the object to do the save
        $entity = new OlimTutor();
        $entity->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($form['personaid']));
        $entity->setTelefono1($form['telefono1']);
        $entity->setTelefono2($form['telefono2']);
        $entity->setCorreoElectronico($form['correoElectronico']);
        $entity->setFechaRegistro(new \Datetime('now'));
        $entity->setInstitucioneducativaId($data['sie']);        
        $entity->setMateriaTipoId($data['materia']);
        $em->persist($entity);
        $em->flush();

        //get values to todo the view
        $jsonDataInscription = $form['data'];
        $arrDataInscription = json_decode($jsonDataInscription, true);
        //get tutores
        $entities =  $this->get('olimfunctions')->getTutores($arrDataInscription);
        
        return $this->render('SieOlimpiadasBundle:OlimTutor:listTutor.html.twig', array(
            'entities' => $entities,
            'jsonDataInscription' => $jsonDataInscription,
        ));
    }

    public function selectTypeInscriptionAction(Request $request){
        //ge the send data
        $jsonDataInscription = $request->get('jsonDataInscription');
        $tutorid = $request->get('tutorid');
        //ge the inscription data
        $arrDataInscription = json_decode($jsonDataInscription, true);
        // dump($arrDataInscription);die;
        switch ($arrDataInscription['materia']) {
            //common inscription
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
                # code...
                dump('common');
                break;
            //informatica inscription
            case 8:
                # code...
                dump('info');
                break;
            // robotica inscription
            case 10:
                # code...
                return $this->redirectToRoute('oliminscriptions_robotica');
                break;
            // feria inscription
            case 11:
                # code...
                dump('feria');
                break;
            
            default:
                # code...
                break;
        }

        

    }










    /**
     * Creates a new OlimTutor entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new OlimTutor();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('olimtutor_show', array('id' => $entity->getId())));
        }

        return $this->render('SieOlimpiadasBundle:OlimTutor:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a OlimTutor entity.
     *
     * @param OlimTutor $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(OlimTutor $entity)
    {
        $form = $this->createForm(new OlimTutorType(), $entity, array(
            'action' => $this->generateUrl('olimtutor_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new OlimTutor entity.
     *
     */
    public function newAction()
    {
        $entity = new OlimTutor();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieOlimpiadasBundle:OlimTutor:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a OlimTutor entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimTutor')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimTutor entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieOlimpiadasBundle:OlimTutor:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing OlimTutor entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimTutor')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimTutor entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieOlimpiadasBundle:OlimTutor:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a OlimTutor entity.
    *
    * @param OlimTutor $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(OlimTutor $entity)
    {
        $form = $this->createForm(new OlimTutorType(), $entity, array(
            'action' => $this->generateUrl('olimtutor_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing OlimTutor entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimTutor')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimTutor entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('olimtutor_edit', array('id' => $id)));
        }

        return $this->render('SieOlimpiadasBundle:OlimTutor:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a OlimTutor entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:OlimTutor')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find OlimTutor entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('olimtutor'));
    }

    /**
     * Creates a form to delete a OlimTutor entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('olimtutor_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
