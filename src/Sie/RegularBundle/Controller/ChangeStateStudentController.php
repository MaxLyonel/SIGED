<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\PaisTipo;
use Sie\AppWebBundle\Form\EstudianteType;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Estudiante controller.
 *
 */
class ChangeStateStudentController extends Controller {

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
//die('krlos');
        $em = $this->getDoctrine()->getManager();

        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':ChangeStateStudent:index.html.twig', array(
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
        $estudiante = new Estudiante();

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('change_state_student_result'))
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

        //get the info student
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));
        //check if the student exists
        if ($objStudent) {
            //get the inscription info about student
            $objCurrentInscription = $em->getRepository('SieAppWebBundle:Estudiante')->findCurrentStudentInscription($objStudent->getId(), $this->session->get('currentyear'));
            //check it the current inscription exists
            if ($objCurrentInscription) {
                //if the student has current inscription with matricula 4, so build the student form
                return $this->render($this->session->get('pathSystem') . ':ChangeStateStudent:result.html.twig', array(
                            'form' => $this->createFormStudent($objCurrentInscription[0])->createView(),
                            'data' => $objCurrentInscription[0]
                ));
            } else {
                $message = 'Estudiante no presenta inscripción para la presente gestión';
                $this->addFlash('notistate', $message);
                return $this->redirectToRoute('change_state_student_index');
            }
        } else {
            $message = 'Estudiante no registrado';
            $this->addFlash('notistate', $message);
            return $this->redirectToRoute('change_state_student_index');
        }
    }

    /**
     * create form Student
     * @param type $data
     * @return the student form
     */
    private function createFormStudent($data) {
        $formStudent = $this->createFormBuilder()
                ->setAction($this->generateUrl('change_state_student_modify'))
                ->add('idStudent', 'hidden', array('data' => $data['idStudent']))
                ->add('iecId', 'hidden', array('data' => $data['institucioneducativaCurso']))
                ->add('save', 'submit', array('label' => 'Retirar Esudiante', 'attr' => array('class' => 'btn btn-default btn-block')));

        return $formStudent->getForm();
    }

    /**
     * todo the registration of traslado
     * @param Request $request
     * 
     */
    public function modifyAction(Request $request) {
        try {
            //create conexion on DB
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            //get the variblees
            $form = $request->get('form');
            //look for the student
            $studentInsc = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
                'institucioneducativaCurso' => $form['iecId'],
                'estudiante' => $form['idStudent']
            ));
            //update the student's estado matricula
            $studentInsc->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(6));
            $em->persist($studentInsc);
            $em->flush();
            //do the commit of DB
            $em->getConnection()->commit();
            $message = 'Operación realizada correctamente';
            $this->addFlash('goodstate', $message);
            return $this->redirectToRoute('change_state_student_index');
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }

}
