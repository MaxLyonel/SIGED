<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sie\AppWebBundle\Entity\PreinsInstitucioneducativaCursoCupo;
use Sie\AppWebBundle\Form\PreinsInstitucioneducativaCursoCupoType;

/**
 * PreinsInstitucioneducativaCursoCupo controller.
 *
 */
class PreinsInstitucioneducativaCursoCupoController extends Controller
{

    /**
     * Lists all PreinsInstitucioneducativaCursoCupo entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo')->findAll();

        return $this->render('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new PreinsInstitucioneducativaCursoCupo entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new PreinsInstitucioneducativaCursoCupo();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('uealtademanda_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a PreinsInstitucioneducativaCursoCupo entity.
     *
     * @param PreinsInstitucioneducativaCursoCupo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(PreinsInstitucioneducativaCursoCupo $entity)
    {
        $form = $this->createForm(new PreinsInstitucioneducativaCursoCupoType(), $entity, array(
            'action' => $this->generateUrl('uealtademanda_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new PreinsInstitucioneducativaCursoCupo entity.
     *
     */
    public function newAction()
    {
        $entity = new PreinsInstitucioneducativaCursoCupo();
        $form   = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a PreinsInstitucioneducativaCursoCupo entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PreinsInstitucioneducativaCursoCupo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing PreinsInstitucioneducativaCursoCupo entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PreinsInstitucioneducativaCursoCupo entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a PreinsInstitucioneducativaCursoCupo entity.
    *
    * @param PreinsInstitucioneducativaCursoCupo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(PreinsInstitucioneducativaCursoCupo $entity)
    {
        $form = $this->createForm(new PreinsInstitucioneducativaCursoCupoType(), $entity, array(
            'action' => $this->generateUrl('uealtademanda_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing PreinsInstitucioneducativaCursoCupo entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PreinsInstitucioneducativaCursoCupo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('uealtademanda_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a PreinsInstitucioneducativaCursoCupo entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find PreinsInstitucioneducativaCursoCupo entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('uealtademanda'));
    }

    /**
     * Creates a form to delete a PreinsInstitucioneducativaCursoCupo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('uealtademanda_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    public function editarAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id=$request->get('iduealtadem');

        $entities = $em->getRepository('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo')->find($id);
        $entities2 = $em->getRepository('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo')->findBy(array('institucioneducativa' =>$entities->getInstitucioneducativa()));
        // echo ">".$id;exit();
        /*dump($entities2); exit();
        $entities2=array_unique($entities2,SORT_NUMERIC);*/
        $nivel_tipo = array();
            foreach ($entities2 as $cur) {
                $nivel_tipo[] = array(
                    'id' => $cur->getId(),
                    'nivel_tipo_id' => $cur->getNivelTipo()->getId(),
                    'nivel_tipo' => $cur->getNivelTipo()->getNivel(),
                    
                );
            }
        // dump($nivel_tipo); exit();
        return $this->render('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo:edit_preinscripcion.html.twig', array(
            'nivel_tipo'      => $nivel_tipo,
            'entity'      => $entities,
        ));

    } 
    public function mostrar_grado_cursoAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $nivel_tipo_id=$request->get('nivel_tipo_id');
        $id_sie=$request->get('id_sie');


        // echo $nivel_tipo_id.'>>> '.$id_sie;exit();
        $entities2 = $em->getRepository('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo')->findBy(array('institucioneducativa' =>$id_sie,'nivelTipo'=>$nivel_tipo_id));
        // echo ">".$id;exit();
        // dump($entities2); exit();
        $nivel_grado = array();
        foreach ($entities2 as $cur) {
            $nivel_grado[] = array(
                'id_grado' => $cur->getGradoTipo()->getId(),
                'grado' => $cur->getGradoTipo()->getGrado(),
            );
        }

        $arrResponse = array(
            'arrGrado' => $nivel_grado
          );
        // dump($arrResponse); exit();
          $response->setStatusCode(200);
          $response->setData($arrResponse);
          return $response;

        // dump($nivel_grado); exit();
    }   
    public function guardar_editar_preinscripcionAction(Request $request){
        // dump($request);die;
        $em = $this->getDoctrine()->getManager();
        $id=$request->get('id');
        $nivel_tipo_id=$request->get('nivel_tipo_id');
        $idgrado=$request->get('idgrado');
        $cupo=$request->get('cupo');
        $obj=mb_strtoupper($request->get('obj'),'utf-8');

        // echo ">".$nivel_tipo_id;exit();
        // $GuardarPreinscripcion= new PreinsInstitucioneducativaCursoCupo();
        $GuardarPreinscripcion = $em->getRepository('SieAppWebBundle:PreinsInstitucioneducativaCursoCupo')->find($id);
        // dump($GuardarPreinscripcion);die;
        $GuardarPreinscripcion->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel_tipo_id));
        $GuardarPreinscripcion->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($idgrado));
        // $GuardarEspecialidad->setInstitucioneducativa($institucioneducativa_id);
        $GuardarPreinscripcion->setCantidadCupos($cupo);
        $GuardarPreinscripcion->setObservacion($obj);
        // $em->persist($GuardarPreinscripcion);
        $em->flush();
        //do the commit of DB
        // $em->getConnection()->commit();
        $message = 'Operación realizada correctamente';
        $this->addFlash('goodstate', $message);
        return $this->redirectToRoute('uealtademanda');
    }
}
