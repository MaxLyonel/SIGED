<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\PaisTipo;
use Sie\AppWebBundle\Form\EstudianteType;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;

/**
 * Estudiante controller.
 *
 */
class EstudianteUpdateController extends Controller {

    private $session;
    public $lugarNac;
    public $dpto;

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
        $form = $this->createFormBuilder($estudiante)
                ->setAction($this->generateUrl('sie_estudiantes_result'))
                ->add('codigoRude', 'text', array('required' => false, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9a-zA-Z\sñÑ]{14,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
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

        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));
        //verificamos si existe el estudiante
        if ($student) {

            //get the values to the build the forms
            $m1 = isset($form['mode1']) ? $form['mode1'] : 0;
            $m2 = isset($form['mode2']) ? $form['mode2'] : 0;
            $m3 = isset($form['mode3']) ? $form['mode3'] : 0;

            $studentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
                'estudiante' => $student->getId(),
                'gestionTipo' => $this->session->get('currentyear'),
                'estadomatriculaTipo' => '4'
            ));
            //verificamos si tiene inscripcion en la gestion 2015
            if ($studentInscription) {

                $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
                $query->bindValue(':user_id', $this->session->get('userId'));
                $query->bindValue(':sie', $studentInscription->getInstitucioneducativa()->getId());
                $query->bindValue(':roluser', $this->session->get('roluser'));
                $query->execute();
                $aTuicion = $query->fetchAll();
                //check the tuicion
                if (!$aTuicion[0]['get_ue_tuicion']) {
                    $this->session->getFlashBag()->add('noticemodi', 'Usuario no tiene tución sobre la Unidad Eductiva');
                    return $this->redirectToRoute('sie_estudiantes');
                }

                $infoInscription = $this->getCurrentInscriptionsStudent($student->getCodigoRude());
                $form_mode1 = (isset($form['mode1'])) ? $this->createFormMode1($student, $m3)->createView() : '';
                $form_mode2 = (isset($form['mode2'])) ? $this->createFormMode2($student, $m3)->createView() : '';
                $form_mode3 = (isset($form['mode3'])) ? $this->createFormMode3($student)->createView() : '';
                //print_r($infoInscription);
            } else {
                $this->session->getFlashBag()->add('noticemodi', 'Estudiante no cuenta con inscripción para la presente gestión');
                return $this->redirectToRoute('sie_estudiantes');
            }
        } else {
            $this->session->getFlashBag()->add('noticemodi', 'Estudiante no existe');
            return $this->redirectToRoute('sie_estudiantes');
        }

        return $this->render($this->session->get('pathSystem') . ':Estudiante:result.html.twig', array(
                    'student' => $student,
                    'infoinscription' => $infoInscription,
                    'form_mode1' => $form_mode1,
                    'form_mode2' => $form_mode2,
                    'form_mode3' => $form_mode3,
                    'm1' => $m1,
                    'm2' => $m2,
                    'm3' => $m3
        ));
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
                ->select('e.codigoRude', 'n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(ei.nivelTipo) as nivelId', 'IDENTITY(ei.gestionTipo) as gestion', 'IDENTITY(ei.gradoTipo) as gradoId', 'IDENTITY(ei.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(ei.paraleloTipo) as paraleloId', 'i.id as sie', 'i.institucioneducativa')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id=ei.estudiante')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'ei.institucioneducativa=i.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'ei.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'ei.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'ei.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'ei.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo=em.id')
                ->where('e.codigoRude = :id')
                ->andWhere('ei.gestionTipo = :gestion')
                ->setParameter('id', $id)
                ->setParameter('gestion', $this->session->get('currentyear'))
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    /**
     * create the students mode 1 form 
     * @param type $student
     * @return form mode 1
     */
    private function createFormMode1($student, $m3) {

        $em = $this->getDoctrine()->getManager();
        //echo $student->getLugarNacTipo()->getId();        die;
        if (!$m3) {

            $form = $this->createFormBuilder($student)
                    ->setAction($this->generateUrl('sie_estudiantes_updatestudentA', array('id' => $student->getId())))
                    ->add('codigoRude', 'hidden', array('label' => 'codigo rude'))
                    ->add('paterno', 'text', array('label' => 'Paterno', 'required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-z]{3,30}')))
                    ->add('materno', 'text', array('label' => 'Materno', 'required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-z]{3,30}')))
                    ->add('nombre', 'text', array('label' => 'Nombre', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-z]{3,40}')))
                    ->add('fechaNacimiento', 'text', array('data' => $student->getFechaNacimiento()->format('d-m-Y'), 'label' => 'Fecha Nacimiento', 'attr' => array('readonly' => 'readonly', 'class' => 'form-control')))
                    ->add('save', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-blue')))
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

            $dataProvincia = ($student->getLugarProvNacTipo()) ? $student->getLugarProvNacTipo()->getId() : 1;

            $form = $this->createFormBuilder($student)
                    ->setAction($this->generateUrl('sie_estudiantes_updatestudentD', array('id' => $student->getId())))
                    ->add('codigoRude', 'hidden', array('label' => 'codigo rude'))
                    ->add('paterno', 'text', array('label' => 'Paterno', 'required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-z]{3,30}')))
                    ->add('materno', 'text', array('label' => 'Materno', 'required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-z]{3,30}')))
                    ->add('nombre', 'text', array('label' => 'Nombre', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-z]{3,40}')))
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
                    return $e->createQueryBuilder('lt')
                            ->where('lt.lugarNivel = :id')
                            ->setParameter('id', '1')
                            ->orderBy('lt.codigo', 'ASC');
                }, 'property' => 'lugar',
                        'data' => $em->getReference("SieAppWebBundle:LugarTipo", $student->getLugarNacTipo()->getId())
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
                    ->add('localidad', 'text', array('data' => $student->getLocalidadNac(), 'required' => false, 'mapped' => false, 'label' => 'Localidad', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control')))
                    ->add('ci', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getCarnetIdentidad(), 'label' => 'CI', 'attr' => array('class' => 'form-control')))
                    ->add('complemento', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getComplemento(), 'label' => 'Complemento', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que la expedicion de su CI, por favor no coloque abreviaturas de departamentos")))
                    //->add('generoTipo', 'choice', array('required' => false, 'mapped' => false, 'choices' => $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll(), 'label' => 'Género', 'attr' => array('class' => 'form-control')))
                    ->add('generoTipo', 'entity', array('label' => 'Género', 'attr' => array('class' => 'form-control'),
                        'mapped' => false, 'class' => 'SieAppWebBundle:GeneroTipo',
                        'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $student->getGeneroTipo()->getId())
                    ))
                    ->add('oficialia', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getOficialia(), 'label' => 'Oficialia', 'attr' => array('class' => 'form-control')))
                    ->add('libro', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getLibro(), 'label' => 'Libro', 'attr' => array('class' => 'form-control')))
                    ->add('partida', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getPartida(), 'label' => 'Partida', 'attr' => array('class' => 'form-control')))
                    ->add('folio', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getFolio(), 'label' => 'Folio', 'attr' => array('class' => 'form-control')))
                    ->add('save', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-blue')))
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
        if (!$m3) {
            $form = $this->createFormBuilder($student)
                    ->setAction($this->generateUrl('sie_estudiantes_updatestudentB', array('id' => $student->getId())))
                    ->add('codigoRude', 'hidden', array('label' => 'codigo rude'))
                    ->add('paterno', 'text', array('label' => 'Paterno', 'required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-z]{3,30}')))
                    ->add('materno', 'text', array('label' => 'Materno', 'required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-z]{3,30}')))
                    ->add('nombre', 'text', array('label' => 'Nombre', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-z]{3,40}')))
                    ->add('fechaNacimiento', 'text', array('data' => $student->getFechaNacimiento()->format('d-m-Y'), 'label' => 'Fecha Nacimiento', 'attr' => array('class' => 'form-control')))
                    ->add('resolAprobatoria', 'textarea', array('required' => false, 'data' => $student->getResolucionaprovatoria(), 'mapped' => false, 'attr' => array('class' => 'form-control', 'rows' => '2', 'cols' => '3', 'maxlength' => '15')))
                    ->add('obsAdicional', 'textarea', array('required' => false, 'data' => $student->getObservacionadicional(), 'mapped' => false, 'attr' => array('class' => 'form-control', 'maxlength' => '50')))
                    ->add('save', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-blue')))
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

            $form = $this->createFormBuilder($student)
                    ->setAction($this->generateUrl('sie_estudiantes_updatestudentE', array('id' => $student->getId())))
                    ->add('codigoRude', 'hidden', array('label' => 'codigo rude'))
                    ->add('paterno', 'text', array('label' => 'Paterno', 'required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-z]{3,30}')))
                    ->add('materno', 'text', array('label' => 'Materno', 'required' => false, 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-z]{3,30}')))
                    ->add('nombre', 'text', array('label' => 'Nombre', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'pattern' => '[a-z A-z]{3,40}')))
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
                    return $e->createQueryBuilder('lt')
                            ->where('lt.lugarNivel = :id')
                            ->setParameter('id', '1')
                            ->orderBy('lt.codigo', 'ASC');
                }, 'property' => 'lugar',
                        'data' => $em->getReference("SieAppWebBundle:LugarTipo", $student->getLugarNacTipo()->getId())
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
                    ->add('localidad', 'text', array('data' => $student->getLocalidadNac(), 'required' => false, 'mapped' => false, 'label' => 'Localidad', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control')))
                    ->add('ci', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getCarnetIdentidad(), 'label' => 'CI', 'attr' => array('class' => 'form-control')))
                    ->add('complemento', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getComplemento(), 'label' => 'Complemento', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que la expedicion de su CI, por favor NO coloque abreviaturas de departamentos")))
                    //->add('generoTipo', 'choice', array('required' => false, 'mapped' => false, 'choices' => $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll(), 'label' => 'Género', 'attr' => array('class' => 'form-control')))
                    ->add('generoTipo', 'entity', array('label' => 'Género', 'attr' => array('class' => 'form-control'),
                        'mapped' => false, 'class' => 'SieAppWebBundle:GeneroTipo',
                        'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $student->getGeneroTipo()->getId())
                    ))
                    ->add('oficialia', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getOficialia(), 'label' => 'Oficialia', 'attr' => array('class' => 'form-control')))
                    ->add('libro', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getLibro(), 'label' => 'Libro', 'attr' => array('class' => 'form-control')))
                    ->add('partida', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getPartida(), 'label' => 'Partida', 'attr' => array('class' => 'form-control')))
                    ->add('folio', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getFolio(), 'label' => 'Folio', 'attr' => array('class' => 'form-control')))
                    ->add('save', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-blue')))
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

        $dataProvincia = ($student->getLugarProvNacTipo()) ? $student->getLugarProvNacTipo()->getId() : 90;
        $datadepartaemnto = $student->getLugarNacTipo() ? $student->getLugarNacTipo()->getId() : 12;

        return $this->createFormBuilder($estudiante)
                        ->setAction($this->generateUrl('sie_estudiantes_updatestudentC', array('id' => $student->getId())))
                        //->setAction($this->generateUrl('sie_estudiantes_result'))
                        //->add('codigoRude', 'hidden', array('label' => 'codigo rude'))
                        //->add('pais', 'choice', array('mapped' => false, 'label' => 'Pais', 'attr' => array('class' => 'form-control')))
                        //->add('pais', 'choice', array('mapped' => false, 'choices' => $em->getRepository('SieAppWebBundle:PaisTipo')->findAll(), 'label' => 'Pais', 'attr' => array('class' => 'form-control')))
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
                        return $e->createQueryBuilder('lt')
                                ->where('lt.lugarNivel = :id')
                                ->setParameter('id', '1')
                                ->orderBy('lt.codigo', 'ASC');
                    }, 'property' => 'lugar',
                            'data' => $em->getReference("SieAppWebBundle:LugarTipo", $datadepartaemnto)
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
                        ->add('localidad', 'text', array('data' => $student->getLocalidadNac(), 'required' => false, 'mapped' => false, 'label' => 'Localidad', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control')))
                        ->add('ci', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getCarnetIdentidad(), 'label' => 'CI', 'attr' => array('class' => 'form-control')))
                        ->add('complemento', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getComplemento(), 'label' => 'Complemento', 'attr' => array('style' => 'text-transform:uppercase', 'class' => 'form-control', 'data-toggle' => "tooltip", 'data-placement' => "right", 'data-original-title' => "Complemento no es lo mismo que expedición, por favor NO colocar abreviaturas de Departamentos")))
                        //->add('generoTipo', 'choice', array('required' => false, 'mapped' => false, 'choices' => $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll(), 'label' => 'Género', 'attr' => array('class' => 'form-control')))
                        ->add('generoTipo', 'entity', array('label' => 'Género', 'attr' => array('class' => 'form-control'),
                            'mapped' => false, 'class' => 'SieAppWebBundle:GeneroTipo',
                            'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $student->getGeneroTipo()->getId())
                        ))
                        ->add('oficialia', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getOficialia(), 'label' => 'Oficialia', 'attr' => array('class' => 'form-control')))
                        ->add('libro', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getLibro(), 'label' => 'Libro', 'attr' => array('class' => 'form-control')))
                        ->add('partida', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getPartida(), 'label' => 'Partida', 'attr' => array('class' => 'form-control')))
                        ->add('folio', 'text', array('required' => false, 'mapped' => false, 'data' => $student->getFolio(), 'label' => 'Folio', 'attr' => array('class' => 'form-control')))
                        ->add('save', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-blue')))
                        ->getForm();
    }

    /**
     * update an existing Estudiante entity.
     *
     */
    public function updateStudentAAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $entity = $em->getRepository('SieAppWebBundle:Estudiante')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Estudiante entity.');
        }
        $entity->setPaterno($form['paterno']);
        $entity->setMaterno($form['materno']);
        $entity->setNombre($form['nombre']);
        $entity->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
        $em->flush();
        $this->session->getFlashBag()->add('okUpdate', 'Datos Modificados Correctamente');
        return $this->redirect($this->generateUrl('sie_estudiantes'));
    }

    /**
     * update an existing Estudiante entity.
     *
     */
    public function updateStudentBAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        $entity = $em->getRepository('SieAppWebBundle:Estudiante')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Estudiante entity.');
        }
        $entity->setPaterno($form['paterno']);
        $entity->setMaterno($form['materno']);
        $entity->setNombre($form['nombre']);
        $entity->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
        $entity->setResolucionaprovatoria($form['resolAprobatoria']);
        $entity->setObservacionadicional($form['obsAdicional']);
        $em->persist($entity);
        $em->flush();
        $this->session->getFlashBag()->add('okUpdate', 'Datos Modificados Correctamente');
        return $this->redirect($this->generateUrl('sie_estudiantes'));
    }

    /**
     * update an existing Estudiante entity.
     *
     */
    public function updateStudentCAction(Request $request, $id) {
        //die('krlos');
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');



        $entity = $em->getRepository('SieAppWebBundle:Estudiante')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Estudiante entity.');
        }
        //Array ( [pais] => 0 [departamento] => 1 [provincia] => 23 [localidad] => localidad [ci] => 9977761 [complemento] => N [generoTipo] => 0 [oficialia] => ofi [libro] => libro [partida] => partida [folio] => folio [save] => [_token] => OF55YYMRBysUBsEUK_Op9az4wkkA9nnABC1AGDdwCKY )
//        $entity->setPaterno($form['paterno']);
//        $entity->setMaterno($form['materno']);
//        $entity->setNombre($form['nombre']);
//        $entity->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
//        $entity->setFechaModificacion(new \DateTime(date('Y-m-d')));
        //save about the place
        $entity->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($form['pais']));
        ($form['pais'] == 1) ? $entity->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento'])) : $entity->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(1));
        ($form['pais'] == 1) ? $entity->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia'])) : $entity->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(11));
        ($form['pais'] == 1) ? $entity->setLocalidadNac(strtoupper($form['localidad'])) : $entity->setLocalidadNac(strtoupper(''));
        //save about the certificadoNac
        $entity->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['generoTipo']));
        $entity->setCarnetIdentidad($form['ci']);
        $entity->setComplemento($form['complemento']);
        $entity->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['generoTipo']));
        $entity->setOficialia($form['oficialia']);
        $entity->setLibro($form['libro']);
        $entity->setPartida($form['partida']);
        $entity->setFolio($form['folio']);
        //need to add 2 files too
        $em->flush();
        $this->session->getFlashBag()->add('okUpdate', 'Datos Modificados Correctamente');
        return $this->redirect($this->generateUrl('sie_estudiantes'));
    }

    /**
     * administrativo
     * @param Request $request
     * @param type $id
     * @return type
     * @throws type
     * Save administrativo and manera voluntaria CAMBIOS
     */
    public function updateStudentDAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        /*
          print_r($form);
          die; */
        $entity = $em->getRepository('SieAppWebBundle:Estudiante')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Estudiante entity.');
        }
        //Array ( [pais] => 0 [departamento] => 1 [provincia] => 23 [localidad] => localidad [ci] => 9977761 [complemento] => N [generoTipo] => 0 [oficialia] => ofi 
        //[libro] => libro [partida] => partida [folio] => folio [save] => [_token] => OF55YYMRBysUBsEUK_Op9az4wkkA9nnABC1AGDdwCKY )
        $entity->setPaterno(strtoupper($form['paterno']));
        $entity->setMaterno(strtoupper($form['materno']));
        $entity->setNombre(strtoupper($form['nombre']));
        $entity->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
        $entity->setFechaModificacion(new \DateTime(date('Y-m-d')));
        //save about the place
        $entity->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($form['pais']));
        ($form['pais'] == 1) ? $entity->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento'])) : $entity->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(1));
        ($form['pais'] == 1) ? $entity->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia'])) : $entity->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(11));
        ($form['pais'] == 1) ? $entity->setLocalidadNac(strtoupper($form['localidad'])) : $entity->setLocalidadNac(strtoupper(''));
        //save about the certificadoNac
        $entity->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['generoTipo']));
        $entity->setCarnetIdentidad($form['ci']);
        $entity->setComplemento($form['complemento']);
        $entity->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['generoTipo']));
        $entity->setOficialia($form['oficialia']);
        $entity->setLibro($form['libro']);
        $entity->setPartida($form['partida']);
        $entity->setFolio($form['folio']);
        //need to add 2 files too
        $em->flush();
        $this->session->getFlashBag()->add('okUpdate', 'Datos Modificados Correctamente');
        return $this->redirect($this->generateUrl('sie_estudiantes'));
    }

    /**
     * save form 1 and form 3
     * @param Request $request
     * @param type $id
     * @return type
     * @throws type
     */
    public function updateStudentEAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');


        $entity = $em->getRepository('SieAppWebBundle:Estudiante')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Estudiante entity.');
        }
        //Array ( [pais] => 0 [departamento] => 1 [provincia] => 23 [localidad] => localidad [ci] => 9977761 [complemento] => N [generoTipo] => 0 [oficialia] => ofi [libro] => libro [partida] => partida [folio] => folio [save] => [_token] => OF55YYMRBysUBsEUK_Op9az4wkkA9nnABC1AGDdwCKY )
        $entity->setPaterno($form['paterno']);
        $entity->setMaterno($form['materno']);
        $entity->setNombre($form['nombre']);
        $entity->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
        $entity->setResolucionaprovatoria($form['resolAprobatoria']);
        $entity->setObservacionadicional($form['obsAdicional']);
        //save about the place
        $entity->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($form['pais']));
        ($form['pais'] == 1) ? $entity->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['departamento'])) : $entity->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(1));
        ($form['pais'] == 1) ? $entity->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($form['provincia'])) : $entity->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(11));
        ($form['pais'] == 1) ? $entity->setLocalidadNac(strtoupper($form['localidad'])) : $entity->setLocalidadNac(strtoupper(''));
        //save about the certificadoNac
        $entity->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['generoTipo']));
        $entity->setCarnetIdentidad($form['ci']);
        $entity->setComplemento($form['complemento']);
        $entity->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($form['generoTipo']));
        $entity->setOficialia($form['oficialia']);
        $entity->setLibro($form['libro']);
        $entity->setPartida($form['partida']);
        $entity->setFolio($form['folio']);
        //form 1 y form3 
        //need to save departamento, etc,etc
        //need to add 2 files too
        $em->flush();
        $this->session->getFlashBag()->add('okUpdate', 'Datos Modificados Correctamente');
        return $this->redirect($this->generateUrl('sie_estudiantes'));
    }

}
