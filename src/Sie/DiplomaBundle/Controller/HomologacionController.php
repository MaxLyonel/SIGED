<?php

namespace Sie\DiplomaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\PaisTipo;
use Sie\AppWebBundle\Form\EstudianteType;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Estudiante controller.
 *
 */
class HomologacionController extends Controller {

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
    public function indexAction() {
    //die('krlos');
        $em = $this->getDoctrine()->getManager();

        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':Homologacion:index.html.twig', array(
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
        $em = $this->getDoctrine()->getManager();
        $estudiante = new Estudiante();

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('sie_diploma_homologacion_result'))                
                ->add('gestion', 'entity', array('data' => '', 'attr' => array('class' => 'form-control'), 'class' => 'Sie\AppWebBundle\Entity\GestionTipo',
                        'query_builder' => function(EntityRepository $er) {
                            return $er->createQueryBuilder('gt')
                                    ->where('gt.id > 2008')
                                    ->orderBy('gt.id', 'DESC');
                        },
                    ))
                ->add('ci', 'text', array('label' => 'CI', 'mapped' => false, 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{5,10}', 'maxlength' => '10')))
                ->add('complemento', 'text', array('required' => false, 'mapped' => false, 'label' => 'Complemento', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que la expedicion de su CI, por favor no coloque abreviaturas de departamentos", 'maxlength' => '2')))
                ->add('generoTipo', 'entity', array('label' => 'Género', 'attr' => array('class' => 'form-control'),
                    'mapped' => false, 'class' => 'SieAppWebBundle:GeneroTipo',
                    'query_builder' => function (EntityRepository $e) {
                return $e->createQueryBuilder('g')
                        ->where('g.id != :id')
                        ->setParameter('id', '3');
            }, 'property' => 'genero'
                ))
                ->add('paterno', 'text', array('label' => 'Paterno', 'mapped' => false, 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,45}', 'style' => 'text-transform:uppercase')))
                ->add('materno', 'text', array('label' => 'Materno', 'mapped' => false, 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,45}', 'style' => 'text-transform:uppercase')))
                ->add('nombre', 'text', array('label' => 'Nombre', 'mapped' => false, 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,40}', 'style' => 'text-transform:uppercase')))
                //->add('fnacimiento', 'text', array('mapped' => false, 'required' => true, 'label' => 'Fecha Nacimiento', 'attr' => array('class' => 'form-control')))
                ->add('fnacimiento', 'text', array('mapped' => false, 'label' => 'Fecha Nacimiento', 'attr' => array('class' => 'form-control')))
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
                ->add('buscar', 'submit', array('label' => 'Buscar'))
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
                ->where('e.paterno like :paterno')
                ->andWhere('e.materno like :materno')
                ->andWhere('e.nombre like :nombre')
                ->setParameter('paterno', strtoupper($data['paterno']) . '%')
                ->setParameter('materno', strtoupper($data['materno']) . '%')
                ->setParameter('nombre', strtoupper($data['nombre']) . '%')
                ->orderBy('e.paterno, e.materno, e.nombre', 'ASC')
                ->getQuery();


        try {
            return $query->getArrayResult();
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
    public function resultAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        //get the value of post
        $form = $request->get('form');

        //get how old is the student
        $tiempo = $this->tiempo_transcurrido($form['fnacimiento'], '30-6-2015');
        $yearStudent = $tiempo[0];
        $form['yearStudent'] = $form['gestion'];

        //build the information on the view
        $this->session->getFlashBag()->add('notiiniprimaria', 'Resultado de la busqueda');
        return $this->render($this->session->get('pathSystem') . ':Homologacion:samestudents.html.twig', array(
                    'samestudents' => $this->getCoincidenciasStudent($form),
                    'formninguno' => $this->nobodyform($form)->createView(),
                    'gestion' => $form['gestion']
        ));
    }

    /**
     * create form to nobody
     * @param type $data
     * @return form to new inscription
     */
    private function nobodyform($data) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('sie_diploma_homologacion_new'))
                        ->add('fullInfo', 'hidden', array('data' => serialize($data), 'required' => false))
                        ->add('ninguno', 'submit', array('label' => 'Inscripción Nuevo', 'attr' => array('class' => 'btn btn-warning btn-sm btn-block')))
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
                    'formInscription' => $formInscription->createView()
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
                    ->setAction($this->generateUrl('sie_diploma_homologacion_savenew'))
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
                                        ->orWhere('gt.id = 3')
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
                    ->setAction($this->generateUrl('sie_diploma_homologacion_savenew'))
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
                                        ->orWhere('gt.id = 2')
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
    public function savenewAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $form = $request->get('form');
            
            $newStudent = unserialize($form['newdata']);
            $newStudent = unserialize($newStudent['fullInfo']);
            //if doesn't exist the rude we generate the rude
            if (!$form['rude']) {
                $digits = 4;
                $mat = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);
                $rude = $form['institucionEducativa'] . $form['gestion'] . $mat . $this->generarRude($form['institucionEducativa'] . $this->session->get('currentyear') . $mat);
                

                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante');");
                $query->execute();

                $student = new Estudiante();
                $student->setPaterno(strtoupper($newStudent['paterno']));
                $student->setMaterno(strtoupper($newStudent['materno']));
                $student->setNombre(strtoupper($newStudent['nombre']));
                $student->setCarnetIdentidad($newStudent['ci']);
                $student->setComplemento($newStudent['complemento']);
                $student->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($newStudent['generoTipo']));
                $student->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($newStudent['pais']));
                if (isset($newStudent['provincia'])){
                    $student->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($newStudent['provincia']));
                } 
                $student->setFechaNacimiento(new \DateTime($newStudent['fnacimiento']));
                $student->setCodigoRude($rude);

                $em->persist($student);
                $em->flush();
                $studentId = $student->getId();
            } else {
                //if the student has a rude, get the id student
                $studentId = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['rude']))->getId();
            }
            $verInscripcion = $this->verificaInscripcionGestionNivelGrago($newStudent['ci'],$newStudent['complemento'],$form['nivel'],$form['grado'],$form['gestion']);
            $verIE = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']);

            if(count($verIE) == 0){
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->add('notiiniprim', 'No existe la unidad educativa con cigo sie: '.$form['institucionEducativa']);
                return $this->redirect($this->generateUrl('sie_diploma_homologacion_index'));
            }
            
            if ($verInscripcion){
                $em->getConnection()->rollback();
                $this->session->getFlashBag()->add('notiiniprim', 'El Registro del estudiante en la gestión, nivel y grado seleccionado, ya existe ...');
            } else {
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
                    $em->flush();   
                    
                    //look for the course to the student
                    $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                        'nivelTipo' => $form['nivel'],
                        'gradoTipo' => $form['grado'],
                        'paraleloTipo' => $form['paralelo'],
                        'turnoTipo' => $form['turno'],
                        'institucioneducativa' => $form['institucionEducativa'],
                        'gestionTipo' => $form['gestion']
                    )); 
                }

                //insert a new record with the new selected variables and put matriculaFinID like 4
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
                $query->execute();
                $studentInscription = new EstudianteInscripcion();
                $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
                $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($form['gestion']));
                $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
                $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($studentId));
                $studentInscription->setObservacion(1);
                $studentInscription->setFechaInscripcion(new \DateTime('now'));
                $studentInscription->setFechaRegistro(new \DateTime('now'));
                $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objCurso->getId()));
                $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(59));
                $studentInscription->setCodUeProcedenciaId(0);
                $em->persist($studentInscription);
                $em->flush();
                //do the commit of DB
                $em->getConnection()->commit();
                $this->session->getFlashBag()->add('goodiniprim', 'El Registro de homologación del estudiante fue correcto ...');
            }
            return $this->redirect($this->generateUrl('sie_diploma_homologacion_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            return $ex;
        }
    }
    
    /*
     * verify inscripcion student
     * @param type $cadena
     * @return bool
     */
    private function verificaInscripcionGestionNivelGrago($ci,$complemento,$nivel,$grado,$gestion){
        $em = $this->getDoctrine()->getManager();
        /*
         * Verifica si la Unidad Educativa puede otorgar diplomas de bachiller o registrar para titulos tecnico medio alternativa
         */
        $query = $em->getConnection()->prepare("
                select * from estudiante as e
                inner join estudiante_inscripcion as ei on ei.estudiante_id = e.id
                inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                where e.carnet_identidad = :ci::varchar and e.complemento = :complemento::varchar and iec.gestion_tipo_id = :gestion::int and iec.nivel_tipo_id = :nivel::int and iec.grado_tipo_id = :grado::int     
                ");
        $query->bindValue(':ci', $ci);
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
                return $this->redirectToRoute('sie_diploma_homologacion_index');
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
                        'formInscription' => $formInscription->createView()
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
