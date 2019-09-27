<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\OlimEstudianteNotaPrueba;
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

        switch ($this->session->get('roluser')) {
            case 8:
                //user nacional
                # code...
                $template = 'index.html.twig';
                $form = $this->lookforSieForm(array())->createView();
                break;
            case 9:
                //user ue
                # code...
                return $this->redirectToRoute('upinfrafile_lookfor_data');
                break;
            default:
                # code...
                break;
        }


        
        return $this->render('SieHerramientaBundle:UpInfraFile:'.$template, array(
                'form' =>  $form
        ));    
    }

    private function lookforSieForm($data){
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('upinfrafile_lookfor_data'))
                ->add('sie','text',array('label'=>'SIE', 'attr'=>array('placeholder'=>'Ingrese SIE', 'class'=>'form-control')))
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
                'form' => $this->creeateFormUpFile()->createview(),
        ));    
    }


    private function creeateFormUpFile(){
        return $this->createFormBuilder()
        ->add('upfile', 'file', array() )
        ->add('sendata', 'submit', array('label'=>'Subir Archivo', 'attr'=>array('class'=>'btn btn-primary', 'id'=>'btnGuardar')))
        ->getForm();
    }

    public function upinfrafileAction(Request $request){
        
        $infoFile = $request->files;
        $arrFile = $_FILES['form'];
        $arrForm = $request->get('form');
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        $sw = false;
        // dump($arrFile);
        // dump($arrFile['name']['upfile']);

        $strName = $arrFile['name']['upfile'];
        $last_word_start = strrpos ( $strName , ".") + 1;
        $last_word_end = strlen($strName) - 1;
        $fileType = substr($strName, $last_word_start, $last_word_end);
        $aValidTypes = array('emp');
        if (!in_array(strtolower($fileType), $aValidTypes)) {
            $sw = true;
            $this->addFlash('warningcons', 'El archivo que intenta subir No tiene la extension correcta');
        }
        // //validate the files weight
        // if (!( 10001 < $oFile->getSize()) && !($oFile->getSize() < 2000000000)) {
        //     $session->getFlashBag()->add('warningcons', 'El archivo que intenta subir no tiene el tamaño correcto');
        //     return $this->redirect($this->generateUrl('consolidation_sie_web'));
        // }
        // //make the temp dir name
        $dirtmp = $this->get('kernel')->getRootDir() . '/../web/uploads/filesInfra/archivos/' . $strName;
        // check if the paht has the correct privilegios
        if (is_readable($this->get('kernel')->getRootDir() . '/../web/uploads/filesInfra/archivos')) {
        // nothing to do     
        }else{
            $sw = true;
            $this->addFlash('warningcons', 'Problemas con permisos al intenar subir el archivo emp');
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
            $this->addFlash('warningcons', 'El archivo ' . $strName . ' tiene problemas para descomprimirse');
            system('rm -fr ' . $dirtmp);
            return $this->redirect($this->generateUrl('consolidation_sie_web'));
        } 


        //get the content of extract file
        $aDataFileUnzip = explode('/', $result);        
        $nameFileUnZip = str_replace(' ', '\ ', $aDataFileUnzip[sizeof($aDataFileUnzip) - 1]);
        $decodeFile = substr($nameFileUnZip,0,strlen($nameFileUnZip)-4);
        // dump($decodeFile);
        // dump($nameFileUnZip);
        // die;
        //convert the file to base64
        system('base64 -d -i ' . $dirtmp . '/' . $nameFileUnZip . ' >  ' . $dirtmp . '/' . $nameFileUnZip . '.txt',$output);
        //convert file to UTF8
        // system('iconv -f UTF-8 -t UTF-8 ' . $dirtmp . '/' . $nameFileUnZip . '.txt >> ' . $dirtmp . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 1]);
        system('iconv -f UTF-8 -t UTF-8 ' . $dirtmp . '/' . $nameFileUnZip . '.txt >> ' . $dirtmp . '/' . $decodeFile);
        //get the content of file        
        // $fileInfoContent = file_get_contents($dirtmp . '/' . $aDataFileUnzip[sizeof($aDataFileUnzip) - 1].'.txt');
        $fileInfoContent = file_get_contents($dirtmp . '/' . $decodeFile);
        $arrFileInfoContent = json_decode($fileInfoContent,true);
        
        dump($arrFileInfoContent);
        // create the  year main directory
        $pathMainDir = $this->get('kernel')->getRootDir() . '/../web/uploads/filesInfra/archivos/';
        $pathYearDir = $pathMainDir.$arrFileInfoContent['cabecera']['gestion_tipo_id'];
        if(!file_exists($pathYearDir)){
            mkdir($pathYearDir, 0770);
        }else{
            if (!is_readable($pathYearDir)) {
                $this->addFlash('warningcons', 'Problemas con permisos al intenar subir el archivo emp');
                system('rm -fr ' . $dirtmp);
            }
        }
        // create distrito dir
        $pathDistritoDir = $pathYearDir.'/'.$arrFileInfoContent['cabecera']['codigo_distrito'];
        if(!file_exists($pathDistritoDir)){
            mkdir($pathDistritoDir, 0770);
        }else{
            if (!is_readable($pathDistritoDir)) {
                $this->addFlash('warningcons', 'Problemas con permisos al intenar subir el archivo emp');
                system('rm -fr ' . $dirtmp);
            }
        }
        // create cod local educativo dir
        $pathLocalEduDir = $pathDistritoDir.'/'.$arrFileInfoContent['cabecera']['jurisdiccion_geografica_id'];
        if(!file_exists($pathLocalEduDir)){
            mkdir($pathLocalEduDir, 0770);
        }else{
            if (!is_readable($pathLocalEduDir)) {
                $this->addFlash('warningcons', 'Problemas con permisos al intenar subir el archivo emp');
                system('rm -fr ' . $dirtmp);
            }
        }
        // create operativo directory
        $pathOperativoDir = $pathLocalEduDir.'/'.$arrFileInfoContent['cabecera']['operativo_tipo_id'];
        if(!file_exists($pathOperativoDir)){
            mkdir($pathOperativoDir, 0770);
        }else{
            if (!is_readable($pathOperativoDir)) {
                $this->addFlash('warningcons', 'Problemas con permisos al intenar subir el archivo emp');
                system('rm -fr ' . $dirtmp);
            }
        }
        dump($pathOperativoDir);
        //move the file on the new local path
        // check if the file exists to move it
        if(is_readable($dirtmp) && is_readable($pathOperativoDir)){
          rename($dirtmp, $pathOperativoDir);  
        }else{
            $this->addFlash('warningcons', 'Problemas al intenar subir el archivo emp');
            system('rm -fr ' . $dirtmp);
        }

       
        die;






        
        $rolUser = $this->session->get('roluser');
         return $this->render('SieHerramientaBundle:UpInfraFile:upinfrafile.html.twig', array(
            'sw' => $sw, 
            'roluser' => $rolUser, 
        )); 

      
    }


}
