<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use \Sie\AppWebBundle\Entity\Usuario;
use \Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class CambioParaleloController extends Controller {

    public $oparalelos;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    public function calidadAction(Request $request) {
        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $request->getSession()->set('idGestion',$request->getSession()->get('currentyear'));

        return $this->redirect($this->generateUrl('herramienta_change_paralelo_sie_index'));
    }

    /**
     * note consultation parents Index
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) {

        //get the session's values
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        // Verificamos si no ha caducado la sesion
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        ////////////////////////////////////////////////////
        $em = $this->getDoctrine()->getManager();

        if ($request->getMethod() == 'POST') {
            $form = $request->get('form');
            $institucion = $form['sie'];
            $gestion = $form['gestion'];
            // creamos variables de sesion de la institucion educativa y gestion
            $request->getSession()->set('idInstitucion', $institucion);
            $request->getSession()->set('idGestion', $gestion);
            
            $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
            if (!$institucioneducativa) {
                $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                return $this->redirect($this->generateUrl('herramienta_change_paralelo_sie_index'));
            }
        } else {
            $institucion = $request->getSession()->get('ie_id');
            $gestion = $request->getSession()->get('idGestion');
        }

        /*
         * obtenemos datos de la unidad educativa
         */
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
        
        return $this->render($this->session->get('pathSystem') . ':CambioParalelo:index.html.twig', array(
                    'form' => $this->craeteformsearch()->createView(),
                    'institucion' => $institucion,
                    'gestion' => $gestion
        ));
    }

    private function craeteformsearch() {

        return $this->createFormBuilder()
                        //->setAction($this->generateUrl('remove_inscription_sie_index'))
                        ->add('rude', 'text', array('label' => 'RUDE', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('search', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'findInscription()')))
                        ->getForm();
    }

    /**
     * find the courser per sie
     * @param Request $request
     * @return type the list of student and inscripion data
     */
    public function resultAction(Request $request) {
        //get the value to send
        $rude = strtoupper($request->get('rude'));
        $gestion = $request->getSession()->get('idGestion');
        //find the id of student
        $em = $this->getDoctrine()->getManager();
        $objStudent = array();
        $oInscription = array();
        $inscriptionForm = array();
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array(
            'codigoRude' => $rude
        ));
        $exist = true;
        //check if the data exist
        if ($objStudent) {
            //look for inscription data
            $oInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getHistoryPerStudent($objStudent->getId(), $gestion);
            if ($oInscription) {
                $oInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getHistoryPerStudentUe($objStudent->getId(), $gestion, $oInscription[0]['sie']);
                //get paralelos
                $this->oparalelos = $this->getParalelosStudent($oInscription[0]['nivelId'], $oInscription[0]['cicloId'], $oInscription[0]['gradoId'], $oInscription[0]['sie']);
                $inscriptionForm = $this->createInscriptionForm($objStudent->getId(), $oInscription[0]['sie'], $oInscription, $rude)->createView();
                //check if exists data
                if (!$oInscription) {
                    $message = 'Estdiante no cuenta con Historial';
                    $this->addFlash('warningrein', $message);
                    $exist = false;
                    $oInscription = array();
                }
                $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($oInscription[0]['sie'], $gestion);
                $notaTipo = $em->getRepository('SieAppWebBundle:NotaTipo')->findOneById($operativo);
                $control_operativo_menus = $em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus')->findOneBy(array('gestionTipoId' => $gestion, 'institucioneducativa' => $oInscription[0]['sie'], 'notaTipo' => $notaTipo));
                $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($oInscription[0]['sie']);
                if($control_operativo_menus) {
                    if($control_operativo_menus->getEstadoMenu() == 1) {
                        return $this->render($this->session->get('pathSystem') . ':CloseModules:plantilla.html.twig', array(
                            'sie' => $institucion->getId(),
                            'gestion' => $gestion,
                            'institucion' => $institucion,
                            'operativo' => $notaTipo
                        ));
                    }
                }
            } else {
                $message = 'No existe información para la presente gestión';
                $this->addFlash('warningrein', $message);
                $exist = false;
                $oInscription = array();
            }
        } else {
            $message = 'Código RUDE no existe';
            $this->addFlash('warningrein', $message);
            $exist = false;
        }

        return $this->render($this->session->get('pathSystem') . ':CambioParalelo:result.html.twig', array(
                    'dataInscription' => $oInscription,
                    'form' => $inscriptionForm,
                    'exist' => $exist
        ));
    }

    /**
     * craete the form to do the change paralelo
     * @param type $idStudent
     * @param type $ue
     * @param type $oInscription
     * @return form with the new info todo  the change
     */
    private function createInscriptionForm($idStudent, $ue, $oInscription, $rude) {

        $em = $this->getDoctrine()->getManager();

        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('herramienta_change_paralelo_sie_change'))
                        ->add('ue', 'text', array('data' => $oInscription[0]['institucioneducativa'] . '-' . $ue, 'disabled' => true, 'label' => 'Unidad Educativa', 'attr' => array('required' => false, 'maxlength' => 8, 'class' => 'form-control')))
                        ->add('ueid', 'hidden', array('data' => $ue))
                        ->add('rude', 'hidden', array('data' => $rude))
                        ->add('nivel', 'text', array('data' => $oInscription[0]['nivel'], 'disabled' => true, 'attr' => array('required' => false, 'class' => 'form-control')))
                        ->add('grado', 'text', array('data' => $oInscription[0]['grado'], 'disabled' => true, 'attr' => array('required' => false, 'class' => 'form-control')))
                        ->add('nivelid', 'hidden', array('data' => $oInscription[0]['nivelId']))
                        ->add('gradoid', 'hidden', array('data' => $oInscription[0]['gradoId']))
                        ->add('cicloid', 'hidden', array('data' => $oInscription[0]['cicloId']))
                        ->add('idStudent', 'hidden', array('required' => false, 'mapped' => false, 'data' => $idStudent))
                        ->add('eiId', 'hidden', array('data' => $oInscription[0]['eiId']))
                        ->add('paraleloOld', 'hidden', array('data' => $oInscription[0]['paraleloId']))
                        ->add('turnoOld', 'hidden', array('data' => $oInscription[0]['turnoId']))
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
     * get the paralelos
     * @param type $nivel
     * @param type $ciclo
     * @param type $grado
     * @param type $ue
     * @return \Sie\RegularBundle\Controller\Exception
     */
    private function getParalelosStudent($nivel, $ciclo, $grado, $ue) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('IDENTITY(iec.paraleloTipo) as paraleloTipo')
                ->where('iec.institucioneducativa = :ue')
                ->andwhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('ue', $ue)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('gestion', $this->session->get('idGestion'))
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
     * change  the paralelo
     * @param Request $request
     * @return modify the old paralelo with the new it
     */
    public function changeAction(Request $request) {

        $form = $request->get('form');

        //die('krlos');
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            //old condition query
            $oldCondition = array(
                'institucioneducativa' => $form['ueid'],
                'nivelTipo' => $form['nivelid'],
                'gradoTipo' => $form['gradoid'],
                'cicloTipo' => $form['cicloid'],
                'paraleloTipo' => $form['paraleloOld'],
                'turnoTipo' => $form['turnoOld'],
                'gestionTipo' => $this->session->get('idGestion')
            );
            //get the old paralelo info
            $objCourseOld = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy($oldCondition);

            //new condition query
            $newCondition = array(
                'institucioneducativa' => $form['ueid'],
                'nivelTipo' => $form['nivelid'],
                'gradoTipo' => $form['gradoid'],
                'cicloTipo' => $form['cicloid'],
                'paraleloTipo' => $form['paralelo'],
                'turnoTipo' => $form['turno'],
                'gestionTipo' => $this->session->get('idGestion')
            );
            //get the new paralelo info
            $objCourse = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy($newCondition);

            $query = $em->getConnection()->prepare('SELECT sp_cambio_paralelo_estudiante(:igestion_tipo_id::INT, :iinstitucioneducativa::INT, :icodigorude::VARCHAR, :inivel_tipo_id::INT, :igrado_tipo::INT, :iturno_tipo::INT, :iturno_tiponuevo::INT, :iparalelo_tipoNuevo::VARCHAR, :iparalelo_tipoAnte::VARCHAR )');

            $query->bindValue(':igestion_tipo_id', intval($this->session->get('idGestion')));
            $query->bindValue(':iinstitucioneducativa', intval($form['ueid']));
            $query->bindValue(':icodigorude', $form['rude']);
            $query->bindValue(':inivel_tipo_id', intval($form['nivelid']));
            $query->bindValue(':igrado_tipo', intval($form['gradoid']));
            $query->bindValue(':iturno_tipo', intval($form['turno']));
            $query->bindValue(':iturno_tiponuevo', intval($form['turno']));
            $query->bindValue(':iparalelo_tipoNuevo', $form['paralelo']);
            $query->bindValue(':iparalelo_tipoAnte', $form['paraleloOld']);

            $query->execute();

            //get old areas
            //$objAreasOld = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getAsignaturasPerCourse
            //check if is possible to do the change
            //get the last inscription to modify
            $objInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['eiId']);
            //to do the update with the new paralelo
            $objInscription->setInstitucioneducativaCurso($objCourse);
            $em->flush();
            // Try and commit the transaction
            $em->getConnection()->commit();
            //get the success message
            $message = "Cambio de paralelo realizado...";
            $this->addFlash('successchangeparalelo', $message);
            //go the index page
            return $this->redirectToRoute('herramienta_change_paralelo_sie_index');
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }

    private function getCourse($sie, $nivel, $grado, $ciclo, $paralelo, $turno, $gestion) {
        $em = $this->getDoctrine()->getManager();
        return 1;
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
                ->setParameter('gestion', $this->session->get('idGestion'))
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
