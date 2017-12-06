<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * EstudianteInscripcion controller.
 *
 */
class InfoTecnicaController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    /**
     * list of request
     *
     */
    public function indexAction(Request $request) {
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':InfoTecnica:index.html.twig', array());
    }

    /**
     * open the request
     * @param Request $request
     * @return obj with the selected request 
     */
    public function openAction(Request $request) {
        //get session data
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':InfoTecnica:open.html.twig', array(
                    'mallaCurricularform' => $this->InfoStudentForm('herramienta_mallacurricular_index', 'Malla Curricular')->createView(),
                    'cursoform' => $this->InfoStudentForm('herramienta_cursosTecnica_index', 'Cursos')->createView(),
                    'estudiantesform' => $this->InfoStudentForm('herramienta_estudiantesTecnica_index', 'Estudiantes')->createView(),
        ));
    }

    /**
     * create form Student Info to send values
     * @return type obj form
     */
    private function InfoStudentForm($goToPath, $nextButton) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl($goToPath))
                        ->add('gestion', 'hidden', array('data' => '2013'))
                        ->add('sie', 'hidden', array('data' => '80730032'))//40730004
                        ->add('next', 'submit', array('label' => "$nextButton", 'attr' => array('class' => 'btn btn-link')))
                        ->getForm()
        ;
    }

}
