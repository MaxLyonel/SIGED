<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;

/**
 * EstudianteInscripcion controller.
 *
 */
class ConsoleController extends Controller {

    public $session;

    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Funcion para abrir la consola de consulta
     */
    public function indexAction(Request $request) {
        if ($this->session->get('roluser') == 8 ) {
            return $this->render('SieRegularBundle:Console:index.html.twig');
        }
        die('Error 403 Forbiden, page not available');
        return false;
    }

    public function ejecutarAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare($request->get('consulta'));
        $query->execute();
        $resultado = $query->fetchAll();

        $response = new JsonResponse();
        $response->setData($resultado);

        return $response;
    }
}
