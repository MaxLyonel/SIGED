<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\Estudiante;
use Symfony\Component\HttpFoundation\Session\Session;


class UpdateEstudentController  extends Controller{

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
      return $this->render($this->session->get('pathSystem') . ':UpdateEstudent:index.html.twig', array(
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
      $objStudent = array();
      $formData = array();
      $swStudent = false;
      $studentId = false;
      // look for the student
      $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['codigoRude']));
      // dump($objStudent->getLocalidadNac());
      // dump($objStudent->getCarnetIdentidad());
      // dump($objStudent->getComplemento());
      // die;
      if($objStudent){

          $swStudent = true;
          $formData = $this->createFormData($objStudent)->createView();
          $studentId = $objStudent->getId();

      }else {
        // no student
        $swStudent = false;
      }
      // dump(base64_encode($objStudentInscription[0]['studentInscId']) );die;
      return $this->render($this->session->get('pathSystem') . ':UpdateEstudent:lookforRestart.html.twig', array(
                  'objStudent'=> $objStudent,
                  'form' => $formData,
                  'studentId' => $studentId,
                  'sw'   => $swStudent
      ));
        // return $this->render('SieRegularBundle:UpdateEstudent:lookforRestart.html.twig', array(
        //         // ...
        //     ));
      }

      /**
       * Creates a form to search the users of student selected
       *
       * @param mixed $id The entity id
       *
       * @return \Symfony\Component\Form\Form The form
       */
      private function createFormData($data) {

          $form = $this->createFormBuilder()
                  // ->setAction($this->generateUrl('history_new_inscription_index'))
                  // ->add('codigoRude', 'text', array('label' => 'Rude', 'invalid_message' => 'campo obligatorio', 'attr' => array('style' => 'text-transform:uppercase', 'maxlength' => 20, 'required' => true, 'class' => 'form-control')))
                   ->add('LocalidadNac', 'text', array('label' => 'Localidad', 'data'=>$data->getLocalidadNac(),'disabled' => ($data->getLocalidadNac())?true:false, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '100', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                   ->add('CarnetIdentidad', 'text', array('label' => 'Carnet', 'data'=>$data->getCarnetIdentidad(), 'disabled'=> ($data->getCarnetIdentidad())?true:false,'attr' => array('class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '10', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                   ->add('Complemento', 'text', array('label' => 'Complemento', 'data'=>$data->getComplemento(), 'disabled'=>($data->getComplemento())?true:false,'attr' => array('class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '2', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                   ->add('studentid', 'hidden', array('data' => $data->getId()))
                   ->add('paterno', 'hidden', array('data' => $data->getPaterno()))
                   ->add('materno', 'hidden', array('data' => $data->getMaterno()))
                   ->add('nombre', 'hidden', array('data' => $data->getNombre()))
                   ->add('fechaNacimiento', 'hidden', array('data' => $data->getFechaNacimiento()->format('d-m-Y')))
                   ->add('ci', 'hidden', array('data' => $data->getCarnetIdentidad()))
                   ->add('complemento', 'hidden', array('data' => $data->getComplemento()))
                   ->add('id', 'hidden', array('data' => $data->getId()))
                  ->add('save', 'button', array('label' => 'Guardar', 'attr'=> array('class'=>'btn btn-success','onclick'=>'resetData()')))
                  ->getForm();
          return $form;
      }

      /**
    * check the student info with the segip service
    **/
    private function saveResultSegipService($form){
        //create db conexion
        $em = $this->getDoctrine()->getManager();
        
        $answerSegip=2;
        // chec if the student has CI-COMPLEMENTO to do the validation
        if($form['ci']){

            $answerSegip = $this->get('sie_app_web.segip')->verificarPersona(
                $form['ci'],
                $form['complemento'],
                $form['paterno'],
                $form['materno'],
                $form['nombre'],
                $form['fechaNacimiento'],
                'prod', 'academico');
            
        }
    
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['id']);
        if($answerSegip===true){
            $student->setSegipId(20);       
        }else{
            $student->setSegipId(0);       
        }
        $em->flush();
        return $answerSegip;
        return $answerSegip;
    }

    public function restartAction(Request $request){

      //get values send
      $form = $request->get('form');
      $form['ci'] = isset($form['CarnetIdentidad'])?$form['CarnetIdentidad']:$form['ci'];
      $form['complemento'] = isset($form['Complemento'])?$form['Complemento']:$form['complemento'];
      // dump($form);
      $resultSegip = $this->saveResultSegipService($form);

// dump($resultSegip);
// die;
      $done = false;
      // create DB conexion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      $mainMessage='';
      try {
        if($resultSegip || $resultSegip == 2){
            $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($form['studentid']);
            reset($form);

            while($val = current($form)){
              if(key($form) == 'studentid' || key($form) == '_token'){
              }else {
                switch (key($form)) {
                  case 'LocalidadNac':
                      $objStudent->setLocalidadNac($val);
                    break;
                  case 'CarnetIdentidad':
                    $objStudent->setCarnetIdentidad($val);
                    break;
                  case 'Complemento':
                    $objStudent->setComplemento($val);
                    break;

                  default:
                    # code...
                    break;
                }
              }
              next($form);
            }

            if($resultSegip == 1){
                $updateMessage = 'Datos Modificados Correctamente - validados con SEGIP';    
            }else{
                $updateMessage = 'Datos Modificados Correctamente';    
            }
            $typeMessage = 'success';
            $mainMessage = 'Guardado';


            $em->flush();
          }else{

            $updateMessage = 'Actualizacion no realizada, los datos reportados no coinciden';
            $typeMessage = 'warning';
            $mainMessage = 'Error';

          }
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
      return $this->render('SieRegularBundle:UpdateEstudent:restart.html.twig', array(
        'done' => $done,
        'updateMessage' => $updateMessage,
        'typeMessage' => $typeMessage,
        'mainMessage' => $mainMessage,
          // ...
      ));
    }

}
