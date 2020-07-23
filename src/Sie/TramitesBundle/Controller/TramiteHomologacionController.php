<?php

namespace Sie\TramitesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\PaisTipo;
use Sie\AppWebBundle\Form\EstudianteType;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\EstudianteInscripcionDocumento;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sie\TramitesBundle\Controller\DefaultController as defaultTramiteController;
use Sie\TramitesBundle\Controller\DocumentoController as documentoController;

/**
 * Estudiante controller.
 *
 */
class TramiteHomologacionController extends Controller {

    private $session;
    public $lugarNac;
    public $dpto;
    public $aCursos;
    public $lastUE;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $this->aCursos = $this->fillCursos();
    }

    /**
     * build the cursos in a array
     * @param type $limitA
     * @param type $limitB
     * @param type $limitC
     * return array with the courses
     */
    private function fillCursos() {
        $this->aCursos = array(
            ('11-1-1'),
            ('11-1-2'),
            ('12-1-1'),
            ('12-1-2'),
            ('12-1-3'),
            ('12-2-4'),
            ('12-2-5'),
            ('12-2-6'),
            ('13-1-1'),
            ('13-1-2'),
            ('13-2-3'),
            ('13-2-4'),
            ('13-3-5'),
            ('13-3-6')
        );
        return($this->aCursos);
    }

    /**
     * Lists all Estudiante entities.
     *
     */
    public function dipHumIndexAction(Request $request) {
        // $defaultTramiteController = new defaultTramiteController();
        // $defaultTramiteController->setContainer($this->container);
        // $route = $request->get('_route');

        // $activeMenu = $defaultTramiteController->setActiveMenu($route);

        $em = $this->getDoctrine()->getManager();

        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':Homologacion:dipHumIndex.html.twig', array(
                    'form' => $this->createSearchForm()->createView(),
                    'titulo' => 'Homologación',
                    'subtitulo' => 'Diploma de Bachiller'
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
        $em = $this->getDoctrine()->getManager();
        $estudiante = new Estudiante();

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('tramite_homologacion_diploma_humanistico_estudiante_busca'))                
                ->add('paterno', 'text', array('label' => 'Paterno', 'mapped' => false, 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,45}', 'style' => 'text-transform:uppercase')))
                ->add('materno', 'text', array('label' => 'Materno', 'mapped' => false, 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,45}', 'style' => 'text-transform:uppercase')))
                ->add('nombre', 'text', array('label' => 'Nombre', 'mapped' => false, 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,40}', 'style' => 'text-transform:uppercase')))
                ->add('buscar', 'button', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /**
     * get the same students with the data send
     * @param type $data
     * @return type
     * return obj sutudent list
     */
    private function getCoincidenciasStudent($data) {
        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Estudiante');
        $query = $repository->createQueryBuilder('e')
                ->select('e.id, e.carnetIdentidad, e.fechaNacimiento, e.codigoRude, e.nombre, e.paterno, e.materno, e.complemento, upper(ltp.lugar) as lugarProvincia, upper(ltd.lugar) as lugarDepartamento, pt.id as paisId, pt.pais as pais, e.segipId')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'ltp.id = e.lugarProvNacTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp.lugarTipo')
                ->leftJoin('SieAppWebBundle:PaisTipo', 'pt', 'WITH', 'pt.id = e.paisTipo')
                ->where('e.paterno like :paterno')
                ->andWhere('e.materno like :materno')
                ->andWhere('e.nombre like :nombre')
                ->setParameter('paterno', strtoupper($data['paterno']) . '%')
                ->setParameter('materno', strtoupper($data['materno']) . '%')
                ->setParameter('nombre', strtoupper($data['nombre']) . '%')
                ->orderBy('e.paterno, e.materno, e.nombre', 'ASC')
                ->getQuery();
        try {
            //return $query->getArrayResult();
            //dump($query->getResult());die;
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $exc) {
//echo $exc->getTraceAsString();
            return array();
        }
    }

    /**
     * get the same students with the data send
     * @param type $data
     * @return type
     * return obj sutudent list
     */
    private function getStudent($id) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Estudiante');
        $query = $repository->createQueryBuilder('e')
                ->select("e.id, e.carnetIdentidad, (e.fechaNacimiento) as fechaNacimiento, gt.genero, e.codigoRude, e.nombre, e.paterno, e.materno, e.complemento, e.pasaporte, e.localidadNac as localidad, upper(ltp.lugar) as lugarProvincia, upper(ltd.lugar) as lugarDepartamento, dt.id as lugarEmitidoId, upper(dt.departamento) as lugarEmitido, pt.id as paisId, pt.pais as pais, e.segipId, e.oficialia, e.libro, e.partida, e.folio")
                ->innerJoin('SieAppWebBundle:GeneroTipo', 'gt', 'WITH', 'gt.id = e.generoTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltp', 'WITH', 'ltp.id = e.lugarProvNacTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo', 'ltd', 'WITH', 'ltd.id = ltp.lugarTipo')
                ->leftJoin('SieAppWebBundle:PaisTipo', 'pt', 'WITH', 'pt.id = e.paisTipo')
                ->leftJoin('SieAppWebBundle:DepartamentoTipo', 'dt', 'WITH', 'dt.id = e.expedido')
                ->where('e.id = :id')
                ->setParameter('id', $id)
                ->orderBy('e.paterno, e.materno, e.nombre', 'ASC')
                ->getQuery();
        try {
            //return $query->getArrayResult();
            //dump($query->getResult());die;
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $exc) {
//echo $exc->getTraceAsString();
            return array();
        }
    }

    /**
     * 
     * @param Request $request
     * @return type
     */
    public function dipHumEstudianteBuscaAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        //get the value of post
        $form = $request->get('form');

        //get how old is the student
        // $tiempo = $this->tiempo_transcurrido($form['fnacimiento'], '30-6-2015');
        // $yearStudent = $tiempo[0];
        // $form['yearStudent'] = $form['gestion'];

        //build the information on the view
        return $this->render($this->session->get('pathSystem') . ':Homologacion:dipHumListaEstudiante.html.twig', array(
            'samestudents' => $this->getCoincidenciasStudent($form),
            'formNuevoEstudiante' => $this->nobodyform($form)->createView()
        ));
    }

    /**
     * Lists all Estudiante entities.
     *
     */
    public function dipHumEstudianteNuevoAction(Request $request) {
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = $fechaActual->format('Y');

        $em = $this->getDoctrine()->getManager();

        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $getData = $request->get('form');
        $form = unserialize($getData['fullInfo']);

        $rolId = 16;

        $documentoController = new documentoController();
        $documentoController->setContainer($this->container);
        $codigoDepartamento = $documentoController->getCodigoLugarRol($id_usuario,8);
        if($codigoDepartamento != 0){
            $form['institucionEducativa'] = '';
            $form['institucionEducativaName'] = '';
        } else {
            $codigoDepartamento = $documentoController->getCodigoLugarRol($id_usuario,$rolId);
            $entityInstitucionEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $codigoDepartamento));
            $form['institucionEducativa'] = $entityInstitucionEducativa->getId();;
            $form['institucionEducativaName'] = $entityInstitucionEducativa->getInstitucioneducativa();
        }        
        
        $form['gestion'] = $gestionActual;
             
        return $this->render($this->session->get('pathSystem') . ':Homologacion:dipHumEstudianteNuevo.html.twig', array(
            'formDatoPersonal' => $this->createEstudianteForm($form)->createView(),
            'formDatoAcademico' => $this->createAcademicoForm($form)->createView()
        ));
    }    
    
    /**
     * Lists all Estudiante entities.
     *
     */
    public function dipHumEstudianteSeleccionadoAction(Request $request) {
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = $fechaActual->format('Y');

        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $estudianteId = base64_decode($request->get('est'));

        $rolId = 16;

        $em = $this->getDoctrine()->getManager();

        $documentoController = new documentoController();
        $documentoController->setContainer($this->container);
        $codigoDepartamento = $documentoController->getCodigoLugarRol($id_usuario,8);
        if($codigoDepartamento != 0){
            $form['institucionEducativa'] = '';
            $form['institucionEducativaName'] = '';
        } else {
            $codigoDepartamento = $documentoController->getCodigoLugarRol($id_usuario,$rolId);
            $entityInstitucionEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $codigoDepartamento));
            $form['institucionEducativa'] = $entityInstitucionEducativa->getId();;
            $form['institucionEducativaName'] = $entityInstitucionEducativa->getInstitucioneducativa();
        }        
        
        $form['gestion'] = $gestionActual;
        $entidadEstudiante = $this->getStudent($estudianteId);
        if(count($entidadEstudiante) > 0){
            $entidadEstudiante = $entidadEstudiante[0];
        } else {
            $entidadEstudiante = array();
        }
             
        return $this->render($this->session->get('pathSystem') . ':Homologacion:dipHumEstudianteSeleccionado.html.twig', array(
            'formDatoPersonal' => $entidadEstudiante,
            'formDatoAcademico' => $this->createAcademicoForm($form)->createView()
        ));
    }  

    /**
     * Lists all Estudiante entities.
     *
     */
    public function dipHumGestionCambiaAction(Request $request) {
        $gestion = $request->get('ges');
        $em = $this->getDoctrine()->getManager();

        $grado = ($gestion <= 2011) ? 4 : 6;
        $entity = $em->getRepository('SieAppWebBundle:GradoTipo');
        $query = $entity->createQueryBuilder('gt')               
                ->where('gt.id = :id')
                ->setParameter('id', $grado)
                ->distinct()
                ->orderBy('gt.id', 'ASC')
                ->getQuery();
        $aGrados = $query->getResult();  
        foreach ($aGrados as $grado) {
            $agrados[] = array('id'=>$grado->getId(),'grado'=>$grado->getGrado());
        }

        $nivel = ($gestion <= 2011) ? 3 : 13;
        $entity = $em->getRepository('SieAppWebBundle:NivelTipo');
        $query = $entity->createQueryBuilder('nt')               
                ->where('nt.id = :id')
                ->setParameter('id', $nivel)
                ->distinct()
                ->orderBy('nt.id', 'ASC')
                ->getQuery();
        $aNiveles = $query->getResult();  
        foreach ($aNiveles as $nivel) {
            $aniveles[] = array('id'=>$nivel->getId(),'nivel'=>$nivel->getNivel());
        }
        //dump(array('grados' => $agrados));die; 
        $response = new JsonResponse();
        return $response->setData(array('grados' => $agrados, 'niveles' => $aniveles));
    }

    /**
    * Creates a form to search the users of student selected
    *
    * @param mixed $id The entity id
    *
    * @return \Symfony\Component\Form\Form The form
    */
   private function createEstudianteForm($form) {
        $em = $this->getDoctrine()->getManager();
        $estudiante = new Estudiante();
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('tramite_homologacion_diploma_humanistico_estudiante_guarda'))      
                ->add('ci', 'text', array('label' => 'CI', 'mapped' => false, 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{5,10}', 'maxlength' => '10')))
                ->add('complemento', 'text', array('required' => false, 'mapped' => false, 'label' => 'Complemento', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que la expedicion de su CI, por favor no coloque abreviaturas de departamentos", 'maxlength' => '2')))
                ->add('expedido', 'entity', array('label' => 'Expedido', 'attr' => array('class' => 'form-control'),
                    'mapped' => false, 'class' => 'SieAppWebBundle:DepartamentoTipo', 
                    'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('dt')
                            ->orderBy('dt.id', 'ASC');
                    }, 'property' => 'departamento',
                    'data' => $em->getReference("SieAppWebBundle:DepartamentoTipo", '0')
                ))
                ->add('pasaporte', 'text', array('label' => 'Pasaporte', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => '', 'style' => 'text-transform:uppercase', 'placeholder' => 'Pasaporte' , 'maxlength' => 20, 'required' => true, 'class' => 'form-control')))
                ->add('generoTipo', 'entity', array('label' => 'Género', 'attr' => array('class' => 'form-control'),
                    'mapped' => false, 'class' => 'SieAppWebBundle:GeneroTipo',
                    'query_builder' => function (EntityRepository $e) {
                return $e->createQueryBuilder('g')
                        ->where('g.id != :id')
                        ->setParameter('id', '3');
            }, 'property' => 'genero'
                ))
                ->add('paterno', 'text', array('label' => 'Paterno', 'data' => $form['paterno'], 'mapped' => false, 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,45}', 'style' => 'text-transform:uppercase')))
                ->add('materno', 'text', array('label' => 'Materno', 'data' => $form['materno'], 'mapped' => false, 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,45}', 'style' => 'text-transform:uppercase')))
                ->add('nombre', 'text', array('label' => 'Nombre', 'data' => $form['nombre'], 'mapped' => false, 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,40}', 'style' => 'text-transform:uppercase')))
                //->add('fnacimiento', 'text', array('mapped' => false, 'required' => true, 'label' => 'Fecha Nacimiento', 'attr' => array('class' => 'form-control')))
                ->add('fnacimiento', 'text', array('mapped' => false, 'label' => 'Fecha Nacimiento', 'attr' => array('class' => 'form-control', 'placeholder'=>'DD-MM-YYYY', 'title'=>'DD-MM-YYYY', 'required'=>true, 'pattern'=>'[0-9]{2}-[0-9]{2}-[0-9]{4}')))
                ->add('pais', 'entity', array('label' => 'Pais', 'attr' => array('class' => 'form-control'),
                    'mapped' => false, 'class' => 'SieAppWebBundle:PaisTipo',
                    'query_builder' => function (EntityRepository $e) {
                return $e->createQueryBuilder('pt')
                        ->orderBy('pt.id', 'ASC');
            }, 'property' => 'pais',
                ))
                ->add('departamento', 'entity', array('label' => 'Departamento', 'attr' => array('class' => 'form-control'),
                    'mapped' => false, 'class' => 'SieAppWebBundle:LugarTipo', 
                    'query_builder' => function (EntityRepository $e) {
                return $e->createQueryBuilder('lt')
                        ->where('lt.lugarNivel = :id')
                        ->setParameter('id', '90')
                        ->orderBy('lt.codigo', 'ASC');
            }, 'property' => 'lugar',
                    'data' => $em->getReference("SieAppWebBundle:LugarTipo", 11)
                ))
                ->add('provincia', 'entity', array('label' => 'Provincia', 'attr' => array('class' => 'form-control'),
                    'mapped' => false, 'class' => 'SieAppWebBundle:LugarTipo', 
                    'query_builder' => function (EntityRepository $e) {
                return $e->createQueryBuilder('lt')
                        ->where('lt.lugarNivel = :id')
                        ->andwhere('lt.lugarTipo = :idDepto')
                        ->setParameter('id', '2')
                        ->setParameter('idDepto', $this->lugarNac)
                        ->orderBy('lt.codigo', 'ASC')
                ;
            }, 'property' => 'lugar',
                    'data' => $em->getReference("SieAppWebBundle:LugarTipo", '90')
                ))
                ->add('localidad', 'text', array('mapped' => false, 'required' => false, 'label' => 'Localidad', 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase', 'pattern' => '[a-zñ A-ZÑ]{3,40}')))
                ->add('oficialia', 'text', array('mapped' => false, 'required' => false, 'label' => 'Oficialia No', 'attr' => array('class' => 'form-control')))
                ->add('libro', 'text', array('mapped' => false, 'required' => false, 'label' => 'Libro No', 'attr' => array('class' => 'form-control')))
                ->add('partida', 'text', array('mapped' => false, 'required' => false, 'label' => 'Partida No', 'attr' => array('class' => 'form-control')))
                ->add('folio', 'text', array('mapped' => false, 'required' => false, 'label' => 'Folio No', 'attr' => array('class' => 'form-control')))
                ->getForm();
        return $form;
    }

    /**
     * buil the inscription form 
     * @param type $aInscrip
     * @return type form
     */
    private function createAcademicoForm($form) {        
        if($form['institucionEducativa'] == ''){
            $disable = false;
        } else {
            $disable = true;
        }
        $em = $this->getDoctrine()->getManager();
        if($form['gestion'] >= 2011){
            $formOmitido = $this->createFormBuilder()
                    ->setAction($this->generateUrl('tramite_homologacion_diploma_humanistico_savenew'))              
                    ->add('gestion', 'entity', array('data' => $form['gestion'], 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('gt')
                                        ->where('gt.id > 2008')
                                        ->orderBy('gt.id', 'DESC');
                            },
                        ));
            if($form['institucionEducativa'] == ''){
                $formOmitido = $formOmitido->add('institucionEducativa', 'text', array('data' => str_pad($form['institucionEducativa'],8,"0",STR_PAD_LEFT), 'label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')));
            } else {
                $formOmitido = $formOmitido->add('institucionEducativa', 'text', array('data' => str_pad($form['institucionEducativa'],8,"0",STR_PAD_LEFT), 'label' => 'SIE', 'attr' => array('maxlength' => 8, 'readonly' => 'readonly', 'class' => 'form-control')));
            }
            
            $formOmitido = $formOmitido
                    ->add('institucionEducativaName', 'text', array('data' => $form['institucionEducativaName'], 'label' => 'Institución Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                    //->add('nivel', 'choice', array('attr' => array('class' => 'form-control', 'required' => true)))
                    ->add('nivel', 'entity', array('attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\NivelTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('nt')
                                        ->where('nt.id = 13')
                                        ->orderBy('nt.id', 'ASC');
                            },
                        ))
                    ->add('grado', 'entity', array('attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GradoTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('gt')
                                        ->where('gt.id = 6')
                                        ->orderBy('gt.id', 'ASC');
                            },
                        ))
                    ->add('paralelo', 'entity', array('attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\ParaleloTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('pt')
                                        ->where("pt.id = '1'")
                                        ->orderBy('pt.id', 'ASC');
                            },
                        ))
                    ->add('turno', 'entity', array('attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\TurnoTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('tt')
                                        ->where('tt.id = 1')
                                        ->orderBy('tt.id', 'ASC');
                            },
                        ))
                    ->add('documento', 'file', array('label' => 'Diploma (.png)', 'required' => true)) 
                    ->add('guardar', 'button', array('label' => 'Guardar'))
                    ->getForm();
        } else {
            $formOmitido = $this->createFormBuilder()
                    ->setAction($this->generateUrl('tramite_homologacion_diploma_humanistico_savenew'))              
                    ->add('gestion', 'entity', array('data' => $form['gestion'], 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('gt')
                                        ->where('gt.id > 2008')
                                        ->orderBy('gt.id', 'DESC');
                            },
                        ));
            if($form['institucionEducativa'] == ''){
                $formOmitido = $formOmitido->add('institucionEducativa', 'text', array('data' => str_pad($form['institucionEducativa'],8,"0",STR_PAD_LEFT), 'label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')));
            } else {
                $formOmitido = $formOmitido->add('institucionEducativa', 'text', array('data' => str_pad($form['institucionEducativa'],8,"0",STR_PAD_LEFT), 'label' => 'SIE', 'attr' => array('maxlength' => 8, 'readonly' => 'readonly', 'class' => 'form-control')));
            }
            
            $formOmitido = $formOmitido
                    ->add('institucionEducativaName', 'text', array('data' => $form['institucionEducativaName'], 'label' => 'Institución Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                    //->add('nivel', 'choice', array('attr' => array('class' => 'form-control', 'required' => true)))
                    ->add('nivel', 'entity', array('attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\NivelTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('nt')
                                        ->where('nt.id = 3')
                                        ->orderBy('nt.id', 'ASC');
                            },
                        ))
                    ->add('grado', 'entity', array('attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GradoTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('gt')
                                        ->where('gt.id = 4')
                                        ->orderBy('gt.id', 'ASC');
                            },
                        ))
                    ->add('paralelo', 'entity', array('attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\ParaleloTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('pt')
                                        ->where("pt.id = '1'")
                                        ->orderBy('pt.id', 'ASC');
                            },
                        ))
                    ->add('turno', 'entity', array('attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\TurnoTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('tt')
                                        ->where('tt.id = 1')
                                        ->orderBy('tt.id', 'ASC');
                            },
                        ))
                    ->add('documento', 'file', array('label' => 'Diploma (.png)', 'required' => true)) 
                    ->add('guardar', 'button', array('label' => 'Guardar'))
                    ->getForm();
        }    
        return $formOmitido;        
    }

    /**
     * create form to nobody
     * @param type $data
     * @return form to new inscription
     */
    private function nobodyform($data) {
        return $this->createFormBuilder()
                    ->setAction($this->generateUrl('tramite_homologacion_diploma_humanistico_new'))
                    ->add('fullInfo', 'hidden', array('data' => serialize($data), 'required' => false))
                    ->add('ninguno', 'button', array('label' => 'Nuevo Estudiante', 'attr' => array('class' => 'btn btn-warning btn-sm')))
                    ->getForm();
    }

    /**
     * build the form to new student
     * @param Request $request
     * @return type a form with the students data
     */
    public function newAction(Request $request) {

        $getData = $request->get('form');
        $data = unserialize($getData['fullInfo']);

        $data['rude'] = '';

        $formInscription = $this->createFormInscription($request->get('form'), '', $data['yearStudent']);
        return $this->render($this->session->get('pathSystem') . ':Homologacion:new.html.twig', array(
                    'newstudent' => $data,
                    'formInscription' => $formInscription->createView(),
                    'titulo' => 'Homologación',
                    'subtitulo' => 'Inscripción estudiante'
        ));
    }

    /**
     * buil the inscription form 
     * @param type $aInscrip
     * @return type form
     */
    private function createFormInscription($data, $rude, $yearStudent) {

        $em = $this->getDoctrine()->getManager();

        if($yearStudent >= 2011){
            return $formOmitido = $this->createFormBuilder()
                    ->setAction($this->generateUrl('tramite_homologacion_diploma_humanistico_savenew'))
                    ->add('institucionEducativa', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                    ->add('institucionEducativaName', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                    //->add('nivel', 'choice', array('attr' => array('class' => 'form-control', 'required' => true)))
                    ->add('nivel', 'entity', array('attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\NivelTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('nt')
                                        ->where('nt.id = 13')
                                        ->orderBy('nt.id', 'ASC');
                            },
                        ))
                    ->add('grado', 'entity', array('attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GradoTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('gt')
                                        ->where('gt.id = 6')
                                        ->orderBy('gt.id', 'ASC');
                            },
                        ))
                    ->add('paralelo', 'entity', array('attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\ParaleloTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('pt')
                                        ->where("pt.id = '1'")
                                        ->orderBy('pt.id', 'ASC');
                            },
                        ))
                    ->add('turno', 'entity', array('attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\TurnoTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('tt')
                                        ->where('tt.id = 1')
                                        ->orderBy('tt.id', 'ASC');
                            },
                        ))
                    ->add('newdata', 'hidden', array('data' => serialize($data)))
                    ->add('rude', 'hidden', array('data' => $rude, 'required' => false))
                    ->add('yearStudent', 'hidden', array('data' => $yearStudent, 'required' => false))
                    ->add('gestion', 'hidden', array('data' => $yearStudent, 'required' => false))
                    ->add('save', 'submit', array('label' => 'Guardar'))
                    ->getForm();
        } else {
            return $formOmitido = $this->createFormBuilder()
                    ->setAction($this->generateUrl('tramite_homologacion_diploma_humanistico_savenew'))
                    ->add('institucionEducativa', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                    ->add('institucionEducativaName', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                    //->add('nivel', 'choice', array('attr' => array('class' => 'form-control', 'required' => true)))
                    ->add('nivel', 'entity', array('attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\NivelTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('nt')
                                        ->where('nt.id = 3')
                                        ->orderBy('nt.id', 'ASC');
                            },
                        ))
                    ->add('grado', 'entity', array('attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GradoTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('gt')
                                        ->where('gt.id = 4')
                                        ->orderBy('gt.id', 'ASC');
                            },
                        ))
                    ->add('paralelo', 'entity', array('attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\ParaleloTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('pt')
                                        ->where("pt.id = '1'")
                                        ->orderBy('pt.id', 'ASC');
                            },
                        ))
                    ->add('turno', 'entity', array('attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\TurnoTipo',
                            'query_builder' => function(EntityRepository $er) {
                                return $er->createQueryBuilder('tt')
                                        ->where('tt.id = 1')
                                        ->orderBy('tt.id', 'ASC');
                            },
                        ))
                    ->add('newdata', 'hidden', array('data' => serialize($data)))
                    ->add('rude', 'hidden', array('data' => $rude, 'required' => false))
                    ->add('yearStudent', 'hidden', array('data' => $yearStudent, 'required' => false))
                    ->add('gestion', 'hidden', array('data' => $yearStudent, 'required' => false))
                    ->add('save', 'submit', array('label' => 'Guardar'))
                    ->getForm();
        }            
    }


    /**
     * todo the registration of extranjero
     * @param Request $request
     * 
     */
    public function dipHumEstudianteGuardaAction(Request $request) {
        $fechaActual = new \DateTime();
        $gestion = $fechaActual->format('Y');
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $form = $request->get('form');
            $files = $request->files->get('form');
            //$newStudent = unserialize($form['newdata']);
            if(!isset($form['est'])){
                $studentId = 0;
            } else {
                $studentId = base64_decode($form['est']);
            }

            $newStudent = $form;
            $response = new JsonResponse();

            if ($studentId == 0) {
                $digits = 4;
                $mat = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);
                $rude = $form['institucionEducativa'] . $form['gestion'] . $mat . $this->generarRude($form['institucionEducativa'] . $this->session->get('currentyear') . $mat);
                $gestion = $form['gestion'];
                //dump($newStudent['fnacimiento']);die;
                //look for the course to the student
                $fechaNacimiento = new \Datetime(date($newStudent['fnacimiento']));
                $objEstudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array(
                    'carnetIdentidad' => $newStudent['ci'],
                    'complemento' => strtoupper($newStudent['complemento']),
                    'nombre' => strtoupper($newStudent['nombre']),
                    'paterno' => strtoupper($newStudent['paterno']),
                    'materno' => strtoupper($newStudent['materno']),
                    'fechaNacimiento' => $fechaNacimiento,
                )); 

                if(count($objEstudiante) > 0){
                    $msg = 'El estudiante '.$newStudent['nombre'].' '.$newStudent['paterno'].' '.$newStudent['materno'].' ya existe, no puede registrarse nuevamente';
                    return $response->setData(array('estado' => false, 'msg' => $msg));
                }
                // $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante');");
                // $query->execute();

                $arrParametros = array('complemento'=>$newStudent['complemento'], 'primer_apellido'=>$newStudent['paterno'], 'segundo_apellido'=>$newStudent['materno'], 'nombre'=>$newStudent['nombre'], 'fecha_nacimiento'=>$newStudent['fnacimiento']);

                $answerSegip = false;
                if ($newStudent['ci'] > 0){
                    $answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($newStudent['ci'] ,$arrParametros, 'prod', 'academico');
                    if(!$answerSegip){
                        $msg = 'Datos del estudiante '.$newStudent['nombre'].' '.$newStudent['paterno'].' '.$newStudent['materno'].', no validados';
                        return $response->setData(array('estado' => false, 'msg' => $msg));
                    }
                }

                if ($newStudent['ci'] > 0){   
                    $entityExpedido = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneBy(array('id' => $newStudent['expedido']));
                } else {
                    $entityExpedido = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneBy(array('id' => 0));
                }
                $student = new Estudiante();
                $student->setPaterno(strtoupper($newStudent['paterno']));
                $student->setMaterno(strtoupper($newStudent['materno']));
                $student->setNombre(strtoupper($newStudent['nombre']));
                $student->setCarnetIdentidad($newStudent['ci']);
                $student->setComplemento($newStudent['complemento']);
                $student->setPasaporte($newStudent['pasaporte']);
                $student->setExpedido($entityExpedido);
                $student->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($newStudent['generoTipo']));
                $student->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($newStudent['pais']));
                if (isset($newStudent['provincia'])){
                    $student->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($newStudent['provincia']));
                } 
                $student->setFechaNacimiento(new \DateTime($newStudent['fnacimiento']));
                $student->setCodigoRude($rude);

                $em->persist($student);
                //$em->flush();
                $studentId = $student->getId();                 
            } else {
                $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('id' => $studentId));
                if(count($student)>0){
                    $newStudent['ci'] = $student->getCarnetIdentidad();
                    $newStudent['complemento'] = $student->getComplemento();
                    $newStudent['pasaporte'] = $student->getPasaporte();
                    $rude = $student->getCodigoRude();
                    // $segipId = $student->getSegipId();
                    // if ($segipId != 1){
                    //     $msg = 'Datos del estudiante '.strtoupper($newStudent['nombre'].' '.$newStudent['paterno'].' '.$newStudent['materno']).', no validados';
                    //     return $response->setData(array('estado' => false, 'msg' => $msg));
                    // } 
                }
            }
            
            $verIE = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find(intval($form['institucionEducativa']));
            if(count($verIE) == 0){
                $em->getConnection()->rollback();
                $msg = 'No existe la unidad educativa con código SIE: '.$form['institucionEducativa'];
                return $response->setData(array('estado' => false, 'msg' => $msg));
                //$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'notiiniprim', 'No existe la unidad educativa con cigo sie: '.$form['institucionEducativa']));
                //return $this->redirect($this->generateUrl('tramite_homologacion_diploma_humanistico_index'));
            }
            
            $verInscripcion = $this->verificaInscripcionGestionNivelGrago($newStudent['ci'],$newStudent['complemento'],$newStudent['pasaporte'],$form['nivel'],$form['grado'],$form['gestion']);            
            if ($verInscripcion){
                $em->getConnection()->rollback();
                $msg  = 'El Registro del estudiante en la gestión, nivel y grado seleccionado, ya existe';
                return $response->setData(array('estado' => false, 'msg' => $msg));
                //$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'notiiniprim', 'El Registro del estudiante en la gestión, nivel y grado seleccionado, ya existe ...'));
            }           
            
            $verInscripcion = $this->verificaInscripcionBachiller($newStudent['ci'],$newStudent['complemento'],$newStudent['pasaporte'],$form['nivel'],$form['grado'],$form['gestion']);           
            if ($verInscripcion){
                $em->getConnection()->rollback();
                $msg  = 'El Registro del estudiante como bachiller, ya existe';
                return $response->setData(array('estado' => false, 'msg' => $msg));
                //$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'notiiniprim', 'El Registro del estudiante en la gestión, nivel y grado seleccionado, ya existe ...'));
            } 

            if(isset($files['documento'])){
                $documento = $files['documento'];
            } else {
                $documento = null;
            }
            $file = $documento;

            if(!isset($file)){
                $em->getConnection()->rollback();
                $msg = 'No adjunto la libreta de '.$grados[$i].'° año de escolaridad';
                return $response->setData(array('estado' => false, 'msg' => $msg));
            }

            if(null == $file){
                $em->getConnection()->rollback();
                $msg = 'No adjunto la libreta de '.$grados[$i].'° año de escolaridad.';
                return $response->setData(array('estado' => false, 'msg' => $msg));
            }  
                      
            if(!in_array($file->guessExtension(), array('jpg','jpeg','png','bmp'))){
                $em->getConnection()->rollback();
                $msg  = 'Formato de la imagen no permitido, intente nuevamente con aquellos permitidos (.jpg, .jpeg, .png, .bmp)';
                return $response->setData(array('estado' => false, 'msg' => $msg));
            }

            $filesize = $file->getClientSize();
            if ($filesize/1024 > 5120) {
                $em->getConnection()->rollback();
                $msg  = 'La fotografía adjunta '.$file->getClientOriginalName().' excede el tamaño permitido, Fotografia muy grande, favor ingresar una fotografía que no exceda los 5MB.';
                return $response->setData(array('estado' => false, 'msg' => $msg));
            } 

            //look for the course to the student
            $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                'nivelTipo' => $form['nivel'],
                'gradoTipo' => $form['grado'],
                'paraleloTipo' => $form['paralelo'],
                'turnoTipo' => $form['turno'],
                'institucioneducativa' => $form['institucionEducativa'],
                'gestionTipo' => $form['gestion']
            )); 
            
            if (count($objCurso) == 0){
                $studentInstitucioneducativaCurso = new InstitucioneducativaCurso();
                $studentInstitucioneducativaCurso->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->find(3));
                $studentInstitucioneducativaCurso->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($form['gestion']));                    
                $studentInstitucioneducativaCurso->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($form['grado']));                    
                $studentInstitucioneducativaCurso->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
                $studentInstitucioneducativaCurso->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find($form['nivel']));
                $studentInstitucioneducativaCurso->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->find($form['paralelo']));
                $studentInstitucioneducativaCurso->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find(1));
                $studentInstitucioneducativaCurso->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->find(0));
                $studentInstitucioneducativaCurso->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->find($form['turno']));                    
                $studentInstitucioneducativaCurso->setMultigrado(0);                  
                $studentInstitucioneducativaCurso->setDesayunoEscolar(0); 
                $em->persist($studentInstitucioneducativaCurso);
                //$em->flush();   
                
                //look for the course to the student
                $objCurso = $studentInstitucioneducativaCurso;
                // $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                //     'nivelTipo' => $form['nivel'],
                //     'gradoTipo' => $form['grado'],
                //     'paraleloTipo' => $form['paralelo'],
                //     'turnoTipo' => $form['turno'],
                //     'institucioneducativa' => $form['institucionEducativa'],
                //     'gestionTipo' => $form['gestion']
                // )); 
            }

            //insert a new record with the new selected variables and put matriculaFinID like 4
            // $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            // $query->execute();

            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($form['gestion']));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(5));
            $studentInscription->setEstudiante($student);
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime('now'));
            $studentInscription->setFechaRegistro(new \DateTime('now'));
            $studentInscription->setInstitucioneducativaCurso($objCurso);
            $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(45));
            $studentInscription->setCodUeProcedenciaId(0);
            $em->persist($studentInscription);

            $filename = "";
            $filename = $rude.'_DiplomaDeBachillerExtranjero_'.$gestion.'_'.$studentInscription->getId().'.'.$file->guessExtension();
            $adjuntoDir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/documento_estudiante/'.$rude.'/';   
            $file->move($adjuntoDir, $filename);

            if (!file_exists($adjuntoDir.'/'.$filename)){
                $em->getConnection()->rollback();
                $msg  = 'La fotografía ('.$file->getClientOriginalName().') del '.$grados[$i].'° año de escolaridad, no fue registrada.';
                return $response->setData(array('estado' => false, 'msg' => $msg));
            }  
           
            $entidadDocumentoTipo = $em->getRepository('SieAppWebBundle:DocumentoTipo')->find(1);
            $studentInscriptionDocumento = new EstudianteInscripcionDocumento();
            $studentInscriptionDocumento->setEstudianteInscripcion($studentInscription);
            $studentInscriptionDocumento->setDocumentoTipo($entidadDocumentoTipo);      
            $studentInscriptionDocumento->setRutaImagen($rude.'/'.$filename);              
            $studentInscriptionDocumento->setObservacion('Convalidación de libretas para emision de Diploma de Bachiller');
            $em->persist($studentInscriptionDocumento); 

            $em->flush();
            //do the commit of DB
            $em->getConnection()->commit();
            // $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => 'El Registro de homologación del estudiante fue correcto ...'));
            $msg  = 'Homologación de Diploma Extranjero registrado correctamente, ahora puede emitir su diploma de bachiler de forma regular';
            return $response->setData(array('estado' => true, 'msg' => $msg));
            //return $this->redirect($this->generateUrl('tramite_homologacion_diploma_humanistico_index'));

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $msg  = 'Error al realizar el registro del estudiante, intente nuevamente';
            return $response->setData(array('estado' => false, 'msg' => $msg));
        }
    }
    
    /*
     * verify inscripcion student
     * @param type $cadena
     * @return bool
     */
    private function verificaInscripcionGestionNivelGrago($ci,$complemento,$pasaporte,$nivel,$grado,$gestion){
        $em = $this->getDoctrine()->getManager();
        /*
         * Verifica si la Unidad Educativa puede otorgar diplomas de bachiller o registrar para titulos tecnico medio alternativa
         */
        $query = $em->getConnection()->prepare("
                select * from estudiante as e
                inner join estudiante_inscripcion as ei on ei.estudiante_id = e.id
                inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                where case :ci::varchar when '' then e.pasaporte = :pasaporte when '0' then e.pasaporte = :pasaporte else e.carnet_identidad = :ci::varchar and e.complemento = :complemento::varchar end and iec.gestion_tipo_id = :gestion::int and iec.nivel_tipo_id = :nivel::int and iec.grado_tipo_id = :grado::int     
                and ei.estadomatricula_tipo_id in (4,5,55,11)
                ");
        $query->bindValue(':ci', $ci);
        $query->bindValue(':pasaporte', $pasaporte);
        $query->bindValue(':complemento', $complemento);
        $query->bindValue(':nivel', $nivel);
        $query->bindValue(':grado', $grado);
        $query->bindValue(':gestion', $gestion);
        $query->execute();
        $entity = $query->fetchAll();
        
        if (count($entity)>0){
            return true;
        } else {
            return false;
        }   
    }
    
    /*
     * verify inscripcion student
     * @param type $cadena
     * @return bool
     */
    private function verificaInscripcionBachiller($ci,$complemento,$pasaporte,$nivel,$grado,$gestion){
        $em = $this->getDoctrine()->getManager();
        /*
         * Verifica si la Unidad Educativa puede otorgar diplomas de bachiller o registrar para titulos tecnico medio alternativa
         */
        $query = $em->getConnection()->prepare("
                select * from estudiante as e
                inner join estudiante_inscripcion as ei on ei.estudiante_id = e.id
                inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                where case :ci::varchar when '' then e.pasaporte = :pasaporte when '0' then e.pasaporte = :pasaporte else e.carnet_identidad = :ci::varchar and e.complemento = :complemento::varchar end 
                and case when iec.gestion_tipo_id <= :gestion then iec.nivel_tipo_id = 3::int and iec.grado_tipo_id = 4::int else iec.nivel_tipo_id = 13::int and iec.grado_tipo_id = 6::int end    
            ");
        $query->bindValue(':ci', $ci);
        $query->bindValue(':pasaporte', $pasaporte);
        $query->bindValue(':complemento', $complemento);
        $query->bindValue(':gestion', $gestion);
        $query->execute();
        $entity = $query->fetchAll();
        
        if (count($entity)>0){
            return true;
        } else {
            return false;
        }   
    }

    /**
     * generate the new rude to the new student
     * @param type $cadena
     * @return type
     */
    private function generarRude($cadena) {
        $codigoRude = "";
        $codigoVerificacion = "123456789A0";
        $peso = 2;
        $sum = 0;
        $int = 0;
        while ($int < strlen($cadena)) {
            if ($peso == 7)
                $peso = 2;
            $sum = $sum + ($peso * ord(substr($cadena, $int, 1)));
            $peso = $peso + 1;
            $int = $int + 1;
        }
        return substr($codigoVerificacion, 10 - ($sum % 11), 1);
    }

    /**
     * get the nivel and grado
     * @param type $nivel
     * @param type $grado
     */
    public function getInfoInscriptionStudent($nivel, $grado) {

        $sw = 1;
        $cursos = $this->aCursos;
        while (($acourses = current($cursos)) !== FALSE && $sw) {
            $anivel = explode("-", current($cursos));
            if ($anivel[0] == $nivel && $anivel[2] == $grado) {
                $inscriptionInfo = $anivel;
                $sw = 0;
            }
            next($cursos);
        }
        return ($inscriptionInfo);
    }

    public function existAction(Request $request) {
        try {
            //create the connection to DB
            $em = $this->getDoctrine()->getManager();
            //get values send by post
            $idStudent = $request->get('id');
            $codigoRude = $request->get('codigoRude');
            $gestion = $request->get('gestion');

            //look for inscription for this idStudent
            $studentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $query = $studentInscription->createQueryBuilder('ei')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                    ->where('ei.estudiante = :idStudent')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->andwhere('ei.estadomatriculaTipo = :mat')
                    ->setParameter('idStudent', $idStudent)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('mat', '4')
                    ->getQuery();
            $objStudentInscription = $query->getResult();
            //validate if the student has an inscription
            if ($objStudentInscription) {
                $this->addFlash('notiiniprim', 'El Estudiante ya cuenta con inscripción para la gestión ' . $gestion);
                return $this->redirectToRoute('tramite_homologacion_diploma_humanistico_index');
            }
            //get info about the student
            $student = $em->getRepository('SieAppWebBundle:Estudiante');
            $query = $student->createQueryBuilder('e')
                    ->select('e.paterno as paterno, e.materno as materno , e.nombre as nombre, e.fechaNacimiento as fnacimiento, e.carnetIdentidad as ci, e.complemento as complemento, IDENTITY(e.generoTipo) as generoTipo, e.codigoRude as rude ')
                    ->where('e.id = :idStudent')
                    ->setParameter('idStudent', $idStudent)
                    ->getQuery();
            $data = $query->getResult();
            //get the bith daty of student
            foreach ($data[0]['fnacimiento'] as $odata) {
                $sbirthdate = $odata;
                break;
            }
            //get how old is the student
            $sbday = explode(' ', $sbirthdate);
            list ($year, $month, $day) = explode('-', $sbday[0]);
            $tiempo = $this->tiempo_transcurrido($day . '-' . $month . '-' . $year, '30-6-2015');
            $yearStudent = $tiempo[0];
            $data[0]['yearStudent'] = $yearStudent;
            //build the form to the inscription            
            $formInscription = $this->createFormInscription($request->get('form'), $data[0]['rude'], $gestion);
            return $this->render($this->session->get('pathSystem') . ':Homologacion:new.html.twig', array(
                        'newstudent' => $data[0],
                        'formInscription' => $formInscription->createView(),
                        'titulo' => 'Homologación',
                        'subtitulo' => 'Inscripción estudiante'
            ));
        } catch (Exception $ex) {
            
        }
    }

    /**
     * get the paralelto, turno and sie name
     * @param type $id like sie
     * @param type $n like nivel
     * @param type $g like grado
     * @return type
     */
    public function findIEAction($ges,$id) {
        $em = $this->getDoctrine()->getManager();
        //get the tuicion
        //select * from get_ue_tuicion(137746,82480002)
        /*
          $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT)');
          $query->bindValue(':user_id', $this->session->get('userId'));
          $query->bindValue(':sie', $id);
          $query->execute();
          $aTuicion = $query->fetchAll();
         */
        $aniveles = array();
        // if ($aTuicion[0]['get_ue_tuicion']) {
        //get the IE
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
        $nombreIE = ($institucion) ? $institucion->getInstitucioneducativa() : "Unidad Educativa no existe";
        $em = $this->getDoctrine()->getManager();
        //get the Niveles

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.nivelTipo)')
                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('iec.institucioneducativa = :sie')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $id)
                ->setParameter('nivel', '13')
                ->setParameter('gestion', $ges)
                ->distinct()
                ->getQuery();
        $aNiveles = $query->getResult();
        foreach ($aNiveles as $nivel) {
            //$aniveles[$nivel[1]] = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel[1])->getNivel();
            $aniveles[$nivel[1]] = $em->getRepository('SieAppWebBundle:NivelTipo')->find(13)->getNivel();
        }
        
        $aniveles[13] = $em->getRepository('SieAppWebBundle:NivelTipo')->find(13)->getNivel();

        /*     } else {
          $nombreIE = 'No tiene Tuición';
          } */

        $response = new JsonResponse();

        return $response->setData(array('nombre' => $nombreIE, 'aniveles' => $aniveles));
    }

    /**
     * get grado
     * @param type $idnivel
     * @param type $sie
     * return list of grados
     */
    public function findgradoAction($idnivel, $ges, $sie) {
        $em = $this->getDoctrine()->getManager();
        //get grado
        $grados = ($idnivel == 13) ? array(6) : array(6);
        $agrados = array();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.gradoTipo)')
                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :idnivel')
                ->andwhere('iec.gradoTipo IN (:grados)')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('idnivel', $idnivel)
                ->setParameter('grados', $grados)
                ->setParameter('gestion', $ges)
                ->distinct()
                ->orderBy('iec.gradoTipo', 'ASC')
                ->getQuery();
        $aGrados = $query->getResult();
        foreach ($aGrados as $grado) {
            $agrados[$grado[1]] = $em->getRepository('SieAppWebBundle:GradoTipo')->find($grado[1])->getGrado();
        }
        
        $response = new JsonResponse();
        return $response->setData(array('agrados' => $agrados));
    }

    /**
     * get the paralelos
     * @param type $idnivel
     * @param type $sie
     * @return type
     */
    public function findparaleloAction($grado, $ges, $sie, $nivel) {
        $em = $this->getDoctrine()->getManager();
//get grado
        $aparalelos = array();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.paraleloTipo)')
                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('gestion', $ges)
                ->distinct()
                ->orderBy('iec.paraleloTipo', 'ASC')
                ->getQuery();
        $aParalelos = $query->getResult();
        foreach ($aParalelos as $paralelo) {
            $aparalelos[$paralelo[1]] = $em->getRepository('SieAppWebBundle:ParaleloTipo')->find($paralelo[1])->getParalelo();
        }

        $response = new JsonResponse();
        return $response->setData(array('aparalelos' => $aparalelos));
    }

    /**
     * get the turnos
     * @param type $turno
     * @param type $sie
     * @param type $nivel
     * @param type $grado
     * @return type
     */
    public function findturnoAction($paralelo, $ges, $sie, $nivel, $grado) {
        $em = $this->getDoctrine()->getManager();
//get grado
        $aturnos = array();

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.turnoTipo)')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andWhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('gestion', $ges)
                ->distinct()
                ->orderBy('iec.turnoTipo', 'ASC')
                ->getQuery();
        $aTurnos = $query->getResult();
        foreach ($aTurnos as $turno) {
            $aturnos[$turno[1]] = $em->getRepository('SieAppWebBundle:TurnoTipo')->find($turno[1])->getTurno();
        }

        $response = new JsonResponse();
        return $response->setData(array('aturnos' => $aturnos));
    }

    ///get the year of student

    function tiempo_transcurrido($fecha_nacimiento, $fecha_control) {
        $fecha_actual = $fecha_control;

        if (!strlen($fecha_actual)) {
            $fecha_actual = date('d-m-Y');
        }

        // separamos en partes las fechas 
        $array_nacimiento = explode("-", $fecha_nacimiento);
        $array_actual = explode("-", $fecha_actual);
        //dump($array_nacimiento);dump($array_actual);die;

        $anos = $array_actual[2] - $array_nacimiento[2]; // calculamos años 
        $meses = $array_actual[1] - $array_nacimiento[1]; // calculamos meses 
        $dias = $array_actual[0] - $array_nacimiento[0]; // calculamos días 
        //ajuste de posible negativo en $días 
        if ($dias < 0) {
            --$meses;

            //ahora hay que sumar a $dias los dias que tiene el mes anterior de la fecha actual 
            switch ($array_actual[1]) {
                case 1:
                    $dias_mes_anterior = 31;
                    break;
                case 2:
                    $dias_mes_anterior = 31;
                    break;
                case 3:
                    if (bisiesto($array_actual[2])) {
                        $dias_mes_anterior = 29;
                        break;
                    } else {
                        $dias_mes_anterior = 28;
                        break;
                    }
                case 4:
                    $dias_mes_anterior = 31;
                    break;
                case 5:
                    $dias_mes_anterior = 30;
                    break;
                case 6:
                    $dias_mes_anterior = 31;
                    break;
                case 7:
                    $dias_mes_anterior = 30;
                    break;
                case 8:
                    $dias_mes_anterior = 31;
                    break;
                case 9:
                    $dias_mes_anterior = 31;
                    break;
                case 10:
                    $dias_mes_anterior = 30;
                    break;
                case 11:
                    $dias_mes_anterior = 31;
                    break;
                case 12:
                    $dias_mes_anterior = 30;
                    break;
            }

            $dias = $dias + $dias_mes_anterior;

            if ($dias < 0) {
                --$meses;
                if ($dias == -1) {
                    $dias = 30;
                }
                if ($dias == -2) {
                    $dias = 29;
                }
            }
        }

        //ajuste de posible negativo en $meses 
        if ($meses < 0) {
            --$anos;
            $meses = $meses + 12;
        }

        $tiempo[0] = $anos;
        $tiempo[1] = $meses;
        $tiempo[2] = $dias;

        return $tiempo;
    }

    function bisiesto($anio_actual) {
        $bisiesto = false;
        //probamos si el mes de febrero del año actual tiene 29 días 
        if (checkdate(2, 29, $anio_actual)) {
            $bisiesto = true;
        }
        return $bisiesto;
        
    }

}
