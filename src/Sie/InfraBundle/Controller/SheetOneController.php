<?php

namespace Sie\InfraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SheetOneController extends Controller
{
    public function indexAction()
    {
        return $this->render('SieInfraBundle:SheetOne:index.html.twig', array(
                // ...
            ));    }

    public function accessAction()
    {
        return $this->render('SieInfraBundle:SheetOne:access.html.twig', array(
                // ...
            ));    }

}
