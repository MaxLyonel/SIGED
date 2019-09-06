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
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;

/**
 * Estudiante controller.
 *
 */
class InscriptionTalentoController extends Controller {

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
        return $this->redirectToRoute('principal_web');
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':InscriptionTalento:index.html.twig', array(
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
                ->setAction($this->generateUrl('inscription_talento_result'))
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
     * obtiene el formulario para realizar la modificacion
     * @param Request $request
     */
    public function resultAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));
        //verificamos si existe el estudiante y si es menor a 15
        if ($student) {

            //look for inscription like talento
            $inscription2 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $query = $inscription2->createQueryBuilder('ei')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
                    ->where('ei.estudiante = :id')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->andwhere('ei.estadomatriculaTipo = :matricula')
                    ->setParameter('id', $student->getId())
                    ->setParameter('gestion', $this->session->get('currentyear'))
                    ->setParameter('matricula', '58')
                    ->getQuery();

            $talentoInscription = $query->getResult();

            if ($talentoInscription) {
                $this->session->getFlashBag()->add('notitalento', 'Estudiante ya cuenta con inscripción talento para la presente gestión');
                return $this->redirectToRoute('inscription_talento_index');
            }

            //verficamos edad para talento
//            $ageStudent = $this->getyearsstudent($student->getFechaNacimiento()->format('Y-m-d'));
//            if ($ageStudent > 15) {
//                $this->session->getFlashBag()->add('notitalento', 'Estudiante no cumple con la edad establecida');
//                return $this->redirectToRoute('inscription_talento_index');
//            }
//            $studentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
//                'estudiante' => $student->getId(),
//                'gestionTipo' => $this->session->get('currentyear')
//                , 'estadomatriculaTipo' => '4'
//            ));
            $inscription3 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $query = $inscription3->createQueryBuilder('ei')
                    ->select('iec.id as iecId, (ei.id) as inscriptionId,IDENTITY(iec.institucioneducativa) as institucioneducativa ', 'IDENTITY(iec.nivelTipo) as nivel', 'IDENTITY(iec.gradoTipo) as grado', 'IDENTITY(iec.paraleloTipo) as paralelo', 'IDENTITY(iec.turnoTipo) as turno', 'IDENTITY(iec.cicloTipo) as ciclo', 'IDENTITY(ei.estadomatriculaTipo) as matricula')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
                    ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                    ->leftJoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo = it.id')
                    ->where('ei.estudiante = :id')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->andwhere('ei.estadomatriculaTipo = :matricula')
                    ->andwhere('it.id = :ietipo')
                    ->setParameter('id', $student->getId())
                    ->setParameter('gestion', $this->session->get('currentyear'))
                    ->setParameter('matricula', '4')
                    ->setParameter('ietipo', 1)
                    ->getQuery();

            $studentInscription = $query->getResult();

            //validate inscription to inicial
            if ($studentInscription[0]['nivel'] == 11) {
                $message = 'No se puede realizar la inscripción, Estudiante ' . $form['codigoRude'] . ' en nivel Inicial.';
                $this->addFlash('notitalento', $message);
                return $this->redirectToRoute('inscription_talento_index');
            }
            // if ($studentInscription[0]['nivel'] == 12 && ($studentInscription[0]['grado']==1 || $studentInscription[0]['grado']==2)) {
            //     $message = 'No se puede realizar la inscripción, Estudiante ' . $form['codigoRude'] . ' en nivel Primario con Grado '.$studentInscription[0]['grado'];
            //     $this->addFlash('notitalento', $message);
            //     return $this->redirectToRoute('inscription_talento_index');
            // }
            //get the notas of student
            $boolStudentCalification = $this->getStudentNotasValidation($studentInscription, $student->getId());
            //check if the student has calification
            // if(!$boolStudentCalification){
            //   $message = 'Estudiante con rude: ' . $form['codigoRude'] . ' cuenta con calificaciones, no es posible realizar la operación ';
            //   $this->addFlash('notitalento', $message);
            //   return $this->redirectToRoute('inscription_talento_index');
            // }


            //verificamos si tiene inscripcion en la gestion actual
            if ($studentInscription) {
                //get los historico de cursos
                $currentInscription = $this->getCurrentInscriptionsStudent($student->getCodigoRude());
                //get the materias and notas
                //$amateriasNotas = $this->getMateriasNotas($student->getId(), $studentInscription->getNivelTipo()->getId(), $studentInscription->getGradoTipo()->getId());
                //$amateriasNotas = $this->getAsignaturasPerStudent($studentInscription->getInstitucioneducativa()->getId(), $studentInscription->getNivelTipo()->getId(), $studentInscription->getGradoTipo()->getId(), $studentInscription->getParaleloTipo()->getId(), $studentInscription->getTurnoTipo()->getId());
                ////comment by krlos
                //$amateriasNotas = $this->getAsignaturasPerStudent($studentInscription[0]['institucioneducativa'], $studentInscription[0]['nivel'], $studentInscription[0]['grado'], $studentInscription[0]['paralelo'], $studentInscription[0]['turno']);
                //todo buscar el grado curso al que le corresponde de acuerdo a su estado matricual
                $posicionCurso = $this->getCourse($studentInscription[0]['nivel'], $studentInscription[0]['ciclo'], $studentInscription[0]['grado'], $studentInscription[0]['matricula']);
                //get paralelos
                $this->oparalelos = $this->getParalelosStudent($posicionCurso, $studentInscription[0]['institucioneducativa']);
                //get current inscription
                $aCurrentIns = array('sie' => $studentInscription[0]['institucioneducativa'], 'nivel' => $studentInscription[0]['nivel'], 'grado' => $studentInscription[0]['grado'], 'paralelo' => $studentInscription[0]['paralelo'], 'turno' => $studentInscription[0]['turno'], 'inscriptionId' => $studentInscription[0]['inscriptionId']);
                //get last Unidad educativa
                $formMaterias = $this->createFormMaterias($student->getId(), $studentInscription[0]['institucioneducativa'], $posicionCurso, serialize($aCurrentIns));
            } else {
                //die('krlos');
                $this->session->getFlashBag()->add('notitalento', 'Estudiante no cuenta con inscripción para la gestión ' . $this->session->get('currentyear'));
                return $this->redirectToRoute('inscription_talento_index');
            }
        } else {
            $this->session->getFlashBag()->add('notitalento', 'Estudiante no registrado');
            return $this->redirectToRoute('inscription_talento_index');
        }
        //add students areas
        $studentAddAreasCurrent = $this->addAreasToStudent($studentInscription[0]['inscriptionId'], $studentInscription[0]['iecId']);

        //everything is ok build the info
        return $this->render($this->session->get('pathSystem') . ':InscriptionTalento:result.html.twig', array(
                    'datastudent' => $student,
                    'currentInscription' => $currentInscription,
                    'formMaterias' => $formMaterias->createView()//,
                        //'materiasnotas' => $amateriasNotas
        ));
    }
    private function getStudentNotasValidation($inscriptionOfStudent, $studentId){
      //create the DB conexion
      $em = $this->getDoctrine()->getManager();

//        /dump(  $inscriptionOfStudent[0]->getNivelTipo()->getId());
                    $bolNote = false;
                    //get nota to the student on transfer way
                    $objNota = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getNotasStudent($studentId,
                                                                                                            $inscriptionOfStudent[0]['nivel'],
                                                                                                            $inscriptionOfStudent[0]['grado'],
                                                                                                            $inscriptionOfStudent[0]['paralelo'],
                                                                                                            $inscriptionOfStudent[0]['turno'],
                                                                                                            $this->session->get('currentyear'),
                                                                                                            $inscriptionOfStudent[0]['institucioneducativa']);



                  if($inscriptionOfStudent[0]['nivel']==11 || $inscriptionOfStudent[0]['nivel']==1){
                      reset($objNota);
                      while ($val = current($objNota)) {
                        if($val['notaCualitativa']){
                          $bolNote = true;
                        }
                          next($objNota);
                      }
                    }else{
                      //validate if the student has notas
                      reset($objNota);
                      while ($val = current($objNota)) {
                        if($val['notaCuantitativa']>0){
                          $bolNote = true;
                        }
                          next($objNota);
                      }
                    }//end if
          return $bolNote;
    }
    private function getParalelosStudent($posCurso, $ue) {
        $em = $this->getDoctrine()->getManager();
        list($nivel, $ciclo, $grado) = explode('-', $this->aCursos[$posCurso]);

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('IDENTITY(iec.paraleloTipo) as paraleloTipo')
                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
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
                ->select('ast.id', 'ast.asignatura, ieco.id as iecoId')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco', 'WITH', 'iec.id=ieco.insitucioneducativaCurso')
                ->leftjoin('SieAppWebBundle:AsignaturaTipo', 'ast', 'WITH', 'ieco.asignaturaTipo=ast.id')
                ->leftjoin('SieAppWebBundle:AreaTipo', 'at', 'WITH', 'ast.areaTipo = at.id')
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
                ->orderBy('at.id,ast.id')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    /**
     * get the asignaturas per student
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
    private function createFormMaterias($idStudent, $ue, $nextcurso, $currentInscription) {

        $em = $this->getDoctrine()->getManager();
        list($nextnivel, $nextciclo, $nextgrado) = explode('-', $this->aCursos[$nextcurso]);
        $onivel = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nextnivel);
        $ogrado = $em->getRepository('SieAppWebBundle:GradoTipo')->find($nextgrado);
        $ociclo = $em->getRepository('SieAppWebBundle:GradoTipo')->find($nextciclo);

        return $formOmitido = $this->createFormBuilder()
                ->setAction($this->generateUrl('inscription_talento_regNotas'))
                //->add('ue', 'text', array('data' => $ue, 'disabled' => true, 'label' => 'Unidad Educativa', 'attr' => array('required' => false, 'maxlength' => 8, 'class' => 'form-control')))
                //->add('ueid', 'hidden', array('data' => $ue))
                ->add('institucionEducativa', 'text', array('data' => $ue, 'label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                ->add('institucionEducativaName', 'text', array('label' => 'Unidad Educativa', 'data' => $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($ue)->getInstitucioneducativa(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('nivel', 'text', array('data' => $onivel->getNivel(), 'disabled' => true, 'attr' => array('required' => false, 'class' => 'form-control')))
                ->add('grado', 'text', array('data' => $ogrado->getGrado(), 'disabled' => true, 'attr' => array('required' => false, 'class' => 'form-control')))
                ->add('nivelId', 'hidden', array('data' => $onivel->getId()))
                ->add('gradoId', 'hidden', array('data' => $ogrado->getId()))
                ->add('cicloId', 'hidden', array('data' => $ociclo->getId()))
                ->add('idStudent', 'hidden', array('required' => false, 'mapped' => false, 'data' => $idStudent))
                //->add('lastue', 'hidden', array('mapped' => false, 'data' => $lastue))
                ->add('idStudent', 'hidden', array('mapped' => false, 'data' => $idStudent))
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
                ->add('turno', 'choice', array('attr' => array('requirede' => true, 'class' => 'form-control')))
                ->add('currentInscription', 'hidden', array('data' => $currentInscription))
                ->add('save', 'submit', array('label' => 'Continuar'))
                ->getForm();
    }

//
//    private function createFormMaterias($idStudent, $ue, $nextcurso) {
//        $em = $this->getDoctrine()->getManager();
//        list($nextnivel, $nextciclo, $nextgrado) = explode('-', $this->aCursos[$nextcurso]);
//        $onivel = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nextnivel);
//        $ogrado = $em->getRepository('SieAppWebBundle:GradoTipo')->find($nextgrado);
//        $ociclo = $em->getRepository('SieAppWebBundle:GradoTipo')->find($nextciclo);
//        return $this->createFormBuilder()
//                        ->setAction($this->generateUrl('inscription_talento_saveMateriasNotas'))
//                        //->add('numeroacta', 'text', array('label' => 'Número de Acta Talento', 'attr' => array('required' => true, 'class' => 'form-control', 'maxlength' => 14, 'pattern' => '[0-9]{12,14}')))
//                        ->add('ue', 'text', array('data' => $ue, 'disabled' => true, 'label' => 'Unidad Educativa', 'attr' => array('required' => false, 'maxlength' => 8, 'class' => 'form-control')))
//                        ->add('ueid', 'hidden', array('data' => $ue))
//                        ->add('nivel', 'text', array('data' => $onivel->getNivel(), 'disabled' => true, 'attr' => array('required' => false, 'class' => 'form-control')))
//                        ->add('grado', 'text', array('data' => $ogrado->getGrado(), 'disabled' => true, 'attr' => array('required' => false, 'class' => 'form-control')))
//                        ->add('nivelid', 'hidden', array('data' => $onivel->getId()))
//                        ->add('gradoid', 'hidden', array('data' => $ogrado->getId()))
//                        ->add('cicloid', 'hidden', array('data' => $ociclo->getId()))
//                        ->add('idStudent', 'hidden', array('required' => false, 'mapped' => false, 'data' => $idStudent))
//                        //->add('paralelo', 'choice', array('attr' => array('required' => true, 'class' => 'form-control')))
//                        ->add('paralelo', 'entity', array('label' => 'Paralelo', 'empty_value' => 'seleccionar...', 'attr' => array('class' => 'form-control'),
//                            'class' => 'SieAppWebBundle:ParaleloTipo',
//                            'query_builder' => function (EntityRepository $e) {
//                        return $e->createQueryBuilder('p')
//                                ->where('p.id in (:ue)')
//                                ->setParameter('ue', $this->oparalelos)
//                                ->distinct()
//                                ->orderBy('p.paralelo', 'ASC')
//                        ;
//                    }, 'property' => 'paralelo'
//                        ))
//                        ->add('turno', 'choice', array('attr' => array('required' => true, 'class' => 'form-control')))
//                        ->add('save', 'submit', array('label' => 'Registrar'))
//                        ->getForm();
//    }

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

//    private function getCurrentInscriptionsStudent($id) {
//        //$session = new Session();
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
//        $query = $entity->createQueryBuilder('e')
//                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(ei.nivelTipo) as nivelId', 'IDENTITY(ei.gestionTipo) as gestion', 'IDENTITY(ei.gradoTipo) as gradoId', 'IDENTITY(ei.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(ei.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa')
//                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
//                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'ei.institucioneducativa = i.id')
//                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'ei.nivelTipo = n.id')
//                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'ei.gradoTipo = g.id')
//                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'ei.paraleloTipo = p.id')
//                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'ei.turnoTipo = t.id')
//                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
//                ->where('e.codigoRude = :id')
//                ->andwhere('ei.gestionTipo = :gestion')
//                ->setParameter('id', $id)
//                ->setParameter('gestion', $this->session->get('currentyear'))
//                ->orderBy('ei.fechaInscripcion', 'ASC')
//                ->getQuery();
//        try {
//            return $query->getResult();
//        } catch (Exception $ex) {
//            return $ex;
//        }
//    }

    /**
     * todo the registration of inscription
     * @param Request $request
     *
     */
    public function saveMateriasNotasAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //get the variblees
        $form = $request->get('form');
        $materias = $request->get('materias');
//        print_r($form);
//        die;
        try {

            //update the matricula to talento in estudianteInscripcion
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
            $currentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(58));
            $em->persist($currentInscription);
            $em->flush();
            //save promedios
            //$studentAsignatura = new EstudianteAsignatura();
            $userId = $this->session->get('userId');

            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');");
            $query->execute();

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
                $studentnotas->setUsuarioId($userId);
                $studentnotas->setFechaRegistro(new \DateTime('now'));
                $studentnotas->setFechaModificacion(new \DateTime('now'));
                $em->persist($studentnotas);
                $em->flush();
                next($materias);
            }
            ////die;
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
//            $studentInscription = new EstudianteInscripcion();
//            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
//            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']));
//            $studentInscription->setCodUeProcedenciaId($form['ueid']);
//            $studentInscription->setObservacion(1);
//            $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
//            $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
//            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objNextInscription->getId()));
//            $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(27));
//            $em->persist($studentInscription);
//            $em->flush();
            //select * from sp_agrega_estudiante_asignatura(2015, 60630013, '6063001220119A', 12, 5, 8, '1');
            $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']);
            //echo "select * from sp_agrega_estudiante_asignatura('" . $this->session->get('currentyear') . "','" . $form['ueid'] . "','" . $objStudent->getCodigoRude() . "','" . $form['nivelid'] . "','" . $form['gradoid'] . "','" . $form['turno'] . "','" . $form['paralelo'] . "');";
            $query = $em->getConnection()->prepare("select * from sp_agrega_estudiante_asignatura('" . $this->session->get('currentyear') . "','" . $form['ueid'] . "','" . $objStudent->getCodigoRude() . "','" . $form['nivelid'] . "','" . $form['gradoid'] . "','" . $form['turno'] . "','" . $form['paralelo'] . "');");
            $query->execute();


            // Try and commit the transaction
            $em->getConnection()->commit();

            $this->session->getFlashBag()->add('goodtalento', 'Inscripción Talento registrado sin problemas');
            return $this->redirectToRoute('inscription_talento_index');

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
    public function findIEAction($id, $nivel, $grado) {

        $em = $this->getDoctrine()->getManager();

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

                $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
                $nombreIE = ($institucion) ? $institucion->getInstitucioneducativa() : 'No existe Unidad Educativa';

                $objOmitido = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
                $query = $objOmitido->createQueryBuilder('iec')
                        ->select('IDENTITY(iec.paraleloTipo) as paraleloTipo', 'pt.paralelo as paralelo')
                        ->leftjoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'iec.paraleloTipo = pt.id')
                        //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')->leftjoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'iec.paraleloTipo = pt.id')
                        ->where('iec.institucioneducativa = :id')
                        ->andwhere('iec.nivelTipo = :nivel')
                        ->andwhere('iec.gradoTipo = :grado')
                        ->andwhere('iec.gestionTipo = :gestion')
                        ->setParameter('id', $id)
                        ->setParameter('nivel', $nivel)
                        ->setParameter('grado', $grado)
                        ->setParameter('gestion', $this->session->get('currentyear'))->distinct()->orderBy('iec.paraleloTipo', 'ASC')
                        ->getQuery();
                $infoOmitido = $query->getResult();

                foreach ($infoOmitido as $info) {
                    $paralelo[$info['paraleloTipo']] = $info['paralelo'];
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

    public function regNotasAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        //validate allow access
        $arrAllowAccessOption = array(7,8);
        if(!in_array($this->session->get('roluser'),$arrAllowAccessOption)){
          $defaultController = new DefaultCont();
          $defaultController->setContainer($this->container);

          $swAccess = $defaultController->getAccessMenuCtrl(array('sie'=>$form['institucionEducativa'], 'gestion'=>$this->session->get('currentyear')));
          //validate if the user download the sie file
          if($swAccess){
            $message = 'No se puede realizar la inscripción debido a que ya descargo el archivo SIE, favor realizar este proceso con su Tec. de Departamento';
            $this->addFlash('warningrezago', $message);
            return $this->redirectToRoute('inscription_rezago_index');
          }

        }


        $nextInscription = serialize(array('sie' => $form['institucionEducativa'], 'nivel' => $form['nivelId'], 'grado' => $form['gradoId'], 'paralelo' => $form['paralelo'], 'turno' => $form['turno']));
        $currentInscription = unserialize($form['currentInscription']);
        //get the current inscription data
        $aAreasCurrentLevel = $this->getAsignaturasPerStudent($currentInscription['sie'], $currentInscription['nivel'], $currentInscription['grado'], $currentInscription['paralelo'], $currentInscription['turno']);
        //get the sutdents areas
        $objStudianteAsig = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('estudianteInscripcion' => $currentInscription['inscriptionId']));

        //get the students areas
        $aAreasCurrentLevelTrue = array();
        foreach ($aAreasCurrentLevel as $areaCurrent) {
            reset($objStudianteAsig);
            while ($val = current($objStudianteAsig)) {
                if ($val->getInstitucioneducativaCursoOferta()->getId() == $areaCurrent['iecoId']) {
                    $aAreasCurrentLevelTrue[] = $areaCurrent;
                }
                next($objStudianteAsig);
            }
        }
        //get the next inscription data
        $aAreasNextLevel = $this->getAsignaturasPerStudent($form['institucionEducativa'], $form['nivelId'], $form['gradoId'], $form['paralelo'], $form['turno']);
        /*
          select * from institucioneducativa_curso
          where institucioneducativa_id = 52210001 and nivel_tipo_id='12' and grado_tipo_id='6' and paralelo_tipo_id='1' and turno_tipo_id='1'
          and gestion_tipo_id='2015'
         */
        $currentCriteria = array(
            'institucioneducativa' => $currentInscription['sie'],
            'nivelTipo' => $currentInscription['nivel'],
            'gradoTipo' => $currentInscription['grado'],
            'paraleloTipo' => $currentInscription['paralelo'],
            'turnoTipo' => $currentInscription['turno'],
            'gestionTipo' => $this->session->get('currentyear')
        );
        $dataCurrent = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy($currentCriteria);

        $nextCriteria = array(
            'institucioneducativa' => $form['institucionEducativa'],
            'nivelTipo' => $form['nivelId'],
            'gradoTipo' => $form['gradoId'],
            'paraleloTipo' => $form['paralelo'],
            'turnoTipo' => $form['turno'],
            'gestionTipo' => $this->session->get('currentyear')
        );
        $dataNext = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy($nextCriteria);

        return $this->render('SieRegularBundle:InscriptionTalento:regNotas.html.twig', array(
                    'datastudent' => $em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']),
                    'formMaterias' => $this->saveDataForm($form['currentInscription'], $nextInscription, $form['idStudent'])->createView(),
                    'materiasnotas' => $aAreasCurrentLevel,
                    'materiasnotas1' => $aAreasCurrentLevelTrue,
                    'materiasnotas2' => $aAreasNextLevel,
                    'dataCurrent' => $dataCurrent,
                    'dataNext' => $dataNext,
                    'currentSie'=> $currentInscription['sie'],
                    'nextSie'=> $form['institucionEducativa']
        ));
    }

    private function saveDataForm($currentInscription, $nextInscription, $idStudent) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('inscription_talento_saveData'))
                        ->add('numeroacta', 'text', array('label' => 'NÚMERO DE ACTA SUPLETORIA', 'attr' => array('class' => 'form-control')))
                        ->add('currentInscription', 'hidden', array('data' => $currentInscription))
                        ->add('nextInscription', 'hidden', array('data' => $nextInscription))
                        ->add('idStudent', 'hidden', array('data' => $idStudent))
                        ->add('notaCualitativa', 'textarea', array(
                            'label' => 'Nota Cualitativa',
                            'attr' => array('class'=>'form-control','style' => 'width:600px')
                              ))
                        ->add('save', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-default')))
                        ->getForm()
        ;
    }

    public function saveDataAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //get the variblees
        $form = $request->get('form');
        $materias = $request->get('currentmaterias');
        $nextmaterias = $request->get('nextmaterias');
        $currentData = unserialize($form['currentInscription']);
        $nextData = unserialize($form['nextInscription']);

        try {

            //update the matricula to talento in estudianteInscripcion
            $objInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $query = $objInscription->createQueryBuilder('ei')
                    ->select('(ei.id) as inscriptionId, iec.id as iecId')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                    ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                    ->leftJoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo = it.id')
                    ->where('ei.estudiante = :idstudent')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->andwhere('ei.estadomatriculaTipo = :etipo')
                    ->andwhere('it.id = :ietipo')
                    ->setParameter('idstudent', $form['idStudent'])
                    ->setParameter('gestion', $this->session->get('currentyear'))
                    ->setParameter('etipo', 4)
                    ->setParameter('ietipo', 1)
                    ->getQuery();
            $objresultInscription = $query->getResult();

            $currentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($objresultInscription[0]['inscriptionId']);
            $currentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(58));
            $em->persist($currentInscription);
            $em->flush();
            //add the areas to the student
            $responseAddAreas = $this->addAreasToStudent($objresultInscription[0]['inscriptionId'], $objresultInscription[0]['iecId']);
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');");
            $query->execute();

            //save promedios
            //$studentAsignatura = new EstudianteAsignatura();
            $userId = $this->session->get('userId');
            reset($materias);
            while ($val = current($materias)) {
                $studentnotas = new EstudianteNota();
                $studentnotas->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(5));
                $studentnotas->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array(
                            'estudianteInscripcion' => $currentInscription->getId(),
                            'institucioneducativaCursoOferta' => key($materias)
                )));
                $studentnotas->setNotaCuantitativa(current($materias));
                $studentnotas->setUsuarioId($userId);
                $studentnotas->setFechaRegistro(new \DateTime('now'));
                $studentnotas->setFechaModificacion(new \DateTime('now'));
                $studentnotas->setNotaCualitativa('');
                $studentnotas->setRecomendacion('');
                $em->persist($studentnotas);
                $em->flush();
                next($materias);
            }

            //update the las inscription
//            $lastInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
//                'estudiante' => $form['idStudent'], 'gestionTipo' => $this->session->get('currentyear')
//            ));
            //get the id of institucioineducativaCurso
            $objNextInscription = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                'paraleloTipo' => $nextData['paralelo'],
                'turnoTipo' => $nextData['turno'],
                'nivelTipo' => $nextData['nivel'],
                'gradoTipo' => $nextData['grado'],
                //'cicloTipo' => $nextData['ciclo'],
                'institucioneducativa' => $nextData['sie'],
                'gestionTipo' => $this->session->get('currentyear')
            ));

            //save the new inscription
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']));
            $studentInscription->setCodUeProcedenciaId($currentData['sie']);
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
            $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objNextInscription->getId()));
            $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(27));
            $em->persist($studentInscription);
            $em->flush();
//            echo $studentInscription->getId();
//            die;
            $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']);
            //echo "select * from sp_agrega_estudiante_asignatura('" . $this->session->get('currentyear') . "','" . $form['ueid'] . "','" . $objStudent->getCodigoRude() . "','" . $form['nivelid'] . "','" . $form['gradoid'] . "','" . $form['turno'] . "','" . $form['paralelo'] . "');";
//            $query = $em->getConnection()->prepare("select * from sp_agrega_estudiante_asignatura('" . $this->session->get('currentyear') . "','" . $nextData['sie'] . "','" . $objStudent->getCodigoRude() . "','" . $nextData['nivel'] . "','" . $nextData['grado'] . "','" . $nextData['turno'] . "','" . $nextData['paralelo'] . "');");
//            $query->execute();
//
//            $objNextInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
//            $query = $objNextInscription->createQueryBuilder('ei')
//                    ->select('(ei.id) as inscriptionId')
//                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
//                    ->where('ei.estudiante = :idstudent')
//                    ->andwhere('iec.gestionTipo = :gestion')
//                    ->andwhere('ei.estadomatriculaTipo = :etipo')
//                    ->andwhere('iec.institucioneducativa = :sie')
//                    ->andwhere('iec.nivelTipo = :nivel')
//                    ->andwhere('iec.gradoTipo = :grado')
//                    ->andwhere('iec.paraleloTipo = :paralelo')
//                    ->andwhere('iec.turnoTipo = :turno')
//                    ->setParameter('idstudent', $form['idStudent'])
//                    ->setParameter('gestion', $this->session->get('currentyear'))
//                    ->setParameter('etipo', 4)
//                    ->setParameter('sie', $nextData['sie'])
//                    ->setParameter('nivel', $nextData['nivel'])
//                    ->setParameter('grado', $nextData['grado'])
//                    ->setParameter('paralelo', $nextData['paralelo'])
//                    ->setParameter('turno', $nextData['turno'])
//                    ->getQuery();
//            $objNextresultInscription = $query->getResult();
//
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');");
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');");
            $query->execute();

            //save notas primver Bim
            reset($nextmaterias);
            while ($val = current($nextmaterias)) {
                //save the relation between student and asignaturas
                $studentAsignatura = new EstudianteAsignatura();
                $studentAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
                $studentAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription->getId()));
                $studentAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find(key($nextmaterias)));
                $studentAsignatura->setFechaRegistro(new \DateTime('now'));
                $em->persist($studentAsignatura);
                $em->flush();

                //save the student nota
                $studentnotas = new EstudianteNota();
                $studentnotas->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(1));
                $studentnotas->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array(
                            'estudianteInscripcion' => $studentInscription->getId(),
                            //'gestionTipo' => $this->session->get('currentyear'),
                            //'asignaturaTipo' => key($nextmaterias)
                            'institucioneducativaCursoOferta' => key($nextmaterias)
                )));
                $studentnotas->setNotaCuantitativa(current($nextmaterias));
                $studentnotas->setUsuarioId($userId);
                $studentnotas->setFechaRegistro(new \DateTime('now'));
                $studentnotas->setFechaModificacion(new \DateTime('now'));
                $studentnotas->setNotaCualitativa('');
                $studentnotas->setRecomendacion('');
                $em->persist($studentnotas);
                $em->flush();
                next($nextmaterias);
            }
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota_cualitativa');");
            $query->execute();
            //set the cualitativas notas to the old inscription
            $cualitativaOldInscription = new EstudianteNotaCualitativa();
            $cualitativaOldInscription->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(5));
            $cualitativaOldInscription->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($objresultInscription[0]['inscriptionId']));
            $cualitativaOldInscription->setNotaCuantitativa(0);
            $cualitativaOldInscription->setNotaCualitativa(mb_strtoupper(trim($form['notaCualitativa']),'utf-8'));
            $cualitativaOldInscription->setRecomendacion('');
            $cualitativaOldInscription->setUsuarioId($this->session->get('userId'));
            $cualitativaOldInscription->setFechaRegistro(new \DateTime('now'));
            $cualitativaOldInscription->setFechaModificacion(new \DateTime('now'));
            $cualitativaOldInscription->setObs('');
            $em->persist($cualitativaOldInscription);
            $em->flush();

            //set the cualitativas notas to the new inscription
            $cualitativaNewInscription = new EstudianteNotaCualitativa();
            $cualitativaNewInscription->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(1));
            $cualitativaNewInscription->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription->getId()));
            $cualitativaNewInscription->setNotaCuantitativa(0);
            $cualitativaNewInscription->setNotaCualitativa(mb_strtoupper(trim($form['notaCualitativa']),'utf-8'));
            $cualitativaNewInscription->setRecomendacion('');
            $cualitativaNewInscription->setUsuarioId($this->session->get('userId'));
            $cualitativaNewInscription->setFechaRegistro(new \DateTime('now'));
            $cualitativaNewInscription->setFechaModificacion(new \DateTime('now'));
            $cualitativaNewInscription->setObs('');
            $em->persist($cualitativaNewInscription);
            $em->flush();
            //commented by krlos
            //select * from sp_agrega_estudiante_asignatura(2015, 60630013, '6063001220119A', 12, 5, 8, '1');
//            $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']);
//            //echo "select * from sp_agrega_estudiante_asignatura('" . $this->session->get('currentyear') . "','" . $form['ueid'] . "','" . $objStudent->getCodigoRude() . "','" . $form['nivelid'] . "','" . $form['gradoid'] . "','" . $form['turno'] . "','" . $form['paralelo'] . "');";
//            $query = $em->getConnection()->prepare("select * from sp_agrega_estudiante_asignatura('" . $this->session->get('currentyear') . "','" . $form['ueid'] . "','" . $objStudent->getCodigoRude() . "','" . $form['nivelid'] . "','" . $form['gradoid'] . "','" . $form['turno'] . "','" . $form['paralelo'] . "');");
//            $query->execute();
            // Try and commit the transaction
            $em->getConnection()->commit();

            $this->session->getFlashBag()->add('goodtalento', 'Inscripción Talento registrado sin problemas');
            return $this->redirectToRoute('inscription_talento_index');

            //return $this->render('SieAppWebBundle:InscriptionTalento:nextCurso.html.twig');
            //return $this->redirect($this->generateUrl('inscription_talento_index'));
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
