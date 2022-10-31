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
 * ChangeInscriptionModularController controller.
 *
 */
class ChangeInscriptionModularController extends Controller {

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
        $this->session->set('removeinscription', false);
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':ChangeInscriptionModular:index.html.twig', array(
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
        //set new gestion to the select year
        $aGestion = array();
        $currentYear = date('Y');
        for ($i = 1; $i <= 1; $i++) {
            $aGestion[$currentYear] = $currentYear;
            $currentYear--;
        }
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('change_inscription_modular_result'))
                ->add('codigoRude', 'text', array('mapped' => false, 'label' => 'Rude', 'required' => true, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control', 'maxlength' => 17, 'pattern' => '[A-Z0-9]{13,17}', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'choice', array('label' => 'Gestión', 'choices' => $aGestion, 'attr' => array('class' => 'form-control')))
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
            $form['gestion'] = $this->session->get('removeGestion');
        }

        //get the info student
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));
        //check if the student exists
        if ($objStudent) {
            //get the inscription info about student getnumberInscription
            //$objNumberInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getnumberInscription($objStudent->getId(), $this->session->get('currentyear'));
            $objInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsEfectivosModular($objStudent->getId(), $form['gestion']);
            //dump($objInscription);die;
            //check it the current inscription exists
            if (sizeof($objInscription) > 0) {
                //$objInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsEfectivos($objStudent->getId(), $this->session->get('currentyear'));
                //if the student has current inscription with matricula 4, so build the student form
                return $this->render($this->session->get('pathSystem') . ':ChangeInscriptionModular:result.html.twig', array(
                            //'form' => $this->createFormStudent($objStudent->getCodigoRude(), $this->session->get('currentyear'))->createView(),
                            'datastudent' => $objStudent,
                            'dataInscription' => $objInscription
                ));
            } else {
                $message = 'Estudiante no presenta más de una inscripción como efectivo para la presente gestión';
                $this->addFlash('notiremovest', $message);
                return $this->redirectToRoute('change_inscription_modular_index');
            }
        } else {
            $message = 'Estudiante no registrado';
            $this->addFlash('notiremovest', $message);
            return $this->redirectToRoute('change_inscription_modular_index');
        }
    }

    /**
     * obtien el formulario para realizar la modificacion
     * @param Request $request
     */
    public function resultparamAction($rude, $gestion) {
        $em = $this->getDoctrine()->getManager();

        //get the info student
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));
        //check if the student exists
        if ($objStudent) {
            //get the inscription info about student getnumberInscription
            //$objNumberInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getnumberInscription($objStudent->getId(), $this->session->get('currentyear'));
            $objInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsEfectivos($objStudent->getId(), $gestion);
            //check it the current inscription exists
            if (sizeof($objInscription) > 0) {
                //$objInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsEfectivos($objStudent->getId(), $this->session->get('currentyear'));
                //if the student has current inscription with matricula 4, so build the student form
                return $this->render($this->session->get('pathSystem') . ':ChangeInscriptionModular:result.html.twig', array(
                            //'form' => $this->createFormStudent($objStudent->getCodigoRude(), $this->session->get('currentyear'))->createView(),
                            'datastudent' => $objStudent,
                            'dataInscription' => $objInscription
                ));
            } else {
                $message = 'Estudiante no presenta más de una inscripción como efectivo para la presente gestión';
                $this->addFlash('notiremovest', $message);
                return $this->redirectToRoute('remove_inscription_student_free_index');
            }
        } else {
            $message = 'Estudiante no registrado';
            $this->addFlash('notiremovest', $message);
            return $this->redirectToRoute('remove_inscription_student_free_index');
        }
    }

    public function removeAction(Request $request) {
        //print_r($request->get('form'));
        $form = $request->get('form');
        //dump($form);die;
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            //look for the record about inscription student to do the update
            $inscriptionStudent = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['insId']);
            $inscriptionStudent->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($form['matricula']));
            $em->persist($inscriptionStudent);
            $em->flush();
            //to do the transaction
            $em->getConnection()->commit();
            //message ok to commit
            $message = "Proceso realizado exitosamente.";
            $this->addFlash('okchange', $message);
            return $this->redirectToRoute('change_inscription_modular_index');
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $message = "Proceso detenido! se ha detectado inconsistencia de datos.";
            $this->session->getFlashBag()->add('notihistory', $ex->getMessage());
            return $this->redirectToRoute('change_inscription_modular_index');
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
