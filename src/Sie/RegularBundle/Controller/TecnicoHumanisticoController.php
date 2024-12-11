<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Form\EstudianteType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\EstudianteInscripcionHumnisticoTecnico;

/**
 * Estudiante controller.
 *
 */
class TecnicoHumanisticoController extends Controller {

    public $session;

    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Lists all Estudiante entities.
     *
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        //$objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUeTecnicoHumanistico();
        //$objUe = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findAll();
        $objUe = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->getSelectAll();

        return $this->render($this->session->get('pathSystem') . ':TecnicoHumanistico:index.html.twig', array(
                    'uEducativas' => $objUe,
        ));
    }

    private function createFormStudents($sie, $gestion) {
        
    }

    public function coursesAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        //get the id of UE
        $form = $request->get('form');
        //get info UE
        $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUeTecnicoHumanistico($form['id']);
        //get the courses
        $objCourses = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getCoursesTecnicoHumanistico($form['id'], $this->session->get('currentyear'));
        return $this->render($this->session->get('pathSystem') . ':TecnicoHumanistico:course.html.twig', array(
                    'infoUe' => $objUe[0],
                    'courses' => $objCourses,
                    'formddjj' => $this->createPrintDdjjForm($objUe[0])->createView()
        ));
    }

    private function createPrintDdjjForm($objUe) {

        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('tecnico_humanistico_printddjj'))
                        ->add('idUe', 'hidden', array('data' => $objUe['idUe']))
                        ->add('printddj', 'button', array('label' => 'Imprimir Declaración', 'attr' => array('class' => 'btn btn-default', 'onclick' => 'printddjj()')))
                        ->getForm()
        ;
    }

    public function printddjjAction(Request $request) {

        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();
        $objUeTecnicoHumanistico = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array(
            'institucioneducativaId' => $form['idUe']
        ));
        $objUeTecnicoHumanistico->setEsimpreso(1);
        $em->persist($objUeTecnicoHumanistico);
        $em->flush();
        //after to install go the index action 
        return $this->forward("SieRegularBundle:TecnicoHumanistico:index");
    }

    /**
     * get the students tecnico humanistico
     * @param Request $request
     * @param type $sie
     * @param type $gestion
     * @param type $nivel
     * @param type $grado
     * @param type $paralelo
     * @return type
     */
    public function studentsAction(Request $request, $sie, $gestion, $nivel, $grado, $paralelo) {
        $em = $this->getDoctrine()->getManager();
        //get Students
        $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getStudentTecnicoHumanistico($sie, $gestion, $nivel, $grado, $paralelo);
        return $this->render($this->session->get('pathSystem') . ':TecnicoHumanistico:students.html.twig', array(
                    'students' => $objStudents
        ));
    }

    public function registroAction(Request $request, $idIns, $rude, $iddiv) {

        return $this->render($this->session->get('pathSystem') . ':TecnicoHumanistico:registro.html.twig', array(
                    'form' => $this->createRegistroForm($idIns, $iddiv, $rude)->createView(),
        ));
    }

    //create form to send the values todo the inscription
    private function createRegistroForm($idIns, $iddiv, $rude) {
        $form = $this->createFormBuilder()
                //->setAction($this->generateUrl('tecnico_humanistico_save'))
                ->add('especialidad', 'entity', array('label' => 'Especialidad', 'attr' => array('class' => 'form-control'), 'mapped' => false, 'class' => 'SieAppWebBundle:EspecialidadTipoHumnisticoTecnico'))
                ->add('horas', 'text', array('label' => 'Horas Cursadas', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{2,8}', 'maxlength' => '10', 'onkeypress' => 'onlyNumber(event)')))
                ->add('idIns', 'hidden', array('data' => $idIns))
                ->add('codigoRude', 'hidden', array('data' => $rude))
                ->add('iddiv', 'hidden', array('data' => $iddiv))
                ->add('registrar', 'button', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-default', 'onclick' => 'goSave(' . $idIns . ',' . $iddiv . ')')))
                ->getForm();
        return $form;
    }

    public function saveAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        //get the values send
        $idIns = $request->get('idIns');
        $iddiv = $request->get('iddiv');
        $idEspecialidad = $request->get('idEspecilidad');
        $horas = $request->get('horas');

//        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_humnistico_tecnico');");
//        $query->execute();
        $objInscriptionTecnicoHumanistico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array(
            'estudianteInscripcionId' => $idIns
        ));
        //if the record exists
        if ($objInscriptionTecnicoHumanistico) {
            //todo the update
            $objInscriptionTecnicoHumanistico->setEspecialidadTipo($idEspecialidad);
            $objInscriptionTecnicoHumanistico->sethoras(($horas) ? $horas : 0);
        } else {
            //todo the insert
            $objInscriptionTecnicoHumanistico = new EstudianteInscripcionHumnisticoTecnico();

            $objInscriptionTecnicoHumanistico->setEstudianteInscripcionId($idIns);
            $objInscriptionTecnicoHumanistico->setEspecialidadTipo($idEspecialidad);
            $objInscriptionTecnicoHumanistico->sethoras(($horas) ? $horas : 0);
            $objInscriptionTecnicoHumanistico->setObservacion('NUEVO');
            $objInscriptionTecnicoHumanistico->setFechaRegistro(new \DateTime('now'));
        }
        $em->persist($objInscriptionTecnicoHumanistico);
        $em->flush();

        return $this->render($this->session->get('pathSystem') . ':TecnicoHumanistico:save.html.twig', array(
                    'idIns' => $idIns,
                    'iddiv' => $iddiv,
                    'rude' => 'krlos'
        ));
    }

    public function listAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUeTecnicoHumanistico($form['id']);
        $objStudents = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->getStudentsTecHum($form['id'], $this->session->get('currentyear'));
        //check if the students are registered
        if (!$objStudents) {
            $message = "La unidad educativa " . $form['id'] . " no presenta registro de Estudiantes con Especialidad";
            $this->addFlash('warningtechum', $message);
            return $this->redirectToRoute('tecnico_humanistico_index');
        }
        //the students are registered
        $message = 'Listado de estudiantes de la Unidad Eductiva';
        $this->addFlash('goodtechum', $message);
        $aSpeciality = array();
        foreach ($objStudents as $students) {
            $aSpeciality[$students['especialidad']][] = $students;
        }
//        echo "<pre>";
//        print_r($aSpeciality);
//        echo "</pre>";
//        echo "<hr>";
//        die;
        return $this->render($this->session->get('pathSystem') . ':TecnicoHumanistico:list.html.twig', array(
                    'students' => $objStudents,
                    'infoUe' => $objUe[0],
                    'speciality' => $aSpeciality
        ));
    }

}
