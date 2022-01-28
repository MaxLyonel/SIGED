<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Form\EstudianteType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Estudiante controller.
 *
 */
class StudentQueryController extends Controller {
//    public $session;
//
//    public function __construct() {
//        $this->session = new Session();
//    }

    /**
     * Lists all Estudiante entities.
     *
     */
    public function indexAction($rude) {

        $em = $this->getDoctrine()->getManager();
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));
        $answer = false;
        if ($objStudent) {
            $answer = true;
        }
        // data es un array con claves 'name', 'email', y 'message'
        //return new Response($answer);
        return new Response('<html><body>Hello ' . $rude . '!</body></html>');
    }

}
