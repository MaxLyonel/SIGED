<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\acreditacionEspecialidad;

/**
 * Malla Curricular controller.
 *
 */
class InscriptionTecController extends Controller {

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
     * remove inscription Index 
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
        return $this->render($this->session->get('pathSystem') . ':InscriptionTec:index.html.twig', array(
                    'form' => $this->craeteformsearch()->createView()
        ));
    }

    private function craeteformsearch() {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('herramienta_inscription_find_inscription'))
                        ->add('rude', 'text', array('label' => 'SIE', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '20', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('gestion', 'choice', array('label' => 'Gestión', 'choices' => array('2016' => '2016', '2015' => '2015'), 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('search', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary')))
                        ->getForm();
    }

    public function findInscriptionAction(Request $request) {
        //get the data send
        $form = $request->get('form');
        //do the conexion
        $em = $this->getDoctrine()->getManager();
        //validate if the student has been registered on nivel=13 and (grado = 6 or grado = 5) 
        $objInscriptionTec = $em->getRepository('SieAppWebBundle:Estudiante')->getInscriptionStudentTecnica($form['rude'], $form['gestion']);
        if (!$objInscriptionTec) {
            $message = 'Estudiante cuenta con  inscripción en Unidad Educativa Técnica';
            $this->addFlash('warinscriptiontec', $message);
            return $this->redirectToRoute('herramienta_inscription_tecnica_index');
        }

        return $this->render($this->session->get('pathSystem') . ':InscriptionTec:findInscription.html.twig', array(
                    'form' => $this->createInscriptionForm()->createView()
        ));

        dump($objInscriptionTec);
        die;
    }

    /**
     * create form to do the inscription 
     * @return type form inscription
     */
    private function createInscriptionForm() {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('herramienta_inscription_save_inscription'))
                        ->add('tecnicaProd', 'text')
                        ->add('especialidad', 'text')
                        ->add('nivel', 'text')
                        ->add('paralelo', 'text')
                        ->add('turno', 'text')
                        ->add('modalidadEnsenanza')
                        ->getForm();
    }

}
