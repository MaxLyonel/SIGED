<?php

namespace Sie\AppWebBundle\Controller;

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
class InscriptionExtranjerosController extends Controller {

    private $session;
    public $lugarNac;
    public $dpto;
    public $aCursos;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $this->aCursos = $this->fillCursos();
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
        return $this->render('SieAppWebBundle:InscriptionExtranjeros:index.html.twig', array(
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
                ->setAction($this->generateUrl('inscription_extranjeros_result'))
                ->add('codigoRude', 'text', array('mapped' => false, 'label' => 'Rude', 'required' => true, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control')))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
//->add('public', 'checkbox', array('mapped'=>false,'label' => 'Show this entry publicly?', 'required' => false))
                ->getForm();
        return $form;
    }

    /**
     * build the new student extranjero
     * @return type
     */
    public function norudeAction() {
        return $this->render('SieAppWebBundle:InscriptionExtranjeros:norude.html.twig', array(
                    'form' => $this->formnorude()->createView(),
        ));
    }

    private function formnorude() {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('inscription_extranjeros_saveext'))
                        ->add('pais', 'entity', array('label' => 'País', 'class' => 'SieAppWebBundle:PaisTipo', 'attr' => array('class' => 'form-control')))
                        ->add('ueprocedencia', 'text', array('label' => 'Unidad Educativa Procedencia', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-Z]{3,25}')))
                        ->add('ci', 'text', array('required' => false, 'label' => 'CI', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{5,8}', 'required' => false)))
                        ->add('complemento', 'text', array('required' => false, 'label' => 'Complemento', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Z]{1,2}')))
                        ->add('paterno', 'text', array('required' => false, 'label' => 'Paterno', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-Z]{3,10}')))
                        ->add('materno', 'text', array('required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-Z]{3,10}')))
                        ->add('nombre', 'text', array('attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-Z]{3,10}')))
                        ->add('fnac', 'text', array('label' => 'Fecha Nacimiento', 'attr' => array('class' => 'form-control')))
                        ->add('genero', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:GeneroTipo', 'attr' => array('class' => 'form-control')))
                        ->add('save', 'submit', array('label' => 'Buscar'))
                        ->getForm();
    }

    /**
     * save inscription extranjero first time
     * @param Request $request
     * @return type
     */
    public function saveextAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        //encontramos las coincidencias
        $sameStudents = $this->getCoincidenciasStudent($form);

        return $this->render('SieAppWebBundle:InscriptionExtranjeros:resultnorude.html.twig', array(
                    'newstudent' => $form,
                    'samestudents' => $sameStudents,
                    'formninguno' => $this->nobodyform($form)->createView()
        ));
    }

    private function nobodyform($data) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('inscription_extranjeros_savenew'))
                        ->add('paterno', 'hidden', array('data' => strtoupper($data['paterno']), 'required' => false))
                        ->add('materno', 'hidden', array('data' => strtoupper($data['materno']), 'required' => false))
                        ->add('nombre', 'hidden', array('data' => strtoupper($data['nombre'])))
                        ->add('fnac', 'hidden', array('data' => $data['fnac']))
                        ->add('ci', 'hidden', array('data' => $data['ci']))
                        ->add('complemento', 'hidden', array('data' => $data['complemento']))
                        ->add('pais', 'hidden', array('data' => $data['pais']))
                        ->add('ueprocedencia', 'hidden', array('data' => $data['ueprocedencia']))
                        ->add('genero', 'hidden', array('data' => $data['genero']))
                        ->add('ninguno', 'submit', array('label' => 'Ninguno', 'attr' => array('class' => 'btn btn-warning btn-sm btn-block')))
                        ->getForm();
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

    public function savenewAction(Request $request) {
        $form = $request->get('form');

        $sw = 0;
        $data = array();
        $formExtranjeros = $this->createFormNewExtranjeros(0, $sw, $form);
        return $this->render('SieAppWebBundle:InscriptionExtranjeros:newextranjero.html.twig', array(
                    'datastudent' => $form,
                    'formExtranjeros' => $formExtranjeros->createView()
        ));
    }

    /**
     * buil the Omitidos form 
     * @param type $aInscrip
     * @return type form
     */
    private function createFormNewExtranjeros($idStudent, $sw, $data) {

        $em = $this->getDoctrine()->getManager();

        return $formOmitido = $this->createFormBuilder()
                ->setAction($this->generateUrl('inscription_extranjeros_savenewextranjero'))
                ->add('institucionEducativa', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                ->add('institucionEducativaName', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('nivel', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('grado', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('idStudent', 'hidden', array('mapped' => false, 'data' => $idStudent))
                ->add('paralelo', 'choice', array('attr' => array('class' => 'form-control', 'required' => true)))
                ->add('turno', 'choice', array('attr' => array('class' => 'form-control', 'required' => false)))
                ->add('sw', 'hidden', array('data' => $sw))
                ->add('newdata', 'hidden', array('data' => serialize($data)))
                ->add('save', 'submit', array('label' => 'Guardar'))
                ->getForm();
    }

    /**
     * todo the registration of traslado
     * @param Request $request
     * 
     */
    public function savenewextranjeroAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        try {

            //get info old about inscription
            $dataNivel = $this->getInfoInscriptionStudent($form['nivel'], $form['grado']);

            $currentInscrip = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $form['idStudent']));
            //create rude and get the student info
            $digits = 3;
            $mat = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);
            $rude = $form['institucionEducativa'] . $this->session->get('currentyear') . $mat . $this->gererarRude($form['institucionEducativa'] . $this->session->get('currentyear') . $mat);
            $newStudent = unserialize($form['newdata']);
            //echo $rude;die('krlos');
            $student = new Estudiante();
            $student->setPaterno($newStudent['paterno']);
            $student->setMaterno($newStudent['materno']);
            $student->setNombre($newStudent['nombre']);
            $student->setCarnetIdentidad($newStudent['ci']);
            $student->setComplemento($newStudent['complemento']);
            $student->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($newStudent['genero']));
            $student->setFechaNacimiento(new \DateTime($newStudent['fnac']));
            $student->setCodigoRude($rude);
            //falta from UE && pais
            $em->persist($student);
            $em->flush();
            //print_r($currentInscrip);
            //insert a new record with the new selected variables and put matriculaFinID like 5
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($form['grado']));
            $studentInscription->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->find($form['paralelo']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find('2015'));
            $studentInscription->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->find($dataNivel[1]));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find($form['nivel']));
            $studentInscription->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find(1));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($student->getId()));
            $studentInscription->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->find($form['turno']));
            $studentInscription->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->find(0));
            //$studentInscription->setCodUeProcedenciaId($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime('now'));
            $studentInscription->setFechaRegistro(new \DateTime('now'));
            $em->persist($studentInscription);
            $em->flush();

            $this->session->getFlashBag()->add('goodext', 'Estudiante Extranjero ya fue inscrito...');
            return $this->redirect($this->generateUrl('inscription_extranjeros_index'));
        } catch (Exception $ex) {
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }

    private function gererarRude($cadena) {
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

//////////////////////////////////////////////////////////////////////////////

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
     * obtien el formulario para realizar la modificacion
     * @param Request $request
     */
    public function resultAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        //flag to know is a new estranjero student
        $sw = 0;
        $data = array();
        if ($request->get('form')) {
            $form = $request->get('form');
        } else {
            // is turn on if the student exists
            $sw = 1;
            $data = array("id" => $request->get('id'), "codigoRude" => $request->get('codigoRude'), "pais" => $request->get('pais'), "ueprocedencia" => $request->get('ueprocedencia'));
            $form['codigoRude'] = $request->get('codigoRude');
        }

        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));
        //verificamos si existe el estudiante
        if ($student) {
            //check the tuicion
//            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
//            $query->bindValue(':user_id', $this->session->get('userId'));
//            $query->bindValue(':sie', $studentInscription->getInstitucioneducativa()->getId());
//            $query->bindValue(':roluser', $this->session->get('roluser'));
//            $query->execute();
//            $aTuicion = $query->fetchAll();
//            //check the tuicion
//            if (!$aTuicion[0]['get_ue_tuicion']) {
//                $this->session->getFlashBag()->add('notiext', 'Usuario no tiene tuición');
//                return $this->redirect($this->generateUrl('inscription_extranjeros_index'));
//            }

            $studentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
                'estudiante' => $student->getId(),
                'gestionTipo' => $this->session->get('currentyear')
                    //, 'estadomatriculaTipo' => '4'
            ));
            if ($studentInscription) {
                $this->session->getFlashBag()->add('notiext', 'Estudiante ya cuenta con inscripción para la presente gestión');
                return $this->redirect($this->generateUrl('inscription_extranjeros_index'));
            }

            //verificamos si tiene inscripcion en la gestion actual
            //get los historico de cursos
            $inscriptions = $this->getCurrentInscriptionsStudent($student->getCodigoRude());
            $formExtranjeros = $this->createFormExtranjeros($student->getId(), $sw, $data);
        }
//everything is ok build the info
        return $this->render('SieAppWebBundle:InscriptionExtranjeros:result.html.twig', array(
                    'datastudent' => $student,
                    'currentInscription' => $inscriptions,
                    'formExtranjeros' => $formExtranjeros->createView()
        ));
    }

    /**
     * get the stutdents inscription - the record
     * @param type $id
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    private function getCurrentInscriptionsStudent($id) {
//$session = new Session();
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(ei.nivelTipo) as nivelId', 'IDENTITY(ei.gestionTipo) as gestion', 'IDENTITY(ei.gradoTipo) as gradoId', 'IDENTITY(ei.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(ei.paraleloTipo) as paraleloId', 'i.id as sie', 'i.institucioneducativa')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'ei.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'ei.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'ei.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'ei.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'ei.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('e.codigoRude = :id')
                ->setParameter('id', $id)
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    /**
     * buil the Omitidos form 
     * @param type $aInscrip
     * @return type form
     */
    private function createFormExtranjeros($idStudent, $sw, $data) {

        $em = $this->getDoctrine()->getManager();

        return $formOmitido = $this->createFormBuilder()
                ->setAction($this->generateUrl('inscription_extranjeros_save'))
                ->add('institucionEducativa', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                ->add('institucionEducativaName', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('nivel', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('grado', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('idStudent', 'hidden', array('mapped' => false, 'data' => $idStudent))
                ->add('paralelo', 'choice', array('attr' => array('class' => 'form-control', 'required' => true)))
                ->add('turno', 'choice', array('attr' => array('class' => 'form-control', 'required' => false)))
                ->add('sw', 'hidden', array('data' => $sw))
                ->add('newdata', 'hidden', array('data' => serialize($data)))
                ->add('save', 'submit', array('label' => 'Guardar'))
                ->getForm();
    }

    /**
     * todo the registration of traslado
     * @param Request $request
     * 
     */
    public function saveAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
//get the variblees
        $form = $request->get('form');

//        print_r($form);
//        die;
        try {

            //get info old about inscription
            $dataNivel = $this->getInfoInscriptionStudent($form['nivel'], $form['grado']);
            $currentInscrip = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $form['idStudent']));
            //print_r($currentInscrip);
            //insert a new record with the new selected variables and put matriculaFinID like 5
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($form['grado']));
            $studentInscription->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->find($form['paralelo']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
            $studentInscription->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->find($dataNivel[1]));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find($form['nivel']));
            $studentInscription->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find(4));
            $studentInscription->setEstudiante($currentInscrip->getEstudiante());
            $studentInscription->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->find($form['turno']));
            $studentInscription->setSucursalTipo($currentInscrip->getSucursalTipo());
            $studentInscription->setCodUeProcedenciaId($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
            $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $studentInscription->setObservacion(1);
            //$em->persist($studentInscription);
            //$em->flush();

            $this->session->getFlashBag()->add('goodext', 'Estudiante Extranjero ya fue inscrito...');
            return $this->redirect($this->generateUrl('inscription_extranjeros_index'));
        } catch (Exception $ex) {
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }

    /**
     * 
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

    /**
     * get the paralelto, turno and sie name
     * @param type $id like sie
     * @param type $n like nivel
     * @param type $g like grado
     * @return type
     */
    public function findIEAction($id) {
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
        $nombreIE = ($institucion) ? $institucion->getInstitucioneducativa() : "";
        $em = $this->getDoctrine()->getManager();
//get the Niveles
        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $entity->createQueryBuilder('ei')
                ->select('(ei.nivelTipo)')
                ->where('ei.institucioneducativa = :sie')
                ->setParameter('sie', $id)
                ->distinct()
                ->getQuery();
        $aNiveles = $query->getResult();
        foreach ($aNiveles as $nivel) {
            $aniveles[$nivel[1]] = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel[1])->getNivel();
        }

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
    public function findgradoAction($idnivel, $sie) {
        $em = $this->getDoctrine()->getManager();
//get grado
        $agrados = array();
        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $entity->createQueryBuilder('ei')
                ->select('(ei.gradoTipo)')
                ->where('ei.institucioneducativa = :sie')
                ->andWhere('ei.nivelTipo = :idnivel')
                ->setParameter('sie', $sie)
                ->setParameter('idnivel', $idnivel)
                ->distinct()
                ->orderBy('ei.gradoTipo', 'ASC')
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
    public function findparaleloAction($grado, $sie, $nivel) {
        $em = $this->getDoctrine()->getManager();
//get grado
        $aparalelos = array();
        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $entity->createQueryBuilder('ei')
                ->select('(ei.paraleloTipo)')
                ->where('ei.institucioneducativa = :sie')
                ->andWhere('ei.nivelTipo = :nivel')
                ->andwhere('ei.gradoTipo = :grado')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->distinct()
                ->orderBy('ei.paraleloTipo', 'ASC')
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
    public function findturnoAction($paralelo, $sie, $nivel, $grado) {
        $em = $this->getDoctrine()->getManager();
//get grado
        $aturnos = array();
        /* $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
          $query = $entity->createQueryBuilder('ei')
          ->select('(ei.turnoTipo)')
          ->where('ei.institucioneducativa = :sie')
          ->andWhere('ei.nivelTipo = :nivel')
          ->andwhere('ei.gradoTipo = :grado')
          ->andwhere('ei.paraleloTipo = :paralelo')
          ->setParameter('sie', $sie)
          ->setParameter('nivel', $nivel)
          ->setParameter('grado', $grado)
          ->setParameter('paralelo', $paralelo)
          ->distinct()
          ->orderBy('ei.turnoTipo', 'ASC')
          ->getQuery(); */
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('ei')
                ->select('(ei.turnoTipo)')
                ->where('ei.institucioneducativa = :sie')
                ->andWhere('ei.nivelTipo = :nivel')
                ->andwhere('ei.gradoTipo = :grado')
                ->andwhere('ei.paraleloTipo = :paralelo')
                ->andWhere('ei.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('gestion', $this->session->get("currentyear"))
                ->distinct()
                ->orderBy('ei.turnoTipo', 'ASC')
                ->getQuery();
        $aTurnos = $query->getResult();
        foreach ($aTurnos as $turno) {
            $aturnos[$turno[1]] = $em->getRepository('SieAppWebBundle:TurnoTipo')->find($turno[1])->getTurno();
        }

        $response = new JsonResponse();
        return $response->setData(array('aturnos' => $aturnos));
    }

}
