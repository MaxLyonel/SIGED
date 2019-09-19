<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEliminados;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;

class RemoveInscriptionController extends Controller {

    private $session;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * remove inscription Index
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        //check if the user is logged
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //set the student and inscriptions data
        $student = array();
        $dataInscription = array();
        $sw = false;

        if ($request->get('form')) {
            //get the form to send
            $form = $request->get('form');
            //get the result of search
            $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));
            //verificamos si existe el estudiante y si es menor a 15
            if ($student) {
                $dataInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getInscriptionHistoryEstudenWhitObservation($form['codigoRude']);
            }
            $sw = true;
            //check if the result has some value
            if (!$student) {
                $message = 'Estudiante con rude ' . $form['codigoRude'] . ' no se presenta registro de inscripciones';
                $this->addFlash('notihistory', $message);
                $sw = false;
                return $this->redirectToRoute('remove_inscription_sie_index');
            }
        }

        return $this->render($this->session->get('pathSystem') . ':RemoveInscription:index.html.twig', array(
                    'form' => $this->craeteformsearch()->createView(),
                    'datastudent' => $student,
                    'dataInscription' => $dataInscription,
                    'sw' => $sw,
        ));
    }

    private function craeteformsearch() {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('remove_inscription_sie_index'))
                ->add('codigoRude', 'text', array('label' => 'RUDE', 'attr' => array('class' => 'form-control', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /**
     * get the notas of student
     * @return list of nota the student
     */
    public function notaAction($idstudent, $nivel, $grado, $paralelo, $turno, $gestion, $sie) {

        $em = $this->getDoctrine()->getManager();
        //get the nota data
        $objNota = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getNotasStudent($idstudent, $nivel, $grado, $paralelo, $turno, $gestion, $sie);
        $aNota = array();
        $aBim = array();
        //build the nota
        foreach ($objNota as $nota) {
            ($nota['notaTipo']) ? $aNota[$nota['asignatura']][$nota['notaTipo']] = ($nivel == 11) ? $nota['notaCualitativa'] : $nota['notaCuantitativa'] : '';
            ($nota['notaTipo']) ? $aBim[$nota['notaTipo']] = ($nota['notaTipo'] == 5) ? 'Prom' : $nota['notaTipo'] . '.B' : '';
        }
        return $this->render('SieRegularBundle:RemoveInscription:nota.html.twig', array(
                    'notastudent' => $aNota,
                    'bimestres' => $aBim
        ));
    }

    /**
     * Remove the inscription
     */
    public function removeAction($gestion, $eiid, Request $request) {
        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {

            $objJuegos = $em->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos')->findOneBy(array('estudianteInscripcion' => $eiid));
            if ($objJuegos) {
                $message = "No se puede eliminar por que el estudiante esta registrado en el sistema de Juegos Plurinacionales";
                $this->addFlash('warningremoveins', $message);
                return $this->redirectToRoute('remove_inscription_sie_index');
            }

            //verificamos si esta en la tabla de olim_estudiante_inscripcion
            $objolim_estudiante_inscripcion = $em->getRepository('SieAppWebBundle:OlimEstudianteInscripcion')->findBy(array('estudianteInscripcion' => $eiid ));
            if ($objolim_estudiante_inscripcion) {
                $message = "No se puede eliminar por que el estudiante esta registrado en el sistema de Olimpiadas";
                $this->addFlash('warningremoveins', $message);
                return $this->redirectToRoute('remove_inscription_sie_index');
            }

            //get the student's inscription
            $objEstudiantInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eiid);
            //get institucioneducativaCurso info
            $objInsctitucionEducativaCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objEstudiantInscripcion->getInstitucioneducativaCurso()->getId());
            $arrAllowAccessOption = array(7,8);
            if(!in_array($this->session->get('roluser'),$arrAllowAccessOption)){

              $defaultController = new DefaultCont();
              $defaultController->setContainer($this->container);
              //get flag to do the operation
              $swAccess = $defaultController->getAccessMenuCtrl(array('sie'=>$objInsctitucionEducativaCurso->getInstitucioneducativa()->getId(), 'gestion'=>$gestion));
              //validate if the user download the sie file
              if($swAccess){
                $message = ' No se puede realizar la transacción debido a que ya descargo el archivo SIE, esta operación debe realizarlo con el Tec. de Departamento';
                $this->addFlash('warningremoveins', $message);
                return $this->redirectToRoute('remove_inscription_sie_index');
              }

            }

            //step 1 remove the inscription observado
            $objStudentInscriptionObservados = $em->getRepository('SieAppWebBundle:EstudianteInscripcionObservacion')->findBy(array('estudianteInscripcion' => $eiid));
            //dump($objStudentInscriptionObservados);
            //die;
            if ($objStudentInscriptionObservados){
                foreach ($objStudentInscriptionObservados as $element) {
                    $em->remove($element);
                }
                $em->flush();
                //$em->remove($objStudentInscriptionObservados);
                //$em->flush();
                $obs = $element->getObs();
            }
            else{
                $obs = '';
            }

//            step 2 delete nota
            $objEstAsig = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('estudianteInscripcion' => $eiid, 'gestionTipo' => $gestion ));

            //dump($objEstAsig);die;
//            foreach ($objEstAsig as $asig) {
//                $objNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura' => $asig->getId()));
//                if ($objNota) {
//
//                }
//            }

            //step 3 delete asignatura
            foreach ($objEstAsig as $element) {
                $objNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura' => $element));
                foreach($objNota as $element2)
                {
                    $em->remove($element2);
                }
                $em->remove($element);
            }
            $em->flush();
          //remove back up data
          // $query = $em->getConnection()->prepare("
          //     DELETE FROM __estudiante_nota_cualitativa_aux WHERE estudiante_inscripcion_id = " . $eiid . "
          // ");
          // $query->execute();


            //dump($objEstAsig);die;
            $objNotaC = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array('estudianteInscripcion' => $eiid));
            foreach ($objNotaC as $element) {
                $em->remove($element);
            }
            $em->flush();

//            array_walk($objEstAsig, array($this, 'deleteEntity'), $em);
            //step 3 delete nota cualitativa
//            $objNotaCualitativa = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array(
//                'estudianteInscripcion' => $eiid
//            ));
//            array_walk($objNotaCualitativa, array($this, 'deleteEntity'), $em);

            //step 4 delete socio economico data
            $objSocioEco = $em->getRepository('SieAppWebBundle:SocioeconomicoRegular')->findBy(array('estudianteInscripcion' => $eiid ));
            //dump($objSocioEco);die;
            foreach ($objSocioEco as $element) {
                $em->remove($element);
            }
            $em->flush();

            //step 5 delete apoderado_inscripcion data
            $objApoIns = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $eiid ));
            //dump($objApoIns);die;

            foreach ($objApoIns as $element) {
                $objApoInsDat = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findBy(array('apoderadoInscripcion' => $element->getId()));
                foreach ($objApoInsDat as $element1){
                    $em->remove($element1);
                }
                $em->remove($element);
            }
            $em->flush();

            //dump($objApoIns);die;
            //remove attached file
            $objStudentInscriptionExtranjero = $em->getRepository('SieAppWebBundle:EstudianteInscripcionExtranjero')->findOneBy(array('estudianteInscripcion'=>$eiid));
            if($objStudentInscriptionExtranjero){
              $em->remove($objStudentInscriptionExtranjero);
              $em->flush();
            }

           //paso 6 borrando apoderados
            $apoderados = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $eiid ));
            foreach ($apoderados as $element) {
                $em->remove($element);
            }
            $em->flush();

           //paso 7 borrando apoderados
            $objEstudianteInscripcionSocioeconomicoRegular = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegular')->findOneBy(array('estudianteInscripcion' => $eiid ));

            if($objEstudianteInscripcionSocioeconomicoRegular){
              $objEstudianteInscripcionSocioeconomicoRegNacion = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegNacion')->findBy(array(
                'estudianteInscripcionSocioeconomicoRegular' => $objEstudianteInscripcionSocioeconomicoRegular->getId()
              ));
              foreach ($objEstudianteInscripcionSocioeconomicoRegNacion as $element) {
                  $em->remove($element);
              }
              $em->flush();

              $objEstudianteInscripcionSocioeconomicoRegInternet = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegInternet')->findBy(array(
                'estudianteInscripcionSocioeconomicoRegular' => $objEstudianteInscripcionSocioeconomicoRegular->getId()
              ));
              foreach ($objEstudianteInscripcionSocioeconomicoRegInternet as $element) {
                  $em->remove($element);
              }
              $em->flush();

              $objEstudianteInscripcionSocioeconomicoRegHablaFrec = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegHablaFrec')->findBy(array(
                'estudianteInscripcionSocioeconomicoRegular' => $objEstudianteInscripcionSocioeconomicoRegular->getId()
              ));
              foreach ($objEstudianteInscripcionSocioeconomicoRegHablaFrec as $element) {
                  $em->remove($element);
              }
              $em->flush();

              $em->remove($objEstudianteInscripcionSocioeconomicoRegular);
              $em->flush();
            }

            //step 8 delete EstudianteInscripcionSocioeconomicoAlternativa data
            $objApoInsAlternativa = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAlternativa')->findBy(array('estudianteInscripcion' => $eiid ));
            //dump($objApoIns);die;

            foreach ($objApoInsAlternativa as $element) {
                $objApoInsDatAltHabla = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltHabla')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $element->getId()));
                foreach ($objApoInsDatAltHabla as $element1){
                    $em->remove($element1);
                }
                $objApoInsDatAltOcupacion = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltOcupacion')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $element->getId()));
                foreach ($objApoInsDatAltOcupacion as $element1){
                    $em->remove($element1);
                }
                $objApoInsDatAltAcceso = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltAcceso')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $element->getId()));
                foreach ($objApoInsDatAltAcceso as $element1){
                    $em->remove($element1);
                }
                $objApoInsDatAltTransporte = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoAltTransporte')->findBy(array('estudianteInscripcionSocioeconomicoAlternativa' => $element->getId()));
                foreach ($objApoInsDatAltTransporte as $element1){
                    $em->remove($element1);
                }

                $em->remove($element);

            }
            $em->flush();

             //paso X borrando objHumanistico
            $objHumanistico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findBy(array('estudianteInscripcion' => $eiid ));
            foreach ($objHumanistico as $element) {
                $em->remove($element);
            }
            $em->flush();

            //step 6 copy data to control table and remove teh inscription
            $objStudentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eiid);

            //paso Xi borrando objHumanistico
            $objEntity = $em->getRepository('SieAppWebBundle:Rude')->findBy(array('estudianteInscripcion' => $eiid ));
            foreach ($objEntity as $element) {
                $em->remove($element);
            }
            $em->flush();

            $studentInscription = new EstudianteInscripcionEliminados();
            $studentInscription->setEstudianteInscripcionId($objStudentInscription->getId());
            $studentInscription->setEstadomatriculaTipoId($objStudentInscription->getEstadoMatriculaTipo()->getId());
            $studentInscription->setEstudianteId($objStudentInscription->getEstudiante()->getId());
            $studentInscription->setNumMatricula($objStudentInscription->getNumMatricula());
            $studentInscription->setObservacionId($objStudentInscription->getObservacionId());
            $studentInscription->setObservacion($objStudentInscription->getObservacion());
            $studentInscription->setFechaInscripcion($objStudentInscription->getFechaInscripcion());
            $studentInscription->setApreciacionFinal($objStudentInscription->getApreciacionFinal());
            $studentInscription->setOperativoId($objStudentInscription->getOperativoId());
            $studentInscription->setFechaRegistro($objStudentInscription->getFechaRegistro());
            $studentInscription->setOrganizacion($objStudentInscription->getOrganizacion());
            $studentInscription->setFacilitadorpermanente($objStudentInscription->getFacilitadorpermanente());
            $studentInscription->setModalidadTipoId($objStudentInscription->getModalidadTipoId());
            $studentInscription->setAcreditacionnivelTipoId($objStudentInscription->getAcreditacionnivelTipoId());
            $studentInscription->setPermanenteprogramaTipoId($objStudentInscription->getPermanenteprogramaTipoId());
            $studentInscription->setFechaInicio($objStudentInscription->getFechaInicio());
            $studentInscription->setFechaFin($objStudentInscription->getFechaFin());
            $studentInscription->setCursonombre($objStudentInscription->getCursonombre());
            $studentInscription->setLugar($objStudentInscription->getLugar());
            $studentInscription->setLugarcurso($objStudentInscription->getLugarcurso());
            $studentInscription->setFacilitadorcurso($objStudentInscription->getFacilitadorcurso());
            $studentInscription->setFechainiciocurso($objStudentInscription->getFechainiciocurso());
            $studentInscription->setFechafincurso($objStudentInscription->getFechafincurso());
            $studentInscription->setCodUeProcedenciaId($objStudentInscription->getCodUeProcedenciaId());
            $studentInscription->setInstitucioneducativaCursoId($objStudentInscription->getInstitucioneducativaCurso()->getId());
            if(($objStudentInscription->getEstadomatriculaInicioTipo()))
              $studentInscription->setEstadomatriculaInicioTipoId($objStudentInscription->getEstadomatriculaInicioTipo()->getId());
            $studentInscription->setObsEliminacion($obs);
            $studentInscription->setUsuarioId($sesion->get('userId'));
            $studentInscription->setFechaEliminacion(new \DateTime('now'));
            $studentInscription->setDoc('false');
            $em->persist($studentInscription);
            $em->flush();

            $em->remove($objStudentInscription);
            $em->flush();

            // Try and commit the transaction
            $em->getConnection()->commit();
            $message = "Proceso realizado exitosamente.";
            $this->addFlash('successremoveins', $message);
            return $this->redirectToRoute('remove_inscription_sie_index');
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $message = "Proceso detenido! Se ha detectado inconsistencia de datos. \n".$ex->getMessage();
            $this->addFlash('warningremoveins', $message);
            return $this->redirectToRoute('remove_inscription_sie_index');
        }
    }

    /**
     * find the student and inscription data
     * @param Request $request
     * @return type the list of student and inscripion data
     */
    public function questAction($rude) {
        $em = $this->getDoctrine()->getManager();
        //check if the user is logged
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //get the result of search
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));
        //verificamos si existe el estudiante y si es menor a 15
        if ($student) {
            $dataInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getInscriptionHistoryEstudenWhitObservation($rude);
        }
        $sw = true;
        return $this->render($this->session->get('pathSystem') . ':RemoveInscription:index.html.twig', array(
                    'form' => $this->craeteformsearch()->createView(),
                    'datastudent' => $student,
                    'dataInscription' => $dataInscription,
                    'sw' => $sw,
        ));
    }
}
