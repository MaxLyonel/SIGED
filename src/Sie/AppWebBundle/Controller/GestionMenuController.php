<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Gestión de Menú Controller.
 */
class GestionMenuController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    /**
     * Muestra el listado de Menús
     */
    public function indexAction(Request $request) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '8') {
            return $this->redirect($this->generateUrl('principal_web'));
        }
        
        return $this->render('SieAppWebBundle:GestionMenu:index.html.twig');
    }
    
    /**
     * List menu by Rol.
     */
    public function listAction($id) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '8') {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $em = $this->getDoctrine()->getManager();
        
        $repository = $em->getRepository('SieAppWebBundle:MenuObjeto');
        
        $query = $repository->createQueryBuilder('mo')
                ->innerJoin('SieAppWebBundle:ObjetoTipo', 'ot', 'WITH', 'mo.objetoTipo = ot.id')
                ->innerJoin('SieAppWebBundle:MenuTipo', 'mt', 'WITH', 'mo.menuTipo = mt.id')
                ->innerJoin('SieAppWebBundle:Permiso', 'pe', 'WITH', 'pe.menuObjeto = mo.id')
                ->innerJoin('SieAppWebBundle:RolPermiso', 'rpe', 'WITH', 'rpe.permiso = pe.id')
                ->where('rpe.rolTipo = :rol')
                ->setParameter('rol', $id)
                ->orderBy('ot.objeto, mt.nombre')
                ->getQuery();
        
        $menuObjeto = $query->getResult();
        
        return $this->render('SieAppWebBundle:GestionMenu:list.html.twig', array(
                    'menuObjeto' => $menuObjeto,
        ));
        
    }
    
    /**
     * Update Estado in Menu entity.
     */
    public function estadoAction($id) {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $rol_usuario = $this->session->get('roluser');

        if ($rol_usuario != '8') {
            return $this->redirect($this->generateUrl('principal_web'));
        }

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:MenuObjeto')->find($id);

        
        $entity->setEsactivo(1- $entity->getEsactivo());

        $em->flush();

        return $this->redirect($this->generateUrl('gestionmenu'));
    }
    
}
