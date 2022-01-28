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
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * HistoryInscription controller.
 *
 */
class HistoryInscriptionController extends Controller {

    private $session;
    public $lugarNac;
    public $dpto;
    public $aCursos;
    public $lastUE;
    public $oparalelos;

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
        //die('krlos');
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
            $resultArray = $this->getResult($request->get('form'));
            $student = $resultArray['student'];
            $dataInscription = $resultArray['dataInscription'];
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
        $estudiante = new Estudiante();

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('history_inscription_index'))
                ->add('codigoRude', 'text', array('label' => 'Rude', 'invalid_message' => 'campo obligatorio', 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 20, 'required' => true, 'class' => 'form-control')))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                //->add('public', 'checkbox', array('mapped'=>false,'label' => 'Show this entry publicly?', 'required' => false))
                ->getForm();
        return $form;
    }

    /**
     * get the history of student
     * @param Request $request
     * @return return array with student and inscription data on array
     */
    private function getResult($form) {

        $em = $this->getDoctrine()->getManager();
        //$form = $request->get('form');

        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));

        //verificamos si existe el estudiante y si es menor a 15
        if ($student) {
            //$dataInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $student->getId()));
            $dataInscription = $this->getDataInscriptionsStudent($student->getCodigoRude());
            if ($dataInscription) {
                //everything is ok build the info
                return array('student' => $student, 'dataInscription' => $dataInscription);
            } else {
                return array('student' => '', 'dataInscription' => '');
            }
        } else {
            return array('student' => '', 'dataInscription' => '');
        }
    }

    /**
     * obtien el formulario para realizar la modificacion
     * @param Request $request
     */
    public function resultAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));

        //verificamos si existe el estudiante y si es menor a 15
        if ($student) {
            //$dataInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $student->getId()));
            $dataInscription = $this->getDataInscriptionsStudent($student->getCodigoRude());
            if ($dataInscription) {
                //everything is ok build the info
                return $this->render($this->session->get('pathSystem') . ':HistoryInscription:result.html.twig', array(
                            'datastudent' => $student,
                            'dataInscription' => $dataInscription,
                ));
            } else {
                $this->session->getFlashBag()->add('notihistory', 'Estudiante con rude ' . $form['codigoRude'] . ' no se presenta registro de inscripciones');
                return $this->redirectToRoute('history_inscription_index');
            }
        } else {
            $this->session->getFlashBag()->add('notihistory', 'Estudiante con rude ' . $form['codigoRude'] . ' no se encuentra registrado');
            return $this->redirectToRoute('inscription_talento_index');
        }
    }

    /**
     * get the stutdents inscription - the record
     * @param type $id
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    private function getDataInscriptionsStudent($id) {
        //$session = new Session();
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('e.id idStudent, n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('e.codigoRude = :id')
                ->setParameter('id', $id)
                ->orderBy('iec.gestionTipo', 'DESC')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

}
