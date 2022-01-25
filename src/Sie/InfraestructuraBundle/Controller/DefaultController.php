<?php

namespace Sie\InfraestructuraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('SieInfraestructuraBundle:Default:index.html.twig', array('name' => $name));
    }
}
