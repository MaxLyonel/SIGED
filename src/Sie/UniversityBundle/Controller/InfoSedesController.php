<?php

namespace Sie\UniversityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class InfoSedesController extends Controller
{
    public function indexAction()
    {
        return $this->render('SieUniversityBundle:InfoSedes:index.html.twig', array(
                // ...
            ));    }

}
