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
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;

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
        // dump($form); die;
        $esGuanawek = false;
        // $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude'], 'segipId' => 0));
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));
        //verificamos si existe el estudiante
        
        if ($student) {
            //*******VERIFICANDO QUE LA/EL ESTUDIANTE TENGA INSCRIPCIÓN EN LA GESTIÓN ACTUAL
            $ie_id=$this->session->get('ie_id');
            $esGuanawek=$this->session->get('esGuanawek');
            if($esGuanawek)
            {
                $_gestion=2021;
                $objUe = $em->getRepository('SieAppWebBundle:Estudiante')->getUeIdbyEstudianteId_SinMatricula($student->getId(), $_gestion);
                $esGuanawek= true;
            }
            else
            {
                $_gestion=$this->session->get('currentyear');
                //$objUe = $em->getRepository('SieAppWebBundle:Estudiante')->getUeIdbyEstudianteId($student->getId(), $this->session->get('currentyear'));
                $objUe = $em->getRepository('SieAppWebBundle:Estudiante')->getUeIdbyEstudianteId($student->getId(), $_gestion);
            }
            
            if (!$objUe) {
                $message = "La/El estudiante con código RUDE: " . $student->getCodigoRude() . ", no presenta inscripción para la presente gestión.";
                $this->addFlash('noticilocalidad', $message);
                return $this->redirectToRoute('modificar_ci_localidad_index');
            }
            //VERIFICANDO TUICIÓN DEL USUARIO
            $cont_true = 0;
            foreach ($objUe as $value) {
                $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
                $query->bindValue(':user_id', $this->session->get('userId'));
                $query->bindValue(':sie', $value['ueId']);
                $query->bindValue(':roluser', $this->session->get('roluser'));
                $query->execute();
                $aTuicion = $query->fetchAll();
                if($aTuicion[0]['get_ue_tuicion']) {
                    $cont_true++;
                }
            }
            
            if ($cont_true === 0) {
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
             //*******VERIFICANDO QUE LA/EL ESTUDIANTE NO TENGA DATOS EN BACHILLER DESTACADO
            $bdestacado=$this->get('seguimiento')->getBachillerDestacado($student->getCodigoRude());
            if ($bdestacado) {
                $message = "La/El estudiante con código RUDE: " . $student->getCodigoRude() . ", cuenta con historial en Bachiller Destacado, por lo que la modificación de datos no se realizará.";
                $this->addFlash('notihistory', $message);            
                return $this->render('SieRegularBundle:UnificacionRude:resulterror.html.twig' );
            }
            //*******VERIFICANDO QUE LA/EL ESTUDIANTE NO TENGA PARTICIPACIÓN EN OLIMPIADAS
            // $olimpiadas=$this->get('seguimiento')->getOlimpiadasGestion($student->getCodigoRude(), $this->session->get('currentyear'));
            // if ($olimpiadas) {
            //     $message = "La/El estudiante con código RUDE: " . $student->getCodigoRude() . ", cuenta con participación en la Olimpiada Científica Plurinacinal, por lo que la modificación de datos no se realizará.";
            //     $this->addFlash('noticilocalidad', $message);
            //     return $this->redirectToRoute('modificar_ci_localidad_index');
            // }           
            //*******VERIFICANDO QUE LA/EL ESTUDIANTE NO TENGA PARTICIPACIÓN EN JUEGOS
            $juegos=$this->get('seguimiento')->getJuegosGestion($student->getCodigoRude(), $this->session->get('currentyear'));
            if ($juegos){
                $message = "La/El estudiante con código RUDE: " . $student->getCodigoRude() . ", cuenta con participación en los Juegos Estudiantiles Plurinacionales, por lo que la modificación de datos no se realizará.";
                $this->addFlash('noticilocalidad', $message);
                return $this->redirectToRoute('modificar_ci_localidad_index');
            }

            $objstudent = $em->getRepository('SieAppWebBundle:Estudiante');
            $query = $objstudent->createQueryBuilder('e')
                ->select('e.id as idStudent, e.paterno, e.materno,e.nombre, e.fechaNacimiento, g.genero, e.carnetIdentidad', 'e.complemento', 'e.segipId', 'e.oficialia', 'e.libro', 'e.partida', 'e.folio', 'IDENTITY(e.generoTipo) as generoId', 'ptp.pais', '(ltd.lugar) as departamento', 'ltp.lugar as provincia', 'e.localidadNac', 'IDENTITY(e.cedulaTipo) as cedulaTipo')
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
            
            //dump($infoStudent[0]);die;
            $form=$this->createFormStudent($infoStudent[0])->createView();
            if($esGuanawek)
                $form=$this->createFormStudentGuanawek($infoStudent[0])->createView();
            // dump($this->session->get('pathSystem'));die;
            // dump($form['cedulatipo']);die;
            return $this->render($this->session->get('pathSystem') . ':ModCiLocalidad:result.html.twig', array(
                //'form' => $this->createFormStudent($infoStudent[0])->createView(),
                'form' => $form,
                'carnetIdentidad' => $infoStudent[0]['carnetIdentidad'],
                'codigoRude' => $student->getCodigoRude(),
                'esGuanawek' => $esGuanawek,
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

            /*if (!$data['paterno']) {
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
            }*/
            if ($data['cedulaTipo']==null) {
                $cedulaTipo = 1;
            } else {
                $cedulaTipo = $data['cedulaTipo'];
            }

            if ($data['segipId']==0) {
                $formStudent->add('ci', 'text', array('label' => 'CI', 'data' => $data['carnetIdentidad'], 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{3,10}', 'maxlength' => 10, 'style' => 'text-transform:uppercase', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Si el Carnet de Identidad es extranjero, debe omitir la parte 'E-' y marcar en Tipo de Cedula como 'EXTRANJERO', pero debe escribir los ceros que contenga el C.I.")));
                $formStudent->add('complemento', 'text', array('label' => 'Complemento', 'data' => $data['complemento'], 'required' => false, 'attr' => array('maxlength' => 2, 'pattern' => '[0-9a-zA-Z]{2}', 'style' => 'text-transform:uppercase', 'class' => 'form-control', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que la expedición del C.I. Por favor NO coloque abreviaturas de Departamentos")));
                $formStudent->add('localidad', 'text', array('label' => 'Localidad', 'data' => strtoupper($data['localidadNac']), 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase', 'maxlength' => 50))); 

                $formStudent->add('cedulatipo', 'choice', array(
                    'choices'   => array(1 => 'Nacional', 2 => 'Extranjero'),
                    'data' => $cedulaTipo,
                    'expanded'  => true,
                    'multiple'  => false,
                    'attr' => ['class' => 'form-check form-check-inline'],
                    'label' => "Tipo de Cedula de Identidad"
                ));
            } else {
                $formStudent->add('ci', 'text', array('label' => 'CI', 'data' => $data['carnetIdentidad'], 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)));
                $formStudent->add('complemento', 'text', array('label' => 'Complemento', 'data' => $data['complemento'], 'required' => false, 'attr' => array('maxlength' => 2, 'pattern' => '[0-9a-zA-Z]{2}', 'style' => 'text-transform:uppercase', 'class' => 'form-control', 'disabled' => true)));

                //  SOLO ALTERNATIVA
                if( $this->session->get('pathSystem') == 'SieHerramientaAlternativaBundle' ){
                    $formStudent->add('localidad', 'text', array('label' => 'Localidad', 'data' => strtoupper($data['localidadNac']), 'attr' => array('class' => 'form-control')));
                }else{
                    $formStudent->add('localidad', 'text', array('label' => 'Localidad', 'data' => strtoupper($data['localidadNac']), 'attr' => array('class' => 'form-control', 'disabled' => true)));
                }
                
                $formStudent->add('cedulatipo', 'choice', array(
                    'choices'   => array(1 => 'Nacional', 2 => 'Extranjero'),
                    'data' => $cedulaTipo,
                    'expanded'  => true,
                    'multiple'  => false,
                    'attr' => ['class' => 'form-check form-check-inline'],
                    'label' => "Tipo de Cedula de Identidad",
                    'disabled' => true
                ));

            }

            $formStudent->add('paterno', 'text', array('label' => 'Paterno', 'data' => $data['paterno'], 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
            ->add('materno', 'text', array('label' => 'Materno', 'data' => $data['materno'], 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
            ->add('nombre', 'text', array('label' => 'Nombre', 'data' => $data['nombre'], 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
            ->add('genero', 'text', array('label' => 'Género', 'data' => $data['genero'], 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
            ->add('pais', 'text', array('label' => 'Pais', 'data' => strtoupper($data['pais']), 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
            ->add('departamento', 'text', array('label' => 'Departamento', 'data' => strtoupper($data['departamento']), 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
            ->add('provincia', 'text', array('label' => 'Provincia', 'data' => strtoupper($data['provincia']), 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
            // ->add('ci', 'text', array('label' => 'CI', 'data' => $data['carnetIdentidad'], 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{3,10}', 'maxlength' => 10, 'style' => 'text-transform:uppercase', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Si el Carnet de Identidad es extranjero, debe omitir la parte 'E-' y marcar en Tipo de Cedula como 'EXTRANJERO', pero debe escribir los ceros que contenga el C.I.")))
            // ->add('complemento', 'text', array('label' => 'Complemento', 'data' => $data['complemento'], 'required' => false, 'attr' => array('maxlength' => 2, 'pattern' => '[0-9a-zA-Z]{2}', 'style' => 'text-transform:uppercase', 'class' => 'form-control', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que la expedición del C.I. Por favor NO coloque abreviaturas de Departamentos")))
            ->add('fechaNacimiento', 'date', array('widget' => 'single_text', 'format' => 'dd-MM-yyyy', 'label' => 'Fecha de Nacimiento', 'data' => $data['fechaNacimiento'], 'required' => true, 'attr' => array('class' => 'form-control calendario', 'disabled' => true)));
        
        $formStudent->add('save', 'submit', array('label' => 'Guardar cambios'));

        return $formStudent->getForm();
    }

    private function createFormStudentGuanawek($data)
    {
        $em = $this->getDoctrine()->getManager();
        $formStudent = $this->createFormBuilder()
            ->setAction($this->generateUrl('modificar_genero_save'))
            ->add('idStudent', 'hidden', array('data' => $data['idStudent']));

            if (!$data['paterno']) {
                $formStudent->add('paterno', 'text', array('label' => 'Paterno', 'data' => $data['paterno'], 'required' => false, 'attr' => array('class' => 'form-control' )));
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

            if ($data['segipId']==0) {
                $formStudent->add('ci', 'text', array('label' => 'CI', 'data' => $data['carnetIdentidad'], 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{3,10}', 'maxlength' => 10, 'style' => 'text-transform:uppercase', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Si el Carnet de Identidad es extranjero, debe omitir la parte 'E-' y marcar en Tipo de Cedula como 'EXTRANJERO', pero debe escribir los ceros que contenga el C.I.")));
                $formStudent->add('complemento', 'text', array('label' => 'Complemento', 'data' => $data['complemento'], 'required' => false, 'attr' => array('maxlength' => 2, 'pattern' => '[0-9a-zA-Z]{2}', 'style' => 'text-transform:uppercase', 'class' => 'form-control', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que la expedición del C.I. Por favor NO coloque abreviaturas de Departamentos")));
            } else {
                $formStudent->add('ci', 'text', array('label' => 'CI', 'data' => $data['carnetIdentidad'], 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)));
                $formStudent->add('complemento', 'text', array('label' => 'Complemento', 'data' => $data['complemento'], 'required' => false, 'attr' => array('maxlength' => 2, 'pattern' => '[0-9a-zA-Z]{2}', 'style' => 'text-transform:uppercase', 'class' => 'form-control', 'disabled' => true)));
                //$formStudent->add('nombre', 'text', array('label' => 'Nombre', 'data' => $data['nombre'], 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)));
            }
            $generos = [1,2];
            $formStudent
            ->add('genero', 'text', array('label' => 'Género', 'data' => $data['genero'], 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
            /* ->add('genero', 'entity', array(
             'class' => 'SieAppWebBundle:GeneroTipo',
             'query_builder' => function (EntityRepository $e) use ($generos)
             {
                 return $e->createQueryBuilder('gt')
                         ->where('gt.id in (:ids)')
                         ->setParameter('ids', $generos)
                         ->orderBy('gt.id', 'ASC');
             },
             'empty_value' => 'Selecionar...',
             'required'=>true,
             'attr' => array('class' => 'form-control')
             //'data'=>($data['genero'] != null)?$em->getReference('SieAppWebBundle:GeneroTipo', $data['genero']):''
             ))*/
            ->add('pais', 'text', array('label' => 'Pais', 'data' => strtoupper($data['pais']), 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
            ->add('departamento', 'text', array('label' => 'Departamento', 'data' => strtoupper($data['departamento']), 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
            ->add('provincia', 'text', array('label' => 'Provincia', 'data' => strtoupper($data['provincia']), 'required' => false, 'attr' => array('class' => 'form-control', 'disabled' => true)))
            ->add('localidad', 'text', array('label' => 'Localidad', 'data' => strtoupper($data['localidadNac']), 'attr' => array('class' => 'form-control', 'disabled' => true ,'style' => 'text-transform:uppercase', 'maxlength' => 50)))
            ->add('ci', 'text', array('label' => 'CI', 'data' => $data['carnetIdentidad'], 'required' => true, 'attr' => array('class' => 'form-control', 'disabled' => true , 'pattern' => '[0-9]{3,10}', 'maxlength' => 10, 'style' => 'text-transform:uppercase', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Si el Carnet de Identidad es extranjero, debe omitir la parte 'E-' y marcar en Tipo de Cedula como 'EXTRANJERO', pero debe escribir los ceros que contenga el C.I.")))
            ->add('complemento', 'text', array('label' => 'Complemento', 'data' => $data['complemento'], 'required' => false, 'attr' => array('maxlength' => 2, 'disabled' => true, 'pattern' => '[0-9a-zA-Z]{2}', 'style' => 'text-transform:uppercase', 'class' => 'form-control', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que la expedición del C.I. Por favor NO coloque abreviaturas de Departamentos")))
            ->add('cedulatipo', 'choice', array(
                'choices'   => array(1 => 'Nacional', 2 => 'Extranjero'),
                'data' => 1,
                'expanded'  => true,
                'multiple'  => false,
                'attr' => ['class' => 'form-check form-check-inline'],
                'label' => "Tipo de Cedula de Identidad"
                ))
            ->add('fechaNacimiento', 'date', array('widget' => 'single_text', 'format' => 'dd-MM-yyyy', 'label' => 'Fecha de Nacimiento', 'data' => $data['fechaNacimiento'], 'required' => false, 'attr' => array('class' => 'form-control calendario','disabled' => true)));
        
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
                    $fechaNacimiento =  $student->getFechaNacimiento()->format('d-m-Y'); 
                }
                if(isset($form['cedulatipo'])){
                    $cedulaTipo = $form['cedulatipo'];
                } else {
                    $cedulaTipo =  $student->getCedulaTipo(); 
                }

                //$fechaNacimiento = date('dd-MM-yyyy',$student->getFechaNacimiento()); 

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
                    'fecha_nacimiento' => $fechaNacimiento,
                    'tipo_persona' => $cedulaTipo == null ? 1 : $cedulaTipo->getId()
                ];
                
                $resultado = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($carnetIdentidad, $data, 'prod', 'academico');
                //quitamos la validacion solo para guanawek                

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

                    if( $cedulaTipo !== null ){
                        $student->setCedulaTipo($em->getRepository('SieAppWebBundle:CedulaTipo')->find($cedulaTipo));
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
        } catch (\Exception $ex) {
            
            $em->getConnection()->rollback();
            $message = 'Operación no realizada. Ocurrió un error interno.';
            $this->addFlash('noticilocalidad', $message);
            return $this->redirectToRoute('modificar_ci_localidad_index');
        }
    }


    public function saveGeneroAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');

            $genero = "";

            $student = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']);
            $oldDataStudent = clone $student;
            $oldDataStudent = (array)$oldDataStudent;

            if($student)
            {
                if(isset($form['genero']))
                {
                    $genero = $em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['genero']);
                }
                else
                {
                    $genero = $student->getGeneroTipo();
                }

                $data = [
                   'genero'=>$genero
                ];
                
                $esGuanawek=$this->session->get('esGuanawek');
                //quitamos la validacion solo para guanawek
                $resultado = true;
                if($esGuanawek==false)
                    $resultado = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($carnetIdentidad, $data, 'prod', 'academico');
                
                if($resultado)
                {
                    if(isset($form['genero']))
                    {
                        $student->setGeneroTipo($genero);
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

                    $mensaje = "Se realizó el proceso satisfactoriamente. Los datos de la/el estudiante:".$student->getCodigoRude().", se corrigieron correctamente.";
                    $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneBy(array('llave' => $student->getCodigoRude(), 'validacionReglaTipo' => 3));
                    
                    if($vproceso)
                    {
                        $this->ratificar($vproceso);
                    }

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


    private function esGuanawek($ie_id,$gestion)
    {
      $return=false;
      $tecnico_humanistico=4; //institucioneducativa_humanistico_tecnico_tipo_id 
      $departamentos=array();
      $em = $this->getDoctrine()->getManager();
      $db = $em->getConnection();
      $query = '
      select * 
      from 
      institucioneducativa_humanistico_tecnico 
      where 
      institucioneducativa_humanistico_tecnico_tipo_id = ? 
      and gestion_tipo_id = ?
      and institucioneducativa_id = ?';

      $stmt = $db->prepare($query);
      $params = array($tecnico_humanistico,$gestion,$ie_id);
      $stmt->execute($params);
      $guanawek=$stmt->fetchAll();

      if($guanawek)
        $return=true;

      return $return;
    }

    private function ratificar($vproceso)
    {
        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $arrayRegistro = null;

        try {
            // Antes
            $arrayRegistro['id'] = $vproceso->getId();
            $arrayRegistro['fecha_proceso'] = $vproceso->getFechaProceso();
            $arrayRegistro['validacion_regla_tipo_id'] = $vproceso->getValidacionReglaTipo()->getId();
            $arrayRegistro['llave'] = $vproceso->getLlave();
            $arrayRegistro['gestion_tipo_id'] = $vproceso->getGestionTipoId();
            $arrayRegistro['periodo_tipo_id'] = $vproceso->getPeriodoTipoId();
            $arrayRegistro['es_activo'] = $vproceso->getEsActivo();
            $arrayRegistro['obs'] = $vproceso->getObs();
            $arrayRegistro['institucioneducativa_id'] = $vproceso->getInstitucioneducativaId();
            $arrayRegistro['lugar_tipo_id_distrito'] = $vproceso->getLugarTipoIdDistrito();
            $arrayRegistro['solucion_tipo_id'] = $vproceso->getSolucionTipoId();
            $arrayRegistro['omitido'] = $vproceso->getOmitido();

            $antes = json_encode($arrayRegistro);

            // despues
            $arrayRegistro = null;

            $vproceso->setEsActivo(true);
            $em->persist($vproceso);
            $em->flush();
            // $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($form['idDetalle']);

            $arrayRegistro['id'] = $vproceso->getId();
            $arrayRegistro['fecha_proceso'] = $vproceso->getFechaProceso();
            $arrayRegistro['validacion_regla_tipo_id'] = $vproceso->getValidacionReglaTipo()->getId();
            $arrayRegistro['llave'] = $vproceso->getLlave();
            $arrayRegistro['gestion_tipo_id'] = $vproceso->getGestionTipoId();
            $arrayRegistro['periodo_tipo_id'] = $vproceso->getPeriodoTipoId();
            $arrayRegistro['es_activo'] = $vproceso->getEsActivo();
            $arrayRegistro['obs'] = $vproceso->getObs();
            $arrayRegistro['institucioneducativa_id'] = $vproceso->getInstitucioneducativaId();
            $arrayRegistro['lugar_tipo_id_distrito'] = $vproceso->getLugarTipoIdDistrito();
            $arrayRegistro['solucion_tipo_id'] = $vproceso->getSolucionTipoId();
            $arrayRegistro['omitido'] = $vproceso->getOmitido();

            $despues = json_encode($arrayRegistro);

            // registro del log
            $resp = $defaultController->setLogTransaccion(
                $vproceso->getId(),
                'validacion_proceso',
                'U',
                json_encode(array('browser' => $_SERVER['HTTP_USER_AGENT'],'ip'=>$_SERVER['REMOTE_ADDR'])),
                $this->session->get('userId'),
                '',
                $despues,
                $antes,
                'SIGED',
                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
            );

            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

}
