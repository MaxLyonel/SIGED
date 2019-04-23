<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;

class RegEventController extends Controller
{
    public function indexAction(Request $request)
    {
        $this->session = $request->getSession();
        $id_Intitucion = $this->session->get('idInstitucion');
        $id_gestion = $this->session->get('currentyear');

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("SELECT even.id,even.nombre_evento,even.fecha_inicio,even.fecha_fin 
                                                FROM cdl_eventos even INNER JOIN cdl_club_lectura lec ON even.cdl_club_lectura_id = lec.id 
											    INNER JOIN institucioneducativa_sucursal ies ON lec.institucioneducativasucursal_id = ies.id
                                                WHERE ies.institucioneducativa_id =$id_Intitucion AND  ies.gestion_tipo_id =$id_gestion");
        $query->execute();
        $listaeventos = $query->fetchAll();//dump($listaeventos);die;





        return $this->render('SieHerramientaBundle:RegEvent:index.html.twig', array('listaeventos'=>$listaeventos,
            'id_Intitucion'=>$id_Intitucion,
            'id_gestion'=>$id_gestion
            ));
    }

    public function findAction()
    {
        return $this->render('SieHerramientaBundle:RegEvent:find.html.twig', array(
                // ...
            ));    }

    public function registerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        return $this->render('SieHerramientaBundle:RegEvent:register.html.twig', array(
                // ...
            ));
    }

}
