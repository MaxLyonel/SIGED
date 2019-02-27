<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EstudianteTrabajaNuevoController extends Controller
{
    public function indexAction()
    {
        return $this->render('SieHerramientaBundle:EstudianteTrabajaNuevo:index.html.twig', array(
                // ...
            ));    }

    public function findAction()
    {
        return $this->render('SieHerramientaBundle:EstudianteTrabajaNuevo:find.html.twig', array(
                // ...
            ));    }

    public function resultAction()
    {
        return $this->render('SieHerramientaBundle:EstudianteTrabajaNuevo:result.html.twig', array(
                // ...
            ));    }

    public function doInscriptionAction()
    {
        return $this->render('SieHerramientaBundle:EstudianteTrabajaNuevo:doInscription.html.twig', array(
                // ...
            ));    }

    public function listAction()
    {
        return $this->render('SieHerramientaBundle:EstudianteTrabajaNuevo:list.html.twig', array(
                // ...
            ));    }

}
