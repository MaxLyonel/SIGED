<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
/**
 * DeleteInscriptionQaController Gestión de Menú Controller.
 */
class DeleteInscriptionQaController extends Controller {

    public $session;
    public $idInstitucion;

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
        //look for the rudes
        $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($form['idDetalle']);
        $query = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findBy(array('obs' => $vproceso->getObs(), 'validacionReglaTipo' => $vproceso->getValidacionReglaTipo(), 'gestionTipo' => $form['gestion']));
        //get the rudes
        $rudeOne = $query[0]->getLlave();
        $rudeTwo = $query[1]->getLlave();
        $gestion = $form['gestion'];
        $institucioneducativa = $form['institucioneducativa'];
        $studentOne = $this->getHistoryStudent($rudeOne,$gestion,$institucioneducativa);
        $studentTwo = $this->getHistoryStudent($rudeTwo,$gestion,$institucioneducativa);
        //send the values to the twig
        return $this->render('SieRegularBundle:DeleteInscriptionQa:index.html.twig', array(
          'studentOne' => $studentOne[0],
          'studentTwo' => $studentTwo[0],
          'idDetalle'  => $form['idDetalle']
        ));
    }
    /*
    get the history per student and gestion
    */
    private function getHistoryStudent($rude, $gestion, $institucioneducativa){
      $em = $this->getDoctrine()->getManager();
      $query = $em->getConnection()->prepare("select * from get_estudiante_historial_gestion_json('" . $rude . "','" . $gestion . "','" . $institucioneducativa . "');");
      $query->execute();
      $dataInscriptionJson = $query->fetchAll();
      $dataInscription=array();
      foreach ($dataInscriptionJson as $key => $inscription) {
         # code...
         $dataInscription [] = json_decode($inscription['get_estudiante_historial_gestion_json'],true);
       }
       return $dataInscription;
    }

    public function deleteInscriptionAction(Request $request){

      //create DB conexxion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();

      $valueResp['inscripcionidValido'] = $request->get('inscripcionidValido');
      $valueResp['inscripcionidInValido'] = $request->get('inscripcionidInValido');
      $valueResp['gestion'] = $request->get('gestion');
      $valueResp['matriculaTipo'] = $request->get('matriculaTipo');

      //values to aprobado
      $arrEstados = array(5,26,37,55,56,57,58,11);
      if(!in_array($valueResp['matriculaTipo'], $arrEstados)){
        try {
          //save data in log table
          $this->saveLogDataPerStudent($valueResp, __FUNCTION__);
          //delete the inscription
          $query = $em->getConnection()->prepare("select * from sp_sist_calidad_elim_inscripcion('" . $valueResp['inscripcionidValido'] . "','" . $valueResp['inscripcionidInValido'] . "');");
          $query->execute();
          //update the validation
          $vproceso = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneById($request->get('idDetalle'));
          $vproceso->setEsActivo('t');
          $em->persist($vproceso);
          $em->flush();

          // Try and commit the transaction
          $em->getConnection()->commit();
          $message = 'Se realizó la validación correctamente ';
          $typeMessage = 'success';
        } catch (Exception $e) {
          $em->getConnection()->rollback();
          echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
      }else{
        $message = 'No se puede proceder, el estudiante tiene estado promovido';
        $typeMessage = 'warning';
      }
      //submit the answer of process
      $this->addFlash($typeMessage, $message);
      return new JsonResponse(array('mensaje'=>$message, 'typeMessage'=>$typeMessage));
    }

    private function saveLogDataPerStudent($valueResp, $theFunction){
      //instanse function from defaultController
      $defaultController = new DefaultCont();
      $defaultController->setContainer($this->container);

      //ge the student info
      $arrValNew = $this->getDataLogSave($valueResp['inscripcionidValido']);
      $arrValOld = $this->getDataLogSave($valueResp['inscripcionidInValido']);

      $resp = $defaultController->setLogTransaccion(
          $valueResp['inscripcionidValido'],
          'EstudianteInscripcion',
          'U',
          json_encode(array('browser' => $_SERVER['HTTP_USER_AGENT'],'ip'=>$_SERVER['REMOTE_ADDR'])),
          $this->session->get('userId'),
          '',
          json_encode($arrValNew),
          json_encode($arrValOld),
          'SIGED',
          json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => $theFunction ))
      );
    }
    /*
    save info to save in log transaccion table
    */
    private function getDataLogSave($idInscription){
      $em = $this->getDoctrine()->getManager();
      $objInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscription);
      $arrResult['Id'] = $objInscription->getId();
      $arrResult['matriculaTipo'] = $objInscription->getEstadomatriculaTipo()->getId();
      $arrResult['institucioneducativaCurso'] = $objInscription->getInstitucioneducativaCurso()->getId();
      //$arrResult['estadomatriculaTipo'] = $objInscription->getEstadomatriculaInicioTipo()->getId();
      return $arrResult;

    }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


}
