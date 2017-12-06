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
class InscUeUnaccreditedController extends Controller {

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
        return $this->render($this->session->get('pathSystem') . ':InscUeUnaccredited:index.html.twig', array(
                    'form' => $this->createSearchForm()->createView(),
        ));
    }

    /**
     * Creates a form to search the users of student selected
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchForm() {
        $em = $this->getDoctrine()->getManager();
        $estudiante = new Estudiante();

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('insc_ue_unaccredited_result'))
                ->add('ci', 'text', array('label' => 'CI', 'mapped' => false, 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{5,10}')))
                ->add('complemento', 'text', array('label' => 'Complemento', 'mapped' => false, 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{2}')))
                ->add('generoTipo', 'entity', array('label' => 'Género', 'attr' => array('class' => 'form-control'),
                    'mapped' => false, 'class' => 'SieAppWebBundle:GeneroTipo',
                    'query_builder' => function (EntityRepository $e) {
                return $e->createQueryBuilder('g')
                        ->where('g.id != :id')
                        ->setParameter('id', '3');
            }, 'property' => 'genero'
                ))
                ->add('paterno', 'text', array('label' => 'Paterno', 'mapped' => false, 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,40}', 'style' => 'text-transform:uppercase')))
                ->add('materno', 'text', array('label' => 'Materno', 'mapped' => false, 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,40}', 'style' => 'text-transform:uppercase')))
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
                    'data' => $em->getReference("SieAppWebBundle:LugarTipo", 90)
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
                ->add('localidad', 'text', array('mapped' => false, 'required' => false, 'label' => 'Localidad', 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
                ->add('oficialia', 'text', array('mapped' => false, 'required' => false, 'label' => 'Oficialia No', 'attr' => array('class' => 'form-control')))
                ->add('libro', 'text', array('mapped' => false, 'required' => false, 'label' => 'Libro No', 'attr' => array('class' => 'form-control')))
                ->add('partida', 'text', array('mapped' => false, 'required' => false, 'label' => 'Partida No', 'attr' => array('class' => 'form-control')))
                ->add('folio', 'text', array('mapped' => false, 'required' => false, 'label' => 'Folio No', 'attr' => array('class' => 'form-control')))
                ->add('ueprocedencia', 'text', array('mapped' => false, 'required' => false, 'label' => 'Unidad Educativa Procedencia', 'attr' => array('class' => 'form-control', 'style' => 'text-transform:uppercase')))
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

    private function lastInscription($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $entity->createQueryBuilder('ei')
                ->select('ei.id as inscriptionId, IDENTITY(iec.nivelTipo) as nivelTipo', 'IDENTITY(iec.cicloTipo) as cicloTipo, IDENTITY(iec.gradoTipo) as gradoTipo', 'IDENTITY(ei.estadomatriculaTipo) as estadomatriculaTipo')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('ei.estudiante = :id')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('id', $id)
                ->setParameter('gestion', $this->session->get('currentyear'))
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
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
        //build the information on the view
        $this->session->getFlashBag()->add('notiiniprimaria', 'Resultado de la busqueda');
        return $this->render($this->session->get('pathSystem') . ':InscUeUnaccredited:samestudents.html.twig', array(
                    'samestudents' => $this->getCoincidenciasStudent($form),
                    'formninguno' => $this->nobodyform($form)->createView()
        ));
    }

    /**
     * create form to nobody
     * @param type $data
     * @return form to new inscription
     */
    private function nobodyform($data) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('insc_ue_unaccredited_new'))
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

        $formInscription = $this->createFormInscription($request->get('form'), '');
        return $this->render($this->session->get('pathSystem') . ':InscUeUnaccredited:new.html.twig', array(
                    'newstudent' => $data,
                    'formInscription' => $formInscription->createView()
        ));
    }

    /**
     * buil the inscription form
     * @param type $aInscrip
     * @return type form
     */
    private function createFormInscription($data, $rude) {

        $em = $this->getDoctrine()->getManager();

        return $formOmitido = $this->createFormBuilder()
                ->setAction($this->generateUrl('insc_ue_unaccredited_savenew'))
                ->add('institucionEducativa', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                ->add('institucionEducativaName', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('nivel', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('grado', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('paralelo', 'choice', array('attr' => array('class' => 'form-control', 'required' => false)))
                ->add('turno', 'choice', array('attr' => array('class' => 'form-control', 'required' => true)))
                ->add('newdata', 'hidden', array('data' => serialize($data)))
                ->add('rude', 'hidden', array('data' => $rude, 'required' => false))
                ->add('save', 'submit', array('label' => 'Guardar'))
                ->getForm();
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

            //if doesn't exist the rude we generate the rude
            if (!$form['rude']) {
                $digits = 4;
                $mat = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);
                $rude = $form['institucionEducativa'] . $this->session->get('currentyear') . $mat . $this->generarRude($form['institucionEducativa'] . $this->session->get('currentyear') . $mat);
                $newStudent = unserialize($form['newdata']);
                $newStudent = unserialize($newStudent['fullInfo']);

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
                $student->setFechaNacimiento(new \DateTime($newStudent['fnacimiento']));
                $student->setCodigoRude($rude);

                $em->persist($student);
                $em->flush();
                $studentId = $student->getId();
            } else {
                //if the student has a rude, get the id student
                $studentId = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['rude']))->getId();
            }

            //look for the course to the student
            $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                'nivelTipo' => $form['nivel'],
                'gradoTipo' => $form['grado'],
                'paraleloTipo' => $form['paralelo'],
                'turnoTipo' => $form['turno'],
                'institucioneducativa' => $form['institucionEducativa'],
                'gestionTipo' => $this->session->get('currentyear')
            ));
            //insert a new record with the new selected variables and put matriculaFinID like 4
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();

            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($studentId));
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime('now'));
            $studentInscription->setFechaRegistro(new \DateTime('now'));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objCurso->getId()));
            $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(60));
            $studentInscription->setCodUeProcedenciaId(0);
            $em->persist($studentInscription);
            $em->flush();
            //do the commit of DB
            $em->getConnection()->commit();
            $this->addFlash('goodnoacre', 'Estudiante inscrito sin problemas...');
            return $this->redirect($this->generateUrl('insc_ue_unaccredited_index'));
        } catch (Exception $ex) {

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
            //look for inscription for this idStudent
            $studentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $query = $studentInscription->createQueryBuilder('ei')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                    ->where('ei.estudiante = :idStudent')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->setParameter('idStudent', $idStudent)
                    ->setParameter('gestion', $this->session->get('currentyear'))
                    ->getQuery();
            $objStudentInscription = $query->getResult();
            //validate if the student has an inscription
            if ($objStudentInscription) {
                $this->addFlash('notinoacre', 'El Estudiante ya cuenta con inscripción para la gestión ' . $this->session->get('currentyear'));
                return $this->redirectToRoute('insc_ue_unaccredited_index');
            }

            //get info about the student
            $student = $em->getRepository('SieAppWebBundle:Estudiante');
            $query = $student->createQueryBuilder('e')
                    ->select('e.paterno as paterno, e.materno as materno , e.nombre as nombre, e.fechaNacimiento as fnacimiento, e.carnetIdentidad as ci, e.complemento as complemento, IDENTITY(e.generoTipo) as generoTipo, e.codigoRude as rude ')
                    ->where('e.id = :idStudent')
                    ->setParameter('idStudent', $idStudent)
                    ->getQuery();
            $data = $query->getResult();

            $formInscription = $this->createFormInscription($request->get('form'), $data[0]['rude']);
            return $this->render($this->session->get('pathSystem') . ':InscUeUnaccredited:new.html.twig', array(
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

         $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
         $query = $entity->createQueryBuilder('iec')
                 ->select('(iec.nivelTipo)')
                 //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                 ->where('iec.institucioneducativa = :sie')
                 ->andwhere('iec.gestionTipo = :gestion')
                 ->setParameter('sie', $id)
                 ->setParameter('gestion', $this->session->get('currentyear'))
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
         $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
         $query = $entity->createQueryBuilder('iec')
                 ->select('(iec.gradoTipo)')
                 //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                 ->where('iec.institucioneducativa = :sie')
                 ->andWhere('iec.nivelTipo = :idnivel')
                 ->andwhere('iec.gestionTipo = :gestion')
                 ->setParameter('sie', $sie)
                 ->setParameter('idnivel', $idnivel)
                 ->setParameter('gestion', $this->session->get('currentyear'))
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
     public function findparaleloAction($grado, $sie, $nivel) {
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
                 ->setParameter('gestion', $this->session->get('currentyear'))
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
     public function findturnoAction($paralelo, $sie, $nivel, $grado) {
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
                 ->setParameter('gestion', $this->session->get('currentyear'))
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






    /**
     * get the paralelto, turno and sie name
     * @param type $id like sie
     * @param type $n like nivel
     * @param type $g like grado
     * @return type
     */
    public function findIEActionOLD($id, $nivel, $grado, $lastue) {

        $em = $this->getDoctrine()->getManager();
        //get the tuicion
        //select * from get_ue_tuicion(137746,82480002)
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
        $query->bindValue(':sie', $id);
        $query->bindValue(':roluser', $this->session->get('roluser'));
        $query->execute();
        $aTuicion = $query->fetchAll();

        $paralelo = array();
        $turno = array();
        $ue = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
        if ($ue) {
            if ($aTuicion[0]['get_ue_tuicion']) {
                if ($lastue != $id) {
                    $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
                    $nombreIE = ($institucion) ? $institucion->getInstitucioneducativa() : 'No existe Unidad Educativa';
//
                    $infoTraslado = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array(
                        'institucioneducativa' => $id,
                        'nivelTipo' => $nivel,
                        'gradoTipo' => $grado,
                        'gestionTipo' => $this->session->get('currentyear')
                    ));
                    foreach ($infoTraslado as $info) {
                        $paralelo[$info->getParaleloTipo()->getId()] = $info->getParaleloTipo()->getParalelo();
//$turno[$info->getTurnoTipo()->getId()] = $info->getTurnoTipo()->getTurno();
                    }
                } else {
                    $nombreIE = 'Estudiante ya cuenta con inscripción en la Unidad Educativa Seleccionada';
                }
            } else {
                $nombreIE = 'Usuario No tiene Tuición sobre la Unidad Educativa';
            }
        } else {
            $nombreIE = 'No existe Unidad Educativa';
        }
        $response = new JsonResponse();

        return $response->setData(array('nombre' => $nombreIE, 'paralelo' => $paralelo, 'turno' => $turno));
    }

}
