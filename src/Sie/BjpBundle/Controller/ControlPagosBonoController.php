<?php

namespace Sie\BjpBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Gestión de Menú Controller.
 */
class ControlPagosBonoController extends Controller {

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
        $em = $this->getDoctrine()->getManager();
      // Verificacmos si existe la session de usuario

        return $this->render('SieBjpBundle:ControlPagosBono:index.html.twig');

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

       

        $em = $this->getDoctrine()->getManager();
        
        $repository = $em->getRepository('SieAppWebBundle:BonojuancitoAsignacion')->getAsignacionInstituciones();
        
        //dump($repository);die;
        
        return $this->render('SieBjpBundle:ControlPagosBono:list.html.twig', array(
                'miObjeto' => $repository,
        
/*        return $this->render('SieBjpBundle:ControlPagosBono:list.html.twig', array(
                    'menuObjeto' => $menuObjeto,

        $repository = $em->getRepository('SieAppWebBundle:MenuObjeto');
        
        $query = $repository->createQueryBuilder('mo')
                ->innerJoin('SieAppWebBundle:ObjetoTipo', 'ot', 'WITH', 'mo.objetoTipo = ot.id')
                ->innerJoin('SieAppWebBundle:MenuTipo', 'mt', 'WITH', 'mo.menuTipo = mt.id')
                //->innerJoin('SieAppWebBundle:MenuTipo', 'mt', 'WITH', 'mo.menuTipo = mt.id')
                //->where('ie.id = :idInstitucion')
                //->setParameter('idInstitucion', $institucion)
                ->orderBy('ot.objeto, mt.nombre')
                ->getQuery();
        
        $menuObjeto = $query->getResult();

        //dump($menuObjeto);die;
        
        return $this->render('SieBjpBundle:ControlPagosBono:list.html.twig', array(
                    'menuObjeto' => $menuObjeto,*/
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

    public function consolidarAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare('select proc_consolidar_4b_2016();');
        $query->execute();
        $consolidacion = $query->fetchAll();

        //dump($consolidacion);die;

        return $this->render('SieBjpBundle:ControlPagosBono:index.html.twig');
    }
    
}
