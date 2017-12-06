<?php

namespace Sie\InfraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SheetSixController extends Controller
{
    public function indexAction()
    {
        return $this->render('SieInfraBundle:SheetSix:index.html.twig', array(
                // ...
            ));    }

    public function accessAction()
    {
        return $this->render('SieInfraBundle:SheetSix:access.html.twig', array(
                // ...
            ));    }

}
