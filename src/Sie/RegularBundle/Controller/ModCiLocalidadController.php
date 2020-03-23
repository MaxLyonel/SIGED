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
        // $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude'], 'segipId' => 0));
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));
        //verificamos si existe el estudiante
        if ($student) {
            //*******VERIFICANDO QUE LA/EL ESTUDIANTE TENGA INSCRIPCIÓN EN LA GESTIÓN ACTUAL
            $objUe = $em->getRepository('SieAppWebBundle:Estudiante')->getUeIdbyEstudianteId($student->getId(), $this->session->get('currentyear'));
            if (!$objUe) {
                $message = "La/El estudiante con código RUDE: " . $student->getCodigoRude() . ", no presenta inscripción para la presente gestión.";
                $this->addFlash('noticilocalidad', $message);
                return $this->redirectToRoute('modificar_ci_localidad_index');
            }
            //VERIFICANDO TUICIÓN DEL USUARIO
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $objUe[0]['ueId']);
            $query->bindValue(':roluser', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();
            
            if (!$aTuicion[0]['get_ue_tuicion']) {
                $message = "EL Usuario no tiene tuición para realizar la operación.";
                $this->addFlash('noticilocalidad', $message);
                return $this->redirectToRoute('modificar_ci_localidad_index');
            }
            //*******VERIFICANDO QUE LA/EL ESTUDIANTE NO TENGA TRÁMITES EN DIPLOMAS
            $tramite=$this->get('seguimiento')->getDiploma($student->getCodigoRude());
            if ($tramite) {
                $message = "La/El estudiante con código RUDE: " . $student->getCodigoRude() . ", cuenta con trámite de diplomas, por lo que la modificación de datos no se realizará.";
                $this->addFlash('noticilocalidad', $message);
                return $this->redirectToRoute('modificar_ci_localidad_index');
            }
            //*******VERIFICANDO QUE LA/EL ESTUDIANTE NO TENGA PARTICIPACIÓN EN OLIMPIADAS
            $olimpiadas=$this->get('seguimiento')->getOlimpiadas($student->getCodigoRude());
            if ($olimpiadas) {
                $message = "La/El estudiante con código RUDE: " . $student->getCodigoRude() . ", cuenta con participación en la Olimpiada Científica Plurinacinal, por lo que la modificación de datos no se realizará.";
                $this->addFlash('noticilocalidad', $message);
                return $this->redirectToRoute('modificar_ci_localidad_index');
            }
            //*******VERIFICANDO QUE LA/EL ESTUDIANTE NO TENGA DATOS EN BACHILLER DESTACADO
            $bdestacado=$this->get('seguimiento')->getBachillerDestacado($student->getCodigoRude());
            if ($bdestacado) {
                $message = "La/El estudiante con código RUDE: " . $student->getCodigoRude() . ", cuenta con historial en Bachiller Destacado, por lo que la modificación de datos no se realizará.";
                $this->addFlash('notihistory', $message);            
                return $this->render('SieRegularBundle:UnificacionRude:resulterror.html.twig' );
            }
            //*******VERIFICANDO QUE LA/EL ESTUDIANTE NO TENGA PARTICIPACIÓN EN JUEGOS
            $juegos=$this->get('seguimiento')->getJuegos($student->getCodigoRude());
            if ($juegos){
                $message = "La/El estudiante con código RUDE: " . $student->getCodigoRude() . ", cuenta con participación en los Juegos Estudiantiles Plurinacionales, por lo que la modificación de datos no se realizará.";
                $this->addFlash('noticilocalidad', $message);
                return $this->redirectToRoute('modificar_ci_localidad_index');
            }

            $objstudent = $em->getRepository('SieAppWebBundle:Estudiante');
            $query = $objstudent->createQueryBuilder('e')
                ->select('e.id as idStudent, e.paterno, e.materno,e.nombre, e.fechaNacimiento, g.genero, e.carnetIdentidad', 'e.complemento', 'e.oficialia', 'e.libro', 'e.partida', 'e.folio', 'IDENTITY(e.generoTipo) as generoId', 'ptp.pais', '(ltd.lugar) as departamento', 'ltp.lugar as provincia', 'e.localidadNac')
                ->leftjoin('SieAppWebBundle:PaisTipo', 'ptp', 'WITH', 'e.paisTipo = ptp.id')
                ->leftjoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'e.lugarNacTipo = ltd.id')
                ->leftjoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'e.lugarProvNacTipo = ltp.id')
                ->leftjoin('SieAppWebBundle:GeneroTipo', 'g', 'WITH', 'e.generoTipo = g.id')
                ->where('e.id = :id')
                ->setParameter('id', $student->getId())
                ->getQuery();
            $infoStudent = $query->getResult();
            
            $message = "Resultado de la búsqueda:";
            $this->addFlash('successcilocalidad', $message);
            return $this->render($this->session->get('pathSystem') . ':ModCiLocalidad:result.html.twig', array(
                'form' => $this->createFormStudent($infoStudent[0])->createView(),
                'carnetIdentidad' => $infoStudent[0]['carnetIdentidad'],
                'codigoRude' => $student->getCodigoRude()
            ));
        } else {
            $message = "La/El Estudiante con código RUDE: ".$form['codigoRude'].", no existe y/o no cuenta con errores.";
            $this->addFlash('noticilocalidad', $message);
            return $this->redirectToRoute('modificar_ci_localidad_index');
        }
    }

    private function createFormStudent($data) {
        $formStudent = $this->createFormBuilder()
            ->setAction($this->generateUrl('modificar_ci_localidad_save'))
            ->add('idStudent', 'hidden', array('data' => $data['idStudent']));

            if (!$data['paterno']) {
                $formStudent->add('paterno', 'text', array('label' => 'Paterno', 'data' => $data['paterno'], 'required' => false, 'attr' => array('class' => 'form-control')));
            } else {
                $formStudent->add('paterno', 'text', array('label' => 'Paterno', 'data' => $data['paterno'], 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)));
            }

            if (!$data['materno']) {
                $formStudent->add('materno', 'text', array('label' => 'Materno', 'data' => $data['materno'], 'required' => false, 'attr' => array('class' => 'form-control')));
            } else {
                $formStudent->add('materno', 'text', array('label' => 'Materno', 'data' => $data['materno'], 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)));
            }
            
            if (!$data['nombre']) {
                $formStudent->add('nombre', 'text', array('label' => 'Nombre', 'data' => $data['nombre'], 'required' => false, 'attr' => array('class' => 'form-control')));
            } else {
                $formStudent->add('nombre', 'text', array('label' => 'Nombre', 'data' => $data['nombre'], 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)));
            }
            
            $formStudent->add('genero', 'text', array('label' => 'Género', 'data' => $data['genero'], 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
            ->add('pais', 'text', array('label' => 'Pais', 'data' => strtoupper($data['pais']), 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
            ->add('departamento', 'text', array('label' => 'Departamento', 'data' => strtoupper($data['departamento']), 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
            ->add('provincia', 'text', array('label' => 'Provincia', 'data' => strtoupper($data['provincia']), 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
            ->add('localidad', 'text', array('label' => 'Localidad', 'data' => strtoupper($data['localidadNac']), 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase', 'maxlength' => 50)))
            ->add('ci', 'text', array('label' => 'CI', 'data' => $data['carnetIdentidad'], 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{3,10}', 'maxlength' => 10, 'style' => 'text-transform:uppercase', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Si el Carnet de Identidad es extranjero, debe omitir la parte 'E-', pero debe escribir los ceros que contenga el C.I.")))
            ->add('complemento', 'text', array('label' => 'Complemento', 'data' => $data['complemento'], 'required' => false, 'attr' => array('maxlength' => 2, 'pattern' => '[0-9a-zA-Z]{2}', 'style' => 'text-transform:uppercase', 'class' => 'form-control', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que la expedición del C.I. Por favor NO coloque abreviaturas de Departamentos")))
            ->add('fechaNacimiento', 'date', array('widget' => 'single_text', 'format' => 'dd-MM-yyyy', 'label' => 'Fecha de Nacimiento', 'data' => $data['fechaNacimiento'], 'required' => true, 'attr' => array('class' => 'form-control calendario')));
        
        $formStudent->add('save', 'submit', array('label' => 'Guardar cambios'));

        return $formStudent->getForm();
    }

    /**
     * todo the registration of traslado
     * @param Request $request
     * 
     */
    public function saveAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');
            $carnetIdentidad = "";
            $complemento = "";
            $paterno = "";
            $materno = "";
            $nombre = "";
            $fechaNacimiento = "";
            $localidad = "";

            $student = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']);
            $oldDataStudent = clone $student;
            $oldDataStudent = (array)$oldDataStudent;

            if($student) {
                if(isset($form['complemento'])){
                    $complemento = $form['complemento'];
                } else {
                    $complemento = $student->getComplemento();
                }
    
                if(isset($form['ci'])){
                    $carnetIdentidad = $form['ci'];
                } else {
                    $carnetIdentidad = $student->getCarnetIdentidad();
                }
    
                if(isset($form['paterno'])){
                    $paterno = $form['paterno'];
                } else {
                    $paterno = $student->getPaterno();
                }
    
                if(isset($form['materno'])){
                    $materno = $form['materno'];
                } else {
                    $materno = $student->getMaterno();
                }
    
                if(isset($form['nombre'])){
                    $nombre = $form['nombre'];
                } else {
                    $nombre = $student->getNombre();
                }
    
                if(isset($form['fechaNacimiento'])){
                    $fechaNacimiento = $form['fechaNacimiento'];
                } else {
                    $fechaNacimiento = $student->getFechaNacimiento();
                }

                if(isset($form['localidad'])){
                    $localidad = $form['localidad'];
                } else {
                    $localidad = $student->getLocalidad();
                }

                $data = [
                    'complemento' => $complemento,
                    'primer_apellido' => $paterno,
                    'segundo_apellido' => $materno,
                    'nombre' => $nombre,
                    'fecha_nacimiento' => $fechaNacimiento
                ];
                
                $resultado = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($carnetIdentidad, $data, 'prod', 'academico');
        
                if($resultado) {
                    if(isset($form['ci'])){
                        $student->setCarnetIdentidad(mb_strtoupper($carnetIdentidad, 'utf-8'));
                    }
                    
                    if(isset($form['complemento'])){
                        $student->setComplemento(mb_strtoupper($complemento, 'utf-8'));
                    }

                    if(isset($form['paterno'])){
                        $student->setPaterno(mb_strtoupper($paterno, 'utf-8'));
                    }

                    if(isset($form['materno'])){
                        $student->setMaterno(mb_strtoupper($materno, 'utf-8'));
                    }

                    if(isset($form['nombre'])){
                        $student->setNombre(mb_strtoupper($nombre, 'utf-8'));
                    }

                    if(isset($form['localidad'])){
                        $student->setLocalidadNac(mb_strtoupper($localidad, 'utf-8'));
                    }

                    if(isset($form['fechaNacimiento'])){
                        $student->setFechaNacimiento(new \DateTime($fechaNacimiento));
                    }
                    
                    $student->setSegipId(1);
                    $em->persist($student);
                    $em->flush();

                    $newDataStudent = (array)$student;
                    $this->get('funciones')->setLogTransaccion(
                        $student->getId(),
                        'estudiante',
                        'U',
                        '',
                        $newDataStudent,
                        $oldDataStudent,
                        'SIGED',
                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                    );
                } else {
                    $message = 'Operación no realizada. Los datos ingresados no fueron validados por SEGIP.';
                    $this->addFlash('noticilocalidad', $message);
                    return $this->redirectToRoute('modificar_ci_localidad_index');
                }
            } else {
                $message = 'Operación no realizada. No existe la/el estudiante.';
                $this->addFlash('noticilocalidad', $message);
                return $this->redirectToRoute('modificar_ci_localidad_index');
            }

            $em->getConnection()->commit();
            $message = 'Operación realizada correctamente.';
            $this->addFlash('goodcilocalidad', $message);
            return $this->redirectToRoute('modificar_ci_localidad_index');
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $message = 'Operación no realizada. Ocurrió un error interno.';
            $this->addFlash('noticilocalidad', $message);
            return $this->redirectToRoute('modificar_ci_localidad_index');
        }
    }
}
