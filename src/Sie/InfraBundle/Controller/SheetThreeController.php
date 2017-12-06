<?php

namespace Sie\InfraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SheetThreeController extends Controller
{
    public function indexAction()
    {
        return $this->render('SieInfraBundle:SheetThree:index.html.twig', array(
                // ...
            ));    }

    public function accessAction()
    {
        return $this->render('SieInfraBundle:SheetThree:access.html.twig', array(
                // ...
            ));    }

}
