<?php

namespace Sie\JuegosBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
	public $session;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        //$this->aCursos = $this->fillCursos();
    }

    public function indexAction(Request $request)
    {
    	/*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

    	$sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //get the roles info
        $aRoles = $this->getUserRoles($id_usuario);
        $this->session->set('roluser', $aRoles);
        

        $em = $this->getDoctrine()->getManager();

        return $this->render($this->session->get('pathSystem') . ':Default:index.html.twig', array(
                    
        ));
    }
    /**
     * get the roles of user- obtenemos datos de roles del user
     * parameters: codigo user
     * @author krlos
     */
    function getUserRoles($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Usuario');
        $query = $entity->createQueryBuilder('u')
                ->select('rt.id, rt.rol')
                ->leftJoin('SieAppWebBundle:UsuarioRol', 'ur', 'WITH', 'u.id=ur.usuario')
                ->leftJoin('SieAppWebBundle:RolTipo', 'rt', 'WITH', 'ur.rolTipo=rt.id')
                ->where('u.id = :id')
                ->setParameter('id', $id)
                ->getQuery();
        //print_r($query);die;
        try {
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $exc) {
            //echo $exc->getTraceAsString();
            return array();
        }
    }
}
