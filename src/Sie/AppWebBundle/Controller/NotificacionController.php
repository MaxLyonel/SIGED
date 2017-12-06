<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Notificacion;
use Sie\AppWebBundle\Entity\NotificacionUsuario;
use Sie\AppWebBundle\Entity\RolTipo;
use Sie\AppWebBundle\Form\NotificacionType;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Notificacion controller.
 *
 */
class NotificacionController extends Controller {

    private $session;

    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Lists all Notificacion entities.
     *
     */
    public function indexAction() {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '8') {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:Notificacion');

        $query = $repository->createQueryBuilder('e')
                ->orderBy('e.fechaPub', 'DESC')
                ->orderBy('e.id', 'DESC')
                ->getQuery();

        $entities = $query->getResult();

        return $this->render('SieAppWebBundle:Notificacion:index.html.twig', array(
                    'entities' => $entities,
        ));
    }

    /**
     * Creates a new Notificacion entity.
     *
     */
    public function createAction(Request $request) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '8') {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $entity = new Notificacion();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {

            if (null != $form->get('adjunto')->getData()) {
                $file = $entity->getAdjunto();

                $filename = md5(uniqid()) . '.' . $file->guessExtension();

                $adjuntoDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/adjuntos';
                $file->move($adjuntoDir, $filename);

                $entity->setAdjunto($filename);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $form_x = $request->get('sie_appwebbundle_notificacion');
            $multiple = $form_x['rolTipo'];

            foreach ($multiple as $value) {
                $nu = new NotificacionUsuario();
                $nu->setNotif($entity);

                $rt = $em->getRepository('SieAppWebBundle:RolTipo')->find($value);
                $nu->setRolTipo($rt);
                $em->persist($nu);
                $em->flush();
            }

            return $this->redirect($this->generateUrl('notificacion_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:Notificacion:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Notificacion entity.
     *
     * @param Notificacion $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Notificacion $entity) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '8') {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $form = $this->createForm(new NotificacionType(), $entity, array(
            'action' => $this->generateUrl('notificacion_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Guardar'));

        return $form;
    }

    /**
     * Displays a form to create a new Notificacion entity.
     *
     */
    public function newAction() {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '8') {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $entity = new Notificacion();
        $form = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:Notificacion:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Notificacion entity.
     *
     */
    public function showAction($id) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '8') {
            return $this->redirect($this->generateUrl('principal_web'));
        }
        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:Notificacion');

        $query = $repository->createQueryBuilder('n')
                ->select('n')
                ->innerJoin('SieAppWebBundle:NotificacionUsuario', 'u', 'WITH', 'u.notif = n.id')
                ->where('n.id = :id')
                ->setParameter('id', $id)
                ->getQuery();

        $entity = $query->getSingleResult();

        $query = $repository->createQueryBuilder('n')
                ->select('u')
                ->innerJoin('SieAppWebBundle:NotificacionUsuario', 'u', 'WITH', 'u.notif = n.id')
                ->where('n.id = :id')
                ->setParameter('id', $id)
                ->getQuery();

        $entities = $query->getResult();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Notificacion entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:Notificacion:show.html.twig', array(
                    'entity' => $entity,
                    'entities' => $entities,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Notificacion entity.
     *
     */
    public function editAction($id) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '8') {
            return $this->redirect($this->generateUrl('principal_web'));
        }
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Notificacion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Notificacion entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:Notificacion:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Notificacion entity.
     *
     * @param Notificacion $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Notificacion $entity) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '8') {
            return $this->redirect($this->generateUrl('principal_web'));
        }
        $form = $this->createForm(new NotificacionType(), $entity, array(
            'action' => $this->generateUrl('notificacion_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }

    /**
     * Edits an existing Notificacion entity.
     *
     */
    public function updateAction(Request $request, $id) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '8') {
            return $this->redirect($this->generateUrl('principal_web'));
        }
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Notificacion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Notificacion entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('notificacion_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:Notificacion:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Notificacion entity.
     *
     */
    public function deleteAction(Request $request, $id) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '8') {
            return $this->redirect($this->generateUrl('principal_web'));
        }
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:Notificacion')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Notificacion entity.');
            }

            if ($entity->getAdjunto() != null) {
                $file = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/adjuntos/' . $entity->getAdjunto();
                if (file_exists($file) && is_writable($file)) {
                    unlink($file);
                }
            }

            $em2 = $this->getDoctrine()->getManager();
            $entity2 = $em2->getRepository('SieAppWebBundle:NotificacionUsuario')->findByNotif($entity);

            foreach ($entity2 as $notifusu) {
                $em2->remove($notifusu);
            }

            $em2->flush();

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('notificacion'));
    }

    /**
     * Creates a form to delete a Notificacion entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '8') {
            return $this->redirect($this->generateUrl('principal_web'));
        }
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('notificacion_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Eliminar'))
                        ->getForm()
        ;
    }

    /**
     * Lists all Notificacion entities for frontend.
     *
     */
    public function listAction() {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:Notificacion');

        $query = $repository->createQueryBuilder('n')
                ->select('u')
                ->innerJoin('SieAppWebBundle:NotificacionUsuario', 'u', 'WITH', 'u.notif = n.id')
                ->orderBy('n.fechaPub', 'DESC')
                ->orderBy('n.id', 'DESC')
                ->getQuery();

        $entities = $query->getResult();

        return $this->render('SieAppWebBundle:Notificacion:list.html.twig', array(
                    'entities' => $entities,
        ));
    }

    /**
     * Finds and displays a Notificacion entity for frontend.
     *
     */
    public function showallAction($id) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:Notificacion');

        $query = $repository->createQueryBuilder('n')
                ->select('n')
                ->innerJoin('SieAppWebBundle:NotificacionUsuario', 'u', 'WITH', 'u.notif = n.id')
                ->where('n.id = :id')
                ->setParameter('id', $id)
                ->getQuery();

        $entity = $query->getSingleResult();

        $query = $repository->createQueryBuilder('n')
                ->select('u')
                ->innerJoin('SieAppWebBundle:NotificacionUsuario', 'u', 'WITH', 'u.notif = n.id')
                ->where('n.id = :id')
                ->setParameter('id', $id)
                ->getQuery();

        $entities = $query->getResult();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Notificacion entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:Notificacion:show_all.html.twig', array(
                    'entity' => $entity,
                    'entities' => $entities,
                    //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Make readed a Notificacion entity.
     *
     */
    public function readedAction() {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Notificacion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Notificacion entity.');
        }

        $userone = $em->getRepository('SieAppWebBundle:Notificacion')->find($id);

        if (!$userone) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }



        return $this->redirect($this->generateUrl('principal_web'));
    }

    /**
     * Updates estado a Notificacion entity.
     *
     */
    public function estadoAction($id) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '8') {
            return $this->redirect($this->generateUrl('principal_web'));
        }


        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Notificacion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Notificacion entity.');
        }

        $estado = $entity->getEstado();
        $estado = 1 - $estado;
        $entity->setEstado($estado);

        $em->flush();

        return $this->redirect($this->generateUrl('notificacion'));
    }

}
