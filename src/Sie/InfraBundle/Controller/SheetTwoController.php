<?php

namespace Sie\InfraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SheetTwoController extends Controller
{
    public function indexAction()
    {
        return $this->render('SieInfraBundle:SheetTwo:index.html.twig', array(
                // ...
            ));    }

    public function accessAction()
    {
        return $this->render('SieInfraBundle:SheetTwo:access.html.twig', array(
                // ...
            ));    }

}
