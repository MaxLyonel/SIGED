<?php

namespace Sie\HerramientaBundle\Controller;


use GuzzleHttp\Psr7\Response;
use Sie\AppWebBundle\Entity\CdlProductoimagen;
use Sie\AppWebBundle\Entity\CdlProductos;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Acl\Exception\Exception;


class RegResultEventController extends Controller
{
    public $session;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();

    }
    public function indexAction(Request $request)
    {   $id_Intitucion = $this->session->get('idInstitucion');
        $id_gestion = $this->session->get('currentyear');
        $id_evento = $request->get('idEvento');  //dump($id_evento);die; // id de evento
        $club = $request->get('club');// id de evento
        $id_club = $request->get('id_club') ;
        $em = $this->getDoctrine()->getManager();
        $evento = $em->getRepository('SieAppWebBundle:CdlEventos')->findOneById($id_evento);
        $resultado = $em->getRepository('SieAppWebBundle:CdlProductos')->findBy(array('cdlEventos'=>$id_evento));
        //dump($resultado);die;
        return $this->render('SieHerramientaBundle:RegResultEvent:index.html.twig', array('listaresultado' => $resultado,
            'id_Intitucion' => $id_Intitucion,
            'id_gestion' => $id_gestion,
            'club' => $club,
            'id_club' => $id_club,
            'id_evento'=>$id_evento,
            'nombreevento' =>$evento->getNombreEvento()
        ));
    }
    public function registerAction(Request $request)
    {   //dump($request);die;
        $res = 200;
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            if ($request->get('id_resultado') == 0 ){
                $resultado = new CdlProductos();
            }else {
                $resultado = $em->getRepository('SieAppWebBundle:CdlProductos')->findOneById($request->get('id_resultado'));

            }
            $resultado->setCdlEventos($em->getRepository('SieAppWebBundle:CdlEventos')->findOneById($request->get('id_evento')));
            $resultado->setNombreProducto($request->get('nombreresultado'));
            $resultado->setObs('');
            $em->persist($resultado);
            $em->flush();
            $em->getConnection()->commit();
            $request->get('id_resultado') == 0 ? $mensaje = 'Datos registrados exitosamente':$mensaje = 'Datos modificados exitosamente';
        } catch (Exception $exceptione) {
            $em->getConnection()->rollback();
            $res = 500;
            $mensaje = 'No se pudo guardar los datos';
        }
        $resultado = $em->getRepository('SieAppWebBundle:CdlProductos')->findBy(array('cdlEventos'=>$request->get('id_evento')));

        return new JsonResponse(array('estado' => $res, 'msg' => $mensaje,'listaresultado'=>$resultado));
    }
    public function deleteAction(Request $request){// dump($request);die;
        $em=$this->getDoctrine()->getManager();
        $resultado = $em->getRepository('SieAppWebBundle:CdlProductos')->findOneById($request->get('idResultado'));
        $imagenes  = $em->getRepository('SieAppWebBundle:CdlProductoimagen')->findBy(array('cdlProductos'=>$request->get('idResultado')));
        if(!isset($resultado)){
            throw $this->createNotFoundation('No existe el resultado');
        }
        if($imagenes){
            $mensaje = 'El resultado: ' . $resultado->getNombreProducto() . ' no fue eliminado porque tiene imágenes registradas';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);
        }else{
            $em->remove($resultado);
            $em->flush();
            $mensaje = 'El resultado: ' . $resultado->getNombreProducto() . ' fue eliminado con éxito';
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        }
        return $this->redirectToRoute('regresultevent', array('idEvento'=>$request->get('idEvento'),'club'=>$request->get('club'),
            'idInstitucion'=>$request->get('idInstitucion'),'gestion'=>$request->get('gestion')));
    }
    public function registerimagenAction(Request $request ){ //dump($request);die;
        $cantidad = $request->get('cantidad'); //dump($cantidad);die;
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            for ($i = 1; $i <= $cantidad; $i++) {
                $titulo = $request->get('nombreimagen' . $i);
                $documento = $request->files->get('adjdocumento'. $i);
                if ($documento) {
                    $destination_path = 'uploads/club/'. $this->session->get('ie_id').'/lectura/';
                    $imagen = date('YmdHis').$i.'.'.$documento->getClientOriginalExtension();
                    $documento->move($destination_path, $imagen);
                    $productoimg = new CdlProductoimagen();
                    $productoimg->setUrlImagen($imagen);
                    $productoimg->setObs($titulo);
                    $productoimg->setCdlProductos($em->getRepository('SieAppWebBundle:CdlProductos')->findOneById($request->get('id_resultado_imagen')));
                    $em->persist($productoimg);
                    $em->flush();
                }
            }
            $em->getConnection()->commit();
            $mensaje = 'Se guardo las imagenes en su galeria';
            $res = 200;
        }catch (Exception $exceptione) {
            $em->getConnection()->rollback();
            $res = 500;
            $mensaje = 'No se pudo guardar los datos';
        }
        return new JsonResponse(array('estado' => $res,'msg'=>$mensaje));
    }

    public function fotosAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $fotos= $this->listFotos($id);
        return $this->render('SieHerramientaBundle:RegResultEvent:imagenes.html.twig', array(
            'fotos'=>$fotos,'idinstitucion'=>$this->session->get('ie_id'),'id_resultado'=>$id,
        ));
    }
    public function deletefotosAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $id_foto = $request->get('id');
        $id_resultado = $request->get('id_resultado');
        $foto = $em->getRepository('SieAppWebBundle:CdlProductoimagen')->findOneById($id_foto);
        $em->remove($foto);
        $em->flush();
        return $this->render('SieHerramientaBundle:RegResultEvent:imagenes.html.twig', array(
            'fotos'=>$this->listFotos($id_resultado),'idinstitucion'=>$this->session->get('ie_id'),'id_resultado'=>$id_resultado
        ));
    }
    public function  listFotos($id_resultado){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            SELECT id,url_imagen,obs
            FROM cdl_productoimagen
            WHERE cdl_productoimagen.cdl_productos_id = $id_resultado ");
        $query->execute();
        $fotos = $query->fetchAll();
        return $fotos;
    }

}
