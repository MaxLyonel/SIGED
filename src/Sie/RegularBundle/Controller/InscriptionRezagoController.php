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
class InscriptionRezagoController extends Controller {

    private $session;
    public $lugarNac;
    public $dpto;
    public $aCursos;
    public $lastUE;
    public $oparalelos;
    public $arrLevelYearOld;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $this->aCursos = $this->fillCursos();

        $this->arrLevelYearOld = array(
            '12' => array(6,7,8,9,10,11),
            '13' => array(12,13,14,15,15,15),
        );
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
        return $this->render($this->session->get('pathSystem') . ':InscriptionRezago:index.html.twig', array(
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

    public function validationInscription($data){

        //create db conexino
        $em = $this->getDoctrine()->getManager();

         $inscription3 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $query = $inscription3->createQueryBuilder('ei')
                    ->select('iec.id as iecId,(ei.id) as inscriptionId,IDENTITY(iec.institucioneducativa) as institucioneducativa ', 'IDENTITY(iec.nivelTipo) as nivel', 'IDENTITY(iec.gradoTipo) as grado', 'IDENTITY(iec.paraleloTipo) as paralelo', 'IDENTITY(iec.turnoTipo) as turno', 'IDENTITY(iec.cicloTipo) as ciclo', 'IDENTITY(ei.estadomatriculaTipo) as matricula, IDENTITY(ei.estudiante) as studentId')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
                    ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                    ->leftJoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo = it.id')
                    ->where('ei.estudiante = :id')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->andwhere('ei.estadomatriculaTipo IN (:matricula)')
                    ->andwhere('it.id = :ietipo')
                    ->setParameter('id', $data['studentId'])
                    ->setParameter('gestion', $data['gestion'])
                    ->setParameter('matricula', $data['matricula'])

                    ->setParameter('ietipo', 1)
                    ->getQuery();

            $studentInscription = $query->getResult();

            return $studentInscription;

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
            // $yearsStudent = $this->get('seguimiento')->getYearsOldsStudentByFecha($student->getFechaNacimiento()->format('d-m-Y'), "30-06-".$this->session->get('currentyear'));

            // if($yearsStudent[0]<=15){
            //     // no thing to do
            // }else{
            //     $message = 'Estudiante no cumple con la edad requerida';
            //     $this->addFlash('warningrezago', $message);
            //     return $this->redirectToRoute('inscription_rezago_index');
            // }

            //validate 2 times on rezago
            $conditionRezago = array('estudiante' => $student->getId(), 'estadomatriculaTipo' => '57');
            $objInscrWithRezago = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy($conditionRezago);
            if (sizeof($objInscrWithRezago) >= 15) {
                $message = 'Estudiante cuenta con ' . sizeof($objInscrWithRezago) . ' inscripciones... para esta gestión';
                $this->addFlash('warningrezago', $message);
                return $this->redirectToRoute('inscription_rezago_index');
            }

               

            //look for inscription like rezago  current year by ID = 4 efectivo
            $inscriptionData = array('studentId' => $student->getId(), 'gestion' => $this->session->get('currentyear'),'matricula' => array('4'));
            $studentInscription = $this->validationInscription($inscriptionData);
            
            if (!$studentInscription) {
                $message = 'No se puede realizar la inscripción, Estudiante no presenta inscripción';
                $this->addFlash('warningrezago', $message);
                return $this->redirectToRoute('inscription_rezago_index');
            }

            //validate inscription to inicial
            if ($studentInscription[0]['nivel'] == 11) {
                $message = 'No se puede realizar la inscripción, Estudiante ' . $form['codigoRude'] . ' en nivel Inicial.';
                $this->addFlash('warningrezago', $message);
                return $this->redirectToRoute('inscription_rezago_index');
            }

            //look for inscription like rezago  by id 10 NI
            $arrMatriculas=array(5,6,10);
            $inscriptionData = array('studentId' => $student->getId(), 'gestion' => $this->session->get('currentyear')-1,'matricula' => $arrMatriculas );
            $studentInscriptionLastYear = $this->validationInscription($inscriptionData);
            if($this->session->get('userName') != '4926577' ){
                if ($studentInscriptionLastYear) {
                    if(in_array($studentInscriptionLastYear[0]['matricula'], array(4,5,11)) ){
                        $message = 'No se puede realizar la inscripción, el estudiante presenta inscripción en la gestion pasada';
                        $this->addFlash('warningrezago', $message);
                        return $this->redirectToRoute('inscription_rezago_index');
                    }
                }                
            }

            // look student data to validate the years old trougth the level and grado
            $student = $em->getRepository('SieAppWebBundle:Estudiante')->find($studentInscription[0]['studentId']);
            $yearsStudent = $this->get('seguimiento')->getYearsOldsStudentByFecha($student->getFechaNacimiento()->format('d-m-Y'), "30-06-".$this->session->get('currentyear'));
            if($yearsStudent[0] > $this->arrLevelYearOld[$studentInscription[0]['nivel']][$studentInscription[0]['grado']-1] && $yearsStudent[0] <= 15){
                // nothing to do
            }else{
                $message = 'No se puede realizar la inscripción, la/el estudiante no cumple con la edad requerida';
                $this->addFlash('warningrezago', $message);
                return $this->redirectToRoute('inscription_rezago_index');
            }            

            //get the notas of student
            $boolStudentCalification = $this->getStudentNotasValidation($studentInscription, $student->getId());
            //check if the student has calification
            // if($boolStudentCalification){
            if(false){
              $message = 'Estudiante con rude: ' . $form['codigoRude'] . ' no cuenta con calificaciones, no es posible realizar la operación ';
              $this->addFlash('warningrezago', $message);
              return $this->redirectToRoute('inscription_rezago_index');
            }
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
                $this->session->getFlashBag()->add('notirezago', 'Estudiante no cuenta con inscripción para la gestión ' . $this->session->get('currentyear'));
                return $this->redirectToRoute('inscription_rezago_index');
            }
        } else {
            $this->session->getFlashBag()->add('notirezago', 'Estudiante no registrado');
            return $this->redirectToRoute('inscription_rezago_index');
        }
        //add students areas
        $studentAddAreasCurrent = $this->addAreasToStudent($studentInscription[0]['inscriptionId'], $studentInscription[0]['iecId']);


        //everything is ok build the info
        return $this->render($this->session->get('pathSystem') . ':InscriptionRezago:result.html.twig', array(
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
                        if($val['notaCuantitativa'] && $val['notaCuantitativa']>0){
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
                ->setAction($this->generateUrl('inscription_rezago_regNotas'))
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
//

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

                $objOmitido = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
                $query = $objOmitido->createQueryBuilder('ei')
                        ->select('IDENTITY(iec.paraleloTipo) as paraleloTipo', 'pt.paralelo as paralelo')
                        ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')->leftjoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'iec.paraleloTipo = pt.id')
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
        // validate if the ue is MODULAR
        $objUeModular = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array(
            'institucioneducativaId'                   => $form['institucionEducativa'], 
            'institucioneducativaHumanisticoTecnicoTipo' => 3, 
            'gestionTipoId'                            => $this->session->get('currentyear'),

        ));
        
        if(!$objUeModular){

            //validate the students yaer old 
            $student = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']);
            $yearsStudent = $this->get('seguimiento')->getYearsOldsStudentByFecha($student->getFechaNacimiento()->format('d-m-Y'), "30-06-".$this->session->get('currentyear'));

            if($yearsStudent[0]<=15){
                // no thing to do
            }else{
                $message = 'Estudiante no cumple con la edad requerida';
                $this->addFlash('warningrezago', $message);
                return $this->redirectToRoute('inscription_rezago_index');
            }

        }

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
        // $areasEstudiante = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('estudianteInscripcion'=>$idEstudianteInscripcion));
        //get the next inscription data
        $aAreasNextLevel = $this->getAsignaturasPerStudent($form['institucionEducativa'], $form['nivelId'], $form['gradoId'], $form['paralelo'], $form['turno']);
//        echo "<pre>";
//        print_r($aAreasCurrentLevel);
//        echo "</pre>";die;
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

        return $this->render('SieRegularBundle:InscriptionRezago:regNotas.html.twig', array(
                    'datastudent' => $em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']),
                    'formMaterias' => $this->saveDataForm($form['currentInscription'], $nextInscription, $form['idStudent'])->createView(),
                    'materiasnotas' => $aAreasCurrentLevel,
                    'materiasnotas1' => $aAreasCurrentLevelTrue,
                    'materiasnotas2' => $aAreasNextLevel,
                    'dataCurrent' => $dataCurrent,
                    'dataNext' => $dataNext
        ));
    }

    private function saveDataForm($currentInscription, $nextInscription, $idStudent) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('inscription_rezago_saveData'))
                        ->add('numeroacta', 'text', array('label' => 'NÚMERO DE ACTA SUPLETORIA', 'attr' => array('class' => 'form-control')))
                        ->add('currentInscription', 'hidden', array('data' => $currentInscription))
                        ->add('nextInscription', 'hidden', array('data' => $nextInscription))
                        ->add('idStudent', 'hidden', array('data' => $idStudent))
                        // ->add('notaCualitativa', 'textarea', array(
                        //     'label' => 'Nota Cualitativa',
                        //     'attr' => array('class'=>'form-control','style' => 'width:600px')
                        //       ))
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
            $currentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(57));
            $em->persist($currentInscription);
            $em->flush();
            //add the areas to the student
            $responseAddAreas = $this->addAreasToStudent($objresultInscription[0]['inscriptionId'], $objresultInscription[0]['iecId']);
            //echo $currentInscription->getId();die;
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');");
            $query->execute();

            //save promedios
            //$studentAsignatura = new EstudianteAsignatura();
            $userId = $this->session->get('userId');
            reset($materias);
            while ($val = current($materias)) {
                //$studianteAsignatura = new EstudianteAsignatura();

                $studentnotas = new EstudianteNota();
                $studentnotas->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(5));
                $studentnotas->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array(
                            'estudianteInscripcion' => $currentInscription->getId(),
                            ///'gestionTipo' => $this->session->get('currentyear'),
                            //'asignaturaTipo' => key($materias)
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
            $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(51));
            $em->persist($studentInscription);
            $em->flush();
//            echo $studentInscription->getId();
//            die;
            $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']);
            //echo "select * from sp_agrega_estudiante_asignatura('" . $this->session->get('currentyear') . "','" . $form['ueid'] . "','" . $objStudent->getCodigoRude() . "','" . $form['nivelid'] . "','" . $form['gradoid'] . "','" . $form['turno'] . "','" . $form['paralelo'] . "');";
//            $query = $em->getConnection()->prepare("select * from sp_agrega_estudiante_asignatura('" . $this->session->get('currentyear') . "','" . $nextData['sie'] . "','" . $objStudent->getCodigoRude() . "','" . $nextData['nivel'] . "','" . $nextData['grado'] . "','" . $nextData['turno'] . "','" . $nextData['paralelo'] . "');");
//            $query->execute();
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
            // reset($nextmaterias);
            // while ($val = current($nextmaterias)) {
            //     //save the relation between student and asignaturas
            //     $studentAsignatura = new EstudianteAsignatura();
            //     $studentAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
            //     $studentAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription->getId()));
            //     $studentAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find(key($nextmaterias)));
            //     $studentAsignatura->setFechaRegistro(new \DateTime('now'));
            //     $em->persist($studentAsignatura);
            //     $em->flush();

            //     //save the student nota
            //     $studentnotas = new EstudianteNota();
            //     $studentnotas->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(1));
            //     $studentnotas->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array(
            //                 'estudianteInscripcion' => $studentInscription->getId(),
            //                 //'gestionTipo' => $this->session->get('currentyear'),
            //                 //'asignaturaTipo' => key($nextmaterias)
            //                 'institucioneducativaCursoOferta' => key($nextmaterias)
            //     )));
            //     $studentnotas->setNotaCuantitativa(current($nextmaterias));
            //     $studentnotas->setUsuarioId($userId);
            //     $studentnotas->setFechaRegistro(new \DateTime('now'));
            //     $studentnotas->setFechaModificacion(new \DateTime('now'));
            //     $studentnotas->setNotaCualitativa('');
            //     $studentnotas->setRecomendacion('');
            //     $em->persist($studentnotas);
            //     $em->flush();
            //     next($nextmaterias);
            // }
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota_cualitativa');");
            $query->execute();
            //set the cualitativas notas to the old inscription
            // $cualitativaOldInscription = new EstudianteNotaCualitativa();
            // $cualitativaOldInscription->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(5));
            // $cualitativaOldInscription->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($objresultInscription[0]['inscriptionId']));
            // $cualitativaOldInscription->setNotaCuantitativa(0);
            // $cualitativaOldInscription->setNotaCualitativa(mb_strtoupper(trim($form['notaCualitativa']),'utf-8'));
            // $cualitativaOldInscription->setRecomendacion('');
            // $cualitativaOldInscription->setUsuarioId($this->session->get('userId'));
            // $cualitativaOldInscription->setFechaRegistro(new \DateTime('now'));
            // $cualitativaOldInscription->setFechaModificacion(new \DateTime('now'));
            // $cualitativaOldInscription->setObs('');
            // $em->persist($cualitativaOldInscription);
            // $em->flush();

            //set the cualitativas notas to the new inscription
            // $cualitativaNewInscription = new EstudianteNotaCualitativa();
            // $cualitativaNewInscription->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(1));
            // $cualitativaNewInscription->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription->getId()));
            // $cualitativaNewInscription->setNotaCuantitativa(0);
            // $cualitativaNewInscription->setNotaCualitativa(mb_strtoupper(trim($form['notaCualitativa']),'utf-8'));
            // $cualitativaNewInscription->setRecomendacion('');
            // $cualitativaNewInscription->setUsuarioId($this->session->get('userId'));
            // $cualitativaNewInscription->setFechaRegistro(new \DateTime('now'));
            // $cualitativaNewInscription->setFechaModificacion(new \DateTime('now'));
            // $cualitativaNewInscription->setObs('');
            // $em->persist($cualitativaNewInscription);
            // $em->flush();
            //commented by krlos
            //select * from sp_agrega_estudiante_asignatura(2015, 60630013, '6063001220119A', 12, 5, 8, '1');
//            $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']);
//            //echo "select * from sp_agrega_estudiante_asignatura('" . $this->session->get('currentyear') . "','" . $form['ueid'] . "','" . $objStudent->getCodigoRude() . "','" . $form['nivelid'] . "','" . $form['gradoid'] . "','" . $form['turno'] . "','" . $form['paralelo'] . "');";
//            $query = $em->getConnection()->prepare("select * from sp_agrega_estudiante_asignatura('" . $this->session->get('currentyear') . "','" . $form['ueid'] . "','" . $objStudent->getCodigoRude() . "','" . $form['nivelid'] . "','" . $form['gradoid'] . "','" . $form['turno'] . "','" . $form['paralelo'] . "');");
//            $query->execute();
            // Try and commit the transaction
            $em->getConnection()->commit();

            $this->session->getFlashBag()->add('goodrezago', 'Inscripción Rezago registrado sin problemas');
            return $this->redirectToRoute('inscription_rezago_index');

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
                // $studentAsignatura->setFechaLastUpdate(new \DateTime('now'));
                $em->persist($studentAsignatura);
                $em->flush();
                //echo "<hr>";
            }
        }
        return true;
    }

    /**
     * build the new student extranjero
     * @return type
     */
    public function norudeAction() {
        return $this->render($this->session->get('pathSystem') . ':InscriptionRezago:norude.html.twig', array(
                    'form' => $this->formnorude()->createView(),
        ));
    }

    private function formnorude() {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('inscription_rezago_saveext'))
                        //->add('pais', 'entity', array('label' => 'País', 'class' => 'SieAppWebBundle:PaisTipo', 'attr' => array('class' => 'form-control')))
                        //->add('ueprocedencia', 'text', array('label' => 'Unidad Educativa Procedencia', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-Z]{3,85}')))
                        ->add('ci', 'text', array('required' => false, 'label' => 'CI', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{5,8}', 'required' => false)))
                        ->add('complemento', 'text', array('required' => false, 'label' => 'Complemento', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9 a-z A-Z]{1,2}', 'maxlength' => '2', 'style' => 'text-transform:uppercase', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que la expedicion de su CI, por favor no coloque abreviaturas de departamentos")))
                        ->add('paterno', 'text', array('required' => false, 'label' => 'Paterno', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,50}')))
                        ->add('materno', 'text', array('required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,50}')))
                        ->add('nombre', 'text', array('attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñ A-ZÑ]{3,50}')))
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

        return $this->render($this->session->get('pathSystem') . ':InscriptionRezago:resultnorude.html.twig', array(
                    'newstudent' => $form,
                    'samestudents' => $sameStudents,
                    'formninguno' => $this->nobodyform($form)->createView()
        ));
    }

    private function nobodyform($data) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('inscription_rezago_savenew'))
                        ->add('paterno', 'hidden', array('data' => strtoupper($data['paterno']), 'required' => false))
                        ->add('materno', 'hidden', array('data' => strtoupper($data['materno']), 'required' => false))
                        ->add('nombre', 'hidden', array('data' => strtoupper($data['nombre'])))
                        ->add('fnac', 'hidden', array('data' => $data['fnac']))
                        ->add('ci', 'hidden', array('data' => $data['ci']))
                        ->add('complemento', 'hidden', array('data' => $data['complemento']))
                        //->add('pais', 'hidden', array('data' => $data['pais']))
                        //->add('ueprocedencia', 'hidden', array('data' => $data['ueprocedencia']))
                        ->add('genero', 'hidden', array('data' => $data['genero']))
                        ->add('ninguno', 'submit', array('label' => 'Nueva Inscripción', 'attr' => array('class' => 'btn btn-warning btn-sm btn-block')))
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
        return $this->render($this->session->get('pathSystem') . ':InscriptionRezago:newextranjero.html.twig', array(
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
                ->setAction($this->generateUrl('inscription_rezago_savenewextranjero'))
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
            //insert a new record with the new selected variables and put matriculaFinID like 5
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($student->getId()));
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime('now'));
            $studentInscription->setFechaRegistro(new \DateTime('now'));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objCurso->getId()));
            $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(51));
            $studentInscription->setCodUeProcedenciaId(0);
            $em->persist($studentInscription);
            $em->flush();
            // Try and commit the transaction
            $em->getConnection()->commit();
            $this->session->getFlashBag()->add('goodext', 'Estudiante con Rezago ya fue inscrito...');
            return $this->redirect($this->generateUrl('inscription_rezago_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
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
     * generate the new rude to the new student
     * @param type $cadena
     * @return type
     */
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

}
