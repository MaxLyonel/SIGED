<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\UploadFileControl;
use \Symfony\Component\HttpFoundation\Response;

/**
 * Author: Carlos Pacha Cordova <pckrlos@gmail.com>
 * Description:This is a class for uploading infra file
 * Date: 19-09-2019
 *
 *
 * UpInfraFileController.php
 *
 * Email bugs/suggestions to <pckrlos@gmail.com>
 */
class UpInfraFileController extends Controller{
    private $session;
    public  $userlogged;
    public function __construct() {        
        $this->session = new Session();        
        $this->userlogged = $this->session->get('userId');
    }

    public function indexAction(Request $request){
        // dump($request);
        // die;
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $template = 'index.html.twig';
        $form = $this->lookforSieForm(array())->createView();

        // switch ($this->session->get('roluser')) {
        //     case 8:
        //         //user nacional
        //         # code...
        //         $template = 'index.html.twig';
        //         $form = $this->lookforSieForm(array())->createView();
        //         break;
        //     case 9:
        //         //user ue
        //         # code...
        //         return $this->redirectToRoute('upinfrafile_lookfor_data');
        //         break;
        //     default:
        //         # code...
        //         break;
        // }


        
        return $this->render('SieHerramientaBundle:UpInfraFile:'.$template, array(
                'form' =>  $form
        ));    
    }

    private function lookforSieForm($data){
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('upinfrafile_lookfor_data'));
        if($this->session->get('roluser')==9)
            $form = $form
                ->add('sie','text',array('label'=>'SIE','data'=>$this->session->get('ie_id'), 'attr'=>array('placeholder'=>'Ingrese SIE', 'class'=>'form-control','readonly'=>true)));
        else{
            $form = $form
                ->add('sie','text',array('label'=>'SIE', 'attr'=>array('placeholder'=>'Ingrese SIE', 'class'=>'form-control')));
        }
        $form = $form
                ->add('find','submit',array('label'=>'Buscar','attr'=>array('class'=>'btn btn-success','onclick'=>'')))
                ->getForm();
        return $form;
    }

    public function lookforDataAction(Request $request){
        // get the send values
        $form = $request->get('form');
        // check the user and methos send
        if(!$form){
            $form = array('sie'=>$this->session->get('ie_id'));
        }
        $form['gestion'] = $this->session->get('currentyear');
        
        return $this->render('SieHerramientaBundle:UpInfraFile:lookforData.html.twig', array(
                'form' => $this->creeateFormUpFile(json_encode($form))->createview(),
        ));    
    }


    private function creeateFormUpFile($jsondata){
        return $this->createFormBuilder()
        ->add('upfile', 'file', array() )
        ->add('dataUe', 'hidden', array('mapped' => false, 'data' => $jsondata))
        ->add('sendata', 'submit', array('label'=>'Subir Archivo', 'attr'=>array('class'=>'btn btn-primary', 'id'=>'btnGuardar')))
        ->getForm();
    }

    public function upinfrafileAction(Request $request){
        
        $infoFile = $request->files;
        $arrFile = $_FILES['form'];
        $arrForm = $request->get('form');
        $arrDataUe = json_decode($arrForm['dataUe'],true);
        $sieUpload = $arrDataUe['sie'];

        // create db conexion
        $em = $this->getDoctrine()->getManager();
        $sw = false;


        $strName = $arrFile['name']['upfile'];
        $last_word_start = strrpos ( $strName , ".") + 1;
        $last_word_end = strlen($strName) - 1;
        $fileType = substr($strName, $last_word_start, $last_word_end);
        $aValidTypes = array('emp');
        if (!in_array(strtolower($fileType), $aValidTypes)) {
            $sw = true;
            $this->addFlash('warningupfileinfra', 'El archivo que intenta subir No tiene la extension correcta');
            $arrData = array('sw'=>$sw,'rolUser'=>$this->session->get('roluser'),'messageType'=>'warning');
            return $this->returnResponse($arrData);
        }
        // //validate the files weight
        // if (!( 10001 < $oFile->getSize()) && !($oFile->getSize() < 2000000000)) {
        //     $session->getFlashBag()->add('warningupfileinfra', 'El archivo que intenta subir no tiene el tamaño correcto');
        //     return $this->redirect($this->generateUrl('consolidation_sie_web'));
        // }
        // //make the temp dir name
        $dirtmp = $this->get('kernel')->getRootDir() . '/../web/uploads/filesInfra/archivos/' . $strName;
        // check if the paht has the correct privilegios
        if (is_readable($this->get('kernel')->getRootDir() . '/../web/uploads/filesInfra/archivos')) {
        // nothing to do     
        }else{
            $sw = true;
            $this->addFlash('warningupfileinfra', 'Problemas con permisos al intenar subir el archivo emp');
            $arrData = array('sw'=>$sw,'rolUser'=>$this->session->get('roluser'),'messageType'=>'warning');
            return $this->returnResponse($arrData);
        }

        //check if the file exist to create the same
        if (!file_exists($dirtmp)) {
            mkdir($dirtmp, 0775);
        }else{
          system('rm -fr ' . $dirtmp.'/*');
        }       
        // move the upfile
        move_uploaded_file($arrFile['tmp_name']['upfile'], $dirtmp.'/'.$arrFile['name']['upfile']);
        // unzip the infra file
        if (!$result = exec('unzip ' . $dirtmp . '/' . $strName . ' -d ' . $dirtmp . '/')) {
            $this->addFlash('warningupfileinfra', 'El archivo ' . $strName . ' tiene problemas para descomprimirse');
            system('rm -fr ' . $dirtmp);
            // return $this->redirect($this->generateUrl('consolidation_sie_web'));
            $arrData = array('sw'=>$sw,'rolUser'=>$this->session->get('roluser'),'messageType'=>'warning');
                return $this->returnResponse($arrData);
        } 


        //get the content of extract file
        $aDataFileUnzip = explode('/', $result);        
        $nameFileUnZip = str_replace(' ', '\ ', $aDataFileUnzip[sizeof($aDataFileUnzip) - 1]);
        $decodeFile = substr($nameFileUnZip,0,strlen($nameFileUnZip)-4);
        //convert the file to base64
        system('base64 -d -i ' . $dirtmp . '/' . $nameFileUnZip . ' >  ' . $dirtmp . '/' . $nameFileUnZip . '.txt',$output);
        //convert file to UTF8
        // system('iconv -f UTF-8 -t UTF-8 ' . $dirtmp . '/' . $nameFileUnZip . '.txt >> ' . $dirtmp . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 1]);
        system('iconv -f UTF-8 -t UTF-8 ' . $dirtmp . '/' . $nameFileUnZip . '.txt >> ' . $dirtmp . '/' . $decodeFile);
        //get the content of file        
        // $fileInfoContent = file_get_contents($dirtmp . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 1].'.txt');
        $fileInfoContent = file_get_contents($dirtmp . '/' . $decodeFile);
        $arrFileInfoContent = json_decode($fileInfoContent,true);
        // to validate the correct LE jurisdiccion_geografica
        //get the infor about the UE
        $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sieUpload);
        // dump($objUe->getLeJuridicciongeografica()->getId());
        // die;
        if($objUe->getLeJuridicciongeografica()->getId() == $arrFileInfoContent['cabecera']['jurisdiccion_geografica_id']){
            // nothing to do 
        }else{
            system('rm -fr ' . $dirtmp);
            $sw = true;
            $this->addFlash('warningupfileinfra', 'Codigo SIE no pertenece al local educativo');
            $arrData = array('sw'=>$sw,'rolUser'=>$this->session->get('roluser'),'messageType'=>'warning');
            return $this->returnResponse($arrData);
        }

        // create the  year main directory
        $pathMainDir   = $this->get('kernel')->getRootDir() . '/../web/uploads/filesInfra/archivos/';
        $pathYearDir   = $pathMainDir.$arrFileInfoContent['cabecera']['gestion_tipo_id'];
        $pathDirToSave = $arrFileInfoContent['cabecera']['gestion_tipo_id'];



        if(!file_exists($pathYearDir)){
            mkdir($pathYearDir, 0770);
        }else{
            if (!is_readable($pathYearDir)) {
                $this->addFlash('warningupfileinfra', 'Problemas con permisos al intenar subir el archivo emp');
                system('rm -fr ' . $dirtmp);
                $arrData = array('sw'=>$sw,'rolUser'=>$this->session->get('roluser'),'messageType'=>'warning');
                return $this->returnResponse($arrData);
            }
        }
        // create distrito dir
        $pathDistritoDir = $pathYearDir.'/'.$arrFileInfoContent['cabecera']['codigo_distrito'];
        $pathDirToSave  = $pathDirToSave .'/'.$arrFileInfoContent['cabecera']['codigo_distrito'];
        if(!file_exists($pathDistritoDir)){
            mkdir($pathDistritoDir, 0770);
        }else{
            if (!is_readable($pathDistritoDir)) {
                $this->addFlash('warningupfileinfra', 'Problemas con permisos al intenar subir el archivo emp');
                system('rm -fr ' . $dirtmp);
                $arrData = array('sw'=>$sw,'rolUser'=>$this->session->get('roluser'),'messageType'=>'warning');
                return $this->returnResponse($arrData);
            }
        }
        // create cod local educativo dir
        $pathLocalEduDir = $pathDistritoDir.'/'.$arrFileInfoContent['cabecera']['jurisdiccion_geografica_id'];
        $pathDirToSave = $pathDirToSave.'/'.$arrFileInfoContent['cabecera']['jurisdiccion_geografica_id'];
        if(!file_exists($pathLocalEduDir)){
            mkdir($pathLocalEduDir, 0770);
        }else{
            if (!is_readable($pathLocalEduDir)) {
                $this->addFlash('warningupfileinfra', 'Problemas con permisos al intenar subir el archivo emp');
                system('rm -fr ' . $dirtmp);
                $arrData = array('sw'=>$sw,'rolUser'=>$this->session->get('roluser'),'messageType'=>'warning');
                return $this->returnResponse($arrData);
            }
        }
        // create operativo directory
        $pathOperativoDir = $pathLocalEduDir.'/'.$arrFileInfoContent['cabecera']['operativo_tipo_id'];
        $pathDirToSave = $pathDirToSave.'/'.$arrFileInfoContent['cabecera']['operativo_tipo_id'];
        if(!file_exists($pathOperativoDir)){
            mkdir($pathOperativoDir, 0770);
        }else{
            if (!is_readable($pathOperativoDir)) {
                $this->addFlash('warningupfileinfra', 'Problemas con permisos al intenar subir el archivo emp');
                system('rm -fr ' . $dirtmp);
                $arrData = array('sw'=>$sw,'rolUser'=>$this->session->get('roluser'),'messageType'=>'warning');
                return $this->returnResponse($arrData);
            }
        }
        

        // save into db info about the file
        //look for row on the db with the head data
        $objUploadFile = $em->getRepository('SieAppWebBundle:UploadFileControl')->findOneBy(array(
            'codUe'     => $arrFileInfoContent['cabecera']['jurisdiccion_geografica_id'],
            // 'operativo' => $arrFileInfoContent['cabecera']['operativo_tipo_id'],
            'operativo' => 7,
            'gestion'   => $arrFileInfoContent['cabecera']['gestion_tipo_id']
        ));
        
        //validate if the file was uploaded
        if ($objUploadFile) {
            if ($objUploadFile->getEstadoFile()) {
                $sw = true;
                system('rm -fr ' . $dirtmp);
                $this->addFlash('warningupfileinfra', 'Archivo no cargado. El archivo que intenta subir ya fue cargado al repositorio');
                $arrData = array('sw'=>$sw,'rolUser'=>$this->session->get('roluser'),'messageType'=>'warning');
                return $this->returnResponse($arrData);
                // return $this->redirect($this->generateUrl('consolidation_sie_web'));
            } else {

                $objUploadFile->setEstadoFile(1);
                $objUploadFile->setDateUpload(new \DateTime('now'));
                $objUploadFile->setRemoteAddr($_SERVER['REMOTE_ADDR']);
                $objUploadFile->setUserAgent($_SERVER['HTTP_USER_AGENT']);
                $em->persist($objUploadFile);
                $em->flush();
            }
        }else{

                $objUploadFileNew = new UploadFileControl();
                $objUploadFileNew->setCodUe($arrFileInfoContent['cabecera']['jurisdiccion_geografica_id']);
                $objUploadFileNew->setBimestre($arrFileInfoContent['cabecera']['operativo_tipo_id']);
                // $objUploadFileNew->setOperativo($arrFileInfoContent['cabecera']['operativo_tipo_id']);
                $objUploadFileNew->setOperativo(7);
                $objUploadFileNew->setVersion($arrFileInfoContent['cabecera']['version']);
                $objUploadFileNew->setEstadoFile(1);
                $objUploadFileNew->setGestion($arrFileInfoContent['cabecera']['gestion_tipo_id']);
                $objUploadFileNew->setDistrito($arrFileInfoContent['cabecera']['codigo_distrito']);
                $objUploadFileNew->setPath('/consolidacion_online/' .$pathDirToSave. '/' . $decodeFile);
                $objUploadFileNew->setDateUpload(new \DateTime('now'));
                $objUploadFileNew->setRemoteAddr($_SERVER['REMOTE_ADDR']);
                $objUploadFileNew->setUserAgent($_SERVER['HTTP_USER_AGENT']);
                $em->persist($objUploadFileNew);
                $em->flush();

        }
        //move the file on the new local path
        // check if the file exists to move it
        if(is_readable($dirtmp) && is_readable($pathOperativoDir)){
          rename($dirtmp, $pathOperativoDir);
        }else{
            $this->addFlash('warningupfileinfra', 'Problemas al intenar subir el archivo emp');
          system('rm -fr ' . $dirtmp);
        }       
        $this->addFlash('warningupfileinfra', 'Archivo subido y registrado');
        $arrData = array('sw'=>$sw,'rolUser'=>$this->session->get('roluser'),'messageType'=>'success');
        return $this->returnResponse($arrData);
      
    }
    private function returnResponse($data){

         $rolUser = $this->session->get('roluser');
         return $this->render('SieHerramientaBundle:UpInfraFile:upinfrafile.html.twig', array(
            'sw' => $data['sw'], 
            'roluser' => $data['rolUser'], 
            'messageType' => $data['messageType'], 
        )); 

    }

            /**
         * save the log information about sie file donwload
         * @param  [type] $form [description]
         * @return [type]       [description]
         */

        private function saveInstitucioneducativaOperativoLog($data){
          // dump($data);
          //get the correct operativo log tipo id to save on the log table
          switch ($data['operativoLogTipo']) {            
            case "0":
              # code...
              $operativoLogTipo = 6;
              break;
            case "1":
            case "2":
            case "3":
            case "4":
            case "5":
              # code...
              $operativoLogTipo = 2;
              break;
            
            default:
              # code...
              $operativoLogTipo = 2;
              break;
          }
          
            //conexion to DB
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
              //save the log data
              $objDownloadFilenewOpe = new InstitucioneducativaOperativoLog();
              $objDownloadFilenewOpe->setInstitucioneducativaOperativoLogTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLogTipo')->find($operativoLogTipo));
              $objDownloadFilenewOpe->setGestionTipoId($data['gestion']);
              $objDownloadFilenewOpe->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find(1));
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


}
