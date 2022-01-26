<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\CdlClubLectura;

use Sie\AppWebBundle\Entity\CdlEventos;

class RegEventController extends Controller
{
    public function indexAction(Request $request)
    { //dump($request);die;
        $this->session = $request->getSession();
        $form = $request->get('form');
        if($form){
            $arrDataUe = json_decode($form['jsonDataUe'],true);
            $id_Intitucion = $arrDataUe['institucioneducativa'];;
            $id_gestion = $arrDataUe['gestionTipo'];
            $id_cdl_club_lectura=$form['cdlId'];
        }else{
            $id_Intitucion    = $request->get('id_Intitucion');
            $id_gestion       = $this->session->get('currentyear');
            $id_cdl_club_lectura     = $request->get('id_club');
        }
        //dump(base64_decode($id_cdl_club_lectura),$id_cdl_club_lectura);die;
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT even.id,even.nombre_evento,even.fecha_inicio,even.fecha_fin 
                                                FROM cdl_eventos even                                                
                                                WHERE  even.cdl_club_lectura_id = $id_cdl_club_lectura");
        $query->execute();
        $listaeventos = $query->fetchAll();
        $clubLectura = $em->getRepository('SieAppWebBundle:CdlClubLectura')->findOneById($id_cdl_club_lectura);
        /*$id_cdl_club_lectura       = base64_decode($request->get('id_club'));*/

        return $this->render('SieHerramientaBundle:RegEvent:index.html.twig', array('listaeventos'=>$listaeventos,
            'id_Intitucion'=>$id_Intitucion,
            'id_gestion'=>$id_gestion,
            'club'=>$clubLectura->getNombreClub(),
            //'id_club'=>base64_decode($id_cdl_club_lectura)
            'id_club'=>$id_cdl_club_lectura
            ));
    }
    public function registerAction(Request $request)
    {
        $res = 200;
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            //$em->getConnection()->prepare("select * from sp_reinicia_secuencia('cdl_eventos');")->execute();
            if ($request->get('id_evento') == 0 ){
                $evento = new CdlEventos();
            }else {
                $evento = $em->getRepository('SieAppWebBundle:CdlEventos')->findOneById($request->get('id_evento'));

            }

            $evento->setCdlClubLectura($em->getRepository('SieAppWebBundle:CdlClubLectura')->findOneById($request->get('id_club')));
            $evento->setNombreEvento(mb_strtoupper($request->get('nombreevento'), 'utf8') );
            $evento->setFechaInicio(new \DateTime($request->get('fecha_inicio')));
            $evento->setFechaFin(new \DateTime($request->get('fecha_fin')));
            $evento->setObs('');
            $em->persist($evento);
            $em->flush();
            $em->getConnection()->commit();
            $request->get('id_evento') == 0 ? $mensaje = 'Datos registrados exitosamente':$mensaje = 'Datos modificados exitosamente';
        } catch (Exception $exceptione) {
            $em->getConnection()->rollback();
            $res = 500;
            $mensaje = 'No se pudo guardar los datos';
        }
        return new JsonResponse(array('estado' => $res, 'msg' => $mensaje));
    }
    public function deleteAction(Request $request){
        $em=$this->getDoctrine()->getManager();
        $id_Evento = $request->get('idEvento');
        $id_institucion = $request->get('idInstitucion');
        $evento = $em->getRepository('SieAppWebBundle:CdlEventos')->find($id_Evento);
        $resultado = $em->getRepository('SieAppWebBundle:CdlProductos')->findBy(array('cdlEventos'=>$id_Evento));
        if(!isset($evento)){
            throw $this->createNotFoundation('No existe el evento');
        }
        if($resultado){
            $mensaje = 'El evento ' . $evento->getNombreEvento() . ' tiene resultados registrados';
            $request->getSession()
                ->getFlashBag()
                ->add('error', $mensaje);
        }else{
            $em->remove($evento);
            $em->flush();
            $mensaje = 'El evento ' . $evento->getNombreEvento() . ' fue eliminado con éxito';
            $request->getSession()
                ->getFlashBag()
                ->add('exito', $mensaje);
        }
       // return $this->redirectToRoute('regevent',array('id_club'=>base64_encode($request->get('id_club'))));

        return $this->redirectToRoute('regevent',array('id_club'=>$request->get('id_club')));

    }
}
