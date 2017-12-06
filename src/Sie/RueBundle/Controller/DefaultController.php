<?php

namespace Sie\RueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('SieRueBundle:Default:index.html.twig', array('name' => $name));
    }
}
