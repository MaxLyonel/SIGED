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
class PostBachilleratoController extends Controller {

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
        return $this->render('SieRegularBundle:PostBachillerato:index.html.twig', array(
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
                ->setAction($this->generateUrl('post_bachillerato_result'))
                ->add('codigoRude', 'text', array('mapped' => false, 'label' => 'Rude', 'required' => true, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control')))
                ->add('gestion', 'choice', array('label' => 'Gestión', 'choices' => array('2017' => '2017','2016' => '2016', '2015' => '2015', '2014' => '2014', '2013' => '2013'), 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
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

    /**
     * get the bachiller
     * @param type $id
     * @param type $nivel
     * @param type $grado
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    private function getBachiller($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $entity->createQueryBuilder('ei')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('ei.estudiante = :id')
                ->andwhere('iec.nivelTipo in (:nivel)')
                ->andwhere('iec.gradoTipo in (:grado)')
                ->setParameter('id', $id)
                ->setParameter('nivel', array('13', '3'))
                ->setParameter('grado', array('6', '4'))
                ->getQuery();

        try {
            return $query->getResult();
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
        $form = $request->get('form');

        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));
        //verificamos si existe el estudiante
        if ($student) {

            $studentbachiller = $this->getbachiller($student->getId());

            if ($studentbachiller) {
                //the student is bachiller
                //get los historico de cursos

                $InscriptionsStudent = $this->getInscriptionsStudent($student->getCodigoRude());

                //obtenemos datos de la gestion pasada para determinar el curso al que le toca
//                $inscriptionOld = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
//                    'estudiante' => $student->getId(),
//                    'gestionTipo' => ($this->session->get('currentyear') - 1),
//                ));

                $objUe = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
                $query = $objUe->createQueryBuilder('ei')
                        ->select('IDENTITY(iec.institucioneducativa) as ieId, IDENTITY(iec.gestionTipo) as gestion')
                        ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                        ->where('iec.gestionTipo = :gestion')
                        ->andwhere('ei.estudiante = :id')
                        //->setParameter('gestion', $this->session->get('currentyear') - 1)
                        ->setParameter('gestion', $form['gestion'])
                        ->setParameter('id', $student->getId())
                        ->getQuery();
                $inscriptionOld = $query->getResult();

                //verificar si tiene inscripcion gestion pasada
                //if ($inscriptionOld) {
                if (true) {
                    //todo buscar el grado curso al que le corresponde de acuerdo a su estado matricual
                    //$posicionCurso = $this->getCourse($inscriptionOld->getNivelTipo()->getId(), $inscriptionOld->getCicloTipo()->getId(), $inscriptionOld->getGradoTipo()->getId(), $inscriptionOld->getEstadoMatriculaTipo()->getId());
                    $formPostBachiller = $this->createFormPostBachiller($student->getId(), $form['gestion']);
                } else {
                    $this->session->getFlashBag()->add('noticepost', 'Estudiante cuenta con inscripción para la gestión ' . ($this->session->get('currentyear') - 1));
                    return $this->redirectToRoute('post_bachillerato_index');
                }
            } else {
                //the student can't do this
                $this->session->getFlashBag()->add('noticepost', 'El estudiante no es bachiller...');
                return $this->redirectToRoute('post_bachillerato_index');
            }

//            $studentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
//                'estudiante' => $student->getId(),
//                'gestionTipo' => $this->session->get('currentyear')
//                    //, 'estadomatriculaTipo' => '4'
//            ));
        } else {
            $this->session->getFlashBag()->add('noticepost', 'Estudiante no registrado');
            return $this->redirectToRoute('post_bachillerato_index');
        }
        //everything is ok build the info
        return $this->render('SieRegularBundle:PostBachillerato:result.html.twig', array(
                    'datastudent' => $student,
                    'currentInscription' => $InscriptionsStudent,
                    'formPostBachiller' => $formPostBachiller->createView()
        ));
    }

    /**
     * buil the Omitidos form
     * @param type $aInscrip
     * @return type form
     */
    private function createFormPostBachiller($idStudent, $gestion) {

        $em = $this->getDoctrine()->getManager();
//        $aInscrip = explode('-', $aInscrip);
//        $nivel = $em->getRepository('SieAppWebBundle:NivelTIpo')->find($aInscrip[0]);
//        $grado = $em->getRepository('SieAppWebBundle:GradoTipo')->find($aInscrip[2]);


        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('post_bachillerato_save'))
                        ->add('institucionEducativa', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                        ->add('institucionEducativaName', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('nivel', 'choice', array('attr' => array('required' => true, 'class' => 'form-control')))
                        ->add('grado', 'choice', array('attr' => array('required' => true, 'class' => 'form-control')))
                        //->add('nivelId', 'hidden', array('mapped' => false, 'data' => $aInscrip[0]))
                        //->add('gradoId', 'hidden', array('mapped' => false, 'data' => $aInscrip[2]))
                        ->add('cicloId', 'hidden', array('mapped' => false, 'data' => 1))
                        ->add('idStudent', 'hidden', array('data' => $idStudent))
                        ->add('gestion', 'hidden', array('data' => $gestion))
                        ->add('paralelo', 'choice', array('attr' => array('required' => true, 'class' => 'form-control')))
                        ->add('turno', 'choice', array('attr' => array('required' => true, 'class' => 'form-control')))
                        ->add('save', 'submit', array('label' => 'Guardar'))
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
        $cursos = $this->aCursos;
        //print_r($cursos);
        $sw = 1;
        while (($acourses = current($cursos)) !== FALSE && $sw) {
            if (current($cursos) == $nivel . '-' . $ciclo . '-' . $grado) {
                $ind = key($cursos);
                $currentCurso = current($cursos);
                $sw = 0;
                //echo key($cursos) . '---' . current($cursos) . ' --<br />';
            }
            next($cursos);
        }
        if ($matricula == 5) {
            $ind = $ind + 1;
        }
        return $ind;
        echo "--......." . $matricula . "--.......";
    }

    /**
     * get the stutdents inscription - the record
     * @param type $id
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    private function getInscriptionsStudent($id) {
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
                ->orderBy('ei.fechaInscripcion', 'DESC')
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
    public function saveAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        //get the variblees
        $form = $request->get('form');

        $em->getConnection()->beginTransaction();
        try {

            $objUe = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $query = $objUe->createQueryBuilder('ei')
                    ->select('IDENTITY(iec.institucioneducativa) as ieId, IDENTITY(iec.gestionTipo) as gestion')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                    ->where('iec.nivelTipo = :nivel')
                    ->andwhere('iec.gradoTipo = :grado')
                    ->andwhere('ei.estadomatriculaTipo = :mat')
                    ->andwhere('ei.estudiante = :id')
                    ->setParameter('nivel', $form['nivel'])
                    ->setParameter('grado', $form['grado'])
                    ->setParameter('id', $form['idStudent'])
                    ->setParameter('mat', '5')
                    ->getQuery();
            $lookforinscription = $query->getResult();

            if ($lookforinscription) {
                $this->session->getFlashBag()->add('noticepost', 'Estudiante ya cuenta con inscripción en curso seleccionado...');
                return $this->redirect($this->generateUrl('post_bachillerato_index'));
            }

            //insert a new record with the new selected variables and put matriculaFinID like 5
            $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                'nivelTipo' => $form['nivel'],
                'gradoTipo' => $form['grado'],
                'paraleloTipo' => $form['paralelo'],
                'turnoTipo' => $form['turno'],
                'institucioneducativa' => $form['institucionEducativa'],
                'gestionTipo' => $form['gestion']
            ));
            //insert a new record with the new selected variables and put matriculaFinID like 5
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();

            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($form['gestion']));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']));
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime('now'));
            $studentInscription->setFechaRegistro(new \DateTime('now'));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objCurso->getId()));
            $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(29));
            $em->persist($studentInscription);
            $em->flush();
            // Try and commit the transaction
            $em->getConnection()->commit();
            //$this->session->getFlashBag()->add('goodomi', 'Se realizo la inscripción del Estudiante satisfactoriamente ');
            $this->session->getFlashBag()->add('successpost', 'Estudiante Post Bachiller fue inscrito...');
            return $this->redirect($this->generateUrl('post_bachillerato_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }

    /**
     * get the paralelto, turno and sie name
     * @param type $id like sie
     * @param type $n like nivel
     * @param type $g like grado
     * @return type
     */
    public function findIEAction($id, $gestion) {
        $em = $this->getDoctrine()->getManager();

        //get the tuicion
        //select * from get_ue_tuicion(137746,82480002)
        $paralelo = array();
        $turno = array();
        $aniveles = array();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
//        echo $institucion->getInstitucioneducativa();
//        die;
        if ($institucion) {

            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion(:user_id::INT, :sie::INT, :roluser::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $id);
            $query->bindValue(':roluser', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();


            if ($aTuicion[0]['get_ue_tuicion']) {
                $institucionwithseccundary = $this->getUeswithsecundaria($id, $gestion);
                $nombreIE = ($institucionwithseccundary) ? $institucion->getInstitucioneducativa() : 'Unidad Educativa no cuenta con 3er, 4to y 5to de seccundaria';
                //get the Niveles
                $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
                $query = $entity->createQueryBuilder('iec')
                        ->select('(iec.nivelTipo)')
                        ->where('iec.institucioneducativa = :sie')
                        ->andwhere('iec.nivelTipo = :nivel')
                        ->andwhere('iec.gestionTipo = :gestion')
                        ->setParameter('sie', $id)
                        ->setParameter('nivel', '13')
                        ->setParameter('gestion', $gestion)
                        ->distinct()
                        ->getQuery();

                $aNiveles = $query->getResult();

                foreach ($aNiveles as $nivel) {
                    $aniveles[$nivel[1]] = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel[1])->getNivel();
                }
            } else {
                $nombreIE = 'No tiene Tuición';
            }
        } else {
            $nombreIE = 'Unidad Educativa no Existe';
        }


        $response = new JsonResponse();
        return $response->setData(array('nombre' => $nombreIE, 'aniveles' => $aniveles));
    }

    /**
     * get grado
     * @param type $idnivel
     * @param type $sie
     * return list of grados
     */
    public function findgradoAction($idnivel, $sie, $gestion) {
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
                ->setParameter('gestion', $gestion)
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
    public function findparaleloAction($grado, $sie, $nivel, $gestion) {
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
                ->setParameter('gestion', $gestion)
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
    public function findturnoAction($paralelo, $sie, $nivel, $grado, $gestion) {
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
                ->setParameter('gestion', $gestion)
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

    private function getUeswithsecundaria($id, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa');
        $query = $entity->createQueryBuilder('ie')
                ->select('ie')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ie.id = iec.institucioneducativa')
                ->where('iec.gestionTipo = :gestion')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo in (:grado)')
                ->andwhere('ie.id = :id')
                ->setParameter('gestion', $gestion)
                ->setParameter('nivel', '13')
                ->setParameter('grado', array(3, 4, 5))
                ->setParameter('id', $id)
                ->setMaxResults(3)
                ->getQuery();

        try {
            return $query->getArrayResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

}
