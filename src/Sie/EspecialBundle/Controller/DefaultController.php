<?php

namespace Sie\EspecialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('SieEspecialBundle:Default:index.html.twig', array('name' => $name));
    }
}
