<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RegStudentController extends Controller
{
    public function indexAction()
    {
        return $this->render('SieHerramientaBundle:RegStudent:index.html.twig', array(
                // ...
            ));    }

    public function findAction()
    {
        return $this->render('SieHerramientaBundle:RegStudent:find.html.twig', array(
                // ...
            ));    }

    public function registerAction()
    {
        return $this->render('SieHerramientaBundle:RegStudent:register.html.twig', array(
                // ...
            ));    }

}
