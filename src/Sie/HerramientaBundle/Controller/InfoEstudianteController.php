<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEliminados;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;

/**
 * EstudianteInscripcion controller.
 *
 */
class InfoEstudianteController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->aCursos = $this->fillCursos();
        $this->aCursosOld = $this->fillCursosOld();
    }

    /**
     * list of request
     *
     */
    public function indexAction(Request $request) {
        //get the session's values
        $this->session = $request->getSession();
//        $id_usuario = $this->session->get('userId');
//        //validation if the user is logged
//        if (!isset($id_usuario)) {
//            return $this->redirect($this->generateUrl('login'));
//        }
//        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:index.html.twig', array());
        //get the value to send
        $form = $request->get('form');

        $em = $this->getDoctrine()->getManager();
        //find the levels from UE
        //levels gestion -1
        //$objLevelsOld = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getNivelBySieAndGestion($form['sie'], $form['gestion']);
        $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getInfoUeducativaBySieGestion($form['sie'], $form['gestion']);

        $exist = true;
        $tieneSextoSec = false;
        $aInfoUnidadEductiva = array();
        if ($objUeducativa) {
            foreach ($objUeducativa as $uEducativa) {

                //get the literal data of unidad educativa
                $sinfoUeducativa = serialize(array(
                    'ueducativaInfo' => array('nivel' => $uEducativa['nivel'], 'grado' => $uEducativa['grado'], 'paralelo' => $uEducativa['paralelo'], 'turno' => $uEducativa['turno']),
                    'ueducativaInfoId' => array('paraleloId' => $uEducativa['paraleloId'], 'turnoId' => $uEducativa['turnoId'], 'nivelId' => $uEducativa['nivelId'], 'gradoId' => $uEducativa['gradoId'], 'cicloId' => $uEducativa['cicloTipoId'], 'iecId' => $uEducativa['iecId']),
                    'requestUser' => array('sie' => $form['sie'], 'gestion' => $form['gestion'])
                ));

                //send the values to the next steps
                $aInfoUnidadEductiva[$uEducativa['turno']][$uEducativa['nivel']][$uEducativa['grado']][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa);

                if($uEducativa['nivelId'] == 13 and $uEducativa['gradoId'] == 6){
                    $tieneSextoSec = true;
                }
            }
        } else {
            $message = 'No existe información de la Unidad Educativa para la gestión seleccionada ó Código SIE no existe ';
            $this->addFlash('warninresult', $message);
            $exist = false;
        }
        // check if the UE close the rude task
        $objinstitucioneducativaOperativoLogExist = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLog')->findOneBy(array(
          'institucioneducativa' => $form['sie'],
          'gestionTipoId'  => $form['gestion'],
          'institucioneducativaOperativoLogTipo' => 4
        ));
        // create the var to show the message about close opertive
        $this->session->set('closeRude',(!$objinstitucioneducativaOperativoLogExist)?false:true);

        $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);
        //$objInfoAutorizadaUe = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->getInfoAutorizadaUe($form['sie'], $form['gestion']);die('krlossdfdfdfs');
        $odataUedu = $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);


        // Verificamos si se cerro el sexto grado de acuerdo al operativo
        $sie = $form['sie'];
        $gestion = $form['gestion'];

        $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);
        $mostrarSextoCerrado = false;
        if($tieneSextoSec and $gestion >= 2018 and $operativo == 4){
            $validacionSexto = $this->get('funciones')->verificarGradoCerrado($sie, $gestion);
            if(!$validacionSexto){
                $mostrarSextoCerrado = true;
            }
        }

        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:index.html.twig', array(
                    'aInfoUnidadEductiva' => $aInfoUnidadEductiva,
                    'sie' => $form['sie'],
                    'gestion' => $form['gestion'],
                    'objUe' => $objUe,
                    //'form' => $this->removeForm()->createView(),
                    'exist' => $exist,
          //          'levelAutorizados' => $objInfoAutorizadaUe,
                    'odataUedu' => $odataUedu,
                    'mostrarSextoCerrado'=>$mostrarSextoCerrado
        ));
    }

    public function getStudentsAction(Request $request) {
        //get db connexion
        $em = $this->getDoctrine()->getManager();
        //get the info ue
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
//        dump($aInfoUeducativa);
//        die;
        //get the values throght the infoUe
        $sie = $aInfoUeducativa['requestUser']['sie'];
        $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
        $ciclo = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
        $gestion = $aInfoUeducativa['requestUser']['gestion'];
        $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
        $gradoname = $aInfoUeducativa['ueducativaInfo']['grado'];
        $paraleloname = $aInfoUeducativa['ueducativaInfo']['paralelo'];

        //get the before level, ciclo and grado
        list($beforeNivel, $beforeCiclo, $beforeGrado) = $this->getBeforeCourse($nivel, $ciclo, $grado);

        //get the next level to do the inscription
        $objNextCursos = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findBy(array(
            'institucioneducativa' => $sie,
            'gestionTipo' => $gestion
        ));

        $objParalelos = array();
        $aNewInscription = array();
        $objTurnos = array();
        $exist = true;
        //check if exists previuos info
        if ($objNextCursos) {
            //get students to inscription
            // $objStudentsNewInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($iecId, '5');
            $objStudentsNewInscription = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourse($sie, $gestion - 1, $beforeNivel, $beforeGrado, $paralelo, $turno);

            //get paralelos to data selected
            $objParalelos = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findBy(array(
                'institucioneducativa' => $sie,
                'nivelTipo' => $nivel,
                'turnoTipo' => $turno,
                'gradoTipo' => $grado,
                'gestionTipo' => $gestion
            ));

            //get the turno for grado
            $objTurnos = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getTurnoBySieInfo($sie, $nivel, $turno, $grado, $gestion + 1);
            if ($objParalelos) {

                //get the student to do the inscription
                reset($objStudentsNewInscription);
                while ($val = current($objStudentsNewInscription)) {
                    //change the inscription to the new student
                    $studentInscription = $this->gettheInscriptionStudent($val['id'], $gestion);
                    //check if exist the inscription
                    if (!$studentInscription) {
                        $aNewInscription[] = $val;
                    }
                    next($objStudentsNewInscription);
                }
                //check if the data exist
                if (!$aNewInscription) {
                    $message = 'No existe estudiantes para inscribir...';
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

        $aData = serialize(array('sie' => $sie, 'nivel' => $nivel, 'grado' => $grado, 'paralelo' => $paralelo, 'turno' => $turno, 'iecId' => $iecId, 'ciclo' => $ciclo, 'gestion' => $gestion, 'gradoname' => $gradoname, 'paraleloname' => $paraleloname));
        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:students.html.twig', array(
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
                    'form' => $this->createFormStudentInscription($infoUe)->createView(),
                    'exist' => $exist
        ));
    }

    public function inscriptionAction(Request $request) {

        // create the conexion to DB
        $em = $this->getDoctrine()->getManager();
//        echo "<pre>";
        //get the send data
        $dataInscription = $request->get('formdata');

        $dataStudents = $request->get('form');
        $dataInscription = unserialize($dataInscription['data']);

        reset($dataStudents);
        while ($valStudents = current($dataStudents)) {
            if (strcmp(key($dataStudents), '_token') !== 0) {
                if (isset($valStudents['student'])) {
                    //get the next level info
                    //$positionCurso = $this->getCourse($dataInscription['nivel'], $dataInscription['ciclo'], $dataInscription['grado'], $valStudents['matricula']);
                    //$dataNextLevel = explode('-', $this->aCursos[$positionCurso]);
                    // print_r($dataNextLevel);
                    //get next level
                    $objNextCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                        'institucioneducativa' => $dataInscription['sie'],
                        'nivelTipo' => $dataInscription['nivel'],
                        'cicloTipo' => $dataInscription['ciclo'],
                        'gradoTipo' => $dataInscription['grado'],
                        'paraleloTipo' => $valStudents['paralelo'],
                        'turnoTipo' => $dataInscription['turno'],
                        'gestionTipo' => $dataInscription['gestion']
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
                        $responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $objNextCurso->getId(), $dataInscription['gestion']);
                    }
                } else {

                }
            }
            next($dataStudents);
        }
        //die;
        //die;
        /* data: ({iecId: iecId, nivel: nivel, sie: sie, gestion: gestion, grado: grado, paralelo: paralelo, turno: turno, ciclo: ciclo}), */

        return $this->redirect($this->generateUrl('herramienta_info_estudiante_get_students', array(
                            'iecId' => $dataInscription['iecId'],
                            'nivel' => $dataInscription['nivel'],
                            'sie' => $dataInscription['sie'],
                            'gestion' => $dataInscription['gestion'],
                            'grado' => $dataInscription['grado'],
                            'paralelo' => $dataInscription['paralelo'],
                            'turno' => $dataInscription['turno'],
                            'ciclo' => $dataInscription['ciclo'],
                            'gradoname' => $dataInscription['gradoname'],
                            'paraleloname' => $dataInscription['paraleloname'],
                            'infoUe' => $request->get('form')['infoUe']
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
            'gestionTipo' => $gestion
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
                $em->persist($studentAsignatura);
                $em->flush();
            }
        }
        return true;
    }

    private function getBeforeCourse($nivel, $ciclo, $grado) {

        reset($this->aCursos);
        $sw = true;
        while ($arrInfoCurso = current($this->aCursos) and $sw) {
            list ($level, $cicle, $grade) = (explode('-', $arrInfoCurso));
            if ($nivel == $level and $ciclo == $cicle and $grado == $grade) {
                $Key = key($this->aCursos);
                $sw = false;
            }

            next($this->aCursos);
        }
        $arrayCurso = ($Key > 0) ? explode('-', $this->aCursos[$Key - 1]) : array(11, 1, 1);
        return $arrayCurso;
    }

    public function inscriptionWithRudeAction(Request $request) {

        $dataInscription = $request->get('aData');
        $aDataInscription = unserialize($dataInscription);
        //print_r($aDataInscription);

        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:inscriptionWithRude.html.twig', array(
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
        //get the inscription on gestion - 1 and matricula = 5
        $objCurrentInscriptionMinus = $this->getCurrentInscriptionMinus($objStudent->getId(), $aDataInscription['gestion']-1);
        //dump($objCurrentInscriptionMinus);die;
        //die;
        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:rudeinscription.html.twig', array(
                    'exist' => $exist,
                    'objStudent' => $objStudent ? $objStudent : array(),
                    'form' => $this->formRudeInscription($dataInscription, $rude, ($objStudent) ? $objStudent->getId() : '')->createView()
        ));
    }

    private function formRudeInscription($data, $rude, $idStudent) {
        return $this->createFormBuilder()
                        ->add('dataInscription', 'hidden', array('data' => $data))
                        ->add('dataStudent', 'hidden', array('data' => serialize(array('rude' => $rude, 'idStudent' => $idStudent))))
                        ->add('doInscription', 'button', array('label' => 'Inscribir', 'attr' => array('class' => 'btn btn-success btn-block btn-xs', 'onclick' => 'exeInscription()')))
                        ->getForm();
    }
    /**
     * to get the values of the current inscription to the student
     * @param type $id
     * @param type $gestion
     * @return type - get the student info
     */
    private function getCurrentInscriptionMinus($id, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $inscription2 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $inscription2->createQueryBuilder('ei')
                ->select('ei.id as id, IDENTITY(ei.estadomatriculaTipo) as estadomatriculaTipo', 'IDENTITY(iec.nivelTipo) as nivelId, IDENTITY(iec.cicloTipo) as cicloId, IDENTITY(iec.gradoTipo) as gradoId' )
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
                ->where('ei.estudiante = :id')
                ->andwhere('iec.gestionTipo = :gestion')
                //->andwhere('ei.estadomatriculaTipo = :mat')
                ->setParameter('id', $id)
                ->setParameter('gestion', $gestion)
                //->setParameter('mat', '5')
                ->getQuery();

        $studentInscription = $query->getResult();
        return $studentInscription;
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

        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:exeInscription.html.twig', array(
                    'exist' => $exist,
        ));
    }

    /**
     * create form to do the massive inscription
     * @return type obj form
     */
    private function createFormStudentInscription($data) {
        return $this->createFormBuilder()
                        ->add('inscription', 'button', array('label' => 'Inscribir', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'doInscription()')))
                        ->add('infoUe', 'hidden', array('data' => $data))
                        ->getForm();
    }

    /**
     * to get the values of the current inscription to the student
     * @param type $id
     * @param type $gestion
     * @return type - get the student info
     */
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

    public function seeStudentsAction(Request $request) {

        //get the info ue
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);

        //get the values throght the infoUe
        $sie = $aInfoUeducativa['requestUser']['sie'];
        $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
        $ciclo = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
        $gestion = $aInfoUeducativa['requestUser']['gestion'];
        $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
        $gradoname = $aInfoUeducativa['ueducativaInfo']['grado'];
        $paraleloname = $aInfoUeducativa['ueducativaInfo']['paralelo'];
        //get db connexion
        $em = $this->getDoctrine()->getManager();

        $objTypeOfUE = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->getTypeOfUE(array('sie'=>$sie,'gestion'=>$aInfoUeducativa['requestUser']['gestion']));
        $arrAllowInscription=array(1,2,3,4,5);
        $objTypeOfUEId = (sizeof($objTypeOfUE))>0?$objTypeOfUE[0]->getInstitucioneducativaHumanisticoTecnicoTipo()->getId():100;
        if(in_array((sizeof($objTypeOfUE))>0?$objTypeOfUE[0]->getInstitucioneducativaHumanisticoTecnicoTipo()->getId():100,$arrAllowInscription)){
          $this->session->set('allowInscription',true);
        }else{
          $this->session->set('allowInscription',false);
        }
        //get turnos
        //$objStudents = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($iecId, '5');
        //get th position of next level

        // $positionCurso = $this->getCourse($nivel, $ciclo, $grado, '5');
        $posicionCurso = ($aInfoUeducativa['requestUser']['gestion'] > 2010) ? $this->getCourse($nivel, $ciclo, $grado, '5') : $this->getCourseOld($nivel, $ciclo, $grado, '5');
        //$dataNextLevel = explode('-', $this->aCursos[$positionCurso]);

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

        $exist = true;
        $objStudents = array();
        $aData = array();
        //check if the data exist
        if ($objNextCurso) {
            //$objStudents = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($objNextCurso->getId(), '5');
            //get students list

            $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourse($sie, $gestion, $nivel, $grado, $paralelo, $turno);
            $aData = serialize(array('sie' => $sie, 'nivel' => $nivel, 'grado' => $grado, 'paralelo' => $paralelo, 'turno' => $turno, 'gestion' => $gestion, 'iecId' => $iecId, 'ciclo' => $ciclo, 'iecNextLevl' => $objNextCurso->getId()));
        } else {
            $message = 'No existen estudiantes inscritos...';
            $this->addFlash('warninsueall', $message);
            $exist = false;
        }

        // Para el centralizador
        $itemsUe = $aInfoUeducativa['ueducativaInfo']['nivel'].",".$aInfoUeducativa['ueducativaInfo']['grado'].",".$aInfoUeducativa['ueducativaInfo']['paralelo'];

        $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($sie,$gestion);



        //to add the especialidad
        $UePlenasAddSpeciality=false;
        $arrUePlenasAddSpeciality = array(
          '81410160',
          '81410080',
          '40730250',
          '81410037',
          '81410134',
          '82220009',
          '80480060',
          '81981445',
          '80660080',
          '81340038',
          '81340065',
          '80730395',
          '80730391',
          '71170009',
          '60730046',
          '71170010',
          '81410157',

          '60900064',
          '81981463',
          '81480060',
          '80630028',
          '81470005',
          '81470069',
          '80980556',
          '80920034',
          '80980514',
          '71480114',
          '40730531',
          '82220001',
          '81170016',
          '80480163'
        );
        $UePlenasAddSpeciality = (in_array($sie, $arrUePlenasAddSpeciality))?true:false;

        // Impresion de libretas
        $tipoUE = $this->get('funciones')->getTipoUE($sie,$gestion);
        $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);

        $imprimirLibreta = false;
        $estadosPermitidosImprimir = array(4,5,11,55);

        if($tipoUE){
            /*
             *  GESTION ACTUAL
             */
            if($gestion == $this->session->get('currentyear')){
                // Unidades educativas plenas, modulares y humanisticas
                if(in_array($tipoUE['id'], array(1,3,5)) and $operativo >= 2){
                    $imprimirLibreta = true;
                }
                // Unidades educativas tecnicas tecnologicas
                if(in_array($tipoUE['id'], array(2)) and $operativo >= 4){
                    $imprimirLibreta = true;
                }
            }


            /*
             * GESTIONES PASADAS
             */
            if($gestion < $this->session->get('currentyear')){
                // Para unidades educativas en gestiones pasadas
                if(in_array($tipoUE['id'], array(1,2,3,5)) and $gestion > 2014 and $gestion < $this->session->get('currentyear') and $operativo >= 4){
                    $imprimirLibreta = true;
                }

                // PAra ues tecnicas tecnologicas
                if(in_array($tipoUE['id'], array(2)) and $gestion >= 2011){
                    $imprimirLibreta = true;
                }

                // Caso especial de la unidad educativa AMERINST
                if($sie == '80730460' and $gestion <= 2015){
                    $imprimirLibreta = false;
                    if($gestion == 2014 and $nivel == 13 and $grado >= 4 and $paralelo >= 6){
                        $imprimirLibreta = true;
                    }
                    if($gestion == 2015 and $nivel == 13 and $grado >= 5 and $paralelo >= 6){
                        $imprimirLibreta = true;
                    }
                    if($gestion >= 2009 and $gestion <= 2013){
                        $imprimirLibreta = true;
                    }
                }
            }
        }else{
            if($gestion > 2014 and $operativo >= 4){
                $imprimirLibreta = true;
            }
        }

      $aRemovesUeAllowed = array(
      '61710014',
      '61710089',
      '61710076',
      '61710068',
      '61710090',
      '61710042',
      '61710087',
      '61710084',
      '61710083',
      '61710085',
      '61710088',
      '61710063',
      '61710028',
      '61710086',
      '61710041',
      '61710043',
      '61710062',
      '61710031',
      '61710077',
      '61710021',
      '61710022',
      '61710036',
      '61710038',
      '61710091',
      '61710092',
      '61710093',
      '61710004',
      '60900064'
      );

    $mostrarSextoCerrado = false;
    if($gestion >= 2018 and $operativo == 4 and $nivel == 13 and $grado == 6){
        $validacionSexto = $this->get('funciones')->verificarGradoCerrado($sie, $gestion);
        if(!$validacionSexto){
            $mostrarSextoCerrado = true;
        }
    }

  $this->session->set('removeInscriptionAllowed', false);
  if(in_array($this->session->get('ie_id'),$aRemovesUeAllowed))
    $this->session->set('removeInscriptionAllowed',true);

        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:seeStudents.html.twig', array(
                    'objStudents' => $objStudents,
                    'iecId'=>$iecId,
                    'sie' => $sie,
                    'turno' => $turno,
                    'nivel' => $nivel,
                    'grado' => $grado,
                    'paralelo' => $paralelo,
                    'gestion' => $gestion,
                    'aData' => $aData,
                    'gradoname' => $gradoname,
                    'paraleloname' => $paraleloname,
                    // 'nivelname' => $nivelname,
                    'form' => $this->createFormStudentInscription($infoUe)->createView(),
                    'infoUe' => $infoUe,
                    'exist' => $exist,
                    'itemsUe'=>$itemsUe,
                    'ciclo'=>$ciclo,
                    'operativo'=>$operativo,
                    'UePlenasAddSpeciality' => $UePlenasAddSpeciality,
                    'imprimirLibreta'=>$imprimirLibreta,
                    'estadosPermitidosImprimir'=>$estadosPermitidosImprimir,
                    'mostrarSextoCerrado'=>$mostrarSextoCerrado
        ));
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

    private function getCourseOld($nivel, $ciclo, $grado, $matricula) {
      //get the array of courses
        $cursos = $this->aCursosOld;
        //this is a switch to find the courses
        $sw = 1;
        //loof for the courses of student
        while (( $acourses = current($cursos)) !== FALSE && $sw) {
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

    private function fillCursosOld() {
        $this->aCursosOld = array(
            ('1-2-1'),
            ('1-3-2'),
            ('2-1-1'),
            ('2-1-2'),
            ('2-1-3'),
            ('2-2-4'),
            ('2-2-5'),
            ('2-2-6'),
            ('2-3-7'),
            ('2-3-8'),
            ('3-1-1'),
            ('3-1-2'),
            ('3-3-3'),
            ('3-3-4')
        );
        return($this->aCursosOld);
    }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * open the request
     * @param Request $request
     * @return obj with the selected request
     */
    public function openAction(Request $request) {
        //get session data
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:open.html.twig', array());
    }

    /**
     * get the turnos UE
     * @param Request $request
     * @return array with turnos UE
     */
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
        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:turnos.html.twig', array(
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


  public function removeInscriptionAcaAction(Request $request) {

        //get the info ue
    $infoUe = $request->get('infoUe');
    $aInfoUeducativa = unserialize($infoUe);

    //get the values throght the infoUe
    $sie = $aInfoUeducativa['requestUser']['sie'];
    $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
    $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
    $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
    $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
    $ciclo = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
    $gestion = $aInfoUeducativa['requestUser']['gestion'];
    $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
    $gradoname = $aInfoUeducativa['ueducativaInfo']['grado'];
    $paraleloname = $aInfoUeducativa['ueducativaInfo']['paralelo'];
    //get db connexion
    $em = $this->getDoctrine()->getManager();
    $em->getConnection()->beginTransaction();
    //get data student
    $infoStudent = $request->get('infoStudent');

    $aInfoStudent = json_decode($infoStudent,true);
    $eiid = $aInfoStudent['eInsId'];

    //start removeInscription
    try {
      $objJuegos = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos')->findOneBy(array('estudianteInscripcion' => $eiid));
      if ($objJuegos) {
          $message = "No se puede eliminar por que el estudiante esta registrado en el sistema de Juegos Plurinacionales";
          $this->addFlash('warningremoveins', $message);
          // return $this->redirectToRoute('remove_inscription_sie_index');
      }
      //get the student's inscription
      $objEstudiantInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eiid);
      //get institucioneducativaCurso info
      $objInsctitucionEducativaCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objEstudiantInscripcion->getInstitucioneducativaCurso()->getId());
      $arrAllowAccessOption = array(7,8);

      //step 1 remove the inscription observado
      $objStudentInscriptionObservados = $em->getRepository('SieAppWebBundle:EstudianteInscripcionObservacion')->findBy(array('estudianteInscripcion' => $eiid));
      //dump($objStudentInscriptionObservados);
      //die;
      if ($objStudentInscriptionObservados){
          foreach ($objStudentInscriptionObservados as $element) {
              $em->remove($element);
          }
          $em->flush();
          //$em->remove($objStudentInscriptionObservados);
          //$em->flush();
          $obs = $element->getObs();
      }
      else{
          $obs = '';
      }

//            step 2 delete nota
      $objEstAsig = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('estudianteInscripcion' => $eiid, 'gestionTipo' => $gestion ));


      //step 3 delete asignatura
      foreach ($objEstAsig as $element) {
          $objNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura' => $element));
          foreach($objNota as $element2)
          {
              $em->remove($element2);
          }
          $em->remove($element);
      }
      $em->flush();

      //dump($objEstAsig);die;
      $objNotaC = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array('estudianteInscripcion' => $eiid));
      foreach ($objNotaC as $element) {
          $em->remove($element);
      }
      $em->flush();

      //step 4 delete socio economico data
      $objSocioEco = $em->getRepository('SieAppWebBundle:SocioeconomicoRegular')->findBy(array('estudianteInscripcion' => $eiid ));
      //dump($objSocioEco);die;
      foreach ($objSocioEco as $element) {
          $em->remove($element);
      }
      $em->flush();

      //step 5 delete apoderado_inscripcion data
      $objApoIns = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $eiid ));
      //dump($objApoIns);die;

      foreach ($objApoIns as $element) {
          $objApoInsDat = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findBy(array('apoderadoInscripcion' => $element->getId()));
          foreach ($objApoInsDat as $element1){
              $em->remove($element1);
          }
          $em->remove($element);
      }
      $em->flush();

      //dump($objApoIns);die;
      //remove attached file
      $objStudentInscriptionExtranjero = $em->getRepository('SieAppWebBundle:EstudianteInscripcionExtranjero')->findOneBy(array('estudianteInscripcion'=>$eiid));
      if($objStudentInscriptionExtranjero){
        $em->remove($objStudentInscriptionExtranjero);
        $em->flush();
      }

     //paso 6 borrando apoderados
      $apoderados = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $eiid ));
      foreach ($apoderados as $element) {
          $em->remove($element);
      }
      $em->flush();

      //step 6 copy data to control table and remove teh inscription
      $objStudentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eiid);

      $studentInscription = new EstudianteInscripcionEliminados();
      $studentInscription->setEstudianteInscripcionId($objStudentInscription->getId());
      $studentInscription->setEstadomatriculaTipoId($objStudentInscription->getEstadoMatriculaTipo()->getId());
      $studentInscription->setEstudianteId($objStudentInscription->getEstudiante()->getId());
      $studentInscription->setNumMatricula($objStudentInscription->getNumMatricula());
      $studentInscription->setObservacionId($objStudentInscription->getObservacionId());
      $studentInscription->setObservacion($objStudentInscription->getObservacion());
      $studentInscription->setFechaInscripcion($objStudentInscription->getFechaInscripcion());
      $studentInscription->setApreciacionFinal($objStudentInscription->getApreciacionFinal());
      $studentInscription->setOperativoId($objStudentInscription->getOperativoId());
      $studentInscription->setFechaRegistro($objStudentInscription->getFechaRegistro());
      $studentInscription->setOrganizacion($objStudentInscription->getOrganizacion());
      $studentInscription->setFacilitadorpermanente($objStudentInscription->getFacilitadorpermanente());
      $studentInscription->setModalidadTipoId($objStudentInscription->getModalidadTipoId());
      $studentInscription->setAcreditacionnivelTipoId($objStudentInscription->getAcreditacionnivelTipoId());
      $studentInscription->setPermanenteprogramaTipoId($objStudentInscription->getPermanenteprogramaTipoId());
      $studentInscription->setFechaInicio($objStudentInscription->getFechaInicio());
      $studentInscription->setFechaFin($objStudentInscription->getFechaFin());
      $studentInscription->setCursonombre($objStudentInscription->getCursonombre());
      $studentInscription->setLugar($objStudentInscription->getLugar());
      $studentInscription->setLugarcurso($objStudentInscription->getLugarcurso());
      $studentInscription->setFacilitadorcurso($objStudentInscription->getFacilitadorcurso());
      $studentInscription->setFechainiciocurso($objStudentInscription->getFechainiciocurso());
      $studentInscription->setFechafincurso($objStudentInscription->getFechafincurso());
      $studentInscription->setCodUeProcedenciaId($objStudentInscription->getCodUeProcedenciaId());
      $studentInscription->setInstitucioneducativaCursoId($objStudentInscription->getInstitucioneducativaCurso()->getId());
      if(($objStudentInscription->getEstadomatriculaInicioTipo()))
        $studentInscription->setEstadomatriculaInicioTipoId($objStudentInscription->getEstadomatriculaInicioTipo()->getId());
      $studentInscription->setObsEliminacion($obs);
      $studentInscription->setUsuarioId($this->session->get('userId'));
      $studentInscription->setFechaEliminacion(new \DateTime('now'));
      $studentInscription->setDoc('false');
      $em->persist($studentInscription);
      $em->flush();

      $em->remove($objStudentInscription);
      $em->flush();

      // Try and commit the transaction
      $em->getConnection()->commit();
      $message = "Proceso realizado exitosamente.";
      $this->addFlash('successremoveins', $message);
      // return $this->redirectToRoute('remove_inscription_sie_index');
    } catch (Exception $e) {
      $em->getConnection()->rollback();
      $message = "Proceso detenido! Se ha detectado inconsistencia de datos. \n".$ex->getMessage();
      $this->addFlash('warningremoveins', $message);
    }

    //end removeInscription



    $objTypeOfUE = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->getTypeOfUE(array('sie'=>$sie,'gestion'=>$aInfoUeducativa['requestUser']['gestion']));
    $arrAllowInscription=array(1,2,3,4,5);
    $objTypeOfUEId = (sizeof($objTypeOfUE))>0?$objTypeOfUE[0]->getInstitucioneducativaHumanisticoTecnicoTipo()->getId():100;
    if(in_array((sizeof($objTypeOfUE))>0?$objTypeOfUE[0]->getInstitucioneducativaHumanisticoTecnicoTipo()->getId():100,$arrAllowInscription)){
      $this->session->set('allowInscription',true);
    }else{
      $this->session->set('allowInscription',false);
    }
    //get turnos
    //$objStudents = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($iecId, '5');
    //get th position of next level

    // $positionCurso = $this->getCourse($nivel, $ciclo, $grado, '5');
    $posicionCurso = ($aInfoUeducativa['requestUser']['gestion'] > 2010) ? $this->getCourse($nivel, $ciclo, $grado, '5') : $this->getCourseOld($nivel, $ciclo, $grado, '5');
    //$dataNextLevel = explode('-', $this->aCursos[$positionCurso]);

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

    $exist = true;
    $objStudents = array();
    $aData = array();
    //check if the data exist
    if ($objNextCurso) {
        //$objStudents = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($objNextCurso->getId(), '5');
        //get students list
        $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourse($sie, $gestion, $nivel, $grado, $paralelo, $turno);
        $aData = serialize(array('sie' => $sie, 'nivel' => $nivel, 'grado' => $grado, 'paralelo' => $paralelo, 'turno' => $turno, 'gestion' => $gestion, 'iecId' => $iecId, 'ciclo' => $ciclo, 'iecNextLevl' => $objNextCurso->getId()));
    } else {
        $message = 'No existen estudiantes inscritos...';
        $this->addFlash('warninsueall', $message);
        $exist = false;
    }

    // Para el centralizador
    $itemsUe = $aInfoUeducativa['ueducativaInfo']['nivel'].",".$aInfoUeducativa['ueducativaInfo']['grado'].",".$aInfoUeducativa['ueducativaInfo']['paralelo'];

    $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($sie,$gestion);



    //to add the especialidad
    $UePlenasAddSpeciality=false;
    $arrUePlenasAddSpeciality = array(
      '81410160',
      '81410080',
      '40730250',
      '81410037',
      '81410134',
      '82220009',
      '80480060',
      '81981445',
      '80660080',
      '81340038',
      '81340065',
      '80730395',
      '80730391',
      '71170009',
      '60730046',
      '71170010',
      '81410157',

      '60900064',
      '81981463',
      '81480060',
      '80630028',
      '81470005',
      '81470069',
      '80980556',
      '80920034',
      '80980514',
      '71480114',
      '40730531',
      '82220001',
      '81170016',
      '80480163'
    );
    $UePlenasAddSpeciality = (in_array($sie, $arrUePlenasAddSpeciality))?true:false;

    // Impresion de libretas
    $tipoUE = $this->get('funciones')->getTipoUE($sie,$gestion);
    $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);

    $imprimirLibreta = false;
    $estadosPermitidosImprimir = array(4,5,11,55);

    if($tipoUE){
        /*
         *  GESTION ACTUAL
         */
        if($gestion == $this->session->get('currentyear')){
            // Unidades educativas plenas, modulares y humanisticas
            if(in_array($tipoUE['id'], array(1,3,5)) and $operativo >= 2){
                $imprimirLibreta = true;
            }
            // Unidades educativas tecnicas tecnologicas
            if(in_array($tipoUE['id'], array(2)) and $operativo >= 4){
                $imprimirLibreta = true;
            }
        }


        /*
         * GESTIONES PASADAS
         */
        if($gestion < $this->session->get('currentyear')){
            // Para unidades educativas en gestiones pasadas
            if(in_array($tipoUE['id'], array(1,2,3,5)) and $gestion >= 2014 and $gestion < $this->session->get('currentyear') and $operativo >= 4){
                $imprimirLibreta = true;
            }

            // PAra ues tecnicas tecnologicas
            if(in_array($tipoUE['id'], array(2)) and $gestion >= 2011){
                $imprimirLibreta = true;
            }

            // Caso especial de la unidad educativa AMERINST
            if($sie == '80730460' and $gestion <= 2015){
                $imprimirLibreta = false;
                if($gestion == 2014 and $nivel == 13 and $grado >= 4 and $paralelo >= 6){
                    $imprimirLibreta = true;
                }
                if($gestion == 2015 and $nivel == 13 and $grado >= 5 and $paralelo >= 6){
                    $imprimirLibreta = true;
                }
            }
        }
    }

    return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:seeStudents.html.twig', array(
                'objStudents' => $objStudents,
                'sie' => $sie,
                'turno' => $turno,
                'nivel' => $nivel,
                'grado' => $grado,
                'paralelo' => $paralelo,
                'gestion' => $gestion,
                'aData' => $aData,
                'gradoname' => $gradoname,
                'paraleloname' => $paraleloname,
                // 'nivelname' => $nivelname,
                'form' => $this->createFormStudentInscription($infoUe)->createView(),
                'infoUe' => $infoUe,
                'exist' => $exist,
                'itemsUe'=>$itemsUe,
                'ciclo'=>$ciclo,
                'operativo'=>$operativo,
                'UePlenasAddSpeciality' => $UePlenasAddSpeciality,
                'imprimirLibreta'=>$imprimirLibreta,
                'estadosPermitidosImprimir'=>$estadosPermitidosImprimir
    ));
      }

      public function cerrarRudeAction(Request $request){
        //get the values
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');

        //conexion to DB
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //look for the record on the DB to this sie and gestion
        $objinstitucioneducativaOperativoLogExist = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLog')->findOneBy(array(
          'institucioneducativa' => $sie,
          'gestionTipoId'  => $gestion,
          'institucioneducativaOperativoLogTipo' => 4
        ));
        // dump($objinstitucioneducativaOperativoLogExist);

        if(!$objinstitucioneducativaOperativoLogExist){

          $query = $em->getConnection()->prepare('select * from sp_validacion_regular_socioeconomicos(:gestion, :sie)');
          $query->bindValue(':gestion', $gestion);
          $query->bindValue(':sie', $sie);
          $query->execute();
          $inconsistenciaRude = $query->fetchAll();
          if(!$inconsistenciaRude){

            //get values
          $form = array(
            'sie'=>$sie,
            'gestion' => $gestion,
            'bimestre' => 1
          );
          //save in donwload File Control
          //$objinstitucioneducativaOperativoLog = $this->saveInstitucioneducativaOperativoLog($form);
           try {
            //save the log data
            $institucioneducativaOperativoLog = new InstitucioneducativaOperativoLog();
            $institucioneducativaOperativoLog->setInstitucioneducativaOperativoLogTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLogTipo')->find(4));
            $institucioneducativaOperativoLog->setGestionTipoId($form['gestion']);
            $institucioneducativaOperativoLog->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find($form['bimestre']+1));
            $institucioneducativaOperativoLog->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']));
            $institucioneducativaOperativoLog->setInstitucioneducativaSucursal(0);
            $institucioneducativaOperativoLog->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($form['bimestre']));
            $institucioneducativaOperativoLog->setDescripcion('...');
            $institucioneducativaOperativoLog->setEsexitoso('t');
            $institucioneducativaOperativoLog->setEsonline('t');
            $institucioneducativaOperativoLog->setUsuario($this->session->get('userId'));
            $institucioneducativaOperativoLog->setFechaRegistro(new \DateTime('now'));
            $institucioneducativaOperativoLog->setClienteDescripcion($_SERVER['HTTP_USER_AGENT']);
            $em->persist($institucioneducativaOperativoLog);
            $em->flush();
            $em->getConnection()->commit();
            // dump($institucioneducativaOperativoLog);
            // return 'krlos';
            return new Response('Datos guardados correctamente... ');
           } catch (Exception $e) {
             $em->getConnection()->rollback();
             //   echo 'Excepción capturada: ', $ex->getMessage(), "\n";
           }
         }else{
           //see inconsistenciaRude
           return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:inconsistenciaRude.html.twig', array(
                       'inconsistenciaRude' => $inconsistenciaRude,
           ));
          }

          dump($objinstitucioneducativaOperativoLog);
        }else{
          // return new Response('Proceso anteriormente realizado... ');
          $response = new Response('Proceso anteriormente realizado... '.$sie, Response::HTTP_OK);

          return $response;
          die;
        }
        die('...');

      }


    public function cerrarSextoGradoAction(Request $request){
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        $em = $this->getDoctrine()->getManager();

        $registroConsolidacion = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
            'unidadEducativa'=>$sie,
            'gestion'=>$gestion
        ));

        if($registroConsolidacion){
            $registroConsolidacion->setCierreSextosec(true);
            $em->flush();
        }

        // Llamar funcion para cerrar sexto grado
            
        return new Response('Curso cerrado con exito!!');

    }

}
