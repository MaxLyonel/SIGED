<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EstudianteTrabajaController extends Controller
{
    public function indexAction()
    {
        return $this->render('SieHerramientaBundle:EstudianteTrabaja:index.html.twig', array(
                // ...
            ));    }

    public function saveAction()
    {
        return $this->render('SieHerramientaBundle:EstudianteTrabaja:save.html.twig', array(
                // ...
            ));    }

}
