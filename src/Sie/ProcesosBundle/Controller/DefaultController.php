<?php

namespace Sie\ProcesosBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\FlujoTipo;

class DefaultController extends Controller
{
    public function indexAction()
    {
        
        
        return $this->render('SieProcesosBundle:Default:listar.html.twig', array('procesos' => $procesos,'nombre'=>'patricia'));
    }
}
