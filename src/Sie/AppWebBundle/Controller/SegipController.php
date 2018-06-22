<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Form\BuscarPersonaSegipType;
use Sie\AppWebBundle\Form\VerificarPersonaSegipType;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Client;

/**
 * SegipController.
 *
 */
class SegipController extends Controller {

    private $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction() {

        $formBuscarPersona = $this->createForm(new BuscarPersonaSegipType(), 
            null, 
            array(
                'action' => $this->generateUrl('segip_search'), 
                'method' => 'POST'
            )
        );

        $formVerificarPersona = $this->createForm(new VerificarPersonaSegipType(), 
            null, 
            array(
                'action' => $this->generateUrl('segip_verify'), 
                'method' => 'POST'
            )
        );

        return $this->render('SieAppWebBundle:Segip:index.html.twig', array(
            'form1' => $formBuscarPersona->createView(),
            'form2' => $formVerificarPersona->createView(),
        ));
    }

    public function searchAction(Request $request){        
        
        $form = $request->get('sie_buscar_persona_segip');
        
        $resultado = $this->get('sie_app_web.segip')->buscarPersona($form['carnet'], $form['complemento'], $form['fechaNac']);

        return $this->render('SieAppWebBundle:Segip:resultado1.html.twig',array(
            'resultado'=>$resultado
        ));
    }

    public function verifyAction(Request $request){
        
        $form = $request->get('sie_verificar_persona_segip');
        
        $resultado = $this->get('sie_app_web.segip')->verificarPersona($form['carnet'], $form['complemento'], $form['paterno'], $form['materno'], $form['nombre'], $form['fechaNac']);

        return $this->render('SieAppWebBundle:Segip:resultado2.html.twig',array(
            'resultado'=>$resultado
        ));
    }
}
