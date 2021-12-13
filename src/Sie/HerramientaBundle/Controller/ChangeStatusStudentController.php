<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ChangeStatusStudentController extends Controller
{
    public function indexAction()
    {
        return $this->render('SieHerramientaBundle:ChangeStatusStudent:index.html.twig', array(
                // ...
            ));    }

}
