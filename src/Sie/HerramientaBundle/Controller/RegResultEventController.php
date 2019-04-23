<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RegResultEventController extends Controller
{
    public function indexAction()
    {
        return $this->render('SieHerramientaBundle:RegResultEvent:index.html.twig', array(
                // ...
            ));    }

    public function findAction()
    {
        return $this->render('SieHerramientaBundle:RegResultEvent:find.html.twig', array(
                // ...
            ));    }

}
