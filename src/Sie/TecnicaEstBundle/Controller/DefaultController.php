<?php

namespace Sie\TecnicaEstBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;

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

        $entityEstTecSede = $em->getRepository('SieAppWebBundle:EstTecSede')->findBy(array('usuario' => $id_usuario));
        
        if (count($entityEstTecSede)<=0){
            return $this->render('SieAppWebBundle:Login:login4.html.twig',array(
                'last_username'=>$this->get('request')->getSession()->get(SecurityContext::LAST_USERNAME),
                'error'=>array('message'=>'¡El usuario no se encuenta registrado para acceder al presente sistema!')
            ));            
        }

        // dump($entityEstTecSede);//die;
        $sedes = array();
        $c = 0;
        foreach ($entityEstTecSede as $registro) {
            $c = $c + 1;
            $sedes[$c]['id'] = bin2hex(serialize($registro->getId()));  
            $sedes[$c]['nombre'] = $registro->getSede();     
            $sedes[$c]['direccion'] = $registro->getEstTecJuridicciongeografica()->getDireccion();        
        }

        $entityEstTecSedeCentral = $em->getRepository('SieAppWebBundle:EstTecSede')->findOneBy(array('usuario' => $id_usuario, 'estTecSedeTipo' => 1));
        // dump($entityEstTecSedeCentral );die;
        return $this->render('SieTecnicaEstBundle:Default:index.html.twig', array(
            'usuario' => $entityUsuario,
            'titulo' => "Sedes",
            'sedes' => $sedes,
            'central' => $entityEstTecSedeCentral,
        ));
        // $info = json_decode(base64_decode($request->get('info')), true);
    }


    public function menuAction(Request $request)
    {
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = $fechaActual->format('Y');
       
        $id_usuario = $this->session->get('userId');
        //$response = new JsonResponse();
        $estado = true;
        $msg = '';
        $editar = false;

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
            
        $em = $this->getDoctrine()->getManager();

        $form = $request->get('form');
        if(isset($form['data'])){
            $form = unserialize(hex2bin($form['data']));
            if(isset($form['sedeId'])){
                //$sedeId = base64_decode($form['sedeId']);
                $sedeId = $form['sedeId'];
            } else {
                $sedeId = 0;
            }
        } else {
            if(isset($form['sede'])){
                //$sedeId = base64_decode($form['sedeId']);
                $sedeId = unserialize(hex2bin($form['sede']));
            } else {
                $sedeId = 0;
            }
        }
        
        //dump($form);die;
        if(isset($form['gestionId'])){
            //$gestionId = base64_decode($form['gestionId']);
            $gestionId = $form['gestionId'];
        } else {
            $gestionId = $gestionActual;
        }        


        $this->session->set('sedeId', $sedeId);

        $entityEstTecSedeActual = $em->getRepository('SieAppWebBundle:EstTecSede')->findOneBy(array('id' => $sedeId));
        $titulo = $entityEstTecSedeActual->getEstTecInstituto()->getInstituto();
        $subtitulo = $entityEstTecSedeActual->getSede();

        $this->session->set('sede', $titulo);
        $this->session->set('subsede', $subtitulo);
        
  
        return $this->redirectToRoute('maininfotecest_index');
    }


    public function tuisionSede($sede, $usuarioId)
    {    
        $em = $this->getDoctrine()->getManager();
        $entityEstTecSede = $em->getRepository('SieAppWebBundle:EstTecSede')->findOneBy(array('usuario' => $usuarioId, 'id' => $sede));
        if (count($entityEstTecSede)>0){
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
        $entityEstTecSede = $em->getRepository('SieAppWebBundle:EstTecSede')->findBy(array('usuario' => $id_usuario));
        $entityEstTecSedeCentral = $em->getRepository('SieAppWebBundle:EstTecSede')->findOneBy(array('usuario' => $id_usuario, 'EstTecSedeTipo' => 1));
        //dump($entityEstTecSede);die;

        if(!$this->tuisionSede($sedeId,$id_usuario)){
            //return $response->setData(array('estado' => false, 'msg' => 'Su sesión finalizo, ingrese nuevamente'));
            $estado = false;
            $msg = 'No esta como usuario en la sede seleccionada, comuniquese con su administrador';
            return $this->render('SieTecnicaEstBundle:Default:index.html.twig', array(
                'sedes' => $entityEstTecSede,
                'central' => $entityEstTecSedeCentral,
                'msg'=>$msg,
                'titulo' => "Sucursales"
            ));
        }

        $this->session->set('gestion', $gestionId);

        return $this->render('SieTecnicaEstBundle:Default:index.html.twig', array(
            'sedes' => $entityEstTecSede,
            'central' => $entityEstTecSedeCentral,
            'titulo' => "Sucursales"
        ));

        $entityEstTecSedeActual = $em->getRepository('SieAppWebBundle:EstTecSede')->findOneBy(array('id' => $sedeId));
        $titulo = $entityEstTecSedeActual->getEstTecInstituto()->getInstituto();
        $subtitulo = $entityEstTecSedeActual->getSede();
        //dump($sedeId);die;
        
        
        return $this->render('SieTecnicaEstBundle:Principal:index.html.twig', array(
            'sede' => $entityEstTecSedeActual,
            'titulo' => $titulo,
            'subtitulo' => $subtitulo
        ));        
        // $info = json_decode(base64_decode($request->get('info')), true);
    }
}
