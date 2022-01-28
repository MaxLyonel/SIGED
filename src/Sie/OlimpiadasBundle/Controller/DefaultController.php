<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('SieOlimpiadasBundle:Default:index.html.twig', array('name' => $name));
    }
}
