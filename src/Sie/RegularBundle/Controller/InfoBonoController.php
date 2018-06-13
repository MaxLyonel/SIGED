<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Symfony\Component\HttpFoundation\Session\Session;


class InfoBonoController extends Controller{

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
                $rude = trim($form['codigoRudeHistory']);
                //get the result of search
                $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));
                //verificamos si existe el estudiante y si es menor a 15
                if ($student) {
                    $query = $em->getConnection()->prepare("select * from get_estudiante_historial_json('" . $rude . "');");
                    $query->execute();
                    $dataInscriptionJson = $query->fetchAll();
                    //dump($dataInscription);

                    foreach ($dataInscriptionJson as $key => $inscription) {
                      # code...
                      $dataInscription [] = json_decode($inscription['get_estudiante_historial_json'],true);
                    }
                    //$dataInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getInscriptionHistoryEstudenWhitObservation($form['codigoRude']);
                    $sw = true;
                }else{
                  //check if the result has some value
                  $message = 'Estudiante con rude ' . $rude . ' no se presenta registro de inscripciones';
                  $this->addFlash('notihistory', $message);
                  $sw = false;
                  return $this->redirectToRoute('info_bono_index');
                }
            }
            //dump($dataInscription);die;
            return $this->render($this->session->get('pathSystem') . ':InfoBono:index.html.twig', array(
                        'form' => $this->createSearchForm()->createView(),
                        'datastudent' => $student,
                        'dataInscription' => $dataInscription,
                        'sw' => $sw
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
                    ->setAction($this->generateUrl('info_bono_index'))
                    ->add('codigoRudeHistory', 'text', array('label' => 'Rude', 'invalid_message' => 'campo obligatorio', 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 20, 'required' => true, 'class' => 'form-control')))
                    ->add('buscar', 'submit', array('label' => 'Buscar'))
                    ->getForm();
            return $form;
        }

         /**
         * Recive Rude to history inscripction
         *
         */
        public function questAction($rude) {
            //die('krlos');
            $em = $this->getDoctrine()->getManager();
            //check if the user is logged
            $id_usuario = $this->session->get('userId');
            if (!isset($id_usuario)) {
                return $this->redirect($this->generateUrl('login'));
            }
            //set the student and inscriptions data
            $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));
            //verificamos si existe el estudiante y si es menor a 15
            if ($student) {
                $dataInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getInscriptionHistoryEstudenWhitObservation($rude);
            }
            $sw = true;
            return $this->render($this->session->get('pathSystem') . ':InfoBono:index.html.twig', array(
                        'form' => $this->createSearchForm()->createView(),
                        'datastudent' => $student,
                        'dataInscription' => $dataInscription,
                        'sw' => $sw
            ));
        }

      public function getreportCalificationsAction(Request $request){
        //get the id inscription
        $studentInscription = $request->get('inscripcionid');
        $studentid = $request->get('studentid');
        //create db conexionn
        $em = $this->getDoctrine()->getManager();

        //get the info about notas
        $objCalifications = $this->get('seguimiento')->getReportCalificationsByStudentInscription($studentInscription);
        // dump($objCalifications);die;
        if($objCalifications){

          //create count to do the operations
          $countBim = 1;
          $countTrim = 1;
          $arrNotasBim = array();
          $arrNotasTrim = array();
          //build the bim & trim array 
          foreach ($objCalifications as $key => $value) {
            
            if('b'.$countBim == $key){
              $arrNotasBim['Bimestre '.$countBim] = $value;
              $countBim++;
            }
            if('t'.$countTrim == $key){
              $arrNotasTrim['Trimestre '.$countTrim] = $value;
              $countTrim++;
            }

          
          }

          //validate if exist bim & trim
          if(!(array_sum($arrNotasBim)>0)){
            $arrNotasBim = false;
          }
          if(!(array_sum($arrNotasTrim)>0)){
            $arrNotasTrim = false;
          }

        }else{
           $arrNotasBim = false;
           $arrNotasTrim = false;
        }
        

          $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($studentid);
          $objStudentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription);
          // dump($objStudentInscription);die;


          return $this->render($this->session->get('pathSystem') . ':InfoBono:getreportCalifications.html.twig', array(
            'student'            => $objStudent,
            'studentInscription' => $objStudentInscription,
            'notasBim'           => $arrNotasBim,
            'notasTrim'          => $arrNotasTrim
                      
          ));
      }


}
