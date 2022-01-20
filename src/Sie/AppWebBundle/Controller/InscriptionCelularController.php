<?php

namespace Sie\AppWebBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;

use Sie\AppWebBundle\Entity\Estudiante; 
use Sie\AppWebBundle\Entity\EstudianteCelularPlataforma; 

class InscriptionCelularController extends Controller
{
    public $session;

    public function __construct() {
        $this->session = new Session();
    }
    
    public function indexAction() {
        return $this->render('SieAppWebBundle:ControlDatosCelular:index.html.twig');
    }
    public function validar_nroAction(Request $request){ 
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $nroCelular = $request->get('n');
        $token = $request->get('token');
        $nro=substr($nroCelular, 0, 2);   
        switch ($nro) {
            case '70':
            case '71':
            case '72':
            case '73':
            case '74':
            case '67':
            case '68':
            case '79':
            case '60':
            case '65':
            case '75':
            case '76':
            case '77':
            case '78':
            case '69':
                if (strlen($nroCelular)=='8') {
                    return $response->setData(array( '0'=>1));   
                }else{
                    return $response->setData(array( '0'=>0));   
                } 
            break;
            default:
                return $response->setData(array( '0'=>0  )); 
            break;
        }
    }
    public function verificarInfoAction(Request $request){
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $codigoRude = $request->get('codigo');
        $fecha = $request->get('fecha');
        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
        ->select('e.paterno,e.materno,e.nombre, e.id')
        ->where('e.codigoRude = :idc')
        ->andwhere('e.fechaNacimiento = :fecha')
        ->setParameter('idc', $codigoRude)
        ->setParameter('fecha', $fecha)
        ->getQuery();
        $objInfoInscription = $query->getResult();
        if(sizeof($objInfoInscription)>=1){
            return $response->setData(array(
                'nombre'=>$objInfoInscription[0]['nombre'],
                'token'=>$objInfoInscription[0]['id'], 
            )); 
        }else{
            return $response->setData(array( 'nombre'=>0  )); 
        }
      return $response;  
    }
    public function guadarDatosAction(Request $request){
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $nroCelular = $request->get('n');
        $token = $request->get('token');
        $entity = $em->getRepository('SieAppWebBundle:EstudianteCelularPlataforma');
        $query = $entity->createQueryBuilder('e')
        ->select('e.id')
        ->where('e.estudiante = :id_e')
        ->andwhere('e.celular = :celular')
        ->setParameter('id_e', $token)
        ->setParameter('celular', $nroCelular)
        ->getQuery();
        $objInfoInscription = $query->getResult();
        if(sizeof($objInfoInscription)>=1){
            return $response->setData(array( '0'=>1)); 
        }else{
            $estudianteCel = new EstudianteCelularPlataforma();
            $estudianteCel->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($token));
            $estudianteCel->setCelular($nroCelular);
            $estudianteCel->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find(2022));
            $estudianteCel->setVigente('1');
            $estudianteCel->setFechaRegistro(new \DateTime('now'));
            $estudianteCel->setFechaModificado(new \DateTime('now'));
            $em->persist($estudianteCel);      
            $em->flush();  
            return $response->setData(array( '0'=>0  )); 
        }
    }
}