<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * EstudianteInscripcion controller.
 *
 */
class AdministrativosController extends Controller {

    public $session;
 
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request) {
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        
        $administrativos = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->getListaAdministrativos($this->session->get('ie_id'),$this->session->get('ie_gestion'),$this->session->get('ie_suc_id'),$this->session->get('ie_per_cod'));
        
        return $this->render('SieHerramientaAlternativaBundle:Administrativos:index.html.twig', array(
                    'personal' => $administrativos
        ));
    }
    
    public function editAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($request->get('pId'));
        $maestroInscripcion = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneById($request->get('miId'));
        $idiomas = $em->getRepository('SieAppWebBundle:MaestroInscripcionIdioma')->findBy(array('maestroInscripcion' => $request->get('idMaestroInscripcion')));
        $em->getConnection()->commit();
        
        return $this->render('SieHerramientaAlternativaBundle:Administrativos:edit.html.twig', array(
                    'form' => $this->editForm($request->getSession()->get('ie_id'), $request->getSession()->get('ie_gestion'), $persona, $maestroInscripcion, $idiomas)->createView(),                  
                    'gestion' => $request->getSession()->get('idGestion'),
                    'persona' => $persona
        ));
    }

    /*
     * formulario de edicion
     */

    private function editForm($idInstitucion, $gestion, $persona, $maestroInscripcion, $idiomas) {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
                        'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                        WHERE ct.rolTipo IN (:id) ORDER BY ct.id')
                ->setParameter('id', array(9));

        $cargos = $query->getResult();
        $cargosArray = array();
        foreach ($cargos as $c) {
            $cargosArray[$c->getId()] = $c->getCargo();
        }

        $financiamiento = $em->createQuery(
                        'SELECT ft FROM SieAppWebBundle:FinanciamientoTipo ft
                WHERE ft.id NOT IN  (:id) ORDER BY ft.id')
                ->setParameter('id', array(0, 5))
                ->getResult();
        $financiamientoArray = array();
        foreach ($financiamiento as $f) {
            $financiamientoArray[$f->getId()] = $f->getFinanciamiento();
        }

        $formacion = $em->createQuery(
                        'SELECT ft FROM SieAppWebBundle:FormacionTipo ft
                WHERE ft.id NOT IN (:id) ORDER BY ft.id')
                ->setParameter('id', array(0, 22))
                ->getResult();
        $formacionArray = array();
        foreach ($formacion as $fr) {
            $formacionArray[$fr->getId()] = $fr->getFormacion();
        }

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('herramienta_info_personal_adm_update'))
                ->add('institucionEducativa', 'hidden', array('data' => $idInstitucion))
                ->add('gestion', 'hidden', array('data' => $gestion))
                ->add('idPersona', 'hidden', array('data' => $persona->getId()))
                ->add('idMaestroInscripcion', 'hidden', array('data' => $maestroInscripcion->getId()))
                ->add('funcion', 'choice', array('label' => 'Cargo que desempeña', 'required' => true, 'choices' => $cargosArray, 'data' => $maestroInscripcion->getCargoTipo()->getId(), 'attr' => array('class' => 'form-control', 'disabled' => 'true')))
                ->add('financiamiento', 'choice', array('label' => 'Fuente de Financiamiento', 'required' => true, 'choices' => $financiamientoArray, 'data' => $maestroInscripcion->getFinanciamientoTipo()->getId(), 'attr' => array('class' => 'form-control', 'disabled' => 'true')))
                ->add('formacion', 'choice', array('label' => 'Último grado de formación alcanzado', 'required' => true, 'choices' => $formacionArray, 'data' => $maestroInscripcion->getFormacionTipo()->getId(), 'attr' => array('class' => 'form-control', 'disabled' => 'true')))
                ->add('formacionDescripcion', 'text', array('label' => 'Descripción del último grado de formación alcanzado', 'required' => false, 'data' => $maestroInscripcion->getFormaciondescripcion(), 'attr' => array('class' => 'form-control jnumbersletters jupper', 'readonly' => 'true', 'autocomplete' => 'off', 'maxlength' => '90')))
                ->add('normalista', 'checkbox', array('required' => false, 'label' => 'Normalista', 'attr' => array('class' => 'form-control', 'checked' => $maestroInscripcion->getNormalista(), 'disabled' => 'true')))
                ->add('item', 'text', array('label' => 'Número de Item', 'required' => true, 'data' => $maestroInscripcion->getItem(), 'attr' => array('autocomplete' => 'off', 'class' => 'form-control', 'pattern' => '[0-9]{1,10}', 'disabled' => 'true')))
                ->add('idiomaOriginario', 'entity', array('class' => 'SieAppWebBundle:IdiomaMaterno', 'data' => ($maestroInscripcion->getEstudiaiomaMaterno()), 'label' => 'Actualmente que idioma originario esta estudiando', 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => 'true')))
                ->add('leeEscribeBraile', 'checkbox', array('required' => false, 'label' => 'Lee y Escribe en Braille', 'attr' => array('disabled' => 'true', 'class' => 'form-control', 'checked' => $maestroInscripcion->getLeeescribebraile())) )
                ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

        return $form;
    }
    
}
