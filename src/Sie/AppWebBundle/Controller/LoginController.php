<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Sie\AppWebBundle\Entity\Usuario;
use Sie\AppWebBundle\Entity\UsuarioSession;

class LoginController extends Controller {
    
    /**
     * create the view interface to login with some rol
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) { //? (--> 2)
        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        //? SE OBTIENE EL ID DEL ROL DEL USUARIO
        $rolUserId = $sesion->get('roluser');


        //? SE REGISTRA LA SESSION DEL USUARIO
        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('usuario_session');")->execute();
        $sesionUsuario = new UsuarioSession();
        $sesionUsuario->setUsuarioId($sesion->get('userId'));
        $nombreUsuario = $sesion->get('name') . " " . $sesion->get('lastname') . " " . $sesion->get('lastname2');
        $sesionUsuario->setNombre($nombreUsuario);
        $sesionUsuario->setFecharegistro((new \DateTime('now'))->format('Y-m-d H:i:s'));
        //dump((new \DateTime('now'))->format('Y-m-d H:i:s')); die;
        $sesionUsuario->setUserName($sesion->get('userName'));
        $sesionUsuario->setIp($_SERVER['REMOTE_ADDR']);

        $sesionUsuario->setLogeoEstado('1');
        $sesionUsuario->setObservaciones($sesion->get('pathSystem').'-/-'.$_SERVER['HTTP_USER_AGENT']);
        $sesionUsuario->setRolTipoId($rolUserId);
        $em->persist($sesionUsuario);
        $em->flush();

        return $this->redirect($this->generateUrl('principal_web'));
    }

    /*public function dirselunieduAction(Request $request, $ue) {
        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $sesion->set('ie_id', $ue);
        $sesion->set('ie_per_estado', '-1');
        $sesion->set('ie_nombre', $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ue)->getInstitucioneducativa());
        $cuenta = $em->getRepository('SieAppWebBundle:RolTipo')->find($sesion->get('roluser'));
        $sesion->set('cuentauser', $cuenta->getRol());
        return $this->redirect($this->generateUrl('principal_web'));
    }*/

    public function sieAction() {
        return $this->redirectToRoute('login');
    }

    public function userlogedAction(Request $request, $persona, $aOptionUser) {
        return $this->render('SieAppWebBundle:WelcomeSiged:index.html.twig', array('persona' => $persona, 'aOptionUser' => $aOptionUser));
    }

    /**
     * build the login interface
     * @param Request $request
     * @return type
     */
    public function logoutAction(Request $request) {
        $sesion = $request->getSession();
        $sesion->clear();
        $usuario = new Usuario();
        $form = $this->createFormBuilder($usuario)
                ->setAction($this->generateUrl('sie_login_homepage'))
                ->add('username', 'text', array('required' => true, 'invalid_message' => 'Campor 1 obligatorio'))
                ->add('password', 'password', array('required' => true, 'invalid_message' => 'Campor 2 obligatorio'))
                ->add('check', 'checkbox', array('mapped' => false, 'label' => 'Omitir esta validacion xd', 'required' => false))
                ->add('captcha', 'captcha', array('label' => 'Registre la imagen',
                    'width' => 250,
                    'height' => 70,
                    'length' => 6,
                    'required' => false,
                    'invalid_message' => 'The captcha code is invalid.'
                ))
                ->add('save', 'submit', array('label' => 'Aceptar'))
                ->getForm();
        return $this->render('SieAppWebBundle:Login:login.html.twig', array("form" => $form->createView()));
    }
}
