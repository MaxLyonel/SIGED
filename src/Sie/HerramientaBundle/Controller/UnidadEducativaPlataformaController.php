<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEliminados;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteHistorialModificacion; 
use Sie\AppWebBundle\Entity\EstudianteInscripcion; 
use Sie\AppWebBundle\Entity\Estudiante; 
use Sie\AppWebBundle\Entity\EstudianteDocumento; 
use Symfony\Component\Validator\Constraints\DateTime;
use Sie\AppWebBundle\Entity\UnificacionRude;
use Sie\AppWebBundle\Entity\EstudianteBack;
use Sie\AppWebBundle\Entity\ApoderadoInscripcion;

class UnidadEducativaPlataformaController extends Controller{

    private $session;
    public $currentyear;
    public $userlogged;
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $this->currentyear = $this->session->get('currentyear');
        $this->userlogged = $this->session->get('userId');
    }   	
    
    public function indexAction(Request $request){

        //validation if the user is logged
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }        
        // db conexion
        $em = $this->getDoctrine()->getManager();    	

        // get info about UE

        
        $objInstitucionEducativa =  $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id'));
        // $arrInstitucionEducativa = array(
        // 	'sie'=>$objInstitucionEducativa->getId(),
        // 	'institucioneducativa'=>$objInstitucionEducativa->getInstitucioneducativa(),
        // );

        $dataUe = array(
        	'sie'=>$objInstitucionEducativa->getId(),
        	'gestion'=>$this->currentyear,
        	'rol'=>array(1,12),

        );
        $dataDir =  $this->getDirector($dataUe);

        // dump($objInstitucionEducativa);die;

        
        return $this->render('SieHerramientaBundle:UnidadEducativaPlataforma:index.html.twig', array(
                'DataInstitucioneducativa' => $objInstitucionEducativa,
                'dataDir' => $dataDir,
            ));    
    }

    private function getDirector($data){

        // db conexion
        $em = $this->getDoctrine()->getManager();    	


            $directors = $em->createQueryBuilder()
                   ->select('mi.id, p.paterno, p.materno, p.nombre, p.carnet, rt.id as rolID')
                   ->from('SieAppWebBundle:MaestroInscripcion','mi')
                   ->innerJoin('SieAppWebBundle:Persona','p','with','mi.persona = p.id')
                   ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','mi.institucioneducativa = ie.id')
                   ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','mi.gestionTipo = gt.id')
                   ->innerJoin('SieAppWebBundle:CargoTipo','ct','with','mi.cargoTipo = ct.id')
                   ->innerJoin('SieAppWebBundle:RolTipo','rt','with','ct.rolTipo = rt.id')
                   ->where('ie.id = :idInstitucion')
                   ->andWhere('gt.id = :gestion')
                   ->andWhere('mi.cargoTipo IN (:cargos)')
                   ->setParameter('idInstitucion',$data['sie'])
                   ->setParameter('gestion',$data['gestion'])
                   ->setParameter('cargos',array(1,12))
                   ->orderBy('p.paterno','asc')
                   ->addOrderBy('p.materno','asc')
                   ->addOrderBy('p.nombre','asc')
                   ->getQuery()
                   ->getResult();
           return ($directors);
           
    }

}
