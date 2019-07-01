<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\BthEstudianteNivelacion;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
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
        // array($this->session->get('currentyear')=>$this->session->get('currentyear'))
        return $this->createFormBuilder()
                // ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                ->add('codigoRude', 'text', array('mapped' => false, 'label' => 'Rude', 'required' => true, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9a-zA-Z\sñÑ]{10,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'choice', array('mapped' => false, 'label' => 'Gestion', 'choices' => array(2018=>2018), 'attr' => array('class' => 'form-control')))
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
            return $this->redirectToRoute('regularizacion_cut_listHistory', array('form'=> base64_encode(json_encode($form)) ));
        }else {
            return $this->redirectToRoute('regularizacion_cut_index');
        }
        //create db conexion
        
    }

    public function listHistoryAction(Request $request, $form){

        $em = $this->getDoctrine()->getManager();
        $swError = true;
        // $form['codigoRude'] = trim(base64_decode($codigoRude,true));
        $jsonForm = base64_decode($form ,true);
        $form = json_decode($jsonForm,true);
        // die;
        // check if the student exists
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>trim($form['codigoRude'])));        
        if($objStudent){
            // check if the student is in 6t
            // $dataStudentSixth      = $this->get('funciones')->getInscriptionBthByRude($objStudent->getCodigoRude());
            $objStudentInscription = $this->get('funciones')->getInscriptionBthByGestion($form);

            if($objStudentInscription){

                if($em->getRepository('SieAppWebBundle:BthEstudianteNivelacion')->findOneBy(array('estudianteInscripcion'=>$objStudentInscription[0]['estInsId']))){
                    $message = 'Registro ya realizado para la/el estudiante';
                    $this->addFlash('warningReg', $message);
                    $swError = false;
                }else{
                    // VALIDAR SI LA INSCRIPCION CORRESPONDE A UN SIE AUTORIZADO
                    $sie = $objStudentInscription[0]['sie'];
                    $gestion = $objStudentInscription[0]['gestion'];
                    
                    $sieAutorizado = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array(
                        'institucioneducativaId'=>$sie,
                        'gestionTipoId'=>$gestion,
                        'institucioneducativaHumanisticoTecnicoTipo'=>1, // plena
                        'esimpreso'=>true
                    ));

                    if($sieAutorizado){

                        // check if the user has permissions
                        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
                        $query->bindValue(':user_id', $this->session->get('userId'));
                        $query->bindValue(':sie', $objStudentInscription[0]['sie']);
                        $query->bindValue(':rolId', $this->session->get('roluser'));
                        $query->execute();
                        $arrTuicion = $query->fetchAll();
                        // check if the user has permissions on the student
                        if ($arrTuicion[0]['get_ue_tuicion']) {

                               if(sizeof($objStudentInscription)>0){
                                    // check if the student is not avalible to BTH
                                    $objInscriptionTecnicoHumanistico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array(
                                      'estudianteInscripcion' => $objStudentInscription[0]['estInsId']
                                  ));
                                    // if($objInscriptionTecnicoHumanistico && $objInscriptionTecnicoHumanistico->getEsValido()==false){
                                    if(true){
                                        
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
                        $message = 'El codigo SIE no esta autorizado';
                        $this->addFlash('warningReg', $message);
                        $swError = false;
                    }
                }
            }else{
                $message = 'Estudiante no tiene inscripcion en la gestión seleccionada';
                $this->addFlash('warningReg', $message);
                $swError = false;
            }        

        }else{
            $message = 'No existe Estudiante';
            $this->addFlash('warningReg', $message);
            $swError = false;

        }

        // dump($objStudent);die;
        // dump($form);die;

        if ($swError) {
            //get current inscription
            $historyStudentInscription = $this->get('funciones')->getCurrentInscriptionStudentByRude($form);
            // dump($historyStudentInscription);
            // die;
            return $this->render('SieHerramientaBundle:RegularizationCUT:history.html.twig', array(
                'currentInscription' => $objStudentInscription[0]['estInsId'],
                'currentGestion' => $objStudentInscription[0]['gestion'],
                'currentSie' => $objStudentInscription[0]['sie'],            
                'arrhistory' => $historyStudentInscription,
                'student' => $objStudent,
                'swError' => $swError,            
                    // ...
                ));  
        }

        return $this->render('SieHerramientaBundle:RegularizationCUT:history.html.twig', array(
            'swError' => $swError,            
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
        $inscriptionIdSelected    = $request->get('inscriptionIdSelected');
        $currentInscriptionId     = $request->get('currentInscriptionId');
        $currentGestion           = $request->get('currentGestion');
        $currentSie               = $request->get('currentSie');
        
        // dump($estInsId);
        $arrSelectStudentInscription = $this->get('funciones')->getSelectStudentInscription($inscriptionIdSelected);
        $arrSelectStudentInscription[0]['currentInscriptionId'] = $request->get('currentInscriptionId');
        $arrSelectStudentInscription[0]['currentGestion']       = $request->get('currentGestion');
        $arrSelectStudentInscription[0]['currentSie']           = $request->get('currentSie');
        
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
        
        $arrNota =array();
        $cc=1;
        while ($cc <= 100) {
            $arrNota[$cc]=$cc;
            $cc++;
        }        
        
        $form =  $this->createFormBuilder()
                ->add('data', 'hidden', array('mapped' => false,'data' => json_encode($data) , 'attr' => array()))
                // ->add('typeNivelation', 'choice', array('label'=>'Tipo de Nivelación','choices'=>$arrTypeNivelation ,'attr'=>array('class'=>'form-control input-sm')))
                ->add('nota', 'choice', array('label' => 'Nota','choices'=>$arrNota, 'attr'=>array('maxlength' => 3, 'class'=>'form-control input-sm')))
                ->add('observation', 'textarea', array('label' => 'Observación', 'attr'=>array('class'=>'form-control input-sm')))
                ;
        if($data['nivelId']==13 && $data['gradoId']>4){
            //get all speciality 
            $resultSpeciality = $this->get('funciones')->getSpeciality($data);
            // dump($resultSpeciality);die;
            $arrSpeciality = array();            
            if( $resultSpeciality ){
                foreach ($resultSpeciality as $key => $value) {
                    $arrSpeciality[$value->getId()] = strtoupper($value->getEspecialidad()) ;                
                }
            }else{
                $arrSpeciality[99] = 'NO CUENTA CON ESPECIALIDAD';
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
        $form     = $request->get('form');
        $arrData = json_decode($form['data'],true);
        // dump($form);
        // dump($arrData);
        
        // die;
        // get data student
        $dataStudent  = json_decode($form['data'],true);
        // dump($dataStudent);
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            // save nota
            $objEstudianteNotaCualitativa = new EstudianteNotaCualitativa();
            $objEstudianteNotaCualitativa->setNotaCuantitativa($form['nota']);
            $objEstudianteNotaCualitativa->setUsuarioId($this->session->get('userId'));
            $objEstudianteNotaCualitativa->setFechaRegistro(new \DateTime('now'));
            $objEstudianteNotaCualitativa->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrData['currentInscriptionId']));
            $objEstudianteNotaCualitativa->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(33));
            $em->persist($objEstudianteNotaCualitativa);
            $em->flush();
            // save regularization
            $objRegister = new BthEstudianteNivelacion();        
            $objRegister->setObs($form['observation']);
            $objRegister->setFechaRegistro(new \DateTime('now'));
            $objRegister->setEstudianteNotaCualitativa($em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($objEstudianteNotaCualitativa->getId()));
            $objRegister->setEspecialidadTecnicoHumanisticoTipo($em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($form['speciality']));
            $objRegister->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->find($arrData['gradoId']));
            $objRegister->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find($form['asignaturaTipo']));
            $objRegister->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrData['currentInscriptionId']));
            $em->persist($objRegister);
            $em->flush();

            $em->getConnection()->commit();
            
        } catch (Exception $e) {
            $this->session->getFlashBag()->add('goodregister', 'Se presento algunos problemas...');        
            $em->getConnection()->rollback();
            // echo 'Excepción capturada: ', $ex->getMessage(), "\n"; 
        }

        $this->session->getFlashBag()->add('goodregister', 'Operacion realizada satisfactoriamente...');        
        return $this->render('SieHerramientaBundle:RegularizationCUT:registerNivelation.html.twig', array(
                // ...
        ));    


        
    }

}
