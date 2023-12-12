<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Modals\Login;
use \Sie\AppWebBundle\Entity\Usuario;
use \Sie\AppWebBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class NoteConsultationUesController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * note consultation parents Index
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':NoteConsultationUes:index.html.twig', array(
                    'form' => $this->craeteformsearch()->createView()
        ));
    }
    /**
     * [craeteformsearch description]
     * @return [type] [description]
     */
    private function craeteformsearch() {

        //set new gestion to the select year
        $arrGestion = array();
        $currentYear = date('Y');
        // for ($i = 0; $i <= 12; $i++) {
        //     $arrGestion[$currentYear] = $currentYear;
        //     $currentYear--;
        // }

        //dump(($currentYear - 2009));die;

        for ($i = $currentYear; $i >= 2009; $i--) {
          $arrGestion[$i] = $i;
        }

        return $this->createFormBuilder()
        //->setAction($this->generateUrl('remove_inscription_sie_index'))
        ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{3,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
        ->add('gestion', 'choice', array('label' => 'Gestión', 'choices' => $arrGestion, 'attr' => array('class' => 'form-control')))
        ->add('search', 'button', array('label' => 'Buscar', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'findInscription()')))
        ->getForm();
    }

    /**
     * find the courser per sie
     * @param Request $request
     * @return type the list of student and inscripion data
     */
    public function resultAction(Request $request) {
        //get the value to send
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        //find the UE
        $em = $this->getDoctrine()->getManager();
        $objUe = array();
        $objCourses = array();
        $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);
        $exist = true;
        $arrValidation = array();
        $arrObservations = array();

         //check if the data exist
        if ($objUe) {

          if($gestion >= 2019 ) {
              // get infor about the consolidation  by YEAR and SIE
              $infoConsolidation = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('gestion' => $gestion, 'unidadEducativa' => $sie));
              
              
              if($infoConsolidation ){
                    // check if the ue close the operativo
                  $operativo = $this->get('funciones')->obtenerOperativo($sie, $gestion);
                  // if(in_array($this->session->get('roluser'), array(7,8,10)) ){
                  //     $operativo = $operativo - 1;
                  // }
                  if($gestion >= 2020){
                    if($operativo <= 2){
                      $message = 'Unidad Educativa no cerro el operativo 3er trimestre';
                      $this->addFlash('warningconsultaue', $message);
                      $exist = false;
                    }
                  } else {
                    if($operativo <= 2){
                      $message = 'Unidad Educativa no cerro el operativo 3er Trim';
                      $this->addFlash('warningconsultaue', $message);
                      $exist = false;
                    }
                  }
                  
                      /***********************************\
                      * *
                      * Validacion tipo de Unidad Educativa
                      * send codigo sie *
                      * return type of UE *
                      * *
                      \************************************/
                      $objUeVal = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($sie);
                      
                      if($objUeVal[0]['tipoUe']!=1){
                          $message = 'Unidad Educativa no pertenece al sistema de Educación  Regular';
                            $this->addFlash('warningconsultaue', $message);
                            $exist = false;
                      }
                      
                      // added new validation to download the reports files
                      // validation UE QA
                        // $query = $em->getConnection()->prepare('select * from sp_verificar_duplicados_ue(:gestion, :sie)');
                        // $query->bindValue(':gestion', $gestion);
                        // $query->bindValue(':sie', $sie);
                        // $query->execute();
                        // $inconsistenciaReviewQa = $query->fetchAll();  

                        //  valiation IG off
                      if($gestion == $this->session->get('currentyear') ) {
                        //first validations calidad
                        /***********************************\
                        * *
                        * validatin of QA
                        * send array => sie, gestion, reglas *
                        * return observations UE *
                        * *
                        \************************************/          
                        //the rule to donwload file with validations\
                        $arrDataVal = array(
                          'sie' => $sie,
                          'gestion' => $gestion,
                          'reglas' => '2,3,6,8,10,11,12,13,15,16,20,24,25,26'
                        );//'1,2,3,4,5,6,7,8,10,11,12,13,16,20,27,37,48'
                      
                        // $objObsQA = $this->get('funciones')->appValidationQuality($arrDataVal);
                        $objObsQA = array();
                        
                        // dump($objObsQA);die;
                        if ($objObsQA) {  
                            $message = 'Unidad Educativa  presenta observaciones de calidad' ;
                            $this->addFlash('warningconsultaue', $message);
                            $exist = false;
                            $arrValidation['observaciones_calidad'] = $objObsQA;                    
                        }

                        // validation UE data
                        /***********************************\
                        * *
                        * Validacion Unidades Educativas: MODULAR, PLENAS,TEC-TEG, NOCTURNAS
                        * send array => sie, gestion, reglas *
                        * return type of UE *
                          * *
                        \************************************/
                        if($gestion == 2020) {
                          $query = $em->getConnection()->prepare('select * from sp_validacion_regular_web_2020(:gestion, :sie, :periodo)');
                          $query->bindValue(':gestion', $gestion);
                          $query->bindValue(':sie', $sie);
                          $query->bindValue(':periodo', 1);
                          $query->execute();
                          $inconsistencia = $query->fetchAll();
                        } elseif($gestion == 2021){
                            $valor_op=array('0'=>6,'1'=>6,'2'=>7,'3'=>8);

                            $queryCheckCal = 'select * from sp_validacion_regular_web2021_fg(:gestion,:sie,:ope)';
                            $query = $em->getConnection()->prepare($queryCheckCal);
                            $query->bindValue(':gestion', $gestion);
                            $query->bindValue(':sie', $sie);
                            $query->bindValue(':ope', $valor_op[$operativo]);
                            $query->execute();
                            $inconsistencia = $query->fetchAll();                            
                        } elseif($gestion == 2022){
                          
                          if(in_array($this->session->get('roluser'), array(9)) ){
                                 $operativo = $operativo - 1;
                          }
                          $valor_op=array('0'=>6,'1'=>6,'2'=>7,'3'=>8);
                          
                          $queryCheckCal = 'select * from sp_validacion_regular_web2022_fg(:gestion,:sie,:ope)';
                          $query = $em->getConnection()->prepare($queryCheckCal);
                          $query->bindValue(':gestion', $gestion);
                          $query->bindValue(':sie', $sie);
                          $query->bindValue(':ope', $valor_op[$operativo]);
                          $query->execute();
                          $inconsistencia = $query->fetchAll(); 
                        } elseif($gestion == 2023){
                          
                          // if(in_array($this->session->get('roluser'), array(9)) ){
                          //        $operativo = $operativo - 1;
                          // }
                          // $valor_op=array('0'=>6,'1'=>6,'2'=>7,'3'=>8);
                          
                          // $queryCheckCal = 'select * from sp_validacion_regular_web2023_fg(:gestion,:sie,:ope)';
                          // $query = $em->getConnection()->prepare($queryCheckCal);
                          // $query->bindValue(':gestion', $gestion);
                          // $query->bindValue(':sie', $sie);
                          // $query->bindValue(':ope', $valor_op[$operativo]);
                          // $query->execute();
                          // $inconsistencia = $query->fetchAll();
                          $inconsistencia = null; 
                        } else {                            
                            $query = $em->getConnection()->prepare('select * from sp_validacion_regular_web(:gestion, :sie, :periodo)');
                            $query->bindValue(':gestion', $gestion);
                            $query->bindValue(':sie', $sie);
                            $query->bindValue(':periodo', 4);
                            $query->execute();
                            $inconsistencia = $query->fetchAll();
                        }
                        if ($inconsistencia) {
                          $message = 'Unidad Educativa presenta observaciones de inconsistencia';
                          $this->addFlash('warningconsultaue', $message);
                          $exist = false;
                          $arrValidation['observaciones_incosistencia'] = $inconsistencia;                        
                        } 

                      // this for the current year and close this task
                      if($gestion == $this->session->get('currentyear')-1){
                        // check if the UE close the RUDE task
                        if(!$infoConsolidation->getRude()){
                          $message = 'Unidad Educativa no consolido el operativo RUDE';
                          $this->addFlash('warningconsultaue', $message);
                          $exist = false;
                        }
                        // check if the UE close the boletin
                        if(!$infoConsolidation->getBoletin()){
                          $message = 'Unidad Educativa aún tiene observaciones en control de CALIDAD';
                          $this->addFlash('warningconsultaue', $message);
                          $exist = false;
                        }

                      }
                    }

              }else{
                $message = 'Unidad Educativa no cuenta con información de consolidación';
                $this->addFlash('warningconsultaue', $message);
                $exist = false;
              }

        }//end new validation >=2019


          // if does not have observation show the course data
          if($exist){
            //look for inscription data
            $objCourses = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getCoursesPerUe($sie, $gestion);
            //check if exists data
            if (!$objCourses){
                $message = 'Unidad Educativa no presenta Cursos';
                $this->addFlash('warningconsultaue', $message);
                $exist = false;
            }else{
              $data = array(
                  'operativoTipo' => 7,
                  'gestion' => $gestion,
                  'id' => $sie,
              );   
              $operativo = $this->get('funciones')->saveDataInstitucioneducativaOperativoLog($data);
              $em->getConnection()->beginTransaction();
              try{
                  $registroConsol = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array('unidadEducativa' => $sie, 'gestion' => $gestion));
                  if (!$registroConsol){
                    if($gestion > 2013){
                      $message = 'Unidad Educativa no consolido su informacion en la gestion indicada';
                      $this->addFlash('warningconsultaue', $message);
                      $exist = false;
                    } 
                  } else {
                    $registroConsol->setFecha(new \DateTime("now"));
                    $registroConsol->setBoletin('1');
                    $em->persist($registroConsol);
                    $em->flush();
                    $em->getConnection()->commit();
                  }                  
              } catch (Exception $e) {
              $em->getConnection()->rollback();
              }
            }
            
          }

        } else {
            $message = 'Unidad Educativa no Existe';
            $this->addFlash('warningconsultaue', $message);
            $exist = false;
        }
        
        return $this->render($this->session->get('pathSystem') . ':NoteConsultationUes:result.html.twig', array(
                    'unidadEducativa' => $objUe,
                    'courses' => $objCourses,
                    'exist' => $exist,
                    'gestionSelected' => $gestion,
                    'arrValidation' => $arrValidation
        ));
    }

}
