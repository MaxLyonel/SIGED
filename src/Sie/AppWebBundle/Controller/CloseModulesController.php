<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;

class CloseModulesController extends Controller {

    public $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request, $sie, $gestion, $operativo) {

        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($sie);
        $notaTipo = $em->getRepository('SieAppWebBundle:NotaTipo')->findOneById($operativo);

        return $this->render($this->session->get('pathSystem') . ':CloseModules:index.html.twig', array(
            'institucion' => $institucion,
            'gestion' => $gestion,
            'operativo' => $notaTipo
        ));
    }

    public function closeBjpAction(Request $request, $sie, $gestion) {

        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($sie);

        return $this->render($this->session->get('pathSystem') . ':CloseModules:close_bjp.html.twig', array(
            'institucion' => $institucion,
            'gestion' => $this->session->get('currentyear')
        ));
    }

}
