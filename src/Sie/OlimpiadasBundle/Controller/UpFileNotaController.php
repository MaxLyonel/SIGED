<?php

namespace Sie\OlimpiadasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\OlimEstudianteNotaPrueba;
use \Symfony\Component\HttpFoundation\Response;

/**
 * Author: Carlos Pacha Cordova <pckrlos@gmail.com>
 * Description:This is a class for load the inscription data; if exist in a particular proccess
 * Date: 10-07-2018
 *
 *
 * UpFileNotaController.php
 *
 * Email bugs/suggestions to <pckrlos@gmail.com>
 */
class UpFileNotaController extends Controller{
    private $session;
    
    public function __construct() {        
        $this->session = new Session();        
    }
    
    public function indexAction(){
       //get the session user
         $id_usuario = $this->session->get('userId');
         //validation if the user is logged
         if (!isset($id_usuario)) {
             return $this->redirect($this->generateUrl('login'));
         }

        // the file view
       return $this->render('SieOlimpiadasBundle:UpFileNota:index.html.twig', array(
            'form' => $this->creeateFormUpFile()->createView()
                // ...
            ));    
    }

    private function creeateFormUpFile(){
        return $this->createFormBuilder()
        // ->add('etapa', 'entity', array('label'=>'Etapa','class'=>'SieAppWebBundle:OlimEtapaTipo', 'property'=>'etapa', 'attr'=>array('class'=>'form-control')))
        ->add('upfile', 'file', array() )
        ->add('enviar', 'submit', array('label'=>'Subir Archivo', 'attr'=>array('class'=>'btn btn-primary', 'id'=>'btnGuardar')))
        ->getForm();
    }

    public function upfileInDBAction(Request $request){
        set_time_limit(0    );
        // get the send values
        $infoFile = $request->files;
        $arrFile = $_FILES['form'];
        $arrForm = $request->get('form');
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        // $em->getConnection()->beginTransaction();
        $dirFile = $this->get('kernel')->getRootDir() . '/../web/uploads/olimpiadas/archivos/';
        // $dirFile = $this->get('kernel')->getRootDir() . '/../web/uploads/olimpiadas/upfileNota/';
        // move_uploaded_file($_FILES['fileNota']['tmp_name'], $dirFile.$_FILES['fileNota']['name']);
        move_uploaded_file($arrFile['tmp_name']['upfile'], $dirFile.$arrFile['name']['upfile']);

        try {

         // $upFile = $dirFile.$_FILES['fileNota']['name'];
         $upFile = $arrFile['name']['upfile'];
         
         $upFileName = substr($upFile, 0, strlen($upFile)-4);
         //call the up file nota into DB
        $query = $em->getConnection()->prepare('select * from sp_consolida_archivo_olimpiadas_cvs(:file, :gestion)');
        $query->bindValue(':file', $upFileName);
        $query->bindValue(':gestion', $this->session->get('currentyear'));
        $query->execute();
        $upFileResponse = $query->fetchAll();
        // validate if the upload has been some error
        $sw = false;
        $nameFile = false;
        if(sizeof($upFileResponse[0])>0){
            $sw = true;
            $arrDataFile = explode(':',$upFileResponse[0]['sp_consolida_archivo_olimpiadas_cvs']);
            $nameFile = trim(substr($arrDataFile[1], 0, strlen($arrDataFile[1])-1));
            
        }
        // return the view values
        return $this->render('SieOlimpiadasBundle:UpFileNota:upfileInDB.html.twig', array(
            'dataFile' =>$upFileResponse[0],
            'nameFile' =>$nameFile,
            'sw' => $sw, 
        ));    
            
        } catch (Exception $e) {
            dump($e);
            // $em->getConnection()->rollback();
        }

      
      
    }


    /**
     * to download the txt file to olimpiada
     * by krlos pacha pckrlos.a.gmail.dot.com
     * @param type
     * @return void
     */
     public function downloadObservationAction(Request $request){
        
           //get path of the file
        $dir = $this->get('kernel')->getRootDir() . '/../web/uploads/';
        $file = $request->get('nameFile');

        //create response to donwload the file
        $response = new Response();
        //then send the headers to foce download the zip file
        $response->headers->set('Content-Type', 'text/plain');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $file));
        $response->setContent(file_get_contents($dir) . $file);
        $response->headers->set('Pragma', "no-cache");
        $response->headers->set('Expires', "0");
        $response->headers->set('Content-Transfer-Encoding', "binary");
        $response->sendHeaders();
        $response->setContent(readfile($dir . $file));
        return $response;
    }

}
