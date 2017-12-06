<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use Sie\AppWebBundle\Entity\Usuario;
use Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;

class InscriptionUeAllController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $this->aCursos = $this->fillCursos();
    }

    /**
     * remove inscription Index 
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':InscriptionUeAll:index.html.twig', array(
                    'form' => $this->craeteformsearch()->createView()
        ));
    }

    private function craeteformsearch() {
        return $this->createFormBuilder()
                        //->setAction($this->generateUrl('remove_inscription_sie_index'))
                        ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('gestion', 'choice', array('label' => 'Gestión', 'choices' => array('2016' => '2016', '2015' => '2015'), 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('search', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'findNivel()')))
                        /* ->add('prevNextInscription', 'choice', array('label' => 'Inscripción', 'choices' => array(
                          '0' => 'Posterior',
                          '1' => 'Anterior'
                          ), 'data' => 0, 'multiple' => false, 'expanded' => true, 'required' => true,
                          )) */
                        ->add('prevNextInscription', 'hidden', array('label' => 'SIE', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->getForm();
    }

    /**
     * find the sie and level's data
     * @param Request $request
     * @return type the list of student and inscripion data
     */
    public function resultAction(Request $request) {
        //get the value to send
        $form = $request->get('form');

        $em = $this->getDoctrine()->getManager();
        //find the levels from UE
        //levels gestion -1
        $objLevelsOld = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getNivelBySieAndGestion($form['sie'], $form['gestion']);
        //levels gestion
        //$objLevelsNew = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getNivelBySieAndGestion($form['sie'], $form['gestion']);

        $exist = true;
        //check if the data exist
        if (!$objLevelsOld) {
            $message = 'No existe información de la Unidad Educativa para la gestión seleccionada ó Código SIE no existe ';
            $this->addFlash('warninresult', $message);
            $exist = false;
        }
        $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);
        $objInfoAutorizadaUe = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->getInfoAutorizadaUe($form['sie'], $form['gestion']);
//        echo '<pre>';
//        print_r($objInfoAutorizadaUe);
//        echo '</pre>';
//        die;
        return $this->render($this->session->get('pathSystem') . ':InscriptionUeAll:result.html.twig', array(
                    'objLevels' => $objLevelsOld,
                    'sie' => $form['sie'],
                    'gestion' => $form['gestion'],
                    'objUe' => $objUe,
                    'typeInscription' => $form['prevNextInscription'],
                    //'form' => $this->removeForm()->createView(),
                    'exist' => $exist,
                    'levelAutorizados' => $objInfoAutorizadaUe
        ));
    }

    public function getTurnosAction(Request $request) {
        //get the values
        $sie = $request->get('sie');
        $nivel = $request->get('nivel');
        $gestion = $request->get('gestion');
        $nivelname = $request->get('nivelname');
        $typeInscription = $request->get('typeInscription');

        $em = $this->getDoctrine()->getManager();
        //get turnos
        $objturnos = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getTurnosBySieAndGestion($sie, $nivel, $gestion);

        $exist = true;
        //check if the data exist
        if (!$objturnos) {
            $message = 'Código SIE no existe';
            $this->addFlash('warninsueall', $message);
            $exist = false;
        }
        return $this->render($this->session->get('pathSystem') . ':InscriptionUeAll:turnos.html.twig', array(
                    'objturnos' => $objturnos,
                    'sie' => $sie,
                    'nivel' => $nivel,
                    'gestion' => $gestion,
                    'nivelname' => $nivelname,
                    'typeInscription' => $typeInscription,
                    //'form' => $this->removeForm()->createView(),
                    'exist' => $exist
        ));
    }

    public function getParalelosAction(Request $request) {
        //get the values
        $sie = $request->get('sie');
        $nivel = $request->get('nivel');
        $gestion = $request->get('gestion');
        $turno = $request->get('turno');
        $nivelname = $request->get('nivelname');
        $turnoname = $request->get('turnoname');

        //get db connexion
        $em = $this->getDoctrine()->getManager();
        //get turnos
        $objparalelos = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getParalelosBySieAndGestion($sie, $nivel, $gestion, $turno);

        $exist = true;
        //check if the data exist
        if (!$objparalelos) {
            $message = 'Código SIE no existe';
            $this->addFlash('warninsueall', $message);
            $exist = false;
        }
        return $this->render($this->session->get('pathSystem') . ':InscriptionUeAll:paralelos.html.twig', array(
                    'objparalelos' => $objparalelos,
                    'sie' => $sie,
                    'nivel' => $nivel,
                    'gestion' => $gestion,
                    'turno' => $turno,
                    'nivelname' => $nivelname,
                    'turnoname' => $turnoname,
                    // 'nivelname' => $nivelname,
                    //'form' => $this->removeForm()->createView(),
                    'exist' => $exist
        ));
    }

    public function getStudentsAction(Request $request) {

        //get the values
        $iecId = $request->get('iecId');
        $sie = $request->get('sie');
        $nivel = $request->get('nivel');
        $gestion = $request->get('gestion');
        $grado = $request->get('grado');
        $paralelo = $request->get('paralelo');
        $turno = $request->get('turno');
        $ciclo = $request->get('ciclo');
        $gradoname = $request->get('gradoname');
        $paraleloname = $request->get('paraleloname');
        //get db connexion
        $em = $this->getDoctrine()->getManager();

        //get the next level to do the inscription
        $objNextCursos = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findBy(array(
            'institucioneducativa' => $sie,
            'gestionTipo' => $gestion + 1
        ));
        $objParalelos = array();
        $aNewInscription = array();
        $objTurnos = array();
        $exist = true;
        //check if exists next info
        if ($objNextCursos) {
            //get students to inscription
            // $objStudentsNewInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($iecId, '5');
            $objStudentsNewInscription = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourse($sie, $gestion, $nivel, $grado, $paralelo, $turno);
            //get paralelos to data selected
            $objParalelos = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findBy(array(
                'institucioneducativa' => $sie,
                'nivelTipo' => $nivel,
                'turnoTipo' => $turno,
                'gradoTipo' => $grado,
                'gestionTipo' => $gestion + 1
            ));
            //get the turno for grado
            $objTurnos = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getTurnoBySieInfo($sie, $nivel, $turno, $grado, $gestion + 1);
            if ($objParalelos) {
                //get the student to do the inscription
                reset($objStudentsNewInscription);
                while ($val = current($objStudentsNewInscription)) {
                    //change the inscription to the new student
                    $studentInscription = $this->gettheInscriptionStudent($val['id'], $gestion + 1);
                    //check if exist the inscription
                    if (!$studentInscription) {
                        $aNewInscription[] = $val;
                    }
                    next($objStudentsNewInscription);
                }
                //check if the data exist
                if (!$aNewInscription) {
                    $message = 'No existe estudiantes...';
                    $this->addFlash('warningstudents', $message);
                    $exist = false;
                }
            } else {
                $exist = false;
                $message = 'No existe información de la siguiente curso ...';
                $this->addFlash('warningstudents', $message);
            }
        } else {
            $exist = false;
            $message = 'No existe información de la siguiente gestión ...';
            $this->addFlash('warningstudents', $message);
        }

        $aData = serialize(array('sie' => $sie, 'nivel' => $nivel, 'grado' => $grado, 'paralelo' => $paralelo, 'turno' => $turno, 'iecId' => $iecId, 'ciclo' => $ciclo, 'gestion' => $request->get('gestion'), 'gradoname' => $gradoname, 'paraleloname' => $paraleloname));
        return $this->render($this->session->get('pathSystem') . ':InscriptionUeAll:students.html.twig', array(
                    'objStudents' => $aNewInscription,
                    'sie' => $sie,
                    'nivel' => $nivel,
                    'gestion' => $gestion,
                    'aData' => $aData,
                    'gradoname' => $gradoname,
                    'paraleloname' => $paraleloname,
                    'objParalelos' => $objParalelos,
                    'objTurnos' => $objTurnos,
                    'paraleloSelected' => $paralelo,
                    'turnoSelected' => $turno,
                    // 'nivelname' => $nivelname,
                    'form' => $this->createFormStudentInscription()->createView(),
                    'exist' => $exist
        ));
    }

    private function createFormStudentInscription() {
        return $this->createFormBuilder()
                        ->add('inscription', 'button', array('label' => 'Inscribir', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'doInscription()')))
                        //->add('datasend', 'text')
                        ->getForm();
    }

    private function gettheInscriptionStudent($id, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $inscription2 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $inscription2->createQueryBuilder('ei')
                ->select('ei.id as id, IDENTITY(ei.estadomatriculaTipo) as estadomatriculaTipo')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
                ->where('ei.estudiante = :id')
                ->andwhere('iec.gestionTipo = :gestion')
                //->andwhere('ei.estadomatriculaTipo = :mat')
                ->setParameter('id', $id)
                ->setParameter('gestion', $gestion)
                //->setParameter('mat', '4')
                ->getQuery();

        $studentInscription = $query->getResult();
        return $studentInscription;
    }

    public function inscriptionAction(Request $request) {
        // create the conexion to DB
        $em = $this->getDoctrine()->getManager();
//        echo "<pre>";
        //get the send data
        $dataInscription = $request->get('formdata');
        $dataStudents = $request->get('form');
        $dataInscription = unserialize($dataInscription['data']);
//        echo '<pre>';
//        print_r($dataInscription);
//        print_r($dataStudents);
//
//        echo "</pre>";
//        die;
//        print_r($dataStudents);
//        var_dump($objNextCurso->getId());
//        die;

        reset($dataStudents);
        while ($valStudents = current($dataStudents)) {
            if (strcmp(key($dataStudents), '_token') !== 0) {
                if (isset($valStudents['student'])) {
                    //get the next level info
                    $positionCurso = $this->getCourse($dataInscription['nivel'], $dataInscription['ciclo'], $dataInscription['grado'], $valStudents['matricula']);
                    $dataNextLevel = explode('-', $this->aCursos[$positionCurso]);
                    // print_r($dataNextLevel);
                    //get next level
                    $objNextCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                        'institucioneducativa' => $dataInscription['sie'],
                        'nivelTipo' => $dataNextLevel[0],
                        'cicloTipo' => $dataNextLevel[1],
                        'gradoTipo' => $dataNextLevel[2],
                        'paraleloTipo' => $valStudents['paralelo'],
                        'turnoTipo' => $dataInscription['turno'],
                        'gestionTipo' => $dataInscription['gestion'] + 1
                    ));
//                    echo $dataInscription['gestion'] . '<pre>';
//                    print_r($valStudents);
//                    print_r($objNextCurso->getId());
//                    echo '</pre>';

                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
                    $query->execute();
                    $matriculas = array('5', '11');
                    if (in_array($valStudents['matricula'], $matriculas)) {

                        $studentInscription = new EstudianteInscripcion();
                        $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($dataInscription['sie']));
                        $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($dataInscription['gestion'] + 1));
                        $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
                        $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($valStudents['student']));
                        $studentInscription->setCodUeProcedenciaId($dataInscription['sie']);
                        $studentInscription->setObservacion(1);
                        $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
                        $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
                        $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objNextCurso->getId()));
                        //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(7));
                        $studentInscription->setCodUeProcedenciaId(0);
                        $em->persist($studentInscription);
                        $em->flush();
                        //add the areas to the student
                        //$responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $objNextCurso->getId(), $dataInscription['gestion'] + 1);
                    }
                } else {
                    
                }
            }
            next($dataStudents);
        }
        //die;
        //die;
        /* data: ({iecId: iecId, nivel: nivel, sie: sie, gestion: gestion, grado: grado, paralelo: paralelo, turno: turno, ciclo: ciclo}), */
        return $this->redirect($this->generateUrl('inscription_ue_all_sie_get_students', array(
                            'iecId' => $dataInscription['iecId'],
                            'nivel' => $dataInscription['nivel'],
                            'sie' => $dataInscription['sie'],
                            'gestion' => $dataInscription['gestion'],
                            'grado' => $dataInscription['grado'],
                            'paralelo' => $dataInscription['paralelo'],
                            'turno' => $dataInscription['turno'],
                            'ciclo' => $dataInscription['ciclo'],
                            'gradoname' => $dataInscription['gradoname'],
                            'paraleloname' => $dataInscription['paraleloname']
        )));
    }

    /**
     * to add the areas to the student
     * @param type $studentInscrId
     * @param type $newCursoId
     * @return boolean
     */
    private function addAreasToStudent($studentInscrId, $newCursoId, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $areasEstudiante = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array(
            'estudianteInscripcion' => $studentInscrId,
            'gestionTipo' => $this->session->get('currentyear')
        ));
        //if doesnt have areas we'll fill these
        if (!$areasEstudiante) {
            $objAreas = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array(
                'insitucioneducativaCurso' => $newCursoId
            ));
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');");
            $query->execute();
            foreach ($objAreas as $areas) {
                //print_r($areas->getAsignaturaTipo()->getId());
                $studentAsignatura = new EstudianteAsignatura();
                $studentAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
                $studentAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscrId));
                $studentAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($areas->getId()));
                $studentAsignatura->setFerchaLastUpdate(new \DateTime('now'));
                //$em->persist($studentAsignatura);
                //$em->flush();
            }
        }
        return true;
    }

    /**
     * obtiene el nivel, ciclo y grado
     * @param type $nivel
     * @param type $ciclo
     * @param type $grado
     * @param type $matricula
     * @return type return nivel, ciclo grado del estudiante
     */
    private function getCourse($nivel, $ciclo, $grado, $matricula) {
        //get the array of courses
        $cursos = $this->aCursos;
        //this is a switch to find the courses
        $sw = 1;
        //loof for the courses of student
        while (($acourses = current($cursos)) !== FALSE && $sw) {
            if (current($cursos) == $nivel . '-' . $ciclo . '-' . $grado) {
                $ind = key($cursos);
                $currentCurso = current($cursos);
                $sw = 0;
            }
            next($cursos);
        }
        if ($matricula == 5) {
            $ind = $ind + 1;
        }
        return $ind;
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

    public function seeStudentsAction(Request $request) {

        //get the values
        $iecId = $request->get('iecId');
        $sie = $request->get('sie');
        $nivel = $request->get('nivel');
        $gestion = $request->get('gestion');
        $grado = $request->get('grado');
        $paralelo = $request->get('paralelo');
        $turno = $request->get('turno');
        $ciclo = $request->get('ciclo');
        $gradoname = $request->get('gradoname');
        $paraleloname = $request->get('paraleloname');
        //get db connexion
        $em = $this->getDoctrine()->getManager();
        //get turnos
        //$objStudents = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($iecId, '5');
        //get th position of next level
        $positionCurso = $this->getCourse($nivel, $ciclo, $grado, '5');
        $dataNextLevel = explode('-', $this->aCursos[$positionCurso]);

        //get next level data 

        $objNextCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
            'institucioneducativa' => $sie,
            'nivelTipo' => $nivel,
            'cicloTipo' => $ciclo,
            'gradoTipo' => $grado,
            'paraleloTipo' => $paralelo,
            'turnoTipo' => $turno,
            'gestionTipo' => $gestion
        ));


//        print_r($objStudents);
//        die;
        $exist = true;
        $objStudents = array();
        $aData = array();
        //check if the data exist
        if ($objNextCurso) {
            //$objStudents = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($objNextCurso->getId(), '5');
            $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourse($sie, $gestion + 1, $nivel, $grado, $paralelo, $turno);
            $aData = serialize(array('sie' => $sie, 'nivel' => $nivel, 'grado' => $grado, 'paralelo' => $paralelo, 'turno' => $turno, 'iecId' => $iecId, 'ciclo' => $ciclo, 'iecNextLevl' => $objNextCurso->getId()));
        } else {
            $message = 'No existen estudiantes inscritos...';
            $this->addFlash('warninsueall', $message);
            $exist = false;
        }


        return $this->render($this->session->get('pathSystem') . ':InscriptionUeAll:seeStudents.html.twig', array(
                    'objStudents' => $objStudents,
                    'sie' => $sie,
                    'nivel' => $nivel,
                    'gestion' => $gestion,
                    'aData' => $aData,
                    'gradoname' => $gradoname,
                    'paraleloname' => $paraleloname,
                    // 'nivelname' => $nivelname,
                    'form' => $this->createFormStudentInscription()->createView(),
                    'exist' => $exist
        ));
    }

    public function inscriptionWithRudeAction(Request $request) {
        $dataInscription = $request->get('aData');
        $aDataInscription = unserialize($dataInscription);
        //print_r($aDataInscription);

        return $this->render($this->session->get('pathSystem') . ':InscriptionUeAll:inscriptionWithRude.html.twig', array(
                    'form' => $this->formInscriptionWithRude($dataInscription)->createView(),
                    'exist' => true
        ));
    }

    private function formInscriptionWithRude($data) {
        return $this->createFormBuilder()
                        ->add('rude', 'text', array('label' => 'Rude', 'attr' => array('class' => 'form-control', 'placeholder' => 'Rude', 'maxlength' => 18, 'pattern' => '[A-Z0-9]{13,18}')))
                        ->add('find', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-default', 'onclick' => 'doRudeInscription()')))
                        ->add('dataInscription', 'hidden', array('data' => $data))
                        ->getForm();
    }

    public function rudeInscriptionAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        //get data send
        $rude = $request->get('rude');
        $dataInscription = $request->get('dataInscription');
        $aDataInscription = unserialize($dataInscription);

        //print_r($aDataInscription);

        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));

        $exist = true;
        if ($objStudent) {
            $objInscriptionStudent = $this->gettheInscriptionStudent($objStudent->getId(), $aDataInscription['gestion']);

            if ($objInscriptionStudent) {
                $exist = false;
                $message = 'Estudiante ya cuenta con inscripción...';
                $this->addFlash('warninrudeInscription', $message);
                //echo 'estudiante ya tiene inscripcion';
            }
        } else {
            $exist = false;
            $message = 'Estudiante no existe...';
            $this->addFlash('warninrudeInscription', $message);
        }
        //die;
        return $this->render($this->session->get('pathSystem') . ':InscriptionUeAll:rudeinscription.html.twig', array(
                    'exist' => $exist,
                    'objStudent' => $objStudent ? $objStudent : array(),
                    'form' => $this->formRudeInscription($dataInscription, $rude, ($objStudent) ? $objStudent->getId() : '')->createView()
        ));
    }

    private function formRudeInscription($data, $rude, $idStudent) {
        return $this->createFormBuilder()
                        ->add('dataInscription', 'hidden', array('data' => $data))
                        ->add('dataStudent', 'hidden', array('data' => serialize(array('rude' => $rude, 'idStudent' => $idStudent))))
                        ->add('doInscription', 'button', array('label' => 'Inscribir', 'attr' => array('class' => 'btn btn-green btn-block btn-xs', 'onclick' => 'exeInscription()')))
                        ->getForm();
    }

    /**
     * todo the inscription - Student with rude
     * @param Request $request
     */
    public function exeInscriptionAction(Request $request) {
        //get the conexion DB
        $em = $this->getDoctrine()->getManager();
        //get the data send
        $dataInscription = $request->get('dataInscription');
        $dataStudent = $request->get('dataStudent');
        //convert the values
        $aDataInscription = unserialize($dataInscription);
        $aDataStudent = unserialize($dataStudent);

        //get the next level information
        $positionCurso = $this->getCourse($aDataInscription['nivel'], $aDataInscription['ciclo'], $aDataInscription['grado'], '5');
        $dataNextLevel = explode('-', $this->aCursos[$positionCurso]);


        // print_r($dataNextLevel);
        //get next level
        $objNextCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
            'institucioneducativa' => $aDataInscription['sie'],
            'nivelTipo' => $dataNextLevel[0],
            'cicloTipo' => $dataNextLevel[1],
            'gradoTipo' => $dataNextLevel[2],
            'paraleloTipo' => $aDataInscription['paralelo'],
            'turnoTipo' => $aDataInscription['turno'],
            'gestionTipo' => $aDataInscription['gestion']
        ));
        $exist = true;
        if ($objNextCurso) {
            //insert the new inscription record
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($aDataInscription['sie']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($aDataInscription['gestion']));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($aDataStudent['idStudent']));
            $studentInscription->setCodUeProcedenciaId($aDataInscription['sie']);
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
            $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objNextCurso->getId()));
            //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(7));
            $studentInscription->setCodUeProcedenciaId(0);
            //$em->persist($studentInscription);
            //$em->flush();
            //add the areas to the student
            //$responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $objNextCurso->getId(), $dataInscription['gestion']);
            $message = 'Inscripción realizada...';
            $this->addFlash('successExeInscription', $message);
        } else {
            $message = 'Inscripción no realizada. No se tiene información del nivel selecionado...';
            $exist = false;
            $this->addFlash('warningExeInscription', $message);
        }

        return $this->render($this->session->get('pathSystem') . ':InscriptionUeAll:exeInscription.html.twig', array(
                    'exist' => $exist,
        ));
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////the end
}
