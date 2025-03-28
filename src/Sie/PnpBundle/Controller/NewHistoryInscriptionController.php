<?php

namespace Sie\PnpBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * NewHistoryInscription controller.
 *
 */
class NewHistoryInscriptionController extends Controller {

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
        $dataInscriptionR = array();
        $dataInscriptionA = array();
        $dataInscriptionE = array();
        $dataInscriptionP = array();
        $sw = false;
        if ($request->get('form_est')) {
            //get the form to send
            $form = $request->get('form_est');
            $rude = trim($form['codigoRudeHistory']);
            //get the result of search 
            $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));
            if (!$student){
              $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('carnetIdentidad' => $rude));
              if($student){
                $rude=$student->getCodigoRude();
              }
            }
            //verificamos si existe el estudiante
            if ($student) {
                $query = $em->getConnection()->prepare("select * from sp_genera_estudiante_historial('" . $rude . "') order by gestion_tipo_id_raep desc, estudiante_inscripcion_id_raep desc;");
                $query->execute();
                
                $dataInscription = $query->fetchAll();

                foreach ($dataInscription as $key => $inscription) {
                  switch ($inscription['institucioneducativa_tipo_id_raep']) {
                    case '1':
                      $dataInscriptionR[$key] = $inscription;
                      break;
                    case '2':
                      $dataInscriptionA[$key] = $inscription;
                      break;
                    case '4':
                      $dataInscriptionE[$key] = $inscription;
                      break;
                    case '5':
                      $inscription["institucioneducativa_curso_id_enc_raep"]=$this->encriptar($inscription["institucioneducativa_curso_id_raep"]);
                      $dataInscriptionP[$key] = $inscription;
                      break;
                  }
                }
                $sw = true;
            }else{
              //check if the result has some value
              $message = 'Estudiante con RUDE: ' . $rude . ', no presenta registro de inscripciones';
              $this->addFlash('notihistory', $message);
              $sw = false;
              return $this->redirectToRoute('sie_pnp_buscar_historial_estudiante_rude');
            }
        }
        
        return $this->render($this->session->get('pathSystem') . ':NewHistoryInscription:index.html.twig', array(
                    'form' => $this->createSearchForm()->createView(),
                    'datastudent' => $student,
                    'dataInscriptionR' => $dataInscriptionR,
                    'dataInscriptionA' => $dataInscriptionA,
                    'dataInscriptionE' => $dataInscriptionE,
                    'dataInscriptionP' => $dataInscriptionP,
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
                ->setAction($this->generateUrl('sie_pnp_buscar_historial_estudiante_rude'))
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
        $em = $this->getDoctrine()->getManager();
        //check if the user is logged
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //set the student and inscriptions data

        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));
        if (!$student){
              $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('carnetIdentidad' => $rude));
              if($student){
                $rude=$student->getCodigoRude();
              }
            }

        //verificamos si existe el estudiante
        if ($student) {
            $dataInscription = $em->getRepository('SieAppWebBundle:Estudiante')->getInscriptionHistoryEstudenWhitObservation($rude);
        }
        $sw = true;
        return $this->render($this->session->get('pathSystem') . ':NewHistoryInscription:index.html.twig', array(
                    'form' => $this->createSearchForm()->createView(),
                    'datastudent' => $student,
                    'dataInscription' => $dataInscription,
                    'sw' => $sw
        ));
    }

    public function historypnpAction(Request $request, $idStudent) {
        $em = $this->getDoctrine()->getManager();
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->find($idStudent);

        $query = $em->getConnection()->prepare("select * from sp_genera_estudiante_historial('" . $student->getCodigoRude() . "') order by gestion_tipo_id_raep desc;");
        $query->execute();
        $dataInscription = $query->fetchAll();

        $dataInscriptionR = array();
        $dataInscriptionA = array();
        $dataInscriptionE = array();
        $dataInscriptionP = array();
        foreach ($dataInscription as $key => $inscription) {
            switch ($inscription['institucioneducativa_tipo_id_raep']) {
                case '1':
                    $dataInscriptionR[$key] = $inscription;
                    break;
                case '2':
                    $dataInscriptionA[$key] = $inscription;
                    break;
                case '4':
                    $dataInscriptionE[$key] = $inscription;
                    break;
                case '5':
                    $dataInscriptionP[$key] = $inscription;
                    break;
            }
        }
        $sw = true;
        return $this->render($this->session->get('pathSystem') . ':NewHistoryInscription:index_history.html.twig', array(
            'datastudent' => $student,
            'dataInscriptionR' => $dataInscriptionR,
            'dataInscriptionA' => $dataInscriptionA,
            'dataInscriptionE' => $dataInscriptionE,
            'dataInscriptionP' => $dataInscriptionP,
            'sw' => $sw
        ));
    }
    public function encriptar ($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_','.'),$data);
        return $data; 
    }

    public function desencriptar ($string) {
        $data = str_replace(array('-','_','.'),array('+','/','='),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data); 
    }
}
