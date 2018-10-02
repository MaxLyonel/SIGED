<?php

namespace Sie\ProcesosBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

use Sie\AppWebBundle\Entity\ProcesoTipo;
use Sie\AppWebBundle\Form\ProcesoTipoType;

/**
 * ProcesoTipo controller.
 *
 */
class ProcesoTipoController extends Controller
{

    public $session;
 
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }
    /**
     * Lists all ProcesoTipo entities.
     *
     */
    public function indexAction(Request $request)
    {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:ProcesoTipo')->findBy(array(),array('id'=>'ASC'));

        return $this->render('SieProcesosBundle:ProcesoTipo:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new ProcesoTipo entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new ProcesoTipo();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('proceso_tipo');")->execute();
            $query = $em->getConnection()->prepare("select * from proceso_tipo where proceso_tipo='" . $entity->getProcesoTipo() ."'");
            $query->execute();
            $tarea = $query->fetchAll();
            if(!$tarea){
                $em->persist($entity);
                $em->flush();
                $mensaje = 'La tarea ' . $entity->getProcesoTipo() . ' se registró con éxito';
                $request->getSession()
                    ->getFlashBag()
                    ->add('exito', $mensaje);
            }else{
                $mensaje = 'La tarea ' . $entity->getProcesoTipo() . ' ya está registrada';
                $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje);
            }
            return $this->redirect($this->generateUrl('procesotipo'));
        }

        return $this->render('SieProcesosBundle:ProcesoTipo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a ProcesoTipo entity.
     *
     * @param ProcesoTipo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(ProcesoTipo $entity)
    {
        $form = $this->createForm(new ProcesoTipoType(), $entity, array(
            'action' => $this->generateUrl('procesotipo_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new ProcesoTipo entity.
     *
     */
    public function newAction()
    {
        $entity = new ProcesoTipo();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieProcesosBundle:ProcesoTipo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a ProcesoTipo entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:ProcesoTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProcesoTipo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieProcesosBundle:ProcesoTipo:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing ProcesoTipo entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:ProcesoTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProcesoTipo entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieProcesosBundle:ProcesoTipo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a ProcesoTipo entity.
    *
    * @param ProcesoTipo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(ProcesoTipo $entity)
    {
        $form = $this->createForm(new ProcesoTipoType(), $entity, array(
            'action' => $this->generateUrl('procesotipo_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing ProcesoTipo entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:ProcesoTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProcesoTipo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $mensaje = 'La tarea se modificó con éxito';
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);

            return $this->redirect($this->generateUrl('procesotipo'));
        }

        return $this->render('SieProcesosBundle:ProcesoTipo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a ProcesoTipo entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        //$form = $this->createDeleteForm($id);
        //$form->handleRequest($request);

        //if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:ProcesoTipo')->find($id);
            //dump($entity->getId());die;
            //dump($entity);die;            
            $query=$em->getConnection()->prepare('select * from flujo_proceso where proceso_id='.$entity->getId());
            $query->execute();
            $fp = $query->fetchAll();
            //dump($fp);die;
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ProcesoTipo entity.');
            }
            if(!$fp){
                //dump($fp);die;
                $em->remove($entity);
                $em->flush();
                $mensaje = 'La tarea se eliminó con éxito';
                $request->getSession()
                    ->getFlashBag()
                    ->add('exito', $mensaje);           
            }else{
                $mensaje = 'No se puede eliminar la tarea, pues esta asociado a un proceso';
                $request->getSession()
                    ->getFlashBag()
                    ->add('error', $mensaje);       
            }
        //}
        return $this->redirect($this->generateUrl('procesotipo'));
    }

    /**
     * Creates a form to delete a ProcesoTipo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('procesotipo_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
