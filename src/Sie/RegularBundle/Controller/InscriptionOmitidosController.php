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
class InscriptionOmitidosController extends Controller {

    private $session;
    public $lugarNac;
    public $dpto;
    public $aCursos;
    public $aCursosOld;
    public $lastUE;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $this->aCursos = $this->fillCursos();
        $this->aCursosOld = $this->fillCursosOld();
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
        return $this->render($this->session->get('pathSystem') . ':InscriptionOmitidos:index.html.twig', array(
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
                ->setAction($this->generateUrl('inscription_omitidos_result'))
                //->add('codigoRude', 'text', array('mapped' => false, 'label' => 'Rude', 'required' => true, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control', 'maxlength' => 18, 'pattern' => '[A-Z0-9]{12,18}')))
                ->add('codigoRude', 'text', array('label' => 'RUDE', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
//->add('public', 'checkbox', array('mapped'=>false,'label' => 'Show this entry publicly?', 'required' => false))
                ->getForm();
        return $form;
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

    private function getBachiller($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $entity->createQueryBuilder('ei')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('ei.estudiante = :id')
                ->andwhere('iec.nivelTipo IN (:nivel)')
                ->andwhere('iec.gradoTipo IN (:grado)')
                ->andwhere('ei.estadomatriculaTipo = :mat')
                ->setParameter('id', $id)
                ->setParameter('nivel', array(13))
                ->setParameter('grado', array(6))
                ->setParameter('mat', '5')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    private function lastInscription($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $entity->createQueryBuilder('ei')
                ->select('ei.id as inscriptionId, IDENTITY(iec.nivelTipo) as nivelTipo', 'IDENTITY(iec.cicloTipo) as cicloTipo, IDENTITY(iec.gradoTipo) as gradoTipo', 'IDENTITY(ei.estadomatriculaTipo) as estadomatriculaTipo, IDENTITY(iec.gestionTipo)')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('ei.estudiante = :id')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('id', $id)
                ->setParameter('gestion', $this->session->get('currentyear') - 1)
                ->orderBy('ei.estadomatriculaTipo', 'ASC')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    private function oldInscription($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $entity->createQueryBuilder('ei')
                ->select('ei.id as inscriptionId, IDENTITY(iec.nivelTipo) as nivelTipo', 'IDENTITY(iec.cicloTipo) as cicloTipo, IDENTITY(iec.gradoTipo) as gradoTipo', 'IDENTITY(ei.estadomatriculaTipo) as estadomatriculaTipo, IDENTITY(iec.gestionTipo) as gestion, ei.fechaInscripcion as fechaInscripcion, ei.fechaRegistro as fechaRegistro')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('ei.estudiante = :id')
                ->setParameter('id', $id)
                //->orderBy('ei.fechaInscripcion ', 'DESC')
                ->orderBy('iec.gestionTipo', 'DESC')
                ->addorderBy('iec.nivelTipo', 'DESC')
                ->addorderBy('iec.gradoTipo', 'DESC')
                ->getQuery();
        $resutlQuery = $query->getResult();
        //look for the next inscription depend the historial
        if ($resutlQuery) {

            reset($resutlQuery);
            $sw = true;
            while ($sw && $val = current($resutlQuery)) {
                if ($val['estadomatriculaTipo'] == '5' || $val['estadomatriculaTipo'] == '37' || $val['estadomatriculaTipo'] == '56' || $val['estadomatriculaTipo'] == '57' || $val['estadomatriculaTipo'] == '58') {
                    $keyCorrect = key($resutlQuery);
                    $sw = false;
                }
                next($resutlQuery);
            }
            if ($sw) {
                if ($resutlQuery) {

                    reset($resutlQuery);
                    $sw1 = true;
                    while ($sw1 && $val = current($resutlQuery)) {
                        $keyCorrect = key($resutlQuery);
                        $sw1 = false;
                        next($resutlQuery);
                    }
                } else {
                    //there is no key
                    $keyCorrect = 'krlos';
                }
            }
        }

        $resutlQuery = ($resutlQuery) ? $resutlQuery[$keyCorrect] : '0';

        try {
            return $resutlQuery;
        } catch (Exception $ex) {
            return $ex;
        }
    }

    /**
     * obtien el formulario para realizar la modificacion
     * @param Request $request
     */
    public function resultAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        //get the data send
        $form = $request->get('form');
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => mb_strtoupper($form['codigoRude'], 'UTF-8')));
        //verificamos si existe el estudiante
        if ($student) {
            //verificar si es bachiller
            $lookforbachiller = $this->getBachiller($student->getId());
            if ($lookforbachiller) {
                $this->session->getFlashBag()->add('notificationomitidos', 'El estudiante ya es bachiller... ');
                return $this->redirectToRoute('inscription_omitidos_index');
            }

            $inscription2 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $query = $inscription2->createQueryBuilder('ei')
                    ->select('ei.id as id')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
                    ->where('ei.estudiante = :id')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->andwhere('ei.estadomatriculaTipo = :mat')
                    ->setParameter('id', $student->getId())
                    ->setParameter('gestion', $this->session->get('currentyear'))
                    ->setParameter('mat', '4')
                    //->setParameter('mat2', '5')
                    ->getQuery();

            $studentInscription = $query->getResult();

            //$objInscription3 = $em->getRepository('SieAppWebBundle:TmpEstudianteUltimoCurso')->findOneBy(array('codigoRudeId' => $form['codigoRude']));
            //check if has inscription
            //if ($objInscription3) {
            //get the current course
            //verificamos si tiene inscripcion en la gestion actual
            if (!$studentInscription) {
                //get the last UE
                //obtenemos datos de la gestion pasada para determinar el curso al que le toca
                //$inscriptionOld = $this->lastInscription($student->getId());
                $inscriptionOld = $this->oldInscription($student->getId());

                //verificar si tiene inscripcion gestion pasada
                if ($inscriptionOld) {

                    //todo buscar el grado curso al que le corresponde de acuerdo a su estado matricual
                    //$posicionCurso = $this->getCourse($inscriptionOld->getNivelTipo()->getId(), $inscriptionOld->getCicloTipo()->getId(), $inscriptionOld->getGradoTipo()->getId(), $inscriptionOld->getEstadoMatriculaTipo()->getId());
                    //$posicionCurso = $this->getCourse($inscriptionOld[0]['nivelTipo'], $inscriptionOld[0]['cicloTipo'], $inscriptionOld[0]['gradoTipo'], $inscriptionOld[0]['estadomatriculaTipo']);
                    $posicionCurso = ($inscriptionOld['gestion'] > 2010) ? $this->getCourse($inscriptionOld['nivelTipo'], $inscriptionOld['cicloTipo'], $inscriptionOld['gradoTipo'], $inscriptionOld['estadomatriculaTipo']) : $this->getCourseOld($inscriptionOld['nivelTipo'], $inscriptionOld['cicloTipo'], $inscriptionOld['gradoTipo'], $inscriptionOld['estadomatriculaTipo']);


                    //$posicionCurso = ($objInscription3->getGestionId() > 2010) ? $this->getCourse($objInscription3->getNivelId(), $objInscription3->getCicloId(), $objInscription3->getGradoId(), $objInscription3->getEstadoMatriculaFinId()) : $this->getCourseOld($objInscription3->getNivelId(), $objInscription3->getCicloId(), $objInscription3->getGradoId(), $objInscription3->getEstadoMatriculaFinId());
                    //get last Unidad educativa

                    $objUe = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
                    $query = $objUe->createQueryBuilder('ei')
                            ->select('IDENTITY(iec.institucioneducativa) as ieId, IDENTITY(iec.gestionTipo) as gestion')
                            ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                            ->where('iec.gestionTipo = :gestion')
                            ->andwhere('ei.estudiante = :id')
                            ->setParameter('gestion', $this->session->get('currentyear'))
                            ->setParameter('id', $student->getId())
                            ->getQuery();
                    $objLastUe = $query->getResult();

                    $lastue = ($objLastUe) ? $objLastUe[0]['ieId'] : 'krlos';

                    $formOmitidos = $this->createFormOmitidos($this->aCursos[$posicionCurso], $student->getId(), $lastue, $form['codigoRude']);

                    //get los historico de cursos

                    $currentInscription = $this->getCurrentInscriptionsStudent($student->getCodigoRude());

                    //everything is ok build the info
                    return $this->render($this->session->get('pathSystem') . ':InscriptionOmitidos:result.html.twig', array(
                                'datastudent' => $student,
                                'currentInscription' => $currentInscription,
                                'formOmitidos' => $formOmitidos->createView()
                    ));
                } else {


                    //$objInscription3 = $em->getRepository('SieAppWebBundle:TmpEstudianteUltimoCurso')->findOneBy(array('codigoRudeId' => $form['codigoRude']));
                    $objInscription3 = $this->oldInscription($student->getId());

                    //check if has inscription
                    if ($objInscription3) {
                        //$posicionCurso = ($objInscription3->getGestionId() > 2010) ? $this->getCourse($objInscription3->getNivelId(), $objInscription3->getCicloId(), $objInscription3->getGradoId(), $objInscription3->getEstadoMatriculaFinId()) : $this->getCourseOld($objInscription3->getNivelId(), $objInscription3->getCicloId(), $objInscription3->getGradoId(), $objInscription3->getEstadoMatriculaFinId());
                        $posicionCurso = ($objInscription3['gestion'] > 2010) ? $this->getCourse($objInscription3['nivelTipo'], $objInscription3['cicloTipo'], $objInscription3['gradoTipo'], $objInscription3['estadomatriculaTipo']) : $this->getCourseOld($objInscription3['nivelTipo'], $objInscription3['cicloTipo'], $objInscription3['gradoTipo'], $objInscription3['estadomatriculaTipo']);
                    } else {
                        //this is nivel 11 and grado 1
                        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
                        $query = $entity->createQueryBuilder('ei')
                                ->select('ei.id as inscriptionId, IDENTITY(iec.nivelTipo) as nivelTipo', 'IDENTITY(iec.cicloTipo) as cicloTipo, IDENTITY(iec.gradoTipo) as gradoTipo', 'IDENTITY(ei.estadomatriculaTipo) as estadomatriculaTipo')
                                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                                ->where('ei.estudiante = :id')
                                ->andwhere('iec.gestionTipo = :gestion')
                                ->setParameter('id', $student->getId())
                                ->setParameter('gestion', $this->session->get('currentyear'))
                                ->getQuery();

                        $inscriptionOld = $query->getResult();
                        if (!$inscriptionOld) {
                            $message = 'Estudiante no cuenta con historial académico para determinar a que curso le corresponde. ';
                            $this->addFlash('notificationomitidos', $message);
                            return $this->redirectToRoute('inscription_omitidos_index');
                        }

                        $posicionCurso = $this->getCourse($inscriptionOld[0]['nivelTipo'], $inscriptionOld[0]['cicloTipo'], $inscriptionOld[0]['gradoTipo'], $inscriptionOld[0]['estadomatriculaTipo']);
                    }
                    $objUe = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
                    $query = $objUe->createQueryBuilder('ei')
                            ->select('IDENTITY(iec.institucioneducativa) as ieId, IDENTITY(iec.gestionTipo) as gestion')
                            ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                            ->where('iec.gestionTipo = :gestion')
                            ->andwhere('ei.estudiante = :id')
                            ->setParameter('gestion', $this->session->get('currentyear'))
                            ->setParameter('id', $student->getId())
                            ->getQuery();
                    $objLastUe = $query->getResult();

                    $lastue = ($objLastUe) ? $objLastUe[0]['ieId'] : 'krlos';

                    $formOmitidos = $this->createFormOmitidos($this->aCursos[$posicionCurso], $student->getId(), $lastue, $form['codigoRude']);

                    //get los historico de cursos

                    $currentInscription = $this->getCurrentInscriptionsStudent($student->getCodigoRude());

                    //everything is ok build the info
                    return $this->render($this->session->get('pathSystem') . ':InscriptionOmitidos:result.html.twig', array(
                                'datastudent' => $student,
                                'currentInscription' => $currentInscription,
                                'formOmitidos' => $formOmitidos->createView()
                    ));

                    die;
//                    $message = 'Estudiante no cuenta con historial académico para determinar a que curso le corresponde. ';
//                    $this->addFlash('notificationomitidos', $message);
//                    //$this->session->getFlashBag()->add('notiomi', 'Estudiante no cuenta con historial académico para determinar a que curso le corresponde. ');
//                    return $this->redirectToRoute('inscription_omitidos_index');
                }
            } else {
                $message = 'Estudiante ya cuenta con inscripción para la gestión ' . $this->session->get('currentyear');
                $this->addFlash('notificationomitidos', $message);
//$this->session->getFlashBag()->add('notiomi ', 'Estudiante ya cuenta con inscripción para la gestión ' . $this->session->get('currentyear'));
                return $this->redirectToRoute('inscription_omitidos_index');
            }
        } else {
//die('krlos');
            $message = "Estudiante no registrado";
            $this->addFlash('notificationomitidos', $message);
//$this->session->getFlashBag()->add('notiomi ', 'Estudiante no registrado');
            return $this->render($this->session->get('pathSystem') . ':InscriptionOmitidos:index.html.twig', array(
                        'form' => $this->createSearchForm()->createView(),
            ));
//return $this->redirectToRoute('inscription_omitidos_index');
        }
    }

    /**
     * buil the Omitidos form
     * @param type $aInscrip
     * @return type form
     */
    private function createFormOmitidos($aInscrip, $idStudent, $lastue, $rude) {

        $em = $this->getDoctrine()->getManager();
        $aInscrip = explode('-', $aInscrip);
        $nivel = $em->getRepository('SieAppWebBundle:NivelTIpo')->find($aInscrip[0]);
        $grado = $em->getRepository('SieAppWebBundle:GradoTipo')->find($aInscrip[2]);


        return $formOmitido = $this->createFormBuilder()
                ->setAction($this->generateUrl('inscription_omitidos_saveOmitidos'))->add('institucionEducativa', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                ->add('institucionEducativaName', 'text', array('label' => 'Unidad Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))->add('nivel', 'text', array('data' => $nivel->getNivel(), 'disabled' => true, 'attr' => array('class' => 'form-control')))->add('grado', 'text', array('data' => $grado->getGrado(), 'disabled' => true, 'attr' => array('class' => 'form-control')))->add('nivelId', 'hidden', array('mapped' => false, 'data' => $aInscrip[0]))->add('gradoId', 'hidden', array('mapped' => false, 'data' => $aInscrip[2]))->add('cicloId', 'hidden', array('mapped' => false, 'data' => $aInscrip[1]))->add('lastue', 'hidden', array('mapped' => false, 'data' => $lastue))->add('rude', 'hidden', array('mapped' => false, 'data' => $rude))->add('idStudent', 'hidden', array('mapped' => false, 'data' => $idStudent))->add('paralelo', 'choice', array('attr' => array('required' => true, 'class' => 'form-control')))->add('turno', 'choice', array('attr' => array('requirede' => true, 'class' => 'form-control')))->add('save', 'submit', array('label' => 'Guardar'))
                ->getForm();
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
        while (( $acourses = current($cursos)) !== FALSE && $sw) {
            if (current($cursos) == $nivel . '-' . $ciclo . '-' . $grado) {
                $ind = key($cursos);
                $currentCurso = current($cursos);
                $sw = 0;
            }
            next($cursos);
        }
        if ($matricula == 5 || $matricula == 56 || $matricula == 57 || $matricula == 58) {
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
        if ($matricula == 5 || $matricula == 56 || $matricula == 57 || $matricula == 58) {
            $ind = $ind + 1;
        }
        return $ind;
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
        $query = $entity->createQueryBuilder('e')->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('e.codigoRude = :id')
                ->setParameter('id', $id)->orderBy('ei.fechaInscripcion', 'DESC')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    /**
     * todo the registration of traslado
     * @param Request $request
     *
     */
    public function saveOmitidosAction(Request $request) {


        try {
            //create conexion on DB
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            //get the variblees
            $form = $request->get('form');

            //get the id of next course
            $newCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                'institucioneducativa' => $form['institucionEducativa'],
                'nivelTipo' => $form['nivelId'],
                'cicloTipo' => $form['cicloId'],
                'gradoTipo' => $form['gradoId'],
                'paraleloTipo' => $form ['paralelo'],
                'gestionTipo' => $this->session->get('currentyear')
            ));

            //get info old about inscription
            $currentInscrip = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $form['idStudent']));

            //insert a new record with the new selected variables and put matriculaFinID like 5
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']));
            $studentInscription->setCodUeProcedenciaId($form['institucionEducativa']);
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
            $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($newCurso->getId()));
            $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(7));
            $studentInscription->setCodUeProcedenciaId(0);
            $em->persist($studentInscription);
            $em->flush();

            //add the areas to the student
            $responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $newCurso->getId());
            //todo the validation
            $query = $em->getConnection()->prepare('SELECT * from sp_corrije_inscrip_transac_estudiante_obser(:rude::VARCHAR, :inscripcionid ::VARCHAR)');
            $query->bindValue(':rude', $form['rude']);
            $query->bindValue(':inscripcionid', $studentInscription->getId());
            $query->execute();
            //$this->session->getFlashBag()->add('goodomi', 'Se realizo la inscripción del Estudiante satisfactoriamente ');
            //do the commit in DB
            $em->getConnection()->commit();
            $this->session->getFlashBag()->add('goodomi', 'Estudiante Omitido fue inscrito...');
            return $this->redirect($this->generateUrl('inscription_omitidos_index'));
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
    private function addAreasToStudent($studentInscrId, $newCursoId) {
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
                $studentAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
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
     * get the paralelto, turno and sie name
     * @param type $id like sie
     * @param type $n like nivel
     * @param type $g like grado
     * @return type
     */
    public function findIEAction($id, $nivel, $grado, $lastue) {

        $em = $this->getDoctrine()->getManager();
//get the tuicion
//select * from get_ue_tuicion(137746,82480002)
        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion(:user_id::INT, :sie::INT, :roluser::INT)');
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

                    $objOmitido = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
                    $query = $objOmitido->createQueryBuilder('iec')
                            ->select('IDENTITY(iec.paraleloTipo) as paraleloTipo', 'pt.paralelo as paralelo')
                            ->leftjoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'iec.paraleloTipo = pt.id')
                            ->where('iec.institucioneducativa = :id')
                            ->andwhere('iec.nivelTipo = :nivel')
                            ->andwhere('iec.gradoTipo = :grado')
                            ->andwhere('iec.gestionTipo = :gestion')
                            ->setParameter('id', $id)
                            ->setParameter('nivel', $nivel)
                            ->setParameter('grado', $grado)
                            ->setParameter('gestion', $this->session->get('currentyear'))
                            ->distinct()
                            ->orderBy('iec.paraleloTipo', 'ASC')
                            ->getQuery();
                    $infoOmitido = $query->getResult();


                    foreach ($infoOmitido as $info) {
                        $paralelo[$info['paraleloTipo']] = $info['paralelo'];
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

}
