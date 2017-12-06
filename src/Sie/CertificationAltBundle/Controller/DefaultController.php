<?php

namespace Sie\CertificationAltBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('SieCertificationAltBundle:Default:index.html.twig', array('name' => $name));
    }
}
