<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\BthEstudianteNivelacion;
// use Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo;

/**
 * Author: krlos Pacha C. <pckrlos@cgmail.com>
 * Description: This is a class for reporting the student nivelation; if exist the correct permissions
 * Date: 23-11-2018
 *
 *
 * class: RegularizationCUTController
 *
 * Email bugs/suggestions to pckrlos@cgmail.com
 */
class RegularizationCUTController extends Controller{
    
    private $session;
    
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        
    }

    /**
     * Function index
     *
     * @author krlos Pacha C. <pckrlos@cgmail.com>
     * @access public
     * @param void
     * @return form
     */
    public function indexAction(){
        // create varialbes session
        $id_usuario = $this->session->get('userId');
        // create db conexion
        $em = $this->getDoctrine()->getManager();

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SieHerramientaBundle:RegularizationCUT:index.html.twig', array(
            'form' => $this->createSearForm()->createView(),
                // ...
            ));    

    }

     /**
     * Function createSearForm
     *
     * @author krlos Pacha C. <pckrlos@cgmail.com>
     * @access private
     * @param void
     * @return form
     */
    private function createSearForm(){
        return $this->createFormBuilder()
            ->add('codigoRude', 'text', array('mapped' => false, 'label' => 'Rude', 'required' => true, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9a-zA-Z\sñÑ]{10,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'choice', array('mapped' => false, 'label' => 'Gestion', 'choices' => array($this->session->get('currentyear')=>$this->session->get('currentyear')), 'attr' => array('class' => 'form-control')))
                ->add('buscar', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary', 'onclick'=>'gethistory();')))
                ->getForm();
    }


    /**
     * Function historyAction
     *
     * @author krlos Pacha C. <pckrlos@cgmail.com>
     * @access public
     * @param string codigoRude
     * @return form
     */
    public function historyAction(Request $request){
        
        //get send data
        $form = $request->get('form');
        if($form['codigoRude']){
            return $this->redirectToRoute('regularizacion_cut_listHistory', array('codigoRude'=> base64_encode($form['codigoRude']) ));
        }else {
            return $this->redirectToRoute('regularizacion_cut_index');
        }
        //create db conexion
        
    }

    public function listHistoryAction(Request $request, $codigoRude){

        $em = $this->getDoctrine()->getManager();
        $swError = true;

        $form['codigoRude'] = base64_decode($codigoRude,true);
        // dump($form['codigoRude']);
        // die;
        // check if the student exists
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['codigoRude']));        
        if($objStudent){
            // check if the student is in 6to
            $dataStudentSixth = $this->get('funciones')->getInscriptionBthByRude($objStudent->getCodigoRude());
            if($dataStudentSixth){
                    // check if the user has permissions
                    $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
                    $query->bindValue(':user_id', $this->session->get('userId'));
                    $query->bindValue(':sie', $dataStudentSixth[0]['sie']);
                    $query->bindValue(':rolId', $this->session->get('roluser'));
                    $query->execute();
                    $arrTuicion = $query->fetchAll();
                    // check if the user has permissions on the student
                    if ($arrTuicion[0]['get_ue_tuicion']) {

                           if(sizeof($dataStudentSixth)>0){
                                // check if the student is not avalible to BTH
                                $objInscriptionTecnicoHumanistico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array(
                                  'estudianteInscripcion' => $dataStudentSixth[0]['estInsId']
                              ));
                                if($objInscriptionTecnicoHumanistico && $objInscriptionTecnicoHumanistico->getEsValido()==false){
                                    
                                }else{
                                     $message = 'Estudiante habilitado para la descarga del CUT';
                                    $this->addFlash('warningReg', $message);
                                    $swError = false;
                                }

                            }else{
                                $message = 'El Estudiante no esta en el nivel requerido';
                                $this->addFlash('warningReg', $message);
                                $swError = false;

                            }

                    }else{
                         $message = 'No tiene tuición para continuar con el ragistro';
                        $this->addFlash('warningReg', $message);
                        $swError = false;

                    }

            }else{
                $message = 'Estudiante no se encuentra en 6to de Seccundaria';
                $this->addFlash('warningReg', $message);
                $swError = false;
            }
            

        }else{
            $message = 'No existe Estudiante';
            $this->addFlash('warningReg', $message);
            $swError = false;

        }
        // dump($objStudent);
        // dump($form);die;


        //get current inscription
        $historyStudentInscription = $this->get('funciones')->getCurrentInscriptionStudentByRude($form);
        
        return $this->render('SieHerramientaBundle:RegularizationCUT:history.html.twig', array(
            'arrhistory' => $historyStudentInscription,
            'student' => $objStudent,
            'swError' => $swError,
                // ...
            ));    

    }

     /**
     * Function regularizationAction
     *
     * @author krlos Pacha C. <pckrlos@cgmail.com>
     * @access public
     * @param int estInsId
     * @return form
     */
    public function regularizationAction(Request  $request){
        //get send values
        $estInsId = $request->get('estInsId');
        // dump($estInsId);
        $arrSelectStudentInscription = $this->get('funciones')->getSelectStudentInscription($estInsId);
        // dump($arrSelectStudentInscription);
        // die;
        // dump(sizeof($arrSelectStudentInscription) );
        if(sizeof($arrSelectStudentInscription) > 0 ){

        }else{
            dump('no cuenta con historial');
        }

        // die;
        return $this->render('SieHerramientaBundle:RegularizationCUT:regularization.html.twig', array(
            'form' => $this->createNivelationForm($arrSelectStudentInscription[0])->createView(),
            'dataregularization' => $arrSelectStudentInscription[0]
                // ...
        ));    
    }

     /**
     * Function createNivelationForm
     *
     * @author krlos Pacha C. <pckrlos@cgmail.com>
     * @access private
     * @param  array data
     * @return form
     */
    private function createNivelationForm($data){
        $arrTypeNivelation = array('test1', 'test2');
        $form =  $this->createFormBuilder()
                ->add('data', 'hidden', array('mapped' => false,'data' => json_encode($data) , 'attr' => array()))
                ->add('typeNivelation', 'choice', array('label'=>'Tipo de Nivelación','choices'=>$arrTypeNivelation ,'attr'=>array('class'=>'form-control input-sm')))
                ->add('nota', 'text', array('label' => 'Nota', 'attr'=>array('class'=>'form-control input-sm')))
                ;
        if($data['nivelId']==13 && $data['gradoId']>4){
            //get all speciality 
            $resultSpeciality = $this->get('funciones')->getSpeciality();
            $arrSpeciality = array();
            foreach ($resultSpeciality as $value) {
                
                $arrSpeciality[$value->getId()] = strtoupper($value->getEspecialidad()) ;
            }
            //set the asignatura tipo
            $asignaturaTipoId = 1039;
           
        }else{
            $arrSpeciality[99] = 'NO APLICA';
            //set the asignatura tipo
            $asignaturaTipoId = 1038;
        }

         $form = $form
                 ->add('speciality', 'choice', array('label'=>'Especialidad','choices'=>$arrSpeciality ,'attr'=>array('class'=>'form-control input-sm')))
                 ->add('asignaturaTipo', 'hidden', array('mapped' => false,'data' => $asignaturaTipoId , 'attr' => array()))
                 ;

        $form = $form 
                
                ->add('register', 'button', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-info', 'onclick'=>'registerNivelation();')))
                ->getForm()
                ;
        // dump($data);die;
        return $form;
    }

    public function registerNivelationAction(Request $request){

        //get send values
        $form  = $request->get('form');
        // get data student
        $dataStudent  = json_decode($form['data'],true);
        dump($dataStudent);

        try {

            // create db conexion
        $em = $this->getDoctrine()->getManager();

        $objRegister = new BthEstudianteNivelacion();
        $objRegister->setNotaCuantitativa($form['nota']);
        $objRegister->setDocRespaldo($form['typeNivelation']);
        $objRegister->setFechaRegistro(new \DateTime('now'));
        $objRegister->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($dataStudent['estInsId']));
        $objRegister->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->find($dataStudent['nivelId']));
        $objRegister->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($dataStudent['gradoId']));
        $objRegister->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(5));
        $objRegister->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->find($this->session->get('userId')));
        $objRegister->setAsignatura($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find($form['asignaturaTipo']));
        $objRegister->setEspecialidadTecnicoHumanistico($em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($form['speciality']));
        $em->persist($objRegister);
        $em->flush();

        dump($form);

        die;
            
        } catch (Exception $e) {
            
        }


        
    }

}
