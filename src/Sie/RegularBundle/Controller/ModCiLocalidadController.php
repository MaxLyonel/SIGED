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
class ModCiLocalidadController extends Controller {

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
    public function indexAction() {
//die('krlos');
        $em = $this->getDoctrine()->getManager();

        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':ModCiLocalidad:index.html.twig', array(
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
                ->setAction($this->generateUrl('modificar_ci_localidad_result'))
                ->add('codigoRude', 'text', array('mapped' => false, 'label' => 'Rude', 'required' => true, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control', 'maxlength' => 18, 'pattern' => '[A-Z0-9]{13,18}')))
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
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));


        //verificamos si existe el estudiante
        if ($student) {
            $objUe = $em->getRepository('SieAppWebBundle:Estudiante')->getUeIdbyEstudianteId($student->getId(), $this->session->get('currentyear'));
            if (!$objUe) {
                $message = "El estudiante con rude: " . $student->getCodigoRude() . " no presenta inscripción para   la presente gestión ";
                $this->addFlash('noticilocalidad', $message);
                return $this->redirectToRoute('modificar_ci_localidad_index');
            }
            //look for the tuicion
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $objUe[0]['ueId']);
            $query->bindValue(':roluser', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();
            //check if the user has the tuicion
            if (!$aTuicion[0]['get_ue_tuicion']) {
                $message = "Usuario no tiene tuición para realizar la operación";
                $this->addFlash('noticilocalidad', $message);
                return $this->redirectToRoute('modificar_ci_localidad_index');
            }

            $objstudent = $em->getRepository('SieAppWebBundle:Estudiante');
            $query = $objstudent->createQueryBuilder('e')
                    ->select('e.id as idStudent, e.paterno, e.materno,e.nombre,g.genero, e.carnetIdentidad', 'e.complemento', 'e.oficialia', 'e.libro', 'e.partida', 'e.folio', 'IDENTITY(e.generoTipo) as generoId', 'ptp.pais', '(ltd.lugar) as departamento', 'ltp.lugar as provincia', 'e.localidadNac')
                    ->leftjoin('SieAppWebBundle:PaisTipo', 'ptp', 'WITH', 'e.paisTipo = ptp.id')
                    ->leftjoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'e.lugarNacTipo = ltd.id')
                    ->leftjoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'e.lugarProvNacTipo = ltp.id')
                    ->leftjoin('SieAppWebBundle:GeneroTipo', 'g', 'WITH', 'e.generoTipo = g.id')
                    ->where('e.id = :id')
                    ->setParameter('id', $student->getId())
                    ->getQuery();
            $infoStudent = $query->getResult();
            //create the form of student
            //$formStudent = $this->createFormStudent($infoStudent[0]);
            $message = "Resultado de la busqueda...";
            $this->addFlash('successcilocalidad', $message);
            return $this->render($this->session->get('pathSystem') . ':ModCiLocalidad:result.html.twig', array(
                        'form' => $this->createFormStudent($infoStudent[0])->createView(),
                        'carnetIdentidad' => $infoStudent[0]['carnetIdentidad'],
                        'codigoRude' => $student->getCodigoRude()
            ));

            //return $this->redirectToRoute('inscription_omitidos_index');
        } else {
            //the student doesnt exist
            $message = "Estudiante no existe";
            $this->addFlash('noticilocalidad', $message);
            return $this->redirectToRoute('modificar_ci_localidad_index');
        }
    }

    private function createFormStudent($data) {
        $formStudent = $this->createFormBuilder()
                ->setAction($this->generateUrl('modificar_ci_localidad_save'))
                ->add('idStudent', 'hidden', array('data' => $data['idStudent']))
                ->add('paterno', 'text', array('label' => 'Paterno', 'data' => $data['paterno'], 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
                ->add('materno', 'text', array('label' => 'Materno', 'data' => $data['materno'], 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
                ->add('nombre', 'text', array('label' => 'Nombre', 'data' => $data['nombre'], 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
                ->add('genero', 'text', array('label' => 'Género', 'data' => $data['genero'], 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
                ->add('pais', 'text', array('label' => 'Pais', 'data' => strtoupper($data['pais']), 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
                ->add('departamento', 'text', array('label' => 'Departamento', 'data' => strtoupper($data['departamento']), 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
                ->add('provincia', 'text', array('label' => 'Provincia', 'data' => strtoupper($data['provincia']), 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
                ->add('localidad', 'text', array('label' => 'Localidad', 'data' => strtoupper($data['localidadNac']), 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase', 'maxlength' => 50)));
        //check if the students ci exists
        if (!$data['carnetIdentidad']) {
            $formStudent->add('ci', 'text', array('label' => 'CI', 'data' => $data['carnetIdentidad'], 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9aA]{3,10}', 'maxlength' => 10, 'style' => 'text-transform:uppercase')));
            $formStudent->add('complemento', 'text', array('label' => 'Complemento', 'data' => $data['complemento'], 'required' => false, 'attr' => array('maxlength' => 2, 'style' => 'text-transform:uppercase', 'class' => 'form-control', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que la expedicion de su CI, por favor NO coloque abreviaturas de departamentos")));
        } else {
            $formStudent->add('ci', 'text', array('label' => 'CI', 'data' => $data['carnetIdentidad'], 'attr' => array('class' => 'form-control', 'disabled' => true)));
            $formStudent->add('complemento', 'text', array('label' => 'Complemento', 'data' => $data['complemento'], 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que la expedicion de su CI, por favor NO coloque abreviaturas de departamentos", 'disabled' => true)));
        }
        $formStudent->add('save', 'submit');

        return $formStudent->getForm();
    }

    /**
     * todo the registration of traslado
     * @param Request $request
     * 
     */
    public function saveAction(Request $request) {
        try {
            //create conexion on DB
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            //get the variblees
            $form = $request->get('form');
            //save the student info
            $student = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']);
            (isset($form['ci'])) ? $student->setCarnetIdentidad($form['ci']) : '';
            (isset($form['complemento'])) ? $student->setComplemento(strtoupper($form['complemento'])) : '';
            $student->setLocalidadNac(strtoupper($form['localidad']));
            $em->persist($student);
            $em->flush();
            //do the commit of DB
            $em->getConnection()->commit();
            $message = 'Operación realizada correctamente';
            $this->addFlash('goodcilocalidad', $message);
            return $this->redirectToRoute('modificar_ci_localidad_index');
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }

}
