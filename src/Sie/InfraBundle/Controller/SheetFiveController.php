<?php

namespace Sie\InfraBundle\Controller;



use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\InfraestructuraH5Ambientepedagogico;
use Sie\AppWebBundle\Form\InfraestructuraH5AmbientepedagogicoType;

class SheetFiveController extends Controller
{
    public function indexAction(Request $request){
        return $this->render('SieInfraBundle:SheetFive:index.html.twig', array(
                // ...
            ));
    }

    public function accessAction(){

        return $this->render('SieInfraBundle:SheetFive:access.html.twig', array(
                // ...
            ));    
    }
    
    /**
     * [accessAction description]
     * @return [type] [description]
     */
    public function pedagogicoAction(){

         $infraestructuraJuridiccionGeograficaId = 11392;
         $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('SieAppWebBundle:InfraestructuraH5Ambientepedagogico')->findBy(array(
            'infraestructuraJuridiccionGeografica'=> $infraestructuraJuridiccionGeograficaId
        ));

        return $this->render('SieAppWebBundle:InfraestructuraH5Ambientepedagogico:index.html.twig', array(
            'entities' => $entities,
            'infraestructuraJuridiccionGeograficaId' => $infraestructuraJuridiccionGeograficaId,
        ));

        // return $this->render('SieInfraBundle:SheetFive:access.html.twig', array(
        //         // ...
        // ));    
    }    
    
    /**
     * [pedagogicoDeportivoAction description]
     * @return [type] [description]
     */
    public function pedagogicoDeportivoAction(){

        return $this->render('SieInfraBundle:SheetFive:access.html.twig', array(
                // ...
        ));    
    }

}
