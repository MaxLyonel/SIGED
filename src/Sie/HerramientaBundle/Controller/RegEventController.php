<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RegEventController extends Controller
{
    public function indexAction()
    {
        return $this->render('SieHerramientaBundle:RegEvent:index.html.twig', array(
                // ...
            ));    }

    public function findAction()
    {
        return $this->render('SieHerramientaBundle:RegEvent:find.html.twig', array(
                // ...
            ));    }

    public function registerAction()
    {
        return $this->render('SieHerramientaBundle:RegEvent:register.html.twig', array(
                // ...
            ));    }

}
