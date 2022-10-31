<?php

namespace Sie\DiplomaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }
    
    public function indexAction(Request $request)
    {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //get the roles info
        $aRoles = $this->getUserRoles($id_usuario);
        $this->session->set('roluser', $aRoles);
        return $this->render('SieDiplomaBundle:Principal:index.html.twig');
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
