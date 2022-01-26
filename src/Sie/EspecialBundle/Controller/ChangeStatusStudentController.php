<?php

namespace Sie\EspecialBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\PaisTipo;
use Sie\AppWebBundle\Form\EstudianteType;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;

class ChangeStatusStudentController extends Controller {



    private $session;
    private $operativo;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * index action build the form to search
     *
     */
    public function indexNewAction(){

        return $this->render('SieEspecialBundle:ChangeStatusStudent:index.html.twig', array(
                // ...
            ));    
    }    
    public function indexAction(Request $request) {
        # SERVICIO QUE CONTROLA EL ACCESO A LAS OPCIONES POR URL
        # VALIDA SI EL ROL DEL USUARIO TIENE PERMISO SOBRE LA URL
        /*if(!$this->get('funciones')->validarRuta($request->attributes->get('_route'))){
            return $this->redirect($this->generateUrl('principal_web'));
        }*/

//die('krlos');
        $em = $this->getDoctrine()->getManager();
        $this->session->set('removeinscription', false);
        // return $this->redirectToRoute('principal_web');
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':ChangeStatusStudent:index.html.twig', array(
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
        //set new gestion to the select year
        $aGestion = array();
        $currentYear = date('Y');
        for ($i = 1; $i <= 2; $i++) {
            $aGestion[$currentYear] = $currentYear;
            $currentYear--;
        }
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('especial_chagestatusstudent_result'))
                ->add('codigoRude', 'text', array('mapped' => false, 'label' => 'Rude', 'required' => true, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control', 'maxlength' => 19, 'pattern' => '[A-Z0-9]{13,19}', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'choice', array('label' => 'Gestión', 'choices' => $aGestion, 'attr' => array('class' => 'form-control')))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
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

         /**
         * add validation QA
         * @var [type]
         */

        

        //get the info student
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));

        //check if the student exists
        if ($objStudent) {
            $objInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsEfectivosEspecial($objStudent->getId(), $form['gestion']);

            //check it the current inscription exists
            if (sizeof($objInscription) > 0) {
                //$objInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsEfectivos($objStudent->getId(), $this->session->get('currentyear'));
                //if the student has current inscription with matricula 4, so build the student form

                return $this->render($this->session->get('pathSystem') . ':ChangeStatusStudent:result.html.twig', array(
                            //'form' => $this->createFormStudent($objStudent->getCodigoRude(), $this->session->get('currentyear'))->createView(),
                            'datastudent' => $objStudent,
                            'dataInscription' => $objInscription
                ));
            } else {
                $message = 'Estudiante no presenta más de una inscripción como efectivo para la presente gestión';
                $this->addFlash('notiremovest', $message);
                return $this->redirectToRoute('especial_chagestatusstudent_index');
            }
        } else {
            $message = 'Estudiante no registrado';
            $this->addFlash('notiremovest', $message);
            return $this->redirectToRoute('especial_chagestatusstudent_index');
        }
    }
    /**
    * show the view main to do the change
    **/
    public function mainCambioEstadoAction(Request $request){

      //crete the DB conexion
      $em = $this->getDoctrine()->getManager();
      //get the send dataInfo
      $dataInfo = $request->get('dataInfo');
      $arrDataInfo = json_decode($dataInfo,true);

      $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($arrDataInfo['estId']);
      //get the student's inscription
      $objEstudiantInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrDataInfo['estInsId']);
      //get institucioneducativaCurso info
      $objInsctitucionEducativaCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objEstudiantInscripcion->getInstitucioneducativaCurso()->getId());
      $arrAllowAccessOption = array(7,8,9);
      if(!in_array($this->session->get('roluser'),$arrAllowAccessOption)){

        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);
        //get flag to do the operation
        $swAccess = $defaultController->getAccessMenuCtrl(array('sie'=>$objInsctitucionEducativaCurso->getInstitucioneducativa()->getId(), 'gestion'=>$arrDataInfo['gestion']));
        //validate if the user download the sie file
        if($swAccess){
          $message = 'No se puede realizar la transacción debido a que ya descargo el archivo SIE, esta operación debe realizarlo con el Tec. de Departamento';
          $this->addFlash('idNoInscription', $message);
          return $this->render($this->session->get('pathSystem') . ':InscriptionIniPriTrue:menssageInscription.html.twig', array());
        }

      }

      return $this->render($this->session->get('pathSystem') . ':ChangeStatusStudent:maincambioestado.html.twig', array(
                  'form' => $this->mainChangeStadoForm($dataInfo,$this->session->get('roluser'))->createView(),
                  'datastudent' => $objStudent,
                  'dataInscription' => $objEstudiantInscripcion
                ));

    }
    /**
    * create the for with the new estados
    **/
    private function mainChangeStadoForm($dataInfo, $rolUser){
      // $arrEstados = array('4'=>'Efectivo', '10'=>'Abandono');
      $rolesAllowed = array(7,10,9);
      if(in_array($rolUser,$rolesAllowed)){
        $arrEstados = array('4'=>'EFECTIVO', '10'=>'RETIRO ABANDONO');
      }else{
        // $arrEstados = array( '10'=>'RETIRO ABANDONO',/*'6'=>'NO INCORPORADO','9'=>'RETIRADO TRASLADO'*/);
        if($rolUser == 8)
          $arrEstados = array('4'=>'EFECTIVO', '10'=>'RETIRO ABANDONO','6'=>'NO INCORPORADO','9'=>'RETIRADO TRASLADO');
        else
          $arrEstados = array();
      }

      return $this->createFormBuilder()
        ->add('estadonew', 'choice', array('label'=>'Estado', 'choices'=>$arrEstados, 'attr'=>array('class'=>'form-control')))
        ->add('verificar', 'button', array('label'=>'Verificar', 'attr'=>array('class'=>'btn btn-info', 'onclick'=>"verificarCambio('$dataInfo')")))
        ->getForm();
    }
    /**
     * [validateNotas description]
     * @param  [type] $notas [description]
     * @param  [type] $state [description]
     * @return [type]        [description]
     */
    private function validateNotas($notas, $state){
      // set the parameters
      $notasComplete = array();
      $swChangeStatus = false;
        foreach ($notas['cuantitativas'] as $key => $value) {
          if(isset($value['nota'])){
            $notasComplete[]=$value['nota'];
          }
        }
        // dump($notasComplete);die;
        if($state==6 || $state==10){
          if(sizeof($notasComplete)>1){
            $swChangeStatus=true;
          }
        }
        //return the validate info
        return $swChangeStatus;
    }
    /**
     * [verificarCambioAction description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function verificarCambioAction(Request $request){
      //cretae the db conexion
      $em = $this->getDoctrine()->getManager();

      //get the send values
      $dataInfo = $request->get('dataInfo');
      $estadoNew = $request->get('estadonew');
      $arrDataInfo = json_decode($dataInfo,true);
      // dump($arrDataInfo);      die;
      

      //get the operativo information
      $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToStudent($arrDataInfo)-1;
      $this->operativo = $this->get('funciones')->obtenerOperativo($arrDataInfo['sie'],$arrDataInfo['gestion']);
      //get the info student
      $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $arrDataInfo['rude']));
      //dump($arrDataInfo);die;
      //answer the verification
      $swverification = true;
      if($arrDataInfo['estadoMatriculaId']==$estadoNew){
        $swverification = false;
        $message = 'No realizado, esta intentando cambiar al mismo estado... ';
        $this->addFlash('changestate', $message);
      }

      // add validation about tuicion of user
      $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
      $query->bindValue(':user_id', $this->session->get('userId'));
      $query->bindValue(':sie', $arrDataInfo['sie']);
      $query->bindValue(':roluser', $this->session->get('roluser'));
      $query->execute();
      $aTuicion = $query->fetchAll();
            //check if the user has the tuicion
      if (!$aTuicion[0]['get_ue_tuicion']) {
        $swverification = false;
        $message = "Usuario no tiene tuición para realizar la operación";
        $this->addFlash('changestate', $message);
      }
      // dump($estadoNew);die;
      //ge notas to do the changeMatricula to Retiro Abandono
      $swChangeStatus = false;
      
      /*validation abuot the NOTAS*/
      if($this->operativo > 0 ){
        $notas = $this->get('notas')->regular($arrDataInfo['estInsId'],$this->operativo);
        $swChangeStatus = $this->validateNotas($notas, $estadoNew);

        if($swChangeStatus && $estadoNew==6){
          $swverification = false;
          $message = 'No realizado, Estudidante cuenta con calificaciones. ';
          $this->addFlash('changestate', $message);
        }

        if($swChangeStatus && $estadoNew==10){
          $swverification = false;
          $message = 'No realizado, Estudidante no cuenta con calificaciones. ';
          $this->addFlash('changestate', $message);
        }
      }

      //get the students inscriptions
      $objInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsEfectivosChange($objStudent->getId(), $arrDataInfo['gestion']);

      if($estadoNew == 4){

        //check if the student has more than one inscription
        if (sizeof($objInscription) > 1) {
          $objInscriptionCurrent = $em->getRepository('SieAppWebBundle:Estudiante')->findCurrentStudentInscription($objStudent->getId(), $arrDataInfo['gestion']);
          //check if the student has inscription like EFECTIVO
          if($objInscriptionCurrent){
            $swverification = false;
            $message = 'No se puede realizar el cambio, por que el Estudiante ya cuenta con otra inscripción en estado EFECTIVO';
            $this->addFlash('changestate', $message);
          }
        }

      }
      $operativo = 3;
    //dump($operativo);die;
      //dump($objInscriptionCurrent);die;
    // Verifcamos si la unidad educativa del estudiante ya consolido 3er bimestre
    if($operativo < 3){
        $swverification = false;
        $message = 'No realizado, para poder realizar el cambio de estado la unidad educativa en la cual esta inscrito el(la) estudiante debe consolidar el tercer bimestre';
        $this->addFlash('changestate', $message);
    }
    // Para ues que ya consolidaron el cuarto bimetre
    // Se ajustara el modulo para que calcule los promedios, aun no promedia
    if($operativo > 3){
        $swverification = false;
        $message = 'No se puede realizar el cambio de estado!!!';
        $this->addFlash('changestate', $message);
    }
// dump($arrDataInfo);die;
    return $this->render($this->session->get('pathSystem') . ':ChangeStatusStudent:verificarcambio.html.twig', array(
              'swverification' => $swverification,
              'operativo'=>$operativo,
              'dataInfo'  => $dataInfo ,
              'estadoNew' => $estadoNew
              ));




    }

    /*
    // this is the next step to do the setting up NOTAS to student
    */
    private function setNotasForm($data){
      return  $this->createFormBuilder()
                ->setAction($this->generateUrl('regularizarNotas_show'))
                // ->add('idInscripcion','text',array('data'=>$data))
                //->add('setNotas','submit',array('label'=>'Registrar Notas','attr'=>array('class'=>'btn btn-success')))
              ->getForm();
    }

    /**
    * Function saveCambioEstadoAction
    *
    * @author krlos Pacha C. <pckrlos@gmail.com>
    * @access public
    * @param object request
    * @return string
    */
    public function saveCambioEstadoAction(Request $request) {


        //get the send values
        $form = $request->get('form');
        $arrDataInfo = json_decode($form['dataInfo'],true);
// dump($form['estadoNew']);die;
        //create the DB conexion
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {

          //find the estudent's inscription to do the change
          $inscriptionStudent = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrDataInfo['estInsId']);
          $oldInscriptionStudent = clone $inscriptionStudent;
          $inscriptionStudent->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($form['estadoNew']));
          $inscriptionStudent->setObservacion($form['justificacionCE']==null?'':$form['justificacionCE']);//$form['justificacionCE']
          $em->persist($inscriptionStudent);
          $em->flush();
          
          $em->getConnection()->commit();
          // added set log info data
          $this->get('funciones')->setLogTransaccion(
                               $inscriptionStudent->getId(),
                                'estudiante_inscripcion',
                                'U',
                                '',
                                $inscriptionStudent,
                                $oldInscriptionStudent,
                                'SIGED',
                                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
          );     
         
          $message = "Proceso realizado exitosamente.";
          $this->addFlash('okchange', $message);
          $response = new JsonResponse();
          //check the tipo of matricula
          if($form['estadoNew']==4){
            return $this->render($this->session->get('pathSystem') . ':ChangeStatusStudent:setNotasChangeStatus.html.twig', array(
                      'setNotasForm'   => $this->setNotasForm($arrDataInfo['estInsId'])->createView(),
                      'idInscripcion'  => $arrDataInfo['estInsId'],
                      'newStatus'      => $form['estadoNew']
                      ));
          }else{
            return $response->setData(array('reloadIt'=>true, 'mssg'=>'Cambio de estado realizado exitosamente'));
            // die('Cambio de estado realizado exitosamente !');
          }


        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $message = "Proceso detenido! se ha detectado inconsistencia de datos.";
            $this->session->getFlashBag()->add('notihistory', $ex->getMessage());
            return $this->redirectToRoute('history_inscription_quest', array('rude' => $form['codigoRude']));
        }
    }




////////////////////////////////////////////////////////////////////////77777we need to check this code down
    /**
     * obtien el formulario para realizar la modificacion
     * @param Request $request
     */
    public function resultparamAction($rude, $gestion) {
        $em = $this->getDoctrine()->getManager();

        //get the info student
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));
        //check if the student exists
        if ($objStudent) {
            //get the inscription info about student getnumberInscription
            //$objNumberInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getnumberInscription($objStudent->getId(), $this->session->get('currentyear'));
            $objInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsEfectivos($objStudent->getId(), $gestion);
            //check it the current inscription exists
            if (sizeof($objInscription) > 0) {
                //$objInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsEfectivos($objStudent->getId(), $this->session->get('currentyear'));
                //if the student has current inscription with matricula 4, so build the student form
                return $this->render($this->session->get('pathSystem') . ':ChangeStatusStudent:result.html.twig', array(
                            //'form' => $this->createFormStudent($objStudent->getCodigoRude(), $this->session->get('currentyear'))->createView(),
                            'datastudent' => $objStudent,
                            'dataInscription' => $objInscription
                ));
            } else {
                $message = 'Estudiante no presenta más de una inscripción como efectivo para la presente gestión';
                $this->addFlash('notiremovest', $message);
                return $this->redirectToRoute('remove_inscription_student_free_index');
            }
        } else {
            $message = 'Estudiante no registrado';
            $this->addFlash('notiremovest', $message);
            return $this->redirectToRoute('remove_inscription_student_free_index');
        }
    }


    /**
     * create form Student
     * @param type $data
     * @return the student form
     */
    private function createFormStudent($data) {
        $formStudent = $this->createFormBuilder()
                ->setAction($this->generateUrl('change_state_student_modify'))
                ->add('idStudent', 'hidden', array('data' => $data['idStudent']))
                ->add('iecId', 'hidden', array('data' => $data['institucioneducativaCurso']))
                ->add('save', 'submit', array('label' => 'Retirar Esudiante', 'attr' => array('class' => 'btn btn-default btn-block')));

        return $formStudent->getForm();
    }

    /**
     * todo the registration of traslado
     * @param Request $request
     *
     */
    public function modifyAction(Request $request) {
        try {
            //create conexion on DB
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            //get the variblees
            $form = $request->get('form');
            //look for the student
            $studentInsc = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array(
                'institucioneducativaCurso' => $form['iecId'],
                'estudiante' => $form['idStudent']
            ));
            //update the student's estado matricula
            $studentInsc->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(6));
            $em->persist($studentInsc);
            $em->flush();
            //do the commit of DB
            $em->getConnection()->commit();
            $message = 'Operación realizada correctamente';
            $this->addFlash('goodstate', $message);
            return $this->redirectToRoute('change_state_student_index');
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }



}
