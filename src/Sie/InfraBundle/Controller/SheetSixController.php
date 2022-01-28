<?php

namespace Sie\InfraBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class SheetSixController extends Controller{

    public function indexAction(Request $request){
            //get the send data
        $form = $request->get('form');

        return $this->render('SieInfraBundle:SheetSix:index.html.twig', array(
            'infraestructuraJuridiccionGeografica' => $form['infraestructuraJuridiccionGeografica'],
            ));    
    }

    public function accessAction()
    {
        return $this->render('SieInfraBundle:SheetSix:access.html.twig', array(
                // ...
            ));    }

}
