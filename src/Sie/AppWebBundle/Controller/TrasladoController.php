<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Usuario;
use Sie\AppWebBundle\Form\UsuarioType;
use Sie\AppWebBundle\Entity\Estudiante;
use \Sie\AppWebBundle\Entity\UsuarioRol;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use \Sie\AppWebBundle\Entity\EstudianteInscripcion;

/**
 * Usuario controller.
 *
 */
class TrasladoController extends Controller {

    private $session;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * form to find the stutdent's users
     *
     */
    public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // data es un array con claves 'name', 'email', y 'message'
        return $this->render('SieAppWebBundle:Traslado:index.html.twig', array(
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
        $agestion = array('2015' => '2015');
        $form = $this->createFormBuilder($estudiante)
                ->setAction($this->generateUrl('traslado_web_findResult'))
                ->add('codigoRude', 'text', array('required' => true, 'invalid_message' => 'Campo 1 obligatorio', 'attr' => array('maxlength' => 17)))
                //->add('gestion', 'choice', array("mapped" => false, 'choices' => $agestion, 'required' => true))
                ->add('gestion', 'hidden', array("mapped" => false, 'data' => '2015', 'required' => true))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /**
     * Lists all Usuario entities.
     *
     */
    public function findResultAction(Request $request) {

        $form = $request->get('form');
        $session = new Session();
        //$codigoRude = ($form) ? $form['codigoRude'] : $request->get('codigoRude');
        if ($form) {
            $codigoRude = $form['codigoRude'];
            $request->getSession()->set('codigoRude', $codigoRude);
        } else {
            $codigoRude = $request->getSession()->get('codigoRude');
        }
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('SieAppWebBundle:Estudiante')->findOneByCodigoRude($codigoRude);
        //verificamos si existe el estudiante y obtenemos informaicno del estudiante;
        if ($entities) {

            $inscriptionOfStudent = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
                'estudiante' => $entities->getId(),
                'gestionTipo' => $this->session->get('currentyear'),
                'estadomatriculaTipo' => '4'
            ));


            //obtenemos informacion de inscription (tipo record) sobre el estudiante
            $dataInscriptions = $this->getInscriptionsStudent($entities->getId());
            //obtenemos lugar tipo para la info de tuicion
            $lugarTipoEntity = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('usuario' => $session->get('userId'), 'rolTipo' => $session->get('userId')));
            //echo $lugarTipoEntity->getLugarTipo()->getId();
            //obtenemos curso actual para realizar el traslado
            $currentInscription = $this->getCurrentInscriptionsStudent($codigoRude);

            if (!$currentInscription) {
                $session->getFlashBag()->add('noticemove', 'El estudiante no cuenta con inscripción Efectiva para la gestion actual');
                return $this->render('SieAppWebBundle:Traslado:searchuser.html.twig', array('form' => $this->createSearchForm()->createView()));
            }
            //get the last UE
            $oLastUE = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
                'estudiante' => $entities->getId(),
                'gestionTipo' => $this->session->get('currentyear')
            ));
            $lastue = $oLastUE->getInstitucioneducativa()->getId();
            $forminscription = $this->createFormInscription($currentInscription[0], $codigoRude, $entities->getId(), $lastue);
        } else {
            $session->getFlashBag()->add('noticemove', 'El Rude es invalido o Estudiante no Existe');
            return $this->render('SieAppWebBundle:Traslado:searchuser.html.twig', array('form' => $this->createSearchForm()->createView()));
        }

        return $this->render('SieAppWebBundle:Traslado:findResult.html.twig', array(
                    'datastudent' => $entities, 'datainscriptions' => $dataInscriptions, 'formInscription' => $forminscription->createView()
        ));
    }

    private function createFormInscription($currentInscription, $rude, $idStudent, $lastue) {

        return $this->createFormBuilder($currentInscription)
                        ->setAction($this->generateUrl('traslado_web_registreInscrip'))
                        ->add('institucionEducativa', 'text', array('mapped' => false, 'label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control', 'pattern' => '[0-9]{7,8}')))
                        ->add('institucionEducativaName', 'text', array('mapped' => false, 'label' => 'Institucion Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('nivel', 'text', array('mapped' => false, 'data' => $currentInscription['nivel'], 'disabled' => true, 'required' => false, 'attr' => array('class' => 'form-control')))
                        ->add('grado', 'text', array('mapped' => false, 'data' => $currentInscription['grado'], 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('nivelId', 'hidden', array('mapped' => false, 'data' => $currentInscription['nivelId']))
                        ->add('gradoId', 'hidden', array('mapped' => false, 'data' => $currentInscription['gradoId']))
                        ->add('rude', 'hidden', array('mapped' => false, 'data' => $rude))
                        ->add('idStudent', 'hidden', array('mapped' => false, 'data' => $idStudent))
                        ->add('lastue', 'hidden', array('data' => $lastue))
                        ->add('paralelo', 'choice', array('attr' => array('required' => true, 'class' => 'form-control')))
                        ->add('turno', 'choice', array('attr' => array('required' => true, 'class' => 'form-control')))
                        ->add('save', 'submit', array('label' => 'Guardar'))
                        ->getForm();
    }

    /**
     * get the stutdents inscription - the record
     * @param type $id
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    private function getInscriptionsStudent($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(ei.nivelTipo) as nivelId', 'IDENTITY(ei.gestionTipo) as gestion', 'IDENTITY(ei.gradoTipo) as gradoId', 'IDENTITY(ei.turnoTipo) as turnoId', 'ei.fechaInscripcion', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(ei.paraleloTipo) as paraleloId', 'i.id as sie', 'i.institucioneducativa')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id=ei.estudiante')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'ei.institucioneducativa=i.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'ei.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'ei.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'ei.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'ei.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo=em.id')
                ->where('e.id = :id')
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
     * get the stutdents inscription - the record
     * @param type $id
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    private function getCurrentInscriptionsStudent($id) {
        //$session = new Session();
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(ei.nivelTipo) as nivelId', 'IDENTITY(ei.gestionTipo) as gestion', 'IDENTITY(ei.gradoTipo) as gradoId', 'IDENTITY(ei.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(ei.paraleloTipo) as paraleloId', 'i.id as sie', 'i.institucioneducativa')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id=ei.estudiante')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'ei.institucioneducativa=i.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'ei.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'ei.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'ei.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'ei.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo=em.id')
                ->where('e.codigoRude = :id')
                ->andWhere('ei.gestionTipo = :gestion')
                ->andWhere('ei.estadomatriculaTipo = :mat ')
                ->setParameter('id', $id)
                ->setParameter('gestion', $this->session->get('currentyear'))
                ->setParameter('mat', 4)
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
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
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
        $paralelo = array();
        $turno = array();
        if ($institucion) {
            $nombreIE = ($institucion) ? $institucion->getInstitucioneducativa() : "No existe Unidad Educativa";
            //get the tuicion
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $id);
            $query->bindValue(':roluser', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

            if ($aTuicion[0]['get_ue_tuicion']) {
                if ($lastue != $id) {
                    $infoTraslado = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array(
                        'institucioneducativa' => $id,
                        'nivelTipo' => $nivel,
                        'gradoTipo' => $grado,
                        'gestionTipo' => $this->session->get('currentyear')
                    ));
                    foreach ($infoTraslado as $info) {
                        $paralelo[$info->getParaleloTipo()->getId()] = $info->getParaleloTipo()->getParalelo();
                        $turno[$info->getTurnoTipo()->getId()] = $info->getTurnoTipo()->getTurno();
                    }
                } else {
                    $nombreIE = 'No se puede realizar el traslado porque ya tiene una inscripcion en esa unidad educativa';
                }
            } else {
                $nombreIE = 'No tiene Tuición sobre la Unidad Educativa';
            }
        } else {
            $nombreIE = "No existe Unidad Educativa";
        }


        $response = new JsonResponse();

        return $response->setData(array('nombre' => $nombreIE, 'paralelo' => $paralelo, 'turno' => $turno));
    }

    /**
     * todo the registration of traslado
     * @param Request $request
     * 
     */
    public function registreInscripAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        //get the variblees
        $form = $request->get('form');

        //print_r($request->get('form'));
        //Array ( [institucionEducativa] => 80480200 [paralelo] => 1 [turno] => 8 [save] => [nivelId] => 13 [gradoId] => 5 
        //[rude] => 4073003320074019 [idStudent] => 123627 [_token] => OF55YYMRBysUBsEUK_Op9az4wkkA9nnABC1AGDdwCKY )
        try {
            //update the inscription with matriculaFinID like 9
            $currentInscrip = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
                'estudiante' => $form['idStudent'],
                'gestionTipo' => $this->session->get('currentyear')
            ));
            $currentInscrip->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(9));
            $em->persist($currentInscrip);
            $em->flush();
            //insert a new record with the new selected variables and put matriculaFinID like 5
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($form['gradoId']));
            $studentInscription->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->find($form['paralelo']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find('2015'));
            $studentInscription->setCicloTipo($currentInscrip->getCicloTipo());
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setNivelTipo($currentInscrip->getNivelTipo());
            $studentInscription->setPeriodoTipo($currentInscrip->getPeriodoTipo());
            $studentInscription->setEstudiante($currentInscrip->getEstudiante());
            $studentInscription->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->find($form['turno']));
            $studentInscription->setSucursalTipo($currentInscrip->getSucursalTipo());
            $studentInscription->setCodUeProcedenciaId(0);
            $studentInscription->setObservacion('');
            $studentInscription->setObservacionId(0);
            $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
            $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $em->persist($studentInscription);
            $em->flush();
            $this->session->getFlashBag()->add('goodmove', 'El traslado fue registrado sin problemas');
            return $this->redirect($this->generateUrl('traslado_web'));
        } catch (Exception $ex) {
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }

}
