<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * PlataformaMoodleController.
 *
 */
class PlataformaMoodleController extends Controller {

    private $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        // Validamos si el usuario ha iniciado sesión
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la sesión
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SieRegularBundle:PlataformaMoodle:index.html.twig');
    }
}