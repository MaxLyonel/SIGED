<?php

namespace Sie\HerramientaBundle\Controller;


use Sie\AppWebBundle\Entity\CdlProductos;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;


class RegResultEventController extends Controller
{
    public function indexAction(Request $request)
    {
        $this->session = $request->getSession();
        $id_Intitucion = $this->session->get('idInstitucion');
        $id_gestion = $this->session->get('currentyear');
        $id_evento = $request->get('idEvento');  //dump($id_evento);die; // id de evento
        $club = $request->get('club');// id de evento
        $em = $this->getDoctrine()->getManager();
        $evento = $em->getRepository('SieAppWebBundle:CdlEventos')->findOneById($id_evento);
        $resultado = $em->getRepository('SieAppWebBundle:CdlProductos')->findBy(array('cdlEventos'=>$id_evento));
        //dump($resultado);die;
        return $this->render('SieHerramientaBundle:RegResultEvent:index.html.twig', array('listaresultado' => $resultado,
            'id_Intitucion' => $id_Intitucion,
            'id_gestion' => $id_gestion,
            'club' => $club,
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
        return new JsonResponse(array('estado' => $res, 'msg' => $mensaje));
    }
    public function deleteAction(Request $request){ //dump($request);die;
        $em=$this->getDoctrine()->getManager();
        $resultado = $em->getRepository('SieAppWebBundle:CdlProductos')->findOneById($request->get('idResultado')); //dump($resultado);die;

        if(!isset($resultado)){
            throw $this->createNotFoundation('No existe el resultado');
        }
        $em->remove($resultado);
        $em->flush();
        $mensaje = 'El resultado ' . $resultado->getNombreProducto() . ' fue eliminado con éxito';
        $request->getSession()
            ->getFlashBag()
            ->add('exito', $mensaje);
        return $this->redirectToRoute('regresultevent', array('idEvento'=>$request->get('idEvento'),'club'=>$request->get('club'),
            'idInstitucion'=>$request->get('idInstitucion'),'gestion'=>$request->get('gestion')));
    }
    public function fotosAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $query = $em->getConnection()->prepare("
            SELECT id,url_imagen,obs
            FROM cdl_productoimagen
            WHERE cdl_productoimagen.cdl_productos_id = $id ");
        $query->execute();
        $fotos = $query->fetchAll();
        return $this->render('SieHerramientaBundle:ReporteLaboFisQuim:fotos.html.twig', array(
            'fotos'=>$fotos
        ));
    }

    public function findAction()
    {
        return $this->render('SieHerramientaBundle:RegResultEvent:find.html.twig', array(
                // ...
            ));    }

}
