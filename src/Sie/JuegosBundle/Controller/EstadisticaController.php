<?php

namespace Sie\JuegosBundle\Controller;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EstadisticaController extends Controller {

    public $session;
    public $idInstitucion;
    private $nivelId;
    private $save;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        //$this->aCursos = $this->fillCursos();
    }

    public function indexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */

    }

    public function faseLugarIndex(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        return $this->render($this->session->get('pathSystem') . ':Clasificacion:culturalindex.html.twig', array(
        ));

    }

    public function faseLugarAction(Request $request, $fase) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $entityUsuarioRol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('usuario'=>$id_usuario));
        $nivelEntidad = $entityUsuarioRol->getLugarTipo()->getLugarNivel()->getId();
        $codigoEntidad = $entityUsuarioRol->getLugarTipo()->getCodigo();
        //dump($codigoEntidad);die;

        $arch = 'Juegos'.$gestionActual.'f'.($fase-1).'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_est_general_seguimiento_v1_rcm.rptdesign&__format=pdf&codigo='.$codigoEntidad.'&gestion='.$gestionActual.'&fase='.$fase.'&nivel='.$nivelEntidad));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
}
