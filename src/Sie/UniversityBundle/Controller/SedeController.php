<?php

namespace Sie\UniversityBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SedeController extends Controller
{
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request)
    {
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = $fechaActual->format('Y');
        
        $id_usuario = $this->session->get('userId');
        //$response = new JsonResponse();
        $estado = true;
        $msg = '';

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
            
        $em = $this->getDoctrine()->getManager();
        //$entityUnivSede = $em->getRepository('SieAppWebBundle:UnivSede')->findBy(array('usuario' => $id_usuario));
        $entityUnivSedeCentral = $em->getRepository('SieAppWebBundle:UnivSede')->findOneBy(array('usuario' => $id_usuario, 'univSedeTipo' => 1));
        //dump($entityUnivSede);die;

        $form = $request->get('form');
        $sedeId = base64_decode($form['sede']);

        $titulo = "Sedes";
        $subtitulo = "";
        
        $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:UnivSede');
        $query = $repository->createQueryBuilder('s')
                ->select('s.id, s.sede, coalesce(ss.id,0) as sucursal')
                ->leftJoin('SieAppWebBundle:UnivSedeSucursal', 'ss', 'WITH', 'ss.univSede = s.id')
                ->where('s.usuario = :usuarioId')
                ->setParameter('usuarioId', $id_usuario)
                ->orderBy('s.id', 'ASC')
                ->getQuery();
        $entityUnivSede =  $query->getResult();

        return $this->render('SieUniversityBundle:Sede:index.html.twig', array(
            'central' => $entityUnivSedeCentral,
            'sedes' => $entityUnivSede,
            'titulo' => $titulo,
            'subtitulo' => $subtitulo
        ));
    }


    public function tuisionSede($sede, $usuarioId)
    {    
        $em = $this->getDoctrine()->getManager();
        $entityUnivSede = $em->getRepository('SieAppWebBundle:UnivSede')->findOneBy(array('usuario' => $usuarioId, 'id' => $sede));
        if (count($entityUnivSede)>0){
            return true;
        } else {
            return false;
        }
    }


}
