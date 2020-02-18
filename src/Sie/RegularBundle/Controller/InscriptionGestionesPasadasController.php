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
use Sie\AppWebBundle\Entity\EstudianteAsignatura;

/**
 * Estudiante controller.
 *
 */
class InscriptionGestionesPasadasController extends Controller {

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
        // return $this->redirectToRoute('principal_web');
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':InscriptionGestionesPasadas:index.html.twig', array(
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

        //set new gestion to the select year added by krlos pacha
        $gestiones = array();
        $currentYear = date('Y')-1;
        for ($i = 1; $i <= 10; $i++) {
          $gestiones[$currentYear] = $currentYear;
          $currentYear--;
        }
        $estudiante = new Estudiante();

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('inscription_gestiones_pasadas_result'))
                ->add('codigoRude', 'text', array('mapped' => false, 'label' => 'Rude', 'required' => true, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9a-zA-Z\sñÑ]{12,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'choice', array('mapped' => false, 'label' => 'Gestion', 'choices' => $gestiones, 'attr' => array('class' => 'form-control')))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
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

        return $this->render($this->session->get('pathSystem') . ':InscriptionGestionesPasadas:resultnorude.html.twig', array(
                    'newstudent' => $form,
                    'samestudents' => $sameStudents,
                    'formninguno' => $this->nobodyform($form)->createView()
        ));
    }

    public function savenewAction(Request $request) {
        $form = $request->get('form');

        $sw = 0;
        $data = array();
        $formExtranjeros = $this->createFormNewExtranjeros(0, $sw, $form);
        return $this->render($this->session->get('pathSystem') . ':InscriptionGestionesPasadas:newextranjero.html.twig', array(
                    'datastudent' => $form,
                    'formExtranjeros' => $formExtranjeros->createView()
        ));
    }

    /**
     * todo the registration of extranjero
     * @param Request $request
     *
     */
    public function savenewextranjeroAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $form = $request->get('form');

        try {
            //get info old about inscription
            //$dataNivel = $this->getInfoInscriptionStudent($form['nivel'], $form['grado']);

            $currentInscrip = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $form['idStudent']));
            //create rude and get the student info
            $digits = 3;
            $mat = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);
            $rude = $form['institucionEducativa'] . $this->session->get('currentyear') . $mat . $this->gererarRude($form['institucionEducativa'] . $this->session->get('currentyear') . $mat);
            $newStudent = unserialize($form['newdata']);
            //put the id seq with the current data
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante');");
            $query->execute();
            //echo $rude;die('krlos');
            $student = new Estudiante();
            $student->setPaterno($newStudent['paterno']);
            $student->setMaterno($newStudent['materno']);
            $student->setNombre($newStudent['nombre']);
            $student->setCarnetIdentidad($newStudent['ci']);
            $student->setComplemento($newStudent['complemento']);
            $student->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($newStudent['genero']));
            $student->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($newStudent['pais']));
            $student->setFechaNacimiento(new \DateTime($newStudent['fnac']));
            $student->setCodigoRude($rude);
            //falta from UE && pais
            $em->persist($student);
            $em->flush();
            //print_r($currentInscrip);
            //get the id of course
            $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                'nivelTipo' => $form['nivel'],
                'gradoTipo' => $form['grado'],
                'paraleloTipo' => $form['paralelo'],
                'turnoTipo' => $form['turno'],
                'institucioneducativa' => $form['institucionEducativa'],
                'gestionTipo' => $this->session->get('currentyear')
            ));
            //put the id seq with the current data
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();

            //insert a new record with the new selected variables and put matriculaFinID like 5
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($student->getId()));
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime('now'));
            $studentInscription->setFechaRegistro(new \DateTime('now'));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objCurso->getId()));
            $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(19));
            $studentInscription->setCodUeProcedenciaId(0);
            $em->persist($studentInscription);
            $em->flush();
            // Try and commit the transaction
            $em->getConnection()->commit();
            $this->session->getFlashBag()->add('goodext', 'Inscripción Realizada...');
            return $this->redirect($this->generateUrl('inscription_gestiones_pasadas_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
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
     * obtien el formulario para realizar la modificacion
     * @param Request $request
     */
    public function resultAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        //flag to know is a new estranjero student
        $sw = 0;
        $data = array();
//        echo "<pre>";
//        print_r($request->get('form'));
//        echo "</pre>";
//        die;
        $form = $request->get('form');
        if ($request->get('form')) {
            $form = $request->get('form');
        } else {
            // is turn on if the student exists
            $sw = 1;
            $data = array("id" => $request->get('id'), "codigoRude" => $request->get('codigoRude'), "pais" => $request->get('pais'), "ueprocedencia" => $request->get('ueprocedencia'));
            $form['codigoRude'] = $request->get('codigoRude');
        }

        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));

        //check if the student exists
        if ($student) {
            //check the tuicion
            //get data about inscription
//            $inscription2 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
//            $query = $inscription2->createQueryBuilder('ei')
//                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
//                    ->where('ei.estudiante = :id')
//                    ->andwhere('iec.gestionTipo = :gestion')
//                    ->andwhere('ei.estadomatriculaTipo = :matricula')
//                    ->orwhere('ei.estadomatriculaTipo = :matricula1')
//                    ->setParameter('id', $student->getId())
//                    ->setParameter('gestion', $form['gestion'])
//                    ->setParameter('matricula', '4')
//                    ->setParameter('matricula1', '5')
//                    ->getQuery();
//            $studentInscription = $query->getResult();
//            //chedk if the student has inscription
//            if ($studentInscription) {
//                $this->session->getFlashBag()->add('notiext', 'Estudiante ya cuenta con inscripción para la presente gestión');
//                return $this->redirect($this->generateUrl('inscription_gestiones_pasadas_index'));
//            }
            //get Current Inscriptions By Gestoin

            $inscriptions = $this->getCurrentInscriptionsStudent($student->getCodigoRude());
            $inscriptionsGestionSelected = $this->getCurrentInscriptionsByGestoin($student->getCodigoRude(), $form['gestion']);
//            echo $form['gestion'];
//            echo ($inscriptions[0]['gestion']);
//            echo ($inscriptions[sizeof($inscriptions) - 1]['gestion']);
//            if (($inscriptions[0]['gestion'] >= $form['gestion']) && ($form['gestion'] >= $inscriptions[sizeof($inscriptions) - 1]['gestion'])) {
//                //in range
//            } else {
//                $message = "Gestion fuera de rango";
//                $this->addFlash('notiext', $message);
//                return $this->redirectToRoute('inscription_gestiones_pasadas_index');
//            }
            ///die;
            //check if the student was Approved on the gestion selected
            if ($inscriptionsGestionSelected) {
                $message = "El estudiante cuenta con inscripción en la gestion seleccionada";
                $this->addFlash('notiext', $message);
                return $this->redirectToRoute('inscription_gestiones_pasadas_index');
            }
//            echo "<pre>";
//            print_r($inscriptions);
//            echo "<pre>";
//            die;

            $formInsc = $this->createFormInsc($student->getId(), $sw, $inscriptions[0], $form['gestion']);
            //everything is ok build the info
            return $this->render($this->session->get('pathSystem') . ':InscriptionGestionesPasadas:result.html.twig', array(
                        'datastudent' => $student,
                        'currentInscription' => $inscriptions,
                        'formExtranjeros' => $formInsc->createView()
            ));
        } else {
            $message = "Estudiante con rude " . $form['codigoRude'] . " no se encuentra registrado";
            $this->addFlash('notiext', $message);
            return $this->redirectToRoute('inscription_gestiones_pasadas_index');
        }
    }

    /**
     * buil the Omitidos form
     * @param type $aInscrip
     * @return type form
     */
    private function createFormInsc($idStudent, $sw, $data, $gestionIns) {

        $em = $this->getDoctrine()->getManager();

        return $formOmitido = $this->createFormBuilder()
                ->setAction($this->generateUrl('inscription_gestiones_pasadas_save'))
                ->add('institucionEducativa', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                ->add('institucionEducativaName', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('nivel', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('grado', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('idStudent', 'hidden', array('mapped' => false, 'data' => $idStudent))
                ->add('paralelo', 'choice', array('attr' => array('class' => 'form-control', 'required' => true)))
                ->add('turno', 'choice', array('attr' => array('class' => 'form-control', 'required' => false)))
                ->add('sw', 'hidden', array('data' => $sw))
                ->add('newdata', 'hidden', array('data' => serialize($data)))
                ->add('gestionIns', 'hidden', array('data' => $gestionIns))
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
        $em->getConnection()->beginTransaction();
        //get the variblees
        $form = $request->get('form');

        try {
            //insert a new record with the new selected variables and put matriculaFinID like 5
            $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                'nivelTipo' => $form['nivel'],
                'gradoTipo' => $form['grado'],
                'paraleloTipo' => $form['paralelo'],
                'turnoTipo' => $form['turno'],
                'institucioneducativa' => $form['institucionEducativa'],
                'gestionTipo' => $form['gestionIns']
            ));
            //put the id seq with the current data
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();
            //insert a new record with the new selected variables and put matriculaFinID like 5
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($form['gestionIns']));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']));
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime('now'));
            $studentInscription->setFechaRegistro(new \DateTime('now'));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objCurso->getId()));
            //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find());
            $em->persist($studentInscription);
            $em->flush();
            //add the areas to the student
            $responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $objCurso->getId(), $form['gestionIns']);
            $em->getConnection()->commit();
            $this->session->getFlashBag()->add('goodext', 'Inscripción realizada sin problemas');
            return $this->redirect($this->generateUrl('inscription_gestiones_pasadas_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }

    /**
     * to add the areas to the student
     * @param type $studentInscrId
     * @param type $newCursoId
     * @return boolean
     */
    private function addAreasToStudent($studentInscrId, $newCursoId, $gestionIns) {
        $em = $this->getDoctrine()->getManager();
        //put the id seq with the current data
        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');");
        $query->execute();
        $areasEstudiante = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array(
            'estudianteInscripcion' => $studentInscrId,
            'gestionTipo' => $this->session->get('currentyear')
        ));
        //if doesnt have areas we'll fill these
        if (!$areasEstudiante) {
            $objAreas = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array(
                'insitucioneducativaCurso' => $newCursoId
            ));

            foreach ($objAreas as $areas) {
                //print_r($areas->getAsignaturaTipo()->getId());
                $studentAsignatura = new EstudianteAsignatura();
                $studentAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestionIns));
                $studentAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscrId));
                $studentAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($areas->getId()));
                $studentAsignatura->setFechaRegistro(new \DateTime('now'));
                $em->persist($studentAsignatura);
                $em->flush();
                //echo "<hr>";
            }
        }
        return true;
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
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('e.codigoRude = :id')
                ->setParameter('id', $id)
                ->orderBy('iec.gestionTipo', 'DESC')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    /**
     * get the stutdents inscription - the record
     * @param type $id
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    private function getCurrentInscriptionsByGestoin($id, $gestion) {
//$session = new Session();
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('e.codigoRude = :id')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('i.institucioneducativaTipo = :itipo')
                ->andwhere('ei.estadomatriculaTipo IN (:mat)')
                ->setParameter('id', $id)
                ->setParameter('gestion', $gestion)
                ->setParameter('itipo', 1)
                ->setParameter('mat', array('5', '4'))
                ->orderBy('iec.gestionTipo', 'DESC')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
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
    public function findIEAction($id, $gestionselected) {
        $em = $this->getDoctrine()->getManager();

         /*
         * verificamos si tiene tuicion
         */
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
        $query->bindValue(':sie', $id);
        $query->bindValue(':rolId', $this->session->get('roluser'));
        $query->execute();
        $aTuicion = $query->fetchAll();
        
        $aniveles = array();
        if ($aTuicion[0]['get_ue_tuicion']) {

                $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);

             if($institucion){
                $validacionIniConsolidation = $this->get('funciones')->getConsolidationInitioOpe($id, $gestionselected);
                
                if($validacionIniConsolidation){
                    $nombreIE = ($institucion) ? ' La Institución Educativa '. $institucion->getInstitucioneducativa().' no tiene su información consolidada' : "";      
                }else{
                    $nombreIE = ($institucion) ? $institucion->getInstitucioneducativa() : "";  
                }


            }else{
                 $nombreIE = ' Insitucion Educativa no existe...';
            }
            //get the Niveles

            $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
            $query = $entity->createQueryBuilder('iec')
                    ->select('(iec.nivelTipo)')
                    //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                    ->where('iec.institucioneducativa = :sie')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->setParameter('sie', $id)
                    ->setParameter('gestion', $gestionselected)
                    ->distinct()
                    ->getQuery();
            $aNiveles = $query->getResult();
            foreach ($aNiveles as $nivel) {
                $aniveles[$nivel[1]] = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel[1])->getNivel();
            }

            /*     } else {
              $nombreIE = 'No tiene Tuición';
              } */

            
        } else {
             $nombreIE = 'No tiene tuición sobre la unidad educativa';
        }
      
        // if ($aTuicion[0]['get_ue_tuicion']) {
        //get the IE
        

        $response = new JsonResponse();

        return $response->setData(array('nombre' => $nombreIE, 'aniveles' => $aniveles));
    }

    /**
     * get grado
     * @param type $idnivel
     * @param type $sie
     * return list of grados
     */
    public function findgradoAction($idnivel, $sie, $gestionselected) {
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
                ->setParameter('gestion', $gestionselected)
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
    public function findparaleloAction($grado, $sie, $nivel, $gestionselected) {
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
                ->setParameter('gestion', $gestionselected)
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
    public function findturnoAction($paralelo, $sie, $nivel, $grado, $gestionselected) {
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
                ->setParameter('gestion', $gestionselected)
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

}
