<?php

namespace Sie\InfraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SheetFiveController extends Controller
{
    public function indexAction()
    {
        return $this->render('SieInfraBundle:SheetFive:index.html.twig', array(
                // ...
            ));
    }

    public function accessAction()
    {
        return $this->render('SieInfraBundle:SheetFive:access.html.twig', array(
                // ...
            ));    }

}
