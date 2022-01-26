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
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Estudiante controller.
 *
 */
class InscriptionOmitidosController extends Controller {

    private $session;
    public $lugarNac;
    public $dpto;
    public $aCursos;
    public $lastUE;

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
        return $this->render('SieAppWebBundle:InscriptionOmitidos:index.html.twig', array(
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
                ->add('codigoRude', 'text', array('mapped' => false, 'label' => 'Rude', 'required' => true, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control', 'maxlength' => 17, 'pattern' => '[A-Z0-9]{13,17}')))
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

            //verificar si es bachiller
            $lookforbachiller = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
                'nivelTipo' => 13,
                'gradoTipo' => 6,
                'estudiante' => $student->getId()
            ));
            if ($lookforbachiller) {
                $this->session->getFlashBag()->add('notiomi', 'El estudiante ya es bachiller... ');
                return $this->redirectToRoute('inscription_omitidos_index');
            }

            $studentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
                'estudiante' => $student->getId(),
                'gestionTipo' => $this->session->get('currentyear')
                , 'estadomatriculaTipo' => '4'
            ));
            //verificamos si tiene inscripcion en la gestion actual
            if (!$studentInscription) {
                //get the last UE
                //get los historico de cursos
                $currentInscription = $this->getCurrentInscriptionsStudent($student->getCodigoRude());
                //obtenemos datos de la gestion pasada para determinar el curso al que le toca
                $inscriptionOld = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
                    'estudiante' => $student->getId(),
                    'gestionTipo' => ($this->session->get('currentyear') - 1 ),
                ));
                //verificar si tiene inscripcion gestion pasada
                if ($inscriptionOld) {
                    //todo buscar el grado curso al que le corresponde de acuerdo a su estado matricual
                    $posicionCurso = $this->getCourse($inscriptionOld->getNivelTipo()->getId(), $inscriptionOld->getCicloTipo()->getId(), $inscriptionOld->getGradoTipo()->getId(), $inscriptionOld->getEstadoMatriculaTipo()->getId());

                    //get last Unidad educativa
                    $objLastUe = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
                        'gestionTipo' => $this->session->get('currentyear'),
                        'estudiante' => $student->getId()
                    ));
                    $lastue = ($objLastUe) ? $objLastUe->getInstitucioneducativa()->getId() : 'krlos';

                    $formOmitidos = $this->createFormOmitidos($this->aCursos[$posicionCurso], $student->getId(), $lastue);
                } else {
                    $this->session->getFlashBag()->add('notiomi', 'Estudiante cuenta con inscripción para la gestión ' . ($this->session->get('currentyear') - 1));
                    return $this->redirectToRoute('inscription_omitidos_index');
                }
            } else {
                $this->session->getFlashBag()->add('notiomi', 'Estudiante ya cuenta con inscripción para la gestión ' . $this->session->get('currentyear'));
                return $this->redirectToRoute('inscription_omitidos_index');
            }
        } else {
            $this->session->getFlashBag()->add('notiomi', 'Estudiante no registrado');
            return $this->redirectToRoute('inscription_omitidos_index');
        }
        //everything is ok build the info
        return $this->render('SieAppWebBundle:InscriptionOmitidos:result.html.twig', array(
                    'datastudent' => $student,
                    'currentInscription' => $currentInscription,
                    'formOmitidos' => $formOmitidos->createView()
        ));
    }

    /**
     * buil the Omitidos form 
     * @param type $aInscrip
     * @return type form
     */
    private function createFormOmitidos($aInscrip, $idStudent, $lastue) {

        $em = $this->getDoctrine()->getManager();
        $aInscrip = explode('-', $aInscrip);
        $nivel = $em->getRepository('SieAppWebBundle:NivelTIpo')->find($aInscrip[0]);
        $grado = $em->getRepository('SieAppWebBundle:GradoTipo')->find($aInscrip[2]);


        return $formOmitido = $this->createFormBuilder()
                ->setAction($this->generateUrl('inscription_omitidos_saveOmitidos'))
                ->add('institucionEducativa', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                ->add('institucionEducativaName', 'text', array('label' => 'Unidad Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('nivel', 'text', array('data' => $nivel->getNivel(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('grado', 'text', array('data' => $grado->getGrado(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('nivelId', 'hidden', array('mapped' => false, 'data' => $aInscrip[0]))
                ->add('gradoId', 'hidden', array('mapped' => false, 'data' => $aInscrip[2]))
                ->add('cicloId', 'hidden', array('mapped' => false, 'data' => $aInscrip[1]))
                ->add('lastue', 'hidden', array('mapped' => false, 'data' => $lastue))
                ->add('idStudent', 'hidden', array('mapped' => false, 'data' => $idStudent))
                ->add('paralelo', 'choice', array('attr' => array('required' => true, 'class' => 'form-control')))
                ->add('turno', 'choice', array('attr' => array('requirede' => true, 'class' => 'form-control')))
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
    private function getCurrentInscriptionsStudent($id) {
        //$session = new Session();
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(ei.nivelTipo) as nivelId', 'IDENTITY(ei.gestionTipo) as gestion', 'IDENTITY(ei.gradoTipo) as gradoId', 'IDENTITY(ei.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(ei.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'ei.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'ei.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'ei.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'ei.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'ei.turnoTipo = t.id')
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
     * todo the registration of traslado
     * @param Request $request
     * 
     */
    public function saveOmitidosAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        //get the variblees
        $form = $request->get('form');
//        print_r($form);
//        die;
        try {

            //get info old about inscription
            $currentInscrip = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $form['idStudent']));
            //insert a new record with the new selected variables and put matriculaFinID like 5
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($form['gradoId']));
            $studentInscription->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->find($form['paralelo']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find('2015'));
            $studentInscription->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->find($form['cicloId']));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setNivelTipo($currentInscrip->getNivelTipo());
            $studentInscription->setPeriodoTipo($currentInscrip->getPeriodoTipo());
            $studentInscription->setEstudiante($currentInscrip->getEstudiante());
            $studentInscription->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->find($form['turno']));
            $studentInscription->setSucursalTipo($currentInscrip->getSucursalTipo());
            $studentInscription->setCodUeProcedenciaId($form['institucionEducativa']);
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
            $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $em->persist($studentInscription);
            $em->flush();
            //$this->session->getFlashBag()->add('goodomi', 'Se realizo la inscripción del Estudiante satisfactoriamente ');
            $this->session->getFlashBag()->add('goodomi', 'Estudiante Omitido fue inscrito...');
            return $this->redirect($this->generateUrl('inscription_omitidos_index'));
        } catch (Exception $ex) {
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

        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
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
            } else {
                $nombreIE = 'Usuario No tiene Tuición sobre la Unidad Educativa';
            }
        } else {
            $nombreIE = 'No existe Unidad Educativa';
        }
        $response = new JsonResponse();

        return $response->setData(array('nombre' => $nombreIE, 'paralelo' => $paralelo, 'turno' => $turno));
    }

}
