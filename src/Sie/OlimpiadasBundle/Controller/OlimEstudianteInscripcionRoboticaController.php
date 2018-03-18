<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\OlimEstudianteInscripcion;
use Sie\AppWebBundle\Form\OlimEstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * OlimEstudianteInscripcion controller.
 *
 */
class OlimEstudianteInscripcionRoboticaController extends Controller{

    private $sesion;

    public function __construct(){
        $this->session = new Session();
    }
    /**
     * Lists all OlimEstudianteInscripcion entities.
     *
     */
    public function indexAction(){
        
        $em = $this->getDoctrine()->getManager();

        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcionRobotica:index.html.twig', array(
            'form' => $this->inscriptionForm()->createView(),
        ));
    }

    /**
     * [inscriptionForm description]
     * @return [type] [description]
     */
    private function inscriptionForm(){
        
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT a FROM SieAppWebBundle:OlimCategoriaTipo a
            WHERE a.id between 6 and 13
            ORDER BY a.id');
        
        $categorias = $query->getResult();
        $categoriasArray = array();
        foreach ($categorias as $value) {
            $categoriasArray[$value->getId()] = $value->getCategoria();
        }

        $newform = $this->createFormBuilder()
            ->add('categoria', 'choice', array('label' => 'Categoría', 'required' => true, 'choices' => $categoriasArray, 'attr' => array('class' => 'form-control', 'onchange' => 'verificarCategoria(this.value)')))
            ->add('equipo', 'text', array('label' => 'Nombre del Equipo', 'required' => true, 'attr' => array('class' => 'form-control', 'placeholder' => 'Ingrese un nombre para el equipo')))
            ->add('proyecto', 'text', array('label' => 'Nombre del Proyecto', 'attr' => array('class' => 'form-control', 'placeholder' => 'Ingrese el nombre del proyecto')))
            ->add('rude1', 'text', array('label' => 'Código Rude 1', 'required' => true, 'attr' => array('class' => 'form-control', 'placeholder' => 'Ingrese un código RUDE')))
            ->add('rude2', 'text', array('label' => 'Código Rude 2', 'attr' => array('class' => 'form-control', 'placeholder' => 'Ingrese un código RUDE')))
            ->add('registrar', 'button', array('label'=>'Registrar'))
            ->getForm();

        return $newform;

    }

    /**
     * Find Estudiante.
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function findEstudianteAction(Request $request)
    {
        $entity = new OlimEstudianteInscripcion();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('oliminscriptions_show', array('id' => $entity->getId())));
        }

        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }
    
    /**
     * [openInscriptinoOlimpiadasAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function openInscriptinoOlimpiadasAction(Request $request){

        // get the send values
        $form = $request->get('form');
        
        $jsondataInscription = json_encode(
            array(
            'sie'=>($this->session->get('roluser')==8)?$form['institucionEducativa']:$this->session->get('ie_id'), 
            'gestion'=>$form['gestion'], 
            'materia'=>$form['olimMateria'], 
            )
        ) ;

        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:openInscriptinoOlimpiadas.html.twig', array(
            'jsondataInscription'=>$jsondataInscription,
        ));
    }

    /**
     * [regDirectorAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function regDirectorAction(Request $request){

        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:openInscriptinoOlimpiadas.html.twig', array(
            'jsondataInscription'=>$jsondataInscription,
        ));
    }
    /**
     * Creates a new OlimEstudianteInscripcion entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new OlimEstudianteInscripcion();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('oliminscriptions_show', array('id' => $entity->getId())));
        }

        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a OlimEstudianteInscripcion entity.
     *
     * @param OlimEstudianteInscripcion $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(OlimEstudianteInscripcion $entity)
    {
        $form = $this->createForm(new OlimEstudianteInscripcionType(), $entity, array(
            'action' => $this->generateUrl('oliminscriptions_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new OlimEstudianteInscripcion entity.
     *
     */
    public function newAction(){
        $entity = new OlimEstudianteInscripcion();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieOlimpiadasBundle:OlimEstudianteInscripcion:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }


    /**
     * Finds and displays a OlimEstudianteInscripcion entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimEstudianteInscripcion entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:OlimEstudianteInscripcion:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing OlimEstudianteInscripcion entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimEstudianteInscripcion entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:OlimEstudianteInscripcion:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a OlimEstudianteInscripcion entity.
    *
    * @param OlimEstudianteInscripcion $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(OlimEstudianteInscripcion $entity)
    {
        $form = $this->createForm(new OlimEstudianteInscripcionType(), $entity, array(
            'action' => $this->generateUrl('oliminscriptions_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing OlimEstudianteInscripcion entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OlimEstudianteInscripcion entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('oliminscriptions_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:OlimEstudianteInscripcion:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a OlimEstudianteInscripcion entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find OlimEstudianteInscripcion entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('oliminscriptions'));
    }

    /**
     * Creates a form to delete a OlimEstudianteInscripcion entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('oliminscriptions_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
