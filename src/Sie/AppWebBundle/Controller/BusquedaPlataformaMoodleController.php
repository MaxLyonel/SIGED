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
        $descripcion="No se encontró información para el RUDE ".$codigo.", favor verifique si escribió correctamente su Código RUDE, caso contrario comuníquese con la Unidad Educativa o Dirección Distrital de Educación correspondiente";
        // dump($valor);die;
        // return $this->render($this->session->get('pathSystem').':ControlDatosPlataforma:informacion_plataforma_index.html.twig',array('valor'=>$valor));
        return $this->render('SieAppWebBundle:ControlDatosPlataforma:informacion_plataforma_index.html.twig', array( 'valor' => $valor,'descripcion' => $descripcion, ));
    }
    public function validarInfoDocAction(Request $request){ 
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $codigo = $request->get('codigo');
        $complemento = mb_strtoupper($request->get('complemento'),'utf-8');
        $query = $em->getConnection()->prepare("select * from public.sp_link_plataforma_maestro('$codigo','$complemento')");
        $query->execute();
        $valor= $query->fetchAll();
        // dump($valor);die;
        $descripcion="No se encontró información para Carnet Identidad ".$codigo." - ".$complemento.", favor verifique si escribió correctamente su Carnet Identidad, caso contrario comuníquese con la Unidad Educativa o Dirección Distrital de Educación correspondiente";
        return $this->render('SieAppWebBundle:ControlDatosPlataforma:informacion_plataforma_index.html.twig', array( 'valor' => $valor,'descripcion' => $descripcion, ));
    }
    
}