<?php

namespace Sie\RieBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        //return $this->render('SieRieBundle:Default:index.html.twig');
        echo '<br><br><br><br><br><br><br><br><br>';
        echo '<center>estamos en controlador</center>';
        //return $this->render('SieRieBundle:Default:rie.html.twig');

    }
}
