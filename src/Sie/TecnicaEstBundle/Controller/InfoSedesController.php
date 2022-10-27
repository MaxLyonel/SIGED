<?php

namespace Sie\TecnicaEstBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class InfoSedesController extends Controller
{
    public function indexAction()
    {
        return $this->render('SieTecEstBundle:InfoSedes:index.html.twig', array(
                // ...
            ));    }

}
