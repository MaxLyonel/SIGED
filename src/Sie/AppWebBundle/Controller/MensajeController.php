<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\Mensaje;
use Sie\AppWebBundle\Entity\MensajeUsuario;
use Sie\AppWebBundle\Entity\Usuario;
use Sie\AppWebBundle\Form\MensajeType;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Mensaje controller.
 *
 */
class MensajeController extends Controller
{
    private $session;

    public function __construct() {
        $this->session = new Session();
    }
    
    /**
     * Lists all Mensaje entities.
     *
     */
    public function indexAction()
    {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '8') {
            return $this->redirect($this->generateUrl('principal_web'));
        }
        
        return $this->redirect($this->generateUrl('mensaje_inbox'));
        
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:Mensaje')->findAll();

        return $this->render('SieAppWebBundle:Mensaje:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    
    public function inboxAction()
    {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '8') {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:Mensaje');

        $query = $repository->createQueryBuilder('m')
                ->select('m.id, m.asunto, m.fecha, m.adjunto1, m.adjunto2, mu.leido, p.paterno, p.materno, p.nombre')
                ->innerJoin('SieAppWebBundle:MensajeUsuario', 'mu', 'WITH', 'mu.mensaje = m.id')
                ->innerJoin('SieAppWebBundle:Usuario', 'u', 'WITH', 'mu.emisor = u.id')
                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'u.persona = p.id')
                ->where('mu.receptor = :receptor')
                ->orderBy('m.fecha', 'DESC')
                ->orderBy('m.id', 'DESC')
                ->setParameter('receptor', $id_usuario)
                ->getQuery();

        $entities = $query->getResult();
        
         //Consulta Mensajes nuevos recibidos

        $emM = $this->getDoctrine()->getManager();

        $repositoryM = $emM->getRepository('SieAppWebBundle:Mensaje');
        $queryT = $repositoryM->createQueryBuilder('m')
                ->select('COUNT(m.id)')
                ->innerJoin('SieAppWebBundle:MensajeUsuario', 'mu', 'WITH', 'mu.mensaje = m.id')
                ->innerJoin('SieAppWebBundle:Usuario', 'u', 'WITH', 'mu.emisor = u.id')
                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'u.persona = p.id')
                ->where('mu.receptor = :receptor')
                ->andWhere('mu.leido = :leido')
                ->setParameter('receptor', $id_usuario)
                ->setParameter('leido', 'f')
                ->getQuery();
        
        $total = $queryT->getSingleScalarResult();
        
        $this->session->set('mensajesNuevosT', $total);
        
        return $this->render('SieAppWebBundle:Mensaje:inbox.html.twig', array(
            'entities' => $entities,
        ));
    }
    
    public function sentAction()
    {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '8') {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:Mensaje');

        $query = $repository->createQueryBuilder('m')
                ->select('m.id, m.asunto, m.fecha, m.adjunto1, m.adjunto2, mu.leido, p.paterno, p.materno, p.nombre')
                ->innerJoin('SieAppWebBundle:MensajeUsuario', 'mu', 'WITH', 'mu.mensaje = m.id')
                ->innerJoin('SieAppWebBundle:Usuario', 'u', 'WITH', 'mu.receptor = u.id')
                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'u.persona = p.id')
                ->where('mu.emisor = :emisor')
                ->orderBy('m.fecha', 'DESC')
                ->orderBy('m.id', 'DESC')
                ->setParameter('emisor', $id_usuario)
                ->getQuery();

        $entities = $query->getResult();

        return $this->render('SieAppWebBundle:Mensaje:sent.html.twig', array(
            'entities' => $entities,
        ));
    }
    
    /**
     * Creates a new Mensaje entity.
     *
     */
    public function createAction(Request $request)
    {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $entity = new Mensaje();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (null != $form->get('adjunto1')->getData()) {
                $file = $entity->getAdjunto1();

                $filename = md5(uniqid()) . '.' . $file->guessExtension();

                $adjuntoDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/mensaje/adjuntos';
                $file->move($adjuntoDir, $filename);

                $entity->setAdjunto1($filename);
            }
            
            if (null != $form->get('adjunto2')->getData()) {
                $file = $entity->getAdjunto2();

                $filename = md5(uniqid()) . '.' . $file->guessExtension();

                $adjuntoDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/mensaje/adjuntos';
                $file->move($adjuntoDir, $filename);

                $entity->setAdjunto2($filename);
            }
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            
            $form_x = $request->get('sie_appwebbundle_mensaje');
            $multiple = $form_x['receptor'];

            foreach ($multiple as $value) {
                $mu = new MensajeUsuario();
                $mu->setMensaje($entity);

                $receptor = $em->getRepository('SieAppWebBundle:Usuario')->find($value);
                $emisor = $em->getRepository('SieAppWebBundle:Usuario')->find($id_usuario);
                $mu->setReceptor($receptor);
                $mu->setEmisor($emisor);
                $em->persist($mu);
                $em->flush();
            }

            $this->get('session')->getFlashBag()->add('msgsent', 'El Mensaje ha sido enviado correctamente.');
            return $this->redirect($this->generateUrl('mensaje_inbox'));
        }

        return $this->render('SieAppWebBundle:Mensaje:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }
    
    /**
     * Reply Mensaje entity.
     *
     */
    public function replyMsgAction(Request $request)
    {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $form_x = $request->get('form');
        $multiple = $form_x['receptor'];
        
        $entity = new Mensaje();
        $form = $this->createReplyForm($entity, $multiple[0]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (null != $form->get('adjunto1')->getData()) {
                $file = $entity->getAdjunto1();

                $filename = md5(uniqid()) . '.' . $file->guessExtension();

                $adjuntoDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/mensaje/adjuntos';
                $file->move($adjuntoDir, $filename);

                $entity->setAdjunto1($filename);
            }
            
            if (null != $form->get('adjunto2')->getData()) {
                $file = $entity->getAdjunto2();

                $filename = md5(uniqid()) . '.' . $file->guessExtension();

                $adjuntoDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/mensaje/adjuntos';
                $file->move($adjuntoDir, $filename);

                $entity->setAdjunto2($filename);
            }
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            
            $form_x = $request->get('form');
            $multiple = $form_x['receptor'];

            foreach ($multiple as $value) {
                $mu = new MensajeUsuario();
                $mu->setMensaje($entity);

                $receptor = $em->getRepository('SieAppWebBundle:Usuario')->find($value);
                $emisor = $em->getRepository('SieAppWebBundle:Usuario')->find($id_usuario);
                $mu->setReceptor($receptor);
                $mu->setEmisor($emisor);
                $em->persist($mu);
                $em->flush();
            }

            return $this->redirect($this->generateUrl('mensaje_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:Mensaje:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }


    /**
     * Creates a form to create a Mensaje entity.
     *
     * @param Mensaje $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Mensaje $entity)
    {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $form = $this->createForm(new MensajeType(), $entity, array(
            'action' => $this->generateUrl('mensaje_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Enviar'));

        return $form;
    }
    
    /**
     * Creates a form to reply a Mensaje entity.
     *
     * @param Mensaje $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createReplyForm(Mensaje $entity, $id)
    {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();

        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($id);
        
        $form = $this->createFormBuilder($entity)
                ->add('asunto', null, array('label' => 'Asunto'))
                ->add('mensaje', null, array('label' => 'Mensaje'))
                ->add('fecha', 'datetime', array('data' => new \DateTime('now'), 'label' => 'Fecha'))
                ->add('adjunto1', 'file', array('label' => 'Adjunto 1', 'required' => false, 'data_class' => null))
                ->add('adjunto2', 'file', array('label' => 'Adjunto 2', 'required' => false, 'data_class' => null))
                ->add('emisor', 'text', array(
                    'mapped' => false,
                    'label' => 'De',
                    'read_only' => true,
                    ))
                ->add('receptor', 'choice', array(
                    'mapped' => false,
                    'required' => true,
                    'label' => 'Para',
                    'multiple' => true,
                    'choices' => array($usuario->getId() => $usuario->getUsername())
                    ))
                ->add('submit', 'submit', array('label' => 'Enviar'))
                ->setAction($this->generateUrl('mensaje_reply_msg'))
                ->setMethod('POST')
                ->getForm();
        
//        $form = $this->createForm(new MensajeType(), $entity, array(
//            'action' => $this->generateUrl('mensaje_create'),
//            'method' => 'POST',
//        ));
//
//        $form->add('submit', 'submit', array('label' => 'Enviar'));

        return $form;
    }

    /**
     * Displays a form to create a new Mensaje entity.
     *
     */
    public function newAction()
    {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '8') {
            return $this->redirect($this->generateUrl('principal_web'));
        }
        
        $entity = new Mensaje();
        $form   = $this->createCreateForm($entity);
        
        return $this->render('SieAppWebBundle:Mensaje:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }
    
    /**
     * Displays a form to reply a Mensaje entity.
     *
     */
    public function replyAction($id)
    {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $entity = new Mensaje();
        
        $form = $this->createReplyForm($entity, $id);
        
        return $this->render('SieAppWebBundle:Mensaje:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Mensaje entity.
     *
     */
    public function showAction($id)
    {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Mensaje')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Mensaje entity.');
        }

        return $this->render('SieAppWebBundle:Mensaje:show.html.twig', array(
            'entity'      => $entity,
        ));
    }
    
    /**
     * Finds and displays a Mensaje entity.
     *
     */
    public function readedInAction($id)
    {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Mensaje')->find($id);        

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Mensaje entity.');
        }
        
        $repository = $em->getRepository('SieAppWebBundle:Mensaje');

        $query = $repository->createQueryBuilder('m')
                ->select('m.id, m.asunto, m.fecha, m.adjunto1, m.adjunto2, u.id as usId, mu.leido, p.paterno, p.materno, p.nombre')
                ->innerJoin('SieAppWebBundle:MensajeUsuario', 'mu', 'WITH', 'mu.mensaje = m.id')
                ->innerJoin('SieAppWebBundle:Usuario', 'u', 'WITH', 'mu.emisor = u.id')
                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'u.persona = p.id')
                ->where('mu.receptor = :receptor')
                ->andWhere('mu.mensaje = :mensaje')
                ->setParameter('receptor', $id_usuario)
                ->setParameter('mensaje', $entity)
                ->getQuery();
        
        $mu = $query->getSingleResult();
        
        $mensajeusuario = $em->getRepository('SieAppWebBundle:MensajeUsuario')->findOneBy(array('mensaje' => $entity, 'receptor' => $id_usuario));
        $mensajeusuario->setLeido('t');
        $em->flush();

        return $this->render('SieAppWebBundle:Mensaje:readed_in.html.twig', array(
            'entity'      => $entity,
            'mu' => $mu,
        ));
    }
    
    /**
     * Finds and displays a Mensaje entity.
     *
     */
    public function readedOutAction($id)
    {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Mensaje')->find($id);        

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Mensaje entity.');
        }

        return $this->render('SieAppWebBundle:Mensaje:readed_out.html.twig', array(
            'entity'      => $entity,
        ));
    }

    /**
     * Displays a form to edit an existing Mensaje entity.
     *
     */
//    public function editAction($id)
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('SieAppWebBundle:Mensaje')->find($id);
//
//        if (!$entity) {
//            throw $this->createNotFoundException('Unable to find Mensaje entity.');
//        }
//
//        $editForm = $this->createEditForm($entity);
//        $deleteForm = $this->createDeleteForm($id);
//
//        return $this->render('SieAppWebBundle:Mensaje:edit.html.twig', array(
//            'entity'      => $entity,
//            'edit_form'   => $editForm->createView(),
//            'delete_form' => $deleteForm->createView(),
//        ));
//    }
//
//    /**
//    * Creates a form to edit a Mensaje entity.
//    *
//    * @param Mensaje $entity The entity
//    *
//    * @return \Symfony\Component\Form\Form The form
//    */
//    private function createEditForm(Mensaje $entity)
//    {
//        $form = $this->createForm(new MensajeType(), $entity, array(
//            'action' => $this->generateUrl('mensaje_update', array('id' => $entity->getId())),
//            'method' => 'PUT',
//        ));
//
//        $form->add('submit', 'submit', array('label' => 'Update'));
//
//        return $form;
//    }
//    /**
//     * Edits an existing Mensaje entity.
//     *
//     */
//    public function updateAction(Request $request, $id)
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('SieAppWebBundle:Mensaje')->find($id);
//
//        if (!$entity) {
//            throw $this->createNotFoundException('Unable to find Mensaje entity.');
//        }
//
//        $deleteForm = $this->createDeleteForm($id);
//        $editForm = $this->createEditForm($entity);
//        $editForm->handleRequest($request);
//
//        if ($editForm->isValid()) {
//            $em->flush();
//
//            return $this->redirect($this->generateUrl('mensaje_edit', array('id' => $id)));
//        }
//
//        return $this->render('SieAppWebBundle:Mensaje:edit.html.twig', array(
//            'entity'      => $entity,
//            'edit_form'   => $editForm->createView(),
//            'delete_form' => $deleteForm->createView(),
//        ));
//    }
//    /**
//     * Deletes a Mensaje entity.
//     *
//     */
//    public function deleteAction(Request $request, $id)
//    {
//        $form = $this->createDeleteForm($id);
//        $form->handleRequest($request);
//
//        if ($form->isValid()) {
//            $em = $this->getDoctrine()->getManager();
//            $entity = $em->getRepository('SieAppWebBundle:Mensaje')->find($id);
//
//            if (!$entity) {
//                throw $this->createNotFoundException('Unable to find Mensaje entity.');
//            }
//
//            $em->remove($entity);
//            $em->flush();
//        }
//
//        return $this->redirect($this->generateUrl('mensaje'));
//    }
//
//    /**
//     * Creates a form to delete a Mensaje entity by id.
//     *
//     * @param mixed $id The entity id
//     *
//     * @return \Symfony\Component\Form\Form The form
//     */
//    private function createDeleteForm($id)
//    {
//        return $this->createFormBuilder()
//            ->setAction($this->generateUrl('mensaje_delete', array('id' => $id)))
//            ->setMethod('DELETE')
//            ->add('submit', 'submit', array('label' => 'Delete'))
//            ->getForm()
//        ;
//    }
}
