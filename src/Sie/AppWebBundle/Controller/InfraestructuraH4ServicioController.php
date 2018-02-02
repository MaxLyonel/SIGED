<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\InfraestructuraH4Servicio;
use Sie\AppWebBundle\Form\InfraestructuraH4ServicioType;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InfraestructuraH4ServicioBateriaBanos;
use Sie\AppWebBundle\Form\InfraestructuraH4ServicioBateriaBanosType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InfraestructuraH4ServicioVestuarios;
use Sie\AppWebBundle\Form\InfraestructuraH4ServicioVestuariosType;


/**
 * InfraestructuraH4Servicio controller.
 *
 */
class InfraestructuraH4ServicioController extends Controller
{

    /**
     * Lists all InfraestructuraH4Servicio entities.
     *
     */
    public function indexAction(){

        //get values to send to create o edit
        $infraestructuraJuridiccionGeograficaId = 10303;
        // 26753
        // create the db conexion
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH4Servicio')->findOneBy(array(
            'infraestructuraJuridiccionGeografica'=> $infraestructuraJuridiccionGeograficaId
        ));

        if ($entity) {
            // if exist then, edit the values
            return $this->redirect($this->generateUrl('infraestructurah4servicio_edit', array('id' => $entity->getId())));

        } else {
            // no exist so create the new data
             return $this->redirect($this->generateUrl('infraestructurah4servicio_new'));
        }

        // // dump($entity);die;

        // $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH4Servicio')->findBy(array(), array('id'=>'DESC'),10);

        // return $this->render('SieAppWebBundle:InfraestructuraH4Servicio:index.html.twig', array(
        //     'entities' => $entities,
        // ));
    }
    /**
     * Creates a new InfraestructuraH4Servicio entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new InfraestructuraH4Servicio();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        // var_dump($form->isValid());die;
        if (!$form->isValid()) {

            // $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('infraestructura_h4_servicio');");
            // $query->execute();

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('infraestructurah4servicio_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH4Servicio:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a InfraestructuraH4Servicio entity.
     *
     * @param InfraestructuraH4Servicio $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(InfraestructuraH4Servicio $entity)
    {
        $form = $this->createForm(new InfraestructuraH4ServicioType(), $entity, array(
            'action' => $this->generateUrl('infraestructurah4servicio_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new InfraestructuraH4Servicio entity.
     *
     */
    public function newAction()
    {
        $entity = new InfraestructuraH4Servicio();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:InfraestructuraH4Servicio:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a InfraestructuraH4Servicio entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH4Servicio')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH4Servicio entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:InfraestructuraH4Servicio:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing InfraestructuraH4Servicio entity.
     *
     */
    public function editAction($id){
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH4Servicio')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH4Servicio entity.');
        }

        $editForm = $this->createEditForm($entity);
        // $deleteForm = $this->createDeleteForm($id);
        // look for the bateras de bano data to draw in the view
        $entityBateriaBanos = $em->getRepository('SieAppWebBundle:InfraestructuraH4ServicioBateriaBanos')->findBy(array(
            // 'infraestructuraH4Servicio' => $id
            'infraestructuraH4Servicio' => 18
        ));
        // look for the vestuario data to draw in the view
        $entityVestuarios = $em->getRepository('SieAppWebBundle:InfraestructuraH4ServicioVestuarios')->findBy(array(
            // 'infraestructuraH4Servicio' => $id
            'infraestructuraH4Servicio' => 18
        ));
        
        if (!$entityBateriaBanos) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH4ServicioBateriaBanos entity.');
        }
        // create new enttity about bateria bano
        $entityNewBateriaBanos = new InfraestructuraH4ServicioBateriaBanos();
        $editFormBateriaBano = $this->createCreateFormBateriaBano($entityNewBateriaBanos);


        return $this->render('SieAppWebBundle:InfraestructuraH4Servicio:edit.html.twig', array(
            'entity'      => $entity,
            'entityBateriaBanos'      => $entityBateriaBanos,
            'entityVestuarios'      => $entityVestuarios,
            'form'   => $editForm->createView(),
            'edit_form'   => $editFormBateriaBano->createView(),
            // 'delete_form' => $deleteForm->createView(),
        ));
    }
        /**
     * Creates a form to create a InfraestructuraH4ServicioBateriaBanos entity.
     *
     * @param InfraestructuraH4ServicioBateriaBanos $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateFormBateriaBano(InfraestructuraH4ServicioBateriaBanos $entity)
    {
        $form = $this->createForm(new InfraestructuraH4ServicioBateriaBanosType(), $entity, array(
            'action' => $this->generateUrl('infraestructurah4serviciobateriabanos_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

      /**
    * Creates a form to edit a InfraestructuraH4ServicioBateriaBanos entity.
    *
    * @param InfraestructuraH4ServicioBateriaBanos $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditFormBateriaBano(InfraestructuraH4ServicioBateriaBanos $entity)
    {
        $form = $this->createForm(new InfraestructuraH4ServicioBateriaBanosType(), $entity, array(
            'action' => $this->generateUrl('infraestructurah4serviciobateriabanos_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('button', 'button', array('label' => 'Actualizar'));

        return $form;
    }

    /**
    * Creates a form to edit a InfraestructuraH4Servicio entity.
    *
    * @param InfraestructuraH4Servicio $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(InfraestructuraH4Servicio $entity)
    {
        $form = $this->createForm(new InfraestructuraH4ServicioType(), $entity, array(
            'action' => $this->generateUrl('infraestructurah4servicio_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }
    /**
     * Edits an existing InfraestructuraH4Servicio entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH4Servicio')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find InfraestructuraH4Servicio entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('infraestructurah4servicio_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:InfraestructuraH4Servicio:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a InfraestructuraH4Servicio entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:InfraestructuraH4Servicio')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find InfraestructuraH4Servicio entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('infraestructurah4servicio'));
    }

    /**
     * Creates a form to delete a InfraestructuraH4Servicio entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('infraestructurah4servicio_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
