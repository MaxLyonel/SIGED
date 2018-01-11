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
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class NoteConsultationUesController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * note consultation parents Index
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
        return $this->render($this->session->get('pathSystem') . ':NoteConsultationUes:index.html.twig', array(
                    'form' => $this->craeteformsearch()->createView()
        ));
    }
    /**
     * [craeteformsearch description]
     * @return [type] [description]
     */
    private function craeteformsearch() {

        //set new gestion to the select year
        $arrGestion = array();
        $currentYear = date('Y');
        for ($i = 0; $i <= 10; $i++) {
            $arrGestion[$currentYear] = $currentYear;
            $currentYear--;
        }

        return $this->createFormBuilder()
                        //->setAction($this->generateUrl('remove_inscription_sie_index'))
                        ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{3,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('gestion', 'choice', array('label' => 'Gestión', 'choices' => $arrGestion, 'attr' => array('class' => 'form-control')))
                        ->add('search', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'findInscription()')))
                        ->getForm();
    }

    /**
     * find the courser per sie
     * @param Request $request
     * @return type the list of student and inscripion data
     */
    public function resultAction(Request $request) {

        //get the value to send
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        //find the UE
        $em = $this->getDoctrine()->getManager();
        $objUe = array();
        $objCourses = array();
        $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);
        $exist = true;
        //check if the data exist
        if ($objUe) {
            //look for inscription data
            $objCourses = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getCoursesPerUe($sie, $gestion);
            //check if exists data
            if (!$objCourses) {
                $message = 'Unidad Educativa no presenta Cursos';
                $this->addFlash('warningconsultaue', $message);
                $exist = false;
            }
        } else {
            $message = 'Unidad Educativa no Existe';
            $this->addFlash('warningconsultaue', $message);
            $exist = false;
        }

        return $this->render($this->session->get('pathSystem') . ':NoteConsultationUes:result.html.twig', array(
                    'unidadEducativa' => $objUe,
                    'courses' => $objCourses,
                    'exist' => $exist,
                    'gestionSelected' => $gestion
        ));
    }

}
