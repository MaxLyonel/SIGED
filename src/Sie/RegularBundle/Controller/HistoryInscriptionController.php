<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * HistoryInscription controller.
 *
 */
class HistoryInscriptionController extends Controller {

    private $session;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Lists all Estudiante entities.
     *
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        //check if the user is logged
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //set the student and inscriptions data 
        $student = array();
        $dataInscription = array();
        $sw = false;
        if ($request->get('form')) {
            //get the form to send
            $form = $request->get('form');
            //get the result of search
            $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));
            //verificamos si existe el estudiante y si es menor a 15
            if ($student) {          
                $dataInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getInscriptionHistoryEstudenWhitObservation($form['codigoRude']);
            }            
            $sw = true;
            //check if the result has some value
            if (!$student) {
                $message = 'Estudiante con rude ' . $form['codigoRude'] . ' no se presenta registro de inscripciones';
                $this->addFlash('notihistory', $message);
                $sw = false;
                return $this->redirectToRoute('history_inscription_index');
            }
        }
        return $this->render($this->session->get('pathSystem') . ':HistoryInscription:index.html.twig', array(
                    'form' => $this->createSearchForm()->createView(),
                    'datastudent' => $student,
                    'dataInscription' => $dataInscription,
                    'sw' => $sw
        ));
    }
    
    /**
     * Creates a form to search the users of student selected
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchForm() {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('history_inscription_index'))
                ->add('codigoRude', 'text', array('label' => 'Rude', 'invalid_message' => 'campo obligatorio', 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 20, 'required' => true, 'class' => 'form-control')))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }
    
     /**
     * Recive Rude to history inscripction
     *
     */
    public function questAction($rude) {
        //die('krlos');
        $em = $this->getDoctrine()->getManager();
        //check if the user is logged
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //set the student and inscriptions data 
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));
        //verificamos si existe el estudiante y si es menor a 15
        if ($student) {          
            $dataInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getInscriptionHistoryEstudenWhitObservation($rude);
        }
        $sw = true;
        return $this->render($this->session->get('pathSystem') . ':HistoryInscription:index.html.twig', array(
                    'form' => $this->createSearchForm()->createView(),
                    'datastudent' => $student,
                    'dataInscription' => $dataInscription,
                    'sw' => $sw
        ));
    }
}
