<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use \Sie\AppWebBundle\Entity\Usuario;
use \Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;

class BoletinCentralizadorController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * students Inscriptions Index 
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':BoletinCentralizador:index.html.twig', array(
                    'form' => $this->craeteformsearchsie()->createView()
        ));
    }

    private function craeteformsearchsie() {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('boletin_centralizador_find'))
                        ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('class' => 'form-control jnumbersletters', 'pattern' => '[0-9A-Z]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-blue')))
                        ->getForm();
    }

    /**
     * find the bachillers per sie
     * @param Request $request
     * @return type the list of bachilleres
     */
    public function findAction(Request $request) {

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $form = $request->get('form');
            //get ghe info about UE
            $institucionEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);
            //validate UE
            if (!$institucionEducativa) {
                $this->session->getFlashBag()->add('noticesi', 'No existe la Unidad Educativa');
                return $this->redirectToRoute('boletin_centralizador_index');
            }
            //look for the tuicion
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $institucionEducativa->getId());
            $query->bindValue(':roluser', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();
            //check if the user has the tuicion
            if (!$aTuicion[0]['get_ue_tuicion']) {
                $this->session->getFlashBag()->add('noticesi', 'No tiene tuición sobre la Unidad Educativa');
                return $this->redirectToRoute('boletin_centralizador_index');
            }

            $numberStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getInscriptionsPerUe($form['sie'], $this->session->get('currentyear'));
            $message = "Resultado de la Busqueda...";
            $this->addFlash('successsi1', $message);
            return $this->render($this->session->get('pathSystem') . ':BoletinCentralizador:find.html.twig', array(
                        'numberStudents' => $numberStudents,
                        'unidadEducativa' => $institucionEducativa
            ));
        }
    }

}
