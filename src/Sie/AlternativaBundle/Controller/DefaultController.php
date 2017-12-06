<?php

namespace Sie\AlternativaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('SieAlternativaBundle:Default:index.html.twig', array('name' => $name));
    }
}
