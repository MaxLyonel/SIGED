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
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\DownloadFileControl;
use Sie\AppWebBundle\Entity\InstitucioneducativaControlOperativoMenus;
use Sie\AppWebBundle\Entity\ControlArchivosBajada;
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog;
class DownloadFileSieController extends Controller {

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * donwload sie file
     * @param Request $request
     * @return type
     */
    public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        /* this is for try with this users
        if ($id_usuario=='92506092' || $sesion->get('roluser')==8 || $id_usuario =='92490280') {

        }else{
            return $this->redirect($this->generateUrl('login'));
        }
        */
        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        return $this->render($this->session->get('pathSystem') . ':DownloadFileSie:index.html.twig', array(
                    'form' => $this->craeteformsearchsie()->createView()
        ));
    }

    private function craeteformsearchsie() {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('download_file_sie_build'))
                        ->add('sie', 'text', array('label' => 'SIE', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9\sñÑ]{6,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                        ->add('gestion', 'choice', array('label' => 'Gestión', 'empty_data' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        ->add('bimestre', 'choice', array('label' => 'Bimestre', 'attr' => array('class' => 'form-control', 'empty_data' => 'Seleccionar...')))
                        ->add('search', 'button', array('label' => 'Generar', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'generateFile()')))
                        ->getForm();
    }
    /**
     * find the validation process info
     * @param Request $request
     * @return true or false
     */
    private function getValidationProcess($data){

      $em = $this->getDoctrine()->getManager();
      //get the validation trougth data info
      $objValidationProcess = $em->getRepository('SieAppWebBundle:ValidacionProceso')->getValidationProcessInfo($data);

      if (!$objValidationProcess) {
        $objValidationProcess = false;
      }
      //return get value
      return $objValidationProcess;

    }

    /**
     * find the bachillers per sie
     * @param Request $request
     * @return type the list of bachilleres
     */
    public function buildAction(Request $request) {

        $form['sie'] = $request->get('sie');
        $form['gestion'] = $request->get('gestion');
        $form['bimestre'] = $request->get('bimestre');

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
          // validation UE QA
          $query = $em->getConnection()->prepare('select * from sp_verificar_duplicados_ue(:gestion, :sie)');
          $query->bindValue(':gestion', $form['gestion']);
          $query->bindValue(':sie', $form['sie']);
          $query->execute();
          $inconsistenciaReviewQa = $query->fetchAll();


          
          $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);


            /***********************************\
          * *
          * Validacion Unidades Educativas: MODULAR, PLENAS,TEC-TEG, NOCTURNAS
          * send array => sie, gestion, reglas *
          * return type of UE *
          * *
          \************************************/
          $form['reglasUE'] = '1,2,3,5,7';
          $objAllowUE = $this->getObservationAllowUE($form);

          if ($objAllowUE) {
            $objObservados = array();
            $errorValidation = array('ueobservation'=>false);
            $objObservados = array();
            
            $swCtrlMenu = false;
            // $optionCtrlOpeMenu = $this->setCtrlOpeMenuInfo($form,$swCtrlMenu);
            $em->getConnection()->commit();

            // set the ctrol menu with false
              //get ue data
              return $this->render($this->session->get('pathSystem') . ':DownloadFileSie:fileDownload.html.twig', array(
                          'uEducativa'        => $errorValidation,
                          'objUe'             => $objUe[0],
                          'objinconsistencia' => $objAllowUE[0],
                          'form'              => '',
                          'swvalidation'      => '1',
                          'swinconsistencia'  => '0',
                          'flagValidation'    => '0',
                          'swObservados'      => '',
                          'ueModular'         => '1',
                          'validationPersonal' => '0',
                          'validationRegistroConsolidado' => '0',
                          'sistemaRegular' => '0'
              ));
          }
          

          /***********************************\
          * *
          * Validacion tipo de Unidad Educativa
          * send codigo sie *
          * return type of UE *
          * *
          \************************************/
          if($objUe[0]['tipoUe']!=1){
            $objObservados = array();
            $errorValidation = array('ueobservation'=>false);
            $objObservados = array();
            return $this->render($this->session->get('pathSystem') . ':DownloadFileSie:fileDownload.html.twig', array(
                        'uEducativa' => $errorValidation,
                        'objUe' => $objUe[0],
                        'swvalidation' => '1',
                        'flagValidation' => '0',
                        'swObservados' => '0',
                        'ueModular' => '0',
                        'swinconsistencia'  => '0',
                        'observaciones' => $objObservados,
                        'validationPersonal' => '0',
                        'validationRegistroConsolidado' => '0',
                        'sistemaRegular' => '1'


            ));
          }
          //  valiation IG off
          $errorValidation = $this->validateDownload($form);
          //add validation for bim
          $inconsistencia = array();
          $swCtrlMenu = true;

           //first validations calidad
          /***********************************\
          * *
          * Validacion CONTROL DE CALIDAD
          * send array => sie, gestion, reglas *
          * return observations UE *
          * *
          \************************************/
          $form['reglas'] = '1,2,3,10,12,13,16,27,48';
      
          $objObsQA = $this->get('funciones')->appValidationQuality($form);
          if ($objObsQA) {
            
            $swCtrlMenu = false;
            // set the ctrol menu with false
            // $optionCtrlOpeMenu = $this->setCtrlOpeMenuInfo($form,$swCtrlMenu);
            $em->getConnection()->commit();
              //get ue data
              return $this->render($this->session->get('pathSystem') . ':DownloadFileSie:fileDownload.html.twig', array(
                          'uEducativa'        => $errorValidation,
                          'objUe'             => $objUe[0],
                          'objinconsistencia' => $objObsQA,
                          'form'              => '',
                          'swvalidation'      => '1',
                          'swinconsistencia'  => '0',
                          'flagValidation'    => '0',
                          'swObservados'      => '1',
                          'ueModular'         => '0',
                          'validationPersonal' => '0',
                          'validationRegistroConsolidado' => '0',
                          'sistemaRegular' => '0'
              ));
          }


          $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);
          if($form['bimestre']>=1 && $form['gestion'] == $this->session->get('currentyear')){
          //the rule to donwload file with validations
          // $form['reglas'] = '1,2,3,10,12,13,16,27';

          //first validations calidad
          /***********************************\
          * *
          * Validacion CONTROL DE CALIDAD
          * send array => sie, gestion, reglas *
          * return observations UE *
          * *
          \************************************/
          $objObsQA = $this->get('funciones')->appValidationQuality($form);
          if ($objObsQA) {
            
            $swCtrlMenu = false;
            // set the ctrol menu with false
            // $optionCtrlOpeMenu = $this->setCtrlOpeMenuInfo($form,$swCtrlMenu);
            $em->getConnection()->commit();
              //get ue data
              return $this->render($this->session->get('pathSystem') . ':DownloadFileSie:fileDownload.html.twig', array(
                          'uEducativa'        => $errorValidation,
                          'objUe'             => $objUe[0],
                          'objinconsistencia' => $objObsQA,
                          'form'              => '',
                          'swvalidation'      => '1',
                          'swinconsistencia'  => '0',
                          'flagValidation'    => '0',
                          'swObservados'      => '1',
                          'ueModular'         => '0',
                          'validationPersonal' => '0',
                          'validationRegistroConsolidado' => '0',
                          'sistemaRegular' => '0'
              ));
          }
          //second type of UE
        

          // validation UE data
          /***********************************\
          * *
          * Validacion Unidades Educativas: MODULAR, PLENAS,TEC-TEG, NOCTURNAS
          * send array => sie, gestion, reglas *
          * return type of UE *
            * *
          \************************************/
          $query = $em->getConnection()->prepare('select * from sp_validacion_regular_web(:gestion, :sie, :periodo)');
          $query->bindValue(':gestion', $form['gestion']);
          $query->bindValue(':sie', $form['sie']);
          $query->bindValue(':periodo', $form['bimestre']-1);
          $query->execute();
          $inconsistencia = $query->fetchAll();
          // dump($inconsistencia);die;
          //this is to validate the files download
          //get info if the UE is plena
          // $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);
          // $errorValidation = $this->validateDownload($form);
          if ($inconsistencia) {

            $swCtrlMenu = false;
            // set the ctrol menu with false
            // $optionCtrlOpeMenu = $this->setCtrlOpeMenuInfo($form,$swCtrlMenu);
            $em->getConnection()->commit();

              //get ue data
              $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);
              return $this->render($this->session->get('pathSystem') . ':DownloadFileSie:fileDownload.html.twig', array(
                          'uEducativa'        => $errorValidation,
                          'objUe'             => $objUe[0],
                          'objinconsistencia' => $inconsistencia,
                          'form'              => '',
                          'swvalidation'      => '1',
                          'swinconsistencia'  => '1',
                          'flagValidation'    => '0',
                          'swObservados'      => '0',
                          'ueModular'         => '0',
                          'validationPersonal' => '0',
                          'validationRegistroConsolidado' => '0',
                          'sistemaRegular' => '0'
              ));
          }

            //executa the validation function on db
            // $query = $em->getConnection()->prepare("select * from sp_valida_calidad_curso_oferta_obs1('" . $form['sie'] . "','" . $form['gestion'] ."');");
            // $query->execute();
            // //get the validation info
            // $arrValidationPrecess = $this->getValidationProcess($form);

            /* this is commented cos this is used on 1er bim
            if ($arrValidationPrecess) {
                //go to the validation process page
                $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);
                return $this->render($this->session->get('pathSystem') . ':DownloadFileSie:fileDownload.html.twig', array(
                            'arrValidationPrecess' => $arrValidationPrecess,
                            'objUe' => $objUe[0],
                            'form' => '',
                            'flagValidation' => '1'
                ));
            }*/
            //
            //this is to validate the files download
            //get info if the UE is plena
            // $errorValidation = $this->validateDownload($form);
            // if ($errorValidation['ueplena'] || $errorValidation['ueobservation']) {
            //     //get ue data
            //     $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);
            //     return $this->render($this->session->get('pathSystem') . ':DownloadFileSie:fileDownload.html.twig', array(
            //                 'uEducativa' => $errorValidation,
            //                 'objUe' => $objUe[0],
            //                 'form' => '',
            //                 'swvalidation' => '1',
            //                 'flagValidation' => '0',
            //                 'swObservados' => '0',
            //                 'swinconsistencia'  => '0',
            //                 'ueModular' => '0',
            //                 'validationPersonal' => '0',
            //                 'validationRegistroConsolidado' => '0',
            //                 'sistemaRegular' => '0'
            //     ));
            // }

            //fill modular data
            // $sieModular = array(
            //   '1'=>'82190066','82190032','82190082','82190056','82220075','82220110',
            //   '82220216','82220066','80390012','60390025','80440013','80440031',
            //   '80870039','80870078','80870069','80870043','70590010','80590021',
            //   '80590037','80590029','72460009','72460008','72460023','72460014',
            //   '81870039','81870028','81870041','81870037','81960030','61840025',
            //   '81870026','81870056','61710061','61710080','61710074','61710033',
            //   '81960054','82220075'
            // );
            // //validation ue modular
            // if (in_array($form['sie'], $sieModular)) {
            //   //get ue data
            //   $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);
            //   return $this->render($this->session->get('pathSystem') . ':DownloadFileSie:fileDownload.html.twig', array(
            //               'uEducativa' => $errorValidation,
            //               'objUe' => $objUe[0],
            //               'swvalidation' => '1',
            //               'flagValidation' => '0',
            //               'swObservados' => '0',
            //               'ueModular' => '1',
            //               'swinconsistencia'  => '0',
            //               'validationPersonal' => '0',
            //               'validationRegistroConsolidado' => '0',
            //               'sistemaRegular' => '0'
            //   ));
            // }
            //validacion if the UE is observacion
            // $objObservados = $em->getRepository('SieAppWebBundle:UesObservadas')->findBy(array(
            //   'institucioneducativaId'=>$form['sie']
            // ));
            //
            // if (sizeof($objObservados)>0){
            //   //get ue data
            //   $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);
            //   return $this->render($this->session->get('pathSystem') . ':DownloadFileSie:fileDownload.html.twig', array(
            //               'uEducativa' => $errorValidation,
            //               'objUe' => $objUe[0],
            //               'swvalidation' => '1',
            //               'flagValidation' => '0',
            //               'swObservados' => '1',
            //               'ueModular' => '0',
            //               'swinconsistencia'  => '0',
            //               'observaciones' => $objObservados,
            //               'validationPersonal' => '0',
            //               'validationRegistroConsolidado' => '0',
            //               'sistemaRegular' => '0'
            //   ));
            // }
            //get the content of directory
            $aDirectoryContent = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/';

            //validation consolidaction info ue
            /***********************************\
            * *
            * Validacion personal Administrativo de las Unidades Educativas
            * send array => sie, gestion, reglas *
            * return type of UE *
            * *
            \************************************/
            $objOperativoValidacionPersonal = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoValidacionpersonal')->findBy(array(
              'institucioneducativa' => $form['sie'],
              'gestionTipo' => $form['gestion'],
              'notaTipo' => $form['bimestre']
            ));
            $arrValidacionPersonal = array();
            if($objOperativoValidacionPersonal>0){
              foreach ($objOperativoValidacionPersonal as $key => $value) {
                # code...
                if($value->getRolTipo()->getId() == 2 || $value->getRolTipo()->getId() == 5)
                  $arrValidacionPersonal[] = $value->getRolTipo()->getId();
              }
            }
            //validation docente Administrativo director
            if(sizeof($arrValidacionPersonal)<2){
              //$errorValidation = array();
              $objObservados = array();
              $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);
              return $this->render($this->session->get('pathSystem') . ':DownloadFileSie:fileDownload.html.twig', array(
                          'uEducativa' => $errorValidation,
                          'objUe' => $objUe[0],
                          'swvalidation' => '1',
                          'flagValidation' => '0',
                          'swObservados' => '0',
                          'ueModular' => '0',
                          'swinconsistencia'  => '0',
                          'observaciones' => $objObservados,
                          'validationPersonal' => '1',
                          'validationRegistroConsolidado' => '0',
                          'sistemaRegular' => '0'

              ));
            }
          }else {
            //validation consolidaction info ue
            /***********************************\
            * *
            * Validacion personal Administrativo de las Unidades Educativas
            * send array => sie, gestion, reglas *
            * return type of UE *
            * *
            \************************************/
            $objOperativoValidacionPersonal = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoValidacionpersonal')->findBy(array(
              'institucioneducativa' => $form['sie'],
              'gestionTipo' => $form['gestion'],
              'notaTipo' => $form['bimestre']
            ));
            $arrValidacionPersonal = array();
            if($objOperativoValidacionPersonal>0){
              foreach ($objOperativoValidacionPersonal as $key => $value) {
                # code...
                if($value->getRolTipo()->getId() == 2 || $value->getRolTipo()->getId() == 5)
                  $arrValidacionPersonal[] = $value->getRolTipo()->getId();
              }
            }
            //validation docente Administrativo director
            if(sizeof($arrValidacionPersonal)<2){
              //$errorValidation = array();
              $objObservados = array();
              $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);
              return $this->render($this->session->get('pathSystem') . ':DownloadFileSie:fileDownload.html.twig', array(
                          'uEducativa' => $errorValidation,
                          'objUe' => $objUe[0],
                          'swvalidation' => '1',
                          'flagValidation' => '0',
                          'swObservados' => '0',
                          'ueModular' => '0',
                          'swinconsistencia'  => '0',
                          'observaciones' => $objObservados,
                          'validationPersonal' => '1',
                          'validationRegistroConsolidado' => '0',
                          'sistemaRegular' => '0'

              ));
            }
          }

          // end valiation IG
          //set the ctrol menu with true
          // $optionCtrlOpeMenu = $this->setCtrlOpeMenuInfo($form,$swCtrlMenu);

          if($form['gestion'] == $this->session->get('currentyear') && $form['bimestre']==0){

              $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToStudent(array('sie'=>$form['sie'],'gestion'=>$form['gestion']-1));


              //get the status UE
              $objStatusUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);
              //get consolidation info UE
              $objSie = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->getGestionBySie($form['sie']);
              $objSieNew = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
                'unidadEducativa' => $form['sie'] ,
                'gestion' => $form['gestion']-1
              ));
              
              //validation of new UE
              if($objStatusUe->getEstadoInstitucionTipo()->getId()==10 && $objSie){
                if($objSieNew && $operativo < 5){
                  //$errorValidation = array();
                  $objObservados = array();
                  $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);
                  return $this->render($this->session->get('pathSystem') . ':DownloadFileSie:fileDownload.html.twig', array(
                              'uEducativa' => $errorValidation,
                              'objUe' => $objUe[0],
                              'swvalidation' => '1',
                              'flagValidation' => '0',
                              'swObservados' => '0',
                              'ueModular' => '0',
                              'swinconsistencia'  => '0',
                              'observaciones' => $objObservados,
                              'validationRegistroConsolidado' => '1',
                              'validationPersonal' => '0',
                              'sistemaRegular' => '0'
                  ));
                }
              }
          }

            //if (!(in_array($form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.igm', scandir($aDirectoryContent, 1)))) {
            if (1) {
                //generate to file with thwe sql process
                $operativo = $form['bimestre'] + 1;
                // switch ($form['gestion']) {
                //     case $this->session->get('currentyear'):
                if($form['gestion'] == 2019 && $operativo == 5){
                  $query = $em->getConnection()->prepare("select * from sp_genera_arch_regular_txt_sin_6to('" . $form['sie'] . "','" . $form['gestion'] . "','" . $operativo . "','" . $form['bimestre'] . "');");
                }else{              
                  switch ($operativo) {
                    case '1':
                      # code...
                      $query = $em->getConnection()->prepare("select * from sp_genera_arch_regular_txtIG('" . $form['sie'] . "','" . $form['gestion'] . "','" . $operativo . "','" . $form['bimestre'] . "');");
                      break;
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                        $query = $em->getConnection()->prepare("select * from sp_genera_arch_regular_txt('" . $form['sie'] . "','" . $form['gestion'] . "','" . $operativo . "','" . $form['bimestre'] . "');");
                      break;
                    default:
                      # code...
                      break;
                  }
                }

                //         break;
                //     case '2015':
                //         $query = $em->getConnection()->prepare("select * from sp_genera_arch_regular_txt('" . $form['sie'] . "','" . $form['gestion'] . "','" . $operativo . "','" . $form['bimestre'] . "');");
                //         break;
                //
                //     default:
                //         $query = $em->getConnection()->prepare("select * from sp_genera_arch_regular_txt('" . $form['sie'] . "','" . $form['gestion'] . "','" . $operativo . "','" . $form['bimestre'] . "');");
                //         break;
                // }

                $query->execute();
                //$em->getConnection()->commit();
                $em->flush();
                $em->clear();
                //die('krlos');60900064
		$pathDoc = '/archivos/descargas/';
		//$outputdata = system('cat '.$pathDoc. ,$retval);
		//  system('/usr/local/bin/base64 -d ' . $dirtmp . '/' . $nameFileUnZip . ' >>  ' . $dirtmp . '/' . $nameFileUnZip . '.txt',$output);
		//$outPutData = system('/usr/local/bin/base64

                //$outputdata = system('/usr/local/bin/base64 '.$pathDoc.''.$form['sie'] . '-' . date('Y-m-d') . '_' . $form['bimestre'] . 'B.sie  >> ' . $pathDoc . 'e' . $form['sie'] . '-' . date('Y-m-d') . '_' . $form['bimestre'] . 'B.sie');
                $outputdata = system('base64 '.$pathDoc.''.$form['sie'] . '-' . date('Y-m-d') . '_' . $form['bimestre'] . 'B.sie  >> ' . $pathDoc . 'e' . $form['sie'] . '-' . date('Y-m-d') . '_' . $form['bimestre'] . 'B.sie');

		/*
                //todo the connexion to the server
                $connection = ssh2_connect('172.20.0.103', 22);
                ssh2_auth_password($connection, 'root', 'ASDFqwe12.103');
                $sftp = ssh2_sftp($connection);
                //get the path server
                $path = '../bajada_local/';
                //ssh2_exec($connection, 'iconv -f UTF-8  -t ISO-8859-1 ' . $path . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie  >> ' . $path . 'ee' . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie');
                ssh2_exec($connection, 'base64  ' . $path . '' . $form['sie'] . '-' . date('Y-m-d') . '_' . $form['bimestre'] . 'B.sie  >> ' . $path . 'e' . $form['sie'] . '-' . date('Y-m-d') . '_' . $form['bimestre'] . 'B.sie');
                //ssh2_exec($connection, 'cp ' . $path . '' . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie   ' . $path . 'e' . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie');
                /////////////////////////////////
                $server = "172.20.0.103"; //address of ftp server (leave out ftp://)
                $ftp_user_name = "regulardb"; // Username
                $ftp_user_pass = "regular2015v4azx-"; // Password

                $mode = "FTP_BINARY";
                $conn = ftp_connect($server, 21);

                $login = ftp_login($conn, $ftp_user_name, $ftp_user_pass);

                if (!$conn || !$login) {
                    die("Connection attempt failed!");
                }
                // try to download $server_file and save to $local_file
                $newGenerateFile = $form['sie'] . '-' . date('Y-m-d') . '_' . $form['bimestre'] . 'B';
                $local_file = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/' . 'e' . $newGenerateFile . '.sie';
                $server_file = 'e' . $newGenerateFile . '.sie';

                if (ftp_get($conn, $local_file, $server_file, FTP_BINARY)) {
                    //echo "generado correctamente to $local_file\n";
                } else {
                    echo "There was a problem\n :(";
                }
		*/

                //$dir = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/';
		$newGenerateFile = $form['sie'] . '-' . date('Y-m-d') . '_' . $form['bimestre'] . 'B';
		$dir = '/archivos/descargas/';

                /*
                  //GET THE FILE
                  $file = $dir . 'e' . $newGenerateFile . '.sie';
                  $fch = fopen($file, "a+");
                  $fileContent = $file = file_get_contents($file, true);
                  //$texto = utf8_decode($fileContent);
                  //$Result = base64_encode($fileContent);
                  fwrite($fch, $fileContent); // Grabas
                  fclose($fch);
                 */

                //exec('/usr/local/bin/zip -P 3I35I3Client ' . $dir . $newGenerateFile . '.zip ' . $dir . 'e' . $newGenerateFile . '.sie');
                exec('zip -P 3I35I3Client ' . $dir . $newGenerateFile . '.zip ' . $dir . 'e' . $newGenerateFile . '.sie');
                exec('mv ' . $dir . $newGenerateFile . '.zip ' . $dir . $newGenerateFile . '.igm ');

                //ssh2_sftp_unlink($sftp, '/bajada_local/' . $server_file);
                //ssh2_sftp_unlink($sftp, '/bajada_local/' . $newGenerateFile . '.sie');
		$server_file = 'e' . $newGenerateFile . '.sie';
                system('rm -fr ' . $pathDoc . $server_file);
                system('rm -fr ' . $pathDoc . $newGenerateFile.'.sie');
//                ftp_close($conn);
            } else {
                $newGenerateFile = $form['sie'] . '-' . date('Y-m-d') . '_' . $form['bimestre'] . 'B';
            }
            //save in donwload File Control
            $objinstitucioneducativaOperativoLog = $this->saveInstitucioneducativaOperativoLog($form);
            $objDonwloadFileControl = $this->saveInControlDownload($form);
            $em->getConnection()->commit();
            //echo "done";
            $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);

            return $this->render($this->session->get('pathSystem') . ':DownloadFileSie:fileDownload.html.twig', array(
                        'uEducativa' => $objUe[0],
                        'file' => $newGenerateFile . '.igm',
                        'form' => $this->createFormToBuild($form['sie'], $form['gestion'], $form['bimestre'])->createView(),
                        'swvalidation' => '0',
                        'flagValidation' => '0',
                        'swinconsistencia'  => '0',
                        'datadownload' => json_encode($form)
            ));
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            $em->getConnection()->rollback();
            $em->close();
            throw $e;
        }
    }
    private function setCtrlOpeMenuInfo($data,$switch){

      //ini db conexion
      $em = $this->getDoctrine()->getManager();
      // $em->getConnection()->beginTransaction();

      try {
        $objCtrlOpeMenu = $em->getRepository('SieAppWebBundle:InstitucioneducativaControlOperativoMenus')->findOneBy(array(
          'institucioneducativa' => $data['sie'],
          'gestionTipoId' => $data['gestion'],
          'notaTipo' => $data['bimestre'],
        ));
        // dump($objCtrlOpeMenu);
        if($objCtrlOpeMenu){
          $objCtrlOpeMenu->setEstadoMenu($switch);
        }else{
          $objCtrlOpeMenu = new InstitucioneducativaControlOperativoMenus();
          $objCtrlOpeMenu->setGestionTipoId($data['gestion']);
          $objCtrlOpeMenu->setEstadoMenu($switch);
          $objCtrlOpeMenu->setFecharegistro(new \DateTime('now'));
          $objCtrlOpeMenu->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($data['bimestre']));
          $objCtrlOpeMenu->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($data['sie']));

        }
        $em->persist($objCtrlOpeMenu);
        $em->flush();
        // $em->getConnection()->commit();

      } catch (Exception $e) {
        // $em->getConnection()->rollback();
        echo "we have this problem ".$e;
        return array();
      }
      // die('krlos');
      return 'done';
    }

    private function getObservationQA($data){
      
      //    and vp.validacion_regla_tipo_id  in (".$data['reglas'].")
      $em = $this->getDoctrine()->getManager();
      $query = $em->getConnection()->prepare("
                                                select vp.*
                                                from validacion_proceso vp
                                                where vp.institucion_educativa_id = '".$data['sie']."' and vp.gestion_tipo_id = '".$data['gestion']."'
                                                and vp.validacion_regla_tipo_id  in (".$data['reglas'].")
                                                and vp.es_activo = 'f'
                                            ");
          //
      $query->execute();
      $objobsQA = $query->fetchAll();


      return $objobsQA;
    }
    private function getObservationAllowUE($data){

      $em = $this->getDoctrine()->getManager();
      $query = $em->getConnection()->prepare("
                                                select *
                                                from institucioneducativa_humanistico_tecnico ieht
                                                left join institucioneducativa_humanistico_tecnico_tipo iehtt on (ieht.institucioneducativa_humanistico_tecnico_tipo_id = iehtt.id)
                                                where ieht.institucioneducativa_id = '".$data['sie']."' and ieht.gestion_tipo_id = '".$data['gestion']."'
                                                and iehtt.id  in (".$data['reglasUE'].")

                                            ");
          //
      $query->execute();
      $objobsQA = $query->fetchAll();

      return $objobsQA;
    }
    /**
     * save the log information about sie file donwload
     * @param  [type] $form [description]
     * @return [type]       [description]
     */

    private function saveInstitucioneducativaOperativoLog($data){
        //conexion to DB
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
          //save the log data
          $objDownloadFilenewOpe = new InstitucioneducativaOperativoLog();
          $objDownloadFilenewOpe->setInstitucioneducativaOperativoLogTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLogTipo')->find(1));
          $objDownloadFilenewOpe->setGestionTipoId($data['gestion']);
          $objDownloadFilenewOpe->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find($data['bimestre']+1));
          $objDownloadFilenewOpe->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($data['sie']));
          $objDownloadFilenewOpe->setInstitucioneducativaSucursal(0);
          $objDownloadFilenewOpe->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($data['bimestre']));
          $objDownloadFilenewOpe->setDescripcion('...');
          $objDownloadFilenewOpe->setEsexitoso('t');
          $objDownloadFilenewOpe->setEsonline('t');
          $objDownloadFilenewOpe->setUsuario($this->session->get('userId'));
          $objDownloadFilenewOpe->setFechaRegistro(new \DateTime('now'));
          $objDownloadFilenewOpe->setClienteDescripcion($_SERVER['HTTP_USER_AGENT']);
          $em->persist($objDownloadFilenewOpe);
          $em->flush();
           $em->getConnection()->commit();
          //dump($data);die;
          return 'krlos';
        } catch (Exception $e) {
          $em->getConnection()->rollback();
          echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }
    private function saveInControlDownload($data){

      $em = $this->getDoctrine()->getManager();
      $objDownloadFilenew = new ControlArchivosBajada();
      $objDownloadFilenew->setCodUe($data['sie']);
      $objDownloadFilenew->setBimestre($data['bimestre'] );
      $objDownloadFilenew->setOperativo($data['bimestre'] + 1);
      $objDownloadFilenew->setEstadoDescarga(1);
      $objDownloadFilenew->setGestion($data['gestion']);
      $objDownloadFilenew->setDateDownload(new \DateTime('now'));
      $objDownloadFilenew->setRemoteAddr($_SERVER['REMOTE_ADDR']);
      $objDownloadFilenew->setUserAgent($_SERVER['HTTP_USER_AGENT']);
      $em->persist($objDownloadFilenew);
      $em->flush();
      return 'krlos';
      //return 1;
    }
    private function validateDownload($data) {
        $em = $this->getDoctrine()->getManager();
        //get the data info Plena
        $objValidateUePlena = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->findOneBy(array('institucioneducativaId' => $data['sie']));
        $arrayError = array('ueplena' => false, 'ueobservation' => false);

        if ($objValidateUePlena) {
            $arrayError['ueplena'] = $objValidateUePlena;
        }
        //get the observation on UE
        /*$objValidateUeObs = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getObservationUnidadEducativaInfo($data);
        if ($objValidateUeObs) {
            $arrayError['ueobservation'] = $objValidateUeObs;
        }*/

        return $arrayError;
        //$objValidate = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);
    }

    private function createFormToBuild($sie, $gestion, $bim) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('download_file_sie_build'))
                        ->add('sie', 'hidden', array('data' => $sie))
                        ->add('gestion', 'hidden', array('data' => $gestion))
                        ->add('bimestre', 'hidden', array('data' => $bim))
                        ->add('generar', 'button', array('label' => 'Generar Archivo', 'attr' => array('class' => 'btn btn-link', 'onclick' => 'buildAgain()')))
                        ->getForm()
        ;
    }

    public function downloadAction(Request $request, $file,$datadownload) {
      // dump($datadownload);die;
      $form = json_decode($datadownload,true);
      $optionCtrlOpeMenu = $this->setCtrlOpeMenuInfo($form,1);
        //get path of the file
        //$dir = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/';
	      $dir = '/archivos/descargas/';
        //remove space on the post values
        $file = preg_replace('/\s+/', '', $file);
        $file = str_replace('%20', '', $file);
        //create response to donwload the file
        $response = new Response();
        //then send the headers to foce download the zip file
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $file));
        $response->setContent(file_get_contents($dir) . $file);
        $response->headers->set('Pragma', "no-cache");
        $response->headers->set('Expires', "0");
        $response->headers->set('Content-Transfer-Encoding', "binary");
        $response->sendHeaders();
        $response->setContent(readfile($dir . $file));
        return $response;
    }

    /**
 	 * to download the sie install
 	 * by krlos pacha pckrlos.a.gmail.dot.com
 	 * @param type
 	 * @return void
	 */
    public function installDownloadAction(Request $request) {

        //get path of the file
        $dir = $this->get('kernel')->getRootDir() . '/../web/uploads/instaladores/';
        $file = 'instalador_SIGED_SIE_v1292.exe';

        //create response to donwload the file
        $response = new Response();
        //then send the headers to foce download the zip file
        $response->headers->set('Content-Type', 'application/exe');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $file));
        $response->setContent(file_get_contents($dir) . $file);
        $response->headers->set('Pragma', "no-cache");
        $response->headers->set('Expires', "0");
        $response->headers->set('Content-Transfer-Encoding', "binary");
        $response->sendHeaders();
        $response->setContent(readfile($dir . $file));
        return $response;
    }

     /**
   * to download the sie install
   * by krlos pacha pckrlos.a.gmail.dot.com
   * @param type
   * @return void
   */
    public function updatedownloadAction(Request $request) {

        //get path of the file
        $dir = $this->get('kernel')->getRootDir() . '/../web/uploads/instaladores/';
        $file = 'actualizador_SIGED_v126_a_v128.exe';

        //create response to donwload the file
        $response = new Response();
        //then send the headers to foce download the zip file
        $response->headers->set('Content-Type', 'application/exe');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $file));
        $response->setContent(file_get_contents($dir) . $file);
        $response->headers->set('Pragma', "no-cache");
        $response->headers->set('Expires', "0");
        $response->headers->set('Content-Transfer-Encoding', "binary");
        $response->sendHeaders();
        $response->setContent(readfile($dir . $file));
        return $response;
    }


    public function getgestionAction($sie) {
        $em = $this->getDoctrine()->getManager();

        //get status of UE
        $objStatusUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);
        // dump($objStatusUe->getEstadoInstitucionTipo()->getId());die;
        $objSie = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->getGestionBySie($sie);

        $aGestion = array();
        //  if ($objSie) {
        if ($objStatusUe->getEstadoInstitucionTipo()->getId()==10) {
            foreach ($objSie as $gsie) {
                $aGestion[$gsie->getGestion()] = $gsie->getGestion();
            }
            //this is for current year
            $aGestion[$this->session->get('currentyear')] = $this->session->get('currentyear');
        }else{
          switch ($objStatusUe->getEstadoInstitucionTipo()->getId()) {
            case 19:
            case 29:
            case 39:
            case 49:
            case 99:
              # code...
              $aGestion['close'] = 'Unidad Educativa Cerrada';
              break;
            case 11:
            case 31:
            case 41:
              # code...
              $aGestion['close'] = 'Unidad Educativa Eliminada';
              break;
            case 0:
              # code...
              $aGestion['close'] = 'Unidad Educativa No Reportada';
              break;

            default:
              # code...
              break;
          }
        }

//        $dateyear = $this->session->get('currentyear');
//        //BUILD THE YEARS TO SELECTED DATA
//        $aGestion = array();
//        for ($i = 1; $i <= 3; $i ++) {
//            $aGestion[$dateyear] = $dateyear;
//            $dateyear = $dateyear - 1;
//        }
        //rsort($aGestion);
        $response = new JsonResponse();
        return $response->setData(array('gestion' => $aGestion));
    }

    public function getbimestreAction($sie, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $operativo = $this->get('funciones')->obtenerOperativoDown($sie, $gestion);
        $objSie = $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->getBimestreBySieAndGestion($sie, $gestion);
        //define the return data values
        // dump($gestion);
        // dump($operativo);
        $aBimestre = array();
        $aBimestres = array('IG', '1er', '2do', '3ro', '4to');
        // dump($this->session->get('roluser'));die;
        //if the user is UE decrement the operativo var
//         if( in_array($this->session->get('roluser'), array(9))){
//           // if($operativo == 4){
//           // //do nothing
//           // }else{
//             $operativo = $operativo - 1;
//           // }
//         }
// dump($operativo);
// die;
        //new way to download the sie file througth the consolidation data on DB
        if($operativo == 5){//if 4 everything is done
          $aBimestre[-1]='Consolidado';
        }else{
          if($operativo >= 0){//mt 0 return plas 1
            $aBimestre[$operativo]=$aBimestres[$operativo];
            // $aBimestre[$operativo]=$aBimestres[0];
          }else{ //lt 0 return the same
            // $aBimestre[$operativo]=$aBimestres[$operativo];
            $aBimestre[$operativo]=$aBimestres[0];
          }

        }

        $aBimestre = ($this->session->get('roluser') != 8) ? ($aBimestre) ? $aBimestre : array() : $aBimestres;
        //$aBimestre = ($aBimestre) ? array('1' => '1er', '2' => '2do', '3' => '3ro') : array();
        $response = new JsonResponse();
        return $response->setData(array('bimestre' => $aBimestre));
        // dump($objSie);die;
        /*this is for the user CBBA
        $lugarOneCBBA = $em->getRepository('SieAppWebBundle:LugarTipo')->find($this->session->get('roluserlugarid'));
        $lugarTwoCBBA = $em->getRepository('SieAppWebBundle:LugarTipo')->find($lugarOneCBBA->getLugarTipo());
        $swCbba = false;
        if($lugarTwoCBBA->getCodigo()==3){
          $swCbba = true;
        }
        end CBBA*/


        // if ($objSie) {
        //   $aBimestre[0] = $aBimestres[0];
        //     foreach ($objSie as $gsie) {
        //         //print_r($gsie);
        //         (!($gsie->getBim1())) ? $aBimestre[1] = $aBimestres[1] : '';
        //         (!($gsie->getBim2())) ? $aBimestre[2] = $aBimestres[2] : '';
        //         (!($gsie->getBim3())) ? $aBimestre[3] = $aBimestres[3] : '';
        //         (!($gsie->getBim4())) ? $aBimestre[4] = $aBimestres[4] : '';
        //       /*  comment by krlos, cos only donwload the 1bim
        //         (($gsie->getBim3())) ? $aBimestre[3] = $aBimestres[3] : '';
        //         (($gsie->getBim4())) ? $aBimestre[4] = $aBimestres[4] : '';*/
        //     }
        // } else {
        //     $aBimestre[0] = $aBimestres[0];
        // }
        // dump($operativo);die;


    }

    public function changedateAction() {
        $aDirectoryContent = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/';
        $aFilesIgm = scandir($aDirectoryContent, 1);
        foreach ($aFilesIgm as $file) {
            $contentFile = explode('-', $file);
            if (sizeof($contentFile) > 2) {
                $lastElement = explode('_', $contentFile[sizeof($contentFile) - 1]);
                $newFileIgm = $contentFile[0] . '-' . date('Y-m-d') . '_' . $lastElement[sizeof($lastElement) - 1];
                exec('mv ' . $aDirectoryContent . $file . ' ' . $aDirectoryContent . $newFileIgm);
            }
        }
        echo "done!!!";
        die;
    }

    public function buildAgainAction(Request $request) {


        $form['sie'] = $request->get('sie');
        $form['gestion'] = $request->get('gestion');
        $form['bimestre'] = $request->get('bimestre');
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            //get the content of directory
            $aDirectoryContent = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/';

            //generate to file with thwe sql process
            $operativo = $form['bimestre'] + 1;
            switch ($form['gestion']) {
                case '2016':
                  switch ($operativo) {
                    case '1':
                      # code...
                      //$query = $em->getConnection()->prepare("select * from sp_genera_arch_regular_txtIG('" . $form['sie'] . "','" . $form['gestion'] . "','" . $operativo . "','" . $form['bimestre'] . "');");
                      break;
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                        $query = $em->getConnection()->prepare("select * from sp_genera_arch_regular_txt('" . $form['sie'] . "','" . $form['gestion'] . "','" . $operativo . "','" . $form['bimestre'] . "');");
                      break;
                    default:
                      # code...
                      break;
                  }

                    break;
                case '2015':
                    $query = $em->getConnection()->prepare("select * from sp_genera_arch_regular_txt('" . $form['sie'] . "','" . $form['gestion'] . "','" . $operativo . "','" . $form['bimestre'] . "');");
                    break;
            }
            $query->execute();
            $em->getConnection()->commit();
            $em->flush();
            $em->clear();
            //die('krlos');
            //todo the connexion to the server
            $connection = ssh2_connect('172.20.0.103', 22);
            ssh2_auth_password($connection, 'root', 'ASDFqwe12.103');
            $sftp = ssh2_sftp($connection);
            //get the path server
            $path = '../bajada_local/';
            //ssh2_exec($connection, 'iconv -f UTF-8  -t ISO-8859-1 ' . $path . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie  >> ' . $path . 'ee' . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie');
            ssh2_exec($connection, 'base64  ' . $path . '' . $form['sie'] . '-' . date('Y-m-d') . '_' . $form['bimestre'] . 'B.sie  >> ' . $path . 'e' . $form['sie'] . '-' . date('Y-m-d') . '_' . $form['bimestre'] . 'B.sie');
            //ssh2_exec($connection, 'cp ' . $path . '' . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie   ' . $path . 'e' . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie');
            /////////////////////////////////
            $server = "172.20.0.103"; //address of ftp server (leave out ftp://)
            $ftp_user_name = "regulardb"; // Username
            $ftp_user_pass = "regular2015v4azx-"; // Password

            $mode = "FTP_BINARY";
            $conn = ftp_connect($server, 21);

            $login = ftp_login($conn, $ftp_user_name, $ftp_user_pass);

            if (!$conn || !$login) {
                die("Connection attempt failed!");
            }
            // try to download $server_file and save to $local_file

            $newGenerateFile = $form['sie'] . '-' . date('Y-m-d') . '_' . $form['bimestre'] . 'B';
            $local_file = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/' . 'e' . $newGenerateFile . '.sie';

	    $server_file = 'e' . $newGenerateFile . '.sie';

            if (ftp_get($conn, $local_file, $server_file, FTP_BINARY)) {
                //echo "generado correctamente to $local_file\n";
            } else {
                echo "There was a problem\n :(";
            }


            $dir = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/';
            /*
              //GET THE FILE
              $file = $dir . 'e' . $newGenerateFile . '.sie';
              $fch = fopen($file, "a+");
              $fileContent = $file = file_get_contents($file, true);
              //$texto = utf8_decode($fileContent);
              //$Result = base64_encode($fileContent);
              fwrite($fch, $fileContent); // Grabas
              fclose($fch);
             */
            exec('zip -P 3I35I3Client ' . $dir . $newGenerateFile . '.zip ' . $dir . 'e' . $newGenerateFile . '.sie');
            exec('mv ' . $dir . $newGenerateFile . '.zip ' . $dir . $newGenerateFile . '.igm ');
            ssh2_sftp_unlink($sftp, '/bajada_local/' . $server_file);
            //system('rm -fr ' . $dir . $newGenerateFile . '.igm ');
            system('rm -fr ' . $dir . $server_file);
            ftp_close($conn);


            //echo "done";
            $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);

            return $this->render($this->session->get('pathSystem') . ':DownloadFileSie:fileDownload.html.twig', array(
                        'uEducativa' => $objUe[0],
                        'file' => $newGenerateFile . '.igm',
                        'form' => $this->createFormToBuild($form['sie'], $form['gestion'], $form['bimestre'])->createView()
            ));
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            $em->getConnection()->rollback();
            $em->close();
            throw $e;
        }
    }

}
