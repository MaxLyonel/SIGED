<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Estudiante controller.
 *
 */
class RemoveInscriptionStudentController extends Controller {

    private $session;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * index action build the form to search
     *
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $this->session->set('removeinscription', false);
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':RemoveInscriptionStudent:index.html.twig', array(
                    'form' => $this->createSearchForm()->createView(),
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
                ->setAction($this->generateUrl('remove_inscription_student_result'))
                ->add('codigoRude', 'text', array('mapped' => false, 'label' => 'Rude', 'required' => true, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control', 'maxlength' => 17, 'pattern' => '[A-Z0-9]{13,17}', 'style' => 'text-transform:uppercase')))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /**
     * obtien el formulario para realizar la modificacion
     * @param Request $request
     */
    public function resultAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        if ($this->session->get('removeinscription')) {
            $form['codigoRude'] = $this->session->get('removeCodigoRude');
        }
        
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));
        //verificamos si existe el estudiante y si es menor a 15
        if ($student) {          
            $dataInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getInscriptionHistoryEstudenWhitObservation($form['codigoRude']);
        }
        
        return $this->render($this->session->get('pathSystem') . ':RemoveInscriptionStudent:result.html.twig', array(           
                    'datastudent' => $student,
                    'dataInscription' => $dataInscription
        ));
    }
    
    public function removeAction(Request $request) {
        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();        
        $em->getConnection()->beginTransaction();
        
        try {            
            $inscriptionStudent = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['inscripcionid']);            
            $inscriptionStudent->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($form['matricula']));
            $em->persist($inscriptionStudent);
            $em->flush();
            
            $em->getConnection()->commit();
            $message = "Proceso realizado exitosamente.";
            $this->addFlash('goodstate', $message);
            
            return $this->redirectToRoute('remove_inscription_student_index');
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $message = "Proceso detenido! Se ha detectado inconsistencia de datos. \n".$ex->getMessage();
            $this->addFlash('notiremovest', $message);
            return $this->redirectToRoute('remove_inscription_student_index');
        }
        
        
    }
}
