<?php

namespace Sie\UniversityBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = $fechaActual->format('Y');
        
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        $entityUsuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('id' => $id_usuario));
        //dump($entityUnivSede);die;
        return $this->render('SieUniversityBundle:Principal:index.html.twig', array(
            'usuario' => $entityUsuario,
            'titulo' => "Inicio"
        ));
        // $info = json_decode(base64_decode($request->get('info')), true);
    }


    public function sedeAction(Request $request)
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
        $entityUnivSede = $em->getRepository('SieAppWebBundle:UnivSede')->findBy(array('usuario' => $id_usuario));
        $entityUnivSedeCentral = $em->getRepository('SieAppWebBundle:UnivSede')->findOneBy(array('usuario' => $id_usuario, 'univSedeTipo' => 1));
        //dump($entityUnivSede);die;

        $form = $request->get('form');
        $sedeId = base64_decode($form['sede']);

        if(!$this->tuisionSede($sedeId,$id_usuario)){
            //return $response->setData(array('estado' => false, 'msg' => 'Su sesión finalizo, ingrese nuevamente'));
            $estado = false;
            $msg = 'No esta como usuario en la sede seleccionada, comuniquese con su administrador';
            return $this->render('SieUniversityBundle:Default:index.html.twig', array(
                'sedes' => $entityUnivSede,
                'central' => $entityUnivSedeCentral,
                'msg'=>$msg,
                'titulo' => "Sucursales"
            ));
        }

        $entityUnivSedeActual = $em->getRepository('SieAppWebBundle:UnivSede')->findOneBy(array('id' => $sedeId));
        $titulo = $entityUnivSedeActual->getUnivUniversidad()->getUniversidad();
        $subtitulo = $entityUnivSedeActual->getSede();
        //dump($sedeId);die;

        $entity = $em->getRepository('SieAppWebBundle:GestionTipo');
        $query = $entity->createQueryBuilder('gt')
                ->orderBy('gt.id', 'DESC')
                ->setMaxResults(5)
                ->getQuery();
        $gestiones = $query->getResult();
        
        return $this->render('SieUniversityBundle:Principal:index.html.twig', array(
            'sede' => $entityUnivSedeActual,
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'gestiones' => $gestiones
        ));

        //$entityUnivSede = $em->getRepository('SieAppWebBundle:UnivSede')->find(5);
        
        // $info = json_decode(base64_decode($request->get('info')), true);
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


    public function operativoAction(Request $request)
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


        $form = $request->get('form');
        $sedeId = base64_decode($form['sede']);
        $gestionId = base64_decode($form['gestion']);

        $em = $this->getDoctrine()->getManager();
        $entityUnivSede = $em->getRepository('SieAppWebBundle:UnivSede')->findBy(array('usuario' => $id_usuario));
        $entityUnivSedeCentral = $em->getRepository('SieAppWebBundle:UnivSede')->findOneBy(array('usuario' => $id_usuario, 'univSedeTipo' => 1));
        //dump($entityUnivSede);die;

        if(!$this->tuisionSede($sedeId,$id_usuario)){
            //return $response->setData(array('estado' => false, 'msg' => 'Su sesión finalizo, ingrese nuevamente'));
            $estado = false;
            $msg = 'No esta como usuario en la sede seleccionada, comuniquese con su administrador';
            return $this->render('SieUniversityBundle:Default:index.html.twig', array(
                'sedes' => $entityUnivSede,
                'central' => $entityUnivSedeCentral,
                'msg'=>$msg,
                'titulo' => "Sucursales"
            ));
        }

        $this->session->set('gestion', $gestionId);

        return $this->render('SieUniversityBundle:Default:index.html.twig', array(
            'sedes' => $entityUnivSede,
            'central' => $entityUnivSedeCentral,
            'titulo' => "Sucursales"
        ));

        $entityUnivSedeActual = $em->getRepository('SieAppWebBundle:UnivSede')->findOneBy(array('id' => $sedeId));
        $titulo = $entityUnivSedeActual->getUnivUniversidad()->getUniversidad();
        $subtitulo = $entityUnivSedeActual->getSede();
        //dump($sedeId);die;
        
        
        return $this->render('SieUniversityBundle:Principal:index.html.twig', array(
            'sede' => $entityUnivSedeActual,
            'titulo' => $titulo,
            'subtitulo' => $subtitulo
        ));        
        // $info = json_decode(base64_decode($request->get('info')), true);
    }
}
