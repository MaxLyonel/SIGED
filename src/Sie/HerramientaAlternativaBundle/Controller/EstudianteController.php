<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Form\EstudianteType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Estudiante controller. cambio de rama
 *
 */
class EstudianteController extends Controller {

    public $session;

    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Lists all Estudiante entities.
     *
     */
    public function indexAction(Request $request) {
        // data es un array con claves 'name', 'email', y 'message'
        return $this->render($this->session->get('pathSystem') . ':Estudiante:searchstudent.html.twig', array(
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
        $form = $this->createFormBuilder($estudiante)
                ->setAction($this->generateUrl('studentalt_result'))
                ->add('paterno', 'text', array('required' => false, 'invalid_message' => 'Campo obligatorio', 'attr' => array('pattern' => '[a-zñ A-ZÑ]{1,25}', 'style' => 'text-transform:uppercase', 'class' => 'form-control')))
                ->add('materno', 'text', array('required' => false, 'invalid_message' => 'Campo obligatorio', 'attr' => array('pattern' => '[a-zñ A-ZÑ]{1,25}', 'style' => 'text-transform:uppercase', 'class' => 'form-control')))
                ->add('nombre', 'text', array('required' => false, 'invalid_message' => 'Campor obligatorio', 'attr' => array('pattern' => '[a-zñ A-ZÑ]{1,25}', 'style' => 'text-transform:uppercase', 'class' => 'form-control')))
                ->add('lookfor', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        return $form;
    }

    /**
     * Find the student.
     *
     */
    public function resultAction(Request $request) {
        $sesion = $request->getSession();
        $form = $request->get('form');

        $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Estudiante');

        $query = $repository->createQueryBuilder('e')
                ->where('e.paterno like :paterno')
                ->andWhere('upper(e.materno) like :materno')
                ->andWhere('upper(e.nombre) like :nombre')
                ->setParameter('paterno', '%' . mb_strtoupper($form['paterno'], 'utf8') . '%')
                ->setParameter('materno', '%' . mb_strtoupper($form['materno'], 'utf8') . '%')
                ->setParameter('nombre', '%' . mb_strtoupper($form['nombre'], 'utf8') . '%')
                ->orderBy('e.paterno, e.materno, e.nombre', 'ASC')
                ->getQuery();
        $entities = $query->getResult();
        if (!$entities) {

            $message = "Busqueda no encontrada...";
            $this->addFlash('warningstudent', $message);
            return $this->redirectToRoute('estudiante_index');
        }

        $message = 'Resultado de la busqueda...';
        $this->addFlash('successstudent', $message);
        return $this->render($this->session->get('pathSystem') . ':Estudiante:resultstudent.html.twig', array(
                    'entities' => $entities,
        ));
    }

    public function historyAction(Request $request, $idStudent) {
        $em = $this->getDoctrine()->getManager();

        $student = $em->getRepository('SieAppWebBundle:Estudiante')->find($idStudent);
        $objInscriptions = $em->getRepository('SieAppWebBundle:Estudiante')->getHistoryInscription($idStudent);


        return $this->render($this->session->get('pathSystem') . ':Estudiante:resultHistory.html.twig', array(
                              'datastudent' => $student,
                    'dataInscription' => $objInscriptions,
        ));
        die;
    }

    public function newAction() {
        $formulario = $this->createNewForm();
        return $this->render($this->session->get('pathSystem') . ':Estudiante:new.html.twig', array('form' => $formulario->createView()));
    }

    private function createNewForm() {
        $estudiante = new Estudiante();
        $em = $this->getDoctrine()->getManager();
        $lugar = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneByLugarNivel(1);
        $form = $this->createFormBuilder($estudiante)
                ->setAction($this->generateUrl('estudiante_main_create'))
                ->add('carnetIdentidad', 'text', array('required' => true, 'pattern' => '[0-9]{4,10}'))
                ->add('paterno', 'text', array('required' => true, 'pattern' => '[a-zA-Z\s]{2,20}'))
                ->add('materno', 'text', array('required' => true, 'pattern' => '[a-zA-Z\s]{2,20}'))
                ->add('nombre', 'text', array('required' => true, 'pattern' => '[a-zA-Z\s]{2,50}'))
                ->add('oficialia', 'text', array('required' => false))
                ->add('libro', 'text', array('required' => false))
                ->add('partida', 'text', array('required' => false))
                ->add('folio', 'text', array('required' => false))
                ->add('sangreTipoId', 'entity', array('class' => 'SieAppWebBundle:SangreTipo', 'property' => 'grupoSanguineo'))
                ->add('idiomaMaternoId', 'entity', array('class' => 'SieAppWebBundle:IdiomaMaterno', 'property' => 'idiomaMaterno'))
                ->add('complemento', 'text', array('required' => false, 'pattern' => '[0-9]{1}[A-Z]{1}', 'max_length' => '2'))
                ->add('fechaNacimiento', 'text', array('required' => true, 'attr' => array('placeholder' => 'dd-mm-YYYY')))
                ->add('correo', 'text', array('required' => false))
                ->add('localidadNac', 'text', array('required' => false))
                ->add('celular', 'text', array('required' => false, 'pattern' => '[0-9]{6,10}'))
                ->add('carnetCodepedis', 'text', array('required' => false))
                ->add('carnetIbc', 'text', array('required' => false))
                ->add('generoTipo', 'entity', array('class' => 'SieAppWebBundle:GeneroTipo', 'property' => 'genero'))
                ->add('lugarNacTipo', 'entity', array('class' => 'SieAppWebBundle:LugarTipo',
                    'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('lt')
                                ->where('lt.lugarNivel = :id')
                                ->setParameter('id', '1')
                                ->orderBy('lt.id', 'ASC')
                        ;
                    }, 'property' => 'lugar'))
                ->add('estadoCivil', 'entity', array('class' => 'SieAppWebBundle:EstadoCivilTipo', 'property' => 'estadoCivil'))
                ->add('guardar', 'submit', array('label' => 'Guardar y continuar'))
                ->getForm();
        return $form;
    }

    public function createAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $form = $request->get('form');

            // Validar que el estudiante no este registrado
            $buscar_estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array(
                'paterno' => $form['paterno'],
                'materno' => $form['materno'],
                'nombre' => $form['nombre'],
                'fechaNacimiento' => new \DateTime($form['fechaNacimiento']),
                'generoTipo' => $form['generoTipo']
            ));
            if ($buscar_estudiante) {
                $this->get('session')->getFlashBag()->add('registroEstudianteError', 'El estudiante ya esta registrado en el sistema.');
                return $this->redirect($this->generateUrl('estudiante_main_new'));
            }

            echo "registrado";
            die;
            //REgistro de estudiante

            $estudiante = new Estudiante();

            $codigoRude = $this->generateRandomString(15);
            $estudiante->setCodigoRude($codigoRude);

            $estudiante->setCarnetIdentidad($form['carnetIdentidad']);
            $estudiante->setPasaporte('');
            $estudiante->setPaterno($form['paterno']);
            $estudiante->setMaterno($form['materno']);
            $estudiante->setNombre($form['nombre']);
            $estudiante->setOficialia($form['oficialia']);
            $estudiante->setLibro($form['libro']);
            $estudiante->setPartida($form['partida']);
            $estudiante->setFolio($form['folio']);
            $estudiante->setSangreTipoId($form['sangreTipoId']);
            $estudiante->setIdiomaMaternoId($form['idiomaMaternoId']);
            $estudiante->setSegipId(0);
            $estudiante->setComplemento($form['complemento']);
            $estudiante->setBolean(0);
            $estudiante->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
            $estudiante->setFechaModificacion(new \DateTime('now'));
            $estudiante->setCorreo($form['correo']);
            $estudiante->setLocalidadNac($form['localidadNac']);
            $estudiante->setCelular($form['celular']);
            $estudiante->setCarnetCodepedis($form['carnetCodepedis']);
            $estudiante->setCarnetIbc($form['carnetIbc']);
            $estudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['generoTipo']));
            $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['lugarNacTipo']));
            $estudiante->setEstadoCivil($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findOneById($form['estadoCivil']));


            $em->persist($estudiante);
            $em->flush();
            ///// falta autoincrement en tabla estudiante y estudianteespecialindirecta

            $sesion = $request->getSession();
            $sesion->set('estudianteId', $estudiante->getId());

            $this->get('session')->getFlashBag()->add('registroEstudiante', 'El estudiante fue registrado correctamente.');
            return $this->redirect($this->generateUrl('estudianteinscripcion_new'));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('registroEstudiante', 'Error al registrar el estudiante.');
            return $this->redirect($this->generateUrl('estudianteespecialindirecta'));
        }
    }

    function generateRandomString($length = 15) {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }

}
