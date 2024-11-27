<?php

namespace Sie\UsuariosBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Usuariopasswd controller.
 *
 */
class UsuariopasswdController extends Controller {

    public $session;
    private $myarrayRoles;

    /**
     * construct function
     */
    public function __construct() {
        //load the session component
        $this->session = new Session();
        //$this->session->set('currentyear', '2015');
    }

    public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieUsuariosBundle:Usuario:passresetlogin.html.twig', array(
                    'form' => $this->createViewForm($sesion->get('userId'))->createView(),
        ));
    }

    public function userresetloginAction($usuarioid, Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SieUsuariosBundle:Usuario:passresetlogin.html.twig', array(
                    'form' => $this->createViewForm($usuarioid)->createView(),
        ));
    }

    private function createViewForm($iduser) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('SieAppWebBundle:Usuario')->findOneById($iduser);
        try {
            $form = $this->createFormBuilder($user)
                    ->setAction($this->generateUrl('usuario_update_passwd'))
                    ->add('id', 'hidden', array('required' => true, 'invalid_message' => 'Campo 1 obligatorio'))
                    ->add('username', 'text', array('required' => true, 'invalid_message' => 'Campo 1 obligatorio', 'disabled' => true))
                    ->add('usernameh', 'hidden', array('mapped' => false, 'data' => $user->getUsername()))
                    ->add('password', 'hidden', array('required' => true, 'data' => $user->getPassword()))
                    //->add('oldpasswd', 'text', array("mapped" => false, 'required' => 'required', 'data' => '', 'disabled' => false))
                    // ->add('newpasswd', 'password', array("mapped" => false, 'required' => 'required', 'data' => '', 'disabled' => false))
                    ->add('newpasswd', 'password', array('mapped' => false, 'required' => true))
                    ->add('repeatpasswd', 'password', array("mapped" => false, 'required' => 'required', 'data' => '', 'disabled' => false))
                    ->add('save', 'submit', array('label' => 'Guardar y volver a ingresar.'))
                    ->getForm();
            return $form;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    /**
     * update the new password
     *
     */
    public function updatepasswdAction(Request $request) {
        $sesion = $request->getSession();
        $form = $request->get('form');
        $dataserver = $request->server;
        $newPassword = $form['newpasswd'];
//        if ($form['password'] == md5($form['oldpasswd'])) {
            if ($form['newpasswd'] == $form['repeatpasswd']) {
                $em = $this->getDoctrine()->getManager();
                $entity = $em->getRepository('SieAppWebBundle:Usuario')->find($form['id']);
                //dump($entity->getPassword2()); dump(md5($form['newpasswd'])); die;
                //die('h');
                if ($entity->getPassword2() === md5($form['newpasswd'])){
                    $sesion->getFlashBag()->add('error', '¡La nueva contraseña no puede ser igual a la contraseña expirada!');
                    return $this->redirect($this->generateUrl('sie_usuarios_reset_login', array('usuarioid' => $form['id'])));
                    /*die('fg');
                    $sesion->getFlashBag()->add('error', '¡La nueva contraseña no puede ser igual a la contraseña expirada!');
                    return $this->redirectToRoute('usuariopasswd');*/
                }elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $newPassword)) {
                    $sesion->getFlashBag()->add('error', 'La contraseña debe tener al menos 8 caracteres, incluir una letra mayúscula, una letra minúscula, un número y un carácter especial (@, $, !, %, *, ?, &).');
                    return $this->redirect($this->generateUrl('sie_usuarios_reset_login', array('usuarioid' => $form['id'])));
                }else {
                    try {//UPDATE REGISTRO
                        $entity->setPassword(md5($form['newpasswd']));
                        $entity->setFechaRegistro(new \DateTime('now'));
                        // $entity->setPassword2(md5($form['newpasswd']));
                        $entity->setEstadoPassword('3');
                        $em->persist($entity);
                        $em->flush();
                        
                        //dump($entity); die;
                        $this->get('funciones')->setLogTransaccion(
                            $form['id'],
                            'usuario',
                            'U',
                            'Reset_Actualizado',
                            $entity,
                            '',
                            'Usuariopasswd/updatepasswd',
                            json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                        );

                        $sesion->getFlashBag()->add('success', 'Password registrado exitosamente!');
                        //$id_usuario = $sesion->get('userId');
                        return $this->redirect($this->generateUrl('login'));

                    } catch (Exception $exc) {
                        $sesion->getFlashBag()->add('error', $exc);
                        return $this->redirectToRoute('usuariopasswd');
                    }                    
                }
            } else {
                $sesion->getFlashBag()->add('error', 'Contraseña nueva no coincide con en la repetición.');                
                return $this->redirect($this->generateUrl('sie_usuarios_reset_login', array('usuarioid' => $form['id'])));                
            }
//        } else {
//            $sesion->getFlashBag()->add('error', 'Contraseña antigua no coincide con la actual.');
//            return $this->redirectToRoute('usuariopasswd');
//        }
    }

    /**
     * Enviamos email para informar sobre el cambio
     *
     */
    private function sendEmail($data, $server, $ip) {
        $mailer = $this->get('mailer');
        $message = $mailer->createMessage()
                ->setSubject($data['usernameh'] . ', tu password fue satisfactoriamente cambiado (SIE)')
                ->setFrom('cafre04@gmail.com')
                ->setTo('siekrlospc@gmail.com')
                ->setBody(
                $this->renderView(
                   'Emails/registration.html.twig', array('data' => $data, 'server' => $server, 'ip' => $ip)
                ), 'text/html'
                )
        ;
        $mailer->send($message);
    }

}
