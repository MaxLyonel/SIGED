<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use \Sie\AppWebBundle\Entity\Usuario;
use \Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;

class ConsultaLibretaController extends Controller {

    public function __construct() {
        $this->session = new Session();
    }

    /**
     * visualizamos paramentos de busqueda
     * @param Request $request
     * @return object form to look for libreta
     */
    public function indexAction(Request $request) {
        $this->session->set('currentyear', date('Y'));
        $usuario = new Usuario();
        $form = $this->createFormBuilder($usuario)
                ->setAction($this->generateUrl('consultalibreta_buscar'))
                ->add('rudeoci', 'text', array('mapped' => false, 'required' => true, 'invalid_message' => 'Campor 1 obligatorio'))
                ->add('fechaNacimiento', 'text', array('mapped' => false, 'label' => 'Fecha de Nacimiento', 'attr' => array('class' => 'form-control', 'maxlength'=> '10')))
                ->add('save', 'submit', array('label' => 'Aceptar'))
                ->getForm();

        return $this->render('SieAppWebBundle:ConsultaLibreta:index.html.twig', array("form" => $form->createView()));
    }

    /**
     *
     * buscar libreta del estudiante
     * @param Request $request
     * @return array libreta, estudiante, inst. educativa
     */
    public function buscarAction(Request $request) {

        $session = new Session();
        $form = $request->get('form');

        if ((strlen($form['fechaNacimiento']) < 10 ) ) {
            //return al misma opcion de busqueda con el mensaje indicado q no existe el estdiante
            $session->getFlashBag()->add('notice', 'No existe el Estudiante, revise datos de entrada(rude/ci o fecha nacimiento)');
            return $this->redirect($this->generateUrl('consultalibreta'));
        }

        //if ($request->getMethod() == 'POST') {
        list($form['day'], $form['month'], $form['year']) = (explode('-', str_replace('/', '-', $form['fechaNacimiento'])));
        $session->set('rudeoci', $form['rudeoci']);
        $session->set('year', $form['year']);
        $session->set('month', $form['month']);
        $session->set('day', $form['day']);
        $session->set('fnac', $form['fechaNacimiento']);
        //}
        $form['rudeoci'] = ($session->get('rudeoci')) ? $session->get('rudeoci') : $form['rudeoci'];
        $form['year'] = ($session->get('year')) ? $session->get('year') : $form['year'];
        $form['month'] = ($session->get('month')) ? $session->get('month') : $form['month'];
        $form['day'] = ($session->get('day')) ? $session->get('day') : $form['day'];
        $em = $this->getDoctrine()->getManager();
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->getDataStudent($form['rudeoci'], $form['year'] . '-' . $form['month'] . '-' . $form['day']);

        //check if the student exists
        if (!$objStudent) {
            //return al misma opcion de busqueda con el mensaje indicado q no existe el estdiante
            $session->getFlashBag()->add('notice', 'No existe el Estudiante');
            return $this->redirect($this->generateUrl('consultalibreta'));
        }

        if (sizeof($objStudent)>1) {
            //return al misma opcion de busqueda con el mensaje indicado q no existe el estdiante
            $session->getFlashBag()->add('notice', 'Estudiante presenta mas de  un registro en el sistema, favor regularizar con su respectivo ténico');
            return $this->redirect($this->generateUrl('consultalibreta'));
        }

        //$form['codigoRude'] = ($sesion->get('rude')) ? $sesion->get('rude') : $estudiante[0]['codigoRude'];
        //$form['codigoRude'] = $estudiante[0]['codigoRude'];
        $form['gestion'] = isset($form['gestion']) ? $form['gestion'] : $this->session->get('currentyear');

        //get the students inscription
        $objInscriptionStudent = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentInscriptionData($objStudent[0]['id'], $form['gestion']);

        if (!($objInscriptionStudent)) {
            //return al misma opcion de busqueda con el mensaje indicado q no existe el estdiante
            $session->getFlashBag()->add('notice', 'Estudiante no presenta Historial con estado EFECTIVO/PROMOVIDO en la gestión '.$this->session->get('currentyear'));
            return $this->redirect($this->generateUrl('consultalibreta'));
        }
        $objNota = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getNotasStudentNew( $objInscriptionStudent[0]['inscripcionid'],$objStudent[0]['id'], $objInscriptionStudent[0]['nivel'], $objInscriptionStudent[0]['grado'], $objInscriptionStudent[0]['paralelo'], $objInscriptionStudent[0]['turno'], $objInscriptionStudent[0]['gestion'], $objInscriptionStudent[0]['sie']);

        $aNota = array();
        $aBim = array();
        //build the nota
        foreach ($objNota as $nota) {
            ($nota['notaTipo']) ? $aNota[$nota['asignatura']][$nota['notaTipo']] = ($objInscriptionStudent[0]['nivel'] == 11) ? $nota['notaCualitativa'] : $nota['notaCuantitativa'] : '';
            ($nota['notaTipo']) ? $aBim[$nota['notaTipo']] = ($nota['notaTipo'] == 5) ? 'Prom' : $nota['notaTipo'] . '.B' : '';
        }

        $aBim = ($aBim) ? $aBim : array();

        $formsearch = $this->createFormBuilder()
                ->setAction($this->generateUrl('consultalibreta_buscar'))
                ->add('rudeoci', 'hidden', array('mapped' => false, 'required' => true, 'invalid_message' => 'Campor 1 obligatorio', 'data' => $objStudent[0]['codigoRude']))
                ->add('year', 'hidden', array('mapped' => false, 'required' => true, 'invalid_message' => 'Campor 1 obligatorio', 'data' => $session->get('year')))
                ->add('month', 'hidden', array('mapped' => false, 'required' => true, 'invalid_message' => 'Campor 1 obligatorio', 'data' => $session->get('month')))
                ->add('day', 'hidden', array('mapped' => false, 'required' => true, 'invalid_message' => 'Campor 1 obligatorio', 'data' => $session->get('day')))
                ->add('fechaNacimiento', 'hidden', array('data' => $form['fechaNacimiento']))
                ->add('gestion', 'choice', array('mapped' => false, 'choices' => array('2017' => '2017', '2016' => '2016', '2015' => '2015', '2014' => '2014'), 'required' => true, 'invalid_message' => 'Campor 2 obligatorio'))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();

        return $this->render('SieAppWebBundle:ConsultaLibreta:resultlibreta.html.twig', array(
                    'gestion' => $form['gestion'],
                    'bimestres' => $aBim,
                    //'datastudent' => $datastudent,
                    'notastudent' => $aNota,
                    'notacualitativostudent' => $aNota,
                    "form" => $formsearch->createView(),
        ));
    }

}
