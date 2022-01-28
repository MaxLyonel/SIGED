<?php

namespace Sie\PermanenteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    private $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction($name)
    {
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render('SiePermanenteBundle:Default:index.html.twig', array('name' => $name));
    }

    public function ceasrudeuserAction(){
        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getEntityManager();
        $db = $em->getConnection();
//        $query = " select * from usuario_generacion_rude a where a.usuario_id = ".$this->session->get('userId');
        $query = "select e.codigo_rude, e.paterno, e.materno, e.nombre, r.fecha_registro from estudiante e inner join (
                    SELECT replace(split_part(split_part(a.datos_creacion::text,';',4),':',3), '\"', '') as campof, a.fecha_registro
                    FROM usuario_generacion_rude a 
                    WHERE a.usuario_id = ".$this->session->get('userId')."
                    ) r on e.codigo_rude = r.campof
                    order by r.fecha_registro, e.paterno, e.materno, e.nombre";
        $stmt = $db->prepare($query);
        $params = array();
        $stmt->execute($params);
        $po = $stmt->fetchAll();
//        dump($po);
//        die;
        return $this->render('SiePermanenteBundle:Default:rudesuser.html.twig', array(
            'po' => $po,
        ));
    }
}
