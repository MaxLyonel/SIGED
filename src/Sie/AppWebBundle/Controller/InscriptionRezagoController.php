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
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Estudiante controller.
 *
 */
class InscriptionRezagoController extends Controller {

    private $session;
    public $lugarNac;
    public $dpto;
    public $aCursos;
    public $lastUE;
    public $oparalelos;

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
        return $this->render('SieAppWebBundle:InscriptionRezago:index.html.twig', array(
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
                ->setAction($this->generateUrl('inscription_rezago_result'))
                ->add('codigoRude', 'text', array('label' => 'Rude', 'invalid_message' => 'campo obligatorio', 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 20, 'required' => true, 'class' => 'form-control')))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                //->add('public', 'checkbox', array('mapped'=>false,'label' => 'Show this entry publicly?', 'required' => false))
                ->getForm();
        return $form;
    }

    /**
     * build the cursos in a array
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

    private function getyearsstudent($edad) {

        list($year, $mounth, $day) = explode("-", $edad);
        $y_dif = date("Y") - $year;
        $m_dif = date("m") - $mounth;
        $d_dif = date("d") - $day;
        if ($d_dif < 0 || $m_dif < 0)
            $y_dif--;
        return $y_dif;
    }

    /**
     * obtien el formulario para realizar la modificacion
     * @param Request $request
     */
    public function resultAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));
        //verificamos si existe el estudiante y si es menor a 15
        if ($student) {

            $ageStudent = $this->getyearsstudent($student->getFechaNacimiento()->format('Y-m-d'));
            if ($ageStudent > 15) {
                $this->session->getFlashBag()->add('notirezago', 'Estudiante no cumple con la edad establecida');
                return $this->redirectToRoute('inscription_rezago_index');
            }

//            $studentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
//                'estudiante' => $student->getId(),
//                'gestionTipo' => $this->session->get('currentyear')
//                , 'estadomatriculaTipo' => '4'
//            ));
            $inscription3 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $query = $inscription3->createQueryBuilder('ei')
                    ->select('IDENTITY(iec.institucioneducativa) as institucioneducativa ', 'IDENTITY(iec.nivelTipo) as nivel', 'IDENTITY(iec.gradoTipo) as grado', 'IDENTITY(iec.paraleloTipo) as paralelo', 'IDENTITY(iec.turnoTipo) as turno', 'IDENTITY(iec.cicloTipo) as ciclo', 'IDENTITY(ei.estadomatriculaTipo) as matricula')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
                    ->where('ei.estudiante = :id')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->andwhere('ei.estadomatriculaTipo = :matricula')
                    ->setParameter('id', $student->getId())
                    ->setParameter('gestion', $this->session->get('currentyear'))
                    ->setParameter('matricula', '4')
                    ->getQuery();

            $studentInscription = $query->getResult();

            //verificamos si tiene inscripcion en la gestion actual
            if ($studentInscription) {
                //get los historico de cursos
                $currentInscription = $this->getCurrentInscriptionsStudent($student->getCodigoRude());

                //get the materias and notas
                //$amateriasNotas = $this->getMateriasNotas($student->getId(), $studentInscription->getNivelTipo()->getId(), $studentInscription->getGradoTipo()->getId());
                //$amateriasNotas = $this->getAsignaturasPerStudent($studentInscription->getInstitucioneducativa()->getId(), $studentInscription->getNivelTipo()->getId(), $studentInscription->getGradoTipo()->getId(), $studentInscription->getParaleloTipo()->getId(), $studentInscription->getTurnoTipo()->getId());
                $amateriasNotas = $this->getAsignaturasPerStudent($studentInscription[0]['institucioneducativa'], $studentInscription[0]['nivel'], $studentInscription[0]['grado'], $studentInscription[0]['paralelo'], $studentInscription[0]['turno']);

                //todo buscar el grado curso al que le corresponde de acuerdo a su estado matricual
                $posicionCurso = $this->getCourse($studentInscription[0]['nivel'], $studentInscription[0]['ciclo'], $studentInscription[0]['grado'], $studentInscription[0]['matricula']);

                //get paralelos
                $this->oparalelos = $this->getParalelosStudent($posicionCurso, $studentInscription[0]['institucioneducativa']);
                //die('krkrkrk');
                //get last Unidad educativa

                $formMaterias = $this->createFormMaterias($student->getId(), $studentInscription[0]['institucioneducativa'], $posicionCurso);
            } else {
                //die('krlos');
                $this->session->getFlashBag()->add('notirezago', 'Estudiante no cuenta con inscripción para la gestión ' . $this->session->get('currentyear'));
                return $this->redirectToRoute('inscription_rezago_index');
            }
        } else {
            $this->session->getFlashBag()->add('notirezago', 'Estudiante no registrado');
            return $this->redirectToRoute('inscription_rezago_index');
        }
        //everything is ok build the info
        return $this->render('SieAppWebBundle:InscriptionRezago:result.html.twig', array(
                    'datastudent' => $student,
                    'currentInscription' => $currentInscription,
                    'formMaterias' => $formMaterias->createView(),
                    'materiasnotas' => $amateriasNotas
        ));
    }

    private function getParalelosStudent($posCurso, $ue) {
        $em = $this->getDoctrine()->getManager();
        list($nivel, $ciclo, $grado) = explode('-', $this->aCursos[$posCurso]);

        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $entity->createQueryBuilder('ei')
                ->select('IDENTITY(iec.paraleloTipo) as paraleloTipo')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('iec.institucioneducativa = :ue')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('ue', $ue)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('gestion', $this->session->get('currentyear'))
                ->distinct()
                ->orderBy('iec.paraleloTipo', 'ASC')
                ->getQuery();
        try {
            $objparalelos = $query->getResult();
            $aparalelos = array();
            if ($objparalelos) {
                foreach ($objparalelos as $paralelo) {
                    $aparalelos[$paralelo['paraleloTipo']] = $paralelo['paraleloTipo'];
                }
            }
            return $aparalelos;
        } catch (Exception $ex) {
            return $ex;
        }
    }

    /**
     * get the asignaturas per student
     * @param type $sie
     * @param type $nivel
     * @param type $grado
     * @param type $paralelo
     * @param type $turno
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    private function getAsignaturasPerStudent($sie, $nivel, $grado, $paralelo, $turno) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('ast.id', 'ast.asignatura')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco', 'WITH', 'iec.id=ieco.insitucioneducativaCurso')
                ->leftjoin('SieAppWebBundle:AsignaturaTipo', 'ast', 'WITH', 'ieco.asignaturaTipo=ast.id')
                ->where('iec.institucioneducativa= :sie')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andwhere('iec.turnoTipo = :turno')
                ->setParameter('sie', $sie)
                ->setParameter('gestion', $this->session->get('currentyear'))
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('turno', $turno)
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    /**
     * get asignaturas per student
     * @param type $idStudent
     * @param type $nivel
     * @param type $grado
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    private function getMateriasNotas($idStudent, $nivel, $grado) {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:EstudianteNota');
        $query = $entity->createQueryBuilder('en')
                ->select('ast.id', 'ast.asignatura')
                ->leftjoin('SieAppWebBundle:EstudianteAsignatura', 'ea', 'WITH', 'en.estudianteAsignatura=ea.id')
                ->leftjoin('SieAppWebBundle:AsignaturaTipo', 'ast', 'WITH', 'ea.asignaturaTipo=ast.id')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'ea.estudianteInscripcion = ei.id')
                ->leftjoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'ei.estudiante = e.id')
                ->where('e.id = :idStudent')
                ->andwhere('ei.gestionTipo = :gestion')
                ->andwhere('ei.nivelTipo = :nivel')
                ->andwhere('ei.gradoTipo = :grado')
                ->andwhere('en.notaTipo = :nota')
                ->setParameter('idStudent', $idStudent)
                ->setParameter('gestion', '2014')//use the session
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado - 1)
                ->setParameter('nota', '1')
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
    private function createFormMaterias($idStudent, $ue, $nextcurso) {
        $em = $this->getDoctrine()->getManager();
        list($nextnivel, $nextciclo, $nextgrado) = explode('-', $this->aCursos[$nextcurso]);
        $onivel = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nextnivel);
        $ogrado = $em->getRepository('SieAppWebBundle:GradoTipo')->find($nextgrado);
        $ociclo = $em->getRepository('SieAppWebBundle:GradoTipo')->find($nextciclo);
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('inscription_rezago_saveMateriasNotas'))
                        ->add('numeroacta', 'text', array('label' => 'Número de Acta Rezago', 'attr' => array('required' => true, 'class' => 'form-control', 'maxlength' => 14, 'pattern' => '[0-9]{12,14}')))
                        ->add('ue', 'text', array('data' => $ue, 'disabled' => true, 'label' => 'Unidad Educativa', 'attr' => array('required' => false, 'maxlength' => 8, 'class' => 'form-control')))
                        ->add('ueid', 'hidden', array('data' => $ue))
                        ->add('nivel', 'text', array('data' => $onivel->getNivel(), 'disabled' => true, 'attr' => array('required' => false, 'class' => 'form-control')))
                        ->add('grado', 'text', array('data' => $ogrado->getGrado(), 'disabled' => true, 'attr' => array('required' => false, 'class' => 'form-control')))
                        ->add('nivelid', 'hidden', array('data' => $onivel->getId()))
                        ->add('gradoid', 'hidden', array('data' => $ogrado->getId()))
                        ->add('cicloid', 'hidden', array('data' => $ociclo->getId()))
                        ->add('idStudent', 'hidden', array('required' => false, 'mapped' => false, 'data' => $idStudent))
                        //->add('paralelo', 'choice', array('attr' => array('required' => true, 'class' => 'form-control')))
                        ->add('paralelo', 'entity', array('label' => 'Paralelo', 'empty_value' => 'seleccionar...', 'attr' => array('class' => 'form-control'),
                            'class' => 'SieAppWebBundle:ParaleloTipo',
                            'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('p')
                                ->where('p.id in (:ue)')
                                ->setParameter('ue', $this->oparalelos)
                                ->distinct()
                                ->orderBy('p.paralelo', 'ASC')
                        ;
                    }, 'property' => 'paralelo'
                        ))
                        ->add('turno', 'choice', array('attr' => array('required' => true, 'class' => 'form-control')))
                        ->add('save', 'submit', array('label' => 'Registrar'))
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
        if ($matricula == 4) {
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
                ->orderBy('ei.fechaInscripcion', 'ASC')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    /**
     * todo the registration of rezago
     * @param Request $request
     * 
     */
    public function saveMateriasNotasAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //get the variblees
        $form = $request->get('form');
        $materias = $request->get('materias');

        try {

            //update the matricula to rezago in estudianteInscripcion
            $objInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $query = $objInscription->createQueryBuilder('ei')
                    ->select('(ei.id) as inscriptionId')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                    ->where('ei.estudiante = :idstudent')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->andwhere('ei.estadomatriculaTipo = :etipo')
                    ->setParameter('idstudent', $form['idStudent'])
                    ->setParameter('gestion', $this->session->get('currentyear'))
                    ->setParameter('etipo', 4)
                    ->getQuery();
            $objresultInscription = $query->getResult();

            $currentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($objresultInscription[0]['inscriptionId']);
            $currentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(57));
            $em->persist($currentInscription);
            $em->flush();
            //save promedios
            //$studentAsignatura = new EstudianteAsignatura();

            reset($materias);
            while ($val = current($materias)) {
                $studentnotas = new EstudianteNota();
                $studentnotas->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(5));
                $studentnotas->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array(
                            'estudianteInscripcion' => $currentInscription->getId(),
                            'gestionTipo' => $this->session->get('currentyear'),
                            'asignaturaTipo' => key($materias)
                )));
                $studentnotas->setNotaCuantitativa(current($materias));
                $studentnotas->setUsuarioId($this->session->get('userId'));
                $studentnotas->setFechaRegistro(new \DateTime('now'));
                $studentnotas->setFechaModificacion(new \DateTime('now'));
                $em->persist($studentnotas);
                $em->flush();
                next($materias);
            }//die;
            //update the las inscription
//            $lastInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
//                'estudiante' => $form['idStudent'], 'gestionTipo' => $this->session->get('currentyear')
//            ));
            //get the id of institucioineducativaCurso
            $objNextInscription = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                'paraleloTipo' => $form['paralelo'],
                'turnoTipo' => $form['turno'],
                'nivelTipo' => $form['nivelid'],
                'gradoTipo' => $form['gradoid'],
                'cicloTipo' => $form['cicloid'],
                'institucioneducativa' => $form['ueid'],
                'gestionTipo' => $this->session->get('currentyear')
            ));

            //save the new inscription
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']));
            $studentInscription->setCodUeProcedenciaId($form['ueid']);
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
            $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objNextInscription->getId()));
            $em->persist($studentInscription);
            $em->flush();
            // Try and commit the transaction
            $em->getConnection()->commit();

            $this->session->getFlashBag()->add('goodrezago', 'Inscripción Talento registrado sin problemas');
            return $this->redirectToRoute('inscription_rezago_index');

            //return $this->render('SieAppWebBundle:InscriptionTalento:nextCurso.html.twig');
            //return $this->redirect($this->generateUrl('inscription_talento_index'));
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
    public function findIEAction($id, $nivel, $grado, $lastue) {

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
        $paralelo = array();
        $turno = array();

        // if ($aTuicion[0]['get_ue_tuicion']) {
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
        /*     } else {
          $nombreIE = 'No tiene Tuición';
          } */

        $response = new JsonResponse();
        return $response->setData(array('nombre' => $nombreIE, 'paralelo' => $paralelo, 'turno' => $turno));
    }

}
