<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\PaisTipo;
use Sie\AppWebBundle\Entity\ValidacionProceso;
use Sie\AppWebBundle\Form\EstudianteType;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
/**
 * Estudiante controller.
 *
 */
class EstudianteUpdateController extends Controller {

    private $session;
    public $lugarNac;
    public $dpto;
    public $pais;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Lists all Estudiante entities.
     *
     */
    public function indexAction() {

        $em = $this->getDoctrine()->getManager();

        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':Estudiante:indexstudent.html.twig', array(
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
        $mode1 = 'Modificación y/o inclusión de nombre(s), apellido(s) y/o modificación de fecha de nacimiento, error(es) cometidos por personal administrativo.';
        $mode2 = 'Modificación y/o inclusión de datos propios del Certificado de nacimiento, Carnet de Identidad y otros datos que son parte del RUDE.';
        $mode3 = 'Modificación y/o inclusión de nombre(s), apellido(s), fecha de nacimiento por reconocimiento de padre y/o madre de familia o por cambio de nombre(s) y/o apellido(s) de manera voluntaria.';
        $aGestiones = array('2015' => '2015', '2014' => '2014', '2013' => '2013');
        $form = $this->createFormBuilder($estudiante)
                ->setAction($this->generateUrl('sie_estudiantes_result'))
                ->add('codigoRude', 'text', array('required' => false, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9a-zA-Z\sñÑ]{14,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                //->add('gestion', 'choice', array('mapped' => false, 'label' => 'Gestion', 'choices' => $aGestiones, 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                ->add('mode1', 'checkbox', array('mapped' => false, 'label' => $mode1, 'required' => false))
                ->add('mode2', 'checkbox', array('mapped' => false, 'label' => $mode2, 'required' => false))
                ->add('mode3', 'checkbox', array('mapped' => false, 'label' => $mode3, 'required' => false))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                //->add('public', 'checkbox', array('mapped'=>false,'label' => 'Show this entry publicly?', 'required' => false))
                ->getForm();
        return $form;
    }

    /**
     * obtien el formulario para realizar la modificacion
     * @param Request $request
     */
    public function resultAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $calidad = false;
        $calidadValidacionId = 0;
        $this->session->set('yearQA',isset($form['gestion'])?$form['gestion']:$this->session->get('currentyear'));

        //que no esté en sexto y que esté en calidad
        if (isset($form['gestion'])) {
            $calidad = true;
            if (isset($form['calidad'])) {
                $calidadValidacionId = $form['calidad'];
            }
        }
              
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => trim($form['codigoRude']) ));
        //verificamos si existe el estudiante
        if ($student) {
          //check if it has tramite
          //dump($this->session->get('roluser'));
          //$validationType = ($this->session->get('roluser')==10)?1:2;
          if($this->get('seguimiento')->getStudentTramite($form['codigoRude'], $this->session->get('roluser'))){
            $this->session->getFlashBag()->add('noticemodi', 'No se permite el cambio, estudiante tiene tramite de diplomas...');
            return $this->redirectToRoute('sie_estudiantes');
          }
          
          // to validate the tuicion value
          if(!$this->get('funciones')->getInscriptionToValidateTuicion($form, $this->session->get('yearQA'), $calidad)){
            $this->session->getFlashBag()->add('noticemodi', 'No tiene tuición sobre la UNIDAD EDUCATIVA o el estado de la/el estudiante no es el permitido.');
            return $this->redirectToRoute('sie_estudiantes');
          }

            //get the values to the build the forms
            $m1 = isset($form['mode1']) ? $form['mode1'] : 0;
            $m2 = isset($form['mode2']) ? $form['mode2'] : 0;
            $m3 = isset($form['mode3']) ? $form['mode3'] : 0;
//
//            $studentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
//                'estudiante' => $student->getId(),
//                'gestionTipo' => $this->session->get('currentyear'),
//                'estadomatriculaTipo' => '4'
//            ));

            $inscription2 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $query = $inscription2->createQueryBuilder('ei')
                    ->select('ei.id as id, IDENTITY(iec.institucioneducativa) as idInstEdu')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
                    ->where('ei.estudiante = :id')
                    //->andwhere('iec.gestionTipo = :gestion')
                    ->andwhere('ei.estadomatriculaTipo in (:mat)')
                    ->setParameter('id', $student->getId())
                    //->setParameter('gestion', $this->session->get('currentyear'))
                    ->setParameter('mat', array('4', '5'))
                    ->getQuery();

            $studentInscription = $query->getResult();

            //verificamos si tiene inscripcion en la gestion 2015
            //if ($studentInscription) {
                /*
                 * Recorre lista de roles en busca del rol 15 - Autorización, si es diplomas de bachiller no verifica la tuicion
                 */
                if ($this->session->get('pathSystem') === 'SieDiplomaBundle') {
                    $rolDiploma = 0;
                    for ($i = 0; $i < count($this->session->get('roluser')[0]); $i++) {
                        if ($this->session->get('roluser')[0]['id'] === 15) {
                            $rolDiploma = 15;
                        }
                    }
                } else {
                    /*
                     * Verifica la tuicion de un usuario sobre una unidad educativa
                     */
                    /*$query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
                    $query->bindValue(':user_id', $this->session->get('userId'));
                    $query->bindValue(':sie', $studentInscription[0]['idInstEdu']);
                    if ($this->session->get('pathSystem') === 'SieDiplomaBundle') {
                        $query->bindValue(':roluser', $rolDiploma);
                    } else {
                        $query->bindValue(':roluser', $this->session->get('roluser'));
                    }

                    $query->execute();
                    $aTuicion = $query->fetchAll();

                    //check the tuicion
                    if (!$aTuicion[0]['get_ue_tuicion']) {
                        $this->session->getFlashBag()->add('noticemodi', 'Usuario no tiene tución sobre la Unidad Eductiva');
                        return $this->redirectToRoute('sie_estudiantes');
                    }*/
                }
                $infoInscription = $this->getCurrentInscriptionsStudent($student->getCodigoRude());
                //dump($m1,$m2,$m3);
                if ($calidadValidacionId>0) {
                    $m1 = 1;
                    $m2 = 1;
                    $m3 = 0;
                }

                $form_mode1 = (isset($form['mode1'])) ? $this->createFormMode1($student, $m3)->createView() : '';
                $form_mode2 = (isset($form['mode2'])) ? $this->createFormMode2($student, $m3)->createView() : '';
                $form_mode3 = (isset($form['mode3'])) ? $this->createFormMode3($student)->createView() : '';

                if ($calidadValidacionId>0) {
                    $form_mode1 = null;
                    $form_mode2 = null;
                    $form_mode3 = null;
                    $form_mode4 = (isset($form['mode3'])) ? $this->createFormModeFecNac($student)->createView() : '';
                }
                //print_r($infoInscription);
                //dump($form_mode1,$form_mode2,$form_mode3);die;
            /*} else {
                $this->session->getFlashBag()->add('noticemodi', 'Estudiante no cuenta con inscripción para gestión ' . $this->session->get('currentyear'));
                return $this->redirectToRoute('sie_estudiantes');
            }*/
        } else {
            $this->session->getFlashBag()->add('noticemodi', 'Estudiante no existe');
            return $this->redirectToRoute('sie_estudiantes');
        }

        //que no esté en sexto y que esté en calidad
        if ($calidadValidacionId>0) {
            return $this->render($this->session->get('pathSystem') . ':Estudiante:result.html.twig', array(
                        'student' => $student,
                        'infoinscription' => $infoInscription,
                        'form_mode1' => $form_mode1,
                        'form_mode2' => $form_mode2,
                        'form_mode3' => $form_mode3,
                        'form_mode4' => $form_mode4,
                        'm1' => $m1,
                        'm2' => $m2,
                        'm3' => $m3,
                        'calidad' => $calidad,
                        'calidadValidacionId' => $calidadValidacionId
            ));
        } else {
            return $this->render($this->session->get('pathSystem') . ':Estudiante:result.html.twig', array(
                        'student' => $student,
                        'infoinscription' => $infoInscription,
                        'form_mode1' => $form_mode1,
                        'form_mode2' => $form_mode2,
                        'form_mode3' => $form_mode3,
                        'form_mode4' => null,
                        'm1' => $m1,
                        'm2' => $m2,
                        'm3' => $m3
            ));
        }
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
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa', 'e.codigoRude as codigoRude')
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

//    private function getCurrentInscriptionsStudent($id) {
//        //$session = new Session();
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
//        $query = $entity->createQueryBuilder('e')
//                ->select('e.codigoRude', 'n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(ei.nivelTipo) as nivelId', 'IDENTITY(ei.gestionTipo) as gestion', 'IDENTITY(ei.gradoTipo) as gradoId', 'IDENTITY(ei.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(ei.paraleloTipo) as paraleloId', 'i.id as sie', 'i.institucioneducativa')
//                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id=ei.estudiante')
//                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'ei.institucioneducativa=i.id')
//                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'ei.nivelTipo = n.id')
//                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'ei.gradoTipo = g.id')
//                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'ei.paraleloTipo = p.id')
//                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'ei.turnoTipo = t.id')
//                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo=em.id')
//                ->where('e.codigoRude = :id')
//                ->andWhere('ei.gestionTipo = :gestion')
//                ->setParameter('id', $id)
//                ->setParameter('gestion', $this->session->get('currentyear'))
//                ->getQuery();
//        try {
//            return $query->getResult();
//        } catch (Exception $ex) {
//            return $ex;
//        }
//    }

    /**
     * create the students mode 1 form
     * @param type $student
     * @return form mode 1
     */
    private function createFormModeFecNac($student) {

        $em = $this->getDoctrine()->getManager();
        $ext = '';
        $cistudent = '';
        
        $form = $this->createFormBuilder($student)
        // ->setAction($this->generateUrl('sie_estudiantes_updatestudentA', array('id' => $student->getId())))
        ->add('codigoRude', 'hidden', array('label' => 'codigo rude'))
        ->add('ci', 'hidden', array('label' => 'carnetIdentidad','required' => false, 'mapped' => false, 'data'=>$student->getCarnetIdentidad()))
        ->add('complemento', 'hidden', array('label' => 'complemento','required' => false, 'mapped' => false))
        ->add('id', 'hidden', array('label' => 'id','required' => false, 'data'=>$student->getId()))
        ->add('fechaNacimiento', 'text', array('data' => $student->getFechaNacimiento()->format('d-m-Y'), 'label' => 'Fecha Nacimiento', 'attr' => array('class' => 'form-control')))
        
        // ->add('save', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
        ->add('save', 'button', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary', 'onclick'=>'updateStudentFormA()')))
        ->getForm();

        return $form;
    }


    /**
     * create the students mode 1 form
     * @param type $student
     * @return form mode 1
     */
    private function createFormMode1($student, $m3) {

        $em = $this->getDoctrine()->getManager();
        $ext = '';
        $cistudent = '';
        if ($student->getCarnetIdentidad()) {
            $aCi = explode('-', $student->getCarnetIdentidad());
            if (sizeof($aCi) > 1) {
                $ext = $aCi[0];
                $cistudent = $aCi[1];
            } else {
                $cistudent = $aCi[0];
            }
        }

        if(!$student->getLugarNacTipo()){
            $objStudentUpdate = $em->getRepository('SieAppWebBundle:Estudiante')->find($student->getId());
            $objStudentUpdate->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(79355));
            $objStudentUpdate->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(11));
            $em->persist($objStudentUpdate);
            $em->flush();
        }

        //echo $student->getLugarNacTipo()->getId();        die;
        if (!$m3) {
            $form = $this->createFormBuilder($student)
                    // ->setAction($this->generateUrl('sie_estudiantes_updatestudentA', array('id' => $student->getId())))
                    ->add('codigoRude', 'hidden', array('label' => 'codigo rude'))
                    ->add('ci', 'hidden', array('label' => 'carnetIdentidad','required' => false, 'mapped' => false, 'data'=>$student->getCarnetIdentidad()))
                    ->add('complemento', 'hidden', array('label' => 'complemento','required' => false, 'mapped' => false))
                    ->add('id', 'hidden', array('label' => 'id','required' => false, 'data'=>$student->getId()))
                    ->add('paterno', 'text', array('label' => 'Paterno', 'required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñü- A-ZÑÜÖÄÃËÏ\'-]{2,30}')))
                    ->add('materno', 'text', array('label' => 'Materno', 'required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñü- A-ZÑÜÖÄÃËÏ\'-]{2,30}')))
                    ->add('nombre', 'text', array('label' => 'Nombre', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñ-\'-. A-ZÑ-\'-.]{2,40}')))
                    ->add('fechaNacimiento', 'text', array('data' => $student->getFechaNacimiento()->format('d-m-Y'), 'label' => 'Fecha Nacimiento', 'attr' => array('readonly' => 'readonly', 'class' => 'form-control')))
                    
                    // ->add('save', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                    ->add('save', 'button', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary', 'onclick'=>'updateStudentFormA()')))
                    ->getForm();
        } else {
            //look for new values
            $entity = $em->getRepository('SieAppWebBundle:Estudiante');
            $query = $entity->createQueryBuilder('e')
                    ->select('e.carnetIdentidad', 'e.complemento', 'e.oficialia', 'e.libro', 'e.partida', 'e.folio', 'IDENTITY(e.generoTipo) as generoId', 'e.localidadNac', 'IDENTITY(lt.lugarTipo) as lugarTipoId', 'p.pais', 'd.departamento')
                    ->leftjoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'e.lugarNacTipo=lt.id')
                    ->leftjoin('SieAppWebBundle:DepartamentoTipo', 'd', 'WITH', 'lt.departamentoTipo=d.id')
                    ->leftjoin('SieAppWebBundle:PaisTipo', 'p', 'WITH', 'd.paisTipo=p.id')
                    ->where('e.codigoRude = :codigoRude')
                    ->setParameter('codigoRude', $student->getCodigoRude())
                    ->getQuery();
            $infoStudent = $query->getArrayResult();
            $this->lugarNac = $student->getLugarNacTipo();
            $this->pais = $student->getPaisTipo()->getId();
            $dataProvincia = ($student->getLugarProvNacTipo()) ? $student->getLugarProvNacTipo()->getId() : 1;

            $form = $this->createFormBuilder($student)
                    // ->setAction($this->generateUrl('sie_estudiantes_updatestudentD', array('id' => $student->getId())))
                    ->add('id', 'hidden', array('label' => 'id','required' => false, 'data'=>$student->getId()))
                    ->add('codigoRude', 'hidden', array('label' => 'codigo rude'))
                    ->add('paterno', 'text', array('label' => 'Paterno', 'required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñü A-ZÑÜÖÄÃËÏ\'-]{2,30}')))
                    ->add('materno', 'text', array('label' => 'Materno', 'required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñü A-ZÑÜÖÄÃËÏ\'-]{2,30}')))
                    ->add('nombre', 'text', array('label' => 'Nombre', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñ-\'-. A-ZÑ-\'-.]{2,40}')))
                    ->add('fechaNacimiento', 'text', array('data' => $student->getFechaNacimiento()->format('d-m-Y'), 'label' => 'Fecha Nacimiento', 'attr' => array('class' => 'form-control')))
                    ->add('pais', 'entity', array('label' => 'Pais', 'attr' => array('class' => 'form-control'),
                        'mapped' => false, 'class' => 'SieAppWebBundle:PaisTipo',
                        'query_builder' => function (EntityRepository $e) {
                    return $e->createQueryBuilder('pt')
                            ->orderBy('pt.id', 'ASC');
                }, 'property' => 'pais',
                        'data' => $em->getReference("SieAppWebBundle:PaisTipo", $student->getPaisTipo()->getId())))
                    ->add('departamento', 'entity', array('label' => 'Departamento', 'attr' => array('class' => 'form-control'),
                        'mapped' => false, 'class' => 'SieAppWebBundle:LugarTipo',
                        'query_builder' => function (EntityRepository $e) {
                    $consult = $e->createQueryBuilder('lt');
                    if ($this->pais == 1) {
                        $consult->where('lt.lugarNivel = :id')
                        ->setParameter('id', '1')
                        ->orderBy('lt.codigo', 'ASC');
                    } else {
                        $consult->where('lt.id = :id')
                        ->setParameter('id', '79355')
                        ->orderBy('lt.codigo', 'ASC');
                    }
                    return $consult;
                }, 'property' => 'lugar',
                        'data' => $em->getReference("SieAppWebBundle:LugarTipo", ($this->pais == 1) ? $student->getLugarNacTipo()->getId() : '79355' )
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
                        'data' => $em->getReference("SieAppWebBundle:LugarTipo", $dataProvincia)
                    ))
                    ->add('localidad', 'text', array('data' => $student->getLocalidadNac(), 'required' => false, 'mapped' => false, 'label' => 'Localidad', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñü- A-ZÑÜÖÄËÏ-\']{2,30}')))
                    ->add('ci', 'text', array('required' => false, 'mapped' => false, 'data' => $cistudent, 'label' => 'CI', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{2,12}', 'maxlength' => '12')))
                    ->add('complemento', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getComplemento(), 'label' => 'Complemento', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que la expedicion de su CI, por favor no coloque abreviaturas de departamentos")))
                    //->add('generoTipo', 'choice', array('required' => false, 'mapped' => false, 'choices' => $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll(), 'label' => 'Género', 'attr' => array('class' => 'form-control')))
                    // ->add('generoTipo', 'entity', array('label' => 'Género', 'attr' => array('class' => 'form-control'),
                    //     'mapped' => false, 'class' => 'SieAppWebBundle:GeneroTipo',
                    //     'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $student->getGeneroTipo()->getId())
                    // ))
                    ->add('generoTipo', 'entity', array('label' => 'Género', 'attr' => array('class' => 'form-control'),
                    'mapped' => false, 'class' => 'SieAppWebBundle:GeneroTipo',
                    'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('gt')
                                ->where('gt.id IN (:arrGenero)')
                                ->setParameter('arrGenero', array(1,2))
                                // ->orderBy('lt.codigo', 'ASC')
                        ;
                    }, 'property' => 'genero',
                    'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $student->getGeneroTipo()->getId())
                ))
                    ->add('oficialia', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getOficialia(), 'label' => 'Oficialia', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-./_ ]{0,40}')))
                    ->add('libro', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getLibro(), 'label' => 'Libro', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-/_() ]{0,20}')))
                    ->add('partida', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getPartida(), 'label' => 'Partida', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-/ ]{0,15}')))
                    ->add('folio', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getFolio(), 'label' => 'Folio', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-/ ]{0,15}')))
                    ->add('pasaporte', 'text', array('label' => 'Pasaporte','required' => false, 'mapped' => false, 'data'=>$student->getPasaporte()))
                    // ->add('save', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                      ->add('save', 'button', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary', 'onclick'=>'updateStudentFormD()')))
                    ->getForm();
        }

        return $form;
    }

    /**
     * create the students mode 1 form
     * @param type $student
     * @return form mode 1
     */
    private function createFormMode2($student, $m3) {
        $em = $this->getDoctrine()->getManager();
        $ext = '';
        $cistudent = '';
        if ($student->getCarnetIdentidad()) {
            $aCi = explode('-', $student->getCarnetIdentidad());
            if (sizeof($aCi) > 1) {
                $ext = $aCi[0];
                $cistudent = $aCi[1];
            } else {
                $cistudent = $aCi[0];
            }
        }
        if (!$m3) {
            $form = $this->createFormBuilder($student)
                    // ->setAction($this->generateUrl('sie_estudiantes_updatestudentB', array('id' => $student->getId())))
                    ->add('codigoRude', 'hidden', array('label' => 'codigo rude'))

                    ->add('ci', 'hidden', array('label' => 'carnetIdentidad','required' => false, 'mapped'=>false, 'data'=>$student->getCarnetIdentidad()))

                    ->add('complemento', 'hidden', array('label' => 'complemento','required' => false, 'mapped'=>false, 'data'=>$student->getComplemento()))
                    ->add('id', 'hidden', array('label' => 'id','required' => false, 'data'=>$student->getId()))


                    ->add('paterno', 'text', array('label' => 'Paterno', 'required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñü- A-ZÑÜÖÄÃËÏ\'-]{2,30}')))
                    ->add('materno', 'text', array('label' => 'Materno', 'required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñü- A-ZÑÜÖÄÃËÏ\'-]{2,30}')))
                    ->add('nombre', 'text', array('label' => 'Nombre', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñ-\'.- A-ZÑ-\'.-]{2,40}')))
                    ->add('fechaNacimiento', 'text', array('data' => $student->getFechaNacimiento()->format('d-m-Y'), 'label' => 'Fecha Nacimiento', 'attr' => array('class' => 'form-control')))
                    ->add('resolAprobatoria', 'textarea', array('required' => false, 'data' => $student->getResolucionaprovatoria(), 'mapped' => false, 'attr' => array('class' => 'form-control', 'rows' => '2', 'cols' => '3', 'maxlength' => '15')))
                    ->add('obsAdicional', 'textarea', array('required' => false, 'data' => $student->getObservacionadicional(), 'mapped' => false, 'attr' => array('class' => 'form-control', 'maxlength' => '50')))
                    // ->add('save', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                    
                     ->add('save', 'button', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary', 'onclick'=>'updateStudentFormB()')))
                     
                    ->getForm();
        } else {
            //look for new values
            $entity = $em->getRepository('SieAppWebBundle:Estudiante');
            $query = $entity->createQueryBuilder('e')
                    ->select('e.carnetIdentidad', 'e.complemento', 'e.oficialia', 'e.libro', 'e.partida', 'e.folio', 'IDENTITY(e.generoTipo) as generoId', 'e.localidadNac', 'IDENTITY(lt.lugarTipo) as lugarTipoId', 'p.pais', 'd.departamento')
                    ->leftjoin('SieAppWebBundle:LugarTipo', 'lt', 'WITH', 'e.lugarNacTipo=lt.id')
                    ->leftjoin('SieAppWebBundle:DepartamentoTipo', 'd', 'WITH', 'lt.departamentoTipo=d.id')
                    ->leftjoin('SieAppWebBundle:PaisTipo', 'p', 'WITH', 'd.paisTipo=p.id')
                    ->where('e.codigoRude = :codigoRude')
                    ->setParameter('codigoRude', $student->getCodigoRude())
                    ->getQuery();
            $infoStudent = $query->getArrayResult();
            $this->lugarNac = $student->getLugarNacTipo();
            $this->pais = $student->getPaisTipo()->getId();
            $form = $this->createFormBuilder($student)
                    // ->setAction($this->generateUrl('sie_estudiantes_updatestudentE', array('id' => $student->getId())))
                    ->add('codigoRude', 'hidden', array('label' => 'codigo rude'))

                    ->add('id', 'hidden', array('label' => 'id','required' => false, 'data'=>$student->getId()))

                    ->add('paterno', 'text', array('label' => 'Paterno', 'required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñü A-ZÑÜÖÄÃËÏ\'-]{2,30}')))
                    ->add('materno', 'text', array('label' => 'Materno', 'required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñü A-ZÑÜÖÄÃËÏ\'-]{2,30}')))
                    ->add('nombre', 'text', array('label' => 'Nombre', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñ-\'-. A-ZÑ-\'.-]{2,40}')))
                    ->add('fechaNacimiento', 'text', array('data' => $student->getFechaNacimiento()->format('d-m-Y'), 'label' => 'Fecha Nacimiento', 'attr' => array('class' => 'form-control')))
                    ->add('resolAprobatoria', 'textarea', array('required' => false, 'data' => $student->getResolucionaprovatoria(), 'mapped' => false, 'attr' => array('class' => 'form-control', 'rows' => '2', 'cols' => '3', 'maxlength' => '15')))
                    ->add('obsAdicional', 'textarea', array('required' => false, 'data' => $student->getObservacionadicional(), 'mapped' => false, 'attr' => array('class' => 'form-control', 'maxlength' => '50')))
                    ->add('pais', 'entity', array('label' => 'Pais', 'attr' => array('class' => 'form-control'),
                        'mapped' => false, 'class' => 'SieAppWebBundle:PaisTipo',
                        'query_builder' => function (EntityRepository $e) {
                    return $e->createQueryBuilder('pt')
                            ->orderBy('pt.id', 'ASC');
                }, 'property' => 'pais',
                        'data' => $em->getReference("SieAppWebBundle:PaisTipo", $student->getPaisTipo()->getId())))
                    ->add('departamento', 'entity', array('label' => 'Departamento', 'attr' => array('class' => 'form-control'),
                        'mapped' => false, 'class' => 'SieAppWebBundle:LugarTipo',
                        'query_builder' => function (EntityRepository $e) {
                    $consult = $e->createQueryBuilder('lt');
                    if ($this->pais == 1) {
                        $consult->where('lt.lugarNivel = :id')
                        ->setParameter('id', '1')
                        ->orderBy('lt.codigo', 'ASC');
                    } else {
                        $consult->where('lt.id = :id')
                        ->setParameter('id', '79355')
                        ->orderBy('lt.codigo', 'ASC');
                    }
                    return $consult;
                }, 'property' => 'lugar',
                        'data' => $em->getReference("SieAppWebBundle:LugarTipo", ($this->pais == 1) ? $student->getLugarNacTipo()->getId() : '79355' )
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
                        'data' => $em->getReference("SieAppWebBundle:LugarTipo", $student->getLugarProvNacTipo()->getId())
                    ))
                    ->add('localidad', 'text', array('data' => $student->getLocalidadNac(), 'required' => false, 'mapped' => false, 'label' => 'Localidad', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñü- A-ZÑÜÖÄËÏ-\']{2,30}')))
                    ->add('ci', 'text', array('required' => false, 'mapped' => false, 'data' => $cistudent, 'label' => 'CI', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{2,12}', 'maxlength' => '12')))
                    ->add('complemento', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getComplemento(), 'label' => 'Complemento', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que la expedicion de su CI, por favor NO coloque abreviaturas de departamentos")))
                    //->add('generoTipo', 'choice', array('required' => false, 'mapped' => false, 'choices' => $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll(), 'label' => 'Género', 'attr' => array('class' => 'form-control')))
                    // ->add('generoTipo', 'entity', array('label' => 'Género', 'attr' => array('class' => 'form-control'),
                    //     'mapped' => false, 'class' => 'SieAppWebBundle:GeneroTipo',
                    //     'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $student->getGeneroTipo()->getId())
                    // ))
                    ->add('generoTipo', 'entity', array('label' => 'Género', 'attr' => array('class' => 'form-control'),
                    'mapped' => false, 'class' => 'SieAppWebBundle:GeneroTipo',
                    'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('gt')
                                ->where('gt.id IN (:arrGenero)')
                                ->setParameter('arrGenero', array(1,2))
                                // ->orderBy('lt.codigo', 'ASC')
                        ;
                    }, 'property' => 'genero',
                    'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $student->getGeneroTipo()->getId())
                    ))
                    ->add('oficialia', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getOficialia(), 'label' => 'Oficialia', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-./_ ]{0,40}')))
                    ->add('libro', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getLibro(), 'label' => 'Libro', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-/_() ]{0,20}')))
                    ->add('partida', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getPartida(), 'label' => 'Partida', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-/ ]{0,15}')))
                    ->add('folio', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getFolio(), 'label' => 'Folio', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-/ ]{0,15}')))
                    ->add('pasaporte', 'text', array('label' => 'Pasaporte','required' => false, 'mapped' => false, 'data'=>$student->getPasaporte()))
                    // ->add('save', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                    ->add('save', 'button', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary', 'onclick'=>'updateStudentFormE()')))
                    ->getForm();
        }
        return $form;
    }

    /**
     * create the students mode 1 form
     * @param type $student
     * @return form mode 1
     */
    private function createFormMode3($student) {

        $estudiante = new Estudiante();
        $em = $this->getDoctrine()->getManager();
        //update the lugar nac tipo when is null
        if(!$student->getLugarNacTipo()){
            $objStudentUpdate = $em->getRepository('SieAppWebBundle:Estudiante')->find($student->getId());
            $objStudentUpdate->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(79355));
            $objStudentUpdate->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(11));
            $em->persist($objStudentUpdate);
            $em->flush();
        }

        $this->lugarNac = $student->getLugarNacTipo();

        $dataProvincia = ($student->getLugarProvNacTipo()) ? $student->getLugarProvNacTipo()->getId() : 90;
        $datadepartaemnto = ($student->getLugarNacTipo()) ? $student->getLugarNacTipo()->getId() : 12;
        $this->pais = $student->getPaisTipo()->getId();
        $ext = '';
        $cistudent = '';
        if ($student->getCarnetIdentidad()) {
            $aCi = explode('-', $student->getCarnetIdentidad());
            if (sizeof($aCi) > 1) {
                $ext = $aCi[0];
                $cistudent = $aCi[1];
            } else {
                $cistudent = $aCi[0];
            }
        }
//        echo $student->getPaisTipo()->getId();
//        die;
        $formMode3 = $this->createFormBuilder($estudiante)
                // ->setAction($this->generateUrl('sie_estudiantes_updatestudentC', array('id' => $student->getId())))
                //->setAction($this->generateUrl('sie_estudiantes_result'))
                //->add('codigoRude', 'hidden', array('label' => 'codigo rude'))
                //->add('pais', 'choice', array('mapped' => false, 'label' => 'Pais', 'attr' => array('class' => 'form-control')))
                //->add('pais', 'choice', array('mapped' => false, 'choices' => $em->getRepository('SieAppWebBundle:PaisTipo')->findAll(), 'label' => 'Pais', 'attr' => array('class' => 'form-control')))
                ->add('nombre', 'hidden', array('label' => '','required' => false, 'mapped'=>false, 'data'=>$student->getNombre()))
                ->add('paterno', 'hidden', array('label' => '','required' => false, 'mapped'=>false, 'data'=>$student->getPaterno()))
                ->add('materno', 'hidden', array('label' => '','required' => false, 'mapped'=>false, 'data'=>$student->getMaterno()))
                ->add('fechaNacimiento', 'hidden', array('label' => '','required' => false, 'mapped'=>false, 'data'=>$student->getFechaNacimiento()->format('d-m-Y')))
                
                ->add('id', 'hidden', array('label' => 'id','required' => false, 'data'=>$student->getId()))


                ->add('pais', 'entity', array('label' => 'Pais', 'attr' => array('class' => 'form-control'),
                    'mapped' => false, 'class' => 'SieAppWebBundle:PaisTipo',
                    'query_builder' => function (EntityRepository $e) {
                return $e->createQueryBuilder('pt')
                        ->orderBy('pt.id', 'ASC');
            }, 'property' => 'pais',
                    'data' => $em->getReference("SieAppWebBundle:PaisTipo", $student->getPaisTipo()->getId())))
                ->add('departamento', 'entity', array('label' => 'Departamento', 'attr' => array('class' => 'form-control'),
                    'mapped' => false, 'class' => 'SieAppWebBundle:LugarTipo',
                    'query_builder' => function (EntityRepository $e) {
                $consult = $e->createQueryBuilder('lt');
                if ($this->pais == 1) {
                    $consult->where('lt.lugarNivel = :id')
                    ->setParameter('id', '1')
                    ->orderBy('lt.codigo', 'ASC');
                } else {
                    $consult->where('lt.id = :id')
                    ->setParameter('id', '79355')
                    ->orderBy('lt.codigo', 'ASC');
                }
                return $consult;
            }, 'property' => 'lugar',
                    'data' => $em->getReference("SieAppWebBundle:LugarTipo", ($this->pais == 1) ? $student->getLugarNacTipo()->getId() : '79355' )
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
                    'data' => $em->getReference("SieAppWebBundle:LugarTipo", $dataProvincia)
                ))
                ->add('localidad', 'text', array('data' => $student->getLocalidadNac(), 'required' => false, 'mapped' => false, 'label' => 'Localidad', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-zñü- A-ZÑÜÖÄËÏ-\']{2,30}')))
                ->add('extranjero', 'text', array('required' => false, 'mapped' => false, 'data' => $ext, 'label' => 'Extranjero', 'attr' => array('pattern' => '[eE]{1,1}', 'maxlength' => '1', 'style' => 'text-transform:uppercase', 'class' => 'form-control', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "En caso de ser EXTRANJERO colocar la letra E")))
                ->add('ci', 'text', array('required' => false, 'mapped' => false, 'data' => $cistudent, 'label' => 'CI', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{2,12}', 'maxlength' => '12')))
                ->add('complemento', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getComplemento(), 'label' => 'Complemento', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que expedición, por favor NO colocar abreviaturas de Departamentos")))
                //->add('generoTipo', 'choice', array('required' => false, 'mapped' => false, 'choices' => $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll(), 'label' => 'Género', 'attr' => array('class' => 'form-control')))
                // ->add('generoTipo', 'entity', array('label' => 'Género', 'attr' => array('class' => 'form-control'),
                //     'mapped' => false, 'class' => 'SieAppWebBundle:GeneroTipo',
                //     'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $student->getGeneroTipo()->getId())
                // ))
                ->add('generoTipo', 'entity', array('label' => 'Género', 'attr' => array('class' => 'form-control'),
                    'mapped' => false, 'class' => 'SieAppWebBundle:GeneroTipo',
                    'query_builder' => function (EntityRepository $e) {
                        return $e->createQueryBuilder('gt')
                                ->where('gt.id IN (:arrGenero)')
                                ->setParameter('arrGenero', array(1,2))
                                // ->orderBy('lt.codigo', 'ASC')
                        ;
                    }, 'property' => 'genero',
                    'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $student->getGeneroTipo()->getId())
                ))
                ->add('oficialia', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getOficialia(), 'label' => 'Oficialia', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-./_ ]{0,40}')))
                ->add('libro', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getLibro(), 'label' => 'Libro', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-.,/_() ]{0,20}')))
                ->add('partida', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getPartida(), 'label' => 'Partida', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-/ ]{0,15}')))
                ->add('folio', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getFolio(), 'label' => 'Folio', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9-/ ]{0,15}')))
                ->add('isDoubleNcnal', 'checkbox', array('label'=>'Doble Nacionalidad','data'=>$student->getEsDobleNacionalidad(),'required' => false, 'mapped' => false,'attr' => array('class'   => 'form-control', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => ""),))
                ->add('pasaporte', 'text', array('label' => 'Pasaporte','required' => false, 'mapped' => false, 'data'=>$student->getPasaporte()))
                // ->add('save', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                  ->add('save', 'button', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary', 'onclick'=>'updateStudentFormC()')))
                ->getForm();

        return $formMode3;
    }

    /**
    * check the student info with the segip service
    **/
    private function saveResultSegipService($form){
        //create db conexion
        $em = $this->getDoctrine()->getManager();

        // chec if the student has CI-COMPLEMENTO to do the validation
        $answerSegip=2;
        
        if($form['ci']){

            $arrParametros = array(       'complemento'=>$form['complemento'],
                'primer_apellido'=>$form['paterno'],
                'segundo_apellido'=>$form['materno'],
                'nombre'=>$form['nombre'],
                'fecha_nacimiento'=>$form['fechaNacimiento']);

            if(strlen($form['extranjero'])!=0)
                $arrParametros['extranjero'] = $form['extranjero'];

            $answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $form['ci'],$arrParametros,'prod', 'academico');
            
        }
            
        
    
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['id']);
        if($answerSegip===true){
            $student->setSegipId(1);       
        }else{
            $student->setSegipId(0);       
        }
        $em->flush();
        return $answerSegip;
    }


    /**
     * update an existing Estudiante entity.
     *
     */
    // public function updateStudentAAction(Request $request, $id) {
    public function updateStudentAAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        
        //que esté en calidad
        $calidad = false;
        $validacionProcesoId = 0;
        $validacionTipoId = 0;
        $validacionProcesoEntity = array();
        if (isset($form['calidad'])) {
            $calidad = true;
            $validacionProcesoId = $form['calidad'];            
        }

        $entity = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['id']);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Estudiante entity.');
        }
        $oldDataStudent = clone $entity;
        $oldDataStudent = (array)$oldDataStudent;
        
        // save the segip id - validation
        $resultSegip = $this->saveResultSegipService($form);

        if($resultSegip || $resultSegip == 2){
              
            if ($calidad) {
                $validacionProcesoEntity = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($validacionProcesoId);
                if(count($validacionProcesoEntity)>0){
                    $validacionTipoId = $validacionProcesoEntity->getValidacionReglaTipo()->getId();
                }
            }  

            if ($calidad and $validacionTipoId == 20) { 
                $entity->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
            } else {
                $entity->setPaterno(mb_strtoupper($form['paterno'], 'utf8'));
                $entity->setMaterno(mb_strtoupper($form['materno'], 'utf8'));
                $entity->setNombre(mb_strtoupper($form['nombre'], 'utf8'));
                $entity->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
            }
            $em->flush();
            $updateDataStudent = (array)$entity;
            $this->get('funciones')->setLogTransaccion(
                                   $entity->getId(),
                                    'estudiante',
                                    'U',
                                    '',
                                    $updateDataStudent,
                                    $oldDataStudent,
                                    'SIGED',
                                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
            );

            if($resultSegip == 1){
                $updateMessage = 'Datos Modificados Correctamente - validados con SEGIP';    
            }else{
                $updateMessage = 'Datos Modificados Correctamente';    
            }
            
            $typeMessage = 'success';
            $mainMessage = 'Guardado';

            if ($calidad and count($validacionProcesoEntity)>0) {
                if(count($validacionProcesoEntity)>0){
                    $validacionProcesoEntity->setEsActivo(true);
                    $em->flush();
                }
            } 
            
            // $this->session->getFlashBag()->add('okUpdate', 'Datos Modificados Correctamente');
            // return $this->redirect($this->generateUrl('sie_estudiantes'));
        }else{
            $updateMessage = 'Actualización no realizada, De acuerdo a la validación del SEGIP, el número de carnet no corresponde a los datos (paterno, materno, nombre y fecha de nacimiento)';
            $typeMessage = 'warning';
            $mainMessage = 'Error';
        }

        return $this->render($this->session->get('pathSystem') . ':Estudiante:updateStudent.html.twig', array(
            'updateMessage' => $updateMessage,
            'typeMessage'   => $typeMessage,
            'mainMessage'   => $mainMessage

        ));
    }

    /**
     * update an existing Estudiante entity.
     *
     */
    public function updateStudentBAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
      

        $entity = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['id']);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Estudiante entity.');
        }
        $oldDataStudent = clone $entity;
        $oldDataStudent = (array)$oldDataStudent;

         // save the segip id - validation
        $resultSegip = $this->saveResultSegipService($form);

        if($resultSegip || $resultSegip == 2){
            
            $entity->setPaterno(mb_strtoupper($form['paterno']));
            $entity->setMaterno(mb_strtoupper($form['materno']));
            $entity->setNombre(mb_strtoupper($form['nombre']));
            $entity->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
            $entity->setResolucionaprovatoria($form['resolAprobatoria']);
            $entity->setObservacionadicional($form['obsAdicional']);
            $em->persist($entity);
            $em->flush();
            $updateDataStudent = (array)$entity;
             $this->get('funciones')->setLogTransaccion(
                                   $entity->getId(),
                                    'estudiante',
                                    'U',
                                    '',
                                    $updateDataStudent,
                                    $oldDataStudent,
                                    'SIGED',
                                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
            );          
            // $this->session->getFlashBag()->add('okUpdate', 'Datos Modificados Correctamente');
            // return $this->redirect($this->generateUrl('sie_estudiantes'));
            if($resultSegip == 1){
                $updateMessage = 'Datos Modificados Correctamente - validados con SEGIP';    
            }else{
                $updateMessage = 'Datos Modificados Correctamente';    
            }
            $typeMessage = 'success';
            $mainMessage = 'Guardado';
            
            // $this->session->getFlashBag()->add('okUpdate', 'Datos Modificados Correctamente');
            // return $this->redirect($this->generateUrl('sie_estudiantes'));
        }else{
              $updateMessage = 'Actualización no realizada, De acuerdo a la validación del SEGIP, el número de carnet no corresponde a los datos (paterno, materno, nombre y fecha de nacimiento)';
            $typeMessage = 'warning';
            $mainMessage = 'Error';
        }

        return $this->render($this->session->get('pathSystem') . ':Estudiante:updateStudent.html.twig', array(
            'updateMessage' => $updateMessage,
            'typeMessage'   => $typeMessage,
            'mainMessage'   => $mainMessage

        ));
    }


    /*
    save the log transaccion
    */
    private function updateQAstatus($student){
      // dump($objOldValues);
      // dump($arrNewValues);die;
      $em = $this->getDoctrine()->getManager();
      // get the student with observatino info
      $objStudentObs =  $em->getRepository('SieAppWebBundle:ValidacionProceso')->getNacFechaAndGeneroInfo($student->getCodigoRude(), $this->session->get('yearQA'));
      //opdate observation
      foreach ($objStudentObs as $key => $value) {
          //get observation values  to update
          $objValidationProcessUpdate = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($value->getId());
          $objValidationProcessUpdate->setEsActivo('t');
          $em->persist($objValidationProcessUpdate);
          $em->flush();

      }

      return true;
    }


    /**
     * update an existing Estudiante entity.
     *
     */
    public function updateStudentCAction(Request $request) {
        //die('krlos');
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        $entity = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['id']);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Estudiante entity.');
        }
        $oldDataStudent = clone $entity;
        $oldDataStudent = (array)$oldDataStudent;
          // save the segip id - validation
        $resultSegip = $this->saveResultSegipService($form);
        // dump($resultSegip);
        // dump($form);
        // die;
        if($resultSegip || $resultSegip == 2){

             // $this->saveLogTransaction($entity, $form, __FUNCTION__, false);
        //die('"'.isset($form['departamento']).'"');
        //Array ( [pais] => 0 [departamento] => 1 [provincia] => 23 [localidad] => localidad [ci] => 9977761 [complemento] => N [generoTipo] => 0 [oficialia] => ofi [libro] => libro [partida] => partida [folio] => folio [save] => [_token] => OF55YYMRBysUBsEUK_Op9az4wkkA9nnABC1AGDdwCKY )
//        $entity->setPaterno($form['paterno']);
//        $entity->setMaterno($form['materno']);
//        $entity->setNombre($form['nombre']);
//        $entity->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
//        $entity->setFechaModificacion(new \DateTime(date('Y-m-d')));
        //save about the place
        $entity->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($form['pais']));
        if ($form['pais'] == 1) {
            if (isset($form['departamento']) != '') {
                $entity->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento']));
            }
            if (isset($form['provincia']) != '') {
                $entity->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia']));
            }
            if (isset($form['localidad']) != '') {
                $entity->setLocalidadNac(strtoupper($form['localidad']));
            }
        } else {
            $entity->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(79355));
            $entity->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(11));
            $entity->setLocalidadNac(strtoupper(''));
        }

//        ($form['pais'] == 1) ? $entity->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento'])) : $entity->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(79355));
//        ($form['pais'] == 1) ? $entity->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia'])) : $entity->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(11));
//        ($form['pais'] == 1) ? $entity->setLocalidadNac(strtoupper($form['localidad'])) : $entity->setLocalidadNac(strtoupper(''));
//
        //save about the certificadoNac
        $entity->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['generoTipo']));
        $ci = ($form['extranjero']) ? mb_strtoupper($form['extranjero']) . '-' . $form['ci'] : $form['ci'];
        $entity->setCarnetIdentidad($ci);
        $entity->setComplemento( mb_strtoupper($form['complemento'], 'utf-8'));
        $entity->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['generoTipo']));
        $entity->setOficialia($form['oficialia']);
        $entity->setLibro($form['libro']);
        $entity->setPartida($form['partida']);
        $entity->setFolio($form['folio']);
        $entity->setEsDobleNacionalidad(isset($form['isDoubleNcnal'])?'1':'0');
        $entity->setPasaporte($form['pasaporte']);
        //need to add 2 files too
        $em->flush();
        $updateDataStudent = (array)$entity;
          $this->get('funciones')->setLogTransaccion(
                               $entity->getId(),
                                'estudiante',
                                'U',
                                '',
                                $updateDataStudent,
                                $oldDataStudent,
                                'SIGED',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
        );  
            if($resultSegip == 1){
                $updateMessage = 'Datos Modificados Correctamente - validados con SEGIP';    
            }else{
                $updateMessage = 'Datos Modificados Correctamente';    
            }
            $typeMessage = 'success';
            $mainMessage = 'Guardado';
            // update QA status
            $this->updateQAstatus($entity);
            // $this->session->getFlashBag()->add('okUpdate', 'Datos Modificados Correctamente');
            // return $this->redirect($this->generateUrl('sie_estudiantes'));
        }else{
              $updateMessage = 'Actualización no realizada, De acuerdo a la validación del SEGIP, el número de carnet no corresponde a los datos (paterno, materno, nombre y fecha de nacimiento)';
            $typeMessage = 'warning';
            $mainMessage = 'Error';
        }

        return $this->render($this->session->get('pathSystem') . ':Estudiante:updateStudent.html.twig', array(
            'updateMessage' => $updateMessage,
            'typeMessage'   => $typeMessage,
            'mainMessage'   => $mainMessage

        ));

       
    }

    /**
     * administrativo
     * @param Request $request
     * @param type $id
     * @return type
     * @throws type
     * Save administrativo and manera voluntaria CAMBIOS
     */
    public function updateStudentDAction(Request $request) {
        //dump("***********************");die;
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        
        /*
          print_r($form);
          die; */
        $entity = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['id']);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Estudiante entity.');
        }
        $oldDataStudent = clone $entity;
        $oldDataStudent = (array)$oldDataStudent;

           // save the segip id - validation
        $resultSegip = $this->saveResultSegipService($form);
          if($resultSegip || $resultSegip == 2){
        // $this->saveLogTransaction($entity, $form, __FUNCTION__, true);
        //Array ( [pais] => 0 [departamento] => 1 [provincia] => 23 [localidad] => localidad [ci] => 9977761 [complemento] => N [generoTipo] => 0 [oficialia] => ofi
        //[libro] => libro [partida] => partida [folio] => folio [save] => [_token] => OF55YYMRBysUBsEUK_Op9az4wkkA9nnABC1AGDdwCKY )
        $entity->setPaterno(mb_strtoupper($form['paterno']));
        $entity->setMaterno(mb_strtoupper($form['materno']));
        $entity->setNombre(mb_strtoupper($form['nombre']));
        $entity->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
        $entity->setFechaModificacion(new \DateTime(date('Y-m-d')));
        //save about the place
        $entity->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($form['pais']));
        ($form['pais'] == 1) ? $entity->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento'])) : $entity->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(79355));
        ($form['pais'] == 1) ? $entity->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia'])) : $entity->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(11));
        ($form['pais'] == 1) ? $entity->setLocalidadNac(strtoupper($form['localidad'])) : $entity->setLocalidadNac(strtoupper(''));
        //save about the certificadoNac
        $ci = ($form['extranjero']) ? mb_strtoupper($form['extranjero']) . '-' . $form['ci'] : $form['ci'];
        $entity->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['generoTipo']));
        $entity->setCarnetIdentidad($ci);
        $entity->setComplemento( mb_strtoupper($form['complemento'], 'utf-8'));
        $entity->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['generoTipo']));
        $entity->setOficialia($form['oficialia']);
        $entity->setLibro($form['libro']);
        $entity->setPartida($form['partida']);
        $entity->setFolio($form['folio']);
        $entity->setEsDobleNacionalidad(isset($form['isDoubleNcnal'])?'1':'0');
        $entity->setPasaporte($form['pasaporte']);
        //need to add 2 files too
        $em->flush();
        $updateDataStudent = (array)$entity;
         $this->get('funciones')->setLogTransaccion(
                               $entity->getId(),
                                'estudiante',
                                'U',
                                '',
                                $updateDataStudent,
                                $oldDataStudent,
                                'SIGED',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
        );   

          // get the student with observatino info
          $objStudentObs =  $em->getRepository('SieAppWebBundle:ValidacionProceso')->getNacFechaAndGeneroInfo($entity->getCodigoRude(), $this->session->get('yearQA')-1);
          //opdate observation
          foreach ($objStudentObs as $key => $value) {
              //get observation values  to update
              $objValidationProcessUpdate = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($value->getId());
              $objValidationProcessUpdate->setEsActivo('t');
              $em->persist($objValidationProcessUpdate);
              $em->flush();

          }

        // $this->session->getFlashBag()->add('okUpdate', 'Datos Modificados Correctamente');
        // return $this->redirect($this->generateUrl('sie_estudiantes'));
            if($resultSegip == 1){
                $updateMessage = 'Datos Modificados Correctamente - validados con SEGIP';    
            }else{
                $updateMessage = 'Datos Modificados Correctamente';    
            }
            $typeMessage = 'success';
            $mainMessage = 'Guardado';
                
            // $this->session->getFlashBag()->add('okUpdate', 'Datos Modificados Correctamente');
            // return $this->redirect($this->generateUrl('sie_estudiantes'));
        }else{
              $updateMessage = 'Actualización no realizada, De acuerdo a la validación del SEGIP, el número de carnet no corresponde a los datos (paterno, materno, nombre y fecha de nacimiento)';
            $typeMessage = 'warning';
            $mainMessage = 'Error';
        }

        return $this->render($this->session->get('pathSystem') . ':Estudiante:updateStudent.html.twig', array(
            'updateMessage' => $updateMessage,
            'typeMessage'   => $typeMessage,
            'mainMessage'   => $mainMessage

        ));


    }

    /**
     * save form 1 and form 3
     * @param Request $request
     * @param type $id
     * @return type
     * @throws type
     */
    public function updateStudentEAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        $entity = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['id']);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Estudiante entity.');
        }
        $oldDataStudent = clone $entity;
        $oldDataStudent = (array)$oldDataStudent;
            // save the segip id - validation
        $resultSegip = $this->saveResultSegipService($form);

        if($resultSegip || $resultSegip == 2){
        //Array ( [pais] => 0 [departamento] => 1 [provincia] => 23 [localidad] => localidad [ci] => 9977761 [complemento] => N [generoTipo] => 0 [oficialia] => ofi [libro] => libro [partida] => partida [folio] => folio [save] => [_token] => OF55YYMRBysUBsEUK_Op9az4wkkA9nnABC1AGDdwCKY )
        $entity->setPaterno(mb_strtoupper($form['paterno']));
        $entity->setMaterno(mb_strtoupper($form['materno']));
        $entity->setNombre(mb_strtoupper($form['nombre']));
        $entity->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
        $entity->setResolucionaprovatoria($form['resolAprobatoria']);
        $entity->setObservacionadicional($form['obsAdicional']);
        //save about the place
        $entity->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($form['pais']));
        ($form['pais'] == 1) ? $entity->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento'])) : $entity->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(79355));
        ($form['pais'] == 1) ? $entity->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia'])) : $entity->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(11));
        ($form['pais'] == 1) ? $entity->setLocalidadNac(strtoupper($form['localidad'])) : $entity->setLocalidadNac(strtoupper(''));
        //save about the certificadoNac
        $ci = ($form['extranjero']) ? mb_strtoupper($form['extranjero']) . '-' . $form['ci'] : $form['ci'];
        $entity->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['generoTipo']));
        $entity->setCarnetIdentidad($ci);
        $entity->setComplemento( mb_strtoupper($form['complemento'], 'utf-8'));
        $entity->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['generoTipo']));
        $entity->setOficialia($form['oficialia']);
        $entity->setLibro($form['libro']);
        $entity->setPartida($form['partida']);
        $entity->setFolio($form['folio']);
        $entity->setEsDobleNacionalidad(isset($form['isDoubleNcnal'])?'1':'0');
        $entity->setPasaporte($form['pasaporte']);
        //form 1 y form3
        //need to save departamento, etc,etc
        //need to add 2 files too
        $em->flush();
        $updateDataStudent = (array)$entity;
          $this->get('funciones')->setLogTransaccion(
                               $entity->getId(),
                                'estudiante',
                                'U',
                                '',
                                $updateDataStudent,
                                $oldDataStudent,
                                'SIGED',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
        );      
        // $this->session->getFlashBag()->add('okUpdate', 'Datos Modificados Correctamente');
        // return $this->redirect($this->generateUrl('sie_estudiantes'));
            if($resultSegip == 1){
                $updateMessage = 'Datos Modificados Correctamente - validados con SEGIP';    
            }else{
                $updateMessage = 'Datos Modificados Correctamente';    
            }
            $typeMessage = 'success';
            $mainMessage = 'Guardado';
            
            // $this->session->getFlashBag()->add('okUpdate', 'Datos Modificados Correctamente');
            // return $this->redirect($this->generateUrl('sie_estudiantes'));
        }else{
              $updateMessage = 'Actualización no realizada, De acuerdo a la validación del SEGIP, el número de carnet no corresponde a los datos (paterno, materno, nombre y fecha de nacimiento)';
            $typeMessage = 'warning';
            $mainMessage = 'Error';
        }

        return $this->render($this->session->get('pathSystem') . ':Estudiante:updateStudent.html.twig', array(
            'updateMessage' => $updateMessage,
            'typeMessage'   => $typeMessage,
            'mainMessage'   => $mainMessage

        ));
    }
    /*
    save the log transaccion
    */
    private function saveLogTransaction($objOldValues, $arrNewValues, $theFunction, $updateIt){
      // dump($objOldValues);
      // dump($arrNewValues);die;
      $em = $this->getDoctrine()->getManager();
      $defaultController = new DefaultCont();
      $defaultController->setContainer($this->container);
      if($updateIt){
        $arrValOld = array(
          'paterno' =>  $objOldValues->getPaterno(),
          'materno' =>  $objOldValues->getMaterno(),
          'nombre' =>  $objOldValues->getNombre(),
          'resolucion' =>  $objOldValues->getResolucionaprovatoria(),
          'observacion' =>  $objOldValues->getObservacionadicional()
        );
      }
       $arrValOld = array(
        'paisTipo' =>  $objOldValues->getPaisTipo()->getId(),
        'lugarNac'  =>  ($arrNewValues['pais'] != 1) ? $em->getRepository('SieAppWebBundle:LugarTipo')->find(79355):$objOldValues->getLugarNacTipo()->getId(),
        // 'provincia' =>  ($arrNewValues['pais'] == 1) ? $em->getRepository('SieAppWebBundle:LugarTipo')->find(11):$objOldValues->getLugarProvNacTipo()->getId(),
        'localidad' =>  ($arrNewValues['pais'] == 1) ? $objOldValues->setLocalidadNac(strtoupper($arrNewValues['localidad'])) : $objOldValues->setLocalidadNac(strtoupper('')),
        'carnetIdentidad' =>  $objOldValues->getCarnetIdentidad(),
        'complemento' =>  $objOldValues->getComplemento(),
        'genero' =>  $objOldValues->getGeneroTipo()->getId(),
        'oficialia' =>  $objOldValues->getOficialia(),
        'libro' =>  $objOldValues->getLibro(),
        'partida' =>  $objOldValues->getPartida(),
        'folio' =>  $objOldValues->getFolio(),
        'nacionalidad' =>  $objOldValues->getEsDobleNacionalidad(),
        'fechaNacimiento' =>  $objOldValues->getFechaNacimiento()
      );
      $resp = $defaultController->setLogTransaccion(
          $objOldValues->getId(),
          'Estudiante',
          'U',
          json_encode(array('browser' => $_SERVER['HTTP_USER_AGENT'],'ip'=>$_SERVER['REMOTE_ADDR'])),
          $this->session->get('userId'),
          '',
          json_encode($arrNewValues),
          json_encode($arrValOld),
          'SIGED',
         json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => $theFunction ))
      );
      // get the student with observatino info
      $objStudentObs =  $em->getRepository('SieAppWebBundle:ValidacionProceso')->getNacFechaAndGeneroInfo($objOldValues->getCodigoRude(), $this->session->get('yearQA'));
      //opdate observation
      foreach ($objStudentObs as $key => $value) {
          //get observation values  to update
          $objValidationProcessUpdate = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($value->getId());
          $objValidationProcessUpdate->setEsActivo('t');
          $em->persist($objValidationProcessUpdate);
          $em->flush();

      }

      return true;
    }
    /**
     * Obtenemos departamento apoderado
     *
     * @param type $pais
     *
     * @return array departamento
     */
    public function departamentosAction($pais) {
        $em = $this->getDoctrine()->getManager();
        if ($pais == 1) {
            $condition = array('lugarNivel' => 1, 'paisTipoId' => $pais);
        } else {
            $condition = array('lugarNivel' => 8, 'id' => '79355');
        }


        $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy($condition);
        $departamento = array();
        foreach ($dep as $d) {
            $departamento[$d->getId()] = $d->getLugar();
        }

        $dto = $departamento;
        $response = new JsonResponse();
        return $response->setData(array('departamento' => $dto));
    }

    /**
     * Obtenemos provincias apoderado
     *
     * @param type $departamento
     *
     * @return array provincias
     */
    public function provinciasAction($departamento) {
        $em = $this->getDoctrine()->getManager();
        $prov = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $departamento));
        $provincia = array();
        foreach ($prov as $p) {
            $provincia[$p->getid()] = $p->getlugar();
        }
        $response = new JsonResponse();
        return $response->setData(array('provincia' => $provincia));
    }

}
