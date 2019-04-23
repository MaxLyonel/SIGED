<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PromotorController extends Controller
{
    public function indexAction()
    {
        return $this->render('SieHerramientaBundle:Promotor:index.html.twig', array(
                // ...
            ));    }

    public function findAction()
    {
        return $this->render('SieHerramientaBundle:Promotor:find.html.twig', array(
                // ...
            ));    }

    public function registerAction()
    {
        return $this->render('SieHerramientaBundle:Promotor:register.html.twig', array(
                // ...
            ));    }

}
