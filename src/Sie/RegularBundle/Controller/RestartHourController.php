<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;

use Sie\AppWebBundle\Entity\Estudiante;
use Symfony\Component\HttpFoundation\Session\Session;


class RestartHourController extends Controller{

  private $session;

  /**
   * the class constructor
   */
  public function __construct() {
      $this->session = new Session();
  }

    public function indexAction(){


      $em = $this->getDoctrine()->getManager();
      //check if the user is logged
      $id_usuario = $this->session->get('userId');
      if (!isset($id_usuario)) {
          return $this->redirect($this->generateUrl('login'));
      }

      //dump($dataInscription);die;
      return $this->render($this->session->get('pathSystem') . ':RestartHour:index.html.twig', array(
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
        $form = $this->createFormBuilder()
                // ->setAction($this->generateUrl('history_new_inscription_index'))
                ->add('codigoRude', 'text', array('label' => 'Rude', 'invalid_message' => 'campo obligatorio', 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 20, 'required' => true, 'class' => 'form-control')))
                ->add('buscar', 'button', array('label' => 'Buscar', 'attr'=> array('onclick'=>'lookforrestart()')))
                ->getForm();
        return $form;
    }

    public function lookforRestartAction(Request $request) {
      // create db conexion
      $em = $this->getDoctrine()->getManager();
      // get the values send
      $form = $request->get('form');
      // $objStudent = array();
      $objStudentInscription = array();
      $objHoras = array();
      $swStudent = false;
      $inscriptionId = false;
      // look for the student
      $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['codigoRude']));
      if($objStudent){
        // look for student inscription
        $objStudentInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getDataStudentToRestart($objStudent->getCodigoRude(), $this->session->get('currentyear'));
        // look for the horas
        $objHoras = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array(
          'estudianteInscripcion'=> $objStudentInscription[0]['studentInscId']
        ));
        if($objHoras){
          $inscriptionId = $objStudentInscription[0]['studentInscId'];
          $swStudent = true;
        }else {
          // there is no hour
          $swStudent = false;
        }

      }else {
        // no student
        $swStudent = false;
      }
      // dump(base64_encode($objStudentInscription[0]['studentInscId']) );die;
      return $this->render($this->session->get('pathSystem') . ':RestartHour:lookforRestart.html.twig', array(
                  'objStudentInscription' => $objStudentInscription[0],
                  'objHoras' => $objHoras,
                  'inscriptionId' => base64_encode($inscriptionId),
                  'sw' => $swStudent
      ));
        return $this->render('SieRegularBundle:RestartHour:lookforRestart.html.twig', array(
                // ...
            ));    }

    public function restartAction(Request $request){
      //get values send
      $inscriptionId = $request->get('inscriptionId');
      $inscriptionId = base64_decode($inscriptionId);
      $done = false;
      // create DB conexion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      try {
        $objHoras = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array(
          'estudianteInscripcion'=> $inscriptionId
        ));
        $objHoras->setHoras(0);
        $objHoras->setEsvalido('f');
        $em->flush();
        // Try and commit the transaction
        $em->getConnection()->commit();
        $done = true;
      } catch (\Exception $e) {
        $em->getConnection()->rollback();
        $message = "Proceso detenido! Se ha detectado inconsistencia de datos. \n".$e->getMessage();
        $this->addFlash('warningremoveins', $message);
        $done = false;
        // return $this->redirectToRoute('restart_hour_index');
      }

      // dump($inscriptionId);die;
      return $this->render('SieRegularBundle:RestartHour:restart.html.twig', array(
        'done' => $done
          // ...
      ));
    }

}
