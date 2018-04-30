<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
/**
 * ChangestadoqaController Gestión de Menú Controller.
 */
class ChangestadoqaController extends Controller {

    public $session;
    public $idInstitucion;
    public $arrLevel;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();


    }

    /**
     * Muestra el listado de Menús
     */
    public function indexAction(Request $request) {
      
        $id_usuario = $this->session->get('userId');
        $em = $this->getDoctrine()->getManager();
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        $form = $request->get('form');
        //get the rudes
        $idProceso = $form['idDetalle'];
        $rudeOne = $form['llave'];
        $rudeTwo = 'x';
        $gestion = $form['gestion'];
        $optionTodo = $form['optionTodo'];//1=change state; 2=delete student
        // $institucioneducativa = $form['institucioneducativa'];
        $sw = true;
        //get the result of search
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rudeOne));
        // get the students historial
        $dataInscription = $this->getHistoryStudent($rudeOne,$gestion);
        // dump($dataInscription);die;
        // $studentTwo = $this->getHistoryStudent($rudeTwo,$gestion,$institucioneducativa);
        //send the values to the twig
        $eliminaRude = false;
        if(isset($form['optionEliminaRude'])){
          $eliminaRude = true;
        }
        return $this->render($this->session->get('pathSystem') . ':Changestadoqa:index.html.twig', array(
                    // 'form' => $this->createSearchForm()->createView(),
                    'datastudent'     => $student,
                    'dataInscription' => $dataInscription,
                    'sw'              => $sw,
                    'optionTodo'      => $optionTodo,
                    'idProceso'      => $idProceso,
                    'gestionSel'     =>  $gestion,
                    'eliminaRude' => $eliminaRude
        ));
        // dump($request);die;
        // return $this->render('SieRegularBundle:Changestadoqa:index.html.twig', array(
        // ));
      }
      /*
      get the history per student and gestion
      */
      private function getHistoryStudent($rude, $gestion){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select * from get_estudiante_historial_json('" . $rude . "');");
        $query->execute();
        $dataInscriptionJson = $query->fetchAll();
        $dataInscription=array();
        foreach ($dataInscriptionJson as $key => $inscription) {
           # code...
           $dataInscription [] = json_decode($inscription['get_estudiante_historial_json'],true);
         }
         return $dataInscription;
      }

      /**
      * show the view main to do the change
      **/
      public function mainCambioEstadoAction(Request $request){

        //crete the DB conexion
        $em = $this->getDoctrine()->getManager();
        //get the send dataInfo
        $dataInfo = $request->get('dataInfo');
        $gestion = $request->get('gestion');
        $idProceso = $request->get('idProceso');
        $arrDataInfo = json_decode($dataInfo,true);

        //get data student
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($arrDataInfo['estId']);
        //get the student's inscription
        $objEstudiantInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrDataInfo['estInsId']);
        //get institucioneducativaCurso info
        $objInsctitucionEducativaCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objEstudiantInscripcion->getInstitucioneducativaCurso()->getId());
        $arrAllowAccessOption = array(7,8,10);
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

        return $this->render($this->session->get('pathSystem') . ':Changestadoqa:maincambioestado.html.twig', array(
                    'form' => $this->mainChangeStadoForm($dataInfo,$idProceso)->createView(),
                    'datastudent' => $objStudent,
                    'dataInscription' => $objEstudiantInscripcion,
                    'gestion' => $gestion,
                    'idProceso' => $idProceso,
                  ));

      }
      /**
      * create the for with the new estados
      **/
      private function mainChangeStadoForm($dataInfo,$idProceso){
        // $arrEstados = array('4'=>'Efectivo', '10'=>'Abandono');
        // $arrEstados = array('6'=>'NO INCORPORADO');
        $arrDataInfo = json_decode($dataInfo,true);
        $operativo = $this->get('funciones')->obtenerOperativo($arrDataInfo['sie'],$arrDataInfo['gestion']);
        $rolesAllowed = array(7,8,10);
        if(in_array($this->session->get('roluser'),$rolesAllowed)){
          if($operativo>1){
            $arrEstados = array('9'=>'RETIRADO TRASLADO','10'=>'RETIRADO ABANDONO');
          }else{
            $arrEstados = array('6'=>'NO INCORPORADO');
          }

        }else{
          $arrEstados = array('6'=>'NO INCORPORADO');
        }

        return $this->createFormBuilder()
          ->add('estadonew', 'choice', array('label'=>'Estado', 'choices'=>$arrEstados, 'attr'=>array('class'=>'form-control')))
          ->add('idProcess', 'hidden', array('label'=>'Estado', 'attr'=>array('class'=>'form-control','value'=>$idProceso, )))
          ->add('verificar', 'button', array('label'=>'Verificar', 'attr'=>array('class'=>'btn btn-info', 'onclick'=>"verificarCambio('$dataInfo')")))
          ->getForm();
      }

      public function verificarCambioAction(Request $request){
        //cretae the db conexion
        $em = $this->getDoctrine()->getManager();
        //get the send values
        $dataInfo = $request->get('dataInfo');
        $estadoNew = $request->get('estadonew');
        $idProceso = $request->get('idProcess');
        $arrDataInfo = json_decode($dataInfo,true);
        //dump($arrDataInfo);die;
        //get the operativo information
        $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToStudent($arrDataInfo)-1;
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
        // Verificamos si el nuevo estado es efectivo
        // entonces verificamos si el estudiante cuenta con calificaciones segun el operativo


        return $this->render($this->session->get('pathSystem') . ':Changestadoqa:verificarcambio.html.twig', array(
                  'swverification' => $swverification,'operativo'=>$operativo,
                  'dataInfo'  => $dataInfo ,
                  'estadoNew' => $estadoNew,
                  'idProceso' => $idProceso,
                  ));
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
          $idProceso = $form['idProcess'];

          //create the DB conexion
          $em = $this->getDoctrine()->getManager();
          $em->getConnection()->beginTransaction();

          try {

              /*
               *   Registramos las notas cuantitativas
              */
              // $idEstudianteNota = $request->get('idEstudianteNota');
              // $idNotaTipo = $request->get('idNotaTipo');
              // $idEstudianteAsignatura = $request->get('idEstudianteAsignatura');
              // $nota = $request->get('nota');


              /*
              * Registramos las notas cualitativas
              */
              $idEstudianteNotaCualitativa = $request->get('idEstudianteNotaCualitativa');
              $idNotaTipoCualitativa = $request->get('idNotaTipoCualitativa');
              $notaCualitativa = $request->get('notaCualitativa');
              // Registro de notas cualitativas de incial y secundaria
              $idInscripcion = $arrDataInfo['estInsId'];


              //find the estudent's inscription to do the change
              $inscriptionStudent = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrDataInfo['estInsId']);
              $inscriptionStudent->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($form['estadoNew']));
              $em->persist($inscriptionStudent);
              $em->flush();
              //execute the sql qa
              // $query = $em->getConnection()->prepare("select * from sp_sist_calidad_doble_insc(2,'" . $arrDataInfo['rude'] . "',''," . $arrDataInfo['gestion'] . ");");
              // $query->execute();

              $query = $em->getConnection()->prepare('SELECT sp_sist_calidad_doble_insc(:tipOpe::VARCHAR, :rude::VARCHAR, :option::VARCHAR, :gestion::VARCHAR)');
              $query->bindValue(':tipOpe', 2);
              $query->bindValue(':rude', $arrDataInfo['rude']);
              $query->bindValue(':option', '');
              $query->bindValue(':gestion', $arrDataInfo['gestion']);
              $query->execute();

              //update the validation process table
              $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($idProceso);
              $vproceso->setEsActivo('t');
              $em->persist($vproceso);
              $em->flush();

              $em->getConnection()->commit();

              $message = "Proceso realizado exitosamente.";
              $this->addFlash('okchange', $message);die('Cambio de estado realizado exitosamente !');
              // return $this->redirectToRoute('history_inscription_quest', array('rude' => $form['codigoRude']));
              return $this->redirect($this->generateUrl('ccalidad_list', array('id' => 2)));
          } catch (Exception $ex) {
              $em->getConnection()->rollback();
              $message = "Proceso detenido! se ha detectado inconsistencia de datos.";
              $this->session->getFlashBag()->add('notihistory', $ex->getMessage());
              //return $this->redirectToRoute('history_inscription_quest', array('rude' => $form['codigoRude']));
          }
      }

      /**
      * Function saveCambioEstadoAction
      *
      * @author krlos Pacha C. <pckrlos@gmail.com>
      * @access public
      * @param object request
      * @return string
      */
      public function mainEliminarInscriptionAction(Request $request) {
        
          //get the send values
          $dataInfo = $request->get('dataInfo');
          $gestionSelected = $request->get('gestion');
          $idProceso = $request->get('idProceso');
          $eliminaRude = $request->get('eliminaRude');
          $arrDataInfo = json_decode($dataInfo,true);
          
          //create the DB conexion
          $em = $this->getDoctrine()->getManager();
          $em->getConnection()->beginTransaction();

          try {
            //get the student's inscription
            $objEstudiantInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrDataInfo['estInsId']);
            //get institucioneducativaCurso info
            $objInsctitucionEducativaCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objEstudiantInscripcion->getInstitucioneducativaCurso()->getId());
            $arrAllowAccessOption = array(7,8);
            if(!in_array($this->session->get('roluser'),$arrAllowAccessOption)){

              $defaultController = new DefaultCont();
              $defaultController->setContainer($this->container);
              //get flag to do the operation
              $swAccess = $defaultController->getAccessMenuCtrl(array('sie'=>$objInsctitucionEducativaCurso->getInstitucioneducativa()->getId(), 'gestion'=>$arrDataInfo['gestion']));
              //validate if the user download the sie file
              if($swAccess){
                $message = ' No se puede realizar la transacción debido a que ya descargo el archivo SIE, esta operación debe realizarlo con el Tec. de Departamento';
                $this->addFlash('okchange', $message);

                // (' Eliminacion de Inscripción realizado exitosamente !');
                return new JsonResponse(array('message'=>$message, 'gestionSelected'=>$gestionSelected, 'typeMessage'=>0));

                // return $this->render($this->session->get('pathSystem') . ':InscriptionIniPriTrue:menssageInscription.html.twig', array());
              }

            }
            // dump($arrDataInfo);die;

              //find the estudent's inscription to do the change
              $inscriptionStudentOBs = $em->getRepository('SieAppWebBundle:EstudianteInscripcionObservacion')->findBy(array('estudianteInscripcion' => $arrDataInfo['estInsId']));
              foreach ($inscriptionStudentOBs as $key => $value) {
                $em->remove($value);
                $em->flush();
              }
              
              $inscriptionStudentAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('estudianteInscripcion' => $arrDataInfo['estInsId']));
              foreach ($inscriptionStudentAsignatura as $key => $value) {
                $em->remove($value);
                $em->flush();
              }

              $inscriptionStudent = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrDataInfo['estInsId']);
              $em->remove($inscriptionStudent);
              $em->flush();
              
              $student = $em->getRepository('SieAppWebBundle:Estudiante')->findByCodigoRude($arrDataInfo['rude']);

              //$em->remove($student);
              //$em->flush();
              if($eliminaRude == 1){
                foreach ($student as $key => $value) {
                  $em->remove($value);
                  $em->flush();
                }
              }
              
              //update the validation process table
              $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($idProceso);
              $vproceso->setEsActivo('t');
              $em->persist($vproceso);
              $em->flush();
              //confirm transaction
              $em->getConnection()->commit();
              //send a message
              $message = 'Eliminación de Inscripción realizado exitosamente';
              $this->addFlash('okchange', $message);
              // (' Eliminacion de Inscripción realizado exitosamente !');
              return new JsonResponse(array('message'=>$message, 'gestionSelected'=>$gestionSelected, 'typeMessage'=>1));

          } catch (Exception $ex) {
              $em->getConnection()->rollback();
              $message = "Proceso detenido! se ha detectado inconsistencia de datos.";
              $this->session->getFlashBag()->add('notihistory', $ex->getMessage());
              //return $this->redirectToRoute('history_inscription_quest', array('rude' => $form['codigoRude']));
          }
      }

}
