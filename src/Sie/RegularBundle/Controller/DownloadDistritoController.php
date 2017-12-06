<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use \Sie\AppWebBundle\Entity\Usuario;
use \Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;

class DownloadDistritoController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * index download distrito
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':DownloadDistrito:index.html.twig', array(
                    'form' => $this->craeteformsearchsie()->createView()
        ));
    }

    private function craeteformsearchsie() {
        return $this->createFormBuilder()
                        ->add('distrito', 'text', array('label' => 'Distrito', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9\sñÑ]{2,4}', 'maxlength' => '4', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('search', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'generateFile()')))
                        ->getForm();
    }

    /**
     * find the distrito per sie
     * @param Request $request
     * @return type the list of bachilleres
     */
    public function buildAction(Request $request) {
        $distrito = $request->get('distrito');
        $em = $this->getDoctrine()->getManager();
        $objDistrito = $em->getRepository('SieAppWebBundle:DistritoTipo')->getInfoDepByDistrito($distrito);
        $objDistrito = ($objDistrito) ? $objDistrito[0] : array();
        $dataExist = ($objDistrito) ? 1 : 0;
        return $this->render($this->session->get('pathSystem') . ':DownloadDistrito:fileDownload.html.twig', array(
                    'objDistrito' => $objDistrito,
                    'dataExist' => $dataExist
        ));
    }

}
