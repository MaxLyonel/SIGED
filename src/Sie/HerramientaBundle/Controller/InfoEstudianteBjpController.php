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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;
use Sie\AppWebBundle\Entity\BonojuancitoInstitucioneducativaCursoValidacion;
use Sie\AppWebBundle\Entity\BonojuancitoEstudianteValidacion;

/**
 * InfoEstudianteBjp controller.
 *
 */
class InfoEstudianteBjpController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->aCursos = $this->fillCursos();
    }

    /**
     * list of request
     *
     */
    public function indexAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $pagoTipo = 0; //En tiempo
        if($request->getMethod()=='POST'){
            $pagoTipo = 1; //Rezagados
        }

        $em = $this->getDoctrine()->getManager();

        if ($pagoTipo == 0) {
            $bjp = $em->getRepository('SieAppWebBundle:BonojuancitoInstitucioneducativaValidacion')->findBy(array(
                'institucioneducativaId' => $this->session->get('ie_id'),
                'gestionTipoId' => $this->session->get('currentyear')-1,
                'esactivo' => 't'
            ));
    
            if($bjp) {
                return $this->redirect($this->generateUrl('sie_app_web_close_modules_bjp', array(
                    'sie' => $this->session->get('ie_id'),
                    'gestion' => $this->session->get('currentyear')-1,
                )));
            }
        }

        $arrSieInfo = array('id'=>$this->session->get('ie_id'), 'datainfo'=>$this->session->get('ie_nombre'));

        /*
        * verificamos si tiene tuicion
        */
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
        $query->bindValue(':sie', $arrSieInfo['id']);
        $query->bindValue(':rolId', $this->session->get('roluser'));
        $query->execute();
        $aTuicion = $query->fetchAll();
        $institucion = $arrSieInfo['id'];
        $gestion = $this->session->get('currentyear')-1;

        $repository = $em->getRepository('SieAppWebBundle:BonojuancitoInstitucioneducativaCursoValidacion');

        $query = $repository->createQueryBuilder('biv')
            ->select('biv')
            ->where('biv.institucioneducativaId = :sie')
            ->andwhere('biv.gestionTipo = :gestion')
            ->setParameter('sie', $institucion)
            ->setParameter('gestion', $this->session->get('currentyear')-1)
            ->addOrderBy('biv.turnoTipoId, biv.nivelTipoId, biv.gradoTipoId, biv.paralelo')
            ->getQuery();

        $objUeducativa = $query->getResult();

        $exist = true;
        $aInfoUnidadEductiva = array();
        if ($objUeducativa) {
            foreach ($objUeducativa as $uEducativa) {
                //get the literal data of unidad educativa
                $sinfoUeducativa = serialize(array(
                    'ueducativaInfo' => array('nivel' => $uEducativa->getNivel(), 'grado' => $uEducativa->getGrado(), 'paralelo' => $uEducativa->getParalelo(), 'turno' => $uEducativa->getTurno()),
                    'ueducativaInfoId' => array('paraleloId' => $uEducativa->getParalelo(), 'turnoId' => $uEducativa->getTurnoTipoId(), 'nivelId' => $uEducativa->getNivelTipoId(), 'gradoId' => $uEducativa->getGradoTipoId()),
                    'requestUser' => array('sie' => $institucion, 'gestion' => $gestion, 'pagoTipo' => $pagoTipo)
                ));
                //send the values to the next steps
                $aInfoUnidadEductiva[$uEducativa->getTurno()][$uEducativa->getNivel()][$uEducativa->getGrado()][$uEducativa->getParalelo()] = array('infoUe' => $sinfoUeducativa);
            }
        } else {
            $message = 'No existe información de la Unidad Educativa para la gestión seleccionada ó Código SIE no existe ';
            $this->addFlash('noResult', $message);
            $exist = false;
        }

        $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucion);
        $odataUedu = $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucion);

        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteBjp:index.html.twig', array(
            'aInfoUnidadEductiva' => $aInfoUnidadEductiva,
            'sie' => $institucion,
            'gestion' => $gestion,
            'objUe' => $objUe,
            'exist' => $exist,
            'odataUedu' => $odataUedu,
            'pagoTipo' => $pagoTipo
        ));
    }

    public function getStudentsAction(Request $request) {
        //get db connexion
        $em = $this->getDoctrine()->getManager();
        //get the info ue
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);

        $sie = $aInfoUeducativa['requestUser']['sie'];
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
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
        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteBjp:students.html.twig', array(
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

        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteBjp:inscriptionWithRude.html.twig', array(
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

        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteBjp:rudeinscription.html.twig', array(
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

        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteBjp:exeInscription.html.twig', array(
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
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
        $gestion = $aInfoUeducativa['requestUser']['gestion'];
        $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
        $gradoname = $aInfoUeducativa['ueducativaInfo']['grado'];
        $paraleloname = $aInfoUeducativa['ueducativaInfo']['paralelo'];
        $pagoTipo = $aInfoUeducativa['requestUser']['pagoTipo'];

        //get db connexion
        $em = $this->getDoctrine()->getManager();
        //get turnos
        //$objStudents = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($iecId, '5');
        //get th position of next level

        //$positionCurso = $this->getCourse($nivel, $ciclo, $grado, '5');

        //$dataNextLevel = explode('-', $this->aCursos[$positionCurso]);

        //get next level data
        $objNextCurso = $em->getRepository('SieAppWebBundle:BonojuancitoInstitucioneducativaCursoValidacion')->findOneBy(array(
            'institucioneducativaId' => $sie,
            'nivelTipoId' => $nivel,
            'gradoTipoId' => $grado,
            'paralelo' => $paralelo,
            'turnoTipoId' => $turno,
            'gestionTipo' => $gestion
        ));

        $exist = true;
        $objStudents = array();
        $aData = array();
        //check if the data exist
        if ($objNextCurso) {
            $nivelArray = array($nivel);
            if($nivel != 12 && $nivel != 13){
                $nivelArray = array(16,$nivel);
            }

            //niveles de la UE
            $repository = $em->getRepository('SieAppWebBundle:BonojuancitoEstudianteValidacion');

            $query = $repository->createQueryBuilder('bev')
                ->select('bev')
                ->where('bev.institucioneducativaId = :sie')
                ->andWhere('bev.turnoTipoId = :turno')
                ->andWhere('bev.nivelTipoId in (:nivel)')
                ->andWhere('bev.gradoTipoId = :grado')
                ->andWhere('bev.paralelo = :paralelo')
                ->andWhere('bev.gestionTipoId = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('turno', $turno)
                ->setParameter('nivel', $nivelArray)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('gestion', $gestion)
                ->addOrderBy('bev.paterno, bev.materno, bev.nombre')
                ->getQuery();

            $objStudents = $query->getResult();
            $aData = serialize(array('sie' => $sie, 'nivel' => $nivel, 'grado' => $grado, 'paralelo' => $paralelo, 'turno' => $turno, 'gestion' => $gestion, 'iecNextLevl' => $objNextCurso->getId()));
        } else {
            $message = 'No existen estudiantes en el turno/nivel/grado/paralelo seleccionados...';
            $this->addFlash('warninsueall', $message);
            $exist = false;
        }

        // Para el centralizador
        $itemsUe = $aInfoUeducativa['ueducativaInfo']['nivel'].",".$aInfoUeducativa['ueducativaInfo']['grado'].",".$aInfoUeducativa['ueducativaInfo']['paralelo'];

        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteBjp:seeStudents.html.twig', array(
            'objStudents' => $objStudents,
            'sie' => $sie,
            'pagoTipo' => $pagoTipo,
            'turno' => $turno,
            'nivel' => $nivel,
            'grado' => $grado,
            'paralelo' => $paralelo,
            'gestion' => $gestion,
            'aData' => $aData,
            'gradoname' => $gradoname,
            'paraleloname' => $paraleloname,
            'form' => $this->createFormStudentInscription($infoUe)->createView(),
            'infoUe' => $infoUe,
            'exist' => $exist,
            'itemsUe'=>$itemsUe,
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
        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteBjp:open.html.twig', array());
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
        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteBjp:turnos.html.twig', array(
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

    public function verificaPagoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $verificarPago = $request->get('verificarPago');

        $sie = $verificarPago['sie'];
        $turnoTipoId = $verificarPago['turno'];
        $nivelTipoId = $verificarPago['nivel'];
        $gradoTipoId = $verificarPago['grado'];
        $paralelo = $verificarPago['paralelo'];
        $pagoTipo = $verificarPago['pagoTipo'];
        $gestion = $this->session->get('currentyear')-1;

        $estudiantesBjp = $em->getRepository('SieAppWebBundle:BonojuancitoEstudianteValidacion')->findBy(array('institucioneducativaId' => $sie, 'turnoTipoId'=> $turnoTipoId, 'nivelTipoId' => $nivelTipoId, 'gradoTipoId' => $gradoTipoId, 'paralelo' => $paralelo, 'gestionTipoId' => $gestion, 'pagoTipoId' => $pagoTipo, 'esPagado' => 't'));

        foreach($estudiantesBjp as $item) {
            $item->setEsPagado('f');
            $item->setPagoTipoId($pagoTipo);
            $em->persist($item);
            $em->flush();
        }
        
        $contador = 0;
        if($verificarPago){
            foreach($verificarPago as $key=>$value) {
                $contador++;
                if($contador > 6){
                    $estudianteBjp = $em->getRepository('SieAppWebBundle:BonojuancitoEstudianteValidacion')->findOneById($key);
                    $estudianteBjp->setEsPagado('t');
                    $estudianteBjp->setPagoTipoId($pagoTipo);
                    $estudianteBjp->setFechaRegistro(new \DateTime('now'));
                    $em->persist($estudianteBjp);
                    $em->flush();
                }
            }
        }

        $message = 'Se realizó la verificación satisfactoriamente.';
        $this->addFlash('msgOk', $message);
        return $this->redirect($this->generateUrl('herramienta_info_estudiante_bjp_index'));
    }

    private function formNuevoEstudiante() {
        $niveles = array('12' => 'Primaria Comunitaria Vocacional', '13' => 'Secundaria Comunitaria Productiva', '16' => 'Educación Especial', '401' => 'Independencia Personal', '402' => 'Independencia Social', '403' => 'Educación Inicial', '404' => 'Educación Primaria', '405' => 'Formación Técnica', '410' => 'Servicios', '411' => 'Programas', '999' => 'Ninguno');
        $grados = array('0' => 'Guarderia', '1' => 'Primero', '2' => 'Segundo', '3' => 'Tercero', '4' => 'Cuarto', '5' => 'Quinto', '6' => 'Sexto', '99' => 'Sin dato');
        $paralelos = array('A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E', 'F' => 'F', 'G' => 'G', 'H' => 'H', 'I' => 'I', 'J' => 'J', 'K' => 'K', 'L' => 'L');

        return $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_info_estudiante_bjp_verifica_pago_nuevo_save'))
            ->add('turno', 'entity', array('label' => 'Turno', 'required' => true, 'class' => 'SieAppWebBundle:TurnoTipo', 'property' => 'turno', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('nivel', 'choice', array('label' => 'Nivel', 'choices' => $niveles, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('grado', 'choice', array('label' => 'Grado', 'choices' => $grados, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('paralelo', 'choice', array('label' => 'Paralelo', 'choices' => $paralelos, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('rude', 'text', array('label' => 'Rude', 'required' => false, 'attr' => array('class' => 'form-control', 'placeholder' => 'Rude', 'maxlength' => 18, 'pattern' => '[A-Z0-9]{13,18}')))
            ->add('sie', 'text', array('label' => 'Código SIE', 'attr' => array('value' => $this->session->get('ie_id'), 'class' => 'form-control', 'placeholder' => 'Código SIE', 'maxlength' => 9, 'pattern' => '[0-9]{1,9}', 'readonly' => true)))
            ->add('carnet', 'text', array('label' => 'Carnet de Identidad', 'required' => false, 'attr' => array('class' => 'form-control', 'placeholder' => 'Carnet de Identidad', 'maxlength' => 15, 'pattern' => '[A-Z0-9]{1,15}')))
            ->add('paterno', 'text', array('label' => 'Apellido Paterno', 'required' => false, 'attr' => array('class' => 'form-control', 'placeholder' => 'Apellido Paterno', 'maxlength' => 50, 'pattern' => '[A-Z\Ññ ]{1,50}')))
            ->add('materno', 'text', array('label' => 'Apellido Materno', 'required' => false, 'attr' => array('class' => 'form-control', 'placeholder' => 'Apellido Materno', 'maxlength' => 50, 'pattern' => '[A-Z\Ññ ]{1,50}')))
            ->add('nombre', 'text', array('label' => 'Nombre(s)', 'required' => true, 'attr' => array('class' => 'form-control', 'placeholder' => 'Nombre(s)', 'maxlength' => 50, 'pattern' => '[A-Z\Ññ ]{1,50}')))
            ->add('fechaNac', 'date', array('label' => 'Fecha de Nacimiento', 'required' => true, 'format' => 'dd-MM-yyyy', 'years' => range(date('Y') - 50, date('Y')), 'data' => new \DateTime('now'), 'attr' => array('class' => 'form-control')))
            ->add('genero', 'entity', array('label' => 'Género', 'required' => false, 'class' => 'SieAppWebBundle:GeneroTipo', 'property' => 'genero', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('guardar', 'submit', array('label' => 'Registrar Estudiante', 'attr' => array('class' => 'btn btn-success')))
            ->getForm();
    }


    public function verificaPagoNuevoAction(Request $request) {
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $form = $request->get('verificarPagoNuevo');

        $arrSieInfo = array('id'=>$this->session->get('ie_id'), 'datainfo'=>$this->session->get('ie_nombre'));

        $institucion = $arrSieInfo['id'];
        $gestion = $this->session->get('currentyear')-1;

        $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucion);
        $odataUedu = $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucion);

        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteBjp:nuevo_estudiante_bjp.html.twig', array(
                    'form' => $this->formNuevoEstudiante()->createView(),
                    'odataUedu' => $odataUedu,
                    'sie' => $institucion,
                    'gestion' => $gestion,
                    'pagoTipo' => $form['pagoTipo']
        ));
    }

    public function verificaPagoNuevoGuardarAction(Request $request) {
        
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $form = $request->get('form');

        $estudianteValidacion = $em->getRepository('SieAppWebBundle:BonojuancitoEstudianteValidacion')->findBy(array(
            'codigoRude' => $form['rude'],
            'institucioneducativaId' => $form['sie'],
            'gestionTipoId' => $this->session->get('currentyear')-1
        ));
        
        if ($estudianteValidacion) {
            $message = 'La/El estudiante ya cuenta con registro para su validación o ya fue validado.';
            $this->addFlash('msgError', $message);
            return $this->redirect($this->generateUrl('herramienta_info_estudiante_bjp_index'));
        } else {
            $objTurno = $em->getRepository('SieAppWebBundle:TurnoTipo')->find($form['turno']);
            $objNivel = $em->getRepository('SieAppWebBundle:NivelTipo')->find($form['nivel']);
            $objGrado = $em->getRepository('SieAppWebBundle:GradoTipo')->find($form['grado']);
            
            switch($form['genero']){
                case 1:
                    $genero = 'Masculino';
                    break;
                case 2:
                    $genero = 'Femenino';
                    break;
                default:
                    $genero = 'Sin Dato';
                    break;
            }
        
            $bjpnew = new BonojuancitoEstudianteValidacion();
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('bonojuancito_estudiante_validacion');")->execute();
            $bjpnew->setInstitucioneducativaId($form['sie']);
            $bjpnew->setTurnoTipoId($form['turno']);
            $bjpnew->setTurno($objTurno->getTurno());
            $bjpnew->setNivelTipoId($form['nivel']);
            $bjpnew->setNivel($objNivel->getNivel());
            $bjpnew->setGradoTipoId($form['grado']);
            $bjpnew->setGrado($objGrado->getGrado());
            $bjpnew->setEstadomatricula('EFECTIVO');
            $bjpnew->setParalelo($form['paralelo']);
            $bjpnew->setCodigoRude(mb_strtoupper($form['rude'], 'UTF-8'));
            $bjpnew->setCarnetIdentidad(mb_strtoupper($form['carnet'], 'UTF-8'));
            $bjpnew->setPaterno(mb_strtoupper($form['paterno'], 'UTF-8'));
            $bjpnew->setMaterno(mb_strtoupper($form['materno'], 'UTF-8'));
            $bjpnew->setNombre(mb_strtoupper($form['nombre'], 'UTF-8'));
            $bjpnew->setEsPagado('t');
            $bjpnew->setEsNuevo('t');
            $bjpnew->setPagoTipoId($form['pagoTipo']);
            $bjpnew->setFechaNacimiento(new \DateTime($form['fechaNac']['year'].'-'.$form['fechaNac']['month'].'-'.$form['fechaNac']['day']));
            $bjpnew->setGenero($genero);
            $bjpnew->setFechaRegistro(new \DateTime('now'));
            $bjpnew->setGestionTipoId($this->session->get('currentyear')-1);

            $em->persist($bjpnew);
            $em->flush();

            $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);

            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('bonojuancito_institucioneducativa_curso_validacion');")->execute();
            $bjpieduca = new BonojuancitoInstitucioneducativaCursoValidacion();
            $bjpieduca->setInstitucioneducativaId($form['sie']);
            $bjpieduca->setTurnoTipoId($form['turno']);
            $bjpieduca->setTurno($objTurno->getTurno());
            $bjpieduca->setNivelTipoId($form['nivel']);
            $bjpieduca->setNivel($objNivel->getNivel());
            $bjpieduca->setGradoTipoId($form['grado']);
            $bjpieduca->setGrado($objGrado->getGrado());
            $bjpieduca->setParalelo($form['paralelo']);
            $bjpieduca->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')-1));

            $em->persist($bjpieduca);
            $em->flush();

            $message = 'Se realizó el registro satisfactoriamente.';
            $this->addFlash('msgOk', $message);
            return $this->redirect($this->generateUrl('herramienta_info_estudiante_bjp_index'));
        }
    }

    /* 
    * Nuevo estudiante bjp por rude 
    */
    private function formbjpSearchRude() {
        // $niveles = array('12' => 'Primaria Comunitaria Vocacional', '13' => 'Secundaria Comunitaria Productiva', '16' => 'Educación Especial', '401' => 'Independencia Personal', '402' => 'Independencia Social', '403' => 'Educación Inicial', '404' => 'Educación Primaria', '405' => 'Formación Técnica', '410' => 'Servicios', '411' => 'Programas', '999' => 'Ninguno');
        // $grados = array('0' => 'Guarderia', '1' => 'Primero', '2' => 'Segundo', '3' => 'Tercero', '4' => 'Cuarto', '5' => 'Quinto', '6' => 'Sexto', '99' => 'Sin dato');
        // $paralelos = array('A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E', 'F' => 'F', 'G' => 'G', 'H' => 'H', 'I' => 'I', 'J' => 'J', 'K' => 'K', 'L' => 'L');

        return $this->createFormBuilder()
            ->setAction($this->generateUrl('herramienta_info_estudiante_bjp_nuevo_rude_search'))
            // ->add('turno', 'entity', array('label' => 'Turno', 'required' => true, 'class' => 'SieAppWebBundle:TurnoTipo', 'property' => 'turno', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            // ->add('nivel', 'choice', array('label' => 'Nivel', 'choices' => $niveles, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            // ->add('grado', 'choice', array('label' => 'Grado', 'choices' => $grados, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            // ->add('paralelo', 'choice', array('label' => 'Paralelo', 'choices' => $paralelos, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('rude', 'text', array('label' => 'Código Rude', 'required' => true, 'attr' => array('class' => 'form-control', 'placeholder' => 'Rude', 'maxlength' => 18, 'pattern' => '[A-Z0-9]{13,18}')))
            // ->add('sie', 'text', array('label' => 'Código SIE', 'attr' => array('value' => $this->session->get('ie_id'), 'class' => 'form-control', 'placeholder' => 'Código SIE', 'maxlength' => 9, 'pattern' => '[0-9]{1,9}', 'readonly' => true)))
            // ->add('carnet', 'text', array('label' => 'Carnet de Identidad', 'required' => false, 'attr' => array('class' => 'form-control', 'placeholder' => 'Carnet de Identidad', 'maxlength' => 15, 'pattern' => '[A-Z0-9]{1,15}')))
            // ->add('paterno', 'text', array('label' => 'Apellido Paterno', 'required' => false, 'attr' => array('class' => 'form-control', 'placeholder' => 'Apellido Paterno', 'maxlength' => 50, 'pattern' => '[A-Z\Ññ ]{1,50}')))
            // ->add('materno', 'text', array('label' => 'Apellido Materno', 'required' => false, 'attr' => array('class' => 'form-control', 'placeholder' => 'Apellido Materno', 'maxlength' => 50, 'pattern' => '[A-Z\Ññ ]{1,50}')))
            // ->add('nombre', 'text', array('label' => 'Nombre(s)', 'required' => true, 'attr' => array('class' => 'form-control', 'placeholder' => 'Nombre(s)', 'maxlength' => 50, 'pattern' => '[A-Z\Ññ ]{1,50}')))
            // ->add('fechaNac', 'date', array('label' => 'Fecha de Nacimiento', 'required' => true, 'format' => 'dd-MM-yyyy', 'years' => range(date('Y') - 50, date('Y')), 'data' => new \DateTime('now'), 'attr' => array('class' => 'form-control')))
            // ->add('genero', 'entity', array('label' => 'Género', 'required' => false, 'class' => 'SieAppWebBundle:GeneroTipo', 'property' => 'genero', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
            ->add('guardar', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-success')))
            ->getForm();
    }


    public function bjpNuevoRudeAction(Request $request) {
        
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $form = $request->get('verificarPagoNuevo');

        $arrSieInfo = array('id'=>$this->session->get('ie_id'), 'datainfo'=>$this->session->get('ie_nombre'));

        $institucion = $arrSieInfo['id'];
        $gestion = $this->session->get('currentyear')-1;

        $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucion);
        $odataUedu = $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($institucion);

        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteBjp:nuevo_estudiante_rude_bjp.html.twig', array(
                    'form' => $this->formbjpSearchRude()->createView(),
                    'odataUedu' => $odataUedu,
                    'sie' => $institucion,
                    'gestion' => $gestion,
                    'pagoTipo' => $form['pagoTipo']
        ));
    }

    public function bjpNuevoRudeSearchAction(Request $request) {

        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $request->get('form');
        $em = $this->getDoctrine()->getManager();

        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array(
            'codigoRude' => $form['rude']
        ));

        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['sie']);

        if ($estudiante) {
            $repository = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');

            $query = $repository->createQueryBuilder('ei')
                ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.gestionTipo = :gestion')
                ->andWhere('e.id = :estudiante')
                ->andWhere('ei.estadomatriculaTipo in (:matricula)')
                ->setParameter('sie', $form['sie'])
                ->setParameter('gestion', $form['gestion'])
                ->setParameter('estudiante', $estudiante->getId())
                ->setParameter('matricula', array(0,4,5,11,55))
                ->getQuery();

            $estudianteInscripcion = $query->getOneOrNullResult();
            
            if ($estudianteInscripcion) {
                return $this->render($this->session->get('pathSystem') . ':InfoEstudianteBjp:nuevo_estudiante_bjp.html.twig', array(
                    'estudianteInscripcion' => $estudianteInscripcion,
                    'pagoTipo' => $form['pagoTipo'],
                    'gestion' => $form['gestion'],
                    'institucioneducativa' => $institucioneducativa
                ));
            } else {
                $message = 'El estudiante no presenta inscripción en la gestión actual, verifique e intente nuevamente.';
                $this->addFlash('msgError', $message);
                return $this->redirect($this->generateUrl('herramienta_info_estudiante_bjp_index'));
            }
        } else {
            $message = 'No se encontró el código RUDE buscado, verifique e intente nuevamente.';
            $this->addFlash('msgError', $message);
            return $this->redirect($this->generateUrl('herramienta_info_estudiante_bjp_index'));
        }
        
    }

    public function bjpNuevoRudeGuardarAction(Request $request) {
        
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $form = $request->get('formNew');
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($form['inscripcionId']);
        $curso = $inscripcion->getInstitucioneducativaCurso();        
        $estudiante = $inscripcion->getEstudiante();

        $estudianteValidacion = $em->getRepository('SieAppWebBundle:BonojuancitoEstudianteValidacion')->findBy(array(
            'codigoRude' => $estudiante->getCodigoRude(),
            'institucioneducativaId' => $form['sie'],
            'gestionTipoId' => $this->session->get('currentyear')-1
        ));
        
        if ($estudianteValidacion) {
            $message = 'La/El estudiante ya cuenta con registro para su validación o ya fue validado.';
            $this->addFlash('msgError', $message);
            return $this->redirect($this->generateUrl('herramienta_info_estudiante_bjp_index'));
        } else {
            
            $objTurno = $curso->getTurnoTipo();
            $objNivel = $curso->getNivelTipo();
            $objGrado = $curso->getGradoTipo();
            $objParalelo = $curso->getParaleloTipo();
            
            switch($estudiante->getGeneroTipo()->getId()){
                case 1:
                    $genero = 'Masculino';
                    break;
                case 2:
                    $genero = 'Femenino';
                    break;
                default:
                    $genero = 'Sin Dato';
                    break;
            }
        
            $bjpnew = new BonojuancitoEstudianteValidacion();
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('bonojuancito_estudiante_validacion');")->execute();
            $bjpnew->setInstitucioneducativaId($form['sie']);
            $bjpnew->setTurnoTipoId($objTurno->getId());
            $bjpnew->setTurno($objTurno->getTurno());
            $bjpnew->setNivelTipoId($objNivel->getId());
            $bjpnew->setNivel($objNivel->getNivel());
            $bjpnew->setGradoTipoId($objGrado->getid());
            $bjpnew->setGrado($objGrado->getGrado());
            $bjpnew->setParalelo($objParalelo->getParalelo());
            $bjpnew->setEstadomatriculaTipoId($inscripcion->getEstadomatriculaTipo()->getId());
            $bjpnew->setEstadomatricula('EFECTIVO');
            $bjpnew->setEstudianteInscripcionId($inscripcion->getId());
            $bjpnew->setCodigoRude(mb_strtoupper($estudiante->getCodigoRude(), 'UTF-8'));
            $bjpnew->setCarnetIdentidad(mb_strtoupper($estudiante->getCarnetIdentidad(), 'UTF-8'));
            $bjpnew->setPaterno(mb_strtoupper($estudiante->getPaterno(), 'UTF-8'));
            $bjpnew->setMaterno(mb_strtoupper($estudiante->getMaterno(), 'UTF-8'));
            $bjpnew->setNombre(mb_strtoupper($estudiante->getNombre(), 'UTF-8'));
            $bjpnew->setEsPagado('t');
            $bjpnew->setEsNuevo('t');
            $bjpnew->setPagoTipoId($form['pagoTipo']);
            $bjpnew->setFechaNacimiento($estudiante->getFechaNacimiento());
            $bjpnew->setGenero($genero);
            $bjpnew->setFechaRegistro(new \DateTime('now'));
            $bjpnew->setGestionTipoId($this->session->get('currentyear')-1);
            
            $em->persist($bjpnew);
            $em->flush();
            
            $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);

            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('bonojuancito_institucioneducativa_curso_validacion');")->execute();
            $bjpieduca = new BonojuancitoInstitucioneducativaCursoValidacion();
            $bjpieduca->setInstitucioneducativaId($form['sie']);
            $bjpieduca->setTurnoTipoId($objTurno->getId());
            $bjpieduca->setTurno($objTurno->getTurno());
            $bjpieduca->setNivelTipoId($objNivel->getId());
            $bjpieduca->setNivel($objNivel->getNivel());
            $bjpieduca->setGradoTipoId($objGrado->getid());
            $bjpieduca->setGrado($objGrado->getGrado());
            $bjpieduca->setParalelo($objParalelo->getParalelo());
            $bjpieduca->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')-1));
            
            $em->persist($bjpieduca);
            $em->flush();

            $message = 'Se realizó el registro satisfactoriamente.';
            $this->addFlash('msgOk', $message);
            return $this->redirect($this->generateUrl('herramienta_info_estudiante_bjp_index'));
        }
    }

    /* 
    * reportes 
    */

    public function repDdjjAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        if($request->get('pagoTipo') == 0) {
            $bjp = $em->getRepository('SieAppWebBundle:BonojuancitoInstitucioneducativaValidacion')->findOneBy(array(
                'institucioneducativaId' => $this->session->get('ie_id'),
                'gestionTipoId' => $this->session->get('currentyear')-1,
                'esactivo' => 'f'
            ));
            $reporte = 'reg_dj_bonojuancitopinto_estadistica_pagados_norezagados_v1_ma';
        } else {
            $bjp = $em->getRepository('SieAppWebBundle:BonojuancitoInstitucioneducativaValidacion')->findOneBy(array(
                'institucioneducativaId' => $this->session->get('ie_id'),
                'gestionTipoId' => $this->session->get('currentyear')-1,
                'esactivo' => 't'
            ));
            $reporte = 'reg_dj_bonojuancitopinto_estadistica_pagados_rezagados_v1_ma';
        }
        
        //check if the date is set the first time
        if($bjp->getObs()>0){
          $bjp->setFechaFinEdit(new \DateTime('now'));
        }else {
          $bjp->setFechaFinVal(new \DateTime('now'));
        }
        $bjp->setObs(($bjp->getObs())>0?(int)$bjp->getObs()+1:1);
        $bjp->setEsactivo('t');
        $em->persist($bjp);
        $em->flush();

        $arrSieInfo = array('id'=>$this->session->get('ie_id'), 'datainfo'=>$this->session->get('ie_nombre'));
        $arch = 'DECLARACION_JURADA_'.$arrSieInfo['id'].'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') .$reporte.'.rptdesign&ue='.$arrSieInfo['id'].'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    public function printDdjjAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $arrSieInfo = array('id'=>$this->session->get('ie_id'), 'datainfo'=>$this->session->get('ie_nombre'));
        
        $gestion = $this->session->get('currentyear')-1;
        $arch = 'DECLARACION_JURADA_'.$arrSieInfo['id'].'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_dj_bonojuancitopinto_estadistica_pagados_norezagados_v1_ma.rptdesign&ue='.$arrSieInfo['id'].'&gestion='.$gestion.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function repBjpDptoAction(Request $request) {
        $form = $request->get('form');

        $gestion = $this->session->get('currentyear')-1;
        $arch = 'REPORTE_BJP_DPTO_'.$form['dpto'].'_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_dj_bonojuancitopinto_estadistica_pagados_norezagados_v1_ma.rptdesign&dpto='.$form['dpto'].'&gestion='.$gestion.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function removeStudentsAction(Request $request) {

        //get the info ue
        $infoUe = $request->get('infoUe');
        $idInscription = $request->get('idInscription');
        $aInfoUeducativa = unserialize($infoUe);

        //get the values throght the infoUe
        $sie = $aInfoUeducativa['requestUser']['sie'];
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
        $gestion = $aInfoUeducativa['requestUser']['gestion'];
        $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
        $gradoname = $aInfoUeducativa['ueducativaInfo']['grado'];
        $paraleloname = $aInfoUeducativa['ueducativaInfo']['paralelo'];

        //get db connexion
        $em = $this->getDoctrine()->getManager();
        //get turnos
        //$objStudents = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($iecId, '5');
        //get th position of next level

        //$positionCurso = $this->getCourse($nivel, $ciclo, $grado, '5');

        //$dataNextLevel = explode('-', $this->aCursos[$positionCurso]);
        //remove the idInscription selected
        try {
          $objInscriptionBjp = $em->getRepository('SieAppWebBundle:BonojuancitoEstudianteValidacion')->find($idInscription);
          $em->remove($objInscriptionBjp);
          $em->flush();
        } catch (\Exception $e) {
          die('error'.$e);
        }

        //get next level data
        $objNextCurso = $em->getRepository('SieAppWebBundle:BonojuancitoInstitucioneducativaCursoValidacion')->findOneBy(array(
            'institucioneducativaId' => $sie,
            'nivelTipoId' => $nivel,
            'gradoTipoId' => $grado,
            'paralelo' => $paralelo,
            'turnoTipoId' => $turno,
            'gestionTipo' => $gestion
        ));

        $exist = true;
        $objStudents = array();
        $aData = array();
        //check if the data exist
        if ($objNextCurso) {
            $nivelArray = array($nivel);
            if($nivel != 12 && $nivel != 13){
                $nivelArray = array(16,$nivel);
            }

            //find the levels from UE
            $repository = $em->getRepository('SieAppWebBundle:BonojuancitoEstudianteValidacion');

            $query = $repository->createQueryBuilder('bev')
                ->select('bev')
                ->where('bev.institucioneducativaId = :sie')
                ->andWhere('bev.turnoTipoId = :turno')
                ->andWhere('bev.nivelTipoId in (:nivel)')
                ->andWhere('bev.gradoTipoId = :grado')
                ->andWhere('bev.paralelo = :paralelo')
                ->andWhere('bev.gestionTipoId = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('turno', $turno)
                ->setParameter('nivel', $nivelArray)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('gestion', $gestion)
                ->addOrderBy('bev.paterno, bev.materno, bev.nombre')
                ->getQuery();

            $objStudents = $query->getResult();
            $aData = serialize(array('sie' => $sie, 'nivel' => $nivel, 'grado' => $grado, 'paralelo' => $paralelo, 'turno' => $turno, 'gestion' => $gestion, 'iecNextLevl' => $objNextCurso->getId()));
        } else {
            $message = 'No existen estudiantes en el turno/nivel/grado/paralelo seleccionados...';
            $this->addFlash('warninsueall', $message);
            $exist = false;
        }

        // Para el centralizador
        $itemsUe = $aInfoUeducativa['ueducativaInfo']['nivel'].",".$aInfoUeducativa['ueducativaInfo']['grado'].",".$aInfoUeducativa['ueducativaInfo']['paralelo'];

        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteBjp:seeStudents.html.twig', array(
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
                    'form' => $this->createFormStudentInscription($infoUe)->createView(),
                    'infoUe' => $infoUe,
                    'exist' => $exist,
                    'itemsUe'=>$itemsUe,
        ));
    }

    public function viewReportAction(Request $request){
      //get values send by post
      $form = $request->get('verificarPagoNuevo');
      //create db connexion
      $em = $this->getDoctrine()->getManager();

      $query = $em->getConnection()->prepare("
      select dept.departamento,dist.distrito,eval.institucioneducativa_id,inst.institucioneducativa,eval.turno,nivel,eval.grado_tipo_id,eval.paralelo,count(*) as  cantidad from bonojuancito_estudiante_validacion eval
        LEFT JOIN institucioneducativa inst on inst.id = eval.institucioneducativa_id
        left JOIN jurisdiccion_geografica jurg on jurg.id = inst.le_juridicciongeografica_id
        left JOIN distrito_tipo dist on dist.id = jurg.distrito_tipo_id
        left JOIN departamento_tipo dept on dept.id = dist.departamento_tipo_id
        where eval.es_pagado = 'true' and eval.institucioneducativa_id =  ".$form['sie']."
        and eval.gestion_tipo_id = cast(date_part('year', current_date) as integer)
        group by dept.departamento,dist.distrito,eval.institucioneducativa_id,inst.institucioneducativa,eval.turno,nivel,eval.grado_tipo_id,eval.paralelo
        order by 3,4,5
        limit 100
      ");
      $query->execute();
      $objReport = $query->fetchAll();

        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteBjp:viewReport.html.twig', array(
          'objReport' => $objReport,
          'data' =>  $form
        ));
    }


}
