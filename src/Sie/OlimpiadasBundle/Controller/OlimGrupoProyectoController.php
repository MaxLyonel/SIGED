<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\OlimGrupoProyecto;
use Sie\AppWebBundle\Form\OlimGrupoProyectoType;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * OlimGrupoProyecto controller.
 *
 */

/**
 * Author: Carlos Pacha Cordova <pckrlos@gmail.com>
 * Description:This is a class for load the inscription data; if exist in a particular proccess
 * Date: 03-04-2018
 *
 *
 * OlimEstudianteInscripcionController.php
 *
 * Email bugs/suggestions to <pckrlos@gmail.com>
 */
class OlimGrupoProyectoController extends Controller
{

    private $sesion;

    public function __construct(){
        $this->session = new Session();
    }

    /**
     * Lists all OlimGrupoProyecto entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->findAll();

        return $this->render('SieOlimpiadasBundle:OlimGrupoProyecto:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new OlimGrupoProyecto entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new OlimGrupoProyecto();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('olimgrupoproyecto_show', array('id' => $entity->getId())));
        }

        return $this->render('SieOlimpiadasBundle:OlimGrupoProyecto:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a OlimGrupoProyecto entity.
     *
     * @param OlimGrupoProyecto $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(OlimGrupoProyecto $entity)
    {
        $form = $this->createForm(new OlimGrupoProyectoType(), $entity, array(
            'action' => $this->generateUrl('olimgrupoproyecto_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new OlimGrupoProyecto entity.
     *
     */
    public function newAction()
    {
        $entity = new OlimGrupoProyecto();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieOlimpiadasBundle:OlimGrupoProyecto:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a OlimGrupoProyecto entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimGrupoProyecto entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieOlimpiadasBundle:OlimGrupoProyecto:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing OlimGrupoProyecto entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimGrupoProyecto entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieOlimpiadasBundle:OlimGrupoProyecto:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a OlimGrupoProyecto entity.
    *
    * @param OlimGrupoProyecto $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(OlimGrupoProyecto $entity)
    {
        $form = $this->createForm(new OlimGrupoProyectoType(), $entity, array(
            'action' => $this->generateUrl('olimgrupoproyecto_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing OlimGrupoProyecto entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimGrupoProyecto entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('olimgrupoproyecto_edit', array('id' => $id)));
        }

        return $this->render('SieOlimpiadasBundle:OlimGrupoProyecto:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a OlimGrupoProyecto entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find OlimGrupoProyecto entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('olimgrupoproyecto'));
    }

    /**
     * Creates a form to delete a OlimGrupoProyecto entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('olimgrupoproyecto_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }


    public function showGroupAction(Request $request){
        
        //get the send values
        $jsonDataInscription = $request->get('datainscription');
        $arrDataInscription = json_decode($jsonDataInscription,true);
        
        // dump($arrDataInscription);die;
        //create dbconexion
        $em = $this->getDoctrine()->getManager();
        $objTutorsGrupo = $em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->findBy(array(
            'olimTutor' => $arrDataInscription['olimtutorid'],
            'olimReglasOlimpiadasTipo'=> $arrDataInscription['categoryId']
        ));

        //get the rule to do the inscription
        $objRuleInscription = $this->get('olimfunctions')->getDataRule($arrDataInscription);
        // dump($objRuleInscription);die;

        // dump($objTutorsGrupo);die;
        return $this->render('SieOlimpiadasBundle:OlimGrupoProyecto:showGroup.html.twig', array(
        // 'form' => $this->CommonInscriptionForm($arrDataInscription)->createView(),
            'objTutorsGrupo' => $objTutorsGrupo,
            'formGroup' => $this->newGroupForm($jsonDataInscription)->createView(),
            'jsonDataInscription' => $jsonDataInscription,
            'regla' => $objRuleInscription

        ));
    }

    private function newGroupForm($jsonDataInscription){
        return $this->createFormBuilder()
                ->add('jsonDataInscription', 'hidden', array('data'=>$jsonDataInscription))
                ->getForm()
                ;
    }



    public function createGroupAction(Request $request){
        //get the send values
        $formjson = $request->get('form');
        $jsonDataInscription =  $formjson['jsonDataInscription'];
        // dump($jsonDataInscription);die;
        $entity = new OlimGrupoProyecto();
        $form   = $this->createCreateGroupForm($entity, $jsonDataInscription);

        return $this->render('SieOlimpiadasBundle:OlimGrupoProyecto:createGroup.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));

    }

    private function createCreateGroupForm(OlimGrupoProyecto $entity, $jsonDataInscription){
        $form = $this->createForm(new OlimGrupoProyectoType(), $entity, array(
            // 'action' => $this->generateUrl('olimgrupoproyecto_create'),
            'method' => 'POST',
        ));
        $form->add('jsonDataInscription', 'hidden', array('mapped'=>false,'data'=>$jsonDataInscription));
        // $form->add('savegroup', 'button', array('label' => 'Create', 'attr'=>array('onclick'=>'createGroupSave()')));

        return $form;
    }


    public function createGroupSaveAction(Request $request){
        
        try {
        //create db conexino
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //get the send data
        $form = $request->get('sie_appwebbundle_olimgrupoproyecto');
        $jsonDataInscription = $form['jsonDataInscription'];
        $arrDataInscription = json_decode($jsonDataInscription, true);
        // dump($arrDataInscription);die;
        // create condition to find
        $nameGroup = trim(mb_strtoupper($form['nombre'], 'utf8'));
        // dump($nameGroup);die;
        $arrCondition = array(
            'nombre'                   => $nameGroup,
            'olimTutor'                => $arrDataInscription['olimtutorid'],
            'olimReglasOlimpiadasTipo' => $arrDataInscription['categoryId']
        );
        
                //check if the groups name exist
        $objGroup = $em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->findBy($arrCondition);
        if(!$objGroup){           

            // dump($arrDataInscription);die;
            // restart the key table
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_grupo_proyecto');");
            $query->execute();
            //save the group information
            $entity = new OlimGrupoProyecto();
            $entity->setNombre($nameGroup);
            $entity->setFechaRegistro(new \DateTime('now'));
            $entity->setUsuarioRegistroId($this->session->get('userId'));
            $entity->setGestionTipoId($arrDataInscription['gestiontipoid']);
            $entity->setOlimTutor($em->getRepository('SieAppWebBundle:OlimTutor')->find($arrDataInscription['olimtutorid']));
            $entity->setOlimReglasOlimpiadasTipo($em->getRepository('SieAppWebBundle:OlimReglasOlimpiadasTipo')->find($arrDataInscription['categoryId']));
            $entity->setPeriodoTipo($em->getRepository('SieAppWebBundle:OlimPeriodoTipo')->find(1));
            $entity->setMateriaTipo($em->getRepository('SieAppWebBundle:OlimMateriaTipo')->find($arrDataInscription['materiaId']));
            $entity->setOlimGrupoProyectoTipo($em->getRepository('SieAppWebBundle:OlimGrupoProyectoTipo')->find(1));
            $em->persist($entity);
            $em->flush();
            
            $em->getConnection()->commit();

        }else{
            $message = 'Grupo no creado, nombre de grupo ya existe...';
            $this->addFlash('noGroupCreate', $message);
        }
            
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
        return $this->redirectToRoute('olimgrupoproyecto_showGroup', array('datainscription'=>$jsonDataInscription));
        
    }

    public function deleteGroupAction(Request $request){

        $jsonDataInscription = $request->get('jsonDataInscription');
        $id = $request->get('groupId');

        $em = $this->getDoctrine()->getManager();

                // check if the group has students
        $studentsGroup = $em->getRepository('SieAppWebBundle:OlimInscripcionGrupoProyecto')->findBy(array(
            'olimGrupoProyecto'=>$id
        ));
        // dump(sizeof($studentsGroup) );die;
        $entity = $em->getRepository('SieAppWebBundle:OlimGrupoProyecto')->find($id);
        // dump(sizeof($studentsGroup));die;
        if(sizeof($studentsGroup)>0){
            $message = 'Grupo no eliminado, el grupo tiene inscritos...';
            $this->addFlash('noGroupCreate', $message);

        }else{
              
            if ($entity) {
                $message = 'Grupo Eliminado correctamente...';
                $this->addFlash('yesGroupDelete', $message);
                $em->remove($entity);
                $em->flush();
            }else{
                throw $this->createNotFoundException('Unable to find OlimGrupoProyecto entity.');
            }
        }

        return $this->redirectToRoute('olimgrupoproyecto_showGroup', array('datainscription'=>$jsonDataInscription));


    }




}
