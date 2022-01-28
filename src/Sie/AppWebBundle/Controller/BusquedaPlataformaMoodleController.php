<?php

namespace Sie\AppWebBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;

use Sie\AppWebBundle\Entity\Estudiante; 

class BusquedaPlataformaMoodleController extends Controller
{
    public $session;

    public function __construct() {
        $this->session = new Session();
    }
    
    public function indexAction() {
        return $this->render('SieAppWebBundle:ControlDatosPlataforma:index.html.twig');
    }
    public function docenteAction() {
        return $this->render('SieAppWebBundle:ControlDatosPlataforma:docente_index.html.twig');
    }
    public function validar_info_estAction(Request $request){ 
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $codigo = $request->get('codigo');
        $query = $em->getConnection()->prepare("select * FROM sp_link_plataforma('$codigo') ");
        $query->execute();
        $valor= $query->fetchAll();
        // dump($valor);die;
        // return $this->render($this->session->get('pathSystem').':ControlDatosPlataforma:informacion_plataforma_index.html.twig',array('valor'=>$valor));
        return $this->render('SieAppWebBundle:ControlDatosPlataforma:informacion_plataforma_index.html.twig', array( 'valor' => $valor,'codigo' => $codigo, ));
    }
    
}