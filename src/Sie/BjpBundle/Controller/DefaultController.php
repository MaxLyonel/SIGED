<?php

namespace Sie\BjpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('SieBjpBundle:Default:index.html.twig', array('name' => $name));
    }
}
