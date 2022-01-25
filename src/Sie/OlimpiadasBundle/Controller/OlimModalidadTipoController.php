<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\OlimModalidadTipo;
use Sie\AppWebBundle\Form\OlimModalidadTipoType;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * OlimModalidadTipo controller.
 *
 */
class OlimModalidadTipoController extends Controller
{

    public $session;

    public function __construct(){
        $this->session = new Session();
    }
    /**
     * Lists all OlimModalidadTipo entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:OlimModalidadTipo')->findBy( array(), array('id' => 'ASC') );
        //$entities = $em->getRepository(OlimModalidadTipo::class)->findAllOrderedById();
        //$entities = $em->getRepository('SieAppWebBundle:OlimModalidadTipo')
        //->createQueryBuilder('o')
        //->orderBy('o.id','ASC')
        //->getQuery();
        return $this->render('SieOlimpiadasBundle:OlimModalidadTipo:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new OlimModalidadTipo entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new OlimModalidadTipo();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->prepare("select * from sp_reinicia_secuencia('olim_modalidad_tipo');")->execute();
            $em->persist($entity);
            $em->flush();
            $mensaje = 'La modalidad ' . $entity->getModalidad() . ' se registró con éxito';
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
            return $this->redirect($this->generateUrl('olimmodalidadtipo_show', array('id' => $entity->getId())));
        }

        return $this->render('SieOlimpiadasBundle:OlimModalidadTipo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a OlimModalidadTipo entity.
     *
     * @param OlimModalidadTipo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(OlimModalidadTipo $entity)
    {
        $form = $this->createForm(new OlimModalidadTipoType(), $entity, array(
            'action' => $this->generateUrl('olimmodalidadtipo_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }

    /**
     * Displays a form to create a new OlimModalidadTipo entity.
     *
     */
    public function newAction()
    {
        $entity = new OlimModalidadTipo();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieOlimpiadasBundle:OlimModalidadTipo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a OlimModalidadTipo entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimModalidadTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimModalidadTipo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieOlimpiadasBundle:OlimModalidadTipo:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing OlimModalidadTipo entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimModalidadTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimModalidadTipo entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieOlimpiadasBundle:OlimModalidadTipo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a OlimModalidadTipo entity.
    *
    * @param OlimModalidadTipo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(OlimModalidadTipo $entity)
    {
        $form = $this->createForm(new OlimModalidadTipoType(), $entity, array(
            'action' => $this->generateUrl('olimmodalidadtipo_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Modificar'));

        return $form;
    }
    /**
     * Edits an existing OlimModalidadTipo entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimModalidadTipo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimModalidadTipo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            $mensaje = 'La modalidad se modificó con éxito';
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
            return $this->redirect($this->generateUrl('olimmodalidadtipo'));
        }

        return $this->render('SieOlimpiadasBundle:OlimModalidadTipo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a OlimModalidadTipo entity.
     *
     */
    public function deleteAction(Request $request,$id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:OlimModalidadTipo')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find OlimModalidadTipo entity.');
            }
            $em->remove($entity);
            $em->flush();
            $mensaje = 'La modalidad se eliminó con éxito';
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        }

        return $this->redirect($this->generateUrl('olimmodalidadtipo'));
    }

    /**
     * Creates a form to delete a OlimModalidadTipo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('olimmodalidadtipo_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm()
        ;
    }
}
