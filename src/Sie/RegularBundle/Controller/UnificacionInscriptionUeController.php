<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEliminados;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * NewHistoryInscription controller.
 *
 */
class UnificacionInscriptionUeController extends Controller {

    private $session;

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
    public function indexAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $gestion=$this->session->get('currentyear');
        $sie = $this->session->get('ie_id');
        //check if the user is logged
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        /*if (in_array($this->session->get('roluser'), array(7,10))
            $this->session->get('roluser')!=8){
            return $this->redirect($this->generateUrl('login'));
        }*/
        //set the student and inscriptions data
        $student = array();
        $dataInscriptionR = array();
        
        $sw = false;
        if ($request->get('form')) {
            $form = $request->get('form');
            $tipo = intval($form['tipo']);

            if ($this->session->get('roluser')!=9){ //si no es director
                $sie = $form['codigoSie'];
            }
            if($tipo == 0) {
                $rude = trim(strtoupper($form['codigoRudeHistory']));
            } else {
                $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Estudiante');
                if($form['complemento']) {
                    $query = $repository->createQueryBuilder('e')
                    ->where('e.carnetIdentidad = :carnetIdentidad')
                    ->andWhere('upper(e.complemento) = :complemento')
                    ->andWhere('e.segipId = :segipId')
                    ->setParameter('carnetIdentidad', $form['carnetIdentidad'])
                    ->setParameter('complemento', mb_strtoupper($form['complemento'], 'utf8'))
                    ->setParameter('segipId', '1')
                    ->orderBy('e.carnetIdentidad', 'ASC')
                    ->getQuery();
                } else {
                    $query = $repository->createQueryBuilder('e')
                    ->where('e.carnetIdentidad = :carnetIdentidad')
                    ->andWhere('e.segipId = :segipId')
                    ->setParameter('carnetIdentidad', $form['carnetIdentidad'])
                    ->orderBy('e.carnetIdentidad', 'ASC')
                    ->setParameter('segipId', '1')
                    ->getQuery();
                }

                $entities = $query->getResult();
                
                $rude = null;
                if(count($entities) == 1) {
                    $rude = trim(strtoupper($entities[0]->getCodigoRude()));
                }
            }

            $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));

            if (in_array($this->session->get('roluser'), array(7,9,10))){ // el director solo puede trabajar su inscripcion
                $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
                $query->bindValue(':user_id', $this->session->get('userId'));
                $query->bindValue(':sie', $sie);
                $query->bindValue(':rolId', $this->session->get('roluser'));
                $query->execute();
                $aTuicion = $query->fetchAll();
    
                if ($aTuicion[0]['get_ue_tuicion']) {
                    
                } else {
                    $message = 'No tiene tuición sobre la Inscripción del Estudiante.'; 
                    $this->addFlash('notihistory', $message);
                    $sw = false;
                    return $this->redirectToRoute('unificacion_inscription_index');
                }

                //dump($codigoRude);
                /*
                $tuicionUe = $this->get('funciones')->getInscriptionToValidateTuicionUe($rude, $gestion);
                if($tuicionUe){
                    //do change
                }
                else{
                    
                }*/
            }

            if($student){
                $dataInscriptionR =  $em->getRepository('SieAppWebBundle:Estudiante')->getHistoryInscriptionPerStudentUe($student->getId(), $gestion, $sie);
                //dump($dataInscriptionR);die;
                $sw = true;
                if(count($dataInscriptionR)==1){
                    $message = 'No se puede unificar las Inscripciones, la/el Estudiante solo cuenta con una Inscripción vigente.'; 
                    $this->addFlash('notihistory', $message);
                    $sw = false;
                    return $this->redirectToRoute('unificacion_inscription_index');
                }
                //si tiene tuicion
            }else{
                if ($tipo == 0) {
                    $message = 'La/el studiante con Código RUDE: ' . $rude . ', no presenta registro de inscripciones.';
                } else {
                    $message = 'La/el studiante con Carnet de Identidad: ' . $form['carnetIdentidad'] . ' y Complemento:' . mb_strtoupper($form['complemento'], 'utf8') . ', no presenta registro de inscripciones.';
                }
                
                $this->addFlash('notihistory', $message);
                $sw = false;
                return $this->redirectToRoute('unificacion_inscription_index');
            }
        }
            
        return $this->render($this->session->get('pathSystem') . ':UnificacionInscriptionUe:index.html.twig', array(
            'form' => $this->createSearchForm()->createView(),
            'form1' => $this->createSearchCIForm()->createView(),
            'datastudent' => $student,
            'dataInscriptionR' => $dataInscriptionR,
            'sw' => $sw,
            'visible' => true
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
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('unificacion_inscription_index'))
            ->add('codigoRudeHistory', 'text', array('label' => 'Rude', 'invalid_message' => 'campo obligatorio', 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 20, 'required' => true, 'class' => 'form-control')))
            ->add('codigoSie', 'text', array('label' => 'Sie', 'invalid_message' => 'campo obligatorio', 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 10, 'required' => true, 'class' => 'form-control')))
            ->add('buscar0', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary')))
                        ->add('buscar0', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();
        return $form;
    }

    private function createSearchCIForm() {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('unificacion_inscription_index'))
            ->add('carnetIdentidad', 'text', array('required' => true, 'invalid_message' => 'Campo obligatorio', 'attr' => array('pattern' => '[0-9]{3,10}', 'maxlength' => 10, 'style' => 'text-transform:uppercase', 'class' => 'form-control')))
            ->add('complemento', 'text', array('required' => false, 'invalid_message' => 'Campo obligatorio', 'attr' => array('pattern' => '[a-zA-Z0-9]{0,2}', 'maxlength' => 2, 'style' => 'text-transform:uppercase', 'class' => 'form-control')))
            ->add('codigoSie', 'text', array('label' => 'Sie', 'invalid_message' => 'campo obligatorio', 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 10, 'required' => true, 'class' => 'form-control')))
            ->add('buscar1', 'submit', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();
        return $form;
    }

    public function removeInscriptionAction($gestion, $eiid, Request $request)
    {
       
        $sesion = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {

            //get the student's inscription
            $objEstudiantInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eiid);
            //get institucioneducativaCurso info
            $objInsctitucionEducativaCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objEstudiantInscripcion->getInstitucioneducativaCurso()->getId());
           

            $objEstudianteInscripcionCambioestado = $em->getRepository('SieAppWebBundle:EstudianteInscripcionCambioestado')->findBy(array('estudianteInscripcion' => $eiid));
            foreach ($objEstudianteInscripcionCambioestado as $element) {
                $em->remove($element);
            }
            $em->flush();            

        //   step 2 delete nota
            $objEstAsig = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('estudianteInscripcion' => $eiid, 'gestionTipo' => $gestion ));
            if(sizeof($objEstAsig)>0){
                foreach ($objEstAsig as $element) {
                    $em->remove($element);
                }
            }
            $em->flush();
        
            $obs = '';  

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

             //paso X borrando objHumanistico
            $objHumanistico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findBy(array('estudianteInscripcion' => $eiid ));
            foreach ($objHumanistico as $element) {
                $em->remove($element);
            }
            $em->flush();

            //step 6 copy data to control table and remove teh inscription
            $objStudentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eiid);

            //to remove all info about RUDE
            $objRude = $em->getRepository('SieAppWebBundle:Rude')->findOneBy(array('estudianteInscripcion' => $eiid ));

            if($objRude){

                $objRudeAbandono = $em->getRepository('SieAppWebBundle:RudeAbandono')->findBy(array('rude' => $objRude->getId() ));

                foreach ($objRudeAbandono as $element) {
                    $em->remove($element);
                }
                $em->flush();


                $objRudeAccesoInternet = $em->getRepository('SieAppWebBundle:RudeAccesoInternet')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeAccesoInternet as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeActividad = $em->getRepository('SieAppWebBundle:RudeActividad')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeActividad as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeCentroSalud = $em->getRepository('SieAppWebBundle:RudeCentroSalud')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeCentroSalud as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeDiscapacidadGrado = $em->getRepository('SieAppWebBundle:RudeDiscapacidadGrado')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeDiscapacidadGrado as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeEducacionDiversa = $em->getRepository('SieAppWebBundle:RudeEducacionDiversa')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeEducacionDiversa as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeIdioma = $em->getRepository('SieAppWebBundle:RudeIdioma')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeIdioma as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeMedioTransporte = $em->getRepository('SieAppWebBundle:RudeMedioTransporte')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeMedioTransporte as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeMediosComunicacion = $em->getRepository('SieAppWebBundle:RudeMediosComunicacion')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeMediosComunicacion as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeRecibioPago = $em->getRepository('SieAppWebBundle:RudeRecibioPago')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeRecibioPago as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeServicioBasico = $em->getRepository('SieAppWebBundle:RudeServicioBasico')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeServicioBasico as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeTurnoTrabajo = $em->getRepository('SieAppWebBundle:RudeTurnoTrabajo')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeTurnoTrabajo as $element) {
                    $em->remove($element);
                }
                $em->flush();

                $objRudeMesesTrabajados = $em->getRepository('SieAppWebBundle:RudeMesesTrabajados')->findBy(array('rude' => $objRude->getId() ));
                foreach ($objRudeMesesTrabajados as $element) {
                    $em->remove($element);
                }
                $em->flush();

            }


            /*$objRudeApoderadoInscripcion = $em->getRepository('SieAppWebBundle:RudeApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $eiid ));
            foreach ($objRudeApoderadoInscripcion as $element) {
                $em->remove($element);
            }
            $em->flush();*/

            $objCdlInscripcion = $em->getRepository('SieAppWebBundle:CdlIntegrantes')->findBy(array('estudianteInscripcion' => $eiid ));
            foreach ($objCdlInscripcion as $element) {
                $em->remove($element);
            }
            $em->flush();            


            if ($objRude) {
                $em->remove($objRude);
            }
            $em->flush();
            
            //eliminados los datos de la tabla bjp_apoderado_inscripcion 
            $bjpApoderadoInscripcion = $em->getRepository('SieAppWebBundle:BjpApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $eiid ));
            foreach ($bjpApoderadoInscripcion as $element)
            {
                $em->remove($element);
            }
            $em->flush();


            //END to remove all info about RUDE

            $studentInscription = new EstudianteInscripcionEliminados();
            $studentInscription->setEstudianteInscripcionId($objStudentInscription->getId());
            $studentInscription->setEstadomatriculaTipoId($objStudentInscription->getEstadoMatriculaTipo()->getId());
            $studentInscription->setEstudianteId($objStudentInscription->getEstudiante()->getId());
            $studentInscription->setNumMatricula($objStudentInscription->getNumMatricula());
            $studentInscription->setObservacionId($objStudentInscription->getObservacionId());
            $studentInscription->setObservacion($objStudentInscription->getObservacion());
            $studentInscription->setFechaInscripcion($objStudentInscription->getFechaInscripcion());
            $studentInscription->setApreciacionFinal('CORREGIR DOBLE INSCRIPCION '.$objStudentInscription->getApreciacionFinal());
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
            $this->addFlash('notihistory', $message);
            $sw = false;
        return $this->redirectToRoute('unificacion_inscription_index');

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $message = "Proceso detenido! Se ha detectado inconsistencia de datos. \n".$ex->getMessage();
            $this->addFlash('warningremoveins', $message);
            return $this->redirectToRoute('unificacion_inscription_index');
        }
    }

 


}