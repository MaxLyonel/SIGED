<?php

namespace Sie\ValidacionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

class RegularController extends Controller
{
    private $session;

    public function __construct() {
        $this->session = new Session();
    }
    
    /*
     * 
     */
    
    public function indexAction()
    {
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        

        return $this->render('SieValidacionBundle:Regular:index.html.twig');
    }
}
